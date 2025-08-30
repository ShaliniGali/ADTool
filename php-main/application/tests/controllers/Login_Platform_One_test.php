<?php
/**
 * @group base
 */

class Login_Platform_One_test extends RhombusControllerTestCase
{
    public function test_authenticate()
    {
        $this->request('GET', 'rb_p1/authenticate/0/default');
        $this->assertRedirect(PLATFORM_ONE_SSO_URL . '/0/default');
    }
}