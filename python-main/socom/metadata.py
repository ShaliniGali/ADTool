import json
import re

from rds.table_model.socom.LookupProgramDetail import (
    LookupProgramDetailModel,
    LookupProgramModel
)

from rds.table_model.socom.DtIssModel import DtIssueModel
from rds.table_model.socom.DtZbtModel import DtZbtModel
from rds.table_model.socom.DtZbtExtractModel import DtZBTExtractModel
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel
from rds.table_model.socom.UsrLookupPOMPosition import UsrLookupPOMPosition
from rds.table_model.socom.DtAMSFEM import DtAMSFEM

from collections import defaultdict
from fastapi import HTTPException

from api.models import PomPositionInput

from api.internal.redis_cache import RedisController

from api.internal.resources import SharedResources
from api.internal.resources import (
    ZbtSummaryTableSet, 
    IssSummaryTableSet, 
    ResourceConstraintCOATableSet,
    create_dynamic_table_class,
)
from sqlalchemy.inspection import inspect


def get_dt_metadata(db_conn,dt_orm,rk_non_zero,**kwargs):
        """
        Type: DtModel refers to DtIssModel or DtISSExtractModel types
        """
        valid_cols = {col.key for col in inspect(dt_orm).columns}
        for col in kwargs['kwargs'].keys():
            if col not in valid_cols:
                raise HTTPException("Invalid column name keys in the input")
        
        select_cols = [
                dt_orm.PROGRAM_ID.label("ID"),
                dt_orm.PROGRAM_GROUP,
                dt_orm.PROGRAM_CODE,
                dt_orm.CAPABILITY_SPONSOR_CODE,
                dt_orm.ASSESSMENT_AREA_CODE,
                dt_orm.POM_SPONSOR_CODE,
                dt_orm.RESOURCE_CATEGORY_CODE,
                dt_orm.EXECUTION_MANAGER_CODE,
                dt_orm.OSD_PROGRAM_ELEMENT_CODE,
                dt_orm.EOC_CODE,
            ]
        if hasattr(dt_orm,"EVENT_NAME"):
            select_cols.append(dt_orm.EVENT_NAME)
        
        query = db_conn.query(*select_cols)
        
        # Apply filters dynamically
        filter_args = kwargs.get("kwargs", {})
        valid_cols = {col.key for col in inspect(dt_orm).columns} #retrieve all columns from ORM
        for col in filter_args:
            if col not in valid_cols:
                raise HTTPException("Invalid column name keys in the input")
        
        #filter by columns from the dt table
        for key, values in filter_args.items():
            if values:
                col_obj = getattr(dt_orm, key)
                query = query.filter(col_obj.in_(values))

        
        resource_col = getattr(dt_orm,"RESOURCE_K",None) #default None
        delta_col = getattr(dt_orm,"RESOURCE_K",None)

        if not rk_non_zero:
            return query.all()
        
        if resource_col:
            query = query.filter(resource_col > 0) 
        if delta_col:
            query = query.filter(delta_col > 0) 

        return query.all()
    
async def get_lkup_program_metadata(db_conn,table,rk_non_zero:bool,**kwargs):
    #db_conn,dt_orm,use_extract,rk_non_zero,**kwargs
    if table == "iss":
        dt_table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
        dt_orm = create_dynamic_table_class(dt_table_name,DtIssueModel)
    elif table == "iss-extract":
        dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
        dt_orm = create_dynamic_table_class(dt_table_name,AbstractORMClass=DtISSExtractModel)
    elif table == "zbt":
        dt_table_name = IssSummaryTableSet.CURRENT["ZBT"][0]
        dt_orm = create_dynamic_table_class(dt_table_name,AbstractORMClass=DtZbtModel)
    elif table == "zbt-extract":
        dt_table_name = ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT"][0]
        dt_orm = create_dynamic_table_class(dt_table_name,AbstractORMClass=DtZBTExtractModel)
    else:
        raise HTTPException(422,"Must clarify that 'COA_TYPE' is either 'iss' / 'iss-extract' / 'zbt' / 'zbt-extract' ")
    
    data = get_dt_metadata(db_conn,dt_orm,rk_non_zero,**kwargs)
    seen = set() #dedup
    # breakpoint()
    
    resp = []
    # breakpoint()
    # print(data)
    for row in data:
        if row in seen:
            continue
        resp.append(row)
        row_ = tuple(str(token) for token in row)
        seen.add(row_)
    # breakpoint()
    return resp



