<?php
/**
 * @group base
 */
class Home_controller_test extends RhombusControllerTestCase {
    public function test_index(){
        $actual = $this->request('POST', '/index',
        []
        );

        $this->assertEquals('SOCOM Home', $page_data['page_title']);
    }
}