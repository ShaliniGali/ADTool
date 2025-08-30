<?php 
/**
 * @group base
 */
class Rhombus_Platform_One_test extends TestCase 
{
    private $obj = null;

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

    public function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];

        $_SERVER['HTTP_AUTHORIZATION'] = $this->jwt;

        // Get object to test
        $this->obj = new Rhombus_Platform_One();
    }

    public function test_has_error() {
        $errors = [1,2,3];

        // Patch mock values into real constants for the model fu.
		MonkeyPatch::patchMethod(
            'Rhombus_Platform_One',
			['get_error' => $errors]
        );

        $return = $this->obj->has_error();

        $this->assertEquals($return, count($errors));
    }

    public function test_get_error() {
        $errors = [1,2,3];

        // Patch mock values into real constants for the model fu.
		MonkeyPatch::patchMethod(
            'Rhombus_Platform_One',
			['get_error' => $errors]
        );

        $return = $this->obj->get_error();

        $this->assertTrue(count($return) === 3);
    }

    public function test_reset_state() {
        $this->obj->reset_state();

        $result = $this->obj->has_error();

        $this->assertFalse($result);
    }

    public function test_check_jwt_active() {
        $result = $this->obj->check_jwt_active();

        $this->assertTrue($result);
    }

    public function test_get_available_apps() {
        $apps = $this->obj->get_available_apps();

        $expected_apps = [];
        foreach ($this->decoded_token['guardian_app_access'] as $app) {
            $expected_apps[] = strtolower($app);
        }
        unset($app);
        $this->assertEquals($apps, $expected_apps);
    }

    public function tests_can_access_page() {

        $result = $this->obj->can_access_page();

        $this->assertTrue($result);
    }

    public function test_check_app_access() {
        $result = $this->obj->check_app_access();

        $this->assertTrue($result);
    }

    public function test_get_current_access_token() {

        $this->obj->check_jwt_active();

        $result = $this->obj->get_current_access_token('email');

        $this->assertNotNull($result);

        $result = $this->obj->get_current_access_token();

        $this->assertTrue(isset($result->email));

        $result = $this->obj->get_current_access_token('notfound');

        $this->assertNull($result);
    }

    public function test_get_jwt() {
        $result = $this->obj->get_jwt();

        $this->assertTrue($result);
    }

    public function test_destruct_write_log() {
        $errors = [1,2,3];

        // Patch mock values into real constants for the model fu.
		MonkeyPatch::patchMethod(
            'Rhombus_Platform_One',
			['get_error' => $errors]
        );

        $errors = $this->obj->get_error();

        unset($this->obj);

        $this->assertTrue(!isset($this->obj));

        $this->assertTrue(count($errors) > 0);
        
        $this->assertLogged('error', implode("\n", $errors));
    }

    public function test_get_available_apps_error() {
        $_SERVER['HTTP_AUTHORIZATION'] = '';

        $result = $this->obj->get_available_apps();

        $this->assertEmpty($result);
    }

    public function test_can_access_page_error() {
        $_SERVER['HTTP_AUTHORIZATION'] = '';

        try {
            $result = $this->obj->can_access_page(true);
        } catch (Throwable $e) { 
            $this->assertInstanceOf('CIPHPUnitTestRedirectException', $e);
        }
    }

    public function test_check_app_access_error() {
        MonkeyPatch::patchFunction('defined', false, 'Rhombus_Platform_One::check_app_access');

        $result = $this->obj->check_app_access();

        $this->assertFalse($result);
    }

    public function test_get_jwt_error() {
        $_SERVER['HTTP_AUTHORIZATION'] = '';

        $result = $this->obj->get_jwt();

        $this->assertFalse($result);
    }

    public function test_process_token_error() {
        $_SERVER['HTTP_AUTHORIZATION'] = '';

        $result = $this->obj->check_jwt_active();

        $this->assertFalse($result);
    }
}