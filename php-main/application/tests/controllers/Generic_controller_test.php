<?php
/**
 * @group base
 */
class Generic_controller_test extends RhombusControllerTestCase {
	
	public function test_dump_accounts() {
		// Model function result(s) and parameter(s) if any.
		$encrypt_decrypt_passwords_result1 = array(
			'password' => '12345',
			'salt' => 'pepper'
		);
		$dump_accounts_param1 = 1;

		// Add model function(s) and inject them into the model.
		$this->request->addCallable(
			function ($CI) use ($encrypt_decrypt_passwords_result1) {
				$DB_ind_model = $this->getDouble('DB_ind_model',
					[
						'encrypt_decrypt_passwords' => $encrypt_decrypt_passwords_result1
					]
				);
				$CI->DB_ind_model = $DB_ind_model;
			}
		);

		// Patch mock values into real constants for the testing function.
		MonkeyPatch::patchConstant(
			'RHOMBUS_PASSWORD_GENERATOR',
			'TRUE',
			Generic_controller::class . '::dump_accounts'
        );

		// Test function output against an assertion.
		$actual = $this->request('POST', 'Generic_controller/dump_accounts/' . $dump_accounts_param1,
			[
				'test' => 'test'
			]
		);
		$this->assertStringNotContainsString('A PHP Error was encountered', $actual);
	}
}