<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class USR_ISSUE_AO_SAVES_seeder extends Seeder {

	private $table_1 = 'USR_ISSUE_AO_SAVES';
      private $table_2 = 'USR_ISSUE_AO_SAVES_HISTORY';
      private $table_3 = 'USR_ISSUE_AD_SAVES';
      private $table_4 = 'USR_ISSUE_AD_SAVES_HISTORY';

	public function run()
	{
		$this->run_table_1();
            $this->run_table_2();
            $this->run_table_3();
            $this->run_table_4();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
                  'ID' => 2542,
                  'AO_RECOMENDATION' => 1,
                  'AO_COMMENT' => 'UHQIOBPNGASXRJYMVWKEFDZCLT',
                  'AO_USER_ID' => 219,
                  'CREATED_DATETIME' => '2040-06-14 03:11:34',
                  'UPDATED_DATETIME' => '2047-06-07 06:05:21',
                  'PROGRAM_ID' => 'GUBEASZWXOHYJRLNIPDKMCFTVQ',
                  'EOC_CODE' => 'AGKCLMQIZSYHXRFOTUPDWEVJBN',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'ID' => 6024,
                  'AO_RECOMENDATION' => 1,
                  'AO_COMMENT' => 'QBFOSGJMAKNZYTVHWXLCUDEPRI',
                  'AO_USER_ID' => 9434,
                  'CREATED_DATETIME' => '2049-02-01 11:25:12',
                  'UPDATED_DATETIME' => '2042-06-16 04:33:02',
                  'PROGRAM_ID' => 'UEDJHWOKGFLTNYZCQAVBIRSMPX',
                  'EOC_CODE' => 'JQKMZCSTEGIWPYAFDXVNOHRBUL',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
                  'ID' => 3873,
                  'AO_RECOMENDATION' => 1,
                  'AO_COMMENT' => 'FSJEQLPKYRTUBZCDGNOIWXVHMA',
                  'AO_USER_ID' => 5546,
                  'CREATED_DATETIME' => '2054-02-03 01:32:50',
                  'UPDATED_DATETIME' => '2034-04-03 08:29:44',
                  'PROGRAM_ID' => 'FPJSVORTKDAMLXNYWQGBEHZUCI',
                  'EOC_CODE' => 'NCPXAJLOYVSBQTEFZGKRIHMUDW',
		];
		$this->db->insert($this->table_1, $data);
	}

      private function run_table_2() {
		$this->db->truncate($this->table_2);

		$data = [
			'ID' => 8347,
                  'ISSUE_AO_ID' => 5368,
                  'AO_RECOMENDATION' => 1,
                  'AO_COMMENT' => 'VJEUKZHOQDYAILWRSTXBNFGCPM',
                  'AO_USER_ID' => 8786,
                  'CREATED_DATETIME' => '2044-09-09 02:15:51',
                  'UPDATED_DATETIME' => '2052-04-03 02:18:11',
                  'PROGRAM_ID' => 'PDKQXJEHMZYSUNCGVOLFTRWIAB',
                  'EOC_CODE' => 'WHUJKGNIRLPZCYASDQMXVEFBOT',
                  'HISTORY_DATETIME' => '2050-03-11 09:21:26',
		];
		$this->db->insert($this->table_2, $data);
		
		$data = [
                  'ID' => 5693,
                  'ISSUE_AO_ID' => 2360,
                  'AO_RECOMENDATION' => 1,
                  'AO_COMMENT' => 'HPWJUEDVYLMQIBTFOGSAZXRNCK',
                  'AO_USER_ID' => 8765,
                  'CREATED_DATETIME' => '2031-06-19 11:24:07',
                  'UPDATED_DATETIME' => '2037-09-25 11:04:50',
                  'PROGRAM_ID' => 'GYLVIKXSMOBTWFACQUEPJNDHRZ',
                  'EOC_CODE' => 'OKDMVGFPUIYWNZACXLBSJHETQR',
                  'HISTORY_DATETIME' => '2059-08-04 03:59:46',
		];
		$this->db->insert($this->table_2, $data);
		
		$data = [
                  'ID' => 8288,
                  'ISSUE_AO_ID' => 8115,
                  'AO_RECOMENDATION' => 1,
                  'AO_COMMENT' => 'XPYGRHEUNMIDFATCBWSJKVLZOQ',
                  'AO_USER_ID' => 8893,
                  'CREATED_DATETIME' => '2025-11-15 11:08:53',
                  'UPDATED_DATETIME' => '2027-02-22 12:48:38',
                  'PROGRAM_ID' => 'IYKHOUZFQTRSWLPNCEBVGADMXJ',
                  'EOC_CODE' => 'SQDPWIUCBFGJHXZKTMYLEVORAN',
                  'HISTORY_DATETIME' => '2054-03-09 08:34:18',
		];
		$this->db->insert($this->table_2, $data);
	}

      private function run_table_3() {
		$this->db->truncate($this->table_3);

		$data = [
                  'ID' => 1338,
                  'AD_RECOMENDATION' => 1,
                  'AD_COMMENT' => 'VOXIPYFZQLMUCKJGBEDNHATWRS',
                  'AD_USER_ID' => 3939,
                  'CREATED_DATETIME' => '2056-12-13 01:41:44',
                  'UPDATED_DATETIME' => '2045-04-01 10:04:17',
                  'PROGRAM_ID' => 'THEJPDXLGMVZBRQWNAFOKSYUIC',
                  'EOC_CODE' => 'NLBOUWPKCRMTZIVFDGSYAJQEXH',
		];
		$this->db->insert($this->table_3, $data);
		
		$data = [
                  'ID' => 1925,
                  'AD_RECOMENDATION' => 1,
                  'AD_COMMENT' => 'DJTWVCMARQZBHKFPXEYLSNUIOG',
                  'AD_USER_ID' => 134,
                  'CREATED_DATETIME' => '2060-10-05 06:41:36',
                  'UPDATED_DATETIME' => '2023-01-12 03:48:28',
                  'PROGRAM_ID' => 'XSUDGWVNYEQTCBZOLAFKIHMPRJ',
                  'EOC_CODE' => 'ZIRSWNMUBCOKDFTVAEYXLHGPJQ',
		];
		$this->db->insert($this->table_3, $data);
		
		$data = [
                  'ID' => 4437,
                  'AD_RECOMENDATION' => 1,
                  'AD_COMMENT' => 'KUJXCFQBMRZAGVYWENDLOTPHIS',
                  'AD_USER_ID' => 1918,
                  'CREATED_DATETIME' => '2035-01-13 04:48:54',
                  'UPDATED_DATETIME' => '2023-08-14 08:55:24',
                  'PROGRAM_ID' => 'OCFNALRHWZIYKPQSVJUEDTBGXM',
                  'EOC_CODE' => 'DRINUAYEXVQBCFJGHPSZWMOKTL',
		];
		$this->db->insert($this->table_3, $data);
	}

      private function run_table_4() {
		$this->db->truncate($this->table_4);

		$data = [
                  'ID' => 9921,
                  'ISSUE_AD_ID' => 1801,
                  'AD_RECOMENDATION' => 1,
                  'AD_COMMENT' => 'DPFJVNSOLCEIHQXBAUYKTRWMZG',
                  'AD_USER_ID' => 1185,
                  'CREATED_DATETIME' => '2028-06-07 09:22:07',
                  'UPDATED_DATETIME' => '2054-07-23 10:42:05',
                  'PROGRAM_ID' => 'AGONHEBFYISXRCMPWDTLJUQZVK',
                  'EOC_CODE' => 'JYKQTRLHVGOBXFICAWSDEPZUNM',
                  'HISTORY_DATETIME' => '2025-07-07 06:42:05',
		];
		$this->db->insert($this->table_4, $data);
		
		$data = [
                  'ID' => 7745,
                  'ISSUE_AD_ID' => 1259,
                  'AD_RECOMENDATION' => 1,
                  'AD_COMMENT' => 'LPWYGFBEMZAJQIKTRVXNHSDUCO',
                  'AD_USER_ID' => 9925,
                  'CREATED_DATETIME' => '2037-08-21 10:40:34',
                  'UPDATED_DATETIME' => '2027-04-16 04:29:59',
                  'PROGRAM_ID' => 'WZVXILSBFYRAMKTCEQNUHJGDPO',
                  'EOC_CODE' => 'JHLYEMWSQXZNRIAVBTGFOKUPDC',
                  'HISTORY_DATETIME' => '2028-02-09 10:28:43',
		];
		$this->db->insert($this->table_4, $data);
		
		$data = [
                  'ID' => 9101,
                  'ISSUE_AD_ID' => 1841,
                  'AD_RECOMENDATION' => 1,
                  'AD_COMMENT' => 'WXOCEZJNKPYASGHTDMQRLIFVUB',
                  'AD_USER_ID' => 4676,
                  'CREATED_DATETIME' => '2057-10-27 08:44:13',
                  'UPDATED_DATETIME' => '2049-06-08 06:19:45',
                  'PROGRAM_ID' => 'TLRHYBZGCKEXASUDMWNVFQJOIP',
                  'EOC_CODE' => 'ZGJDSXRHLTKWQVCIFMAUBOYPNE',
                  'HISTORY_DATETIME' => '2025-02-08 01:59:45',
		];
		$this->db->insert($this->table_4, $data);
	}
}
