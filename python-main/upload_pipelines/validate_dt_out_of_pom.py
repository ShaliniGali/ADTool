import os
import pandas as pd
import sys
import json
import traceback

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
            WHERE u.`TYPE` = 'DT_OUT_POM'
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

class ExcelValidatorExtend(ExcelValidator):
    def run_all_validators(self, errors, file_id, subcat, schema, conn):
        warnings = []
        try:
            for method, method_desc in [
                (lambda: self.validate_columns(), 'Required Columns'),
                (lambda: self.validate_non_empty(), 'Non-Empty Non-Nullables'),
                (lambda: self.validate_varchar_constraints(), 'VARCHAR Constraints'),
                (lambda: self.validate_integer(), 'Integer Typing'),
                (lambda: self.validate_program_id(schema, conn), 'Program Ids'),
                (lambda: self.validate_fiscal_year(file_id,schema, conn), 'Fiscal Year'),
            ]:
                try:
                    if method_desc == "Fiscal Year" and subcat != "PB": #we do not validate fiscal years for ACTUALS/ENT
                        continue
                    method()
                except ExcelValidationError as e:
                    error_format = f'ERROR <{method_desc}>: {e}'
                    warning_format = f'WARNING <{method_desc}>: {e}'
                    if method_desc in["Program Ids","Required Columns","Integer Typing","Fiscal Year"]:
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
        file_source = "OUT_OF_POM" #fixed, same format
        subcat = row.TABLE_NAME #'PB','ACTUALS','ENT'
        #full_s3_path = "s3://" + BUCKET + "/" + file_path
        cron_processed = 1 #default, success
        try:
            file_stream = get_file_stream(s3_client, BUCKET, file_path)

            if file_path and ".csv" in file_path:
                df_file = pd.read_csv(file_stream)
                df_file = parse_header_columns(df_file)
                validator = CsvValidatorExtend(df_file,file_source)

            elif file_path and ".xlsx" in file_path:
                df_file = pd.read_excel(file_stream)
                df_file = parse_header_columns(df_file)
                validator = ExcelValidatorExtend(df_file,file_source)
        except Exception as e:
            write_log(e)
            update_usr_dt_sched_errors(pk=id,errors=repr(e),db_conn=conn) #convert error class to string
            cron_processed = -1
        
        if cron_processed != -1: #pass the input reads
            #note: errors will have all the warnings in there
            warnings = validator.run_all_validators(errors,file_id,subcat, SCHEMA_SOCOM_UI, conn.bind)

        if errors and cron_processed != -1:
            update_usr_dt_sched_errors(id,errors,conn)  
            cron_processed = -2
        if warnings and cron_processed != -1:
            update_usr_dt_sched_warnings(id,warnings,conn)
        
        update_cron_processed(id,cron_processed,conn) #-1,0,2
        update_cron_status(id,cron_status=1,db_conn=conn)
        
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