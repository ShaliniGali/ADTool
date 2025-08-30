<?php

#[AllowDynamicProperties]
class SOCOM_Portfolio_Viewer_model extends CI_Model
{
    public function get_budget_trend_data(array $filters, array $groupby, string $inflation_adj = 'false') {
        $api_params = array();
        $api_params['model'] = $filters;
        $api_params['groupby'] = $groupby;

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/pb-comparison/agg?inflation_adj=' . $inflation_adj
        );

        return json_decode($res, true);
    }

    public function get_final_enacted_budget_data(array $filters, array $groupby, string $inflation_adj = 'false') {
        $api_params = array();
        $api_params['model'] = $filters;
        $api_params['groupby'] = $groupby;

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/budget-execution/agg?inflation_adj=' . $inflation_adj
        );

        return json_decode($res, true);
    }

    public function get_capability_categories() {
        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL.'/socom/metadata/get-capability-categories'
        );

        return json_decode($res, true);
    }

    public function get_fem_agg_data($program_groups, $resource_categories) {

        $api_params = [];
        $api_params['program_groups'] = $program_groups;
        $api_params['resource_cat_codes'] = $resource_categories;
        
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/ams/fem/agg'
        );
        return json_decode($res, true);
    }

    public function get_metadata_descriptions($program_groups) {

        $api_url_list = [];
        foreach ($program_groups as $program_group) {
            $api_url_list[] = 'program_group=' . urlencode($program_group);
        }

        $api_url = implode('&', $api_url_list);
        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL.'/socom/ams/metadata/descriptions?' . $api_url
        );
        return json_decode($res, true);
    }

    public function get_dropdown_data($table, $column, $order_by, $filters = null, $match=null) {
        
        if ($column == 'PROGRAM_GROUP') {
            $this->DBs->SOCOM_UI->select('ELEMENT_OF_COST,APPN,PE,FY');
            $this->DBs->SOCOM_UI->distinct();
            $this->DBs->SOCOM_UI->from('DT_AMS_FEM');
            $sub_query = $this->DBs->SOCOM_UI->get_compiled_select();
        }
        
        $this->DBs->SOCOM_UI->select($column);
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from($table);
        if ($filters) {
            foreach ($filters as $key => $value) {
                $this->DBs->SOCOM_UI->where_in($key, $value);
            }
        }
        if ($match) {
            $this->DBs->SOCOM_UI->like('EXECUTION_MANAGER_CODE', $match, 'after');
        }

        if ($column == 'RESOURCE_CATEGORY_CODE') {
            $this->DBs->SOCOM_UI->like('RESOURCE_CATEGORY_CODE', '$', 'before');
        }

        if ($column == 'PROGRAM_GROUP') {
            $this->DBs->SOCOM_UI->where("(
                EOC_CODE, RESOURCE_CATEGORY_CODE, OSD_PROGRAM_ELEMENT_CODE, FISCAL_YEAR
            ) IN ( 
                {$sub_query}
            )");
        }
        
        $this->DBs->SOCOM_UI->order_by($order_by);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_funding_resource_category($table, $column, $order_by, $filters = null) {
        
        $this->DBs->SOCOM_UI->select('ELEMENT_OF_COST,APPN,PE,FY');
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from('DT_AMS_FEM');
        $sub_query = $this->DBs->SOCOM_UI->get_compiled_select();
        
        $this->DBs->SOCOM_UI->select($column);
        $this->DBs->SOCOM_UI->distinct();
        $this->DBs->SOCOM_UI->from($table);
        if ($filters) {
            foreach ($filters as $key => $value) {
                $this->DBs->SOCOM_UI->where_in($key, $value);
            }
        }

        $this->DBs->SOCOM_UI->where("(
            EOC_CODE, RESOURCE_CATEGORY_CODE, OSD_PROGRAM_ELEMENT_CODE, FISCAL_YEAR
        ) IN ( 
            {$sub_query}
        )");

        $this->DBs->SOCOM_UI->order_by($order_by);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_ams_pxid($program_groups) {
        $api_params = array();
        $api_params['program_groups'] = $program_groups;
        $api_params['program_codes'] = [];
        
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/ams/metadata/pxid'
        );
        return json_decode($res, true);
    }
    
    public function get_ams_dropdown_data($program_groups, $column) {
        $fielding_data = $this->get_fielding_data($program_groups);
        $unique_list = array_unique(array_column($fielding_data, $column));
        sort($unique_list);
        return $unique_list;
    }

    public function get_fielding_data($params) {
        $route_header = [];
        if (isset($params['PROGRAM_GROUP']) && $params['PROGRAM_GROUP'] != '') {
            $route_header['program_group'] = $params['PROGRAM_GROUP'];
        }
        if (isset($params['FISCAL_YEAR']) && $params['FISCAL_YEAR'] != '') {
            $route_header['fy'] = $params['FISCAL_YEAR'];
        }
        if (isset($params['FIELDING_ITEM']) && $params['FIELDING_ITEM'] != '') {
            $route_header['fielding_items'] = $params['FIELDING_ITEM'];
        }
        $params_query = http_build_query($route_header);

        $api_params = [];
        $api_params['components'] = $params['COMPONENT'] ?? [];
        $api_params['fielding_types'] = $params['FIEDLING_TYPES'] ?? [];

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode( $api_params),
            RHOMBUS_PYTHON_URL . '/socom/ams/fielding/agg?' . $params_query
        );
        return json_decode($res, true);
    }

    public function update_ams_budgets_table($program_groups) {
        $api_params = $program_groups;
        
        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL.'/socom/ams/budgets/prog/comparison'
        );
        return json_decode($res, true);
    }

    public function get_milestone_data($program_group, $programs=null) {
       
        $api_url_list = [];
        foreach ($programs as $program) {
            $api_url_list[] = 'program_fullnames=' . urlencode($program);
        }

        if (!empty($api_url_list)) {
            $api_url = '&' . implode('&', $api_url_list);
        }

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL.'/socom/ams/metadata/milestones?program_group=' . urlencode($program_group) . $api_url
        );
 
        return json_decode($res, true);
    }

    public function get_min_max_fy($table) {
        $this->DBs->SOCOM_UI->select('MIN(FISCAL_YEAR) as MIN_FY');
        $this->DBs->SOCOM_UI->select('MAX(FISCAL_YEAR) as MAX_FY');
        $this->DBs->SOCOM_UI->from($table);
        return $this->DBs->SOCOM_UI->get()->result_array();
    }

    public function get_requirements_data($params) {
        $route_header = [];
        if (isset($params['PXID']) && $params['PXID'] != '') {
            $route_header['pxid'] = $params['PXID'];
        }
        if (isset($params['MILESTONE']) && $params['MILESTONE'] != '') {
            $route_header['milestone'] = $params['MILESTONE'];
        }
        if (isset($params['MILESTONE_STATUS']) && $params['MILESTONE_STATUS'] != '') {
            $route_header['milestone_status'] = $params['MILESTONE_STATUS'];
        }
        $params_query = http_build_query($route_header);

        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            '',
            RHOMBUS_PYTHON_URL.'/socom/ams/milestones/requirements?' . $params_query
        );
 
        return json_decode($res, true);
    }
    
}