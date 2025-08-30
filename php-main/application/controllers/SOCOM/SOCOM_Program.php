<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Program extends CI_Controller {

    protected const CONTENT_TYPE_JSON = 'application/json';
    public function __construct(){
        parent::__construct();

        if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }

        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_Weights_model');
        $this->load->model('SOCOM_Weights_List_model');
        $this->load->model('SOCOM_Assessment_Area_model');
        $this->load->model('SOCOM_Program_model');
        $this->load->library('SOCOM/Dynamic_Year');
        $this->ISS_YEAR = $this->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');
        $this->YEAR_LIST = $this->dynamic_year->getYearList($this->ISS_YEAR);
    }

    // --------------------------------------------------------------------

    /**
     * 
     */
    public function index()
    {
        get_cycle_id();

        $is_guest = $this->rbac_users->is_guest();
		$is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $page_data['page_title'] = "SOCOM Program Viewer";
        $page_data['page_tab'] = "";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array(
            'select2.css',
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'SOCOM/socom_home.css',
            'handsontable.min.css', 
            'SOCOM/score.css'
        );
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $data = [];

        $data['table_headers'] = [
            'Program', 'Capability Sponsor', 'Resource Category Code', ...$this->YEAR_LIST, '', 'Weights: Guidance', 'Weights: POM', '', 'StoRM ID','StoRM Score'
        ];

        $data['default_criteria'] = array_column($this->SOCOM_model->get_option_criteria_names(), 'CRITERIA');
        $data['default_criteria_description'] = $this->SOCOM_model->get_option_criteria_names_and_description();
        #$data['score_table_headers'] = [ 'Program', 'POM Sponsor', 'Capability Sponsor', '2026', '2027', '2028', '2029', '2030', '', 'Guidance', 'POM', ''];
        $user_id = $this->session->userdata("logged_in")["id"];
        $data['optimizer_weights'] = $this->SOCOM_Weights_model->get_user_weights($user_id);

        $data['fy_list'] = json_encode($this->YEAR_LIST);
        
        $get_active_cycle_with_criteria = $this->DBs->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
        $data['get_active_cycle_with_criteria'] = $get_active_cycle_with_criteria;
        
        [$year, ] = get_years_coa(false);
        $data['subapp_pom_year'] = $year;
        $data['subapp_pom_year_issue'] = get_years_coa(true)[0];


        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/program/program_list_view', $data);
        $this->load->view('templates/close_view');
    }

    public function get_program($scored = false) {
        
        if ($scored === 'scored') {
            $scored = true;
        } else {
            $scored = false;
        }

        $post_check = $this->DB_ind_model->validate_post($this->input->post());
        
        $post_data = $post_check['post_data'];
        if (!$this->session->has_userdata('use_iss_extract')) {
            $this->session->set_userdata('use_iss_extract', true); // Default to 'true'
        }
        if (isset($post_data['use_iss_extract'])) {
            $use_iss_extract = filter_var($post_data['use_iss_extract'], FILTER_VALIDATE_BOOLEAN);
            $this->session->set_userdata('use_iss_extract', $use_iss_extract);
        }
        $assesment_area_code = $post_data['ass-area'] ?? [];
        $program_group = $post_data['program'] ?? [];
        $optimizer_propogation =  (int)(($post_data['optimizer_propogation'] ?? 0) === 'true');
        $optimizer_propogation_stop =  (int)(($post_data['optimizer_propogation'] ?? 0) === 'false');
        // $iss_extract = filter_var($post_data['use_iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $iss_extract = filter_var($this->session->userdata('use_iss_extract'), FILTER_VALIDATE_BOOLEAN);
        
        if ($optimizer_propogation_stop === 1) {
            unset($_SESSION['SOCOM']['optimizer']);
        } else if ($optimizer_propogation === 1) {
            filter_var(
                $assesment_area_code,
                FILTER_SANITIZE_SPECIAL_CHARS,
                FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK);
            
            filter_var(
                $program_group,
                FILTER_SANITIZE_SPECIAL_CHARS,
                FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK);
            
            $this->session->set_userdata('SOCOM', ['optimizer' => [
                'ass-area' => $assesment_area_code,
                'program_group' => $program_group
            ]]);
        }

        if (isset($this->session->userdata('SOCOM')['optimizer'])) {
            $assesment_area_code = $this->session->userdata('SOCOM')['optimizer']['ass-area'] ?? [];
            $program_group = $this->session->userdata('SOCOM')['optimizer']['program_group'] ?? [];
        }

        $result = $this->SOCOM_model->get_program($scored, $assesment_area_code, $program_group, $iss_extract);

        $http_status = 200;

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($result));
    }

    public function update_selection() {

        if (!$this->input->is_ajax_request()) {
            show_error('No direct script access allowed', 403);
            return;
        }
        $post_data = $this->input->post();
        if (isset($post_data['use_iss_extract'])) {
            $use_iss_extract = filter_var($post_data['use_iss_extract'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            $this->session->set_userdata('use_iss_extract', $use_iss_extract);
            echo json_encode(['status' => 'success', 'use_iss_extract' => $use_iss_extract]);
            return;
        }
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        http_response_code(400);
    }
    
    public function get_weighted_table($type){
        $post_check = $this->DB_ind_model->validate_post($this->input->get());
        $post_data = $post_check['post_data'];
        $weight_id = (int)$post_data['weight_id'];
        $result = $this->SOCOM_model->get_weighted_table($weight_id);
        $description = isset($result[0]['SESSION']) ? json_decode($result[0]['SESSION'],true) : [strtolower($type) => []];
        $http_status = 200;
        foreach ( $description[strtolower($type)] as &$value) {
            $value = number_format((float)$value, 2, '.', '');
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode([
                'data' => [$description[strtolower($type)]]
            ]));
    }
}