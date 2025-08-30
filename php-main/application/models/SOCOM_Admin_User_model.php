<?php

require_once(APPPATH.'models/SOCOM_User_Base.php');

#[AllowDynamicProperties]
class SOCOM_Admin_User_model extends SOCOM_User_Base {
    protected const TYPE = 'ADMIN';
    protected $table = 'USR_ADMIN_USERS';
    protected $historyTable = 'USR_ADMIN_USERS_HISTORY';
    protected $groups = [
        1 => 'NONE',
        2 => 'User Admin',
    ];

    public function user_can_admin() {
        return  $this->SOCOM_Users_model->user_can_admin();
    }

    public function get_user() {
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        $users =  $this->SOCOM_Users_model->get_user($user_id) ?? [];

        return $this->SOCOM_Users_model->get_admin_user($users);
    }

    public function get_users() {
        $users =  $this->SOCOM_Users_model->get_users();

        return $this->SOCOM_Users_model->get_admin_user($users);
    }

    public function activate_user(int $id, bool $auto_activate= false) {
        return $this->SOCOM_Users_model->activate_admin_user($id);
    }

    public function set_user(int $id, int|string $gid) {
        if (!is_int($gid)) {
            return false;
        }
        return $this->SOCOM_Users_model->set_admin_user($id, $gid);
    }

    public function delete_user(int $id) {
        return $this->SOCOM_Users_model->delete_admin_user($id);
    }

    public function save_user_history(int $id) {
        return $this->SOCOM_Users_model->save_admin_user_history($id);
    }
}