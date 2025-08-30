<?php

#[AllowDynamicProperties]
class  Subapps_alias_manager_model extends CI_Model
{
    private $subapps_alias_table = 'subapps_alias';

    public function get_subapps_alias($where=array('status'=>AccountStatus::Active)){
        $this->DBs->KEYCLOAK_TILE->select('id, alias_name, status');
        $this->DBs->KEYCLOAK_TILE->from($this->subapps_alias_table);
        $this->DBs->KEYCLOAK_TILE->where($where);

        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function update_subapps_alias_status($id){
        $status = array(
            'status' => AccountStatus::Deleted,
            'timestamp'=>time(),
        );
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_alias_table, $status, 'id ='.$id);
    }

    public function insert_subapps_alias_record($data){
        $data['status'] = AccountStatus::Active;
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->insert($this->subapps_alias_table, $data, true);
        return $this->DBs->KEYCLOAK_TILE->insert_id();
    }

    public function update_subapps_alias_record($data){
        $update_id = $data["id"];
        unset($data['id']);
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_alias_table, $data, 'id ='.$update_id);
    }
}