from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List
from fastapi import HTTPException

from sqlalchemy import (
    Column,
    VARCHAR,
    JSON,
    Integer
)

from sqlalchemy.orm import (
    Mapped,

)




class LookupPOMPositionDecrement(SOCOMBase):
    __tablename__ = 'LOOKUP_POM_POSITION_DECREMENT'
    __table_args__ = {
        'schema': SCHEMA
    }
    PK_DUMMY: Mapped[int] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    POSITION:Mapped[str] = Column('POSITION',VARCHAR(30))
    SUBAPP:Mapped[str] = Column('SUBAPP',VARCHAR(30))
    EXT_DECR:Mapped[int] = Column('EXT_DECR',Integer)
    ZBT_DECR:Mapped[int] = Column('ZBT_DECR',Integer)
    ISS_DECR:Mapped[int] = Column('ISS_DECR',Integer)
    POM_DECR:Mapped[int] = Column('POM_DECR',Integer)


    @classmethod
    def get_decrement_map(cls,db_conn):
        query = db_conn.query(cls.POSITION,cls.SUBAPP,cls.EXT_DECR,cls.ZBT_DECR,cls.ISS_DECR,cls.POM_DECR).all()
        data = {}
        for pos,subapp,ext,zbt,iss,pom in query:
            data[pos] = {"SUBAPP":subapp,"EXT":ext,"ZBT":zbt,"ISS":iss,"POM":pom}
        return data