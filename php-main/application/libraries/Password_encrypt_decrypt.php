<?php
//
// created July 13 2020 Ian
//

defined('BASEPATH') || exit('No direct script access allowed');
#[AllowDynamicProperties]
class Password_encrypt_decrypt {

    private const PBKDF2_ITERATIONS = ENCRYPT_DECRYPT_PASSWORD_ITERATIONS;
    private const ALGORITHM_KEY_SIZE = ENCRYPTION_SIZE;

    public function encrypt($string){

        $result = array();
        //
        // Encoding need only password string
        //
        //
        // Generate a 128-bit salt using a CSPRNG.
        //
        $generate_cypto_salt =  base64_encode(random_bytes(2048));
        $password = base64_encode(hash_pbkdf2("sha256", $string, base64_decode($generate_cypto_salt), self::PBKDF2_ITERATIONS, self::ALGORITHM_KEY_SIZE, true));

        $result['password'] = $password;
        $result['salt'] = $generate_cypto_salt;

        return $result;
    }

    //
    // Decoding needs array, password and salt
    //
    public function decrypt($string, $salt){

        $password = base64_encode(hash_pbkdf2("sha256", $string, base64_decode($salt), self::PBKDF2_ITERATIONS, self::ALGORITHM_KEY_SIZE, true));

        return $password;
    }

    /**
     * Verify a password against a stored hash
     * 
     * @param string $password The plain text password to verify
     * @param string $stored_hash The stored hash to compare against
     * @param string $salt The salt used to generate the stored hash
     * @return bool True if password matches, false otherwise
     */
    public function verify_password($password, $stored_hash, $salt) {
        // Generate hash from input password using the stored salt
        $input_hash = base64_encode(hash_pbkdf2("sha256", $password, base64_decode($salt), self::PBKDF2_ITERATIONS, self::ALGORITHM_KEY_SIZE, true));
        
        // Compare the generated hash with stored hash
        return hash_equals($stored_hash, $input_hash);
    }

    /**
     * @author Moheb, September 3rd, 2020
     * Returns true if the password is strong; otherwise, returns false.
     * 
     * A password is strong if and only if it satisfies all of the following rules:
     *  i) at least one upper case letter (A – Z)
     *  ii) at least one lower case letter(a-z)
     *  iii) at least one digit (0 – 9)
     *  iv) at least one special characters of !@#$%&*()
     * 
     * @param string $password
     * @return bool
     */
    public function isStrongPassword($password) {
        return preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/', $password);
    }

    /**
     * @author Moheb, September 3rd, 2020
     * 
     * Returns true if $password is exactly the same as $password_confirmation; otherwise, returns false.
     * 
     * @param string $password
     * @param string $password_confirmation
     * @return bool
     */
    public function isValidPasswordConfirmation($password, $password_confirmation) {
        return $password === $password_confirmation;
    }
}