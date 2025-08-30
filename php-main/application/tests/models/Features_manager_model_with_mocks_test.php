<?php 
/**
 * @group base
 */
class Features_manager_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Features_manager_model();

        // Inject mock object into the model
        $this->obj->DBs->KEYCLOAK_TILE = $this->getMethodChainingDBMock();

    }

    public function test_get_features() {

        $result = [
			[
				'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
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
		$actual = $this->obj->get_features();
		$this->assertIsArray($actual);
    }

    public function test_update_features_status() {

        $id = 1;
        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_features_status($id);
        $this->assertNull($actual);
    }

    public function test_insert_features_record() {

        $insert_data = [
            'app_id' => 1,
            'subapp_id' => 1,
            'feature_id' => 1,
            'user_role_id' => '[1,2]'
        ];

        $this->obj->DBs->KEYCLOAK_TILE->method('insert')->willReturn(TRUE);
        $actual = $this->obj->insert_features_record($insert_data);
        $this->assertNull($actual);
    }

    public function test_update_features_record() {

        $insert_data = [
            'name' => 'test',
            'id' => 1
        ];

        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_features_record($insert_data);
        $this->assertNull($actual);
    }
}
?>