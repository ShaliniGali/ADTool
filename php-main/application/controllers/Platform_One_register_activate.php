<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Platform_One_register_activate extends CI_Controller
{
    
    public function activate($hash)
    {
        $data['hash'] = $hash;
        $data['base_url'] = RHOMBUS_BASE_URL;

        $this->load->view('platform_one/activate_register_view', $data);
    }
}