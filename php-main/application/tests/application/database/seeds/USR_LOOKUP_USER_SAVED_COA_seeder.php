<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class USR_LOOKUP_USER_SAVED_COA_seeder extends Seeder {

	private $table_1 = 'USR_LOOKUP_USER_SAVED_COA';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 5458,
                  'COA_TITLE' => 'UVBXCJKFRHOQSGTPEZILWYANMD',
                  'COA_DESCRIPTION' => 'SFQLJUHRNAEPBXCGIWDZVTYMOK',
                  'USER_ID' => 4488,
                  'SAVED_COA_ID' => 786,
                  'STATE' => 'ZFYHWMEUISVLOABJXGNRKTCDPQ',
                  'CREATED_DATETIME' => '2034-07-20 06:35:19',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 8813,
                  'COA_TITLE' => 'PHXJLYRWMGBUQTNZCEDKOSIFVA',
                  'COA_DESCRIPTION' => 'FRCMNSQHETJALZWUOYKVPBXIGD',
                  'USER_ID' => 2225,
                  'SAVED_COA_ID' => 9703,
                  'STATE' => 'MJATRSBCEOWUZFXVDYNGQKIHLP',
                  'CREATED_DATETIME' => '2050-10-30 09:20:48',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'ID' => 9649,
                  'COA_TITLE' => 'JNHLCKFWRSOAUMBQYPGVZEDTIX',
                  'COA_DESCRIPTION' => 'QUXMRCGHJLFYWNPTSKIZEOBAVD',
                  'USER_ID' => 2225,
                  'SAVED_COA_ID' => 2867,
                  'STATE' => 'YIXQAGTDSZOKBREJFPCMWNHULV',
                  'CREATED_DATETIME' => '2033-12-28 04:27:57',
		];
		$this->db->insert($this->table_1, $data);
	}
}
