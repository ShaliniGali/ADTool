from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

from collections import defaultdict
import json

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
    and_,
)

from sqlalchemy.orm import (
    Mapped,
    aliased,
)

from typing import List,Dict,Any

from rds.table_model.socom.DtBudgetExecution import DtBudgetExecution

from api.internal.redis_cache import RedisController
class DtAMSFEM(SOCOMBase):
    __tablename__ ="DT_AMS_FEM"
    __table_args__={
        'schema': SCHEMA
    }
    
    PK_DUMMY: Mapped[str] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    PXID: Mapped[int] = Column("PXID",Integer)
    FY: Mapped[int] = Column('FY',SmallInteger)
    APPN: Mapped[str] = Column('APPN',VARCHAR(8))
    PEO: Mapped[str] = Column('PEO',VARCHAR(8))
    PE: Mapped[str] = Column('PE',VARCHAR(16))
    ELEMENT_OF_COST: Mapped[str] = Column('ELEMENT_OF_COST',VARCHAR(15))
    AMOUNT: Mapped[int] = Column("AMOUNT",Integer)
    OBL_AMT: Mapped[float] = Column("OBL_AMT",Float)
    EXPEND_PLAN_AMOUNT: Mapped[int] = Column("EXPEND_PLAN_AMOUNT",Integer)

    @classmethod
    def get_all_fundings_by_prog_groups(cls,prog_group_list,resource_cat_codes,db_conn,redis):
        ams_ppbes_subquery = (
            db_conn.query(
                cls.ELEMENT_OF_COST,
                cls.APPN,
                cls.PE,
                cls.FY
            ).distinct()
        ).subquery()
        #(eoc,resource category,program element, fy)
        #apply ppbes-ams filter
        key = "api::/socom/ams/fem/agg"
        if not redis.get(key):
            aggregated_data = (
                db_conn.query(
                    DtBudgetExecution.PROGRAM_GROUP,
                    DtBudgetExecution.EOC_CODE,
                    DtBudgetExecution.FISCAL_YEAR,
                    DtBudgetExecution.RESOURCE_CATEGORY_CODE,
                    DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE,
                    func.sum(DtBudgetExecution.SUM_ACTUALS).label("SUM_ACTUALS"),
                    func.sum(DtBudgetExecution.SUM_ENT).label("SUM_ENT"),
                    func.sum(DtBudgetExecution.SUM_PB).label("SUM_PB")
                ).filter(
                    DtBudgetExecution.EXECUTION_MANAGER_CODE.like("SORDAC%"),
                    tuple_(
                        DtBudgetExecution.EOC_CODE,
                        DtBudgetExecution.RESOURCE_CATEGORY_CODE,
                        DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE,
                        DtBudgetExecution.FISCAL_YEAR
                    ).in_(ams_ppbes_subquery),  #filter by AMS membership directly
                    # DtBudgetExecution.RESOURCE_CATEGORY_CODE.in_(resource_cat_codes)
                ).group_by(
                    DtBudgetExecution.PROGRAM_GROUP, #reserve for assignment later
                    DtBudgetExecution.EOC_CODE,
                    DtBudgetExecution.FISCAL_YEAR,
                    DtBudgetExecution.RESOURCE_CATEGORY_CODE,
                    DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE
                ).subquery()
            )

            #main query
            data = db_conn.query(
                aggregated_data.c.PROGRAM_GROUP,
                aggregated_data.c.RESOURCE_CATEGORY_CODE,
                cls.FY,
                func.sum(cls.AMOUNT) / 1000,
                func.sum(cls.OBL_AMT) / 1000,
                func.sum(cls.EXPEND_PLAN_AMOUNT) / 1000
            ).join(
                aggregated_data,  # Join with the pre-aggregated data
                (aggregated_data.c.EOC_CODE == cls.ELEMENT_OF_COST) &
                (aggregated_data.c.FISCAL_YEAR == cls.FY) &
                (aggregated_data.c.RESOURCE_CATEGORY_CODE == cls.APPN) &
                (aggregated_data.c.OSD_PROGRAM_ELEMENT_CODE == cls.PE)
            ).group_by(
                aggregated_data.c.PROGRAM_GROUP,
                aggregated_data.c.RESOURCE_CATEGORY_CODE,
                cls.FY
            )#.all()

            result = defaultdict(lambda: defaultdict(dict)) #nested defaultdict init!
            for prog_group, rcc, fy, amount, obl_amount, expended_amount in data:
                # result[prog_group][rcc]["RESOURCE_CATEGORY_CODE"] = rcc  # Auto-initialized

                # Store fiscal year data
                result[prog_group][rcc][fy] = {
                    "PLAN AMOUNT": int(amount),
                    "OBLIGATED AMOUNT": int(obl_amount),
                    "EXPEND AMOUNT": int(expended_amount)
                }

            RedisController.write_json_to_redis(key,json.dumps(result),redis,expires_in=500)
        
        data = RedisController.get_json_from_redis(key,redis)
        
        #now filter data by PROGRAM_GROUP
        data = {prog_group:val for prog_group,val in data.items() if prog_group in prog_group_list}
                
        def filter_by_resource_cat(data):
            filtered_result = defaultdict(lambda: defaultdict(dict))

            for prog_group, resource_data in data.items():
                for rcc, fiscal_data in resource_data.items():
                    if rcc in resource_cat_codes:  # Filter based on resource_cat_codes
                        filtered_result[prog_group][rcc] = {
                            fy: amounts for fy, amounts in fiscal_data.items() if fy != "RESOURCE_CATEGORY_CODE"
                        }

            return filtered_result
     
        return filter_by_resource_cat(data) if resource_cat_codes else data
    
    #note, & bitwise operator in precedence, need encap
    @classmethod
    def get_pxid_from_metadata(cls,program_group:List[str],program_code:List[str],db_conn):
        
        query = db_conn.query(DtBudgetExecution.PROGRAM_GROUP, DtBudgetExecution.PROGRAM_CODE, cls.PXID
                            ).join(DtBudgetExecution,
                               ( DtBudgetExecution.EOC_CODE == cls.ELEMENT_OF_COST) &
                                (DtBudgetExecution.FISCAL_YEAR == cls.FY) &
                                (DtBudgetExecution.RESOURCE_CATEGORY_CODE == cls.APPN) &
                                (DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE == cls.PE)
                                )
        if program_group:
            query = query.filter(DtBudgetExecution.PROGRAM_GROUP.in_(program_group))
        if program_code:
            query = query.filter(DtBudgetExecution.PROGRAM_CODE.in_(program_code))
        
        data = query.distinct().all()
        result = defaultdict(set)
        for pgroup,pcode, pxid in data:
            result[f"{pgroup}|{pcode}"].add(pxid) #auto removal of dups from joins
        
        # result = {k:list(s) for k,s in result.items()}
        return result
        
    
    @classmethod
    def compare_prog_budgets(cls,program_groups:List[str],db_conn):

        from rds.table_model.socom.LookupAMSPGMX import LookupAMSPGMX
        ams_ppbes_subquery = (
            db_conn.query(
                cls.ELEMENT_OF_COST,
                cls.APPN,
                cls.PE,
                cls.FY
            )
            # ).filter(cls.APPN.in_(appns)).distinct()
        ).subquery()

        #GRAB PXID OF THE AMS_PPBES MEMBERSHIPS
        #(eoc,resource category,program element, fy)

        aggregated_data = (
            db_conn.query(
                DtBudgetExecution.PROGRAM_GROUP,
                DtBudgetExecution.EOC_CODE,
                DtBudgetExecution.FISCAL_YEAR,
                DtBudgetExecution.RESOURCE_CATEGORY_CODE,
                DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE,
            ).filter(
                DtBudgetExecution.EXECUTION_MANAGER_CODE.like("SORDAC%"),
                tuple_(
                    DtBudgetExecution.EOC_CODE,
                    DtBudgetExecution.RESOURCE_CATEGORY_CODE,
                    DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE,
                    DtBudgetExecution.FISCAL_YEAR
                ).in_(ams_ppbes_subquery),
                DtBudgetExecution.PROGRAM_GROUP.in_(program_groups)
            ).group_by(
                DtBudgetExecution.PROGRAM_GROUP,
                DtBudgetExecution.EOC_CODE,
                DtBudgetExecution.FISCAL_YEAR,
                DtBudgetExecution.RESOURCE_CATEGORY_CODE,
                DtBudgetExecution.OSD_PROGRAM_ELEMENT_CODE
            ).subquery()
        )    

        #cannot group by with func.coalesce() on query
        data = db_conn.query(
                            func.coalesce(LookupAMSPGMX.PROGRAM_FULLNAME,"No Program Name Found"),
                            aggregated_data.c.EOC_CODE,
                            cls.APPN,
                            cls.FY,
                            func.sum(cls.EXPEND_PLAN_AMOUNT).label('SPENT')/1000,
                            func.sum(cls.AMOUNT).label('BUDGET')/1000,
                            func.sum(cls.OBL_AMT).label("GOAL")/1000,
                            cls.PE
                            ).join(
                                aggregated_data,
                                (aggregated_data.c.EOC_CODE == cls.ELEMENT_OF_COST) &
                                (aggregated_data.c.FISCAL_YEAR == cls.FY) &
                                (aggregated_data.c.RESOURCE_CATEGORY_CODE == cls.APPN) &
                                (aggregated_data.c.OSD_PROGRAM_ELEMENT_CODE == cls.PE)
                            ).join(LookupAMSPGMX,
                                and_(LookupAMSPGMX.PXID == cls.PXID,
                                     LookupAMSPGMX.PXID.isnot(None)
                                )
                            ).group_by(
                                func.coalesce(LookupAMSPGMX.PROGRAM_FULLNAME,"No Program Name Found"),
                                aggregated_data.c.EOC_CODE,
                                cls.APPN,
                                cls.FY
                            ).order_by(
                                LookupAMSPGMX.PROGRAM_FULLNAME,
                                cls.FY, 
                                cls.APPN,
                                cls.ELEMENT_OF_COST
                            )#.all()
        
        
        # print(data.statement.compile(db_conn.bind, compile_kwargs={"literal_binds": True}))
        
        
        result = []
        for prog_group, eoc_code, appn, fy, spent, budget, goal, pe in data:
            temp = {"PROGRAM_FULLNAME":prog_group,
                    "EOC_CODE":eoc_code,
                    "APPN":appn,
                    "PE":pe,
                    "FY":fy,
                    "SPENT":round(spent),
                    "BUDGET":round(budget),
                    "GOAL":round(goal),
                    "$K AT RISK":round(float(goal)-float(spent)),
                    }
            if budget != 0:
                temp["% SPENT"] = round((float(spent)/float(budget))*100)
                temp["% GOAL"] = round((float(goal)/float(budget))*100)
                temp["% DELTA"] = round(float(temp["% GOAL"]) - float(temp["% SPENT"]))
            result.append(temp)     
        
        return result
