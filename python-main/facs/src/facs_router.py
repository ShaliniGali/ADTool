import os

from schemas import AuthDetails, Feature, User, rmfeature
from role_mapping import get_access_result, create_role_mapping
from db_connection import db_conn_info
from ingest import insert_info, delete_info
from auth import AuthHandler, UserAuth
from params import api_key_type, login_constants
from audit import audit_log

from typing import Optional,List
from fastapi import APIRouter, Depends, HTTPException, Request
from pydantic import BaseModel
from sqlalchemy import create_engine
import pandas as pd


#DB CONNECTION
userAuth = UserAuth()
DB_name = os.environ.get('FACS_DB_NAME')
feature_table = os.environ.get('FACS_FEATURE_TABLE')
cred_table = os.environ.get('FACS_CRED_TABLE')
admin_pw = os.environ.get('FACS_ADMIN_PASS')
facs_router = APIRouter(
    prefix="/facs",
    tags=["FACS, role-service"],
    responses={404: {"description": "Endpoint Not Found"}}
)

auth_handler = AuthHandler()

def get_db():
    db = db_conn_info['rhombus_session']()
    try:
        yield db
    finally:
        db.close()

def get_db_row_id(key,db_session):
    select_data = pd.read_sql(f'SELECT id FROM {DB_name}.{cred_table}' + ' where `Key`="'+ key + '" limit 1',db_session.bind)
    return select_data.id.values[0]

def auth_check(valid_values=[], request:Request=None, db_session=None):
    if request.headers.get("Authorization") == None:
        raise HTTPException(401, detail='Please login first')

    usertype = userAuth.check_key(auth_handler.decode_token(request.headers.get("Authorization").split()[1]), db_session)
    for x in valid_values:
        if(x in usertype):
            values = {'type': x, 'status': True, "msg":None}
            return values
    raise HTTPException(403, detail="Invalid user_role to access this endpoint")

#Endpoints

#Only admins should use this endpoint

@facs_router.post('/create_admin_key', status_code=201)
async def create_admin_key(db_session=Depends(get_db)):
    return userAuth.first_time_admin_key(db_session)

@facs_router.post('/create_key', status_code=201)
async def create_key(auth_details: AuthDetails,request: Request, db_session=Depends(get_db)):
    auth_check([api_key_type['root']], request, db_session)
    if auth_details.user_type == api_key_type['root']:
        raise HTTPException(403, detail='A new root user key is not allowed')
    return userAuth.create_key(auth_details.key, auth_details.user_type, db_session)

@facs_router.delete('/delete_key', status_code=201)
async def delete_key(auth_details: AuthDetails,request: Request, db_session=Depends(get_db)):
    auth_check([api_key_type['root']], request, db_session)
    user_api_key = auth_handler.decode_token(request.headers.get("Authorization").split()[1])
    return userAuth.delete_key(auth_details.key, db_session,get_db_row_id(user_api_key,db_session))

#dev & admin groups are allowed to do ingest
@facs_router.post("/ingest")
async def ingest_insert(feature: Feature, request: Request, db_session=Depends(get_db)):
    auth_check([api_key_type['admin'],api_key_type['dev']], request, db_session)
    user_api_key = auth_handler.decode_token(request.headers.get("Authorization").split()[1])
    return insert_info(feature.info_Type, feature.info_Value, db_session,get_db_row_id(user_api_key,db_session)) 

#Only admin group can access this endpoint
@facs_router.delete("/rmfeature")
async def remove_feature(feature: Feature, request: Request, db_session=Depends(get_db)):
    auth_check([api_key_type['admin']], request, db_session)
    user_api_key = auth_handler.decode_token(request.headers.get("Authorization").split()[1])
    result = delete_info(feature.info_Type, feature.info_Value, db_session,get_db_row_id(user_api_key,db_session))
    return result

# admin & app groups can access this endpoint
@facs_router.post("/hasaccess")
async def has_access(user: User, request: Request, db_session=Depends(get_db)):
    auth_check([api_key_type['admin'], api_key_type['app'],  api_key_type['dev'],  api_key_type['testing']], request, db_session)
    user_api_key = auth_handler.decode_token(request.headers.get("Authorization").split()[1])
    return get_access_result(user.app_name,user.subapp_name,user.feature_name,user.user_roles,user.overall,db_session,get_db_row_id(user_api_key,db_session))

@facs_router.post('/create_role_map', status_code=201)
async def create_role_map(user: User, request: Request, db_session=Depends(get_db)):
    auth_check([api_key_type['admin']], request, db_session)
    user_api_key = auth_handler.decode_token(request.headers.get("Authorization").split()[1])
    return create_role_mapping(get_db_row_id(user_api_key,db_session),app_name=user.app_name,sub_app=user.subapp_name,feature=user.feature_name,roles=user.user_roles,db_session=db_session)

@facs_router.post('/login')
async def login(auth_details: AuthDetails, request: Request, db_session=Depends(get_db)):
    key=userAuth.check_key(auth_details.key,db_session)    
    if(key!=None):
        token = auth_handler.encode_token(auth_details.key)
        audit_log(get_db_row_id(auth_details.key, db_session), login_constants["user_login_event"], 'key', login_constants["login_success"], token, db_session)
        return { 'token': token }
    else:
        audit_log(None, login_constants["user_login_event"], key, login_constants["login_failure"], '', db_session)
        raise HTTPException(status_code=401, detail='Invalid or missing API_KEY...')
