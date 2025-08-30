import os
import re
import traceback
import pandas as pd
import sys
from io import BytesIO
import json
from sqlalchemy.sql import text
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

from validate_constants import (
    required_columns_lookup,
    all_cols_nullable_lookup, unique_rows_subset_lookup,
    all_cols_maxlen_lookup, numeric_cols_lookup
)

from sqlalchemy.sql import text
import json
from io import BytesIO

from upload_pipelines.lib.utils import write_log

from lib.conn import (
    SoComMinioSetting,
    SoComAWSSetting,
    get_s3_client,
)

class ExcelValidationError(Exception):
    def __init__(self, message, file_id=None, db_conn=None):
        super().__init__(message)
        self.file_id = file_id # row reference to USR_DT_UPLOADS
        self.db_conn = db_conn

    def log_error(self):
        cron_status = -1 # is -1 the right value for a failure
        cron_processed = -1  # is -1 the right value for a failure
        with self.db_conn.cursor() as cursor:
            cursor.execute(
                'UPDATE USR_DT_UPLOADS SET CRON_STATUS = %s, CRON_PROCESSED, ERRORS = %s WHERE ID = %s',
                (cron_status, cron_processed, str(self), self.file_id)
            )
            self.db_conn.commit()

class CsvValidationError(ExcelValidationError):
    def __init__(self,message,file_id=None,db_conn=None):
        super().__init__(message,file_id,db_conn)


