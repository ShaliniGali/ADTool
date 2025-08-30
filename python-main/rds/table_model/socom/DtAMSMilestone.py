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
    DateTime,
    func,
    Boolean,
    case,
)

from typing import List

from sqlalchemy.orm import (
    Mapped,
    aliased,
)

from datetime import datetime
from fastapi import HTTPException

from rds.table_model.socom.DtAMSFEM import DtAMSFEM
from rds.table_model.socom.LookupAMSPGMX import LookupAMSPGMX

class DtAMSMilestone(SOCOMBase):
    __tablename__ ="DT_AMS_MILESTONE"
    __table_args__={
        'schema': SCHEMA
    }
    
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PXID: Mapped[int] = Column("PXID",Integer)
    PEO: Mapped[str] = Column("PEO",VARCHAR(8))
    PROC_STRATEGY: Mapped[str] = Column("PROC_STRATEGY",VARCHAR(8))
    TEMPLATE: Mapped[str] = Column("TEMPLATE",VARCHAR(32))
    MILESTONE_SORT_ORDER: Mapped[str] = Column("MILESTONE_SORT_ORDER",Integer)
    MILESTONE: Mapped[str] = Column("MILESTONE",VARCHAR(8))
    START_DATE: Mapped[datetime] = Column("START_DATE",DateTime)
    END_DATE: Mapped[datetime] = Column("END_DATE",DateTime)
    MILESTONE_STATUS: Mapped[str] = Column("MILESTONE_STATUS",VARCHAR(32))
    COMPLETE_CHECK: Mapped[str] = Column("COMPLETE_CHECK",VARCHAR(16))
    DUE_DATE: Mapped[datetime] = Column("DUE_DATE",DateTime)
    IS_WAIVED: Mapped[bool] = Column("IS_WAIVED",Boolean)
    REQUIREMENT: Mapped[bool] = Column("REQUIREMENT",VARCHAR(12))
    COMPLETION_STATUS: Mapped[bool] = Column("COMPLETION_STATUS",VARCHAR(16))
    COMPLETED_DATE: Mapped[datetime] = Column("COMPLETED_DATE",DateTime)
    IS_CURRENT: Mapped[bool] = Column("IS_CURRENT",Boolean)

    @classmethod
    def get_milestone_by_prog_group(cls,prog_group:str,program_fullnames:List[str],db_conn):
        pxids = DtAMSFEM.get_pxid_from_metadata(program_group=[prog_group],program_code=[],db_conn=db_conn)

        filter_set = set().union(*pxids.values())

        if not pxids:
            raise HTTPException(404,"PXIDs not found for submitted program group")
        
        pxid_tuples = db_conn.query(DtAMSFEM.PXID,func.sum(DtAMSFEM.EXPEND_PLAN_AMOUNT)).filter(DtAMSFEM.PXID.in_(filter_set)).group_by(DtAMSFEM.PXID).all()
        pxid_sorting_map = {pxid: value for pxid, value in pxid_tuples}

        has_requirements = case(
            [
                (cls.START_DATE.is_(None),0),
                ((cls.COMPLETE_CHECK == "INCOMPLETE") & (cls.DUE_DATE.is_(None)),0),
                ((cls.COMPLETE_CHECK == "INCOMPLETE") & (cls.MILESTONE_STATUS == "Previous Milestone") & (cls.IS_WAIVED.is_(True)),0)
            ],
            else_ = 1
        )

        query = db_conn.query(
            cls.PXID,
            cls.PROC_STRATEGY,
            cls.MILESTONE,
            cls.START_DATE,
            cls.END_DATE,
            cls.MILESTONE_STATUS,
            LookupAMSPGMX.PROGRAM_FULLNAME,
            has_requirements.label("HAS_REQUIREMENTS")

        ).join(LookupAMSPGMX,
               LookupAMSPGMX.PXID == cls.PXID).filter((cls.PXID.in_(filter_set)) & (LookupAMSPGMX.PROGRAM_FULLNAME.isnot(None)))
        
        if program_fullnames:
            query = query.filter(LookupAMSPGMX.PROGRAM_FULLNAME.in_(program_fullnames))
        
        data = query.group_by(
                    cls.PXID,
                    cls.PROC_STRATEGY,
                    cls.MILESTONE,
                    cls.START_DATE,
                    cls.END_DATE,
                    cls.MILESTONE_STATUS,
                    LookupAMSPGMX.PROGRAM_FULLNAME,
                    has_requirements
               ).order_by(
                   cls.MILESTONE_SORT_ORDER,cls.MILESTONE
               ).all()
        
        result = defaultdict(list)
        result["ALL_PROGRAM_FULLNAME"] = set()

        for item in data:
            milestone_status = item["MILESTONE_STATUS"]
            
            result[milestone_status].append({
                "PXID":item["PXID"],
                "SUM_EXPENDED_AMOUNT":pxid_sorting_map[item["PXID"]],
                "PROGRAM_FULLNAME": item["PROGRAM_FULLNAME"],
                "PROC_STRATEGY": item["PROC_STRATEGY"],
                "MILESTONE": item["MILESTONE"],
                "START_DATE": item["START_DATE"],
                "END_DATE": item["END_DATE"],
                "HAS_REQUIREMENTS":item["HAS_REQUIREMENTS"]

            })
            result["ALL_PROGRAM_FULLNAME"].add(item["PROGRAM_FULLNAME"])
        
        result["ALL_PROGRAM_FULLNAME"] = sorted(list(result["ALL_PROGRAM_FULLNAME"]))
        return result
    

    @classmethod
    def get_milestone_requirements(cls,pxid:int,milestone:str,milestone_status:str,db_conn):
        query = db_conn.query(
            cls.PXID,
            cls.PEO,
            cls.PROC_STRATEGY,
            cls.TEMPLATE,
            cls.MILESTONE_SORT_ORDER, 
            cls.MILESTONE,
            cls.MILESTONE_STATUS,
            cls.REQUIREMENT, 
            cls.COMPLETE_CHECK, 
            cls.IS_CURRENT, 
            cls.START_DATE,
            cls.END_DATE,
            cls.DUE_DATE,
            cls.COMPLETED_DATE,
            cls.IS_WAIVED,
            cls.COMPLETION_STATUS).filter(
                (cls.PXID == pxid) &
                (cls.MILESTONE == milestone) &
                (cls.MILESTONE_STATUS == milestone_status)
            )
        
        data = query.all()
        
        if not data:
            raise HTTPException(404,"No requirements found for the given PXID, Milestone and Milestone_status")
        
        data = [
            {column["name"]: value for column, value in zip(query.column_descriptions, row)}
            for row in data
        ]

        #filter based off milestone status
        result = []
        for row in data.copy():
            if not row["START_DATE"]:
                continue
            elif row["COMPLETE_CHECK"] == "INCOMPLETE" and not row["DUE_DATE"]:
                continue
            elif row["COMPLETE_CHECK"] == "INCOMPLETE" and \
                  milestone_status == "Previous Milestone" and \
                  row["IS_WAIVED"] == True:
                continue
            
            result.append(row)

        return result

    @classmethod
    def get_ams_metadata_by_prog_groups(cls,prog_group:str,db_conn):
        pxids = DtAMSFEM.get_pxid_from_metadata(program_group=[prog_group],program_code=[],db_conn=db_conn)
        
        filter_set = set()

        for k,s in pxids.items():
            filter_set.update(s)

        if not pxids:
            raise HTTPException(404,"PXIDs not found for submitted program group")
        data = db_conn.query(
            cls.DESCRIPTION_PLAIN_TEXT, cls.ACCOMPLISHMENT_TEXT, cls.ISSUE_TEXT).filter(
            cls.PXID.in_(filter_set)
            ).distinct().all() #assume only 1 key since input is a string, not list
        
        result = defaultdict(set)
        for desc,acc,iss in data:
            if desc: result["DESCRIPTION"].add(desc)
            if acc: result["ACCOMPLISHMENT"].add(acc)
            if iss: result["ISSUE_TEXT"].add(iss)
        
        return result