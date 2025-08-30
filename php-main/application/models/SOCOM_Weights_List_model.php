<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class  SOCOM_Weights_List_model extends CI_Model
{

    // --------------------------------------------------------------------
    
    /**
     * Get Criteria Weights
     * 
     * @return	array   array of CRITERIA_WEIGHTS for the user
     */
    public function get_data($weight_id=null)
    {
        $criteria_name_id = get_criteria_name_id();

        $user_id = (int)$this->session->userdata('logged_in')['id'];
        
        $this->DBs->SOCOM_UI
            ->select('WEIGHT_ID,TITLE,DESCRIPTION,TIMESTAMP')
            ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
            ->where('DELETED !=', 1)
            ->where('USER_ID', (int)$user_id)
            ->order_by('TIMESTAMP DESC');

        if (ctype_digit($weight_id) == true) {
            $this->DBs->SOCOM_UI->where('WEIGHT_ID', (int)$weight_id);
        }

        $results = $this->DBs->SOCOM_UI
                    ->where('CRITERIA_NAME_ID', $criteria_name_id)
                    ->get()
                    ->result_array();
            
        return ['data' => $results];
    }

    public function get_weight_dropdown_selects() {
        $criteria_name_id = get_criteria_name_id();

        $user_id = (int)$this->session->userdata('logged_in')['id'];
        
        $results = $this->DBs->SOCOM_UI
            ->select('WEIGHT_ID, TITLE')
            ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
            ->where('DELETED !=', 1)
            ->where('USER_ID', (int)$user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->get()
            ->result_array();
        $html = '';
        foreach ($results as $weight) {
            $html .= '<option value="'. $weight['WEIGHT_ID'] . '">' . $weight['TITLE'] . '</option>';
        }
        return $html;
    }

    public function get_criteria_weights_table() {
        $criteria_name_id = get_criteria_name_id();

        $user_id = (int)$this->session->userdata('logged_in')['id'];

        $results = $this->DBs->SOCOM_UI
            ->select('WEIGHT_ID, TITLE as NAME, DESCRIPTION, TIMESTAMP')
            ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
            ->where('DELETED !=', 1)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->get()
            ->result_array();
        return $results;
    }

    // --------------------------------------------------------------------

    /**
     * Saves edited weight score data
     */
    public function save_weight_score_data($weight_id, $weight_data) {
        $criteria_name_id = get_criteria_name_id();

        $user_id = (int)$this->session->userdata['logged_in']['id'];
        
        $format_weight = function($weight_data) {
            $formatted_weight_data = [];
            foreach ($weight_data as $cwpair) {
                $formatted_weight_data[$cwpair['criteria']] = (string) $cwpair['weight'];
            }

            return $formatted_weight_data;
        };

        $formatted_weight_data['guidance'] = $format_weight($weight_data['guidance']);
        $formatted_weight_data['pom'] = $format_weight($weight_data['pom']);
        
        return $this->DBs->SOCOM_UI
            ->set('SESSION', json_encode($formatted_weight_data))
            ->set('TIMESTAMP', date("Y-m-d H:i:s"))
            ->where('WEIGHT_ID', (int) $weight_id)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->update('USR_LOOKUP_CRITERIA_WEIGHTS');
    }
    
    /**
     * Retrives default score values for a weight if the current user has yet to score on said weight.
     */
    public function get_saved_weight_data($weight_id)
    {
        $criteria_name_id = get_criteria_name_id();

        $user_id = (int)$this->session->userdata['logged_in']['id'];

        $weights = $this->DBs->SOCOM_UI
        ->select('SESSION')
        ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
        ->where('WEIGHT_ID', $weight_id)
        ->where('USER_ID', $user_id)
        ->where('CRITERIA_NAME_ID', $criteria_name_id)
        ->get()
        ->row_array()['SESSION'] ?? '[]';

        $weights = json_decode($weights, true);
        $sorted_weight = function($weights) {
            $default_score_data = [];

            ksort($weights);
            foreach ($weights as $criteria => $weight) {
                $default_score_data[] = array(
                    'criteria' => $criteria,
                    'weight' => floatval($weight)
                );
            }

            return $default_score_data;
        };

        $default_score_data['guidance'] = $sorted_weight($weights['guidance']);
        $default_score_data['pom'] = $sorted_weight($weights['pom']);

        return $default_score_data;
    }

    /* Note: num_user_has_scored is unused
    public function num_user_has_scored($user_id) {
        return $this->DB_UI
            ->select('COUNT(*) as count')
            ->from('USR_OPTION_SCORES')
            ->where('USER_ID', (int)$user_id)
            ->where('IS_ACTIVE', 1)
            ->where('DELETED', 0)
            ->get()->row_array()['count'] ?? 0;
    }
    */
}
