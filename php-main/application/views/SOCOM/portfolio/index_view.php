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
    $js_files['datatable'] = ["datatables_v1.min.js", 'global'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $js_files['portfolio'] = ['actions/SOCOM/portfolio/portfolio.js','custom'];
    $js_files['drilldown_filter'] = ['actions/SOCOM/portfolio/drilldown_filter.js','custom'];
    $js_files['dropdown_filter'] = ['actions/SOCOM/portfolio/dropdown_filter.js','custom'];
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<style>
    .content-wrapper > header {
        background-color: var(--cds-ui-background,#ffffff);
        padding: var(--cds-spacing-05, 16px) var(--cds-spacing-07, 32px) var(--cds-spacing-05, 16px);
    }

    .tab-content-wrapper {
        flex: 1 !important;
        overflow: auto;
    }

    .tab-bar {
        background-color: var(--cds-ui-03, #e0e0e0);
        padding: 0 var(--cds-spacing-07, 32px);
    }
    .content-wrapper {
        height: calc(100% - 64px);
        background-color: var(--cds-ui-01, #f4f4f4);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .portfolio-banner {
        width: 100%;
        background: #139500 !important;
        text-align: center;
        padding: 8px;
        color: white;
        font-weight: 600;
        font-size: small;
    }
</style>
<?php $this->load->view('SOCOM/toast_notifications');?>
<div id="overlay-loader"></div>
<div class="content-wrapper">
    <header>
        <?php $this->load->view('SOCOM/header_buttons_view',
            array('current_page'=>'Portfolio Viewer', 'page' => 'portfolio_viewer', 'page_summary_path' => 'portfolio_viewer')
        );?>
    </header>
    <!-- <span id="portfolio-banner" class="portfolio-banner">
        <?php // echo $banner; ?>
    </span> -->
    <div class="d-flex flex-column" style="background-color: #EFEFEF; overflow-y:scroll;">
        <div>
            <div data-tabs class="bx--tabs bx--tabs--container">
                <div class="bx--tabs-trigger" tabindex="0">
                    <a href="javascript:void(0)" class="bx--tabs-trigger-text" tabindex="-1"></a>
                    <svg focusable="false" preserveAspectRatio="xMidYMid meet"
                    style="will-change: transform;" xmlns="http://www.w3.org/2000/svg"
                        width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 11L3 6 3.7 5.3 8 9.6 12.3 5.3 13 6z"></path></svg>
                </div>
                <ul id="portfolio-viewer-tabs" class="bx--tabs__nav bx--tabs__nav--hidden w-100" role="tablist">
                    <li
                    data-type="budget-trend-overview"
                    data-selected="false"
                    class="bx--tabs__nav-item bx--tabs__nav-item--selected w-100"
                    data-target=".tab-budget-trend-overview-container" role="tab"  aria-selected="true"  >
                    <a tabindex="0" id="tab-link-budget-trend-overview-container" class="bx--tabs__nav-link w-100"
                    href="javascript:void(0)" role="tab"
                        aria-controls="tab-panel-budget-trend-overview-container">Budget Trend Overview</a>
                    </li>
                    <li
                    data-type="program-execution-drilldown"
                    data-selected="false"
                    class="bx--tabs__nav-item w-100"
                    data-target=".tab-program-execution-drilldown-container" role="tab"  >
                    <a tabindex="0" id="tab-link-program-execution-drilldown-container"
                    class="bx--tabs__nav-link w-100" href="javascript:void(0)" role="tab"
                        aria-controls="tab-panel-program-execution-drilldown-container">Program Execution Drill Down</a>
                    </li>
                    <li
                    data-type="compare-programs"
                    data-selected="false"
                    class="bx--tabs__nav-item w-100"
                    data-target=".tab-compare-programs-container" role="tab"  >
                    <a tabindex="0" id="tab-link-compare-programs-container"
                    class="bx--tabs__nav-link w-100" href="javascript:void(0)" role="tab"
                        aria-controls="tab-panel-compare-programs-container">Compare Programs</a>
                    </li>
                </ul>
            </div>
            <!-- The markup below is for demonstration purposes only -->
            <div class="bx--tab-content tab-content-background">
                <div id="tab-link-budget-trend-overview-container" class="tab-budget-trend-overview-container tab-content-wrapper d-flex"
                role="tabpanel" aria-labelledby="tab-link-budget-trend-overview-container"
                    aria-hidden="false" >
                    <div class="d-flex flex-column w-100">
                        <?php $this->load->view('SOCOM/portfolio/budget_trend_overview_view.php'); ?>
                    </div>

                </div>
                <div id="tab-panel-program-execution-drilldown-container" class="tab-program-execution-drilldown-container"
                role="tabpanel" aria-labelledby="tab-link-program-execution-drilldown-container"
                    aria-hidden="true" hidden>
                    <div class="d-flex flex-column w-100">
                        <?php $this->load->view('SOCOM/portfolio/program_execution_drilldown_view.php'); ?>
                    </div>
                </div>
                <div id="tab-panel-compare-programs-container" class="tab-compare-programs-container"
                role="tabpanel" aria-labelledby="tab-link-compare-programs-container"
                    aria-hidden="true" hidden>
                    <div class="d-flex flex-column w-100">
                        <?php $this->load->view('SOCOM/portfolio/compare_programs_view.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // $("#portfolio-viewer-tabs > .bx--tabs__nav-item").on('click', function(){
    //     let targetContainer = $(this).attr('data-target');
    //     let targetContainerType = $(this).attr('data-type');

    //     if (targetContainerType == 'compare-programs' &&  $(this).attr('data-selected') == 'false') {
    //         $(this).attr('data-selected', 'true');
    //         $('#overlay-loader').html(overlay_loading_html);
    //         compareProgramsOnReady();
    //     }
    // });
</script>