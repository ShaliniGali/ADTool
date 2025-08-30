from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

from collections import defaultdict

from sqlalchemy.inspection import inspect
from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
    SmallInteger,
    Numeric,
    func,
    cast,
    Float,
    TEXT,
)

from sqlalchemy.orm import (
    Mapped,
    aliased,
)

from fastapi import HTTPException

from rds.table_model.socom.DtBudgetExecution import DtBudgetExecution
from rds.table_model.socom.DtAMSFEM import DtAMSFEM

class LookupAMSPGMX(SOCOMBase):
    __tablename__ ="LOOKUP_AMS_PGMX"
    __table_args__={
        'schema': SCHEMA
    }
    
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PXID: Mapped[int] = Column("PXID",Integer)
    PROGRAM_FULLNAME: Mapped[str] = Column("PROGRAM_FULLNAME",VARCHAR(256))
    PROGRAM_SHORT_NAME: Mapped[str] = Column("PROGRAM_SHORT_NAME",VARCHAR(128))
    ACQUISITION_TYPE: Mapped[str] = Column("ACQUISITION_TYPE",VARCHAR(36))
    DESCRIPTION_PLAIN_TEXT: Mapped[str] = Column("DESCRIPTION_PLAIN_TEXT",TEXT)
    ACCOMPLISHMENT_TEXT: Mapped[str] = Column("ACCOMPLISHMENT_TEXT",TEXT)
    ISSUE_TEXT: Mapped[str] = Column("ISSUE_TEXT",TEXT)


    @classmethod
    def get_ams_metadata_by_prog_groups(cls,prog_group:str,db_conn):
        pxids = DtAMSFEM.get_pxid_from_metadata(program_group=[prog_group],program_code=[],db_conn=db_conn)
        filter_set = set()

        for k,s in pxids.items():
            filter_set.update(s)

        if not pxids:
            raise HTTPException(404,"PXIDs not found for submitted program group")
        data = db_conn.query(
            cls.PROGRAM_FULLNAME,
            cls.DESCRIPTION_PLAIN_TEXT, cls.ACCOMPLISHMENT_TEXT, cls.ISSUE_TEXT).filter(
            cls.PXID.in_(filter_set)
            ).distinct().all() #assume only 1 key since input is a string, not list
        
        result = defaultdict(set)
        for pname,desc,acc,iss in data:
            if desc: result["PROGRAM DESCRIPTION"].add(pname+": "+desc)
            if acc: result["PROGRAM ACCOMPLISHMENTS"].add(pname+": "+acc)
            if iss: result["PROGRAM ISSUES"].add(pname+": "+iss)
        return result