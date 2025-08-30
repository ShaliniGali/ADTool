import json
from typing import List
from copy import deepcopy
from collections import defaultdict

from api.internal.resources import (
    create_dynamic_table_class, 
    IssSummaryTableSet
)

from decimal import Decimal

from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel
from rds.table_model.socom.UsrLookupSavedCOA import UsrLookupSavedCOA
from rds.table_model.socom.LookupProgramDetail import LookupProgramModel
from rds.table_model.socom.UsrLookupUserSavedCOA import UsrLookupUserSavedCOA

from fastapi import HTTPException


def process_funding_nest_years(funding_lines):
    # each line has a separate (year,funding), combine it into fiscal_year:{year:funding..}
# Group data
    grouped_data = defaultdict(lambda: defaultdict(Decimal))
    # breakpoint()
    for entry in funding_lines:
        # Create a unique key excluding 'FISCAL_YEAR' and 'DELTA_AMT'
        key = tuple((k, v) for k, v in entry.items() if k not in ('FISCAL_YEAR', 'DELTA_AMT'))
        
        # Sum DELTA_AMT for each FISCAL_YEAR
        grouped_data[key][str(entry['FISCAL_YEAR'])] += entry['DELTA_AMT']

    # Convert back to list of dictionaries with FYDP total
    result = []
    for key, fiscal_data in grouped_data.items():
        fiscal_dict = dict(fiscal_data)
        
        # Compute FYDP total
        fiscal_dict['FYDP'] = sum(fiscal_data.values())
        
        result.append({**dict(key), 'FISCAL_YEAR': fiscal_dict})
    return result

def process_events_opt(fine_dt_event_funding,opt_run,agg_dt_event_funding):
    """
    Take in a list of fine dt events funding, and return ONLY the relevant lines
    based off the opt_run.
    opt_run input MUST not be None
    {'2027': {'5GRAB_AT&L_NSW_A': 180, 'OTHERII_NSW_NSW_C': 484, ...}} format
    where chosen programs are listed based on year
    """
    agg_dt_event_funding_new = deepcopy(agg_dt_event_funding)
    chosen_funding_line = []
    excluded_funding_line = []
    reference_filter = set() #tells us which (prog id, year) is involved the selection
    for year in opt_run:
        for prog_id in opt_run[year]:
            reference_filter.add((prog_id,int(year)))

    for funding in fine_dt_event_funding:
        prog_id = funding["PROGRAM_CODE"]+"_"+funding["POM_SPONSOR_CODE"]+ \
            "_"+funding["CAPABILITY_SPONSOR_CODE"]+"_"+funding["ASSESSMENT_AREA_CODE"]
        event = funding["EVENT_NAME"]
        key = (prog_id,int(funding["FISCAL_YEAR"]))
        row_id = funding["EVENT_NAME"]+"_"+funding['PROGRAM_CODE'] +  "_" + funding["CAPABILITY_SPONSOR_CODE"] + "_" +\
            funding["ASSESSMENT_AREA_CODE"]+"_"+funding['RESOURCE_CATEGORY_CODE']+"_"+funding["EOC_CODE"]+"_"+funding['OSD_PROGRAM_ELEMENT_CODE']
        funding["ROW_ID"] = row_id
        
        if key in reference_filter and event in agg_dt_event_funding_new:
            #assume that non-existing events are with total funding of negative
            chosen_funding_line.append(funding)
            agg_dt_event_funding_new[event] -= funding["DELTA_AMT"]
        
        #event in the namespace, but the program id was not chosen
        elif event in agg_dt_event_funding_new:
            excluded_funding_line.append(funding)
    
    chosen_dict = {}
    # breakpoint
    chosen_dict["funding_status"] = {}
    chosen_dict["include"] = process_funding_nest_years(chosen_funding_line)

    chosen_dict["exclude"] = process_funding_nest_years(excluded_funding_line)
    # breakpoint()
    for event,funding in agg_dt_event_funding_new.items():
        if funding <= 0:
            chosen_dict["funding_status"][event] = "Fully Funded"
        elif funding == agg_dt_event_funding[event]:
            chosen_dict["funding_status"][event] = "Not Funded"
        elif 0 < funding < agg_dt_event_funding[event]:
            chosen_dict["funding_status"][event] = "Partially Funded"
        else:
            chosen_dict["funding_status"][event] = "Unknown"
    # for funding in fine_dt_event_funding:
    ###need to update dict with the funding status
    return chosen_dict

