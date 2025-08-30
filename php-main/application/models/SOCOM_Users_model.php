<?php

#[AllowDynamicProperties]
class SOCOM_Users_model extends CI_Model {
    
    public function is_super_admin() {
        $emails = SOCOM_ADMIN_USERS;


        $user_id = (int)$this->session->userdata("logged_in")["id"];
        $user = $this->get_user($user_id) ?? [];

        return (isset($user[$user_id]) && in_array($user[$user_id], $emails, true) === true);
    }

    public function user_can_super_admin() {
        if (!$this->is_super_admin() ) {
            log_message('error', 'User is not an admin user');

            throw new ErrorException('User is not an admin user');
        }
    }

    public function user_can_admin() {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $result = true;

        if (
            $this->is_super_admin() === false &&
            $this->is_admin_user($user_id) === false
        ) {
            log_message('error', 'User is not an admin user');

            throw new ErrorException('User is not an admin user');
        }

        return $result;
    }

    public function user_can_ao_ad_admin() {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $result = true;

        if (
            $this->is_ao_ad_user($user_id) === false
        ) {
            log_message('error', 'User is not an ao or ad user');

            throw new ErrorException('User is not an ao or ad user');
        }

        return $result;
    }

    public function get_id_from_email($email) {
        try {
            $this->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

        return  $this->DBs->GUARDIAN_DEV
            ->select('id')
            ->from('users')
            ->where('email', $email)
            ->where('status', AccountStatus::Active)
            ->get()
            ->row_array()['id'] ?? false;
    }
    
    public function get_users() {
        $users = $this->DBs->GUARDIAN_DEV
            ->select('id')
            ->select('name')
            ->select('email')
            ->select('status')
            ->from('users')
            ->where('status', AccountStatus::Active)
            ->get()
            ->result_array() ?? [];

        return array_column($users, 'email', 'id');
    }

    public function get_user($id) {
        $users = $this->DBs->GUARDIAN_DEV
            ->select('id')
            ->select('name')
            ->select('email')
            ->from('users')
            ->where('id', (int)$id)
            ->where('status', AccountStatus::Active)
            ->get()
            ->result_array();

        return array_column($users, 'email', 'id');
    }

    public function get_ao_ad_user() {
        try {
            $this->user_can_ao_ad_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

       

        $id = (int)$this->session->userdata("logged_in")["id"];
        $user =  $this->get_user($id);

        $ao_ad_users = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('GROUP')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('IS_DELETED')
            ->select('UPDATE_USER')
            ->from('USR_AO_AD_USERS')
            ->where('USER_ID', $id)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->result_array();

        $ao_ad_users_full = [];
        foreach ($ao_ad_users as $ao_ad_user) {
            $ao_ad_users_full[$ao_ad_user['USER_ID']] = ['EMAIL' => $user[$ao_ad_user['USER_ID']]] + 
                ['UPDATE_EMAIL' => $user[$ao_ad_user['UPDATE_USER']] ?? 'Pending' ] + 
                $ao_ad_user;

        }
        unset($ao_ad_users, $ao_ad_user);
    
        return array_values($ao_ad_users_full);
    }
    
    public function get_ao_ad_users(array $users) {
        try {
            $this->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

        $ids = array_keys($users);
        
        $ids = array_map('intval', $ids);

        $ao_ad_users = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('GROUP')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('IS_DELETED')
            ->select('UPDATE_USER')
            ->from('USR_AO_AD_USERS')
            ->where_in('USER_ID', $ids)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->result_array();

        $ao_ad_users_full = [];
        foreach ($ao_ad_users as $ao_ad_user) {
            $ao_ad_users_full[$ao_ad_user['USER_ID']] = ['EMAIL' => $users[$ao_ad_user['USER_ID']]] + 
                ['UPDATE_EMAIL' => $users[$ao_ad_user['UPDATE_USER']] ?? 'Pending' ] + 
                $ao_ad_user;

        }
        unset($ao_ad_users, $ao_ad_user);
    
        return array_values($ao_ad_users_full);
    }

    public function activate_ao_ad_user(int $id) {
        try {
            $this->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }
        
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI->trans_start();
        $this->save_ao_ad_user_history($id);

        $this->DBs->SOCOM_UI
            ->set('UPDATE_USER', (int)$user_id)
            ->set('IS_DELETED', 0)
            ->where('USER_ID', (int)$id)
            ->update('USR_AO_AD_USERS');

        $result = $this->DBs->SOCOM_UI->trans_complete();
        log_message('debug', 'AO AD User table updation result trsansaction was '.$result ? ' true '  : ' false ');
        return $result;
    }

    public function set_ao_ad_user(int $id, int $gid) {
        if ($gid < 1 || $gid > 4) {
            $log = vsprintf('%s unable to %s',[__CLASS__, __METHOD__]);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        $groups = [
            1 => 'NONE',
            2 => 'AO',
            3 => 'AD',
            4 => 'AO and AD',
        ];

        if ($this->is_ao_ad_user($id, [0,1])) {
            $this->DBs->SOCOM_UI->trans_start();
            $this->save_ao_ad_user_history($id);

            $this->DBs->SOCOM_UI
                ->set('GROUP', $groups[$gid])
                ->set('IS_DELETED', 1)
                ->set('UPDATE_USER', null)
                ->where('USER_ID', (int)$id)
                ->update('USR_AO_AD_USERS');

            $result = $this->DBs->SOCOM_UI->trans_complete();
            log_message('debug', 'AO AD User table update result transaction was '.$result ? ' true '  : ' false ');
        } else {
            $result = $this->DBs->SOCOM_UI
                ->set('GROUP', $groups[$gid])
                ->set('USER_ID', (int)$id)
                ->set('IS_DELETED', 1)
                ->insert('USR_AO_AD_USERS');
            
            if ($result === true) {
                $result = $this->DBs->GUARDIAN_DEV->insert_id();
            }
        }

        return $result;
    }

    public function delete_ao_ad_user(int $id) {
        try {
            $this->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI->trans_start();
        $this->save_ao_ad_user_history($id);

        $this->DBs->SOCOM_UI
            ->set('UPDATE_USER', (int)$user_id)
            ->set('IS_DELETED', 1)
            ->where('USER_ID', (int)$id)
            ->update('USR_AO_AD_USERS');

        $result = $this->DBs->SOCOM_UI->trans_complete();

        log_message('debug', 'AO AD User table deletion result transaction was '.$result ? ' true '  : ' false ');
        return $result;
    }

    public function is_ao_ad_user(int $id, $deleted = [0]) {
        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from('USR_AO_AD_USERS')
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', $deleted)
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
    }

    public function save_ao_ad_user_history(int $userId) {
        $body = $this->DBs->SOCOM_UI
        ->select('ID')
        ->select('GROUP')
        ->select('USER_ID')
        ->select('CREATED_DATETIME')
        ->select('UPDATED_DATETIME')
        ->select('IS_DELETED')
        ->select('UPDATE_USER')
        ->from('USR_AO_AD_USERS')
        ->where('USER_ID', (int)$userId)
        ->order_by('UPDATED_DATETIME DESC')
        ->get()
        ->row_array();

        if (isset($body, $body['ID'], $body['GROUP'], $body['USER_ID'], $body['CREATED_DATETIME'], $body['UPDATED_DATETIME'], $body['IS_DELETED'], $body['UPDATE_USER'])) {
            $result = $this->DBs->SOCOM_UI
                ->set('AO_AD_ID', $body['ID'])
                ->set('GROUP', $body['GROUP'])
                ->set('USER_ID', $body['ID'])
                ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                ->set('IS_DELETED', $body['IS_DELETED'])
                ->set('UPDATE_USER', $body['UPDATE_USER'])
                ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                ->insert('USR_AO_AD_USERS_HISTORY');
            } else {
                $log = sprintf('User id %s was not found in table %s and history was not saved.', $userId, 'USR_AO_AD_USERS');
                log_message('error', $log);
            }
    }

    public function is_admin_user(int $id, array $deleted = [0]) {
        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from('USR_ADMIN_USERS')
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', $deleted)
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
    }

    public function get_admin_user(array $users) {
        try {
            $this->user_can_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

        $ids = array_keys($users);

        $ids = array_map('intval', $ids);
        
        $admin_users = $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('GROUP')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->select('UPDATED_DATETIME')
            ->select('IS_DELETED')
            ->select('UPDATE_USER')
            ->from('USR_ADMIN_USERS')
            ->where_in('USER_ID', $ids)
            ->order_by('UPDATED_DATETIME DESC')
            ->get()
            ->result_array();

        $users_full = [];
        foreach ($admin_users as $admin_user) {
            $users_full[$admin_user['USER_ID']] = ['EMAIL' => $users[$admin_user['USER_ID']]] + 
                ['UPDATE_EMAIL' => $users[$admin_user['UPDATE_USER']] ?? 'Pending'] +
                $admin_user;
        }
        unset($admin_users, $admin_user);

        return array_values($users_full);
    }

    public function activate_admin_user(int $id) {
        try {
            $this->user_can_super_admin();
        } catch(ErrorException $e) {
            throw $e;
        }
        
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI->trans_start();
        $this->save_admin_user_history($id);

        $this->DBs->SOCOM_UI
            ->set('UPDATE_USER', (int)$user_id)
            ->set('IS_DELETED', 0)
            ->where('USER_ID', (int)$id)
            ->update('USR_ADMIN_USERS');

        $result = $this->DBs->SOCOM_UI->trans_complete();
        log_message('debug', 'Admin User table updation result transaction was '.$result ? ' true '  : ' false ');
        return $result;
    }

    public function set_admin_user(int $id, int $gid) {
        if ($gid < 1 || $gid > 2) {
            $log = vsprintf('%s unable to %s',[__CLASS__, __METHOD__]);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        $groups = [
            1 => 'NONE',
            2 => 'User Admin',
        ];

        if ($this->is_admin_user($id, [0,1])) {
            $this->DBs->SOCOM_UI->trans_start();
            $this->save_admin_user_history($id);

            $this->DBs->SOCOM_UI
                ->set('GROUP', $groups[$gid])
                ->set('IS_DELETED', 1)
                ->set('UPDATE_USER', null)
                ->where('USER_ID', (int)$id)
                ->update('USR_ADMIN_USERS');
            
            $result = $this->DBs->SOCOM_UI->trans_complete();
            log_message('debug', 'Admin User table updation result transaction was '.$result ? ' true '  : ' false ');
        } else {
            $result = $this->DBs->SOCOM_UI
                ->set('GROUP', $groups[$gid])
                ->set('USER_ID', (int)$id)
                ->set('IS_DELETED', 1)
                ->insert('USR_ADMIN_USERS');
            
            if ($result === true) {
                $result = $this->DBs->GUARDIAN_DEV->insert_id();
            }
        }

        return $result;
    }

    public function delete_admin_user(int $id) {
        try {
            $this->user_can_super_admin();
        } catch(ErrorException $e) {
            throw $e;
        }

        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $this->DBs->SOCOM_UI->trans_start();
        $this->save_admin_user_history($id);

        $this->DBs->SOCOM_UI
            ->set('UPDATE_USER', (int)$user_id)
            ->set('IS_DELETED', 1)
            ->where('USER_ID', (int)$id)
            ->update('USR_ADMIN_USERS');
        
        $result = $this->DBs->SOCOM_UI->trans_complete();
        log_message('debug', 'Admin User table deletion result transaction was '.$result ? ' true '  : ' false ');
        return $result;
    }

    public function save_admin_user_history(int $userId) {
        $body = $this->DBs->SOCOM_UI
        ->select('ID')
        ->select('GROUP')
        ->select('USER_ID')
        ->select('CREATED_DATETIME')
        ->select('UPDATED_DATETIME')
        ->select('IS_DELETED')
        ->select('UPDATE_USER')
        ->from('USR_ADMIN_USERS')
        ->where('USER_ID', (int)$userId)
        ->order_by('UPDATED_DATETIME DESC')
        ->get()
        ->row_array();

        if (isset($body, $body['ID'], $body['GROUP'], $body['USER_ID'], $body['CREATED_DATETIME'], $body['UPDATED_DATETIME'], $body['IS_DELETED'], $body['UPDATE_USER'])) {
            $result = $this->DBs->SOCOM_UI
                ->set('ADMIN_ID', $body['ID'])
                ->set('GROUP', $body['GROUP'])
                ->set('USER_ID', $body['ID'])
                ->set('CREATED_DATETIME', $body['CREATED_DATETIME'])
                ->set('UPDATED_DATETIME', $body['UPDATED_DATETIME'])
                ->set('IS_DELETED', $body['IS_DELETED'])
                ->set('UPDATE_USER', $body['UPDATE_USER'])
                ->set('HISTORY_DATETIME', $body['UPDATED_DATETIME'])
                ->insert('USR_ADMIN_USERS_HISTORY');
        } else {
            $log = sprintf('User id %s was not found in table %s and history was not saved.', $userId, 'USR_ADMIN_USERS');
            log_message('error', $log);
        }
    }
    
            /**
     * Gets user info
     *
     * @return mixed
     */
    public function get_user_info($id)
    {
        $result = $this->DBs->GUARDIAN_DEV
                ->where('id', $id)
                ->from('users')
                ->get()
                ->result_array();

        if (!empty($result))
        {
                return $result[0];
        }

        return false;
    }
}