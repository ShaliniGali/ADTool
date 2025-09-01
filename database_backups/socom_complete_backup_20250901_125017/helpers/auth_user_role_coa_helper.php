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

if (!function_exists('is_dev_bypass_enabled')) {
    function is_dev_bypass_enabled() {
        // Check for development bypass environment variable
        $dev_bypass = getenv('SOCOM_DEV_BYPASS_AUTH') ?: getenv('SOCOM_DEV_BYPASS_AUTH');
        return $dev_bypass === 'TRUE' || $dev_bypass === 'true' || $dev_bypass === '1';
    }
}

if (!function_exists('auth_coa_role_guest')) {
    function auth_coa_role_guest() {
        // If dev bypass is enabled, return false (not guest)
        if (is_dev_bypass_enabled()) {
            return false;
        }
        $rbac = get_rbac();
        return $rbac->is_guest();
    }
}

if (!function_exists('auth_coa_role_restricted')) {
    function auth_coa_role_restricted() {
        // If dev bypass is enabled, return false (not restricted)
        if (is_dev_bypass_enabled()) {
            return false;
        }
        return get_rbac()->is_restricted();
    }
}

if (!function_exists('auth_coa_role_user')) {
    function auth_coa_role_user() {
        return get_rbac()->is_user();
    }
}

if (!function_exists('auth_coa_role_admin')) {
    function auth_coa_role_admin() {
        return get_rbac()->is_admin();
    }
}

if (!function_exists('auth_coa_reset_roles')) {
    function auth_coa_reset_roles() {
        get_rbac()->reset_user();
    }
}