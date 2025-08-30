<?php

class SOCOM_Score_model_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('SOCOM_Score_model_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_Score_model();
    }

    public function test_save_score_validate_valid_params() {
        $params = [
            'user_id' => 1,
            'score_name' => 'Test Score',
            'score_description' => 'This is a test score description.',
            'program_id' => 'PRG001'
        ];

        $result = $this->obj->_save_score_validate($params, false);
        $this->assertTrue($result);
    }

    public function test_save_score_validate_invalid_user_id() {
        $params = [
            'user_id' => 'invalid_id',
            'score_name' => 'Test Score',
            'score_description' => 'This is a test score description.',
            'program_id' => 'PRG001'
        ];

        $result = $this->obj->_save_score_validate($params, false);
        $this->assertFalse($result);
    }

    public function test_save_score_validate_invalid_score_name() {
        $params = [
            'user_id' => 1,
            'score_name' => str_repeat('A', 101), // Exceeds 100 characters
            'score_description' => 'This is a test score description.',
            'program_id' => 'PRG001'
        ];

        $result = $this->obj->_save_score_validate($params, false);
        $this->assertFalse($result);
    }

    public function test_save_score_validate_missing_score_name() {
        $params = [
            'user_id' => 1,
            'score_description' => 'This is a test score description.',
            'program_id' => 'PRG001'
        ];

        $result = $this->obj->_save_score_validate($params, false);
        $this->assertFalse($result);
    }

    public function test_save_score_validate_invalid_score_description() {
        $params = [
            'user_id' => 1,
            'score_name' => 'Test Score',
            'score_description' => str_repeat('A', 1025), // Exceeds 1024 characters
            'program_id' => 'PRG001'
        ];

        $result = $this->obj->_save_score_validate($params, false);
        $this->assertFalse($result);
    }

    public function test_save_score_validate_invalid_score_id_on_update() {
        $params = [
            'user_id' => 1,
            'score_id' => 'invalid_id',
            'score_name' => 'Test Score',
            'score_description' => 'This is a test score description.',
        ];

        $result = $this->obj->_save_score_validate($params, true);
        $this->assertFalse($result);
    }

    public function test_save_score_validate_invalid_program_id_on_insert() {
        $params = [
            'user_id' => 1,
            'score_name' => 'Test Score',
            'score_description' => 'This is a test score description.',
            'program_id' => 123 // Invalid type
        ];

        $result = $this->obj->_save_score_validate($params, false);
        $this->assertFalse($result);
    }

    public function test_save_score_insert() {
        $params = [
            'user_id' => 1,
            'score_name' => 'New Score',
            'score_description' => 'This is a new score description.',
            'program_id' => 'PRG002',
            'score_data' => []
        ];

        $result = $this->obj->save_score($params, false);
        $this->assertTrue($result);
    }

    public function test_save_score_update() {
        $params = [
            'user_id' => 1,
            'score_id' => 1,
            'score_name' => 'Updated Score',
            'score_description' => 'This is an updated score description.',
            'score_data' => []
        ];

        $result = $this->obj->save_score($params, true);
        $this->assertTrue($result);
    }

    public function test_save_score_invalid_params() {
        $params = [
            'user_id' => 'invalid',
            'score_name' => 'Invalid Score',
            'score_description' => 'This description exceeds the maximum allowed length.'.str_repeat('A', 1000),
            'program_id' => 'PRG002',
            'score_data' => []
        ];

        $result = $this->obj->save_score($params, false);
        $this->assertFalse($result);
    }

    public function test_get_score_valid() {
        $score_id = 1;
        $program_id = 'PRG001';
        $user_id = 1;

        $result = $this->obj->get_score($score_id, $program_id, $user_id);

        $this->assertIsArray($result);
    }

    public function test_get_score_invalid_score_id() {
        $result = $this->obj->get_score('invalid', 'PRG001', 1);
        $this->assertFalse($result);
    }

    public function test_get_score_invalid_user_id() {
        $result = $this->obj->get_score(1, 'PRG001', 'invalid');
        $this->assertFalse($result);
    }

    public function test_get_score_nonexistent() {
        // Assuming this ID does not exist in the database
        $score_id = 9999;
        $program_id = 'PRG001';
        $user_id = 1;

        $result = $this->obj->get_score($score_id, $program_id, $user_id);
        $this->assertIsArray($result);
    }
}