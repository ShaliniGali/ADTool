<?php

#[AllowDynamicProperties]
class  Role_mappings_manager_model extends CI_Model
{
    private $role_mappings_table = 'role_feature_mapping';
    private $tiles_table = 'keycloak_tiles';
    private $subapps_table = 'subapp_info';
    private $features_table = 'feature_info';
    private $roles_table = 'user_roles';

    public function get_role_mappings(){
        $tileAppName = PROJECT_TILE_APP_NAME;
        if(HAS_SUBAPPS){
            $tileAccountSession = $this->session->userdata('tile_account_session');
            $tileAppName = $tileAccountSession['tile_account_name'];
        }
        
        $this->DBs->KEYCLOAK_TILE->select('rfm.id, rfm.app_id, rfm.subapp_id, rfm.feature_id, rfm.user_role_id');
        $this->DBs->KEYCLOAK_TILE->from($this->role_mappings_table.' rfm');
        $this->DBs->KEYCLOAK_TILE->join($this->tiles_table.' t', 'rfm.app_id = t.id');
        $this->DBs->KEYCLOAK_TILE->join($this->subapps_table.' s', 's.id = rfm.subapp_id ');
        $this->DBs->KEYCLOAK_TILE->join($this->features_table.' f', 'f.id = rfm.feature_id ');
        $this->DBs->KEYCLOAK_TILE->join($this->roles_table.' r', 'JSON_CONTAINS(rfm.user_role_id , CAST(r.id as JSON), "$")');
        $this->DBs->KEYCLOAK_TILE->where(array(
            't.status' => AccountStatus::Active,
            'f.status' => AccountStatus::Active,
            's.status' => AccountStatus::Active,
            'r.status' => AccountStatus::Active,
            't.title' => $tileAppName
        ));
        $this->DBs->KEYCLOAK_TILE->distinct();

        $role_mappings = $this->DBs->KEYCLOAK_TILE->get()->result_array();


        return $role_mappings;
    }

    public function get_basic_role_mappings($where=array('status'=>AccountStatus::Active)){
        $this->DBs->KEYCLOAK_TILE->select('id, app_id, subapp_id, feature_id, user_role_id');
        $this->DBs->KEYCLOAK_TILE->from($this->role_mappings_table);
        $this->DBs->KEYCLOAK_TILE->where($where);

        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function insert_role_mappings_record($data){
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->insert($this->role_mappings_table, $data, true);
    }

    public function clear_role_mappings_user_roles($id){
        $status = array(
            'user_role_id' => '[]',
            'timestamp'=>time(),
        );
        $this->DBs->KEYCLOAK_TILE->update($this->role_mappings_table, $status, 'id ='.$id);
    }

    public function update_role_mappings_record($data, $where=array()){
        $data['timestamp'] = time();
        $this->DBs->KEYCLOAK_TILE->where($where);
        $this->DBs->KEYCLOAK_TILE->update($this->role_mappings_table, $data);
    }

    public function update_role_mappings_record_by_subapp($data, $subapps_alias, $tile_id){
        $add_where = '';
        if($subapps_alias != 'all'){
            $add_where = ' AND JSON_CONTAINS(s.subapps_alias, "'.$subapps_alias.'")';
        }
        $sql = '
            UPDATE '.$this->role_mappings_table.' AS rfm 
            JOIN '.$this->subapps_table.' AS s ON rfm.subapp_id = s.id 
            SET rfm.user_role_id = "'.$data.'", rfm.timestamp='.time().'
            WHERE rfm.app_id = '.$tile_id.'
            '.$add_where.'
        ';
        $this->DBs->KEYCLOAK_TILE->query($sql);
    }

    public function append_role_id_by_subapps($role_id, $subapps_alias, $tile_id){
        $add_where = '';
        if($subapps_alias != 'all'){
            $add_where = ' AND JSON_CONTAINS(s.subapps_alias, "'.$subapps_alias.'")';
        }

        $sql = '
            UPDATE '.$this->role_mappings_table.' AS rfm 
            JOIN '.$this->subapps_table.' AS s ON rfm.subapp_id = s.id 
            SET rfm.user_role_id = JSON_ARRAY_APPEND(rfm.user_role_id, "$", '.$role_id.')
            WHERE rfm.app_id = '.$tile_id.'
            AND NOT JSON_CONTAINS(rfm.user_role_id, "'.$role_id.'")
            AND JSON_UNQUOTE(rfm.user_role_id) <> "null"
            '.$add_where.'
        ';
        $this->DBs->KEYCLOAK_TILE->query($sql);

        $sql = '
            UPDATE '.$this->role_mappings_table.' AS rfm 
            JOIN '.$this->subapps_table.' AS s ON rfm.subapp_id = s.id 
            SET rfm.user_role_id = "['.$role_id.']"
            WHERE rfm.app_id = '.$tile_id.'
            AND rfm.user_role_id IS NULL
            '.$add_where.'
        ';
        $this->DBs->KEYCLOAK_TILE->query($sql);
    }

    public function fetch_subapp_alias_per_role($role_id, $tile_id){

        $this->DBs->KEYCLOAK_TILE->select('s.subapps_alias');
        $this->DBs->KEYCLOAK_TILE->from($this->role_mappings_table.' rfm');
        $this->DBs->KEYCLOAK_TILE->join($this->subapps_table.' s', 'rfm.subapp_id = s.id');
        $this->DBs->KEYCLOAK_TILE->where('JSON_CONTAINS(rfm.user_role_id, "'.$role_id.'")');
        $this->DBs->KEYCLOAK_TILE->where('rfm.app_id', $tile_id);
        return $this->DBs->KEYCLOAK_TILE->get()->result_array();
    }

    public function remove_role_id_by_subapp($role_id, $subapp, $tile_id){
        $sql = '
            UPDATE '.$this->role_mappings_table.' AS rfm 
            JOIN '.$this->subapps_table.' AS s ON rfm.subapp_id = s.id 
            SET rfm.user_role_id = (
                SELECT JSON_ARRAYAGG(CAST(value AS SIGNED))
                FROM (
                    SELECT JSON_UNQUOTE(value) AS value
                    FROM JSON_TABLE(
                    rfm.user_role_id,
                    "$[*]" COLUMNS(value VARCHAR(100) PATH "$")
                    ) AS jt
                    WHERE value <> "'.$role_id.'"
                ) AS subquery
            )
            WHERE rfm.app_id = '.$tile_id.'
            AND JSON_CONTAINS(rfm.user_role_id, "'.$role_id.'", "$")
            AND JSON_CONTAINS(s.subapps_alias, "'.$subapp.'", "$")
        ';
        $this->DBs->KEYCLOAK_TILE->query($sql);
    }
}