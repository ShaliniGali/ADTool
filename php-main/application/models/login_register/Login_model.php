<?php

use Aws\Result;

#[AllowDynamicProperties]
class  Login_model extends CI_Model
{
  private $table_user = 'users';
  private $max_login_attempts = 6;
  private $expiry_period_in_days = 180;
  private $account_expiry_in_seconds;
  private $reset_password_prompt_attempts = 2;

  public function __construct() {
      parent::__construct();
      $this->account_expiry_in_seconds = $this->expiry_period_in_days * 24 * 60 * 60;
  }


  public function get_max_login_attempts()
  {
    return $this->max_login_attempts;
  }
  /**
   * 
   * Created: Sumit 22 Oct 2019
   * Updated: Sumit 12 Jul 2020
   * 
   */
  public function user_login_success($user, $status)
  {
    if ($status == 'Only_email') {
      $this->DBs->GUARDIAN_DEV->select('*');
      $this->DBs->GUARDIAN_DEV->from($this->table_user);
      $this->DBs->GUARDIAN_DEV->where(array('email' => $user));
      $user = $this->DBs->GUARDIAN_DEV->get()->result_array();
      $status = AccountStatus::Active;
    }
    /**
     * 
     * Reset login attempts
     * 
     */
    $this->DBs->GUARDIAN_DEV->set(array('timestamp' => time(), 'login_attempts' => 0));
    $this->DBs->GUARDIAN_DEV->where(array('email' => $user[0]['email'], 'status' => $status));
    $this->DBs->GUARDIAN_DEV->update($this->table_user);
    if((RHOMBUS_SSO_PLATFORM_ONE=='TRUE'||RHOMBUS_SSO_KEYCLOAK=='TRUE') &&  HAS_SUBAPPS){
      $subappId = $this->session->userdata('tile_id_logged_in');
      if($subappId == null){
        $subappId = 'NOT_FOUND';
      }
      $this->DBs->GUARDIAN_DEV->select();
      $this->DBs->GUARDIAN_DEV->where('email',$user[0]['email']);
      $this->DBs->GUARDIAN_DEV->where('subapp_id',$subappId);
      $this->DBs->GUARDIAN_DEV->where('status',AccountStatus::Active);
      $this->DBs->GUARDIAN_DEV->from('users_subapp');
      $rows = $this->DBs->GUARDIAN_DEV->get()->result_array();
      if(!empty($rows)){
        $tile_account_type = $rows[0]['account_type'];
        $tile_account_name = constant('SSO_'.$rows[0]['subapp_id']);
      }
      else{
        $tile_account_type = 'NOT_FOUND';
        $tile_account_name = 'NOT_FOUND';
      }
      $this->session->set_userdata('tile_account_session',array(
        'tile_account_type' => $tile_account_type,
        'tile_account_name' => $tile_account_name,
      ));
    }

    /**
     * 
     * Add info to the sessions
     * 
     */
    $session_data = array(
      'email' => $user[0]['email'],
      'name' => $user[0]['name'],
      'account_type' => $user[0]['account_type'],
      'timestamp' => $user[0]['timestamp'],
      'profile_image' => $user[0]['image'],
      'id' => $user[0]['id']
    );
    $this->session->unset_userdata('tfa_pending');
    $this->session->set_userdata('logged_in', $session_data);
    $this->check_admin_expiry($user);
    /**
     * 
     * Add login info to the dump
     * 
     */

    $this->dump_user('last_login', fetch_user_ip());

    return true;
  }

