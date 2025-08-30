<?php

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

#[AllowDynamicProperties]
class UserAuthorization {
    private $token = null;
    private $exp_token = null;

    private $decoded_token = null;

    public function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->model('SOCOM_Site_User_model');
        $this->CI->load->model('SOCOM_Cap_User_model');
    }

    public function get_token() {
        $jwt = $this->CI->session->userdata('auth_jwt');
        if ($jwt !== null) {
            $this->token  = json_decode($jwt);
            
            try {
                $this->decoded_token = JWT::decode($this->token->access_token, new Key(SOCOM_JWT_SECRET_KEY, SOCOM_JWT_ALGORITHM));
            } catch(ExpiredException $e) {
                $this->exp_token = $e->getPayload();
            }
        }

        return $this->token;
    }

    public function get_decoded_token() {
        if ($this->decoded_token === null) {
            $this->get_token();
        }

        return $this->decoded_token;
    }

    public function set_token($user_jwt) {
        $json_decoded_jwt = json_decode($user_jwt);
        try {
            if (isset($json_decoded_jwt->detail)) {
                throw new UnexpectedValueException($json_decoded_jwt->detail);
            }
            $decoded_jwt = JWT::decode($json_decoded_jwt->access_token, new Key(SOCOM_JWT_SECRET_KEY, SOCOM_JWT_ALGORITHM));
        } 
        catch (UnexpectedValueException $e) {
            log_message('error', $e->getMessage());
            return false;
        }

        $this->CI->session->set_userdata('auth_jwt', $user_jwt);
    }

    public function auth_jwt() {
        $url = RHOMBUS_PYTHON_URL.'/auth/jwt';
        $headers = 'Content-Type: ' . APPLICATION_JSON . "\r\n";

        $api_params['user_id'] = (int)$this->CI->session->userdata('logged_in')['id'];
        $user_role = $this->CI->SOCOM_Site_User_model->get_user()[0]['GROUP'] ?? 'None';
        $api_params['user_role'] = SiteUsersType::from($user_role)->value;
        $cap_role = $this->CI->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? 'None';
        $api_params['cap_group'][] = $cap_role;

        $params = json_encode($api_params);
        $php_api_http_status = false;

        $user_jwt = php_api_call('POST', $headers, $params, $url, $php_api_http_status);

        $this->set_token($user_jwt);
    }

    public function auth_jwt_refresh() {
        $this->get_token();

        $url = RHOMBUS_PYTHON_URL.'/auth/jwt/refresh';
        $headers = 'Content-Type: ' . APPLICATION_JSON ."\r\n";
        
        $api_params = http_build_query([
            'token' => $this->token->access_token,
            'expires_delta' => (time() - (int)$this->exp_token->exp)
        ]);

        $user_jwt = php_api_call('POST', $headers, null, $url.'?'.$api_params, $php_api_http_status);

        $this->set_token($user_jwt);
    }

    public function check_refresh() {
        $result = false;
        $this->get_token();
        
        if (isset($this->exp_token->exp) && $this->exp_token->exp < time()) {
            $result = true;
        }

        return $result;
    }

    public function reset_state() {
        $this->token = null;

        $this->CI->session->unset_userdata('auth_jwt');
    }
}

enum SiteUsersType: string  {
	case NONE = 'None';
	case POM_ADMIN = 'Pom Admin';
	case POM_USER = 'Pom User';
}
