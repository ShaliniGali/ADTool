<?php
/**
 * @group base
 */

class Keycloak_sso_controller_test extends RhombusControllerTestCase
{
    private $code = '8064e98b-8d7d-490a-b9ca-b3f7054ad7b9.a736a6dc-fbc1-4e7a-9003-28bc86311f41.17a8a648-b5b6-4f51-99cc-c2109d7ba8e9';
    private $session_state = 'a736a6dc-fbc1-4e7a-9003-28bc86311f41';
    private $mockData = null;
    private $token = [
        "access_token" => "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJVVlh5aWRkbEtjNHFCc1JKdGFYTjYwUUlCVFZ5QzJodFMtVVBZVGxOLUZFIn0.eyJleHAiOjE2NzUzNzI5OTEsImlhdCI6MTY3NTM3MjY5MSwiYXV0aF90aW1lIjoxNjc1MzcyNjg4LCJqdGkiOiI3ZGQxOGUzMS1jODFmLTQ4ZGMtODQ2Ny05YzE2M2FhOThmYjIiLCJpc3MiOiJodHRwczovL3NlY3VyZS5yaG9tYnVzcG93ZXIuY29tL3JlYWxtcy9EZXZUZXN0aW5nRW52aXJvbm1lbnQiLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMzhkZDkxNWUtMzEyMS00M2E4LWJiZDItN2Q5OTJlZjcwY2YwIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoiZ3VhcmRpYW4iLCJzZXNzaW9uX3N0YXRlIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiYWNyIjoiMSIsInJlYWxtX2FjY2VzcyI6eyJyb2xlcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJlbWFpbCBndWFyZGlhbl9hcHBfcm9sZSBwcm9maWxlIiwic2lkIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiZ3VhcmRpYW5fYXBwX2FjY2VzcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl0sImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiVGVzdCBVc2VyIiwicHJlZmVycmVkX3VzZXJuYW1lIjoidGVzdC51c2VyIiwiZ2l2ZW5fbmFtZSI6IlRlc3QiLCJmYW1pbHlfbmFtZSI6IlVzZXIiLCJlbWFpbCI6InRlc3QudXNlckByaG9tYnVzcG93ZXIuY29tIn0.MVXq7JnAT-py1SGlWd4mxHcBJ2ZAMD9qlRqqHNf_83dJcOy_cN0T6ZohGUkr4P1Feh0ws1zvtyHBvuXsZuxYw9qnphLFz9QvdzHu90U1rCT27hJnA4ET121CM33DiTP3aCxQzs6otJOucHdcB2ZMUtZzoJIk5NUygJMbH4PXoBtvwc1VH98fzBPrvKHuEMlMxI8KhcmV89vw7609hXqv9916RsqE5gJp63jxgsnKz7H9c7jK-fluvneVDij79wsElrQgiVNGnCTHXQoj-5yCWcop3ueOURvh6P_JEgdO3ha0UmlWGHD5gcCfyYWQonddWJi8QGC2eAF4lpHiLiFvOQ",
        "id_token" => "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJVVlh5aWRkbEtjNHFCc1JKdGFYTjYwUUlCVFZ5QzJodFMtVVBZVGxOLUZFIn0.eyJleHAiOjE2NzUzNzI5OTEsImlhdCI6MTY3NTM3MjY5MSwiYXV0aF90aW1lIjoxNjc1MzcyNjg4LCJqdGkiOiI3ZGQxOGUzMS1jODFmLTQ4ZGMtODQ2Ny05YzE2M2FhOThmYjIiLCJpc3MiOiJodHRwczovL3NlY3VyZS5yaG9tYnVzcG93ZXIuY29tL3JlYWxtcy9EZXZUZXN0aW5nRW52aXJvbm1lbnQiLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMzhkZDkxNWUtMzEyMS00M2E4LWJiZDItN2Q5OTJlZjcwY2YwIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoiZ3VhcmRpYW4iLCJzZXNzaW9uX3N0YXRlIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiYWNyIjoiMSIsInJlYWxtX2FjY2VzcyI6eyJyb2xlcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJlbWFpbCBndWFyZGlhbl9hcHBfcm9sZSBwcm9maWxlIiwic2lkIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiZ3VhcmRpYW5fYXBwX2FjY2VzcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl0sImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiVGVzdCBVc2VyIiwicHJlZmVycmVkX3VzZXJuYW1lIjoidGVzdC51c2VyIiwiZ2l2ZW5fbmFtZSI6IlRlc3QiLCJmYW1pbHlfbmFtZSI6IlVzZXIiLCJlbWFpbCI6InRlc3QudXNlckByaG9tYnVzcG93ZXIuY29tIn0.MVXq7JnAT-py1SGlWd4mxHcBJ2ZAMD9qlRqqHNf_83dJcOy_cN0T6ZohGUkr4P1Feh0ws1zvtyHBvuXsZuxYw9qnphLFz9QvdzHu90U1rCT27hJnA4ET121CM33DiTP3aCxQzs6otJOucHdcB2ZMUtZzoJIk5NUygJMbH4PXoBtvwc1VH98fzBPrvKHuEMlMxI8KhcmV89vw7609hXqv9916RsqE5gJp63jxgsnKz7H9c7jK-fluvneVDij79wsElrQgiVNGnCTHXQoj-5yCWcop3ueOURvh6P_JEgdO3ha0UmlWGHD5gcCfyYWQonddWJi8QGC2eAF4lpHiLiFvOQ",
        "expires_in" => 300,
        "refresh_expires_in" => 1800,
        "refresh_token" => "eyJhbGciOiJIUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICI2ODA0MWQ5Yy1iZDNiLTQ1YjctYjUxMS0zZjFjYzU1NzU2MTIifQ.eyJleHAiOjE2NzUzNzQ0OTEsImlhdCI6MTY3NTM3MjY5MSwianRpIjoiYzJkZDQzOWEtMTBiYS00NWUyLWI3ZjUtMDlmNzg4OWE0NDg0IiwiaXNzIjoiaHR0cHM6Ly9zZWN1cmUucmhvbWJ1c3Bvd2VyLmNvbS9yZWFsbXMvRGV2VGVzdGluZ0Vudmlyb25tZW50IiwiYXVkIjoiaHR0cHM6Ly9zZWN1cmUucmhvbWJ1c3Bvd2VyLmNvbS9yZWFsbXMvRGV2VGVzdGluZ0Vudmlyb25tZW50Iiwic3ViIjoiMzhkZDkxNWUtMzEyMS00M2E4LWJiZDItN2Q5OTJlZjcwY2YwIiwidHlwIjoiUmVmcmVzaCIsImF6cCI6Imd1YXJkaWFuIiwic2Vzc2lvbl9zdGF0ZSI6ImE3MzZhNmRjLWZiYzEtNGU3YS05MDAzLTI4YmM4NjMxMWY0MSIsInNjb3BlIjoiZW1haWwgZ3VhcmRpYW5fYXBwX3JvbGUgcHJvZmlsZSIsInNpZCI6ImE3MzZhNmRjLWZiYzEtNGU3YS05MDAzLTI4YmM4NjMxMWY0MSJ9.fh5teTccaWQPn4yybCS9AVAKkq3X9zki5mm5dUTOno4",
        "token_type" => "Bearer",
        "not-before-policy" => 0,
        "session_state" => "a736a6dc-fbc1-4e7a-9003-28bc86311f41",
        "scope" => "email guardian_app_role profile"
    ];
    
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
        $_SESSION['keycloak_token'] = json_encode($this->token);
        $_SESSION['keycloak_session'] = $this->token['session_state'];