def process_events_manual(fine_dt_event_funding,manual_run,agg_dt_event_funding):
    """
    Take in a list of fine dt events funding, and return ONLY the relevant lines
    based off the opt_run.
    manual_run input MUST not be None
    {'2027': {'5GRAB_AT&L_NSW_A': 180, 'OTHERII_NSW_NSW_C': 484, ...}} format
    where chosen programs are listed based on year
    """
    # breakpoint()
    manual_run = manual_run['coa_output']
    agg_dt_event_funding_new = deepcopy(agg_dt_event_funding)
    chosen_funding_line = []
    excluded_funding_line = []
    reference_filter = {} #tells us which (prog id, cat,ect..year):delta amt is involved the selection
    for funding in manual_run:
        years_lst = [k for k in funding if k.startswith("20")]
        for year in years_lst:
            prog_id = funding["Program"]+"_"+funding["POM SPONSOR"]+ \
                "_"+funding["CAP SPONSOR"]+"_"+funding["ASSESSMENT AREA"]
            eoc_code = funding['EOC']
            resource_cat_code = funding['RESOURCE CATEGORY']
            
            osd_pe = funding['OSD PE']

            event = funding['Event Name']
            key = (prog_id,eoc_code,resource_cat_code,event,osd_pe,int(year))
            #some funding rows will be nulls -> 0
            reference_filter[key] = funding[str(year)] if funding[str(year)] else 0

    for funding in fine_dt_event_funding:
        prog_id = funding["PROGRAM_CODE"]+"_"+funding["POM_SPONSOR_CODE"]+ \
            "_"+funding["CAPABILITY_SPONSOR_CODE"]+"_"+funding["ASSESSMENT_AREA_CODE"]
        event = funding["EVENT_NAME"]
        eoc_code = funding["EOC_CODE"]
        resource_cat_code = funding["RESOURCE_CATEGORY_CODE"]
        osd_pd = funding["OSD_PROGRAM_ELEMENT_CODE"]
        year = funding['FISCAL_YEAR']

        row_id = funding["EVENT_NAME"]+"_"+funding['PROGRAM_CODE'] +  "_" + funding["CAPABILITY_SPONSOR_CODE"] + "_" +\
            funding["ASSESSMENT_AREA_CODE"]+"_"+funding['RESOURCE_CATEGORY_CODE']+"_"+funding["EOC_CODE"]+"_"+funding['OSD_PROGRAM_ELEMENT_CODE']
        funding["ROW_ID"] = row_id
        # breakpoint()
        
        key = (prog_id,eoc_code,resource_cat_code,event,osd_pd,int(year))

        if key in reference_filter and event in agg_dt_event_funding_new:
            # print(key)
            # breakpoint()
            #assume that non-existing events are with total funding of negative
            temp = funding #keeping the format the same, and replace dt value with manual override rows
            temp['DELTA_AMT'] = reference_filter[key]
            chosen_funding_line.append(temp)
            agg_dt_event_funding_new[event] -= temp['DELTA_AMT']
        
        #event in the namespace, but the program id was not chosen
        elif event in agg_dt_event_funding_new:
            excluded_funding_line.append(funding)
    
    # breakpoint()
    chosen_dict = {}
    chosen_dict["funding_status"] = {}
    chosen_dict["include"] = process_funding_nest_years(chosen_funding_line)

    chosen_dict["exclude"] = process_funding_nest_years(excluded_funding_line)
    # breakpoint()
    for event,funding in agg_dt_event_funding_new.items():
        if funding <= 0:
            chosen_dict["funding_status"][event] = "Fully Funded"
        elif funding == agg_dt_event_funding[event]:
            chosen_dict["funding_status"][event] = "Not Funded"
        elif 0 < funding < agg_dt_event_funding[event]:
            chosen_dict["funding_status"][event] = "Partially Funded"
        else:
            chosen_dict["funding_status"][event] = "Unknown"

    ###need to update dict with the funding status
    return chosen_dict


