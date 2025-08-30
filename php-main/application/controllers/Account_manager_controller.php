<?php
// Created Sai August 11th 2020
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Account_manager_controller extends CI_Controller
{

    public function index()
    {
        /**
         * Show account manager only to Super Admins
         */
        $is_SuperAdmin = $this->useraccounttype->checkSuperAdmin();
        /**
         * If not super admin redirect to home page.
         */
        if ($is_SuperAdmin) {
            $SSOAccounts = $this->Account_manager_model->isSSOAvailable();
            $data = array(
                'columns' => array(
                    array('data' => 'id', 'visible' => false),
                    array('data' => 'email', 'visible' => true),
                    array('data' => 'status', 'visible' => false),
                    array('data' => 'account_type', 'visible' => false),
                    array('data' => 'admin_expiry', 'visible' => false)
                )
            );
            if (
                (
                    RHOMBUS_SSO === 'TRUE' ||
                    RHOMBUS_SSO_KEYCLOAK === 'TRUE' ||
                    RHOMBUS_SSO_PLATFORM_ONE === 'TRUE'
                ) &&
                $SSOAccounts !== false
            ) {
                $this->addAppUserToSSOAccounts($SSOAccounts);
                $data['sso'] = $SSOAccounts;
            } else {
                $data['accounts'] = $this->Account_manager_model->getAccount();
            }
            $this->load->view('account_manager_view', $data);
        } else {
            $this->output->set_status_header(401);
        }
    }

    protected function addAppUserToSSOAccounts(&$SSOAccounts) {
        $app_user_tiles = $this->Keycloak_tiles_model->get_app_users();
        foreach($SSOAccounts as &$sso_user){
            if(isset($app_user_tiles[AccountStatus::Active])
                && array_key_exists($sso_user['email'], $app_user_tiles[AccountStatus::Active])){
                $sso_user['active_apps'] =
                array_column($app_user_tiles[AccountStatus::Active][$sso_user['email']],'label');

                foreach($app_user_tiles[AccountStatus::Active][$sso_user['email']] as $subapp){
                    $sso_user['account_type_subapp'][$subapp['label']] = $subapp['account_type'];
                }
            } else {
                $sso_user['active_apps'] = array();
            }
            if(isset($app_user_tiles[AccountStatus::RegistrationPending])
                && array_key_exists($sso_user['email'], $app_user_tiles[AccountStatus::RegistrationPending])){
                    $sso_user['requested_apps'] =
                    array_column($app_user_tiles[AccountStatus::RegistrationPending][$sso_user['email']],'label');

                    foreach($app_user_tiles[AccountStatus::RegistrationPending][$sso_user['email']] as $subapp){
                        $sso_user['account_type_subapp'][$subapp['label']] = $subapp['account_type'];
                    }
            } else {
                $sso_user['requested_apps'] = array();
            }
        }
    }

    /**
     * Get active account data from user and users_keys table
     * Created Sai: August 11 2020
     */
    public function getAccountData()
    {
        $SSOAccounts = $this->Account_manager_model->isSSOAvailable();
        if ($SSOAccounts !== false) {
            $this->addAppUserToSSOAccounts($SSOAccounts);
            echo json_encode($SSOAccounts);
        } else {
            echo json_encode($this->Account_manager_model->getAccount());
        }
    }


    /**
     * Created Sai: August 11 2020
     * Validates the userArray
     * @return string success|failure
     */
    public function validateUser($userArray)
    {
        return $this->form_validation->run_rules($userArray);
    }

    /**
     *
     * Created Sai: August 11 2020
     * Updates the user accounts with modifications made by the admin
     */
    public function updateUser()
    {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $post_data = $data_check["post_data"];
            if (!(defined(RHOMBUS_FACS) && RHOMBUS_FACS == 'TRUE')
                && $post_data['AccountType'] != $this->useraccounttype::defaultUser) {
                $result = $this->validateUser(array(
                    'Id' => array('rules' => 'required'),
                    'ExpiryDate' => array('rules' => 'required|callback_checkDateValid'),
                    'AccountType' => array('rules' => 'required')
                ));
                if ($result !== 'success') {
                    echo json_encode(array("result" => "error"));
                } else {
                    $response = $this->Account_manager_model->updateUser($post_data, 'typeAdmin');
                    echo json_encode(array("result" => $response['message']));
                }
            } else {
                $result = $this->validateUser(array(
                    'Id' => array('rules' => 'required'),
                    'AccountType' => array('rules' => 'required')
                ));
                if ($result !== 'success') {
                    echo json_encode(array("result" => "error"));
                } else {
                    $response = $this->Account_manager_model->updateUser($post_data, 'typeUser');
                    echo json_encode(array("result" => $response['message']));
                }
            }
        }
    }


    /**
     * Marks status as delete in users table
     * Created Sai: August 11 2020
     */
    public function deleteAccount()
    {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $pdata = $data_check["post_data"];
            $response = $this->Account_manager_model->deleteAccount($pdata['id'], $pdata['email'], $pdata['type']);
            echo json_encode(array("result" => $response['message']));
        }
    }

     /**
     * Validates Dates. returns false if previous date is entered
     * Created Sai: August 11 2020
     */
    function checkDateValid($date)
    {
        return new DateTime() < new DateTime($date);
        
    }

    function encrypt_data(){
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $post_data = $data_check["post_data"];

            $data = $post_data["data"];

            $data["time"] = time();

            $result = encrypted_string($data, "encode");
            echo json_encode(array("result" => $result));
        }
    }

    public function registerSSOUser() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if ($data_check["result"]) {
            $pd = $data_check["post_data"];
            $updated = $this->Account_manager_model->activateSSOUSer($pd['id'], $pd['email'], $pd['account_type']);

            if ($updated) {
                echo json_encode(array('status' => 'success', 'message' => 'Successfully registered'));
                $this->Generic->send_email(array(
                    'receiverEmail' => $pd['email'],
                    'subject' => "Account registration: Approved",
                    'receiverName' => "",
                    'template' => 'custom',
                    'footer' => ['ipAddress' => ''],
                    'content' => [
                        ['type' => 'row', 'row' => [[
                            'type' => 'text',
                            'text' => 'Your request for an SSO account has been approved '.
                                        'by a Rhombus Power administrator. '.
                                        '<br>You may now use your SSO to login.'
                        ]]],
                        ['type' => 'row', 'row' => [[
                            'type' => 'text',
                            'text' => 'If this is not you then please contact it@rhombuspower.com.'
                        ]]],
                        ['type' => 'row', 'row' => [[
                            'type' => 'text',
                            'text' => 'Thanks,<br> IT Team'
                        ]]]
                    ]
                ));
            } else {
                echo json_encode(array('status' => 'failure', 'message' => 'Failed to register'));
            }
        }
    }

    public function registerSubapps(){
        $dataCheck = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if (!$dataCheck["result"]) {
            return;
        }
        $pd = $dataCheck["post_data"];
        $email = $pd['email'];
        $subapps = $pd['subapps'];

        $updated = false;
        foreach($subapps as $subapp){
            $subappId = $this->Keycloak_tiles_model->getSubappIdfromName($subapp);
            $updated = $this->Account_manager_model->registerSubapps($email,$subappId);
        }

        if($updated==false){
            echo json_encode(array('status' => 'failure', 'message' => 'Failed to register'));
        }
        else{
            echo json_encode(array('status' => 'success', 'message' => 'Subapps registered'));
        }
    }

    public function registerSubappsType(){
        $dataCheck = $this->DB_ind_model->validate_post($this->input->post()); //validating input posts
        if (!$dataCheck["result"]) {
            return;
        }
        $pd = $dataCheck["post_data"];
        $email = $pd['email'];
        $payloadSub = $pd['payloadSub'];
        $updated = false;
        foreach($payloadSub as $subappData){
            $subappId = $this->Keycloak_tiles_model->getSubappIdfromName(
                str_replace('&amp;', '&',$subappData['label'])
            );
            $updated = $this->Account_manager_model->saveSubappsType($email,$subappId,$subappData['type']);
        }

        if(!$updated){
            echo json_encode(array('status' => 'failure', 'message' => 'Failed to save'));
        }
        else{
            echo json_encode(array('status' => 'success', 'message' => 'Subapps type changed'));
        }
    }
    
}