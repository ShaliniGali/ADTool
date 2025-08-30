<?php 
/**
 * @group base
 */
class Account_manager_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Account_manager_model();

        // Inject mock object into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

    }

    public function test_getAccount() {
        $result = [
            [
                "id"    => "1",
                "email" => 2020,
                "account_type"  => 11,
                "admin_expiry"  => 50,
                "status"  => 52,
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
    
        // Call and test
        $actual = $this->obj->getAccount();

        // echo json_encode($actual);

        $expected = $result;

        $this->assertEquals($expected, $actual);

    }

    public function test_updateTableUser() {
        $result = 1;

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $data['Id'] = 1;
        $data['AccountType'] = 'USER';
        // Call and test
        $actual = $this->obj->updateTableUser($data);

        $this->assertEquals($result, $actual);

    }

    public function test_updateUser() {

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $result = [
            ["id" => 1]
        ];
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

        
        $type = "typeAdmin";
        $data['type'] = 'sso';
        $data['Id'] = 1;
        $data['AccountType'] = 'USER';
        $data['ExpiryDate'] = '12/07/2021';
        // Call and test
        $actual = $this->obj->updateUser($data, $type);
        $expected = array("message"=>"success");

        $this->assertEquals($expected, $actual);

        $type = "notAdmin";
        $data = [];
        $data['Id'] = 1;
        $data['type'] = 'sso';
        $data['AccountType'] = 'USER';
        // Call and test
        $actual = $this->obj->updateUser($data, $type);
        $expected = array("message"=>"success");
        $this->assertEquals($expected, $actual);

    }

    public function test_updateUser_notAdmin() {
        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $result = [
            ["id" => 1]
        ];
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

        $type = "notAdmin";
        $data = [];
        $data['Id'] = 1;
        $data['type'] = 'sso';
        $data['AccountType'] = 'USER';
        // Call and test
        $actual = $this->obj->updateUser($data, $type);

        $expected = array("message"=>"success");
        $this->assertEquals($expected, $actual);

    }

    public function test_deleteAccount_notSSO() {
        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

         $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturnOnConsecutiveCalls(TRUE, TRUE);
        
        $id = 1;
        $email = 'test@rhombuspower.com';
        $type = 'user';
        // Call and test
        $actual = $this->obj->deleteAccount($id, $email, $type);
        $expected = array("message"=>"success");

        // echo json_encode($actual);

        $this->assertEquals($expected, $actual);

    }

    public function test_deleteAccount_SSO() {
        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);
        
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

         $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $id = 1;
        $email = 'test@rhombuspower.com';
        $type = 'sso';
        // Call and test
        $actual = $this->obj->deleteAccount($id, $email, $type);
        $expected = array("message"=>"success");

        // echo json_encode($actual);
        

        $this->assertEquals($expected, $actual);

    }

    public function test_isSSOAvailable() {


        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $result = [
            [
                "id"    => "1",
                "email" => "test@rhombuspower.com",
                "status"  => 'Active',
                "account_type"  => 'User',
                "admin_expiry"  => '12/04/2022',
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
        
        // Call and test
        $actual = $this->obj->isSSOAvailable();
       
        $expected = $result;
       
        $this->assertEquals($expected, $actual);

    }

    public function test_activateSSOUSer_Updated() {
        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $result = [
                "id"    => "1",
                "email" => "test@rhombuspower.com",
                "status"  => 'Active',
                "account_type"  => 'User',
                "admin_expiry"  => '12/04/2022',
                'timestamp' => '12/04/2022',
        ];

        $dbResult->method('row_array')->willReturnOnConsecutiveCalls($result,$result);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $id = 1;
        $email = 'test@rhombuspower.com'; 
        // Call and test
        $actual = $this->obj->activateSSOUSer($id, $email);
       
        $expected = true;
       
        $this->assertEquals($expected, $actual);

    }
   
    public function test_activateSSOUSer_Updated_notnull() {
        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        
            $result = [
                "id"    => "1",
                "email" => "test@rhombuspower.com",
                "status"  => 'Active',
                "account_type"  => 'User',
                "admin_expiry"  => '12/04/2022',
                'timestamp' => '12/04/2022',
        ];

        $dbResult->method('row_array')->willReturnOnConsecutiveCalls($result,$result);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
        
        $id = 1;
        $email = 'test@rhombuspower.com'; 
        // Call and test
        $actual = $this->obj->activateSSOUSer($id, $email,'USER');
       
        $expected = true;
       
        $this->assertEquals($expected, $actual);

    }
}

?>