<?php

#[AllowDynamicProperties]
class SOCOM_Database_Upload_model extends CI_Model
{
	/**
	 * Will save the Database Upload to the database
	 * 
	 * @param int $user_id
	 * @param array $params
	 * 
	 * @return bool true on ssuccess or false on failure
	 */
	public function save_to_database($user_id, $params)
	{
		$cycle_id = get_cycle_id();

		$result = false;

		$can_upload = $this->check_if_user_can_upload();
		$date = date("Y-m-d H:i:s");

		if (!is_int($user_id) || $can_upload === false) {
			return $result;
		}


		$upload_type = UploadType::from($params['TYPE']);
		
        $this->DBs->SOCOM_UI->trans_start();
		
		$this->DBs->SOCOM_UI
			->set('TYPE', $upload_type->name)
			->set('CYCLE_ID', $cycle_id)
			->set('S3_PATH', $params['S3_PATH'])
			->set('FILE_NAME', $params['FILE_NAME'])
			->set('VERSION', $params['VERSION'])
			->set('TITLE', $params['TITLE'])
			->set('DESCRIPTION', $params['DESCRIPTION'])
			->set('CYCLE_ID', $cycle_id)
			->set('USER_ID', $user_id)
			->set('CREATED_TIMESTAMP', $date)
			->set('FILE_STATUS', FILE_STATUS_NEW);

		if ($this->DBs->SOCOM_UI->insert('USR_DT_UPLOADS')) {
			$usr_dt_uploads_id = $this->DBs->SOCOM_UI->insert_id();

            $CI = get_instance();

            $pipline_id = $CI->SOCOM_Scheduler_model->add_to_pipeline($upload_type);


			$CI->SOCOM_Scheduler_model->add_to_map($upload_type, $pipline_id, $usr_dt_uploads_id);
            
			if ($upload_type->getUploadPrefix() !== 'program_alignment') {
				$CI->SOCOM_Git_Data_model->git_track_data(GitDataType::UPLOAD_FILE, $usr_dt_uploads_id, $user_id);
			}
		}

        $this->DBs->SOCOM_UI->trans_complete();
		return $this->DBs->SOCOM_UI->trans_status() ?  $usr_dt_uploads_id : false;
	}

	/**
	 * Get the one Database Upload for the user
	 * 
	 * @param int $user_id
	 * @param int $file_id
	 * 
	 * @return array
	 */
	public function get_upload($user_id, $file_id)
	{
		$cycle_id = get_cycle_id();

		if (!is_int($user_id) || !is_int($file_id)) {
			return false;
		}

		$results = $this->DBs->SOCOM_UI
			->select('u.ID')
            ->select('u.TYPE')
			->select('u.FILE_STATUS')
			->select('u.S3_PATH')
			->select('u.FILE_NAME')
			->select('u.VERSION')
			->select('u.TITLE')
			->select('u.DESCRIPTION')
			->select('u.USER_ID')
            ->select('u.UPDATE_USER_ID')
			->select('u.CREATED_TIMESTAMP')
            ->select('u.UPDATED_TIMESTAMP')
            ->select('s.CRON_STATUS')
            ->select('s.CRON_PROCESSED')
            ->select('s.ERRORS')
			->from('USR_DT_UPLOADS u')
            ->join('USR_DT_SCHEDULER_MAP m', 'ON m.MAP_ID = u.ID')
            ->join('USR_DT_SCHEDULER s', 'ON s.ID = m.DT_SCHEDULER_ID')
			->join('USR_LOOKUP_CYCLES c', 'ON c.ID = s.CYCLE_ID')
			->where('u.ID', $file_id)
			->where('u.USER_ID', $user_id)
			->where('c.ID', $cycle_id)
			->order_by('u.CREATED_TIMESTAMP', 'DESC')
			->get()
			->result_array();

		if (!empty($results)) {
			$this->format_user_file_results($results, $user_id, true);
		}

		return $results[0] ?? false;
	}

