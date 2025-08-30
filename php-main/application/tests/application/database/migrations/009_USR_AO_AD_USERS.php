<?php

class Migration_Usr_Ao_Ad_Users extends CI_Migration
{
    private $table = 'USR_AO_AD_USERS';

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
            'GROUP' => [
                'type' => "enum('None','AO','AD','AO and AD')"
            ],
            'USER_ID' => [
                'type' => "int"
            ],
            'CREATED_DATETIME' => [
                'type' => "datetime"
            ],
            'UPDATED_DATETIME' => [
                'type' => "datetime"
            ],
            'IS_DELETED' => [
                'type' => "tinyint",
            'constraint' => "1"
            ],
            'UPDATE_USER' => [
                'type' => "int"
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
