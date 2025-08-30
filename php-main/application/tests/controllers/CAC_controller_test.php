<?php 
/**
 * @group base
 */
class CAC_controller_test extends RhombusControllerTestCase 
{

    public function test_auth_if_not_login() {
        // Model function result(s).
        $user_info_result = [
            [
            "id" => "1",
            "name" => "name",
            "email" => "name@rhombuspower.com",
            "password" => "ec5mKJWnLbUiqOj7GAICDw==",
            "status" => "Active",
            "timestamp" => 1636764275,
            "account_type" => "ADMIN",
            "login_attempts" => 0,
            "login_layers" => "00111",
            "image" => NULL,
            "saltiness" => 'test_salt'
            ]
        ];
        $user_id_result = 1;
        $user_login_success_result = NULL;
        $dump_user = NULL;

        get_instance()->session->set_userdata('tfa_pending', 'bmax1@fauxemail.com');

        MonkeyPatch::patchConstant(
            LoginLayers::class . '::CAC',
            'CAC',
            LoginLayers::class . '::auth'
        );

        MonkeyPatch::patchConstant(
            LoginLayers::class . '::LayerOn',
            'layeron',
            LoginLayers::class . '::auth'
        );

        MonkeyPatch::patchMethod(
            'Login_model',
            ['get_user_id' => 1]
        );

        // Model function callable(s).
        $this->request->addCallable(
            function ($CI) use ($user_info_result, $user_id_result, $user_login_success_result, $dump_user) {
                $Login_model = $this->getDouble('Login_model',
                    [
                        'user_info' => $user_info_result,
                        'user_login_success' => $user_login_success_result,
                        'dump_user' => $dump_user,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $this->request('POST', '/cac/auth', []);
        $this->assertRedirect('/');

    }

    public function test_auth_else_login() {
        // Model function result(s).
        $user_info_result = [
            [
            "id" => "1",
            "name" => "name",
            "email" => "name@rhombuspower.com",
            "password" => "ec5mKJWnLbUiqOj7GAICDw==",
            "status" => "Active",
            "timestamp" => 1636764275,
            "account_type" => "ADMIN",
            "login_attempts" => 0,
            "login_layers" => "00111",
            "image" => NULL,
            "saltiness" => 'test_salt'
            ]
        ];
        $user_id_result = 1;
        $user_login_success_result = NULL;
        $dump_user = NULL;

        get_instance()->session->set_userdata('tfa_pending', 'bmax1@fauxemail.com');

        MonkeyPatch::patchConstant(
            LoginLayers::class . '::CAC',
            'CAC',
            LoginLayers::class . '::auth'
        );

        MonkeyPatch::patchConstant(
            LoginLayers::class . '::LayerOn',
            'layeron',
            LoginLayers::class . '::auth'
        );

        MonkeyPatch::patchMethod(
            'Login_model',
            ['get_user_id' => NULL]
        );

        // Model function callable(s).
        $this->request->addCallable(
            function ($CI) use ($user_info_result, $user_id_result, $user_login_success_result, $dump_user) {
                $Login_model = $this->getDouble('Login_model',
                    [
                        'user_info' => $user_info_result,
                        'user_login_success' => $user_login_success_result,
                        'dump_user' => $dump_user,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $this->request('POST', '/cac/auth', []);
        $this->assertRedirect('Login');

    }

}

?>
