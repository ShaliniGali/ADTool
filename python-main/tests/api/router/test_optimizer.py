import pytest
import optimizer as test
from fastapi import HTTPException

def test_calculate_budget(socom_client):
    payload =     {
        "eyr": 2031,
        "syr": 2026, 
        "budget": [ 
            19999,
            19999,
            19999,
            19999,
            19999
        ],
        "option": 1,
        "criteria_name_id":1, 
        "score_id": [
            {
                "USER_ID": 5, 
                "PROGRAM_ID": "5GBEA_MARSOC_AFSOC" 
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "5GBEE_MARSOC_MARSOC"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "5GBOR_AFSOC_MARSOC"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "OTHERAA_USASOC_USASOC"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "OTHERDD_USASOC_USASOC"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "OTHERII_NSW_NSW"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "OTHERIZ_AFSOC_NSW"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "PRIVACYCAT_AT&L_AFSOC"
            },
            {
                "USER_ID": 5,
                "PROGRAM_ID": "YUMMYCH_AFSOC_AFSOC"
            }
        ],
        "weight_id": 119, 
        "ProgramIDs": [ 
            "5GBEA_MARSOC_AFSOC",
            "5GBEE_MARSOC_MARSOC",
            "5GBOR_AFSOC_MARSOC",
            "OTHERAA_USASOC_USASOC",
            "OTHERDD_USASOC_USASOC",
            "OTHERII_NSW_NSW",
            "OTHERIZ_AFSOC_NSW",
            "PRIVACYCAT_AT&L_AFSOC",
            "YUMMYCH_AFSOC_AFSOC"
        ],
        "storm_flag": "true", 
        "must_exclude": [], 
        "must_include": [ 
            "OTHERDD_USASOC_USASOC"
        ],
        "support_all_years": "true" 
    }

    response = socom_client.post('/optimizer/calculate_budget', json=payload)
    assert response.status_code == 200

    result = response.json()

    expected_result = {'resource_k': {'2026': 
        {'OTHERDD_USASOC_USASOC': 626,
        'OTHERIZ_AFSOC_NSW': 0,
        'OTHERAA_USASOC_USASOC': 629,
        'YUMMYCH_AFSOC_AFSOC': 1211,
        '5GBEE_MARSOC_MARSOC': 4916},
        '2027': {'OTHERDD_USASOC_USASOC': 666,
        'OTHERIZ_AFSOC_NSW': 0,
        'OTHERAA_USASOC_USASOC': 729,
        'YUMMYCH_AFSOC_AFSOC': 0,
        '5GBEE_MARSOC_MARSOC': 4946},
        '2028': {'OTHERDD_USASOC_USASOC': 706,
        'OTHERIZ_AFSOC_NSW': 0,
        'OTHERAA_USASOC_USASOC': 829,
        'YUMMYCH_AFSOC_AFSOC': 0,
        '5GBEE_MARSOC_MARSOC': 4976},
        '2029': {'OTHERDD_USASOC_USASOC': 746,
        'OTHERIZ_AFSOC_NSW': 0,
        'OTHERAA_USASOC_USASOC': 929,
        'YUMMYCH_AFSOC_AFSOC': 0,
        '5GBEE_MARSOC_MARSOC': 5006},
        '2030': {'OTHERDD_USASOC_USASOC': 786,
        'OTHERIZ_AFSOC_NSW': 0,
        'OTHERAA_USASOC_USASOC': 1029,
        'YUMMYCH_AFSOC_AFSOC': 0,
        '5GBEE_MARSOC_MARSOC': 5036}},
        'selected_programs': [{'program_id': 'OTHERDD_USASOC_USASOC',
        'pom_sponsor': 'USASOC',
        'capability_sponsor': 'USASOC',
        'weighted_guidance_score': 0.5,
        'weighted_pom_score': 0.5,
        'resource_k': {'2030': 786,
            '2029': 746,
            '2028': 706,
            '2027': 666,
            '2026': 626},
        'total_storm_score': 80.0},
        {'program_id': 'OTHERIZ_AFSOC_NSW',
        'pom_sponsor': 'AFSOC',
        'capability_sponsor': 'NSW',
        'weighted_guidance_score': 1.0,
        'weighted_pom_score': 1.0,
        'resource_k': {'2030': 0, '2029': 0, '2028': 0, '2027': 0, '2026': 0},
        'total_storm_score': 73.0},
        {'program_id': 'OTHERAA_USASOC_USASOC',
        'pom_sponsor': 'USASOC',
        'capability_sponsor': 'USASOC',
        'weighted_guidance_score': 1.0,
        'weighted_pom_score': 1.0,
        'resource_k': {'2030': 1029,
            '2029': 929,
            '2028': 829,
            '2027': 729,
            '2026': 629},
        'total_storm_score': 71.0},
        {'program_id': 'YUMMYCH_AFSOC_AFSOC',
        'pom_sponsor': 'AFSOC',
        'capability_sponsor': 'AFSOC',
        'weighted_guidance_score': 13.0,
        'weighted_pom_score': 13.0,
        'resource_k': {'2030': 0, '2029': 0, '2028': 0, '2027': 0, '2026': 1211},
        'total_storm_score': 62.0},
        {'program_id': '5GBEE_MARSOC_MARSOC',
        'pom_sponsor': 'MARSOC',
        'capability_sponsor': 'MARSOC',
        'weighted_guidance_score': 1.05,
        'weighted_pom_score': 1.05,
        'resource_k': {'2030': 5036,
            '2029': 5006,
            '2028': 4976,
            '2027': 4946,
            '2026': 4916},
        'total_storm_score': 57.0}],
            'remaining': {'2026': 12617,
            '2027': 13658,
            '2028': 13488,
            '2029': 13318,
            '2030': 13148
    }}
    assert result == expected_result

    payload_failure = {}

    resp_failure = socom_client.post('/optimizer/calculate_budget',json=payload_failure)
    assert resp_failure.status_code == 422

