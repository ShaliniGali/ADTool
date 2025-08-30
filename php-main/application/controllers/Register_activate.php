<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Register_activate extends CI_Controller
{
    /**
     * Sumit, 17 March 2020
     */
    public function activate($hash)
    {
        $data['hash'] = $hash;
        $data['base_url'] = RHOMBUS_BASE_URL;
        $this->load->view('activate_register_view', $data);
    }
}
