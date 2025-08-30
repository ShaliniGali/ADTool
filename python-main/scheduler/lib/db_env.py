import sys
import os
from dotenv import load_dotenv
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from urllib.parse import quote_plus
from pathlib import Path

current_dir = Path(__file__).resolve().parent
vault_path = current_dir.parent.parent 
sys.path.append(str(vault_path))


load_dotenv()

IS_SOCOM = os.environ.get("VAULT_FLAG", 'TRUE')
print(f"VAULT USING: {IS_SOCOM}")

if IS_SOCOM == 'TRUE':  
    from api.internal.vault import VaultConnector

VAULT_ENV = {
    'SOCOM_UI': {'DB_ALIAS': os.getenv('SOCOM_UI_VAULT_DB_ALIAS'), 'DB_USER': os.getenv('SOCOM_UI_VAULT_DB_USER')}
}

CREDS_DB_ENV = {
    'SOCOM_UI': {'DB_IDX': 0, 'DB_NAME': os.environ.get("DB_NAME", "SOCOM_UI")}
}


def get_cred_vault(db_name):
    vault_cn = VaultConnector(
            db=VAULT_ENV[db_name]['DB_ALIAS'],
            user=VAULT_ENV[db_name]['DB_USER']
    )
    
    creds = vault_cn.get_creds()
    creds.update({'port': '3306'})
    
    return creds


def get_creds_env(db_name):
    creds = {
        'hostname': os.environ.get(f'SOCOM_PRODUCTS_{CREDS_DB_ENV[db_name]["DB_IDX"]}_host'),
        'username': os.environ.get(f'SOCOM_PRODUCTS_{CREDS_DB_ENV[db_name]["DB_IDX"]}_username'),
        'password': os.environ.get(f'SOCOM_PRODUCTS_{CREDS_DB_ENV[db_name]["DB_IDX"]}_password'),
        'dbname': CREDS_DB_ENV[db_name]["DB_NAME"],
        'port': os.environ.get(f'SOCOM_PRODUCTS_{CREDS_DB_ENV[db_name]["DB_IDX"]}_port', '3306')
    }
        
    return creds

def get_creds(db_name):
    try:
        if IS_SOCOM == 'FALSE':
            print("Fetching credentials from environment (Cred method)")

            creds = get_creds_env(db_name)
        else:
            print(f"Fetching credentials from Vault")

            creds = get_cred_vault(db_name)
        
        return creds

    except Exception as e:
        print(f"Error in get_creds: {str(e)}")
        raise e
    
    return creds

def create_db_session(db_name):

    creds = get_creds(db_name)
    
    username = quote_plus(creds["username"])
    password = quote_plus(creds["password"])
    hostname = creds["hostname"]  
    
    connection_string = f'mysql+mysqlconnector://{username}:{password}@{hostname}/{creds["dbname"]}'

    engine = create_engine(connection_string)
    print(f"Attempting to connect to DB")

    Session = sessionmaker(bind=engine)
    print(f"Connected to database {creds['dbname']} successfully!")

    return Session()