  /**
   * Created: Moheb, July 22nd, 2020
   * 
   * Returns the user id for the given username.
   * 
   * @param string $username
   * @return string
   */
  public function get_user_id($username)
  {
    $this->DBs->GUARDIAN_DEV->select('id');
    $this->DBs->GUARDIAN_DEV->from($this->table_user);
    $this->DBs->GUARDIAN_DEV->where('email', $username);
    $this->DBs->GUARDIAN_DEV->not_like('status', AccountStatus::Deleted);
    $this->DBs->GUARDIAN_DEV->not_like('status', AccountStatus::Blocked);
    $this->DBs->GUARDIAN_DEV->order_by('id', 'DESC');
    $this->DBs->GUARDIAN_DEV->limit(1);
    $user = $this->DBs->GUARDIAN_DEV->get()->result_array();
    return empty($user) ? null : $user[0]['id'];
  }

  /**
   * Created: Moheb, July 22nd, 2020
   * 
   * Sets the account status to blocked after it has reached the maximum failed login attempts
   * for the user specified by $id.
   * If the account has reached the maximum failed attempts and is neither blocked yet nor
   * deleted, the account status is set to blocked and the function returns true.
   * If the account is already blocked, the function returns true.
   * Otherwise, the function returns the total failed login attempts so far.
   * On account blocking, an email is sent with the activity type and corresponding message.
   * 
   * @param string $id
   * @param string $type
   * @param string $message
   * @return bool | int
   */
  public function enforce_block_rules($id, $type, $message)
  {
    $this->DBs->GUARDIAN_DEV->select('login_attempts, status');
    $this->DBs->GUARDIAN_DEV->from($this->table_user);
    $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
    $result = $this->DBs->GUARDIAN_DEV->get()->result_array()[0];
    $attempts = $result['login_attempts'];
    $status = $result['status'];

    if (($attempts >= $this->get_max_login_attempts()) &&
        !AccountStatus::hasStatus($status, AccountStatus::Deleted) &&
        !AccountStatus::hasStatus($status, AccountStatus::Blocked)
    ) {
      $this->block_account($id, $status);
      $this->Login_model->dump_user($type, $message, $id);
      return true;
    } else if (AccountStatus::hasStatus($status, AccountStatus::Blocked)) {
      return true;
    }
    return $attempts;
  }


    /**
     * @author Moheb, September 5th, 2020
     * 
     * Retrieves the 'undeleted' user record for the user specified by $username.
     * If no such record is available, null is returned.
     * 
     * @param string $username
     * @return array | null
     */
    public function get_undeleted_user($username, $app_schema = 'default') {
        if($app_schema != 'default'){
          $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
        }
        $this->DBs->GUARDIAN_DEV->select('*');
        $this->DBs->GUARDIAN_DEV->where('email = ', $username);
        $this->DBs->GUARDIAN_DEV->where('status !=', AccountStatus::Deleted);
        $this->DBs->GUARDIAN_DEV->limit(1);
        $user = $this->DBs->GUARDIAN_DEV->get($this->table_user)->result_array();
        return empty($user) ? null : $user[0];
    }

/**
 * Created: Sumit: 19 November 2019
 * 
 * Updated: Sumit: 12 July 2020
 * Modified: Moheb, July 22nd, 2020: now properly dumps the blocked account's id login attempt.
 * Modified: Moheb, September 4th, 2020:
 * 1) Now increments login attempts on failed attempts
 * even if after prompting password reset.
 * 2) Cleaned up the code.
 * 
 * Validates the user in various stages before logging into the account.
 * 
 * 
 * @param string username: Email of the user
 * @param string password: Password provided by user
 * 
 * @return not_registered: user is not registered or registration is pending.
 * @return User_does_not_exist: user is not registered for google authenticator.
 * @return register_login_layer: user should register in atleast one of the four authentication systems.
 * @return account_blocked: Blocked the account when user exceeded more that '6' login attempts or account is expired.
 * @return reset_passsword: if user enters incorrect password for mare that 3 times. Confirms with the user to check if to reset the password.
 * and marks the status to reset password. 
 */
  public function user_check($username, $password) {
    $username = strtolower($username);
    // Prepare the response array.
    $result = array();
    // Get all user credentials for the 'undeleted' user specified by $username.
    $user = $this->get_undeleted_user($username);
    // If no 'undeleted' username is found,
    if (!$user) {
        $result['message'] = 'not_registered';
        $this->dump_user('unauthorized_email', $username);
        return $result;
    }

    $user_id = $user['id'];
    $login_attempts = $user['login_attempts'];
    $encrypted_password = $user['password'];
    $password_salt = $user['saltiness'];
    if (RHOMBUS_ENABLE_CAC === false) {
      $user['login_layers'][2] = 0;
    }
    $login_layers = $user['login_layers'];
    $account_status = $user['status'];
    $timestamp = $user['timestamp'];
    $dump_data = json_encode(array('username' => $username, 'attempted_password' => $password));
    
    // If login attempts are below login attempts the maximum login attempts threshhold
    if ($login_attempts < $this->max_login_attempts) {
        // If the user's status indicates a reset password request was issued, 
        // send a reset password email, send a password reset message and return.
        $this->dump_user_in_user_check($result,$user,$user_id,$username,$password,$login_attempts,$encrypted_password,$password_salt,$login_layers,$account_status,$timestamp,$dump_data);
        
    // If the maximum number of login is reached, block the account
    } else {
        $this->block_account($user_id, $account_status);
        $result['message'] = 'account_blocked';
        $this->dump_user('account_blocked_login_attempt', $username, $user_id);
        $this->Login_model->login_attempt_email($user, 'account_blocked', $user_id);
    }
    return $result;
  }

