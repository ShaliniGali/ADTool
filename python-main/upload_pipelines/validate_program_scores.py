import os
import json
import pandas as pd
from io import BytesIO
from sqlalchemy import text
from dotenv import load_dotenv
from pathlib import Path
import sys
import traceback
from minio import Minio

from sqlalchemy.orm import Session
from sqlalchemy import text

current_dir = Path(__file__).resolve().parent
parent_dir = current_dir.parent  
lib_path = parent_dir

sys.path.append(str(lib_path))

from lib.conn import (
    SoComMinioSetting,
    SoComAWSSetting,
    get_s3_client,
)

from utils.logger import get_logger, logging
from lib.utils import ErrorLogger

logger = get_logger('program_score_upload')

env_path = "/.env"
env = load_dotenv(dotenv_path=env_path)
if not env:
    print("not found /.env in root directory, trying to read ./.env")
    load_dotenv('./.env',  override=True)

schema = os.getenv('SOCOM_UI', 'SOCOM_UI')

class ScoreUploadValidator:
    @classmethod
    def validate_scores(cls, df):
        df.columns = df.columns.str.strip()
        
        excluded_columns = ['Weighting Criteria', 'Title', 'Description']
        
        for column in df.columns:
            if column in excluded_columns:
                continue

            try:
                invalid_values = pd.to_numeric(df[column], errors='coerce').between(1, 100, inclusive='both')
                if not invalid_values.all():
                    return False
            except Exception as e:
                print(f"Error validating column '{column}': {e}")
                return False
        
        return True

    @classmethod
    def validate_criterias(cls, session, row_id, df, weighting_terms):
        
        desc_cols = ['title', 'description','weighting criteria']
        df_columns = [col.lower() for col in df.columns if col.lower() not in desc_cols]
        ref_cols = [col.lower() for col in weighting_terms]
        missing_columns = [col for col in ref_cols if col not in df_columns]

        if missing_columns:
            update_cron_processed(db=session, cron_processed=-2, id=row_id, error=f'Validator could not validate criteria terms: {missing_columns}')
            print(f"Validation failed: Required columns missing: {missing_columns}")
            return False
        
        return True

def fetch_s3_path_from_db(session):
    df = pd.read_sql(f"""
        SELECT
            s.ID, u.ID as FILE_ID, u.TYPE, u.FILE_STATUS,
            u.S3_PATH, u.FILE_NAME, u.VERSION, u.TITLE, u.DESCRIPTION, 
            u.USER_ID, s.CRON_STATUS, s.CRON_PROCESSED, s.ERRORS
        FROM {schema}.USR_DT_UPLOADS u
        JOIN {schema}.USR_DT_SCHEDULER_MAP m ON m.MAP_ID = u.ID
        JOIN {schema}.USR_DT_SCHEDULER s ON s.ID = m.DT_SCHEDULER_ID
        JOIN {schema}.USR_LOOKUP_CYCLES c ON c.ID = s.CYCLE_ID
        WHERE c.IS_ACTIVE = 1 AND s.TYPE = 'PROGRAM_SCORE_UPLOAD' AND s.CRON_STATUS = 0 AND u.FILE_STATUS = 1
        ORDER BY u.CREATED_TIMESTAMP DESC;
        
        """,
        con=session.bind
    )

    return df

def fetch_program_ids_set(session,type_of_coa):
    assert type_of_coa in ["IO","RC"], "type of coa needs to be either 'IO' or 'RC'."
    if type_of_coa == "IO":
        cond = "WHERE EVENT_NAME IS NOT NULL"
    else:
        cond = "WHERE EVENT_NAME IS NULL"

    df = pd.read_sql(f"""
        SELECT DISTINCT ID FROM {schema}.LOOKUP_PROGRAM {cond};
    """,
    con=session.bind)
    lst = df["ID"].tolist()
    return set(lst)

