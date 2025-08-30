<?php

require_once(APPPATH. 'models/DBsCore.php');

#[AllowDynamicProperties]
class  DBs extends DBsCore
{

    public function __construct()
    {
        parent::__construct();

        if(SHOW_SOCOM){
            $SOCOM_UI = $this->SOCOM_UI = $this->findDBConnection(getenv('SOCOM_UI'));
        }


        $this->load->model('SOCOM_Cycle_Management_model');
        $this->load->model('SOCOM_Dynamic_Year_model');
        $this->load->model('SOCOM_Site_User_model');
        $this->load->model('SOCOM_Cap_User_model');
        $this->load->model('SOCOM_Users_model');
    }
    
}
