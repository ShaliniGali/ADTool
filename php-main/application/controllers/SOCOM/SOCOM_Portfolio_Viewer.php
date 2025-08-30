<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// require_once APPPATH . 'core/BaseUserGroup_Controller.php';

#[AllowDynamicProperties]
class SOCOM_Portfolio_Viewer extends CI_Controller {
    protected const APPLICATION_JSON = 'application/json';
    
    /**
     * Optimizer constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SOCOM_Weights_model');
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_COA_model');
        $this->load->model('SOCOM_Storm_model');
        $this->load->model('SOCOM_Portfolio_Viewer_model');

        $criteria_name_id = get_criteria_name_id();
        $criteria = array_column(
            $this->SOCOM_Cycle_Management_model->get_terms_by_criteria_id($criteria_name_id),
            'CRITERIA_TERM'
        );
        $this->chart_type = [
            'FINANCIAL_EXECUTION' => [
                'PLAN AMOUNT' => 'line',
                'OBLIGATED AMOUNT' => 'column',
                'EXPEND AMOUNT' => 'column'
            ]
        ];
        $this->colors = [
            'MILESTONES' => [
                'CURRENT' => '#FFFF00',
                'PREVIOUS' => '#92d051',
                'FUTURE' => '#a12b93'
            ],
            'FINANCIAL_EXECUTION' => [
                'PLAN AMOUNT' => '#000000',
                'OBLIGATED AMOUNT' => '#ff8c00',
                'EXPEND AMOUNT' => '#8b0000'
            ]
        ];
        $this->selected_weight_columns = array_combine($criteria, $criteria);
    }

    // --------------------------------------------------------------------

	public function index()
	{
        $page_data['page_title'] = 'Portfolio Viewer';
        $page_data['page_tab'] = 'Portfolio Viewer';
        $page_data['page_navbar'] = true;
        $page_data['page_specific_css'] = [
        'select2.css',
        'carbon-light-dark-theme.css',
        'datatables.css',
        'jquery.dataTables.min.css',
        'responsive.dataTables.min.css',
        'SOCOM/socom_home.css',
        'SOCOM/portfolio_viewer.css'];
        $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
        $is_guest = $this->rbac_users->is_guest();
        $get_guest_cap_sponsor = $this->rbac_users->get_user_groups();

        $capability_categories = $this->SOCOM_Portfolio_Viewer_model->get_capability_categories();

        if ($is_guest) {
            $category = $capability_categories['mapping'][$get_guest_cap_sponsor] ?? '';
            $formatted_data = [
                $category => [$get_guest_cap_sponsor]
            ];
            $filtered_data = [
                $category => [$get_guest_cap_sponsor]
            ];
        } else {
            [$formatted_data, $filtered_data] = $this->format_capability_categories($capability_categories);
        }

        $budget_authority = array_column($this->SOCOM_Portfolio_Viewer_model->get_dropdown_data(
            'DT_BUDGET_EXECUTION', 'RESOURCE_CATEGORY_CODE', 'RESOURCE_CATEGORY_CODE', '', 'SORDAC'
        ), 'RESOURCE_CATEGORY_CODE');

        $ass_area_code = array_column($this->SOCOM_Portfolio_Viewer_model->get_dropdown_data(
            'DT_BUDGET_EXECUTION', 'ASSESSMENT_AREA_CODE', 'ASSESSMENT_AREA_CODE', '', 'SORDAC'
        ), 'ASSESSMENT_AREA_CODE');

        $cap_sponsor =[];
        foreach($formatted_data as $value) {
            $cap_sponsor = array_merge($value, $cap_sponsor);
        }
        
        $program_group_filters = [];
        if (!empty($cap_sponsor)) {
            $program_group_filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
        }

        if (!empty($budget_authority)) {
            $program_group_filters['RESOURCE_CATEGORY_CODE'] = $budget_authority;
        }

        $filtered_program_groups = $this->SOCOM_Portfolio_Viewer_model->get_dropdown_data(
            'DT_BUDGET_EXECUTION', 'PROGRAM_GROUP', 'PROGRAM_GROUP', $program_group_filters, 'SORDAC'
        );

        $program_groups = $this->SOCOM_Portfolio_Viewer_model->get_dropdown_data(
            'DT_BUDGET_EXECUTION', 'PROGRAM_GROUP', 'PROGRAM_GROUP', '', 'SORDAC'
        );

        $data = [];
        $data['budget_trend_overview'] = [
            'category_map' => $formatted_data,
            'sub_category_showing' => $capability_categories['sub_category_showing'],
            'dropdown' => [
                ...$filtered_data,
                'APPROPRIATION' => $budget_authority,
                'ASSESSMENT AREA CODE' => $ass_area_code
            ]
        ];

        if ($is_guest) {
            $data['program_groups'] = array_column($filtered_program_groups, 'PROGRAM_GROUP');
        } else{
            $data['program_groups'] = array_column($program_groups, 'PROGRAM_GROUP');
        }

        $data['filtered_program_groups'] = array_column($filtered_program_groups, 'PROGRAM_GROUP');
        $data['banner'] = '';
        $is_guest = $this->rbac_users->is_guest();
        $is_restricted = $this->rbac_users->is_restricted();
        $page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;

        $this->load->view('templates/header_view', $page_data);
		$this->load->view('SOCOM/portfolio_viewer/index_view',$data);
        $this->load->view('templates/close_view');
	}

    private function format_capability_categories($data) {
        $formatted_data = [];
        $filtered_data = [];

        foreach ($data['mapping'] as $key => $value) {
            $new_category = str_replace(' ', '_', $value);
            if (isset($formatted_data[$new_category])) {
                $formatted_data[$new_category][] = $key;
            }
            else {
                $formatted_data[$new_category] = [$key];
            }
        }

        $filtered_data = $formatted_data;
        foreach($formatted_data as $key => $value) {
            if (!in_array($key, $data['sub_category_showing'])) {
                $filtered_data[$key] = $key;
            }
        }

        return [$formatted_data, $filtered_data];
    }

    public function update_budget_trend_overview_graph() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $cap_sponsor = isset($post_data['CAPABILITY_SPONSOR_CODE']) ? $post_data['CAPABILITY_SPONSOR_CODE'] : [];
            $resource_category = isset($post_data['RESOURCE_CATEGORY']) ? $post_data['RESOURCE_CATEGORY'] : [];
            $ass_area_code = isset($post_data['ASSESSMENT_AREA_CODE']) ? $post_data['ASSESSMENT_AREA_CODE'] : [];
            $program_group = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $inflation_adj = isset($post_data['INFLATION_ADJ']) ? $post_data['INFLATION_ADJ'] : 'false';

            $filters = [];
            if (!empty($cap_sponsor)) {
                $filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
            }
            
            if (!empty($resource_category)) {
                $filters['RESOURCE_CATEGORY_CODE'] = $resource_category;
            }
            
            if (!empty($program_group)) {
                $filters['PROGRAM_GROUP'] = $program_group;
            }

            if (!empty($ass_area_code)) {
                $filters['ASSESSMENT_AREA_CODE'] = $ass_area_code;
            }
            $groupby = ['FISCAL_YEAR'];
    
            $graph_data = $this->SOCOM_Portfolio_Viewer_model->get_budget_trend_data($filters, $groupby, $inflation_adj);

            $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($graph_data))
            ->_display();
            exit();
        }
    }

    public function update_final_enacted_budget_graph() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $cap_sponsor = isset($post_data['CAPABILITY_SPONSOR_CODE']) ? $post_data['CAPABILITY_SPONSOR_CODE'] : [];
            $resource_category = isset($post_data['RESOURCE_CATEGORY']) ? $post_data['RESOURCE_CATEGORY'] : [];
            $program_group = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $ass_area_code = isset($post_data['ASSESSMENT_AREA_CODE']) ? $post_data['ASSESSMENT_AREA_CODE'] : [];
            $inflation_adj = isset($post_data['INFLATION_ADJ']) ? $post_data['INFLATION_ADJ'] : 'false';

            $filters = [];
            if (!empty($cap_sponsor)) {
                $filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
            }
            
            if (!empty($resource_category)) {
                $filters['RESOURCE_CATEGORY_CODE'] = $resource_category;
            }
            
            if (!empty($program_group)) {
                $filters['PROGRAM_GROUP'] = $program_group;
            }

            if (!empty($ass_area_code)) {
                $filters['ASSESSMENT_AREA_CODE'] = $ass_area_code;
            }
            $groupby = ['FISCAL_YEAR'];
    
            $graph_data = $this->SOCOM_Portfolio_Viewer_model->get_final_enacted_budget_data($filters, $groupby, $inflation_adj);
            // $pb_min_max_fy = $this->SOCOM_Portfolio_Viewer_model->get_min_max_fy('DT_PB_COMPARISON');
            // $exec_min_max_fy = $this->SOCOM_Portfolio_Viewer_model->get_min_max_fy('DT_BUDGET_EXECUTION');
            // $min_max_fy = [
            //     min([$pb_min_max_fy['MIN_FY'], $exec_min_max_fy['MIN_FY']]), 
            //     max([$pb_min_max_fy['MAX_FY'], $exec_min_max_fy['MAX_FY']])
            // ];
    
            $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($graph_data))
            ->_display();
            exit();
        }
    }


    public function update_funding_graph() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP']  : [];

            $filters = [];
            $filters['PROGRAM_GROUP'] = $program_groups;
            $groupby = ['FISCAL_YEAR', 'RESOURCE_CATEGORY_CODE'];

            $final_enacted_budget_data = $this->SOCOM_Portfolio_Viewer_model->get_final_enacted_budget_data(
                $filters, $groupby
            );
            $pb_years = $this->SOCOM_model->get_pb_comparison_dashed_line();
            $budget_trend_data = $this->SOCOM_Portfolio_Viewer_model->get_budget_trend_data($filters, $groupby);

            $graph_data = $this->format_program_execution_drilldown_graph_data(
                $budget_trend_data, $pb_years, $final_enacted_budget_data
            );
    
            $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($graph_data))
            ->_display();
            exit();
        }
    }

    public function update_ams_graph() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP']  : [];
            $resource_categories = isset($post_data['RESOURCE_CATEGORY_CODE']) ? $post_data['RESOURCE_CATEGORY_CODE']  : [];

            $fem_agg_data = $this->SOCOM_Portfolio_Viewer_model->get_fem_agg_data($program_groups, $resource_categories);

            $graph_data = $this->format_ams_graph_data($fem_agg_data, $program_groups);
            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($graph_data))
                ->_display();
                exit();
        }
    }

    public function get_metadata_descriptions() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP']  : [];

            $metadata_descriptions = $this->SOCOM_Portfolio_Viewer_model->get_metadata_descriptions($program_groups);

            $result = [
                'data' => $metadata_descriptions
            ];
            
            $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($result))
            ->_display();
            exit();
        }
    }


    private function format_ams_graph_data($result, $program_groups) {
        $graph_data = [
            'categories' => [],
            'data' => []
        ];
        if (!empty($program_groups) && !empty($result)) {

            $program_group = $program_groups[0];
            $data = $result[$program_group];

            $full_amount_list = [];
            $amount_list = array_values($data);

            foreach($amount_list as  $amount) {
                foreach($amount as $year => $value) {

                    if (!isset($full_amount_list[$year])) {
                        $full_amount_list[$year] = $value;
                    }
                    else {
                        $full_amount_list[$year]['PLAN AMOUNT'] += $value['PLAN AMOUNT'];
                        $full_amount_list[$year]['OBLIGATED AMOUNT'] += $value['OBLIGATED AMOUNT'];
                        $full_amount_list[$year]['EXPEND AMOUNT'] += $value['EXPEND AMOUNT'];
                    }
                }
            }

            $list_of_year = array_keys($full_amount_list);
            $start_year = min($list_of_year);
            $end_year = max($list_of_year);

            $categories = range($start_year, $end_year);
            $dollar_types = array_keys($full_amount_list[$start_year]);

            $formatted_data = [];

            foreach($dollar_types as $dollar_type) {
                $formatted_data[$dollar_type] = [];
                foreach($categories as $category) {
                    if (isset($full_amount_list[$category])) {
                        $formatted_data[$dollar_type][] = $full_amount_list[$category][$dollar_type];
                    }
                    else {
                        $formatted_data[$dollar_type][] = 0;
                    }
                }
            }


            $graph_data['categories'] = $categories;
            foreach($formatted_data as $key => $value) {
                $name = str_replace("_", " ", $key);

                $graph_data['data'][] = [
                    'name' => $name,
                    'data' => $value,
                    'color' => $this->colors['FINANCIAL_EXECUTION'][$name],
                    'type' => $this->chart_type['FINANCIAL_EXECUTION'][$name]
                ];
            }
        }
        return $graph_data;
    }

    private function format_program_execution_drilldown_graph_data(
        $budget_trend_data, $pb_years, $final_enacted_budget_data
    ) {
        $data = [];
        $series = [];
        $pb_year_map = [];
        foreach($pb_years as $pb_year) {
            $pb_year_map[$pb_year] = 'PB' . substr(strval($pb_year), -2);
        }
  
        $latest_pb_year = max($pb_years);
        $latest_pb = $pb_year_map[$latest_pb_year];
        $latest_sum_actuals = min($pb_years);

        foreach($budget_trend_data as $value) {
            if (isset($data[$value['RESOURCE_CATEGORY_CODE']])) {
                if (isset($data[$value['RESOURCE_CATEGORY_CODE']][$value['FISCAL_YEAR']])) {
                    $data[$value['RESOURCE_CATEGORY_CODE']][$value['FISCAL_YEAR']] += $value[$latest_pb];
                }
                else {
                    $data[$value['RESOURCE_CATEGORY_CODE']][$value['FISCAL_YEAR']] = $value[$latest_pb];
                }
            }
            else {
                $data[$value['RESOURCE_CATEGORY_CODE']] = [];
                $data[$value['RESOURCE_CATEGORY_CODE']][$value['FISCAL_YEAR']] = $value[$latest_pb];
            }
        }

        foreach($final_enacted_budget_data as $value) {
            $data[$value['RESOURCE_CATEGORY_CODE']][$value['FISCAL_YEAR']] += $value['SUM_ACTUALS'];
        }
        $categories = [];
        $latest_pb_year_index = 0;
        foreach($data as $resource_category => $info) {
            $temp_years = array_keys($info);
            if (count($categories) < count($temp_years)) {
                $categories = $temp_years;
                $latest_pb_year_index = array_search($latest_sum_actuals, $categories);
            }
        }
        foreach($categories as $year) {
            foreach($data as $resource_category => $info) {
                if (!isset($data[$resource_category][$year])) {
                    $data[$resource_category][$year] = null;
                }
                ksort($data[$resource_category]);
            }
        }
        foreach($pb_years as $pb_year) {
            $filtered_budget_trend_data = array_filter($budget_trend_data, function($value) use($pb_year) {
                return $value['FISCAL_YEAR'] === $pb_year;
            });
            foreach($filtered_budget_trend_data as $filtered_data) {
                $data[$filtered_data['RESOURCE_CATEGORY_CODE']][$pb_year] = $filtered_data[$pb_year_map[$pb_year]];
            }
        }

        foreach($data as $resource_category => $info) {
            $series[] = [
                'name' => $resource_category,
                'data' => array_values($info),
                'zoneAxis' => 'x',
                'zones' => [[
                    'value' =>  $latest_pb_year_index,
                    'dashStyle' => 'solid'
                ],
                [
                    'dashStyle' => 'dash'
                ]]

            ];
        }

        return [
            'categories' => $categories,
            'data' => $series,
            'latest_pb_year_index' => $latest_pb_year_index,
            'latest_pb_year' => $latest_pb_year
        ];
    }

    public function update_chart_data_amount() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $cap_sponsor = isset($post_data['CAPABILITY_SPONSOR_CODE']) ? $post_data['CAPABILITY_SPONSOR_CODE']  : [];
            $resource_category = isset($post_data['RESOURCE_CATEGORY']) ? $post_data['RESOURCE_CATEGORY'] : [];
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $ass_area_code = isset($post_data['ASSESSMENT_AREA_CODE']) ? $post_data['ASSESSMENT_AREA_CODE'] : [];

            $filters = [];
            $filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
            $filters['RESOURCE_CATEGORY_CODE'] = $resource_category;
            $filters['ASSESSMENT_AREA_CODE'] = $ass_area_code;
            $filters['PROGRAM_GROUP'] = $program_groups;

            $groupby_chart = ['FISCAL_YEAR', 'RESOURCE_CATEGORY_CODE'];
            $graph_data_chart = $this->SOCOM_Portfolio_Viewer_model->get_budget_trend_data($filters, $groupby_chart);
            $processed_chart = $this->processChartData($graph_data_chart, 'RESOURCE_CATEGORY_CODE');
        }
        

        if ($processed_chart) {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'data' => $processed_chart
                ]))
                ->_display();
            exit();
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'Failed to fetch data'
                ]))
                ->_display();
            exit();
        }
    }

    public function update_data_top_program() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $cap_sponsor = isset($post_data['CAPABILITY_SPONSOR_CODE']) ? $post_data['CAPABILITY_SPONSOR_CODE']  : [];
            $resource_category = isset($post_data['RESOURCE_CATEGORY']) ? $post_data['RESOURCE_CATEGORY'] : [];
            $ass_area_code = isset($post_data['ASSESSMENT_AREA_CODE']) ? $post_data['ASSESSMENT_AREA_CODE'] : [];

            $filters = [];
            $filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
            $filters['RESOURCE_CATEGORY_CODE'] = $resource_category;
            $filters['ASSESSMENT_AREA_CODE'] = $ass_area_code;

            $groupby_chart = ['FISCAL_YEAR', 'PROGRAM_GROUP'];
            $graph_data_chart = $this->SOCOM_Portfolio_Viewer_model->get_budget_trend_data($filters, $groupby_chart);
            $processed_chart = $this->processChartData($graph_data_chart, 'PROGRAM_GROUP', true);
        }
        

        if ($processed_chart) {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'data' => $processed_chart
                ]))
                ->_display();
            exit();
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'Failed to fetch data'
                ]))
                ->_display();
            exit();
        }
    }

    public function update_chart_data_selected_program(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $cap_sponsor = isset($post_data['CAPABILITY_SPONSOR_CODE']) ? $post_data['CAPABILITY_SPONSOR_CODE']  : [];
            $resource_category = isset($post_data['RESOURCE_CATEGORY']) ? $post_data['RESOURCE_CATEGORY'] : [];
            $program_group = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $ass_area_code = isset($post_data['ASSESSMENT_AREA_CODE']) ? $post_data['ASSESSMENT_AREA_CODE'] : [];


            $filters = [];
            $filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
            $filters['RESOURCE_CATEGORY_CODE'] = $resource_category;
            $filters['ASSESSMENT_AREA_CODE'] = $ass_area_code;
            $filters['PROGRAM_GROUP'] = $program_group;

            $groupby_chart3 = ['FISCAL_YEAR', 'PROGRAM_GROUP'];

            $graph_data_chart3 = $this->SOCOM_Portfolio_Viewer_model->get_budget_trend_data($filters, $groupby_chart3);

            $processed_chart3 = $this->processChartData($graph_data_chart3, 'PROGRAM_GROUP');
        }
        if ($processed_chart3) {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'data' => ['chart3' => $processed_chart3 ]
                ]))
                ->_display();
            exit();
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'Failed to fetch data'
                ]))
                ->_display();
            exit();
        }
    }

    private function processChartData($graph_data, $stacking_field, $limit = false) {
        
        if (empty($graph_data)) {
            return ['categories' => [], 'series' => []];
        }
        //get latest pb year
        $pb_keys = array_keys($graph_data[0]);
        $pb_years = array_filter($pb_keys, function ($key) {
            return strpos($key, 'PB') === 0;
        });
        rsort($pb_years);
        $latest_pb = reset($pb_years);

        //pb+4 years
        $fiscal_years = [];
        foreach ($graph_data as $row) {
            $fiscal_years[] = (int) $row['FISCAL_YEAR'];
        }
        $min_fy = max($fiscal_years) - 4;
        $valid_fiscal_years = range($min_fy, max($fiscal_years));

        //vales per fiscal yr and stacking field
        $aggregated_data = [];
        foreach ($graph_data as $row) {
            $fy = (int) $row['FISCAL_YEAR'];
            $category = $row[$stacking_field];
            $value = isset($row[$latest_pb]) ? (float) $row[$latest_pb] : 0;
            if (!in_array($fy, $valid_fiscal_years)) {
                continue;
            }
            if (!isset($aggregated_data[$category])) {
                $aggregated_data[$category] = array_fill_keys($valid_fiscal_years, 0);
            }
            $aggregated_data[$category][$fy] += $value;
        }

        //sorting in descending order
        uasort($aggregated_data, function ($a, $b) {
            return array_sum($b) - array_sum($a);
        });

        // If there are more than 10 graphs then show only top 10
        if ($limit) {
            $aggregated_data = array_slice($aggregated_data, 0, 10, true);
        }

        //format for highcharts
        $categories = array_keys($aggregated_data);
        $series = [];
        sort($valid_fiscal_years);
        foreach ($valid_fiscal_years as $fy) {
            $data = [];
            foreach ($categories as $category) {
                $data[] = $aggregated_data[$category][$fy] ?? 0;
            }
            $series[] = ['name' => $fy, 'data' => $data];
        }

        return [
            'categories' => $categories,
            'series' => $series,
        ];
    }

    public function get_program_group_dropdown() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $cap_sponsor = isset($post_data['CAPABILITY_SPONSOR_CODE']) ? $post_data['CAPABILITY_SPONSOR_CODE']  : [];
            $resource_category = isset($post_data['RESOURCE_CATEGORY']) ? $post_data['RESOURCE_CATEGORY'] : [];
            $ass_area_code = isset($post_data['ASSESSMENT_AREA_CODE']) ? $post_data['ASSESSMENT_AREA_CODE'] : [];

            $filters = [];

            if (!empty($cap_sponsor)) {
                $filters['CAPABILITY_SPONSOR_CODE'] = $cap_sponsor;
            }

            if (!empty($resource_category)) {
                $filters['RESOURCE_CATEGORY_CODE'] = $resource_category;
            }

            if (!empty($ass_area_code)) {
                $filters['ASSESSMENT_AREA_CODE'] = $ass_area_code;
            }

            $program_groups = array_column($this->SOCOM_Portfolio_Viewer_model->get_dropdown_data(
                'DT_PB_COMPARISON', 'PROGRAM_GROUP', 'PROGRAM_GROUP',  $filters
            ), 'PROGRAM_GROUP');

    
            $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($program_groups))
            ->_display();
            exit();
        }
    }

    public function get_funding_dropdown() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); // Validate input
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];

            $filters = [];
            $filters['PROGRAM_GROUP'] = $program_groups[0];


            $resource_category = array_column($this->SOCOM_Portfolio_Viewer_model->get_funding_resource_category(
                'DT_BUDGET_EXECUTION', 'RESOURCE_CATEGORY_CODE', 'RESOURCE_CATEGORY_CODE', $filters
            ), 'RESOURCE_CATEGORY_CODE');
    
            $result = [
                'success' => true,
                'dropdowns' => $resource_category
            ];
         
            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($result))
                ->_display();
            exit();
        } else {
            $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Invalid input']))
                ->_display();
            exit();
        }
    }

    public function get_fielding_dropdown() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); // Validate input
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $is_guest = $this->rbac_users->is_guest();
            $get_guest_cap_sponsor = $this->rbac_users->get_user_groups();

            $params = [];
            $params['PROGRAM_GROUP'] = $program_groups[0];
            $fiscal_years = $this->SOCOM_Portfolio_Viewer_model->get_ams_dropdown_data($params, 'PLAN_FISCAL_YEAR');
            $valid_years = [];

            foreach ($fiscal_years as $year) {
                $params['FISCAL_YEAR'] = $year;
                $params['FIEDLING_TYPES'] = ['Fielding'];
                $data = $this->SOCOM_Portfolio_Viewer_model->get_fielding_data($params);
                if (!empty($data)) {
                    $valid_years[] = $year;
                }
            }
    
            if (!empty($valid_years)) {
                $selected_year = min($valid_years);
                $params['FISCAL_YEAR'] = $selected_year;
                if ($is_guest) {
                    $components = [$get_guest_cap_sponsor];
                }
                else {
                    $components = $this->SOCOM_Portfolio_Viewer_model->get_ams_dropdown_data($params, 'COMPONENT');
                }
                $result = [
                    'success' => true,
                    'dropdowns' => [
                        'fiscal_years' => $valid_years,
                        'components' => $components,
                        'selected_year' => $selected_year
                    ]
                ];
            }
            else {
                $result = [
                    'success' => true,
                    'message' => 'No data found',
                    'dropdowns' => []
                ];
            }
            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($result))
                ->_display();
            exit();
        } else {
            $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Invalid input']))
                ->_display();
            exit();
        }
    }

    public function get_fielding_component_dropdown() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); // Validate input
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $fiscal_year = !empty($post_data['FISCAL_YEAR']) ? $post_data['FISCAL_YEAR'] : '';
            $is_guest = $this->rbac_users->is_guest();
            $get_guest_cap_sponsor = $this->rbac_users->get_user_groups();
            
            $params = [];
            $params['PROGRAM_GROUP'] = $program_groups[0];
            $params['FISCAL_YEAR'] = $fiscal_year;
            if ($is_guest) {
                $components = [$get_guest_cap_sponsor];
            }
            else {
                $components = $this->SOCOM_Portfolio_Viewer_model->get_ams_dropdown_data($params, 'COMPONENT');
            }

            $result = [
                'success' => true,
                'dropdowns' => [
                    'components' => $components
                ]
            ];
            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($result))
                ->_display();
            exit();
        } else {
            $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Invalid input']))
                ->_display();
            exit();
        }
    }

    public function get_fielding_data() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); // Validate input
        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $fiscal_year = !empty($post_data['FISCAL_YEAR']) ? $post_data['FISCAL_YEAR'] : '';
            $component = isset($post_data['COMPONENT']) ? $post_data['COMPONENT'] : [];
            $fielding_item = isset($post_data['FIELDING_ITEM']) ? $post_data['FIELDING_ITEM'] : "";
            $is_guest = $this->rbac_users->is_guest();
            $get_guest_cap_sponsor = $this->rbac_users->get_user_groups();

            if ($is_guest) {
                $guest_components = [$get_guest_cap_sponsor];
                if (empty($component)) {
                    $component = $guest_components;
                }
                else {
                    $component = array_filter($component, function ($value) use($guest_components)  {
                        return in_array($value, $guest_components);
                    });
                }
            }
   
            $params = [];
            $params['PROGRAM_GROUP'] = $program_groups[0];
            $params['FISCAL_YEAR'] = $fiscal_year;
            $params['COMPONENT'] = $component;
            $params['FIELDING_ITEM'] = $fielding_item;
            $params['FIEDLING_TYPES'] = ['Fielding'];

            $fielding_data = $this->SOCOM_Portfolio_Viewer_model->get_fielding_data($params);
            $cumulative_data = [];
            if ($fielding_item !== "") {
                $params['FIEDLING_TYPES'] = ['Funding'];
                $funding_data = $this->SOCOM_Portfolio_Viewer_model->get_fielding_data($params);

                $params['FIEDLING_TYPES'] = ['Delivery'];
                $delivery_data = $this->SOCOM_Portfolio_Viewer_model->get_fielding_data($params);
                
                $cumulative_data = [
                    'funding' => $funding_data,
                    'fielding' => $fielding_data,
                    'delivery' => $delivery_data
                ];
            }
            
            $result = [
                'success' => true,
                'data' => [
                    'planned_actual_data' => $fielding_data,
                    'cumulative_data' => $cumulative_data
                ]
            ];
            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($result))
                ->_display();
            exit();
        } else {
            $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Invalid input']))
                ->_display();
            exit();
        }
    }

    public function update_ams_budgets_table() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
         

            $table_data = $this->SOCOM_Portfolio_Viewer_model->update_ams_budgets_table($program_groups);
            $result = [
                'status' => 'success',
                'data' => $table_data
            ];

            $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($result))
            ->_display();
            exit();
        }
    }

    public function get_milestone_data() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $program_groups = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : [];
            $program = isset($post_data['PROGRAM']) ? $post_data['PROGRAM'] : "";

            // get the program selections
            if (!empty($program_groups)) {
                $program_group = $program_groups[0];
            }

            if ($program == "") {
                $table_data = $this->SOCOM_Portfolio_Viewer_model->get_milestone_data($program_group);
                $program_selections = $table_data['ALL_PROGRAM_FULLNAME'];
                if (!empty($program_selections) && $program == "") {
                    $program = $program_selections[0];
                }
            }

            $milestone_data = $this->SOCOM_Portfolio_Viewer_model->get_milestone_data($program_group, [$program]);
            $page_data['procurement_strategy'] = $this->get_milestone_procurement_strategy($program, $milestone_data);
            $page_data['selected_program'] =  $program;
            $page_data['program_group'] =  $program_group;
            $page_data['program_selections'] =  $program_selections;
            $page_data['milestone_data'] = [
                'current' => [
                    'title' => 'Current Milestone',
                    'data' => $milestone_data['Current Milestone'],
                    'fill' => $this->colors['MILESTONES']['CURRENT']
                ],
                'previous' => [
                    'title' => 'Previous Milestone',
                    'data' => $milestone_data['Previous Milestone'],
                    'fill' => $this->colors['MILESTONES']['PREVIOUS']
                ],
                'future' => [
                    'title' => 'Future Milestone',
                    'data' => $milestone_data['Future Milestone'],
                    'fill' => $this->colors['MILESTONES']['FUTURE']
                ]
            ];
            $this->load->view('SOCOM/portfolio_viewer/milestones_view', $page_data);
        }
        else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'Failed to fetch data'
                ]))
                ->_display();
            exit();
        }
    }

    private function get_milestone_procurement_strategy($program, $milestone_data) {
        foreach($milestone_data as $key => $value) {
            if (strpos($key, 'Milestone') !== false) {
                foreach($value as $info) {
                    return $info['PROC_STRATEGY'];
                }
            }
        }
        return '';
    }

    public function update_milestone_data() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
           
            $program_group = isset($post_data['PROGRAM_GROUP']) ? $post_data['PROGRAM_GROUP'] : "";
            $program = isset($post_data['PROGRAM']) ? $post_data['PROGRAM'] : "";

            $milestone_data = $this->SOCOM_Portfolio_Viewer_model->get_milestone_data($program_group, [$program]);
            $page_data['procurement_strategy'] = $this->get_milestone_procurement_strategy($program, $milestone_data);
            $page_data['selected_program'] =  $program;
            $page_data['program_group'] =  $program_group;
            $page_data['milestone_data'] = [
                'current' => [
                    'title' => 'Current Milestone',
                    'data' => $milestone_data['Current Milestone'],
                    'fill' => $this->colors['MILESTONES']['CURRENT']
                ],
                'previous' => [
                    'title' => 'Previous Milestone',
                    'data' => $milestone_data['Previous Milestone'],
                    'fill' => $this->colors['MILESTONES']['PREVIOUS']
                ],
                'future' => [
                    'title' => 'Future Milestone',
                    'data' => $milestone_data['Future Milestone'],
                    'fill' => $this->colors['MILESTONES']['FUTURE']
                ]
            ];
            $this->load->view('SOCOM/portfolio_viewer/milestones_table_view', $page_data);
        }
        else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'Failed to fetch data'
                ]))
                ->_display();
            exit();
        }
    }

    public function get_requirements_data() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if ($data_check['result']) {
            $post_data = $data_check['post_data'];
            
            $api_params = [];
            $api_params['PXID'] = isset($post_data['PXID']) ? $post_data['PXID'] : "";
            $api_params['MILESTONE'] = isset($post_data['MILESTONE']) ? $post_data['MILESTONE'] : "";
            $api_params['MILESTONE_STATUS'] = isset($post_data['MILESTONE_STATUS']) ? $post_data['MILESTONE_STATUS'] : "";

            $result = $this->SOCOM_Portfolio_Viewer_model->get_requirements_data($api_params);
            $this->output->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($result))
                ->_display();
            exit();
        }
    }
}