class ExcelValidator:
    def __init__(self, df, file_source, debug=False):
        if file_source not in required_columns_lookup.keys():
            raise ExcelValidationError(f'{file_source} not one of {required_columns_lookup.keys()}')
        
        self.df = df
        self.file_source = file_source
        self.required_columns = required_columns_lookup[self.file_source]
        self.debug=debug
        self.info = []
        self.warnings = []
        self.errors = []

    def validate_columns(self):
        missing = [col for col in self.required_columns if col not in self.df.columns]
        if missing:
            missing_str = ', '.join(missing)
            raise ExcelValidationError(f'Missing required columns: [{missing_str}]')

    def validate_non_empty(self):
        non_nullable_columns = [
            k
            for k in self.required_columns
            if all_cols_nullable_lookup[k] == False
        ] # find all columns that require no nulls

        if self.df[non_nullable_columns].isnull().any().any():
            null_columns = [col for col in non_nullable_columns if self.df[col].isnull().any()]
            null_columns_str = ', '.join(null_columns)
            raise ExcelValidationError(f'at least one of these non nullable columns contains null values: [{null_columns_str}]')
        
    def validate_unique(self):
        id_cols = unique_rows_subset_lookup[self.file_source]
        duplicated_count =  self.df.duplicated(subset=id_cols).sum()
        if duplicated_count > 0:
            id_cols_str = ', '.join(id_cols)
            raise ExcelValidationError(f'found duplicate {duplicated_count} rows with subset of columns: [{id_cols_str}]')

    def validate_varchar_constraints(self):
        varchar_cols = {k: v for k, v in all_cols_maxlen_lookup.items() if isinstance(v, int) and (k in self.required_columns)}
        exceeds_list = []
        for col, max_len in varchar_cols.items():
            exceeds_count = (self.df[col].astype(str).str.len() > max_len).sum()
            if exceeds_count > 0:
                exceeds_list.append(f'column: {col}, max_len: {max_len}, number of rows in violation: {exceeds_count}')

        if exceeds_list:
            raise ExcelValidationError('\n'.join(exceeds_list))
        

    def validate_integer(self):
        integer_cols = {k: v for k, v in numeric_cols_lookup.items() if isinstance(v, int) and (k in self.required_columns)}
        problem_list = []

        for col, inttype in integer_cols.items():
            count_non_ints = (~self.df[col].apply(lambda x: x.isdigit() or isinstance(x, int))).sum() # nulls shouldve already been checked for
            if count_non_ints:
                problem_list.append(f'column: {col}, type: {inttype}, number of rows in violation: {count_non_ints}')

        if problem_list:
            raise ExcelValidationError('\n'.join(problem_list))
        
    def validate_program_code(self, schema, conn):
        # read LOOKUP_PROGRAM_DETAIL for distinct ID (PROGRAM_ID)
        # construct PROGRAM_ID={PROGRAM_CODE}_{POM_SPONSOR_CODE}_{CAPABILITY_SPONSOR_CODE}_{ASSESSMENT_AREA_CODE}
        # check if all constructed PROGRAM_ID from the input files exist in LOOKUP
        query = f'''SELECT DISTINCT ID FROM {schema}.LOOKUP_PROGRAM'''
        valid_program_ids = pd.read_sql(query, conn)['ID'].to_list()

        tmpdf = self.df.copy()
        tmpdf['PROGRAM_ID'] = (
                                    tmpdf['PROGRAM_CODE'].astype(str) + '_' +
                                    tmpdf['POM_SPONSOR_CODE'].astype(str) + '_' +
                                    tmpdf['CAPABILITY_SPONSOR_CODE'].astype(str) + '_' +
                                    tmpdf['ASSESSMENT_AREA_CODE'].astype(str) + '_' +
                                    tmpdf['EXECUTION_MANAGER_CODE'].astype(str) + '_' +
                                    tmpdf['RESOURCE_CATEGORY_CODE'].astype(str) + '_' +
                                    tmpdf['EOC_CODE'].astype(str) + '_' +
                                    tmpdf['OSD_PROGRAM_ELEMENT_CODE'].astype(str)
                            )

        if not tmpdf['PROGRAM_ID'].isin(valid_program_ids).all():
            invalid_program_ids = tmpdf.loc[~tmpdf['PROGRAM_ID'].isin(valid_program_ids), 'PROGRAM_ID'].to_list()
            if len(invalid_program_ids) > 8:
                raise ExcelValidationError(f'number of invalid program ids found: [{len(invalid_program_ids)}]')
            else:
                invalid_program_ids_str = ', '.join(invalid_program_ids)
                raise ExcelValidationError(f'invalid program ids found: [{invalid_program_ids_str}]')

        
        duplicated_ids = tmpdf[tmpdf.duplicated(subset=['PROGRAM_ID'], keep=False)]['PROGRAM_ID'].unique().tolist()
        if duplicated_ids:
            if len(duplicated_ids) > 8:
                raise ExcelValidationError(f'{len(duplicated_ids)} duplicated PROGRAM_IDs found in input file.')
            else:
                dup_str = ', '.join(duplicated_ids)
                raise ExcelValidationError(f'duplicated PROGRAM_IDs found in file: [{dup_str}]')

    def validate_cols_not_negative(self):
        # assume validate_integer has not necesarily been passed, that is, recheck for integer status
        base_err_msg = 'during check of non-negative values'
        
        temp_df = self.df.copy()

        non_neg_cols = ['RESOURCE_K', 'WORK_YEARS'] if self.file_source != "POM" else ["RESOURCE_K"]
    
        missing_cols = []
        for non_neg_col in non_neg_cols:
            if non_neg_col not in temp_df.columns:
                missing_cols.append(non_neg_col)
                
        if missing_cols:
            missing_cols_str = ', '.join(missing_cols)
            raise ExcelValidationError(f'{base_err_msg}, discovered [{missing_cols_str}] columns DNE!')
        
        non_int_cols = []
        for non_neg_col in non_neg_cols:
            try:
                temp_df[non_neg_col] = temp_df[non_neg_col].astype(int)
            except ValueError as e:
                non_int_cols.append(non_neg_col)
                
        if non_int_cols:
            non_int_cols_str = ', '.join(non_int_cols)
            raise ExcelValidationError(f'{base_err_msg}, discovered not all values of columns [{non_int_cols_str}] can be integer type!')
        

        neg_cols = []
        for non_neg_col in non_neg_cols:
            if not temp_df[non_neg_col].ge(0).all():
                neg_cols.append(non_neg_col)

        if neg_cols:
            neg_cols_str = ', '.join(neg_cols)
            raise ExcelValidationError(f'{base_err_msg}, discovered negative values in columns!: [{neg_cols_str}]')

    def validate_valid_resource_cat_code(self, schema, conn):
        # read LOOKUP_RESOURCE_CATEGORY
        # check all values of input column RESOURCE_CATEGORY_CODE exist in LOOKUP_RESOURCE_CATEGORY
        query = f'''SELECT DISTINCT RESOURCE_CATEGORY_CODE FROM {schema}.LOOKUP_RESOURCE_CATEGORY'''
        valid_resource_categories = pd.read_sql(query, conn)['RESOURCE_CATEGORY_CODE'].to_list()
        if not self.df['RESOURCE_CATEGORY_CODE'].isin(valid_resource_categories).all():
            invalid_resource_categories = self.df.loc[~self.df['RESOURCE_CATEGORY_CODE'].isin(valid_resource_categories), 'RESOURCE_CATEGORY_CODE'].to_list()
            invalid_resource_categories_str = ', '.join(invalid_resource_categories)
            raise ExcelValidationError(f'invalid resource categories found: [{invalid_resource_categories_str}]')
        

    def validate_fiscal_year(self, schema, conn):
        # check year is four characters in string format
        # POM Year is min, POM Year + 4 is max.
        query = f'''SELECT MAX(POM_YEAR) AS POM_YEAR FROM {schema}.USR_LOOKUP_POM_POSITION WHERE IS_ACTIVE=1'''
        curr_fy = int(pd.read_sql(query, conn)['POM_YEAR'].to_list()[0])
        fy_range = range(curr_fy, curr_fy+5)
        temp_df = self.df[~self.df['FISCAL_YEAR'].isin(fy_range)]
        num_bad_rows = len(temp_df)
        if num_bad_rows > 0:
            error_output = str(temp_df['FISCAL_YEAR'].value_counts().to_dict())
            raise ExcelValidationError(f'found out of range fiscal years! number of rows: {num_bad_rows}, count of offending FYs: {error_output}')

    def run_all_validators(self, errors, schema, conn):
        try:
            for method, method_desc in [
                (lambda: self.validate_columns(), 'Required Columns'),
                (lambda: self.validate_non_empty(), 'Non-Empty Non-Nullables'),
                (lambda: self.validate_unique(), 'No Duplicate Column Subsets'),
                (lambda: self.validate_varchar_constraints(), 'VARCHAR Constraints'),
                (lambda: self.validate_integer(), 'Integer Typing'),
                (lambda: self.validate_program_code(schema, conn), 'Program Code Exists'),
                (lambda: self.validate_cols_not_negative(), 'Resource K, Work Years - Not Negative'),
                (lambda: self.validate_valid_resource_cat_code(schema, conn), 'Resource Category Code Exists'),
                (lambda: self.validate_fiscal_year(schema, conn), 'Fiscal Year'),
            ]:
                try:
                    method()
                except ExcelValidationError as e:
                    error_format = f'ERROR <{method_desc}>: {e}'
                    errors.append(error_format)
                except Exception as e:
                    if self.debug:
                        traceback.print_exc()
                    else:
                        error_format = f'ERROR <{method_desc}>: likely because of an earlier failure: {e}'
                        errors.append(error_format)

            if errors:
                error_message = '\n'.join(f"    {i+1}. {err}" for i, err in enumerate(errors))
                raise ExcelValidationError(error_message)

        except ExcelValidationError as e:
            print(e)


