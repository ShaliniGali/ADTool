import json
from collections import defaultdict
from typing import List, Union, Dict
from pydantic import (
    BaseModel,
    Field,
    PositiveFloat,
    PositiveInt,
    validator
)
from api.internal.socom_models import (
    OptimizerOutputModel, 
    OptimizerFilterParams,
    COAWeightedScoresInputModel,
)

from rds.table_model.socom.LookupProgramDetail import (
    LookupProgramModel
)

from rds.table_model.socom.DtIssModel import DtIssueModel
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel

from rds.table_model.socom.UsrOptionScore import UsrOptionScore
from rds.table_model.socom.UsrLookupCriticalWts import UsrLookupCriticalWts
from rds.table_model.socom.LookupStorm import LookupStorm
from rds.table_model.socom.DtExtModel import DtExtModel


from api.internal.resources import (
    ResourceConstraintCOATableSet,
    create_dynamic_table_class,
    IssSummaryTableSet,
)

from fastapi import HTTPException
import numpy as np
from copy import deepcopy

class ProgData(BaseModel):
    #model for each program id with resource and weights
    program_id: str = Field(default='')
    program_code: str = Field(default='')
    program_group: str = Field(default='')
    pom_sponsor: str = Field(default='')
    capability_sponsor: str = Field(default='')
    assessment_area_code: str = Field(default='')
    eoc_code:str = Field(default='')
    resource_category_code:str = Field(default='')
    osd_pe:str = Field(default='')
    execution_manager_code:str = Field(default='')
    weighted_guidance_score: Union[int,float] = Field(default=0)
    weighted_pom_score: Union[int,float] = Field(default=0)
    resource_k: Dict[str,Union[PositiveFloat,PositiveInt]] = Field(default={}) 
    total_storm_score: Union[int, float] = Field(default=0)
    
    #validation from pydantic side
    @validator('weighted_guidance_score', pre=True)
    def round_weighted_guidance_score(cls, val):
        rounded_value = round(float(val), 2)
        return int(rounded_value) if rounded_value.is_integer() else rounded_value

    @validator('weighted_pom_score', pre=True)
    def round_weighted_pom_score(cls, val):
        rounded_value = round(float(val), 2)
        return int(rounded_value) if rounded_value.is_integer() else rounded_value  


class ProgEventData(ProgData):
    event_name: str = Field(default='')


class ProgSerializer:
    def __init__(self):
        """
        data: ["prog ID","prog name","pom sponsor code","capability sponsor code", "fiscal year","resource_k"]
        """
        self.data = []

        
    def __repr__(self):
        return f"{self.data}"

    def serialize(self,data,use_iss_extract):
        # print("\n================================================")
        # print(data)
        #data: [("prog ID","prog name","pom sponsor code","capability sponsor code", "fiscal year","resource_k"),...]
        
        if not use_iss_extract: #RC type
            for row in data:
                (
                    program_id,
                    program_code,
                    program_group,
                    assessment_area_code,
                    eoc_code,
                    pom_sponsor,
                    capability_sponsor,
                    resource_category_code,
                    osd_pe,
                    execution_manager_code,
                    fiscal_year,
                    resource_k
                ) = row
                input_model = ProgData(
                    program_id=program_id,
                    program_code=program_code,
                    program_group=program_group,
                    assessment_area_code=assessment_area_code,
                    eoc_code=eoc_code,
                    pom_sponsor=pom_sponsor,
                    capability_sponsor=capability_sponsor,
                    resource_category_code=resource_category_code,
                    osd_pe=osd_pe,
                    execution_manager_code=execution_manager_code,
                )
                input_model.resource_k[str(fiscal_year)]=float(resource_k)
                self.data.append(input_model)    
        
        else:
            for row in data:
                (
                    program_id,
                    program_code,
                    program_group,
                    assessment_area_code,
                    eoc_code,
                    pom_sponsor,
                    capability_sponsor,
                    resource_category_code,
                    osd_pe,
                    execution_manager_code,
                    event_name,
                    fiscal_year,
                    resource_k
                ) = row
                input_model = ProgEventData(
                    program_id=program_id,
                    program_code=program_code,
                    program_group=program_group,
                    assessment_area_code=assessment_area_code,
                    eoc_code=eoc_code,
                    pom_sponsor=pom_sponsor,
                    capability_sponsor=capability_sponsor,
                    resource_category_code=resource_category_code,
                    osd_pe=osd_pe,
                    execution_manager_code=execution_manager_code,
                    event_name=event_name
                )
                input_model.resource_k[str(fiscal_year)]=float(resource_k)
                    
                
                self.data.append(input_model)    
                # print("\n================================")
                # print(self.data)


class ProgSerializerV2(ProgSerializer):
    def __init__(self):
        super().__init__()
    def serialize(self,data):
        for prog_id,prog_name,prog_group,program_code,pom_sponsor_code,capability_sponsor_code,assessment_area_code, \
            exec_manager_code, resource_cat, eoc_code, osd_pe, fiscal_year, resource_k in data:
            
            input_model = ProgData()
            
            input_model.resource_k[str(fiscal_year)] = float(resource_k) 
            input_model.program_id = prog_id
            input_model.program_code = program_code
            input_model.program_group = prog_group
            input_model.capability_sponsor = capability_sponsor_code
            input_model.pom_sponsor = pom_sponsor_code
            input_model.assessment_area_code = assessment_area_code
            input_model.execution_manager_code = exec_manager_code
            input_model.resource_category_code = resource_cat
            input_model.eoc_code = eoc_code
            input_model.osd_pe = osd_pe
            self.data.append(input_model)    
    

