import pandas as pd
import numpy as np
import os
from io import BytesIO
from sqlalchemy import text
import time

import tenacity

from rds.table_model.base_model import SCHEMA
from rds.table_model.socom.UsrDtUploads import UsrDtUploads
from rds.table_model.socom.UsrDtLookupMetadata import UsrDtLookupMetadata
from rds.table_model.socom.UsrDtScheduler import UsrDtScheduler

from rds.table_model.socom.DtIssExtractDirtyModel import DtISSExtractDirtyModel
from rds.table_model.socom.DtZbtExtractDirtyModel import DtZBTExtractDirtyModel

from upload_pipelines.lib.conn import (
    SoComMinioSetting,
    SoComAWSSetting,
    get_s3_client,
)
from minio import Minio
from api.internal.utils import ExtractTransformer
import re

from contextlib import contextmanager
from fastapi import HTTPException
from api.internal.conn import get_socom_session
from api.internal.utils import generate_hash_pid
from sqlalchemy.dialects.mysql import INTEGER, VARCHAR, SMALLINT
from sqlalchemy.exc import ProgrammingError
from sqlalchemy import inspect
from sqlalchemy import inspect, text, Table, MetaData
from sqlalchemy.exc import SQLAlchemyError

from api.internal.resources import (
    ZbtSummaryTableSet, 
    IssSummaryTableSet,
    create_dynamic_table_class,
)



@contextmanager
def background_db_session():
    db = next(get_socom_session())
    try:
        yield db
    finally:
        db.close()

def check_index_exists(db_conn, table_name, index_name):
    query = text("""
        SELECT COUNT(*)
        FROM information_schema.statistics
        WHERE table_schema = :schema
          AND table_name = :table
          AND index_name = :index
    """)
    result = db_conn.execute(query, {
        "schema": SCHEMA,
        "table": table_name,
        "index": index_name
    }).scalar()
    return result > 0

def create_index_if_missing(db_conn, table_name, column):
    index_name = f"idx_{table_name.lower()}_{column.lower()}"
    if not check_index_exists(db_conn, table_name, index_name):
        sql = f"CREATE INDEX {index_name} ON {SCHEMA}.{table_name} ({column});"
        with db_conn.get_bind().connect() as conn:
            conn.execute(text(sql))
    

def parse_header_columns(df):
    df.columns = [re.sub(r'[^\x00-\x7f]', r'', x) for x in list(df.columns)]
    df.columns = [re.sub(r'[ \s]', r'_', x) for x in list(df.columns)]
    df.columns = [re.sub(r'\$', r'', x) for x in list(df.columns)]
    df.columns = [x.upper() for x in list(df.columns)]
    df.columns = [x.replace("/","_") for x in df.columns]
    df.columns = [x.replace("$","_") for x in df.columns]
    df.rename(columns={"ASSESSMENT_AREA":"ASSESSMENT_AREA_CODE","EXECUTION_MANAGER":"EXECUTION_MANAGER_CODE"},inplace=True)
    return df

def get_file_stream(s3_client, bucket, s3_path):
    if isinstance(s3_client, Minio):
        response = s3_client.get_object(bucket, s3_path)
        content = response.read()
        response.close()
        response.release_conn()
    else:
        response = s3_client.get_object(Bucket=bucket, Key=s3_path)
        content = response["Body"].read()

    return BytesIO(content)


