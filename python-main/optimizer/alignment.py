import json
from typing import List

from optimizer.socom_maximizer import (
    ProgSerializer,
    transform_for_knapsack,
)

from api.internal.utils import (
    transform_pid_to_unhash,
    generate_hash_pid
)
from socom.eoc_funding import get_prog_eoc_funding

from rds.table_model.socom.UsrLookupSavedCOA import UsrLookupSavedCOA

from rds.table_model.socom.LookupProgramDetail import (
    LookupProgramModel
)

from fastapi import HTTPException
import copy

class AlignmentOuptut:
    def __init__(self,depth:List[int]):
        #max of 4 depth
        self.depth = depth #[1,2,3,4] for example
        # {"selected_programs":{"first_tier":{},"second_tier":{},"third_tier":{}},
        #                 "unselected_programs":{"first_tier":{},"second_tier":{},"third_tier":{}}
        #}
        self.value = {"selected_programs":{},"unselected_programs":{}}
        self._process()

    def _process(self):
        mapper = {1:"first_tier",2:"second_tier",3:"third_tier",4:"fourth_tier"}
        while self.depth:
            depth = self.depth.pop()
            
            if depth < 1 or depth  > 4:
                raise ValueError("tier depth must be between 1 and 4")
            
            self.value['selected_programs'][mapper[depth]] = {}
            self.value['unselected_programs'][mapper[depth]] = {}
        
    def __repr__(self):
        return f"{self.value}"

        

#Alignment calculations
def process_alignment_tiers(align_output,data,alignments,selection,tier_key):
    """
    Function to calculate alignment for JCA/CGA/KSP/KOP
    align_output(AlignmentOutput): dictionary of absolute resources values by tier key {selected:{first_tier:{"1.1.0":500, "1.2.0":200,...}}}
    data (dict): dictionary of {"program id:":total_resources_across_all_years}
    alignments(dict): {"program id1:":[1.1.0,2.1.0,...]}
    selection (str): Union["selected","unselected"]
    tier_key (str): Union["third_tier","second_tier","first_tier]
    """

    #processing level3
    for prog_id,current_resources in data.items(): #{prog:resources} format
        if not current_resources or not alignments[prog_id]: #either 0 resources or {'program_id':None} alignment
            continue
        
        for alignment in alignments[prog_id]:
            if tier_key == "fourth_tier":
                alignment = alignment.split(".")+["0","0","0"]
                alignment = ".".join(alignment[:4])
            elif tier_key == "third_tier":
                alignment = alignment.split(".")+ ["0","0"] #padding by zeros just in case of 1.0 or 1, not 1.0.0
                alignment = ".".join(alignment[:3])
            elif tier_key == "second_tier":
                alignment = alignment.split(".")+ ["0"]
                alignment = ".".join(alignment[:2])
            elif tier_key == "first_tier":
                alignment = alignment.split(".")
                alignment = ".".join(alignment[:1]) #[0] wont work because '35' -> '3.5'. treated as as list

            if alignment not in align_output.value[selection][tier_key]:
                align_output.value[selection][tier_key][alignment] = {}
            
            align_output.value[selection][tier_key][alignment][prog_id] = (
                align_output.value[selection][tier_key][alignment].get(prog_id, 0) +
                current_resources / len(alignments[prog_id])
            )
            align_output.value[selection][tier_key][alignment][prog_id] = (
                round(align_output.value[selection][tier_key][alignment][prog_id], 2)
            )

    return align_output

            
def calculate_opt_alignment(id:str,alignment_type,db_conn):
    """
    Currently using IO type only
    """
    sel_data,unsel_data, alignment = UsrLookupSavedCOA.get_alignment_from_coa_id(id,alignment_type,LookupProgramModel,db_conn)
    sel_data = {prog: sum(float(value) for value in d.values()) for prog,d in sel_data.items()}

    unsel_serializer = ProgSerializer()
    unsel_serializer.serialize(unsel_data,use_iss_extract=True)
    unsel_serializer = transform_for_knapsack(unsel_serializer.data)

    #extract out all the resource_k for the exluded programs
    #reformat both the selected and unselected into {'prog id ':total resources...}
    unsel_data = {prog['program_id']:sum(prog['resource_k'].values()) for prog in unsel_serializer}

    #grab the unhashed pids to show on the UI
    unsel_unhashed_pids = transform_pid_to_unhash(unsel_data.keys(),db_conn)
    sel_unhashed_pids = transform_pid_to_unhash(sel_data.keys(),db_conn)
    unhashed_alignment = transform_pid_to_unhash(alignment.keys(),db_conn)
    #remap unhashed id values for UI
    unsel_data = {unsel_unhashed_pids[k]:unsel_data[k] for k in unsel_data}
    sel_data = {sel_unhashed_pids[k]:sel_data[k] for k in sel_data}
    alignment = {unhashed_alignment[k]:alignment[k] for k in alignment}



    if not alignment:
        raise HTTPException(status_code=404, 
            detail="no selected programs found with optimizer ID, or no alignment found for those selected programs")
    if alignment_type == "JCA":
        align_output = AlignmentOuptut(depth=[1,2,3])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="first_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "CGA":
        align_output = AlignmentOuptut(depth=[1,2])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="first_tier")
            
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "KOP_KSP":
        align_output = AlignmentOuptut(depth=[3,4])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="third_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="third_tier")
    return align_output


