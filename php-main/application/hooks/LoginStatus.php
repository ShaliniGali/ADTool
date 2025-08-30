<?php
defined('BASEPATH') || exit('No direct script access allowed');

class LoginStatus {
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
        'CAC_controller'
    );

    private $excludedSSOControllers = array(
        'keycloak_tiles_controller',
        'keycloak_register_activate',
        'Platform_One_register_activate'
    );

    /**
     * This is the logged_in session template.
     * Any logged_in session changes in the user_login_success 
     * function inside the Login_model must be reflected below.
     * 
     * Each array value is a boolean indicating whether to check if
     * the value corresponding to the key in the logged_in
     * session is set or not.
     */
    private $logged_in_structure = array(
        'email' => true,
        'name' => true,
        'account_type' => true,
        'timestamp' => true,
        'profile_image' => false,
        'id' => true
    );

    public function __construct() {
        $this->CI = &get_instance();
        $this->currClass = strtolower($this->CI->router->class);
        foreach($this->excludedControllers as &$c) {
            $c = strtolower($c);
        }
    }

    public function checkStatus() {
        if (in_array($this->currClass, $this->excludedControllers)) {
            return;
        }
        // Bypass kc_tiles and excluded SSO controller since checkStatusTile will take care of this
        if ((RHOMBUS_SSO_KEYCLOAK=='TRUE' || RHOMBUS_SSO_PLATFORM_ONE=='TRUE') && in_array($this->currClass, $this->excludedSSOControllers)){
            return;
        }

        if($this->CI->session->userdata('logged_in')){
            if ($this->CI->session->has_userdata('redirect')) {
                $keeping_redirect_session = $this->CI->session->userdata('redirect');
                $this->CI->session->unset_userdata('redirect');
                redirect($keeping_redirect_session);
            } 
        } else {
            $this->CI->session->unset_userdata('redirect');
            $this->CI->session->set_userdata('redirect', current_url());
            
            if (RHOMBUS_SSO_KEYCLOAK=='TRUE' && $this->CI->session->userdata('tiles_logged_in')){
                redirect('kc_tiles');
            }
            else if(RHOMBUS_SSO_KEYCLOAK=='TRUE' && !$this->CI->session->userdata('tiles_logged_in')){
                redirect('rb_kc/authenticate/0/default');
            }
            else if(RHOMBUS_SSO_PLATFORM_ONE=='TRUE' && $this->CI->session->userdata('tiles_logged_in')){
                redirect('kc_tiles');
            }
            else if(RHOMBUS_SSO_PLATFORM_ONE=='TRUE' && !$this->CI->session->userdata('tiles_logged_in')){
                redirect('rb_p1/authenticate/0/default');
            }
            else{
                redirect('Login');
            }
        }
    }

    public function checkStatusTile(){
        if (!in_array($this->currClass, $this->excludedSSOControllers)) {
            return;
        }
        if($this->CI->session->userdata('tiles_logged_in')){
            // if ($this->CI->session->has_userdata('redirect')) {
            //     $keeping_redirect_session = $this->CI->session->userdata('redirect');
            //     $this->CI->session->unset_userdata('redirect');
            //     redirect($keeping_redirect_session);
            // }
            return;
        }
        else{
            $this->CI->session->unset_userdata('redirect');
            if(uri_string() != 'kc_tiles'){
                $this->CI->session->set_userdata('redirect', current_url());
            }
            // Redirect to SSO login page
            if (RHOMBUS_SSO_PLATFORM_ONE =='TRUE')
                redirect('rb_p1/authenticate/0/default');
            else
                redirect('rb_kc/authenticate/0/default');
        }
    }

    /**
     * Verify the integrity of the logged_in session structure.
     * This does not check whether the session values have been modified
     * beyond unsetting values from, removing or adding to the logged_in session.
     */
    public function verifySession() {
        if (in_array($this->currClass, $this->excludedControllers)) {
            return;
        }
        
        $logged_in = $this->CI->session->userdata('logged_in');

        if (!is_array($logged_in) || (count($logged_in) != count($this->logged_in_structure))) {
            echo '<script>alert("Alert: Tampering detected with the login session.")</script>';
            var_dump($currClass);
            exit;
        }
        foreach($this->logged_in_structure as $key => $val) {
            if (!array_key_exists($key, $logged_in) || ($val && !isset($logged_in[$key]))) {
                echo '<script>alert("Alert: Tampering detected with the login session")</script>';
                exit;
            }
        }
    }
}