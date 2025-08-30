import os, sys
from sqlalchemy import text


table_notification = 'USR_LOOKUP_NOTIFICATIONS'

def create_user_notification(conn, schema, table, type, msg, to_user_id, from_user_id=0, is_sys=0, status='info'):
    stmt = text(f'''
                    INSERT INTO {schema}.{table} 
                    (TYPE, MESSAGE, STATUS, IS_SYSTEM, TO_USER_ID, FROM_USER_ID)
                    VALUES(:TYPE, :MSG, :STATUS, :IS_SYS, :TO_USER_ID, :FROM_USER_ID)
                ''')
    
    row = conn.execute(stmt, {"TYPE": type, 
        "MSG": msg, 
        "STATUS": status,
        "IS_SYS": is_sys,
        "TO_USER_ID": to_user_id,
        "FROM_USER_ID": from_user_id})

    row_count = row.rowcount
    
    row.close()

    if row_count == 1:
        print(f'''Created new notification row for TO_USER_ID: {to_user_id}''')
    else:
        print(f'''Unable to create notification row for TO_USER_ID: {to_user_id}, rows updated: {row_count}''')

    return row_count