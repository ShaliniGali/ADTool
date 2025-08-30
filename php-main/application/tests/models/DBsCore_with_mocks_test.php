<?php 
/**
 * @group base
 */
class DBsCore_with_mocks_test extends RhombusModelTestCase 
{

    public function setUp(): void
    {
        parent::setUp();

        MonkeyPatch::patchFunction('defined', true, DBsCore::class);
        $this->schemas = [
            'ACTF',
            'USAFPPBE',
            'SLRD',
            'CAPDEV',
            'TRIAD',
            'COMPETITION',
            'THREAT',
            'WSS',
            'MANPOWER',
            'EAAFM',
            'STRATEGICBASING',
            'CSPI',
            'OBLIGATIONEXPENDITURE',
            'FH',
            'COMBINED',
            'KG',
            'OOB',
            'USSFPPBE'
        ];
        foreach ($this->schemas as $schema) {
            MonkeyPatch::patchConstant(
                $schema . '_SCHEMA',
                'GUARDIAN',
                DBsCore::class . '::getDBConnection'
            );
        }
        // Get object to test
        $this->obj = new DBsCore();
    }
    
    public function test_getDBConnection() {
        foreach ($this->schemas as $schema) {
            $actual = $this->obj->getDBConnection($schema);
            $this->assertIsObject($actual);
        }
    }

}