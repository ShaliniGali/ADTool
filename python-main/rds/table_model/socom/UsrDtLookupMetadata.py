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

class TypeEnum(str, enum.Enum):
    PROGRAM_SCORE_UPLOAD = "PROGRAM_SCORE_UPLOAD"
    DT_UPLOAD_BASE_UPLOAD = "DT_UPLOAD_BASE_UPLOAD"
    DT_UPLOAD_BASE_UPLOAD_APPEND = "DT_UPLOAD_BASE_UPLOAD_APPEND"
    DT_UPLOAD_EXTRACT_UPLOAD = "DT_UPLOAD_EXTRACT_UPLOAD"

class UsrDtLookupMetadata(SOCOMBase):
    __tablename__ = 'USR_DT_LOOKUP_METADATA'
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True) #FILE ID
    USR_DT_UPLOAD_ID: Mapped[int] = Column('USR_DT_UPLOAD_ID',Integer)     
    TABLE_NAME: Mapped[str] = Column('TABLE_NAME',VARCHAR(50))
    POM_YEAR: Mapped[int] = Column('POM_YEAR',Integer)
    IS_DIRTY_TABLE_ACTIVE: Mapped[bool] = Column('IS_DIRTY_TABLE_ACTIVE', Integer)
    IS_ACTIVE: Mapped[bool] = Column('IS_ACTIVE', Integer)

    

