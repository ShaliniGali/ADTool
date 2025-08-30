<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Database_Save_In_POM_Cycle_Data_Upload extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Database_Upload_Metadata_model');
        $this->load->model('SOCOM_Scheduler_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Cycle_Management_model');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        $this->load->library('SOCOM/Database_Upload_Services');
        
        $this->load->library('SOCOM/In_POM_Cycle_Data_Upload_Import');
    }

    public function save_upload()
    {
        $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);

        if ($is_pom_admin === false) {
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
            $this->form_validation->set_rules('table-listing', 'Table Listing', 'required|in_list[EXT,ZBT,ISS,POM]');

            $data_check = $this->DB_ind_model->validate_post($this->input->post());

            if ($this->form_validation->run() !== false && $data_check['result']) {

                $post_data = $data_check['post_data'];
                $version = $post_data['version'] ?? '';
                $title = $post_data['title'] ?? '';
                $description = $post_data['description'] ?? '';
                $year = $post_data['year'] ?? '';
                $table_listing = $post_data['table-listing'] ?? '';


                try {
                    $this->in_pom_cycle_data_upload_import->setParams([
                        'VERSION' => $version,
                        'TITLE' => $title,
                        'DESCRIPTION' => $description,
                        'POM_YEAR' => $year,
                        'TABLE_LISTING' => $table_listing
                    ], UploadType::DT_UPLOAD_BASE_UPLOAD);

                    $this->in_pom_cycle_data_upload_import->setFilePostName('file');

                    $status = $this->in_pom_cycle_data_upload_import->saveUpload();

                    $this->in_pom_cycle_data_upload_import->saveToMetaDatabase();

                    $http_status = 200;
                } catch (ErrorException $e) {
                    $generalError = $e->getMessage();
                    $http_status = 500;
                }
            }

            if ($status === false || $http_status === 500) {
                $errors = [
                        form_error('version'),
                        form_error('description')
                ];

                if (isset($generalError)) {
                    array_unshift($errors, $generalError);
                }
                if ($status === false) {
                    array_unshift($errors, 'Unknown Upload Error');
                }

                $output = json_encode([
                    'messages' => array_unique($errors),
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
        $response = [];

        if ($is_pom_admin === false) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        }
        else {
            $status = $this->user_can_access();
            if ($status !== FALSE) {
                $user_id = (int)$this->session->userdata['logged_in']['id'];

                if (($file_list = $this->SOCOM_Database_Upload_Metadata_model->get_file_upload_list($user_id, UploadType::DT_UPLOAD_BASE_UPLOAD)) !== false) {
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
        $response = [];

        if ($is_pom_admin === false) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        }
        else {
            $this->user_can_access(false);

            $data = $this->SOCOM_Database_Upload_Metadata_model->get_processed_list_pom_status(UploadType::DT_UPLOAD_BASE_UPLOAD);
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
}