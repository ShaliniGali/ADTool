<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Dashboard_Cycle_Users extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Admin_User_model');
        $this->load->model('SOCOM_Cycle_User_model');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
    }

    // --------------------------------------------------------------------

    public function get_user_list()
    {
        $users = $this->SOCOM_Users_model->get_users();

        try {
            $users = $this->SOCOM_Cycle_User_model->get_users($users);
        } catch (ErrorException $e) {
            $users = [];
        }
        
        $http_status = 200;

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $users]));
    }

    // --------------------------------------------------------------------

    public function save_status()
    {
        $admin_users = false;

        $this->form_validation->set_rules('sid', 'Sid', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required');

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        
        if ($this->form_validation->run() !== FALSE && $data_check['result']) {
            $this->rbac_users->reset_user();

            $post_data = $data_check['post_data'];
            $sid = (int)$post_data['sid'] ?? 0;
            $email = $post_data['email'] ?? '';

            $user_id = $this->SOCOM_Users_model->get_id_from_email($email);
            if ($sid === 1) {
                $admin_users = $this->SOCOM_Cycle_User_model->delete_user($user_id);
            } else {
                $admin_users = $this->SOCOM_Cycle_User_model->activate_user($user_id);
            }

            $this->rbac_users->reinitialize_user();
        }

        if ($admin_users === false) {
            $http_status = 500;

            $output = json_encode([
                'errors' => [
                    'sid' => form_error('sid'),
                    'email' => form_error('email')
                ], 
                'status' => $admin_users
            ]);
        } else {
            $http_status = 200;
            $output = json_encode(['status' => $admin_users]);
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output($output);
    }

    // --------------------------------------------------------------------

    public function save_my_user()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $gid = (int)$post_data['gid'] ?? 0;
        }
        $this->rbac_users->reset_user();
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $users = $this->SOCOM_Cycle_User_model->set_user($user_id, $gid);

        if ($users === false) {
            $http_status = 500;
        } else {
            $http_status = 200;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['status' => $users]));
    }
}
