<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Login_token_controller extends CI_Controller {
    private $username;

    public function __construct() {
        parent::__construct();
        $this->username = $this->session->userdata('tfa_pending');
        $this->load->library('GoogleAuthenticator');
    }

    /**
     * Created: Moheb, July 22nd, 2020
     * 
     * Records the activity identified by $type for the user specified by $id.
     * 
     * @param string $id
     * @param string $type
     * @param string $token
     * @param unsigned $attempts
     * @return void
     */
    private function dumpActivity($id, $type, $token, $attempts) {
        $login_token_json = json_encode(array(
            'login_token' => $token,
            'attempts' => $attempts
        ));
        $this->Login_model->dump_user($type, $login_token_json, $id);
    }
    
    /**
     * Created: Moheb, July 22nd, 2020
     * 
     * Generates a new login token for the user email stored in session then sends an email to the
     * user with the newly generated token so the user may use it to login.
     */
    public function generateLoginToken() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $post_data = $data_check["post_data"];

            $id = $this->Login_token_model->has_login_token_layer($this->username);

            $login_token = strval($this->googleauthenticator->createSecret(16));

            $this->Login_token_model->generate_login_token($id, $login_token);

            /**
             * Sending email
             */
            $message = "A login token has been request for your account to access the " . RHOMBUS_PROJECT_NAME . 
                " UI. <br><br> You may click the following link to login: <a href='" . base_url() . 
                "'>Rhombus Power</a><br><br> Please use the following login token to access the website.<br><br>" . 
                $login_token;
            $result_message = $this->Generic->send_email(array(
                'receiverEmail' => $this->username,
                'subject' => "Account login token.",
                'receiverName' => "",
                'template' => 'custom',
                'footer' => ['ipAddress' => ''],
                'content' => [
                    ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
                    ['type' => 'row', 'row' => [[
                        'type' => 'text',
                        'text' => 'If this is not you then please contact it@rhombuspower.com.'
                    ]]],
                    ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
                ]
            ), $id);

            echo json_encode(array('status' => 'success', 'message' => 'Login token issued.'));
        }
    }

    /**
     * Created: Moheb, July 22nd, 2020
     * 
     * Checks if the login token provided by the user is valid.
     */
    public function authenticateLoginToken() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $post_data = $data_check["post_data"];

            $user_token = $post_data['token'];
            $response = array('status' => 'failure', 'message' => 'Invalid token');

            if (strlen($user_token) != 16) {
                $response['message'] = 'Login token must be 16 characters long.';
                echo json_encode($response);
                return;
            }

            if (preg_match('/[^A-Za-z0-9]/', $user_token)) {
                $response['message'] = 'Login token must consist of letters and digits only.';
                echo json_encode($response);
                return;
            }

            $id = $this->Login_token_model->has_login_token_layer($this->username);

            $block_type = 'login_token_max_failed_attempts';
            $block_msg =  'Maximum failed attempts reached with login tokens.';
            $is_blocked_or_attempts = $this->Login_model->enforce_block_rules($id, $block_type, $block_msg);
            if ($is_blocked_or_attempts === true) {
                $response['status'] = 'max_attempts_reached';
                $response['message'] = 'Maximum failed login attempts reached.';
                echo json_encode($response);
                return;
            }

            if ($this->Login_token_model->get_login_token($id) == $user_token) {
                $response['status'] = 'success';
                $response['message'] = 'Login token authenticated successfully.';
                $this->Login_model->update_login_attempts_by_id($id, true);
                $this->dumpActivity($id, 'valid_login_token', $user_token, 0);
                $this->Login_model->user_login_success($this->username, 'Only_email');
                /**
                 * 
                 * Sumit: 27 July 2020
                 * Delete login token in case user wants to reuse the same token
                 * 
                 */
                $this->Login_token_model->delete_login_token($id);
            } else {
                $this->Login_model->update_login_attempts_by_id($id);
                $this->dumpActivity($id, 'Invalid_login_token', $user_token, $is_blocked_or_attempts + 1);
            }
            echo json_encode($response);
        }
    }
}