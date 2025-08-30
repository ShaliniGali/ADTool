<?php
/**
 * @group base
 */
class Login_private_subnet_model_test extends RhombusModelTestCase {
    public function setUp(): void
    {
        parent::setUp();

        // Set obj to test
        $this->obj = new Login_private_subnet_model();

        // Inject mock objects into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();
    }

    public function test_enforcePrivateSubnetLogin() {
        $this->assertNotTrue($this->obj->enforcePrivateSubnetLogin());
    }
    
    public function test_has_access() {
        $result = [];
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV->method('get')->willReturn($dbResult);

        $privateIp = 'test';
        $actual = $this->obj->has_access($privateIp);
        $this->assertIsBool($actual);

        $privateIp = null;
        $server_name = 'test';
        $actual = $this->obj->has_access($privateIp, $server_name);
        $this->assertIsBool($actual);
    }
}