        copy(APPPATH.'tests/test_files/public_key.txt', APPPATH.'secure_uploads/keycloak/public_key.txt');

        Firebase\JWT\JWT::$timestamp = $this->decoded_token['exp']-30;

        MonkeyPatch::patchMethod(
            Rhombus_Keycloak::class,
            ['curl_keycloak' => [json_decode(json_encode($this->token)), 200]]
        );

        $this->mockData ='?code='.urlencode($this->code).'&session_state='.urlencode($this->session_state);
    }

    public function test_success_validation_failure()
    {
        // no code or session_state
        $this->request('GET', 'rb_kc/success/0/default');
        $this->assertRedirect('/rb_kc/failure');
    }

    public function test_success_new_user() {
        $token = $this->token;
        $token = json_encode($token);

        // login success
        $this->request->addCallable(
            function ($CI) use($token) {
                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['get_token' => true],
                    ['get_available_apps' => ['app_access']],
                    ['validate_token' => json_decode($token)],
                    ['can_access_page' => true],
                    ['check_app_access' => true],
                    ['process_token' => true],
                    ['check_token_active' => true],
                    ['get_current_decoded_token' => json_decode($token)]
                );
                
                $Keycloak_model = $this->createMock(Keycloak_model::class);
                $Keycloak_model->method('updateAccount')->willReturn(true);
                $Keycloak_model->method('userExists')->willReturn([['id' => 1, 'status' => AccountStatus::RegistrationPending]]);
                $CI->Keycloak_model = $Keycloak_model;

                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;
                $Login_model = $this->getDouble(
                    'Login_model', [
                        'user_login_success' => TRUE
                    ]
                );
                $CI->Login_model = $Login_model;

            }
        );
        $this->request('GET', 'rb_kc/success/1/default' . $this->mockData);
        $this->assertRedirect('');
    }

    public function test_success_new_user_success() {
        MonkeyPatch::patchConstant('ADMIN_EMAILS', 'test.user@rhombuspower.com');

        $token = $this->token;
        $token = json_encode($token);

        // login success
        $this->request->addCallable(
            function ($CI) use($token) {
                MonkeyPatch::patchConstant('ADMIN_EMAILS', 'test.user@rhombuspower.com');

                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['get_token' => true],
                    ['get_available_apps' => ['app_access']],
                    ['validate_token' => json_decode($token)],
                    ['can_access_page' => true],
                    ['check_app_access' => true],
                    ['process_token' => true],
                    ['check_token_active' => true],
                    ['get_current_decoded_token' => json_decode($token)]
                );

                $Keycloak_model = $this->getDouble(
                    'Keycloak_model', [
                        'promptAccountRegistration' => false,
                        'userExists' => []
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
            }
        );
        $response = $this->request('GET', 'rb_kc/success/0/default' . $this->mockData);
        $this->assertRedirect(base_url('kc_tiles'));
    }

    public function test_success_new_user_fail() {
        $token = $this->token;
        $token = json_encode($token);

        // login success
        $this->request->addCallable(
            function ($CI) use($token) {
                MonkeyPatch::patchConstant('ADMIN_EMAILS', 'test.user@rhombuspower.com');

                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['get_token' => true],
                    ['get_available_apps' => ['app_access']],
                    ['validate_token' => json_decode($token)],
                    ['can_access_page' => true],
                    ['check_app_access' => true],
                    ['process_token' => true],
                    ['check_token_active' => true],
                    ['get_current_decoded_token' => json_decode($token)]
                );

                $Keycloak_model = $this->createMock(Keycloak_model::class);
                $Keycloak_model->method('updateAccount')->willReturn(true);
                $Keycloak_model->method('promptAccountRegistration')->willReturn(true);
                $Keycloak_model->method('userExists')->willReturn([]);
                $CI->Keycloak_model = $Keycloak_model;

                $CI->Login_model = $this->getDouble(
                    Login_model::class,
                    ['user_login_success' => true]
                );
            }
        );
        $this->request('GET', 'rb_kc/success/1/default' . $this->mockData);
        $this->assertRedirect(base_url('rb_kc/failure'));
    }

    public function test_success_invalid_email() {

        $this->request->addCallable(
            function ($CI)  {
                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['get_token' => true],
                    ['get_current_decoded_token' => false],
                    ['check_app_access' => true]
                );

                MonkeyPatch::patchFunction('isValidEmailDomain', false, Keycloak_sso_controller::class);
            }
        );
        
        $actual = $this->request('GET', 'rb_kc/success/0/default'. $this->mockData);
        $this->assertRedirect(base_url('rb_kc/failure'));
    }

    public function test_failure_invalid_email() {
        $token = $this->token;
        $token['email'] = false;
        $token = json_encode($token);

        $this->request->addCallable(
            function ($CI) use($token) {
                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['get_token' => true],
                    ['get_available_apps' => ['app_access']],
                    ['validate_token' => json_decode($token)],
                    ['can_access_page' => true],
                    ['check_app_access' => true],
                    ['process_token' => true],
                    ['check_token_active' => true],
                    ['get_current_decoded_token' => json_decode($token)]
                );

                $Keycloak_model = $this->getDouble(
                    'Keycloak_model', [
                        'promptAccountRegistration' => 1
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
            }
        );
        
        $actual = $this->request('GET', 'rb_kc/failure');
        $this->assertNotNull($actual);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_failure_valid_email() {

        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model', [
                        'promptAccountRegistration' => 1
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
                
                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;

                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['can_access_page' => true],
                    ['get_current_decoded_token' => false]
                );

                MonkeyPatch::patchFunction('isValidEmailDomain', false, 'Keycloak_sso_controller::failure');
            }
        );
        
        $actual = $this->request('GET', 'rb_kc/failure');
        $this->assertNotNull($actual);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_failure_registered() {
        // valid email domain, not super admin, registered
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model', [
                        'promptAccountRegistration' => 'Registration_pending',
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;

                $SSO_model = $this->getDouble(
                    'SSO_model', [
                        'registerSSOUser' => TRUE
                    ]
                );
                $CI->SSO_model = $SSO_model;

                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['can_access_page' => true],
                    ['get_current_decoded_token' => false]
                );
            }
        );
        $actual = $this->request('GET', 'rb_kc/failure');
        $this->assertNotNull($actual);
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_requestRegistration_failure_registered() {
        $token = $this->token;
        $token['email'] = false;
        $token = json_encode($token);

        // valid email domain, already registered
        $this->request->addCallable(
            function ($CI) use($token) {
                $Keycloak_model = $this->getDouble(
                    'Keycloak_model', [
                        'promptAccountRegistration' => 'Registration_pending',
                        'registerKEYCLOAKUser' => 1
                    ]
                );
                $CI->Keycloak_model = $Keycloak_model;
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
                
                $Platform_One_model = $this->getDouble(
                    'Platform_One_model', [
                        'registerPLATFORMONEUser' => 1
                    ]
                );
                $CI->Platform_One_model = $Platform_One_model;

                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['get_available_apps' => ['app_access']],
                    ['validate_token' => json_decode($token)],
                    ['can_access_page' => true],
                    ['check_app_access' => true],
                    ['process_token' => true],
                    ['check_token_active' => true],
                    ['get_current_decoded_token' => json_decode($token)]
                );
            }
        );
        $actual = $this->request('GET', 'rb_kc/requestRegistration');
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('failure', $actual['status']);
    }

    public function test_requestRegistration_success()
    {
        $token = $this->token;
        $token = json_encode($token);

        // valid email domain, not registered
        $this->request->addCallable(
            function ($CI) use($token) {
                MonkeyPatch::patchMethod(
                    Rhombus_Keycloak::class,
                    ['check_app_access' => true],
                    ['get_current_decoded_token' => json_decode($token)]
                );

                $CI->Keycloak_model = $this->getDouble(
                    'Keycloak_model', [
                        'promptAccountRegistration' => true,
                        'registerKEYCLOAKUser' => 1
                    ]
                );

                $Platform_One_model = $this->getDouble(
                    'Platform_One_model', [
                        'registerPLATFORMONEUser' => 1
                    ]
                );
                $CI->Platform_One_model = $Platform_One_model;

                $Login_model = $this->getDouble(
                    'Login_model', [
                        'get_undeleted_user' => ''
                    ]
                );
                $CI->Login_model = $Login_model;

                $Register_model = $this->getDouble(
                    'Register_model', [
                        'user_register' => 1,
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
        $actual = $this->request('POST', 'rb_kc/requestRegistration', [
            'req' => 1
        ]);
        $actual = json_decode($actual, TRUE);
        $this->assertEquals('success', $actual['status']);
    }
}
?>