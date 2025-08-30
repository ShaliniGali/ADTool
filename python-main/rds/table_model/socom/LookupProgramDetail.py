from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)
from typing import List
from fastapi import HTTPException

from sqlalchemy.inspection import inspect
from sqlalchemy import (
    Column, 
    VARCHAR,
    JSON,
    func,
    distinct,
)

from sqlalchemy.orm import (
    Mapped,
)

class LookupProgramDetailModel(SOCOMBase):
    __tablename__ = "LOOKUP_PROGRAM_DETAIL"
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',VARCHAR(40),primary_key=True)
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    PROGRAM_CODE: Mapped[str] = Column('PROGRAM_CODE',VARCHAR(11))
    PROGRAM_NAME: Mapped[str] = Column('PROGRAM_NAME',VARCHAR(60))
    PROGRAM_TYPE_CODE: Mapped[str] = Column('PROGRAM_TYPE_CODE',VARCHAR(1))
    PROGRAM_SUB_TYPE_CODE: Mapped[str] = Column('PROGRAM_SUB_TYPE_CODE',VARCHAR(5))
    PROGRAM_DESCRIPTION: Mapped[str] = Column('PROGRAM_DESCRIPTION',VARCHAR(10000))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    ASSESSMENT_AREA_CODE: Mapped[str] = Column('ASSESSMENT_AREA_CODE',VARCHAR(1))
    POM_SPONSOR_CODE: Mapped[str] = Column('POM_SPONSOR_CODE',VARCHAR(13))
    JCA_LV1_ID: Mapped[str] = Column('JCA_LV1_ID',VARCHAR(4))
    JCA_LV2_ID: Mapped[str] = Column('JCA_LV2_ID',VARCHAR(5))
    JCA_LV3_ID: Mapped[str] = Column('JCA_LV3_ID',VARCHAR(6))
    STORM_ID: Mapped[int] = Column('STORM_ID',VARCHAR(40))
    JCA:Mapped[JSON] = Column('JCA',JSON)
    KOP_KSP:Mapped[JSON] = Column('KOP_KSP',JSON)
    CGA:Mapped[JSON] = Column('CGA',JSON)    

    @classmethod
    def get_resource_k_from_progIds(cls,DtIssueModel,db_conn,ProgIds:List[str]):
        subquery = db_conn.query(
            distinct(cls.ID).label('ID'),
            cls.PROGRAM_NAME,
            cls.PROGRAM_GROUP, 
            cls.PROGRAM_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE).subquery()
        # print(subquery.c.keys())
        # print(subquery.all())
        # breakpoint()
        query = db_conn.query(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            DtIssueModel.ASSESSMENT_AREA_CODE,
            DtIssueModel.FISCAL_YEAR,
            func.sum(DtIssueModel.RESOURCE_K).label('RESOURCE_K')
        ).select_from(
            DtIssueModel
        ).join(
            subquery,
            (DtIssueModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtIssueModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtIssueModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtIssueModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtIssueModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE),
            isouter=True #left join
        ).group_by(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            DtIssueModel.POM_POSITION_CODE,
            DtIssueModel.FISCAL_YEAR,
            DtIssueModel.ASSESSMENT_AREA_CODE
        ).having(
            subquery.c.ID.in_(ProgIds)
        )
        
        return query.all()

    @classmethod
    def get_delta_amount_from_progIds(cls,DtIssExtractModel,db_conn,ProgIds:List[str]):
        subquery = db_conn.query(
            distinct(cls.ID).label('ID'),
            cls.PROGRAM_NAME,
            cls.PROGRAM_GROUP, 
            cls.PROGRAM_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE).subquery()
        # print(subquery.c.keys())
        # print(subquery.all())
        query = db_conn.query(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            DtIssExtractModel.POM_SPONSOR_CODE,
            DtIssExtractModel.CAPABILITY_SPONSOR_CODE,
            DtIssExtractModel.ASSESSMENT_AREA_CODE,
            DtIssExtractModel.FISCAL_YEAR,
            func.sum(DtIssExtractModel.DELTA_AMT).label('DELTA_AMT')
        ).select_from(
            DtIssExtractModel
        ).join(
            subquery,
            (DtIssExtractModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtIssExtractModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtIssExtractModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtIssExtractModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtIssExtractModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE),
            isouter=True #left join
        ).filter(
            DtIssExtractModel.DELTA_AMT > 0  #confirmed with client that they only show positive values. avoid optimizing negatives greedily
        ).group_by(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            DtIssExtractModel.POM_SPONSOR_CODE,
            DtIssExtractModel.CAPABILITY_SPONSOR_CODE,
            DtIssExtractModel.POM_POSITION_CODE,
            DtIssExtractModel.FISCAL_YEAR,
            DtIssExtractModel.ASSESSMENT_AREA_CODE
        ).having(
            subquery.c.ID.in_(ProgIds)
        )
        
        return query.all()


    @classmethod
    def get_eoc_funding_from_progIds(cls, DtIssueModel, db_conn, ProgIds: List[int]):
        # Define aliases
        # LUT = aliased(cls)
        # DT = aliased(DtIssueModel)

        # Create the subquery
        subquery = db_conn.query(
            cls.ID,
            cls.PROGRAM_CODE,
            cls.PROGRAM_GROUP,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
        ).filter(cls.ID.in_(ProgIds)).subquery()

        # Main query using the subquery
        query = db_conn.query(
            subquery.c.ID,
            DtIssueModel.PROGRAM_CODE,
            DtIssueModel.PROGRAM_GROUP,
            subquery.c.ASSESSMENT_AREA_CODE,            
            # DtIssueModel.EOC_CODE,
            func.coalesce(DtIssueModel.EOC_CODE, 'DEFAULT_EOC').label('EOC_CODE'),
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            # DtIssueModel.RESOURCE_CATEGORY_CODE,
            func.coalesce(DtIssueModel.RESOURCE_CATEGORY_CODE, 'DEFAULT_CATEGORY').label('RESOURCE_CATEGORY_CODE'),  
            DtIssueModel.FISCAL_YEAR,
            # func.sum(DtIssueModel.RESOURCE_K).label('RESOURCE_K'),
            func.sum(func.coalesce(DtIssueModel.RESOURCE_K, 0)).label('RESOURCE_K')
        ).select_from(
            subquery
        ).join(
            DtIssueModel,
            (DtIssueModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtIssueModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtIssueModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtIssueModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtIssueModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE),
            isouter=True
        ).filter(DtIssueModel.EOC_CODE.isnot(None)).group_by(
            subquery.c.PROGRAM_CODE,
            subquery.c.PROGRAM_GROUP,
            subquery.c.ASSESSMENT_AREA_CODE,
            DtIssueModel.EOC_CODE,
            DtIssueModel.RESOURCE_CATEGORY_CODE,
            DtIssueModel.FISCAL_YEAR
        ).order_by(
            DtIssueModel.PROGRAM_CODE,
            DtIssueModel.EOC_CODE,
            DtIssueModel.FISCAL_YEAR
        )
        # from sqlalchemy.dialects import mysql
        # print(query.statement.compile(dialect=mysql.dialect()))
        return query.all()
    
    @classmethod
    def get_metadata_iss_zbt(cls,DtModel,rk_non_zero,db_conn,**kwargs):
        """
        Type: DtModel refers to DtIssModel or DtZbtModel
        """
        #helper function
        def filter_by_key(query,key,col):

            if (key in kwargs['kwargs'] and kwargs['kwargs'][key]):

                return query.filter(col.in_(kwargs["kwargs"][key]))
            return query
        
        valid_cols = {col.key for col in inspect(cls).columns}
        valid_cols.add("EOC_CODE") 
        valid_cols.add("RESOURCE_CATEGORY_CODE") 
        for col in kwargs['kwargs'].keys():
            if col not in valid_cols:
                raise HTTPException("Invalid column name keys in the input")
        
        query = db_conn.query(
            cls.ID,
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
            cls.PROGRAM_NAME,
            cls.PROGRAM_TYPE_CODE,
            cls.PROGRAM_SUB_TYPE_CODE,
            cls.PROGRAM_DESCRIPTION,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.POM_SPONSOR_CODE,
            cls.JCA_LV1_ID,
            cls.JCA_LV2_ID,
            cls.JCA_LV3_ID,
            cls.STORM_ID,
            DtModel.EOC_CODE,
            DtModel.RESOURCE_CATEGORY_CODE
        ).join(
            DtModel,
            (DtModel.PROGRAM_GROUP == cls.PROGRAM_GROUP) &
            (DtModel.PROGRAM_CODE == cls.PROGRAM_CODE) &
            (DtModel.POM_SPONSOR_CODE == cls.POM_SPONSOR_CODE) &
            (DtModel.CAPABILITY_SPONSOR_CODE == cls.CAPABILITY_SPONSOR_CODE) &
            (DtModel.ASSESSMENT_AREA_CODE == cls.ASSESSMENT_AREA_CODE))
            # isouter=True) #left join
        # if "PROGRAM_GROUP" in kwargs and kwargs["PROGRAM_GROUP"]:
            # query.filter(cls.PROGRAM_GROUP.in_(kwargs["PROGRAM_GROUP"])
        query = filter_by_key(query,"ID",cls.ID)
        query = filter_by_key(query,"PROGRAM_GROUP",cls.PROGRAM_GROUP)
        query = filter_by_key(query,"PROGRAM_CODE",cls.PROGRAM_CODE)
        query = filter_by_key(query,"PROGRAM_NAME",cls.PROGRAM_NAME)
        query = filter_by_key(query,"PROGRAM_TYPE_CODE",cls.PROGRAM_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_SUB_TYPE_CODE",cls.PROGRAM_SUB_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_DESCRIPTION",cls.PROGRAM_DESCRIPTION)
        query = filter_by_key(query,"CAPABILITY_SPONSOR_CODE",cls.CAPABILITY_SPONSOR_CODE)
        query = filter_by_key(query,"ASSESSMENT_AREA_CODE",cls.ASSESSMENT_AREA_CODE)
        query = filter_by_key(query,"POM_SPONSOR_CODE",cls.POM_SPONSOR_CODE)
        query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV1_ID)
        query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV2_ID)
        query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV3_ID)
        query = filter_by_key(query,"STORM_ID",cls.STORM_ID)
        query = filter_by_key(query,"RESOURCE_CATEGORY_CODE",DtModel.RESOURCE_CATEGORY_CODE)
        query = filter_by_key(query,"EOC_CODE",DtModel.EOC_CODE)
        
        if rk_non_zero:
            query = query.filter(DtModel.RESOURCE_K > 0)
        return query.all()
    
    @classmethod
    def get_metadata_iss_zbt_extract(cls,DtExtractModel,rk_non_zero,db_conn,**kwargs):
        #helper function
        def filter_by_key(query,key,col):

            if (key in kwargs['kwargs'] and kwargs['kwargs'][key]):

                return query.filter(col.in_(kwargs["kwargs"][key]))
            return query
        
        query = db_conn.query(
            cls.ID,
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
            cls.PROGRAM_NAME,
            cls.PROGRAM_TYPE_CODE,
            cls.PROGRAM_SUB_TYPE_CODE,
            cls.PROGRAM_DESCRIPTION,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.POM_SPONSOR_CODE,
            cls.JCA_LV1_ID,
            cls.JCA_LV2_ID,
            cls.JCA_LV3_ID,
            cls.STORM_ID,
            DtExtractModel.EOC_CODE,
            DtExtractModel.RESOURCE_CATEGORY_CODE,
            DtExtractModel.EVENT_NAME,
            DtExtractModel.OSD_PROGRAM_ELEMENT_CODE,
            
        ).join(
            DtExtractModel,
            (DtExtractModel.PROGRAM_GROUP == cls.PROGRAM_GROUP) &
            (DtExtractModel.PROGRAM_CODE == cls.PROGRAM_CODE) &
            (DtExtractModel.POM_SPONSOR_CODE == cls.POM_SPONSOR_CODE) &
            (DtExtractModel.CAPABILITY_SPONSOR_CODE == cls.CAPABILITY_SPONSOR_CODE) &
            (DtExtractModel.ASSESSMENT_AREA_CODE == cls.ASSESSMENT_AREA_CODE)
        )
            # isouter=True) #left join
        # if "PROGRAM_GROUP" in kwargs and kwargs["PROGRAM_GROUP"]:
            # query.filter(cls.PROGRAM_GROUP.in_(kwargs["PROGRAM_GROUP"])
        # breakpoint()
        query = filter_by_key(query,"ID",cls.ID)
        query = filter_by_key(query,"PROGRAM_GROUP",cls.PROGRAM_GROUP)
        query = filter_by_key(query,"PROGRAM_CODE",cls.PROGRAM_CODE)
        query = filter_by_key(query,"PROGRAM_NAME",cls.PROGRAM_NAME)
        query = filter_by_key(query,"PROGRAM_TYPE_CODE",cls.PROGRAM_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_SUB_TYPE_CODE",cls.PROGRAM_SUB_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_DESCRIPTION",cls.PROGRAM_DESCRIPTION)
        query = filter_by_key(query,"CAPABILITY_SPONSOR_CODE",cls.CAPABILITY_SPONSOR_CODE)
        query = filter_by_key(query,"ASSESSMENT_AREA_CODE",cls.ASSESSMENT_AREA_CODE)
        query = filter_by_key(query,"POM_SPONSOR_CODE",cls.POM_SPONSOR_CODE)
        query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV1_ID)
        query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV2_ID)
        query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV3_ID)
        query = filter_by_key(query,"STORM_ID",cls.STORM_ID)
        query = filter_by_key(query,"RESOURCE_CATEGORY_CODE",DtExtractModel.RESOURCE_CATEGORY_CODE)
        query = filter_by_key(query,"EOC_CODE",DtExtractModel.EOC_CODE)
        query = filter_by_key(query,"EVENT_NAME",DtExtractModel.EVENT_NAME)
        query = filter_by_key(query,"OSD_PROGRAM_ELEMENT_CODE",DtExtractModel.OSD_PROGRAM_ELEMENT_CODE)
        
        if rk_non_zero:
            query = query.filter(DtExtractModel.DELTA_AMT != 0)
        
        query.group_by(
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            DtExtractModel.EOC_CODE,
            DtExtractModel.EVENT_NAME,
            DtExtractModel.EXECUTION_MANAGER_CODE,
            DtExtractModel.RESOURCE_CATEGORY_CODE,
            DtExtractModel.OSD_PROGRAM_ELEMENT_CODE
        )
        return query.all()
    
    @classmethod
    def get_progIds(cls, acc_list: List, prog_group_list: List, db_conn):
        # Retrieve distinct PROGRAM_CODE + CAPABILITY_SPONSOR_CODE + POM_SPONSOR_CODE + ASSESSMENT_AREA_CODE
        #distinct here will retrieve only distinct comb of the 4
        query = db_conn.query(
            cls.PROGRAM_CODE, 
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE).distinct()
        
        if acc_list:
            query = query.filter(cls.ASSESSMENT_AREA_CODE.in_(acc_list))
        if prog_group_list:
            query = query.filter(cls.PROGRAM_GROUP.in_(prog_group_list))
        
        data = query.all()
        # breakpoint()
        data = ["_".join(token) for token in data]
        return data
    
    @classmethod
    def get_prog_event_fundings(cls, DtISSExtractModel, db_conn, ProgIds: List[str]):
        """
        given a list of program ids, return all the fundings from DT_ISS_Extract ONLY, for each event level -> output. No manual override stuff
        """
        subquery = db_conn.query(
            cls.ID,
            cls.PROGRAM_CODE,
            cls.PROGRAM_GROUP,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
        ).filter(cls.ID.in_(ProgIds)).subquery()

        query = db_conn.query(
            subquery.c.ID,
            DtISSExtractModel.PROGRAM_CODE,
            DtISSExtractModel.PROGRAM_GROUP,
            subquery.c.ASSESSMENT_AREA_CODE,
            DtISSExtractModel.EOC_CODE,
            DtISSExtractModel.POM_SPONSOR_CODE,
            DtISSExtractModel.CAPABILITY_SPONSOR_CODE,
            DtISSExtractModel.RESOURCE_CATEGORY_CODE,
            DtISSExtractModel.EXECUTION_MANAGER_CODE,
            func.sum(func.coalesce(DtISSExtractModel.DELTA_AMT, 0)).label("DELTA_AMT"),
            DtISSExtractModel.EVENT_NAME,
            DtISSExtractModel.OSD_PROGRAM_ELEMENT_CODE,
            DtISSExtractModel.FISCAL_YEAR,
        ).select_from(
            DtISSExtractModel
        ).join(
            subquery,
            (DtISSExtractModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtISSExtractModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtISSExtractModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtISSExtractModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtISSExtractModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE),
            isouter=True #left join
        ).filter(
            subquery.c.ID.isnot(None)
        ).group_by(
            subquery.c.ID,
            DtISSExtractModel.PROGRAM_CODE,
            DtISSExtractModel.EOC_CODE,
            DtISSExtractModel.EVENT_NAME,
            DtISSExtractModel.OSD_PROGRAM_ELEMENT_CODE,
            DtISSExtractModel.FISCAL_YEAR,
            DtISSExtractModel.RESOURCE_CATEGORY_CODE,
        )

        return query.all()



