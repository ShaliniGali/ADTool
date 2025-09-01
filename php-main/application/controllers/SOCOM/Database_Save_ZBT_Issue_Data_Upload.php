<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Database_Save_ZBT_Issue_Data_Upload extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Database_Upload_Metadata_model');
        $this->load->model('SOCOM_Scheduler_model');
        $this->load->model('SOCOM_DT_Editor_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Cycle_Management_model');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        $this->load->library('SOCOM/Database_Upload_Services');
        
        $this->load->library('SOCOM/ZBT_Issue_Data_Upload_Import');

        $this->load->model('SOCOM_Submit_Approve_model');

    }

    public function save_upload()
    {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();

        if (!$is_pom_admin && !$is_guest && !$is_restricted) {
            $http_status = 403;
            $output = json_encode([
                'messages' => ['No Permissions'],
                'status' => false
            ]);
        }
        else {
            $status = $this->user_can_access();
        
            $http_status = 500;

            $this->form_validation->set_rules('version', 'Version', 'required');
            $this->form_validation->set_rules('title', 'Title', 'required|max_length[100]');
            $this->form_validation->set_rules('description', 'Description', 'required|max_length[5000]');
            $this->form_validation->set_rules('year', 'Year', 'required|max_length[4]|integer');
            $this->form_validation->set_rules('table-listing', 'Table Listing', 'required|in_list[ZBT_EXTRACT,ISS_EXTRACT]');

            $data_check = $this->DB_ind_model->validate_post($this->input->post());

            if ($this->form_validation->run() !== false && $data_check['result']) {

                $post_data = $data_check['post_data'];
                $version = $post_data['version'] ?? '';
                $title = $post_data['title'] ?? '';
                $description = $post_data['description'] ?? '';
                $year = $post_data['year'] ?? '';
                $table_listing = $post_data['table-listing'] ?? '';


                try {
                    $this->zbt_issue_data_upload_import->setParams([
                        'VERSION' => $version,
                        'TITLE' => $title,
                        'DESCRIPTION' => $description,
                        'POM_YEAR' => $year,
                        'TABLE_LISTING' => $table_listing
                    ], UploadType::DT_UPLOAD_EXTRACT_UPLOAD);

                    $this->zbt_issue_data_upload_import->setFilePostName('file');

                    $status = $this->zbt_issue_data_upload_import->saveUpload();

                    $this->zbt_issue_data_upload_import->saveToMetaDatabase();

                    $http_status = 200;
                } catch (ErrorException $e) {
                    $generalError = $e->getMessage();
                    $http_status = 500;
                }
            }

            if ($status === false || $http_status === 500) {
                $errors = [
                        form_error('version'),
                        form_error('description'),
                        form_error('year'),
                        form_error('table-listing')
                ];

                if (isset($generalError)) {
                    array_unshift($errors, $generalError);
                }
                if ($status === false) {
                    array_unshift($errors, 'Unknown Upload Error');
                }

                $output = json_encode([
                    'messages' => array_values(array_unique($errors)),
                    'status' => false
                ]);
            } else {
                $http_status = 200;
                $output = json_encode([
                    'status' => true,
                    'messages' => ['File upload successful, please review upload history.']
                    ]
                );
            }
        }

        $this->output
        ->set_status_header($http_status)
        ->set_content_type(self::CONTENT_TYPE_JSON)
        ->set_output($output);
    }

    public function list_uploads() {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();

        $response = [];

        if (!$is_pom_admin && !$is_guest && !$is_restricted) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        }
        else {
            $status = $this->user_can_access();
            if ($status !== FALSE) {
                $user_id = (int)$this->session->userdata['logged_in']['id'];

                if (($file_list = $this->SOCOM_Database_Upload_Metadata_model->get_file_upload_list($user_id, UploadType::DT_UPLOAD_EXTRACT_UPLOAD)) !== false) {
                    $http_status = 200;
                    $response['data'] = $file_list;
                    $response['status'] = true;
                } else {
                    $http_status = 500;
                    $response['status'] = 'Error Socom Document uploads not found';
                }
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function get_processed() {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();

        $response = [];

        if (!$is_pom_admin && !$is_guest && !$is_restricted) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        }
        else {
            $this->user_can_access(false);

            $data = $this->SOCOM_Database_Upload_Metadata_model->get_processed_list_pom_status(UploadType::DT_UPLOAD_EXTRACT_UPLOAD);
            $response = ['data' => $data];

            $http_status = 200;
            if (!$data) {
                $response['status'] = 'SOCOM not uploaded or no active SOCOM.';
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function get_processed_admin() {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
        $response = [];

        if (!$is_pom_admin) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        }
        else {
            $this->user_can_access(false);

            $data = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_admin(UploadType::DT_UPLOAD_EXTRACT_UPLOAD);
                                                        
            foreach ($data as &$row) {
                $id = encrypted_string($row['ID'], 'decode');
                $row['HAS_SUBMITTED_ROWS'] = $this->SOCOM_Database_Upload_Metadata_model
                    ->has_submitted_status_by_metadata_id((int)$id);
                $row['TOTAL_ROWS_EDIT'] = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($id, false);
                $row['TOTAL_ROWS_VIEW'] = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($id, true);
            }

            $response = ['data' => $data];

            $http_status = 200;
            if (!$data) {
                $response['status'] = 'SOCOM not uploaded or no active SOCOM.';
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function user_can_access($html = false) {
        $http_status = 403;
        $response['status'] = 'Unauthorized user, unable to use this feature of SOCOM';

        // Check if user has permission to use this feature
        if (!$this->SOCOM_Database_Upload_Metadata_model->check_if_user_can_upload()) {
            
            if ($html === false) {
                $this->output
                    ->set_status_header($http_status)
                    ->set_content_type(self::CONTENT_TYPE_JSON)
                    ->set_output(json_encode($response))
                    ->_display();
                
                exit();
            } else {
                show_error($response['status'], $http_status);
            }
        }

        return [200, $response['status']];
    }

    public function parse_upload() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];

            $is_guest = $this->rbac_users->is_guest();
            $is_restricted = $this->rbac_users->is_restricted();
            $response = [];

            if (!$is_guest && !$is_restricted) {
                $http_status = 403;
                $response['status'] = 'No Permissions';
                $response['success'] = false;
            }
            else {
                $row_id = encrypted_string($post_data['row_id'], "decode");

                $headers = [
                    'accept: application/json',
                    'Content-Type: application/json'
                ];

                $api_endpoint = RHOMBUS_PYTHON_URL.'/socom/dirty-table/'.$row_id;

                $res = php_api_call(
                    'POST',
                    $headers,
                    null,
                    $api_endpoint
                );
                $result = json_decode($res, true);

                if (isset($result['detail'])) {
                    $http_status = 500;
                    $response['status'] = 'Error processing Socom Document upload parsing request';
                }
                else {

                    $http_status = 200;
                    $response['status'] = 'SOCOM Document parse success';
                }
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function upsert_upload() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];

            $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
            $response = [];
 
            if (!$is_pom_admin) {
                $http_status = 403;
                $response['status'] = 'No Permissions';
                $response['success'] = false;
            }
            else {
                $position = $post_data['position'];

                $headers = [
                    'accept: application/json',
                    'Content-Type: application/json'
                ];

                $api_params = [];
                $api_endpoint = RHOMBUS_PYTHON_URL.'/socom/dt_table/upsert?position='. $position;

                $res = php_api_call(
                    'POST',
                    $headers,
                    json_encode($api_params),
                    $api_endpoint
                );
                $result = json_decode($res, true);

                if (isset($result['detail'])) {
                    $http_status = 500;
                    $response['status'] = $result['detail'];
                }
                else {

                    $http_status = 200;
                    $response['status'] = 'SOCOM Document upsert success';
                }
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function save_submit($html = false) {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();

        $http_status = 403;
        $response = ['status' => 'Unauthorized user, unable to use this feature of SOCOM'];

        if (!$is_guest && !$is_restricted) {
            $http_status = 403;
            $response = ['status' => 'No Permissions'];
            if ($html === false) {
                $this->output
                    ->set_status_header($http_status)
                    ->set_content_type(self::CONTENT_TYPE_JSON)
                    ->set_output(json_encode($response))
                    ->_display();
                exit();
            } else {
                show_error($response['status'], $http_status);
            }
        }

        $map_id = encrypted_string($this->input->post('map_id'), 'decode');
        $description = $this->input->post('description')  ?: '';

        try {
            if (!$this->SOCOM_Submit_Approve_model->save_submit($map_id, $description)) {
                if ($html === false) {
                    $this->output
                        ->set_status_header($http_status)
                        ->set_content_type(self::CONTENT_TYPE_JSON)
                        ->set_output(json_encode($response))
                        ->_display();
                    exit();
                } else {
                    show_error($response['status'], $http_status);
                }
            }

            $user_id = (int)$this->session->userdata['logged_in']['id'];

            $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_FINAL_SUBMISSION, $map_id, $user_id);


            $http_status = 200;
            $response['status'] = 'Submission saved successfully.';
        } catch (ErrorException $e) {

            $http_status = 403;
            $response['status'] = $e->getMessage();
        }


        if ($html === false) {
            $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode($response))
                ->_display();
            exit();
        } else {
            return [$http_status, $response['status']];
        }
    }

    public function save_approve($html = false) {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);

        $http_status = 403;
        $response = ['status' => 'Unauthorized user, unable to use this feature of SOCOM'];
        
        if (!$is_pom_admin) {
            $http_status = 403;
            $response = ['status' => 'No Permissions'];
            if ($html === false) {
                $this->output
                    ->set_status_header($http_status)
                    ->set_content_type(self::CONTENT_TYPE_JSON)
                    ->set_output(json_encode($response))
                    ->_display();
                exit();
            } else {
                show_error($response['status'], $http_status);
            }
        }

        $map_id = encrypted_string($this->input->post('map_id'), 'decode');
        $description = $this->input->post('description') ?: '';

        if (!$this->SOCOM_Submit_Approve_model->save_approve($map_id, $description)) {
            if ($html === false) {
                $this->output
                    ->set_status_header($http_status)
                    ->set_content_type(self::CONTENT_TYPE_JSON)
                    ->set_output(json_encode($response))
                    ->_display();
                exit();
            } else {
                show_error($response['status'], $http_status);
            }
        }

        $user_id = (int)$this->session->userdata['logged_in']['id'];

        $this->SOCOM_Git_Data_model->git_track_data(GitDataType::ADMIN_APPROVAL, $map_id, $user_id);


        $http_status = 200;
        $response['status'] = 'Submission saved successfully.';

        if ($html === false) {
            $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode($response))
                ->_display();
            exit();
        } else {
            return [$http_status, $response['status']];
        }
    } 
    public function get_metadata_view_status_admin($encoded_id) {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
        $response = [];

        if (!$is_pom_admin) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        } else {
            $this->user_can_access(false);
            $id = encrypted_string($encoded_id, 'decode');
            $data = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_view_status((int)$id);

            if ($data) {
                foreach ($data as &$row) {
                    if(!empty($row['USER_ID'])){
                        $user = $this->SOCOM_Users_model->get_user($row['USER_ID']);
                        $row['EMAIL'] = $user[$row['USER_ID']] ?? null;
                    }
                    if($row['SUBMISSION_STATUS']){
                        $row['SUBMISSION_STATUS'] = "True";
                    }
                    else{
                        $row['SUBMISSION_STATUS'] = "False";
                    }
                }
                unset($row);
                $response = $data;
                $http_status = 200;
            } else {
                $http_status = 404;
                $response['status'] = 'No data found';
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

}