import openpyxl
from typing import Dict, List, Optional
import pandas as pd
import numpy as np
from io import BytesIO
from fastapi.responses import StreamingResponse,JSONResponse
from api.internal.conn import get_socom_session
from api.internal.redis_cache import get_redis_decoded
from api.internal.resources import (
    ZbtSummaryTableSet, 
    IssSummaryTableSet,
    create_dynamic_table_class,
)


from fastapi import (
    APIRouter,
    HTTPException, 
    Depends, 
    Query,
    BackgroundTasks,
)


from api.models import (
    ProgEOCFundingResponse,
    ProgEocFundingModel,
    ZbtSummaryFilterInputModel,
    IssSummaryFilterInputModel,
    JCANonCoveredInputModel,
    CGANonCoveredInputModel,
    CritScoreDownloadInput,
    PomPositionInput,
    ProgEventFundingModel,
    ProgEventFundingResponse,
    AggPbFundingInput,
    AggBudgetExecInput,

)

from socom.eoc_funding import (
    get_prog_eoc_funding,
    get_prog_event_funding,
)

from socom.pb_funding import (
    get_pb_comparison_agg,
    get_budget_exec_agg
)

from socom.ams import (
    get_ams_fem_funding_agg,
    get_ams_fielding_from_pg,
    get_ams_desc_from_prog_groups,
    get_ams_milestones,
    get_ams_milestones_reqs,
    comparison_budget_view,
)

from socom.metadata import (
    get_lkup_program_metadata, 
    get_dt_tables_from_pom_position,
    set_current_active_pom_position, 
    get_pxid_from_programs,
)

from socom.file_download import (
    download_prog_score_excel,
)

from socom.file_upload import (
    upload_dt_position_tables,
    upload_dt_oop_tables,
    validate_oop_parsing,
    upload_extract_dirty_table,
)
from rds.table_model.socom.LookupJCA import LookupJCA
from rds.table_model.socom.LookupCGA import LookupCGA
from rds.table_model.socom.LookupKP import LookupKOPKSP
from rds.table_model.socom.UsrLookupPOMPosition import UsrLookupPOMPosition
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel
from rds.table_model.socom.DtZbtExtractModel import DtZBTExtractModel
from rds.table_model.socom.DtBudgetExecution import DtBudgetExecution
from rds.table_model.socom.DtPBComparison import DtPBComparison
from rds.table_model.socom.DtZbtExtractDirtyModel import DtZBTExtractDirtyModel
from rds.table_model.socom.DtIssExtractDirtyModel import DtISSExtractDirtyModel

from socom.summary import (
    get_zbt_summary_fromdb,
    get_iss_summary_fromdb,
    ZBTQuery,
    ISSQuery,
    get_zbt_event_summary_view,
    get_zbt_event_summary_list_view, 
    get_zbt_event_summary_list_export,
    get_iss_event_summary_view,
    get_iss_event_summary_list_view,
    get_iss_event_summary_list_export,
    # get_latest_ad_recommendations,
    
)

from optimizer.events import (
    process_event_detailed_summary
)

from authentication.auth import (
    # get_current_user,
    UserRole,
    require_roles,
)

from fastapi import HTTPException, Depends

router = APIRouter(
    prefix="/socom",
    tags=["General SOCOM Endpoints"],
    responses={404:{"description":"Endpoint Not Found"}}
)

@router.post("/prog_eoc_funding",
             status_code=200,
             response_model=ProgEOCFundingResponse,name="get metadata (especially EOC Code from a List of Program Ids) [in use]",
            )
