import os
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from urllib.parse import quote_plus
from dotenv import load_dotenv

load_dotenv()

VAULT_FLAG = os.environ.get("VAULT_FLAG", 'TRUE').strip().upper()

VAULT_ENV = {
    'SOCOM_UI': {'DB_ALIAS': os.getenv('SOCOM_UI_VAULT_DB_ALIAS'), 'DB_USER': os.getenv('SOCOM_UI_VAULT_DB_USER')}
}

CREDS_DB_ENV = {
    'SOCOM_UI': {'DB_IDX': 0, 'DB_NAME': os.environ.get("DB_NAME", "SOCOM_UI")}
}

def get_cred_vault(db_name):
    from api.internal.vault import VaultConnector
    
    vault_cn = VaultConnector(
        db=VAULT_ENV[db_name]['DB_ALIAS'],
        user=VAULT_ENV[db_name]['DB_USER']
    )
    
    creds = vault_cn.get_creds()
    creds.update({'port': '3306'})
    
    return creds

def get_creds_env(db_name):
    db_idx = CREDS_DB_ENV[db_name]["DB_IDX"]
    
    creds = {
        'hostname': os.getenv(f'SOCOM_PRODUCTS_{db_idx}_host', 'localhost'),
        'username': os.getenv(f'SOCOM_PRODUCTS_{db_idx}_username', 'root'),
        'password': os.getenv(f'SOCOM_PRODUCTS_{db_idx}_password', ''),
        'dbname': CREDS_DB_ENV[db_name]["DB_NAME"],
        'port': os.getenv(f'SOCOM_PRODUCTS_{db_idx}_port', '3306')
    }
    
    return creds

def get_creds(db_name):
    try:
        if VAULT_FLAG == 'FALSE':
            print("Fetching credentials from environment variables...")
            creds = get_creds_env(db_name)
        else:
            print("Fetching credentials from Vault...")
            creds = get_cred_vault(db_name)
        
        return creds

    except Exception as e:
        print(f"Error in get_creds: {str(e)}")
        raise e

def create_db_session(db_name):
    creds = get_creds(db_name)

    username = quote_plus(creds["username"])
    password = quote_plus(creds["password"])
    hostname = creds["hostname"]
    port = creds["port"]
    
    connection_string = f'mysql+mysqlconnector://{username}:{password}@{hostname}:{port}/{creds["dbname"]}'

    engine = create_engine(connection_string)
    print(f"Attempting to connect to DB: {creds['dbname']} at {hostname}:{port}")

    Session = sessionmaker(bind=engine)
    print(f"Connected to database {creds['dbname']} successfully!")

    return Session()
