​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Score_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->socom_model = new SOCOM_model();
        $this->socom_score_model = new SOCOM_Score_model();
    }

    public function test_get_resultNotFalse() {
        $this->request->addCallable(
            function ($CI) {
                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'get_score' => [
                            'NAME' => 'test'
                        ]
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/score/get');
        $this->assertNotNull($actual);
    }

    public function test_get_resultFalse() {
        $this->request->addCallable(
            function ($CI) {
                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'get_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/score/get');
        $this->assertNotNull($actual);
    }

    public function test_edit_msgEmpty_resultTrue() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'a'
                            ],
                            [
                                'CRITERIA' => 'b'
                            ],
                            [
                                'CRITERIA' => 'c'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => TRUE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/edit', [
            'score_id' => 1,
            'score_data' => [
                'a' => 40,
                'b' => 90,
                'c' => 60
            ],
            'data' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_edit_msgEmpty_resultNotTrue() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'a'
                            ],
                            [
                                'CRITERIA' => 'b'
                            ],
                            [
                                'CRITERIA' => 'c'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/edit', [
            'score_id' => 1,
            'score_data' => [
                'a' => 40,
                'b' => 90,
                'c' => 60
            ],
            'data' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_edit_msgEmpty_resultNotTrue_validate_post_form_error() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'a'
                            ],
                            [
                                'CRITERIA' => 'b'
                            ],
                            [
                                'CRITERIA' => 'c'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );

        MonkeyPatch::patchFunction('form_error', 'error','SOCOM_Score::_validate_post');
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/edit', [
            'score_id' => 1,
            'score_data' => [
                'a' => 40,
                'b' => 90,
                'c' => 60,
            ],
            'data' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_edit_msgEmpty_resultNotTrue_validate_post_invalid_value() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'a'
                            ],
                            [
                                'CRITERIA' => 'b'
                            ],
                            [
                                'CRITERIA' => 'c'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );

        MonkeyPatch::patchFunction('form_error', 'error','SOCOM_Score::_validate_post');

        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/edit', [
            'score_id' => 1,
            'score_data' => [
                'a' => 100,
                'b' => 90,
                'c' => 0,
            ],
            'data' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_edit_msgNotEmpty() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => []
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/edit', [
            'score_id' => 1,
            'score_name' => 'Score',
            'program_id' => "Test",
            'score_data' => [
                'ACQUISITION' => 1
            ],
            'score_description' => "test"
        ]);
        $this->assertNotNull($actual);
    }

    public function test_edit_msgNotEmpty_noScoreID() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => []
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/edit', [
            'score_name' => 'Score',
            'program_id' => "Test",
            'score_data' => [
                'ACQUISITION' => 1
            ]
        ]);
        $this->assertNotNull($actual);
    }

    public function test_create_msgEmpty_resultNotFalse() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => []
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => true
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/create', [
            'score_id' => 1,
            'score_name' => 'Score',
            'program_id' => "Test",
            'score_data' => [
                'ACQUISITION' => 1
            ],
            'score_description' => "test"
        ]);
        $this->assertNotNull($actual);
    }

    public function test_create_msgEmpty_resultFalse() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'a'
                            ],
                            [
                                'CRITERIA' => 'b'
                            ],
                            [
                                'CRITERIA' => 'c'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/create', [
            'score_id' => 1,
            'score_data' => [
                'a' => 50,
                'b' => 90,
                'c' => 40,
            ],
            'data' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_create_msgEmpty_resultTrue() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'a'
                            ],
                            [
                                'CRITERIA' => 'b'
                            ],
                            [
                                'CRITERIA' => 'c'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => TRUE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/create', [
            'score_id' => 1,
            'score_data' => [
                'a' => 50,
                'b' => 90,
                'c' => 40,
            ],
            'data' => []
        ]);
        $this->assertNotNull($actual);
    }

    public function test_create_msgNotEmpty() {
        $this->request->addCallable(
            function ($CI) {
                $socom_model = $this->getDouble(
                    'socom_model', [
                        'get_option_criteria_names' => [
                            [
                                'CRITERIA' => 'test'
                            ]
                        ]
                    ]
                );
                $CI->socom_model = $socom_model;

                $socom_score_model = $this->getDouble(
                    'socom_score_model', [
                        'save_score' => FALSE
                    ]
                );
                $CI->socom_score_model = $socom_score_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/program/score/create', [
            'score_name' => 'Score',
            'program_id' => "Test",
            'score_data' => [
                'ACQUISITION' => 1
            ],
            'score_description' => "test"
        ]);
        $this->assertNotNull($actual);
    }
}