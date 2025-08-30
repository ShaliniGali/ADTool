import pytest
import optimizer.socom_maximizer as test
from api.internal import socom_models
import pandas as pd

from fastapi import HTTPException
from decimal import Decimal

MODEL_DICT = {
    "eyr": 2031,
    "syr": 2026,
    "budget": [
        10000,
        20000,
        30000,
        40000,
        50000
    ],
    "option": 1,
    "criteria_name_id":1, 
    "score_id": [
        {
            "USER_ID": 2,
            "PROGRAM_ID": "OTHERAA_USASOC_USASOC"
        },
        {
            "USER_ID": 2,
            "PROGRAM_ID": "OTHERDD_USASOC_USASOC"
        },
        {
            "USER_ID": 2,
            "PROGRAM_ID": "OTHERII_NSW_NSW"
        },
        {
            "USER_ID": 2,
            "PROGRAM_ID": "OTHERIZ_AFSOC_NSW"
        },
        {
            "USER_ID": 2,
            "PROGRAM_ID": "STUFFAA_NSW_NSW"
        },
        {
            "USER_ID": 2,
            "PROGRAM_ID": "STUFFKK_MARSOC_AFSOC"
        },
        {
            "USER_ID": 2,
            "PROGRAM_ID": "YUMMYPZ_AFSOC_AFSOC"
        }
    ],
    "weight_id": 103,
    "ProgramIDs": [
        "STUFFKK_MARSOC_AFSOC",
        "OTHERII_NSW_NSW",
        "OTHERIZ_AFSOC_NSW",
        "STUFFAA_NSW_NSW",
        "YUMMYPZ_AFSOC_AFSOC",
        "OTHERAA_USASOC_USASOC",
        "OTHERDD_USASOC_USASOC"
    ],
    "storm_flag": False,
    "must_exclude": ["OTHERIZ_AFSOC_NSW"],
    "must_include": [
        "OTHERII_NSW_NSW"
    ],
    "support_all_years": True
}
MODEL_DICT_FILTER_GOOD = {
    "model": {
    "resource_k": {
        "2026": {
        "OTHERAA_USASOC_USASOC": 629,
        "OTHERDD_USASOC_USASOC": 626,
        "STUFFKK_MARSOC_AFSOC": 708,
        "OTHERIZ_AFSOC_NSW": 100,
        "YUMMYPZ_AFSOC_AFSOC": 1373
        },
        "2027": {
        "OTHERAA_USASOC_USASOC": 729,
        "OTHERDD_USASOC_USASOC": 666,
        "STUFFKK_MARSOC_AFSOC": 723,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1713
        },
        "2028": {
        "OTHERAA_USASOC_USASOC": 829,
        "OTHERDD_USASOC_USASOC": 706,
        "STUFFKK_MARSOC_AFSOC": 738,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1469
        },
        "2029": {
        "OTHERAA_USASOC_USASOC": 929,
        "OTHERDD_USASOC_USASOC": 746,
        "STUFFKK_MARSOC_AFSOC": 753,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1734
        },
        "2030": {
        "OTHERAA_USASOC_USASOC": 1029,
        "OTHERDD_USASOC_USASOC": 786,
        "STUFFKK_MARSOC_AFSOC": 768,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 2019
        }
    },
    "selected_programs": [
        {
        "program_id": "OTHERAA_USASOC_USASOC",
        "pom_sponsor": "USASOC",
        "capability_sponsor": "USASOC",
        "weighted_guidance_score": 100,
        "weighted_pom_score": 100,
        "resource_k": {
            "2026": 629,
            "2027": 729,
            "2028": 829,
            "2029": 929,
            "2030": 1029
        },
        "total_storm_score": 0
        },
        {
        "program_id": "OTHERDD_USASOC_USASOC",
        "pom_sponsor": "USASOC",
        "capability_sponsor": "USASOC",
        "weighted_guidance_score": 92,
        "weighted_pom_score": 92,
        "resource_k": {
            "2026": 626,
            "2027": 666,
            "2028": 706,
            "2029": 746,
            "2030": 786
        },
        "total_storm_score": 0
        },
        {
        "program_id": "STUFFKK_MARSOC_AFSOC",
        "pom_sponsor": "MARSOC",
        "capability_sponsor": "AFSOC",
        "weighted_guidance_score": 90,
        "weighted_pom_score": 90,
        "resource_k": {
            "2026": 708,
            "2027": 723,
            "2028": 738,
            "2029": 753,
            "2030": 768
        },
        "total_storm_score": 0
        },
        {
        "program_id": "OTHERIZ_AFSOC_NSW",
        "pom_sponsor": "AFSOC",
        "capability_sponsor": "NSW",
        "weighted_guidance_score": 80,
        "weighted_pom_score": 80,
        "resource_k": {
            "2026": 100,
            "2027": 0,
            "2028": 0,
            "2029": 0,
            "2030": 0
        },
        "total_storm_score": 0
        },
        {
        "program_id": "YUMMYPZ_AFSOC_AFSOC",
        "pom_sponsor": "AFSOC",
        "capability_sponsor": "AFSOC",
        "weighted_guidance_score": 80,
        "weighted_pom_score": 80,
        "resource_k": {
            "2026": 1373,
            "2027": 1713,
            "2028": 1469,
            "2029": 1734,
            "2030": 2019
        },
        "total_storm_score": 0
        }
    ],
    "remaining": {
        "2026": 6664,
        "2027": 16169,
        "2028": 26258,
        "2029": 35838,
        "2030": 45398
    }
    },
    "filter": {
        "filter_zero_resource_k": True
    }
    }
