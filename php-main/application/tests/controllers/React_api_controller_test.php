<?php
defined('BASEPATH') || exit('No direct script access allowed');

class React_api_controller_test extends RhombusControllerTestCase
{

    public function test_app_data()
    {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'convert_tile_data_json' => []
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );

        $tiles = $this->request('GET', 'api/sso/apps');
        $this->assertIsString($tiles);
    }

    public function test_user_data()
    {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'convert_tile_data_json' => []
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $session_data = array(
                    'email' => 'unit_tester123@rhombuspower.com',
                    'name' => 'Unit Tester',
                    'account_type' => 'USER',
                    'timestamp' => 1609459200,
                    'profile_image' => NULL,
                    'id' => 1
                );

                $CI->session->set_userdata('logged_in', $session_data);
            }
        );

        $actual = $this->request('GET', 'api/sso/user');
        $this->assertIsString($actual);
    }

    public function test_save_favorites()
    {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'save_favourites' => []
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );


        $actual = $this->request('POST', 'api/sso/favorites', ['ids' => ['1','2']]);
        $this->assertIsString($actual);
    }

    public function test_save_favorites_get()
    {
        $this->request->addCallable(
            function ($CI) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'save_favourites' => []
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );


        $actual = $this->request('GET', 'api/sso/favorites');
        $this->assertIsString($actual);
    }
}
