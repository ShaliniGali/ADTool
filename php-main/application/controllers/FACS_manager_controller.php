<?php
defined('BASEPATH') || exit('No direct script access allowed');

class FACS_manager_controller extends CI_Controller
{
    protected const ADD_DATA_NAME = 'addData[name]';
    protected const EDIT_DATA_NAME = 'editData[name]';

    public function index()
    {
        /**
         * Show account manager only to Super Admins
         */
        $is_SuperAdmin = $this->useraccounttype->checkSuperAdmin();
        /**
         * If not super admin redirect to home page.
         */
        if ($is_SuperAdmin) {
            $roles = $this->Roles_manager_model->get_roles();

            $this->load->view('facs_manager_view', array('roles'=>$roles));
        } else {
            $this->output->set_status_header(401);
        }
    }

    public function delete_facs(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if($data_check["result"]){ 
            $post_data = $data_check["post_data"];
            $return_data ='';
            
            $misc = null;
            switch($post_data['facs_type']){
                case 'roles':
                    $this->Roles_manager_model->update_roles_status($post_data["rowId"]);
                    $return_data = $this->Roles_manager_model->get_roles();
                    break;
                case 'subapps_alias':
                    $this->Subapps_alias_manager_model->update_subapps_alias_status($post_data["rowId"]);
                    $return_data = $this->Subapps_alias_manager_model->get_subapps_alias();
                    break;
                case 'subapps':
                    $this->Subapps_manager_model->update_subapps_status($post_data["rowId"]);
                    $return_data = $this->Subapps_manager_model->get_subapps();
                    $misc = $this->create_subapps_dropdown_values($return_data);
                    break;
                case 'features':
                    $this->Features_manager_model->update_features_status($post_data["rowId"]);
                    $return_data = $this->Features_manager_model->get_features();
                    $misc = $this->create_features_dropdown_values($return_data);
                    break;
                case 'role_mappings':
                    $this->Role_mappings_manager_model->clear_role_mappings_user_roles($post_data["rowId"]);
                    $return_data = $this->Role_mappings_manager_model->get_role_mappings();
                    break;
                default:
                    break;
            }

            // send data to client
            echo json_encode(array("result"=>$return_data, "misc"=>$misc));
        }
    }

    public function add_facs(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if($data_check["result"]){ 
            $post_data = $data_check["post_data"];

            // validate data
            switch($post_data['facs_type']){
                case 'roles':
                case 'subapps':
                case 'features':
                    $this->form_validation->set_rules(self::ADD_DATA_NAME, 'name', 'required');
                    break;
                case 'subapps_alias':
                    $this->form_validation->set_rules('addData[alias_name]', 'name', 'required');
                    break;
                case 'role_mappings':
                    $this->form_validation->set_rules('addData[app_id]', 'app', 'required');
                    $this->form_validation->set_rules('addData[subapp_id]', 'subapp', 'required');
                    $this->form_validation->set_rules('addData[feature_id]', 'feature', 'required');
                    $this->form_validation->set_rules('addData[user_role_id]', 'user roles', 'required');
                    break;
                default:
                    break;
            }

            if($this->form_validation->run() == FALSE){
                $errors = '';
                switch($post_data['facs_type']){
                    case 'roles':
                        $errors = array("roles_error_name"=>form_error(self::ADD_DATA_NAME));
                        break;
                    case 'subapps_alias':
                        $errors = array("subapps_alias_error_alias_name"=>form_error('addData[alias_name]'));
                        break;
                    case 'subapps':
                        $errors = array("subapps_error_name"=>form_error(self::ADD_DATA_NAME));
                        break;
                    case 'features':
                        $errors = array("features_error_name"=>form_error(self::ADD_DATA_NAME));
                        break;
                    case 'role_mappings':
                        $errors = array("role_mappings_error_user_roles"=>form_error('addData[user_role_id]'));
                        break;
                    default:
                        break;
                }

                echo json_encode(array("validation" => "fail", "errors" => $errors));
            }else{
                $addData = $post_data["addData"];
                unset($addData["id"]);

                $misc = null;
                switch($post_data['facs_type']){
                    case 'roles':
                        $this->Roles_manager_model->insert_roles_record($addData);
                        $return_data = $this->Roles_manager_model->get_roles();
                        break;
                    case 'subapps_alias':
                        $this->Subapps_alias_manager_model->insert_subapps_alias_record($addData);
                        $return_data = $this->Subapps_alias_manager_model->get_subapps_alias();
                        break;
                    case 'subapps':
                        $this->Subapps_manager_model->insert_subapps_record($addData);
                        $return_data = $this->Subapps_manager_model->get_subapps();
                        $misc = $this->create_subapps_dropdown_values($return_data);
                        break;
                    case 'features':
                        $this->Features_manager_model->insert_features_record($addData);
                        $return_data = $this->Features_manager_model->get_features();
                        $misc = $this->create_features_dropdown_values($return_data);
                        break;
                    case 'role_mappings':
                        $this->Role_mappings_manager_model->insert_role_mappings_record($addData);
                        $return_data = $this->Role_mappings_manager_model->get_role_mappings();
                        break;
                    default:
                        break;
                }

                //reload table
                echo json_encode(array("validation"=> "success", "result"=> $return_data, "misc"=> $misc));
            }

        }
    }

