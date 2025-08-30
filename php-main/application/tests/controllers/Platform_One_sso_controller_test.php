<?php
/**
 * @group base
 */

class Platform_One_sso_controller_test extends RhombusControllerTestCase
{
    private $mockData = null;

    private $jwt = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJVVlh5aWRkbEtjNHFCc1JKdGFYTjYwUUlCVFZ5QzJodFMtVVBZVGxOLUZFIn0.eyJleHAiOjE2NzUzNzI5OTEsImlhdCI6MTY3NTM3MjY5MSwiYXV0aF90aW1lIjoxNjc1MzcyNjg4LCJqdGkiOiI3ZGQxOGUzMS1jODFmLTQ4ZGMtODQ2Ny05YzE2M2FhOThmYjIiLCJpc3MiOiJodHRwczovL3NlY3VyZS5yaG9tYnVzcG93ZXIuY29tL3JlYWxtcy9EZXZUZXN0aW5nRW52aXJvbm1lbnQiLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMzhkZDkxNWUtMzEyMS00M2E4LWJiZDItN2Q5OTJlZjcwY2YwIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoiZ3VhcmRpYW4iLCJzZXNzaW9uX3N0YXRlIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiYWNyIjoiMSIsInJlYWxtX2FjY2VzcyI6eyJyb2xlcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJlbWFpbCBndWFyZGlhbl9hcHBfcm9sZSBwcm9maWxlIiwic2lkIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiZ3VhcmRpYW5fYXBwX2FjY2VzcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl0sImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiVGVzdCBVc2VyIiwicHJlZmVycmVkX3VzZXJuYW1lIjoidGVzdC51c2VyIiwiZ2l2ZW5fbmFtZSI6IlRlc3QiLCJmYW1pbHlfbmFtZSI6IlVzZXIiLCJlbWFpbCI6InRlc3QudXNlckByaG9tYnVzcG93ZXIuY29tIn0.MVXq7JnAT-py1SGlWd4mxHcBJ2ZAMD9qlRqqHNf_83dJcOy_cN0T6ZohGUkr4P1Feh0ws1zvtyHBvuXsZuxYw9qnphLFz9QvdzHu90U1rCT27hJnA4ET121CM33DiTP3aCxQzs6otJOucHdcB2ZMUtZzoJIk5NUygJMbH4PXoBtvwc1VH98fzBPrvKHuEMlMxI8KhcmV89vw7609hXqv9916RsqE5gJp63jxgsnKz7H9c7jK-fluvneVDij79wsElrQgiVNGnCTHXQoj-5yCWcop3ueOURvh6P_JEgdO3ha0UmlWGHD5gcCfyYWQonddWJi8QGC2eAF4lpHiLiFvOQ";

    private $decoded_token = [
        "exp" => 1675372991,
        "iat" => 1675372691,
        "auth_time" => 1675372688,
        "jti" => "7dd18e31-c81f-48dc-8467-9c163aa98fb2",
        "iss" => "https://secure.rhombuspower.com/realms/DevTestingEnvironment",
        "aud" => "account",
        "sub" => "38dd915e-3121-43a8-bbd2-7d992ef70cf0",
        "typ" => "Bearer",
        "azp" => "guardian",
        "session_state" => "a736a6dc-fbc1-4e7a-9003-28bc86311f41",
        "acr" => "1",
        "realm_access" => [
            "roles" => [
            "default-roles-devtestingenvironment",
            "offline_access",
            "uma_authorization",
            "app_access"
            ]
        ],
        "resource_access" => [
            "account" => [
            "roles" => [
                "manage-account",
                "manage-account-links",
                "view-profile"
            ]
            ]
        ],
        "scope" => "email guardian_app_role profile",
        "sid" => "a736a6dc-fbc1-4e7a-9003-28bc86311f41",
        "guardian_app_access" => [
            "default-roles-devtestingenvironment",
            "offline_access",
            "uma_authorization",
            "app_access"
        ],
        "email_verified" => true,
        "name" => "Test User",
        "preferred_username" => "test.user",
        "given_name" => "Test",
        "family_name" => "User",
        "email" => "test.user@rhombuspower.com"
    ];

    public function setUp() : void
    {
        parent::setUp();

        $_SESSION = [];

        $_SERVER['HTTP_AUTHORIZATION'] = $this->jwt;
    }

    public function test_success_no_user_kc_tiles()
    {

        // login success
        $this->request->addCallable(
            function ($CI) {
                
                $Platform_One_model = $this->createMock(Platform_One_model::class);
                $Platform_One_model->method('userExists')->willReturn([]);
                $CI->Platform_One_model = $Platform_One_model;
        });
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Platform_One_sso_controller::class);

