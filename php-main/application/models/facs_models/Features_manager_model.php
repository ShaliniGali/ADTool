<?php

#[AllowDynamicProperties]
class  Features_manager_model extends CI_Model
{
    private $features_table = 'feature_info';

    public function get_features(){
        $this->DBs->KEYCLOAK_TILE->select('id, name, status');
        $this->DBs->KEYCLOAK_TILE->from($this->features_table);
        $this->DBs->KEYCLOAK_TILE->where('status', AccountStatus::Active);

        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function update_features_status($id){
        $status = array(
            'status' => AccountStatus::Deleted,
            'timestamp'=>time(),
        );
        $this->DBs->KEYCLOAK_TILE->update($this->features_table, $status, 'id ='.$id);
    }

    public function insert_features_record($data){
        $data['status'] = AccountStatus::Active;
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->insert($this->features_table, $data, true);
        return $this->DBs->KEYCLOAK_TILE->insert_id();
    }

    public function update_features_record($data){
        $update_id = $data["id"];
        unset($data['id']);
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->update($this->features_table, $data, 'id ='.$update_id);
    }
}