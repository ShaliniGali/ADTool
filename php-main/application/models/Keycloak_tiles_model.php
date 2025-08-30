<?php

    #[AllowDynamicProperties]
class  Keycloak_tiles_model extends CI_Model
    {

        private $tiles_table = 'keycloak_tiles';
        private $tiles_favourite_table = 'tile_favourite_mapping';

        public function get_tiles($params = []){
            $this->DBs->KEYCLOAK_TILE->select('id, title, icon, note, description,group,deployment,
            label,status,deployed_networks,deployed_environments');
            $this->DBs->KEYCLOAK_TILE->from($this->tiles_table);
            
            if (isset($params['status'])) {
                $this->DBs->KEYCLOAK_TILE->where('status', $params['status']);
            } else {
                $this->DBs->KEYCLOAK_TILE->where('status', AccountStatus::Active);
            }

            if (isset($params['title'])) {
                $this->DBs->KEYCLOAK_TILE->where('title', $params['title']);
            }
            
            return $this->DBs->KEYCLOAK_TILE->get()->result_array();
        }

        public function update_tile_status($id){
            $status = array(
                'status' => AccountStatus::Deleted,
                'updated_on'=>time(),
            );
            $this->DBs->KEYCLOAK_TILE->update($this->tiles_table, $status, 'id ='.$id);
        }

        public function insert_tiles_record($data){
            $data['status'] = AccountStatus::Active;
            $data['created_on'] = time();
            $this->DBs->KEYCLOAK_TILE->insert($this->tiles_table, $data, true);
        }

        public function update_tiles_record($data){
            $update_id = $data["id"];
            unset($data['id']);
            $data['updated_on'] = time();
            $this->DBs->KEYCLOAK_TILE->update($this->tiles_table, $data, 'id ='.$update_id);
        }

        public function convert_tile_data_json(){
            $tiles = $this->get_tiles();

            $res = array();
            $i=1;
            foreach($tiles as $each_t){
                $temp_arr = array();
                $temp_arr['key'] = $each_t['id'];
                $temp_arr['label'] = $each_t['label'];
                $temp_arr['icon'] = $each_t['icon'];

                if (in_array(DEPLOYMENT_ENVIRONMENT,json_decode($each_t['deployment']))
                    && getenv('SSO_'.strtoupper(preg_replace('/[&]/','',$each_t['title'])).'_SCHEMA') != '') {
                    $user_data = ($this->session->userdata('tiles_logged_in')!=NULL) ?
                        $this->session->userdata('tiles_logged_in') : $this->session->userdata('logged_in');
                    $email = $user_data['email'];
                    $curr_db = $this->DBs->getDBConnection(strtoupper(preg_replace('/[ &]/','',$each_t['title'])));
                    $curr_db->select('*');
                    $curr_db->where('email = ', $email);
                    $curr_db->where('status !=', AccountStatus::Deleted);
                    $curr_db->limit(1);
                    $user = $curr_db->get('users')->result_array();
                    if(empty($user)){
                        $temp_arr['status'] ='NOT_REGISTERED';
                    }
                    else{
                        $temp_arr['status'] = $this->get_subapp_status($user,$each_t,$user_data);
                    }
                    $temp_arr['url'] = constant('SSO_'.strtoupper(preg_replace('/[ &]/','',$each_t['title'])).'_URL');
                    $home_route = constant('SSO_'.strtoupper(preg_replace('/[ &]/','',$each_t['title'])).'_HOME');
                    $temp_arr['url'] .= RHOMBUS_SSO_PLATFORM_ONE=='TRUE'?
                    'rb_p1/authenticate/1'.$home_route. '::'. $each_t['id']:
                    'rb_kc/authenticate/1'.$home_route. '::'. $each_t['id'];
                    
                }
                else{
                    $temp_arr['status'] = json_decode($each_t['deployment']);
                }
                
                $temp_arr['group'] = $each_t['group'];
                $temp_arr['description'] = $each_t['description'];
                $temp_arr['deployed_environments'] = json_decode($each_t['deployed_environments']);
                $temp_arr['deployed_networks'] = json_decode($each_t['deployed_networks']);
                $temp_arr['favorite'] = false;
                $res[] = $temp_arr;
                $i++;
            }
            
            return $res;
        }

        public function get_subapp_status($user,$currTile,$user_data){
            $email = $user_data['email'];
            $curr_db = $this->DBs->getDBConnection(strtoupper(preg_replace('/[ &]/','',$currTile['title'])));
            $curr_db->select('*');
            $curr_db->where('email = ', $email);
            $curr_db->where('subapp_id = ', $currTile['id']);
            $curr_db->where('status !=', AccountStatus::Deleted);
            $curr_db->limit(1);
            $subapp_user = $curr_db->get('users_subapp')->result_array();

            if(empty($subapp_user)){
                return 'NOT_REGISTERED';
            }
            if($user[0]['status']=='Active' && $subapp_user[0]['status']=='Active'){
                return 'REGISTERED';
            }
            return 'PENDING';
        }

        public function registerUserOnSubApp($subapp_data, $app_schema = 'default'){
            if($app_schema != 'default'){
                $curr_db = $this->DBs->getDBConnection(strtoupper($app_schema));
            }

            $curr_db->select();
            $curr_db->from('users_subapp');
            $curr_db->where('email',$subapp_data['email']);
            $curr_db->where('subapp_id',$subapp_data['subapp_id']);
            $rows = $curr_db->get()->result_array();

            if(empty($rows)){
                $curr_db->set($subapp_data);
                $curr_db->insert('users_subapp');
            }
            else{
                $curr_db->set('status',$subapp_data['status']);
                $curr_db->set('timestamp',$subapp_data['timestamp']);
                $curr_db->set('account_type',$subapp_data['account_type']);
                $curr_db->where('email',$subapp_data['email']);
                $curr_db->where('subapp_id',$subapp_data['subapp_id']);
                $curr_db->update('users_subapp');
            }
        }

        public function get_app_users(){
            $tiles = $this->get_tiles();
            $user_tiles = array();
            foreach($tiles as $t){
                if (in_array(DEPLOYMENT_ENVIRONMENT,json_decode($t['deployment']))
                    && getenv('SSO_'.strtoupper(preg_replace('/[&]/','',$t['title'])).'_SCHEMA') != '') {
                    $curr_db = $this->DBs->getDBConnection(strtoupper(preg_replace('/[ &]/','',$t['title'])));
                    $curr_db->select('u1.email');
                    $curr_db->select('u2.status');
                    $curr_db->select('u2.account_type');
                    $curr_db->select('u2.subapp_id');
                    $curr_db->where_in('u1.status', [AccountStatus::RegistrationPending, AccountStatus::Active]);
                    $curr_db->where_in(
                        'u2.status', [AccountStatus::RegistrationPending, AccountStatus::Active]
                    );
                    $curr_db->from('users u1');
                    $curr_db->join('users_subapp u2','u1.email=u2.email');
                    $users = $curr_db->get()->result_array();
                    foreach($users as $u){
                        if($u['subapp_id']===$t['id']){
                            $user_tiles[$u['status']][$u['email']][] =
                            array('label' => $t['label'], 'account_type' => $u['account_type']);
                        }
                    }
                }
            }
            return $user_tiles;
        }

        public function save_favourites($tile_ids){

            $user_data =
            ($this->session->userdata('tiles_logged_in')!=NULL) ?
            $this->session->userdata('tiles_logged_in') : $this->session->userdata('logged_in');
            $email = $user_data['email'];

            $this->DBs->KEYCLOAK_TILE->select('email,keycloak_tiles_id');
            $this->DBs->KEYCLOAK_TILE->from($this->tiles_favourite_table);
            $this->DBs->KEYCLOAK_TILE->where('email', $email);

            $response = $this->DBs->KEYCLOAK_TILE->get()->result_array();

            
            // Update
            if(!empty($response)){
                $data = array(
                    'keycloak_tiles_id' => json_encode($tile_ids),
                    'timestamp' => time()
                );
                $this->DBs->KEYCLOAK_TILE->set($data);
                $this->DBs->KEYCLOAK_TILE->where('email', $email);
                return $this->DBs->KEYCLOAK_TILE->update($this->tiles_favourite_table);
            }
            // Add
            else{ 
                $data = array(
                    'email' => $email,
                    'keycloak_tiles_id' => json_encode($tile_ids),
                    'timestamp' => time()
                );
                $this->DBs->KEYCLOAK_TILE->set($data);
                return $this->DBs->KEYCLOAK_TILE->insert($this->tiles_favourite_table);
            }
        }

        public function getSubappIdfromName($subapp){
            $this->DBs->KEYCLOAK_TILE->select();
            $this->DBs->KEYCLOAK_TILE->from('keycloak_tiles');
            $this->DBs->KEYCLOAK_TILE->where('label',$subapp);
            $rows = $this->DBs->KEYCLOAK_TILE->get()->result_array();
            
            if(!empty($rows)){
                return $rows[0]['id'];
            }
            return false;
        }

    }
