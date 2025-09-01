<?php

require_once(APPPATH.'models/SOCOM_Database_Upload_model.php');

#[AllowDynamicProperties]
class SOCOM_Database_Upload_Metadata_model extends SOCOM_Database_Upload_model
{
	/**
	 * Save metadata information to the database USR_DT_UPLOADS_METADATA,
	 * 
	 * @param int $user_id
	 * @param array $params
	 * 
	 * @return bool true on success or false on failure
	 */
	public function save_to_metadata_db(int $usr_dt_uploads_id, array $params, UploadType $uploadType){
		$is_append = 0;
		$table_listing = $params['TABLE_LISTING'];
		$pom_year = $params['POM_YEAR'];

		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
		$user_id = (int)$this->session->userdata("logged_in")["id"];

		if ($cap_sponsor === false) {
			$log = sprintf('User ID: %s, does not have a Capability Sponsor', $user_id ?? 'Not Logged In');
			throw new ErrorException($log);
		}
		
		if ($uploadType->value === UploadType::DT_OUT_POM->value) {
			$table_name = $table_listing;
			$dirty_table_name = null;
		} else {
			$table_name = "DT_{$table_listing}_{$pom_year}";
			$dirty_table_name = "DT_{$table_listing}_DIRTY_{$pom_year}";
		}

		$this->DBs->SOCOM_UI->trans_start();

		$usr_dt_table_metadata_id = $this->set_table_meta($table_name, $dirty_table_name, $user_id);
		if (is_int($usr_dt_table_metadata_id)) {
			$this->DBs->SOCOM_UI
				->set('USR_DT_UPLOADS_ID', $usr_dt_uploads_id)
				->set('POM_YEAR', $pom_year)
				->set('TABLE_TYPE_DESCR', $table_listing)
				->set('TABLE_NAME', $table_name)
				->set('CAP_SPONSOR', $cap_sponsor)
				->set('USR_DT_TABLE_METADATA_ID', $usr_dt_table_metadata_id)
				->insert('USR_DT_UPLOADS_METADATA');

			$user_id = (int)$this->session->userdata['logged_in']['id'];

			$CI = get_instance();
		
			$CI->SOCOM_Git_Data_model->git_track_data(GitDataType::CREATE_METADATA, $usr_dt_uploads_id, $user_id);
		
			$this->DBs->SOCOM_UI->trans_complete();

			$result = $this->DBs->SOCOM_UI->trans_status();
		} else {
			$this->DBs->SOCOM_UI->trans_rollback();
			$log = sprintf('Upload Table Metadata could not be saved');
			throw new ErrorException($log);
		}
		

		return $result;
	}

