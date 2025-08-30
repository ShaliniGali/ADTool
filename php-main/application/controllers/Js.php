<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Js extends CI_Controller {
    
//
// 	Sumit  28 October 2019 
//
//	Keep appending your constants here
//
	public function vars(){
		
		$data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]){ 
	        $post_data = $data_check["post_data"];
            //
            // All inputs are in $post_data
            //
            $data = array( 
	        'RHOMBUS_MAPBOX_LIGHT' => RHOMBUS_MAPBOX_LIGHT,
	        'RHOMBUS_DEBUG'=> RHOMBUS_DEBUG,
	        'RHOMBUS_CONSOLE'=>RHOMBUS_CONSOLE
	        );
	    echo json_encode($data);
		exit();
		} 

	}


}