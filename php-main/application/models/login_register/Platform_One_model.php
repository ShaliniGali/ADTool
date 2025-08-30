<?php

#[AllowDynamicProperties]
class  Platform_One_model extends CI_Model
{
    protected $usersTable = 'users';
    protected $usersPLATFORMONETable = 'users_platform_one';


    /**
     * Taken from Register_model
     * Created: Michael, 28 Nov 2021
     * 
     * Helper. Updates the account status and accountType
     * @param $id integer
     * @param $status string Active|Login_layer
     * @param $account_type string USER|ADMIN\MODERATOR
     */
    public function updateAccountStatus($id, $status, $account_type = null, $enabled_layers = null)
    {
        $row = ['status' => $status];
        $this->DBs->GUARDIAN_DEV->set($row);
        $this->DBs->GUARDIAN_DEV->where(array('user_id' => $id));
        $this->DBs->GUARDIAN_DEV->update($this->usersPLATFORMONETable);
        
        $this->Login_model->dump_user('users_platform_one_status', json_encode($row), $id);

        $row = ['timestamp' => time(), 'login_attempts' => 0, 'status' => $status];
        if ($enabled_layers) {
            $row['login_layers'] = $enabled_layers;
        }
        if ($account_type !== null) {
            $row['account_type'] = $account_type;
        }
        $this->DBs->GUARDIAN_DEV->set($row);
        $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
        $this->DBs->GUARDIAN_DEV->update($this->usersTable);

        $this->Login_model->dump_user('users_status', json_encode($row), $id);
    }
    
    /**
     * Taken from Register_model
     * 
     * Created: Michael 28 Nov 2021
     * 
     * Admin activates the user and changes status to 'Login_layer'.
     * Sends email to user with recovery keys stating that the account is verified.
     * 
     * @param integer $data: array of user data to be activated, must have $id
     * @param string  $type: Type of verification(admin_verify);
     */
    public function user_activate($data, $type) {


        $id = $data['id'];
        $user_info = $this->Login_model->user_info($id);

        /**
         * 
         * A user has forgotten his password and self acitivating his account or admin is accepting a user account registration request
         * 
         */
        /**
         * Dump user data
         * 
         */
        $this->Login_model->dump_user('account_activate', json_encode(array('user_id' => $id, 'admin_id' => $this->session->userdata('logged_in')['id'], 'type' => $type)));
        /**
         * Send an email to the user who is request for account registration
         * 
         */



        if ($type == "admin_verify") {

            if ($user_info[0]['status'] == AccountStatus::RegistrationPending) {

                $login_layers = str_split($user_info[0]['login_layers'], 1);

                $this->updateAccountStatus_in_user_activate($data, $login_layers, $id);
                
                $user_email = $this->Login_model->user_info($id)[0]['email'];
                $recovery_code = implode(', <br>', json_decode($this->Login_model->get_key_info($id)[0]['recovery_key'], true)['Recoverykeys']);
                /**
                 * Creating email
                 */
                $message = "Congratulations, <br><br> Your requested account for the Rhombus power " . RHOMBUS_PROJECT_NAME . " UI has been reviewed and approved by the Rhombus Power administrators. <br><br> You can login now: <a href='" . base_url() . "'>Login</a><br><br> You may use any of the following single use recovery codes to login to the UI. You can issue a new set of recovery codes once the existing set of recovery codes is exhausted.<br><br>" . $recovery_code;
                $result_message = $this->Generic->send_email(array(
                    'receiverEmail' => $user_email,
                    'subject' => "Request Complete: Account Registration",
                    'receiverName' => "",
                    'template' => 'custom',
                    'footer' => ['ipAddress' => ''],
                    'content' => [
                        ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
                        ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'If this is not you then please contact it@rhombuspower.com.']]],
                        ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
                    ]
                ));

                //$this->Login_model->dump_user('users_keycloak_account_approval', $result_message, $id);

