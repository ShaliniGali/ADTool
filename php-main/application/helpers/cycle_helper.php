<?php

if (!function_exists('get_criteria_name_id')) {
    function get_criteria_name_id($error = true) {
        static $criteria_name_id = null;

        if ($criteria_name_id === null) {
            $CI = get_instance();

            $criteria_name_id = $CI->SOCOM_Cycle_Management_model->get_active_cycle_id()['CRITERIA_NAME_ID'] ?? false;
            if (!is_int($criteria_name_id)) {
                log_message('error', 'Unable to get current active Criteria');
                if ($error === true) {
                    show_error('Unable to get current active Criteria', 500, 'Cycle and Criteria need to be created');
                }
            }
        }
        
        return $criteria_name_id;
    }
}

if (!function_exists('get_cycle_id')) {
    function get_cycle_id() {
        static $cycle_id = null;

        if ($cycle_id === null) {
            $CI = get_instance();

            $cycle_id = $CI->SOCOM_Cycle_Management_model->get_active_cycle_id()['CYCLE_ID'] ?? false;
            if (!is_int($cycle_id)) {
                log_message('error', 'Unable to get current active Cycle');

                show_error('Unable to get current active Cycle', 500, 'Cycle and Criteria need to be created');
            }
        }
        
        return $cycle_id;
    }
}

if (!function_exists('get_pom_id')) {
    function get_pom_id() {
        static $pom_id = null;

        if ($pom_id === null) {
            $CI = get_instance();

            $pom_id = $CI->SOCOM_Cycle_Management_model->get_active_cycle_id()['POM_ID'] ?? false;
            if (!is_int($pom_id)) {
                log_message('error', 'Unable to get current active POM ID');

                show_error('Unable to get current active POM ID', 500, 'Cycle, Criteria and POM Year/Position need to be created');
            }
        }
        
        return $pom_id;
    }
}

if (!function_exists('covert_score_data_keys')) {
    function covert_score_data_keys(&$data) {
        foreach($data as $key => $score) {
            if (strpos($key, '-') !== false) {
                $data[str_replace('-', ' ', $key)] = $score;
                unset($data[$key]);
            }
        }

        ksort($data);
    }
}