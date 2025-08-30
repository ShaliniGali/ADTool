<?php

class SOCOM_Program_model_with_mocks_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('SOCOM_model_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_Program_model();
    }

    public function test_get_program_id() {
        $program_name = 'EOYTGIAZWDKFJNSCRVLUBQXHPM';
        $result = $this->obj->get_program_id($program_name);
        $this->assertNotEmpty($result);
    }

    public function test_get_program_id_nonexistent_program_name() {
        $program_name = 'DOES_NOT_EXIST';
        $result = $this->obj->get_program_id($program_name);
        $this->assertFalse($result);
    }
}