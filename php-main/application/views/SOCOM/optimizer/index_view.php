<?php

    $this->load->view('templates/essential_javascripts');

    $js_files = array();
    $CI = &get_instance();
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
    $js_files['select2'] = ["select2.full.js", 'global'];
    $js_files['highstock'] = ["highstock.js", 'global'];
    $js_files['heatmap'] = ["heatmap.js", 'global'];
    $js_files['exporting'] = ["exporting.js", 'global'];
    $js_files['no_data'] = ["no-data-to-display.js", 'global'];
    $js_files['highcharts_more'] = ["highcharts-more.js", 'global'];
    $js_files['drilldown'] = ["highmaps-drilldown.js", 'global'];
    $js_files['accessibility'] = ["accessibility.js", 'global'];
    $js_files['ion_range'] = ["ion.rangeSlider.js", 'global'];
    $js_files['xlsx'] = ['xlsx.full.min.js', 'global'];
    $js_files['tabulator-tables'] = ['tabulator-tables.min.js','global'];
    $js_files['datatable'] = ["datatables_v1.min.js", 'global'];
    $js_files['highcharts_p1'] = ['actions/p1_highcharts.js','custom'];
    $js_files['gears_percentage'] = ['actions/SOCOM/optimizer/gears_percentage.js','custom'];
    $js_files['tranches_form'] = ['actions/SOCOM/optimizer/tranches_form.js','custom'];


    if(P1_FLAG){
        $js_files['Editor'] = ["Editor.js", 'global'];
    }

    $js_files['notification'] = ["actions/SOCOM/notification.js", 'custom'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];

    $js_files['weight_view'] = ['actions/SOCOM/weights/weight_view.js','custom'];


    $js_files['optimizer'] = ['actions/SOCOM/optimizer/optimizer.js','custom'];
    $js_files['js.color.gradient'] = ['js.color.gradient.js', 'global'];
    $js_files['treemap'] = ['treemap.js', 'global'];
    $js_files['coa'] = ['actions/SOCOM/optimizer/coa.js','custom'];
    $js_files['coa_helper'] = ['actions/SOCOM/optimizer/coa_helper.js','custom'];

    $js_files['business_rules'] = ['actions/SOCOM/optimizer/business_rules.js','custom'];
    
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);

	$this->load->view('SOCOM/toast_notifications');
?>

<style>
	.active-cycle-position{
    padding-top: 2rem;
    padding-left: 1.0rem;
  	}

    .heading-line-position{
        padding-left: 1.0rem;
    }
    #heading-radio-optimizer{
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		height: 30px;
	}

    #issue-analysis-event-filter-dropdown .select2-container {
        width: 18vw !important;
    }

    #opt-form-wrapper {
        position: relative;
        z-index: 2;
    }
    #coa-chart-wrapper {
        position: relative;
        z-index: 1;
    }
    #coa-graph {
        height: 1000px;
        position: relative;
        z-index: 1;
    }
    @media (max-width: 920px) {
        .coa-flex-container {
            flex-direction: column !important;
        }
        #opt-form-wrapper,
        #coa-chart-wrapper {
            width: 100% !important;
        }
    }
</style>

<h4 class="font-weight-bold mb-2 active-cycle-position" cycleId=<?= !empty($get_active_cycle_with_criteria['CYCLE_ID']) ? $get_active_cycle_with_criteria['CYCLE_ID']: '' ?>>Active Cycle: <span id="active-cycle-name"><?= !empty($get_active_cycle_with_criteria['CYCLE_NAME']) ? $get_active_cycle_with_criteria['CYCLE_NAME'] : 'No Active Cycle' ?></span></h4>
<div id="heading-radio-optimizer" class="mb-2 heading-line-position">
    <p id="resource_coa_header" class="d-none">COA Optimization is Connected with Position Year FY<?php echo $subapp_pom_year ?> for Resource Constraining</p>
    <p id="issue_coa_header">COA Optimization is Connected with Position Year FY<?php echo $subapp_pom_year_issue ?> for Issue Optimization</p>
</div>
<div id="overlay-loader"></div>
<div id="loading-wrapper" class="map-loading-indicator d-none">
    <?php $this->load->view('SOCOM/loading_view'); ?>
</div>
<div class="d-flex flex-row w-100">
<?php $this->load->view('SOCOM/weights/inline_weights_view', ['default_criteria_description' => $default_criteria_description]); ?>
<?php $this->load->view('SOCOM/program/export_filter', ['page' => 'optimizer']); ?>
</div>
<div class="d-flex flex-row">
    <div class="d-flex flex-column ml-3 mb-3">
                <legend class="bx--label">Optimizer Program Filter</legend>
                <button id="option_filter" class="bx--btn bx--btn--primary bx--btn--sm disabled">Filter</button>
    </div>
    <div id="business_rules_div" class="d-flex flex-column ml-3 mb-3">
                <legend class="bx--label">Business Rules</legend>
                <button id="business_rules" class="bx--btn bx--btn--primary bx--btn--sm disabled">Business Rules</button>
                <?php $this->load->view('SOCOM/optimizer/business_rules_view'); ?>
    </div>
</div>

