import jwt, os
import time
import string
import secrets
from typing import List
from fastapi import HTTPException, Security
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from passlib.context import CryptContext
from datetime import datetime, timedelta
import pandas as pd

from params import api_key_type, api_table_status, auth_constants
from audit import audit_log
cred_table = os.environ.get('FACS_CRED_TABLE')
DB_name = os.environ.get('FACS_DB_NAME')

class AuthHandler():
    security = HTTPBearer()
    pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
    
    secret = os.environ.get('FACS_ROLE_SERVICE_KEY')

    def get_password_hash(self, password):
        return self.pwd_context.hash(password)

    def verify_password(self, plain_password, hashed_password):
        return self.pwd_context.verify(plain_password, hashed_password)

    def encode_token(self, user_id):
        payload = {
            'exp': datetime.utcnow() + timedelta(days=0, minutes=25),
            'iat': datetime.utcnow(),
            'sub': user_id
        }
        return jwt.encode(
            payload,
            self.secret,
            algorithm='HS256'
        )

    def decode_token(self, token):
        try:
            payload = jwt.decode(token, self.secret, algorithms=['HS256'])
            return payload['sub']
        except jwt.ExpiredSignatureError:
            raise HTTPException(status_code=401, detail='Signature has expired')
        except jwt.InvalidTokenError:
            raise HTTPException(status_code=401, detail='Invalid token')

    def auth_wrapper(self, auth: HTTPAuthorizationCredentials = Security(security)):
        return self.decode_token(auth.credentials)

class UserAuth():
    def valid_key(self,key):
        if key == None or len(key) != 16:
            return {'status':False,'message':"Key does not match, must have valid non-empty 16 digit key"}
        return {'status':True,'message':"Valid Key"}

    def first_time_admin_key(self, db_session):
        select_data = pd.read_sql(f"select `Type` from {DB_name}.{cred_table} WHERE `Status` = 'Active' AND `Type` = 'root' LIMIT 1;", db_session.bind)
        if select_data.empty == True:
            key = ''.join(secrets.choice(string.ascii_uppercase + string.digits) for _ in range(16))
            response = self.create_key(key, api_key_type['root'], db_session)
            response['key'] = key
            response['warning'] = 'Do Not share this key to anyone'
            return response
        return {"message":"Failure"}

    def create_key(self, key, user_type, db_session):
        val_key = self.valid_key(key)
        if not val_key['status']:
            raise HTTPException(400, detail=val_key['message'])

        if user_type not in api_key_type.values():
            raise HTTPException(400, detail="User type not allowed")
        
        if self.check_key(key,db_session) != None:
            return {"message":"Key exists"}
        try:
            db_session.execute(f"""insert into {DB_name}.{cred_table} (`Key`,`Type`,`Status`,`Timestamp`) VALUES ('{key}','{user_type}','{api_table_status["active"]}','{str(int(time.time()))}');""")
            db_session.commit()
            return {"message":"Success"}
        except Exception:
            db_session.rollback()
            raise HTTPException(400, detail="Database error")


    
    def check_key(self,key,db_session):
        val_key = self.valid_key(key)
        if not val_key['status']:
            return None
        select_data = pd.read_sql(f"""select `Type` from {DB_name}.{cred_table} WHERE `Status` = '{api_table_status["active"]}' AND `Key` = '{key}' LIMIT 1;""",db_session.bind)

        if select_data.empty == True:
            return None
        return select_data['Type'][0]

    def delete_key(self,key,db_session,user_id=None):
        if self.check_key(key,db_session) != None:
            inputs = """set `Status` = '{api_table_status["deleted"]}' WHERE `Key`='{key}' and `Status`='{api_table_status["active"]}';"""
            try:
                db_session.execute(f"""UPDATE {DB_name}.{cred_table} set `Status` = '{api_table_status["deleted"]}' WHERE `Key`='{key}' and `Status`='{api_table_status["active"]}';""")
                db_session.commit()
                out = {"message": "Success"}
                audit_log(user_id, auth_constants["deleted_key"], inputs, auth_constants["no_value"], out, db_session)
            except Exception:
                audit_log(user_id, auth_constants["error"], inputs, auth_constants["no_value"], auth_constants["error"], db_session)
                db_session.rollback()
                raise HTTPException(400, detail="Database error")
        else:
            inputs = 'key'
            audit_log(user_id, auth_constants["error"], inputs, auth_constants["no_value"], auth_constants["key_error"], db_session)
            raise HTTPException(400, detail=auth_constants["key_error"])
        return out
    