async def get_iss_eoc_funding(program_ids:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = get_prog_eoc_funding(db_conn=db_conn,program_ids=program_ids)
    data = [ProgEocFundingModel.parse_obj(row) for row in data]
    resp = ProgEOCFundingResponse.parse_obj(data)
    return resp


@router.post("/prog_event_funding", 
             status_code=200, 
             response_model=ProgEventFundingResponse, name="get event funding from the DT_ISS_EXTRACT based on list of program ids input")
async def get_iss_event_funding(program_ids: List[str], db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = get_prog_event_funding(db_conn=db_conn,program_ids=program_ids)
    data = [ProgEventFundingModel.parse_obj(row) for row in data]
    resp = ProgEventFundingResponse.parse_obj(data)
    return resp
    

@router.post("/download/scores/excel",
             status_code=200,
             name="download scoring programs in excel format [in use]")
async def download_excel_criteria_scores(model:CritScoreDownloadInput,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    """Manually download csv file. Input is dictionary of dataframe format"""
    type_of_coa = "IO" if model.TYPE_OF_COA == "ISS_EXTRACT" else "RC_T"
    metadata = {"CYCLE_ID":[model.cycle_id],"TYPE OF COA": type_of_coa}
    df_meta = pd.DataFrame(metadata)
    print(type_of_coa)
    df = download_prog_score_excel(model,db_conn)
    # df.to_csv(buffer,index=False) #dump data into buffer
    buffer = BytesIO()
    #write excel into the buffer
    with pd.ExcelWriter(buffer,engine="openpyxl") as writer:
        df.to_excel(writer,sheet_name="data",index=False)
        df_meta.to_excel(writer,sheet_name="metadata",index=False)
    
    buffer.seek(0)
    headers = {
        'Content-Disposition': 'attachment; filename="download.xlsx"'
    }
    data = StreamingResponse(buffer,headers=headers,
                             media_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
    
    return data



@router.post("/metadata/{coa_type}",status_code=200,name="retrieve metadata from LOOKUP_PROGRAM [in use]")
async def get_metadata(rk_non_zero:bool,data:Dict[str,List],coa_type,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    """retrieve metadata with query parameters"""
    try:
        resp = await get_lkup_program_metadata(db_conn,coa_type,v2=False,rk_non_zero=rk_non_zero,kwargs=data)
        print(len(resp))
        # breakpoint()
    except Exception as e:
        print(e)
        raise HTTPException(422,"Please double check the input parameters")
    return resp


@router.post("/jca_description",response_model = Dict[str,str],status_code=200,name="retrieve JCA description from IDs [in use]")
async def get_jca_description(data:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    resp = LookupJCA.get_description_by_ids(ids=data,db_conn=db_conn)
    return resp

@router.post("/cga_description",response_model = Dict[str,Dict[str,str]],status_code=200,name="retrieve JCA description from IDs [in use]")
async def get_cga_description(data:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    resp = LookupCGA.get_description_by_ids(ids=data,db_conn=db_conn)
    return resp

@router.post("/kop_ksp_description",response_model = Dict[str,Dict],status_code=200,name="retrieve JCA description from IDs [in use]")
async def get_kop_ksp_description(data:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    resp = LookupKOPKSP.get_description_by_ids(ids=data,db_conn=db_conn)
    return resp

@router.post("/jca/noncovered",response_model=List[str],status_code=200,name="retrieve noncovered ID spaces from a given list of ID's [in use]")
async def get_jca_noncovered_ids(data:JCANonCoveredInputModel,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    # breakpoint()

    #when level ==1, we ensure that ALL element must be "x.0.0"
    if data.level == 1  and not all(s.endswith(".0.0") for s in data.ids):
        raise HTTPException(422, "Invalid ids input for level 1. Make sure all inputs are 'x.0.0' format")
    
    #if level !=2  we return error
    #if any of the items are "x.0.0" or not all of the items have ".0" ie "2.1.3" then we return error
    elif data.level == 2  and ( not all(s.endswith(".0") for s in data.ids) or \
            any(s.endswith(".0.0") for s in data.ids)):
        raise HTTPException(422, "Invalid ids input for level 2. Make sure all inputs are 'x.x.0' format")
    
    #make sure that the ending is not "x.x.0" or "x.0.0" when level == 3
    elif data.level == 3  and any(s.endswith(".0") for s in data.ids):
        raise HTTPException(422, "Invalid ids input for level 3. Make sure all inputs are 'x.x.x' format where x != 0")
    
    resp = LookupJCA.get_noncovered_by_ids(ids=data.ids,level=data.level,db_conn=db_conn)
    return resp

@router.post("/cga/noncovered",response_model=List[str],status_code=200,name="retrieve noncovered ID spaces from a given list of ID's [in use]")
async def get_cga_noncovered_ids(data:CGANonCoveredInputModel,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    if data.level == "group_id" and any("." in s for s in data.ids):
        raise HTTPException(422,"Group ID must not be in the form of x.x")
    elif data.level == "gap_id" and not all("." in s for s in data.ids):
        raise HTTPException(422,"GAP ID must be in the form of x.x")
    
    resp = LookupCGA.get_noncovered_by_ids(ids=data.ids,level=data.level,db_conn=db_conn)
    return resp


@router.post("/kop-ksp/noncovered",response_model=List[str],status_code=200,name="retrieve noncovered ID spaces from a given list of ID's [in use]")
async def get_kp_noncovered_ids(data:JCANonCoveredInputModel,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    # breakpoint()
    #1.2.3.0
    if data.level == 3  and not all(s.count(".") == 3  and s.endswith(".0") for s in data.ids):
        raise HTTPException(422, "Invalid ids input for level 3. Make sure all inputs are 'x.x.x' format where x != 0")
    
    #1.2.3.4
    elif data.level == 4  and (any(s.count(".") == 3 and s.endswith(".0") for s in data.ids)):
        raise HTTPException(422, "Invalid ids input for level 4. Make sure all inputs are 'x.x.x.x' format where x != 0")
    
    resp = LookupKOPKSP.get_noncovered_by_ids(ids=data.ids,level=data.level,db_conn=db_conn)
    return resp

#get zbt_summary
@router.post("/zbt/program_summary",status_code=200,name="retrieve zbt program summary all [in use]")
async def get_zbt_summary(model:ZbtSummaryFilterInputModel, background_tasks:BackgroundTasks,db_conn=Depends(get_socom_session),
                          redis=Depends(get_redis_decoded),
                          user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    """retrieve zbt program summary all with query parameters"""
    approval_filter = model.APPROVAL_FILTER[0] if isinstance(model.APPROVAL_FILTER, list) else model.APPROVAL_FILTER
    key = f"api::/socom/zbt/program_summary::{approval_filter}"
    lock_key = f"{key}::status"
    
    try:

        pom_year = UsrLookupPOMPosition.get_active_pom_year_position(db_conn)[0]
        pom_year = int(pom_year)
        year_range = tuple(str(year) for year in range(pom_year,pom_year+5))
        
        query = ZBTQuery(zbt_extract_table=ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT"][0],
                ext_table=ZbtSummaryTableSet.CURRENT["EXT"][0],
                year_range=year_range)
        
        if redis.get(lock_key) == "running":
            return {"message": "A job is already in progress, please check back later."}
        #background task if key is not available
        elif key not in redis.keys() or model.REFRESH == True:
            redis.set(lock_key, "running", ex=120)  # Set status with a timeout of 2 minutes
            background_tasks.add_task(get_zbt_summary_fromdb,model,query,db_conn,redis)
            return {"message": "Task submitted, please check back in a few seconds"}
        
        resp = get_zbt_summary_fromdb(model,query,db_conn,redis)
        print(len(resp))
        
    except Exception as e:
        print(e)
        raise HTTPException(500,"Internal Server Error")
    return resp

#get iss_summary
@router.post("/iss/program_summary",status_code=200,name="retrieve iss program summary all [in use]")
async def get_iss_summary(model:IssSummaryFilterInputModel,background_tasks: BackgroundTasks, db_conn=Depends(get_socom_session),
                          redis=Depends(get_redis_decoded),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    """retrieve iss program summary all with query parameters"""
    approval_filter = model.APPROVAL_FILTER[0] if isinstance(model.APPROVAL_FILTER, list) else model.APPROVAL_FILTER
    key = f"api::/socom/iss/program_summary::{approval_filter}"
    lock_key = f"{key}::status"

    try:
        pom_year = UsrLookupPOMPosition.get_active_pom_year_position(db_conn)[0]
        pom_year = int(pom_year)
        
        orm_model = create_dynamic_table_class(table_name=IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0],
                                   AbstractORMClass=DtISSExtractModel)
        year_range = await orm_model.get_distinct_fiscal_years(db_conn)
        year_range = tuple(year_range)
        print(year_range)

        query = ISSQuery(
                    ext=IssSummaryTableSet.CURRENT["EXT"][0],
                    zbt=IssSummaryTableSet.CURRENT["ZBT"][0],
                    iss_extract=IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0],
                    zbt_extract=IssSummaryTableSet.CURRENT["ZBT_EXTRACT"][0],  
                    year_range=year_range
                )

        if redis.get(lock_key) and redis.get(lock_key) == "running":
            return {"message": "A job is already in progress, please check back later."}        
        #background task if key is not available.
        elif key not in redis.keys() or model.REFRESH == True:
            redis.set(lock_key, "running", ex=120)  # Set status with a timeout of 2 minutes
            background_tasks.add_task(get_iss_summary_fromdb,model,query,db_conn,redis)
            return {"message": "Task submitted, please check back in a few seconds"}
        
        # resp = get_iss_summary_fromdb(model,query,db_conn,redis)
        resp = get_iss_summary_fromdb(model,query,db_conn,redis)
        print(len(resp))
        
    except Exception as e:
        print(e)
        raise HTTPException(500,"Internal Server Error")
    
    finally:
        db_conn.close()
    
    return resp

@router.post("/iss/ad_recommendations/latest",status_code=200,name="get the latest ad recommendations")
async def latest_ad_recommendations_view(event_names:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_latest_ad_recommendations(event_names,db_conn)
    return data

@router.get("/zbt/event_summary/", status_code=200, name="get event summary for zbt summary subapp [in use]")
async def zbt_event_summary_list_view(event_names: List[str] = Query(None), db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await get_zbt_event_summary_view(event_names, db_conn)
    return result

@router.get("/iss/event_summary/", status_code=200, name="get event summary for iss summary subapp [in use]")
async def iss_event_summary_list_view(event_names: List[str] = Query(None), db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await get_iss_event_summary_view(event_names, db_conn)
    return result

@router.post("/iss/event_summary/events",status_code=200,name="get event summary for iss summary subapp [in use]")
async def iss_event_summary_list_view(event_names:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await get_iss_event_summary_list_view(event_names,db_conn)
    # breakpoint()
    return result

@router.post("/iss/event_summary/events/export",status_code=200,name="get event summary for iss summary subapp [in use]")
async def iss_event_summary_list_view(event_names:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await get_iss_event_summary_list_export(event_names,db_conn)
    return result

@router.post("/iss/event_summary/detail_summary",status_code=200,name="get event summary for the iss extract detailed summary coa run")
async def get_event_summary_detail_summary(coa_id:List[int],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await process_event_detailed_summary(coa_id,db_conn)
    return result
@router.post("/metadata/pom-position/dt-tables",status_code=200,name="get dt tables based on given pom position and year [in user]")
async def get_current_pom_position_tables(model:PomPositionInput,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_dt_tables_from_pom_position(model,db_conn)
    return data


@router.get("/metadata/pom-position/active",status_code=200,name="refresh the backend to the currently active POM position/year [in use]")
async def refresh_active_pom_positon(db_conn=Depends(get_socom_session),redis=Depends(get_redis_decoded),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await set_current_active_pom_position(db_conn,redis)
    return {'status':result}


@router.post("/pb-comparison/agg",status_code=200,name="retrieve PB funding with dynamic group bys")
async def get_pb_comparison_funds(model:AggPbFundingInput,groupby:List[str],inflation_adj:bool,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_pb_comparison_agg(model,groupby,inflation_adj, db_conn,)
    return data


@router.post("/budget-execution/agg",status_code=200,name="sum actual of budget execution, enacted line")
async def get_enacted_budget_sum(model: AggBudgetExecInput, groupby: List[str],inflation_adj:bool,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_budget_exec_agg(model,groupby,inflation_adj,db_conn)
    return data

@router.get("/pb-comparison/dash-lines",status_code=200,name="getting dashed lines for pb comparison")
async def get_pb_dashed_lines(db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = DtBudgetExecution.get_pb_dash_lines(db_conn)
    return data

@router.get("/metadata/get-capability-categories",status_code=200,name="retrieve all cap sponsor code given a category")
async def get_all_categories_from_cap(db_conn= Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = DtPBComparison.get_all_cap_sponsor_categories(db_conn)
    return {"mapping":data,"sub_category_showing":["TSOCs","COMPONENT"]}


@router.post("/ams/fem/agg",status_code=200,name="retrieve amount, obligation amount, and expend plan amount given a list of prog groups")
async def get_ams_fem_funding(program_groups:List[str],resource_cat_codes:List[str],db_conn=Depends(get_socom_session),redis=Depends(get_redis_decoded),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):    
    data = await get_ams_fem_funding_agg(program_groups,resource_cat_codes, db_conn,redis)
    return data


@router.post("/ams/metadata/pxid",status_code=200,name="retrieve pxid given program groups/program codes")
async def get_ams_fem_funding(program_groups:List[str], program_codes:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_pxid_from_programs( program_groups,program_codes,db_conn)
    return data


@router.get("/ams/metadata/descriptions",status_code=200,name="retrieve ams descriptions metadata from program groups")
async def get_ams_fem_funding(program_group:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_ams_desc_from_prog_groups(program_group,db_conn)
    return data

@router.get("/ams/metadata/milestones",status_code=200,name="retrieve ams proc strat metadata from program groups")
async def get_ams_milestone_strat(program_group:str,program_fullnames:Optional[List[str]]=Query(None),db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_ams_milestones(program_group,program_fullnames, db_conn)
    return data

@router.get("/ams/milestones/requirements",status_code=200,name="retrieve requirements from a given PXID and milestone [in use]")
async def get_ams_milestones_req(pxid:int,milestone:str,milestone_status:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_ams_milestones_reqs(pxid,milestone,milestone_status, db_conn)
    return data

@router.post("/ams/fielding/agg",status_code=200,name="retrieve plan quantity and actual quantity based on components (CAP SPONSOR) and FY")
async def get_ams_fielding_components(program_group:str,components:List[str],fy:Optional[int]=Query(None),fielding_items:Optional[List[str]]=Query(None),
                                      fielding_types:Optional[List[str]]=None,db_conn=Depends(get_socom_session),
                                      user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await get_ams_fielding_from_pg(program_group,fielding_items,components,fy,fielding_types,db_conn)
    return data


@router.post("/ams/budgets/prog/comparison")
async def get_ams_budgets_comparison_view(program_group:List[str],db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = await comparison_budget_view(program_group, db_conn)
    return data


@router.post("/dt_table/upload")
async def upload_dt_replace_tables(row_id:int,background_tasks:BackgroundTasks,
                                   redis=Depends(get_redis_decoded),
                                   user=Depends(require_roles(UserRole.ADMIN)),
                                   db_conn=Depends(get_socom_session)):
    from rds.table_model.socom.UsrDtUploads import UsrDtUploads

    if redis.exists("SOCOM::DT_UPLOAD::LOCK"):
        raise HTTPException(status_code=409, detail="Another upload job is in progress. Please check back in a few minutes.")
    
    row_meta = UsrDtUploads.get_file_download_metadata(row_id,db_conn)
    table_type = row_meta['TYPE']
    redis.set("SOCOM::DT_UPLOAD::LOCK",1,ex=60*2)
    if table_type == "DT_UPLOAD_BASE_UPLOAD":
        background_tasks.add_task(upload_dt_position_tables,row_id,user,redis)
    
    elif table_type == "DT_OUT_POM":
        val = validate_oop_parsing(row_id)
        if not val:
            raise HTTPException(422,"Basic requirements are not met, please check that PB is done prior to ENT/ACTUALS for the current year")
        background_tasks.add_task(upload_dt_oop_tables,row_id,user,redis)
    else:
        raise HTTPException(400,"User request error. Check on upload Type")
    return {"message":"table submission upload in progress. Please check back later"}



@router.post("/zbt/event_summary/events", status_code=200, name="get event summary for zbt summary subapp [in use]")
async def zbt_event_summary_list_view(event_names: List[str], db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await get_zbt_event_summary_list_view(event_names, db_conn)
    return result

@router.post("/zbt/event_summary/events/export", status_code=200, name="get event summary for zbt summary subapp [in use]")
async def zbt_event_summary_list_export(event_names: List[str], db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await get_zbt_event_summary_list_export(event_names, db_conn)
    return result

@router.post("/zbt/event_summary/detail_summary", status_code=200, name="get event summary for the zbt extract detailed summary coa run")
async def get_zbt_event_summary_detail_summary(coa_id: List[int], db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    result = await process_event_detailed_summary(coa_id, db_conn)
    return result


#upsert tables
@router.post("/dt_table/upsert",status_code=201,name="upsert ZBT & ISS records from dirty to final table based on pom position and year")
async def upsert_table_rows(position: str, db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN))):

    if position == 'zbt':
        orm_model = create_dynamic_table_class(AbstractORMClass=DtZBTExtractModel, table_name=ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT"][0])
        dirty_orm_model = create_dynamic_table_class(AbstractORMClass=DtZBTExtractDirtyModel, table_name=ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT_DIRTY"][0])
    elif position == 'iss':
        orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractModel, table_name=IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0])
        dirty_orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractDirtyModel, table_name=IssSummaryTableSet.CURRENT["ISS_EXTRACT_DIRTY"][0])
    else:
        raise HTTPException(422, f"Invalid 'position' value: '{position}'. Expected 'zbt' or 'iss'.")

    data = await dirty_orm_model.upsert_table_rows(orm_model, db_conn)
    return data   


@router.post("/dirty-table/{row_id}",status_code=201,name="parse iss/zbt into dirty table")
async def parse_upload_extract_dirty(row_id:int,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    upload_extract_dirty_table(row_id,db_conn)
    return {"status":1}