    public function edit_facs(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if($data_check["result"]){ 
            $post_data = $data_check["post_data"];

            // validate data
            switch($post_data['facs_type']){
                case 'roles':
                case 'subapps':
                case 'features':
                    $this->form_validation->set_rules(self::EDIT_DATA_NAME, 'name', 'required');
                    break;
                case 'apps':
                    $this->form_validation->set_rules('editData[label]', 'name', 'required');
                    break;
                case 'subapps_alias':
                    $this->form_validation->set_rules('editData[alias_name]', 'name', 'required');
                    break;
                case 'role_mappings':
                    $this->form_validation->set_rules('editData[app_id]', 'app', 'required');
                    $this->form_validation->set_rules('editData[subapp_id]', 'subapp', 'required');
                    $this->form_validation->set_rules('editData[feature_id]', 'feature', 'required');
                    $this->form_validation->set_rules('editData[user_role_id]', 'user roles', 'required');
                    break;
                default:
                    break;
            }

            if($this->form_validation->run() == FALSE){
                $errors = '';
                switch($post_data['facs_type']){
                    case 'roles':
                        $errors = array("roles_error_name"=>form_error(self::EDIT_DATA_NAME));
                        break;
                    case 'apps':
                        $errors = array("apps_error_label"=>form_error('editData[label]'));
                        break;
                    case 'subapps':
                        $errors = array("subapps_error_name"=>form_error(self::EDIT_DATA_NAME));
                        break;
                    case 'subapps_alias':
                        $errors = array("subapps_alias_error_alias_name"=>form_error('editData[alias_name]'));
                        break;
                    case 'features':
                        $errors = array("features_error_name"=>form_error(self::EDIT_DATA_NAME));
                        break;
                    case 'role_mappings':
                        $errors = array("role_mappings_error_user_roles"=>form_error('editData[user_role_id]'));
                        break;
                    default:
                        break;
                }
                echo json_encode(array("validation" => "fail", "errors" => $errors));
            }else{
                $misc = null;
                // update database
                switch($post_data['facs_type']){
                    case 'roles':
                        $this->Roles_manager_model->update_roles_record($post_data["editData"]);
                        $return_data = $this->Roles_manager_model->get_roles();
                        break;
                    case 'apps':
                        $this->Keycloak_tiles_model->update_tiles_record($post_data["editData"]);
                        $return_data = $this->Keycloak_tiles_model->get_tiles();
                        break;
                    case 'subapps':
                        $where = array('id'=>$post_data["editData"]['id']);
                        unset($post_data["editData"]['id']);
                        $this->Subapps_manager_model->update_subapps_record($post_data["editData"], $where);
                        $temp_subapps_data = $this->get_subapps_mapping_data();
                        $return_data = $temp_subapps_data['result'];
                        $misc = array(
                            'controller_list'=>$temp_subapps_data['controller_list'],
                            'subapps_alias_list'=>$temp_subapps_data['subapps_alias_list']
                        );
                        break;
                    case 'subapps_alias':
                        $this->Subapps_alias_manager_model->update_subapps_alias_record($post_data["editData"]);
                        $return_data = $this->Subapps_alias_manager_model->get_subapps_alias();
                        break;
                    case 'features':
                        $this->Features_manager_model->update_features_record($post_data["editData"]);
                        $return_data = $this->Features_manager_model->get_features();
                        $misc = $this->create_features_dropdown_values($return_data);
                        break;
                    case 'role_mappings':
                        $where = array('id'=>$post_data["editData"]['id']);
                        unset($post_data["editData"]['id']);
                        $this->Role_mappings_manager_model->update_role_mappings_record($post_data["editData"], $where);
                        $temp_role_mappings_data = $this->role_mappings_data();
                        $return_data = $temp_role_mappings_data['result'];
                        $misc = array(
                            "app_list" => $temp_role_mappings_data["app_list"],
                            "subapp_list" => $temp_role_mappings_data["subapp_list"],
                            "feature_list" => $temp_role_mappings_data["feature_list"],
                            "roles_list" => $temp_role_mappings_data["roles_list"],
                            "subapps_alias_list" => $temp_role_mappings_data["subapps_alias_list"],
                            "subapps_mapping" => $temp_role_mappings_data["subapps_mapping"]
                        );
                        break;
                    default:
                        break;
                }

                //reload page
                echo json_encode(array("validation"=> "success", "result"=> $return_data, "misc"=>$misc));
            }
        }
    }

