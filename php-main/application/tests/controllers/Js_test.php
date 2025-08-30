<?php
/**
 * @group base
 */
class Js_test extends RhombusControllerTestCase {

    public function test_vars() {

        $actual = $this->request('POST','Js/vars',[
            'nothing' => 'nothing'
        ]);
        $this->assertIsString($actual);
    }
}
?>