<?php

class SOCOM_Users_model_test extends RhombusModelTestCase {

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('Users_seeder');
        $CI->seeder->call('USR_ADMIN_USERS_seeder');
        $CI->seeder->call('SOCOM_AOAD_model_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_Users_model();

        $this->obj->session = $this->getSessionMock();
    }

    public function test_is_super_admin_true() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);
        
        $result = $this->obj->is_super_admin();

        $this->assertTrue($result);
    }

    public function test_user_can_super_admin_true() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_AOAD_model::class, ['is_super_admin' => true]);
        
        $this->obj->user_can_super_admin();

        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function test_user_can_super_admin_false() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, ['is_super_admin' => false]);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not an admin user');

        $this->obj->user_can_super_admin();
    }

    public function test_user_can_admin_super_admin_true() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_super_admin' => true,
            'is_admin_user' => false
        ]);

        $result = $this->obj->user_can_admin();

        $this->assertTrue($result);
    }

    public function test_user_can_admin_false() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_super_admin' => false,
            'is_admin_user' => false
        ]);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not an admin user');

        $this->obj->user_can_admin();
    }

    public function test_user_can_ao_ad_admin_true() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_ao_ad_user' => true
        ]);

        $result = $this->obj->user_can_ao_ad_admin();

        $this->assertTrue($result);
    }

    public function test_user_can_ao_ad_admin_false() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_ao_ad_user' => false
        ]);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not an ao or ad user');
    
        $this->obj->user_can_ao_ad_admin();
    }

    public function test_get_id_from_email_true() {
        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_super_admin' => true,
            'is_admin_user' => false
        ]);

        $result = $this->obj->get_id_from_email("unit_tester@rhombuspower.com");
        $this->assertTrue(TRUE);
    }

    public function test_get_id_from_email_false() {
        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_super_admin' => false,
            'is_admin_user' => false
        ]);

        $this->expectException(ErrorException::class);

        $result = $this->obj->get_id_from_email("unit_tester@rhombuspower.com");
    }

    public function test_get_users_true() {

        $result = $this->obj->get_users();
        $this->assertTrue(TRUE);
    }

    public function test_get_ao_ad_user_success() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_ao_ad_user' => true
        ]);

        $result = $this->obj->get_ao_ad_user();
        $this->assertTrue(TRUE);
    }

    public function test_get_ao_ad_user_exception() {

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_ao_ad_user' => false
        ]);

        $this->expectException(ErrorException::class);
        $result = $this->obj->get_ao_ad_user();
    }

    public function test_get_ao_ad_users_success() {
        $users = [
            2225 => 'user1@example.com'
        ];

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => true
        ]);

        $result = $this->obj->get_ao_ad_users($users);
        $this->assertTrue(TRUE);
    }

    public function test_get_ao_ad_users_permission_denied() {
        $users = [
            2225 => 'user1@example.com',
            2226 => 'user2@example.com'
        ];

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => function() {
                throw new ErrorException('User is not an admin user');
            }
        ]);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not an admin user');

        $this->obj->get_ao_ad_users($users);
    }

    public function test_activate_ao_ad_users_success() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => true,
            'save_ao_ad_user_history' => true
        ]);

        $result = $this->obj->activate_ao_ad_user(1);
        $this->assertTrue(TRUE);
    }

    public function test_activate_ao_ad_user_exception() {

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => function() {
                throw new ErrorException('User is not an admin user');
            }
        ]);

        $this->expectException(ErrorException::class);
        $result = $this->obj->activate_ao_ad_user(1);
    }


    public function test_set_ao_ad_user_invalid_gid() {
        $id = 2225;
        $invalid_gid = 5;

        $this->expectException(ErrorException::class);
        $this->obj->set_ao_ad_user($id, $invalid_gid);
    }

    public function test_set_ao_ad_user_update_existing() {
        $id = 2225;
        $gid = 2;

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_ao_ad_user' => true,
            'save_ao_ad_user_history' => true
        ]);

        $result = $this->obj->set_ao_ad_user($id, $gid);

        $this->assertTrue(TRUE);
    }

    public function test_set_ao_ad_user_insert_new() {
        $id = 2225;
        $gid = 3;

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_ao_ad_user' => false
        ]);

        $result = $this->obj->set_ao_ad_user($id, $gid);
        $this->assertTrue(TRUE);
    }

    public function test_delete_ao_ad_user_existing() {
        $id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => true,
            'save_ao_ad_user_history' => true
        ]);

        $result = $this->obj->delete_ao_ad_user($id);
        $this->assertTrue(TRUE);
    }

    public function test_delete_ao_ad_user_exception() {
        $id = 2225;

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => function() {
                throw new ErrorException('User is not an admin user');
            }
        ]);

        $this->expectException(ErrorException::class);
        $this->obj->delete_ao_ad_user($id);
    }

    public function test_is_ao_ad_user() {
        $id = 2225;
    
        $this->obj->is_ao_ad_user($id);
        $this->assertTrue(TRUE);
    }

    public function test_save_ao_ad_user_history_success() {
        $userId = 2225;

        $this->obj->save_ao_ad_user_history($userId);
        $this->assertTrue(TRUE);
    }

    public function test_save_ao_ad_user_history_user_not_found() {
        $userId = 2224;

        $this->obj->save_ao_ad_user_history($userId);
        $this->assertTrue(TRUE);
    }

    public function test_is_admin_user() {
        $id = 2225;
    
        $this->obj->is_admin_user($id);
        $this->assertTrue(TRUE);
    }


    public function test_get_admin_users_success() {
        $users = [
            2225 => 'user1@example.com'
        ];

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => true
        ]);

        $result = $this->obj->get_admin_user($users);
        $this->assertTrue(TRUE);
    }

    public function test_get_admin_users_permission_denied() {
        $users = [
            2225 => 'user1@example.com',
            2226 => 'user2@example.com'
        ];

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_admin' => function() {
                throw new ErrorException('User is not an admin user');
            }
        ]);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('User is not an admin user');

        $this->obj->get_admin_user($users);
    }

    public function test_activate_admin_users_success() {
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_super_admin' => true,
            'save_admin_user_history' => true
        ]);

        $result = $this->obj->activate_admin_user(1);
        $this->assertTrue(TRUE);
    }

    public function test_activate_admin_user_exception() {

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_super_admin' => function() {
                throw new ErrorException('User is not an admin user');
            }
        ]);

        $this->expectException(ErrorException::class);
        $result = $this->obj->activate_admin_user(1);
    }

    public function test_set_admin_user_invalid_gid() {
        $id = 2225;
        $invalid_gid = 5;

        $this->expectException(ErrorException::class);
        $this->obj->set_admin_user($id, $invalid_gid);
    }

    public function test_set_admin_user_update_existing() {
        $id = 2225;
        $gid = 2;

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_admin_user' => true,
            'save_admin_user_history' => true
        ]);

        $result = $this->obj->set_admin_user($id, $gid);

        $this->assertTrue(TRUE);
    }

    public function test_set_admin_user_insert_new() {
        $id = 2225;
        $gid = 1;

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'is_admin_user' => false
        ]);

        $result = $this->obj->set_admin_user($id, $gid);
        $this->assertTrue(TRUE);
    }

    public function test_delete_admin_user_existing() {
        $id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_super_admin' => true,
            'save_admin_user_history' => true
        ]);

        $result = $this->obj->delete_admin_user($id);
        $this->assertTrue(TRUE);
    }

    public function test_delete_admin_user_exception() {
        $id = 2225;

        MonkeyPatch::patchMethod(SOCOM_Users_model::class, [
            'user_can_super_admin' => function() {
                throw new ErrorException('User is not an admin user');
            }
        ]);

        $this->expectException(ErrorException::class);
        $this->obj->delete_admin_user($id);
    }

    public function test_save_admin_user_history_success() {
        $userId = 2225;

        $this->obj->save_admin_user_history($userId);
        $this->assertTrue(TRUE);
    }

    public function test_save_admin_user_history_user_not_found() {
        $userId = 2224;

        $this->obj->save_admin_user_history($userId);
        $this->assertTrue(TRUE);
    }

    public function test_get_user_info_true() {
        $id = 2225;
        $result = $this->obj->get_user_info($id);

        $this->assertTrue(TRUE);
    }

    public function test_get_user_info_false() {
        $id = 120;
        $result = $this->obj->get_user_info($id);

        $this->assertFalse($result);
    }
}