<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_COA extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';
    public function __construct() {
        parent::__construct();
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_COA_model');
        $this->load->model('SOCOM_Program_model');
        $this->load->model('SOCOM_Users_model');
        $this->load->helper('general');
        $this->load->helper('coa');
        $this->load->library('SOCOM/Dynamic_Year');
        $this->output_header_keys = [
            'Program' => 'PROGRAM_CODE',
            'POM SPONSOR' => 'POM_SPONSOR_CODE',
            'CAP SPONSOR' => 'CAPABILITY_SPONSOR_CODE',
            'ASSESSMENT AREA' => 'ASSESSMENT_AREA_CODE'
        ];
        $this->weighted_score_header_keys = [
            'StoRM Score' => 'total_storm_scores',
            'Guidance Score' => 'weighted_guidance_score',
            'POM Score' => 'weighted_pom_score'
        ];

        $this->weighted_storm_headers = [
            'storm' => ['StoRM Score'],
            'weighted' => [
                'pom' => ['POM Score'],
                'guidance' => ['Guidance Score'],
                'both' => ['POM Score', 'Guidance Score']
            ]
        ];
        $this->FY = $this->dynamic_year->getPomYearForSubapp('RESOURCE_CONSTRAINED_COA_YEAR');

        $this->weighted_score_option_headers = [
            'POM' => [
                'POM' => 'POM'
            ],
            'GUIDANCE' => [
                'GUIDANCE' => 'GUIDANCE'
            ],
            'BOTH' => [
                'POM' => 'POM',
                'GUIDANCE' => 'GUIDANCE'
            ],
            'STORM' => [
                'STORM' => 'STORM'
            ]
        ];

        $this->weighted_score_option = [
            1 => 'both',
            2 => 'guidance',
            3 => 'pom',
            4 => 'storm'
        ];

        $this->eoc_code_chart_colors = [
            'RDT&E $' => '#90ee90',
            'RDTE $' => '#90ee90',
            'PROC $' => '#000000',
            'O&M $' => '#ADD8E6',
            'MILCON $' => '#FF8C00',
            'CIV PRPY' => '#800080',
            'MIL PRPY' => '#FF00FF',
            'None $' => '#FF0000'
        ];
        $this->treemap_colors = [];

        [$pom_year, $years_list] = get_years_coa(true);

        $this->requested_funding = [];
        $this->proposed_funding = [];
        $this->all_funding = [];

        foreach ($years_list as $year) {
            $this->requested_funding[] = ['data' => 'RF_FY' . $year, 'title' => 'FY' . $year];
            $this->proposed_funding[] = ['data' => 'PF_FY' . $year, 'title' => 'FY' . $year];
            $this->all_funding[] = ['data' => 'FY' . $year, 'title' => 'FY' . $year];
        }
        
        $this->requested_funding[] = ['data' => 'RF_FYDP_DELTA', 'title' => 'FYDP Delta'];
        $this->proposed_funding[] = ['data' => 'PF_FYDP_DELTA', 'title' => 'FYDP Delta'];
        $this->all_funding[] = ['data' => 'FYDP_DELTA', 'title' => 'FYDP Delta'];

        $this->detailed_summary_headers = [
            'eoc-code' => [
                ['data' => 'PROGRAM', 'title' => 'PROGRAM'],
                ['data' => 'PROGRAM_GROUP', 'title' => 'PROGRAM GROUP'],
                ['data' => 'EOC', 'title' => 'EOC'],
                ['data' => 'CAP_SPONSOR', 'title' => 'CAP SPONSOR'],
                ['data' => 'RESOURCE_CATEGORY', 'title' => 'RESOURCE CATEGORY'],
                ['data' => 'OSD_PROGRAM_ELEMENT_CODE', 'title' => 'OSD PE CODE'],
                ['data' => 'FYDP', 'title' => 'FYDP']
            ],
            'jca-alignment' => [
                'covered' => [
                    ['data' => 'JCA_ALIGNMENT', 'title' => 'JCA Alignment'],
                    ['data' => 'DESCRIPTION', 'title' => 'JCA Description'],
                    ['data' => 'PROGRAM_BREAKDOWN', 'title' => 'Program Breakdown'],
                ],
                'noncovered' => [
                    ['data' => 'JCA_ALIGNMENT', 'title' => 'JCA Alignment'],
                    ['data' => 'DESCRIPTION', 'title' => 'JCA Description']
                ],
                'program_breakdown' => [
                    ['data' => 'PROGRAM', 'title' => 'PROGRAM'],
                    ['data' => 'CAP_SPONSOR', 'title' => 'CAP SPONSOR'],
                    ['data' => 'RESOURCE_K', 'title' => 'RESOURCE $K']
                ]
            ],
            'kop-ksp' => [
                'covered' => [
                    ['data' => 'KOP_KSP', 'title' => 'KOPs/KSPs'],
                    ['data' => 'DESCRIPTION', 'title' => 'KOPs/KSPs Description'],
                    ['data' => 'PROGRAM_BREAKDOWN', 'title' => 'Program Breakdown'],
                ],
                'noncovered' => [
                    ['data' => 'KOP_KSP', 'title' => 'KOPs/KSPs'],
                    ['data' => 'DESCRIPTION', 'title' => 'KOPs/KSPs Description']
                ],
                'program_breakdown' => [
                    ['data' => 'PROGRAM', 'title' => 'PROGRAM'],
                    ['data' => 'CAP_SPONSOR', 'title' => 'CAP SPONSOR'],
                    ['data' => 'RESOURCE_K', 'title' => 'RESOURCE $K']
                ]
            ],
            'capability-gaps' => [
                'covered' => [
                    ['data' => 'CAPABILITY_GAPS', 'title' => 'Capability Gaps'],
                    ['data' => 'DESCRIPTION', 'title' => 'Capability Gaps Description'],
                    ['data' => 'PROGRAM_BREAKDOWN', 'title' => 'Program Breakdown'],
                ],
                'noncovered' => [
                    ['data' => 'CAPABILITY_GAPS', 'title' => 'Capability Gaps'],
                    ['data' => 'DESCRIPTION', 'title' => 'Capability Gaps Description']
                ],
                'program_breakdown' => [
                    ['data' => 'PROGRAM', 'title' => 'PROGRAM'],
                    ['data' => 'CAP_SPONSOR', 'title' => 'CAP SPONSOR'],
                    ['data' => 'RESOURCE_K', 'title' => 'RESOURCE $K']
                ]
            ],
            'issue-analysis' => [
                'event' => [
                    'summary' => [ // for Detailed Summary
                        ['data' => 'EVENT_NAME', 'title' => 'Event Name'],
                        ['data' => 'PROPOSED_CHANGES', 'title' => 'Proposed Changes']
                    ],
                    'summary_proposed_funding_modal' => [ // for Detailed Summary Proposed Changes modal
                        ['data' => 'PROGRAM_CODE', 'title' => 'Program'],
                        ['data' => 'EOC_CODE', 'title' => 'EOC'],
                        ['data' => 'CAPABILITY_SPONSOR', 'title' => 'Capability Sponsor'],
                        ['data' => 'ASSESSMENT_AREA', 'title' => 'Assessment Area'],
                        ['data' => 'RESOURCE_CATEGORY', 'title' => 'Resource Category'],
                        ['data' => 'OSD_PE', 'title' => 'OSD PE Code'],
                        ...$this->all_funding

                    ],
                    'comparison' => [ // for Detailed Comparison
                        ['data' => 'EVENT_NAME', 'title' => 'Event Name'],
                        ['data' => 'EVENT_TITLE', 'title' => 'Event Title']
                    ],
                    'comparison_event_details_modal' => [ // for Detailed Comparison Event Details modal
                        ['data' => 'PROGRAM_CODE', 'title' => 'Program'],
                        ['data' => 'EOC_CODE', 'title' => 'EOC'],
                        ['data' => 'CAPABILITY_SPONSOR', 'title' => 'Capability Sponsor'],
                        ['data' => 'ASSESSMENT_AREA', 'title' => 'Assessment Area'],
                        ['data' => 'RESOURCE_CATEGORY', 'title' => 'Resource Category'],
                        ['data' => 'OSD_PE', 'title' => 'OSD PE Code'],
                        ['data' => 'DELTA_LINE', 'title' => 'Delta Line'],
                        ...$this->all_funding
                    ]
                ],
                'program_eoc' =>
                [
                    'eoc_information' =>[
                        'summary' =>  [ // for both Detailed Summary
                            ['data' => 'PROGRAM_CODE', 'title' => 'Program'],
                            ['data' => 'EOC_CODE', 'title' => 'EOC'],
                            ['data' => 'CAPABILITY_SPONSOR', 'title' => 'Capability Sponsor'],
                            ['data' => 'ASSESSMENT_AREA', 'title' => 'Assessment Area'],
                            ['data' => 'RESOURCE_CATEGORY', 'title' => 'Resource Category'],
                            ['data' => 'OSD_PE', 'title' => 'OSD PE Code'],
                            ['data' => 'EVENT_NAME', 'title' => 'Event Name'],
                            ...$this->requested_funding,
                            ...$this->proposed_funding
                        ],
                        'comparison' => [ // for Detailed Comparison
                            ['data' => 'PROGRAM_CODE', 'title' => 'Program'],
                            ['data' => 'EOC_CODE', 'title' => 'EOC'],
                            ['data' => 'CAPABILITY_SPONSOR', 'title' => 'Capability Sponsor'],
                            ['data' => 'ASSESSMENT_AREA', 'title' => 'Assessment Area'],
                            ['data' => 'RESOURCE_CATEGORY', 'title' => 'Resource Category'],
                            ['data' => 'OSD_PE', 'title' => 'OSD PE Code'],
                            ['data' => 'EVENT_NAME', 'title' => 'Event Name'],
                            ['data' => 'DELTA_LINE', 'title' => 'Delta Line'],
                            ...$this->all_funding
                        ]
                    ],
                    'requested_funding' => $this->requested_funding,
                    'proposed_funding' => $this->proposed_funding,
                    'all_funding' => $this->all_funding,
                ],
                'fiscal_years' => $years_list
            ]
        ];

        $this->program_id_format = [
            'ISS_EXTRACT' => ['PROGRAM_CODE', 'CAPABILITY_SPONSOR_CODE', 'POM_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE'],
            'RC_T' => [
                'PROGRAM_CODE', 'CAPABILITY_SPONSOR_CODE', 'POM_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE', 
                'EXECUTION_MANAGER_CODE', 'RESOURCE_CATEGORY_CODE', 'EOC_CODE', 'OSD_PROGRAM_ELEMENT_CODE'
            ],
        ];
    }

    // --------------------------------------------------------------------

    /**
     * 
     *     
     * 
     */
    public function get_coa_user_list(){
        
        
        $http_status = 200;

        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];

        $iss_extract = filter_var($post_data['iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
            
        $type_of_coa = $iss_extract ? 'ISS_EXTRACT' : 'RC_T';

        $response = $this->SOCOM_COA_model->get_my_coa($type_of_coa);

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response]));
    }

    // --------------------------------------------------------------------

    /**
     * 
     *     
     * 
     */
    public function get_coa_user_data(){
        // Validate post data.
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $ids = $post_data['ids'] ?? [];

        $response = [];
        $program_group_map = [];
        $http_status = 406;
        if (is_array($ids)) {
            $ids = array_map('intval', $ids);
            $response = $this->SOCOM_COA_model->get_user_saved_coa_data($ids);

            if (!empty($response)) {
                $http_status = 200;
                foreach($response as &$coa) {
                    // get program group map
                    $type_of_coa = $coa['TYPE_OF_COA'];
                    $override_table_session = json_decode($coa['OVERRIDE_TABLE_SESSION'] ?? '' , true);
                    if (isset($override_table_session['ProgramIDs'])) {
                        $program_ids = $override_table_session['ProgramIDs'];
                        $program_group_map = $this->SOCOM_COA_model->get_program_group_map($type_of_coa, $program_ids);
                    }
                    $coa['program_group_map'] = $program_group_map;

                    // get original rows
                    if ($type_of_coa === 'RC_T') {
                        $coa['original_resource_k'] = $this->get_original_resource_k($coa);
                    }
                }
            }

            $year_lists = array_column($response, 'YEAR_LIST');
            $fy = json_decode($year_lists[0], true);
            if (
                count($year_lists) > 1 && $year_lists[0] !== $year_lists[1] ||
                count($year_lists) > 2 && $year_lists[0] !== $year_lists[2] ||
                count($fy) !== 5
            ) {
                $http_status = 500;
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response, 'FY' => $fy, 'program_group_map' => $program_group_map]));
    }

    // --------------------------------------------------------------------

    /**
     * Saves coa. Called by clicking the save button.
     */
    public function save_coa()
    {
        // Validate post data.
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $id = (int)trim($post_data['id']);
        $name = trim($post_data['name']);
        $description = trim($post_data['description']);
        
        $response = ['status' => 'success', 'message' => ''];
        $http_status = 201;
        if (is_int($id)) {
            $result = $this->SOCOM_COA_model->store_user_run($id, $name, $description);
        }

        if ($result != false) {
            $http_status = 201;
            $response['status'] = 'success';
            $response['message'] = 'User COA saved, click "Load COA" to view.';
        } else {
            $http_status = 406;
            $response['status'] = 'error';
            $response['message'] = 'Unable to save COA';
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }





    // --------------------------------------------------------------------
    /**
     * Load program list to insert modal dropdown
     *
     * @param scenario_id
     * @return  html
     */
    public function insert_coa_table_row($scenario_id){
        //Validate post data.
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        $post_data = $data_check['post_data'];
        $score_option = performIssetTenaryOp(
            isset($post_data['score_option']),
            $post_data,
            'score_option',
            ''
        );
        $match_row_ids = performIssetTenaryOp(
            isset($post_data['match_row_ids']),
            $post_data,
            'match_row_ids',
            ''
        );
        $match_row_ids = json_decode($match_row_ids, JSON_OBJECT_AS_ARRAY);
        $mdata = $this->SOCOM_COA_model->get_metadata($scenario_id, true);

        // get selected program codes
        $coa_type = $mdata['TYPE_OF_COA'] == 'ISS_EXTRACT' ? 'iss-extract' : 'rc-t';
        $response = $this->SOCOM_COA_model->get_coa_metadata($coa_type , []);
        $program_codes = array_values(array_unique(array_column($response, 'PROGRAM_CODE')));

        $fiscal_years = json_decode($this->SOCOM_COA_model->get_metadata($scenario_id)['YEAR_LIST'], true);

        $page_data = array();

        // get program list
        $page_data['program_code_list'] = $this->SOCOM_COA_model->get_unselected_programs(
            $program_codes,
            $match_row_ids,
            $mdata['TYPE_OF_COA'],
            'PROGRAM_CODE'
        );
        
        // get headers
        $page_data['headers'] = [
            'PROGRAM_CODE' => 'PROGRAM',
            'EOC_CODE' => 'EOC CODE',
            'CAPABILITY_SPONSOR_CODE' => 'CAPABILITY SPONSOR CODE',
            'ASSESSMENT_AREA_CODE' => 'ASSESSMENT AREA CODE',
            'RESOURCE_CATEGORY_CODE' => 'RESOURCE CATEGORY CODE'
        ];

        if ($mdata['TYPE_OF_COA'] == 'ISS_EXTRACT' ) {
            $page_data['headers'] = array_merge(
                $page_data['headers'],
                [
                    'EVENT_NAME' => 'EVENT NAME', 
                    'OSD_PROGRAM_ELEMENT_CODE' => 'OSD PE Code'
                ]
            );
        }
        else{
            $page_data['headers'] = array_merge(
                $page_data['headers'],
                [
                    'EXECUTION_MANAGER_CODE' => 'EXECUTION MANAGER', 
                    'OSD_PROGRAM_ELEMENT_CODE' => 'OSD PE Code'
                ]
            );
        }

        $page_data['headers'] = array_merge(
            $page_data['headers'],
            $this->weighted_score_option_headers[$score_option]
        );

        $year_headers = array_combine(
            $keys = array_map(function($y) { return 'FY' . substr($y, -2); }, $fiscal_years),
            $keys
        );

        $year_headers_map = array_combine(
            array_values($fiscal_years),
            array_keys($year_headers)
        );

        $page_data['headers'] =  array_merge($page_data['headers'], $year_headers);
        $page_data['year_headers'] = $year_headers;
        $page_data['headers_map'] = [
            'Program' => 'PROGRAM_CODE',
            'EOC' => 'EOC_CODE',
            'POM SPONSOR' => 'POM_SPONSOR_CODE',
            'CAP SPONSOR' => 'CAPABILITY_SPONSOR_CODE',
            'ASSESSMENT AREA' => 'ASSESSMENT_AREA_CODE',
            'StoRM Score' => 'STORM',
            'POM Score' => 'POM',
            'Guidance Score' => 'GUIDANCE',
            'RESOURCE CATEGORY' => 'RESOURCE_CATEGORY_CODE',
            'Event Name' => 'EVENT_NAME',
            'OSD PE' => 'OSD_PROGRAM_ELEMENT_CODE',
            'EXECUTION MANAGER' => 'EXECUTION_MANAGER_CODE'
        ] +  $year_headers_map;

        $page_data['weighted_score_keys'] = array_keys($this->weighted_score_option_headers);
        $page_data['scenario_id'] = $scenario_id;
        $page_data['current_year'] = $this->FY;
        $page_data['user_id'] =  $this->session->userdata('logged_in')['id'];
        $page_data['type_of_coa'] = $mdata['TYPE_OF_COA'];
        $page_data['disable_columns'] = ['EXECUTION_MANAGER_CODE'];
        
        $this->load->view('SOCOM/optimizer/coa_simulation_table_insert_view.php', $page_data);
    }

    // --------------------------------------------------------------------
    /**
     * Update insert modal dropdown
     *
     * @param scenario_id
     * @return  html
     */
    public function update_coa_table_insert_dropdown($scenario_id) {
        //Validate post data.
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }
        $post_data = $data_check['post_data'];
        $program_code = isset($post_data['program_code']) ? [$post_data['program_code']] : [];
        $eoc_code = isset($post_data['eoc_code']) && $post_data['eoc_code'] != '' ? 
                            [$post_data['eoc_code']] : [];
        $capability_sponsor_code = isset($post_data['capability_sponsor_code']) && $post_data['capability_sponsor_code'] != '' ?
                                    [$post_data['capability_sponsor_code']] : [];
        $resource_category_code = isset($post_data['resource_category_code']) && $post_data['resource_category_code'] != '' ?
                                    [$post_data['resource_category_code']] : [];
        $ass_area_code = isset($post_data['ass_area_code']) && $post_data['ass_area_code'] != '' ?
                                    [$post_data['ass_area_code']] : [];
        $event_name = isset($post_data['event_name']) && $post_data['event_name'] != '' ?
                                    [$post_data['event_name']] : [];
        $execution_manager_code = isset($post_data['execution_manager_code']) && $post_data['execution_manager_code'] != '' ?
                                    [$post_data['execution_manager_code']] : [];
        $osd_pe_code = isset($post_data['osd_pe_code']) && $post_data['osd_pe_code'] != '' ?
                                    [$post_data['osd_pe_code']] : [];
        $eoc_codes_filter = isset($post_data['eoc_codes_filter']) ? $post_data['eoc_codes_filter'] : [];

        $match_row_ids = json_decode(performIssetTenaryOp(isset($post_data['match_row_ids']), $post_data,  'match_row_ids', '[]'));
        
        $response = [];
        $http_status = 406;
        if (!empty($program_code)) {
            $mdata = $this->SOCOM_COA_model->get_metadata($scenario_id, true);
            $type_of_coa = str_replace("_", "-", strtolower($mdata['TYPE_OF_COA']));

            $params = [
                'program_code' => $program_code,
                'eoc_code' => $eoc_code,
                'capability_sponsor_code' => $capability_sponsor_code,
                'ass_area_code' => $ass_area_code,
                'resource_category_code' => $resource_category_code,
                'event_name' => $event_name,
                'osd_pe_code' => $osd_pe_code,
                'execution_manager_code' => $execution_manager_code
            ];

            $response = $this->SOCOM_COA_model->get_coa_metadata($type_of_coa, $params, true);

            // filter out the result by match_row_ids from rows in the manual override table 
            $result = $this->get_filtered_coa_table_insert_dropdown_options(
                $response, $match_row_ids, $mdata['TYPE_OF_COA']
            );

            if (!empty($result)) {
                $program_id = $result[0]['ID'];
                $http_status = 200;
                $response['dropdown'] = [];
                $weighted_score = $this->get_saved_coa_score($scenario_id, [$program_id], $mdata['TYPE_OF_COA']);
                $eoc_code_value =  $this->SOCOM_COA_model->get_dropdown_codes(
                    'EOC_CODE',
                    $program_code,
                    $eoc_codes_filter,
                    null,
                    $mdata['TYPE_OF_COA']
                );

                $response['dropdown']['EOC_CODE'] = $this->convert_coa_column_values(
                    $result,
                    'EOC_CODE',
                    $eoc_code_value
                );
                $response['dropdown']['CAPABILITY_SPONSOR_CODE'] =  $this->convert_coa_column_values($result, 'CAPABILITY_SPONSOR_CODE');
                $response['dropdown']['ASSESSMENT_AREA_CODE'] =  $this->convert_coa_column_values($result, 'ASSESSMENT_AREA_CODE');
                $response['dropdown']['POM_SPONSOR_CODE'] = $this->convert_coa_column_values($result, 'POM_SPONSOR_CODE');
                $response['dropdown']['RESOURCE_CATEGORY_CODE'] = $this->convert_coa_column_values($result, 'RESOURCE_CATEGORY_CODE');
                $response['dropdown']['EVENT_NAME'] = $this->convert_coa_column_values($result, 'EVENT_NAME');
                $response['dropdown']['OSD_PROGRAM_ELEMENT_CODE'] = $this->convert_coa_column_values($result, 'OSD_PROGRAM_ELEMENT_CODE');
                $response['dropdown']['EXECUTION_MANAGER_CODE'] = $this->convert_coa_column_values($result, 'EXECUTION_MANAGER_CODE');
                $response['weighted_score'] = !empty($weighted_score) && isset($weighted_score[$program_id]) ?
                    $weighted_score[$program_id] : [];

                $cond_1 = (count($response['dropdown']['EOC_CODE']) === 1 &&
                count($response['dropdown']['CAPABILITY_SPONSOR_CODE']) === 1 &&
                count($response['dropdown']['RESOURCE_CATEGORY_CODE']) === 1 &&
                count($response['dropdown']['ASSESSMENT_AREA_CODE']) === 1 );

                $cond_2 = true;
                if ($mdata['TYPE_OF_COA'] == 'ISS_EXTRACT' ) {
                    $cond_2 = (count($response['dropdown']['OSD_PROGRAM_ELEMENT_CODE']) === 1 &&
                                count($response['dropdown']['EVENT_NAME']) === 1);
                }

                $response['ID'] = ($cond_1 && $cond_2 ) ? $program_id : '';
            }
        }
        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response]));
    }



    private function get_filtered_coa_table_insert_dropdown_options($data, $match_row_ids, $type_of_coa) {
        $result = [];
        if ($type_of_coa == 'ISS_EXTRACT') {
            $columns = ['EOC_CODE', 'POM_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE', 'CAPABILITY_SPONSOR_CODE', 'EVENT_NAME', 'OSD_PROGRAM_ELEMENT_CODE','RESOURCE_CATEGORY_CODE'];
        }
        else {
            $columns = ['EOC_CODE', 'POM_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE', 'CAPABILITY_SPONSOR_CODE', 'EXECUTION_MANAGER_CODE', 'OSD_PROGRAM_ELEMENT_CODE', 'RESOURCE_CATEGORY_CODE'];
        }
        foreach($data as $row) {
            $row_id = [];
            foreach($columns as $column) {
                $row_id[] = $row[$column];
            }
            if (!in_array(implode('_', $row_id ), $match_row_ids)) {
                $result[] = $row;
            }
        }
        return $result;
    }

    private function get_saved_coa_score($saved_coa_id, $program_ids, $type_of_coa) {
        $optimizer_input = $this->SOCOM_COA_model->get_saved_coa_optimizer_input($saved_coa_id);
        if ($optimizer_input) {
            $weight_id = $optimizer_input['weight_id'];
            return $this->SOCOM_COA_model->get_weighted_score($weight_id, $program_ids, $type_of_coa);
        }
        else {
            return $this->SOCOM_COA_model->get_manual_override_weighted_score($saved_coa_id);
        }
    }

    public function get_coa_table_row_budget($scenario_id) {
        //Validate post data.
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }
        $post_data = $data_check['post_data'];
        $program_id = $post_data['program_id'] ?? "";
        $eoc_code = $post_data['eoc_code'] ?? "";
        $capability_sponsor_code = $post_data['capability_sponsor_code'] ?? "";
        $ass_area_code = $post_data['ass_area_code'] ?? "";
        $resource_category_code = $post_data['resource_category_code'] ?? "";
        $event_name = $post_data['event_name'] ?? "";
        $osd_pe_code = $post_data['osd_pe_code'] ?? "";
        $execution_manager_code = $post_data['execution_manager_code'] ?? "";

        $response = [];
        $http_status = 406;
        if ($program_id != "") {
            $mdata = $this->SOCOM_COA_model->get_metadata($scenario_id);
            if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {
                $result = $this->SOCOM_COA_model->fetchOutputInfoIssExtract([$program_id]);
                $resource_or_delta = 'DELTA_AMT';
            } else {
                $result = json_decode($this->SOCOM_COA_model->fetchOutputInfo([$program_id]), true);
                $resource_or_delta = 'RESOURCE_K';
            }

            $fy_values = null;
            foreach($result as $funding) {

                $cond = $funding['EOC_CODE'] === $eoc_code &&
                        $funding['CAPABILITY_SPONSOR_CODE'] === $capability_sponsor_code &&
                        $funding['ASSESSMENT_AREA_CODE'] === $ass_area_code &&
                        $funding['RESOURCE_CATEGORY_CODE'] === $resource_category_code;
                
                if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {
                    $cond = $cond && $funding['EVENT_NAME'] === $event_name &&
                    $funding['OSD_PE'] === $osd_pe_code;
                }
                else {
                    $cond = $cond && $funding['EXECUTION_MANAGER_CODE'] === $execution_manager_code &&
                    $funding['OSD_PROGRAM_ELEMENT_CODE'] === $osd_pe_code;
                }

                if ($cond) {
                    $fy_values = $funding[$resource_or_delta];
                }
            }

            if ( $fy_values ) {
                $http_status = 200;
                foreach($fy_values as $key => $value) {
                    if (is_int($key)) {
                        $response['FY' . substr(strval($key), -2)] = (float)$value;
                    }

                }
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response]));
        
    }

    private function convert_coa_column_values($result, $column_name, $filter = null) {
        $result = array_filter(array_values(
            array_unique(array_column($result, $column_name))
        ), function ($v) {
            return $v !== null;
        });

        if ($filter !== null) {
            $values = array_column($filter, $column_name);
            $intersect_result = array_filter($result, function($item) use ($values) {
                return in_array($item, $values);
            });
            $result = empty($intersect_result) ? $result : array_values($intersect_result);
        }
        return $result;
    }

    private function get_optimized_data(int $id, string $type_of_coa) {
        $optimized_data = $this->SOCOM_COA_model->get_optimized_data($id);

        if (isset($optimized_data['CALC_BUDGET_VALUES'])) {
            $selected_programs = json_decode(
                $optimized_data['CALC_BUDGET_VALUES'] ?? ['selected_programs' => []], true
            )['selected_programs'];

            usort($selected_programs, function($a, $b) {
                return strcmp($a['program_code'], $b['program_code']);
            });
            
            $formatted_selected_programs = [];
            foreach($selected_programs as $selected_program) {
                $formatted_selected_programs [] = [
                    'ID' => $selected_program['program_id'],
                    'PROGRAM_CODE' => $selected_program['program_code'],
                    'ASSESSMENT_AREA_CODE' => $selected_program['assessment_area_code'],
                    'EOC_CODE' => $selected_program['eoc_code'],
                    'OSD_PE' => $selected_program['osd_pe'],
                    'POM_SPONSOR_CODE' => $selected_program['pom_sponsor'],
                    'RESOURCE_CATEGORY_CODE' => $selected_program['resource_category_code'],
                    'CAPABILITY_SPONSOR_CODE' => $selected_program['capability_sponsor'],
                    'PROGRAM_GROUP' => $selected_program['program_group'],
                    'RESOURCE_K' => $selected_program['resource_k'],
                    'PROGRAM_NAME' => $selected_program['program_name'],
                    'OSD_PROGRAM_ELEMENT_CODE' => $selected_program['osd_pe'],
                    'EXECUTION_MANAGER_CODE' => $selected_program['execution_manager_code']
                ];
            }
        }
        else {
            $override_table_session = json_decode(
                $optimized_data['OVERRIDE_TABLE_SESSION'] ?? [], true
            );

            $fiscal_years = json_decode(
                $optimized_data['YEAR_LIST'] ?? [], true
            );

            $coa_output = $override_table_session['coa_output'];
            $programIDs = $override_table_session['ProgramIDs'];

            $program_data = $this->SOCOM_Program_model->get_program_data(
                $programIDs, ['ID', 'PROGRAM_GROUP', 'PROGRAM_NAME']
            );

            $program_id_map = [];

            foreach($program_data as $program) {
                $program_id_map[$program['ID']] = $program;
            }

            $formatted_selected_programs = [];
            foreach($coa_output as $row) {
                if (!str_contains($row['RESOURCE CATEGORY'], 'Committed Grand Total $K')) {
                    $program_id = covertToProgramId(
                        $type_of_coa, [
                            'program_code' => $row['Program'] ?? '',
                            'pom_sponsor' => $row['POM SPONSOR'] ?? '',
                            'cap_sponsor' => $row['CAP SPONSOR'] ?? '',
                            'ass_area_code' => $row['ASSESSMENT AREA'] ?? '',
                            'execution_manager' => $row['EXECUTION MANAGER'] ?? '',
                            'resource_category' => $row['RESOURCE CATEGORY'] ?? '',
                            'eoc_code' => $row['EOC'] ?? '',
                            'osd_pe_code' => $row['OSD PE'] ?? '',
                            'event_name' => $row['EVENT NAME'] ?? ''
                        ]
                    );

                    $resource_k = [];
                    $program_group = $program_id_map[$program_id]['PROGRAM_GROUP'] ?? '';
                    $program_name = $program_id_map[$program_id]['PROGRAM_NAME'] ?? '';
                    foreach($fiscal_years as $fy) {
                        $resource_k[$fy] = $row[$fy];
                    }

                    $formatted_selected_programs [] = [
                        'ID' => $program_id,
                        'PROGRAM_CODE' => $row['Program'],
                        'ASSESSMENT_AREA_CODE' => $row['ASSESSMENT AREA'],
                        'EOC_CODE' => $row['EOC'],
                        'OSD_PE' => $row['OSD PE'],
                        'POM_SPONSOR_CODE' => $row['POM SPONSOR'],
                        'RESOURCE_CATEGORY_CODE' => $row['RESOURCE CATEGORY'],
                        'CAPABILITY_SPONSOR_CODE' => $row['CAP SPONSOR'],
                        'PROGRAM_GROUP' => $program_group,
                        'RESOURCE_K' => $resource_k,
                        'PROGRAM_NAME' => $program_name,
                        'OSD_PROGRAM_ELEMENT_CODE' => $row['OSD PE'],
                        'EXECUTION_MANAGER_CODE' => $row['EXECUTION MANAGER']
                    ];
                }
            }
        }
        return $formatted_selected_programs;
    }

    public function get_output_table($scenario_id) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $ids = json_decode(performIssetTenaryOp(isset($post_data['ids']), $post_data,  'ids', '[]'));
        $budget = performIssetTenaryOp(isset($post_data['budget']), $post_data,  'budget', []);
        $coa_table_id = performIssetTenaryOp(isset($post_data['coa_table_id']), $post_data,  'coa_table_id', 0);
        $mdata = $this->SOCOM_COA_model->get_metadata($scenario_id);
        $type_of_coa = $mdata['TYPE_OF_COA'];
        $fiscal_years = json_decode($mdata['YEAR_LIST'], true);

        $is_iss_extract_coa = $type_of_coa === 'ISS_EXTRACT';

        $scores = $this->get_saved_coa_score($scenario_id,  $ids, $type_of_coa);

        if (isset($scores['detail'])) {
            log_message('error', 'When calling method get_saved_coa_score the score returned from API was not possible. Using SAVED COA Score values from JSON.');
            log_message('error', sprintf('API Score (Endpoint: optimizer/weighted_scores) Error Message: %s', $scores['detail']));
            $scores_original_optimization = $this->SOCOM_COA_model->get_saved_coa_scores($scenario_id);
        } else {
            $scores_original_optimization = [];
        }
        

        $optimizer_input = $this->SOCOM_COA_model->get_saved_coa_optimizer_input($scenario_id);
        if ($optimizer_input) {
            $active_weighted_storm_view = performGeneralTenaryOp($optimizer_input['storm_flag'],'storm' ,'weighted');
            $active_weighted_scored_view = $this->weighted_score_option[$optimizer_input['option']];
        }
        else {
            $weighted_score_option = $this->SOCOM_COA_model->get_manual_override_weighted_score_option($scenario_id);
            $active_weighted_storm_view = performGeneralTenaryOp($weighted_score_option === 'storm', $weighted_score_option, 'weighted');
            $active_weighted_scored_view = performGeneralTenaryOp($weighted_score_option === 'storm', 'both', $weighted_score_option);
        }

        $page_data = array();
        $newProgramData = array();
        
        if ($is_iss_extract_coa) {
            $output_info = $this->SOCOM_COA_model->fetchOutputInfoIssExtract($ids);
        } else {
            $output_info = $this->get_optimized_data($scenario_id, $type_of_coa);
        }

        $headers_rows_obj = $this->setOutputHeadersRowsObj($is_iss_extract_coa);
        $added_headers = [];
        if (isset($this->weighted_storm_headers[$active_weighted_storm_view][$active_weighted_scored_view])) {
            $added_headers = $this->weighted_storm_headers[$active_weighted_storm_view][$active_weighted_scored_view];
        } else {
            $added_headers = $this->weighted_storm_headers[$active_weighted_storm_view];
        }

        // find the score column to be hidden 
        $hidden_score_column = array_diff(
            array_keys($this->weighted_score_header_keys),
            $added_headers
        );
        $year_array = $fiscal_years;
        $saved_coa = $this->SOCOM_COA_model->get_saved_coa_values($scenario_id);

        if (empty($saved_coa)) {
            $saved_coa = $this->get_manual_override_coa_values($scenario_id, $type_of_coa);
        }
        $original_program_ids = array_column($saved_coa['selected_programs'], 'program_id');
        $output_table = $this->format_output_table(
            $output_info,
            $budget,
            $year_array,
            $scores,
            $headers_rows_obj,
            $saved_coa,
            $scores_original_optimization
        );
        $output_data = $output_table['data'];        
        $table_header_array = $output_table['table_headers'];
        $data_header_array = $output_table['data_headers'];
        $field_array = $output_table['field'];

        $session_data = $this->SOCOM_COA_model->get_manual_override_data($scenario_id);
        $session_data = !empty($session_data) ? $session_data[0] : [];
        $page_data['table_data'] = $output_data;
        $page_data['table_headers'] = $table_header_array;
        $page_data['data_headers'] = $data_header_array;
        $page_data['datatable_field'] = $field_array;

        if ($is_iss_extract_coa) {
            $budget_uncommitted_table_data = $output_table['budget_uncommitted_table'];
        } else {
            if ($this->SOCOM_COA_model->is_manual_override($scenario_id)) { 
                $program_ids = json_decode($session_data['OVERRIDE_TABLE_SESSION'], true)['ProgramIDs'];
            }
            else {
                $program_ids = $this->SOCOM_COA_model->get_program_ids($scenario_id, $type_of_coa);
            }
            $original_rows = json_decode($this->SOCOM_COA_model->fetchOutputInfo($program_ids), true);
            $committed_grand_total = $output_table['committed_grand_total'];
            $budget_uncommitted_table_data = $this->format_budget_uncommitted_rows_rc(
                $original_rows, $committed_grand_total, $budget, $year_array
            );
        }
        $page_data['budget_uncommitted_table_data'] = $budget_uncommitted_table_data['data'];
        $page_data['budget_uncommitted_table_headers'] = $budget_uncommitted_table_data['headers'];

        if (!empty($session_data) && $session_data['OVERRIDE_TABLE_SESSION'] != null) {
            $page_data['budget_uncommitted_override_table_data'] = json_decode($session_data['OVERRIDE_TABLE_SESSION'], true)['budget_uncommitted'];
        }
        else {
            $page_data['budget_uncommitted_override_table_data'] = $page_data['budget_uncommitted_table_data'];
        }
        $page_data['override_table_metadata'] = $this->formatOverrideTableMetadata($session_data);
        $page_data['year_array'] = $year_array;

        $delete_button_elememt = '<button class="bx--btn bx--btn--ghost bx--btn--sm coa-delete-row-btn">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>';
        $gear_button_elememt = '<button class="bx--btn bx--btn--ghost bx--btn--sm coa-gear-row-btn">
            <i class="fa fa-cog" aria-hidden="true"></i>
        </button>';
        $page_data['override_data'] = [];
        $insert_button_element = '<button scenario-id="1" data-prog="1" data-eoc="2"
            id="coa-insert-row-btn"
            class="bx--btn bx--btn--secondary bx--btn--sm coa-insert-row-btn"
            data-modal-target="#coa-table-insert">
            <i class="fa fa-plus" aria-hidden="true"></i>
        </button>';
        $page_data['datatable_headers_override'] = array_merge(
            [
            ['data' => 'delete', 'title' => $insert_button_element],
            ['data' => 'gear', 'title' => '']
        ],
            $data_header_array
        );

        if (!empty($session_data) && $session_data['OVERRIDE_TABLE_SESSION'] != null) {
            $page_data['override_data'] = json_decode($session_data['OVERRIDE_TABLE_SESSION'], true)['coa_output'];
            $newProgramIds = [];
            foreach($page_data['override_data'] as &$row) {
                $row['delete'] = $row['RESOURCE CATEGORY'] !== 'Committed Grand Total $K' ? $delete_button_elememt : '';
                $row['gear'] = $row['RESOURCE CATEGORY'] !== 'Committed Grand Total $K' ? $gear_button_elememt : '';
                $newProgramIds[] = $this->addMissingWeightedScoreData(
                    $type_of_coa, $row, $scores, $page_data['datatable_headers_override'], $scores_original_optimization, $original_program_ids
                );
            }

            $newProgramIds = array_values(array_filter($newProgramIds, function($val) { return $val !== false; }));

            if (!empty($newProgramIds)) {
                if ($is_iss_extract_coa) {
                    $newProgramData = $this->SOCOM_COA_model->fetchOutputInfoIssExtract(array_column($newProgramIds, 'ProgramId'));
                } else {
                    $newProgramData = json_decode($this->SOCOM_COA_model->fetchOutputInfo(array_column($newProgramIds, 'ProgramId')), true);
                }
            }
        }
        else {
            foreach($output_data as $row) {
                $first_column = '';
                $second_column = '';
                if ($row['RESOURCE CATEGORY'] !== 'Committed Grand Total $K') {
                    $first_column = $delete_button_elememt;
                    $second_column = $gear_button_elememt;
                }
                $override_row = [
                    'delete' => $first_column,
                    'gear' => $second_column
                ] + $row;
                $page_data['override_data'][] = $override_row;
            }
            $newProgramIds = $this->SOCOM_COA_model->get_program_ids($scenario_id, $type_of_coa);
            if (!empty($newProgramIds)) {
                if ($is_iss_extract_coa) {
                    $originalProgramData = $this->SOCOM_COA_model->fetchOutputInfoIssExtract(array_values($newProgramIds));
                } else {
                    $originalProgramData = json_decode($this->SOCOM_COA_model->fetchOutputInfo(array_values($newProgramIds)), true);
                }
            }
        }
        $page_data['override_form'] = (!empty($session_data) && $session_data['OVERRIDE_FORM_SESSION'] != null) ?
                                        json_decode($session_data['OVERRIDE_FORM_SESSION'], true) : [];
        $page_data['state'] = $this->SOCOM_COA_model->get_manual_override_status($scenario_id)['STATE'];
        $page_data['user_id'] = $this->session->userdata('logged_in')['id'];
        $page_data['scenario_id'] = $scenario_id;
        $page_data['current_user'] = $this->SOCOM_Users_model->get_user_info($page_data['user_id'])['name'];
        $page_data['coa_table_id'] = $coa_table_id;
        $page_data['hidden_score_column'] = array_values($hidden_score_column);
        $page_data['type_of_coa'] = $type_of_coa;
        $page_data['new_program_ids'] = $newProgramIds ?? [];
        $page_data['new_program_data'] = empty($newProgramData) ? $output_info : $newProgramData;
        $page_data['original_program_data'] = empty($originalProgramData) ? $page_data['new_program_data'] : $originalProgramData;

        $this->output
                ->set_status_header(200)
                ->set_content_type('text/html')
                ->set_output($this->load->view('SOCOM/optimizer/coa_output_table_view', $page_data, true))
                ->_display();
        exit;

    }
    private function format_output_table(
        $output_info, $budget, $year_array, $scores, $headers_rows_obj, $saved_coa, $scores_original_optimization
    ) {
        $result = [];
        $table_headers = $headers_rows_obj['table_headers'];
        $data_headers = $headers_rows_obj['data_headers'];
        $field = [];
        $grand_row = $headers_rows_obj['grand_row'];
        $selected_program_codes = [];
        $has_column_updated = false;
        $grand_fydp = 0;
        $count = 0;
        foreach($output_info as $row => $program_value) {
            $row = $headers_rows_obj['row'];
            $program_name = $program_value['ID'];
            $row['EOC'] = $program_value['EOC_CODE'];
            $row['EXECUTION MANAGER'] = $program_value['EXECUTION_MANAGER_CODE'] ?? '';

            foreach($this->output_header_keys as $header_key => $output_key) {
                if (isset($program_value[$output_key])) {
                    $row[$header_key] = $program_value[$output_key];
                }
            }
            $fydp = 0;
            foreach($year_array as $year) {
                $value = ceil($this->checkForPartialFunding($saved_coa, isset($program_value['RESOURCE_K']) ? $program_value['RESOURCE_K'] : $program_value['DELTA_AMT'], $year, $program_name));
                $key_stringified = strval($year);
                $row[$key_stringified] = $value;
                $fydp += $value ?? 0;
                if (!$has_column_updated) {
                    $data_headers[] = ['data' => $key_stringified];
                    $field[] = ['name' => $key_stringified];
                    $table_headers[] = $key_stringified;
                    if (!isset($grand_row[$key_stringified])) {
                        $grand_row[$key_stringified] = 0;
                    }
                }
                    $grand_row[$key_stringified] += $value;
                    $grand_fydp += $value;
            }


            $row['RESOURCE CATEGORY'] = $program_value['RESOURCE_CATEGORY_CODE'] ?? '';

            if (in_array('Event Name', $table_headers)) {
                $row["Event Name"] = $program_value["EVENT_NAME"] ?? '';
            }

            if (in_array('OSD PE', $table_headers)) {
                $row["OSD PE"] = $program_value["OSD_PE"] ?? ($program_value["OSD_PROGRAM_ELEMENT_CODE"] ?? '');
            }
            
            if (!$has_column_updated) {
                $data_headers[] = ['data' => 'FYDP'];
                $field[] = ['name' => 'FYDP'];
                $data_headers[] = ['data' => 'DT_RowId'];
                $field[] = ['name' => 'DT_RowId'];
                $table_headers[] = 'FYDP';
                $table_headers[] = 'DT_RowId';
            }
            $row['FYDP'] = $fydp;
            $has_column_updated = true;
            $rowId = $program_value['ID'];
            $row['DT_RowId'] = performIssetTenaryOp(
                isset($program_value['DT_RowId']),
                $program_value,
                'DT_RowId',
                $rowId
            );
            $count++;
            foreach($this->weighted_score_header_keys as $weighted_score_header_key => $weighted_score_output_key) {                
                if (!isset($scores[$program_name][$weighted_score_output_key]) && isset($scores['detail'])) {
                    $row[$weighted_score_header_key] = $scores_original_optimization
                        [$weighted_score_output_key][$program_value['ID']] ?? 0;
                } else {
                    $row[$weighted_score_header_key] = $scores[$program_name][$weighted_score_output_key] ?? '';
                }
            }
            if ($fydp != 0) {
                $result[] = $row;
            }
        }
            
        
        $grand_row['FYDP'] = $grand_fydp;
        $grand_row['DT_RowId'] = performIssetMultiKeyTenaryOp(
            [$count, 'DT_RowId'],
            $program_value,
            null
        );
        $result[] = $grand_row;
        $budget_uncommitted_table = $this->format_budget_uncommitted_rows($grand_row, $budget, $year_array);
        return [
            'data' => $result,
            'data_headers' => $data_headers,
            'table_headers' => $table_headers,
            'field' => $field,
            'year_array' => $year_array,
            'budget_uncommitted_table' => $budget_uncommitted_table,
            'committed_grand_total' =>  $grand_row
        ];
    }

    private function get_manual_override_coa_values($id, $type_of_coa) {
        $optimized_data = $this->SOCOM_COA_model->get_optimized_data($id);

        $override_table_session = json_decode(
            $optimized_data['OVERRIDE_TABLE_SESSION'] ?? [], true
        );

        $fiscal_years = json_decode(
            $optimized_data['YEAR_LIST'] ?? [], true
        );

        $programIDs = $override_table_session['ProgramIDs'];

        $program_data = $this->SOCOM_Program_model->get_program_data(
            $programIDs, ['ID', 'PROGRAM_GROUP', 'PROGRAM_NAME']
        );

        $program_id_map = [];
        foreach($program_data as $program) {
            $program_id_map[$program['ID']] = $program;
        }

        $saved_coa = [
            'selected_programs' => [],
            'resource_k' => []
        ];
        foreach($fiscal_years as $fy) {
            $saved_coa['resource_k'][$fy] = [];
        }
        foreach($override_table_session['coa_output'] as $row) {
            if (!str_contains($row['RESOURCE CATEGORY'], 'Committed Grand Total $K')) {
                $program_id = covertToProgramId(
                    $type_of_coa, [
                        'program_code' => $row['Program'] ?? '',
                        'pom_sponsor' => $row['POM SPONSOR'] ?? '',
                        'cap_sponsor' => $row['CAP SPONSOR'] ?? '',
                        'ass_area_code' => $row['ASSESSMENT AREA'] ?? '',
                        'execution_manager' => $row['EXECUTION MANAGER'] ?? '',
                        'resource_category' => $row['RESOURCE CATEGORY'] ?? '',
                        'eoc_code' => $row['EOC'] ?? '',
                        'osd_pe_code' => $row['OSD PE'] ?? '',
                        'event_name' => $row['EVENT NAME'] ?? ''
                    ]
                );

                $resource_k = [];
                $program_group = $program_id_map[$program_id]['PROGRAM_GROUP'] ?? '';
                foreach($fiscal_years as $fy) {
                    $resource_k[$fy] = $row[$fy];
                    $saved_coa['resource_k'][$fy][$program_id] = $row[$fy];
                }

                $saved_coa['selected_programs'][] = [
                    'program_id' => $program_id,
                    'resource_k' => $resource_k,
                    'pom_sponsor' => $row['POM SPONSOR'],
                    'program_group' => $program_group,
                    'total_storm_score' => $row['StoRM Score'],
                    'capability_sponsor' => $row['CAP SPONSOR'],
                    'weighted_pom_score' => $row['POM Score'],
                    'weighted_guidance_score' => $row['Guidance Score']
                ];
            }
        }
        return  $saved_coa;
    }

    private function get_selected_program_ids($table_data, $type_of_coa) {
        $program_ids = [];
        foreach( $table_data as $row) {
            if (!str_contains($row['RESOURCE CATEGORY'], 'Committed Grand Total $K')) {
                $program_ids [] =  covertToProgramId($type_of_coa, [
                    'program_code' => $row['Program'] ?? '',
                    'cap_sponsor' => $row['CAP SPONSOR'] ?? '',
                    'pom_sponsor' =>$row['POM SPONSOR'] ?? '',
                    'ass_area_code' => $row['ASSESSMENT AREA'] ?? '',
                    'execution_manager' => $row['EXECUTION MANAGER'] ?? '',
                    'resource_category' => $row['RESOURCE CATEGORY'] ?? '',
                    'eoc_code' =>$row['EOC'] ?? '',
                    'osd_pe_code' => $row['OSD PE'] ?? '',
                    'event_name' => $row['EVENT NAME'] ?? ''
                ]);
            }
        }
        return $program_ids;
    }
    
    public function manual_override_save($scenario_id){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $overridetable = isset($post_data['override_table']) ? $post_data['override_table'] : '';
            $override_table_metadata =
                isset($post_data['override_table_metadata']) ? $post_data['override_table_metadata'] : '';
            
            // Get unselected program
            $program_codes = performIssetTenaryOp(
                isset($post_data['program_codes']),
                $post_data,
                'program_codes',
                []
            );

            $eoc_codes = performIssetTenaryOp(
                isset($post_data['eoc_codes']),
                $post_data,
                'eoc_codes',
                []
            );
            $mdata = $this->SOCOM_COA_model->get_metadata($scenario_id);

            $program_ids = $this->SOCOM_COA_model->get_program_ids($scenario_id, $mdata['TYPE_OF_COA']);

            $selected_program_ids = $this->get_selected_program_ids(
                json_decode($overridetable, true)['coa_output'], $mdata['TYPE_OF_COA']
            );
            $program_ids  = array_values(array_unique(array_merge($program_ids, $selected_program_ids)));

            $overridetable = json_decode($overridetable, true);
            $overridetable['ProgramIDs'] = $program_ids;
            $overridetable['unselected_program_ids'] = $this->format_manual_override_unselected_program_ids(
                $mdata['TYPE_OF_COA'],
                $selected_program_ids,
                $program_ids
            );
        
            $result = true;
            if ($overridetable != '') {
                $result = $result && $this->SOCOM_COA_model->manual_override_save(
                    $scenario_id, 'OVERRIDE_TABLE_SESSION', json_encode($overridetable, true)
                );
            }

            if ($override_table_metadata != '') {
                $result = $result && $this->SOCOM_COA_model->manual_override_save(
                    $scenario_id, 'OVERRIDE_TABLE_METADATA', $override_table_metadata
                );
            }

            echo json_encode($result);
        }
    }

    private function format_manual_override_unselected_program_ids($type_of_coa, $selected_program_ids, $program_ids) {
        return array_values(array_diff($program_ids, $selected_program_ids));
    }

    private function format_manual_override_program_id($type_of_coa,$coa_output, $unselected_program) {
        $program_ids = array_column($unselected_program, 'PROGRAM_ID');

        foreach($coa_output as $row) {
            if (!str_contains($row['RESOURCE CATEGORY'],'Grand Total')) {
                $program_ids[] = covertToProgramId($type_of_coa, [
                    'program_code' => $row['Program'] ?? '',
                    'cap_sponsor' => $row['CAP SPONSOR'] ?? '',
                    'pom_sponsor' =>$row['POM SPONSOR'] ?? '',
                    'ass_area_code' => $row['ASSESSMENT AREA'] ?? '',
                    'execution_manager' => $row['EXECUTION MANAGER'] ?? '',
                    'resource_category' => $row['RESOURCE CATEGORY'] ?? '',
                    'eoc_code' =>$row['EOC'] ?? '',
                    'osd_pe_code' => $row['OSD PE'] ?? '',
                    'event_name' => $row['EVENT NAME'] ?? ''
                ]);
            }
        }
        return $program_ids;
    }
    
    public function save_override_form($scenario_id){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $overrideForm = $post_data['override_form'];

            $result = $this->SOCOM_COA_model->save_override_form(
                $scenario_id, $overrideForm
            );
            
            echo json_encode($result);
        }
    }

    public function change_scenario_status($scenario_id){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $status_value = $post_data['status_value'];

            $result = $this->SOCOM_COA_model->change_scenario_status(
                $scenario_id, $status_value
            );

            echo json_encode($result);
        }
    }

    public function get_display_banner(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            // $result = $this->model->get_display_banner();
            $result = 'placeholder';

            echo json_encode(array('text'=>$result));
        }
    }
    
    private function format_budget_uncommitted_rows($grand_row, $budget, $year_array) {
        $budget_row = [
            'TYPE' => 'Proposed Budget $K'
        ];
        $uncommitted_row = [
            'TYPE' => 'Uncommitted $K'
        ];
        $budget_fydp = 0;
        $uncommitted_fydp = 0;
        $headers = [
            ['data' => 'TYPE', 'title' => 'TYPE']
        ];
        foreach($year_array as $index => $year) {
            $budget_fy = $budget[$index];
            $budget_row[$year] = $budget_fy;
            $budget_fydp += $budget_fy;
            $grand_fy = $grand_row[$year];
            $uncommitted_fy = $budget_fy - $grand_fy;
            $uncommitted_row[$year] = $uncommitted_fy;
            $uncommitted_fydp += $uncommitted_fy;
            $headers[] = [
                'data' => strval($year),
                'title' => 'FY' . substr(strval($year), -2)
            ];
        }
        $headers[] = ['data' => 'FYDP', 'title' => 'FYDP'];
        $budget_row['FYDP'] = $budget_fydp;
        $uncommitted_row['FYDP'] = $uncommitted_fydp;
        return [
            'data' => [
                $budget_row,
                $uncommitted_row
            ],
            'headers' => $headers
        ];
    }

    private function format_budget_uncommitted_rows_rc($original_rows, $committed_grand_total, $budget, $year_array) {
        $grand_row = [];
        $budget_row = [
            'TYPE' => 'Proposed Budget $K'
        ];
        $uncommitted_row = [
            'TYPE' => 'Uncommitted $K'
        ];
        $budget_fydp = 0;
        $uncommitted_fydp = 0;
        $headers = [
            ['data' => 'TYPE', 'title' => 'TYPE']
        ];

        //get grand row
        foreach($year_array as $year) {
            $grand_row[$year] = 0;
        }

        foreach($original_rows as $row) {
            foreach($year_array as $year) {
                $grand_row[$year] += $row['RESOURCE_K'][$year] ?? 0;
            }
        }

        foreach($year_array as $index => $year) {
            // proposed budget = origial data - proposed cuts
            $grand_fy = $grand_row[$year];
            $budget_fy = $budget[$index];
            $budget_row[$year] = $grand_fy - $budget_fy;
            $budget_fydp += $budget_row[$year];

            // uncommitted $K = proposed budget - committed $K
            $uncommitted_fy = $budget_row[$year] - $committed_grand_total[$year];
            $uncommitted_row[$year] = $uncommitted_fy;
            $uncommitted_fydp += $uncommitted_fy;
            $headers[] = [
                'data' => strval($year),
                'title' => 'FY' . substr(strval($year), -2)
            ];
        }
        $headers[] = ['data' => 'FYDP', 'title' => 'FYDP'];
        $budget_row['FYDP'] = $budget_fydp;
        $uncommitted_row['FYDP'] = $uncommitted_fydp;
        return [
            'data' => [
                $budget_row,
                $uncommitted_row
            ],
            'headers' => $headers
        ];
    }
    
    private function formatOverrideTableMetadata($session_data) {
        $result = [];
        if (!empty($session_data) && $session_data['OVERRIDE_TABLE_METADATA'] != null) {
            $result = json_decode($session_data['OVERRIDE_TABLE_METADATA'], true);
            $result['coa_output'] = (object)$result['coa_output'];
            $result['budget_uncommitted'] = (object)$result['budget_uncommitted'];
        }
        return $result;
    }
    
    public function get_detailed_summary($scenario_id, $table_id) {

        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        $title = '';
        $is_iss_extract = false;
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $title = $post_data['title'];
            $type_of_coa = $post_data['type_of_coa'];
            $is_iss_extract = $type_of_coa === 'ISS_EXTRACT';
        }

        $this->load->view('SOCOM/optimizer/coa_detailed_summary_tab_view',[
            'scenario_id' => $scenario_id,
            'table_id' => $table_id,
            'title' => $title,
            'is_iss_extract' => $is_iss_extract,
            'type' => 'summary',
            'detailed_summary_headers' =>  $this->detailed_summary_headers
        ]);
    }

    public function get_detailed_comparison() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        
        $titles = [];
        $saved_coa_ids = [];
        $is_iss_extract = false;
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $titles = $post_data['titles'];
            $saved_coa_ids = $post_data['saved_coa_ids'];
            $type_of_coas = $post_data['type_of_coas'];
            $is_iss_extract = array_reduce($type_of_coas, function($carry, $item) {
                return $carry && $item === 'ISS_EXTRACT';
            }, true);
        }

        $this->load->view('SOCOM/optimizer/coa_detailed_summary_tab_view',[
            'scenario_id' => 0,
            'table_id' => 0,
            'title' => '<strong>' . implode('</strong> and <strong>',  $titles) . '</strong>',
            'type' => 'comparison',
            'saved_coa_ids' => $saved_coa_ids,
            'is_iss_extract' => $is_iss_extract,
            'titles' => $titles,
            'detailed_summary_headers' => $this->detailed_summary_headers
        ]);
    }

    private function format_detailed_summary_issue_analysis_output_data($saved_coa_id) {
        $result = $this->SOCOM_COA_model->get_issue_analysis_data([intval($saved_coa_id)]);

        return [
            'event' => $this->get_detailed_summary_issue_analysis_event_data($result[$saved_coa_id]),
            'program_eoc' => $this->get_detailed_summary_issue_analysis_program_eoc_data(
                $result[$saved_coa_id], $result['all_events']
            )
        ];
    }

    private function get_detailed_summary_issue_analysis_event_data($data) {
        $event_data = $data['event'];

        $fully_funded = $event_data['fully_funded_issues'];
        $partially_funded = $event_data['partially_funded_issues'];
        $non_funded = $event_data['non_funded_issues'];

        $fully_funded_data = array_map(function($event) {
            $proposed_changes_data = [
                'include' => $event['include'],
                'exclude' => $event['exclude'],
                'fiscal_years' => $this->detailed_summary_headers['issue-analysis']['fiscal_years']
            ];

            return [
                ...$event,
                'PROPOSED_CHANGES' => $this->get_proposed_changes_button($event['EVENT_NAME'], $proposed_changes_data),
            ];
        }, $fully_funded);

        $partially_funded_data = array_map(function($event) {
            $proposed_changes_data = [
                'include' => $event['include'],
                'exclude' => $event['exclude'],
                'fiscal_years' => $this->detailed_summary_headers['issue-analysis']['fiscal_years']
            ];

            return [
                ...$event,
                'PROPOSED_CHANGES' => $this->get_proposed_changes_button($event['EVENT_NAME'], $proposed_changes_data),
            ];
        }, $partially_funded);

        $non_funded_data = array_map(function($event) {
            $proposed_changes_data = [
                'include' => $event['include'],
                'exclude' => $event['exclude'],
                'fiscal_years' => $this->detailed_summary_headers['issue-analysis']['fiscal_years']
            ];

            return [
                ...$event,
                'PROPOSED_CHANGES' => $this->get_proposed_changes_button($event['EVENT_NAME'], $proposed_changes_data),
            ];
        }, $non_funded);

        return [
            'data' => [
                'fully_funded' => $fully_funded_data,
                'partially_funded' => $partially_funded_data,
                'non_funded' => $non_funded_data,
            ],
            'headers' => $this->detailed_summary_headers['issue-analysis']['event']['summary'],
            'fiscal_years' => $this->detailed_summary_headers['issue-analysis']['fiscal_years']
        ];
    }

    private function get_detailed_summary_issue_analysis_program_eoc_data($data, $all_events) {
        $detailed_event_summary = array_merge(
            $data['event']['fully_funded_issues'],
            $data['event']['partially_funded_issues'],
            $data['event']['non_funded_issues']
        );
       
        $event_summary = $this->SOCOM_COA_model->get_event_summary_data($all_events, 'issue');
        $row_ids = [];

        $event_summary_group_by_row_id = [];
        foreach($all_events as $event) {
            foreach( $event_summary[$event] as $event_info) {
                $event_summary_group_by_row_id[$event_info["ROW_ID"]] = $event_info;
                $row_ids[] = $event_info["ROW_ID"];
            }
        }

        $detailed_event_summary_group_by_row_id = [];
        foreach ($detailed_event_summary as $value) {
            foreach($value['include'] as $include_event) {
                $detailed_event_summary_group_by_row_id[$include_event['ROW_ID']] = $include_event;
            }
        }

        $proposed_funding_prefix = 'PF_';
        $requested_funding_prefix = 'RF_';

        $result = [];
        foreach($row_ids as $row_id) {
            $event_summary_row = $event_summary_group_by_row_id[$row_id];

            //requested funding
            $requested_funding = $event_summary_row['FISCAL_YEAR'];
            $requested_funding_data = [];
            $requested_funding_fydp_delta = 0;
            foreach($requested_funding as $year => $value) {
                $requested_funding_value =$value;
                $requested_funding_data[$requested_funding_prefix . "FY" . $year] = $requested_funding_value;
                $requested_funding_fydp_delta += $requested_funding_value;
            }
            $requested_funding_data[$requested_funding_prefix . "FYDP_DELTA"] = $requested_funding_fydp_delta;

            // proposed funding
            $proposed_funding = [];
            if (isset($detailed_event_summary_group_by_row_id[$row_id])) {
                $proposed_funding = $detailed_event_summary_group_by_row_id[$row_id]['FISCAL_YEAR'];
            }
            else {
                foreach($event_summary_row['FISCAL_YEAR'] as $year => $value) {
                    $proposed_funding[$year] = 0;
                }
                $proposed_funding['FYDP'] = 0;
            }

            // Set 0 to any missing years
            $fiscal_years = $this->detailed_summary_headers['issue-analysis']['fiscal_years'];
            foreach ($fiscal_years as $year) {
                if (!isset($proposed_funding[$year])) {
                    $proposed_funding[$year] = 0;
                }
            }

            $proposed_funding_data = [];
            foreach($proposed_funding as $year => $value) {
                if ($year == 'FYDP') {
                    $proposed_funding_data [$proposed_funding_prefix . "FYDP_DELTA"] = $value;
                }
                else {
                    $proposed_funding_data [$proposed_funding_prefix . "FY" . $year] = $value;
                }
            }

            $result[] = [
                'PROGRAM_CODE' => $event_summary_row['PROGRAM_CODE'],
                'EOC_CODE' => $event_summary_row['EOC_CODE'],
                'CAPABILITY_SPONSOR' => $event_summary_row['CAPABILITY_SPONSOR_CODE'],
                'ASSESSMENT_AREA' => $event_summary_row['ASSESSMENT_AREA_CODE'],
                "RESOURCE_CATEGORY" => $event_summary_row['RESOURCE_CATEGORY_CODE'],
                'OSD_PE' => $event_summary_row['OSD_PROGRAM_ELEMENT_CODE'],
                'EVENT_NAME' =>  $event_summary_row['EVENT_NAME'],
                ...$requested_funding_data,
                ...$proposed_funding_data
            ];
        }

        return [
            'data' =>  [
                'eoc_information' => $result
            ],
            'headers' => $this->detailed_summary_headers['issue-analysis']['program_eoc']
        ];
    }

    private function format_eoc_code_output_data($data, $saved_coa_id, $filters=[]) {
  
        $table_data = [];
        $graph_data = [];
        $grouped_resource_k = [];
        $fy = [];
        $cap_sponsor = [];
        $cap_sponsor_filter = isset($filters['cap_sponsor']) ? $filters['cap_sponsor'] : [];

        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
        if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {
            $resource_or_delta = 'DELTA_AMT';
        } else {
            $resource_or_delta = 'RESOURCE_K';
        }
        $year_list = is_string($mdata['YEAR_LIST']) ? json_decode($mdata['YEAR_LIST'], true) : $mdata['YEAR_LIST'];
        foreach($data as $program_info) {
            $fydp = 0;
            $resource_or_delta_value = [];
            foreach($year_list as $year) {
                $resource_or_delta_value[$year] = $program_info[$resource_or_delta][$year] ?? 0;
                $fydp += $resource_or_delta_value[$year];
            }

            if ((empty($cap_sponsor_filter) || in_array($program_info['CAPABILITY_SPONSOR_CODE'], $cap_sponsor_filter))
                && $fydp > 0) {
                $table_data [] = [
                    "PROGRAM" => $program_info['PROGRAM_CODE'],
                    "PROGRAM_GROUP"=> $program_info['PROGRAM_GROUP'],
                    "EOC" =>  $program_info['EOC_CODE'],
                    "CAP_SPONSOR" => $program_info['CAPABILITY_SPONSOR_CODE'],
                    "RESOURCE_CATEGORY" => $program_info['RESOURCE_CATEGORY_CODE'],
                    "OSD_PROGRAM_ELEMENT_CODE" => $program_info['OSD_PROGRAM_ELEMENT_CODE'] ?? $program_info['OSD_PE'],
                    $resource_or_delta => $resource_or_delta_value,
                    "FYDP" => $fydp
                ];

                if (!isset($grouped_resource_k[$program_info['RESOURCE_CATEGORY_CODE']])) {
                    foreach (array_keys($resource_or_delta_value) as $api_year) {
                        if (array_search($api_year, $year_list) === false) {
                            unset($resource_or_delta_value[$api_year]);
                        }
                    }
                    $grouped_resource_k[$program_info['RESOURCE_CATEGORY_CODE']] =  $resource_or_delta_value;

                }
                else {
                    foreach($year_list as $years) {
                        $grouped_resource_k[$program_info['RESOURCE_CATEGORY_CODE']][$years] += 
                            $resource_or_delta_value[$years];
                    }
                }
            }

            if (!in_array($program_info['CAPABILITY_SPONSOR_CODE'], $cap_sponsor)) {
                $cap_sponsor[] = $program_info['CAPABILITY_SPONSOR_CODE'];
            }
        }

        // Desired order based on colors array
        $desired_order = array_keys($this->eoc_code_chart_colors);

        // Reorder the original array
        $reordered_resource_k = [];
        foreach ($desired_order as $key) {
            if (isset($grouped_resource_k[$key])) {
                $reordered_resource_k[$key] = $grouped_resource_k[$key];
            }
        }
        // Merge the elements missing in the color map the reordered array 
        $reordered_resource_k = array_merge(
            $reordered_resource_k, array_diff($grouped_resource_k, $reordered_resource_k)
        );
  
        foreach($reordered_resource_k as $resource_category => $value) {
            $graph_data['series'][] = [
                'name' => $resource_category,
                'data' => array_values($value),
                'color' => $this->eoc_code_chart_colors[$resource_category] ?? $this->generateRandomHexColor()
            ];
        }
        $graph_data['categories'] = $year_list;
        sort($cap_sponsor);
        return [
            'table' => $table_data,
            'graph' =>  $graph_data,
            'filters' => [
                'fy' => $year_list,
                'cap_sponsor' => $cap_sponsor
            ]
        ];
    }

    private function generateRandomHexColor() {
        // Generate a random hex color
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    private function covertToPrograms($program_ids) {
        return array_unique(array_map(function($id) {
            return explode('_', $id)[0]; // Get the first part before the underscore
        }, $program_ids));
    }

    private function get_program_breakdown_button($type, $version, $table_id, $selected) {
        $option = $selected ? 'included' : 'excluded';
        return '<button class="bx--btn bx--btn--ghost" type="button"
             onclick="get_coa_program_breakdown('.$table_id.',`'.$type.'`,`'.$version.'`,`'. $option.'`)"
             data-modal-target="#coa-program-breakdown">
            <?xml version="1.0" encoding="utf-8"?>
            <svg version="1.1" id="icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                width="32px" height="32px" viewBox="0 0 32 32" style="enable-background:new 0 0 32 32;" xml:space="preserve">
            <style type="text/css">
                .st0{fill:none;}
            </style>
            <title>overflow-menu--horizontal</title>
            <circle cx="8" cy="16" r="2"/>
            <circle cx="16" cy="16" r="2"/>
            <circle cx="24" cy="16" r="2"/>
            <rect id="_Transparent_Rectangle_" class="st0" width="32" height="32"/>
            </svg>
        </button>';
    }

    private function get_proposed_changes_button($event_name, $data) {
        $json_data = json_encode($data);
        return '<button class="bx--btn bx--btn--ghost" type="button"
             onclick="get_proposed_changes(`'.$event_name.'`)"
             data-event-name="'.$event_name.'"
             data-proposed-changes=\''.$json_data.'\'
             data-modal-target="#coa-proposed-changes">
            <?xml version="1.0" encoding="utf-8"?>
            <svg version="1.1" id="icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                width="32px" height="32px" viewBox="0 0 32 32" style="enable-background:new 0 0 32 32;" xml:space="preserve">
            <style type="text/css">
                .st0{fill:none;}
            </style>
            <title>overflow-menu--horizontal</title>
            <circle cx="8" cy="16" r="2"/>
            <circle cx="16" cy="16" r="2"/>
            <circle cx="24" cy="16" r="2"/>
            <rect id="_Transparent_Rectangle_" class="st0" width="32" height="32"/>
            </svg>
        </button>';
    }

    private function get_event_name_button($event_name) {
        return '<a href="javascript:void(0);" class="bx--btn bx--btn--ghost"
            onclick="get_event_details(`'.$event_name.'`)"
            data-modal-target="#coa-event-details">
            '.$event_name.'
        </a>';
    }

    private function totalProgramValue($programs) {
        return array_sum(array_values($programs));
    }

    private function format_program_breakdown_data($programs) {
        $result = [];
        foreach($programs as $key => $value) {

            $program = explode('_', $key)[0];
            $cap_sponsor = explode('_', $key)[2];

            $result [] = [
                'PROGRAM' => $program,
                'CAP_SPONSOR' => $cap_sponsor,
                'RESOURCE_K' => round($value, 0)
            ];
        }
        return $result;
    }

    private function get_jca_alignment_filters($covered_ids, $noncovered_ids, $level_filters) {

        $lvl_1_options = [];
        $lvl_2_options = [];
        $lvl_3_options = [];

        $lv1_filter = isset($level_filters['lvl_1']) ? $level_filters['lvl_1'] : [];
        $lv2_filter = isset($level_filters['lvl_2']) ? $level_filters['lvl_2'] : [];

        $filtered_lvl_1_ids = array_merge($covered_ids['lvl_1'],  $noncovered_ids['lvl_1']);
        $filtered_lvl_2_ids = array_merge($covered_ids['lvl_2'],  $noncovered_ids['lvl_2']);
        if (isset($covered_ids['lvl_3']) && isset($noncovered_ids['lvl_3'])) {
            $filtered_lvl_3_ids = array_merge($covered_ids['lvl_3'],  $noncovered_ids['lvl_3']);
        }

        $lvl_1_options = array_map(function($value) {
            return explode('.', $value)[0];
        },  $filtered_lvl_1_ids);

        $lvl_2_options = [];
        $lvl_3_options = [];
        
        if (count($lv1_filter) == 1) {
            foreach ($filtered_lvl_2_ids as $filtered_lvl_2_id) {
                $parts = explode('.', $filtered_lvl_2_id);
                if ($parts[0] == $lv1_filter[0]) {
                    if (isset($parts[1])) {
                        $lvl_2_options[] = $parts[1];
                    }
                }
            }
            $lvl_2_options = array_unique($lvl_2_options);
        }

        if (count($lv2_filter) == 1) {
            foreach ($filtered_lvl_3_ids as $filtered_lvl_3_id) {
                $parts = explode('.', $filtered_lvl_3_id);
                if ($parts[0] == $lv1_filter[0] && $parts[1] == $lv2_filter[0]) {
                    if (isset($parts[2])) {
                        $lvl_3_options[] = $parts[2];
                    }
                }
            }
            $lvl_3_options = array_unique($lvl_3_options);
        }

        return [
            'lvl_1' => $lvl_1_options,
            'lvl_2' => $lvl_2_options,
            'lvl_3' => $lvl_3_options
        ];
    }

    private function get_filtered_tier_ids($level_filters, $lvl1_ids, $lvl2_ids, $lvl3_ids=[]) {
        $lv1_filters = isset($level_filters['lvl_1']) ? $level_filters['lvl_1'] : [];
        $lv2_filters = isset($level_filters['lvl_2']) ? $level_filters['lvl_2'] : [];
        $lv3_filters = isset($level_filters['lvl_3']) ? $level_filters['lvl_3'] : [];

        if (count($lv1_filters) == 0) {
            return [
                'lvl_1' => $lvl1_ids,
                'lvl_2' => $lvl2_ids,
                'lvl_3' => $lvl3_ids
            ];
        }
        
        $lv1_prefix_list = [];
        $lv2_prefix_list = [];
        $lv3_prefix_list = [];
        foreach($lv1_filters as $lv1_id) {
            $lv1_prefix = $lv1_id;
            $lv1_prefix_list [] = $lv1_prefix;
            foreach($lv2_filters as $lv2_id) {
                $lv2_prefix = $lv1_id . '.' . $lv2_id;
                $lv2_prefix_list [] = $lv2_prefix;
                foreach($lv3_filters as $lv3_id) {
                    $lv3_prefix = $lv1_id . '.' . $lv2_id . '.' . $lv3_id;
                    $lv3_prefix_list [] = $lv3_prefix;
                }
            }
        }
       
        $lvl1_ids = array_filter($lvl1_ids, fn($value) => in_array(explode('.', $value)[0], $lv1_prefix_list));

        if (!empty($lv2_prefix_list)) {
            $lvl2_ids = array_filter($lvl2_ids, fn($value) => in_array(
                explode('.', $value)[0] . '.' . explode('.', $value)[1], $lv2_prefix_list
            ));
        }
        else {
            $lvl2_ids = array_filter($lvl2_ids, fn($value) => in_array(explode('.', $value)[0], $lv1_prefix_list));
        }

        if (!empty($lv3_prefix_list) && !empty($lv2_prefix_list)) {
            $lvl3_ids = array_filter($lvl3_ids, fn($value) => in_array(
                explode('.', $value)[0] . '.' . explode('.', $value)[1] . '.' . explode('.', $value)[2], $lv3_prefix_list
            ));
        }
        elseif(!empty($lv2_prefix_list)) {
            $lvl3_ids = array_filter($lvl3_ids, fn($value) => in_array(
                explode('.', $value)[0] . '.' . explode('.', $value)[1], $lv2_prefix_list
            ));
        }
        else {
            $lvl3_ids = array_filter($lvl3_ids, fn($value) => in_array(explode('.', $value)[0], $lv1_prefix_list));
        }

        return [
            'lvl_1' => $lvl1_ids,
            'lvl_2' => $lvl2_ids,
            'lvl_3' => $lvl3_ids
        ];
    }

    private function get_kop_ksp_filters($covered_ids, $noncovered_ids, $level_filters) {

        $lvl_1_options = [];
        $lvl_2_options = [];

        $lv1_filter = isset($level_filters['lvl_1']) ? $level_filters['lvl_1'] : [];

        $lvl_1_ids = array_merge($covered_ids['lvl_1'],  $noncovered_ids['lvl_1']);
        $lvl_2_ids = array_merge($covered_ids['lvl_2'],  $noncovered_ids['lvl_2']);

        $lvl_1_options = array_map(function($value) {
            return substr($value, 0, -2);
        },  $lvl_1_ids);

        $lvl_2_options = [];

        if (count($lv1_filter) == 1) {
            foreach ($lvl_2_ids as $lvl_2_id) {
                $substr_id = substr($lvl_2_id, 0, 5);
                $parts = explode('.', $lvl_2_id);
                if ($substr_id == $lv1_filter[0]) {
                    $lvl_2_options[] = end($parts);

                }
            }
            $lvl_2_options = array_unique($lvl_2_options);
        }

        return [
            'lvl_1' => $lvl_1_options,
            'lvl_2' => $lvl_2_options
        ];
    }

    private function get_filtered_tier_kop_ksp_ids($level_filters, $lvl1_ids, $lvl2_ids) {
        $lv1_filters = isset($level_filters['lvl_1']) ? $level_filters['lvl_1'] : [];
        $lv2_filters = isset($level_filters['lvl_2']) ? $level_filters['lvl_2'] : [];

        if (count($lv1_filters) == 0) {
            return [
                'lvl_1' => $lvl1_ids,
                'lvl_2' => $lvl2_ids
            ];
        }
        
        $lv1_prefix_list = [];
        $lv2_prefix_list = [];
        foreach($lv1_filters as $lv1_id) {
            $lv1_prefix = $lv1_id;
            $lv1_prefix_list [] = $lv1_prefix;
            foreach($lv2_filters as $lv2_id) {
                $lv2_prefix = $lv1_id . '.' . $lv2_id;
                $lv2_prefix_list [] = $lv2_prefix;
            }
        }
       
        $lvl1_ids = array_filter($lvl1_ids, fn($value) => in_array(substr($value, 0, -2), $lv1_prefix_list));

        if (!empty($lv2_prefix_list)) {
            $lvl2_ids = array_filter($lvl2_ids, fn($value) => in_array($value, $lv2_prefix_list));
        }
        else {
            $lvl2_ids = array_filter($lvl2_ids, fn($value) => in_array(substr($value, 0, -2), $lv1_prefix_list));
        }

        return [
            'lvl_1' => $lvl1_ids,
            'lvl_2' => $lvl2_ids
        ];
    }

    private function format_kop_ksp_output_data($data, $table_id, $selected=true, $level_filters=[]) {
        $graph_data = [];
        $covered_table = [];
        $noncovered_table = [];
        $tier_data = $selected ?
                $data['absolute_alignment']['selected_programs'] :
                $data['absolute_alignment']['unselected_programs'];
     
        // get covered ids
        $lvl3_ids = array_map(function($value){ return $value . '.0';}, array_keys($tier_data['third_tier']));
        $lvl4_ids = array_keys($tier_data['fourth_tier']);
        $lvl4_no_zero_ids = array_values(array_filter($lvl4_ids, function($value) {
            return end(explode('.', $value)) != '0';
        }));
        
        // get non-covered ids
        $lvl3_noncovered_ids = $this->SOCOM_COA_model->get_kop_ksp_noncovered($lvl3_ids, 3);
        $lvl4_noncovered_ids = $this->SOCOM_COA_model->get_kop_ksp_noncovered($lvl4_no_zero_ids, 4);
        // get description map
        $lvl3_description_map = $this->SOCOM_COA_model->get_kop_ksp_description($lvl3_ids);
        $lvl4_description_map = $this->SOCOM_COA_model->get_kop_ksp_description($lvl4_ids);
        $lvl4_noncovered_description_map = $this->SOCOM_COA_model->get_kop_ksp_description($lvl4_noncovered_ids);

        $filtered_ids = $this->get_filtered_tier_kop_ksp_ids($level_filters, $lvl3_ids, $lvl4_ids);
        $filtered_noncovered_ids = $this->get_filtered_tier_kop_ksp_ids($level_filters, $lvl3_noncovered_ids, $lvl4_noncovered_ids);

        $graph_tier_1 = $filtered_ids['lvl_1'];
        $graph_tier_2 = $filtered_ids['lvl_2'];

        // setup parents
        foreach ($graph_tier_1 as $id) {
            $description = '';
            $description = $lvl3_description_map[$id]['DESCRIPTION'];
            $parent_id = substr(strval($id), 0, -2);
            $color = isset($this->treemap_colors[$parent_id]) ?
                $this->treemap_colors[$parent_id] : $this->generateRandomHexColor();
            $this->treemap_colors[$parent_id] = $color;

            $graph_data[] = [
                'id' => $parent_id,
                'name' => substr(strval($id), 0, -2),
                'color' => $color,
                'programs' => implode(", ", $this->covertToPrograms(array_keys($tier_data['third_tier'][$parent_id]))),
                'text' => $description
            ];
        }

        // setup children
        foreach ($graph_tier_2 as $id) {
            $graph_data [] = [
                'name' =>  $id,
                'parent' =>  substr($id, 0, -2),
                'value' => $this->totalProgramValue($tier_data['fourth_tier'][$id]),
                'programs' => implode(", ", $this->covertToPrograms(array_keys($tier_data['fourth_tier'][$id]))),
                'text' =>  $lvl4_description_map[$id]['DESCRIPTION']
            ];
        }

        $cga_covered_ids = $filtered_ids['lvl_2'];
        $cga_noncovered_ids = $filtered_noncovered_ids['lvl_2'];
        sort($cga_covered_ids);
        sort($cga_noncovered_ids);

        foreach($cga_covered_ids as $id) {
            $covered_table[] = [
                'KOP_KSP' => $id,
                'RESOURCE_K' => round($this->totalProgramValue($tier_data['fourth_tier'][$id])),
                'DESCRIPTION' =>  $lvl4_description_map[$id]['DESCRIPTION'],
                'PROGRAM_BREAKDOWN' => $this->get_program_breakdown_button('kop-ksp', $id, $table_id, $selected)
            ];

            $program_breakdown_map[$id] = [
                'title' => 'KOPs/KOPs ' . $id . ' - '. $lvl4_description_map[$id]['DESCRIPTION'],
                'data' => $this->format_program_breakdown_data($tier_data['fourth_tier'][$id])
            ];
        }

        foreach($cga_noncovered_ids as $id) {
            $noncovered_table[] = [
                'KOP_KSP' => $id,
                'DESCRIPTION' =>  $lvl4_noncovered_description_map[$id]['DESCRIPTION']
            ];
        }

        $groupped_ids = [
            'lvl_1' => $lvl3_ids,
            'lvl_2' => $lvl4_ids,
        ];

        $groupped_noncovered_ids = [
            'lvl_1' => $lvl3_noncovered_ids,
            'lvl_2' => $lvl4_noncovered_ids
        ];
        
        return [
            'graph' => $graph_data,
            'table' => [
                'covered' => $covered_table,
                'noncovered' => $noncovered_table
            ],
            'filters' => $this->get_kop_ksp_filters($groupped_ids, $groupped_noncovered_ids, $level_filters),
            'program_breakdown' => $program_breakdown_map
        ];
    }

    private function format_capability_gaps_output_data($data, $table_id, $selected=true, $level_filters=[]) {
        $graph_data = [];
        $covered_table = [];
        $noncovered_table = [];
        $tier_data = $selected ?
                $data['absolute_alignment']['selected_programs'] :
                $data['absolute_alignment']['unselected_programs'];
                
        // get covered ids
        $lvl1_ids = array_keys($tier_data['first_tier']);
        $lvl2_ids = array_keys($tier_data['second_tier']);
        
        // get non-covered ids
        $lvl1_noncovered_ids = $this->SOCOM_COA_model->get_capability_gaps_noncovered($lvl1_ids, 'group_id');
        $lvl2_noncovered_ids = $this->SOCOM_COA_model->get_capability_gaps_noncovered($lvl2_ids, 'gap_id');

        $gap_ids = array_map(fn($value) => explode('.', $value)[1], $lvl2_ids);
        $noncovered_gap_ids = array_map(fn($value) => explode('.', $value)[1], $lvl2_noncovered_ids);
        
        // get description map
        $cga_description_map = $this->SOCOM_COA_model->get_capability_gaps_description($gap_ids);
        $noncovered_cga_description_map = $this->SOCOM_COA_model->get_capability_gaps_description($noncovered_gap_ids);

        $filtered_cga_ids = $this->get_filtered_tier_ids($level_filters, $lvl1_ids, $lvl2_ids);
        $filtered_noncovered_cga_ids = $this->get_filtered_tier_ids($level_filters, $lvl1_noncovered_ids, $lvl2_noncovered_ids);

        $graph_tier_1 = $filtered_cga_ids['lvl_1'];
        $graph_tier_2 = $filtered_cga_ids['lvl_2'];

        // setup parents
        foreach ($graph_tier_1 as $id) {
            $children = array_values(array_filter($graph_tier_2, fn($value) => strpos($value, $id) === 0));
            $description = '';
            if (count($children) > 0){
                $gap_id = explode('.', $children[0])[1];
                $description = $cga_description_map[$gap_id]['GROUP_DESCRIPTION'];
            }

            $parent_id = strval($id);
            $color = isset($this->treemap_colors[$parent_id]) ?
                $this->treemap_colors[$parent_id] : $this->generateRandomHexColor();
            $this->treemap_colors[$parent_id] = $color;

            $graph_data[] = [
                'id' => $parent_id,
                'name' => 'EGL Group ' . $id,
                'color' => $color,
                'programs' =>  implode(", ", $this->covertToPrograms(array_keys($tier_data['first_tier'][$id]))),
                'text' => $description
            ];
        }

        // setup children
        foreach ($graph_tier_2 as $id) {
            $gap_id = explode('.', $id)[1];
            $graph_data [] = [
                'name' =>  $cga_description_map[$gap_id]['CGA_NAME'],
                'parent' =>  $cga_description_map[$gap_id]['GROUP_ID'],
                'value' => $this->totalProgramValue($tier_data['second_tier'][$id]),
                'programs' =>  implode(", ", $this->covertToPrograms(array_keys($tier_data['second_tier'][$id]))),
                'text' =>  $cga_description_map[$gap_id]['GAP_DESCRIPTION']
            ];
        }

        $cga_covered_ids = $filtered_cga_ids['lvl_2'];
        $cga_noncovered_ids = $filtered_noncovered_cga_ids['lvl_2'];
        sort($cga_covered_ids);
        sort($cga_noncovered_ids);

        $program_breakdown_map = [];
        foreach($cga_covered_ids as $id) {
            $splited_id = explode('.', $id);
            $gap_id = strval($splited_id[1]);

            $covered_table[] = [
                'CAPABILITY_GAPS' => $cga_description_map[$gap_id]['CGA_NAME'],
                'RESOURCE_K' => round($this->totalProgramValue($tier_data['second_tier'][$id])),
                'DESCRIPTION' =>  $cga_description_map[$gap_id]['GAP_DESCRIPTION'],
                'PROGRAM_BREAKDOWN' => $this->get_program_breakdown_button('capability-gaps', $id, $table_id, $selected)
            ];

            $program_breakdown_map[$id] = [
                'title' => $cga_description_map[$gap_id]['CGA_NAME'] . ' - ' . $cga_description_map[$gap_id]['GAP_DESCRIPTION'],
                'data' => $this->format_program_breakdown_data($tier_data['second_tier'][$id])
            ];
        }

        foreach($cga_noncovered_ids as $id) {
            $splited_id = explode('.', $id);
            $gap_id = strval($splited_id[1]);

            $noncovered_table[] = [
                'CAPABILITY_GAPS' => $noncovered_cga_description_map[$gap_id]['CGA_NAME'],
                'DESCRIPTION' =>  $noncovered_cga_description_map[$gap_id]['GAP_DESCRIPTION']
            ];
        }

        $non_filtered_cga_ids = [
            'lvl_1' => $lvl1_ids,
            'lvl_2' => $lvl2_ids,
        ];
 
        $non_filtered_noncovered_cga_ids = [
            'lvl_1' => $lvl1_noncovered_ids,
            'lvl_2' => $lvl2_noncovered_ids,
        ];

        return [
            'graph' => $graph_data,
            'table' => [
                'covered' => $covered_table,
                'noncovered' => $noncovered_table
            ],
            'filters' => $this->get_jca_alignment_filters($non_filtered_cga_ids, $non_filtered_noncovered_cga_ids, $level_filters),
            'program_breakdown' => $program_breakdown_map
        ];
    }
    
        
    private function format_jca_alignment_output_data($data, $table_id, $selected=true, $level3_details=false, $level_filters=[]) {
        $graph_data = [];
        $covered_table = [];
        $noncovered_table = [];
        $tier_data = $selected ?
                $data['absolute_alignment']['selected_programs'] :
                $data['absolute_alignment']['unselected_programs'];

        // get covered ids
        $lvl1_ids = array_map(fn($value) => $value . '.0.0', array_keys($tier_data['first_tier']));
        $lvl2_ids = array_values(
            array_filter(array_map(fn($value) => $value . '.0', array_keys($tier_data['second_tier'])), function($version) {
            return preg_match("/^([1-9]+)\.([1-9]+)\.0$/", $version);
        }));
        $lvl3_ids = array_values(array_filter(array_keys($tier_data['third_tier']), function($version) {
            return preg_match('/^([1-9]+)\.([1-9]+)\.([1-9]+)$/', $version);
        }));

        $jca_ids = array_merge($lvl1_ids, $lvl2_ids, $lvl3_ids);

        // get non-covered ids
        $lvl1_noncovered_ids = $this->SOCOM_COA_model->get_jca_alignment_noncovered($lvl1_ids, 1);
        $lvl2_noncovered_ids = $this->SOCOM_COA_model->get_jca_alignment_noncovered($lvl2_ids, 2);
        $lvl3_noncovered_ids = $this->SOCOM_COA_model->get_jca_alignment_noncovered($lvl3_ids, 3);
        
        $noncovered_jca_ids = array_merge($lvl1_noncovered_ids, $lvl2_noncovered_ids, $lvl3_noncovered_ids);
        
        // get description map
        $jca_description_map = $this->SOCOM_COA_model->get_jca_alignment_description($jca_ids);
        $noncovered_jca_description_map = $this->SOCOM_COA_model->get_jca_alignment_description($noncovered_jca_ids);

        $filtered_jca_ids = $this->get_filtered_tier_ids($level_filters, $lvl1_ids, $lvl2_ids, $lvl3_ids);
        $filtered_noncovered_jca_ids = $this->get_filtered_tier_ids($level_filters, $lvl1_noncovered_ids, $lvl2_noncovered_ids, $lvl3_noncovered_ids);

        if (!$level3_details) {
            $tier_1 = 'lvl_1';
            $tier_2 = 'lvl_2';
            $tier = 'second_tier';
            $tier_parent = 'first_tier';
            $parent_pos = 1;
            $child_pos = 3;
        }
        else {
            $tier_1 = 'lvl_2';
            $tier_2 = 'lvl_3';
            $tier = 'third_tier';
            $tier_parent = 'second_tier';
            $parent_pos = 3;
            $child_pos = 5;
        }

        $graph_tier_1 = $filtered_jca_ids[$tier_1];
        $graph_tier_2 = $filtered_jca_ids[$tier_2];
        
        // setup parents
        foreach ($graph_tier_1 as $id) {
            $parent_id = substr($id, 0, $parent_pos);
            $treemap_color_id = explode('.', $id)[0];
            $color = isset($this->treemap_colors[$treemap_color_id]) ? $this->treemap_colors[$treemap_color_id] : $this->generateRandomHexColor();
            $this->treemap_colors[$parent_id] = $color;
            $graph_data[] = [
                'id' => $parent_id,
                'name' => $parent_id,
                'color' => $color,
                'text' =>  $jca_description_map[$id],
                'programs' => implode(", ", $this->covertToPrograms(array_keys($tier_data[$tier_parent][$parent_id]))),
                'prefix' => 'JCA'
            ];

            if (count(array_filter($graph_tier_2, function($v) use($parent_pos, $id) {
                return substr($v, 0, $parent_pos) == substr($id, 0, $parent_pos);
            })) == 0) {
                $graph_tier_2[] = $id;
            }
        }

        // setup parents
        foreach ($graph_tier_2 as $id) {
            if (substr($id, $child_pos - 1, $child_pos) === '0') {
                $graph_data [] = [
                    'name' =>  '',
                    'parent' => substr($id, 0, $parent_pos),
                    'value' => round($this->totalProgramValue($tier_data[$tier_parent][substr($id, 0, $parent_pos)])),
                    'text' =>  $jca_description_map[$id],
                    'programs' =>  implode(", ", $this->covertToPrograms(array_keys($tier_data[$tier_parent][substr($id, 0, $parent_pos)]))),
                    'prefix' => 'JCA'
                ];
            }
            else{
                $graph_data [] = [
                    'name' =>  substr($id, 0, $child_pos),
                    'parent' => substr($id, 0, $parent_pos),
                    'value' => round($this->totalProgramValue($tier_data[$tier][substr($id, 0, $child_pos)])),
                    'text' =>  $jca_description_map[$id],
                    'programs' => implode(", ", $this->covertToPrograms(array_keys($tier_data[$tier][substr($id, 0, $child_pos)]))),
                    'prefix' => 'JCA'
                ];
            }
        }

        if (!$level3_details) {
            $jca_covered_ids = $filtered_jca_ids['lvl_2'];
            $jca_noncovered_ids = $filtered_noncovered_jca_ids['lvl_2'];
        }else {
            $jca_covered_ids = $filtered_jca_ids['lvl_3'];
            $jca_noncovered_ids = $filtered_noncovered_jca_ids['lvl_3'];
        }
        sort($jca_covered_ids);
        sort($jca_noncovered_ids);

        $program_breakdown_map = [];
        foreach($jca_covered_ids as $id) {
            $splited_id = explode('.', $id);
            $first_level = strval($splited_id[0]);
            $second_level = strval($splited_id[1]);
            $third_level = strval($splited_id[2]);

            if ($second_level == '0' && $third_level == '0') {
                $tier = 'first_tier';
                $jca_alignment = $first_level;
            }
            else if ($third_level == '0') {
                $tier = 'second_tier';
                $jca_alignment = $first_level . '.' .  $second_level;
            }
            else {
                $tier = 'third_tier';
                $jca_alignment = $id;
            }

            $covered_table[] = [
                'JCA_ALIGNMENT' => $jca_alignment,
                'RESOURCE_K' => round($this->totalProgramValue($tier_data[$tier][$jca_alignment])),
                'DESCRIPTION' =>  $jca_description_map[$id],
                'PROGRAM_BREAKDOWN' => $this->get_program_breakdown_button('jca-alignment', $id, $table_id, $selected)
            ];
            $program_breakdown_map[$id] = [
                'title' => 'JCA '. $jca_alignment . ' - ' . $jca_description_map[$id],
                'data' => $this->format_program_breakdown_data($tier_data[$tier][$jca_alignment])
            ];
        }

        foreach($jca_noncovered_ids as $id) {
            $splited_id = explode('.', $id);
            $first_level = strval($splited_id[0]);
            $second_level = strval($splited_id[1]);
            $third_level = strval($splited_id[2]);

            if ($second_level == '0' && $third_level == '0') {
                $tier = 'first_tier';
                $jca_alignment = $first_level;
            }
            else if ($third_level == '0') {
                $tier = 'second_tier';
                $jca_alignment = $first_level . '.' .  $second_level;
            }
            else {
                $tier = 'third_tier';
                $jca_alignment = $id;
            }

            $noncovered_table[] = [
                'JCA_ALIGNMENT' => $jca_alignment,
                'DESCRIPTION' =>  $noncovered_jca_description_map[$id]
            ];
        }

        $non_filtered_jca_ids = [
            'lvl_1' => $lvl1_ids,
            'lvl_2' => $lvl2_ids,
            'lvl_3' => $lvl3_ids
        ];
 
        $non_filtered_noncovered_jca_ids = [
            'lvl_1' => $lvl1_noncovered_ids,
            'lvl_2' => $lvl2_noncovered_ids,
            'lvl_3' => $lvl3_noncovered_ids
        ];

        return [
            'graph' => $graph_data,
            'table' => [
                'covered' => $covered_table,
                'noncovered' => $noncovered_table
            ],
            'filters' => $this->get_jca_alignment_filters($non_filtered_jca_ids, $non_filtered_noncovered_jca_ids, $level_filters),
            'program_breakdown' => $program_breakdown_map
        ];
    }

    private function format_eoc_code_manual_override_session($saved_coa_id, $type_of_coa) {

        $session = json_decode(
            $this->SOCOM_COA_model->get_manual_override_session($saved_coa_id)['OVERRIDE_TABLE_SESSION'], true
        );

        $coa_output = $session['coa_output'];
        $included_result = [];

        $program_group_map = $this->SOCOM_COA_model->get_program_group_map($type_of_coa);
        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
        $year_list = json_decode($mdata['YEAR_LIST'], true);
        if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {
            $resource_or_delta = 'DELTA_AMT';
        } else {
            $resource_or_delta = 'RESOURCE_K';
        }
        foreach($coa_output as $coa) {
            if ($coa['RESOURCE CATEGORY'] != 'Committed Grand Total $K') {
                $unique_id = $coa['Program'] . "_" . $coa['CAP SPONSOR'] . "_" . $coa['POM SPONSOR'] . "_" . 
                    $coa['EOC'] . "_" . $coa['RESOURCE CATEGORY'];
                if (!isset($included_result[$unique_id])) {
                    $progran_id = covertToProgramId(
                        $type_of_coa,
                        [
                            'program_code' => $coa['Program'] ?? '',
                            'cap_sponsor' => $coa['CAP SPONSOR'] ?? '',
                            'pom_sponsor' => $coa['POM SPONSOR'] ?? '',
                            'ass_area_code' => $coa['ASSESSMENT AREA'] ?? '',
                            'execution_manager' => $coa['EXECUTION MANAGER'] ?? '',
                            'resource_category' => $coa['RESOURCE CATEGORY'] ?? '',
                            'eoc_code' => $coa['EOC'] ?? '',
                            'osd_pe_code' => $coa['OSD PE'] ?? '',
                            'event_name' => $coa['EVENT NAME'] ?? ''
                        ]
                    );
                    $resource_k = [];
                    foreach($year_list as $year) {
                        $resource_k[$year] = $coa[$year] ? intval($coa[$year]) : 0 ;
                    }
                    $included_result[$unique_id] = [
                        'ID' =>   $progran_id ,
                        'PROGRAM_CODE' =>  $coa['Program'],
                        'EOC_CODE' => $coa['EOC'],
                        'RESOURCE_CATEGORY_CODE' => $coa['RESOURCE CATEGORY'],
                        'CAPABILITY_SPONSOR_CODE' => $coa['CAP SPONSOR'],
                        'PROGRAM_GROUP' => $program_group_map[$progran_id],
                        'OSD_PE' => $coa['OSD PE'],
                        $resource_or_delta =>  $resource_k
                    ];
                }
                else {
                    foreach($year_list as $year) {
                        $included_result[$unique_id][$resource_or_delta][$year] += $coa[$year] ? intval($coa[$year]) : 0;
                    }
                }
            }
        }

        // Get unselected program ids
        $unselected_program_ids = $session['unselected_program_ids'];
        $fetchedOutput = $this->SOCOM_COA_model->fetchOutputInfoIssExtract($unselected_program_ids);

        return  [
            'included_result' => array_values($included_result),
            'excluded_result' => $fetchedOutput
        ];
    }

    private function check_for_partial_funding_eoc_code(&$coa_data, $saved_coa_id, $is_excluded = false) {
        $saved_coa = $this->SOCOM_COA_model->get_saved_coa_values($saved_coa_id);
        $partial_funding_program_ids = [];

        $unselected_program_ids = [];
        if ($this->SOCOM_COA_model->is_manual_override($saved_coa_id)) {
            $session = json_decode(
                $this->SOCOM_COA_model->get_manual_override_session($saved_coa_id)['OVERRIDE_TABLE_SESSION'], true
            );
            $unselected_program_ids = $session['unselected_program_ids'];
        }

        foreach($coa_data as &$coa) {
            $program_id = $coa['ID'];
            $resource_k = $coa['DELTA_AMT'];
            foreach($resource_k as $year => $program_value) {
                $value = null;
                $valid_resource_funds = (isset($saved_coa['resource_k'][$year][$program_id]) && isset($resource_k[$year]) || !$saved_coa 
                                            || (!empty($unselected_program_ids) && !in_array($program_id, array_values($unselected_program_ids))));
                if ($is_excluded) {
                    $valid_resource_funds = !$valid_resource_funds;
                }
                
                if ($valid_resource_funds) {
                    $value = $resource_k[$year];
                }
                $coa['DELTA_AMT'][$year] = $value;
            }
            if ($coa['DELTA_AMT'] !== $resource_k) {
                $partial_funding_program_ids[] = $program_id;
            }
        }
        return $partial_funding_program_ids;
    }

    private function format_detailed_summary_data($type, $saved_coa_id, $table_id, $selected_ids, $unselected_ids) {
        $data = [];
        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
        $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';
        switch($type) {
            case 'eoc-code':


                if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {
                    if ($this->SOCOM_COA_model->is_manual_override($saved_coa_id)) {
                        $formatted_session = $this->format_eoc_code_manual_override_session($saved_coa_id, $mdata['TYPE_OF_COA']);
                        $included_result = $formatted_session['included_result'];
                    }
                    else {
                        $included_result = $this->SOCOM_COA_model->fetchOutputInfoIssExtract($selected_ids);
                    }

                    $partial_funding_program_ids = $this->check_for_partial_funding_eoc_code($included_result, $saved_coa_id);
                    $unselected_ids = array_merge($unselected_ids, $partial_funding_program_ids);
                    $excluded_result = $this->SOCOM_COA_model->fetchOutputInfoIssExtract($unselected_ids);
                    $this->check_for_partial_funding_eoc_code($excluded_result, $saved_coa_id, true);
                }
                else {
                    $eoc_funding = $this->SOCOM_COA_model->get_eoc_funding($saved_coa_id);
                    $included_result = $eoc_funding['includes'];
                    $excluded_result = $eoc_funding['excludes'];
                }

                $included_data = $this->format_eoc_code_output_data($included_result, $saved_coa_id);
                $excluded_data = $this->format_eoc_code_output_data($excluded_result, $saved_coa_id);

                $data = [
                    'table' => [ 
                        'included' => [
                            'data' => $included_data['table']
                        ],
                        'excluded' => [
                            'data' => $excluded_data['table']
                        ],
                        'headers' => $this->detailed_summary_headers[$type],
                    ],
                    'graph' => [
                        'included' => $included_data['graph'],
                        'excluded' => $excluded_data['graph']
                    ],
                    'filter' => [
                        'included' => $included_data['filters'],
                        'excluded' => $excluded_data['filters']
                    ]
                ];
                break;
            case 'jca-alignment':
                $result = $this->SOCOM_COA_model->get_jca_alignment_data($saved_coa_id, $coa_type);
                $included_data = $this->format_jca_alignment_output_data($result, $table_id);
                $excluded_data = $this->format_jca_alignment_output_data($result, $table_id, false);
                
                $final_filter = [];
                foreach ($included_data['filters'] as $key => $value) {
                    $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
                    sort($final_filter[$key]);
                }

                $data =  [
                    'graph' => [
                        'included' => $included_data['graph'],
                        'excluded' => $excluded_data['graph']
                    ],
                    'table' => [ 
                        'included' => [
                            'covered' => $included_data['table']['covered'],
                            'noncovered' => $included_data['table']['noncovered']
                        ],
                        'excluded' => [
                            'covered' => $excluded_data['table']['covered'],
                            'noncovered' => $excluded_data['table']['noncovered']
                        ],
                        'headers' => $this->detailed_summary_headers[$type],
                    ],
                    'filter' => $final_filter,
                    'treemap_colors' => $this->treemap_colors,
                    'program_breakdown' => [
                        'included' => $included_data['program_breakdown'],
                        'excluded' => $excluded_data['program_breakdown']
                    ]
                ];
                break;
            case 'capability-gaps':
                $result = $this->SOCOM_COA_model->get_capability_gaps_data($saved_coa_id, $coa_type);

                $included_data = $this->format_capability_gaps_output_data($result, $table_id);
                $excluded_data = $this->format_capability_gaps_output_data($result, $table_id, false);

                $final_filter = [];
                foreach ($included_data['filters'] as $key => $value) {
                    $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
                    sort($final_filter[$key]);
                }

                $data =  [
                    'graph' => [
                        'included' => $included_data['graph'],
                        'excluded' => $excluded_data['graph']
                    ],
                    'table' => [ 
                        'included' => [
                            'covered' => $included_data['table']['covered'],
                            'noncovered' => $included_data['table']['noncovered']
                        ],
                        'excluded' => [
                            'covered' => $excluded_data['table']['covered'],
                            'noncovered' => $excluded_data['table']['noncovered']
                        ],
                        'headers' => $this->detailed_summary_headers[$type],
                    ],
                    'filter' => $final_filter,
                    'treemap_colors' => $this->treemap_colors,
                    'program_breakdown' => [
                        'included' => $included_data['program_breakdown'],
                        'excluded' => $excluded_data['program_breakdown']
                    ]
                ];
                break;
            case 'kop-ksp':
                $result = $this->SOCOM_COA_model->get_kop_ksp_data($saved_coa_id, $coa_type);

                $included_data = $this->format_kop_ksp_output_data($result, $table_id);
                $excluded_data = $this->format_kop_ksp_output_data($result, $table_id, false);

                $final_filter = [];
                foreach ($included_data['filters'] as $key => $value) {
                    $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
                    sort($final_filter[$key]);
                }

                $data =  [
                    'graph' => [
                        'included' => $included_data['graph'],
                        'excluded' => $excluded_data['graph']
                    ],
                    'table' => [ 
                        'included' => [
                            'covered' => $included_data['table']['covered'],
                            'noncovered' => $included_data['table']['noncovered']
                        ],
                        'excluded' => [
                            'covered' => $excluded_data['table']['covered'],
                            'noncovered' => $excluded_data['table']['noncovered']
                        ],
                        'headers' => $this->detailed_summary_headers[$type],
                    ],
                    'filter' => $final_filter,
                    'treemap_colors' => $this->treemap_colors,
                    'program_breakdown' => [
                        'included' => $included_data['program_breakdown'],
                        'excluded' => $excluded_data['program_breakdown']
                    ]
                ];
                break;
            case 'issue-analysis':
                $result = $this->format_detailed_summary_issue_analysis_output_data($saved_coa_id);

                $event_info = $result['event'];
                $program_eoc_info = $result['program_eoc'];
                
                $data = [
                    'event' => [
                        'fully_funded' => $event_info['data']['fully_funded'],
                        'partially_funded' => $event_info['data']['partially_funded'],
                        'non_funded' => $event_info['data']['non_funded'],
                        'headers' => $event_info['headers'],
                        'fiscal_years' => $event_info['fiscal_years'],
                    ],
                    'program_eoc' => [
                        'headers' => $program_eoc_info['headers'],
                        'eoc_information' => $program_eoc_info['data']['eoc_information'],
                        'requested_funding' => $program_eoc_info['data']['requested_funding'],
                        'proposed_funding' => $program_eoc_info['data']['proposed_funding']
                    ],
                ];
                break;
            default:
                break;
        }
        return $data;
    }

    public function get_detailed_summary_data($scenario_id) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $type = $post_data['type'];
            $selected_ids = isset($post_data['selected_ids']) ? json_decode($post_data['selected_ids'], true) : [];
            $unselected_ids = isset($post_data['unselected_ids']) ? json_decode($post_data['unselected_ids'], JSON_OBJECT_AS_ARRAY) : [];
            $table_id = isset($post_data['table_id']) ? $post_data['table_id'] : [];

            $response = $this->format_detailed_summary_data($type, $scenario_id, $table_id, $selected_ids, $unselected_ids);
            $http_status = 200;

            $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response]));
        }
    }

    public function get_program_breakdown($type) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $title = $post_data['title'];
            $page_data['headers'] = $this->detailed_summary_headers[$type]['program_breakdown'];
            $page_data['type'] = $type;
            $page_data['title'] = $title;
            $this->load->view('SOCOM/optimizer/coa_program_breakdown_table_view.php', $page_data);
        }
    }

    private function  update_detailed_summary_eoc_code_view($post_data) {
        $program_ids = isset($post_data['program_ids']) ? json_decode($post_data['program_ids'], true) : [];
        $saved_coa_id = isset($post_data['saved_coa_id']) ? $post_data['saved_coa_id'] : 0;
        $cap_sponsor = isset($post_data['cap_sponsor']) ? $post_data['cap_sponsor'] : [];
        $mode = isset($post_data['mode']) ? $post_data['mode'] : 'included';
        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);

        $filters = [
            'cap_sponsor' => $cap_sponsor
        ];

        if ($this->SOCOM_COA_model->is_manual_override($saved_coa_id)) {
            $formatted_session = $this->format_eoc_code_manual_override_session($saved_coa_id, $mdata['TYPE_OF_COA']);
            $result = $formatted_session[ $mode . '_result'];
        } else {
            if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {
                $result = $this->SOCOM_COA_model->fetchOutputInfoIssExtract($program_ids);
                $is_excluded = false;
                if ($mode === 'excluded') {
                    $is_excluded = true;
                }
                $this->check_for_partial_funding_eoc_code($result, $saved_coa_id, $is_excluded);
            } else {
                $eoc_funding = $this->SOCOM_COA_model->get_eoc_funding($saved_coa_id);
                $result = $eoc_funding['excludes'];
            }
        }

        $response = $this->format_eoc_code_output_data($result, $saved_coa_id, $filters);
        $response['headers'] = $this->detailed_summary_headers['eoc-code'];

        return $response;
    }

    private function  update_detailed_summary_jca_alignment_view($post_data) {
        $details_checkbox = isset($post_data['details_checkbox']) && $post_data['details_checkbox'] == 'true'? true : false;
        $saved_coa_id = isset($post_data['saved_coa_id']) ? $post_data['saved_coa_id'] : 0;
        $table_id = isset($post_data['table_id']) ? $post_data['table_id'] : 0;
        $this->treemap_colors = isset($post_data['treemap_colors']) ? 
            json_decode($post_data['treemap_colors'], true) : $this->treemap_colors;
        $level_filters = [
            'lvl_1' => isset($post_data['lvl_1']) ? $post_data['lvl_1'] : [],
            'lvl_2' => isset($post_data['lvl_2']) ? $post_data['lvl_2'] : [],
            'lvl_3' => isset($post_data['lvl_3']) ? $post_data['lvl_3'] : []
        ];
        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
        $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';
        $result = $this->SOCOM_COA_model->get_jca_alignment_data($saved_coa_id, $coa_type);

        $included_data = $this->format_jca_alignment_output_data($result, $table_id, true,  $details_checkbox, $level_filters);
        $excluded_data = $this->format_jca_alignment_output_data($result, $table_id, false, $details_checkbox, $level_filters);

        $final_filter = [];
        foreach ($included_data['filters'] as $key => $value) {
            $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
            sort($final_filter[$key]);
        }

        return [
            'graph' => [
                'included' => $included_data['graph'],
                'excluded' => $excluded_data['graph']
            ],
            'table' => [ 
                'included' => [
                    'covered' => $included_data['table']['covered'],
                    'noncovered' => $included_data['table']['noncovered']
                ],
                'excluded' => [
                    'covered' => $excluded_data['table']['covered'],
                    'noncovered' => $excluded_data['table']['noncovered']
                ],
                'headers' => $this->detailed_summary_headers['jca-alignment']
            ],
            'filter' =>  $final_filter,
            'treemap_colors' => $this->treemap_colors,
            'program_breakdown' => [
                'included' => $included_data['program_breakdown'],
                'excluded' => $excluded_data['program_breakdown']
            ]
        ];
    }

    private function update_detailed_summary_capability_gaps_view($post_data) {
        $saved_coa_id = isset($post_data['saved_coa_id']) ? $post_data['saved_coa_id'] : 0;
        $table_id = isset($post_data['table_id']) ? $post_data['table_id'] : 0;
        $this->treemap_colors = isset($post_data['treemap_colors']) ? 
            json_decode($post_data['treemap_colors'], true) : $this->treemap_colors;
        $level_filters = [
            'lvl_1' => isset($post_data['lvl_1']) ? $post_data['lvl_1'] : [],
            'lvl_2' => isset($post_data['lvl_2']) ? $post_data['lvl_2'] : []
        ];

        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
        $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';
        $result = $this->SOCOM_COA_model->get_capability_gaps_data($saved_coa_id, $coa_type);

        $included_data = $this->format_capability_gaps_output_data($result, $table_id, true, $level_filters);
        $excluded_data = $this->format_capability_gaps_output_data($result, $table_id, false, $level_filters);

        $final_filter = [];
        foreach ($included_data['filters'] as $key => $value) {
            $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
            sort($final_filter[$key]);
        }

        return [
            'graph' => [
                'included' => $included_data['graph'],
                'excluded' => $excluded_data['graph']
            ],
            'table' => [ 
                'included' => [
                    'covered' => $included_data['table']['covered'],
                    'noncovered' => $included_data['table']['noncovered']
                ],
                'excluded' => [
                    'covered' => $excluded_data['table']['covered'],
                    'noncovered' => $excluded_data['table']['noncovered']
                ],
                'headers' => $this->detailed_summary_headers['capability-gaps']
            ],
            'filter' =>  $final_filter,
            'treemap_colors' => $this->treemap_colors,
            'program_breakdown' => [
                'included' => $included_data['program_breakdown'],
                'excluded' => $excluded_data['program_breakdown']
            ]
        ];
    }

    private function update_detailed_summary_kop_ksp_view($post_data) {
        $saved_coa_id = isset($post_data['saved_coa_id']) ? $post_data['saved_coa_id'] : 0;
        $table_id = isset($post_data['table_id']) ? $post_data['table_id'] : 0;
        $this->treemap_colors = isset($post_data['treemap_colors']) ? 
            json_decode($post_data['treemap_colors'], true) : $this->treemap_colors;

        $level_filters = [
            'lvl_1' => isset($post_data['lvl_1']) ? $post_data['lvl_1'] : [],
            'lvl_2' => isset($post_data['lvl_2']) ? $post_data['lvl_2'] : []
        ];
        $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
        $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';

        $result = $this->SOCOM_COA_model->get_kop_ksp_data($saved_coa_id, $coa_type);

        $included_data = $this->format_kop_ksp_output_data($result, $table_id, true, $level_filters);
        $excluded_data = $this->format_kop_ksp_output_data($result, $table_id, false, $level_filters);

        $final_filter = [];
        foreach ($included_data['filters'] as $key => $value) {
            $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
            sort($final_filter[$key]);
        }

        return [
            'graph' => [
                'included' => $included_data['graph'],
                'excluded' => $excluded_data['graph']
            ],
            'table' => [ 
                'included' => [
                    'covered' => $included_data['table']['covered'],
                    'noncovered' => $included_data['table']['noncovered']
                ],
                'excluded' => [
                    'covered' => $excluded_data['table']['covered'],
                    'noncovered' => $excluded_data['table']['noncovered']
                ],
                'headers' => $this->detailed_summary_headers['kop-ksp']
            ],
            'filter' =>  $final_filter,
            'treemap_colors' => $this->treemap_colors,
            'program_breakdown' => [
                'included' => $included_data['program_breakdown'],
                'excluded' => $excluded_data['program_breakdown']
            ]
        ];
    }

    public function update_detailed_summary_view($type) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $formatted_type = str_replace('-', '_', $type);
            $update_detailed_summary_function = "update_detailed_summary_{$formatted_type}_view";
            $response = $this->$update_detailed_summary_function($post_data);

            $http_status = 200;

            $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response]));
        }
    }

    public function get_detailed_comparison_data($type) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts     
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $formatted_type = str_replace('-', '_', $type);
            $format_detailed_comparison_function = "format_detailed_comparison_{$formatted_type}_data";
            $response = $this->$format_detailed_comparison_function($post_data, $type);

            $http_status = 200;

            $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode(['data' => $response]));
        }
    }

    private function format_detailed_comparison_issue_analysis_data($post_data, $type) {
        $saved_coa_ids = isset($post_data['saved_coa_ids']) ? $post_data['saved_coa_ids'] : [];
        $titles = isset($post_data['titles']) ? $post_data['titles'] : [];
        $result = $this->SOCOM_COA_model->get_issue_analysis_data($saved_coa_ids);

        return [
            'event' =>  $this->get_detailed_comparison_issue_analysis_event_data($result, $saved_coa_ids),
            'program_eoc' => $this->get_detailed_comparison_issue_analysis_program_eoc_data($result, $saved_coa_ids, $titles)
        ];
    }

    private function get_detailed_comparison_issue_analysis_program_eoc_data($result, $saved_coa_ids, $titles) {
        $program_eoc_data = [];

        $all_events = $result['all_events'];
        $detailed_event_summary = [];

        foreach($saved_coa_ids as $coa_id) {
            $detailed_event_summary[$coa_id] = array_merge(
                $result[$coa_id]['event']['fully_funded_issues'],
                $result[$coa_id]['event']['partially_funded_issues'],
                $result[$coa_id]['event']['non_funded_issues']
            );
        }
        $event_summary = $this->SOCOM_COA_model->get_event_summary_data($all_events, 'issue');
        $row_ids = [];

        $event_summary_group_by_row_id = [];
        foreach($all_events as $event) {
            foreach( $event_summary[$event] as $event_info) {
                $event_summary_group_by_row_id[$event_info["ROW_ID"]] = $event_info;
                $row_ids[] = $event_info["ROW_ID"];
            }
        }

        // group by coa id and row id
        $detailed_event_summary_group_by_row_id = [];
        foreach($saved_coa_ids as $coa_id) {
            $detailed_event_summary_group_by_row_id[$coa_id] = [];
            foreach ($detailed_event_summary[$coa_id] as $value) {
                foreach($value['include'] as $include_event) {
                    $detailed_event_summary_group_by_row_id[$coa_id][$include_event['ROW_ID']] = $include_event;
                }
            }
        }

        $program_eoc_data = [];
        foreach($row_ids as $row_id) {
            $event_summary_row = $event_summary_group_by_row_id[$row_id];

            //requested funding
            $requested_funding = $event_summary_row['FISCAL_YEAR'];
            $requested_funding_data = [];
            $requested_funding_fydp_delta = 0;
            foreach($requested_funding as $year => $value) {
                $requested_funding_value =$value;
                $requested_funding_data["FY" . $year] = $requested_funding_value;
                $requested_funding_fydp_delta += $requested_funding_value;
            }
            $requested_funding_data["FYDP_DELTA"] = $requested_funding_fydp_delta;

            $program_eoc_data[] = [
                'PROGRAM_CODE' => $event_summary_row['PROGRAM_CODE'],
                'EOC_CODE' => $event_summary_row['EOC_CODE'],
                'CAPABILITY_SPONSOR' => $event_summary_row['CAPABILITY_SPONSOR_CODE'],
                'ASSESSMENT_AREA' => $event_summary_row['ASSESSMENT_AREA_CODE'],
                "RESOURCE_CATEGORY" => $event_summary_row['RESOURCE_CATEGORY_CODE'],
                'OSD_PE' => $event_summary_row['OSD_PROGRAM_ELEMENT_CODE'],
                'EVENT_NAME' =>  $event_summary_row['EVENT_NAME'],
                'DELTA_LINE' => "Capability Sponsor Request",
                ...$requested_funding_data
            ];

            // proposed funding for each coa
            foreach($saved_coa_ids as $idx => $coa_id) {

                // proposed funding
                $proposed_funding = [];
                if (isset($detailed_event_summary_group_by_row_id[$coa_id][$row_id])) {
                    $proposed_funding = $detailed_event_summary_group_by_row_id[$coa_id][$row_id]['FISCAL_YEAR'];
                }
                else {
                    foreach($event_summary_row['FISCAL_YEAR'] as $year => $value) {
                        $proposed_funding[$year] = 0;
                    }
                    $proposed_funding['FYDP'] = 0;
                }

                // Set 0 to any missing years
                $fiscal_years = $this->detailed_summary_headers['issue-analysis']['fiscal_years'];
                foreach ($fiscal_years as $year) {
                    if (!isset($proposed_funding[$year])) {
                        $proposed_funding[$year] = 0;
                    }
                }

                $proposed_funding_data = [];
                foreach($proposed_funding as $year => $value) {
                    if ($year == 'FYDP') {
                        $proposed_funding_data ["FYDP_DELTA"] = $value;
                    }
                    else {
                        $proposed_funding_data ["FY" . $year] = $value;
                    }
                }

                $program_eoc_data[] = [
                    'PROGRAM_CODE' => $event_summary_row['PROGRAM_CODE'],
                    'EOC_CODE' => $event_summary_row['EOC_CODE'],
                    'CAPABILITY_SPONSOR' => $event_summary_row['CAPABILITY_SPONSOR_CODE'],
                    'ASSESSMENT_AREA' => $event_summary_row['ASSESSMENT_AREA_CODE'],
                    "RESOURCE_CATEGORY" => $event_summary_row['RESOURCE_CATEGORY_CODE'],
                    'OSD_PE' => $event_summary_row['OSD_PROGRAM_ELEMENT_CODE'],
                    'EVENT_NAME' =>  $event_summary_row['EVENT_NAME'],
                    'DELTA_LINE' => $titles[$idx] ." Proposed Funding",
                    ...$proposed_funding_data
                ];
            }
        }

        return [
            'headers' => $this->detailed_summary_headers['issue-analysis']['program_eoc']['eoc_information']['comparison'],
            'data' =>  $program_eoc_data
        ];
    }

    private function get_detailed_comparison_issue_analysis_event_data($result, $saved_coa_ids) {
        $event_headers = $this->detailed_summary_headers['issue-analysis']['event']['comparison'];
        $event_details_header = $this->detailed_summary_headers['issue-analysis']['event']['comparison_event_details_modal'];

        $data = [];
        foreach($saved_coa_ids as $coa_id) {
            $coa_result = $result[$coa_id];
            // Add CoA titles to header
            $event_headers[] = [
                'data' => $coa_id,
                'title' => $result[$coa_id]['coa_title']
            ];

            $fully_funded_data = $coa_result['event']['fully_funded_issues'];
            $partially_funded_data = $coa_result['event']['partially_funded_issues'];
            $non_funded_data = $coa_result['event']['non_funded_issues'];

            foreach($fully_funded_data as $event) {
                $event_name = $event['EVENT_NAME'];
                if (!isset($data[$event_name])) {
                    $data[$event_name]['EVENT_NAME'] = $this->get_event_name_button($event_name);
                    $data[$event_name]['EVENT_TITLE'] = $event['EVENT_TITLE'];
                }

                $data[$event_name][$coa_id] = 'Fully Funded';
            }

            foreach($partially_funded_data as $event) {
                $event_name = $event['EVENT_NAME'];
                if (!isset($data[$event_name])) {
                    $data[$event_name]['EVENT_NAME'] = $this->get_event_name_button($event_name);
                    $data[$event_name]['EVENT_TITLE'] = $event['EVENT_TITLE'];
                }
                $data[$event_name][$coa_id] = 'Partially Funded';
            }
            
            foreach($non_funded_data as $event) {
                $event_name = $event['EVENT_NAME'];
                if (!isset($data[$event_name])) {
                    $data[$event_name]['EVENT_NAME'] = $this->get_event_name_button($event_name);
                    $data[$event_name]['EVENT_TITLE'] = $event['EVENT_TITLE'];
                }
                $data[$event_name][$coa_id] = 'Not Funded';
            }

        }

        return [
            'headers' => $event_headers,
            'event_detail_header' => $event_details_header,
            'data' => array_values($data),
        ];
    }

    private function format_detailed_comparison_kop_ksp_data($post_data, $type) {
        $saved_coa_ids = isset($post_data['saved_coa_ids']) ? $post_data['saved_coa_ids'] : [];

        $data = [];
        $level_filter = [
            'lvl_1' => [],
            'lvl_2' => []
        ];
        foreach($saved_coa_ids as $saved_coa_id) {
            $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
            $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';
            
            $result = $this->SOCOM_COA_model->get_kop_ksp_data($saved_coa_id, $coa_type);
            $is_included = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? true : false;
            $included_data = $this->format_kop_ksp_output_data($result, $saved_coa_id, $is_included);

            $final_filter = [];
            foreach ($included_data['filters'] as $key => $value) {
                $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
                sort($final_filter[$key]);
            }

            $data[$saved_coa_id] =  [
                'graph' => [
                    'included' => $included_data['graph']
                ],
                'table' => [ 
                    'included' => [
                        'covered' => $included_data['table']['covered'],
                        'noncovered' => $included_data['table']['noncovered']
                    ],
                    'headers' => $this->detailed_summary_headers[$type],
                    'program_breakdown' => $included_data['program_breakdown']
                ],
            ];
            $level_filter['lvl_1'] = array_merge($final_filter['lvl_1']);
            $level_filter['lvl_2'] = array_merge($final_filter['lvl_2']);
        }
        $level_filter['lvl_1'] = array_unique($level_filter['lvl_1']);
        $level_filter['lvl_2'] = array_unique($level_filter['lvl_2']);

        $data['filter'] = $level_filter;
        $data['treemap_colors'] = $this->treemap_colors;
        return $data;
    }

    private function format_detailed_comparison_capability_gaps_data($post_data, $type) {
        $saved_coa_ids = isset($post_data['saved_coa_ids']) ? $post_data['saved_coa_ids'] : [];

        $data = [];
        $level_filter = [
            'lvl_1' => [],
            'lvl_2' => []
        ];
        foreach($saved_coa_ids as $saved_coa_id) {
            $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
            $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';
            $result = $this->SOCOM_COA_model->get_capability_gaps_data($saved_coa_id, $coa_type);
            $is_included = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? true : false;
            $included_data = $this->format_capability_gaps_output_data($result, $saved_coa_id, $is_included);

            $final_filter = [];
            foreach ($included_data['filters'] as $key => $value) {
                $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
                sort($final_filter[$key]);
            }

            $data[$saved_coa_id] =  [
                'graph' => [
                    'included' => $included_data['graph']
                ],
                'table' => [ 
                    'included' => [
                        'covered' => $included_data['table']['covered'],
                        'noncovered' => $included_data['table']['noncovered']
                    ],
                    'headers' => $this->detailed_summary_headers[$type],
                    'program_breakdown' => $included_data['program_breakdown']
                ]
            ];
            $level_filter['lvl_1'] = array_merge($final_filter['lvl_1']);
            $level_filter['lvl_2'] = array_merge($final_filter['lvl_2']);
        }
        $level_filter['lvl_1'] = array_unique($level_filter['lvl_1']);
        $level_filter['lvl_2'] = array_unique($level_filter['lvl_2']);

        $data['filter'] = $level_filter;
        $data['treemap_colors'] = $this->treemap_colors;
        return $data;
    }

    private function format_detailed_comparison_jca_alignment_data($post_data, $type) {
        $saved_coa_ids = isset($post_data['saved_coa_ids']) ? $post_data['saved_coa_ids'] : [];

        $data = [];
        $level_filter = [
            'lvl_1' => [],
            'lvl_2' => [],
            'lvl_3' => []
        ];
        foreach($saved_coa_ids as $saved_coa_id) {
            $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);
            $coa_type = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? 'iss-extract' : 'iss';
            $result = $this->SOCOM_COA_model->get_jca_alignment_data($saved_coa_id, $coa_type);
            $is_included = $mdata['TYPE_OF_COA'] === 'ISS_EXTRACT' ? true : false;
            $included_data = $this->format_jca_alignment_output_data($result, $saved_coa_id, $is_included);

            $final_filter = [];
            foreach ($included_data['filters'] as $key => $value) {
                $final_filter[$key] = array_unique(array_merge($value,$included_data['filters'][$key]), SORT_REGULAR);
                sort($final_filter[$key]);
            }

            $data[$saved_coa_id] =  [
                'graph' => [
                    'included' => $included_data['graph']
                ],
                'table' => [ 
                    'included' => [
                        'covered' => $included_data['table']['covered'],
                        'noncovered' => $included_data['table']['noncovered']
                    ],
                    'headers' => $this->detailed_summary_headers[$type],
                    'program_breakdown' => $included_data['program_breakdown']
                ]
            ];
            $level_filter['lvl_1'] = array_merge($final_filter['lvl_1']);
            $level_filter['lvl_2'] = array_merge($final_filter['lvl_2']);
            $level_filter['lvl_3'] = array_merge($final_filter['lvl_3']);
        }
        $level_filter['lvl_1'] = array_unique($level_filter['lvl_1']);
        $level_filter['lvl_2'] = array_unique($level_filter['lvl_2']);
        $level_filter['lvl_3'] = array_unique($level_filter['lvl_3']);

        $data['filter'] = $level_filter;
        $data['treemap_colors'] = $this->treemap_colors;
        return $data;
    }

    private function format_detailed_comparison_eoc_code_data($post_data, $type) {
        $selected_program_ids = isset($post_data['selected_program_ids']) ? json_decode($post_data['selected_program_ids'], true) : [];
        $saved_coa_ids = isset($post_data['saved_coa_ids']) ? $post_data['saved_coa_ids'] : [];
        
        $data = [];
        foreach($selected_program_ids as $table_id => $ids) {
            $saved_coa_id = $saved_coa_ids[$table_id];
            $mdata = $this->SOCOM_COA_model->get_metadata($saved_coa_id);

            if ($mdata['TYPE_OF_COA'] === 'ISS_EXTRACT') {

                if ($this->SOCOM_COA_model->is_manual_override($saved_coa_id)) {
                    $formatted_session = $this->format_eoc_code_manual_override_session($saved_coa_id, $mdata['TYPE_OF_COA']);
                    $result = $formatted_session['included_result'];
                }
                else {
                    $result = $this->SOCOM_COA_model->fetchOutputInfoIssExtract($ids);
                    $this->check_for_partial_funding_eoc_code($result, $saved_coa_id);
                }
            }
            else{
                $eoc_funding = $this->SOCOM_COA_model->get_eoc_funding($saved_coa_id);
                $result = $eoc_funding['excludes'];
            }

            $formatted_data = $this->format_eoc_code_output_data($result, $saved_coa_id);
            $data[$table_id] = [
                'table' => [
                    'included' => [
                        'data' => $formatted_data['table']
                    ],
                    'headers' => $this->detailed_summary_headers[$type]
                ],
                'graph' => [
                    'included' => $formatted_data['graph']
                ],
                'filter' => [
                    'included' => $formatted_data['filters']
                ]
            ];
        }
        return $data;
    }
    
    private function setOutputHeadersRowsObj($is_iss_extract_coa) {
        $table_headers = [
            'Program',
            'EOC',
            'POM SPONSOR',
            'CAP SPONSOR',
            'ASSESSMENT AREA',
            'EXECUTION MANAGER',
            'OSD PE'
        ];

        if ($is_iss_extract_coa) {
            $table_headers[] = 'Event Name';
        }

        $table_headers = array_merge($table_headers,array_keys($this->weighted_score_header_keys));
        $table_headers[] = 'RESOURCE CATEGORY';
        $data_headers = [];
        $row = [];
        $grand_row = [];
        foreach($table_headers as $header) {
            $data_headers[] = [
                'data' => $header
            ];
            $row[$header] = '';
            if ($header === 'RESOURCE CATEGORY') {
                $grand_row[$header] = 'Committed Grand Total $K';
            } else {
                $grand_row[$header] = '';
            }
        }
        return [
            'table_headers' => $table_headers,
            'data_headers' => $data_headers,
            'row' => $row,
            'grand_row' => $grand_row
        ];
    }

    private function addMissingWeightedScoreData($type_of_coa, &$row, $scores, $override_headers, $scores_original_optimization = [], $original_program_ids = []) {
        $program_name = $row['DT_RowId'] ?? '';
        $onlyOverrideProgramId = ['DT_RowId' => $row['DT_RowId'], 'ProgramId' => $program_name];

        foreach($override_headers as $header) {
            $header_key = $header['data'];
            if (!isset($row[$header_key]) && isset($this->weighted_score_header_keys[$header_key])) {
                $score_key = $this->weighted_score_header_keys[$header_key];
                if (!isset($scores[$program_name][$score_key]) && isset($scores['detail'])) {
                    $row[$header_key] = $scores_original_optimization[$score_key ][
                        $program_name
                    ] ?? 0;
                } else {
                    $row[$header_key] = ($row['Program'] !== '')  ? $scores[$program_name][$score_key] : '';
                }
            }
        }

        return $onlyOverrideProgramId;
        
    }
    
    private function checkForPartialFunding($saved_coa, $resource_k, $year, $program_name) {
        $value = null;
        $valid_resource_funds = (isset($saved_coa['resource_k'][$year][$program_name]) && isset($resource_k[$year]) || !$saved_coa);
        if ($valid_resource_funds) {
            $value = $resource_k[$year];
        }
        return $value;
    }


    private function get_original_resource_k($coa) {
        $selected_programs = json_decode($coa['COA_VALUES'], true)['selected_programs'] ?? [];
        $ids = array_column($selected_programs, 'program_id');

        $year_list = json_decode($coa['YEAR_LIST'], true) ?? [];

        $original_resource_k = [];
        foreach($year_list as $year) {
            $original_resource_k[$year] = 0;
        }

        $original_rows = json_decode($this->SOCOM_COA_model->fetchOutputInfo($ids), true);
        foreach($original_rows as $row) {
            foreach($year_list as $year) {
                $original_resource_k[$year] += $row['RESOURCE_K'][$year] ?? 0;
            }
        }
  
        return $original_resource_k;
    }
}
