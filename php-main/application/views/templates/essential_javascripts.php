<?php

$js_files = array();
    $js_files['clipboard'] = ['clipboard.min.js','global'];
    $js_files['popper'] = ['popper.min.js','global'];
    $js_files['jquery'] = ['jquery.min.js','global'];
    $js_files['purpose_29'] = ['jquery-ui.min.js','global']; //here to avoid a conflict
    $js_files['bootstrap'] = ['bootstrap.bundle.js','global'];
    $js_files['sanitize_html.js'] = ['sanitize-html.js','global'];
    $js_files['tilt'] = ['tilt.jquery.js','global'];
    $js_files['cryptoJS'] = ['cryptoJS.js','global'];
    $js_files['rhombus'] = ['essential/rhombus.js','custom'];
    $js_files['d3'] = ['d3.min.js','global'];
    $js_files['carbon'] = ['carbon.min.js', 'global'];
    
    if (isset($this->session->userdata['logged_in'])) {

        defined('RHOMBUS_SSO_TIMEOUT') or define('RHOMBUS_SSO_TIMEOUT', 60);
    
        echo '<script>const timeout_max_time = ' . RHOMBUS_SSO_TIMEOUT . '</script>';
    
        $base = 'timeout';
        $data = array(); // Reset the data array.... Just to be safe..
        $data['modal_id'] = "timeout_modal";
        $this->load->view('templates/modals', $data);
    
        $js_files['rb_keep_login'] = ['essential/rb_keep_login.js', 'custom'];
    }
    

    $CI =& get_instance();
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);

    if (!isset($this->session->userdata['logged_in'])) {
        echo '<script>rhombus_dark_mode("dark","switch_false"); </script>';
    }

?>