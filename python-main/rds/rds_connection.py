
# Import packages
import os
from urllib.parse import quote_plus

import pandas as pd
from sqlalchemy import create_engine

from api.internal.utils import str2bool

# ------------------------- Set up RDS connection -------------------------

def get_rds_conn(username: str, password: str, endpoint: str, port: int=3306):
    """ Returns RDS connection using SQLAlchemy
    
    Args:
        username: string
            username for database
        password: String
            password for database
        endpoint: String
            endpoint URL for the database
        port: Integer, default 3306
            port on which MYSQL connection will be established

    Returns:
        (sqlalchemy.engine.base.Engine): MYSQL connection to RDS at requested port

    Note:
        dbname name is only to be used for vault implementation
    """
    
    engine = create_engine(
        f'mysql+mysqlconnector://{username}:{password}@{endpoint}:{port}',
        connect_args={
            'use_pure': True, 
        },
        pool_recycle=1800,
        pool_pre_ping=True,
        pool_size=20,
    )
    return engine


# ------------------------- Fetch Tables -------------------------

def fetch_complete_table(rds_conn, schema, table_name):
    """ Queries complete table from RDS

    Args:
        rds_conn (sqlalchemy.engine.base.Engine): MYSQL connection to RDS
        schema (String): name of schema to query
        table_name (String): name of the table to fetch

    Returns:
        df (pandas.DataFrmae): The pandas DataFrame generated from MYSQL table in RDS
    """
    q = f''' SELECT * FROM {schema}.{table_name} '''
    df = pd.read_sql(q, rds_conn)
    return df

def fetch_partial_table(rds_conn, schema, table_name, columns):
    """ Queries partial table from RDS

    Args:
        rds_conn (sqlalchemy.engine.base.Engine): MYSQL connection to RDS
        schema (String): name of schema to query
        table_name (String): name of the table to fetch
        columns (array): names of specific columns to fetch

    Returns:
        df (pandas.DataFrmae): The pandas DataFrame generated from MYSQL table in RDS
    """
    q = f''' SELECT {', '.join(columns)} FROM {schema}.{table_name} '''
    df = pd.read_sql(q, rds_conn)
    return df