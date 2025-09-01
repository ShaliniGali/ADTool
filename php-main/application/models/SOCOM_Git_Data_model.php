<?php

class SOCOM_Git_Data_model extends CI_Model {

    public function git_track_data(GitDataType $type, int|null $map_id = null, int|null $user_id = null, $transaction = false){
        if ($transaction === true) {
            $this->DBs->SOCOM_UI->trans_start();
        }

        $pom_admin = $this->rbac_users->is_admin();
        if ($pom_admin === true) {
            $is_admin = 1;
        } else {
            $is_admin = 0;
        }
        $this->DBs->SOCOM_UI
                ->set('IS_ADMIN', $is_admin);

        if(isset($user_id)){
            $this->DBs->SOCOM_UI
                ->set('USER_ID', $user_id);
        }

        $this->DBs->SOCOM_UI
            ->set('TYPE', $type->name)
            ->insert('USR_DT_GIT_DATA');
        
        $id = $this->DBs->SOCOM_UI->insert_id();
        
        if (is_int($id) && $map_id !== null) {
            
            $this->DBs->SOCOM_UI
                ->set('TYPE', $type->name)
                ->set('USR_DT_GIT_DATA_ID', $id)
                ->set('MAP_ID', $map_id)
                ->insert('USR_DT_GIT_MAP');
        }

        if ($transaction === true) {
            $this->DBs->SOCOM_UI->trans_complete();
            unset($tagData);

            if (!$this->DBs->SOCOM_UI->trans_status()) {
                throw new ErrorException('Saving to the database was not successful');
            }
        }

        return $id;
    }
}

enum GitDataType: string {
	case UPLOAD_FILE = 'UPLOAD_FILE';
	case PROCESS_FILE = 'PROCESS_FILE';
	case CANCEL_FILE = 'CANCEL_FILE';
	case DELETE_FILE = 'DELETE_FILE';
    case CREATE_METADATA = 'CREATE_METADATA';
    case CREATE_DATABASE = 'CREATE_DATABASE';
    case USER_DATA_OPEN = 'USER_DATA_OPEN';
    case USER_DATA_CLOSE = 'USER_DATA_CLOSE';
    case USER_DATA_SEARCH = 'USER_DATA_SEARCH';
    case USER_DATA_EDIT = 'USER_DATA_EDIT';
    case USER_DATA_HISTORY = 'USER_DATA_HISTORY';
    case USER_DATA_SAVE_START = 'USER_DATA_SAVE_START';
    case USER_DATA_SAVE_END = 'USER_DATA_SAVE_END';
    case USER_DATA_CANCEL = 'USER_DATA_CANCEL';
    case USER_DATA_FINAL_SUBMISSION = 'USER_DATA_FINAL_SUBMISSION';
    case ADMIN_APPROVAL = 'ADMIN_APPROVAL';


}


