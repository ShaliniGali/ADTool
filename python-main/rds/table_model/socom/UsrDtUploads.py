from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List
import datetime
import enum

from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
    DateTime,
    Float,
    CHAR,
    Enum,

)

from sqlalchemy.orm import (
    Mapped, 

)

from tenacity import retry, stop_after_attempt, wait_exponential, retry_if_exception_type
from sqlalchemy.exc import SQLAlchemyError
from typing import TYPE_CHECKING

from rds.table_model.socom.UsrDtLookupMetadata import UsrDtLookupMetadata

class TypeEnum(str, enum.Enum):
    PROGRAM_SCORE_UPLOAD = "PROGRAM_SCORE_UPLOAD"
    DT_UPLOAD_BASE_UPLOAD = "DT_UPLOAD_BASE_UPLOAD"
    DT_UPLOAD_BASE_UPLOAD_APPEND = "DT_UPLOAD_BASE_UPLOAD_APPEND"
    DT_UPLOAD_EXTRACT_UPLOAD = "DT_UPLOAD_EXTRACT_UPLOAD"
    DT_OUT_POM = "DT_OUT_POM"

class UsrDtUploads(SOCOMBase):
    __tablename__ = 'USR_DT_UPLOADS'
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True) #FILE ID
    CYCLE_ID: Mapped[int] = Column('CYCLE_ID',Integer)     
    TYPE: Mapped[TypeEnum] = Column('TYPE',Enum(TypeEnum),nullable=False) #actually enum
    FILE_STATUS: Mapped[int] = Column("FILE_STATUS",Integer)               
    TITLE: Mapped[str] = Column('TITLE',VARCHAR(100))
    DESCRIPTION: Mapped[str] = Column('DESCRIPTION',VARCHAR(5000))
    VERSION: Mapped[float] = Column('VERSION',CHAR(20))
    S3_PATH:Mapped[str] = Column('S3_PATH',VARCHAR(1500))
    FILE_NAME:Mapped[str] = Column('FILE_NAME',VARCHAR(500))
    USER_ID: Mapped[int] = Column('USER_ID',Integer)
    UPDATE_USER_ID: Mapped[int] = Column('UPDATE_USER_ID',Integer)
    IS_DELETED: Mapped[int] = Column('IS_DELETED',Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP',DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP',DateTime)

    

    @classmethod
    def get_file_download_metadata(cls,id,db_conn):
        query = db_conn.query(cls,UsrDtLookupMetadata.TABLE_NAME,
                              UsrDtLookupMetadata.POM_YEAR).join(
            UsrDtLookupMetadata,UsrDtLookupMetadata.USR_DT_UPLOAD_ID == cls.ID
            ).filter(cls.ID == id).first()

        row_id = query.UsrDtUploads.ID
        table_type = query.UsrDtUploads.TYPE
        pom_year = query.POM_YEAR
        table_name = query.TABLE_NAME
        cycle_id = query.UsrDtUploads.CYCLE_ID
        title = query.UsrDtUploads.TITLE
        description = query.UsrDtUploads.DESCRIPTION
        s3_path = query.UsrDtUploads.S3_PATH
        version = query.UsrDtUploads.VERSION
        user_id = query.UsrDtUploads.USER_ID
        update_user_id = query.UsrDtUploads.UPDATE_USER_ID
        created_timestamp = query.UsrDtUploads.CREATED_TIMESTAMP.strftime("%Y-%m-%d %H:%M:%S")
        updated_timestamp = query.UsrDtUploads.UPDATED_TIMESTAMP.strftime("%Y-%m-%d %H:%M:%S")
        
        metadata = {
            "FILE_ID": row_id,
            "TYPE":table_type,
            "TABLE_NAME": table_name,
            "POM_YEAR":pom_year,
            "CYCLE_ID": cycle_id,
            "TITLE":title,
            "DESCRIPTION":description,
            "S3_PATH":s3_path,
            "VERSION":version,
            "USER_ID":user_id,
            "UPDATE_USER_ID":update_user_id,
            "CREATED_TIMESTAMP": created_timestamp,
            "UPDATED_TIMESTAMP":updated_timestamp
        }

        return metadata