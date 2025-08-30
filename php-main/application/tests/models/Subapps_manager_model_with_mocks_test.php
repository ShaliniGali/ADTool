<?php 
/**
 * @group base
 */
class Subapps_manager_model_with_mocks_test extends RhombusModelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Subapps_manager_model();

        // Inject mock object into the model
        $this->obj->DBs->KEYCLOAK_TILE = $this->getMethodChainingDBMock();

    }

    public function test_get_subapps() {

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
		$actual = $this->obj->get_subapps();
		$this->assertIsArray($actual);
    }


    public function test_update_subapps_status() {

        $id = 1;
        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_subapps_status($id);
        $this->assertNull($actual);
    }

    public function test_insert_subapps_record() {

        $insert_data = [
            'name' => 'test'
        ];

        $this->obj->DBs->KEYCLOAK_TILE->method('insert')->willReturn(TRUE);
        $actual = $this->obj->insert_subapps_record($insert_data);
        $this->assertNull($actual);
    }

    public function test_update_subapps_record() {

        $insert_data = [
            'name' => 'test1',
            'id' => 1
        ];

        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_subapps_record($insert_data);
        $this->assertNull($actual);
    }
}
?>