MODEL_DICT_FILTER_BAD  = {
    "model": {
    "resource_k": {
        "2026": {
        "OTHERAA_USASOC_USASOC": 629,
        "OTHERDD_USASOC_USASOC": 626,
        "STUFFKK_MARSOC_AFSOC": 708,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1373
        },
        "2027": {
        "OTHERAA_USASOC_USASOC": 729,
        "OTHERDD_USASOC_USASOC": 666,
        "STUFFKK_MARSOC_AFSOC": 723,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1713
        },
        "2028": {
        "OTHERAA_USASOC_USASOC": 829,
        "OTHERDD_USASOC_USASOC": 706,
        "STUFFKK_MARSOC_AFSOC": 738,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1469
        },
        "2029": {
        "OTHERAA_USASOC_USASOC": 929,
        "OTHERDD_USASOC_USASOC": 746,
        "STUFFKK_MARSOC_AFSOC": 753,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 1734
        },
        "2030": {
        "OTHERAA_USASOC_USASOC": 1029,
        "OTHERDD_USASOC_USASOC": 786,
        "STUFFKK_MARSOC_AFSOC": 768,
        "OTHERIZ_AFSOC_NSW": 0,
        "YUMMYPZ_AFSOC_AFSOC": 2019
        }
    },
    "selected_programs": [
        {
        "program_id": "OTHERAA_USASOC_USASOC",
        "pom_sponsor": "USASOC",
        "capability_sponsor": "USASOC",
        "weighted_guidance_score": 100,
        "weighted_pom_score": 100,
        "resource_k": {
            "2026": 629,
            "2027": 729,
            "2028": 829,
            "2029": 929,
            "2030": 1029
        },
        "total_storm_score": 0
        },
        {
        "program_id": "OTHERDD_USASOC_USASOC",
        "pom_sponsor": "USASOC",
        "capability_sponsor": "USASOC",
        "weighted_guidance_score": 92,
        "weighted_pom_score": 92,
        "resource_k": {
            "2026": 626,
            "2027": 666,
            "2028": 706,
            "2029": 746,
            "2030": 786
        },
        "total_storm_score": 0
        },
        {
        "program_id": "STUFFKK_MARSOC_AFSOC",
        "pom_sponsor": "MARSOC",
        "capability_sponsor": "AFSOC",
        "weighted_guidance_score": 90,
        "weighted_pom_score": 90,
        "resource_k": {
            "2026": 708,
            "2027": 723,
            "2028": 738,
            "2029": 753,
            "2030": 768
        },
        "total_storm_score": 0
        },
        {
        "program_id": "OTHERIZ_AFSOC_NSW",
        "pom_sponsor": "AFSOC",
        "capability_sponsor": "NSW",
        "weighted_guidance_score": 80,
        "weighted_pom_score": 80,
        "resource_k": {
            "2026": 0,
            "2027": 0,
            "2028": 0,
            "2029": 0,
            "2030": 0
        },
        "total_storm_score": 0
        },
        {
        "program_id": "YUMMYPZ_AFSOC_AFSOC",
        "pom_sponsor": "AFSOC",
        "capability_sponsor": "AFSOC",
        "weighted_guidance_score": 80,
        "weighted_pom_score": 80,
        "resource_k": {
            "2026": 1373,
            "2027": 1713,
            "2028": 1469,
            "2029": 1734,
            "2030": 2019
        },
        "total_storm_score": 0
        }
    ],
    "remaining": {
        "2026": 6664,
        "2027": 16169,
        "2028": 26258,
        "2029": 35838,
        "2030": 45398
    }
    },
    "filter": {
        "filter_zero_resource_k": True
    }
    }

