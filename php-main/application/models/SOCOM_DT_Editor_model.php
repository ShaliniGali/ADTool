<?php

class SOCOM_DT_Editor_model extends CI_Model {

    public function get_edit_table() {
        $pom = $this->SOCOM_Dynamic_Year_model->getCurrentPom();

        return sprintf('USR_DT_DIRTY_TABLE_EDIT_JOBS_%s', $pom['POM_YEAR'] ?? 0);
    }

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
        $table_name = $this->get_edit_table();

        foreach($edit_jobs as $i => $edit_job) {           
                $result = $this->DBs->SOCOM_UI
                    ->select('COUNT(*) as COUNT', false)
                    ->from($table_name)
                    ->where('USR_DT_UPLOADS_ID', $edit_job['USR_DT_UPLOADS_ID'])
                    ->where('PROGRAM_ID', $edit_job['PROGRAM_ID'])
                    ->where('YEAR', $edit_job['YEAR'])
                    ->where('IS_ACTIVE', 0)
                    ->where('UPDATED_DATETIME >=', $start_time, false)
                    ->where('UPDATED_DATETIME <=', 'CURRENT_TIMESTAMP()', false)
                    ->get()->row_array();

                if ($result['COUNT'] ?? 0 > 0) {
                    $recent_edits[$i] = [
                        'PROGRAM_ID' => $edit_job['PROGRAM_ID'],
                        'YEAR' => $edit_job['YEAR'],
                        'EDITED_ROW' => $this->get_usr_dt_upload_dirty_row($usr_dt_upload, $edit_job['USR_DT_UPLOADS_ID'], $edit_job['PROGRAM_ID'], $edit_job['YEAR'], $start_time, false),
                    ];
                    foreach ($edit_job['FIELDS_CHANGED'] as $field_changed) { 
                        $recent_edits[$i]['CHANGES'][] = $this->get_history_changes($edit_job['USR_DT_UPLOADS_ID'], $edit_job['PROGRAM_ID'], $edit_job['YEAR'], $field_changed, $start_time);
                    }
                }
        }

