<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if(!P1_FLAG){
    require_once(APPPATH.'libraries/php-jwt-5.5.1/src/JWT.php');
    require_once(APPPATH.'libraries/php-jwt-5.5.1/src/Key.php');
    require_once(APPPATH.'libraries/php-jwt-5.5.1/src/SignatureInvalidException.php');
    require_once(APPPATH.'libraries/php-jwt-5.5.1/src/ExpiredException.php');
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;

#[\AllowDynamicProperties]
class Rhombus_Keycloak {
    /**
     * @var const PUBLIC_KEY_FILE Is the location that the public key file will be stored
     *                            for future requests and will be used in decoding the JWT token
     *                            Please make sure that this location
     *                            is writable by the web user.
     */
    private const PUBLIC_KEY_FILE = 'application/secure_uploads/keycloak/public_key.txt';

    /**
     * @var const PUBLIC_KEY_REFRESH the numer of seconds which will refresh the PUBLIC_KEY_FILE
     */
    private const PUBLIC_KEY_REFRESH = 86400;

    private $CI;

    /**
     * @var array $keycloak_error contains errors encountered when using the object
     */
    private $keycloak_error = [];

    /**
     * @var stdClass $current_token the json_decoded result from get_token and get_refresh_token
     */
    private $current_token = null;

    /**
     * @var stdClass $current_id_token the JWT validated token.  This can be 
     *                               found on self::$current_token->access_token or $current_token->id_token
     */
    private $current_id_token = null;

    /**
     * @var stdClass $current_access_token the JWT validated token.  This can be 
     *                               found on self::$current_token->access_token or $current_token->id_token
     */
    private $current_access_token = null;

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
        return $this->keycloak_error;
    }

    /**
     * Resets the object, used when getting the initial token or logging out
     */
    public function reset_state() {
        $this->current_token = null;
        $this->current_decoded_token = null;
        $this->keycloak_error = [];

        $this->CI->session->unset_userdata('keycloak_token');
        $this->CI->session->unset_userdata('keycloak_session');
        $this->CI->session->unset_userdata('keycloak_login_code');
    }

    /**
     * Will check if the current token is active, not expired
     * A refresh token can also be fetched
     * 
     * @param bool $refresh if true the refresh token will be fetched 
     *                      in the event that the token is expired
     * 
     * @return bool true if the token or refresh token are active
     */
    public function check_token_active($refresh = false) {
        $token = json_decode($this->CI->session->userdata('keycloak_token'));

        $result = $this->process_token($token, 'access_token');

        if (!$result && $refresh === true) {
            $result = $this->get_refresh_token();
        }

        return $result;
    }

    /**
     * Will return the available apps that the user can access
     * 
     * @return array
     */
    public function get_available_apps() {
        $apps_allowed = [];
        
        if (
            $this->check_token_active() && 
            defined('KEYCLOAK_SSO_CLIENT_SCOPE')
        ) {
            $apps_allowed = $this->get_current_access_token(KEYCLOAK_SSO_CLIENT_SCOPE) ?? [];
            
            foreach ($apps_allowed as &$app) {
                $app = strtolower($app);
            }
            unset($app);
        } 
        
        if (empty($apps_allowed)) {
            $this->keycloak_error[] = 'No available apps, is the user authenticated or is KEYCLOAK_SSO_CLIENT_SCOPE incorrect?';
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
            $this->check_token_active(true) && 
            $this->check_app_access() && 
            !$this->has_error()
        ) {
            $result = true;
        } else {
            $this->keycloak_error[] = 'User cannot access current page';
        }

        if ($result === false  && $redirect === true) {
            redirect('/login/logout');
        }

        return $result;
    }

    /**
     * Will chewck if the user has access to the current application.  The check is based upon
     * the value of KEYCLOAK_SSO_APP_NAME which can be found in constants.php.  You need make sure each 
     * application has a value for the KEYCLOAK_SSO_APP_NAME or the user will not be able to log in.
     * This can be configured for each application with environment variables.
     * 
     * If this appears to not work do not forget to check the errors using self::get_error and self::has_error
     * 
     * @return bool true if the user has access to the application
     */
    public function check_app_access() {
        $result = false;

        if (defined('KEYCLOAK_SSO_APP_NAME') && defined('KEYCLOAK_SSO_CLIENT_SCOPE')) {
            $apps_allowed = $this->get_available_apps();

            if (is_array($apps_allowed) && in_array(KEYCLOAK_SSO_APP_NAME, $apps_allowed,  true)) {
                $result = true;
            } else {
                $this->keycloak_error[] = 'User does not have access to this Guardian application';
            }
        } else {
            $this->keycloak_error[] = 'The environment variables not set for {APP}_KEYCLOAK_SSO_APP_NAME and {APP}_KEYCLOAK_SSO_CLIENT_SCOPE';
        }

        return $result;
    }

    /**
     * Will return a property of the JWT id token.
     * 
     * @return mixed the value of the token or the entire id JWT token plus the current token 
     *               returned from the self::get_token or self::get_refresh_token.
     */
    public function get_current_id_token($property = null) {
        if (isset($this->current_id_token->{$property})) {
            $token = $this->current_id_token->{$property};
        } else if ($property === null) {
            $token = $this->current_id_token;
            $token->current_token = $this->current_token;
        } else {
            $token = null;
        }

        return $token;
    }

    public function get_current_access_token($property = null) {
        if (isset($this->current_access_token->{$property})) {
            $token = $this->current_access_token->{$property};
        } else if ($property === null) {
            $token = $this->current_access_token;
            $token->current_token = $this->current_token;
        } else {
            $token = null;
        }

        return $token;
    }
    
    /**
     * You must configure the constant KEYCLOAK_SSO_ID_TOKEN and KEYCLOAK_SSO_CLIENT_SECRET.
     * 
     * KEYCLOAK_SSO_ID_TOKEN will call the get_id_token method, otherwise get_access_token will be called
     * KEYCLOAK_SSO_CLIENT_SECRET is required if you want to use get_id_token
     * 
     * If KEYCLOAK_SSO_ID_TOKEN (you will be using access_token) you will also be able to use the refresh_token
     * 
     * Will get the authentication token after the user has logged into the keycloak SSO
     * It is required that the KEYCLOAK_SSO_CLIENT_ID is configured from constants.php.  This can
     * be done using environment variables.
     * 
     * Errors can be checked here in the event that the method returns false.
     * 
     * @param string $code the code returned from a HTTP query to the url returned from self::authenticate
     * @param string $session_state the session value used for the user during the login session returned from
     *                              a HTTP query to the url returned from self::authenticate
     * 
     * @return bool true if the token was verified using JWT
     */
    public function get_token($code, $session_state,$skip_flag=0,$authenticate_url='default') {
        $this->reset_state();

        $data = $this->get_access_token($code, $session_state,$skip_flag,$authenticate_url);

        $result = $this->_get_token($data, 'access_token', $code, $session_state);

        return $result;
    }

    public function _get_token($data, $token_name, $code, $session_state) {
        $result = false;

        $this->CI->session->set_userdata('keycloak_session', $session_state);
        $this->CI->session->set_userdata('keycloak_login_code', $code);
        
        $url = $this->keycloak_url([], 'token');

        list($token, $response_code) = $this->curl_keycloak($url, [], $data);
        
        if ($response_code === 200) {
            $result = $this->process_token($token, $token_name);
            if ($result === true) {
                $this->CI->session->set_userdata('keycloak_token', json_encode($token));
            }
        } else {
            $this->keycloak_error[] = sprintf('Unable to authenticate the user with keycloak. HTTP status code %d.', $response_code);
        }

        return $result;
    }

    /**
     * Will enable detection of KEYCLOAK_SSO_CLIENT_SCOPE parameter using access_token
     * 
     * This method supports refresh_token using grant_type = authorization_code
     * 
     * @param string $code the code returned from a HTTP query to the url returned from self::authenticate
     * @param string $session_state the session value used for the user during the login session returned from
     *                              a HTTP query to the url returned from self::authenticate
     * 
     * @return array 
     */
    protected function get_access_token($code, $session_state,$skip_flag=0,$authenticate_url='default') {
        $data = [
            'client_id' => KEYCLOAK_SSO_CLIENT_ID,
            'grant_type' => 'authorization_code',
            'session_state' => $session_state,
            'code' => $code,
            'redirect_uri' => base_url('rb_kc/success/'.$skip_flag.'/'.$authenticate_url)
        ];
        $this->add_request_credentials($data);
    
        return $data;
    }

    /**
     * Will verify the session_state value matches with the user session from the initial request
     * 
     * @return bool true if the session_state from the self::get_token, self::current_token->session state 
     *                      matches the user PHP session value for keycloak_session
     */
    public function verify_session_state() {
        $session_state = false; 

        $keycloak_session = $this->CI->session->userdata('keycloak_session');

        if (
            isset($this->current_token->session_state) &&
            $this->current_token->session_state === $keycloak_session
        ) {
            $session_state = true;
        } else {
            $this->keycloak_error[] = 'Keycloak session_state and application session_state do not match';
        }

        return $session_state;
    }

    /**
     * Checks for some required token properties to work with JWT
     * 
     * @return bool true if the required JWT properties are on the self::$current_token token
     */
    public function verify_token_properties() {
        $token_properties = $this->verify_access_token_properties();

        return $token_properties;
    }

    /**
     * Will verify access_token and refresh_token are in the JWT token returned from KEYCLOAK
     */
    protected function verify_access_token_properties() {
        $token_properties = false;

        if (
            isset($this->current_token->access_token, $this->current_token->refresh_token) &&
            (KEYCLOAK_SSO_ID_TOKEN !== 'TRUE' || isset($this->current_token->id_token))
        ) {
            $token_properties = true;
        } else {
            $this->keycloak_error[] = sprintf('Keycloak token does not have access_token, refresh_token or id_token. KEYCLOAK_SSO_ID_TOKEN: %s', KEYCLOAK_SSO_ID_TOKEN);
        }

        return $token_properties;
    }

    /**
     * Will get the users refresh token and relies on verify_token_properties
     * KEYCLOAK_SSO_CLIENT_ID must be configured in constants.php and can be done using 
     * environment variables.
     * 
     * Check errors if this is returning false.
     * 
     * @return bool true if the refresh token request was successful
     */
    protected function get_refresh_token($skip_flag=1,$authenticate_url='default') {
        $result = false;


        if ($this->verify_token_properties('access_token') === true) {
            $url = $this->keycloak_url([], 'token');

            $data = [
                'client_id' => KEYCLOAK_SSO_CLIENT_ID,
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->current_token->refresh_token,
                'response_type' => 'token',
                'redirect_uri' => base_url('rb_kc/success/'.$skip_flag.'/'.$authenticate_url)
            ];
            $this->add_request_credentials($data);

            $headers = [
                sprintf('Bearer: %s', $this->current_token->access_token)
            ];
            
            list($token, $response_code) = $this->curl_keycloak($url, $headers, $data);
        
            if ($response_code === 200) {
                $result = $this->process_token($token, 'access_token');
                if ($result === true) {
                    $this->keycloak_error = [];

                    $this->CI->Keycloak_model->updateToken();
                }
            } else {
                $this->keycloak_error[] = sprintf('Unable to get refresh token for the user with keycloak. HTTP status code %d.', $response_code);
            }
        }

        return $result;
    }

    /**
     * This will take the token returnd from a token or refresh token request
     * and verify the session_state variable, required properties for access_token and refresh_token
     * It will then process the token into a JWT decoded token.
     * 
     * Errors can be checked here if this is returning false
     * 
     * @return bool true if the token was proccessed using JWT
     */
    private function process_token($token) {
        $result = false;

        $this->current_token = $token;

        if (
            $this->verify_session_state() === true &&
            $this->verify_token_properties() === true
        ) {
            if (KEYCLOAK_SSO_ID_TOKEN === 'TRUE') {
                $this->current_id_token = $this->validate_token($this->current_token, 'id_token');
            }
            $this->current_access_token = $this->validate_token($this->current_token, 'access_token');

            if ($this->current_access_token !== false) {
                $result = true;
            } else {
                $this->keycloak_error[] = 'Keycloak SSO session does not have decoded JWT token, try again';
            }
        } else {
            $this->keycloak_error[] = 'Could not process token, session_state could not be verified';
        }

        return $result;
    }
    
    /**
     * Will validate the token using JWT and Keycloak Public Key.
     * This method will write the static::PUBLIC_KEY_FILE file and store it
     * for future uses if not available.  
     * 
     * @param stdClass $token the value must have the access_token property
     * 
     * @return stdClass|bool the decoded JWT token or false on failure
     */
    private function validate_token($token, $token_name) {
        $decoded = false;

        $public_key = $this->create_public_key();

        if ($public_key === false) {
            $this->keycloak_error[] = sprintf('Unable to read public key file check permissions %s', static::PUBLIC_KEY_FILE);
            
        } else if(!isset($token->$token_name)) {
            $this->keycloak_error[] = sprintf("Required %s property not found on request token");
        } else {
            $jwt = $token->$token_name;
        
            $key = $public_key;
        
            try {
                $decoded = JWT::decode($jwt, new Key($key, 'RS256'));
            } catch(SignatureInvalidException|ExpiredException $e) {
                $this->keycloak_error[] = $e->getMessage();
            }
        }

        return $decoded;
    }

    /**
     * Will create a public_key if not found and will return the public key value
     * 
     * @return bool|string false if unable to generate a public key or the public key
     */
    private function create_public_key() {
        $public_key = false;

        if (
            is_file(static::PUBLIC_KEY_FILE) && 
            is_readable(static::PUBLIC_KEY_FILE) && 
            filemtime(static::PUBLIC_KEY_FILE) > (time()-static::PUBLIC_KEY_REFRESH)

        ) {
            $public_key = file_get_contents(static::PUBLIC_KEY_FILE);
        }

        if ($public_key === false) {
            $url = $this->keycloak_url();

            list($info, $response_code) = $this->curl_keycloak($url);
            if (isset($info->public_key)) {
                
                $public_key = <<<EOT
-----BEGIN PUBLIC KEY-----
{$info->public_key}
-----END PUBLIC KEY-----
EOT;

                if (write_file(static::PUBLIC_KEY_FILE, $public_key) === false) {
                    $public_key = false;
                }
            }
        }

        return $public_key;
    }

    /**
     * Requires that the KEYCLOAK_SSO_CLIENT_ID be set this can be done using 
     * environment variables see constants.php
     * 
     * @return string the autnehtication url for keycloak
     */
    public function get_authenticate_url($skip_flag=0,$get_authenticate_url='default') {

		$data = [
			'response_type' => 'code',
			'response_mode' => 'query', //'form_post',
			'client_id' => KEYCLOAK_SSO_CLIENT_ID,
			'redirect_uri' => base_url(
                'rb_kc/success/0'.
                (strlen($get_authenticate_url) ? '/' . $get_authenticate_url : '')
            )
		];

        if($skip_flag==1){
            $data['redirect_uri'] = base_url('rb_kc/success/'.$skip_flag.'/'.$get_authenticate_url);
        }


        if (KEYCLOAK_SSO_ID_TOKEN === 'TRUE') {
            $data['scope'] = 'openid';
        }

        return $this->keycloak_url($data, 'auth');
    }
    
    /**
     * Will log the user out of the keycloak server
     * and requres an active token
     * 
     * @return bool true if the user has been logged out
     */
    public function logout() {
        $result = false;

        if ($this->check_token_active(false)) {

            $token = json_decode($this->CI->session->userdata('keycloak_token'));

            $url = $this->keycloak_url([], 'logout');
            
            $data = [
                'client_id' => KEYCLOAK_SSO_CLIENT_ID,
                'refresh_token' => $token->refresh_token,
                'session_state' => $token->session_state
            ];
            $this->add_request_credentials($data);

            $headers = [
                sprintf('Bearer: %s', $token->access_token)
            ];

            list(, $response_code) = $this->curl_keycloak($url, $headers, $data);
        
            if ($response_code === 204) {
                $result = true;
            } else {
                $this->keycloak_error[] = 'Unable to log the user out of keycloak';
            }
        } else {
            $this->keycloak_error[] = 'No access or refresh token found to log user out of keycloak';
        }

        $this->log_all_errors();
        
        $this->reset_state();

        return $result;
    }

    /**
     * Will return a keycloak url.
     * 
     * It is required that KEYCLOAK_SSO_URL and KEYCLOAK_SSO_REALM are configured in constants.php. 
     * KEYCLOAK_SSO_URL is the URL of the server
     * KEYCLOAK_SSO_REALM is the realm that will be used by the application
     * This can be done using environment variables.
     * KEYCLOAK_SSO_DEV can also be configured in constants.php for development purposes.
     * 
     * @param array $data a possible query string that is required for the request
     * @param string $service supported values are auth, token and logout
     * 
     * @return string the url that can be used for a request to the server.
     */
    public function keycloak_url($data = [], $service = null) {
        if (KEYCLOAK_SSO_DEV === 'TRUE' && $service !== 'auth') {
            $http_host = 'http://keycloak:8080';
        } else {
            $http_host =  KEYCLOAK_SSO_URL;
        }
        
        if (KEYCLOAK_USE_AUTH_URL === 'TRUE') {
            $http_host .= '/auth';
        }

        $url_str = sprintf('%s/realms/%s', $http_host, KEYCLOAK_SSO_REALM);
        $endpoint = '%s/protocol/openid-connect/%s';

        switch($service) {
            case 'auth':
                $url_str = sprintf($endpoint, $url_str, 'auth');
                break;
            case 'token':
                $url_str = sprintf($endpoint, $url_str, 'token');
                break;
           /* case 'userinfo':
                $url_str = sprintf($endpoint, $url_str, 'userinfo');
                break;
            case 'certs':
                $url_str = sprintf($endpoint, $url_str, 'certs');
                break;
            case 'introspect':
                $url_str = sprintf($endpoint, $url_str, 'token/introspect');
                break;
            case 'revoke':
                $url_str = sprintf($endpoint, $url_str, 'revoke');
                break;*/
            case 'logout':
                $url_str = sprintf($endpoint, $url_str, 'logout');
                break;
        }

        if (!empty($data)) {
            $query = http_build_query($data);
            $url = sprintf("%s?%s", 
                $url_str,
                $query
            );
        } else {
            $url = $url_str;
        }

        return $url;
    }

    /**
     * Will make a request to the Keycloak server
     * 
     * @params string $url a url from self::keycloak_url
     * @params array $headers an array of headers to send with the request
     * @params array $data an array of post parameters
     */
    protected function curl_keycloak($url, $headers = [], $data = []) {
        $ch = curl_init($url);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (strlen($response) > 0) {
            $response = json_decode($response);
        }

        $response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        unset($ch);

        if (RB_KEYCLOAK_DEBUG === 'TRUE') {
            $this->keycloak_error[] = sprintf('Keycloak URL: %s', $url);
            $this->keycloak_error[] = sprintf('Keycloak headers sent: %s', var_export($headers, true));
            $this->keycloak_error[] = sprintf('Keycloak post fields: %s', var_export($data, true));
            $this->keycloak_error[] = sprintf('Keycloak response code: %s', $response_code);
            $this->keycloak_error[] = sprintf('Keycloak response: %s', json_encode($response));
        }

        return [$response, $response_code];
    }

    /**
     * Will add the client_secret in use with call to keycloak API
     */
    protected function add_request_credentials(&$data) {
        $clientSecret = trim(KEYCLOAK_SSO_CLIENT_SECRET);
        
        if (strlen($clientSecret) > 0) {
            $data['client_secret'] = $clientSecret;
        }
    }

    /**
     * Will log all keycloak errors
     */
    protected function log_all_errors() {
        if ($this->has_error()) {
            log_message('error', 'Keycloak authentication errors:');
            log_message('error', implode("\n", $this->get_error()));
        }

        $this->keycloak_error = [];
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
        $this->log_all_errors();
    }
}