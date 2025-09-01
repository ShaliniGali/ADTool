<?php
defined('BASEPATH') || exit('No direct script access allowed');

defined('RHOMBUS_ENCRYPTION_KEY') || define('RHOMBUS_ENCRYPTION_KEY', getenv('RHOMBUS_ENCRYPTION_KEY'));
defined('GLOBAL_APP_STRUCTURE') OR define('GLOBAL_APP_STRUCTURE', 'SOCOM');
defined('RHOMBUS_DEBUG_ENV') || define('RHOMBUS_DEBUG_ENV', FALSE);

require_once(BASEPATH . 'core/Common.php');
require_once(realpath(__DIR__ . '/../helpers/dbcredentials_helper.php'));
require_once(realpath(__DIR__ . '/../helpers/vault_helper.php'));
require_once(realpath(__DIR__ . '/../helpers/Rhombus_url_helper.php'));
require_once(realpath(__DIR__ . '/../libraries/AccountStatus.php'));


defined('P1_FLAG') || define('P1_FLAG', TRUE);
defined('DEPLOYMENT_ENVIRONMENT') || define('DEPLOYMENT_ENVIRONMENT', getenv(GLOBAL_APP_STRUCTURE.'_deployment_environment'));
defined('RHOMBUS_ENVIRONMENT') || define('RHOMBUS_ENVIRONMENT', getenv('SOCOM_ENVIRONMENT'));

defined('RHOMBUS_HANDSONTABLE_LICENSE') OR define('RHOMBUS_HANDSONTABLE_LICENSE', getenv('CI_HANDSONTABLE_KEY'));

/**
 * Constants for Keycloak Tiles
 */
defined('Keycloak_show_tiles') || define('Keycloak_show_tiles', explode("::::",getenv('Keycloak_show_tiles'))); 
defined('Keycloak_disable_tiles') || define('Keycloak_disable_tiles', explode("::::",getenv('Keycloak_disable_tiles'))); 

/**
 * Constants for SAML SSO
 */
defined('UI_SIPR_ENVIRONMENT') || define('UI_SIPR_ENVIRONMENT', (getenv(GLOBAL_APP_STRUCTURE.'_SAML_USERS_SIPR') == 'TRUE')?TRUE:FALSE );
defined('RB_SAML_USERS_DATABASENAME') || define('RB_SAML_USERS_DATABASENAME', getenv(GLOBAL_APP_STRUCTURE.'_SAML_USERS_DATABASENAME'));
defined('RB_SAML_USERS_DB_FLAG') || define('RB_SAML_USERS_DB_FLAG', getenv(GLOBAL_APP_STRUCTURE.'_SAML_USERS_DB_FLAG'));
defined('RB_SAML_DEBUG') || define('RB_SAML_DEBUG', getenv(GLOBAL_APP_STRUCTURE.'_SAML_DEBUG'));
defined('RHOMBUS_SSO') || define('RHOMBUS_SSO', getenv(GLOBAL_APP_STRUCTURE.'_SAML_SSO'));
if(BASEPATH == 'SAML_include'){
    defined('RHOMBUS_DATABASES') || define('RHOMBUS_DATABASES', RB_SAML_USERS_DB_FLAG);
}
if (RHOMBUS_ERROR_HANDLING === 'development') {
    defined('RHOMBUS_USER_QA') || define('RHOMBUS_USER_QA', explode('::::', getenv(GLOBAL_APP_STRUCTURE.'_emails_qa')));
} else {
    defined('RHOMBUS_USER_QA') || define('RHOMBUS_USER_QA', false);
}


/** Enabling modules */
defined('SHOW_SOCOM') || define('SHOW_SOCOM', (getenv('SHOW_SOCOM') == 'TRUE') ? TRUE : FALSE );

/**
 * Constants for Keycloak SSO
 */
