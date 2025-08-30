<?php

class Migration_Usr_Option_Scores extends CI_Migration
{
    private $table = 'USR_OPTION_SCORES';

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
            'ID' => [
                'type' => "int"
            ],
            'NAME' => [
                'type' => "varchar",
            'constraint' => "100"
            ],
            'DESCRIPTION' => [
                'type' => "varchar",
            'constraint' => "1024"
            ],
            'SESSION' => [
                'type' => "json"
            ],
            'PROGRAM_ID' => [
                'type' => "varchar",
            'constraint' => "40"
            ],
            'USER_ID' => [
                'type' => "int"
            ],
            'DELETED' => [
                'type' => "tinyint",
            'constraint' => "1"
            ],
            'CREATED_TIMESTAMP' => [
                'type' => "datetime"
            ],
            'UPDATED_TIMESTAMP' => [
                'type' => "datetime"
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
