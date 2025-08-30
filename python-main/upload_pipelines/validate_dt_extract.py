import os
import pandas as pd
import numpy as np
import sys
import json
import traceback
from collections import defaultdict

from sqlalchemy.sql import text

sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))



from upload_pipelines.lib.utils import (
    write_log,

)

from upload_pipelines.validate_dt_base import (
    ExcelValidator,
    ExcelValidationError,
    CsvValidationError,
    update_usr_dt_sched_errors,
    update_usr_dt_sched_warnings,
    update_cron_status,
    update_cron_processed,
    parse_header_columns,
    get_file_stream
)

from lib.conn import (
    SoComMinioSetting,
    SoComAWSSetting,
    get_s3_client,
)


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
            WHERE u.`TYPE` = 'DT_UPLOAD_EXTRACT_UPLOAD'
                AND s.CRON_STATUS = 0 
                AND u.FILE_STATUS = 1;
        """
        df = pd.read_sql(sql, conn.bind)
    except Exception as e:
        print(e,f'cannot read {sql}')
        write_log(e)
        return None
    return df[["ID","USER_ID","FILE_ID","TYPE",'TABLE_NAME',"S3_PATH"]]


def update_usr_dt_sched_errors(pk,errors,db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET ERRORS = :errors WHERE ID = :id"
    db_conn.execute(text(query), {"errors": json.dumps(errors), "id": pk})
    db_conn.commit()  # Commit changes


# New function to update warnings
def update_usr_dt_sched_warnings(pk, warnings, db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET WARNINGS = :warnings WHERE ID = :id"
    db_conn.execute(text(query), {"warnings": json.dumps(warnings), "id": pk})
    db_conn.commit()

def update_cron_status(pk,cron_status,db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET CRON_STATUS = :cron_status WHERE ID = :id"
    db_conn.execute(text(query), {"cron_status": cron_status, "id": pk})
    db_conn.commit()

def update_cron_processed(pk,cron_processed,db_conn):
    query = f"UPDATE {SCHEMA_SOCOM_UI}.USR_DT_SCHEDULER SET CRON_PROCESSED = :cron_processed WHERE ID = :id"
    db_conn.execute(text(query), {"cron_processed": cron_processed, "id": pk})
    db_conn.commit()    


def transform_budget_extract_simple4(df: pd.DataFrame, created_by=0, updated_by=0) -> pd.DataFrame:
    static_columns = [
        "Event Number", "Event Type", "Event Title", "Event Justification",
        "Assessment Area Code", "POM Sponsor Code", "Program Group", "Program Code",
        "EOC Code", "Special Project Code", "OSD Program Element Code",
        "Budget Activity Code", "Budget Activity Name", "SAG Code", "SAG Name",
        "Budget Subactivity Name", "Execution Manager", "Capability Sponsor",
        "Line Item", "RDTE Project", "Resource Category Code"
    ]

    suffix_map = {
        "base": "RESOURCE_K",
        "prop": "PROP_AMT",
        "delta": "DELTA_AMT",
        "o2b": "O2B_AMT",
        "prop o2b": "PROP_O2B_AMT",
        "delta o2b": "DELTA_O2B_AMT"
    }

    df1 = df.copy()
    df1["KEY"] = df1[static_columns].astype(str).agg('|'.join, axis=1)
    dynamic_cols = [col for col in df1.columns if col not in static_columns and col != "KEY"]

    output_rows = []

    for i in range(len(df1)):
        row = df1.iloc[i]
        static_data = row[static_columns].to_dict()

        # Group funding columns by fiscal year
        year_data = defaultdict(dict)

        for col in dynamic_cols:
            if pd.isna(row[col]):
                continue

            try:
                year, fund_type = col.strip().split(" ", 1)
                fund_type = fund_type.lower()
                if fund_type in suffix_map:
                    suffix_col = suffix_map[fund_type]
                    year_data[year][suffix_col] = row[col]
            except ValueError:
                continue  # Skip malformed columns

        for year, values in year_data.items():
            new_row = {
                **static_data,
                "FISCAL_YEAR": int(year),
                "CREATED_BY": created_by,
                "UPDATED_BY": updated_by
            }
            new_row.update(values)
            output_rows.append(new_row)

    output_df = pd.DataFrame(output_rows)
    dynamic_col_refined = list(suffix_map.values())
    output_df[dynamic_col_refined] = output_df[dynamic_col_refined].fillna(0.0)
    return output_df



def pre_upsert_process(df,user_id,pom_position=None):
    df = transform_budget_extract_simple4(df, created_by=user_id, updated_by=None)
    mapper = {
     'Event Number':"EVENT_NUMBER",
        'Event Type':"EVENT_TYPE", 
        'Event Title':"EVENT_TITLE", 
        'Event Justification':"EVENT_JUSTIFICATION",
       'Assessment Area Code':"ASSESSMENT_AREA_CODE", 
        'POM Sponsor Code':"POM_SPONSOR_CODE", 
        'Program Group':"PROGRAM_GROUP",
       'Program Code':"PROGRAM_CODE", 
        'EOC Code':"EOC_CODE", 
        'Special Project Code':"SPECIAL_PROJECT_CODE",
       'OSD Program Element Code':"OSD_PROGRAM_ELEMENT_CODE", 
        'Budget Activity Code':"BUDGET_ACTIVITY_CODE",
       'Budget Activity Name':"BUDGET_ACTIVITY_NAME", 
        'SAG Code':"SUB_ACTIVITY_GROUP_CODE", 
        'SAG Name':"SUB_ACTIVITY_GROUP_NAME",
       'Budget Subactivity Name':"BUDGET_SUB_ACTIVITY_NAME", 
        'Execution Manager':"EXECUTION_MANAGER_CODE", 
        'Capability Sponsor':"CAPABILITY_SPONSOR_CODE",
       'Line Item':"LINE_ITEM_CODE", 
        'RDTE Project':"RDTE_PROJECT_CODE", 
        'Resource Category Code':"RESOURCE_CATEGORY_CODE"
    }
    df.rename(columns=mapper,inplace=True)
    df["CREATED_BY"] = 1
    cols_to_fill = ['RESOURCE_K', 'PROP_AMT', 'DELTA_AMT',
                'O2B_AMT', 'PROP_O2B_AMT', 'DELTA_O2B_AMT']

    df[cols_to_fill] = df[cols_to_fill].fillna(0)
    df["EVENT_NAME"] = df.apply(
        lambda row: f"{row['CAPABILITY_SPONSOR_CODE']} {row['EVENT_TYPE']} {row['EVENT_NUMBER']}",
        axis=1
    )
    #add default columns
    default_map = {"BUDGET_SUB_ACTIVITY_CODE":None, 
        "DELTA_OCO_AMT":None, 
        "EVENT_DATE": None, 
        "EVENT_STATUS":None, "EVENT_STATUS_COMMENT":None, 
        "EVENT_USER":None, "OCO_AMT":0, "POM_POSITION_CODE":pom_position, "PROP_OCO_AMT":0,
        "CREATED_BY":user_id,}
    for col,val in default_map.items():
        df[col] = val
    return df


class ExcelValidatorExtend(ExcelValidator):
    def validate_program_id(self, schema, conn):
        from api.internal.utils import generate_hash_pid
        # read LOOKUP_PROGRAM_DETAIL for distinct ID (PROGRAM_ID)
        # construct PROGRAM_ID={PROGRAM_CODE}_{POM_SPONSOR_CODE}_{CAPABILITY_SPONSOR_CODE}_{ASSESSMENT_AREA_CODE}
        # check if all constructed PROGRAM_ID from the input files exist in LOOKUP
        # query = f'''SELECT DISTINCT ID FROM {schema}.LOOKUP_PROGRAM WHERE EVENT_NAME IS NOT NULL;'''
        query = f'''SELECT DISTINCT ID FROM {schema}.LOOKUP_PROGRAM;'''
        valid_program_ids = pd.read_sql(query, conn)['ID'].to_list()
        
        tmpdf = self.df.copy()
        tmpdf['PROGRAM_ID'] = (
                                    tmpdf['PROGRAM_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['POM_SPONSOR_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['CAPABILITY_SPONSOR_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['ASSESSMENT_AREA_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['EXECUTION_MANAGER_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['RESOURCE_CATEGORY_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['EOC_CODE'].astype(str).str.strip() + '_' +
                                    tmpdf['OSD_PROGRAM_ELEMENT_CODE'].astype(str).str.strip()
                                    # tmpdf['OSD_PROGRAM_ELEMENT_CODE'].astype(str).str.strip() + '_' +
                                    # tmpdf['EVENT_NAME'].astype(str).str.strip()
                            )
        
        tmpdf["PROGRAM_ID_HASHED"] = tmpdf["PROGRAM_ID"].apply(generate_hash_pid)
        
        if not tmpdf['PROGRAM_ID_HASHED'].isin(valid_program_ids).all():
            invalid_program_ids = tmpdf.loc[~tmpdf['PROGRAM_ID_HASHED'].isin(valid_program_ids), 'PROGRAM_ID'].to_list()
            if len(invalid_program_ids) > 8:
                raise ExcelValidationError(f'number of invalid program ids found: [{len(invalid_program_ids)}], samples:{",".join(list(set(invalid_program_ids))[:10])}')
            else:
                invalid_program_ids_str = ', '.join(invalid_program_ids)
                raise ExcelValidationError(f'invalid program ids found: [{invalid_program_ids_str}]')
            
    def run_all_validators(self, errors, file_id, schema, conn):
        warnings = []
        try:
            for method, method_desc in [
                (lambda: self.validate_columns(), 'Required Columns'),
                (lambda: self.validate_non_empty(), 'Non-Empty Non-Nullables'),
                (lambda: self.validate_varchar_constraints(), 'VARCHAR Constraints'),
                (lambda: self.validate_integer(), 'Integer Typing'),
                (lambda: self.validate_program_id(schema, conn), 'Program Ids'),
                (lambda: self.validate_fiscal_year(file_id,schema, conn), 'Fiscal Year'),
                (lambda: self.validate_unique(), 'Unique Row') #each row defined in the excel has no dup
            ]:
                try:
                    method()
                except ExcelValidationError as e:
                    error_format = f'ERROR <{method_desc}>: {e}'
                    warning_format = f'WARNING <{method_desc}>: {e}'
                    if method_desc in["Required Columns","Integer Typing","Fiscal Year","Unique Row"]:
                        errors.append(error_format)
                    else:
                        warnings.append(warning_format)
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
        
        return warnings


class CsvValidatorExtend(ExcelValidatorExtend):
    pass

def validate_files(s3_client,df,conn):
    for index,row in df.iterrows():
        warnings = []
        errors = []
        file_path = row.S3_PATH
        id = row.ID
        file_id = row.FILE_ID
        user_id = row.USER_ID
        file_source = "EXTRACT" #fixed, same format
        #full_s3_path = "s3://" + BUCKET + "/" + file_path
        cron_processed = 1 #default, success
        try:
            file_stream = get_file_stream(s3_client, BUCKET, file_path)

            if file_path and ".csv" in file_path:
                df_file = pd.read_csv(file_stream)
                # df_file = parse_header_columns(df_file)
                df_file = pre_upsert_process(df_file,user_id)
                validator = CsvValidatorExtend(df_file,file_source)   

            elif file_path and ".xlsx" in file_path:
                df_file = pd.read_excel(file_stream)
                # df_file = parse_header_columns(df_file)
                df_file = pre_upsert_process(df_file,user_id)            
                validator = ExcelValidatorExtend(df_file,file_source)
        except Exception as e:
            write_log(e)
            update_usr_dt_sched_errors(pk=id,errors=repr(e),db_conn=conn) #convert error class to string
            cron_processed = -1
        
        if cron_processed != -1: #pass the input reads
            #note: errors will have all the warnings in there
            warnings = validator.run_all_validators(errors,file_id,SCHEMA_SOCOM_UI, conn.bind)

        if errors and cron_processed != -1:
            update_usr_dt_sched_errors(id,errors,conn)  
            cron_processed = -2
        if warnings and cron_processed != -1:
            update_usr_dt_sched_warnings(id,warnings,conn)
        
        update_cron_processed(id,cron_processed,conn) #-1,0,2
        update_cron_status(id,cron_status=1,db_conn=conn)

        # if cront_status== 1: #success, parse data
        
    return df

if __name__ == '__main__':
    # sys.path.append(os.path.expanduser('~/_tools'))
    from dotenv import load_dotenv
    load_dotenv('/.env',  override=True)
    
    from api.internal.conn import (
        SCHEMA_SOCOM_UI,
        get_socom_session
    )



    use_minio = os.getenv("USE_MINIO", "0") == "1"
    
    setting = SoComMinioSetting() if use_minio else SoComAWSSetting()
    # CHANGE HERE FOR LOCAL TESTING OF MINIO
    # setting.local_mode() #only applicable for local

    s3_client = get_s3_client(setting)

    BUCKET = os.getenv("SOCOM_S3_BUCKET")
    session = next(get_socom_session())
    df = get_job_rows(session)
    validate_files(s3_client, df, session)