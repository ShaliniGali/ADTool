<?php

  $this->load->view('templates/essential_javascripts');

  $is_guest = $this->rbac_users->get_role('auth_guest');

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
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<style>
    #program-description{
        text-align: center;
    }

    .filter-container .select2-selection__rendered  {
        margin: unset !important
    }

    #filter-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    #all-filters-button {
        display: block;
        margin: 1rem auto;
    }

    .irs--round .irs-grid-text {
        color: #525252;
    }
</style>
<?php $this->load->view('SOCOM/toast_notifications');?>
<div class="content-wrapper">
    <header>
        <?php $this->load->view('SOCOM/header_buttons_view',
            array('current_page'=>'Budget to Execution', 'page' => 'budget_to_execution', 'page_summary_path' => 'budget_to_execution')
        );?>
    </header>
    <div class="grey-background">
        <div class="d-flex flex-row align-items-center">
            <div id="filter-container" class="filter-container">
                <div id="cs-dropdown" class="d-flex flex-column mr-4 mt-2">
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
                        <option id="cap-sponsor-all" option="ALL">ALL</option>
                        <?php
                            $selected = count($capability_sponsor) === 1 ? 'selected' : '';
                        ?>
                        <?php foreach($capability_sponsor as $value): ?>
                            <option value="<?= $value['SPONSOR_CODE']?>"
                                <?= ($selected && $value === reset($capability_sponsor)) ? 'selected' : ''; ?>>
                                <?= $value['SPONSOR_TITLE']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="ass-area-dropdown" class="d-flex flex-column mr-4 mt-2" >
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
                        <option option="ALL">ALL</option>
                        <?php foreach($ass_area as $value): ?>
                            <option value="<?= $value['ASSESSMENT_AREA_CODE']?>"><?= $value['ASSESSMENT_AREA']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="execution-manager-dropdown" class="d-flex flex-column mr-4 mt-2" hidden>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">
                            Execution Manager
                            <div id="execution-manager-loading" class="mx-2" hidden>
                                <div style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => false]); ?></div>
                            </div>
                        </div>
                        <div>
                            <button id="execution-manager-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                            data-select-all="true" 
                            disabled
                            type="button" onclick="dropdown_selection('#execution-manager-<?= $id ?>')"> 
                                Select All 
                            </button>
                            </div>
                    </div>
                    <select
                        id="execution-manager-<?= $id ?>"
                        type="execution-manager"
                        combination-id=""
                        class="selection-dropdown wss-selections"
                        multiple="multiple"
                        onchange="dropdown_onchange(1, 'execution-manager')"
                        disabled
                        >
                        <option option="ALL">ALL</option>
                    </select>
                </div>

                <div id="program-dropdown" class="d-flex flex-column mr-4 mt-2" >
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">
                            Program Group
                            <div id="program-loading" class="mx-2" hidden>
                                <div style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => false]); ?></div>
                            </div>
                        </div>
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
                    <option option="ALL">ALL</option>
                    </select>
                </div>

                <div id="program-name-dropdown" class="d-flex flex-column mr-4 mt-2" hidden>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">
                            Program Name
                            <div id="program-name-loading" class="mx-2" hidden>
                                <div style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => false]); ?></div>
                            </div>
                        </div>
                        <div>
                            <button id="program-name-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                            data-select-all="true" disabled
                            type="button" onclick="dropdown_selection('#program-name-<?= $id ?>')"> 
                                Select All 
                            </button>
                        </div>
                    </div>
                    <select
                        id="program-name-<?= $id ?>"
                        type="program-name"
                        combination-id=""
                        class="selection-dropdown wss-selections"
                        multiple="multiple"
                        onchange="dropdown_onchange(1, 'program-name')"
                        disabled
                        >
                        <option option="ALL">ALL</option>
                    </select>
                </div>

                <div id="eoc-code-dropdown" class="d-flex flex-column mr-4 mt-2" hidden>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">
                            EOC Code
                            <div id="eoc-code-loading" class="mx-2" hidden>
                                <div style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => false]); ?></div>
                            </div>
                        </div>
                        <div>
                            <button id="eoc-code-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                            data-select-all="true" disabled
                            type="button" onclick="dropdown_selection('#eoc-code-<?= $id ?>')"> 
                                Select All 
                            </button>
                        </div>
                    </div>
                    <select
                        id="eoc-code-<?= $id ?>"
                        type="eoc-code"
                        combination-id=""
                        class="selection-dropdown wss-selections"
                        multiple="multiple"
                        onchange="dropdown_onchange(1, 'eoc-code')"
                        disabled
                        >
                        <option option="ALL">ALL</option>
                    </select>
                </div>

                <div id="resource_category_dropdown" class="d-flex flex-column mr-4 mt-2" >
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">
                            Resource Category
                            <div id="resource_category-loading" class="mx-2" hidden>
                                <div style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => false]); ?></div>
                            </div>
                        </div>
                        <div>
                            <button id="resource_category-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                            data-select-all="true" disabled
                            type="button" onclick="dropdown_selection('#resource_category-<?= $id ?>')"> 
                                Select All 
                            </button>
                            </div>
                    </div>
                    <select 
                        id="resource_category-<?= $id ?>" 
                        type="resource_category" 
                        combination-id="" 
                        class="selection-dropdown" 
                        multiple="multiple"
                        onchange="dropdown_onchange(1, 'resource_category')"
                        disabled
                    >
                        <option option="ALL">ALL</option>
                    </select>
                </div>

                <div id="osd-pe-dropdown" class="d-flex flex-column mr-4 mt-2" hidden>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">
                            OSD PE
                            <div id="osd-pe-loading" class="mx-2" hidden>
                                <div style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => false]); ?></div>
                            </div>
                        </div>
                        <div>
                            <button id="osd-pe-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                            data-select-all="true" disabled
                            type="button" onclick="dropdown_selection('#osd-pe-<?= $id ?>')"> 
                                Select All 
                            </button>
                        </div>
                    </div>
                    <select
                        id="osd-pe-<?= $id ?>"
                        type="osd-pe"
                        combination-id=""
                        class="selection-dropdown wss-selections"
                        multiple="multiple"
                        onchange="dropdown_onchange(1, 'osd-pe')"
                        disabled
                        >
                        <option option="ALL">ALL</option>
                    </select>
                </div>
            </div>
            <div class = "d-flex flex-column">
                <div class="d-flex flex-row align-self-end mb-2 mt-2" >
                    <button
                        id="budget-to-execution-filter"
                        class="bx--btn bx--btn--primary header-button bx--btn--field"
                        type="button"
                        onclick="dropdown_onchange(1, 'filter')"
                        disabled
                    >
                        Apply Filter
                        <div id="apply-filter-loading" class="mx-2" hidden>
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

                <div>
                    <button
                        id="budget-to-execution-filter-compare"
                        class="bx--btn bx--btn--primary header-button bx--btn--field"
                        type="button"
                        onclick="dropdown_onchange(1, 'compare')"
                        disabled
                    > Compare
                </div>
            </div>
            <div class="bx--form-item" id="budget-to-execution-common-range-toggle-container" hidden>
                <input class="bx--toggle-input" id="budget-to-execution-common-range-toggle" type="checkbox" onchange="common_range_toggle()">
                <label class="bx--toggle-input__label" for="budget-to-execution-common-range-toggle">
                    Make common y-axis range
                    <span class="bx--toggle__switch">
                    <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                    <span class="bx--toggle__text--on" aria-hidden="true">On</span>
                    </span>
                </label>
            </div>
        </div>

        <button 
            id="all-filters-button" 
            class="bx--btn bx--btn--primary bx--btn--field"
            onclick="toggle_all_filters(1)"
        >
            Show All Filters
        </button>

        <div class = "d-flex flex-row justify-content-center ">
            <div class= "d-flex flex-column w-100 justify-content-center m-1" id="chart-1-container">
                <div class="w-100 chart-container" id="chart-1">
                    <div class="d-flex w-100 justify-content-center">
                        <h2>Click Apply Filter to see data</h2>

                    </div>
                </div>
                <div id="slider-container-1" style=
                                    "display: none; width:50%;
                                    position:relative;
                                    left:25%">
                                        <input type="text" id="year-slider-1" name="year" />
                </div>
                        
            </div>
            <div class="flex-column w-50 d-none justify-content-center m-1" id= "chart-2-container" >
                <div class="w-100 chart-container" id="chart-2">
                    <div class="w-100justify-content-center">
                    </div>
                </div>
                <div id="slider-container-2" style=
                                    "display: none; width:50%;
                                     position:relative;
                                    left:25%">
                                        <input type="text" id="year-slider-2" name="year" />
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const base_url_js = <?php echo json_encode(base_url()); ?>;
    const page = <?= json_encode($page); ?>;
    const graphData = <?= json_encode($graphData); ?>;

    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '17vw'
    })
    .on('change.select2', function() {
            var dropdown = $(this).siblings('span.select2-container');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
    })

    if (<?= $is_guest ? 'true' : 'false' ?>) {
        document.getElementById("cs-<?= $id ?>-selection").disabled = true;
        document.getElementById("cap-sponsor-all").remove();
    }
</script>

<?php
    $js_files = array();
    $CI = &get_instance();
    $js_files['s_home'] = ['actions/SOCOM/budget_to_execution.js','custom'];
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

