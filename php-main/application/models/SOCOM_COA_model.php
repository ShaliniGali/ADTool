<?php

#[AllowDynamicProperties]
class  SOCOM_COA_model extends CI_Model {

    const APPLICATION_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('coa');
        $this->load->library('SOCOM/Dynamic_Year');
        $this->tables = [
            'ISS' => function() {
                return $this->dynamic_year->getTable('RESOURCE_CONSTRAINED_COA', true, 'ISS');
            },
            'ISS_EXTRACT' => function() {
                return $this->dynamic_year->getTable('ISS_SUMMARY', true, 'ISS_EXTRACT');
            },
            'RC_T' => function() {
                return $this->dynamic_year->getTable('RESOURCE_CONSTRAINED_COA', true, 'ISS');
            },
        ];
    }
    
    /**
     * Stores an optimizer run in the database
     * @param string $optimzer_input
     * @param string $coa_output
     * @return int|boolean
     */
    public function store_run($optimzer_input, $calculate_budget_result, $filter_budget_result, string $type_of_coa, int $storm_flag = 1)
    {
        if(!in_array($type_of_coa, ['ISS', 'ISS_EXTRACT', 'RC_T'], true)){
            show_error('Unable to save Type Of COA');
        }
        if(!in_array($storm_flag, [0, 1], true)){
            show_error('Unable to save Storm Flag Value');
        }
        $user_id = (int)$this->session->userdata['logged_in']['id'];

        $criteria_name_id = get_criteria_name_id();
        $cycle_id = get_cycle_id();

        [$pom_year, $year_list] = get_years_coa($type_of_coa === 'ISS_EXTRACT');
        
        $pom = $this->SOCOM_Dynamic_Year_model->getCurrentPomFull();

        $result = $this->DBs->SOCOM_UI
            ->set('OPTIMIZER_INPUT', $optimzer_input)
            ->set('COA_VALUES', $filter_budget_result) 
            ->set('CALC_BUDGET_VALUES', $calculate_budget_result)
            ->set('CREATED_DATETIME', 'NOW()', false)
            ->set('USER_ID', $user_id)
            ->set('CRITERIA_NAME_ID', $criteria_name_id)
            ->set('CYCLE_ID', $cycle_id)
            ->set('TYPE_OF_COA', $type_of_coa)
            ->set('APP_VERSION', APP_TAG)
            ->set('POM_YEAR', $pom_year)
            ->set('YEAR_LIST', json_encode($year_list))
            ->set('POM_ID', $pom['ID'])
            ->set('STORM_FLAG', $storm_flag)
            ->set('POSITION', $pom['LATEST_POSITION'])
            ->insert('USR_LOOKUP_SAVED_COA');
        
        if ($result === true) {
            $result = $this->DBs->SOCOM_UI->insert_id();
        }

        return $result;
    }

    public function get_metadata(int $id, bool $api_input_output = false) {
        if ($api_input_output === true) {
            $this->DBs->SOCOM_UI
            ->select('COA_VALUES')
            ->select('OPTIMIZER_INPUT');
        }
        return $this->DBs->SOCOM_UI
            ->select('APP_VERSION')
            ->select('TYPE_OF_COA')
            ->select('POM_YEAR')
            ->select('YEAR_LIST')
            ->select('CREATED_DATETIME')
            ->select('USER_ID')
            ->select('CRITERIA_NAME_ID')
            ->select('CYCLE_ID')
            ->from('USR_LOOKUP_SAVED_COA')
            ->where('ID', $id)
            ->get()
            ->row_array() ?? [];
    }

    public function get_api_version(int $id) {
        return $this->DBs->SOCOM_UI
            ->select('APP_VERSION')
            ->from('USR_LOOKUP_SAVED_COA')
            ->where('ID', $id)
            ->get()
            ->row_array()['APP_VERSION'] ?? false;
    }
        
    /**
     * Stores a user save of an optimizer run in the database
     * @param int $id
     * @param string $name
     * @param string $description
     * @return int|boolean
     */
    public function store_user_run($id, $name, $description)
    {
        $result = false;

        if ($this->saved_coa_exists($id) === 0) {
            throw InvalidArgumentException('There is not an existing COA to associate with the user request.  Please run the optimizer again.');
        }
        
        $user_id = (int)$this->session->userdata['logged_in']['id'];

        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI
            ->set('COA_TITLE', $name)
            ->set('COA_DESCRIPTION', $description)
            ->set('CREATED_DATETIME', 'NOW()', false)
            ->set('USER_ID', $user_id)
            ->set('SAVED_COA_ID', $id)
            ->set('CRITERIA_NAME_ID', $criteria_name_id)
            ->insert('USR_LOOKUP_USER_SAVED_COA');
            
        if ($result === true) {
            $result = $this->DBs->SOCOM_UI->insert_id();
        }

        return $result;
    }

    // --------------------------------------------------------------------

    public function saved_coa_exists($id) {
        $user_id = (int)$this->session->userdata['logged_in']['id'];
        $criteria_name_id = get_criteria_name_id();

        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) as count')
            ->from('USR_LOOKUP_SAVED_COA')
            ->where('ID', $id)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->get()->row_array()['count'] ?? 0;
    }

    // --------------------------------------------------------------------

    /**
     * Returns an array of saved optimizer runs
     * @return array
     */
    public function get_user_saved_coa() {
        $user_id = (int)$this->session->userdata['logged_in']['id'];
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI
            ->select('CASE WHEN `usc`.`STATE` IS NOT NULL THEN CONCAT(COA_TITLE, "*") ELSE COA_TITLE END AS COA_TITLE')
            ->select('COA_DESCRIPTION')
            ->select('SAVED_COA_ID')
            ->from('USR_LOOKUP_USER_SAVED_COA usc')
            ->join('USR_LOOKUP_SAVED_COA sc', 'usc.SAVED_COA_ID = sc.ID AND usc.USER_ID = sc.USER_ID')
            ->where('sc.USER_ID', $user_id)
            ->where('sc.CRITERIA_NAME_ID', $criteria_name_id)
            ->get()->result_array();

        return $result;
    }

    /**
     * Returns an array of saved optimizer runs
     * @return array
     */
    public function get_user_saved_coa_data($ids) {
        if (empty($ids)) {
            return [];
        }
        
        $user_id = (int)$this->session->userdata['logged_in']['id'];
        $criteria_name_id = get_criteria_name_id();
        
        $result = $this->DBs->SOCOM_UI
            ->select('COA_VALUES')
            ->select('OPTIMIZER_INPUT')
            ->select('CALC_BUDGET_VALUES')
            ->select('CASE WHEN `usc`.`STATE` IS NOT NULL THEN CONCAT(COA_TITLE, "*") ELSE COA_TITLE END AS COA_TITLE')
            ->select('SAVED_COA_ID')
            ->select('COA_TYPE')
            ->select('STATE')
            ->select('OVERRIDE_TABLE_SESSION')
            ->select('POM_YEAR')
            ->select('YEAR_LIST')
            ->select('TYPE_OF_COA')
            ->from('USR_LOOKUP_USER_SAVED_COA usc')
            ->join('USR_LOOKUP_SAVED_COA sc', 'usc.SAVED_COA_ID = sc.ID AND usc.USER_ID = sc.USER_ID')
            ->where_in('SAVED_COA_ID', $ids)
            ->where('sc.USER_ID', (int)$user_id)
            ->where('sc.CRITERIA_NAME_ID', $criteria_name_id)
            ->get()->result_array();
        
        $new_result = $this->set_override_remaining_balance($result);
        return $new_result;
    }

    /**
     * Returns an array of list of dropdown codes
     * @return array
     */
    public function get_dropdown_codes($type, $program_codes = [], $eoc_codes = [], $order_type=null, $type_of_coa='ISS') {
        $this->DBs->SOCOM_UI
            ->select($type)
            ->distinct()
            ->from($this->tables[$type_of_coa]());
        if ($order_type) {
            $this->DBs->SOCOM_UI->order_by($order_type);
        }
        if (!empty($program_codes)) {
            $this->DBs->SOCOM_UI->where_in('PROGRAM_CODE', $program_codes);
            if (!empty($eoc_codes)) {
                $this->DBs->SOCOM_UI->where_not_in('EOC_CODE', $eoc_codes);
            }
        }
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    /**
     * Returns an array of list of program IDs
     * @return array
     */
    public function get_program_ids($coa_id, $type_of_coa) {
        $program_ids = [];
        
        $result =$this->DBs->SOCOM_UI
            ->select('OPTIMIZER_INPUT')
            ->from('USR_LOOKUP_SAVED_COA')
            ->where('ID', $coa_id)
            ->get()->row_array();

        if (isset($result['OPTIMIZER_INPUT']) && $result['OPTIMIZER_INPUT']) {
            $optimzer_input = json_decode($result['OPTIMIZER_INPUT'], true);
            $program_ids = $optimzer_input[0]['ProgramIDs'];
        }
        else { // for merge coa
            $program_ids = [];
        }
        return array_values($program_ids);
    }

        /**
     * Returns an array of list of dropdown codes
     * @return array
     */
    public function get_unselected_programs(
        $program_codes, $match_row_ids, $type_of_coa, $order_type=null
    ) {
        $unselected_programs = [];
        if ($type_of_coa == 'ISS_EXTRACT') {
            $columns = ['EOC_CODE', 'POM_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE', 'CAPABILITY_SPONSOR_CODE', 'EVENT_NAME', 'OSD_PROGRAM_ELEMENT_CODE', 'RESOURCE_CATEGORY_CODE'];
        }
        else {
            $columns = ['EOC_CODE', 'POM_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE', 'CAPABILITY_SPONSOR_CODE', 'EXECUTION_MANAGER_CODE', 'OSD_PROGRAM_ELEMENT_CODE', 'RESOURCE_CATEGORY_CODE'];
        }

        $this->DBs->SOCOM_UI
            ->select("PROGRAM_CODE, POM_SPONSOR_CODE, CONCAT_WS('_', " . implode(', ', $columns) . ") as MATCH_ROW_ID")
            ->from($this->tables[$type_of_coa]());
        if ($order_type) {
            $this->DBs->SOCOM_UI->order_by($order_type);
        }
        if (!empty($program_codes)) {
            $this->DBs->SOCOM_UI->where_in('PROGRAM_CODE', $program_codes);
        }

        if ($type_of_coa == 'ISS_EXTRACT') {
            $this->DBs->SOCOM_UI->where('DELTA_AMT > 0');
        }
        else {
            $this->DBs->SOCOM_UI->where('RESOURCE_K > 0');
        }
        $result = $this->DBs->SOCOM_UI->get()->result_array();

        $unselected_programs = array_filter($result, function($value) use($match_row_ids) {
            return !in_array($value['MATCH_ROW_ID'],$match_row_ids );
        });

        $unselected_programs = array_reduce($unselected_programs, function($carry, $item) {
            if (!isset($carry[$item['PROGRAM_CODE']])) {
                $carry[$item['PROGRAM_CODE']] = $item;
            }
            return $carry;
        }, []);
        return $unselected_programs;
    }

    /**
     * Returns an array of funding values for each program
     * @return array
     */
    public function fetchOutputInfo($ids) {
        return php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($ids),
            RHOMBUS_PYTHON_URL.'/socom/prog_eoc_funding'
        );
    }

    /**
     * Returns an array of funding values for each program (for COAs that were optimized with ISS_EXTRACT)
     * @return array
     */
    public function fetchOutputInfoIssExtract($ids) {
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($ids),
            RHOMBUS_PYTHON_URL.'/socom/prog_event_funding'
        );

        return json_decode($res, true);
    }

    /**
     * Returns an array of funding valur for each program
     * @return array
     */
    public function get_coa_metadata($type_of_coa, $params, $rk_non_zero = false) {

        $program_code = $params['program_code'] ?? '';
        $eoc_code = $params['eoc_code'] ?? [];
        $capability_sponsor_code = $params['capability_sponsor_code'] ?? [];
        $ass_area_code = $params['ass_area_code'] ?? [];
        $resource_category_code = $params['resource_category_code'] ?? [];
        $event_name = $params['event_name'] ?? [];
        $osd_pe_code = $params['osd_pe_code'] ?? [];
        $execution_manager_code = $params['execution_manager_code'] ?? [];

        $api_params = array();
        if (!empty($program_code)) {
            $api_params['PROGRAM_CODE'] = $program_code;
        }
        
        if (!empty($eoc_code)) {
            $api_params['EOC_CODE'] = $eoc_code;
        }
        if (!empty($capability_sponsor_code)) {
            $api_params['CAPABILITY_SPONSOR_CODE'] = $capability_sponsor_code;
        }
        if (!empty($ass_area_code)) {
            $api_params['ASSESSMENT_AREA_CODE'] = $ass_area_code;
        }
        if (!empty($resource_category_code)) {
            $api_params['RESOURCE_CATEGORY_CODE'] = $resource_category_code;
        }
        if (!empty($event_name)) {
            $api_params['EVENT_NAME'] = $event_name;
        }
        if (!empty($osd_pe_code)) {
            $api_params['OSD_PROGRAM_ELEMENT_CODE'] = $osd_pe_code;
        }
        if (!empty($execution_manager_code)) {
            $api_params['EXECUTION_MANAGER_CODE'] = $execution_manager_code;
        }

        if ($type_of_coa === 'rc-t') {
            $endpoint_path = '/socom/metadata/iss';
        }
        else {
            $endpoint_path = '/socom/metadata/' . $type_of_coa;
        }
        $endpoint_path = "{$endpoint_path}?rk_non_zero=". ($rk_non_zero ? 'true' : 'false');
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.$endpoint_path
        );
        return json_decode($res, true);
    }

    public function get_fiscal_years($table) {
        $result = $this->DBs->SOCOM_UI
            ->select('FISCAL_YEAR')
            ->distinct()
            ->from($table)
            ->get()->result_array();
        if (!empty($result)) {
            $result = array_column($result, 'FISCAL_YEAR');
        }
        return $result;
    }

    /**
     * Stores coa override table metadata in the database
     * @param string $override_table_metadata
     * @param int $saved_coa_id
     * @return int|boolean
     */
    public function store_coa_metadata($override_table_metadata, $saved_coa_id)
    {
        $user_id = (int)$this->session->userdata['logged_in']['id'];
        $criteria_name_id = get_criteria_name_id();

        return $this->DBs->SOCOM_UI
            ->set('OVERRIDE_TABLE_METADATA', $override_table_metadata)
            ->set('USER_ID', $user_id)
            ->where('ID', $saved_coa_id)
            ->where('IS_DELETED', 0)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->update('USR_LOOKUP_SAVED_COA');
    }

    /**
     * Update coa override table modal status in the database
     * @param int $saved_coa_id
     * @param string $status_value
     * @return int|boolean
     */
    public function change_scenario_status(
        $saved_coa_id, $status_value
    ){
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        $this->DBs->SOCOM_UI
            ->set('STATE', $status_value)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->where('SAVED_COA_ID', $saved_coa_id);

        return $this->DBs->SOCOM_UI->update('USR_LOOKUP_USER_SAVED_COA') ? true : false;
    }


    /**
     * Update coa override table metadata and override table in the database
     * @param int $saved_coa_id
     * @param string $field
     * @param string $data
     * @return int|boolean
     */
    public function manual_override_save(
        $saved_coa_id, $field, $data
    ){
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        $this->DBs->SOCOM_UI
            ->set($field, json_encode(json_decode($data, true), true))
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->where('ID', $saved_coa_id);
            
        return $this->DBs->SOCOM_UI->update('USR_LOOKUP_SAVED_COA') ? true : false;
    }

    /**
     * Update coa override form in the database
     * @param int $saved_coa_id
     * @param string $overrideForm
     * @return int|boolean
     */
    public function save_override_form(
        $saved_coa_id, $overrideForm
    ){
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        $this->DBs->SOCOM_UI
        ->set('OVERRIDE_FORM_SESSION',json_encode(json_decode($overrideForm, true), true))
        ->where('USER_ID', $user_id)
        ->where('CRITERIA_NAME_ID', $criteria_name_id)
        ->where('ID', $saved_coa_id);

        return $this->DBs->SOCOM_UI->update('USR_LOOKUP_SAVED_COA') ? true : false;
    }

    /**
     * Get coa override table session data from the database
     * @param int $saved_coa_id
     * @return int|boolean
     */
    public function get_manual_override_data($saved_coa_id) {
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        return $this->DBs->SOCOM_UI->select('OVERRIDE_TABLE_SESSION')
                                ->select('OVERRIDE_TABLE_METADATA')
                                ->select('OVERRIDE_FORM_SESSION')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('CRITERIA_NAME_ID', $criteria_name_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->result_array();
    }

    /**
     * Update coa override table metadata and override table in the database
     * @param int $saved_coa_id
     * @param string $overridetable
     * @param string $override_table_metadata
     * @return int|boolean
     */
    public function get_manual_override_status($saved_coa_id) {
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        return $this->DBs->SOCOM_UI->select('STATE')
                                ->from('USR_LOOKUP_USER_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('CRITERIA_NAME_ID', $criteria_name_id)
                                ->where('SAVED_COA_ID', $saved_coa_id)
                                ->get()
                                ->result_array()[0];
    } 
    
    public function set_override_remaining_balance($result) {
        foreach($result as &$coa) {
            if (isset($coa['OVERRIDE_TABLE_SESSION'])) {
                $coa_values = json_decode($coa['COA_VALUES'], true);
                $remaining = &$coa_values['remaining'];
                $override_table_session = json_decode($coa['OVERRIDE_TABLE_SESSION'], true);
                foreach($remaining as $fy => $value) {
                    if (isset($override_table_session['budget_uncommitted'][1])) {
                        if ($coa['TYPE_OF_COA'] === 'RC_T') {
                            $remaining[$fy] = &$override_table_session['budget_uncommitted'][1][$fy];
                            $remaining[$fy] =  $remaining[$fy] * -1;
                        }
                        else {
                            $remaining[$fy] = &$override_table_session['budget_uncommitted'][1][$fy];
                        }
                    }
                }
                $coa_values['remaining'] = $remaining;
                $coa['COA_VALUES'] = json_encode($coa_values);
            }
        }
        return $result;
    }

    /**
     * Returns an array of weighted score
     * @param int $weight_id
     * @param array $program_ids
     * @param int $score_type
     * @return array
     */
    public function get_weighted_score(
        $weight_id, $program_ids, $type_of_coa
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];

        $api_params = array();
        $api_params['weight_id'] = $weight_id;
        $api_params['user_id'] =  $user_id;
        $api_params['program_ids'] = $program_ids;
        $api_params['criteria_name_id'] = get_criteria_name_id();
        if ($type_of_coa === 'ISS_EXTRACT') {
            $api_path = '/optimizer/weighted_scores';
        }
        else {
            $api_path = '/optimizer/v2/weighted_scores';
        }
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL. $api_path
        );
        return json_decode($res, true);
    }

    /**
     * Returns saved coa optimizer input
     * @param int $saved_coa_id
     * @return int
     */
    public function get_saved_coa_optimizer_input(
        $saved_coa_id
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI->select('OPTIMIZER_INPUT')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('CRITERIA_NAME_ID', $criteria_name_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->row_array();
        
        if (!empty($result) && !empty($result['OPTIMIZER_INPUT'])) {
            $option = json_decode($result['OPTIMIZER_INPUT'], true)[0];
        }
        return empty($option) ? '' : $option;
    }

    public function get_saved_coa_scores(
        $saved_coa_id
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI
                                ->select('COA_VALUES->\'$.selected_programs[*].program_id\' as program_id')
                                ->select('COA_VALUES->\'$.selected_programs[*].total_storm_score\' as storm_scores')
                                ->select('COA_VALUES->\'$.selected_programs[*].weighted_pom_score\' as pom_scores')
                                ->select('COA_VALUES->\'$.selected_programs[*].weighted_guidance_score\' as guidance_scores')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('CRITERIA_NAME_ID', $criteria_name_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->result_array();
        $coa_values = '';
        if (!empty($result)) {
            $storm_scores = array_combine(
                json_decode($result[0]['program_id'], true), 
                json_decode($result[0]['storm_scores'], true)
            );
            $pom_scores = array_combine(
                json_decode($result[0]['program_id'], true), 
                json_decode($result[0]['pom_scores'], true)
            );
            $guidance_scores = array_combine(
                json_decode($result[0]['program_id'], true), 
                json_decode($result[0]['guidance_scores'], true)
            );
            return [
                'total_storm_scores' => $storm_scores,
                'weighted_pom_score' => $pom_scores,
                'weighted_guidance_score' => $guidance_scores
            ];
        }
        return $coa_values;
    }

    public function get_saved_coa_values(
        $saved_coa_id
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI->select('COA_VALUES')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('CRITERIA_NAME_ID', $criteria_name_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->result_array();
        $coa_values = [];
        if (!empty($result)) {
            $coa_values = json_decode($result[0]['COA_VALUES'], true);
        }
        return !empty($coa_values) ? $coa_values : '';
    }

    /**
     * Returns jca alignment data
     * @param int $saved_coa_id
     * @return array
     */
    public function get_jca_alignment_data($saved_coa_id, $coa_type) {
        $endpoint_version = '';
        if ($coa_type == 'iss') {
            $endpoint_version = 'v2/';
        }

        if($this->is_manual_override($saved_coa_id)) {
            $path = "/optimizer/{$endpoint_version}jca_manual_override";
        }
        else {
            $path = "/optimizer/{$endpoint_version}jca_alignment/opt-run";
        }

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL."{$path}?id=" . strval($saved_coa_id)
        );

        return json_decode($res, true);
    }

    /**
     * Returns jca alignment description
     * @param int $saved_coa_id
     * @return array
     */
    public function get_jca_alignment_description($jca_ids) {
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($jca_ids, true),
            RHOMBUS_PYTHON_URL.'/socom/jca_description'
        );

        return json_decode($res, true);
    }

    /**
     * Returns jca alignment noncovered ids
     * @param int $saved_coa_id
     * @return array
     */
    public function get_jca_alignment_noncovered($jca_ids, $level) {

        $api_params = [];
        $api_params['ids'] = $jca_ids;
        $api_params['level'] = $level;
        
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params, true),
            RHOMBUS_PYTHON_URL.'/socom/jca/noncovered'
        );

        return json_decode($res, true);
    }

    /**
     * Returns cga alignment data
     * @param int $saved_coa_id
     * @return array
     */
    public function get_capability_gaps_data($saved_coa_id, $coa_type) {
        $endpoint_version = '';
        if ($coa_type == 'iss') {
            $endpoint_version = 'v2/';
        }

        if($this->is_manual_override($saved_coa_id)) {
            $path = "/optimizer/{$endpoint_version}cga_manual_override";
        }
        else {
            $path = "/optimizer/{$endpoint_version}cga_alignment/opt-run";
        }

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL."{$path}?id=" . strval($saved_coa_id)
        );

        return json_decode($res, true);
    }

    /**
     * Returns cga alignment description
     * @param int $saved_coa_id
     * @return array
     */
    public function get_capability_gaps_description($cga_ids) {
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($cga_ids, true),
            RHOMBUS_PYTHON_URL.'/socom/cga_description'
        );

        return json_decode($res, true);
    }

    /**
     * Returns cga alignment noncovered ids
     * @param array $cga_ids
     * @param string $level
     * @return array
     */
    public function get_capability_gaps_noncovered($cga_ids, $level) {

        $api_params = [];
        $api_params['ids'] = $cga_ids;
        $api_params['level'] = $level;

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params, true),
            RHOMBUS_PYTHON_URL.'/socom/cga/noncovered'
        );

        return json_decode($res, true);
    }

    /**
     * Returns kops ksps alignment data
     * @param int $saved_coa_id
     * @return array
     */
    public function get_kop_ksp_data($saved_coa_id, $coa_type) {
        $endpoint_version = '';
        if ($coa_type == 'iss') {
            $endpoint_version = 'v2/';
        }

        if($this->is_manual_override($saved_coa_id)) {
            $path = "/optimizer/{$endpoint_version}kp_manual_override";
        }
        else {
            $path = "/optimizer/{$endpoint_version}kop_ksp_alignment/opt-run";
        }

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL."{$path}?id=" . strval($saved_coa_id)
        );

        return json_decode($res, true);
    }

    /**
     * Returns kops ksps  alignment description
     * @param int $kop_ksp_ids
     * @return array
     */
    public function get_kop_ksp_description($kop_ksp_ids) {
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($kop_ksp_ids, true),
            RHOMBUS_PYTHON_URL.'/socom/kop_ksp_description'
        );

        return json_decode($res, true);
    }

    /**
     * Returns cga alignment noncovered ids
     * @param array $cga_ids
     * @param string $level
     * @return array
     */
    public function get_kop_ksp_noncovered($cga_ids, $level) {

        $api_params = [];
        $api_params['ids'] = $cga_ids;
        $api_params['level'] = $level;

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params, true),
            RHOMBUS_PYTHON_URL.'/socom/kop-ksp/noncovered'
        );

        return json_decode($res, true);
    }


    /**
     * Returns detailed comparison issue analysis data
     * @param array $saved_coa_ids
     * @return array
     */
    public function get_detailed_comparison_issue_analysis_data($saved_coa_ids) {
        $api_params = ['coa_ids' => $saved_coa_ids];

        $path = ""; // TODO - Add path

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params, true),
            RHOMBUS_PYTHON_URL.$path
        );

        return json_decode($res, true);
    }

    /**
     * Returns detailed summary issue analysis data
     * @param int $saved_coa_id
     * @return array
     */
    public function get_detailed_summary_issue_analysis_data($saved_coa_id) {
        $api_params = ['coa_id' => $saved_coa_id];

        $path = ""; // TODO - Add path

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params, true),
            RHOMBUS_PYTHON_URL.$path
        );

        return json_decode($res, true);
    }

    /**
     * Returns if the coa has been manual overridden
     * @param int $saved_coa_id
     * @return boolean
     */

    public function is_manual_override($saved_coa_id) {
        $result = $this->get_manual_override_status($saved_coa_id);
        $session = $this->get_manual_override_session($saved_coa_id);

        return $result['STATE'] !== null && $session['OVERRIDE_TABLE_SESSION'] !== null ? true : false;
    }

    /**
     * Returns manaul override coa data
     * @param int $saved_coa_id
     * @return array
     */

     public function get_manual_override_session($saved_coa_id) {
        $user_id = $this->session->userdata('logged_in')['id'];
        $criteria_name_id = get_criteria_name_id();

        return $this->DBs->SOCOM_UI->select('OVERRIDE_TABLE_SESSION')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('CRITERIA_NAME_ID', $criteria_name_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->result_array()[0];
    }

    /**
     * Returns program_group
     * @param int $saved_coa_id
     * @return array
     */

    public function get_program_group_map($type_of_coa, $program_ids=null) {

        if ($type_of_coa === 'ISS_EXTRACT') {
            $event_name_cond = 'EVENT_NAME IS NOT NULL';
        } else {
            $event_name_cond = 'EVENT_NAME IS NULL';
        }

        $this->DBs->SOCOM_UI->select('ID, PROGRAM_GROUP')
                                ->from('LOOKUP_PROGRAM');
        $this->DBs->SOCOM_UI->where($event_name_cond, null, false);
        if ($program_ids !== null) {
            $this->DBs->SOCOM_UI->where_in('ID', $program_ids);
        }

        $program_groups = $this->DBs->SOCOM_UI->group_by('ID')
                                            ->get()
                                            ->result_array();

        $program_group_map = [];
        foreach($program_groups as $program_group) {
            $program_group_map[$program_group['ID']] = $program_group['PROGRAM_GROUP'];
        }
        return $program_group_map;
    }

    /**
     * Return coas saved by me (via optimizer run) and shared to me from other users
     * @return array
     */
    public function get_my_coa(string $type_of_coa = 'ISS') {
        if(!in_array($type_of_coa, ['ISS', 'ISS_EXTRACT', 'RC_T'], true)){
            show_error('Unable to save Type Of COA');
        }
        
        // Handle missing session data with dev bypass
        if (isset($this->session->userdata['logged_in']['id'])) {
            $user_id = (int)$this->session->userdata['logged_in']['id'];
        } else {
            // Use default user ID for development
            $user_id = 1;
        }
        
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI
            ->select('user_saved.ID')
            ->select('user_saved.SAVED_COA_ID')
            ->select('user_saved.CRITERIA_NAME_ID')
            ->select('CASE WHEN `STATE` IS NOT NULL THEN CONCAT(user_saved.COA_TITLE, "*") ELSE user_saved.COA_TITLE END AS COA_TITLE')
            ->select('user_saved.COA_DESCRIPTION')
            ->select('user_saved.SAVED_COA_ID')
            ->select('user_saved.CREATED_DATETIME')
            ->select('user_shared.ORIGINAL_USER_ID')
            ->select('user_shared.IS_REVOKED')
            ->from('USR_LOOKUP_USER_SAVED_COA user_saved')
            ->join('USR_LOOKUP_USER_SHARED_COA user_shared', 'user_saved.ID = user_shared.NEW_COA_ID', 'left')
            ->join('USR_LOOKUP_SAVED_COA coa_saved', 'coa_saved.ID = user_saved.SAVED_COA_ID')
            ->where('coa_saved.type_of_coa', $type_of_coa)
            ->where('user_saved.USER_ID', $user_id)
            ->where('user_saved.CRITERIA_NAME_ID', $criteria_name_id)
            ->where('(user_shared.IS_REVOKED IS NULL OR user_shared.IS_REVOKED = 0)', null, false) // Ensures all user_saved records + only user_shared records that are not revoked are included.
            ->get()->result_array();
        
        return $result;
    }

    /**
     * Return coas shared by me
     * @return array
     */
    public function get_coa_shared_by_me($is_revoked = 0, $type_of_coa = 'ISS') {
        // Handle missing session data with dev bypass
        if (isset($this->session->userdata['logged_in']['id'])) {
            $user_id = (int)$this->session->userdata['logged_in']['id'];
        } else {
            // Use default user ID for development
            $user_id = 1;
        }
        
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI
            ->select('user_shared.ID AS SHARED_COA_ID')
            ->select('user_shared.ORIGINAL_COA_ID')
            ->select('user_shared.NEW_USER_ID')
            ->select('CASE WHEN user_saved.STATE IS NOT NULL THEN CONCAT(user_saved.COA_TITLE, "*") ELSE user_saved.COA_TITLE END AS COA_TITLE')
            ->select('user_saved.COA_DESCRIPTION')
            ->select('user_saved.SAVED_COA_ID')
            ->select('user_saved.CREATED_DATETIME')
            ->select('user_saved.COA_TYPE')
            ->select('user_shared.CREATED_DATETIME as SHARED_DATETIME')
            ->select('saved_coa.TYPE_OF_COA')
            ->from('USR_LOOKUP_USER_SHARED_COA user_shared')
            ->join('USR_LOOKUP_USER_SAVED_COA user_saved', 'user_saved.ID = user_shared.ORIGINAL_COA_ID AND user_saved.USER_ID = user_shared.ORIGINAL_USER_ID')
            ->join('USR_LOOKUP_SAVED_COA saved_coa', 'user_saved.SAVED_COA_ID = saved_coa.ID')
            ->where('user_shared.ORIGINAL_USER_ID', $user_id)
            ->where('user_shared.IS_REVOKED', $is_revoked)
            ->where('user_saved.CRITERIA_NAME_ID', $criteria_name_id)
            ->where('saved_coa.TYPE_OF_COA', $type_of_coa)
            ->get()->result_array();

        return $result;
    }

    /**
     * Returns coas shared to me
     * @return array
     */
    public function get_coa_shared_to_me($is_revoked = 0, $type_of_coa = 'ISS') {
        // Handle missing session data with dev bypass
        if (isset($this->session->userdata['logged_in']['id'])) {
            $user_id = (int)$this->session->userdata['logged_in']['id'];
        } else {
            // Use default user ID for development
            $user_id = 1;
        }
        
        $criteria_name_id = get_criteria_name_id();

        $result = $this->DBs->SOCOM_UI
            ->select('user_shared.ID as SHARED_COA_ID')
            ->select('CASE WHEN user_saved.STATE IS NOT NULL THEN CONCAT(user_saved.COA_TITLE, "*") ELSE user_saved.COA_TITLE END AS COA_TITLE')
            ->select('user_saved.COA_DESCRIPTION')
            ->select('user_saved.SAVED_COA_ID')
            ->select('user_saved.CREATED_DATETIME')
            ->select('user_shared.CREATED_DATETIME as SHARED_DATETIME')
            ->select('user_shared.NEW_COA_ID')
            ->select('user_shared.ORIGINAL_USER_ID')
            ->select('user_saved.COA_TYPE')
            ->select('saved_coa.TYPE_OF_COA')
            ->from('USR_LOOKUP_USER_SAVED_COA user_saved')
            ->join('USR_LOOKUP_USER_SHARED_COA user_shared', 'user_saved.ID = user_shared.NEW_COA_ID AND user_saved.USER_ID = user_shared.NEW_USER_ID')
            ->join('USR_LOOKUP_SAVED_COA saved_coa', 'user_saved.SAVED_COA_ID = saved_coa.ID')
            ->where('user_saved.USER_ID', $user_id)
            ->where('user_saved.CRITERIA_NAME_ID', $criteria_name_id)
            ->where('user_shared.IS_REVOKED', $is_revoked)
            ->where('saved_coa.TYPE_OF_COA', $type_of_coa)
            ->get()->result_array();

        return $result;
    }

    /**
     * Returns program_group
     * @param int[] $selected_email_ids Array of selected email IDs.
     * @param array $selected_coas Array of COAs, where each COA is an associative array containing COA_TITLE, COA_DESCRIPTION, and SAVED_COA_ID.
     * @return boolean
     */
    public function share_coa($selected_email_ids, $selected_coas) {
        $original_user_id = (int)$this->session->userdata['logged_in']['id'];
        $criteria_name_id = get_criteria_name_id();
        foreach ($selected_email_ids as $email_id) {
            foreach ($selected_coas as $coa) {
                // Copy row from USR_LOOKUP_SAVED_COA with new ID and USER_ID
                $row_copy = $this->DBs->SOCOM_UI
                    ->select('*')
                    ->from('USR_LOOKUP_SAVED_COA')
                    ->where('ID', $coa['SAVED_COA_ID'])
                    ->get()->row_array() ?? false;

                if ($row_copy) {
                    unset($row_copy['ID']);
                    unset($row_copy['CREATED_DATETIME']);
                    $row_copy['USER_ID'] = $email_id;
                    $row_copy['CREATED_DATETIME'] = date('Y-m-d H:i:s');
                
                    $result_1 = $this->DBs->SOCOM_UI
                        ->insert('USR_LOOKUP_SAVED_COA', $row_copy);
                    
                    if (!$result_1) {
                        return false;
                    } else {
                        // Get the ID of the newly inserted SAVED_COA_ID
                        $new_saved_coa_id = $this->DBs->SOCOM_UI->insert_id();
                    }
                } else {
                    return false;
                }

                $row_copy_2 = $this->DBs->SOCOM_UI
                    ->select('*')
                    ->from('USR_LOOKUP_USER_SAVED_COA')
                    ->where('SAVED_COA_ID', $coa['SAVED_COA_ID'])
                    ->where('USER_ID', $original_user_id)
                    ->where('CRITERIA_NAME_ID', $criteria_name_id)
                    ->get()
                    ->row_array();
            
                if ($row_copy_2) {
                    $state = $row_copy_2['STATE'];
                } else {
                    $state = false;
                }

                // Copy row from USR_LOOKUP_USER_SAVED_COA with new USER_ID and SAVED_COA_ID
                $result_2 = $this->DBs->SOCOM_UI
                    ->set('CRITERIA_NAME_ID', $coa['CRITERIA_NAME_ID'])
                    ->set('COA_TITLE', $coa['COA_TITLE'])
                    ->set('COA_DESCRIPTION', $coa['COA_DESCRIPTION'])
                    ->set('CREATED_DATETIME', 'NOW()', false)
                    ->set('USER_ID', $email_id)
                    ->set('SAVED_COA_ID', $new_saved_coa_id)
                    ->set('STATE', $state)
                    ->insert('USR_LOOKUP_USER_SAVED_COA');
                
                if (!$result_2) {
                    return false;
                }

                // Get the ID of the newly inserted COA
                $new_coa_id = $this->DBs->SOCOM_UI->insert_id();

                // Insert into USR_LOOKUP_USER_SHARED_COA
                $shared_result = $this->DBs->SOCOM_UI
                    ->set('ORIGINAL_USER_ID', $original_user_id)
                    ->set('NEW_USER_ID', $email_id)
                    ->set('ORIGINAL_COA_ID', $coa['ID']) // Using ID from USR_LOOKUP_USER_SAVED_COA instead of SAVED_COA_ID
                    ->set('NEW_COA_ID', $new_coa_id)
                    ->set('CRITERIA_NAME_ID', $coa['CRITERIA_NAME_ID'])
                    ->insert('USR_LOOKUP_USER_SHARED_COA');
                
                if (!$shared_result) {
                    return false;
                }
            }
        }

        return true;
    }
    

    /**
     * Update IS_REVOKED status for given ID in USR_LOOKUP_USER_SHARED_COA to 1
     * @param int $shared_coa_id
     * @return int|boolean
     */
    function revoke_coa($shared_coa_id) {
        $criteria_name_id = get_criteria_name_id();
        $this->DBs->SOCOM_UI
            ->set('IS_REVOKED', 1)
            ->set('CRITERIA_NAME_ID', $criteria_name_id)
            ->where('ID', $shared_coa_id);

        return $this->DBs->SOCOM_UI->update('USR_LOOKUP_USER_SHARED_COA') ? true : false;
    }


    /**
     * Returns program_group
     * @param int[] $selected_email_ids Array of selected email IDs.
     * @param array $selected_coas Array of COAs, where each COA is an associative array containing COA_TITLE, COA_DESCRIPTION, and SAVED_COA_ID.
     * @return boolean
     */
    function merge_coa($merge_coa, $type_of_coa, $storm_flag) {
        $user_id = (int)$this->session->userdata['logged_in']['id'];
        $criteria_name_id = get_criteria_name_id();
        $cycle_id = get_cycle_id();

        // Copy row from USR_LOOKUP_SAVED_COA with new USER_ID and SAVED_COA_ID
        $saved_coa = $merge_coa['USR_LOOKUP_SAVED_COA'];

        [$pom_year, $year_list] = get_years_coa($type_of_coa === 'ISS_EXTRACT');

        $pom = $this->SOCOM_Dynamic_Year_model->getCurrentPomFull();
        
        $insert_saved_coa = $this->DBs->SOCOM_UI
        ->set('CRITERIA_NAME_ID', $criteria_name_id)
        ->set('OVERRIDE_TABLE_SESSION',  $saved_coa['OVERRIDE_TABLE_SESSION'])
        ->set('OVERRIDE_TABLE_METADATA', $saved_coa['OVERRIDE_TABLE_METADATA'])
        ->set('OVERRIDE_FORM_SESSION', $saved_coa['OVERRIDE_FORM_SESSION'])
        ->set('USER_ID',  $user_id)
        ->set('CYCLE_ID', $cycle_id)
        ->set('TYPE_OF_COA',  $type_of_coa)
        ->set('APP_VERSION', APP_TAG)
        ->set('POM_YEAR', $pom_year)
        ->set('YEAR_LIST', json_encode($year_list))
        ->set('POM_ID', $pom['ID'])
        ->set('STORM_FLAG', $storm_flag)
        ->set('POSITION', $pom['LATEST_POSITION'])
        ->set('CREATED_DATETIME', 'NOW()', false)
        ->set('IS_DELETED', 0)
        ->insert('USR_LOOKUP_SAVED_COA');

        if ($insert_saved_coa) {
            // Get the ID of the newly inserted SAVED_COA_ID
            $new_saved_coa_id = $this->DBs->SOCOM_UI->insert_id();
            $user_saved_coa = $merge_coa['USR_LOOKUP_USER_SAVED_COA'];

            // Copy row from USR_LOOKUP_USER_SAVED_COA with new USER_ID and SAVED_COA_ID
            $user_saved_coa_id = $this->DBs->SOCOM_UI
            ->set('CRITERIA_NAME_ID', $criteria_name_id)
            ->set('COA_TITLE', $user_saved_coa['COA_TITLE'])
            ->set('COA_DESCRIPTION', $user_saved_coa['COA_DESCRIPTION'])
            ->set('COA_TYPE', $user_saved_coa['COA_TYPE'])
            ->set('USER_ID',  $user_id)
            ->set('SAVED_COA_ID', $new_saved_coa_id)
            ->set('STATE', $user_saved_coa['STATE'])
            ->set('CREATED_DATETIME', 'NOW()', false)
            ->insert('USR_LOOKUP_USER_SAVED_COA');

            if ($user_saved_coa_id) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Returns saved coa weighted score
     * @param int $saved_coa_id
     * @return array
     */
    public function get_manual_override_weighted_score(
        $saved_coa_id
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];
        
        $result = $this->DBs->SOCOM_UI->select('OVERRIDE_TABLE_SESSION, TYPE_OF_COA')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->result_array();
        $weighted_score = [];
        if ($result) {
            $session = json_decode($result[0]['OVERRIDE_TABLE_SESSION'], true);
            $type_of_coa = $result[0]['TYPE_OF_COA'];
            foreach($session['coa_output'] as $data) {
                if (!str_contains($data['RESOURCE CATEGORY'],'Grand Total')) {
                    $progra_id = covertToProgramId($type_of_coa ,[
                        'program_code' => $data['Program'] ?? '',
                        'cap_sponsor' => $data['CAP SPONSOR'] ?? '',
                        'pom_sponsor' =>$data['POM SPONSOR'] ?? '',
                        'ass_area_code' => $data['ASSESSMENT AREA'] ?? '',
                        'execution_manager' => $data['EXECUTION MANAGER'] ?? '',
                        'resource_category' => $data['RESOURCE CATEGORY'] ?? '',
                        'eoc_code' =>$data['EOC'] ?? '',
                        'osd_pe_code' => $data['OSD PE'] ?? '',
                        'event_name' => $data['EVENT NAME'] ?? '',
                    ]);
                    $weighted_score[$progra_id] = [
                        'total_storm_scores' => $data['StoRM Score'],
                        'weighted_pom_score' => $data['POM Score'],
                        'weighted_guidance_score' => $data['Guidance Score']
                    ];
                }
            }
            unset($session, $result);
        }
        return $weighted_score;
    }

    /**
     * Returns saved coa weighted option
     * @param int $saved_coa_id
     * @return array
     */
    public function get_manual_override_weighted_score_option(
        $saved_coa_id
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];
         
        $result = $this->DBs->SOCOM_UI->select('COA_TYPE')
                                ->from('USR_LOOKUP_USER_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('SAVED_COA_ID', $saved_coa_id)
                                ->get()
                                ->row_array();
        
        return $result['COA_TYPE'];
    }

    /**
     * Returns saved coa weighted optioncccc      ccv
     * @param int $saved_coa_id
     * @return array
     */
    public function get_type_of_coa(
        $saved_coa_id
    ) {
        $user_id = $this->session->userdata('logged_in')['id'];
        
        $result = $this->DBs->SOCOM_UI->select('TYPE_OF_COA')
                                ->from('USR_LOOKUP_SAVED_COA')
                                ->where('USER_ID', $user_id)
                                ->where('ID', $saved_coa_id)
                                ->get()
                                ->row_array();
        
        return $result['TYPE_OF_COA'];
    }

    public function get_issue_analysis_data($ids) {
        return json_decode(php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($ids),
            RHOMBUS_PYTHON_URL.'/socom/iss/event_summary/detail_summary'
        ), true);
    }

    public function get_event_summary_data($events, $page) {

        $event_string = [];
        foreach ($events as $event) {
            $event_string[] = 'event_names=' . $event;
        }
        if ($page === 'issue') {
            $api = 'iss';
        } else if ($page === 'zbt_summary') {
            $api = 'zbt';
        }
        $api_endpoint = sprintf('%s/socom/%s/event_summary/?%s', RHOMBUS_PYTHON_URL, $api, implode('&', $event_string));

        return json_decode(php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            $api_endpoint
        ), true);
    }


    public function get_to_cut(int $percentage) {
        if ($percentage > 100 || $percentage < 0) {
            log_message('error', 'Percentage must be between 0 and 100');
            return false;
        }
        
        $issue_table = $this->dynamic_year->getTable(
            'RESOURCE_CONSTRAINED_COA',
            true,
            'ISS'
        );

        $result = $this->DBs->SOCOM_UI
            ->select('FISCAL_YEAR')
            ->select(sprintf('(ROUND(SUM(RESOURCE_K)*%s)) as RESOURCE_K_CUT', $percentage/100), false)
            ->from($issue_table)
            ->group_by('FISCAL_YEAR')
            ->get()
            ->result_array();

        return $result;
    }

    public function get_optimized_data(int $id) {
        return $this->DBs->SOCOM_UI
            ->select('CALC_BUDGET_VALUES, OVERRIDE_TABLE_SESSION, YEAR_LIST')
            ->from('USR_LOOKUP_SAVED_COA')
            ->where('ID', $id)
            ->get()
            ->row_array() ?? [];
    }

    public function get_eoc_funding($coa_id) {
        return json_decode(php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL.'/optimizer/detail_summary/eoc_fund?coa_id='.$coa_id
        ), true);
    }
}