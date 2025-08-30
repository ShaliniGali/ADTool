<?php

#[AllowDynamicProperties]
class Database_Upload_Services {
	// Set the TEMP_UPLOAD_DIR to the secure upload folder
	// applications/secure_uploads/{my_upload_name} will work
	public const TEMP_UPLOAD_DIR = APPPATH.'secure_uploads/socom/';

	public function __construct() {
        $this->CI =& get_instance();

		$this->CI->load->model('SOCOM_Database_Upload_model');
		$this->CI->load->model('AWS');
	}

	/**
	 * Will validate an uploaded CSV file
	 * 
	 * @param string $filename
	 * 
	 * @return bool|string String will be the current error message
	 * 
	 * @throws ErrorException
	 * @throws InvalidArgumentException
	 */
	public function validateUploadCsv($filename, $fiscal_year=null)
	{
		$status = true;

		try {
			$filename_full_path = $this->checkFilePath($filename);
		} catch (ErrorException $e) {
			throw $e;
		}

		return $status;
	}

	/**
	 * Will delete the Database Upload file from S3
	 * 
	 * @param string $file_name name of file in s3
	 * 
	 * @return false|string ObjectURL, URL of the uploaded object
	 */
	public function deleteFileFromS3($user_id, $file_id)
	{
		$upload = $this->CI->SOCOM_Database_Upload_model->get_upload($user_id, $file_id);

		if (
			$upload === false ||
			$upload['can_edit'] === false
		) {
			return false;
		}

		try {
			$result = $this->CI->AWS->deleteS3File($upload['S3_PATH'], SOCOM_S3_BUCKET, SOCOM_S3_REGION, GUARDIAN_ACCESS_KEY, GUARDIAN_SECRET_KEY);

		} catch (ErrorException $e) {
			$result = false;
		}

		return $result;
	}

	/**
	 * Will save the Database Upload file to S3
	 * 
	 * @param string $file_name name of file in s3
	 * @param string $local_file local file name
	 * 
	 * @return false|string ObjectURL, URL of the uploaded object
	 */
	public function saveFileToS3($file_name, $local_file, UploadType $uploadType)
	{
		try {
			$file_path = $this->checkFilePath($local_file);

			$file_name = sprintf('%s/%s/%s', 'SOCOM/scheduler_upload', $uploadType->getUploadPrefix(), $file_name);

			$result = $this->CI->AWS->saveS3File(
				$file_path, 
				$file_name, 
				SOCOM_S3_BUCKET, 
				SOCOM_S3_REGION, 
				GUARDIAN_ACCESS_KEY, 
				GUARDIAN_SECRET_KEY
			);

			$result = parse_url($result, PHP_URL_PATH);
			if ($result !== false && strpos($result, '/') === 0) {
				$result = substr($result, 1);
			}
			$result = urldecode($result);
		} catch (ErrorException|Exception $e) {
			log_message('error', $e->getMessage());
			$result = false;
		}

		return $result;
	}

	/**
	 * Deletes the upload file
	 * 
	 * @return bool true if the file was deleted
	 * @throws ErrorException
	 */
	public function deleteUploadFile($file_name)
	{
		$result = false;

		try {
			$file_path = $this->checkFilePath($file_name);

			$result = unlink($file_path);

			if ($result !== true || file_exists($file_path)) {
				throw new ErrorException('File exists and could not delete');
			}
		} catch (ErrorException $e) {
			$log = sprintf('Unable to delete %s', $file_name);
			log_message('error', $log);

			throw $e;
		}

		return $result;
	}

	/**
	 * @param string|null $file_name the name of the file and create the file if it does not exist
	 * 
	 * @return string $file_name prefixed with the TEMP folder as a directory.
	 */
	public function getTempDir($file_name = null)
	{
		if ($file_name !== null) {
			// If the path exists as a symlink, throw error.
			if (is_link($file_name)) {
				log_message('error', sprintf('file_name exists as a symlink: %s', $file_name));
				throw new ErrorException('file_name exists as a symlink');
			}

			if (basename($file_name) !== $file_name) {
				log_message('error', sprintf('file_name is incorrect: %s', $file_name));
				throw new ErrorException('file_name is incorrect');
			}

			$file_path = sprintf('%s/%s', static::TEMP_UPLOAD_DIR, $file_name);
		} else {
			$file_path = static::TEMP_UPLOAD_DIR;
		}

		$file_real_path = realpath($file_path);

		if ($file_real_path === false) {
			$log = sprintf('File path incorrect %s', $file_path);
			log_message('error', $log);

			throw new ErrorException('Please check file path it is incorrect.');
		}

		return $file_real_path;
	}

	/**
	 * @param string $file_name - basename of file
	 * 
	 * @throws ErrorException
	 * 
	 * @return string - full path to file
	 */
	public function checkFilePath($file_name)
	{
		try {
			$file_path = $this->getTempDir($file_name);
		} catch (ErrorException $e) {
			throw $e;
		}

		if (!is_file($file_path) || !is_readable($file_path)) {
			$log = sprintf('File: %s, File not found or is not readable', $file_name);
			log_message('error', $log);
			throw new ErrorException('File not found or is not readable', 1001);
		}

		if (!is_writable($file_path)) {
			$log = sprintf('File: %s, File is not writeable', $file_name);
			log_message('error', $log);
			throw new ErrorException('File not found or is not writable', 1001);
		}

		return $file_path;
	}

    /**
     * Check if the upload file has virous
     * 
     * @param int $user_id
     * @param string $file_name
     * @param string $file_name_full_path
     * 
     * @return	boolean|string  
     */
	public function runAntivirus($user_id, $file_name, $file_name_full_path) {
		if (!file_exists($file_name_full_path)) {
			$log = 'Virus scan attempted, but no files exists';

			log_message('error', $log);
			log_message('error', $file_name_full_path);

			return $log;
		}

		list($headers, $content) = php_api_call_upload_file($file_name_full_path, 'file', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

		$call_res = php_api_call(
			'POST', 
			$headers, 
			$content, 
			RHOMBUS_PYTHON_URL.'/avscan_uploadfile'
		);
		$avr = json_decode($call_res, true);

        $delete_file = function ($file_name) {
            $is_deleted = unlink($file_name);
			
            return $is_deleted;
        };

		if(isset($avr['status']) && in_array($avr['status'], ['FOUND', 'ERROR', 'OK'])) {
            if ($avr['status'] === 'FOUND' || $avr['status'] === 'ERROR') {
                $delete_file($file_name_full_path);
            }

            if ($avr['status'] === 'FOUND') {
				log_message('error', 'Virus discovered from a user file upload');
                return $this->create_file_status_virus($file_name, $user_id);
			} else if ($avr['status'] === 'ERROR') {
				log_message('error', 'Virus detection ERROR. AV Scan API unable to scan file');
                return 'Error with AV Scanner';
			} else if ($avr['status'] === 'OK') {
				log_message('info', 'No virus discovered in file upload');
				return true;
			}
		}

        $delete_file($file_name_full_path);
		log_message('error', 'Unable to contact AV scanner');
		return 'Unable to contact scanner';
	}

    
}