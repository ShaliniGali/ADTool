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


class UsrLookupUserCriteriaTerms(SOCOMBase):
    __tablename__ = 'USR_LOOKUP_USER_CRITERIA_TERMS'
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True) #FILE ID
    CRITERIA_NAME_ID: Mapped[int] = Column('CRITERIA_NAME_ID',Integer)                                  
    CRITERIA_TERM: Mapped[str] = Column('CRITERIA_TERM',CHAR(100))
    USER_ID: Mapped[int] = Column('USER_ID',Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP',DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP',DateTime)
    
    @classmethod
    def get_crit_terms_from_cycle_id(cls,UsrLookupUserCriteriaName,cycle_id:int,db_conn):
        query = db_conn.query(
            cls.CRITERIA_TERM, 
            UsrLookupUserCriteriaName.ID.label('CRITERIA_NAME_ID'),
            UsrLookupUserCriteriaName.CYCLE_ID
        ).join(UsrLookupUserCriteriaName, cls.CRITERIA_NAME_ID == UsrLookupUserCriteriaName.ID).filter(
            UsrLookupUserCriteriaName.CYCLE_ID == cycle_id).order_by(cls.ID.asc())

        data = [token[0] for token in query.all()]
        print(data)
        return data