	public function set_table_meta(string $final_table_name, string $dirty_table_name, int $user_id) {
		$cycle_id = get_cycle_id();

		$result = $this->DBs->SOCOM_UI
			->select('ID')
			->select('COUNT(*) as COUNT', false)
			->from('USR_DT_LOOKUP_TABLE_METADATA')
			->where('CYCLE_ID', $cycle_id)
			->where('FINAL_TABLE_NAME', $final_table_name)
			->where('DIRTY_TABLE_NAME', $dirty_table_name)
			->get()->row_array();

        if (($result['COUNT'] ?? 0) <= 0) {
			$this->DBs->SOCOM_UI
				->set('CYCLE_ID', $cycle_id)
				->set('USER_ID', $user_id)
				->set('FINAL_TABLE_NAME', $final_table_name)
				->set('DIRTY_TABLE_NAME', $dirty_table_name)
				->insert('USR_DT_LOOKUP_TABLE_METADATA');
		}
			
        return $result['ID'] ?? $this->DBs->SOCOM_UI->insert_id();
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
			->select('tm.IS_ACTIVE')
			->select('s.WARNINGS')
			->from('USR_DT_UPLOADS u')
            ->join('USR_DT_SCHEDULER_MAP m', 'ON m.MAP_ID = u.ID')
            ->join('USR_DT_SCHEDULER s', 'ON s.ID = m.DT_SCHEDULER_ID')
			->join('USR_LOOKUP_CYCLES c', 'ON c.ID = s.CYCLE_ID')
			->join('USR_DT_UPLOADS_METADATA tm', 'ON tm.USR_DT_UPLOADS_ID = u.ID')
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

	public function get_metadata_with_cap_sponsor($file_id, UploadType $uploadType){
		static $metadata_result = [];

		if(!isset($metadata_result[$file_id])) {

			$cycle_id = get_cycle_id();
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
				->select('c.CYCLE_NAME')
				->select('tm.CAP_SPONSOR')
				->select('s.WARNINGS')
				->select('tm.TABLE_NAME')
				->select('dtm.DIRTY_TABLE_NAME')
				->select('tm.USR_DT_TABLE_METADATA_ID')
				->select('dtm.REVISION')
				->from('USR_DT_UPLOADS u')
				->join('USR_DT_SCHEDULER_MAP m', 'ON m.MAP_ID = u.ID')
				->join('USR_DT_SCHEDULER s', 'ON s.ID = m.DT_SCHEDULER_ID')
				->join('USR_LOOKUP_CYCLES c', 'ON c.ID = s.CYCLE_ID')
				->join('USR_DT_UPLOADS_METADATA tm', 'ON tm.USR_DT_UPLOADS_ID = u.ID')
				->join('USR_DT_LOOKUP_TABLE_METADATA dtm', 'ON dtm.ID = tm.USR_DT_TABLE_METADATA_ID', 'left')
				->where('u.ID', $file_id)
				->where('c.ID', $cycle_id)
				->where('c.IS_ACTIVE', 1)
				->where('u.TYPE', $uploadType->name)
				->where_in('u.FILE_STATUS', [FILE_STATUS_REQUESTED, FILE_STATUS_NEW]);
				if ($pom_admin !== true) {
					$this->DBs->SOCOM_UI
						->where('tm.CAP_SPONSOR', $cap_sponsor);
				}
			$metadata_result[$file_id] = $this->DBs->SOCOM_UI
					->get()->row_array();
		}
		return $metadata_result[$file_id];
	}
	public function get_metadata_admin(UploadType $uploadType) {
		$cycle_id = get_cycle_id();
		$user_id = (int)$this->session->userdata['logged_in']['id'];

		$this->DBs->SOCOM_UI
			->select('distinct dtm.ID', false)
			->select('dtm.DIRTY_TABLE_NAME')
			->select('dtm.FINAL_TABLE_NAME')
			->select('dtm.IS_FINAL_TABLE_ACTIVE')
			->select('dtm.USER_ID')
			->select('dtm.REVISION')
			->select('dtm.CREATED_TIMESTAMP')
			->select('dtm.UPDATED_TIMESTAMP')
			->from('USR_DT_LOOKUP_TABLE_METADATA dtm')
			->join('USR_DT_UPLOADS_METADATA m', 'm.USR_DT_TABLE_METADATA_ID = dtm.ID')
			->join('USR_DT_UPLOADS u', 'u.ID = m.USR_DT_UPLOADS_ID')
			->join('USR_LOOKUP_CYCLES c', 'c.ID = dtm.CYCLE_ID')
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->where('u.TYPE', 'DT_UPLOAD_EXTRACT_UPLOAD');

		$results = $this->DBs->SOCOM_UI->get()->result_array();
		
		foreach ($results as &$row) {
			$row['ID'] = encrypted_string($row['ID'], 'encode');
		}

		return $results;
	}

	public function get_metadata_admin_id(int $id) {
		$cycle_id = get_cycle_id();
		$user_id = (int)$this->session->userdata['logged_in']['id'];

		$this->DBs->SOCOM_UI
			->select('dtm.ID')
			->select('DIRTY_TABLE_NAME')
			->select('FINAL_TABLE_NAME')
			->select('IS_FINAL_TABLE_ACTIVE')
			->select('dtm.USER_ID')
			->select('dtm.REVISION')
			->select('CREATED_TIMESTAMP')
			->select('UPDATED_TIMESTAMP')
			->from('USR_DT_LOOKUP_TABLE_METADATA dtm')
			->join('USR_LOOKUP_CYCLES c', 'c.ID = dtm.CYCLE_ID')
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->where('dtm.ID', $id);

		$results = $this->DBs->SOCOM_UI->get()->row_array();
		
		$results['ID'] = encrypted_string($results['ID'], 'encode');

		return $results;
	}

	public function get_metadata_aggregate_admin_id(int $id) {
		$cycle_id = get_cycle_id();
		$user_id = (int)$this->session->userdata['logged_in']['id'];

		$this->DBs->SOCOM_UI
			->select('dtm.ID')
			->select('DIRTY_TABLE_NAME')
			->select('FINAL_TABLE_NAME')
			->select('IS_FINAL_TABLE_ACTIVE')
			->select('dtm.USER_ID')
			->select('dtm.REVISION')
			->select('dtm.CREATED_TIMESTAMP')
			->select('dtm.UPDATED_TIMESTAMP')
			->select('GROUP_CONCAT(DISTINCT u.S3_PATH SEPARATOR ", ") as S3_PATH')
			->select('GROUP_CONCAT(DISTINCT u.FILE_NAME SEPARATOR ", ") as FILE_NAME')
			->select('GROUP_CONCAT(DISTINCT u.USER_ID SEPARATOR ", ") as USER_ID')
			->select('GROUP_CONCAT(DISTINCT m.CAP_SPONSOR SEPARATOR ", ") as CAP_SPONSOR')
			->from('USR_DT_LOOKUP_TABLE_METADATA dtm')
			->join('USR_DT_UPLOADS_METADATA m', 'm.USR_DT_TABLE_METADATA_ID = dtm.ID')
			->join('USR_DT_UPLOADS u', 'u.ID = m.USR_DT_UPLOADS_ID')
			->join('USR_LOOKUP_CYCLES c', 'c.ID = dtm.CYCLE_ID')
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->where('dtm.ID', $id);

		$results = $this->DBs->SOCOM_UI->get()->row_array();
		
		$results['ID'] = encrypted_string($results['ID'], 'encode');

		return $results;
	}

	public function has_submitted_status_by_metadata_id(int $id): bool
	{
		$result = $this->DBs->SOCOM_UI
			->select('DIRTY_TABLE_NAME')
			->from('USR_DT_LOOKUP_TABLE_METADATA')
			->where('ID', $id)
			->get()
			->row_array();

		if (empty($result['DIRTY_TABLE_NAME'])) {
			return false;
		}

		$table = $result['DIRTY_TABLE_NAME'];

		$sql = "SELECT 1 FROM {$table} WHERE submission_status = 'SUBMITTED' LIMIT 1";
		$q = $this->DBs->SOCOM_UI->query($sql);
		return $q->num_rows() > 0;
	}

	public function get_metadata_view_status($id) {
		$cycle_id = get_cycle_id();

		return $this->DBs->SOCOM_UI
			->distinct()
			->select("
				u.USER_ID,
				m.CAP_SPONSOR,
				Title,
				sa.ACTION_STATUS AS SUBMISSION_STATUS
			")
			->from('USR_DT_LOOKUP_TABLE_METADATA dtm')
			->join('USR_DT_UPLOADS_METADATA m', 'm.USR_DT_TABLE_METADATA_ID = dtm.ID')
			->join('USR_DT_UPLOADS u', 'u.ID = m.USR_DT_UPLOADS_ID')
			->join('USR_LOOKUP_CYCLES c', 'c.ID = dtm.CYCLE_ID')
			->join(
				'USR_DT_SUBMIT_APPROVE_ACTIONS sa',
				"sa.USER_ID = u.USER_ID",
				'left'
			)
			->where('c.ID', $cycle_id)
			->where('c.IS_ACTIVE', 1)
			->where('dtm.ID', $id)
			->get()
			->result_array();
	}
}