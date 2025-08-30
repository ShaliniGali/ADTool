<?php

#[AllowDynamicProperties]
class  SOCOM_AOAD_model extends CI_Model {
    
    public function get_table($type, $ao_ad_status) {
        if ($type === 'zbt_summary') {
            if ($ao_ad_status === 'ao') {
                $table = 'USR_ZBT_AO_SAVES';
                $table_history = 'USR_ZBT_AO_SAVES_HISTORY';
            } elseif ($ao_ad_status === 'ad') {
                $table = 'USR_ZBT_AD_SAVES';
                $table_history = 'USR_ZBT_AD_SAVES_HISTORY';
            } elseif ($ao_ad_status === 'final_ad') {
                $table = 'USR_ZBT_AD_FINAL_SAVES';
                $table_history = 'USR_ZBT_AD_FINAL_SAVES_HISTORY';
            }
        } elseif ($type === 'issue') {
            if ($ao_ad_status === 'ao') {
                $table = 'USR_ISSUE_AO_SAVES';
                $table_history = 'USR_ISSUE_AO_SAVES_HISTORY';
            } elseif ($ao_ad_status === 'ad') {
                $table = 'USR_ISSUE_AD_SAVES';
                $table_history = 'USR_ISSUE_AD_SAVES_HISTORY';
            } elseif ($ao_ad_status === 'final_ad') {
                $table = 'USR_ISSUE_AD_FINAL_SAVES';
                $table_history = 'USR_ISSUE_AD_FINAL_SAVES_HISTORY';
            }
        }

        return $table ? [$table, $table_history] : [false, false];
    }

