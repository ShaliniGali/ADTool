import os
import pandas as pd
from sqlalchemy.orm import Session
from upload_pipelines.lib.validation import DTTableValidator
from upload_pipelines.lib.db_connection import create_db_session  
from rds.table_model.base_model import Base

SCHEMA = os.getenv("SOCOM_UI", "SOCOM_UI")
TABLE_NAME = os.getenv("TABLE_NAME", None)

def get_all_table_names():
    return list(Base.metadata.tables.keys())

def fetch_data_from_table(session: Session, table_name):
    model = Base.metadata.tables.get(table_name)
    if not model:
        print(f"Error: Table '{table_name}' does not exist in metadata.")
        return pd.DataFrame()

    try:
        result = session.execute(f"SELECT * FROM {SCHEMA}.{table_name}")
        df = pd.DataFrame(result.fetchall(), columns=result.keys())

        if df.empty:
            print(f"No data found in table {table_name}.")
            return pd.DataFrame()
        
        return df

    except Exception as e:
        print(f"Error fetching data from {table_name}: {e}")
        return pd.DataFrame()

def validate_table(session, table_name, total_tables, current_index):
    df = fetch_data_from_table(session, table_name)
    if df.empty:
        print(f"{table_name}: No data available for validation.")
        return

    print(f"Validating table '{table_name}' ({current_index}/{total_tables})...")

    db_columns = DTTableValidator.get_columns_from_db(session, table_name, SCHEMA)
    if not db_columns:
        print(f"{table_name}: Error: Unable to fetch columns.")
        return

    int_columns = ["DELTA_AMT", "DELTA_O2B_AMT", "DELTA_OCO_AMT", "FISCAL_YEAR", "O2B_AMT", "OCO_AMT", "PROP_AMT", 
                   "PROP_O2B_AMT", "PROP_OCO_AMT", "RESOURCE_K", "SUM_ACTUALS", "SUM_ENT", "SUM_PB", "POM_ID", "AD_USER_ID", "ID"]

    smallint_columns = ["FISCAL_YEAR", "SPECIAL_PROJECT_CODE"]
    tinyint_columns = ["IS_DELETED"]

    str_columns = ["ASSESSMENT_AREA_CODE", "BUDGET_ACTIVITY_CODE", "BUDGET_ACTIVITY_NAME", "BUDGET_SUB_ACTIVITY_CODE", 
                   "BUDGET_SUB_ACTIVITY_NAME", "CAPABILITY_SPONSOR_CODE", "EOC_CODE", "EVENT_JUSTIFICATION", 
                   "EVENT_NAME", "EVENT_STATUS", "EVENT_STATUS_COMMENT", "EVENT_TITLE", "EVENT_TYPE", "EVENT_USER",
                   "EXECUTION_MANAGER_CODE", "LINE_ITEM_CODE", "OSD_PROGRAM_ELEMENT_CODE", "POM_POSITION_CODE", 
                   "POM_SPONSOR_CODE", "PROGRAM_CODE", "PROGRAM_GROUP", "RDTE_PROJECT_CODE", "RESOURCE_CATEGORY_CODE", 
                   "SUB_ACTIVITY_GROUP_CODE", "SUB_ACTIVITY_GROUP_NAME", "AD_COMMENT", "PROGRAM_ID", "EOC_CODE", "EVENT_ID"]

    is_valid = DTTableValidator.validate_all(
        df,
        int_columns=int_columns + smallint_columns + tinyint_columns,
        str_columns=str_columns,
        max_length=255,
        required_columns=["id", "name", "budget"],
        unique_columns=["id"]
    )

    result_msg = f"{table_name}: Validation {'Passed' if is_valid else 'Failed'}"
    print(result_msg)

    progress = (current_index / total_tables) * 100
    print(f"Progress: {progress:.2f}% completed\n")

def main():
    session = create_db_session("SOCOM_UI") 

    try:
        if TABLE_NAME:
            table_names = [TABLE_NAME]
        else:
            table_names = get_all_table_names()

        total_tables = len(table_names)
        if total_tables == 0:
            print("No tables found for validation.")
            return

        for index, table_name in enumerate(table_names, start=1):
            validate_table(session, table_name, total_tables, index)

    finally:
        session.close()

if __name__ == "__main__":
    main()
