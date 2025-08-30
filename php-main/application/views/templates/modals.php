<?php
if (realpath(dirname(__FILE__) . '/modals/' . $modal_id . '.php')) {
    $this->load->view('templates/modals/' . $modal_id, $this->_ci_cached_vars);
}
?>