  private function dump_user_in_user_check(&$result,$user,$user_id,$username,$password,$login_attempts,$encrypted_password,$password_salt,$login_layers,$account_status,$timestamp,$dump_data){
    if (AccountStatus::hasStatus($account_status, AccountStatus::ResetPassword)) {
      // Increment the login attempts by one.
      $this->update_login_attempts_by_id($user_id);
      $this->Login_model->login_attempt_email($user, 'reset_password', $user_id);
      $this->session->set_userdata('reset_password', $username);
      $result['message'] = 'force_reset_password';
      $this->dump_user('invalid_password_login_attempt', $dump_data, $user_id);
      $this->dump_user('reset_password', $username);
    }
    // Encrypt the attempted login password and match it against the password from the users tables
    else if ($encrypted_password == $this->password_encrypt_decrypt->decrypt($password, $password_salt)) {

      $this->encrypted_password_in_user_check($result,$user,$user_id,$username,$password,$login_attempts,$encrypted_password,$password_salt,$login_layers,$account_status,$timestamp,$dump_data);
    } 
    else {
      // Increment the login attempts by one.
      $this->update_login_attempts_by_id($user_id);

      if ($login_attempts >= $this->reset_password_prompt_attempts) {
          $result['message'] = 'reset_password';
          $this->dump_user('invalid_password_login_attempt', $dump_data, $user_id);
          $this->session->set_userdata('reset_password', $username);
      } else {
          // Dump failed login attempt
          $result['message'] = 'failed';
          $this->dump_user('invalid_password_login_attempt', $dump_data, $user_id);
      }
    }
  }

  private function encrypted_password_in_user_check(&$result,$user,$user_id,$username,$password,$login_attempts,$encrypted_password,$password_salt,$login_layers,$account_status,$timestamp,$dump_data){
    $this->session->set_userdata('tfa_pending', $username);
    // Fill up the response array with the user's id, username and login layers
    $result['layers'] = str_split($login_layers);
    $result['id'] = $user_id;
    $result['user'] = $username;

    // If the user's status is rejected, send a rejection response and return.
    if ($account_status == AccountStatus::Rejected) {
        $result['message'] = 'account_rejected';
        $this->dump_user('rejected_account_login_attempt', $username, $user_id);
    }
    // If the user's status is pending registration, send a registration pending message and return.
    elseif ($account_status == AccountStatus::RegistrationPending) {
        $result['message'] = 'registration_pending_exist';
        $this->dump_user('registration_pending_login_attempt', $username, $user_id);
    }
    // If user's status is login layers, generate a google private key for the user in the users keys
    else if ($account_status == AccountStatus::LoginLayer) {
        // Generate google private key
        $this->Google_2FA_model->add_google_2fa_private_key(
            $this->Google_2FA_model->has_google_2fa_layer($username)
        );
        $result['message'] = 'register_login_layer';
        $result['layers'] = $login_layers;
    }
    // If the user's account is active
    else if ($account_status == AccountStatus::Active) {
      $this->login_attempt_in_user_check($result,$user,$user_id,$username,$password,$login_attempts,$encrypted_password,$password_salt,$login_layers,$account_status,$timestamp,$dump_data);
    }
  }

