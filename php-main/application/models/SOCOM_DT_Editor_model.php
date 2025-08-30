<?php

class SOCOM_DT_Editor_model extends CI_Model {

    public function get_edit_start_time() {
        $result = $this->DBs->SOCOM_UI
                ->select('CURRENT_TIMESTAMP() as edit_start_time', false)
                ->get()->row_array();
            
        if (!isset($result['edit_start_time'])) {
            throw new ErrorException('Unable to get edit start time');
        }

        return encrypted_string($result['edit_start_time'], 'encode');
    }

    public function check_recent_edits(int $usr_dt_upload, array $edit_jobs, string $edit_start_time) {
        $recent_edits = [];

        $start_time = sprintf("'%s'", encrypted_string($edit_start_time, 'decode'));

        foreach($edit_jobs as $edit_job) {
            $result = $this->DBs->SOCOM_UI
                ->select('COUNT(*) as COUNT', false)
                ->from('USR_DT_DIRTY_TABLE_EDIT_JOBS')
                ->where('PROGRAM_ID', $edit_job['PROGRAM_ID'])
                ->where('YEAR', $edit_job['YEAR'])
                ->where('IS_ACTIVE', 0)
                ->where('UPDATED_DATETIME >=', $start_time, false)
                ->where('UPDATED_DATETIME <=', 'CURRENT_TIMESTAMP()', false)
                ->get()->row_array();

            if ($result['COUNT'] ?? 0 > 0) {
                $recent_edits[] = [
                    'PROGRAM_ID' => $edit_job['PROGRAM_ID'],
                    'YEAR' => $edit_job['YEAR'],
                    'EDITED_ROW' => $this->get_usr_dt_upload_dirty_row($usr_dt_upload, $edit_job['PROGRAM_ID'], $edit_job['YEAR'], false),
                    'CHANGES' => $this->get_history_changes($edit_job['PROGRAM_ID'], $edit_job['YEAR'])
                ];
            }
        }

        return $recent_edits;
    }

    public function create_edit_jobs(array &$user_edits) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $edit_jobs = [];

        $this->DBs->SOCOM_UI->trans_begin();
        foreach (array_keys($user_edits) as $program_id_year) {
            if (preg_match('/^([a-zA-Z0-9]{128})_([0-9]{4})$/', $program_id_year, $matches) === 1) {
                $program_id = $matches[1];
                $year = $matches[2];

                $edit_jobs[] = [
                    'PROGRAM_ID' => $program_id,
                    'YEAR' => $year
                ];

                $result = $this->DBs->SOCOM_UI
                    ->select('COUNT(*) as COUNT', false)
                    ->from('USR_DT_DIRTY_TABLE_EDIT_JOBS')
                    ->where('PROGRAM_ID', $program_id)
                    ->where('YEAR', $year)
                    ->where('IS_ACTIVE', 1)
                    ->get()->row_array();

                if (($result['COUNT'] ?? 0) > 0 === true) {
                    $this->DBs->SOCOM_UI->trans_rollback();
                    throw new ErrorException('Unable to create edit because different user is currently saving the same PROGRAM_ID and YEAR');
                }
                
                $this->DBs->SOCOM_UI
                    ->set('PROGRAM_ID', $program_id)
                    ->set('YEAR', $year)
                    ->set('USER_ID', $user_id)
                    ->set('IS_ACTIVE', 1)
                    ->insert('USR_DT_DIRTY_TABLE_EDIT_JOBS');
            } else {
                unset($user_edits[$program_id_year]);
                $this->DBs->SOCOM_UI->trans_rollback();
                throw new ErrorException('Invalid program year key sent during edit request');
            }
        }

        if ($this->DBs->SOCOM_UI->trans_status()) {
            $this->DBs->SOCOM_UI->trans_commit();
        }

