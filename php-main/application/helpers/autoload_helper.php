<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('autoload_all_helpers')) {
    /**
     * @author Kingston location: Moheb, Sumit, December 8th, 2020.
     * Autoloads all helper functions inside application/helpers with the sufffix _helper.php
     * 
     * @param void
     * @return void
     */
    function autoload_all_helpers() {
        $ci = &get_instance();
        $currFile = basename(__FILE__);
        $helpers = scandir(APPPATH . 'helpers');

        foreach ($helpers as $helper) {
            if (($helper !== $currFile) && (strpos($helper, 'autoloaded_helper') !== false)) {
                $ci->load->helper(str_replace('_helper', '', $helper));
            }
        }
    }
    autoload_all_helpers();
}