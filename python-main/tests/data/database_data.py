import pandas as pd
import json
import os

def create_dataset_lookup_program():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/lookup_program.json') 
    file_path = os.path.abspath(file_path)
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)


def create_dataset_lookup_program_detail():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/lookup_program_detail.json') 
    file_path = os.path.abspath(file_path)

    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_iss_2026():
 
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/dt_iss_2026.json') 
    file_path = os.path.abspath(file_path)  

    with open (file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)    

def create_dataset_usr_option_scores():
  
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/usr_option_scores.json') 
    file_path = os.path.abspath(file_path) 
    
    with open (file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)  

def create_dataset_lookup_storm():
 
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/lookup_storm.json') 
    file_path = os.path.abspath(file_path)
    
    with open (file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_usr_criteria_wts():
	data = 	[
	{
		"WEIGHT_ID" : 103,
		"TITLE" : "Testing more",
		"DESCRIPTION" : "{\"pom\": \"\", \"guidance\": \"\"}",
		"SESSION" : "{\"pom\": {\"RISK\": 0.05, \"READINESS\": 0.1, \"FOUNDATIONAL\": 0.1, \"DESIGN_ALIGNMENT\": 0.13, \"COST_PRACTICALITY\": 0.05, \"STRATEGIC_ALIGNMENT\": 0.1, \"MANPOWER_FEASIBILITY\": 0.1, \"POLITICAL_FEASIBILITY\": 0.19, \"ACQUISITION_FEASIBILITY\": 0.1, \"COST_PROFILE_FEASIBILITY\": 0, \"SECURITY_COOPERATION_FEASIBILITY\": 0.08}, \"guidance\": {\"RISK\": 0.1, \"READINESS\": 0.1, \"FOUNDATIONAL\": 0.06, \"DESIGN_ALIGNMENT\": 0.09, \"COST_PRACTICALITY\": 0.05, \"STRATEGIC_ALIGNMENT\": 0.17, \"MANPOWER_FEASIBILITY\": 0.14, \"POLITICAL_FEASIBILITY\": 0.11, \"ACQUISITION_FEASIBILITY\": 0.03, \"COST_PROFILE_FEASIBILITY\": 0.05, \"SECURITY_COOPERATION_FEASIBILITY\": 0.1}}",
		"USER_ID" : 2,
		"DELETED" : 0,
		"TIMESTAMP" : "2024-06-04 10:54:38"
	}
	]
	return pd.DataFrame(data)

def create_dataset_dt_ext_2026():
  
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/DT_EXT_2026.json') 
    file_path = os.path.abspath(file_path)
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_ext_2027():
  
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/DT_EXT_2027.json') 
    file_path = os.path.abspath(file_path)
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_iss_extract_2026():
 
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/DT_ISS_EXTRACT_2026.json') 
    file_path = os.path.abspath(file_path)
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_zbt_2026():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/DT_ZBT_2026.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_lookup_jca():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/LOOKUP_JCA.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_zbt_extract_2026():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/DT_ZBT_EXTRACT_2026.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_zbt_extract_2027():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/DT_ZBT_EXTRACT_2027.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_lookup_jca2():

    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/LOOKUP_JCA2.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)

def create_dataset_dt_zbt_extract_2027():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/dt_zbt_extract_2027.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)    

def create_dataset_usr_lookup_saved_coa():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    file_path = os.path.join(base_dir, 'json/usr_lookup_saved_coa.json') 
    file_path = os.path.abspath(file_path)  
    
    with open(file_path) as f:
        data = json.load(f)
    return pd.DataFrame(data)