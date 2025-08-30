from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

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
    tuple_,
)

from sqlalchemy.orm import (
    Mapped,
    aliased,
)

from typing import List,Dict,Any
import json

from api.internal.redis_cache import RedisController
from socom.metadata import get_cap_sponsor_category

class DtPBComparison(SOCOMBase):
    __tablename__ ="DT_PB_COMPARISON"
    __table_args__={
        'schema': SCHEMA
    }
    
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    ASSESSMENT_AREA_CODE: Mapped[str] = Column("ASSESSMENT_AREA_CODE",VARCHAR(1))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    EOC_CODE: Mapped[str] = Column('EOC_CODE',VARCHAR(15))
    EXECUTION_MANAGER_CODE: Mapped[str] = Column('EXECUTION_MANAGER_CODE',VARCHAR(13))
    FISCAL_YEAR: Mapped[int] = Column('FISCAL_YEAR',SmallInteger)
    OSD_PROGRAM_ELEMENT_CODE: Mapped[str] = Column('OSD_PROGRAM_ELEMENT_CODE',VARCHAR(10))
    PROGRAM_CODE: Mapped[str] = Column('PROGRAM_CODE',VARCHAR(11))
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    RESOURCE_CATEGORY_CODE: Mapped[str] = Column('RESOURCE_CATEGORY_CODE',VARCHAR(8))

    #need to overload "PBYY" columns
    @classmethod
    def add_pb_column(cls,cols:List[str]):
        """
        Dynamically add PBYY columns to the ORM model
        """
        for col in cols:
            if col not in cls.__table__.columns: #cannot add the same column over and over
                setattr(cls, col, Column(col, Numeric(12, 2)))
    

    @classmethod
    def aggregate_pb_sums(cls, model, group_by_cols: List[str], pb_cols: List[str],db_conn):
        from rds.table_model.socom.DtAMSFEM import DtAMSFEM
        """
        Perform aggregation of PBYY columns with GROUP BY and optional filtering.

        :param db_conn: Active SQLAlchemy session (db_conn.query(...))
        :param group_by_cols: List of columns to group by.
        :param pb_cols: List of PBYY columns to sum.
        :param filters: Dictionary of filters {column_name: value}.
        :return: Query results as a list of tuples.
        """
        # Convert column names to ORM column objects
        cls.add_pb_column(pb_cols)


        group_by_fields = [getattr(cls, col) for col in group_by_cols]
        pb_sums = [func.coalesce(func.sum(getattr(cls, col)),0).label(col) for col in pb_cols]

        
        ams_ppbes_subquery = (db_conn.query(
                DtAMSFEM.ELEMENT_OF_COST,
                DtAMSFEM.APPN,
                DtAMSFEM.PE,
            ).distinct()
            ).subquery()
        #(eoc,resource category,program element)

        base_query = db_conn.query(*group_by_fields,*pb_sums).filter(
                    (tuple_(
                        cls.EOC_CODE,
                        cls.RESOURCE_CATEGORY_CODE,
                        cls.OSD_PROGRAM_ELEMENT_CODE,
                        # cls.FISCAL_YEAR
                    ).in_(ams_ppbes_subquery)) & (cls.EXECUTION_MANAGER_CODE.like("SORDAC%"))) #base
        filters = model.dict() #{"ATTRIBUTE":[filters]}
        for col, values in filters.items():
            if values: #None/empty list for no filter
                base_query = base_query.filter(getattr(cls, col).in_(values))

        query = base_query.group_by(*group_by_fields)
        result = query.all()
        
        column_names = group_by_cols + pb_cols
        result = [dict(zip(column_names,row)) for row in result]
        if result and "FISCAL_YEAR" in result[0]:
            result = sorted(result,key=lambda x:x["FISCAL_YEAR"])
        return result
    
    @classmethod
    def get_all_cap_sponsor_categories(cls,db_conn):
        """
        Retrieve all capability sponsor codes and their associated categories
        """
        data = db_conn.query(cls.CAPABILITY_SPONSOR_CODE,cls.RESOURCE_CATEGORY_CODE).distinct().all()
        result = {}
        for cap_sponsor_code,resource_category_code in data:
            cat_code = get_cap_sponsor_category(cap_sponsor_code)
            result[cap_sponsor_code] = cat_code
        
        return result

        
