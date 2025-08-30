import os

from sqlalchemy import (
    MetaData,
    text,
)

from typing import List


from sqlalchemy import (
    Column, 
    Integer, 
    VARCHAR,
    SmallInteger,
    and_,
    func,
    DateTime,
    Boolean,
    insert,
    inspect,
    tuple_
)

from sqlalchemy.dialects.mysql import insert as mysql_insert

from sqlalchemy.orm import (
    Mapped,
)
from sqlalchemy import delete

from fastapi import HTTPException
import pandas as pd
import datetime

from sqlalchemy.ext.declarative import declarative_base
from api.internal.conn import get_socom_session



SOCOMBase = declarative_base()
METADATA = MetaData()


SCHEMA = os.environ.get("SOCOM_UI", "SOCOM_UI") 

def get_all_socom_tables():
    query = text("""
        SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = :schema;

    """)
    socom_session = next(get_socom_session())
    tables = socom_session.execute(query,{"schema": SCHEMA})
    tables = [t[0] for t in tables]
    return tables

def map_dirty_to_final_rows(dirty_row, orm_model):
    return orm_model(
        # Mapping existing row
        PROGRAM_ID = dirty_row.PROGRAM_ID,
        ASSESSMENT_AREA_CODE = dirty_row.ASSESSMENT_AREA_CODE,
        BUDGET_ACTIVITY_CODE = dirty_row.BUDGET_ACTIVITY_CODE,
        BUDGET_ACTIVITY_NAME = dirty_row.BUDGET_ACTIVITY_NAME,
        BUDGET_SUB_ACTIVITY_CODE = dirty_row.BUDGET_SUB_ACTIVITY_CODE,
        BUDGET_SUB_ACTIVITY_NAME = dirty_row.BUDGET_SUB_ACTIVITY_NAME,
        CAPABILITY_SPONSOR_CODE = dirty_row.CAPABILITY_SPONSOR_CODE,
        DELTA_AMT = dirty_row.DELTA_AMT,
        DELTA_O2B_AMT = dirty_row.DELTA_O2B_AMT,
        DELTA_OCO_AMT = dirty_row.DELTA_OCO_AMT,
        EOC_CODE = dirty_row.EOC_CODE,
        EVENT_DATE = dirty_row.EVENT_DATE,
        EVENT_JUSTIFICATION = dirty_row.EVENT_JUSTIFICATION,
        EVENT_NAME = dirty_row.EVENT_NAME,
        EVENT_STATUS = dirty_row.EVENT_STATUS,
        EVENT_STATUS_COMMENT = dirty_row.EVENT_STATUS_COMMENT,
        EVENT_TITLE = dirty_row.EVENT_TITLE,
        EVENT_TYPE = dirty_row.EVENT_TYPE,
        EVENT_USER = dirty_row.EVENT_USER,
        EXECUTION_MANAGER_CODE = dirty_row.EXECUTION_MANAGER_CODE,
        FISCAL_YEAR = dirty_row.FISCAL_YEAR,
        LINE_ITEM_CODE = dirty_row.LINE_ITEM_CODE,
        O2B_AMT = dirty_row.O2B_AMT,
        OCO_AMT = dirty_row.OCO_AMT,
        OSD_PROGRAM_ELEMENT_CODE = dirty_row.OSD_PROGRAM_ELEMENT_CODE,
        POM_POSITION_CODE = dirty_row.POM_POSITION_CODE,
        POM_SPONSOR_CODE = dirty_row.POM_SPONSOR_CODE,
        PROGRAM_CODE = dirty_row.PROGRAM_CODE,
        PROGRAM_GROUP = dirty_row.PROGRAM_GROUP,
        PROP_AMT = dirty_row.PROP_AMT,
        PROP_O2B_AMT = dirty_row.PROP_O2B_AMT,
        PROP_OCO_AMT = dirty_row.PROP_OCO_AMT,
        RDTE_PROJECT_CODE = dirty_row.RDTE_PROJECT_CODE,
        RESOURCE_CATEGORY_CODE = dirty_row.RESOURCE_CATEGORY_CODE,
        RESOURCE_K = dirty_row.RESOURCE_K,
        SPECIAL_PROJECT_CODE = dirty_row.SPECIAL_PROJECT_CODE,
        SUB_ACTIVITY_GROUP_CODE = dirty_row.SUB_ACTIVITY_GROUP_CODE,
        SUB_ACTIVITY_GROUP_NAME = dirty_row.SUB_ACTIVITY_GROUP_NAME
    )

