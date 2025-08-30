<style>

    #cs-dropdown .select2-selection__rendered,
    #ass-area-dropdown .select2-selection__rendered,
    #program-dropdown .select2-selection__rendered,
    #resource_category-dropdown .select2-selection__rendered,
    #approval-status-dropdown .select2-selection__rendered,
    #pom-dropdown .select2-selection__rendered {
        margin: unset !important
    }

    #program-breakdown-stacked-chart-container {
        overflow-y: unset !important;
    }

    #program-breakdown-stacked-chart{
        min-height: 100vh !important;
        width: 100% !important;
    }
</style>



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
    $js_files['tabulator-tables'] = ['tabulator-tables.min.js','global'];
    $js_files['datatable'] = ["datatables_v1.min.js", 'global'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];

    if(P1_FLAG){
        $js_files['Editor'] = ["Editor.js", 'global'];
    }

    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<?php $this->load->view('SOCOM/toast_notifications'); ?>

<div class="content-wrapper" style="overflow: scroll;">
    <header>
        <?php $this->load->view('SOCOM/header_buttons_view',
            array(
                'current_page'=> $page_title . ' Program Breakdown',
                'page' => $page,
                'breadcrumb_text' =>  $breadcrumb_text,
                'page_summary_path' => $page_summary_path,
                'program' => $selected['program']
            )
        );?>
        <?php if ($page == 'zbt_summary'): ?>
            <div class="mt-3">
                <p>ZBT Summary is Connected with Position Year FY<?php echo $subapp_pom_year ?></p>
            </div>
        <?php endif; ?>
        <?php if ($page == 'issue'): ?>
            <div class="mt-3">
                <p>Issue Summary is Connected with Position Year FY<?php echo $subapp_pom_year ?></p>
            </div>
        <?php endif; ?>
    </header>
    <div class="grey-background">
        <div id="filter-summary-row" class="d-flex flex-row justify-content-between">
            <div class="w-66 filter-container">
                <div class="d-flex flex-row">
                    <div id="cs-dropdown" class="d-flex flex-column mr-4 mt-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Capability Sponsor</div>
                            <div>
                                <button id="cs-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                data-select-all="true"
                                type="button" onclick="dropdown_selection('#cs-<?= $id ?>')"> 
                                    Select All 
                                </button>
                             </div>
                        </div>
                        <select
                                id="cs-<?= $id ?>"
                                type="cs"
                                combination-id=""
                                class="selection-dropdown wss-selections"
                                onchange="dropdown_onchange(1, 'cs')"
                                multiple="multiple"
                                >
                            <option option="ALL" <?= $selected['cs'] ? '' : 'selected'; ?> value="ALL">ALL</option>
                            <?php foreach($capability_sponsor as $value): ?>
                                <option value="<?= $value['SPONSOR_CODE']?>"><?= $value['SPONSOR_TITLE']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="ass-area-dropdown" class="d-flex flex-column mr-4 mt-3" >
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Assessment Area Code</div>
                            <div>
                                <button id="ass-area-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                data-select-all="true"
                                type="button" onclick="dropdown_selection('#ass-area-<?= $id ?>')"> 
                                    Select All 
                                </button>
                             </div>
                        </div>
                        <select
                                id="ass-area-<?= $id ?>"
                                type="ass-area"
                                combination-id=""
                                class="selection-dropdown wss-selections"
                                multiple="multiple"
                                onchange="dropdown_onchange(1, 'ass-area')"
                                >
                            <option option="ALL" <?= $selected['ass_area'] ? '' : 'selected'; ?> value="ALL">ALL</option>
                            <?php foreach($ass_area as $value): ?>
                                <option value="<?= $value['ASSESSMENT_AREA_CODE']?>"><?= $value['ASSESSMENT_AREA']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="program-dropdown" class="d-flex flex-column mr-4 mt-3" >
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Program Group</div>
                            <div>
                                <button id="program-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                data-select-all="true" disabled
                                type="button" onclick="dropdown_selection('#program-<?= $id ?>')"> 
                                    Select All 
                                </button>
                             </div>
                        </div>
                        <select 
                            id="program-<?= $id ?>" 
                            type="program" 
                            combination-id="" 
                            class="selection-dropdown" 
                            multiple="multiple"
                            onchange="dropdown_onchange(1, 'program')"
                            disabled
                        >
                            <option></option>
                        </select>
                    </div>


                    <div id="approval-dropdown" class="d-flex flex-column mr-4 mt-3 justify-content-end" >
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Approval</div>
             
                        </div>
                        <select 
                            id="approval-<?= $id ?>" 
                            type="approval" 
                            combination-id="" 
                            class="selection-dropdown"
                            onchange="dropdown_onchange(1, 'approval')"
                            disabled
                        >
                            <option></option>
                            <?php foreach($approval as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex flex-column align-self-end" >
                        <button
                            id="program-summary-filter"
                            class="bx--btn bx--btn--primary header-button bx--btn--field"
                            type="button"
                            onclick="dropdown_onchange(1, 'filter')"
                            disabled
                        >
                            Apply Filter
                            <div id="apply-filter-loading" hidden>
                                <div data-loading class="bx--loading bx--loading--small">
                                    <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
                                        <title>Loading</title>
                                        <circle class="bx--loading__background" cx="0" cy="0" r="26.8125" />
                                        <circle class="bx--loading__stroke" cx="0" cy="0" r="26.8125" />
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
                
                <div>
                    <div class="mt-3">
                        <button
                            id="save-manual-changes"
                            class="bx--btn bx--btn--primary header-button bx--btn--field"
                            disabled
                            type="button"
                            onclick="dropdown_onchange(1, 'save')"
                        >
                            Data Refresh
                            <div id="apply-save-loading" hidden>
                                <div data-loading class="bx--loading bx--loading--small">
                                    <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
                                        <title>Loading</title>
                                        <circle class="bx--loading__background" cx="0" cy="0" r="26.8125" />
                                        <circle class="bx--loading__stroke" cx="0" cy="0" r="26.8125" />
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="d-flex flex-column justify-content-center table-container mt-1 mb-5" id="historical-pom-data-container" hidden>
            
        </div>
        <div class="d-flex flex-column justify-content-center table-container mt-1 mb-5" id="eoc-summary-container" hidden>
            
        </div>
        <div class="d-flex flex-column justify-content-center table-container mt-1 mb-5" id="eoc-historical-pom-data-container" hidden>
            
        </div>
        <!-- <div class="d-flex flex-row justify-content-center table-container mt-1 mb-5" id="program-table-container">
            <?php //$this->load->view('SOCOM/program_table_view', $data['program_summary_data']); ?>
        </div> -->
        <div class="d-flex flex-row justify-content-center table-container mt-5 mb-5" id="program-breakdown-stacked-chart-container">
            <div id="program-breakdown-stacked-chart">
                <div class="d-flex w-100 p-2 justify-content-center">
                    <h2>Click Apply Filter to see data</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $js_files = array();
    $CI = &get_instance();
    $js_files['s_home'] = ['actions/SOCOM/program_breakdown.js','custom'];
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<script>
const page = <?= json_encode($page); ?>;
const user_emails = <?= json_encode($user_emails); ?>;
selected_options = <?= json_encode($selected, true); ?>;

$(".selection-dropdown").select2({
    placeholder: "Select an option",
    width: '16vw'
}).on('change.select2', function() {
        var dropdown = $(this).siblings('span.select2-container');
        if (dropdown.height() > 100) {
            dropdown.css('max-height', '100px');
            dropdown.css('overflow-y', 'auto');
        }
})

var selected_ass_area = selected_options['ass_area'] == '' ? 'ALL' : selected_options['ass_area'];
var selected_cs = selected_options['cs'] == '' ? 'ALL' : selected_options['cs'];

$('#pom-<?= $id ?>-selection').trigger('click');
$('#ass-area-<?= $id ?>-selection').trigger('click');
$('#cs-<?= $id ?>-selection').trigger('click');
$('#pom-<?= $id ?>').val('ALL').trigger('change');
$('#ass-area-<?= $id ?>').val(selected_ass_area).trigger('change');
$('#cs-<?= $id ?>').val(selected_cs).trigger('change');

</script>