  private function login_attempt_in_user_check(&$result,$user,$user_id,$username,$password,$login_attempts,$encrypted_password,$password_salt,$login_layers,$account_status,$timestamp,$dump_data){
    if (is_array(RHOMBUS_USER_QA) && in_array($username, RHOMBUS_USER_QA, true)) {
      $result['message'] = 'success';
      $this->Login_model->user_login_success(array($user), $account_status);
      $this->session->unset_userdata('reset_password', $username);
      $this->dump_user('rhombus_user_qa_login', $username, $user_id);
      return $result;
    }

    // Block account if the account has expired.
    if ((time() - $timestamp) > $this->account_expiry_in_seconds) {
        $this->Login_model->login_attempt_email($user, 'account_expire');
        $result['message'] = 'account_blocked';
        $this->dump_user('account_expired_login_attempt', $username, $user_id);
        return $result;
    }

    // If TFA layer is active, send a require login layer.
    if (RHOMBUS_TFA_LAYER == 'TRUE') {
        $result['message'] = 'require_login_layer';
        return $result;
    }

    // Othwerwise, successfully log the user in.
    $result['message'] = 'success';
    $this->Login_model->user_login_success(array($user), $account_status);
    $this->session->unset_userdata('reset_password', $username);
  }

  /**
   * created Sai August 10th 2020
   * Checks if the loggedin user is admin or moderator,
   * checks for expiry date from users_keys table.
   * if date is expired  then markes account_type as 'USER' in users table
   * @param array $user_data
   */
  public function check_admin_expiry($user_details) {
    if ((USER_TYPE !== null) && (USER_TYPE['use-db'] === TRUE)) {
        return;
    }

    /**
     * gets super admin emails from constants file
     */
    $admins = ADMIN_EMAILS;
    $is_SuperAdmin = $this->useraccounttype->checkSuperAdmin($user_details[0]['email']);
	
    /**
     * Show account manager only to Super Admins
     */
    if (!$is_SuperAdmin) {
      if ($user_details[0]['account_type'] != $this->useraccounttype->getleastprivilege()['type']) {
        /**
         * Fetched expiry date from user_keys table
         */

        $date = $this->Users_keys_model->get_admin_expiry_date($user_details[0]['id']);
        $expiry_ = new DateTime($date);
        $current_ = new DateTime();
        /**
         * checks if admin or moderator is expired
         */
        if ($expiry_ < $current_) {
          try {
            /**
             * updates account type to 'user'
             */
            $this->DBs->GUARDIAN_DEV->set('account_type', $this->useraccounttype->getleastprivilege()['type']);
            $this->DBs->GUARDIAN_DEV->where('id', $user_details[0]['id']);
            $this->DBs->GUARDIAN_DEV->update($this->table_user);

            /**
             * dumping admin id and expiry date
             */
            $dump_data = json_encode(array('id' => $user_details[0]['id']));
            $this->Login_model->dump_user('expiring_admin_moderator', $dump_data, $user_details[0]['id']);
          } catch (Exception $e) {
            return false;
          }
          return true;
        }
      }
    }
  }

  /**
   * 
   * Created: Sumit: 12 July 2020
   * Updated: Sai 13 July 2020
   * 
   */

