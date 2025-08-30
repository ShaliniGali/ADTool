import os
import requests
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

def get_db_creds():
    db_info = {}
    db_info['Port'] = 3306
    facs_db_api = os.environ.get("FACS_RHOMBUS_DB_API")

    if facs_db_api != None and facs_db_api.lower() == "true":
        ip = requests.get('https://api.ipify.org').text
        key = os.environ.get('FACS_DB_API_KEY')
        flag = os.environ.get('FACS_DB_API_FLAG')
        url = 'https://app.guardian.rhombus.cloud/Api/db_info'
        payload = {"server_ip": ip, "key":key, "db_name":flag}
        r = requests.post(url, data=payload)
    
        coninfo = [c for c in r.json() if c['db_name']==flag][0]
        db_info['User'] = coninfo['user_name']
        db_info['Host'] = coninfo['host_name']
        db_info['Pass'] = coninfo['password'] 
    else:
        db_info['User'] = os.environ.get('FACS_DB_USER')
        db_info['Host'] = os.environ.get('FACS_DB_HOST')
        db_info['Pass'] = os.environ.get('FACS_DB_PASS') 
    return db_info

def make_db_conn():
    db = get_db_creds()
    sql_engine          =   create_engine('mysql+mysqlconnector://{}:{}@{}:{}'.format(db["User"],db["Pass"],db["Host"],db["Port"]), connect_args={'use_pure': True, "ssl_disabled": True})
    rhombus_session     =   sessionmaker(bind=sql_engine)
    return {
        'sqlEngine':sql_engine,
        'rhombus_session': rhombus_session
    }

db_conn_info = make_db_conn()