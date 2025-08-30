<?php

#[AllowDynamicProperties]
class  Users_keys_model extends CI_Model {
    private $user_keys_table = 'users_keys';

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Checks whether the user specified by $id already has an entry in the user keys table
     * then returns true if an entry exists; otherwise, returns false.
     * 
     * @param string $id
     * @return bool
     */

    private function user_authentication_key_exists($id) {
        $this->DBs->GUARDIAN_DEV->select('user_id');
        $this->DBs->GUARDIAN_DEV->from($this->user_keys_table);
        $this->DBs->GUARDIAN_DEV->where(array('user_id' => $id));
        $this->DBs->GUARDIAN_DEV->limit(1);
        return (count($this->DBs->GUARDIAN_DEV->get()->result_array()) != 0);
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Updates an already existing authentication keys entry in the user keys table for the given $user_id
     * for the given column specified by $col.
     * 
     * @param string id
     * @param json $json
     * @param string $col
     * @return void
     */
    private function update_existing_key($user_id, $json, $col) {
        $this->DBs->GUARDIAN_DEV->set($col, json_encode($json), FALSE);
        $this->DBs->GUARDIAN_DEV->where('user_id', $user_id);
        return $this->DBs->GUARDIAN_DEV->update($this->user_keys_table);
    }

    /**
     * Created: Moheb, July 15th, 2020
     * Modified: Moheb, July 21st, 2020: column names are now fetched from the db instead of hardcoded.
     * 
     * Inserts an authentication keys entry in the user keys table with the given $user_id.
     * 
     * @param string id
     * @param json $json
     * @param string $col
     * @return void
     */
    private function insert_authentication_keys_entry($user_id, $json, $json_col) {
        $data = array();
        $cols = $this->DBs->GUARDIAN_DEV->list_fields($this->user_keys_table);

        foreach($cols as $col) {
            if ($col == $json_col) {
                $data[$col] = $json;
            } elseif ($col == 'user_id') {
                $data[$col] = $user_id;
            } else {
                $data[$col] = NULL;
            }
        }
        return $this->DBs->GUARDIAN_DEV->insert($this->user_keys_table, $data);
    }

    /**
     * Created: Moheb, July 22nd, 2020
     * 
     * Inserts into the users keys table the entry specified by $db_row inside the
     * column specified by $col for the user specified by $user_id.
     * 
     * @param string $id
     * @param Object $db_row
     * @param string $col
     * @return void
     */
    public function insert_json($user_id, $db_row, $col) {
        if ($this->user_authentication_key_exists($user_id)) {
            return $this->update_existing_key($user_id, $db_row, $col);
        }
        return $this->insert_authentication_keys_entry($user_id, $db_row, $col);
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Returns true if the provided $key is a json key inside the column specified by $col; 
     * otherwise, returns false.
     * 
     * @param string $key
     * @param string $col
     * @return bool
     */
    private function is_valid_json_key($key, $col) {
        $selection = 'JSON_KEYS(' . $col . ') AS `keys`';
        $condition = '`' . $col . '` IS NOT NULL';
        $this->DBs->GUARDIAN_DEV->select($selection);
        $this->DBs->GUARDIAN_DEV->where($condition, NULL, FALSE);
        $this->DBs->GUARDIAN_DEV->limit(1);
        $keys = json_decode($this->DBs->GUARDIAN_DEV->get($this->user_keys_table)->result_array()[0]['keys']);
        return in_array($key, $keys);
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Returns the json value corresponding to the given $key for the user specified by $user_id,
     * inside the column specified by $col.
     * If the $key is not specified ($key = null), the entire json is returned.
     * Otherwise, returns an error message.
     * 
     * @param string $user_id
     * @param string $key
     * @param string $col
     * @return array
     */
    public function get_json_value_by_key($user_id, $col, $key = null) {
        $this->DBs->GUARDIAN_DEV->select($col);
        $this->DBs->GUARDIAN_DEV->from($this->user_keys_table);
        $this->DBs->GUARDIAN_DEV->where(array('user_id' => $user_id));
        $json = json_decode($this->DBs->GUARDIAN_DEV->get()->result_array()[0][$col], true);

        if ($key === null) {
            return $json;
        } else if ($this->is_valid_json_key($key, $col)) {
            return $json[$key];
        }
        return array('status' => 'failure', 'message' => 'Undefined json key.');
    }

    /**
     * Created: Moheb, July 15th, 2020
     * 
     * Updates a json value with a given key inside the column specified by $col
     * for the user specified by $user_id.
     * 
     * @param string $user_id
     * @param string $key
     * @param string $value
     * @param string $col
     * @return void
     */
    public function set_json_value_by_key($user_id, $key, $value, $col) {

        if ($this->is_valid_json_key($key, $col)) {
            
            $key = json_encode('$.' . $key);

            $selection = 'JSON_TYPE(JSON_EXTRACT(`' . $col . '`, ' . $key . ')) AS `type`';
            $this->DBs->GUARDIAN_DEV->select($selection);
            $this->DBs->GUARDIAN_DEV->from($this->user_keys_table);
            $this->DBs->GUARDIAN_DEV->where('user_id', $user_id);
            $type = $this->DBs->GUARDIAN_DEV->get()->result_array()[0]['type'];

            if ($type != 'INTEGER' || $type != 'DOUBLE' || $type != 'DECIMAL') {
                $value = json_encode($value);
            }
            $this->DBs->GUARDIAN_DEV->set('`'.$col.'`', 'JSON_SET(`' . $col . '`, ' . $key . ', ' . $value .')', FALSE);
            $this->DBs->GUARDIAN_DEV->where('user_id', $user_id);
            $this->DBs->GUARDIAN_DEV->update($this->user_keys_table);
        }
    }

    /**
     * @author Moheb, August 24th, 2020
     * 
     * Returns the yubi key associated with the user specified by $user_id.
     * If the user has no registered yubi key, an empty string is returned.
     * 
     * @param string $user_id
     * @return string
     */

    public function get_yubikey($user_id) {
        $this->DBs->GUARDIAN_DEV->select('yubi_key');
        $this->DBs->GUARDIAN_DEV->from($this->user_keys_table);
        $this->DBs->GUARDIAN_DEV->where('user_id', $user_id);
        $key = $this->DBs->GUARDIAN_DEV->get()->result_array()[0]['yubi_key'];
        return $key == NULL ? "" : $key;
    }

    /**
     * @author Moheb, November 23rd, 2020
     * 
     * Returns the admin expiry date in unix timestamp for the user specified
     * by the $user_id.
     * 
     * @param string $user_id
     * @return int | null
     */
    public function get_admin_expiry_date($user_id) {
        $this->DBs->GUARDIAN_DEV->select('admin_expiry');
        $this->DBs->GUARDIAN_DEV->from($this->user_keys_table);
        $this->DBs->GUARDIAN_DEV->where('user_id', $user_id);
        return $this->DBs->GUARDIAN_DEV->get()->result_array()[0]['admin_expiry'];
    }
}