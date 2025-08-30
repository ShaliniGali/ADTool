from pydantic import (
    Field,
    validator
)

from rds.table_model.socom.DtIssModel import DtIssueModel
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel
from api.internal.resources import (
    create_dynamic_table_class,
    SharedResources,
    ResourceConstraintCOATableSet,
    IssSummaryTableSet,
)

from typing import Dict
from collections import defaultdict

#########################################################################
#pydantic models
class ProgMetaModel:
    """enter the endpoint name here"""
    program_id: str = Field(default='')
    program_code: str = Field(default='')
    eoc_code: str = Field(default='')
    pom_sponsor_code: str = Field(default='')
    capability_sponsor_code: str = Field(default='')
    resource_category_code: str = Field(default='')
    resource_k: Dict[str,int] #{'2026':1000,'2027':2000,...})
    
    @validator('resource_k',pre=True)
    def round_resource_k(cls,resource_k):
        for year in resource_k:
            rounded_value = round(float(resource_k[year]), 2)
        return int(rounded_value) if rounded_value.is_integer() else rounded_value

#########################################################################
#serializers classes
class ProgEoCFundSerializer:
    def __init__(self):
        self.data = []
    
    def __repr__(self):
        return f"{self.data}"

    def serialize(self,data):
        grouped_data = defaultdict(lambda: {"RESOURCE_K": {}})
        for entry in data: #(values of each field)
            key = (
                entry["ID"],
                entry["PROGRAM_CODE"],
                entry["PROGRAM_GROUP"],
                entry["ASSESSMENT_AREA_CODE"],
                entry["EOC_CODE"],
                entry["POM_SPONSOR_CODE"],
                entry["CAPABILITY_SPONSOR_CODE"],
                entry["RESOURCE_CATEGORY_CODE"],
                entry["OSD_PROGRAM_ELEMENT_CODE"],
                entry["EXECUTION_MANAGER_CODE"]
            )
            grouped_data[key]["RESOURCE_K"][entry["FISCAL_YEAR"]] = entry["RESOURCE_K"]
        
        for key, value in grouped_data.items():
            self.data.append({
                "ID": key[0],
                "PROGRAM_CODE": key[1],
                "PROGRAM_GROUP": key[2],
                "ASSESSMENT_AREA_CODE": key[3],
                "EOC_CODE": key[4],
                "POM_SPONSOR_CODE": key[5],
                "CAPABILITY_SPONSOR_CODE": key[6],
                "RESOURCE_CATEGORY_CODE": key[7],
                "OSD_PROGRAM_ELEMENT_CODE": key[8],
                "EXECUTION_MANAGER_CODE": key[9],
                "RESOURCE_K": value["RESOURCE_K"]
            })


class ProgEventFundSerializer:
    def __init__(self):
        self.data = []

    def serialize(self, data):
        grouped_data = defaultdict(lambda: {"DELTA_AMT": {}, "EVENT_NAME": None, "OSD_PE": None})
        for entry in data:
            key = (
                entry["ID"],
                entry["PROGRAM_CODE"],
                entry["PROGRAM_GROUP"],
                entry["ASSESSMENT_AREA_CODE"],
                entry["EOC_CODE"],
                entry["POM_SPONSOR_CODE"],
                entry["CAPABILITY_SPONSOR_CODE"],
                entry["RESOURCE_CATEGORY_CODE"],
                entry["EXECUTION_MANAGER_CODE"],
                entry["EVENT_NAME"],
                entry["OSD_PROGRAM_ELEMENT_CODE"]

            )
            grouped_data[key]["DELTA_AMT"][entry["FISCAL_YEAR"]] = entry["DELTA_AMT"]

        for key, value in grouped_data.items():
            self.data.append({
                "ID": key[0],
                "PROGRAM_CODE": key[1],
                "PROGRAM_GROUP": key[2],
                "ASSESSMENT_AREA_CODE": key[3],
                "EOC_CODE": key[4],
                "POM_SPONSOR_CODE": key[5],
                "CAPABILITY_SPONSOR_CODE": key[6],
                "RESOURCE_CATEGORY_CODE": key[7],
                "EXECUTION_MANAGER_CODE": key[8],
                "EVENT_NAME": key[9],
                "OSD_PE":key[10],      
                "DELTA_AMT": value["DELTA_AMT"]
            })


###################
#serialize methods
def get_prog_eoc_funding(db_conn,program_ids):
    table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtIssueModel,table_name=table_name)
    data = orm_model.get_resource_k_by_pids(program_ids,db_conn)
    serializer = ProgEoCFundSerializer()
    serializer.serialize(data)
    resp = serializer.data
    return resp


def get_prog_event_funding(db_conn,program_ids):
    table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractModel,table_name=table_name)
    data = orm_model.get_delta_amt_by_pids(program_ids,db_conn)
    serializer = ProgEventFundSerializer()
    serializer.serialize(data)
    resp = serializer.data
    return resp
