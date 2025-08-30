<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Storm extends CI_Controller {

    protected const CONTENT_TYPE_JSON = 'application/json';
    public function __construct(){
        parent::__construct();

        $this->load->model('SOCOM_Storm_model');
    }

    // --------------------------------------------------------------------

    /**
     * 
     */
    public function get_storm()
    {
        $result = ['data' => $this->SOCOM_Storm_model->get_storm()];

        $http_status = 200;

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($result));
    }
}