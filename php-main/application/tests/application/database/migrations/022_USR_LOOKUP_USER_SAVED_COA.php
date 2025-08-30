<?php

class Migration_Usr_Lookup_User_Saved_Coa extends CI_Migration
{
    private $table = 'USR_LOOKUP_USER_SAVED_COA';

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
            'COA_TITLE' => [
                'type' => "varchar",
            'constraint' => "100"
            ],
            'COA_DESCRIPTION' => [
                'type' => "varchar",
            'constraint' => "500"
            ],
            'USER_ID' => [
                'type' => "int"
            ],
            'SAVED_COA_ID' => [
                'type' => "int"
            ],
            'STATE' => [
                'type' => "text"
            ],
            'CREATED_DATETIME' => [
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
