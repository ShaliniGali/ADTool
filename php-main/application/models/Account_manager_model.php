<?php
/**
 * Created Sai 11 August 2020
 */
#[AllowDynamicProperties]
class  Account_manager_model extends CI_Model
{


    private $table_user = 'users';
    private $table_users_keys = 'users_keys';
    private $usersSSOTable = 'users_SSO';
    private $users_keycloak_table = 'users_keycloak';
    private $users_platformone_table = 'users_platform_one';
    private $sso_table;

    function __construct() {
        if (RHOMBUS_SSO_KEYCLOAK=='TRUE') {
            $this->sso_table = $this->users_keycloak_table;
        } else if (RHOMBUS_SSO_PLATFORM_ONE === 'TRUE') {
            $this->sso_table = $this->users_platformone_table;
        } else {
            $this->sso_table = $this->usersSSOTable;
        }
    }

    /**
     * created Sai August 11th 2020
     * Gets account details from users and users_keys table
     */
    public function getAccount()
    {
        $this->DBs->GUARDIAN_DEV->select('users.id, users.email, users.account_type, users_keys.admin_expiry, users.status');
        $this->DBs->GUARDIAN_DEV->from($this->table_user);
        $this->DBs->GUARDIAN_DEV->where('status = "'.AccountStatus::Active.'" OR status = "'.AccountStatus::RegistrationPending.'"');
        $this->DBs->GUARDIAN_DEV->join('users_keys', 'users_keys.user_id = users.id');
        $user = $this->DBs->GUARDIAN_DEV->get()->result_array();
        return $user;
    }


    /**
     * created Sai August 13th 2020
     * Updates the data provided by the superadmin in users and users table
     * @param array  $data  
     */
    public function updateTableUser($data)
    {
        $this->DBs->GUARDIAN_DEV->set('account_type', $data['AccountType']);
        $this->DBs->GUARDIAN_DEV->where('id', $data['Id']);
        $response_update_users = $this->DBs->GUARDIAN_DEV->update($this->table_user);
        return $response_update_users;
    }

    /**
     * created Sai August 11th 2020
     * Updates the data provided by the superadmin in users and users_keys table
     * @param array  $data  
     * @param string $type typeAdmin | typeUser
     */
    public function updateUser($data, $type)
    {
        if($data['type'] == 'sso'){
            $this->DBs->GUARDIAN_DEV->select($this->table_user.'.id');
            $this->DBs->GUARDIAN_DEV->from($this->table_user);
            $this->DBs->GUARDIAN_DEV->join($this->sso_table, $this->table_user.'.email = '.$this->sso_table.'.email');
            $this->DBs->GUARDIAN_DEV->where(array($this->sso_table.'.id'=> $data['Id'], $this->sso_table.'.status' => AccountStatus::Active, $this->table_user.'.status' => AccountStatus::Active));
            $data['Id'] = $this->DBs->GUARDIAN_DEV->get()->result_array()[0]["id"];

        }

        if ($type == "typeAdmin") {
            $this->DBs->GUARDIAN_DEV->set('admin_expiry', $data['ExpiryDate']);
            $this->DBs->GUARDIAN_DEV->where('user_id', $data['Id']);
            $response_update_userkeys = $this->DBs->GUARDIAN_DEV->update($this->table_users_keys);

            if ($response_update_userkeys) {
                $res = $this->updateTableUser($data);
            }
            if ($res) {
                /**
                 * dumping  id 
                 */
                $this->dump('Account_status_changed', $data['Id']);
                $result['message'] = "success";
                // return $result moved outside to make the deadcode accessible
                // return $result;
            }
        } else {
            $res = $this->updateTableUser($data);
            if ($res) {
                /**
                 * dumping  id 
                 */
                $this->dump('Account_status_changed', $data['Id']);
                $result['message'] = "success";
                 // return $result;
            }
            // TODO: Not sure when the else condition will be executed. Need to write an else part and test
        }
        return $result;
    }

    public function deleteSubapps($email){
        $db = $this->DBs->GUARDIAN_DEV;
        $db->set('status', AccountStatus::Deleted);
        $db->set('timestamp', time());
        $db->where('email', $email);
        $db->update('users_subapp');
    }

