<?php 
/**
 * @group base
 */
class Role_mappings_manager_model_with_mocks_test extends RhombusModelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Role_mappings_manager_model();

        // Inject mock object into the model
        $this->obj->DBs->KEYCLOAK_TILE = $this->getMethodChainingDBMock();

    }

    public function test_get_role_mappings() {

        $result = [
			[
				'id' => 2,
                'app_id' => 2,
                'subapp_id' => 3,
                'user_role_id' => '[2]'
			]
		];

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->KEYCLOAK_TILE
			->method('get')
			->willReturn($dbResult);

		// Call and test
		$actual = $this->obj->get_role_mappings();
		$this->assertIsArray($actual);
    }


    public function test_insert_role_mappings_record() {

        $data = [
            'id' => 2,
            'app_id' => 2,
            'subapp_id' => 3,
            'user_role_id' => '[1,2]'
        ];
        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->insert_role_mappings_record($data);
        $this->assertNull($actual);
    }

    public function test_clear_role_mappings_user_roles() {


        $this->obj->DBs->KEYCLOAK_TILE->method('insert')->willReturn(TRUE);
        $actual = $this->obj->clear_role_mappings_user_roles(2);
        $this->assertNull($actual);
    }

    public function test_update_role_mappings_record() {

        $insert_data = [
            'app_id' => 2,
            'subapp_id' => 3,
            'feature_id' => 4,
            'user_role_id' => '[1,2]',
            'id' => 1
        ];

        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_role_mappings_record($insert_data);
        $this->assertNull($actual);
    }

    public function test_get_basic_role_mappings() {
        $result = [
			[
				'id' => 2,
                'app_id' => 2,
                'subapp_id' => 3,
                'user_role_id' => '[2]'
			]
		];

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->KEYCLOAK_TILE
			->method('get')
			->willReturn($dbResult);

        $actual = $this->obj->get_basic_role_mappings();
        $this->assertIsArray($actual);
    }
}
?>