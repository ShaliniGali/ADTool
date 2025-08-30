<?php 
/**
 * @group base
 */
class DB_ind_model_with_mocks_test extends RhombusModelTestCase 
{
	public function setUp(): void {
		parent::setUp();

		// Get object to test.
		$this->obj = new DB_ind_model();

		// Inject mock object into the model.
		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
	}

	public function test_check_login_notempty() {
		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
			
		$this->obj->session = $this
			->getMockBuilder('CI_Session')
			->disableOriginalConstructor()
			->getMock();

			$this->obj->session->method('userdata')->willReturnOnConsecutiveCalls(
				array(
					'id' => 'test_id1'
				),
				'Test'
			);
			$this->obj->session->method('has_userdata')->willReturn(TRUE);

			try {
				$actual = $this->obj->check_login();
			} 
			catch (Throwable $e) { 
				$this->assertInstanceOf('CIPHPUnitTestRedirectException', $e);

			}
	}

	public function test_check_login_isempty() {
		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
			
		$this->obj->session = $this
			->getMockBuilder('CI_Session')
			->disableOriginalConstructor()
			->getMock();

			$this->obj->session->method('userdata')->willReturn(
				array()
			);
			$this->obj->session->method('has_userdata')->willReturn(FALSE);

			try {
				$actual = $this->obj->check_login();
			} 
			catch (Throwable $e) { 
				$this->assertInstanceOf('CIPHPUnitTestRedirectException', $e);
			}
	}

	public function test_check_login_assert_null() {
		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
			
		$this->obj->session = $this
			->getMockBuilder('CI_Session')
			->disableOriginalConstructor()
			->getMock();

			$this->obj->session->method('userdata')->willReturn(
				array(
					'id' => 'test_id1'
				)
			);
			$this->obj->session->method('has_userdata')->willReturn(FALSE);
			$actual = $this->obj->check_login();
			$this->assertNull($actual);
	}

	public function test_validate_post() {
		$result = [];
		$param = ['id' => 'test_id1','id2' => array('test_id2')];

		// Call and test.
		$actual = $this->obj->validate_post($param);
		$expected = '{"result":"test_id11","post_data":{"id":"test_id1","id2":["test_id2"]}}';
		$this->assertIsArray($actual);
		$this->assertEquals(json_decode($expected, TRUE), $actual);
	}

}