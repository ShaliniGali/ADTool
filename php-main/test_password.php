<?php
define('ENCRYPT_DECRYPT_PASSWORD_ITERATIONS', 10000);
define('ENCRYPTION_SIZE', 32);

require_once('application/libraries/Password_encrypt_decrypt.php');

$password_encrypt = new Password_encrypt_decrypt();
$result = $password_encrypt->encrypt('password');

echo "Hash: " . $result['password'] . "\n";
echo "Salt: " . $result['salt'] . "\n";
?>
