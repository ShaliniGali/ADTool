import time, os
import pandas as pd

from params import api_table_status
from fastapi import HTTPException

from audit import audit_log
db = os.environ.get('FACS_DB_NAME')

    
"""
@Type: feature/subapp/app/role
"""
def find_table(type):
    out = ""
    if type == "feature":
        out = {"table":"feature_info"} 
    elif type == "app":
        out = {"table":"keycloak_tiles"} 
    elif type == "subapp":
        out = {"table":"subapp_info"} 
    elif type == "role":
        out = {"table":"user_roles"}  
    else: 
        out = "type is required"
    return out


def write_sql(select_data, type, op, sql, user_id, value, db_session):
    if op == 'insert':
        if select_data.empty == True:
            try:
                db_session.execute(sql)
                db_session.commit()
                out = "success"
            except Exception:
                db_session.rollback()
                out = "Database error"
                audit_log(user_id, type, sql, value, out, db_session)
                raise HTTPException(400, detail=out)
        else:
            out = type + " already exists"
    elif op == 'delete':
        if select_data.empty == False:
            try:
                db_session.execute(sql)
                db_session.commit()
                out = "success"
            except Exception:
                db_session.rollback()
                out = "Database error"
                audit_log(user_id, type, sql, value, out, db_session)
                raise HTTPException(400, detail=out)
        else:
            out = type + " not exist"
    return out

"""
@Type: feature/subapp/app/role
"""
def insert_info(type=None, value=None, db_session=None, user_id=None):
    out = find_table(type)
    if 'table' in out:
        if value != None and value != "":
            value = str(value)
            select_sql = 'SELECT id  FROM '+db+'.'+out['table']+' where status="'+ api_table_status['active']
            if (type == "feature") or (type == "subapp") or (type == "role"):
                select_data = pd.read_sql(select_sql +'" and Name="'+value+'" LIMIT 1',db_session.bind)
                sql = f"""INSERT INTO {db}.{out['table']} (`Name`,`Status`, `Timestamp`) VALUES ('{value}','{api_table_status["active"]}',{str(int(time.time()))})"""
                out = write_sql(select_data, type, 'insert', sql, user_id, value, db_session)
            if (type == "app"):
                select_data = pd.read_sql(select_sql +'" and title="'+value+'" LIMIT 1',db_session.bind)
                sql = f"""INSERT INTO {db}.{out['table']} (`title`,`status`, `created_on`) VALUES ('{value}','{api_table_status['active']}',{str(int(time.time()))})"""
                out = write_sql(select_data, type, 'insert', sql, user_id, value, db_session)
        else:
            out = "Value is required"
            
        audit_log(user_id, type, f"ingest:insert_info, '{value}','{api_table_status['active']}'", value, out, db_session)
    return {"message":out}

"""
@Type: feature/subapp/app/role
"""
def delete_info(type=None, value=None, db_session=None, user_id=None):
    out = find_table(type)
    if 'table' in out:
        if value != None and value != "":
            value = str(value)
            if (type == "feature") or (type == "subapp") or (type == "role"):
                select_data = pd.read_sql('SELECT id FROM '+db+'.'+out['table']+' where Status="'+ api_table_status['active'] +'" and Name="'+value+'" limit 1',db_session.bind)
                sql = 'UPDATE '+db+'.'+out['table']+f''' SET Status = "{api_table_status['deleted']}" WHERE Name="'''+value+'" and Status="'+ api_table_status['active'] +'"'
                out = write_sql(select_data, type, 'delete', sql, user_id, value, db_session)
            if (type == "app"):
                select_data = pd.read_sql('SELECT id FROM '+db+'.'+out['table']+' where status="'+ api_table_status['active'] +'" and title="'+value+'" limit 1',db_session.bind)
                sql = 'UPDATE '+db+'.'+out['table']+f''' SET Status = "{api_table_status['deleted']}" WHERE title="'''+value+'" and Status="'+ api_table_status['active'] +'"'
                out = write_sql(select_data, type, 'delete', sql, user_id, value, db_session)

        else:
            out = "Value is required"

        audit_log(user_id, type, f"ingest: delete_info, '{value}','{api_table_status['active']}'", value, out, db_session)
    return {"message":out}
