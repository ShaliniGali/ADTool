from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List

from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
)

from sqlalchemy.orm import (
    Mapped, 

)

class LookupStorm(SOCOMBase):
    __tablename__ = "LOOKUP_STORM"
    __table_args__ = {
        'schema': SCHEMA
    }
    PK_DUMMY: Mapped[int] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    ID: Mapped[int] = Column('ID',VARCHAR(40))
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    ACCESS_TYPE: Mapped[str] = Column('ACCESS_TYPE',VARCHAR(50))
    SA_SCORE: Mapped[int] = Column('SA_SCORE',Integer)
    ID_SC_SCORE: Mapped[int] = Column('ID_SC_SCORE',Integer)
    M_SCORE: Mapped[int] = Column('M_SCORE',Integer)
    TOTAL_SCORE: Mapped[int] = Column('TOTAL_SCORE',Integer)


    @classmethod
    def get_total_score_from_progIds(cls, LookupProgramDetailsModel, db_conn, prog_ids:List[str], to_dict:bool=False):
        query = db_conn.query(
            LookupProgramDetailsModel.ID,
            cls.TOTAL_SCORE
        ).select_from(
            LookupProgramDetailsModel.__table__
        ).join(
            cls.__table__,
            LookupProgramDetailsModel.STORM_ID == cls.ID
        ).filter(
            LookupProgramDetailsModel.ID.in_(prog_ids)
        ).distinct()
        if to_dict:
            result_dicts = {result.ID: result.TOTAL_SCORE for result in query.all()}
            return result_dicts
        else:
            return query.all()