    public function get_ao_by_event_id_user_id($event_id, $type, $user_id = null, $is_deleted = 3) {
        $ao_ad_status = 'ao';
        $pom_id = get_pom_id();
        [$table] = $this->get_table($type, $ao_ad_status);
        if ($table === false) {
            log_message('error', 'Incorrect type sent when trying to save AOAD');
            throw new ErrorException("AOAD Type not found");
        }

        if (is_int($is_deleted) && ($is_deleted >= 0 || $is_deleted <= 3)) {
            $ids = [$is_deleted];
            if ($is_deleted === 1 || $is_deleted === 2) {
                $ids[] = 3;
            }
        }
    
        $user_id_can =  (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('IF(IS_DELETED != 2, AO_RECOMENDATION, NULL) as AO_RECOMENDATION', false)
            ->select('IF(IS_DELETED != 1, AO_COMMENT, NULL) as AO_COMMENT', false)
            ->select('IS_DELETED')
            ->select('AO_USER_ID')
            ->select('IF('.(int)$user_id_can.' = AO_USER_ID, 1, 0) as SHOW_CAN', false)
            ->from($table)
            ->where('EVENT_ID', $event_id)
            ->where('POM_ID', $pom_id);
    
        if (!empty($ids)) {
            $this->DBs->SOCOM_UI
                ->where_not_in('IS_DELETED', $ids);
        }

        // If $user_id is provided, retrieve single row (for user specific recommendation) 
        if ($user_id !== null) {
            $result = $this->DBs->SOCOM_UI->where('AO_USER_ID', $user_id)->get()->row_array();
        
            if ($result) {
                $result['AO_RECOMENDATION'] = $this->capitalize_recommendation($result['AO_RECOMENDATION']);
            }
            
            return $result ?? false;
        } else {
            // If no $user_id is provided, retrieve all rows (for all comments across all users) 
            $results = $this->DBs->SOCOM_UI->get()->result_array();

            foreach ($results as &$result) {
                $result['AO_RECOMENDATION'] = $this->capitalize_recommendation($result['AO_RECOMENDATION']);
            }
            
            return $results;
        }
    }

    public function get_ad_by_event_id_user_id($event_id, $type, $user_id = null, $is_deleted = 3, string $ao_ad_status = 'ad') {
        if ($ao_ad_status !== 'final_ad') {
            $ao_ad_status = 'ad';
        }
        $pom_id = get_pom_id();
        [$table] = $this->get_table($type, $ao_ad_status);
        if ($table === false) {
            log_message('error', 'Incorrect type sent when trying to save AOAD');
            throw new ErrorException("AOAD Type not found");
        }

        if (is_int($is_deleted) && ($is_deleted >= 0 || $is_deleted <= 3)) {
            $ids = [$is_deleted];
            if ($is_deleted === 1 || $is_deleted === 2) {
                $ids[] = 3;
            }
        }
        
        $user_id_can =  (int)$this->session->userdata("logged_in")["id"];
        if ($ao_ad_status !== 'final_ad') {
            $this->DBs->SOCOM_UI->select('IF(IS_DELETED != 1, AD_COMMENT, NULL) as AD_COMMENT', false)
                ->where('EVENT_ID', $event_id);
        } else {
            $this->DBs->SOCOM_UI->where('EVENT_NAME', $event_id);
        }

        $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('IF(IS_DELETED != 2, AD_RECOMENDATION, NULL) as AD_RECOMENDATION', false)
            ->select('AD_USER_ID')
            ->select('IS_DELETED')
            ->select('IF('.(int)$user_id_can.' = AD_USER_ID, 1, 0) as SHOW_CAN', false)
            ->from($table)
            ->where('POM_ID', $pom_id);
            
        if (!empty($ids)) {
            $this->DBs->SOCOM_UI
                ->where_not_in('IS_DELETED', $ids);
        } else if ($ao_ad_status === 'final_ad') {
                $this->DBs->SOCOM_UI
                    ->where('((IF(IS_DELETED != 2, AD_RECOMENDATION, NULL) IS NOT NULL))');
        }

        // If $user_id is provided, retrieve single row (for user specific recommendation) 
        if ($ao_ad_status === 'final_ad' || $user_id !== null) {
            if ($ao_ad_status === 'final_ad') {
                $result = $this->DBs->SOCOM_UI->get()->row_array();
            } else {
                $result = $this->DBs->SOCOM_UI->where('AD_USER_ID', $user_id)->get()->row_array();
            }
            
            if ($result) {
                $result['AD_RECOMENDATION'] = $this->capitalize_recommendation($result['AD_RECOMENDATION']);
            }
            
            return $result ?? false;
        } else {
            // If no $user_id is provided, retrieve all rows (for all comments across all users) 
            $results = $this->DBs->SOCOM_UI->get()->result_array();

            foreach ($results as &$result) {
                $result['AD_RECOMENDATION'] = $this->capitalize_recommendation($result['AD_RECOMENDATION']);
            }
            
            return $results;
        }
    }

    public function get_final_ad_by_event_id_user_id(string $event_id, string $type, int|null $user_id = null, bool $is_deleted = true) {
        $user_id_can =  (int)$this->session->userdata("logged_in")["id"];
        $ao_ad_status = 'final_ad';
        $pom_id = get_pom_id();
        
        [$table] = $this->get_table($type, $ao_ad_status);
        if ($table === false) {
            log_message('error', 'Incorrect type sent when trying to save AOAD');
            throw new ErrorException("AOAD Type not found");
        }

        if ($this->is_ad_user()) {
            $this->DBs->SOCOM_UI->select('1 as SHOW_CAN', false);
        } else {
            $this->DBs->SOCOM_UI->select('0 as SHOW_CAN', false);
        }
    
        $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('AD_RECOMENDATION')
            ->select('AD_USER_ID')
            
            ->select('IS_DELETED')
            ->from($table)
            ->where('EVENT_NAME', $event_id)
            ->where('POM_ID', $pom_id);
        
        if ($is_deleted === true) {
            $this->DBs->SOCOM_UI->where('IS_DELETED', 0);
        }
    
        // If $user_id is provided, retrieve single row (for user specific recommendation) 
        if ($user_id !== null) {
            $this->DBs->SOCOM_UI->where('AD_USER_ID', $user_id);
        }

        $result = $this->DBs->SOCOM_UI->get()->row_array();

        if ($result) {
            $result['AD_RECOMENDATION'] = $this->capitalize_recommendation($result['AD_RECOMENDATION']);
        }
            
        return $result ?? false;
    }

    public function capitalize_recommendation($recommendation) {
        $recommendation_map = [
            'approve at scale' => 'Approve at Scale',
            'approve' => 'Approve'
        ];
    
        // Convert the recommendation to lowercase to ensure case-insensitivity
        $lowercase_recommendation = strtolower($recommendation);
    
        // Check if the lowercase recommendation exists in the hashmap
        if (array_key_exists($lowercase_recommendation, $recommendation_map)) {
            return $recommendation_map[$lowercase_recommendation];  // Return the capitalized version
        }
    
        // If not found, return default
        return $recommendation;
    }
    

    public function is_ao_user() {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $group = $this->DBs->SOCOM_UI
            ->select('GROUP')
            ->from('USR_AO_AD_USERS')
            ->where_in('USER_ID', $user_id)
            ->where('IS_DELETED', 0)
            ->get()
            ->row_array()['GROUP'] ?? false;

        return in_array($group, ['AO', 'AO and AD'], true);

    }

    public function is_ad_user() {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $group = $this->DBs->SOCOM_UI
            ->select('GROUP')
            ->from('USR_AO_AD_USERS')
            ->where_in('USER_ID', $user_id)
            ->where('IS_DELETED', 0)
            ->get()
            ->row_array()['GROUP'] ?? false;

        return in_array($group, ['AD', 'AO and AD'], true);
    }

    public function validate_final_ad_input($data) {
        if (
            !array_key_exists('AD_RECOMENDATION', $data)
        ) {
            $log = 'Must be saving AD_RECOMENDATION only';
            log_message('error', $log);
            
            throw new ErrorException($log);
        }
    }

    public function validate_ao_ad_input($data) {
        if (
            !array_key_exists('AO_RECOMENDATION', $data) &&
            !array_key_exists('AO_COMMENT', $data) &&
            !array_key_exists('AD_RECOMENDATION', $data) &&
            !array_key_exists('AD_COMMENT', $data)
        ) {
            $log = 'Must be saving one of AO_RECOMENDATION, AD_RECOMENDATION, AO_COMMENT, AD_COMMENT';
            log_message('error', $log);
            
            throw new ErrorException($log);
        }
    }

    public function validate_ao_ad_field($field) {
        if (
            'AO_RECOMENDATION' !== $field &&
            'AO_COMMENT' !== $field &&
            'AD_RECOMENDATION' !== $field &&
            'AD_COMMENT' !== $field
        ) {
            $log = 'Field must be one of AO_RECOMENDATION, AD_RECOMENDATION, AO_COMMENT, AD_COMMENT';
            log_message('error', $log);
            
            throw new ErrorException($log);
        }
    }

    public function save_ao_ad_data($data, $event_id, $type, $ao_ad_status) {
        try {
            $this->validate_ao_ad_input($data);

            [$table, $table_history] = $this->get_table($type, $ao_ad_status);
            if ($table === false) {
                log_message('error', 'Incorrect type sent when trying to save AOAD');
                throw new ErrorException('AOAD Type not found');
            }

            $user_id = (int)$this->session->userdata("logged_in")["id"];
            
            $field = key($data);
            $field_type = stripos($field, 'COMMENT') !== false ? 'COMMENT' : 'DROPDOWN';
            $value = current($data);
            $aoad = false;
            $pom_id = get_pom_id();

            try {
                list($user_field, $aoad) = $this->get_ao_ad_user_field_plus_aoad($field, $event_id, $type, $user_id, false);
            } catch (ErrorException $e) {
                throw $e;
            }

            if ($aoad === false) {
                // Insert
                $result = $this->DBs->SOCOM_UI
                    ->set($field, $value)
                    ->set('EVENT_ID', $event_id)
                    ->set($user_field, $user_id)
                    ->set('POM_ID', $pom_id)
                    ->insert($table);
            } else {
                $updated_is_deleted = $this->_get_is_deleted_for_update($aoad, $field);

                $this->save_ao_ad_user_history($user_id, $event_id, $table, $table_history);
                $result = $this->DBs->SOCOM_UI
                    ->set($field, $value)
                    ->set($user_field, $user_id)
                    ->set('IS_DELETED', $updated_is_deleted)
                    ->where('EVENT_ID', $event_id)
                    ->where('POM_ID', $pom_id)
                    ->where($user_field, $user_id)
                    ->update($table);
                
                log_message('debug', 'AO AD User table update result was '.$result ? ' true '  : ' false ');
            }
            
            return $result;
        } catch (ErrorException $e) {
            log_message('debug', sprintf('AO AD User table update/insert result was %s', $e->getMessage()));
            throw $e;
        }
    }

    protected function _get_is_deleted_for_update($aoad, $field) {
        if ($aoad['IS_DELETED'] === 3) {
            if (substr($field, 2) === '_COMMENT') {
                $updated_is_deleted = 2;
            } else if (substr($field, 2) === '_RECOMENDATION') {
                $updated_is_deleted = 1;
            }
        } else if ($aoad['IS_DELETED'] === 1 && substr($field, 2) === '_COMMENT') {
            $updated_is_deleted = 0;
        } else if ($aoad['IS_DELETED'] === 2 && substr($field, 2) === '_RECOMENDATION') {
            $updated_is_deleted = 0;
        } else {
            $updated_is_deleted = $aoad['IS_DELETED'];
        }
        
        return $updated_is_deleted;
    }

    public function get_ao_ad_user_field_plus_aoad(string $field, string $event_id, string $type, int|null $user_id, string $ao_ad_status) {
        try {
            $this->validate_ao_ad_field($field);
        } catch (ErrorException $e) {
            throw $e;
        }

        $aoad = false;

        // Check if user has appropriate permissions
        if (strpos($field, 'AO') === 0) {
            if (!$this->is_ao_user()) {
                $log = 'User is not able to get AO';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                $aoad = $this->get_ao_by_event_id_user_id($event_id, $type, $user_id, false);
            }
            $user_field = 'AO_USER_ID';
        } elseif (strpos($field, 'AD') === 0) {
            if (!$this->is_ad_user()) {
                $log = 'User is not able to get AD';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                if ($ao_ad_status === 'final_ad') {
                    $aoad = $this->get_final_ad_by_event_id_user_id($event_id, $type, $user_id, false, false);
                } else {
                    $aoad = $this->get_ad_by_event_id_user_id($event_id, $type, $user_id, false, $ao_ad_status);
                }
            }
            $user_field = 'AD_USER_ID';
        }

        return [$user_field, $aoad];
    }


    public function save_final_ad_data($data, $event_id, $type, $ao_ad_status) {
        try {
            $this->validate_final_ad_input($data);

            [$table, $table_history] = $this->get_table($type, $ao_ad_status);
            if ($table === false) {
                log_message('error', 'Incorrect type sent when trying to save AOAD');
                throw new ErrorException('AOAD Type not found');
            }

            $user_id = (int)$this->session->userdata("logged_in")["id"];
            
            $field = key($data);
            $field_type = stripos($field, 'COMMENT') !== false ? 'COMMENT' : 'DROPDOWN';
            $value = current($data);
            $aoad = false;
            $pom_id = get_pom_id();

            // Check if user has appropriate permissions
            if (!$this->is_ad_user()) {
                $log = 'User is not able to save AD';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                $aoad = $this->get_final_ad_by_event_id_user_id($event_id, $type, null, false);
            }
            

            if ($ao_ad_status === 'final_ad') {
                $event = $this->get_event_status($type, $event_id);
                if ($event !== 'Not Decided') {
                    throw new ErrorException(sprintf('Unable to save, approvals exists for event %s and must be deleted', $event_id));
                }
            }
            
            $user_field = 'AD_USER_ID';

            if (empty($aoad)) {
                // Insert
                $result = $this->DBs->SOCOM_UI
                    ->set($field, $value)
                    ->set('EVENT_NAME', $event_id)
                    ->set($user_field, $user_id)
                    ->set('POM_ID', $pom_id)
                    ->insert($table);
            } else {
                // TODO: Error handling to prevent updating the results since the data is finalized
                $this->DBs->SOCOM_UI->trans_start();
                $this->DBs->SOCOM_UI
                    ->set($field, $value)
                    ->set($user_field, $user_id)
                    ->set('IS_DELETED', 0)
                    ->where('EVENT_NAME', $event_id)
                    ->where('POM_ID', $pom_id)
                    ->update($table);
                $result = $this->DBs->SOCOM_UI->trans_complete();
                log_message('debug', 'AO AD User table update result transaction was '.$result ? ' true '  : ' false ');
            }
            
            return $result;
        } catch (ErrorException $e) {
            throw $e;
        }
    }


    public function save_ao_ad_user_history(int $userId, string $event_id, string $tablename, string $tablehistoryname ) {
        $pom_id = get_pom_id();

        $body = "";
        if (strpos($tablename, "AO") !== false) {
            $body = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('POM_ID')
            ->select('AO_RECOMENDATION')
            ->select('AO_COMMENT')
            ->select('AO_USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('EVENT_ID')
            ->select('IS_DELETED')
            ->from($tablename)
            ->where('AO_USER_ID', (int)$userId)
            ->where('EVENT_ID', $event_id)
            ->where('POM_ID', $pom_id)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->row_array();
        }
        else if (strpos($tablename, "AD") !== false) {
            if (strpos($tablename, 'AD_FINAL') !== false) {
                $body = $this->DBs->SOCOM_UI
                ->select('ID')
                ->select('POM_ID')
                ->select('AD_RECOMENDATION')
                ->select('AD_USER_ID')
                ->select('CREATED_DATETIME')
                ->select('UPDATED_DATETIME')
                ->select('EVENT_NAME')
                ->select('IS_DELETED')
                ->from($tablename)
                ->where('AD_USER_ID', (int)$userId)
                ->where('EVENT_NAME', $event_id)
                ->where('POM_ID', $pom_id)
                ->order_by('UPDATED_DATETIME DESC')
                ->get()
                ->row_array();
            } else {
                $body = $this->DBs->SOCOM_UI
                ->select('ID')
                ->select('POM_ID')
                ->select('AD_RECOMENDATION')
                ->select('AD_USER_ID')
                ->select('CREATED_DATETIME')
                ->select('UPDATED_DATETIME')
                ->select('EVENT_ID')
                ->select('IS_DELETED')
                ->from($tablename)
                ->where('AD_USER_ID', (int)$userId)
                ->where('EVENT_ID', $event_id)
                ->where('POM_ID', $pom_id)
                ->order_by('UPDATED_DATETIME DESC')
                ->get()
                ->row_array();
            }
        }
        

        if (isset($body)) {
            if (strpos($tablename, "AO")) {
                if (strpos($tablehistoryname, "ZBT")) {
                    $this->DBs->SOCOM_UI
                        ->set('ZBT_AO_ID', $body['ID'])
                        ->set('POM_ID', $body['POM_ID'])
                        ->set('AO_RECOMENDATION', $body['AO_RECOMENDATION'])
                        ->set('AO_COMMENT', $body['AO_COMMENT'])
                        ->set('AO_USER_ID', $body['AO_USER_ID'])
                        ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                        ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                        ->set('EVENT_ID', $body['EVENT_ID'])
                        ->set('IS_DELETED', $body['IS_DELETED'])
                        ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                        ->insert($tablehistoryname);
                } else if (strpos($tablehistoryname, "ISSUE")) {
                    $this->DBs->SOCOM_UI
                        ->set('ISSUE_AO_ID', $body['ID'])
                        ->set('POM_ID', $body['POM_ID'])
                        ->set('AO_RECOMENDATION', $body['AO_RECOMENDATION'])
                        ->set('AO_COMMENT', $body['AO_COMMENT'])
                        ->set('AO_USER_ID', $body['AO_USER_ID'])
                        ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                        ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                        ->set('EVENT_ID', $body['EVENT_ID'])
                        ->set('IS_DELETED', $body['IS_DELETED'])
                        ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                        ->insert($tablehistoryname);
                }
            } else if (strpos($tablehistoryname, "AD")) {
                if (strpos($tablename, "AD_FINAL") !== false) {
                    $this->DBs->SOCOM_UI->set('EVENT_NAME', $body['EVENT_NAME']);
                } else {
                    $this->DBs->SOCOM_UI->set('AD_COMMENT', $body['AD_COMMENT'])
                        ->set('EVENT_ID', $body['EVENT_ID']);
                }

                if (strpos($tablename, "ZBT")) {
                    $this->DBs->SOCOM_UI
                        ->set('ZBT_AD_ID', $body['ID'])
                        ->set('POM_ID', $body['POM_ID'])
                        ->set('AD_RECOMENDATION', $body['AD_RECOMENDATION'])
                        ->set('AD_USER_ID', $body['AD_USER_ID'])
                        ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                        ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                        ->set('IS_DELETED', $body['IS_DELETED'])
                        ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                        ->insert($tablehistoryname);
                } else if (strpos($tablehistoryname, "ISSUE")) {
                    $this->DBs->SOCOM_UI
                        ->set('ISSUE_AD_ID', $body['ID'])
                        ->set('POM_ID', $body['POM_ID'])
                        ->set('AD_RECOMENDATION', $body['AD_RECOMENDATION'])
                        ->set('AD_USER_ID', $body['AD_USER_ID'])
                        ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                        ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                        ->set('IS_DELETED', $body['IS_DELETED'])
                        ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                        ->insert($tablehistoryname);
                }
            }
        } else {
            $log = sprintf('User ID %s was not found in table %s and history was not saved.', $userId, $tablename);
            log_message('error', $log);
        }
    }

    public function save_final_ad_approve_table(
        string $type, string $event_id, string $pom_position, array $year_list, array $funding_lines_data
    ) {
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $criteria_name_id = get_criteria_name_id(false);
        $cycle_id = get_cycle_id();
        $pom_id = get_pom_id();
        $fy_list = $this->get_fypd_list($year_list, $funding_lines_data);
        
        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_EVENT_FUNDING_LINES';
        } else if ($type === 'issue') {
            $table = 'USR_EVENT_FUNDING_LINES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        $data = $this->DBs->SOCOM_UI
                    ->select('EVENT_NAME')
                    ->from($table)
                    ->where('EVENT_NAME', $event_id)
                    ->where('POM_ID', $pom_id)
                    ->get()
                    ->row_array();
        
        if (!empty($data) > 0) {
            $this->SOCOM_Event_Funding_Lines_model->add_to_history($type, $event_id, $pom_id);
            
            // update
            $result = $this->DBs->SOCOM_UI
                ->set('FY_1', $fy_list[0])
                ->set('FY_2', $fy_list[1])
                ->set('FY_3', $fy_list[2])
                ->set('FY_4', $fy_list[3])
                ->set('FY_5', $fy_list[4])
                ->set('APPROVE_TABLE', json_encode($funding_lines_data))
                ->set('USER_ID', $user_id)
                ->set('UPDATE_USER_ID', $user_id)
                ->set('CREATED_DATETIME', 'NOW()', false)
                ->set('UPDATED_DATETIME', 'NOW()', false)
                ->set('IS_DELETED', 0)
                ->where('EVENT_NAME', $event_id)
                ->where('POM_ID', $pom_id)
                ->update($table);
        }
        else {
            // insert
            $result = $this->DBs->SOCOM_UI
                    ->set('EVENT_NAME', $event_id)
                    ->set('CYCLE_ID', $cycle_id)
                    ->set('CRITERIA_NAME_ID', $criteria_name_id)
                    ->set('POM_ID', $pom_id)
                    ->set('POM_POSITION', $pom_position)
                    ->set('FY_1', $fy_list[0])
                    ->set('FY_2', $fy_list[1])
                    ->set('FY_3', $fy_list[2])
                    ->set('FY_4', $fy_list[3])
                    ->set('FY_5', $fy_list[4])
                    ->set('APPROVE_TABLE', json_encode($funding_lines_data))
                    ->set('YEAR_LIST',json_encode($year_list))
                    ->set('USER_ID', $user_id)
                    ->set('UPDATE_USER_ID', $user_id)
                    ->set('CREATED_DATETIME', 'NOW()', false)
                    ->set('UPDATED_DATETIME', 'NOW()', false)
                    ->set('APP_VERSION', APP_TAG)
                    ->insert($table);
        }
        return $result;
    }

    private function get_fypd_list($year_list, $funding_lines_data) {
        $fy_list = [];
        foreach($funding_lines_data as $row) {
            foreach($year_list as $year) {
                if (isset($fy_list[$year])) {
                    $fy_list[$year] += $row[$year];
                }
                else {
                    $fy_list[$year] = $row[$year];
                }
            }
        }
        return array_values($fy_list);
    }

    public function get_event_status($type, $event_name) {
        $pom_id = get_pom_id();

        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_AD_FINAL_SAVES';
        } else if ($type === 'issue') {
            $table = 'USR_ISSUE_AD_FINAL_SAVES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        return $this->DBs->SOCOM_UI
            ->select('AD_RECOMENDATION')
            ->from($table)
            ->where('EVENT_NAME', $event_name)
            ->where('POM_ID', $pom_id)
            ->where('IS_DELETED', 0)
            ->get()
            ->row_array()['AD_RECOMENDATION'] ?? 'Not Decided';
    }

    public function get_final_ad_granted_data_event_table($type) {
        $pom_id = get_pom_id();
        
        if ($type === 'zbt_summary') {
            $getTableParam1 = 'ZBT_SUMMARY';
            $getTableParam2 = 'ZBT_EXTRACT';
            $table = 'USR_ZBT_AD_FINAL_SAVES';
            $table_funding = 'USR_ZBT_EVENT_FUNDING_LINES';
        } else if ($type === 'issue') {
            $getTableParam1 = 'ISS_SUMMARY';
            $getTableParam2 = 'ISS_EXTRACT';
            $table = 'USR_ISSUE_AD_FINAL_SAVES';
            $table_funding = 'USR_EVENT_FUNDING_LINES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        $pom_table = $this->dynamic_year->getTable($getTableParam1, true, $getTableParam2);

        $tmp_final_ad_actions = $this->DBs->SOCOM_UI
            ->select('a.EVENT_NAME, FY_1, FY_2, FY_3, FY_4, FY_5, YEAR_LIST')
            ->from($table . ' a')
            ->join($table_funding.' b', 'ON b.EVENT_NAME = a.EVENT_NAME and a.POM_ID = b.POM_ID')
            ->where('a.POM_ID', $pom_id)
            ->where('a.IS_DELETED', 0)
            ->where('b.IS_DELETED', 0)
            ->get()
            ->result_array() ?? [];
        
        $final_ad_actions = [];
        foreach ($tmp_final_ad_actions as $fada) {
            $year_list = json_decode($fada['YEAR_LIST'], true);
            foreach ($year_list as $i => $fy) {
                $final_ad_actions[$fada['EVENT_NAME']][$fy] = $fada[sprintf('FY_%s', ($i+1))] ?? 0;
            }
        }
        unset($tmp_final_ad_actions, $fada);
        
        return $final_ad_actions;
    }

    public function get_final_ad_granted_data($type, $event_name) {
        $pom_id = get_pom_id();

        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_EVENT_FUNDING_LINES';
        } else if ($type === 'issue') {
            $table = 'USR_EVENT_FUNDING_LINES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        return $this->DBs->SOCOM_UI
            ->select('APPROVE_TABLE')
            ->select('YEAR_LIST')
            ->from($table)
            ->where('EVENT_NAME', $event_name)
            ->where('POM_ID', $pom_id)
            ->where('IS_DELETED', 0)
            ->get()
            ->row_array() ?? [];
    }
    // $type = ['zbt_summary', 'issue']

    public function delete_ao_ad_item($event_id, $field, $type = null, $ao_ad = null, int $deleted_aoad_item = 0) {
        $user_id = $this->session->userdata("logged_in")["id"];
        //$ao_ad returns AO or AD
        //$field return AO_COMMENT, AD_COMMENT, AO_RECOMENDATION, AD_RECOMENDATION

        [$table, $table_history] = $this->get_table($type, $ao_ad);
        if (!$table) {
            throw new ErrorException("Invalid type provided for deletion");
        }
        try {
            $no_user_id = $this->is_ad_user() && $ao_ad === 'final_ad';
            list($user_field, $aoad) = $this->get_ao_ad_user_field_plus_aoad($field, $event_id, $type, $no_user_id ? null : $user_id, $ao_ad);
        } catch (Exception $e) {
            throw $e;
        }

        if ($deleted_aoad_item < 1 || $deleted_aoad_item > 3) {
            log_message('error',
                'Cannot delete because $deleted_aoad_item is not correct deleted value'.
                ' must be between 1 and less than 4.'
            );
            return false;
        } elseif (
            $deleted_aoad_item === SOCOM_AOAD_DELETED_COMMENT && $aoad['IS_DELETED'] === SOCOM_AOAD_DELETED_DROPDOWN ||
            $deleted_aoad_item === SOCOM_AOAD_DELETED_DROPDOWN && $aoad['IS_DELETED'] === SOCOM_AOAD_DELETED_COMMENT
        ) {
            $deleted_aoad_item = SOCOM_AOAD_DELETED_BOTH;
        }

        if ($aoad === false) {
            log_message('error', 
                'No previous comment or dropdown submission to AOAD found and cannot delete, returning false');
            return false;
        }

        $this->DBs->SOCOM_UI->trans_start();

        $this->save_ao_ad_user_history($aoad[$user_field], $event_id, $table, $table_history);

        $pom_id = get_pom_id();
        if ($ao_ad === 'final_ad') {
            $this->DBs->SOCOM_UI->where('EVENT_NAME', $event_id);
        } else {
            $this->DBs->SOCOM_UI->where('EVENT_ID', $event_id);
        }

            
        $result = $this->DBs->SOCOM_UI
            ->set('IS_DELETED', $deleted_aoad_item)
            ->where($user_field, $aoad[$user_field])
            ->where('POM_ID', $pom_id)
            ->update($table);

        if ($ao_ad === 'final_ad') {
            $result = $this->SOCOM_Event_Funding_Lines_model->delete_event_funding_lines($type, $event_id, $aoad[$user_field]);
            if ($result === false) {
                log_message("error", sprintf("Unable to delete funding lines when saving Final AD Action for event %s", $event_id));
                $this->DBs->SOCOM_UI->trans_rollback();
                return false;
            }
        }

        $result = $this->DBs->SOCOM_UI->trans_complete();
        
        return $result;
    }

}