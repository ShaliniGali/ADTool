<?php 
/**
 * @group base
 */
class Keycloak_tiles_model_with_mocks_test extends RhombusModelTestCase 
{

    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Keycloak_tiles_model();

        // Inject mock object into the model
        $this->obj->DBs->KEYCLOAK_TILE = $this->getMethodChainingDBMock();
        $this->obj->DBs->GUARDIAN = $this->getMethodChainingDBMock();
    }

    
    public function test_get_tiles() {
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
		$actual = $this->obj->get_tiles(['title'=>'abc']);
		$this->assertIsArray($actual);
    }

    public function test_update_tile_status() {
        $id = 2;
        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_tile_status($id);
        $this->assertNull($actual);
    }

    public function test_update_tiles_record() {
        $data = [
            'id' => 2
        ];
        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->update_tiles_record($data);
        $this->assertNull($actual);
    }

    public function test_insert_tiles_record() {
        $data = [
            'id' => 2
        ];
        $this->obj->DBs->KEYCLOAK_TILE->method('insert')->willReturn(TRUE);
        $actual = $this->obj->insert_tiles_record($data);
        $this->assertNull($actual);
    }

    public function test_convert_tile_data_json() {
        $result = [
			[
				'id' => 2,
                'title' => 'CAPDEV',
                'status' => AccountStatus::Active,
                'icon' => 'icon',
                'note' => 'note',
                'description' => 'description',
                'group' => 'group',
                'deployment' => '["SIPR"]',
                'color' => 2,
                'label' => 'CAPDEV'
			]
		];

        MonkeyPatch::patchMethod(
            'Keycloak_tiles_model',
            [
                'get_tiles' => $result
            ]
        );

        MonkeyPatch::patchMethod(
			'DBsCore',
			['getDBConnection' => $this->obj->DBs->GUARDIAN]
		);

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->GUARDIAN
			->method('get')
			->willReturn($dbResult);

		// Call and test
		$actual = $this->obj->convert_tile_data_json();
		$this->assertIsArray($actual);
    }
    public function test_convert_tile_data_json_nipr() {
        $tiles = [[
            'id' => 2,
            'title' => 'CAPDEV',
            'status' => AccountStatus::Active,
            'icon' => 'icon',
            'note' => 'note',
            'description' => 'description',
            'group' => 'group',
            'deployment' => '["NIPR"]',
            'color' => 2,
            'label' => 'CAPDEV'
		]];
        $result = [[
            'status' => 'Active'
        ]];

        MonkeyPatch::patchMethod(
            'Keycloak_tiles_model',
            [
                'get_tiles' => $tiles
            ]
        );

        MonkeyPatch::patchMethod(
			'DBsCore',
			['getDBConnection' => $this->obj->DBs->GUARDIAN]
		);

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->GUARDIAN
			->method('get')
			->willReturn($dbResult);

		// Call and test
		$actual = $this->obj->convert_tile_data_json();
		$this->assertIsArray($actual);
    }
    
    public function test_save_favourites() {
        $result = [
			[
				'id' => 2,
                'title' => 'CAPDEV',
                'status' => AccountStatus::Active,
                'icon' => 'icon',
                'note' => 'note',
                'description' => 'description',
                'group' => 'group',
                'deployment' => '["NIPR"]',
                'color' => 2,
                'label' => 'CAPDEV'
			]
		];

        $dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->KEYCLOAK_TILE
			->method('get')
			->willReturn($dbResult);

        $param = [
            2
        ];
        $this->obj->DBs->KEYCLOAK_TILE->method('update')->willReturn(TRUE);
        $actual = $this->obj->save_favourites($param);
        $this->assertTrue($actual);
    }

    public function test_save_favourites_empty() {
        $result = [
		];

        $dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->KEYCLOAK_TILE
			->method('get')
			->willReturn($dbResult);

        $param = [
            2
        ];
        $this->obj->DBs->KEYCLOAK_TILE->method('insert')->willReturn(TRUE);
        $actual = $this->obj->save_favourites($param);
        $this->assertTrue($actual);
    }

    public function test_get_app_users() {
        $result = [
			[
				'id' => 2,
                'title' => 'CAPDEV',
                'status' => AccountStatus::Active,
                'icon' => 'icon',
                'note' => 'note',
                'description' => 'description',
                'group' => 'group',
                'deployment' => '["SIPR"]',
                'color' => 2,
                'label' => 'CAPDEV'
			]
		];

		MonkeyPatch::patchConstant(
			'DEPLOYMENT_ENVIRONMENT',
			'SIPR'
		);

        MonkeyPatch::patchMethod(
            'Keycloak_tiles_model',
            [
                'get_tiles' => $result
            ]
        );

        MonkeyPatch::patchMethod(
			'DBsCore',
			['getDBConnection' => $this->obj->DBs->GUARDIAN]
		);

        $user_result = [
            [
                'email' => 'test1@rhombuspower.com',
                'status' => 'Registration_pending',
                'subapp_id' => 2
            ],
            [
                'email' => 'test2@rhombuspower.com',
                'status' => 'Active',
                'subapp_id' => 2
            ]
        ];

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($user_result);

		$this->obj->DBs->GUARDIAN
			->method('get')
			->willReturn($dbResult);

        $actual = $this->obj->get_app_users();
        
        $expected = '{"Registration_pending":{"test1@rhombuspower.com":[{"label":"CAPDEV","account_type":null}]},"Active":{"test2@rhombuspower.com":[{"label":"CAPDEV","account_type":null}]}}';
       
        $this->assertEquals(json_encode($actual), $expected);
    }

    public function test_registerUserOnSubApp_insert() {
        $result = [];

        MonkeyPatch::patchMethod(
			'DBsCore',
			['getDBConnection' => $this->obj->DBs->GUARDIAN]
		);

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->GUARDIAN
			->method('get')
			->willReturn($dbResult);
            
        $this->obj->DBs->GUARDIAN
			->method('insert')
			->willReturn(TRUE);

		// Call and test
		$actual = $this->obj->registerUserOnSubApp([], 'schema');
        $this->assertNull($actual);
    }
    public function test_registerUserOnSubApp_update() {
        $result = [[
            'id' => 1
        ]];

        MonkeyPatch::patchMethod(
			'DBsCore',
			['getDBConnection' => $this->obj->DBs->GUARDIAN]
		);

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->GUARDIAN
			->method('get')
			->willReturn($dbResult);
            
        $this->obj->DBs->GUARDIAN
			->method('update')
			->willReturn(TRUE);

		// Call and test
		$actual = $this->obj->registerUserOnSubApp([], 'schema');
        $this->assertNull($actual);
    }

    public function test_getSubappIdfromName_empty() {
        $result = [];

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->KEYCLOAK_TILE
			->method('get')
			->willReturn($dbResult);

		// Call and test
		$actual = $this->obj->getSubappIdfromName('name');
        $this->assertFalse($actual);
    }
    public function test_getSubappIdfromName() {
        $result = [
            ['id' => 5]
        ];

		$dbResult = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$dbResult->method('result_array')->willReturn($result);

		$this->obj->DBs->KEYCLOAK_TILE
			->method('get')
			->willReturn($dbResult);

		// Call and test
		$actual = $this->obj->getSubappIdfromName('name');
        $this->assertEquals(5, $actual);
    }
    
}