<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Dashboard_Admin_AOAD_Users extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Admin_User_model');
        $this->load->model('SOCOM_Cycle_User_model');
        $this->load->model('SOCOM_Cap_User_model');
    }

    // --------------------------------------------------------------------

    public function index()
    {
        $page_data['page_title'] = "SOCOM Dashboard";
        $page_data['page_tab'] = "SOCOM Dashboard";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
            'SOCOM/dashboard.css',
            'carbon-light-dark-theme.css',
            'dashboard_block.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'SOCOM/socom_home.css'
        ];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $is_user = $this->rbac_users->is_user();
        $is_admin = $this->rbac_users->is_admin();
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $user = $this->SOCOM_Users_model->get_user($user_id);
        try {
            $admin_user = $this->SOCOM_Users_model->get_admin_user($user)[0] ?? [];
        } catch (ErrorException $e) {
            $admin_user = [];
        }
        try {
            $ao_ad_user = $this->SOCOM_Users_model->get_ao_ad_user()[0] ?? [];
        } catch (ErrorException $e) {
            $ao_ad_user = [];
        }
        try {
            $cycle_user = $this->SOCOM_Cycle_User_model->get_user()[0] ?? [];
        } catch (ErrorException $e) {
            $cycle_user = [];
        }
        try {
            $site_user = $this->SOCOM_Site_User_model->get_user()[0] ?? [];
        } catch (ErrorException $e) {
            $site_user = [];
        }
        try {
            $cap_user = $this->SOCOM_Cap_User_model->get_user()[0] ?? [];
        } catch (ErrorException $e) {
            $cap_user = [];
        }

        $all_cap_groups = $this->SOCOM_Cap_User_model->getGroups();
        $page['email'] = $user[$user_id];
        $page['pom_group'] = $site_user['GROUP'] ?? null;
        $page['admin_group'] = $admin_user['GROUP'] ?? null;
        $page['ao_ad_group'] = $ao_ad_user['GROUP'] ?? null;
        $page['cycle_weight_group'] = $cycle_user['GROUP'] ?? null;
        $page['cap_sponsor_group'] = $cap_user['GROUP'] ?? null;
        $page['all_cap_groups'] = $all_cap_groups;
        $page['is_super_admin'] = $this->SOCOM_Users_model->is_super_admin();
        $page['is_group_admin'] = $this->SOCOM_Users_model->is_admin_user($user_id);
        $page['is_user'] = $is_user;
        $page['is_admin'] = $is_admin;
        $page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $page['has_old_cap_sponsor_group'] = $this->SOCOM_Cap_User_model->user_has_old_group();

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/dashboard/account_management/tabed_admin', $page);
        $this->load->view('templates/close_view');
    }

    public function get_admin_user_list()
    {

        $users = $this->SOCOM_Users_model->get_users();

        try {
            $admin_users = $this->SOCOM_Users_model->get_admin_user($users);
        } catch (ErrorException $e) {
            $admin_users = [];
        }

        $http_status = 200;

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $admin_users]));
    }

    // --------------------------------------------------------------------

    public function get_ao_ad_user_list()
    {

        $users = $this->SOCOM_Users_model->get_users();

        try {
            $ao_ad_users = $this->SOCOM_Users_model->get_ao_ad_users($users);
        } catch (ErrorException $e) {
            $ao_ad_users = [];
        }

        $http_status = 200;

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $ao_ad_users]));
    }

    // --------------------------------------------------------------------

    public function save_ao_ad_status()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $sid = (int)$post_data['sid'] ?? 0;
            $email = $post_data['email'] ?? '';
        }

        $user_id = $this->SOCOM_Users_model->get_id_from_email($email);
        if ($sid === 1) {
            $admin_users = $this->SOCOM_Users_model->delete_ao_ad_user($user_id);
        } else {
            $admin_users = $this->SOCOM_Users_model->activate_ao_ad_user($user_id);
        }

        if ($admin_users === false) {
            $http_status = 500;
        } else {
            $http_status = 200;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['status' => $admin_users]));
    }

    // --------------------------------------------------------------------

    public function save_admin_status()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $sid = (int)$post_data['sid'] ?? 0;
            $email = $post_data['email'] ?? '';
        }

        $user_id = $this->SOCOM_Users_model->get_id_from_email($email);
        if ($sid === 1) {
            $admin_users = $this->SOCOM_Users_model->delete_admin_user($user_id);
        } else {
            $admin_users = $this->SOCOM_Users_model->activate_admin_user($user_id);
        }

        if ($admin_users === false) {
            $http_status = 500;
        } else {
            $http_status = 200;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['status' => $admin_users]));
    }

    // --------------------------------------------------------------------

    public function save_my_user_admin()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $gid = (int)$post_data['gid'] ?? 0;
        }

        $user_id = (int)$this->session->userdata("logged_in")["id"];
        
        $admin_users = $this->SOCOM_Users_model->set_admin_user($user_id, $gid);

        if ($admin_users === false) {
            $http_status = 500;
        } else {
            $http_status = 200;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['status' => $admin_users]));
    }

    // --------------------------------------------------------------------

    public function save_my_user_ao_ad()
    {

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $gid = (int)$post_data['gid'] ?? 0;
        }

        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $ao_ad_users = $this->SOCOM_Users_model->set_ao_ad_user($user_id, $gid);

        if ($ao_ad_users === false) {
            $http_status = 500;
        } else {
            $http_status = 200;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['status' => $ao_ad_users]));
    }
}
