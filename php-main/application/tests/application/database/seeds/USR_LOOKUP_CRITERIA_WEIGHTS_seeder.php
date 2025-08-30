<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class USR_LOOKUP_CRITERIA_WEIGHTS_seeder extends Seeder {

	private $table_1 = 'USR_LOOKUP_CRITERIA_WEIGHTS';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'WEIGHT_ID' => 9358,
                  'TITLE' => 'Test Title',
                  'DESCRIPTION' => "[]",
                  'SESSION' => "[]",
                  'USER_ID' => 0,
                  'DELETED' => 0,
                  'TIMESTAMP' => '2042-09-27 03:22:18',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'WEIGHT_ID' => 5269,
                  'TITLE' => 'IALKWJXCZOTYSPRDFGBHVEMUNQ',
                  'DESCRIPTION' => "[]",
                  'SESSION' => "[]",
                  'USER_ID' => 544,
                  'DELETED' => 1,
                  'TIMESTAMP' => '2028-07-17 12:42:24',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'WEIGHT_ID' => 3586,
                  'TITLE' => 'SWTXBZADVNQUMJFKGRCHLOIEPY',
                  'DESCRIPTION' => "[]",
                  'SESSION' => "[]",
                  'USER_ID' => 2693,
                  'DELETED' => 0,
                  'TIMESTAMP' => '2023-03-25 07:35:24',
		];
		$this->db->insert($this->table_1, $data);
	}
}
