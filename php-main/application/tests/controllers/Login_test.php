​​​​​​​​​​​​​​​<?php 
/**
 * @group base
 */
class Login_test extends RhombusControllerTestCase
{
    public function test_index()
    {
        // First_admin_controller redirect
        // If FirstAdminFlag.txt doesn't exist, create and delete file for testing (setUp/tearDown)
        $firstAdminFlagFileExists = is_file(APPPATH.'first_admin_folder/FirstAdminFlag.txt');
        if (!$firstAdminFlagFileExists) {
            file_put_contents(APPPATH.'first_admin_folder/FirstAdminFlag.txt', 'test');
        }
        $this->request('GET', 'Login/index');
        $this->assertRedirect('/first_admin/index');
        if (!$firstAdminFlagFileExists) {
            unlink(APPPATH.'first_admin_folder/FirstAdminFlag.txt');
        }

        // Home redirect
        // If FirstAdminFlag.txt exists, delete and create file for testing (setUp/tearDown)
        $firstAdminFlagFileExists = is_file(APPPATH.'first_admin_folder/FirstAdminFlag.txt');
        $firstAdminFlagFileContents = '1';
        if ($firstAdminFlagFileExists) {
            $firstAdminFlagFileContents = file_get_contents(APPPATH.'first_admin_folder/FirstAdminFlag.txt');
            unlink(APPPATH.'first_admin_folder/FirstAdminFlag.txt');
        }
        $this->request->addCallable(
            function ($CI) {
                $Login_private_subnet_model = $this->getDouble(
                    'Login_private_subnet_model', [
                        'enforcePrivateSubnetLogin' => FALSE,
                    ]
                );
                $CI->Login_private_subnet_model = $Login_private_subnet_model;
            }
        );
        $this->request('GET', 'Login/index');
        $this->assertRedirect('Home');
        if ($firstAdminFlagFileExists) {
            file_put_contents(APPPATH.'first_admin_folder/FirstAdminFlag.txt', $firstAdminFlagFileContents);
        }

        // load login_private_subnet_view
        $this->request->addCallable(
            function ($CI) {
                $Login_private_subnet_model = $this->getDouble(
                    'Login_private_subnet_model', [
                        'enforcePrivateSubnetLogin' => TRUE,
                        'has_access' => FALSE
                    ]
                );
                $CI->Login_private_subnet_model = $Login_private_subnet_model;
            }
        );
        $actual = $this->request('GET', 'Login/index');
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');

        // load login_view
        $this->request->addCallable(
            function ($CI) {
                $Login_private_subnet_model = $this->getDouble(
                    'Login_private_subnet_model', [
                        'enforcePrivateSubnetLogin' => FALSE,
                    ]
                );
                $CI->Login_private_subnet_model = $Login_private_subnet_model;

                $CI->session->unset_userdata('logged_in');
            }
        );
        $actual = $this->request('GET', 'Login/index');
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');
    }

