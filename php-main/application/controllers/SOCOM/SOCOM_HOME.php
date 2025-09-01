<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_HOME extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_AOAD_model');
        $this->load->model('SOCOM_Program_model');
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Dynamic_Year_model');
        $this->load->model('DB_ind_model');
        $this->load->model('DBs');
        $this->load->library('SOCOM/Dynamic_Year');
        $this->load->library('SOCOM/RBAC_Users', null, 'rbac_users');
        $this->load->helper('auth_user_role_coa');

        $this->ZBT_YEAR = $this->dynamic_year->getPomYearForSubapp('ZBT_SUMMARY_YEAR');
        $this->ZBT_FY = $this->ZBT_YEAR % 100;
        $this->ZBT_YEAR_LIST = $this->dynamic_year->getYearList($this->ZBT_YEAR);

        $this->ISS_YEAR = $this->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');
        $this->ISS_FY = $this->ISS_YEAR % 100;
        $this->ISS_YEAR_LIST = $this->dynamic_year->getYearList($this->ISS_YEAR);

        $this->page_variables = [
            'zbt_summary' => [
                'page_title' =>  "ZBT",
                'breadcrumb_text' => "ZBT Summary",
                'page_summary_path' => "zbt_summary",
                'position' => [
                    $this->ZBT_FY . 'EXT' => 'base_k',
                    $this->ZBT_FY . 'ZBT_REQUESTED' => 'prop_amt',
                    $this->ZBT_FY . 'ZBT_REQUESTED_DELTA' => 'delta_amt'
                ]
            ],
            'issue' => [
                'page_title' =>  "Issue",
                'breadcrumb_text' => "Issue Summary",
                'page_summary_path' => "issue",
                'position' => [
                    $this->ISS_FY . 'EXT' => 'base_k',
                    $this->ISS_FY . 'ZBT' => 'prop_amt',
                    $this->ISS_FY . 'ZBT_DELTA' => 'delta_amt',
                    $this->ISS_FY . 'ISS_REQUESTED' => 'issue_prop_amt',
                    $this->ISS_FY . 'ISS_REQUESTED_DELTA' => 'issue_delta_amt',
                ]
            ]
        ];

        $this->l_cap_sponsor = ['AFSOC','AT&L', 'NSW', 'USASOC'];
        $this->l_pom_sponsor = ['AFSOC','AT&L', 'CROSS', 'MARSOC'];
        $this->l_ass_area = ['A','B','D'];
    }
    public function index() {
        $page_data['page_title'] = "SOCOM Home";
        $page_data['page_tab'] = "SOCOM Home";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['carbon-light-dark-theme.css','dashboard_block.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/home_view');
        $this->load->view('templates/close_view');
    }

    public function resource_constrained_coa()
    {
        if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }

        $is_guest = $this->rbac_users->is_guest();
		$is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $page_data['page_title'] = "SOCOM Resource Constrained COA";
        $page_data['page_tab'] = "SOCOM Resource Constrained COA";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['carbon-light-dark-theme.css','dashboard_block.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        $data = [];
        $get_active_cycle_with_criteria = $this->DBs->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
        $data['get_active_cycle_with_criteria'] = $get_active_cycle_with_criteria;
        
        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/resource_constrained_coa_view', $data);
        $this->load->view('templates/close_view');
    }

    public function zbt_summary() {
        // Check if dev bypass is enabled
        $dev_bypass_enabled = is_dev_bypass_enabled();
        
        // Only check authentication if dev bypass is not enabled
        if (!$dev_bypass_enabled) {
            if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
                $http_status = 403;
                $response['status'] = "Unauthorized user, access denied.";
                show_error($response['status'], $http_status);
            }
        }
        
        $page_data['page_title'] = "ZBT Summary";
        $page_data['page_tab'] = "ZBT Summary";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['select2.css','carbon-light-dark-theme.css','SOCOM/socom_home.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $page = 'zbt_summary';
        $cap_sponsor_results = $this->DBs->SOCOM_model->cap_sponsor_count($page);
        $cap_sponsor_count = $cap_sponsor_results['cap_sponsor_count'];
        $total_zbt_events = $cap_sponsor_results['total_events'];
        $cap_sponsor_dollar_results = $this->DBs->SOCOM_model->cap_sponsor_dollar($page);
        $cap_sponsor_dollar = $cap_sponsor_dollar_results['cap_sponsor_dollar'];
        $dollars_moved = $cap_sponsor_dollar_results['dollars_moved'];
        $net_change = $this->DBs->SOCOM_model->net_change($page);
        $dollars_moved_resource_category = $this->dollars_moved_resource_category_cross_join($page);
        $cap_sponsor_approve_reject = $this->DBs->SOCOM_model->cap_sponsor_approve_reject($page);
        $cap_sponsor_approve_reject_categories= $cap_sponsor_approve_reject['categories'];
        $cap_sponsor_approve_reject_series_data = $cap_sponsor_approve_reject['series_data'];

        [$pom_year, ]  = get_years_zbt_summary();
        $data['subapp_pom_year_zbt'] = $pom_year;
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/zbt_summary_view', array_merge([
            'cap_sponsor_count' => $cap_sponsor_count,
            'cap_sponsor_dollar' => $cap_sponsor_dollar,
            'total_events' => $total_zbt_events,
            'dollars_moved' => $dollars_moved,
            'net_change' => $net_change,
            'cap_sponsor_approve_reject_categories' => $cap_sponsor_approve_reject_categories,
            'cap_sponsor_approve_reject_series_data' => $cap_sponsor_approve_reject_series_data,
            'page' => 'ZBT'
        ], $dollars_moved_resource_category, $data
        ));
        $this->load->view('templates/close_view');
    }

    public function issue() {
        // Check if dev bypass is enabled
        $dev_bypass_enabled = is_dev_bypass_enabled();
        
        // Only check authentication if dev bypass is not enabled
        if (!$dev_bypass_enabled) {
            if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
                $http_status = 403;
                $response['status'] = "Unauthorized user, access denied.";
                show_error($response['status'], $http_status);
            }
        }
        
        $page_data['page_title'] = "Issue";
        $page_data['page_tab'] = "Issue";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['select2.css','carbon-light-dark-theme.css','SOCOM/socom_home.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $page = 'issue';
        $cap_sponsor_results = $this->DBs->SOCOM_model->cap_sponsor_count($page);
        $cap_sponsor_count = $cap_sponsor_results['cap_sponsor_count'];
        $total_events = $cap_sponsor_results['total_events'];
        $cap_sponsor_dollar_results = $this->DBs->SOCOM_model->cap_sponsor_dollar($page);
        $cap_sponsor_dollar = $cap_sponsor_dollar_results['cap_sponsor_dollar'];
        $dollars_moved = $cap_sponsor_dollar_results['dollars_moved'];

        $net_change = $this->DBs->SOCOM_model->net_change($page);
        $dollars_moved_resource_category = $this->dollars_moved_resource_category_cross_join($page);
        $cap_sponsor_approve_reject = $this->DBs->SOCOM_model->cap_sponsor_approve_reject($page);
        $cap_sponsor_approve_reject_categories= $cap_sponsor_approve_reject['categories'];
        $cap_sponsor_approve_reject_series_data = $cap_sponsor_approve_reject['series_data'];

        [$pom_year, ]  = get_years_issue_summary();
        $data['subapp_pom_year_issue'] = $pom_year;
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/issue_view', array_merge([
            'cap_sponsor_count' => $cap_sponsor_count,
            'cap_sponsor_dollar' => $cap_sponsor_dollar,
            'total_events' => $total_events,
            'dollars_moved' => $dollars_moved,
            'net_change' => $net_change,
            'cap_sponsor_approve_reject_categories' => $cap_sponsor_approve_reject_categories,
            'cap_sponsor_approve_reject_series_data' => $cap_sponsor_approve_reject_series_data,
            'page' => 'Issue'
        ], $dollars_moved_resource_category, $data
        ));
        $this->load->view('templates/close_view');
    }

    public function pb_comparison() {
        // Check if dev bypass is enabled
        $dev_bypass_enabled = is_dev_bypass_enabled();
        
        // Only check authentication if dev bypass is not enabled
        if (!$dev_bypass_enabled) {
            if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
                $http_status = 403;
                $response['status'] = "Unauthorized user, access denied.";
                show_error($response['status'], $http_status);
            }
        }
        
        $page_data['page_title'] = "PB Comparison";
        $page_data['page_tab'] = "PB Comparison";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['select2.css','carbon-light-dark-theme.css','SOCOM/socom_home.css','ion.rangeSlider.min.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $page = 'pb_comparison';

        // Return empty data structure to prevent database errors
        $graph_data = [
            'dashed_line' => [date('Y'), date('Y')+4],
            'data' => [],
            'categories' => []
        ];

        // Get capability sponsors from the actual data table for pb_comparison
        $capability_sponsor_codes = $this->DBs->SOCOM_model->get_capability_sponsor_code($page);
        $capability_sponsor = [];
        
        // Get the full sponsor information from lookup table for the codes found in data
        if (!empty($capability_sponsor_codes)) {
            $codes = array_column($capability_sponsor_codes, 'CAPABILITY_SPONSOR_CODE');
            $this->DBs->SOCOM_UI->select('SPONSOR_CODE, SPONSOR_TITLE');
            $this->DBs->SOCOM_UI->from('LOOKUP_SPONSOR');
            $this->DBs->SOCOM_UI->where('SPONSOR_TYPE', 'CAPABILITY');
            $this->DBs->SOCOM_UI->where_in('SPONSOR_CODE', $codes);
            $this->DBs->SOCOM_UI->order_by('SPONSOR_TITLE');
            $capability_sponsor = $this->DBs->SOCOM_UI->get()->result_array();
        }
        
        $pom_sponsor = [];
        $ass_area = $this->DBs->SOCOM_model->get_assessment_area_code();
        
        // Get program groups from the actual data table for pb_comparison
        $program_groups = $this->DBs->SOCOM_UI
            ->select('PROGRAM_GROUP')
            ->distinct()
            ->from('DT_PB_COMPARISON')
            ->where('PROGRAM_GROUP IS NOT NULL')
            ->order_by('PROGRAM_GROUP')
            ->get()
            ->result_array();
        
        $program = [];
        foreach ($program_groups as $group) {
            $program[] = ['PROGRAM_GROUP' => $group['PROGRAM_GROUP']];
        }
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;
        
        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/pb_comparison_view', [
            'page' =>  $page,
            'graphData' => $graph_data,
            'id' => 1,
            'capability_sponsor' => $capability_sponsor,
            'pom_sponsor' => $pom_sponsor,
            'ass_area' => $ass_area,
            'program' => $program,
            'resource_category' => [],
            'execution_manager_code' => [],
            'program_code' => [],
            'eoc_code' => [],
            'osd_pe_code' => [],
        ]);
        $this->load->view('templates/close_view');
    }

    public function update_program_filter() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_approval_status = ['PENDING', 'COMPLETED'];
            $l_pom_sponsor = $this->get_pom_sponsor_list();

            $page = isset($post_data['page']) ? $post_data['page'] : '';
            $section = isset($post_data['section']) ? $post_data['section'] : [];

            $program_list = [];

            if ($section == 'program_summary') {
                $filtered_program = $this->DBs->SOCOM_model->program_approval_status(
                    $page,
                    $l_pom_sponsor,
                    $l_cap_sponsor,
                    $l_ass_area,
                    $l_approval_status,
                    true
                );

                $unique_program_groups = [];
                $result = [];
                foreach ( $filtered_program as $item) {
                    if (!in_array($item['PROGRAM_GROUP'], $unique_program_groups)) {
                        $unique_program_groups[] = $item['PROGRAM_GROUP'];
                        $result[] = ['PROGRAM_GROUP' => $item['PROGRAM_GROUP']];
                    }
                }
                $program_list['data'] = $result;
            }
            else {
                if ($section == 'pb_comparison') {
                    $program_list['data'] = $this->DBs->SOCOM_model->get_program_list(
                        'DT_PB_COMPARISON', $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_execution_manager
                    );
                } elseif ($section == "budget_to_execution") {
                    $program_list['data'] = $this->DBs->SOCOM_model->get_program_list(
                        'DT_BUDGET_EXECUTION', $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_execution_manager
                    );
                }
            }
            
            $program_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($program_list))
                ->_display();
            exit();
        }
    }

    public function update_program_filter_by_group(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        $post_data = $data_check['post_data'];
        $ass_area = $post_data['ass-area'];
        $program_list['data'] = $this->SOCOM_Program_model->get_program_by_group($ass_area);
        $program_list['status'] = 'OK';

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($program_list))
            ->_display();
        exit();
    }

    public function get_all_program_group(){
        $program_list['data'] = $this->SOCOM_Program_model->get_program_by_group_all();
        $program_list['status'] = 'OK';

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($program_list))
            ->_display();
        exit();
    }

    public function get_resource_category_code() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        $post_data = $data_check['post_data'];
        $program_group = $post_data['program-group'];
        $resource_category_list['data'] = $this->SOCOM_Program_model->get_resource_category_code_by_program_group($program_group);
        $resource_category_list['status'] = 'OK';

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($resource_category_list))
            ->_display();
        exit();
    }

    public function get_capability_sponsor() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        $post_data = $data_check['post_data'];
        $program_group = $post_data['program-group'];
        $resource_category = $post_data['resource-category'];
        $resource_category_list['data'] = $this->SOCOM_Program_model->get_cap_sonsor_code_by_resource_category_code_program_group($resource_category, $program_group);
        $resource_category_list['status'] = 'OK';

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($resource_category_list))
            ->_display();
        exit();
    }


    public function update_resource_category_filter_pb() {
        $this->update_resource_category_filter("pb_comparison");
    }

    public function update_resource_category_filter_be() {
        $this->update_resource_category_filter("budget_to_execution");
    }

    public function update_program_filter_be() {
        $this->update_program_filter("budget_to_execution");
    }

    public function filter($page, $type, $action) {
        if ($page === 'budget_to_execution' && $type === 'program' && $action === 'update') {
            $this->update_program_filter("budget_to_execution");
        } elseif ($page === 'pb_comparison' && $type === 'program' && $action === 'update') {
            $this->update_program_filter("pb_comparison");
        } else {
            show_404();
        }
    }

    public function update_resource_category_filter($section) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_program = isset($post_data['program']) ? json_decode($post_data['program'], true) : [];
            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_program_name = isset($post_data['program-name']) ? json_decode($post_data['program-name'], true) : [];
            $l_eoc_code = isset($post_data['eoc-code']) ? json_decode($post_data['eoc-code'], true) : [];

            $resource_category_list = [];
            if ($section == "pb_comparison") {
                $resource_category_list['data'] = $this->DBs->SOCOM_model->get_resource_category_list(
                    "DT_PB_COMPARISON", $l_cap_sponsor, $l_ass_area, $l_program, $l_execution_manager, $l_program_name, $l_eoc_code
                );
            }
            elseif ($section == "budget_to_execution") {
                $resource_category_list['data'] = $this->DBs->SOCOM_model->get_resource_category_list(
                    "DT_BUDGET_EXECUTION", $l_cap_sponsor, $l_ass_area, $l_program, $l_execution_manager, $l_program_name, $l_eoc_code
                );
            }
            $resource_category_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($resource_category_list))
                ->_display();
            exit();
        }
    }

    public function update_execution_manager_filter_pb() {
        $this->update_execution_manager_filter("pb_comparison");
    }

    public function update_execution_manager_filter_be() {
        $this->update_execution_manager_filter("budget_to_execution");
    }

    public function update_execution_manager_filter($section) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];

            $execution_manager_list = [];
            if ($section == "pb_comparison") {
                $execution_manager_list['data'] = $this->DBs->SOCOM_model->get_execution_manager_list(
                    "DT_PB_COMPARISON", $l_cap_sponsor, $l_ass_area
                );
            }
            elseif ($section == "budget_to_execution") {
                $execution_manager_list['data'] = $this->DBs->SOCOM_model->get_execution_manager_list(
                    "DT_BUDGET_EXECUTION", $l_cap_sponsor, $l_ass_area
                );
            }
            $execution_manager_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($execution_manager_list))
                ->_display();
            exit();
        }
    }

    public function update_program_name_filter_pb() {
        $this->update_program_name_filter("pb_comparison");
    }

    public function update_program_name_filter_be() {
        $this->update_program_name_filter("budget_to_execution");
    }

    public function update_program_name_filter($section) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_program = isset($post_data['program']) ? json_decode($post_data['program'], true) : [];

            $program_code_list = [];
            if ($section == "pb_comparison") {
                $program_code_list['data'] = $this->DBs->SOCOM_model->get_program_name_list(
                    "DT_PB_COMPARISON", $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program
                );
            }
            elseif ($section == "budget_to_execution") {
                $program_code_list['data'] = $this->DBs->SOCOM_model->get_program_name_list(
                    "DT_BUDGET_EXECUTION", $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program
                );
            }
            $program_code_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($program_code_list))
                ->_display();
            exit();
        }
    }

    public function update_eoc_code_filter_pb() {
        $this->update_eoc_code_filter("pb_comparison");
    }

    public function update_eoc_code_filter_be() {
        $this->update_eoc_code_filter("budget_to_execution");
    }

    public function update_eoc_code_filter($section) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_program = isset($post_data['program']) ? json_decode($post_data['program'], true) : [];
            $l_program_name = isset($post_data['program-name']) ? json_decode($post_data['program-name'], true) : [];

            $eoc_code_list = [];
            if ($section == "pb_comparison") {
                $eoc_code_list['data'] = $this->DBs->SOCOM_model->get_eoc_code_list(
                    "DT_PB_COMPARISON", $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program, $l_program_name
                );
            }
            elseif ($section == "budget_to_execution") {
                $eoc_code_list['data'] = $this->DBs->SOCOM_model->get_eoc_code_list(
                    "DT_BUDGET_EXECUTION", $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program, $l_program_name
                );
            }
            $eoc_code_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($eoc_code_list))
                ->_display();
            exit();
        }
    }

    public function update_osd_pe_filter_pb() {
        $this->update_osd_pe_filter("pb_comparison");
    }

    public function update_osd_pe_filter_be() {
        $this->update_osd_pe_filter("budget_to_execution");
    }

    public function update_osd_pe_filter($section) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_program = isset($post_data['program']) ? json_decode($post_data['program'], true) : [];
            $l_program_name = isset($post_data['program-name']) ? json_decode($post_data['program-name'], true) : [];
            $l_eoc_code = isset($post_data['eoc-code']) ? json_decode($post_data['eoc-code'], true) : [];
            $l_resource_category = isset($post_data['resource_category']) ? $post_data['resource_category'] : [];

            $osd_pe_list = [];
            if ($section == "pb_comparison") {
                $osd_pe_list['data'] = $this->DBs->SOCOM_model->get_osd_pe_list(
                    "DT_PB_COMPARISON", $l_cap_sponsor, $l_ass_area, $l_execution_manager,
                    $l_program, $l_program_name, $l_eoc_code, $l_resource_category
                );
            }
            elseif ($section == "budget_to_execution") {
                $osd_pe_list['data'] = $this->DBs->SOCOM_model->get_osd_pe_list(
                    "DT_BUDGET_EXECUTION", $l_cap_sponsor, $l_ass_area, $l_execution_manager,
                    $l_program, $l_program_name, $l_eoc_code, $l_resource_category
                );
            }
            $osd_pe_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($osd_pe_list))
                ->_display();
            exit();
        }
    }

    public function update_pb_comparison_graph() {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $program = isset($post_data['program']) ? $post_data['program'] : [];
            $resource_category = isset($post_data['resource_category']) ? $post_data['resource_category'] : [];

            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_program_name = isset($post_data['program-name']) ? $post_data['program-name'] : [];
            $l_eoc_code = isset($post_data['eoc-code']) ? $post_data['eoc-code'] : [];
            $l_osd_pe = isset($post_data['osd-pe']) ? $post_data['osd-pe'] : [];

            $graphData = $this->get_pb_comparison_data(
                $l_cap_sponsor, $l_ass_area, $program, $resource_category, 
                $l_execution_manager, $l_program_name, $l_eoc_code, $l_osd_pe
            );

            $graphData['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($graphData))
                ->_display();
            exit();
        }
    }

    public function get_pb_comparison_data(
        $l_cap_sponsor=[], $l_ass_area=[], $program=[], $resource_category=[],
        $l_execution_manager=[], $l_program_name=[], $l_eoc_code=[], $l_osd_pe=[]
    ) {
        $pb_sum = [];
        
        $graph_data['dashed_line'] = $this->SOCOM_model->get_pb_comparison_dashed_line();
        if (isset($graph_data['dashed_line']['detail'])) {
            log_message('error', sprintf('DASHED LINE ENDPOINT ERROR: %s', $graph_data['dashed_line']['detail']));
            $graph_data['dashed_line'][0] = date('Y');
            $graph_data['dashed_line'][1] = $graph_data['dashed_line'][0]+4;
        }
        $year_list = [];
        $min_fiscal_year = $this->SOCOM_model->get_pb_comparison_min_fiscal_year();
        
        // Add error handling for missing data
        if (!$min_fiscal_year) {
            log_message('error', 'No minimum fiscal year found in DT_PB_COMPARISON table');
            // Return empty data structure to prevent errors
            return [
                'dashed_line' => [date('Y'), date('Y')+4],
                'data' => [],
                'categories' => []
            ];
        }
        
        foreach(range(substr($min_fiscal_year, 2), substr(max($graph_data['dashed_line']), 2)) as $yl) {
            $year_list[] = $yl;
        }
        
        // Add validation to ensure we only query for years that exist in the database
        $available_years = ['24', '25', '26', '27', '28', '29', '30']; // Based on table structure
        $year_list = array_intersect($year_list, $available_years);
        
        if (empty($year_list)) {
            log_message('error', 'No valid years found for PB comparison');
            return [
                'dashed_line' => [date('Y'), date('Y')+4],
                'data' => [],
                'categories' => []
            ];
        }
        
        $pb_sum = $this->SOCOM_model->get_pb_comparison_sum(
            $year_list, $l_cap_sponsor, $l_ass_area, $program, $resource_category,
            $l_execution_manager, $l_program_name, $l_eoc_code, $l_osd_pe
        );

        $years = array_column($pb_sum, 'FISCAL_YEAR');

        $graph_data['data'] = $this->format_pb_comparison_sum($pb_sum, $years, $year_list);
        $graph_data["categories"] = $years;
        return  $graph_data;
    }

    private function format_pb_comparison_sum($data, $years, $fy_list) {
        // Initialize the result array
        $result = [];
        $init_array = array_fill(0, count($years), null);
        foreach($fy_list as $fy) {
            $result['SUM_PB_'.$fy] = [
                'name' =>  "PB 20$fy",
                'data' => $init_array
            ];
        }

        // iterate each row of the data
        foreach($data as $idx => $row) {
            foreach ($row as $key => $value) {
                //set data to the result by each data point and fiscal year
                if (strpos($key, 'SUM_PB_') === 0) {
                    $result[$key]['data'][$idx] = $value;
                }
            }
        }

        // convert each value to integer
        foreach($result as &$value) {
            foreach($value['data'] as $idx => $val) {
                if (!is_null($val)) {
                    $value['data'][$idx] = intval($val);
                }
            }
        }

        return array_values($result);
    }
    
    public function budget_to_execution() {
        // Check if dev bypass is enabled
        $dev_bypass_enabled = is_dev_bypass_enabled();
        
        // Only check authentication if dev bypass is not enabled
        if (!$dev_bypass_enabled) {
            if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
                $http_status = 403;
                $response['status'] = "Unauthorized user, access denied.";
                show_error($response['status'], $http_status);
            }
        }
        
        $page_data['page_title'] = "Budget to Execution";
        $page_data['page_tab'] = "Budget to Execution";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = ['select2.css','carbon-light-dark-theme.css','SOCOM/socom_home.css','ion.rangeSlider.min.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $page = 'budget_to_execution';

        $data = $this->DBs->SOCOM_model->get_budget_and_execution_sum();

        $formmatted_data = $this->format_sum_budget_and_execution($data);

        $graph_data = [];
        $graph_data['data'] =  $formmatted_data['data'];
        $graph_data["categories"] =  $formmatted_data['years'];

        // Get capability sponsors from the actual data table for budget_to_execution
        $capability_sponsor_codes = $this->DBs->SOCOM_UI
            ->select('CAPABILITY_SPONSOR_CODE')
            ->distinct()
            ->from('DT_BUDGET_EXECUTION')
            ->where('CAPABILITY_SPONSOR_CODE IS NOT NULL')
            ->order_by('CAPABILITY_SPONSOR_CODE')
            ->get()
            ->result_array();
        
        $capability_sponsor = [];
        if (!empty($capability_sponsor_codes)) {
            $codes = array_column($capability_sponsor_codes, 'CAPABILITY_SPONSOR_CODE');
            $this->DBs->SOCOM_UI->select('SPONSOR_CODE, SPONSOR_TITLE');
            $this->DBs->SOCOM_UI->from('LOOKUP_SPONSOR');
            $this->DBs->SOCOM_UI->where('SPONSOR_TYPE', 'CAPABILITY');
            $this->DBs->SOCOM_UI->where_in('SPONSOR_CODE', $codes);
            $this->DBs->SOCOM_UI->order_by('SPONSOR_TITLE');
            $capability_sponsor = $this->DBs->SOCOM_UI->get()->result_array();
        }
        
        $pom_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM');
        $ass_area = $this->DBs->SOCOM_model->get_assessment_area_code();
        
        // Get program groups from the actual data table for budget_to_execution
        $program_groups = $this->DBs->SOCOM_UI
            ->select('PROGRAM_GROUP')
            ->distinct()
            ->from('DT_BUDGET_EXECUTION')
            ->where('PROGRAM_GROUP IS NOT NULL')
            ->order_by('PROGRAM_GROUP')
            ->get()
            ->result_array();
        
        $program = [];
        foreach ($program_groups as $group) {
            $program[] = ['PROGRAM_GROUP' => $group['PROGRAM_GROUP']];
        }
        
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;
        
        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/budget_to_execution_view', [
            'page' =>  $page,
            'graphData' => $graph_data,
            'id' => 1,
            'capability_sponsor' => $capability_sponsor,
            'pom_sponsor' => $pom_sponsor,
            'ass_area' => $ass_area,
            'program' => $program,
            'resource_category' => []
        ]);
        $this->load->view('templates/close_view');
    }

    public function update_budget_to_execution_graph() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $program = isset($post_data['program']) ? $post_data['program'] : [];
            $resource_category = isset($post_data['resource_category']) ? $post_data['resource_category'] : [];

            $l_execution_manager = isset($post_data['execution-manager']) ? $post_data['execution-manager'] : [];
            $l_program_name = isset($post_data['program-name']) ? $post_data['program-name'] : [];
            $l_eoc_code = isset($post_data['eoc-code']) ? $post_data['eoc-code'] : [];
            $l_osd_pe = isset($post_data['osd-pe']) ? $post_data['osd-pe'] : [];

            $data = $this->DBs->SOCOM_model->get_budget_and_execution_sum(
                $l_cap_sponsor, $l_ass_area, $program, $resource_category,
                $l_execution_manager, $l_program_name, $l_eoc_code, $l_osd_pe
            );

            $formmatted_data = $this->format_sum_budget_and_execution($data);
    
            $graph_data = [];
            $graph_data['data'] =  $formmatted_data['data'];
            $graph_data["categories"] =  $formmatted_data['years'];
            $graph_data['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($graph_data))
                ->_display();
            exit();
        }
    }

    private function format_sum_budget_and_execution($data) {
        $years = [];

        $sum_budget = [];
        $sum_execution = [];
        $sum_enacted = [];
        foreach($data as $value) {
            $years [] = $value['FISCAL_YEAR'];
            $sum_budget [] = $value['SUM_BUDGET'] === null ? null : (int)$value['SUM_BUDGET'] ;
            $sum_execution [] =$value['SUM_EXECUTION'] === null ? null : (int)$value['SUM_EXECUTION'] ;
            $sum_enacted [] = $value['SUM_ENACTED'] === null ? null : (int)$value['SUM_ENACTED'];
        }

        return [
            'years' => $years,
            'data' =>  [
               [
                    'name' => 'BUDGET',
                    'data' =>  $sum_budget
               ],
               [
                    'name' => 'EXECUTION',
                    'data' =>  $sum_execution
               ],
               [
                    'name' => 'ENACTED',
                    'data' =>  $sum_enacted
               ],
            ]
        ];
    }

    public function program_summary($page) {

        $page_data['page_title'] = "Program Summary";
        $page_data['page_tab'] = "Program Summary";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
            'select2.css',
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'SOCOM/socom_home.css'
        ];

        $data_check = $this->DB_ind_model->validate_post($this->input->get()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $selected_capability_sponsor = $post_data['cs'] ?? '';
            $selected_ass_area = $post_data['ass-area'] ?? '';
            $selected_program_group = $post_data['program-group'] ?? '';
            $selected_program_code = $post_data['program-code'] ?? '';

            if ($selected_program_code) {
                $selected_program = $this->DBs->SOCOM_model->get_program_name($selected_program_code)[0]['PROGRAM_NAME'];
            }

        } else {
            $selected_capability_sponsor = '';
            $selected_ass_area = '';
            $selected_program_group = '';
            $selected_program_code = '';
            $selected_program = '';
        }

        $capability_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'CAPABILITY');
        $pom_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM');
        $ass_area = $this->DBs->SOCOM_model->get_assessment_area_code();
        $table_data['data'] = [
            'program_summary_data' => []
        ];
        $table_data['capability_sponsor'] = $capability_sponsor;
        $table_data['pom_sponsor'] = $pom_sponsor;
        $table_data['ass_area'] = $ass_area;
        $table_data['total_events'] = 0;
        $table_data['dollars_moved'] = 0;
        $table_data['net_change'] = 0;
        $table_data['page'] = $page;
        $table_data['page_title'] = $this->page_variables[$page]['page_title'];
        $table_data['page_summary_path'] = $this->page_variables[$page]['page_summary_path'];
        $table_data['breadcrumb_text'] = $this->page_variables[$page]['breadcrumb_text'];
        $table_data['id'] = 1;
        $table_data['user_emails'] = $this->SOCOM_Users_model->get_users();
        $table_data['selected'] = [
            'ass_area' =>  $selected_ass_area,
            'cs' =>  $selected_capability_sponsor,
            'program_group' =>  $selected_program_group,
            'program_code' => $selected_program_code,
            'program' => $selected_program
        ]; 
        if ($page == 'zbt_summary'){
            [$data['subapp_pom_year'], $year_list] = get_years_zbt_summary();
        }
        else if($page == 'issue'){
            [$data['subapp_pom_year'], $year_list] = get_years_issue_summary();
        }
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;
        
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $this->load->view('templates/header_view', $page_data);

        $this->load->view('SOCOM/program_summary_view', array_merge($table_data, $data));
        $this->load->view('templates/close_view');
    }

    public function update_program_summary_table($page) {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $l_pom_sponsor = $this->get_pom_sponsor_list();
            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $programs = isset($post_data['program']) ? $post_data['program'] : [];
            $programs = isset($post_data['selected_all_programs']) ? [] : $programs;
            $is_manual_changes = $post_data['is_manual_changes'] ?? false;


            // Ensure programs is limited to 5 filters if user bypasses the check_filter_limit on the UI
            $tags = $this->DBs->SOCOM_model->get_user_assigned_tag('LOOKUP_TAG');
            $bins = $this->DBs->SOCOM_model->get_user_assigned_bin('LOOKUP_JCA');

            $get_program_summary_function = 'get_' . $page . '_program_summary';
            $program_summary = $this->DBs->SOCOM_model->$get_program_summary_function(
                $l_pom_sponsor,
                $l_cap_sponsor,
                $l_ass_area,
                $programs,
                $is_manual_changes
            );

            if (isset($program_summary['message'])) {
                $this->output->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($program_summary))
                    ->_display();
                exit();
            }

            $filters = [
                'pom_sponsor' => $l_pom_sponsor,
                'cap_sponsor' => $l_cap_sponsor,
                'ass_area' => $l_ass_area
            ];

            $data = $this->format_program_summary_data($program_summary, $tags, $bins, $page, $filters);
            $this->output->set_status_header(200)
                ->set_content_type('text/html')
                ->set_output(
                    $this->load->view('SOCOM/program_table_view', $data['program_summary_data'], true)
                )
                ->_display();
            exit();
        }
    }
    public function save_program_summary_table($page) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $l_pom_sponsor = $this->get_pom_sponsor_list();
            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $programs = isset($post_data['program']) ? $post_data['program'] : [];
            $programs = isset($post_data['selected_all_programs']) ? [] : $programs;
            $is_manual_changes = $post_data['is_manual_changes'] ?? false;

            $get_program_summary_function = 'get_' . $page . '_program_summary';
            $response = $this->DBs->SOCOM_model->$get_program_summary_function(
                $l_pom_sponsor,
                $l_cap_sponsor,
                $l_ass_area,
                $programs,
                $is_manual_changes
            );

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->_display();
            exit();
        }
    }

    public function update_program_summary_card($page) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $l_pom_sponsor = $this->get_pom_sponsor_list();
            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_approval_status = isset($post_data['approval-status']) ? $post_data['approval-status'] : [];
            $programs = isset($post_data['program']) ? $post_data['program'] : [];

            if (!empty($programs) && !empty($l_approval_status)) {
                $summary_card_function = $page . '_program_summary_card';
                $summary = $this->DBs->SOCOM_model->$summary_card_function(
                    $page,
                    $l_pom_sponsor,
                    $l_cap_sponsor,
                    $l_ass_area,
                    $l_approval_status,
                    $programs
                );
            }
            else {
                $summary = [
                    'total_events' => 0,
                    'dollars_moved' => 0,
                    'net_change' => 0
                ];
            }

            $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
            $this->load->view('SOCOM/program_summary_card_view', [
                'page_title' => $this->page_variables[$page]['page_title'],
                'total_events' => $summary['total_events'],
                'dollars_moved' => $summary['dollars_moved'],
                'net_change' => $summary['net_change']
            ]);
        }
    }

    private function group_program_summary_data($page, $program_summary) {
        $groupped_data = [];
        foreach($program_summary as $program_info) {

            $base_program_name = $program_info['PROGRAM_NAME'];
            $eoc_code = implode(',<br/>', $program_info['EOC_CODES']);
            $jca_alignment = implode(',<br/>', explode(', ' ,$program_info['JCA_ALIGNMENT'][0]));
            $approval_action_status = $program_info['APPROVAL_ACTION_STATUS'];

            $groupped_program_info = [];

            foreach($program_info['RESOURCE_K'] as $position => $resource_k) {
                $is_base_k = $this->page_variables[$page]['position'][$position] === 'base_k';
                $groupped_program_info[$this->page_variables[$page]['position'][$position]] = [
                    'tag' => '',
                    'bin' => $is_base_k ? $jca_alignment : '',
                    'eoc' => $is_base_k ? $eoc_code : '',
                    'position' => str_replace('_', ' ', $position)
                ] + $program_info['RESOURCE_K'][$position];

                if ($is_base_k) {
                    $groupped_program_info[$this->page_variables[$page]['position'][$position]]['status']
                        = $approval_action_status;
                }

                //fill missing fy as 0
                $missing_fys = array_diff(
                    explode(', ' , $program_info['FISCAL_YEARS']) , 
                    array_keys($program_info['RESOURCE_K'][$position])
                );
                foreach($missing_fys as $fy) {
                    $groupped_program_info[$this->page_variables[$page]['position'][$position]][$fy] = 0;
                }
            }
            $groupped_data[$base_program_name] = $groupped_program_info;
        }
        return  $groupped_data;
    }

    public function format_program_summary_data($program_summary, $tags, $bins, $page, $filters) {
        $is_zbt = $page ==='zbt_summary';
        $year_list = ($is_zbt) ? $this->ZBT_YEAR_LIST : $this->ISS_YEAR_LIST;
        $grouped_summary_data = $this->group_program_summary_data($page, $program_summary); 

        $headers = [
            [
                'data' => 'program',
                'title' => 'Program'
            ],
            [
                'data' => 'eoc',
                'title' => 'EOC'
            ],
            [
                'data' => 'bin',
                'title' => 'JCA Alignment'
            ],
            [
                'data' => 'position',
                'title' => 'POM Position',
                'className' => 'editable'
            ]
        ];
        $editor_columns = [
            [
                'name' => 'position',
                'text' => 'POM Position'
            ]
        ];
        foreach($year_list as $year) {
            $headers[] = [
                'data' => $year,
                'title' => $year,
                'className' => 'editable'
            ];
            $editor_columns[] = [
                'name' => $year,
                'text' => $year
            ];
        }
        $headers = array_merge($headers, [
                [
                    'data' => 'fydp',
                    'title' => 'FYDP'
                ],
                [
                    'data' => 'historical',
                    'title' => 'Historical POM Data'
                ],
                [
                    'data' => 'summary',
                    'title' => 'EOC Summary'
                ],
                [
                    'data' => 'status',
                    'title' => 'Approval Action Status'
                ],
                [
                    'data' => "DT_RowId",
                    'title' => "DT_RowId"
                ], 
                [
                    'data' => "r_visibility",
                    'title' => "Visibility"
                ]
            ]
        );
        $rowspan = ($is_zbt) ? 3 : 5;
        $data = $this->format_datatable_program_summary($page, $grouped_summary_data, $year_list, 1, null, $rowspan);
        $year_list[] = 'fydp';
        $zbt_page_length = [[15, 30, 60, 90, -1],[15, 30, 60, 90, 'All']];
        $default_page_length = [[10, 25, 50, 100, -1],[10, 25, 50, 100, 'All']];
        return [
            'program_summary_data' => [
                'data' => $data,
                'headers' => $headers,
                'indexOfYear' => 3,
                'yearIndex' => [3, 4, 5, 6, 7, 8],
                'yearList' => $year_list,
                'editor_columns' =>  $editor_columns,
                'rowspan' => $rowspan,
                'rowPerPage' => ($is_zbt) ? 15 : 10,
                'lengthMenu' => ($is_zbt) ? $zbt_page_length : $default_page_length
            ]
        ];
    }

    private function dollars_moved_resource_category_cross_join($page = null, $filter = null) {

        $dollars_moved_resource_category = $this->DBs->SOCOM_model->dollars_moved_resource_category_cross_join(
            $page,
            $filter
        );

        return [
            'dollars_move_fiscal_years' => $dollars_moved_resource_category['fiscal_years'],
            'dollars_move_series_data' =>$dollars_moved_resource_category['series_data']
        ];
    }

    private function dollars_moved_resource_category($page = null, $filter = null) {

        $dollars_moved_resource_category = $this->DBs->SOCOM_model->dollars_moved_resource_category(
            $page,
            $filter
        );

        return [
            'dollars_move_fiscal_years' => $dollars_moved_resource_category['fiscal_years'],
            'dollars_move_series_data' =>$dollars_moved_resource_category['series_data']
        ];
    }

    public function get_dollars_moved_resource_category() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $filter = isset($post_data['filter']) ? $post_data['filter'] : null;
            $page = isset($post_data['page']) ? $post_data['page'] : null;
            
            $dollars_moved_resource_data = $this->dollars_moved_resource_category($page, $filter);
            $dollars_moved_resource_data['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($dollars_moved_resource_data))
                ->_display();
            exit();
        }
    }
    private function group_by_program_name($summary_data, $tags, $bins, $page, $year='26', $hasDropdown = true, $filters=[]) {
        $grouped_data = [];
        $base_k = $summary_data['base_k'];
        $approval_status = $this->format_approval_by_program_name($summary_data['approval_status'] ?? []);
        $fy = [];

        foreach($base_k as $index => $values) {
            if (!count($fy) && isset($values['FISCAL_YEARS'])) {
                $fy = explode(', ', $values['FISCAL_YEARS']);
            }

            $tag_dropdown = $this->generate_tag_dropdown($tags, $index, $values['PROGRAM_NAME']);
            $eoc_value = $this->generate_eoc_value($values['PROGRAM_NAME'], $filters, $page);
            $bin_dropdown = $this->generate_bin_dropdown($bins, $index, $values['PROGRAM_NAME']);
            $base_program_name = $values['PROGRAM_NAME'];
            $base_value = intval($values['BASE_K']) ?? 0;
            $prop_value = isset($summary_data['prop_amt'][$index]) ? intval($summary_data['prop_amt'][$index]['PROP_AMT']) : 0;
            $delta_value = isset($summary_data['delta_amt'][$index]) ? $summary_data['delta_amt'][$index]['DELTA_AMT'] : 0;
            $base_position = $values["{$year}EXT"] ?? '';
            $prop_position = isset($summary_data['prop_amt'][$index]) ? $summary_data['prop_amt'][$index]["{$year}ZBT_REQUESTED"] : '';
            $delta_position = isset($summary_data['delta_amt'][$index]) ? $summary_data['delta_amt'][$index]["{$year}ZBT_REQUESTED_DELTA"] : '';

            $program_approval_status = $approval_status[$base_program_name] ?? "COMPLETED";
            if ($page == 'issue' || $page == 'historical') {
                $issue_prop_position = isset($summary_data['issue_prop_amt'][$index]) ? $summary_data['issue_prop_amt'][$index]["{$year}ISS_REQUESTED"] : '';
                $issue_delta_position = isset($summary_data['issue_delta_amt'][$index]) ? $summary_data['issue_delta_amt'][$index]["{$year}ISS_REQUESTED_DELTA"] : '';
                $issue_prop_value = isset($summary_data['issue_prop_amt'][$index]) ? intval($summary_data['issue_prop_amt'][$index]['ISS_PROP_AMT']) : 0;
                $issue_delta_value = isset($summary_data['issue_delta_amt'][$index]) ? intval($summary_data['issue_delta_amt'][$index]['DELTA_AMT']) : 0;
            }

            if ($page == 'historical') {
                $pom_prop_position = isset($summary_data['pom_prop_amt'][$index]) ? $summary_data['pom_prop_amt'][$index]["{$year}POM_REQUESTED"] : '';
                $pom_delta_position = isset($summary_data['pom_delta_amt'][$index]) ? $summary_data['pom_delta_amt'][$index]["{$year}POM_REQUESTED_DELTA"] : '';
                $pom_prop_value = isset($summary_data['pom_prop_amt'][$index]) ? intval($summary_data['pom_prop_amt'][$index]['POM_PROP_AMT']) : 0;
                $pom_delta_value = isset($summary_data['pom_delta_amt'][$index]) ? intval($summary_data['pom_delta_amt'][$index]['DELTA_AMT']) : 0;
            }


            $fiscal_year = strval($values['FISCAL_YEAR']);
            if (!isset($grouped_data[$base_program_name])) {
                $grouped_data[$base_program_name] = [
                    'base_k' => [
                        'tag' => $hasDropdown ? $tag_dropdown : '',
                        'bin' => $hasDropdown ? $bin_dropdown : '',
                        'position' => $base_position,
                        $fiscal_year=> $base_value,
                        'eoc' => $eoc_value
                    ],
                    'prop_amt' => [
                        'tag' => '',
                        'bin' =>'',
                        'position' => $prop_position,
                        $fiscal_year => $prop_value,
                        'eoc' => ''
                    ],
                    'delta_amt' => [
                        'tag' => '',
                        'bin' => '',
                        'position' => $delta_position,
                        $fiscal_year => $delta_value,
                        'eoc' => ''
                    ]
                ];

                if ($page == 'issue') {
                    $grouped_data[$base_program_name]['issue_prop_amt'] = [
                        'tag' => '',
                        'bin' => '',
                        'position' => $issue_prop_position,
                        $fiscal_year => $issue_prop_value,
                        'eoc' => ''
                    ];
                    $grouped_data[$base_program_name]['issue_delta_amt'] = [
                        'tag' => '',
                        'bin' => '',
                        'position' => $issue_delta_position,
                        $fiscal_year => $issue_delta_value,
                        'eoc' => ''
                    ];
                }

                if ($page == 'historical') {
                    $grouped_data[$base_program_name] = [
                        'base_k' => [
                            'tag' => $hasDropdown ? $tag_dropdown : '',
                            'bin' => $hasDropdown ? $bin_dropdown : '',
                            'position' => $base_position,
                            $fiscal_year=> $base_value,
                        ],
                        'prop_amt' => [
                            'tag' => '',
                            'bin' =>'',
                            'position' => $prop_position,
                            $fiscal_year => $prop_value,
                        ],
                        'delta_amt' => [
                            'tag' => '',
                            'bin' => '',
                            'position' => $delta_position,
                            $fiscal_year => $delta_value,
                        ]
                    ];
                    
                    $grouped_data[$base_program_name]['issue_prop_amt'] = [
                        'tag' => '',
                        'bin' => '',
                        'position' => $issue_prop_position,
                        $fiscal_year => $issue_prop_value,
                    ];
                    $grouped_data[$base_program_name]['issue_delta_amt'] = [
                        'tag' => '',
                        'bin' => '',
                        'position' => $issue_delta_position,
                        $fiscal_year => $issue_delta_value,
                    ];
                    $grouped_data[$base_program_name]['pom_prop_amt'] = [
                        'tag' => '',
                        'bin' => '',
                        'position' => $pom_prop_position,
                        $fiscal_year => $pom_prop_value
                    ];
                    $grouped_data[$base_program_name]['pom_delta_amt'] = [
                        'tag' => '',
                        'bin' => '',
                        'position' => $pom_delta_position,
                        $fiscal_year => $pom_delta_value
                    ];
                } else {
                    $grouped_data[$base_program_name]['base_k']['status'] = $program_approval_status;
                }
            } else {
                $grouped_data[$base_program_name]['base_k'][$fiscal_year] = $base_value;
                $grouped_data[$base_program_name]['prop_amt'][$fiscal_year] = $prop_value;
                $grouped_data[$base_program_name]['delta_amt'][$fiscal_year] = $delta_value;
                if ($page == 'issue' || $page == 'historical') {
                    $grouped_data[$base_program_name]['issue_prop_amt'][$fiscal_year] = $issue_prop_value;
                    $grouped_data[$base_program_name]['issue_delta_amt'][$fiscal_year] = $issue_delta_value;
                }
                if ($page == 'historical') {
                    $grouped_data[$base_program_name]['pom_prop_amt'][$fiscal_year] = $pom_prop_value;
                    $grouped_data[$base_program_name]['pom_delta_amt'][$fiscal_year] = $pom_delta_value;
                }
            }
        }
        return [
            'fy' => $fy,
            'grouped_data' => $grouped_data
        ];
    }

    private function generate_tag_dropdown($tags, $i, $program) {
        // $tag_dropdown = '<select type="tag" combination-id="" class="selection-dropdown" id="tag-'. $i .'"
        // onchange="dropdown_onchange(`tag-'.$i.'`, `tag`)" multiple><option></option>';
        // foreach($tags as $tag) {
        //     $tag_dropdown .= '<option value="'. $tag['TAG'] .'">'. $tag['TAG_TITLE'] .'</option>';
        // }
        // $tag_dropdown .= '</select>';
        return '';
    }

    private function generate_eoc_value($program, $filters, $page) {
        if (empty( $filters)) {
            return '';
        }

        $eoc_function = "get_{$page}_eoc";
        $list = $this->DBs->SOCOM_model->$eoc_function(
            $program, $filters['pom_sponsor'], $filters['cap_sponsor'], $filters['ass_area']
        );
        
        if (empty($list)) {
            return '';
        }
        else{ 
            return $list[0]['EOC_CODE'];
        }
    }

    private function generate_bin_dropdown($bins, $i, $program) {
        // $bin_list = [];
        // foreach($bins as $bin) {
        //     $bin_list[$bin['JCA_LV1_ID']] = $bin['JCA_LV1'];
        // }

        // $bin_dropdown = '<select type="bin" combination-id="" class="selection-dropdown" id="bin-'. $i .'"
        // onchange="dropdown_onchange(`bin-'.$i.'`, `bin`)" multiple><option></option>';
        // foreach($bin_list as $id => $bin) {
        //     $bin_dropdown .= '<option value="'. $id .'">'. $bin .'</option>';
        // }
        // $bin_dropdown .= '</select>';
        // return $bin_dropdown;

        //print_r(json_encode($bins));
      

        $bin_list = $this->DBs->SOCOM_model->get_user_assigned_bin_by_program($program);

        if (empty($bin_list)) {
            return '';
        }
        else{ 
            return implode(', <br>', array_filter(array_values($bin_list[0])));
        }
    }

    private function format_datatable_program_summary($page, $summary_data, $fys, $row_id=1, $fiscal_yr=null, $rowspan=5) {
        $final_data = [];
        $common_options = [
            'historical' => '',
            'summary' => '',
            'status' => ''
        ];

        // get the years to be removed
        $remove_fys = [];
        for ($i = min($fys) - 2; $i < min($fys); $i++) {
            $remove_fys[] = $i;
        }
        
        foreach($summary_data as $program_name => $values) {
            $base_row = $values['base_k'];
            $prop_row = $values['prop_amt'];
            $delta_row = $values['delta_amt'];
            $base_fydp = 0;
            $prop_fydp = 0;
            $delta_fydp = 0;

            foreach($fys as $fy) {
                $base_fydp += $base_row[$fy];
                $prop_fydp += $prop_row[$fy];
                $delta_fydp += $delta_row[$fy];
            }

            foreach($remove_fys as $fy) {
                unset($base_row[$fy]);
                unset($prop_row[$fy]);
                unset($delta_row[$fy]);
            }

            $base_row['fydp'] = $base_fydp;
            $prop_row['fydp'] = $prop_fydp;
            $delta_row['fydp'] = $delta_fydp;

            $historical_button = '<button class="bx--btn bx--btn--primary"
            onclick="view_onchange(1, `details`,`'.$program_name.'`)" type="button">Click here</button>';
            $summary_button = '<button class="bx--btn bx--btn--primary"
                onclick="view_onchange(1, `summary`, `'.$program_name.'`)" type="button">Click here</button>';
            $base_options = [
                'historical' => $historical_button,
                'summary' => $summary_button,
                'status' => ''
            ];
            $base_row['program'] = $program_name;
            $base_row = ($page !== 'historical') ? $base_row + $base_options : $base_row;
            $base_row['DT_RowId'] = $row_id;
            $row_id++;
            $prop_row['program'] = ($page !== 'historical') ? '' : $program_name;
            $prop_row = ($page !== 'historical') ? $prop_row + $common_options : $prop_row;
            $prop_row['DT_RowId'] = ($page !== 'historical') ? $program_name."_".$row_id : $row_id;

            $row_id++;
            $delta_row['program'] = ($page !== 'historical') ? '' : $program_name;
            $delta_row = ($page !== 'historical') ? $delta_row + $common_options : $delta_row;
            $delta_row['DT_RowId'] = ($page !== 'historical') ? $program_name."_".$row_id : $row_id;

            $row_id++;

            $visibility = ($base_fydp != 0) || ($prop_fydp != 0) || ($delta_fydp != 0);

            if ($page == 'issue' || $page == 'historical') {
                $issue_prop_row = $values['issue_prop_amt'];
                $issue_delta_row = $values['issue_delta_amt'];
                $issue_prop_fydp = 0;
                $issue_delta_fydp = 0;
                foreach($fys as $fy) {
                    $issue_prop_fydp += $issue_prop_row[$fy];
                    $issue_delta_fydp += $issue_delta_row[$fy];
                }
                $issue_prop_row['fydp'] = $issue_prop_fydp;
                $issue_delta_row['fydp'] = $issue_delta_fydp;
             
                $issue_prop_row['program'] = ($page !== 'historical') ? '' : $program_name;
                $issue_prop_row = ($page !== 'historical') ? $issue_prop_row + $common_options : $issue_prop_row;
                $issue_prop_row['DT_RowId'] = ($page !== 'historical') ? $program_name."_".$row_id : $row_id;
                $issue_prop_row['status'] =  ($page !== 'historical') ? $base_row['status'] : '';

                $row_id++;

                $issue_delta_row['program'] = ($page !== 'historical') ? '' : $program_name;
                $issue_delta_row = ($page !== 'historical') ? $issue_delta_row + $common_options : $issue_delta_row ;
                $issue_delta_row['DT_RowId'] =  ($page !== 'historical') ? $program_name."_".$row_id : $row_id;
                $issue_delta_row['status'] =  ($page !== 'historical') ? $base_row['status'] : '';

                $row_id++;

                $visibility =  (($rowspan === 3) ? $visibility : $visibility || ($issue_prop_fydp != 0) || ($issue_delta_fydp != 0));

                $issue_prop_row['r_visibility'] = $visibility == false ? "hidden" : "not hidden";
                $issue_delta_row['r_visibility'] = $visibility == false ? "hidden" : "not hidden";
            } 
            if ($page == 'historical') {
                $pom_prop_row = $values['pom_prop_amt'];
                $pom_delta_row = $values['pom_delta_amt'];
                $pom_prop_fydp = 0;
                $pom_delta_fydp = 0;
                foreach($fys as $fy) {
                    $pom_prop_fydp += $pom_prop_row[$fy];
                    $pom_delta_fydp += $pom_delta_row[$fy];
                }
                $pom_prop_row['fydp'] = $pom_prop_fydp;
                $pom_delta_row['fydp'] = $pom_delta_fydp;

                $pom_prop_row['program'] =  $program_name;
                $pom_prop_row['DT_RowId'] = $row_id;
                $row_id++;

                $pom_delta_row['program'] = $program_name;
                $pom_delta_row['DT_RowId'] = $row_id;
                $row_id++;
                
                $visibility = (($rowspan === 3) ? $visibility : $visibility || ($pom_prop_fydp != 0) ||  ($pom_delta_fydp != 0));

                $pom_prop_row['r_visibility'] = $visibility == false ?  "hidden" : "not hidden";
                $pom_delta_row['r_visibility'] = $visibility == false ?  "hidden" : "not hidden";

            } else {
                $prop_row['status'] = $base_row['status'];
                $delta_row['status'] = $base_row['status'];
            }
            
            $base_row['r_visibility'] = $visibility == false ?  "hidden" : "not hidden";
            $prop_row['r_visibility'] = $visibility == false ?  "hidden" : "not hidden";
            $delta_row['r_visibility'] = $visibility == false ?  "hidden" : "not hidden";
    
            $final_data = array_merge($final_data, [
                $base_row,
                $prop_row,
                $delta_row
            ]);

            if ($page == 'issue' || $page == 'historical') {
                $final_data = array_merge($final_data, [
                    $issue_prop_row,
                    $issue_delta_row
                ]);
            }

            if ($page == 'historical') {
                $final_data = array_merge($final_data, [
                    $pom_prop_row,
                    $pom_delta_row
                ]);
            }
        }

        return $final_data;
    }

    public function historical_pom($page) {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $data = $this->get_historical_pom_view_data($page, $post_data);
            $program = isset($post_data['program']) ? $post_data['program'] : '';
            $program_code = isset($post_data['program_code']) ? $post_data['program_code'] : '';
            if ($program  && !$program_code) {
                $program_code = $this->SOCOM_Program_model->get_program_id($program, 'PROGRAM_CODE');
            }

            $data['eoc_code'] = array_column(
                $this->DBs->SOCOM_model->get_historical_pom_eoc_dropdown($page, $program_code),
                'EOC_CODE'
            );

            $this->load->view('SOCOM/historical_pom_view', $data);
        }
    }

    public function update_historical_pom($page) {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $data = $this->get_historical_pom_view_data($page, $post_data);

            $this->load->view('SOCOM/historical_pom_data_view', $data);
        }
    }

    private function get_historical_pom_view_data($page, $post_data) {
        $l_pom_sponsor = $this->get_pom_sponsor_list();
        $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : $this->l_cap_sponsor;
        $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : $this->l_ass_area;
        $eoc_code = isset($post_data['eoc_code']) ? $post_data['eoc_code'] : [];
        $eoc_code_dropdown = isset($post_data['eoc_code_dropdown']) ? $post_data['eoc_code_dropdown'] : [];
        $program = isset($post_data['program']) ? $post_data['program'] : '';
        $view = isset($post_data['view']) ? $post_data['view'] : '';
        $program_code = $this->SOCOM_Program_model->get_program_id($program, 'PROGRAM_CODE');

        $zbt_fy = $this->ZBT_FY;
        $zbt_year = $this->ZBT_YEAR;

        $iss_fy = $this->ISS_FY;
        $iss_year = $this->ISS_YEAR;

        $params = [
            'l_pom_sponsor' => $l_pom_sponsor,
            'l_cap_sponsor' => $l_cap_sponsor,
            'l_ass_area' => $l_ass_area,
            'eoc_code' =>  $eoc_code
        ];
        
        // get historical pom data
        $historical_data = $this->get_historical_pom_data(
            $page, $view, $program, $program_code, $params
        );
        $tags = $this->DBs->SOCOM_model->get_user_assigned_tag('LOOKUP_TAG');
        $bins = $this->DBs->SOCOM_model->get_user_assigned_bin('LOOKUP_JCA');
        
        if ($page == 'issue') {
            $historical_data_two_years_ago = $historical_data[$iss_year - 2];
            $historical_data_last_year = $historical_data[$iss_year - 1];
            $historical_data_current_year = $historical_data[$iss_year];
            $formatted_data_two_years_ago = $this->group_by_program_name($historical_data_two_years_ago, $tags, $bins, 'historical', $iss_fy - 2, true);
            $formatted_data_last_year = $this->group_by_program_name($historical_data_last_year, $tags, $bins, 'historical', $iss_fy - 1, false);
            $formatted_data_current_year = $this->group_by_program_name($historical_data_current_year, $tags, $bins, 'historical', $iss_fy, false);

            $year_list = $this->ISS_YEAR_LIST;

            $formatted_grouped_data_two_years_ago = [];
            $formatted_grouped_data_last_year = [];
            $formatted_grouped_data_current_year = [];

            $formatted_grouped_data_two_years_ago[$program] = $this->default_formatted_data(
                $formatted_data_two_years_ago['grouped_data'], $program,  $year_list
            );
            $formatted_grouped_data_last_year[$program] = $this->default_formatted_data(
                $formatted_data_last_year['grouped_data'], $program,  $year_list
            );
            $formatted_grouped_data_current_year[$program] = $this->default_formatted_data(
                $formatted_data_current_year['grouped_data'], $program,  $year_list
            );

        } elseif ($page == 'zbt_summary') {
            $historical_data_two_years_ago = $historical_data[$zbt_year - 2];
            $historical_data_last_year = $historical_data[$zbt_year - 1];
            $historical_data_current_year = $historical_data[$zbt_year];
            $formatted_data_two_years_ago = $this->group_by_program_name($historical_data_two_years_ago, $tags, $bins, 'historical', $zbt_fy - 2, true);
            $formatted_data_last_year = $this->group_by_program_name($historical_data_last_year, $tags, $bins, 'historical', $zbt_fy - 1, false);
            $formatted_data_current_year = $this->group_by_program_name($historical_data_current_year, $tags, $bins, 'historical', $zbt_fy, false);

            $year_list = $this->ZBT_YEAR_LIST;

            $formatted_grouped_data_two_years_ago = [];
            $formatted_grouped_data_last_year = [];
            $formatted_grouped_data_current_year = [];

            $formatted_grouped_data_two_years_ago[$program] = $this->default_formatted_data(
                $formatted_data_two_years_ago['grouped_data'], $program,  $year_list
            );
            $formatted_grouped_data_last_year[$program] = $this->default_formatted_data(
                $formatted_data_last_year['grouped_data'], $program,  $year_list
            );
            $formatted_grouped_data_current_year[$program] = $this->default_formatted_data(
                $formatted_data_current_year['grouped_data'], $program,  $year_list
            );
        }


        $headers = [
            0 => [
            'data' => 'program',
            'title' => 'Program',
            ],
            // 1 => [
            // 'data' => 'tag',
            // 'title' => 'Tag',
            // ],
            1 => [
            'data' => 'bin',
            'title' => 'JCA Alignment',
            ],
            2 => [
            'data' => 'position',
            'title' => 'POM Position',
            ]
        ];

        foreach($year_list as $year) {
            $headers[] = [
                'data' => $year,
                'title' => $year,
                'className' => 'editable'
            ];
        }
        $headers = array_merge($headers, [
            [
                'data' => 'fydp',
                'title' => 'FYDP',
            ],
            [
                'data' => 'DT_RowId',
                'title' => 'DT_RowId',
            ]
        ]);

        if ($page == 'zbt_summary'){
            $formatted_program_data_two_years_ago = $this->format_datatable_program_summary(
                'historical', $formatted_grouped_data_two_years_ago,$year_list, 1, $zbt_fy - 2, 5
            );
            $formatted_program_data_last_year = $this->format_datatable_program_summary(
                'historical', $formatted_grouped_data_last_year,$year_list, end($formatted_program_data_two_years_ago)['DT_RowId'] + 1, $zbt_fy - 1, 5
            );
            $formatted_program_data_current_year = $this->format_datatable_program_summary(
                'historical', $formatted_grouped_data_current_year,$year_list, end($formatted_program_data_last_year)['DT_RowId'] + 1, $zbt_fy, 5
            );
        } elseif ($page == 'issue') {
            $formatted_program_data_two_years_ago = $this->format_datatable_program_summary(
                'historical', $formatted_grouped_data_two_years_ago, $year_list, 1, $iss_fy - 2, 5
            );
            $formatted_program_data_last_year = $this->format_datatable_program_summary(
                'historical', $formatted_grouped_data_last_year,$year_list, end($formatted_program_data_two_years_ago)['DT_RowId'] + 1, $iss_fy - 1, 5
            );
            $formatted_program_data_current_year = $this->format_datatable_program_summary(
                'historical', $formatted_grouped_data_current_year,$year_list, end($formatted_program_data_last_year)['DT_RowId'] + 1, $iss_fy, 5
            );
        }

        if ($page == 'zbt_summary'){
            unset($formatted_program_data_current_year[3]);
            unset($formatted_program_data_current_year[4]);
            unset($formatted_program_data_current_year[5]);
            unset($formatted_program_data_current_year[6]);
        } elseif ($page == 'issue') {
            unset($formatted_program_data_current_year[5]);
            unset($formatted_program_data_current_year[6]);
        }

        if ($page == 'zbt_summary'){
            $historical_data = array_merge(
                $formatted_program_data_two_years_ago, $formatted_program_data_last_year, $formatted_program_data_current_year
            );
        } elseif ($page == 'issue') {
            $historical_data = array_merge(
                $formatted_program_data_two_years_ago, $formatted_program_data_last_year, $formatted_program_data_current_year
            );
        }
        foreach( $historical_data as $idx => &$value) {
            if ($idx > 0) {
                $value['tag'] = $program;
                $value['bin'] = $program;
            }
        }

        // graph data
        $minYear = intval(min($year_list)) - 2;
        $maxYear = intval(max($year_list));
        $categories = range($minYear, $maxYear);

        if ($page == 'zbt_summary'){
            $current_pom_cycle_graph_data = $this->get_current_pom_cycle_graph_data(
                $page, $year_list, $formatted_grouped_data_current_year[$program]
            );
        } elseif ($page == 'issue') {
            $current_pom_cycle_graph_data = $this->get_current_pom_cycle_graph_data(
                $page, $year_list, $formatted_grouped_data_current_year[$program]
            );
        }

        if ($page == 'zbt_summary'){
            $historical_pom_graph_data = $this->get_historical_pom_graph_data(
                $page,
                $categories,
                $formatted_grouped_data_two_years_ago[$program],
                $formatted_grouped_data_last_year[$program],
                $formatted_grouped_data_current_year[$program]
            );
        } elseif ($page == 'issue') {
            $historical_pom_graph_data = $this->get_historical_pom_graph_data(
                $page,
                $categories,
                $formatted_grouped_data_two_years_ago[$program],
                $formatted_grouped_data_last_year[$program],
                $formatted_grouped_data_current_year[$program]
            );
        }

        // setup table data
        $year_list[] = 'fydp';
        $year_index_start = 3;
        $year_indices = [
            3, 4, 5, 6, 7, 8
        ];

        if ($page == 'zbt_summary') {
            $current_pom_cycle_graph_title = $program . " Changes from {$zbt_fy}EXT to {$zbt_fy}ZBT";
            $historical_pom_graph_title = $program . " Changes from " . ($zbt_fy - 2) . "POM to " . ($zbt_fy - 1) . "POM to {$zbt_fy}ZBT";
        } else {
            $current_pom_cycle_graph_title = $program . " Changes from {$iss_fy}EXT to {$iss_fy}ZBT to {$iss_fy}ISS";
            $historical_pom_graph_title = $program . " Changes from " . ($iss_fy - 2) . "POM to " . ($iss_fy - 1) . "POM to {$iss_fy}ISS";
        }

        $graph_subtitle = [];
        if (!empty($eoc_code)) {
            $graph_subtitle['subtitle'] = 'EOC Code: ' . implode(',', $eoc_code);
        }

        return [
            'data' => [
                'historical_pom' => [
                    'data' => $historical_data,
                    'headers' => $headers,
                    'yearList' => $year_list,
                    'indexOfYear' => $year_index_start,
                    'yearIndex' => $year_indices
                ],
                'current_pom_cycle_graph' => [
                    'categories' => $year_list,
                    'program' => $program,
                    'data' => $current_pom_cycle_graph_data,
                    'title' => $current_pom_cycle_graph_title,
                    ...$graph_subtitle
                ],
                'historical_graph' => [
                    'categories' => $categories,
                    'program' => $program,
                    'data' => $historical_pom_graph_data,
                    'title' =>  $historical_pom_graph_title,
                    ...$graph_subtitle
                ]
            ],
            'program' => $program,
            'page' => $page
        ];
    }

    private function default_formatted_data($formatted_data, $program, $year_list) {
        $default_data = [];
        if (isset($formatted_data[$program])) {
            foreach($formatted_data[$program] as $type => $value) {
                foreach($formatted_data[$program][$type] as $key => $value2) {
                    foreach($year_list as $year) {
                        if (!isset($formatted_data[$program][$type][$year])) {
                            $formatted_data[$program][$type][$year] = 0;
                        }
                    }
                }
            }
            return $formatted_data[$program];
        }

        if (!empty($formatted_data)) {
            $firstKey = array_key_first($formatted_data);
            $default_data = $formatted_data[$firstKey];


            foreach($default_data as $type => $value) {
                foreach($default_data[$type] as $key => $value2) {
                    if (is_int($key) ) {
                        $default_data[$type][$key] = 0;
                    }

                    if ($key == 'bin') {
                        $default_data[$type][$key] = '';
                    }
                }
            }
            return $default_data;
        }
        else {
            return $default_data;
        }
    }

    private function get_current_pom_cycle_graph_data($page, $years, $historical_data) {
        $zbt_fy = $this->ZBT_FY;
        $iss_fy = $this->ISS_FY;
        
        if ($page == 'issue') {
            $zbt_name = $iss_fy . 'ZBT';
            $zbt_color = '';
            $ext_name = $iss_fy . 'EXT';
        } else {
            $zbt_color = '#90ed7d';
            $zbt_name = $zbt_fy . 'ZBT Requested';
            $ext_name = $zbt_fy . 'EXT';
        }
        
        $ext = [
            'name' => $ext_name,
            'color' =>  '#ed7d32',
            'data' => []
        ];
        $zbt = [
            'name' =>  $zbt_name,
            'color' => $zbt_color,
            'data' => []
        ];
        $iss = [
            'name' => $iss_fy . 'ISS Requested',
            'color' => '#90ed7d',
            'data' => []
        ];
 
        foreach($years as $year) {
            $ext['data'][] = $historical_data['base_k'][ $year];
            $zbt['data'][] = $historical_data['prop_amt'][ $year];
            if ($page == 'issue') {
                $iss['data'][] = $historical_data['issue_prop_amt'][ $year];
            }
        }


        $data = [$ext,  $zbt];
        if ($page == 'issue') {
            $data[] =  $iss;
        }

        return $data;
    }


    private function get_historical_pom_graph_data(
        $page, $years, $historical_data_1, $historical_data_2, $historical_data_3
    ) {
        if ($page == 'issue') {
            $historical_data_key = 'issue_prop_amt';
            $requested_name = $this->ISS_FY . 'ISS Requested';
            $pom_name_1 = ($this->ISS_FY - 2) . 'POM';
            $pom_name_2 = ($this->ISS_FY - 1) . 'POM';
        }
        else if ($page == 'zbt_summary') {
            $historical_data_key = 'prop_amt';
            $requested_name = $this->ZBT_FY . 'ZBT Requested';
            $pom_name_1 = ($this->ZBT_FY - 2) . 'POM';
            $pom_name_2 = ($this->ZBT_FY - 1) . 'POM';
        }

        $pom_1 = [
            'name' => $pom_name_1,
            'data' => []
        ];
        $pom_2 = [
            'name' => $pom_name_2,
            'data' => []
        ];
        $requested = [
            'name' => $requested_name,
            'color' => '#90ed7d',
            'data' => [] 
        ];

        if ($page == 'issue') {
            foreach($years as $year) {
                if ($year != $this->ISS_YEAR + 3 && $year != $this->ISS_YEAR + 4) {
                    $pom_1['data'][] = $historical_data_1['pom_prop_amt'][$year];
                }
                if ($year != $this->ISS_YEAR + 4) {
                    $pom_2['data'][] = $historical_data_2['pom_prop_amt'][$year];
                }
                $requested['data'][] = $historical_data_3[$historical_data_key][$year];
            }
        }
        else if ($page == 'zbt_summary') {
            foreach($years as $year) {
                if ($year != $this->ZBT_YEAR + 3 && $year != $this->ZBT_YEAR + 4) {
                    $pom_1['data'][] = $historical_data_1['pom_prop_amt'][$year];
                }
                if ($year != $this->ZBT_YEAR + 4) {
                    $pom_2['data'][] = $historical_data_2['pom_prop_amt'][$year];
                }
                $requested['data'][] = $historical_data_3[$historical_data_key][$year];
            }
        }

        return [$pom_1,  $pom_2, $requested];
    }
    

    private function get_historical_pom_data(
        $page, $view, $program, $program_code, $params
    ) {
        $zbt_fy = $this->ZBT_FY;
        $iss_fy = $this->ISS_FY;

        $zbt_year = $this->ZBT_YEAR;
        $iss_year = $this->ISS_YEAR;


        if ($page == 'issue') {
            $historical_data_two_years_ago = $this->DBs->SOCOM_model->get_historical_pom_data($page, $view, $iss_fy - 2,  $params,  $iss_fy, $program, $program_code);
            $historical_data_last_year = $this->DBs->SOCOM_model->get_historical_pom_data($page, $view, $iss_fy - 1,  $params,  $iss_fy, $program, $program_code);
            $historical_data_current_year = $this->DBs->SOCOM_model->get_historical_pom_data($page, $view, $iss_fy,  $params,  $iss_fy, $program, $program_code);
            return [
                $iss_year - 2 => $historical_data_two_years_ago,
                $iss_year - 1 => $historical_data_last_year,
                $iss_year => $historical_data_current_year,
            ];
        } else {
            $historical_data_two_years_ago = $this->DBs->SOCOM_model->get_historical_pom_data($page, $view, $zbt_fy - 2, $params, $zbt_fy, $program, $program_code);
            $historical_data_last_year = $this->DBs->SOCOM_model->get_historical_pom_data($page, $view, $zbt_fy - 1, $params, $zbt_fy, $program, $program_code);
            $historical_data_current_year = $this->DBs->SOCOM_model->get_historical_pom_data($page, $view, $zbt_fy, $params, $zbt_fy, $program, $program_code);

            return [
                $zbt_year - 2 => $historical_data_two_years_ago,
                $zbt_year - 1 => $historical_data_last_year,
                $zbt_year => $historical_data_current_year,
            ];
        }
    }

    private function get_eoc_summary_headers($year_list) {
        $init_headers = [
            [
                'data' => 'program',
                'title' => 'EOC',
            ],
            [
                'data' => 'event_name',
                'title' => 'Event Name',
            ],
            [
                'data' => 'event_title',
                'title' => 'Event Title',
            ],
            [
                'data' => 'ass_area_code',
                'title' => 'Assessment Area',
            ],
            [
                'data' => 'cap_sponsor_code',
                'title' => 'Capability Sponsor',
            ],
            [
                'data' => 'resource_cat_code',
                'title' => 'Resource Category',
            ],
            [
                'data' => 'special_project_code',
                'title' => 'Special Project Code',
            ],
            [
                'data' => 'osd_program_elem_code',
                'title' => 'OSD Program Element Code',
            ],
            [
                'data' => 'event_justification',
                'title' => 'Event Justification',
            ],
            [
                'data' => 'position',
                'title' => 'POM Position',
            ]
        ];

        // add year headers
        $years_header = [];
        foreach($year_list as $year) {
            $years_header[] = [
                'data' => $year,
                'title' => $year,
                'className' => 'editable',
                "defaultContent"=> null
            ];
        }

        $years_header[] = [
            'data' => 'fydp',
            'title' => 'FYDP',
        ];

        // add AE AO headers
        $ae_ao_headers = [
            [
                'data' => 'ao_rec',
                'title' => 'AO Recommendation',
            ],
            [
                'data' => 'ao_comment',
                'title' => 'AO Comment',
            ],
            [
                'data' => 'ad_approval',
                'title' => 'AD Approval',
            ],
            [
                'data' => 'ad_comment',
                'title' => 'AD Comment',
            ]
        ];

        $headers = array_merge($init_headers, $years_header, $ae_ao_headers,[
            [
                'data' => 'DT_RowId',
                'title' => 'DT_RowId',
            ]
        ]);

        $init_header_size = count($init_headers);
        $year_header_size = count($years_header);
        $ae_ao_headers_size = count($ae_ao_headers);
        
        $year_index_start = $init_header_size;
        $year_indices = range($year_index_start, $year_index_start + $year_header_size - 1);
        $ao_ae_index_start =  $init_header_size + $year_header_size;
        $ao_ae_index_indices = range($ao_ae_index_start, $ao_ae_index_start + $ae_ao_headers_size - 1);
        $init_header_indices = range(0, $init_header_size - 2);

        return [
            'headers' =>  $headers,
            'year_index_start' =>  $year_index_start,
            'year_indices' =>  $year_indices,
            'ao_ae_index_indices' =>  $ao_ae_index_indices,
            'init_header_indices' => $init_header_indices
        ];
    }
    
    public function eoc_summary($page) {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result']) {

            $post_data = $data_check['post_data'];
 
            $data = $this->get_eoc_summary_view_data($page, $post_data);

            $this->load->view('SOCOM/eoc_summary_view', $data);
        }
    }

    public function update_eoc_summary($page) {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result']) {

            $post_data = $data_check['post_data'];
    
            $data = $this->get_eoc_summary_view_data($page, $post_data);

            $this->load->view('SOCOM/eoc_summary_data_view', $data);
        }
    }

    private function get_eoc_summary_view_data($page, $post_data) {
        $l_pom_sponsor =$this->get_pom_sponsor_list();
        $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : $this->l_cap_sponsor;
        $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : $this->l_ass_area;
        $program = isset($post_data['program']) ? $post_data['program'] : '';
        $program_code = isset($post_data['program_code']) ? $post_data['program_code'] : '';
        $eoc_code = isset($post_data['eoc_code']) ? $post_data['eoc_code'] : [];

        $zbt_fy = $this->ZBT_FY;
        $iss_fy = $this->ISS_FY;

        $zbt_year = $this->ZBT_YEAR;
        $iss_year = $this->ISS_YEAR;

        $current_year = $page === 'issue' ? $iss_year :  $zbt_year;

        if ($program_code) {
            $program = $this->DBs->SOCOM_model->get_program_name($program_code)[0]['PROGRAM_NAME'];
        }
        else {
            $program_code = $this->SOCOM_Program_model->get_program_id($program, 'PROGRAM_CODE');
        }

        $params = [
            'l_pom_sponsor' => $l_pom_sponsor,
            'l_cap_sponsor' => $l_cap_sponsor,
            'l_ass_area' => $l_ass_area,
            'eoc_code' => $eoc_code,
            'program_code' => $program_code
        ];

        $eoc_data = $this->get_eoc_summary_data(
            $page, $params
        );


        $eoc_data_by_year = $eoc_data[$current_year];

        if ($page == 'issue') {
            $formatted_data = $this->group_by_eoc($eoc_data_by_year, $page, $iss_fy);
        } else {
            $formatted_data = $this->group_by_eoc($eoc_data_by_year, $page, $zbt_fy);
        }

        $year_list = $page == 'issue' ? $this->ISS_YEAR_LIST : $this->ZBT_YEAR_LIST;
        $formatted_grouped_data = [];
        $formatted_grouped_data = $formatted_data['grouped_data'];
        $formatted_program_data = [];
 

        $formatted_program_data_obj = $this->format_datatable_eoc_summary(
            $page, $formatted_grouped_data,$year_list
        );
        $formatted_program_data = $formatted_program_data_obj['final_data'];
    
        // $eoc_data = array_merge(
        //     $formatted_program_data_2024, $formatted_program_data_2025, $formatted_program_data_2026
        // );

        $eoc_data = $formatted_program_data;
        // graph data
        $categories = $year_list;

        // EOC Summary graph data uses historical pom data
        $historical_data = $this->get_historical_pom_data(
            $page, 'details', $program, $program_code, $params
        );

        if ($page == 'issue') {
            $historical_data_by_year = $historical_data[$iss_year];
            $formatted_historical_data = $this->group_by_program_name($historical_data_by_year, '', '', 'historical', $iss_fy, false);
        } elseif ($page == 'zbt_summary') {
            $historical_data_by_year = $historical_data[$zbt_year];
            $formatted_historical_data = $this->group_by_program_name($historical_data_by_year, '', '', 'historical', $zbt_fy, false);
        }

        $formatted_grouped_historical_data = [];
        $formatted_grouped_historical_data[$program] = $formatted_historical_data['grouped_data'][$program] ?? [];

        $graph_data = $this->get_eoc_summary_graph_data(
            $page, $categories, $formatted_grouped_historical_data[$program], $eoc_code
        );
        $eoc_code_list = $this->get_eoc_code_list($eoc_data);
        $header_values  = $this->get_eoc_summary_headers($year_list);
        $headers = $header_values['headers'];
        $year_index_start = $header_values['year_index_start'];
        $year_indices = $header_values['year_indices'];
        $ao_ae_comment_index_indices = $header_values['ao_ae_index_indices'];
        $initHeadersIndexList = $header_values['init_header_indices'];
        $year_list[] = 'fydp';

        $graph_subtitle = [];
        if (!empty($eoc_code)) {
            $graph_subtitle['subtitle'] = 'EOC Code: ' . implode(',', $eoc_code);
        }

        return [
            'data' => [
                'eoc_summary' => [
                    'data' => $eoc_data,
                    'headers' => $headers,
                    'yearList' => $year_list,
                    'indexOfYear' => $year_index_start,
                    'yearIndex' => $year_indices,
                    'aeaoIndexList' => $ao_ae_comment_index_indices,
                    'initHeadersIndexList' => $initHeadersIndexList,
                    'fy' => $page == 'issue' ? $iss_fy : $zbt_fy,
                ],
                'eoc_summary_graph' => [
                    'categories' => $categories,
                    'program' => $program,
                    'data' => $graph_data,
                    'fy' => $page == 'issue' ? $iss_fy : $zbt_fy,
                    'page' => $page,
                    ...$graph_subtitle
                ]
            ],
            'eoc_code' => $eoc_code_list,
            'program' => $program,
            'page' => $page
        ];
    }

    private function get_eoc_code_list($eoc_data) {
        $eoc_code_list = [];
        foreach($eoc_data as $eoc) {
            $eoc_code_list[] = $eoc['program_name'];
        }
        return array_unique($eoc_code_list);
    }

    private function get_eoc_summary_graph_data($page, $years, $historical_data) {
        $zbt_fy = $this->ZBT_FY;
        $iss_fy = $this->ISS_FY;

        if ($page == 'issue') {
            $zbt_name = $iss_fy . 'ZBT';
            $ext_name = $iss_fy . 'EXT';
            $zbt_color = '';
        } else {
            $zbt_name = $zbt_fy . 'ZBT Requested';
            $ext_name = $zbt_fy . 'EXT';
            $zbt_color = '#90ed7d';
        }
        
        $ext = [
            'name' => $ext_name,
            'color' =>  '#ed7d32',
            'data' => []
        ];
        $zbt = [
            'name' =>  $zbt_name,
            'color' => $zbt_color,
            'data' => []
        ];
        $iss = [
            'name' => $iss_fy . 'ISS Requested',
            'color' => '#90ed7d',
            'data' => []
        ];

        foreach($years as $year) {
            $ext['data'][] = $historical_data['base_k'][ $year];
            $zbt['data'][] = $historical_data['prop_amt'][ $year];
            if ($page == 'issue') {
                $iss['data'][] = $historical_data['issue_prop_amt'][ $year];
            }
        }

        $data = [];
        
        if ($page == 'issue') {
            $data = [$zbt, $iss];
        } else {
            $data = [$ext, $zbt];
        }
    
        return $data;
    }

    private function get_eoc_summary_data($page, $params) {

        // $params = [
        //     'l_pom_sponsor' => $l_pom_sponsor,
        //     'l_cap_sponsor' => $l_cap_sponsor,
        //     'l_ass_area' => $l_ass_area,
        //     'program_code' => $program_code
        // ];

        // $eoc_data_2024 = $this->DBs->SOCOM_model->get_eoc_summary_data($page, '24', $params_2024);
        // $eoc_data_2025 = $this->DBs->SOCOM_model->get_eoc_summary_data($page, '25', $params_2025);
        // $eoc_data_2026 = $this->DBs->SOCOM_model->get_eoc_summary_data($page, '26', $params_2026);

        $fy = $page == 'zbt_summary' ?  $this->ZBT_FY : $this->ISS_FY;
        $year = $page == 'zbt_summary' ? $this->ZBT_YEAR : $this->ISS_YEAR;
        
        $eoc_data = $this->DBs->SOCOM_model->get_eoc_summary_data($page, $fy, $params);

        return [
            // '2024' => $eoc_data_2024,
            // '2025' => $eoc_data_2025,
            // '2026' => $eoc_data_2026
            $year => $eoc_data
        ];
    }
    
    private function format_approval_by_program_name($approval_status) {
        $result = [];
        foreach ($approval_status as $program) {
            $result[$program['PROGRAM_NAME']] = $program['APPROVAL_ACTION_STATUS'];
        }
        return $result;
    }
    
    private function group_by_eoc($summary_data, $page, $year) {
        $grouped_data = [];
        $function_name = "group_by_eoc_$page";
        $fy = [];

        $this->$function_name($summary_data, $fy, $grouped_data, $year);
        return [
            'fy' => $fy,
            'grouped_data' => $grouped_data
        ];
    }
    
    private function group_by_eoc_zbt_summary($summary_data, &$fy, &$grouped_data, $year) {
        $base_k = $summary_data['base_k'];
        $event_name = $summary_data['prop_amt'][0]["EVENT_NAME"] ?? '';
        $event_justification = $summary_data['prop_amt'][0]["EVENT_JUSTIFICATION"] ?? '';
        // $explode_event_name = explode(", ", $event_name);
        // $explode_event_justification = explode(", ", $event_justification);
        foreach($base_k as $index => $values) {
            if (!count($fy) && isset($values['FISCAL_YEARS'])) {
                $fy = explode(', ', $values['FISCAL_YEARS']);
            }
            
            $base_program_name = $values['EOC'];
            $base_value = intval($values['BASE_K']) ?? 0;
            $prop_value = intval($summary_data['prop_amt'][$index]['PROP_AMT']) ?? 0;
            $delta_value = intval($summary_data['delta_amt'][$index]['DELTA_AMT']) ?? 0;
            $base_position = $values["{$year}EXT"] ?? '';
            $prop_position = $summary_data['prop_amt'][$index]["{$year}ZBT_REQUESTED"] ?? 'empty';
            $delta_position = $summary_data['delta_amt'][$index]["{$year}ZBT_REQUESTED_DELTA"] ?? 'empty';
            $ass_area_code = $values["ASSESSMENT_AREA_CODE"];
            $resource_cat_code = $values["RESOURCE_CATEGORY_CODE"];
            $pom_sponsor_code = $values["POM_SPONSOR_CODE"];
            $event_name_dropdown = $values["EVENT_NAME"];
            $event_justification_data = $values["EVENT_JUSTIFICATION"];
            $cap_sponsor_code = $values["CAPABILITY_SPONSOR_CODE"];
            $event_title = $values["EVENT_TITLE"];
            $special_project_code = $values["SPECIAL_PROJECT_CODE"];
            $osd_program_elem_code = $values["OSD_PROGRAM_ELEMENT_CODE"];
            
            //$program_id = $this->SOCOM_Program_model->get_program_id($program);
            //$eoc_id = $base_program_name;//$this->SOCOM_Program_model->get_eoc_code();

            // Get recommendations by program and current user
            $user_id = (int)$this->session->userdata("logged_in")["id"];
            $ao_data = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name_dropdown, 'zbt_summary', $user_id, SOCOM_AOAD_DELETED_DROPDOWN);
            $ad_data = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name_dropdown, 'zbt_summary', $user_id, SOCOM_AOAD_DELETED_DROPDOWN);
            $is_ao_user = $this->SOCOM_AOAD_model->is_ao_user();
            $is_ad_user = $this->SOCOM_AOAD_model->is_ad_user();

            // Get all user comments by program
            $ao_comments = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name_dropdown, 'zbt_summary', null, SOCOM_AOAD_DELETED_COMMENT);
            $ad_comments = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name_dropdown, 'zbt_summary', null, SOCOM_AOAD_DELETED_COMMENT);

            // Generate 'View' buttons to open AO/AD comments modal
            $ao_text_forms = $this->generate_eoc_text_form($index, 'ao', $ao_comments, $is_ao_user, $event_name_dropdown);
            $ad_text_forms = $this->generate_eoc_text_form($index, 'ad', $ad_comments, $is_ad_user, $event_name_dropdown);

            // Generate 'View' buttons to open AO/AD modal with dropdown
            $ao_dropdown_form = $this->generate_eoc_dropdown_form($index, "ao", $ass_area_code, $user_id, $ao_comments, $is_ao_user, $event_name_dropdown, 'zbt_summary');
            $ad_dropdown_form = $this->generate_eoc_dropdown_form($index, "ad", $ass_area_code, $user_id, $ad_comments, $is_ad_user, $event_name_dropdown, 'zbt_summary');
            $event_justification_dropdown = $this->generate_eoc_event_form($index, $event_justification_data, $user_id, $enabled, $event_name, 'zbt_summary');
            $event_name_link =  "<a href='/socom/zbt_summary/event_summary/{$event_name_dropdown}'>{$event_name_dropdown}</a>";
            $fiscal_year = strval($values['FISCAL_YEAR']);

            $delta_value = $this->find_delta_value_issue(
                $summary_data['delta_amt'],
                [
                    'PROGRAM_NAME' => $base_program_name,
                    'EVENT_NAME' => $event_name_dropdown,
                    'OSD_PROGRAM_ELEM_CODE' => $osd_program_elem_code,
                    'SPECIAL_PROJECT_CODE' => $special_project_code,
                    'CAPABILITY_SPONSOR_CODE' => $cap_sponsor_code,
                    'RESOURCE_CATEGORY_CODE' => $resource_cat_code,
                    'ASSESSMENT_AREA_CODE' => $ass_area_code,
                    'POM_SPONSOR_CODE' => $pom_sponsor_code,
                    'CURRENT_YEAR' => $fiscal_year
                    
                ],
                "DELTA_AMT"
            );
            
            if (
                !isset($grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE']  . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE'] . '-' .$values['SPECIAL_PROJECT_CODE']])
            ) {
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE']  . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE'] . '-' .$values['SPECIAL_PROJECT_CODE']] = [
                    'base_k' => [
                        'program_name' => $base_program_name,
                        'ass_area_code' => $ass_area_code,
                        'resource_cat_code' => $resource_cat_code,
                        'pom_sponsor_code' => $pom_sponsor_code,
                        'event_name' =>  $event_name_link,
                        'event_justification' => $event_justification_dropdown,
                        'event_title' => $event_title,
                        'special_project_code' => $special_project_code,
                        'osd_program_elem_code' => $osd_program_elem_code,
                        'cap_sponsor_code' => $cap_sponsor_code,
                        'position' => $base_position,
                        $fiscal_year=> $base_value,
                        'ao_rec' => $ao_dropdown_form,
                        'ao_comment' => $ao_text_forms,
                        'ad_approval' => $ad_dropdown_form,
                        'ad_comment' => $ad_text_forms
                    ],
                    'prop_amt' => [
                        'program_name' => $base_program_name,
                        'ass_area_code' => $ass_area_code,
                        'resource_cat_code' => $resource_cat_code,
                        'pom_sponsor_code' => $pom_sponsor_code,
                        'event_name' => $event_name,
                        'event_justification' => $event_justification,
                        'event_title' => $event_title,
                        'special_project_code' => $special_project_code,
                        'osd_program_elem_code' => $osd_program_elem_code,
                        'cap_sponsor_code' => $cap_sponsor_code,
                        'position' => $prop_position,
                        $fiscal_year => $prop_value,
                        'ao_rec' => "",
                        'ao_comment' => "",
                        'ad_approval' => "",
                        'ad_comment' => ""
                    ],
                    'delta_amt' => [
                        'program_name' => $base_program_name,
                        'ass_area_code' => $ass_area_code,
                        'resource_cat_code' => $resource_cat_code,
                        'pom_sponsor_code' => $pom_sponsor_code,
                        'event_name' => $event_name,
                        'event_justification' => $event_justification,
                        'event_title' => $event_title,
                        'special_project_code' => $special_project_code,
                        'osd_program_elem_code' => $osd_program_elem_code,
                        'cap_sponsor_code' => $cap_sponsor_code,
                        'position' => $delta_position,
                        $fiscal_year => $delta_value,
                        'ao_rec' => "",
                        'ao_comment' => "",
                        'ad_approval' => "",
                        'ad_comment' => ""
                    ]
                ];
            } else {
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE']  . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE'] . '-' .$values['SPECIAL_PROJECT_CODE']]['base_k'][$fiscal_year] 
                        = $base_value;
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE'] . '-' .$values['SPECIAL_PROJECT_CODE']]['prop_amt'][$fiscal_year]
                        = $prop_value;
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE'] . '-' .$values['SPECIAL_PROJECT_CODE']]['delta_amt'][$fiscal_year] 
                        = $delta_value;
            }
        }
    }
    
    private function group_by_eoc_issue($summary_data, &$fy, &$grouped_data, $year) {
        $base_ext = $summary_data['issue_base_zbt_amt'];
        $event_name = $summary_data['issue_prop_amt'][0]["EVENT_NAME"] ?? '';
        $event_justification = $summary_data['issue_prop_amt'][0]["EVENT_JUSTIFICATION"] ?? '';
        $explode_event_name = explode(", ", $event_name);
        $explode_event_justification = explode(", ", $event_justification);

        foreach($base_ext as $index => $values) {
            if (!count($fy) && isset($values['FISCAL_YEARS'])) {
                $fy = explode(', ', $values['FISCAL_YEARS']);
            }
            $current_year = $values['FISCAL_YEAR'];
            $base_program_name = $values['EOC'];
            $zbt_value = intval($summary_data['issue_base_zbt_amt'][$index]['ZBT_AMT']) ?? 0;
            $ext_position = $values["{$year}EXT"] ?? '';
            $zbt_position = $summary_data['issue_base_zbt_amt'][$index]["{$year}ZBT"] ?? 'empty';
            $zbt_delta_position = "$zbt_position Delta";
            $issue_prop_position = "{$year}ISS REQUESTED";
            $issue_delta_position = "{$year}ISS REQUESTED DELTA";
            $issue_prop_value = $summary_data['issue_prop_amt'][$index]['PROP_AMT']; //intval($ext_value + $issue_delta_value);
            $ass_area_code = $values["ASSESSMENT_AREA_CODE"];
            $resource_cat_code = $values["RESOURCE_CATEGORY_CODE"];
            $pom_sponsor_code = $values["POM_SPONSOR_CODE"];
            $cap_sponsor_code = $values["CAPABILITY_SPONSOR_CODE"];
            $event_title = $values["EVENT_TITLE"];
            $special_project_code = $values["SPECIAL_PROJECT_CODE"];
            $osd_program_elem_code = $values["OSD_PROGRAM_ELEMENT_CODE"];
            $issue_delta_value = $this->find_delta_value_issue(
                $summary_data['issue_delta_amt'],
                [
                    'PROGRAM_NAME' => $base_program_name,
                    'EVENT_NAME' => $values['EVENT_NAME'],
                    'OSD_PROGRAM_ELEM_CODE' => $osd_program_elem_code,
                    'SPECIAL_PROJECT_CODE' => $special_project_code,
                    'CAPABILITY_SPONSOR_CODE' => $cap_sponsor_code,
                    'RESOURCE_CATEGORY_CODE' => $resource_cat_code,
                    'ASSESSMENT_AREA_CODE' => $ass_area_code,
                    'POM_SPONSOR_CODE' => $pom_sponsor_code,
                    'CURRENT_YEAR' => $current_year
                    
                ],
                "DELTA_AMT"
            );

            //$program_id = $this->SOCOM_Program_model->get_program_id($program);
            //$eoc_id = $base_program_name;//$this->SOCOM_Program_model->get_eoc_code();
            $event_name_dropdown =  $values["EVENT_NAME"];

            // Get recommendations by program and user
            $user_id = (int)$this->session->userdata("logged_in")["id"];
            $ao_data = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name_dropdown, 'issue', $user_id, SOCOM_AOAD_DELETED_DROPDOWN);
            $ad_data = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name_dropdown, 'issue', $user_id, SOCOM_AOAD_DELETED_DROPDOWN);
            $is_ao_user = $this->SOCOM_AOAD_model->is_ao_user();
            $is_ad_user = $this->SOCOM_AOAD_model->is_ad_user();
            
            // Generate 'View' buttons to open AO/AD comments modal
            $ao_comments = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name_dropdown, 'issue', null, SOCOM_AOAD_DELETED_COMMENT);
            $ad_comments = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name_dropdown, 'issue', null, SOCOM_AOAD_DELETED_COMMENT);
            $ao_text_forms = $this->generate_eoc_text_form($index, 'ao', $ao_comments, $is_ao_user, $event_name_dropdown);
            $ad_text_forms = $this->generate_eoc_text_form($index, 'ad', $ad_comments, $is_ad_user, $event_name_dropdown);
            
            $event_justification_data =  $values["EVENT_JUSTIFICATION"];

            // Generate 'View' buttons to open AO/AD modal with dropdown
            $ao_dropdown_form = $this->generate_eoc_dropdown_form($index, "ao", $ass_area_code, $user_id, $ao_comments, $is_ao_user, $event_name_dropdown, 'issue');
            $ad_dropdown_form = $this->generate_eoc_dropdown_form($index, "ad", $ass_area_code, $user_id, $ad_comments, $is_ad_user, $event_name_dropdown, 'issue');
            $event_justification_dropdown = $this->generate_eoc_event_form($index, $event_justification_data, $user_id, $enabled, $event_name, 'issue');
            $event_name_link =  "<a href='/socom/issue/event_summary/{$event_name_dropdown}'>{$event_name_dropdown}</a>";

            $fiscal_year = strval($values['FISCAL_YEAR']);
            if (!isset($grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' .
            $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
            $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
            $values['OSD_PROGRAM_ELEMENT_CODE'] . '-' .$values['SPECIAL_PROJECT_CODE']])) {
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
                $values['OSD_PROGRAM_ELEMENT_CODE']. '-' .$values['SPECIAL_PROJECT_CODE']] = [
                    'issue_base_zbt_amt' => [
                        'program_name' => $base_program_name,
                        'ass_area_code' => $ass_area_code,
                        'resource_cat_code' => $resource_cat_code,
                        'pom_sponsor_code' => $pom_sponsor_code,
                        'event_name' => $event_name_link,
                        'event_justification' => $event_justification_dropdown,
                        'event_title' => $event_title,
                        'special_project_code' => $special_project_code,
                        'osd_program_elem_code' => $osd_program_elem_code,
                        'cap_sponsor_code' => $cap_sponsor_code,
                        'position' => $zbt_position,
                        $fiscal_year => $zbt_value,
                        'ao_rec' => $ao_dropdown_form,
                        'ao_comment' => $ao_text_forms,
                        'ad_approval' => $ad_dropdown_form,
                        'ad_comment' => $ad_text_forms
                    ],
                    'issue_prop_amt' => [
                        'program_name' => $base_program_name,
                        'ass_area_code' => $ass_area_code,
                        'resource_cat_code' => $resource_cat_code,
                        'pom_sponsor_code' => $pom_sponsor_code,
                        'event_name' => '',
                        'event_justification' => '',
                        'event_title' => $event_title,
                        'special_project_code' => $special_project_code,
                        'osd_program_elem_code' => $osd_program_elem_code,
                        'cap_sponsor_code' => $cap_sponsor_code,
                        'position' => $issue_prop_position,
                        $fiscal_year => $issue_prop_value,
                        'ao_rec' => "",
                        'ao_comment' => "",
                        'ad_approval' => "",
                        'ad_comment' => ""
                    ],
                    'issue_delta_amt' => [
                        'program_name' => $base_program_name,
                        'ass_area_code' => $ass_area_code,
                        'resource_cat_code' => $resource_cat_code,
                        'pom_sponsor_code' => $pom_sponsor_code,
                        'event_name' => '',
                        'event_justification' => '',
                        'event_title' => $event_title,
                        'special_project_code' => $special_project_code,
                        'osd_program_elem_code' => $osd_program_elem_code,
                        'cap_sponsor_code' => $cap_sponsor_code,
                        'position' => $issue_delta_position,
                        $fiscal_year => $issue_delta_value,
                        'ao_rec' => "",
                        'ao_comment' => "",
                        'ad_approval' => "",
                        'ad_comment' => ""
                    ]
                ];

            } else {
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE']. '-' .$values['SPECIAL_PROJECT_CODE']]
                    ['issue_base_zbt_amt'][$fiscal_year] = $zbt_value;
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE']. '-' .$values['SPECIAL_PROJECT_CODE']]
                    ['issue_prop_amt'][$fiscal_year] = $issue_prop_value;
                $grouped_data[$base_program_name . '-' . $values['EVENT_NAME'] . '-' . 
                    $values['ASSESSMENT_AREA_CODE'] . '-' . $values['POM_SPONSOR_CODE'] . '-' . 
                    $values['CAPABILITY_SPONSOR_CODE'] . '-' .$values['RESOURCE_CATEGORY_CODE'] . '-' .
                    $values['OSD_PROGRAM_ELEMENT_CODE']. '-' .$values['SPECIAL_PROJECT_CODE']]
                    ['issue_delta_amt'][$fiscal_year] = $issue_delta_value;
            }
        }
    }
    private function format_datatable_eoc_summary($page, $summary_data, $fys, $row_id=1) {
        $final_data = [];
        $base_row = [];
        $prop_row = [];
        $delta_row = [];
        $common_options = [
            'eoc_summary' => '',
            'issue' => ''
        ];
        $count = 0;
        foreach($summary_data as $program_name => $values) {
            if ($page == 'issue') {
                $issue_base_zbt_row = $values['issue_base_zbt_amt'];
                $issue_prop_row = $values['issue_prop_amt'];
                $issue_delta_row = $values['issue_delta_amt'];
                $program_name = $issue_base_zbt_row['program_name'];
                $issue_base_zbt_fydp = 0;
                $issue_prop_fydp = 0;
                $issue_delta_fydp = 0;
                foreach($fys as $fy) {
                    if (isset($issue_base_zbt_row[$fy])) {
                        $issue_base_zbt_fydp += $issue_base_zbt_row[$fy];
                    } else {
                        $issue_base_zbt_fydp += 0;
                        $issue_base_zbt_row[$fy] = 0;
                    }
                    if (isset($issue_prop_row[$fy])) {
                        $issue_prop_fydp += $issue_prop_row[$fy];
                    } else {
                        $issue_prop_fydp += 0;
                        $issue_prop_row[$fy] = 0;
                    }
                    if (isset($issue_delta_row[$fy])) {
                        $issue_delta_fydp += $issue_delta_row[$fy];
                    } else {
                        $issue_delta_fydp += 0;
                        $issue_delta_row[$fy] = 0;
                    }
                }
                $issue_base_zbt_row['fydp'] = $issue_base_zbt_fydp;
                $issue_prop_row['fydp'] = $issue_prop_fydp;
                $issue_delta_row['fydp'] = $issue_delta_fydp;
             
                
                $issue_base_zbt_row['program'] = $program_name ?? '';
                $issue_base_zbt_row = $issue_base_zbt_row + $common_options;
                $issue_base_zbt_row['DT_RowId'] =$program_name."_".$row_id;
                $row_id++;
                
                
                $issue_prop_row['program'] = '';
                $issue_prop_row = $issue_prop_row + $common_options;
                $issue_prop_row['DT_RowId'] =$program_name."_".$row_id;
                $row_id++;

                $issue_delta_row['program'] = '';
                $issue_delta_row = $issue_delta_row + $common_options;
                $issue_delta_row['DT_RowId'] = $program_name."_".$row_id;
                $row_id++;
            } else {
                $base_row = $values['base_k'];
                $prop_row = $values['prop_amt'];
                $delta_row = $values['delta_amt'];
                $program_name = $base_row['program_name'];
                $base_fydp = 0;
                $prop_fydp = 0;
                $delta_fydp = 0;
                foreach($fys as $fy) {
                    if (isset($base_row[$fy])) {
                        $base_fydp += $base_row[$fy];
                    } else {
                        $base_fydp += 0;
                        $base_row[$fy] = 0;
                    }
                    if (isset($prop_row[$fy])) {
                        $prop_fydp += $prop_row[$fy];
                    } else {
                        $prop_fydp += 0;
                        $prop_row[$fy] = 0;
                    }
                    if (isset($delta_row[$fy])) {
                        $delta_fydp += $delta_row[$fy];
                    } else {
                        $delta_fydp += 0;
                        $delta_row[$fy] = 0;
                    }
                }
                $base_row['fydp'] = $base_fydp;
                $prop_row['fydp'] = $prop_fydp;
                $delta_row['fydp'] = $delta_fydp;
                $base_row['program'] = $program_name;
    
                $base_options = [
                   
                ];
                $base_row = ($page !== 'eoc_summary') ? $base_row + $base_options : $base_row;
                $base_row['DT_RowId'] = $row_id;
                $row_id++;
                $prop_row['program'] = ($page !== 'eoc_summary') ? '' : $program_name;
                $prop_row = ($page !== 'eoc_summary') ? $prop_row + $common_options : $prop_row;
                $prop_row['DT_RowId'] = ($page !== 'eoc_summary') ? $program_name."_".$row_id : $row_id;
    
                $row_id++;
                $delta_row['program'] = ($page !== 'eoc_summary') ? '' : $program_name;
                $delta_row = ($page !== 'eoc_summary') ? $delta_row + $common_options : $delta_row;
                $delta_row['DT_RowId'] = ($page !== 'eoc_summary') ? $program_name."_".$row_id : $row_id;
    
                $row_id++;
            }


            if ($page != 'issue') {
                $final_data = array_merge($final_data, [
                    $base_row,
                    $prop_row,
                    $delta_row
                ]);

            } else {
                $final_data = array_merge($final_data, [
                    $issue_base_zbt_row,
                    $issue_prop_row,
                    $issue_delta_row
                ]);
            }
            $count++;
        }

        return [
            'final_data' => $final_data,
            'count' => $count
        ];
    }

    private function generate_eoc_dropdown_form($index, $ao_ad, $ass_area_code, $user_id, $comments, $enabled, $event_name, $page) {   
        
        $id = "$ao_ad-$ass_area_code-$index-event-approval";
        $class = preg_replace('/\s+/', '_', "$ao_ad-$event_name-approvals");

        $recsWithEmail = array_map(function($object) use ($ao_ad, $comments) {
            return $this->add_email_field($object, $ao_ad, $comments);
        }, $comments);
        
        $recsWithEmail = json_encode($recsWithEmail, JSON_HEX_APOS | JSON_HEX_QUOT);

        return "
        <div class='bx--button__field-wrapper'>
            <input id='$id' type='hidden'
            class='$class'
            value='$recsWithEmail'
        />
            <button class='bx--btn bx--btn--sm bx--btn--primary' onclick='viewDropdownModal(`$id`, `$event_name`, `$user_id`, `$enabled`, `$page`)'>View</button>
        </div>
        ";
    }

    private function generate_eoc_event_form($index, $event_justification_data, $user_id, $enabled, $event_name, $page){

        $id = "$index-justification";
        
        $event_justification_data_sanitized = str_replace(
            [
                chr(92),        // Backslash (\)
                chr(13) . chr(10), // Carriage Return + New Line (\r\n)
                chr(13),        // Carriage Return only (\r)
                chr(10)         // New Line only (\n)
            ],
            [
                '\\\\', '\\r\\n', '\\r', '\\n'
            ],
            $event_justification_data
         );
        $event_justification_data_json = json_encode($event_justification_data_sanitized);

        //escape the json string to prevent any html issues
        $escaped_event_justification_data_json = htmlspecialchars($event_justification_data_json, ENT_QUOTES);
        $short_justification = strlen($event_justification_data)>200 ? substr($event_justification_data, 0, 200).'...' : $event_justification_data;

        $view_more_button = '';
        if(strlen($event_justification_data)>200){
            $view_more_button = "<button class='bx--btn bx--btn--sm bx--btn--primary' onclick='viewEventJustification(`$id`, `$escaped_event_justification_data_json`, `$user_id`, `$enabled`, `$event_name`, `$page`)'>View more</button>";
        }
        return "
        <div class='bx--button__field-wrapper'>
        <input id='$id' type='hidden' />
            <div id = 'event-justification-text-$id' class='bx--form__helper-text' style='font-size: 1em; line-height: 1.5; color: 333;'>
                $short_justification
            </div>
                $view_more_button
        </div>
        ";
    }

    public function get_email_by_id($object, $ao_ad, $comments) {
        $user_id = null;
        if ($ao_ad === 'ao') {
            $user_id = $object['AO_USER_ID']; 
        } elseif ($ao_ad === 'ad') {
            $user_id = $object['AD_USER_ID'];
        }
        $user = $this->SOCOM_Users_model->get_user($user_id);
        return $user[$user_id] ?? null;
    }
    
    public function add_email_field($object, $ao_ad, $comments) {
        $email = $this->get_email_by_id($object, $ao_ad, $comments);
        $object['email'] = $email;
        return $object;
    }
    
    private function generate_eoc_text_form($index, $ao_ad, $comments, $enabled, $event_name) {
        $id = preg_replace('/\s+/', '_', "$ao_ad-$event_name-$index-comments");

        $commentsWithEmail = array_map(function($object) use ($ao_ad, $comments) {
            return $this->add_email_field($object, $ao_ad, $comments);
        }, $comments);
        
        $commentsWithEmail = json_encode($commentsWithEmail, JSON_HEX_APOS | JSON_HEX_QUOT);

        return "
        <div class='bx--button__field-wrapper'>
            <input id='$id' type='hidden' 
            class='$ao_ad-$event_name-comments'
            value='$commentsWithEmail' />
            <button class='bx--btn bx--btn--sm bx--btn--primary' onclick='viewCommentModal(`$id`, `$event_name`, `$enabled`)'>View</button>
        </div>
        ";
    }
    
    private function find_delta_value_issue($summary_data, $params, $target) {
        $program_name = $params['PROGRAM_NAME'];
        $event_name = $params['EVENT_NAME'];
        $osd_program_elem_code = $params['OSD_PROGRAM_ELEM_CODE'];
        $special_project_code = $params['SPECIAL_PROJECT_CODE'];
        $cap_sponsor_code = $params['CAPABILITY_SPONSOR_CODE'];
        $resource_cat_code = $params['RESOURCE_CATEGORY_CODE'];
        $ass_area_code = $params['ASSESSMENT_AREA_CODE'];
        $pom_sponsor_code = $params['POM_SPONSOR_CODE'];
        $current_year = $params['CURRENT_YEAR'];

        foreach($summary_data as $value) {
            if (($value['EOC'] === $program_name) &&
                (strval($value['FISCAL_YEAR']) === strval($current_year)) &&
                ($value['EVENT_NAME'] === $event_name) &&
                ($value['OSD_PROGRAM_ELEMENT_CODE'] === $osd_program_elem_code) &&
                ($value['SPECIAL_PROJECT_CODE'] === $special_project_code) &&
                ($value['CAPABILITY_SPONSOR_CODE'] === $cap_sponsor_code) &&
                ($value['RESOURCE_CATEGORY_CODE'] === $resource_cat_code) &&
                ($value['ASSESSMENT_AREA_CODE'] === $ass_area_code) &&
                ($value['POM_SPONSOR_CODE'] === $pom_sponsor_code)
            ) {
                return $value[$target];
            }
        }
        return 0;
    }
    
    // Not in use
    // public function eoc_historical_pom($page) {

    //     $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
    //     if ($data_check['result']) {
    
    //         $post_data = $data_check['post_data'];
    //         $l_pom_sponsor = isset($post_data['pom']) ? $post_data['pom'] : $this->l_pom_sponsor;
    //         $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : $this->l_cap_sponsor;
    //         $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : $this->l_ass_area;
    //         $program = isset($post_data['program']) ? $post_data['program'] : '';
    //         $view = isset($post_data['view']) ? $post_data['view'] : '';

    //         $historical_data = $this->get_historical_pom_data(
    //             $page, $view, $program,$l_pom_sponsor, $l_cap_sponsor, $l_ass_area
    //         );

    //         $tags = $this->DBs->SOCOM_model->get_user_assigned_tag('LOOKUP_TAG');
    //         $bins = $this->DBs->SOCOM_model->get_user_assigned_bin_by_program($program);
    //         $bins = empty($bins) ? [] : implode(',<br/>', array_values($bins[0]));

    //         $historical_data_2024 = $historical_data['2024'];
    //         $historical_data_2025 = $historical_data['2025'];
    //         $historical_data_2026 = $historical_data['2026'];

    //         $formatted_data_2024 = $this->group_by_eoc_historical($historical_data_2024, $tags, $bins, 'historical', '24', true);
    //         $formatted_data_2025 = $this->group_by_eoc_historical($historical_data_2025, $tags, $bins, 'historical', '25', true);
    //         $formatted_data_2026 = $this->group_by_eoc_historical($historical_data_2026, $tags, $bins, 'historical', '26', true);
    
    //         // $year_list = $formatted_data_2024['fy'];
    //         $year_list = [2026, 2027, 2028, 2029, 2030];
    
    //         $formatted_grouped_data_2024 = [];
    //         $formatted_grouped_data_2025 = []; 
    //         $formatted_grouped_data_2026 = [];

    //         $formatted_grouped_data_2024= $formatted_data_2024['grouped_data'];
    //         $formatted_grouped_data_2025 = $formatted_data_2025['grouped_data'];
    //         $formatted_grouped_data_2026 = $formatted_data_2026['grouped_data'];
            
    //         $headers = [
    //             0 => [
    //                 'data' => 'program',
    //                 'title' => 'EOC',
    //             ],
    //             1 => [
    //                 'data' => 'ass_area_code',
    //                 'title' => 'Assessment Area',
    //             ],
    //             2 => [
    //                 'data' => 'pom_sponsor_code',
    //                 'title' => 'POM Sponsor',
    //             ],
    //             3 => [
    //                 'data' => 'cap_sponsor_code',
    //                 'title' => 'Capability Sponsor',
    //             ],
    //             // 5 => [
    //             //     'data' => 'tag',
    //             //     'title' => 'Tag',
    //             // ],
    //             6 => [
    //                 'data' => 'bin',
    //                 'title' => 'JCA Alignment',
    //             ],
    //             7 => [
    //                 'data' => 'resource_cat_code',
    //                 'title' => 'Resource Category',
    //             ],
    //             8 => [
    //                 'data' => 'position',
    //                 'title' => 'POM Position',
    //             ]
    //         ];
    
    //         foreach($year_list as $year) {
    //             $headers[] = [
    //                 'data' => $year,
    //                 'title' => $year,
    //                 'className' => 'editable'
    //             ];
    //         }
    //         $headers = array_merge($headers, [
    //             [
    //                 'data' => 'fydp',
    //                 'title' => 'FYDP',
    //             ],
    //             [
    //                 'data' => 'DT_RowId',
    //                 'title' => 'DT_RowId',
    //             ]
    //         ]);

    //         $formatted_program_data_2024 = $this->format_datatable_program_summary(
    //             'historical', $formatted_grouped_data_2024,$year_list, 1, '24', 5
    //         );
    //         $formatted_program_data_2025 = $this->format_datatable_program_summary(
    //             'historical', $formatted_grouped_data_2025,$year_list, end($formatted_program_data_2024)['DT_RowId'] + 1, '25', 5
    //         );
    //         $formatted_program_data_2026 = $this->format_datatable_program_summary(
    //             'historical', $formatted_grouped_data_2026,$year_list, 1, '26', 5
    //         );
    //         // only show ZBT Program Summary EXT, ZBT Requested, ZBT Requested Delta in 2026
    //         if ($page == 'zbt_summary'){
    //             unset($formatted_program_data_2026[3]);
    //             unset($formatted_program_data_2026[4]);
    //             unset($formatted_program_data_2026[5]);
    //             unset($formatted_program_data_2026[6]);
    //         } elseif ($page == 'issue') {
    //             unset($formatted_program_data_2026[5]);
    //             unset($formatted_program_data_2026[6]);
    //         }
        
    //         $historical_data = array_merge(
    //             $formatted_program_data_2024, $formatted_program_data_2025, $formatted_program_data_2026
    //         );
    //         // graph data
    //         $categories = $year_list;

    //         $graph_data = $this->get_eoc_historical_pom_graph_data(
    //             $page, $categories, $formatted_grouped_data_2026
    //         );
    
            
    //         $year_list[] = 'fydp';
    //         $year_index_start = 7;
    //         $year_indices = [
    //             7, 8, 9, 10, 11, 12
    //         ];
    
    //         $data = [
    //             'data' => [
    //                 'historical_pom' => [
    //                     'data' => $historical_data,
    //                     'headers' => $headers,
    //                     'yearList' => $year_list,
    //                     'indexOfYear' => $year_index_start,
    //                     'yearIndex' => $year_indices
    //                 ],
    //                 'historical_graph' => [
    //                     'categories' => $categories,
    //                     'program' => $program,
    //                     'data' => $graph_data
    //                 ]
    //             ],
    //             'program' => $program
    //         ];
    //         $this->load->view('SOCOM/eoc_historical_pom_data_view', $data);
    //     }
    // }

    //not in use
    // private function group_by_eoc_historical($summary_data, $tags, $bins, $page, $year='26', $hasDropdown = true) {
    //     $grouped_data = [];
    //     $base_k = $summary_data['base_k'];
    //     $fy = [];
    //     foreach($base_k as $index => $values) {
    //         if (!count($fy) && isset($values['FISCAL_YEARS'])) {
    //             $fy = explode(', ', $values['FISCAL_YEARS']);
    //         }
    //         $ass_area_code = $values["ASSESSMENT_AREA_CODE"];
    //         $resource_cat_code = $values["RESOURCE_CATEGORY_CODE"];
    //         $pom_sponsor_code = $values["POM_SPONSOR_CODE"];
    //         $cap_sponsor_code = $values["CAPABILITY_SPONSOR_CODE"];
    //         $tag_dropdown = $this->generate_tag_dropdown($tags, "$ass_area_code-$resource_cat_code-$index", $values['EOC']);
    //         $bin_dropdown = $bins; //$this->generate_bin_dropdown($bins, "$ass_area_code-$resource_cat_code-$index", $values['EOC']);
    //         $base_program_name = $values['EOC'];
    //         $base_value = intval($values['BASE_K']) ?? 0;
    //         $prop_value = isset($summary_data['prop_amt'][$index]) ? intval($summary_data['prop_amt'][$index]['PROP_AMT']) : 0;
    //         $delta_value = isset($summary_data['delta_amt'][$index]) ? intval($summary_data['delta_amt'][$index]['DELTA_AMT']) : 0;
    //         $base_position = $values["{$year}EXT"] ?? "{$year}EXT";
    //         $prop_position = isset($summary_data['prop_amt'][$index]) ? $summary_data['prop_amt'][$index]["{$year}ZBT_REQUESTED"] : "{$year}ZBT";
    //         $delta_position = isset($summary_data['delta_amt'][$index]) ? $summary_data['delta_amt'][$index]["{$year}ZBT_REQUESTED_DELTA"] : `ZBT DELTA`;

    //         if ($page == 'issue' || $page == 'historical') {
    //             $issue_prop_position = isset($summary_data['issue_prop_amt'][$index]) ? $summary_data['issue_prop_amt'][$index]["{$year}ISS_REQUESTED"] : "{$year}ISS";
    //             $issue_delta_position = isset($summary_data['issue_delta_amt'][$index]) ? $summary_data['issue_delta_amt'][$index]["{$year}ISS_REQUESTED_DELTA"] : 'ISS DELTA';
    //             $issue_prop_value = isset($summary_data['issue_prop_amt'][$index]) ? intval($summary_data['issue_prop_amt'][$index]['ISS_PROP_AMT']) : 0;
    //             $issue_delta_value = isset($summary_data['issue_delta_amt'][$index]) ? intval($summary_data['issue_delta_amt'][$index]['DELTA_AMT']) : 0;
    //         }

    //         if ($page == 'historical') {
    //             $pom_prop_position = isset($summary_data['pom_prop_amt'][$index]) ? $summary_data['pom_prop_amt'][$index]["{$year}POM_REQUESTED"] : "{$year}POM";
    //             $pom_delta_position = isset($summary_data['pom_delta_amt'][$index]) ? $summary_data['pom_delta_amt'][$index]["{$year}POM_REQUESTED_DELTA"] : "{$year}EXT to {$year}POM Delta";
    //             $pom_prop_value = isset($summary_data['pom_prop_amt'][$index]) ? intval($summary_data['pom_prop_amt'][$index]['POM_PROP_AMT']) : 0;
    //             $pom_delta_value = isset($summary_data['pom_delta_amt'][$index]) ? intval($summary_data['pom_delta_amt'][$index]['DELTA_AMT']) : 0;
    //         }

    //         $fiscal_year = strval($values['FISCAL_YEAR']);
    //         if (!isset($grouped_data[$base_program_name])) {
    //             $grouped_data[$base_program_name] = [
    //                 'base_k' => [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => $hasDropdown ? $tag_dropdown : '',
    //                     'bin' => $hasDropdown ? $bin_dropdown : '',
    //                     'position' => $base_position,
    //                     $fiscal_year=> $base_value,
    //                 ],
    //                 'prop_amt' => [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => '',
    //                     'bin' => $bin_dropdown,
    //                     'position' => $prop_position,
    //                     $fiscal_year => $prop_value
    //                 ],
    //                 'delta_amt' => [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => '',
    //                     'bin' => $bin_dropdown,
    //                     'position' => $delta_position,
    //                     $fiscal_year => $delta_value
    //                 ]
    //             ];

    //             if ($page == 'issue' || $page == 'historical') {
    //                 $grouped_data[$base_program_name]['issue_prop_amt'] = [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => '',
    //                     'bin' => $bin_dropdown,
    //                     'position' => $issue_prop_position,
    //                     $fiscal_year => $issue_prop_value
    //                 ];
    //                 $grouped_data[$base_program_name]['issue_delta_amt'] = [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => '',
    //                     'bin' => $bin_dropdown,
    //                     'position' => $issue_delta_position,
    //                     $fiscal_year => $issue_delta_value
    //                 ];
    //             }

    //             if ($page == 'historical') {
    //                 $grouped_data[$base_program_name]['pom_prop_amt'] = [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => '',
    //                     'bin' => $bin_dropdown,
    //                     'position' => $pom_prop_position,
    //                     $fiscal_year => $pom_prop_value
    //                 ];
    //                 $grouped_data[$base_program_name]['pom_delta_amt'] = [
    //                     'ass_area_code' => $ass_area_code,
    //                     'resource_cat_code' => $resource_cat_code,
    //                     'pom_sponsor_code' => $pom_sponsor_code,
    //                     'cap_sponsor_code' => $cap_sponsor_code,
    //                     'tag' => '',
    //                     'bin' => $bin_dropdown,
    //                     'position' => $pom_delta_position,
    //                     $fiscal_year => $pom_delta_value
    //                 ];
    //             }
    //         } else {
    //             $grouped_data[$base_program_name]['base_k'][$fiscal_year] = $base_value;
    //             $grouped_data[$base_program_name]['prop_amt'][$fiscal_year] = $prop_value;
    //             $grouped_data[$base_program_name]['delta_amt'][$fiscal_year] = $delta_value;
    //             if ($page == 'issue' || $page == 'historical') {
    //                 $grouped_data[$base_program_name]['issue_prop_amt'][$fiscal_year] = $issue_prop_value;
    //                 $grouped_data[$base_program_name]['issue_delta_amt'][$fiscal_year] = $issue_delta_value;
    //             }
    //             if ($page == 'historical') {
    //                 $grouped_data[$base_program_name]['pom_prop_amt'][$fiscal_year] = $pom_prop_value;
    //                 $grouped_data[$base_program_name]['pom_delta_amt'][$fiscal_year] = $pom_delta_value;
    //             }
    //         }
    //     }

    //     return [
    //         'fy' => $fy,
    //         'grouped_data' => $grouped_data
    //     ];
    // }

    //not in use
    // private function get_eoc_historical_pom_graph_data($page, $years, $historical_data) {
    //     $ext = [
    //         'name' => '26EXT',
    //         'color' =>  '#ed7d32',
    //         'data' => []
    //     ];
    //     $zbt = [
    //         'name' => '26ZBT',
    //         'data' => []
    //     ];
    //     $iss = [
    //         'name' => '26ISS',
    //         'data' => []
    //     ];

    //     foreach($years as $year) {

    //         $ext_sum = 0;
    //         $zbt_sum = 0;

    //         if ($page == 'issue') {
    //             $iss_sum = 0;
    //         }

    //         foreach($historical_data as $program) {    
    //             if (isset($program['base_k'][ $year])) {
    //                 $ext_sum += $program['base_k'][ $year];
    //             }

    //             if (isset($program['prop_amt'][ $year])) {
    //                 $zbt_sum += $program['prop_amt'][ $year];
    //             }

    //             if ($page == 'issue' && isset($program['issue_prop_amt'][ $year])) {

    //                 $iss_sum += $program['issue_prop_amt'][ $year];
    //             }
    //         }

    //         $ext['data'][] = $ext_sum;
    //         $zbt['data'][] = $zbt_sum;

    //         if ($page == 'issue') {
    //             $iss['data'][] = $iss_sum;
    //         }
    //     }

    //     $data = [$ext,  $zbt];
    //     if ($page == 'issue') {
    //         $data[] =  $iss;
    //     }

    //     return $data;
    // }

    private function get_pom_sponsor_list(){
        return array_column(
            $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM'),
            'SPONSOR_CODE'
        );
    }
}