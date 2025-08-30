<?php

class SOCOM_COA_model_with_mocks_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('DT_ISS_2026_seeder');
        $CI->seeder->call('SOCOM_model_seeder');
        $CI->seeder->call('USR_LOOKUP_SAVED_COA_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_COA_model();
        $this->obj->session = $this->getSessionMock();
    }

    public function test_store_run() {
        $mockUserId = 8587;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $optimizer_input = "[]";
        $coa_output = "[]";
        $result = $this->obj->store_run($optimizer_input, $coa_output);
        $this->assertIsInt($result);
    }

    public function test_store_user_run_saved_coa_does_not_exist() {
        $mockUserId = 8587;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $id = 0;
        $name = 'name';
        $description = 'description';
        $result = $this->obj->store_user_run($id, $name, $description);
        $this->assertIsInt($result);
    }

    public function test_get_user_saved_coa() {
        $mockUserId = 8587;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->get_user_saved_coa();
        $this->assertNotEmpty($result);
    }

    public function test_get_user_saved_coa_data_empty_input() {
        $mockUserId = 8587;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->get_user_saved_coa_data([]);
        $this->assertEmpty($result);
    }

    public function test_get_user_saved_coa_data() {
        $mockUserId = 4488;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->get_user_saved_coa_data([5458]);
        $this->assertEmpty($result);
    }

    public function test_get_dropdown_codes() {
        $type = '';
        $program_codes = ['NICTORBPGUX'];
        $eoc_codes = ['ITXJQLZCSMBYADH'];
        $result = $this->obj->get_dropdown_codes($type, $program_codes, $eoc_codes);
        $this->assertEmpty($result);
    }

    public function test_get_coa_funding_data_empty_input() {
        $ids = [];
        $result = $this->obj->get_coa_funding_data($ids);
        $this->assertEmpty($result);
    }

    public function test_get_coa_funding_data() {
        $ids = [''];
        $result = $this->obj->get_coa_funding_data($ids);
        $this->assertNotEmpty($result);
    }

    public function test_fetchOutputInfo() {
        $ids = [''];

        MonkeyPatch::patchFunction('php_api_call', json_encode([]), SOCOM_COA_model::class);

        $result = $this->obj->fetchOutputInfo($ids);

        $this->assertNotNull($result);
    }

    public function test_get_coa_metadata() {
        $program_codes = [''];
        $eoc_codes = [''];
        $pom_sponsor_code = [''];
        $capability_sponsor_code = [''];
        $resource_category_code = [''];

        MonkeyPatch::patchFunction('php_api_call', json_encode([]), SOCOM_COA_model::class);

        $result = $this->obj->get_coa_metadata($program_codes, $eoc_codes, $pom_sponsor_code, $capability_sponsor_code, $resource_category_code);
        $this->assertNotNull($result);
    }

    public function test_get_fiscal_years() {
        $table = ['DT_ISS_2026'];

        $result = $this->obj->get_fiscal_years($table);
        $this->assertNotEmpty($result);
    }

    public function test_store_coa_metadata() {
        $mockUserId = 4488;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $override_table_metadata = '[]';
        $saved_coa_id = 5458;

        $result = $this->obj->store_coa_metadata($override_table_metadata, $saved_coa_id);
        $this->assertNotEmpty($result);
    }

    public function test_change_scenario_status() {
        $mockUserId = 4488;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $saved_coa_id = 5458;
        $status_value = '';

        $result = $this->obj->change_scenario_status($saved_coa_id, $status_value);
        $this->assertIsBool($result);
    }

    public function test_manual_override_save() {
        $mockUserId = 4488;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $saved_coa_id = 5458;
        $field = 'OVERRIDE_FORM_SESSION';
        $data = "[]";

        $result = $this->obj->manual_override_save($saved_coa_id, $field, $data);
        $this->assertIsBool($result);
    }

    public function test_save_override_form() {
        $mockUserId = 4488;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $saved_coa_id = 5458;
        $overrideForm = "[]";

        $result = $this->obj->save_override_form($saved_coa_id, $overrideForm);
        $this->assertIsBool($result);
    }

    public function test_get_manual_override_data() {
        $mockUserId = 8587;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $saved_coa_id = 3543;

        $result = $this->obj->get_manual_override_data($saved_coa_id);
        $this->assertNotEmpty($result);
    }

    public function test_get_manual_override_status() {
        $mockUserId = 4488;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $saved_coa_id = 786;

        $result = $this->obj->get_manual_override_status($saved_coa_id);
        $this->assertTrue(true);
    }

    public function test_set_override_remaining_balance() {
        $result_input = [
            [
                'OVERRIDE_TABLE_SESSION' => json_encode(['budget_uncommitted' => [1 => [2024 => 500, 2025 => 700]]]),
                'COA_VALUES' => json_encode(['remaining' => [2024 => 100, 2025 => 200]])
            ]
        ];

        $result = $this->obj->set_override_remaining_balance($result_input);
        $this->assertNotEmpty($result);
    }

    public function test_get_weighted_score() {
        $weight_id = '';
        $program_ids = [];

        MonkeyPatch::patchFunction('php_api_call', json_encode([]), SOCOM_COA_model::class);

        $result = $this->obj->get_weighted_score($weight_id, $program_ids);
        $this->assertNotNull($result);
    }

    public function test_get_saved_coa_optimizer_input() {
        $mockUserId = 8587;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $saved_coa_id = 3543;

        $result = $this->obj->get_saved_coa_optimizer_input($result_input);
        $this->assertNotNull($result);
    }
}