##------------------------------------------
#end of entity definition
##------------------------------------------

def get_resource_k_from_progIds(db_conn,model):
    """
    retrieve from the db with info: ProgIds, Resources_k, Prog Name, POM Sponsor Code,
    Capability Sponsor code, Fiscal Year columns. Then filter out with the input ProgIds
    Args:
        db_conn (Session): connection to the database
        model: optimizer input model
    Return:
        Json
    """
    table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
    orm_model = create_dynamic_table_class(table_name,DtIssueModel)
    # data = LookupProgramModel.get_resource_k_from_progIds(orm_model,db_conn,model.ProgramIDs)
    data = orm_model.get_resource_k_by_pids(model.ProgramIDs,db_conn)
    
    serializer = ProgSerializer()
    serializer.serialize(data,use_iss_extract=False)
    
    wts_score = {id:{"weighted_guidance_score":0,"weighted_pom_score":0} for id in model.ProgramIDs} #default if not yet scored
    
    if model.score_id:
        #weighted pom and guidance scores for each proID
        wts_score = UsrOptionScore.calculate_weights(
            db_conn,
            UsrLookupCriticalWts,
            model.weight_id,
            model.score_id,
            model.criteria_name_id,
            storm_flag=model.storm_flag
        )

    storm_score = LookupStorm.get_total_score_from_progIds(LookupProgramModel,db_conn,prog_ids=model.ProgramIDs,to_dict=True)
    storm_score = {id:storm_score[id] if id in storm_score else 0 for id in model.ProgramIDs}

    # print(wts_score)

    #combine the weighted scores and storm scores if possible - default to zero if not exist
    # Result dictionary
    temp = {}
    default_keys = ["weighted_guidance_score", "weighted_pom_score", "total_storm_score"]
    for key in set(wts_score.keys()).union(storm_score.keys()):
        # Initialize with defaults
        temp[key] = {k: 0 for k in default_keys}
        
        # Update with values from `a` if they exist
        if key in wts_score:
            temp[key].update(wts_score[key])
        
        # Add `key3` from `b` if it exists
        if key in storm_score:
            temp[key]["total_storm_score"] = storm_score[key]

    # wts = {prog_id:{"pom":25,"guidance":28} for prog_id in model.ProgramIDs}
    wts_score = temp #inclusive of both weighted scores if scored with score id's and all the programs in the storm scores
    for prog_data in serializer.data:
        prog_data.weighted_guidance_score = wts_score.get(prog_data.program_id,{}).get("weighted_guidance_score",0)
        prog_data.weighted_pom_score = wts_score.get(prog_data.program_id,{}).get("weighted_pom_score",0)
        prog_data.total_storm_score = wts_score.get(prog_data.program_id,{}).get("total_storm_score",0)
    
    resp = optimizer(serializer,model) 
    return resp #serializer.data

def get_delta_amt_from_progIds(db_conn,model):
    """
    retrieve from the db with info: ProgIds, delta_amt, Prog Name, POM Sponsor Code,
    Capability Sponsor code, Fiscal Year columns. Then filter out with the input ProgIds
    DT_ISS_EXTACT_2026 table
    Args:
        db_conn (Session): connection to the database
        model: optimizer input model
    Return:
        Json
    """
    
    table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
    orm_model = create_dynamic_table_class(table_name,DtISSExtractModel)
    data = orm_model.get_delta_amt_by_pids(model.ProgramIDs,db_conn)
    
    serializer = ProgSerializer()
    serializer.serialize(data=data,use_iss_extract=True)
    
    wts_score = {id:{"weighted_guidance_score":0,"weighted_pom_score":0} for id in model.ProgramIDs} #default if not yet scored
    
    if model.score_id:
        #weighted pom and guidance scores for each proID
        wts_score = UsrOptionScore.calculate_weights(
            db_conn,
            UsrLookupCriticalWts,
            model.weight_id,
            model.score_id,
            model.criteria_name_id,
            storm_flag=model.storm_flag
        )

    storm_score = LookupStorm.get_total_score_from_progIds(LookupProgramModel,db_conn,prog_ids=model.ProgramIDs,to_dict=True)
    storm_score = {id:storm_score[id] if id in storm_score else 0 for id in model.ProgramIDs}

    # print(wts_score)

    #combine the weighted scores and storm scores if possible - default to zero if not exist
    # Result dictionary
    temp = {}
    default_keys = ["weighted_guidance_score", "weighted_pom_score", "total_storm_score"]
    for key in set(wts_score.keys()).union(storm_score.keys()):
        # Initialize with defaults
        temp[key] = {k: 0 for k in default_keys}
        
        # Update with values from `a` if they exist
        if key in wts_score:
            temp[key].update(wts_score[key])
        
        # Add `key3` from `b` if it exists
        if key in storm_score:
            temp[key]["total_storm_score"] = storm_score[key]

    # wts = {prog_id:{"pom":25,"guidance":28} for prog_id in model.ProgramIDs}
    wts_score = temp #inclusive of both weighted scores if scored with score id's and all the programs in the storm scores
    # breakpoint()
    # if model.storm_flag:
    for prog_data in serializer.data:
        prog_data.weighted_guidance_score = wts_score.get(prog_data.program_id,{}).get("weighted_guidance_score",0)
        prog_data.weighted_pom_score = wts_score.get(prog_data.program_id,{}).get("weighted_pom_score",0)
        prog_data.total_storm_score = wts_score.get(prog_data.program_id,{}).get("total_storm_score",0)

    resp = optimizer(serializer,model) 
    # print(resp)
    return resp #serializer.data

