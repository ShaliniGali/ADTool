<?php

#[AllowDynamicProperties]
class RBAC_Users {
    protected $params = [];

    public function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->model('SOCOM_Cap_User_model');

        if ($this->CI->session->userdata("logged_in")) {
            $this->params['USER_ID'] = (int)$this->CI->session->userdata("logged_in")["id"];
        } else {
            $this->params['USER_ID'] = 0;
        }
    }
    
    public function is_user() { 
        if ($this->get_role('auth_user') === null) {
            $is_user = $this->CI->SOCOM_Cap_User_model->is_role_user($this->params['USER_ID']);
            $this->save_role('auth_user', $is_user);
        }
        return $this->get_role('auth_user');
    }

    public function is_restricted() {
        if ($this->get_role('auth_restricted') === null) {
            $is_restricted = $this->CI->SOCOM_Cap_User_model->is_role_restricted($this->params['USER_ID']);
            $this->save_role('auth_restricted', $is_restricted);
        }
        return $this->get_role('auth_restricted');
    }

    public function is_admin() {
        if ($this->get_role('auth_admin') === null) {
            $is_admin = $this->CI->SOCOM_Cap_User_model->is_role_admin($this->params['USER_ID']);
            $this->save_role('auth_admin', $is_admin);
        }
    
        return $this->get_role('auth_admin');
    }

    public function is_guest() {
        if ($this->get_role('auth_guest') === null) {
            $is_role = false;
            if ((!$this->is_user()) && (!$this->is_restricted()) && (!$this->is_admin())) {
                $is_role = true;
            }
            $this->save_role('auth_guest', $is_role);
        }
        return $this->get_role('auth_guest');
    }

    public function reset_user() { 
        $this->CI->session->unset_userdata('auth_user');
        $this->CI->session->unset_userdata('auth_restricted');
        $this->CI->session->unset_userdata('auth_admin');
        $this->CI->session->unset_userdata('auth_guest');
        $this->CI->userauthorization->reset_state();
    }

    public function reinitialize_user() { 
        $this->is_admin();
        $this->is_user();
        $this->is_restricted();
        $this->is_guest();
    }

    public function save_role($role, $is_role) {
        if (isset($this->CI->session->userdata('logged_in')['id'])) {
            $this->CI->session->set_userdata(array($role => $is_role));
        } else {
            log_message('error', 'User has no ID');
        }
    }

    public function get_role($user_role_type) {
        return $this->CI->session->userdata($user_role_type);
    }

    public function get_user_groups() {
        return $this->CI->SOCOM_Cap_User_model->get_user()[0]['GROUP'] ?? false;
    }
}