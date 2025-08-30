<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Generic_controller extends CI_Controller {
    
    //
    // Sumit, 28 October 2019
    //
	public function dump_accounts($quantity){
		
		if(RHOMBUS_PASSWORD_GENERATOR=="TRUE"){
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $result = array();
	   
		for ($i = 0; $i < $quantity; $i++) {
	       
	       $dump_result = array();
	       $pass = array(); 
		   $alphaLength = strlen($alphabet) - 1; 
		    for ($j = 0; $j < 8; $j++) {
		        $n = random_int(0,$alphaLength);
		        $pass[] = $alphabet[$n];
		   }

	       $dump_result['password'] = implode($pass);
	       $temp_result = $this->DB_ind_model->encrypt_decrypt_passwords("encode",$dump_result['password']);

	       $dump_result['encrypted_password'] = $temp_result['password'];
	       $dump_result['salt'] = $temp_result['salt'];
	       
	       array_push($result, $dump_result);
	    }

		echo json_encode($result);
		}
	   
	}

    

}