​​​​​​​​​​​​​​​<?php 
/**
 * @group base
 */
class Register_activate_test extends RhombusControllerTestCase
{
    public function test_activate()
    {
        $this->request('GET', 'Register_activate/activate/test');
        $this->assertRedirect(RHOMBUS_BASE_URL);

        $this->request->addCallable(
            function ($CI) {
                $userInfoResult = [
                    [
                        'name' => 'Unit Tester',
                        'status' => 'Registration_pending',
                        'email' => 'unit_tester@rhombuspower.com'
                    ]
                ];
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_info' => $userInfoResult
                    ]
                );

                $CI->Login_model = $Login_model;
            }
        );
        $hash = encrypted_string([
            'id' => 1,
            'status' => 'Registration_pending',
            'email' => 'unit_tester@rhombuspower.com',
            'message' => 'test',
            'account_type' => 'User',
        ], 'encode');
        $actual = $this->request('GET', 'Register_activate/activate/' . $hash);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }
}
