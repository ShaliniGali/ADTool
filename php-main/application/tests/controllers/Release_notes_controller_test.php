<?php
/**
 * @group release
 */
class Release_notes_controller_test extends RhombusControllerTestCase {
    protected const errorString = 'A PHP Error was encountered';
    public function test_index()
    {
        $this->request->addCallable(
            function ($CI) {
                $useraccounttype = $this->getDouble('useraccounttype',
                    [
                        'checkSuperAdmin' => true
                    ]
                );
                $CI->useraccounttype = $useraccounttype;
            }
        );
        $actual = $this->request('GET', '/release_notes');
        $this->assertStringNotContainsString(self::errorString, $actual);
    }

    public function test_index_not_admin() {
        $this->request->addCallable(
            function ($CI) {
                $useraccounttype = $this->getDouble('useraccounttype',
                    [
                        'checkSuperAdmin' => false
                    ]
                );
                $CI->useraccounttype = $useraccounttype;
            }
        );
        $actual = $this->request('GET', '/release_notes');
        $this->assertStringNotContainsString(self::errorString, $actual);
    }
    public function test_get_note() {
        $md = '# 3.1.8

        - July 17, 2023

        - **Bug Fixes**
        * Reset button only resets Program Group, MDS, and Program; not position or position year or the "apply detailed FH What-If" section
        * Slider to toggle "Tour" does not work (Hidden for now)
        * CSS UI fixes from last week

        - **UI changes**
        * Remove pdf icons from all download modals(all pages)
        * On export equations, chart reads copywright 2022. Should be updated to 2023 when possible
        * Leftside hamburger menu has question marks next to each application name. (Icons are not populated on NIPR either; recommend removing altogether or displaying general application icon)

        - **New Features**
        * Adding fsdm version selection for MP and FH
        * Deleting Sessions
        * Separate out session requests into tab and use server side processing to decrease the loading time.

        # 3.1.7

        - July 17, 2023

        - **Bug Fixes**
        * Reset button only resets Program Group, MDS, and Program; not position or position year or the "apply detailed FH What-If" section
        * Slider to toggle "Tour" does not work (Hidden for now)
        * CSS UI fixes from last week

        - **UI changes**
        * Remove pdf icons from all download modals(all pages)
        * On export equations, chart reads copywright 2022. Should be updated to 2023 when possible
        * Leftside hamburger menu has question marks next to each application name. (Icons are not populated on NIPR either; recommend removing altogether or displaying general application icon)

        - **New Features**
        * Adding fsdm version selection for MP and FH
        * Deleting Sessions
        * Separate out session requests into tab and use server side processing to decrease the loading time.';
        MonkeyPatch::patchFunction('file_get_contents', $md, 'Release_notes_controller::get_note');
        $actual = $this->request('GET', '/release_notes/get_note');
        $this->assertStringNotContainsString(self::errorString, $actual);
    }
}
