<?php

#[AllowDynamicProperties]
class  Register_model extends CI_Model
{

    private $userTable = 'users';


    private function dump_user_email_activity($result_message, $user_id, $app_schema = 'default') {
        if ((UI_EMAIL_SEND === 'TRUE' || UI_EMAIL_SEND_SMTP === 'TRUE') && isset($result_message['insert_id'])) {
            if($app_schema != 'default'){
                $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
            }
            $this->DBs->GUARDIAN_DEV->where('id', $result_message['insert_id']);
            $this->DBs->GUARDIAN_DEV->update('users_dump', array('user_id' => $user_id));
        }
    }

    /** 
     * Sumit, 22 October 2019 
     * 
     * Registers user in database and sets status to 'registration_pending'
     * @param array $info: 
     * @param string name: Name of the user
     * @param string email: Email of the user
     * @param string status: status of the user
     * @param unixtimestamp time: Time when user registered
     * @param integer login_attempts: initially set to '0'
     * @param string  login_layers: initially set to '0001'(0 - In-Active, 1 - Active)
     * '0'- Google Authenticator,'0'- Yubikey,'0'-cac reader,'1'-recovery code.
     * @param Longtext Image: NULL
     * @param string saltiness
     * 
     * @return user_id from insert to self::userTable
     */
    public function user_register($info, $adminVerification = true, $app_schema = 'default', $keycloak_activate_url=null)
    {
        if($app_schema != 'default'){
            $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
        }
        $name = '';
        $status = AccountStatus::RegistrationPending;
        if(isset($info['name'])){
            $name = $info['name'];
        }
        if(!$adminVerification){
            $status = AccountStatus::Active;
        }

        $data = array(
            'name' => $name,
            'email' => $info['email'],
            'password' => $info['password'],
            'status' => $status,
            'timestamp' => time(),
            'account_type' => strtoupper(trim($info['account_type'])),
            'login_attempts' => "",
            'image' => "",
            'saltiness' => $info['saltiness'],
            'login_layers' => "00111",
        );

        $this->DBs->GUARDIAN_DEV->insert($this->userTable, $data);
        $insert_id = $this->DBs->GUARDIAN_DEV->insert_id();


        $key_data = array(
            'recovery_key' => $this->get_recovery_keys(),
            'user_id' => $insert_id
        );

        $this->DBs->GUARDIAN_DEV->insert('users_keys', $key_data);

        /**
         * Dumping user data
         * 
         */
        $dump_data = json_encode(array('used_id' => $insert_id, 'username' => $info['email']));
        $this->Login_model->dump_user('account_register', $dump_data, $insert_id, $app_schema);


        /**
         * Send an email to Admins for approval
         * 
         */
        $message = '';
        if($adminVerification){

            $admins = ADMIN_EMAILS;

            for ($i = 0; $i < count($admins); $i++) {
    
                $admin_email = $admins[$i];
                $data = array();
                $data['id'] = $insert_id;
                $data['email'] = $admin_email;
                $data['time'] = time();
                $data['account_type'] = $info['account_type'];
                $data['message'] = $info['message'];
                if (RHOMBUS_SSO_KEYCLOAK === 'TRUE') {
                    $url = $keycloak_activate_url . "rb_kc/activate/" . encrypted_string($data, "encode");
                } else if (RHOMBUS_SSO_PLATFORM_ONE === 'TRUE') {
                    $url = base_url() . "rb_p1/activate/" . encrypted_string($data, "encode");
                } else {
                    $url = base_url() . "Register_activate/activate/" . encrypted_string($data, "encode");
                }
                
                /**
                 * Updated: Sai 12, July 2020
                 * Send email to admin
                 */
                $message = "<br><br> A user has requested to register for " . RHOMBUS_PROJECT_NAME . " UI.<br><br> User-info:<br> Email: " . $info['email'] . "<br> User's name: " . $info['name'] . "<br> Account type: " . $info['account_type'] . "<br> Message: " . $info['message'] . " <br><br> Click this URL to activate this user account: <a href='" . $url . "'>Activate Account</a><br><br>";
                $result_message = $this->Generic->send_email(array(
                    'receiverEmail' => $admin_email,
                    'subject' => "Request: Account Registration",
                    'receiverName' => "",
                    'template' => 'custom',
                    'footer' => ['ipAddress' => ''],
                    'content' => [
                        ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
                        ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
                    ]
                ), null, $app_schema);
    
                $admin = $this->Login_model->get_undeleted_user($admin_email, $app_schema);
             if (isset($admin['id'])) {
                 $admin_id = $admin['id'];
                 $this->dump_user_email_activity($result_message, $admin_id, $app_schema);
             }
            }
            $message = "<br><br> Your request for a Rhombus Power account has been issued and is pending an administrator's review.<br><br> Once your account is approved, you'll be notified via email.<br><br> Thank you for your patience.";
        }else{
            $message = "<br><br> An account was issued to this email and approved automatically.<br><br> As a reminder, in order to grant a user super-admin status edit the value RB_admin_emails in your .env file";
        }



        /**
         * Updated: Sai 12, July 2020
         * Send email to Registered user
         */
        $result_message = $this->Generic->send_email(array(
            'receiverEmail' => $info['email'],
            'subject' => "Request: Account Registration",
            'receiverName' => "",
            'template' => 'custom',
            'footer' => ['ipAddress' => ''],
            'content' => [
                ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
                ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'If this is not you then please contact it@rhombuspower.com.']]],
                ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
            ]
        ), null, $app_schema);
        $this->dump_user_email_activity($result_message, $insert_id, $app_schema);
        return $insert_id;
    }


    /**
     * Created: Sai, 19 August 2020
     * Helper. Updates the account status and accountType
     * @param $id integer
     * @param $status string Active|Login_layer
     * @param $account_type string USER|ADMIN\MODERATOR
     */
    public function updateAccountStatus($id, $status, $account_type = null, $enabled_layers = null)
    {
        $row = array('timestamp' => time(), 'login_attempts' => 0, 'status' => $status);
        if ($enabled_layers) {
            $row['login_layers'] = $enabled_layers;
        }
        if ($account_type !== null) {
            $row['account_type'] = $account_type;
        }
        $this->DBs->GUARDIAN_DEV->set($row);
        $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
        $this->DBs->GUARDIAN_DEV->update('users');
    }

    private function update_account_status_in_user_activate($user_info,$data,$id,$login_layers){
        if ($data['enableLoginLayer'] == "Yes") {

            ($data['tfa']['gAuth'] == "Yes") ? $login_layers[LoginLayers::GoogleAuthenticator] = LoginLayers::LayerOn : $login_layers[LoginLayers::GoogleAuthenticator] = LoginLayers::LayerOff;
            ($data['tfa']['yubikey'] == "Yes") ? $login_layers[LoginLayers::Yubikey] = LoginLayers::LayerOn : $login_layers[LoginLayers::Yubikey] = LoginLayers::LayerOff;
            ($data['tfa']['cac'] == "Yes") ? $login_layers[LoginLayers::CAC] = LoginLayers::LayerOn : $login_layers[LoginLayers::CAC] = LoginLayers::LayerOff;

            $this->updateAccountStatus($id, AccountStatus::LoginLayer, $data['account_type'], join($login_layers));
        } else {
            $this->updateAccountStatus($id, AccountStatus::Active, $data['account_type'], join($login_layers));
        }
    }

    private function registration_pending_in_user_activate($user_info,$data,$id){
        if ($user_info[0]['status'] == AccountStatus::RegistrationPending) {

            $login_layers = str_split($user_info[0]['login_layers'], 1);

            $this->update_account_status_in_user_activate($user_info,$data,$id,$login_layers);
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

            $this->dump_user_email_activity($result_message, $id);
            return true;
        } else {
            return false;
        }
    }
    /**
     * Created: Sumit, 10 Feb 2020
     * Updated: Sumit 17 March 2020
     * 
     * Admin activates the user and changes status to 'Login_layer'.
     * Sends email to user with recovery keys stating that the account is verified.
     * 
     * @param integer $id: user if to be activated
     * @param string  $type: Type of verification(admin_verify);
     */
    public function user_activate($data, $type)
    {


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
            return $this->registration_pending_in_user_activate($user_info,$data,$id);
        }
        if ($type == "self_verify") {
            $this->updateAccountStatus($id, AccountStatus::removeStatus($user_info[0]['status'], AccountStatus::Blocked), $data['account_type']);
            return true;
        }
    }


    /**
     *  Created: Sai July, 13 2020
     *  Creates 6 Unique recovery keys each with a length of 16.
     * @return JSON keys
     */
    public function get_recovery_keys()
    {
        $randomKey = array();
        $result = array();
        for ($i = 0; $i <= 5; $i++) {
            $key = bin2hex($this->encryption->create_key(8));
            array_push($randomKey, $key);
        }
        $result = json_encode(array('Recoverykeys' => $randomKey));
        return $result;
    }



    /**
     * created Sai 11 August 2020
     * 
     * Inserts expiry date in user_keys table
     * 
     * @param string $date
     * @param integer $id
     */
    public function insert_expiry_date($date, $id)
    {
        $this->DBs->GUARDIAN_DEV->set('admin_expiry', $date);
        $this->DBs->GUARDIAN_DEV->where('user_id', $id);
        $response = $this->DBs->GUARDIAN_DEV->update('users_keys');
        /**
         * dumping admin id and expiry date
         */
        $dump_data = json_encode(array('id' => $id, 'date' => $date));
        $this->Login_model->dump_user('Admin_expiry_date_created', $dump_data, $id);

        return $response;
    }


    public function reject_register($id, $email)
    {
        $user_info = $this->Login_model->user_info($id);
        $this->DBs->GUARDIAN_DEV->select('id');
        $account_exist = $this->DBs->GUARDIAN_DEV->get('users')->result_array();
        if (count($account_exist) > 0) {

            if ($user_info[0]['status'] == AccountStatus::RegistrationPending) {
                $this->DBs->GUARDIAN_DEV->set('status', AccountStatus::Rejected);
                $this->DBs->GUARDIAN_DEV->where('id', $id);
                $response = $this->DBs->GUARDIAN_DEV->update('users');
                if ($response) {
                    /**
                     * Creating email
                     */
                    $message = "<br><br> Your request for the Rhombus " . RHOMBUS_PROJECT_NAME . " UI has been rejected. Please contact admin.<br><br>";
                    $result_message = $this->Generic->send_email(array(
                        'receiverEmail' => $email,
                        'subject' => "Account registration: Rejected",
                        'receiverName' => "",
                        'template' => 'custom',
                        'footer' => ['ipAddress' => ''],
                        'content' => [
                            ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
                            ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'If this is not you then please contact it@rhombuspower.com.']]],
                            ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
                        ]
                    ));
                    $this->dump_user_email_activity($result_message, $id);
                    /**
                     * dumping admin id and expiry date
                     */
                    $dump_data = json_encode(array('id' => $id, 'status' => 'Rejected'));
                    $this->Login_model->dump_user('Account rejected', $dump_data, $id);

                    $result['message'] = "success";
                    return $result;
                } else {
                    $result['message'] = "failure";
                    return $result;
                }
            } else {
                $result['message'] = "failure";
                return $result;
            }
        } else {
            $result['message'] = "failure";
            return $result;
        }
    }

    public function check_empty()
    {
        $this->DBs->GUARDIAN_DEV->select('id');
        $this->DBs->GUARDIAN_DEV->from($this->userTable);
        $this->DBs->GUARDIAN_DEV->limit(1);
        return $this->DBs->GUARDIAN_DEV->get()->result();
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
        $success_count = 0;
        foreach($users as $user){
            $success_user = $db->insert($this->userTable, $user);
            $insert_id = $db->insert_id();

            $key_data = array(
                'recovery_key' => $this->get_recovery_keys(),
                'user_id' => $insert_id
            );
            $success_keys = $db->insert('users_keys', $key_data);

            if($success_user && $success_keys)
                $success_count++;
        }
        return $success_count === 0 ? FALSE : $success_count;
    }
}
