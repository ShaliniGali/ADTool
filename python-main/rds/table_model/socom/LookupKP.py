from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List
from fastapi import HTTPException

from sqlalchemy import (
    Column,
    VARCHAR,
    JSON,
)

from sqlalchemy.orm import (
    Mapped,

)




class LookupKOPKSP(SOCOMBase):
    __tablename__ = 'LOOKUP_KOP_KSP'
    __table_args__ = {
        'schema': SCHEMA
    }
    ID:Mapped[str] = Column('ID',VARCHAR(10),primary_key=True)
    TYPE:Mapped[str] = Column('TYPE',VARCHAR(3))
    CHILDREN:Mapped[JSON] = Column('CHILDREN',JSON)
    DESCRIPTION:Mapped[str] = Column('DESCRIPTION',VARCHAR(5000))

    @classmethod
    def get_description_by_ids(cls,ids:List[str],db_conn):
        query = db_conn.query(cls).filter(cls.ID.in_(ids))
        data = query.all()
        
        #changing these dtypes may affect the response model in pydantic 
        result = {obj.ID:{"TYPE":obj.TYPE,
                              "CHILDREN":obj.CHILDREN,
                              "DESCRIPTION":obj.DESCRIPTION} 
                    for obj in data}
        
        for id in ids:
            if str(id) not in result:
                raise HTTPException(404,f"No ID found for: {id}")
        return result

    @classmethod
    def get_noncovered_by_ids(cls,ids:List[str],level:int,db_conn):
        if level not in [3,4]:
            HTTPException(422,details="Invalid level: Needs to be one of levels 3 or 4")
        
        if level == 3:
            ids = [id for id in ids if id.endswith(".0")] #1.1.1.0, 1.1.2.0 ect...
            
            if not ids:
                return []

            query = db_conn.query(cls.ID).filter(cls.ID.like("%.0"))
        
        if level == 4:
            ids = [id for id in ids if not id.endswith(".0")]
            if not ids:
                return []
            
            query = db_conn.query(cls.ID).filter(cls.ID.notlike("%.0"))


        result = [data[0] for data in query.all()]
        result = [data for data in set(result) - set(ids)]
        print(len(result))
        # breakpoint()
        return sorted(result)