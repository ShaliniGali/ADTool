<?php

#[AllowDynamicProperties]
class  Login_token_model extends CI_Model {
    private $users_table = 'users';
    private $user_keys_table = 'users_keys';


    /** 
     * Created: Moheb 22nd, 2020
     * 
     * Checks if the specified username has a login token layer requirement.
     * If the google 2fa layer is required, an id for the username is returned;
     * otherwise, null is returned.
     * 
     * @param string $username
     * @return string | null
    */
    public function has_login_token_layer($username, $status = AccountStatus::LoginLayer) {
        $this->DBs->GUARDIAN_DEV->select('id');
        $this->DBs->GUARDIAN_DEV->from($this->users_table);
        // $this->DBs->GUARDIAN_DEV->where(array('email' => $username, 'status' => $status));
        $this->DBs->GUARDIAN_DEV->where(array('email' => $username));
        // $this->DBs->GUARDIAN_DEV->where("SUBSTRING(login_layers, 5, 1) = 1");
        $id = $this->DBs->GUARDIAN_DEV->get()->result_array();

        if (count($id) != 0) {
            return $id[0]['id'];
        }
        return null;
    }

    /**
     * Created: Moheb, July 22nd, 2020
     * 
     * Inserts into the users keys table a login token specified by $key for the user specified by $user_id.
     * Returns true on success;
     * 
     * @param string $user_id
     * @param string $key
     * @return bool
     */
    public function generate_login_token($user_id, $key) {
        $this->Users_keys_model->insert_json($user_id, $key, 'login_token');
        return true;
    }

    /**
     * Created: Moheb, July 22nd, 2020
     * 
     * Returns the login token from the users keys table for the user specified by $user_id.
     * 
     * @param string $user_id
     * @return string
     */
    public function get_login_token($user_id) {
        $this->DBs->GUARDIAN_DEV->select('login_token');
        $this->DBs->GUARDIAN_DEV->from($this->user_keys_table);
        $this->DBs->GUARDIAN_DEV->where(array('user_id' => $user_id));
        return $this->DBs->GUARDIAN_DEV->get()->result_array()[0]['login_token'];
    }

    /**
     * Created: Sumit, July 27 2020
     * 
     * Returns true and delete login token for the user specified by $user_id.
     * 
     * @param string $user_id
     * @return true
     */
    public function delete_login_token($user_id) {

        $this->DBs->GUARDIAN_DEV->set(array('login_token' => null));
        $this->DBs->GUARDIAN_DEV->where(array('user_id' => $user_id));
        $this->DBs->GUARDIAN_DEV->update($this->user_keys_table);
        return true;
    }
}