    public function get_facs(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts

        if($data_check["result"]){ 
            $post_data = $data_check["post_data"];

            switch($post_data['facs_type']){
                case 'apps':
                    echo json_encode(array('result' => $this->Keycloak_tiles_model->get_tiles()));
                    break;
                case 'roles':
                    echo json_encode(array('result' => $this->Roles_manager_model->get_roles()));
                    break;
                case 'subapps_alias':
                    $result = array(
                        'result' => $this->Subapps_alias_manager_model->get_subapps_alias(),
                    );
                    echo json_encode($result);
                    break;
                case 'subapps':
                    echo json_encode($this->get_subapps_mapping_data());
                    break;
                case 'features':
                    $result = array(
                        'result' => $this->Features_manager_model->get_features(),
                        'function_list' => array()
                    );
                    $result['function_list'] = $this->create_features_dropdown_values($result['result']);
                    echo json_encode($result);
                    break;
                case 'role_mappings':
                    echo json_encode($this->role_mappings_data());
                    break;
                default:
                    break;
            }
        }
    }

    private function get_controller_list(){
        $this->load->library('ControllerList');
        $controllers = $this->controllerlist->getControllers();
        return $controllers;
    }

    private function create_subapps_dropdown_values($query_list){
        if(empty($query_list)){
            return array();
        }

        $merged_list = array_merge_recursive(...$query_list);
        $controller_list = array_keys($this->get_controller_list());
        $result = array();

        foreach($controller_list as $values){
            if(!in_array($values, $merged_list['name'])) {
                $result[] = [$values, $values];
            }
        }
        return $result;
    }

    private function create_features_dropdown_values($query_list){
        if(empty($query_list)){
            return array();
        }

        $merged_list = array_merge_recursive(...$query_list);
        $controller_list = $this->get_controller_list();
        $result = array();

        foreach($controller_list as $controller => $functions){
            $group_values = array();
            foreach($functions as $func){
                if(!in_array($func, $merged_list['name'])) {
                    $group_values[] = array('id'=>$func, 'text'=>$func);
                }
            }
            if($group_values){
                $group = array(
                    "text" => $controller,
                    "children" => $group_values
                );
                array_push($result, $group);
            }
        }
        return $result;
    }

    private function role_mappings_data(){
        $tileAppName = PROJECT_TILE_APP_NAME;
        if(HAS_SUBAPPS){
            $tileAccountSession = $this->session->userdata('tile_account_session');
            $tileAppName = $tileAccountSession['tile_account_name'];
        }

        $apps = $this->Keycloak_tiles_model->get_tiles([
            'status' => AccountStatus::Active,
            'title' => $tileAppName
        ]);
        $roles = $this->Roles_manager_model->get_roles();
        $subapps = $this->Subapps_manager_model->get_subapps();
        $features = $this->Features_manager_model->get_features();
        $subapps_alias = $this->Subapps_alias_manager_model->get_subapps_alias();
        $result = array(
            "result" => $this->Role_mappings_manager_model->get_role_mappings(),
            "app_list" => array(),
            "subapp_list" => array(),
            "feature_list" => array(),
            "roles_list" => array(),
            "subapps_alias_list" => array(),
            "subapps_mapping" => array()
        );

        foreach($apps as $values){
            $result['app_list'][] = [$values["id"], $values["label"]];
        }
        foreach($subapps as $values){
            $result['subapp_list'][] = [$values["id"], $values["name"]];
            $result['subapps_mapping'][$values["id"]] = $values['subapps_alias'];
        }
        foreach($features as $values){
            $result['feature_list'][] = [$values["id"], $values["name"]];
        }
        foreach($roles as $values){
            $result['roles_list'][] = [$values["id"], $values["name"]];
        }
        foreach($subapps_alias as $values){
            $result['subapps_alias_list'][] = [$values["id"], $values["alias_name"]];
        }
        return $result;
    }

