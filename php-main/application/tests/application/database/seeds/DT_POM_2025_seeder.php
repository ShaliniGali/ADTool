<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class DT_POM_2025_seeder extends Seeder {

	private $table_1 = 'DT_POM_2025';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'ASSESSMENT_AREA_CODE' => 'O',
			'BASE_K' => 2999,
			'BUDGET_ACTIVITY_NAME' => 'KNZWPEFVGQXJLBSROHAIUMDTYC',
			'BUDGET_SUB_ACTIVITY_NAME' => 'MW',
			'CAPABILITY_SPONSOR_CODE' => 'STPDGZWFRLYXB',
			'EOC_CODE' => 'DCMXBERYGZTVQSU',
			'EVENT_JUSTIFICATION' => 'HIGYZOVLEXBKCJFSWUNQMRDTPA',
			'EVENT_NAME' => 'JHGDIWLVXSRPYTAMCEZOQBUNKF',
			'EXECUTION_MANAGER_CODE' => 'TISYEXQJZGUNK',
			'FISCAL_YEAR' => 1,
			'OCO_OTHD_K' => 785,
			'OCO_TO_BASE_K' => 4904,
			'OSD_PROGRAM_ELEMENT_CODE' => 'AVOXDICKZP',
			'POM_SPONSOR_CODE' => 'PDNLCKYOWSRUB',
			'PROGRAM_CODE' => 'ESNFUQGIODT',
			'RESOURCE_CATEGORY_CODE' => 'JROQGSEU',
			'RESOURCE_K' => 3437,
			'SPECIAL_PROJECT_CODE' => 1,
			'SUB_ACTIVITY_GROUP_CODE' => 'CHWR',
			'SUB_ACTIVITY_GROUP_NAME' => 'WLYOPCVQEKFGUJISABZTNDXHMR',
		];

		$this->db->insert($this->table_1, $data);

	}
}
