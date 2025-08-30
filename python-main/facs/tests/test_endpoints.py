import pytest
import pandas as pd
import numpy as np
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from fastapi.testclient import TestClient

schema = 'FACS_TEST_DATA'
endpoint_ingest = "/facs/ingest"

# Change to tests directory
@pytest.fixture(autouse=True)
def test_dir(request):
    return request.fspath.dirname #role-service/tests


@pytest.fixture(scope="function")
def db_connection():
    engine = create_engine('sqlite://', connect_args={'check_same_thread': False})
    conn = engine.connect()
    conn.execute("ATTACH DATABASE ':memory:' AS FACS_TEST_DATA")
    return conn

@pytest.fixture(autouse=True)
def _mock_create_engine(mocker, db_connection):
    my_mock = mocker.patch('sqlalchemy.create_engine', return_value=db_connection)
    my_mock.mocked_str = "Mocked_connection"
    

@pytest.fixture(autouse=True)
def client(monkeypatch, db_connection):
    monkeypatch.setenv('FACS_RHOMBUS_DB_API', 'false')
    monkeypatch.setenv('FACS_DB_NAME', schema)
    monkeypatch.setenv('FACS_CRED_TABLE', 'cred_keys')
    monkeypatch.setenv('FACS_AUDIT_TABLE', 'audit_log')
    monkeypatch.setenv('FACS_FEATURE_TABLE', 'feature_table')
    monkeypatch.setenv('FACS_ROLE_SERVICE_KEY', 'test_secret_key')
    

    conn = db_connection.connect()
    session = sessionmaker(bind=db_connection)
    setup_facs_tables(conn, schema)

    def override_get_db():
        db = session()
        try:
            yield db
        finally:
            db.close()

    from facs_router import get_db
    import facs_main as test
    
    test.app.dependency_overrides[get_db] = override_get_db
    yield TestClient(test.app)
    del test.app.dependency_overrides[get_db]

def setup_facs_tables(conn, schema):
    sql=f""" CREATE TABLE {schema}.audit_log (
        `time` varchar(255) PRIMARY KEY,
        `user_id` varchar(255),
        `type` varchar(255),
        `input` varchar(255),
        `value` varchar(255),
        `outcome` varchar(255)
        ) """
    conn.execute(sql)
    
    sql=f""" CREATE TABLE {schema}.cred_keys (
        `id` integer PRIMARY KEY AUTOINCREMENT,
        `Key` text ,
        `Type` text,
        `Status` text,
        `Timestamp` text
        ) """
    conn.execute(sql)
    
    sql=f""" CREATE TABLE {schema}.feature_info (
        `id` integer PRIMARY KEY AUTOINCREMENT,
        `Name` text,
        `Status` text,
        `Timestamp` text
        ) """
    conn.execute(sql)
    
    sql=f""" CREATE TABLE {schema}.keycloak_tiles (
        `id` integer PRIMARY KEY AUTOINCREMENT,
        `title` varchar(45),
        `icon` varchar(45),
        `note` varchar(45),
        `description` text,
        `status` varchar(45),
        `created_on` varchar(45),
        `updated_on` varchar(45)
        ) """
    conn.execute(sql)
    
    sql=f""" CREATE TABLE {schema}.role_feature_mapping (
        `id` integer PRIMARY KEY AUTOINCREMENT,
        `user_role_id` json,
        `Timestamp` int(11),
        `app_id` int(11),
        `subapp_id` int(11),
        `feature_id` int(11)
        ) """
    conn.execute(sql)
    
    sql=f""" CREATE TABLE {schema}.subapp_info (
        `id` integer PRIMARY KEY AUTOINCREMENT,
        `Name` text,
        `Status` text,
        `Timestamp` int(11)
        ) """
    conn.execute(sql)
    
    sql=f""" CREATE TABLE {schema}.user_roles (
        `id` integer PRIMARY KEY AUTOINCREMENT,
        `Name` text,
        `Status` text,
        `Timestamp` int
        ) """
    conn.execute(sql)
            
