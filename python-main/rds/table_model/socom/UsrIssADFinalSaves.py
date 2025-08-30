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

from sqlalchemy.orm import (
    Mapped,
)

class UsrIssADFinalSaves(SOCOMBase):
    __tablename__ = 'USR_ISSUE_AD_FINAL_SAVES'
    # __abstract__ = False
    __table_args__ = {
        'schema': SCHEMA
    }

    ID: Mapped[int] = Column('ID', Integer, primary_key=True, autoincrement=True) 
    AD_RECOMENDATION: Mapped[str] = Column('AD_RECOMENDATION', Enum('Approve', 'Approve at Scale', 'Disapprove', name='ad_recommendation_enum'), nullable=False)  
    AD_USER_ID: Mapped[int] = Column('AD_USER_ID', Integer)
    CREATED_TIMESTAMP: Mapped[datetime.datetime] = Column('CREATED_TIMESTAMP',DateTime)
    UPDATED_TIMESTAMP: Mapped[datetime.datetime] = Column('UPDATED_TIMESTAMP',DateTime)
    EVENT_NAME: Mapped[str] = Column('EVENT_NAME', VARCHAR(100)) 
    POM_ID: Mapped[int] = Column('POM_ID', Integer)
    IS_DELETED: Mapped[int] = Column('IS_DELETED', SmallInteger)
    
    @classmethod
    async def get_ad_recommendations(cls, event_names: List[str],pom_id: int, db_conn):
       
        query = db_conn.query(
            cls.EVENT_NAME,
            cls.AD_RECOMENDATION
        ).filter(
            cls.EVENT_NAME.in_(event_names),
            cls.POM_ID == pom_id,
            cls.IS_DELETED == 0  
        ).distinct().all()

        result_dict = {row[0]: row[1] for row in query}

        recommendations = [
            {"EVENT_NAME": event, "AD_RECOMENDATION": result_dict.get(event, "Not Decided")}
            for event in event_names
        ]

        return recommendations

    @classmethod
    def get_approved_iss_events(
        cls,
        dt_iss_extract_table: str,
        db_conn,
        capability_sponsor_code: str = "ALL",
        assessment_area_code: str = "ALL",
        program_group: str = "ALL"
    ):
        """
        Return PROGRAM_CODE, PROGRAM_NAME, CAPABILITY_SPONSOR_CODE, ASSESSMENT_AREA_CODE,
        PROGRAM_GROUP, FISCAL_YEAR, DELTA_AMT, EVENT_NAME, AD_RECOMENDATION
        """
        filters = []
        if capability_sponsor_code != "ALL":
            filters.append(f"D.CAPABILITY_SPONSOR_CODE = '{capability_sponsor_code}'")
        if assessment_area_code != "ALL":
            filters.append(f"D.ASSESSMENT_AREA_CODE = '{assessment_area_code}'")
        if program_group != "ALL":
            filters.append(f"D.PROGRAM_GROUP = '{program_group}'")

        where_clause = " AND ".join(filters) if filters else "1=1"

        sql = f"""
            SELECT 
                D.PROGRAM_CODE,
                LUT.PROGRAM_NAME,
                D.CAPABILITY_SPONSOR_CODE,
                D.ASSESSMENT_AREA_CODE,
                D.PROGRAM_GROUP,
                D.FISCAL_YEAR,
                D.DELTA_AMT,
                D.EVENT_NAME,
                Z.AD_RECOMENDATION
            FROM {SCHEMA}.USR_ISSUE_AD_FINAL_SAVES Z
            JOIN {dt_iss_extract_table} D ON Z.EVENT_NAME = D.EVENT_NAME
            LEFT JOIN {SCHEMA}.LOOKUP_PROGRAM_DETAIL LUT
                ON D.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                AND D.PROGRAM_CODE = LUT.PROGRAM_CODE
            WHERE {where_clause}
            AND Z.IS_DELETED = 0
        """
        return db_conn.execute(text(sql)).fetchall()
     