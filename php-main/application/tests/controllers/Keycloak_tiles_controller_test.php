<?php
/**
 * @group base
 */

class Keycloak_tiles_controller_test extends RhombusControllerTestCase
{
    
    private $token = [
        'access_token' => "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJJMlUwckJOc24wSDhhR2xQREZSemJhcVBOTFpqcmNmLVlqU3VqZEwxZWZNIn0.eyJleHAiOjE2NTIyOTY5NzIsImlhdCI6MTY1MjI5NjY3MiwiYXV0aF90aW1lIjoxNjUyMjk2NjcxLCJqdGkiOiI1ZDA0MzU2Ny0wNzMyLTRlZDEtYTNmYS1hZDcyNTg5Mjk2N2MiLCJpc3MiOiJodHRwczovL2Rldi1taWNoYWVsLnJob21idXNwb3dlci5jb206ODAwNS9hdXRoL3JlYWxtcy9uZXdfcmVhbG0iLCJzdWIiOiI4ZWJhM2IzNy05MzRiLTQ5ZTItOGQzZC1kYjU1ZGI0YjE5OGQiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJuZXdfY2xpZW50Iiwic2Vzc2lvbl9zdGF0ZSI6IjYwZTNmMGU3LWU4ODUtNDA3Yy1iZDc2LWUxNWYwZjMwNzg2OSIsImFjciI6IjEiLCJyZXNvdXJjZV9hY2Nlc3MiOnsibmV3X2NsaWVudCI6eyJyb2xlcyI6WyJHdWFyZGlhbiBVc2VyIl19fSwic2NvcGUiOiJlbWFpbCBwcm9maWxlIiwic2lkIjoiNjBlM2YwZTctZTg4NS00MDdjLWJkNzYtZTE1ZjBmMzA3ODY5IiwiZW1haWxfdmVyaWZpZWQiOnRydWUsIm5hbWUiOiJNaWNoYWVsIEFsYWltbyIsInByZWZlcnJlZF91c2VybmFtZSI6Im1pY2hhZWwuYWxhaW1vQHJob21idXNwb3dlci5jb20iLCJ1c2Vycy5ncm91cCI6WyIvR3JvdXAtRnVsbC1UZXN0L0FwcCJdLCJnaXZlbl9uYW1lIjoiTWljaGFlbCIsImZhbWlseV9uYW1lIjoiQWxhaW1vIiwiZW1haWwiOiJtaWNoYWVsLmFsYWltb0ByaG9tYnVzcG93ZXIuY29tIn0.AZJhO5E5NdQgbs68LbLpJCoWCsx6gabe4duxscvl83ZhAcM0NJJQPngxUz-Y3Yz1X-6ypA4uB8X3Jtngp-iCSjyzYk_0VuBC4ujAcxipzxjYihpL4wIkAy4Au9HoFZ2WYmfialbrxYmdKU9xSrXjyICh58dVKi1D4ZuLKx2LuVh_w540QXjBMi8LTCHg0EfPwLjM09AB61DHaPDQgYOJuXKqWanKJ-aj3uINVpT8ezGb9OG6TREt4cPWRo_5AG5ykaVr_Yjmb2mGN0HIoMucJM0ywUqeJZJhU6uqf_QDLBo05ztTVyWbpoeBIL2cVxQbL-YkzYExYTD7M9z8JDCPfQ",
        'expires_in' => 300,
        'refresh_expires_in' => 1800,
        'refresh_token' => "eyJhbGciOiJIUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICI2YmIyMWI5Mi03N2IyLTRjNDQtYjg3MS00NjYyOTMwMzZhM2YifQ.eyJleHAiOjE2NTIyOTg0NzIsImlhdCI6MTY1MjI5NjY3MiwianRpIjoiMDZkYjE1ZTEtOTYwNi00MzJhLWJjNjctOTQ4YjQ2ZTQ2MjIyIiwiaXNzIjoiaHR0cHM6Ly9kZXYtbWljaGFlbC5yaG9tYnVzcG93ZXIuY29tOjgwMDUvYXV0aC9yZWFsbXMvbmV3X3JlYWxtIiwiYXVkIjoiaHR0cHM6Ly9kZXYtbWljaGFlbC5yaG9tYnVzcG93ZXIuY29tOjgwMDUvYXV0aC9yZWFsbXMvbmV3X3JlYWxtIiwic3ViIjoiOGViYTNiMzctOTM0Yi00OWUyLThkM2QtZGI1NWRiNGIxOThkIiwidHlwIjoiUmVmcmVzaCIsImF6cCI6Im5ld19jbGllbnQiLCJzZXNzaW9uX3N0YXRlIjoiNjBlM2YwZTctZTg4NS00MDdjLWJkNzYtZTE1ZjBmMzA3ODY5Iiwic2NvcGUiOiJlbWFpbCBwcm9maWxlIiwic2lkIjoiNjBlM2YwZTctZTg4NS00MDdjLWJkNzYtZTE1ZjBmMzA3ODY5In0.jjraL1Y2CunQTUQfbxdBkm6Dkk7lt3k_iMEyLeRoCSg",
        'token_type' => "Bearer",
        'not-before-policy' => 0,
        'session_state' => "60e3f0e7-e885-407c-bd76-e15f0f307869",
        'scope' => "email profile"
    ];
    
