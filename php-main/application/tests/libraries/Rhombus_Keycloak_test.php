<?php 
/**
 * @group base
 */
class Rhombus_Keycloak_test extends TestCase 
{
    private $obj = null;

    private $code = '8064e98b-8d7d-490a-b9ca-b3f7054ad7b9.a736a6dc-fbc1-4e7a-9003-28bc86311f41.17a8a648-b5b6-4f51-99cc-c2109d7ba8e9';

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

    private $refresh_token = [
        "access_token" => "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJVVlh5aWRkbEtjNHFCc1JKdGFYTjYwUUlCVFZ5QzJodFMtVVBZVGxOLUZFIn0.eyJleHAiOjE2NzUzNzI5OTEsImlhdCI6MTY3NTM3MjY5MSwiYXV0aF90aW1lIjoxNjc1MzcyNjg4LCJqdGkiOiI3ZGQxOGUzMS1jODFmLTQ4ZGMtODQ2Ny05YzE2M2FhOThmYjIiLCJpc3MiOiJodHRwczovL3NlY3VyZS5yaG9tYnVzcG93ZXIuY29tL3JlYWxtcy9EZXZUZXN0aW5nRW52aXJvbm1lbnQiLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMzhkZDkxNWUtMzEyMS00M2E4LWJiZDItN2Q5OTJlZjcwY2YwIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoiZ3VhcmRpYW4iLCJzZXNzaW9uX3N0YXRlIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiYWNyIjoiMSIsInJlYWxtX2FjY2VzcyI6eyJyb2xlcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJlbWFpbCBndWFyZGlhbl9hcHBfcm9sZSBwcm9maWxlIiwic2lkIjoiYTczNmE2ZGMtZmJjMS00ZTdhLTkwMDMtMjhiYzg2MzExZjQxIiwiZ3VhcmRpYW5fYXBwX2FjY2VzcyI6WyJkZWZhdWx0LXJvbGVzLWRldnRlc3RpbmdlbnZpcm9ubWVudCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJhcHBfYWNjZXNzIl0sImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiVGVzdCBVc2VyIiwicHJlZmVycmVkX3VzZXJuYW1lIjoidGVzdC51c2VyIiwiZ2l2ZW5fbmFtZSI6IlRlc3QiLCJmYW1pbHlfbmFtZSI6IlVzZXIiLCJlbWFpbCI6InRlc3QudXNlckByaG9tYnVzcG93ZXIuY29tIn0.MVXq7JnAT-py1SGlWd4mxHcBJ2ZAMD9qlRqqHNf_83dJcOy_cN0T6ZohGUkr4P1Feh0ws1zvtyHBvuXsZuxYw9qnphLFz9QvdzHu90U1rCT27hJnA4ET121CM33DiTP3aCxQzs6otJOucHdcB2ZMUtZzoJIk5NUygJMbH4PXoBtvwc1VH98fzBPrvKHuEMlMxI8KhcmV89vw7609hXqv9916RsqE5gJp63jxgsnKz7H9c7jK-fluvneVDij79wsElrQgiVNGnCTHXQoj-5yCWcop3ueOURvh6P_JEgdO3ha0UmlWGHD5gcCfyYWQonddWJi8QGC2eAF4lpHiLiFvOQ",
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

    private $refresh_decoded_token = [
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

    public function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_SESSION['keycloak_token'] = json_encode($this->token);
        $_SESSION['keycloak_session'] = $this->token['session_state'];

        Firebase\JWT\JWT::$timestamp = $this->decoded_token['exp']-30;

        copy(APPPATH.'tests/test_files/public_key.txt', APPPATH.'secure_uploads/keycloak/public_key.txt');

        $token = json_encode($this->token);
        MonkeyPatch::patchFunction('curl_exec', $token, 'Rhombus_Keycloak::curl_keycloak');
        MonkeyPatch::patchMethod(Keycloak_model::class, ['updateToken' => true]);

        // Get object to test
        $this->obj = new Rhombus_Keycloak();
    }

    public function test_has_error() {
        $errors = [1,2,3];

        // Patch mock values into real constants for the model fu.
		MonkeyPatch::patchMethod(
            'Rhombus_Keycloak',
			['get_error' => $errors]
        );
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $return = $this->obj->has_error();

        $this->assertEquals($return, count($errors));
    }

    public function test_get_error() {
        Firebase\JWT\JWT::$timestamp = time();
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $this->obj->logout();

        $return = $this->obj->get_error();

        $this->assertTrue(count($return) == 0);
    }

    public function test_reset_state() {
        $this->obj->reset_state();
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->has_error();

        $this->assertFalse($result);
    }

    public function test_check_token_active() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->check_token_active($refresh = false);

        $this->assertTrue($result);
    }

    public function test_get_available_apps() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $apps = $this->obj->get_available_apps();

