# -*- coding: utf-8 -*-
"""
Description:
    Test configuration

Author: Rhombus Guardian
POC Email: engineering@rhombuspower.com
POC Telephone Number: (408) 685-0370

"""
from datetime import datetime
import pytest


from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

from tests.data.database_data import (
    create_dataset_lookup_program,
    create_dataset_lookup_program_detail,
    create_dataset_dt_iss_2026,
    create_dataset_usr_criteria_wts,
    create_dataset_usr_option_scores,
    create_dataset_lookup_storm,
    create_dataset_dt_ext_2026,
    create_dataset_dt_ext_2027,
    create_dataset_dt_iss_extract_2026,
    create_dataset_dt_zbt_2026,
    create_dataset_lookup_jca,
    create_dataset_dt_zbt_extract_2026,
    create_dataset_lookup_jca2,
    create_dataset_dt_zbt_extract_2027,
    create_dataset_usr_lookup_saved_coa,
)


monkeypatch = pytest.MonkeyPatch()


monkeypatch.setenv('FACS_RHOMBUS_DB_API', 'false')
monkeypatch.setenv('FACS_DB_NAME', 'FACS_TEST_DATA')
monkeypatch.setenv('FACS_DB_USER', 'testing')
monkeypatch.setenv('FACS_DB_HOST', 'localhost')
monkeypatch.setenv('FACS_DB_PASS', 'password')
monkeypatch.setenv('FACS_CRED_TABLE', 'cred_keys')
monkeypatch.setenv('FACS_AUDIT_TABLE', 'audit_log')
monkeypatch.setenv('FACS_FEATURE_TABLE', 'feature_table')
monkeypatch.setenv('FACS_ROLE_SERVICE_KEY', 'test_secret_key')

monkeypatch.setenv('SOCOM_BASE_URL', '')
monkeypatch.setenv('SOCOM_SAML_USERS_SIPR', 'TRUE')
monkeypatch.setenv('CI_PRODUCTS_0_USERNAME', '')
monkeypatch.setenv('CI_PRODUCTS_0_PASSWORD', '')
monkeypatch.setenv('CI_PRODUCTS_0_HOST', '')
monkeypatch.setenv('CI_DB_API_URL', '')
monkeypatch.setenv('CI_DB_API_KEY', '')
monkeypatch.setenv('SOCOM_UI', 'SOCOM_UI')
monkeypatch.setenv('SOCOM_DB_API_NAME', '')
monkeypatch.setenv('SOCOM_PYTHON_API_NETWORK_ALIAS', '')
monkeypatch.setenv('SOCOM_PYTHON_PORT', '')
monkeypatch.setenv('SOCOM_PYTHON_API_TESTING', 'FALSE')
monkeypatch.setenv('SOCOM_PYTHON_RDS', 'TRUE') # need this not to use vault


@pytest.fixture(autouse=True)
def socom_schema():
    return 'SOCOM_UI'


#making an in memory db
@pytest.fixture(scope="module")
def db_connection(): #create a db everytime we call this fixture
    engine = create_engine('sqlite://',connect_args={'check_same_thread':False})
    conn = engine.connect()
    schema = 'SOCOM_UI'
    conn.execute(f"ATTACH DATABASE ':memory' AS {schema}")

    setup_socom_tables(engine,schema)
    return engine

@pytest.fixture(autouse=True)
def mock_session(db_connection):
    socom_session = sessionmaker(bind=db_connection)
    with socom_session() as session:
        yield session

def setup_socom_tables(engine,schema):
    #test tables for socom

    lookup_prog = create_dataset_lookup_program()
    lookup_prog_detail = create_dataset_lookup_program_detail()
    
    lookup_prog.to_sql("LOOKUP_PROGRAM",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)
    lookup_prog_detail.to_sql("LOOKUP_PROGRAM_DETAIL", schema="SOCOM_UI", if_exists="replace", index=False, con=engine)

    dt_iss_2026 = create_dataset_dt_iss_2026()
    dt_iss_2026.to_sql("DT_ISS_2026",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    dt_zbt_2026 = create_dataset_dt_zbt_2026()
    dt_zbt_2026.to_sql("DT_ZBT_2026", schema=schema, if_exists="replace", index=False, con=engine)

    UsrLookupCriteriaWts = create_dataset_usr_criteria_wts()
    UsrLookupCriteriaWts.to_sql("USR_LOOKUP_CRITERIA_WEIGHTS",schema=schema,if_exists="replace",index=False,con=engine)
    
    UsrOptionScores = create_dataset_usr_option_scores()
    UsrOptionScores.to_sql("USR_OPTION_SCORES",schema=schema,if_exists="replace",index=False,con=engine)
    
    lookup_storm = create_dataset_lookup_storm()
    lookup_storm.to_sql("LOOKUP_STORM",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    dt_ext_2026 = create_dataset_dt_ext_2026()
    dt_ext_2026.to_sql("DT_EXT_2026",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    dt_ext_2027 = create_dataset_dt_ext_2027()
    dt_ext_2027.to_sql("DT_EXT_2027",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    dt_zbt_extract_2026 = create_dataset_dt_zbt_extract_2026()
    dt_zbt_extract_2026.to_sql("DT_ZBT_EXTRACT_2026", schema=schema, if_exists="replace", index=False, con=engine)

    dt_zbt_extract_2027 = create_dataset_dt_zbt_extract_2027()
    dt_zbt_extract_2027.to_sql("DT_ZBT_EXTRACT_2027", schema=schema, if_exists="replace", index=False, con=engine)

    dt_iss_extract_2026 = create_dataset_dt_iss_extract_2026()
    dt_iss_extract_2026.to_sql("DT_ISS_EXTRACT_2026", schema=schema, if_exists="replace", index=False, con=engine)

    lookup_jca = create_dataset_lookup_jca()
    lookup_jca.to_sql("LOOKUP_JCA",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    lookup_jca = create_dataset_lookup_jca2()
    lookup_jca.to_sql("LOOKUP_JCA2",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    dt_zbt_extract_2027 = create_dataset_dt_zbt_extract_2027()
    dt_zbt_extract_2027.to_sql("DT_ZBT_EXTRACT_2027",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

    usr_lookup_saved_coa = create_dataset_usr_lookup_saved_coa()
    usr_lookup_saved_coa.to_sql("USR_LOOKUP_SAVED_COA",schema="SOCOM_UI",if_exists="replace",index=False,con=engine)

"""
API ENDPOINT TESTING
"""
import main as test
import api.router.optimizer as optimizer
from fastapi.testclient import TestClient


def override_get_socom_db():
    engine = create_engine('sqlite://', connect_args={'check_same_thread': False})
    conn = engine.connect()
    conn.execute(f"ATTACH DATABASE ':memory:' AS SOCOM_UI")
    schema = "SOCOM_UI"
    setup_socom_tables(engine,schema)
    return conn

# #for api requests only
@pytest.fixture(autouse=True,scope='module')
def socom_session():
    socom_session = sessionmaker(bind=override_get_socom_db())
    with socom_session() as session:
        yield session


@pytest.fixture(autouse=True, scope='module')
def socom_client(socom_session):
    session = sessionmaker(bind=override_get_socom_db())
    async def get_socom_session():
        with session() as session:
            yield session
    
    test.app.dependency_overrides[get_socom_session] = get_socom_session #any of the depedencies with said name detected
    client =  TestClient(test.app)
    yield client
