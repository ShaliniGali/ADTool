<?php 
/**
 * @group base
 */
class Login_token_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Login_token_model();

        // Inject mock object into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

    }

    // Test for has_login_token_layer with data
    // 
    public function test_has_login_token_layer_int() {
        $result = [
            [
                "id"    => 2,
            ] 
        ];

        $id = 2;
        $status = 'Login_layer';

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        // Call and test
        $actual = $this->obj->has_login_token_layer($id,$status);

        // For debugging
        // echo json_encode($actual);

        $this->assertIsInt($actual);

    }

    // Test for has_login_token_layer with empty data
    // 
    public function test_has_login_token_layer_null() {
        $result = [ 
        ];

        $id = 2;
        $status = "Login_layer";

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        // Call and test
        $actual = $this->obj->has_login_token_layer($id,$status);

        // For debugging
        // echo json_encode($actual);

        $this->assertNull($actual);

    }

    // Test for get_login_token with data
    // 
    public function test_get_login_token() {
        $result = [ 
            [
                "login_token"    => "some_random_string",
            ] 
        ];

        $id = 2;
        $status = "Login_layer";

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        // Call and test
        $actual = $this->obj->get_login_token($id,$status);

        // For debugging
        // echo json_encode($actual);

        $this->assertIsString($actual);

    }

    // Test for generate_login_token with TRUE
    // 
    public function test_generate_login_token() {

        $result1 = [ 
            [
                "user_id"    =>2,
            ] 
        ];


        $user_id = 2;
        $key = "Active";

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result1);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
            $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->generate_login_token($user_id,$key);

        // For debugging
        // echo json_encode($actual);
        // exit;
        $this->assertTrue($actual);

    }


    // Test for delete_login_token with TRUE
    //
    public function test_delete_login_token() {

        $user_id = 2;

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(TRUE);
    
        // Call and test
        $actual = $this->obj->delete_login_token($user_id);

        // For debugging
        // echo var_dump($actual);

        $this->assertTrue($actual);

    }
}

?>