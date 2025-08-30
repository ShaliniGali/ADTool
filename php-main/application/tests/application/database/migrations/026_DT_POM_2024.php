<?php

class Migration_Dt_pom_2024 extends CI_Migration
{
    private $table = 'DT_POM_2024';

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
            'BASE_K' => [
                'type' => "int"
            ],
            'BUDGET_ACTIVITY_NAME' => [
                'type' => "varchar",
                'constraint' => "30"
            ],
            'BUDGET_SUB_ACTIVITY_NAME' => [
                'type' => "varchar",
                'constraint' => "60"
            ],
            'CAPABILITY_SPONSOR_CODE' => [
                'type' => "varchar",
                'constraint' => "13"
            ],
            'EOC_CODE' => [
                'type' => "varchar",
                'constraint' => "15"
            ],
            'EVENT_JUSTIFICATION' => [
                'type' => "varchar",
                'constraint' => "500"
            ],
            'EVENT_NAME' => [
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
            'OCO_OTHD_K' => [
                'type' => "int"
            ],
            'OCO_TO_BASE_K' => [
                'type' => "int"
            ],
            'OSD_PROGRAM_ELEMENT_CODE' => [
                'type' => "varchar",
                'constraint' => "10"
            ],
            'POM_SPONSOR_CODE' => [
                'type' => "varchar",
                'constraint' => "13"
            ],
            'PROGRAM_CODE' => [
                'type' => "varchar",
                'constraint' => "11"
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

    public function down() {}
}
