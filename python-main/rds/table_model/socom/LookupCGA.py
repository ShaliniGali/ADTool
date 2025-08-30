from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List
from fastapi import HTTPException

from sqlalchemy import (
    Column,
    VARCHAR,
    Integer,
    distinct,
)

from sqlalchemy.orm import (
    Mapped,

)




class LookupCGA(SOCOMBase):
    __tablename__ = 'LOOKUP_CGA'
    __table_args__ = {
        'schema': SCHEMA
    }
    GAP_ID:Mapped[int] = Column('GAP_ID',Integer,primary_key=True)
    CGA_NAME:Mapped[str] = Column('CGA_NAME',VARCHAR(100))
    GAP_DESCRIPTION:Mapped[str] = Column('GAP_DESCRIPTION',VARCHAR(5000))
    GROUP_ID:Mapped[int] = Column('GROUP_ID',Integer)
    GROUP_DESCRIPTION:Mapped[str] = Column('GROUP_DESCRIPTION',VARCHAR(5000))

    @classmethod
    def get_description_by_ids(cls,ids:List[str],db_conn):
        query = db_conn.query(cls).filter(cls.GAP_ID.in_(ids))
        data = query.all()
        
        #changing these dtypes may affect the response model in pydantic 
        result = {str(obj.GAP_ID):{"CGA_NAME":obj.CGA_NAME,
                              "GAP_DESCRIPTION":obj.GAP_DESCRIPTION,
                              "GROUP_ID":str(obj.GROUP_ID),
                              "GROUP_DESCRIPTION":obj.GROUP_DESCRIPTION} 
                    for obj in data}
        
        for id in ids:
            if str(id) not in result:
                raise HTTPException(404,f"No ID found for: {id}")
        return result
    
    @classmethod
    def get_noncovered_by_ids(cls,ids:List[str],level:int,db_conn):  
        print(ids)
        if level == "group_id":
            # like_conditions = [cls.GROUP_ID.notlike(p) for p in ids]
            query = db_conn.query(distinct(cls.GROUP_ID)).filter(cls.GROUP_ID.notin_(ids))
            result = [data[0] for data in query.all()]
        elif level == "gap_id":
            # breakpoint()
            gap_ids = [id.split(".")[-1] for id in ids] #xx.yy ->  yy
            group_ids = [id.split(".")[0] for id in ids] #xx.yy -> xx
            
            #query = db_conn.query(cls.GROUP_ID,cls.GAP_ID).filter(cls.GROUP_ID.in_(group_ids)).filter(cls.GAP_ID.notin_(gap_ids))
            #query = db_conn.query(cls.GROUP_ID,cls.GAP_ID).filter(cls.GAP_ID.notin_(gap_ids)).distinct()
            query = db_conn.query(cls.GROUP_ID,cls.GAP_ID)
            result = [str(data[0])+"."+str(data[1]) for data in query.all()]
            result = [res for res in result if res not in ids]
        # breakpoint()
        print(len(result))
        return sorted(result)