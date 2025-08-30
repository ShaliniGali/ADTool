<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class First_admin_controller extends CI_Controller {
	public function index() {
        $isEmpty = $this->Register_model->check_empty();
        if(empty($isEmpty)){
            $this->load->view('first_admin_view');
        }else{
            $this->directorymanager->deleteFile('application/first_admin_folder/FirstAdminFlag.txt');
            redirect('login');
        }
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
    public function create_accounts()
    {

        $isEmpty = $this->Register_model->check_empty();
        if(!empty($isEmpty)){
            $result['message'] = "error";
            echo json_encode(array("result" => $result['message']));
            return;
        }

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); 

        if (!$data_check["result"]) {
            return;
        }

        $post_data = $data_check["post_data"];
        /**
         * validating input post
         */
        $valid = $this->form_validation->run_rules(array(
            'username' => array('rules' => array('required', 'valid_email')),
            'password' => array('rules' => array('required')),
            'account_type' => array('rules' => array('required'))
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
            $this->encode_in_create_accounts($post_data);
        }
	}

    private function encode_in_create_accounts($post_data){
        $username = strtolower($post_data['username']);
        $password = $post_data['password'];
        $password_confirmation = $post_data['password_confirmation'];
        $account_type = $post_data['account_type'];
        $name = $post_data['name'];
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

        if (!$this->useraccounttype->isValidAccountType($account_type)) {
            $validation_response['message']['account_type_check'] = 'Invalid account type.';
        }

        if (!empty($validation_response['message'])) {
            echo json_encode($validation_response);
            return;
        }

        $encode_password = $this->password_encrypt_decrypt->encrypt($password);
        $data = array(
            'email'            => $username,
            'password'         => $encode_password['password'],
            'account_type'     => $account_type,
            'name'          => $name,
            'saltiness'        => $encode_password['salt'],
            'login_layers'     => (LoginLayers::LayerOff).(LoginLayers::LayerOff).(LoginLayers::LayerOff)
        );
        $this->Register_model->user_register($data,false);
        $result['message'] = "first_success";
        echo json_encode(array("result" => $result['message']));
    }
}
