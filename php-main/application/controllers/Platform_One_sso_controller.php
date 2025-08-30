<?php
defined('BASEPATH') or exit('No direct script access allowed');

// @todo verify user data from server
class Platform_One_sso_controller extends CI_Controller
{
    public function success($skip_flag=0, $authenticate_url='') {
        $result = $this->rhombus_platform_one->get_jwt();

        if (
            $result === false || 
            $this->rhombus_platform_one->check_app_access() === false
        ) {
            redirect('/rb_p1/failure');
        } else {
            $email = $this->rhombus_platform_one->get_current_access_token('email');
        }
        
        /**
         * Important to validate email before making an unnecessary query to the database.
         */
        if (!isValidEmailDomain($email)) {
            redirect('/rb_p1/failure');
        }

        if($skip_flag!=1){
            $session_data = array(
                'email' => $this->rhombus_platform_one->get_current_access_token('email'),
                'first_name' => $this->rhombus_platform_one->get_current_access_token('given_name'),
                'last_name' => $this->rhombus_platform_one->get_current_access_token('family_name'),
            );
            $this->session->set_userdata('tiles_logged_in', $session_data);
            redirect('kc_tiles', 'refresh');
        } else {
            $authArr = explode('::',$authenticate_url);
            $this->session->set_userdata('tile_id_logged_in', $authArr[1]);
            $authenticate_url = $authArr[0];
            $ueTable = $this->Platform_One_model->get_PLATFORMONE_table();
            $userInfo = $this->Platform_One_model->userExists($email, true, $ueTable);
            if (!empty($userInfo)) {
                $this->Login_model->user_login_success($userInfo, null);
                $this->Platform_One_model->updateAccount((int)$userInfo[0]['id']);
                redirect(base_url(
                    (strlen($authenticate_url) && $authenticate_url !== 'default' ? 
                    str_replace('-','/',$authenticate_url) :
                    '')
                ), 'refresh');
            } else {
                redirect(base_url('rb_p1/failure/'), 'refresh');
            }
        }


    }

    public function failure() {
        /**
         * 
         * Unset any sessions an ongoing UI has
         * 
         */
        $this->session->unset_userdata('logged_in');

        $this->rhombus_platform_one->can_access_page(true);

        $page_data['page_title'] = "Platform One Authentication Failure";
        $page_data['page_tab'] = "Platform One Authentication Failure";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array();
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $this->load->view('templates/header_view', $page_data);

        $view_data = array();
        $view_data['template'] = 1;
        
        
        $email = $this->rhombus_platform_one->get_current_access_token('email');

        if (isValidEmailDomain($email)) {

            $this->process_super_admin($email);

            if ($this->hasRegistered($email)) {
                $view_data['template'] = 2;
            } else {
                $view_data['template'] = 3;
                $js_files['requestaccountregistration'] = ['actions/request_account_registration.js','custom'];
                $this->load->library('RB_js_css');
                $this->rb_js_css->compress($js_files);
            }
        }
        
        $this->load->view('platform_one/message_view', $view_data);
    }

    private function hasRegistered($email) {
        $status = $this->Platform_One_model->promptAccountRegistration($email);
        return ($status !== true) && AccountStatus::hasStatus($status, AccountStatus::RegistrationPending);
    }

    public function requestRegistration() {
        $this->rhombus_platform_one->can_access_page(true);
        
        $response = array(
            'status' => 'failure',
            'message' => 'Invalid authentication. Please contact a Rhombus Power ISSO administrator.'
        );
        
        $current_jwt = $this->rhombus_platform_one->get_current_access_token();

        if (isValidEmailDomain($current_jwt->email)) {
            if ($this->hasRegistered($current_jwt->email)) {
                $response['message'] = $this->getAlreadyRegisteredMessage();
            } else {
                $data = $this->registration_info($current_jwt->email);
                $user_id = $this->Register_model->user_register($data);
                $this->Keycloak_model->registerKEYCLOAKUser($this->platform_one_format_token_data(
                    $user_id,
                    ["email"=>$current_jwt->email],
                    AccountStatus::RegistrationPending
                ));
                $this->Platform_One_model->registerPLATFORMONEUser($this->format_jwt_data(
                    $user_id,
                    AccountStatus::RegistrationPending
                ));
                $this->SSO_model->registerSSOUser($current_jwt->email, AccountStatus::RegistrationPending);

                $response['status'] = 'success';
                $po_icon = '<i class="text-muted far fa-check-circle fa-5x"></i>';
                $po_msg =  '
                    <h4 class="pt-4 mt-4 text-muted">
                        A Rhombus Power administrator has successfully received your account registration request.
                    </h4>
                ';
                $response['message'] = '
                    <div class="row pt-3">
                        ' . printout_message($po_icon, $po_msg) . '
                    </div>
                ';
            }
        }
        echo json_encode($response);
    }

