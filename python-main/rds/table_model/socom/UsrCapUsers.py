from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)
from fastapi import HTTPException

import enum
from datetime import datetime
from typing import List

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


class UsrCapUsers(SOCOMBase):
    __tablename__ ="USR_CAP_USERS"
    __table_args__={
        'schema': SCHEMA
    }
    
    ID: Mapped[int] = Column(Integer, primary_key=True, autoincrement=True)
    GROUP: Mapped[str] = Column(VARCHAR(13), nullable=False)
    USER_ID: Mapped[int] = Column(Integer, nullable=False)
    CREATED_DATETIME: Mapped[datetime] = Column(DateTime, nullable=False)
    UPDATED_DATETIME: Mapped[datetime] = Column(DateTime, nullable=False)
    IS_DELETED: Mapped[bool] = Column(Boolean, nullable=False, default=False)
    UPDATE_USER: Mapped[int] = Column(Integer, nullable=True)
    HISTORY_DATETIME: Mapped[datetime] = Column(DateTime, nullable=True)

    @classmethod
    async def verify_cap_group(cls,user_id,cap_group:List[str],db_conn):
        stmt = (
            select(cls.GROUP).where(cls.USER_ID == user_id).where(cls.GROUP.in_(cap_group)).where(cls.IS_DELETED==0)
        )
        result = db_conn.execute(stmt)
        found_groups = {row[0] for row in result.fetchall()}

        missing = set(cap_group) - found_groups
        if missing:
            raise HTTPException(status_code=400, detail=f"User not permitted in group(s): {list(missing)}")

        return True