<?php

class Migration_Lookup_Storm extends CI_Migration
{
    private $table = 'LOOKUP_STORM';

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
                'type' => "varchar",
            'constraint' => "25"
            ],
            'PROGRAM_GROUP' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'CAPABILITY_SPONSOR_CODE' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'ACCESS_TYPE' => [
                'type' => "varchar",
            'constraint' => "25"
            ],
            'SA_SCORE' => [
                'type' => "bigint"
            ],
            'ID_SC_SCORE' => [
                'type' => "bigint"
            ],
            'M_SCORE' => [
                'type' => "bigint"
            ],
            'TOTAL_SCORE' => [
                'type' => "bigint"
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
