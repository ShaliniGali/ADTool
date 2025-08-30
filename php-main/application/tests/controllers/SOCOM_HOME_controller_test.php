​​​​​​​​​​​​​​​<?php
/**
 * @group base
 * @group socom
 */
class SOCOM_HOME_controller_test extends RhombusControllerTestCase
{
    public function test_index(){
        $actual = $this->request('POST', '/socom/index',
        []
        );

        $this->assertIsString($actual);
    }

    public function test_zbt_summary(){
        $cap_sponsor_results_data = [
          'cap_sponsor_count' => 1,
          'total_zbt_events' => 2,
        ];
        $cap_sponsor_dollar_data = [
            'cap_sponsor_dollar' => 1,
        ];
        $net_change_data = 100;
        $cap_sponsor_approve_reject_data = [
            'categories' => ['cat', 'dog'],
            'series_data' => [1, 2]
        ];
        $dollars_moved_resource_category_data = [
            'fiscal_years' => [2024, 2025],
            'series_data' => [1, 2]
        ];
        $SOCOM_mock = [
                'cap_sponsor_count' => $cap_sponsor_results_data,
                'cap_sponsor_dollar' => $cap_sponsor_dollar_data,
                'net_change' => $net_change_data,
                'cap_sponsor_approve_reject' => $cap_sponsor_approve_reject_data,
                'dollars_moved_resource_category' => $dollars_moved_resource_category_data
        ];
        $this->request->addCallable(
            function ($CI) use ($SOCOM_mock) {
                $SOCOM_model = $this->getDouble('SOCOM_model',$SOCOM_mock);
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', '/socom/zbt_summary');

        $this->assertIsString($actual);
    }

    public function test_program_summary(){
        $get_sponsor_data = [];
        $get_assessment_area_code_data = '1';
        $zbt_program_summary_card_data = [
            'total_zbt_events' => 1,
            'dollars_moved' => 120,
            'net_change' => 1
        ];
        $get_user_assigned_tag_data = [
            ['Tag', 'Title']
        ];
        $get_user_assigned_bin_data = [
            'lvl1','force', 'lvl2', 'air', 'lvl3', 'navy'
        ];
        $get_program_summary_data = [
            'base_k' => [
                0 => [
                    'FISCAL_YEARS' => '2023, 2024'
                ],
                1 => [
                    'FISCAL_YEARS' => '2023, 2024'
                ]
            ]
        ];
        $SOCOM_mock = [
                'get_sponsor' => $get_sponsor_data,
                'get_assessment_area_code' => $get_assessment_area_code_data,
                'zbt_program_summary_card' => $zbt_program_summary_card_data,
                'get_user_assigned_tag' => $get_user_assigned_tag_data,
                'get_user_assigned_bin' => $get_user_assigned_bin_data,
                'get_program_summary' => $get_program_summary_data
        ];
        $this->request->addCallable(
            function ($CI) use ($SOCOM_mock) {
                $SOCOM_model = $this->getDouble('SOCOM_model',$SOCOM_mock);
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $actual = $this->request('GET', '/socom/program_summary');

        $this->assertIsString($actual);
    }

    public function test_get_dollars_moved_resource_category(){
        $dollars_moved_resource_category_data = [
            'fiscal_years' => [2024, 2025],
            'series_data' => [1, 2]
        ];
        $SOCOM_mock = [
                'dollars_moved_resource_category' => $dollars_moved_resource_category_data
        ];
        $this->request->addCallable(
            function ($CI) use ($SOCOM_mock) {
                $SOCOM_model = $this->getDouble('SOCOM_model',$SOCOM_mock);
                $CI->SOCOM_model = $SOCOM_model;
            }
        );
        $post_data = [
            'result' => true
        ];
        $actual = $this->request('POST',
            '/socom/get_dollars_moved_resource_category', $post_data);

        $this->assertIsString($actual);
    }
}
?>
