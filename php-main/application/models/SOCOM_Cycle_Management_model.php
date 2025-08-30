<?php

#[AllowDynamicProperties]
class  SOCOM_Cycle_Management_model extends CI_Model {
    public function get_cycle_list() {
        $results = $this->DBs->SOCOM_UI
            ->select('t1.ID')
            ->select('t1.CYCLE_NAME')
            ->select('t1.DESCRIPTION')
            ->select('t1.USER_ID')
            ->select('t1.IS_ACTIVE')
            ->select('t1.IS_DELETED')
            ->select('t1.CREATED_DATETIME')
            ->from('USR_LOOKUP_CYCLES t1')
            ->join('USR_LOOKUP_POM_POSITION_CYCLE t2', ' ON t2.CYCLE_ID = t1.ID')
            ->join('USR_LOOKUP_POM_POSITION t3', ' ON t3.ID = t2.POM_ID')
            ->where('t3.IS_ACTIVE', 1)
            ->where('IS_DELETED', 0)
            ->order_by('IS_ACTIVE', 'DESC')
            ->order_by('CREATED_DATETIME', 'DESC')
            ->get()
            ->result_array();

        return $results;
    }


    public function get_cycle_by_id($cycle_id) {
        $result = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('CYCLE_NAME')
            ->select('DESCRIPTION')
            ->select('USER_ID')
            ->select('IS_ACTIVE')
            ->select('IS_DELETED')
            ->select('CREATED_DATETIME')
            ->from('USR_LOOKUP_CYCLES')
            ->where('ID', $cycle_id)
            ->get()
            ->result_array();
        
        return !empty($result) ? $result : false;
    }


    public function get_deleted_cycles() {
        $results = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('CYCLE_NAME')
            ->select('DESCRIPTION')
            ->select('USER_ID')
            ->select('IS_ACTIVE')
            ->select('IS_DELETED')
            ->select('CREATED_DATETIME')
            ->from('USR_LOOKUP_CYCLES')
            ->where('IS_DELETED', 1)
            ->get()
            ->result_array();

        return $results;
    }


    public function get_active_cycle_id() {
        static $cycle_result = null;

        if ($cycle_result === null) {
            $cycle_result = $this->DBs->SOCOM_UI
                ->select('c.ID as CYCLE_ID')
                ->select('n.ID as CRITERIA_NAME_ID')
                ->select('pc.POM_ID as POM_ID')
                ->from('USR_LOOKUP_CYCLES c')
                ->join('USR_LOOKUP_USER_CRITERIA_NAME n', 'ON c.ID = n.CYCLE_ID', 'left')
                ->join('USR_LOOKUP_POM_POSITION_CYCLE pc', 'ON c.ID = pc.CYCLE_ID')
                ->where('c.IS_ACTIVE', 1)
                ->get()
                ->row_array();
        }

        return $cycle_result ?? false;
    }


    public function get_active_cycle() {
        $result = $this->DBs->SOCOM_UI
            ->select('c.ID')
            ->select('CYCLE_NAME')
            ->select('DESCRIPTION')
            ->select('c.USER_ID')
            ->select('IS_ACTIVE')
            ->select('IS_DELETED')
            ->select('c.CREATED_DATETIME')
            ->from('USR_LOOKUP_CYCLES c')
            ->join('USR_LOOKUP_POM_POSITION_CYCLE pc', 'ON c.ID = pc.CYCLE_ID')
            ->where('IS_ACTIVE', 1)
            ->get()
            ->row_array();

        return $result ?? null;
    }


    public function get_active_cycle_with_criteria() {
        $result = $this->DBs->SOCOM_UI
            ->select('cy.ID AS CYCLE_ID, cy.CYCLE_NAME, cr.ID AS CRITERIA_ID, cr.CRITERIA_NAME, cy.IS_ACTIVE')
            ->from('USR_LOOKUP_CYCLES cy')
            ->join('USR_LOOKUP_USER_CRITERIA_NAME cr', 'cy.ID = cr.CYCLE_ID', 'left')
            ->join('USR_LOOKUP_POM_POSITION_CYCLE pc', 'ON cy.ID = pc.CYCLE_ID')
            ->where('cy.IS_ACTIVE', 1)
            ->get()
            ->row_array();
    
        return $result;
    }


