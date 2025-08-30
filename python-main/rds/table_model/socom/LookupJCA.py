from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List
from fastapi import HTTPException

from sqlalchemy import (
    Column,
    VARCHAR,
)

from sqlalchemy.orm import (
    Mapped,

)




class LookupJCA(SOCOMBase):
    __tablename__ = "LOOKUP_JCA2" #later version.
    __table_args__ = {
        'schema': SCHEMA
    }
    ID:Mapped[str] = Column('ID',VARCHAR(10),primary_key=True)
    DESCRIPTION:Mapped[str] = Column('DESCRIPTION',VARCHAR(1000))

    @classmethod
    def get_description_by_ids(cls,ids:List[str],db_conn):
        query = db_conn.query(cls).filter(cls.ID.in_(ids))
        data = query.all()

        result = {obj.ID:obj.DESCRIPTION for obj in data}
        #assertion to make sure inputs are not bad keys
        for id in ids:
            if id not in result:
                raise HTTPException(404,f"No ID found for: '{id}'")
        return result
    
    @classmethod
    def get_noncovered_by_ids(cls,ids:List[str],level:int,db_conn):
        if level not in [1,2,3]:
            HTTPException(422,details="Invalid level: Needs to be one of levels 1,2, or 3")
        
        if level == 3:
            ids = [id for id in ids if not id.endswith(".0")] #skip 3.1.0 on third level for example, that would be second level
            
            if not ids:
                return []
            
            #query = db_conn.query(cls.ID).filter(or_(*like_conditions)).filter(cls.ID.notlike("%.0"))
            query = db_conn.query(cls.ID).filter(cls.ID.notlike("%.%.0"))
        
        if level == 2:
            ids = [id for id in ids if not id.endswith(".0.0")]
            if not ids:
                return []

            #query = db_conn.query(cls.ID).filter(or_(*like_conditions)).filter(cls.ID.notlike("%.0.0"))
            query = db_conn.query(cls.ID).filter(cls.ID.like("%.%.0")).filter(cls.ID.notlike("%.0.%"))


        if level == 1:
            ids = [id for id in ids if id.endswith(".0.0")]
            if not ids:
                return []
            query = db_conn.query(cls.ID).filter(cls.ID.like("%.0.0"))


        result = [data[0] for data in query.all()]
        result = [data for data in set(result) - set(ids)]
        print(len(result))
        # breakpoint()
        return sorted(result)