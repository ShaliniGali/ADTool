<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SSO_controller extends CI_Controller
{
	protected $triangle_html_icon = '<i class="text-muted fas fa-exclamation-triangle fa-5x"></i>';
	protected $circle_html_icon = '<i class="text-muted far fa-check-circle fa-5x"></i>';
	protected $sso_failure_message = 'SSO Failure';

    private $rbenc;
    public function __construct() {
        parent::__construct();
        require_once(realpath(APPPATH . 'simplesamlphp/lib/SimpleSAML/Utils/RBEncrypt.php'));
        $this->rbenc = new \SimpleSAML\Utils\RBEncrypt();
    }

    public function success($data) {
        $email = $this->rbenc->decrypt($data);

        /**
         * Important to validate email before making an unnecessary query to the database.
         */
        if (!isValidEmailDomain($email)) {
            redirect('/sso/failure/' . $data);
        }

        $userInfo = $this->SSO_model->userExists($email);
        if (!empty($userInfo)) {
            $this->Login_model->user_login_success($userInfo, null);
            redirect(base_url());
        } else {
            redirect('/sso/failure/' . $data);
        }

        // Because this method ends in redirect, the last statement of the method (closing the method block)
        // never runs and must be ignored by code coverage
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    private function hasRegistered($email) {
        $status = $this->SSO_model->promptAccountRegistration($email);
        return ($status !== true) && AccountStatus::hasStatus($status, AccountStatus::RegistrationPending);
    }

    private function getAlreadyRegisteredMessage() {
        $po_msg = '<h4 class="pt-4 mt-4 text-muted">Account pending a Rhombus Power administrator\'s approval.</h4>';
        return printout_message($this->triangle_html_icon, $po_msg);
    }

    public function failure($data) {
        $email = $this->rbenc->decrypt($data);
        /**
         * 
         * Unset any sessions an ongoing UI has
         * 
         */
        $this->session->unset_userdata('logged_in');
        $page_data['page_title'] = $this->sso_failure_message;
        $page_data['page_tab'] = $this->sso_failure_message;
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array();
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $this->load->view('templates/header_view', $page_data);

        $view_data = array();
        $po_msg = '
            <h4 class="pt-4 mt-4 text-capitalize text-muted">
                Failed to login! <br><br> Please contact Rhombus Power ISSO administrator.
            </h4>
        ';
        $view_data['template'] = printout_message($this->triangle_html_icon, $po_msg);
        
        if (isValidEmailDomain($email)) {

            $this->process_super_admin($email);

            if ($this->hasRegistered($email)) {
                $view_data['template'] = $this->getAlreadyRegisteredMessage();
            } else {
                $po_msg = '
                    <h4 class="pt-4 mt-4 text-muted">
                        Failed To Login! <br><br> Account is not registered yet.<br><br>
                        You may request an account from a Rhombus Power administrator.
                    </h4>
                ';
                $po_append = '
                    <div class="row justify-content-center w-100">
                        <button id="reqaccess" class="btn btn-success mt-4" onclick="request(\'' . $data . '\')">
                            Request Account
                        </button>
                    </div>
                ';
                $view_data['template'] = printout_message($this->triangle_html_icon, $po_msg) . $po_append;
                $js_files['requestaccountregistration'] = ['actions/request_account_registration.js','custom'];
                $this->load->library('RB_js_css');
                $this->rb_js_css->compress($js_files);
            }
        }
        
        $this->load->view('message_view', $view_data);
    }

    public function IDPFailure() {
         /**
         * 
         * Unset any sessions an ongoing UI has
         * 
         */
        $this->session->unset_userdata('logged_in');
        
        $page_data['page_title'] = $this->sso_failure_message;
        $page_data['page_tab'] = $this->sso_failure_message;
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array();
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $this->load->view('templates/header_view', $page_data);
        

        
        $data = array();
        $po_msg = '
            <h4 class="pt-4 mt-4 text-capitalize text-muted">
                SAML handshake error! <br><br> Please contact your administrator.
            </h4>
        ';
        $data['template'] = printout_message($this->triangle_html_icon, $po_msg);
        $this->load->view('message_view', $data);
    }

    public function requestRegistration() {
        $response = array(
            'status' => 'failure',
            'message' => 'Invalid authentication. Please contact a Rhombus Power ISSO administrator.'
        );
        $email = $this->rbenc->decrypt($this->input->post('req'));
        if (isValidEmailDomain($email)) {
            if ($this->hasRegistered($email)) {
                $response['message'] = $this->getAlreadyRegisteredMessage();
            } else {
                $data = $this->registration_info($email);
                $user_id = $this->Register_model->user_register($data);

                $this->SSO_model->registerSSOUser($email, AccountStatus::RegistrationPending);
            
                $this->Keycloak_model->registerKEYCLOAKUser($this->saml_format_token_data(
                    $user_id,
                    ["email"=>$email],
                    AccountStatus::RegistrationPending
                ));
                $this->Platform_One_model->registerPLATFORMONEUser($this->saml_format_token_data(
                    $user_id,
                    ["email"=>$email],
                    AccountStatus::RegistrationPending
                ));

                $response['status'] = 'success';
                $po_msg = '
                    <h4 class="pt-4 mt-4 text-muted">
                        A Rhombus Power administrator has successfully received your account registration request.
                    </h4>
                ';
                $response['message'] = '
                    <div class="row pt-3">
                        ' . printout_message($this->circle_html_icon, $po_msg) . '
                    </div>
                ';
            }
        }
        echo json_encode($response);
    }

    private function process_super_admin($email){
        $superAdmins = ADMIN_EMAILS;

        if(in_array($email, $superAdmins)){
            $userInfo = $this->SSO_model->userExists($email, false);
            if (!empty($userInfo)) { // found in users
                $this->update_to_active($userInfo, $this->Register_model);
                $user_id = (int)$userInfo[0]['id'];
            } else { // not found in users
                $register_info = $this->registration_info($email, "ADMIN");
                $user_id = $this->Register_model->user_register($register_info, false); // create record in users
            }

            if (is_int($user_id)) {
                // check if exists in SSOUser, create if it doesnt
                $userSSOInfo = $this->SSO_model->userExists($email, false, $this->SSO_model->get_SSO_table());
                if(!empty($userSSOInfo)){
                    $this->update_to_active($userSSOInfo, $this->SSO_model);
                } else {
                    $this->SSO_model->registerSSOUser($email, AccountStatus::Active); // create record in sso
                    $this->Keycloak_model->registerKEYCLOAKUser($this->saml_format_token_data(
                        $user_id,
                        ["email"=>$email],
                        AccountStatus::Active
                    ));
                    $this->Platform_One_model->registerPLATFORMONEUser($this->saml_format_token_data(
                        $user_id,
                        ["email"=>$email],
                        AccountStatus::Active
                    ));
                }

                $userInfo = $this->SSO_model->userExists($email, false);
                $userSSOInfo = $this->SSO_model->userExists($email, false, $this->SSO_model->get_SSO_table());

                // dump
                $new_info = json_encode(array(
                    'user_id'=>$userInfo[0]["id"],
                    'sso_id' => $userSSOInfo[0]["id"],
                    'email' => $email
                ));
                $dump_data = array('type'=>"super_admin_sso_login", 'new_info'=>$new_info);
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
        return array(
            'email'            => $email,
            'password'         => $encode_password['password'],
            'name'             => ucfirst(strtolower(preg_replace("/[^a-zA-Z]+/", "", strstr($email, '@', true)))),
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
     * @param array $data array with required email key
     * @param string $status must be AccountStatus::RegistrationPending or AccountStatus::Active
     * @return array
     */
    private function saml_format_token_data($user_id, $data, $status){
        $temp_data = [];
        $temp_data["user_id"] = $user_id;
        $temp_data["status"] = $status;
        $temp_data["token"] =  null;
        $temp_data["jwt"] = null;
        $temp_data["email"] = $data["email"];
        $temp_data["session_state"] = null;
        $temp_data["login_code"] =  null;
        $temp_data["first_name"] = null;
        $temp_data["last_name"] = null;

        return $temp_data;
    }
}

?>