def transform_for_knapsack(data):
    """
    Concatenates resource_k for the same program_id together
    Reformat into [ProgData(prog_id:id,resource_k:{year:val,year:val..}),{...}] format
    Args:
        data (list[ProgData]): [{program_id:id,resource_k:{year:val}},{...}] format
            Same progId has multiple records for different resource_k years
    Return:
        data (list[Dict]): [{program_id:id,resource_k:{year:val,year:val2...}},{...}] format
    """
    # print("\n================================================")
    # print("Transform for knapsack Data...")
    # print(data)
    data = [row.dict() for row in data]
    # print(data)
    new_data = {} #{"prog id":{}}
    for row in data:
        prog_id = row['program_id']
        if prog_id in new_data:
            
            new_data[prog_id]['resource_k'] = row['resource_k'] | new_data[prog_id]['resource_k']
        else:
            new_data[prog_id] = {}
            new_data[prog_id]['program_id'] = row['program_id']
            new_data[prog_id]['program_group'] = row['program_group']
            new_data[prog_id]['program_code'] = row['program_code']
            new_data[prog_id]['pom_sponsor'] = row['pom_sponsor']
            new_data[prog_id]['capability_sponsor'] = row['capability_sponsor']
            new_data[prog_id]['assessment_area_code'] = row['assessment_area_code']
            new_data[prog_id]['eoc_code'] = row['eoc_code']
            new_data[prog_id]['resource_category_code'] = row['resource_category_code']
            new_data[prog_id]['osd_pe'] = row['osd_pe']
            new_data[prog_id]['execution_manager_code'] = row['execution_manager_code']
            
            if 'event_name' in row: #for IO optimizer
                new_data[prog_id]['event_name'] = row['event_name']

            new_data[prog_id]['weighted_guidance_score'] = round(float(row['weighted_guidance_score']),2)
            new_data[prog_id]['weighted_pom_score'] = round(float(row['weighted_pom_score']),2)
            new_data[prog_id]['resource_k'] = row['resource_k']
            try:
                new_data[prog_id]['total_storm_score'] = round(float(row['total_storm_score']),2)
            except (TypeError, ValueError) as e:
                print("[Warnning] cannot round the numbers :", e)
                new_data[prog_id]['total_storm_score'] = 0
    new_data = [new_data[prog_id] for prog_id in new_data]
    # print(new_data)
    # breakpoint()
    return new_data

def custom_sort(data, must_include, option, storm_flag=False,per_resource_optimizer=False):
    """
    Args:
        Data (dict): {"program_id":ABC..,"pom_sponsor":...,"resource_k":{year:val,year:val},
        {"program_id":XYZ..,"pom_sponsor":...,"resource_k":{year:val,year:val}}
        must_include (list): list of program ids that we must include
        option (enum): 1,2 or 3 for different benchmark
        storm_flag (bool): whether we calculate by storm or not. If enabled, we dont sort by pom/guidance/both
        per_resource_optimizer(bool): enable optimization is done with dollar amount per score/storm

    Return:
        sorted_data (dict): same format as data, sorted based on either weighted guidance or pom scores.
    """
    
    # Separate elements that must be included
    included = [item for item in data if item['program_id'] in must_include]
    # Sort the remaining elements by the product of guidance_score and p_score
    remaining = [item for item in data if item['program_id'] not in must_include]
    
    #storm flag ignores the rest of the options
    if storm_flag:
        # breakpoint()
        if per_resource_optimizer:
            included = sorted(included, key=lambda x: x['total_storm_score']/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['total_storm_score']/0.001, reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['total_storm_score']/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['total_storm_score']/0.001, reverse=True)
        else:
            included = sorted(included, key=lambda x: x['total_storm_score'], reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['total_storm_score'], reverse=True)
    
    #both option
    elif option == 1:
        if per_resource_optimizer:
            # Sort programs by G[i]*P[i] in descending order
            included = sorted(included, key=lambda x: (x['weighted_guidance_score'] * x['weighted_pom_score'])/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['weighted_guidance_score'] * x['weighted_pom_score']/0.001, reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: (x['weighted_guidance_score'] * x['weighted_pom_score'])/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['weighted_guidance_score'] * x['weighted_pom_score']/0.001, reverse=True)
        else:
            included = sorted(included, key=lambda x: x['weighted_guidance_score'] * x['weighted_pom_score'], reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['weighted_guidance_score'] * x['weighted_pom_score'], reverse=True)
    
    #guidance     
    elif option == 2:
        if per_resource_optimizer:
            # Sort programs by G[i] in descending order
            included = sorted(included, key=lambda x: x['weighted_guidance_score']/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['weighted_guidance_score']/0.001, reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['weighted_guidance_score']/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['weighted_guidance_score']/0.001, reverse=True)
        else:
            included = sorted(included, key=lambda x: x['weighted_guidance_score'], reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['weighted_guidance_score'], reverse=True)
    #pom
    elif option == 3:
        if per_resource_optimizer:
            # Sort programs by P[i] in descending order
            included = sorted(included, key=lambda x: x['weighted_pom_score']/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['weighted_pom_score']/0.001, reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['weighted_pom_score']/sum(x["resource_k"].values()) if sum(x["resource_k"].values()) != 0 else x['weighted_pom_score']/0.001, reverse=True)

        else:
            included = sorted(included, key=lambda x: x['weighted_pom_score'], reverse=True)
            remaining_sorted = sorted(remaining, key=lambda x: x['weighted_pom_score'], reverse=True)

    else:
        raise ValueError("Invalid option number. Please choose 1, 2, or 3 or storm flag == True")
    
    # Combine the sorted lists
    sorted_data = included + remaining_sorted


    return sorted_data