#V2
class LookupProgramModel(SOCOMBase):
    __tablename__ = "LOOKUP_PROGRAM"
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',VARCHAR(100),primary_key=True)
    PROGRAM_GROUP: Mapped[str] = Column('PROGRAM_GROUP',VARCHAR(13))
    PROGRAM_CODE: Mapped[str] = Column('PROGRAM_CODE',VARCHAR(11))
    PROGRAM_NAME: Mapped[str] = Column('PROGRAM_NAME',VARCHAR(60))
    PROGRAM_TYPE_CODE: Mapped[str] = Column('PROGRAM_TYPE_CODE',VARCHAR(1))
    PROGRAM_SUB_TYPE_CODE: Mapped[str] = Column('PROGRAM_SUB_TYPE_CODE',VARCHAR(5))
    PROGRAM_DESCRIPTION: Mapped[str] = Column('PROGRAM_DESCRIPTION',VARCHAR(10000))
    CAPABILITY_SPONSOR_CODE: Mapped[str] = Column('CAPABILITY_SPONSOR_CODE',VARCHAR(13))
    ASSESSMENT_AREA_CODE: Mapped[str] = Column('ASSESSMENT_AREA_CODE',VARCHAR(1))
    POM_SPONSOR_CODE: Mapped[str] = Column('POM_SPONSOR_CODE',VARCHAR(13))
    EXECUTION_MANAGER_CODE: Mapped[str] = Column('EXECUTION_MANAGER_CODE',VARCHAR(13))
    EOC_CODE: Mapped[str] = Column('EOC_CODE',VARCHAR(15))
    RESOURCE_CATEGORY_CODE: Mapped[str] = Column('RESOURCE_CATEGORY_CODE',VARCHAR(8))
    OSD_PROGRAM_ELEMENT_CODE: Mapped[str] = Column('OSD_PROGRAM_ELEMENT_CODE',VARCHAR(10))
    EVENT_NAME: Mapped[str] = Column("EVENT_NAME",VARCHAR(60))
    # JCA_LV1_ID: Mapped[str] = Column('JCA_LV1_ID',VARCHAR(4))
    # JCA_LV2_ID: Mapped[str] = Column('JCA_LV2_ID',VARCHAR(5))
    # JCA_LV3_ID: Mapped[str] = Column('JCA_LV3_ID',VARCHAR(6))
    STORM_ID: Mapped[int] = Column('STORM_ID',VARCHAR(40))
    JCA:Mapped[JSON] = Column('JCA',JSON)
    KOP_KSP:Mapped[JSON] = Column('KOP_KSP',JSON)
    CGA:Mapped[JSON] = Column('CGA',JSON)


    @classmethod
    def get_resource_k_from_progIds(cls,DtIssueModel,db_conn,ProgIds:List[str]):
        subquery = db_conn.query(
            cls.ID.label('ID'),
            cls.PROGRAM_NAME,
            cls.PROGRAM_GROUP, 
            cls.PROGRAM_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.EXECUTION_MANAGER_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.EOC_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            ).distinct().subquery() #to avoid duplicate funding lines when join

        query = db_conn.query(
            subquery.c.ID, 
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            subquery.c.PROGRAM_CODE,
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            DtIssueModel.ASSESSMENT_AREA_CODE,
            subquery.c.EXECUTION_MANAGER_CODE,
            subquery.c.RESOURCE_CATEGORY_CODE,
            subquery.c.EOC_CODE,
            subquery.c.OSD_PROGRAM_ELEMENT_CODE,         
            DtIssueModel.FISCAL_YEAR,
            func.sum(DtIssueModel.RESOURCE_K).label('RESOURCE_K')
        ).select_from(
            DtIssueModel
        ).join(
            subquery,
            (DtIssueModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtIssueModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtIssueModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtIssueModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtIssueModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE) &
            (DtIssueModel.EXECUTION_MANAGER_CODE == subquery.c.EXECUTION_MANAGER_CODE) &
            (DtIssueModel.RESOURCE_CATEGORY_CODE == subquery.c.RESOURCE_CATEGORY_CODE) &
            (DtIssueModel.EOC_CODE == subquery.c.EOC_CODE) &
            (DtIssueModel.OSD_PROGRAM_ELEMENT_CODE == subquery.c.OSD_PROGRAM_ELEMENT_CODE),
            isouter=True #left join
        ).filter(
            subquery.c.ID.in_(ProgIds)
        ).group_by(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            subquery.c.PROGRAM_CODE,
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            DtIssueModel.ASSESSMENT_AREA_CODE,
            DtIssueModel.EXECUTION_MANAGER_CODE,
            DtIssueModel.RESOURCE_CATEGORY_CODE,
            DtIssueModel.EOC_CODE,
            DtIssueModel.OSD_PROGRAM_ELEMENT_CODE,
            DtIssueModel.FISCAL_YEAR
        ).having(
            func.sum(DtIssueModel.RESOURCE_K) > 0
        )
        
        return query.all()
    


    @classmethod
    def get_eoc_funding_from_progIds(cls, DtIssueModel, db_conn, ProgIds: List[int]):
        # Create the subquery
        subquery = db_conn.query(
            cls.ID,
            cls.PROGRAM_CODE,
            cls.PROGRAM_GROUP,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.EOC_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.PROGRAM_NAME,
            cls.EXECUTION_MANAGER_CODE,
        ).filter(cls.ID.in_(ProgIds)).subquery()

        # Main query using the subquery
        query = db_conn.query(
            subquery.c.ID,
            DtIssueModel.PROGRAM_CODE,
            DtIssueModel.PROGRAM_GROUP,
            subquery.c.ASSESSMENT_AREA_CODE,            
            # DtIssueModel.EOC_CODE,
            func.coalesce(DtIssueModel.EOC_CODE, 'DEFAULT_EOC').label('EOC_CODE'),
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            # DtIssueModel.RESOURCE_CATEGORY_CODE,
            func.coalesce(DtIssueModel.RESOURCE_CATEGORY_CODE, 'DEFAULT_CATEGORY').label('RESOURCE_CATEGORY_CODE'),
            subquery.c.OSD_PROGRAM_ELEMENT_CODE,
            subquery.c.PROGRAM_NAME,
            subquery.c.EXECUTION_MANAGER_CODE,  
            DtIssueModel.FISCAL_YEAR,
            # func.sum(DtIssueModel.RESOURCE_K).label('RESOURCE_K'),
            func.sum(func.coalesce(DtIssueModel.RESOURCE_K, 0)).label('RESOURCE_K')
        ).select_from(
            subquery
        ).join(
            DtIssueModel,
            (DtIssueModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtIssueModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtIssueModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtIssueModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtIssueModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE) &
            (DtIssueModel.EOC_CODE == subquery.c.EOC_CODE) &
            (DtIssueModel.RESOURCE_CATEGORY_CODE == subquery.c.RESOURCE_CATEGORY_CODE) &
            (DtIssueModel.OSD_PROGRAM_ELEMENT_CODE == subquery.c.OSD_PROGRAM_ELEMENT_CODE) &
            (DtIssueModel.EXECUTION_MANAGER_CODE == subquery.c.EXECUTION_MANAGER_CODE),
            isouter=True
        ).filter(DtIssueModel.EOC_CODE.isnot(None)).group_by(
            subquery.c.ID,
            subquery.c.PROGRAM_CODE,
            subquery.c.PROGRAM_GROUP,
            subquery.c.ASSESSMENT_AREA_CODE,
            DtIssueModel.POM_SPONSOR_CODE,
            DtIssueModel.CAPABILITY_SPONSOR_CODE,
            DtIssueModel.EOC_CODE,
            DtIssueModel.RESOURCE_CATEGORY_CODE,
            DtIssueModel.OSD_PROGRAM_ELEMENT_CODE,
            DtIssueModel.EXECUTION_MANAGER_CODE,
            DtIssueModel.FISCAL_YEAR
        ).order_by(
            DtIssueModel.PROGRAM_CODE,
            DtIssueModel.EOC_CODE,
            DtIssueModel.FISCAL_YEAR
        )
        
        # from sqlalchemy.dialects import mysql
        # print(query.statement.compile(dialect=mysql.dialect()))
        return query.all()
    

    @classmethod
    def get_metadata_iss_zbt(cls,DtModel,rk_non_zero,db_conn,**kwargs):
        """
        Type: DtModel refers to DtIssModel or DtZbtModel
        """
        #helper function
        def filter_by_key(query,key,col):

            if (key in kwargs['kwargs'] and kwargs['kwargs'][key]):

                return query.filter(col.in_(kwargs["kwargs"][key]))
            return query
        
        valid_cols = {col.key for col in inspect(cls).columns}

        for col in kwargs['kwargs'].keys():
            if col not in valid_cols:
                raise HTTPException("Invalid column name keys in the input")
        
        query = db_conn.query(
            cls.ID,
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
            cls.PROGRAM_NAME,
            cls.PROGRAM_TYPE_CODE,
            cls.PROGRAM_SUB_TYPE_CODE,
            cls.PROGRAM_DESCRIPTION,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.POM_SPONSOR_CODE,
            cls.STORM_ID,
            cls.EOC_CODE,
            cls.RESOURCE_CATEGORY_CODE,
            cls.OSD_PROGRAM_ELEMENT_CODE,
            cls.EXECUTION_MANAGER_CODE,
            cls.EVENT_NAME,
        ).join(
            DtModel,
            (DtModel.PROGRAM_GROUP == cls.PROGRAM_GROUP) &
            (DtModel.PROGRAM_CODE == cls.PROGRAM_CODE) &
            (DtModel.POM_SPONSOR_CODE == cls.POM_SPONSOR_CODE) &
            (DtModel.CAPABILITY_SPONSOR_CODE == cls.CAPABILITY_SPONSOR_CODE) &
            (DtModel.ASSESSMENT_AREA_CODE == cls.ASSESSMENT_AREA_CODE) &
            (DtModel.EOC_CODE == cls.EOC_CODE) &
            (DtModel.OSD_PROGRAM_ELEMENT_CODE == cls.OSD_PROGRAM_ELEMENT_CODE) &
            (DtModel.RESOURCE_CATEGORY_CODE == cls.RESOURCE_CATEGORY_CODE) &
            (DtModel.EXECUTION_MANAGER_CODE == cls.EXECUTION_MANAGER_CODE)
        ).group_by(cls.ID)

        query = filter_by_key(query,"ID",cls.ID)
        query = filter_by_key(query,"PROGRAM_GROUP",cls.PROGRAM_GROUP)
        query = filter_by_key(query,"PROGRAM_CODE",cls.PROGRAM_CODE)
        query = filter_by_key(query,"PROGRAM_NAME",cls.PROGRAM_NAME)
        query = filter_by_key(query,"PROGRAM_TYPE_CODE",cls.PROGRAM_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_SUB_TYPE_CODE",cls.PROGRAM_SUB_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_DESCRIPTION",cls.PROGRAM_DESCRIPTION)
        query = filter_by_key(query,"CAPABILITY_SPONSOR_CODE",cls.CAPABILITY_SPONSOR_CODE)
        query = filter_by_key(query,"ASSESSMENT_AREA_CODE",cls.ASSESSMENT_AREA_CODE)
        query = filter_by_key(query,"POM_SPONSOR_CODE",cls.POM_SPONSOR_CODE)
        query = filter_by_key(query,"STORM_ID",cls.STORM_ID)
        query = filter_by_key(query,"RESOURCE_CATEGORY_CODE",cls.RESOURCE_CATEGORY_CODE)
        query = filter_by_key(query,"EOC_CODE",cls.EOC_CODE)
        query = filter_by_key(query,"EXECUTION_MANAGER_CODE",cls.EXECUTION_MANAGER_CODE)
        query = filter_by_key(query,"OSD_PROGRAM_ELEMENT_CODE",cls.OSD_PROGRAM_ELEMENT_CODE)
        query = filter_by_key(query,"PROGRAM_NAME",cls.PROGRAM_NAME)
        query = filter_by_key(query,"EVENT_NAME",cls.EVENT_NAME)
        
        if rk_non_zero:
            query = query.filter(DtModel.RESOURCE_K > 0)
        return query.all()
    
    @classmethod
    def get_metadata_iss_zbt_extract(cls,DtExtractModel,rk_non_zero,db_conn,**kwargs):
        #helper function
        def filter_by_key(query,key,col):

            if (key in kwargs['kwargs'] and kwargs['kwargs'][key]):

                return query.filter(col.in_(kwargs["kwargs"][key]))
            return query
        
        query = db_conn.query(
            cls.ID,
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
            cls.PROGRAM_NAME,
            cls.PROGRAM_TYPE_CODE,
            cls.PROGRAM_SUB_TYPE_CODE,
            cls.PROGRAM_DESCRIPTION,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE,
            cls.POM_SPONSOR_CODE,
            # cls.JCA_LV1_ID,
            # cls.JCA_LV2_ID,
            # cls.JCA_LV3_ID,
            cls.STORM_ID,
            DtExtractModel.EOC_CODE,
            DtExtractModel.RESOURCE_CATEGORY_CODE,
            DtExtractModel.EVENT_NAME,
            DtExtractModel.OSD_PROGRAM_ELEMENT_CODE,
            
        ).join(
            DtExtractModel,
            (DtExtractModel.PROGRAM_GROUP == cls.PROGRAM_GROUP) &
            (DtExtractModel.PROGRAM_CODE == cls.PROGRAM_CODE) &
            (DtExtractModel.POM_SPONSOR_CODE == cls.POM_SPONSOR_CODE) &
            (DtExtractModel.CAPABILITY_SPONSOR_CODE == cls.CAPABILITY_SPONSOR_CODE) &
            (DtExtractModel.ASSESSMENT_AREA_CODE == cls.ASSESSMENT_AREA_CODE)
            ).group_by(
            cls.PROGRAM_GROUP,
            cls.PROGRAM_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE
        )
            # isouter=True) #left join
        # if "PROGRAM_GROUP" in kwargs and kwargs["PROGRAM_GROUP"]:
            # query.filter(cls.PROGRAM_GROUP.in_(kwargs["PROGRAM_GROUP"])
        # breakpoint()
        query = filter_by_key(query,"ID",cls.ID)
        query = filter_by_key(query,"PROGRAM_GROUP",cls.PROGRAM_GROUP)
        query = filter_by_key(query,"PROGRAM_CODE",cls.PROGRAM_CODE)
        query = filter_by_key(query,"PROGRAM_NAME",cls.PROGRAM_NAME)
        query = filter_by_key(query,"PROGRAM_TYPE_CODE",cls.PROGRAM_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_SUB_TYPE_CODE",cls.PROGRAM_SUB_TYPE_CODE)
        query = filter_by_key(query,"PROGRAM_DESCRIPTION",cls.PROGRAM_DESCRIPTION)
        query = filter_by_key(query,"CAPABILITY_SPONSOR_CODE",cls.CAPABILITY_SPONSOR_CODE)
        query = filter_by_key(query,"ASSESSMENT_AREA_CODE",cls.ASSESSMENT_AREA_CODE)
        query = filter_by_key(query,"POM_SPONSOR_CODE",cls.POM_SPONSOR_CODE)
        # query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV1_ID)
        # query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV2_ID)
        # query = filter_by_key(query,"JCA_LV1_ID",cls.JCA_LV3_ID)
        query = filter_by_key(query,"STORM_ID",cls.STORM_ID)
        query = filter_by_key(query,"RESOURCE_CATEGORY_CODE",DtExtractModel.RESOURCE_CATEGORY_CODE)
        query = filter_by_key(query,"EOC_CODE",DtExtractModel.EOC_CODE)
        query = filter_by_key(query,"EVENT_NAME",DtExtractModel.EVENT_NAME)
        query = filter_by_key(query,"OSD_PROGRAM_ELEMENT_CODE",DtExtractModel.OSD_PROGRAM_ELEMENT_CODE)
        
        if rk_non_zero:
            query = query.filter(DtExtractModel.DELTA_AMT > 0)
        return query.all()
    

    @classmethod
    def get_delta_amount_from_progIds(cls,DtIssExtractModel,db_conn,ProgIds:List[str]):
        subquery = db_conn.query(
            distinct(cls.ID).label('ID'),
            cls.PROGRAM_NAME,
            cls.PROGRAM_GROUP, 
            cls.PROGRAM_CODE,
            cls.POM_SPONSOR_CODE,
            cls.CAPABILITY_SPONSOR_CODE,
            cls.ASSESSMENT_AREA_CODE).subquery()
        # print(subquery.c.keys())
        # print(subquery.all())
        query = db_conn.query(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            DtIssExtractModel.POM_SPONSOR_CODE,
            DtIssExtractModel.CAPABILITY_SPONSOR_CODE,
            DtIssExtractModel.ASSESSMENT_AREA_CODE,
            DtIssExtractModel.FISCAL_YEAR,
            func.sum(DtIssExtractModel.DELTA_AMT).label('DELTA_AMT')
        ).select_from(
            DtIssExtractModel
        ).join(
            subquery,
            (DtIssExtractModel.PROGRAM_GROUP == subquery.c.PROGRAM_GROUP) &
            (DtIssExtractModel.PROGRAM_CODE == subquery.c.PROGRAM_CODE) &
            (DtIssExtractModel.POM_SPONSOR_CODE == subquery.c.POM_SPONSOR_CODE) &
            (DtIssExtractModel.CAPABILITY_SPONSOR_CODE == subquery.c.CAPABILITY_SPONSOR_CODE) &
            (DtIssExtractModel.ASSESSMENT_AREA_CODE == subquery.c.ASSESSMENT_AREA_CODE),
            isouter=True #left join
        ).filter(
            DtIssExtractModel.DELTA_AMT > 0  #confirmed with client that they only show positive values. avoid optimizing negatives greedily
        ).group_by(
            subquery.c.ID,
            subquery.c.PROGRAM_NAME,
            subquery.c.PROGRAM_GROUP,
            DtIssExtractModel.POM_SPONSOR_CODE,
            DtIssExtractModel.CAPABILITY_SPONSOR_CODE,
            DtIssExtractModel.POM_POSITION_CODE,
            DtIssExtractModel.FISCAL_YEAR,
            DtIssExtractModel.ASSESSMENT_AREA_CODE
        ).having(
            subquery.c.ID.in_(ProgIds)
        )
        
        return query.all()