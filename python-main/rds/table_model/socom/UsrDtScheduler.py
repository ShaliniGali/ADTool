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

class UsrDtScheduler(SOCOMBase):
    __tablename__ = 'USR_DT_SCHEDULER'
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True) #FILE ID
    CYCLE_ID: Mapped[int] = Column('CYCLE_ID',Integer)     
    CRON_STATUS: Mapped[int] = Column('CRON_STATUS',Integer)
    USER_ID: Mapped[int] = Column('USER_ID',Integer)

    

