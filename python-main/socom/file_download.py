from api.models import CritScoreDownloadInput
from api.internal.resources import (
    create_dynamic_table_class,
    ResourceConstraintCOATableSet,
    IssSummaryTableSet
)

from typing import List
import pandas as pd
import numpy as np

from rds.table_model.socom.DtIssModel import DtIssueModel
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel
from rds.table_model.socom.UsrLookupUserCriteriaTerms import UsrLookupUserCriteriaTerms
from rds.table_model.socom.UsrLookupUserCriteriaName import UsrLookupUserCriteriaName


from fastapi import HTTPException


def get_prog_scores_excel(dt_orm_model,acc_list: List, prog_group_list: List, db_conn):
        from sqlalchemy import func
        
        event_col = getattr(dt_orm_model,"EVENT_NAME",None)
        rk_col = getattr(dt_orm_model,"RESOURCE_K",None)
        selected_cols = [dt_orm_model.PROGRAM_CODE,
                         dt_orm_model.POM_SPONSOR_CODE,
                         dt_orm_model.CAPABILITY_SPONSOR_CODE,
                         dt_orm_model.ASSESSMENT_AREA_CODE,
                         dt_orm_model.EXECUTION_MANAGER_CODE,
                         dt_orm_model.RESOURCE_CATEGORY_CODE,
                         dt_orm_model.EOC_CODE,
                         dt_orm_model.OSD_PROGRAM_ELEMENT_CODE]
        
        if event_col: selected_cols.append(event_col)

        query = db_conn.query(*selected_cols).group_by(dt_orm_model.PROGRAM_ID)

        if event_col:
            query = query.having(func.sum(dt_orm_model.DELTA_AMT) > 0)

        elif rk_col:
            query = query.having(func.sum(dt_orm_model.RESOURCE_K) > 0)

        # Apply filters only if lists are non-empty
        if acc_list and len(acc_list) > 0:
            query = query.filter(dt_orm_model.ASSESSMENT_AREA_CODE.in_(acc_list))
        
        if prog_group_list and len(prog_group_list) > 0:
            query = query.filter(dt_orm_model.PROGRAM_GROUP.in_(prog_group_list))

        #ensure null values are not breaking
        data = [ "_".join(row) for row in query.all()]
        return data

def download_prog_score_excel(model:CritScoreDownloadInput,db_conn):
    if model.TYPE_OF_COA == "ISS_EXTRACT":
        dt_table = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
        orm_model = create_dynamic_table_class(dt_table,DtISSExtractModel)
    else:
        dt_table = ResourceConstraintCOATableSet.CURRENT["ISS"][0]
        orm_model = create_dynamic_table_class(dt_table,DtIssueModel)
    
    if model.TYPE_OF_COA not in ["ISS","ISS_EXTRACT"]:
        raise HTTPException(422,"TYPE_OF_COA must be 'ISS' or 'ISS_EXTRACT'")
    prog_ids = get_prog_scores_excel(orm_model,model.assessment_area_code,model.program_group,db_conn)

    if not prog_ids:
        raise HTTPException(404,"no program ids found for the attached assessment area code and program group")
    
    crit_terms = UsrLookupUserCriteriaTerms.get_crit_terms_from_cycle_id(UsrLookupUserCriteriaName,cycle_id=model.cycle_id,db_conn=db_conn)
    if not crit_terms:
        raise HTTPException(404,f"no criteria terms found for the cycle id: {model.cycle_id}")

    df = pd.DataFrame(index=prog_ids, columns=crit_terms+["Title","Description"])
    # breakpoint()
    df.index.name = "Weighting Criteria"
    df = df.reset_index()
    return df

