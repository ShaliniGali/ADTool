from pydantic import BaseModel
from typing import Optional,List


class AuthDetails(BaseModel):
    key: Optional [str]
    # Group can be "admin", "dev", or "app"
    user_type: Optional [str]
    
    
class Feature(BaseModel):
    info_Type: Optional [str]
    info_Value: Optional [str]  
    
class User(BaseModel):
    app_name: Optional [str]
    subapp_name: Optional [str]
    feature_name: Optional [str]
    user_roles: Optional [List[str]]
    overall: Optional [str]
    
    def __str__(self):
        return f'\n {self.app_name} \n {self.subapp_name} \n {self.feature_name} \n {self.user_roles}'

class rmfeature(BaseModel):
    app_name: Optional [str]
    feature_name: Optional [str]
    user_name: Optional [str]
    
    def __str__(self):
        return f'\n {self.app_name} \n {self.feature_name} \n {self.user_name} \n'