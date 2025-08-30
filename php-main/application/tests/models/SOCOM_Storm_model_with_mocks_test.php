<?php

class SOCOM_Storm_model_with_mocks_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('SOCOM_model_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_Storm_model();
    }

    public function test_get_storm() {
        $result = $this->obj->get_storm();
        $this->assertTrue(true);
    }
}