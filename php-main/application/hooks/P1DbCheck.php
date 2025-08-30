<?php
defined('BASEPATH') || exit('No direct script access allowed');

class P1DbCheck {
    private $CI;
    private $currClass;

    /**
     * All controllers excluded from the user P1 DB check should be defined in the
     * $excludedControllers array.
     */
    private $excludedControllers = array();

    public function __construct() {
        $this->CI = &get_instance();
        $this->currClass = strtolower($this->CI->router->class);
        foreach($this->excludedControllers as &$c) {
            $c = strtolower($c);
        }
    }

    public function checkStatus() {
        if (
            P1_FLAG === FALSE ||
            !defined('APP_VERSION_DATABASE') ||
            !defined('APP_VERSION') ||
            in_array($this->currClass, $this->excludedControllers)
        ) {
            return;
        }

        if ($this->maintenance_check() !== 1) {
            show_error('Site is under Maintenance', 503);
        }
    }

    private function maintenance_check() {
        $db = $this->CI->load->database(APP_VERSION_DATABASE['db'], TRUE);

        if(!in_array('LOOKUP_APP_VERSION',$db->list_tables())){
            return 1;
        }
        $result = $db->select('STATUS')
            ->from(APP_VERSION_DATABASE['table'])
            ->where('APP_VERSION', APP_VERSION)->get()->row_array(0)['STATUS'] ?? false;
    
        if ($result === false) {
            log_message('error', 'Database Version not found');

            $result = 0;
        }

        return $result;
    }
}