        $expected_apps = [];
        foreach ($this->decoded_token['guardian_app_access'] as $app) {
            $expected_apps[] = strtolower($app);
        }
        unset($app);
        $this->assertEquals($apps, $expected_apps);
    }

    public function tests_can_access_page() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->can_access_page();

        $this->assertTrue($result);
    }

    public function test_check_app_access() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->check_app_access();

        $this->assertTrue($result);
    }

    public function test_get_current_id_token() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $this->obj->check_token_active();

        $result = $this->obj->get_current_id_token('email');

        $this->assertNotNull($result);

        $result = $this->obj->get_current_id_token();

        $this->assertTrue(isset($result->email));

        $result = $this->obj->get_current_id_token('notfound');

        $this->assertNull($result);
    }

    public function test_get_current_access_token() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $this->obj->check_token_active();

        $result = $this->obj->get_current_access_token('email');

        $this->assertNotNull($result);

        $result = $this->obj->get_current_access_token();

        $this->assertTrue(isset($result->email));

        $result = $this->obj->get_current_access_token('notfound');

        $this->assertNull($result);
    }

    public function test_get_token() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        MonkeyPatch::patchMethod(
            Rhombus_Keycloak::class,
            ['curl_keycloak' => [json_decode(json_encode($this->token)), 200]]
        );

        $result = $this->obj->get_token($this->code, $this->token['session_state']);

        $this->assertTrue($result);
    }

    public function test_verify_session_state_error() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $_SESSION['keycloak_session'] = 'false';
        
        $result = $this->obj->verify_session_state();

        $this->assertFalse($result);
    }

    public function test_verify_token_properties_error() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->verify_token_properties();

        $this->assertFalse($result);
    }

    public function test_get_refresh_token() {
        MonkeyPatch::patchFunction('curl_getinfo', 204, 'Rhombus_Keycloak::curl_keycloak');
        MonkeyPatch::patchMethod(
            Rhombus_Keycloak::class,
            ['curl_keycloak' => [json_decode(json_encode($this->refresh_token)), 200]]
        );

        Firebase\JWT\JWT::$timestamp = $this->refresh_decoded_token['exp'] - 30;

        $result = $this->obj->check_token_active(true);

        $this->assertTrue($result);
    }

    public function test_get_authenticate_url() {
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->get_authenticate_url();

        $url = parse_url($result);
        parse_str($url['query'], $query);
        
        $test1 = defined('KEYCLOAK_SSO_REALM') && $url['path'] === sprintf('/auth/realms/%s/protocol/openid-connect/auth', KEYCLOAK_SSO_REALM);
        
        if (isset($url['port'])) {
            $test2 = sprintf('%s://%s:%s', $url['scheme'], $url['host'], $url['port']);
        } else {
            $test2 = sprintf('%s://%s', $url['scheme'], $url['host']);
        }

        $test2 = $test2 === KEYCLOAK_SSO_URL;

        $test3 = isset($query['response_type'], $query['response_mode'], $query['client_id'], $query['redirect_uri']) && 
                    $query['response_type'] === 'code' && 
                    $query['response_mode'] === 'query' &&
                    $query['client_id'] === KEYCLOAK_SSO_CLIENT_ID &&
                    $query['redirect_uri'] === base_url('rb_kc/success');

        $this->assertFalse($test1 && $test2 && $test3);
    }

    public function test_logout() {
        MonkeyPatch::patchFunction('curl_getinfo', 204, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->logout();

        $this->assertTrue($result);
    }

    public function test_destruct_write_log() {
        Firebase\JWT\JWT::$timestamp = time();
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $this->obj->logout();

        $errors = $this->obj->get_error();

        unset($this->obj);

        $this->assertTrue(!isset($this->obj));
    }

    public function test_get_available_apps_error() {
        Firebase\JWT\JWT::$timestamp = time();
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->get_available_apps();

        $this->assertEmpty($result);
    }

    public function test_can_access_page_error() {
        Firebase\JWT\JWT::$timestamp = time();
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        try {
            $result = $this->obj->can_access_page(true);
        } catch (Throwable $e) { 
            $this->assertInstanceOf('CIPHPUnitTestRedirectException', $e);

        }
    }

    public function test_check_app_access_error() {
        MonkeyPatch::patchFunction('defined', false, 'Rhombus_Keycloak::check_app_access');
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->check_app_access();

        $this->assertFalse($result);
    }

    public function test_get_token_error() {
        MonkeyPatch::patchFunction('curl_getinfo', 204, 'Rhombus_Keycloak::curl_keycloak');
        $result = $this->obj->get_token($this->code, $this->token['session_state']);

        $this->assertFalse($result);
    }

    public function test_get_refresh_token_error() {
        Firebase\JWT\JWT::$timestamp = $this->refresh_decoded_token['exp'] - 0;
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->check_token_active(true);

        $this->assertFalse($result);
    }

    public function test_process_token_error() {
        $_SESSION['keycloak_token'] = false;
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->check_token_active();

        $this->assertFalse($result);
    }

    public function test_validate_token_error_no_public_key() {
        MonkeyPatch::patchFunction('is_file', false, 'Rhombus_Keycloak::create_public_key');
        MonkeyPatch::patchFunction('write_file', false, 'Rhombus_Keycloak::create_public_key');
        MonkeyPatch::patchFunction('curl_getinfo', 200, 'Rhombus_Keycloak::curl_keycloak');

        $result = $this->obj->get_token($this->code, $this->token['session_state']);

        $this->assertFalse($result);
    }

    /**
     * @todo get this working
    public function test_validate_token_error_no_access_token() {
        MonkeyPatch::patchFunction('isset', false, 'Rhombus_Keycloak::validate_token');

        $result = $this->obj->check_token_active(false);

        $this->assertFalse($result);
    }
    */
}