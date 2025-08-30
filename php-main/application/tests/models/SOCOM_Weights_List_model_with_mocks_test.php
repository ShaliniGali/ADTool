<?php

class SOCOM_Weights_List_model_with_mocks_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('USR_LOOKUP_CRITERIA_WEIGHTS_seeder');
        $CI->seeder->call('USR_OPTION_SCORES_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_Weights_List_model();
        $this->obj->session = $this->getSessionMock();
    }

    public function test_get_data() {
        $mockUserId = 8540;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $weight_id = 9358;
        $result = $this->obj->get_data($weight_id);
        $this->assertNotEmpty($result);
    }

    public function test_get_weight_dropdown_selects() {
        $mockUserId = 2693;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->get_weight_dropdown_selects();
        $this->assertNotEmpty($result);
    }

    public function test_get_criteria_weights_table() {
        $mockUserId = 2693;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->get_criteria_weights_table();
        $this->assertNotEmpty($result);
    }

    public function test_save_weight_score_data() {
        $mockUserId = 8540;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);


        $weight_id = 9358;
        $weight_data = [
            'guidance' => [
                ['criteria' => '', 'weight' => 0.25],
                ['criteria' => '', 'weight' => 0.50],
                ['criteria' => '', 'weight' => 0.25]
            ],
            'pom' => [
                ['criteria' => '', 'weight' => 0.40],
                ['criteria' => '', 'weight' => 0.30],
                ['criteria' => '', 'weight' => 0.30]
            ]
        ];

        $result = $this->obj->save_weight_score_data($weight_id, $weight_data);
        $this->assertNotEmpty($result);
    }

    public function test_get_saved_weight_data() {
        $mockUserId = 8540;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $weight_id = 9358;
        $result = $this->obj->get_saved_weight_data($weight_id);
        $this->assertNotEmpty($result);
    }
}