def test_ProgData():
    #test ProgData class
    program_id = "test id"
    program_name = "test program"
    pom_sponsor = "test pom sponsor"
    capability_sponsor = "test cap sponsor"
    weighted_guidance_score = 80
    weighted_pom_score = 90
    resource_k = {"2026":100,"2027":200,"2028":300,"2029":400,"2030":500}
    progdata = test.ProgData(program_id=program_id,
                            program_name=program_name,
                            pom_sponsor=pom_sponsor,
                            capability_sponsor=capability_sponsor,
                            weighted_pom_score=weighted_pom_score,
                            weighted_guidance_score=weighted_guidance_score,
                            resource_k = resource_k
                            )
    
    assert progdata.program_id == program_id
    assert progdata.program_name == program_name
    assert progdata.pom_sponsor == pom_sponsor
    assert progdata.capability_sponsor == capability_sponsor
    assert progdata.weighted_pom_score == weighted_pom_score
    assert progdata.weighted_guidance_score == weighted_guidance_score
    assert progdata.resource_k == resource_k




def test_Progserializer():
    #Test the Progserializer class
    program_id = "test id"
    program_name = "test program"
    pom_sponsor = "test pom sponsor"
    capability_sponsor = "test cap sponsor"

    #Test serializer
    progserializer = test.ProgSerializer()
    fiscal_year = [str(year) for year in range(2026,2031)]
    # resource_k = Decimal(100.0) #resource_k = fiscal year in decimal
    # print(resource_k)
    tokens = []
    for fy in fiscal_year:
        token = (program_id,program_name,pom_sponsor,capability_sponsor,fy,Decimal(fy)) #resource_k = Decimal(k)
        tokens.append(token)
    progserializer.serialize(tokens)
    
    assert len(progserializer.data) == len(fiscal_year) == len(tokens)
    for progdata in progserializer.data:
        assert progdata.program_id == program_id
        assert progdata.program_name == program_name
        assert progdata.pom_sponsor == pom_sponsor
        assert progdata.capability_sponsor == capability_sponsor
        assert progdata.weighted_guidance_score == 0 #default
        assert progdata.weighted_pom_score == 0 #default
        resource_k = progdata.resource_k
        fy = list(resource_k.keys())[0] #should only be 1 key
        resource_k = resource_k[fy]
        assert fy == str(resource_k)
    

def test_transform_for_knapsack():
    data_template = {"program_id":"test id",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 80,
            "weighted_pom_score":50,
            "resource_k":{}}
    data = []
    for year in range(2026,2031):
        data_template['resource_k'][year] = year
        prog_data_obj = test.ProgData.parse_obj(data_template)
        data.append(prog_data_obj)
    
    # print(data)
    output = test.transform_for_knapsack(data)
    # print(output[0]['resource_k'].keys())
    assert len(output) == 1 #combined all same program id into 1
    assert len(output[0]['resource_k'].keys()) == len(range(2026,2031)) #concat the resource_k from the years




