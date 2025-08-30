<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login_keycloak extends CI_Controller
{
	/**
	 * Requires routes.php to contain route keycloak/success
	 * 
	 */
	public function authenticate($skip_flag=0,$redirect_url='') {
		redirect($this->rhombus_keycloak->get_authenticate_url($skip_flag,$redirect_url));
	}
}