class CsvValidator(ExcelValidator):
    def __init__(self, df, file_source, debug=False):
        super().__init__(df,file_source,debug)


def get_job_rows(conn):
    """
    get the files to be processed for DT_ISS, DT_ZBT, DT_EXT, DT_POM
    """
    try:
        df = []
        sql = f"""
            SELECT
                s.ID, u.ID as FILE_ID, meta.TABLE_NAME as TABLE_NAME, s.TYPE, u.FILE_STATUS,
                u.S3_PATH, u.FILE_NAME, u.VERSION, u.TITLE, u.DESCRIPTION,
                u.USER_ID, s.CRON_STATUS, s.CRON_PROCESSED, s.ERRORS
            FROM {SCHEMA_SOCOM_UI}.USR_DT_UPLOADS u
            JOIN {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER_MAP m ON m.MAP_ID = u.ID
            JOIN {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER s ON s.ID = m.DT_SCHEDULER_ID
            JOIN {SCHEMA_SOCOM_UI}.USR_DT_LOOKUP_METADATA meta on u.ID = meta.USR_DT_UPLOAD_ID	
            WHERE u.`TYPE` = 'PROGRAM_SCORE_UPLOAD'
                AND s.CRON_STATUS = 0 
                AND u.FILE_STATUS = 1;
        """
        df = pd.read_sql(sql, conn.bind)
    except Exception as e:
        print(e,f'cannot read {sql}')
        write_log(e)
        return None
    return df[["ID","FILE_ID","TYPE",'TABLE_NAME',"S3_PATH"]]


