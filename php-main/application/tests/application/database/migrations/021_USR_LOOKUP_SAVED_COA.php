<?php

class Migration_Usr_Lookup_Saved_Coa extends CI_Migration
{
    private $table = 'USR_LOOKUP_SAVED_COA';

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
            'COA_VALUES' => [
                'type' => "json"
            ],
            'OPTIMIZER_INPUT' => [
                'type' => "json"
            ],
            'OVERRIDE_TABLE_SESSION' => [
                'type' => "json"
            ],
            'OVERRIDE_TABLE_METADATA' => [
                'type' => "json"
            ],
            'OVERRIDE_FORM_SESSION' => [
                'type' => "json"
            ],
            'USER_ID' => [
                'type' => "int"
            ],
            'CREATED_DATETIME' => [
                'type' => "datetime"
            ],
            'IS_DELETED' => [
                'type' => "tinyint",
            'constraint' => "1"
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
