<?php 
/**
 * @group base
 */
class Generic_with_mocks_test extends RhombusModelTestCase {
	public function setUp(): void {
		parent::setUp();

		// Get object to test.
		$this->obj = new Generic();

		// Inject mock DB and session objects into the testing object.
		$this->obj->session = $this->getSessionMock();
		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
		$this->obj->DBs->SOCOM_UI = $this->getMethodChainingDBMock();
	}

	public function test_validatePassword() {
		// Call and test.
		$actual = $this->obj->validatePassword();
		$this->assertIsString($actual);
	}

	public function test_send_email_if_curl_exec_failure() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			'user_id' => 'id1',
			'type' => 'type1',
			'new_info' => 'info1',
			'old_info' => '',
			'timestamp' => '12:30:00'
		);
		$send_email_param1 = array(
			'receiverEmail' => 'bmax@fauxemail.com',
			'subject' => 'Subject',
			'content' => 'content'
		);
		$userdata_result1 = array(
			'id' => '1'
		);

		// Create mock function(s) and inject them into the testing function
		// Note: we are redefining $this->obj in this situation because
		//		 we need to mock a method in the same class being tested.
		$this->obj = $this->getMockBuilder('Generic')
			->onlyMethods(['get_curl_exec'])
			->getMock();
		$this->obj->expects($this->any())
			->method('get_curl_exec')
			->willReturn(FALSE);
		$this->obj->session = $this->getSessionMock();
		$this->obj->session->method('has_userdata')->willReturn(TRUE);
		$this->obj->session->method('userdata')->willReturn($userdata_result1);
		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
		$this->obj->DBs->GUARDIAN_DEV->method('insert_id')->willReturn(TRUE);

		// Patch mock values into real constants for the testing function.
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND',
			'TRUE',
			Generic::class . '::send_email'
        );
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND_SMTP',
			'FALSE',
			Generic::class . '::send_email'
        );

		// Call and test.
		$actual = $this->obj->send_email($send_email_param1);
		$this->assertFalse($actual);
	}

	public function test_send_email_else_curl_exec_success() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			'user_id' => 'id1',
			'type' => 'type1',
			'new_info' => 'info1',
			'old_info' => '',
			'timestamp' => '12:30:00'
		);
		$send_email_param1 = array(
			'bmax@fauxemail.com'
		);
		$userdata_result1 = array(
			'id' => '1'
		);

		// Create mock function(s) and inject them into the testing function
		// Note: we are redefining $this->obj in this situation because
		//		 we need to mock a method in the same class being tested.
		$this->obj = $this->getMockBuilder('Generic')
			->onlyMethods(['get_curl_exec'])
			->getMock();
		$this->obj->expects($this->any())
			->method('get_curl_exec')
			->willReturn('success');
		$this->obj->session = $this->getSessionMock();
		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
		$this->obj->session->method('has_userdata')->willReturn(TRUE);
		$this->obj->session->method('userdata')->willReturn($userdata_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('insert_id')->willReturn(TRUE);

		// Patch mock values into real constants for the model fu.
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND',
			'TRUE', 
			Generic::class . '::send_email'
        );
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND_SMTP',
			'FALSE',
			Generic::class . '::send_email'
        );

		// Call and test.
		$actual = $this->obj->send_email($send_email_param1);
		$this->assertIsArray($actual);
	}

	public function test_send_email_else_curl_exec_success_not_default() {
		// Model function result(s) and/or function parameter(s).
		$db_result1 = array(
			'user_id' => 'id1',
			'type' => 'type1',
			'new_info' => 'info1',
			'old_info' => '',
			'timestamp' => '12:30:00'
		);
		$send_email_param1 = array(
			'bmax@fauxemail.com'
		);
		$userdata_result1 = array(
			'id' => '1'
		);

		// Create mock function(s) and inject them into the testing function
		// Note: we are redefining $this->obj in this situation because
		//		 we need to mock a method in the same class being tested.
		$this->obj = $this->getMockBuilder('Generic')
			->onlyMethods(['get_curl_exec'])
			->getMock();
		$this->obj->expects($this->any())
			->method('get_curl_exec')
			->willReturn('success');
		$this->obj->session = $this->getSessionMock();
		$this->obj->DBs = $this->getMockBuilder("Dbs")
			->onlyMethods(['getDBConnection'])
			->getMock();
		$this->obj->DBs->expects($this->any())
			->method('getDBConnection')
			->willReturn($this->getMethodChainingDBMock());

		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
		$this->obj->session->method('has_userdata')->willReturn(TRUE);
		$this->obj->session->method('userdata')->willReturn($userdata_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('insert')->willReturn(TRUE);
		$this->obj->DBs->GUARDIAN_DEV->method('insert_id')->willReturn(TRUE);

		// Patch mock values into real constants for the model fu.
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND',
			'TRUE', 
			Generic::class . '::send_email'
        );
		MonkeyPatch::patchConstant(
			'UI_EMAIL_SEND_SMTP',
			'FALSE',
			Generic::class . '::send_email'
        );
		MonkeyPatch::patchConstant(
			'CAPDEV_SCHEMA',
			'GUARDIAN'
		);

		// Call and test.
		$actual = $this->obj->send_email($send_email_param1,null,'CAPDEV');
		$this->assertIsArray($actual);
	}

	public function test_dump_users_info_if_user_exists() {
		// Model function result(s) and/or function parameter(s).
		$dump_users_info_param1 = array(
			'type' => 'type1',
			'new_info' => 'info1',
			'old_info' => '',
		);
		$dump_users_info_param2 = '1';
		$userdata_result1 = array(
			'id' => '1'
		);

		// Create mock function(s) and inject them into the testing function
		$this->obj->session->method('has_userdata')->willReturn(TRUE);
		$this->obj->session->method('userdata')->willReturn($userdata_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('insert')->willReturn(TRUE);

		// Call and test.
		$actual = $this->obj->dump_users_info($dump_users_info_param1, $dump_users_info_param2);
		$this->assertNull($actual);
	}

	public function test_dump_users_info_else_user_is_null() {
		// Model function result(s) and/or function parameter(s).
		$dump_users_info_param1 = array(
			'type' => 'type1',
			'new_info' => 'info1',
			'old_info' => '',
		);
		$userdata_result1 = array(
			'id' => '1'
		);

		// Create mock function(s) and inject them into the testing function
		$this->obj->session->method('has_userdata')->willReturn(TRUE);
		$this->obj->session->method('userdata')->willReturn($userdata_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('insert')->willReturn(TRUE);

		// Call and test.
		$actual = $this->obj->dump_users_info($dump_users_info_param1);
		$this->assertNull($actual);
	}

	public function test_dump_users_info_else_user_is_null_empty() {
		// Model function result(s) and/or function parameter(s).
		$dump_users_info_param1 = array(
			'type' => 'type1',
			'new_info' => 'info1',
			'old_info' => '',
		);
		$userdata_result1 = array(
			'id' => '1'
		);

		// Create mock function(s) and inject them into the testing function
		$this->obj->DBs = $this->getMockBuilder("Dbs")
			->onlyMethods(['getDBConnection'])
			->getMock();
		$this->obj->DBs->expects($this->any())
			->method('getDBConnection')
			->willReturn($this->getMethodChainingDBMock());

		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
		$this->obj->session->method('has_userdata')->willReturn(TRUE);
		$this->obj->session->method('userdata')->willReturn($userdata_result1);
		$this->obj->DBs->GUARDIAN_DEV->method('insert')->willReturn(TRUE);

		MonkeyPatch::patchConstant(
			'CAPDEV_SCHEMA',
			'GUARDIAN'
		);

		// Call and test.
		$actual = $this->obj->dump_users_info($dump_users_info_param1,null,'CAPDEV');
		$this->assertNull($actual);
	}

	public function test_get_curl_exec_failure() {
		$get_curl_exec_param1 = array(
			"server_ip" => 'server_ip1',
			"key" => 'email_api_key1',
			"content" => 'curl_content1'
		);
		
		// Call and test.
		$actual = $this->obj->get_curl_exec($get_curl_exec_param1);
		$this->assertFalse($actual);
	}
}