class DtPositionBase(SOCOMBase):
    """base table for DT_ISS_202x, DT_ZBT_202x, DT_EXT_202x,..."""
    __abstract__ = True
    __table_args__={
        'schema': SCHEMA
    }
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PROGRAM_ID: Mapped[str] = Column("PROGRAM_ID",VARCHAR(128))
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    PROGRAM_CODE: Mapped[str] = Column('PROGRAM_CODE',VARCHAR(11))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    POM_POSITION_CODE: Mapped[str] = Column('POM_POSITION_CODE',VARCHAR(9))
    POM_SPONSOR_CODE: Mapped[str] = Column('POM_SPONSOR_CODE',VARCHAR(13))
    EOC_CODE: Mapped[str] = Column('EOC_CODE',VARCHAR(15))
    RESOURCE_CATEGORY_CODE: Mapped[str] = Column('RESOURCE_CATEGORY_CODE',VARCHAR(8))
    FISCAL_YEAR: Mapped[int] = Column('FISCAL_YEAR',SmallInteger)
    RESOURCE_K: Mapped[str] = Column('RESOURCE_K',Integer)
    ADJUSTMENT_K: Mapped[int] = Column('ADJUSTMENT_K',Integer)
    ASSESSMENT_AREA_CODE: Mapped[str] = Column('ASSESSMENT_AREA_CODE',VARCHAR(1))
    BASE_K: Mapped[int] = Column('BASE_K',Integer)
    BUDGET_ACTIVITY_CODE: Mapped[str] = Column('BUDGET_ACTIVITY_CODE',VARCHAR(1))
    BUDGET_ACTIVITY_NAME: Mapped[str] = Column('BUDGET_ACTIVITY_NAME',VARCHAR(30))
    BUDGET_SUB_ACTIVITY_CODE: Mapped[str] = Column('BUDGET_SUB_ACTIVITY_CODE',VARCHAR(2))
    BUDGET_SUB_ACTIVITY_NAME: Mapped[str] = Column('BUDGET_SUB_ACTIVITY_NAME',VARCHAR(60))
    END_STRENGTH: Mapped[int] = Column('END_STRENGTH',Integer)
    EXECUTION_MANAGER_CODE: Mapped[str] = Column('EXECUTION_MANAGER_CODE',VARCHAR(13))
    LINE_ITEM_CODE: Mapped[str] = Column('LINE_ITEM_CODE',VARCHAR(13))
    OCO_OTHD_ADJUSTMENT_K: Mapped[int] = Column('OCO_OTHD_ADJUSTMENT_K',Integer)
    OCO_OTHD_K: Mapped[int] = Column('OCO_OTHD_K',Integer)
    OCO_TO_BASE_K: Mapped[int] = Column('OCO_TO_BASE_K',Integer)
    OSD_PROGRAM_ELEMENT_CODE: Mapped[str] = Column('OSD_PROGRAM_ELEMENT_CODE',VARCHAR(10))
    RDTE_PROJECT_CODE: Mapped[str] = Column('RDTE_PROJECT_CODE',VARCHAR(8))
    SPECIAL_PROJECT_CODE: Mapped[int] = Column('SPECIAL_PROJECT_CODE',SmallInteger)
    SUB_ACTIVITY_GROUP_CODE: Mapped[str] = Column('SUB_ACTIVITY_GROUP_CODE',VARCHAR(4))
    SUB_ACTIVITY_GROUP_NAME: Mapped[str] = Column('SUB_ACTIVITY_GROUP_NAME',VARCHAR(60))
    WORK_YEARS: Mapped[int] = Column('WORK_YEARS',Integer)        

    @classmethod
    def get_resource_k_by_pids(cls,program_ids,db_conn):
        """
        Given a list of program ids, retrieve the eoc funding for them in the finest detail, by fiscal year
        """
        query = db_conn.query(
            cls.PROGRAM_ID.label("ID"),
            cls.PROGRAM_CODE,
            cls.PROGRAM_GROUP,
            cls.ASSESSMENT_AREA_CODE,            
            func.coalesce(cls.EOC_CODE, 'DEFAULT_EOC').label('EOC_CODE'),
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            func.coalesce(cls.RESOURCE_CATEGORY_CODE, 'DEFAULT_CATEGORY').label('RESOURCE_CATEGORY_CODE'),
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.EXECUTION_MANAGER_CODE,  
            cls.FISCAL_YEAR,
            func.sum(func.coalesce(cls.RESOURCE_K, 0)).label('RESOURCE_K')
        ).filter(
            cls.PROGRAM_ID.in_(program_ids)
        ).group_by(
            cls.PROGRAM_ID,cls.FISCAL_YEAR
        ).order_by(
            cls.PROGRAM_CODE,
            cls.EOC_CODE,
            cls.FISCAL_YEAR
        )
        return query.all()
    


