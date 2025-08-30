<?php

#[AllowDynamicProperties]
class  SOCOM_Storm_model extends CI_Model {
    public function get_storm() {
        return $this->DBs->SOCOM_UI
            ->select('LS.ID storm_id')
            ->select('LS.TOTAL_SCORE storm')
            ->from('LOOKUP_STORM LS')
            ->join('LOOKUP_PROGRAM LP', 'ON LS.ID = LP.STORM_ID')
            ->get()->result_array();
    }

    public function get_program_ids(array $programs, string $lookup_table='LOOKUP_PROGRAM') {
        if (empty($programs)) {
            return [];
        }
        
        return array_column($this->DBs->SOCOM_UI
            ->select('LP.ID as ID')
            ->from('LOOKUP_STORM LS')
            ->join("{$lookup_table} LP", 'ON LS.ID = LP.STORM_ID')
            ->where_in('LP.ID', $programs)
            ->get()->result_array(), 'ID');
    }
}