    private function getAlreadyRegisteredMessage() {
        $po_icon = '<i class="text-muted fas fa-exclamation-triangle fa-5x"></i>';
        $po_msg = '<h4 class="pt-4 mt-4 text-muted">Account pending a Rhombus Power administrator\'s approval.</h4>';
        return printout_message($po_icon, $po_msg);
    }

    private function process_super_admin($email){
        $superAdmins = ADMIN_EMAILS;

        if(in_array($email, $superAdmins)){
            $userInfo = $this->Platform_One_model->userExists($email, false);
            if (!empty($userInfo)) { // found in users
                $this->update_to_active($userInfo, $this->Register_model);
                $user_id = (int)$userInfo[0]['id'];
            } else { // not found in users
                $register_info = $this->registration_info($email, "ADMIN");
                $user_id = $this->Register_model->user_register($register_info, false); // create record in users
            }

            if (is_int($user_id)) {
                // check if exists user_keycloak, create if it doesnt
                $userExistsTable = $this->Platform_One_model->get_PLATFORMONE_table();
                $userPLATFORMONE = $this->Platform_One_model->userExists($email, false, $userExistsTable);
                if(!empty($userPLATFORMONE)){
                    $this->update_to_active($userPLATFORMONE, $this->Platform_One_model);
                } else {
                    $this->Keycloak_model->registerKEYCLOAKUser($this->platform_one_format_token_data(
                        $user_id,
                        ["email"=>$email],
                        AccountStatus::Active
                    )); // create record in keycloak
                    $this->Platform_One_model->registerPLATFORMONEUser($this->format_jwt_data(
                        $user_id,
                        AccountStatus::Active
                    )); // create record in keycloak
                    $this->SSO_model->registerSSOUser($email, AccountStatus::Active); // create record in sso
                }
                
                $userInfo = $this->Platform_One_model->userExists($email, false);
                $ueTable = $this->Platform_One_model->get_PLATFORMONE_table();
                $userPLATFORMONE = $this->Platform_One_model->userExists($email, false, $ueTable);

                // dump
                $new_info = json_encode(array(
                    'user_id'=>$userInfo[0]["id"],
                    'platform_one_id' => $userPLATFORMONE[0]["id"],
                    'email' => $email
                ));
                $dump_data = array('type'=>"super_admin_platform_one_login", 'new_info'=>$new_info);
                $this->Generic->dump_users_info($dump_data, $userInfo[0]["id"]);

                // login the user
                $this->Login_model->user_login_success($userInfo, null);
                redirect(base_url());   
            } else {
                log_message('error', 'id not found when registering super admin');
                redirect('login/logout');
            }
        }
    }

    private function registration_info($email, $account_type = "USER"){
        $encode_password = $this->password_encrypt_decrypt->encrypt(hash('sha256', time()));
        
        $current_jwt = $this->rhombus_platform_one->get_current_access_token();

        return array(
            'email'            => $email,
            'password'         => $encode_password['password'],
            'name'             => ucfirst(strtolower($current_jwt->given_name.$current_jwt->family_name)),
            'account_type'     => $account_type,
            'message'          => 'SSO registration request for ' . $email,
            'saltiness'        => $encode_password['salt'],
            'login_layers'     => (LoginLayers::LayerOff).(LoginLayers::LayerOff).(LoginLayers::LayerOff)
        );
    }

    private function update_to_active($info, $model){
        if($info[0]["status"] != AccountStatus::Active){ // non active status
            $model->updateAccountStatus($info[0]["id"], AccountStatus::Active); // update to active
        }
    }
    
    /**
     * @param $user_id user id from users table
     * @param string $status must be AccountStatus::RegistrationPending or AccountStatus::Active
     * 
     * @return array
     */
    private function format_jwt_data($user_id, $status){
        $current_jwt = $this->rhombus_platform_one->get_current_access_token();
        $temp_data = [];
        $temp_data["user_id"] = $user_id;
        $temp_data["status"] = $status;
        $temp_data["jwt"] =  json_encode($current_jwt);
        $temp_data["email"] = $current_jwt->email;
        $temp_data["first_name"] = $current_jwt->given_name;
        $temp_data["last_name"] = $current_jwt->family_name;

        return $temp_data;
    }

        /**
     * @param $user_id user id from users table
     * @param array $data array with required email key
     * @param string $status must be AccountStatus::RegistrationPending or AccountStatus::Active
     * @return array
     */
    private function platform_one_format_token_data($user_id, $data, $status) {
        $temp_data = [];
        $temp_data["user_id"] = $user_id;
        $temp_data["status"] = $status;
        $temp_data["token"] =  '';
        $temp_data["email"] = $data["email"];
        $temp_data["session_state"] = '';
        $temp_data["login_code"] =  '';
        $temp_data["first_name"] = '';
        $temp_data["last_name"] = '';

        return $temp_data;
    }
}