    public function test_activate()
    {
        // load login_view
        $this->request->addCallable(
            function ($CI) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info' => [
                            [
                                'account_type' => 'test',
                                'time' => 'test',
                                'id' => 'test'
                            ]
                        ],
                    ]
                );
                $CI->Login_model = $Login_model;

                $Register_model = $this->getDouble(
                    'Register_model', [
                        'user_activate' => NULL
                    ]
                );
                $CI->Register_model = $Register_model;
            }
        );
        $hash = encrypted_string([
            'type' => 'test',
            'time' => time(),
            'account_type' => 'test',
            'email' => 'test',
            'id' => 1
        ], 'encode');
        $actual = $this->request('GET', '/login/activate/' . $hash);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');
    }

    public function test_activate_register_noDataCheck()
    {
        $params = array();
        $actual = $this->request('POST', '/login/activate_register', $params);
    
        $this->assertSame('',$actual);
    }

    public function test_activate_register()
    {
        // account is not USER, fail validation
        $params = [
            'ExpiryDate' => '',
            'SiteURL' => encrypted_string(
                [
                    'type' => 'test',
                    'email' => 'test',
                    'account_type' => 'MODERATOR',
                    'id' => 1,
                    'time' => time()
                ]
                , 'encode'
            ),
            'AccountType' => 'MODERATOR',
            'EnableLoginLayer' => 'No',
            'TFAGroup' => [
                'gAuth' => '',
                'yubikey' => 'No',
                'cac' => 'No',
            ],
        ];
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('error', $actual['result']);

        // account is not USER, failed to insert expiry data
        $this->request->addCallable(
            function ($CI) {
                $Register_model = $this->getDouble(
                    'Register_model', [
                        'insert_expiry_date' => FALSE,
                        'user_activate' => FALSE
                    ]
                );
                $CI->Register_model = $Register_model;
            }
        );
        $params['ExpiryDate'] = 'test';
        $params['TFAGroup']['gAuth'] = 'No';
        $this->request('POST', '/login/activate_register', $params);
        $this->assertResponseCode(200);

        // account is not USER, non-null AccountType, don't enable login layer
        $this->request->addCallable(
            function ($CI) {
                $Register_model = $this->getDouble(
                    'Register_model', [
                        'insert_expiry_date' => TRUE,
                        'user_activate' => FALSE
                    ]
                );
                $CI->Register_model = $Register_model;

                $LoginModel = $this->createMock(Login_model::class);
                $LoginModel->method('user_info')->willReturn([['id' => 1, 'status' => AccountStatus::Active]]);
                $CI->Login_model = $LoginModel;
            }
        );
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertResponseCode(200);

        // account is not USER, null AccountType, enable login layer, don't enable any login layers
        $params['AccountType'] = '';
        $params['EnableLoginLayer'] = 'Yes';
        $params['TFAGroup'] = [
            'gAuth' => 'No',
            'yubikey' => 'No',
            'cac' => 'No'
        ];
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('error', $actual['result']);

        // account is not USER, enable login layer
        $params['TFAGroup']['gAuth'] = 'Yes';
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual['result']);

        // account is USER, fail validation
        $params = [
            'SiteURL' => encrypted_string([
                'type' => 'test',
                'email' => 'test',
                'account_type' => 'USER',
                'id' => 1,
                'time' => time()
                ], 'encode'
            ),
            'AccountType' => 'USER',
            'EnableLoginLayer' => 'No',
            'TFAGroup' => [
                'gAuth' => '',
                'yubikey' => 'No',
                'cac' => 'No',
            ],
        ];
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('error', $actual['result']);

        // account is USER, don't enable login layer
        $params['TFAGroup']['gAuth'] = 'No';
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual['result']??'');

        // account is USER, enable login layer and don't enable any login layers
        $params['TFAGroup']['gAuth'] = 'No';
        $params['TFAGroup']['yubikey'] = 'No';
        $params['TFAGroup']['cac'] = 'No';
        $params['EnableLoginLayer'] = 'Yes';
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('error', $actual['result']);

        // account is USER, enable login layer and enable a login layer
        $params['TFAGroup']['gAuth'] = 'Yes';
        $actual = $this->request('POST', '/login/activate_register', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual['result']??'');
    }

    public function test_logout()
    {
        $this->request->addCallable(
            function ($CI) {
                $session = $this->getDouble(
                    'CI_Session', [
                        'sess_destroy' => TRUE
                    ]
                );
                $CI->session = $session;
            }
        );
        $actual = $this->request('GET', '/login/logout');
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');
    }

    public function test_user_check()
    {
        // invalid input
        $params = [
            'username' => 'unit_tester@rhombuspower.com',
        ];
        $actual = $this->request('POST', '/login/user_check', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('failed', $actual['result']);

        // tos not checked
        $params = [
            'username' => 'unit_tester@rhombuspower.com',
            'password' => 'test',
            'tos_agreement_check' => 'false'
        ];
        $actual = $this->request('POST', '/login/user_check', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('failed', $actual['result']);

        // with cac on
        MonkeyPatch::patchFunction('setcookie', '', 'Login::user_check');
        $this->request->addCallable(
            function ($CI) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_check' => [
                            'message' => 'require_login_layer',
                            'layers' => [
                                2 => '1'
                            ]
                        ]
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        $params = [
            'username' => 'unit_tester@rhombuspower.com',
            'password' => 'test',
            'tos_agreement_check' => 'true'
        ];
        $actual = @$this->request('POST', '/login/user_check', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('require_login_layer', $actual['result']);

        // with cac off
        $this->request->addCallable(
            function ($CI) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_check' => [
                            'message' => 'require_login_layer',
                            'layers' => [
                                2 => '0'
                            ]
                        ]
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        $params = [
            'username' => 'unit_tester@rhombuspower.com',
            'password' => 'test',
            'tos_agreement_check' => 'true'
        ];
        $this->disableStrictErrorCheck();
        $actual = $this->request('POST', '/login/user_check', $params);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('require_login_layer', $actual['result']);
    }

    public function test_login_recovery_code()
    {
        // invalid request
        $this->request->addCallable(
            function ($CI) {
                $CI->session->set_userdata('tfa_pending', 'test');

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => 1,
                        'recovery_code_login' => 'test'
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        $actual = $this->request('POST', '/login/login_recovery_code');
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('error', $actual['result']['message']);

        $actual = $this->request('POST', '/login/login_recovery_code', [
            'Recovery_key' => 'abc123'
        ]);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('test', $actual['result']);
    }

    public function test_reset_recovery_codes()
    {
        $this->request->addCallable(
            function ($CI) {
                $CI->session->set_userdata('tfa_pending', 'test');

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => 1,
                        'reset_recovery_code' => [
                            'message' => 'test'
                        ]
                    ]
                );
                $CI->Login_model = $Login_model;

                $Register_model = $this->getDouble(
                    'Register_model', [
                        'get_recovery_keys' => 'test'
                    ]
                );
                $CI->Register_model = $Register_model;
            }
        );
        $actual = $this->request('GET', '/login/reset_recovery_codes');
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('test', $actual['result']);
    }

    public function test_check_key_exist()
    {
        $this->request->addCallable(
            function ($CI) {
                $CI->session->set_userdata('tfa_pending', 'test');

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => 1,
                        'check_recovery_key' => [
                            'message' => 'test'
                        ]
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        $actual = $this->request('GET', '/login/check_key_exist');
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('test', $actual['result']);
    }

    public function test_confirm_reset_password()
    {
        // on success
        $this->request->addCallable(
            function ($CI) {
                $CI->session->set_userdata('tfa_pending', 'test');

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => 1,
                        'update_password' => [
                            'message' => 'test'
                        ]
                    ]
                );
                $CI->Login_model = $Login_model;

                $Generic = $this->getDouble(
                    'Generic', [
                        'validatePassword' => 'success',
                    ]
                );
                $CI->Generic = $Generic;
            }
        );
        $actual = $this->request('POST', '/login/confirm_reset_password', [
            'username' => 'test',
            'Password' => 'test',
        ]);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('test', $actual['result']);

        // on failure
        $this->request->addCallable(
            function ($CI) {
                $Generic = $this->getDouble(
                    'Generic', [
                        'validatePassword' => FALSE,
                    ]
                );
                $CI->Generic = $Generic;
            }
        );
        $actual = $this->request('POST', '/login/confirm_reset_password', [
            'username' => 'test',
            'Password' => 'test',
        ]);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('error', $actual['result']);
    }

    public function test_activate_reset_password_checkResult() 
    { 
        $user_info_result =  [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => 00111, 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchFunction(
			'encrypted_string',
			[
                'id' => 1,
                'time' => 123
            ],
			Login::class . '::activate_reset_password'
        );
        
        $this->request->addCallable(
            function ($CI) use ($user_info_result) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info' => $user_info_result,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $hash = 'hash';
        $actual = $this->request('GET', '/login/activate_reset_password/' . $hash);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');
    }

    public function test_activate_reset_password_checkResultWithEmptyDecryptData() 
    { 
        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchFunction(
			'encrypted_string',
			[],
			Login::class . '::activate_reset_password'
        );
        
        $hash = 'hash';
        $actual = $this->request('GET', '/login/activate_reset_password/' . $hash);
        $this->assertRedirect(base_url());
    }

    public function test_activate_reset_password_checkResultWithResetPasswordStatusAndHugeTime() 
    { 
        $user_info_result =  [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Reset_password', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => 00111, 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchFunction(
			'encrypted_string',
			[
                'id' => 2,
                'time' => 2324324832483294334
            ],
			Login::class . '::activate_reset_password'
        );
        
        $this->request->addCallable(
            function ($CI) use ($user_info_result) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info' => $user_info_result,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $hash = 'hash';
        $actual = $this->request('GET', '/login/activate_reset_password/' . $hash);
        // echo $actual;
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');
    }

    public function test_activate_reset_password_checkResultWithResetPasswordStatusAndSmallTime() 
    { 
        $user_info_result =  [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Reset_password', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => 00111, 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchFunction(
			'encrypted_string',
			[
                'id' => 2,
                'time' => 214
            ],
			Login::class . '::activate_reset_password'
        );
        
        $this->request->addCallable(
            function ($CI) use ($user_info_result) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info' => $user_info_result,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $hash = 'hash';
        $actual = $this->request('GET', '/login/activate_reset_password/' . $hash);
        // echo $actual;
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual??'');
    }

    public function test_send_reset_password() 
    { 
        $user_id = 1;
        $send_reset_password = [
            'message' => 'success'
        ];

        $this->request->addCallable(
            function ($CI) use ($user_id, $send_reset_password) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => $user_id,
                        'send_reset_password_details' => $send_reset_password
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $actual = $this->request('GET', '/login/send_reset_password');
        $expected = '{"result":"success"}';

        $this->assertJsonStringEqualsJsonString($expected, $actual);
        $this->assertIsArray(json_decode($actual, true));
    }

    public function test_send_reset_password_by_email_checkInValidForm() 
    { 
        $actual = $this->request('POST', '/login/send_reset_password_by_email',
            ['email' => 'test']);

        $expected = '{"validation":"fail","message":"<p>The email field must contain a valid email address.<\/p>"}';
        $this->assertIsArray(json_decode($actual, true));
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function test_send_reset_password_by_email_checkValidForm() 
    { 
        $user_id = 1;
        $send_reset_password = [
            'message' => 'success'
        ];
        $this->request->addCallable(
            function ($CI) use ($user_id, $send_reset_password) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => $user_id,
                        'send_reset_password_details' => $send_reset_password
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        $actual = $this->request('POST', '/login/send_reset_password_by_email',
            ['email' => 'test@email.com']);

        $expected = '{"validation":"success","result":"success"}';
        $this->assertIsArray(json_decode($actual, true));
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function test_send_reset_password_by_email_checkValidFormWithNullId() 
    { 
        $user_id = '';
        $send_reset_password = [
            'message' => 'success'
        ];
        $this->request->addCallable(
            function ($CI) use ($user_id, $send_reset_password) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_user_id' => $user_id,
                        'send_reset_password_details' => $send_reset_password
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        $actual = $this->request('POST', '/login/send_reset_password_by_email',
            ['email' => 'test@email.com']);
        $expected = '{"validation":"fail","message":"Invalid user credential."}';
        $this->assertIsArray(json_decode($actual, true));
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function test_nothing() { 
        $actual = $this->request('GET', '/login/nothing');
        $expected = 'this endpoint exists to maintain sso login';

        $this->assertEquals($expected, $actual);
        $this->assertIsString($actual);
    }
}
