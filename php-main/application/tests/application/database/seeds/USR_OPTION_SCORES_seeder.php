<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class USR_OPTION_SCORES_seeder extends Seeder {

	private $table_1 = 'USR_OPTION_SCORES';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 2945,
                  'NAME' => 'LGYXSCMJUZTIOFHENRPBWQAKDV',
                  'DESCRIPTION' => 'XSFARHMQOTZJGNPICYUDVBEWKL',
                  'SESSION' => "[]",
                  'PROGRAM_ID' => 'SZWBAYXELIVQONMDRGHUTJKPFC',
                  'USER_ID' => 488,
                  'DELETED' => 1,
                  'CREATED_TIMESTAMP' => '2024-02-15 03:55:45',
                  'UPDATED_TIMESTAMP' => '2035-02-02 09:22:30',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 2167,
                  'NAME' => 'IDSFRUPTBXJQZKCWOELVMAYGNH',
                  'DESCRIPTION' => 'HSCNTUFGAZKLIJDPERBOXVWYMQ',
                  'SESSION' => "[]",
                  'PROGRAM_ID' => 'PNMHOALFURGWCETXSKJQYZVDIB',
                  'USER_ID' => 6714,
                  'DELETED' => 1,
                  'CREATED_TIMESTAMP' => '2059-11-17 07:21:35',
                  'UPDATED_TIMESTAMP' => '2037-09-20 08:53:05',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'ID' => 1988,
                  'NAME' => 'MYPHEXTVIBZNFAJOQGRUWDCSLK',
                  'DESCRIPTION' => 'MGFTSKREZBLJYHVNCIXQPDWUOA',
                  'SESSION' => "[]",
                  'PROGRAM_ID' => 'INTKADOUSGXZFJWMELYRHCPVBQ',
                  'USER_ID' => 4527,
                  'DELETED' => 0,
                  'CREATED_TIMESTAMP' => '2056-01-27 07:48:54',
                  'UPDATED_TIMESTAMP' => '2036-01-17 02:44:46',
		];
		$this->db->insert($this->table_1, $data);
	}
}
