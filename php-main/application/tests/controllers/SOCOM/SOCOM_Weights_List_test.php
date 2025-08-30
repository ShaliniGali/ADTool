​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Weights_List_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_model = new SOCOM_model();
        $this->SOCOM_Weights_List_model = new SOCOM_Weights_List_model();
    }

    public function test_index() {
        $actual = $this->request('GET', 'socom/resource_constrained_coa/weights/list/index');
        $this->assertNotNull($actual);
    }

    public function test_get_data_nonEmptyResponseData() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_List_model = $this->getDouble(
                    'SOCOM_Weights_List_model', [
                        'get_criteria_weights_table' => [
                            'test' => 'value'
                        ]
                    ]
                );
                $CI->SOCOM_Weights_List_model = $SOCOM_Weights_List_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/criteria/weights/list/data');
        $this->assertNotNull($actual);
    }

    public function test_get_data_emptyResponseData() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_List_model = $this->getDouble(
                    'SOCOM_Weights_List_model', [
                        'get_criteria_weights_table' => NULL
                    ]
                );
                $CI->SOCOM_Weights_List_model = $SOCOM_Weights_List_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/criteria/weights/list/data');
        $this->assertNotNull($actual);
    }

    public function test_get_weight() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_List_model = $this->getDouble(
                    'SOCOM_Weights_List_model', [
                        'get_saved_weight_data' => [
                            'guidance' => [],
                            'pom' => []
                        ],
                        'get_data' => [
                            'data' => [
                                [
                                    'NAME' => 'test'
                                ]
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_Weights_List_model = $SOCOM_Weights_List_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/criteria/weights/get/1');
        $this->assertNotNull($actual);
    }

    public function test_save_weights_invalidPost() {
        $actual = $this->request('POST', 'socom/resource_constrained_coa/weights/list/save');
        $this->assertNotNull($actual);
    }

    public function test_save_weights_validPost_guidancePomCheckWeightsBothTrue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_List_model = $this->getDouble(
                    'SOCOM_Weights_List_model', [
                        'save_weight_score_data' => true
                    ]
                );
                $CI->SOCOM_Weights_List_model = $SOCOM_Weights_List_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/weights/list/save', [
            'weight_data' => json_encode([
                'guidance' => [
                    [
                        'weight' => 1.0
                    ]
                ],
                'pom' => [
                    [
                        'weight' => 1.0
                    ]
                ],
            ]),
            'weight_id' => 0
        ]);
        $this->assertNotNull($actual);
    }

    public function test_save_weights_validPost_guidancePomCheckWeightsEitherFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_List_model = $this->getDouble(
                    'SOCOM_Weights_List_model', [
                        'get_saved_weight_data' => [
                            'guidance' => [],
                            'pom' => []
                        ],
                        'get_data' => [
                            'data' => [
                                [
                                    'NAME' => 'test'
                                ]
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_Weights_List_model = $SOCOM_Weights_List_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/weights/list/save', [
            'weight_data' => json_encode(array(
                "guidance" => array(
                    array(
                        "criteria" => "ACQUISITION_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "COST_PRACTICALITY",
                        "weight" => 0.05
                    ),
                    array(
                        "criteria" => "COST_PROFILE_FEASIBILITY",
                        "weight" => 0.05
                    ),
                    array(
                        "criteria" => "DESIGN_ALIGNMENT",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "FOUNDATIONAL",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "MANPOWER_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "POLITICAL_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "READINESS",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "RISK",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "SECURITY_COOPERATION_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "STRATEGIC_ALIGNMENT",
                        "weight" => 0.1
                    )
                ),
                "pom" => array(
                    array(
                        "criteria" => "ACQUISITION_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "COST_PRACTICALITY",
                        "weight" => 0.05
                    ),
                    array(
                        "criteria" => "COST_PROFILE_FEASIBILITY",
                        "weight" => 0.05
                    ),
                    array(
                        "criteria" => "DESIGN_ALIGNMENT",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "FOUNDATIONAL",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "MANPOWER_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "POLITICAL_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "READINESS",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "RISK",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "SECURITY_COOPERATION_FEASIBILITY",
                        "weight" => 0.1
                    ),
                    array(
                        "criteria" => "STRATEGIC_ALIGNMENT",
                        "weight" => 0.1
                    )
                )
            )),
            'weight_id' => 0
        ]);
        $this->assertNotNull($actual);
    }
}