	/**
	 * Get the Database Uploads for the user
	 * 
	 * @param int $user_id
	 * 
	 * @return array
	 */
	public function get_file_upload_list($user_id, UploadType $upload_type)
	{
		$cycle_id = get_cycle_id();
		
		if (!is_int($user_id)) {
			return false;
		}

		$results = $this->DBs->SOCOM_UI
			->select('u.ID')
			->select('u.FILE_STATUS')
			->select('u.VERSION')
			->select('u.DESCRIPTION')
			->select('u.S3_PATH')
			->select('u.FILE_NAME')
            ->select('u.TYPE')
			->select('u.USER_ID')
            ->select('u.UPDATE_USER_ID')
			->select('u.CREATED_TIMESTAMP')
            ->select('u.UPDATED_TIMESTAMP')
			->select('s.CRON_STATUS')
            ->select('s.CRON_PROCESSED')
			->select('c.CYCLE_NAME')
			->from('USR_DT_UPLOADS u')
            ->join('USR_DT_SCHEDULER_MAP m', 'ON m.MAP_ID = u.ID')
            ->join('USR_DT_SCHEDULER s', 'ON s.ID = m.DT_SCHEDULER_ID')
			->join('USR_LOOKUP_CYCLES c', 'ON c.ID = s.CYCLE_ID')
			->where('s.CRON_STATUS', CRON_STATUS_NEW)
			->where('u.USER_ID', $user_id)
			->where('u.TYPE', $upload_type->name)
			->where_in('u.FILE_STATUS', [FILE_STATUS_NEW, FILE_STATUS_VIRUS])
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->order_by('u.UPDATED_TIMESTAMP', 'DESC')
			->get()
			->result_array();
		
		if (!empty($results)) {
			$this->format_user_file_results($results, $user_id, true);
		}

		return $results;
	}

	/**
	 * Will add a FILE_STATUS_TXT field and a CRON_PROCESSED_TXT field to the user results
	 * 
	 * If email is true the user email will be added
	 * 
	 * @param array $results a reference to the results
	 * @param int $user_id
	 * @param bool $email
	 */
	public function format_user_file_results(&$results, $user_id, $email = false)
	{	
		if ($email === true) {
			$get_email = function ($id) {
				return $this->DBs->GUARDIAN_DEV
					->select('email')
					->from('users')
					->where('id', $id)
					->get()
					->row_array()['email'] ?? 'Not Found';
			};
		}

		foreach ($results as &$row) {
			if ($email === true) {
				$row['email'] = $get_email($row['USER_ID']);
			}

			if (
				$row['FILE_STATUS'] === (int)FILE_STATUS_CANCELLED ||
				$row['CRON_STATUS'] === (int)CRON_STATUS_PROCESSED ||
				(int)$row['USER_ID'] !== $user_id
			) {
				$row['can_edit'] = false;
			} else {
				$row['can_edit'] = true;
			}

			switch ($row['FILE_STATUS']) {
				case 0:
					$row['FILE_STATUS_TXT'] = 'Submitted';
					break;
				case 1:
					$row['FILE_STATUS_TXT'] = 'Waiting to Process';
					break;
				case 2:
					$row['FILE_STATUS_TXT'] = 'Deleted';
					break;
				case 3:
					$row['FILE_STATUS_TXT'] = 'Canceled';
					break;
				case 4:
					$row['FILE_STATUS_TXT'] = 'Virus Detected';
					break;
				default:
					$row['FILE_STATUS_TXT'] = 'Default';
					break;
			}

			if ($row['CRON_STATUS'] === 1) {
				$row['FILE_STATUS_TXT'] = 'Processed';
			}

			switch ($row['CRON_PROCESSED']) {
				case 0:
					$row['CRON_PROCESSED_TXT'] = 'Not Processed';
					break;
				case 1:
					$row['CRON_PROCESSED_TXT'] = 'Completed';
					break;
				case -1:
					$row['CRON_PROCESSED_TXT'] = 'Error';
					break;
				case -2:
					$row['CRON_PROCESSED_TXT'] = 'Format Error';
					break;
				default:
					$row['CRON_PROCESSED_TXT'] = 'Default';
					break;
			}

			$upl_type = UploadType::from($row['TYPE']);
			$row['TYPE_NAME'] = $upl_type->type_name();

			$row['ID'] = encrypted_string($row['ID'], 'encode');

			if (isset($row['IS_ACTIVE'], $row['TABLE_NAME']) && $row['IS_ACTIVE'] === 1) {
				if ($upl_type === UploadType::DT_UPLOAD_EXTRACT_UPLOAD) {
					$prefix = 'FINAL_';
					
					$this->get_table_metadata($row, $row['DIRTY_TABLE_NAME'], 'DIRTY_');

					$row['TOTAL_ROWS_EDIT'] = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($row['ID'], false);
					$row['TOTAL_ROWS_VIEW'] = $this->SOCOM_DT_Editor_model->get_dt_table_total_count($row['ID'], true);
				} else {
					$prefix = '';
				}
				$this->get_table_metadata($row, $row['TABLE_NAME'], $prefix);
			} else {
				$row['FINAL_CREATE_TIME'] = '';
				$row['FINAL_UPDATE_TIME'] = '';
				$row['DIRTY_CREATE_TIME'] = '';
				$row['DIRTY_UPDATE_TIME'] = '';
				$row['CURRENT_TIME'] = '';

				if ($upl_type === UploadType::DT_UPLOAD_EXTRACT_UPLOAD) {
					$row['TOTAL_ROWS_EDIT'] = 0;
					$row['TOTAL_ROWS_VIEW'] = 0;
				}
			}
		}
	}

