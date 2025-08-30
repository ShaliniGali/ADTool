<?php
/**
 * Created: Moheb, August 6th, 2020
 * 
 * Enforces login subnet rules where the UI's visitor ip must be checked
 * and validated before the visitor is granted access to the UI's login page.
 * 
 * These rules may be entirely ignored by setting the RHOMBUS_PRIVATE_SUBNET_LOGIN
 * constant inside the constants.php file to 'FALSE' (OR any value not equal to 'TRUE').
 */
#[AllowDynamicProperties]
class  Login_private_subnet_model extends CI_Model {
    private $private_subnet_login;
    private $login_subnet_table_name = 'login_subnet_ip';

    public function __construct() {
        parent::__construct();
        $this->private_subnet_login = (RHOMBUS_PRIVATE_SUBNET_LOGIN === 'TRUE');
    }

    /**
     * Created: Moheb, August 6th, 2020
     * 
     * Returns true if the private subnet login access is required; otherwise, returns false.
     * 
     * @param void
     * @return bool
     */
    public function enforcePrivateSubnetLogin() {
        return $this->private_subnet_login;
    }
    
    /**
     * Created: Moheb, August 6th, 2020
     * 
     * Checks whether an ip, specified by $private_ip, has access to the UI
     * or not. If $private_ip is not specified (i.e $private_ip == null),
     * a server name may be provided via $server_name to check the UI access permissions.
     * Returns true if and only if the ip exists inside the table
     * specified by $this->login_subnet_table_name with and 'Active' status.
     * Otherwise, returns false.
     * 
     * @param string $private_ip
     * @param string $server_name
     * @return bool
     */
    public function has_access($private_ip, $server_name = null) {
        $this->DBs->GUARDIAN_DEV->select('status');
        $this->DBs->GUARDIAN_DEV->from($this->login_subnet_table_name);
        if ($private_ip) {
            $this->DBs->GUARDIAN_DEV->where(array('ip' => $private_ip));
        } elseif ($server_name) {
            $this->DBs->GUARDIAN_DEV->where(array('note' => $server_name));
        }
        $this->DBs->GUARDIAN_DEV->where(array('status' => AccountStatus::Active));
        $account_info = $this->DBs->GUARDIAN_DEV->get()->result_array();
        return (count($account_info) != 0);
    }
}
