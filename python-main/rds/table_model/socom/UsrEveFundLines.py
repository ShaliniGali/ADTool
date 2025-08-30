from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)
from typing import List
from fastapi import HTTPException
import datetime

from sqlalchemy.inspection import inspect
from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
    SmallInteger,
    DateTime,
    func,
    String,
    JSON

)

from sqlalchemy.orm import (
    Mapped,
)

class UsrEventFundingLines(SOCOMBase):
    __tablename__ = 'USR_EVENT_FUNDING_LINES'
    # __abstract__ = True

    __table_args__ = {
        'schema': SCHEMA
    }

    ID: Mapped[int] = Column('ID', Integer, primary_key=True, autoincrement=True)
    EVENT_NAME: Mapped[str] = Column('EVENT_NAME', String(100))
    CYCLE_ID: Mapped[int] = Column('CYCLE_ID', Integer)
    CRITERIA_NAME_ID: Mapped[int] = Column('CRITERIA_NAME_ID', Integer)
    POM_ID: Mapped[int] = Column('POM_ID', Integer)
    POM_POSITION: Mapped[str] = Column('POM_POSITION', String(30))
    FY_1: Mapped[int] = Column('FY_1', Integer)
    FY_2: Mapped[int] = Column('FY_2', Integer)
    FY_3: Mapped[int] = Column('FY_3', Integer)
    FY_4: Mapped[int] = Column('FY_4', Integer)
    FY_5: Mapped[int] = Column('FY_5', Integer)
    APPROVE_TABLE: Mapped[dict] = Column('APPROVE_TABLE', JSON)
    YEAR_LIST: Mapped[list] = Column('YEAR_LIST', JSON)
    USER_ID: Mapped[int] = Column('USER_ID', Integer)
    UPDATE_USER_ID: Mapped[int] = Column('UPDATE_USER_ID', Integer)
    CREATED_DATETIME: Mapped[DateTime] = Column('CREATED_DATETIME', DateTime)
    UPDATED_DATETIME: Mapped[DateTime] = Column('UPDATED_DATETIME', DateTime)
    APP_VERSION: Mapped[str] = Column('APP_VERSION', String(45))

    @classmethod
    async def get_event_funding_lines(cls, pom_id: int, event_names: List[str], db_conn):
      
        query =  db_conn.query(
            cls.EVENT_NAME,
            cls.FY_1,
            cls.FY_2,
            cls.FY_3,
            cls.FY_4,
            cls.FY_5,
            cls.APPROVE_TABLE
        ).filter(
            cls.POM_ID == pom_id,
            cls.EVENT_NAME.in_(event_names)
        ).all()
        # if not query:
            # raise HTTPException(404, f"No funding lines found for events: {event_names} with POM_ID: {pom_id}")
        
        result = []
        for row in query:
            result.append({
                "EVENT_NAME": row[0],
                "FY_1": row[1],
                "FY_2": row[2],
                "FY_3": row[3],
                "FY_4": row[4],
                "FY_5": row[5],
                "APPROVAL_TABLE":row[6]
            })
        return result
