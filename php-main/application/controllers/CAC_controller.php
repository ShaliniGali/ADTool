<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class CAC_controller extends CI_Controller {
    private $id;
    private $username;

    public function __construct() {
        parent::__construct();
        $this->username = $this->session->userdata('tfa_pending');
        $this->id = intval($this->Login_model->get_user_id($this->username));
    }

	public function auth() {
        $layers = $this->Login_model->user_info($this->id)[0]["login_layers"];
        if (
            RHOMBUS_ENABLE_CAC === true &&
            $this->id &&
            $this->username &&
            $layers[LoginLayers::CAC] == LoginLayers::LayerOn
        ) {
            $this->Login_model->user_login_success($this->username, 'Only_email');
            $this->Login_model->dump_user('CAC_successful_login', '', $this->id);
            redirect('/');
        } else {
            redirect('Login');
        }
    }
}