    public function save_cycle($data) {
        try {
            $user_id = (int)$this->session->userdata("logged_in")["id"];
            
            $cycle_id = $data['ID'];

            $cycle = false;

            // Check if user has appropriate permissions
            $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);
            if (!$is_cycle_admin_user) {
                $log = 'User is not able to create or update cycle';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                $cycle = $this->get_cycle_by_id($cycle_id);
            }

            // Create new cycle if cycle_id does not exist
            if ($cycle === false) {
                $cycle_name = $data['CYCLE_NAME'];
                $cycle_desc = $data['DESCRIPTION'];

                // Deactivate existing active cycle
                $this->DBs->SOCOM_UI->set('IS_ACTIVE', 0)
                    ->where('IS_ACTIVE', 1)
                    ->update('USR_LOOKUP_CYCLES');

                // Create new cycle and set active
                $result = $this->DBs->SOCOM_UI
                    ->set('CYCLE_NAME', $cycle_name)
                    ->set('DESCRIPTION', $cycle_desc)
                    ->set('USER_ID', $user_id)
                    ->set('IS_ACTIVE', 1)
                    ->set('IS_DELETED', 0)
                    ->insert('USR_LOOKUP_CYCLES');
                    
                    $cycle_id = $this->DBs->SOCOM_UI->insert_id();

                    $this->SOCOM_Dynamic_Year_model->setCycleToActivePom($cycle_id);
            } else {
                // Update
                $updating_is_active = $data['IS_ACTIVE'] === 1 ?? false;
                $updating_is_deleted = $data['IS_DELETED'] === 1 ?? false;
                $updating_cycle_text = $data['CYCLE_NAME'] || $data['DESCRIPTION'] ?? false;

                $this->DBs->SOCOM_UI->trans_start();
                // Save to history table
                $this->save_cycle_history($cycle_id);

                if ($updating_is_active) {
                    // Deactivate any existing active cycle
                    $this->DBs->SOCOM_UI->set('IS_ACTIVE', 0)
                        ->where('IS_ACTIVE', 1)
                        ->update('USR_LOOKUP_CYCLES');

                    // Set selected cycle to active
                    $this->DBs->SOCOM_UI
                        ->set('IS_ACTIVE', 1)
                        ->where('ID', $cycle_id)
                        ->update('USR_LOOKUP_CYCLES');
                }

                else if ($updating_is_deleted) {
                    // Delete and deactivate selected cycle
                    $this->DBs->SOCOM_UI
                        ->set('IS_DELETED', 1)
                        ->set('IS_ACTIVE', 0)
                        ->where('ID', $cycle_id)
                        ->update('USR_LOOKUP_CYCLES');
                }

                else if ($updating_cycle_text) {
                    // Update selected cycle name
                    $this->DBs->SOCOM_UI
                        ->set('CYCLE_NAME', $data['CYCLE_NAME'])
                        ->set('DESCRIPTION', $data['DESCRIPTION'])
                        ->where('ID', $cycle_id)
                        ->update('USR_LOOKUP_CYCLES');
                }

                $result = $this->DBs->SOCOM_UI->trans_complete();
            }
            
            return $result;
        } catch (ErrorException $e) {
            throw $e;
        }
    }


    public function save_cycle_history($cycle_id) {
        $body = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('CYCLE_NAME')
            ->select('DESCRIPTION')
            ->select('USER_ID')
            ->select('IS_ACTIVE')
            ->select('IS_DELETED')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->from('USR_LOOKUP_CYCLES')
            ->where('ID', $cycle_id)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->row_array();

        if (isset($body)) {
            $this->DBs->SOCOM_UI
                ->set('CYCLE_ID', $body['ID'])
                ->set('CYCLE_NAME', $body['CYCLE_NAME'])
                ->set('DESCRIPTION', $body['DESCRIPTION'])
                ->set('USER_ID', $body['USER_ID'])
                ->set('IS_ACTIVE', $body['IS_ACTIVE'])
                ->set('IS_DELETED', $body['IS_DELETED'])
                ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                ->insert('USR_LOOKUP_CYCLES_HISTORY');
        } else {
            $log = sprintf('Cycle id %s was not found in table %s and history was not saved.', $cycle_id, 'USR_LOOKUP_CYCLES');
            log_message('error', $log);
        }
    }


    public function get_criteria_by_id($criteria_id) {
        $result = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('CRITERIA_NAME')
            ->select('CYCLE_ID')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->from('USR_LOOKUP_USER_CRITERIA_NAME')
            ->where('ID', $criteria_id)
            ->get()
            ->row_array();
        
        return !empty($result) ? $result : false;
    }


    public function save_criteria($data) {
        try {
            $user_id = (int)$this->session->userdata("logged_in")["id"];
            
            $criteria_id = $data['ID'];
            $criteria_name = $data['CRITERIA_NAME'];
            $cycle_id = $data['CYCLE_ID'];

            $criteria = false;

            // Check if user has appropriate permissions
            $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);
            $is_weight_criteria_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(3);

            $has_permissions = $is_cycle_admin_user || $is_weight_criteria_admin_user;

            if (!($has_permissions)) {
                $log = 'User is not able to create or update criteria';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                $criteria = $this->get_criteria_by_id($criteria_id);
            }

            // Create new criteria if criteria_id does not exist
            if ($criteria === false) {
                $result = $this->DBs->SOCOM_UI
                    ->set('CRITERIA_NAME', $criteria_name)
                    ->set('CYCLE_ID', $cycle_id)
                    ->set('USER_ID', $user_id)
                    ->insert('USR_LOOKUP_USER_CRITERIA_NAME');
            } else {
                // Update
                $this->DBs->SOCOM_UI->trans_start();
                // Save to history table
                $this->save_criteria_history($criteria_id);

                $this->DBs->SOCOM_UI
                    ->set('CRITERIA_NAME', $data['CRITERIA_NAME'])
                    ->where('ID', $criteria_id)
                    ->update('USR_LOOKUP_USER_CRITERIA_NAME');

                $result = $this->DBs->SOCOM_UI->trans_complete();
            }
            return $result;
        } catch (ErrorException $e) {
            throw $e;
        }
    }


    public function save_criteria_history($criteria_id) {
        $body = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('CRITERIA_NAME')
            ->select('CYCLE_ID')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->from('USR_LOOKUP_USER_CRITERIA_NAME')
            ->where('ID', $criteria_id)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->row_array();

        if (isset($body)) {
            $this->DBs->SOCOM_UI
                ->set('CRITERIA_NAME_ID', $body['ID'])
                ->set('CRITERIA_NAME', $body['CRITERIA_NAME'])
                ->set('CYCLE_ID', $body['CYCLE_ID'])
                ->set('USER_ID', $body['USER_ID'])
                ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                ->insert('USR_LOOKUP_USER_CRITERIA_NAME_HISTORY');
        } else {
            $log = sprintf('Cycle id %s was not found in table %s and history was not saved.', $cycle_id, 'USR_LOOKUP_CYCLES');
            log_message('error', $log);
        }
    }


    public function get_terms_by_criteria_id($criteria_name_id) {
        $results = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('CRITERIA_NAME_ID')
            ->select('CRITERIA_TERM')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('CRITERIA_DESCRIPTION')
            ->from('USR_LOOKUP_USER_CRITERIA_TERMS')
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->order_by('CRITERIA_TERM', 'ASC')
            ->get()
            ->result_array();

        return $results;
    }

    
    public function save_criteria_term($data) {
        try {
            $user_id = (int)$this->session->userdata("logged_in")["id"];
            
            $criteria_name_id = $data['CRITERIA_NAME_ID'];
            $criteria_term = $data['CRITERIA_TERM'];

            // Check if user has appropriate permissions
            $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);
            $is_weight_criteria_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(3);

            $has_permissions = $is_cycle_admin_user || $is_weight_criteria_admin_user;

            if (!($has_permissions)) {
                $log = 'User is not able to create or update criteria';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                $result = $this->DBs->SOCOM_UI
                    ->set('CRITERIA_TERM', $criteria_term)
                    ->set('CRITERIA_NAME_ID', $criteria_name_id)
                    ->set('USER_ID', $user_id)
                    ->insert('USR_LOOKUP_USER_CRITERIA_TERMS');
            }
            
            return $result;
        } catch (ErrorException $e) {
            throw $e;
        }
    }


    public function create_criteria_name_and_terms($data) {
        try {
            $user_id = (int)$this->session->userdata("logged_in")["id"];

            $criteria_id = $data['ID'];
            $criteria_name = $data['CRITERIA_NAME'];
            $cycle_id = $data['CYCLE_ID'];
            $criteria_terms = $data['CRITERIA_TERMS'];

            $criteria = false;

            // Check if user has appropriate permissions
            $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);
            $is_weight_criteria_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(3);

            $has_permissions = $is_cycle_admin_user || $is_weight_criteria_admin_user;

            if (!($has_permissions)) {
                $log = 'User is not able to create or update criteria';
                log_message('error', $log);
                
                throw new ErrorException($log);
            } else {
                $criteria = $this->get_criteria_by_id($criteria_id);
            }

            // Create new criteria if criteria_id does not exist
            if ($criteria === false) {
                // Start a transaction
                $this->DBs->SOCOM_UI->trans_start();
                
                // Insert into USR_LOOKUP_USER_CRITERIA_NAME
                $this->DBs->SOCOM_UI
                    ->set('CRITERIA_NAME', $criteria_name)
                    ->set('CYCLE_ID', $cycle_id)
                    ->set('USER_ID', $user_id)
                    ->insert('USR_LOOKUP_USER_CRITERIA_NAME');
                
                // Get the ID of the record inserted into USR_LOOKUP_USER_CRITERIA_NAME
                $criteria_name_id = $this->DBs->SOCOM_UI->insert_id();
                
                // Prepare terms data
                $terms_data = [];
                foreach ($criteria_terms as $term_name) {
                    $terms_data[] = [
                        'CRITERIA_NAME_ID' => $criteria_name_id, // Use the ID from the first table (USR_LOOKUP_USER_CRITERIA_NAME)
                        'CRITERIA_TERM' => $term_name,
                        'USER_ID' => $user_id,
                    ];
                }
                
                // Insert into USR_LOOKUP_USER_CRITERIA_TERMS
                if (!empty($terms_data)) {
                    $this->DBs->SOCOM_UI->insert_batch('USR_LOOKUP_USER_CRITERIA_TERMS', $terms_data);
                }

                $result = $this->DBs->SOCOM_UI->trans_complete();

                return $result;
            }
        } catch (ErrorException $e) {
            throw $e;
        }
    }

    public function cycle_exists($cycle_name) {
        $result = $this->DBs->SOCOM_UI
            ->select('ID')
            ->from('USR_LOOKUP_CYCLES')
            ->where('CYCLE_NAME', $cycle_name)
            ->where('IS_DELETED', 0)
            ->get()
            ->num_rows();
        return $result > 0;
     }

    public function update_criteria_description($id, $criteria_name_id, $description) {
        $data = ['CRITERIA_DESCRIPTION' => $description !== '' ? $description : NULL];
        $result = $this->DBs->SOCOM_UI
            ->where('ID', $id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->update('USR_LOOKUP_USER_CRITERIA_TERMS', $data);
        return $result;
    }
    
    public function delete_criteria_description($id, $criteria_name_id) {
        $result = $this->DBs->SOCOM_UI
            ->where('ID', $id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->update('USR_LOOKUP_USER_CRITERIA_TERMS', ['CRITERIA_DESCRIPTION' => NULL]);
        return $result;
    }
    
}
