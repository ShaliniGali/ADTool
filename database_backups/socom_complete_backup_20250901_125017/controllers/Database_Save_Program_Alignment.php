<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Database_Save_Program_Alignment extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Database_Upload_model');
        $this->load->model('SOCOM_Scheduler_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Cycle_Management_model');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        $this->load->library('SOCOM/Database_Upload_Services');
        
        $this->load->library('SOCOM/Program_Import');
    }

    public function save_upload()
    {
        $status = $this->user_can_access();
        
        $http_status = 500;

        $this->form_validation->set_rules('version', 'Version', 'required');
        $this->form_validation->set_rules('title', 'Title', 'required|max_length[100]');
        $this->form_validation->set_rules('description', 'Description', 'required|max_length[5000]');

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        
        if ($this->form_validation->run() !== false && $data_check['result']) {

            $post_data = $data_check['post_data'];
            $version = $post_data['version'] ?? '';
            $title = $post_data['title'] ?? '';
            $description = $post_data['description'] ?? '';

            try {
                $this->program_import->setParams([
                    'VERSION' => $version,
                    'TITLE' => $title,
                    'DESCRIPTION' => $description
                ], UploadType::PROGRAM_SCORE_UPLOAD);

                $this->program_import->setFilePostName('file');

                $status = $this->program_import->saveUpload();
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

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output($output);
    }


    public function activate_socom() {
        list($http_status, $response['status']) = $this->user_can_access(false);

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        { 
            $post_data = $data_check['post_data'];
            $file = encrypted_string($post_data['file'], 'decode');
            $status = $this->SOCOM_Database_Upload_model->set_file_active($file);

            if ($status) {
                $http_status = 200;
                $response['status'] = 'Success Setting the active SOCOM.';
            } else {
                $http_status = 406;
                $response['status'] = 'Error setting the active SOCOM.';
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    /**
     * The list database uploads ajax response
     * for users
     * 
     * Output is JSON
     */
    public function list_uploads() {

        $status = $this->user_can_access();
        $response = [];
        if ($status !== FALSE) {
            
            // Handle missing session data with dev bypass
            if (isset($this->session->userdata['logged_in']['id'])) {
                $user_id = (int)$this->session->userdata['logged_in']['id'];
            } else {
                $user_id = 1; // Default user ID for development
            }

            if (($file_list = $this->SOCOM_Database_Upload_model->get_file_upload_list($user_id, UploadType::PROGRAM_SCORE_UPLOAD)) !== false) {
                $http_status = 200;
                $response['data'] = $file_list;
                $response['status'] = true;
            } else {
                $http_status = 500;
                $response['status'] = 'Error Socom Document uploads not found';
            }

            $this->output
                    ->set_status_header($http_status)
                    ->set_content_type(self::CONTENT_TYPE_JSON)
                    ->set_output(json_encode($response));
        }
    }

    public function process_file(){
        $this->user_can_access(false);

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        { 
            $post_data = $data_check['post_data'];
            $file = encrypted_string($post_data['file'], 'decode');
            $status = $this->SOCOM_Database_Upload_model->update_file_status($file,FILE_STATUS_REQUESTED);
            
            if ($status) {
                $http_status = 200;
                $response['status'] = 'The file has been successfully queued and will be updated accordingly';
            } else {
                $http_status = 406;
                $response['status'] = 'Error processing the file';
            }
            
            $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode($response));
        }
    }

    public function get_processed() {
        $this->user_can_access(false);

        $data = $this->SOCOM_Database_Upload_model->get_processed_list(UploadType::PROGRAM_SCORE_UPLOAD);
        $response = ['data' => $data];

        $http_status = 200;
        if (!$data) {
            $response['status'] = 'SOCOM not uploaded or no active SOCOM.';
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function results_list_view() {
        // Alias for get_processed to match the expected endpoint
        $this->get_processed();
    }

    public function user_can_access($html = false) {
        // Check if dev bypass is enabled
        $dev_bypass_enabled = is_dev_bypass_enabled();
        
        if ($dev_bypass_enabled) {
            return [200, 'Access granted via dev bypass'];
        }
        
        $http_status = 403;
        $response['status'] = 'Unauthorized user, unable to use this feature of SOCOM';

        // Check if user has permission to use this feature
        if (!$this->SOCOM_Database_Upload_model->check_if_user_can_upload()) {
            
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