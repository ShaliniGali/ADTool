from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from rds.table_model.socom.LookupStorm import LookupStorm
from rds.table_model.socom.LookupProgramDetail import LookupProgramDetailModel

from fastapi import HTTPException

from sqlalchemy import ( 
    Column, 
    Integer, 
    VARCHAR,
    JSON,
    or_,
    and_
)

from sqlalchemy.orm import (
    Mapped, 

)

class UsrOptionScore(SOCOMBase):
    __tablename__ ="USR_OPTION_SCORES"
    __table_args__={
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column(Integer,primary_key=True,autoincrement=True) #dummy
    SESSION: Mapped[JSON] = Column('SESSION',JSON)
    USER_ID: Mapped[int] = Column('USER_ID',Integer)
    PROGRAM_ID: Mapped[str] = Column('PROGRAM_ID',VARCHAR(40))
    CRITERIA_NAME_ID: Mapped[int] = Column('CRITERIA_NAME_ID',Integer)

    @staticmethod
    def parse_pom_wts(score_dict,wts_data,db_conn,storm_flag):
        if storm_flag:
            prog_ids = list(score_dict.keys())
            prog_storm = LookupStorm.get_total_score_from_progIds(LookupProgramDetailModel, db_conn, prog_ids, to_dict=True)
        
        result = {k:{} for k in score_dict.keys()} #{progid:{}}
        for prog_id in score_dict:
            total_pom = 0
            total_guidance = 0
            for cat in score_dict[prog_id]:
                if cat in wts_data["pom"]:
                    # print(cat,wts_data['pom'][cat],score_dict[prog_id][cat])
                    total_pom  += float(score_dict[prog_id][cat])*float(wts_data["pom"][cat])
                if cat in wts_data["guidance"]:
                    # print(wts_data['guidance'][cat],score_dict[prog_id][cat])                
                    total_guidance  += float(score_dict[prog_id][cat])*float(wts_data["guidance"][cat])
            result[prog_id]["weighted_pom_score"] = round(total_pom,2) if not isinstance(total_pom,int) else total_pom
            result[prog_id]["weighted_guidance_score"] = round(total_guidance,2) if not isinstance(total_guidance,int) else total_guidance
            # breakpoint()
            if storm_flag and prog_id in prog_storm:
                result[prog_id]["total_storm_score"] = prog_storm[prog_id]
            elif storm_flag and prog_id not in prog_storm:
                result[prog_id]["total_storm_score"] = 0
                print(f"warning: {prog_id} is not found in LOOKUP_STORM table")
        return result

    @classmethod
    def calculate_weights(cls,db_conn,UsrLookupCriticalWts,weights_id,score_id,criteria_name_id,storm_flag):
        """
        passing the weights id, we can calculate the weighted score for pom and guidance
        each program has its own scores - retrieve from the database
        score_id = {"PROGRAM_ID":..,"USER_ID":...}
        """
        
        conditions = [and_(cls.PROGRAM_ID == score['PROGRAM_ID'], cls.USER_ID == score['USER_ID']) for score in score_id]
        score_data = db_conn.query(cls.PROGRAM_ID,cls.SESSION).filter(cls.CRITERIA_NAME_ID == criteria_name_id).filter(or_(*conditions)).all()
        if not score_data:
            raise HTTPException(400,"criteria_name_id  and score_id combination could not find any scored parameters in the database")
        
        # Assuming score_data is a list of tuples where each tuple is (PROGRAM_ID, SESSION)
        score_dict = {}
        for data in score_data:
            program_id, session = data
            score_dict[program_id] = session
        
        wts_data = db_conn.query(UsrLookupCriticalWts.SESSION).filter(UsrLookupCriticalWts.WEIGHT_ID == weights_id).one_or_none()[-1]
        # print(score_dict)
        # print(wts_data)
        if not score_dict or not wts_data:
            return None
        # print(score_dict)
        result = UsrOptionScore.parse_pom_wts(score_dict,wts_data,db_conn,storm_flag)
        # print(result)
        # result = {key:sum(result[key].values()) for key in result}
        return result