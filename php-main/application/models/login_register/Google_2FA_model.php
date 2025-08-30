<?php

/** 
 * Created: Moheb, July 13th, 2020
 * 
 * Google 2FA model to update the appropriate user google 2fa keys in the database
 * based on the user's interaction with the Google 2FA feature.
*/
#[AllowDynamicProperties]
class  Google_2FA_model extends CI_Model {
    private $users_table = 'users';
    private $user_keys_table = 'users_keys';

    /** 
     * Created: Moheb July 13th, 2020
     * Modified: Moheb July 15th, 2020: Changed the function name to properly reflect its functionality.
     * Modified: Moheb September 4th, 2020: Checks if the user is neither blocked nor deleted.
     * 
     * Checks if the specified username has a google 2fa layer requirement.
     * If the google 2fa layer is required, an id for the username is returned;
     * otherwise, null is returned.
     * 
     * @param string $username
     * @return string | null
    */
    public function has_google_2fa_layer($username, $status = AccountStatus::LoginLayer) {
        $this->DBs->GUARDIAN_DEV->select('id');
        $this->DBs->GUARDIAN_DEV->from($this->users_table);
        $this->DBs->GUARDIAN_DEV->where('email', $username);
        $this->DBs->GUARDIAN_DEV->not_like('status', AccountStatus::Deleted);
        $this->DBs->GUARDIAN_DEV->not_like('status', AccountStatus::Blocked);
        $this->DBs->GUARDIAN_DEV->order_by('id', 'DESC');
        $this->DBs->GUARDIAN_DEV->limit(1);
        $id = $this->DBs->GUARDIAN_DEV->get()->result_array();

        if (count($id) != 0) {
            return $id[0]['id'];
        }
        return null;
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Adds or replaces a google 2fa key json in the user keys table.
     * A json consists of the private google 2fa key, authentication attempts and last QR code registered as follows:
     * 
     * {'keys': ..., 'attempts': ..., 'last_qr_code': ..., 'status':...}
     * 
     * If an entry for the given $user_id isn't defined yet, a new one is created, then a google 2fa json key is inserted
     * into the column corresponding to the google 2fa json key. The rest of the columns are NULL for the newly created entry.
     * 
     * If an entry for the given $user_id is defined, only the google 2fa json key corresponding column is updated
     * with the new key in the table.
     * 
     * If the username is not valid (i.e $user_id is not a registered user), null is returned.
     * 
     * @param string $user_id
     * @return bool
     */
    public function add_google_2fa_private_key($user_id) {
        $tfa_private_key = strval($this->googleauthenticator->createSecret());
        
        $google_2fa_json = array(
            'key' => strval($this->googleauthenticator->createSecret()),
            'attempts' => 0,
            'last_qr_code' => NULL,
            'status' => 'pending'
        );

        $db_row = json_encode($google_2fa_json);

        $this->Users_keys_model->insert_json($user_id, $db_row, 'google_key');
        return true;
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Increments the google 2fa json attempts value for the user specified by $user_id.
     * Also increments the total login attempts in the users table.
     * 
     * @param string $user_id
     * @return void
     */
    public function increment_attempts($user_id) {
        $this->DBs->GUARDIAN_DEV->set('google_key','JSON_SET(`google_key`, "$.attempts", CAST((JSON_EXTRACT(`google_key`, \'$.attempts\') + 1) AS UNSIGNED))', FALSE);
        $this->DBs->GUARDIAN_DEV->where('user_id', $user_id);
        $this->DBs->GUARDIAN_DEV->update($this->user_keys_table);

        $this->Login_model->update_login_attempts_by_id($user_id);
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Removed the Login_layer status for the user specified by $id and switches the first bit,
     * the google 2fa authentication layer bit, into 1 meaning it's active.
     *
     * @param string $id
     * @return void
     */
    public function remove_login_layer($id) {
        $this->DBs->GUARDIAN_DEV->select('login_layers');
        $this->DBs->GUARDIAN_DEV->from($this->users_table);
        $this->DBs->GUARDIAN_DEV->where('id = ', $id);
        $login_layers = $this->DBs->GUARDIAN_DEV->get()->result_array()[0]['login_layers'];
        $login_layers[LoginLayers::GoogleAuthenticator] = LoginLayers::LayerOn;

        $this->DBs->GUARDIAN_DEV->set('status', AccountStatus::Active);
        $this->DBs->GUARDIAN_DEV->set('login_layers', $login_layers);
        $this->DBs->GUARDIAN_DEV->where('id = ', $id);
        $this->DBs->GUARDIAN_DEV->update($this->users_table);
    }
}