def test_custom_sort():
    data = [{"program_id":"test id1",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 100,
            "weighted_pom_score":0,
            "resource_k":{2026:100}},
            {"program_id":"test id2",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 200,
            "weighted_pom_score":200,
            "resource_k":{2026:100}},    
            {"program_id":"test id3",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 300,
            "weighted_pom_score":100,
            "resource_k":{2026:100}} ]
    
    # data = test.transform_for_knapsack(data)
    sorted_data = test.custom_sort(data,must_include=[],option=1)
    # breakpoint()
    
    #different optimization options
    assert sorted_data[0]['program_id'] == "test id2"
    assert sorted_data[1]['program_id'] == "test id3"
    assert sorted_data[2]['program_id'] == "test id1"

    sorted_data = test.custom_sort(data,must_include=[],option=2)
    assert sorted_data[0]['program_id'] == "test id3"
    assert sorted_data[1]['program_id'] == "test id2"
    assert sorted_data[2]['program_id'] == "test id1"

    sorted_data = test.custom_sort(data,must_include=[],option=3)
    assert sorted_data[0]['program_id'] == "test id2"
    assert sorted_data[1]['program_id'] == "test id3"
    assert sorted_data[2]['program_id'] == "test id1"


def test_maximize_option():
    data = [{"program_id":"test id1",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 100,
            "weighted_pom_score":0,
            "resource_k":{"2026":100}},
            {"program_id":"test id2",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 200,
            "weighted_pom_score":200,
            "resource_k":{"2026":100}},    
            {"program_id":"test id3",
            "program_name":"test name",
            "pom_sponsor": "test sponsor",
            "capability_sponsor": "test capability sponsor",
            "weighted_guidance_score": 300,
            "weighted_pom_score":100,
            "resource_k":{"2026":100}} ]
    must_include = ["test id 1"]
    option = 3 #pom weight
    syr = 2026
    eyr = 2027
    support_all_years = False #just 1 year of budget submitted, dummy
    budgets = [100]

    selected_progs, result_dict = test.maximize_option(data,must_include,option,syr,eyr,budgets,support_all_years)
    assert selected_progs[0]["program_id"] == "test id2" #only must include, lowest score, no budget left
    assert result_dict[2026] == {"test id2": 100}
    # breakpoint()

    #new budget less than any available options, including must_include
    budgets = [50]
    selected_progs, result_dict = test.maximize_option(data,must_include,option,syr,eyr,budgets,support_all_years)
    # breakpoint()
    assert (not selected_progs) and (not result_dict)


def test_obtain_selected_programs():
    #programs are selected but there might be dups
    selected_programs = [{
        'program_id': 'AircraftXXX_AFSOC_AFSOC', 
        'pom_sponsor': 'AFSOC', 'capability_sponsor': 'AFSOC', 
        'weighted_guidance_score': 100.0, 'weighted_pom_score': 100.0,
        'resource_k': {'2030': 56571, 
                       '2029': 78768, 
                       '2028': 65648, 
                       '2027': 64847, 
                       '2026': 64750}},
        {
        'program_id': 'AircraftXXX_AFSOC_AFSOC', 
        'pom_sponsor': 'AFSOC', 'capability_sponsor': 'AFSOC', 
        'weighted_guidance_score': 100.0, 'weighted_pom_score': 100.0,
        'resource_k': {'2030': 56571, 
                       '2029': 78768, 
                       '2028': 65648, 
                       '2027': 64847, 
                       '2026': 64750}},
        {
        'program_id': 'VehiclesXXX_MARSOC_MARSOC', 
        'pom_sponsor': 'MARSOC', 'capability_sponsor': 'MARSOC', 
        'weighted_guidance_score': 60.3, 'weighted_pom_score': 60.3, 
        'resource_k': {'2030': 51989, 
                       '2029': 61354, 
                       '2028': 51137, 
                       '2027': 69172, 
                       '2026': 55251}}
    ]
    results = {2026:{'AircraftXXX_AFSOC_AFSOC': 64750}, 
               2027:{'AircraftXXX_AFSOC_AFSOC': 64847}}

    selected_programs_new = test.obtain_selected_programs(selected_programs,results)
    assert len(selected_programs_new) == 1 #only data within result is selected. No duplicate program ids also

