from typing import List

from sqlalchemy import text
from fastapi import HTTPException

from socom.metadata import get_cap_sponsor_category

from rds.table_model.socom.DtPBComparison import DtPBComparison
from rds.table_model.socom.DtBudgetExecution import DtBudgetExecution
from rds.table_model.socom.DtAMSFEM import DtAMSFEM
from rds.table_model.base_model import (
    SCHEMA,
)


def inflation_adjustment(curr_money,rate,start_year,end_year):
    if int(end_year) >= int(start_year):
        adj_money = curr_money*(1 + rate)**abs(int(end_year) - int(start_year))
    else:
        adj_money = curr_money/(1 + rate)**abs(int(end_year) - int(start_year))
    return round(adj_money,0)

async def get_pb_comparison_agg(model,groupby,inflation_adj,db_conn):
    query = text("""
        SELECT COLUMN_NAME 
        FROM information_schema.columns 
        WHERE table_name = :table_name AND table_schema = :schema_name;
    """)
    all_cols = db_conn.execute(query, {"table_name": "DT_PB_COMPARISON","schema_name":SCHEMA})
    pb_cols = [col[0] for col in all_cols if col[0].startswith("PB")]
    max_pb = max([2000+int(col.removeprefix("PB")) for col in pb_cols]) #convert["PBYY"...] and retrieve max = 2025 ie
    data = DtPBComparison.aggregate_pb_sums(model,groupby,pb_cols,db_conn=db_conn)

    if not data:
        raise HTTPException(404,"Data not found with the inputs params")

    for d in data.copy():
        #assign component
        if "CAPABILITY_SPONSOR_CODE" in d:
            d["CAPABILITY_CATEGORY"] = get_cap_sponsor_category(d["CAPABILITY_SPONSOR_CODE"])

        #adjust for 2% inflation
        if inflation_adj:
            PB_set = [k for k in d.keys() if "PB" in k]
            PB_years = {s:int("20"+s.removeprefix("PB")) for s in PB_set}

            for pb_year in PB_set:
                start_year = d["FISCAL_YEAR"] #FISCAL:2020
                # end_year = PB_years[pb_year] #PB16
                end_year = max_pb
                # print(start_year,d[pb_year])
                #reference money = now, adjust future dollars in today's today. PB16 value in 2020 will be less with inflation
                #note: use the print statement to debug if later pb year money is higher by ~years diff * 2% (reference in today's dollars)
                d[pb_year] = inflation_adjustment(float(d[pb_year]),0.02,start_year,end_year)
                # print(d[pb_year])
                
    return data


async def get_budget_exec_agg(model,groupby,inflation_adj,db_conn):
    query = text("""
        SELECT COLUMN_NAME 
        FROM information_schema.columns 
        WHERE table_name = :table_name AND table_schema = :schema_name;
    """)
    all_cols = db_conn.execute(query, {"table_name": "DT_PB_COMPARISON","schema_name":SCHEMA})
    all_cols = [row[0] for row in all_cols] 
    pb_cols = [col for col in all_cols if "PB" in col and len(col) == 4]
    max_pb = max([2000+int(col.removeprefix("PB")) for col in pb_cols]) #convert["PBYY"...] and retrieve max = 2025 ie

    data = DtBudgetExecution.aggregate_sums(model,groupby,db_conn)

    for d in data.copy():
        if "CAPABILITY_CATEGORY" in d:
            d["CAPABILITY_CATEGORY"] = get_cap_sponsor_category(d["CAPABILITY_SPONSOR_CODE"])
        if inflation_adj:
            
            start_year = max_pb
            end_year = d["FISCAL_YEAR"]
            # print(start_year,end_year)
            #reference money = now, adjust past dollars in today's today. should be higher than current SUM_ACTUALS/ect.. dollars
            #currently on the DT table
            #note: use the print statement to debug if later pb year money is lower by ~years diff * 2%
            # print(d["SUM_ACTUALS"])
            d["SUM_ACTUALS"] = inflation_adjustment(float(d["SUM_ACTUALS"]),0.02,end_year,start_year)
            d["SUM_ENT"] = inflation_adjustment(float(d["SUM_ENT"]),0.02,end_year,start_year)
            d["SUM_PB"] = inflation_adjustment(float(d["SUM_PB"]),0.02,end_year,start_year)
            # print(d["SUM_ACTUALS"])       

    return data