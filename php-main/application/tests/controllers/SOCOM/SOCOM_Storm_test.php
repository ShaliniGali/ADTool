​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Storm_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_Storm_model = new SOCOM_Storm_model();
    }


    public function test_get_storm() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Storm_model = $this->getDouble(
                    'SOCOM_Storm_model', [
                        'get_storm' => []
                    ]
                );
                $CI->SOCOM_Storm_model = $SOCOM_Storm_model;
            }
        );
        $actual = $this->request('GET', 'socom/resource_constrained_coa/program/list/get_storm');
        $this->assertNotNull($actual);
    }
}