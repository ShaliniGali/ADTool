​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_AOAD_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_Program_model = new SOCOM_Program_model();
        $this->SOCOM_AOAD_model = new SOCOM_AOAD_model();
    }

    public function test_save_ao_ad_dropdown_ao() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;

                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'save_ao_ad_data' => true,
                        'get_ao_by_event_id_user_id' => 'comments'
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ao/dropdown/save', [
            'value' => 'Disapprove',
            'type' => 'dropdown',
            'program' => 'test',
            'eoc_id' => 1
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_dropdown_ad() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;

                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'save_ao_ad_data' => true,
                        'get_ad_by_event_id_user_id' => 'comments'
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ad/dropdown/save', [
            'value' => 'Disapprove',
            'type' => 'dropdown',
            'program' => 'test',
            'eoc_id' => 1
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_dropdown_emptyValue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ad/dropdown/save', [
            'value' => '',
            'type' => 'dropdown',
            'program' => 'test',
            'eoc_id' => 1
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_dropdown_noParams() {
        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ad/dropdown/save', []);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_comment_ao() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;

                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'save_ao_ad_data' => true,
                        'get_ao_by_event_id_user_id' => 'comments',
                        'get_ad_by_event_id_user_id' => 'comments'
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ao/comment/save', [
            'value' => 'Disapprove',
            'type' => 'dropdown',
            'program' => 'test',
            'eoc_id' => 1
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_comment_ad() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;

                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'save_ao_ad_data' => true,
                        'get_ao_by_event_id_user_id' => 'comments',
                        'get_ad_by_event_id_user_id' => 'comments'
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ad/comment/save', [
            'value' => 'Disapprove',
            'type' => 'dropdown',
            'program' => 'test',
            'eoc_id' => 1
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_comment_emptyValue() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;

                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'save_ao_ad_data' => true,
                        'get_ao_by_event_id_user_id' => 'comments',
                        'get_ad_by_event_id_user_id' => 'comments'
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ad/comment/save', [
            'value' => '',
            'type' => 'dropdown',
            'program' => 'test',
            'eoc_id' => 1
        ]);
        $this->assertIsString($actual);
    }

    public function test_save_ao_ad_comment_noParams() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Program_model = $this->getDouble(
                    'SOCOM_Program_model', [
                        'get_program_id' => 10
                    ]
                );
                $CI->SOCOM_Program_model = $SOCOM_Program_model;

                $SOCOM_AOAD_model = $this->getDouble(
                    'SOCOM_AOAD_model', [
                        'save_ao_ad_data' => true,
                        'get_ao_by_event_id_user_id' => 'comments',
                        'get_ad_by_event_id_user_id' => 'comments'
                    ]
                );
                $CI->SOCOM_AOAD_model = $SOCOM_AOAD_model;
            }
        );


        $actual = $this->request('POST', 'socom/zbt_summary/eoc_summary/ad/comment/save', []);
        $this->assertIsString($actual);
    }

}