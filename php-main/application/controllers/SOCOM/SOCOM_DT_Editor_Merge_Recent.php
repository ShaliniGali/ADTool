<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_DT_Editor_Merge_Recent extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
        $this->load->model('SOCOM_DT_Editor_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Database_Upload_model');
        $this->load->model('SOCOM_Database_Upload_Metadata_model');
        $this->load->model('SOCOM_ZBT_ISS_Upload_Lut_model');
        $this->load->model('Login_model');
    }

}