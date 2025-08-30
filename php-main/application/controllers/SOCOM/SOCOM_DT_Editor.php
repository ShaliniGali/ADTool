<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_DT_Editor extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
        $this->load->model('SOCOM_DT_Editor_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Database_Upload_model');
        $this->load->model('SOCOM_Database_Upload_Metadata_model');
        $this->load->model('Login_model');
    }

    // --------------------------------------------------------------------
    public function index($usr_dt_upload, $admin = '0') {
        $page_data['page_title'] = "SOCOM Dashboard";
        $page_data['page_tab'] = "SOCOM Dashboard";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
            'select2.css',
            'carbon-light-dark-theme.css',
            'SOCOM/dashboard.css',
            'dashboard_block.css',
            'handsontable.min.css',
            'SOCOM/socom_home.css'
        ];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        
        $this->SOCOM_DT_Editor_model->check_usr_dt_upload($usr_dt_upload, $admin === '1');
        
        $data = [];

        $is_admin = $this->rbac_users->is_admin();
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();

        $data['is_admin'] = $is_admin;
        $page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $col_headers = $this->SOCOM_DT_Editor_model->get_column_headings();
        $searchable_columns = $this->SOCOM_DT_Editor_model->get_searchable_columns();
        $data['row_headers'] = $row_headers ?? [];
        $data['col_headers'] = $col_headers;
        $data['searchable_columns'] = $searchable_columns;
        $data['licenseKey'] = RHOMBUS_HANDSONTABLE_LICENSE;
        $data['usr_dt_upload'] = $usr_dt_upload;
        $data['edit_start_time'] = $this->SOCOM_DT_Editor_model->get_edit_start_time();
        $data['admin_viewer'] = $admin === '1' ? '/1' : '';



        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/dashboard/upload/zbt_iss_editor/editor_view', $data);
        $this->load->view('templates/close_view');
    }

    public function fetch_data_editor($admin = '0') {
        $page = intval($this->input->post('page')) ?? 0;
        $usr_dt_upload = $this->input->post('usr_dt_upload') ?? false;

        $col_headers = $this->SOCOM_DT_Editor_model->get_column_headings();
        $data_result = $this->SOCOM_DT_Editor_model->get_dt_table_data($usr_dt_upload, $page, $admin === '1');
        $total_rows = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($usr_dt_upload, $admin === '1');
        $data = [];
        foreach ($data_result as $row) {
            $data[] = array_values($row);
        }
        $row_headers = [];
        for ($i = 1 + $page * 100; $i <= ($page + 1) * 100 && $i <= $total_rows; $i++) {
            $row_headers[] = "Row $i";
        }
        $response = [
            'data' => $data,
            'row_headers' => $row_headers,
            'col_headers' => $col_headers,
            'total_pages' => ceil($total_rows / 100),
            'edit_start_time' => $this->SOCOM_DT_Editor_model->get_edit_start_time()
        ];
        $this->output->set_output(json_encode($response));
    }

    public function search_data_editor($admin = '0') {
        $column = $this->input->get('column');
        $query = $this->input->get('query');
        $searchable_columns = $this->SOCOM_DT_Editor_model->get_searchable_columns();
        $usr_dt_upload = $this->input->get('usr_dt_upload') ?? false;
        if (!$column || !$query || !in_array($column, $searchable_columns, true)) {
            echo json_encode(['data' => [], 'row_headers' => [], 'col_headers' => []]);
            return;
        }
        $col_headers = $this->SOCOM_DT_Editor_model->get_column_headings();
        $data_result = $this->SOCOM_DT_Editor_model->search_dt_table_data($usr_dt_upload, $column, $query, $admin === '1');
        $data = [];
        foreach ($data_result as $row) {
            $data[] = array_values($row);
        }
        $row_headers = [];
        for ($i = 1; $i <= count($data); $i++) {
            $row_headers[] = "Row $i";
        }
        $response = [
            'data' => $data,
            'row_headers' => $row_headers,
            'col_headers' => $col_headers
        ];
        $this->output->set_output(json_encode($response));
    }

    public function save_data_edits() {
        $this->output->set_content_type(self::CONTENT_TYPE_JSON);
        $usr_dt_upload = $this->input->post('usr_dt_upload');
        $changes = json_decode($this->input->post('changes'), true);
        $edit_start_time = $this->input->post('editor_start_time');
        $result = $this->SOCOM_DT_Editor_model->save_all_user_changes($usr_dt_upload, $changes, UploadType::DT_UPLOAD_EXTRACT_UPLOAD, $edit_start_time);

        $this->output->set_output(json_encode($result));
    }
}
