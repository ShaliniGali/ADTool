<?php
class UserAccountType_test extends TestCase 
{
    private $accountTypes = [
        [
            'type' => 'ADMIN',
            'privilege' => 0
        ],
        [
            'type' => 'MODERATOR',
            'privilege' => 1
        ],
        [
            'type' => 'USER',
            'privilege' => 2
        ]
    ];
    
    public function setUp(): void
    {
        parent::setUp();
        // Get object to test
        $this->obj = new UserAccountType();
    }

    public function test_isValidAccountType() {
        $actual = $this->obj->isValidAccountType('USER');
        $this->assertTrue($actual);
    }

    public function test_getAllAccounts() {
        $actual = $this->obj->getAllAccounts();
        $this->assertEquals($this->accountTypes, $actual);
    }

    public function test_getleastprivilege() {
        $actual = $this->obj->getleastprivilege();
        $this->assertEquals($this->accountTypes[max(array_column($this->accountTypes,"privilege"))], $actual);
    }

    public function test_getleastprivilege_db_true() {
        $USER_TYPE = USER_TYPE;
        
        MonkeyPatch::patchConstant('USER_TYPE', ['use-db' => true, 'column' => 'name']);

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => '',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $actual = $this->obj->getleastprivilege();
        $this->assertEquals('undefined privileges', $actual);

        //MonkeyPatch::patchConstant('USER_TYPE',  $USER_TYPE);
    }

    public function test_generateAccountTypeMenu_null() {
        $actual = $this->obj->generateAccountTypeMenu();
        $this->assertStringContainsString('<option value="ADMIN">ADMIN</option>', $actual);

    }
    public function test_generateAccountTypeMenu_radio() {
        $actual = $this->obj->generateAccountTypeMenu('Radio');
        $this->assertStringContainsString('<input type="radio" id="ADMIN" name="account_type" value="ADMIN"', $actual);
    }

    public function test_checkSuperAdmin() {
        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $actual = $this->obj->checkSuperAdmin();
        $this->assertFalse($actual);
    }
}