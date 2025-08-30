<?php
/**
 * @group base
 */
class Platform_One_model_with_mocks_test extends RhombusModelTestCase 
{
    private $decoded_token = [
        'exp' => 1652296972,
        'iat' => 1652296672,
        'auth_time' => 1652296671,
        'jti' => '5d043567-0732-4ed1-a3fa-ad725892967c',
        'iss' => 'https://dev-michael.rhombuspower.com:8005/auth/realms/new_realm',
        'aud' => 'account',
        'sub' => '8eba3b37-934b-49e2-8d3d-db55db4b198d',
        'typ' => 'Bearer',
        'azp' => 'new_client',
        'session_state' => '60e3f0e7-e885-407c-bd76-e15f0f307869',
        'acr' => 1,
        'resource_access' => [
            'new_client' => [
                    'roles' => [
                       'guardian_user'
                    ]
            ]
        ],
        'account' => [
            'roles' => [
                'manage-account',
                'manage-account-links',
                'view-profile'
            ]
        ],
        'scope' => 'email profile',
        'sid' => '60e3f0e7-e885-407c-bd76-e15f0f307869',
        'email_verified' => true,
        'users.group' => [
            '/Group-Full-Test/App'
        ],
        'name' => 'Michael Alaimo',
        'preferred_username' => 'michael.alaimo@rhombuspower.com',
        'given_name' => 'Michael',
        'family_name' => 'Alaimo',
        'email' => 'michael.alaimo@rhombuspower.com'
    ];

	public function setUp(): void {
		parent::setUp();

		// Get object to test.
		$this->obj = new Platform_One_model();

		// Inject mock object into the model.
		$this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

        $this->obj->DBs->GUARDIAN_DEV->method('update')->willReturn(true);
		$this->obj->DBs->GUARDIAN_DEV->method('insert_id')->willReturn(1);

		$this->obj->DBs->SOCOM_UI = $this->getMethodChainingDBMock();

        $this->obj->DBs->SOCOM_UI->method('update')->willReturn(true);
		$this->obj->DBs->SOCOM_UI->method('insert_id')->willReturn(1);

        $Login_model = $this
        ->getMockBuilder('Login_model')
        ->onlyMethods(['dump_user', 'user_info', 'get_key_info'])
        ->getMock();

        $this->obj->Login_model = $Login_model;

		$Generic = $this->getMockBuilder('Generic')
		->onlyMethods(['send_email'])
		->getMock();

		$Generic->method('send_email')->willReturn(true);

		$this->Generic = $Generic;

		MonkeyPatch::patchMethod(Rhombus_Platform_One::class, ['get_current_access_token' => json_decode(json_encode($this->decoded_token))]);
	}

	public function test_updateAccountStatus() {
		$id = 1;
		$status = AccountStatus::RegistrationPending;
		$account_type = 'USER';
		$enabled_layers = '10000';
		
        $result = $this->obj->updateAccountStatus($id, $status, $account_type, $enabled_layers);

		$this->assertNull($result);
	}

	public function test_user_activate_mfa() {
		$data = [
			'id' => 1,
			'account_type' => 'ADMIN',
			'enableLoginLayer' => 'Yes',
			'tfa' => [
				'gAuth' => 'Yes',
				'yubikey' => 'Yes',
				'cac' => 'Yes'
			]
		];
		$type = 'admin_verify';

		$this->obj->Login_model->
			method('user_info')->
			with(1)->
			willReturn([['id' => 1, 'email' => 'unit.testing@rhombuspower.com', 'status' => AccountStatus::RegistrationPending, 'login_layers' => '10000']]);


		$this->obj->Login_model->
			method('get_key_info')->willReturn([['recovery_key' => json_encode(['Recoverykeys' => [1,2,3,4]])]]);

		$result = $this->obj->user_activate($data, $type);

		$this->assertTrue($result);
	} 

	public function test_user_activate_no_mfa() {
		$data = [
			'id' => 1,
			'account_type' => 'ADMIN',
			'enableLoginLayer' => 'No'
		];
		$type = 'admin_verify';

		$this->obj->Login_model->
			method('user_info')->
			with(1)->
			willReturn([['id' => 1, 'email' => 'unit.testing@rhombuspower.com', 'status' => AccountStatus::RegistrationPending, 'login_layers' => '10000']]);


		$this->obj->Login_model->
			method('get_key_info')->willReturn([['recovery_key' => json_encode(['Recoverykeys' => [1,2,3,4]])]]);

		
		$result = $this->obj->user_activate($data, $type);

		$this->assertTrue($result);
	}