def calculate_opt_alignment_v2(id:str,alignment_type,db_conn):
    """
    Currently for RC type only
    """
    sel_data,unsel_data, alignment = UsrLookupSavedCOA.get_alignment_from_coa_id_v2(id,alignment_type,LookupProgramModel,db_conn)

    sel_data = {prog: sum(float(value) for value in d.values()) for prog,d in sel_data.items()}
    unsel_data = {prog: sum(float(value) for value in d.values()) for prog,d in unsel_data.items()}
    
    #grab the unhashed pids to show on the UI
    unsel_unhashed_pids = transform_pid_to_unhash(unsel_data.keys(),db_conn)
    sel_unhashed_pids = transform_pid_to_unhash(sel_data.keys(),db_conn)
    unhashed_alignment = transform_pid_to_unhash(alignment.keys(),db_conn)
    #remap unhashed id values for UI
    unsel_data = {unsel_unhashed_pids[k]:unsel_data[k] for k in unsel_data}
    sel_data = {sel_unhashed_pids[k]:sel_data[k] for k in sel_data}
    alignment = {unhashed_alignment[k]:alignment[k] for k in alignment}

    if not alignment:
        raise HTTPException(status_code=404, 
            detail="no selected programs found with optimizer ID, or no alignment found for those selected programs")

    if alignment_type == "JCA":
        align_output = AlignmentOuptut(depth=[1,2,3])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="first_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "CGA":
        align_output = AlignmentOuptut(depth=[1,2])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="first_tier")
            
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "KOP_KSP":
        align_output = AlignmentOuptut(depth=[3,4])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="third_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="third_tier")
    return align_output




async def calculate_manual_override_alignment_v2(id:int,alignment_type,db_conn):
    data = await calc_detail_summary_eoc_funding(coa_id=id,db_conn=db_conn)
    sel_data = data['includes']
    unsel_data = data['excludes']
    sel_data = {d['ID']: sum(d['RESOURCE_K'].values()) for d in sel_data}
    unsel_data = {d['ID']: sum(d['RESOURCE_K'].values()) for d in unsel_data}
    
    alignment = set(sel_data.keys()) | set(unsel_data.keys()) #union 2 spaces
    alignment = UsrLookupSavedCOA.get_alignment_space(LookupProgramModel,alignment_type,alignment,db_conn)

    #grab the unhashed pids to show on the UI
    unsel_unhashed_pids = transform_pid_to_unhash(unsel_data.keys(),db_conn)
    sel_unhashed_pids = transform_pid_to_unhash(sel_data.keys(),db_conn)
    unhashed_alignment = transform_pid_to_unhash(alignment.keys(),db_conn)
    #remap unhashed id values for UI
    unsel_data = {unsel_unhashed_pids[k]:unsel_data[k] for k in unsel_data}
    sel_data = {sel_unhashed_pids[k]:sel_data[k] for k in sel_data}
    alignment = {unhashed_alignment[k]:alignment[k] for k in alignment}
    
    if not alignment:
        raise HTTPException(status_code=404, 
            detail="no selected programs found with optimizer ID, or no alignment found for those selected programs")

    if alignment_type == "JCA":
        align_output = AlignmentOuptut(depth=[1,2,3])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="first_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "CGA":
        align_output = AlignmentOuptut(depth=[1,2])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="first_tier")
            
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "KOP_KSP":
        align_output = AlignmentOuptut(depth=[3,4])
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,sel_data,alignment,selection="selected_programs",tier_key="third_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,unsel_data,alignment,selection="unselected_programs",tier_key="third_tier")
    return align_output
    