class DtExtractBase(SOCOMBase):
    __abstract__ = True
    __table_args__={
        'schema': SCHEMA
    }
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PROGRAM_ID: Mapped[str] = Column('PROGRAM_ID',VARCHAR(128))
    ASSESSMENT_AREA_CODE: Mapped[str] = Column('ASSESSMENT_AREA_CODE',VARCHAR(1))
    BUDGET_ACTIVITY_CODE: Mapped[str] = Column('BUDGET_ACTIVITY_CODE',VARCHAR(1))
    BUDGET_ACTIVITY_NAME: Mapped[str] = Column('BUDGET_ACTIVITY_NAME',VARCHAR(30))
    BUDGET_SUB_ACTIVITY_CODE: Mapped[str] = Column('BUDGET_SUB_ACTIVITY_CODE',VARCHAR(2))
    BUDGET_SUB_ACTIVITY_NAME: Mapped[str] = Column('BUDGET_SUB_ACTIVITY_NAME',VARCHAR(60))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    DELTA_AMT: Mapped[int] = Column('DELTA_AMT',Integer)
    DELTA_O2B_AMT: Mapped[int] = Column('DELTA_O2B_AMT',Integer)
    DELTA_OCO_AMT: Mapped[int] = Column('DELTA_OCO_AMT',Integer)
    EOC_CODE: Mapped[str] = Column('EOC_CODE',VARCHAR(15))
    EVENT_DATE: Mapped[datetime.datetime] = Column('EVENT_DATE',DateTime)
    EVENT_JUSTIFICATION: Mapped[str] = Column('EVENT_JUSTIFICATION',VARCHAR(500))
    EVENT_NAME: Mapped[str] = Column('EVENT_NAME',VARCHAR(60))
    EVENT_STATUS: Mapped[str] = Column('EVENT_STATUS',VARCHAR(21))
    EVENT_STATUS_COMMENT: Mapped[str] = Column('EVENT_STATUS_COMMENT',VARCHAR(30))
    EVENT_TITLE: Mapped[str] = Column('EVENT_TITLE',VARCHAR(200))
    EVENT_TYPE: Mapped[str] = Column('EVENT_TYPE',VARCHAR(3))
    EVENT_USER: Mapped[str] = Column('EVENT_USER',VARCHAR(60))
    EXECUTION_MANAGER_CODE: Mapped[str] = Column('EXECUTION_MANAGER_CODE',VARCHAR(13))
    FISCAL_YEAR: Mapped[int] = Column('FISCAL_YEAR',SmallInteger)
    LINE_ITEM_CODE: Mapped[str] = Column('LINE_ITEM_CODE',VARCHAR(13))
    O2B_AMT: Mapped[int] = Column('O2B_AMT',Integer)
    OCO_AMT: Mapped[int] = Column('OCO_AMT',Integer)
    OSD_PROGRAM_ELEMENT_CODE: Mapped[str] = Column('OSD_PROGRAM_ELEMENT_CODE',VARCHAR(10))
    POM_POSITION_CODE: Mapped[str] = Column('POM_POSITION_CODE',VARCHAR(9))
    POM_SPONSOR_CODE: Mapped[str] = Column('POM_SPONSOR_CODE',VARCHAR(13))
    PROGRAM_CODE: Mapped[str] = Column('PROGRAM_CODE',VARCHAR(11))
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    PROP_AMT: Mapped[int] = Column('PROP_AMT',Integer)
    PROP_O2B_AMT: Mapped[int] = Column('PROP_O2B_AMT',Integer)
    PROP_OCO_AMT: Mapped[int] = Column('PROP_OCO_AMT',Integer)
    RDTE_PROJECT_CODE: Mapped[str] = Column('RDTE_PROJECT_CODE',VARCHAR(8))
    RESOURCE_CATEGORY_CODE: Mapped[str] = Column('RESOURCE_CATEGORY_CODE',VARCHAR(8))
    RESOURCE_K: Mapped[str] = Column('RESOURCE_K',Integer)
    SPECIAL_PROJECT_CODE: Mapped[int] = Column('SPECIAL_PROJECT_CODE',SmallInteger)
    SUB_ACTIVITY_GROUP_CODE: Mapped[str] = Column('SUB_ACTIVITY_GROUP_CODE',VARCHAR(4))
    SUB_ACTIVITY_GROUP_NAME: Mapped[str] = Column('SUB_ACTIVITY_GROUP_NAME',VARCHAR(60))

    @classmethod
    def get_prog_scores_excel_download(cls, acc_list: List, prog_group_list: List, db_conn):
        #assuming the DT tables uploaded verified against the LP table already
        query = db_conn.query(cls.PROGRAM_ID).group_by(cls.PROGRAM_ID).having(func.sum(cls.DELTA_AMT) > 0)
        
        # Apply filters only if lists are non-empty
        if acc_list and len(acc_list) > 0:
            query = query.filter(cls.ASSESSMENT_AREA_CODE.in_(acc_list))
        
        if prog_group_list and len(prog_group_list) > 0:
            query = query.filter(cls.PROGRAM_GROUP.in_(prog_group_list))

        data = query.all()
        print(data)
        return data
    @classmethod
    def get_delta_amt_by_pids(cls,program_ids,db_conn):
        """
        Given a list of program ids, retrieve the event funding for them in the finest detail, by fiscal year
        """
        query = db_conn.query(
            cls.PROGRAM_ID.label("ID"),
            cls.PROGRAM_CODE,
            cls.PROGRAM_GROUP,
            cls.ASSESSMENT_AREA_CODE,            
            func.coalesce(cls.EOC_CODE, 'DEFAULT_EOC').label('EOC_CODE'),
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            func.coalesce(cls.RESOURCE_CATEGORY_CODE, 'DEFAULT_CATEGORY').label('RESOURCE_CATEGORY_CODE'),
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.EXECUTION_MANAGER_CODE,
            cls.EVENT_NAME,  
            cls.FISCAL_YEAR,
            func.sum(func.coalesce(cls.DELTA_AMT, 0)).label('DELTA_AMT')
        ).filter(
            cls.PROGRAM_ID.in_(program_ids)
        ).group_by(
            cls.PROGRAM_ID,cls.FISCAL_YEAR
        ).order_by(
            cls.PROGRAM_CODE,
            cls.EOC_CODE,
            cls.FISCAL_YEAR
        )
        return query.all()
    
    @classmethod
    async def get_event_summary(cls,event_name:str,db_conn):
        if not event_name:
            raise HTTPException(422,"User needs to provide the event name")
        
        query = db_conn.query(
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
	        cls.EOC_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.SPECIAL_PROJECT_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.FISCAL_YEAR,
            func.sum(cls.DELTA_AMT).label("DELTA_AMT")
        ).filter(cls.EVENT_NAME==event_name and cls.DELTA_AMT != 0).group_by(
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE, 
            cls.EOC_CODE, 
            cls.CAPABILITY_SPONSOR_CODE, 
            cls.ASSESSMENT_AREA_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.SPECIAL_PROJECT_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.FISCAL_YEAR
        ).order_by(cls.EOC_CODE.asc(),cls.FISCAL_YEAR.asc()).all()
        
        result = [
            {
                "PROGRAM_GROUP": row[0],
                "PROGRAM_CODE":row[1],
                "EOC_CODE": row[2],
                "CAPABILITY_SPONSOR_CODE": row[3],
                "ASSESSMENT_AREA_CODE": row[4],
                "RESOURCE_CATEGORY_CODE": row[5],
                "SPECIAL_PROJECT_CODE": row[6],
                "OSD_PROGRAM_ELEMENT_CODE": row[7],
                "FISCAL_YEAR": row[8],
                "DELTA_AMT": row[9]
            }
            for row in query
        ]
        return result
    
    @classmethod
    async def get_event_summary_list(cls,event_names:List[str],db_conn):
        if not event_names:
            raise HTTPException(422,"User needs to provide the event name")
        
        query = db_conn.query(
            cls.EVENT_NAME,
            cls.EVENT_TITLE,
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
	        cls.EOC_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.SPECIAL_PROJECT_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.FISCAL_YEAR,
            func.sum(cls.DELTA_AMT).label("DELTA_AMT")
        ).filter(cls.EVENT_NAME.in_(event_names),
                cls.DELTA_AMT != 0).group_by(
            cls.EVENT_NAME,
            cls.EVENT_TITLE,
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE, 
            cls.EOC_CODE, 
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE, 
            cls.ASSESSMENT_AREA_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.SPECIAL_PROJECT_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.FISCAL_YEAR
        ).order_by(cls.EOC_CODE.asc(),cls.FISCAL_YEAR.asc()).all()
        
        result = [
            {
                "EVENT_NAME": row[0],
                "EVENT_TITLE": row[1],
                "PROGRAM_GROUP": row[2],
                "PROGRAM_CODE":row[3],
                "EOC_CODE": row[4],
                "POM_SPONSOR_CODE":row[5],
                "CAPABILITY_SPONSOR_CODE": row[6],
                "ASSESSMENT_AREA_CODE": row[7],
                "RESOURCE_CATEGORY_CODE": row[8],
                "SPECIAL_PROJECT_CODE": row[9],
                "OSD_PROGRAM_ELEMENT_CODE": row[10],
                "FISCAL_YEAR": row[11],
                "DELTA_AMT": row[12]
            }
            for row in query
        ]
        
        # print(result)
        return result
    
    @classmethod
    async def get_distinct_fiscal_years(cls,db_conn):

        query = db_conn.query(
            cls.FISCAL_YEAR
        ).distinct().all()
        
        result = [str(year[0]) for year in query]
        return result

    @classmethod
    async def get_event_title_from_name(cls,event_names:List[str],db_conn):
        data = db_conn.query(
            cls.EVENT_NAME,
            cls.EVENT_TITLE
        ).filter(cls.EVENT_NAME.in_(event_names)).distinct().all()
        # print(data)
        
        if not data:
            raise HTTPException(404,f"No event found with event name: '{event_names}'")
        return data

    @classmethod
    async def get_event_justification_from_name(cls,event_names:List[str],db_conn):
        data = db_conn.query(
            cls.EVENT_NAME,
            cls.EVENT_JUSTIFICATION
        ).filter(cls.EVENT_NAME.in_(event_names)).distinct().all()
        
        # print(data)
        
        if not data:
            raise HTTPException(404,f"No event found with event name: '{event_names}'")
        return data

    @classmethod
    def get_event_names_from_program_ids(cls, LookupProgramModel, program_ids: List[str], db_conn):
        #given a list of program ids -> return event names
        query = db_conn.query(
            LookupProgramModel.ID, 
            cls.EVENT_NAME,
        ).join(
            LookupProgramModel,
            (LookupProgramModel.PROGRAM_CODE == cls.PROGRAM_CODE) &
            (LookupProgramModel.CAPABILITY_SPONSOR_CODE == cls.CAPABILITY_SPONSOR_CODE) &
            (LookupProgramModel.POM_SPONSOR_CODE == cls.POM_SPONSOR_CODE) &
            (LookupProgramModel.ASSESSMENT_AREA_CODE == cls.ASSESSMENT_AREA_CODE),
            isouter=True  # LEFT JOIN            
        ).filter(LookupProgramModel.ID.in_(program_ids)).filter(cls.DELTA_AMT > 0).distinct()
        #unique set of (id,event_name) pairs

        result = {} #,mapper
        for prog_id,event_name in query.all():
            if event_name not in result:
                result[event_name] = set()
            result[event_name].add(prog_id)

        return result


