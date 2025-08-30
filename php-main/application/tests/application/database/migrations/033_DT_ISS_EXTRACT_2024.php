<?php

class Migration_Dt_iss_extract_2024 extends CI_Migration
{
    private $table = 'DT_ISS_EXTRACT_2024';

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
            'ASSESSMENT_AREA_CODE' => [
                'type' => "varchar",
            'constraint' => "1"
            ],
            'BUDGET_ACTIVITY_CODE' => [
                'type' => "varchar",
            'constraint' => "1"
            ],
            'BUDGET_ACTIVITY_NAME' => [
                'type' => "varchar",
            'constraint' => "30"
            ],
            'BUDGET_SUB_ACTIVITY_CODE' => [
                'type' => "varchar",
            'constraint' => "2"
            ],
            'BUDGET_SUB_ACTIVITY_NAME' => [
                'type' => "varchar",
            'constraint' => "60"
            ],
            'CAPABILITY_SPONSOR_CODE' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'DELTA_AMT' => [
                'type' => "int"
            ],
            'DELTA_O2B_AMT' => [
                'type' => "int"
            ],
            'DELTA_OCO_AMT' => [
                'type' => "int"
            ],
            'EOC_CODE' => [
                'type' => "varchar",
            'constraint' => "15"
            ],
            'EVENT_DATE' => [
                'type' => "datetime"
            ],
            'EVENT_JUSTIFICATION' => [
                'type' => "varchar",
            'constraint' => "500"
            ],
            'EVENT_NAME' => [
                'type' => "varchar",
            'constraint' => "60"
            ],
            'EVENT_STATUS' => [
                'type' => "varchar",
            'constraint' => "21"
            ],
            'EVENT_STATUS_COMMENT' => [
                'type' => "varchar",
            'constraint' => "30"
            ],
            'EVENT_TITLE' => [
                'type' => "varchar",
            'constraint' => "200"
            ],
            'EVENT_TYPE' => [
                'type' => "varchar",
            'constraint' => "3"
            ],
            'EVENT_USER' => [
                'type' => "varchar",
            'constraint' => "60"
            ],
            'EXECUTION_MANAGER_CODE' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'FISCAL_YEAR' => [
                'type' => "smallint"
            ],
            'LINE_ITEM_CODE' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'O2B_AMT' => [
                'type' => "int"
            ],
            'OCO_AMT' => [
                'type' => "int"
            ],
            'OSD_PROGRAM_ELEMENT_CODE' => [
                'type' => "varchar",
            'constraint' => "10"
            ],
            'POM_POSITION_CODE' => [
                'type' => "varchar",
            'constraint' => "9"
            ],
            'POM_SPONSOR_CODE' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'PROGRAM_CODE' => [
                'type' => "varchar",
            'constraint' => "11"
            ],
            'PROGRAM_GROUP' => [
                'type' => "varchar",
            'constraint' => "13"
            ],
            'PROP_AMT' => [
                'type' => "int"
            ],
            'PROP_O2B_AMT' => [
                'type' => "int"
            ],
            'PROP_OCO_AMT' => [
                'type' => "int"
            ],
            'RDTE_PROJECT_CODE' => [
                'type' => "varchar",
            'constraint' => "8"
            ],
            'RESOURCE_CATEGORY_CODE' => [
                'type' => "varchar",
            'constraint' => "8"
            ],
            'RESOURCE_K' => [
                'type' => "int"
            ],
            'SPECIAL_PROJECT_CODE' => [
                'type' => "smallint"
            ],
            'SUB_ACTIVITY_GROUP_CODE' => [
                'type' => "varchar",
            'constraint' => "4"
            ],
            'SUB_ACTIVITY_GROUP_NAME' => [
                'type' => "varchar",
            'constraint' => "60"
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
