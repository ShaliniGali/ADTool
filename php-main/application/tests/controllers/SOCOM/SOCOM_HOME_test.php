​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_HOME_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_model = new SOCOM_model();
    }

    public function test_index() {
        $actual = $this->request('GET', 'socom/index');
        $this->assertNotNull($actual);
    }

    public function test_resource_constrained_coa() {
        $actual = $this->request('GET', 'socom/resource_constrained_coa');
        $this->assertNotNull($actual);
    }

    public function test_zbt_summary() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'cap_sponsor_count' => [
                            'cap_sponsor_count' => 1,
                            'total_events' => 1
                        ],
                        'cap_sponsor_dollar' => [
                            'cap_sponsor_dollar' => 1,
                            'dollars_moved' => 1
                        ],
                        'net_change' => 1,
                        'dollars_moved_resource_category' => [
                            'fiscal_years' => ['2026'],
                            'series_data' => []
                        ],
                        'cap_sponsor_approve_reject' => [
                            'categories' => ['test'],
                            'series_data' => []
                        ]
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/zbt_summary');
        $this->assertNotNull($actual);
    }

    public function test_issue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'cap_sponsor_count' => [
                            'cap_sponsor_count' => 1,
                            'total_events' => 1
                        ],
                        'cap_sponsor_dollar' => [
                            'cap_sponsor_dollar' => 1,
                            'dollars_moved' => 1
                        ],
                        'net_change' => 1,
                        'dollars_moved_resource_category' => [
                            'fiscal_years' => ['2026'],
                            'series_data' => []
                        ],
                        'cap_sponsor_approve_reject' => [
                            'categories' => ['test'],
                            'series_data' => []
                        ]
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/issue');
        $this->assertNotNull($actual);
    }

    public function test_pb_comparsion() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_pb_comparsion_sum' => [
                            'FISCAL_YEAR' => 2020,
                            'SUM_PB_2020' => 1
                        ],
                        'get_sponsor' => 'test',
                        'get_assessment_area_code' => 'test'
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/pb_comparsion');
        $this->assertNotNull($actual);
    }

    public function test_update_program_filter_pb() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_program_list' => []
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/pb_comparison/filter/update', [
            'pom' => ['test'],
            'cs' => ['test'],
            'ass-area' => ['test'],
            'section' => 'pb_comparison',
            'page' => ['test']
        ]);
        $this->assertNotNull($actual);
    }

    public function test_update_program_filter_exit() {
      $actual = $this->request('POST', 'socom/pb_comparison/filter/update', []);
      $this->assertNotNull($actual);
  }
    
    public function test_update_program_filter_program_summary() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_program_list' => [],
                        'program_approval_status' => [
                            [
                                'PROGRAM_GROUP' => 'A'
                            ],
                            [
                                'PROGRAM_GROUP' => 'A'
                            ],
                            [
                                'PROGRAM_GROUP' => 'B'
                            ]
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/program_summary/filter/update', [
            'pom' => ['test'],
            'cs' => ['test'],
            'ass-area' => ['test'],
            'section' => 'program_summary',
            'page' => 'issue'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_update_pb_comparsion_graph() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_pb_comparsion_sum' => [
                            'FISCAL_YEAR' => 2022,
                            'SUM_PB_2022' => [
                                'SUM_PB_2022' => 1
                            ]
                        ],
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/pb_comparsion/graph/update', [
            'placeholder' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_budget_to_execution() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_sponsor' => 'test',
                        'get_assessment_area_code' => 'test',
                        'get_sum_budget_and_execution' => [
                            [
                                'FISCAL_YEAR' => 2024,
                                'SUM_BUDGET' => 1,
                                'SUM_EXECUTION' => 1
                            ]
                        ],
                        'get_resource_category_code' => [
                            [
                                'RESOURCE_CATEGORY_CODE' => 2,
                                'RESOURCE_CATEGORY' => 1
                            ],
                            [
                                'RESOURCE_CATEGORY_CODE' => 1,
                                'RESOURCE_CATEGORY' => 1
                            ]
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/budget_to_execution');
        $this->assertNotNull($actual);
    }

    public function test_program_summary() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_sponsor' => 'test',
                        'get_assessment_area_code' => 'test',
                        'issue_program_summary_card' => [
                            'total_events' => 1,
                            'dollars_moved' => 1,
                            'net_change' => 1
                        ],
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin' => [],
                        'get_issue_program_summary' => []
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/issue/program_summary');
        $this->assertNotNull($actual);
    }
    
    public function test_update_program_summary_table() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_user_assigned_tag' => ['1', '2'],
                        'get_user_assigned_bin' => ['1', '2'],
                        'get_issue_eoc' => [],
                        'get_user_assigned_bin_by_program' => [],
                        'get_issue_program_summary' => [
                          0 => [
                            'PROGRAM_NAME' => '5G BEAR',
                            'EOC_CODES' => [
                              0 => '5GBEA.XXX',
                            ],
                            'approval_status' => ['Completed', 'Completed', 'Completed', 'Completed', 'Completed'],
                            'EOC_CODES' => ['EOC1', 'EOC2', 'EOC3', 'EOC4', 'EOC5', 'EOC6'],
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                            'PROGRAM_GROUP' => '5G',
                            'APPROVAL_ACTION_STATUS' => 'COMPLETED',
                            'JCA_ALIGNMENT' => [
                              0 => NULL,
                            ],
                            'RESOURCE_K' => [
                              '26EXT' => [
                                2026 => 18401,
                                2027 => 18531,
                                2028 => 18661,
                                2029 => 18791,
                                2030 => 18921,
                              ],
                              '26ZBT' => [
                                2026 => 18401,
                                2027 => 18531,
                                2028 => 18661,
                                2029 => 18791,
                                2030 => 18921,
                              ],
                              '26ZBT_DELTA' => [
                                2026 => 0,
                                2027 => 0,
                                2028 => 0,
                                2029 => 0,
                                2030 => 0,
                              ],
                              '26ISS_REQUESTED' => [
                                2026 => 20887,
                                2027 => 21813,
                                2028 => 22739,
                                2029 => 23665,
                                2030 => 24592,
                              ],
                              '26ISS_REQUESTED_DELTA' => [
                                2026 => 2486,
                                2027 => 3282,
                                2028 => 4078,
                                2029 => 4874,
                                2030 => 5671,
                              ],
                            ],
                          ],
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/program_summary/table/update', [
            'test' => 'test',
            'program' => [
                'Test1',
                'Test2',
                'Test3',
                'Test4',
                'Test5',
                'Test6'
            ]
        ]);
        $this->assertNotNull($actual);
    }

    public functIon test_update_program_summary_table_exit() {
      $actual = $this->request('POST', 'socom/issue/program_summary/table/update', []);
      $this->assertNotNull($actual);
    }

    public function test_update_program_summary_table_eoc_value() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_user_assigned_tag' => ['1', '2'],
                        'get_user_assigned_bin' => ['1', '2'],
                        'get_issue_eoc' => [['EOC_CODE' => 'EOC_CODE']],
                        'get_user_assigned_bin_by_program' => [],
                        'get_issue_program_summary' => [
                          0 => [
                            'PROGRAM_NAME' => '5G BEAR',
                            'EOC_CODES' => [
                              0 => '5GBEA.XXX',
                            ],
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                            'PROGRAM_GROUP' => '5G',
                            'APPROVAL_ACTION_STATUS' => 'COMPLETED',
                            'JCA_ALIGNMENT' => [
                              0 => NULL,
                            ],
                            'RESOURCE_K' => [
                              '26EXT' => [
                                2026 => 18401,
                                2027 => 18531,
                                2028 => 18661,
                                2029 => 18791,
                                2030 => 18921,
                              ],
                              '26ZBT' => [
                                2026 => 18401,
                                2027 => 18531,
                                2028 => 18661,
                                2029 => 18791,
                                2030 => 18921,
                              ],
                              '26ZBT_DELTA' => [
                                2026 => 0,
                                2027 => 0,
                                2028 => 0,
                                2029 => 0,
                                2030 => 0,
                              ],
                              '26ISS_REQUESTED' => [
                                2026 => 20887,
                                2027 => 21813,
                                2028 => 22739,
                                2029 => 23665,
                                2030 => 24592,
                              ],
                              '26ISS_REQUESTED_DELTA' => [
                                2026 => 2486,
                                2027 => 3282,
                                2028 => 4078,
                                2029 => 4874,
                                2030 => 5671,
                              ],
                            ],
                          ],
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/program_summary/table/update', [
            'test' => 'test',
            'program' => [
                'Test1',
                'Test2',
                'Test3',
                'Test4',
                'Test5',
                'Test6'
            ]
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_update_program_summary_table_empty() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin' => [],
                        'get_issue_program_summary' => []
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/program_summary/table/update', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_update_program_summary_card() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin' => [],
                        'issue_program_summary_card' => [
                            'total_events' => 1,
                            'dollars_moved' => 1,
                            'net_change' => 1
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/program_summary/card/update', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_update_program_summary_card_empty() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin' => [],
                        'issue_program_summary_card' => [
                            'total_events' => 1,
                            'dollars_moved' => 1,
                            'net_change' => 1
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/program_summary/card/update', [
            'approval-status' => [true],
            'program' => [
                'Test1',
                'Test2',
                'Test3',
                'Test4',
                'Test5',
                'Test6'
            ]
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_get_dollars_moved_resource_category() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'dollars_moved_resource_category' => [
                            'fiscal_years' => [],
                            'series_data' => []
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/get_dollars_moved_resource_category', [
            'filter' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_get_dollars_moved_resource_category_exit() {
      $actual = $this->request('POST', 'socom/get_dollars_moved_resource_category', []);
      $this->assertNotNull($actual);
  }

    public function test_historical_pom_pageZbtSummary() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_historical_pom_data' => [
                            '2024' => [],
                            '2025' => [],
                            '2026' => []
                        ],
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin' => []
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/zbt_summary/historical_pom', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_historal_pom_pageIssue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_historical_pom_data' => [
                            'base_k' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'BASE_K' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'BASE_K' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'BASE_K' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2026,
                                'ISS_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2027,
                                'ISS_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2028,
                                'ISS_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2026,
                                'POM_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2027,
                                'POM_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2028,
                                'POM_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ]
                        ],
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin' => [],
                        'get_user_assigned_bin_by_program' => [
                            [
                                '1' => 'bin'
                            ]
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/historical_pom', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_eoc_summary_pageNotIssue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_eoc_summary_data' => []
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }
    public function test_eoc_summary_pageIssue_wo_data() {
      $this->request->addCallable(
        function ($CI) {
            $SOCOM_model = $this->getDouble(
                'SOCOM_model', [
                    'get_eoc_summary_data' => [
                      'issue_base_zbt_amt' => [

                      ],
                      'issue_prop_amt' => [

                      ]
                    ]
                  ]
                );
                $CI->SOCOM_model = $SOCOM_model;
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 1
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;
                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'get_ao_by_event_id_user_id' => [
                            'AO_RECOMENDATION' => 'Approve',
                            'ao-test-0-eoc-approval' => [
                                'AO_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'get_ad_by_event_id_user_id' => [
                            'ad-test-0-eoc-approval' => [
                                'AD_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'is_ao_user' => true,
                        'is_ad_user' => true
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ],
                        'get_user' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/eoc_summary', [
            'pom' => [
              ['test']
            ],
            'cs' => [
              ['test']
            ],
            'ass-area' => [
                ['test']
            ],
            'program' => 'Test'
        ]);
        $this->assertNotNull($actual);
    }


    public function test_eoc_summary_pageIssue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_eoc_summary_data' => [
                            'issue_base_zbt_amt' => [
                              0 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'BASE_K' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'BASE_K' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'BASE_K' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2026,
                                'ISS_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2027,
                                'ISS_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2028,
                                'ISS_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2026,
                                'POM_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2027,
                                'POM_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2028,
                                'POM_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 1
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;
                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'get_ao_by_event_id_user_id' => [
                            'ao-test-0-eoc-approval' => [
                                'AO_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'get_ad_by_event_id_user_id' => [
                            'ad-test-0-eoc-approval' => [
                                'AD_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'is_ao_user' => true,
                        'is_ad_user' => true
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ],
                        'get_user' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/eoc_summary', [
            'pom' => [],
            'cs' => [],
            'ass-area' => [
                ['test']
            ],
            'program' => 'Teet'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_eoc_summary_pageZbtSummary() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_eoc_summary_data' => [
                            'base_k' => [
                              0 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2026,
                                'BASE_K' => 0,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              1 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2027,
                                'BASE_K' => 0,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              2 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2028,
                                'BASE_K' => 0,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              3 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2029,
                                'BASE_K' => 0,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              4 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2030,
                                'BASE_K' => 0,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              5 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2026,
                                'BASE_K' => 763,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              6 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2027,
                                'BASE_K' => 863,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              7 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2028,
                                'BASE_K' => 963,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              8 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2029,
                                'BASE_K' => 1063,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              9 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26EXT' => '26EXT',
                                'FISCAL_YEAR' => 2030,
                                'BASE_K' => 1163,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                            ],
                            'prop_amt' => [
                              0 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2026,
                                'PROP_AMT' => 76,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              1 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2027,
                                'PROP_AMT' => 86,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              2 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2028,
                                'PROP_AMT' => 96,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              3 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2029,
                                'PROP_AMT' => 106,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              4 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2030,
                                'PROP_AMT' => 116,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              5 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2026,
                                'PROP_AMT' => 835,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              6 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2027,
                                'PROP_AMT' => 966,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              7 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2028,
                                'PROP_AMT' => 1098,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              8 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2029,
                                'PROP_AMT' => 1230,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              9 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'FISCAL_YEAR' => 2030,
                                'PROP_AMT' => 1361,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                            ],
                            'delta_amt' => [
                              0 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 76,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              1 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 86,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              2 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 96,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              3 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 106,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              4 => [
                                'EOC' => 'OTHERAA.XXN',
                                'EVENT_NAME' => 'JM_EVENT_04',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 116,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              5 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 72,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              6 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 103,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              7 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 135,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              8 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 167,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                              9 => [
                                'EOC' => 'OTHERAA.XXX',
                                'EVENT_NAME' => 'JA_EVENT_00',
                                'ASSESSMENT_AREA_CODE' => 'C',
                                'POM_SPONSOR_CODE' => 'USASOC',
                                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                                'RESOURCE_CATEGORY_CODE' => 'O&M $',
                                'EVENT_JUSTIFICATION' => 'Cat is hungry',
                                'POM_POSITION_CODE' => '26EXT',
                                '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 198,
                                'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                              ],
                            ],
                            'issue_base_zbt_amt' => [ 100
                            ],
                            'issue_base_ext_amt' => [ 100
                            ],
                            'issue_prop_amt' => [ 100
                            ],
                            'issue_delta_amt' => [ 100
                            ],
                        ]
                    ]
                );
                $CI->DBs->SOCOM_model = $SOCOM_model;
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 1
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;
                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'get_ao_by_event_id_user_id' => [],
                        'get_ad_by_event_id_user_id' => [],
                        'is_ao_user' => true,
                        'is_ad_user' => false
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary', [
          'pom' => [],
          'cs' => [],
          'ass-area' => [
              ['test']
          ],
          'program' => 'Teet'
        ]);
        $this->assertNotNull($actual);
    }

    
    public function test_pb_comparison() {
        $result = [
            [
                'FISCAL_YEAR' => 2020,
                'SUM_PB_2020' => 16720130,
              ],
              [
                'FISCAL_YEAR' => 2021,
                'SUM_PB_2020' => 16855287,
              ],
              [
                'FISCAL_YEAR' => 2022,
                'SUM_PB_2020' => 16981003,
              ],
              [
                'FISCAL_YEAR' => 2023,
                'SUM_PB_2020' => 17103103,
              ],
              [
                'FISCAL_YEAR' => 2024,
                'SUM_PB_2020' => 17227927,
              ],
              [
                'FISCAL_YEAR' => 2025,
                'SUM_PB_2020' => 17227927,
              ]
        ];
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getMockBuilder('SOCOM_model')
                                    ->disableOriginalConstructor()
                                    ->getMock();


                // Mock the method get_pb_comparison_sum
                $SOCOM_model->expects($this->exactly(5))
                            ->method('get_pb_comparison_sum')
                            ->willReturnOnConsecutiveCalls(
                                [
                                    [
                                        'FISCAL_YEAR' => 2020,
                                        'SUM_PB_2020' => 16720130,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2021,
                                        'SUM_PB_2020' => 16855287,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2022,
                                        'SUM_PB_2020' => 16981003,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2023,
                                        'SUM_PB_2020' => 17103103,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2024,
                                        'SUM_PB_2020' => 17227927,
                                      ]
                                ],
                                [
                                      [
                                        'FISCAL_YEAR' => 2021,
                                        'SUM_PB_2021' => 16855287,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2022,
                                        'SUM_PB_2021' => 16981003,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2023,
                                        'SUM_PB_2021' => 17103103,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2024,
                                        'SUM_PB_2021' => 17227927,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2025,
                                        'SUM_PB_2021' => 16720130,
                                      ]
                                ],
                                [
                                      [
                                        'FISCAL_YEAR' => 2022,
                                        'SUM_PB_2022' => 16981003,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2023,
                                        'SUM_PB_2022' => 17103103,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2024,
                                        'SUM_PB_2022' => 17227927,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2025,
                                        'SUM_PB_2022' => 16720130,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2026,
                                        'SUM_PB_2022' => 16855287,
                                      ]
                                ],
                                [
                                    [
                                    'FISCAL_YEAR' => 2023,
                                    'SUM_PB_2023' => 17103103,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2024,
                                    'SUM_PB_2023' => 17227927,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2025,
                                    'SUM_PB_2023' => 16720130,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2026,
                                    'SUM_PB_2023' => 16855287,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2027,
                                    'SUM_PB_2023' => 16981003,
                                    ],
                                ],
                                [
                                    [
                                    'FISCAL_YEAR' => 2024,
                                    'SUM_PB_2024' => 17227927,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2025,
                                    'SUM_PB_2024' => 16720130,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2026,
                                    'SUM_PB_2024' => 16855287,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2027,
                                    'SUM_PB_2024' => 16981003,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2028,
                                    'SUM_PB_2024' => 17103103,
                                    ],
                                ]      
                            );

                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/pb_comparison');
        $this->assertNotNull($actual);
    }

    public function test_update_pb_comparison_graph_exit() {
        $actual = $this->request('POST', 'socom/pb_comparison/graph/update', []);
        $this->assertNotNull($actual);
    }
    
    public function test_update_pb_comparison_graph() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getMockBuilder('SOCOM_model')
                                    ->disableOriginalConstructor()
                                    ->getMock();


                // Mock the method get_pb_comparison_sum
                $SOCOM_model->expects($this->exactly(5))
                            ->method('get_pb_comparison_sum')
                            ->willReturnOnConsecutiveCalls(
                                [
                                    [
                                        'FISCAL_YEAR' => 2020,
                                        'SUM_PB_2020' => 16720130,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2021,
                                        'SUM_PB_2020' => 16855287,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2022,
                                        'SUM_PB_2020' => 16981003,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2023,
                                        'SUM_PB_2020' => 17103103,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2024,
                                        'SUM_PB_2020' => 17227927,
                                      ]
                                ],
                                [
                                      [
                                        'FISCAL_YEAR' => 2021,
                                        'SUM_PB_2021' => 16855287,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2022,
                                        'SUM_PB_2021' => 16981003,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2023,
                                        'SUM_PB_2021' => 17103103,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2024,
                                        'SUM_PB_2021' => 17227927,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2025,
                                        'SUM_PB_2021' => 16720130,
                                      ]
                                ],
                                [
                                      [
                                        'FISCAL_YEAR' => 2022,
                                        'SUM_PB_2022' => 16981003,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2023,
                                        'SUM_PB_2022' => 17103103,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2024,
                                        'SUM_PB_2022' => 17227927,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2025,
                                        'SUM_PB_2022' => 16720130,
                                      ],
                                      [
                                        'FISCAL_YEAR' => 2026,
                                        'SUM_PB_2022' => 16855287,
                                      ]
                                ],
                                [
                                    [
                                    'FISCAL_YEAR' => 2023,
                                    'SUM_PB_2023' => 17103103,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2024,
                                    'SUM_PB_2023' => 17227927,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2025,
                                    'SUM_PB_2023' => 16720130,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2026,
                                    'SUM_PB_2023' => 16855287,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2027,
                                    'SUM_PB_2023' => 16981003,
                                    ],
                                ],
                                [
                                    [
                                    'FISCAL_YEAR' => 2024,
                                    'SUM_PB_2024' => 17227927,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2025,
                                    'SUM_PB_2024' => 16720130,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2026,
                                    'SUM_PB_2024' => 16855287,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2027,
                                    'SUM_PB_2024' => 16981003,
                                    ],
                                    [
                                    'FISCAL_YEAR' => 2028,
                                    'SUM_PB_2024' => 17103103,
                                    ],
                                ]      
                            );

                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/pb_comparison/graph/update', [
            'pom' => [],
            'cs' => [],
            'ass-area' => [],
            'section' => 'program_summary',
            'resource_category' => []
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_update_budget_to_execution_graph() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_sum_budget_and_execution' => [
                            [
                                'FISCAL_YEAR' => 2024,
                                'SUM_BUDGET' => 1,
                                'SUM_EXECUTION' => 1
                            ]
                        ]
                    ]
                );

                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('POST', 'socom/budget_to_execution/graph/update', [
            'pom' => [],
            'cs' => [],
            'ass-area' => [],
            'program' => [],
            'resource_category' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_update_budget_to_execution_graph_exit() {
  
      $actual = $this->request('POST', 'socom/budget_to_execution/graph/update', []);
      $this->assertNotNull($actual);
  }
    
    public function test_eoc_historical_pom_pageIssue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_historical_pom_data' => [
                            'base_k' => [
                              0 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'BASE_K' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'BASE_K' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'BASE_K' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2026,
                                'ISS_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2027,
                                'ISS_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2028,
                                'ISS_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2026,
                                'POM_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2027,
                                'POM_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2028,
                                'POM_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ]
                        ],
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin_by_program' => []
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 1
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;
                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'get_ao_by_event_id_user_id' => [
                            'ao-test-0-eoc-approval' => [
                                'AO_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'get_ad_by_event_id_user_id' => [
                            'ad-test-0-eoc-approval' => [
                                'AD_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'is_ao_user' => true,
                        'is_ad_user' => true
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ],
                        'get_user' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'socom/issue/eoc_historical_pom', [
            'pom' => [],
            'cs' => [],
            'ass-area' => [
                ['test']
            ],
            'program' => 'Teet'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_eoc_historical_pom_pageZbt() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_historical_pom_data' => [
                            'base_k' => [
                              0 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'BASE_K' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'BASE_K' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'BASE_K' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'program_name' => 'OTHER ACTIONS',
                                '24EXT' => '24EXT',
                                'BASE_K' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED' => '24ZBT',
                                'PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ZBT_REQUESTED_DELTA' => '24ZBT DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2026,
                                'ISS_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2027,
                                'ISS_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'POM_POSITION_CODE' => '24ISS',
                                'FISCAL_YEAR' => 2028,
                                'ISS_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED' => '24ISS',
                                'ISS_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'issue_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24ZBT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24ISS_REQUESTED_DELTA' => '24ISS DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_prop_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2026,
                                'POM_PROP_AMT' => 924,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2027,
                                'POM_PROP_AMT' => 1020,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'FISCAL_YEAR' => 2028,
                                'POM_PROP_AMT' => 1116,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED' => '24POM',
                                'POM_PROP_AMT' => 0,
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'pom_delta_amt' => [
                              0 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2026,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              1 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2027,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              2 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2028,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              3 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2029,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                              4 => [
                                'PROGRAM_NAME' => 'OTHER ACTIONS',
                                '24POM_REQUESTED_DELTA' => '24EXT to 24POM DELTA',
                                'POM_POSITION_CODE' => '24EXT',
                                'FISCAL_YEAR' => 2030,
                                'DELTA_AMT' => 0,
                                'FISCAL_YEARS' => '2024, 2025, 2026, 2027, 2028',
                              ],
                            ],
                            'approval_status' => [
                                ['APPROVAL_ACTION_STATUS' => 'OTHER ACTIONS' ],
                                ['APPROVAL_ACTION_STATUS' => 'OTHER ACTIONS' ],
                                ['APPROVAL_ACTION_STATUS' => 'OTHER ACTIONS' ],
                                ['APPROVAL_ACTION_STATUS' => 'OTHER ACTIONS' ],
                                ['APPROVAL_ACTION_STATUS' => 'OTHER ACTIONS' ]
                            ]
                        ],
                        'get_user_assigned_tag' => [],
                        'get_user_assigned_bin_by_program' => []
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 1
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;
                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'get_ao_by_event_id_user_id' => [
                            'ao-test-0-eoc-approval' => [
                                'AO_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'get_ad_by_event_id_user_id' => [
                            'ad-test-0-eoc-approval' => [
                                'AD_USER_ID' => '2',
                                'email' => '2@gmail.com'
                            ]
                        ],
                        'is_ao_user' => true,
                        'is_ad_user' => true
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ],
                        'get_user' => [
                            'name' => 'test',
                            'email' => 'test@rhombuspower.com'
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'socom/zbt_summary/eoc_historical_pom', [
            'pom' => [],
            'cs' => [],
            'ass-area' => [
                ['test']
            ],
            'program' => 'Teet',
            'dropdowns' => ['Approve','Disapprove','123']
        ]);
        $this->assertNotNull($actual);
    }
    

    public function test_eoc_summary_pageZbtSummary_format_datatable_eoc_summary() {
      $this->request->addCallable(
          function ($CI) {
              $SOCOM_model = $this->getDouble(
                  'SOCOM_model', [
                      'get_eoc_summary_data' => [
                        'base_k' => [
                          0 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2026,
                            'BASE_K' => 0,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          1 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2027,
                            'BASE_K' => 0,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          2 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2028,
                            'BASE_K' => 0,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          3 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2029,
                            'BASE_K' => 0,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          4 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2030,
                            'BASE_K' => 0,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          5 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2026,
                            'BASE_K' => 763,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          6 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2027,
                            'BASE_K' => 863,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          7 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2028,
                            'BASE_K' => 963,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          8 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2029,
                            'BASE_K' => 1063,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          9 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26EXT' => '26EXT',
                            'FISCAL_YEAR' => 2030,
                            'BASE_K' => 1163,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                        ],
                        'prop_amt' => [
                          0 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2026,
                            'PROP_AMT' => 76,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          1 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2027,
                            'PROP_AMT' => 86,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          2 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2028,
                            'PROP_AMT' => 96,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          3 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2029,
                            'PROP_AMT' => 106,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          4 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2030,
                            'PROP_AMT' => 116,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          5 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2026,
                            'PROP_AMT' => 821,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          6 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2027,
                            'PROP_AMT' => 936,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          7 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2028,
                            'PROP_AMT' => 1052,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          8 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2029,
                            'PROP_AMT' => 1167,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          9 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED' => '26ZBT REQUESTED',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'FISCAL_YEAR' => 2030,
                            'PROP_AMT' => 1283,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                        ],
                        'delta_amt' => [
                          0 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2026,
                            'DELTA_AMT' => 76,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          1 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2027,
                            'DELTA_AMT' => 86,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          2 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2028,
                            'DELTA_AMT' => 96,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          3 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2029,
                            'DELTA_AMT' => 106,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          4 => [
                            'EOC' => 'OTHERAA.XXN',
                            'EVENT_NAME' => 'JM_EVENT_04',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Mid-Jan Review',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2030,
                            'DELTA_AMT' => 116,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          5 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2026,
                            'DELTA_AMT' => 58,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          6 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2027,
                            'DELTA_AMT' => 73,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          7 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2028,
                            'DELTA_AMT' => 89,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          8 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2029,
                            'DELTA_AMT' => 104,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                          9 => [
                            'EOC' => 'OTHERAA.XXX',
                            'EVENT_NAME' => 'JF_EVENT_36',
                            'ASSESSMENT_AREA_CODE' => 'C',
                            'POM_SPONSOR_CODE' => 'USASOC',
                            'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                            'RESOURCE_CATEGORY_CODE' => 'O&M $',
                            'EVENT_JUSTIFICATION' => 'Something possibly',
                            'POM_POSITION_CODE' => '26EXT',
                            '26ZBT_REQUESTED_DELTA' => '26ZBT REQUESTED DELTA',
                            'FISCAL_YEAR' => 2030,
                            'DELTA_AMT' => 120,
                            'FISCAL_YEARS' => '2026, 2027, 2028, 2029, 2030',
                          ],
                        ],
                        'issue_base_zbt_amt' => [
                        ],
                        'issue_base_ext_amt' => [
                        ],
                        'issue_prop_amt' => [
                        ],
                        'issue_delta_amt' => [
                        ],
                      ]
                  ]
              );
              $CI->DBs->SOCOM_model = $SOCOM_model;
              $SOCOM_Program_model = $this->getDouble(
                  'SOCOM_Program_model', [
                      'get_program_id' => 1
                  ]
              );
              $CI->SOCOM_Program_model = $SOCOM_Program_model;

              

              $SOCOM_AOAD_model = $this->getDouble(
                'SOCOM_AOAD_model', [
                    'get_ao_by_event_id_user_id' => [
                      [
                        'AO_RECOMENDATION' => 'Approve',
                        'ID' => 0,
                        'AO_COMMENT' => 'Hello',
                        'AO_USER_ID' => 0
                      ]
                    ],
                    'get_ad_by_event_id_user_id' => [
                      [
                      'AD_RECOMENDATION' => 'Approve',
                      'ID' => 0,
                      'AD_COMMENT' => 'Hello',
                      'AD_USER_ID' => 0
                      ]
                        
                    ],
                    'is_ao_user' => true,
                    'is_ad_user' => true
                ]
              );
              $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;

              $SOCOM_Users_model = $this->getDouble(
                  'SOCOM_Users_model', [
                      'get_user' => [
                          'test@rhombuspower.com'
                      ],
                      'get_users' => [
                        'test@rhombuspower.com'
                      ]
                  ]
              );
              $CI->SOCOM_Users_model = $SOCOM_Users_model;
          }
      );
      $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary', [
        'pom' => [],
        'cs' => [],
        'ass-area' => [
            ['test']
        ],
        'program' => 'Test',
        'dropdowns' => ['Approve','Disapprove','123']
      ]);
      $this->assertNotNull($actual);
  }

}