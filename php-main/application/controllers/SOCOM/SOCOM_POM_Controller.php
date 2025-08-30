<?php
class SOCOM_POM_Controller extends CI_Controller{
    protected const CONTENT_TYPE_JSON = 'application/json';
    
    public function __construct() {
        parent::__construct();

        $this->load->model('SOCOM_Cycle_User_model');
        $this->load->model('SOCOM_Dynamic_Year_model');
    }

    public function get_tables_exist_pom() {
        $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);

        if ($is_cycle_admin_user === false) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        } else {
            $http_status = 200;

            $data_check = $this->DB_ind_model->validate_post($this->input->post());
            if ($data_check['result']) {
                $post_data = $data_check['post_data'];
                $year = $post_data['pom_year'] ?? null;
                $position = $post_data['pom_position'] ?? null;

                $this->dynamic_year->setTablesExist($year, $position);
            }
            
            $response = ['data' => $this->dynamic_year->getTablesPom()];
            $response['missing'] = $this->dynamic_year->hasMissing();
            
            if ($response['missing'] === true) {
                //$response['status'] = 'Tables are missing for this POM, Save is disabled';
                $response['success'] = false;
            } elseif (empty($response['data'])) {
                $response['status'] = 'No Tables Found';
                $response['success'] = false;
            } else {
                $response['success'] = true;
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }
 
    public function get_tables_exist($page) {
        $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);

        if (
            $is_cycle_admin_user === false || 
            !in_array($page, Dynamic_Year::SUBAPPS, true)
        ) {
            $http_status = 403;
            $response['status'] = 'No Permissions';
            $response['success'] = false;
        } else {
            $http_status = 200;
            
            $data_check = $this->DB_ind_model->validate_post($this->input->post());
            if ($data_check['result']) {
                $post_data = $data_check['post_data'];
                $year = $post_data['pom_year'] ?? null;
                $position = $post_data['pom_position'] ?? null;

                $this->dynamic_year->setTablesExist($year, $position);
            }


            $response = ['data' => $this->dynamic_year->getTablesPagePom()[$page] ?? []];

            if (empty($response['data'])) {
                $response['status'] = 'No Tables Found';
                $response['success'] = false;
            } else {
                $response['success'] = true;
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function save_new_pom() {
        if (!$this->input->is_ajax_request()) {
            return $this->output
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'Invalid request']));
        }
        $year = (int)$this->input->post('year');
        $position = $this->input->post('position');
        if (empty($year) || empty($position)) {
            return $this->output
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'Year and position are required']));
        }
        // Get current active POM
        $current_pom = $this->SOCOM_Dynamic_Year_model->getCurrentPom();
        // Verify year is different than current active year
        
        if ((int)$current_pom['POM_YEAR'] === $year && $current_pom['LATEST_POSITION'] == $position) {
            return $this->output
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'When using current POM year, position must be different']));
        }
        
        $missingTables = $this->dynamic_year->getByYear($year, $position)['MISSING'] ?? false;

        if ($missingTables !== false) {
            $log = sprintf(
                'Unable to save %s for POM year %s due to missing tables: %s',
                $position,
                $year,
                implode(', ', $missingTables['CURRENT']['POM'] ?? ['Could not parse'])
            );
            log_message('error', $log);

            return $this->output
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => $log]));
        }

        // Save new POM
        $result = $this->SOCOM_Dynamic_Year_model->saveNewPom($year, $position);
        if (!$result) {
            return $this->output
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_status_header(500)
                ->set_output(json_encode(['status' => false, 'message' => 'Failed to save POM']));
        }

        $this->dynamic_year->setActive();

        return $this->output
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode([
                'status' => true,
                'message' => 'POM saved successfully',
                'data' => [
                    'year' => $year,
                    'position' => $position
                ]
            ]));
    }

}

?>