    public function test_index() {
        // user is super admin
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'convert_tile_data_json' => 'tiles',
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);

        // //$actual = $this->request('GET', '/kc_tiles');
        $actual = $this->request('GET', '/kc_tiles');
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
        $this->assertTrue(true);
    }

    public function test_tile_login_app_schema_is_false() {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model' , [
                        'promptAccountRegistration' => false,
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);

        $actual = $this->request('POST', '/kc_tiles/login',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'app_label' => true

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_tile_login_status_fail() {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model' , [
                        'promptAccountRegistration' => false,
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);

        $actual = $this->request('POST', '/kc_tiles/login',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'app_label' => true

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_tile_login_with_status_success() {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model' , [
                        'promptAccountRegistration' => false,
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);


        MonkeyPatch::patchMethod(
            AccountStatus::class,
            ['hasStatus' => true]
        );

        $actual = $this->request('POST', '/kc_tiles/login',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'app_label' => true

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_request_register_app_schema_is_false() {
        MonkeyPatch::patchFunction('constant', false, Keycloak_tiles_controller::class);
        MonkeyPatch::patchConstant('ADMIN_EMAILS', ['test@rhombuspower.com']);
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);

        $actual = $this->request('POST', '/kc_tiles/register',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'applications' => 1

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_request_register_invalid_email() {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model' , [
                        'promptAccountRegistration' => false,
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;

                $Rhombus_Platform_One = $this->getDouble(
                    'Rhombus_Platform_One' , [
                        'can_access_page' => true,
                        'get_current_access_token' => true
                    ]
                );
                $CI->rhombus_platform_one = $Rhombus_Platform_One;

                $Rhombus_Keycloak = $this->getDouble(
                    'Rhombus_Keycloak' , [
                        'can_access_page' => true,
                        'get_current_access_token' => true
                    ]
                );
                $CI->rhombus_keycloak = $Rhombus_Keycloak;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', false, Keycloak_tiles_controller::class);
        MonkeyPatch::patchFunction('constant', true, Keycloak_tiles_controller::class);

        $actual = $this->request('POST', '/kc_tiles/register',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'applications' => 1

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_request_register_registration_fail() {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model' , [
                        'promptAccountRegistration' => false,
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;

                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'registerUserOnSubApp' => true,
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $Rhombus_Platform_One = $this->getDouble(
                    'Rhombus_Platform_One' , [
                        'can_access_page' => true,
                        'get_current_access_token' => true
                    ]
                );
                $CI->rhombus_platform_one = $Rhombus_Platform_One;

                $Rhombus_Keycloak = $this->getDouble(
                    'Rhombus_Keycloak' , [
                        'can_access_page' => true,
                        'get_current_access_token' => true
                    ]
                );
                $CI->rhombus_keycloak = $Rhombus_Keycloak;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);

        MonkeyPatch::patchFunction('constant', true, Keycloak_tiles_controller::class);
        MonkeyPatch::patchMethod(
            AccountStatus::class,
            ['hasStatus' => true]
        );

        $actual = $this->request('POST', '/kc_tiles/register',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'applications' => 1

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_request_register_registration_success() {
        
        $token = $this->token;
        $token['current_token'] = ['access_token' => 1];
        $token['session_state'] = 'state';
        $token = json_encode($token);
        
        $this->request->addCallable(
            function ($CI) use($token) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model' , [
                        'promptAccountRegistration' => true,
                        'registerKEYCLOAKUser' => true
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;

                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'registerUserOnSubApp' => true,
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $Rhombus_Platform_One = $this->getDouble(
                    'Rhombus_Platform_One' , [
                        'can_access_page' => true,
                        'get_current_access_token' => json_decode($token)
                    ]
                );
                $CI->rhombus_platform_one = $Rhombus_Platform_One;

                $Rhombus_Keycloak = $this->getDouble(
                    'Rhombus_Keycloak' , [
                        'can_access_page' => true,
                        'get_current_access_token' => json_decode($token)
                    ]
                );
                $CI->rhombus_keycloak = $Rhombus_Keycloak;

                $Register_model = $this->getDouble(
                    'Register_model' , [
                        'user_register' => 1
                    ]
                );
                $CI->Register_model = $Register_model;


                $SSO_model = $this->getDouble(
                    'SSO_model' , [
                        'registerSSOUser' => true
                    ]
                );
                $CI->SSO_model = $SSO_model;


                $Platform_One_model = $this->getDouble(
                    'Platform_One_model' , [
                        'registerPLATFORMONEUser' => true
                    ]
                );
                $CI->Platform_One_model = $Platform_One_model;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Keycloak_tiles_controller::class);

        MonkeyPatch::patchFunction('constant', true, Keycloak_tiles_controller::class);

        $actual = $this->request('POST', '/kc_tiles/register',
        [
            'id' => 1,
            'email' => 'test@rhombuspower.com',
            'type' => 'user',
            'applications' => 1

        ]);

        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

}
?>
