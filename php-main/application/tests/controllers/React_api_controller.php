<?php
defined('BASEPATH') || exit('No direct script access allowed');

class React_api_controller extends CI_Controller
{

    public function app_data()
    {
        $tiles = $this->Keycloak_tiles_model->convert_tile_data_json();
        echo json_encode($tiles);
    }

    public function user_data()
    {
        $user_data = ($this->session->userdata('tiles_logged_in')!=NULL) 
            ? $this->session->userdata('tiles_logged_in') : $this->session->userdata('logged_in');
		echo json_encode($user_data);
    }

    public function save_favorites()
    {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if(!$data_check["result"]){ 
            return;
        }

        $post_data = $data_check["post_data"];
        $tile_ids = isset($post_data["ids"]) ? $post_data["ids"] : array();
        $update_response = $this->Keycloak_tiles_model->save_favourites($tile_ids);
        
        echo json_encode($update_response);

    }
}
