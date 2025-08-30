<?php
/**
 * @group base
 */
class Users_keys_model_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new Users_keys_model();

        // Inject mock object into the model
        $this->obj->DBs->GUARDIAN_DEV = $this->getMethodChainingDBMock();

    }

    public function test_insert_json_with_existing_key() {

        $result = array(
            array('user_id' => 1)
        );

        $insert_obj = array(
            'yubi_key' => '6f06526e28b51175'
        );

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('update')
            ->willReturn(true);

        $actual = $this->obj->insert_json(1,$insert_obj,'yubi_key');

        $this->assertTrue($actual);
    }

    public function test_insert_json_with_new_key() {

        $result = array();

        $insert_obj = array(
            'yubi_key' => '6f06526e28b51174'
        );

        $fields = array(
            'id',
            'user_id',
            'google_key',
            'yubi_key',
            'cac_key',
            'recover_key',
            'login_token',
            'admin_expiry'
        );

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result,$fields);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('list_fields')
            ->willReturn($fields);

        $this->obj->DBs->GUARDIAN_DEV
            ->method('insert')
            ->willReturn(true);

        

        $actual = $this->obj->insert_json(2,$insert_obj,'yubi_key');

        $this->assertTrue($actual);
    }

    public function test_get_json_value_by_key_with_null_key() {

        $result = [
            ['yubi_key' => '{"yubi_key":"6f06526e28b51175"}']
        ];

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->get_json_value_by_key(1,'yubi_key',null);

        $this->assertIsArray($actual);
    }


    public function test_get_json_value_by_key_with_key() {

        $key = '6f06526e28b51175'; 
        
        $result = [
            ['yubi_key' => '{"6f06526e28b51175":"6f06526e28b51175"}']
        ];

        $result2 = array(
            array(
                'keys' => '["6f06526e28b51175"]'
            )
        );

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result,$result2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->get_json_value_by_key(1,'yubi_key',$key);

        $this->assertSame($actual,$key);
    }

    public function test_get_json_value_by_key_with_undefined_json_key() {

        $key = '6f06526e28b51175'; 
        $result = [
            ['yubi_key' => '{"6f06526e28b51175":"6f06526e28b51175"}']
        ];

        $result2 = array(
            array(
                'keys' => '["6f06526e28b511"]'
            )
        );

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result,$result2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $actual = $this->obj->get_json_value_by_key(1,'yubi_key',$key);

        $this->assertArrayHasKey('message',$actual);
        $this->assertArrayHasKey('status',$actual);

    }

    public function test_set_json_value_by_key() {

        $key = '6f06526e28b51175';
        $column = 'yubi_key';
        $id = 1;
        $new_key = hash('sha256', 'test user updated yubi key');

        $result = array(
                    array(
                        'keys' => '["6f06526e28b51175"]'
                    )
                );
       

        $result2 = array(
                        array(
                            'type' => 'User key with mocks test'
                        )
                    );

        $dbResult = $this->getMockBuilder('CI_DB_result')
        ->disableOriginalConstructor()
        ->getMock();
        
        $dbResult->method('result_array')->willReturnOnConsecutiveCalls($result,$result2);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);

        $this->obj->DBs->GUARDIAN_DEV->method('update')->willReturn(TRUE);
        
        // Call and test
        $actual = $this->obj->set_json_value_by_key(1,$key,$new_key,$column);

        $this->assertNull($actual);
        

    }

    public function test_get_yubikey() {
        
        $result = array(
                    array(
                        'yubi_key' => hash('sha256', 'test user yubi key')
                    )
                );
        
            $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
            
            $dbResult->method('result_array')->willReturn($result);
            $this->obj->DBs->GUARDIAN_DEV
                ->method('get')
                ->willReturn($dbResult);

            
            // Call and test
            $actual = $this->obj->get_yubikey(1);

            $this->assertEquals($result[0]['yubi_key'], $actual);

    }

    public function test_get_admin_expiry_date() {

        $date = new DateTime();

        $result = [
            [
                "admin_expiry"    => $date->getTimestamp()
            ] 
        ];

        // Create mock object for CI_DB_result
        $dbResult = $this->getMockBuilder('CI_DB_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbResult->method('result_array')->willReturn($result);
        $this->obj->DBs->GUARDIAN_DEV
            ->method('get')
            ->willReturn($dbResult);
    
        // Call and test
        $actual = $this->obj->get_admin_expiry_date(1);

        $this->assertEquals($result[0]['admin_expiry'], $actual);
    }
}
?>