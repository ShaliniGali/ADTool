<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SSO_Users_registration_controller extends CI_controller {
    private $users = 'USERS';
    private $usersSSO = 'SSOUSERS';
    private $usersKeycloak = 'KEYCLOAKUSERS';
    private $usersPlatformOne = 'PLATFORMONEUSERS';

    public function __construct() {
        parent::__construct();
		
    }

    public function index() {
		if ($this->check_sso_admin_access()) {
    		echo '<script>var valid_domains = ' . json_encode(VALID_EMAIL_DOMAINS) . ';</script>';
        	echo '<script>var rhombus_email_domain = ' . json_encode(RHOMBUS_EMAIL_DOMAIN) . ';</script>';
        	$input_placeholder = VALID_EMAIL_DOMAINS;
        	if (count($input_placeholder)==1) {
				$input_placeholder[1] = $input_placeholder[0];
			}
        	array_walk($input_placeholder, function(&$value, $key) { $value = 'email'.($key+1).'@'.$value; } );
        	$input_placeholder = implode(",\n",$input_placeholder);
        	$this->load->view("sso_users_registration_view.php",array("placeholder"=>$input_placeholder));
		}    
    }

	private function check_sso_admin_access() {
        if (!$this->useraccounttype->checkSuperAdmin()) {
            $this->output->set_status_header(401);
			return false;
        }
		return true;
	}

    private function setUsersDate($type) {
        $usersData = [];
        if ($type === $this->users) {
            $usersData = $this->Login_model->getUsersStatuses();
        } else if($type === $this->usersSSO){
            $usersData = $this->SSO_model->getUsersStatuses();
        } else if($type === $this->usersKeycloak) {
            $usersData = $this->Keycloak_model->getUsersStatuses();
        } else if($type === $this->usersPlatformOne) {
            $usersData = $this->Platform_One_model->getUsersStatuses();
        }
        return $usersData;
    }

    private function updateTable($type, $account_data) {
		$emails = array_unique($account_data);
        $isRegistered = false;
        $isSetToActive = false;
        $usersToRegister = array();
        $userIDsToSetActive = array();
        $invalidEmails = array();
        $usersSetActive = array();
        $usersAlreadyExist = array();

		$usersData = $this->setUsersDate($type);

		$this->updateTableEmailHelper(
            [$usersData,  $type],
            $emails,
            $invalidEmails,
            $usersAlreadyExist,
            $usersSetActive,
            $usersToRegister,
            $userIDsToSetActive
        );
        
        if (!empty($usersToRegister)) {
            if ($type === $this->users) {
                $isRegistered = $this->Register_model->registerActiveUsers($usersToRegister);
            } else if($type === $this->usersSSO){
                $isRegistered = $this->SSO_model->registerActiveUsers($usersToRegister);
            } else if($type === $this->usersKeycloak) {
                $isRegistered = $this->Keycloak_model->registerActiveUsers($usersToRegister);
            }
        }
        if (!empty($userIDsToSetActive)) {
            if ($type === $this->users) {
                $isSetToActive = $this->Login_model->setUsersActiveByIds($userIDsToSetActive);
            } else if($type === $this->usersSSO){
                $isSetToActive = $this->SSO_model->setUsersActiveByIds($userIDsToSetActive);
            } else if($type === $this->usersKeycloak) {
                $isSetToActive = $this->Keycloak_model->setUsersActiveByIds($userIDsToSetActive);
            } else if($type === $this->usersPlatformOne) {
                $isSetToActive = $this->Platform_One_model->setUsersActiveByIds($userIDsToSetActive);
            }
        }

        $response = array(
            'result' => 'success',
            'usersAlreadyExist' => $usersAlreadyExist,
            'usersAdded' => array(),
            'failedToRegister' => array(),
            'usersUpdated' => array(),
            'failedToUpdate' => array(),
            'unauthorizedEmailDomains' => $invalidEmails
        );

        if ($isRegistered !== false) {
            $response['usersAdded'] = array_column($usersToRegister, 'email');
        } else {
            $response['failedToRegister'] = $usersToRegister;
        }

        if ($isSetToActive !== false) {
            $response['usersUpdated'] = $usersSetActive;
        } else {
            $response['failedToUpdate'] = $usersSetActive;
        }

        $this->Generic->dump_users_info(array(
            'new_info' => json_encode($response),
            'type' => 'sso_admin_generated_accounts',
            'old_info' => ''
        ));
        return $response;
    }

    private function updateTableEmailHelperMaskEmail(
        $usersData,
        &$email,
        &$usersAlreadyExist,
        &$userIDsToSetActive,
        &$usersSetActive
    ) {
        foreach ($usersData as $user) { // if found, iterate through array to find the email to start processing
            if ($user['email'] === $email) { // once found, check if it is pending registration or not
                if ($user['status'] === AccountStatus::Active) {
                    $usersAlreadyExist[] = $this->mask_email($email);
                } else {
                    $userIDsToSetActive[] = $user['id'];
                    $usersSetActive[] = $this->mask_email($email);
                }
                break;
            }
        }
    }

	private function updateTableEmailHelper(
        $usersEmailInfo,
        &$emails,
        &$invalidEmails,
        &$usersAlreadyExist,
        &$usersSetActive, 
        &$usersToRegister,
        &$userIDsToSetActive
    ) {
        [$usersData, $type] = $usersEmailInfo;
		$usersEmails = array_unique(array_column($usersData, 'email'));
		
		foreach ($emails as $email) {
			if (!isValidEmailDomain($email)) {
				$invalidEmails[] = $this->mask_email($email);
				continue;
			} // continue only if the email is valid
			if (in_array($email, $usersEmails)) { // check if input email exists in database
                $this->updateTableEmailHelperMaskEmail(
                    $usersData,
                    $email,
                    $usersAlreadyExist,
                    $userIDsToSetActive,
                    $usersSetActive
                );
			} else {
				if ($type === $this->users) {
					$this->generateActiveUser($email);
				} else if ($type === $this->usersSSO) {
					$this->generateSSOActiveUser($email);
				} else if ($type === $this->usersKeycloak) {
					$this->generateSSOActiveUser($email);
				} else if ($type === $this->usersPlatformOne) {
					$this->generateSSOActiveUser($email);
				}
				$usersToRegister[] = $this->mask_email($email);
			}
		}
	}

    public function registerSSOUsers() {
        $err_response = array("result"=> "fail", "message"=>"No user input found");
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
		if ($data_check["result"]){
            $post_data = $data_check["post_data"];
            $account_data = $post_data['accounts'];
			
            if(empty($account_data)){
				echo json_encode($err_response);
            }
			else {
				$entities = $this->updateTable($this->users, $account_data);
				$entities_sso = $this->updateTable($this->usersSSO, $account_data);
				$entities_keycloak = $this->updateTable($this->usersKeycloak, $account_data);
                $entities_platformOne = $this->updateTable($this->usersPlatformOne, $account_data);
	
				echo json_encode(array(
					'result' => "success",
					'entities' => $entities,
					'entitiesSSO' => $entities_sso,
					'entitiesKeycloak' => $entities_keycloak,
                    'entitiesPlatformOne' => $entities_platformOne
				));
			}
        } else {
            echo json_encode($err_response);
        }
    }

	private function mask_email($email, $unmasked_len = 4) {
		$masked_email = '';

		if (is_string($email)) {
			$email_array = explode('@', $email);
			$email_name = str_split($email_array[0]);
			$domain_name = str_split($email_array[1]);
			$email_unmasked_len = $domain_unmasked_len = $unmasked_len;
			
		
			if (count($email_name) <= $email_unmasked_len) {
				$email_unmasked_len = count($email_name) - 1;
			}
			if (count($domain_name) <= $domain_unmasked_len) {
				$domain_unmasked_len = count($domain_name) - 1;
			}
	
			for ($i = 0; $i < count($email_name); $i++) {
                $masked_email .= ($i <= $email_unmasked_len) ? $email_name[$i] : '#';
			}
	
			$masked_email .= '@';
	
			for ($j = 0; $j < count($domain_name); $j++) {
                $masked_email .= ($j <= $domain_unmasked_len) ? $domain_name[$j] : '#';
			}
		}

		return $masked_email;
	}

    private function generateActiveUser(&$email) {
        $encode_password = $this->password_encrypt_decrypt->encrypt(hash('sha256', time()));
        $email = array(
            'email'            => $email,
            'password'         => $encode_password['password'],
            'name'             => ucfirst(strtolower(preg_replace("/[^a-zA-Z]+/", "", strstr($email, '@', true)))),
            'status'           => AccountStatus::Active,
            'account_type'     => UserAccountType::defaultUser,
            'login_attempts'   => 0,
            'timestamp'        => time(),
            'saltiness'        => $encode_password['salt'],
            'login_layers'     => (LoginLayers::LayerOff).(LoginLayers::LayerOff).(LoginLayers::LayerOff).
                                    (LoginLayers::LayerOn).(LoginLayers::LayerOn)
        );
    }

    private function generateSSOActiveUser(&$email) {
        $email = array(
            'email'            => $email,
            'status'           => AccountStatus::Active,
            'timestamp'        => time()
        );
    }
}