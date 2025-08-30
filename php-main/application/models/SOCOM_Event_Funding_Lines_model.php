<?php

#[AllowDynamicProperties]
class  SOCOM_Event_Funding_Lines_model extends CI_Model {
    public function delete_event_funding_lines($type, string $event_name, int $user_id) {
        if (!$this->SOCOM_AOAD_model->is_ad_user()) {
            $log = 'User is no AD user';
            log_message('error', $log);
            throw new ErrorException($log);
        } 

        $pom_id = get_pom_id();

        $this->add_to_history($type, $event_name, $pom_id);

        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_EVENT_FUNDING_LINES';
        } else if ($type === 'issue') {
            $table = 'USR_EVENT_FUNDING_LINES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        return $this->DBs->SOCOM_UI
            ->set('IS_DELETED', 1)
            ->where('POM_ID', $pom_id)
            ->where('EVENT_NAME', $event_name)
            ->update($table);
    }

    public function add_to_history(string $type, string $event_name, int $pom_id) {
        $funding_lines = $this->get_event_funding_lines($type, $event_name, $pom_id);
        
        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_EVENT_FUNDING_LINES_HISTORY';
        } else if ($type === 'issue') {
            $table = 'USR_EVENT_FUNDING_LINES_HISTORY';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        $result = false;
        if (!empty($finding_lines)) {  
            $result = $this->DBs->SOCOM_UI
                ->set('USR_EVENT_FUNDING_LINES_ID', $funding_lines['ID'])
                ->set('EVENT_NAME', $funding_lines['EVENT_NAME'])                            
                ->set('CYCLE_ID', $funding_lines['CYCLE_ID'])
                ->set('CRITERIA_NAME_ID', $funding_lines['CRITERIA_NAME_ID'])
                ->set('POM_ID', $funding_lines['POM_ID'])
                ->set('POM_POSITION', $funding_lines['POM_POSITION'])
                ->set('FY_1', $funding_lines['FY_1'])
                ->set('FY_2', $funding_lines['FY_2'])
                ->set('FY_3', $funding_lines['FY_3'])
                ->set('FY_4', $funding_lines['FY_4'])
                ->set('FY_5', $funding_lines['FY_5'])
                ->set('APPROVE_TABLE', $funding_lines['APPROVE_TABLE'])
                ->set('YEAR_LIST', $funding_lines['YEAR_LIST'])
                ->set('USER_ID', $funding_lines['USER_ID'])
                ->set('IS_DELETED', $funding_lines['IS_DELETED'])
                ->set('UPDATE_USER_ID', $funding_lines['UPDATE_USER_ID'])
                ->set('CREATED_DATETIME', $funding_lines['CREATED_DATETIME'])
                ->set('UPDATED_DATETIME', $funding_lines['UPDATED_DATETIME'])
                ->set('APP_VERSION', $funding_lines['APP_VERSION'])
                ->insert($table);
        }

        return $result;
    }