    /**
     * created sai: August 11 2020
     * Marks account status to delete
     * @param integer $id
     */
    public function deleteAccount($id, $email = null, $type = 'user')
    {
        $dumpType = 'deleted_user_account';
        $db = $this->DBs->GUARDIAN_DEV;
        $result['message'] = "error";

        if ($type === 'sso') {
            $dumpType = 'sso_deleted_user_account';
            $this->deleteSubapps($email);
            $db->set('status', AccountStatus::Deleted);
            $db->where('id', $id);
            $db->update($this->usersSSOTable);

            $db->set('status', AccountStatus::Deleted);
            $db->where('email', $email);
            $db->update($this->users_keycloak_table);

            $db->set('status', AccountStatus::Deleted);
            $db->where('email', $email);
            $db->update($this->users_platformone_table);

            $db->set('status', AccountStatus::Deleted);
            $db->where('email', $email);
            $response_update_users = $this->DBs->GUARDIAN_DEV->update($this->table_user);
        } else {
            $db->set('status', AccountStatus::Deleted);
            $db->where('id', $id);
            $response_update_users = $this->DBs->GUARDIAN_DEV->update($this->table_user);
        }

        if ($response_update_users) {
            $this->dump($dumpType, $id);
            $result['message'] = "success";
        }
        return $result;
    }


    /**
     * Dumps admin activity
     */
    public function dump($type, $id)
    {
        $dump_data = json_encode(array('id' => $id));
        $this->Login_model->dump_user($type, $dump_data, $this->session->userdata('logged_in')['id']);
    }

    public function isSSOAvailable() {
            $db = $this->DBs->GUARDIAN_DEV;
            $db->select($this->sso_table.'.id, '.$this->sso_table.'.email, '.$this->sso_table.'.status, account_type, admin_expiry');
            $db->from($this->sso_table);
            $db->join($this->table_user, $this->sso_table.'.email = '.$this->table_user.'.email');
            $db->join('users_keys', 'users_keys.user_id = users.id', 'left');

            $db->where_in($this->sso_table.'.status', [AccountStatus::Active, AccountStatus::RegistrationPending]);
            $db->where_in($this->table_user.'.status', [AccountStatus::Active, AccountStatus::RegistrationPending]);
            return $db->get()->result_array();
    }

    public function activateSSOUSer($id, $email, $account_type=null) {
        $result = false;
        $db = $this->DBs->GUARDIAN_DEV;
        $db->set('status', AccountStatus::Active);
        $db->where('id', $id);
        $updated = $db->update($this->sso_table);

        $db->set('status', AccountStatus::Active);
        if($account_type != null)
            $db->set('account_type', $account_type);
        $db->where('email', $email);
        $db->where('status', AccountStatus::RegistrationPending);
        $updated = $db->update($this->table_user);

        if ($this->sso_table == $this->usersSSOTable) {
            $r_update_tables = [
                $this->users_keycloak_table,
                $this->users_platformone_table
            ];
        } else if ($this->sso_table == $this->users_keycloak_table) {
            $r_update_tables = [
                $this->usersSSOTable,
                $this->users_platformone_table
            ];
        } else if ($this->sso_table == $this->users_platformone_table) {
            $r_update_tables = [
                $this->usersSSOTable,
                $this->users_keycloak_table
            ];
        }

        if (!empty($r_update_tables)) {
            foreach ($r_update_tables as $u_table) {
                $db->select('MAX(timestamp) as timestamp');
                $db->where('email', $email);
                $db->where('status', AccountStatus::RegistrationPending);
                $time = $db->get($u_table)->row_array()['timestamp'];

                $db->set('status', AccountStatus::Active);
                $db->where('email', $email);
                $db->where('timestamp', $time);
                $db->where('status', AccountStatus::RegistrationPending);
                $updated = $db->update($u_table);
            }
            unset($r_update_tables, $u_table);
        }
        
        if ($updated) {
            $this->dump('sso_registration_approval', $id);
            $result = true;
        }
        return $result;
    }

    public function registerSubapps($email, $subappId){
        $subapp_data = array(
            'status' => AccountStatus::Active,
            'timestamp' => time()
        );
        $this->DBs->GUARDIAN_DEV->set($subapp_data);
        $this->DBs->GUARDIAN_DEV->where('email', $email);
        $this->DBs->GUARDIAN_DEV->where('subapp_id', $subappId);
        return $this->DBs->GUARDIAN_DEV->update('users_subapp');
    }

    public function saveSubappsType($email, $subappId, $type){
        $subapp_data = array(
            'status' => AccountStatus::Active,
            'account_type' => $type,
            'timestamp' => time()
        );
        $this->DBs->GUARDIAN_DEV->set($subapp_data);
        $this->DBs->GUARDIAN_DEV->where('email', $email);
        $this->DBs->GUARDIAN_DEV->where('subapp_id', $subappId);
        return $this->DBs->GUARDIAN_DEV->update('users_subapp');
    }
}
