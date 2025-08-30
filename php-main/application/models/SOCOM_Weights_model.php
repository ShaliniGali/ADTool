<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class  SOCOM_Weights_model extends CI_Model
{
    
    // --------------------------------------------------------------------
        
    /**
     * Create Criteria Weights
     * 
     * 
     * @param	string	$title
     * @param	Array	$session
     * @param	Array	$description
     * @param   Array   $criterias
     * @return	mixed   
     */
    public function create_weights($title, $session, $description, $criterias)
    {
        $time = date("Y-m-d H:i:s");
        
        $user = (int)$this->session->userdata['logged_in']['id'];
        
        if (strlen(trim($title)) == 0) {
            $log = 'Title must be input';

            log_message('error', $log);
            
            throw new InvalidArgumentException($log);
        }

        if (
            !isset($description['guidance'], $description['pom']) ||
            (!is_string($description['guidance']) || !is_string($description['pom']))
        ) {
            $log = 'Both guidance and pom description must exist even in emtpy';

            log_message('error', $log);
            
            throw new InvalidArgumentException($log);
        }

        $description['guidance'] = trim($description['guidance']);
        $description['pom'] = trim($description['pom']);

        if (
            !isset($session['guidance'], $session['pom']) ||
            (empty($session['guidance']) || empty($session['pom']))
        ) {
            $log = 'Both guidance and pom SESSION must exist with all criteria';
            log_message('error', $log);

            throw new InvalidArgumentException($log);
        }

        foreach ($criterias as $criteria) {
            if (
                !isset($session['guidance'][$criteria], $session['pom'][$criteria])
            ) {
                $log = sprintf('%s criteria must be set for weight', $criteria);
                log_message('error', $log);

                throw new InvalidArgumentException($log);
            }

            if (
                !is_float($session['guidance'][$criteria]) ||
                !is_float($session['pom'][$criteria])
            ) {
                $session['guidance'][$criteria] = (float)$session['guidance'][$criteria];
                $session['pom'][$criteria] = (float)$session['pom'][$criteria];
            }
        }

        $criteria_name_id = get_criteria_name_id();

        $criteria_info = array(
            'TITLE'              => trim($title),
            'DESCRIPTION'       => json_encode($description),
            'SESSION'           => json_encode($session),
            'USER_ID'           => $user,
            'TIMESTAMP'         => $time,
            'CRITERIA_NAME_ID' => $criteria_name_id
        );


        return $this->DBs->SOCOM_UI->insert('USR_LOOKUP_CRITERIA_WEIGHTS', $criteria_info);

    }

    // --------------------------------------------------------------------
    
    /**
     * Get Criteria Weights
     * 
     * 
     * @param	int	$user_id
     * 
     * @return	array   array of CRITERIA_WEIGHTS for the user
     */
    public function get_user_weights()
    {
        $criteria_name_id = get_criteria_name_id();

        $user_id = (int)$this->session->userdata['logged_in']['id'];

        return $this->DBs->SOCOM_UI
            ->select('*')
            ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
            ->where('DELETED', 0)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->get()
            ->result_array();
    }

    // --------------------------------------------------------------------
        
    /**
     * Count Weights
     * 
     * 
     * @param	int	$user_id
     * @param   string $title
     * 
     * @return	array   array of CRITERIA_WEIGHTS for the user
     */
    public function count_weights($title = null)
    {
        $criteria_name_id = get_criteria_name_id();

        $user_id = $this->session->userdata['logged_in']['id'];

        $this->DBs->SOCOM_UI
            ->select('COUNT(*) as count')
            ->from('USR_LOOKUP_CRITERIA_WEIGHTS')
            ->where('USER_ID', (int)$user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->where('DELETED', 0);

        if ($title !== null) {
            $this->DBs->SOCOM_UI
                ->where('TITLE', $title);
        }
            
            
        return $this->DBs->SOCOM_UI->get()
            ->row_array()['count'] ?? 0;
    }

    /**
     * Deletes user weight
     * @return bool true if the weight was deleted
     * @throws ErrorException
     */
    public function delete_user_weight($weight_id) {
        $criteria_name_id = get_criteria_name_id();
        $user = $this->session->userdata['logged_in']['id'];
        
        if (ctype_digit($weight_id) === false){
            return false;
        }
        
        return $this->DBs->SOCOM_UI
            ->set('DELETED', 1)
            ->where('USER_ID', (int)$user)
            ->where('WEIGHT_ID', (int)$weight_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->update('USR_LOOKUP_CRITERIA_WEIGHTS');
    } 
    public function get_user_score_id_lists($programs, $type_of_coa) {
        $criteria_name_id = get_criteria_name_id();

        if (empty($programs)) {
            return [];
        }

        $user_id = (int)$this->session->userdata("logged_in")["id"];
        
        return $this->DBs->SOCOM_UI
            ->select('PROGRAM_ID, USER_ID')
            ->from('USR_OPTION_SCORES')
            ->where('DELETED', 0)
            ->where_in('PROGRAM_ID', $programs)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->where('TYPE_OF_COA', $type_of_coa)
            ->get()->result_array();
    }
}