def check_support_all_years_knapsack(leftover_budgets,program):
    #return true if a program can support all years, else false
    for year in leftover_budgets:
        if str(year) not in program['resource_k']: #program can be partially funded ie: [2026,2027,2030] EOC funding only
            continue
        elif leftover_budgets[year] - program['resource_k'][str(year)] < 0:
            return False
    return True

def maximize_option(data, must_include, option, syr, eyr, budgets, support_all_years, storm_flag=False,per_resource_optimizer=False):
    """
    Maximize the objective based on the given option number.

    Args:
    - data: List of dictionaries containing program information
    - option: Integer representing the option number (1, 2, or 3)
    - budgets: List of total allowed budgets for each year
    - support_all_years (bool): whether to support all years for each program. Will only select programs that supports all years
    - storm_flag (bool): maximize option by storm instead of POM/guidance
    - per_resource_optimizer (bool): maximize option by dollar per per-unit instead of full amount

    Returns:
    - selected_programs: List of selected programs based on the chosen option
    """
    selected_programs = []
    result_dict = {}
    leftover_budgets = {syr+i:budgets[i] for i in range(len(budgets))} #{2026:14000,2027:15000,...}
    sorted_data = custom_sort(data, must_include, option, storm_flag,per_resource_optimizer=per_resource_optimizer)
    # print(sorted_data)
        
    for program in sorted_data:
        # print(sorted_data)
        #check if support_all_years current program can fit into all years
        #retrofit
        if support_all_years and not check_support_all_years_knapsack(leftover_budgets,program):
            continue
        
        # for year, budget in zip(range(syr, eyr), budgets):
        for year,budget in leftover_budgets.items():
            if str(year) in program['resource_k']:
                if budget - program['resource_k'][str(year)] >= 0:
                    selected_programs.append(program)
                    leftover_budgets[year] -= program['resource_k'][str(year)]
                    if year not in result_dict:
                        result_dict[year] = {program['program_id']: program['resource_k'][str(year)]}
                    else:
                        result_dict[year][program['program_id']] = program['resource_k'][str(year)]
                
                #case where must include would not all fit into the total budget
                elif program['program_id'] in must_include and \
                     budget - program['resource_k'][str(year)] < 0:
                    raise HTTPException(status_code=422, detail="All Programs in Must include (priority) "+
                        f"cannot fit into the budget for this year: {year}")
            else:
                continue
            
    return selected_programs, result_dict

def check_exclusion_cut_val(data,syr,cut_matrix):
    exc_cut_matrix = np.zeros_like(cut_matrix[0], dtype=float)
    for row in data:
        for year in row['resource_k']:
            amount = row['resource_k'][year]
            yidx = int(year) - syr
            exc_cut_matrix[yidx] += amount
    
    #for each year, add up all tranches to see if we can afford full cuts for must excludes
    diff = np.sum(cut_matrix, axis=0) - exc_cut_matrix
    # print(diff)
    # breakpoint()
    # Check if any element is negative
    if np.any(diff < 0):
        raise HTTPException(
            status_code=400,
            detail=f"Cut matrix must cover all 'remove from play' resource funding per cell. {diff}."+ 
            "Need to increase the budget from affected years"
        )
def adjust_cut_overflow(cut_matrix, tidx, year_idx,cut_amount,keep_cutting=False):
    """
    Attempt to subtract 'amount' from cut_matrix[tidx, year_idx].
    If that causes a negative value, redistribute the overflow upward
    within the same year to higher tranches.
    """
    
    tranche_count = len(cut_matrix)
    #keep cutting is only available for the last tranche/retrofit
    if keep_cutting and tidx == (tranche_count - 1):
        cut_matrix[tidx,year_idx] -= cut_amount
        return cut_matrix
    #cannot distribute any further, last tranche
    elif tidx == tranche_count-1 and cut_matrix[tidx, year_idx] > 0:    
        # print(cut_matrix)
        cut_matrix[tidx,year_idx] -= cut_amount #cut if possible
        return cut_matrix
    elif tidx == tranche_count-1 and cut_matrix[tidx,year_idx] < 0: #
        return cut_matrix #cannot cut further/uncut
    
    # Compute overflow
    overflow = cut_amount - cut_matrix[tidx, year_idx] # Amount we couldn't cut
    cut_matrix[tidx, year_idx] = 0  # Clamp to 0 (only if not the last tranche, we took care of that condition above)

    # Redistribute overflow equally to higher tranches (same year)
    remaining_tranches = tranche_count - tidx - 1
    # print(remaining_tranches,overflow)
    normalized_overflow = np.ceil(overflow / remaining_tranches)

    for t in range(tidx + 1, tranche_count):
        cut_matrix[t, year_idx] -= normalized_overflow #redistribute the tranches above
        #cascade to upper tranches since we over distribute the later tranche
        #t+1 is actually 2 tranches above
        #if next tranche is negative again, then it will be re-adjusted in the next overflow - until 2 tranches below the last tranche
        if (t < tranche_count - 1) and (cut_matrix[t,year_idx] < 0):
            cut_matrix[t+1,year_idx] -= abs(cut_matrix[t,year_idx])
            cut_matrix[t,year_idx] = 0
    return cut_matrix