def login(client):
    # get root_key
    response = client.post("/facs/create_admin_key")
    root_key = response.json()['key']

    # login
    jwt_token = client.post(
        "/facs/login", 
        json={"key": root_key}
    ).json()['token']
    return jwt_token

def populate_sample_keys(db_connection, schema):
    table_cred_keys = pd.DataFrame(
        [
            ['2', '1234567890123456', 'admin', 'Active', '1671047341'],
            ['3', '1234567890123457', 'dev', 'Active', '1671047387'],
            ['4', '1234567890123458', 'app', 'Active', '1671047462'],
            ['5', '1234567890123459', 'testing', 'Active', '1671047462'],
        ],
        columns=["id", "Key", "Type", "Status", "Timestamp"]
    )
    table_cred_keys.to_sql('cred_keys', db_connection, schema, if_exists='append', index=False)


#Generic User Login Helper Method
def user_login(client,key):    
    jwt_token = client.post(
        "/facs/login", 
        json={"key": key}
    ).json()['token']
    return jwt_token

""" Will use this later when working...
def ingest_response_helper(verb,info_type, info_value,token):   
    tmp = 'client.'+ verb + '(' + '"/facs/ingest",' + 'json={"info_Type": "'+info_type+ '","info_Value": "'+info_value+'"},' + 'headers={\'Authorization\': f\'Bearer '+token+'\'})'
    print(tmp)
    response = exec(tmp)
    return response
"""

# Test for Create Admin key
def test_create_admin_key_endpoint(client):
    response = client.post(
        "/facs/create_admin_key",
        
    )
    assert response.status_code == 201
    response_json = response.json()
    assert list(response_json.keys()) == ['message', 'key', 'warning']
    assert response_json['message'] == 'Success'
    assert response_json['warning'] == 'Do Not share this key to anyone'


