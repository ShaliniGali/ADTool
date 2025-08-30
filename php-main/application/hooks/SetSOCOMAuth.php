<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SetSOCOMAuth {
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
        'SOCOM_Dashboard_Admin_AOAD_Users',
        'SOCOM_Dashboard_Site_Users',
        'SOCOM_Dashboard_Cap_Users',
        'SOCOM_Dashboard_Cycle_Users'
    );

    private $excludedSSOControllers = array(
        'keycloak_tiles_controller',
        'keycloak_register_activate',
        'Platform_One_register_activate'
    );

    private $currClass;

    public function __construct() {
        $this->CI = &get_instance();
        // Load your custom library
        $this->CI->load->library('SOCOM/UserAuthorization');
    
        $this->currClass = strtolower($this->CI->router->class);
        foreach($this->excludedControllers as &$c) {
            $c = strtolower($c);
        }
    }

    // This function will be called by the hook
    public function setUserAuth() {
        if (
            in_array($this->currClass, $this->excludedControllers) ||
            in_array($this->currClass, $this->excludedSSOControllers)
            ) {
            return;
        }

        if (isset($this->CI->session->userdata('logged_in')['id'])) {
            if ($this->CI->userauthorization->check_refresh()) {
                $this->CI->userauthorization->auth_jwt_refresh();
            } else if (strlen($this->CI->userauthorization->get_token()->access_token ?? '') === 0) {
                // Call the method you want from the library
                $this->CI->userauthorization->auth_jwt();
            }
        } else {
            // Log an error if the library is not loaded
            log_message('error', 'User has no ID');
        }
    }
}