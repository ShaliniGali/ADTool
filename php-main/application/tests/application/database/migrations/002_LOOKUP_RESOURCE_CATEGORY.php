<?php

class Migration_Lookup_Resource_Category extends CI_Migration
{
    private $table = 'LOOKUP_RESOURCE_CATEGORY';

    public function __construct() {
        // Only accessible via CLI
        if (ENVIRONMENT !== 'testing' || is_cli() === false) {
            exit();
        }
        
        parent::__construct();
    }

    public function up()
    {
        $this->dbforge->add_field(['RESOURCE_CATEGORY_CODE' => [
            'type' => "varchar",
        'constraint' => "8"
        ],
        'RESOURCE_CATEGORY' => [
            'type' => "varchar",
        'constraint' => "100"
        ],
        'UNIT' => [
            'type' => "varchar",
        'constraint' => "20"
        ],]);

        if(!$this->db->table_exists($this->table)){
            $this->dbforge->create_table($this->table);
        }
        
    }

    public function down()
    {
    }
}