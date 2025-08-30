from fastapi import HTTPException
import numpy as np

from typing import (
    Dict,
    List,
    Union,
    Literal,
    Optional
)
from pydantic import (
    BaseModel,
    root_validator, 
    ValidationError
)

class OptimizerInputModel(BaseModel):
    ProgramIDs: List[str]
    must_include:Union[List[str]] = []
    must_exclude:Union[List[str]] = []
    budget: Optional[List[Union[int,float]]] = None #bogey values, manually input from UI
    syr: int
    eyr: int
    option: Optional[int] #Optional[Literal[1,2,3]] = 1 #only 1,2 or 3 or None if storm flag
    weight_id:Optional[int] #USR_LOOKUP_CRITERIA_WEIGHTS
    score_id: Optional[List[Dict]] #USR_OPTION_SCORES. #{"USER_ID":ID,"PROGRAM_ID":ID}
    support_all_years:bool
    storm_flag: Optional[bool] = False
    criteria_name_id: int
    use_iss_extract: bool = False
    per_resource_optimizer: bool = False

    @root_validator(pre=True)
    def check_option_based_on_storm_flag(cls, values):
        storm_flag = values.get('storm_flag', False)
        option = values.get('option')
        weight_id = values.get('weight_id',None)
        score_id = values.get('score_id',[])
    
        # If storm_flag is False, option must be present
        if not storm_flag and option is None:
            raise HTTPException(422,'option must be provided when storm_flag is False')
        
        if not weight_id and len(score_id) > 0:
            print(score_id)
            raise HTTPException(422, 'weight_id must be provided if score_id is provided')
        # If storm_flag is True, option can be None (it's already optional)
        return values

#V2
class OptimizerInputModelV2(OptimizerInputModel):
    budget: Optional[List[int]] = None #cut by raw values
    # budget_perc: Optional[List[float]] #cut by %. [0.08,0.08] means 8%
    support_all_years:Optional[bool] = False #default, unuse
    use_iss_extract: Optional[bool] = False #default, unuse
    num_tranches: int
    percent_allocation: List[float]
    tranches: List[float] #to cut %, not to keep
    cut_by_percentage: Optional[float] = None #user to cut by a flat percentage of the portfolio. Ie: 8% = 0.08
    keep_cutting: bool = False
    @root_validator(pre=True)
    def check_inputs(cls,values):
        """
        define validations here. Values is the input dict basically
        """

        num_tranches = values.get('num_tranches')
        percent_allocation = values.get('percent_allocation')
        tranches = values.get('tranches')
        budget = values.get('budget')
        cut_by_percentage = values.get('cut_by_percentage')
        must_include = set(values.get('must_include'))
        must_exclude = set(values.get('must_exclude'))

        if len(tranches) != len(percent_allocation)  or len(tranches) != num_tranches:
            raise HTTPException(422,'size of tranches, percent_alloc and num of tranches need to be the same')

        elif not (1 <= num_tranches <= 4):
            raise HTTPException(422,'num_tranches need to be between 1 and 4')
        
        if not must_include.isdisjoint(must_exclude): #overlapped
            raise HTTPException(422, 'must_include and must_exclude lists must be disjoined, no overlapping')
        
        elif (cut_by_percentage is None) == (budget is None):
            raise HTTPException(422,'XOR between cut by percentage option or manual budget option')
    
        elif sum(percent_allocation) != 1:
            raise HTTPException(422,'Percent Allocation needs to add up to 100%')

        return values #needed. reconstruction
    
class ProgramOutput(BaseModel):
    program_id: str
    program_group: str
    program_code:str
    pom_sponsor: Optional[str]
    capability_sponsor: Optional[str]
    assessment_area_code: Optional[str]
    execution_manager_code: Optional[str]
    resource_category_code: Optional[str]
    event_name: Optional[str] = "" 
    eoc_code: Optional[str]
    osd_pe: Optional[str]
    weighted_guidance_score: Optional[str]
    weighted_pom_score: Union[float,int]
    resource_k: Union[None,Dict[str,Union[int,float]]]
    total_storm_score: Union[float,int]

class OptimizerOutputModel(BaseModel):
    """this is used as an input for filtering post optimization run. General purpose"""
    resource_k: Union[Dict[str,Dict[str,Union[int,float]]], None]
    selected_programs: Union[None, List[ProgramOutput]]
    remaining: Dict[str,Union[int,float]]

class OptimizerFilterParams(BaseModel):
    filter_zero_resource_k: bool


class COAWeightedScoresInputModel(BaseModel):
    weight_id: int
    user_id: int
    program_ids: List[str]
    criteria_name_id: int
    # pom: bool = True
    # guidance: bool = True
    # storm: bool = True


#{coa1:[1,2,3]}
class MergeCoaInputModel(BaseModel):
    coa_selection: Dict[int,List[int]] #{coa_id:[indices of chosen rows]}
    user_id: int
    
    