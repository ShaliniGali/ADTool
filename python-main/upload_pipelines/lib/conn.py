import os
from botocore.config import Config
from minio import Minio
import boto3


class SoComMinioSetting:
    """Use for object storage connection inferface"""
    SOCOM_MINIO_TLS_ENABLED = os.getenv("SOCOM_MINIO_TLS_ENABLED", "true").strip().lower() == "true"
    SOCOM_S3_REGION = os.environ.get("SOCOM_S3_REGION", "us-gov-west-1")
    SOCOM_S3_ENDPOINT_URL = os.environ.get("SOCOM_S3_ENDPOINT_URL", "https://s3.us-gov-east-1.amazonaws.com")
    SOCOM_AWS_SERVER_PUBLIC_KEY = os.environ.get("SOCOM_AWS_SERVER_PUBLIC_KEY")
    SOCOM_AWS_SERVER_SECRET_KEY = os.environ.get("SOCOM_AWS_SERVER_SECRET_KEY")
    SOCOM_S3_BUCKET = os.environ.get("SOCOM_S3_BUCKET")
    SECURED = True if SOCOM_MINIO_TLS_ENABLED else False

    @classmethod
    def local_mode(cls):
        cls.SOCOM_MINIO_TLS_ENABLED = False
        cls.SOCOM_S3_REGION = os.environ.get("SOCOM_S3_REGION", "us-gov-west-1")
        cls.SOCOM_S3_ENDPOINT_URL = "localhost:9000"
        cls.SOCOM_AWS_SERVER_PUBLIC_KEY = "minioadmin"
        cls.SOCOM_AWS_SERVER_SECRET_KEY = "minioadmin"
        cls.SOCOM_S3_BUCKET = os.environ.get("SOCOM_S3_BUCKET")
        cls.SECURED = False
class SoComAWSSetting:
    """Use for object storage connection inferface"""
    SOCOM_S3_REGION = os.environ.get("SOCOM_S3_REGION", "us-gov-west-1")
    SOCOM_S3_ENDPOINT_URL = os.environ.get("SOCOM_S3_ENDPOINT_URL", "https://s3.us-gov-east-1.amazonaws.com")
    SOCOM_AWS_SERVER_PUBLIC_KEY = os.environ.get("SOCOM_AWS_SERVER_PUBLIC_KEY", None)
    SOCOM_AWS_SERVER_SECRET_KEY = os.environ.get("SOCOM_AWS_SERVER_SECRET_KEY", None)
    SOCOM_S3_BUCKET = os.environ.get("SOCOM_S3_BUCKET")
    SECURED = False


def get_s3_client(setting):
    if isinstance(setting, SoComMinioSetting):
        print("Connecting to MinIO...")
        print(f"Endpoint URL: {setting.SOCOM_S3_ENDPOINT_URL}")
        #breakpoint()
        client = Minio(
            setting.SOCOM_S3_ENDPOINT_URL,
            access_key=setting.SOCOM_AWS_SERVER_PUBLIC_KEY,
            secret_key=setting.SOCOM_AWS_SERVER_SECRET_KEY,
            secure=setting.SECURED
        )
        try:
            buckets = client.list_buckets()
            print(f"Connected to MinIO. Available buckets: {[bucket.name for bucket in buckets]}")
        except Exception as e:
            print(f"Error connecting to MinIO: {e}")
    elif isinstance(setting, SoComAWSSetting):
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
    else:
        raise ValueError("Unsupported setting type.")
    
    return client