	public function test_user_activate_admin_fail() {
		$data = [
			'id' => 2,
			'account_type' => 'ADMIN',
			'enableLoginLayer' => 'No'
		];
		$type = 'admin_verify';

		$this->obj->Login_model->
			method('user_info')->
			with(2)->
			willReturn([['id' => 2, 'email' => 'unit.testing@rhombuspower.com', 'status' => AccountStatus::Blocked, 'login_layers' => '10000']]);
		
		$result = $this->obj->user_activate($data, $type);

		$this->assertFalse($result);
	}

	public function tests_user_activate_self_verify() {
		$data = [
			'id' => 2,
			'account_type' => 'USER',
			'enableLoginLayer' => 'No'
		];
		$type = 'self_verify';

		$result = $this->obj->user_activate($data, $type);

		$this->assertTrue($result);
	}

	public function test_registerPLATFORMONEUser() {
		$id = 1;
		$status = AccountStatus::RegistrationPending;

		$result = $this->obj->registerPLATFORMONEUser([
			'user_id' => $id,
			'jwt' => 'jwt',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
		]);

		$this->assertIsInt($result);
	}

	public function test_registerPLATFORMONEUser_notdefault() {
		$id = 1;
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

		$id = 1;
		$status = AccountStatus::RegistrationPending;

		$result = $this->obj->registerPLATFORMONEUser([
			'user_id' => $id,
			'jwt' => 'jwt',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
		],'CAPDEV');

		$this->assertIsInt($result);
	}

	public function test_registerPLATFORMONEUser_null_id() {
		$id = null;

		try {
			$this->obj->registerPLATFORMONEUser([
				'user_id' => $id,
				'jwt' => 'jwt'
			]);
		} catch (Throwable $e) { 
            $this->assertInstanceOf('CIPHPUnitTestRedirectException', $e);
			$this->assertLogged('error', 'Could not updateAccount for user');
        }
	}

	public function test_updateJwt() {
		$result = $this->obj->updateJwt();

		$this->assertTrue($result);
	}

	public function tests_updateAccount() {
		$result = [
			'id' => 1,
			'name' => 'Michael'
		];

		$dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('row_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);


		$result = $this->obj->updateAccount(1);

		$this->assertTrue($result);
	}

	public function test_updateJwt_error() {
		MonkeyPatch::patchMethod(Rhombus_Platform_One::class, ['get_current_access_token' => false]);

		try {
			$this->obj->updateAccount(1);
		} catch (Throwable $e) { 
            $this->assertInstanceOf('CIPHPUnitTestRedirectException', $e);
			$this->assertLogged('error', 'Could not updateAccount for user');
        }
	}

	public function test_userExists() {
		$email = 'testing_user@rhombuspuwer.com';
		$strict=true; 
		$table="users";

		$result_array = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$result_array->method('result_array')->willReturn([]);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($result_array);

		$result = $this->obj->userExists($email, $strict, $table);

		$this->assertIsArray($result);
	}

	public function test_promptAccountRegistration() {
		$email = 'michael.alaimo@rhombuspower.com';

		$result_array = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$result_array->method('result_array')->willReturn([['status' => AccountStatus::Active]]);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($result_array);

		$result = $this->obj->promptAccountRegistration($email);

		$this->assertEquals($result, AccountStatus::Active);
	}

	public function test_promptAccountRegistration_notdefault() {
		MonkeyPatch::patchConstant('CAPDEV_SCHEMA', 'GUARDIAN');

		$email = 'michael.alaimo@rhombuspower.com';

		$result_array = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$result_array->method('result_array')->willReturn([['status' => AccountStatus::Active]]);
		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($result_array);
		$this->obj->DBs->SOCOM_UI->method('get')->willReturn($result_array);

		$result = $this->obj->promptAccountRegistration($email,'CAPDEV');

		$this->assertEquals($result, AccountStatus::Active);
	}

	public function test_promptAccountRegistration_true() {
		$email = 'michael.alaimo@rhombuspower.com';

		$result_array = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$result_array->method('result_array')->willReturn([]);

		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($result_array);

		$result = $this->obj->promptAccountRegistration($email);

		$this->assertTrue($result);
	}

	public function test_get_PLATFORMONE_table() {
		$result = $this->obj->get_PLATFORMONE_table();
		$this->assertTrue(TRUE);
	}

	public function test_getUsersStatuses() {

		$result_array = $this->getMockBuilder('CI_DB_result')
			->disableOriginalConstructor()
			->getMock();
		$result_array->method('result_array')->willReturn([]);

		$this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($result_array);
		$result = $this->obj->getUsersStatuses();
		$this->assertIsArray($result);
	}

	public function test_registerActiveUsers() {

		$result = $this->obj->registerActiveUsers([1,2]);
		$this->assertNull($result);
	}

	public function test_setUsersActiveByIds() {

		$result = $this->obj->setUsersActiveByIds([1,2]);
		$this->assertTrue($result);
	}
}