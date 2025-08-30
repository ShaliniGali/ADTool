<?php

#[AllowDynamicProperties]
class  SOCOM_Assessment_Area_model extends CI_Model {
    function get_assessment_area() {
        return $this->DBs->SOCOM_UI
            ->select('ASSESSMENT_AREA_CODE')
            ->select('ASSESSMENT_AREA')
            ->from('LOOKUP_ASSESSMENT_AREA')
            ->get()
            ->result_array();
    }
}
