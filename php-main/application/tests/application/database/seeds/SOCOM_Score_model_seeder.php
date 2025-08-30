<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class SOCOM_Score_model_seeder extends Seeder {

	private $table_1 = 'USR_OPTION_SCORES';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 2818,
			'NAME' => 'BCZKDWSEYILMJVQXUHPTFOGRAN',
			'DESCRIPTION' => 'IGRXWETSZODCHBVYNJPLFMQKAU',
			'SESSION' => "[]",
			'PROGRAM_ID' => 'KVYMJWDIFNPTZSURBGCLHAXQOE',
			'USER_ID' => 8096,
			'DELETED' => 1,
			'CREATED_TIMESTAMP' => '2041-04-21 03:17:44',
			'UPDATED_TIMESTAMP' => '2042-02-02 11:21:45',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 5674,
			'NAME' => 'OGACZJYQUFXTDSBWEMHRPVNIKL',
			'DESCRIPTION' => 'TBCSARXQOZJYNFWDPGUMKEHILV',
			'SESSION' => "[]",
			'PROGRAM_ID' => 'GLXFMHWCTZESVUNRODJKPIYBAQ',
			'USER_ID' => 1866,
			'DELETED' => 1,
			'CREATED_TIMESTAMP' => '2036-01-08 10:51:28',
			'UPDATED_TIMESTAMP' => '2046-02-16 07:53:09',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 1773,
			'NAME' => 'UOKLNCZFYWAQVPBHTMDRSGEIXJ',
			'DESCRIPTION' => 'SVBADLUHZIRGOCYTWQNMEKJPXF',
			'SESSION' => "[]",
			'PROGRAM_ID' => 'PMATIJKSHYWXLDCZBUOQFERVNG',
			'USER_ID' => 6346,
			'DELETED' => 1,
			'CREATED_TIMESTAMP' => '2049-06-08 12:03:19',
			'UPDATED_TIMESTAMP' => '2059-03-02 02:10:03',
		];
		$this->db->insert($this->table_1, $data);
	}
}
