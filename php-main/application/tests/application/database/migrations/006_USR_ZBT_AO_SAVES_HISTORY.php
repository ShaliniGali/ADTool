<?php

class Migration_Usr_Zbt_Ao_Saves_History extends CI_Migration
{
    private $table = 'USR_ZBT_AO_SAVES_HISTORY';

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
            'ZBT_AO_ID' => [
                'type' => "int"
            ],
            'AO_RECOMENDATION' => [
                'type' => "enum('Approve','Disapprove')"
            ],
            'AO_COMMENT' => [
                'type' => "varchar",
                'constraint' => "2000"
            ],
            'AO_USER_ID' => [
                'type' => "int"
            ],
            'CREATED_DATETIME' => [
                'type' => "datetime"
            ],
            'UPDATED_DATETIME' => [
                'type' => "datetime"
            ],
            'PROGRAM_ID' => [
                'type' => "varchar",
                'constraint' => "100"
            ],
            'EOC_CODE' => [
                'type' => "varchar",
                'constraint' => "100"
            ],
            'EVENT_ID' => [
                'type' => "varchar",
                'constraint' => "100"
            ],
            'HISTORY_DATETIME' => [
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
