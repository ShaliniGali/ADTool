<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login_Platform_One extends CI_Controller
{
	/**
	 * Requires routes.php to contain route rb_p1/success
	 * 
	 */
	public function authenticate($skip_flag=0,$redirect_url='') {
		redirect(PLATFORM_ONE_SSO_URL . '/' . $skip_flag .(strlen($redirect_url) ? '/' . $redirect_url : ''));
	}
}