def test_create_key(client):
    token = login(client)
    endpoint_create_key = "/facs/create_key"
    # create new key with invalid user_type
    response = client.post(
        endpoint_create_key,
        json={"key": "1234567890123456", "user_type": "admin1"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 400
    assert response.json() == {'detail': 'User type not allowed'}

    # create new key with root user_type   
    response = client.post(
        endpoint_create_key,
        json={"key": "1234567890123456", "user_type": "root"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 403
    assert response.json() == {'detail': 'A new root user key is not allowed'}

    # create new invalid key 
    response = client.post(
        endpoint_create_key,
        json={"key": "123456789012345678901234", "user_type": "admin"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 400
    assert response.json() == {'detail': 'Key does not match, must have valid non-empty 16 digit key'}

    # create new valid key 
    response = client.post(
        endpoint_create_key,
        json={"key": "1234567890123456", "user_type": "admin"},
        headers={'Authorization': f'Bearer {token}'}
    )

    assert response.status_code == 201
    assert response.json() == {'message': 'Success'}


def test_delete_key(client, db_connection):
    token = login(client)
    populate_sample_keys(db_connection, schema)

    # delete invalid key 
    response = client.request(
        "DELETE",
        "/facs/delete_key",
        json={"key": "12345678901234567", "user_type": "admin"},
        headers={'Authorization': f'Bearer {token}'}
    )

    assert response.status_code == 400
    assert response.json() == {'detail': 'Key does not exist'}

    # delete valid key 
    response = client.request(
        "DELETE",
        "/facs/delete_key",
        json={"key": "1234567890123456", "user_type": "admin"},
        headers={'Authorization': f'Bearer {token}'}
    )

    assert response.status_code == 201
    assert response.json() == {'message': 'Success'}

#Test For Ingest Endpoint    
def test_ingest(client, db_connection):
    populate_sample_keys(db_connection, schema)
    
    #Use The Dev User Key
    token = user_login(client,'1234567890123457')


    #Successfuly Ingest with Valid Token and user_type of Dev
    response = client.post(endpoint_ingest,json={"info_Type": "feature","info_Value": "test_feature"},
                headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 200
    assert response.json() == {'message': 'success'}  
    
    #Ingest with Invalid Token and user_type of Dev
    response = client.post(
        endpoint_ingest,
        json={"info_Type": "subapp","info_Value": "test_subapp"},
        headers={'Authorization': f'Bearer {token+"xx"}'}
    )
    assert response.status_code == 401
    assert response.json() == {'detail': 'Invalid token'}     
    
    #Ingest with valid Token and user_type of admin a duplicate feature entry
    #Use The admin User Key
    token = user_login(client,'1234567890123456')
    response = client.post(
        endpoint_ingest,
        json={"info_Type": "feature","info_Value": "test_feature"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 200
    assert response.json() == {'message': 'feature already exists'} 
    
    #Ingest with valid Token and user_type of admin new feature entry
    response = client.post(
        endpoint_ingest,
        json={"info_Type": "feature","info_Value": "test_feature1"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 200
    assert response.json() == {'message': 'success'} 

#Test For Remove Feature Endpoint    
def test_rmfeature(client,db_connection):
    populate_sample_keys(db_connection, schema)
    
    #Use The Admin User Key
    token = user_login(client,'1234567890123456')

    #Create an app & feature & subapp so that they can be removed
    
    response = client.post(
        endpoint_ingest,
        json={"info_Type": "feature","info_Value": "test_feature"},
        headers={'Authorization': f'Bearer {token}'}
    )    
    assert response.status_code == 200
    assert response.json() == {'message': 'success'} 
    
    response = client.post(
        endpoint_ingest,
        json={"info_Type": "app","info_Value": "test_app"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 200
    assert response.json() == {'message': 'success'} 
    
    response = client.post(
        endpoint_ingest,
        json={"info_Type": "subapp","info_Value": "test_sub_app"},
        headers={'Authorization': f'Bearer {token}'}
    )
    assert response.status_code == 200
    assert response.json() == {'message': 'success'} 

    #Admin Successfuly Removes Feature

    response = client.request(
        "DELETE",
        "/facs/rmfeature",
        json={"info_Type": "feature","info_Value": "test_feature"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    assert response.status_code == 200
    assert response.json() == {'message': 'success'}  

    #Admin successfuly removes subapp
    response = client.request(
        "DELETE",
        "/facs/rmfeature",
        json={"info_Type": "subapp","info_Value": "test_sub_app"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    assert response.status_code == 200
    assert response.json() == {'message': 'success'} 
 
#Test For create_role_map endpoint     
def test_create_role_map(client,db_connection):
    populate_sample_keys(db_connection, schema)
    
    #Use The Admin User Key 
    token = user_login(client,'1234567890123456')
    
    #Use ingest endpoint to create all components of the role map
    _ = client.post(
        endpoint_ingest,
        json={"info_Type": "app","info_Value": "test_app"},
        headers={'Authorization': f'Bearer {token}'}
    ) 
    
    _ = client.post(
        endpoint_ingest,
        json={"info_Type": "subapp","info_Value": "test_sub_app"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    _ = client.post(
        endpoint_ingest,
        json={"info_Type": "feature","info_Value": "test_feature"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    _ = client.post(
        endpoint_ingest,
        json={"info_Type": "role","info_Value": "test_role1"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    _ = client.post(
        endpoint_ingest,
        json={"info_Type": "role","info_Value": "test_role2"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    _ = client.post(
        endpoint_ingest,
        json={"info_Type": "role","info_Value": "test_role3"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    #Create an role_map using the user object class
    response = client.post(
        "/facs/create_role_map",
        json={"app_name": "test_app","subapp_name": "test_sub_app","feature_name": "test_feature", 
              "user_roles" : ["test_role1", "test_role2", "test_role3"], "overall":"test_overall"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    assert response.status_code == 201
    assert response.json() == 'Mapping Added'
    
    #Create an role_map using with incorrect names to cause error
    response = client.post(
        "/facs/create_role_map",
        json={"app_name": "test_app1","subapp_name": "test_sub_ap1p","feature_name": "test_feature", 
              "user_roles" : ["test_role1", "test_role211", "test_role3"], "overall":"test_overall"},
        headers={'Authorization': f'Bearer {token}'}
    )
    
    assert response.status_code == 201
    assert response.json() == 'Requested section does not exist:AppName=test_app1'
