<?php 
/**
 * @group base
 * @group controller
 */
class Account_manager_controller_test extends RhombusControllerTestCase
{

    public function test_index_isSSO(){

        $sso_data = [
            [
                "id"    => "1",
                "email" => "test@rhombuspower.com",
                "status"  => 'Active',
                "account_type"  => 'User',
                "admin_expiry"  => '12/04/2022',
            ] 
        ];
        
        $account_data = [
            [
                "id"    => "1",
                "email" => 2020,
                "account_type"  => 11,
                "admin_expiry"  => 50,
                "status"  => 52,
            ] 
        ];
        
        $this->request->addCallable(
            function ($CI) use ($sso_data, $account_data) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'isSSOAvailable' => $sso_data,
                        'getAccount' => $account_data
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;

                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'get_app_users' => [
                            'test1@rhombuspower.com' => 'Knowledge Graph', 
                            'test2@rhombuspower.com' => 'Competition'
                        ]
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/index',
            [
                'temp' => 'temp',
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }

    public function test_index_isNotSSO(){
       

        $sso_data = [
            [
                "id"    => "1",
                "email" => "test@rhombuspower.com",
                "status"  => 'Active',
                "account_type"  => 'User',
                "admin_expiry"  => '12/04/2022',
            ] 
        ];
        
        $account_data = [
            [
                "id"    => "1",
                "email" => 2020,
                "account_type"  => 11,
                "admin_expiry"  => 50,
                "status"  => 52,
            ] 
        ];

        // Add Mock user session
        $this->request->addCallable(
            function ($CI) {
                $session_data = array(
                    'email' => 'unit_tester123@rhombuspower.com',
                    'name' => 'Unit Tester',
                    'account_type' => 'USER',
                    'timestamp' => 1609459200,
                    'profile_image' => NULL,
                    'id' => 1
                );

                $CI->session->set_userdata('logged_in', $session_data);
            }
        );
        
        $this->request->addCallable(
            function ($CI) use ($sso_data, $account_data) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'isSSOAvailable' => $sso_data,
                        'getAccount' => $account_data
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/index',
            [
                'temp' => 'temp',
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }


    public function test_index_isSSOAccountFalse(){

        $sso_data = false;
        
        $account_data = [
            [
                "id"    => "1",
                "email" => 2020,
                "account_type"  => 11,
                "admin_expiry"  => 50,
                "status"  => 52,
            ] 
        ];

        // Add Mock user session
        $this->request->addCallable(
            function ($CI) {
                $session_data = array(
                    'email' => 'unit_tester1@rhombuspower.com',
                    'name' => 'Unit Tester',
                    'account_type' => 'USER',
                    'timestamp' => 1609459200,
                    'profile_image' => NULL,
                    'id' => 1
                );

                $CI->session->set_userdata('logged_in', $session_data);
            }
        );
        
        $this->request->addCallable(
            function ($CI) use ($sso_data, $account_data) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'isSSOAvailable' => $sso_data,
                        'getAccount' => $account_data
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/index',
            [
                'temp' => 'temp',
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }

    public function test_getAccountData_isSSO(){

        $sso_data = [
            [
                "id"    => "1",
                "email" => "test@rhombuspower.com",
                "status"  => 'Active',
                "account_type"  => 'User',
                "admin_expiry"  => '12/04/2022',
                "active_apps"=>[],"requested_apps"=>[]
            ] 
        ];
        
        $account_data = [
            [
                "id"    => "1",
                "email" => 2020,
                "account_type"  => 11,
                "admin_expiry"  => 50,
                "status"  => 52,
            ] 
        ];

        
        $this->request->addCallable(
            function ($CI) use ($sso_data, $account_data) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'isSSOAvailable' => $sso_data,
                        'getAccount' => $account_data
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;

                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'get_app_users' => [
                            'test1@rhombuspower.com' => 'Knowledge Graph', 
                            'test2@rhombuspower.com' => 'Competition'
                        ]
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/getAccountData',
            [
                'temp' => 'temp',
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertEquals(json_encode($sso_data), $actual);
    }

    public function test_getAccountData_isNotSSO(){

        $sso_data = false;

        $account_data = [
            [
                "id"    => "1",
                "email" => 2020,
                "account_type"  => 11,
                "admin_expiry"  => 50,
                "status"  => 52,
            ] 
        ];

        
        $this->request->addCallable(
            function ($CI) use ($sso_data, $account_data) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'isSSOAvailable' => $sso_data,
                        'getAccount' => $account_data
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/getAccountData',
            [
                'temp' => 'temp',
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertEquals(json_encode($account_data), $actual);
    }

    public function test_updateUser_User(){

        $updateUser = array(
			'message' => 'the_message_1'
		);

        $this->request->addCallable(
            function ($CI) use ($updateUser) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'updateUser' => $updateUser
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/updateUser',
            [
                'AccountType' => 'USER',
                'ExpiryDate' => '12/04/2022',
                'Id' => 1
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }

    public function test_updateUser_Admin(){

        $updateUser = array(
			'message' => 'the_message_1'
		);

        $this->request->addCallable(
            function ($CI) use ($updateUser) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'updateUser' => $updateUser
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/updateUser',
            [
                'AccountType' => 'Admin',
                'ExpiryDate' => '12/04/2022',
                'Id' => 1
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }


    public function test_updateUser_validationFalse(){

        $updateUser = TRUE;

        $this->request->addCallable(
            function ($CI) use ($updateUser) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'updateUser' => $updateUser
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/updateUser',
            [
                'AccountType' => 'USER',
                'ExpiryDate' => '12/04/2022'
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }

    public function test_updateUser_validationTrue(){

        $updateUser = TRUE;

        $this->request->addCallable(
            function ($CI) use ($updateUser) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'updateUser' => $updateUser
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/updateUser',
            [
                'AccountType' => 'admin',
                'ExpiryDate' => '12/04/2022'
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }

    public function test_updateUser_validationTrue_validateUser_fail(){

        $updateUser = TRUE;

        $this->request->addCallable(
            function ($CI) use ($updateUser) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'updateUser' => $updateUser
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;

                $form_validation = $this->getDouble(
                    'Rhombus_Form_validation', [
                        'run_rules' => 'success'
                    ]
                );
                $CI->form_validation = $form_validation;
            }
        );

        $actual = $this->request('POST', '/account_manager/updateUser',
            [
                'AccountType' => 'admin',
                'ExpiryDate' => '12/04/2022'
            ]
        );

        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }

    public function test_deleteAccount(){

        $result = array("message"=>"success");
        

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'deleteAccount' => $result
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/deleteAccount',
            [
                'id' => 1,
                'email' => 'test@rhombuspower.com',
                'type' => 'user',

            ]
        );

        $expected = json_encode(array("result"=>"success"));
        // To Debug
        // echo $actual;
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }


    public function test_encrypt_data(){

        $result = TRUE;
        

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'activateSSOUSer' => $result
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/encrypt_data',
            [
                'data' => [
                    
                    "id"=> 1,
                    "email"=> 'test@rhombuspower.com',
                    "type"=> "admin_verify"
                    ]
             ]
        );
        // To Debug
        // echo $actual;
        $this->assertIsString($actual);
    }


    public function test_registerSSOUser_Updated(){

        $result = TRUE;
        

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'activateSSOUSer' => $result
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/registerSSOUser',
            [
                'id' => 1,
                'email' => 'test@rhombuspower.com',
                'account_type' => 'account_type'
            ]
        );

        $expected = '{"status":"success","message":"Successfully registered"}';
        // To Debug
        // echo $actual;
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function test_registerSSOUser_notUpdated(){

        $result = FALSE;
        

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Account_manager_model = $this->getDouble(
                    'Account_manager_model', [
                        'activateSSOUSer' => $result
                    ]
                );
                $CI->Account_manager_model = $Account_manager_model;
            }
        );

        $actual = $this->request('POST', '/account_manager/registerSSOUser',
            [
                'id' => 1,
                'email' => 'test@rhombuspower.com',
                'account_type' => 'account_type'
            ]
        );

        $expected = '{"status":"failure","message":"Failed to register"}';
        // To Debug
        // echo $actual;
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
}
?>
