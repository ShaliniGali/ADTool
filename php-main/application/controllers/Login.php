<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Login extends CI_Controller
{

	private const REQUIRED_CALLBACK_CHECK_INPUT = 'required|callback_check_input';

	public function index()
	{
		$private_subnet_access_required = $this->Login_private_subnet_model->enforcePrivateSubnetLogin();

		if (is_file(APPPATH . 'first_admin_folder/FirstAdminFlag.txt')) {
			redirect('/first_admin/index');
		}

		if (!$private_subnet_access_required || $this->Login_private_subnet_model->has_access(fetch_user_ip())) {
			if (!is_null($this->session->userdata('logged_in'))) {
				redirect('Home');
			} else {
				$this->load->view('login_view');
			}
		} else {
			$this->load->view('login_private_subnet_view');
		}
	}

	public function activate($hash)
	{
		$decrypt_data = encrypted_string($hash, "decode");
		$user_info = $this->Login_model->user_info($decrypt_data['id'])[0];
		$data['account_type'] = $user_info['account_type'];
		$data['hash'] = $hash;
		$data['base_url'] = RHOMBUS_BASE_URL;
		$this->load->view('activate_view', $data);
	}

	/**
	 * Created Sai August 6th 2020
	 * Activates the registered user and updates status to 'Login_layer'
	 * @param string $hash: encrypted string
	 * @return DOM A DOM with success message
	 */
	public function activate_register()
	{
		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
		$post_data = $data_check["post_data"];
		if (is_null($post_data) || empty($post_data)) {
			return;
		}

		$decrypt_data = encrypted_string($post_data['SiteURL'], "decode");
		if (RHOMBUS_SSO_PLATFORM_ONE === 'TRUE') {
			$view = 'platform_one/activate_view';
		} else if (RHOMBUS_SSO_KEYCLOAK === 'TRUE') {
			$view = 'keycloak/activate_view';
		} else {
			$view = 'activate_view';
		}

		if (!(defined(RHOMBUS_FACS) && RHOMBUS_FACS == 'TRUE') && $post_data['AccountType'] != "USER") {
			$this->activate_register_notuser($post_data,$view,$decrypt_data);
		} else {
			$this->activate_register_user($post_data,$view);
		}
	}

	private function activate_register_user($post_data,$view){
		$result = $this->form_validation->run_rules(array(
			'EnableLoginLayer' => array('rules' => 'required'),
			'TFAGroup[gAuth]' => array('rules' => self::REQUIRED_CALLBACK_CHECK_INPUT),
			'TFAGroup[yubikey]' => array('rules' => self::REQUIRED_CALLBACK_CHECK_INPUT),
			'TFAGroup[cac]' => array('rules' => self::REQUIRED_CALLBACK_CHECK_INPUT)
		));
		if ($result !== 'success') {
			echo json_encode(array("result" => "error"));
		} else {
			$data['hash'] = $post_data['SiteURL'];
			$data['account_type'] = $post_data['AccountType'];
			$data['login_layer'] = $post_data['EnableLoginLayer'];
			if ($post_data['EnableLoginLayer'] == "Yes") {
				if ($post_data['TFAGroup']['gAuth'] == "No" && $post_data['TFAGroup']['yubikey'] == "No"
				&& $post_data['TFAGroup']['cac'] == "No") {
					echo json_encode(array("result" => "error"));
				} else {
					$data['tfa'] = $post_data['TFAGroup'];
					$data['base_url'] = RHOMBUS_BASE_URL;
					$res = $this->load->view($view, $data, TRUE);
					echo json_encode(array('result' => $res));
				}
			} else {
				$data['tfa'] = $post_data['TFAGroup'];
				$data['base_url'] = RHOMBUS_BASE_URL;
				$res = $this->load->view($view, $data, TRUE);
				echo json_encode(array('result' => $res));
			}
		}
	}

	private function activate_register_notuser($post_data,$view,$decrypt_data){
		$result = $this->form_validation->run_rules(array(
			'ExpiryDate' => array('rules' => 'required'),
			'EnableLoginLayer' => array('rules' => 'required'),
			'TFAGroup[gAuth]' => array('rules' => self::REQUIRED_CALLBACK_CHECK_INPUT),
			'TFAGroup[yubikey]' => array('rules' => self::REQUIRED_CALLBACK_CHECK_INPUT),
			'TFAGroup[cac]' => array('rules' => self::REQUIRED_CALLBACK_CHECK_INPUT)
		));
		if ($result !== 'success') {
			echo json_encode(array("result" => "error"));
		} else {
			/**
			 * gets expiry date set by super admin and pushes it to database.
			 */
			$response = $this->Register_model->insert_expiry_date($post_data['ExpiryDate'], $decrypt_data['id']);
			if ($response) {
				/**
				 * if admin do not modify account in case of moderator or admin
				 */
				if ($post_data['AccountType']) {
					$data['account_type'] = $post_data['AccountType'];
				} else {
					$data['account_type'] = $decrypt_data['account_type'];
				}
				$data['hash'] = $post_data['SiteURL'];
				$data['login_layer'] = $post_data['EnableLoginLayer'];
				$this->activate_register_notuserEnableLoginLayer($data,$post_data,$view);
			}
		}
	}

	private function activate_register_notuserEnableLoginLayer($data,$post_data,$view){
		if ($post_data['EnableLoginLayer'] == "Yes") {
			if ($post_data['TFAGroup']['gAuth'] == "No" && $post_data['TFAGroup']['yubikey'] == "No"
			&& $post_data['TFAGroup']['cac'] == "No") {
				echo json_encode(array("result" => "error"));
			} else {
				$data['tfa'] = $post_data['TFAGroup'];
				$data['base_url'] = RHOMBUS_BASE_URL;
				$res = $this->load->view($view, $data, TRUE);
				echo json_encode(array('result' => $res));
			}
		} else {
			$data['tfa'] = $post_data['TFAGroup'];
			$data['base_url'] = RHOMBUS_BASE_URL;
			$res = $this->load->view($view, $data, TRUE);
			echo json_encode(array('result' => $res));
		}
	}

	/**
	 * Validates input post
	 * created Sai August 24th 2020
	 */
	public function check_input($val)
	{
		return ($val == "Yes" || $val == "No");
	}



	//
	// Sumit, 19 September 2019
	// Updated, 26 Feb 2021
	//
	public function logout()
	{
		if (RHOMBUS_SSO_KEYCLOAK === 'TRUE') {
			$this->rhombus_keycloak->logout();
		}
		
		if (
			RHOMBUS_SSO_KEYCLOAK === 'TRUE' || 
			RHOMBUS_SSO_PLATFORM_ONE === 'TRUE'
		) {
			$this->session->unset_userdata('tiles_logged_in');
		}
		
		$this->session->unset_userdata('logged_in');
		$this->session->unset_userdata('auth_jwt');
        $this->session->sess_destroy();

		if (
			RHOMBUS_SSO_PLATFORM_ONE === 'TRUE' && 
			defined('RHOMBUS_SSO_PLATFORM_ONE_LOGOUT')
		) {
			redirect(RHOMBUS_SSO_PLATFORM_ONE_LOGOUT);
		} else {
        	$this->load->view('login_view');
		}
	}


	//
	// Sumit, 17 September 2019
	//
	public function user_check()
	{
		$valid = $this->form_validation->run_rules(array(
			'username' => array('rules' => array('required', 'valid_email')),
			'password' => array('rules' => array('required'))
		));
		if ($valid !== 'success') {
			echo json_encode(array(
				"result" => "failed",
				"message" => "Invalid input"
			));
			return;
		}

		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
		if ($data_check["result"]) {

			$post_data = $data_check["post_data"];
			$username = $post_data['username'];
			$password = $post_data['password'];
			$status = AccountStatus::Active;

			if ($post_data['tos_agreement_check'] == 'false') {
				echo json_encode(array(
					"result" => "failed",
					"message" => "You must agree to the Rhombus Power Terms of Service and Privacy Policy before
					logging in"
				));
				return;
			}


			$result = $this->Login_model->user_check($username, $password);

			/**
			 * 
			 * $result is returning three keys: layers, message, and key
			 *
			 * message = register_login_layer, account_blocked, require_google_auth, require_key, require_cac, 
			 * require_recoverycode, success, failed, account_blocked or not_registered
			 * 
			 */

			$result['result'] = $result['message'];
			if ($result['result'] == "require_login_layer"
			&& $result["layers"][LoginLayers::CAC] == LoginLayers::LayerOn) {
				setcookie('login_layer', 'true', 0, '/cac/auth', config_item('cookie_domain'),
				config_item('cookie_secure'), true);
			} else {
				setcookie('login_layer', 'false', 0, '/cac/auth', config_item('cookie_domain'),
				config_item('cookie_secure'), true);
			}

			echo json_encode($result);
		}
	}

	/**
	 * created Sai 13 July 2020
	 * 
	 * Allows user to login using recovery key
	 * 
	 * @param: string RecoveryKey: user provided recovery key
	 * @param: integer Id
	 * 
	 */
	public function login_recovery_code()
	{

		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
		$post_data = $data_check["post_data"];
		$result = $this->form_validation->run_rules(array(
			'Recovery_key' => array('rules' => 'required|alpha_numeric'),
		));
		if ($result !== 'success') {
			$result_login['message'] = "error";
		} else {
			$id = $this->Login_model->get_user_id($this->session->userdata('tfa_pending'));
			$result_login = $this->Login_model->recovery_code_login($post_data['Recovery_key'], $id);
		}
		echo json_encode(array("result" => $result_login));
	}

	/**
	 * created Sai 14 July 2020
	 * 
	 * Resets the users keys if they are expired
	 * 
	 * @param integer Id
	 * @param string  RecoveryKeys: New set of recovery keys
	 */
	public function reset_recovery_codes()
	{
		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
		$post_data = $data_check["post_data"];
		$TFA_codes = $this->Register_model->get_recovery_keys();
		$id = $this->Login_model->get_user_id($this->session->userdata('tfa_pending'));
		$result_reset = $this->Login_model->reset_recovery_code($TFA_codes, $id);
		echo json_encode(array("result" => $result_reset['message']));
	}


	/**
	 * created Sai 15 July 2020
	 * 
	 * Checks if the user has any recovery keys.
	 * 
	 * @param integer Id
	 */
	public function check_key_exist()
	{

		$data_check = $this->DB_ind_model->validate_post($this->input->post());
		$post_data = $data_check["post_data"];
		$id = $this->Login_model->get_user_id($this->session->userdata('tfa_pending'));
		$result_check = $this->Login_model->check_recovery_key($id);

		echo json_encode(array("result" => $result_check['message']));
	}


	/**
	 * created: sai July 31st 2020
	 * Validates the password and checks if the password is previously used 
	 * updates the password.
	 * Modified: Ian, Aug 20 2020, moved form validation to Generic model
	 */
	public function confirm_reset_password()
	{
		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
		$post_data = $data_check["post_data"];
		$id = $this->Login_model->get_user_id($post_data['username']);

		$result = $this->Generic->validatePassword();

		if ($result !== 'success') {
			$result['message'] = "error";
			echo json_encode(array("result" => $result['message']));
		} else {
			$result = $this->Login_model->update_password($post_data['Password'], $id);
			echo json_encode(array("result" => $result['message']));
		}
	}


	/**
	 * Created: Sai July 31st 2020
	 * redirects reset password view when user clicks on link
	 * @param: string encrypted string.
	 */
	public function activate_reset_password($hash)
	{
		$decrypt_data = encrypted_string($hash, "decode");

		if (empty($decrypt_data)) {
			redirect(base_url());
		}

		$user_info = $this->Login_model->user_info($decrypt_data['id'])[0];

		if (!AccountStatus::hasStatus($user_info['status'], AccountStatus::ResetPassword)) {
			$data = array();
			$po_icon = '<i class="fas fa-check-circle m-2 fa-4x d-block mb-4" style=" vertical-align: middle;"></i>';
			$po_msg = '
				<div class="text-muted">
					Your password has been reset. Please <a href="/" class="text-muted">Login</a>
				</div>
			';
			$data['template'] = printout_message($po_icon, $po_msg);
			$this->load->view('message_view', $data);
			return;
		}
		if (($decrypt_data['time'] + 5 * 60) < time()) {
			$data = array();
			$po_icon = '
				<i class="fas fa-times-circle m-2 fa-4x d-block mb-4 text-danger"style=" vertical-align: middle;"></i>
			';
			$po_msg = '
				<div class="text-muted">This link has expired. If you need help contact it@rhombuspower.com</div>
			';
			$data['template'] = printout_message($po_icon, $po_msg);
			$this->load->view('message_view', $data);
			return;
		}

		$data['email'] = $user_info['email'];
		$this->load->view('reset_password_view', $data);
	}


	/**
	 * Created: Sai July 31st 2020
	 * Gets confirmation from user if to reset the password
	 */
	public function send_reset_password()
	{
		$id = $this->Login_model->get_user_id($this->session->userdata('reset_password'));
		$result = $this->Login_model->send_reset_password_details($id);
		echo json_encode(array("result" => $result['message']));
	}

	/**
	 * harmless endpoint
	 * 
	 * used to maintain sso login
	 */
	public function nothing()
	{
		echo 'this endpoint exists to maintain sso login';
	}

	public function send_reset_password_by_email(){
		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
		if ($data_check["result"]) {
			$this->form_validation->set_rules('email', 'email', 'required|valid_email');
			if($this->form_validation->run() == FALSE){
				echo json_encode(array("validation" => "fail", "message" => form_error('email')));
			}else{
				$email = $data_check["post_data"]["email"];
				$id = $this->Login_model->get_user_id($email);
				if ($id == null) {
					echo json_encode(array("validation" => "fail", "message" => "Invalid user credential."));
				} else {
					$result = $this->Login_model->send_reset_password_details($id);
					echo json_encode(array("validation"=>"success", "result" => $result['message']));
				}
			}
		}
	}
}
