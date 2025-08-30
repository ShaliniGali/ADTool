<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class SOCOM_AOAD_model_seeder extends Seeder {

	private $table_1 = 'USR_ZBT_AO_SAVES';
	private $table_2 = 'USR_ZBT_AO_SAVES_HISTORY';
	private $table_3 = 'USR_ZBT_AD_SAVES';
	private $table_4 = 'USR_ZBT_AD_SAVES_HISTORY';
	private $table_5 = 'USR_AO_AD_USERS';
	private $table_6 = 'USR_AO_AD_USERS_HISTORY';
	private $table_7 = 'LOOKUP_PROGRAM';

	public function run()
	{
		$this->run_table_1();
		$this->run_table_2();
		$this->run_table_3();
		$this->run_table_4();
		$this->run_table_5();
		$this->run_table_6();
		$this->run_table_7();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ID' => 3609,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'UEFCYVWBLGOQXDPMISJKRTAHNZ',
			'AO_USER_ID' => 2225,
			'CREATED_DATETIME' => '2042-12-08 11:10:08',
			'UPDATED_DATETIME' => '2057-06-29 07:19:03',
			'PROGRAM_ID' => 'RFKSBTACZONXPVWHJEDUIMLGYQ',
			'EOC_CODE' => 'AXMEKDCFSHGPNRJTVIUQWLBZYO',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 646,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'IKERBJSOVGQXTHCLWNDPUZAYFM',
			'AO_USER_ID' => 4275,
			'CREATED_DATETIME' => '2051-11-05 07:51:15',
			'UPDATED_DATETIME' => '2028-10-02 10:05:27',
			'PROGRAM_ID' => 'GSYMVXWTIBHDKACPFJLOEQURNZ',
			'EOC_CODE' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'EVENT_ID' => 'AXMEKDCFSHGPNRJTVIUQWLBZYO',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'ID' => 5747,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'KATGVLYSPDXCFEWQJUZBHRMNIO',
			'AO_USER_ID' => 1178,
			'CREATED_DATETIME' => '2030-12-03 03:33:41',
			'UPDATED_DATETIME' => '2039-05-09 09:15:56',
			'PROGRAM_ID' => 'DGSMLRNBYITCOWXUZHVAQFKPEJ',
			'EOC_CODE' => 'SWLPJDEMTAZKRNGQUCYOBIHVXF',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
		];
		$this->db->insert($this->table_1, $data);
	}

	private function run_table_2() {
		$this->db->truncate($this->table_2);

		$data = [
			'ID' => 4602,
			'ZBT_AO_ID' => 466,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'MVZQHESIWBULORNPAKDJCYGXTF',
			'AO_USER_ID' => 285,
			'CREATED_DATETIME' => '2057-01-18 02:33:06',
			'UPDATED_DATETIME' => '2023-08-06 06:19:17',
			'PROGRAM_ID' => 'ADXQWBLZJCKNMGRYIEVSUPTOFH',
			'EOC_CODE' => 'OSMTFPLGIHYEBJCDKUVXAZWQNR',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2047-07-18 11:37:23',
		];

		$this->db->insert($this->table_2, $data);

		$data = [
			'ID' => 5988,
			'ZBT_AO_ID' => 9564,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'LPFKQXEMYGTRDNSUHWICJVZBAO',
			'AO_USER_ID' => 9201,
			'CREATED_DATETIME' => '2045-02-28 11:21:36',
			'UPDATED_DATETIME' => '2059-10-01 01:44:33',
			'PROGRAM_ID' => 'HZUNGIAOVQEBYFTDRLPKXJCSWM',
			'EOC_CODE' => 'NKSPGIBXLOYMEVUFTRQCZAWJDH',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2026-11-20 01:28:10',
		];

		$this->db->insert($this->table_2, $data);

		$data = [
			'ID' => 5363,
			'ZBT_AO_ID' => 7540,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'IDMPQOLXTBEVZFYCKJANWGUHSR',
			'AO_USER_ID' => 997,
			'CREATED_DATETIME' => '2059-03-25 05:16:06',
			'UPDATED_DATETIME' => '2023-06-25 09:36:03',
			'PROGRAM_ID' => 'YJXTZSDOBMEHFCGUPNWIVRQKAL',
			'EOC_CODE' => 'DFZWGNUKQYOAJSVLPXIMTCREHB',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2028-01-05 03:58:11',
		];

		$this->db->insert($this->table_2, $data);

		$data = [
			'ID' => 6370,
			'ZBT_AO_ID' => 9910,
			'AO_RECOMENDATION' => 1,
			'AO_COMMENT' => 'JCPEIQKNDVSLATHBWGZFUMOYXR',
			'AO_USER_ID' => 2359,
			'CREATED_DATETIME' => '2058-06-02 01:24:17',
			'UPDATED_DATETIME' => '2031-11-26 06:51:06',
			'PROGRAM_ID' => 'BZTHRUJFOSPQLGINMVAWDYKEXC',
			'EOC_CODE' => 'LVFUYSXCOGAHPZEJTRNIDBWQKM',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2041-10-13 02:54:25',
		];

		$this->db->insert($this->table_2, $data);
	}

	private function run_table_3() {
		$this->db->truncate($this->table_3);

		$data = [
			'ID' => 6339,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'WGAIPJNLZBOUSQRDVKMYTCXHFE',
			'AD_USER_ID' => 1805,
			'CREATED_DATETIME' => '2054-01-14 09:56:58',
			'UPDATED_DATETIME' => '2044-04-01 02:19:11',
			'PROGRAM_ID' => 'XUQCORYVTWFIGBKHMJLZPAENSD',
			'EOC_CODE' => 'BSYOPAVITZNWMKJGHLRDQCEXFU',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
		];

		$this->db->insert($this->table_3, $data);

		$data = [
			'ID' => 4313,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'TVWCBOLDXSRKUYNFIGMQJEHZAP',
			'AD_USER_ID' => 8188,
			'CREATED_DATETIME' => '2056-03-15 02:46:25',
			'UPDATED_DATETIME' => '2050-08-18 09:10:54',
			'PROGRAM_ID' => 'ABPGOVQKLESYHTCZDXUWJRIFNM',
			'EOC_CODE' => 'FXJDZMLYGNSKIAPCQOHVEWBUTR',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
		];

		$this->db->insert($this->table_3, $data);

		$data = [
			'ID' => 8627,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'ZHPYLNBXQEKOFWRSGIDAVMUCJT',
			'AD_USER_ID' => 9620,
			'CREATED_DATETIME' => '2025-10-24 09:06:45',
			'UPDATED_DATETIME' => '2059-01-29 03:04:18',
			'PROGRAM_ID' => 'VGQCNRZDBHUPTLIXWASFEJMOKY',
			'EOC_CODE' => 'XRBTNYUWVGKALQDZMSJOCHPEFI',	
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
		];

		$this->db->insert($this->table_3, $data);

		$data = [
			'ID' => 3687,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'ATXILCSOKYNEFWDJGUMPHRVZBQ',
			'AD_USER_ID' => 3227,
			'CREATED_DATETIME' => '2031-12-29 08:56:38',
			'UPDATED_DATETIME' => '2044-11-10 03:06:39',
			'PROGRAM_ID' => 'XLOZIGMCTQJRPWHDABEYKSFVUN',
			'EOC_CODE' => 'WSZXKJLNCFQTDGUORIPBMHAVEY',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
		];

		$this->db->insert($this->table_3, $data);
	}

	private function run_table_4() {
		$this->db->truncate($this->table_4);

		$data = [
			'ID' => 3398,
			'ZBT_AD_ID' => 9107,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'YPOBWANDEQMZSLHXIKFJTURVCG',
			'AD_USER_ID' => 5803,
			'CREATED_DATETIME' => '2030-06-29 01:48:30',
			'UPDATED_DATETIME' => '2050-01-27 04:36:50',
			'PROGRAM_ID' => 'YFTHQBXCOUDWVRIAZKEGNMSLPJ',
			'EOC_CODE' => 'VSIQPHCYWMOELBZGFUKXTNRADJ',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2057-07-09 12:18:20',
		];

		$this->db->insert($this->table_4, $data);

		$data = [
			'ID' => 7659,
			'ZBT_AD_ID' => 1930,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'MDCSELOTNIZQYWVBFKJPRXHAGU',
			'AD_USER_ID' => 1165,
			'CREATED_DATETIME' => '2050-08-15 06:55:46',
			'UPDATED_DATETIME' => '2056-03-07 09:55:13',
			'PROGRAM_ID' => 'GXRZMYCSHVDEIPWKFTOUBNQALJ',
			'EOC_CODE' => 'RSTFYICAMUHXEDZPNLOBQVJKGW',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2048-11-19 10:26:53',
		];

		$this->db->insert($this->table_4, $data);

		$data = [
			'ID' => 1864,
			'ZBT_AD_ID' => 854,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'KBPFZDWTOGHASCQRIMYUNJXELV',
			'AD_USER_ID' => 1607,
			'CREATED_DATETIME' => '2047-01-14 07:36:06',
			'UPDATED_DATETIME' => '2049-05-08 12:23:47',
			'PROGRAM_ID' => 'ZPIOFVJSKNQHEULTXCGBDWRMYA',
			'EOC_CODE' => 'MUJDNLOZHFEXKRQBGTWCIAYSPV',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2025-06-28 12:50:44',
		];

		$this->db->insert($this->table_4, $data);

		$data = [
			'ID' => 7586,
			'ZBT_AD_ID' => 8589,
			'AD_RECOMENDATION' => 1,
			'AD_COMMENT' => 'WRHAKPBGDCNUOYQTLFVXSJIMEZ',
			'AD_USER_ID' => 7377,
			'CREATED_DATETIME' => '2057-01-12 01:39:38',
			'UPDATED_DATETIME' => '2042-04-19 10:21:56',
			'PROGRAM_ID' => 'BSMWQIVTOYZEPJHLDXGNRCAUFK',
			'EOC_CODE' => 'KVODIUPWAZJRNGBTMEYQSXCFLH',
			'EVENT_ID' => 'RUBHZDOTYSKEWMACGLNJQIPVXF',
			'HISTORY_DATETIME' => '2040-11-12 03:32:32',
		];

		$this->db->insert($this->table_4, $data);
	}

	public function run_table_5() {
		$data = [
			'ID' => 59,
			'GROUP' => 'AO',
			'USER_ID' => 2225,
			'CREATED_DATETIME' => '2050-08-04 07:53:51',
			'UPDATED_DATETIME' => '2052-09-22 02:38:16',
			'IS_DELETED' => 0,
			'UPDATE_USER' => 8630,
		];

		$this->db->insert($this->table_5, $data);

		$data = [
			'ID' => 9249,
			'GROUP' => 'AO and AD',
			'USER_ID' => 3418,
			'CREATED_DATETIME' => '2057-04-12 08:26:06',
			'UPDATED_DATETIME' => '2059-04-02 07:27:23',
			'IS_DELETED' => 1,
			'UPDATE_USER' => 5978,
		];

		$this->db->insert($this->table_5, $data);

		$data = [
			'ID' => 9346,
			'GROUP' => 'AD',
			'USER_ID' => 1805,
			'CREATED_DATETIME' => '2040-05-22 10:14:41',
			'UPDATED_DATETIME' => '2048-09-07 12:55:15',
			'IS_DELETED' => 0,
			'UPDATE_USER' => 1334,
		];

		$this->db->insert($this->table_5, $data);

		$data = [
			'ID' => 7368,
			'GROUP' => 'AO and AD',
			'USER_ID' => 582,
			'CREATED_DATETIME' => '2032-06-03 06:15:38',
			'UPDATED_DATETIME' => '2043-12-21 03:36:53',
			'IS_DELETED' => 1,
			'UPDATE_USER' => 6893,
		];

		$this->db->insert($this->table_5, $data);
	}

	public function run_table_6() {
		$data = [
			'ID' => 5372,
			'AO_AD_ID' => 4160,
			'GROUP' => 1,
			'USER_ID' => 3377,
			'CREATED_DATETIME' => '2058-04-01 05:38:27',
			'UPDATED_DATETIME' => '2050-01-02 08:51:19',
			'IS_DELETED' => 1,
			'UPDATE_USER' => 1407,
			'HISTORY_DATETIME' => '2046-06-04 01:14:34',
		];

		$this->db->insert($this->table_6, $data);

		$data = [
			'ID' => 5457,
			'AO_AD_ID' => 36,
			'GROUP' => 1,
			'USER_ID' => 1877,
			'CREATED_DATETIME' => '2034-11-26 11:52:38',
			'UPDATED_DATETIME' => '2033-02-11 04:58:18',
			'IS_DELETED' => 1,
			'UPDATE_USER' => 3289,
			'HISTORY_DATETIME' => '2030-02-14 07:01:31',
		];

		$this->db->insert($this->table_6, $data);

		$data = [
			'ID' => 1907,
			'AO_AD_ID' => 3887,
			'GROUP' => 1,
			'USER_ID' => 8382,
			'CREATED_DATETIME' => '2031-06-04 06:34:30',
			'UPDATED_DATETIME' => '2048-10-18 09:53:50',
			'IS_DELETED' => 1,
			'UPDATE_USER' => 8679,
			'HISTORY_DATETIME' => '2050-04-11 08:45:41',
		];

		$this->db->insert($this->table_6, $data);

		$data = [
			'ID' => 7703,
			'AO_AD_ID' => 4683,
			'GROUP' => 1,
			'USER_ID' => 8000,
			'CREATED_DATETIME' => '2045-10-14 08:25:49',
			'UPDATED_DATETIME' => '2046-12-26 03:11:28',
			'IS_DELETED' => 1,
			'UPDATE_USER' => 8501,
			'HISTORY_DATETIME' => '2035-08-18 04:56:12',
		];

		$this->db->insert($this->table_6, $data);
	}

	public function run_table_7() {
		$this->db->truncate($this->table_7);

		$data = [
			'ID' => 'AHOSVZIPQURWTECBJMKYDLGXNF',
			'PROGRAM_GROUP' => 'MWINBUCQJHOGX',
			'PROGRAM_CODE' => 'NICTORBPGUX',
			'PROGRAM_NAME' => 'EOYTGIAZWDKFJNSCRVLUBQXHPM',
			'PROGRAM_TYPE_CODE' => 'Q',
			'PROGRAM_SUB_TYPE_CODE' => 'EXJCK',
			'PROGRAM_DESCRIPTION' => 'RKIHOLNEFYVWAGMCBUZPQSXJDT',
			'CAPABILITY_SPONSOR_CODE' => 'JRYCESMPBKNWU',
			'ASSESSMENT_AREA_CODE' => 'Z',
			'POM_SPONSOR_CODE' => 'QWBJLUTVZXYFI',
			'JCA_LV1_ID' => 'EUVW',
			'JCA_LV2_ID' => 'XGLOK',
			'JCA_LV3_ID' => 'XNIFZH',
			'STORM_ID' => 'JUZGVPEMAXLSWHTQONKFRCYIB',
		];

		$this->db->insert($this->table_7, $data);

		$data = [
			'ID' => 'NMLYJXCKTAPDHOWIVZGFQBSURE',
			'PROGRAM_GROUP' => 'GJDZIPYCWOMHQ',
			'PROGRAM_CODE' => 'ESNFUQGIODT',
			'PROGRAM_NAME' => 'KPOBFZJIXWMCDYSNEULQGAVTRH',
			'PROGRAM_TYPE_CODE' => 'S',
			'PROGRAM_SUB_TYPE_CODE' => 'CESWT',
			'PROGRAM_DESCRIPTION' => 'RGUEXTBSFAJCYMQKZPNILHOWVD',
			'CAPABILITY_SPONSOR_CODE' => 'LWITUDHXPGZOF',
			'ASSESSMENT_AREA_CODE' => 'N',
			'POM_SPONSOR_CODE' => 'ICBWXVJHZADUN',
			'JCA_LV1_ID' => 'DRHZ',
			'JCA_LV2_ID' => 'JPVNT',
			'JCA_LV3_ID' => 'IHFXKQ',
			'STORM_ID' => 'UKTSYXJDGAHMQROWPLEFBIZNC',
		];

		$this->db->insert($this->table_7, $data);

		$data = [
			'ID' => 'KVMLIXHZTDAEUQFCJRBOWSPYNG',
			'PROGRAM_GROUP' => 'EMAFLITVUBXPZ',
			'PROGRAM_CODE' => 'CLGVKWNUHBE',
			'PROGRAM_NAME' => 'LUNBCVHSMJQTWDOYKPREXZAFGI',
			'PROGRAM_TYPE_CODE' => 'M',
			'PROGRAM_SUB_TYPE_CODE' => 'OBZNV',
			'PROGRAM_DESCRIPTION' => 'PNEGKQTDBYFZSMAJVCRIHOUWXL',
			'CAPABILITY_SPONSOR_CODE' => 'MPAWYDSVZHITU',
			'ASSESSMENT_AREA_CODE' => 'W',
			'POM_SPONSOR_CODE' => 'YVTRNICZBDJEA',
			'JCA_LV1_ID' => 'UCVN',
			'JCA_LV2_ID' => 'MDIGV',
			'JCA_LV3_ID' => 'PWGNTK',
			'STORM_ID' => 'ZHMLQIXTWPONASGEVKJUDBFCY',
		];

		$this->db->insert($this->table_7, $data);

		$data = [
			'ID' => 'WMZFRQHAVSDTGCBYPEJUNLKIXO',
			'PROGRAM_GROUP' => 'EMAFLITVUBXPZ',
			'PROGRAM_CODE' => 'CLGVKWNUHBE',
			'PROGRAM_NAME' => 'HMFYDVOWLXICREJUNQZPBSKATG',
			'PROGRAM_TYPE_CODE' => 'J',
			'PROGRAM_SUB_TYPE_CODE' => 'TMEIB',
			'PROGRAM_DESCRIPTION' => 'HBQAMDENRCUPYKTOWSLFGXIJZV',
			'CAPABILITY_SPONSOR_CODE' => 'ONTLGQZWBFHAS',
			'ASSESSMENT_AREA_CODE' => 'U',
			'POM_SPONSOR_CODE' => 'ODUPXTBHLQVAM',
			'JCA_LV1_ID' => 'VPKX',
			'JCA_LV2_ID' => 'TWGZN',
			'JCA_LV3_ID' => 'EBWZFK',
			'STORM_ID' => 'MFHUWVCDZBAJNPGOLIRESTYXQ',
		];

		$this->db->insert($this->table_7, $data);
	}
}