        return $edit_jobs;
    }

    public function remove_edit_jobs(array $edit_jobs) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        foreach($edit_jobs as $edit_job) {
            $this->DBs->SOCOM_UI
                ->set('IS_ACTIVE', 0)
                ->where('PROGRAM_ID', $edit_job['PROGRAM_ID'])
                ->where('YEAR', $edit_job['YEAR'])
                ->where('USER_ID', $user_id)
                ->update('USR_DT_DIRTY_TABLE_EDIT_JOBS');
        }
    }

    public function save_all_user_changes(string $usr_dt_upload, array $user_edits, UploadType $upload_type, string $edit_start_time) {

        $usr_dt_upload_decoded = $this->decode_usr_dt_upload($usr_dt_upload, 'decode');

        $result = [
            'status' => true,
            'message' => ''
        ];

        try {
            $edit_jobs = $this->create_edit_jobs($user_edits);
        } catch(ErrorException $e) {
            $result['status'] = false;

            $result['message'] = $e->getMessage();
        }
        
        if ($result['status'] === true) {
            $recent_edits = $this->check_recent_edits($usr_dt_upload_decoded, $edit_jobs, $edit_start_time);
        }

        if (!empty($recent_edits)) {
            $result['status'] = false;
            $result['recent_edits'] = $recent_edits;
            $result['message'] = 'Recent edits have been made to the same programs and fiscal years, please merge your changes.';
            $this->remove_edit_jobs($edit_jobs);
            $this->DBs->SOCOM_UI->trans_commit();
        }

        if ($result['status'] === true) {
            $this->DBs->SOCOM_UI->trans_begin();

            foreach ($user_edits as $user_edit) {
                $program_id = $user_edit['originalRow']['PROGRAM_ID'];
                $original_row = $user_edit['originalRow'];
                foreach ($user_edit['edits'] as $edits) {
                    $old_value = $edits['oldValue'];
                    $new_value = $edits['newValue'];
                    $column_changed = $edits['columnChanged'];
                    $fiscal_year = $edits['fiscalYear'];
                    try {
                        $save_edit = $this->save_edit($usr_dt_upload_decoded, $program_id, $fiscal_year, $upload_type, $original_row, $column_changed, $old_value, $new_value);
                        
                        $log = vsprintf(
                            '%s %s user table edit result transaction was %s',
                            [get_called_class(), __METHOD__, ($save_edit ? ' true '  : ' false ')]
                        );
                        
                        log_message('error', $log);
                    } catch (ErrorException $e) {
                        $log = $e->getMessage();
                        log_message('error', $log);
                        
                        $result['status'] = false;
                        $result['messages'] = $log;
                        
                        break;
                    } 
                }
            }

            if ($this->DBs->SOCOM_UI->trans_status()) {
                $this->remove_edit_jobs($edit_jobs);
                $this->DBs->SOCOM_UI->trans_commit();
                $result['status'] = true;
                $result['message'] = 'Edits Saved';
            } else {
                $this->DBs->SOCOM_UI->trans_rollback();
                $result['status'] = false;
                $result['message'] = 'Unable to save edits';
            }
        }

        return $result;
    }

    public function check_program_id(string $program_id, string $cap_sponsor) {

        $result = $this->DBs->SOCOM_UI
            ->select('COUNT(*) count')
            ->from('LOOKUP_PROGRAM')
            ->where('ID', $program_id)
            ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor)
            ->get()
            ->row_array()['count'] ?? 0;

        return 1; //$result;
    }

    public function get_history_changes(string $program_id, int $fiscal_year) {
        $result = $this->DBs->SOCOM_UI
            ->select('FIELD_CHANGED')
            ->select('OLD_VALUE')
            ->select('NEW_VALUE')
            ->select('USER_ID')
            ->from($this->get_edit_history_table())
            ->where('PROGRAM_ID', $program_id)
            ->where('FISCAL_YEAR', $fiscal_year)
            ->order_by('UPDATED_DATETIME DESC')
            ->limit(1)
            ->get()
            ->row_array();
        
        if (isset($result['USER_ID'])) {
            $result['USER_EMAIL'] = $this->Login_model->user_info($result['USER_ID'])[0]['email'];
            unset($result['USER_ID']);
        }

        return $result;
    }

    public function get_usr_dt_upload_dirty_row(int $usr_dt_upload, string $program_id, int $fiscal_year, bool $admin = false) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
		$pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;

        $table_name = $this->get_dirty_table_name($usr_dt_upload, $admin);
        $column_headings = $this->get_column_headings($usr_dt_upload);

        foreach ($column_headings as $column_heading) {
            $this->DBs->SOCOM_UI
                ->select($column_heading);
        }

        $this->DBs->SOCOM_UI
            ->from($table_name);

        if ($pom_admin !== true) {
            $this->DBs->SOCOM_UI
                ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
        }

        $this->DBs->SOCOM_UI->where('PROGRAM_ID', $program_id);
        $this->DBs->SOCOM_UI->where('FISCAL_YEAR', $fiscal_year);

        return $this->DBs->SOCOM_UI->get()->row_array();
    }

    public function check_original_row(int $usr_dt_upload, array $original_row, string $program_id, int $fiscal_year, bool $admin = false) {
        $row = $this->get_usr_dt_upload_dirty_row($usr_dt_upload, $program_id, $fiscal_year, $admin);

        if (count($row) !== count($original_row)) {
            throw new ErrorException('Not all columns returned during save request');
        }

        foreach ($row as $column => $data) {
            if (!array_key_exists($column, $original_row)) {
                throw new ErrorException(sprintf(
                    "Missing column in original row %s", 
                    $column
                ));
            } else if ($original_row[$column] !== $data) {
                throw new ErrorException(sprintf(
                    "Value of original row for %s is incorrect. database: %s, browser: %s", 
                    $column, 
                    $data, 
                    $original_row[$column]
                ));
            }
        }

        return $row;
    }

    public function check_usr_dt_upload(string $usr_dt_upload) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
		$pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
        $usr_dt_upload_decoded = $this->decode_usr_dt_upload($usr_dt_upload);

            $this->DBs->SOCOM_UI
            ->select('COUNT(*) count')
            ->from('USR_DT_UPLOADS u')
            ->join('USR_DT_LOOKUP_METADATA m', 'ON m.USR_DT_UPLOAD_ID = u.ID')
            ->where('u.ID', $usr_dt_upload_decoded);
            
        if ($pom_admin !== true) {
            $this->DBs->SOCOM_UI
                ->where('m.CAP_SPONSOR', $cap_sponsor);
        }

        return $this->DBs->SOCOM_UI->get()->row_array()['count'] ?? 0;

    }

    public function decode_usr_dt_upload(string $usr_dt_upload) {
        return encrypted_string($usr_dt_upload, 'decode');
    }

    public function encode_usr_dt_upload(string $usr_dt_upload) {
        return encrypted_string($usr_dt_upload, 'encode');
    }

    public function save_edit(
        int $usr_dt_upload, 
        string $program_id, 
        int $fiscal_year, 
        UploadType $upload_type, 
        array $original_row, 
        string $column_changed, 
        string $old_value, 
        string $new_value
    ) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        try {
            $this->SOCOM_Cap_User_model->is_user();

            $cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;

            $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id);
        } catch(ErrorException $e) {
            throw $e;
        }
        
        if ($pom_admin === false && $this->check_program_id($program_id, $cap_sponsor) <= 0) {
            throw new Error('Program ID does not exist for user');
        }
        
        try {
            $database_row = $this->check_original_row($usr_dt_upload, $original_row, $program_id, $fiscal_year, false);
        } catch(ErrorException $e) {
            throw $e;
        }

        $metadata = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_with_cap_sponsor($usr_dt_upload, $upload_type);

        $result = $this->save_edit_history(
            $usr_dt_upload, 
            $program_id, 
            $fiscal_year, 
            $original_row, 
            $column_changed, 
            $old_value, 
            $new_value, 
            $metadata['REVISION'] ?? 0
        );
        
        if ($result === false) {
            throw new ErrorException("Unable to save history of edit");
        } else {
            $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_HISTORY, $result, $user_id, false);
        }

        $table_name = $metadata['DIRTY_TABLE_NAME'];

        $result = $this->change_field(
            $program_id,
            $fiscal_year,
            $column_changed,
            $database_row,
            $table_name,
            $new_value
        );

        if ($result === true) {
            $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_EDIT, $usr_dt_upload, $user_id, false);
        } else {
            throw new ErrorException('Unable to save editor save request');
        }
        
        $result = $this->set_revision((($metadata['REVISION'] ?? 0)+1), $metadata['USR_DT_TABLE_METADATA_ID'], $user_id);

        if ($result !== true) {
            throw new ErrorException('Unable to update metadata REVISION');
        }

        return $result;
    }

    public function change_field(
        string $program_id, 
        int $fiscal_year, 
        string $column_changed, 
        array $database_row, 
        string $table_name, 
        int|string $new_value
    ) {
        if (!in_array($column_changed, array_keys($database_row), true)) {
            $log = sprintf('Field %s not found in dirty table %s', $column_changed, $table_name);
            throw new ErrorException($log);
        }
        $this->DBs->SOCOM_UI->set($column_changed, $new_value);

        $this->DBs->SOCOM_UI->where('PROGRAM_ID', $program_id);
        $this->DBs->SOCOM_UI->where('FISCAL_YEAR', $fiscal_year);

        $this->DBs->SOCOM_UI->update($table_name);
        return $this->DBs->SOCOM_UI->affected_rows() > 0;
    }

    public function set_revision(int $metadata_revision, int $usr_dt_table_metadata_id, int $user_id) {

        $this->DBs->SOCOM_UI
            ->set('REVISION', $metadata_revision)
            ->set('USER_ID', $user_id)
            ->where('ID', $usr_dt_table_metadata_id)
            ->update('USR_DT_LOOKUP_TABLE_METADATA');
            
        return $this->DBs->SOCOM_UI->affected_rows() > 0;
    }

    public function get_edit_history_table() {
        $pom = $this->SOCOM_Dynamic_Year_model->getCurrentPom();

        return sprintf('USR_DT_EDIT_HISTORY_TEMPLATE_%s', $pom['POM_YEAR'] ?? 0);
    }

    public function save_edit_history(int $dt_upload_id, string $program_id, int $fiscal_year, array $original_row, string $column_changed, string $old_value, string $new_value, int $revision) {
        $history_data = [
            'USR_DT_UPLOAD_ID' => $dt_upload_id,
            'USER_ID' => (int) $this->session->userdata("logged_in")["id"],
            'FIELD_CHANGED' => $column_changed,
            'ORIGINAL_ROW' => json_encode($original_row),
            'OLD_VALUE' => json_encode($old_value),
            'NEW_VALUE' => json_encode($new_value),
            'PROGRAM_ID' => $program_id,
            'FISCAL_YEAR' => $fiscal_year,
            'REVISION' => $revision
        ];
        $table_name = $this->get_edit_history_table();
        $result = $this->DBs->SOCOM_UI->insert($table_name, $history_data);

        return $result === true && $this->DBs->SOCOM_UI->affected_rows() > 0 ? $this->DBs->SOCOM_UI->insert_id() : false;
    }

    public function get_column_headings() {
        return [
            'PROGRAM_ID',
            'EVENT_NAME',
            'EVENT_NUMBER',
            'EVENT_TYPE',
            'EVENT_TITLE',
            'EVENT_JUSTIFICATION',
            'PROGRAM_GROUP',
            'PROGRAM_CODE',
            'EOC_CODE',
            'CAPABILITY_SPONSOR_CODE',
            'POM_SPONSOR_CODE',
            'EXECUTION_MANAGER_CODE',
            'ASSESSMENT_AREA_CODE',
            'OSD_PROGRAM_ELEMENT_CODE',
            'RESOURCE_CATEGORY_CODE',
            'BUDGET_ACTIVITY_CODE',
            'BUDGET_SUB_ACTIVITY_CODE',
            'LINE_ITEM_CODE',
            'SPECIAL_PROJECT_CODE',
            'RDTE_PROJECT_CODE',
            'SUB_ACTIVITY_GROUP_CODE',
            'FISCAL_YEAR',
            'RESOURCE_K',
            'PROP_AMT',
            'DELTA_AMT'
        ];
    }

    public function get_dt_table_total_count(int|string $usr_dt_upload, bool $admin = false){
        if (is_string($usr_dt_upload)) {
            $usr_dt_upload = encrypted_string($usr_dt_upload, 'decode');
        }
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;

        $table_name = $this->get_dirty_table_name($usr_dt_upload, $admin);

        $result = $this->DBs->SOCOM_UI
                ->select('count(*) AS total', false)
                ->from($table_name);
        if ($pom_admin !== true) {
			$this->DBs->SOCOM_UI
				->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
		}
        $result = $this->DBs->SOCOM_UI->get()->row();
        return $result ? (int)$result->total : 0;
    }

    public function get_dirty_table_name(int|string $usr_dt_upload, bool $admin) {
        static $dirty_table_name = null;
        if ($dirty_table_name === null) {
            $dirty_table_name = $this->DBs->SOCOM_UI
                    ->select('DIRTY_TABLE_NAME');
            if ($admin === true) {
                $this->DBs->SOCOM_UI
                    ->from('USR_DT_LOOKUP_TABLE_METADATA u');
            } else {
                $this->DBs->SOCOM_UI
                    ->from('USR_DT_UPLOADS u')
                    ->join('USR_DT_LOOKUP_METADATA m', 'u.ID = m.USR_DT_UPLOAD_ID');
            }

            $dirty_table_name = $this->DBs->SOCOM_UI
                    ->where('u.ID', $usr_dt_upload)
                    ->get()->row_array()['DIRTY_TABLE_NAME'] ?? null;
        }

        return $dirty_table_name;
    }

    public function get_dt_table_data(int|string $usr_dt_upload, int $page = 0, bool $admin = false) {
        if (is_string($usr_dt_upload)) {
            $usr_dt_upload = encrypted_string($usr_dt_upload, 'decode');
        }
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
        $limit = 100;

        $table_name = $this->get_dirty_table_name($usr_dt_upload, $admin);
        $get_column_headings = $this->get_column_headings();

        foreach($get_column_headings as $field){
            $this->DBs->SOCOM_UI->select($field);
        }

        $result = $this->DBs->SOCOM_UI
            ->from($table_name);
            if ($pom_admin !== true) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
            }

            $this->DBs->SOCOM_UI
                ->limit($limit)
                ->offset($page*$limit);
        
        $result = $this->DBs->SOCOM_UI->get()->result_array();

        return $result;
    }

    public function search_dt_table_data(int|string $usr_dt_upload, string $column, string $searchTerm, bool $admin = false) {
        if (is_string($usr_dt_upload)) {
            $usr_dt_upload = encrypted_string($usr_dt_upload, 'decode');
        }
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
        $cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
        
        $table_name = $this->get_dirty_table_name($usr_dt_upload, $admin);

        $get_column_headings = $this->get_column_headings($usr_dt_upload);

        foreach ($get_column_headings as $field) {
            $this->DBs->SOCOM_UI->select($field);
        }

        $this->DBs->SOCOM_UI->from($table_name);
        if ($pom_admin !== true) {
            $this->DBs->SOCOM_UI->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
        }

        if(is_numeric($searchTerm)) {
            $this->DBs->SOCOM_UI->where($column, $searchTerm);
        } else {
            $this->DBs->SOCOM_UI->like($column, $searchTerm, 'both');
        }
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_searchable_columns() {
        return [
            'EVENT_NAME',
            'EVENT_NUMBER',
            'EVENT_TYPE',
            'EVENT_TITLE',
            'EVENT_JUSTIFICATION',
            'PROGRAM_GROUP',
            'PROGRAM_CODE',
            'EOC_CODE',
            'CAPABILITY_SPONSOR_CODE',
            'POM_SPONSOR_CODE',
            'EXECUTION_MANAGER_CODE',
            'ASSESSMENT_AREA_CODE',
            'OSD_PROGRAM_ELEMENT_CODE',
            'RESOURCE_CATEGORY_CODE',
            'FISCAL_YEAR'
        ];
    }
}