@tenacity.retry(
    stop=tenacity.stop_after_attempt(3),           # Retry up to 3 times
    wait=tenacity.wait_fixed(5),                   # Wait 10 seconds between attempts
    retry=tenacity.retry_if_exception_type(Exception),  # Retry on *any* exception
    reraise=True                          # Reraise the last exception if all retries fail
)
def check_rds_column_exist(
    table_name: str,
    col_name: str,
    add_col: bool = False,
    datatype: str = "INTEGER"
) -> bool:
    """
    Check if a column exists in a table and optionally add it.
    
    Parameters:
    - table_name: name of the table
    - col_name: column to check
    - add_col: if True, add the column if missing
    - datatype: SQL datatype to use if adding the column
    
    Returns:
    - True if column exists (either initially or after being added)
    """
    with background_db_session() as db_conn:
        inspector = inspect(db_conn.bind)
        existing_columns = [col['name'].lower() for col in inspector.get_columns(table_name, schema=SCHEMA)]

        if col_name.lower() not in existing_columns and add_col:
            try:
                db_conn.execute(text(f"""
                    ALTER TABLE {SCHEMA}.{table_name}
                    ADD COLUMN {col_name} {datatype}
                """))
                db_conn.commit()
            except SQLAlchemyError as e:
                db_conn.rollback()
                raise RuntimeError(f"Failed to add column '{col_name}': {e}")
            
            # Refresh the table metadata using reflection
            meta = MetaData()
            meta.reflect(bind=db_conn, schema=SCHEMA, only=[table_name])

        # Final check after potential ALTER
        inspector = inspect(db_conn.bind)
        updated_columns = [col['name'].lower() for col in inspector.get_columns(table_name, schema=SCHEMA)]
        return col_name.lower() in updated_columns


def get_df_from_s3(row_id,db_conn):
    BUCKET = os.getenv("SOCOM_S3_BUCKET")
    use_minio = os.getenv("USE_MINIO", "0") == "1"
    setting = SoComMinioSetting() if use_minio else SoComAWSSetting()
    s3_client = get_s3_client(setting)

    row = UsrDtUploads.get_file_download_metadata(row_id, db_conn)
    s3_path = row['S3_PATH']
    file_stream = get_file_stream(s3_client, BUCKET, s3_path)

    if s3_path.endswith(".csv"):
        df = pd.read_csv(file_stream)
    elif s3_path.endswith(".xlsx"):
        df = pd.read_excel(file_stream)
    else:
        raise HTTPException(422, detail="file extension must be .csv or .xlsx")
    return df,row