def cut_optimizer(data, tranches, must_include, must_exclude, syr, eyr, option, cut_matrix, keep_cutting,storm_flag=False,per_resource_optimizer=False):
    """
    Maximize the objective based on the given option number.

    Args:
    - data: List of dictionaries containing program information
    - tranches: for each program, what percentage do we cut per tranche. Keep %
    - option: Integer representing the option number (1, 2, or 3)
    - must_exclude: List if program ids to full cut
    - must_include: List of program ids to always cut less (highest tranches)
    - syr: int, Start year (included)
    - eyr: int, End year (not included)
    - cut_matrix: Numpy array (tranches,years) that tells us how much to cut for each years vs. tranches
    - keep_cutting: Bool. if last tranche is already negative, we will still cut if this option is True
    - storm_flag (bool): maximize option by storm instead of POM/guidance
    - per_resource_optimizer (bool): maximize option by dollar per per-unit instead of full amount

    Returns:
    - results_dict: {year:{program:chosen_funding},..}
    - selected_programs: [{prog details:...,{resource_k:{year:val}}}]
    """
    selected_programs = []
    num_tranches = len(tranches)

    data_exclude = [d for d in data if d['program_id'] in must_exclude]
    data_include = [d for d in data if d['program_id'] in must_include]
    data = [d for d in data if d['program_id'] not in (must_include+must_exclude)]
    # breakpoint()
    #sorts are descending, cut optimize is ascending in term of scores. Need to reverse
    sorted_data = custom_sort(data, [], option, storm_flag,per_resource_optimizer=per_resource_optimizer)
    sorted_include = custom_sort(data_include,[],option,storm_flag,per_resource_optimizer=per_resource_optimizer)
    sorted_exclude = custom_sort(data_exclude,[],option,storm_flag,per_resource_optimizer=per_resource_optimizer)

    data = sorted_exclude[::-1] + sorted_data[::-1] + sorted_include[::-1]
    # print([d['program_id'] for d in data])
    #check to see if we can cut all excluded section and fill up the tranches
    check_exclusion_cut_val(sorted_exclude,syr,cut_matrix)
    # breakpoint()
    funding_lines = []
    cutting_lines = []

    #{2026:{1:[...],...,"full_keep"}}
    tranche_assignment = tranche_assignment = {
            str(year): {tranche: [] for tranche in range(len(tranches))}
            for year in range(syr, eyr)
        }
    for year in tranche_assignment:
        tranche_assignment[year]["full_keep"] = []
    

    #iteration row by row in the matrix
    for prog in data:

        # Skip cutting if in must_include
        if prog['program_id'] in must_include:
            funding_lines.append(prog)
            selected_programs.append(prog)

            for year in sorted(prog['resource_k'].keys()):
                tranche_assignment[str(year)]["full_keep"].append({"program_id":prog['program_id'],"program_group":prog["program_group"]})  # always fully kept tranche
            continue
        
        temp_funding = deepcopy(prog)
        temp_cutting = deepcopy(prog)
        temp_cutting['resource_k'] = {year:0 for year in prog['resource_k']}

        for year in sorted(prog['resource_k'].keys()):

            pid = prog['program_id']
            amount = prog['resource_k'][year]
            yidx = int(year) - syr

            cut_made = False #any matrix change -> True, use for "fully_keep"

            for tidx in range(len(cut_matrix)):
                #do not record 0 funding lines,
                #last tranche, and negative -> cannot redistribute funding any further
                #unless we allow keep cutting
                if (amount == 0) or \
                    (tidx == num_tranches - 1 and cut_matrix[tidx,yidx] <= 0 and not keep_cutting):
                    break

                #current tranch is empty -> cannot cut. next tranche
                #skipping ONLY if not at the last tranche
                if (cut_matrix[tidx,yidx] == 0) and (tidx != num_tranches - 1):
                    continue
                cut_amount = amount if pid in must_exclude else amount*tranches[tidx]
                funding_amount = amount - cut_amount
                print(pid,cut_amount,funding_amount,year)
                print(cut_matrix)
                
                #keep cutting logic will autocut within the adjust_cut_overflow() function
                if cut_amount > cut_matrix[tidx,yidx]:
                    before_cut = cut_matrix[tidx,yidx]
                    cut_matrix = adjust_cut_overflow(cut_matrix,tidx,yidx,cut_amount,keep_cutting)
                    after_cut = cut_matrix[tidx,yidx]

                    if before_cut != after_cut: #detect a cut in the overflow
                        tranche_assignment[year][tidx].append({"program_id":prog['program_id'],"program_group":prog["program_group"]})
                        cut_made = True
                    
                else:
                    cut_matrix[tidx,yidx] -= cut_amount
                    tranche_assignment[year][tidx].append({"program_id":prog['program_id'],"program_group":prog["program_group"]}) #apply to tranche right AFTER cut
                    cut_made = True
                
                
                #break AFTER the cut assignment, regardless of overflown or not
                #if tranche is not available to assign (curr_tranche cut remaining == 0)
                #we have the continue statement above
                temp_funding['resource_k'][year] = np.floor(funding_amount)
                temp_cutting['resource_k'][year] = np.ceil(cut_amount)
                break
            
            #check if any cut was made -> if not assign to fully_keep tranche
            if not cut_made:
                tranche_assignment[str(year)]['full_keep'].append({"program_id":prog['program_id'],"program_group":prog["program_group"]})

        # print(cut_matrix)
        if sum(temp_funding['resource_k'].values()) > 0:
            funding_lines.append(temp_funding)
            selected_programs.append(temp_funding)
        if sum(temp_cutting['resource_k'].values()) > 0:
            cutting_lines.append(temp_cutting)


    results_dict = defaultdict(dict)
    # breakpoint()
    for entry in funding_lines:
        program_id = entry['program_id']
        for year, value in entry['resource_k'].items():
            results_dict[int(year)][program_id] = value
    print(cut_matrix)
    #if cut fundings are too much, 
    if np.any(cut_matrix[-1,:] > 0):
        raise HTTPException(
            status_code=400,
            detail="Cut matrix not enough to cover all programs, need to reduce cutting budget: "+
            f"{cut_matrix}"
        )
    
    return selected_programs,results_dict,cutting_lines,tranche_assignment            