def extract_years_from_override(id: str, db_conn):
    override_data = db_conn.query(UsrLookupSavedCOA.OVERRIDE_TABLE_SESSION).filter(UsrLookupSavedCOA.ID == id).first()
    
    if not override_data or not override_data[0]:
        return []

    override_json = override_data[0] if isinstance(override_data[0], dict) else json.loads(override_data[0])

    years = set()

    if "coa_output" in override_json:
        for item in override_json["coa_output"]:
            years.update(item.keys())
    
    if "budget_uncommitted" in override_json:
        for item in override_json["budget_uncommitted"]:
            years.update(item.keys())

    year_list = sorted([year for year in years if year.isdigit()])
    
    return year_list


async def calculate_manual_override_alignment(id:int,alignment_type,db_conn):

    sel_data, unsel_data, alignment = UsrLookupSavedCOA.get_manual_override(id,alignment_type,LookupProgramModel, db_conn)
    years = extract_years_from_override(id, db_conn)
    
    #sum up {pid:total_amt}
    unsel_data_dict = {}
    for prog in unsel_data:
        prog_id = prog[0]  

        if prog_id not in unsel_data_dict:
            unsel_data_dict[prog_id] = 0

        year_value = float(prog[7])
        unsel_data_dict[prog_id] += year_value
    
    #sum up {pid:total_amt}
    formatted_sel_data = {}
    for prog, data in sel_data.items():
        
        if not prog: #DT_RowId is null
            continue
        total_resource = 0
        for year in years:
            total_resource += float(data.get(year, 0))  
        formatted_sel_data[prog] = total_resource

    #grab the unhashed pids to show on the UI
    unsel_unhashed_pids = transform_pid_to_unhash(unsel_data_dict.keys(),db_conn)
    sel_unhashed_pids = transform_pid_to_unhash(formatted_sel_data.keys(),db_conn)
    unhashed_alignment = transform_pid_to_unhash(alignment.keys(),db_conn)
    #remap unhashed id values for UI
    unsel_data_dict = {unsel_unhashed_pids[k]:unsel_data_dict[k] for k in unsel_data_dict}
    formatted_sel_data = {sel_unhashed_pids[k]:formatted_sel_data[k] for k in formatted_sel_data}
    alignment = {unhashed_alignment[k]:alignment[k] for k in alignment}

    if alignment_type == "JCA":
        align_output = AlignmentOuptut(depth=[1,2,3])
        jca_alignment = {k: d["JCA"] for k, d in alignment.items()}
        align_output = process_alignment_tiers(align_output,formatted_sel_data,jca_alignment,selection="selected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,formatted_sel_data,jca_alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,formatted_sel_data,jca_alignment,selection="selected_programs",tier_key="first_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data_dict,jca_alignment,selection="unselected_programs",tier_key="third_tier")
        align_output = process_alignment_tiers(align_output,unsel_data_dict,jca_alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data_dict,jca_alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "CGA":
        align_output = AlignmentOuptut(depth=[1,2])
        cga_alignment = {k: d["CGA"] for k, d in alignment.items()}
        align_output = process_alignment_tiers(align_output,formatted_sel_data,cga_alignment,selection="selected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,formatted_sel_data,cga_alignment,selection="selected_programs",tier_key="first_tier")
            
        align_output = process_alignment_tiers(align_output,unsel_data_dict,cga_alignment,selection="unselected_programs",tier_key="second_tier")
        align_output = process_alignment_tiers(align_output,unsel_data_dict,cga_alignment,selection="unselected_programs",tier_key="first_tier")

    elif alignment_type == "KOP_KSP":
        align_output = AlignmentOuptut(depth=[3,4])
        kp_alignment = {k: d["KOP_KSP"] for k, d in alignment.items()}
        align_output = process_alignment_tiers(align_output,formatted_sel_data,kp_alignment,selection="selected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,formatted_sel_data,kp_alignment,selection="selected_programs",tier_key="third_tier")
        
        align_output = process_alignment_tiers(align_output,unsel_data_dict,kp_alignment,selection="unselected_programs",tier_key="fourth_tier")
        align_output = process_alignment_tiers(align_output,unsel_data_dict,kp_alignment,selection="unselected_programs",tier_key="third_tier")

    return align_output



# Function to calculate funding differences
def calculate_funding_diff(db_funding_map, includes, key):
    # Get the RESOURCE_K dictionaries from both maps
    resource_k_db = db_funding_map[key]['RESOURCE_K']
    resource_k_include = includes[key]['RESOURCE_K']

    # Compute difference for each year
    resource_k_diff = {
        #clamp down to 0 if negative (manual override has editted over the db funding resource k)
        str(year): max(resource_k_db[year] - resource_k_include.get(str(year), 0),0) 
        for year in resource_k_db
    }
    # Check if the sum of all values in resource_k_diff is zero
    if sum(resource_k_diff.values()) == 0:
        return None
    
    return resource_k_diff

async def calc_detail_summary_eoc_funding(coa_id,db_conn):
    from socom.metadata import get_lkup_program_metadata

    coa_run = UsrLookupSavedCOA.get_opt_run_data_from_coa_id([coa_id],db_conn)
    coa_run = coa_run[coa_id]
    opt_run_data = coa_run['opt-run']
    manual_session = coa_run['manual_session']

    if manual_session:
        all_programs = set(manual_session['ProgramIDs'])
    else:
        all_programs = set(coa_run['all_programs'])
    
    db_funding = get_prog_eoc_funding(db_conn,program_ids = all_programs)
    db_funding_map = {prog['ID']: {k: v for k, v in prog.items()} for prog in db_funding} #CDC {ID:{rest of the keys}}
    # print(all_programs)
    
    #check on manual override (if exist)
    # print(len(all_programs))
    includes = {}
    if manual_session: #manual override
        incl_data = manual_session['coa_output']
        # print(len(data))
        for row in incl_data:
            if not row["Program"]: #summary portion
                continue

            pid = f"{row['Program']}_{row['POM SPONSOR']}_{row['CAP SPONSOR']}"+ \
                f"_{row['ASSESSMENT AREA']}_{row['EXECUTION MANAGER']}_{row['RESOURCE CATEGORY']}_{row['EOC']}_{row['OSD PE']}"
            pid = generate_hash_pid(pid)
            # print(pid)
            years = [year for year in row.keys() if year.startswith('20') and len(year) == 4]
            resource_k = {year:row[year] for year in years}
            includes.update({pid:{"RESOURCE_K":resource_k}})
    else: #coa output
        incl_data = opt_run_data #{year:{id:resource_k}}
        for year in incl_data:
            for id,resource_k in incl_data[year].items():
                if id not in includes:
                    includes[id] = {'RESOURCE_K':{}}
                includes[id]['RESOURCE_K'][str(year)] = resource_k

    
    # print(len(includes))
    # print([k for k in includes if k not in db_funding_map])
    for program_id in includes:
        for key, value in db_funding_map[program_id].items():
            if key != "RESOURCE_K":
                includes[program_id][key] = copy.deepcopy(value)

    excludes = {pid:{'RESOURCE_K':copy.deepcopy(db_funding_map[pid]['RESOURCE_K'])} for pid in db_funding_map if pid not in includes}
    
    excl_metadata = await get_lkup_program_metadata(
        db_conn,
        "iss",
        rk_non_zero=False,
        kwargs={"PROGRAM_ID":list(excludes.keys())}
    )
    #parse metadata into the excludes section
    for row in excl_metadata:
        id = row["ID"]
        excludes[id]["ID"] = id
        excludes[id]["PROGRAM_CODE"] = row["PROGRAM_CODE"]
        excludes[id]['PROGRAM_GROUP'] = row['PROGRAM_GROUP']
        excludes[id]["ASSESSMENT_AREA_CODE"] = row["ASSESSMENT_AREA_CODE"]
        excludes[id]["EOC_CODE"] = row["EOC_CODE"]
        excludes[id]["POM_SPONSOR_CODE"] = row["POM_SPONSOR_CODE"]
        excludes[id]["RESOURCE_CATEGORY_CODE"] = row["RESOURCE_CATEGORY_CODE"]
        excludes[id]["CAPABILITY_SPONSOR_CODE"] = row["CAPABILITY_SPONSOR_CODE"]
        # excludes[id]["PROGRAM_NAME"] = row["PROGRAM_NAME"]
        excludes[id]["OSD_PROGRAM_ELEMENT_CODE"] = row["OSD_PROGRAM_ELEMENT_CODE"]
        # excludes[id]["EVENT_NAME"] = row["EVENT_NAME"]
        excludes[id]["EXECUTION_MANAGER_CODE"] = row["EXECUTION_MANAGER_CODE"]
    
    #now calculate the diff in resource k between includes and the db for exclude
    for id in includes:
        diff = calculate_funding_diff(db_funding_map,includes,id)
        if diff:
            excludes[id] = copy.deepcopy(includes[id])
            excludes[id]['RESOURCE_K'] = diff
    excludes = {pid:d for pid,d in excludes.items() if sum(d['RESOURCE_K'].values()) > 0}
    data = {'excludes':list(excludes.values()), 'includes':list(includes.values())}
    return data