@tenacity.retry(
    stop=tenacity.stop_after_attempt(3),           # Retry up to 3 times
    wait=tenacity.wait_fixed(10),                   # Wait 10 seconds between attempts
    retry=tenacity.retry_if_exception_type(Exception),  # Retry on *any* exception
    reraise=True                          # Reraise the last exception if all retries fail
)
def upload_dt_position_tables(row_id, user, redis):
    with background_db_session() as db_conn:
        df,row_meta = get_df_from_s3(row_id,db_conn)
        df = parse_header_columns(df)
        
        dtypes = {
            'PROGRAM_ID': VARCHAR(128),
            'ADJUSTMENT_K': INTEGER(),
            'ASSESSMENT_AREA_CODE': VARCHAR(1),
            'BASE_K': INTEGER(),
            'BUDGET_ACTIVITY_CODE': VARCHAR(1),
            'BUDGET_ACTIVITY_NAME': VARCHAR(30),
            'BUDGET_SUB_ACTIVITY_CODE': VARCHAR(2),
            'BUDGET_SUB_ACTIVITY_NAME': VARCHAR(60),
            'CAPABILITY_SPONSOR_CODE': VARCHAR(13),
            'END_STRENGTH': INTEGER(),
            'EOC_CODE': VARCHAR(15),
            'EVENT_JUSTIFICATION': VARCHAR(500),
            'EVENT_NAME': VARCHAR(60),
            'EXECUTION_MANAGER_CODE': VARCHAR(13),
            'FISCAL_YEAR': SMALLINT(),
            'LINE_ITEM_CODE': VARCHAR(13),
            'OCO_OTHD_ADJUSTMENT_K': INTEGER(),
            'OCO_OTHD_K': INTEGER(),
            'OCO_TO_BASE_K': INTEGER(),
            'OSD_PROGRAM_ELEMENT_CODE': VARCHAR(10),
            'POM_POSITION_CODE': VARCHAR(9),
            'POM_SPONSOR_CODE': VARCHAR(13),
            'PROGRAM_CODE': VARCHAR(11),
            'PROGRAM_GROUP': VARCHAR(24),
            'RDTE_PROJECT_CODE': VARCHAR(9),
            'RESOURCE_CATEGORY_CODE': VARCHAR(8),
            'RESOURCE_K': INTEGER(),
            'SPECIAL_PROJECT_CODE': SMALLINT(),
            'SUB_ACTIVITY_GROUP_CODE': VARCHAR(9),
            'SUB_ACTIVITY_GROUP_NAME': VARCHAR(60),
            'WORK_YEARS': INTEGER()
        }

        df = df[[col for col in dtypes if col in df.columns]]

        # Create PROGRAM_ID
        cols_to_concat = [
            'PROGRAM_CODE', 'POM_SPONSOR_CODE', 'CAPABILITY_SPONSOR_CODE',
            'ASSESSMENT_AREA_CODE', 'EXECUTION_MANAGER_CODE',
            'RESOURCE_CATEGORY_CODE', 'EOC_CODE', 'OSD_PROGRAM_ELEMENT_CODE'
        ]
        df['PROGRAM_ID'] = df.reindex(columns=cols_to_concat).fillna('').agg('_'.join, axis=1)
        df['PROGRAM_ID'] = df['PROGRAM_ID'].map(generate_hash_pid)
        all_cols = list(dtypes.keys())
        
        df = df[[col for col in all_cols if col in df.columns]] #reordering for upload

        df.to_sql(
            name=row_meta["TABLE_NAME"],
            schema=SCHEMA,
            if_exists="replace",
            dtype=dtypes,
            index=False,
            con=db_conn.bind,
            chunksize=200
        )

        # Deactivate old metadata
        records = db_conn.query(UsrDtLookupMetadata).filter(
            UsrDtLookupMetadata.TABLE_NAME == row_meta["TABLE_NAME"],
            UsrDtLookupMetadata.IS_ACTIVE == 1
        ).all()
        for record in records:
            record.IS_ACTIVE = 0            

        # Mark new metadata active
        active_record = db_conn.query(UsrDtLookupMetadata).filter(
            UsrDtLookupMetadata.USR_DT_UPLOAD_ID == row_id
        ).first()
        
        active_record.IS_ACTIVE = 1

        # Finalize
        db_conn.commit()
        db_conn.refresh(active_record)


        # Add indexes, need time for the database to update statistics prior to running this
        time.sleep(5)
        columns = ["PROGRAM_ID", "EOC_CODE", "PROGRAM_CODE", "PROGRAM_GROUP",
                    "CAPABILITY_SPONSOR_CODE", "POM_SPONSOR_CODE", "ASSESSMENT_AREA_CODE"]
        
        for col in columns:
            if col in df.columns:
                create_index_if_missing(db_conn, row_meta["TABLE_NAME"], col)

        msg_id = redis.xadd("SOCOM::DT_UPLOADS::NOTIF", {
            "message": f"table {row_meta['TABLE_NAME']} uploaded by {user}!"
        }, maxlen=100)
        print(f"Table {row_meta['TABLE_NAME']} uploaded successfully!")

        redis.delete("SOCOM::DT_UPLOAD::LOCK") #release lock
        return msg_id


class PbValidator:
    @staticmethod
    def check_pb_year_col(df_pb,year):
        
        if df_pb is None or df_pb.empty:
            return False
        cols = df_pb.columns
        pbyy = "PB"+str(year).replace("20","") #PB27 for example
        
        return pbyy in cols
    

