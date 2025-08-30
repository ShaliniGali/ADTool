from typing import List, Optional

from fastapi import Query
import redis

from rds.table_model.socom.DtAMSFEM import DtAMSFEM
from rds.table_model.socom.LookupAMSPGMX import LookupAMSPGMX
from rds.table_model.socom.DtAMSMilestone import DtAMSMilestone
from rds.table_model.socom.DtAMSFielding import DtAMSFielding

async def get_ams_desc_from_prog_groups( program_group, db_conn):
    data = LookupAMSPGMX.get_ams_metadata_by_prog_groups(program_group,db_conn)
    return data


async def get_ams_milestones(program_group,program_fullnames,db_conn):
    data = DtAMSMilestone.get_milestone_by_prog_group(program_group,program_fullnames,db_conn)
    return data


async def get_ams_milestones_reqs(pxid,milestone,milestone_status,db_conn):
    data = DtAMSMilestone.get_milestone_requirements(pxid,milestone,milestone_status,db_conn)
    return data

async def get_ams_fem_funding_agg(program_groups:List[str],resouce_cat_codes:List[str], db_conn,redis):    
    data = DtAMSFEM.get_all_fundings_by_prog_groups(program_groups,resouce_cat_codes,db_conn,redis)
    return data
    

async def get_ams_fielding_from_pg(program_group:str,fielding_items:List[str],components:List[str],fy:int, fielding_types:Optional[List[str]],db_conn):
    data = DtAMSFielding.get_ams_fielding_quantity(program_group,fielding_items, components,fy,fielding_types,db_conn)
    return data


async def comparison_budget_view(program_group:List[str],db_conn):
    data = DtAMSFEM.compare_prog_budgets(program_group, db_conn)
    return data