def save_output(result,path:str):
    """
    Save result into a json formatted file at a given path
    Args:
        result (dict): dict of the optimized result
        path (str): path to save
    Return:
        None
    """
    with open (path,"w") as f:
        json.dump(result,f)

def obtain_selected_programs(selected_programs,results):

    seen_set = set()
    result_progs_ids = []
    selected_programs_new = []
    
    for year in results.keys(): #2026, 2027..ect
        result_progs_ids += list(results[year].keys())

    for prog_id in selected_programs:
        if prog_id['program_id'] in seen_set or prog_id['program_id'] not in result_progs_ids:
            continue
        seen_set.add(prog_id['program_id'])
        selected_programs_new.append(prog_id)
        
    return selected_programs_new

def optimizer(serializer:ProgSerializer,model:Dict): #(data, must_include, must_exclude, budgets, syr, eyr, option):
    data = serializer.data
    data = transform_for_knapsack(data)
    must_include = model.must_include
    must_exclude = model.must_exclude
    budgets = model.budget
    syr = model.syr
    eyr = model.eyr
    option = model.option
    support_all_years = int(model.support_all_years)
    storm_flag = model.storm_flag
    per_resource_optimizer = model.per_resource_optimizer
    
    
    results = {}

    #input not valid between budget/delta and the length of years
    if len(budgets) != len(range(syr, eyr)):
        raise HTTPException(status_code=422, detail="Input not found or Input parameters are not valid. "+
            "Please check that the list length of budgets and the start year/end year are the same size")

    # elif len(budgets) == len(range(syr, eyr)):
    data = [x for x in data if x['program_id'] not in must_exclude]
    selected_programs, results = maximize_option(data, must_include, option, syr, eyr, budgets, support_all_years,storm_flag,per_resource_optimizer)
    # breakpoint()
    _selected_programs = []
    #O(n^2), need to be careful on future optimizations
    #filter out duplicates selected programs
    for d in selected_programs:
        if d not in _selected_programs:
            _selected_programs.append(d)
    selected_programs = _selected_programs

    #get a leftover dictionary
    __leftover = {i+syr:budgets[i] for i in range(len(budgets))} #initialized as total budget
    budget_used = {year:sum(v.values()) for year,v in results.items()}
    # # leftover = [alloc - used for alloc,used in zip(budgets,budget_used)]
    leftover = {year: (__leftover[year] - budget_used[year]) if year in budget_used else __leftover[year] for year in __leftover.keys()} #calculating the leftover

    ###filter out the duplicate set from selected_programs
    selected_programs = obtain_selected_programs(selected_programs,results)

    #to catch case where support_all_years == 1 and there is only 1 program that common key, not ranging across all continuous years

    resp = {"resource_k":results,"selected_programs":selected_programs,"remaining":leftover}
    # breakpoint()
    if not per_resource_optimizer:
        return resp
    
    #calculate dollars per-unit for display if selected per-unit score
    for prog in resp["selected_programs"].copy():
        total_resources = sum(prog["resource_k"].values())
        # breakpoint()
        if storm_flag:
            #to avoid dividing by zero, we assign all the values to nulls if score is 0
            prog["storm_per_resource"] = round(prog["total_storm_score"]/total_resources,5) if total_resources > 0 else None
        elif option == 1: #both
            prog["guidance_and_pom_per_resource"] = round((prog["weighted_guidance_score"]*prog["weighted_pom_score"])/total_resources,5) if total_resources > 0 else None
        elif option == 2: #guidance
            prog["guidance_per_resource"] = round(prog["weighted_guidance_score"]/total_resources,5) if total_resources > 0 else None
        elif option == 3: #pom
            prog["pom_per_resource"] = round(prog["weighted_pom_score"]/total_resources,5) if total_resources > 0 else None

    return resp


####################
#cutting resource optimization
def parse_to_cut_matrix(db_conn,model):
        #return a matrix of (tranches,year) to tell us how much we need to cut for each year/tranche
        table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
        orm_model = create_dynamic_table_class(table_name,DtIssueModel)
        
        resource_k = LookupProgramModel.get_resource_k_from_progIds(orm_model,db_conn,model.ProgramIDs)

        #process based on percentage
        if model.cut_by_percentage:
            #parse the resource_k into {2026:sum, 2027:sum,..}
            sum_k_years = {}
            for row in resource_k:
                year = row[-2]
                val = row[-1]
                if year: #non-null
                    sum_k_years[int(year)] = sum_k_years.get(year,0) + val #int key for sorting
            # breakpoint()
            sum_k_years = [float(value) for year, value in sorted(sum_k_years.items())] #[sum2026,sum2027,...]
            #now cut by %
            cut_list = [val*model.cut_by_percentage for val in sum_k_years]
        
        #manual input cut budget 
        else:
            cut_list = model.budget #shape (years,1)

        if len(cut_list) != len(range(model.syr,model.eyr)):
            raise HTTPException(422, "start year and end year do not match with the year range in the database!")

        tranches_alloc = np.array(model.percent_allocation)  # shape (tranches,1)
        cut_matrix = np.outer(tranches_alloc, cut_list)  # shape (tranches,years)
        return cut_matrix,resource_k