class OOPDbParser:
    @staticmethod
    @tenacity.retry(
    stop=tenacity.stop_after_attempt(3),           # Retry up to 3 times
    wait=tenacity.wait_fixed(10),                   # Wait 10 seconds between attempts
    retry=tenacity.retry_if_exception_type(Exception),  # Retry on *any* exception
    reraise=True                          # Reraise the last exception if all retries fail
)
    def parse_pb_comparison(df_pb):
        with background_db_session() as db_conn:
            dtypes = {
                'ASSESSMENT_AREA_CODE': VARCHAR(1),
                'CAPABILITY_SPONSOR_CODE': VARCHAR(13),
                'EOC_CODE': VARCHAR(15),
                'EXECUTION_MANAGER_CODE': VARCHAR(13),
                'FISCAL_YEAR': SMALLINT(),
                'OSD_PROGRAM_ELEMENT_CODE': VARCHAR(10),
                'PROGRAM_CODE': VARCHAR(11),
                'PROGRAM_GROUP': VARCHAR(24),
                'RESOURCE_CATEGORY_CODE': VARCHAR(8),
            }
            
            pb_cols = [col for col in df_pb.columns if col.startswith("PB")]
            for pb in pb_cols:
                dtypes[pb] = INTEGER()
            db_col_order = ["ASSESSMENT_AREA_CODE","CAPABILITY_SPONSOR_CODE","EOC_CODE","EXECUTION_MANAGER_CODE","FISCAL_YEAR","OSD_PROGRAM_ELEMENT_CODE",
                            "PROGRAM_CODE","PROGRAM_GROUP","RESOURCE_CATEGORY_CODE"] + pb_cols
            
            df_pb = df_pb.reindex(sorted(df_pb.columns),axis=1) 
        
            df_pb[db_col_order].to_sql(
                name="DT_PB_COMPARISON",
                schema=SCHEMA,
                if_exists="replace",
                dtype=dtypes,
                index=False,
                con=db_conn.bind,
                chunksize=200
            )

        with background_db_session() as db_conn:
            columns = ["ASSESSMENT_AREA_CODE", "CAPABILITY_SPONSOR_CODE", "EOC_CODE", "EXECUTION_MANAGER_CODE",
                "OSD_PROGRAM_ELEMENT_CODE", "PROGRAM_GROUP"]

            for col in columns:
                if col in df_pb.columns:
                    create_index_if_missing(db_conn,"DT_PB_COMPARISON", col)

    @staticmethod
    @tenacity.retry(
        stop=tenacity.stop_after_attempt(3),           
        wait=tenacity.wait_fixed(10),                  
        retry=tenacity.retry_if_exception_type(Exception),
        reraise=True
    )
    def parse_budget_execution(df_be):
        with background_db_session() as db_conn:
            dtypes = {
                'ASSESSMENT_AREA_CODE': VARCHAR(1),
                'CAPABILITY_SPONSOR_CODE': VARCHAR(13),
                'EOC_CODE': VARCHAR(15),
                'EXECUTION_MANAGER_CODE': VARCHAR(13),
                'FISCAL_YEAR': SMALLINT(),
                'OSD_PROGRAM_ELEMENT_CODE': VARCHAR(10),
                'PROGRAM_CODE': VARCHAR(11),
                'PROGRAM_GROUP': VARCHAR(24),
                'RESOURCE_CATEGORY_CODE': VARCHAR(8),
                "SUM_ACTUALS":INTEGER(),
                "SUM_PB":INTEGER(),
                "SUM_ENT":INTEGER()
            }

            df_be.to_sql(
                name="DT_BUDGET_EXECUTION",
                schema=SCHEMA,
                if_exists="replace",
                dtype=dtypes,
                index=False,
                con=db_conn.bind,
                chunksize=200
            )

        with background_db_session() as db_conn:
            columns = ["ASSESSMENT_AREA_CODE", "CAPABILITY_SPONSOR_CODE", "EOC_CODE", "EXECUTION_MANAGER_CODE",
                "PROGRAM_CODE", "PROGRAM_GROUP","RESOURCE_CATEGORY_CODE"]

            for col in columns:
                if col in df_be.columns:
                    create_index_if_missing(db_conn,"DT_BUDGET_EXECUTION", col)

