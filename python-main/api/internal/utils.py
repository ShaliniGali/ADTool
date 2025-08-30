from sqlalchemy import select, func
import hashlib
import pandas as pd
from collections import defaultdict
LIST_JOIN_FORMAT = '"{0}"'


_true_set = {'yes', 'true', 't', 'y', '1'}
_false_set = {'no', 'false', 'f', 'n', '0'}


def str2bool(value, raise_exc=False):
    if isinstance(value, str):
        value = value.lower()
        if value in _true_set:
            return True
        if value in _false_set:
            return False
    if raise_exc:
        raise ValueError('Expected "%s"' % '", "'.join(_true_set | _false_set))
    return None



def transform_pid_to_unhash(pid_list,db_conn):
    from rds.table_model.socom.LookupProgramDetail import LookupProgramModel
    stmt = (
        select(
            LookupProgramModel.ID,
            func.concat_ws(
                '_',
                LookupProgramModel.PROGRAM_CODE,
                LookupProgramModel.POM_SPONSOR_CODE,
                LookupProgramModel.CAPABILITY_SPONSOR_CODE,
                LookupProgramModel.ASSESSMENT_AREA_CODE,
                LookupProgramModel.EXECUTION_MANAGER_CODE,
                LookupProgramModel.RESOURCE_CATEGORY_CODE,
                LookupProgramModel.EOC_CODE,
                LookupProgramModel.OSD_PROGRAM_ELEMENT_CODE,
                LookupProgramModel.EVENT_NAME
            ).label("UNHASHED_ID")
        )
        .where(LookupProgramModel.ID.in_(pid_list))
    )    
    result = db_conn.execute(stmt).mappings().all()
    return {row["ID"]:row["UNHASHED_ID"] for row in result}


def generate_hash_pid(pid:str):
    # encoding string using encode() then sending to md5()
    result = hashlib.sha512(pid.encode())
    return result.hexdigest()




class ExtractTransformer:

    @classmethod
    def transform_budget_extract(cls,df, created_by=0, updated_by=None) -> pd.DataFrame:
        static_columns = [
            "Event Number", "Event Type", "Event Title", "Event Justification",
            "Assessment Area Code", "POM Sponsor Code", "Program Group", "Program Code",
            "EOC Code", "Special Project Code", "OSD Program Element Code",
            "Budget Activity Code", "Budget Activity Name", "SAG Code", "SAG Name",
            "Budget Subactivity Name", "Execution Manager", "Capability Sponsor",
            "Line Item", "RDTE Project", "Resource Category Code"
        ]

        suffix_map = {
            "base": "RESOURCE_K",
            "prop": "PROP_AMT",
            "delta": "DELTA_AMT",
            "o2b": "O2B_AMT",
            "prop o2b": "PROP_O2B_AMT",
            "delta o2b": "DELTA_O2B_AMT"
        }

        df1 = df.copy()
        df1["KEY"] = df1[static_columns].astype(str).agg('|'.join, axis=1)
        dynamic_cols = [col for col in df1.columns if col not in static_columns and col != "KEY"]

        output_rows = []

        for i in range(len(df1)):
            row = df1.iloc[i]
            static_data = row[static_columns].to_dict()

            # Group funding columns by fiscal year
            year_data = defaultdict(dict)

            for col in dynamic_cols:
                if pd.isna(row[col]):
                    continue

                try:
                    year, fund_type = col.strip().split(" ", 1)
                    fund_type = fund_type.lower()
                    if fund_type in suffix_map:
                        suffix_col = suffix_map[fund_type]
                        year_data[year][suffix_col] = row[col]
                except ValueError:
                    continue  # Skip malformed columns

            for year, values in year_data.items():
                new_row = {
                    **static_data,
                    "FISCAL_YEAR": int(year),
                    "CREATED_BY": created_by,
                    "UPDATED_BY": updated_by
                }
                new_row.update(values)
                output_rows.append(new_row)

        output_df = pd.DataFrame(output_rows)
        dynamic_col_refined = list(suffix_map.values())
        output_df[dynamic_col_refined] = output_df[dynamic_col_refined].fillna(0.0)
        return output_df


    @classmethod
    def pre_upsert_process(cls,df,user_id,pom_position=None):
        df = cls.transform_budget_extract(df, created_by=user_id, updated_by=None)
        mapper = {
        'Event Number':"EVENT_NUMBER",
            'Event Type':"EVENT_TYPE", 
            'Event Title':"EVENT_TITLE", 
            'Event Justification':"EVENT_JUSTIFICATION",
        'Assessment Area Code':"ASSESSMENT_AREA_CODE", 
            'POM Sponsor Code':"POM_SPONSOR_CODE", 
            'Program Group':"PROGRAM_GROUP",
        'Program Code':"PROGRAM_CODE", 
            'EOC Code':"EOC_CODE", 
            'Special Project Code':"SPECIAL_PROJECT_CODE",
        'OSD Program Element Code':"OSD_PROGRAM_ELEMENT_CODE", 
            'Budget Activity Code':"BUDGET_ACTIVITY_CODE",
        'Budget Activity Name':"BUDGET_ACTIVITY_NAME", 
            'SAG Code':"SUB_ACTIVITY_GROUP_CODE", 
            'SAG Name':"SUB_ACTIVITY_GROUP_NAME",
        'Budget Subactivity Name':"BUDGET_SUB_ACTIVITY_NAME", 
            'Execution Manager':"EXECUTION_MANAGER_CODE", 
            'Capability Sponsor':"CAPABILITY_SPONSOR_CODE",
        'Line Item':"LINE_ITEM_CODE", 
            'RDTE Project':"RDTE_PROJECT_CODE", 
            'Resource Category Code':"RESOURCE_CATEGORY_CODE"
        }
        df.rename(columns=mapper,inplace=True)
        df["CREATED_BY"] = 1
        cols_to_fill = ['RESOURCE_K', 'PROP_AMT', 'DELTA_AMT',
                    'O2B_AMT', 'PROP_O2B_AMT', 'DELTA_O2B_AMT']

        df[cols_to_fill] = df[cols_to_fill].fillna(0)
        df["EVENT_NAME"] = df.apply(
            lambda row: f"{row['CAPABILITY_SPONSOR_CODE']} {row['EVENT_TYPE']} {row['EVENT_NUMBER']}",
            axis=1
        )
        #add default columns
        default_map = {"BUDGET_SUB_ACTIVITY_CODE":None, 
            "DELTA_OCO_AMT":None, 
            "EVENT_DATE": None, 
            "EVENT_STATUS":None, "EVENT_STATUS_COMMENT":None, 
            "EVENT_USER":None, "OCO_AMT":0, "POM_POSITION_CODE":pom_position, "PROP_OCO_AMT":0,
            "CREATED_BY":user_id,}
        for col,val in default_map.items():
            df[col] = val
        return df