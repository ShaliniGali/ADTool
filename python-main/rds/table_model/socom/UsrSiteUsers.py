from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

import enum
from datetime import datetime

from sqlalchemy import (
    select,
    Boolean, 
    Column, 
    Integer, 
    DateTime,
    JSON,
    SmallInteger, 
    Enum,
    VARCHAR,
)

from sqlalchemy.orm import (
    Mapped,
)

from fastapi import HTTPException

# Optional: Enum type defined using Python Enum for type safety
class GroupEnum(str, enum.Enum):
    NONE = "None"
    POM_ADMIN = "Pom Admin"
    POM_USER = "Pom User"


class UsrSiteUsers(SOCOMBase):
    __tablename__ ="USR_SITE_USERS"
    __table_args__={
        'schema': SCHEMA
    }
    
    ID: Mapped[int] = Column(Integer, primary_key=True, autoincrement=True)
    GROUP: Mapped[str] = Column(VARCHAR(100), nullable=False) #Enum not varchar, currently only use for val
    USER_ID: Mapped[int] = Column(Integer, nullable=False)
    CREATED_DATETIME: Mapped[datetime] = Column(DateTime, nullable=False)
    UPDATED_DATETIME: Mapped[datetime] = Column(DateTime, nullable=False)
    IS_DELETED: Mapped[bool] = Column(Boolean, nullable=False, default=False)
    UPDATE_USER: Mapped[int] = Column(Integer, nullable=True)
    HISTORY_DATETIME: Mapped[datetime] = Column(DateTime, nullable=True)

    @classmethod
    async def verify_user_role(cls,user_id,user_role,db_conn):
        # print(user_id,user_role)

        stmt = (
            select(cls).where(cls.USER_ID == user_id).where(cls.GROUP == user_role).where(cls.IS_DELETED==0)
        )

        # from sqlalchemy.dialects import mysql
        # print(stmt.compile(dialect=mysql.dialect(), compile_kwargs={"literal_binds": True}))

        result =  db_conn.execute(stmt).first()
        
        if not result:
            raise HTTPException(401,"Invalid user_id or user_role")
        return True if result else False #if there exist record