def validate_oop_parsing(row_id):
    
    with background_db_session() as db_conn:
        df_s3, row_meta = get_df_from_s3(row_id,db_conn)
        df_s3 = parse_header_columns(df_s3)

    
    with background_db_session() as db_conn:
        df_pb = pd.read_sql(f"SELECT * FROM {SCHEMA}.DT_PB_COMPARISON;",con=db_conn.bind)
        df_be = pd.read_sql(f"SELECT * FROM {SCHEMA}.DT_BUDGET_EXECUTION;",con=db_conn.bind)

    
    table_type = row_meta["TABLE_NAME"]

    #PB has no requirements in our topological graph
    if table_type not in ["ENT","ACTUALS"]:
        return True
    
    pom_year = row_meta["POM_YEAR"]
    if not PbValidator.check_pb_year_col(df_pb,pom_year):
        #PBYY not in the PB_COMPARISON table, need to parse pb before ENT/ACTUALS
        return False
    
    key_cols = [
        'PROGRAM_CODE',
        'CAPABILITY_SPONSOR_CODE',
        'ASSESSMENT_AREA_CODE',
        'EXECUTION_MANAGER_CODE',
        'RESOURCE_CATEGORY_CODE',
        'EOC_CODE',
        'OSD_PROGRAM_ELEMENT_CODE'
    ]

    # Create sets of row tuples to compare
    df_s3_keys = set(tuple(row) for row in df_s3[key_cols].itertuples(index=False, name=None))

    #create a mask from df_s3 where the fields match current df_be data
    mask = df_be[key_cols].apply(tuple, axis=1).isin(df_s3_keys)

    #Check if all values in 'SUM_PB' for masked rows are non-null
    all_non_nulls = df_be.loc[mask, 'SUM_PB'].notnull().all()
    return all_non_nulls


def update_be_df(table_type,pom_year,df_be,df_s3,db_conn):
    #to filter out current pom_year + 5 range
    
    be_groupby_cols = sorted([
        'ASSESSMENT_AREA_CODE','CAPABILITY_SPONSOR_CODE','EOC_CODE',
        'EXECUTION_MANAGER_CODE','FISCAL_YEAR',
        'OSD_PROGRAM_ELEMENT_CODE', 'PROGRAM_CODE','RESOURCE_CATEGORY_CODE'
    ])
    df_be['FISCAL_YEAR'].astype(int)
    df_s3['FISCAL_YEAR'].astype(int)
    df_be_filtered = df_be[df_be['FISCAL_YEAR'] != int(pom_year)]
    df_s3_filtered = df_s3[df_s3["FISCAL_YEAR"] == int(pom_year)]
    df_s3_filtered = df_s3_filtered.groupby(be_groupby_cols)["RESOURCE_K"].sum().reset_index()
    df_s3_filtered.rename({"RESOURCE_K":f"SUM_{table_type}"},axis='columns',inplace=True)
    if table_type == "PB":
        #PB always come first, then later on ENT/ACTUALS
        df_s3["SUM_ACTUALS"] = np.nan
        df_s3["SUM_ENT"] = np.nan
        df_be_new = pd.concat([df_be_filtered,df_s3_filtered])
    else:
        #Drop the other 2 "SUM" columns (PB/ACTUALS or PB/ENT) if it's not the current table_type
        sum_col = df_be_filtered[[x for x in df_be_filtered.columns if not re.match('SUM', x)] + [f'SUM_{table_type}']]
        sum_col = sum_col[~pd.isna(sum_col[f'SUM_{table_type}'])].\
            reset_index(drop=True) #Drop rows here where the SUM for the table type is missing

        sum_col = pd.concat([sum_col,df_s3_filtered]).\
            sort_values(by=['PROGRAM_CODE', 'PROGRAM_GROUP','EOC_CODE', 'CAPABILITY_SPONSOR_CODE',\
            'ASSESSMENT_AREA_CODE', 'RESOURCE_CATEGORY_CODE', 'EXECUTION_MANAGER_CODE', 'FISCAL_YEAR']).\
                reset_index(drop=True) #Now that we've separated off the target table type, combine it with DT_ADD

        df_be_new = df_be.drop(columns=f"SUM_{table_type}") #drop the current SUM_* table for the reference
        df_be_new = df_be_new.merge(sum_col, on=be_groupby_cols, how= 'outer')

    db_order_col = ['ASSESSMENT_AREA_CODE','CAPABILITY_SPONSOR_CODE','EOC_CODE',
        'EXECUTION_MANAGER_CODE','FISCAL_YEAR',
        'OSD_PROGRAM_ELEMENT_CODE', 'PROGRAM_CODE','PROGRAM_GROUP','RESOURCE_CATEGORY_CODE','SUM_ACTUALS','SUM_ENT','SUM_PB']
    sort_cols = ['PROGRAM_CODE','CAPABILITY_SPONSOR_CODE','ASSESSMENT_AREA_CODE','EOC_CODE','EXECUTION_MANAGER_CODE',
                'RESOURCE_CATEGORY_CODE','OSD_PROGRAM_ELEMENT_CODE','FISCAL_YEAR']
    df_be_new = df_be_new.sort_values(by=sort_cols, ascending=True)
    #Map the PROGRAM_GROUP into the DataFrame   
    code_to_group = get_pg_from_pc(df_s3["PROGRAM_CODE"].tolist(), db_conn)
    df_be_new['PROGRAM_GROUP'] = df_be_new['PROGRAM_CODE'].map(code_to_group)
    df_be_new["PROGRAM_GROUP"].fillna("UNDEFINED",inplace=True)
    
    return df_be_new[db_order_col]

