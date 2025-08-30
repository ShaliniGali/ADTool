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
    case,
    tuple_,

)

from sqlalchemy.orm import (
    Mapped,
)

from typing import List,Dict,Any


class DtBudgetExecution(SOCOMBase):
    __tablename__ ="DT_BUDGET_EXECUTION"
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
    SUM_ACTUALS: Mapped[str] = Column('SUM_ACTUALS',Integer)
    SUM_ENT: Mapped[str] = Column('SUM_ENT',Integer)
    SUM_PB: Mapped[str] = Column('SUM_PB',Integer)
    

    @classmethod
    def aggregate_sums(cls, model, group_by_cols: List[str], db_conn):
        """
        Perform aggregation of PBYY columns with GROUP BY and optional filtering.

        :param db_conn: Active SQLAlchemy session (db_conn.query(...))
        :param group_by_cols: List of columns to group by.
        :param filters: Dictionary of filters {column_name: value}.
        :return: Query results as a list of tuples.
        """
        # Convert column names to ORM column objects
        from rds.table_model.socom.DtAMSFEM import DtAMSFEM #avoid circular import 

        group_by_fields = [getattr(cls, col) for col in group_by_cols]
        query = db_conn.query(*group_by_fields, cls.SUM_ACTUALS,cls.SUM_ENT,cls.SUM_PB) #get the base query first 

        # ams_ppbes_link = DtAMSFEM.get_ppbes_distinct_rows(db_conn)
        ams_ppbes_subquery =  (db_conn.query(
                DtAMSFEM.ELEMENT_OF_COST,
                DtAMSFEM.APPN,
                DtAMSFEM.PE,
                DtAMSFEM.FY
            ).distinct()
            ).subquery()
        #(eoc,resource category,program element, fy)
        #apply ppbes-ams filter
        query = query.filter(
                    tuple_(
                        cls.EOC_CODE,
                        cls.RESOURCE_CATEGORY_CODE,
                        cls.OSD_PROGRAM_ELEMENT_CODE,
                        cls.FISCAL_YEAR
                    ).in_(ams_ppbes_subquery) & (cls.EXECUTION_MANAGER_CODE.like("SORDAC%"))) #base
        
        # Apply filters if provided
        filters = model.dict() #{"ATTRIBUTE":[filters]}

        for col, values in filters.items():
            if values: #None/empty list for no filter
                query = query.filter(getattr(cls, col).in_(values))
        #applying the group by after filtering
        sum_actuals = func.coalesce(cast(func.sum(cls.SUM_ACTUALS),Integer), 0)
        sum_ent = func.coalesce(cast(func.sum(cls.SUM_ENT),Integer), 0)
        sum_pb = func.coalesce(cast(func.sum(cls.SUM_PB),Integer), 0)
        query = query.with_entities(*group_by_fields, sum_actuals, sum_ent, sum_pb).group_by(*group_by_fields)
        
        result = query.all()

        # Dynamically get column names for the output
        column_names = group_by_cols + ["SUM_ACTUALS","SUM_ENT","SUM_PB"]        
        # Convert tuples to dictionaries
        result = [dict(zip(column_names, row)) for row in result]

        if result and "FISCAL_YEAR" in result[0]:
            result = sorted(result,key= lambda x: x["FISCAL_YEAR"])
        return result

    @classmethod
    def get_pb_dash_lines(cls,db_conn):
        subquery = db_conn.query(
                cls.FISCAL_YEAR,
                (case(
                (func.sum(cls.SUM_ACTUALS).is_(None), 1), 
                else_=0
            )).label("DASHED_LINE")
        ).group_by(cls.FISCAL_YEAR).subquery()
        data = db_conn.query(subquery).filter(subquery.c.DASHED_LINE > 0).all()

        return [year for year,dashed_line in data if year]
        