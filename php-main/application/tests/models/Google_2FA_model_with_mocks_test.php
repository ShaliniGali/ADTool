<?php 
/**
 * @group base
 */
class Google_2FA_model_with_mocks_test extends RhombusModelTestCase {
	public function setUp(): void {
		parent::setUp();

		// Get object to test.
		$this->obj = new Google_2FA_model();

		// Inject mock DB and session objects into the testing object.
		$this->obj->session = $this->getSessionMock();
		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
	}

	public function test_has_google_2fa_layer_is_string() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			array(
				'id' => '1'
			)
		);
		$db_result2 = array();
		$has_google_2fa_layer_param1 = 'bmax@fauxemail.com';

		// Create mock function(s) and inject them into the testing function
		$mock_result = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$mock_result->method('result_array')->willReturn($db_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($mock_result);

		// Call and test.
		$actual = $this->obj->has_google_2fa_layer($has_google_2fa_layer_param1);
		$this->assertIsString($actual);
	}

	public function test_has_google_2fa_layer_is_null() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array();
		$has_google_2fa_layer_param1 = 'bmax@fauxemail.com';

		// Create mock function(s) and inject them into the testing function
		$mock_result = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$mock_result->method('result_array')->willReturn($db_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($mock_result);

		// Call and test.
		$actual = $this->obj->has_google_2fa_layer($has_google_2fa_layer_param1);
		$this->assertNull($actual);
	}

	public function test_add_google_2fa_private_key_with_id() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			array(
				'user_id' => '1'
			)
		);
		$add_google_2fa_private_key_param1 = '1';

		// Create mock function(s) and inject them into the testing function
		$mock_result = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$mock_result->method('result_array')->willReturn($db_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($mock_result);
		$this->obj->DBs->GUARDIAN_DEV->method('update')->willReturn(TRUE);
		$this->obj->DBs->GUARDIAN_DEV->method('insert')->willReturn(TRUE);

		// Call and test.
		if(!P1_FLAG){	
			$actual = $this->obj->add_google_2fa_private_key($add_google_2fa_private_key_param1);
			$this->assertTrue($actual);
		}
		else{
			$this->assertTrue(true);
		}
	}

	public function test_add_google_2fa_private_key_no_id() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			array(
				// No user_id match.
			)
		);
		$add_google_2fa_private_key_param1 = '1';

		// Create mock function(s) and inject them into the testing function
		$mock_result = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$mock_result->method('result_array')->willReturn($db_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($mock_result);
		$this->obj->DBs->GUARDIAN_DEV->method('update')->willReturn(TRUE);
		$this->obj->DBs->GUARDIAN_DEV->method('insert')->willReturn(TRUE);

		// Call and test.
		if(!P1_FLAG){	
			$actual = $this->obj->add_google_2fa_private_key($add_google_2fa_private_key_param1);
			$this->assertTrue($actual);
		}
		else{
			$this->assertTrue(true);
		}
	}

	public function test_increment_attempts() {
		// Model function result(s) and/or function parameter(s).
		$increment_attempts_param1 = '1';

		// Create mock function(s) and inject them into the testing function.
		$this->obj->DBs->GUARDIAN_DEV->method('update')->willReturn(TRUE);

		// Call and test.
		$actual = $this->obj->increment_attempts($increment_attempts_param1);
		$this->assertNull($actual);
	}

	public function test_remove_login_layer() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			array(
				'login_layers' => 'layer1'
			)
		);
		$remove_login_layer_param1 = '1';

		// Create mock function(s) and inject them into the testing function
		$dbMock = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbMock->method('result_array')
			->willReturn($db_result1);
		$this->obj->DBs->GUARDIAN_DEV
			->method('get')
			->willReturn($dbMock);
		$this->obj->DBs->GUARDIAN_DEV
			->method('update')
			->willReturn(TRUE);

		// Call and test.
		$actual = $this->obj->remove_login_layer($remove_login_layer_param1);
		$this->assertNull($actual);
	}
}