def get_pg_from_pc(pcodes,db_conn):
    from rds.table_model.socom.LookupProgramDetail import LookupProgramModel as lut
    data = db_conn.query(lut.PROGRAM_CODE,lut.PROGRAM_GROUP).filter(lut.PROGRAM_CODE.in_(pcodes)).all()
    return {row[0]:row[1] for row in data}

def upload_dt_oop_tables(row_id, user,redis):

    # result = check_rds_column_exist("DT_PB_COMPARISON", "PB28")
    # if not result:
    #     print("false")

    # print(result)
    
    with background_db_session() as db_conn:
        df_s3,row_meta = get_df_from_s3(row_id,db_conn)
    df_s3 = parse_header_columns(df_s3)
    
    with background_db_session() as db_conn:
        df_pb = pd.read_sql(f"SELECT * FROM {SCHEMA}.DT_PB_COMPARISON;",con=db_conn.bind)
        df_be = pd.read_sql(f"SELECT * FROM {SCHEMA}.DT_BUDGET_EXECUTION;",con=db_conn.bind)
    
    pom_year = row_meta["POM_YEAR"]
    table_type = row_meta["TABLE_NAME"]
    
    
    pb_groupby_cols = sorted(['FISCAL_YEAR', 'PROGRAM_CODE', 'EOC_CODE', 'CAPABILITY_SPONSOR_CODE',\
                         'ASSESSMENT_AREA_CODE', 'RESOURCE_CATEGORY_CODE', 'EXECUTION_MANAGER_CODE',\
                            'OSD_PROGRAM_ELEMENT_CODE'])
    
    #only applicable for PB
    if table_type == "PB":
        pbyy = "PB"+str(pom_year[-2:])
        if pbyy in df_pb.columns:
            df_pb = df_pb.drop(columns=pbyy,axis=1) #idempotent
        df_s3_new = df_s3.groupby(pb_groupby_cols, dropna=False)["RESOURCE_K"].sum().reset_index()
        merged = df_pb.merge(df_s3_new, on=pb_groupby_cols, how="outer")
        merged = merged.rename(columns={"RESOURCE_K": pbyy})
        df_pb = merged
        sort_cols = ['PROGRAM_CODE','CAPABILITY_SPONSOR_CODE','ASSESSMENT_AREA_CODE','EXECUTION_MANAGER_CODE',
                     'RESOURCE_CATEGORY_CODE','EOC_CODE','OSD_PROGRAM_ELEMENT_CODE','FISCAL_YEAR']
        

        #Map the PROGRAM_GROUP into the DataFrame
        code_to_group = get_pg_from_pc(df_s3["PROGRAM_CODE"].tolist(), db_conn)
        df_pb['PROGRAM_GROUP'] = df_pb['PROGRAM_CODE'].map(code_to_group)
        df_pb["PROGRAM_GROUP"].fillna("UNDEFINED",inplace=True)
        df_pb = df_pb.sort_values(by=sort_cols, ascending=True)
        OOPDbParser.parse_pb_comparison(df_pb)

    with background_db_session() as db_conn:
        df_be_new  = update_be_df(table_type,pom_year,df_be,df_s3,db_conn)
    OOPDbParser.parse_budget_execution(df_be_new)
    
        
        
    
    redis.delete("SOCOM::DT_UPLOAD::LOCK") #release lock
    



