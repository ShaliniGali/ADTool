<?php

class SOCOM_AOAD_model_with_mocks_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('SOCOM_AOAD_model_seeder');
        $CI->seeder->call('USR_ISSUE_AO_SAVES_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_AOAD_model();
        $this->obj->session = $this->getSessionMock();
    }

    public function test_get_table() {
        $result = $this->obj->get_table('zbt_summary', 'ao');
        $this->assertNotEmpty($result);

        $result = $this->obj->get_table('zbt_summary', 'ad');
        $this->assertNotEmpty($result);

        $result = $this->obj->get_table('issue', 'ao');
        $this->assertNotEmpty($result);

        $result = $this->obj->get_table('issue', 'ad');
        $this->assertNotEmpty($result);
    }

    public function test_get_ao_by_event_id_user_id_with_invalid_type() {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("AOAD Type not found");
        $result = $this->obj->get_ao_by_event_id_user_id('RFKSBTACZONXPVWHJEDUIMLGYQ', 'AXMEKDCFSHGPNRJTVIUQWLBZYO', 'invalid_type', 2225);
    }

    public function test_get_ad_by_event_id_user_id_with_invalid_type() {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("AOAD Type not found");
        $result = $this->obj->get_ad_by_event_id_user_id('XUQCORYVTWFIGBKHMJLZPAENSD', 'BSYOPAVITZNWMKJGHLRDQCEXFU', 'invalid_type', 1805);
    }

    public function test_is_ao_user() {
        $mockUserId = 2225;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->is_ao_user();
        $this->assertTrue($result);

        $mockUserId = 3418;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->is_ao_user();
        $this->assertTrue($result);
    }

    public function test_is_ad_user() {
        $mockUserId = 1805;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->is_ad_user();
        $this->assertTrue($result);

        $mockUserId = 582;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->is_ad_user();
        $this->assertTrue($result);
    }

    public function test_validate_ao_ad_input() {
        $result = $this->obj->validate_ao_ad_input([
            'AO_RECOMENDATION' => 1,
            'AO_COMMENT'=> 'Comment',
        ]);
        $this->assertTrue(true);
    }

    public function test_validate_ao_ad_input_throw_exception() {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("Must be saving one of AO_RECOMENDATION, AD_RECOMENDATION, AO_COMMENT, AD_COMMENT");

        $result = $this->obj->validate_ao_ad_input([
            'RECOMENDATION' => 1,
            'COMMENT'=> 'Comment',
        ]);
    }

    public function test_save_ao_ad_data_invalid_type() {
        $data = [
            'AD_RECOMENDATION' => 1,
            'AD_COMMENT'=> 'Comment',
        ];
        $program = '';
        $eoc_id = '';
        $event_id = '';
        $type = 'invalid_type';
        $ao_ad_status = 'ao';
        
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('AOAD Type not found');

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
    }

    public function test_save_ao_ad_data_program_not_found() {
        $data = [
            'AO_RECOMENDATION' => 1,
            'AO_COMMENT'=> 'Comment',
        ];
        $program = '';
        $eoc_id = '';
        $event_id = '';
        $type = 'zbt_summary';
        $ao_ad_status = 'ao';

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => false]);
        
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Program is not found');

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
    }

    public function test_save_ao_ad_data_ao_no_permissions() {
        $data = [
            'AO_RECOMENDATION' => 1,
            'AO_COMMENT'=> 'Comment',
        ];
        $program = '';
        $eoc_id = '';
        $event_id = '';
        $type = 'zbt_summary';
        $ao_ad_status = 'ao';

        $mockUserId = null;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => 'RFKSBTACZONXPVWHJEDUIMLGYQ']);
        
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not able to save AO');

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
    }

    public function test_save_ao_ad_data_ad_no_permissions() {
        $data = [
            'AD_RECOMENDATION' => 1,
            'AD_COMMENT'=> 'Comment',
        ];
        $program = '';
        $eoc_id = '';
        $event_id = '';
        $type = 'zbt_summary';
        $ao_ad_status = 'ad';

        $mockUserId = null;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => 'RFKSBTACZONXPVWHJEDUIMLGYQ']);
        
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not able to save AD');

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
    }

    public function test_save_ao_ad_data_is_ao_user() {
        $data = [
            'AO_RECOMENDATION' => 1,
            'AO_COMMENT'=> 'Comment',
        ];
        $program = '';
        $eoc_id = '';
        $event_id = '';
        $type = 'zbt_summary';
        $ao_ad_status = 'ao';

        MonkeyPatch::patchMethod(SOCOM_AOAD_model::class, ['is_ao_user' => true]);

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => 'RFKSBTACZONXPVWHJEDUIMLGYQ']);

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
        $this->assertNotEmpty($result);
    }

    public function test_save_ao_ad_data_is_ad_user() {
        $data = [
            'AD_RECOMENDATION' => 1,
            'AD_COMMENT'=> 'Comment',
        ];
        $program = '';
        $eoc_id = '';
        $event_id = '';
        $type = 'zbt_summary';
        $ao_ad_status = 'ad';

        MonkeyPatch::patchMethod(SOCOM_AOAD_model::class, ['is_ad_user' => true]);

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => 'XUQCORYVTWFIGBKHMJLZPAENSD']);

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
        $this->assertNotEmpty($result);
    }

    public function test_save_ao_ad_data_is_ao_user_update() {
        $data = [
            'AO_RECOMENDATION' => 1,
            'AO_COMMENT'=> 'Comment',
        ];
        $program = 'RFKSBTACZONXPVWHJEDUIMLGYQ';
        $eoc_id = 'AXMEKDCFSHGPNRJTVIUQWLBZYO';
        $event_id = 'AXMEKDCFSHGPNRJTVIUQWLBZYO';
        $type = 'zbt_summary';
        $ao_ad_status = 'ao';

        $mockUserId = 2225;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $program_id = 'RFKSBTACZONXPVWHJEDUIMLGYQ';

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => $program_id]);

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
        $this->assertNotEmpty($result);
    }

    public function test_save_ao_ad_data_is_ad_user_update() {
        $data = [
            'AD_RECOMENDATION' => 1,
            'AD_COMMENT'=> 'Comment',
        ];
        $program = 'XUQCORYVTWFIGBKHMJLZPAENSD';
        $eoc_id = 'BSYOPAVITZNWMKJGHLRDQCEXFU';
        $event_id = 'RUBHZDOTYSKEWMACGLNJQIPVXF';
        $type = 'zbt_summary';
        $ao_ad_status = 'ad';

        $mockUserId = 1805;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $program_id = 'XUQCORYVTWFIGBKHMJLZPAENSD';

        $this->obj->SOCOM_Program_model = $this->getDouble('SOCOM_Program_model', ['get_program_id' => $program_id]);

        $result = $this->obj->save_ao_ad_data($data, $event_id, $type, $ao_ad_status);
        $this->assertNotEmpty($result);
    }


    public function test_get_ao_by_event_id_user_id() {
        $result = $this->obj->get_ao_by_event_id_user_id('DGSMLRNBYITCOWXUZHVAQFKPEJ', 'SWLPJDEMTAZKRNGQUCYOBIHVXF', 'RUBHZDOTYSKEWMACGLNJQIPVXF', 'zbt_summary', 1178);
        $this->assertNotEmpty($result);
    }

    public function test_get_ad_by_event_id_user_id() {
        $result = $this->obj->get_ad_by_event_id_user_id('XUQCORYVTWFIGBKHMJLZPAENSD', 'BSYOPAVITZNWMKJGHLRDQCEXFU', 'RUBHZDOTYSKEWMACGLNJQIPVXF', 'zbt_summary', 1805);
        $this->assertNotEmpty($result);
    }

    public function test_save_ao_ad_user_history() {
        $userId = 219;
        $tablename = 'USR_ISSUE_AO_SAVES';
        $tablehistoryname = 'USR_ISSUE_AO_SAVES_HISTORY';
        $this->obj->save_ao_ad_user_history($userId, $tablename, $tablehistoryname);

        $this->assertTrue(true);


        $userId = 3939;
        $tablename = 'USR_ISSUE_AD_SAVES';
        $tablehistoryname = 'USR_ISSUE_AD_SAVES_HISTORY';
        $this->obj->save_ao_ad_user_history($userId, $tablename, $tablehistoryname);

        $this->assertTrue(true);
    }

    public function test_save_ao_ad_user_history_user_not_found() {
        $userId = 10000000000;
        $tablename = 'USR_ISSUE_AO_SAVES';
        $tablehistoryname = 'USR_ISSUE_AO_SAVES_HISTORY';
        $this->obj->save_ao_ad_user_history($userId, $tablename, $tablehistoryname);

        $this->assertTrue(true);
    }
}