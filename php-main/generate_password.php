<?php
// Simple script to generate password hash for testing

// Define constants that the library expects
define('ENCRYPT_DECRYPT_PASSWORD_ITERATIONS', 10000);
define('ENCRYPTION_SIZE', 32);

// Include the password encryption library
require_once('application/libraries/Password_encrypt_decrypt.php');

$password_encrypt = new Password_encrypt_decrypt();
$result = $password_encrypt->encrypt('password');

echo "Password: password\n";
echo "Generated Hash: " . $result['password'] . "\n";
echo "Generated Salt: " . $result['salt'] . "\n";
?>
