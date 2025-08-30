from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)


import datetime

from sqlalchemy import (
    Column, 
    Integer, 
    JSON,
    CHAR,
    DateTime,
)

from sqlalchemy.orm import (
    Mapped,  
)


class UsrLookupUserCriteriaName(SOCOMBase):
    __tablename__ = 'USR_LOOKUP_USER_CRITERIA_NAME'
    __table_args__ = {
        'schema': SCHEMA
    }    
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True) #FILE ID
    CRITERIA_NAME: Mapped[str] = Column('CRITERIA_NAME',CHAR(100))                                  
    CYCLE_ID: Mapped[int] = Column('CYCLE_ID',Integer)
    USER_ID: Mapped[int] = Column('USER_ID',Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP',DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP',DateTime)