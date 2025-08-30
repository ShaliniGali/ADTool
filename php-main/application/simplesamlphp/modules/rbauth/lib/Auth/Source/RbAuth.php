<?php
namespace SimpleSAML\Module\rbauth\Auth\Source;
use PDO;

class RbAuth extends \SimpleSAML\Module\core\Auth\UserPassBase {
    private $dsn;

    /* The database username, password & options. */
    private $username;
    private $password;
    private $options;

    public function __construct($info, $config) {
        parent::__construct($info, $config);

        if (!is_string($config['dsn'])) {
            throw new Exception('Missing or invalid dsn option in config.');
        }
        $this->dsn = $config['dsn'];
        if (!is_string($config['username'])) {
            throw new Exception('Missing or invalid username option in config.');
        }
        $this->username = $config['username'];
        if (!is_string($config['password'])) {
            throw new Exception('Missing or invalid password option in config.');
        }
        $this->password = $config['password'];
        if (isset($config['options'])) {
            if (!is_array($config['options'])) {
                throw new Exception('Missing or invalid options option in config.');
            }
            $this->options = $config['options'];
        }
    }

    public function login($username, $password) {

        /* Connect to the database. */
        $db = new PDO($this->dsn, $this->username, $this->password, $this->options);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /* Ensure that we are operating with UTF-8 encoding.
         * This command is for MySQL. Other databases may need different commands.
         */
        $db->exec("SET NAMES 'utf8'");

        /* With PDO we use prepared statements. This saves us from having to escape
         * the username in the database query.
         */
        $st = $db->prepare('SELECT `id`, `email` FROM `users_SSO` WHERE `email`=:username AND `status`="Active"');

        if (!$st->execute(['username' => $username])) {
            return 'Failure';
        }

        /* Retrieve the row from the database. */
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 'Failure';
        }

        return 'Success';
    }

}
