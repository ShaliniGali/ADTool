<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * @group base
 */
#[AllowDynamicProperties]
class Home extends CI_Controller {
	public function index() {
		$page_data['page_title'] = "SOCOM Home";
		$page_data['page_tab'] = "SOCOM Home";
		$page_data['page_navbar'] = true;
		$page_data['page_specific_css'] = ['dashboard_block.css'];
		$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
		$is_guest = $this->rbac_users->is_guest();
		$is_restricted = $this->rbac_users->is_restricted();
		$page_data['is_guest'] = $is_guest;
        $page_data['is_restricted'] = $is_restricted;
		
		$this->load->view('templates/header_view', $page_data);
		$this->load->view('SOCOM/home_view');
		$this->load->view('templates/close_view');
	}
}
