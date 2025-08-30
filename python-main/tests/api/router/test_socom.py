from api.router import socom as test
import requests
import json
import pandas as pd
from io import StringIO
from fastapi import HTTPException
import pytest
from unittest.mock import patch
from io import BytesIO

def test_get_eoc_funding(socom_client):
    
    payload =[
        "OTHERAA_USASOC_USASOC"
    ]
    resp = socom_client.post('/socom/prog_eoc_funding',json=payload) #full path
    assert resp.status_code == 200
    resp = resp.json()

    # assert ("OTHERAA.XXX" in resp["OTHERAA_USASOC_USASOC"]["eoc_code"]) \
    #     and ("O&M $" in resp["OTHERAA_USASOC_USASOC"]["resource_category_code"])

    for item in resp:
        if item.get("ID") == "OTHERAA_USASOC_USASOC":
            assert "OTHERAA.XXX" in item["EOC_CODE"]
            assert "PROC $" in item["RESOURCE_CATEGORY_CODE"]

def test_download_excel_criteria_scores(socom_client):
    payload = {
        "assessment_area_code":["C"],
        "program_group": ["OTHER"],
        "cycle_id":1
    }
    
    resp = socom_client.post("/socom/download/scores/excel",json=payload)
    print(resp.json)
    
    assert resp.status_code == 200
    
    buffer = BytesIO(resp.content)
    df = pd.read_excel(buffer)
    cols = df.columns.tolist()
    assert [col == "Weighting Criteria" or "OTHER" in col for col in cols] #program ids are OTHER_XX_XX encoded
    assert len(df["Weighting Criteria"].values.tolist()) > 0 #some criteria
    

def test_get_jca_description(socom_client):
    payload = ["1.1.0"]
    resp = socom_client.post("/socom/jca_description",json=payload)

    assert resp.status_code == 200

    resp_json = resp.json()

    expected_result = {
        "1.1.0": "FORCE SUPPORT, FORCE MANAGEMENT"
    }

    assert resp_json == expected_result

def test_get_jca_noncovered_by_ids(socom_client):
    payload = {
    "ids": [
      "1.1.0"
    ],
    "level": 2
    }
    resp = socom_client.post("/socom/jca/noncovered",json=payload)

    assert resp.status_code == 200

    resp_json = resp.json()

    expected_result = [
        "1.4.0",
        "1.2.0",
        "1.3.0"
    ]

    assert set(expected_result).issubset(set(resp_json))

def test_get_cga_description(socom_client):
    payload = [
        "8"
    ]

    resp = socom_client.post("/socom/cga_description",json=payload)

    assert resp.status_code == 200

    resp_json = resp.json()

    expected_result = {
        "8": {
            "CGA_NAME": "2024-NSW-05",
            "GAP_DESCRIPTION": "We lack the ability to scuba dive.",
            "GROUP_ID": "8",
            "GROUP_DESCRIPTION": "We lack the ability to personally submerge in water."
        }
    }

    assert set(resp_json) == set(expected_result)

def test_get_cga_noncovered_ids(socom_client):
    payload = {
        "ids": [
            "13.56"
        ],
        "level": "gap_id"
        }
    resp = socom_client.post("/socom/cga/noncovered",json=payload)

    assert resp.status_code == 200

    resp_json = resp.json()

    expected_result =[
    "13.13",
    "13.61"
    ]

    payload_eight = {
    "ids": [
        "8.8"
    ],
    "level": "gap_id"
    }

    resp_eight = socom_client.post("/socom/cga/noncovered",json=payload_eight)

    resp_eight_json = resp_eight.json()

    expected_result_eight = [
          "8.101"
    ]
    assert set(expected_result).issubset(set(resp_json))
    assert set(expected_result_eight).issubset(set(resp_eight_json))

def test_get_kop_ksp_description(socom_client):
    payload = [
    "1.1.1.0"
    ]

    resp = socom_client.post("/socom/kop_ksp_description",json=payload)
    assert resp.status_code == 200
    resp_json = resp.json()

    expected_result = {
        "1.1.1.0": {
            "TYPE": "KOP",
            "CHILDREN": [
            "1.1.1.1",
            "1.1.1.2",
            "1.1.1.3"
            ],
            "DESCRIPTION": "Shoes are important to wear"
        }
        }
    assert set(resp_json) == set(expected_result)