def upload_extract_dirty_table(row_id,db_conn):
    row =  db_conn.query(
            UsrDtLookupMetadata.TABLE_NAME,
            UsrDtLookupMetadata.POM_YEAR,
            UsrDtUploads.USER_ID
        ).join(UsrDtUploads, UsrDtUploads.ID == UsrDtLookupMetadata.USR_DT_UPLOAD_ID) \
        .join(UsrDtScheduler, UsrDtScheduler.ID == UsrDtUploads.ID) \
        .filter(UsrDtLookupMetadata.USR_DT_UPLOAD_ID == row_id) \
        .filter(UsrDtUploads.TYPE == "DT_UPLOAD_EXTRACT_UPLOAD") \
        .filter(UsrDtScheduler.CRON_STATUS == 1).first()
    if not row:
        raise HTTPException(400,"No valid row selected. Please ensure cron status = 1 and DT_UPLOAD_EXTRACT_UPLOAD upload type")
    table_name = row.TABLE_NAME.split("_")
    table_name = "_".join(table_name[:-1])+"_DIRTY"+f"_{table_name[-1]}"
    table_type = "zbt" if "zbt" in table_name.lower() else "iss"
    pom_year = row.POM_YEAR
    pom_position = f"{pom_year.replace('20','')}{table_type.upper()}"
    user_id = row.USER_ID

    df, _ = get_df_from_s3(row_id,db_conn)
    
    df = ExtractTransformer.pre_upsert_process(df,user_id,pom_position,)

    if table_type == "zbt":
        orm_model = create_dynamic_table_class(AbstractORMClass=DtZBTExtractDirtyModel, table_name=ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT_DIRTY"][0])
    elif table_type == "iss":
        orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractDirtyModel, table_name=IssSummaryTableSet.CURRENT["ISS_EXTRACT_DIRTY"][0])
    
    df['PROGRAM_ID'] = (
        df['PROGRAM_CODE'].astype(str).str.strip() + '_' +
        df['POM_SPONSOR_CODE'].astype(str).str.strip() + '_' +
        df['CAPABILITY_SPONSOR_CODE'].astype(str).str.strip() + '_' +
        df['ASSESSMENT_AREA_CODE'].astype(str).str.strip() + '_' +
        df['EXECUTION_MANAGER_CODE'].astype(str).str.strip() + '_' +
        df['RESOURCE_CATEGORY_CODE'].astype(str).str.strip() + '_' +
        df['EOC_CODE'].astype(str).str.strip() + '_' +
        df['OSD_PROGRAM_ELEMENT_CODE'].astype(str).str.strip() + '_' +
        df['EVENT_NAME'].astype(str).str.strip()
    )
    
    df["PROGRAM_ID"] = df["PROGRAM_ID"].apply(generate_hash_pid)
    orm_model.upsert_dataframe(df,db_conn)

    #Set IS_ACTIVE = 1 and commit
    metadata_row = db_conn.query(UsrDtLookupMetadata).filter(UsrDtLookupMetadata.USR_DT_UPLOAD_ID == row_id).first()
    if metadata_row:
        metadata_row.IS_ACTIVE = 1
        metadata_row.IS_DIRTY_TABLE_ACTIVE = 1
        db_conn.commit()
        db_conn.refresh(metadata_row)
    