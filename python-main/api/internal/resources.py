from api.internal.conn import get_socom_session
from rds.table_model.socom.LookupPOMPositionDecrement import LookupPOMPositionDecrement
from rds.table_model.base_model import get_all_socom_tables
from rds.table_model.socom.UsrLookupPOMPosition import UsrLookupPOMPosition

from typing import (
    Dict, 
    List
)

from pydantic import (
    BaseModel,
    Field,
)

from rds.table_model.base_model import (
    SCHEMA,
)

####-------------------------------------
# Defining lifespan resources
####-------------------------------------

class SharedResources:
    DT_TABLE_DECR_MAP : dict[str,int] = dict()
    DT_TABLE_SET : set = set()

class ZbtSummaryTableSet:
    CURRENT: dict[str, List[str]] = Field(default_factory=dict)
    HISTORICAL_POM: dict[str, List[str]] = Field(default_factory=dict)
    
    def __repr__(self):
        return f"ZbtSummaryTableSet(CURRENT={self.CURRENT}, HISTORICAL_POM={self.HISTORICAL_POM})"
    
    def __str__(self):
        return f"Current: {self.CURRENT}, Historical POM: {self.HISTORICAL_POM}"
    
class IssSummaryTableSet:
    CURRENT: dict[str, List[str]] = Field(default_factory=dict)
    HISTORICAL_POM: dict[str, List[str]] = Field(default_factory=dict)
    
    def __repr__(self):
        return f"IssSummaryTableSet(CURRENT={self.CURRENT}, HISTORICAL_POM={self.HISTORICAL_POM})"
    
    def __str__(self):
        return f"Current: {self.CURRENT}, Historical POM: {self.HISTORICAL_POM}"


class ResourceConstraintCOATableSet:
    CURRENT: dict[str, List[str]] = Field(default_factory=dict)
    
    def __repr__(self):
        return f"ResourceConstraintCOATableSet(CURRENT={self.CURRENT}"
    
    def __str__(self):
        return f"Current: {self.CURRENT}"
    

def create_dynamic_table_class(table_name: str,AbstractORMClass):
    # Dynamically define a new class
    DynamicTable = type(
        table_name,  # Class name, which can be same as the table name
        (AbstractORMClass,),  # Inherit from AbstractBase
        {
            '__tablename__': table_name,  # Set the table name dynamically
            '__table_args__': {'extend_existing': True,"schema":SCHEMA},
        }
    )
    return DynamicTable
    


def query_pom_position_decrement_map():
    db_conn = next(get_socom_session())  # Get the database session
    try:
        SharedResources.DT_TABLE_DECR_MAP = LookupPOMPositionDecrement.get_decrement_map(db_conn)
    finally:
        db_conn.close()  # Ensure the session is closed afterward


def get_all_dt_tables():
    tables = [table for table in get_all_socom_tables()]
    SharedResources.DT_TABLE_SET = set(table for table in tables if table.startswith("DT_"))


def set_pom_position():
    db_conn = next(get_socom_session())
    pom_year,pom_position = UsrLookupPOMPosition.get_active_pom_year_position(db_conn)
    
    return int(pom_year),pom_position