def convert_to_dict(d):
    if isinstance(d, defaultdict):
        return {k: convert_to_dict(v) for k, v in d.items()}
    elif isinstance(d, list):
        return [convert_to_dict(i) for i in d]
    elif isinstance(d, dict):
        return {k: convert_to_dict(v) for k, v in d.items()}
    return d

async def get_dt_tables_from_pom_position(model,set_position=False):
    data = SharedResources.DT_TABLE_DECR_MAP
    
    pom_year = int(model.year)
    position = model.position
    decrement = data[position] #mapping of the decrement based on the position

    template = {
            "ZBT_SUMMARY":{"CURRENT":defaultdict(list),"HISTORICAL_POM":defaultdict(list)},
            "ISS_SUMMARY":{"CURRENT":defaultdict(list),"HISTORICAL_POM":defaultdict(list)},
            "RESOURCE_CONSTRAINED_COA":{"CURRENT":defaultdict(list)}
    }

    tableset = set()

    #zbt summary app. Decrement by subapp TYPE, not by table name
    template["ZBT_SUMMARY"]["CURRENT"]["EXT"].append(f"DT_EXT_{pom_year-decrement['EXT']}")
    template["ZBT_SUMMARY"]["CURRENT"]["ZBT_EXTRACT"].append(f"DT_ZBT_EXTRACT_{pom_year - decrement['EXT']}")
    template["ZBT_SUMMARY"]["CURRENT"]["ZBT_EXTRACT_DIRTY"].append(f"DT_ZBT_EXTRACT_DIRTY_{pom_year - decrement['EXT']}")

    template["ZBT_SUMMARY"]["HISTORICAL_POM"]["EXT"].extend([f'DT_EXT_{pom_year-decrement["EXT"]-2}',
                                         f'DT_EXT_{pom_year-decrement["EXT"]-1}'])
    template["ZBT_SUMMARY"]["HISTORICAL_POM"]["ZBT"].extend([f'DT_ZBT_{pom_year-decrement["EXT"]-2}',
                                         f'DT_ZBT_{pom_year-decrement["EXT"]-1}']) 
    template["ZBT_SUMMARY"]["HISTORICAL_POM"]["ISS"].extend([f'DT_ISS_{pom_year-decrement["EXT"]-2}',
                                         f'DT_ISS_{pom_year-decrement["EXT"]-1}']) 
    template["ZBT_SUMMARY"]["HISTORICAL_POM"]["POM"].extend([f'DT_POM_{pom_year-decrement["EXT"]-2}',
                                         f'DT_POM_{pom_year-decrement["EXT"]-1}']) 
    
    tableset.update(template["ZBT_SUMMARY"]["CURRENT"]["EXT"])
    tableset.update(template["ZBT_SUMMARY"]["CURRENT"]["ZBT_EXTRACT"])
    tableset.update(template["ZBT_SUMMARY"]["HISTORICAL_POM"]["EXT"])
    tableset.update(template["ZBT_SUMMARY"]["HISTORICAL_POM"]["ZBT"])
    tableset.update(template["ZBT_SUMMARY"]["HISTORICAL_POM"]["ISS"])
    tableset.update(template["ZBT_SUMMARY"]["HISTORICAL_POM"]["POM"])

    #iss summary app
    template["ISS_SUMMARY"]["CURRENT"]["EXT"].append(f"DT_EXT_{pom_year-decrement['ZBT']}")
    template["ISS_SUMMARY"]["CURRENT"]["ZBT"].append(f"DT_ZBT_{pom_year-decrement['ZBT']}")
    template["ISS_SUMMARY"]["CURRENT"]["ISS_EXTRACT"].append(f"DT_ISS_EXTRACT_{pom_year-decrement['ZBT']}")
    template["ISS_SUMMARY"]["CURRENT"]["ISS_EXTRACT_DIRTY"].append(f"DT_ISS_EXTRACT_DIRTY_{pom_year-decrement['ZBT']}")
    template["ISS_SUMMARY"]["CURRENT"]["ZBT_EXTRACT"].append(f"DT_ZBT_EXTRACT_{pom_year-decrement['ZBT']}")

    template["ISS_SUMMARY"]["HISTORICAL_POM"]["EXT"].extend([f'DT_EXT_{pom_year-decrement["ZBT"]-2}',
                                         f'DT_EXT_{pom_year-decrement["ZBT"]-1}'])
    template["ISS_SUMMARY"]["HISTORICAL_POM"]["ZBT"].extend([f'DT_ZBT_{pom_year-decrement["ZBT"]-2}',
                                         f'DT_ZBT_{pom_year-decrement["ZBT"]-1}']) 
    template["ISS_SUMMARY"]["HISTORICAL_POM"]["ISS"].extend([f'DT_ISS_{pom_year-decrement["ZBT"]-2}',
                                         f'DT_ISS_{pom_year-decrement["ZBT"]-1}']) 
    template["ISS_SUMMARY"]["HISTORICAL_POM"]["POM"].extend([f'DT_POM_{pom_year-decrement["ZBT"]-2}',
                                         f'DT_POM_{pom_year-decrement["ZBT"]-1}']) 
    
    tableset.update( template["ISS_SUMMARY"]["CURRENT"]["EXT"])
    tableset.update( template["ISS_SUMMARY"]["CURRENT"]["ZBT"])
    tableset.update( template["ISS_SUMMARY"]["CURRENT"]["ISS_EXTRACT"])
    tableset.update(template["ISS_SUMMARY"]["HISTORICAL_POM"]["EXT"])
    tableset.update(template["ISS_SUMMARY"]["HISTORICAL_POM"]["ZBT"])
    tableset.update(template["ISS_SUMMARY"]["HISTORICAL_POM"]["ISS"])
    tableset.update(template["ISS_SUMMARY"]["HISTORICAL_POM"]["POM"])
    #coa app
    template["RESOURCE_CONSTRAINED_COA"]["CURRENT"]["ISS"].append(f"DT_ISS_{pom_year-decrement['ISS']}")

    tableset.update(template["RESOURCE_CONSTRAINED_COA"]["CURRENT"]["ISS"])

    

    unfound_tables = tableset - SharedResources.DT_TABLE_SET

    if unfound_tables:
        raise HTTPException(404,f"Unable to find these tables for the given position and year: {', '.join(unfound_tables)}")
    
    if set_position:
        ZbtSummaryTableSet.CURRENT = template["ZBT_SUMMARY"]["CURRENT"]
        ZbtSummaryTableSet.HISTORICAL_POM = template["ZBT_SUMMARY"]["HISTORICAL_POM"]
        IssSummaryTableSet.CURRENT = template["ISS_SUMMARY"]["CURRENT"]
        IssSummaryTableSet.HISTORICAL_POM = template["ISS_SUMMARY"]["HISTORICAL_POM"]
        ResourceConstraintCOATableSet.CURRENT = template["RESOURCE_CONSTRAINED_COA"]["CURRENT"]
    
    return template