    private function get_subapps_mapping_data(){
        $subapps_alias = $this->Subapps_alias_manager_model->get_subapps_alias();
        $result = array(
            'result' => $this->Subapps_manager_model->get_subapps(),
            'controller_list' => array(),
            'subapps_alias_list' =>array()
        );

        foreach($subapps_alias as $values){
            $result['subapps_alias_list'][] = [$values["id"], $values["alias_name"]];
        }
        foreach($result['result'] as $values){
            $result['controller_list'][] = [$values["name"], $values["name"]];
        }
        return $result;
    }

    public function auto_populate_facs_tables(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if($data_check["result"]){ 
            $post_data = $data_check["post_data"];

            $tileAppName = PROJECT_TILE_APP_NAME;
            if(HAS_SUBAPPS){
                $tileAccountSession = $this->session->userdata('tile_account_session');
                $tileAppName = $tileAccountSession['tile_account_name'];
            }

            $tile_id = $this->Keycloak_tiles_model->get_tiles([
                'status' => AccountStatus::Active, 
                'title' => $tileAppName
            ])[0]['id'];
            $role_id = $post_data['user_roles'];
            $subapps = $this->Subapps_manager_model->get_subapps();
            $db_active_controllers = empty($subapps) ? $subapps : array_merge_recursive(...$subapps);
            $features = $this->Features_manager_model->get_features();
            $db_active_functions = empty($features) ? $features : array_merge_recursive(...$features);
            $project_controllers_and_functions = $this->get_controller_list();
            $created_functions = array();
            $subapp_proposal = $post_data['subapps'];
            $mapping_pattern = $post_data['mapping_type'];

            if($mapping_pattern == 'single_role_multiple_subapp'){
                $temp_id = json_decode($role_id, true);

                if(is_array($temp_id) && count($temp_id) == 1){
                    $temp_role_id = $temp_id[0];

                    if($subapp_proposal[0] == 'all'){
                        $this->Role_mappings_manager_model->append_role_id_by_subapps($temp_role_id, $subapp_proposal[0], $tile_id);
                    } else {
                        // remove role_id from existing records with role_id that are not in proposed subapps
                        $temp_subapp_ids = $this->Role_mappings_manager_model->fetch_subapp_alias_per_role(
                            $temp_role_id,
                            $tile_id
                        );
                        $temp_subapp_id_list = empty($temp_subapp_ids) ? $temp_subapp_ids : array_merge_recursive(...$temp_subapp_ids);
                        if(!empty($temp_subapp_id_list)){
                            if(is_array($temp_subapp_id_list['subapps_alias'])){
                                $unique_subapps = array_unique($temp_subapp_id_list['subapps_alias']);
                            } else {
                                $unique_subapps = array($temp_subapp_id_list['subapps_alias']);
                            }
                            $temp_unique_subapps = [];
                            foreach($unique_subapps as $val){
                                $temp_unique_subapps[] = json_decode($val);
                            }
                            $unique_subapps_alias = empty($temp_unique_subapps) ? $temp_unique_subapps : array_merge_recursive(...$temp_unique_subapps);
                            $subapp_ids_removed = array_diff($unique_subapps_alias, $subapp_proposal);
                            foreach($subapp_ids_removed as $id){
                                $this->Role_mappings_manager_model->remove_role_id_by_subapp(
                                    $temp_role_id, 
                                    $id,
                                    $tile_id
                                );
                            }
                        }

                        // add role_id to all proposed subapps
                        foreach($subapp_proposal as $subapp_id){
                            $this->Role_mappings_manager_model->append_role_id_by_subapps($temp_role_id, $subapp_id, $tile_id);
                        }
                    }
                }
            } else {
                // regular overwrite and when $mapping_pattern == 'single_subapp_multiple_role'
                if($subapp_proposal[0] == 'all'){
                    $this->Role_mappings_manager_model->update_role_mappings_record_by_subapp(
                        $role_id, 
                        $subapp_proposal[0],
                        $tile_id
                    );
                } else {
                    foreach($subapp_proposal as $subapp_id){
                        $this->Role_mappings_manager_model->update_role_mappings_record_by_subapp(
                            $role_id, 
                            $subapp_id,
                            $tile_id
                        );
                    }
                }
            }

            foreach($project_controllers_and_functions as $c => $f_list){
                // get id of controller, else, insert and get id
                $search_key_c =
                empty($db_active_controllers) ? false : array_search($c, $db_active_controllers['name']);
                $c_id = $search_key_c !== false ? 
                    $db_active_controllers['id'][$search_key_c] : 
                    $this->Subapps_manager_model->insert_subapps_record(['name'=>$c]);

                foreach($f_list as $f){
                    // get id of function, else, insert and get id
                    $search_key_f =
                    empty($db_active_functions) ? false : array_search($f, $db_active_functions['name']);
                    if($search_key_f !== false){
                        $f_id = $db_active_functions['id'][$search_key_f];
                    } elseif ((!empty($created_functions)) && (array_search($f, $created_functions) !== false)){
                        $f_id = array_search($f, $created_functions);
                    } else{
                        $f_id = $this->Features_manager_model->insert_features_record(['name'=>$f]);
                        $created_functions[$f_id] = $f;
                    }
                    // check if rolemapping exists, if not, create with controller id and function id and app id
                    $role_mapping = $this->Role_mappings_manager_model->get_basic_role_mappings([
                        'app_id' => $tile_id,
                        'subapp_id' => $c_id,
                        'feature_id' => $f_id
                    ]);
                    if(!$role_mapping) {
                        $this->Role_mappings_manager_model->insert_role_mappings_record([
                            'app_id' => $tile_id,
                            'subapp_id' => $c_id,
                            'feature_id' => $f_id,
                            'user_role_id' => $role_id
                        ]);
                    }
                }
            }

            echo json_encode($this->role_mappings_data());
        }
    }

