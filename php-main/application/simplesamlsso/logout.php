<?php
require_once(realpath( __DIR__ . '/../../system/dotenv/autoloader.php'));
require_once(realpath(__DIR__ . '/../simplesamlphp/lib/_autoload.php'));

$base_url = parse_url(($_SERVER['SCRIPT_URI']));
$make_url = $base_url['scheme']."://".$base_url['host'].":".$base_url['port']."/";

defined('rhombus_encrypt_env') OR define('rhombus_encrypt_env', FALSE);
defined('rhombus_encrypt_env_path') OR define('rhombus_encrypt_env_path', '../env_encryption');
defined('rhombus_encrypt_env_file') OR define('rhombus_encrypt_env_file', '.env.enc');
defined('rhombus_encrypt_env_salt') OR define('rhombus_encrypt_env_salt', 'EncryptionKey.bin');
if(rhombus_encrypt_env == TRUE)
	$dotenv = new Dotenv\Dotenv(rhombus_encrypt_env_path, rhombus_encrypt_env_file);
else
	$dotenv = new Dotenv\Dotenv("/");
$dotenv->load();

$auth = new \SimpleSAML\Auth\Simple(md5($make_url));
$auth->logout('/');
\SimpleSAML\Session::getSessionFromRequest()->cleanup();
?>
