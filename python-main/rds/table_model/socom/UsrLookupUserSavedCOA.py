from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)

from api.internal.resources import (
    ResourceConstraintCOATableSet,
    create_dynamic_table_class,
)

import datetime
from fastapi import HTTPException

from sqlalchemy import (
    Boolean, 
    Column, 
    Integer, 
    DateTime,
    JSON,
    VARCHAR,
)

from sqlalchemy.orm import (
    Mapped,

)



class UsrLookupUserSavedCOA(SOCOMBase):
    __tablename__ = "USR_LOOKUP_USER_SAVED_COA"
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True)
    SAVED_COA_ID: Mapped[int] = Column('SAVED_COA_ID',Integer)
    COA_TITLE: Mapped[str] = Column('COA_TITLE',VARCHAR(100))

    @classmethod
    def get_coa_names_from_saved_coa_ids(cls,coa_ids,db_conn):
        query = db_conn.query(cls.SAVED_COA_ID,cls.COA_TITLE).filter(cls.SAVED_COA_ID.in_(coa_ids)).all()

        data = {coa_id:"" for coa_id in coa_ids} #default empty string
        for saved_coa_id,coa_title in query:
            data[saved_coa_id] =  coa_title

        return data
