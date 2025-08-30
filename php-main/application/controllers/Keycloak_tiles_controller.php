<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Keycloak_tiles_controller extends CI_Controller {
    public function index() {
		// Tile data
		$tiles = $this->Keycloak_tiles_model->convert_tile_data_json();
		$data = array();
		$data['tile_data'] = json_encode($tiles);

		// user data
		$user_data = ($this->session->userdata('tiles_logged_in')!=NULL) 
            ? $this->session->userdata('tiles_logged_in') : $this->session->userdata('logged_in');
		$data['user_data'] = json_encode($user_data);
		$this->load->view('react/home_view',$data);
	}

    public function tile_login(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $app_label = strtoupper(str_replace(" ", "_", $data_check["post_data"]["app_label"]));
            $app_schema = getenv($app_label.'_SPLASH_SCHEMA');
            $app_url = getenv($app_label.'_SPLASH_URL');
            $email = $this->session->userdata('tiles_logged_in')['email'];
        
            
            if($app_schema == false || $app_schema == "" || $app_schema == NULL){
                echo json_encode(array("result"=>"failed"));
            } else {
                $status = $this->Keycloak_model->promptAccountRegistration($email, $app_schema);
                if(($status !== true) && AccountStatus::hasStatus($status, AccountStatus::Active)){
                    $result = 'success';
                    echo json_encode(array("result"=>$result,'app_url'=>$app_url));
                } else {
                    $result = AccountStatus::hasStatus($status, AccountStatus::RegistrationPending)
                        ? 'regPending' : 'notRegistered';
                    echo json_encode(array("result"=>$result));
                }
            }
        }
    }

    public function request_register() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        $post_data = $data_check["post_data"];
        $app_ids = $post_data['applications'];
        $app_ids_array = explode(',',$app_ids);

        $statusHeader = true;
        foreach($app_ids_array as $app_id){
            $app_label = constant('SSO_'.$app_id);
            $app_schema = $app_label;
            $app_url = constant('SSO_'.$app_label.'_URL');
            if($app_schema == false || $app_schema == "" || $app_schema == NULL){
                $statusHeader = false;
            } else {
                $kc_library = RHOMBUS_SSO_PLATFORM_ONE == 'TRUE'? $this->rhombus_platform_one: $this->rhombus_keycloak;

                $tiles_user_data = $this->session->userdata('tiles_logged_in');
                
                $kc_library->can_access_page(true);
                
                $current_token = $kc_library->get_current_access_token();

                $email = $tiles_user_data['email'];
                $response = [];
                $init_user_data = array(
                    "email" => $email,
                    "first_name" => $tiles_user_data['first_name'],
                    "last_name" => $tiles_user_data['last_name']
                );
                if (!isValidEmailDomain($email)) {
                    echo json_encode($response);
                    return;
                }
            
                $status = $this->Keycloak_model->promptAccountRegistration($email, $app_schema);

                $subapp_data = array(
                    'email' => $init_user_data['email'],
                    'subapp_id' => $app_id,
                    'status' => AccountStatus::RegistrationPending,
                    'account_type' => 'USER',
                    'timestamp' => time()
                );
                if (in_array($init_user_data['email'], ADMIN_EMAILS)) {
                    $subapp_data['status'] = AccountStatus::Active;
                    $subapp_data['account_type'] = 'ADMIN';
                }
                $this->Keycloak_tiles_model->registerUserOnSubApp($subapp_data, $app_schema);
                if (
                    ($status !== true) && 
                    (
                        AccountStatus::hasStatus($status, AccountStatus::RegistrationPending) || 
                        AccountStatus::hasStatus($status, AccountStatus::Active)
                    )
                ) {
                    $result = AccountStatus::hasStatus($status, AccountStatus::RegistrationPending)
                        ? 'regPending' : 'userAlreadyExists';
                    $response['result'] = $result;
                } else {
                    $data = $this->registerInfo($init_user_data);
                    $user_id = $this->Register_model->user_register(
                        $data, $data['adminVerification'], $app_schema, $app_url
                    );
                    $user_data = array_merge($init_user_data, array(
                        "user_id" => $user_id,
                        "status" =>  AccountStatus::RegistrationPending,
                        "token" =>  $current_token->current_token->access_token,
                        "session_state" => $current_token->session_state,
                        "login_code" =>  $this->session->userdata('keycloak_login_code'),
                    ));

					if (in_array($data['email'], ADMIN_EMAILS)) {
						$user_data['status'] = AccountStatus::Active;
					}

                    $this->Keycloak_model->registerKEYCLOAKUser($user_data, $app_schema);
                    $this->SSO_model->registerSSOUser($email, AccountStatus::RegistrationPending, $app_schema);
                    $user_data['jwt'] = $user_data['token'];
                    unset($user_data['token'], $user_data['session_state'], $user_data['login_code']);
                    $this->Platform_One_model->registerPLATFORMONEUser($user_data, $app_schema);

                    $response['result'] = 'userRegistered';
                }
                
            }
        }
        if($statusHeader==false){
            $this->output->set_status_header(401);
        }
        echo json_encode($response);
    }

    private function registerInfo($data){
        $encode_password = $this->password_encrypt_decrypt->encrypt(hash('sha256', time()));

        $adminVerification = true;
        if (in_array($data['email'], ADMIN_EMAILS)) {
            $adminVerification = false;
        }
        return array(
            'email'            => $data['email'],
            'password'         => $encode_password['password'],
            'name'             => ucfirst(strtolower($data['first_name'].$data['last_name'])),
            'account_type'     => 'USER',
            'message'          => 'SSO registration request for ' . $data['email'],
            'saltiness'        => $encode_password['salt'],
            'adminVerification' => $adminVerification,
            'login_layers'     => (LoginLayers::LayerOff).(LoginLayers::LayerOff).(LoginLayers::LayerOff)
        );
    }
}
