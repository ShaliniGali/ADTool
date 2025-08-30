​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_COA_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
    }

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('DT_EXT_2024_seeder');
        $CI->seeder->call('DT_EXT_2025_seeder');
        $CI->seeder->call('DT_EXT_2026_seeder');
        $CI->seeder->call('DT_ZBT_2024_seeder');
        $CI->seeder->call('DT_ZBT_2025_seeder');
        $CI->seeder->call('DT_ZBT_2026_seeder');
        $CI->seeder->call('DT_ISS_2024_seeder');
        $CI->seeder->call('DT_ISS_EXTRACT_2024_seeder');
        $CI->seeder->call('DT_ISS_EXTRACT_2026_seeder');
        $CI->seeder->call('DT_ISS_2025_seeder');
        $CI->seeder->call('DT_ISS_2026_seeder');
        $CI->seeder->call('DT_POM_2024_seeder');
        $CI->seeder->call('DT_POM_2025_seeder');
    }

    public function test_get_coa_user_list_responseOk() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_user_saved_coa' => [
                            'test' => 'test'
                        ]
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        $actual = $this->request('GET', 'optimizer/get_coa');
        $this->assertNotNull($actual);
    }

    public function test_get_coa_user_list_responseFail() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_user_saved_coa' => NULL
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        $actual = $this->request('GET', 'optimizer/get_coa');
        $this->assertNotNull($actual);
    }

    public function test_get_coa_user_data_invalidPost() {
        $actual = $this->request('POST', 'optimizer/get_coa_data');
        $this->assertIsString($actual);
    }

    public function test_get_coa_user_data_validPost() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_user_saved_coa_data' => [
                            'test' => 'test'
                        ]
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        $actual = $this->request('POST', 'optimizer/get_coa_data', [
            'ids' => [1, 2, 3]
        ]);
        $this->assertNotNull($actual);
    }

    public function test_save_coa_invalidPost() {
        $actual = $this->request('POST', 'optimizer/save_coa');
        $this->assertIsString($actual);
    }

    public function test_save_coa_resultNotFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'store_user_run' => TRUE
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        $actual = $this->request('POST', 'optimizer/save_coa', [
            'id' => encrypted_string(1, 'encode'),
            'name' => 'test',
            'description' => 'test'
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_coa_resultFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'store_user_run' => FALSE
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        $actual = $this->request('POST', 'optimizer/save_coa', [
            'id' => intval(encrypted_string('1', 'encode')),
            'name' => 'test',
            'description' => 'test'
        ]);
        $this->assertIsString($actual);
    }

    public function test_insert_coa_table_row() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_dropdown_codes' => FALSE,
                        'get_saved_coa_optimizer_input' => ['option' => 1, 'storm_flag' => false]
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/insert', [
            'program_codes' => intval(encrypted_string('1', 'encode')),
            'eoc_codes' => 'test',
            'description' => 'test'
        ]);
        $this->assertIsString($actual);
    }

    public function test_update_coa_table_insert_dropdown() {
        $result = array(
            array(
                'ID' => 'OTHERAA_USASOC_USASOC',
                'PROGRAM_GROUP' => 'OTHER',
                'PROGRAM_CODE' => 'OTHERAA',
                'PROGRAM_NAME' => 'OTHER ACTIONS',
                'PROGRAM_TYPE_CODE' => 'O',
                'PROGRAM_SUB_TYPE_CODE' => 'O',
                'PROGRAM_DESCRIPTION' => 'txt',
                'CAPABILITY_SPONSOR_CODE' => 'USASOC',
                'ASSESSMENT_AREA_CODE' => 'C',
                'POM_SPONSOR_CODE' => 'USASOC',
                'JCA_LV1_ID' => '3',
                'JCA_LV2_ID' => '5',
                'JCA_LV3_ID' => '1',
                'STORM_ID' => 'OTHER_USASOC_1',
                'EOC_CODE' => 'OTHERAA.XXX',
                'RESOURCE_CATEGORY_CODE' => 'O&M $'
            )
        );

        MonkeyPatch::patchMethod(SOCOM_COA::class, ['get_saved_coa_score' => "13.5"]);

        MonkeyPatch::patchMethod(SOCOM_COA_model::class, ['get_coa_metadata' => $result,
        'get_dropdown_codes' => [1]]);

        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/insert/update', [
           'program_code' => 'program_code',
           'pom_sponsor_code' => 'program_code',
           'capability_sponsor_code' => 'program_code',
           'resource_category_code' => 'program_code',
           'eoc_codes_filter' => 'eoc_codes_filter',
        ]);
        $this->assertIsString($actual);
    }

    public function test_update_coa_table_insert_dropdown_noParam() {
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/insert/update', []);
        $this->assertIsString($actual);
    }

    public function test_get_coa_table_row_budget() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'fetchOutputInfo' => json_encode([
                            'PROGRAM' => [
                                'resource_k' => [
                                    'CODE' => [
                                        2026 => 12
                                    ]
                                ]
                            ]
                        ], true)
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/insert/get', [
            'program_id' => 'PROGRAM',
            'eoc_code' => 'CODE',
        ]);
        $this->assertIsString($actual);
    }


    public function test_get_coa_table_row_budget_noParam() {
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/insert/get', []);
        $this->assertIsString($actual);
    }

    public function test_get_output_table_with_storm_view() {

        $this->weighted_score_option = [
            1 => 'both',
            2 => 'guidance',
            3 => 'pom',
            4 => 'storm'
        ];

        $result = array(
            'OTHERAA_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERAA.XXX' => array(
                        '2026' => 763,
                        'resource_category_code' => 'O&M $',
                        '2027' => 863,
                        '2028' => 963,
                        '2029' => 1063,
                        '2030' => 1163
                    )
                ),
                'program_code' => 'OTHERAA',
                'eoc_code' => array(
                    '0' => 'OTHERAA.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            ),
            'OTHERDD_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERDD.XXX' => array(
                        '2026' => 825,
                        'resource_category_code' => 'O&M $',
                        '2027' => 900,
                        '2028' => 975,
                        '2029' => 1050,
                        '2030' => 1125
                    )
                ),
                'program_code' => 'OTHERDD',
                'eoc_code' => array(
                    '0' => 'OTHERDD.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            )
        );

        $override_table_session = json_encode([
            'budget_uncommitted' => [
                [
                    "2026" => "20000",
                    "2027" => "20000",
                    "2028" => "20000",
                    "2029" => "20000",
                    "2030" => "20000",
                    "FYDP" => 100000,
                    "TYPE" => 'Proposed Budget $K'
                ],
                [
                    "2026" => 487,
                    "2027" => 2889,
                    "2028" => 11872,
                    "2029" => 11665,
                    "2030" => 11458,
                    "FYDP" => 38371,
                    "TYPE" => 'Uncommitted $K'
                ]
            ],
            'coa_output' => [
                [
                    "2026" => "5028",
                    "2027" => "5058",
                    "2028" => "5088",
                    "2029" => "5118",
                    "2030" => "5148",
                    "EOC" => "5GBEE.XXX",
                    "FYDP" => 25440,
                    "Program" => "5GBEE",
                    "DT_RowId" => 0,
                    "CAP SPONSOR" => "MARSOC",
                    "POM SPONSOR" => "MARSOC",
                    "StoRM Score" => 57,
                    "RESOURCE CATEGORY" => "CATEGORY1"
                ],
                [
                    "2026" => 19513,
                    "2027" => 17111,
                    "2028" => 8128,
                    "2029" => 8335,
                    "2030" => 8542,
                    "EOC" => "",
                    "FYDP" => 61629,
                    "Program" => "",
                    "DT_RowId" => 5,
                    "CAP SPONSOR" => "",
                    "POM SPONSOR" => "",
                    "StoRM Score" => "",
                    "RESOURCE CATEGORY" => 'Committed Grand Total $K'
                ]
            ]
        ]);

        $override_table_metadata = json_encode([
            'metadata_field1' => 'metadata_value1',
            'metadata_field2' => 'metadata_value2'
        ]);
        $override_justification = json_encode([
            'justification' => []
        ]);
        $session_data_mock = [
            [
                'OVERRIDE_TABLE_SESSION' => $override_table_session,
                'OVERRIDE_TABLE_METADATA' => $override_table_metadata,
                'OVERRIDE_FORM_SESSION' => $override_justification
            ]
        ];

        MonkeyPatch::patchMethod(SOCOM_COA::class, ['get_saved_coa_score' => "13.5"]);

        MonkeyPatch::patchMethod(SOCOM_COA_model::class, ['get_fiscal_years' => [2026, 2027, 2028, 2029, 2030],
        'fetchOutputInfo' => json_encode($result, true), 'get_manual_override_data' => $session_data_mock,
        'get_manual_override_status' => ['STATE' => 'approved'], 'get_saved_coa_optimizer_input' => ['storm_flag' => 'storm', 'option' => 3]]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, ['get_user_info' => ['name' => 'name']]);
        
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/output', [
            'ids' => [1, 2, 3],
            'budget' => [100000, 100000, 100000, 100000, 100000],
            'coa_table_id' => 789
        ]);
        $this->assertIsString($actual);
    }
    

    public function test_get_output_table() {

        $result = array(
            'OTHERAA_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERAA.XXX' => array(
                        '2026' => 763,
                        'resource_category_code' => 'O&M $',
                        '2027' => 863,
                        '2028' => 963,
                        '2029' => 1063,
                        '2030' => 1163
                    )
                ),
                'program_code' => 'OTHERAA',
                'eoc_code' => array(
                    '0' => 'OTHERAA.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            ),
            'OTHERDD_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERDD.XXX' => array(
                        '2026' => 825,
                        'resource_category_code' => 'O&M $',
                        '2027' => 900,
                        '2028' => 975,
                        '2029' => 1050,
                        '2030' => 1125
                    )
                ),
                'program_code' => 'OTHERDD',
                'eoc_code' => array(
                    '0' => 'OTHERDD.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            )
        );

        $override_table_session = json_encode([
            'budget_uncommitted' => [
                [
                    "2026" => "20000",
                    "2027" => "20000",
                    "2028" => "20000",
                    "2029" => "20000",
                    "2030" => "20000",
                    "FYDP" => 100000,
                    "TYPE" => 'Proposed Budget $K'
                ],
                [
                    "2026" => 487,
                    "2027" => 2889,
                    "2028" => 11872,
                    "2029" => 11665,
                    "2030" => 11458,
                    "FYDP" => 38371,
                    "TYPE" => 'Uncommitted $K'
                ]
            ],
            'coa_output' => [
                [
                    "2026" => "5028",
                    "2027" => "5058",
                    "2028" => "5088",
                    "2029" => "5118",
                    "2030" => "5148",
                    "EOC" => "5GBEE.XXX",
                    "FYDP" => 25440,
                    "Program" => "5GBEE",
                    "DT_RowId" => 0,
                    "CAP SPONSOR" => "MARSOC",
                    "POM SPONSOR" => "MARSOC",
                    "StoRM Score" => 57,
                    "RESOURCE CATEGORY" => "CATEGORY1"
                ],
                [
                    "2026" => 19513,
                    "2027" => 17111,
                    "2028" => 8128,
                    "2029" => 8335,
                    "2030" => 8542,
                    "EOC" => "",
                    "FYDP" => 61629,
                    "Program" => "",
                    "DT_RowId" => 5,
                    "CAP SPONSOR" => "",
                    "POM SPONSOR" => "",
                    "StoRM Score" => "",
                    "RESOURCE CATEGORY" => 'Committed Grand Total $K'
                ]
            ]
        ]);

        $override_table_metadata = json_encode([
            'metadata_field1' => 'metadata_value1',
            'metadata_field2' => 'metadata_value2'
        ]);
        $override_justification = json_encode([
            'justification' => []
        ]);
        $session_data_mock = [
            [
                'OVERRIDE_TABLE_SESSION' => $override_table_session,
                'OVERRIDE_TABLE_METADATA' => $override_table_metadata,
                'OVERRIDE_FORM_SESSION' => $override_justification
            ]
        ];

        MonkeyPatch::patchMethod(SOCOM_COA::class, ['get_saved_coa_score' => "13.5"]);

        MonkeyPatch::patchMethod(SOCOM_COA_model::class, ['get_fiscal_years' => [2026, 2027, 2028, 2029, 2030],
        'fetchOutputInfo' => json_encode($result, true), 'get_manual_override_data' => $session_data_mock,
        'get_manual_override_status' => ['STATE' => 'approved'], 'get_saved_coa_optimizer_input' => ['storm_flag' => 'weighted', 'option' => 3]]);


        MonkeyPatch::patchMethod(SOCOM_Users_model::class, ['get_user_info' => ['name' => 'name']]);
        
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/output', [
            'ids' => [1, 2, 3],
            'budget' => [100000, 100000, 100000, 100000, 100000],
            'coa_table_id' => 789
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_output_table_wo_session_data() {

        $result = array(
            'OTHERAA_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERAA.XXX' => array(
                        '2026' => 763,
                        'resource_category_code' => 'O&M $',
                        '2027' => 863,
                        '2028' => 963,
                        '2029' => 1063,
                        '2030' => 1163
                    )
                ),
                'program_code' => 'OTHERAA',
                'eoc_code' => array(
                    '0' => 'OTHERAA.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            ),
            'OTHERDD_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERDD.XXX' => array(
                        '2026' => 825,
                        'resource_category_code' => 'O&M $',
                        '2027' => 900,
                        '2028' => 975,
                        '2029' => 1050,
                        '2030' => 1125
                    )
                ),
                'program_code' => 'OTHERDD',
                'eoc_code' => array(
                    '0' => 'OTHERDD.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            )
        );
        $scores = [
            'OTHERAA_USASOC_USASOC' => [
                'total_storm_scores' => 57,
                'weighted_pom_score' => 1,
                'weighted_guidance_score' => 1
            ],
            'OTHERDD_USASOC_USASOC' => [
                'total_storm_scores' => 57,
                'weighted_pom_score' => 1,
                'weighted_guidance_score' => 1
            ]
        ];
        MonkeyPatch::patchMethod(SOCOM_COA::class, ['get_saved_coa_score' => $scores, 'shouldAddCOACell' => TRUE]);

        MonkeyPatch::patchMethod(SOCOM_COA_model::class, ['get_fiscal_years' => [2026, 2027, 2028, 2029, 2030],
        'fetchOutputInfo' => json_encode($result, true), 'get_manual_override_data' => [],
        'get_manual_override_status' => ['STATE' => 'approved'], 'get_saved_coa_optimizer_input' => ['storm_flag' => 'weighted', 'option' => 3]]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, ['get_user_info' => ['name' => 'name']]);
        
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/output', [
            'ids' => [1, 2, 3],
            'budget' => [100000, 100000, 100000, 100000, 100000],
            'coa_table_id' => 789
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_output_table_session() {
        $result = array(
            'OTHERAA_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERAA.XXX' => array(
                        '2026' => 763,
                        'resource_category_code' => 'O&M $',
                        '2027' => 863,
                        '2028' => 963,
                        '2029' => 1063,
                        '2030' => 1163
                    )
                ),
                'program_code' => 'OTHERAA',
                'eoc_code' => array(
                    '0' => 'OTHERAA.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            ),
            'OTHERDD_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERDD.XXX' => array(
                        '2026' => 825,
                        'resource_category_code' => 'O&M $',
                        '2027' => 900,
                        '2028' => 975,
                        '2029' => 1050,
                        '2030' => 1125
                    )
                ),
                'program_code' => 'OTHERDD',
                'eoc_code' => array(
                    '0' => 'OTHERDD.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            )
        );
        $scores = [
            'OTHERAA_USASOC_USASOC' => [
                'total_storm_scores' => 57,
                'weighted_pom_score' => 1,
                'weighted_guidance_score' => 1
            ],
            'OTHERDD_USASOC_USASOC' => [
                'total_storm_scores' => 57,
                'weighted_pom_score' => 1,
                'weighted_guidance_score' => 1
            ]
        ];
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'fetchOutputInfo' => $result,
                        'get_fiscal_years' => [2026, 2027, 2028, 2029, 2030],
                        'get_manual_override_data' => [[
                            'OVERRIDE_TABLE_SESSION' => json_encode([
                                'coa_output' => [[], []],
                                'budget_uncommitted' => [[], []],
                            ], true),
                            'OVERRIDE_TABLE_METADATA' => json_encode([
                                'coa_output' => [[], []],
                                'budget_uncommitted' => [[], []],
                            ], true)
                        ]],
                        'get_manual_override_status' => ['STATE' => 'approved'],
                        'get_saved_coa_optimizer_input' => ['storm_flag' => 'weighted', 'option' => 3],
                        'get_weighted_score' => $scores

                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;

                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_user_info' => ['name' => 'name']
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/output', [
            'ids' => [1, 2, 3],
            'budget' => [100000, 100000, 100000, 100000, 100000],
            'coa_table_id' => 789
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_output_table_session_noParam() {
        $actual = $this->request('POST', 'optimizer/scenario/1/simulation/table/output', []);
        $this->assertIsString($actual);
    }

    public function test_manual_override_save() {
        
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'manual_override_save' => ""
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        
        $actual = $this->request('POST', 'optimizer/scenario/1/manual_override_save', [
            'override_table' => 'override_table_metadata',
            'override_table_metadata' => 'override_table_metadata'
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_override_form() {
        
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'save_override_form' => ""
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        
        $actual = $this->request('POST', 'optimizer/scenario/1/save_override_form', [
            'override_form' => 'override_form'
        ]);
        $this->assertIsString($actual);
    }

    public function test_change_scenario_status() {
        
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'change_scenario_status' => ""
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );
        
        $actual = $this->request('POST', 'optimizer/scenario/1/change_scenario_status', [
            'status_value' => 'status_value'
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_display_banner() {        
        $actual = $this->request('POST', 'optimizer/get_display_banner', [
            'status_value' => 'status_value'
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_summary_data() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_dropdown_codes' => FALSE,
                        'get_saved_coa_optimizer_input' => ['option' => 1, 'storm_flag' => false],
                        'fetchOutputInfo' => json_encode([
                            'PROGRAM' => [
                                'resource_k' => [
                                    'CODE' => [
                                        2026 => 12
                                    ]
                                ]
                            ]
                        ], true),
                        'get_user_saved_coa' => [
                            'test' => 'test'
                        ],
                        'get_user_saved_coa_data' => [
                            'test' => 'test'
                        ],
                        'store_user_run' => TRUE
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/get_detailed_summary_data', ['id' => encrypted_string(1, 'encode'),
        'name' => 'test',
        'description' => 'test']);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_summary() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_dropdown_codes' => FALSE,
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/table/1/get_detailed_summary', ['id' => encrypted_string(1, 'encode'),
        'tableId' => encrypted_string(1, 'encode'),
        'name' => 'test',
        'description' => 'test']);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_comparisson() {

        $actual = $this->request('POST', 'optimizer/get_detailed_comparison', ['saved_coa_ids' => [1789],
        'titles' => ['No Privacy']]);
        $this->assertIsString($actual);
    }

    public function test_update_detailed_sumary_data_eoc_code() {
        $result = array(
            'OTHERAA_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERAA.XXX' => array(
                        '2026' => 763,
                        'resource_category_code' => 'O&M $',
                        '2027' => 863,
                        '2028' => 963,
                        '2029' => 1063,
                        '2030' => 1163
                    )
                ),
                'program_code' => 'OTHERAA',
                'eoc_code' => array(
                    '0' => 'OTHERAA.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            ),
            'OTHERDD_USASOC_USASOC' => array(
                'resource_k' => array(
                    'OTHERDD.XXX' => array(
                        '2026' => 825,
                        'resource_category_code' => 'O&M $',
                        '2027' => 900,
                        '2028' => 975,
                        '2029' => 1050,
                        '2030' => 1125
                    )
                ),
                'program_code' => 'OTHERDD',
                'eoc_code' => array(
                    '0' => 'OTHERDD.XXX'
                ),
                'pom_sponsor_code' => 'USASOC',
                'capability_sponsor_code' => 'USASOC',
                'resource_category_code' => array(
                    '0' => 'O&M $'
                )
            )
        );
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_dropdown_codes' => FALSE,
                        'fetchOutputInfo' => $result
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_summary_data/eoc_code/update', [
            
            'program_ids' => [
                'OTHERDD_USASOC_USASOC',
                'OTHERAA_USASOC_USASOC'
            ],
            'type' => 'eoc-code',
            'fy' => [
                2026,
                2027,
                2028,
                2029,
                2030,
            ],
            'cap_sponsor' => [
                'USASOC',
            ]
              
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_summary_data_eoc_code() {

        $fetchOutputInfoData = [
            [
                "ID" => "OTHERAA_USASOC_USASOC",
                "PROGRAM_CODE" => "OTHERAA",
                "EOC_CODE" => "OTHERAA.XXX",
                "POM_SPONSOR_CODE" => "USASOC",
                "RESOURCE_CATEGORY_CODE" => "O&M $",
                "CAPABILITY_SPONSOR_CODE" => "USASOC",
                "PROGRAM_GROUP" => "OTHER",
                "RESOURCE_K" => [
                    "2026" => 691,
                    "2027" => 791,
                    "2028" => 891,
                    "2029" => 991,
                    "2030" => 1091
                ]
            ],
            [
                "ID" => "OTHERDD_USASOC_USASOC",
                "PROGRAM_CODE" => "OTHERDD",
                "EOC_CODE" => "OTHERDD.XXX",
                "POM_SPONSOR_CODE" => "USASOC",
                "RESOURCE_CATEGORY_CODE" => "O&M $",
                "CAPABILITY_SPONSOR_CODE" => "USASOC",
                "PROGRAM_GROUP" => "OTHER",
                "RESOURCE_K" => [
                    "2026" => 746,
                    "2027" => 821,
                    "2028" => 896,
                    "2029" => 971,
                    "2030" => 1046
                ]
            ],
        ];

        $this->request->addCallable(
            function ($CI) use(&$fetchOutputInfoData) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_dropdown_codes' => FALSE,
                        'get_saved_coa_optimizer_input' => ['option' => 1, 'storm_flag' => false],
                        'fetchOutputInfo' => json_encode($fetchOutputInfoData, true),
                        'get_user_saved_coa' => [
                            'test' => 'test'
                        ],
                        'get_user_saved_coa_data' => [
                            'test' => 'test'
                        ],
                        'store_user_run' => TRUE
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/get_detailed_summary_data',
        [
            'id' => encrypted_string(1, 'encode'),
            'type' => 'eoc-code',
            'selected_ids' => [1, 2, 3],
            'unselected_ids' => [4, 5, 6]
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_summary_data_jca_alignment() {

        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "first_tier" => [
                        "8" => 25700.67,
                        "4" => 21513.53,
                        "1" => 21513.53,
                        "5" => 687.86,
                        "2" => 1375.72,
                        "3" => 687.86,
                        "6" => 687.86
                    ],
                    "second_tier" => [
                        "8.2" => 23263.17,
                        "4.1" => 20825.67,
                        "1.4" => 20825.67,
                        "5.1" => 687.86,
                        "2.4" => 687.86,
                        "2.5" => 687.86,
                        "1.1" => 687.86,
                        "3.1" => 687.86,
                        "4.3" => 687.86,
                        "6.2" => 687.86,
                        "8.1" => 2437.5
                    ],
                    "third_tier" => [
                        "8.2.2" => 23263.17,
                        "4.1.2" => 20825.67,
                        "1.4.0" => 20825.67,
                        "5.1.3" => 687.86,
                        "2.4.3" => 687.86,
                        "2.5.0" => 687.86,
                        "1.1.2" => 687.86,
                        "3.1.1" => 687.86,
                        "4.3.2" => 687.86,
                        "6.2.2" => 687.86,
                        "8.1.0" => 2437.5
                    ]
                ],
                "unselected_programs" => [
                    "first_tier" => [],
                    "second_tier" => [],
                    "third_tier" => []
                ]
            ]
        ];

        $description = [
            "1.0.0" => "FORCE SUPPORT",
            "1.1.0" => "FORCE SUPPORT, FORCE MANAGEMENT",
            "1.1.2" => "FORCE SUPPORT, FORCE MANAGEMENT, FORCE CONFIGURATION",
            "1.4.0" => "FORCE SUPPORT, HEALTH READINESS",
            "2.0.0" => "BATTLESPACE AWARENESS",
            "2.4.0" => "BATTLESPACE AWARENESS, ANALYSIS",
            "2.4.3" => "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)",
            "2.5.0" => "BATTLESPACE AWARENESS, BA DATA DISSEMINATION AND RELAY",
            "3.0.0" => "FORCE APPLICATION",
            "3.1.0" => "FORCE APPLICATION, MANEUVER",
            "3.1.1" => "FORCE APPLICATION, MANEUVER, MANEUVER TO ENGAGE (MTE)",
            "4.0.0" => "LOGISTICS",
            "4.1.0" => "LOGISTICS, DEPLOYMENT AND DISTRIBUTION",
            "4.1.2" => "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE",
            "4.3.0" => "LOGISTICS, MAINTAIN",
            "4.3.2" => "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
            "5.0.0" => "COMMAND AND CONTROL",
            "5.1.0" => "COMMAND AND CONTROL, ORGANIZE",
            "5.1.3" => "COMMAND AND CONTROL, ORGANIZE, FOSTER ORGANIZATIONAL COLLABORATION",
            "6.0.0" => "NET-CENTRIC",
            "6.2.0" => "NET-CENTRIC, ENTERPRISE SERVICES",
            "6.2.2" => "NET-CENTRIC, ENTERPRISE SERVICES, COMPUTING SERVICES",
            "8.0.0" => "BUILDING PARTNERSHIPS",
            "8.1.0" => "BUILDING PARTNERSHIPS, COMMUNICATE",
            "8.2.0" => "BUILDING PARTNERSHIPS, SHAPE",
            "8.2.2" => "BUILDING PARTNERSHIPS, SHAPE, PROVIDE AID TO FOREIGN PARTNERS AND INSTITUTIONS"
        ];

        $ids = [
            "1.2.0",
            "1.3.0",
            "2.1.0",
            "2.2.0",
            "2.3.0",
            "3.2.0",
            "4.2.0",
            "4.4.0",
            "4.5.0",
            "4.6.0",
            "4.7.0",
            "5.2.0",
            "5.3.0",
            "5.4.0",
            "5.5.0",
            "5.6.0",
            "6.1.0",
            "6.3.0",
            "6.4.0",
            "7.1.0",
            "7.2.0",
            "9.1.0",
            "9.2.0",
            "9.4.0",
            "9.5.0"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_jca_alignment_data' => $data,
                        'get_jca_alignment_description' => $description,
                        'get_jca_alignment_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/get_detailed_summary_data',
        [
            'id' => encrypted_string(1, 'encode'),
            'type' => 'jca-alignment',
            'selected_ids' => [1, 2, 3],
            'unselected_ids' => [4, 5, 6]
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_summary_data_capability_gaps() {

        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "first_tier" => [
                        "32" => 20825.67,
                        "20" => 20825.67,
                        "39" => 20825.67,
                        "35" => 4815
                    ],
                    "second_tier" => [
                        "32.116" => 20825.67,
                        "20.20" => 20825.67,
                        "39.39" => 20825.67,
                        "35.35" => 4815
                    ],
                    "third_tier" => []
                ],
                "unselected_programs" => [
                    "first_tier" => [],
                    "second_tier" => [],
                    "third_tier" => []
                ]
            ]
        ];

        $description = [
            "20" => [
                "CGA_NAME" => "2024-AFSOC-07",
                "GAP_DESCRIPTION" => "We lack the ability to audibly gasp in suspense.",
                "GROUP_ID" => "20",
                "GROUP_DESCRIPTION" => "We lack the ability to audibly gasp in suspense."
            ],
            "35" => [
                "CGA_NAME" => "2024-MARSOC-08",
                "GAP_DESCRIPTION" => "We lack the ability to qualify for the Olympics.",
                "GROUP_ID" => "35",
                "GROUP_DESCRIPTION" => "We lack the ability to qualify for the Olympics."
            ],
            "39" => [
                "CGA_NAME" => "2024-MARSOC-03",
                "GAP_DESCRIPTION" => "We lack the ability to own more than one monitor at once.",
                "GROUP_ID" => "39",
                "GROUP_DESCRIPTION" => "We lack the ability to own more than one monitor at once."
            ],
            "116" => [
                "CGA_NAME" => "2024-AT&L-10",
                "GAP_DESCRIPTION" => "We lack the ability to grow red apples.",
                "GROUP_ID" => "32",
                "GROUP_DESCRIPTION" => "We lack the ability to grow apples."
            ]
        ];

        $ids = [
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31",
            "33",
            "34",
            "36",
            "37",
            "38",
            "40"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_capability_gaps_data' => $data,
                        'get_capability_gaps_description' => $description,
                        'get_capability_gaps_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/get_detailed_summary_data',
        [
            'id' => encrypted_string(1, 'encode'),
            'type' => 'capability-gaps',
            'selected_ids' => [1, 2, 3],
            'unselected_ids' => [4, 5, 6]
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_summary_data_kop_ksp() {

        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "third_tier" => [
                        "1.1.1" => 24990.8,
                        "1.1.2" => 12495.4,
                        "1.2.1" => 12495.4,
                        "2.2.1" => 12495.4,
                        "2.1.2" => 4875
                    ],
                    "fourth_tier" => [
                        "1.1.1.0" => 12495.4,
                        "1.1.1.2" => 12495.4,
                        "1.1.2.3" => 12495.4,
                        "1.2.1.2" => 12495.4,
                        "2.2.1.2" => 12495.4,
                        "2.1.2.2" => 4875
                    ]
                ],
                "unselected_programs" => [
                    "third_tier" => [],
                    "fourth_tier" => []
                ]
            ]
        ];

        $description = [
            "1.1.1.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.1.1.1",
                    "1.1.1.2",
                    "1.1.1.3"
                ],
                "DESCRIPTION" => "Shoes are important to wear"
            ],
            "1.1.2.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.1.2.1",
                    "1.1.2.2",
                    "1.1.2.3",
                    "1.1.2.4"
                ],
                "DESCRIPTION" => "Shirts are important to wear"
            ],
            "1.2.1.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.2.1.1",
                    "1.2.1.2",
                    "1.2.1.3",
                    "1.2.1.4"
                ],
                "DESCRIPTION" => "People need to eat food during regular meal times"
            ],
            "2.1.2.0" => [
                "TYPE" => "KSP",
                "CHILDREN" => [
                    "2.1.2.1",
                    "2.1.2.2"
                ],
                "DESCRIPTION" => "Most plants grow outside"
            ],
            "2.2.1.0" => [
                "TYPE" => "KSP",
                "CHILDREN" => [
                    "2.2.1.1",
                    "2.2.1.2"
                ],
                "DESCRIPTION" => "Pancakes are an important breakfast food"
            ]
        ];

        $ids = [
            "1.1.3.0",
            "1.2.2.0",
            "1.3.1.0",
            "2.1.1.0",
            "2.1.3.0",
            "2.1.4.0",
            "2.2.2.0",
            "2.3.1.0"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_kop_ksp_data' => $data,
                        'get_kop_ksp_description' => $description,
                        'get_kop_ksp_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/scenario/1/get_detailed_summary_data',
        [
            'id' => encrypted_string(1, 'encode'),
            'type' => 'kop-ksp',
            'selected_ids' => [1, 2, 3],
            'unselected_ids' => [4, 5, 6]
        ]);
        $this->assertIsString($actual);
    }

    public function test_update_detailed_sumary_data_jca_alignment() {
        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "first_tier" => [
                        "8" => 25700.67,
                        "4" => 21513.53,
                        "1" => 21513.53,
                        "5" => 687.86,
                        "2" => 1375.72,
                        "3" => 687.86,
                        "6" => 687.86
                    ],
                    "second_tier" => [
                        "8.2" => 23263.17,
                        "4.1" => 20825.67,
                        "1.4" => 20825.67,
                        "5.1" => 687.86,
                        "2.4" => 687.86,
                        "2.5" => 687.86,
                        "1.1" => 687.86,
                        "3.1" => 687.86,
                        "4.3" => 687.86,
                        "6.2" => 687.86,
                        "8.1" => 2437.5
                    ],
                    "third_tier" => [
                        "8.2.2" => 23263.17,
                        "4.1.2" => 20825.67,
                        "1.4.0" => 20825.67,
                        "5.1.3" => 687.86,
                        "2.4.3" => 687.86,
                        "2.5.0" => 687.86,
                        "1.1.2" => 687.86,
                        "3.1.1" => 687.86,
                        "4.3.2" => 687.86,
                        "6.2.2" => 687.86,
                        "8.1.0" => 2437.5
                    ]
                ],
                "unselected_programs" => [
                    "first_tier" => [],
                    "second_tier" => [],
                    "third_tier" => []
                ]
            ]
        ];

        $description = [
            "1.0.0" => "FORCE SUPPORT",
            "1.1.0" => "FORCE SUPPORT, FORCE MANAGEMENT",
            "1.1.2" => "FORCE SUPPORT, FORCE MANAGEMENT, FORCE CONFIGURATION",
            "1.4.0" => "FORCE SUPPORT, HEALTH READINESS",
            "2.0.0" => "BATTLESPACE AWARENESS",
            "2.4.0" => "BATTLESPACE AWARENESS, ANALYSIS",
            "2.4.3" => "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)",
            "2.5.0" => "BATTLESPACE AWARENESS, BA DATA DISSEMINATION AND RELAY",
            "3.0.0" => "FORCE APPLICATION",
            "3.1.0" => "FORCE APPLICATION, MANEUVER",
            "3.1.1" => "FORCE APPLICATION, MANEUVER, MANEUVER TO ENGAGE (MTE)",
            "4.0.0" => "LOGISTICS",
            "4.1.0" => "LOGISTICS, DEPLOYMENT AND DISTRIBUTION",
            "4.1.2" => "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE",
            "4.3.0" => "LOGISTICS, MAINTAIN",
            "4.3.2" => "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
            "5.0.0" => "COMMAND AND CONTROL",
            "5.1.0" => "COMMAND AND CONTROL, ORGANIZE",
            "5.1.3" => "COMMAND AND CONTROL, ORGANIZE, FOSTER ORGANIZATIONAL COLLABORATION",
            "6.0.0" => "NET-CENTRIC",
            "6.2.0" => "NET-CENTRIC, ENTERPRISE SERVICES",
            "6.2.2" => "NET-CENTRIC, ENTERPRISE SERVICES, COMPUTING SERVICES",
            "8.0.0" => "BUILDING PARTNERSHIPS",
            "8.1.0" => "BUILDING PARTNERSHIPS, COMMUNICATE",
            "8.2.0" => "BUILDING PARTNERSHIPS, SHAPE",
            "8.2.2" => "BUILDING PARTNERSHIPS, SHAPE, PROVIDE AID TO FOREIGN PARTNERS AND INSTITUTIONS"
        ];

        $ids = [
            "1.2.0",
            "1.3.0",
            "2.1.0",
            "2.2.0",
            "2.3.0",
            "3.2.0",
            "4.2.0",
            "4.4.0",
            "4.5.0",
            "4.6.0",
            "4.7.0",
            "5.2.0",
            "5.3.0",
            "5.4.0",
            "5.5.0",
            "5.6.0",
            "6.1.0",
            "6.3.0",
            "6.4.0",
            "7.1.0",
            "7.2.0",
            "9.1.0",
            "9.2.0",
            "9.4.0",
            "9.5.0"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_jca_alignment_data' => $data,
                        'get_jca_alignment_description' => $description,
                        'get_jca_alignment_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_summary_data/jca_alignment/update', [
            'details_checkbox' => 'true',
            'saved_coa_id' => 0,
            'lvl_1' => ["1"],
            'lvl_2' => ["1"],
            'lvl_3' => ["1"]
        ]);
        $this->assertIsString($actual);
    }

    public function test_update_detailed_sumary_data_capability_gaps() {
        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "first_tier" => [
                        "32" => 20825.67,
                        "20" => 20825.67,
                        "39" => 20825.67,
                        "35" => 4815
                    ],
                    "second_tier" => [
                        "32.116" => 20825.67,
                        "20.20" => 20825.67,
                        "39.39" => 20825.67,
                        "35.35" => 4815
                    ],
                    "third_tier" => []
                ],
                "unselected_programs" => [
                    "first_tier" => [],
                    "second_tier" => [],
                    "third_tier" => []
                ]
            ]
        ];

        $description = [
            "20" => [
                "CGA_NAME" => "2024-AFSOC-07",
                "GAP_DESCRIPTION" => "We lack the ability to audibly gasp in suspense.",
                "GROUP_ID" => "20",
                "GROUP_DESCRIPTION" => "We lack the ability to audibly gasp in suspense."
            ],
            "35" => [
                "CGA_NAME" => "2024-MARSOC-08",
                "GAP_DESCRIPTION" => "We lack the ability to qualify for the Olympics.",
                "GROUP_ID" => "35",
                "GROUP_DESCRIPTION" => "We lack the ability to qualify for the Olympics."
            ],
            "39" => [
                "CGA_NAME" => "2024-MARSOC-03",
                "GAP_DESCRIPTION" => "We lack the ability to own more than one monitor at once.",
                "GROUP_ID" => "39",
                "GROUP_DESCRIPTION" => "We lack the ability to own more than one monitor at once."
            ],
            "116" => [
                "CGA_NAME" => "2024-AT&L-10",
                "GAP_DESCRIPTION" => "We lack the ability to grow red apples.",
                "GROUP_ID" => "32",
                "GROUP_DESCRIPTION" => "We lack the ability to grow apples."
            ]
        ];

        $ids = [
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31",
            "33",
            "34",
            "36",
            "37",
            "38",
            "40"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_capability_gaps_data' => $data,
                        'get_capability_gaps_description' => $description,
                        'get_capability_gaps_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_summary_data/capability_gaps/update', [
            'details_checkbox' => 'true',
            'saved_coa_id' => 0,
            'lvl_1' => ["1"],
            'lvl_2' => ["1"],
            'lvl_3' => ["1"]
        
        ]);
        $this->assertIsString($actual);
    }

    public function test_update_detailed_sumary_data_kop_ksp() {
        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "third_tier" => [
                        "1.1.1" => 24990.8,
                        "1.1.2" => 12495.4,
                        "1.2.1" => 12495.4,
                        "2.2.1" => 12495.4,
                        "2.1.2" => 4875
                    ],
                    "fourth_tier" => [
                        "1.1.1.0" => 12495.4,
                        "1.1.1.2" => 12495.4,
                        "1.1.2.3" => 12495.4,
                        "1.2.1.2" => 12495.4,
                        "2.2.1.2" => 12495.4,
                        "2.1.2.2" => 4875
                    ]
                ],
                "unselected_programs" => [
                    "third_tier" => [],
                    "fourth_tier" => []
                ]
            ]
        ];

        $description = [
            "1.1.1.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.1.1.1",
                    "1.1.1.2",
                    "1.1.1.3"
                ],
                "DESCRIPTION" => "Shoes are important to wear"
            ],
            "1.1.2.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.1.2.1",
                    "1.1.2.2",
                    "1.1.2.3",
                    "1.1.2.4"
                ],
                "DESCRIPTION" => "Shirts are important to wear"
            ],
            "1.2.1.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.2.1.1",
                    "1.2.1.2",
                    "1.2.1.3",
                    "1.2.1.4"
                ],
                "DESCRIPTION" => "People need to eat food during regular meal times"
            ],
            "2.1.2.0" => [
                "TYPE" => "KSP",
                "CHILDREN" => [
                    "2.1.2.1",
                    "2.1.2.2"
                ],
                "DESCRIPTION" => "Most plants grow outside"
            ],
            "2.2.1.0" => [
                "TYPE" => "KSP",
                "CHILDREN" => [
                    "2.2.1.1",
                    "2.2.1.2"
                ],
                "DESCRIPTION" => "Pancakes are an important breakfast food"
            ]
        ];

        $ids = [
            "1.1.3.0",
            "1.2.2.0",
            "1.3.1.0",
            "2.1.1.0",
            "2.1.3.0",
            "2.1.4.0",
            "2.2.2.0",
            "2.3.1.0"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_kop_ksp_data' => $data,
                        'get_kop_ksp_description' => $description,
                        'get_kop_ksp_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_summary_data/kop_ksp/update', [
            'details_checkbox' => 'true',
            'saved_coa_id' => 0,
            'lvl_1' => ["1.1.3"],
            'lvl_2' => ["0"],
            'lvl_3' => ["1"]
        
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_comparison_data_eoc_code() {
        $fetchOutputInfoData = [
            [
                "ID" => "OTHERAA_USASOC_USASOC",
                "PROGRAM_CODE" => "OTHERAA",
                "EOC_CODE" => "OTHERAA.XXX",
                "POM_SPONSOR_CODE" => "USASOC",
                "RESOURCE_CATEGORY_CODE" => "O&M $",
                "CAPABILITY_SPONSOR_CODE" => "USASOC",
                "PROGRAM_GROUP" => "OTHER",
                "RESOURCE_K" => [
                    "2026" => 691,
                    "2027" => 791,
                    "2028" => 891,
                    "2029" => 991,
                    "2030" => 1091
                ]
            ],
            [
                "ID" => "OTHERDD_USASOC_USASOC",
                "PROGRAM_CODE" => "OTHERDD",
                "EOC_CODE" => "OTHERDD.XXX",
                "POM_SPONSOR_CODE" => "USASOC",
                "RESOURCE_CATEGORY_CODE" => "O&M $",
                "CAPABILITY_SPONSOR_CODE" => "USASOC",
                "PROGRAM_GROUP" => "OTHER",
                "RESOURCE_K" => [
                    "2026" => 746,
                    "2027" => 821,
                    "2028" => 896,
                    "2029" => 971,
                    "2030" => 1046
                ]
            ],
        ];

        $this->request->addCallable(
            function ($CI) use(&$fetchOutputInfoData) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_dropdown_codes' => FALSE,
                        'get_saved_coa_optimizer_input' => ['option' => 1, 'storm_flag' => false],
                        'fetchOutputInfo' => json_encode($fetchOutputInfoData, true),
                        'get_user_saved_coa' => [
                            'test' => 'test'
                        ],
                        'get_user_saved_coa_data' => [
                            'test' => 'test'
                        ],
                        'store_user_run' => TRUE
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_comparison_data/eoc_code', [
            'selected_program_ids' =>[1789],
            'program_codes' => intval(encrypted_string('1', 'encode')),
            'eoc_codes' => 'test',
            'type' => 'eoc_',
            'program_id' => 'PROGRAM',
            'selected_program_ids' => ['PROGRAM', 'PROGRAM2']
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_comparison_data_jca_alignment() {
        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "first_tier" => [
                        "8" => 25700.67,
                        "4" => 21513.53,
                        "1" => 21513.53,
                        "5" => 687.86,
                        "2" => 1375.72,
                        "3" => 687.86,
                        "6" => 687.86
                    ],
                    "second_tier" => [
                        "8.2" => 23263.17,
                        "4.1" => 20825.67,
                        "1.4" => 20825.67,
                        "5.1" => 687.86,
                        "2.4" => 687.86,
                        "2.5" => 687.86,
                        "1.1" => 687.86,
                        "3.1" => 687.86,
                        "4.3" => 687.86,
                        "6.2" => 687.86,
                        "8.1" => 2437.5
                    ],
                    "third_tier" => [
                        "8.2.2" => 23263.17,
                        "4.1.2" => 20825.67,
                        "1.4.0" => 20825.67,
                        "5.1.3" => 687.86,
                        "2.4.3" => 687.86,
                        "2.5.0" => 687.86,
                        "1.1.2" => 687.86,
                        "3.1.1" => 687.86,
                        "4.3.2" => 687.86,
                        "6.2.2" => 687.86,
                        "8.1.0" => 2437.5
                    ]
                ],
                "unselected_programs" => [
                    "first_tier" => [],
                    "second_tier" => [],
                    "third_tier" => []
                ]
            ]
        ];

        $description = [
            "1.0.0" => "FORCE SUPPORT",
            "1.1.0" => "FORCE SUPPORT, FORCE MANAGEMENT",
            "1.1.2" => "FORCE SUPPORT, FORCE MANAGEMENT, FORCE CONFIGURATION",
            "1.4.0" => "FORCE SUPPORT, HEALTH READINESS",
            "2.0.0" => "BATTLESPACE AWARENESS",
            "2.4.0" => "BATTLESPACE AWARENESS, ANALYSIS",
            "2.4.3" => "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)",
            "2.5.0" => "BATTLESPACE AWARENESS, BA DATA DISSEMINATION AND RELAY",
            "3.0.0" => "FORCE APPLICATION",
            "3.1.0" => "FORCE APPLICATION, MANEUVER",
            "3.1.1" => "FORCE APPLICATION, MANEUVER, MANEUVER TO ENGAGE (MTE)",
            "4.0.0" => "LOGISTICS",
            "4.1.0" => "LOGISTICS, DEPLOYMENT AND DISTRIBUTION",
            "4.1.2" => "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE",
            "4.3.0" => "LOGISTICS, MAINTAIN",
            "4.3.2" => "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
            "5.0.0" => "COMMAND AND CONTROL",
            "5.1.0" => "COMMAND AND CONTROL, ORGANIZE",
            "5.1.3" => "COMMAND AND CONTROL, ORGANIZE, FOSTER ORGANIZATIONAL COLLABORATION",
            "6.0.0" => "NET-CENTRIC",
            "6.2.0" => "NET-CENTRIC, ENTERPRISE SERVICES",
            "6.2.2" => "NET-CENTRIC, ENTERPRISE SERVICES, COMPUTING SERVICES",
            "8.0.0" => "BUILDING PARTNERSHIPS",
            "8.1.0" => "BUILDING PARTNERSHIPS, COMMUNICATE",
            "8.2.0" => "BUILDING PARTNERSHIPS, SHAPE",
            "8.2.2" => "BUILDING PARTNERSHIPS, SHAPE, PROVIDE AID TO FOREIGN PARTNERS AND INSTITUTIONS"
        ];

        $ids = [
            "1.2.0",
            "1.3.0",
            "2.1.0",
            "2.2.0",
            "2.3.0",
            "3.2.0",
            "4.2.0",
            "4.4.0",
            "4.5.0",
            "4.6.0",
            "4.7.0",
            "5.2.0",
            "5.3.0",
            "5.4.0",
            "5.5.0",
            "5.6.0",
            "6.1.0",
            "6.3.0",
            "6.4.0",
            "7.1.0",
            "7.2.0",
            "9.1.0",
            "9.2.0",
            "9.4.0",
            "9.5.0"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_jca_alignment_data' => $data,
                        'get_jca_alignment_description' => $description,
                        'get_jca_alignment_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_comparison_data/jca_alignment', [
            'saved_coa_ids' =>[1789, 1234]
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_comparison_data_kop_ksp() {
        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "third_tier" => [
                        "1.1.1" => 24990.8,
                        "1.1.2" => 12495.4,
                        "1.2.1" => 12495.4,
                        "2.2.1" => 12495.4,
                        "2.1.2" => 4875
                    ],
                    "fourth_tier" => [
                        "1.1.1.0" => 12495.4,
                        "1.1.1.2" => 12495.4,
                        "1.1.2.3" => 12495.4,
                        "1.2.1.2" => 12495.4,
                        "2.2.1.2" => 12495.4,
                        "2.1.2.2" => 4875
                    ]
                ],
                "unselected_programs" => [
                    "third_tier" => [],
                    "fourth_tier" => []
                ]
            ]
        ];

        $description = [
            "1.1.1.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.1.1.1",
                    "1.1.1.2",
                    "1.1.1.3"
                ],
                "DESCRIPTION" => "Shoes are important to wear"
            ],
            "1.1.2.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.1.2.1",
                    "1.1.2.2",
                    "1.1.2.3",
                    "1.1.2.4"
                ],
                "DESCRIPTION" => "Shirts are important to wear"
            ],
            "1.2.1.0" => [
                "TYPE" => "KOP",
                "CHILDREN" => [
                    "1.2.1.1",
                    "1.2.1.2",
                    "1.2.1.3",
                    "1.2.1.4"
                ],
                "DESCRIPTION" => "People need to eat food during regular meal times"
            ],
            "2.1.2.0" => [
                "TYPE" => "KSP",
                "CHILDREN" => [
                    "2.1.2.1",
                    "2.1.2.2"
                ],
                "DESCRIPTION" => "Most plants grow outside"
            ],
            "2.2.1.0" => [
                "TYPE" => "KSP",
                "CHILDREN" => [
                    "2.2.1.1",
                    "2.2.1.2"
                ],
                "DESCRIPTION" => "Pancakes are an important breakfast food"
            ]
        ];

        $ids = [
            "1.1.3.0",
            "1.2.2.0",
            "1.3.1.0",
            "2.1.1.0",
            "2.1.3.0",
            "2.1.4.0",
            "2.2.2.0",
            "2.3.1.0"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_kop_ksp_data' => $data,
                        'get_kop_ksp_description' => $description,
                        'get_kop_ksp_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_comparison_data/kop_ksp', [
            'saved_coa_ids' =>[1789, 1234]
        ]);
        $this->assertIsString($actual);
    }

    public function test_get_detailed_comparison_data_capability_gaps() {
        $data = [
            "absolute_alignment" => [
                "selected_programs" => [
                    "first_tier" => [
                        "32" => 20825.67,
                        "20" => 20825.67,
                        "39" => 20825.67,
                        "35" => 4815
                    ],
                    "second_tier" => [
                        "32.116" => 20825.67,
                        "20.20" => 20825.67,
                        "39.39" => 20825.67,
                        "35.35" => 4815
                    ],
                    "third_tier" => []
                ],
                "unselected_programs" => [
                    "first_tier" => [],
                    "second_tier" => [],
                    "third_tier" => []
                ]
            ]
        ];

        $description = [
            "20" => [
                "CGA_NAME" => "2024-AFSOC-07",
                "GAP_DESCRIPTION" => "We lack the ability to audibly gasp in suspense.",
                "GROUP_ID" => "20",
                "GROUP_DESCRIPTION" => "We lack the ability to audibly gasp in suspense."
            ],
            "35" => [
                "CGA_NAME" => "2024-MARSOC-08",
                "GAP_DESCRIPTION" => "We lack the ability to qualify for the Olympics.",
                "GROUP_ID" => "35",
                "GROUP_DESCRIPTION" => "We lack the ability to qualify for the Olympics."
            ],
            "39" => [
                "CGA_NAME" => "2024-MARSOC-03",
                "GAP_DESCRIPTION" => "We lack the ability to own more than one monitor at once.",
                "GROUP_ID" => "39",
                "GROUP_DESCRIPTION" => "We lack the ability to own more than one monitor at once."
            ],
            "116" => [
                "CGA_NAME" => "2024-AT&L-10",
                "GAP_DESCRIPTION" => "We lack the ability to grow red apples.",
                "GROUP_ID" => "32",
                "GROUP_DESCRIPTION" => "We lack the ability to grow apples."
            ]
        ];

        $ids = [
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31",
            "33",
            "34",
            "36",
            "37",
            "38",
            "40"
        ];

        $this->request->addCallable(
            function ($CI) use($data, $description, $ids) {
                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'get_capability_gaps_data' => $data,
                        'get_capability_gaps_description' => $description,
                        'get_capability_gaps_noncovered' => $ids
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;
            }
        );

        $actual = $this->request('POST', 'optimizer/get_detailed_comparison_data/capability_gaps', [
            'saved_coa_ids' =>[1789, 1234]
        ]);
        $this->assertIsString($actual);
    }
}