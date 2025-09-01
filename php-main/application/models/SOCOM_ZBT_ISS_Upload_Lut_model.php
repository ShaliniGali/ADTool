<?php

#[AllowDynamicProperties]
class SOCOM_ZBT_ISS_Upload_Lut_model extends CI_Model {

    public function get_execution_managers()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('EXECUTION_MANAGER_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('EXECUTION_MANAGER_CODE IS NOT NULL', null, false)
            ->order_by('EXECUTION_MANAGER_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_pom_sponsors()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('POM_SPONSOR_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('POM_SPONSOR_CODE IS NOT NULL', null, false)
            ->order_by('POM_SPONSOR_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_assessment_areas()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('ASSESSMENT_AREA_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('ASSESSMENT_AREA_CODE IS NOT NULL', null, false)
            ->order_by('ASSESSMENT_AREA_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_program_groups()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('PROGRAM_GROUP AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('PROGRAM_GROUP IS NOT NULL', null, false)
            ->order_by('PROGRAM_GROUP')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_program_codes()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('PROGRAM_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('PROGRAM_CODE IS NOT NULL', null, false)
            ->order_by('PROGRAM_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_eoc_codes()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('EOC_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('EOC_CODE IS NOT NULL', null, false)
            ->order_by('EOC_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_resource_category_codes()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('RESOURCE_CATEGORY_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('RESOURCE_CATEGORY_CODE IS NOT NULL', null, false)
            ->order_by('RESOURCE_CATEGORY_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }

    public function get_osd_pe_codes()
    {
        $result = $this->DBs->SOCOM_UI
            ->distinct()
            ->select('OSD_PROGRAM_ELEMENT_CODE AS value')
            ->from('LOOKUP_PROGRAM')
            ->where('OSD_PROGRAM_ELEMENT_CODE IS NOT NULL', null, false)
            ->order_by('OSD_PROGRAM_ELEMENT_CODE')
            ->get()
            ->result_array();
        return array_column($result, 'value');
    }
}