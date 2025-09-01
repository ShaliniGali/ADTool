<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CSRF Helper
 * 
 * Provides CSRF validation functions that work across different environments
 */

/**
 * Validate CSRF token for AJAX endpoints
 * 
 * @param CI_Controller $controller The controller instance
 * @return bool True if validation passes or is not required, false if validation fails
 */
function validate_csrf_token($controller) {
    // Only validate CSRF in production environment
    if (ENVIRONMENT !== 'production') {
        return true;
    }
    
    $csrf_token = $controller->input->post('rhombus_token');
    if (!$csrf_token || $csrf_token !== $controller->security->get_csrf_hash()) {
        $controller->output->set_status_header(403)
                           ->set_content_type('application/json')
                           ->set_output(json_encode([
                               'error' => 'Invalid or missing CSRF token',
                               'success' => false
                           ]));
        return false;
    }
    
    return true;
}

/**
 * Get CSRF token for frontend use
 * 
 * @param CI_Controller $controller The controller instance
 * @return string The CSRF token
 */
function get_csrf_token($controller) {
    return $controller->security->get_csrf_hash();
}

/**
 * Get CSRF token name for frontend use
 * 
 * @param CI_Controller $controller The controller instance
 * @return string The CSRF token name
 */
function get_csrf_token_name($controller) {
    return $controller->security->get_csrf_token_name();
}