  private function login_attempt_email($user, $type, $user_id = "")
  {
    $data = array();
    $data['id'] = $user['id'];
    $data['email'] = $user['email'];
    $data['status'] = $user['status'];
    $data['time'] = time();

    $subject = "";
    $message = "";
    if ($type == "account_blocked") {
      $data['type'] = "self_verify";
      $url = base_url() . "login/activate/" . encrypted_string($data, "encode");
      $subject = "Login Attempt";
      $message = "<br><br> Due to suspicious activity associated with your account on the " . RHOMBUS_PROJECT_NAME . " Rhombus Power website, your account has been blocked. <br><br> You may follow this link to re-activate your account: <a href='" . $url . "'>Activate My Account</a>. Please note that this link will expire after 5 minutes.<br><br>";
    }

    if ($type == "account_login") {
      $subject  = "Login Attempt";
      $message = "<br><br> You have been logged in to the " . RHOMBUS_PROJECT_NAME . " Rhombus Power website. <br><br>";
    }

    if ($type == "account_expire") {
      $data['type'] = "self_verify";
      $url = base_url() . "login/activate/" . encrypted_string($data, "encode");
      $subject  = "Login Attempt";
      $message = "<br><br> Due to no activity in the past " . $this->expiry_period_in_days . " days on the " . RHOMBUS_PROJECT_NAME . " Rhombus Power website, your account has been deactivated. <br><br> You may follow this link to re-activate your account: <a href='" . $url . "'>Activate My Account</a><br><br>";
    }

    if ($type == "reset_password") {
      $url = base_url() . "login/activate_reset_password/".encrypted_string($data, "encode");
      $subject  = "Reset Password";
      $message = "<br><br> A password reset has been issued for your " . RHOMBUS_PROJECT_NAME . " Rhombus Power website account. <br><br> Please follow this link to reset your password. <a href='" . $url . "'>Reset My Password</a>. Please note that this link will expire after 5 minutes.<br><br>";
    }

    if ($type == "reset_password_success") {
      $url = base_url();
      $subject  = "Confirmation of password reset";
      $message = "<br><br>Your password has been successfully reset for the " . RHOMBUS_PROJECT_NAME . " Rhombus power website. <br><br> You may follow this link to login to your account. <a href='" . $url . "'>Login</a>.<br><br>";
    }
    /**
     * Send Email:
     * Parameters: ReceiverEmail, Subject, ReceiverName, TypeOfTemplate, Content.
     */
    $this->Generic->send_email(array(
      'receiverEmail' => $user['email'],
      'subject' => $subject,
      'receiverName' => $user['name'],
      'template' => 'custom',
      'footer' => ['ipAddress' => ''],
      'content' => [
        ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
        ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'If this is not you then please contact it@rhombuspower.com.']]],
        ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
      ]
    ), $user_id);
    return true;
  }

  /**
   *  Dump all information in user_dump table.
   *  @param string $type: 
   * 
   *  1.recovery_code_login: User loggedin using recovery code. stores user email
   *  2.Wrong_recovery_key: User entered wrong or expired recovery key when login. stores useremail and key used.
   *  3.last_login: user logged in for last time. stores user ip.
   *  4.account_expired: dumps user email if account is expired.
   *  5.wrong_password_login: dumps user email if wrong password is provided.
   *  6.account_blocked_login: dumps email when user tries to login into blocked account.
   *  7.unauthorized_email: dumps unauthorized email provided by user.
   *  8.recovery_code_reset: dumps after user resets recovery codes.
   *  9.registration_pending_login_attempt:dumps user email when user tries to login before activating account.
   * 
   *  @param string $new_info: info provide by user.
   * 
   */
  public function dump_user($type, $new_info, $id = null, $app_schema = 'default')
  {
    $dump = array();
    $dump['type'] = $type;
    $dump['new_info'] = $new_info;
    $dump['old_info'] = "";
    $this->Generic->dump_users_info($dump, $id, $app_schema);
  }


  /**
   * 
   * Created: Sumit: 17 March 2020
   * 
   * @param integer $id: user Id.
   * 
   * @return $result returns user data.
   */
  public function user_info($id)
  {
    $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
    $this->DBs->GUARDIAN_DEV->limit(1);
    $result = $this->DBs->GUARDIAN_DEV->get($this->table_user)->result_array();
    return $result;
  }

  public function user_info_by_email($email)
  {
    $this->DBs->GUARDIAN_DEV->where(array('email' => $email));
    $this->DBs->GUARDIAN_DEV->not_like('status', AccountStatus::Deleted);
    $this->DBs->GUARDIAN_DEV->not_like('status', AccountStatus::Blocked);
    $this->DBs->GUARDIAN_DEV->limit(1);
    $result = $this->DBs->GUARDIAN_DEV->get($this->table_user)->result_array();
    return $result;
  }

  /**
   * 
   * Created Sai July 16 2020
   * 
   * 2FA get key info
   * 
   * @param integer $id: user Id.
   * 
   * @return $result returns key data of that user
   */
  public function get_key_info($id)
  {

    $this->DBs->GUARDIAN_DEV->where(array('user_id' => $id));
    $this->DBs->GUARDIAN_DEV->limit(1);
    $result = $this->DBs->GUARDIAN_DEV->get('users_keys')->result_array();
    return $result;
  }

  public function get_account_status($id)
  {
    $this->DBs->GUARDIAN_DEV->select('status');
    $this->DBs->GUARDIAN_DEV->from($this->table_user);
    $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
    return $this->DBs->GUARDIAN_DEV->get()->result_array()[0]['status'];
  }

  public function block_account($id, $oldStatus)
  {
    if (!AccountStatus::hasStatus($oldStatus, AccountStatus::Blocked)) {
        $this->DBs->GUARDIAN_DEV->set('status', AccountStatus::appendStatus($oldStatus, AccountStatus::Blocked));
        $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
        $this->DBs->GUARDIAN_DEV->update($this->table_user);
    }
  }

  /**
   * Created: Moheb, July 16th, 2020
   * 
   * Updates the login attempts for the user specified by $id and the given $status.
   * If status is null, the user's login attempts are incremented regardless of status.
   * If reset is true, resets login attempts to 0; otherwise, increments it by 1.
   * 
   * @param string $id
   * @param bool $status | default = false
   * @param string $status | default = null
   * @return void
   */
  public function update_login_attempts_by_id($id, $reset = false, $status = null)
  {
    if ($reset) {
      $this->DBs->GUARDIAN_DEV->set('login_attempts', 0, FALSE);
    } else {
      $this->DBs->GUARDIAN_DEV->set('login_attempts', 'login_attempts+1', FALSE);
    }
    if ($status !== null) {
      $this->DBs->GUARDIAN_DEV->where(array('status' => $status));
    }
    $this->DBs->GUARDIAN_DEV->where(array('id' => $id));
    $this->DBs->GUARDIAN_DEV->update($this->table_user);
  }

  /**
   * 
   * Created Sai July 16 2020
   * 
   * 2FA Login check recovery code
   * 
   * @param integer $id: user Id.
   * 
   * 
   * @return success if key exists
   * @return failure if no keys
   */
  public function check_recovery_key($id)
  {
    $user_key_info = $this->get_key_info($id);
    $keys = json_decode($user_key_info[0]['recovery_key'], true);
    if (count($keys['Recoverykeys']) > 0) {
      $result['message'] = "success";
      return $result;
    } else {
      $result['message'] = "failure";
      return $result;
    }
  }


  private function loop_in_recovery_code_login($keys,$available_keys,$rk,$user_data,$id){
    for ($i = 0; $i <= count($keys['Recoverykeys']) - 1; $i++) {

      if ($keys['Recoverykeys'][$i] == $rk) {
        /**
         * Removing the used key from datatabase
         */
        unset($keys['Recoverykeys'][$i]);
        foreach ($keys['Recoverykeys'] as $key) :
          array_push($available_keys, $key);
        endforeach;
        $keys_left = json_encode(array('Recoverykeys' => $available_keys));
        /**
         * Updating the available keys in database
         */
        $this->DBs->GUARDIAN_DEV->set('recovery_key', $keys_left);
        $this->DBs->GUARDIAN_DEV->where('user_id', $id);
        $res = $this->DBs->GUARDIAN_DEV->update('users_keys');
        if ($res) {
          $this->Login_model->user_login_success($user_data[0]['email'], "Only_email");
          /**
           * Dump the data.
           */
          $dump_data = json_encode(array('recovery_key_used' => $rk, 'user' => $user_data[0]['email']));
          $this->dump_user('recovery_code_login', $dump_data, $id);

          $result["message"] = "success";
        } else {
          $result["message"] = "failure";
        }
        return $result;
      } else {
        /**
         * Skips if key not matches
         */
        continue;
      }
    }
    return array();
  }

  /**
   * 
   * Created Sai July 13 2020
   * 
   * 2FA Login using Recovery code
   * 
   * @param string $rk: RecoveryKey of the user.
   * @param integer $Id: Row Id in the database
   * 
   * @return: success|failure|noKeysLeft|accountBlocked
   */
  public function recovery_code_login($rk, $id)
  {
    $user_data = $this->user_info($id);
    $user_key_info = $this->get_key_info($id);
    $available_keys = array();
    $keys = json_decode($user_key_info[0]['recovery_key'], true);
    // $dump_data = '';
    if ($user_data[0]['login_attempts'] < $this->max_login_attempts) {

      $result = $this->loop_in_recovery_code_login($keys,$available_keys,$rk,$user_data,$id);
      if(!empty($result)){
        return $result;
      }
      /**
       * Updates the 'login_attempts' when wrong key is enetered
       */
      $this->DBs->GUARDIAN_DEV->set('login_attempts', 'login_attempts+1', FALSE);
      $this->DBs->GUARDIAN_DEV->where('id', $id);
      $this->DBs->GUARDIAN_DEV->update($this->table_user);
      /**
       * Dump the data.
       */
      $dump_data = json_encode(array('recovery_key_used' => $rk, 'user' => $user_data[0]['email']));

      $this->dump_user('Wrong_recovery_key', $dump_data, $id);
      $result["message"] = "failure";
      $result["login_attempts"] = $this->get_max_login_attempts() - $user_data[0]['login_attempts'];
      return $result;
    } else {
      /**
       * Blocks the account when maxiumum of 6 attemps are crossed.
       */
      $result["message"] = "account_blocked";
      $this->Login_model->login_attempt_email($user_data[0], "account_blocked");
      return $result;
    }
  }



  /**
   * 
   * Created Sai July 13 2020
   * 
   * 2FA Reset recovery keys of the user
   * 
   * @param string $keys: New  set of recovery keys.
   * @param integer $id: Row Id in the database
   * @return: success|failure
   */

  public function reset_recovery_code($keys, $id)
  {
    try {  // prevents UI from breaking if database error      
      /**
       * Sending email to user with updated keys.
       */
      $recovery_code = implode(', <br>', json_decode($keys, true)['Recoverykeys']);

      /**
       * Checking if user is registered in user_keys table.
       * if not append new user in table with keys.
       */
      $user_exist = $this->get_key_info($id);
      if ($user_exist == null) {
        $key_data = array(
          'recovery_key' => $keys,
          'user_id' => $id
        );
        $res = $this->DBs->GUARDIAN_DEV->insert('users_keys', $key_data);
      } else {
        $this->DBs->GUARDIAN_DEV->set('recovery_key', $keys);
        $this->DBs->GUARDIAN_DEV->where('user_id', $id);
        $res = $this->DBs->GUARDIAN_DEV->update('users_keys');
      }

      if ($res) {
        $user_data = $this->user_info($id);
        $message = "<br><br> Your keys have been reset. Please check the new keys<br><br>" . $recovery_code;
        $this->Generic->send_email(array(
          'receiverEmail' => $user_data[0]['email'],
          'subject' => "Keys Reset",
          'receiverName' => $user_data[0]['name'],
          'template' => 'custom',
          'footer' => ['ipAddress' => ''],
          'content' => [
            ['type' => 'row', 'row' => [['type' => 'text', 'text' => $message]]],
            ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'If this is not you then please contact it@rhombuspower.com.']]],
            ['type' => 'row', 'row' => [['type' => 'text', 'text' => 'Thanks,<br> IT Team']]]
          ]
        ));
        $result['message'] = "success";
        /**
         *  dumping user info.
         */
        $dump_data = json_encode(array('used_id' => $id, 'username' => $user_data[0]['email']));
        $this->dump_user('recovery_code_reset', $dump_data, $id);
        return $result;
      }
    } catch (Exception $e) {
      $result['message'] = "failure";
    }
    return $result;
  }


  /**
   * Created Sai July 31st 2020
   * 
   * @param integer $id id of the user.
   * 
   * Sends the reset password instructions to user via email and marks the status to 'reset_password'.
   */

  public function send_reset_password_details($id)
  {
    $user_info = $this->user_info($id);
    if (!AccountStatus::hasStatus($user_info[0]['status'], AccountStatus::ResetPassword)) {
        $this->DBs->GUARDIAN_DEV->set('status', AccountStatus::appendStatus($user_info[0]['status'], AccountStatus::ResetPassword));
        $this->DBs->GUARDIAN_DEV->where('id', $id);
        $this->DBs->GUARDIAN_DEV->update($this->table_user);
    }
    
    $this->dump_user("reset_password", $user_info[0]['email'], $id);
    $this->Login_model->login_attempt_email($user_info[0], "reset_password", $user_info[0]['id']);
    $result['message'] = "success";
    return $result;
  }


  /**
   * Created Sai July 31st 2020
   * 
   * @param string $password. New password of the user
   * @param integer $id id of the user.
   * 
   * 1. Checks if the new password is used previously.
   * 2. Encrypts the password.
   * 3. Checks for login layers
   * 4. updates the saltiness and password of the user.
   * 5. Dumps the data.
   */
  public function update_password($password, $id)
  {
    $user_info = $this->user_info($id);
    
    $new_password = $this->password_encrypt_decrypt->decrypt($password, $user_info[0]['saltiness']);
    /**
     * Checks if the newpassword matches old password.
     */
    if ($new_password == $user_info[0]['password']) {
      $result['message'] = "password_used";
      return $result;
    } else {
      /**
       * Encrypts the new password
       */
      $encode_password = $this->password_encrypt_decrypt->encrypt($password);

      $this->DBs->GUARDIAN_DEV->set(array('password' => $encode_password['password'],  'saltiness' => $encode_password['salt'], 'status' => AccountStatus::retrieveOriginalStatus($user_info[0]['status']), 'login_attempts' => 0));
      $this->DBs->GUARDIAN_DEV->where('id', $id);
      $response = $this->DBs->GUARDIAN_DEV->update($this->table_user);
      if ($response) {
        $result['message'] = "success";
        $this->session->unset_userdata('reset_password_success');
        $dump_data = json_encode(array('used_id' => $id, 'username' => $user_info[0]['email']));
        /**
         * dump user data
         */
        $this->dump_user('reset_password', $dump_data, $id);
        $this->Login_model->login_attempt_email($user_info[0], "reset_password_success", $user_info[0]['id']);
        return $result;
      }
    }
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
      $db->from($this->table_user);
      $db->where('email IS NOT NULL');
      $db->where('status !=', AccountStatus::Deleted);
      //$db->group_by('email');
      $db->order_by('id', 'DESC');
      return $db->get()->result_array();
  }

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
      return $db->update($this->table_user);
  }
}
