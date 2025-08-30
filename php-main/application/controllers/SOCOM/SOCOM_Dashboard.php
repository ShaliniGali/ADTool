<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Dashboard extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Cycle_User_model');
    }

    // --------------------------------------------------------------------

    public function index()
    {
        $page_data['page_title'] = "SOCOM Dashboard";
        $page_data['page_tab'] = "SOCOM Dashboard";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
            'carbon-light-dark-theme.css',
            'SOCOM/dashboard.css',
            'dashboard_block.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'SOCOM/socom_home.css'
        ];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $data = [];

        $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);
        $is_weight_criteria_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(3);
        $is_admin = $this->rbac_users->is_admin();
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();

        $data['is_cycle_admin_user'] = $is_cycle_admin_user;
        $data['is_weight_criteria_admin_user'] = $is_weight_criteria_admin_user;
        $data['is_admin'] = $is_admin;
        $page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/dashboard/home', $data);
        $this->load->view('templates/close_view');
    }
}
