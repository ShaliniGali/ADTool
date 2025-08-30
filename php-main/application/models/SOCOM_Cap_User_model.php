<?php

require_once(APPPATH.'models/SOCOM_Users_Group_Lookup_Base.php');

#[AllowDynamicProperties]
class SOCOM_Cap_User_model extends SOCOM_Users_Group_Lookup_Base {
    protected const TYPE = 'CapUser';
    protected $table = 'USR_CAP_USERS';
    protected $historyTable = 'USR_CAP_USERS_HISTORY';
    protected $historyIdField = 'CAP_USER_ID';
    protected $group_table = 'LOOKUP_SPONSOR';
    protected $group_field = 'SPONSOR_CODE';  
    protected $where = ['field' => 'SPONSOR_TYPE', 'value' => 'CAPABILITY'];
}