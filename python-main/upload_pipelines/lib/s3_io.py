import os
import sys
import json
import logging
import traceback
from io import BytesIO
from datetime import datetime
from typing import (
    Optional, 
    Tuple
)

import boto3
import pandas as pd
from botocore.config import Config
from botocore.exceptions import ClientError

from dotenv import load_dotenv
import os
load_dotenv() 

class SoComAWSSetting:
    SOCOM_S3_REGION = os.environ.get("SOCOM_S3_REGION", "us-gov-west-1")
    SOCOM_S3_ENDPOINT_URL = os.environ.get("SOCOM_S3_ENDPOINT_URL", "https://s3.us-gov-east-1.amazonaws.com")
    SOCOM_AWS_SERVER_PUBLIC_KEY = os.environ.get("SOCOM_AWS_SERVER_PUBLIC_KEY",None) #dev-instance as None
    SOCOM_AWS_SERVER_SECRET_KEY = os.environ.get("SOCOM_AWS_SERVER_SECRET_KEY",None) #dev-instance as None
    SOCOM_S3_BUCKET = os.environ.get("SOCOM_S3_BUCKET")
    SECURED = False
    def __str__(cls):
        return f"{cls.SOCOM_AWS_SERVER_PUBLIC_KEY},{cls.SOCOM_AWS_SERVER_SECRET_KEY}"

def get_s3_client(setting:SoComAWSSetting):
    my_config = Config(
        region_name=setting.SOCOM_S3_REGION,
        signature_version='s3v4'
    )
    client = boto3.client(
        's3', 
        config=my_config,
        aws_access_key_id=setting.SOCOM_AWS_SERVER_PUBLIC_KEY, 
        aws_secret_access_key=setting.SOCOM_AWS_SERVER_SECRET_KEY,
        endpoint_url=setting.SOCOM_S3_ENDPOINT_URL
    )
    return client

def download_s3_file(client,bucket_name,object_name,local_file_path=None):
    
    resp = client.get_object(Bucket=bucket_name, Key=object_name)
    
    if resp['ResponseMetadata']['HTTPStatusCode'] != 200:
        raise FileNotFoundError(f'Unable to access S3 or find file on s3: {object_name}')
    
    data = resp['Body'].read()
    
    if not local_file_path:
        #for multiple sheets:
        """
        dfs = pd.read_excel(data, engine='openpyxl', sheet_name=["data","metadata"])
        df_content = dfs['data']
        df_metadata = dfs['metadata']

        """
        return BytesIO(data)
    
    #local files
    with open (local_file_path,"wb") as f:
        f.write(data)
    return None