defined('ACCEPT_JSON_STRING') || define('ACCEPT_JSON_STRING', 'accept: application/json');
defined('RHOMBUS_SSO_KEYCLOAK') OR define('RHOMBUS_SSO_KEYCLOAK', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO'));
defined('KEYCLOAK_SSO_DEV') OR define('KEYCLOAK_SSO_DEV', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_dev'));
defined('KEYCLOAK_SSO_URL') OR define('KEYCLOAK_SSO_URL', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_URL'));
defined('KEYCLOAK_SSO_REALM') OR define('KEYCLOAK_SSO_REALM', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_REALM'));
defined('RB_KEYCLOAK_DEBUG') OR define('RB_KEYCLOAK_DEBUG', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_DEBUG'));
defined('KEYCLOAK_SSO_CLIENT_ID') OR define('KEYCLOAK_SSO_CLIENT_ID', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_CLIENT_ID'));
defined('KEYCLOAK_SSO_CLIENT_SCOPE') OR define('KEYCLOAK_SSO_CLIENT_SCOPE', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_CLIENT_SCOPE'));
defined('KEYCLOAK_SSO_APP_NAME') OR define('KEYCLOAK_SSO_APP_NAME', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_APP_NAME'));
defined('KEYCLOAK_SSO_CLIENT_SECRET') OR define('KEYCLOAK_SSO_CLIENT_SECRET', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_CLIENT_SECRET'));
defined('KEYCLOAK_SSO_ID_TOKEN') OR define('KEYCLOAK_SSO_ID_TOKEN', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_SSO_ID_TOKEN'));
defined('KEYCLOAK_USE_AUTH_URL') OR define('KEYCLOAK_USE_AUTH_URL', getenv(GLOBAL_APP_STRUCTURE.'_KEYCLOAK_USE_AUTH_URL'));

/**
 * Platform One Keycloak
 */
defined('RB_PLATFORM_ONE_DEBUG') || define('RB_PLATFORM_ONE_DEBUG', getenv(GLOBAL_APP_STRUCTURE.'_PLATFORM_ONE_DEBUG'));
defined('RHOMBUS_SSO_PLATFORM_ONE') || define('RHOMBUS_SSO_PLATFORM_ONE', getenv(GLOBAL_APP_STRUCTURE.'_P1_SSO'));
defined('PLATFORM_ONE_SSO_URL') || define('PLATFORM_ONE_SSO_URL', getenv(GLOBAL_APP_STRUCTURE.'_P1_SSO_URL'));
defined('PLATFORM_ONE_SSO_PARAM') || define('PLATFORM_ONE_SSO_PARAM', getenv(GLOBAL_APP_STRUCTURE.'_P1_SSO_PARAM'));
defined('PLATFORM_ONE_SSO_APP_NAME') || define('PLATFORM_ONE_SSO_APP_NAME', getenv(GLOBAL_APP_STRUCTURE.'_P1_SSO_APP_NAME'));

defined('RHOMBUS_DATABASES') || define('RHOMBUS_DATABASES', getenv(GLOBAL_APP_STRUCTURE.'_Databases'));
defined('RHOMBUS_BASE_URL') || define('RHOMBUS_BASE_URL', getenv(GLOBAL_APP_STRUCTURE.'_BASE_URL'));
defined('RHOMBUS_DB_API_KEY') || define('RHOMBUS_DB_API_KEY', getenv('RB_DB_API_KEY'));
defined('RHOMBUS_SSO_DB_API_KEY') OR define('RHOMBUS_SSO_DB_API_KEY', getenv('KEYCLOAK_SSO_DB'));

// Rhombus API Call Debug Output
defined('RB_API_CALL_DEBUG') || define('RB_API_CALL_DEBUG', (getenv('RB_API_CALL_DEBUG') === 'TRUE' ? true : false));
defined('RHOMBUS_PYTHON_URL') || define('RHOMBUS_PYTHON_URL', getenv(GLOBAL_APP_STRUCTURE.'_PYTHON_URL'));

/**
 * FACS
 */
defined('RHOMBUS_FACS') OR define('RHOMBUS_FACS', getenv(GLOBAL_APP_STRUCTURE.'_FACS'));
defined('RB_FACS_API_KEY') || define('RB_FACS_API_KEY', getenv(GLOBAL_APP_STRUCTURE.'_FACS_API_KEY'));
defined('RB_FACS_URL') || define('RB_FACS_URL', getenv(GLOBAL_APP_STRUCTURE.'_RB_FACS_URL'));
defined('PROJECT_TILE_APP_NAME') || define('PROJECT_TILE_APP_NAME', getenv(GLOBAL_APP_STRUCTURE.'_TILE_APP_NAME'));
defined('PROJECT_USER_ROLE_DEFAULT') || define('PROJECT_USER_ROLE_DEFAULT', getenv(GLOBAL_APP_STRUCTURE.'_USER_ROLE_DEFAULT'));

/**
 * VAULT
 */
defined('VAULT_USERNAME') OR define('VAULT_USERNAME', getenv(GLOBAL_APP_STRUCTURE.'_VAULT_USERNAME'));
defined('VAULT_PASSWORD') OR define('VAULT_PASSWORD', getenv(GLOBAL_APP_STRUCTURE.'_VAULT_PASSWORD'));
defined('VAULT_URL') OR define('VAULT_URL', getenv(GLOBAL_APP_STRUCTURE.'_VAULT_URL'));
defined('VAULT_DB_ALIAS') OR define('VAULT_DB_ALIAS', explode("::::",getenv(GLOBAL_APP_STRUCTURE.'_VAULT_DB_ALIAS')));
defined('VAULT_DB_USER') OR define('VAULT_DB_USER', explode("::::",getenv(GLOBAL_APP_STRUCTURE.'_VAULT_DB_USER')));
defined('VAULT_FLAG') OR define('VAULT_FLAG', getenv(GLOBAL_APP_STRUCTURE.'_VAULT_FLAG'));

$products = [];

if (!getenv('Unit_test_indicator') && DEPLOYMENT_ENVIRONMENT=='NIPR'){
    if(VAULT_FLAG === 'TRUE'){
        $jsonData = load_vault_db_credentials();
    } else {
        $jsonData = loadDBCredentials();
    }

    $user_credentials = $jsonData[0];

    //adding all products that exists 
    for ($i = 1; $i < count($jsonData); $i++) {
        $products[$i - 1] = $jsonData[$i];
    }
        
    defined('H_N_CREDENTIALS') OR define('H_N_CREDENTIALS', $user_credentials['host_name']);
    defined('U_N_CREDENTIALS') OR define('U_N_CREDENTIALS', $user_credentials['user_name']);
    defined('P_W_CREDENTIALS') OR define('P_W_CREDENTIALS', $user_credentials['password']);
    defined('PORT_CREDENTIALS') OR define('PORT_CREDENTIALS', 3306);
    
    for ($p = 0; $p < count($products); $p++) {
		defined('H_N_PRODUCTS_'.$p) OR define('H_N_PRODUCTS_'.$p, $products[$p]['host_name']);
		defined('U_N_PRODUCTS_'.$p) OR define('U_N_PRODUCTS_'.$p, $products[$p]['user_name']);
		defined('P_W_PRODUCTS_'.$p) OR define('P_W_PRODUCTS_'.$p, $products[$p]['password']);
		defined('PORT_PRODUCTS_'.$p) OR define('PORT_PRODUCTS_'.$p, 3306);
    }
}


//
// 	Sumit  15 August 2019 
//
//	Rhombus constants
//
defined('RHOMBUS_DEBUG') || define('RHOMBUS_DEBUG', 'TRUE');  // 'TRUE', 'FALSE'
defined('RHOMBUS_CONSOLE') || define('RHOMBUS_CONSOLE', 'TRUE'); // 'TRUE', 'FALSE'
defined('RHOMBUS_PROJECT_NAME') || define('RHOMBUS_PROJECT_NAME', 'SOCOM');

//sso timeout counter in minutes
//it is NOT recomended the timeout is set to 1 minute or lower
defined('RHOMBUS_SSO_TIMEOUT') || define('RHOMBUS_SSO_TIMEOUT', 60);

/**
 * Modified: Moheb, August 21st, 2020
 * Now checks that an email API key must be provided; otherwise, displays an error.
 * @param string UI_EMAIL_SEND 
 * TRUE OR FALSE
 * 
 * This indicator allows you to send email from the UI using Email API key.
 * 
 *	TRUE   => Email service is active
 *  FALSE  => Email service is de-active
 * 
 * * Important:
 * If you are seting UI_EMAIL_SEND to false, UI will not be able to send emails
 * If you set UI_EMAIL_SEND to TRUE, please make sure; your instance IP is whitelisted for sending email. 
 * You will also require to set in your env
 * RB_EMAIL_API_KEY=
 * RB_EMAIL_API_URL=
 * 
 */
defined('UI_EMAIL_SEND_SMTP') || define('UI_EMAIL_SEND_SMTP', getenv('UI_EMAIL_SEND_SMTP')); 
defined('UI_EMAIL_SMTP_FROM') || define('UI_EMAIL_SMTP_FROM', getenv('UI_EMAIL_SMTP_FROM'));
defined('UI_EMAIL_SEND') || define('UI_EMAIL_SEND', getenv('UI_EMAIL_SEND')); 
defined('RB_EMAIL_API_KEY') || define('RB_EMAIL_API_KEY', getenv('RB_EMAIL_API_KEY')); // 'TRUE', 'FALSE'
defined('RB_EMAIL_API_URL') || define('RB_EMAIL_API_URL', getenv('RB_EMAIL_API_URL')); // 'TRUE', 'FALSE'

// if TRUE UI will allow Username Password Registration, TFA features and Reset Password Features
defined('UI_USERNAME_PASS_AUTH') || define('UI_USERNAME_PASS_AUTH', getenv('UI_USERNAME_PASS_AUTH'));  // 'TRUE', 'FALSE'

/**
 * @param array ADMIN_EMAILS 
 * An array of strings indicating a distinct super-admin email for each string.
 * 
 * This indicator keeps the list of super admins. A super admin has a privilege to activate and deactivate user account at any time
 * 
 */
defined('ADMIN_EMAILS') || define('ADMIN_EMAILS', explode("::::",getenv('SOCOM_admin_emails')));

defined('CREDENTIALS_TILE_DB') || define('CREDENTIALS_TILE_DB', getenv('SSO_CREDENTIALS_TILE_DB'));

if(defined('RHOMBUS_FACS') && RHOMBUS_FACS == 'TRUE'){
    defined('USER_TYPE') || define('USER_TYPE', array(
        'use-db' => TRUE,
        'db' => CREDENTIALS_TILE_DB,
        'table' => 'user_roles',
        'column' => 'Name')
    ); 
} else {
    defined('USER_TYPE') || define('USER_TYPE', array(
        'use-db' => FALSE,
        'db' => getenv(GLOBAL_APP_STRUCTURE.'_guardian_users'),
        'table' => 'users',
        'column' => 'account_type')
    );
}

/**
 * 
 * @param string FILE_CACHING_CSS_JS 
 * TRUE OR FALSE
 * 
 * This indicator allows you to cache your js or css files eliminating the need to cache clear at browser level
 * 
 * TRUE -> Caching is true
 * FALSE -> Caching is false
 * 
 */
defined('FILE_CACHING_CSS_JS') || define('FILE_CACHING_CSS_JS', "FALSE");
/**
 * 
 * @param string FILE_DOWNLOAD_CSS_JS 
 * TRUE OR FALSE
 * 
 * This indicator allows you to download your js or css files coming from S3 bucket
 * 
 * TRUE -> Files will get downloaded in your server
 * FALSE -> File will get removed from your server
 * 
 */
defined('FILE_DOWNLOAD_CSS_JS') || define('FILE_DOWNLOAD_CSS_JS', "FALSE");
/**
 * 
 * @param string S3_CSS_JS_URL 
 * 
 * This is your S3 global file address
 * 
 */
defined('S3_CSS_JS_URL') || define('S3_CSS_JS_URL', 'https://application-files.s3-us-gov-west-1.amazonaws.com/');

/**
 * @param string GUARDIAN_ACCESS_KEY
 * 
 * The AWS S3 access key
 */
 $gaurdian_access_key = getenv(GLOBAL_APP_STRUCTURE.'_guardian_access_key');
 defined('GUARDIAN_ACCESS_KEY') OR
     define(
         'GUARDIAN_ACCESS_KEY',
         (strlen($gaurdian_access_key) ? $gaurdian_access_key : null));
 unset($gaurdian_access_key);
 
 /**
 * @param string GUARDIAN_SECRET_KEY
 * 
 * The AWS S3 secret key
 */
 $guardian_secret_key = getenv(GLOBAL_APP_STRUCTURE.'_guardian_secret_key');
 defined('GUARDIAN_SECRET_KEY') OR
     define(
         'GUARDIAN_SECRET_KEY',
         (strlen($guardian_secret_key) ? $guardian_secret_key : null));
 unset($guardian_secret_key);

 defined('S3_ENDPOINT') || define('S3_ENDPOINT', getenv(GLOBAL_APP_STRUCTURE.'_S3_ENDPOINT'));

//
defined('RHOMBUS_PASSWORD_GENERATOR') || define('RHOMBUS_PASSWORD_GENERATOR', 'FALSE'); 

defined('RHOMBUS_MAPBOX_LIGHT') || define('RHOMBUS_MAPBOX_LIGHT', '');
defined('RHOMBUS_MAPBOX_DARK') || define('RHOMBUS_MAPBOX_DARK', '');
defined('RHOMBUS_MAPBOX_SATELLITE') || define('RHOMBUS_MAPBOX_SATELLITE', '');

/**
 * @param string RHOMBUS_TFA_LAYER 
 * TRUE OR FALSE
 * 
 * This indicator allows you to enable and disable TDA at browser level
 * 
 * TRUE -> RHOMBUS_TFA_LAYER is true
 * FALSE -> RHOMBUS_TFA_LAYER is false
 */
defined('RHOMBUS_TFA_LAYER') || define('RHOMBUS_TFA_LAYER', getenv(GLOBAL_APP_STRUCTURE.'_tfa_layer'));
defined('RHOMBUS_ENABLE_CAC') or define('RHOMBUS_ENABLE_CAC',
	(getenv('RHOMBUS_ENABLE_CAC') === 'TRUE' ? true : false));
/**
 * @param string RHOMBUS_EMAIL_DOMAIN 
 * TRUE OR FALSE
 * 
 * This indicator toggles email domain validation
 * 
 * TRUE -> RHOMBUS_EMAIL_DOMAIN is true, email domain must be found in VALID_EMAIL_DOMAINS
 * FALSE -> RHOMBUS_EMAIL_DOMAIN is false, email domain can be anything
 */
defined('RHOMBUS_EMAIL_DOMAIN') || define('RHOMBUS_EMAIL_DOMAIN', (getenv(GLOBAL_APP_STRUCTURE.'_VALIDATE_EMAIL_DOMAIN') == 'TRUE') ? 'TRUE' : 'FALSE');

/**
 * @param string RHOMBUS_PRIVATE_SUBNET_LOGIN 
 * TRUE OR FALSE
 * 
 * NOTE: 
 * IF set to TRUE
 * A Database table whose name defined inside models/login_and_registration/Login_private_subnet_model.php
 * with the private field $login_subnet_table_name must be created. The table structure must have:
 * an ip column and a status column.
 * 
 * 
 * This indicator allows you to register user_name with RHOMBUS_EMAIL_DOMAIN i.e: @rhombuspower.com
 * 
 *	TRUE   => A UI visitor's ip must be validated before accessing the UI.
 *  FALSE => Any UI visitor may access the UI.
 */
defined('RHOMBUS_PRIVATE_SUBNET_LOGIN') || define('RHOMBUS_PRIVATE_SUBNET_LOGIN', getenv(GLOBAL_APP_STRUCTURE.'_subnet_login'));

/**
 * Created: Moheb, August 18th, 2020
 * 
 * List of all valid email domains that may be used for user registration.
 */
defined('VALID_EMAIL_DOMAINS') || define('VALID_EMAIL_DOMAINS', array('rhombuspower.com', 'gmail.com','af.mil','us.af.mil','dodiis.mil')); 

/**
 * @param string LOGIN_LAYERS 
 * An array of string binary bits.
 * 
 * LOGIN_LAYERS is an indicator to force a user register secruity layers by switching
 * a bit from "0" to "1". Multiple bits may be set to "1" as desired.
 *
 * [0] -> Google Authenticator
 * [1] -> Yubkikey
 * [2] -> CAC Reader
 * [3] -> Recovery Code
 * [4] -> Login token
 * 
 * Example:
 * array("1", "0", "0", "0", "0") forces the user to register only for 
 * the Google Authenticator as indicated by the Google Authenticator bit whose value is 1.
 */
defined('LOGIN_LAYERS') || define('LOGIN_LAYERS', array("1", "0", "0", "0", "0"));

/**
 * 
 *  Encryption Parameters
 * 
 * NOTE: In order to maintain the same user login credentials across all Rhombus dev UIs for that user,
 * the encryption algorithm must be identical accross all those dev UIs. Thus, all of following constants
 * must be identical accross all UIs that share the same user credentials.
 * 
 * Constants:
 * - ENCRYPT_DECRYPT_FILE_IV: 
 *      An initialization vector (IV), 16 bytes (characters) long, used by the hashing algorithm to mask the encrypted bytes.
 * 
 * - ENCRYPT_DECRYPT_PASSWORD_ITERATIONS:
 *      Number of iterations the hashing algorithm performs for the data encryption.
 * 
 * - ENCRYPTION_KEY:
 *      The 16 bytes (characters) long key used for the data encryption.
 * 
 * - ENCRYPTION_SIZE:
 *      The size of the algorithm encryption key, 16 bytes (characters) long.
 * 
 * You may change the 16 bytes (characters) long to a 'power of two' length but make sure
 * to change the rest of the constants accordingly (optiional: ENCRYPT_DECRYPT_PASSWORD_ITERATIONS).
 * A minimum of 16 bytes is recommended, any higher value for length may slow down the encryption/decryption algorithm.
 * 
 * For further details on how the password encryption works, 
 * @see /libraries/Password_encrypt_decrypt.php
 */

 /** */
defined('ENCRYPT_DECRYPT_FILE_IV') || define('ENCRYPT_DECRYPT_FILE_IV', getenv(GLOBAL_APP_STRUCTURE.'_encrypt_decrypt_file_iv')); 
defined('ENCRYPT_DECRYPT_PASSWORD_ITERATIONS') || define('ENCRYPT_DECRYPT_PASSWORD_ITERATIONS', getenv(GLOBAL_APP_STRUCTURE.'_encrypt_decrypt_password_iterations')); 
defined('ENCRYPTION_KEY') || define('ENCRYPTION_KEY', getenv(GLOBAL_APP_STRUCTURE.'_encryption_key')); 
defined('ENCRYPTION_SIZE') || define('ENCRYPTION_SIZE', getenv(GLOBAL_APP_STRUCTURE.'_encryption_size'));

/**
 * 
 * AWS KEYS
 * 
 */
defined('AWS_IAM_KEY') || define('AWS_IAM_KEY', getenv(GLOBAL_APP_STRUCTURE.'_aws_iam_key'));
defined('AWS_IAM_SECRET_KEY') || define('AWS_IAM_SECRET_KEY', getenv(GLOBAL_APP_STRUCTURE.'_aws_iam_secret_key'));

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  || define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') || define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   || define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  || define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           || define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     || define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       || define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  || define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   || define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              || define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            || define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       || define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| Constants for Upload Feature for api/scheduler to UI FILE_ and CRON_
|--------------------------------------------------------------------------
*/
defined('FILE_STATUS_NEW')  OR define('FILE_STATUS_NEW', '0');
defined('FILE_STATUS_REQUESTED') OR define('FILE_STATUS_REQUESTED', '1');
defined('FILE_STATUS_DELETED') OR define('FILE_STATUS_DELETED', '2');
defined('FILE_STATUS_CANCELLED')   OR define('FILE_STATUS_CANCELLED', '3');
defined('FILE_STATUS_VIRUS') OR define('FILE_STATUS_VIRUS', '4');
defined('FILE_STATUS_NON_FIPS_COMPLIANT') OR define('FILE_STATUS_NON_FIPS_COMPLIANT', '5');
defined('FILE_STATUS_SLICED_IN_PROGRESS')   OR define('FILE_STATUS_SLICED_IN_PROGRESS', '6');
defined('FILE_STATUS_SLICED_COMPLETE')   OR define('FILE_STATUS_SLICED_COMPLETE', '7');
defined('FILE_STATUS_SLICED_ERROR')   OR define('FILE_STATUS_SLICED_ERROR', '8');

defined('CRON_STATUS_NEW')  OR define('CRON_STATUS_NEW', '0');
defined('CRON_STATUS_PROCESSED') OR define('CRON_STATUS_PROCESSED', '1');

defined('CRON_PROCESSED_NEW')  OR define('CRON_PROCESSED_NEW', '0');
defined('CRON_PROCESSED_SUCCESS') OR define('CRON_PROCESSED_SUCCESS', '1');
defined('CRON_PROCESSED_ERROR_PIPELINE') OR define('CRON_PROCESSED_ERROR_PIPELINE', '-1');
defined('CRON_PROCESSED_ERROR_FORMAT') OR define('CRON_PROCESSED_ERROR_FORMAT', '-2');
defined('CRON_PROCESSED_NON_FIPS_COMPLIANT') OR define('CRON_PROCESSED_NON_FIPS_COMPLIANT', '-3');

defined('APP_TAG') || define('APP_TAG', getenv(GLOBAL_APP_STRUCTURE.'_TAG'));

// APP VERSION
defined('APP_VERSION') || define('APP_VERSION', getenv(GLOBAL_APP_STRUCTURE.'_APP_VERSION'));

//defined('APP_VERSION_DATABASE') || define('APP_VERSION_DATABASE', [
//    'db' => 'guardian-admingi',
//    'table' => 'LOOKUP_APP_VERSION'
//]);


/**
 * Constants for smtp
 */
defined('RHOMBUS_SMTP_HOST') || define('RHOMBUS_SMTP_HOST', getenv(GLOBAL_APP_STRUCTURE.'_smtp_host'));
defined('RHOMBUS_SMTP_PORT') || define('RHOMBUS_SMTP_PORT', getenv(GLOBAL_APP_STRUCTURE.'_smtp_port'));
defined('RHOMBUS_SMTP_USER') || define('RHOMBUS_SMTP_USER', getenv(GLOBAL_APP_STRUCTURE.'_smtp_user'));
defined('RHOMBUS_SMTP_PASS') || define('RHOMBUS_SMTP_PASS', getenv(GLOBAL_APP_STRUCTURE.'_smtp_pass'));

defined('ACTF_DB') || define('ACTF_DB', getenv('SSO_ACTF_DB'));
defined('CAPDEV_DB') || define('CAPDEV_DB', getenv('SSO_CAPDEV_DB'));
defined('SLRD_DB') || define('SLRD_DB', getenv('SSO_SLRD_DB'));
defined('USAFPPBE_DB') || define('USAFPPBE_DB', getenv('SSO_USAFPPBE_DB'));
defined('TRIAD_DB') || define('TRIAD_DB', getenv('SSO_TRIAD_DB'));
defined('COMPETITION_DB') || define('COMPETITION_DB', getenv('SSO_COMPETITION_DB'));
defined('THREAT_DB') || define('THREAT_DB', getenv('SSO_THREAT_DB'));
defined('WSS_DB') || define('WSS_DB', getenv('SSO_WSS_DB'));
defined('MANPOWER_DB') || define('MANPOWER_DB', getenv('SSO_MANPOWER_DB'));
defined('EAAFM_DB') || define('EAAFM_DB', getenv('SSO_EAAFM_DB'));
defined('STRATEGICBASING_DB') || define('STRATEGICBASING_DB', getenv('SSO_STRATEGICBASING_DB'));
defined('CSPI_DB') || define('CSPI_DB', getenv('SSO_CSPI_DB'));
defined('OBLIGATIONEXPENDITURE_DB') || define('OBLIGATIONEXPENDITURE_DB', getenv('SSO_OBLIGATIONEXPENDITURE_DB'));
defined('FH_DB') || define('FH_DB', getenv('SSO_FH_DB'));
defined('COMBINED_DB') || define('COMBINED_DB', getenv('SSO_COMBINED_DB'));
defined('KG_DB') || define('KG_DB', getenv('SSO_KG_DB'));
defined('OOB_DB') || define('OOB_DB', getenv('SSO_OOB_DB'));
defined('USSFPPBE_DB') || define('USSFPPBE_DB', getenv('SSO_USSFPPBE_DB'));
defined('SOCOM_DB') || define('SOCOM_DB', getenv('SSO_SOCOM_DB'));

// Database configurations
if(
    !getenv('Unit_test_indicator') &&
    (
        RHOMBUS_FACS === 'TRUE' || 
        RHOMBUS_SSO_KEYCLOAK === 'TRUE' || 
        RHOMBUS_SSO_PLATFORM_ONE === 'TRUE'
    ) 
){


    defined('PORT_PRODUCTS_SSO') OR define('PORT_PRODUCTS_SSO', 3306);
    if(DEPLOYMENT_ENVIRONMENT=='NIPR'){
        if(VAULT_FLAG === 'TRUE'){
            $SSO_DB = load_vault_db_credentials();
        } else {
            $SSO_DB = getSSODBCredentials(RHOMBUS_SSO_DB_API_KEY)['APIData'];
        }
        
        defined('H_N_PRODUCTS_SSO') OR define('H_N_PRODUCTS_SSO', $SSO_DB[0]['host_name']);
        defined('U_N_PRODUCTS_SSO') OR define('U_N_PRODUCTS_SSO', $SSO_DB[0]['user_name']);
        defined('P_W_PRODUCTS_SSO') OR define('P_W_PRODUCTS_SSO', $SSO_DB[0]['password']);

        $db_array = array();

        if(getenv('SSO_ACTF_SCHEMA')!=''){
            defined('ACTF_SCHEMA') || define('ACTF_SCHEMA', getenv('SSO_ACTF_SCHEMA'));
            $db_array[] = ACTF_DB;
        }

        if(getenv('SSO_USAFPPBE_SCHEMA')!=''){
            defined('USAFPPBE_SCHEMA') || define('USAFPPBE_SCHEMA', getenv('SSO_USAFPPBE_SCHEMA'));
            $db_array[] = USAFPPBE_DB;
        }

        if(getenv('SSO_SLRD_SCHEMA')!=''){
            defined('SLRD_SCHEMA') || define('SLRD_SCHEMA', getenv('SSO_SLRD_SCHEMA'));
            $db_array[] = SLRD_DB;
        } 
        if(getenv('SSO_CAPDEV_SCHEMA')!=''){
            defined('CAPDEV_SCHEMA') || define('CAPDEV_SCHEMA', getenv('SSO_CAPDEV_SCHEMA'));
            $db_array[] = CAPDEV_DB;
        } 
        if(getenv('SSO_TRIAD_SCHEMA')!=''){
            defined('TRIAD_SCHEMA') || define('TRIAD_SCHEMA', getenv('SSO_TRIAD_SCHEMA'));
            $db_array[] = TRIAD_DB;
        } 
        if(getenv('SSO_COMPETITION_SCHEMA')!=''){
            defined('COMPETITION_SCHEMA') || define('COMPETITION_SCHEMA', getenv('SSO_COMPETITION_SCHEMA'));
            $db_array[] = COMPETITION_DB;
        } 
        if(getenv('SSO_THREAT_SCHEMA')!=''){
            defined('THREAT_SCHEMA') || define('THREAT_SCHEMA', getenv('SSO_THREAT_SCHEMA'));
            $db_array[] = THREAT_DB;
        } 
        if(getenv('SSO_WSS_SCHEMA')!=''){
            defined('WSS_SCHEMA') || define('WSS_SCHEMA', getenv('SSO_WSS_SCHEMA'));
            $db_array[] = WSS_DB;
        }
        if(getenv('SSO_MANPOWER_SCHEMA')!=''){
            defined('MANPOWER_SCHEMA') || define('MANPOWER_SCHEMA', getenv('SSO_MANPOWER_SCHEMA'));
            $db_array[] = MANPOWER_DB;
        }
        if(getenv('SSO_EEAFM_SCHEMA')!=''){
            defined('EEAFM_SCHEMA') || define('EEAFM_SCHEMA', getenv('SSO_EEAFM_SCHEMA'));
            $db_array[] = EEAFM_DB;
        }
        if(getenv('SSO_STRATEGICBASING_SCHEMA')!=''){
            defined('STRATEGICBASING_SCHEMA') || define('STRATEGICBASING_SCHEMA', getenv('SSO_STRATEGICBASING_SCHEMA'));
            $db_array[] = STRATEGICBASING_DB;
        }
        if(getenv('SSO_CSPI_SCHEMA')!=''){
            defined('CSPI_SCHEMA') || define('CSPI_SCHEMA', getenv('SSO_CSPI_SCHEMA'));
            $db_array[] = CSPI_DB;
        }
        if(getenv('SSO_OBLIGATIONEXPENDITURE_SCHEMA')!=''){
            defined('OBLIGATIONEXPENDITURE_SCHEMA') || define('OBLIGATIONEXPENDITURE_SCHEMA', getenv('SSO_OBLIGATIONEXPENDITURE_SCHEMA'));
            $db_array[] = OBLIGATIONEXPENDITURE_DB;
        }

        if(getenv('SSO_FH_SCHEMA')!=''){
            defined('FH_SCHEMA') || define('FH_SCHEMA', getenv('SSO_FH_SCHEMA'));
            $db_array[] = FH_DB;
        }

        if(getenv('SSO_COMBINED_SCHEMA')!=''){
            defined('COMBINED_SCHEMA') || define('COMBINED_SCHEMA', getenv('SSO_COMBINED_SCHEMA'));
            $db_array[] = COMBINED_DB;
        }
        
        if (getenv('SSO_KG_SCHEMA') != '') {
            defined('KG_SCHEMA') || define('KG_SCHEMA', getenv('SSO_KG_SCHEMA'));
            $db_array[] = KG_DB;
        }

        if (getenv('SSO_OOB_SCHEMA') != '') {
            defined('OOB_SCHEMA') || define('OOB_SCHEMA', getenv('SSO_OOB_SCHEMA'));
            $db_array[] = OOB_DB;
        }


        if(getenv('SSO_USSFPPBE_SCHEMA')!=''){
            defined('USSFPPBE_SCHEMA') || define('USSFPPBE_SCHEMA', getenv('SSO_USSFPPBE_SCHEMA'));
            $db_array[] = USSFPPBE_DB;
        }
             
        /**
        * SSO connection definitions (schema definitions could be removed)
        */
        $db_koint_key = implode("::::",$db_array);
        $db_combined_data = getSSODBCredentials($db_koint_key)['APIData'];

        if(getenv('SSO_ACTF_SCHEMA')!=''){
            defined('ACTF_SCHEMA') || define('ACTF_SCHEMA', getenv('SSO_ACTF_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_ACTF') OR define('H_N_PRODUCTS_ACTF', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_ACTF') OR define('U_N_PRODUCTS_ACTF', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_ACTF') OR define('P_W_PRODUCTS_ACTF', $db_data[0]['password']);
        }
        if(getenv('SSO_USAFPPBE_SCHEMA')!=''){
            defined('USAFPPBE_SCHEMA') || define('USAFPPBE_SCHEMA', getenv('SSO_USAFPPBE_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_USAFPPBE') OR define('H_N_PRODUCTS_USAFPPBE', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_USAFPPBE') OR define('U_N_PRODUCTS_USAFPPBE', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_USAFPPBE') OR define('P_W_PRODUCTS_USAFPPBE', $db_data[0]['password']);
        }
        if(getenv('SSO_SLRD_SCHEMA')!=''){
            defined('SLRD_SCHEMA') || define('SLRD_SCHEMA', getenv('SSO_SLRD_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_SLRD') OR define('H_N_PRODUCTS_SLRD', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_SLRD') OR define('U_N_PRODUCTS_SLRD', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_SLRD') OR define('P_W_PRODUCTS_SLRD', $db_data[0]['password']);
        } 
        if(getenv('SSO_CAPDEV_SCHEMA')!=''){
            defined('CAPDEV_SCHEMA') || define('CAPDEV_SCHEMA', getenv('SSO_CAPDEV_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_CAPDEV') OR define('H_N_PRODUCTS_CAPDEV', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_CAPDEV') OR define('U_N_PRODUCTS_CAPDEV', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_CAPDEV') OR define('P_W_PRODUCTS_CAPDEV', $db_data[0]['password']);
        } 
        if(getenv('SSO_TRIAD_SCHEMA')!=''){
            defined('TRIAD_SCHEMA') || define('TRIAD_SCHEMA', getenv('SSO_TRIAD_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_TRIAD') OR define('H_N_PRODUCTS_TRIAD', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_TRIAD') OR define('U_N_PRODUCTS_TRIAD', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_TRIAD') OR define('P_W_PRODUCTS_TRIAD', $db_data[0]['password']);
        } 
        if(getenv('SSO_COMPETITION_SCHEMA')!=''){
            defined('COMPETITION_SCHEMA') || define('COMPETITION_SCHEMA', getenv('SSO_COMPETITION_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_COMPETITION') OR define('H_N_PRODUCTS_COMPETITION', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_COMPETITION') OR define('U_N_PRODUCTS_COMPETITION', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_COMPETITION') OR define('P_W_PRODUCTS_COMPETITION', $db_data[0]['password']);
        } 
        if(getenv('SSO_THREAT_SCHEMA')!=''){
            defined('THREAT_SCHEMA') || define('THREAT_SCHEMA', getenv('SSO_THREAT_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_THREAT') OR define('H_N_PRODUCTS_THREAT', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_THREAT') OR define('U_N_PRODUCTS_THREAT', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_THREAT') OR define('P_W_PRODUCTS_THREAT', $db_data[0]['password']);
        } 
        if(getenv('SSO_WSS_SCHEMA')!=''){
            defined('WSS_SCHEMA') || define('WSS_SCHEMA', getenv('SSO_WSS_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_WSS') OR define('H_N_PRODUCTS_WSS', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_WSS') OR define('U_N_PRODUCTS_WSS', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_WSS') OR define('P_W_PRODUCTS_WSS', $db_data[0]['password']);
        }
        if(getenv('SSO_MANPOWER_SCHEMA')!=''){
            defined('MANPOWER_SCHEMA') || define('MANPOWER_SCHEMA', getenv('SSO_MANPOWER_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_MANPOWER') OR define('H_N_PRODUCTS_MANPOWER', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_MANPOWER') OR define('U_N_PRODUCTS_MANPOWER', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_MANPOWER') OR define('P_W_PRODUCTS_MANPOWER', $db_data[0]['password']);
        }
        if(getenv('SSO_EAAFM_SCHEMA')!=''){
            defined('EAAFM_SCHEMA') || define('EAAFM_SCHEMA', getenv('SSO_EAAFM_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_EAAFM') OR define('H_N_PRODUCTS_EAAFM', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_EAAFM') OR define('U_N_PRODUCTS_EAAFM', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_EAAFM') OR define('P_W_PRODUCTS_EAAFM', $db_data[0]['password']);
        }
        if(getenv('SSO_STRATEGICBASING_SCHEMA')!=''){
            defined('STRATEGICBASING_SCHEMA') || define('STRATEGICBASING_SCHEMA', getenv('SSO_STRATEGICBASING_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_STRATEGICBASING') OR define('H_N_PRODUCTS_STRATEGICBASING', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_STRATEGICBASING') OR define('U_N_PRODUCTS_STRATEGICBASING', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_STRATEGICBASING') OR define('P_W_PRODUCTS_STRATEGICBASING', $db_data[0]['password']);
        }
        if(getenv('SSO_CSPI_SCHEMA')!=''){
            defined('CSPI_SCHEMA') || define('CSPI_SCHEMA', getenv('SSO_CSPI_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_CSPI') OR define('H_N_PRODUCTS_CSPI', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_CSPI') OR define('U_N_PRODUCTS_CSPI', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_CSPI') OR define('P_W_PRODUCTS_CSPI', $db_data[0]['password']);
        }
        if(getenv('SSO_OBLIGATIONEXPENDITURE_SCHEMA')!=''){
            defined('OBLIGATIONEXPENDITURE_SCHEMA') || define('OBLIGATIONEXPENDITURE_SCHEMA', getenv('SSO_OBLIGATIONEXPENDITURE_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_OBLIGATIONEXPENDITURE') OR define('H_N_PRODUCTS_OBLIGATIONEXPENDITURE', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_OBLIGATIONEXPENDITURE') OR define('U_N_PRODUCTS_OBLIGATIONEXPENDITURE', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_OBLIGATIONEXPENDITURE') OR define('P_W_PRODUCTS_OBLIGATIONEXPENDITURE', $db_data[0]['password']);
        }
        if(getenv('SSO_FH_SCHEMA')!=''){
            defined('FH_SCHEMA') || define('FH_SCHEMA', getenv('SSO_FH_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_FH') OR define('H_N_PRODUCTS_FH', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_FH') OR define('U_N_PRODUCTS_FH', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_FH') OR define('P_W_PRODUCTS_FH', $db_data[0]['password']);
        }

        if(getenv('SSO_COMBINED_SCHEMA')!=''){
            defined('COMBINED_SCHEMA') || define('COMBINED_SCHEMA', getenv('SSO_COMBINED_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_COMBINED') || define('H_N_PRODUCTS_COMBINED', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_COMBINED') || define('U_N_PRODUCTS_COMBINED', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_COMBINED') || define('P_W_PRODUCTS_COMBINED', $db_data[0]['password']);
        }

        if (getenv('SSO_KG_SCHEMA') != '') {
            defined('KG_SCHEMA') || define('KG_SCHEMA', getenv('SSO_KG_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_KG') || define('H_N_PRODUCTS_KG', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_KG') || define('U_N_PRODUCTS_KG', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_KG') || define('P_W_PRODUCTS_KG', $db_data[0]['password']);
        }
        
        if (getenv('SSO_OOB_SCHEMA') != '') {
            defined('OOB_SCHEMA') || define('OOB_SCHEMA', getenv('SSO_OOB_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_OOB') || define('H_N_PRODUCTS_OOB', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_OOB') || define('U_N_PRODUCTS_OOB', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_OOB') || define('P_W_PRODUCTS_OOB', $db_data[0]['password']);
        }

        if(getenv('SSO_USSFPPBE_SCHEMA')!=''){
            defined('USSFPPBE_SCHEMA') || define('USSFPPBE_SCHEMA', getenv('SSO_USSFPPBE_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_USSFPPBE') OR define('H_N_PRODUCTS_USSFPPBE', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_USSFPPBE') OR define('U_N_PRODUCTS_USSFPPBE', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_USSFPPBE') OR define('P_W_PRODUCTS_USSFPPBE', $db_data[0]['password']);
        }

        if(getenv('SSO_USSFPPBE_SCHEMA')!=''){
            defined('USSFPPBE_SCHEMA') || define('USSFPPBE_SCHEMA', getenv('SSO_USSFPPBE_SCHEMA'));
            $db_data = $db_combined_data;
            defined('H_N_PRODUCTS_USSFPPBE') OR define('H_N_PRODUCTS_USSFPPBE', $db_data[0]['host_name']);
            defined('U_N_PRODUCTS_USSFPPBE') OR define('U_N_PRODUCTS_USSFPPBE', $db_data[0]['user_name']);
            defined('P_W_PRODUCTS_USSFPPBE') OR define('P_W_PRODUCTS_USSFPPBE', $db_data[0]['password']);
        }
    }
    else {
        defined('H_N_PRODUCTS_SSO') OR define('H_N_PRODUCTS_SSO', getenv("SSO_DB_HOST"));
        defined('U_N_PRODUCTS_SSO') OR define('U_N_PRODUCTS_SSO', getenv("SSO_DB_USERNAME"));
        defined('P_W_PRODUCTS_SSO') OR define('P_W_PRODUCTS_SSO', getenv("SSO_DB_PASSWORD"));

        if(getenv('SSO_FORCE_SCHEMA')!=''){
            defined('FORCE_SCHEMA') || define('FORCE_SCHEMA', getenv('SSO_FORCE_SCHEMA'));

            defined('H_N_PRODUCTS_FORCE') OR define('H_N_PRODUCTS_FORCE', getenv("SSO_FORCE_HOST"));
            defined('U_N_PRODUCTS_FORCE') OR define('U_N_PRODUCTS_FORCE', getenv("SSO_FORCE_USERNAME"));
            defined('P_W_PRODUCTS_FORCE') OR define('P_W_PRODUCTS_FORCE', getenv("SSO_FORCE_PASSWORD"));
        }

        if(getenv('SSO_USAFPPBE_SCHEMA')!=''){
            defined('USAFPPBE_SCHEMA') || define('USAFPPBE_SCHEMA', getenv('SSO_USAFPPBE_SCHEMA'));
            defined('H_N_PRODUCTS_USAFPPBE') OR define('H_N_PRODUCTS_USAFPPBE', getenv("SSO_USAFPPBE_HOST"));
            defined('U_N_PRODUCTS_USAFPPBE') OR define('U_N_PRODUCTS_USAFPPBE', getenv("SSO_USAFPPBE_USERNAME"));
            defined('P_W_PRODUCTS_USAFPPBE') OR define('P_W_PRODUCTS_USAFPPBE', getenv("SSO_USAFPPBE_PASSWORD"));
        }

        if(getenv('SSO_ACTF_SCHEMA')!=''){
            defined('ACTF_SCHEMA') || define('ACTF_SCHEMA', getenv('SSO_ACTF_SCHEMA'));
            defined('H_N_PRODUCTS_ACTF') OR define('H_N_PRODUCTS_ACTF', getenv("SSO_ACTF_HOST"));
            defined('U_N_PRODUCTS_ACTF') OR define('U_N_PRODUCTS_ACTF', getenv("SSO_ACTF_USERNAME"));
            defined('P_W_PRODUCTS_ACTF') OR define('P_W_PRODUCTS_ACTF', getenv("SSO_ACTF_PASSWORD"));
        }

        if(getenv('SSO_USAFPPBE_SCHEMA')!=''){
            defined('USAFPPBE_SCHEMA') || define('USAFPPBE_SCHEMA', getenv('SSO_USAFPPBE_SCHEMA'));
            defined('H_N_PRODUCTS_USAFPPBE') OR define('H_N_PRODUCTS_USAFPPBE', getenv("SSO_USAFPPBE_HOST"));
            defined('U_N_PRODUCTS_USAFPPBE') OR define('U_N_PRODUCTS_USAFPPBE', getenv("SSO_USAFPPBE_USERNAME"));
            defined('P_W_PRODUCTS_USAFPPBE') OR define('P_W_PRODUCTS_USAFPPBE', getenv("SSO_USAFPPBE_PASSWORD"));
        }

        if(getenv('SSO_SLRD_SCHEMA')!=''){
            defined('SLRD_SCHEMA') || define('SLRD_SCHEMA', getenv('SSO_SLRD_SCHEMA'));
            defined('H_N_PRODUCTS_SLRD') OR define('H_N_PRODUCTS_SLRD', getenv('SSO_SLRD_HOST'));
            defined('U_N_PRODUCTS_SLRD') OR define('U_N_PRODUCTS_SLRD', getenv('SSO_SLRD_USERNAME'));
            defined('P_W_PRODUCTS_SLRD') OR define('P_W_PRODUCTS_SLRD', getenv('SSO_SLRD_PASSWORD'));
        }

        if(getenv('SSO_CAPDEV_SCHEMA')!=''){
            defined('CAPDEV_SCHEMA') || define('CAPDEV_SCHEMA', getenv('SSO_CAPDEV_SCHEMA'));
            defined('H_N_PRODUCTS_CAPDEV') OR define('H_N_PRODUCTS_CAPDEV', getenv('SSO_CAPDEV_HOST'));
            defined('U_N_PRODUCTS_CAPDEV') OR define('U_N_PRODUCTS_CAPDEV', getenv('SSO_CAPDEV_USERNAME'));
            defined('P_W_PRODUCTS_CAPDEV') OR define('P_W_PRODUCTS_CAPDEV', getenv('SSO_CAPDEV_PASSWORD'));
        }

        if(getenv('SSO_TRIAD_SCHEMA')!=''){
            defined('TRIAD_SCHEMA') || define('TRIAD_SCHEMA', getenv('SSO_TRIAD_SCHEMA'));
            defined('H_N_PRODUCTS_TRIAD') OR define('H_N_PRODUCTS_TRIAD', getenv('SSO_TRIAD_HOST'));
            defined('U_N_PRODUCTS_TRIAD') OR define('U_N_PRODUCTS_TRIAD', getenv('SSO_TRIAD_USERNAME'));
            defined('P_W_PRODUCTS_TRIAD') OR define('P_W_PRODUCTS_TRIAD', getenv('SSO_TRIAD_PASSWORD'));
        }

        if(getenv('SSO_COMPETITION_SCHEMA')!=''){
            defined('COMPETITION_SCHEMA') || define('COMPETITION_SCHEMA', getenv('SSO_COMPETITION_SCHEMA'));
            defined('H_N_PRODUCTS_COMPETITION') OR define('H_N_PRODUCTS_COMPETITION', getenv('SSO_COMPETITION_HOST'));
            defined('U_N_PRODUCTS_COMPETITION') OR define('U_N_PRODUCTS_COMPETITION', getenv('SSO_COMPETITION_USERNAME'));
            defined('P_W_PRODUCTS_COMPETITION') OR define('P_W_PRODUCTS_COMPETITION', getenv('SSO_COMPETITION_PASSWORD'));
        }

        if(getenv('SSO_THREAT_SCHEMA')!=''){
            defined('THREAT_SCHEMA') || define('THREAT_SCHEMA', getenv('SSO_THREAT_SCHEMA'));
            defined('H_N_PRODUCTS_THREAT') OR define('H_N_PRODUCTS_THREAT', getenv('SSO_THREAT_HOST'));
            defined('U_N_PRODUCTS_THREAT') OR define('U_N_PRODUCTS_THREAT', getenv('SSO_THREAT_USERNAME'));
            defined('P_W_PRODUCTS_THREAT') OR define('P_W_PRODUCTS_THREAT', getenv('SSO_THREAT_PASSWORD'));
        }

        if(getenv('SSO_WSS_SCHEMA')!=''){
            defined('WSS_SCHEMA') || define('WSS_SCHEMA', getenv('SSO_WSS_SCHEMA'));
            defined('H_N_PRODUCTS_WSS') OR define('H_N_PRODUCTS_WSS', getenv('SSO_WSS_HOST'));
            defined('U_N_PRODUCTS_WSS') OR define('U_N_PRODUCTS_WSS', getenv('SSO_WSS_HOST'));
            defined('P_W_PRODUCTS_WSS') OR define('P_W_PRODUCTS_WSS', getenv('SSO_WSS_HOST'));
        }
        if(getenv('SSO_MANPOWER_SCHEMA')!=''){
            defined('MANPOWER_SCHEMA') || define('MANPOWER_SCHEMA', getenv('SSO_MANPOWER_SCHEMA'));
            defined('H_N_PRODUCTS_MANPOWER') OR define('H_N_PRODUCTS_MANPOWER', getenv('SSO_MANPOWER_HOST'));
            defined('U_N_PRODUCTS_MANPOWER') OR define('U_N_PRODUCTS_MANPOWER', getenv('SSO_MANPOWER_HOST'));
            defined('P_W_PRODUCTS_MANPOWER') OR define('P_W_PRODUCTS_MANPOWER', getenv('SSO_MANPOWER_HOST'));
        }
        if(getenv('SSO_EAAFM_SCHEMA')!=''){
            defined('EAAFM_SCHEMA') || define('EAAFM_SCHEMA', getenv('SSO_EAAFM_SCHEMA'));
            defined('H_N_PRODUCTS_EAAFM') OR define('H_N_PRODUCTS_EAAFM', getenv('SSO_EAAFM_HOST'));
            defined('U_N_PRODUCTS_EAAFM') OR define('U_N_PRODUCTS_EAAFM', getenv('SSO_EAAFM_HOST'));
            defined('P_W_PRODUCTS_EAAFM') OR define('P_W_PRODUCTS_EAAFM', getenv('SSO_EAAFM_HOST'));
        }
        if(getenv('SSO_STRATEGICBASING_SCHEMA')!=''){
            defined('STRATEGICBASING_SCHEMA') || define('STRATEGICBASING_SCHEMA', getenv('SSO_STRATEGICBASING_SCHEMA'));
            defined('H_N_PRODUCTS_STRATEGICBASING') OR define('H_N_PRODUCTS_STRATEGICBASING', getenv('SSO_STRATEGICBASING_HOST'));
            defined('U_N_PRODUCTS_STRATEGICBASING') OR define('U_N_PRODUCTS_STRATEGICBASING', getenv('SSO_STRATEGICBASING_HOST'));
            defined('P_W_PRODUCTS_STRATEGICBASING') OR define('P_W_PRODUCTS_STRATEGICBASING', getenv('SSO_STRATEGICBASING_HOST'));
        }
        if(getenv('SSO_CSPI_SCHEMA')!=''){
            defined('CSPI_SCHEMA') || define('CSPI_SCHEMA', getenv('SSO_CSPI_SCHEMA'));
            defined('H_N_PRODUCTS_CSPI') OR define('H_N_PRODUCTS_CSPI', getenv('SSO_CSPI_HOST'));
            defined('U_N_PRODUCTS_CSPI') OR define('U_N_PRODUCTS_CSPI', getenv('SSO_CSPI_HOST'));
            defined('P_W_PRODUCTS_CSPI') OR define('P_W_PRODUCTS_CSPI', getenv('SSO_CSPI_HOST'));
        }
        if(getenv('SSO_OBLIGATIONEXPENDITURE_SCHEMA')!=''){
            defined('OBLIGATIONEXPENDITURE_SCHEMA') || define('OBLIGATIONEXPENDITURE_SCHEMA', getenv('SSO_OBLIGATIONEXPENDITURE_SCHEMA'));
            defined('H_N_PRODUCTS_OBLIGATIONEXPENDITURE') OR define('H_N_PRODUCTS_OBLIGATIONEXPENDITURE', getenv('SSO_OBLIGATIONEXPENDITURE_HOST'));
            defined('U_N_PRODUCTS_OBLIGATIONEXPENDITURE') OR define('U_N_PRODUCTS_OBLIGATIONEXPENDITURE', getenv('SSO_OBLIGATIONEXPENDITURE_USERNAME'));
            defined('P_W_PRODUCTS_OBLIGATIONEXPENDITURE') OR define('P_W_PRODUCTS_OBLIGATIONEXPENDITURE', getenv('SSO_OBLIGATIONEXPENDITURE_PASSWORD'));
        }
        if(getenv('SSO_FH_SCHEMA')!=''){
            defined('FH_SCHEMA') || define('FH_SCHEMA', getenv('SSO_FH_SCHEMA'));
            defined('H_N_PRODUCTS_FH') OR define('H_N_PRODUCTS_FH', getenv('SSO_FH_HOST'));
            defined('U_N_PRODUCTS_FH') OR define('U_N_PRODUCTS_FH', getenv('SSO_FH_USERNAME'));
            defined('P_W_PRODUCTS_FH') OR define('P_W_PRODUCTS_FH', getenv('SSO_FH_PASSWORD'));
        }
        if(getenv('SSO_COMBINED_SCHEMA')!=''){
            defined('COMBINED_SCHEMA') || define('COMBINED_SCHEMA', getenv('SSO_COMBINED_SCHEMA'));
            defined('H_N_PRODUCTS_COMBINED') || define('H_N_PRODUCTS_COMBINED', getenv('SSO_COMBINED_HOST'));
            defined('U_N_PRODUCTS_COMBINED') || define('U_N_PRODUCTS_COMBINED', getenv('SSO_COMBINED_USERNAME'));
            defined('P_W_PRODUCTS_COMBINED') || define('P_W_PRODUCTS_COMBINED', getenv('SSO_COMBINED_PASSWORD'));
        }

        if(getenv('SSO_USSFPPBE_SCHEMA')!=''){
            defined('USSFPPBE_SCHEMA') || define('USSFPPBE_SCHEMA', getenv('SSO_USSFPPBE_SCHEMA'));
            defined('H_N_PRODUCTS_USSFPPBE') OR define('H_N_PRODUCTS_USSFPPBE', getenv("SSO_USSFPPBE_HOST"));
            defined('U_N_PRODUCTS_USSFPPBE') OR define('U_N_PRODUCTS_USSFPPBE', getenv("SSO_USSFPPBE_USERNAME"));
            defined('P_W_PRODUCTS_USSFPPBE') OR define('P_W_PRODUCTS_USSFPPBE', getenv("SSO_USSFPPBE_PASSWORD"));
        }

        if (getenv('SSO_KG_SCHEMA') != '') {
            defined('KG_SCHEMA') || define('KG_SCHEMA', getenv('SSO_KG_SCHEMA'));
            defined('H_N_PRODUCTS_KG') || define('H_N_PRODUCTS_KG', getenv('SSO_KG_HOST'));
            defined('U_N_PRODUCTS_KG') || define('U_N_PRODUCTS_KG', getenv('SSO_KG_USERNAME'));
            defined('P_W_PRODUCTS_KG') || define('P_W_PRODUCTS_KG', getenv('SSO_KG_PASSWORD'));
        }
        
        if (getenv('SSO_OOB_SCHEMA') != '') {
            defined('OOB_SCHEMA') || define('OOB_SCHEMA', getenv('SSO_OOB_SCHEMA'));
            defined('H_N_PRODUCTS_OOB') || define('H_N_PRODUCTS_OOB', getenv('SSO_OOB_HOST'));
            defined('U_N_PRODUCTS_OOB') || define('U_N_PRODUCTS_OOB', getenv('SSO_OOB_USERNAME'));
            defined('P_W_PRODUCTS_OOB') || define('P_W_PRODUCTS_OOB', getenv('SSO_OOB_PASSWORD'));
        }

        if(getenv('SSO_USSFPPBE_SCHEMA')!=''){
            defined('USSFPPBE_SCHEMA') || define('USSFPPBE_SCHEMA', getenv('SSO_USSFPPBE_SCHEMA'));
            defined('H_N_PRODUCTS_USSFPPBE') OR define('H_N_PRODUCTS_USSFPPBE', getenv("SSO_USSFPPBE_HOST"));
            defined('U_N_PRODUCTS_USSFPPBE') OR define('U_N_PRODUCTS_USSFPPBE', getenv("SSO_USSFPPBE_USERNAME"));
            defined('P_W_PRODUCTS_USSFPPBE') OR define('P_W_PRODUCTS_USSFPPBE', getenv("SSO_USSFPPBE_PASSWORD"));
        }

        if(getenv('SSO_SOCOM_SCHEMA')!=''){
            defined('SOCOM_SCHEMA') || define('SOCOM_SCHEMA', getenv('SSO_SOCOM_SCHEMA'));
            defined('H_N_PRODUCTS_SOCOM') OR define('H_N_PRODUCTS_SOCOM', getenv("SSO_SOCOM_HOST"));
            defined('U_N_PRODUCTS_SOCOM') OR define('U_N_PRODUCTS_SOCOM', getenv("SSO_SOCOM_USERNAME"));
            defined('P_W_PRODUCTS_SOCOM') OR define('P_W_PRODUCTS_SOCOM', getenv("SSO_SOCOM_PASSWORD"));
        }
    }
}


defined('SSO_CAPDEV_URL')        || define('SSO_CAPDEV_URL', getenv('SSO_CAPDEV_URL'));
defined('SSO_CAPDEV_HOME')        || define('SSO_CAPDEV_HOME', getenv('SSO_CAPDEV_HOME'));

defined('SSO_COMPETITION_URL')   || define('SSO_COMPETITION_URL',  getenv('SSO_COMPETITION_URL'));
defined('SSO_COMPETITION_HOME')   || define('SSO_COMPETITION_HOME',  getenv('SSO_COMPETITION_HOME'));

defined('SSO_USAFPPBE_URL')        || define('SSO_USAFPPBE_URL',  getenv('SSO_USAFPPBE_URL'));
defined('SSO_USAFPPBE_HOME')        || define('SSO_USAFPPBE_HOME',  getenv('SSO_USAFPPBE_HOME'));

defined('SSO_SLRD_URL')        || define('SSO_SLRD_URL',  getenv('SSO_SLRD_URL'));
defined('SSO_SLRD_HOME')        || define('SSO_SLRD_HOME',  getenv('SSO_SLRD_HOME'));

defined('SSO_TRIAD_URL')        || define('SSO_TRIAD_URL',  getenv('SSO_TRIAD_URL'));
defined('SSO_TRIAD_HOME')        || define('SSO_TRIAD_HOME',  getenv('SSO_TRIAD_HOME'));

defined('SSO_FORCE_URL')        || define('SSO_FORCE_URL',  getenv('SSO_FORCE_URL'));
defined('SSO_FORCE_HOME')        || define('SSO_FORCE_HOME',  getenv('SSO_FORCE_HOME'));

defined('SSO_ACTF_URL')        || define('SSO_ACTF_URL',  getenv('SSO_ACTF_URL'));
defined('SSO_ACTF_HOME')        || define('SSO_ACTF_HOME',  getenv('SSO_ACTF_HOME'));

defined('SSO_THREAT_URL')        || define('SSO_THREAT_URL',  getenv('SSO_THREAT_URL'));
defined('SSO_THREAT_HOME')        || define('SSO_THREAT_HOME',  getenv('SSO_THREAT_HOME'));

defined('SSO_WSS_URL')        || define('SSO_WSS_URL',  getenv('SSO_WSS_URL'));
defined('SSO_WSS_HOME')        || define('SSO_WSS_HOME',  getenv('SSO_WSS_HOME'));

defined('SSO_MANPOWER_URL')        || define('SSO_MANPOWER_URL',  getenv('SSO_MANPOWER_URL'));
defined('SSO_MANPOWER_HOME')        || define('SSO_MANPOWER_HOME',  getenv('SSO_MANPOWER_HOME'));

defined('SSO_STRATEGICBASING_URL')        || define('SSO_STRATEGICBASING_URL',  getenv('SSO_STRATEGICBASING_URL'));
defined('SSO_STRATEGICBASING_HOME')        || define('SSO_STRATEGICBASING_HOME',  getenv('SSO_STRATEGICBASING_HOME'));

defined('SSO_EAAFM_URL')        || define('SSO_EAAFM_URL', getenv('SSO_EAAFM_URL'));
defined('SSO_EAAFM_HOME')        || define('SSO_EAAFM_HOME', getenv('SSO_EAAFM_HOME'));

defined('SSO_CSPI_HOME')        || define('SSO_CSPI_HOME', getenv('SSO_CSPI_HOME'));
defined('SSO_CSPI_URL')        || define('SSO_CSPI_URL', getenv('SSO_CSPI_URL'));

defined('SSO_OBLIGATIONEXPENDITURE_URL') || define('SSO_OBLIGATIONEXPENDITURE_URL',
getenv('SSO_OBLIGATIONEXPENDITURE_URL'));
defined('SSO_OBLIGATIONEXPENDITURE_HOME') || define('SSO_OBLIGATIONEXPENDITURE_HOME',
getenv('SSO_OBLIGATIONEXPENDITURE_HOME'));

defined('SSO_FH_URL')        || define('SSO_FH_URL', getenv('SSO_FH_URL'));
defined('SSO_FH_HOME')        || define('SSO_FH_HOME', getenv('SSO_FH_HOME'));

defined('SSO_COMBINED_URL')        || define('SSO_COMBINED_URL', getenv('SSO_COMBINED_URL'));
defined('SSO_COMBINED_HOME')        || define('SSO_COMBINED_HOME', getenv('SSO_COMBINED_HOME'));

defined('SSO_KG_URL')        || define('SSO_KG_URL', getenv('SSO_KG_URL'));
defined('SSO_KG_HOME')        || define('SSO_KG_HOME', getenv('SSO_KG_HOME'));

defined('SSO_OOB_URL')        || define('SSO_OOB_URL', getenv('SSO_OOB_URL'));
defined('SSO_OOB_HOME')        || define('SSO_OOB_HOME', getenv('SSO_OOB_HOME'));

defined('SSO_USSFPPBE_URL')        || define('SSO_USSFPPBE_URL',  getenv('SSO_USSFPPBE_URL'));
defined('SSO_USSFPPBE_HOME')        || define('SSO_USSFPPBE_HOME',  getenv('SSO_USSFPPBE_HOME'));

defined('SSO_SOCOM_URL')        || define('SSO_SOCOM_URL',  getenv('SSO_SOCOM_URL'));
defined('SSO_SOCOM_HOME')        || define('SSO_SOCOM_HOME',  getenv('SSO_SOCOM_HOME'));

defined('SSO_1')        || define('SSO_1', 'ACTF');
defined('SSO_2')        || define('SSO_2', 'USAFPPBE');
defined('SSO_3')        || define('SSO_3', 'SLRD');
defined('SSO_4')        || define('SSO_4', 'CAPDEV');
defined('SSO_5')        || define('SSO_5', 'TRIAD');
defined('SSO_6')        || define('SSO_6', 'COMPETITION');
defined('SSO_7')        || define('SSO_7', 'KNOWONE');
defined('SSO_8')        || define('SSO_8', 'WSS');
defined('SSO_9')        || define('SSO_9', 'MANPOWER');
defined('SSO_10')        || define('SSO_10', 'STRATEGICBASING');
defined('SSO_11')        || define('SSO_11', 'EAAFM');
defined('SSO_12')        || define('SSO_12', 'CSPI');
defined('SSO_13')        || define('SSO_13', 'OBLIGATIONEXPENDITURE');
defined('SSO_14')        || define('SSO_14', 'FH');
defined('SSO_15')        || define('SSO_15', 'COMBINED');
defined('SSO_16')        || define('SSO_16', 'KG');
defined('SSO_17')        || define('SSO_17', 'OOB');
defined('SSO_18')        || define('SSO_18', 'USSFPPBE');
defined('SSO_19')        || define('SSO_19', 'SOCOM');

defined('HAS_SUBAPPS') || define('HAS_SUBAPPS', TRUE);

defined('APPLICATION_JSON')        || define('APPLICATION_JSON', 'application/json');

require_once(realpath(__DIR__ . '/project_config/project_constants.php'));