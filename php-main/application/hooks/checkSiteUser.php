<?php
defined('BASEPATH') or exit('No direct script access allowed');
class checkSiteUser {
    private $CI;
    private $currClass;
    private $excludedControllers = array(
        'SOCOM_Dashboard_Admin_AOAD_Users',
        'keycloak_tiles_controller',
        'keycloak_register_activate',
        'Platform_One_register_activate',
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
        'SOCOM_Dashboard_Cycle_Users',
        'SOCOM_Dashboard_Site_Users',
        'SOCOM_Dashboard_Cap_Users'
    );
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('SOCOM_Site_User_model');
        $this->currClass = strtolower($this->CI->router->class);
            foreach($this->excludedControllers as &$c) {
                $c = strtolower($c);
            }
    }
    public function isSiteUser() {
        if (in_array($this->currClass, $this->excludedControllers) || !isset($this->CI->session->userdata('logged_in')['id'])) {
            return;
        }
        $isUser = $this->CI->SOCOM_Site_User_model->is_user(null,[0]);
        $isCapUser = $this->CI->SOCOM_Cap_User_model->is_user(null,[0]);
        if (!$isUser || !$isCapUser) {
                $this->CI->session->set_flashdata('error',
                    'User must choose to be both Pom Site and Capability Sponsor user who is approved by an Admin before using the Site.');
                redirect('/dashboard/myuser');
        }

        $this->CI->rbac_users->reinitialize_user();
    }
}