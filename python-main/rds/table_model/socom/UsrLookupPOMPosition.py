from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)

from fastapi import HTTPException
from datetime import datetime

from sqlalchemy import (
    Column, 
    Integer, 
    JSON,
    VARCHAR,
    Boolean,
    DateTime,
)

from sqlalchemy.orm import (
    Mapped,  
)


class UsrLookupPOMPosition(SOCOMBase):
    __tablename__ = 'USR_LOOKUP_POM_POSITION'
    __table_args__ = {
        'schema': SCHEMA
    }    
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True) #FILE ID
    POM_YEAR: Mapped[str] = Column('POM_YEAR',VARCHAR(45))                                  
    LATEST_POSITION: Mapped[str] = Column('LATEST_POSITION',VARCHAR(30))
    IS_ACTIVE: Mapped[bool] = Column('IS_ACTIVE',Boolean)
    USER_ID: Mapped[int] = Column("USER_ID",Integer)
    CREATED_DATETIME: Mapped[datetime] = Column("CREATED_DATETIME",DateTime)
    # SUBAPP: Mapped[str] = Column('SUBAPP',VARCHAR(45))
    # YEARLY_DECREMENT: Mapped[JSON] = Column('YEARLY_DECREMENT',JSON)

    @classmethod
    def get_active_pom_year_position(cls,db_conn):
        data = db_conn.query(cls.POM_YEAR,cls.LATEST_POSITION).filter(cls.IS_ACTIVE == 1).one_or_none()
        if not data:
            raise HTTPException(404,"database not found or no active entries in the database table")
        # breakpoint()
        return data
    
    @classmethod
    def get_active_pom_id(cls, db_conn):
        
        data = db_conn.query(cls.ID).filter(cls.IS_ACTIVE == 1).one_or_none()
        if not data:
            raise HTTPException(404, "No active POM_ID found in USR_LOOKUP_POM_POSITION table")
        return data.ID