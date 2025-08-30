<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Event_Summary extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';
    protected const DROP_DOWN_CHOICES = [
        'zbt_summary' => [
            'Approve',
            'Approve at Scale',
            'Disapprove'
        ],
        'issue' => [
            'Approve',
            'Approve at Scale',
            'Disapprove'
        ]
    ];

    protected const AD_CONSENSUS_FILTER_CHOICES = [
        'zbt_summary' => [
            'Approve',
            'Approve at Scale',
            'Disapprove',
            'Not Decided'
        ],
        'issue' => [
            'Approve',
            'Approve at Scale',
            'Disapprove',
            'Not Decided'
        ]
    ];

    protected const REVIEW_STATUS_CHOICES = [
        'zbt_summary' => [
            'Disapproval Flag',
            'No Disapproval Flag',
            'Unreviewed'
        ],
        'issue' => [
            'Approval Flag',
            'Approve at Scale Flag',
            'Disapprove Flag',
            'Unreviewed'
        ]
    ];
    
    public function __construct() {
        parent::__construct();
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_COA_model');
        $this->load->model('SOCOM_AOAD_model');
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Event_Funding_Lines_model');

        $this->load->model('SOCOM_Dynamic_Year_model');
        $this->load->library('SOCOM/Dynamic_Year');

        $this->ZBT_YEAR = $this->dynamic_year->getPomYearForSubapp('ZBT_SUMMARY_YEAR');
        $this->ZBT_FY = $this->ZBT_YEAR % 100;
        $this->ZBT_YEAR_LIST = $this->dynamic_year->getYearList($this->ZBT_YEAR);

        $this->ISS_YEAR = $this->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');
        $this->ISS_FY = $this->ISS_YEAR % 100;
        $this->ISS_YEAR_LIST = $this->dynamic_year->getYearList($this->ISS_YEAR);
    }

    public function event_summary($page, $event_name=null) {
        $page_data['page_title'] = "Event Summary";
        $page_data['page_tab'] = "Event Summary";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
            'select2.css',
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'essential/fontawesome_all.css',
            'handsontable.min.css',
            'SOCOM/socom_home.css',
            'SOCOM/gear_percentage.css'
        ];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

        // $data = $this->DBs->SOCOM_model->get_sum_budget_and_execution();

        // $formmatted_data = $this->format_sum_budget_and_execution($data);

        $breadcrumb_text = [
            'zbt_summary' => 'ZBT Summary',
            'issue' => 'Issue Summary'
        ];

        $year = [
            'zbt_summary' => $this->ZBT_YEAR,
            'issue' => $this->ISS_YEAR
        ][$page];

        // $graph_data = [];
        // $graph_data['data'] =  $formmatted_data['data'];
        // $graph_data["categories"] =  $formmatted_data['years'];
        $event_name_list = $this->DBs->SOCOM_model->get_event_name_list($page);
        $user_emails = $this->SOCOM_Users_model->get_users();
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $ao_data = false;//$this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name, $page, null);
        $ad_data = false; //$this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name, $page, null);
        $is_ao_user = $this->SOCOM_AOAD_model->is_ao_user();
        $is_ad_user = $this->SOCOM_AOAD_model->is_ad_user();


        $aoad_dropdown_choices = self::DROP_DOWN_CHOICES[$page];

        // $capability_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'CAPABILITY');
        // $pom_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM');
        // $ass_area = $this->DBs->SOCOM_model->get_assessment_area_code();

        if ($page == 'zbt_summary'){
            [$pomYear, $year_list] = get_years_zbt_summary();
        }
        elseif($page == 'issue'){
            [$pomYear, $year_list] = get_years_issue_summary();
        }
       
        $data['subapp_pom_year'] = $pomYear;
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;


        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/event_summary_view'
        , array_merge([
            'page' =>  $page,
            'breadcrumb_text' => $breadcrumb_text[$page],
            'event_names' => $event_name_list,
            'selected_event' => urldecode($event_name),
            'user_emails' => $user_emails,
            'user_id' => $user_id,
            'ao_data' => $ao_data,
            'ad_data' => $ad_data,
            'is_ad_user' => $is_ad_user,
            'is_ao_user' => $is_ao_user,
            'aoad_dropdown_choices' =>  $aoad_dropdown_choices
            // 'graphData' => $graph_data,
            // 'id' => 1,
            // 'capability_sponsor' => $capability_sponsor,
            // 'pom_sponsor' => $pom_sponsor,
            // 'ass_area' => $ass_area,
            // 'program' => [],
            // 'resource_category' => []
        ], $data)
        );
        $this->load->view('templates/close_view');
    }
    
    public function get_event_summary_data($page, $event_name) {
        $api_params = '';

        $api_endpoint = [
            'zbt_summary' => RHOMBUS_PYTHON_URL.'/socom/zbt/event_summary/?event_names='.$event_name,
            'issue' => RHOMBUS_PYTHON_URL.'/socom/iss/event_summary/?event_names='.$event_name
        ][$page];
        $response = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            $api_params,
            $api_endpoint
        );

        $decoded_event_name = urldecode($event_name);
        if ($page === 'issue') {
            $review_status_data = $this->SOCOM_Event_Funding_Lines_model->get_review_status_iss();
        } else 
            if ($page === 'zbt_summary') {
            $review_status_data = $this->SOCOM_Event_Funding_Lines_model->get_review_status_zbt();
        }

        $review_status_map = [];
            foreach($review_status_data as $row){
                $review_status_map[$row['EVENT_NAME']] = $row['FLAGS'] ?? $row['FLAG'];
            }

        $event_status = $this->SOCOM_AOAD_model->get_event_status($page, $event_name);
        $result = json_decode($response, true);
        sort($result['all_years']);
        $result['event_status'] = $event_status;
        $result['events'] = $result[$decoded_event_name];
        $result['event_justification'] = $result['event_justifications'][$decoded_event_name];
        $result['review_status']= $review_status_map[$decoded_event_name];
        $response = json_encode( $result, true);

        $http_status = $response ? 200 : 400;

        $this->output
            ->set_status_header($http_status)
            ->set_content_type('application/json')
            ->set_output($response);
    }

    public function get_exported_event_summary_data($page) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $event_names = $post_data['event_names'] ?? [];

        if ($page === 'zbt_summary') {
            $route_type = 'zbt';
        }  
        else {
            $route_type  ='iss';
        }

        $api_endpoint = RHOMBUS_PYTHON_URL . '/socom/' . $route_type . '/event_summary/events/export';

        $response = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($event_names),
            $api_endpoint
        );

        $result = json_decode($response, true);

        $response = [
            'data' => $result
        ];

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function get_overall_event_summary_data($page) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cap-sponsor']) ? $post_data['cap-sponsor']  : [];
            $l_ad_consensus = isset($post_data['ad-consensus']) ? $post_data['ad-consensus'] : [];
            $aac= isset($post_data['aac']) ? $post_data['aac'] : [];

            if ($page === 'issue') {
                $api = 'iss';
                $review_status_data = $this->SOCOM_Event_Funding_Lines_model->get_review_status_iss();
                $type_of_coa= 'iss-extract';
            } else if ($page === 'zbt_summary') {
                $api = 'zbt';
                $review_status_data = $this->SOCOM_Event_Funding_Lines_model->get_review_status_zbt();
                $type_of_coa= 'zbt-extract';
            }

            $review_status_map = [];
            foreach($review_status_data as $row){
                $review_status_map[$row['EVENT_NAME']] = $row['FLAGS'] ?? $row['FLAG'];
            }
            $params['capability_sponsor_code']= $l_cap_sponsor;
            $params['ass_area_code']= $aac;
            $event_name_list = $this->SOCOM_COA_model->get_coa_metadata($type_of_coa, $params);

            $event_names = array_values(array_unique(array_column($event_name_list, 'EVENT_NAME')));

            $api_endpoint = sprintf('%s/socom/%s/event_summary/events', RHOMBUS_PYTHON_URL, $api);
            $response = php_api_call(
                'POST',
                'Content-Type: ' . APPLICATION_JSON,
                json_encode( $event_names),
                $api_endpoint
            );
            
            $result = json_decode($response, true);

            $disapproved_events = array_keys(array_filter($result['ad_consensus'], function($value) {
                return $value == 'Disapprove';
            }));
            $disapproved_event_summary = $this->SOCOM_COA_model->get_event_summary_data($disapproved_events, $page);

            $table_data = $this->format_overall_event_summary_data($result, $l_ad_consensus);
            
            foreach($table_data as &$event){
                $event['REVIEW_STATUS'] = $review_status_map[$event['EVENT_NAME']] ?? 'Unreviewed';
            }

            $response = [
                'overall_sum' => $this->SOCOM_Event_Funding_Lines_model->get_summary_overall_sum($event_names, $l_ad_consensus, $page),
                'overall_sum_approve' => $this->SOCOM_Event_Funding_Lines_model->get_summary_overall_sum_approve($event_names, $l_ad_consensus, $page),
                'data' => $table_data,
                'all_years' => $result['all_years'],
                'final_ad_actions' => $this->SOCOM_AOAD_model->get_final_ad_granted_data_event_table($page),
                'proposed_disapproved_programs' => $this->format_proposed_disapproved_program(
                    $disapproved_event_summary, $disapproved_events
                )
            ];

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
                
        }
    }

    private function format_proposed_disapproved_program($event_data, $events) {
        $proposed_disapproved_program = [];
        foreach ($events as $event) {
            $proposed_disapproved_program[$event] = [];
            foreach($event_data[$event] as $info) {
                foreach($info['FISCAL_YEAR'] as $fy => $value) {
                    if (!isset($proposed_disapproved_program[$event][$fy])) {
                        $proposed_disapproved_program[$event][$fy] = $value;
                    }
                    else {
                        $proposed_disapproved_program[$event][$fy] += $value;
                    }
                }
            }
           
        }
        return $proposed_disapproved_program;
    }

    public function format_overall_event_summary_data($data, $l_ad_consensus) {
        $event_titles = $data['event_title'];

    
        $events = $data['events'];
        $table_data = [];

        foreach ($events as $event_name => $event_info) {
            if (in_array($event_info['AD_CONSENSUS'],  $l_ad_consensus)) {
                #$event_title = $event_info['EVENT_TITLE'];
                $recommendation = $event_info['AD_CONSENSUS'];
                $aac= $event_info['ASSESSMENT_AREA_CODE'];

                $table_data[] = [
                    'EVENT_NAME' => $event_name,
                    'ISSUE_CAP_SPONSOR' => implode(', ', $event_info['CAPABILITY_SPONSOR_CODE']),
                    'EVENT_TITLE' => $event_titles[$event_info['EVENT_NAME']],
                    'FISCAL_YEAR' => $event_info['FISCAL_YEAR'],
                    'AD_CONSENSUS' => $recommendation,
                    'ASSESSMENT_AREA_CODE'=> $aac
                ];
            }
        }
        return $table_data;
    }

    public function overall_event_summary($page) {
        $page_data['page_title'] = "Overall Event Summary";
        $page_data['page_tab'] = "Overall Event Summary";
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
            'select2.css',
            'carbon-light-dark-theme.css',
            'datatables.css',
            'jquery.dataTables.min.css',
            'responsive.dataTables.min.css',
            'SOCOM/socom_home.css'
        ];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $selected_cap_sponsor = [];
        $data_check = $this->DB_ind_model->validate_post($this->input->get()); //validating input posts

        $raw_cs  = $this->input->get('cap-sponsor');      // e.g. "AFSOC,MARSOC"
        $raw_ad  = $this->input->get('ad-consensus');     // e.g. "Approve,Disapprove"
        $raw_rs  = $this->input->get('review-status');    // e.g. "Flagged,Unreviewed"
        $raw_aac = $this->input->get('aac');              // e.g. "A,B,C"
    
        // Explode into arrays (or empty array if nothing passed)
        
        $selected_ad_consensus = $raw_ad  !== null ? explode(',', $raw_ad)  : [];
        $selected_review_status = $raw_rs  !== null ? explode(',', $raw_rs)  : [];
        $selected_aac          = $raw_aac !== null ? explode(',', $raw_aac) : [];


        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            if(isset($post_data['cap-sponsor']) && is_array($post_data['cap-sponsor'])){
                $selected_cap_sponsor = $post_data['cap-sponsor'];
            } elseif($raw_cs  !== null){
                $selected_cap_sponsor = explode(',', $raw_cs);
            }
        }

        // $data = $this->DBs->SOCOM_model->get_sum_budget_and_execution();

        // $formmatted_data = $this->format_sum_budget_and_execution($data);

        $breadcrumb_text = [
            'zbt_summary' => 'ZBT Summary',
            'issue' => 'Issue Summary'
        ];

  
        $capability_sponsor_list = $this->DBs->SOCOM_model->get_capability_sponsor_code($page);
        // $graph_data = [];
        // $graph_data['data'] =  $formmatted_data['data'];
        // $graph_data["categories"] =  $formmatted_data['years'];
        $event_name_list = $this->DBs->SOCOM_model->get_event_name_list($page);
        $user_emails = $this->SOCOM_Users_model->get_users();
        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $ao_data = false;//$this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name, $page, null);
        $ad_data = false;//$this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name, $page, null);
        $is_ao_user = $this->SOCOM_AOAD_model->is_ao_user();
        $is_ad_user = $this->SOCOM_AOAD_model->is_ad_user();
        $aoad_dropdown_choices = self::DROP_DOWN_CHOICES[$page];
        $ad_consensus_filter_choices = self::AD_CONSENSUS_FILTER_CHOICES[$page];
        $review_status_choices = self::REVIEW_STATUS_CHOICES[$page];
        $aac_list= $this->DBs->SOCOM_model->get_aac_code($page);

        if ($page == 'zbt_summary'){
            [$data['subapp_pom_year'], $year_list] = get_years_zbt_summary();
        }
        elseif($page == 'issue'){
            [$data['subapp_pom_year'], $year_list] = get_years_issue_summary();
        }
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;
        
        $this->load->view('templates/header_view', $page_data);
        $this->load->view('SOCOM/overall_event_summary_view'
        , array_merge([
            'page' =>  $page,
            'breadcrumb_text' => $breadcrumb_text[$page],
            'capability_sponsor' => $capability_sponsor_list,
            'aoad_dropdown_choices' => $aoad_dropdown_choices,
            'ad_consensus_filter_choices' => $ad_consensus_filter_choices,
            'review_status_choices' => $review_status_choices,
            'aac_list' => $aac_list,
            'selected_cap_sponsor' => $selected_cap_sponsor,
            'selected_ad_consensus'    => $selected_ad_consensus,
            'selected_review_status'   => $selected_review_status,
            'selected_aac'  => $selected_aac,
            // 'pom_sponsor' => $pom_sponsor,
            // 'ass_area' => $ass_area,
            // 'program' => [],
            // 'resource_category' => []
        ], $data)
        );
        $this->load->view('templates/close_view');
    }

    public function get_ao_ad_data($page, $event_name) {
        $event_name = urldecode($event_name);
        $ao_data = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name, $page, null);
        $ad_data = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name, $page, null);
        $ad_final_data = $this->SOCOM_AOAD_model->get_final_ad_by_event_id_user_id($event_name, $page, null, true);
        if ($ad_final_data !== false) {
            $ad_final_data = [$ad_final_data];
        } else {
            $ad_final_data = [];
        }

        $response = [
            'AO_DATA' => $ao_data,
            'AD_DATA' => $ad_data,
            'FINAL_AD_DATA' => $ad_final_data
        ];

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($response))
            ->_display();
        exit();
    }

}