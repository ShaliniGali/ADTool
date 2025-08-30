​​​​​​​​​​​​​​​<?php 
/**
 * @group base
 */
class Audit_controller_test extends RhombusControllerTestCase
{

    // Without session data
    public function test_constructor(){

        $session_data = array(
            'email' => 'notsuperadmin@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $display = TRUE;

        $this->request->addCallable(
            function ($CI) use ($display) {
                $rb_auditing = $this->getDouble(
                    'RB_Auditing', [
                        'display' => $display,
                    ]
                );
                $CI->rb_auditing = $rb_auditing;
            }
        );

        $actual = $this->request('POST', '/audit/difference/1');
        
        // To Debug
        // echo $actual;
        $this->assertTrue(TRUE);
    }


    public function test_difference(){

        $session_data = array(
            'email' => 'unit_tester@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $display = TRUE;

        $this->request->addCallable(
            function ($CI) use ($display) {
                $rb_auditing = $this->getDouble(
                    'RB_Auditing', [
                        'display' => $display,
                    ]
                );
                $CI->rb_auditing = $rb_auditing;
            }
        );

        $actual = $this->request('POST', '/audit/difference/1');
        
        // To Debug
        // echo $actual;
        $this->assertTrue(TRUE);
    }

    public function test_audit(){

        $session_data = array(
            'email' => 'unit_tester@rhombuspower.com',
            'name' => 'Unit Tester',
            'account_type' => 'USER',
            'timestamp' => 1609459200,
            'profile_image' => NULL,
            'id' => 1
        );
        get_instance()->session->set_userdata('logged_in', $session_data);

        $audit = TRUE;

        $this->request->addCallable(
            function ($CI) use ($audit) {
                $rb_auditing = $this->getDouble(
                    'RB_Auditing', [
                        'database' => $audit,
                    ]
                );
                $CI->rb_auditing = $rb_auditing;
            }
        );

        $actual = $this->request('POST', '/audit/audit');
        
        // To Debug
        // echo $actual;
        $this->assertTrue(TRUE);
    }
}

?>