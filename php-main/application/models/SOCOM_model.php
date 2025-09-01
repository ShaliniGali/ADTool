<?php

#[AllowDynamicProperties]
class  SOCOM_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->usedHues = [];
        $this->colors = [];
        $this->load->library('SOCOM/Dynamic_Year');
        
        // Initialize dynamic year library
        $this->dynamic_year->setFromCurrentYear();

        $this->ZBT_YEAR = $this->dynamic_year->getPomYearForSubapp('ZBT_SUMMARY_YEAR');
        $this->ZBT_FY = $this->ZBT_YEAR % 100;
        $this->ZBT_YEAR_LIST = $this->dynamic_year->getYearList($this->ZBT_YEAR);

        $this->ISS_YEAR = $this->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');
        $this->ISS_FY = $this->ISS_YEAR % 100;
        $this->ISS_YEAR_LIST = $this->dynamic_year->getYearList($this->ISS_YEAR);

        $this->COA_YEAR = $this->dynamic_year->getPomYearForSubapp('RESOURCE_CONSTRAINED_COA_YEAR');
        $this->COA_FY = $this->COA_YEAR % 100;
        $this->COA_YEAR_LIST = $this->dynamic_year->getYearList($this->COA_YEAR);

        $this->page_variables = [
            'zbt_summary' => [
                'subapp' => 'ZBT_SUMMARY',
                'type' => [
                    'EXT' => 'EXT',
                    'EXTRACT' => 'ZBT_EXTRACT',
                    'ZBT' => 'ZBT',
                    'ISS' => 'ISS',
                    'POM' => 'POM'
                ],
                'year' => $this->ZBT_YEAR,
                'fy' => $this->ZBT_FY,
                'year_list' => $this->ZBT_YEAR_LIST
            ],
            'issue' => [
                'subapp' => 'ISS_SUMMARY',
                'type' => [
                    'EXT' => 'EXT',
                    'EXTRACT' => 'ISS_EXTRACT',
                    'ZBT' => 'ZBT',
                    'ISS' => 'ISS',
                    'POM' => 'POM'
                ],
                'year' => $this->ISS_YEAR,
                'fy' => $this->ISS_FY,
                'year_list' => $this->ISS_YEAR_LIST
            ],
            'coa' => [
                'subapp' => 'RESOURCE_CONSTRAINED_COA',
                'type' => [
                    'ISS' => 'ISS',
                    'EXTRACT' => 'ISS_EXTRACT',
                ],
                'year' => $this->COA_YEAR,
                'fy' => $this->COA_FY,
                'year_list' => $this->COA_YEAR_LIST
            ],
            'pb_comparison' => [
                'subapp' => 'DT_PB_COMPARISON',
                'type' => [
                    'EXTRACT' => 'DT_PB_COMPARISON',
                ],
                'year' => $this->ZBT_YEAR,
                'fy' => $this->ZBT_FY,
                'year_list' => $this->ZBT_YEAR_LIST
            ]
        ];
        
        // Debug output for development
        if (is_dev_bypass_enabled()) {
            error_log("DEBUG: SOCOM_model constructor called, coa subapp: " . $this->page_variables['coa']['subapp']);
        }
    }

    public function cap_sponsor_count($page) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );
        $inner_query = $this->cap_sponsor_count_inner_query(
            $table,
            "IF(CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC', 'SOF AT&L',
            CAPABILITY_SPONSOR_CODE) as CAP,
            COUNT(DISTINCT(EVENT_NAME)) as COUNT"
        );
        $cap_sponsor_count = $this->DBs->SOCOM_UI->query($inner_query)->result_array();
        $this->DBs->SOCOM_UI->select('
            SUM(A.COUNT) AS TOTAL_EVENTS
        ');
        $this->DBs->SOCOM_UI->from('('. $inner_query . ')  as A');
        $total_zbt_events_result = $this->DBs->SOCOM_UI->get()->row_array();
        $total_zbt_events = isset($total_zbt_events_result['TOTAL_EVENTS'])
            ? $total_zbt_events_result['TOTAL_EVENTS'] : 0;

        return [
            'cap_sponsor_count' => $this->format_cap_sponsor_pie_chart(
                $cap_sponsor_count,
                'CAP',
                'COUNT'
            ),
            'total_events' => $total_zbt_events
        ];

    }

    public function cap_sponsor_dollar($page) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );
        $this->DBs->SOCOM_UI->select("
            IF(CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC', 'SOF AT&L', CAPABILITY_SPONSOR_CODE) 
            as CAP,
            SUM(DELTA_AMT) AS SUM_DELTA_AMT
        ");
        $this->DBs->SOCOM_UI->from($table);
        $this->DBs->SOCOM_UI->where('DELTA_AMT > ', 0);
        $this->DBs->SOCOM_UI->group_by('CAP');
        $this->DBs->SOCOM_UI->order_by('CAP');
        $inner_query = $this->DBs->SOCOM_UI->get_compiled_select();
        $cap_sponsor_dollar = $this->DBs->SOCOM_UI->query($inner_query)->result_array();
        
        // Get dollars_moved
        $this->DBs->SOCOM_UI->select('
            SUM(DELTA_AMT) AS SUM_DELTA_AMT
        ');
        $this->DBs->SOCOM_UI->from($table);
        $this->DBs->SOCOM_UI->where('DELTA_AMT > ', 0);
        $inner_query = $this->DBs->SOCOM_UI->get_compiled_select();
        $this->DBs->SOCOM_UI->select('
            SUM(B.SUM_DELTA_AMT) AS TOTAL_POS_DOLLARS
        ');
        $this->DBs->SOCOM_UI->from('('. $inner_query . ')  as B');
        $dollars_moved_result = $this->DBs->SOCOM_UI->get()->row_array();
        $dollars_moved = isset($dollars_moved_result['TOTAL_POS_DOLLARS'])
            ?
            $this->format_dollar($dollars_moved_result['TOTAL_POS_DOLLARS'])
            :
            0;
        return [
            'cap_sponsor_dollar' => $this->format_cap_sponsor_pie_chart(
                $cap_sponsor_dollar,
                'CAP',
                'SUM_DELTA_AMT'
            ),
            'dollars_moved' => $dollars_moved
        ];
    }

    public function net_change($page) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        $this->DBs->SOCOM_UI->select('
            SUM(DELTA_AMT) AS SUM_DELTA_AMT
        ');
        $this->DBs->SOCOM_UI->from($table);
        $net_change_result = $this->DBs->SOCOM_UI->get()->row_array();
        return isset($net_change_result['SUM_DELTA_AMT'])
            ?
            $this->format_dollar(
                $net_change_result['SUM_DELTA_AMT']
            )
            :
            0;
    }

    private function format_cap_sponsor_pie_chart($data, $key1, $key2) {
        $result = [];
        $color = null;
        foreach($data as $value) {
            if (isset($this->colors[$value[$key1]])) {
                $color = $this->colors[$value[$key1]];
            } else {
                $color = $this->generate_unique_random_colors($value[$key1]);
            }
            $result[] = [
                'name' => $value[$key1],
                'y' => intval($value[$key2]), 
                'color' => $color
            ];
        }   
        return $result;
    }

    private function generate_unique_random_colors($key) {    
        do {
            $hue = mt_rand(0, 360);
        } while (in_array($hue, $this->usedHues));

        $this->usedHues[] = $hue;

        $saturation = mt_rand(50, 100);
        $lightness = mt_rand(40, 80);

        $this->colors[$key] = $this->hsl_to_hex($hue, $saturation, $lightness);
        
        return $this->colors[$key];
    }
    
    private function hsl_to_hex($hue, $saturation, $lightness) {
        $saturation /= 100;
        $lightness /= 100;
    
        $c = (1 - abs(2 * $lightness - 1)) * $saturation;
        $x = $c * (1 - abs(fmod($hue / 60, 2) - 1));
        $m = $lightness - $c / 2;
    
        if ($hue < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($hue < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($hue < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($hue < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($hue < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }
    
        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);
    
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
    
    private function format_dollar($number) {
        $number = (float) $number;
        $isNegative = $number < 0;
        $number = abs($number);
        $abbreviations = array(
            12 => 'T',
            9  => 'B',
            6  => 'M',
            3  => 'K',
        );
        foreach ($abbreviations as $magnitude => $abbreviation) {
            if ($number >= 10**$magnitude) {
                $formatted = number_format($number / 10**$magnitude, 1) . $abbreviation;
                $formatted = rtrim($formatted, '.0');
                return ($isNegative ? '-' : '') . $formatted;
            }
        }
        return ($isNegative ? '-' : '') . number_format($number);
    }

    private function cap_sponsor_count_inner_query($table, $columns) {
        $this->DBs->SOCOM_UI->select($columns);
        $this->DBs->SOCOM_UI->from($table);
        $this->DBs->SOCOM_UI->group_by('CAP');
        $this->DBs->SOCOM_UI->order_by('CAP');
        return $this->DBs->SOCOM_UI->get_compiled_select();
    }

    public function dollars_moved_resource_category_cross_join($page) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        $sql = <<<EOT
        SELECT A.FISCAL_YEAR, B.RESOURCE_CATEGORY_CODE, COALESCE(C.SUM_DELTA_AMT, 0) AS SUM_DELTA_AMT
        FROM (SELECT DISTINCT FISCAL_YEAR FROM {$table}) AS A
        
        CROSS JOIN
        
        (SELECT DISTINCT RESOURCE_CATEGORY_CODE FROM {$table}) AS B
        
        LEFT JOIN (SELECT
        FISCAL_YEAR,
        RESOURCE_CATEGORY_CODE,
        SUM(DELTA_AMT) AS SUM_DELTA_AMT
        FROM {$table}
        WHERE DELTA_AMT > 0
        GROUP BY FISCAL_YEAR, RESOURCE_CATEGORY_CODE) AS C
        ON A.FISCAL_YEAR = C.FISCAL_YEAR
        AND B.RESOURCE_CATEGORY_CODE = C.RESOURCE_CATEGORY_CODE
        
        GROUP BY A.FISCAL_YEAR, B.RESOURCE_CATEGORY_CODE
        ORDER BY B.RESOURCE_CATEGORY_CODE, A.FISCAL_YEAR
EOT;

        $result = $this->DBs->SOCOM_UI->query($sql)->result_array();

        return $this->format_resource_series_graph($result);
    }

    public function dollars_moved_resource_category($page, $filter=true) {
        $inner_query = '';
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        if ($filter) {
            $inner_query = $this->cap_sponsor_count_inner_query(
                $table,
                'CAPABILITY_SPONSOR_CODE as CAP'
            );
        }
        $this->DBs->SOCOM_UI->select('
            a.FISCAL_YEAR as FISCAL_YEAR,
            a.RESOURCE_CATEGORY_CODE as RESOURCE_CATEGORY_CODE,
            SUM(a.DELTA_AMT) AS SUM_DELTA_AMT
        ');
        $this->DBs->SOCOM_UI->from("$table as a");
        $this->DBs->SOCOM_UI->where('a.DELTA_AMT > ', 0);
        if ($filter) {
            $this->DBs->SOCOM_UI->where_in(
                'CAPABILITY_SPONSOR_CODE',
                $inner_query,
                false
            );
        }
        $this->DBs->SOCOM_UI->group_by('a.FISCAL_YEAR,a.RESOURCE_CATEGORY_CODE');
        $this->DBs->SOCOM_UI->order_by('a.RESOURCE_CATEGORY_CODE,a.FISCAL_YEAR');
        $result = $this->DBs->SOCOM_UI->get()->result_array();
        return $this->format_resource_series_graph($result);
    }
    public function cap_sponsor_approve_reject($page) {
        $result = [];
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );
        if ($page == 'zbt_summary') {
            $result = $this->cap_sponsor_approve_reject_zbt_summary($table);
            $result = $this->default_count_to_zero_for_missing_event_status($result);
        }
        if ($page == 'issue') {
            $result = $this->cap_sponsor_approve_reject_issue_summary($table);
            $result = $this->default_count_to_zero_for_missing_event_status($result);
        }
        return $this->format_approve_reject_series_graph($result);
    }

    public function cap_sponsor_approve_reject_zbt_summary($table) {
        $this->DBs->SOCOM_UI->select("
        COUNT(DISTINCT(A.EVENT_NAME)) AS EVENT_COUNT,
        IF(A.CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC', 'SOF AT&L', A.CAPABILITY_SPONSOR_CODE) AS CAPABILITY_SPONSOR_CODE,
        COALESCE(B.AD_RECOMENDATION, 'Not Decided') AS EVENT_STATUS
        ");
        $this->DBs->SOCOM_UI->from("$table AS A");
        $this->DBs->SOCOM_UI->join(
            "(SELECT IF(AD_RECOMENDATION REGEXP 'at scale$', 'Approve at Scale', AD_RECOMENDATION) 
                AS AD_RECOMENDATION, 
                EVENT_NAME 
                FROM USR_ZBT_AD_FINAL_SAVES
                WHERE IS_DELETED = 0
            ) AS B",
            "A.EVENT_NAME = B.EVENT_NAME",
            "left"
        );
        $this->DBs->SOCOM_UI->group_by("IF(
            A.CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC',
            'SOF AT&L',
            A.CAPABILITY_SPONSOR_CODE
            ), B.AD_RECOMENDATION");
        $this->DBs->SOCOM_UI->order_by("A.CAPABILITY_SPONSOR_CODE");
        
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function cap_sponsor_approve_reject_issue_summary($table) {
        $this->DBs->SOCOM_UI->select("
            COUNT(DISTINCT(A.EVENT_NAME)) AS EVENT_COUNT,
            IF(A.CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC', 'SOF AT&L', A.CAPABILITY_SPONSOR_CODE) AS CAPABILITY_SPONSOR_CODE,
            COALESCE(B.AD_RECOMENDATION, 'Not Decided') AS EVENT_STATUS
        ");
        $this->DBs->SOCOM_UI->from("$table AS A");
        $this->DBs->SOCOM_UI->join(
            "(SELECT IF(AD_RECOMENDATION REGEXP 'at scale$', 'Approve at Scale', AD_RECOMENDATION) 
                AS AD_RECOMENDATION, 
                EVENT_NAME 
                FROM USR_ISSUE_AD_FINAL_SAVES
                WHERE IS_DELETED = 0
            ) AS B",
            "A.EVENT_NAME = B.EVENT_NAME",
            "left"
        );
        $this->DBs->SOCOM_UI->group_by("IF(
            A.CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC',
            'SOF AT&L',
            A.CAPABILITY_SPONSOR_CODE
            ), B.AD_RECOMENDATION");
        $this->DBs->SOCOM_UI->order_by("A.CAPABILITY_SPONSOR_CODE");
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    private function format_resource_series_graph($data) {
        $fiscal_years = array_unique(array_column($data, 'FISCAL_YEAR'));
        $series_data = [];
        foreach($data as $graph_point) {
            $resource_category_code = $graph_point['RESOURCE_CATEGORY_CODE'];
            $sum_delta_amt = floatval($graph_point['SUM_DELTA_AMT']);
            if (!isset($series_data[$resource_category_code])) {
                $series_data[$resource_category_code] = [
                    'name' => $resource_category_code,
                    'data' => [],
                ];
            }
            $series_data[$resource_category_code]['data'][] = $sum_delta_amt;
        }
        return [
            'fiscal_years' => $fiscal_years,
            'series_data' => array_values($series_data)
        ];
    }

    private function format_approve_reject_series_graph($data) {
        $categories = [];
        $series_data = [];
        foreach($data as $graph_point) {
            $cap_sponsor_code = $graph_point['CAPABILITY_SPONSOR_CODE'];
            $event_status = ucfirst($graph_point['EVENT_STATUS']);
            $event_count = (int) $graph_point['EVENT_COUNT'];

            if (!in_array($cap_sponsor_code, $categories)) {
                $categories[] = $cap_sponsor_code;
            }

            $series_index = array_search(
                $event_status,
                array_column($series_data, 'name')
            );

            if ($series_index === false) {
                $series_data[] = [
                    'name' => $event_status,
                    'data' => [],
                ];
                $series_index = count($series_data) - 1;
            }
            $series_data[$series_index]['data'][] = $event_count;
        }
        return [
            'categories' => $categories,
            'series_data' => $series_data
        ];
    }

    public function default_count_to_zero_for_missing_event_status($data) {
        $event_statuses = ["Not Decided", "Approve", "Approve at Scale", "Disapprove"];

        $grouped_data = [];
        foreach ($data as $entry) {
            $grouped_data[$entry['CAPABILITY_SPONSOR_CODE']][] = $entry;
        }
        
        foreach ($grouped_data as $cap_sponsor_code => $events) {
            $existing_statuses = array_map(function($event) {
                return $event['EVENT_STATUS'];
            }, $events);
            
            // Check for each possible status and if it's missing, add it with 0 count
            foreach ($event_statuses as $status) {
                if (!in_array($status, $existing_statuses)) {
                    $grouped_data[$cap_sponsor_code][] = [
                        'EVENT_COUNT' => 0,
                        'CAPABILITY_SPONSOR_CODE' => $cap_sponsor_code,
                        'EVENT_STATUS' => $status
                    ];
                }
            }
        }
        
        $updated_data = [];
        foreach ($grouped_data as $cap_sponsor_code => $events) {
            foreach ($events as $event) {
                $updated_data[] = $event;
            }
        }

        return $updated_data;
    }

    public function get_sponsor($table, $sponsor_type) {
        static $decoded_token = null;

        if ($this->rbac_users->is_guest()){
            if ($decoded_token === null) {
                $decoded_token = $this->userauthorization->get_decoded_token();
            }
            return [['SPONSOR_CODE' => $decoded_token->cap_group[0], 'SPONSOR_TITLE' => $decoded_token->cap_group[0]]];
        }

        $this->DBs->SOCOM_UI->select('
            SPONSOR_CODE,
            SPONSOR_TITLE
        ');
        $this->DBs->SOCOM_UI->from($table);
        $this->DBs->SOCOM_UI->where('SPONSOR_TYPE', $sponsor_type);
        $this->DBs->SOCOM_UI->order_by('SPONSOR_TITLE');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_sponsor_program_breakdown($table, $sponsor_type) {

        $this->DBs->SOCOM_UI->select('
            SPONSOR_CODE,
            SPONSOR_TITLE
        ');
        $this->DBs->SOCOM_UI->from($table);
        $this->DBs->SOCOM_UI->where('SPONSOR_TYPE', $sponsor_type);
        $this->DBs->SOCOM_UI->order_by('SPONSOR_TITLE');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_assessment_area_code() {
        $this->DBs->SOCOM_UI->select('
            ASSESSMENT_AREA_CODE,
            ASSESSMENT_AREA
        ');
        $this->DBs->SOCOM_UI->from('LOOKUP_ASSESSMENT_AREA');
        $this->DBs->SOCOM_UI->order_by('ASSESSMENT_AREA');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_resource_category_code() {
        $this->DBs->SOCOM_UI->select('
            RESOURCE_CATEGORY_CODE,
            RESOURCE_CATEGORY
        ');
        $this->DBs->SOCOM_UI->from('LOOKUP_RESOURCE_CATEGORY');
        $this->DBs->SOCOM_UI->order_by('RESOURCE_CATEGORY');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function zbt_summary_program_summary_card(
        $page, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list
    ) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );
        return [
            'total_events' => $this->program_summary_count(
                $table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, 'ZBT', $program_list
            ),
            'dollars_moved' => $this->program_summary_dollars_moved(
                $table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list
            ),
            'net_change' =>  $this->program_summary_net_change(
                $table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list
            )
        ];
    }


    public function get_user_assigned_tag($table) {
        $this->DBs->SOCOM_UI->select('TAG,TAG_TITLE');
        $this->DBs->SOCOM_UI->from($table);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_user_assigned_bin($table) {
        $this->DBs->SOCOM_UI->select('JCA_LV1_ID, JCA_LV1');
        $this->DBs->SOCOM_UI->from($table);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }
/*
    public function get_eoc($program_code) {
        $this->DBs->SOCOM_UI->select('B.EOC_CODE')
        ->distinct()
        ->from('LOOKUP_PROGRAM_DETAIL A')
        ->join('DT_ISS_2026 B', '  A.PROGRAM_CODE = B.PROGRAM_CODE', 'left join')
        ->where('PROGRAM_NAME', $program_code);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }
*/
    public function get_zbt_summary_eoc($program_code, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['ZBT']
        );
        
        $sub_query1 = "
            SELECT 
                * 
            FROM 
                {$table}
            WHERE 
                CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
                AND POM_SPONSOR_CODE IN('" . implode( "','", $l_pom_sponsor) . "')
                AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
        ";

        $table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXTRACT']
        );

        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXT']
        );

        $sub_query2 = "
            SELECT
                0 AS ADJUSTMENT_K,
                ASSESSMENT_AREA_CODE,
                0 AS BASE_K,
                BUDGET_ACTIVITY_CODE,
                BUDGET_ACTIVITY_NAME,
                BUDGET_SUB_ACTIVITY_CODE,
                BUDGET_SUB_ACTIVITY_NAME,
                CAPABILITY_SPONSOR_CODE,
                0 AS END_STRENGTH,
                EOC_CODE,
                EVENT_JUSTIFICATION,
                EVENT_NAME,
                EXECUTION_MANAGER_CODE,
                FISCAL_YEAR,
                LINE_ITEM_CODE,
                0 AS OCO_OTHD_ADJUSTMENT_K,
                0 AS OCO_OTHD_K,
                0 AS OCO_TO_BASE_K,
                OSD_PROGRAM_ELEMENT_CODE,
                POM_POSITION_CODE, 
                POM_SPONSOR_CODE,
                PROGRAM_CODE,
                PROGRAM_GROUP,
                RDTE_PROJECT_CODE,
                RESOURCE_CATEGORY_CODE,
                RESOURCE_K, 
                SPECIAL_PROJECT_CODE,
                SUB_ACTIVITY_GROUP_CODE,
                SUB_ACTIVITY_GROUP_NAME,
                2024 AS WORK_YEARS
            FROM
                {$table}
            WHERE
                (
                    PROGRAM_CODE NOT IN (
                        SELECT
                            DISTINCT PROGRAM_CODE
                        FROM
                            {$ext_table}
                    )
                    OR EOC_CODE NOT IN (
                        SELECT
                            DISTINCT EOC_CODE
                        FROM
                            {$ext_table}
                    )
                ) 
                AND CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
                AND POM_SPONSOR_CODE IN('" . implode( "','", $l_pom_sponsor) . "')
                AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
        ";

        $query2 = "
            SELECT DISTINCT B.EOC_CODE FROM LOOKUP_PROGRAM AS A
            LEFT JOIN (
                ${sub_query1}
                UNION ALL
                $sub_query2
            ) AS B ON 
            A.PROGRAM_CODE = B.PROGRAM_CODE
            WHERE PROGRAM_NAME = '${program_code}'
        ";

        $query = "SELECT GROUP_CONCAT(EOC_CODE SEPARATOR ', <br/>') AS EOC_CODE FROM ( ${query2} ) AS TEMP";
        return $this->DBs->SOCOM_UI->query($query)->result_array();
    }

    public function get_issue_eoc($program_code, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXT']
        );

        $sub_query1 = "
        SELECT 
            * 
        FROM 
            {$table} 
        WHERE 
            CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
            AND POM_SPONSOR_CODE IN('" . implode( "','", $l_pom_sponsor) . "')
            AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
        ";

        $table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXTRACT']
        );

        $zbt_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['ZBT']
        );

        $sub_query2 = "
            SELECT 
                0 AS `ADJUSTMENT_K`, 
                `ASSESSMENT_AREA_CODE`, 
                0 AS `BASE_K`, 
                `BUDGET_ACTIVITY_CODE`, 
                `BUDGET_ACTIVITY_NAME`, 
                `BUDGET_SUB_ACTIVITY_CODE`, 
                `BUDGET_SUB_ACTIVITY_NAME`, 
                `CAPABILITY_SPONSOR_CODE`, 
                0 AS `END_STRENGTH`, 
                `EOC_CODE`, 
                `EVENT_JUSTIFICATION`, 
                `EVENT_NAME`, 
                `EXECUTION_MANAGER_CODE`, 
                `FISCAL_YEAR`, 
                `LINE_ITEM_CODE`, 
                0 AS `OCO_OTHD_ADJUSTMENT_K`, 
                0 AS `OCO_OTHD_K`, 
                0 AS `OCO_TO_BASE_K`, 
                `OSD_PROGRAM_ELEMENT_CODE`, 
                `POM_POSITION_CODE`, 
                `POM_SPONSOR_CODE`, 
                `PROGRAM_CODE`, 
                `PROGRAM_GROUP`, 
                `RDTE_PROJECT_CODE`, 
                `RESOURCE_CATEGORY_CODE`, 
                0 AS `RESOURCE_K`, 
                `SPECIAL_PROJECT_CODE`, 
                `SUB_ACTIVITY_GROUP_CODE`, 
                `SUB_ACTIVITY_GROUP_NAME`, 
                2024 AS `WORK_YEARS`  
            FROM 
                {$table} 
            WHERE 
                ( 
                    `PROGRAM_CODE` NOT IN ( 
                        SELECT 
                            DISTINCT PROGRAM_CODE 
                        FROM 
                            {$zbt_table} 
                    ) 
                    OR `EOC_CODE` NOT IN ( 
                        SELECT 
                            DISTINCT EOC_CODE 
                        FROM 
                            {$zbt_table} 
                    ) 
                )
            AND CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
            AND POM_SPONSOR_CODE IN('" . implode( "','", $l_pom_sponsor) . "')
            AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
        ";

        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXT']
        );

        $sub_query3 = "
            SELECT
                0 AS `ADJUSTMENT_K`, 
                `ASSESSMENT_AREA_CODE`, 
                0 AS `BASE_K`, 
                `BUDGET_ACTIVITY_CODE`, 
                `BUDGET_ACTIVITY_NAME`, 
                `BUDGET_SUB_ACTIVITY_CODE`, 
                `BUDGET_SUB_ACTIVITY_NAME`, 
                `CAPABILITY_SPONSOR_CODE`, 
                0 AS `END_STRENGTH`, 
                `EOC_CODE`, 
                `EVENT_JUSTIFICATION`, 
                `EVENT_NAME`, 
                `EXECUTION_MANAGER_CODE`, 
                `FISCAL_YEAR`, 
                `LINE_ITEM_CODE`, 
                0 AS `OCO_OTHD_ADJUSTMENT_K`, 
                0 AS `OCO_OTHD_K`, 
                0 AS `OCO_TO_BASE_K`, 
                `OSD_PROGRAM_ELEMENT_CODE`, 
                `POM_POSITION_CODE`, 
                `POM_SPONSOR_CODE`, 
                `PROGRAM_CODE`, 
                `PROGRAM_GROUP`, 
                `RDTE_PROJECT_CODE`, 
                `RESOURCE_CATEGORY_CODE`, 
                0 AS `RESOURCE_K`, 
                `SPECIAL_PROJECT_CODE`, 
                `SUB_ACTIVITY_GROUP_CODE`, 
                `SUB_ACTIVITY_GROUP_NAME`, 
                2024 AS `WORK_YEARS` 
            FROM
                {$ext_table}
            WHERE
                ( 
                    `PROGRAM_CODE` NOT IN ( 
                        SELECT 
                            DISTINCT PROGRAM_CODE 
                        FROM 
                            {$zbt_table}
                    ) 
                    OR `EOC_CODE` NOT IN ( 
                        SELECT 
                            DISTINCT EOC_CODE 
                        FROM 
                            {$zbt_table} 
                    ) 
                )
            AND CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
            AND POM_SPONSOR_CODE IN('" . implode( "','", $l_pom_sponsor) . "')
            AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
        ";

        $query2 = "
            SELECT DISTINCT B.EOC_CODE FROM LOOKUP_PROGRAM AS A
            LEFT JOIN (
                {$sub_query1}
                UNION ALL
                {$sub_query2}
                UNION ALL
                {$sub_query3}
            ) AS B ON 
            A.PROGRAM_CODE = B.PROGRAM_CODE
            WHERE PROGRAM_NAME = '{$program_code}'
        ";

        $query = "SELECT GROUP_CONCAT(EOC_CODE SEPARATOR ', <br />') AS EOC_CODE FROM ( {$query2} ) AS TEMP";
        return $this->DBs->SOCOM_UI->query($query)->result_array();
    }

    public function get_user_assigned_bin_by_program($program_code) {
        $this->DBs->SOCOM_UI->select('JCA')
        ->distinct()
        ->from('LOOKUP_PROGRAM A')
        ->where('PROGRAM_NAME', $program_code);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_zbt_summary_program_summary(
       $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $programs, $refresh=false, $approval_filter = []
    ) {

        $api_params = array();
 
        $api_params['CAPABILITY_SPONSOR_CODE'] = $l_cap_sponsor;
        $api_params['POM_SPONSOR_CODE'] = $l_pom_sponsor;
        $api_params['ASSESSMENT_AREA_CODE'] = $l_ass_area;
        $api_params['PROGRAM_GROUP'] = $programs;
        $api_params['REFRESH'] = $refresh;
        $api_params['APPROVAL_FILTER'] = $approval_filter;

        $response = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/zbt/program_summary'
        );

        return json_decode($response, true);
    }

    public function get_issue_program_summary(
        $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $programs, $refresh=false, $approval_filter = []
    ) {
        $api_params = array();
 
        $api_params['CAPABILITY_SPONSOR_CODE'] = $l_cap_sponsor;
        $api_params['POM_SPONSOR_CODE'] = $l_pom_sponsor;
        $api_params['ASSESSMENT_AREA_CODE'] = $l_ass_area;
        $api_params['PROGRAM_GROUP'] = $programs;
        $api_params['REFRESH'] = $refresh;
        $api_params['APPROVAL_FILTER'] = $approval_filter;

        $response = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/iss/program_summary'
        );

        return json_decode($response, true);
    }

    public function calculate_prop_amt($base_k, $delta_amt, $key1, $key2, $requested_prop_param) {
        $prop_amt = [];
        $delta_amt_map = [];

        foreach ($delta_amt as $item) {
            $key = $item['PROGRAM_NAME'] . '-' . $item['FISCAL_YEAR'];
            $delta_amt_map[$key] = $item[$key2];
        }

        foreach ($base_k as $item) {
            $key = $item['PROGRAM_NAME'] . '-' . $item['FISCAL_YEAR'];
            $difference = isset($delta_amt_map[$key]) ? (int)$delta_amt_map[$key] + (int)$item[$key1] : 0;
            $prop_amt [] = [
                "PROGRAM_NAME" => $item['PROGRAM_NAME'],
                $requested_prop_param['key'] => $requested_prop_param['value'],
                "POM_POSITION_CODE" => $item["POM_POSITION_CODE"],
                "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                $requested_prop_param['result_key'] => $difference,
                "FISCAL_YEARS" => $item['FISCAL_YEARS']
            ];
        }
        return $prop_amt;
    }

    public function calculate_delta_amt($base_k, $prop_amt, $key1, $key2, $requested_delta_param) {
        $delta_amt = [];
        $prop_amt_map = [];

        foreach ($prop_amt as $item) {
            $key = $item['PROGRAM_NAME'] . '-' . $item['FISCAL_YEAR'];
            $prop_amt_map[$key] = $item[$key2];
        }

        foreach ($base_k as $item) {
            $key = $item['PROGRAM_NAME'] . '-' . $item['FISCAL_YEAR'];
            $difference = isset($prop_amt_map[$key]) ? $prop_amt_map[$key] - $item[$key1] : 0;
            $delta_amt [] = [
                "PROGRAM_NAME" => $item['PROGRAM_NAME'],
                $requested_delta_param['key'] => $requested_delta_param['value'],
                "POM_POSITION_CODE" => $item["POM_POSITION_CODE"],
                "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                "DELTA_AMT" => $difference,
                "FISCAL_YEARS" => $item['FISCAL_YEARS']
            ];
        }
        return $delta_amt;
    }

    public function eoc_historical_calculate_prop_amt($base_k, $delta_amt, $key1, $key2, $requested_delta_param) {
        $prop_amt = [];
        $delta_amt_map = [];

        foreach ($delta_amt as $item) {
            $key = $item['EOC'] . '-' .$item['FISCAL_YEAR'];
            $delta_amt_map[$key] = $item[$key2];
        }

        foreach ($base_k as $item) {
            $key = $item['EOC'] . '-' .$item['FISCAL_YEAR'];
            $difference = isset($delta_amt_map[$key]) ? $delta_amt_map[$key] + $item[$key1] : 0;
            $prop_amt [] = [
                "EOC" => $item['EOC'],
                $requested_delta_param['key'] => $requested_delta_param['value'],
                "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
                "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
                "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
                "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
                "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                $requested_delta_param['result_key'] => $difference,
                "FISCAL_YEARS" => $item['FISCAL_YEARS']
            ];
        }
        return $prop_amt;
    }

    public function eoc_calculate_prop_amt($base_k, $delta_amt, $key1, $key2, $requested_delta_param) {
        $prop_amt = [];
        $delta_amt_map = [];

        foreach ($delta_amt as $item) {
            $key = $item['EOC'] . '-' . $item['EVENT_NAME'] . '-' . $item['POM_SPONSOR_CODE'] . '-' . $item['ASSESSMENT_AREA_CODE'] . '-' . $item['CAPABILITY_SPONSOR_CODE'] . '-' .$item['FISCAL_YEAR'];
            $delta_amt_map[$key] = $item[$key2];
        }

        foreach ($base_k as $item) {
            $key = $item['EOC'] . '-' . $item['EVENT_NAME'] . '-' . $item['POM_SPONSOR_CODE'] . '-' . $item['ASSESSMENT_AREA_CODE'] . '-' . $item['CAPABILITY_SPONSOR_CODE'] . '-' .$item['FISCAL_YEAR'];
            $difference = isset($delta_amt_map[$key]) ? $delta_amt_map[$key] + $item[$key1] : 0;
            $prop_amt [] = [
                "EOC" => $item['EOC'],
                "EVENT_NAME" => $item['EVENT_NAME'],
                "EVENT_JUSTIFICATION" => $item['EVENT_JUSTIFICATION'],
                "POM_POSITION_CODE" => $item['POM_POSITION_CODE'],
                $requested_delta_param['key'] => $requested_delta_param['value'],
                "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
                "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
                "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
                "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
                "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                "PROP_AMT" => $difference,
                "FISCAL_YEARS" => $item['FISCAL_YEARS']
            ];
        }
        return $prop_amt;
    }
    
    public function eoc_calculate_delta_amt($base_k, $prop_amt, $key1, $key2, $requested_delta_param) {
        $delta_amt = [];
        $prop_amt_map = [];

        foreach ($prop_amt as $item) {
            $key = $item['EOC'] . '-' . $item['FISCAL_YEAR'];
            $prop_amt_map[$key] = (int)$item[$key2];
        }

        
        foreach ($base_k as $item) {
            $key = $item['EOC'] . '-' . $item['FISCAL_YEAR'];
            $difference = isset($prop_amt_map[$key]) && (is_numeric($prop_amt_map[$key]))
                 ? $prop_amt_map[$key] - $item[$key1] : 0;
            $delta_amt [] = [
                "EOC" => $item['EOC'],
                $requested_delta_param['key'] => $requested_delta_param['value'],
                "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
                "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
                "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
                "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
                "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                "DELTA_AMT" => $difference,
                "FISCAL_YEARS" => $item['FISCAL_YEARS']
            ];
        }
        return $delta_amt;
    }

    public function get_issue_summary_fy_query(
        $table1, $selection, $fy, $eoc_code, $program_code = '', $pom=false, $page=''
    ) {
        $year_list = $this->page_variables[$page]['year_list'];
        $minYear = intval('20'. $fy);
        $maxYear = max($year_list);
        $year_list = range($minYear, $maxYear);
        $year_list_query = "'" . implode("', '", $year_list) . "'";

        $query1_extra_select = '';
        if (!$pom) {
            $query1_extra_select = ' PROGRAM_GROUP, POM_POSITION_CODE, ';
        }

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND EXT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            ${query1_extra_select}
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            FISCAL_YEAR,
            RESOURCE_K")->from($table1);
        $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('('. $sub_query1 . ') AS EXT')->get_compiled_select();

        $query2_extra_select = '';
        $query2_extra_and = '';
        if (!$pom) {
            $query2_extra_select = ' PROGRAM_GROUP,';
            $query2_extra_and = ' AND EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP';
        }

        $query2 = " LEFT JOIN (
            SELECT
                PROGRAM_NAME,
                PROGRAM_CODE,
                {$query2_extra_select}
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE,
                POM_SPONSOR_CODE
            FROM
                LOOKUP_PROGRAM
        ) AS LUT ON EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            {$query2_extra_and}
        AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
        AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
        AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";

        if (!$pom) {
            $query3_extra_group = ' POM_POSITION_CODE,';
        }
        else {
            $query3_extra_group = " ${fy}POM_REQUESTED,";
        }

        $query3 = "
        WHERE
            EXT.FISCAL_YEAR IN (${year_list_query})
            AND EXT.PROGRAM_CODE = '${program_code}'
            ${eoc_code_filter}
        GROUP BY
            LUT.PROGRAM_NAME,
            ${query3_extra_group}
            EXT.FISCAL_YEAR
        ORDER BY
            LUT.PROGRAM_NAME,
            EXT.FISCAL_YEAR
        ";

        // print_r('---------------get_issue_summary_fy_query----------------');
        // print_r($query1 . $query2 . $query3);
        // print_r('--------------------------------------------------------');
        return $this->DBs->SOCOM_UI->query($query1 . $query2 . $query3)->result_array();
    }

    public function get_issue_summary_query(
        $table1, $table2, $table3, $selection, $eoc_code, $delta = false, $program_code = '', $ext = false
    ) {
        $fy = $this->page_variables['issue']['fy'];
        $year = $this->page_variables['issue']['year'];

        $year_list = $this->page_variables['issue']['year_list'];
        $year_list_query = "'" . implode("', '", $year_list) . "'";

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND ZBT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $this->DBs->SOCOM_UI->select('
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            POM_POSITION_CODE,
            FISCAL_YEAR,
            RESOURCE_K
        ')->from($table2);
        $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();

        $this->DBs->SOCOM_UI->select("
                EOC_CODE,
                PROGRAM_CODE,
                PROGRAM_GROUP,
                POM_SPONSOR_CODE,
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE,
                '${fy}ZBT' AS POM_POSITION_CODE,
                FISCAL_YEAR,
                0 AS RESOURCE_K
            ")
            ->from($table1)
            ->where("(
                (PROGRAM_CODE, FISCAL_YEAR) NOT IN (SELECT DISTINCT PROGRAM_CODE, FISCAL_YEAR FROM ${table2})
            )");
        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            '${fy}ZBT' AS POM_POSITION_CODE,
            FISCAL_YEAR,
            0 AS RESOURCE_K")
            ->from($table3)
            ->where("( (PROGRAM_CODE, FISCAL_YEAR) NOT IN (SELECT DISTINCT PROGRAM_CODE, FISCAL_YEAR FROM ${table2}))");
       

        $sub_query3 = $this->DBs->SOCOM_UI->get_compiled_select();

        $union_all2 = '';
        if (!$delta) {
            $union_all2 = ' UNION ALL ' . $sub_query3;
        }

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('( ('. $sub_query1 . ') UNION ALL (' . $sub_query2 . ') ' . $union_all2 .') AS ZBT')->get_compiled_select();

        $query2_extra_join = '';
        if (!$ext) {
            $query2_extra_join = 'ZBT.PROGRAM_GROUP = LUT.PROGRAM_GROUP AND';
        }

        $query2 = " LEFT JOIN (
            SELECT
                PROGRAM_NAME,
                PROGRAM_GROUP,
                PROGRAM_CODE,
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE,
                POM_SPONSOR_CODE
            FROM
                LOOKUP_PROGRAM
        ) AS LUT ON ZBT.PROGRAM_GROUP = LUT.PROGRAM_GROUP 
        AND ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE
        AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
        AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
        AND ZBT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
        
        $query3_extra_where = '';
        if ($delta) {
            $query2 =  $query2  . " LEFT JOIN (
                SELECT
                    PROGRAM_CODE,
                    EOC_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE,
                    FISCAL_YEAR,
                    DELTA_AMT,
                    EXECUTION_MANAGER_CODE,
                    OSD_PROGRAM_ELEMENT_CODE
                FROM
                    ${table1}
            ) AS ISS_EXTRACT ON ZBT.PROGRAM_CODE = ISS_EXTRACT.PROGRAM_CODE
            AND ZBT.FISCAL_YEAR = ISS_EXTRACT.FISCAL_YEAR
            AND ZBT.EOC_CODE = ISS_EXTRACT.EOC_CODE
            AND ZBT.POM_SPONSOR_CODE = ISS_EXTRACT.POM_SPONSOR_CODE
            AND ZBT.CAPABILITY_SPONSOR_CODE = ISS_EXTRACT.CAPABILITY_SPONSOR_CODE
            AND ZBT.OSD_PROGRAM_ELEMENT_CODE = ISS_EXTRACT.OSD_PROGRAM_ELEMENT_CODE
            AND ZBT.EXECUTION_MANAGER_CODE = ISS_EXTRACT.EXECUTION_MANAGER_CODE";

            $query3_extra_where = "AND ZBT.EXECUTION_MANAGER_CODE != '' ";
        }

        $query3 = "
        WHERE
            ZBT.FISCAL_YEAR IN (${year_list_query})
            AND LUT.PROGRAM_NAME IS NOT NULL
            AND ZBT.PROGRAM_CODE = '${program_code}'
            ${eoc_code_filter}
        GROUP BY
            LUT.PROGRAM_NAME,
            POM_POSITION_CODE,
            ZBT.FISCAL_YEAR
        ORDER BY
            LUT.PROGRAM_NAME,
            ZBT.FISCAL_YEAR
        ";
        // print_r('==============get_issue_summary_query=================');
        // print_r($query1 . $query2 . $query3);
        // print_r('===============================');
        return $this->DBs->SOCOM_UI->query($query1 . $query2 . $query3)->result_array();
    }

    public function get_issue_summary_delta_query(
        $extract_table, $zbt_table, $ext_table, $selection, $eoc_code, $program_code = ''
    ) {
        $fy = $this->page_variables['issue']['fy'];
        $year_list = $this->page_variables['issue']['year_list'];
        $year_list_query = "'" . implode("', '", $year_list) . "'";

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND ISS_EXTRACT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $this->DBs->SOCOM_UI->select('
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            FISCAL_YEAR,
            DELTA_AMT
        ')->from($extract_table);
        $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();

        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            FISCAL_YEAR,
            0 AS DELTA_AMT")
            ->from($ext_table)
            ->where("(
                (PROGRAM_CODE, FISCAL_YEAR) NOT IN (
                    SELECT 
                        DISTINCT PROGRAM_CODE, 
                        FISCAL_YEAR 
                    FROM 
                        {$extract_table}
                    WHERE
                        PROGRAM_CODE IS NOT NULL
                )
            )");
        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            FISCAL_YEAR,
            0 AS DELTA_AMT")
            ->from($zbt_table)
            ->where("(
                (PROGRAM_CODE, FISCAL_YEAR) NOT IN (
                    SELECT 
                        DISTINCT PROGRAM_CODE,
                        FISCAL_YEAR
                    FROM 
                        ${extract_table}
                    WHERE
                        PROGRAM_CODE IS NOT NULL
                )
            )");
       

        $sub_query3 = $this->DBs->SOCOM_UI->get_compiled_select();

        $union_all2 = ' UNION ALL ' . $sub_query3;

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('( ('. $sub_query1 . ') UNION ALL (' . $sub_query2 . ') ' . $union_all2 .') AS ISS_EXTRACT')->get_compiled_select();

        $query2 = " LEFT JOIN (
            SELECT
                PROGRAM_NAME,
                PROGRAM_GROUP,
                PROGRAM_CODE,
                POM_SPONSOR_CODE,
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE
            FROM
                LOOKUP_PROGRAM
        ) AS LUT ON ISS_EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
        AND ISS_EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
        AND ISS_EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
        AND ISS_EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
        AND ISS_EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
        
        $query3 = "
        WHERE
            ISS_EXTRACT.FISCAL_YEAR IN (${year_list_query})
            AND LUT.PROGRAM_NAME IS NOT NULL
            AND ISS_EXTRACT.PROGRAM_CODE = '${program_code}'
            ${eoc_code_filter}
        GROUP BY
            LUT.PROGRAM_NAME,
            ${fy}ISS_REQUESTED_DELTA,
            ISS_EXTRACT.FISCAL_YEAR
        ORDER BY
            LUT.PROGRAM_NAME,
            ISS_EXTRACT.FISCAL_YEAR
        ";
        // print_r('==============get_issue_summary_query delta=================');
        // print_r($query1 . $query2 . $query3);
        // print_r('===============================');
        return $this->DBs->SOCOM_UI->query($query1 . $query2 . $query3)->result_array();
    }

 
    public function get_zbt_program_summary_query(
        $params, $selection, $program_list = [], $delta = false, $program = ''
    ) {
        $l_pom_sponsor = $params['l_pom_sponsor'];
        $l_cap_sponsor = $params['l_cap_sponsor'];
        $l_ass_area = $params['l_ass_area'];
        $eoc_code = $params['eoc_code'];

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND EXT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $zbt_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['ZBT']
        );
        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXT'] // may need to change later
        );
        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXTRACT']
        );

        $fy = $this->page_variables['zbt_summary']['fy'];
        $year = $this->page_variables['zbt_summary']['year'];
        $two_years_ago_fy = $year - 2;

        $year_list = $this->page_variables['zbt_summary']['year_list'];
        $year_list_query = "'" . implode("', '", $year_list) . "'";
     
        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            POM_POSITION_CODE,
            FISCAL_YEAR,
            RESOURCE_K
        ")->from($ext_table);
        if ($program == '') {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                                ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                                ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        }
        $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();
        
        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            '{$fy}EXT' AS POM_POSITION_CODE,
            FISCAL_YEAR,
            0 AS RESOURCE_K")
            ->from($extract_table)
            ->where("((PROGRAM_CODE, FISCAL_YEAR) NOT IN (SELECT DISTINCT PROGRAM_CODE, FISCAL_YEAR FROM {$ext_table}))");

        if ($program == '') {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                                ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                                ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        }

        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('( ('. $sub_query1 . ') UNION ALL (' . $sub_query2 . ')) AS EXT')->get_compiled_select();
            
        $query2_delta_extra_where = '';
        $query3_extra_where = '';
        $query3_extra_where_program_list = '';
        if ($program == '') {
            $query3_extra_where = "`LUT`.`PROGRAM_NAME` IS NOT NULL";
            $query2_delta_extra_where = " WHERE
            CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
            AND POM_SPONSOR_CODE IN('" . implode( "','", $l_pom_sponsor) . "')
            AND ASSESSMENT_AREA_CODE IN('". implode( "','", $l_ass_area) ."')";
        }
        else {
            $query3_extra_where = "`EXT`.`PROGRAM_CODE` = '" . $program . "' AND `LUT`.`PROGRAM_NAME` IS NOT NULL";
        }

        if (!empty($program_list)) {
            $query3_extra_where_program_list = "AND `LUT`.`PROGRAM_GROUP` IN('". implode( "','", $program_list) ."')";
        }

        
        $query2 = " LEFT JOIN (
                SELECT
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    ASSESSMENT_AREA_CODE,
                    POM_SPONSOR_CODE
                FROM
                    LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
        
        $query2_delta  = "";
        $query3_delta_extra_where = "";

        $query3 = "
            WHERE 
                `EXT`.`FISCAL_YEAR` IN ({$year_list_query})
                AND {$query3_extra_where} {$query3_delta_extra_where} {$query3_extra_where_program_list}
                {$eoc_code_filter}
            GROUP BY 
                `LUT`.`PROGRAM_NAME`,
                `POM_POSITION_CODE`,
                `EXT`.`FISCAL_YEAR`
            ORDER BY 
                `LUT`.`PROGRAM_NAME`,
                `EXT`.`FISCAL_YEAR`
        ";

        // print_r('===============26zbt query======================');
        // print_r($query1 . $query2 .  $query2_delta  . $query3);
        // print_r('=============================================');
        return $this->DBs->SOCOM_UI->query($query1 . $query2 .  $query2_delta  . $query3)->result_array();
    }

    public function get_zbt_program_summary_delta_query(
        $params, $selection, $program_list = [], $delta = false, $program = ''
    ) {
        $l_pom_sponsor = $params['l_pom_sponsor'];
        $l_cap_sponsor = $params['l_cap_sponsor'];
        $l_ass_area = $params['l_ass_area'];
        $eoc_code = $params['eoc_code'];

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND EXT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXT'] // may need to change later
        );
        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXTRACT']
        );

        $fy = $this->page_variables['zbt_summary']['fy'];
        $year = $this->page_variables['zbt_summary']['year'];

        $year_list = $this->page_variables['zbt_summary']['year_list'];
        $year_list_query = "'" . implode("', '", $year_list) . "'";
     
        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            FISCAL_YEAR,
            DELTA_AMT
        ")->from($extract_table);
        if ($program == '') {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                                ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                                ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        }
        $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();

        $this->DBs->SOCOM_UI->select("
            EOC_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,
            FISCAL_YEAR,
            0 AS DELTA_AMT")
            ->from($ext_table)
            ->where("
            (PROGRAM_CODE, FISCAL_YEAR) NOT IN (
                SELECT 
                    DISTINCT PROGRAM_CODE, FISCAL_YEAR
                FROM 
                    {$extract_table} 
                WHERE 
                    PROGRAM_CODE IS NOT NULL
            )");

        if ($program == '') {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                                ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                                ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        }

        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('( ('. $sub_query1 . ') UNION ALL (' . $sub_query2 . ') ) AS EXT')->get_compiled_select();
                    
        $query2 = " LEFT JOIN (
                SELECT
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    POM_SPONSOR_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    ASSESSMENT_AREA_CODE
                FROM
                    LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
        
        $query2_delta  = "";

        $query3_extra_where = "`EXT`.`PROGRAM_CODE` = '" . $program . "' AND `LUT`.`PROGRAM_NAME` IS NOT NULL";

        $query3 = "
            WHERE 
                `EXT`.`FISCAL_YEAR` IN ({$year_list_query})
                AND {$query3_extra_where} {$query3_extra_where_program_list}
                {$eoc_code_filter}
            GROUP BY 
                `LUT`.`PROGRAM_NAME`,
                `{$fy}ZBT_REQUESTED_DELTA`,
                `EXT`.`FISCAL_YEAR`
            ORDER BY 
                `LUT`.`PROGRAM_NAME`,
                `EXT`.`FISCAL_YEAR`
        ";

        // print_r('===============26zbt delta query=======================');
        // print_r($query1 . $query2 .  $query2_delta  . $query3);
        // print_r('=============================================');
        return $this->DBs->SOCOM_UI->query($query1 . $query2 .  $query2_delta  . $query3)->result_array();
    }

    public function zbt_summary_program_summary_query(
        $table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program = null
    ) {
        $query1 = $this->get_issue_zbt_extract($table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area);
        $join_query1 = $this->get_lut_count('
            PROGRAM_NAME,PROGRAM_GROUP,PROGRAM_CODE,POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,ASSESSMENT_AREA_CODE
        ');

        $query2 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('('. $query1 .') AS EXT')->get_compiled_select();

        $join_query2 = $this->get_zbt_extract($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area);

        $query3 = " LEFT JOIN (".  $join_query1 .")
        AS LUT ON 
            `EXT`.`PROGRAM_GROUP` = `LUT`.`PROGRAM_GROUP`
            AND `EXT`.`PROGRAM_CODE` = `LUT`.`PROGRAM_CODE`
            AND `EXT`.`POM_SPONSOR_CODE` = `LUT`.`POM_SPONSOR_CODE`
            AND `EXT`.`CAPABILITY_SPONSOR_CODE` = `LUT`.`CAPABILITY_SPONSOR_CODE`
            AND `EXT`.`ASSESSMENT_AREA_CODE` = `LUT`.`ASSESSMENT_AREA_CODE`
        ";

        $query4 = " LEFT JOIN (".  $join_query2 .")
        AS ZBT_EXTRACT ON 
            `EXT`.`EOC_CODE` = `ZBT_EXTRACT`.`EOC_CODE`
            AND `EXT`.`OSD_PROGRAM_ELEMENT_CODE` = `ZBT_EXTRACT`.`OSD_PROGRAM_ELEMENT_CODE`
            AND `EXT`.`CAPABILITY_SPONSOR_CODE` = `ZBT_EXTRACT`.`CAPABILITY_SPONSOR_CODE`
            AND `EXT`.`POM_SPONSOR_CODE` = `ZBT_EXTRACT`.`POM_SPONSOR_CODE`
            AND `EXT`.`ASSESSMENT_AREA_CODE` = `ZBT_EXTRACT`.`ASSESSMENT_AREA_CODE`
            AND `EXT`.`EXECUTION_MANAGER_CODE` = `ZBT_EXTRACT`.`EXECUTION_MANAGER_CODE`
            AND `EXT`.`RESOURCE_CATEGORY_CODE` = `ZBT_EXTRACT`.`RESOURCE_CATEGORY_CODE`
            AND `EXT`.`FISCAL_YEAR` = `ZBT_EXTRACT`.`FISCAL_YEAR`
        ";

        $query5 = " 
            WHERE `EXT`.`FISCAL_YEAR` IN ('2026', '2027', '2028', '2029', '2030') AND `LUT`.`PROGRAM_NAME` IS NOT NULL
            GROUP BY `LUT`.`PROGRAM_NAME`,`EXT`.`POM_POSITION_CODE`,`EXT`.`FISCAL_YEAR`
            ORDER BY `PROGRAM_NAME`, `FISCAL_YEAR`
        ";
        return $this->DBs->SOCOM_UI->query($query2 . $query3 . $query4 . $query5)->result_array();
    }
    
    public function zbt_summary_program_summary_delta_query(
        $table1, $table2, $table3, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list=[]
    ) {
        $this->DBs->SOCOM_UI->select('*')->from($table1);
      
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                            ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                            ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        
        $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();
        
        $this->DBs->SOCOM_UI->select('
            0 AS ADJUSTMENT_K,
            ASSESSMENT_AREA_CODE,
            0 AS BASE_K,
            BUDGET_ACTIVITY_CODE,
            BUDGET_ACTIVITY_NAME,
            BUDGET_SUB_ACTIVITY_CODE,
            BUDGET_SUB_ACTIVITY_NAME,
            CAPABILITY_SPONSOR_CODE,
            0 AS END_STRENGTH,
            EOC_CODE,
            EVENT_JUSTIFICATION,
            EVENT_NAME,
            EXECUTION_MANAGER_CODE,
            FISCAL_YEAR,
            LINE_ITEM_CODE,
            0 AS OCO_OTHD_ADJUSTMENT_K,
            0 AS OCO_OTHD_K,
            0 AS OCO_TO_BASE_K,
            OSD_PROGRAM_ELEMENT_CODE,
            "26ZBT" AS POM_POSITION_CODE,
            POM_SPONSOR_CODE,
            PROGRAM_CODE,
            PROGRAM_GROUP,
            RDTE_PROJECT_CODE,
            RESOURCE_CATEGORY_CODE,
            0 AS RESOURCE_K,
            SPECIAL_PROJECT_CODE,
            SUB_ACTIVITY_GROUP_CODE,
            SUB_ACTIVITY_GROUP_NAME,
            2024 AS WORK_YEARS')
            ->from($table3)
            ->group_start()
            ->where('PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ' .$table1.') OR 
            EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM ' .$table1.')')
            ->group_end();

  
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                            ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                            ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        

        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('('. $sub_query1 . ' UNION ALL ' . $sub_query2 . ') AS ZBT')->get_compiled_select();

        $query2_extra_select = '';
        $query2_extra_where = '';
        $query3_extra_where = '';
        $query3_extra_where_program_list = '';

        if (!empty($program_list)) {
            $query3_extra_where_program_list = "AND `LUT`.`PROGRAM_GROUP` IN('". implode( "','", $program_list) ."')";
        }

     
        $query2_extra_select =  "POM_SPONSOR_CODE,
        CAPABILITY_SPONSOR_CODE,
        ASSESSMENT_AREA_CODE,";
        $query2_extra_where = "
        AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
        AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
        AND ZBT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
        $query3_extra_where = "`LUT`.`PROGRAM_NAME` IS NOT NULL";
        
        $query2 = " LEFT JOIN (
                SELECT {$query2_extra_select}
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE
                FROM
                    LOOKUP_PROGRAM
            ) AS LUT ON ZBT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE {$query2_extra_where}";
        
        $query3 = "
            WHERE 
                `ZBT`.`FISCAL_YEAR` IN ('2026', '2027', '2028', '2029', '2030')
                AND {$query3_extra_where} {$query3_extra_where_program_list}
                AND ZBT.EXECUTION_MANAGER_CODE != ''
            GROUP BY 
                `LUT`.`PROGRAM_NAME`,
                `ZBT`.`POM_POSITION_CODE`,
                `ZBT`.`FISCAL_YEAR`
            ORDER BY 
                `LUT`.`PROGRAM_NAME`,
                `ZBT`.`FISCAL_YEAR`
        ";

        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXTRACT'] // may need to change later
        );
        
        $this->DBs->SOCOM_UI->select('  PROGRAM_CODE,
                                        EOC_CODE,
                                        CAPABILITY_SPONSOR_CODE,
                                        POM_SPONSOR_CODE, 
                                        ASSESSMENT_AREA_CODE, 
                                        FISCAL_YEAR, 
                                        DELTA_AMT, 
                                        EXECUTION_MANAGER_CODE ')
                            ->from($extract_table);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
                            ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
                            ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        $subquery = $this->DBs->SOCOM_UI->get_compiled_select();
        $query4 = " LEFT JOIN (
            $subquery ) as ISS_EXTRACT ON ZBT.PROGRAM_CODE = ISS_EXTRACT.PROGRAM_CODE
            AND ZBT.FISCAL_YEAR = ISS_EXTRACT.FISCAL_YEAR
            AND ZBT.EOC_CODE = ISS_EXTRACT.EOC_CODE
            AND ZBT.POM_SPONSOR_CODE = ISS_EXTRACT.POM_SPONSOR_CODE
            AND ZBT.CAPABILITY_SPONSOR_CODE = ISS_EXTRACT.CAPABILITY_SPONSOR_CODE
            AND ZBT.ASSESSMENT_AREA_CODE = ISS_EXTRACT.ASSESSMENT_AREA_CODE
            AND ZBT.EXECUTION_MANAGER_CODE = ISS_EXTRACT.EXECUTION_MANAGER_CODE 
        ";
        // print_r('===============zbt_summary_program_summary_delta_query======================');
        // print_r($query1 . $query2  . $query4 . $query3);
        // print_r('=============================================');
        return $this->DBs->SOCOM_UI->query($query1 . $query2  . $query4 . $query3)->result_array();
    }

    public function issue_program_summary_query(
        $table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program=null
    ) {
        $query1 = $this->get_issue_zbt_extract($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area);
        $join_query1 = $this->get_lut_count('
            PROGRAM_NAME,PROGRAM_GROUP,PROGRAM_CODE,POM_SPONSOR_CODE,
            CAPABILITY_SPONSOR_CODE,ASSESSMENT_AREA_CODE
        ');
        $query2 = $this->DBs->SOCOM_UI
                        ->select($selection)
                        ->from('('. $query1 .') AS EXT')->get_compiled_select();
 
        $query3 = " LEFT JOIN (".  $join_query1 .")
                AS LUT ON 
                    `EXT`.`PROGRAM_GROUP` = `LUT`.`PROGRAM_GROUP`
                    AND `EXT`.`PROGRAM_CODE` = `LUT`.`PROGRAM_CODE`
                    AND `EXT`.`POM_SPONSOR_CODE` = `LUT`.`POM_SPONSOR_CODE`
                    AND `EXT`.`CAPABILITY_SPONSOR_CODE` = `LUT`.`CAPABILITY_SPONSOR_CODE`
                    AND `EXT`.`ASSESSMENT_AREA_CODE` = `LUT`.`ASSESSMENT_AREA_CODE`
                    WHERE EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030') AND
                    `LUT`.`PROGRAM_NAME`IS NOT NULL
                ";
        if ( $program) {
            $query3 = $query3 . ' AND PROGRAM_NAME= "'.  $program . '"';
        }
        $query4 = " GROUP BY LUT.PROGRAM_NAME,EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR
                    ORDER BY `PROGRAM_NAME`, `FISCAL_YEAR`";
        $result = $this->DBs->SOCOM_UI->query($query2 . $query3 . $query4)->result_array();
        return $result;
    }

    
    // public function issue_program_summary_main_query(
    //     $table1, $table2, $table3, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $selection, $program_list=[], $program=null
    // ) {
    //     $this->DBs->SOCOM_UI->select('*')->from($table1);
    //     if ($program == '') {
    //         $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
    //                             ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
    //                             ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
    //     }
    //     $sub_query1 = $this->DBs->SOCOM_UI->get_compiled_select();
        
    //     $this->DBs->SOCOM_UI->select('
    //         0 AS ADJUSTMENT_K, 
    //         ASSESSMENT_AREA_CODE,
    //         0 AS BASE_K,
    //         BUDGET_ACTIVITY_CODE,
    //         BUDGET_ACTIVITY_NAME,
    //         BUDGET_SUB_ACTIVITY_CODE,
    //         BUDGET_SUB_ACTIVITY_NAME,
    //         CAPABILITY_SPONSOR_CODE,
    //         0 AS END_STRENGTH,EOC_CODE,
    //         EVENT_JUSTIFICATION,EVENT_NAME,
    //         EXECUTION_MANAGER_CODE,
    //         FISCAL_YEAR,
    //         LINE_ITEM_CODE,
    //         0 AS OCO_OTHD_ADJUSTMENT_K,
    //         0 AS OCO_OTHD_K,0 AS OCO_TO_BASE_K,
    //         OSD_PROGRAM_ELEMENT_CODE,
    //         "26EXT" AS POM_POSITION_CODE,
    //         POM_SPONSOR_CODE,
    //         PROGRAM_CODE,
    //         PROGRAM_GROUP,
    //         RDTE_PROJECT_CODE,
    //         RESOURCE_CATEGORY_CODE,
    //         0 AS RESOURCE_K,
    //         SPECIAL_PROJECT_CODE,
    //         SUB_ACTIVITY_GROUP_CODE,
    //         SUB_ACTIVITY_GROUP_NAME,
    //         2024 AS WORK_YEARS')
    //         ->from($table3)
    //         ->group_start()
    //         ->where('PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ' .$table1.')')
    //         ->group_end();

    //     if ($program == '') {
    //         $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
    //                             ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
    //                             ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
    //     }

    //     $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();
        
    //     $this->DBs->SOCOM_UI->select('
    //     0 AS ADJUSTMENT_K, 
    //     ASSESSMENT_AREA_CODE,
    //     0 AS BASE_K,
    //     BUDGET_ACTIVITY_CODE,
    //     BUDGET_ACTIVITY_NAME,BUDGET_SUB_ACTIVITY_CODE,
    //     BUDGET_SUB_ACTIVITY_NAME,
    //     CAPABILITY_SPONSOR_CODE,
    //     0 AS END_STRENGTH,EOC_CODE,
    //     EVENT_JUSTIFICATION,EVENT_NAME,EXECUTION_MANAGER_CODE,
    //     FISCAL_YEAR,
    //     LINE_ITEM_CODE,
    //     0 AS OCO_OTHD_ADJUSTMENT_K,
    //     0 AS OCO_OTHD_K,
    //     0 AS OCO_TO_BASE_K,
    //     OSD_PROGRAM_ELEMENT_CODE,
    //     "26EXT" AS POM_POSITION_CODE,
    //     POM_SPONSOR_CODE,
    //     PROGRAM_CODE,
    //     PROGRAM_GROUP,
    //     RDTE_PROJECT_CODE,
    //     RESOURCE_CATEGORY_CODE,
    //     0 AS RESOURCE_K,
    //     SPECIAL_PROJECT_CODE,
    //     SUB_ACTIVITY_GROUP_CODE,
    //     SUB_ACTIVITY_GROUP_NAME,
    //     2024 AS WORK_YEARS')
    //     ->from($table2)
    //     ->group_start()
    //     ->where('PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ' .$table1.')')
    //     ->group_end();
    //     ;

    //     if ($program == '') {
    //         $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
    //                             ->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor)
    //                             ->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
    //     }

    //     $sub_query3 = $this->DBs->SOCOM_UI->get_compiled_select();

    //     $query1 = $this->DBs->SOCOM_UI
    //         ->select($selection)
    //         ->from('('. $sub_query1 . ' UNION ALL ' . $sub_query2 . ' UNION ALL '. $sub_query3 . ') AS EXT')->get_compiled_select();

    //     $query2_extra_select = '';
    //     $query2_extra_where = '';
    //     $query3_extra_where = '';
    //     $query3_extra_where_program_list = '';

    //     if (!empty($program_list)) {
    //         $query3_extra_where_program_list = "AND `LUT`.`PROGRAM_GROUP` IN('". implode( "','", $program_list) ."')";
    //     }

    //     if ($program == '') {
    //         $query2_extra_select =  "POM_SPONSOR_CODE,
    //         CAPABILITY_SPONSOR_CODE,
    //         ASSESSMENT_AREA_CODE,";
    //         $query2_extra_where = "
    //         AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //         AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //         AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
    //         $query3_extra_where = "`LUT`.`PROGRAM_NAME` IS NOT NULL";
    //     }
    //     else {
    //         $query3_extra_where = "`LUT`.`PROGRAM_NAME` = '" .$program . "'";
    //     }
        
    //     $query2 = " LEFT JOIN (
    //             SELECT ".$query2_extra_select."
    //                 PROGRAM_NAME,
    //                 PROGRAM_GROUP,
    //                 PROGRAM_CODE
    //             FROM
    //                 LOOKUP_PROGRAM_DETAIL
    //         ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
    //         AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE".$query2_extra_where;
        

    //     $query3 = "
    //         WHERE 
    //             `EXT`.`FISCAL_YEAR` IN ('2026', '2027', '2028', '2029', '2030') 
    //             AND " . $query3_extra_where  . " ${query3_extra_where_program_list}
    //         GROUP BY 
    //             `LUT`.`PROGRAM_NAME`,
    //             `EXT`.`POM_POSITION_CODE`,
    //             `EXT`.`FISCAL_YEAR`
    //         ORDER BY 
    //             `PROGRAM_NAME`, 
    //             `FISCAL_YEAR`
    //     ";

    //     // print_r('================================issue_program_summary_main_query==============================');
    //     // print_r($query1 . $query2  . $query3);
    //     // print_r('==============================================================================================');
    //     return $this->DBs->SOCOM_UI->query($query1 . $query2  . $query3)->result_array();
    // }


    private function get_lut_count($select) {
        $this->DBs->SOCOM_UI->select($select);
        return $this->DBs->SOCOM_UI->from('LOOKUP_PROGRAM')->get_compiled_select();
    }

    private function get_zbt_extract($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area) {
        $this->DBs->SOCOM_UI->select('
            EOC_CODE,OSD_PROGRAM_ELEMENT_CODE,CAPABILITY_SPONSOR_CODE,POM_SPONSOR_CODE,ASSESSMENT_AREA_CODE,
            EXECUTION_MANAGER_CODE,RESOURCE_CATEGORY_CODE,EVENT_STATUS,EVENT_NAME,EVENT_JUSTIFICATION
            ,FISCAL_YEAR,DELTA_AMT,PROP_AMT,RESOURCE_K
        ');
        $this->DBs->SOCOM_UI->from($table1);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor);
        $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        return $this->DBs->SOCOM_UI->get_compiled_select();
    }

    private function get_issue_zbt_extract($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area) {
        $this->DBs->SOCOM_UI->select('*');
        $this->DBs->SOCOM_UI->from($table1);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('POM_SPONSOR_CODE',  $l_pom_sponsor);
        $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        return $this->DBs->SOCOM_UI->get_compiled_select();
    }

    public function get_historical_pom_data($page, $view, $fy, $params, $current_yr, $program,  $program_code) {
        $helper_func = $view . '_data_helper';
        return $this->$helper_func($page, $fy, $params, $current_yr, $program, $program_code);
    }
    private function details_data_helper($page, $fy, $params, $current_yr, $program, $program_code) {
        $eoc_code = $params['eoc_code'];
        $item_key = 'PROGRAM_NAME';
        $pom_prop_amt = [];
        $pom_delta_amt = [];
        $issue_prop_amt = [];
        $issue_delta_amt = [];

        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            $current_yr === $fy,
            $this->page_variables[$page]['type']['EXT'],
            $current_yr === $fy ? 0 : 2 - ($current_yr - $fy)
        );

        if ($fy == $current_yr && $page == 'zbt_summary') {
            $base_k = $this->get_zbt_program_summary_query(
                $params,
                "LUT.PROGRAM_NAME,'{$fy}EXT' AS {$fy}EXT,EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS BASE_K",
                [],
                false,
                $program_code
            );
            $delta_amt = $this->get_zbt_program_summary_delta_query(
                $params,
                "LUT.PROGRAM_NAME,'{$fy}ZBT REQUESTED DELTA' AS {$fy}ZBT_REQUESTED_DELTA,EXT.FISCAL_YEAR,
                SUM(EXT.DELTA_AMT) AS DELTA_AMT",
                [],
                true,
                $program_code
            );
            $this->appendDataToBase($base_k, $fy.'EXT', $item_key, $page, '', $fy);

            $this->defaultToZero($base_k, $delta_amt, 'DELTA_AMT', $fy.'ZBT_REQUESTED_DELTA', $fy.'ZBT REQUESTED DELTA');
    
            if (empty($base_k) && !empty($delta_amt)) {
                $this->defaultToZero($delta_amt, $base_k, 'BASE_K', $fy.'EXT', $fy.'EXT');
            }
   
            $prop_amt = $this->calculate_prop_amt(
                $base_k, $delta_amt, 'BASE_K', 'DELTA_AMT',
                [
                    'key' => $fy.'ZBT_REQUESTED',
                    'value'=> $fy.'ZBT REQUESTED',
                    'result_key' => 'PROP_AMT'
                ]
            );
            $this->defaultToZero($base_k, $prop_amt, 'PROP_AMT', $fy.'ZBT_REQUESTED', $fy.'ZBT REQUESTED');

            $data = [
                'base_k' => $base_k,
                'prop_amt' => $prop_amt,
                'delta_amt' => $delta_amt
            ];
        } elseif ($fy == $current_yr && $page == 'issue') {

            $zbt_table = $this->dynamic_year->getTable(
                $this->page_variables['issue']['subapp'],
                true,
                $this->page_variables['issue']['type']['ZBT']
            );
            $ext_table = $this->dynamic_year->getTable(
                $this->page_variables['issue']['subapp'],
                true,
                $this->page_variables['issue']['type']['EXT']
            );
            $extract_table = $this->dynamic_year->getTable(
                $this->page_variables['issue']['subapp'],
                true,
                $this->page_variables['issue']['type']['EXTRACT']
            );

            $base_k =  $this->get_issue_summary_query(
                $extract_table,
                $ext_table,
                $zbt_table,
                "LUT.PROGRAM_NAME,'${fy}EXT' AS ${fy}EXT,ZBT.POM_POSITION_CODE,ZBT.FISCAL_YEAR,SUM(ZBT.RESOURCE_K) AS BASE_K",
                $eoc_code,
                false,
                $program_code,
                true
            );

            $prop_amt = $this->get_issue_summary_query(
                $extract_table,
                $zbt_table,
                $ext_table,
                "LUT.PROGRAM_NAME,'${fy}ZBT' AS ${fy}ZBT_REQUESTED,ZBT.POM_POSITION_CODE,ZBT.FISCAL_YEAR,SUM(ZBT.RESOURCE_K) AS PROP_AMT",
                $eoc_code,
                false,
                $program_code
            );

            $issue_delta_amt = $this->get_issue_summary_delta_query(
                $extract_table,
                $zbt_table,
                $ext_table,
                "LUT.PROGRAM_NAME,'${fy}ISS REQUESTED DELTA' AS ${fy}ISS_REQUESTED_DELTA,ISS_EXTRACT.FISCAL_YEAR,
                SUM(ISS_EXTRACT.DELTA_AMT) AS DELTA_AMT",
                $eoc_code,
                $program_code
            );

            $this->appendDataToBase($base_k, $fy.'EXT', $item_key, $page, '', $fy);
            $this->defaultToZero($base_k, $issue_delta_amt, 'DELTA_AMT', $fy.'ISS_REQUESTED_DELTA', $fy.'ISS REQUESTED DELTA');
            if (empty($base_k) && !empty($issue_delta_amt)) {
                $this->defaultToZero($issue_delta_amt, $base_k, 'BASE_K', $fy.'EXT', $fy.'EXT');
            }
            $this->defaultToZero($base_k, $prop_amt, 'PROP_AMT', $fy.'ZBT_REQUESTED', $fy.'ZBT');

            $delta_amt = $this->calculate_delta_amt(
                $base_k, $prop_amt, 'BASE_K', 'PROP_AMT',
                [
                    'key' => $fy.'ZBT_REQUESTED_DELTA',
                    'value'=> $fy.'ZBT DELTA'
                ]
            );

            $issue_prop_amt = $this->calculate_prop_amt(
                $prop_amt, $issue_delta_amt, 'PROP_AMT', 'DELTA_AMT',
                [
                    'key' => $fy.'ISS_REQUESTED',
                    'value'=> $fy.'ISS REQUESTED',
                    'result_key' => 'ISS_PROP_AMT'
                ]
            );


            $this->defaultToZero($base_k, $issue_prop_amt, 'ISS_PROP_AMT', $fy.'ISS_REQUESTED', $fy.'ISS REQUESTED');

            $this->sortResultByKeys($base_k,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($prop_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($delta_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($issue_prop_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($issue_delta_amt,'PROGRAM_NAME', 'FISCAL_YEAR');

            $data = [
                'base_k' => $base_k,
                'prop_amt' => $prop_amt,
                'delta_amt' => $delta_amt,
                'issue_prop_amt' => $issue_prop_amt,
                'issue_delta_amt' => $issue_delta_amt
            ];
        }
        else {
            $ext_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                false,
                $this->page_variables[$page]['type']['EXT'],
                2 - ($current_yr - $fy)
            );
            $zbt_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                false,
                $this->page_variables[$page]['type']['ZBT'],
                2 - ($current_yr - $fy)
            );

            $iss_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                false,
                $this->page_variables[$page]['type']['ISS'],
                2 - ($current_yr - $fy)
            );

            $pom_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                false,
                $this->page_variables[$page]['type']['POM'],
                2 - ($current_yr - $fy)
            );

            $base_k =  $this->get_issue_summary_fy_query(
                $ext_table,
                "LUT.PROGRAM_NAME,'{$fy}EXT' AS {$fy}EXT,EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS BASE_K",
                $fy,
                $eoc_code,
                $program_code,
                false,
                $page
            );

            $prop_amt =  $this->get_issue_summary_fy_query(
                $zbt_table,
                "LUT.PROGRAM_NAME,'{$fy}ZBT' AS {$fy}ZBT_REQUESTED,EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS PROP_AMT",
                $fy,
                $eoc_code,
                $program_code,
                false,
                $page
            );

            $issue_prop_amt =  $this->get_issue_summary_fy_query(
                $iss_table,
                "LUT.PROGRAM_NAME,'{$fy}ISS' AS {$fy}ISS_REQUESTED,EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS ISS_PROP_AMT",
                $fy,
                $eoc_code,
                $program_code,
                false,
                $page
            );

            $pom_prop_amt =  $this->get_issue_summary_fy_query(
                $pom_table,
                "LUT.PROGRAM_NAME,'{$fy}POM' AS {$fy}POM_REQUESTED,EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS POM_PROP_AMT",
                $fy,
                $eoc_code,
                $program_code,
                true,
                $page
            );
            $this->appendDataToBase($base_k, $fy.'EXT', $item_key, $page, $program, $fy, $page);

            $this->defaultToZero($base_k, $prop_amt, 'PROP_AMT', $fy.'ZBT_REQUESTED', $fy.'ZBT');
            $this->defaultToZero($base_k, $issue_prop_amt, 'ISS_PROP_AMT', $fy.'ISS_REQUESTED', $fy.'ISS');
            $this->defaultToZero($base_k, $pom_prop_amt, 'POM_PROP_AMT', $fy.'POM_REQUESTED', $fy.'POM');

            $delta_amt = $this->calculate_delta_amt(
                $base_k, $prop_amt, 'BASE_K', 'PROP_AMT',
                [
                    'key' => $fy.'ZBT_REQUESTED_DELTA',
                    'value'=>$fy.'ZBT DELTA'
                ]
            );

            $issue_delta_amt = $this->calculate_delta_amt(
                $prop_amt, $issue_prop_amt, 'PROP_AMT', 'ISS_PROP_AMT',
                [
                    'key' => $fy.'ISS_REQUESTED_DELTA',
                    'value'=>$fy.'ISS DELTA'
                ]
            );

            $pom_delta_amt = $this->calculate_delta_amt(
                $base_k, $pom_prop_amt, 'BASE_K', 'POM_PROP_AMT',
                [
                    'key' => $fy.'POM_REQUESTED_DELTA',
                    'value'=>$fy.'EXT to '. $fy . 'POM DELTA'
                ]
            );

            $this->sortResultByKeys($base_k,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($prop_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($delta_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($issue_prop_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($issue_delta_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($pom_prop_amt,'PROGRAM_NAME', 'FISCAL_YEAR');
            $this->sortResultByKeys($pom_delta_amt,'PROGRAM_NAME', 'FISCAL_YEAR');

            $data = [
                'base_k' => $base_k,
                'prop_amt' => $prop_amt,
                'delta_amt' => $delta_amt,
                'issue_prop_amt' => $issue_prop_amt,
                'issue_delta_amt' => $issue_delta_amt,
                'pom_prop_amt' => $pom_prop_amt,
                'pom_delta_amt'=> $pom_delta_amt
            ];
        } 
        return $data;
    }

    // Note: Function not in use
    // private function eoc_historical_pom_data_helper($page, $fy, $params, $program) {
    //     $table1 = $params['table1'];
    //     $table2 = $params['table2'];
    //     $table3 = $params['table3'];
    //     $table4 = $params['table4'];
    //     $table5 = $params['table5'];
    //     $table6 = $params['table6'];
    //     $l_pom_sponsor = $params['l_pom_sponsor'];
    //     $l_cap_sponsor = $params['l_cap_sponsor'];
    //     $l_ass_area = $params['l_ass_area'];
    //     $pom_prop_amt = [];
    //     $pom_delta_amt = [];
    //     $issue_prop_amt = [];
    //     $issue_delta_amt = [];
    //     $item_key = 'EOC';
    //     $this->DBs->SOCOM_UI->select("
    //         GROUP_CONCAT(DISTINCT FISCAL_YEAR ORDER BY FISCAL_YEAR SEPARATOR ', ')
    //         ");
    //     $this->DBs->SOCOM_UI->from($table2);
    //     $fiscal_years_column = $this->DBs->SOCOM_UI->get_compiled_select();
    //     if ($fy == '26' && $page == 'zbt_summary') {

    //         $base_k = $this->get_eoc_historical_summary_query(
    //             $table1,
    //             'DT_EXT_2026',
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE, EXT.POM_POSITION_CODE,'{$fy}ZBT',EXT.FISCAL_YEAR,
    //             SUM(IFNULL(ZBT_EXTRACT.RESOURCE_K,EXT.RESOURCE_K)) AS BASE_K,
    //             ($fiscal_years_column) as FISCAL_YEARS"
    //         );

    //         $delta_amt = $this->get_eoc_historical_summary_query(
    //             $table1,
    //             'DT_EXT_2026',
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE, EXT.POM_POSITION_CODE,
    //             'REQUESTED ZBT DELTA' AS 26ZBT_REQUESTED_DELTA,EXT.FISCAL_YEAR,
    //             SUM(IFNULL(ZBT_EXTRACT.DELTA_AMT,0)) AS DELTA_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             true
    //         );

    //         $this->eocAppendDataToBase($base_k, '26ZBT', $item_key);
    //         $this->eocDefaultToZero($base_k, $delta_amt, 'DELTA_AMT', '26ZBT_REQUESTED_DELTA', '26ZBT REQUESTED DELTA');

    //         $prop_amt = $this->eoc_historical_calculate_prop_amt(
    //             $base_k, $delta_amt, 'BASE_K', 'DELTA_AMT',
    //             [
    //                 'key' => '26ZBT_REQUESTED',
    //                 'value'=> '26ZBT REQUESTED',
    //                 'result_key' => 'PROP_AMT'
    //             ]
    //         );

    //         $this->eocDefaultToZero($base_k, $prop_amt, 'PROP_AMT', '26ZBT_REQUESTED', '26ZBT REQUESTED');

    //         $data = [
    //             'base_k' => $base_k,
    //             'prop_amt' => $prop_amt,
    //             'delta_amt' => $delta_amt
    //         ];

    //     } elseif ($fy == '26' && $page == 'issue') {

    //         $base_k = $this->get_eoc_historical_issue_summary_query(
    //             'DT_ZBT_2026',
    //             'DT_EXT_2026',
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "ZBT.EOC_CODE as EOC,ZBT.ASSESSMENT_AREA_CODE,ZBT.POM_SPONSOR_CODE,
    //             ZBT.CAPABILITY_SPONSOR_CODE,ZBT.RESOURCE_CATEGORY_CODE, ZBT.POM_POSITION_CODE,
    //             '${fy}EXT',ZBT.FISCAL_YEAR,
    //             SUM(ZBT.RESOURCE_K) AS BASE_K,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             true
    //         );

    //         $prop_amt = $this->get_eoc_historical_issue_summary_query(
    //             'DT_EXT_2026',
    //             'DT_ZBT_2026',
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "ZBT.EOC_CODE as EOC,ZBT.ASSESSMENT_AREA_CODE,ZBT.POM_SPONSOR_CODE,
    //             ZBT.CAPABILITY_SPONSOR_CODE,ZBT.RESOURCE_CATEGORY_CODE, ZBT.POM_POSITION_CODE,
    //             '${fy}ZBT' AS {$fy}ZBT_REQUESTED,ZBT.FISCAL_YEAR,
    //             SUM(IFNULL(ISS_EXTRACT.RESOURCE_K, ZBT.RESOURCE_K)) AS PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS"
    //         );

    //         $issue_delta_amt = $this->get_eoc_historical_issue_summary_query(
    //             'DT_EXT_2026',
    //             'DT_ZBT_2026',
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "ZBT.EOC_CODE as EOC,ZBT.ASSESSMENT_AREA_CODE,ZBT.POM_SPONSOR_CODE,
    //             ZBT.CAPABILITY_SPONSOR_CODE,ZBT.RESOURCE_CATEGORY_CODE, ZBT.POM_POSITION_CODE,
    //             '${fy}ISS REQUESTED DELTA' AS ${fy}ISS_REQUESTED_DELTA,ZBT.FISCAL_YEAR,
    //             SUM(ISS_EXTRACT.DELTA_AMT) AS DELTA_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             true
    //         );

    //         $this->appendDataToBase($base_k, '26EXT', $item_key, $page);
    //         $this->eocDefaultToZero($base_k, $prop_amt, 'PROP_AMT', '26ZBT_REQUESTED', '26ZBT');
    //         $this->eocDefaultToZero($base_k, $issue_delta_amt, 'DELTA_AMT', '26ISS_REQUESTED_DELTA', '26ISS REQUESTED DELTA');


    //         $delta_amt = $this->eoc_calculate_delta_amt(
    //             $base_k, $prop_amt, 'BASE_K', "PROP_AMT",
    //             [
    //                 'key' => '26ZBT_REQUESTED_DELTA',
    //                 'value'=> '26ZBT DELTA'
    //             ]
    //         );

    //         $issue_prop_amt = $this->eoc_historical_calculate_prop_amt(
    //             $prop_amt, $issue_delta_amt, 'PROP_AMT', 'DELTA_AMT',
    //             [
    //                 'key' => '26ISS_REQUESTED',
    //                 'value'=> '26ISS REQUESTED',
    //                 'result_key' => 'ISS_PROP_AMT'
    //             ]
    //         );

    //         $this->sortResultByKeys($base_k,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($delta_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($issue_prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($issue_delta_amt,'EOC', 'FISCAL_YEAR');
    
    //         $data = [
    //             'base_k' => $base_k,
    //             'prop_amt' => $prop_amt,
    //             'delta_amt' => $delta_amt,
    //             'issue_prop_amt' => $issue_prop_amt,
    //             'issue_delta_amt' => $issue_delta_amt
    //         ];
    //     }
    //     elseif ($page == 'issue') {

    //         //EXT
    //         $base_k = $this->get_eoc_historical_issue_summary_query(
    //             'DT_ZBT_2026',
    //             'DT_EXT_20'.$fy,
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "ZBT.EOC_CODE as EOC,ZBT.ASSESSMENT_AREA_CODE,ZBT.POM_SPONSOR_CODE,
    //             ZBT.CAPABILITY_SPONSOR_CODE,ZBT.RESOURCE_CATEGORY_CODE, ZBT.POM_POSITION_CODE,
    //             '${fy}EXT',ZBT.FISCAL_YEAR,
    //             SUM(ZBT.RESOURCE_K) AS BASE_K,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             true
    //         );

    //         //ZBT
    //         $prop_amt = $this->get_eoc_historical_issue_summary_query(
    //             'DT_ZBT_2026',
    //             'DT_ZBT_20'.$fy,
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "ZBT.EOC_CODE as EOC,ZBT.ASSESSMENT_AREA_CODE,ZBT.POM_SPONSOR_CODE,
    //             ZBT.CAPABILITY_SPONSOR_CODE,ZBT.RESOURCE_CATEGORY_CODE, ZBT.POM_POSITION_CODE,
    //             '${fy}ZBT' AS '${fy}ZBT_REQUESTED',ZBT.FISCAL_YEAR,
    //             SUM(ZBT.RESOURCE_K) AS PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             true
    //         );

    //         //ISS
    //         $issue_prop_amt = $this->get_eoc_historical_issue_summary_query(
    //             'DT_ZBT_2026',
    //             'DT_ISS_20'.$fy,
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "ZBT.EOC_CODE as EOC,ZBT.ASSESSMENT_AREA_CODE,ZBT.POM_SPONSOR_CODE,
    //             ZBT.CAPABILITY_SPONSOR_CODE,ZBT.RESOURCE_CATEGORY_CODE, ZBT.POM_POSITION_CODE,
    //             '{$fy}ISS' AS {$fy}ISS_REQUESTED,ZBT.FISCAL_YEAR,
    //             SUM(ZBT.RESOURCE_K) AS ISS_PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             true
    //         );

    //         $this->eocAppendDataToBase($base_k, $fy.'EXT', $item_key);

    //         $delta_amt = $this->eoc_calculate_delta_amt(
    //             $base_k, $prop_amt, 'BASE_K', 'PROP_AMT',
    //             [
    //                 'key' => $fy.'ZBT_REQUESTED_DELTA',
    //                 'value'=>$fy.'ZBT DELTA'
    //             ]
    //         );

    //         $issue_delta_amt = $this->eoc_calculate_delta_amt(
    //             $prop_amt, $issue_prop_amt, 'PROP_AMT', 'ISS_PROP_AMT',
    //             [
    //                 'key' => $fy.'ISS_REQUESTED_DELTA',
    //                 'value'=>$fy.'ISS DELTA'
    //             ]
    //         );

    //         $pom_prop_amt = $this->get_eoc_historical_issue_summary_pom_query(
    //             'DT_ZBT_2026',
    //             'DT_POM_20'.$fy,
    //             'DT_ISS_EXTRACT_2026',
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE,
    //             '{$fy}POM' AS {$fy}POM_REQUESTED,
    //             EXT.FISCAL_YEAR,
    //             SUM(EXT.RESOURCE_K) AS POM_PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS"
    //         );

    //         $pom_delta_amt = $this->eoc_calculate_delta_amt(
    //             $base_k, $pom_prop_amt, 'BASE_K', 'POM_PROP_AMT',
    //             [
    //                 'key' => $fy.'POM_REQUESTED_DELTA',
    //                 'value'=>$fy.'EXT to '. $fy . 'POM Delta'
    //             ]
    //         );

    //         $this->eocDefaultToZero($base_k, $prop_amt, 'PROP_AMT', $fy.'ZBT_REQUESTED', $fy.'ZBT');
    //         $this->eocDefaultToZero($base_k, $issue_prop_amt, 'ISS_PROP_AMT', $fy.'ISS_REQUESTED', $fy.'ISS');
    //         $this->eocDefaultToZero($base_k, $issue_delta_amt, 'DELTA_AMT', $fy.'ISS_REQUESTED_DELTA', $fy.'ISS DELTA');
    //         $this->eocDefaultToZero($base_k, $pom_prop_amt, 'POM_PROP_AMT', $fy.'POM_REQUESTED', $fy.'POM');
    //         $this->eocDefaultToZero($base_k, $pom_delta_amt, 'DELTA_AMT', $fy.'POM_REQUESTED_DELTA', $fy.'EXT to '. $fy . 'POM DELTA');

    //         $this->sortResultByKeys($base_k,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($delta_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($issue_prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($issue_delta_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($pom_prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($pom_delta_amt,'EOC', 'FISCAL_YEAR');

    //         $data = [
    //             'base_k' => $base_k,
    //             'prop_amt' => $prop_amt,
    //             'delta_amt' => $delta_amt,
    //             'issue_prop_amt' => $issue_prop_amt,
    //             'issue_delta_amt' => $issue_delta_amt,
    //             'pom_prop_amt' => $pom_prop_amt,
    //             'pom_delta_amt'=> $pom_delta_amt
    //         ];
    //     }
    //     else {

    //         $base_k = $this->get_eoc_historical_summary_query(
    //             'DT_ZBT_EXTRACT_2026',
    //             $table2,
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE, EXT.POM_POSITION_CODE,'{$fy}EXT',EXT.FISCAL_YEAR,
    //             SUM(EXT.RESOURCE_K) AS BASE_K,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             $fy
    //         );

    //         $this->eocAppendDataToBase($base_k, $fy.'EXT', $item_key);

    //         $prop_amt = $this->get_eoc_historical_summary_query(
    //             'DT_ZBT_EXTRACT_2026',
    //             $table5,
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE, EXT.POM_POSITION_CODE,'{$fy}ZBT' AS {$fy}ZBT_REQUESTED,EXT.FISCAL_YEAR,
    //             SUM(EXT.RESOURCE_K) AS PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             $fy
    //         );

    //         $delta_amt = $this->eoc_calculate_delta_amt(
    //             $base_k, $prop_amt, 'BASE_K', 'PROP_AMT',
    //             [
    //                 'key' => $fy.'ZBT_REQUESTED_DELTA',
    //                 'value'=>$fy.'ZBT DELTA'
    //             ]
    //         );

    //         $issue_prop_amt = $this->get_eoc_historical_summary_query(
    //             'DT_ZBT_EXTRACT_2026',
    //             $table4,
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE, EXT.POM_POSITION_CODE,
    //             '{$fy}ISS' AS {$fy}ISS_REQUESTED,
    //             EXT.FISCAL_YEAR,
    //             SUM(EXT.RESOURCE_K) AS ISS_PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             $fy
    //         );

    //         $issue_delta_amt = $this->eoc_calculate_delta_amt(
    //             $prop_amt, $issue_prop_amt, 'PROP_AMT', 'ISS_PROP_AMT',
    //             [
    //                 'key' => $fy.'ISS_REQUESTED_DELTA',
    //                 'value'=>'ISS DELTA'
    //             ]
    //         );

    //         $pom_prop_amt = $this->get_eoc_historical_summary_query(
    //             'DT_ZBT_EXTRACT_2026',
    //             $table6,
    //             $program,
    //             "EXT.EOC_CODE as EOC,EXT.ASSESSMENT_AREA_CODE,EXT.POM_SPONSOR_CODE,
    //             EXT.CAPABILITY_SPONSOR_CODE,EXT.RESOURCE_CATEGORY_CODE,
    //             '{$fy}POM' AS {$fy}POM_REQUESTED,
    //             EXT.FISCAL_YEAR,
    //             SUM(EXT.RESOURCE_K) AS POM_PROP_AMT,
    //             ($fiscal_years_column) as FISCAL_YEARS",
    //             false,
    //             $fy,
    //             true
    //         );


    //         $pom_delta_amt = $this->eoc_calculate_delta_amt(
    //             $base_k, $pom_prop_amt, 'BASE_K', 'POM_PROP_AMT',
    //             [
    //                 'key' => $fy.'POM_REQUESTED_DELTA',
    //                 'value'=>$fy.'EXT to '. $fy . 'POM Delta'
    //             ]
    //         );

    //         $this->eocDefaultToZero($base_k, $prop_amt, 'PROP_AMT', $fy.'ZBT_REQUESTED', $fy.'ZBT');
    //         $this->eocDefaultToZero($base_k, $issue_prop_amt, 'ISS_PROP_AMT', $fy.'ISS_REQUESTED', $fy.'ISS');
    //         $this->eocDefaultToZero($base_k, $issue_delta_amt, 'DELTA_AMT', $fy.'ISS_REQUESTED_DELTA', $fy.'ISS DELTA');
    //         $this->eocDefaultToZero($base_k, $pom_prop_amt, 'POM_PROP_AMT', $fy.'POM_REQUESTED', $fy.'POM');
    //         $this->eocDefaultToZero($base_k, $pom_delta_amt, 'DELTA_AMT', $fy.'POM_REQUESTED_DELTA', $fy.'EXT to '. $fy . 'POM DELTA');

    //         $this->sortResultByKeys($base_k,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($delta_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($issue_prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($issue_delta_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($pom_prop_amt,'EOC', 'FISCAL_YEAR');
    //         $this->sortResultByKeys($pom_delta_amt,'EOC', 'FISCAL_YEAR');

    //         $data = [
    //             'base_k' => $base_k,
    //             'prop_amt' => $prop_amt,
    //             'delta_amt' => $delta_amt,
    //             'issue_prop_amt' => $issue_prop_amt,
    //             'issue_delta_amt' => $issue_delta_amt,
    //             'pom_prop_amt' => $pom_prop_amt,
    //             'pom_delta_amt'=> $pom_delta_amt
    //         ];
    //     }
    //     return $data;
    // }
    
    public function program_summary_count($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $page, $program_list) {
        
        $this->DBs->SOCOM_UI->select("
            IF(CAPABILITY_SPONSOR_CODE REGEXP '^SORDAC', 'SOF AT&L',
            CAPABILITY_SPONSOR_CODE) as CAP,
            COUNT(DISTINCT(EVENT_NAME)) as COUNT
        ");
        $this->DBs->SOCOM_UI->from($table1);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->where_in('PROGRAM_GROUP',  $program_list);
        $this->DBs->SOCOM_UI->group_by('CAP');
        $this->DBs->SOCOM_UI->order_by('CAP');

        $inner_query = $this->DBs->SOCOM_UI->get_compiled_select();
        $this->DBs->SOCOM_UI->select('
            SUM(A.COUNT) AS TOTAL_EVENTS
        ');
        $this->DBs->SOCOM_UI->from('('. $inner_query . ')  as A');
        $total_zbt_events_result = $this->DBs->SOCOM_UI->get()->row_array();

        return isset($total_zbt_events_result['TOTAL_EVENTS'])
            ? $total_zbt_events_result['TOTAL_EVENTS'] : 0;
    }
    
    public function program_summary_dollars_moved($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list) {       
        // Get dollars_moved
        $this->DBs->SOCOM_UI->select('
            SUM(DELTA_AMT) AS SUM_DELTA_AMT
        ');
        $this->DBs->SOCOM_UI->from($table1);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->where_in('PROGRAM_GROUP',  $program_list);
        $this->DBs->SOCOM_UI->where('DELTA_AMT > ', 0);
        $inner_query = $this->DBs->SOCOM_UI->get_compiled_select();
        $this->DBs->SOCOM_UI->select('
            SUM(B.SUM_DELTA_AMT) AS TOTAL_POS_DOLLARS
        ');
        $this->DBs->SOCOM_UI->from('('. $inner_query . ')  as B');

        $dollars_moved_result = $this->DBs->SOCOM_UI->get()->row_array();
        return isset($dollars_moved_result['TOTAL_POS_DOLLARS'])
            ?
            $this->format_dollar($dollars_moved_result['TOTAL_POS_DOLLARS'])
            :
            0;
    }
    
    public function program_summary_net_change($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list) {
        $this->DBs->SOCOM_UI->select('
            SUM(DELTA_AMT) AS SUM_DELTA_AMT
        ');
        $this->DBs->SOCOM_UI->from($table1);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->where_in('PROGRAM_GROUP',  $program_list);
        $net_change_result = $this->DBs->SOCOM_UI->get()->row_array();
        return isset($net_change_result['SUM_DELTA_AMT'])
            ?
            $this->format_dollar(
                $net_change_result['SUM_DELTA_AMT']
            )
            :
            0;
    }
    
    public function issue_program_summary_card(
        $page, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list
    ) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );
        return [
            'total_events' => $this->program_summary_count(
                $table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status,'ISS', $program_list
            ),
            'dollars_moved' => $this->program_summary_dollars_moved(
                $table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list
            ),
            'net_change' =>  $this->program_summary_net_change(
                $table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_approval_status, $program_list
            )
        ];
    }
    /**
	 * Retrives names for product criteria (WEIGHTS)
	 */
	public function get_option_criteria_names() {
        $criteria_name_id = get_criteria_name_id();
		
        return $this->DBs->SOCOM_UI
            ->select('CRITERIA_TERM as CRITERIA')
            ->from('USR_LOOKUP_USER_CRITERIA_TERMS')
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->order_by('CRITERIA_TERM ASC')
            ->get()
            ->result_array() ?? [];
	}

    public function get_option_criteria_names_and_description() {
        $criteria_name_id = get_criteria_name_id();
		
        return $this->DBs->SOCOM_UI
            ->select('CRITERIA_TERM as CRITERIA')
            ->select('CRITERIA_DESCRIPTION')
            ->from('USR_LOOKUP_USER_CRITERIA_TERMS')
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->order_by('CRITERIA_TERM ASC')
            ->get()
            ->result_array() ?? [];
	}
    //prefer do it on data side
    private function defaultToZero($base_k, &$target_amt, $key1, $header_key, $header_value) {
        $target_amt_map = [];

        foreach ($target_amt as $idx => $item) {
            $key = $item['PROGRAM_NAME'] . '-' . $item['FISCAL_YEAR'];
          
            if (!$item[$key1]) {
                $target_amt[$idx][$key1] = 0;
            }

            $target_amt_map[$key] = $target_amt[$idx][$key1];
        }

        foreach ($base_k as $item) {
            $key = $item['PROGRAM_NAME'] . '-' . $item['FISCAL_YEAR'];
            
            if  (!isset($target_amt_map[$key])) {
                $target_amt [] = [
                    "PROGRAM_NAME" => $item['PROGRAM_NAME'],
                    $header_key => $header_value,
                    $key1 => 0,
                    "POM_POSITION_CODE" => $item["POM_POSITION_CODE"],
                    "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                    "FISCAL_YEARS" => $item['FISCAL_YEARS']
                ];
            }
        }
    }

    //prefer do it on data side
    private function eocDetailsDefaultToZero($base_k, &$target_amt, $key1, $header_key, $header_value) {
        $target_amt_map = [];

        foreach ($target_amt as $idx => $item) {
            $key = $item['EOC'] . '-' . $item['EVENT_NAME'] . '-' . $item['ASSESSMENT_AREA_CODE'] . '-' .
                $item['POM_SPONSOR_CODE'] . '-' . $item['CAPABILITY_SPONSOR_CODE'] . '-' .
                $item['RESOURCE_CATEGORY_CODE'] . '-' . $item['OSD_PROGRAM_ELEMENT_CODE'] . '-' .
                $item['SPECIAL_PROJECT_CODE'];
            
            if (!$item[$key1]) {
                $target_amt[$idx][$key1] = 0;
            }

            $target_amt_map[$key] = $target_amt[$idx][$key1];
        }

        foreach ($base_k as $item) {
            $key = $item['EOC'] . '-' . $item['EVENT_NAME'] . '-' . $item['ASSESSMENT_AREA_CODE'] . '-' .
                $item['POM_SPONSOR_CODE'] . '-' . $item['CAPABILITY_SPONSOR_CODE'] . '-' .
                $item['RESOURCE_CATEGORY_CODE'] . '-' . $item['OSD_PROGRAM_ELEMENT_CODE'] . '-' .
                $item['SPECIAL_PROJECT_CODE'];
            
            if  (!isset($target_amt_map[$key])) {
                $target_amt [] = [
                    "EOC" =>  $item['EOC'],
                    "EVENT_NAME" => $item['EVENT_NAME'],
                    "EVENT_JUSTIFICATION" => $item['EVENT_JUSTIFICATION'],
                    "POM_POSITION_CODE" => $item['POM_POSITION_CODE'],
                    $header_key => $header_value,
                    "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
                    "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
                    "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
                    "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
                    $key1 => 0,
                    "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                    "FISCAL_YEARS" => $item['FISCAL_YEARS']
                ];

            }
        }
    }

    //prefer do it on data side
    private function appendDataToBase(&$base_k, $header_key, $item_key, $page, $program='', $current_fy='') {
        $fys = $this->page_variables[$page]['year_list'];

        if (!empty($base_k)) {
            $base_k_map = [];
            foreach ($base_k as $item) {
                $key = $item[$item_key] . '-' . $item['FISCAL_YEAR'];
                $base_k_map[$key] = $item['BASE_K'];
            }

            foreach ($base_k as $idx => $item) {

                foreach( $fys as $fy) {
                    $key = $item[$item_key] . '-' . $fy;
                    if  (!isset($base_k_map[$key])) {
                        $base_k [] = [
                            $item_key => $item[$item_key],
                            $header_key => $item[$header_key],
                            "BASE_K" => 0,
                            "POM_POSITION_CODE" => $item["POM_POSITION_CODE"],
                            "FISCAL_YEAR" => $fy,
                            "FISCAL_YEARS" => $item['FISCAL_YEARS']
                        ];
                        $base_k_map[$key] = 0;
                    }
                }
            }
        }
        else {
            if ($program) {
                foreach($fys as $fy) {
                    $base_k [] = [
                        "PROGRAM_NAME" => "BAD THINGS",
                        $current_fy."EXT" => $current_fy."EXT",
                        "POM_POSITION_CODE" => "26EXT",
                        "PROGRAM_NAME" => $program,
                        "FISCAL_YEAR" => $fy,
                        "BASE_K" => 0,
                        "FISCAL_YEARS" => "2024, 2025, 2026, 2027, 2028"
                    ];
                }
            }
        }
    }
    
    private function eocDefaultToZero($base_k, &$target_amt, $key1, $header_key, $header_value) {
        $target_amt_map = [];

        foreach ($target_amt as $item) {
            $key = $item['EOC'] . '-' . $item['FISCAL_YEAR'];
            $target_amt_map[$key] = $item[$key1];
        }

        foreach ($base_k as $item) {
            $key = $item['EOC'] . '-' . $item['FISCAL_YEAR'];
            
            if  (!isset($target_amt_map[$key])) {
                $target_amt [] = [
                    "EOC" => $item['EOC'],
                    $header_key => $header_value,
                    $key1 => 0,
                    "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
                    "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
                    "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
                    "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
                    "FISCAL_YEAR" => $item['FISCAL_YEAR'],
                    "FISCAL_YEARS" => $item['FISCAL_YEARS']
                ];
            }
        }
    }

    //prefer do it on data side
    private function eocDetailsAppendDataToBase(&$base_k, $header_key, $item_key, $fys, $base_key='BASE_K') {
        $base_k_map = [];
        foreach ($base_k as $item) {
            $key = $item[$item_key] . '-' . $item['EVENT_NAME'] . '-' . $item['ASSESSMENT_AREA_CODE'] . '-' .
                $item['POM_SPONSOR_CODE'] . '-' . $item['CAPABILITY_SPONSOR_CODE'] . '-' .
                $item['RESOURCE_CATEGORY_CODE'] . '-' . $item['OSD_PROGRAM_ELEMENT_CODE'] . '-' . 
                $item['SPECIAL_PROJECT_CODE'];
            $base_k_map[$key] = $item[$base_key];
        }

        foreach ($base_k as $idx => $item) {

            foreach( $fys as $fy) {
                $key = $item[$item_key] . '-' . $item['EVENT_NAME'] . '-' . $item['ASSESSMENT_AREA_CODE'] . '-' .
                    $item['POM_SPONSOR_CODE'] . '-' . $item['CAPABILITY_SPONSOR_CODE'] . '-' .
                    $item['RESOURCE_CATEGORY_CODE'] . '-' . $item['OSD_PROGRAM_ELEMENT_CODE'] . '-' .
                    $item['SPECIAL_PROJECT_CODE'];
                if  (!isset($base_k_map[$key])) {
                    $base_k [] = [
                        "EOC" => $item[$item_key],
                        "EVENT_NAME" => $item['EVENT_NAME'],
                        "EVENT_JUSTIFICATION" => $item['EVENT_JUSTIFICATION'],
                        "POM_POSITION_CODE" => $item['POM_POSITION_CODE'],
                        $header_key => $item[$header_key],
                        "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
                        "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
                        "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
                        "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
                        "FISCAL_YEAR" => $fy,
                        $base_key => 0,
                        "FISCAL_YEARS" => $item['FISCAL_YEARS']
                    ];

                    $base_k_map[$key] = 0;
                }
            }
        }
    }


    //prefer do it on data side
    // private function eocAppendDataToBase(&$base_k, $header_key, $item_key) {
    //     $fys = [2026, 2027, 2028, 2029, 2030];
        
    //     $base_k_map = [];
    //     foreach ($base_k as $item) {
    //         $key = $item[$item_key] . '-' . $item['FISCAL_YEAR'];
    //         $base_k_map[$key] = $item['BASE_K'];
    //     }

    //     foreach ($base_k as $idx => $item) {

    //         foreach( $fys as $fy) {
    //             $key = $item[$item_key] . '-' . $fy;
    //             if  (!isset($base_k_map[$key])) {
    //                 $base_k [] = [
    //                     $item_key => $item[$item_key],
    //                     $header_key => $item[$header_key],
    //                     "BASE_K" => 0,
    //                     "ASSESSMENT_AREA_CODE" => $item["ASSESSMENT_AREA_CODE"],
    //                     "POM_SPONSOR_CODE" => $item["POM_SPONSOR_CODE"],
    //                     "CAPABILITY_SPONSOR_CODE" => $item["CAPABILITY_SPONSOR_CODE"],
    //                     "RESOURCE_CATEGORY_CODE" => $item["RESOURCE_CATEGORY_CODE"],
    //                     "FISCAL_YEAR" => $fy,
    //                     "FISCAL_YEARS" => $item['FISCAL_YEARS']
    //                 ];
    //                 $base_k_map[$key] = 0;
    //             }
    //         }
    //     }
    // }

    private function sortResultByKeys(&$data, $key1, $key2) {
        // Sort the data using the custom comparison function
        usort($data, function($a, $b) use($key1, $key2) {
            if ($a[$key1] == $b[$key1]) {
                return $a[$key2] - $b[$key2];
            }
            return strcmp($a[$key1], $b[$key1]);
        });
    }

    private function sortResultByMultiKeys(&$data, $key1, $key2, $key3) {
        // Sort the data using the custom comparison function
        usort($data, function($a, $b) use($key1, $key2, $key3) {
            if ($a[$key1] < $b[$key1]) return -1;
            if ($a[$key1] > $b[$key1]) return 1;
        
            if ($a[$key2] < $b[$key2]) return -1;
            if ($a[$key2] > $b[$key2]) return 1;
        
            if ($a[$key3] < $b[$key3]) return -1;
            if ($a[$key3] > $b[$key3]) return 1;
        
            return 0;
        });
    }

    public function get_program_scored() {
        $criteria_name_id = get_criteria_name_id();
        $user_id = (int)$this->session->userdata['logged_in']['id'];

        $program_id = $this->DBs->SOCOM_UI
            ->select('LUT.ID as PROGRAM_ID')
            ->distinct()
            ->from('LOOKUP_PROGRAM LUT')
            ->join('DT_ISS_2026 POS', ' POS.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND POS.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND POS.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND POS.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND POS.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE', 'join')
            ->join('USR_OPTION_SCORES SCORE', ' `LUT`.`ID` = `SCORE`.`PROGRAM_ID`', 'join')
            ->where('SCORE.USER_ID', $user_id)
            ->where('SCORE.CRITERIA_NAME_ID', $criteria_name_id)
            ->get()
            ->result_array() ?? [];

        return $program_id;
    }

    protected function get_assessment_area_code_program_group($user_aac, $program_codes) {
        $assessment_area_codes = array_column(
            $this->SOCOM_Assessment_Area_model->get_assessment_area(),
            'ASSESSMENT_AREA_CODE'
        );

        $accs = [];
        foreach ($user_aac as $acc) {
            if (in_array($acc, $assessment_area_codes, true)) {
                $accs[] = $this->DBs->SOCOM_UI->escape($acc);
            }
        }
        if (!empty($accs)) {
            $assessment_area_code = sprintf(" AND ASSESSMENT_AREA_CODE IN ( %s )", implode(", ", $accs));
            
            if (!empty($program_codes) && !in_array('ALL', $program_codes)) {
                $db = $this->DBs->SOCOM_UI;
                $program_codes = array_map(
                    function($var) use($db) { return  $db->escape(trim($var)); },
                    $program_codes
                );
                
                $assessment_area_code .= sprintf(" AND PROGRAM_GROUP IN ( %s )", implode(", ", $program_codes));
            }
        } else {
            $assessment_area_code = '';
        }

        return $assessment_area_code;
    }

    private function get_program_issue($scored=false, array $user_aac=[], array $program_names=[]) {

        $assessment_area_code = $this->get_assessment_area_code_program_group($user_aac, $program_names);
        $criteria_name_id = get_criteria_name_id();
        $user_id = (int)$this->session->userdata['logged_in']['id'];

        // Development fallback for missing variables
        if (ENVIRONMENT === 'development') {
            if (!isset($this->page_variables['issue']['subapp'])) {
                $this->page_variables['issue']['subapp'] = 'ISS_SUMMARY';
            }
            if (!isset($this->page_variables['issue']['type']['EXTRACT'])) {
                $this->page_variables['issue']['type']['EXTRACT'] = 'EXTRACT';
            }
            if (!isset($this->ISS_YEAR_LIST)) {
                $this->ISS_YEAR_LIST = ['2024', '2025', '2026', '2027', '2028'];
            }
        }

        $lookup_program = 'LOOKUP_PROGRAM';
        $even_name = 'EVENT_NAME IS NOT NULL';
        $table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXTRACT']
        );
        $year_list = implode(', ', $this->ISS_YEAR_LIST);

        $query = <<<EOT
        SELECT
            JSON_OBJECTAGG(POS.FISCAL_YEAR, POS.RESOURCE_K) as FY,
            LUT.ID as PROGRAM_ID,
            POS.PROGRAM_CODE,
            POS.PROGRAM_GROUP,
            POS.CAPABILITY_SPONSOR_CODE,
            POS.RESOURCE_CATEGORY_CODE,
            LS.TOTAL_SCORE as storm,
            LUT.STORM_ID as storm_id,
            POS.`ASSESSMENT_AREA_CODE`,
            POS.POM_SPONSOR_CODE,
            POS.EXECUTION_MANAGER_CODE,
            POS.EOC_CODE,
            POS.OSD_PROGRAM_ELEMENT_CODE,
            POS.`EVENT_NAME`
        FROM(
                SELECT
                    A.ASSESSMENT_AREA_CODE,
                    A.CAPABILITY_SPONSOR_CODE,
                    FISCAL_YEAR,
                    SUM(DELTA_AMT) AS RESOURCE_K,
                    A.PROGRAM_GROUP,
                    A.RESOURCE_CATEGORY_CODE,
                    A.PROGRAM_CODE,
                    A.POM_SPONSOR_CODE,
                    A.EXECUTION_MANAGER_CODE,
                    A.EOC_CODE,
                    A.OSD_PROGRAM_ELEMENT_CODE,
                    A.EVENT_NAME,
                    A.PROGRAM_ID
                FROM
                    {$table} as A
                GROUP BY
                    A.PROGRAM_ID,
                    FISCAL_YEAR
            ) POS
            JOIN(
                SELECT
                    ID,
                    STORM_ID,
                    EVENT_NAME
                FROM
                    {$lookup_program}
                WHERE
                    {$even_name}
                    {$assessment_area_code}
                GROUP BY
                    ID
            ) AS LUT ON POS.PROGRAM_ID = LUT.ID
            LEFT JOIN LOOKUP_STORM LS ON LS.ID = LUT.STORM_ID
        WHERE
            POS.RESOURCE_K > 0
        GROUP BY
            POS.PROGRAM_ID
        ORDER BY
            POS.PROGRAM_CODE
        EOT;
       
        #echo $query;die;
        $result = $this->DBs->SOCOM_UI->query($query)->result_array();
        //print_r($query);
        $query =<<<EOT
        SELECT 
            `ID` as `SCORE_ID`, `SESSION` as `SCORE_SESSION`, PROGRAM_ID
        FROM
            `USR_OPTION_SCORES`
        WHERE
            `USER_ID` = {$user_id} AND CRITERIA_NAME_ID = {$criteria_name_id}
        EOT;
            $scores = $this->DBs->SOCOM_UI->query($query)->result_array();
 
        return [
            'data' => $result,
            'year_list' => array_map('intval', explode(',', $year_list)),
            'scores' => array_combine(array_column($scores, 'PROGRAM_ID'), $scores)
        ];
    }

    public function get_program($scored=false, array $user_aac=[], array $program_names=[], bool $iss_extract=false) {
        
        $assessment_area_code = $this->get_assessment_area_code_program_group($user_aac, $program_names);
        
        $criteria_name_id = get_criteria_name_id();
        
        $user_id = (int)($this->session->userdata['logged_in']['id'] ?? 1);
        
        if ($iss_extract === false) {
            $lookup_program = 'LOOKUP_PROGRAM';
            $even_name = 'EVENT_NAME IS NULL';
            $table = $this->dynamic_year->getTable(
                $this->page_variables['coa']['subapp'],
                true,
                $this->page_variables['coa']['type']['ISS']
            );

            // Debug output for development
            if (is_dev_bypass_enabled()) {
                error_log("DEBUG: Table name in get_program: " . $table);
            }

            $year_list = implode(', ', $this->COA_YEAR_LIST);
            $jsonObjectAgg = [
                'JSON_OBJECTAGG(FISCAL_YEAR, RESOURCE_K)',
                'SUM(RESOURCE_K)',
                'SUM(IFNULL(RESOURCE_K,0)) AS SUM',
                "CASE WHEN B.SUM > 0 THEN 'FUNDED' ELSE 'NOT FUNDED' END AS FUNDED",
                "C.FUNDED NOT IN ('NOT FUNDED')",
                ''
            ];
            $event = [
                '',
                '',
                '',
                '',
                '',
            ];


            $pom_join = <<<EOT
                %s.PROGRAM_CODE = %s.PROGRAM_CODE AND 
                %s.POM_SPONSOR_CODE = %s.POM_SPONSOR_CODE AND
                %s.CAPABILITY_SPONSOR_CODE = %s.CAPABILITY_SPONSOR_CODE AND 
                %s.ASSESSMENT_AREA_CODE = %s.ASSESSMENT_AREA_CODE AND 
                %s.EXECUTION_MANAGER_CODE = %s.EXECUTION_MANAGER_CODE AND
                %s.RESOURCE_CATEGORY_CODE = %s.RESOURCE_CATEGORY_CODE AND
                %s.EOC_CODE = %s.EOC_CODE AND
                %s.OSD_PROGRAM_ELEMENT_CODE = %s.OSD_PROGRAM_ELEMENT_CODE
EOT;
            $pom_join_1 = sprintf($pom_join, ...['POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT']);
            $pom_join_2 = sprintf($pom_join, ...['A', 'C', 'A', 'C', 'A', 'C', 'A', 'C', 'A', 'C', 'A', 'C', 'A', 'C', 'A', 'C']);

            $rcc = 'RESOURCE_CATEGORY_CODE,';

            $g_fields = ' %sPOM_SPONSOR_CODE, %sEXECUTION_MANAGER_CODE, %sEOC_CODE, %sOSD_PROGRAM_ELEMENT_CODE';
            $g1_fields = ','. sprintf($g_fields, ...['', '', '', '']);
            $g2_fields = ','. sprintf($g_fields, ...['B.','B.', 'B.', 'B.']);
            $g3_fields = ','. sprintf($g_fields, ...['A.', 'A.', 'A.', 'A.']);
            $g4_fields = ','. sprintf($g_fields, ...['POS.', 'POS.', 'POS.', 'POS.']);
            
            $group_by = '%sPROGRAM_CODE , %sPOM_SPONSOR_CODE , %sCAPABILITY_SPONSOR_CODE, %sASSESSMENT_AREA_CODE, %sEXECUTION_MANAGER_CODE, %sRESOURCE_CATEGORY_CODE, %sEOC_CODE, %sOSD_PROGRAM_ELEMENT_CODE';

            $primary_group_1 = sprintf($group_by, ...['', '', '', '', '', '', '', '', '']);
            $primary_group_fy =  sprintf($group_by, ...['C.', 'C.', 'C.', 'C.', 'C.', 'C.', 'C.', 'C.', 'C.']);
            $primary_group_2 = sprintf($group_by, ...['B.', 'B.', 'B.', 'B.', 'B.', 'B.', 'B.', 'B.', 'B.']);
            $primary_group_3 = sprintf($group_by, ...['POS.', 'POS.', 'POS.', 'POS.', 'POS.', 'POS.', 'POS.', 'POS.', 'POS.']);


        } else {
            return $this->get_program_issue($scored, $user_aac, $program_names);
//             $even_name = 'EVENT_NAME IS NOT NULL';
//             $table = $this->dynamic_year->getTable(
//                 $this->page_variables['issue']['subapp'],
//                 true,
//                 $this->page_variables['issue']['type']['EXTRACT']
//             );

//             $year_list = implode(', ', $this->ISS_YEAR_LIST);
//             $jsonObjectAgg = [
//                 'JSON_OBJECTAGG(FISCAL_YEAR, RESOURCE_K)',
//                 'SUM(DELTA_AMT)',
//                 'SUM(IFNULL(RESOURCE_K,0)) AS SUM, SUM(IFNULL(DELTA_AMT,0)) AS SUMD ',
//                 "CASE WHEN B.SUM > 0 THEN 'FUNDED' ELSE 'NOT FUNDED' END AS FUNDED,
//                 CASE WHEN B.SUMD > 0 THEN 'FUNDED' ELSE 'NOT FUNDED' END AS FUNDEDD ",
//                 "C.FUNDEDD NOT IN ('NOT FUNDED')",
//                 'DELTA_AMT, '
//             ];

//             $event = [
//                 ', POS.`EVENT_NAME`',
//                 ', A.EVENT_NAME',
//                 ', EVENT_NAME',
//                 ', B.EVENT_NAME',
//                 ', EVENT_NAME',
//             ];

//             $pom_join =<<<EOT
// %s.PROGRAM_CODE = %s.PROGRAM_CODE AND 
// %s.CAPABILITY_SPONSOR_CODE = %s.CAPABILITY_SPONSOR_CODE AND 
// %s.ASSESSMENT_AREA_CODE = %s.ASSESSMENT_AREA_CODE AND 
// %s.POM_SPONSOR_CODE = %s.POM_SPONSOR_CODE
// EOT;
//             $pom_join_1 = sprintf($pom_join, ...['POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT', 'POS', 'LUT']);
//             $pom_join_2 = sprintf($pom_join, ...['A', 'C', 'A', 'C', 'A', 'C', 'A', 'C']);

//             $g_fields = ' %sPOM_SPONSOR_CODE, %sEXECUTION_MANAGER_CODE, %sEOC_CODE, %sOSD_PROGRAM_ELEMENT_CODE';
//             $g1_fields = ','. sprintf($g_fields, ...['', '', '', '']);
//             $g2_fields = ','. sprintf($g_fields, ...['B.','B.', 'B.', 'B.']);
//             $g3_fields = ','. sprintf($g_fields, ...['A.', 'A.', 'A.', 'A.']);
//             $g4_fields = ','. sprintf($g_fields, ...['POS.', 'POS.', 'POS.', 'POS.']);

//             $rcc = '';

//             $group_by = '%sPROGRAM_CODE , %sPOM_SPONSOR_CODE , %sCAPABILITY_SPONSOR_CODE, %sASSESSMENT_AREA_CODE';
            
//             $primary_group_1 = sprintf($group_by, ...['', '', '', '']);
//             $primary_group_fy = sprintf($group_by, ...['C.', 'C.', 'C.', 'C.']);

//             $primary_group_2 = sprintf($group_by, ...['B.', 'B.', 'B.', 'B.']);
//             $primary_group_3 = sprintf($group_by, ...['POS.', 'POS.', 'POS.', 'POS.']);
        }

        if ($scored === true) {
            $join_scores = " JOIN
                (SELECT 
                    `ID`, `NAME`, `DESCRIPTION`, `PROGRAM_ID`, `SESSION`, `USER_ID`, `CRITERIA_NAME_ID`
                FROM
                    `USR_OPTION_SCORES`
                WHERE
                    `USER_ID` = {$user_id}) AS SCORE ON `LUT`.`ID` = `SCORE`.`PROGRAM_ID` AND CRITERIA_NAME_ID = {$criteria_name_id}";
        } else {
            $join_scores = '';
        }

        
        $query = <<<EOT
        SELECT 
        {$jsonObjectAgg[0]} as FY, LUT.ID as PROGRAM_ID, POS.PROGRAM_CODE , POS.PROGRAM_GROUP, POS.CAPABILITY_SPONSOR_CODE,
        POS.RESOURCE_CATEGORY_CODE,
        TOTAL_SCORE as storm, STORM_ID as storm_id,
        POS.`ASSESSMENT_AREA_CODE` {$g4_fields} {$event[0]} 
        FROM (SELECT
        A.ASSESSMENT_AREA_CODE,
        A.CAPABILITY_SPONSOR_CODE,
        FISCAL_YEAR,
        {$jsonObjectAgg[1]} AS RESOURCE_K,
        A.PROGRAM_GROUP,
        A.RESOURCE_CATEGORY_CODE,
        A.PROGRAM_CODE
        {$g3_fields}
        {$event[1]}
        FROM
        (SELECT
            ASSESSMENT_AREA_CODE,
            CAPABILITY_SPONSOR_CODE,
            FISCAL_YEAR,
            RESOURCE_K,
            {$jsonObjectAgg[5]}
            PROGRAM_GROUP,
            PROGRAM_CODE,
            RESOURCE_CATEGORY_CODE
            {$g1_fields}
            {$event[2]}
            FROM
                {$table}
            ) AS A
            LEFT JOIN
            (SELECT B.ASSESSMENT_AREA_CODE, B.PROGRAM_CODE, B.CAPABILITY_SPONSOR_CODE, B.RESOURCE_CATEGORY_CODE {$g2_fields} {$event[3]}, {$jsonObjectAgg[3]}
            FROM (
                SELECT ASSESSMENT_AREA_CODE, PROGRAM_CODE, CAPABILITY_SPONSOR_CODE, RESOURCE_CATEGORY_CODE {$g1_fields} {$event[4]}, {$jsonObjectAgg[2]} 
                FROM {$table}
            GROUP BY {$primary_group_1} ) AS B
            GROUP BY {$primary_group_2} ) AS C
            ON 
            {$pom_join_2}
            WHERE 1=1
            GROUP BY {$primary_group_fy}, FISCAL_YEAR) POS
        
        JOIN
        (SELECT ID,PROGRAM_NAME,PROGRAM_GROUP,PROGRAM_CODE,CAPABILITY_SPONSOR_CODE,ASSESSMENT_AREA_CODE,{$rcc}STORM_ID
        {$g1_fields}
        FROM {$lookup_program}
        WHERE 1=1 GROUP BY ID) AS LUT ON
        POS.PROGRAM_CODE = LUT.PROGRAM_CODE AND 
        POS.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE AND 
        POS.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
       LEFT 
            JOIN LOOKUP_STORM LS ON LS.ID = LUT.STORM_ID
        {$join_scores}
   GROUP BY {$primary_group_3}
EOT;
       
        #echo $query;die;
        // Debug: Log the query for troubleshooting
        if (is_dev_bypass_enabled()) {
            error_log("DEBUG: get_program query: " . $query);
        }
        $result = $this->DBs->SOCOM_UI->query($query)->result_array();

        $query =<<<EOT
    SELECT 
        `ID` as `SCORE_ID`, `SESSION` as `SCORE_SESSION`, PROGRAM_ID
    FROM
        `USR_OPTION_SCORES`
    WHERE
        `USER_ID` = {$user_id} AND CRITERIA_NAME_ID = {$criteria_name_id}
EOT;
        $scores = $this->DBs->SOCOM_UI->query($query)->result_array();

        return [
            'data' => $result,
            'year_list' => array_map('intval', explode(',', $year_list)),
            'scores' => array_combine(array_column($scores, 'PROGRAM_ID'), $scores)
        ];
    }

    public function get_weighted_table($weight_id){
        $criteria_name_id = get_criteria_name_id();

        return $this->DBs->SOCOM_UI
            ->select('*')
            ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
            //->where('USER_ID', (int)$user_id)
            ->where('DELETED', 0)
            ->where('WEIGHT_ID', (int)$weight_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->get()
            ->result_array();
    }

    public function get_pb_comparison_dashed_line($api_params = []) {
        $response = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode( $api_params),
            RHOMBUS_PYTHON_URL.'/socom/pb-comparison/dash-lines'
        );
        return json_decode($response, true);
    }

    public function get_pb_comparison_min_fiscal_year() {
        return $this->DBs->SOCOM_UI->distinct()
            ->select('MIN(FISCAL_YEAR) FISCAL_YEAR')
            ->from('DT_PB_COMPARISON')
            ->get()->row_array()['FISCAL_YEAR'] ?? false;
    }

    public function get_pb_comparison_sum(
            $year_list, $l_cap_sponsor, $l_ass_area, $program, $resource_category,
            $l_execution_manager, $l_program_name, $l_eoc_code, $l_osd_pe
        ) {

        if (!empty($l_program_name) && !in_array("ALL", $l_program_name)) {
            $program_codes = array_column(
                $this->SOCOM_Program_model->get_program_codes($l_program_name),
                'PROGRAM_CODE'
            );
        }

        $main_select = $this->DBs->SOCOM_UI->distinct()
            ->select('FISCAL_YEAR')
            ->from('DT_PB_COMPARISON')
            ->get_compiled_select();

            foreach($year_list as $yl) {
                $this->DBs->SOCOM_UI
                        ->select('SUM(PB'.$yl.') AS SUM_PB_'.$yl, false);
            }
            
            $this->DBs->SOCOM_UI
                ->select('FISCAL_YEAR')
                ->from('DT_PB_COMPARISON');

        if (!empty($l_cap_sponsor)) {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        }
        if (!empty($l_ass_area)) {
            $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        }
        if (!empty($program) && !in_array("ALL", $program)) {
            $this->DBs->SOCOM_UI->where_in('PROGRAM_GROUP', $program);
        } 
        if (!empty($resource_category)) {
            $this->DBs->SOCOM_UI->where_in('RESOURCE_CATEGORY_CODE', $resource_category);
        }
        if (!empty($l_execution_manager)) {
            $this->DBs->SOCOM_UI->where_in('EXECUTION_MANAGER_CODE', $l_execution_manager);
        }
        if (!empty($program_codes)) {
            $this->DBs->SOCOM_UI->where_in('PROGRAM_CODE', $program_codes);
        }
        if (!empty($l_eoc_code) && !in_array("ALL", $l_eoc_code)) {
            $this->DBs->SOCOM_UI->where_in('EOC_CODE', $l_eoc_code);
        }
        if (!empty($l_osd_pe) && !in_array("ALL", $l_osd_pe)) {
            $this->DBs->SOCOM_UI->where_in('OSD_PROGRAM_ELEMENT_CODE', $l_osd_pe);
        }

        $this->DBs->SOCOM_UI->group_by('FISCAL_YEAR')->order_by('FISCAL_YEAR');
        
        $join_select = $this->DBs->SOCOM_UI->get_compiled_select();

        $this->DBs->SOCOM_UI->
            select('A.FISCAL_YEAR');
        foreach($year_list as $yl) {
            $this->DBs->SOCOM_UI
                    ->select('B.SUM_PB_'.$yl);
        }
       
        $this->DBs->SOCOM_UI->
            from('(' . $main_select . ') as A')->
            join('(' . $join_select .') as B', 'ON A.FISCAL_YEAR = B.FISCAL_YEAR', 'LEFT');
        
        $result = $this->DBs->SOCOM_UI->get()->result_array();

        $q = $this->DBs->SOCOM_UI->last_query();

        return $result;
    }
    
    public function get_budget_and_execution_sum($l_cap_sponsor=null, $l_ass_area=null, $program=null, $resource_category=null,$l_execution_manager=null, $l_program_name=null, $l_eoc_code=null, $l_osd_pe=null) {

        if (!empty($l_program_name) && !in_array("ALL", $l_program_name)) {
            $program_codes = array_column(
                $this->SOCOM_Program_model->get_program_codes($l_program_name),
                'PROGRAM_CODE'
            );
        }

        $main_select = $this->DBs->SOCOM_UI->distinct()
            ->select('FISCAL_YEAR')
            ->from('DT_BUDGET_EXECUTION')
            ->get_compiled_select();
        
        $this->DBs->SOCOM_UI
            ->select('SUM(SUM_ACTUALS) AS SUM_ACTUALS', false)
            ->select('SUM(SUM_ENT) AS SUM_ENT', false)
            ->select('SUM(SUM_PB) AS SUM_PB', false)
            ->select('FISCAL_YEAR')
            ->from('DT_BUDGET_EXECUTION');

        if (!empty($l_cap_sponsor)) {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        }
        if (!empty($l_ass_area)) {
            $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        }
        if (!empty($program) && !in_array("ALL", $program)) {
            $this->DBs->SOCOM_UI->where_in('PROGRAM_GROUP', $program);
        } 
        if (!empty($resource_category)) {
            $this->DBs->SOCOM_UI->where_in('RESOURCE_CATEGORY_CODE', $resource_category);
        }
        if (!empty($l_execution_manager)) {
            $this->DBs->SOCOM_UI->where_in('EXECUTION_MANAGER_CODE', $l_execution_manager);
        }
        if (!empty($program_codes)) {
            $this->DBs->SOCOM_UI->where_in('PROGRAM_CODE', $program_codes);
        }
        if (!empty($l_eoc_code) && !in_array("ALL", $l_eoc_code)) {
            $this->DBs->SOCOM_UI->where_in('EOC_CODE', $l_eoc_code);
        }
        if (!empty($l_osd_pe) && !in_array("ALL", $l_osd_pe)) {
            $this->DBs->SOCOM_UI->where_in('OSD_PROGRAM_ELEMENT_CODE', $l_osd_pe);
        }

        $this->DBs->SOCOM_UI->group_by('FISCAL_YEAR')->order_by('FISCAL_YEAR');
        
        $join_select = $this->DBs->SOCOM_UI->get_compiled_select();
        
        $this->DBs->SOCOM_UI->
            select('A.FISCAL_YEAR')-> 
            select('B.SUM_ACTUALS as SUM_EXECUTION')->
            select('B.SUM_ENT as SUM_ENACTED')->
            select('B.SUM_PB as SUM_BUDGET')->
            from('(' . $main_select . ') as A')->
            join('(' . $join_select .') as B', 'ON A.FISCAL_YEAR = B.FISCAL_YEAR', 'LEFT');
        
        $result = $this->DBs->SOCOM_UI->get()->result_array();

        $q = $this->DBs->SOCOM_UI->last_query();

        return $result;
    }
    
    public function program_approval_status(
        $page, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $status = [], $filter=false
    ) {
        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );
        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXT']
        );
        $zbt_extract_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXTRACT']
        );
        $issue_extract_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXTRACT']
        );

        $join_query1 = $this->DBs->SOCOM_UI->select('
                PROGRAM_NAME,
                PROGRAM_GROUP,
                PROGRAM_CODE,
                POM_SPONSOR_CODE,
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE')
                ->from('LOOKUP_PROGRAM')
                ->get_compiled_select();

        $query1 = "
            SELECT
                `PROGRAM_GROUP`,
                `PROGRAM_CODE`,
                `CAPABILITY_SPONSOR_CODE`,
                `POM_SPONSOR_CODE`,
                `ASSESSMENT_AREA_CODE`,
                `EVENT_STATUS`
            FROM
                ${extract_table}
            WHERE
                CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
                AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
            UNION ALL
            SELECT
                `PROGRAM_GROUP`,
                `PROGRAM_CODE`,
                `CAPABILITY_SPONSOR_CODE`,
                `POM_SPONSOR_CODE`,
                `ASSESSMENT_AREA_CODE`,
                'DECIDED' AS EVENT_STATUS
            FROM `${ext_table}`
            WHERE
                    (PROGRAM_CODE NOT IN (SELECT
                        DISTINCT PROGRAM_CODE
                    FROM ${zbt_extract_table}) OR
                        EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM ${zbt_extract_table})
                    )
                AND
                CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
                AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
        ";

        if ($page === 'issue') {
            $zbt_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                true,
                $this->page_variables[$page]['type']['ZBT']
            );

            $query1 = $query1 .
            " UNION ALL
            SELECT
                `PROGRAM_GROUP`,
                `PROGRAM_CODE`,
                `CAPABILITY_SPONSOR_CODE`,
                `POM_SPONSOR_CODE`,
                `ASSESSMENT_AREA_CODE`,
                'DECIDED' AS `EVENT_STATUS`
            FROM
                `${zbt_table}`
            WHERE
                (
                    PROGRAM_CODE NOT IN (
                        SELECT
                            DISTINCT PROGRAM_CODE
                        FROM
                            ${issue_extract_table}
                    ) OR EOC_CODE NOT IN (
                        SELECT
                            DISTINCT EOC_CODE
                        FROM
                            ${zbt_extract_table}
                    )
                )
                AND CAPABILITY_SPONSOR_CODE IN('". implode( "','", $l_cap_sponsor) . "')
                AND ASSESSMENT_AREA_CODE  IN('". implode( "','", $l_ass_area) ."')
            ";
        }

        $selector =  $filter ? 'PROGRAM_GROUP' : 'PROGRAM_NAME';
        $sql = "SELECT DISTINCT
                    LUT.${selector},
                    CASE WHEN SUM(IF(EXTRACT.EVENT_STATUS LIKE 'NOT DECIDED', 1, 0)) = 0 
                    THEN 'COMPLETED' WHEN SUM(IF(EXTRACT.EVENT_STATUS = 'NOT DECIDED', 1, 0)) > 0
                    THEN 'PENDING' END AS APPROVAL_ACTION_STATUS
                FROM 
                    ($query1) AS EXTRACT
                LEFT JOIN 
                    ($join_query1) AS LUT ON 
                    EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                    AND EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
                    AND EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                    AND EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                    AND EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
                WHERE
                    LUT.PROGRAM_GROUP IS NOT NULL
                GROUP BY
                    LUT.PROGRAM_NAME
                HAVING
                    APPROVAL_ACTION_STATUS IN('". implode( "','", $status) . "')
                ORDER BY $selector
        ";
        return $this->DBs->SOCOM_UI->query($sql)->result_array();
    }
    
    public function get_eoc_summary_data($page, $fy, $params) {
        $year_list = $this->page_variables[$page]['year_list'];
        $program_code = $params['program_code'];
        $eoc_code = $params['eoc_code'];

        $dt_extract_table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        $fiscal_years_column = $this->distinct_list_table($dt_extract_table, 'FISCAL_YEAR');

        $base_k = [];
        $prop_amt = [];
        $delta_amt = [];
        $issue_base_ext_amt = [];
        $issue_base_zbt_amt = [];
        $issue_prop_amt = [];
        $issue_delta_amt = [];

        if ($page == 'issue') {
            // $issue_base_ext_amt = $this->get_eoc_issue_summary_query(
            //     'DT_ZBT_2026',
            //     $table2,
            //     $table5,
            //     $program,
            //     "EXT.EOC_CODE AS EOC,ISS_EXTRACT.EVENT_NAME,
            //     EXT.ASSESSMENT_AREA_CODE,
            //     EXT.POM_SPONSOR_CODE,
            //     EXT.CAPABILITY_SPONSOR_CODE,
            //     EXT.RESOURCE_CATEGORY_CODE,
            //     ISS_EXTRACT.EVENT_JUSTIFICATION,
            //     EXT.POM_POSITION_CODE,
            //     '26EXT',EXT.FISCAL_YEAR,SUM(ISS_EXTRACT.RESOURCE_K) AS EXT_AMT,
            //     ($fiscal_years_column) as FISCAL_YEARS"
            // );
            $issue_base_zbt_amt = $this->get_eoc_issue_summary_query(
                $program_code,
                $eoc_code,
                "ISS_EXTRACT.EOC_CODE AS EOC,
                ISS_EXTRACT.EVENT_NAME,
                ISS_EXTRACT.EVENT_TITLE,
                ISS_EXTRACT.CAPABILITY_SPONSOR_CODE,
                ISS_EXTRACT.ASSESSMENT_AREA_CODE,
                ISS_EXTRACT.RESOURCE_CATEGORY_CODE,
                ISS_EXTRACT.SPECIAL_PROJECT_CODE,
                ISS_EXTRACT.OSD_PROGRAM_ELEMENT_CODE,
                ISS_EXTRACT.EVENT_JUSTIFICATION,
                ISS_EXTRACT.POM_SPONSOR_CODE,
                ISS_EXTRACT.POM_POSITION_CODE,
                ISS_EXTRACT.FISCAL_YEAR,
                '${fy}ZBT',
                SUM(ISS_EXTRACT.RESOURCE_K) AS ZBT_AMT"
            );

            $issue_delta_amt = $this->get_eoc_issue_summary_query(
                $program_code,
                $eoc_code,
                "ISS_EXTRACT.EOC_CODE AS EOC,
                ISS_EXTRACT.EVENT_NAME,
                ISS_EXTRACT.EVENT_TITLE,
                ISS_EXTRACT.CAPABILITY_SPONSOR_CODE,
                ISS_EXTRACT.ASSESSMENT_AREA_CODE,
                ISS_EXTRACT.RESOURCE_CATEGORY_CODE,
                ISS_EXTRACT.SPECIAL_PROJECT_CODE,
                ISS_EXTRACT.OSD_PROGRAM_ELEMENT_CODE,
                ISS_EXTRACT.EVENT_JUSTIFICATION,
                ISS_EXTRACT.POM_SPONSOR_CODE,
                ISS_EXTRACT.POM_POSITION_CODE,
                ISS_EXTRACT.FISCAL_YEAR,
                '${fy}ISS REQUESTED DELTA' AS ${fy}ISS_REQUESTED_DELTA,
                SUM(ISS_EXTRACT.DELTA_AMT) AS DELTA_AMT",
                true
            );
            $this->eocDetailsAppendDataToBase($issue_base_ext_amt, "${fy}EXT", 'EOC', $year_list, 'EXT_AMT');
            $this->eocDetailsDefaultToZero($issue_base_ext_amt, $issue_base_zbt_amt,  'ZBT_AMT', "${fy}ZBT", "${fy}ZBT");
            $this->eocDetailsDefaultToZero($issue_base_ext_amt, $issue_delta_amt,  'DELTA_AMT', "${fy}ISS_REQUESTED_DELTA", "${fy}ISS REQUESTED DELTA");

            $issue_prop_amt = $this->eoc_calculate_prop_amt(
                $issue_base_zbt_amt, $issue_delta_amt, 'ZBT_AMT', 'DELTA_AMT',
                [
                    'key' => "${fy}ISS_REQUESTED",
                    'value'=> "${fy}ISS REQUESTED"
                ]
            );

            $this->sortResultByMultiKeys($issue_base_ext_amt, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');
            $this->sortResultByMultiKeys($issue_base_zbt_amt, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');
            $this->sortResultByMultiKeys($issue_delta_amt, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');
            $this->sortResultByMultiKeys($issue_prop_amt, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');


            // $issue_base_ext_amt = $this->eoc_issue_summary_query(
            //     $table2,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "LUT_EOC.EOC_CODE AS EOC,EXT.ASSESSMENT_AREA_CODE,
            //     EXT.POM_SPONSOR_CODE,
            //     EXT.CAPABILITY_SPONSOR_CODE,
            //     EXT.RESOURCE_CATEGORY_CODE,
            //     '26EXT',EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS EXT_AMT,
            //     ($fiscal_years_column) as FISCAL_YEARS"
            // );
            // $issue_base_zbt_amt = $this->eoc_issue_summary_query(
            //     $table2,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "LUT_EOC.EOC_CODE AS EOC,EXT.ASSESSMENT_AREA_CODE,
            //         EXT.POM_SPONSOR_CODE,
            //         EXT.CAPABILITY_SPONSOR_CODE,
            //         EXT.RESOURCE_CATEGORY_CODE,
            //     '26ZBT',EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS ZBT_AMT
            //     ,
            //     ($fiscal_years_column) as FISCAL_YEARS"
            // );
            // $issue_prop_amt = $this->eoc_summary_query(
            //     $table1,
            //     $table4,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "LUT.PROGRAM_NAME,'26ISS REQUESTED' AS 26ISS_REQUESTED,
            //     EXT.FISCAL_YEAR,SUM(IFNULL(ZBT_EXTRACT.PROP_AMT,
            //     ZBT_EXTRACT.RESOURCE_K)) AS PROP_AMT,
            //     ($event_names_column) as EVENT_NAME,
            //     ($event_justifications_column) as EVENT_JUSTIFICATION,
            //     ($fiscal_years_column) as FISCAL_YEARS"
            // );

            // $issue_delta_amt = $this->eoc_summary_query(
            //     $table1,
            //     $table4,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "LUT.PROGRAM_NAME,'26ISS REQUESTED DELTA' AS 26ISS_REQUESTED_DELTA,EXT.FISCAL_YEAR,
            //     SUM(IFNULL(ZBT_EXTRACT.DELTA_AMT,0)) AS DELTA_AMT,
            //     ($fiscal_years_column) as FISCAL_YEARS",
            // );
        } else {
            
            // $base_k = $this->eoc_summary_query(
            //     $table1,
            //     $table2,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "EXT.EOC_CODE AS EOC,ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
            //     ZBT_EXTRACT.POM_SPONSOR_CODE,
            //     ZBT_EXTRACT.EVENT_NAME,
            //     ZBT_EXTRACT.EVENT_JUSTIFICATION,
            //     ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
            //     ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
            //     '{$fy}EXT',EXT.FISCAL_YEAR,SUM(EXT.RESOURCE_K) AS BASE_K,
            //     ($fiscal_years_column) as FISCAL_YEARS
            //     "
            // );
        
            // $prop_amt = $this->eoc_summary_query(
            //     $table1,
            //     $table2,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "LUT_EOC.EOC_CODE AS EOC,ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
            //     ZBT_EXTRACT.POM_SPONSOR_CODE,
            //     ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
            //     ($event_names_column) as EVENT_NAME,
            //     ($event_justifications_column) as EVENT_JUSTIFICATION,
            //     ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
            //     '{$fy}ZBT REQUESTED' as {$fy}ZBT_REQUESTED,EXT.FISCAL_YEAR,
            //     SUM(IFNULL(ZBT_EXTRACT.PROP_AMT,ZBT_EXTRACT.RESOURCE_K)
            //     ) AS PROP_AMT, ($fiscal_years_column) as FISCAL_YEARS"
            // );
            // $delta_amt = $this->eoc_summary_query(
            //     $table1,
            //     $table2,
            //     $l_pom_sponsor,
            //     $l_cap_sponsor,
            //     $l_ass_area,
            //     $program,
            //     '2026',
            //     "LUT_EOC.EOC_CODE AS EOC,ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
            //     ZBT_EXTRACT.POM_SPONSOR_CODE,
            //     ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
            //     ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
            //     '{$fy}ZBT REQUESTED DELTA' as {$fy}ZBT_REQUESTED_DELTA,EXT.FISCAL_YEAR,SUM(IFNULL(ZBT_EXTRACT.DELTA_AMT,0)) AS DELTA_AMT,
            //     ($fiscal_years_column) as FISCAL_YEARS"
            // );


            $base_k = $this->get_eoc_summary_query(
                $program_code,
                $eoc_code,
                "ZBT_EXTRACT.EOC_CODE AS EOC,
                ZBT_EXTRACT.EVENT_NAME,
                ZBT_EXTRACT.EVENT_TITLE,
                ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
                ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
                ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
                ZBT_EXTRACT.SPECIAL_PROJECT_CODE,
                ZBT_EXTRACT.OSD_PROGRAM_ELEMENT_CODE,
                ZBT_EXTRACT.EVENT_JUSTIFICATION,
                ZBT_EXTRACT.POM_SPONSOR_CODE,
                ZBT_EXTRACT.POM_POSITION_CODE,
                ZBT_EXTRACT.FISCAL_YEAR,
                '{$fy}EXT',
                SUM(ZBT_EXTRACT.RESOURCE_K) AS BASE_K
                "
            );

            $delta_amt = $this->get_eoc_summary_query(
                $program_code,
                $eoc_code,
                "ZBT_EXTRACT.EOC_CODE AS EOC,
                ZBT_EXTRACT.EVENT_NAME,
                ZBT_EXTRACT.EVENT_TITLE,
                ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
                ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
                ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
                ZBT_EXTRACT.SPECIAL_PROJECT_CODE,
                ZBT_EXTRACT.OSD_PROGRAM_ELEMENT_CODE,
                ZBT_EXTRACT.EVENT_JUSTIFICATION,
                ZBT_EXTRACT.POM_SPONSOR_CODE,
                ZBT_EXTRACT.POM_POSITION_CODE,
                ZBT_EXTRACT.FISCAL_YEAR,
                '{$fy}ZBT REQUESTED DELTA' as {$fy}ZBT_REQUESTED_DELTA,
                SUM(ZBT_EXTRACT.DELTA_AMT) AS DELTA_AMT
                ",
                true
            );

            $this->eocDetailsAppendDataToBase($base_k, "{$fy}EXT", 'EOC', $year_list);
            $this->eocDetailsDefaultToZero($base_k, $delta_amt,  'DELTA_AMT', "{$fy}ZBT_REQUESTED_DELTA", "{$fy}ZBT REQUESTED DELTA");

            $prop_amt = $this->eoc_calculate_prop_amt(
                $base_k, $delta_amt, 'BASE_K', 'DELTA_AMT',
                [
                    'key' => "{$fy}ZBT_REQUESTED",
                    'value'=> "{$fy}ZBT REQUESTED"
                ]
            );

            $this->sortResultByMultiKeys($prop_amt, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');
            $this->sortResultByMultiKeys($base_k, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');
            $this->sortResultByMultiKeys($delta_amt, 'EOC', 'EVENT_NAME', 'FISCAL_YEAR');
        }
        
        return [
            'base_k' => $base_k,
            'prop_amt' => $prop_amt,
            'delta_amt' => $delta_amt,
            'issue_base_zbt_amt' => $issue_base_zbt_amt,
            'issue_base_ext_amt'=> $issue_base_ext_amt,
            'issue_prop_amt' => $issue_prop_amt,
            'issue_delta_amt' => $issue_delta_amt
        ];

    }

    // not in use
    // public function get_eoc_historical_issue_summary_pom_query(
    //     $table1, $table2, $table3, $program, $selection
    // ) {
    //     $sub_query1 = $this->DBs->SOCOM_UI->select('*')
    //                                     ->from($table2)
    //                                     ->get_compiled_select();
     
    //     $this->DBs->SOCOM_UI->select("
    //         ASSESSMENT_AREA_CODE,
    //         0 AS BASE_K,
    //         BUDGET_ACTIVITY_NAME,
    //         BUDGET_SUB_ACTIVITY_NAME,
    //         CAPABILITY_SPONSOR_CODE,
    //         EOC_CODE,
    //         EVENT_JUSTIFICATION,
    //         EVENT_NAME,
    //         EXECUTION_MANAGER_CODE,
    //         FISCAL_YEAR,
    //         0 AS OCO_OTHD_K,
    //         0 AS OCO_TO_BASE_K,
    //         OSD_PROGRAM_ELEMENT_CODE,
    //         POM_SPONSOR_CODE,
    //         PROGRAM_CODE,
    //         RESOURCE_CATEGORY_CODE,
    //         RESOURCE_K,
    //         SPECIAL_PROJECT_CODE,
    //         SUB_ACTIVITY_GROUP_CODE,
    //         SUB_ACTIVITY_GROUP_NAME")
    //         ->from($table3)
    //         ->where("(PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ${table2}) OR
    //         EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM ${table2}))");

    //     $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

    //     $this->DBs->SOCOM_UI->select("
    //         ASSESSMENT_AREA_CODE,
    //         0 AS BASE_K,
    //         BUDGET_ACTIVITY_NAME,
    //         BUDGET_SUB_ACTIVITY_NAME,
    //         CAPABILITY_SPONSOR_CODE,
    //         EOC_CODE,
    //         EVENT_JUSTIFICATION,
    //         EVENT_NAME,
    //         EXECUTION_MANAGER_CODE,
    //         FISCAL_YEAR,
    //         0 AS OCO_OTHD_K,
    //         0 AS OCO_TO_BASE_K,
    //         OSD_PROGRAM_ELEMENT_CODE,
    //         POM_SPONSOR_CODE,
    //         PROGRAM_CODE,
    //         RESOURCE_CATEGORY_CODE,
    //         RESOURCE_K,
    //         SPECIAL_PROJECT_CODE,
    //         SUB_ACTIVITY_GROUP_CODE,
    //         SUB_ACTIVITY_GROUP_NAME")
    //     ->from($table1)
    //     ->where("(PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ${table2}) OR
    //     EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM ${table2}))");

    //     $sub_query3 = $this->DBs->SOCOM_UI->get_compiled_select();

    //     $query1 = $this->DBs->SOCOM_UI
    //         ->select($selection)
    //         ->from('('. $sub_query1 . ' UNION ALL ' . $sub_query2 . ' UNION ALL ' . $sub_query3 .') AS EXT')->get_compiled_select();

    //     $query2 = "  LEFT JOIN (
    //         SELECT
    //             PROGRAM_NAME,
    //             PROGRAM_CODE,
    //             POM_SPONSOR_CODE,
    //             CAPABILITY_SPONSOR_CODE,
    //             ASSESSMENT_AREA_CODE
    //         FROM
    //             LOOKUP_PROGRAM_DETAIL
    //     ) AS LUT ON EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
    //     AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //     AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //     AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE ";

    //     $query3 = "
    //     WHERE
    //         EXT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
    //         AND LUT.PROGRAM_NAME = '${program}'
    //     GROUP BY
    //         EXT.EOC_CODE,
    //         EXT.FISCAL_YEAR
    //     ORDER BY
    //         EXT.EOC_CODE,
    //         EXT.FISCAL_YEAR";
    //     return $this->DBs->SOCOM_UI->query($query1 . $query2 . $query3)->result_array();
    // }

    // not in use
    // public function get_eoc_historical_issue_summary_query(
    //     $table1, $table2, $table3, $program, $selection, $delta=false, $ext=false
    // ) {
    //     $variable_1 = $delta ?  'DELTA_AMT' : 'RESOURCE_K';
    //     $sub_query1 = $this->DBs->SOCOM_UI->select('*')
    //                                     ->from($table2)
    //                                     ->get_compiled_select();
     
    //     $this->DBs->SOCOM_UI->select("
    //         0 AS ADJUSTMENT_K,
    //         ASSESSMENT_AREA_CODE,
    //         0 AS BASE_K,
    //         BUDGET_ACTIVITY_CODE,
    //         BUDGET_ACTIVITY_NAME,
    //         BUDGET_SUB_ACTIVITY_CODE,
    //         BUDGET_SUB_ACTIVITY_NAME,
    //         CAPABILITY_SPONSOR_CODE,
    //         0 AS END_STRENGTH,
    //         EOC_CODE,
    //         EVENT_JUSTIFICATION,
    //         EVENT_NAME,
    //         EXECUTION_MANAGER_CODE,
    //         FISCAL_YEAR,
    //         LINE_ITEM_CODE,
    //         0 AS OCO_OTHD_ADJUSTMENT_K,
    //         0 AS OCO_OTHD_K,
    //         0 AS OCO_TO_BASE_K,
    //         OSD_PROGRAM_ELEMENT_CODE,
    //         POM_POSITION_CODE,
    //         POM_SPONSOR_CODE,
    //         PROGRAM_CODE,
    //         PROGRAM_GROUP,
    //         RDTE_PROJECT_CODE,
    //         RESOURCE_CATEGORY_CODE,
    //         0 AS RESOURCE_K,
    //         SPECIAL_PROJECT_CODE,
    //         SUB_ACTIVITY_GROUP_CODE,
    //         SUB_ACTIVITY_GROUP_NAME,
    //         2024 AS WORK_YEARS")
    //         ->from($table3)
    //         ->where("(PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ${table2}) OR
    //         EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM ${table2}))");

    //     $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

    //     $this->DBs->SOCOM_UI->select("
    //     0 AS ADJUSTMENT_K,
    //     ASSESSMENT_AREA_CODE,
    //     0 AS BASE_K,
    //     BUDGET_ACTIVITY_CODE,
    //     BUDGET_ACTIVITY_NAME,
    //     BUDGET_SUB_ACTIVITY_CODE,
    //     BUDGET_SUB_ACTIVITY_NAME,
    //     CAPABILITY_SPONSOR_CODE,
    //     0 AS END_STRENGTH,
    //     EOC_CODE,
    //     EVENT_JUSTIFICATION,
    //     EVENT_NAME,
    //     EXECUTION_MANAGER_CODE,
    //     FISCAL_YEAR,
    //     LINE_ITEM_CODE,
    //     0 AS OCO_OTHD_ADJUSTMENT_K,
    //     0 AS OCO_OTHD_K,
    //     0 AS OCO_TO_BASE_K,
    //     OSD_PROGRAM_ELEMENT_CODE,
    //     POM_POSITION_CODE,
    //     POM_SPONSOR_CODE,
    //     PROGRAM_CODE,
    //     PROGRAM_GROUP,
    //     RDTE_PROJECT_CODE,
    //     RESOURCE_CATEGORY_CODE,
    //     0 AS RESOURCE_K,
    //     SPECIAL_PROJECT_CODE,
    //     SUB_ACTIVITY_GROUP_CODE,
    //     SUB_ACTIVITY_GROUP_NAME,
    //     2024 AS WORK_YEARS")
    //     ->from($table1)
    //     ->where("(PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM ${table2}) OR
    //     EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM ${table2}))");

    //     $sub_query3 = $this->DBs->SOCOM_UI->get_compiled_select();

    //     $query1 = $this->DBs->SOCOM_UI
    //         ->select($selection)
    //         ->from('('. $sub_query1 . ' UNION ALL ' . $sub_query2 . ' UNION ALL ' . $sub_query3 .') AS ZBT')->get_compiled_select();

    //     $query2 = " LEFT JOIN (
    //         SELECT
    //             PROGRAM_NAME,
    //             PROGRAM_GROUP,
    //             PROGRAM_CODE,
    //             POM_SPONSOR_CODE,
    //             CAPABILITY_SPONSOR_CODE,
    //             ASSESSMENT_AREA_CODE
    //         FROM
    //             LOOKUP_PROGRAM_DETAIL
    //     ) AS LUT ON ZBT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
    //     AND ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE
    //     AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //     AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //     AND ZBT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE ";

    //     if (!$ext) {
    //         $query2 = $query2 . "LEFT JOIN (
    //             SELECT
    //                 PROGRAM_CODE,
    //                 EOC_CODE,
    //                 FISCAL_YEAR,
    //                 OSD_PROGRAM_ELEMENT_CODE,
    //                 RESOURCE_CATEGORY_CODE,
    //                 ${variable_1},
    //                 ASSESSMENT_AREA_CODE,
    //                 POM_SPONSOR_CODE,
    //                 CAPABILITY_SPONSOR_CODE
    //             FROM
    //                 DT_ISS_EXTRACT_2026
    //         ) AS ISS_EXTRACT ON ZBT.PROGRAM_CODE = ISS_EXTRACT.PROGRAM_CODE
    //         AND ZBT.EOC_CODE = ISS_EXTRACT.EOC_CODE
    //         AND ZBT.FISCAL_YEAR = ISS_EXTRACT.FISCAL_YEAR
    //         AND ZBT.OSD_PROGRAM_ELEMENT_CODE = ISS_EXTRACT.OSD_PROGRAM_ELEMENT_CODE
    //         AND ZBT.RESOURCE_CATEGORY_CODE = ISS_EXTRACT.RESOURCE_CATEGORY_CODE
    //         AND ZBT.ASSESSMENT_AREA_CODE = ISS_EXTRACT.ASSESSMENT_AREA_CODE
    //         AND ZBT.POM_SPONSOR_CODE = ISS_EXTRACT.POM_SPONSOR_CODE
    //         AND ZBT.CAPABILITY_SPONSOR_CODE = ISS_EXTRACT.CAPABILITY_SPONSOR_CODE";
    //     }
    
    //     $query3 = "
    //     WHERE
    //         ZBT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
    //         AND LUT.PROGRAM_NAME = '${program}'
    //     GROUP BY
    //         ZBT.EOC_CODE,
    //         ZBT.POM_POSITION_CODE,
    //         ZBT.FISCAL_YEAR
    //     ORDER BY
    //         ZBT.EOC_CODE,
    //         ZBT.FISCAL_YEAR";

    //     // print_r('===================================');
    //     // print_r($query1 . $query2 . $query3);
    //     // print_r('===================================');
    //     return $this->DBs->SOCOM_UI->query($query1 . $query2 . $query3)->result_array();
    // }

    // not in use
    // public function get_eoc_historical_summary_query(
    //     $table1, $table2, $program, $selection, $delta=false, $fy='26', $pom=false
    // ) {
    //     $variable_1 = $delta ?  'DELTA_AMT' : 'RESOURCE_K';
    //     $sub_query1 = $this->DBs->SOCOM_UI->select('*')
    //                                     ->from($table2)
    //                                     ->get_compiled_select();
    //     $extra_join = "";
    //     $group_by_column = "";
    //     if (!$pom) {
    //         $union_columns = '0 AS ADJUSTMENT_K, ASSESSMENT_AREA_CODE,0 AS BASE_K,
    //         BUDGET_ACTIVITY_CODE,BUDGET_ACTIVITY_NAME,BUDGET_SUB_ACTIVITY_CODE,BUDGET_SUB_ACTIVITY_NAME,
    //         CAPABILITY_SPONSOR_CODE,0 AS END_STRENGTH,EOC_CODE,EVENT_JUSTIFICATION,EVENT_NAME,EXECUTION_MANAGER_CODE,
    //         FISCAL_YEAR,LINE_ITEM_CODE,0 AS OCO_OTHD_ADJUSTMENT_K,0 AS OCO_OTHD_K,0 AS OCO_TO_BASE_K,
    //         OSD_PROGRAM_ELEMENT_CODE,POM_POSITION_CODE,POM_SPONSOR_CODE,PROGRAM_CODE,PROGRAM_GROUP,RDTE_PROJECT_CODE,
    //         RESOURCE_CATEGORY_CODE,RESOURCE_K,SPECIAL_PROJECT_CODE,SUB_ACTIVITY_GROUP_CODE,SUB_ACTIVITY_GROUP_NAME,
    //         2024 AS WORK_YEARS';
    //         $extra_join = "AND EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP";
    //         $group_by_column = "EXT.POM_POSITION_CODE,";
    //     }
    //     else {
    //         $union_columns =
    //         'ASSESSMENT_AREA_CODE,
    //         0 AS BASE_K,
    //         BUDGET_ACTIVITY_NAME,
    //         BUDGET_SUB_ACTIVITY_NAME,
    //         CAPABILITY_SPONSOR_CODE,
    //         EOC_CODE,
    //         EVENT_JUSTIFICATION,
    //         EVENT_NAME,
    //         EXECUTION_MANAGER_CODE,
    //         FISCAL_YEAR,
    //         0 AS OCO_OTHD_K,
    //         0 AS OCO_TO_BASE_K,
    //         OSD_PROGRAM_ELEMENT_CODE,
    //         POM_SPONSOR_CODE,
    //         PROGRAM_CODE,
    //         RESOURCE_CATEGORY_CODE,
    //         RESOURCE_K,
    //         SPECIAL_PROJECT_CODE,
    //         SUB_ACTIVITY_GROUP_CODE,
    //         SUB_ACTIVITY_GROUP_NAME';
    //     }

        
    //     $this->DBs->SOCOM_UI->select($union_columns)
    //         ->from($table1)
    //         ->where("(PROGRAM_CODE NOT IN (SELECT DISTINCT PROGRAM_CODE FROM {$table2}) OR
    //         EOC_CODE NOT IN (SELECT DISTINCT EOC_CODE FROM {$table2}))");

    //     $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

    //     $query1 = $this->DBs->SOCOM_UI
    //         ->select($selection)
    //         ->from('('. $sub_query1 . ' UNION ALL ' . $sub_query2 . ') AS EXT')->get_compiled_select();

    //     $query2 = " LEFT JOIN (
    //         SELECT
    //             PROGRAM_NAME,
    //             PROGRAM_GROUP,
    //             PROGRAM_CODE,
    //             POM_SPONSOR_CODE,
    //             CAPABILITY_SPONSOR_CODE,
    //             ASSESSMENT_AREA_CODE
    //         FROM
    //             LOOKUP_PROGRAM_DETAIL
    //     ) AS LUT ON EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
    //     AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //     AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //     AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE " . $extra_join;
    
    //     $query3 = "";
    //     $query4_orderBy = "EXT.EOC_CODE,";
    //     $query4_extra_where = "";
    //     if ($fy == 26) {
    //         $query4_extra_where = "AND EXT.EXECUTION_MANAGER_CODE != ''";
    //         $query3 = " LEFT JOIN (
    //             SELECT
    //                 PROGRAM_CODE,
    //                 EOC_CODE,
    //                 FISCAL_YEAR,
    //                 OSD_PROGRAM_ELEMENT_CODE,
    //                 RESOURCE_CATEGORY_CODE,
    //                 ASSESSMENT_AREA_CODE,
    //                 POM_SPONSOR_CODE,
    //                 CAPABILITY_SPONSOR_CODE, ". $variable_1 ." 
    //             FROM
    //                 DT_ZBT_EXTRACT_2026
    //         ) AS ZBT_EXTRACT ON EXT.PROGRAM_CODE = ZBT_EXTRACT.PROGRAM_CODE
    //         AND EXT.EOC_CODE = ZBT_EXTRACT.EOC_CODE
    //         AND EXT.FISCAL_YEAR = ZBT_EXTRACT.FISCAL_YEAR
    //         AND EXT.OSD_PROGRAM_ELEMENT_CODE = ZBT_EXTRACT.OSD_PROGRAM_ELEMENT_CODE
    //         AND EXT.RESOURCE_CATEGORY_CODE = ZBT_EXTRACT.RESOURCE_CATEGORY_CODE
    //         AND EXT.ASSESSMENT_AREA_CODE = ZBT_EXTRACT.ASSESSMENT_AREA_CODE
    //         AND EXT.POM_SPONSOR_CODE = ZBT_EXTRACT.POM_SPONSOR_CODE
    //         AND EXT.CAPABILITY_SPONSOR_CODE = ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE";
    //     }
        
    //     $query4_where = "
    //     `EXT`.`FISCAL_YEAR` IN ('2026', '2027', '2028', '2029', '2030')
    //     AND LUT.PROGRAM_NAME = '${program}' ${query4_extra_where}";

    //     $query4 = " WHERE " .$query4_where . "
    //         GROUP BY
    //        " .  $group_by_column . " 
    //             EXT.EOC_CODE,
    //             EXT.FISCAL_YEAR
    //         ORDER BY
    //             " . $query4_orderBy ."
    //             EXT.FISCAL_YEAR
    //     ";
    //     return $this->DBs->SOCOM_UI->query($query1 . $query2 . $query3 . $query4)->result_array();
    // }

    public function get_eoc_issue_summary_query($program_code, $eoc_code, $selection, $delta=false) {
        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXT']
        );
        $zbt_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['ZBT']
        );

        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables['issue']['subapp'],
            true,
            $this->page_variables['issue']['type']['EXTRACT']
        );

        $table1 = $ext_table;
        $table2 = $zbt_table;
        $table3 = $extract_table;

        $variable_1 = '';
        $variable_2 = '';
        if ($delta) {
            $variable_1 = 'DELTA_AMT';
        }
        else {
            $variable_1 = 'RESOURCE_K';
        }

        $fy = $this->page_variables['issue']['fy'];
        $year = $this->page_variables['issue']['year'];
        $two_years_ago_fy = $year - 2;

        $year_list = $this->page_variables['issue']['year_list'];
        $year_list_query = "'" . implode("', '", $year_list) . "'";

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND ISS_EXTRACT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $this->DBs->SOCOM_UI->select("
            PROGRAM_CODE,
            PROGRAM_GROUP,
            EOC_CODE,
            EVENT_NAME,
            EVENT_TITLE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            RESOURCE_CATEGORY_CODE,
            SPECIAL_PROJECT_CODE,
            OSD_PROGRAM_ELEMENT_CODE,
            EVENT_JUSTIFICATION,
            '${fy}ZBT' AS POM_POSITION_CODE,
            FISCAL_YEAR,
            ${variable_1}
        ")
        ->from($table3);

  

        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('('. $sub_query2 . ') AS ISS_EXTRACT')->get_compiled_select();
        
        $query3 = " LEFT JOIN (
            SELECT
                PROGRAM_NAME,
                PROGRAM_GROUP,
                PROGRAM_CODE,
                POM_SPONSOR_CODE,
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE
            FROM
                LOOKUP_PROGRAM
        ) AS LUT ON ISS_EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND ISS_EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND ISS_EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND ISS_EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND ISS_EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";

        $query4 = "
            WHERE
                ISS_EXTRACT.FISCAL_YEAR IN (${year_list_query})
                AND ISS_EXTRACT.EVENT_NAME IS NOT NULL
                AND ISS_EXTRACT.EVENT_JUSTIFICATION IS NOT NULL
                AND ISS_EXTRACT.PROGRAM_CODE = '${program_code}'
                ${eoc_code_filter}
            GROUP BY
                ISS_EXTRACT.EOC_CODE,
                ISS_EXTRACT.EVENT_NAME,
                ISS_EXTRACT.EVENT_TITLE,
                ISS_EXTRACT.CAPABILITY_SPONSOR_CODE,
                ISS_EXTRACT.ASSESSMENT_AREA_CODE,
                ISS_EXTRACT.RESOURCE_CATEGORY_CODE,
                ISS_EXTRACT.OSD_PROGRAM_ELEMENT_CODE,
                ISS_EXTRACT.POM_POSITION_CODE,
                ISS_EXTRACT.FISCAL_YEAR
            ORDER BY
                ISS_EXTRACT.EOC_CODE,
                ISS_EXTRACT.EVENT_NAME,
                ISS_EXTRACT.EVENT_TITLE,
                ISS_EXTRACT.ASSESSMENT_AREA_CODE,
                ISS_EXTRACT.FISCAL_YEAR
        ";
        // print_r('-------------------------get_eoc_issue_summary_query ------->');
        // print_r($query1 . $query3 . $query4);
        // print_r('=====================================');
        return $this->DBs->SOCOM_UI->query($query1 . $query3 . $query4)->result_array();
    }

    public function get_eoc_summary_query($program, $eoc_code,$selection, $delta=false) {
        $variable_1 = '';
        $variable_2 = '';
        if ($delta) {
            $variable_1 = 'DELTA_AMT';
        }
        else {
            $variable_1 = 'RESOURCE_K';
        }

        $ext_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXT']
        );
        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables['zbt_summary']['subapp'],
            true,
            $this->page_variables['zbt_summary']['type']['EXTRACT']
        );

        $table1 = $extract_table;
        $table2 = $ext_table;

        $fy = $this->page_variables['zbt_summary']['fy'];
        $year = $this->page_variables['zbt_summary']['year'];
        $two_years_ago_fy = $year - 2;

        $year_list = $this->page_variables['zbt_summary']['year_list'];
        $year_list_query = "'" . implode("', '", $year_list) . "'";

        $eoc_code_filter = '';
        if (!empty($eoc_code)) {
            $eoc_code_filter = "AND ZBT_EXTRACT.EOC_CODE IN ('" . implode("', '", $eoc_code) . "')";
        }

        $this->DBs->SOCOM_UI->select("
            PROGRAM_CODE,
            PROGRAM_GROUP,
            EOC_CODE,
            EVENT_NAME,
            EVENT_TITLE,
            CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE,
            POM_SPONSOR_CODE,
            RESOURCE_CATEGORY_CODE,
            SPECIAL_PROJECT_CODE,
            OSD_PROGRAM_ELEMENT_CODE,
            EVENT_JUSTIFICATION,
            '${fy}EXT' AS POM_POSITION_CODE,
            FISCAL_YEAR,
            ${variable_1}")
            ->from($table1);

        $sub_query2 = $this->DBs->SOCOM_UI->get_compiled_select();

        $query1 = $this->DBs->SOCOM_UI
            ->select($selection)
            ->from('('. $sub_query2 . ') AS ZBT_EXTRACT')->get_compiled_select();


        $query3 = " LEFT JOIN (
            SELECT
                PROGRAM_NAME,
                PROGRAM_GROUP,
                PROGRAM_CODE,
                POM_SPONSOR_CODE,
                CAPABILITY_SPONSOR_CODE,
                ASSESSMENT_AREA_CODE
            FROM
                LOOKUP_PROGRAM
        ) AS LUT ON ZBT_EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
        AND ZBT_EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
        AND ZBT_EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
        AND ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
        AND ZBT_EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE";
    
        $query4 = "
            WHERE ZBT_EXTRACT.FISCAL_YEAR IN (${year_list_query})
                AND ZBT_EXTRACT.EVENT_NAME IS NOT NULL
                AND ZBT_EXTRACT.EVENT_JUSTIFICATION IS NOT NULL
                AND ZBT_EXTRACT.PROGRAM_CODE = '". $program ."'
                ${eoc_code_filter}
            GROUP BY " .  $variable_2 ."
                ZBT_EXTRACT.EOC_CODE,
                ZBT_EXTRACT.EVENT_NAME,
                ZBT_EXTRACT.EVENT_TITLE,
                ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
                ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
                ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
                ZBT_EXTRACT.OSD_PROGRAM_ELEMENT_CODE,
                ZBT_EXTRACT.POM_POSITION_CODE,
                ZBT_EXTRACT.FISCAL_YEAR
            ORDER BY
                ZBT_EXTRACT.EOC_CODE,
                ZBT_EXTRACT.EVENT_NAME,
                ZBT_EXTRACT.EVENT_TITLE,
                ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
                ZBT_EXTRACT.FISCAL_YEAR
        ";
        // print_r('================================get_eoc_summary_query 21================================');
        // print_r($query1 . $query3 . $query4);
        // print_r('======================================================================================');
        return $this->DBs->SOCOM_UI->query($query1 . $query3 . $query4)->result_array();
    }
    
    // public function eoc_summary_query(
    //     $table1, $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $program, $start_year, $selection
    // ) {
    //     $end_year = intval($start_year) + 4;
    //     $query1 = $this->DBs->SOCOM_UI->select()
    //                                   ->from($table2)
    //                                   ->where_in('CAPABILITY_SPONSOR_CODE', $l_cap_sponsor)
    //                                   ->where_in('POM_SPONSOR_CODE', $l_pom_sponsor)
    //                                   ->where_in('ASSESSMENT_AREA_CODE', $l_ass_area)
    //                                   ->get_compiled_select();
    //     $join_query1 = $this->get_lut_count('
    //         PROGRAM_NAME,PROGRAM_GROUP,PROGRAM_CODE,POM_SPONSOR_CODE,
    //         CAPABILITY_SPONSOR_CODE,ASSESSMENT_AREA_CODE
    //     ');
    //     $join_query2 = $this->get_zbt_extract($table1, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area);
    //     $query2 = $this->DBs->SOCOM_UI
    //         ->select($selection)
    //         ->from('('. $query1 .') as EXT')->get_compiled_select();
    //     $join_query3 = $this->get_lookup_eoc('
    //         EOC_CODE,EOC
    //     ');
    //     $query3 = " LEFT JOIN ($join_query3) AS LUT_EOC ON 
    //         EXT.EOC_CODE = LUT_EOC.EOC_CODE ";
    //     $query4 = " LEFT JOIN ($join_query2) as ZBT_EXTRACT
    //             ON EXT.EOC_CODE = ZBT_EXTRACT.EOC_CODE
    //             AND EXT.OSD_PROGRAM_ELEMENT_CODE = ZBT_EXTRACT.OSD_PROGRAM_ELEMENT_CODE
    //             AND EXT.CAPABILITY_SPONSOR_CODE = ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE
    //             AND EXT.POM_SPONSOR_CODE = ZBT_EXTRACT.POM_SPONSOR_CODE
    //             AND EXT.ASSESSMENT_AREA_CODE = ZBT_EXTRACT.ASSESSMENT_AREA_CODE
    //             AND EXT.EXECUTION_MANAGER_CODE = ZBT_EXTRACT.EXECUTION_MANAGER_CODE
    //             AND EXT.RESOURCE_CATEGORY_CODE = ZBT_EXTRACT.RESOURCE_CATEGORY_CODE
    //             AND EXT.FISCAL_YEAR = ZBT_EXTRACT.FISCAL_YEAR
    //             WHERE ZBT_EXTRACT.FISCAL_YEAR >= $start_year AND ZBT_EXTRACT.FISCAL_YEAR <= $end_year
    //     ";

    //     $query5 = " LEFT JOIN ($join_query1) AS LUT ON 
    //         EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
    //         AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
    //         AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //         AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //         AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
    
    //     ";

    //     if ($program) {
    //         $query5 = "$query5 AND PROGRAM_NAME='$program'";
    //     }
    //     $query6 = "GROUP BY LUT_EOC.EOC_CODE,ZBT_EXTRACT.ASSESSMENT_AREA_CODE,
    //     ZBT_EXTRACT.POM_SPONSOR_CODE,
    //     ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE,
    //     ZBT_EXTRACT.RESOURCE_CATEGORY_CODE,
    //     EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR";
    //     return $this->DBs->SOCOM_UI->query($query2 . $query5. $query3 . $query4 . $query6)
    //             ->result_array();
    // }

    // public function get_eoc_issue_summary_query(
    //     $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $program, $start_year, $selection
    // ) {
    //     $end_year = intval($start_year) + 4;
    //     $query1 = $this->DBs->SOCOM_UI->select()
    //                                   ->from($table2)
    //                                   ->where_in('CAPABILITY_SPONSOR_CODE', $l_cap_sponsor)
    //                                   ->where_in('POM_SPONSOR_CODE', $l_pom_sponsor)
    //                                   ->where_in('ASSESSMENT_AREA_CODE', $l_ass_area)
    //                                   ->get_compiled_select();
    //     $join_query1 = $this->get_lut_count('
    //         PROGRAM_NAME,PROGRAM_GROUP,PROGRAM_CODE,POM_SPONSOR_CODE,
    //         CAPABILITY_SPONSOR_CODE,ASSESSMENT_AREA_CODE
    //     ');
    //     $join_query3 = $this->get_lookup_eoc('
    //         EOC_CODE,EOC
    //     ');
        
    //     $query2 = $this->DBs->SOCOM_UI
    //                 ->select($selection)
    //                 ->from('('. $query1 .') as EXT')->get_compiled_select();
    //     $query3 = " LEFT JOIN ($join_query3) AS LUT_EOC ON 
    //             EXT.EOC_CODE = LUT_EOC.EOC_CODE ";
    //     $query4 = " LEFT JOIN ($join_query1) AS LUT ON 
    //         EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
    //         AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
    //         AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //         AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //         AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
    //         WHERE EXT.FISCAL_YEAR >= $start_year AND EXT.FISCAL_YEAR <= $end_year
    //     ";
        
    //     if ($program) {
    //         $query4 = "$query4 AND PROGRAM_NAME='$program'";
    //     }
    //     $query5 = "GROUP BY  LUT_EOC.EOC_CODE,EXT.ASSESSMENT_AREA_CODE,
    //     EXT.POM_SPONSOR_CODE,
    //     EXT.CAPABILITY_SPONSOR_CODE,
    //     EXT.RESOURCE_CATEGORY_CODE,
    //     EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR";
    //     return $this->DBs->SOCOM_UI->query($query2 . $query3 . $query4 . $query5)
    //                    ->result_array();
    // }
    
    
    // public function eoc_issue_summary_query(
    //     $table2, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $program, $start_year, $selection
    // ) {
    //     $end_year = intval($start_year) + 4;
    //     $query1 = $this->DBs->SOCOM_UI->select()
    //                                   ->from($table2)
    //                                   ->where_in('CAPABILITY_SPONSOR_CODE', $l_cap_sponsor)
    //                                   ->where_in('POM_SPONSOR_CODE', $l_pom_sponsor)
    //                                   ->where_in('ASSESSMENT_AREA_CODE', $l_ass_area)
    //                                   ->get_compiled_select();
    //     $join_query1 = $this->get_lut_count('
    //         PROGRAM_NAME,PROGRAM_GROUP,PROGRAM_CODE,POM_SPONSOR_CODE,
    //         CAPABILITY_SPONSOR_CODE,ASSESSMENT_AREA_CODE
    //     ');
    //     $join_query3 = $this->get_lookup_eoc('
    //         EOC_CODE,EOC
    //     ');
        
    //     $query2 = $this->DBs->SOCOM_UI
    //                 ->select($selection)
    //                 ->from('('. $query1 .') as EXT')->get_compiled_select();
    //     $query3 = " LEFT JOIN ($join_query3) AS LUT_EOC ON 
    //             EXT.EOC_CODE = LUT_EOC.EOC_CODE ";
    //     $query4 = " LEFT JOIN ($join_query1) AS LUT ON 
    //         EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
    //         AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
    //         AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
    //         AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
    //         AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
    //         WHERE EXT.FISCAL_YEAR >= $start_year AND EXT.FISCAL_YEAR <= $end_year
    //     ";
        
    //     if ($program) {
    //         $query4 = "$query4 AND PROGRAM_NAME='$program'";
    //     }
    //     $query5 = "GROUP BY  LUT_EOC.EOC_CODE,EXT.ASSESSMENT_AREA_CODE,
    //     EXT.POM_SPONSOR_CODE,
    //     EXT.CAPABILITY_SPONSOR_CODE,
    //     EXT.RESOURCE_CATEGORY_CODE,
    //     EXT.POM_POSITION_CODE,EXT.FISCAL_YEAR";
    //     return $this->DBs->SOCOM_UI->query($query2 . $query3 . $query4 . $query5)
    //                    ->result_array();
    // }
    
    
    private function get_lookup_eoc($select) {
        return $this->DBs->SOCOM_UI->select($select)
                            ->from('LOOKUP_EOC')
                            ->get_compiled_select();
    }
    
    private function distinct_list_table($table, $column) {
        return $this->DBs->SOCOM_UI->select("
            GROUP_CONCAT(DISTINCT $column ORDER BY $column SEPARATOR ', ')
            ")
                ->from($table)
                ->get_compiled_select();
    }

    public function get_program_group_list($l_pom_sponsor, $l_cap_sponsor, $l_ass_area) {
        return $this->DBs->SOCOM_UI
                ->select('PROGRAM_GROUP')
                ->distinct()
                ->from('LOOKUP_PROGRAM')
                ->where_in('CAPABILITY_SPONSOR_CODE', $l_cap_sponsor)
                ->where_in('POM_SPONSOR_CODE', $l_pom_sponsor)
                ->where_in('ASSESSMENT_AREA_CODE', $l_ass_area)
                ->get()
                ->result_array();
    }

    public function get_program_list($table, $l_pom_sponsor, $l_cap_sponsor, $l_ass_area, $l_execution_manager) {
        $query = $this->DBs->SOCOM_UI
                ->select('PROGRAM_GROUP')
                ->distinct()
                ->from($table);

        // Only add where_in clauses if the arrays are not empty
        if (!empty($l_cap_sponsor)) {
            $query->where_in('CAPABILITY_SPONSOR_CODE', $l_cap_sponsor);
        }
        
        if (!empty($l_ass_area)) {
            $query->where_in('ASSESSMENT_AREA_CODE', $l_ass_area);
        }

        if (!empty($l_execution_manager)) {
            $query->where_in('EXECUTION_MANAGER_CODE', $l_execution_manager);
        }

        $result = $query->get()->result_array();
        return $result;
    }

    public function get_resource_category_list($table, $l_cap_sponsor, $l_ass_area, $l_program, $l_execution_manager, $l_program_name, $l_eoc_code) {
        $this->DBs->SOCOM_UI->select('A.RESOURCE_CATEGORY_CODE');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from("$table A");
        $this->DBs->SOCOM_UI->join('LOOKUP_PROGRAM B', 'B.PROGRAM_CODE = A.PROGRAM_CODE');
        $this->DBs->SOCOM_UI->where_in('A.CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('A.ASSESSMENT_AREA_CODE',  $l_ass_area);
        if (!(count($l_program) === 1 && $l_program[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('A.PROGRAM_GROUP',  $l_program);
        }
        if (!empty($l_execution_manager)) {
            $this->DBs->SOCOM_UI->where_in('A.EXECUTION_MANAGER_CODE',  $l_execution_manager);
        }
        if (!empty($l_program_name) && !(count($l_program_name) === 1 && $l_program_name[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('B.PROGRAM_NAME',  $l_program_name);
        }
        if (!empty($l_eoc_code) && !(count($l_eoc_code) === 1 && $l_eoc_code[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('A.EOC_CODE',  $l_eoc_code);
        }
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_event_name_list($page, $l_cap_sponsor=[]) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        $this->DBs->SOCOM_UI->select('EVENT_NAME');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from($table);
        if (!empty($l_cap_sponsor)) {
            $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        }
        $this->DBs->SOCOM_UI->order_by('EVENT_NAME');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_program_name($program_code) {
        return $this->DBs->SOCOM_UI
        ->select('PROGRAM_NAME')
        ->from("LOOKUP_PROGRAM")
        ->where("PROGRAM_CODE", $program_code)
        ->get()
        ->result_array();
    }

    public function get_capability_sponsor_code($page) {
        // Handle pb_comparison page specially
        if ($page === 'pb_comparison') {
            return $this->DBs->SOCOM_UI
            ->select('CAPABILITY_SPONSOR_CODE')
            ->distinct()
            ->from('DT_PB_COMPARISON')
            ->order_by('CAPABILITY_SPONSOR_CODE')
            ->get()
            ->result_array();
        }
        
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        return $this->DBs->SOCOM_UI
        ->select('CAPABILITY_SPONSOR_CODE')
        ->distinct()
        ->from($table)
        ->order_by('CAPABILITY_SPONSOR_CODE')
        ->get()
        ->result_array();
    }

    public function get_aac_code($page) {
        $table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        return $this->DBs->SOCOM_UI
        ->select('ASSESSMENT_AREA_CODE')
        ->distinct()
        ->from($table)
        ->order_by('ASSESSMENT_AREA_CODE')
        ->get()
        ->result_array();
    }


    public function get_execution_manager_list($table, $l_cap_sponsor, $l_ass_area) {
        $this->DBs->SOCOM_UI->select('EXECUTION_MANAGER_CODE');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from($table);
        $this->DBs->SOCOM_UI->where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->order_by('EXECUTION_MANAGER_CODE', 'ASC');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }
    

    public function get_program_name_list($table, $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program) {
        $this->DBs->SOCOM_UI->select('B.PROGRAM_NAME');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from("$table A");
        $this->DBs->SOCOM_UI->join('LOOKUP_PROGRAM B', 'B.PROGRAM_CODE = A.PROGRAM_CODE');
        $this->DBs->SOCOM_UI->where_in('A.CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('A.ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->where_in('A.EXECUTION_MANAGER_CODE',  $l_execution_manager);
        if (!(count($l_program) === 1 && $l_program[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('A.PROGRAM_GROUP',  $l_program);
        }
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_eoc_code_list($table, $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program, $l_program_name) {
        $this->DBs->SOCOM_UI->select('A.EOC_CODE');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from("$table A");
        $this->DBs->SOCOM_UI->join('LOOKUP_PROGRAM B', 'B.PROGRAM_CODE = A.PROGRAM_CODE');
        $this->DBs->SOCOM_UI->where_in('A.CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('A.ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->where_in('A.EXECUTION_MANAGER_CODE',  $l_execution_manager);
        if (!(count($l_program) === 1 && $l_program[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('A.PROGRAM_GROUP',  $l_program);
        }
        if (!(count($l_program_name) === 1 && $l_program_name[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('B.PROGRAM_NAME',  $l_program_name);
        }
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_osd_pe_list($table, $l_cap_sponsor, $l_ass_area, $l_execution_manager, $l_program, $l_program_name, $l_eoc_code, $l_resource_category) {
        $this->DBs->SOCOM_UI->select('A.OSD_PROGRAM_ELEMENT_CODE');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from("$table A");
        $this->DBs->SOCOM_UI->join('LOOKUP_PROGRAM B', 'B.PROGRAM_CODE = A.PROGRAM_CODE');
        $this->DBs->SOCOM_UI->where_in('A.CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor);
        $this->DBs->SOCOM_UI->where_in('A.ASSESSMENT_AREA_CODE',  $l_ass_area);
        $this->DBs->SOCOM_UI->where_in('A.EXECUTION_MANAGER_CODE',  $l_execution_manager);
        if (!(count($l_program) === 1 && $l_program[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('A.PROGRAM_GROUP',  $l_program);
        }
        if (!(count($l_program_name) === 1 && $l_program_name[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('B.PROGRAM_NAME',  $l_program_name);
        }
        if (!(count($l_eoc_code) === 1 && $l_eoc_code[0] === "ALL")) {
            $this->DBs->SOCOM_UI->where_in('A.EOC_CODE',  $l_eoc_code);
        }
        $this->DBs->SOCOM_UI->where_in('A.RESOURCE_CATEGORY_CODE',  $l_resource_category);
        $this->DBs->SOCOM_UI->order_by('A.OSD_PROGRAM_ELEMENT_CODE', 'ASC');
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_historical_pom_eoc_dropdown($page, $program_code) {

        if ($page == 'issue') {
            $ext_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                true,
                $this->page_variables[$page]['type']['ZBT']
            );
        }
        else {
            $ext_table = $this->dynamic_year->getTable(
                $this->page_variables[$page]['subapp'],
                true,
                $this->page_variables[$page]['type']['EXT']
            );
        }

        $extract_table = $this->dynamic_year->getTable(
            $this->page_variables[$page]['subapp'],
            true,
            $this->page_variables[$page]['type']['EXTRACT']
        );

        $query1 = $this->DBs->SOCOM_UI->select('EOC_CODE')
                    ->distinct()
                    ->from($ext_table)
                    ->where('PROGRAM_CODE', $program_code)
                    ->get_compiled_select();

        $query2 = $this->DBs->SOCOM_UI->select('EOC_CODE')
                    ->distinct()
                    ->from($extract_table)
                    ->where('PROGRAM_CODE', $program_code)
                    ->get_compiled_select();
        $union_query = '(' . $query1 . ' UNION ALL ' . $query2  . ') AS A';
        $this->DBs->SOCOM_UI->select('A.EOC_CODE')->distinct();
        $this->DBs->SOCOM_UI->from($union_query);
        return  $this->DBs->SOCOM_UI->get()->result_array();
    }

    // public function get_event_name($page, $l_cap_sponsor) {
    
    //     $table = $this->dynamic_year->getTable(
    //         $this->page_variables[$page]['subapp'],
    //         true,
    //         $this->page_variables[$page]['type']['EXTRACT']
    //     );

    //     print_r( $table );

    //     return $this->DBs->SOCOM_UI
    //         ->select('EVENT_NAME')
    //         ->distinct()
    //         ->from($table)
    //         >where_in('CAPABILITY_SPONSOR_CODE',  $l_cap_sponsor)
    //         ->order_by('EVENT_NAME')
    //         ->get()
    //         ->result_array();
    // }
}

