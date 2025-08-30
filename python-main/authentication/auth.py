import jwt
import os
from typing import (
    Optional, 
    List, 
    Callable
)

from jwt import ExpiredSignatureError, InvalidTokenError  # PyJWT exceptions
from datetime import datetime, timedelta, timezone
from fastapi import (
    HTTPException,
    Depends,
    status,
)

from fastapi.security import (
    HTTPBearer,
    HTTPAuthorizationCredentials,
)

from api.internal.redis_cache import get_redis_decoded

from rds.table_model.socom.UsrCapUsers import UsrCapUsers
from rds.table_model.socom.UsrSiteUsers import UsrSiteUsers

from pydantic import BaseModel

from enum import Enum

JWT_SECRET_KEY = os.environ.get("SOCOM_JWT_SECRET_KEY")
JWT_ALGORITHM = os.environ.get("SOCOM_JWT_ALGORITHM")

BEARER_SCHEME = HTTPBearer() #global scheme object

##Pydantic Models
class Token(BaseModel):
    access_token:str
    token_type: str

class UserRole(str,Enum):
    ADMIN = "Pom Admin"
    USER = "Pom User"
    NONE = "None"

class UserCapGroup(BaseModel):
    cap_groups : List[str]

##End of Pydantic Models

async def create_jwt_token(data: dict, expires_delta: Optional[timedelta],db_conn):
    
    #{"user_id":..,"cap_group":[...],"user_role":...}
    if "user_role" not in data or "user_id" not in data:
        raise HTTPException(status_code=422, detail="Invalid Request, needs user role and ID")

    if expires_delta:
        expire = datetime.now(timezone.utc) + timedelta(minutes=expires_delta)
    else:
        expire = datetime.now(timezone.utc) + timedelta(minutes=120) #120 min default
    
    to_encode = data.copy()
    _ = await UsrCapUsers.verify_cap_group(user_id=data['user_id'],cap_group=data["cap_group"],db_conn=db_conn)
    _ = await UsrSiteUsers.verify_user_role(user_id=data["user_id"],user_role=data["user_role"],db_conn=db_conn)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, JWT_SECRET_KEY, algorithm=JWT_ALGORITHM)
    return Token(access_token=encoded_jwt,token_type="bearer")

async def decode_jwt_token(token: str,verify_exp:bool,redis):
    blacklist_check_key = f"SOCOM::JWT::BLACKLIST::{token}"

    if redis.exists(blacklist_check_key):
        raise HTTPException(401,"Invalid Token. Blacklisted")
    try:
        options= {"verify_exp":verify_exp}
        error = None
        payload = jwt.decode(token, JWT_SECRET_KEY, algorithms=[JWT_ALGORITHM],options=options)

        return payload, error
    except ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="ExpiredToken")
    except InvalidTokenError:
        raise HTTPException(status_code=401, detail="InvalidToken")

async def refresh_jwt_token(token: str,expires_delta,redis,db_conn):
    key = f"SOCOM::JWT::BLACKLIST::{token}"

    #blacklist checks happen inside, not checking expiration else ExpiredSignatureError
    payload,error = await decode_jwt_token(token,verify_exp=False,redis=redis)
    
    user_data = {
        "user_id": payload["user_id"],
        "user_role": payload["user_role"],
        "cap_group": payload["cap_group"]
    }

    redis.set(key,1,ex=10800) #dummy value


    token =  await create_jwt_token(user_data,expires_delta=expires_delta,db_conn=db_conn)
    
    return token #Token() obj


async def get_current_user(credentials:HTTPAuthorizationCredentials = Depends(BEARER_SCHEME),redis=Depends(get_redis_decoded)):
    """
    Args:
        credentials: HTTPAuthorizationCredentials(scheme='Bearer',credentials='***')
        redis: dependency injection
    Returns:
        {"user_id":1,"user_role":"role","cap_group":[list of allowed cap groups],"exp":seconds,**kwargs}
    """
    token= credentials.credentials
    payload, error = await decode_jwt_token(token,verify_exp=True,redis=redis)
    return payload


def require_roles(*allowed_roles:UserRole) -> Callable:
    #wrapper due to dependency injection
    def role_checker(user=Depends(get_current_user)):
        user_role = user.get("user_role")
        if user_role not in allowed_roles: #list of enums of UserRole to check!
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail=f"Insufficient role permission: required one of {[r.value for r in allowed_roles]}"
            )
        return user
    return role_checker