def test_get_resource_k_from_progIds(mock_session,db_connection,socom_schema):
    OptInputModel = socom_models.OptimizerInputModel.parse_obj(MODEL_DICT)
    resp = test.get_resource_k_from_progIds(mock_session,OptInputModel)
    # df = pd.read_sql("SELECT * FROM LOOKUP_PROGRAM;",con=db_connection)
    # need to verify the output by hand checking data
    resource_k = resp['resource_k']

    OptInputModel.budget
    for i,year in enumerate(range(OptInputModel.syr,OptInputModel.eyr)):
        #asserting that the resources k allocation + remaining == the total budget
        #need to understand why year is int, not str when output resp return is str
        assert OptInputModel.budget[i] -  sum(resource_k[year].values()) == resp['remaining'][year]

    MODEL_DICT_NEW = MODEL_DICT.copy()
    MODEL_DICT_NEW["must_include"] = MODEL_DICT_NEW["ProgramIDs"]
    MODEL_DICT_NEW
    MODEL_DICT_NEW["budget"] = [100 for _ in MODEL_DICT_NEW["budget"]]
    OptInputModelNew = socom_models.OptimizerInputModel.parse_obj(MODEL_DICT_NEW)
    
    #cannot fit all must include into the budget
    resp = {}
    MODEL_DICT_NEW["budget"] = [0 for _ in MODEL_DICT_NEW["budget"]]
    OptInputModelNew = socom_models.OptimizerInputModel.parse_obj(MODEL_DICT_NEW)
    resp = test.get_resource_k_from_progIds(mock_session,OptInputModelNew)
    # assert ("warning" in resp)
    # breakpoint()
    assert resp["selected_programs"] == []
    
    #422 due to input error
    resp = {}
    #how to catch http error in pytest
    with pytest.raises(HTTPException) as err:
        MODEL_DICT_NEW["budget"] = [100 for _ in MODEL_DICT_NEW["budget"]] + [100] #mismatch list size of budget and eyr/syr
        OptInputModelNew = socom_models.OptimizerInputModel.parse_obj(MODEL_DICT_NEW)
        resp = test.get_resource_k_from_progIds(mock_session,OptInputModelNew)
    assert err.value.status_code == 422
    assert not resp

    #coverage with support all years. Our MODEL_DICT input doesnt invalidate the result.
    OptInputModel = socom_models.OptimizerInputModel.parse_obj(MODEL_DICT)
    OptInputModel.support_all_years = True
    resp = test.get_resource_k_from_progIds(mock_session,OptInputModel)
    # df = pd.read_sql("SELECT * FROM LOOKUP_PROGRAM;",con=db_connection)
    # need to verify the output by hand checking data
    resource_k = resp['resource_k']

    OptInputModel.budget
    for i,year in enumerate(range(OptInputModel.syr,OptInputModel.eyr)):
        #asserting that the resources k allocation + remaining == the total budget
        #need to understand why year is int, not str when output resp return is str
        assert OptInputModel.budget[i] -  sum(resource_k[year].values()) == resp['remaining'][year]




########################
#filter optimizer output endpoint
def test_post_optimization_filter():
    

    model = socom_models.OptimizerOutputModel.parse_obj(MODEL_DICT_FILTER_GOOD["model"])
    filter = socom_models.OptimizerFilterParams.parse_obj(MODEL_DICT_FILTER_GOOD["filter"])

    new_model = test.post_optimization_filter(model,filter)
    
    #assert all listed programs not include the 0 resource_k data
    new_model.pop('filter')
    assert  new_model == MODEL_DICT_FILTER_GOOD["model"]
    

    #now we change the 'OTHERIZ_AFSOC_NSW' program id to have zeros all throughout
    model = socom_models.OptimizerOutputModel.parse_obj(MODEL_DICT_FILTER_BAD["model"])
    new_model = test.post_optimization_filter(model,filter)
    for program in new_model["selected_programs"]:
        # breakpoint()
        assert program.program_id != "OTHERIZ_AFSOC_NSW"

    filter.filter_zero_resource_k = False #change to not apply filter
    #bad data with all zeros resource_k
    new_model = test.post_optimization_filter(model,filter)
    assert new_model == MODEL_DICT_FILTER_BAD["model"] #filter did not run



def test_get_weighted_coa_scores(mock_session,db_connection):
    reference = {'OTHERII_NSW_NSW': 
            {'total_storm_scores': 49, 
             'weighted_pom_score': 80.57, 
             'weighted_guidance_score': 84.44}}
    #defined above
    weight_id = 103
    user_id = 2
    criteria_name_id = 1
    program_ids = ["OTHERII_NSW_NSW"]
    model = socom_models.COAWeightedScoresInputModel(weight_id=weight_id,user_id=user_id,program_ids=program_ids,criteria_name_id=criteria_name_id)
    result = test.get_weighted_coa_scores(model,mock_session)
    assert result == reference
    