async def set_current_active_pom_position(db_conn,redis):
    data = UsrLookupPOMPosition.get_active_pom_year_position(db_conn)
    # model = {
    #     "position": data[1],
    #     "year": data[0]
    #     }
    model = PomPositionInput(position=data[1],year=data[0])

    try:
        data = await get_dt_tables_from_pom_position(model,set_position=True)
        data = convert_to_dict(data)
        data = json.dumps(data)
        RedisController.write_json_to_redis("api::/socom/metadata/pom-position/active",data,redis)
        
    except Exception as e:
        raise HTTPException(500, f"cannot set the current active POM position and Year, {e}")

    return True


def get_cap_sponsor_category(cap_sponsor_code):
    #code is capability sponsor code
    if re.search(r'^SORDAC',cap_sponsor_code):
        return "SOF AT&L"
    elif re.search("^JSOC",cap_sponsor_code):
        return "JSOC"
    elif cap_sponsor_code in ["AFSOC","NSW","USASOC","MARSOC","NAVSPECWARCOM"]:
        return "COMPONENT"
    elif re.search(r'^SOC',cap_sponsor_code):
        return "TSOCs"
    
    else:
        return "USSOCOM HQ"



async def get_pxid_from_programs( program_groups, program_codes, db_conn):
    data = DtAMSFEM.get_pxid_from_metadata( program_groups, program_codes, db_conn)
    return data