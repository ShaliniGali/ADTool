<?php
require_once(realpath( __DIR__ . '/../../system/dotenv/autoloader.php'));
require_once(realpath(__DIR__ . '/../simplesamlphp/lib/_autoload.php'));
require_once(realpath(__DIR__ . '/../simplesamlphp/lib/SimpleSAML/Utils/RBEncrypt.php'));

$base_url = parse_url(($_SERVER['SCRIPT_URI']));
$make_url = $base_url['scheme']."://".$base_url['host'].":".$base_url['port']."/"; 

/**
 * 
 * Start Session For Redirect
 * 
 */
session_start();
$_SESSION['SSO_Setup'] = True;

defined('rhombus_encrypt_env') OR define('rhombus_encrypt_env', FALSE);
defined('rhombus_encrypt_env_path') OR define('rhombus_encrypt_env_path', '../env_encryption');
defined('rhombus_encrypt_env_file') OR define('rhombus_encrypt_env_file', '.env.enc');
defined('rhombus_encrypt_env_salt') OR define('rhombus_encrypt_env_salt', 'EncryptionKey.bin');
if(rhombus_encrypt_env == TRUE)
	$dotenv = new Dotenv\Dotenv(rhombus_encrypt_env_path, rhombus_encrypt_env_file);
else
	$dotenv = new Dotenv\Dotenv("/");
$dotenv->load();
define('BASEPATH', "SAML_include");
require_once(realpath(__DIR__ . '/../config/constants.php'));

$_SERVER['PATH_INFO'] = '/' . md5($make_url);
$_REQUEST['output'] = 'xhtml';
$_REQUEST['rbsp'] = 'metadata';
require_once('../simplesamlphp/lib/_autoload.php');
require_once('../simplesamlphp/modules/saml/www/sp/metadata.php');

try {
    $auth = new \SimpleSAML\Auth\Simple(md5($make_url));
    $auth->requireAuth();
    \SimpleSAML\Session::getSessionFromRequest()->cleanup();
} catch (Exception $e) {
    $errorCode = 'AUTHSOURCEERROR';
    $config = SimpleSAML\Configuration::getInstance();
    $error = new \SimpleSAML\Error\Rb_Error($errorCode);
    $error->capture_error($config, array(
        'errorCode' => $errorCode,
        'dictTitle' => $error->getDictTitle(),
        'dictDescr' => $error->getDictDescr(),
        'parameters' => array(
            '%AUTHSOURCE%' => $make_url,
            '%REASON%' => $e->getMessage()
        )
    ));
    exit;
}

if ($auth->isAuthenticated()) {
    $result = $auth->getAttributes();
    $email = null;
    if (isset($result['urn:oid:0.9.2342.19200300.100.1.3'])) {
        $email = $result['urn:oid:0.9.2342.19200300.100.1.3'][0];
    } elseif (isset($result['email'])) {
        $email = $result['email'][0];
    } else if (isset($result['mail'])) {
        $email = $result['mail'][0];
    }

    $rbenc = new \SimpleSAML\Utils\RBEncrypt();
    if (!isset($email)) {
        /**
         * 
         * Clean SAML session before redirecting to the next page avoiding dead loop
         * 
         */

        // Destroy session
        header('Location: ' . $make_url . 'sso/failure/'. $rbenc->encrypt($email));
        exit;
    }
    
    if (UI_SIPR_ENVIRONMENT === TRUE) {
        $SAML_JSON_DATA = array(
            array(
                'host_name' => getenv('RB_SAML_USERS_HOST'),
                'user_name' => getenv('RB_SAML_DB_USERNAME'),
                'password' => getenv('RB_SAML_DB_PASSWORD'),
                'port' => getenv('RB_SAML_USERS_PORT')
            )
        );
    } else {
        require_once(realpath(__DIR__ . '/../helpers/dbcredentials_helper.php'));

        defined('RHOMBUS_DATABASES') OR define('RHOMBUS_DATABASES', RB_SAML_USERS_DB_FLAG);
        defined('RHOMBUS_BASE_URL') OR define('RHOMBUS_BASE_URL', $make_url);
        defined('RHOMBUS_DB_API_KEY') OR define('RHOMBUS_DB_API_KEY', getenv('RB_DB_API_KEY'));
    
        $SAML_JSON_DATA = loadDBCredentials();
        $SAML_JSON_DATA[0]['port'] = 3306;
    }

    $dsn = array(
        'host' => $SAML_JSON_DATA[0]['host_name'],
        'port' => $SAML_JSON_DATA[0]['port'],
        'dbname' => RB_SAML_USERS_DATABASENAME
    );
    
    $config = array(
        'dsn' => 'mysql:' . http_build_query($dsn,'',';'),
        'username' => $SAML_JSON_DATA[0]['user_name'],
        'password' => $SAML_JSON_DATA[0]['password']
    );
    $dbauth = new SimpleSAML\Module\rbauth\Auth\Source\RbAuth(array('AuthId' => 'RbAuth'), $config);
    
    $finalResult = $dbauth->login($email, 'dummy');

    $redirect = $finalResult === 'Success' ? 'success' : 'failure';
    /**
     * 
     * Clean SAML session before redirecting to the next page avoiding dead loop
     * 
     */
    header('Location:' . $make_url . 'sso/' . $redirect . '/'. $rbenc->encrypt($email));
    exit;
} else {
    /**
     * 
     * Clean SAML session before redirecting to the next page avoiding dead loop
     * 
     */
    header('Location:' . $make_url . 'sso/IDPFailure');
    exit;
}
?>