def get_resource_k_from_progIdsV2(db_conn,model):
    """
    retrieve from the db with info: ProgIds, Resources_k, Prog Name, POM Sponsor Code,
    Capability Sponsor code,.., Fiscal Year columns. Then filter out with the input ProgIds
    Args:
        db_conn (Session): connection to the database
        model: optimizer input model
    Return:
        Json
    """
    #V2 is called LookupProgramModel
    
    # data = LookupProgramModel.get_resource_k_from_progIds(orm_model,db_conn,model.ProgramIDs)
    cut_matrix,resource_k = parse_to_cut_matrix(db_conn,model)
    serializer = ProgSerializerV2()
    serializer.serialize(resource_k)
    
    wts_score = {id:{"weighted_guidance_score":0,"weighted_pom_score":0} for id in model.ProgramIDs} #default if not yet scored
    if model.score_id:
        #weighted pom and guidance scores for each proID
        wts_score = UsrOptionScore.calculate_weights(
            db_conn,
            UsrLookupCriticalWts,
            model.weight_id,
            model.score_id,
            model.criteria_name_id,
            storm_flag=model.storm_flag
        )

    storm_score = LookupStorm.get_total_score_from_progIds(LookupProgramModel,
                                                           db_conn,prog_ids=model.ProgramIDs,
                                                           to_dict=True)
    storm_score = {id:storm_score[id] if id in storm_score else 0 for id in model.ProgramIDs}

    # print(wts_score)

    #combine the weighted scores and storm scores if possible - default to zero if not exist
    # Result dictionary
    temp = {}
    default_keys = ["weighted_guidance_score", "weighted_pom_score", "total_storm_score"]
    for key in set(wts_score.keys()).union(storm_score.keys()):
        # Initialize with defaults
        temp[key] = {k: 0 for k in default_keys}
        
        # Update with values from `a` if they exist
        if key in wts_score:
            temp[key].update(wts_score[key])
        
        # Add `key3` from `b` if it exists
        if key in storm_score:
            temp[key]["total_storm_score"] = storm_score[key]

    # wts = {prog_id:{"pom":25,"guidance":28} for prog_id in model.ProgramIDs}
    wts_score = temp #inclusive of both weighted scores if scored with score id's and all the programs in the storm scores
    for prog_data in serializer.data:
        prog_data.weighted_guidance_score = wts_score.get(prog_data.program_id,{}).get("weighted_guidance_score",0)
        prog_data.weighted_pom_score = wts_score.get(prog_data.program_id,{}).get("weighted_pom_score",0)
        prog_data.total_storm_score = wts_score.get(prog_data.program_id,{}).get("total_storm_score",0)
    resp = optimizerV2(serializer,model,cut_matrix) 
    # print(resp)
    return resp #serializer.data

def transform_for_knapsack_v2(data):
    """
    Concatenates resource_k for the same program_id together
    Reformat into [ProgData(prog_id:id,resource_k:{year:val,year:val..}),{...}] format
    Args:
        data (list[ProgData]): [{program_id:id,resource_k:{year:val}},{...}] format
            Same progId has multiple records for different resource_k years
    Return:
        data (list[Dict]): [{program_id:id,resource_k:{year:val,year:val2...}},{...}] format
    """
    # print("\n================================================")
    # print("Transform for knapsack Data...")
    data = [row.dict() for row in data]
    # print(data)
    new_data = {} #{"prog id":{}}
    for row in data:
        prog_id = row['program_id']
        if prog_id in new_data:
            
            new_data[prog_id]['resource_k'] = row['resource_k'] | new_data[prog_id]['resource_k']
        else:
            new_data[prog_id] = {}
            new_data[prog_id]['program_id'] = row['program_id']
            new_data[prog_id]['program_group'] = row['program_group']
            new_data[prog_id]['program_code'] = row['program_code']
            new_data[prog_id]['pom_sponsor'] = row['pom_sponsor']
            new_data[prog_id]['capability_sponsor'] = row['capability_sponsor']
            new_data[prog_id]['assessment_area_code'] = row['assessment_area_code']
            new_data[prog_id]['execution_manager_code'] = row['execution_manager_code']
            new_data[prog_id]['resource_category_code'] = row['resource_category_code']
            new_data[prog_id]['eoc_code'] = row['eoc_code']
            new_data[prog_id]['osd_pe'] = row['osd_pe']

            new_data[prog_id]['weighted_guidance_score'] = round(float(row['weighted_guidance_score']),2)
            new_data[prog_id]['weighted_pom_score'] = round(float(row['weighted_pom_score']),2)
            new_data[prog_id]['resource_k'] = row['resource_k']
            try:
                new_data[prog_id]['total_storm_score'] = round(float(row['total_storm_score']),2)
            except (TypeError, ValueError) as e:
                print("[Warnning] cannot round the numbers :", e)
                new_data[prog_id]['total_storm_score'] = 0
    new_data = [new_data[prog_id] for prog_id in new_data]
    return new_data

