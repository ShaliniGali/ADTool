<?php
/**
 * @group SOCOMmodel
 */
class SOCOM_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        $this->obj = new SOCOM_model();

        $this->obj->DBs->SOCOM_UI = $this->getMethodChainingDBMock();

    }

    /*
    public function test_cap_sponsor_count() {
        $inner_query = '';
        $count = [
            [
                'CAPABILITY_SPONSOR_CODE' => '12',
                'ZBT_COUNT' => '1'
            ]
        ];
        $cap_sponsor_row = array(
            'TOTAL_ZBT_EVENTS' => [
                'CAPABILITY_SPONSOR_CODE' => '12',
                'ZBT_COUNT' => 1
            ]
        );
        
        $result = [
            'cap_sponsor_count' => [
                [
                    '12', 1
                ]
                ],
            'total_zbt_events' => [
                'CAPABILITY_SPONSOR_CODE' => '12',
                'ZBT_COUNT' => 1
            ]
            ];
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('result_array')->willReturn($count);
        
        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $dbResult2 = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult2->method('row_array')->willReturn($cap_sponsor_row);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult2);


        $actual = $this->obj->cap_sponsor_count('DT_EXT_2026');
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }*/
    
    public function test_cap_sponsor_dollar() {
        $inner_query = '';
        $count = [
            [
                'CAPABILITY_SPONSOR_CODE' => '12',
                'SUM_DELTA_AMT' => '1'
            ]
        ];
        $cap_sponsor_row = array(
            'TOTAL_POS_DOLLARS' => '1'
        );
        
        $result = [
            'cap_sponsor_dollar' => [
                [
                    'name' => '',
                    'y' => 1,
                    'color' => '#'
                ]
                ],
            'dollars_moved' => '1'
        ];
        // MonkeyPatch::patchMethod(
        //     'WhatIf',
        //     ['_get_input_from_session' => $session_data]
        // );
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('result_array')->willReturn($count);
        
        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $dbResult2 = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult2->method('row_array')->willReturn($cap_sponsor_row);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult2);


        $actual = $this->obj->cap_sponsor_dollar('DT_EXT_2026');
            
        $expected = $result;
        $hex_color_regex = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';

        $this->assertEquals(count($expected), count($actual));
        $this->assertMatchesRegularExpression($hex_color_regex, $actual['cap_sponsor_dollar'][0]['color']);

    }
    
    public function test_cap_sponsor_dollar_magnitude() {
        $inner_query = '';
        $count = [
            [
                'CAPABILITY_SPONSOR_CODE' => '12',
                'SUM_DELTA_AMT' => '1000000000'
            ]
        ];
        $cap_sponsor_row = array(
            'TOTAL_POS_DOLLARS' => '1000000000'
        );
        
        $result = [
            'cap_sponsor_dollar' => [
                [
                    'name' => '',
                    'y' => 1000000000,
                    'color' => '#'
                ]
                ],
            'dollars_moved' => '1.0B'
        ];
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('result_array')->willReturn($count);
        
        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $dbResult2 = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult2->method('row_array')->willReturn($cap_sponsor_row);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult2);


        $actual = $this->obj->cap_sponsor_dollar('DT_EXT_2026');
            
        $expected = $result;
        $hex_color_regex = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';

        $this->assertEquals(count($expected), count($actual));
        $this->assertMatchesRegularExpression($hex_color_regex, $actual['cap_sponsor_dollar'][0]['color']);

    }
    
    public function test_net_change() {
        $delta = array(
            'SUM_DELTA_AMT' => '1000000000'
        );
        
        $result = '1.0B';

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult->method('row_array')->willReturn($delta);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->net_change('DT_EXT_2026');
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }
    
    public function test_dollars_moved_resource_category() {
        $inner_query = '';
        
        $result = [
            [
                'FISCAL_YEAR' => '2023',
                'RESOURCE_CATEGORY_CODE' => '1',
                'SUM_DELTA_AMT' => 2
            ],
            [
                'FISCAL_YEAR' => '2024',
                'RESOURCE_CATEGORY_CODE' => '1',
                'SUM_DELTA_AMT' => 2
            ]
        ];
        
        
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('result_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);



        $actual = $this->obj->dollars_moved_resource_category('DT_EXT_2026');
            
        $expected = [
            'fiscal_years' => [
                2023, 2024
            ],
            'series_data' => [
                [
                    'name' => '1',
                    'data' => [
                        2, 2
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }
    
    public function test_cap_sponsor_approve_reject() {
        $actual_data = [
          [
            'CAPABILITY_SPONSOR_CODE' => '2',
            'EVENT_STATUS' => 'event1',
            'ZBT_COUNT' => '1'
          ],
          [
            'CAPABILITY_SPONSOR_CODE' => '2',
            'EVENT_STATUS' => 'event1',
            'ZBT_COUNT' => '1'
          ]    
        ];
        
        $result = [
            'categories' => [
                '2'
            ],
            'series_data' => [
                [
                    'name' => 'Event1',
                    'data' => [
                        '1', '1'
                    ]
                ]
            ]
        ];

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult->method('result_array')->willReturn($actual_data);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->cap_sponsor_approve_reject('DT_EXT_2026');
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_sponsor() {
        
        $result = [
            [
                'SPONSOR_CODE' => '2',
                'SPONSOR_TITLE' => 'ARMY'
            ]
        ];

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_sponsor('LOOKUP_SPONSOR', 'ARMY');
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }
    public function test_get_assessment_area_code() {
        
        $result = [
            [
                'ASSESSMENT_AREA_CODE' => 'A',
                'ASSESSMENT_AREA' => 'A'
            ]
        ];

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_assessment_area_code();
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_user_assigned_tag() {
        
        $result = [];

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_user_assigned_tag('TAG');
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }
    public function test_get_user_assigned_bin() {
        
        $result = [];

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
    
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_user_assigned_bin('BIN');
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }
    /*
    public function test_get_program_summary() {
        $inner_query = '';
        $query_result = [0];
        $result = [
            'base_k' => [0],
            'prop_amt' =>  [0],
            'delta_amt' =>  [0]
        ];
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);
        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
    
        $dbResult->method('result_array')->willReturn($query_result);
        $this->obj->DBs->SOCOM_UI
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_program_summary();
            
        $expected = $result;
        $this->assertEquals($expected, $actual);
    }*/

    public function test_get_resource_category_code() {    
        $queryResult = $this->getMockBuilder('CI_DB_result')
                            ->disableOriginalConstructor()
                            ->getMock();
    
        $this->obj->DBs->SOCOM_UI->method('get')
                       ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI->method('select')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('from')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('order_by')
            ->willReturnSelf();
    
        $this->obj->get_resource_category_code($program_code);

        $this->assertTrue(TRUE);
    }

    public function test_zbt_summary_program_summary_card() {
        $table1 = 'table1';
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $status = TRUE;
        $program_list = ['PROGRAm_1'];

        MonkeyPatch::patchMethod(SOCOM_model::class, ['program_summary_count' => 1 ,
            'program_summary_dollars_moved' => 1.1, 
            'program_summary_net_change' => 1.1 ]);


        $this->obj->zbt_summary_program_summary_card($$table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $status, $program_list);

        $this->assertTrue(TRUE);
    }

    public function test_get_eoc() {
        $program_code = 'TestProgram';
    
        $expected_result = [
            [
                'JCA_LV1' => 'Level1',
                'JCA_LV2' => 'Level2',
                'JCA_LV3' => 'Level3',
            ],
        ];
    
        $queryResult = $this->getMockBuilder('CI_DB_result')
                            ->disableOriginalConstructor()
                            ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);
    
        $this->obj->DBs->SOCOM_UI->method('get')
                       ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI->method('select')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('distinct')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('from')
            ->with('LOOKUP_PROGRAM A')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('join')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('where')
            ->willReturnSelf();
    
        $this->obj->get_eoc($program_code);

        $this->assertTrue(TRUE);
    }


    public function test_get_zbt_summary_eoc() {
        $program_code = 'Program1';
        $l_pom_sponsor = ['Sponsor1', 'Sponsor2'];
        $l_cap_sponsor = ['CapSponsor1'];
        $l_ass_area = ['Area1', 'Area2'];
    
        $sub_query1 = "
            SELECT 
                * 
            FROM 
                DT_EXT_2026 
            WHERE 
                CAPABILITY_SPONSOR_CODE IN('CapSponsor1')
                AND POM_SPONSOR_CODE IN('Sponsor1','Sponsor2')
                AND ASSESSMENT_AREA_CODE  IN('Area1','Area2')
        ";
    
        $sub_query2 = "
            SELECT
                0 AS ADJUSTMENT_K,
                ASSESSMENT_AREA_CODE,
                0 AS BASE_K,
                BUDGET_ACTIVITY_CODE,
                BUDGET_ACTIVITY_NAME,
                BUDGET_SUB_ACTIVITY_CODE,
                BUDGET_SUB_ACTIVITY_NAME,
                CAPABILITY_SPONSOR_CODE,
                0 AS END_STRENGTH,
                EOC_CODE,
                EVENT_JUSTIFICATION,
                EVENT_NAME,
                EXECUTION_MANAGER_CODE,
                FISCAL_YEAR,
                LINE_ITEM_CODE,
                0 AS OCO_OTHD_ADJUSTMENT_K,
                0 AS OCO_OTHD_K,
                0 AS OCO_TO_BASE_K,
                OSD_PROGRAM_ELEMENT_CODE,
                POM_POSITION_CODE, 
                POM_SPONSOR_CODE,
                PROGRAM_CODE,
                PROGRAM_GROUP,
                RDTE_PROJECT_CODE,
                RESOURCE_CATEGORY_CODE,
                RESOURCE_K, 
                SPECIAL_PROJECT_CODE,
                SUB_ACTIVITY_GROUP_CODE,
                SUB_ACTIVITY_GROUP_NAME,
                2024 AS WORK_YEARS
            FROM
                DT_ZBT_EXTRACT_2026
            WHERE
                (
                    PROGRAM_CODE NOT IN (
                        SELECT
                            DISTINCT PROGRAM_CODE
                        FROM
                            DT_EXT_2026
                    )
                    OR EOC_CODE NOT IN (
                        SELECT
                            DISTINCT EOC_CODE
                        FROM
                            DT_EXT_2026
                    )
                ) 
                AND CAPABILITY_SPONSOR_CODE IN('CapSponsor1')
                AND POM_SPONSOR_CODE IN('Sponsor1','Sponsor2')
                AND ASSESSMENT_AREA_CODE  IN('Area1','Area2')
        ";
    
        $query2 = "
            SELECT DISTINCT B.EOC_CODE FROM LOOKUP_PROGRAM AS A
            LEFT JOIN (
                ${sub_query1}
                UNION ALL
                $sub_query2
            ) AS B ON 
            A.PROGRAM_CODE = B.PROGRAM_CODE
            WHERE PROGRAM_NAME = '${program_code}'
        ";
    
        $query = "SELECT GROUP_CONCAT(EOC_CODE SEPARATOR ', <br/>') AS EOC_CODE FROM ( ${query2} ) AS TEMP";
    
        $expected_result = [
            ['EOC_CODE' => 'EOC1, <br/>EOC2']
        ];
    
        $queryResult = $this->getMockBuilder('CI_DB_result')
                            ->disableOriginalConstructor()
                            ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);
    
        $this->obj->DBs->SOCOM_UI->method('query')
                       ->with($query)
                       ->willReturn($queryResult);
    
        $actual = $this->obj->get_zbt_summary_eoc($program_code, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area);
        $expected = $expected_result;
    
        // Assertions
        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected[0]['EOC_CODE'], $actual[0]['EOC_CODE']);
    }
    
    public function test_get_issue_eoc() {
        $program_code = 'Program1';
        $l_pom_sponsor = ['Sponsor1', 'Sponsor2'];
        $l_cap_sponsor = ['CapSponsor1'];
        $l_ass_area = ['Area1', 'Area2'];

        $expected_result = [
            ['EOC_CODE' => 'EOC1, <br/>EOC2']
        ];

        $queryResult = $this->getMockBuilder('CI_DB_result')
                            ->disableOriginalConstructor()
                            ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);

        $this->obj->DBs->SOCOM_UI->method('query')
        ->willReturn($queryResult);

        $this->obj->get_issue_eoc($program_code, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area);
        $this->assertTrue(TRUE);

    }

    public function test_get_user_assigned_bin_by_program() {
        $program_code = 'TestProgram';
    
        $expected_result = [
            [
                'JCA_LV1' => 'Level1',
                'JCA_LV2' => 'Level2',
                'JCA_LV3' => 'Level3',
            ],
        ];
    
        $queryResult = $this->getMockBuilder('CI_DB_result')
                            ->disableOriginalConstructor()
                            ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);
    
        $this->obj->DBs->SOCOM_UI->method('get')
                       ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI->method('select')
            ->with('B.JCA_LV1, B.JCA_LV2, B.JCA_LV3')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('distinct')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('from')
            ->with('LOOKUP_PROGRAM A')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('join')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('where')
            ->willReturnSelf();
    
        $this->obj->get_user_assigned_bin_by_program($program_code);

        $this->assertTrue(TRUE);
    }
    
    public function test_get_user_assigned_bin_by_program_code() {
        $program_code = 'TestProgram';
    
        $expected_result = [
            [
                'JCA_LV1' => 'Level1',
                'JCA_LV2' => 'Level2',
                'JCA_LV3' => 'Level3',
            ],
        ];
    
        $queryResult = $this->getMockBuilder('CI_DB_result')
                            ->disableOriginalConstructor()
                            ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);
    
        $this->obj->DBs->SOCOM_UI->method('get')
                       ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI->method('select')
            ->with('B.JCA_LV1, B.JCA_LV2, B.JCA_LV3')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('distinct')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('from')
            ->with('LOOKUP_PROGRAM A')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('join')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('where')
            ->willReturnSelf();
    
        $actual = $this->obj->get_user_assigned_bin_by_program_code($program_code);
        $expected = $expected_result;
    
        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    public function test_get_zbt_summary_program_summary() {
        $l_pom_sponsor = [];
        $l_cap_sponsor = [];
        $l_ass_area = [];
        $programs = [];

        MonkeyPatch::patchFunction(
            "php_api_call",
            json_encode([]),
            SOCOM_model::class . "::get_zbt_summary_program_summary"
        );

        $actual = $this->obj->get_zbt_summary_program_summary($l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $programs);

        $this->assertIsArray($actual);
    }

    public function test_get_issue_program_summary() {
        $l_pom_sponsor = [];
        $l_cap_sponsor = [];
        $l_ass_area = [];
        $programs = [];

        MonkeyPatch::patchFunction(
            "php_api_call",
            json_encode([]),
            SOCOM_model::class . "::get_issue_program_summary"
        );

        $actual = $this->obj->get_issue_program_summary($l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $programs);

        $this->assertIsArray($actual);
    }

    public function test_calculate_prop_amt() {
        $base_k = [
            [
                'PROGRAM_NAME' => 'program1',
                'POM_POSITION_CODE' => 'position1',
                'FISCAL_YEAR' => '2026',
                'BASE_K' => 1000,
                'FISCAL_YEARS' => '2026'
            ]
        ];
    
        $delta_amt = [
            [
                'PROGRAM_NAME' => 'program1',
                'FISCAL_YEAR' => '2026',
                'DELTA_AMT' => 200
            ]
        ];
    
        $expected_result = [
            [
                'PROGRAM_NAME' => 'program1',
                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                'POM_POSITION_CODE' => 'position1',
                'FISCAL_YEAR' => '2026',
                'PROP_AMT' => 1200,
                'FISCAL_YEARS' => '2026'
            ]
        ];
    
        $requested_prop_param = [
            'key' => '26ZBT_REQUESTED',
            'value' => '26ZBT REQUESTED',
            'result_key' => 'PROP_AMT'
        ];
    
        $actual_result = $this->obj->calculate_prop_amt(
            $base_k, $delta_amt, 'BASE_K', 'DELTA_AMT', $requested_prop_param
        );
    
        $this->assertEquals($expected_result, $actual_result);
    }

    public function test_calculate_delta_amt() {
        $base_k = [
            [
                'PROGRAM_NAME' => 'program1',
                'POM_POSITION_CODE' => 'position1',
                'FISCAL_YEAR' => '2026',
                'BASE_K' => 1000,
                'FISCAL_YEARS' => '2026'
            ]
        ];
    
        $prop_amt = [
            [
                'PROGRAM_NAME' => 'program1',
                'FISCAL_YEAR' => '2026',
                'PROP_AMT' => 1200
            ]
        ];
    
        $expected_result = [
            [
                'PROGRAM_NAME' => 'program1',
                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                'POM_POSITION_CODE' => 'position1',
                'FISCAL_YEAR' => '2026',
                'DELTA_AMT' => 200,
                'FISCAL_YEARS' => '2026'
            ]
        ];
    
        $requested_delta_param = [
            'key' => '26ZBT_REQUESTED_DELTA',
            'value' => '26ZBT REQUESTED DELTA'
        ];
    
        $actual_result = $this->obj->calculate_delta_amt(
            $base_k, $prop_amt, 'BASE_K', 'PROP_AMT', $requested_delta_param
        );
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_eoc_historical_calculate_prop_amt() {
        $base_k = [
            [
                'EOC' => 'eoc1',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'RESOURCE_CATEGORY_CODE' => 'category1',
                'FISCAL_YEAR' => '2026',
                'BASE_K' => 5000,
                'FISCAL_YEARS' => '2026'
            ]
        ];
    
        $delta_amt = [
            [
                'EOC' => 'eoc1',
                'FISCAL_YEAR' => '2026',
                'DELTA_AMT' => 1000
            ]
        ];
    
        $expected_result = [
            [
                'EOC' => 'eoc1',
                'PROP_AMT' => 'PROP AMT',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'RESOURCE_CATEGORY_CODE' => 'category1',
                'FISCAL_YEAR' => '2026',
                'PROP_AMT' => 6000,
                'FISCAL_YEARS' => '2026'
            ]
        ];
    
        $requested_delta_param = [
            'key' => 'PROP_AMT',
            'value' => 'PROP AMT',
            'result_key' => 'PROP_AMT'
        ];
    
        $actual_result = $this->obj->eoc_historical_calculate_prop_amt(
            $base_k, $delta_amt, 'BASE_K', 'DELTA_AMT', $requested_delta_param
        );
    
        $this->assertEquals($expected_result, $actual_result);
    }

    
    public function test_eoc_calculate_prop_amt() {
        $base_k = [
            [
                'EOC' => 'eoc1',
                'EVENT_NAME' => 'event1',
                'EVENT_JUSTIFICATION' => 'justification1',
                'POM_POSITION_CODE' => 'pom1',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'RESOURCE_CATEGORY_CODE' => 'resource1',
                'FISCAL_YEAR' => '2024',
                'BASE_K' => 5000,
                'FISCAL_YEARS' => '2024'
            ]
        ];
    
        $delta_amt = [
            [
                'EOC' => 'eoc1',
                'EVENT_NAME' => 'event1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'FISCAL_YEAR' => '2024',
                'DELTA_AMT' => 1500
            ]
        ];
    
        $requested_delta_param = [
            'key' => 'PROP_AMT',
            'value' => 'PROP AMT',
            'result_key' => 'PROP_AMT'
        ];
    
        $expected_result = [
            [
                'EOC' => 'eoc1',
                'EVENT_NAME' => 'event1',
                'EVENT_JUSTIFICATION' => 'justification1',
                'POM_POSITION_CODE' => 'pom1',
                'PROP_AMT' => 'PROP AMT',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'RESOURCE_CATEGORY_CODE' => 'resource1',
                'FISCAL_YEAR' => '2024',
                'PROP_AMT' => 6500,
                'FISCAL_YEARS' => '2024'
            ]
        ];
    
        $actual_result = $this->obj->eoc_calculate_prop_amt(
            $base_k, $delta_amt, 'BASE_K', 'DELTA_AMT', $requested_delta_param
        );
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    
    public function test_eoc_calculate_delta_amt() {
        $base_k = [
            [
                'EOC' => 'eoc1',
                'EVENT_NAME' => 'event1',
                'EVENT_JUSTIFICATION' => 'justification1',
                'POM_POSITION_CODE' => 'pom1',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'RESOURCE_CATEGORY_CODE' => 'resource1',
                'FISCAL_YEAR' => '2024',
                'BASE_K' => 5000,
                'FISCAL_YEARS' => '2024'
            ]
        ];
    
        $prop_amt = [
            [
                'EOC' => 'eoc1',
                'FISCAL_YEAR' => '2024',
                'PROP_AMT' => 6500
            ]
        ];
    
        $requested_delta_param = [
            'key' => 'DELTA_AMT',
            'value' => 'DELTA AMT'
        ];
    
        $expected_result = [
            [
                'EOC' => 'eoc1',
                'DELTA_AMT' => 'DELTA AMT',
                'ASSESSMENT_AREA_CODE' => 'area1',
                'POM_SPONSOR_CODE' => 'sponsor1',
                'CAPABILITY_SPONSOR_CODE' => 'cap_sponsor1',
                'RESOURCE_CATEGORY_CODE' => 'resource1',
                'FISCAL_YEAR' => '2024',
                'DELTA_AMT' => 1500,
                'FISCAL_YEARS' => '2024'
            ]
        ];
    
        $actual_result = $this->obj->eoc_calculate_delta_amt(
            $base_k, $prop_amt, 'BASE_K', 'PROP_AMT', $requested_delta_param
        );
    
        $this->assertEquals($expected_result, $actual_result);
    }

    public function test_get_issue_summary_fy_query() {
        $table1 = 'issue_table';
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program = 'Program1';
        $pom = false;
        $page = '';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM issue_table
            ) AS EXT
            LEFT JOIN (
                SELECT
                    PROGRAM_NAME,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM
                    LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            WHERE
                EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME = 'Program1'
            GROUP BY
                LUT.PROGRAM_NAME,
                EXT.FISCAL_YEAR
            ORDER BY
                LUT.PROGRAM_NAME,
                EXT.FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
        
        $this->obj->DBs->SOCOM_UI->method('select')
        ->willReturnSelf();
    
        $dbMock->method('select')
            ->willReturnSelf();
        
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->expects($this->exactly(2))
               ->method('get_compiled_select')
               ->willReturn($expected_query);
        
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
        
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
        
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $this->obj->get_issue_summary_fy_query($table1, $selection, $program, $pom, $page);

        $this->assertTrue(TRUE);
    }
    
    public function test_get_issue_summary_query_false_delta() {
        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $delta = false;
        $program = 'Program1';
        $ext = false;
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM table2
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K,
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26ZBT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table1
                WHERE (
                    PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2)
                )
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K,
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26ZBT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table3
                WHERE (PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2))
            ) AS ZBT
            LEFT JOIN (
                SELECT
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM LOOKUP_PROGRAM
            ) AS LUT ON  ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            WHERE
                ZBT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME = 'Program1'
            GROUP BY
                LUT.PROGRAM_NAME,
                ZBT.POM_POSITION_CODE,
                ZBT.FISCAL_YEAR
            ORDER BY
                LUT.PROGRAM_NAME,
                ZBT.FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where')
               ->willReturnSelf();
    
        $dbMock
               ->method('get_compiled_select')
               ->willReturnOnConsecutiveCalls(
                   "SELECT * FROM table2",
                   "SELECT
                        0 AS ADJUSTMENT_K,
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26ZBT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table1
                    WHERE (
                        PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2)
                    )",
                   "SELECT
                        0 AS ADJUSTMENT_K,
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26ZBT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table3
                    WHERE (PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2))"
               );
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->get_issue_summary_query($table1, $table2, $table3, $selection, $delta, $program, $ext);

        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_get_issue_summary_query_true_delta() {
        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $delta = true;
        $program = 'Program1';
        $ext = false;
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM table2
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K,
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26ZBT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table1
                WHERE (
                    PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2)
                )
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K,
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26ZBT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table3
                WHERE (PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2))
            ) AS ZBT
            LEFT JOIN (
                SELECT
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM LOOKUP_PROGRAM
            ) AS LUT ON  ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            WHERE
                ZBT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME = 'Program1'
            GROUP BY
                LUT.PROGRAM_NAME,
                ZBT.POM_POSITION_CODE,
                ZBT.FISCAL_YEAR
            ORDER BY
                LUT.PROGRAM_NAME,
                ZBT.FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where')
               ->willReturnSelf();
    
        $dbMock
               ->method('get_compiled_select')
               ->willReturnOnConsecutiveCalls(
                   "SELECT * FROM table2",
                   "SELECT
                        0 AS ADJUSTMENT_K,
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26ZBT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table1
                    WHERE (
                        PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2)
                    )",
                   "SELECT
                        0 AS ADJUSTMENT_K,
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26ZBT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table3
                    WHERE (PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM table2))"
               );
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->get_issue_summary_query($table1, $table2, $table3, $selection, $delta, $program, $ext);

        $this->assertEquals($expected_result, $actual_result);
    }
    
    
    public function test_get_zbt_program_summary_query_with_default_parameters() {
        $table1 = 'table1';
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = [];
        $delta = false;
        $program = '';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM table2
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K, 
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26EXT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table1
                WHERE (
                    PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                )
            ) AS EXT
            LEFT JOIN (
                SELECT ASSESSMENT_AREA_CODE,
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM
                    LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            WHERE 
                EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME IS NOT NULL
            GROUP BY 
                LUT.PROGRAM_NAME,
                EXT.POM_POSITION_CODE,
                EXT.FISCAL_YEAR
            ORDER BY 
                PROGRAM_NAME,
                FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where_in')
               ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
               ->willReturnOnConsecutiveCalls(
                   "SELECT * FROM table2",
                   "SELECT
                        0 AS ADJUSTMENT_K, 
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26EXT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table1
                    WHERE (
                        PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                    )"
               );
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->get_zbt_program_summary_query($table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list, $delta, $program);
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_get_zbt_program_summary_query_with_program() {
        $table1 = 'table1';
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = [];
        $delta = false;
        $program = 'Program1';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM table2
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K, 
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26EXT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table1
                WHERE (
                    PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                )
            ) AS EXT
            LEFT JOIN (
                SELECT 
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            WHERE 
                EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME = 'Program1'
            GROUP BY 
                LUT.PROGRAM_NAME,
                EXT.POM_POSITION_CODE,
                EXT.FISCAL_YEAR
            ORDER BY 
                PROGRAM_NAME,
                FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where_in')
               ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
               ->willReturnOnConsecutiveCalls(
                   "SELECT * FROM table2",
                   "SELECT
                        0 AS ADJUSTMENT_K, 
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26EXT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table1
                    WHERE (
                        PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                    )"
               );
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->get_zbt_program_summary_query($table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list, $delta, $program);
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_get_zbt_program_summary_query_with_delta() {
        $table1 = 'table1';
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = [];
        $delta = true;
        $program = '';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM table2
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K, 
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26EXT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table1
                WHERE (
                    PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                )
            ) AS EXT
            LEFT JOIN (
                SELECT 
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            LEFT JOIN (
                SELECT
                    PROGRAM_CODE,
                    EOC_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE,
                    ASSESSMENT_AREA_CODE,
                    FISCAL_YEAR,
                    DELTA_AMT,
                    RESOURCE_K
                FROM
                    DT_ZBT_EXTRACT_2026
                WHERE
                    CAPABILITY_SPONSOR_CODE IN('CAP1', 'CAP2')
                    AND POM_SPONSOR_CODE IN('POM1', 'POM2')
                    AND ASSESSMENT_AREA_CODE IN('AREA1', 'AREA2')
            ) AS ZBT_EXTRACT ON EXT.PROGRAM_CODE = ZBT_EXTRACT.PROGRAM_CODE
            AND EXT.FISCAL_YEAR = ZBT_EXTRACT.FISCAL_YEAR
            AND EXT.EOC_CODE = ZBT_EXTRACT.EOC_CODE
            AND EXT.POM_SPONSOR_CODE = ZBT_EXTRACT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE
            AND EXT.ASSESSMENT_AREA_CODE = ZBT_EXTRACT.ASSESSMENT_AREA_CODE
            WHERE 
                EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME IS NOT NULL
                AND EXT.EXECUTION_MANAGER_CODE != ''
            GROUP BY 
                LUT.PROGRAM_NAME,
                EXT.POM_POSITION_CODE,
                EXT.FISCAL_YEAR
            ORDER BY 
                PROGRAM_NAME,
                FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where_in')
               ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
               ->willReturnOnConsecutiveCalls(
                   "SELECT * FROM table2",
                   "SELECT
                        0 AS ADJUSTMENT_K, 
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26EXT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table1
                    WHERE (
                        PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                    )"
               );
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->get_zbt_program_summary_query($table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list, $delta);
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_get_zbt_program_summary_query_with_program_list() {
        $table1 = 'table1';
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = ['Group1', 'Group2'];
        $delta = false;
        $program = '';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2027']
        ];
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR
            FROM (
                SELECT * FROM table2
                UNION ALL
                SELECT
                    0 AS ADJUSTMENT_K, 
                    ASSESSMENT_AREA_CODE,
                    0 AS BASE_K,
                    BUDGET_ACTIVITY_CODE,
                    BUDGET_ACTIVITY_NAME,
                    BUDGET_SUB_ACTIVITY_CODE,
                    BUDGET_SUB_ACTIVITY_NAME,
                    CAPABILITY_SPONSOR_CODE,
                    0 AS END_STRENGTH,
                    EOC_CODE,
                    EVENT_JUSTIFICATION,
                    EVENT_NAME,
                    EXECUTION_MANAGER_CODE,
                    FISCAL_YEAR,
                    LINE_ITEM_CODE,
                    0 AS OCO_OTHD_ADJUSTMENT_K,
                    0 AS OCO_OTHD_K,
                    0 AS OCO_TO_BASE_K,
                    OSD_PROGRAM_ELEMENT_CODE,
                    '26EXT' AS POM_POSITION_CODE,
                    POM_SPONSOR_CODE,
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    RDTE_PROJECT_CODE,
                    RESOURCE_CATEGORY_CODE,
                    0 AS RESOURCE_K,
                    SPECIAL_PROJECT_CODE,
                    SUB_ACTIVITY_GROUP_CODE,
                    SUB_ACTIVITY_GROUP_NAME,
                    2024 AS WORK_YEARS
                FROM table1
                WHERE (
                    PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                )
            ) AS EXT
            LEFT JOIN (
                SELECT 
                    ASSESSMENT_AREA_CODE,
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            WHERE 
                EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                AND LUT.PROGRAM_NAME IS NOT NULL
                AND LUT.PROGRAM_GROUP IN('Group1', 'Group2')
            GROUP BY 
                LUT.PROGRAM_NAME,
                EXT.POM_POSITION_CODE,
                EXT.FISCAL_YEAR
            ORDER BY 
                PROGRAM_NAME,
                FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                        ->disableOriginalConstructor()
                        ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where_in')
               ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
               ->willReturnOnConsecutiveCalls(
                   "SELECT * FROM table2",
                   "SELECT
                        0 AS ADJUSTMENT_K, 
                        ASSESSMENT_AREA_CODE,
                        0 AS BASE_K,
                        BUDGET_ACTIVITY_CODE,
                        BUDGET_ACTIVITY_NAME,
                        BUDGET_SUB_ACTIVITY_CODE,
                        BUDGET_SUB_ACTIVITY_NAME,
                        CAPABILITY_SPONSOR_CODE,
                        0 AS END_STRENGTH,
                        EOC_CODE,
                        EVENT_JUSTIFICATION,
                        EVENT_NAME,
                        EXECUTION_MANAGER_CODE,
                        FISCAL_YEAR,
                        LINE_ITEM_CODE,
                        0 AS OCO_OTHD_ADJUSTMENT_K,
                        0 AS OCO_OTHD_K,
                        0 AS OCO_TO_BASE_K,
                        OSD_PROGRAM_ELEMENT_CODE,
                        '26EXT' AS POM_POSITION_CODE,
                        POM_SPONSOR_CODE,
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        RDTE_PROJECT_CODE,
                        RESOURCE_CATEGORY_CODE,
                        0 AS RESOURCE_K,
                        SPECIAL_PROJECT_CODE,
                        SUB_ACTIVITY_GROUP_CODE,
                        SUB_ACTIVITY_GROUP_NAME,
                        2024 AS WORK_YEARS
                    FROM table1
                    WHERE (
                        PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM DT_EXT_2026)
                    )"
               );
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->expects($this->once())
                    ->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->expects($this->once())
               ->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->get_zbt_program_summary_query($table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list, $delta);
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_zbt_summary_program_summary_query_basic() {
        $table1 = 'table1';
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program = null;
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program2', 'PROGRAM_CODE' => 'Code2', 'FISCAL_YEAR' => '2027']
        ];
    
        $query1 = "SELECT * FROM table2";
        $join_query1 = "SELECT PROGRAM_NAME, PROGRAM_GROUP, PROGRAM_CODE, POM_SPONSOR_CODE, CAPABILITY_SPONSOR_CODE, ASSESSMENT_AREA_CODE FROM lut_count";
        $query2 = "SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR FROM (SELECT * FROM table2) AS EXT";
        $join_query2 = "SELECT * FROM table1";
    
        $expected_query = "
            SELECT PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR 
            FROM (SELECT * FROM table2) AS EXT 
            LEFT JOIN (SELECT PROGRAM_NAME, PROGRAM_GROUP, PROGRAM_CODE, POM_SPONSOR_CODE, CAPABILITY_SPONSOR_CODE, ASSESSMENT_AREA_CODE FROM lut_count) AS LUT 
            ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP 
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE 
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE 
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE 
            AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE 
            LEFT JOIN (SELECT * FROM table1) AS ZBT_EXTRACT 
            ON EXT.EOC_CODE = ZBT_EXTRACT.EOC_CODE 
            AND EXT.OSD_PROGRAM_ELEMENT_CODE = ZBT_EXTRACT.OSD_PROGRAM_ELEMENT_CODE 
            AND EXT.CAPABILITY_SPONSOR_CODE = ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE 
            AND EXT.POM_SPONSOR_CODE = ZBT_EXTRACT.POM_SPONSOR_CODE 
            AND EXT.ASSESSMENT_AREA_CODE = ZBT_EXTRACT.ASSESSMENT_AREA_CODE 
            AND EXT.EXECUTION_MANAGER_CODE = ZBT_EXTRACT.EXECUTION_MANAGER_CODE 
            AND EXT.RESOURCE_CATEGORY_CODE = ZBT_EXTRACT.RESOURCE_CATEGORY_CODE 
            AND EXT.FISCAL_YEAR = ZBT_EXTRACT.FISCAL_YEAR 
            WHERE EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030') 
            AND LUT.PROGRAM_NAME IS NOT NULL 
            GROUP BY LUT.PROGRAM_NAME, EXT.POM_POSITION_CODE, EXT.FISCAL_YEAR 
            ORDER BY PROGRAM_NAME, FISCAL_YEAR
        ";
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                       ->disableOriginalConstructor()
                       ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
               ->willReturn($query2);
    
        $queryResult = $this->getMockBuilder('stdClass')
                            ->addMethods(['result_array'])
                            ->getMock();
    
        $queryResult->method('result_array')
                    ->willReturn($expected_result);
    
        $dbMock->method('query')
               ->willReturn($queryResult);
    
        $this->obj = $this->getMockBuilder(get_class($this->obj))
                          ->onlyMethods(['get_issue_zbt_extract', 'get_lut_count', 'get_zbt_extract'])
                          ->getMock();
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $this->obj->zbt_summary_program_summary_query($table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program);

        $this->assertTrue(TRUE);
    }

    public function test_zbt_summary_program_summary_delta_query_basic() {
        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = ['Group1', 'Group2'];
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program2', 'PROGRAM_CODE' => 'Code2', 'FISCAL_YEAR' => '2027']
        ];
    
        $sub_query1 = "SELECT * FROM $table1 WHERE CAPABILITY_SPONSOR_CODE IN ('CAP1', 'CAP2') AND POM_SPONSOR_CODE IN ('POM1', 'POM2') AND ASSESSMENT_AREA_CODE IN ('AREA1', 'AREA2')";
        $sub_query2 = "SELECT 0 AS ADJUSTMENT_K, ASSESSMENT_AREA_CODE, 0 AS BASE_K, BUDGET_ACTIVITY_CODE, BUDGET_ACTIVITY_NAME, BUDGET_SUB_ACTIVITY_CODE, BUDGET_SUB_ACTIVITY_NAME, CAPABILITY_SPONSOR_CODE, 0 AS END_STRENGTH, EOC_CODE, EVENT_JUSTIFICATION, EVENT_NAME, EXECUTION_MANAGER_CODE, FISCAL_YEAR, LINE_ITEM_CODE, 0 AS OCO_OTHD_ADJUSTMENT_K, 0 AS OCO_OTHD_K, 0 AS OCO_TO_BASE_K, OSD_PROGRAM_ELEMENT_CODE, '26ZBT' AS POM_POSITION_CODE, POM_SPONSOR_CODE, PROGRAM_CODE, PROGRAM_GROUP, RDTE_PROJECT_CODE, RESOURCE_CATEGORY_CODE, 0 AS RESOURCE_K, SPECIAL_PROJECT_CODE, SUB_ACTIVITY_GROUP_CODE, SUB_ACTIVITY_GROUP_NAME, 2024 AS WORK_YEARS FROM $table3 WHERE PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM $table1) OR EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM $table1) AND CAPABILITY_SPONSOR_CODE IN ('CAP1', 'CAP2') AND POM_SPONSOR_CODE IN ('POM1', 'POM2') AND ASSESSMENT_AREA_CODE IN ('AREA1', 'AREA2')";
        $query1 = "SELECT $selection FROM ($sub_query1 UNION ALL $sub_query2) AS ZBT";
        $query2 = " LEFT JOIN (SELECT POM_SPONSOR_CODE, CAPABILITY_SPONSOR_CODE, ASSESSMENT_AREA_CODE, PROGRAM_NAME, PROGRAM_GROUP, PROGRAM_CODE FROM LOOKUP_PROGRAM) AS LUT ON ZBT.PROGRAM_GROUP = LUT.PROGRAM_GROUP AND ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE AND ZBT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
        $query3 = " WHERE ZBT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030') AND LUT.PROGRAM_NAME IS NOT NULL AND ZBT.EXECUTION_MANAGER_CODE != '' GROUP BY LUT.PROGRAM_NAME, ZBT.POM_POSITION_CODE, ZBT.FISCAL_YEAR ORDER BY LUT.PROGRAM_NAME, ZBT.FISCAL_YEAR";
        $subquery = "SELECT PROGRAM_CODE, EOC_CODE, CAPABILITY_SPONSOR_CODE, POM_SPONSOR_CODE, ASSESSMENT_AREA_CODE, FISCAL_YEAR, DELTA_AMT, EXECUTION_MANAGER_CODE FROM DT_ISS_EXTRACT_2026 WHERE CAPABILITY_SPONSOR_CODE IN ('CAP1', 'CAP2') AND POM_SPONSOR_CODE IN ('POM1', 'POM2') AND ASSESSMENT_AREA_CODE IN ('AREA1', 'AREA2')";
        $query4 = " LEFT JOIN ($subquery) as ISS_EXTRACT ON ZBT.PROGRAM_CODE = ISS_EXTRACT.PROGRAM_CODE AND ZBT.FISCAL_YEAR = ISS_EXTRACT.FISCAL_YEAR AND ZBT.EOC_CODE = ISS_EXTRACT.EOC_CODE AND ZBT.POM_SPONSOR_CODE = ISS_EXTRACT.POM_SPONSOR_CODE AND ZBT.CAPABILITY_SPONSOR_CODE = ISS_EXTRACT.CAPABILITY_SPONSOR_CODE AND ZBT.ASSESSMENT_AREA_CODE = ISS_EXTRACT.ASSESSMENT_AREA_CODE AND ZBT.EXECUTION_MANAGER_CODE = ISS_EXTRACT.EXECUTION_MANAGER_CODE";
    
        $final_query = $query1 . $query2 . $query4 . $query3;
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
                ->disableOriginalConstructor()
                ->getMock();
    
        $dbMock->method('select')
               ->willReturnSelf();
    
        $dbMock->method('from')
               ->willReturnSelf();
    
        $dbMock->method('where')
               ->willReturnSelf();
    
        $dbMock->method('where_in')
               ->willReturnSelf();
    
        $dbMock->method('group_start')
               ->willReturnSelf();
    
        $dbMock->method('group_end')
               ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
               ->will($this->onConsecutiveCalls($sub_query1, $sub_query2, $query1, $subquery));
    
        $queryResult = $this->getMockBuilder('stdClass')
                ->addMethods(['result_array'])
                ->getMock();
    
        $queryResult->method('result_array')
                ->willReturn($expected_result);
    
        $dbMock->method('query')
               ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI = $dbMock;
    
        $actual_result = $this->obj->zbt_summary_program_summary_delta_query($table1, $table2, $table3, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list);
    
        $this->assertEquals($expected_result, $actual_result);
    }
    
    public function test_issue_program_summary_query() {
        $table1 = 'table1';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program = 'TestProgram';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program2', 'PROGRAM_CODE' => 'Code2', 'FISCAL_YEAR' => '2027']
        ];
    
        $query1 = "MOCKED_QUERY_1";
        $join_query1 = "MOCKED_JOIN_QUERY_1";
        $query2 = "SELECT $selection FROM ($query1) AS EXT";
        $query3 = " LEFT JOIN ($join_query1) AS LUT ON `EXT`.`PROGRAM_GROUP` = `LUT`.`PROGRAM_GROUP`
                    AND `EXT`.`PROGRAM_CODE` = `LUT`.`PROGRAM_CODE`
                    AND `EXT`.`POM_SPONSOR_CODE` = `LUT`.`POM_SPONSOR_CODE`
                    AND `EXT`.`CAPABILITY_SPONSOR_CODE` = `LUT`.`CAPABILITY_SPONSOR_CODE`
                    AND `EXT`.`ASSESSMENT_AREA_CODE` = `LUT`.`ASSESSMENT_AREA_CODE`
                    WHERE EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030') AND
                    `LUT`.`PROGRAM_NAME` IS NOT NULL AND PROGRAM_NAME= \"$program\"";
        $query4 = " GROUP BY LUT.PROGRAM_NAME,EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR
                    ORDER BY `PROGRAM_NAME`, `FISCAL_YEAR`";
        $final_query = $query2 . $query3 . $query4;
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->onlyMethods(['get_issue_zbt_extract', 'get_lut_count'])
            ->getMock();

        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->willReturn($query2);
    
        $queryResult = $this->getMockBuilder('stdClass')
            ->addMethods(['result_array'])
            ->getMock();
    
        $queryResult->method('result_array')
            ->willReturn($expected_result);
    
        $dbMock->method('query')->willReturn($queryResult);
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $actual_result = $socomModelMock->issue_program_summary_query($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program);
    
        $this->assertEquals($expected_result, $actual_result);
    }

    public function test_issue_program_summary_main_query_with_program() {
        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = ['ProgramGroup1', 'ProgramGroup2'];
        $program = 'TestProgram';
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program2', 'PROGRAM_CODE' => 'Code2', 'FISCAL_YEAR' => '2027']
        ];

        $queryResult = $this->getMockBuilder('CI_DB_result')
                ->disableOriginalConstructor()
                ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);
    
        $this->obj->DBs->SOCOM_UI->method('get')
                ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI->method('select')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('distinct')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('from')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('join')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('where')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('where_in')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('group_start')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('group_end')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('get_compiled_select')
            ->willReturnOnConsecutiveCalls(['']);


        $this->obj->issue_program_summary_main_query($table1, $table2, $table3, $l_pom_sponsor, $l_cap_sponsor,
            $l_ass_area, $selection, $program_list, $program);
    
        $this-> assertTrue(TRUE);
    }

    public function test_issue_program_summary_main_query_without_program() {
        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2'];
        $l_ass_area = ['AREA1', 'AREA2'];
        $selection = 'PROGRAM_NAME, PROGRAM_CODE, FISCAL_YEAR';
        $program_list = ['ProgramGroup1', 'ProgramGroup2'];
    
        $expected_result = [
            ['PROGRAM_NAME' => 'Program1', 'PROGRAM_CODE' => 'Code1', 'FISCAL_YEAR' => '2026'],
            ['PROGRAM_NAME' => 'Program2', 'PROGRAM_CODE' => 'Code2', 'FISCAL_YEAR' => '2027']
        ];

        $queryResult = $this->getMockBuilder('CI_DB_result')
                ->disableOriginalConstructor()
                ->getMock();
        $queryResult->method('result_array')->willReturn($expected_result);
    
        $this->obj->DBs->SOCOM_UI->method('get')
                ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI->method('select')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('distinct')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('from')
            ->willReturnSelf();
    
        $this->obj->DBs->SOCOM_UI->method('join')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('where')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('where_in')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('group_start')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('group_end')
            ->willReturnSelf();

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('get_compiled_select')
            ->willReturnOnConsecutiveCalls(['']);

        $this->obj->issue_program_summary_main_query($table1, $table2, $table3, $l_pom_sponsor, $l_cap_sponsor,
            $l_ass_area, $selection, $program_list);
    
        $this-> assertTrue(TRUE);
    }
    

    public function test_get_historical_pom_data() {
        $page = 'page';
        $view = 'eoc_historical_pom';
        $fy = 'fy';
        $params = 'params';
        $program = 'program';

        MonkeyPatch::patchMethod(SOCOM_model::class, ['eoc_historical_pom_data_helper' => true]);

        $this->obj->get_historical_pom_data($page, $view, $fy, $param, $program);

        $this-> assertTrue(TRUE);
    }

    public function test_details_data_helper_zbt_summary() {
        $page = 'zbt_summary';
        $fy = '26';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_zbt_program_summary_query')
            ->will($this->onConsecutiveCalls(
                [ ['some_base_k_data' => [ 'FISCAL_YEAR' => '2023'], 'BASE_K' => 'BASE_K']],
                [ ['some_bassome_delta_amt_datae_k_data' => [ 'FISCAL_YEAR' => '2023'], 'BASE_K' => 'BASE_K']]
            ));

        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('details_data_helper');
        $method->setAccessible(true);
    
        $actual_result = $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }

    public function test_details_data_helper_issue() {
        $page = 'issue';
        $fy = '26';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_issue_summary_query')
            ->will($this->onConsecutiveCalls(
                [[
                    'PROGRAM_NAME' => '',
                    '26ZBT_REQUESTED' => '26ZBT',
                    'PROP_AMT' => 0,
                    'POM_POSITION_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]],
                [ [
                    'PROGRAM_NAME' => '',
                    '26EXT' => '26ZBT',
                    'PROP_AMT' => 0,
                    'POM_POSITION_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]]
            ));

        $socomModelMock->method('calculate_delta_amt')
            ->willReturn(  [ [
                'PROGRAM_NAME' => '',
                '26EXT' => '26ZBT',
                'PROP_AMT' => 0,
                'POM_POSITION_CODE' => '',
                'FISCAL_YEAR' => 2026,
                'FISCAL_YEARS' => ''
        ]]);

        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('details_data_helper');
        $method->setAccessible(true);
        
        $actual_result = $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }

    public function test_details_data_helper_other() {
        $page = 'issue';
        $fy = '25';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_issue_summary_fy_query')
            ->will($this->onConsecutiveCalls(
                [[
                    'PROGRAM_NAME' => '',
                    '26ZBT_REQUESTED' => '26ZBT',
                    'PROP_AMT' => 0,
                    'POM_POSITION_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]],
                [ [
                    'PROGRAM_NAME' => '',
                    '26EXT' => '26ZBT',
                    'PROP_AMT' => 0,
                    'POM_POSITION_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]]
            ));

        $socomModelMock->method('calculate_delta_amt')
            ->willReturn(  [ [
                'PROGRAM_NAME' => '',
                '26EXT' => '26ZBT',
                'PROP_AMT' => 0,
                'POM_POSITION_CODE' => '',
                'FISCAL_YEAR' => 2026,
                'FISCAL_YEARS' => ''
        ]]);

        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('details_data_helper');
        $method->setAccessible(true);
    
        $actual_result = $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }
    
    public function test_eoc_historical_pom_data_helper_zbt_summary_26() {
        $page = 'zbt_summary';
        $fy = '26';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_eoc_historical_summary_query')
            ->will($this->onConsecutiveCalls(
                [[
                    'EOC' => '',
                    '25EXT' => '',
                    'BASE_K' => 0,
                    'ASSESSMENT_AREA_CODE' => '',
                    'POM_SPONSOR_CODE' => '',
                    'CAPABILITY_SPONSOR_CODE' => '',
                    'RESOURCE_CATEGORY_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]],
                [[
                    'EOC' => 0,
                    '25ZBT_REQUESTED' => '25ZBT',
                    'PROP_AMT' => 0,
                    'ASSESSMENT_AREA_CODE' => 0,
                    'POM_SPONSOR_CODE' => 0,
                    'CAPABILITY_SPONSOR_CODE' => 0,
                    'RESOURCE_CATEGORY_CODE' => 0,
                    'FISCAL_YEAR' => 0,
                    'FISCAL_YEARS' => 0
                ]]
            ));
    
        $socomModelMock->method('eoc_historical_calculate_prop_amt')
            ->willReturn([[
                'EOC' => 0,
                '25ZBT_REQUESTED' => '25ZBT',
                'PROP_AMT' => 0,
                'ASSESSMENT_AREA_CODE' => 0,
                'POM_SPONSOR_CODE' => 0,
                'CAPABILITY_SPONSOR_CODE' => 0,
                'RESOURCE_CATEGORY_CODE' => 0,
                'FISCAL_YEAR' => 0,
                'FISCAL_YEARS' => 0
            ]]);

    
        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('eoc_historical_pom_data_helper');
        $method->setAccessible(true);
    
        $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }

    public function test_eoc_historical_pom_data_helper_issue_26() {
        $page = 'issue';
        $fy = '26';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_eoc_historical_issue_summary_query')
            ->will($this->onConsecutiveCalls(
                [[
                    'EOC' => '',
                    '25EXT' => '',
                    'BASE_K' => 0,
                    'ASSESSMENT_AREA_CODE' => '',
                    'POM_SPONSOR_CODE' => '',
                    'CAPABILITY_SPONSOR_CODE' => '',
                    'RESOURCE_CATEGORY_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]],
                [[
                    'EOC' => 0,
                    '25ZBT_REQUESTED' => '25ZBT',
                    'PROP_AMT' => 0,
                    'ASSESSMENT_AREA_CODE' => 0,
                    'POM_SPONSOR_CODE' => 0,
                    'CAPABILITY_SPONSOR_CODE' => 0,
                    'RESOURCE_CATEGORY_CODE' => 0,
                    'FISCAL_YEAR' => 0,
                    'FISCAL_YEARS' => 0
                ]]
            ));
    
        $socomModelMock->method('eoc_historical_calculate_prop_amt')
            ->willReturn(['some_prop_amt_data']);
        $socomModelMock->method('eoc_calculate_delta_amt')
            ->willReturn([[
                'EOC' => 0,
                '25ZBT_REQUESTED' => '25ZBT',
                'PROP_AMT' => 0,
                'ASSESSMENT_AREA_CODE' => 0,
                'POM_SPONSOR_CODE' => 0,
                'CAPABILITY_SPONSOR_CODE' => 0,
                'RESOURCE_CATEGORY_CODE' => 0,
                'FISCAL_YEAR' => 0,
                'FISCAL_YEARS' => 0
        ]]);
    
        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('eoc_historical_pom_data_helper');
        $method->setAccessible(true);
    
        $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }

    public function test_eoc_historical_pom_data_helper_issue_25() {
        $page = 'issue';
        $fy = '25';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_eoc_historical_issue_summary_query')
            ->will($this->onConsecutiveCalls(
                [[
                    'EOC' => '',
                    '25EXT' => '',
                    'BASE_K' => 0,
                    'ASSESSMENT_AREA_CODE' => '',
                    'POM_SPONSOR_CODE' => '',
                    'CAPABILITY_SPONSOR_CODE' => '',
                    'RESOURCE_CATEGORY_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]],
                [[
                    'EOC' => 0,
                    '25ZBT_REQUESTED' => '25ZBT',
                    'PROP_AMT' => 0,
                    'ASSESSMENT_AREA_CODE' => 0,
                    'POM_SPONSOR_CODE' => 0,
                    'CAPABILITY_SPONSOR_CODE' => 0,
                    'RESOURCE_CATEGORY_CODE' => 0,
                    'FISCAL_YEAR' => 0,
                    'FISCAL_YEARS' => 0
                ]]
            ));
    
        $socomModelMock->method('eoc_historical_calculate_prop_amt')
            ->willReturn(['some_prop_amt_data']);
        $socomModelMock->method('eoc_calculate_delta_amt')
            ->willReturn( [[
                'EOC' => 0,
                '25ZBT_REQUESTED' => '25ZBT',
                'PROP_AMT' => 0,
                'ASSESSMENT_AREA_CODE' => 0,
                'POM_SPONSOR_CODE' => 0,
                'CAPABILITY_SPONSOR_CODE' => 0,
                'RESOURCE_CATEGORY_CODE' => 0,
                'FISCAL_YEAR' => 0,
                'FISCAL_YEARS' => 0
            ]]);
    
        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('eoc_historical_pom_data_helper');
        $method->setAccessible(true);
    
        $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }
    
    public function test_eoc_historical_pom_data_helper_zbt_summary_25() {
        $page = 'zbt_summary';
        $fy = '25';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'table6' => 'table6',
            'l_pom_sponsor' => ['POM1', 'POM2'],
            'l_cap_sponsor' => ['CAP1', 'CAP2'],
            'l_ass_area' => ['AREA1', 'AREA2']
        ];
        $program = 'TestProgram';
    
        $socomModelMock = $this->getMockBuilder('SOCOM_model')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();
    
        $dbMock->method('select')
            ->willReturnSelf();
    
        $dbMock->method('from')
            ->willReturnSelf();
    
        $dbMock->method('get_compiled_select')
            ->will($this->onConsecutiveCalls(
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')",
                "GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')"
            ));
    
        $socomModelMock->DBs = (object) ['SOCOM_UI' => $dbMock];
    
        $socomModelMock->method('get_eoc_historical_summary_query')
            ->will($this->onConsecutiveCalls(
                [ [
                    'EOC' => '',
                    '25EXT' => '',
                    'BASE_K' => 0,
                    'ASSESSMENT_AREA_CODE' => '',
                    'POM_SPONSOR_CODE' => '',
                    'CAPABILITY_SPONSOR_CODE' => '',
                    'RESOURCE_CATEGORY_CODE' => '',
                    'FISCAL_YEAR' => 2026,
                    'FISCAL_YEARS' => ''
                ]],
                [[
                    'EOC' => 0,
                    '25ZBT_REQUESTED' => '25ZBT',
                    'PROP_AMT' => 0,
                    'ASSESSMENT_AREA_CODE' => 0,
                    'POM_SPONSOR_CODE' => 0,
                    'CAPABILITY_SPONSOR_CODE' => 0,
                    'RESOURCE_CATEGORY_CODE' => 0,
                    'FISCAL_YEAR' => 0,
                    'FISCAL_YEARS' => 0
                ]]
            ));
    
        $socomModelMock->method('eoc_historical_calculate_prop_amt')
            ->willReturn(['some_prop_amt_data']);
        
        $socomModelMock->method('eoc_calculate_delta_amt')
            ->willReturn([[
                'EOC' => 0,
                '25ZBT_REQUESTED' => '25ZBT',
                'PROP_AMT' => 0,
                'ASSESSMENT_AREA_CODE' => 0,
                'POM_SPONSOR_CODE' => 0,
                'CAPABILITY_SPONSOR_CODE' => 0,
                'RESOURCE_CATEGORY_CODE' => 0,
                'FISCAL_YEAR' => 0,
                'FISCAL_YEARS' => 0
            ]]);
    
        $reflection = new ReflectionClass($socomModelMock);
        $method = $reflection->getMethod('eoc_historical_pom_data_helper');
        $method->setAccessible(true);
    
        $method->invokeArgs($socomModelMock, [$page, $fy, $params, $program]);
    
        $this->assertTrue(TRUE);
    }

    public function test_program_summary_count() {
        $table1 = 'table1'; 
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2']; 
        $l_ass_area = ['AREA1', 'AREA2'];
        $l_approval_status = ''; 
        $page = 'ISSUE'; 
        $program_list = ['PROGRAM_1'];

        $inner_query = '';

        $result = [
            'TOTAL_ISSUE_EVENTS' => 10
        ];
        
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('row_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $actual = $this->obj->program_summary_count($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $page, $program_list);

        $this->assertIsInt($actual);
    }

    public function test_program_summary_dollars_moved() {
        $table1 = 'table1'; 
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2']; 
        $l_ass_area = ['AREA1', 'AREA2'];
        $l_approval_status = ''; 
        $program_list = ['PROGRAM_1'];

        $inner_query = '';

        $result = [
            'TOTAL_POS_DOLLARS' => 10
        ];
        
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('row_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $actual = $this->obj->program_summary_dollars_moved($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $page, $program_list);

        $this->assertIsNumeric($actual);
    }

    public function test_program_summary_net_change() {
        $table1 = 'table1'; 
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2']; 
        $l_ass_area = ['AREA1', 'AREA2'];
        $l_approval_status = ''; 
        $program_list = ['PROGRAM_1'];

        $inner_query = '';

        $result = [
            'TOTAL_POS_DOLLARS' => 10
        ];
        
        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('row_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $actual = $this->obj->program_summary_net_change($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $page, $program_list);

        $this->assertIsNumeric($actual);
    }

    public function test_issue_program_summary_card() {
        $table1 = 'table1'; 
        $table2 = 'table2';
        $l_pom_sponsor = ['POM1', 'POM2'];
        $l_cap_sponsor = ['CAP1', 'CAP2']; 
        $l_ass_area = ['AREA1', 'AREA2'];
        $l_approval_status = ''; 
        $program_list = ['PROGRAM_1'];

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $queryResult->method('row_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);
    
        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $expected = [
            'total_events' => 0,
            'dollars_moved' => 0,
            'net_change' => 0
        ];

        $actual = $this->obj->issue_program_summary_card($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $page, $program_list);

        $this->assertEquals($expected, $actual);
    }

    public function test_get_option_criteria_names() {
        $result = [
            'CRITERIA' => 0.5,
            'WEIGHT' => 0.5
        ];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $actual = $this->obj->get_option_criteria_names();

        $this->assertEquals($result, $actual);
    }

    public function test_get_program_scored() {
        $result = [];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $result = $this->obj->get_program_scored();

        $this->assertIsArray($result);
    }

    public function test_get_program_scored_false() {
        $result = [];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);
        
        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $result = $this->obj->get_program(false);

        $this->assertIsArray($result);
    }

    public function test_get_program_scored_true() {
        $result = [];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);
        
        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $result = $this->obj->get_program(true);

        $this->assertIsArray($result);
    }

    public function test_get_weighted_table() {
        $result = [];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $result = $this->obj->get_weighted_table($weight_id);

        $this->assertIsArray($result);
    }

    public function test_get_pb_comparison_sum() {
        $result = [];

        $fy = '25';
        $l_pom_sponsor = ['POM1', 'POM2', 'POM_SPONSOR_CODE'];
        $l_cap_sponsor = ['CAP1', 'CAP2', 'CAPABILITY_SPONSOR_CODE']; 
        $l_ass_area = ['AREA1', 'AREA2', 'ASSESSMENT_AREA_CODE'];
        $program = ['Program1', 'PROGRAM_CODE'];
        $resource_category = ['RESOURCE_CATEGORY_CODE'];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $result = $this->obj->get_pb_comparison_sum($fy, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $program, $resource_category);

        $this->assertIsArray($result);
    }

    public function test_get_sum_budget_and_execution() {
        $result = [];

        $l_pom_sponsor = ['POM1', 'POM2', 'POM_SPONSOR_CODE'];
        $l_cap_sponsor = ['CAP1', 'CAP2', 'CAPABILITY_SPONSOR_CODE']; 
        $l_ass_area = ['AREA1', 'AREA2', 'ASSESSMENT_AREA_CODE'];
        $program = ['Program1', 'PROGRAM_CODE'];
        $resource_category = ['RESOURCE_CATEGORY_CODE'];

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $result = $this->obj->get_sum_budget_and_execution($l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $program, $resource_category);

        $this->assertIsArray($result);
    }

    public function test_program_approval_status() {
        $result = [];

        $table1 = 'table1';
        $page = 'issue';
        $l_pom_sponsor = ['POM1', 'POM2', 'POM_SPONSOR_CODE'];
        $l_cap_sponsor = ['CAP1', 'CAP2', 'CAPABILITY_SPONSOR_CODE']; 
        $l_ass_area = ['AREA1', 'AREA2', 'ASSESSMENT_AREA_CODE'];
        $status = [];
        $filter = false;

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $result = $this->obj->program_approval_status($table1, $page, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $status, $filter);

        $this->assertIsArray($result);
    }

    public function test_get_eoc_summary_data_issue() {
        $result = [];

        $page = 'issue';
        $fy = '25';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'l_pom_sponsor' => 'POM_SPONSOR_CODE',
            'l_cap_sponsor' => 'CAPABILITY_SPONSOR_CODE',
            'l_ass_area' => 'ASSESSMENT_AREA_CODE',
            'program' => 'PROGRAM',
        ];

        $queryResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $result = $this->obj->get_eoc_summary_data($page, $fy, $params);

        $this->assertIsArray($result);
    }

    public function test_get_eoc_summary_data_zbt() {
        $result = [];

        $page = 'zbt';
        $fy = '25';
        $params = [
            'table1' => 'table1',
            'table2' => 'table2',
            'table3' => 'table3',
            'table4' => 'table4',
            'table5' => 'table5',
            'l_pom_sponsor' => 'POM_SPONSOR_CODE',
            'l_cap_sponsor' => 'CAPABILITY_SPONSOR_CODE',
            'l_ass_area' => 'ASSESSMENT_AREA_CODE',
            'program' => 'PROGRAM',
        ];

        $queryResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $result = $this->obj->get_eoc_summary_data($page, $fy, $params);

        $this->assertIsArray($result);
    }

    public function test_get_eoc_historical_issue_summary_pom_query() {
        $result = [];

        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $program = 'Program';
        $selection = '*';

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $result = $this->obj->get_eoc_historical_issue_summary_pom_query($table1, $table2, $table3, $program, $selection);

        $this->assertIsArray($result);
    }

    public function test_get_eoc_historical_issue_summary_query() {
        $result = [];

        $table1 = 'table1';
        $table2 = 'table2';
        $table3 = 'table3';
        $program = 'Program';
        $selection = '*';
        $delta = true;
        $ext = true;

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $result = $this->obj->get_eoc_historical_issue_summary_query($table1, $table2, $table3, $program, $selection, $delta, $ext);

        $this->assertIsArray($result);
    }

    public function test_get_eoc_historical_summary_query_pom_true() {
        $result = [];

        $table1 = 'table1';
        $table2 = 'table2';
        $program = 'Program';
        $selection = '*';
        $delta = true;
        $fy='26';
        $pom=true;

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $result = $this->obj->get_eoc_historical_summary_query($table1, $table2, $program, $selection, $delta=false, $fy, $pom);

        $this->assertIsArray($result);
    }

    public function test_get_eoc_historical_summary_query_pom_false() {
        $result = [];

        $table1 = 'table1';
        $table2 = 'table2';
        $program = 'Program';
        $selection = '*';
        $delta = true;
        $fy='26';
        $pom=false;

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI
            ->method('get_compiled_select')
            ->willReturn($inner_query);

        $actual = $this->obj->get_eoc_historical_summary_query($table1, $table2, $program, $selection, $delta=false, $fy, $pom);

        $this->assertIsArray($actual);
    }

    public function test_get_program_group_list() {
        $result = [];

        $l_pom_sponsor = ['POM1', 'POM2', 'POM_SPONSOR_CODE'];
        $l_cap_sponsor = ['CAP1', 'CAP2', 'CAPABILITY_SPONSOR_CODE']; 
        $l_ass_area = ['AREA1', 'AREA2', 'ASSESSMENT_AREA_CODE'];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')
            ->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $actual = $this->obj->get_program_group_list($l_pom_sponsor, $l_cap_sponsor, $l_ass_area);

        $this->assertIsArray($actual);
    }

    public function test_get_program_list() {
        $result = [];

        $l_pom_sponsor = ['POM1', 'POM2', 'POM_SPONSOR_CODE'];
        $l_cap_sponsor = ['CAP1', 'CAP2', 'CAPABILITY_SPONSOR_CODE']; 
        $l_ass_area = ['AREA1', 'AREA2', 'ASSESSMENT_AREA_CODE'];

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')
            ->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $actual = $this->obj->get_program_list($l_pom_sponsor, $l_cap_sponsor, $l_ass_area);

        $this->assertIsArray($actual);
    }

    public function test_cap_sponsor_count() {
        $result = [
            'cap_sponsor_count' => [],
            'total_events' => 0,
        ];

        $table = 'table1';

        $inner_query = '';

        $queryResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $queryResult->method('result_array')
            ->willReturn($result);

        $this->obj->DBs->SOCOM_UI->method('get')
            ->willReturn($queryResult);

        $this->obj->DBs->SOCOM_UI->method('query')
            ->willReturn($queryResult);

        $actual = $this->obj->cap_sponsor_count($l_pom_sponsor, $l_cap_sponsor, $l_ass_area);

        $this->assertIsArray($actual);
    }
}
?>