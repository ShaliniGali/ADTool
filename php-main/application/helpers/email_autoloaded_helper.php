<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('isValidEmailDomain'))
{
    /**
     * @author Moheb, September 2nd, 2020
     * 
     * Validates a given email's domain name. Returns true if valid; otherwise, returns false.
     * An email's domain name is valid if it is in the VALID_EMAIL_DOMAINS array (@see constants.php).
     * Otherwise, the email is invalid.
     * 
     * @param string
     * @return bool
     */
    function isValidEmailDomain($email)
    {
        return (RHOMBUS_EMAIL_DOMAIN == "FALSE") || in_array(strtolower(substr($email, strrpos($email, '@') + 1)), VALID_EMAIL_DOMAINS);
    }   
}