def optimizerV2(serializer:ProgSerializerV2,model,cut_matrix):
    data = serializer.data
    data = transform_for_knapsack_v2(data)    
    ##parse per_resource optimization here
    results = {}
    budget = model.budget if model.budget else np.sum(cut_matrix, axis=0) #if pass by cut_by_percentage, we wont have budget List

    #already deduped, grouped by program ids
    selected_programs, results,cutting_lines,tranche_assignment = cut_optimizer(
        data,
        model.tranches,
        model.must_include,
        model.must_exclude,
        model.syr,
        model.eyr, model.option,
        cut_matrix,
        model.keep_cutting,
        model.storm_flag,
        model.per_resource_optimizer
    )
    
    __leftover = {i+model.syr:budget[i] for i in range(len(budget))} #initialized as total budget

    # budget_used = defaultdict(float)
    budget_used = {year: 0 for year in __leftover}
    # breakpoint()
    for program in cutting_lines:
        for year, amount in program["resource_k"].items():
            budget_used[int(year)] += amount
    #has to come from cutting lines
    budget_used = dict(sorted(budget_used.items()))
    leftover = {year: (__leftover[year] - budget_used[year]) if year in budget_used else __leftover[year] for year in __leftover.keys()} #calculating the leftover

    resp = {"resource_k":results,
            "selected_programs":selected_programs,
            "cutting_programs":cutting_lines,
            "tranche_assignment":tranche_assignment,
            "remaining":leftover}
    
    # breakpoint()
    if not model.per_resource_optimizer:
        return resp
    
    #calculate dollars per-unit for display if selected per-unit score
    prog_budget_full_map = {d['program_id']:d['resource_k'] for d in data} #full budget before cutting or keeping
    for d in selected_programs:
        prog_id = d["program_id"]
        total_resources = sum(prog_budget_full_map[prog_id].values())

        if model.storm_flag:
            #to avoid dividing by zero, we assign all the values to nulls if score is 0
            d["storm_per_resource"] = round(d["total_storm_score"]/total_resources,5) if total_resources > 0 else None
        elif model.option == 1: #both
            d["guidance_and_pom_per_resource"] = round((d["weighted_guidance_score"]*d["weighted_pom_score"])/total_resources,5) if total_resources > 0 else None
        elif model.option == 2: #guidance
            d["guidance_per_resource"] = round(d["weighted_guidance_score"]/total_resources,5) if total_resources > 0 else None
        elif model.option == 3: #pom
            d["pom_per_resource"] = round(d["weighted_pom_score"]/total_resources,5) if total_resources > 0 else None
    
    #store the same result in cutting lines
    for d in cutting_lines:
        prog_id = d["program_id"]
        total_resources = sum(prog_budget_full_map[prog_id].values())

        if model.storm_flag:
            #to avoid dividing by zero, we assign all the values to nulls if score is 0
            d["storm_per_resource"] = round(d["total_storm_score"]/total_resources,5) if total_resources > 0 else None
        elif model.option == 1: #both
            d["guidance_and_pom_per_resource"] = round((d["weighted_guidance_score"]*d["weighted_pom_score"])/total_resources,5) if total_resources > 0 else None
        elif model.option == 2: #guidance
            d["guidance_per_resource"] = round(d["weighted_guidance_score"]/total_resources,5) if total_resources > 0 else None
        elif model.option == 3: #pom
            d["pom_per_resource"] = round(d["weighted_pom_score"]/total_resources,5) if total_resources > 0 else None
    return resp

####################
#post processing endpoint

def post_optimization_filter(model:OptimizerOutputModel,filter:OptimizerFilterParams):
    # breakpoint()
    new_model = model.dict()
    
    if filter.filter_zero_resource_k:
 
        new_model['filter'] = dict()
        new_model['filter']['filter_zero_resource_k'] = dict()
        new_model['filter']['filter_zero_resource_k']['removed_programs_id'] = set()
        remove_set = set()
        #remove only programs in which all of the spanning years are zero 
        #(not cases where we have non-zeros in some years)
        for program in model.selected_programs:
            if sum(program.resource_k.values()) == 0:
                remove_set.add(program.program_id)
        
        new_model['selected_programs'] = [
            program for program in model.selected_programs 
            if program.program_id not in remove_set
        ]
        for year in model.resource_k:
            new_model['resource_k'][year] = {
                program: value for program, value in model.resource_k[year].items()
                if program not in remove_set
            }

        for program_id in remove_set:
            new_model['filter']['filter_zero_resource_k']['removed_programs_id'].add(program_id)
    return new_model


def get_weighted_coa_scores(model:COAWeightedScoresInputModel,v2:bool,db_conn):
    #item_id is the PK column of the UsrLookupSavedCOA table
    score_id = [{"PROGRAM_ID":program,"USER_ID":model.user_id} for program in model.program_ids]
    resp = {}
    storm_scores = {}
    wted_scores = {}
    if not v2:
        storm_scores = LookupStorm.get_total_score_from_progIds(LookupProgramModel,db_conn,model.program_ids,to_dict=True)
    else:
        storm_scores = LookupStorm.get_total_score_from_progIds(LookupProgramModel,db_conn,model.program_ids,to_dict=True)
    storm_scores = {prog_id:{"total_storm_scores":score} for prog_id,score in storm_scores.items()}
    
    for program in model.program_ids:
        if program not in storm_scores:
            storm_scores[program] = {"total_storm_scores": 0}
    try:
        wted_scores = UsrOptionScore.calculate_weights(db_conn,UsrLookupCriticalWts,model.weight_id,score_id=score_id,criteria_name_id=model.criteria_name_id,storm_flag=False)
    except HTTPException as e:
        # Handle specific HTTP error
        if e.status_code == 400:
            wted_scores = {} # default
        else: #other errors
            raise
    
    #fill in the missing wted_scores in the dict, default as zeros
    for prog_id in model.program_ids:
        wted_scores[prog_id] = wted_scores.get(prog_id,{"weighted_pom_score":0,"weighted_guidance_score":0})

    resp = {
        key: {**storm_scores.get(key, {"total_storm_scores": 0}), **wted_scores.get(key, {})}
        for key in set(storm_scores) | set(wted_scores)
    }
    
    return resp


