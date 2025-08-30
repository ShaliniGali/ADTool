<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class USR_LOOKUP_SAVED_COA_seeder extends Seeder {

	private $table_1 = 'USR_LOOKUP_SAVED_COA';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 3543,
                  'COA_VALUES' => "[]",
                  'OPTIMIZER_INPUT' => "[]",
                  'OVERRIDE_TABLE_SESSION' => "[]",
                  'OVERRIDE_TABLE_METADATA' => "[]",
                  'OVERRIDE_FORM_SESSION' => "[]",
                  'USER_ID' => 8587,
                  'CREATED_DATETIME' => '2028-05-24 06:52:14',
                  'IS_DELETED' => 1,
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 1803,
                  'COA_VALUES' => "[]",
                  'OPTIMIZER_INPUT' => "[]",
                  'OVERRIDE_TABLE_SESSION' => "[]",
                  'OVERRIDE_TABLE_METADATA' => "[]",
                  'OVERRIDE_FORM_SESSION' => "[]",
                  'USER_ID' => 2225,
                  'CREATED_DATETIME' => '2028-01-15 02:37:01',
                  'IS_DELETED' => 0,
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'ID' => 5181,
                  'COA_VALUES' => "[]",
                  'OPTIMIZER_INPUT' => "[]",
                  'OVERRIDE_TABLE_SESSION' => "[]",
                  'OVERRIDE_TABLE_METADATA' => "[]",
                  'OVERRIDE_FORM_SESSION' => "[]",
                  'USER_ID' => 2225,
                  'CREATED_DATETIME' => '2052-10-06 04:54:24',
                  'IS_DELETED' => 0,
		];
		$this->db->insert($this->table_1, $data);

            $data = [
                  'ID' => 5458,
                  'COA_VALUES' => "[]",
                  'OPTIMIZER_INPUT' => "[]",
                  'OVERRIDE_TABLE_SESSION' => "[]",
                  'OVERRIDE_TABLE_METADATA' => "[]",
                  'OVERRIDE_FORM_SESSION' => "[]",
                  'USER_ID' => 4488,
                  'CREATED_DATETIME' => '2052-10-06 04:54:24',
                  'IS_DELETED' => 0,
		];
		$this->db->insert($this->table_1, $data);
	}
}
