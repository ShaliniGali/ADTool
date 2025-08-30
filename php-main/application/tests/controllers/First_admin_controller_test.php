<?php
/**
 * @group base
 */
class First_admin_controller_test extends RhombusControllerTestCase {

	public function test_index_checks_empty() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
					]
				);
				$CI->Register_model = $Register_model;
			}
		);
		$actual = $this->request('GET', '/first_admin/index');
		$this->assertIsString('A PHP Error was encountered', $actual);
	}

	public function test_index_checks_not_empty() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => ['test'],
					]
				);
				$CI->Register_model = $Register_model;

				$directorymanager = $this->getDouble('directorymanager',
					[
						'deleteFile' => '',
					]
				);
				$CI->directorymanager = $directorymanager;
			}
		);

		$actual = $this->request('GET', '/first_admin/index');
		$this->assertRedirect('login');
	}

	public function test_create_accounts_noDataCheck() {

		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);
		
		$actual = $this->request('POST', '/first_admin/create_accounts', array());
		$this->assertSame('', $actual);
	}

    public function test_create_accounts_check_empty() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => ['Test'],
						'user_register' => true
					]
				);
				$CI->Register_model = $Register_model;
			}

		);

		$actual = $this->request('POST', '/first_admin/create_accounts');
		$expected = json_encode([
			'result' => 'error'
		]);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_create_accounts() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);

		$actual = $this->request('POST', '/first_admin/create_accounts', [
			'username' => 'test@rhombuspower.com',
			'password' => 'Password@123$#&2345',
			'password_confirmation' => 'Password@123$#&2345',
			'name' => 'Test User',
			'account_type' => 'USER',
			'message' => 'User'
		]);
		$expected = json_encode([
			'result' => 'first_success'
		]);
		$this->assertEquals($expected, $actual);
	}

	public function test_create_accounts_reject() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);

		$actual = $this->request('POST','/first_admin/create_accounts', [
			'username' => 'test@rhombuspower.com',
			'password' => 'Password@123$#&2345',
			'password_confirmation' => 'Password@123$#&2345',
			'name' => 'Test User',
			'account_type' => 'USER',
			'message' => 'User'
		]);
		$this->assertIsString($actual);
	}

	public function test_create_accounts_with_nonvalid_email() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);

		$actual = $this->request('POST','/first_admin/create_accounts', [
			'username' => 'test@invaliddomain.com',
			'password' => 'Password@123$#&2345',
			'password_confirmation' => 'Password@123$#&2345',
			'name' => 'Test User',
			'account_type' => 'USER',
			'message' => 'User'
		]);
		$expected = json_encode([
			'result' => 'validation_failure',
			'message' => [
				'email_check' => 'Unauthorized email domain.'
			]
		]);
		$this->assertEquals($expected, $actual);
	}

	public function test_create_accounts_with_weak_password() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);

		$actual = $this->request('POST','/first_admin/create_accounts', [
			'username' => 'test@rhombuspower.com',
			'password' => '123',
			'password_confirmation' => '123',
			'name' => 'Test User',
			'account_type' => 'USER',
			'message' => 'User'
		]);
		$expected = json_encode([
			'result' => 'validation_failure',
			'message' => [
				'password_strength' => 'Weak password.'
			]
		]);
		$this->assertEquals($expected, $actual);
	}

	public function test_create_accounts_with_nonmatching_password() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);

		$actual = $this->request('POST','/first_admin/create_accounts',[
			'username' => 'test@rhombuspower.com',
			'password' => 'Password@123$#&2345',
			'password_confirmation' => 'Password@123$#&2345Password@123$#&2345',
			'name' => 'Test User',
			'account_type' => 'USER',
			'message' => 'User'
		]);
		$expected = json_encode([
			'result' => 'validation_failure',
			'message' => [
				'password_confirmation_check' => 'Password does not match.'
			]
		]);
		$this->assertEquals($expected, $actual);
	}

	public function test_create_account_with_invalid_account_type() {
		$this->request->addCallable(
			function ($CI) {
				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;
			}
		);
		
        $actual = $this->request('POST','/first_admin/create_accounts',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'Undefined',
                    'message' => 'User'
		]);
		$expected = json_encode([
			'result' => 'validation_failure',
			'message' => [
				'account_type_check' => 'Invalid account type.'
			]
		]);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_create_accounts_with_form_validation() {
		
		$res2 = true;

		$this->request->addCallable(
			function ($CI) use ($res2) {
				$Login_model = $this->getDouble(
					'Login_model', [
						'dump_user' => true,
					]
				);
				$CI->Login_model = $Login_model;

				$Register_model = $this->getDouble('Register_model',
					[
						'check_empty' => [],
						'user_register' => TRUE,
					]
				);
				$CI->Register_model = $Register_model;

				$form_validation = $this->getDouble('Rhombus_Form_validation',
					[
						'run_rules' => [],
					]
				);
				$CI->form_validation = $form_validation;
			}
		);

        $actual = $this->request('POST','/first_admin/create_accounts',[
                    'username' => 'test@rhombuspower.com',
                    'password' => 'Password@123$#&2345',
                    'password_confirmation' => 'Password@123$#&2345',
                    'name' => 'Test User',
                    'account_type' => 'USER',
		]);


		$result = '{"result":"error"}';
        $this->assertEquals($result,$actual);
    }
	
}
