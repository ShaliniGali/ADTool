<?php 
/**
 * @group base
 */
class Register_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Register_model();

        // Inject mock object into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

    }

    // Test for updateAccountStatus with TRUE
    
    public function test_updateAccountStatus() {

        $id = 2;
        $status = 'ACTIVE';
        $account_type = 'ADMIN';
        $enabled_layers = '00111';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->updateAccountStatus($id, $status, $account_type, $enabled_layers);

        // For debugging
        // echo var_dump($actual);

        $this->assertNull($actual);

    }

    // Test for updateAccountStatus with Data
    //
    public function test_get_recovery_keys() {
    
        $expected = array(
            'Recoverykeys' => ['s1','s2','s3','s4','s5','s6']
        );

        // Call and test
        $actual = $this->obj->get_recovery_keys();

        // For debugging
        // print_r(json_decode($actual,true));

        $this->assertEquals(count(json_decode($actual,true)['Recoverykeys']),count($expected['Recoverykeys']));

    }

    // Test for insert_expiry_date with TRUE
    //
    public function test_insert_expiry_date() {

        $id = 2;
        $date = '2021-10-10';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->insert_expiry_date($date,$id);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue($actual);

    }

    // Test for check_empty with data
    //
    public function test_check_empty() {

        $result = [
            [
                "id"    => "1",
            ] 
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        
        $dbResult->method('result')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        // Call and test
        $actual = $this->obj->check_empty();

        // For debugging
        // echo var_dump($actual);

        $this->assertIsArray($actual);

    }
    
    // Test for registerActiveUsers with data
    // TODO: update user
    public function test_registerActiveUsers() {

        $result = [
            [
                "id"    => "1",
            ] 
        ];

        $users = [1];
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert_id')
            ->willReturn(1);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->registerActiveUsers($users);

        // For debugging
        // echo var_dump($actual);

        $this->assertIsInt($actual);

    }
    
    // Test for user_activate with TRUE
    //
    public function test_user_activate_admin_Active() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Active",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];



        $data['id'] = 2;
        $type = 'admin_verify';

        // Create mock object for CI_DB_result
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        
        $dbResult->method('result_array')->willReturn($user_info);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

    
        // Call and test
        $actual = $this->obj->user_activate($data,$type);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue(!$actual);

    }

    public function test_user_activate_admin_pending_loginLayeryes() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $key_info = [
            [
                "id"    => "1",
                "recovery_key"    => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}',
            ] 
        ];

        $data['id'] = 2;
        $data['enableLoginLayer'] = 'Yes';
        $type = 'admin_verify';
        $data['tfa'] = [];
        $data['tfa']['gAuth'] = 'Yes';
        $data['tfa']['yubikey'] = 'Yes';
        $data['tfa']['cac'] = 'Yes';
        $data['account_type'] = 'Admin';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        
        // $dbResult->method('result_array')->willReturn($user_info);
        // $this->obj->DBs->GUARDIAN_DEV
        //     ->method('get')
        //     ->willReturn($dbResult);
        
        $dbResult->method('result_array')
            ->willReturnOnConsecutiveCalls($user_info,$user_info,$key_info);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // updateAccountStatus
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        // dump_user_email_activity
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

    
        // Call and test
        $actual = $this->obj->user_activate($data,$type);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue($actual);

    }


     public function test_user_activate_admin_pending_loginLayersAndEvaluationDumpUserEmailWithTrueUiEmailSend() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $key_info = [
            [
                "id"    => "1",
                "recovery_key"    => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}',
            ] 
        ];

        $data['id'] = 2;
        $data['enableLoginLayer'] = 'Yes';
        $type = 'admin_verify';
        $data['tfa'] = [];
        $data['tfa']['gAuth'] = 'Yes';
        $data['tfa']['yubikey'] = 'Yes';
        $data['tfa']['cac'] = 'Yes';
        $data['account_type'] = 'Admin';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')
            ->willReturnOnConsecutiveCalls($user_info,$user_info,$key_info);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // updateAccountStatus
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);


        $generic = $this->getDouble(
            'Generic', [
                'send_email' => ['insert_id' => 'something'] 
            ]
        );
        $this->obj->Generic = $generic;

        // dump_user_email_activity
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        // Patch mock values into real constants for the testing function.
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND',
			'TRUE',
			Login_model::class . '::dump_user_email_activity'
        );

    
        // Call and test
        $actual = $this->obj->user_activate($data,$type);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue($actual);

    }

    public function test_user_activate_admin_pending_loginLayerNo() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $key_info = [
            [
                "id"    => "1",
                "recovery_key"    => '{"Recoverykeys": ["6f06526e28b51175", "bd7f430e8fc78b7d", "6c353f32c5869a38", "69e3a6e2646646a2", "45661c6f2003034e", "4ad3ce66b4f9cf79"]}',
            ] 
        ];

        $data['id'] = 2;
        $data['enableLoginLayer'] = 'No';
        $type = 'admin_verify';
        $data['tfa'] = [];
        $data['tfa']['gAuth'] = 'Yes';
        $data['tfa']['yubikey'] = 'Yes';
        $data['tfa']['cac'] = 'Yes';
        $data['account_type'] = 'Admin';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        
        // $dbResult->method('result_array')->willReturn($user_info);
        // $this->obj->DBs->GUARDIAN_DEV
        //     ->method('get')
        //     ->willReturn($dbResult);
        
        $dbResult->method('result_array')
            ->willReturnOnConsecutiveCalls($user_info,$user_info,$key_info);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
        
        // Dump user data
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // updateAccountStatus
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

        // dump_user_email_activity
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

    
        // Call and test
        $actual = $this->obj->user_activate($data,$type);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue($actual);

    }


    public function test_user_activate_admin_self_verify_loginLayerNo() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];


        $data['id'] = 2;
        $data['enableLoginLayer'] = 'No';
        $data['account_type'] = 'Admin';
        $type = 'self_verify';
        $data['tfa'] = [];
        $data['tfa']['gAuth'] = 'Yes';
        $data['tfa']['yubikey'] = 'Yes';
        $data['tfa']['cac'] = 'Yes';

        // Create mock object for CI_DB_result
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        
        $dbResult->method('result_array')->willReturn($user_info);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // updateAccountStatus
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

    
        // Call and test
        $actual = $this->obj->user_activate($data,$type);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue($actual);

    }

    public function test_user_activate_admin_unknown_loginLayerNo() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];


        $data['id'] = 2;
        $data['enableLoginLayer'] = 'No';
        $data['account_type'] = 'Admin';
        $type = 'unknown';
        $data['tfa'] = [];
        $data['tfa']['gAuth'] = 'Yes';
        $data['tfa']['yubikey'] = 'Yes';
        $data['tfa']['cac'] = 'Yes';

        // Create mock object for CI_DB_result
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        
        $dbResult->method('result_array')->willReturn($user_info);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // updateAccountStatus
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);

    
        // Call and test
        $actual = $this->obj->user_activate($data,$type);

        // For debugging
        // echo var_dump($actual);

        $this->assertNull($actual);

    }
    

    ///////////////////////////////////
    //Reject Register Test Cases

    public function test_reject_register_noAccountData() {

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $user_info2 = [
        ];

        $id = 2;
        $email = 'some@rhombuspower.com';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($user_info,$user_info2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
            
        // Call and test
        $actual = $this->obj->reject_register($id,$email);

        // For debugging
        // echo var_dump($actual);

        $this->assertSame($actual['message'],'failure');

    }

    public function test_reject_register_AccountData_RegistrationPending() {

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $user_info2 = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $id = 2;
        $email = 'some@rhombuspower.com';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($user_info,$user_info2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
            
        // Call and test
        $actual = $this->obj->reject_register($id,$email);

        // For debugging
        // echo var_dump($actual);

        $this->assertSame($actual['message'],'failure');

    }
    
    public function test_reject_register_AccountData_Active() {

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Active",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "ZSfIxeSmrnNmbKsISuijtK2dWaLqi+CBidi7Gfe3GkH0b/EV45YwfhGmna0DCA+RSxWOpS/hhY3tAicP+ThrgntHW3QWeOFlclhe5kj1Lub4MWb4zhuMzJyxdCqGQWAcjkiwN3WDf7ZG9BOxu5ynmWwcK4w1fthddvJ+Wfo5pzfKcTj7aXtr9nvszVf2+5nM4BGGbvzV/xnJdLhlFBWHnBzqwMNCGT7V991re9GutA3uh0+UkcnvX+FV1R+yn5wKBM/KuE7AMY66q6O6wBO4KY0Jt6pwCaWfOexn48NJLlQpMcZ1zNQsBdC843vCOXVfh8ARrPHWS/5r5vmOHhExOk+9DkAC4l03l6Lajr9/CrlosISm3/6bvDOGjub52V6ib2DKzfLOhUojeEesjLtphYYKI+g6Z5z8aTWQEnQMWkOWMCKacHZqUrlzR5n+ptrURPShVHEBmfkz/6Bq4pgNU4AN9lXLc9W/TqDAovMBGsGtwx1zs/NUtEIyqnA30MZaoanprQtEg4S/EmXpjwY1x9ZcZAzTuJhfXqnunKCRRnhQ7nPaWJCs+E8CWZrH2iAmmy6iiua5TQkaKWqrhdVJcs+jAGOw2SGS9lA0qwMvPduh3IJlrrXCESi5+LmTVAA0eeKLe9f6Zk5rhQLHbnU7JW6jlvtVjnwWWBZb8kOSVYdXacoDpan0/FVPQt6qLkaC9VVu5gB1xxCacemDbCNEHJyRVa4pVxrB7A4BShl4mapGR/XLFum3Si2ZTZAmP6pgjBUuqjGn2Ij/uE3jjNOLL/fj7YXN4+egTVeiB1kcE6gl8CdVSo4JZJJTO2/42hF8lXHaXs9KSNMOlbAPoH8JFQOLNUR5QY8lSj5z8uufNPjAurFWd5EzTjGFW3Pa/wEY5VSCzBjNeTFe+2AXYFpaJyhgtdQpigVKsnEFg6zQIYaCqnSjJb6OpQnLuAR/z3C++9EbMnbaq1Q4Efc1odeIkkaRcOy4ERudwimSRwmtMvgxMtCacQb3Z/dqhTOHrsml2fWKkkCVrkDyoOEC65li3BqwfFxcn2iYFw/JmFd7ozqwPiOvfpqfL+OaCe0de9/A/CcQLaC/bQOCrRRr4IFnE5EgUsqHnrxcMlCoRJelWEsykaWcIWLqc4nVX6zCybZMdiP9itsSMB0IOIw6PRYPb76vWv71r5ReKAx/PLf3bc4Ixe+QtUHJIYRxKYDEff/qUBZAmUyv+r3AX6FrwYoXnE/GiO6oxKpHY5bc2MKgJMAN75xwEbWkeXaKnfg/WawwtbhU2lyl9G6c0Z8qyeeF39vl3m0wmzy9ImKQy6EmYu02P9hp9LXOl5PVrQ/zpIvf5IPkgFO0Mw+KHBoHNEuYdXzJv0mNkPk1H2saEisNtsgYtVfdSrLoT+VZDtftp2KAsxH1op9GSpwTtNUdxCVIBpYN6CasGm6dvE6tYMOBpmvsLD+uUDVtKhW0YRtCSVVnNU0f5xHbOt0JBHeA5FNUU7EYsvxdM0LbYBeasHVLMezJJmS+D764jTaVusJnjox+RNI7QL15Bgpn+quZX8OJQJBPPT5jqeSrlSLCY932fAJcL6lUhe0sMdnI+2pUj55AZPyfT2jcUzmZ+Vw1uVF2PUreSjBJYZlzvFS+sobnRmyFn/EzWUN4Q8CgNbaX9fTx0mvGb32EdrFk3Hzkj0CnmllXL92EKUtzoO/9C3LTLKsxhldQmMG5UgVm4BX1iG6Szw03Xwu4zlroT8paErno/Kg+91MEVlB68p+qRmCOvJ6JBM78aQ7kKFxp9rbHzMKybuguat+J6QFZ1H1BxoC56L6mfNK1eAD4wbzq8g7gDssVkW6860gtm3W583gVHk+bJEMSb9TLb2t80eFy8xGSUHzmqigr/POxuVYy+N1UD96UroOZPmYX99Tri5NiHVZOBqnNPi/nF0/VbOZ4WYykxaiPwAQBSW+SkFh7Ed+hdedmjdvYe1AcuIbmgTtRKjiME3vYc0GU94k6zi4SpoC6OkcRZC5barzB8WxSkYn9UIp1SRkS2H1qcD197GMPAk0X0Fh7ZBzCIvEHVwHRgNGuQLtZCE4pTqTOel99Rrtk/TmEmWqrs5pHKeLGRQE7AbZweA827oXrpkrMB7za6w9cv39Jc878v/iAFbzzBc5WfcQcHO9xWwfyglFPG29x80SMBczeCsds39WOAkaBdF0+TYwppNeuDRkDaTYVz5GKxXk+E+82xanrvGEMCdTDFsw/yc5Btwe0ImR0b0nSvlOWj4s/Y7cGj/Gm1pbsnjSJI5UEjXUHT9M4MhAGhfesHmjM4tp4FaNj4TVTO4qNWJrsWNJjoEsD7Id9tW7PjoqpLayE/4bOV2Qx80eHYPjeLIn+vZ9ckvNBAbqocRRfEKwQYWM5iH0PR4ABc4uQ3aZcbp3zwp7A2SVg9sA+fp8Nxzfj/bfC5u2PFJKvedUQ3i+tXmLTt24eLwjDY5QYi0B1r4p6EE6Bt2/Ulu5ST4L/XqC9Xx50DsTAlAS+wuDuaMJrk4TrrvvNUInaq4ivdRz3m+8OJJbbD4L0cYxZbgudDz4AMISdwPZYGMIV/X/cmvm5GVa6RIfP4rVQMvLJfpsJV6SFTORhQ5/YYiNCDARG07WoQxoByYojuelEdLGfpikTmf4LqPrh8qpBCYCQWWQWJkcmbrUYdTP5LhAipoSDC++FG/D3DiNA47XZs/p/DOG76d1NFgF4UIqKQb3c6sXtXog=",

            ] 
        ];

        $user_info2 = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $id = 2;
        $email = 'some@rhombuspower.com';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($user_info,$user_info2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
            
        // Call and test
        $actual = $this->obj->reject_register($id,$email);

        // For debugging
        // echo var_dump($actual);

        $this->assertSame($actual['message'],'failure');

    }

    public function test_reject_register_AccountData_RegistrationPending_True() {

        $user_info = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "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",

            ] 
        ];

        $user_info2 = [
            [
                "id"    => "1",
                "name"    => "name",
                "email"    => "name@rhombuspower.com",
                "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
                "status"    => "Registration_pending",
                "timestamp"    => 1636764275,
                "account_type"    => "ADMIN",
                "login_attempts"    => 0,
                "login_layers"    => "00111",
                "image"    => NULL,
                "saltiness"    => "ZSfIxeSmrnNmbKsISuijtK2dWaLqi+CBidi7Gfe3GkH0b/EV45YwfhGmna0DCA+RSxWOpS/hhY3tAicP+ThrgntHW3QWeOFlclhe5kj1Lub4MWb4zhuMzJyxdCqGQWAcjkiwN3WDf7ZG9BOxu5ynmWwcK4w1fthddvJ+Wfo5pzfKcTj7aXtr9nvszVf2+5nM4BGGbvzV/xnJdLhlFBWHnBzqwMNCGT7V991re9GutA3uh0+UkcnvX+FV1R+yn5wKBM/KuE7AMY66q6O6wBO4KY0Jt6pwCaWfOexn48NJLlQpMcZ1zNQsBdC843vCOXVfh8ARrPHWS/5r5vmOHhExOk+9DkAC4l03l6Lajr9/CrlosISm3/6bvDOGjub52V6ib2DKzfLOhUojeEesjLtphYYKI+g6Z5z8aTWQEnQMWkOWMCKacHZqUrlzR5n+ptrURPShVHEBmfkz/6Bq4pgNU4AN9lXLc9W/TqDAovMBGsGtwx1zs/NUtEIyqnA30MZaoanprQtEg4S/EmXpjwY1x9ZcZAzTuJhfXqnunKCRRnhQ7nPaWJCs+E8CWZrH2iAmmy6iiua5TQkaKWqrhdVJcs+jAGOw2SGS9lA0qwMvPduh3IJlrrXCESi5+LmTVAA0eeKLe9f6Zk5rhQLHbnU7JW6jlvtVjnwWWBZb8kOSVYdXacoDpan0/FVPQt6qLkaC9VVu5gB1xxCacemDbCNEHJyRVa4pVxrB7A4BShl4mapGR/XLFum3Si2ZTZAmP6pgjBUuqjGn2Ij/uE3jjNOLL/fj7YXN4+egTVeiB1kcE6gl8CdVSo4JZJJTO2/42hF8lXHaXs9KSNMOlbAPoH8JFQOLNUR5QY8lSj5z8uufNPjAurFWd5EzTjGFW3Pa/wEY5VSCzBjNeTFe+2AXYFpaJyhgtdQpigVKsnEFg6zQIYaCqnSjJb6OpQnLuAR/z3C++9EbMnbaq1Q4Efc1odeIkkaRcOy4ERudwimSRwmtMvgxMtCacQb3Z/dqhTOHrsml2fWKkkCVrkDyoOEC65li3BqwfFxcn2iYFw/JmFd7ozqwPiOvfpqfL+OaCe0de9/A/CcQLaC/bQOCrRRr4IFnE5EgUsqHnrxcMlCoRJelWEsykaWcIWLqc4nVX6zCybZMdiP9itsSMB0IOIw6PRYPb76vWv71r5ReKAx/PLf3bc4Ixe+QtUHJIYRxKYDEff/qUBZAmUyv+r3AX6FrwYoXnE/GiO6oxKpHY5bc2MKgJMAN75xwEbWkeXaKnfg/WawwtbhU2lyl9G6c0Z8qyeeF39vl3m0wmzy9ImKQy6EmYu02P9hp9LXOl5PVrQ/zpIvf5IPkgFO0Mw+KHBoHNEuYdXzJv0mNkPk1H2saEisNtsgYtVfdSrLoT+VZDtftp2KAsxH1op9GSpwTtNUdxCVIBpYN6CasGm6dvE6tYMOBpmvsLD+uUDVtKhW0YRtCSVVnNU0f5xHbOt0JBHeA5FNUU7EYsvxdM0LbYBeasHVLMezJJmS+D764jTaVusJnjox+RNI7QL15Bgpn+quZX8OJQJBPPT5jqeSrlSLCY932fAJcL6lUhe0sMdnI+2pUj55AZPyfT2jcUzmZ+Vw1uVF2PUreSjBJYZlzvFS+sobnRmyFn/EzWUN4Q8CgNbaX9fTx0mvGb32EdrFk3Hzkj0CnmllXL92EKUtzoO/9C3LTLKsxhldQmMG5UgVm4BX1iG6Szw03Xwu4zlroT8paErno/Kg+91MEVlB68p+qRmCOvJ6JBM78aQ7kKFxp9rbHzMKybuguat+J6QFZ1H1BxoC56L6mfNK1eAD4wbzq8g7gDssVkW6860gtm3W583gVHk+bJEMSb9TLb2t80eFy8xGSUHzmqigr/POxuVYy+N1UD96UroOZPmYX99Tri5NiHVZOBqnNPi/nF0/VbOZ4WYykxaiPwAQBSW+SkFh7Ed+hdedmjdvYe1AcuIbmgTtRKjiME3vYc0GU94k6zi4SpoC6OkcRZC5barzB8WxSkYn9UIp1SRkS2H1qcD197GMPAk0X0Fh7ZBzCIvEHVwHRgNGuQLtZCE4pTqTOel99Rrtk/TmEmWqrs5pHKeLGRQE7AbZweA827oXrpkrMB7za6w9cv39Jc878v/iAFbzzBc5WfcQcHO9xWwfyglFPG29x80SMBczeCsds39WOAkaBdF0+TYwppNeuDRkDaTYVz5GKxXk+E+82xanrvGEMCdTDFsw/yc5Btwe0ImR0b0nSvlOWj4s/Y7cGj/Gm1pbsnjSJI5UEjXUHT9M4MhAGhfesHmjM4tp4FaNj4TVTO4qNWJrsWNJjoEsD7Id9tW7PjoqpLayE/4bOV2Qx80eHYPjeLIn+vZ9ckvNBAbqocRRfEKwQYWM5iH0PR4ABc4uQ3aZcbp3zwp7A2SVg9sA+fp8Nxzfj/bfC5u2PFJKvedUQ3i+tXmLTt24eLwjDY5QYi0B1r4p6EE6Bt2/Ulu5ST4L/XqC9Xx50DsTAlAS+wuDuaMJrk4TrrvvNUInaq4ivdRz3m+8OJJbbD4L0cYxZbgudDz4AMISdwPZYGMIV/X/cmvm5GVa6RIfP4rVQMvLJfpsJV6SFTORhQ5/YYiNCDARG07WoQxoByYojuelEdLGfpikTmf4LqPrh8qpBCYCQWWQWJkcmbrUYdTP5LhAipoSDC++FG/D3DiNA47XZs/p/DOG76d1NFgF4UIqKQb3c6sXtXog=",

            ] 
        ];

        $id = 2;
        $email = 'some@rhombuspower.com';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($user_info,$user_info2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
            
        // updateAccountStatus
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);   

        // Call and test
        $actual = $this->obj->reject_register($id,$email);

        // For debugging
        // echo var_dump($actual);

        $this->assertSame($actual['message'],'success');

    }

    public function test_user_register_verification_false() {

        $info = [
            "id"    => "1",
            "name"    => "name",
            "email"    => "name@rhombuspower.com",
            "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
            "status"    => "Active",
            "timestamp"    => 1636764275,
            "account_type"    => "ADMIN",
            "login_attempts"    => 0,
            "login_layers"    => "00111",
            "image"    => NULL,
            "saltiness"    => "ZSfIxeSmrnNmbKsISuijtK2dWaLqi+CBidi7Gfe3GkH0b/EV45YwfhGmna0DCA+RSxWOpS/hhY3tAicP+ThrgntHW3QWeOFlclhe5kj1Lub4MWb4zhuMzJyxdCqGQWAcjkiwN3WDf7ZG9BOxu5ynmWwcK4w1fthddvJ+Wfo5pzfKcTj7aXtr9nvszVf2+5nM4BGGbvzV/xnJdLhlFBWHnBzqwMNCGT7V991re9GutA3uh0+UkcnvX+FV1R+yn5wKBM/KuE7AMY66q6O6wBO4KY0Jt6pwCaWfOexn48NJLlQpMcZ1zNQsBdC843vCOXVfh8ARrPHWS/5r5vmOHhExOk+9DkAC4l03l6Lajr9/CrlosISm3/6bvDOGjub52V6ib2DKzfLOhUojeEesjLtphYYKI+g6Z5z8aTWQEnQMWkOWMCKacHZqUrlzR5n+ptrURPShVHEBmfkz/6Bq4pgNU4AN9lXLc9W/TqDAovMBGsGtwx1zs/NUtEIyqnA30MZaoanprQtEg4S/EmXpjwY1x9ZcZAzTuJhfXqnunKCRRnhQ7nPaWJCs+E8CWZrH2iAmmy6iiua5TQkaKWqrhdVJcs+jAGOw2SGS9lA0qwMvPduh3IJlrrXCESi5+LmTVAA0eeKLe9f6Zk5rhQLHbnU7JW6jlvtVjnwWWBZb8kOSVYdXacoDpan0/FVPQt6qLkaC9VVu5gB1xxCacemDbCNEHJyRVa4pVxrB7A4BShl4mapGR/XLFum3Si2ZTZAmP6pgjBUuqjGn2Ij/uE3jjNOLL/fj7YXN4+egTVeiB1kcE6gl8CdVSo4JZJJTO2/42hF8lXHaXs9KSNMOlbAPoH8JFQOLNUR5QY8lSj5z8uufNPjAurFWd5EzTjGFW3Pa/wEY5VSCzBjNeTFe+2AXYFpaJyhgtdQpigVKsnEFg6zQIYaCqnSjJb6OpQnLuAR/z3C++9EbMnbaq1Q4Efc1odeIkkaRcOy4ERudwimSRwmtMvgxMtCacQb3Z/dqhTOHrsml2fWKkkCVrkDyoOEC65li3BqwfFxcn2iYFw/JmFd7ozqwPiOvfpqfL+OaCe0de9/A/CcQLaC/bQOCrRRr4IFnE5EgUsqHnrxcMlCoRJelWEsykaWcIWLqc4nVX6zCybZMdiP9itsSMB0IOIw6PRYPb76vWv71r5ReKAx/PLf3bc4Ixe+QtUHJIYRxKYDEff/qUBZAmUyv+r3AX6FrwYoXnE/GiO6oxKpHY5bc2MKgJMAN75xwEbWkeXaKnfg/WawwtbhU2lyl9G6c0Z8qyeeF39vl3m0wmzy9ImKQy6EmYu02P9hp9LXOl5PVrQ/zpIvf5IPkgFO0Mw+KHBoHNEuYdXzJv0mNkPk1H2saEisNtsgYtVfdSrLoT+VZDtftp2KAsxH1op9GSpwTtNUdxCVIBpYN6CasGm6dvE6tYMOBpmvsLD+uUDVtKhW0YRtCSVVnNU0f5xHbOt0JBHeA5FNUU7EYsvxdM0LbYBeasHVLMezJJmS+D764jTaVusJnjox+RNI7QL15Bgpn+quZX8OJQJBPPT5jqeSrlSLCY932fAJcL6lUhe0sMdnI+2pUj55AZPyfT2jcUzmZ+Vw1uVF2PUreSjBJYZlzvFS+sobnRmyFn/EzWUN4Q8CgNbaX9fTx0mvGb32EdrFk3Hzkj0CnmllXL92EKUtzoO/9C3LTLKsxhldQmMG5UgVm4BX1iG6Szw03Xwu4zlroT8paErno/Kg+91MEVlB68p+qRmCOvJ6JBM78aQ7kKFxp9rbHzMKybuguat+J6QFZ1H1BxoC56L6mfNK1eAD4wbzq8g7gDssVkW6860gtm3W583gVHk+bJEMSb9TLb2t80eFy8xGSUHzmqigr/POxuVYy+N1UD96UroOZPmYX99Tri5NiHVZOBqnNPi/nF0/VbOZ4WYykxaiPwAQBSW+SkFh7Ed+hdedmjdvYe1AcuIbmgTtRKjiME3vYc0GU94k6zi4SpoC6OkcRZC5barzB8WxSkYn9UIp1SRkS2H1qcD197GMPAk0X0Fh7ZBzCIvEHVwHRgNGuQLtZCE4pTqTOel99Rrtk/TmEmWqrs5pHKeLGRQE7AbZweA827oXrpkrMB7za6w9cv39Jc878v/iAFbzzBc5WfcQcHO9xWwfyglFPG29x80SMBczeCsds39WOAkaBdF0+TYwppNeuDRkDaTYVz5GKxXk+E+82xanrvGEMCdTDFsw/yc5Btwe0ImR0b0nSvlOWj4s/Y7cGj/Gm1pbsnjSJI5UEjXUHT9M4MhAGhfesHmjM4tp4FaNj4TVTO4qNWJrsWNJjoEsD7Id9tW7PjoqpLayE/4bOV2Qx80eHYPjeLIn+vZ9ckvNBAbqocRRfEKwQYWM5iH0PR4ABc4uQ3aZcbp3zwp7A2SVg9sA+fp8Nxzfj/bfC5u2PFJKvedUQ3i+tXmLTt24eLwjDY5QYi0B1r4p6EE6Bt2/Ulu5ST4L/XqC9Xx50DsTAlAS+wuDuaMJrk4TrrvvNUInaq4ivdRz3m+8OJJbbD4L0cYxZbgudDz4AMISdwPZYGMIV/X/cmvm5GVa6RIfP4rVQMvLJfpsJV6SFTORhQ5/YYiNCDARG07WoQxoByYojuelEdLGfpikTmf4LqPrh8qpBCYCQWWQWJkcmbrUYdTP5LhAipoSDC++FG/D3DiNA47XZs/p/DOG76d1NFgF4UIqKQb3c6sXtXog=",

        ];

        // updateAccountStatus
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
         
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);   

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert_id')
            ->willReturn(1);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // DUMP_USER
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // send_email
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);
            
        // Call and test
        $actual = $this->obj->user_register($info,FALSE);

        // For debugging
        // echo var_dump($actual);

        $this->assertEquals(1, $actual);

    }

    public function test_user_register_verification_True() {

        $undeleted_user = [
            [
                "id"    => "1",
            ] 
        ];

        $info = [
            "id"    => "1",
            "name"    => "name",
            "email"    => "name@rhombuspower.com",
            "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
            "status"    => "Active",
            "timestamp"    => 1636764275,
            "account_type"    => "ADMIN",
            "login_attempts"    => 0,
            "login_layers"    => "00111",
            "image"    => NULL,
            "saltiness"    => "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",
            "message"   => "message"
        ];

        // updateAccountStatus
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
         
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);   

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert_id')
            ->willReturn(1);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // send_email
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        $dbResult->method('result_array')->willReturn($undeleted_user);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        // dump_user_email_activity
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // Call and test
        $actual = $this->obj->user_register($info,TRUE);

        // For debugging
        // echo var_dump($actual);

        $this->assertEquals(1, $actual);

    }

    public function test_user_register_verification_TrueAndNotKeycloak() {

        $undeleted_user = [
            [
                "id"    => "1",
            ] 
        ];

        $info = [
            "id"    => "1",
            "name"    => "name",
            "email"    => "name@rhombuspower.com",
            "password"    => "ec5mKJWnLbUiqOj7GAICDw==",
            "status"    => "Active",
            "timestamp"    => 1636764275,
            "account_type"    => "ADMIN",
            "login_attempts"    => 0,
            "login_layers"    => "00111",
            "image"    => NULL,
            "saltiness"    => "ZSfIxeSmrnNmbKsISuijtK2dWaLqi+CBidi7Gfe3GkH0b/EV45YwfhGmna0DCA+RSxWOpS/hhY3tAicP+ThrgntHW3QWeOFlclhe5kj1Lub4MWb4zhuMzJyxdCqGQWAcjkiwN3WDf7ZG9BOxu5ynmWwcK4w1fthddvJ+Wfo5pzfKcTj7aXtr9nvszVf2+5nM4BGGbvzV/xnJdLhlFBWHnBzqwMNCGT7V991re9GutA3uh0+UkcnvX+FV1R+yn5wKBM/KuE7AMY66q6O6wBO4KY0Jt6pwCaWfOexn48NJLlQpMcZ1zNQsBdC843vCOXVfh8ARrPHWS/5r5vmOHhExOk+9DkAC4l03l6Lajr9/CrlosISm3/6bvDOGjub52V6ib2DKzfLOhUojeEesjLtphYYKI+g6Z5z8aTWQEnQMWkOWMCKacHZqUrlzR5n+ptrURPShVHEBmfkz/6Bq4pgNU4AN9lXLc9W/TqDAovMBGsGtwx1zs/NUtEIyqnA30MZaoanprQtEg4S/EmXpjwY1x9ZcZAzTuJhfXqnunKCRRnhQ7nPaWJCs+E8CWZrH2iAmmy6iiua5TQkaKWqrhdVJcs+jAGOw2SGS9lA0qwMvPduh3IJlrrXCESi5+LmTVAA0eeKLe9f6Zk5rhQLHbnU7JW6jlvtVjnwWWBZb8kOSVYdXacoDpan0/FVPQt6qLkaC9VVu5gB1xxCacemDbCNEHJyRVa4pVxrB7A4BShl4mapGR/XLFum3Si2ZTZAmP6pgjBUuqjGn2Ij/uE3jjNOLL/fj7YXN4+egTVeiB1kcE6gl8CdVSo4JZJJTO2/42hF8lXHaXs9KSNMOlbAPoH8JFQOLNUR5QY8lSj5z8uufNPjAurFWd5EzTjGFW3Pa/wEY5VSCzBjNeTFe+2AXYFpaJyhgtdQpigVKsnEFg6zQIYaCqnSjJb6OpQnLuAR/z3C++9EbMnbaq1Q4Efc1odeIkkaRcOy4ERudwimSRwmtMvgxMtCacQb3Z/dqhTOHrsml2fWKkkCVrkDyoOEC65li3BqwfFxcn2iYFw/JmFd7ozqwPiOvfpqfL+OaCe0de9/A/CcQLaC/bQOCrRRr4IFnE5EgUsqHnrxcMlCoRJelWEsykaWcIWLqc4nVX6zCybZMdiP9itsSMB0IOIw6PRYPb76vWv71r5ReKAx/PLf3bc4Ixe+QtUHJIYRxKYDEff/qUBZAmUyv+r3AX6FrwYoXnE/GiO6oxKpHY5bc2MKgJMAN75xwEbWkeXaKnfg/WawwtbhU2lyl9G6c0Z8qyeeF39vl3m0wmzy9ImKQy6EmYu02P9hp9LXOl5PVrQ/zpIvf5IPkgFO0Mw+KHBoHNEuYdXzJv0mNkPk1H2saEisNtsgYtVfdSrLoT+VZDtftp2KAsxH1op9GSpwTtNUdxCVIBpYN6CasGm6dvE6tYMOBpmvsLD+uUDVtKhW0YRtCSVVnNU0f5xHbOt0JBHeA5FNUU7EYsvxdM0LbYBeasHVLMezJJmS+D764jTaVusJnjox+RNI7QL15Bgpn+quZX8OJQJBPPT5jqeSrlSLCY932fAJcL6lUhe0sMdnI+2pUj55AZPyfT2jcUzmZ+Vw1uVF2PUreSjBJYZlzvFS+sobnRmyFn/EzWUN4Q8CgNbaX9fTx0mvGb32EdrFk3Hzkj0CnmllXL92EKUtzoO/9C3LTLKsxhldQmMG5UgVm4BX1iG6Szw03Xwu4zlroT8paErno/Kg+91MEVlB68p+qRmCOvJ6JBM78aQ7kKFxp9rbHzMKybuguat+J6QFZ1H1BxoC56L6mfNK1eAD4wbzq8g7gDssVkW6860gtm3W583gVHk+bJEMSb9TLb2t80eFy8xGSUHzmqigr/POxuVYy+N1UD96UroOZPmYX99Tri5NiHVZOBqnNPi/nF0/VbOZ4WYykxaiPwAQBSW+SkFh7Ed+hdedmjdvYe1AcuIbmgTtRKjiME3vYc0GU94k6zi4SpoC6OkcRZC5barzB8WxSkYn9UIp1SRkS2H1qcD197GMPAk0X0Fh7ZBzCIvEHVwHRgNGuQLtZCE4pTqTOel99Rrtk/TmEmWqrs5pHKeLGRQE7AbZweA827oXrpkrMB7za6w9cv39Jc878v/iAFbzzBc5WfcQcHO9xWwfyglFPG29x80SMBczeCsds39WOAkaBdF0+TYwppNeuDRkDaTYVz5GKxXk+E+82xanrvGEMCdTDFsw/yc5Btwe0ImR0b0nSvlOWj4s/Y7cGj/Gm1pbsnjSJI5UEjXUHT9M4MhAGhfesHmjM4tp4FaNj4TVTO4qNWJrsWNJjoEsD7Id9tW7PjoqpLayE/4bOV2Qx80eHYPjeLIn+vZ9ckvNBAbqocRRfEKwQYWM5iH0PR4ABc4uQ3aZcbp3zwp7A2SVg9sA+fp8Nxzfj/bfC5u2PFJKvedUQ3i+tXmLTt24eLwjDY5QYi0B1r4p6EE6Bt2/Ulu5ST4L/XqC9Xx50DsTAlAS+wuDuaMJrk4TrrvvNUInaq4ivdRz3m+8OJJbbD4L0cYxZbgudDz4AMISdwPZYGMIV/X/cmvm5GVa6RIfP4rVQMvLJfpsJV6SFTORhQ5/YYiNCDARG07WoQxoByYojuelEdLGfpikTmf4LqPrh8qpBCYCQWWQWJkcmbrUYdTP5LhAipoSDC++FG/D3DiNA47XZs/p/DOG76d1NFgF4UIqKQb3c6sXtXog=",
            "message"   => "message"
        ];

        // updateAccountStatus
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
         
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);   

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert_id')
            ->willReturn(1);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        // send_email
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        $dbResult->method('result_array')->willReturn($undeleted_user);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        // dump_user_email_activity
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);

        MonkeyPatch::patchConstant(
            'RHOMBUS_SSO_KEYCLOAK',
            'FALSE',
            Register_model::class . '::user_register'
        );

        // Call and test
        $actual = $this->obj->user_register($info,TRUE);

        // For debugging
        // echo var_dump($actual);

        $this->assertEquals(1, $actual);

    }

}

?>
