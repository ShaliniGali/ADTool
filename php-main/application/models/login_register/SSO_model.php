<?php

#[AllowDynamicProperties]
class  SSO_model extends CI_Model
{
    protected $usersTable = 'users';
    protected $usersSSOTable = 'users_SSO';


    public function userExists($email, $strict=true, $table="users") {
        $where = array('email' => $email);
        if($strict == true) $where['status'] = AccountStatus::Active;

        $useTable = ($table == $this->usersTable)? $this->usersTable:$this->usersSSOTable;

        $this->DBs->GUARDIAN_DEV->select('*');
        $this->DBs->GUARDIAN_DEV->from($useTable);
        $this->DBs->GUARDIAN_DEV->where($where);
        $this->DBs->GUARDIAN_DEV->limit(1);
        $this->DBs->GUARDIAN_DEV->order_by('id', "desc");
        return $this->DBs->GUARDIAN_DEV->get()->result_array();
    }

    public function promptAccountRegistration($email) {
        $this->DBs->GUARDIAN_DEV->select('*');
        $this->DBs->GUARDIAN_DEV->from($this->usersTable);
        $this->DBs->GUARDIAN_DEV->where('email', $email);
        $this->DBs->GUARDIAN_DEV->group_start();
        $this->DBs->GUARDIAN_DEV->like('status', AccountStatus::Active); // Should never be the case, just for safety.
        $this->DBs->GUARDIAN_DEV->or_like('status', AccountStatus::Blocked);
        $this->DBs->GUARDIAN_DEV->or_like('status', AccountStatus::Rejected);
        $this->DBs->GUARDIAN_DEV->or_like('status', AccountStatus::RegistrationPending);
        $this->DBs->GUARDIAN_DEV->group_end();
        $this->DBs->GUARDIAN_DEV->limit(1);
        $this->DBs->GUARDIAN_DEV->order_by('id');
        $res = $this->DBs->GUARDIAN_DEV->get()->result_array();
        if (!empty($res)) {
            return $res[0]['status'];
        }
        return true;
    }

    public function registerSSOUser($email, $status, $app_schema = 'default') {
        if($app_schema != 'default'){
            $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
        }
        $this->DBs->GUARDIAN_DEV->insert($this->usersSSOTable, array(
            'email' => $email,
            'status' => $status,
            'timestamp' => time()
        ));
    }

    public function updateAccountStatus($id, $status)
    {
        $row = array('timestamp' => time(), 'status' => $status);
        $this->DBs->GUARDIAN_DEV->set($row);
        $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
        $this->DBs->GUARDIAN_DEV->update($this->usersSSOTable);
    }

    public function get_user_table(){ return $this->usersTable; }
    public function get_SSO_table(){ return $this->usersSSOTable; }

    /**
     * @author Moheb, July 1st, 2021
     * 
     * Sets all user statuses identified by user ids to Active.
     * Returns true if all users were updated successfully; otherwise, returns false. 
     * 
     * @param array $ids, all user ids to set Active
     * @return bool
     */
    public function setUsersActiveByIds(array $ids) {
        $db = $this->DBs->GUARDIAN_DEV;
        $db->set('status', AccountStatus::Active);
        $db->where('id IN (' . implode(',', $ids) . ')');
        return $db->update($this->usersSSOTable);
    }

    /**
     * @author Moheb, July 1st, 2021
     * 
     * Adds all the given emails to the users table with an Active status,
     * and a randomly generated password.
     * 
     * NOTE: Logging in, via non-SSO, with any email registered by the function below
     * must reset the password associated with the email first.
     * Returns the number of users added successfully to the users table,
     * otherwise returns false.
     * 
     * @param array $users all users to register
     * @return bool|
     * 
     */
    public function registerActiveUsers(array $users) {
        $db = $this->DBs->GUARDIAN_DEV;
        return $db->insert_batch($this->usersSSOTable, $users);
    }

    /**
     * @author Moheb, July 1st, 2021
     * 
     * Fetches all undeleted users' ids, emails and statuses.
     * 
     * @param void
     * @return array
     */
    public function getUsersStatuses() {
        $db = $this->DBs->GUARDIAN_DEV;
        $db->select('email, id, status');
        $db->from($this->usersSSOTable);
        $db->where('email IS NOT NULL');
        $db->where('status !=', AccountStatus::Deleted);
       // $db->group_by('email');
        $db->order_by('id', 'DESC');
        return $db->get()->result_array();
    }
} 