                return true;
            } else {
                return false;
            }
        }
        if ($type == "self_verify") {
            $this->updateAccountStatus($id, AccountStatus::removeStatus($user_info[0]['status'], AccountStatus::Blocked), $data['account_type']);
            return true;
        }
    }

    private function updateAccountStatus_in_user_activate($data,$login_layers,$id){
        if ($data['enableLoginLayer'] == "Yes") {

            ($data['tfa']['gAuth'] == "Yes") ? $login_layers[LoginLayers::GoogleAuthenticator] = LoginLayers::LayerOn : $login_layers[LoginLayers::GoogleAuthenticator] = LoginLayers::LayerOff;
            ($data['tfa']['yubikey'] == "Yes") ? $login_layers[LoginLayers::Yubikey] = LoginLayers::LayerOn : $login_layers[LoginLayers::Yubikey] = LoginLayers::LayerOff;
            ($data['tfa']['cac'] == "Yes") ? $login_layers[LoginLayers::CAC] = LoginLayers::LayerOn : $login_layers[LoginLayers::CAC] = LoginLayers::LayerOff;

            $this->updateAccountStatus($id, AccountStatus::LoginLayer, $data['account_type'], join($login_layers));
        } else {
            $this->updateAccountStatus($id, AccountStatus::Active, $data['account_type'], join($login_layers));
        }
    }

    /**
     * Will verify that the user is authenticated and update the
     * self::usersPLATFORMONETable
     * 
     * Two dumps will be created for the login_platform_one and jwt_platform_one
     * 
     * @param array $data array of data to insert into self::usersPLATFORMONETable
     * 
     * @return bool|int false on failure or the new id from self::usersPLATFORMONETable
     */
    public function registerPLATFORMONEUser($data, $app_schema = 'default') {
        $result_id = false;

        if (
            $data["jwt"] !== false && 
            is_int($data["user_id"]) 
        ) {
            $data['timestamp'] = time();

            if($app_schema != 'default'){
                $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
            }
            $this->DBs->GUARDIAN_DEV->set($data);
            $this->DBs->GUARDIAN_DEV->insert($this->usersPLATFORMONETable);
            $result_id = $this->DBs->GUARDIAN_DEV->insert_id();

            $dump_data = json_encode([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name']
            ]);
            $this->Login_model->dump_user('login_platform_one', $dump_data, $result_id, $app_schema);
            $dump_data = json_encode([
                'jwt' => $data['jwt']
            ]);
            $this->Login_model->dump_user('jwt_platform_one', $dump_data, $result_id, $app_schema);

            unset($data);
        } else {
            log_message('error', 'Could not updateAccount for user');
            redirect('login/logout');
        }

        return $result_id;
    }

    /**
     * This will update the self::usersPLATFORMONETable table with the most recent 
     * jwt.
     * 
     * A dump is created using jwt_platform_one
     * 
     * @return bool true on success
     */
    public function updateJwt() {
        $result = false;

        $current_jwt = $this->rhombus_platform_one->get_current_access_token();
        if ($current_jwt !== false) {
            $data['jwt'] = json_encode($current_jwt);
            $data['timestamp'] = time();

            $this->DBs->GUARDIAN_DEV->set($data);
            $this->DBs->GUARDIAN_DEV->where('email', $current_jwt->email);
            $result = $this->DBs->GUARDIAN_DEV->update($this->usersPLATFORMONETable);

            $dump_data = json_encode([
                'jwt' => $data['jwt']
            ]);
            $this->Login_model->dump_user('jwt_platform_one', $dump_data, $this->session->userdata('logged_in')['id']);

            unset($data);
        }

        return $result;
    }

    /**
     * Updates the account using a id field from self::usersTable
     * The user must be authenticated with Keycloak
     * 
     * A login_keycloak and jwt_platform_one have been created
     * 
     * @param int user id
     * 
     * @return bool true on success
     */
    public function updateAccount($id) {
        $result = false;

        $current_jwt = $this->rhombus_platform_one->get_current_access_token();
        if ($current_jwt !== false && is_int($id)) {
            $max_timestamp = $this->DBs->GUARDIAN_DEV
                ->select('MAX(timestamp) as timestamp')
                ->from($this->usersPLATFORMONETable)
                ->where('user_id', $id)
                ->get()->row_array()['timestamp'] ?? '';

            $data['user_id'] = $id;
            $data['jwt'] = json_encode($current_jwt);
            $data['first_name'] = $current_jwt->given_name;
            $data['last_name'] = $current_jwt->family_name;
            $data['timestamp'] = time();

            $this->DBs->GUARDIAN_DEV->set($data);
            $this->DBs->GUARDIAN_DEV->where('timestamp', $max_timestamp);
            $this->DBs->GUARDIAN_DEV->where('email', $current_jwt->email);
            $result = $this->DBs->GUARDIAN_DEV->update($this->usersPLATFORMONETable);
            
            if ($result) {

                $this->DBs->GUARDIAN_DEV->select('id, name');
                $this->DBs->GUARDIAN_DEV->from($this->usersTable);
                $this->DBs->GUARDIAN_DEV->where('email', $current_jwt->email);
                $row = $this->DBs->GUARDIAN_DEV->get()->row_array();

                $name =  ucfirst(strtolower($current_jwt->given_name.$current_jwt->family_name));

                $dump_data = json_encode([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name']
                ]);
                $this->Login_model->dump_user('login_platform_one', $dump_data, $row['id']);
                $dump_data = json_encode([
                    'jwt' => $data['jwt']
                ]);
                $this->Login_model->dump_user('jwt_platform_one', $dump_data, $row['id']);

                if ($row['name'] !== $name) {
                    $data = ['name' => $name];
                    $this->DBs->GUARDIAN_DEV->set($data);
                    $this->DBs->GUARDIAN_DEV->where('email', $current_jwt->email);
                    $result = $this->DBs->GUARDIAN_DEV->update($this->usersTable);
                }
            }

            unset($data);
        } else {
            log_message('error', 'Could not updateAccount for user');
            redirect('login/logout');
        }

        return $result;
    }

    /**
     * Will return the user from self::usersTable or self::usersPLATFORMONETable
     * 
     * @param string $email the user email
     * @param bool $strict when true will only fetch users with status = AccountStatus::Active
     * @param string $table self::usersTable or self::usersPLATFORMONETable
     * 
     * @return array the user
     */
    public function userExists($email, $strict=true, $table="users") {
        $where = array('u1.email' => $email);
        if($strict == true) {$where['u1.status'] = AccountStatus::Active;}

        $useTable = ($table == $this->usersTable)? $this->usersTable:$this->usersPLATFORMONETable;
        
        $this->DBs->GUARDIAN_DEV->select('*');
        $this->DBs->GUARDIAN_DEV->from($useTable . ' as u1');
        if ($useTable !== $this->usersTable) {
            $this->DBs->GUARDIAN_DEV->join($this->usersTable . ' as u2', 'u2.id = u1.user_id', 'inner');
        }
        $this->DBs->GUARDIAN_DEV->where($where);
        $this->DBs->GUARDIAN_DEV->limit(1);
        $this->DBs->GUARDIAN_DEV->order_by('u1.id', "desc");
        return $this->DBs->GUARDIAN_DEV->get()->result_array();
    }

    /**
     * Returns a status from a registered user in the
     * self::usersTable with a status of AcountStatus::{Active,Blocked,Rejects, or RegistrationPending}
     * 
     * @return bool|string
     */
    public function promptAccountRegistration($email, $app_schema = 'default') {
        if($app_schema != 'default') {
            $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
        }

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

    /**
     * returns the self::usersPLATFORMONETable table
     * 
     * @return string
     */
    public function get_PLATFORMONE_table(){ 
        return $this->usersPLATFORMONETable; 
    }

    /**
     * Fetches all undeleted users' ids, emails and statuses.
     * 
     * @param void
     * @return array
     */
    public function getUsersStatuses() {
        $db = $this->DBs->GUARDIAN_DEV;
        $db->select('email, id, status');
        $db->from($this->usersPLATFORMONETable);
       // $db->where('email IS NOT NULL');
        $db->where('status !=', AccountStatus::Deleted);
       // $db->group_by('email');
        $db->order_by('id', 'DESC');
        return $db->get()->result_array();
    }

    public function registerActiveUsers(array $users) {
        $db = $this->DBs->GUARDIAN_DEV;
        return $db->insert_batch($this->usersPLATFORMONETable, $users);
    }

    public function setUsersActiveByIds(array $ids) {
        $db = $this->DBs->GUARDIAN_DEV;
        $db->set('status', AccountStatus::Active);
        $db->where('id IN (' . implode(',', $ids) . ')');
        return $db->update($this->usersPLATFORMONETable);
    }
}
