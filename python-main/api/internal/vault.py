import os
import time
import random
from datetime import datetime
from urllib.parse import quote_plus

from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

from vacc.background import (
    CERT_PATH,
    create_vault_proxy_client
)
from vacc.client import VaultClient
from api.internal.utils import str2bool


class VaultConnector:

    def __init__(self, db: str, user: str):
        """ Initialize a Vault instance with specified database and user alias.

        This constructor initializes the Vault instance with the provided database, user parameters 
        and loads required environmental variables.

        Args:
            db (str) : database alias in vault
            user (str): database user alias in vault
        """
        self.db = db
        self.user = user
        self.FF_PROXY = self.is_sipr = str2bool(os.environ.get("SOCOM_PYTHON_RDS", 'FALSE')) # or P1_FLAG
        print(self.FF_PROXY)
        self.make_new_connection()

    def make_new_connection(self):
        """ This method establishes a connection to a MySQL database using the credentials obtained from the get_creds function.
            It creates an SQLAlchemy engine that can be used for database operations.

        Returns:
            sqlalchemy.engine.base.Engine: An SQLAlchemy Engine object representing the database connection.
        """
        conn_time = datetime.now()
        creds = self.get_creds()

        engine = create_engine(
            f"mysql+mysqlconnector://{quote_plus(creds['username'])}:{quote_plus(creds['password'])}@{quote_plus(creds['hostname'])}/{quote_plus(creds['dbname'])}"
        )
        session = sessionmaker(bind=engine)
        self.db_conn = {
            "dbname" : creds['dbname'],
            "engine": engine,
            "session": session,
            "lease_created": conn_time,
            "lease_duration": creds.get("lease_duration"),
        }

    def get_creds(self):
        """Gets the Database credentials from Vault using the environmental variables.

        Returns:
            dict: A dictionary containing the database credentials. The keys are:
            - 'hostname': The hostname and port in the format 'host:port'.
            - 'username': The database username.
            - 'password': The database password.
            - 'dbname': The database name. 
        """
        if (os.getenv("DB") == "RDS"):
            vc = self.get_vault_client()
            tries = 10
            while tries:
                try:
                    creds = vc.get_db_creds(
                        username=os.getenv("VAULT_USERNAME"),
                        db_alias=self.db,
                        db_user=self.user,
                    )
                    return creds
                except ConnectionError:
                    sleeptime = 2**(4-tries) + random.uniform(0.0, 1.0)
                    time.sleep(sleeptime)
                    tries -= 1
            print("Unable to get DB creds from Vault.")
        
        if (os.getenv("DB") == "LOCAL") and os.getenv("CI_PRODUCTS_0_HOST"):
            return  {
                "hostname": os.getenv("CI_PRODUCTS_0_HOST"),
                "username": os.getenv("CI_PRODUCTS_0_USERNAME"),
                "password": os.getenv("CI_PRODUCTS_0_PASSWORD"),
                "dbname": os.getenv("CI_PRODUCTS_0_DATABASE", "socom_ui"),
            }

    def get_vault_client(self):
        
        if self.FF_PROXY:
            vc = create_vault_proxy_client(
                os.path.join(CERT_PATH, 'client.key_secret'),
                os.path.join(CERT_PATH, 'server.key'),
            )
        else:
            vc = VaultClient(os.getenv("VAULT_URL"))
            assert vc.login_user(
                username=os.getenv("VAULT_USERNAME"),
                password=os.getenv("VAULT_PASSWORD"),
                stored=False,
            )
        return vc

    def get_session(self):
        """Retrieve a session database connection.

        Returns:
            database: A connection to the session database.
        """
        duration = self.db_conn.get("lease_duration")
        if duration and self.has_connection_expired(duration):
            self.make_new_connection()
        return self.db_conn["session"]
        
    def get_db(self):
        """Retrieve the Database connection

        Returns:
            _type_: A connection to the database engine.
        """
        duration = self.db_conn.get("lease_duration")
        if duration and self.has_connection_expired(duration):
            self.make_new_connection()
        return self.db_conn["engine"]

    def has_connection_expired(self, duration):
        curr_time = datetime.now()
        time_since_conn = (curr_time - self.db_conn["lease_created"]).total_seconds()
        return duration <= time_since_conn

    def get_dbname(self):
        """Retrive the name of the connected database.

        Returns:
            str: database name.
        """
        return self.db_conn["dbname"]
