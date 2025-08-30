<?php

    class DB_ind_model extends CI_Model{
    //
    // Sumit, 19 September 2019
    // Updated: 1 Sept 2019
    //
    /**
     *                      WARNING
     * The function check_login() is @deprecated as of December 21st, 2020.
     * Please refrain from using this function in the future.
     * For more info on how the UI checks whether a user is logged in or not,
     * @see application/config/hooks.php and application/hooks/LoginStatus.php
     */
    public function check_login(){
        if(!empty($this->session->userdata('logged_in')['id'])){
            if ($this->session->has_userdata('redirect')) {
                $keeping_redirect_session = $this->session->userdata('redirect');
                $this->session->unset_userdata('redirect');
                redirect($keeping_redirect_session);
            } 
        } else {
            $this->session->unset_userdata('redirect');
            $this->session->set_userdata('redirect', current_url());
            redirect('Login');
        }
    }


    //
    // Validating post data and using Codeigniters' db->escape() on post values
    //
    // Geri, 6 November 2019
    //
    public function validate_post($post_data)
    {
        //checking if data is null
        $result = implode(array_values(array_map(function ($post_arg) {
                if (is_array($post_arg)){
                    return true;
                }
                return $post_arg;
        }, $post_data)));

        // //making sure values 
        // function escape_value(&$value, $key){            
        //     $ci =& get_instance();
        //     $value = $ci->db->escape($value);
        // }
        // array_walk_recursive($post_data, 'escape_value');
        
        return array("result"=>$result, "post_data"=>$post_data);
    }


	public function encrypt_decrypt_passwords() {

	}
  }

?>