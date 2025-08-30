<?php

    #[AllowDynamicProperties]
class  Generic extends CI_Model
    {

    /**
     * 
     * Created: Ian, Aug 19 2020
     * Note: post data key names must be: 'Password' and 'ConfirmPassword'
     * 
     */
    public function validatePassword(){
      return $this->form_validation->run_rules(array(
        'Password' => array('rules' => 'required|regex_match["(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}"]'),
        'ConfirmPassword' => array('rules' => 'required|matches[Password]'),
        'CurrentPassword'=> array('rules' => 'differs[Password]')
      ));

      return $result;
    }

    /**
     * Created: Sumit, 4 February 2020
     * Modified: lea, june 30 2020, edited to use email library
     * Modified: Moheb, July 22nd, 2020: now accepts user_id on function calls to properly
     * recognized the user id when there's no session yet.
     */
    public function send_email($email, $user_id = null, $app_schema = 'default'){
      if(($email) && (UI_EMAIL_SEND === 'TRUE' || UI_EMAIL_SEND_SMTP === 'TRUE')){
        if(UI_EMAIL_SEND_SMTP === 'TRUE'){
          $this->rb_email->rhombus_email($email);
          if($this->rb_email->status != 'success'){
            log_message('error', $this->rb_email->message);
            return $this->rb_email->message;
            //handel error here
            //if the status is not success the email wont send
          }
        } else {
          $temp_data = array(
            "server_ip"=>$_SERVER['SERVER_NAME'],
            "key"=>getenv('RB_EMAIL_API_KEY'),
            "content"=>json_encode($email)
          );
          $temp_data = http_build_query($temp_data);
          $jsonData = php_api_call('POST', '', $temp_data, getenv('RB_EMAIL_API_URL'));

          if(trim($jsonData) != 'success'){
            return $jsonData;
            //handel error here
            //if the status is not success the email wont send
          }
        }
        //
        //  Log sent email
        //
        if($this->session->has_userdata('logged_in')){
          $user_id = $this->session->userdata('logged_in')['id'];
        }
        $data = array(
                'user_id' => $user_id,
                'type' => "email_sent",
                'new_info' => json_encode($email),
                'old_info' => "",
                'timestamp' => time()
        );
        if($app_schema != 'default'){
          $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
        }
        $this->DBs->GUARDIAN_DEV->insert('users_dump', $data);
        $insert_id = $this->DBs->GUARDIAN_DEV->insert_id();
        $email['insert_id'] = $insert_id;
      }
       return $email;
    }
      
    //
    // Sumit, 24 September 2019
    //
    public function dump_users_info($info, $id = null, $app_schema = 'default'){
      if($app_schema != 'default'){
        $this->DBs->GUARDIAN_DEV = $this->DBs->getDBConnection(strtoupper($app_schema));
      }
      if ($id === null && $this->session->has_userdata('logged_in')) {
          $id = $this->session->userdata('logged_in')['id'];
      }
      $data = array(
              'user_id' => $id,
              'type' => $info['type'],
              'new_info' => $info['new_info'],
              'old_info' => (($info['old_info']=="") ? fetch_user_ip() : $info['old_info']),
              'timestamp' => time()
      );

      $this->DBs->GUARDIAN_DEV->insert('users_dump', $data);

    }

	/**
	 * Executes a curl session.
	 * Returns the result on a success, and false on a failure.
	 * 
	 * @param Array $data
	 * @return Mixed 
	 */
	public function get_curl_exec($data) {
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, getenv('RB_EMAIL_API_URL'));
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		return curl_exec($curlSession);
	}
  }
?>
