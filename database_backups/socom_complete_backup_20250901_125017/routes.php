<?php
defined('BASEPATH') || exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
|
| NOTE: For the default_controller route assignment,
|		it can be DIFFERENT for each project.
*/
$route['default_controller'] = 'home';
$route['/'] = $route['default_controller'];
$route['404_override'] = 'My404';
$route['translate_uri_dashes'] = FALSE;

// Account_manager_controller
$route['Account_manager_controller'] = 'Login/index';
$route['Account_manager_controller/(.+)'] = 'Login/index';
$route['account_manager/index'] = 'Account_manager_controller/index';
$route['account_manager/getAccountData'] = 'Account_manager_controller/getAccountData';
$route['account_manager/updateUser'] = 'Account_manager_controller/updateUser';
$route['account_manager/deleteAccount'] = 'Account_manager_controller/deleteAccount';
$route['account_manager/encrypt_data'] = 'Account_manager_controller/encrypt_data';
$route['account_manager/registerSSOUser'] = 'Account_manager_controller/registerSSOUser';
$route['account_manager/registerSubapps'] = 'Account_manager_controller/registerSubapps';
$route['account_manager/registerSubappsType'] = 'Account_manager_controller/registerSubappsType';

// Audit_controller
$route['Audit_controller'] = 'Login/index';
$route['Audit_controller/(.+)'] = 'Login/index';
$route['audit/difference/(:num)'] = 'Audit_controller/difference/$1';
$route['audit/audit'] = 'Audit_controller/audit';

// CAC_controller
$route['CAC_controller'] = 'Login/index';
$route['CAC_controller/(.+)'] = 'Login/index';
$route['cac/auth'] = 'CAC_controller/auth';

// First_admin_controller
$route['First_admin_controller'] = 'Login/index';
$route['First_admin_controller/(.+)'] = 'Login/index';
$route['first_admin/index'] = 'First_admin_controller/index';
$route['first_admin/create_accounts'] = 'First_admin_controller/create_accounts';

// Keycloak_register_active
$route['Keycloak_register_active'] = 'Login/index';
$route['Keycloak_register_active/(.+)'] = 'Login/index';
$route['rb_kc/activate/(:any)'] = 'Keycloak_register_activate/activate/$1';

// Keycloak_sso_controller
$route['Keycloak_sso_controller'] = 'Login/index';
$route['Keycloak_sso_controller/(.+)'] = 'Login/index';
$route['rb_kc/success/(:num)/(:any)'] = 'Keycloak_sso_controller/success/$1/$2';
$route['rb_kc/failure'] = 'Keycloak_sso_controller/failure';
$route['rb_kc/requestRegistration'] = 'Keycloak_sso_controller/requestRegistration';

// Login_keycloak
$route['Login_keycloak'] = 'Login/index';
$route['Login_keycloak/(.+)'] = 'Login/index';
$route['rb_kc/authenticate/(:num)/(:any)'] = 'Login_keycloak/authenticate/$1/$2';

// Platform_One_register_active
$route['Platform_One_register_active'] = 'Login/index';
$route['Platform_One_register_active/(.+)'] = 'Login/index';
$route['rb_p1/activate/(:any)'] = 'Platform_One_register_activate/activate/$1';

// Platform_One_sso_controller
$route['Platform_One_sso_controller'] = 'Login/index';
$route['Platform_One_sso_controller/(.+)'] = 'Login/index';
$route['rb_p1/success/(:num)/(:any)'] = 'Platform_One_sso_controller/success/$1/$2';
$route['rb_p1/failure'] = 'Platform_One_sso_controller/failure';
$route['rb_p1/requestRegistration'] = 'Platform_One_sso_controller/requestRegistration';

// Login_Platform_One
$route['Login_Platform_One'] = 'Login/index';
$route['Login_Platform_One/(.+)'] = 'Login/index';
$route['rb_p1/authenticate/(:num)/(:any)'] = 'Login_Platform_One/authenticate/$1/$2';

// Login_token_controller
$route['Login_token_controller'] = 'Login/index';
$route['Login_token_controller/(.+)'] = 'Login/index';
$route['login_token/generateLoginToken'] = 'Login_token_controller/generateLoginToken';
$route['login_token/authenticateLoginToken'] = 'Login_token_controller/authenticateLoginToken';

