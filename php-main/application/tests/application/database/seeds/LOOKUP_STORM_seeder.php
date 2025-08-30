<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class LOOKUP_STORM_seeder extends Seeder {

	private $table_1 = 'LOOKUP_STORM';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 'OXIFBLPREVQZHTCJWUKMDSGNY',
            'PROGRAM_GROUP' => 'YXPVEBZSWULHR',
            'CAPABILITY_SPONSOR_CODE' => 'KVJQHSIXYFLWR',
            'ACCESS_TYPE' => 'QWEPCRFMHUVOZLJSDABGITXKY',
            'SA_SCORE' => 1,
            'ID_SC_SCORE' => 1,
            'M_SCORE' => 1,
            'TOTAL_SCORE' => 1,
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 'FRIQYHSMZUDVACWOKPXNBTEGJ',
            'PROGRAM_GROUP' => 'AGEVUBNLPMZFS',
            'CAPABILITY_SPONSOR_CODE' => 'UIBTNXHSKCYAQ',
            'ACCESS_TYPE' => 'KQPCROEJSGHMIATVXWBFDYLZN',
            'SA_SCORE' => 1,
            'ID_SC_SCORE' => 1,
            'M_SCORE' => 1,
            'TOTAL_SCORE' => 1,
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
            'ID' => 'WQVTUSYAKLJODECFRIZBPXNHG',
            'PROGRAM_GROUP' => 'XADZEYVPFBNJO',
            'CAPABILITY_SPONSOR_CODE' => 'WEOXJCTNVRPZG',
            'ACCESS_TYPE' => 'ATSHRLPFMZKJOVNUDBGCIEYXW',
            'SA_SCORE' => 1,
            'ID_SC_SCORE' => 1,
            'M_SCORE' => 1,
            'TOTAL_SCORE' => 1,
		];
		$this->db->insert($this->table_1, $data);
	}
}
