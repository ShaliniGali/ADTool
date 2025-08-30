<?php

#[AllowDynamicProperties]
class  Keycloak_model extends CI_Model
{
    protected $usersTable = 'users';
    protected $usersKEYCLOAKTable = 'users_keycloak';

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
        $this->DBs->GUARDIAN_DEV->update($this->usersKEYCLOAKTable);
        
        $this->Login_model->dump_user('users_keycloak_status', json_encode($row), $id);

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

                $this->updateAccountStatus_in_user_activate($data,$login_layers,$id);

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

    /**
     * Will verify that the user is authenticated and update the
     * self::usersKEYCLOAKTable
     * 
     * Two dumps will be created for the login_keycloak and token_keycloak
     * 
     * @param array $data array of data to insert into self::usersKEYCLOAKTable
     * 
     * @return bool|int false on failure or the new id from self::usersKEYCLOAKTable
     */
    public function registerKEYCLOAKUser($data, $app_schema = 'default') {
        if($app_schema != 'default'){
            $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
        }
        $result_id = false;

        // $current_token = $this->rhombus_keycloak->get_current_access_token();
        
        if (
            $data["token"] !== false && 
            is_int($data["user_id"]) 
        ) {
            $data['timestamp'] = time();

            $this->DBs->GUARDIAN_DEV->set($data);
            $this->DBs->GUARDIAN_DEV->insert($this->usersKEYCLOAKTable);
            $result_id = $this->DBs->GUARDIAN_DEV->insert_id();

            $dump_data = json_encode([
                'session_state' => $data['session_state'], 
                'login_code' => $data['login_code'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name']
            ]);
            $this->Login_model->dump_user('login_keycloak', $dump_data, $result_id);
            $dump_data = json_encode([
                'token' => $data['token']
            ]);
            $this->Login_model->dump_user('token_keycloak', $dump_data, $result_id);

            unset($data);
        } else {
            log_message('error', 'Could not updateAccount for user');
            redirect('login/logout');
        }

        return $result_id;
    }

    /**
     * This will update the self::usersKEYCLOAKTable table with the most recent 
     * token.
     * 
     * A dump is created using token_keycloak
     * 
     * @return bool true on success
     */
    public function updateToken() {
        $result = false;

        $current_token = $this->rhombus_keycloak->get_current_access_token();
        if ($current_token !== false) {
            $data['token'] = json_encode($current_token->current_token);
            $data['timestamp'] = time();

            $this->DBs->GUARDIAN_DEV->set($data);
            $this->DBs->GUARDIAN_DEV->where('email', $current_token->email);
            $result = $this->DBs->GUARDIAN_DEV->update($this->usersKEYCLOAKTable);

            $dump_data = json_encode([
                'token' => $data['token']
            ]);
            $this->Login_model->dump_user('token_keycloak', $dump_data, $this->session->userdata('logged_in')['id']);

            unset($data);
        }

        return $result;
    }

    /**
     * Updates the account using a id field from self::usersTable
     * The user must be authenticated with Keycloak
     * 
     * A login_keycloak and token_keycloak have been created
     * 
     * @param int user id
     * 
     * @return bool true on success
     */
    public function updateAccount($id) {
        $result = false;

        $current_token = $this->rhombus_keycloak->get_current_access_token();
        if ($current_token !== false && is_int($id)) {
            $data['user_id'] = $id;
            $data['session_state'] = $current_token->session_state;
            $data['login_code'] = $this->session->userdata('keycloak_login_code');
            $data['token'] = json_encode($current_token->current_token);
            $data['first_name'] = $current_token->given_name;
            $data['last_name'] = $current_token->family_name;
            $data['timestamp'] = time();

            $this->DBs->GUARDIAN_DEV->set($data);
            $this->DBs->GUARDIAN_DEV->where('email', $current_token->email);
            $result = $this->DBs->GUARDIAN_DEV->update($this->usersKEYCLOAKTable);
            
            if ($result) {

                $this->DBs->GUARDIAN_DEV->select('id, name');
                $this->DBs->GUARDIAN_DEV->from($this->usersTable);
                $this->DBs->GUARDIAN_DEV->where('email', $current_token->email);
                $row = $this->DBs->GUARDIAN_DEV->get()->row_array();

                $name =  ucfirst(strtolower($current_token->given_name.$current_token->family_name));

                $dump_data = json_encode([
                    'session_state' => $data['session_state'], 
                    'login_code' => $data['login_code'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name']
                ]);
                $this->Login_model->dump_user('login_keycloak', $dump_data, $row['id']);
                $dump_data = json_encode([
                    'token' => $data['token']
                ]);
                $this->Login_model->dump_user('token_keycloak', $dump_data, $row['id']);

                if ($row['name'] !== $name) {
                    $data = ['name' => $name];
                    $this->DBs->GUARDIAN_DEV->set($data);
                    $this->DBs->GUARDIAN_DEV->where('email', $current_token->email);
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
     * Will return the user from self::usersTable or self::usersKEYCLOAKTable
     * 
     * @param string $email the user email
     * @param bool $strict when true will only fetch users with status = AccountStatus::Active
     * @param string $table self::usersTable or self::usersKEYCLOAKTable
     * 
     * @return array the user
     */
    public function userExists($email, $strict=true, $table="users") {
        $where = array('u1.email' => $email);
        if($strict == true) $where['u1.status'] = AccountStatus::Active;

        $useTable = ($table == $this->usersTable)? $this->usersTable:$this->usersKEYCLOAKTable;

        $this->DBs->GUARDIAN_DEV->select('*');
        $this->DBs->GUARDIAN_DEV->from($useTable . ' as u1');
        $this->DBs->GUARDIAN_DEV->where($where);
        if ($useTable !== $this->usersTable) {
            $this->DBs->GUARDIAN_DEV->join($this->usersTable . ' as u2', 'u2.id = u1.user_id', 'inner');
        }
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
        if($app_schema != 'default'){
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
     * returns the self::usersKEYCLOAKTable table
     * 
     * @return string
     */
    public function get_KEYCLOAK_table(){ return $this->usersKEYCLOAKTable; }

    /**
     * Fetches all undeleted users' ids, emails and statuses.
     * 
     * @param void
     * @return array
     */
    public function getUsersStatuses() {
        $db = $this->DBs->GUARDIAN_DEV;
        $db->select('email, id, status');
        $db->from($this->usersKEYCLOAKTable);
       // $db->where('email IS NOT NULL');
        $db->where('status !=', AccountStatus::Deleted);
       // $db->group_by('email');
        $db->order_by('id', 'DESC');
        return $db->get()->result_array();
    }

    public function registerActiveUsers(array $users) {
        $db = $this->DBs->GUARDIAN_DEV;
        return $db->insert_batch($this->usersKEYCLOAKTable, $users);
    }

    public function setUsersActiveByIds(array $ids) {
        $db = $this->DBs->GUARDIAN_DEV;
        $db->set('status', AccountStatus::Active);
        $db->where('id IN (' . implode(',', $ids) . ')');
        return $db->update($this->usersKEYCLOAKTable);
    }
}
