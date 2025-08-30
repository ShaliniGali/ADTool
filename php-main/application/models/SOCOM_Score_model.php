<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class  SOCOM_Score_model extends CI_Model
{

    public function _save_score_validate($params, $update) {
        if (
            !isset($params['user_id']) || 
            !is_int($params['user_id'])
        ) {
            log_message(
                'error', 
                sprintf(__METHOD__. ' needs user_id is not integer: %s', 
                    $params['user_id'] ?? 'not set')
            );
            return false;
        }
        if (
            $update === true &&
            (!isset($params['score_id'], $params['criteria_name_id']) ||
            (!is_int($params['score_id']) || !is_int($params['criteria_name_id'])))
        ) {
            log_message(
                'error',
                sprintf(
                    '%s needs score_id is not integer: %s or or criteria_name_id is not integer: %s',
                    __METHOD__, 
                    $params['score_id'] ?? 'not set', 
                    $params['criteria_name_id'] ?? 'not set'
                )
            );
            return false;
        }
        if (
            $update === false && 
            (!isset($params['program_id'], $params['criteria_name_id']) ||
            (!is_string($params['program_id']) || !is_int($params['criteria_name_id'])))
        ) {
            log_message(
                'error',
                sprintf(
                    '%s needs program_id is not string: %s or or criteria_name_id is not integer: %s',
                    __METHOD__, 
                    $params['program_id'] ?? 'not set',
                    $params['criteria_name_id'] ?? 'not set'
                )
            );
            return false;
        }
        if (!isset($params['score_name'])) {
            log_message(
                'error', 
                sprintf(__METHOD__. ' needs score_name is not set: %s', 
                    $params['score_name'] ?? 'not set')
            );
            return false;
        }
        if (
            !isset($params['score_name']) || 
            strlen($params['score_name']) > 100
        ) {
            log_message(
                'error', 
                sprintf(__METHOD__. ' needs score_name must be set and < 100 characters: %s', 
                    $params['score_name'] ?? 'not set')
            );
            return false;
        }
        if (
            !isset($params['score_description']) || 
            strlen($params['score_description']) > 1024
        ) {
            log_message(
                'error', 
                sprintf(__METHOD__. ' needs score_description must be set and < 1024 characters: %s', 
                    $params['score_description'] ?? 'not set')
            );
            return false;
        }
        if (
            !isset($params['type_of_coa']) || 
            !in_array($params['type_of_coa']->value, ['ISS_EXTRACT', 'RC_T', true])
        ) {
            log_message(
                'error', 
                sprintf(__METHOD__. ' needs type_of_coa must be set and one of ISS_EXTRACT or RC_T value: %s', 
                    $params['type_of_coa'] ?? 'not set')
            );
            return false;
        }
        return true;
    }

    public function save_score($params, $update = false) {
        if ($this->_save_score_validate($params, $update) === false) {
            return false;
        }

        $params['user_id'] = (int)$this->session->userdata['logged_in']['id'];   
            
        $result = false;
        if ($update === false) {
            $result = $this->DBs->SOCOM_UI
                ->set('NAME', $params['score_name'])
                ->set('SESSION', json_encode($params['score_data']))
                ->set('DESCRIPTION', $params['score_description'])
                ->set('PROGRAM_ID', $params['program_id'])
                ->set('TYPE_OF_COA', $params['type_of_coa']->value)
                ->set('CRITERIA_NAME_ID', $params['criteria_name_id'])
                ->set('CREATED_TIMESTAMP', 'NOW()', false)
                ->set('USER_ID', $params['user_id'])
                ->insert('USR_OPTION_SCORES');
        } else {
            $this->DBs->SOCOM_UI->trans_start();

            $score = $this->get_score(
                $params['score_id'],
                $params['program_id'],
                $params['criteria_name_id'],
                $params['user_id'],
                $params['type_of_coa'],
                true
            );
            $result = $this->save_user_history($score);

            $result = $this->DBs->SOCOM_UI
                ->set('NAME', $params['score_name'])
                ->set('SESSION', json_encode($params['score_data']))
                ->set('DESCRIPTION', $params['score_description'])
                ->where('ID', $params['score_id'])
                ->where('CRITERIA_NAME_ID', $params['criteria_name_id'])
                ->where('USER_ID', $params['user_id'])
                ->where('DELETED', 0)
                ->where('TYPE_OF_COA', $params['type_of_coa']->value)
                ->update('USR_OPTION_SCORES');

            $result = $this->DBs->SOCOM_UI->trans_complete();
        }

        return $result;
    }

    public function save_user_history(array $score) {
        if(empty($score)) {
            log_message('error', 'Unable to save score history to USR_OPTION_SCORES_HISTORY');
            return false;
        }

        return $this->DBs->SOCOM_UI
                ->set('SCORE_ID', $score['ID'])
                ->set('PROGRAM_ID', $score['PROGRAM_ID'])
                ->set('TYPE_OF_COA', $score['TYPE_OF_COA'])
                ->set('NAME', $score['NAME'])
                ->set('SESSION', json_encode($score['SESSION']))
                ->set('DESCRIPTION', $score['DESCRIPTION'])
                ->set('CRITERIA_NAME_ID', $score['CRITERIA_NAME_ID'])
                ->set('CREATED_TIMESTAMP', $score['CREATED_TIMESTAMP'])
                ->set('UPDATED_TIMESTAMP', $score['UPDATED_TIMESTAMP'])
                ->set('DELETED', $score['DELETED'])
                ->set('USER_ID', $score['USER_ID'])
                ->insert('USR_OPTION_SCORES_HISTORY');
    }

    public function get_score(int $score_id, string $program_id, int $criteria_name_id, int $user_id, TypeOfCoa $type_of_coa, bool $history = false) {
        if ($history === true) {
            $this->DBs->SOCOM_UI
                ->select('ID')
                ->select('PROGRAM_ID')
                ->select('TYPE_OF_COA')
                ->select('NAME')
                ->select('DESCRIPTION')
                ->select('SESSION')
                ->select('CREATED_TIMESTAMP')
                ->select('UPDATED_TIMESTAMP')
                ->select('CRITERIA_NAME_ID')
                ->select('DELETED')
                ->select('USER_ID');
        } else {
            $this->DBs->SOCOM_UI
                ->select('ID')
                ->select('NAME')
                ->select('DESCRIPTION')
                ->select('SESSION')
                ->select('UPDATED_TIMESTAMP')
                ->select('TYPE_OF_COA');
        }
        
        $result = $this->DBs->SOCOM_UI
            ->where('ID', $score_id)
            ->where('PROGRAM_ID', $program_id)
            ->where('TYPE_OF_COA', $type_of_coa->value)
            ->where('USER_ID', $user_id)
            ->where('CRITERIA_NAME_ID', $criteria_name_id)
            ->from('USR_OPTION_SCORES')
            ->get()
            ->row_array();                                                                                                     

        $result['SESSION'] = json_decode($result['SESSION'], true);
        
        if (!is_array($result['SESSION'])) {
            $result['SESSION'] = [];
        }

        ksort($result['SESSION']);

        return $result;
    }
}

enum TypeOfCoa: string {
    case ISS_EXTRACT = 'ISS_EXTRACT' ;
    case RC_T = 'RC_T';
}
