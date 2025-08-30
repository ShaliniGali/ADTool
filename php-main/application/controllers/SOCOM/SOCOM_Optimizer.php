<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// require_once APPPATH . 'core/BaseUserGroup_Controller.php';

#[AllowDynamicProperties]
class SOCOM_Optimizer extends CI_Controller {
    protected const APPLICATION_JSON = 'application/json';
    
    /**
     * Optimizer constructor
     */
    public function __construct()
    {
        parent::__construct();
        if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }
        $this->load->model('SOCOM_Weights_model');
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_COA_model');
        $this->load->model('SOCOM_Storm_model');
        $this->load->model('SOCOM_Program_model');

        $criteria_name_id = get_criteria_name_id();
        $criteria = array_column(
            $this->SOCOM_Cycle_Management_model->get_terms_by_criteria_id($criteria_name_id),
            'CRITERIA_TERM'
        );
        
        $this->selected_weight_columns = array_combine($criteria, $criteria);
        $this->include_exclude_program_concat_fields = 
            "CONCAT_WS('_', PROGRAM_CODE, CAPABILITY_SPONSOR_CODE, RESOURCE_CATEGORY_CODE)";
    }

    // --------------------------------------------------------------------

	public function index()
	{
        $page_data['page_title'] = 'Optimizer';
        $page_data['page_tab'] = 'Optimizer';
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['select2.css',
        'carbon-light-dark-theme.css',
        'datatables.css',
        'jquery.dataTables.min.css',
        'responsive.dataTables.min.css',
        'SOCOM/socom_home.css','SOCOM/optimizer.css',
        'handsontable.min.css',
        'SOCOM/gear_percentage.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        // $page_data['user_group'] = $this->curr_user_group;
        $data = [];        
        $data['optimizer_weights'] = $this->SOCOM_Weights_model->get_user_weights();
        $data['default_criteria'] = array_column($this->SOCOM_model->get_option_criteria_names(), 'CRITERIA');
        $data['default_criteria_description'] = $this->SOCOM_model->get_option_criteria_names_and_description();
        $get_active_cycle_with_criteria = $this->DBs->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
        $data['get_active_cycle_with_criteria'] = $get_active_cycle_with_criteria;

        
        [$year, $year_list] = get_years_coa(false);
        $data['subapp_pom_year'] = $year;
        $data['subapp_pom_year_issue'] = get_years_coa(true)[0];

        $data['subapp_pom_year_list'] =  $year_list;
        $data['fy_list'] = json_encode($year_list);
        
        $this->load->view('templates/header_view', $page_data);
		$this->load->view('SOCOM/optimizer/index_view',$data);
        $this->load->view('templates/close_view');
	}

    /**
     * Added This as a temporary fix as we could not cover the exit functional line.
     * @codeCoverageIgnore
     */

    public function optimize($type_of_coa) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $budget = $post_data['budget'] ?? [];
            $weight_id = $post_data['weight_id'] ?? false;
            $must_include = json_decode($post_data['must_include'] ?? '[]', JSON_OBJECT_AS_ARRAY);
            $must_exclude = json_decode($post_data['must_exclude'] ?? '[]', JSON_OBJECT_AS_ARRAY);
            $budget = $post_data['budget'] ?? [];
            $syr = (int)$post_data['syr'] ?? date('Y');
            $eyr = (int)$post_data['eyr'] ?? date('Y');
            $option = (int)$post_data['option'] ?? 0;
            $support_all_years = (bool)$post_data['support_all_years'] ?? false;
            $storm_flag = $post_data['storm_flag'] === 'true' ? true : false;
            $per_resource_optimizer = filter_var($post_data['per_resource_optimizer'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $programs = json_decode($post_data['programs'], JSON_OBJECT_AS_ARRAY);
            $iss_extract = filter_var($post_data['iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $lookup_table = 'LOOKUP_PROGRAM';
            $endpoint_path = '/optimizer/calculate_budget';

            $use_event_name = $type_of_coa === 'RC_T' ? false : true;

            if ($storm_flag === true) {
                $programs = $this->DBs->SOCOM_Storm_model->get_program_ids($programs, $lookup_table);
                $score_ids = [];
            } else {
                $score_ids = $this->DBs->SOCOM_Weights_model->get_user_score_id_lists($programs, $type_of_coa);
            }
            $budgetTransformed = [];
            foreach($budget as $b){
                $budgetTransformed[] = intval($b);
            }

            $content = [
                "ProgramIDs"=> $programs,
                "must_include"=> $must_include,
                "must_exclude"=> $must_exclude,
                "budget"=> $budgetTransformed,
                "syr"=> $syr,
                "eyr"=> $eyr,
                "option"=> $option,
                "support_all_years"=> $support_all_years,
                "weight_id"=> intval($weight_id),
                "score_id"=> $score_ids,
                'storm_flag' =>  $storm_flag,
                'use_iss_extract' => $iss_extract,
                'per_resource_optimizer' => $per_resource_optimizer,
                'criteria_name_id' => get_criteria_name_id()
            ];

            if ($type_of_coa === 'RC_T') {
                $num_tranches = (int)$post_data['num_tranches'] ?? 1;
                $percent_allocation = $post_data['percent_allocation'] ?? [1];
                $tranches = $post_data['tranches'] ?? [1];
                $cut_by_percentage = $post_data['cut_by_percentage'] ?? 0;
                $keep_cutting = filter_var($post_data['keep_cutting'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $endpoint_path = '/optimizer/v2/calculate_budget';

                foreach ($tranches as $key => $value) {
                    $tranches[$key] = (float)$value;
                }
                foreach ($percent_allocation as $key => $value) {
                    $percent_allocation[$key] = (float)$value;
                }

                unset($content['support_all_years']);
                
                //new params
                $rct_params = [
                    'num_tranches' => $num_tranches,
                    'percent_allocation' => $percent_allocation,
                    'tranches' => $tranches,
                    'keep_cutting' => $keep_cutting
                ];

                if (empty($content['budget'])) {
                    $rct_params['cut_by_percentage'] = $cut_by_percentage;
                }
                
                $content = [
                    ...$rct_params,
                    ...$content
                ];
            }

            $calculate_budget_result = php_api_call(
                'POST',
                'Content-Type: ' . self::APPLICATION_JSON,
                json_encode($content),
                RHOMBUS_PYTHON_URL.$endpoint_path
            );

            
            $output = json_decode($calculate_budget_result, true); 

            $tranche_assignment = $output['tranche_assignment'] ?? [];
            $flat_tranche_assignment = [];

            foreach ($tranche_assignment as $secondLevelGroups) {
                foreach ($secondLevelGroups as $index => $group) {
                    if (!isset($flat_tranche_assignment[$index])) {
                        $flat_tranche_assignment[$index] = [
                            'program_id' => [],
                            'program_group' => []
                        ];
                    }
            
                    foreach ($group as $program) {
                        $flat_tranche_assignment[$index]['program_id'][] = $program['program_id'];
                        $flat_tranche_assignment[$index]['program_group'][] = $program['program_group'];
                    }
                }
            }

            $calculate_budget_result = json_decode($calculate_budget_result, true); 
            $calculate_budget_result['tranche_assignment'] = $flat_tranche_assignment;
            $calculate_budget_result= json_encode($calculate_budget_result, true);

            if ($output === null || isset($output['detail'])) {
                $id = false;
            } else {
                $outputf['filter']['filter_zero_resource_k'] = true;
                $outputf['model'] = $output;
                
                $filter_budget_result = php_api_call(
                    'POST',
                    'Content-Type: ' . self::APPLICATION_JSON,
                    json_encode($outputf),
                    RHOMBUS_PYTHON_URL.'/optimizer/filter_budget'
                );
                
                $output = json_decode($filter_budget_result, true);

                $output['tranche_assignment'] = $flat_tranche_assignment;
                
                if (isset(
                    $output['resource_k'],
                    $output['selected_programs'],
                    $output['remaining']
                )) {
                    $id = $this->SOCOM_COA_model->store_run(
                        json_encode([$content, $outputf]),
                        $calculate_budget_result,
                        $filter_budget_result,
                        $type_of_coa,
                        intval($storm_flag),
                    );

                    $app_version = $this->SOCOM_COA_model->get_api_version($id);
                } else {
                    $id = false;
                    $app_version = false;
                }
            }
            $http_status = 200;
        } else {
            $output = ['detail' => 'Unable to contact optimizer'];
            $id = false;
            $app_version = false;
            $http_status = 500;
        }

        $this->output
             ->set_status_header($http_status)
             ->set_content_type(self::APPLICATION_JSON)
             ->set_output(json_encode(['id' => (string)$id, 'api_version' => $app_version, 'coa' => $output]))
             ->_display();
        exit;
    }

    function proposed_cuts() {
        $this->form_validation->set_rules('percentage', 'To Cut', 'required|greater_than_equal_to[0]|less_than_equal_to[100]');

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        
        $http_status = 500;

        $result = [
            'status' => false,
            'message' => 'Unknown error when fetching Proposed Cuts $k',
            'data' => []
        ];

        if ($this->form_validation->run() !== FALSE && $data_check['result']) {
            $post_data = $data_check['post_data'];
            $percentage = (int)$post_data['percentage'] ?? 0;

            $result['data'] = $this->SOCOM_COA_model->get_to_cut($percentage);

            if (!empty($result['data'])) {
                $http_status = 200;
                $result['status'] = 'true';
                $result['message'] = 'Fetched Proposed Cuts %k';
            }
        } else {
            $result['status'] = false;
            $result['message'] = 'Unable to fetch Proposed Cuts $k';
        }

        $this->output
             ->set_status_header($http_status)
             ->set_content_type(self::APPLICATION_JSON)
             ->set_output(json_encode($result));
    }
}