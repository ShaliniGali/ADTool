<?php

require_once(APPPATH.'models/SOCOM_User_Base.php');

#[AllowDynamicProperties]
class SOCOM_Cycle_User_model extends SOCOM_User_Base {
    protected const TYPE = 'CYCLE';
    protected $table = 'USR_CYCLE_USERS';
    protected $historyTable = 'USR_CYCLE_USERS_HISTORY';
    protected $historyIdField = 'CYCLE_USER_ID';
    protected $groups = [
        1 => 'None',
        2 => 'Cycle Admin',
        3 => 'Weight Criteria Admin'
    ];
}