        // no code or session_state
        $this->request('GET', 'rb_p1/success/0/default');
        $this->assertRedirect('/kc_tiles');
    }

    public function test_success_new_user() {
        // login success
        $this->request->addCallable(
            function ($CI) {
                $Platform_One_model = $this->createMock(Platform_One_model::class);
                $Platform_One_model->method('updateAccount')->willReturn(true);
                $Platform_One_model->method('userExists')->willReturn([['id' => 1, 'status' => AccountStatus::RegistrationPending]]);
                $CI->Platform_One_model = $Platform_One_model;

                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;

                MonkeyPatch::patchMethod(
                    Login_model::class,
                    ['user_login_success' => true]
                );
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Platform_One_sso_controller::class);

        $this->request('GET', 'rb_p1/success/1/default');
        $this->assertRedirect(base_url(''));
    }

    public function test_success_new_user_fail() {

        // login success
        $this->request->addCallable(
            function ($CI) {

                $Platform_One = $this->createMock(Platform_One_model::class);
                $Platform_One->method('updateAccount')->willReturn(true);
                $Platform_One->method('promptAccountRegistration')->willReturn(true);
                $Platform_One->method('userExists')->willReturn(null);
                $CI->Platform_One_model = $Platform_One;

                $CI->Login_model = $this->getDouble(
                    Login_model::class,
                    ['user_login_success' => true]
                );
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Platform_One_sso_controller::class);

        $this->request('GET', 'rb_p1/success/1/default');
        $this->assertRedirect(base_url('rb_p1/failure/'));
    }

    public function test_success_invalid_email() {

        $this->request->addCallable(
            function ($CI)  {
                MonkeyPatch::patchMethod(
                    Rhombus_Platform_One::class,
                    ['get_jwt' => true],
                    ['get_current_decoded_jwt' => false],
                    ['check_app_access' => true]
                );
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', false, Platform_One_sso_controller::class);

        $actual = $this->request('GET', 'rb_p1/success/0/default');
        $this->assertRedirect(base_url('rb_p1/failure'));
    }

    public function test_failure_invalid_email() {
        $this->request->addCallable(
            function ($CI) {
                MonkeyPatch::patchMethod(
                    Platform_One_model::class,
                    ['promptAccountRegistration' => true]
                );
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', false, Platform_One_sso_controller::class);

        $actual = $this->request('GET', 'rb_p1/failure');
        $this->assertNotNull($actual);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_failure_valid_email() {

        $this->request->addCallable(
            function ($CI) {
                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;

                MonkeyPatch::patchMethod(
                    Rhombus_Platform_One::class,
                    ['can_access_page' => true],
                    ['get_current_decoded_jwt' => false]
                );
                MonkeyPatch::patchMethod(
                    Platform_One_model::class,
                    ['promptAccountRegistration' => true]
                );
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Platform_One_sso_controller::class);

        $actual = $this->request('GET', 'rb_p1/failure/0/default');
        $this->assertNotNull($actual);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_failure_registered() {
        // valid email domain, not super admin, registered
        $this->request->addCallable(
            function ($CI) {
                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;

                MonkeyPatch::patchMethod(
                    Rhombus_Platform_One::class,
                    ['can_access_page' => true],
                    ['get_current_decoded_jwt' => false]
                );

                MonkeyPatch::patchMethod(
                    Platform_One_sso_controller::class,
                    ['hasRegistered' => true]
                );
            }
        );

        $actual = $this->request('GET', 'rb_p1/failure');
        $this->assertNotNull($actual);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_requestRegistration_failure_registered() {
        $jwt = $this->decoded_token;

        // valid email domain, already registered
        $this->request->addCallable(
            function ($CI) use($jwt) {
                $Platform_One_model = $this->getDouble(
                    'Platform_One_model', [
                        'promptAccountRegistration' => 'Registration_pending',
                    ]
                );
                $CI->Platform_One_model = $Platform_One_model;
                $Register_model = $this->getDouble(
                    'Register_model',
                    [
                        'user_register' => 1,
                    ]
                );
                $CI->Register_model = $Register_model;
                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => true,
                    ]
                );
                $CI->SSO_model = $SSO_model;

                MonkeyPatch::patchMethod(
                    Platform_One_sso_controller::class,
                    ['hasRegistered' => true]
                );

                MonkeyPatch::patchMethod(
                    Rhombus_Platform_One::class,
                    ['get_available_apps' => ['app_access']],
                    ['can_access_page' => true],
                    ['check_app_access' => true],
                    ['process_jwt' => true],
                    ['check_jwt_active' => true]
                );
            }
        );

        $actual = $this->request('GET', 'rb_p1/requestRegistration');
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_requestRegistration_success()
    {
        // valid email domain, not registered
        $this->request->addCallable(
            function ($CI) {
                MonkeyPatch::patchMethod(
                    Keycloak_model::class,
                    ['registerKEYCLOAKUser' => true]
                );

                MonkeyPatch::patchMethod(
                    Rhombus_Platform_One::class,
                    ['check_app_access' => true]
                );

                $CI->Platform_One_model = $this->getDouble(
                    'Platform_One_model', [
                        'promptAccountRegistration' => true,
                        'registerPLATFORMONEUser' => [1]
                    ]
                );

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_undeleted_user' => ''
                    ]
                );
                $CI->Login_model = $Login_model;

                $Register_model = $this->getDouble(
                    'Register_model', [
                        'user_register' => 1
                    ]
                );
                $CI->Register_model = $Register_model;

                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;

                $Password_encrypt_decrypt = $this->getDouble(
                    'Password_encrypt_decrypt', [
                        'encrypt' => [
                            'password' => '',
                            'salt' => openssl_random_pseudo_bytes(16)
                        ]
                    ]
                );
                $CI->password_encrypt_decrypt = $Password_encrypt_decrypt;
                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => true,
                    ]
                );
                $CI->SSO_model = $SSO_model;
            }
        );
        MonkeyPatch::patchFunction('isValidEmailDomain', true, Platform_One_sso_controller::class);

        $actual = $this->request('POST', 'rb_p1/requestRegistration', [
            'req' => 1
        ]);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('success', $actual['status']);
    }
}
?>