    public function get_event_funding_lines_by_id(string $type, int $id) {
        $pom_id = get_pom_id();
        
        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_EVENT_FUNDING_LINES';
        } else if ($type === 'issue') {
            $table = 'USR_EVENT_FUNDING_LINES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        return $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('EVENT_NAME')
            ->select('CYCLE_ID')
            ->select('CRITERIA_NAME_ID')
            ->select('POM_ID')
            ->select('POM_POSITION')
            ->select('FY_1')
            ->select('FY_2')
            ->select('FY_3')
            ->select('FY_4')
            ->select('FY_5')
            ->select('APPROVE_TABLE')
            ->select('YEAR_LIST')
            ->select('USER_ID')
            ->select('IS_DELETED')
            ->select('UPDATE_USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('APP_VERSION')
            ->from($table)
            ->where('ID', $id)
            ->where('POM_ID', $pom_id)
            ->get()
            ->row_array();
    }

    public function get_event_funding_lines(string $type, string $event_name, int $pom_id, int $is_deleted = 0) {
        $pom_id = get_pom_id();
        if ($pom_id !== $pom_id) {
            log_message('error', 'Attempted to fetch usr_event_funding_lines for inactive pom_id');
            return false;
        }

        if ($type === 'zbt_summary') {
            $table = 'USR_ZBT_EVENT_FUNDING_LINES';
        } else if ($type === 'issue') {
            $table = 'USR_EVENT_FUNDING_LINES';
        } else {
            log_message('error', 'Called with incorrect type');
            return false;
        }

        return $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('EVENT_NAME')
            ->select('CYCLE_ID')
            ->select('CRITERIA_NAME_ID')
            ->select('POM_ID')
            ->select('POM_POSITION')
            ->select('FY_1')
            ->select('FY_2')
            ->select('FY_3')
            ->select('FY_4')
            ->select('FY_5')
            ->select('APPROVE_TABLE')
            ->select('YEAR_LIST')
            ->select('USER_ID')
            ->select('IS_DELETED')
            ->select('UPDATE_USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('APP_VERSION')
            ->from($table)
            ->where('POM_ID', $pom_id)
            ->where('IS_DELETED', $is_deleted)
            ->where('EVENT_NAME', $event_name)
            ->get()
            ->row_array() ?? [];
    }



    /**
     * This PHP function retrieves the overall sum of specific financial year values from a database
     * table based on certain conditions.
     * 
     * @return This function is querying a database to calculate the sum of values in columns FY_1,
     * FY_2, FY_3, FY_4, and FY_5 from tables USR_ISSUE_AD_FINAL_SAVES and USR_EVENT_FUNDING_LINES. The
     * query filters the results based on the condition that the column AD_RECOMENDATION in table
     * USR_ISSUE_AD_FINAL_S
     */
    public function get_summary_overall_sum($event_names, $l_ad_consensus, $page) {
        $pom_id = get_pom_id();

        if ($page === 'issue') {
            [$pom_year, $year_list] = get_years_issue_summary();
        } else {
            [$pom_year, $year_list] = get_years_zbt_summary();
        }

        [$table, $table_history, $table_funding_lines] = $this->get_table($page, 'final_ad');
        
        $defaultYears = function() use($table, $table_funding_lines, $pom_id, $year_list) {
            $year_list = json_encode($year_list);
            $year_list = $this->DBs->SOCOM_UI
                ->select('DISTINCT YEAR_LIST', false)
                ->from($table.' f ')
                ->join($table_funding_lines.' l', 'ON f.EVENT_NAME = l.EVENT_NAME AND l.POM_ID = f.POM_ID')
                ->where('f.POM_ID', $pom_id)
                ->where('l.IS_DELETED', 0)
                ->where('f.IS_DELETED', 0)
                ->get()
                ->row_array()['YEAR_LIST'] ?? $year_list;

            $result = [
                'YEAR_LIST' => $year_list,
                'FY_1_sum' => 0,
                'FY_2_sum' => 0,
                'FY_3_sum' => 0,
                'FY_4_sum' => 0,
                'FY_5_sum' => 0,
            ];

            return $result;
        };

        if (!in_array('Approve at Scale', $l_ad_consensus, true)) {
            $result = $defaultYears();
        } else {
            $result = $this->DBs->SOCOM_UI
                ->select('DISTINCT YEAR_LIST', false)
                ->select('COUNT(*) as c')
                ->select('SUM(l.FY_1) as FY_1_sum', false)
                ->select('SUM(l.FY_2) as FY_2_sum', false)
                ->select('SUM(l.FY_3) as FY_3_sum', false)
                ->select('SUM(l.FY_4) as FY_4_sum', false)
                ->select('SUM(l.FY_5) as FY_5_sum', false)
                ->from($table.' f')
                ->join($table_funding_lines.' l', 'ON f.EVENT_NAME = l.EVENT_NAME AND l.POM_ID = f.POM_ID')
                ->where('f.POM_ID', $pom_id)
                ->where('l.IS_DELETED', 0)
                ->where('f.IS_DELETED', 0)
                ->where_in('l.EVENT_NAME', $event_names)
                ->where_in('f.AD_RECOMENDATION', 'Approve at Scale')
                ->get()->row_array();
                
                if ($result['c'] === 0) {
                    $result = $defaultYears();
                } else {
                    unset($result['c']);
                }
        }

        return $result;
    }

    /**
     * This PHP function retrieves summary data based on event names, consensus list, and page number.
     * 
     * @param event_names Event names is an array containing the names of events for which you want to
     * retrieve data. It is used as a filter in the database query to fetch information related to
     * these specific events.
     * @param l_ad_consensus The `l_ad_consensus` parameter seems to be an array that contains values
     * related to some form of consensus. In the provided code snippet, it is checked whether the value
     * 'Approve' is present in this array. If 'Approve' is not found in the array, a specific query
     * @param page It seems like you forgot to provide the details for the `page` parameter in the
     * `get_summary_overall_sum_approve` function. If you need assistance with something related to the
     * `page` parameter or any other part of the code, feel free to ask!
     * 
     * @return The function `get_summary_overall_sum_approve` returns an array of results based on the
     * conditions specified in the function. If the 'Approve' value is not found in the
     * `` array, it retrieves a list of fiscal years with default values for the sum
     * delta. If the 'Approve' value is found, it calculates the sum delta for each fiscal year based
     */
    public function get_summary_overall_sum_approve($event_names, $l_ad_consensus, $page) {
        $pom_id = get_pom_id();

        if ($page === 'issue') {
            $getTableParam1 = 'ISS_SUMMARY';
            $getTableParam2 = 'ISS_EXTRACT';
            $pomYearSubapp = 'ISS_SUMMARY_YEAR';
            $yearTest = !in_array('Approve', $l_ad_consensus, true);
            [$pom_year, $year_list] = get_years_issue_summary();
        } else {
            $getTableParam1 = 'ZBT_SUMMARY';
            $getTableParam2 = 'ZBT_EXTRACT';
            $pomYearSubapp = 'ZBT_SUMMARY_YEAR';
            $yearTest = !in_array('Approve', $l_ad_consensus, true);
            [$pom_year, $year_list] = get_years_zbt_summary();
        }
        $pom_table = $this->dynamic_year->getTable($getTableParam1, true, $getTableParam2);

        [$table, $table_history, $table_funding_lines] = $this->get_table($page, 'final_ad');

        $defaultYears = function() use($table, $table_funding_lines, $pom_id, $year_list) {
            $year_list = json_encode($year_list);

            $year_list = $this->DBs->SOCOM_UI
                ->select('DISTINCT YEAR_LIST', false)
                ->from($table.' f ')
                ->join($table_funding_lines.' l', 'ON f.EVENT_NAME = l.EVENT_NAME AND l.POM_ID = f.POM_ID')
                ->where('f.POM_ID', $pom_id)
                ->where('l.IS_DELETED', 0)
                ->where('f.IS_DELETED', 0)
                ->get()
                ->row_array()['YEAR_LIST'] ?? $year_list;
            
            $year_list = json_decode($year_list, true);

            $result = [];
            for($i = 0; $i < 5; $i++) {
                $result[] = [
                    'FISCAL_YEAR' => $year_list[$i],
                    'SUM_DELTA' => 0
                ];
            }

            return $result;
        };

        if ($yearTest) {
            $result = $defaultYears();
        } else {
            $result = $this->DBs->SOCOM_UI
                ->select('ie.FISCAL_YEAR')
                ->select('SUM(ie.DELTA_AMT) AS SUM_DELTA', false)
                ->from($pom_table . ' ie')                                                                                                                                                                                                                                                          
                ->join($table . ' f', 'ON ie.EVENT_NAME = f.EVENT_NAME')
                ->where('f.POM_ID', $pom_id)
                ->where('f.IS_DELETED', 0)
                ->where_in('f.EVENT_NAME', $event_names)
                ->where('f.AD_RECOMENDATION', 'Approve')
                ->group_by('ie.FISCAL_YEAR')
                ->get();
                
                if ($result->num_rows() === 0) {
                    $result = $defaultYears();
                } else {
                    $result = $result->result_array();
                }
        }

        return $result;
    }

    public function get_table($type, $ao_ad_status) {
        if ($type === 'zbt_summary') {
            if ($ao_ad_status === 'final_ad') {
                $table = 'USR_ZBT_AD_FINAL_SAVES';
                $table_history = 'USR_ZBT_AD_FINAL_SAVES_HISTORY';
                $table_funding_lines = 'USR_ZBT_EVENT_FUNDING_LINES';
            }
        } elseif ($type === 'issue') {
            if ($ao_ad_status === 'final_ad') {
                $table = 'USR_ISSUE_AD_FINAL_SAVES';
                $table_history = 'USR_ISSUE_AD_FINAL_SAVES_HISTORY';
                $table_funding_lines = 'USR_EVENT_FUNDING_LINES';
            }
        }

        return $table ? [$table, $table_history, $table_funding_lines] : [false, false, false];
    }

    public function get_review_status_zbt(){
        $pom_table = $this->dynamic_year->getTable('ZBT_SUMMARY', true, 'ZBT_EXTRACT');
        $review_status = "
            WITH REVIEW AS (SELECT A.EVENT_ID, GROUP_CONCAT(DISTINCT A.RECS) AS RECS
            FROM

            (SELECT EVENT_ID, AO_RECOMENDATION AS RECS
            FROM USR_ZBT_AO_SAVES
            WHERE IS_DELETED = 0

            UNION ALL

            SELECT EVENT_ID, AD_RECOMENDATION AS RECS
            FROM USR_ZBT_AD_SAVES
            WHERE IS_DELETED = 0) AS A

            WHERE A.RECS != ''
            GROUP BY A.EVENT_ID),

            `EVENTS` AS (SELECT DISTINCT EVENT_NAME FROM {$pom_table})

            SELECT `EVENTS`.EVENT_NAME,
            CASE
                WHEN REVIEW.RECS LIKE '%Disapprove%' THEN 'Disapproval Flag'
                WHEN REVIEW.RECS IS NULL THEN 'Unreviewed'
                ELSE 'No Disapproval Flag'
                END AS FLAG

            FROM `EVENTS`

            LEFT JOIN

            REVIEW

            ON `EVENTS`.EVENT_NAME = REVIEW.EVENT_ID;";
        
        $query = $this->DBs->SOCOM_UI->query($review_status);
        return $query->result_array();
    }

    public function get_review_status_iss(){
        $pom_table = $this->dynamic_year->getTable('ISS_SUMMARY', true, 'ISS_EXTRACT');
        $review_status = "
            WITH REVIEW AS (SELECT B.EVENT_ID, GROUP_CONCAT(B.FLAG_TEXT SEPARATOR ', ') AS CONCAT_FLAG
            FROM (SELECT DISTINCT A.EVENT_ID,
            CASE
                WHEN RECS = 'Approve' THEN 'Approval Flag'
                WHEN RECS = 'Approve at Scale' THEN 'Approve at Scale Flag'
                WHEN RECS = 'Disapprove' THEN 'Disapprove Flag'
                END AS FLAG_TEXT

            FROM

            (SELECT EVENT_ID, AO_RECOMENDATION AS RECS
            FROM USR_ISSUE_AO_SAVES
            WHERE IS_DELETED = 0

            UNION ALL

            SELECT EVENT_ID, AD_RECOMENDATION AS RECS
            FROM USR_ISSUE_AD_SAVES
            WHERE IS_DELETED = 0) AS A

            WHERE A.RECS != ''
            ORDER BY EVENT_ID) AS B
            GROUP BY B.EVENT_ID),

            `EVENTS` AS (SELECT DISTINCT EVENT_NAME FROM {$pom_table})

            SELECT `EVENTS`.EVENT_NAME, COALESCE(REVIEW.CONCAT_FLAG, 'Unreviewed') AS FLAGS

            FROM `EVENTS`

            LEFT JOIN

            REVIEW

            ON `EVENTS`.EVENT_NAME = REVIEW.EVENT_ID;";

        $query = $this->DBs->SOCOM_UI->query($review_status);
        return $query->result_array();
    }
}