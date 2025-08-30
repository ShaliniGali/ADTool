<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('fetch_user_ip')) {
    //
    // Sumit 8 May 2020
    //
	function fetch_user_ip() {		
        $userIP =   'UNKNOWN';
        if(isset($_SERVER['HTTP_CLIENT_IP'])){
            $userIP =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $userIP =   $_SERVER['HTTP_X_FORWARDED_FOR'];
        }elseif(isset($_SERVER['HTTP_X_FORWARDED'])){
            $userIP =   $_SERVER['HTTP_X_FORWARDED'];
        }elseif(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])){
            $userIP =   $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])){
            $userIP =   $_SERVER['HTTP_FORWARDED_FOR'];
        }elseif(isset($_SERVER['HTTP_FORWARDED'])){
            $userIP =   $_SERVER['HTTP_FORWARDED'];
        }elseif(isset($_SERVER['REMOTE_ADDR'])){
            $userIP =   $_SERVER['REMOTE_ADDR'];
        }
        return $userIP;
    }
}