<div class="w-100 d-flex flex-row">
    <div class="w-75 px-2">
        <?php $this->load->view('SOCOM/optimizer/optimizer_table_view'); ?>

        <div class="d-flex flex-row ml-3 mb-5 w-100 coa-flex-container">
            <div class="d-flex flex-column w-50 ml-1 mt-3 mb-5" id="opt-form-wrapper">
                <div class="d-flex flex-row mb-2"><h4>Optimize</h4></div>
                <div id="show-weighted-score" class="d-none flex-row">
            <?php $this->load->view('components/radioButtonGroup', [
                'name' => 'weighted_score_based',
                'label' => 'Weighted Scores Based',
                'useTile' => false,
                'radioButtons' => [
                    'POM' => [
                        'id' => 'r-pom',
                        'value' => '3',
                        'key' => 3,
                        'checked' => false,
                        'label' => 'POM'
                    ],
                    'Guidance' => [
                        'id' => 'r-guidance',
                        'value' => '2',
                        'key' => 2,
                        'checked' => false,
                        'label' => 'Guidance'
                    ],
                    'Both' => [
                        'id' => 'r-both',
                        'value' => '1',
                        'key' => 1,
                        'checked' => true,
                        'label' => 'Both'
                    ]
                ]
            ]); ?>
        </div>
        <div id="show-storm-score" class="flex-row d-flex"><span class="bx--label">Will Utilize StoRM Score</span></div>
                <div id="iss-optimizer-options">
                    <?php $this->load->view('SOCOM/optimizer/optimizer_options_view'); ?>
                </div>
                <div id="rc-optimizer-options" class="d-none">
                    <?php $this->load->view('SOCOM/optimizer/optimizer_options_view_resource_constrained'); ?>
                </div>
                <div class="d-flex flex-row justify-content-between mt-3">
                    <?php $this->load->view('SOCOM/optimizer/coa_save_load_view'); ?>
                </div>
            </div> 
            <div class="w-50" id="coa-chart-wrapper">
                <?php $this->load->view('SOCOM/optimizer/coa_graph_view'); ?>
            </div>
        </div>
    </div>
    <div id="iss-opt-table-view" class="w-25 px-2">
        <div>
            <?php $this->load->view('SOCOM/optimizer/coa_table_view', ['n' => 1, 'year_list' => $subapp_pom_year_list]); ?>
        </div>
        <div id="otable-2" class="mt-3" style="display: none;">
            <?php $this->load->view('SOCOM/optimizer/coa_table_view', ['n' => 2, 'year_list' => $subapp_pom_year_list]); ?>
        </div>
        <div id="otable-3" class="mt-3" style="display: none;">
            <?php $this->load->view('SOCOM/optimizer/coa_table_view', ['n' => 3, 'year_list' => $subapp_pom_year_list]); ?>
        </div>
    </div>
    <div id="rc-opt-table-view" class="w-25 px-2 d-none">
        <div>
            <?php $this->load->view('SOCOM/optimizer/to_cut_view') ?>
            <?php $this->load->view('SOCOM/optimizer/coa_table_view_resource_constrained', ['n' => 1, 'year_list' => $subapp_pom_year_list]); ?>
        </div>
        <div id="otable-rc-2" class="mt-3" style="display: none;">
            <?php $this->load->view('SOCOM/optimizer/coa_table_view_resource_constrained', ['n' => 2, 'year_list' => $subapp_pom_year_list]); ?>
        </div>
        <div id="otable-rc-3" class="mt-3" style="display: none;">
            <?php $this->load->view('SOCOM/optimizer/coa_table_view_resource_constrained', ['n' => 3, 'year_list' => $subapp_pom_year_list]); ?>
        </div>
    </div>
</div>
<?php $this->load->view('SOCOM/optimizer/coa_manual_override_view'); ?>
<?php $this->load->view('SOCOM/optimizer/coa_detailed_summary_view'); ?>
<?php $this->load->view('SOCOM/optimizer/coa_program_breakdown_view'); ?>
<?php $this->load->view('SOCOM/optimizer/coa_proposed_changes_view'); ?>
<?php $this->load->view('SOCOM/optimizer/coa_event_details_view'); ?>

<div class="d-flex flex-row w-100">
    
</div>

<script>
    const handson_license = '<?= RHOMBUS_HANDSONTABLE_LICENSE ?>';
    var optionFlagMap = {};
    let selectWaitTimer;
    let waitTimeAfterSelecting = 1000;
    
    function handleOptionSelectChanged() {
        clearTimeout(selectWaitTimer);
        selectWaitTimer = setTimeout(function() {
            $('#option-list').DataTable().draw();
        }, waitTimeAfterSelecting);
    }

    const default_criteria = JSON.parse('<?= json_encode($default_criteria) ?>');
    let fy_list =  <?=$fy_list?>;
    function changeProgramDropdown(){
        $(`#optimizer-table`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get/scored');
        selected_POM_weight_table.ajax.reload(() => {
            selected_Guidance_weight_table.ajax.reload(() => {
                $(`#optimizer-table`).DataTable().ajax.reload();
            });
        });
    }

    $(document).ready(function () {
        const $radioButtons = $('input[name="use_iss_extract"]');
        const $checkbox = $('#extract-checkbox-dataset');
        $radioButtons.on('change', function () {
            const selectedValue = $(this).val();
            // Automatically tick the checkbox if "ISS Extract" is selected
            if (selectedValue === "false") {
                $checkbox.prop('checked', true);
            } else {
                $checkbox.prop('checked', false);
            }
        });
        $checkbox.on('change', function(){
            if($checkbox.is(':checked')){
                $radioButtons.filter('[value="false"]').prop('checked', true).trigger('change');
            }
            else{
                $radioButtons.filter('[value="true"]').prop('checked', true).trigger('change');
            }
        })
    });

    $(function() {
        $('#option_filter').on('click', function() {
            $('#filter_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
        });

    })

    $(function() {
        $('#business_rules').on('click', function() {
            $('#business_rules_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
        });
    })
</script>
<style>
    #option_filter{
        width: 150px;
    }
</style>
