<?php

class Migration_Lookup_Program extends CI_Migration
{
    private $table = 'LOOKUP_PROGRAM';

    public function __construct() {
        // Only accessible via CLI
        if (ENVIRONMENT !== 'testing' || is_cli() === false) {
            exit();
        }
        
        parent::__construct();
    }

    public function up()
    {
        $this->dbforge->add_field(['ID' => [
            'type' => "varchar",
        'constraint' => "40"
        ],
        'PROGRAM_GROUP' => [
            'type' => "varchar",
        'constraint' => "13"
        ],
        'PROGRAM_CODE' => [
            'type' => "varchar",
        'constraint' => "11"
        ],
        'PROGRAM_NAME' => [
            'type' => "varchar",
        'constraint' => "60"
        ],
        'PROGRAM_TYPE_CODE' => [
            'type' => "varchar",
        'constraint' => "1"
        ],
        'PROGRAM_SUB_TYPE_CODE' => [
            'type' => "varchar",
        'constraint' => "5"
        ],
        'PROGRAM_DESCRIPTION' => [
            'type' => "varchar",
        'constraint' => "10000"
        ],
        'CAPABILITY_SPONSOR_CODE' => [
            'type' => "varchar",
        'constraint' => "13"
        ],
        'ASSESSMENT_AREA_CODE' => [
            'type' => "varchar",
        'constraint' => "1"
        ],
        'POM_SPONSOR_CODE' => [
            'type' => "varchar",
        'constraint' => "13"
        ],
        'JCA_LV1_ID' => [
            'type' => "varchar",
        'constraint' => "4"
        ],
        'JCA_LV2_ID' => [
            'type' => "varchar",
        'constraint' => "5"
        ],
        'JCA_LV3_ID' => [
            'type' => "varchar",
        'constraint' => "6"
        ],
        'STORM_ID' => [
            'type' => "varchar",
        'constraint' => "25"
        ],]);

        if(!$this->db->table_exists($this->table)){
            $this->dbforge->create_table($this->table);
        }
        
    }

    public function down()
    {
    }
}