<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class RoleAccess {
    private $CI;
    private $currClass;

    /**
     * All controllers excluded from the user login check should be defined in the
     * $excludedControllers array.
     */
    private $excludedControllers = array(
        'Login',
        'Login_keycloak',
        'Login_Platform_One',
        'Js',
        'Login_token_controller',
        'First_admin_controller',
        'Register',
        'SSO_controller',
        'Keycloak_sso_controller',
        'Platform_One_sso_controller',
        'CAC_controller',
        'Tutorial',
        'keycloak_tiles_controller',
        'account_manager_controller',
        'FACS_manager_controller',
        'react_api_controller',
        'Release_notes_controller'
    );

    public function __construct() {
        $this->CI = &get_instance();
        $this->currClass = strtolower($this->CI->router->class);
        $this->currMethod = strtolower($this->CI->router->method);
        $this->CI->load->helper('url');
        $this->CI->load->helper('facs');

        foreach($this->excludedControllers as &$c) {
            $c = strtolower($c);
        }
    }

    public function checkRoleStatus() {
        if (
            RHOMBUS_FACS !== 'TRUE' || 
            in_array($this->currClass, $this->excludedControllers)
        ) {
            return;
        }

        $account_type = $this->CI->session->userdata('logged_in')['account_type'] ?? false;
        
        if ($this->CI->session->userdata('logged_in')['id'] && $account_type !== false) {
            $tileAppName = PROJECT_TILE_APP_NAME;
            if(HAS_SUBAPPS){
                $tileAccountSession = $this->CI->session->userdata('tile_account_session');
                $tileAppName = $tileAccountSession['tile_account_name'];
                $account_type = $tileAccountSession['tile_account_type'];
            }

            $api_response = json_decode(
                hasaccess_facs_api_call(
                    $tileAppName,
                    $this->currClass,
                    $this->currMethod,
                    $account_type
                ),
                true
            )['Access'][0] ?? false;
        } else {
            $api_response = false;
        }

        if ($api_response !== true) {
            show_error(
                'Your account is not able to acccess this resource',
                403,
                'Access Denied'
            );
        }
    }

}