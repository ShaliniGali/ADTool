<?php

class Migration_Usr_Lookup_Criteria_Weights extends CI_Migration
{
    private $table = 'USR_LOOKUP_CRITERIA_WEIGHTS';

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
            'WEIGHT_ID' => [
                'type' => "int"
            ],
            'TITLE' => [
                'type' => "varchar",
            'constraint' => "45"
            ],
            'DESCRIPTION' => [
                'type' => "json"
            ],
            'SESSION' => [
                'type' => "json"
            ],
            'USER_ID' => [
                'type' => "int"
            ],
            'DELETED' => [
                'type' => "tinyint",
            'constraint' => "1"
            ],
            'TIMESTAMP' => [
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
