<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('encrypted_string')) {
    //
    // Sumit, 11 February 2020
    //
    function encrypted_string($string,$type){
        $ci = &get_instance();
        if($type=="decode"){
            $result = json_decode($ci->encryption->decrypt(base64_decode($string)),true);
        }
        if($type=="encode"){
            $result = trim(base64_encode($ci->encryption->encrypt(json_encode($string))));
            $result = str_replace("=", "", $result);
        }
        return $result;
    }
}