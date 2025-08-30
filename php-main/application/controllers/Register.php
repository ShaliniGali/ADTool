<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Register extends CI_Controller
{   
    private $maxResetPasswordHoursWindow = 72; // hours
    /**
     * @author Moheb, September 2nd, 2020
     * 
     * Validates a given email's domain name. Echos 'valid' if valid; otherwise, echos 'invalid'.
     * An email's domain name is valid if it is in the VALID_EMAIL_DOMAINS array (@see constants.php).
     * Otherwise, the email is invalid.
     * 
     * @param void
     * @return void
     */
    public function validateEmailDomain() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check["result"]) {
            echo isValidEmailDomain($data_check["post_data"]['email']) ? 'valid' : 'invalid';
        }
    }


	/**
	 * Created: Lea mar 8 2021
	 * redirects reset password view if link is valid
	 */
	public function activate()
	{
        $email = hex2bin($this->input->get('v'));
        $salitness = $this->input->get('s');
        $userdata = $this->Login_model->user_info_by_email($email);
        $userdata = !empty($userdata) ? $userdata[0] : false;

        if(($userdata !== false) && ($email == $userdata['email']) 
        && ($salitness == hash('sha256', $userdata['saltiness'])) 
        && AccountStatus::hasStatus($userdata['status'], AccountStatus::ResetPassword) 
        && ((intval($userdata['timestamp']) + ($this->maxResetPasswordHoursWindow * 60 * 60)) > time())) {
            $data['email'] = $userdata['email'];
            $this->load->view('reset_password_view', $data);
            return;
        }

        $page_data['page_title'] = "Registration";
        $page_data['page_tab'] = "Registration";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array();
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $this->load->view('templates/header_view', $page_data);
        $po_icon = '
            <i class="fas fa-check-circle m-2 fa-4x d-block mb-4 text-danger" style=" vertical-align: middle;"></i>
        ';
        $po_msg = '
            <div class="text-muted">This link is not valid. If you need help please contact it@rhombuspower.com</div>
        ';
        $data['template'] = printout_message($po_icon, $po_msg);
		$this->load->view('message_view', $data);
    }

    /**
     * Created: Sumit, 18 October 2019
     * Validates the user input and registers the user into datatabase
     * 
     * @param string username: Email of the user
     * @param string password: password of the user
     * @param string account_type: admin|user|moderator 
     * @param string message: message by the user
     * 
     * @return string
     */
    public function create_account()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check["result"]) {
            $post_data = $data_check["post_data"];
            /**
             * validating input post
             */
            $valid = $this->form_validation->run_rules(array(
                'username' => array('rules' => array('required', 'valid_email')),
                'password' => array('rules' => array('required')),
                'account_type' => array('rules' => array('required')),
                'message' => array('rules' => array('required'))
            ));
            
            if ($valid !== 'success') {

                $result['message'] = "error";
                /**
                 *  dumping unauthorized registration
                 */
                $dump_data = json_encode(array('user' => $post_data['username'], 'ip' => fetch_user_ip()));
                $this->Login_model->dump_user('Unauthorized_registration', $dump_data);
                echo json_encode(array("result" => $result['message']));
            } else {
                $username = strtolower($post_data['username']);
                $password = $post_data['password'];
                $pwd_confirmation = $post_data['password_confirmation'];
                $name = $post_data['name'];
                $acc_type = $post_data['account_type'];
                $message = $post_data['message'];

                $response = $this->_validate_create_account($username, $password, $pwd_confirmation, $name, $acc_type);

                if($response){
                    $result = $this->_create_user($username, $password, $name, $acc_type, $message);

                    echo json_encode(array("result" => $result['message']));
                }
            }
        }
    }

    public function _create_user($username, $password, $name, $account_type, $message) {
        $result = $this->Login_model->user_check($username, $password);

        if ($result['message'] == "not_registered") {
            /**
             * Validation successful; create user
             */

            $encode_password = $this->password_encrypt_decrypt->encrypt($password);
            $data = array(
                'email'            => $username,
                'password'         => $encode_password['password'],
                'name'             => $name,
                'account_type'     => $account_type,
                'message'          => $message,
                'saltiness'        => $encode_password['salt'],
                'login_layers'     => (LoginLayers::LayerOff).(LoginLayers::LayerOff).(LoginLayers::LayerOff)
            );
            $this->Register_model->user_register($data);
            $result['message'] = "registeration_pending";
        } else if ($result['message'] == "account_rejected") {
            $result['message'] = "account_rejected";
        } else {
            $result['message'] = "login";
        }

        return $result;
    }

    public function _validate_create_account($username, $password, $password_confirmation, $name, $account_type) {
        $validation_response = array('result' => 'validation_failure', 'message' => array());
        
        if (!isValidEmailDomain($username)) {
            $validation_response['message']['email_check'] = 'Unauthorized email domain.';
        } 

        if (!$this->password_encrypt_decrypt->isStrongPassword($password)) {
            $validation_response['message']['password_strength'] = 'Weak password.';
        }

        if (!$this->password_encrypt_decrypt->isValidPasswordConfirmation($password, $password_confirmation)) {
            $validation_response['message']['password_confirmation_check'] = 'Password does not match.';
        }

        if (!preg_match ("/^[a-zA-Z\s]+$/", $name)) {
            $validation_response['message']['name_check'] = 'A name may not contain special characters or digits.';
        }

        if (!$this->useraccounttype->isValidAccountType($account_type)) {
            $validation_response['message']['account_type_check'] = 'Invalid account type.';
        }

        if (!empty($validation_response['message'])) {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($validation_response))
                ->_display();
            return false;
        }
        return true;
    }

    public function reject_register()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check["result"]) {
            $post_data = $data_check["post_data"];
            $decrypt_data = encrypted_string($post_data['SiteURL'], "decode");
            $result = $this->Register_model->reject_register($decrypt_data['id'], $decrypt_data['email']);
            echo json_encode(array("result" => $result['message']));
        }
    }
}