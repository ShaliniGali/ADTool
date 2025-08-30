<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Database_Upload extends CI_Controller {

    protected const CONTENT_TYPE_JSON = 'application/json';


    public function __construct()
    {
        parent::__construct();
        if(auth_coa_role_restricted()!= null) {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }
        $this->load->model('SOCOM_Database_Upload_model');
        $this->load->model('SOCOM_Scheduler_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Site_User_model');

        $this->load->library('SOCOM/Database_Upload_Services');
    }

    /**
     * @
     */
    public function index() {
        $this->user_can_access(true);

        $data['user_id'] =  (int)$this->session->userdata('logged_in')['id'];
               
        $page_data['page_title'] = "SOCOM Upload";
        $page_data['page_tab'] = "Socom";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array(
            'SOCOM/dashboard.css',
            'SOCOM/socom_home.css',
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
        );
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $active_cycle = $this->SOCOM_Cycle_Management_model->get_active_cycle();
        $data = [
            'cycle_name' => $active_cycle['CYCLE_NAME']
        ];

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('templates/essential_javascripts');
        $this->load->view('SOCOM/dashboard/upload/tabed_admin', $data);
        $this->load->view('templates/close_view');
    }

    public function process_file(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

            if ($data_check['result']) {
                $post_data = $data_check['post_data'];
                $tab_name = $post_data['name'];

                $tab_list = array('InPOMCycle', 'OutOfPOMCycle', 'ZBTIssue');

                $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
                $is_guest = $this->rbac_users->is_guest();

                if (in_array($tab_name, $tab_list) && ($is_pom_admin === false && $is_guest === false)) {
                    $http_status = 403;
                    $response['status'] = 'No Permissions';
                    $response['success'] = false;
                }
                else {
                    $this->user_can_access(false);

                    $file = $post_data['file'] = encrypted_string($post_data['file'], 'decode');
                    $status = $this->SOCOM_Database_Upload_model->update_file_status($file, FILE_STATUS_REQUESTED);
                        
                    if ($status) {
                        $http_status = 200;
                        $response['status'] = 'The file has been successfully queued and will be updated accordingly';
                    } else {
                        $http_status = 406;
                        $response['status'] = 'Error processing the file';
                    }
                }
            }

            $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
        
    }

    /**
     * Will let a user cancel their database upload
     * 
     * For the uploading user only
     * 
     * Output is JSON
     */
    public function cancel_file() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $tab_name = $post_data['name'];

            $tab_list = array('InPOMCycle', 'OutOfPOMCycle', 'ZBTIssue');

            $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);

            if (in_array($tab_name, $tab_list) && ($is_pom_admin === false)) {
                $http_status = 403;
                $response['status'] = 'No Permissions';
                $response['success'] = false;
            }
            else {
                $file = encrypted_string($post_data['file'], 'decode');
                $status = $this->SOCOM_Database_Upload_model->update_file_status($file,FILE_STATUS_CANCELLED);
                
                if ($status) {
                    $http_status = 200;
                    $response['status'] = 'SOCOM Document upload cancel success';
                } else {
                    $http_status = 406;
                    $response['status'] = 'Error processing Socom Document upload cancel request';
                }
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
    
    public function delete_file(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $tab_name = $post_data['name'];

            $tab_list = array('InPOMCycle', 'OutOfPOMCycle', 'ZBTIssue');

            $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);

            if (in_array($tab_name, $tab_list) && ($is_pom_admin === false)) {
                $http_status = 403;
                $response['status'] = 'No Permissions';
                $response['success'] = false;
            }
            else {
                $this->user_can_access(false);
    
                $file = encrypted_string($post_data['file'], 'decode');
                $status = $this->SOCOM_Database_Upload_model->update_file_status($file, FILE_STATUS_DELETED);
                    
                if ($status) {
                    $http_status = 200;
                    $response['status'] = 'The Uploaded SOCOM File has been successfully deleted';
                } else {
                    $http_status = 406;
                    $response['status'] = 'Error deleting the file';
                }
    
            }
        }

        $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode($response));
    }

    /**
     * Will let a user activate their database upload
     * Output is JSON
     */
    public function activate_file() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $tab_name = $post_data['name'];

            $tab_list = array('InPOMCycle', 'OutOfPOMCycle', 'ZBTIssue');

            $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);

            if (in_array($tab_name, $tab_list) && ($is_pom_admin === false)) {
                $http_status = 403;
                $response['status'] = 'No Permissions';
                $response['success'] = false;
            }
            else {
                $row_id = encrypted_string($post_data['row_id'], 'decode');

                $headers = [
                    'accept: application/json',
                    'Content-Type: application/json'
                ];

                $api_params = '';
                $api_endpoint = RHOMBUS_PYTHON_URL.'/socom/dt_table/upload?row_id='.$row_id;

                try {
                    $res = php_api_call(
                        'POST',
                        $headers,
                        json_encode($api_params),
                        $api_endpoint
                    );
    
                    $http_status = 200;
                    $response['status'] = 'SOCOM Document activate success';
                } catch (ErrorException $e) {
                    $http_status = 500;
                    $response['status'] = 'Error processing Socom Document upload activate request';
                }
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }
}
