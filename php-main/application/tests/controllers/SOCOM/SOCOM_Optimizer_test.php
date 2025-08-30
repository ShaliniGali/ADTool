​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Optimizer_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_model = new SOCOM_model();
        $this->SOCOM_Weights_model = new SOCOM_Weights_model();
        $this->SOCOM_Weights_model = new SOCOM_Weights_model();
        $this->SOCOM_COA_model = new SOCOM_COA_model();
    }

    public function test_index() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'get_user_weights' => []
                    ]
                );
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
            }
        );
        $actual = $this->request('GET', 'optimizer/view');
        $this->assertNotNull($actual);
    }

    public function test_optimize_validPost() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_program_scored' => [
                            [
                                'PROGRAM_ID' => 1
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;

                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'get_user_score_id_lists' => []
                    ]
                );
                $CI->DBs->SOCOM_Weights_model = $SOCOM_Weights_model;

                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'store_run' => '1'
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;

                MonkeyPatch::patchFunction('php_api_call', json_encode([
                    'resource_k' => '1000',
                    'selected_programs' => [],
                    'remaining' => 0
                ]), SOCOM_Optimizer::class);
            }
        );
        $actual = $this->request('POST', 'optimizer/optimize', [
            'budget' => ['1']
        ]);
        $this->assertNotNull($actual);
    }

    public function test_optimize_validPost_error() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_model = $this->getDouble(
                    'SOCOM_model', [
                        'get_program_scored' => [
                            [
                                'PROGRAM_ID' => 1
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_model = $SOCOM_model;

                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'get_user_score_id_lists' => []
                    ]
                );
                $CI->DBs->SOCOM_Weights_model = $SOCOM_Weights_model;

                $SOCOM_COA_model = $this->getDouble(
                    'SOCOM_COA_model', [
                        'store_run' => '1'
                    ]
                );
                $CI->SOCOM_COA_model = $SOCOM_COA_model;

                MonkeyPatch::patchFunction('php_api_call', json_encode([
                    
                ]), SOCOM_Optimizer::class);
            }
        );

        try {
			$actual = $this->request('POST', 'optimizer/optimize', [
                'budget' => ['1']
            ]);
		} catch (CIPHPUnitTestExitException $e) {
			$output = ob_get_clean();
		}
        
        $this->assertNotNull($actual);
    }

    public function test_optimize_invalidPost() {
        $actual = $this->request('POST', 'optimizer/optimize', []);
        $this->assertNotNull($actual);
    }
}