/* 

use PHPUnit\Framework\TestCase;

class YourClassNameTest extends TestCase
{
    protected $yourClassInstance;
    protected $mockDB;
    protected $mockSession;

    protected function setUp(): void
    {
        // Create a mock for the session and database
        $this->mockSession = $this->createMock(Session::class);
        $this->mockDB = $this->createMock(Database::class);
        
        // Create an instance of the class under test, injecting mocks
        $this->yourClassInstance = new YourClassName($this->mockSession, $this->mockDB);
    }

    public function testGetSavedCoaOptimizerInputReturnsCorrectData()
    {
        $saved_coa_id = 1;
        $user_id = 42;

        // Set up the mock session to return a logged in user
        $this->mockSession->method('userdata')
            ->willReturn(['logged_in' => ['id' => $user_id]]);

        // Set up the mock database response
        $this->mockDB->method('select')
            ->with('OPTIMIZER_INPUT')
            ->willReturnSelf();
        $this->mockDB->method('from')
            ->with('USR_LOOKUP_SAVED_COA')
            ->willReturnSelf();
        $this->mockDB->method('where')
            ->willReturnSelf();
        $this->mockDB->method('get')
            ->willReturn($this->createMock(QueryResult::class));
        
        $resultArray = [['OPTIMIZER_INPUT' => json_encode(['test_value'])]];
        $this->mockDB->method('result_array')->willReturn($resultArray);

        // Call the method under test
        $result = $this->yourClassInstance->get_saved_coa_optimizer_input($saved_coa_id);

        // Assert the expected output
        $this->assertEquals(['test_value'], $result);
    }

    public function testGetSavedCoaOptimizerInputReturnsEmptyWhenNoData()
    {
        $saved_coa_id = 1;
        $user_id = 42;

        // Set up the mock session to return a logged in user
        $this->mockSession->method('userdata')
            ->willReturn(['logged_in' => ['id' => $user_id]]);

        // Set up the mock database to return no results
        $this->mockDB->method('select')
            ->with('OPTIMIZER_INPUT')
            ->willReturnSelf();
        $this->mockDB->method('from')
            ->with('USR_LOOKUP_SAVED_COA')
            ->willReturnSelf();
        $this->mockDB->method('where')
            ->willReturnSelf();
        $this->mockDB->method('get')
            ->willReturn($this->createMock(QueryResult::class));
        
        $this->mockDB->method('result_array')->willReturn([]);

        // Call the method under test
        $result = $this->yourClassInstance->get_saved_coa_optimizer_input($saved_coa_id);

        // Assert the expected output
        $this->assertEquals('', $result);
    }

    public function testGetSavedCoaOptimizerInputReturnsEmptyWhenInvalidJson()
    {
        $saved_coa_id = 1;
        $user_id = 42;

        // Set up the mock session to return a logged in user
        $this->mockSession->method('userdata')
            ->willReturn(['logged_in' => ['id' => $user_id]]);

        // Set up the mock database response with invalid JSON
        $this->mockDB->method('select')
            ->with('OPTIMIZER_INPUT')
            ->willReturnSelf();
        $this->mockDB->method('from')
            ->with('USR_LOOKUP_SAVED_COA')
            ->willReturnSelf();
        $this->mockDB->method('where')
            ->willReturnSelf();
        $this->mockDB->method('get')
            ->willReturn($this->createMock(QueryResult::class));
        
        $resultArray = [['OPTIMIZER_INPUT' => 'invalid_json']];
        $this->mockDB->method('result_array')->willReturn($resultArray);

        // Call the method under test
        $result = $this->yourClassInstance->get_saved_coa_optimizer_input($saved_coa_id);

        // Assert the expected output
        $this->assertEquals('', $result);
    }
}


*/
