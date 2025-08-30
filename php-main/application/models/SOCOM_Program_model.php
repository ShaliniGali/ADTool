<?php

#[AllowDynamicProperties]
class  SOCOM_Program_model extends CI_Model {
    function get_program_id($program_name, $type='ID') {
        return $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('PROGRAM_CODE')
            ->from('LOOKUP_PROGRAM')
            ->where('PROGRAM_NAME', $program_name)
            ->get()
            ->row_array()[$type] ?? false;

    }

    function get_program_codes(array $program_names, $type='ID') {
        return $this->DBs->SOCOM_UI
            ->select('PROGRAM_CODE')
            ->from('LOOKUP_PROGRAM')
            ->where_in('PROGRAM_NAME', $program_names)
            ->get()
            ->result_array() ?? false;

    }

    function get_program_by_group($ass_area) {
        return $this->DBs->SOCOM_UI
            ->select('DISTINCT (PROGRAM_GROUP)')
            ->from('LOOKUP_PROGRAM')
            ->where_in('ASSESSMENT_AREA_CODE',  $ass_area)
            ->get()
            ->result_array() ?? false;

    }

    function get_program_id_by_filter($field, $filter, $use_event_name=false) {
        
        if ($use_event_name) {
            $event_name_cond = "EVENT_NAME is not null";
        }
        else {
            $event_name_cond = "EVENT_NAME is null";
        }
        
        $result = $this->DBs->SOCOM_UI
            ->select('ID')
            ->from('LOOKUP_PROGRAM')
            ->where_in($field, $filter)
            ->where($event_name_cond)
            ->get()
            ->result_array() ?? [];

        return array_column($result, 'ID');        
    }

    function get_program_by_group_all() {
        return $this->DBs->SOCOM_UI
            ->select('DISTINCT PROGRAM_GROUP', false)
            ->from('LOOKUP_PROGRAM')
            ->get()
            ->result_array() ?? false;
    }

    function get_resource_category_code_by_program_group(array $program_groups) {
        // $program_groups = filter_var($program_groups, FILTER_UNSAFE_RAW, [
        //     'flags' => FILTER_FLAG_STRIP_LOW| FILTER_FLAG_STRIP_HIGH
        // ]);

        if (is_null($program_groups)) {
            log_message('error', 'Empty program_groups');
            return false;
        }

        if (in_array('Select All', $program_groups)) {
            return $this->DBs->SOCOM_UI
            ->select('DISTINCT RESOURCE_CATEGORY_CODE', false)
            ->from('LOOKUP_PROGRAM')
            ->get()
            ->result_array() ?? false;
        }

        return $this->DBs->SOCOM_UI
            ->select('DISTINCT RESOURCE_CATEGORY_CODE', false)
            ->from('LOOKUP_PROGRAM')
            ->where_in('PROGRAM_GROUP', $program_groups)
            ->get()
            ->result_array() ?? false;
    }

    function get_cap_sonsor_code_by_resource_category_code_program_group(array $resource_category_code, array $program_groups) {
        // $program_groups = filter_var($program_groups, FILTER_UNSAFE_RAW, [
        //     'flags' => FILTER_FLAG_STRIP_LOW| FILTER_FLAG_STRIP_HIGH
        // ]);

        if (is_null($program_groups)) {
            log_message('error', 'Empty program_groups');
            return false;
        }

        // $resource_category_code = filter_var($resource_category_code, FILTER_UNSAFE_RAW, [
        //     'flags' => FILTER_FLAG_STRIP_LOW| FILTER_FLAG_STRIP_HIGH
        // ]);

        if (is_null($resource_category_code)) {
            log_message('error', 'Empty resource_category_code');
            return false;
        }

        if (in_array('Select All', $program_groups) && in_array('Select All', $resource_category_code)) {
            return $this->DBs->SOCOM_UI
                ->select('DISTINCT CAPABILITY_SPONSOR_CODE', false)
                ->from('LOOKUP_PROGRAM')
                ->get()
                ->result_array() ?? false;
        }

        elseif (in_array('Select All', $program_groups)) {
            return $this->DBs->SOCOM_UI
                ->select('DISTINCT CAPABILITY_SPONSOR_CODE', false)
                ->from('LOOKUP_PROGRAM')
                ->where_in('RESOURCE_CATEGORY_CODE', $resource_category_code)
                ->get()
                ->result_array() ?? false;
        }

        elseif (in_array('Select All', $resource_category_code)) {
            return $this->DBs->SOCOM_UI
                ->select('DISTINCT CAPABILITY_SPONSOR_CODE', false)
                ->from('LOOKUP_PROGRAM')
                ->where_in('PROGRAM_GROUP', $program_groups)
                ->get()
                ->result_array() ?? false;
        }
        return $this->DBs->SOCOM_UI
            ->select('DISTINCT CAPABILITY_SPONSOR_CODE', false)
            ->from('LOOKUP_PROGRAM')
            ->where_in('RESOURCE_CATEGORY_CODE', $resource_category_code)
            ->where_in('PROGRAM_GROUP', $program_groups)
            ->get()
            ->result_array() ?? false;
    }

    function get_program_data($program_ids, $fields) {
        $result = [];

        if (!empty($fields)) {
            foreach($fields as $field) {
                $this->DBs->SOCOM_UI->select($field);
            }

            $result = $this->DBs->SOCOM_UI->from('LOOKUP_PROGRAM')
                ->where_in('ID', $program_ids)
                ->get()
                ->result_array();
        }
        return $result;
    }
}
