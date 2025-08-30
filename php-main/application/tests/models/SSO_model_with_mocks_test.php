<?php 
/**
 * @group base
 * @group model
 */
class SSO_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new SSO_model();

        // Inject mock object into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
        $this->obj->DBs->SOCOM_UI = $this->getMethodChainingDBMock();

    }

    public function test_userExists(){
        $result = [
            [
                "id"    => "1",
                "name" => "test",
                "email" => 'test@test.com',
                "password" => "password",
                "status" => "Active",
                "timestamp" => 0,
                "account_type"  => "USER",
                "login_attempts" => 0,
                "login_layers" => "10101",
                "image" => null,
                "saltiness" => null
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
        $actual = $this->obj->userExists('test@test.com');

        // echo json_encode($actual);

        $expected = $result;

        $this->assertEquals($expected, $actual);
    }

    public function test_userExists_strict(){
        $result = [
            [
                "id"    => "1",
                "email" => 'test@test.com',
                "status" => "Active",
                "timestamp" => 0,
                "unique_identifier" => null
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
        $actual = $this->obj->userExists('test@test.com', false, 'users_SSO');

        // echo json_encode($actual);

        $expected = $result;

        $this->assertEquals($expected, $actual);
    }

    public function test_promptAccountRegistration(){
        $result = [
            [
                "id"    => "1",
                "name" => "test",
                "email" => 'test@test.com',
                "password" => "password",
                "status" => "Active",
                "timestamp" => 0,
                "account_type"  => "USER",
                "login_attempts" => 0,
                "login_layers" => "10101",
                "image" => null,
                "saltiness" => null
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
        $actual = $this->obj->promptAccountRegistration('test@test.com');

        // echo json_encode($actual);

        $expected = "Active";

        $this->assertEquals($expected, $actual);
    }

    public function test_promptAccountRegistration_empty(){
        $result = [];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        // Call and test
        $actual = $this->obj->promptAccountRegistration('test@test.com');

        // echo json_encode($actual);

        $expected = true;

        $this->assertEquals($expected, $actual);
    }

    public function test_registerSSOUser(){

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();

        $this->obj->DBs->GUARDIAN_DEV =  $this->getMethodChainingDBMock();
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->registerSSOUser('test@test.com', 'Active');

        // echo json_encode($actual);

        $this->assertNull($actual);
    }

    public function test_registerSSOUser_notDefault(){
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();

        $this->obj->DBs = $this->getMockBuilder("DBs")
        ->onlyMethods(['getDBConnection'])
        ->getMock();

        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

        $this->obj->DBs->GUARDIAN_DEV->method('insert_id')->willReturn($id);

        $this->obj->DBs
        ->method('getDBConnection')
        ->willReturn($this->obj->DBs->GUARDIAN_DEV);

        MonkeyPatch::patchConstant(
        'CAPDEV_SCHEMA',
        'GUARDIAN'
        );

        // Call and test
        $actual = $this->obj->registerSSOUser('test@test.com', 'Active','CAPDEV');

        // echo json_encode($actual);

        $this->assertNull($actual);
    }

    public function test_updateAccountStatus(){

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
        ->method('update')
        ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->updateAccountStatus('1', 'Active');

        // echo json_encode($actual);

        $this->assertNull($actual);
    }

    public function test_get_user_table(){
        $expected = "users";
        $actual = $this->obj->get_user_table();
        $this->assertEquals($expected, $actual);
    }

    public function test_get_SSO_table(){
        $expected = "users_SSO";
        $actual = $this->obj->get_SSO_table();
        $this->assertEquals($expected, $actual);
    }

    public function test_setUsersActiveByIds(){
        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
        ->method('update')
        ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->setUsersActiveByIds([1]);

        // echo json_encode($actual);

        $this->assertTrue($actual);
    }

    public function test_registerActiveUsers(){

        $data = [
            [
                "id"    => "1",
                "email" => 'test@test.com',
                "status" => "Active",
                "timestamp" => 0,
                "unique_identifier" => null
            ] 
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
        ->method('insert_batch')
        ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->registerActiveUsers($data);

        // echo json_encode($actual);

        $this->assertTrue($actual);
    }

    public function test_getUsersStatuses(){
        $result = [
            [
                "id"    => "1",
                "email" => 'test@test.com',
                "status" => "Active",
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
        $actual = $this->obj->getUsersStatuses();

        // echo json_encode($actual);

        $expected = $result;

        $this->assertEquals($expected, $actual);
    }

   
}

?>