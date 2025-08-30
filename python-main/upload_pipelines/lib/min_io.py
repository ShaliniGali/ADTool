from typing import (
    Tuple,
    Optional
)

from io import BytesIO
from minio import Minio

from dotenv import load_dotenv
import os
load_dotenv() 

# # Load AWS Credentials
# FORCE_S3_REGION = os.environ.get("FORCE_S3_REGION", "us-gov-west-1")
# FORCE_S3_ENDPOINT_URL = os.environ.get("FORCE_S3_ENDPOINT_URL", "s3.us-gov-east-1.amazonaws.com")
# if FORCE_S3_REGION == None:
#     raise ValueError(".env parameter not found 'FORCE_S3_REGION'")
# if FORCE_S3_ENDPOINT_URL == None:
#     raise ValueError(".env parameter not found 'FORCE_S3_ENDPOINT_URL'")
# FORCE_AWS_SERVER_PUBLIC_KEY = os.environ.get("FORCE_AWS_SERVER_PUBLIC_KEY")
# FORCE_AWS_SERVER_SECRET_KEY = os.environ.get("FORCE_AWS_SERVER_SECRET_KEY")

class SoComMinioSetting:
    SOCOM_S3_REGION = os.environ.get("SOCOM_S3_REGION", "us-gov-west-1")
    SOCOM_S3_ENDPOINT_URL = os.environ.get("SOCOM_S3_ENDPOINT_URL", "https://s3.us-gov-east-1.amazonaws.com")
    SOCOM_AWS_SERVER_PUBLIC_KEY = os.environ.get("SOCOM_AWS_SERVER_PUBLIC_KEY")
    SOCOM_AWS_SERVER_SECRET_KEY = os.environ.get("SOCOM_AWS_SERVER_SECRET_KEY")
    SOCOM_S3_BUCKET = os.environ.get("SOCOM_S3_BUCKET")
    SECURED = False


    @classmethod
    def load_local_setting(cls):
        """defined as the default docker setting for testing purposes only"""
        cls.SOCOM_S3_REGION = None
        cls.SOCOM_S3_ENDPOINT_URL = "localhost:9000"
        cls.SOCOM_AWS_SERVER_PUBLIC_KEY = "admin"
        cls.SOCOM_AWS_SERVER_SECRET_KEY = "admin123"
        cls.SECURE = False


def get_s3_client(minio_setting:SoComMinioSetting):
    # if (region_name or endpoint_url or aws_access_key_id or aws_secret_access_key) == None:
    #     raise ValueError("Missing environment variables for MinIO")
    client = Minio(
        region=minio_setting.SOCOM_S3_REGION, 
        endpoint=minio_setting.SOCOM_S3_ENDPOINT_URL, 
        access_key=minio_setting.SOCOM_AWS_SERVER_PUBLIC_KEY,
        secret_key=minio_setting.SOCOM_AWS_SERVER_SECRET_KEY, 
        secure=minio_setting.SECURED
    )
    return client

def upload_s3_file(client,bucket_name, object_name,local_file_path):
    # Upload an Excel file with minio
    try:
        client.fput_object(bucket_name, object_name, file_path=local_file_path)
        print(f'Uploaded {local_file_path} to {bucket_name}/{object_name}')
        return 1
    except S3Error as e:
        print(f'Error uploading file: {e}')
        return 0
        
def download_s3_file(client,bucket_name,object_name,local_file_path=None):
    #using minio
    if not local_file_path: #streaming
        res = client.get_object(bucket_name, object_name) #retrieve as bytes
        if res.status != 200:
            raise HTTPException(500,details="cannot retrieve stored object. Please check path")
            
        data = BytesIO(res.read()) #urllib3 package
        data.seek(0)
        return data
    
    else: #download file to store locally
        try:
            client.fget_object(bucket_name, object_name, local_file_path)
            print(f'Downloaded {object_name} to {local_file_path}')
        except S3Error as e:
            print(f'Error downloading file: {e}')
    return None