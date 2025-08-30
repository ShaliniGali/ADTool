<?php
/**
 * @group base
 */
class Register_test extends RhombusControllerTestCase {

    public function test_validateEmailDomain() {

        $actual = $this->request('POST','/register/validateEmailDomain',[
            'email' => 'nothing'
        ]);
        $this->assertEquals('invalid',$actual);
    }

    public function test_activate() {

        $result = array(
                    array(
                        'id' => 1,
                        'name' => 'test user',
                        'email' => 'test@rhombuspower.com',
                        'password' => 'udlgK34tmYuWF/B/b2tIBg==',
                        'status' => 'Reset_password',
                        'timestamp' => time(),
                        'account_type' => 'USER',
                        'login_attempts' => 0,
                        'login_layers' => 00111,
                        'saltiness' => '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'
                    )
                );

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info_by_email' => $result,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        

        $actual = $this->request('GET','/register/activate/?v='.bin2hex('test@rhombuspower.com').'&&s=0539328a0b153607777927146b17cd4eef11b030f43c62f2e9b507975c191563');
        
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }
    
    public function test_activate_with_invalid_link() {

        $result = array(
                    array(
                        'id' => 1,
                        'name' => 'test user',
                        'email' => 'test@rhombuspower.com',
                        'password' => 'udlgK34tmYuWF/B/b2tIBg==',
                        'status' => 'Reset',
                        'timestamp' => time(),
                        'account_type' => 'USER',
                        'login_attempts' => 0,
                        'login_layers' => 00111,
                        'saltiness' => '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'
                    )
                );

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info_by_email' => $result,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );
        

        $actual = $this->request('GET','/register/activate/?v='.bin2hex('test@rhombuspower.com').'&&s='.md5('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'));
        
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_create_account() {

        $result = array(
                    'message' => 'not_registered'
                );

                $this->request->addCallable(
                    function ($CI) use ($result) {
                        $Login_model = $this->getDouble(
                            'Login_model', [
                                'user_check' => $result,
                            ]
                        );
                        $CI->Login_model = $Login_model;

                        $Register_model = $this->getDouble(
                            'Register_model', [
                                'user_register' => true,
                            ]
                        );
                        $CI->Register_model = $Register_model;
                    }
                );

        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($actual);
    }


    public function test_create_account_account_reject() {

        $result = array(
                    'message' => 'account_rejected'
                );

                $this->request->addCallable(
                    function ($CI) use ($result) {
                        $Login_model = $this->getDouble(
                            'Login_model', [
                                'user_check' => $result,
                            ]
                        );
                        $CI->Login_model = $Login_model;

                        $Register_model = $this->getDouble(
                            'Register_model', [
                                'user_register' => true,
                            ]
                        );
                        $CI->Register_model = $Register_model;
                    }
                );

        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($actual);
    }

    public function test_create_account_login() {

        $result = array(
                    'message' => 'success'
                );

                $this->request->addCallable(
                    function ($CI) use ($result) {
                        $Login_model = $this->getDouble(
                            'Login_model', [
                                'user_check' => $result,
                            ]
                        );
                        $CI->Login_model = $Login_model;

                        $Register_model = $this->getDouble(
                            'Register_model', [
                                'user_register' => true,
                            ]
                        );
                        $CI->Register_model = $Register_model;
                    }
                );

        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($actual);
    }

    public function test_create_account_with_unauthorized_email() {

        $result = json_encode(array('result' => 'validation_failure', 'message' => array('email_check'=>'Unauthorized email domain.')));
        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@test.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($result,$actual);
    }

    public function test_create_account_with_weak_password() {

        $result = json_encode(array('result' => 'validation_failure', 'message' => array('password_strength'=>'Weak password.')));
        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => '123',
                    'password_confirmation' => '123',
                    'name' => 'Test User',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($result,$actual);
    }

    public function test_create_account_with_password_not_matching() {

        $result = json_encode(array('result' => 'validation_failure', 'message' => array('password_confirmation_check'=>'Password does not match.')));
        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($result,$actual);
    }

    public function test_create_account_with_name_check() {

        $result = json_encode(array('result' => 'validation_failure', 'message' => array('name_check'=>'A name may not contain special characters or digits.')));
        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User@!@#',
                    'account_type' => 'USER',
                    'message' => 'User'
        ]);

        $this->assertIsString($result,$actual);
    }

    public function test_create_account_with_invalid_account_type() {

        $result = json_encode(array('result' => 'validation_failure', 'message' => array('account_type_check'=>'Invalid account type.')));
        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'Undefined',
                    'message' => 'User'
        ]);

        $this->assertIsString($result,$actual);
    }

    public function test_create_account_with_form_validation() {

        $result = json_encode(array(
                    'result' => 'error'
                ));

        $res2 = true;

                $this->request->addCallable(
                    function ($CI) use ($res2) {
                        $Login_model = $this->getDouble(
                            'Login_model', [
                                'dump_user' => $res2,
                                'user_check' => TRUE,
                            ]
                        );
                        $CI->Login_model = $Login_model;

                    }
                );

        $actual = $this->request('POST','/register/create_account',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
        ]);

        $this->assertEquals($result,$actual);
    }

    public function test_reject_register() {

        $result = array('message'=>'success');

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Register_model = $this->getDouble(
                    'Register_model', [
                        'reject_register' => $result,
                    ]
                );
                $CI->Register_model = $Register_model;

            }
        );

        $actual = $this->request('POST','/register/reject_register',[
            'SiteURL' =>  '{"result":"NDQ3ZDQ2NjNiYzI2NDYxOTk3MTI0NjZjMjA3NGM0OTYzMDRhNWRlNjMzY2U0ZDliMTNkOTczYjY1OWRhNWM4ZTY2ZWRiNzgyMDNhODM4ODQ3MTA2YjZhNzY2NGNlZTE1ZjJlZmQyY2RmM2VlNGRjOTVjNTJhNTFmZDhkOWZhZWRLTHA3QzN0cTVJeVBQRkhMS01zR1MxYU9VbmFwUFFYQ01KdVZ1MzlMRGp4djk5Y1F1cXZWRndFdVNMaHIrUzBrWGlzbWUybkN3WVF2T0FaR1pSWnZwUWE5SkhvRXJqYXFhV2FBL09ZUVQyN3BDL3RZWTVja3dKKysydkZJV3RpTTlOeVFLSjBTWnR3cU5sZHVhcU9EaWc9PQ"}'
        ]);

       $this->assertJsonStringEqualsJsonString(json_encode(array('result'=>$result['message'])),$actual);
    }
}
?>