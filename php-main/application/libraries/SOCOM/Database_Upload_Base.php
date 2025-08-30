<?php

require_once(APPPATH.'libraries/SOCOM/Database_Upload_Interface.php');

#[AllowDynamicProperties]
class Database_Upload_Base implements Database_Upload_Interface {
    protected const TYPE = false; // please define in child class to match database TYPE field
    protected const CONTENT_TYPE_JSON = 'application/json';
    protected const MAX_FILE_SIZE = 20971520;

    protected $filePostName;
    protected $fileName = 'socom_%s_%s.xlsx';
    protected $uploadType;
    protected $params = [];

    protected $usr_dt_uploads_id = false;

    public function __construct() {
        $this->CI =& get_instance();

        $this->params['TYPE'] = static::TYPE;
    }

    public function setParams(array $params, UploadType $uploadType) {
        $this->usr_dt_uploads_id = false;

        $this->params = array_merge($this->params, $params);
        
        $this->params['CYCLE_ID'] = get_cycle_id();
        if (!is_int($this->params['CYCLE_ID'])) {
            throw new ErrorException("No Active Cycle and cannot proceed to save upload");
        }
        $this->params['USER_ID'] = (int)$this->CI->session->userdata("logged_in")["id"];
        $this->params['FILE_STATUS'] = FILE_STATUS_NEW;

        $this->uploadType = $uploadType;
    }

    public function validateFile(string $uploadFileName) {
        $userId = (int)$this->CI->session->userdata("logged_in")["id"];

        try {
            $upload_config = array(
                'upload_path' => $this->CI->database_upload_services->getTempDir(),
                'file_name' => $uploadFileName,
                'allowed_types' => 'xlsx',
                'max_size' => self::MAX_FILE_SIZE,
                'overwrite' => true // Ensure file is never duplicated
            );

            $this->CI->load->library('upload', $upload_config);
        } catch(ErrorException $e) {
            throw new ErrorException($e->getMessage());
        }

        if ($this->CI->upload->do_upload($this->filePostName)) {
            try {
                $status = $this->CI->database_upload_services->validateUploadCsv($uploadFileName);
                
                $this->checkResult($status);
            } catch(ErrorException $e) {
                throw new ErrorException($e->getMessage());
            }
        } else {
            $status = $this->CI->upload->display_errors('', '');
        }

        $this->params['FILE_NAME'] = filter_var($_FILES[$this->filePostName]['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        return $status;
    }

    public function virusScan() {
        $userId = (int)$this->CI->session->userdata("logged_in")["id"];

        $status = false;
        // If the file was uploaded correctly, create database upload
        if (isset($_FILES[$this->filePostName]['name'], $_FILES[$this->filePostName]['tmp_name'])) {
            $status = $this->CI->database_upload_services->runAntivirus(
                $userId, 
                $_FILES[$this->filePostName]['name'],
                $_FILES[$this->filePostName]['tmp_name']
            );

            if ($status !== true) {
                $response['validation'] = true;
                throw new UnexpectedValueException($status);
            }
        }

        return $status;
    }

    public function checkResult($status) {
        if ($status !== true) {
            throw new ErrorException($status);
        }
    }

    public function saveUpload() {
        if (!isset($this->filePostName, $_FILES[$this->filePostName])) {
            log_message('error', 'PHP $_FILES[ name ] name not set or not found in upload data.');
            
            throw new ErrorException('No file upload detected.');
        }

        try {
            $status = $this->virusScan();
            $this->checkResult($status);
        } catch(ErrorException $e) {
            throw new ErrorException($e->getMessage());
        }

        $userId = (int)$this->CI->session->userdata("logged_in")["id"];
        $uploadFileName = sprintf($this->fileName,  date('Y_m_d_h_i_s'), $userId);

        try {
            $status = $this->validateFile($uploadFileName);
            $this->checkResult($status);
        } catch(ErrorException $e) {
            log_message('error', $e->getMessage());

            throw new ErrorException($e->getMessage());
        }

        try {
            $status = $this->saveToS3($uploadFileName, $this->uploadType);
        } catch(ErrorException $e) {
            log_message('error', $e->getMessage());

            throw new ErrorException($e->getMessage());
        }

        try {
            $status = $this->saveToDatabase();
        } catch(ErrorException $e) {
            log_message('error', $e->getMessage());

            throw new ErrorException($e->getMessage());
        }

        return $status;
    }

    public function setFilePostName(string $val) {
        $this->filePostName = $val;
    }

    public function saveToS3(string $uploadFileName, UploadType $uploadType) {
        $s3_path = $this->CI->database_upload_services->saveFileToS3($uploadFileName, $uploadFileName, $uploadType);
        if ($s3_path) {
            $this->params['S3_PATH'] = $s3_path;
            $this->deleteUploadedFile($uploadFileName);
        } else {
            log_message('error', 'Unable to upload file to s3');
            throw new UnexpectedValueException('Unable to upload file to s3');
        }

        return $s3_path !== false;
    }

    public function deleteUploadedFile(string $uploadFileName) {
        try {
            $this->CI->database_upload_services->deleteUploadFile($uploadFileName);
        } catch (ErrorException $e) {
            log_message('error', 'Could not delete upload file');
        }
    }

    public function saveToDatabase() {
        $userId = (int)$this->CI->session->userdata("logged_in")["id"];

        try {
            $status = $this->CI->SOCOM_Database_Upload_model->save_to_database($userId, $this->params, $this->uploadType);
            if (is_int($status)) {
                $this->usr_dt_uploads_id = $status;
            }
        } catch(ErrorException $e) {
            log_message('error', $e->getMessage());
            throw new ErrorException($e->getMessage());
        }

        return $status;
    }

    public function saveToMetaDatabase() {
        try {
            if ($this->usr_dt_uploads_id === false) {
                throw new ErrorException('USR_DT_UPLOADS_ID is false, unable to save metadata');
            }
            $status = $this->CI->SOCOM_Database_Upload_Metadata_model->save_to_metadata_db($this->usr_dt_uploads_id, $this->params, $this->uploadType);
        } catch(ErrorException $e) {
            log_message('error', $e->getMessage());
            throw new ErrorException($e->getMessage());
        }

        return $status;
    }

}