    public function subapps_mapping(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if($data_check["result"]){ 
            $post_data = $data_check["post_data"];
            $controllers = $post_data['controllers'];
            $subapps = $post_data['subapps'];
            $mapping_pattern = $post_data['mapping_type'];

            if($mapping_pattern == 'single_subapp_multiple_controllers'){
                $temp_id = json_decode($subapps, true);

                if(is_array($temp_id) && count($temp_id) == 1){
                    $temp_subapp_id = $temp_id[0];

                    if($controllers[0] == 'all'){
                        $this->Subapps_manager_model->soft_update_subapps_record(
                            $temp_subapp_id, 
                            array('status'=>AccountStatus::Active)
                        );
                    } else {
                        // remove subapp from existing controller records with subapp that are not in controllers
                        $temp_controller_names = $this->Subapps_manager_model->fetch_controller_names_per_subapp(
                            $temp_subapp_id,
                            array('status'=>AccountStatus::Active)
                        );
                        $temp_controller_name_list = empty($temp_controller_names) ? $temp_controller_names : array_merge_recursive(...$temp_controller_names);
                        if(!empty($temp_controller_name_list)){
                            if(is_array($temp_controller_name_list['name'])){
                                $unique_controllers = array_unique($temp_controller_name_list['name']);
                            } else {
                                $unique_controllers = array($temp_controller_name_list['name']);
                            }
                            $controller_names_removed = array_diff($unique_controllers, $controllers);
                            foreach($controller_names_removed as $name){
                                $this->Subapps_manager_model->soft_remove_subapps_alias_mappings_record(
                                    $temp_subapp_id, 
                                    array('status'=>AccountStatus::Active, 'name'=>$name)
                                );
                            }
                        }

                        // add subapp to all controllers
                        foreach($controllers as $name){
                            $this->Subapps_manager_model->soft_update_subapps_record(
                                $temp_subapp_id,
                                array('status'=>AccountStatus::Active, "name"=>$name)
                            );
                        }
                    }
                }
            } else {
                // regular overwrite and when $mapping_pattern == 'single_controller_multiple_subapp'
                if($controllers[0] == 'all'){
                    $this->Subapps_manager_model->update_subapps_record(
                        array('subapps_alias'=>$subapps), 
                        array('status'=>AccountStatus::Active)
                    );
                } else {
                    foreach($controllers as $name){
                        $this->Subapps_manager_model->update_subapps_record(
                            array('subapps_alias'=>$subapps), 
                            array('status'=>AccountStatus::Active, "name"=>$name)
                        );
                    }
                }
            }

            echo json_encode($this->get_subapps_mapping_data());
        }
    }
}