from fastapi import APIRouter
from typing import List, Optional
from enum import Enum
from api.internal.conn import get_socom_session
from authentication.auth import (
    create_jwt_token,
    refresh_jwt_token,
)

from api.internal.redis_cache import get_redis_decoded

from fastapi import Depends
from typing import Dict

from fastapi import (
    APIRouter,
    Depends
)

router = APIRouter(
    prefix="/auth",
    tags=["Authentication"],
    responses={404:{"description":"Endpoint Not Found"}}
)


@router.post("/jwt",status_code=200,name="create jwt token")
async def create_jwt(data:Dict,db_conn=Depends(get_socom_session)):
    expires_delta = data.get("exp",None)
    data = await create_jwt_token(data,expires_delta,db_conn)
    return data


@router.post("/jwt/refresh", status_code=200, name="refresh JWT Token")
async def refresh_jwt(token:str, expires_delta:Optional[int], db_conn=Depends(get_socom_session),redis=Depends(get_redis_decoded)):
    # Optional: Validate user in DB here before token generation
    refresh_token = await refresh_jwt_token(token,expires_delta,redis,db_conn)
    return refresh_token

