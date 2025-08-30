<?php

require_once(APPPATH.'models/SOCOM_User_Interface.php');

#[AllowDynamicProperties]
class SOCOM_User_Base extends CI_Model implements SOCOM_User_Interface {
    protected const TYPE = null;
    protected $table;
    protected $historyTable;
    protected $minGid = 1;
    protected $maxGid;
    protected $groups = [];

    public function __construct() {
        $this->maxGid = count($this->groups);
    }

    public function is_user_by_group(int|string $gid, ?int $id = null, array $deleted = [0]) {
        if (!is_int($gid) || ($gid < $this->minGid || $gid > $this->maxGid)) {
            $log = vsprintf('%s unable to %s, check $gid is within bounds and integer',[__CLASS__, __METHOD__]);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }

        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from($this->table)
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', $deleted)
            ->where('GROUP', $this->groups[$gid])
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
    }

    public function user_can_admin() {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $result = true;

        if (
            $this->is_user($user_id) === false
        ) {
            $log = sprintf('User is not a %s user', self::TYPE);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        return $result;
    }

    public function is_user(?int $id = null, array $deleted = [0]) {
        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }

        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from($this->table)
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', $deleted)
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
    }

    public function get_user() {
      /*  try {
            $this->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }*/

        $id = (int)$this->session->userdata("logged_in")["id"];
        $users =  $this->SOCOM_Users_model->get_users();

        $socom_users = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('GROUP')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('IS_DELETED')
            ->select('UPDATE_USER')
            ->from($this->table)
            ->where('USER_ID', $id)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->result_array();

        $users_full = [];
        foreach ($socom_users as $socom_user) {
            $users_full[$socom_user['USER_ID']] = ['EMAIL' => $users[$socom_user['USER_ID']]] + 
                ['UPDATE_EMAIL' => $users[$socom_user['UPDATE_USER']] ?? 'Pending' ] + 
                $socom_user;
        }
        unset($socom_users, $socom_user);
        
        return array_values($users_full);
    }

    public function get_users() {
        try {
            $this->SOCOM_Admin_User_model->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

        $users =  $this->SOCOM_Users_model->get_users();

        $socom_users = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('GROUP')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('IS_DELETED')
            ->select('UPDATE_USER')
            ->from($this->table)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->result_array();

        $users_full = [];
        foreach ($socom_users as $socom_user) {
            $users_full[$socom_user['USER_ID']] = ['EMAIL' => $users[$socom_user['USER_ID']]] + 
                ['UPDATE_EMAIL' => $users[$socom_user['UPDATE_USER']] ?? 'Pending' ] + 
                $socom_user;
        }
        unset($socom_users, $socom_user);
    
        return array_values($users_full);
    }

    public function activate_user(int $id, bool $auto_activate=false) {
        if(!$auto_activate){
        try {
            $this->SOCOM_Admin_User_model->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }
        }
        
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI->trans_start();

        $this->save_user_history($id);

        $this->DBs->SOCOM_UI
            ->set('UPDATE_USER', (int)$user_id)
            ->set('IS_DELETED', 0)
            ->where('USER_ID', (int)$id)
            ->update($this->table);

        $result = $this->DBs->SOCOM_UI->trans_complete();
        
        $log = vsprintf(
            '%s %s user table updation result transaction was %s',
            [get_called_class(), __METHOD__, ($result ? ' true '  : ' false ')]
        );
        
        log_message('error', $log);
        
        return $result;
    }

    public function set_user(int $id, int|string $gid) {
        if (!is_int($gid) || ($gid < $this->minGid || $gid > $this->maxGid)) {
            $log = vsprintf('%s unable to %s, check $gid is within bounds and integer',[__CLASS__, __METHOD__]);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        if ($this->is_user($id, [0,1])) {
            $this->DBs->SOCOM_UI->trans_start();
            $this->save_user_history($id);

            $this->DBs->SOCOM_UI
                ->set('GROUP', $this->groups[$gid])
                ->set('IS_DELETED', 1)
                ->set('UPDATE_USER', null)
                ->where('USER_ID', (int)$id)
                ->update($this->table);

            $result = $this->DBs->SOCOM_UI->trans_complete();
            $log = vsprintf(
                '%s %s user table updation result trsansaction was %s',
                [get_called_class(), __METHOD__, ($result ? ' true '  : ' false ')]
            );
            log_message('error', $log);
        } else {
            $result = $this->DBs->SOCOM_UI
                ->set('GROUP', $this->groups[$gid])
                ->set('USER_ID', (int)$id)
                ->set('IS_DELETED', 1)
                ->insert($this->table);
            
            if ($result === true) {
                $result = $this->DBs->GUARDIAN_DEV->insert_id();
            }
        }

        return $result;
    }

    public function delete_user(int $id) {
        try {
            $this->SOCOM_Admin_User_model->is_user();
        } catch(ErrorException $e) {
            throw $e;
        }

        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI->trans_start();
        $this->save_user_history($id);

        $this->DBs->SOCOM_UI
            ->set('UPDATE_USER', (int)$user_id)
            ->set('IS_DELETED', 1)
            ->where('USER_ID', (int)$id)
            ->update($this->table);

        $result = $this->DBs->SOCOM_UI->trans_complete();
        $log = vsprintf(
            '%s %s user table updation result trsansaction was %s',
            [get_called_class(), __METHOD__, ($result ? ' true '  : ' false ')]
        );
        log_message('debug', $log);

        return $result;
    }

    public function save_user_history(int $id) {
        $body = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('GROUP')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('IS_DELETED')
            ->select('UPDATE_USER')
            ->from($this->table)
            ->where('USER_ID', (int)$id)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->row_array();

        if (
            isset($body,
                $body['ID'],
                $body['GROUP'],
                $body['USER_ID'],
                $body['CREATED_DATETIME'],
                $body['UPDATED_DATETIME'],
                $body['IS_DELETED'],
                $body['UPDATE_USER']
            )
        ) {
            $result = $this->DBs->SOCOM_UI
                ->set($this->historyIdField, $body['ID'])
                ->set('GROUP', $body['GROUP'])
                ->set('USER_ID', $body['ID'])
                ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                ->set('IS_DELETED', $body['IS_DELETED'])
                ->set('UPDATE_USER', $body['UPDATE_USER'])
                ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                ->insert($this->historyTable);
        } else {
            $log = sprintf('User id %s was not found in table %s and history was not saved.', $id, $this->historyTable);
            log_message('error', $log);
        }
    }
}