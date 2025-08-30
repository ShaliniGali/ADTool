<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Program_Breakdown extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_AOAD_model');
        $this->load->model('SOCOM_Program_model');
        $this->load->model('SOCOM_COA_model');
        $this->load->model('SOCOM_Users_model');
        $this->load->model('SOCOM_Dynamic_Year_model');
        $this->load->library('SOCOM/Dynamic_Year');



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
                ],
                'approval' => [
                    'zbt_all' => 'All ZBTs',
                    'zbt_approved' => 'Approved ZBTs',
                ],
                'graph' => [
                    'categories' => [
                        'Positive Requested Changes',
                        'Negative Requested Changes',
                        'Total Requested Changes',
                    ]
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
                ],
                'approval' => [
                    'issue_all' => 'All Issues (no ZBTs)',
                    'issue_all_zbt_all' => 'All Issues and All ZBTs',
                    'issue_all_zbt_approved' => 'All Issues and Approved ZBTs',
                    'issue_approved_zbt_approved' => 'Approved Issues and Approved ZBTs'
                ],
                'graph' => [
                    'categories' => [
                        'Positive Requested Changes',
                        'Total Requested Changes'
                    ]
                ]
            ]
        ];

        $this->colors = [
            'green' => '#7eab55',
            'red' => '#f65959'
        ];

        $this->l_cap_sponsor = ['AFSOC','AT&L', 'NSW', 'USASOC'];
        $this->l_pom_sponsor = ['AFSOC','AT&L', 'CROSS', 'MARSOC'];
        $this->l_ass_area = ['A','B','D'];
    }

    // --------------------------------------------------------------------

    /**
     * 
     */
    public function index($page) {

        $page_data['page_title'] = "Program Breakdown";
        $page_data['page_tab'] = "Program Breakdown";
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
            $selected_approval = $post_data['approval'] ?? '';

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

        if($page == 'zbt_summary' || $page == 'issue'){
            $capability_sponsor = $this->DBs->SOCOM_model->get_sponsor_program_breakdown('LOOKUP_SPONSOR', 'CAPABILITY');
        } else {
            $capability_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'CAPABILITY');
        }
        $pom_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM');
        $ass_area = $this->DBs->SOCOM_model->get_assessment_area_code();
        $table_data['data'] = [
            'program_summary_data' => []
        ];
        $table_data['capability_sponsor'] = $capability_sponsor;
        $table_data['pom_sponsor'] = $pom_sponsor;
        $table_data['ass_area'] = $ass_area;
        $table_data['approval'] = $this->page_variables[$page]['approval'];
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
            'program' => $selected_program,
            'approval' => $selected_approval
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

        $this->load->view('SOCOM/program_breakdown_view', array_merge($table_data, $data));
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
            $is_manual_changes = isset($post_data['is_manual_changes']) ? 
                filter_var($post_data['is_manual_changes'], FILTER_VALIDATE_BOOLEAN) : false;
            $approval = isset($post_data['approval']) ? [$post_data['approval']] : [];

            $get_program_summary_function = 'get_' . $page . '_program_summary';
            $response = $this->DBs->SOCOM_model->$get_program_summary_function(
                $l_pom_sponsor,
                $l_cap_sponsor,
                $l_ass_area,
                $programs,
                $is_manual_changes,
                $approval
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

    private function get_pom_sponsor_list(){
        return array_column(
            $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM'),
            'SPONSOR_CODE'
        );
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
        $program_code = isset($post_data['program_code']) ? $post_data['program_code'] : '';
        $view = isset($post_data['view']) ? $post_data['view'] : '';
        if ($program_code) {
            $program = $this->DBs->SOCOM_model->get_program_name($program_code)[0]['PROGRAM_NAME'];
        }
        else {
            $program_code = $this->SOCOM_Program_model->get_program_id($program, 'PROGRAM_CODE');
        }

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

    private function get_eoc_code_list($eoc_data) {
        $eoc_code_list = [];
        foreach($eoc_data as $eoc) {
            $eoc_code_list[] = $eoc['program_name'];
        }
        return array_unique($eoc_code_list);
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
        // $user_id = (int)$this->session->userdata("logged_in")["id"];
        // $ao_data = false;//$this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name, $page, null);
        // $ad_data = false; //$this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name, $page, null);
        // $is_ao_user = $this->SOCOM_AOAD_model->is_ao_user();
        // $is_ad_user = $this->SOCOM_AOAD_model->is_ad_user();


        // $is_super_admin = $this->SOCOM_Users_model->is_super_admin();
        // $is_group_admin = $this->SOCOM_Users_model->is_admin_user($user_id);


        // $cap_user = $this->SOCOM_Cap_User_model->get_users()[0]??[];
        // $site_user = $this->SOCOM_Site_User_model->get_user()[0]  ??[];

        // $pom_group = $site_user["GROUP"];
        // $cap_sponsor_group = $cap_user["GROUP"];


        // if( $cap_sponsor_group ){
            if(auth_aoad_role_admin() || auth_aoad_role_user()){
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
        } else{
            $ae_ao_headers = [];
        }
        
        


        // add AE AO headers
       

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

    private function group_by_program_name($summary_data, $tags, $bins, $page, $year='26', $hasDropdown = true, $filters=[]) {
        $grouped_data = [];
        $base_k = $summary_data['base_k'];
        $approval_status = $this->format_approval_by_program_name($summary_data['approval_status'] ?? []);
        $fy = [];

        foreach($base_k as $index => $values) {
            if (!count($fy) && isset($values['FISCAL_YEARS'])) {
                $fy = explode(', ', $values['FISCAL_YEARS']);
            }

            $tag_dropdown = '';
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

    private function format_approval_by_program_name($approval_status) {
        $result = [];
        foreach ($approval_status as $program) {
            $result[$program['PROGRAM_NAME']] = $program['APPROVAL_ACTION_STATUS'];
        }
        return $result;
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
        $bin_list = $this->DBs->SOCOM_model->get_user_assigned_bin_by_program($program);
        
        $jca_list = [];
        foreach($bin_list as $jca) {
            $jca_list = array_merge($jca_list, json_decode($jca['JCA'], true));
        }

        $bin_list = $this->SOCOM_COA_model->get_jca_alignment_description($jca_list);
        if (empty($bin_list)) {
            return '';
        }
        else{ 
            return implode(', <br>', array_values($bin_list));
        }
    }

    public function update_program_filter() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];

            $l_cap_sponsor = isset($post_data['cs']) ? $post_data['cs']  : [];
            $l_ass_area = isset($post_data['ass-area']) ? $post_data['ass-area'] : [];
            $l_approval_status = ['PENDING', 'COMPLETED'];
            $l_pom_sponsor = $this->get_pom_sponsor_list();

            $page = isset($post_data['page']) ? $post_data['page'] : '';

            $program_list = [];

           
            $filtered_program = $this->DBs->SOCOM_model->program_approval_status(
                $page,
                $l_pom_sponsor,
                $l_cap_sponsor,
                $l_ass_area,
                $l_approval_status,
                true
            );

            //print_r( $filtered_program);

            $unique_program_groups = [];
            $result = [];
            foreach ( $filtered_program as $item) {
                if (!in_array($item['PROGRAM_GROUP'], $unique_program_groups)) {
                    $unique_program_groups[] = $item['PROGRAM_GROUP'];
                    $result[] = ['PROGRAM_GROUP' => $item['PROGRAM_GROUP']];
                }
            }
            $program_list['data'] = $result;
            

            
            $program_list['status'] = 'OK';

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($program_list))
                ->_display();
            exit();
        }
    }

    private function get_eoc_summary_data($page, $params) {
        $fy = $page == 'zbt_summary' ?  $this->ZBT_FY : $this->ISS_FY;
        $year = $page == 'zbt_summary' ? $this->ZBT_YEAR : $this->ISS_YEAR;
        
        $eoc_data = $this->DBs->SOCOM_model->get_eoc_summary_data($page, $fy, $params);

        return [
            $year => $eoc_data
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
            $is_ao_user = auth_aoad_role_admin() || auth_aoad_role_user() ? $this->SOCOM_AOAD_model->is_ao_user() : false;
            $is_ad_user = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->SOCOM_AOAD_model->is_ad_user() : false;

            // Get all user comments by program
            $ao_comments = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_name_dropdown, 'zbt_summary', null, SOCOM_AOAD_DELETED_COMMENT) : '';
            $ad_comments = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_name_dropdown, 'zbt_summary', null, SOCOM_AOAD_DELETED_COMMENT) : '';

            // Generate 'View' buttons to open AO/AD comments modal
            $ao_text_forms = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->generate_eoc_text_form($index, 'ao', $ao_comments, $is_ao_user, $event_name_dropdown) : '';
            $ad_text_forms = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->generate_eoc_text_form($index, 'ad', $ad_comments, $is_ad_user, $event_name_dropdown) : '';

            // Generate 'View' buttons to open AO/AD modal with dropdown
            $ao_dropdown_form = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->generate_eoc_dropdown_form($index, "ao", $ass_area_code, $user_id, $ao_comments, $is_ao_user, $event_name_dropdown, 'zbt_summary') : '';
            $ad_dropdown_form = auth_aoad_role_admin() || auth_aoad_role_user()  ? $this->generate_eoc_dropdown_form($index, "ad", $ass_area_code, $user_id, $ad_comments, $is_ad_user, $event_name_dropdown, 'zbt_summary') : '';
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

    public function add_email_field($object, $ao_ad, $comments) {
        $email = $this->get_email_by_id($object, $ao_ad, $comments);
        $object['email'] = $email;
        return $object;
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

    public function update_program_breakdown_graph($page) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
    
        if ($data_check['result'])
        {
            $post_data = $data_check['post_data'];
            $selected_capability_sponsor = $post_data['cs'] ?? [];
            $selected_ass_area = $post_data['ass-area'] ?? [];
            $selected_program_group = $post_data['program'] ?? [];
            $selected_approval = $post_data['approval'] ?? '';
            $approval_filter = $selected_approval ? [$selected_approval] : [];
            $selected_pom_sponsor = $this->get_pom_sponsor_list();
            
            $categories = $this->page_variables[$page]['graph']['categories'];

            $series = [];
            $get_program_summary_function = 'get_' . $page . '_program_summary';
            $program_summary = $this->DBs->SOCOM_model->$get_program_summary_function(
                $selected_pom_sponsor,
                $selected_capability_sponsor,
                $selected_ass_area,
                $selected_program_group,
                false,
                $approval_filter
            );

            $message = '';
            $message_type = 'success';

            // check message if the endpoint returns a message
            if (!empty($program_summary) && isset($program_summary[0]['MESSAGE'])) {
                $message = $program_summary[0]['MESSAGE'];
            }
            if (isset($program_summary['message'])) {
                $message = $program_summary['message'];
            }
            if (isset($program_summary['detail'])) {
                $message = $program_summary['detail'];
                $message_type = 'error';
            }
            
            if ($message) {
                $this->output->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'message' => $message,
                        'type' => $message_type
                    ]))
                    ->_display();
                exit();
            }

            usort($program_summary, function($a, $b) {
                return $b['OVERALL_SUM'] <=> $a['OVERALL_SUM'];
            });

            $total = [
                'POSITIVE_SUM' => 0,
                'NEGATIVE_SUM' => 0,
                'OVERALL_SUM' => 0
            ];
            foreach ($program_summary as $program) {
                $positive_sum = $program['POSITIVE_SUM'] ?? null;
                $negative_sum = $program['NEGATIVE_SUM'] ?? null;
                $overall_sum = $program['OVERALL_SUM'] ?? null;
                
                $total['Positive Requested Changes'] += $positive_sum;
                $total['Negative Requested Changes'] += $negative_sum;
                $total['Total Requested Changes'] += $overall_sum;


                if ($page === 'zbt_summary') {
                    $data = [
                        [
                            'y' => $positive_sum,
                            'color' => $positive_sum >= 0 ? $this->colors['green'] : $this->colors['red']
                        ],
                        [
                            'y' => abs($negative_sum),
                            'color' => $negative_sum >= 0 ? $this->colors['green'] : $this->colors['red']
                        ],
                        [
                            'y' => abs($overall_sum),
                            'color' => $overall_sum >= 0 ? $this->colors['green'] : $this->colors['red']
                        ]
                    ];
                }
                else {
                    $data = [
                        [
                            'y' => $positive_sum,
                            'color' => $positive_sum >= 0 ? $this->colors['green'] : $this->colors['red']
                        ],
                        [
                            'y' => abs($overall_sum),
                            'color' => $overall_sum >= 0 ? $this->colors['green'] : $this->colors['red']
                        ]
                    ];
                }

                $series[] = [
                    'name' => $program['PROGRAM_NAME'] ?? 'Unknown Program',
                    'program_code' => $program['PROGRAM_CODE'] ?? 'Unknown Program code',
                    'data' => $data
                ];
            }

            $response = [
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'series' => $series,
                    'title' => 'Program Breakdown',
                    'total' =>  $total
                ]
            ];

            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->_display();
            exit();
        }
    }
}