def handle_usr_option_scores(id_value, session, user_id, program_id, criteria_name_id, name, description, session_data):
    
    user_id = int(user_id)
    criteria_name_id = int(criteria_name_id)
    prog_ids_rc = fetch_program_ids_set(session,'RC')
    prog_ids_io = fetch_program_ids_set(session,'IO')
    
    assert program_id in prog_ids_rc or program_id in prog_ids_io, "Error: program id is not found within the LOOKUP_PROGRAM tables"

    if program_id in prog_ids_rc:
        type_of_coa = "RC_T"
    else:
        type_of_coa = "ISS_EXTRACT"


    logger.log(logging.INFO, 'Saving Score data for user %s, program_id %s and criteria_name_id %s' % (user_id, program_id, criteria_name_id))
               
    check_query = f"""
        SELECT COUNT(*) as count FROM {schema}.USR_OPTION_SCORES
        WHERE CRITERIA_NAME_ID = :criteria_name_id AND PROGRAM_ID = :program_id AND USER_ID = :user_id AND TYPE_OF_COA = :type_of_coa
    """
    result = session.execute(text(check_query), {'user_id': user_id, 'program_id': program_id, 'criteria_name_id': criteria_name_id, 'type_of_coa':type_of_coa})
    row = dict(result.fetchone()._mapping.items())
    
    
    if result and row['count'] > 0:
        logger.log(logging.INFO, 'Found score data of %s existing for user, program and cycle' % (row['count']))

        update_history_query = f"""
        INSERT INTO {schema}.USR_OPTION_SCORES_HISTORY (SCORE_ID, CRITERIA_NAME_ID, NAME, DESCRIPTION, SESSION, PROGRAM_ID, TYPE_OF_COA, USER_ID, DELETED, CREATED_TIMESTAMP, UPDATED_TIMESTAMP, HISTORY_DATETIME)
        SELECT ID, CRITERIA_NAME_ID, NAME, DESCRIPTION, SESSION, PROGRAM_ID, TYPE_OF_COA, USER_ID, DELETED, CREATED_TIMESTAMP, UPDATED_TIMESTAMP, NOW()
        FROM {schema}.USR_OPTION_SCORES
        WHERE USER_ID = :user_id AND PROGRAM_ID = :program_id AND CRITERIA_NAME_ID = :criteria_name_id AND TYPE_OF_COA = :type_of_coa;
        """
        result = session.execute(text(update_history_query), {
            'user_id': user_id,
            'program_id': program_id,
            'criteria_name_id': criteria_name_id,
            'type_of_coa':type_of_coa
        })
        if result and result.rowcount > 0:
            logger.log(logging.INFO, 'History saved of existing score for user, program and cycle')

            update_scores_query = f"""
            UPDATE {schema}.USR_OPTION_SCORES
            SET NAME = :name, DESCRIPTION = :description, SESSION = :session_data, DELETED = :deleted, UPDATED_TIMESTAMP = NOW()
            WHERE USER_ID = :user_id AND PROGRAM_ID = :program_id AND CRITERIA_NAME_ID = :criteria_name_id AND TYPE_OF_COA = :type_of_coa;
            """
            result = session.execute(text(update_scores_query), {
                'name': name,
                'description': description,
                'session_data': json.dumps(session_data),
                'deleted': 0,
                'user_id': user_id,
                'program_id': program_id,
                'criteria_name_id': criteria_name_id,
                'type_of_coa':type_of_coa
            })

            logger.log(logging.INFO, 'Updated %s score data for user, program and cycle' % (result.rowcount))

        else:
            update_cron_processed(db=session, cron_processed=-2, id=id_value, error='Saving to history table was not successful')
            logger.log(logging.INFO, 'Saving to history table was not successful')
            os.sys.exit(1)
    else:
        logger.log(logging.INFO, "Adding new score data for user, program %s and criteria_name_id %s, for user %s" % (program_id, criteria_name_id, user_id))

        insert_query = f"""
        INSERT INTO {schema}.USR_OPTION_SCORES (CRITERIA_NAME_ID, NAME, DESCRIPTION, SESSION, PROGRAM_ID, TYPE_OF_COA, USER_ID, DELETED, CREATED_TIMESTAMP, UPDATED_TIMESTAMP)
        VALUES (:criteria_name_id, :name, :description, :session_data, :program_id, :type_of_coa, :user_id, :deleted, NOW(), NOW());
        """


        result = session.execute(text(insert_query), {
            'criteria_name_id': criteria_name_id,
            'name': name,
            'description': description,
            'session_data': json.dumps(session_data),
            'program_id': program_id,
            'user_id': user_id,
            'deleted': 0,
            'type_of_coa':type_of_coa
        })

        logger.log(logging.INFO, 'Saved %s score data for user, program and cycle' % (result.rowcount))

    return result.rowcount > 0
    

