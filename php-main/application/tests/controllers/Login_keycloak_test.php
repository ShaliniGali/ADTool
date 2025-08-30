<?php
/**
 * @group base
 */

class Login_keycloak_test extends RhombusControllerTestCase
{
    public function test_activate()
    {
        $rk = new Rhombus_Keycloak();

        $this->request->addCallable(
            function ($CI) use($rk) {
                $rhombus_keycloak = $this->getDouble(
                    'Rhombus_Keycloak' , [
                        'get_authenticate_url' => $rk->get_authenticate_url(0,'')
                    ]
                );
                $CI->rhombus_keycloak = $rhombus_keycloak;
            }
        );

        $this->request('GET', 'rb_kc/authenticate/0/default');
        $this->assertRedirect($rk->get_authenticate_url(0,''));
    }
}
