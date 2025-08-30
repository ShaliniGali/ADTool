<?php
/**
 * Created: Moheb, Sumit, August 14th, 2020
 * 
 * Library for managing account types and centeralizing the account types
 * throughout the whole UI.
 * 
 * privilege undestanding:
 * 0 -> No restriction
 * 1 -> Less restriction
 * 2 -> High restriction 
 * 
 * NOTE: activate_register.js
 * IF $accountTypes private field changes, please DO NOT forget to update the following line:
 * if (selectedAccountType == "admin" || selectedAccountType == "moderator") {
 * 
 * Usage:
 * 1) isValidAccountType("SEMI-USER"):
 *    will return false if "SEMI-USER" is not listed in $accountTypes.
 * 
 * 2) getAllAccountTypes():
 *    will return every well-defined account type in $accountTypes.
 * 
 * 3) getAllAccounts():
 *    will return all accounts with type and privileges
 * 
 * 4) getleastprivilege():
 *    will return the least prvilige account.
 *    NOTE: If the data types are coming from the database (@see USER_TYPE constant in constants.php),
 *          This function returns the user type from the logged_in session.
 * 
 * 5) generateAccountTypeMenu():
 *    will return all account types in customized dropdown menu html.
 * 
 */
#[AllowDynamicProperties]
class UserAccountType {

    const defaultAdmin = 'ADMIN';
    const defaultModerator = 'MODERATOR';
    const defaultUser = 'USER';

    private $ci;

    // privilege level has to be contigioused
    private $accountTypes =
        array(
            array("type" => UserAccountType::defaultAdmin,      "privilege" => 0),
            array("type" => UserAccountType::defaultModerator,  "privilege" => 1),
            array("type" => UserAccountType::defaultUser,       "privilege" => 2)
        );

    public function __construct() {
        if (!defined('USER_TYPE')) {
            echo '<script>alert("Error: USER_TYPE is not defined in application/config/constants.php")</script>';
            return;
        }
        else if (!isset(USER_TYPE['use-db'])) {
            echo '<script>alert("use-db must be a well defined boolean.")</script>';
            return;
        }

        $this->ci =& get_instance();
        if (USER_TYPE['use-db'] === TRUE) {
            $db = $this->ci->load->database(USER_TYPE['db'], TRUE);
            $db->select('DISTINCT (' . USER_TYPE['column'] . ')');
            $db->where(USER_TYPE['column'] . ' IS NOT NULL');
            $db->where(array('status'=>AccountStatus::Active));
            $types = $db->get(USER_TYPE['table'])->result_array();
            $this->accountTypes =  array_map(function($type) { return array('type' => $type[USER_TYPE['column']]); }, $types);
        }
    }

    public function isValidAccountType($type) {
        return in_array($type, array_column($this->accountTypes,"type"));
    }

    public function getAllAccountTypes() {
        return array_column($this->accountTypes,"type");
    }

    public function getAllAccounts() {
        return $this->accountTypes;
    }

    public function getleastprivilege() {
        if (USER_TYPE['use-db'] === TRUE) {
            $type = $this->ci->session->userdata('logged_in')[USER_TYPE['column']];
            if (!empty($type)) {
                return $type;
            }
            return 'undefined privileges';
        }
        return $this->accountTypes[max(array_column($this->accountTypes,"privilege"))];
    }

    public function generateAccountTypeMenu($type = null, $table = false) {
        $account_types = $this->getAllAccountTypes();
        $html = '';
        if($type==null){
            $html = '<select name="user_role" '.(!$table?'id="user_role"':'').' place required class="select2 w-100 mb-4 py-2 '.($table?'account_type form-control':'').'">';
            for ($i = 0; $i < count($account_types); $i++) {
                $html .= '<option value="' . $account_types[$i] . '">' . $account_types[$i] . '</option>';
            }
            $html .= '</select>';
        } 
        if($type=="Radio"){
            for ($i = 0; $i < count($account_types); $i++) {
                $html .= '<label class="radio col-4">
                <p class="text-muted">' . $account_types[$i] . '</p>
                <input type="radio" id="' . $account_types[$i] . '" name="account_type" value="' . $account_types[$i] . '" class="custom-control-input" required>
                <div class="invalid-feedback">Please choose one of the following account</div>
                <span class="check"></span>
              </label>';
            }
        }
        return $html;
    }

    public function checkSuperAdmin($account = null){
        $account = ($account === null) ? $this->ci->session->userdata('logged_in')['email'] : $account;
        return in_array($account, ADMIN_EMAILS);
    }
}
