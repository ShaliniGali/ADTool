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

if (!function_exists('auth_aoad_role_guest')) {
    function auth_aoad_role_guest() {
        return get_rbac()->is_guest();
    }
}

if (!function_exists('auth_aoad_role_restricted')) {
    function auth_aoad_role_restricted() {
        return get_rbac()->is_restricted();
    }
}

if (!function_exists('auth_aoad_role_user')) {
    function auth_aoad_role_user() {
        return get_rbac()->is_user();
    }
}

if (!function_exists('auth_aoad_role_admin')) {
    function auth_aoad_role_admin() {
        return get_rbac()->is_admin();
    }
}

if (!function_exists('auth_aoad_reset_roles')) {
    function auth_aoad_reset_roles() {
        get_rbac()->reset_user();
    }
}