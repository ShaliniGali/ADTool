​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Weights_Builder_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_Weights_model = new SOCOM_Weights_model();
    }

    public function test_create_weights() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'count_weights' => 10
                    ]
                );
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/weights/create');
        $this->assertNotNull($actual);
    }

    public function test_save_weights_catchException() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'create_weights' => new InvalidArgumentException('test message')
                    ]
                );
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/weights/save', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_save_weights_noException() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'create_weights' => TRUE
                    ]
                );
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
            }
        );
        $actual = $this->request('POST', 'socom/resource_constrained_coa/weights/save', [
            'test' => 'test'
        ]);
        $this->assertNotNull($actual);
    }

    public function test_save_weights_throwException() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_model = $this->getMockBuilder('SOCOM_Weights_model')
                ->disableOriginalConstructor()
                ->getMock();


                $SOCOM_Weights_model->expects($this->once())
                    ->method('create_weights')
                    ->willThrowException(new InvalidArgumentException('Invalid arguments provided'));
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
        });
        
        $actual = $this->request('POST', 'socom/resource_constrained_coa/weights/save', [
            'test' => 'test'
        ]);
        $response = json_decode($actual, true);

        $this->assertFalse($response['status']);
        $this->assertEquals('Invalid arguments provided', $response['message']);
        $this->assertResponseCode(500); 
    }

    public function test_delete_weights() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Weights_model = $this->getDouble(
                    'SOCOM_Weights_model', [
                        'delete_user_weight' => TRUE
                    ]
                );
                $CI->SOCOM_Weights_model = $SOCOM_Weights_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/weights/delete/1');
        $this->assertNotNull($actual);
    }
}