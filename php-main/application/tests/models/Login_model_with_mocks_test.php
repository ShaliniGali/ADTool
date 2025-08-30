<?php 
/**
 * @group base
 */
class Login_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Login_model();

        // Inject mock object into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

        // Inject mock object into the session
        $this->obj->session = $this->getSessionMock();

        $this->obj->Login_model = $this->obj;
    }

    // Test for has_login_token_layer with data
    // 
    public function test_get_max_login_attempts_isInt() 
    {
     
        // Call and test
        $actual = $this->obj->get_max_login_attempts();

        $this->assertIsInt($actual);
    }

    // Test for user_login_success with data
    // 
    public function test_user_login_success_checkResultWithOnlyEmailStatus()
    {
        $result_only_email = [ 
            [
                # id, name, email, password, status, timestamp, account_type, login_attempts, login_layers, image, saltiness
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '',
                'subapp_id' => '2',
                'saltiness' => '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'

            ]
        ];

        $user = [
            [
                'email' => 'test@email.com'
            ]
        ];
        $status = 'Only_email';


        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result_only_email);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => TRUE 
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE 
            ]
        );
        $this->obj->Generic = $generic;

        $actual = $this->obj->user_login_success($user, $status);

        $this->assertTrue($actual);

    }

    // Test for user_login_success with notifications model
    // 
    public function test_user_login_success_checkResultNotificationClassSet()
    {

        $user = [
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'
            ]
        ];
        $status = 'random status';

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => TRUE 
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

      /*  $notifications_model = $this->getDouble(
            'Notifications_model', [
                'get_user_notification' => TRUE 
            ]
        );
        $this->obj->Notifications_model = $notifications_model;*/

        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn(array());
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->user_login_success($user, $status);

        $this->assertTrue($actual);

    }

    // Test for get_user_id with data
    //
    public function test_get_user_id_isInt()
    {

        $result = [ 
            [
                'id' => 1
            ]
        ];

        $username = 'username';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->get_user_id($username);
        $this->assertIsInt($actual);

    }

    // Test for get_user_id with data
    //
    public function test_get_user_id_isNull()
    {

        $result = [];

        $username = 'username';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->get_user_id($username);
        $this->assertNull($actual);

    }

    // Test for enforce_block_rules with data
    //
    public function test_enforce_block_rules_checkResultWithMoreLoginAttemptsThanMax()
    {
        $result = [
            [
                'login_attempts' => 123232312321000,
                'status' => 'Active'
            ]
        ];

        $id = 1;
        $type = 'type';
        $message = 'message';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE 
            ]
        );
        $this->obj->Generic = $generic;

        

        $actual = $this->obj->enforce_block_rules($id, $type, $message);

        $this->assertTrue($actual);

    }

    // Test for enforce_block_rules with data
    //
    public function test_enforce_block_rules_checkResultWithBlockedAccount()
    {
        $result = [
            [
                'login_attempts' => 3,
                'status' => 'Blocked'
            ]
        ];

        $id = 1;
        $type = 'type';
        $message = 'message';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->enforce_block_rules($id, $type, $message);

        $this->assertTrue($actual);

    }

    // Test for enforce_block_rules with data
    //
    public function test_enforce_block_rules_checkResultIsIntAndEquals()
    {
        $result = [
            [
                'login_attempts' => 3,
                'status' => 'Active'
            ]
        ];

        $id = 1;
        $type = 'type';
        $message = 'message';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->enforce_block_rules($id, $type, $message);

        $expected = $result[0]['login_attempts'];

        $this->assertIsInt($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for get_undeleted_user with data
    //
    public function test_get_undeleted_user_checkResult()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

            
        $actual = $this->obj->get_undeleted_user($username);
        $this->assertIsArray($actual);
        $this->assertEquals($result[0], $actual);

    }

    // Test for get_undeleted_user with data
    //
    public function test_get_undeleted_user_isNull()
    {
        $result = [];


        $username = 'username';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->get_undeleted_user($username);
        $this->assertNull($actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndMaxLoginAttempts()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 1231232199999, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'message' => 'account_blocked'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndResetPasswordStatus()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Reset_password', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'message' => 'force_reset_password'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndCorrectEncryptedPasswordAndRejectedStatus()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Rejected', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'layers' => str_split($result[0]['login_layers']),
            'id' => $result[0]['id'],
            'user' => $username,
            'message' => 'account_rejected'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndCorrectEncryptedPasswordAndRegistrationPendingStatus()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Registration_pending', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'layers' => str_split($result[0]['login_layers']),
            'id' => $result[0]['id'],
            'user' => $username,
            'message' => 'registration_pending_exist'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndCorrectEncryptedPasswordAndLoginLayerStatus()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Login_layer', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $google_2fa_model = $this->getDouble(
            'Google_2FA_model', [
                'add_google_2fa_private_key' => TRUE,
                'has_google_2fa_layer' => TRUE
            ]
        );
        $this->obj->Google_2FA_model = $google_2fa_model;

        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'layers' => $result[0]['login_layers'],
            'id' => $result[0]['id'],
            'user' => $username,
            'message' => 'register_login_layer'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndCorrectEncryptedPasswordAndActiveStatusAndExpired()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => -1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'layers' => str_split($result[0]['login_layers']),
            'id' => $result[0]['id'],
            'user' => $username,
            'message' => 'account_blocked'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    // 
    //
    public function test_user_check_checkResultWithUserExistsAndCorrectEncryptedPasswordAndActiveStatusAndTFALayerActive()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148123, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchConstant(
			'RHOMBUS_TFA_LAYER',
			'TRUE',
			Login_model::class . '::login_attempt_in_user_check'
        );

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'layers' => str_split($result[0]['login_layers']),
            'id' => $result[0]['id'],
            'user' => $username,
            'message' => 'require_login_layer'
        ];

        $this->assertIsArray($actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndCorrectEncryptedPasswordAndActiveStatusSuccess()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148123, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'subapp_id' => 2, 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturnOnConsecutiveCalls(TRUE, TRUE);
        
        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        /*$notifications_model = $this->getDouble(
            'Notifications_model', [
                'get_user_notification' => TRUE 
            ]
        );
        $this->obj->Notifications_model = $notifications_model;
*/
        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => TRUE 
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'layers' => str_split($result[0]['login_layers']),
            'id' => $result[0]['id'],
            'user' => $username,
            'message' => 'success'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndIncorrectPasswordAndLoginAttemptsMoreThanResetPasswordPromptAttempts()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 5, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

            $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => '123'
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'message' => 'reset_password'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserExistsAndIncorrectPasswordAndFailedLoginAttempt()
    {
        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 1, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);
        
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

            $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => '123'
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'message' => 'failed'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for user_check with data
    //
    public function test_user_check_checkResultWithUserDoesntExist()
    {
        $result = NULL;

        $username = 'username';
        $password = 'password';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $actual = $this->obj->user_check($username, $password);

        $expected = [
            'message' => 'not_registered'
        ];

        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

        // Test for check_admin_expiry with data
    //
    public function test_check_admin_expiry_checkResultWithNoSuperAdmin()
    {

        $users_details = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => FALSE,
                'getleastprivilege' => ['type' => 'random']
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE 
            ]
        );
        $this->obj->Generic = $generic;

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $actual = $this->obj->check_admin_expiry($users_details);

        $this->assertTrue($actual);
    }

    // Test for check_admin_expiry with data
    //
    public function test_check_admin_expiry_checkResultWithUserTypes()
    {

        $users_details = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => 'ZSfIxeSmrnNmbKsISuijtK2dWaLqi+CBidi7Gfe3GkH0b/EV45YwfhGmna0DCA+RSxWOpS/hhY3tAicP+ThrgntHW3QWeOFlclhe5kj1Lub4MWb4zhuMzJyxdCqGQWAcjkiwN3WDf7ZG9BOxu5ynmWwcK4w1fthddvJ+Wfo5pzfKcTj7aXtr9nvszVf2+5nM4BGGbvzV/xnJdLhlFBWHnBzqwMNCGT7V991re9GutA3uh0+UkcnvX+FV1R+yn5wKBM/KuE7AMY66q6O6wBO4KY0Jt6pwCaWfOexn48NJLlQpMcZ1zNQsBdC843vCOXVfh8ARrPHWS/5r5vmOHhExOk+9DkAC4l03l6Lajr9/CrlosISm3/6bvDOGjub52V6ib2DKzfLOhUojeEesjLtphYYKI+g6Z5z8aTWQEnQMWkOWMCKacHZqUrlzR5n+ptrURPShVHEBmfkz/6Bq4pgNU4AN9lXLc9W/TqDAovMBGsGtwx1zs/NUtEIyqnA30MZaoanprQtEg4S/EmXpjwY1x9ZcZAzTuJhfXqnunKCRRnhQ7nPaWJCs+E8CWZrH2iAmmy6iiua5TQkaKWqrhdVJcs+jAGOw2SGS9lA0qwMvPduh3IJlrrXCESi5+LmTVAA0eeKLe9f6Zk5rhQLHbnU7JW6jlvtVjnwWWBZb8kOSVYdXacoDpan0/FVPQt6qLkaC9VVu5gB1xxCacemDbCNEHJyRVa4pVxrB7A4BShl4mapGR/XLFum3Si2ZTZAmP6pgjBUuqjGn2Ij/uE3jjNOLL/fj7YXN4+egTVeiB1kcE6gl8CdVSo4JZJJTO2/42hF8lXHaXs9KSNMOlbAPoH8JFQOLNUR5QY8lSj5z8uufNPjAurFWd5EzTjGFW3Pa/wEY5VSCzBjNeTFe+2AXYFpaJyhgtdQpigVKsnEFg6zQIYaCqnSjJb6OpQnLuAR/z3C++9EbMnbaq1Q4Efc1odeIkkaRcOy4ERudwimSRwmtMvgxMtCacQb3Z/dqhTOHrsml2fWKkkCVrkDyoOEC65li3BqwfFxcn2iYFw/JmFd7ozqwPiOvfpqfL+OaCe0de9/A/CcQLaC/bQOCrRRr4IFnE5EgUsqHnrxcMlCoRJelWEsykaWcIWLqc4nVX6zCybZMdiP9itsSMB0IOIw6PRYPb76vWv71r5ReKAx/PLf3bc4Ixe+QtUHJIYRxKYDEff/qUBZAmUyv+r3AX6FrwYoXnE/GiO6oxKpHY5bc2MKgJMAN75xwEbWkeXaKnfg/WawwtbhU2lyl9G6c0Z8qyeeF39vl3m0wmzy9ImKQy6EmYu02P9hp9LXOl5PVrQ/zpIvf5IPkgFO0Mw+KHBoHNEuYdXzJv0mNkPk1H2saEisNtsgYtVfdSrLoT+VZDtftp2KAsxH1op9GSpwTtNUdxCVIBpYN6CasGm6dvE6tYMOBpmvsLD+uUDVtKhW0YRtCSVVnNU0f5xHbOt0JBHeA5FNUU7EYsvxdM0LbYBeasHVLMezJJmS+D764jTaVusJnjox+RNI7QL15Bgpn+quZX8OJQJBPPT5jqeSrlSLCY932fAJcL6lUhe0sMdnI+2pUj55AZPyfT2jcUzmZ+Vw1uVF2PUreSjBJYZlzvFS+sobnRmyFn/EzWUN4Q8CgNbaX9fTx0mvGb32EdrFk3Hzkj0CnmllXL92EKUtzoO/9C3LTLKsxhldQmMG5UgVm4BX1iG6Szw03Xwu4zlroT8paErno/Kg+91MEVlB68p+qRmCOvJ6JBM78aQ7kKFxp9rbHzMKybuguat+J6QFZ1H1BxoC56L6mfNK1eAD4wbzq8g7gDssVkW6860gtm3W583gVHk+bJEMSb9TLb2t80eFy8xGSUHzmqigr/POxuVYy+N1UD96UroOZPmYX99Tri5NiHVZOBqnNPi/nF0/VbOZ4WYykxaiPwAQBSW+SkFh7Ed+hdedmjdvYe1AcuIbmgTtRKjiME3vYc0GU94k6zi4SpoC6OkcRZC5barzB8WxSkYn9UIp1SRkS2H1qcD197GMPAk0X0Fh7ZBzCIvEHVwHRgNGuQLtZCE4pTqTOel99Rrtk/TmEmWqrs5pHKeLGRQE7AbZweA827oXrpkrMB7za6w9cv39Jc878v/iAFbzzBc5WfcQcHO9xWwfyglFPG29x80SMBczeCsds39WOAkaBdF0+TYwppNeuDRkDaTYVz5GKxXk+E+82xanrvGEMCdTDFsw/yc5Btwe0ImR0b0nSvlOWj4s/Y7cGj/Gm1pbsnjSJI5UEjXUHT9M4MhAGhfesHmjM4tp4FaNj4TVTO4qNWJrsWNJjoEsD7Id9tW7PjoqpLayE/4bOV2Qx80eHYPjeLIn+vZ9ckvNBAbqocRRfEKwQYWM5iH0PR4ABc4uQ3aZcbp3zwp7A2SVg9sA+fp8Nxzfj/bfC5u2PFJKvedUQ3i+tXmLTt24eLwjDY5QYi0B1r4p6EE6Bt2/Ulu5ST4L/XqC9Xx50DsTAlAS+wuDuaMJrk4TrrvvNUInaq4ivdRz3m+8OJJbbD4L0cYxZbgudDz4AMISdwPZYGMIV/X/cmvm5GVa6RIfP4rVQMvLJfpsJV6SFTORhQ5/YYiNCDARG07WoQxoByYojuelEdLGfpikTmf4LqPrh8qpBCYCQWWQWJkcmbrUYdTP5LhAipoSDC++FG/D3DiNA47XZs/p/DOG76d1NFgF4UIqKQb3c6sXtXog='

            ]
        ];


        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchConstant(
			'USER_TYPE',
			['use-db' => TRUE],
			Login_model::class . '::check_admin_expiry'
        );

        $actual = $this->obj->check_admin_expiry($users_details);

        $this->assertNull($actual);
    }

    // Test for check_admin_expiry with data
    //
    public function test_check_admin_expiry_checkResultWithNoSuperAdminThrowsException()
    {

        $users_details = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => FALSE,
                'getleastprivilege' => ['type' => 'random']
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $users_keys_model = $this->getDouble(
            'Users_keys_model', [
                'get_admin_expiry_date' => '1000-01-01'
            ]
        );
        $this->obj->Users_keys_model = $users_keys_model;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE 
            ]
        );
        $this->obj->Generic = $generic;

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn($this->throwException(new Exception()));

        $actual = $this->obj->check_admin_expiry($users_details);

        $this->assertFalse($actual);
    }

    // Test for dump_user with data
    //
    public function test_dump_user()
    {
        $type = '';
        $new_info = [];
            
        $actual = $this->obj->dump_user($type, $new_info);

        $this->assertNull($actual);
    }

    // Test for user_info with data
    //
    public function test_user_info()
    {

        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
            
        $actual = $this->obj->user_info($id);
        $this->assertIsArray($actual);
        $this->assertEquals($result, $actual);

    }

    // Test for user_info_by_email with data
    //
    public function test_user_info_by_email()
    {

        $result = [ 
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $email = 'email';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->user_info_by_email($email);
        $this->assertIsArray($actual);
        $this->assertEquals($result, $actual);

    }

    // Test for get_key_info with data
    //
    public function test_get_key_info()
    {
        $result = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_key_info($id);
        $this->assertIsArray($actual);
        $this->assertEquals($result, $actual);

    }

    // Test for get_account_status with data
    //
    public function test_get_account_status()
    {
        $result = [ 
            [
                'status' => 'Active'
            ]
        ];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);


        $actual = $this->obj->get_account_status($id);

        $expected = $result[0]['status'];

        $this->assertIsString($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for block_account with data
    //
    public function test_block_account()
    {

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $id = 'id';
        $oldStatus = 'Blocked';

        $actual = $this->obj->block_account($id, $oldStatus);

        $this->assertNull($actual);

    }

    // Test for update_login_attempts_by_id with data
    //
    public function test_update_login_attempts_by_id_checkResultWithResetFalse()
    {

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $id = 'id';

        $actual = $this->obj->update_login_attempts_by_id($id);
        $this->assertNull($actual);

    }

    // Test for update_login_attempts_by_id with data
    //
    public function test_update_login_attempts_by_id_checkResultWithResetTrue()
    {

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $id = 'id';
        $reset = TRUE;

        $actual = $this->obj->update_login_attempts_by_id($id, $reset);
        $this->assertNull($actual);

    }

    public function test_update_login_attempts_by_id_checkResultWithResetTrueAndNonNullStatus()
    {

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $id = 'id';
        $reset = TRUE;
        $status = 'status';

        $actual = $this->obj->update_login_attempts_by_id($id, $reset, $status);
        $this->assertNull($actual);

    }

    // Test for check_recovery_key with data
    //
    public function test_check_recovery_key_checkResultWithSuccess()
    {
        $result = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->check_recovery_key($id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for check_recovery_key with data
    //
    public function test_check_recovery_key_checkResultWithFailure()
    {
        
        $result = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": []}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->check_recovery_key($id);
        $expected = [
            'message' => 'failure'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for recovery_code_login with data
    //
    public function test_recovery_code_login_checkResultWithLessThanMaxLoginAttemptsAndSuccess()
    {
        $result_info = [
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_info, $result_rks, $result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $rk = '6f06526e28b51175';
        $id = 'id';

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => TRUE 
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

/*        $notifications_model = $this->getDouble(
            'Notifications_model', [
                'get_user_notification' => TRUE 
            ]
        );
        $this->obj->Notifications_model = $notifications_model;*/

        $actual = $this->obj->recovery_code_login($rk, $id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for recovery_code_login with data
    //
    public function test_recovery_code_login_checkResultWithLessThanMaxLoginAttemptsAndFailure()
    {
        $result_info = [
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_info, $result_rks);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(FALSE);

        $rk = '6f06526e28b51175';
        $id = 'id';

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => TRUE 
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

      /*  $notifications_model = $this->getDouble(
            'Notifications_model', [
                'get_user_notification' => TRUE 
            ]
        );
        $this->obj->Notifications_model = $notifications_model;*/

        $actual = $this->obj->recovery_code_login($rk, $id);
        $expected = [
            'message' => 'failure'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);
    }
    
    // Test for recovery_code_login with data
    //
    public function test_recovery_code_login_checkResultWithLessThanMaxLoginAttemptsAndFailureFromNoRecoveryKeyFound()
    {
        $result_info = [
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 0, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_info, $result_rks);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $rk = 'bad key';
        $id = 'id';

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
        $this->obj->session->method('set_userdata')->willReturn(TRUE);

        $useraccounttype = $this->getDouble(
            'UserAccountType', [
                'checkSuperAdmin' => TRUE 
            ]
        );
        $this->obj->useraccounttype = $useraccounttype;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

       /* $notifications_model = $this->getDouble(
            'Notifications_model', [
                'get_user_notification' => TRUE 
            ]
        );
        $this->obj->Notifications_model = $notifications_model;*/

        $actual = $this->obj->recovery_code_login($rk, $id);
        $expected = [
            'message' => 'failure',
            'login_attempts' => 6
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);
    }
    
    // Test for recovery_code_login with data
    //
    public function test_recovery_code_login_checkResultWithMoreThanMaxLoginAttempts()
    {
        $result_info = [
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_info, $result_rks);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $rk = 'bad key';
        $id = 'id';

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;


        $actual = $this->obj->recovery_code_login($rk, $id);
        $expected = [
            'message' => 'account_blocked'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);
    }

    // Test for reset_recovery_code with data
    //
    public function test_reset_recovery_code_checkResultWithUserThatExists()
    {
        $result_info = [
            [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => 'ZSfIxeSmrnNmbKsISuijtK2dWaLqi+CBidi7Gfe3GkH0b/EV45YwfhGmna0DCA+RSxWOpS/hhY3tAicP+ThrgntHW3QWeOFlclhe5kj1Lub4MWb4zhuMzJyxdCqGQWAcjkiwN3WDf7ZG9BOxu5ynmWwcK4w1fthddvJ+Wfo5pzfKcTj7aXtr9nvszVf2+5nM4BGGbvzV/xnJdLhlFBWHnBzqwMNCGT7V991re9GutA3uh0+UkcnvX+FV1R+yn5wKBM/KuE7AMY66q6O6wBO4KY0Jt6pwCaWfOexn48NJLlQpMcZ1zNQsBdC843vCOXVfh8ARrPHWS/5r5vmOHhExOk+9DkAC4l03l6Lajr9/CrlosISm3/6bvDOGjub52V6ib2DKzfLOhUojeEesjLtphYYKI+g6Z5z8aTWQEnQMWkOWMCKacHZqUrlzR5n+ptrURPShVHEBmfkz/6Bq4pgNU4AN9lXLc9W/TqDAovMBGsGtwx1zs/NUtEIyqnA30MZaoanprQtEg4S/EmXpjwY1x9ZcZAzTuJhfXqnunKCRRnhQ7nPaWJCs+E8CWZrH2iAmmy6iiua5TQkaKWqrhdVJcs+jAGOw2SGS9lA0qwMvPduh3IJlrrXCESi5+LmTVAA0eeKLe9f6Zk5rhQLHbnU7JW6jlvtVjnwWWBZb8kOSVYdXacoDpan0/FVPQt6qLkaC9VVu5gB1xxCacemDbCNEHJyRVa4pVxrB7A4BShl4mapGR/XLFum3Si2ZTZAmP6pgjBUuqjGn2Ij/uE3jjNOLL/fj7YXN4+egTVeiB1kcE6gl8CdVSo4JZJJTO2/42hF8lXHaXs9KSNMOlbAPoH8JFQOLNUR5QY8lSj5z8uufNPjAurFWd5EzTjGFW3Pa/wEY5VSCzBjNeTFe+2AXYFpaJyhgtdQpigVKsnEFg6zQIYaCqnSjJb6OpQnLuAR/z3C++9EbMnbaq1Q4Efc1odeIkkaRcOy4ERudwimSRwmtMvgxMtCacQb3Z/dqhTOHrsml2fWKkkCVrkDyoOEC65li3BqwfFxcn2iYFw/JmFd7ozqwPiOvfpqfL+OaCe0de9/A/CcQLaC/bQOCrRRr4IFnE5EgUsqHnrxcMlCoRJelWEsykaWcIWLqc4nVX6zCybZMdiP9itsSMB0IOIw6PRYPb76vWv71r5ReKAx/PLf3bc4Ixe+QtUHJIYRxKYDEff/qUBZAmUyv+r3AX6FrwYoXnE/GiO6oxKpHY5bc2MKgJMAN75xwEbWkeXaKnfg/WawwtbhU2lyl9G6c0Z8qyeeF39vl3m0wmzy9ImKQy6EmYu02P9hp9LXOl5PVrQ/zpIvf5IPkgFO0Mw+KHBoHNEuYdXzJv0mNkPk1H2saEisNtsgYtVfdSrLoT+VZDtftp2KAsxH1op9GSpwTtNUdxCVIBpYN6CasGm6dvE6tYMOBpmvsLD+uUDVtKhW0YRtCSVVnNU0f5xHbOt0JBHeA5FNUU7EYsvxdM0LbYBeasHVLMezJJmS+D764jTaVusJnjox+RNI7QL15Bgpn+quZX8OJQJBPPT5jqeSrlSLCY932fAJcL6lUhe0sMdnI+2pUj55AZPyfT2jcUzmZ+Vw1uVF2PUreSjBJYZlzvFS+sobnRmyFn/EzWUN4Q8CgNbaX9fTx0mvGb32EdrFk3Hzkj0CnmllXL92EKUtzoO/9C3LTLKsxhldQmMG5UgVm4BX1iG6Szw03Xwu4zlroT8paErno/Kg+91MEVlB68p+qRmCOvJ6JBM78aQ7kKFxp9rbHzMKybuguat+J6QFZ1H1BxoC56L6mfNK1eAD4wbzq8g7gDssVkW6860gtm3W583gVHk+bJEMSb9TLb2t80eFy8xGSUHzmqigr/POxuVYy+N1UD96UroOZPmYX99Tri5NiHVZOBqnNPi/nF0/VbOZ4WYykxaiPwAQBSW+SkFh7Ed+hdedmjdvYe1AcuIbmgTtRKjiME3vYc0GU94k6zi4SpoC6OkcRZC5barzB8WxSkYn9UIp1SRkS2H1qcD197GMPAk0X0Fh7ZBzCIvEHVwHRgNGuQLtZCE4pTqTOel99Rrtk/TmEmWqrs5pHKeLGRQE7AbZweA827oXrpkrMB7za6w9cv39Jc878v/iAFbzzBc5WfcQcHO9xWwfyglFPG29x80SMBczeCsds39WOAkaBdF0+TYwppNeuDRkDaTYVz5GKxXk+E+82xanrvGEMCdTDFsw/yc5Btwe0ImR0b0nSvlOWj4s/Y7cGj/Gm1pbsnjSJI5UEjXUHT9M4MhAGhfesHmjM4tp4FaNj4TVTO4qNWJrsWNJjoEsD7Id9tW7PjoqpLayE/4bOV2Qx80eHYPjeLIn+vZ9ckvNBAbqocRRfEKwQYWM5iH0PR4ABc4uQ3aZcbp3zwp7A2SVg9sA+fp8Nxzfj/bfC5u2PFJKvedUQ3i+tXmLTt24eLwjDY5QYi0B1r4p6EE6Bt2/Ulu5ST4L/XqC9Xx50DsTAlAS+wuDuaMJrk4TrrvvNUInaq4ivdRz3m+8OJJbbD4L0cYxZbgudDz4AMISdwPZYGMIV/X/cmvm5GVa6RIfP4rVQMvLJfpsJV6SFTORhQ5/YYiNCDARG07WoQxoByYojuelEdLGfpikTmf4LqPrh8qpBCYCQWWQWJkcmbrUYdTP5LhAipoSDC++FG/D3DiNA47XZs/p/DOG76d1NFgF4UIqKQb3c6sXtXog='

            ]
        ];
        $result_rks = [ 
            [
                
                'id' => 1, 
                'user_id' => 1, 
                'google_key' => NULL,
                'yubi_key' => NULL,
                'cac_key' => NULL,
                'recovery_key' => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}', 
                'login_token' => NULL, 
                'admin_expiry' => NULL
            ]
        ];


        $keys = '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}';
        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_rks, $result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;


        $actual = $this->obj->reset_recovery_code($keys, $id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for reset_recovery_code with data
    //
    public function test_reset_recovery_code_checkResultWithUserThatDoesntExist()
    {
        $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [];


        $keys = '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}';
        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_rks, $result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;


        $actual = $this->obj->reset_recovery_code($keys, $id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for reset_recovery_code with data
    //
    public function test_reset_recovery_code_checkResultWithThrownException()
    {
        $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [];


        $keys = '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}';
        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result_rks, $result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn($this->throwException(new Exception()));



        $actual = $this->obj->reset_recovery_code($keys, $id);
        $expected = [
            'message' => 'failure'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for send_reset_password_details with data
    //
    public function test_send_reset_password_details_checkResultWithResetPasswordStatus()
    {

           $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Reset_password', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;


        $actual = $this->obj->send_reset_password_details($id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for send_reset_password_details with data
    //
    public function test_send_reset_password_details_checkResultWithActiveStatus()
    {

        $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $result_rks = [];

        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

  
        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;


        $actual = $this->obj->send_reset_password_details($id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for update_password with data
    //
    public function test_update_password_checksNewPasswordMatchesOldPassword()
    {

        $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $password = 'password';
        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => $result_info[0]['password']
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $actual = $this->obj->update_password($password, $id);
        $expected = [
            'message' => 'password_used'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    // Test for update_password with data
    //
    public function test_update_password_checksNewPasswordDoesntMatchOldPassword()
    {

        $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $password = 'password';
        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);


        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => 'decrypted password',
                'encrypt' => [
                    'password' => 'encrypted password',
                    'salt' => 'salt'
                ]
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $generic = $this->getDouble(
            'Generic', [
                'dump_users_info' => TRUE,
                'send_email' => TRUE
            ]
        );
        $this->obj->Generic = $generic;

        $this->obj->session->method('unset_userdata')->willReturn(TRUE);
    
        $actual = $this->obj->update_password($password, $id);
        $expected = [
            'message' => 'success'
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);

    }

    public function test_update_password_checksNewPasswordDoesntMatchOldPasswordAndNoResponse()
    {

        $result_info = [
             [
                'id' => 1, 
                'name' => 'test name', 
                'email' => 'test@email.com',
                'password' => 'ec5mKJWnLbUiqOj7GAICDw==', 
                'status' => 'Active', 
                'timestamp' => 1636918148, 
                'account_type' => 'ADMIN', 
                'login_attempts' => 123123, 
                'login_layers' => '00111', 
                'image' => '', 
                'saltiness' => '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'

            ]
        ];
        $password = 'password';
        $id = 'id';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result_info);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(FALSE);


        $password_encrypt_decrypt = $this->getDouble(
            'Password_encrypt_decrypt', [
                'decrypt' => 'decrypted password',
                'encrypt' => [
                    'password' => 'encrypted password',
                    'salt' => 'salt'
                ]
            ]
        );
        $this->obj->password_encrypt_decrypt = $password_encrypt_decrypt;

        $actual = $this->obj->update_password($password, $id);

        $this->assertNull($actual);

    }

    // Test for getUsersStatuses with data
    //
    public function test_getUsersStatuses()
    {
        $result = [
            [
                'email' => 'email',
                'id' => 2,
                'status' => 'Active'
            ]
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->getUsersStatuses();
        $this->assertIsArray($actual);

    }

    // Test for setUsersActiveByIds with data
    //
    public function test_setUsersActiveByIds()
    {
        $ids = [1,2,3,4,5];
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
            
        $actual = $this->obj->setUsersActiveByIds($ids);

        $this->assertTrue($actual);
    }

}

?>
