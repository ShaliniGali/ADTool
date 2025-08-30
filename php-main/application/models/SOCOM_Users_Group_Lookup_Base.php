<?php

require_once(APPPATH.'models/SOCOM_User_Base.php');

#[AllowDynamicProperties]
class SOCOM_Users_Group_Lookup_Base extends SOCOM_User_Base {
    protected $group_table;
    protected $group_field;

    protected $groups = [];

    public function user_has_old_group(?int $id = null) {
        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }

        $group = $this->DBs->SOCOM_UI
            ->select('GROUP')
            ->from($this->table)
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', 0)
            ->get()
            ->row_array()['GROUP'] ?? false;

        if ($group !== false && !in_array($group, $this->getGroups(), true)) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    public function is_user(?int $id = null, array $deleted = [0]) {
        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }

        if (!is_int($id) || $id === 0) {
            return false;
        }

        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from($this->table. ' t')
            ->join($this->group_table . ' gt', 'ON t.GROUP = gt.'.$this->group_field)
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', $deleted)
            ->where($this->where['field'], $this->where['value'])
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
    }

    public function is_user_by_group(int|string $group_name, int $id = null, array $deleted = [0]) {
        if (!in_array($group_name, $this->getGroups(), true)) {
            $log = vsprintf('%s unable to %s',[__CLASS__, __METHOD__]);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }
        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from($this->table. ' t')
            ->join($this->group_table . ' gt', 'ON t.GROUP = gt.'.$this->group_field)
            ->where('USER_ID', (int)$id)
            ->where_in('IS_DELETED', $deleted)
            ->where($this->where['field'], $this->where['value'])
            ->where('GROUP', $group_name)
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
    }

    public function set_user(int $id, int|string $group_name) {
        if (!in_array($group_name, $this->getGroups(), true)) {
            $log = vsprintf('%s unable to %s',[__CLASS__, __METHOD__]);
            log_message('error', $log);

            throw new ErrorException($log);
        }

        if ($this->is_user($id, [0,1]) || $this->user_has_old_group($id)) {
            $this->DBs->SOCOM_UI->trans_start();
            $this->save_user_history($id);

            $this->DBs->SOCOM_UI
                ->set('GROUP', $group_name)
                ->set('IS_DELETED', 1)
                ->set('UPDATE_USER', null)
                ->where('USER_ID', (int)$id)
                ->update($this->table);

            $result = $this->DBs->SOCOM_UI->trans_complete();
            $log = vsprintf(
                '%s %s user table updation result transaction was %s',
                [get_called_class(), __METHOD__, ($result ? ' true '  : ' false ')]
            );
            log_message('error', $log);
        } else {
            $result = $this->DBs->SOCOM_UI
                ->set('GROUP', $group_name)
                ->set('USER_ID', (int)$id)
                ->set('IS_DELETED', 1)
                ->insert($this->table);
            
            if ($result === true) {
                $result = $this->DBs->GUARDIAN_DEV->insert_id();
            }
        }

        return $result;
    }

    public function getGroups() {
        if (empty($this->groups)) {
            $groups = array_column($this->DBs->SOCOM_UI
                ->select($this->group_field)
                ->from($this->group_table)
                ->order_by($this->group_field)
                ->group_by($this->group_field)
                ->where($this->where['field'], $this->where['value'])
                ->get()
                ->result_array(), $this->group_field);
            
            if (!empty($groups)) {
                $keys = range(1, count($groups));
                
                $this->groups = array_combine($keys, $groups);
            }
        }

        return $this->groups;
    }
    public function is_role_user(int $id) {
        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }

        if (!is_int($id) || $id === 0) {
            return false;
        }

        try{
            return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from($this->table. ' t')
            ->join($this->group_table . ' gt', 'ON t.GROUP = gt.'.$this->group_field)
            ->where('USER_ID', (int)$id)
            ->where_in('GROUP', ['J8', 'J8-A'])
            ->where($this->where['field'], $this->where['value'])
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
        } catch(Exception $e){
            return false;
        }
    }
    
    public function is_role_restricted(int $id) {
        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }
        if (!is_int($id) || $id === 0) {
            return false;
        }

        try{
   
        return $this->DBs->SOCOM_UI
            ->select('COUNT(*) COUNT')
            ->from($this->table. ' t')
            ->join($this->group_table . ' gt', 'ON t.GROUP = gt.'.$this->group_field)
            ->where('USER_ID', (int)$id)
            ->group_start()
            ->like('GROUP', 'SOFM', 'after')
            ->or_like('GROUP', 'SORDAC', 'after')
            ->or_where('GROUP', 'SOLIC')
            ->group_end()
            ->where($this->where['field'], $this->where['value'])
            ->get()
            ->row_array()['COUNT'] >= 1 ?? false;
        }
        catch (Exception $e) {
            return false;
        }
    }

    public function is_role_admin(int $id) {
        if ($id === null) {
            $id = (int)$this->session->userdata("logged_in")["id"];
        }
        if (!is_int($id) || $id === 0) {
            return false;
        }
        try{
            $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
            return $this->is_role_user($id) && $is_pom_admin;
        }catch(Exception $e){
            return false;
        } 
    }

    public function is_guest() {
        try {
            return $this->rbac_users->is_guest();
        } catch(Exception $e) {
            return false;
        } 
    }
}