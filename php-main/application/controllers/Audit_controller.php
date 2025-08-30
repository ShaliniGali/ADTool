<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Audit_controller extends CI_Controller {


   function __construct()
   {
       parent::__construct();


       /**
        * Show account manager only to Super Admins
        */
       $is_SuperAdmin = $this->useraccounttype->checkSuperAdmin();
       /**
        * If not super admin redirect to home page.
        */
       if (!$is_SuperAdmin) {
           $this->output->set_status_header(401);
           return;
       }
   }

   /**
    * created by lea on june 15 2021
    * takes a number and displays the difference between
    * the most recent audit and the nth one back
    */
   public function difference($num){
      $this->load->library('RB_Auditing');
      $this->rb_auditing->display($num);
   }
   
   /**
    * created by lea on june 15 2021
    * calls the audit library and saves the current database structure
    * in a json in the RB_database_audits directory
    */
   public function audit(){
      $this->load->library('RB_Auditing');
      $this->rb_auditing->database();
   }
}