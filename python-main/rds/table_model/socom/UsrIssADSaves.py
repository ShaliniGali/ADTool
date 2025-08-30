from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

import datetime

from sqlalchemy.inspection import inspect
from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
    SmallInteger,
    DateTime,
    Enum,

)

from sqlalchemy.orm import (
    Mapped,
)

class UsrIssADSaves(SOCOMBase):
    __tablename__ = 'USR_ISSUE_AD_SAVES'
    __table_args__ = {
        'schema': SCHEMA
    }

    ID: Mapped[int] = Column('ID', Integer, primary_key=True, autoincrement=True) 
    AD_RECOMENDATION: Mapped[str] = Column('AD_RECOMENDATION', Enum('Approve', 'Approve at Scale', 'Disapprove', name='ad_recommendation_enum'), nullable=False)  
    AD_USER_ID: Mapped[int] = Column('AD_USER_ID', Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP',DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP',DateTime)
    EVENT_ID: Mapped[str] = Column('EVENT_ID', VARCHAR(100)) 
    POM_ID: Mapped[int] = Column('POM_ID', Integer)
    IS_DELETED: Mapped[int] = Column('IS_DELETED', SmallInteger)
    