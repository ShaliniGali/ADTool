<?php

#[AllowDynamicProperties]
class  Roles_manager_model extends CI_Model
{
    private $roles_table = 'user_roles';

    public function get_roles($where=array('status'=>AccountStatus::Active)){
        $this->DBs->KEYCLOAK_TILE->select('id, name, status');
        $this->DBs->KEYCLOAK_TILE->from($this->roles_table);
        $this->DBs->KEYCLOAK_TILE->where($where);

        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function update_roles_status($id){
        $status = array(
            'status' => AccountStatus::Deleted,
            'timestamp'=>time(),
        );
        $this->DBs->KEYCLOAK_TILE->update($this->roles_table, $status, 'id ='.$id);
    }

    public function insert_roles_record($data){
        $data['status'] = AccountStatus::Active;
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->insert($this->roles_table, $data, true);
    }

    public function update_roles_record($data){
        $update_id = $data["id"];
        unset($data['id']);
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->update($this->roles_table, $data, 'id ='.$update_id);
    }
}