	public function get_table_metadata(&$row, $table_name, $prefix='') {
		$result = $this->DBs->SOCOM_UI
			->select('CREATE_TIME')
			->select('UPDATE_TIME')
			->select('CURRENT_TIMESTAMP() as `CURRENT_TIME`', false)
			->from('INFORMATION_SCHEMA.TABLES')
			->where('table_schema', getenv('SOCOM_UI'))
			->where('table_name', $table_name)
			->get()
			->row_array();
		if (!empty($result)) {
			$row[$prefix.'CREATE_TIME'] = $result['CREATE_TIME'];
			$row[$prefix.'UPDATE_TIME'] = $result['UPDATE_TIME'];
			$row['CURRENT_TIME'] = $result['CURRENT_TIME'];
		}
	}

	/**
	 * Check if user is Authorized for Database Uploads
	 * 
	 * @return	mixed  
	 */
	public function check_if_user_can_upload()
	{
		if (RHOMBUS_FACS === 'TRUE') {
			log_message('error', 'SOCOM database upload is relying on FACS for upload permissions');

			return true;
		}

		$user_id = (int)$this->session->userdata['logged_in']['id'];

		$row = $this->DBs->GUARDIAN_DEV
			->select('ACCOUNT_TYPE')
			->from('users')
			->where('id', $user_id)
			->where('status', AccountStatus::Active)
			->get()
			->row_array()['ACCOUNT_TYPE'] ?? false;

		if (strtoupper($row) === 'ADMIN') {
			return true;
		}

		return true;
	}