def test_filter_optimizer_output(socom_client):
    payload = {
        "model": {
            "resource_k": {
                "2026": {
                    "AircraftXXX_AFSOC_AFSOC": 50000,
                    "AircraftXXX_AFSOC_MARSOC": 0,
                    "AircraftXXX_AFSOC_AT&L": 20000
                },
                "2027": {
                    "AircraftXXX_AFSOC_AFSOC": 60000,
                    "AircraftXXX_AFSOC_MARSOC": 0,
                    "AircraftXXX_AFSOC_AT&L": 30000
                }
            },
            "selected_programs": [
                {
                    "program_id": "AircraftXXX_AFSOC_AFSOC",
                    "pom_sponsor": "AFSOC",
                    "capability_sponsor": "AFSOC",
                    "weighted_guidance_score": 85.5,
                    "weighted_pom_score": 90.0,
                    "resource_k": {
                        "2026": 50000,
                        "2027": 60000,
                        "2028": 70000
                    },
                    "total_storm_score": 100
                }
            ],
            "remaining": {
                "2026": 50000,
                "2027": 60000,
                "2028": 70000
            }
        },
        "filter": {
            "filter_zero_resource_k": True
        }
    }

    response = socom_client.post('/optimizer/filter_budget', json=payload)

    assert response.status_code == 200
    response_data = response.json()

    expected_output = {
        "resource_k": {
            "2026": {
                "AircraftXXX_AFSOC_AFSOC": 50000,
                "AircraftXXX_AFSOC_MARSOC": 0,
                "AircraftXXX_AFSOC_AT&L": 20000
            },
            "2027": {
                "AircraftXXX_AFSOC_AFSOC": 60000,
                "AircraftXXX_AFSOC_MARSOC": 0,
                "AircraftXXX_AFSOC_AT&L": 30000
            }
        },
        "selected_programs": [
            {
                "program_id": "AircraftXXX_AFSOC_AFSOC",
                "pom_sponsor": "AFSOC",
                "capability_sponsor": "AFSOC",
                "weighted_guidance_score": 85.5,
                "weighted_pom_score": 90.0,
                "resource_k": {
                    "2026": 50000,
                    "2027": 60000,
                    "2028": 70000
                },
                "total_storm_score": 100
            }
        ],
        "remaining": {
            "2026": 50000,
            "2027": 60000,
            "2028": 70000
        },
        "filter": {
            "filter_zero_resource_k": {
                "removed_programs_id": []
            }
        }
    }

    assert response_data == expected_output 
    
    payload_failure = {}
    response_failure = socom_client.post('/optimizer/filter_budget', json=payload_failure)
    assert response_failure.status_code == 422

def test_optimizer_saved_coa_score(socom_client):
    payload = {
        "weight_id": 74,
        "user_id": 14,
        "program_ids": [
            "OTHERIZ_AFSOC_NSW"
        ],
        "criteria_name_id":1
    }
    
    response = socom_client.post('/optimizer/weighted_scores', json=payload)

    assert response.status_code == 200

    result = response.json()
    # print(result)
    expected_result = {
        "OTHERIZ_AFSOC_NSW": {
            "total_storm_scores": 73,
            "weighted_pom_score": 24,
            "weighted_guidance_score": 23.7
        }
    }

    assert result == expected_result

    payload_failure = {
        "weight_id": 0,
        "user_id": 0,
        "program_ids": [
            "string"
        ]
    }

    resp_failure = socom_client.post('/optimizer/weighted_scores', json=payload_failure)
    assert resp_failure.status_code == 422


