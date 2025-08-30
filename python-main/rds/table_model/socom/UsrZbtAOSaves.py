from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)
import datetime

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

class UsrZbtAOSaves(SOCOMBase):
    __tablename__ = 'USR_ZBT_AO_SAVES'
    __table_args__ = {
        'schema': SCHEMA
    }

    ID: Mapped[int] = Column('ID', Integer, primary_key=True, autoincrement=True) 
    AO_RECOMENDATION: Mapped[str] = Column('AO_RECOMENDATION', Enum('Approve', 'Approve at Scale', 'Disapprove', name='ad_recommendation_enum'), nullable=False)  
    AO_USER_ID: Mapped[int] = Column('AO_USER_ID', Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP',DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP',DateTime)
    EVENT_ID: Mapped[str] = Column('EVENT_ID', VARCHAR(100)) 
    POM_ID: Mapped[int] = Column('POM_ID', Integer)
    IS_DELETED: Mapped[int] = Column('IS_DELETED', SmallInteger)
    