def update_cron_status(db: Session, cron_status: int, id: int):
    if cron_status > 1 or cron_status < -1:
        raise ValueError("Invalid file status provided")
    
    update_status_query = f"""
    UPDATE {schema}.USR_DT_SCHEDULER
    SET CRON_STATUS = :cron_status
    WHERE ID = :id AND TYPE = 'PROGRAM_SCORE_UPLOAD';
    """
    db.execute(text(update_status_query), {'cron_status': cron_status, 'id': id})
    db.commit()

def update_cron_processed(db: Session, cron_processed: int, id: int, error: str):
    if cron_processed > 1 or cron_processed < -3:
        raise ValueError("Invalid file status provided")
    
    update_processed_query = f"""
    UPDATE {schema}.USR_DT_SCHEDULER
    SET CRON_PROCESSED = :cron_processed_status,
    ERRORS = :error
    WHERE ID = :id AND TYPE = 'PROGRAM_SCORE_UPLOAD'; 
    """
    db.execute(text(update_processed_query), {'cron_processed_status': cron_processed, 'id': id, 'error': error})
    db.commit()

def get_criteria_name_id(session):
    query = f"""
    SELECT cn.ID AS CRITERIA_NAME_ID FROM {schema}.USR_LOOKUP_USER_CRITERIA_NAME cn 
    JOIN {schema}.USR_LOOKUP_CYCLES c ON c.ID = cn.CYCLE_ID WHERE c.IS_ACTIVE = 1;
    """
    data_result = session.execute(query)

    row = dict(data_result.fetchone()._mapping.items())
    
    return row['CRITERIA_NAME_ID'] if data_result.rowcount > 0 else False

def get_weighting_terms(session):
    #dynamically retrieve the active criteria terms
    query = f"""
        SELECT CRITERIA_TERM FROM {schema}.USR_LOOKUP_USER_CRITERIA_TERMS t JOIN 
        {schema}.USR_LOOKUP_USER_CRITERIA_NAME n ON n.ID = t.CRITERIA_NAME_ID 
        JOIN {schema}.USR_LOOKUP_CYCLES c ON c.ID = n.CYCLE_ID WHERE c.IS_ACTIVE=1;
    """
    data = session.execute(text(query)).fetchall()
    return sorted([row[0].strip().upper() for row in data])

def fetch_s3_data(s3_client, s3_paths, session, row_id):
    s3_data_frames = []
    for s3_path in s3_paths:
        try:
            if isinstance(s3_client, Minio):
                response = s3_client.get_object(bucket_name=os.environ.get('SOCOM_S3_BUCKET'), object_name=s3_path)
                bytesobj = BytesIO(response.read()) 

            else:
                response = s3_client.get_object(Bucket=os.environ.get('SOCOM_S3_BUCKET'), Key=s3_path)
                bytesobj = BytesIO(response['Body'].read())

            if s3_path.endswith('.xlsx'):
                s3_df = pd.read_excel(bytesobj)
            elif s3_path.endswith('.csv'):
                s3_df = pd.read_csv(bytesobj, encoding='ISO-8859-1')
            else:
                update_cron_processed(db=session, cron_processed=-2, id=row_id, error='file not ending with .csv or .xlsx')
                os.sys.exit(2)

            s3_data_frames.append(s3_df)
            
        except Exception as e:
            logger.log(logging.INFO, f"An error occurred while processing {s3_path}: {e}")
            os.sys.exit(2)

    return s3_data_frames