def transform_funding_data(data):
    ##transform data into a format for UI to use
    
    result = {
        "event": {
            "fully_funded_issues": [],
            "partially_funded_issues": [],
            "non_funded_issues": []
        },
        # "program/eoc": []
    }
    
    funding_status = data.get("funding_status", {})
    include_data = data.get("include", [])
    exclude_data = data.get("exclude", [])
    
    # Categorize events based on their funding status
    funding_categories = {
        "Fully Funded": "fully_funded_issues",
        "Partially Funded": "partially_funded_issues",
        "Not Funded": "non_funded_issues"
    }
    
    event_map = defaultdict(lambda: {"include": [], "exclude": []})
    
    for item in include_data:
        event_title = item["EVENT_TITLE"]
        event_name = item["EVENT_NAME"]
        funding_type = funding_status.get(event_name, "Unknown")
        if funding_type in funding_categories:
            category = funding_categories[funding_type]
            event_map[(event_name,event_title)]["include"].append({
                "PROGRAM_CODE": item["PROGRAM_CODE"],
                "EOC_CODE": item["EOC_CODE"],
                "CAPABILITY_SPONSOR_CODE": item["CAPABILITY_SPONSOR_CODE"],
                "ASSESSMENT_AREA_CODE": item["ASSESSMENT_AREA_CODE"],
                "RESOURCE_CATEGORY_CODE": item["RESOURCE_CATEGORY_CODE"],
                "OSD_PROGRAM_ELEMENT_CODE": item["OSD_PROGRAM_ELEMENT_CODE"],
                "FISCAL_YEAR": item["FISCAL_YEAR"],
                "ROW_ID": item["ROW_ID"]
            })
    
    for item in exclude_data:
        event_name = item["EVENT_NAME"]
        event_title = item["EVENT_TITLE"]
        funding_type = funding_status.get(event_name, "Unknown")
        if funding_type in funding_categories:
            category = funding_categories[funding_type]
            event_map[(event_name,event_title)]["exclude"].append({
                "PROGRAM_CODE": item["PROGRAM_CODE"],
                "EOC_CODE": item["EOC_CODE"],
                "CAPABILITY_SPONSOR_CODE": item["CAPABILITY_SPONSOR_CODE"],
                "ASSESSMENT_AREA_CODE": item["ASSESSMENT_AREA_CODE"],
                "RESOURCE_CATEGORY_CODE": item["RESOURCE_CATEGORY_CODE"],
                "OSD_PROGRAM_ELEMENT_CODE": item["OSD_PROGRAM_ELEMENT_CODE"],
                "FISCAL_YEAR": item["FISCAL_YEAR"],
                "ROW_ID": item["ROW_ID"]
            })
    
    for event, data in event_map.items():
        event_name,event_title = event
        funding_type = funding_status.get(event_name, "Unknown")
        if funding_type in funding_categories:
            category = funding_categories[funding_type]
            result["event"][category].append({
                "EVENT_NAME": event_name,
                "EVENT_TITLE": event_title,
                "include": data["include"],
                "exclude": data["exclude"]
            })
    return result

async def process_event_detailed_summary(coa_ids:List[int], db_conn):
    data = UsrLookupSavedCOA.get_opt_run_data_from_coa_id(coa_ids,db_conn)
    all_prog_ids = {coa:data[coa]["all_programs"] for coa in data}
    # breakpoint()
    table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
    orm_model = create_dynamic_table_class(table_name,DtISSExtractModel)

    #for all coas, all programs
    all_distinct_progs = set(prog for progs in all_prog_ids.values() for prog in progs)
    #{event:{set of prog ids involved} for all program ids (sel+unsel)
    prog_event_map = orm_model.get_event_names_from_program_ids(LookupProgramModel,all_distinct_progs,db_conn)

    #fine-grain/line by line
    fine_dt_event_funding = await orm_model.get_event_summary_list(prog_event_map.keys(),db_conn)
    
    if not fine_dt_event_funding:
        raise HTTPException(404,
                            f"No event funding found associated with these program ids: {all_prog_ids}")
    
    #{event:total-funding}    
    agg_dt_event_funding = {}
    for funding in fine_dt_event_funding:
        event_name = funding["EVENT_NAME"]
        agg_dt_event_funding[event_name] = agg_dt_event_funding.get(event_name,0) + funding["DELTA_AMT"] 
    
    #filter out events from the dt total funding with negative total funding
    agg_dt_event_funding = {k:v for k,v in agg_dt_event_funding.items() if v > 0}
    prog_event_map = {k: v for k, v in prog_event_map.items() if agg_dt_event_funding.get(k, 0) > 0}
    
    ######process opt run and manual override here
    #list of chosen and unchosen fundings
    coa_titles = UsrLookupUserSavedCOA.get_coa_names_from_saved_coa_ids(coa_ids,db_conn)
    
    result = {}
    for coa in data:
        # breakpoint()
        if data[coa]['manual_session']:
            funding = process_events_manual(fine_dt_event_funding,data[coa]['manual_session'],agg_dt_event_funding)
            result[coa] = transform_funding_data(funding)
        #manual override doesnt exist
        else:
            funding = process_events_opt(fine_dt_event_funding,data[coa]['opt-run'],agg_dt_event_funding)
            result[coa] = transform_funding_data(funding)
        
        #add in the title
        result[coa]["coa_title"] = coa_titles[coa]
    result["all_events"] = list(agg_dt_event_funding.keys())
    return result