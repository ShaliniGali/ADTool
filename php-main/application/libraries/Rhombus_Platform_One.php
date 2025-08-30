<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if(!P1_FLAG){
    require_once(APPPATH.'libraries/php-jwt-5.5.1/src/JWT.php');
}

use Firebase\JWT\JWT;

#[\AllowDynamicProperties]
class Rhombus_Platform_One {
    private $CI;

    /**
     * @var array $p1_error contains errors encountered when using the object
     */
    private $p1_error = [];

    /**
     * @var stdClass $current_decoded_jwt the JWT validated token. 
     * 
     */
    private $current_decoded_jwt = null;

    public function __construct() {
        $this->CI =& get_instance();

        $this->CI->load->helper('file');
    }        

    /**
     * Used to determine if there were errors or not
     * 
     * @return bool true if the errors are present
     */
    public function has_error() {
        return count($this->get_error()) > 0;
    }

    /**
     * Will return the errors that occured.  These will be written to the 
     * Code igniter log at the end of each request see self::__destruct
     * 
     * @return array an array of errors
     */
    public function get_error() {
        return $this->p1_error;
    }

    /**
     * Resets the object, used when getting the initial token or logging out
     */
    public function reset_state() {
        $this->current_jwt = null;
        $this->current_decoded_jwt = null;
        $this->p1_error = [];

        $this->CI->session->unset_userdata('p1_jwt');
    }

    /**
     * Will check if the current JWT is active
     * 
     * @return bool true if the JWT or refresh token are active
     */
    public function check_jwt_active() {
        return  $this->get_jwt();
    }

    /**
     * Will return the available apps that the user can access
     * 
     * @return array
     */
    public function get_available_apps() {
        $apps_allowed = [];
        
        if (
            $this->check_jwt_active() && 
            defined('PLATFORM_ONE_SSO_PARAM')
        ) {
            $apps_allowed = $this->get_current_access_token(PLATFORM_ONE_SSO_PARAM) ?? [];
            
            foreach ($apps_allowed as &$app) {
                $app = strtolower($app);
            }
            unset($app);
        } 
        if (empty($apps_allowed)) {
            $this->p1_error[] = 'No available apps, is the user authenticated or is PLATFORM_ONE_SSO_PARAM incorrect?';
        }

        return $apps_allowed;
    }

    /**
     * Will test if the user can access the current page.  This requires that
     * the constant for self::check_app_access is configured for this to work.
     * 
     * @param bool $redirect true will redirect the user to the logout page if they do not have access.
     *                       using false will require the calling code to handle the unauthorized user
     * 
     * @return bool true if the user can access the page.
     */
    public function can_access_page($redirect = true) {
        $result = false;
 
        if (
            $this->check_jwt_active(true) && 
            $this->check_app_access() && 
            !$this->has_error()
        ) {
            $result = true;
        } else {
            $this->p1_error[] = 'User cannot access current page';
        }

        if ($result === false  && $redirect === true) {
            redirect('/login/logout');
        }

        return $result;
    }

    /**
     * Will check if the user has access to the current application.  The check is based upon
     * the value of PLATFORM_ONE_SSO_APP_NAME which can be found in constants.php.  You need make sure each 
     * application has a value for the PLATFORM_ONE_SSO_APP_NAME or the user will not be able to log in.
     * This can be configured for each application with environment variables.
     * 
     * If this appears to not work do not forget to check the errors using self::get_error and self::has_error
     * 
     * @return bool true if the user has access to the application
     */
    public function check_app_access() {
        $result = false;

        if (defined('PLATFORM_ONE_SSO_APP_NAME') && defined('PLATFORM_ONE_SSO_APP_NAME')) {
            $apps_allowed = $this->get_available_apps();

            if (is_array($apps_allowed) && in_array(PLATFORM_ONE_SSO_APP_NAME, $apps_allowed,  true)) {
                $result = true;
            } else {
                $this->p1_error[] = 'User does not have access to this Guardian application';
                $this->p1_error[] = sprintf('Allowed Apps %s', var_export($apps_allowed, true));
                $this->p1_error[] = sprintf('Expected App Name %s', PLATFORM_ONE_SSO_APP_NAME);
            }
        } else {
            $this->p1_error[] = 'The environment variables not set for {APP}_P1_SSO_APP_NAME and {APP}_P1_SSO_APP_NAME';
        }

        return $result;
    }

    /**
     * Will return a property of the JWT decoded token.
     * 
     * @return mixed the value of the token or the entire decoded JWT token 
     *               returned from the self::get_jwt.
     */
    public function get_current_access_token($property = null) {
        if (isset($this->current_decoded_jwt->{$property})) {
            $jwt = $this->current_decoded_jwt->{$property};
        } else if ($property === null) {
            $jwt = $this->current_decoded_jwt;
            $jwt->current_token = new stdClass();
            $jwt->current_token->access_token = $this->current_jwt;
        } else {
            $jwt = null;
        }

        return $jwt;
    }

    /**
     * Will use the WWW-Authorization: header, nginx needs to set this.
     * 
     * Errors can be checked here in the event that the method returns false.
     * 
     * @return bool true if the token was verified using JWT
     */
    public function get_jwt() {
        $result = false;

        $this->reset_state();

        $jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? false;
        if ($jwt !== false) {
            $result = $this->process_jwt($jwt);
        } else {
            $this->p1_error[] = sprintf('Unable to authenticate the user because the Authorization header is missing.');
        }

    
        return $result;
    }

    /**
     * This will take the JWT from Authorization header.
     * It will then process the token into a JWT decoded token.
     * 
     * Errors can be checked here if this is returning false
     * 
     * @return bool true if the token was proccessed using JWT
     */
    private function process_jwt($jwt) {
        $result = false;

        $this->current_jwt = trim(str_replace('Bearer',  '', $jwt));

        if (RB_PLATFORM_ONE_DEBUG) {
            log_message('error', sprintf('JWT TOKEN: %s', $this->current_jwt));
        }

        $this->current_decoded_jwt = $this->validate_jwt($this->current_jwt);

        if ($this->current_decoded_jwt !== false) {
            $result = true;

            if ($result === true) {
                $this->CI->session->set_userdata('p1_jwt', $this->current_jwt);
            }
        } else {
            $this->p1_error[] = 'Platform One SSO session does not have decoded JWT token, try again';
        }

        return $result;
    }

    /**
     * Will validate the JWT and Platform One Public Key.
     * This method will write the static::PUBLIC_KEY_FILE file and store it
     * for future uses if not available.  
     * @param string $jwt
     * 
     * @return stdClass|bool the decoded JWT token or false on failure
     */
    private function validate_jwt($jwt) {
        $body64 = explode('.', $jwt)[1] ?? false;

        if ($body64 !== false) {
            $base64_decoded = JWT::urlsafeB64Decode($body64);
            $decoded = json_decode($base64_decoded);

            if (RB_PLATFORM_ONE_DEBUG) {
                log_message('error', sprintf('JWT Decoded: %s', var_export($decoded, true)));
            }
        } else {
            $decoded = false;
        }

        return $decoded;
    }
    
    /**
     * Will log errors to the Code Igniter log file if they are present at the 
     * end of each request.
     * 
     * Set log_threshold in config.php to 1 and review the current logs.
     * 
     * The application/log folder must be writable by the web user.
     */
    public function __destruct() {
        if ($this->has_error()) {
            log_message('error', 'Platform One authentication errors:');
            log_message('error', implode("\n", $this->get_error()));
        }
    }
}