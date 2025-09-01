<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Dashboard_COA_Management extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
          if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_COA_model');
        $this->load->model('DB_ind_model');
        $this->load->model('SOCOM_Cycle_Management_model');
        $this->load->helper('general');
        $this->load->helper('coa');
        $this->load->helper('cycle');
        $this->load->library('SOCOM/Dynamic_Year');


        $this->ISS_FY = $this->dynamic_year->getPomYearForSubapp('RESOURCE_CONSTRAINED_COA_YEAR');
        $this->ISS_EXTRACT_FY = $this->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');

        $this->iss_fy_years = array_map('strval', range($this->ISS_FY, $this->ISS_FY + 4));
        $this->iss_extract_fy_years = array_map('strval', range($this->ISS_EXTRACT_FY, $this->ISS_EXTRACT_FY + 4));

        $this->fy_years = [
            'ISS' => $this->iss_fy_years,
            'RC_T' => $this->iss_fy_years,
            'ISS_EXTRACT' => $this->iss_extract_fy_years
        ];

        $this->merged_COA_table_headers = [
            ['data' => 'SELECTION', 'title' => ''],
            ['data' => 'ID', 'title' => 'ID'],
            ['data' => 'PROGRAM', 'title' => 'Program'],
            ['data' => 'EOC', 'title' => 'EOC'],
            ['data' => 'CAP_SPONSOR', 'title' => 'Cap Sponsor'],
            ['data' => 'POM_SPONSOR', 'title' => 'POM Sponsor'],
            ['data' => 'ASSESSMENT_AREA', 'title' => 'Assessment Area'],
            ['data' => 'RESOURCE_CATEGORY', 'title' => 'Resource Category'],
            ['data' => 'STORM_SCORE', 'title' => 'StoRM Score'],
            ['data' => 'POM_SCORE', 'title' => 'POM Score'],
            ['data' => 'GUIDANCE_SCORE', 'title' => 'Guidance Score'],
            ['data' => 'EXECUTION_MANAGER_CODE', 'title' => 'Execution Manager'],
            ['data' => 'OSD_PE', 'title' => 'OSD PE Code']
        ];

        $this->weighted_score_option_header_keys = [
            'POM' => ['POM_SCORE'],
            'GUIDANCE' => ['GUIDANCE_SCORE'],
            'BOTH' => ['POM_SCORE', 'GUIDANCE_SCORE'],
            'STORM' => ['STORM_SCORE']
        ];

        $this->weighted_score_option = [
            1 => 'both',
            2 => 'guidance',
            3 => 'pom',
            4 => 'storm'
        ];

        $this->coa_table_key_map = [
            'RC_T' => [
                'PROGRAM_CODE' => 'program_code',
                'EOC_CODE' => 'eoc_code',
                'CAPABILITY_SPONSOR_CODE' => 'capability_sponsor',
                'RESOURCE_CATEGORY_CODE' => 'resource_category_code',
                'ASSESSMENT_AREA_CODE' => 'assessment_area_code',
                'OSD_PE' => 'osd_pe',
                'POM_SPONSOR_CODE' => 'pom_sponsor',
                'EXECUTION_MANAGER_CODE' => 'execution_manager_code',
                'RESOURCE_K' => 'resource_k',
                'ID' => 'program_id'
            ],
            'ISS_EXTRACT' => [
               'PROGRAM_CODE' => 'PROGRAM_CODE',
               'EOC_CODE' => 'EOC_CODE',
               'CAPABILITY_SPONSOR_CODE' => 'CAPABILITY_SPONSOR_CODE',
               'RESOURCE_CATEGORY_CODE' => 'RESOURCE_CATEGORY_CODE',
               'ASSESSMENT_AREA_CODE' => 'ASSESSMENT_AREA_CODE',
               'EVENT_NAME' => 'EVENT_NAME',
               'OSD_PE' => 'OSD_PE',
               'POM_SPONSOR_CODE' => 'POM_SPONSOR_CODE',
               'EXECUTION_MANAGER_CODE' => 'EXECUTION_MANAGER_CODE',
               'DELTA_AMT' => 'DELTA_AMT',
               'ID' => 'ID'
            ]
        ];
    }

    // --------------------------------------------------------------------

    public function index() {
        $page_data['page_title'] = "COA Management";
        $page_data['page_tab'] = "";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = array(
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'select2.css',
            'handsontable.min.css',
            'SOCOM/socom_home.css'
        );
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $data = [];

        $data['user_emails'] = $this->SOCOM_Users_model->get_users();
        
        // Handle missing session data with dev bypass
        if (isset($this->session->userdata["logged_in"]["id"])) {
            $data['user_id'] = (int)$this->session->userdata("logged_in")["id"];
        } else {
            $data['user_id'] = 1; // Default user ID for development
        }
        
        $data['active_cycle_with_criteria'] = $this->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
        
        // Debug: Test the get_active_cycle_id method
        $active_cycle = $this->SOCOM_Cycle_Management_model->get_active_cycle_id();
        log_message('debug', 'Active cycle data: ' . json_encode($active_cycle));
        $data['fy_years'] = $this->fy_years;

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/dashboard/coa_management/coa_management_view', $data);
        $this->load->view('templates/close_view');
    }


    public function test_criteria() {
        $criteria_name_id = get_criteria_name_id();
        $user_id = 1;
        
        $result = [
            'criteria_name_id' => $criteria_name_id,
            'user_id' => $user_id,
            'type' => gettype($criteria_name_id),
            'is_false' => ($criteria_name_id === false),
            'is_null' => is_null($criteria_name_id)
        ];
        
        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($result))
            ->_display();
        exit();
    }

    public function get_my_coa() {
        $http_status = 200;

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        $post_data = $data_check['post_data'];

        $use_iss_extract = filter_var($post_data['use_iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $type_of_coa = $use_iss_extract ? 'ISS_EXTRACT' : 'RC_T';

        $response = ['data' => $this->SOCOM_COA_model->get_my_coa($type_of_coa)];

        if (empty($response)) {
            $response['status'] = 'No COAs Found';
            $response['success'] = false;
        } else {
            $response['success'] = true;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }


    public function get_coa_shared_by_me() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        $post_data = $data_check['post_data'];

        $is_revoked = $post_data['is_revoked'] ?? 0;

        $use_iss_extract = filter_var($post_data['use_iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $type_of_coa = $use_iss_extract ? 'ISS_EXTRACT' : 'RC_T';

        $http_status = 200;
        $response = ['data' => $this->SOCOM_COA_model->get_coa_shared_by_me($is_revoked, $type_of_coa)];

        if (empty($response)) {
            $response['status'] = 'No COAs Found';
            $response['success'] = false;
        } else {
            $response['success'] = true;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }


    public function get_coa_shared_to_me() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        $post_data = $data_check['post_data'];

        $is_revoked = $post_data['is_revoked'] ?? 0;

        $use_iss_extract = filter_var($post_data['use_iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $type_of_coa = $use_iss_extract ? 'ISS_EXTRACT' : 'RC_T';

        $http_status = 200;
        $response = ['data' => $this->SOCOM_COA_model->get_coa_shared_to_me($is_revoked, $type_of_coa)];

        if (empty($response)) {
            $response['status'] = 'No COAs Found';
            $response['success'] = false;
        } else {
            $response['success'] = true;
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    
    public function share_coa() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];

        $selected_email_ids = $post_data['selected_email_ids'];
        $selected_coas = $post_data['selected_coas'];
        
        $response = ['success' => true, 'status' => ''];
        $http_status = 201;

        $result = $this->SOCOM_COA_model->share_coa($selected_email_ids, $selected_coas);

        if ($result != false) {
            $http_status = 201;
            $response['success'] = true;
            $response['status'] = 'Successfully shared COA.';
        } else {
            $http_status = 406;
            $response['success'] = false;
            $response['status'] = 'Unable to share COA';
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    public function revoke_coa() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];

        $shared_coa_id = $post_data['shared_coa_id'];
        
        $response = ['success' => true, 'status' => ''];
        $http_status = 201;

        $result = $this->SOCOM_COA_model->revoke_coa($shared_coa_id);

        if ($result != false) {
            $http_status = 201;
            $response['success'] = true;
            $response['status'] = 'Successfully revoked COA.';
        } else {
            $http_status = 406;
            $response['success'] = false;
            $response['status'] = 'Unable to revoke COA';
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    public function get_deleted_coas() {
        // $http_status = 200;
        // $response = [
        //     'success' => true,
        //     'data' => $this->SOCOM_COA_model->get_deleted_coas()
        // ];

        // $this->output
        //     ->set_status_header($http_status)
        //     ->set_content_type(self::CONTENT_TYPE_JSON)
        //     ->set_output(json_encode($response));
    }

    public function get_selected_coa() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];

        $selected_coas = $post_data['selected_coas'] ?? [];

        $type_of_coa = $post_data['pom_cycle_type'] ?? 'ISS';

        $page_data['table_data'] = [];

        $title = [];

        $response = $this->SOCOM_COA_model->get_user_saved_coa_data($selected_coas);

        foreach( $response as  $coa_data) {
            $coa_info = json_decode($coa_data['COA_VALUES'], true);

            // get program ids
            $program_ids = [];
            if (!isset($coa_info['selected_programs'])) {
                $session = json_decode($coa_data['OVERRIDE_TABLE_SESSION'], true);
                foreach($session['coa_output'] as $row) {
                    if ($row['RESOURCE CATEGORY'] != 'Committed Grand Total $K' ) { 
                        $program_ids[] = covertToProgramId(
                            $type_of_coa,
                            [
                                'program_code' => $row['Program'] ?? '',
                                'cap_sponsor' => $row['CAP SPONSOR'] ?? '',
                                'pom_sponsor' => $row['POM SPONSOR'] ?? '',
                                'ass_area_code' => $row['ASSESSMENT AREA'] ?? '',
                                'execution_manager' => $row['EXECUTION MANAGER'] ?? '',
                                'resource_category' => $row['RESOURCE CATEGORY'] ?? '',
                                'eoc_code' => $row['EOC'] ?? '',
                                'osd_pe_code' => $row['OSD PE'] ?? '',
                                'event_name' => $row['EVENT NAME'] ?? ''
                            ]
                        );
                    }
                }
            }else {
                $program_ids = array_column($coa_info['selected_programs'], 'program_id');
            }

            $table_data = [];

            $visible_score_indexes = $this->get_visible_score_columns($coa_data['SAVED_COA_ID'],  $type_of_coa);
            if (!$this->SOCOM_COA_model->is_manual_override($coa_data['SAVED_COA_ID'])) {
                if ($type_of_coa === 'ISS_EXTRACT') {
                    $output_info = $this->SOCOM_COA_model->fetchOutputInfoIssExtract( $program_ids);
                    $resource_or_delta = 'DELTA_AMT';
                }
                else {
                    $output_info = json_decode($coa_data['CALC_BUDGET_VALUES'], true)['selected_programs'];
                    $resource_or_delta = 'RESOURCE_K';
                }
                $scores = $this->get_saved_coa_score($coa_data['SAVED_COA_ID'], $program_ids);
                $output_table_data = $this->get_coa_table_data($output_info, $scores, $type_of_coa, $resource_or_delta);
            }else {
                $output_info = json_decode($coa_data['OVERRIDE_TABLE_SESSION'], true);
                $output_table_data = $this->get_coa_table_manual_override_data($output_info['coa_output'],  $type_of_coa);
            }

            $headers = $this->get_merge_coa_table_headers($type_of_coa);

            $table_data['title'] = $coa_data['COA_TITLE'];
            $table_data['data'] = $output_table_data;
            $table_data['headers'] = $headers;
            $table_data['score_index'] = $headers;
            $title[] = $coa_data['COA_TITLE'];
            $table_data['id'] = $coa_data['SAVED_COA_ID'];
            $table_data['visible_score_columns'] =  array_values($visible_score_indexes);

            $page_data['table_data'][] = $table_data;
        }
        $page_data['title'] = 'Merging  <strong>' . implode("</strong> and <strong>", $title) . '</strong>';

        $this->load->view('SOCOM/dashboard/coa_management/coa_merge_table_view', $page_data);
    }

    private function get_merge_coa_table_headers($type_of_coa) {
        $year_headers = [];
        foreach($this->fy_years[$type_of_coa] as $year) {
            $year_headers[] = ['data' => $year, 'title' => $year];
        }

        $headers = $this->merged_COA_table_headers;

        if ($type_of_coa === 'ISS_EXTRACT') {
            $headers = array_merge(
                $headers,
                [
                    ['data' => 'EVENT_NAME', 'title' => 'Event Name']
                ]
            );
        }

        return array_merge(
            $headers,
            $year_headers,
            [ ['data' => 'FYDP_K', 'title' => 'FYDP $K'] ]
        );
    }


    private function get_visible_score_columns($saved_coa_id,  $type_of_coa) {
        // Get score options
        $optimizer_input = $this->SOCOM_COA_model->get_saved_coa_optimizer_input($saved_coa_id);

        if ($optimizer_input) {
            $weighted_score_option = strtoupper(performGeneralTenaryOp(
                $optimizer_input['storm_flag'],
                'storm',
                $this->weighted_score_option[$optimizer_input['option']]
            ));
        }
        else {
            $weighted_score_option = $this->SOCOM_COA_model->get_manual_override_weighted_score_option($saved_coa_id); 
        }

        // Get visible score columns
        $option_header_keys = $this->weighted_score_option_header_keys[strtoupper($weighted_score_option)];

        $headers = $this->get_merge_coa_table_headers($type_of_coa);

        return array_keys(array_filter( $headers, function($item) use ($option_header_keys) {
            return in_array($item['data'], $option_header_keys);
        }));
    }

    private function get_saved_coa_score($saved_coa_id, $program_ids) {
        $optimizer_input = $this->SOCOM_COA_model->get_saved_coa_optimizer_input($saved_coa_id);
        $weight_id = $optimizer_input['weight_id'];
        return $this->SOCOM_COA_model->get_weighted_score($weight_id, $program_ids, $type_of_coa);
    }

    private function get_coa_table_data($output_info, $scores, $type_of_coa, $resource_or_delta) {
        $output_data = [];
        foreach($output_info as $info) {
            $data = [];
            $data['ID'] = covertToProgramId(
                $type_of_coa,
                [
                    'program_code' => $info[$this->coa_table_key_map[$type_of_coa]['PROGRAM_CODE']] ?? '',
                    'cap_sponsor' => $info[$this->coa_table_key_map[$type_of_coa]['CAPABILITY_SPONSOR_CODE']] ?? '',
                    'pom_sponsor' => $info[$this->coa_table_key_map[$type_of_coa]['POM_SPONSOR_CODE']] ?? '',
                    'ass_area_code' => $info[$this->coa_table_key_map[$type_of_coa]['ASSESSMENT_AREA_CODE']] ?? '',
                    'execution_manager' => $info[$this->coa_table_key_map[$type_of_coa]['EXECUTION_MANAGER_CODE']] ?? '',
                    'resource_category' => $info[$this->coa_table_key_map[$type_of_coa]['RESOURCE_CATEGORY_CODE']] ?? '',
                    'eoc_code' => $info[$this->coa_table_key_map[$type_of_coa]['EOC_CODE']] ?? '',
                    'osd_pe_code' => $info[$this->coa_table_key_map[$type_of_coa]['OSD_PE']] ?? '',
                    'event_name' => $info[$this->coa_table_key_map[$type_of_coa]['EVENT_NAME']] ?? ''
                ]
            );
            $data['PROGRAM'] = $info[$this->coa_table_key_map[$type_of_coa]['PROGRAM_CODE']];
            $data['EOC'] = $info[$this->coa_table_key_map[$type_of_coa]['EOC_CODE']];
            $data['CAP_SPONSOR'] = $info[$this->coa_table_key_map[$type_of_coa]['CAPABILITY_SPONSOR_CODE']];
            $data['POM_SPONSOR'] = $info[$this->coa_table_key_map[$type_of_coa]['POM_SPONSOR_CODE']];
            $data['ASSESSMENT_AREA'] = $info[$this->coa_table_key_map[$type_of_coa]['ASSESSMENT_AREA_CODE']];
            $data['RESOURCE_CATEGORY'] = $info[$this->coa_table_key_map[$type_of_coa]['RESOURCE_CATEGORY_CODE']];
            $data['EXECUTION_MANAGER_CODE'] = $info[$this->coa_table_key_map[$type_of_coa]['EXECUTION_MANAGER_CODE']];
            $data['OSD_PE'] = $info[$this->coa_table_key_map[$type_of_coa]['OSD_PE']];
            $data['STORM_SCORE'] = $scores[$info[$this->coa_table_key_map[$type_of_coa]['ID']]]['total_storm_scores'];
            $data['POM_SCORE'] = $scores[$info[$this->coa_table_key_map[$type_of_coa]['ID']]]['weighted_pom_score'];
            $data['GUIDANCE_SCORE'] = $scores[$info[$this->coa_table_key_map[$type_of_coa]['ID']]]['weighted_guidance_score'];
            
            if ($type_of_coa === 'ISS_EXTRACT') {
                $data['EVENT_NAME'] = $info[$this->coa_table_key_map[$type_of_coa]['EVENT_NAME']];
            }

            $fydp_k = 0;
            foreach($this->fy_years[$type_of_coa] as $year) {
                $data[$year] = isset($info[$this->coa_table_key_map[$type_of_coa][$resource_or_delta]][$year]) ?
                                        $info[$this->coa_table_key_map[$type_of_coa][$resource_or_delta]][$year] : 0;
                $fydp_k += $data[$year];
            }

            $data['FYDP_K'] = $fydp_k;
            $output_data[] = $data;
        }

        return $output_data;
    }

    private function get_coa_table_manual_override_data($output_info, $type_of_coa) {
        $output_data = [];

        foreach($output_info as $info) {
            if ($info['RESOURCE CATEGORY'] !== 'Committed Grand Total $K') {
                $data = [];
                $data['ID'] = covertToProgramId(
                    $type_of_coa,
                    [
                        'program_code' => $info['Program'] ?? '',
                        'cap_sponsor' => $info['CAP SPONSOR'] ?? '',
                        'pom_sponsor' => $info['POM SPONSOR'] ?? '',
                        'ass_area_code' => $info['ASSESSMENT AREA'] ?? '',
                        'execution_manager' => $info['EXECUTION MANAGER'] ?? '',
                        'resource_category' => $info['RESOURCE CATEGORY'] ?? '',
                        'eoc_code' => $info['EOC'] ?? '',
                        'osd_pe_code' => $info['OSD PE'] ?? '',
                        'event_name' => $info['Event Name'] ?? ''
                    ]
                );

                $data['PROGRAM'] = $info['Program'];
                $data['EOC'] = $info['EOC'];
                $data['CAP_SPONSOR'] = $info['CAP SPONSOR'];
                $data['POM_SPONSOR'] = $info['POM SPONSOR'];
                $data['ASSESSMENT_AREA'] = $info['ASSESSMENT AREA'];
                $data['RESOURCE_CATEGORY'] = $info['RESOURCE CATEGORY'];
                $data['EXECUTION_MANAGER_CODE'] = $info['EXECUTION MANAGER'];
                $data['OSD_PE'] = $info['OSD PE'];
                $data['STORM_SCORE'] = isset($info['StoRM Score']) ? $info['StoRM Score'] : 0;
                $data['POM_SCORE'] = isset($info['POM Score']) ? $info['POM Score'] : 0;
                $data['GUIDANCE_SCORE'] = isset($info['Guidance Score']) ?  $info['Guidance Score'] : 0;

                if ($type_of_coa === 'ISS_EXTRACT') {
                    $data['EVENT_NAME'] = $info['Event Name'];
                }
                
                $fydp_k = 0;
                foreach($this->fy_years[$type_of_coa] as $year) {
                    $data[$year] = (int)$info[$year];
                    $fydp_k += $data[$year];
                }

                $data['FYDP_K'] = $fydp_k;
                $output_data[] = $data;
            }
        }

        return $output_data;
    }

    public function merge_coa() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        $http_status = 400;
        $response = ['message' => 'error'];
        if (!$data_check['result']) {
            $this->output->set_status_header($http_status);
            return;
        }

        $post_data = $data_check['post_data'];
        $use_iss_extract = filter_var($post_data['use_iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $type_of_coa = $use_iss_extract ? 'ISS_EXTRACT' : 'RC_T';
        $storm_flag = isset($post_data['coa_type']) && $post_data['coa_type'] === 'storm' ? true : false;

        $formatted_merge_coa_data = $this->format_merge_coa_data($post_data, $type_of_coa);

        if ($this->SOCOM_COA_model->merge_coa($formatted_merge_coa_data, $type_of_coa, $storm_flag)) {
            $http_status = 200;
            $response = ['message' => 'success'];
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }


    private function format_merge_coa_data($post_data, $type_of_coa) {

        $budget_uncommitted_rows = $post_data['budget_uncommitted'] ?? [];
        $selected_rows = isset($post_data['selected_rows']) ? json_decode($post_data['selected_rows'], true) : [];
        $post_data['selected_rows'] ?? [];
        $coa_name = $post_data['coa_name'] ?? [];
        $coa_description = $post_data['coa_description'] ?? [];
        $coa_type = $post_data['coa_type'] ?? [];
        $coa_ids = $post_data['coa_ids'] ?? [];
        $selected_rows_ids = [];

        $program_ids = [];
        foreach($coa_ids as  $coa_id) {
            if ($this->SOCOM_COA_model->is_manual_override($saved_coa_id)) {
                $session = json_decode(
                    $this->SOCOM_COA_model->get_manual_override_session($saved_coa_id)['OVERRIDE_TABLE_SESSION'], true
                );
                $program_ids = array_merge($program_ids, $session['ProgramIDs']);
            }
            else {
                $optimizer_input = $this->SOCOM_COA_model->get_saved_coa_optimizer_input($coa_id);
                $program_ids = array_merge($program_ids, $optimizer_input['ProgramIDs']);
            }
        }

        $merge_coa_data = [];
        $merge_coa_data['budget_uncommitted'] = [];
        $merge_coa_data['unselected_program_ids'] = [];
        $merge_coa_data['coa_output'] = [];
        $merge_coa_data['ProgramIDs'] = array_unique($program_ids);

        // get budget_uncommitted
        foreach($budget_uncommitted_rows as $budget) {
            if ($budget['HEADER'] != 'Committed') {
                $budget_info = [
                    'FYDP' => $budget['FYDP_K'],
                    'TYPE' => $budget['HEADER'] . ' $K'
                ];

                foreach($this->fy_years[$type_of_coa] as $fy_year) {
                    $budget_info[$fy_year] = $budget[$fy_year];
                }
                $merge_coa_data['budget_uncommitted'][] = $budget_info;
            }
        }

        //init grand total
        $grand_total = [
            "EOC" => "",
            "FYDP" => 0,
            "Program" => "",
            "DT_RowId" => null,
            "POM Score" => "",
            "CAP SPONSOR" => "",
            "POM SPONSOR" => "",
            'ASSESSMENT AREA' => "",
            "StoRM Score" => "",
            "Guidance Score" => "",
            "EXECUTION MANAGER" => "",
            "OSD PE" => "",
            "RESOURCE CATEGORY" => 'Committed Grand Total $K'
        ];

        if ($type_of_coa === 'ISS_EXTRACT') {
            $grand_total['Event Name'] = "";
            $grand_total['OSD PE'] = "";
        }
        
        foreach($this->fy_years[$type_of_coa] as $fy_year) {
            $grand_total[$fy_year] = 0;
        }

        foreach($selected_rows as $selected_row) {

            $row_id = $selected_row['PROGRAM'] . '_' . $selected_row['CAP_SPONSOR'] . '_' . $selected_row['POM_SPONSOR']
                . $selected_row['EOC'] . '_' . $selected_row['RESOURCE_CATEGORY'];
            $selected_rows_ids[] = covertToProgramId(
                $type_of_coa,
                [
                    'program_code' => $selected_row['PROGRAM'] ?? '',
                    'cap_sponsor' => $selected_row['CAP_SPONSOR'] ?? '',
                    'pom_sponsor' => $selected_row['POM_SPONSOR'] ?? '',
                    'ass_area_code' => $selected_row['ASSESSMENT_AREA'] ?? '',
                    'execution_manager' => $selected_row['EXECUTION_MANAGER_CODE'] ?? '',
                    'resource_category' => $selected_row['RESOURCE_CATEGORY'] ?? '',
                    'eoc_code' => $selected_row['EOC'] ?? '',
                    'osd_pe_code' => $selected_row['OSD_PE'] ?? '',
                    'event_name' => $selected_row['EVENT_NAME'] ?? '',
                ]
            );
            $selected_row_info = [
                'EOC' => $selected_row['EOC'],
                'FYDP' => $selected_row['FYDP_K'],
                'Program' => $selected_row['PROGRAM'],
                'DT_RowId' => $row_id,
                'POM Score' => $selected_row['POM_SCORE'],
                'CAP SPONSOR' => $selected_row['CAP_SPONSOR'],
                'POM SPONSOR' => $selected_row['POM_SPONSOR'],
                'ASSESSMENT AREA' => $selected_row['ASSESSMENT_AREA'],
                'StoRM Score' => $selected_row['STORM_SCORE'],
                'Guidance Score' => $selected_row['GUIDANCE_SCORE'],
                'RESOURCE CATEGORY' => $selected_row['RESOURCE_CATEGORY'],
                'EXECUTION MANAGER' => $selected_row['EXECUTION_MANAGER_CODE'],
                'OSD PE' => $selected_row['OSD_PE']
            ];

            if ($type_of_coa === 'ISS_EXTRACT') {
                $selected_row_info['Event Name'] = $selected_row['EVENT_NAME'];
            }

            foreach($this->fy_years[$type_of_coa] as $fy_year) {
                $selected_row_info[$fy_year] = $selected_row[$fy_year];
                $grand_total[$fy_year] += (int)$selected_row[$fy_year];
                $grand_total['FYDP'] += (int)$selected_row[$fy_year];
            }

            $merge_coa_data['coa_output'][] = $selected_row_info;
        }
        $merge_coa_data['coa_output'][] =  $grand_total;
        $merge_coa_data['unselected_program_ids'] = array_values(array_diff(
            $merge_coa_data['ProgramIDs'], $selected_rows_ids
        ));

        return [
            'USR_LOOKUP_USER_SAVED_COA' => [
                'COA_TITLE' => $coa_name,
                'COA_DESCRIPTION' => $coa_description,
                'COA_TYPE' => $coa_type,
                'STATE' => 'IN_PROGRESS'
            ],
            'USR_LOOKUP_SAVED_COA' => [
                'OVERRIDE_TABLE_SESSION' => json_encode($merge_coa_data),
                'OVERRIDE_TABLE_METADATA' => json_encode([
                    "coa_output" => [],
                    "budget_uncommitted" => []
                ]),
                'OVERRIDE_FORM_SESSION' => json_encode(["justification" => []])
            ]
        ];
    }
    public function get_proposed_budget() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        $http_status = 400;
        if (!$data_check['result']) {
            $this->output->set_status_header($http_status);
            return;
        }

        $post_data = $data_check['post_data'];
        $type_of_coa = $post_data['pom_cycle_type'] ?? 'ISS';
        $page_data['fy_years'] = $this->fy_years[$type_of_coa];

        $this->load->view('SOCOM/dashboard/coa_management/coa_merge_proposed_budget_view', $page_data);
    }
}