	/**
	 * Will process, cancel or delete a file by setting the FILE_STATUS field
	 * 
	 * @param int $file_id
	 * @param int $user_id
	 * @param int $timestamp
	 * 
	 * @return	boolean  
	 */
	public function update_file_status($file_id, $file_status)
	{
		$cycle_id = get_cycle_id();

		$user_id = (int)$this->session->userdata('logged_in')['id'];

		$upload = $this->get_upload($user_id, $file_id);
		
		if ($upload['can_edit'] === false) {
			return false;
		}

		if (
			in_array((int)$file_status, [
				(int)FILE_STATUS_DELETED, 
				(int)FILE_STATUS_CANCELLED
				], true
			) === true
		){
			$CI = get_instance();
			$proceed = $CI->database_upload_services->deleteFileFromS3($user_id, $file_id);
		} else {
			$proceed = true;
		}

		if ($proceed === true) {
			$this->DBs->SOCOM_UI->set('FILE_STATUS', $file_status);
			$this->DBs->SOCOM_UI->set('UPDATE_USER_ID', $user_id);
			$this->DBs->SOCOM_UI->where('CYCLE_ID', $cycle_id);
			$this->DBs->SOCOM_UI->where('ID', $file_id);
			
			$upload_type = UploadType::from($upload['TYPE']);

			if ($this->DBs->SOCOM_UI->update('USR_DT_UPLOADS')) {
				switch ($file_status) {
					case FILE_STATUS_NEW:
						$data_type = 'UPLOAD_FILE';
						break;
					case FILE_STATUS_REQUESTED:
						$data_type = 'PROCESS_FILE';
						break;
					case FILE_STATUS_DELETED:
						$data_type = 'DELETE_FILE';
						break;
					case FILE_STATUS_CANCELLED:
						$data_type = 'CANCEL_FILE';
						break;
						default:
							$log = sprintf('File Status %s not supported for git tracking', $file_status);
							throw new ErrorException($log);
				}
				if ($upload_type->getUploadPrefix() !== 'program_alignment') {
					$git_track_value = GitDataType::from($data_type);

					$CI = get_instance();
					$CI->SOCOM_Git_Data_model->git_track_data($git_track_value, $file_id, $user_id);
				}
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
     * Setup file status to 4(upload file has virus)
     * 
     * @param string $file_name
     * @param int $user_id
     * 
     * @return boolean|string
     */
    public function create_file_status_virus($file_name, $user_id = NULL) {
		$cycle_id = get_cycle_id();

        if (!$user_id)
        {
            $user_id = $this->session->userdata('logged_in')['id'];
        }

        $date = date("Y-m-d H:i:s");

        $this->DBs->SOCOM_UI
			->set('UPDATE_USER_ID', $user_id)
			->set('USER_ID', $user_id)
			->set('FILE_NAME', $file_name)
			->set('CYCLE_ID', $cycle_id)
			->set('FILE_STATUS', FILE_STATUS_VIRUS);

        if ($this->DBs->SOCOM_UI->insert('USR_DT_UPLOADS'))
        {
            return 'Virus discovered unable to process file upload';
        }

        log_message('error', sprintf('Unable to set the FILE_STATUS to %s', FILE_STATUS_VIRUS));

        return 'Virus discovered unable to process file upload';
    }

	public function get_processed_list(UploadType $uploadType, $page=0) {
		$cycle_id = get_cycle_id();

		$user_id = (int)$this->session->userdata['logged_in']['id'];

		$results = $this->DBs->SOCOM_UI
            ->select('u.ID')
            ->select('u.TYPE')
            ->select('u.FILE_STATUS')
			->select('u.VERSION')
            ->select('u.S3_PATH')
            ->select('u.FILE_NAME')
            ->select('u.USER_ID')
            ->select('u.UPDATE_USER_ID')
            ->select('u.CREATED_TIMESTAMP')
            ->select('u.UPDATED_TIMESTAMP')
            ->select('s.CRON_STATUS')
            ->select('s.CRON_PROCESSED')
			->select('s.ERRORS')
			->select('c.CYCLE_NAME')
			->from('USR_DT_UPLOADS u')
            ->join('USR_DT_SCHEDULER_MAP m', 'ON m.MAP_ID = u.ID')
            ->join('USR_DT_SCHEDULER s', 'ON s.ID = m.DT_SCHEDULER_ID')
			->join('USR_LOOKUP_CYCLES c', 'ON c.ID = s.CYCLE_ID')
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->where('u.TYPE', $uploadType->name)
			->where('u.USER_ID', $user_id)
			->where('u.FILE_STATUS', FILE_STATUS_REQUESTED)
			//->where('s.CRON_STATUS', CRON_STATUS_PROCESSED)
			//->where('s.CRON_PROCESSED', CRON_PROCESSED_SUCCESS)
			->order_by('u.UPDATED_TIMESTAMP', 'DESC')
			->get()->result_array();

		$this->format_user_file_results($results, $user_id, true);

		return $results;
	}

	public function get_processed_list_pom_status(UploadType $uploadType, $page=0) {
		$cycle_id = get_cycle_id();

		$user_id = (int)$this->session->userdata['logged_in']['id'];

		$pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2) ?? false;

		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;

		$this->DBs->SOCOM_UI
            ->select('u.ID')
            ->select('u.TYPE')
            ->select('u.FILE_STATUS')
			->select('u.VERSION')
            ->select('u.S3_PATH')
            ->select('u.FILE_NAME')
            ->select('u.USER_ID')
            ->select('u.UPDATE_USER_ID')
            ->select('u.CREATED_TIMESTAMP')
            ->select('u.UPDATED_TIMESTAMP')
            ->select('s.CRON_STATUS')
            ->select('s.CRON_PROCESSED')
			->select('s.ERRORS')
			->select('s.WARNINGS')
			->select('c.CYCLE_NAME')
			->select('mt.TABLE_NAME')
            ->select('dtm.DIRTY_TABLE_NAME')
			->select('mt.IS_ACTIVE')
            ->select('mt.CAP_SPONSOR')
			->select('mt.TABLE_TYPE_DESCR')
			->from('USR_DT_UPLOADS u')
            ->join('USR_DT_SCHEDULER_MAP m', 'ON m.MAP_ID = u.ID')
			->join('USR_DT_UPLOADS_METADATA mt', 'ON mt.USR_DT_UPLOADS_ID = u.ID')
			->join('USR_DT_LOOKUP_TABLE_METADATA dtm', 'mt.USR_DT_TABLE_METADATA_ID = dtm.ID')
            ->join('USR_DT_SCHEDULER s', 'ON s.ID = m.DT_SCHEDULER_ID')
			->join('USR_LOOKUP_CYCLES c', 'ON c.ID = s.CYCLE_ID')
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->where('u.TYPE', $uploadType->name);
			
			if ($pom_admin !== true) {
				$this->DBs->SOCOM_UI
					->where('mt.CAP_SPONSOR', $cap_sponsor);
			}

		$results = $this->DBs->SOCOM_UI
				->where('u.FILE_STATUS', FILE_STATUS_REQUESTED)
				//->where('s.CRON_STATUS', CRON_STATUS_PROCESSED)
				//->where('s.CRON_PROCESSED', CRON_PROCESSED_SUCCESS)
				->order_by('u.UPDATED_TIMESTAMP', 'DESC')
				->get()->result_array();

		$this->format_user_file_results($results, $user_id, true);

		return $results;
	}

	public function get_fiscal_years_out_of_pom_upload() {
		$result = $this->DBs->SOCOM_UI
			->select('MAX(FISCAL_YEAR) + 1 AS BASE')
			->from('DT_BUDGET_EXECUTION')
			->where('SUM_ACTUALS IS NOT NULL', null, false)
			->get()
			->row_array();
		if ($result && isset($result['BASE'])) {
			$base = (int)$result['BASE'];
			return range($base - 4, $base);
		}
		return [];
	}
}

enum UploadType: string  {
	case PROGRAM_SCORE_UPLOAD = 'PROGRAM_SCORE_UPLOAD';
	case DT_UPLOAD_BASE_UPLOAD = 'DT_UPLOAD_BASE_UPLOAD';
	case DT_UPLOAD_BASE_UPLOAD_APPEND = 'DT_UPLOAD_BASE_UPLOAD_APPEND';
	case DT_UPLOAD_EXTRACT_UPLOAD = 'DT_UPLOAD_EXTRACT_UPLOAD';
	case DT_OUT_POM = 'DT_OUT_POM';

	public function type_name(): string
    {
        return match($this) {
            UploadType::PROGRAM_SCORE_UPLOAD => 'Program Alignment',
            UploadType::DT_UPLOAD_BASE_UPLOAD => 'In-Pom Cycle',
			UploadType::DT_UPLOAD_BASE_UPLOAD_APPEND => 'Out-of-Pom Cycle 1',
			UploadType::DT_UPLOAD_EXTRACT_UPLOAD => 'ZBT/Issue Data',
			UploadType::DT_OUT_POM => 'Out-of-Pom Cycle'
        };
    }

	public function getUploadPrefix(): string
    {
        return match($this) {
            UploadType::PROGRAM_SCORE_UPLOAD => 'program_alignment',
            UploadType::DT_UPLOAD_BASE_UPLOAD => 'in_pom',
			UploadType::DT_UPLOAD_BASE_UPLOAD_APPEND => 'out_of_pom_1',
			UploadType::DT_UPLOAD_EXTRACT_UPLOAD => 'zbt_issue_data',
			UploadType::DT_OUT_POM => 'out_of_pom'
        };
    }
}