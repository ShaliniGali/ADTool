​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Program_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_model = new SOCOM_model();
        $this->SOCOM_Weights_model = new SOCOM_Weights_model();
        $this->SOCOM_Weights_List_model = new SOCOM_Weights_List_model();
    }

    public function test_index() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_option_criteria_names' => []
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;

                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'get_user_weights' => []
                    ]
                );
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/list');
        $this->assertNotNull($actual);
    }

    public function test_get_program_resultNotFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_program' => TRUE
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/list/get/scored');
        $this->assertNotNull($actual);
    }

    public function test_get_program_resultFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_program' => FALSE
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/list/get');
        $this->assertNotNull($actual);
    }

    public function test_get_weighted_table_resultNotFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_weighted_table' => [
                            [
                                'SESSION' => json_encode([
                                    'test' => [1, 2, 3]
                                ])
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/weight_table/get/TEST');
        $this->assertNotNull($actual);
    }

    public function test_get_weighted_table_resultFalse() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_weighted_table' => json_encode([
                            [
                                'SESSION' => FALSE
                            ]
                        ])
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;

                MonkeyPatch::patchFunction('json_decode', FALSE, SOCOM_Program::class);
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/weight_table/get/TEST');
        $this->assertNotNull($actual);
    }
}