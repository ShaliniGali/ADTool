"""
Sumit 8 Dec 2022
At an endpoint, you pass AppName, FeatureName, SubAppName, a list of roles ["admin", "moderator"]
return will be individual access based on list of roles [True, False, True]
return will be Overall access True/False
"""
import pandas as pd
import os, json
import time
from params import api_table_status, access_constants
from fastapi import HTTPException
from audit import audit_log

db_name = os.environ.get('FACS_DB_NAME')


"""
connect to database
"""

def get_db_id(db_name="", db_table="", search_field="", field="", db_session=None):
    select_data = None
    if isinstance(field, str):
        select_data = pd.read_sql(f'''SELECT id FROM {db_name}.{db_table} where status="{api_table_status['active']}" and {search_field}="{field}" limit 1''', db_session.bind)
    if select_data.empty == False:
        return str(select_data['id'][0])
    else:
        return False

def get_feature_id_with_subapp_id(role_id=None, db_session=None, db_name="", app_name="",subapp_name="",feature_name="",table1="keycloak_tiles",table2="subapp_info",table3="feature_info",table4="role_feature_mapping"):
    select_data = None
    select_data = pd.read_sql(f'''SELECT rfm.id FROM {db_name}.{table4} AS rfm JOIN {db_name}.{table1} AS kt ON rfm.app_id=kt.id AND kt.`title`='{app_name}' JOIN {db_name}.{table2} AS si ON rfm.subapp_id=si.id AND si.status = '{api_table_status["active"]}' AND si.`Name`='{subapp_name}' JOIN {db_name}.{table3} AS f ON rfm.feature_id=f.id AND f.status = '{api_table_status["active"]}' AND f.`Name`='{feature_name}' WHERE JSON_CONTAINS(rfm.`user_role_id`, '{role_id}', "$") limit 1''', db_session.bind)
    if select_data.empty == False:
        return str(select_data['id'][0])
    else:
        return False

def get_access_result(app_name="", sub_app="", feature="", roles=[""], overall="True", db_session=None, user_id=None):
    print(app_name, sub_app, feature)
    output = {"Access":[False] * len(roles)}
    app_id = get_db_id(db_name=db_name, db_table="keycloak_tiles", search_field="title", field=app_name, db_session=db_session)
    inputs = {"app_name": app_name, "sub_app": sub_app, "feature": feature, "roles": roles, "overall": overall}
    if(app_id == False):
        error_msg = f"Requested section does not exist:AppName={app_name}"
        audit_log(user_id, access_constants["access_result"], inputs, output["Access"], error_msg, db_session)
        return error_msg
    sub_app_id= get_db_id(db_name=db_name, db_table="subapp_info", search_field="Name", field=sub_app, db_session=db_session)
    
    if(sub_app_id == False):
        error_msg = f"Requested section does not exist:SubApp={sub_app}" 
        audit_log(user_id, access_constants["access_result"], inputs, output["Access"], error_msg, db_session)
        return error_msg

    feature_id= get_db_id(db_name=db_name, db_table="feature_info", search_field="Name", field=feature, db_session=db_session)
    if(feature_id == False):
        error_msg = f"Requested section does not exist:Feature={feature}"
        audit_log(user_id, access_constants["access_result"], inputs, output["Access"], error_msg, db_session)
        return error_msg

    for index, x in enumerate(roles):
        select_data = pd.read_sql(f'''SELECT id FROM {db_name}.user_roles where status="{api_table_status['active']}" and Name="{x}" limit 1''', db_session.bind)
        role_id = False
        if select_data.empty == False:
            role_id = str(select_data['id'][0])
        output["Access"][index] = role_id
        print(app_id, sub_app_id, feature_id, role_id)

        if app_id!=False and sub_app_id!=False and feature_id!=False and role_id!=False:
            select_data= get_feature_id_with_subapp_id(role_id, db_session, db_name, app_name, sub_app, feature)
            print(select_data)

            if select_data:
                output["Access"][index] = True
            else: 
                output["Access"][index] = False
    if(overall == "True") or (overall == None):
        output["Access"] = all(role for role in output["Access"])
    audit_log(user_id, access_constants["access_result"], inputs, output["Access"], output, db_session)
    return output

def create_role_mapping(user_id=None, app_name="", sub_app="", feature="", roles=[], db_session=None):
    inputs = {"app_name": app_name, "sub_app": sub_app, "feature": feature, "roles": roles}
    
    app_id = get_db_id(db_name=db_name, db_table="keycloak_tiles", search_field="title", field=app_name, db_session=db_session)
    if(app_id == False):
        error_msg = f"Requested section does not exist:AppName={app_name}"
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["error"], error_msg, db_session)
        return error_msg
    
    sub_app_id= get_db_id(db_name=db_name, db_table="subapp_info", search_field="Name", field=sub_app, db_session=db_session)
    if(sub_app_id == False):
        error_msg = f"Requested section does not exist:SubApp={sub_app}" 
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["error"], error_msg, db_session)
        return error_msg
    
    feature_id= get_db_id(db_name=db_name, db_table="feature_info", search_field="Name", field=feature, db_session=db_session)
    if(feature_id == False):
        error_msg = f"Requested section does not exist:Feature={feature}"
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["error"], error_msg, db_session)
        return error_msg
    
    if(len(roles) == 0):
        error_msg = "Roles are required"
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["error"], error_msg, db_session)
        return error_msg
    
    role_ids = []
    for role in roles:
        role_id = get_db_id(db_name=db_name, db_table="user_roles", search_field="Name", field=role, db_session=db_session)
        if role_id != False:
            role_ids.append(int(role_id))
    if(len(role_ids) == 0):
        error_msg = "Roles are not found"
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["error"], error_msg, db_session)
        return error_msg
    list_str = json.dumps(role_ids)
    select_data = pd.read_sql(f'''SELECT user_role_id FROM {db_name}.role_feature_mapping WHERE app_id='{app_id}' and subapp_id='{sub_app_id}' and feature_id='{feature_id}' limit 1''', db_session.bind)
    try:
        if select_data.empty == False:
            db_session.execute(f"""UPDATE {db_name}.role_feature_mapping SET `user_role_id` = '{list_str}' WHERE app_id='{app_id}' and subapp_id='{sub_app_id}' and feature_id='{feature_id}'""")
        else:
            db_session.execute(f"""INSERT INTO {db_name}.role_feature_mapping (`user_role_id`, `Timestamp`, `app_id`, `subapp_id`, `feature_id`) VALUES ('{list_str}', {str(int(time.time()))}, '{app_id}', '{sub_app_id}', '{feature_id}')""") 
        db_session.commit()
        success_msg = "Mapping Added"
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["mapping_created"], success_msg, db_session)
        return success_msg
    except Exception:
        error_msg="Database error"
        audit_log(user_id, access_constants["role_mapping"], inputs, access_constants["error"], error_msg, db_session)
        db_session.rollback()
        raise HTTPException(400, detail=error_msg)

