from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)

from sqlalchemy import (
    Column, 
    Integer, 
    JSON,
)

from sqlalchemy.orm import (
    Mapped,  
)


class UsrLookupCriticalWts(SOCOMBase):
    __tablename__ ="USR_LOOKUP_CRITERIA_WEIGHTS"
    __table_args__={
        'schema': SCHEMA
    }
    WEIGHT_ID: Mapped[int] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    SESSION: Mapped[JSON] = Column('SESSION',JSON)