#refactor to show programs per alignment instead of total sum
def sum_tiers(d):
    new_d = {}
    for sel in d:
        new_d[sel] = new_d.get(sel,{})
        for tier in d[sel]:
            new_d[sel][tier] = new_d[sel].get(tier,{})
            for node in d[sel][tier]:
                # new_d[sel][tier][node] = new_d[sel].get(tier,{})
                new_d[sel][tier][node] = round(sum(d[sel][tier][node].values()),2)
    return {'absolute_alignment':new_d}

def test_get_jca_alignment(socom_client):
    id = 1675
    resp = socom_client.get(f"/optimizer/jca_alignment/opt-run?id={id}")
    result = resp.json()
    # print(result)
    expected_result = {
        "absolute_alignment": {
            "selected_programs": {
            "third_tier": {
                "5.1.3": 369.86,
                "2.4.3": 369.86,
                "2.5.0": 369.86,
                "1.1.2": 369.86,
                "3.1.1": 369.86,
                "4.3.2": 369.86,
                "6.2.2": 369.86,
                "8.1.0": 2437.5,
                "8.2.2": 2437.5,
                "9.5.2": 950.0
            },
            "second_tier": {
                "5.1": 369.86,
                "2.4": 369.86,
                "2.5": 369.86,
                "1.1": 369.86,
                "3.1": 369.86,
                "4.3": 369.86,
                "6.2": 369.86,
                "8.1": 2437.5,
                "8.2": 2437.5,
                "9.5": 950.0
            },
            "first_tier": {
                "5": 369.86,
                "2": 739.72,
                "1": 369.86,
                "3": 369.86,
                "4": 369.86,
                "6": 369.86,
                "8": 4875.0,
                "9": 950.0
            }
            },
            "unselected_programs": {
            "third_tier": {
                "8.2.2": 21225.67,
                "4.1.2": 46105.67,
                "1.4.0": 21225.67,
                "2.4.3": 129916.0,
                "1.2.4": 3585.0,
                "2.5.0": 1211.0
            },
            "second_tier": {
                "8.2": 21225.67,
                "4.1": 46105.67,
                "1.4": 21225.67,
                "2.4": 129916.0,
                "1.2": 3585.0,
                "2.5": 1211.0
            },
            "first_tier": {
                "8": 21225.67,
                "4": 46105.67,
                "1": 24810.67,
                "2": 131127.0
            }
            }
        }
        }


    result = sum_tiers(result['absolute_alignment'])
    # breakpoint()
    assert result == expected_result

def test_get_cga_alignment(socom_client):
    id = 1675
    resp = socom_client.get(f"/optimizer/cga_alignment/opt-run?id={id}")
    result = resp.json()

    expected_result = {
        "absolute_alignment": {
            "selected_programs": {
            "second_tier": {
                "35.35": 2589.0,
                "10.10": 950.0
            },
            "first_tier": {
                "35": 2589.0,
                "10": 950.0
            }
            },
            "unselected_programs": {
            "second_tier": {
                "32.116": 21225.67,
                "20.20": 21225.67,
                "39.39": 23018.17,
                "21.83": 1792.5,
                "36.49": 1211.0
            },
            "first_tier": {
                "32": 21225.67,
                "20": 21225.67,
                "39": 23018.17,
                "21": 1792.5,
                "36": 1211.0
            }
            }
        }
        }

    result = sum_tiers(result['absolute_alignment'])
    # print(result)
    assert result == expected_result

def test_get_kop_ksp_alignment(socom_client):
    id = 1675

    resp = socom_client.get(f"/optimizer/kop_ksp_alignment/opt-run?id={id}")
    result = resp.json()

    expected_result = {
        "absolute_alignment": {
            "selected_programs": {
            "fourth_tier": {
                "2.1.2.2": 4875.0,
                "1.2.1.3": 950.0
            },
            "third_tier": {
                "2.1.2": 4875.0,
                "1.2.1": 950.0
            }
            },
            "unselected_programs": {
            "fourth_tier": {
                "1.1.1.0": 65953.4,
                "1.1.1.2": 12735.4,
                "1.1.2.3": 12735.4,
                "1.2.1.2": 12735.4,
                "2.2.1.2": 12735.4,
                "2.1.3.0": 53218.0,
                "1.3.1.4": 24880.0
            },
            "third_tier": {
                "1.1.1": 78688.8,
                "1.1.2": 12735.4,
                "1.2.1": 12735.4,
                "2.2.1": 12735.4,
                "2.1.3": 53218.0,
                "1.3.1": 24880.0
            }
            }
        }
        }

    result = sum_tiers(result['absolute_alignment'])
    # print(result)
    assert result == expected_result

