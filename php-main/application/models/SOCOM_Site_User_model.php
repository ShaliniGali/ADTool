<?php

require_once(APPPATH.'models/SOCOM_User_Base.php');

#[AllowDynamicProperties]
class SOCOM_Site_User_model extends SOCOM_User_Base {
    protected const TYPE = 'Site';
    protected $table = 'USR_SITE_USERS';
    protected $historyTable = 'USR_SITE_USERS_HISTORY';
    protected $historyIdField = 'SITE_USER_ID';
    protected $groups = [
        1 => 'None',
        2 => 'Pom Admin',
        3 => 'Pom User'
    ];
}