def validate_and_update(session, s3_data_frames, row):
    from api.internal.utils import generate_hash_pid
    try:
        s3_df = s3_data_frames[0]
        
        s3_df.columns = s3_df.columns.str.strip()
        
        if 'Weighting Criteria' not in s3_df.columns or s3_df['Weighting Criteria'].isna().sum() > 0:
            update_cron_processed(db=session, cron_processed=-2, id=row['ID'], error="Weighting Criteria cannot be empty.")
            return
        
        for col in ['Title', 'Description']:
            if col not in s3_df.columns:
                s3_df[col] = ""
            else:
                s3_df[col] = s3_df[col].fillna("")
        
        weighting_terms = get_weighting_terms(session) #active terms

        for col in weighting_terms:
            if col in s3_df.columns:
                s3_df[col] = s3_df[col].fillna(1)

        #CHECK IF ALL CRITERIAS EXIST FIRST
        if not ScoreUploadValidator.validate_criterias(session,row['ID'],s3_df,weighting_terms):
            return
        
        if not ScoreUploadValidator.validate_scores(s3_df):
            update_cron_processed(db=session, cron_processed=-2, id=row['ID'], error='Validator could not validate score values')
            return

        criteria_name_id = get_criteria_name_id(session)
        if not criteria_name_id:
            update_cron_processed(db=session, cron_processed=-2, id=row['ID'], error='Uploaded current Criteria linked to the active cycle')
            return
        
        user_id = row['USER_ID']
        
        for _, program_row in s3_df.iterrows():
            program_id = program_row['Weighting Criteria']
            program_id = generate_hash_pid(program_id)   #hash id of unhashed user upload id
            name = program_row.get('Title', "") 
            description = program_row.get('Description', "")

            session_data = program_row.drop(labels=['Weighting Criteria', 'Title', 'Description']).to_dict()
            #row id is the USR_DT_UPLOADS row id
            if handle_usr_option_scores(row['ID'], session, user_id, program_id, criteria_name_id, name, description, session_data) <= 0:
                update_cron_processed(db=session, cron_processed=-2, id=row['ID'], error='Unable to save upload file data to database')
                return

        update_cron_processed(db=session, cron_processed=1, id=row['ID'], error='')

    except Exception as e:
        update_cron_processed(db=session, cron_processed=-2, id=row['ID'], error=f"{e}")
        logger.log(logging.INFO, f"An error occurred during processing ID {row['ID']}: {e}")
        traceback.print_exc()
        os.sys.exit(2)

def check_and_upload(session, s3_client):
    try:
        db_df = fetch_s3_path_from_db(session)
        logger.log(logging.INFO, db_df.columns)
        logger.log(logging.INFO, db_df.shape)
        logger.log(logging.INFO, db_df)

        if db_df.empty:
            logger.log(logging.INFO, "No new file to process...")
            os.sys.exit(0)

        for index, row in db_df.iterrows():
            logger.log(logging.INFO, "ROW ID %s " % (row['ID']))
            update_cron_status(db=session, cron_status=1, id=row['ID'])
            s3_data_frames = fetch_s3_data(s3_client, [row['S3_PATH']], session, row['ID'])

            validate_and_update(session, s3_data_frames, row)

           

    except Exception as e:
        logger.log(logging.INFO, f"An error occurred: {e}")


def main():
    from api.internal.conn import get_socom_session
    use_minio = os.getenv("USE_MINIO", "0") == "1"
    
    setting = SoComMinioSetting() if use_minio else SoComAWSSetting()
    s3_client = get_s3_client(setting)
    check_and_upload(next(get_socom_session()), s3_client)

    os.sys.exit(0)

if __name__ == "__main__":
    main()
