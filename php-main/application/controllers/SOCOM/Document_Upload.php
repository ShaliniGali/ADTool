<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Document_Upload extends CI_Controller {

    protected const CONTENT_TYPE_JSON = 'application/json';
    protected const MAX_FILE_SIZE = 20971520; // 20MB
    protected const ALLOWED_EXTENSIONS = ['xlsx', 'xls', 'csv', 'pdf', 'doc', 'docx'];
    protected const UPLOAD_PATH = 'secure_uploads/documents/';

    public function __construct()
    {
        parent::__construct();
        
        // Check authentication
        if(auth_coa_role_restricted() != null) {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }
        
        $this->load->model('SOCOM_Database_Upload_model');
        $this->load->model('SOCOM_Scheduler_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Site_User_model');
        $this->load->library('SOCOM/Database_Upload_Services');
        $this->load->library('upload');
        
        // Create upload directory if it doesn't exist
        $this->createUploadDirectory();
    }

    /**
     * Main upload page
     */
    public function index() {
        $this->user_can_access(true);

        $data['user_id'] = (int)$this->session->userdata('logged_in')['id'];
        $data['max_file_size'] = $this->formatBytes(self::MAX_FILE_SIZE);
        $data['allowed_extensions'] = implode(', ', self::ALLOWED_EXTENSIONS);
               
        $page_data['page_title'] = "SOCOM Document Upload";
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
        $data['cycle_name'] = $active_cycle['CYCLE_NAME'];

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('templates/essential_javascripts');
        $this->load->view('SOCOM/dashboard/upload/document_upload', $data);
        $this->load->view('templates/close_view');
    }

    /**
     * Handle file upload
     */
    public function upload_file() {
        $this->user_can_access(false);
        
        $response = ['success' => false, 'message' => '', 'file_id' => null];
        
        try {
            // Check if file was uploaded
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            $file = $_FILES['document'];
            
            // Validate file
            $validation_result = $this->validateUploadedFile($file);
            if ($validation_result !== true) {
                throw new Exception($validation_result);
            }

            // Process the upload
            $upload_result = $this->processFileUpload($file);
            if (!$upload_result) {
                throw new Exception('Failed to process file upload');
            }

            $response['success'] = true;
            $response['message'] = 'File uploaded successfully';
            $response['file_id'] = $upload_result;
            $response['file_name'] = $file['name'];
            
            $http_status = 200;
            
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            $http_status = 400;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    /**
     * Get upload history for user
     */
    public function get_upload_history() {
        $this->user_can_access(false);
        
        $user_id = (int)$this->session->userdata('logged_in')['id'];
        $uploads = $this->SOCOM_Database_Upload_model->get_user_uploads($user_id);
        
        $response = [
            'success' => true,
            'uploads' => $uploads
        ];

        $this->output
            ->set_status_header(200)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    /**
     * Download uploaded file
     */
    public function download_file($file_id) {
        $this->user_can_access(false);
        
        $user_id = (int)$this->session->userdata('logged_in')['id'];
        $upload = $this->SOCOM_Database_Upload_model->get_upload($user_id, $file_id);
        
        if (!$upload) {
            show_error('File not found', 404);
            return;
        }

        $file_path = $this->getFilePath($upload['S3_PATH']);
        
        if (!file_exists($file_path)) {
            show_error('File not found on server', 404);
            return;
        }

        $this->load->helper('download');
        force_download($upload['FILE_NAME'], file_get_contents($file_path));
    }

    /**
     * Delete uploaded file
     */
    public function delete_file() {
        $this->user_can_access(false);
        
        $response = ['success' => false, 'message' => ''];
        
        try {
            $file_id = $this->input->post('file_id');
            if (!$file_id) {
                throw new Exception('File ID is required');
            }

            $user_id = (int)$this->session->userdata('logged_in')['id'];
            $result = $this->SOCOM_Database_Upload_model->update_file_status($file_id, FILE_STATUS_DELETED);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'File deleted successfully';
                $http_status = 200;
            } else {
                throw new Exception('Failed to delete file');
            }
            
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            $http_status = 400;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    /**
     * Validate uploaded file
     */
    private function validateUploadedFile($file) {
        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return 'File size exceeds maximum allowed size of ' . $this->formatBytes(self::MAX_FILE_SIZE);
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return 'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS);
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            return isset($error_messages[$file['error']) ? $error_messages[$file['error']] : 'Unknown upload error';
        }

        return true;
    }

    /**
     * Process file upload
     */
    private function processFileUpload($file) {
        $user_id = (int)$this->session->userdata('logged_in')['id'];
        $cycle_id = get_cycle_id();
        
        // Generate unique filename
        $timestamp = date('Y_m_d_H_i_s');
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = "socom_doc_{$timestamp}_{$user_id}.{$extension}";
        
        // Move uploaded file to secure directory
        $upload_path = APPPATH . self::UPLOAD_PATH . $unique_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            return false;
        }

        // Save to database
        $upload_data = [
            'TYPE' => 'DOCUMENT',
            'CYCLE_ID' => $cycle_id,
            'S3_PATH' => $unique_filename,
            'FILE_NAME' => $file['name'],
            'VERSION' => '1.0',
            'TITLE' => pathinfo($file['name'], PATHINFO_FILENAME),
            'DESCRIPTION' => 'Document upload via SOCOM interface',
            'USER_ID' => $user_id,
            'FILE_STATUS' => FILE_STATUS_NEW
        ];

        $file_id = $this->SOCOM_Database_Upload_model->save_to_database($user_id, $upload_data);
        
        if ($file_id) {
            // Add to processing pipeline
            $this->SOCOM_Scheduler_model->add_to_pipeline('DOCUMENT');
            $this->SOCOM_Scheduler_model->add_to_map('DOCUMENT', $file_id, $file_id);
            
            // Track in git if applicable
            $this->SOCOM_Git_Data_model->git_track_data(GitDataType::UPLOAD_FILE, $file_id, $user_id);
        }

        return $file_id;
    }

    /**
     * Create upload directory if it doesn't exist
     */
    private function createUploadDirectory() {
        $upload_dir = APPPATH . self::UPLOAD_PATH;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
    }

    /**
     * Get file path from S3 path
     */
    private function getFilePath($s3_path) {
        return APPPATH . self::UPLOAD_PATH . $s3_path;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Check if user can access upload functionality
     */
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
}