def test_get_kp_noncovered_ids(socom_client):
    payload_lv3 = {
    "ids": ["1.1.1.0", "1.1.2.0"],
    "level": 3
    }

    resp_lv3 = socom_client.post("/socom/kop-ksp/noncovered",json=payload_lv3)
    assert resp_lv3.status_code == 200

    resp_json_lv3 = resp_lv3.json()

    expected_result_lv3 = [
    "1.1.3.0"
    ]
    
    payload_lv4 = {
    "ids": ["1.1.1.1", "1.1.1.2", "1.1.2.1"],
    "level": 4
    }

    resp_lv4 = socom_client.post("/socom/kop-ksp/noncovered",json=payload_lv4)
    assert resp_lv4.status_code == 200
    resp_json_lv4 = resp_lv4.json()

    expected_result_lv4 = [
    "1.1.2.2",
    "1.1.2.4",
    "1.1.1.3",
    "1.1.2.3"
    ]

    assert set(expected_result_lv3).issubset(set(resp_json_lv3))
    assert set(expected_result_lv4).issubset(set(resp_json_lv4))

def test_get_zbt_summary(socom_client):
    payload = {
 
        "CAPABILITY_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "POM_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "ASSESSMENT_AREA_CODE" : ["A", "B", "C", "D", "E"],
        "PROGRAM_GROUP" : ["STUFF"]
    }

    resp = socom_client.post("socom/zbt/program_summary",json=payload)
    assert resp.status_code == 200
    resp = resp.json()

    expected_result = [
                                {
              "PROGRAM_NAME": "STUFF ARMADILLO",
              "EOC_CODES": [
                "STUFFAA.QWE",
                "STUFFAA.XXX",
                "STUFFAA.QWR"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 36162,
                  "2028": 30430,
                  "2029": 13010,
                  "2030": 13030,
                  "2031": 13050
                },
                "27ZBT_REQUESTED": {
                  "2027": 28755,
                  "2028": 26846,
                  "2029": 11709,
                  "2030": 11504,
                  "2031": 11299
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -7407,
                  "2028": -3584,
                  "2029": -1301,
                  "2030": -1526,
                  "2031": -1751
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF CAT",
              "EOC_CODES": [
                "STUFFCAT.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "FORCE APPLICATION, MANEUVER, MANEUVER TO ENGAGE (MTE)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 11591,
                  "2028": 11645,
                  "2029": 11699,
                  "2030": 11753,
                  "2031": 11807
                },
                "27ZBT_REQUESTED": {
                  "2027": 12912,
                  "2028": 13259,
                  "2029": 13607,
                  "2030": 13955,
                  "2031": 14303
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 1321,
                  "2028": 1614,
                  "2029": 1908,
                  "2030": 2202,
                  "2031": 2496
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF CRICKET",
              "EOC_CODES": [
                "STUFFCRI.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 10460,
                  "2028": 10520,
                  "2029": 10580,
                  "2030": 10640,
                  "2031": 10700
                },
                "27ZBT_REQUESTED": {
                  "2027": 9866,
                  "2028": 9590,
                  "2029": 9314,
                  "2030": 9038,
                  "2031": 8761
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -594,
                  "2028": -930,
                  "2029": -1266,
                  "2030": -1602,
                  "2031": -1939
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF GRASSHOPPER",
              "EOC_CODES": [
                "STUFFGRA.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BUILDING PARTNERSHIPS, COMMUNICATE",
                "FORCE APPLICATION",
                "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 18068,
                  "2028": 18214,
                  "2029": 18360,
                  "2030": 18506,
                  "2031": 18652
                },
                "27ZBT_REQUESTED": {
                  "2027": 20661,
                  "2028": 21486,
                  "2029": 22311,
                  "2030": 23137,
                  "2031": 23962
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 2593,
                  "2028": 3272,
                  "2029": 3951,
                  "2030": 4631,
                  "2031": 5310
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF HOPEFUL6",
              "EOC_CODES": [
                "STUFFHOP.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, BA DATA DISSEMINATION AND RELAY"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 17516,
                  "2028": 17608,
                  "2029": 17700,
                  "2030": 17792,
                  "2031": 17884
                },
                "27ZBT_REQUESTED": {
                  "2027": 19503,
                  "2028": 20405,
                  "2029": 21307,
                  "2030": 22209,
                  "2031": 23111
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 1987,
                  "2028": 2797,
                  "2029": 3607,
                  "2030": 4417,
                  "2031": 5227
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF JACKAL",
              "EOC_CODES": [
                "STUFFJJ.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "FORCE SUPPORT, FORCE PREPARATION, DOCTRINE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF JEALOUS9",
              "EOC_CODES": [
                "STUFFJEA.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, PRODUCT GENERATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 8828,
                  "2028": 8959,
                  "2029": 9090,
                  "2030": 9221,
                  "2031": 9352
                },
                "27ZBT_REQUESTED": {
                  "2027": 7948,
                  "2028": 7822,
                  "2029": 7695,
                  "2030": 7569,
                  "2031": 7442
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -880,
                  "2028": -1137,
                  "2029": -1395,
                  "2030": -1652,
                  "2031": -1910
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF KANGAROO",
              "EOC_CODES": [
                "STUFFKK.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, PRODUCT GENERATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 570,
                  "2028": 585,
                  "2029": 600,
                  "2030": 615,
                  "2031": 630
                },
                "27ZBT_REQUESTED": {
                  "2027": 684,
                  "2028": 722,
                  "2029": 761,
                  "2030": 800,
                  "2031": 839
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 114,
                  "2028": 137,
                  "2029": 161,
                  "2030": 185,
                  "2031": 209
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF LEOPARD2",
              "EOC_CODES": [
                "STUFFLEO.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
                "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 4485,
                  "2028": 4509,
                  "2029": 4533,
                  "2030": 4557,
                  "2031": 4581
                },
                "27ZBT_REQUESTED": {
                  "2027": 4710,
                  "2028": 4921,
                  "2029": 5133,
                  "2030": 5345,
                  "2031": 5556
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 225,
                  "2028": 412,
                  "2029": 600,
                  "2030": 788,
                  "2031": 975
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF LONELY",
              "EOC_CODES": [
                "STUFFLON.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "CORPORATE MANAGEMENT AND SUPPORT, PROGRAM, BUDGET AND FINANCE, ACCOUNTING AND FINANCE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 13049,
                  "2028": 13081,
                  "2029": 13113,
                  "2030": 13145,
                  "2031": 13177
                },
                "27ZBT_REQUESTED": {
                  "2027": 13733,
                  "2028": 14231,
                  "2029": 14730,
                  "2030": 15228,
                  "2031": 15727
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 684,
                  "2028": 1150,
                  "2029": 1617,
                  "2030": 2083,
                  "2031": 2550
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF MUG7",
              "EOC_CODES": [
                "STUFFMUG.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 1884,
                  "2028": 1916,
                  "2029": 1948,
                  "2030": 1980,
                  "2031": 2012
                },
                "27ZBT_REQUESTED": {
                  "2027": 1623,
                  "2028": 1613,
                  "2029": 1603,
                  "2030": 1592,
                  "2031": 1582
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -261,
                  "2028": -303,
                  "2029": -345,
                  "2030": -388,
                  "2031": -430
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF NEW HAMPSHIRE 1",
              "EOC_CODES": [
                "STUFFNH.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "LOGISTICS, MAINTAIN, FIELD MAINTENANCE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 805,
                  "2028": 905,
                  "2029": 1005,
                  "2030": 1015,
                  "2031": 1025
                },
                "27ZBT_REQUESTED": {
                  "2027": 725,
                  "2028": 804,
                  "2029": 883,
                  "2030": 871,
                  "2031": 860
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -80,
                  "2028": -101,
                  "2029": -122,
                  "2030": -144,
                  "2031": -165
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF NEW HAMPSHIRE 2",
              "EOC_CODES": [
                "STUFFNZ.YYZ",
                "STUFFNZ.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BUILDING PARTNERSHIPS, SHAPE, LEVERAGE CAPACITIES AND CAPABILITIES OF SECURITY ESTABLISHMENTS"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 508,
                  "2028": 518,
                  "2029": 528,
                  "2030": 538,
                  "2031": 548
                },
                "27ZBT_REQUESTED": {
                  "2027": 668,
                  "2028": 702,
                  "2029": 737,
                  "2030": 772,
                  "2031": 807
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 160,
                  "2028": 184,
                  "2029": 209,
                  "2030": 234,
                  "2031": 259
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF RABBIT4",
              "EOC_CODES": [
                "STUFFRAB.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "FORCE SUPPORT, FORCE PREPARATION, DOCTRINE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 6753,
                  "2028": 6849,
                  "2029": 6945,
                  "2030": 7041,
                  "2031": 7137
                },
                "27ZBT_REQUESTED": {
                  "2027": 7675,
                  "2028": 7979,
                  "2029": 8284,
                  "2030": 8588,
                  "2031": 8893
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 922,
                  "2028": 1130,
                  "2029": 1339,
                  "2030": 1547,
                  "2031": 1756
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF URSULA",
              "EOC_CODES": [
                "STUFFUU.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                }
              }
            }
    ]

    for i in range(len(expected_result)):
        expected_result[i]["EOC_CODES"] = set(expected_result[i]["EOC_CODES"])
        resp[i]["EOC_CODES"] = set(resp[i]["EOC_CODES"])

        expected_result[i]["JCA_ALIGNMENT"] = set(expected_result[i]["JCA_ALIGNMENT"])
        resp[i]["JCA_ALIGNMENT"] = set(resp[i]["JCA_ALIGNMENT"])

    def normalize_data(item):
        if isinstance(item, dict):
            return {k: normalize_data(v) for k, v in item.items()}
        elif isinstance(item, list):
            return [normalize_data(v) for v in item]
        elif isinstance(item, float) and item.is_integer():
            return int(item)
        return item

    normalized_resp = normalize_data(resp)
    normalized_expected = normalize_data(expected_result)

    assert normalized_resp == normalized_expected

def test_get_iss_summary(socom_client):

    payload = {
        "CAPABILITY_SPONSOR_CODE": ["AT&L","AFSOC","NSW", "USASOC", "MARSOC"],
        "POM_SPONSOR_CODE": ["AT&L","AFSOC","NSW", "USASOC", "MARSOC"],
        "ASSESSMENT_AREA_CODE": ["A","B","C","D","E"],
        "PROGRAM_GROUP": ["OTHER"],
        "PROGRAM_NAME": ["OTHER ACTIONS"]
    }

    resp = socom_client.post("socom/iss/program_summary",json=payload)
    assert resp.status_code == 200
    resp = resp.json()

    expected_result = [
        {
            "PROGRAM_NAME": "OTHER ACTIONS",
            "EOC_CODES": [
            "OTHERAA.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "OTHER",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
            "COMMAND AND CONTROL, ORGANIZE, FOSTER ORGANIZATIONAL COLLABORATION"
            ],
            "RESOURCE_K": {
            "26EXT": {
                "2026": 763,
                "2027": 863,
                "2028": 963,
                "2029": 1063,
                "2030": 1163
            },
            "26ZBT": {
                "2026": 691,
                "2027": 791,
                "2028": 891,
                "2029": 991,
                "2030": 1091
            },
            "26ZBT_DELTA": {
                "2026": -72,
                "2027": -72,
                "2028": -72,
                "2029": -72,
                "2030": -72
            },
            "26ISS_REQUESTED": {
                "2026": 806,
                "2027": 916,
                "2028": 1026,
                "2029": 1136,
                "2030": 1246
            },
            "26ISS_REQUESTED_DELTA": {
                "2026": 115,
                "2027": 125,
                "2028": 135,
                "2029": 145,
                "2030": 155
            }
            }
        }
    ]

    for i in range(len(expected_result)):
        expected_result[i]["EOC_CODES"] = set(expected_result[i]["EOC_CODES"])
        resp[i]["EOC_CODES"] = set(resp[i]["EOC_CODES"])

        expected_result[i]["JCA_ALIGNMENT"] = set(expected_result[i]["JCA_ALIGNMENT"])
        resp[i]["JCA_ALIGNMENT"] = set(resp[i]["JCA_ALIGNMENT"])

    def normalize_data(item):
        if isinstance(item, dict):
            return {k: normalize_data(v) for k, v in item.items()}
        elif isinstance(item, list):
            return [normalize_data(v) for v in item]
        elif isinstance(item, float) and item.is_integer():
            return int(item)
        return item

    normalized_resp = normalize_data(resp)
    normalized_expected = normalize_data(expected_result)

    assert normalized_resp == normalized_expected

def test_get_metadata(socom_client):
    payload = {
        "PROGRAM_GROUP": ["OTHER"],
        "PROGRAM_CODE": ["OTHERAA"],
        "CAPABILITY_SPONSOR_CODE": ["AT&L","AFSOC","NSW", "USASOC", "MARSOC"],
        "POM_SPONSOR_CODE": ["AT&L","AFSOC","NSW", "USASOC", "MARSOC"],
        "ASSESSMENT_AREA_CODE": ["C"]
    }

    resp = socom_client.post("/socom/metadata",json=payload)
    assert resp.status_code == 200

    resp = resp.json()

    expected_result = [
            {
                "ID": "OTHERAA_USASOC_USASOC",
                "PROGRAM_GROUP": "OTHER",
                "PROGRAM_CODE": "OTHERAA",
                "PROGRAM_NAME": "OTHER ACTIONS",
                "PROGRAM_TYPE_CODE": "O",
                "PROGRAM_SUB_TYPE_CODE": "A",
                "PROGRAM_DESCRIPTION": "txt",
                "CAPABILITY_SPONSOR_CODE": "USASOC",
                "ASSESSMENT_AREA_CODE": "C",
                "POM_SPONSOR_CODE": "USASOC",
                "JCA_LV1_ID": "5",
                "JCA_LV2_ID": "1",
                "JCA_LV3_ID": "3",
                "STORM_ID": "OTHER_USASOC_1",
                "EOC_CODE": "OTHERAA.XXX",
                "RESOURCE_CATEGORY_CODE": "PROC $"
            }
    ]
    
    for i in range(len(expected_result)):
        if "EOC_CODES" in expected_result[i]:
            expected_result[i]["EOC_CODES"] = set(expected_result[i]["EOC_CODES"])
            resp[i]["EOC_CODES"] = set(resp[i]["EOC_CODES"])

        if "JCA_ALIGNMENT" in expected_result[i]:
            expected_result[i]["JCA_ALIGNMENT"] = set(expected_result[i]["JCA_ALIGNMENT"])
            resp[i]["JCA_ALIGNMENT"] = set(resp[i]["JCA_ALIGNMENT"])

    def normalize_data(item):
        if isinstance(item, dict):
            return {k: normalize_data(v) for k, v in item.items()}
        elif isinstance(item, list):
            return [normalize_data(v) for v in item]
        elif isinstance(item, float) and item.is_integer():
            return int(item)
        return item

    normalized_resp = normalize_data(resp)
    normalized_expected = normalize_data(expected_result)

    assert normalized_resp == normalized_expected

# Checking failure test

def test_get_eoc_funding_failure(socom_client):
    payload = "invalid"
    resp = socom_client.post('/socom/prog_eoc_funding', json=payload)
    assert resp.status_code == 422

def test_get_jca_noncovered_by_ids_failure(socom_client):

    payload_level_1 = {
        "ids": [
            "1.1"  
        ],
        "level": 1
    }
    resp_level_1 = socom_client.post("/socom/jca/noncovered", json=payload_level_1)
    assert resp_level_1.status_code == 422
    assert "Invalid ids input for level 1" in resp_level_1.text

    payload_level_3 = {
        "ids": [
            "1.1.0"  
        ],
        "level": 3
    }
    resp_level_3 = socom_client.post("/socom/jca/noncovered", json=payload_level_3)
    assert resp_level_3.status_code == 422
    assert "Invalid ids input for level 3" in resp_level_3.text

    payload_level_2 = {
        "ids": [
            "1.1"
        ],
        "level": 2
    }
    resp_level_2 = socom_client.post("/socom/jca/noncovered", json=payload_level_2)
    assert resp_level_2.status_code == 422

def test_get_kp_noncovered_ids_failure(socom_client):
    payload_level_3_invalid = {
        "ids": [
            "1.2.3"
        ],
        "level": 3
    }
    resp_level_3 = socom_client.post("/socom/kop-ksp/noncovered", json=payload_level_3_invalid)
    assert resp_level_3.status_code == 422

    payload_level_4_invalid = {
        "ids": [
            "1.2.3.0"
        ],
        "level": 4
    }
    resp_level_4 = socom_client.post("/socom/kop-ksp/noncovered", json=payload_level_4_invalid)
    assert resp_level_4.status_code == 422

def test_get_cga_noncovered_ids_failure(socom_client):
    payload_group_id_invalid = {
        "ids": [
            "1.1"
        ],
        "level": "group_id"
    }
    resp_group_id = socom_client.post("/socom/cga/noncovered", json=payload_group_id_invalid)
    assert resp_group_id.status_code == 422

    payload_gap_id_invalid = {
        "ids": [
            "11"
        ],
        "level": "gap_id"
    }
    resp_gap_id = socom_client.post("/socom/cga/noncovered", json=payload_gap_id_invalid)
    assert resp_gap_id.status_code == 422

def test_get_zbt_summary_failure(socom_client):
    payload = "invalid"
    resp = socom_client.post("socom/zbt/program_summary", json=payload)
    assert resp.status_code == 422

def test_get_iss_summary_failure(socom_client):
    payload = {
        "CAPABILITY_SPONSOR_CODE": ["AT&L"],
        "POM_SPONSOR_CODE": ["NSW"],
        "ASSESSMENT_AREA_CODE": ["C"],
        "PROGRAM_GROUP": ["INVALID_GROUP"],
        "PROGRAM_NAME": ["OTHER ACTIONS"]
    }
    resp = socom_client.post("socom/iss/program_summary", json=payload)
    assert resp.status_code == 200
    assert resp.json() == []

def test_get_metadata_failure(socom_client):
    payload = {
        "additionalProp1": [
            "string"
        ],
        "additionalProp2": [
            "string"
        ],
        "additionalProp3": [
            "string"
        ]
    }
    resp = socom_client.post("/socom/metadata", json=payload)
    assert resp.status_code == 422
