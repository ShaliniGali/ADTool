<?php

class SOCOM_model_db_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('SOCOM_model_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_model();
    }

    public function test_cap_sponsor_count() {
        $result = $this->obj->cap_sponsor_count('DT_ZBT_EXTRACT_2026');

        $this->assertNotEmpty($result);
    }

    public function test_cap_sponsor_dollar() {
        $result = $this->obj->cap_sponsor_dollar('DT_ZBT_EXTRACT_2026');

        $this->assertNotEmpty($result);
    }

    public function test_net_change() {
        $result = $this->obj->net_change('DT_ZBT_EXTRACT_2026');

        $this->assertNotEmpty($result);
    }

    public function test_dollars_moved_resource_category() {
        $result = $this->obj->dollars_moved_resource_category('DT_ZBT_EXTRACT_2026');

        $this->assertNotEmpty($result);
    }

    public function test_cap_sponsor_approve_reject() {
        $result = $this->obj->cap_sponsor_approve_reject('DT_ZBT_EXTRACT_2026');

        $this->assertNotEmpty($result);
    }

    public function test_get_resource_category_code() {
        $result = $this->obj->get_resource_category_code('DT_ZBT_EXTRACT_2026');

        $this->assertNotEmpty($result);
    }

    public function test_zbt_summary_program_summary_card() {
        $table1 = 'DT_ZBT_EXTRACT_2026'; 
        $table2 = ''; 
        $l_pom_sponsor = ['IPGBFOCVUKTRW']; 
        $l_cap_sponsor = ['TUXRPFMVWAJLD'];
        $l_ass_area = ['K']; 
        $l_approval_status = ['PENDING','COMPLETED'];
        $program_list = ['NICTORBPGUX'];

        $result = $this->obj->zbt_summary_program_summary_card(
            $table1, 
            $table2, 
            $l_pom_sponsor, 
            $l_cap_sponsor, 
            $l_ass_area, 
            $l_approval_status, 
            $program_list);

        $this->assertNotEmpty($result);
    }

    public function test_get_eoc() {
        $program_code = 'EOYTGIAZWDKFJNSCRVLUBQXHPM';
        $result = $this->obj->get_eoc($program_code);

        $this->assertNotEmpty($result);
    }
     
}