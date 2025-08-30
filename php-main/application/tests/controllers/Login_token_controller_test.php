​​​​​​​​​​​​​​​<?php
/**
 * @group base
 */
class Login_token_controller_test extends RhombusControllerTestCase
{

    public function test_generateLoginToken(){
        $result= 2;
        $result_loginToken = TRUE;
    
        $this->request->addCallable(
            function ($CI) use ($result,$result_loginToken) {
                $Login_token_model = $this->getDouble(
                    'Login_token_model', [
                        'has_login_token_layer' => $result,
                        'generate_login_token' => $result_loginToken
                    ]
                );
                $CI->Login_token_model = $Login_token_model;
            }
        );

        if(!P1_FLAG){
            $actual = $this->request('POST', '/login_token/generateLoginToken',[
                'nothing' => 'nothing'
            ]);

            // To Debug
            // echo $actual;
            $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
        }
        else{
            $this->assertTrue(true);
        }
    }

    public function test_authenticateLoginToken_invalidLength(){
    

        $actual = $this->request('POST', '/login_token/authenticateLoginToken',[
            'token' => 'D547FEGWIV7'
        ]);

        // To Debug
        // var_dump($actual);
        $this->assertSame($actual, '{"status":"failure","message":"Login token must be 16 characters long."}');
    }

    public function test_authenticateLoginToken_invalidChars(){
    

        $actual = $this->request('POST', '/login_token/authenticateLoginToken',[
            'token' => '@@47FEGWIV7Y&S3P'
        ]);

        // To Debug
        // var_dump($actual);
        $this->assertSame($actual, '{"status":"failure","message":"Login token must consist of letters and digits only."}');
    }

    public function test_authenticateLoginToken_BlockedTrue(){
    
        $result= 2;
        $result_blocked = TRUE;
    
        $this->request->addCallable(
            function ($CI) use ($result,$result_blocked) {
                $Login_token_model = $this->getDouble(
                    'Login_token_model', [
                        'has_login_token_layer' => $result,
                    ]
                );
                $CI->Login_token_model = $Login_token_model;

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'enforce_block_rules' => $result_blocked,
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $actual = $this->request('POST', '/login_token/authenticateLoginToken',[
            'token' => 'D547FEGWIV7YUS3P'
        ]);

        // To Debug
        // var_dump($actual);
        $this->assertSame($actual, '{"status":"max_attempts_reached","message":"Maximum failed login attempts reached."}');
    }

    public function test_authenticateLoginToken_invalidToken(){
    
        $result= 2;
        $result_blocked = FALSE;
        $invalid_token = 'D547FEGWIV7YUS3P';
        $update_login = TRUE;
        $dump_user = TRUE;
    
        $this->request->addCallable(
            function ($CI) use ($result,$result_blocked,$invalid_token,$update_login,$dump_user) {
                $Login_token_model = $this->getDouble(
                    'Login_token_model', [
                        'has_login_token_layer' => $result,
                        'get_login_token' => $invalid_token
                    ]
                );
                $CI->Login_token_model = $Login_token_model;

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'enforce_block_rules' => $result_blocked,
                        'update_login_attempts_by_id' => $update_login,
                        'dump_user' => $dump_user
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $actual = $this->request('POST', '/login_token/authenticateLoginToken',[
            'token' => 'D547FEGWIV7YUSSS'
        ]);

        // To Debug
        // var_dump($actual);
        $this->assertSame($actual, '{"status":"failure","message":"Invalid token"}');
    }

    public function test_authenticateLoginToken_validToken(){
    
        $result= 2;
        $result_blocked = FALSE;
        $invalid_token = 'D547FEGWIV7YUS3P';
        $update_login = TRUE;
        $dump_user = TRUE;
        $login_success = TRUE;
        $delete_token = TRUE;
    
        $this->request->addCallable(
            function ($CI) use ($result,$result_blocked,$invalid_token,$update_login,$dump_user,$login_success,$delete_token) {
                $Login_token_model = $this->getDouble(
                    'Login_token_model', [
                        'has_login_token_layer' => $result,
                        'get_login_token' => $invalid_token,
                        'delete_login_token' => $delete_token
                    ]
                );
                $CI->Login_token_model = $Login_token_model;

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'enforce_block_rules' => $result_blocked,
                        'update_login_attempts_by_id' => $update_login,
                        'dump_user' => $dump_user,
                        'user_login_success' => $login_success
                    ]
                );
                $CI->Login_model = $Login_model;
            }
        );

        $actual = $this->request('POST', '/login_token/authenticateLoginToken',[
            'token' => 'D547FEGWIV7YUS3P'
        ]);

        // To Debug
        // var_dump($actual);
        $this->assertSame($actual, '{"status":"success","message":"Login token authenticated successfully."}');
    }
}

?>