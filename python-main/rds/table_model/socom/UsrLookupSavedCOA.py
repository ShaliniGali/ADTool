from rds.table_model.base_model import (
    SOCOMBase, 
    SCHEMA,
)
from typing import List
from rds.table_model.socom.DtIssModel import DtIssueModel
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel

from api.internal.resources import (
    ResourceConstraintCOATableSet,
    create_dynamic_table_class,
    IssSummaryTableSet,
)

import datetime
from fastapi import HTTPException

from sqlalchemy import (
    Boolean, 
    Column, 
    Integer, 
    DateTime,
    JSON,
)

from sqlalchemy.orm import (
    Mapped,

)

class UsrLookupSavedCOA(SOCOMBase):
    __tablename__ = "USR_LOOKUP_SAVED_COA"
    __table_args__ = {
        'schema': SCHEMA
    }
    ID: Mapped[int] = Column('ID',Integer,primary_key=True,autoincrement=True)
    CRITERIA_NAME_ID: Mapped[int] = Column('CRITERIA_NAME_ID',Integer)
    COA_VALUES:Mapped[JSON] = Column('COA_VALUES',JSON)
    OPTIMIZER_INPUT:Mapped[JSON] = Column('OPTIMIZER_INPUT',JSON)
    USER_ID:Mapped[int] = Column('USER_ID',Integer)
    CREATED_DATETIME:Mapped[datetime.datetime] = Column('CREATED_DATETIME',DateTime)
    IS_DELETED:Mapped[bool] = Column('IS_DELETED',Boolean)
    OVERRIDE_TABLE_SESSION: Mapped[JSON] = Column('OVERRIDE_TABLE_SESSION',JSON)

    @classmethod
    def get_alignment_space(cls,LPModel,col,prog_ids:List[str],db_conn):
        """Retrieve JCA/CG/KP spaces for each program id:[...], input is a list of prog ids. col can be 'JCA', 'CGA', 'KOP_KSP'. """
        lp_query = db_conn.query(LPModel.ID,
                                getattr(LPModel,col)).filter(
                                LPModel.ID.in_(prog_ids))
        pid_alignment = {prog:alignments for prog,alignments in lp_query.all()} #dictionary type {progid: [alignments]}
        return pid_alignment
        
    @classmethod
    def get_manual_override(cls, id: str, alignment_type:str,LookupProgramModel, db_conn):
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT, cls.COA_VALUES, cls.OVERRIDE_TABLE_SESSION).filter(cls.ID == id).first()
        
        # if coa_query[0] is not None:
        #     opt_input = coa_query[0]  
        # else:
        #     opt_input = coa_query[2]
    
        coa = coa_query[1]        
        override_session = coa_query[2]  
        # breakpoint()
        sel_prog_ids = [
            f"{entry['DT_RowId']}" 
            for entry in override_session['coa_output']]

        sel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.JCA,LookupProgramModel.CGA,LookupProgramModel.KOP_KSP).filter(
            LookupProgramModel.ID.in_(sel_prog_ids))
        
        sel_alignment_dict = {
            row.ID: {
                "JCA": row.JCA,
                "CGA": row.CGA,
                "KOP_KSP": row.KOP_KSP
            }
            for row in sel_lp_query
        }
        # sel_jca_alignment = {prog: alignments for prog, alignments in sel_lp_query.all()}


        coa_input = None
        if coa_query[0]:
            coa_input = coa_query[0][0]
        if isinstance(coa_input, dict) and 'ProgramIDs' in coa_input:
            run_prog_ids = [prog_id for prog_id in coa_input['ProgramIDs']]
        else:
            run_prog_ids = []

        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids))

        # breakpoint()

        unsel_lp_query = db_conn.query(
            LookupProgramModel.ID, 
            LookupProgramModel.JCA,
            LookupProgramModel.CGA,
            LookupProgramModel.KOP_KSP).filter(
            LookupProgramModel.ID.in_(unsel_prog_ids))

        unsel_alignment_dict = {
            row.ID: {
                "JCA": row.JCA,
                "CGA": row.CGA,
                "KOP_KSP": row.KOP_KSP
            }
            for row in unsel_lp_query
        }
        alignment = {**sel_alignment_dict, **unsel_alignment_dict}
        
        
        dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
        dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
        unsel_data = LookupProgramModel.get_delta_amount_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)


        sel_data = {}
        for entry in override_session['coa_output']:
            pid = entry["DT_RowId"]
            if pid not in sel_data:
                sel_data[pid] = {}
            
            years = [key for key in entry.keys() if key.isdigit()]

            for year in years:
                if year not in sel_data[pid]:
                    sel_data[pid][year] = 0

                value = entry.get(year, 0)
                
                if value is None:
                    value = 0
                elif isinstance(value, str):
                    try:
                        value = float(value)
                    except ValueError:
                        value = 0  

                sel_data[pid][year] += value

        if not override_session['coa_output']:
            for entry in coa['resource_k']:
                pid = entry["DT_RowId"]
                if pid not in sel_data:
                    sel_data[pid] = {}

                years = [key for key in entry.keys() if key.isdigit()]
                
                for year in years:
                    if year not in sel_data[pid]:
                        sel_data[pid][year] = 0
                    
                    value = entry.get(year, 0)
                    if isinstance(value, str):
                        try:
                            value = float(value)
                        except ValueError:
                            value = 0

                    sel_data[pid][year] += value

        return sel_data, unsel_data, alignment

    @classmethod
    def get_jca_manual_override_v2(cls, id: str, coa_type, LookupProgramModel, db_conn):
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT, cls.COA_VALUES, cls.OVERRIDE_TABLE_SESSION).filter(cls.ID == id).first()
    
        coa = coa_query[1]        
        override_session = coa_query[2]  
        # breakpoint()
        #note: only workable for coa type == "iss"
        # sel_prog_ids = [
        #     f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}" 
        #     for entry in override_session['coa_output'] 
        #     if entry['Program'] and entry['POM SPONSOR'] and entry['CAP SPONSOR'] and entry['ASSESSMENT AREA']
        # ]
        
        # df["ID"] = df["PROGRAM_CODE"]+ "_"+df["POM_SPONSOR_CODE"]+"_"+df["CAPABILITY_SPONSOR_CODE"] +"_"+ \
        # df["ASSESSMENT_AREA_CODE"] + \
        # "_"+df["EXECUTION_MANAGER_CODE"]+"_"+df["RESOURCE_CATEGORY_CODE"] +"_"+df["EOC_CODE"]+\
        # "_"+df["OSD_PROGRAM_ELEMENT_CODE"]
        
        sel_prog_ids = [
            f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
                for entry in override_session['coa_output'] 
                if entry['Program'] and entry['POM SPONSOR'] and entry['CAP SPONSOR'] and entry['ASSESSMENT AREA']
        ]

        sel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.JCA).filter(
            LookupProgramModel.ID.in_(sel_prog_ids))
        sel_jca_alignment = {prog: alignments for prog, alignments in sel_lp_query.all()}


        coa_input = None
        if coa_query[0]:
            coa_input = coa_query[0][0]
        if isinstance(coa_input, dict) and 'ProgramIDs' in coa_input:
            run_prog_ids = [prog_id for prog_id in coa_input['ProgramIDs']]
        else:
            run_prog_ids = []

        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids))

        # breakpoint()

        unsel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.JCA).filter(
            LookupProgramModel.ID.in_(unsel_prog_ids))
        unsel_jca_alignment = {prog: alignments for prog, alignments in unsel_lp_query.all()}

        jca_alignment = {**sel_jca_alignment, **unsel_jca_alignment}
        
        if coa_type == "iss":
            dt_table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtIssueModel)
            unsel_data = LookupProgramModel.get_resource_k_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)
        else: #iss-extract
            dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
            unsel_data = LookupProgramModel.get_delta_amount_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)


        sel_data = {}
        for entry in override_session['coa_output']:
            progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
            if progId not in sel_data:
                sel_data[progId] = {}
            
            years = [key for key in entry.keys() if key.isdigit()]

            for year in years:
                if year not in sel_data[progId]:
                    sel_data[progId][year] = 0

                value = entry.get(year, 0)
                
                if value is None:
                    value = 0
                elif isinstance(value, str):
                    try:
                        value = float(value)
                    except ValueError:
                        value = 0  

                sel_data[progId][year] += value

        if not override_session['coa_output']:
            for entry in coa['resource_k']:
                progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
                if progId not in sel_data:
                    sel_data[progId] = {}

                years = [key for key in entry.keys() if key.isdigit()]
                
                for year in years:
                    if year not in sel_data[progId]:
                        sel_data[progId][year] = 0
                    
                    value = entry.get(year, 0)
                    if isinstance(value, str):
                        try:
                            value = float(value)
                        except ValueError:
                            value = 0

                    sel_data[progId][year] += value

        return sel_data, unsel_data, jca_alignment

    @classmethod
    def get_cga_manual_override(cls, id: str, coa_type, LookupProgramModel, db_conn):
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT, cls.COA_VALUES, cls.OVERRIDE_TABLE_SESSION).filter(cls.ID == id).first()
        # if coa_query[0] is not None:
        #     opt_input = coa_query[0]  
        # else:
        #     opt_input = coa_query[2] 
        coa = coa_query[1]        
        override_session = coa_query[2]  

        sel_prog_ids = [
            f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}" 
            for entry in override_session['coa_output'] 
            if entry['Program'] and entry['POM SPONSOR'] and entry['CAP SPONSOR'] and entry['ASSESSMENT AREA']
        ]
        # breakpoint()
        sel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.CGA).filter(
            LookupProgramModel.ID.in_(sel_prog_ids))
        sel_cga_alignment = {prog: alignments for prog, alignments in sel_lp_query.all()}

        coa_input = None
        if coa_query[0]:
            coa_input = coa_query[0][0]
        if isinstance(coa_input, dict) and 'ProgramIDs' in coa_input:
            run_prog_ids = [prog_id for prog_id in coa_input['ProgramIDs']]
        else:
            run_prog_ids = []

        # run_prog_ids = [prog_id for prog_id in opt_input[0]['ProgramIDs']]
        
        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids))

        unsel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.CGA).filter(
            LookupProgramModel.ID.in_(unsel_prog_ids))
        unsel_cga_alignment = {prog: alignments for prog, alignments in unsel_lp_query.all()}
        
        cga_alignment = {**sel_cga_alignment, **unsel_cga_alignment}
        
        if coa_type == "iss":
            dt_table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtIssueModel)
            unsel_data = LookupProgramModel.get_resource_k_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)
        else: #iss-extract
            dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
            unsel_data = LookupProgramModel.get_delta_amount_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)


        sel_data = {}
        for entry in override_session['coa_output']:
            progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}"
            if progId not in sel_data:
                sel_data[progId] = {}
            
            years = [key for key in entry.keys() if key.isdigit()]

            for year in years:
                if year not in sel_data[progId]:
                    sel_data[progId][year] = 0

                value = entry.get(year, 0)
                
                if value is None:
                    value = 0
                elif isinstance(value, str):
                    try:
                        value = float(value)
                    except ValueError:
                        value = 0  

                sel_data[progId][year] += value

        if not override_session['coa_output']:
            for entry in coa['resource_k']:
                progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}"
                if progId not in sel_data:
                    sel_data[progId] = {}

                years = [key for key in entry.keys() if key.isdigit()]
                
                for year in years:
                    if year not in sel_data[progId]:
                        sel_data[progId][year] = 0
                    
                    value = entry.get(year, 0)
                    if isinstance(value, str):
                        try:
                            value = float(value)
                        except ValueError:
                            value = 0

                    sel_data[progId][year] += value
        
        return sel_data, unsel_data, cga_alignment

    @classmethod
    def get_cga_manual_override_v2(cls, id: str, coa_type, LookupProgramModel, db_conn):
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT, cls.COA_VALUES, cls.OVERRIDE_TABLE_SESSION).filter(cls.ID == id).first()
        coa = coa_query[1]        
        override_session = coa_query[2]  

        sel_prog_ids = [
            f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
                for entry in override_session['coa_output'] 
                if entry['Program'] and entry['POM SPONSOR'] and entry['CAP SPONSOR'] and entry['ASSESSMENT AREA']
        ]
        # breakpoint()
        sel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.CGA).filter(
            LookupProgramModel.ID.in_(sel_prog_ids))
        sel_cga_alignment = {prog: alignments for prog, alignments in sel_lp_query.all()}

        coa_input = None
        if coa_query[0]:
            coa_input = coa_query[0][0]
        if isinstance(coa_input, dict) and 'ProgramIDs' in coa_input:
            run_prog_ids = [prog_id for prog_id in coa_input['ProgramIDs']]
        else:
            run_prog_ids = []
        
        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids))

        unsel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.CGA).filter(
            LookupProgramModel.ID.in_(unsel_prog_ids))
        unsel_cga_alignment = {prog: alignments for prog, alignments in unsel_lp_query.all()}
        
        cga_alignment = {**sel_cga_alignment, **unsel_cga_alignment}
        
        if coa_type == "iss":
            dt_table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtIssueModel)
            unsel_data = LookupProgramModel.get_resource_k_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)
        else: #iss-extract
            dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
            unsel_data = LookupProgramModel.get_delta_amount_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)


        sel_data = {}
        for entry in override_session['coa_output']:
            progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
            if progId not in sel_data:
                sel_data[progId] = {}
            
            years = [key for key in entry.keys() if key.isdigit()]

            for year in years:
                if year not in sel_data[progId]:
                    sel_data[progId][year] = 0

                value = entry.get(year, 0)
                
                if value is None:
                    value = 0
                elif isinstance(value, str):
                    try:
                        value = float(value)
                    except ValueError:
                        value = 0  

                sel_data[progId][year] += value

        if not override_session['coa_output']:
            for entry in coa['resource_k']:
                progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
                if progId not in sel_data:
                    sel_data[progId] = {}

                years = [key for key in entry.keys() if key.isdigit()]
                
                for year in years:
                    if year not in sel_data[progId]:
                        sel_data[progId][year] = 0
                    
                    value = entry.get(year, 0)
                    if isinstance(value, str):
                        try:
                            value = float(value)
                        except ValueError:
                            value = 0

                    sel_data[progId][year] += value
        
        return sel_data, unsel_data, cga_alignment
    
    @classmethod
    def get_kp_manual_override(cls, id: str, coa_type, LookupProgramModel, db_conn):
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT, cls.COA_VALUES, cls.OVERRIDE_TABLE_SESSION).filter(cls.ID == id).first()
        # if coa_query[0] is not None:
        #     opt_input = coa_query[0]  
        # else:
        #     opt_input = coa_query[2]  
        coa = coa_query[1]        
        override_session = coa_query[2]  

        sel_prog_ids = [
            f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}" 
            for entry in override_session['coa_output'] 
            if entry['Program'] and entry['POM SPONSOR'] and entry['CAP SPONSOR'] and entry['ASSESSMENT AREA']
        ]

        sel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.KOP_KSP).filter(
            LookupProgramModel.ID.in_(sel_prog_ids))
        sel_kp_alignment = {prog: alignments for prog, alignments in sel_lp_query.all()}

        coa_input = None
        if coa_query[0]:
            coa_input = coa_query[0][0]
        if isinstance(coa_input, dict) and 'ProgramIDs' in coa_input:
            run_prog_ids = [prog_id for prog_id in coa_input['ProgramIDs']]
        else:
            run_prog_ids = []
        
        
        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids))

        unsel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.KOP_KSP).filter(
            LookupProgramModel.ID.in_(unsel_prog_ids))
        unsel_kp_alignment = {prog: alignments for prog, alignments in unsel_lp_query.all()}

        kp_alignment = {**sel_kp_alignment, **unsel_kp_alignment}
        
        if coa_type == "iss":
            dt_table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtIssueModel)
            unsel_data = LookupProgramModel.get_resource_k_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)
        else: #iss-extract
            dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
            unsel_data = LookupProgramModel.get_delta_amount_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)


        sel_data = {}
        for entry in override_session['coa_output']:
            progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}"
            if progId not in sel_data:
                sel_data[progId] = {}
            
            years = [key for key in entry.keys() if key.isdigit()]

            for year in years:
                if year not in sel_data[progId]:
                    sel_data[progId][year] = 0

                value = entry.get(year, 0)
                
                if value is None:
                    value = 0
                elif isinstance(value, str):
                    try:
                        value = float(value)
                    except ValueError:
                        value = 0  

                sel_data[progId][year] += value

        if not override_session['coa_output']:
            for entry in coa['resource_k']:
                progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}"
                if progId not in sel_data:
                    sel_data[progId] = {}

                years = [key for key in entry.keys() if key.isdigit()]
                
                for year in years:
                    if year not in sel_data[progId]:
                        sel_data[progId][year] = 0
                    
                    value = entry.get(year, 0)
                    if isinstance(value, str):
                        try:
                            value = float(value)
                        except ValueError:
                            value = 0

                    sel_data[progId][year] += value

        return sel_data, unsel_data, kp_alignment

    @classmethod
    def get_kp_manual_override_v2(cls, id: str, coa_type, LookupProgramModel, db_conn):
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT, cls.COA_VALUES, cls.OVERRIDE_TABLE_SESSION).filter(cls.ID == id).first()
        coa = coa_query[1]        
        override_session = coa_query[2]  

        sel_prog_ids = [
            f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
            for entry in override_session['coa_output'] 
            if entry['Program'] and entry['POM SPONSOR'] and entry['CAP SPONSOR'] and entry['ASSESSMENT AREA']
        ]

        sel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.KOP_KSP).filter(
            LookupProgramModel.ID.in_(sel_prog_ids))
        sel_kp_alignment = {prog: alignments for prog, alignments in sel_lp_query.all()}

        coa_input = None
        if coa_query[0]:
            coa_input = coa_query[0][0]
        if isinstance(coa_input, dict) and 'ProgramIDs' in coa_input:
            run_prog_ids = [prog_id for prog_id in coa_input['ProgramIDs']]
        else:
            run_prog_ids = []
        
        
        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids))

        unsel_lp_query = db_conn.query(LookupProgramModel.ID, LookupProgramModel.KOP_KSP).filter(
            LookupProgramModel.ID.in_(unsel_prog_ids))
        unsel_kp_alignment = {prog: alignments for prog, alignments in unsel_lp_query.all()}

        kp_alignment = {**sel_kp_alignment, **unsel_kp_alignment}
        
        if coa_type == "iss":
            dt_table_name = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtIssueModel)
            unsel_data = LookupProgramModel.get_resource_k_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)
        else: #iss-extract
            dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
            dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
            unsel_data = LookupProgramModel.get_delta_amount_from_progIds(dt_orm_table, db_conn, unsel_prog_ids)


        sel_data = {}
        for entry in override_session['coa_output']:
            progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
            if progId not in sel_data:
                sel_data[progId] = {}
            
            years = [key for key in entry.keys() if key.isdigit()]

            for year in years:
                if year not in sel_data[progId]:
                    sel_data[progId][year] = 0

                value = entry.get(year, 0)
                
                if value is None:
                    value = 0
                elif isinstance(value, str):
                    try:
                        value = float(value)
                    except ValueError:
                        value = 0  

                sel_data[progId][year] += value

        if not override_session['coa_output']:
            for entry in coa['resource_k']:
                progId = f"{entry['Program']}_{entry['POM SPONSOR']}_{entry['CAP SPONSOR']}_{entry['ASSESSMENT AREA']}_{entry['EXECUTION MANAGER']}_{entry['RESOURCE CATEGORY']}_{entry['EOC']}_{entry['OSD PE']}"
                if progId not in sel_data:
                    sel_data[progId] = {}

                years = [key for key in entry.keys() if key.isdigit()]
                
                for year in years:
                    if year not in sel_data[progId]:
                        sel_data[progId][year] = 0
                    
                    value = entry.get(year, 0)
                    if isinstance(value, str):
                        try:
                            value = float(value)
                        except ValueError:
                            value = 0

                    sel_data[progId][year] += value

        return sel_data, unsel_data, kp_alignment

    @classmethod
    def get_alignment_from_coa_id(cls,id:str,alignment_type,LookupProgramModel,db_conn):
        """
        This function is to retrieve alignment from optimizer run, non-manual override
        Currently only ussable for IO
        """
        #1675 test
        #id is the primary key of the SAVED COA
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT,cls.COA_VALUES).filter(cls.ID==id).first() #QUERY(CLS) for whole row
        opt_input = coa_query[0]
        coa = coa_query[1] #a list type, saved coa
        #selected programs processing
        sel_prog_ids = [prog_id['program_id'] for prog_id in opt_input[1]['model']['selected_programs']]
        sel_alignment = cls.get_alignment_space(LookupProgramModel,alignment_type,sel_prog_ids,db_conn)
        
        #unselected programs processing
        run_prog_ids = [prog_id for prog_id in opt_input[0]['ProgramIDs']] #total prog ids
        unsel_prog_ids = list(set(run_prog_ids) - set(sel_prog_ids)) #exceptions
        unsel_alignment = cls.get_alignment_space(LookupProgramModel,alignment_type,unsel_prog_ids,db_conn) #dictionary type {progid: [alignments]}
        
        alignment = {**sel_alignment,**unsel_alignment} #total alignment space (selected + unselected)

        dt_table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
        dt_orm_table = create_dynamic_table_class(dt_table_name,DtISSExtractModel)
        unsel_data = dt_orm_table.get_delta_amt_by_pids(unsel_prog_ids,db_conn)
        
        #we return the 'resource_k' instead of the 'selected programs' because of partial fitting for programs. Funding will change!
        #reformat the coa from {year:{prog_id:val}} into {prog_id:{year:val}}
        sel_data = {}
        for year, entries in coa['resource_k'].items():
            for progId, val in entries.items():
                if progId not in sel_data:
                    sel_data[progId] = {}
                sel_data[progId][year] = val

        return sel_data, unsel_data, alignment


    @classmethod
    def get_alignment_from_coa_id_v2(cls,id:str,alignment_type,LookupProgramModel,db_conn):
        """
        This function is to retrieve alignment from optimizer run, non-manual override
        Currently only usable for RC
        """
        #1675 test
        #id is the primary key of the SAVED COA
        coa_query = db_conn.query(cls.OPTIMIZER_INPUT,cls.COA_VALUES).filter(cls.ID==id).first() #QUERY(CLS) for whole row
        opt_input = coa_query[0]
        #selected programs processing
        sel_prog_ids = [prog_id['program_id'] for prog_id in opt_input[1]['model']['selected_programs']]
        sel_alignment = cls.get_alignment_space(LookupProgramModel,alignment_type,sel_prog_ids,db_conn) #dictionary type {progid: [alignments]}

        #unselected programs processing, note for iss: any program can be both in unselected (funding) and selected (funding) due to cutting nature
        unsel_prog_ids = [prog_id['program_id'] for prog_id in opt_input[1]['model']['cutting_programs']]
        unsel_alignment = cls.get_alignment_space(LookupProgramModel,alignment_type,unsel_prog_ids,db_conn) #dictionary type {progid: [alignments]}
        
        alignment = {**sel_alignment,**unsel_alignment} #total alignment space (selected + unselected)

        #since all our cut/fund data are within the optimizer run already, simply parse {id:{resource_k}}
        sel_data = {d['program_id']:d['resource_k'] for d in opt_input[1]['model']['selected_programs']} 
        unsel_data = {d['program_id']:d['resource_k'] for d in opt_input[1]['model']['cutting_programs']}

        return sel_data, unsel_data, alignment
    

    @classmethod    
    def get_opt_run_data_from_coa_id(cls,coa_ids,db_conn):
        data = db_conn.query(cls.ID,cls.COA_VALUES,cls.OPTIMIZER_INPUT,cls.OVERRIDE_TABLE_SESSION).filter(cls.ID.in_(coa_ids)).all()
        if len(data) != len(coa_ids):
            raise HTTPException(404,"not all coa ids are found in the database. Please double check inputs")
        result = {}
        for id,coa_values,optimizer_input, override_session in data:
            if coa_values: #non-merged coas
                result[id] = {'opt-run':coa_values['resource_k'],
                            'manual_session':override_session,
                            'all_programs':optimizer_input[0]['ProgramIDs']
                            }
                if override_session:
                    new_rows_ids = override_session['ProgramIDs']
                    result[id]["all_programs"].extend(new_rows_ids)

            else: #merged coas
                result[id] = {'opt-run':None,
                            'manual_session':override_session,
                            'all_programs':override_session['ProgramIDs']
                            }
        return result
            