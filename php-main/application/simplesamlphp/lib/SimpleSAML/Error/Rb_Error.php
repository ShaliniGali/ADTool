<?php
declare(strict_types=1);

namespace SimpleSAML\Error;

/**
 * Class hide the error with GetEnv
 *
 * @author Moheb/Sumit 26 Feb 2021
 * @package SimpleSAMLphp
 */

class Rb_Error extends Error {
    public static function capture_error(\SimpleSAML\Configuration $config, array $data) {
        $t = new \SimpleSAML\XHTML\Template($config, 'RBErrorView.php', 'errors');
        $t->data = array_merge($t->data, $data);
        $t->show();
        exit;
    }
}