        return $recent_edits;
    }

    public function create_edit_jobs(int $usr_dt_upload, array &$user_edits) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $edit_jobs = [];
        $table_name = $this->get_edit_table();

        $this->DBs->SOCOM_UI->trans_begin();
        foreach (array_keys($user_edits) as $program_id_year) {
            if (preg_match('/^([a-zA-Z0-9]+)_([a-zA-Z0-9]{128})_([0-9]{4})$/', $program_id_year, $matches) === 1) {
                $usr_dt_upload_edit = encrypted_string($matches[1], 'decode');
                $program_id = $matches[2];
                $year = $matches[3];
                
                $edit_jobs[$program_id_year] = [
                    'USR_DT_UPLOADS_ID' => $usr_dt_upload_edit,
                    'PROGRAM_ID' => $program_id,
                    'YEAR' => $year,
                    'FIELDS_CHANGED' => array_keys($user_edits[$program_id_year]['edits'])
                ];

                $result = $this->DBs->SOCOM_UI
                    ->select('COUNT(*) as COUNT', false)
                    ->from($table_name)
                    ->where('USR_DT_UPLOADS_ID', $usr_dt_upload_edit)
                    ->where('PROGRAM_ID', $program_id)
                    ->where('YEAR', $year)
                    ->where('IS_ACTIVE', 1)
                    ->get()->row_array();

                if (($result['COUNT'] ?? 0) > 0 === true) {
                    $this->DBs->SOCOM_UI->trans_rollback();
                    throw new ErrorException('Unable to create edit because different user is currently saving the same PROGRAM_ID and YEAR');
                }
                
                $this->DBs->SOCOM_UI
                    ->set('USR_DT_UPLOADS_ID', $usr_dt_upload_edit)
                    ->set('PROGRAM_ID', $program_id)
                    ->set('YEAR', $year)
                    ->set('USER_ID', $user_id)
                    ->set('IS_ACTIVE', 1)
                    ->insert($table_name);
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
        $table_name = $this->get_edit_table();

        foreach($edit_jobs as $edit_job) {
            $this->DBs->SOCOM_UI
                ->set('IS_ACTIVE', 0)
                ->where('PROGRAM_ID', $edit_job['PROGRAM_ID'])
                ->where('YEAR', $edit_job['YEAR'])
                ->where('USER_ID', $user_id)
                ->update($table_name);
        }
    }

    public function save_all_user_changes(string $usr_dt_upload, array $user_edits, UploadType $upload_type, string $edit_start_time, bool $overwrite = false) {

        $usr_dt_upload_decoded = $this->decode_usr_dt_upload($usr_dt_upload, 'decode');

        $result = [
            'status' => true,
            'message' => ''
        ];

        try {
            $edit_jobs = $this->create_edit_jobs($usr_dt_upload_decoded, $user_edits);
        } catch(ErrorException $e) {
            $result['status'] = false;

            $result['message'] = $e->getMessage();

            $edit_jobs = [];
        }
        
        if ($result['status'] === true && $overwrite === false) {
            $recent_edits = $this->check_recent_edits($usr_dt_upload_decoded, $edit_jobs, $edit_start_time);
        }

        if (!empty($recent_edits)) {
            $result['status'] = false;
            $result['recent_edits'] = $recent_edits;
            $result['message'] = 'Recent edits have been made to the same programs and fiscal years, please merge your changes.';
            #$this->DBs->SOCOM_UI->trans_commit();
        }

        if ($result['status'] === true) {
            $user_id = (int)$this->session->userdata("logged_in")["id"];

            $this->DBs->SOCOM_UI->trans_begin();

            $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_SAVE_START, $usr_dt_upload_decoded, $user_id, false);


            foreach ($user_edits as $user_edit) {
                $program_id = $user_edit['originalRow']['PROGRAM_ID'];
                $user_edit['originalRow']['USR_DT_UPLOADS_ID'] = $usr_dt_uploads_id = $this->decode_usr_dt_upload($user_edit['originalRow']['USR_DT_UPLOADS_ID'], 'decode');
                $original_row = $user_edit['originalRow'];
                // quick fix 1.6.1-rc2
                unset($original_row['IS_APPROVE']);
                $fiscal_year =  $user_edit['originalRow']['FISCAL_YEAR'];
                
                try {
                    $database_row = $this->check_original_row($usr_dt_upload_decoded, $usr_dt_uploads_id, $original_row, $program_id, $fiscal_year, false, $overwrite);
                } catch(ErrorException $e) {
                    $log = $e->getMessage();
                    log_message('error', $log);
                    
                    $result['status'] = false;
                    $result['messages'] = $log;

                    break;
                }
                
                foreach ($user_edit['edits'] as $edits) {
                    $old_value = $edits['OLD_VALUE'];
                    $new_value = $edits['NEW_VALUE'];
                    $column_changed = $edits['FIELD_CHANGED'];
                    $fiscal_year = $edits['FISCAL_YEAR'];
                    try {
                        $save_edit = $this->save_edit($usr_dt_upload_decoded, $usr_dt_uploads_id, $program_id, $fiscal_year, $upload_type, $original_row, $column_changed, $old_value, $new_value, $database_row);
                        
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

            $this->SOCOM_Git_Data_model->git_track_data(GitDataType::USER_DATA_SAVE_END, $usr_dt_upload_decoded, $user_id, false);

            if ($result['status'] === true && $this->DBs->SOCOM_UI->trans_status()) {
                $this->DBs->SOCOM_UI->trans_commit();
                $result['status'] = true;
                $result['message'] = 'Edits Saved';
            } else {
                $this->DBs->SOCOM_UI->trans_rollback();
                $result['status'] = false;
                $result['message'] = 'Unable to save edits';
            }
        }

        $this->remove_edit_jobs((array)$edit_jobs);

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

    public function get_history_changes(int $usr_dt_upload, string $program_id, int $fiscal_year, string $field_changed, string $start_time) {
        $result = $this->DBs->SOCOM_UI
            ->select('FIELD_CHANGED')
            ->select('OLD_VALUE')
            ->select('NEW_VALUE')
            ->select('USER_ID')
            ->from($this->get_edit_history_table())
            ->where('USR_DT_UPLOADS_ID', $usr_dt_upload)
            ->where('PROGRAM_ID', $program_id)
            ->where('FISCAL_YEAR', $fiscal_year)
            ->where('FIELD_CHANGED', $field_changed)
            ->where('UPDATED_DATETIME >=', $start_time, false)
            ->where('UPDATED_DATETIME <=', 'CURRENT_TIMESTAMP()', false)
            ->order_by('UPDATED_DATETIME DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (isset($result['USER_ID'])) {
            $result['USER_EMAIL'] = $this->Login_model->user_info($result['USER_ID'])[0]['email'];
            unset($result['USER_ID']);
        }

        $result['OLD_VALUE'] = json_decode($result['OLD_VALUE']);
        $result['NEW_VALUE'] = json_decode($result['NEW_VALUE']);
        
        return $result;
    }

    public function get_usr_dt_upload_dirty_row(int $usr_dt_upload_decoded, int $usr_dt_upload, string $program_id, int $fiscal_year, bool $view = false) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
		$pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;

        $table_name = $this->get_dirty_table_name($usr_dt_upload_decoded, $view);
        $column_headings = $this->get_column_headings();

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

        $this->DBs->SOCOM_UI
            ->where('USR_DT_UPLOADS_ID', $usr_dt_upload)
            ->where('PROGRAM_ID', $program_id)
            ->where('FISCAL_YEAR', $fiscal_year);

        $result = $this->DBs->SOCOM_UI->get()->row_array();
        
        return $result;
    }

    public function check_original_row(int $usr_dt_upload_decoded, int $usr_dt_upload, array $original_row, string $program_id, int $fiscal_year, bool $view = false, bool $overwrite = false) {
        $row = $this->get_usr_dt_upload_dirty_row($usr_dt_upload_decoded, $usr_dt_upload, $program_id, $fiscal_year, $view);

        if ($row === null || count($row) !== count($original_row)) {
            throw new ErrorException('Not all columns returned during save request');
        }

        foreach ($row as $column => $data) {
            if (!array_key_exists($column, $original_row)) {
                throw new ErrorException(sprintf(
                    "Missing column in original row %s", 
                    $column
                ));
            } else if ($overwrite === false && $original_row[$column] !== $data) {
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

    public function check_usr_dt_upload(string $usr_dt_upload, bool $view = false) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
		$pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
        $usr_dt_upload_decoded = $this->decode_usr_dt_upload($usr_dt_upload);

        $this->DBs->SOCOM_UI
            ->select('COUNT(*) count')
            ->from('USR_DT_UPLOADS u')
            ->join('USR_DT_UPLOADS_METADATA m', 'ON m.USR_DT_UPLOADS_ID = u.ID');

        if ($pom_admin === true) {
            $this->DBs->SOCOM_UI
                ->select('tm.ID')
                ->join('USR_DT_LOOKUP_TABLE_METADATA tm', 'ON m.USR_DT_TABLE_METADATA_ID = tm.ID')
                ->where('tm.ID', $usr_dt_upload_decoded)
                ->group_by('tm.ID');
        } else {
            $this->DBs->SOCOM_UI
                ->select('u.ID')
                ->where('m.CAP_SPONSOR', $cap_sponsor)
                ->where('u.ID', $usr_dt_upload_decoded);
        }

        return $this->DBs->SOCOM_UI->get()->row_array();

    }

    public function decode_usr_dt_upload(string $usr_dt_upload) {
        return encrypted_string($usr_dt_upload, 'decode');
    }

    public function encode_usr_dt_upload(string $usr_dt_upload) {
        return encrypted_string($usr_dt_upload, 'encode');
    }

    public function save_edit(
        int $usr_dt_upload_decoded, 
        int $usr_dt_upload, 
        string $program_id, 
        int $fiscal_year, 
        UploadType $upload_type, 
        array $original_row, 
        string $column_changed, 
        string|null $old_value, 
        string $new_value,
        array $database_row
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
            $usr_dt_upload,
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
        int $usr_dt_upload,
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

        $this->DBs->SOCOM_UI->where('USR_DT_UPLOADS_ID', $usr_dt_upload);
        $this->DBs->SOCOM_UI->where('PROGRAM_ID', $program_id);
        $this->DBs->SOCOM_UI->where('FISCAL_YEAR', $fiscal_year);

        return $this->DBs->SOCOM_UI->update($table_name);
        //return $this->DBs->SOCOM_UI->affected_rows() > 0;
    }

    public function set_revision(int $metadata_revision, int $usr_dt_table_metadata_id, int $user_id) {

        $result = $this->DBs->SOCOM_UI
            ->set('REVISION', $metadata_revision)
            ->set('USER_ID', $user_id)
            ->where('ID', $usr_dt_table_metadata_id)
            ->update('USR_DT_LOOKUP_TABLE_METADATA');
            
        return $result;
    }

    public function get_edit_history_table() {
        $pom = $this->SOCOM_Dynamic_Year_model->getCurrentPom();

        return sprintf('USR_DT_EDIT_HISTORY_TEMPLATE_%s', $pom['POM_YEAR'] ?? 0);
    }

    public function save_edit_history(int $dt_upload_id, string $program_id, int $fiscal_year, array $original_row, string $column_changed, string|null $old_value, string $new_value, int $revision) {
        $history_data = [
            'USR_DT_UPLOADS_ID' => $dt_upload_id,
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
            'ID',
            'USR_DT_UPLOADS_ID',
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
            'DELTA_AMT',
            'SUBMISSION_STATUS'
        ];
    }

    public function get_dt_table_total_count(int|string $usr_dt_upload, bool $view = false){
        if (is_string($usr_dt_upload)) {
            $usr_dt_upload = encrypted_string($usr_dt_upload, 'decode');
        }
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;

        $table_name = $this->get_dirty_table_name($usr_dt_upload, $view);

        $result = $this->DBs->SOCOM_UI
                ->select('count(*) AS total', false)
                ->from($table_name);

        if ($pom_admin !== true) {
            if ($view !== true) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor)
                    ->where('SUBMISSION_STATUS', 'PENDING');
            }

            if ($view === false) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
            }

            $this->DBs->SOCOM_UI
                ->where('USR_DT_UPLOADS_ID', $usr_dt_upload);
        }

        $result = $this->DBs->SOCOM_UI->get()->row();
        return $result ? (int)$result->total : 0;
    }

    public function get_dirty_table_name(int|string $usr_dt_upload, bool $view) {
        static $dirty_table_name = null;
        if ($dirty_table_name === null) {
            $user_id = (int)$this->session->userdata("logged_in")["id"];
            $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
            $cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
            
            $dirty_table_name = $this->DBs->SOCOM_UI
                    ->select('DIRTY_TABLE_NAME');
            if ($pom_admin) {
                $this->DBs->SOCOM_UI
                    ->from('USR_DT_LOOKUP_TABLE_METADATA u')
                    ->where('u.ID', $usr_dt_upload);
            } else {
                $this->DBs->SOCOM_UI
                    ->from('USR_DT_UPLOADS ud')
                    ->join('USR_DT_UPLOADS_METADATA m', 'ud.ID = m.USR_DT_UPLOADS_ID')
                    ->join('USR_DT_LOOKUP_TABLE_METADATA u', 'm.USR_DT_TABLE_METADATA_ID = u.ID')
                    ->where('ud.ID', $usr_dt_upload)
                    ->group_by('u.ID');
            }

            $dirty_table_name = $this->DBs->SOCOM_UI
                    ->get()->row_array()['DIRTY_TABLE_NAME'] ?? null;

        }

        return $dirty_table_name;
    }

    public function get_dt_table_data(int|string $usr_dt_upload, int $page = 0, bool $view = false) {
        if (is_string($usr_dt_upload)) {
            $usr_dt_upload = encrypted_string($usr_dt_upload, 'decode');
        }
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
		$cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
        $limit = 100;

        $table_name = $this->get_dirty_table_name($usr_dt_upload, $view);
        $get_column_headings = $this->get_column_headings();

        foreach($get_column_headings as $field){
            $this->DBs->SOCOM_UI->select($field);
        }

        $result = $this->DBs->SOCOM_UI
            ->from($table_name);

        if ($pom_admin !== true) {
            if ($view !== true) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor)
                    ->where('SUBMISSION_STATUS', 'PENDING');
            }

            if ($view === false) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
            }

            $this->DBs->SOCOM_UI
                ->where('USR_DT_UPLOADS_ID', $usr_dt_upload);
        }

        $this->DBs->SOCOM_UI
            ->order_by('ID', 'ASC')
            ->limit($limit)
            ->offset($page * $limit);
        
        $result = $this->DBs->SOCOM_UI->get()->result_array();

        return $result;
    }

    public function search_dt_table_data(int|string $usr_dt_upload, string $column, string $searchTerm, bool $view = false) {
        if (is_string($usr_dt_upload)) {
            $usr_dt_upload = encrypted_string($usr_dt_upload, 'decode');
        }
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $pom_admin = $this->SOCOM_Cap_User_model->is_role_admin($user_id) ?? false;
        $cap_sponsor = $this->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
        
        $table_name = $this->get_dirty_table_name($usr_dt_upload, $view);

        $get_column_headings = $this->get_column_headings($usr_dt_upload);

        foreach ($get_column_headings as $field) {
            $this->DBs->SOCOM_UI->select($field);
        }

        $this->DBs->SOCOM_UI->from($table_name);
        

        if ($pom_admin !== true) {
            if ($view !== true) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor)
                    ->where('SUBMISSION_STATUS', 'PENDING');
            }

            if ($view === false) {
                $this->DBs->SOCOM_UI
                    ->where('CAPABILITY_SPONSOR_CODE', $cap_sponsor);
            }

            $this->DBs->SOCOM_UI
                ->where('USR_DT_UPLOADS_ID', $usr_dt_upload);
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

    public function get_editor_historical_data($usr_dt_upload) {

        $history_table = $this->get_edit_history_table();
        $cycle_id = get_cycle_id();
        
        $git_type = [
            'UPLOAD_FILE', 'PROCESS_FILE', 'CANCEL_FILE', 'DELETE_FILE', 'CREATE_METADATA',
            'USER_DATA_OPEN', 'USER_DATA_SEARCH', 'USER_DATA_CLOSE', 'USER_DATA_SAVE_START',
            'USER_DATA_FINAL_SUBMISSION', 'ADMIN_APPROVAL'
        ];
        
        $select_fields = "
            g_start.ID               AS GIT_DATA_ID,
            g_start.TYPE             AS GIT_TYPE,
            g_start.USER_ID          AS GIT_USER_ID,
            g_start.CREATED_DATETIME AS GIT_CREATED_DATETIME,
            g_start.UPDATED_DATETIME AS GIT_UPDATED_DATETIME,
            upload.ID                AS UPLOAD_ID,
            upload.TITLE,
            upload.FILE_NAME,
            upload.USER_ID           AS UPLOAD_USER_ID,
            upload.CREATED_TIMESTAMP AS UPLOAD_CREATED_TIMESTAMP,
            edit_summary.NUM_EDITS,
            edit_summary.EDIT_DETAILS,
            edit_summary.LAST_EDITED_BY,
            edit_summary.MAX_REVISION
        ";


        // Subquery sr — mapping each save start to its matching save end
        $subquerySr = "(SELECT 
        g_start.ID AS START_ID,
        g_start.USER_ID,
        gmap.MAP_ID AS UPLOAD_ID,
        g_start.CREATED_DATETIME AS START_TIME,
        (
            SELECT ge.CREATED_DATETIME
            FROM USR_DT_GIT_DATA ge
            WHERE ge.USER_ID = g_start.USER_ID
            AND ge.TYPE = 'USER_DATA_SAVE_END'
            AND ge.CREATED_DATETIME >= g_start.CREATED_DATETIME
            ORDER BY ge.CREATED_DATETIME ASC, ge.ID ASC
            LIMIT 1
        ) AS END_TIME
        FROM USR_DT_GIT_DATA g_start
        JOIN USR_DT_GIT_MAP gmap ON g_start.ID = gmap.USR_DT_GIT_DATA_ID
        WHERE g_start.TYPE = 'USER_DATA_SAVE_START') sr";

        // Subquery edit_summary — aggregate edits per upload & session start time
        $subqueryEditSummary = "(SELECT
        eh.USR_DT_UPLOADS_ID,
        sr.START_TIME,
        COUNT(*) AS NUM_EDITS,
        MAX(eh.REVISION) AS MAX_REVISION,
        MAX(eh.USER_ID) AS LAST_EDITED_BY,
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'FIELD_CHANGED', eh.FIELD_CHANGED,
                'OLD_VALUE', eh.OLD_VALUE,
                'NEW_VALUE', eh.NEW_VALUE,
                'USER_ID', eh.USER_ID,
                'FISCAL_YEAR', eh.FISCAL_YEAR
            )
        ) AS EDIT_DETAILS
        FROM {$history_table} eh
        JOIN {$subquerySr} ON eh.USR_DT_UPLOADS_ID = sr.UPLOAD_ID
        AND eh.CREATED_DATETIME >= sr.START_TIME
        AND (sr.END_TIME IS NULL OR eh.CREATED_DATETIME <= sr.END_TIME)
        GROUP BY eh.USR_DT_UPLOADS_ID, sr.START_TIME) edit_summary";
                
        $this->DBs->SOCOM_UI
            ->select($select_fields)
            ->from('USR_DT_GIT_DATA g_start')
            ->join('USR_DT_GIT_MAP gmap', 'g_start.ID = gmap.USR_DT_GIT_DATA_ID')
            ->join('USR_DT_UPLOADS upload', 'gmap.MAP_ID = upload.ID')
            ->join(
                $subqueryEditSummary,
                'edit_summary.USR_DT_UPLOADS_ID = upload.ID AND edit_summary.START_TIME = g_start.CREATED_DATETIME', 
                'left'
            )
            ->where_in('g_start.TYPE', $git_type)
            ->where_in('upload.CYCLE_ID', $cycle_id)
            ->where('upload.ID', $usr_dt_upload)
            ->order_by('g_start.ID');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function save_approve($usr_dt_upload, $rows_grouped_by_file) {
        foreach ($rows_grouped_by_file as $file_id => $row_ids) {

            $upload_metadata = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_with_cap_sponsor(
                $file_id, 
                UploadType::DT_UPLOAD_EXTRACT_UPLOAD
            );
            
            $position = '';
            if (str_contains($upload_metadata['DIRTY_TABLE_NAME'], 'ISS')) {
                $position = 'iss';
            } elseif (str_contains($upload_metadata['DIRTY_TABLE_NAME'], 'ZBT')) {
                $position = 'zbt';
            }

            $submission_status = 'APPROVED';
            $res = php_api_call(
                'PATCH',
                'Content-Type: ' . APPLICATION_JSON,
                json_encode($row_ids, true),
                RHOMBUS_PYTHON_URL."/socom/dirty-table/{$position}/status?new_status=" . $submission_status
            );

            $result = json_decode($res, true);
            if (isset($result['detail'])) {
                return ['status' => 'error', 'message' => $result['detail']];
            }
        }

        return ['status'=> 'success', 'message' => 'Rows approved successfully'];
    }
}
