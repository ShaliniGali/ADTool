<?php

class Migration_Users extends CI_Migration
{
    private $table = 'users';

    public function __construct()
    {
        // Only accessible via CLI
        if (ENVIRONMENT !== 'testing' || is_cli() === false) {
            exit();
        }

        parent::__construct();
    }

    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => "int"
            ],
            'name' => [
                'type' => "text"
            ],
            'email' => [
                'type' => "text"
            ],
            'password' => [
                'type' => "text"
            ],
            'status' => [
                'type' => "text"
            ],
            'timestamp' => [
                'type' => "int"
            ],
            'account_type' => [
                'type' => "text"
            ],
            'login_attempts' => [
                'type' => "int"
            ],
            'login_layers' => [
                'type' => "text"
            ],
            'image' => [
                'type' => "longtext"
            ],
            'saltiness' => [
                'type' => "text"
            ],
        ]);

        if (!$this->db->table_exists($this->table)) {
            $this->dbforge->create_table($this->table);
        }
    }

    public function down()
    {
    }
}
