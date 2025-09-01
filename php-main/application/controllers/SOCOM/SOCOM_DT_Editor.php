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
        $this->load->model('SOCOM_Users_model');
        $this->load->model('Login_model');
    }

    // --------------------------------------------------------------------
    public function index($usr_dt_upload, $view = '0') {
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
        
        $db_check = $this->SOCOM_DT_Editor_model->check_usr_dt_upload($usr_dt_upload, $view === '1');
        if (!isset($db_check['ID'], $db_check['count'])) {
            show_error('No upload found for this request');
        }

        $data_size_check = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($usr_dt_upload, $view === '1');
        if (isset($db_check['ID'], $db_check['count']) && $db_check['count'] === 0 || $data_size_check === 0) {
            show_error('No data found for this request');
        }
        
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
        $data['admin_viewer'] = $view === '1' ? '/1' : '';

        
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_OPEN, $this->SOCOM_DT_Editor_model->decode_usr_dt_upload($usr_dt_upload), $user_id, false);

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/dashboard/upload/zbt_iss_editor/editor_view', $data);
        $this->load->view('templates/close_view');
    }

    public function fetch_data_editor($admin = '0') {
        $page = intval($this->input->post('page')) ?? 0;
        $usr_dt_upload = $this->input->post('usr_dt_upload') ?? false;

        $col_headers = $this->SOCOM_DT_Editor_model->get_column_headings();
        $data = $this->SOCOM_DT_Editor_model->get_dt_table_data($usr_dt_upload, $page, $admin === '1');
        $total_rows = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($usr_dt_upload, $admin === '1');
        
        $row_headers = [];
        for ($i = 1 + $page * 100; $i <= ($page + 1) * 100 && $i <= $total_rows; $i++) {
            $row_headers[] = "Row $i";
            $c = $i - 1 - ($page * 100);
            $data[$c]['USR_DT_UPLOADS_ID'] = encrypted_string($data[$c]['USR_DT_UPLOADS_ID'], 'encode');
        }
        $response = [
            'data' => $data,
            'row_headers' => $row_headers,
            'col_headers' => $col_headers,
            'total_pages' => ceil($total_rows / 100),
            'edit_start_time' => $this->SOCOM_DT_Editor_model->get_edit_start_time(),
            'is_admin' => $this->rbac_users->is_admin()
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
        $user_id = (int)$this->session->userdata['logged_in']['id'];

        $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_SEARCH, $this->SOCOM_DT_Editor_model->decode_usr_dt_upload($usr_dt_upload), $user_id);

        $col_headers = $this->SOCOM_DT_Editor_model->get_column_headings();
        $data = $this->SOCOM_DT_Editor_model->search_dt_table_data($usr_dt_upload, $column, $query, $admin === '1');
        foreach ($data as &$row) {
            $row['USR_DT_UPLOADS_ID'] = encrypted_string($row['USR_DT_UPLOADS_ID'], 'encode');
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
        $overwrite = filter_var($this->input->post('overwrite'), FILTER_VALIDATE_BOOL) ?? false;
        
        $result = $this->SOCOM_DT_Editor_model->save_all_user_changes($usr_dt_upload, $changes, UploadType::DT_UPLOAD_EXTRACT_UPLOAD, $edit_start_time, $overwrite);

        $this->output->set_output(json_encode($result));
    }

    public function approve_data_edits() {
        $this->output->set_content_type(self::CONTENT_TYPE_JSON);
 
        $usr_dt_upload = encrypted_string($this->input->post('usr_dt_upload'), 'decode');
        $rows_to_approve = $this->input->post('rows_to_approve');
    
        $rows_grouped_by_file = [];
        foreach( $rows_to_approve as $row) {
            $file_id = encrypted_string($row['file_id'], 'decode');
            if (isset($rows_grouped_by_file[$file_id])) {
                $rows_grouped_by_file[$file_id][] = (int)$row['row_id'];
            } else {
                $rows_grouped_by_file[$file_id] = [(int)$row['row_id']];
            }
        }

        $result = $this->SOCOM_DT_Editor_model->save_approve($usr_dt_upload, $rows_grouped_by_file);

        $this->output->set_output(json_encode($result));
    }

    public function get_editor_historical_data($usr_dt_upload) {
        
        $usr_dt_upload_decoded = $this->SOCOM_DT_Editor_model->decode_usr_dt_upload($usr_dt_upload);
        $status = 500;

        if ($usr_dt_upload_decoded) {
            $data = $this->SOCOM_DT_Editor_model->get_editor_historical_data($usr_dt_upload_decoded);
            if ($data && !empty($data)) {
                $formatted_graph_data = $this->format_editor_historical_data($data);
                $file_title = $data[0]['TITLE'] ?? '';

                $status = 200;
                $message = 'success';
            }
            else {
                $status = 400;
                $message = 'Failed to load historical data';
            }

            $this->output->set_status_header($status)
                ->set_content_type('application/json')
                ->set_output(json_encode(
                    [
                        'status' => $message,
                        'data' => [
                            'graph_data' => $formatted_graph_data ?? [],
                            'title' => $file_title ?? ''
                        ]
                    ]))
                ->_display();
            exit();
        }
        else {
            $this->output->set_status_header($status)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error']))
                ->_display();
            exit();
        }
    }


    private function format_editor_historical_data($data) {
        $data_group_by_date = [];
        foreach ($data as $entry) {
            $git_created_time = strtotime($entry['GIT_CREATED_DATETIME']) * 1000;
            if (isset($data_group_by_date[$git_created_time])) {
                if (!in_array($entry['GIT_TYPE'],  $data_group_by_date[$git_created_time]['label'])) {
                    $data_group_by_date[$git_created_time]['label'][] = $entry['GIT_TYPE'];
                }

                $description = $this->get_git_description($entry);

                $data_group_by_date[$git_created_time]['description'] = array_merge(
                    $data_group_by_date[$git_created_time]['description'] ?? [],
                    $description
                );
            } else {
   
                $data_group_by_date[$git_created_time] = [
                    'x' => $git_created_time,
                    'name' => $git_created_time,
                    'label' => [$entry['GIT_TYPE']],
                    'description' => $this->get_git_description($entry)
                ];


            }
        }
        return array_values($data_group_by_date);
    }

    private function get_git_description($entry) {
        $users = $this->SOCOM_Users_model->get_users();
        if ($entry['GIT_TYPE'] === GitDataType::USER_DATA_SAVE_START->name) {
            if ($entry['EDIT_DETAILS'] && !empty($entry['EDIT_DETAILS'])) {
                $edit_details = json_decode($entry['EDIT_DETAILS'], true);
                $description = [];
                foreach($edit_details as $value) {
                    $description[] = "<strong>{$value['FIELD_CHANGED']}</strong> changed from 
                        <strong>{$value['OLD_VALUE']}</strong> to 
                        <strong>{$value['NEW_VALUE']}</br></strong> by 
                        <strong>{$users[$entry['GIT_USER_ID']]}</strong> for the fiscal year 
                        <strong>{$value['FISCAL_YEAR']}</strong>";
                }
            }
        }
        else {
            $description = ["{$entry['GIT_TYPE']} by <strong>{$users[$entry['GIT_USER_ID']]}</strong>"];
        }
        return $description;
    }
}
