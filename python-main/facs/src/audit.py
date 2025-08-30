
from db_connection import db_conn_info
import datetime, os
from fastapi import HTTPException

audit_table = os.environ.get('FACS_AUDIT_TABLE')
db_name = os.environ.get('FACS_DB_NAME')

def standardize_quotes(text):
    return text.replace('\'', '"')

def audit_log(user_id, type, inputs, value, result, db_session):
    time = datetime.datetime.now()
    try:
        db_session.execute(f"insert into {db_name}.{audit_table} VALUES ('{time}','{user_id}','{standardize_quotes(str(type))}','{standardize_quotes(str(inputs))}','{standardize_quotes(str(value))}','{standardize_quotes(str(result))}')")
        db_session.commit()
    except Exception:
        db_session.rollback()
        out = "Database error"
        raise HTTPException(400, detail=out)




