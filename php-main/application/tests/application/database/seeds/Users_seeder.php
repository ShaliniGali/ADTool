<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class Users_seeder extends Seeder {

	private $table_1 = 'users';

	public function run()
	{
		$this->run_table_1();
	}

	private function run_table_1() {
		$this->db->truncate($this->table_1);

		$data = [
			'id' => 2225,
            'name' => 'DABYGWQVNMIHURXCKZFLTSPJEO',
            'email' => 'unit_tester@rhombuspower.com',
            'password' => 'GDVOIKXNZFWCSAYPQMUHBJTREL',
            'status' => 'Active',
            'timestamp' => 3711,
            'account_type' => 'GYUDSCIBXMAOKVPZJNRWQLEFTH',
            'login_attempts' => 8792,
            'login_layers' => 'NEOYIPMULRZGHJQSWCFVKAXBDT',
            'image' => 'FEKNPMGBCHLJIOWAVYQXDTUZRS',
            'saltiness' => 'RYTCWGVIDLUJQKZXPNHESFBAOM',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
			'id' => 1805,
            'name' => 'GSBCKUQHDEZPOLWNMVAYXIRJFT',
            'email' => 'NORLHXDKSEQPVWYCBZJAIMFUTG',
            'password' => 'FNGYBUHPQOCSIEVKJDLARTWMXZ',
            'status' => 'QTONVZSMHLYABICFEXJKUPWRGD',
            'timestamp' => 3306,
            'account_type' => 'VTLISFOBMENRDZPJQXWCAYHGUK',
            'login_attempts' => 8300,
            'login_layers' => 'DTWSYXIBLJMAERVKCGOHNPZQFU',
            'image' => 'ICJKNSDYLFEVTWQRPGZAXBOHMU',
            'saltiness' => 'IEYRCJVTNPAQGKBOHXMWULFDZS',
		];
		$this->db->insert($this->table_1, $data);
		
		$data = [
            'id' => 5077,
            'name' => 'CGAKLYJSXZUDIPMRTEWOHNVFBQ',
            'email' => 'PCHNKZWYOAGISMURQVTEBLDXFJ',
            'password' => 'QPEZWLXUCDKMRVYHJOANTSGIBF',
            'status' => 'GLQUBZVAYTICHSREXODJKFMWPN',
            'timestamp' => 6114,
            'account_type' => 'INXFJODBAHCSZQEWTGPUMVKRYL',
            'login_attempts' => 4864,
            'login_layers' => 'FMEITLRWVQCSZUOJKXYHNPBADG',
            'image' => 'GWPMZUOKQINDFTLXJAEHVYCBSR',
            'saltiness' => 'GEPNQDVYLOWRIZMCABSJUTFXHK',
		];
		$this->db->insert($this->table_1, $data);
	}
}
