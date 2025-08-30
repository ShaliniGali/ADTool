<?php
/**
 * General Helper class
 *
 *
 */
defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('get_rbac')) {
    function get_rbac() {
        $CI = &get_instance();
        return $CI->rbac_users;
    }
}

if (!function_exists('auth_zbt_summary_role_guest')) {
    function auth_zbt_summary_role_guest() {
        $rbac = get_rbac();
        return $rbac->is_guest();
    }
}

if (!function_exists('auth_zbt_summary_role_restricted')) {
    function auth_zbt_summary_role_restricted() {
        return get_rbac()->is_restricted();
    }
}

if (!function_exists('auth_zbt_summary_role_user')) {
    function auth_zbt_summary_role_user() {
        return get_rbac()->is_user();
    }
}

if (!function_exists('auth_zbt_summary_role_admin')) {
    function auth_zbt_summary_role_admin() {
        return get_rbac()->is_admin();
    }
}

if (!function_exists('auth_zbt_summary_reset_roles')) {
    function auth_zbt_summary_reset_roles() {
        get_rbac()->reset_user();
    }
}

if(!function_exists('auth_zbt_summary_role_cap')){
    function auth_zbt_summary_role_cap() {
        return get_rbac()-> get_user_groups();
    }
}