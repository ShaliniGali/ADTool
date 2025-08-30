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
    Enum,
    text
)

from sqlalchemy.orm import Mapped
import pdb  

class UsrZbtADFinalSaves(SOCOMBase):
    __tablename__ = 'USR_ZBT_AD_FINAL_SAVES'
    # __abstract__ = False

    __table_args__ = {
        'schema': SCHEMA
    }

    ID: Mapped[int] = Column('ID', Integer, primary_key=True, autoincrement=True) 
    AD_RECOMENDATION: Mapped[str] = Column('AD_RECOMENDATION', Enum('Approve', 'Approve at Scale', 'Disapprove', name='ad_recommendation_enum'), nullable=False)  
    AD_USER_ID: Mapped[int] = Column('AD_USER_ID', Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP', DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP', DateTime)
    EVENT_NAME: Mapped[str] = Column('EVENT_NAME', VARCHAR(100)) 
    POM_ID: Mapped[int] = Column('POM_ID', Integer)
    IS_DELETED: Mapped[int] = Column('IS_DELETED', SmallInteger)
    
    @classmethod
    async def get_ad_recommendations(cls, event_names: List[str], pom_id: int, db_conn):
        query = db_conn.query(
            cls.EVENT_NAME,
            cls.AD_RECOMENDATION
        ).filter(
            cls.EVENT_NAME.in_(event_names),
            cls.POM_ID == pom_id,
            cls.IS_DELETED == 0  
        ).distinct().all()

        if not query:  # If there is no data
            return [{"EVENT_NAME": event, "AD_RECOMENDATION": "Not Decided"} for event in event_names]


        result_dict = {row[0]: row[1] for row in query}

        recommendations = [
            {"EVENT_NAME": event, "AD_RECOMENDATION": result_dict.get(event, "Not Decided")}
            for event in event_names
        ]

        return recommendations
    

    @classmethod
    def get_approved_zbt_events(
        cls, 
        dt_zbt_extract_table: str, 
        db_conn, 
        capability_sponsor_code: str = "ALL", 
        assessment_area_code: str = "ALL", 
        program_group: str = "ALL"
    ):
        def unwrap_if_list(val):
            if isinstance(val, (list, tuple)):
                return str(val[0]) if len(val) > 0 else None
            return str(val)

        approve_sql = f"""
            SELECT 
                D.PROGRAM_CODE,
                LUT.PROGRAM_NAME,
                D.CAPABILITY_SPONSOR_CODE,
                D.ASSESSMENT_AREA_CODE,
                D.PROGRAM_GROUP,
                D.FISCAL_YEAR,
                D.DELTA_AMT,
                Z.EVENT_NAME,
                Z.AD_RECOMENDATION
            FROM {SCHEMA}.USR_ZBT_AD_FINAL_SAVES Z
            JOIN {dt_zbt_extract_table} D ON Z.EVENT_NAME = D.EVENT_NAME
            LEFT JOIN {SCHEMA}.LOOKUP_PROGRAM_DETAIL LUT 
                ON D.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                AND D.PROGRAM_CODE = LUT.PROGRAM_CODE
                AND D.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                AND D.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                AND D.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE Z.IS_DELETED = 0
            AND Z.AD_RECOMENDATION IN ('Approve', 'Approve at Scale')
            AND LUT.PROGRAM_NAME IS NOT NULL
        """

        conditions = []
        if capability_sponsor_code != "ALL":
            conditions.append("D.CAPABILITY_SPONSOR_CODE = :capability_sponsor_code")
        if assessment_area_code != "ALL":
            conditions.append("D.ASSESSMENT_AREA_CODE = :assessment_area_code")
        if program_group != "ALL":
            conditions.append("D.PROGRAM_GROUP = :program_group")

        if conditions:
            approve_sql += " AND " + " AND ".join(conditions)

        stmt = text(approve_sql)

        params = {}
        if capability_sponsor_code != "ALL":
            params["capability_sponsor_code"] = unwrap_if_list(capability_sponsor_code)
        if assessment_area_code != "ALL":
            params["assessment_area_code"] = unwrap_if_list(assessment_area_code)
        if program_group != "ALL":
            params["program_group"] = unwrap_if_list(program_group)

        rows = db_conn.execute(stmt, params).fetchall()

        return rows

