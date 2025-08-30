<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SetDynamicYear {
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
        $this->CI->load->library('SOCOM/Dynamic_Year');
    
        $this->currClass = strtolower($this->CI->router->class);
        foreach($this->excludedControllers as &$c) {
            $c = strtolower($c);
        }
    }

    // This function will be called by the hook
    public function fetchDynamicYear() {
        if (
            in_array($this->currClass, $this->excludedControllers) ||
            in_array($this->currClass, $this->excludedSSOControllers)
            ) {
            return;
        }

        if (isset($this->CI->dynamic_year)) {
            // Call the method you want from the library
            $this->CI->dynamic_year->setFromCurrentYear();
            $this->CI->dynamic_year->setTablesExist();
        } else {
            // Log an error if the library is not loaded
            log_message('error', 'Dynamic_Year library is not loaded.');
        }
    }
}