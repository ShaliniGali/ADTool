from fastapi import APIRouter
from typing import List
from enum import Enum

from api.internal.socom_models import (
    OptimizerInputModel,
    OptimizerInputModelV2, 
    OptimizerOutputModel, 
    OptimizerFilterParams,
    COAWeightedScoresInputModel
)
from api.internal.conn import get_socom_session
from optimizer.socom_maximizer import (
    get_resource_k_from_progIds,
    get_resource_k_from_progIdsV2,
    post_optimization_filter,
    get_weighted_coa_scores,
    get_delta_amt_from_progIds
    )

from optimizer.alignment import (
    calculate_opt_alignment,
    calculate_opt_alignment_v2,
    calculate_manual_override_alignment,
    calc_detail_summary_eoc_funding,
    calculate_manual_override_alignment_v2,
)

from authentication.auth import (
    # get_current_user,
    UserRole,
    require_roles,
)

from optimizer.socom_maximizer import ProgSerializer

from fastapi import HTTPException, Depends

router = APIRouter(
    prefix="/optimizer",
    tags=["Optimizer"],
    responses={404:{"description":"Endpoint Not Found"}}
)

@router.post("/calculate_budget",status_code=200,name = 'calculate/optimize budget [in use]')
async def calculate_budget(model:OptimizerInputModel,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    if model.use_iss_extract:
        data = get_delta_amt_from_progIds(db_conn,model)
    else:
        data = get_resource_k_from_progIds(db_conn,model)
    if not data: #{}
        raise HTTPException(422, 'Could not process the input data')
    return data


@router.post("/v2/calculate_budget",status_code=200,name = "calculate/optimize budget by cutting [in use]")
async def calculate_budget_v2(model:OptimizerInputModelV2,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    if model.use_iss_extract:
        data = None
    else:
        data = get_resource_k_from_progIdsV2(db_conn,model)
    if not data:
        raise HTTPException(422,"Could not process the input data")
    return data



@router.post("/filter_budget",status_code=200,name= 'filter budgets post calculation [in use]')
async def filter_optimizer_output(model:OptimizerOutputModel,filter:OptimizerFilterParams,user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    try:
        data = post_optimization_filter(model,filter)
    except Exception as e:
        print(e)
        raise HTTPException(422,'Could not process the input data, please check the format')
    return data


@router.post("/weighted_scores",status_code=200, name="get the weighted score/storm score for saved coa [in use]")
async def optimizer_saved_coa_score(model:COAWeightedScoresInputModel,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    try:
        data = get_weighted_coa_scores(model=model,v2=False,db_conn=db_conn)
    except Exception as e:
        print(e)
        raise HTTPException(422,'Could not process the input data, please check the the request body parameters')
    return data

@router.post("/v2/weighted_scores",status_code=200, name="get the weighted score/storm score for saved coa [in use]")
async def optimizer_saved_coa_score(model:COAWeightedScoresInputModel,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    try:
        data = get_weighted_coa_scores(model=model,v2=True,db_conn=db_conn)
    except Exception as e:
        print(e)
        raise HTTPException(422,'Could not process the input data, please check the the request body parameters')
    return data


#########
#Alignments
class CoaType(str, Enum):
    ISS = "iss"
    ISS_EXTRACT = "iss-extract"


@router.get("/detail_summary/eoc_fund")
async def get_eoc_funding(coa_id:int,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    data = await calc_detail_summary_eoc_funding(coa_id=coa_id,db_conn=db_conn)
    return data

@router.get("/jca_alignment/opt-run",status_code=200,name="get jca alignment from given COA run [in use]")
async def get_jca_alignment(id:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    align_output = calculate_opt_alignment(id=id,alignment_type="JCA",db_conn=db_conn)
    data["absolute_alignment"] = align_output.value
    return data    

@router.get("/cga_alignment/opt-run",status_code=200,name="get jca alignment from given COA run [in use]")
async def get_cga_alignment(id:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    align_output = calculate_opt_alignment(id=id,alignment_type="CGA",db_conn=db_conn)
    data["absolute_alignment"] = align_output.value
    return data


@router.get("/kop_ksp_alignment/opt-run",status_code=200,name="get jca alignment from given COA run [in use]")
async def get_kop_ksp_alignment(id:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    align_output = calculate_opt_alignment(id=id,alignment_type="KOP_KSP",db_conn=db_conn)
    data["absolute_alignment"] = align_output.value
    return data

#######
@router.get("/v2/jca_alignment/opt-run",status_code=200,name="get jca alignment from given COA run [in use]")
async def get_jca_alignment(id:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    align_output = calculate_opt_alignment_v2(id,"JCA",db_conn)
    data["absolute_alignment"] = align_output.value
    return data    

@router.get("/v2/cga_alignment/opt-run",status_code=200,name="get jca alignment from given COA run [in use]")
async def get_cga_alignment(id:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    align_output = calculate_opt_alignment_v2(id,"CGA",db_conn)
    data["absolute_alignment"] = align_output.value
    return data

@router.get("/v2/kop_ksp_alignment/opt-run",status_code=200,name="get jca alignment from given COA run [in use]")
async def get_kop_ksp_alignment(id:str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    align_output = calculate_opt_alignment_v2(id,"KOP_KSP",db_conn)
    data["absolute_alignment"] = align_output.value
    return data    

########    
@router.get("/jca_manual_override", status_code=200, name="get the manual override for saved coa [in use]")
async def get_jca_manual_override_alignment(id: str, db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    override_output = await calculate_manual_override_alignment(id=id,alignment_type="JCA",db_conn=db_conn)

    data["absolute_alignment"] = override_output.value

    return data

@router.get("/cga_manual_override", status_code=200, name="get the manual override for CGA [in use]")
async def get_cga_manual_override(id: str,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    override_output = await calculate_manual_override_alignment(id=id,alignment_type="CGA",db_conn=db_conn)
    data["absolute_alignment"] = override_output.value
    return data

@router.get("/kp_manual_override", status_code=200, name="get the manual override for KP [in use]")
async def get_kp_manual_override(id: str, db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    override_output = await calculate_manual_override_alignment(id=id,alignment_type="KOP_KSP",db_conn=db_conn)
    data["absolute_alignment"] = override_output.value
    return data

#######
@router.get("/v2/jca_manual_override", status_code=200, name="get the manual override for saved coa [in use]")
async def get_jca_manual_override_alignment(id:int, db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    override_output = await calculate_manual_override_alignment_v2(id,"JCA",db_conn)

    data["absolute_alignment"] = override_output.value

    return data
@router.get("/v2/cga_manual_override", status_code=200, name="get the manual override for CGA [in use]")
async def get_cga_manual_override(id:int,db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    override_output = await calculate_manual_override_alignment_v2(id,"CGA",db_conn)
    data["absolute_alignment"] = override_output.value
    return data
@router.get("/v2/kp_manual_override", status_code=200, name="get the manual override for KP [in use]")
async def get_kp_manual_override(id:int, db_conn=Depends(get_socom_session),user=Depends(require_roles(UserRole.ADMIN,UserRole.USER))):
    data = {}
    override_output = await calculate_manual_override_alignment_v2(id,"KOP_KSP",db_conn)
    data["absolute_alignment"] = override_output.value
    return data