def update_usr_dt_sched_errors(pk,errors,db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET ERRORS = :errors WHERE ID = :id"
    db_conn.execute(text(query), {"errors": json.dumps(errors), "id": pk})
    db_conn.commit()  # Commit changes

def update_cron_status(pk,cron_status,db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET CRON_STATUS = :cron_status WHERE ID = :id"
    db_conn.execute(text(query), {"cron_status": cron_status, "id": pk})
    db_conn.commit()

def update_cron_processed(pk,cron_processed,db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET CRON_PROCESSED = :cron_processed WHERE ID = :id"
    db_conn.execute(text(query), {"cron_processed": cron_processed, "id": pk})
    db_conn.commit()    


def parse_header_columns(df):
    df.columns = [re.sub(r'[^\x00-\x7f]', r'', x) for x in list(df.columns)]
    df.columns = [re.sub(r'[ \s]', r'_', x) for x in list(df.columns)]
    df.columns = [re.sub(r'\$', r'', x) for x in list(df.columns)]
    df.columns = [x.upper() for x in list(df.columns)]
    return df


def validate_files(s3_client,df,conn):

    for index,row in df.iterrows():
        errors = []
        file_path = row.S3_PATH
        id = row.ID
        file_id = row.FILE_ID
        file_source = row.TABLE_NAME.split("_")[1] #EXT, ISS, POM, ect...
        #full_s3_path = "s3://" + BUCKET + "/" + file_path
        cron_processed = 1 #default, success
        
        try:
            print("Attempting to fetch file from S3...")

            file = s3_client.get_object(Bucket=BUCKET, Key=file_path)

            if file_path and ".csv" in file_path:
                df_file = pd.read_csv(BytesIO(file["Body"].read()))
                df_file = parse_header_columns(df_file)
                validator = CsvValidator(df_file,file_source)

            elif file_path and ".xlsx" in file_path:
                df_file = pd.read_excel(BytesIO(file["Body"].read()))
                breakpoint()
                df_file = parse_header_columns(df_file)
                validator = ExcelValidator(df_file,file_source)
        except Exception as e:
            write_log(e)
            update_usr_dt_sched_errors(pk=id,errors=repr(e),db_conn=conn) #convert error class to string
            cron_processed = -1
        
        if cron_processed != -1: #pass the input reads
            validator.run_all_validators(errors,SCHEMA_SOCOM_UI, conn.bind)

        if errors and cron_processed != -1:
            update_usr_dt_sched_errors(id,errors,conn)  
            cron_processed = -2
        
        elif cron_processed == 1:
            print(" Validation passed.")
            
        update_cron_processed(id,cron_processed,conn) #-1,0,2
        update_cron_status(id,cron_status=1,db_conn=conn)
        
    return df

if __name__ == '__main__':
    # sys.path.append(os.path.expanduser('~/_tools'))
    from dotenv import load_dotenv
    load_dotenv('/home/ec2-user/socom_python/upload_pipelines/.env',  override=True) # original: /.env
    
    from api.internal.conn import SCHEMA_SOCOM_UI
    from api.internal.conn import get_socom_session
 

    use_minio = os.getenv("USE_MINIO", "0") == "1"
    setting = SoComMinioSetting() if use_minio else SoComAWSSetting()
    s3_client = get_s3_client(setting)

    BUCKET = os.getenv("SOCOM_S3_BUCKET")
    session = next(get_socom_session())
  
    df = get_job_rows(session)
    validate_files(s3_client, df, session)