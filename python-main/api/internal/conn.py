import os
import time
import random
import datetime

from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

from api.internal.utils import str2bool
from rds.rds_connection import get_rds_conn

SCHEMA_SOCOM_UI = os.environ.get("SOCOM_UI", "SOCOM_UI")

is_testing = str2bool(os.environ.get("SOCOM_PYTHON_API_TESTING", 'FALSE'))
testing_mode_string = "FASTAPI is running as Testing Mode"

use_vault = str2bool(os.environ.get("VAULT_FLAG", 'TRUE'))  # <-- Updated

if use_vault:
    from api.internal.vault import VaultConnector
    dbs = os.getenv('SOCOM_VAULT_DB_ALIAS', '')
    users = os.getenv('SOCOM_VAULT_DB_USER', '')
    db = dbs.split("::::")[-1]
    user = users.split("::::")[-1]
    vault_socom = VaultConnector(
        db=db,
        user=user
    )
else:
    vault_socom = None


def get_cred():
    """get database information from environment variables

    Returns:
        [dict]: Db credentials
    """
    db_info = {}
    db_info['SOCOM_UI'] = {}
    db_info = load_creds_from_env(db_info, db_name='SOCOM_UI', product_idx=0)
    return db_info


def load_creds_from_env(db_info, db_name: str, product_idx: int):
    if db_name.startswith('CREDENTIAL') or db_name == 'SOCOM_UI':
        db_info[db_name]['hostname'] = os.environ.get("CI_PRODUCTS_0_HOST")
        db_info[db_name]['username'] = os.environ.get("CI_PRODUCTS_0_USERNAME")
        db_info[db_name]['password'] = os.environ.get("CI_PRODUCTS_0_PASSWORD")
        db_info[db_name]['port'] = 3306
        return db_info

    db_info[db_name]['hostname'] = os.environ.get(f"SOCOM_PRODUCTS_{product_idx}_host")
    db_info[db_name]['username'] = os.environ.get(f"SOCOM_PRODUCTS_{product_idx}_username")
    db_info[db_name]['password'] = os.environ.get(f"SOCOM_PRODUCTS_{product_idx}_password")
    db_info[db_name]['port'] = 3306
    return db_info


# def load_creds_from_env(db_info, db_name: str, product_idx: int):
#     if db_name.startswith('CREDENTIAL'):
#         db_info[db_name]['hostname'] = os.environ.get("CI_CREDENTIALS_HOST")
#         db_info[db_name]['username'] = os.environ.get("CI_CREDENTIALS_USERNAME")
#         db_info[db_name]['password'] = os.environ.get("CI_CREDENTIALS_PASSWORD")
#         db_info[db_name]['port'] = 3306
#         return db_info

#     db_info[db_name]['hostname'] = os.environ.get(f"SOCOM_PRODUCTS_{product_idx}_host")
#     db_info[db_name]['username'] = os.environ.get(f"SOCOM_PRODUCTS_{product_idx}_username")
#     db_info[db_name]['password'] = os.environ.get(f"SOCOM_PRODUCTS_{product_idx}_password")
#     db_info[db_name]['port'] = 3306
#     return db_info


db_info = get_cred()


def get_engine(db_schema, vault_connector):
    if vault_connector is None:
        conn = get_rds_conn(
            username=db_info[db_schema].get('username'),
            password=db_info[db_schema].get('password'),
            endpoint=db_info[db_schema].get('hostname'),
            port=int(db_info[db_schema].get('port', 3306)),
        )
    else:
        conn = vault_connector.get_db()
    return conn


def get_session(db_schema: str, vault_connector=None):
    session = sessionmaker(bind=get_engine(db_schema, vault_connector))
    return session


def refresh_session(sessionmaker, vault_connector=None):
    if vault_connector is not None:
        leased_created = vault_connector.db_conn['lease_created']
        lease_duration = vault_connector.db_conn['lease_duration']
        if (datetime.datetime.now() - leased_created).total_seconds() > lease_duration:
            print("Getting new DB session")
            sessionmaker.close_all()
            sessionmaker = vault_connector.get_session()
    return sessionmaker


socom_session = get_session('SOCOM_UI', vault_socom)


# Dependency
def get_socom_session():
    global socom_session
    socom_session = refresh_session(socom_session, vault_socom)
    db = socom_session()
    try:
        yield db
    finally:
        db.close()