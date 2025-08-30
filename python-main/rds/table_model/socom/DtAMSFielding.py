from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

from collections import defaultdict
from typing import List, Optional

from sqlalchemy.inspection import inspect
from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
    SmallInteger,
    func,
)

from sqlalchemy.orm import (
    Mapped,
    aliased,
)
from fastapi import HTTPException

from rds.table_model.socom.DtBudgetExecution import DtBudgetExecution
from rds.table_model.socom.DtAMSFEM import DtAMSFEM
from rds.table_model.socom.LookupAMSPGMX import LookupAMSPGMX

class DtAMSFielding(SOCOMBase):
    __tablename__ ="DT_AMS_FIELDING"
    __table_args__={
        'schema': SCHEMA
    }
    
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PXID: Mapped[int] = Column("PXID",Integer)
    COMPONENT: Mapped[str] = Column("COMPONENT",VARCHAR(13))
    FIELDING_ITEM: Mapped[str] = Column("FIELDING_ITEM",VARCHAR(128))
    PEO: Mapped[str] = Column("PEO",VARCHAR(8))
    TYPE: Mapped[str] = Column("TYPE",VARCHAR(8))
    PLAN_QUANTITY: Mapped[int] = Column("PLAN_QUANTITY",Integer)
    ACTUAL_QUANTITY: Mapped[int] = Column("ACTUAL_QUANTITY",Integer)
    PLAN_FISCAL_YEAR: Mapped[int] = Column("PLAN_FISCAL_YEAR",SmallInteger)
    ACTUAL_FISCAL_YEAR: Mapped[int] = Column("ACTUAL_FISCAL_YEAR",SmallInteger)

    @classmethod
    def get_ams_fielding_quantity(cls,prog_group:str,fielding_items:List[str],components:List[str],fy,fielding_types:Optional[List[str]],db_conn):
        pxids = DtAMSFEM.get_pxid_from_metadata(program_group=[prog_group],program_code=[],db_conn=db_conn)
        filter_set = set().union(*pxids.values())
        print(len(filter_set))
        if not pxids:
            raise HTTPException(404,"PXIDs not found for submitted program group")
        
        query = db_conn.query(
            func.sum(cls.PLAN_QUANTITY).label("SUM_PLAN_QUANTITY"),
            func.sum(cls.ACTUAL_QUANTITY).label("SUM_ACTUAL_QUANTITY"),
            cls.COMPONENT,
            cls.PLAN_FISCAL_YEAR,
            cls.FIELDING_ITEM,
            # cls.TYPE, #only for debugging purposes
        ).filter(
            ((cls.PXID.in_(filter_set)) & (cls.PXID.isnot(None))))
        
        if fielding_types:
            query = query.filter(cls.TYPE.in_(fielding_types))

        query = query.group_by(
            cls.COMPONENT,
            cls.PLAN_FISCAL_YEAR,
            cls.FIELDING_ITEM
        ).order_by(cls.PLAN_FISCAL_YEAR,cls.FIELDING_ITEM,cls.COMPONENT)
        
        if components:
            query = query.filter(cls.COMPONENT.in_(components))
        
        if fy:
            query = query.filter(cls.PLAN_FISCAL_YEAR == int(fy))
        
        if fielding_items:
            query = query.filter(cls.FIELDING_ITEM.in_(fielding_items))
        # [
        #     {
        #         "SUM_PLAN_QUANTITY": 778625,
        #         "SUM_ACTUAL_QUANTITY": 777467,
        #         "COMPONENT": "NSW",
        #         "FIELDING_ITEM":"Mosquito",
        #         "PLAN_FISCAL_YEAR": 2023
        #     },..]
        data = query.all()
        return data