class DtExtractDirtyBase(SOCOMBase):
    __abstract__ = True
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PROGRAM_ID: Mapped[str] = Column('PROGRAM_ID',VARCHAR(128),nullable=False,)
    ASSESSMENT_AREA_CODE: Mapped[str] = Column('ASSESSMENT_AREA_CODE',VARCHAR(1))
    BUDGET_ACTIVITY_CODE: Mapped[str] = Column('BUDGET_ACTIVITY_CODE',VARCHAR(1))
    BUDGET_ACTIVITY_NAME: Mapped[str] = Column('BUDGET_ACTIVITY_NAME',VARCHAR(30))
    BUDGET_SUB_ACTIVITY_CODE: Mapped[str] = Column('BUDGET_SUB_ACTIVITY_CODE',VARCHAR(2))
    BUDGET_SUB_ACTIVITY_NAME: Mapped[str] = Column('BUDGET_SUB_ACTIVITY_NAME',VARCHAR(60))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    DELTA_AMT: Mapped[int] = Column('DELTA_AMT',Integer)
    DELTA_O2B_AMT: Mapped[int] = Column('DELTA_O2B_AMT',Integer)
    DELTA_OCO_AMT: Mapped[int] = Column('DELTA_OCO_AMT',Integer)
    EOC_CODE: Mapped[str] = Column('EOC_CODE',VARCHAR(15))
    EVENT_DATE: Mapped[datetime.datetime] = Column('EVENT_DATE',DateTime)
    EVENT_JUSTIFICATION: Mapped[str] = Column('EVENT_JUSTIFICATION',VARCHAR(500))
    EVENT_NAME: Mapped[str] = Column('EVENT_NAME',VARCHAR(60))
    EVENT_NUMBER: Mapped[int] = Column('EVENT_NUMBER',Integer)
    EVENT_STATUS: Mapped[str] = Column('EVENT_STATUS',VARCHAR(21))
    EVENT_STATUS_COMMENT: Mapped[str] = Column('EVENT_STATUS_COMMENT',VARCHAR(30))
    EVENT_TITLE: Mapped[str] = Column('EVENT_TITLE',VARCHAR(200))
    EVENT_TYPE: Mapped[str] = Column('EVENT_TYPE',VARCHAR(3))
    EVENT_USER: Mapped[str] = Column('EVENT_USER',VARCHAR(60))
    EXECUTION_MANAGER_CODE: Mapped[str] = Column('EXECUTION_MANAGER_CODE',VARCHAR(13))
    FISCAL_YEAR: Mapped[int] = Column('FISCAL_YEAR',SmallInteger)
    LINE_ITEM_CODE: Mapped[str] = Column('LINE_ITEM_CODE',VARCHAR(13))
    O2B_AMT: Mapped[int] = Column('O2B_AMT',Integer)
    OCO_AMT: Mapped[int] = Column('OCO_AMT',Integer)
    OSD_PROGRAM_ELEMENT_CODE: Mapped[str] = Column('OSD_PROGRAM_ELEMENT_CODE',VARCHAR(10))
    POM_POSITION_CODE: Mapped[str] = Column('POM_POSITION_CODE',VARCHAR(9))
    POM_SPONSOR_CODE: Mapped[str] = Column('POM_SPONSOR_CODE',VARCHAR(13))
    PROGRAM_CODE: Mapped[str] = Column('PROGRAM_CODE',VARCHAR(11))
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    PROP_AMT: Mapped[int] = Column('PROP_AMT',Integer)
    PROP_O2B_AMT: Mapped[int] = Column('PROP_O2B_AMT',Integer)
    PROP_OCO_AMT: Mapped[int] = Column('PROP_OCO_AMT',Integer)
    RDTE_PROJECT_CODE: Mapped[str] = Column('RDTE_PROJECT_CODE',VARCHAR(8))
    RESOURCE_CATEGORY_CODE: Mapped[str] = Column('RESOURCE_CATEGORY_CODE',VARCHAR(8))
    RESOURCE_K: Mapped[str] = Column('RESOURCE_K',Integer)
    SPECIAL_PROJECT_CODE: Mapped[int] = Column('SPECIAL_PROJECT_CODE',SmallInteger)
    SUB_ACTIVITY_GROUP_CODE: Mapped[str] = Column('SUB_ACTIVITY_GROUP_CODE',VARCHAR(4))
    SUB_ACTIVITY_GROUP_NAME: Mapped[str] = Column('SUB_ACTIVITY_GROUP_NAME',VARCHAR(60))
    CREATED_BY: Mapped[int] = Column('CREATED_BY',Integer,nullable=False)
    UPDATED_BY: Mapped[int] = Column('UPDATED_BY',Integer)
    CREATED_DATETIME: Mapped[datetime.datetime] = Column('CREATED_DATETIME',DateTime,default=datetime.datetime.utcnow, onupdate=datetime.datetime.utcnow,nullable=False)
    UPDATED_DATETIME: Mapped[datetime.datetime] = Column('UPDATED_DATETIME',DateTime)
    SUBMISSION_STATUS:Mapped[str] = Column('SUBMISSION_STATUS',default='PENDING')
    IS_ACTIVE: Mapped[bool] = Column('IS_ACTIVE',Boolean,default=0, onupdate=datetime.datetime.utcnow,nullable=False)

    @classmethod
    def upsert_dataframe(cls, df: pd.DataFrame, db_conn):
        """
        Perform upsert into table using PROGRAM_ID and FISCAL_YEAR as keys.

        Args:
            df (pd.DataFrame): Input DataFrame.
            db_conn (Session): SQLAlchemy session.
        """
        if df.empty:
            return

        # Drop rows missing required keys
        df = df.dropna(subset=["PROGRAM_ID", "FISCAL_YEAR"])
        df["UPDATED_DATETIME"] = None
        df["UPDATED_BY"] = None
        df.columns = [str(c) for c in df.columns]
        df = df.astype("object") #convert to obj, becuase changing nan -> none doesnt work for float cols
        df = df.loc[:, df.columns.notna()]  # Drop NaN column names
        #pd.where(condition to keep, value_if_false) 
        df = df.where(pd.notnull(df), None) #checked

        # Filter columns to match the model
        model_columns = {col.name for col in inspect(cls).columns}
        df = df[[col for col in df.columns if col in model_columns]]

        # Convert to list of dicts
        records = df.to_dict(orient="records")
        
        # Build MySQL-specific insert statement
        stmt = mysql_insert(cls).values(records)

        # Define columns to update (exclude keys and auto fields)
        conflict_keys = {"PK_DUMMY", "PROGRAM_ID", "FISCAL_YEAR"}
        update_dict = {
            col.name: stmt.inserted[col.name]
            for col in cls.__table__.columns
            if col.name not in conflict_keys
        }

        # Add ON DUPLICATE KEY UPDATE clause
        #on duplicate key -> update
        upsert_stmt = stmt.on_duplicate_key_update(**update_dict)

        # Execute and commit
        result = db_conn.execute(upsert_stmt)

        db_conn.commit()
        return result
    
    @classmethod
    async def upsert_table_rows(cls, extract_orm, db_conn):

        rows_to_insert = db_conn.query(cls).filter(cls.IS_ACTIVE.is_(True)).filter(cls.SUBMISSION_STATUS=='APPROVED').distinct().all()
        if not rows_to_insert:
            raise HTTPException(status_code=201, detail="No active row found in dirty table")
        
        # delete the rows in final table where the rows is active in dirty table
        rows_to_delete = []
        for row in rows_to_insert:
            rows_to_delete.append( (row.PROGRAM_ID,  row.FISCAL_YEAR))

        
        stmt = delete(extract_orm).where(
            tuple_(extract_orm.PROGRAM_ID, extract_orm.FISCAL_YEAR).in_(rows_to_delete)
        )
        db_conn.execute(stmt)


        # Map and insert into final table
        new_rows = [map_dirty_to_final_rows(row, extract_orm) for row in rows_to_insert]
        db_conn.add_all(new_rows)

        db_conn.commit()

        return True