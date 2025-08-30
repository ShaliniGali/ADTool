from sqlalchemy import Column, Integer, String
from sqlalchemy.ext.declarative import declarative_base

from typing import (
    Dict,
    List,
    Optional,
    Union,
    Literal,
    Set
)
from pydantic import (
    BaseModel,
    PositiveFloat,
    PositiveInt
)

from enum import Enum
#####
#Optimizer models
class ProgYearDelta(BaseModel):
    year: Dict[str,int]

class ProgInputData(ProgYearDelta):
    program: str
    pom_sponsor: str
    capability_sponsor: str
    guidance_score: Union[int,float]
    guidance_weight: Union[PositiveFloat,PositiveInt]
    pom_score: Union[int,float]
    pom_weight: Union[PositiveFloat,PositiveInt]
    
class ProgramModel(BaseModel):
    data: List[ProgInputData]
    must_include:List[str]
    must_exclude:List[str]
    budget: List[Union[int,float]]
    syr: int
    eyr: int
    option: int #Literal[1,2,3] #only 1,2 or 3
    support_all_years:bool

#response model for /socom/ services
class ProgEocFundingModel(BaseModel):
    """Response model to get program eoc funding for DT_ISS_20xx tables"""
    ID: str
    PROGRAM_CODE: Union[str,None]
    ASSESSMENT_AREA_CODE: Union[str,None]
    EOC_CODE: Union[str,None]
    POM_SPONSOR_CODE: Union[str,None]
    RESOURCE_CATEGORY_CODE: Union[str,None]
    CAPABILITY_SPONSOR_CODE: Union[str,None]
    PROGRAM_GROUP: Union[str,None]
    PROGRAM_NAME: Optional[str] = None
    OSD_PROGRAM_ELEMENT_CODE: Optional[str] = None
    PROGRAM_NAME: Optional[str] = None
    EXECUTION_MANAGER_CODE: Optional[str] = None
    RESOURCE_K: Dict[str,int]



class ProgEOCFundingResponse(BaseModel):
    #This tells Pydantic that the root of this model is a dictionary
    __root__: List[ProgEocFundingModel]

class ZbtSummaryFilterInputModel(BaseModel):
    CAPABILITY_SPONSOR_CODE: Union[List[str],None]
    POM_SPONSOR_CODE: Union[List[str],None]
    ASSESSMENT_AREA_CODE: Union[List[str],None]
    PROGRAM_GROUP: Union[List[str],None]
    PROGRAM_NAME: Union[List[str],None]
    REFRESH: bool
    APPROVAL_FILTER: Union[List[str], None]


class IssSummaryFilterInputModel(BaseModel):
    CAPABILITY_SPONSOR_CODE: Union[List[str],None]
    POM_SPONSOR_CODE: Union[List[str],None]
    ASSESSMENT_AREA_CODE: Union[List[str],None]
    PROGRAM_GROUP: Union[List[str],None]
    PROGRAM_NAME: Union[List[str],None]
    REFRESH: bool
    APPROVAL_FILTER: Union[List[str], None]



class JCANonCoveredInputModel(BaseModel):
    ids: List[str]
    level: int

class CGANonCoveredInputModel(BaseModel):
    ids: List[str]
    level: str #Union[Literal["gap_id"],Literal["group_id"]] #strictly enforced


class CritScoreDownloadInput(BaseModel):
    """use to download scores from excel file"""
    class TypeOfCOA(str, Enum):
        ISS_EXTRACT = "ISS_EXTRACT"
        ISS = "ISS"
    assessment_area_code: Union[None,List[str]]
    program_group: Union[None,List[str]]
    cycle_id: int
    TYPE_OF_COA: TypeOfCOA

class PomPositionEnum(str,Enum):
    EXT = "EXT"
    ZBT = "ZBT"
    ISS = "ISS"
    POM = "POM"

class PomPositionInput(BaseModel):
    position: PomPositionEnum
    year: int = 2026
    
class ProgEventFundingModel(BaseModel):
    ID: str
    PROGRAM_CODE: Union[str, None]
    ASSESSMENT_AREA_CODE: Union[str, None]
    EOC_CODE: Union[str, None]
    POM_SPONSOR_CODE: Union[str, None]
    RESOURCE_CATEGORY_CODE: Union[str, None]
    CAPABILITY_SPONSOR_CODE: Union[str, None]
    PROGRAM_GROUP: Union[str, None]
    EXECUTION_MANAGER_CODE: Union[str,None]
    DELTA_AMT: Dict[str, float]
    EVENT_NAME: Union[str, None]
    OSD_PE: Union[str, None]

class ProgEventFundingResponse(BaseModel):
    __root__: List[ProgEventFundingModel]


class AggPbFundingInput(BaseModel):
    ASSESSMENT_AREA_CODE: Optional[List[str]] = None
    CAPABILITY_SPONSOR_CODE: Optional[List[str]] = None
    EOC_CODE: Optional[List[str]] = None
    EXECUTION_MANAGER_CODE: Optional[List[str]] = None
    FISCAL_YEAR: Optional[List[str]] = None
    OSD_PROGRAM_ELEMENT_CODE: Optional[List[str]] = None
    PROGRAM_CODE: Optional[List[str]] = None
    PROGRAM_GROUP: Optional[List[str]] = None
    RESOURCE_CATEGORY_CODE: Optional[List[str]] = None

class AggBudgetExecInput(AggPbFundingInput):
    pass