// Login
// Note: DO NOT add the default routes here
$route['login/index'] = 'Login/index';
$route['login/activate/(A-Za-z0-9)'] = 'Login/activate/$1';
$route['login/activate_register'] = 'Login/activate_register';
$route['login/logout'] = 'Login/logout';
$route['login/user_check'] = 'Login/user_check';
$route['login/login_recovery_code'] = 'Login/login_recovery_code';
$route['login/reset_recovery_codes'] = 'Login/reset_recovery_codes';
$route['login/check_key_exist'] = 'Login/check_key_exist';
$route['login/confirm_reset_password'] = 'Login/confirm_reset_password';
$route['login/activate_reset_password/(A-Za-z0-9)'] = 'Login/activate_reset_password/$1';
$route['login/send_reset_password'] = 'Login/send_reset_password';
$route['login/nothing'] = 'Login/nothing';
$route['login/send_reset_password_by_email'] = 'Login/send_reset_password_by_email';

// Register_active
$route['Register_active'] = 'Login/index';
$route['Register_active/(.+)'] = 'Login/index';
$route['register_active/activate/(A-Za-z0-9)'] = 'Register_active/activate/$1';

// Register
$route['Register'] = 'Login/index';
$route['Register/(.+)'] = 'Login/index';
$route['register/validateEmailDomain'] = 'Register/validateEmailDomain';
$route['register/activate'] = 'Register/activate';
$route['register/create_account'] = 'Register/create_account';
$route['register/reject_register'] = 'Register/reject_register';

// SSO_controller
$route['NIPR/SSO_controller'] = 'Login/index';
$route['NIPR/SSO_controller/(.+)'] = 'Login/index';
$route['sso/success/(.+)'] = 'NIPR/SSO_controller/success/$1';
$route['sso/failure/(.+)'] = 'NIPR/SSO_controller/failure/$1';
$route['sso/IDPFailure'] = 'NIPR/SSO_controller/IDPFailure';
$route['sso/requestRegistration'] = 'NIPR/SSO_controller/requestRegistration';

// SSO_Metadata_controller
$route['NIPR/SSO_Metadata_controller'] = 'Login/index';
$route['NIPR/SSO_Metadata_controller/(.+)'] = 'Login/index';
$route['sso_metadata/getSelfMetadata'] = 'NIPR/SSO_Metadata_controller/getSelfMetadata';
$route['sso_metadata/getAllRemoteSPMetadatas'] = 'NIPR/SSO_Metadata_controller/getAllRemoteSPMetadatas';

// SSO_Users_registration_controller
$route['NIPR/SSO_Users_registration_controller'] = 'Login/index';
$route['NIPR/SSO_Users_registration_controller/(.+)'] = 'Login/index';
$route['sso_users_registration/index'] = 'NIPR/SSO_Users_registration_controller/index';
$route['sso_users_registration/registerSSOUsers'] = 'NIPR/SSO_Users_registration_controller/registerSSOUsers';

// Keycloak_tiles_controller
$route['kc_tiles'] = 'Keycloak_tiles_controller/index';
$route['kc_tiles/login'] = 'Keycloak_tiles_controller/tile_login';
$route['kc_tiles/register'] = 'Keycloak_tiles_controller/request_register';
$route['kc_tiles/update'] = 'Keycloak_tiles_controller/update';
$route['kc_tiles/populate_fields'] = 'Keycloak_tiles_controller/populate_fields';
$route['kc_tiles/save_tiles'] = 'Keycloak_tiles_controller/save_tiles';

// FACS_manager_controller
$route['facs_manager'] = 'FACS_manager_controller/index';
$route['facs_manager/delete_facs'] = 'FACS_manager_controller/delete_facs';
$route['facs_manager/add_facs'] = 'FACS_manager_controller/add_facs';
$route['facs_manager/edit_facs'] = 'FACS_manager_controller/edit_facs';
$route['facs_manager/get_facs'] = 'FACS_manager_controller/get_facs';
$route['facs_manager/autopop'] = 'FACS_manager_controller/auto_populate_facs_tables';
$route['facs_manager/subapps_mapping'] = 'FACS_manager_controller/subapps_mapping';

// react_api_server
$route['api/sso/apps'] = 'React_api_controller/app_data';
$route['api/sso/user'] = 'React_api_controller/user_data';
$route['api/sso/register'] = 'Keycloak_tiles_controller/request_register';
$route['api/sso/favorites'] = 'React_api_controller/save_favorites';

include(realpath(__DIR__ . '/project_config/project_routes.php'));