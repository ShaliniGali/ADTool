<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class USR_ADMIN_USERS_seeder extends Seeder {

	private $table_1 = 'USR_ADMIN_USERS';
      private $table_2 = 'USR_ADMIN_USERS_HISTORY';

	public function run()
	{
		$this->run_table_1();
            $this->run_table_2();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 416,
                  'GROUP' => 1,
                  'USER_ID' => 7321,
                  'CREATED_DATETIME' => '2034-06-12 06:21:01',
                  'UPDATED_DATETIME' => '2052-09-30 10:24:02',
                  'IS_DELETED' => 1,
                  'UPDATE_USER' => 2088,
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 416,
                  'GROUP' => 1,
                  'USER_ID' => 7321,
                  'CREATED_DATETIME' => '2034-06-12 06:21:01',
                  'UPDATED_DATETIME' => '2052-09-30 10:24:02',
                  'IS_DELETED' => 1,
                  'UPDATE_USER' => 2088,
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'ID' => 5385,
                  'GROUP' => 1,
                  'USER_ID' => 6419,
                  'CREATED_DATETIME' => '2054-08-03 11:39:55',
                  'UPDATED_DATETIME' => '2057-12-21 02:31:41',
                  'IS_DELETED' => 1,
                  'UPDATE_USER' => 685,
		];
		$this->db->insert($this->table_1, $data);
	}

      private function run_table_2() {
		$this->db->truncate($this->table_2);

		$data = [
			'ID' => 9723,
                  'ADMIN_ID' => 6261,
                  'GROUP' => 1,
                  'USER_ID' => 9299,
                  'CREATED_DATETIME' => '2046-05-28 08:21:23',
                  'UPDATED_DATETIME' => '2050-11-05 09:41:10',
                  'IS_DELETED' => 1,
                  'UPDATE_USER' => 4414,
                  'HISTORY_DATETIME' => '2026-04-04 07:58:51',
		];
		$this->db->insert($this->table_2, $data);
		
		$data = [
                  'ID' => 6971,
                  'ADMIN_ID' => 6556,
                  'GROUP' => 1,
                  'USER_ID' => 2267,
                  'CREATED_DATETIME' => '2035-10-30 07:17:06',
                  'UPDATED_DATETIME' => '2050-11-23 07:32:42',
                  'IS_DELETED' => 1,
                  'UPDATE_USER' => 9776,
                  'HISTORY_DATETIME' => '2046-06-02 07:55:36',
		];
		$this->db->insert($this->table_2, $data);
		
		$data = [
                  'ID' => 1420,
                  'ADMIN_ID' => 6998,
                  'GROUP' => 1,
                  'USER_ID' => 4288,
                  'CREATED_DATETIME' => '2031-09-25 03:11:09',
                  'UPDATED_DATETIME' => '2043-07-15 10:59:10',
                  'IS_DELETED' => 1,
                  'UPDATE_USER' => 8808,
                  'HISTORY_DATETIME' => '2056-10-02 08:59:05',
		];
		$this->db->insert($this->table_2, $data);
	}
}
