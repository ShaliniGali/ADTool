<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class DT_ISS_EXTRACT_2024_seeder extends Seeder {

	private $table_1 = 'DT_ISS_EXTRACT_2024';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ASSESSMENT_AREA_CODE' => 'N',
			'BUDGET_ACTIVITY_CODE' => 'W',
			'BUDGET_ACTIVITY_NAME' => 'LWMXRYJIZEGBTVDAUNOQPHKFSC',
			'BUDGET_SUB_ACTIVITY_CODE' => 'JK',
			'BUDGET_SUB_ACTIVITY_NAME' => 'KHWATXMYDNQFLZUORSGPCJBVEI',
			'CAPABILITY_SPONSOR_CODE' => 'GELQPDYHNIROS',
			'DELTA_AMT' => 1526,
			'DELTA_O2B_AMT' => 9558,
			'DELTA_OCO_AMT' => 2435,
			'EOC_CODE' => 'ITXJQLZCSMBYADH',
			'EVENT_DATE' => '2048-06-28 01:06:38',
			'EVENT_JUSTIFICATION' => 'GHDBTYNLZCIEOWKAMSPUXJQFRV',
			'EVENT_NAME' => 'LBSGMWTRUCIAZYHDFVJPXNOKQE',
			'EVENT_STATUS' => 'UHYDLVWQBPXATNSKRGECO',
			'EVENT_STATUS_COMMENT' => 'XJWAYLNIMFZHBRCDTKPSVQUEGO',
			'EVENT_TITLE' => 'MZNSYIWDXHPRACKOBJUTLQGVFE',
			'EVENT_TYPE' => 'GAE',
			'EVENT_USER' => 'NRWJMVPHQXEUKSFTIYAZDOBCLG',
			'EXECUTION_MANAGER_CODE' => 'OPVEGTUZNAMXW',
			'FISCAL_YEAR' => 1,
			'LINE_ITEM_CODE' => 'XMEVKISQWPOHC',
			'O2B_AMT' => 706,
			'OCO_AMT' => 6801,
			'OSD_PROGRAM_ELEMENT_CODE' => 'WSQHYEDMOX',
			'POM_POSITION_CODE' => 'TNEFCSUQK',
			'POM_SPONSOR_CODE' => 'ZXJDRGTIEBSNW',
			'PROGRAM_CODE' => 'NICTORBPGUX',
			'PROGRAM_GROUP' => 'MWINBUCQJHOGX',
			'PROP_AMT' => 3541,
			'PROP_O2B_AMT' => 4784,
			'PROP_OCO_AMT' => 2953,
			'RDTE_PROJECT_CODE' => 'GZALVTEF',
			'RESOURCE_CATEGORY_CODE' => 'ISBGZWPK',
			'RESOURCE_K' => 3204,
			'SPECIAL_PROJECT_CODE' => 1,
			'SUB_ACTIVITY_GROUP_CODE' => 'ALFR',
			'SUB_ACTIVITY_GROUP_NAME' => 'TBPYDNGKMUFQXLSIEZRACWOHJV',
		];

		$this->db->insert($this->table_1, $data);
	}
}
