<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Dashboard_Cycle_Management extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Cycle_User_model');
        $this->load->model('SOCOM_Cycle_Management_model');
        $this->load->model('SOCOM_Dynamic_Year_model');

        $this->load->library('SOCOM/Dynamic_Year');
    }

    // --------------------------------------------------------------------

    public function index() {
        $page_data['page_title'] = "Cycle List";
        $page_data['page_tab'] = "";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array(
            'SOCOM/dashboard.css',
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'select2.css', 
            'handsontable.min.css',
            'SOCOM/socom_home.css'
        );
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $data = [];

        $is_admin = $this->rbac_users->is_admin();
        if(!$is_admin){
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }

        $is_cycle_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(2);
        $is_weight_criteria_admin_user = $this->SOCOM_Cycle_User_model->is_user_by_group(3);
        $active_cycle_with_criteria = $this->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
        $pom_year_result = $this->SOCOM_Dynamic_Year_model->getCurrentPom();
        $all_pom_years = $this->SOCOM_Dynamic_Year_model->getAllPomYears();
        $latest_pom_year = $this->SOCOM_Dynamic_Year_model->getLatestPomYear();
        $pom_positions = $this->dynamic_year::POSITIONS;
        $pom_year = $pom_year_result['POM_YEAR'];
        $pom_position = $pom_year_result['LATEST_POSITION'];


        $data['is_cycle_admin_user'] = $is_cycle_admin_user;
        $data['is_weight_criteria_admin_user'] = $is_weight_criteria_admin_user;
        $data['active_cycle_with_criteria'] = $active_cycle_with_criteria;
        $data['pom_year'] = $pom_year;
        $data['pom_position'] = $pom_position;
        $data['all_pom_years'] = $all_pom_years;
        $data['pom_positions'] = $pom_positions;
        $data['latest_pom_year'] = $latest_pom_year;

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/dashboard/cycle_admin/cycle_management_view', $data); 
        $this->load->view('templates/close_view');
    }
    
    public function get_cycles() {
        $http_status = 200;
        $response = ['data' => $this->SOCOM_Cycle_Management_model->get_cycle_list()];
        if (empty($response['data'])) {
            $response['status'] = 'No Cycles Found';
            $response['success'] = false;
        } else {
            $response['success'] = true;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    public function create_cycle() {
        $this->form_validation->set_rules('cycle_name', 'Cycle Name', 'required');

        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        if ($this->form_validation->run() !== FALSE && $data_check['result']) {
            $post_data = $data_check['post_data'];
            $cycle_name = trim($post_data['cycle_name']) ?? '';
            $cycle_desc = trim($post_data['cycle_desc']) ?? '';

            if (!preg_match('/^FY\d{2}_[A-Za-z0-9]+_\d+$/', $cycle_name)) { // Regex example: FY24_TEXT_10
                $result['success'] = false;
                $result['message'] = 'Error: Cycle name is not the correct format. Please see cycle name instructions.';
                $http_status = 500;
            } else {
                if ($this->SOCOM_Cycle_Management_model->cycle_exists($cycle_name)) {
                    $result['success'] = false;
                    $result['message'] = 'Error: A cycle with this name already exists. Please choose a different name.';
                    $http_status = 409;
                }else{
                    $data = [
                        'CYCLE_NAME' => $cycle_name,
                        'DESCRIPTION' => $cycle_desc,
                    ];
        
                    $saved = $this->SOCOM_Cycle_Management_model->save_cycle($data);
        
                    if ($saved) {
                        $result['success'] = true;
                        $result['message'] = 'Cycle Created Successfully';
                        $http_status = 200;
                    } else {
                        $result['success'] = false;
                        $result['message'] = 'Error: Unable to Create Cycle';
                        $http_status = 500;
                    }
            }
            }
        } else {
            $result['success'] = false;
            $result['message'] = 'Error: Unable to Create Cycle';
            $http_status = 500;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($result, JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    public function update_cycle() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output
                ->set_status_header(400)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid input']));
            return;
        }
    
        $post_data = $data_check['post_data'];
        $cycle_id = $post_data['id'] ?? 0;
        $cycle_name = $post_data['cycle_name'] ?? '';
        $cycle_desc = $post_data['cycle_desc'] ?? '';
        $update_type = $post_data['update_type'] ?? '';
    
        if (empty($cycle_id)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode(['success' => false, 'message' => 'Empty cycle id is not allowed']));
            return;
        }
    
        $data = ['ID' => $cycle_id];
        $saved = false;
    
        switch ($update_type) {
            case 'ACTIVATE_CYCLE':
                $data['IS_ACTIVE'] = 1;
                $saved = $this->SOCOM_Cycle_Management_model->save_cycle($data);
                break;
    
            case 'DELETE_CYCLE':
                $data['IS_DELETED'] = 1;
                $saved = $this->SOCOM_Cycle_Management_model->save_cycle($data);
                break;
    
            case 'UPDATE_CYCLE_TEXT':
                if (!preg_match('/^FY\d{2}_[A-Za-z0-9]+_\d+$/', $cycle_name)) { // Regex example: FY24_TEXT_10
                    $this->output
                        ->set_status_header(500)
                        ->set_content_type(self::CONTENT_TYPE_JSON)
                        ->set_output(json_encode(['success' => false, 'message' => 'Error: Cycle name is not the correct format. Please see cycle name instructions.']));
                    return;
                }
                $data['CYCLE_NAME'] = $cycle_name;
                $data['DESCRIPTION'] = $cycle_desc;
                $saved = $this->SOCOM_Cycle_Management_model->save_cycle($data);
                break;
    
            default:
                $this->output
                    ->set_status_header(400)
                    ->set_content_type(self::CONTENT_TYPE_JSON)
                    ->set_output(json_encode(['success' => false, 'message' => 'Invalid update type']));
                return;
        }
    
        if ($saved) {
            $this->output
                ->set_status_header(200)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode(['success' => true, 'message' => 'Cycle updated successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode(['success' => false, 'message' => 'Error: Unable to Update Cycle']));
        }
    }
    

    public function get_deleted_cycles() {
        $http_status = 200;
        $response = [
            'success' => true,
            'data' => $this->SOCOM_Cycle_Management_model->get_deleted_cycles()
        ];

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }


    public function get_active_cycle() {
        $http_status = 200;
        $response = [
            'success' => true,
            'data' => $this->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria()
        ];

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }


    public function get_criteria_terms() {
        $http_status = 200;

        $criteria_name_id = get_criteria_name_id(false);

        $response['data'] = (array)$this->SOCOM_Cycle_Management_model->get_terms_by_criteria_id($criteria_name_id);
        $response['CRITERIA_NAME'] = $this->SOCOM_Cycle_Management_model->get_criteria_by_id($criteria_name_id)['CRITERIA_NAME'] ?? '';
        
        if (empty($response['data'])) {
            $response['status'] = 'No Criteria Terms Found';
            if (is_array($response['data'])) {
                $response['success'] = true;
            } else {
                $response['success'] = false;
            }
        } else {
            $response['success'] = true;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }


    public function create_criteria() {
        $this->form_validation->set_rules('criteria_name', 'Criteria Name', 'required');

        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        $this->form_validation->run();

        if ($this->form_validation->run() !== FALSE && $data_check['result']) {
            $post_data = $data_check['post_data'];

            $criteria_id = trim($post_data['criteria_id']) ?? 0;
            $criteria_name = trim($post_data['criteria_name']) ?? '';
            $criteria_terms = $post_data['criteria_terms'] ?? [];
            $cycle_id = get_cycle_id();

            foreach ($criteria_terms as $criteria_term) {
                if (preg_match('/^[A-Za-z0-9 _]+$/', $criteria_term) !== 1) { // Regex example: FY24_TEXT_10
                    $this->output
                        ->set_status_header(400)
                        ->set_content_type(self::CONTENT_TYPE_JSON)
                        ->set_output(json_encode(['success' => false, 'message' =>
                            sprintf(
                                'Error: Criteria Term is not the correct format "%s". Please see Criteria Term instructions.',
                                $criteria_term)
                        ], JSON_HEX_APOS | JSON_HEX_QUOT));
                    return;
                }
            }

            $data = [
                'ID' => $criteria_id,
                'CRITERIA_NAME' => $criteria_name,
                'CYCLE_ID' => $cycle_id,
                'CRITERIA_TERMS' => $criteria_terms,
            ];

            $saved = $this->SOCOM_Cycle_Management_model->create_criteria_name_and_terms($data);

            if ($saved) {
                $result['success'] = true;
                $result['message'] = 'Criteria Created Successfully';
                $http_status = 200;
                
            } else {
                $result['success'] = false;
                $result['message'] = 'Error: Unable to Create Criteria';
                $http_status = 500;
            }
        } else {
            $result['success'] = false;
            $result['message'] = 'Error: Unable to Create Criteria';
            $http_status = 500;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($result, JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    public function update_criteria_description() {
        $http_status = 200;
        $id = $this->input->post('id');
        $criteria_name_id = $this->input->post('criteria_name_id');
        $description = $this->input->post('description');

        if (!$id || !$criteria_name_id) {
            $http_status = 400;
            $response = ['success' => false, 'message' => 'Invalid request parameters'];
        } else {
            $success = $this->SOCOM_Cycle_Management_model->update_criteria_description($id, $criteria_name_id, $description);
            $response = ['success' => $success, 'message' => $success ? 'Description updated successfully' : 'Error updating description'];
        }
        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }
     
     public function delete_criteria_description() {
        $http_status = 200;
        $id = $this->input->post('id');
        $criteria_name_id = $this->input->post('criteria_name_id');

        if (!$id || !$criteria_name_id) {
            $http_status = 400;
            $response = ['success' => false, 'message' => 'Invalid request parameters'];
        } else {
            $success = $this->SOCOM_Cycle_Management_model->delete_criteria_description($id, $criteria_name_id);
            $response = ['success' => $success, 'message' => $success ? 'Description deleted successfully' : 'Error deleting description'];
        }
        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }
}
