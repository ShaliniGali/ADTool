<?php

#[AllowDynamicProperties]
class  Subapps_manager_model extends CI_Model
{
    private $subapps_table = 'subapp_info';

    public function get_subapps($where=array('status'=>AccountStatus::Active)){
        $this->DBs->KEYCLOAK_TILE->select('id, name, subapps_alias, status');
        $this->DBs->KEYCLOAK_TILE->from($this->subapps_table);
        $this->DBs->KEYCLOAK_TILE->where($where);

        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function update_subapps_status($id){
        $status = array(
            'status' => AccountStatus::Deleted,
            'timestamp'=>time(),
        );
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_table, $status, 'id ='.$id);
    }

    public function insert_subapps_record($data){
        $data['status'] = AccountStatus::Active;
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->insert($this->subapps_table, $data, true);
        return $this->DBs->KEYCLOAK_TILE->insert_id();
    }

    public function update_subapps_record($data, $where=array()){
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->where($where);
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_table, $data);
    }

    public function soft_update_subapps_record($subapps_alias_id, $where=array()){

        $this->DBs->KEYCLOAK_TILE->set('subapps_alias', "JSON_ARRAY_APPEND(subapps_alias, '$', $subapps_alias_id)", FALSE);
        $this->DBs->KEYCLOAK_TILE->where('NOT JSON_CONTAINS(subapps_alias, "'.$subapps_alias_id.'")');
        $this->DBs->KEYCLOAK_TILE->where('JSON_UNQUOTE(subapps_alias) <> "null"');
        $this->DBs->KEYCLOAK_TILE->where($where);
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_table);

        $this->DBs->KEYCLOAK_TILE->set('subapps_alias', "[$subapps_alias_id]");
        $this->DBs->KEYCLOAK_TILE->where('subapps_alias', null);
        $this->DBs->KEYCLOAK_TILE->where($where);
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_table);
    }

    public function fetch_controller_names_per_subapp($subapps_alias, $where=array()){

        $this->DBs->KEYCLOAK_TILE->select('name');
        $this->DBs->KEYCLOAK_TILE->where('JSON_CONTAINS(subapps_alias, "'.$subapps_alias.'")');
        $this->DBs->KEYCLOAK_TILE->where($where);
        $this->DBs->KEYCLOAK_TILE->from($this->subapps_table);
        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function soft_remove_subapps_alias_mappings_record($subapps_alias, $where=array()){

        $this->DBs->KEYCLOAK_TILE->set('subapps_alias', 
        "(
            SELECT JSON_ARRAYAGG(CAST(value AS SIGNED))
            FROM (
                SELECT JSON_UNQUOTE(value) AS value
                FROM JSON_TABLE(
                    subapps_alias,
                    '$[*]' COLUMNS(value VARCHAR(100) PATH '$')
                ) AS jt
                WHERE value <> '".$subapps_alias."'
            ) AS subquery
        )"
        , FALSE);
        $this->DBs->KEYCLOAK_TILE->where("JSON_CONTAINS(subapps_alias, '".$subapps_alias."', '$')");
        $this->DBs->KEYCLOAK_TILE->where($where);
        $this->DBs->KEYCLOAK_TILE->update($this->subapps_table);
    }
}