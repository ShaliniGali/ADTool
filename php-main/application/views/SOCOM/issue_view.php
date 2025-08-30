<?php

  $this->load->view('templates/essential_javascripts');

?>

<?php
    $js_files = array();
    $CI = &get_instance();
    
    $js_files['toggle_theme'] = ["actions/SOCOM/toggle_theme.js", 'custom'];
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
    $js_files['select2'] = ["select2.full.js", 'global'];
    $js_files['highstock'] = ["highstock.js", 'global'];
    $js_files['heatmap'] = ["heatmap.js", 'global'];
    $js_files['exporting'] = ["exporting.js", 'global'];
    $js_files['no_data'] = ["no-data-to-display.js", 'global'];
    $js_files['highcharts_more'] = ["highcharts-more.js", 'global'];
    $js_files['accessibility'] = ["accessibility.js", 'global'];
    $js_files['ion_range'] = ["ion.rangeSlider.js", 'global'];
    $js_files['highcharts_p1'] = ['actions/p1_highcharts.js','custom'];

    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<div class="content-wrapper">
    <header>
        <?php $this->load->view('SOCOM/header_buttons_view',
        array('current_page'=>'Issue Summary', 'page' => $page,  'page_summary_path' => strtolower($page) ));?>
    </header>
    <div class="grey-background">
        <div class="d-flex flex-row justify-content-between">
            <div class="w-33 pie-chart-container" id="pie-1"> Pie 1</div>
            <div class="w-33 pie-chart-container" id="pie-2"> Pie 2</div>
            <div class="w-33 pie-chart-container">
                <div class="exec-container-child-1 h-100">
                    <span class="bold-labels" style="margin-left:0px;">Number of Issues:</span>
                        <div class="yellow-box" >
                            <p id="executed-hours"><?= $total_events; ?></p>
                        </div>
                        <p class="hours-label">Net change will be zero unless there are non-zero balances</p>
                        <div class="hours-parent">
                            <div class="hours-child-1">
                                Dollars (Thousands) Moved:
                                <div id="programmed-hours">$<?= $dollars_moved; ?></div>
                            </div>
                            <div class="hours-child-2">
                                Net Change:
                                <div id="required-hours" >$<?= $net_change;?></div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-row">
            <div class="w-50 chart-container" id="chart-1"> Chart1</div>
            <div class="w-50 chart-container" id="chart-2"> Chart2</div>
        </div>
    </div>
</div>

<script>
    const base_url_js = <?php echo json_encode(base_url()); ?>;
    const page = <?= json_encode($page); ?>;
    var cap_sponsor_count = <?= json_encode($cap_sponsor_count); ?>;
    var cap_sponsor_dollar = <?= json_encode($cap_sponsor_dollar); ?>;
    var dollars_move_fiscal_years = <?= json_encode($dollars_move_fiscal_years); ?>;
    var dollars_move_series_data = <?= json_encode($dollars_move_series_data); ?>;
    var cap_sponsor_approve_reject_categories = <?= json_encode($cap_sponsor_approve_reject_categories); ?>;
    var cap_sponsor_approve_reject_series_data = <?= json_encode($cap_sponsor_approve_reject_series_data); ?>;
    var user_emails = <?= json_encode($user_emails); ?>;
</script>

<?php
    $js_files = array();
    $CI = &get_instance();
    $js_files['s_home'] = ['actions/SOCOM/home.js','custom'];
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

