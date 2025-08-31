"use strict"

let currentCOA = null;
let SCENARIO_STATE = {};
let editor_object = [];
let input_object = {};
let coa_values_object = {};
let overrided_budget_impact_history = {};
let override_session_data = {};
let eoc_codes = [];
let selected_program_codes = [];
let selected_program_ids = [];
const uneditable_column = ['', 'FYDP'];
let pieChart = 'var(--cds-pie-01)'
let detailed_summary = {
    'comparison': {},
    'summary': {}
}
let current_pom_sponsor_code = [];
let treemap_colors = {}
let currentSavedCOA = {}
var lastSelectedItemsMap = {
    'include-fy': ["ALL"],
    'include-sponsor': ["ALL"],
    'include-sponsor-1': ["ALL"],
    'include-sponsor-2': ["ALL"],
    'include-sponsor-3': ["ALL"],
    'exclude-fy': ["ALL"],
    'exclude-sponsor': ["ALL"],
    'jca-alignment-lvl1': ["ALL"],
    'jca-alignment-lvl2': ["ALL"],
    'jca-alignment-lvl3': ["ALL"],
    'capability-gaps-lvl1': ["ALL"],
    'capability-gaps-lvl2': ["ALL"],
    'kop-ksp-lvl1': ["ALL"],
    'kop-ksp-lvl2': ["ALL"],
    'issue-analysis': ["ALL"],
}
let detailed_summary_view = {}
let program_breakdown_table = {}
let issue_analysis_data = {
    event: {},
    program_eoc: {},
}

function setCurrentCOA(id) {
    currentCOA = id;
}
const selectionTextOptions = {
    'false': 'Select All',
    'true': 'Deselect All'
}
let currentEOCPID = null

const overlay_loading_html = `
<div class="bx--loading-overlay" style="z-index: 90000;">
<div data-loading class="bx--loading">
  <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
    <title>Loading</title>
    <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
  </svg>
</div>
</div>`;

const loading_icon = `
<div data-loading class="bx--loading">
    <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
        <title>Loading</title>
        <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
    </svg>
</div>`;

const weighted_score_options = {
    'both': {
        'option': 1,
        'storm_flag': false
    } ,
    'guidance': {
        'option': 2,
        'storm_flag': false
    } ,
    'storm': {
        'option': 3,
        'storm_flag': true
    } ,
    'pom': {
        'option': 3,
        'storm_flag': false
    } 
}

function getCurrentCOA() {
    return currentCOA;
}

function showCOAModal(){
    $('#coa_save_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
}

function closeCOAModal(){
    $('#coa_save_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
}

function showLoadCOAModal(){
    $('#coa_load_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
}

function closeLoadCOAModal(){
    $('#coa_load_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
}

function getCOAList(callback) {
    showNotification('COA List, choose up to 3 COA to view.', 'success');
    $("#coa-load-table").DataTable().ajax.reload(callback);
}

let global_titles = [];
function getUserSavedCOA() {
    let ids = [],
    rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'rc-' : '';

    $('#coa-load-table').DataTable().$('input[name="load_coa[]"]:checked').each(function(i, elem) { ids.push(elem.value); }) 
    $(`#otable-${rc}1`).hide();
    $(`#otable-${rc}2`).hide();
    $(`#otable-${rc}3`).hide();
    
    return $.post('/optimizer/get_coa_data', 
        {
            rhombus_token: rhombuscookie(),
            ids
        }, 
        function(data) {
            coa_values_object = {}, input_object = {}
            let chart, seriesData, input, title = [], ctable, saved_coa_ids = [];
            //$('#coa-graph').highcharts().destroy();
            // $('#coa-graph').empty().css('height', '150px');
            $('#save-coa').prop('disabled', true);
            $('#run-optimizer').prop('disabled', true);


            $('.coa-save').show();
            chart = undefined;
            
            let type_of_coa_list = [];
            let mergeCOAOnly = true;
            for (let i in data.data) {
                let program_group_map = data.data[i]['program_group_map'] ?? [];
                let oi = JSON.parse(data.data[i]['OPTIMIZER_INPUT']);
                input = (oi instanceof Array ? oi[0] : oi);
                title.push(data.data[i]['COA_TITLE']);
                saved_coa_ids.push(data.data[i]['SAVED_COA_ID']);
                ctable = parseInt(i)+1;
                enableCOATableInput(ctable);
                input_object[ctable] = input;
                let coa_values = JSON.parse(data.data[i]['COA_VALUES']);
                let type_of_coa = data.data[i]['TYPE_OF_COA'];
                type_of_coa_list.push(type_of_coa);
                coa_values_object[ctable] = coa_values;
                if (ctable > 1) {
                    $(`#otable-${rc}${ctable}`).show();
                    $(`#coa-table-${rc}${ctable}`).DataTable().draw();
                }
                $(`#coa-save-${rc}${ctable}`).html(data.data[i]['COA_TITLE']);
                let overrideModalButton = `
                    <button 
                        class="bx--btn bx--btn--secondary coa-override-modal-button w-50 mr-2"
                        data-modal-target="#coa-output"
                        onclick="show_coa_output(${ctable}, ${data.data[i]['SAVED_COA_ID']})"
                    >
                        Manual Override
                    </button>
                    <button 
                        class="bx--btn bx--btn--secondary coa-detailed-summary w-50"
                        data-modal-target="#coa-detailed-summary"
                        onclick="show_coa_detailed_summary(
                        ${ctable}, ${data.data[i]['SAVED_COA_ID']}, '${data.data[i]['COA_TITLE']}', '${type_of_coa}'
                        )"
                    >
                        Detailed Summary
                    </button>
                `
                $(`#coa-manual-override-modal-button-container-${rc}${ctable}`).html(overrideModalButton)


                // for tranche filter
                if (input != null) {
                    mergeCOAOnly = false;
                }

                if (input != null) {
                    seriesData = applyOutputs(JSON.parse(data.data[i]['COA_VALUES']), (parseInt(i)+1), input.option, input.storm_flag);
                }
                else {
                    let covertedData = covertOverrideTableSessionData(type_of_coa, data.data[i], data.FY);
                    coa_values_object[ctable]['selected_programs'] = covertedData['outputData']['selected_programs']
                    input = covertedData['input'];
                    input_object[ctable] = input;
                    seriesData = applyOutputs(covertedData['outputData'], (parseInt(i)+1), input.option, input.storm_flag, program_group_map);
                }
                

                allCoaSeriesData[i] = seriesData;
                if (typeof chart === 'undefined') {
                    chart = createCOAGraph(seriesData, data.data.length);
                } else {
                    let ddId= null
                    chart.coaIndex = chart.coaIndex+1
                     let previousSeries= chart.myCustomSeries
                    
                    seriesData.series.forEach((mainSeries) => {


                        let found = false;

                        // Loop through the current series in the chart
                        chart.series.forEach(existingSeries => {
                            // Check if the existingSeries first data point's name matches mainSeries's first data point's name
                            if (
                            existingSeries.data && 
                            existingSeries.data.length > 0 && 
                            existingSeries.name === mainSeries.name

                            ) {
                            found = true;
                            // If a data point already exists at chart.coaIndex, update it,
                                existingSeries.addPoint(
                                    {
                                        x: chart.coaIndex,  // Set the correct index
                                        y: mainSeries.data[0].y,  // The actual data value
                                        drilldown: mainSeries.data[0].drilldown,
                                        name: mainSeries.data[0].name,
                                        stack: mainSeries.data[0].stack
                                    }, 
                                        false
                                );
                            
                            }
                        });

                        // If no matching series was found, add the mainSeries to the chart.
                        if (!found) {
                            chart.addSeries(mainSeries, false);
                        }
                        
                        // Update the custom drilldown mapping with the new drilldown data.
                         ddId = mainSeries.data[0].drilldown;


                          if (ddId){

                            let ddSeries= seriesData.drilldownSeries.filter(s => s.id === ddId)
                            if(ddSeries) {
                                if(!chart.customDrilldownMapping[chart.coaIndex]){
                                    chart.customDrilldownMapping[chart.coaIndex]=[]
                                }
                                if(!chart.customDrilldownMapping[chart.coaIndex][ddId]){
                                    chart.customDrilldownMapping[chart.coaIndex][ddId]=[]
                                }
                                chart.customDrilldownMapping[chart.coaIndex][ddId].push( ddSeries)
                            }
                          }                    
                    });
                    chart.myCustomSeries = chart.series.map(s => s.options);
                    
                    chart.recalcYMax();
                    
                    chart.redraw();

                }

                $(`#coa-table-${rc}${ctable} .deltaOptimizer`).each((i, elem) => {
                    elem.value=input.budget[i];
                });

                disableCOATableInput(ctable);
                showHideToCutField(false);
            }

            // for tranche filter
            if ($('input[name="use_iss_extract"]:checked').val() !== 'true') {
                const budgetValuesByIndex = {};
                for (let i = 0; i < data.data.length; i++) {
                    const item = data.data[i];
                    const parsedCalcBudgetValues = JSON.parse(item['CALC_BUDGET_VALUES']);
                    budgetValuesByIndex[i] = parsedCalcBudgetValues;
                }

                let highestNumOfTranche = -1;

                Object.values(budgetValuesByIndex).forEach(budget => {
                    const tranche = budget?.tranche_assignment;
                    if (!tranche || typeof tranche !== 'object') return;

                    const keys = Object.keys(tranche)
                        .filter(k => !isNaN(k))
                        .map(k => parseInt(k, 10));

                    const maxKey = Math.max(...keys, -1);
                    if (maxKey > highestNumOfTranche) {
                        highestNumOfTranche = maxKey;
                    }
                });

                tranche_assignment = budgetValuesByIndex;
                // only display filter for single COA
                addCoaTrancheSelector(true, highestNumOfTranche+1, mergeCOAOnly);
            }

            currentSavedCOA = {
                'saved_coa_ids': saved_coa_ids,
                'titles': title,
                'type_of_coas': type_of_coa_list
            }

            global_titles = title;
            $(`#detailed-comparison-btn`).empty();

            // detailed comparison button
            let detailedComparisonButton = `<div id="detailed-comparison-btn" class="w-100 d-flex justify-content-center">
            <button 
                class="bx--btn bx--btn--secondary coa-detailed-summary"
                data-modal-target="#coa-detailed-summary"
                onclick="show_coa_detailed_comparison()"
            >
                Detailed Comparison
            </button></div>`;
            $(`#coa-graph`).after(detailedComparisonButton);

            $(`.deltaOptimizer`).trigger('change');
            chart.xAxis[0].setCategories(title);
            chart.reflow();
            chart.redraw();
            setCurrentCOA(null);
            $('#create-coa').prop('disabled', false);
            closeLoadCOAModal();
        },
        "json"
    ).fail(function(jqXHR) { ajaxFail(jqXHR, 'Unable to Load COA'); });
}

function covertOverrideTableSessionData(type_of_coa, data,years) {

    let input = weighted_score_options[data['COA_TYPE']];
    let override_table_session = JSON.parse(data['OVERRIDE_TABLE_SESSION']);
    let selected_programs = [];
    let remaining = {}
    let budget = [];
    for (let [key, value] of Object.entries(override_table_session['coa_output'])) {

        if (value['RESOURCE CATEGORY'] != 'Committed Grand Total $K') {
            selected_programs.push({
                'program_id': covertToProgramId(
                    type_of_coa,
                    {
                        'program_code': value['Program'] ?? '',
                        'cap_sponsor': value['CAP SPONSOR'] ?? '',
                        'pom_sponsor': value['POM SPONSOR'] ?? '',
                        'ass_area_code': value['ASSESSMENT AREA'] ?? '',
                        'execution_manager': value['EXECUTION MANAGER'] ?? '',
                        'resource_category': value['RESOURCE CATEGORY'] ?? '',
                        'eoc_code': value['EOC'] ?? '',
                        'osd_pe_code': value['OSD PE'] ?? '',
                        'event_name': value['Event Name'] ?? ''
                    }
                ),
                'total_storm_score': value['StoRM Score'],
                'weighted_pom_score': value['POM Score'],
                'weighted_guidance_score': value['Guidance Score']
            })
        }
    }

    override_table_session['budget_uncommitted'].forEach( v => {
        if ( v['TYPE'] === "Uncommitted $K") {
            years.forEach(year => {
                remaining[year] = v[year];
            })
        }
        if ( v['TYPE'] === "Proposed Budget $K") {
            years.forEach(year => {
                budget.push(v[year]);
            })
        }
    })

    input['budget'] = budget;

    return {
        'input': input,
        'outputData': {
            'remaining': remaining,
            'selected_programs': selected_programs
        }
    }
}

function saveCOA() {
    hideNotification();

    let name = $('#coa-name').val(),
        description = $('#coa-description').val(),
        id = getCurrentCOA();

    if (typeof name !== 'string' || name.trim().length === 0) {
        showNotification('Name must have a value', 'error');
        return false;
    }

    if (typeof description !== 'string' || description.trim().length >= 500) {
        showNotification('Description must be less than 500', 'error');
        return false;
    }

    if (typeof id !== 'string' || id.length === 0) {
        showNotification('Current COA is not able to be saved, please run the optimizer and try again', 'error');
        return false;
    }

    return $.post('/optimizer/save_coa', 
        {
            rhombus_token: rhombuscookie(),
            name: name.trim(), 
            description: description.trim(),
            id: id
        }, 
        function(data) {
            showNotification('COA Saved, view it in load COA modal.', 'success');
            setCurrentCOA(null);
            closeCOAModal();
        },
        "json"
    ).fail(function(jqXHR) { ajaxFail(jqXHR, 'Unable to save COA'); });
}

function showLoadCOA(csc) {
    csc.reset();
    
    getCOAList(function() {
        setNotificationName('coa-load');
        showLoadCOAModal();
    });
}

function showCOA() {
    setNotificationName('coa');
    resetForm();
    showCOAModal()
}

function onReadyCOA() {
    const $radioButtons = $('input[name="use_iss_extract"]');

    let csc = new checkedSavedCoa()
    let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'rc-' : '';

    $('#save-coa').on('click', showCOA);
    $('#load-coa').on('click', () => { 
        showLoadCOA(csc); 
    } );

    $("#coa-load-table").DataTable({
        columnDefs: [
            { 
                'targets': 0,
                'searchable': false,
                'orderable': false,
                'className': 'dt-body-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="load_coa[]" value="">';
            }
         },
            { targets: 1, data: "COA_TITLE", name: "COA_NAME", defaultContent: ''},
            { targets: 2, data: "COA_DESCRIPTION", name: "COA_DESCRIPTION", defaultContent: '' },
        ],
        initComplete: function() {
            $('#coa-load-table tbody').on('change', 'td input[name="load_coa[]"]', function() {
                
                if (this.checked === false) {
                    csc.remove();
                }

                if (csc.get() === 3) {
                    this.checked = false;
                    return false;
                }
                
                if (this.checked === true) {
                    csc.add()
                }
            });
        },
        ajax: {
            url: "/optimizer/get_coa",
            type: 'POST',
            data: {
                iss_extract: function() { return $radioButtons.filter(':checked').val() === "true"; },
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        rowCallback: function (row, data) {
            $('td:eq(0) input', row).val(data['SAVED_COA_ID']);

            return data;
        },
    });


    $(`#coa-table-rc-1, #coa-table-1`).DataTable({
        columnDefs: [{ width: '10%', targets: 0 }, { width: '50%', targets: 1 }, { width: '40%', targets: 2 }],
        order: [],
        orderable: false,
        ordering: false,
        searching: false,
        paging: false,
        "dom": "lfrti",
        length: 100,   // number of rows to display
        rowHeight: '75px',
        scrollX: true,
        lengthChange: false
    });

    $(`#coa-table-rc-2, #coa-table-2`).DataTable({
        columnDefs: [{ width: '10%', targets: 0 }, { width: '50%', targets: 1 }, { width: '40%', targets: 2 }],
        order: [],
        orderable: false,
        ordering: false,
        searching: false,
        paging: false,
        length: 100,   // number of rows to display
        rowHeight: '75px',
        scrollX: true,
        lengthChange: false
    });

    $(`#coa-table-rc-3, #coa-table-3`).DataTable({
        columnDefs: [{ width: '10%', targets: 0 }, { width: '50%', targets: 1 }, { width: '40%', targets: 2 }],
        order: [],
        orderable: false,
        ordering: false,
        searching: false,
        paging: false,
        bPaginate: false,
        length: 100,   // number of rows to display
        rowHeight: '75px',
        scrollX: true,
        lengthChange: false
    });
    $(".dataTables_wrapper").css("width","100%");

    // Reset lastSelectedItemsMap when closing the modal
    document.addEventListener('modal-hidden', function(evt) {
        lastSelectedItemsMap = {
            'include-fy': ["ALL"],
            'include-sponsor': ["ALL"],
            'include-sponsor-1': ["ALL"],
            'include-sponsor-2': ["ALL"],
            'include-sponsor-3': ["ALL"],
            'exclude-fy': ["ALL"],
            'exclude-sponsor': ["ALL"],
            'jca-alignment-lvl1': ["ALL"],
            'jca-alignment-lvl2': ["ALL"],
            'jca-alignment-lvl3': ["ALL"],
            'capability-gaps-lvl1': ["ALL"],
            'capability-gaps-lvl2': ["ALL"],
            'kop-ksp-lvl1': ["ALL"],
            'kop-ksp-lvl2': ["ALL"],
            'issue-analysis': ["ALL"],
        }
    });
}


function insertCoaTableRow(scenarioId, type_of_coa) {
    // Get match row ids
    let matchRowIds = getMatchRowIds(coa_output_override_table, type_of_coa);
    
    loadPageData(
        '#coa-table-insert-modal-container', 
        '/optimizer/scenario/' + scenarioId + '/simulation/table/insert',
        {
            rhombus_token: rhombuscookie(),
            score_option: getStormWeightedBased(),
            use_iss_extract: $('input[name="use_iss_extract"]:checked').val(),
            match_row_ids: JSON.stringify(matchRowIds)
        }
    );
}

// Function to get column index based on header text
function getMatchRowIds(table, type_of_coa) {

    let matchCols = [];
    if (type_of_coa === 'ISS_EXTRACT') {
        matchCols = ['EOC', 'POM SPONSOR',  'ASSESSMENT AREA', 'CAP SPONSOR', 'Event Name', 'OSD PE', 'RESOURCE CATEGORY'];
    }
    else {
        matchCols = ['EOC', 'POM SPONSOR',  'ASSESSMENT AREA', 'CAP SPONSOR', 'EXECUTION MANAGER', 'OSD PE','RESOURCE CATEGORY'];
    }

    let matchRowIdList = [];

    table.rows().every(function (rowIdx, tableLoop, rowLoop) {
        let rowData = this.data();
        let matchRowId = [];
        matchCols.forEach(col => {
            matchRowId.push(rowData[col]);
        })
        
        let matchRowIdString = matchRowId.join('_');
        if (!matchRowIdString.includes("Committed Grand Total")) {
            matchRowIdList.push(matchRowIdString);
        }
    });
    return matchRowIdList;
}

function updateInsertCoaTableRowDropdown(type, scenario_id, current_year, type_of_coa) {
    let input_object = get_input_object_insert_modal(type);
    resetInsertCoaTableRowForm(type, current_year);

    let matchRowIds = getMatchRowIds(coa_output_override_table, type_of_coa);
    $.post('/optimizer/scenario/' + scenario_id + '/simulation/table/insert/update', 
        {
            rhombus_token: rhombuscookie(),
            ...input_object,
            eoc_codes_filter: eoc_codes,
            match_row_ids: JSON.stringify(matchRowIds)

        }, 
        function(response) {
            // update weighted score
            if (response['data']['weighted_score'] != undefined) {
                if (('weighted_pom_score' in response['data']['weighted_score'])) {
                    $('#text-input-POM').val(parseInt(response['data']['weighted_score'][`weighted_pom_score`]))
                }
                if (('weighted_guidance_score' in response['data']['weighted_score'])) {
                    $('#text-input-GUIDANCE').val(parseInt(response['data']['weighted_score'][`weighted_guidance_score`]))
                }
                if (('total_storm_scores' in response['data']['weighted_score'])) {
                    $('#text-input-STORM').val(parseInt(response['data']['weighted_score'][`total_storm_scores`]))
                }
            }           

            let autofill = (response['data']['ID'] !== '') ? true : false;
            showHideInsertCoaTableRowDropdown(type, false, autofill);
            let hasMoreOptions = false;
            for (const [key, value] of Object.entries(response['data']['dropdown'])) {
                // make sure the onchange dropdown does not update
                if (key == 'POM_SPONSOR_CODE') {
                    current_pom_sponsor_code = value;
                    continue;
                } 

                if (type !== key) {
                    $(`#text-input-${key}`).empty();
                    if (!hasMoreOptions) {
                        if (value.length > 1) {
                            $(`#text-input-${key}`).append(new Option('', '', true, true)); 
                            hasMoreOptions = true;
                            $(`#text-input-${key}`).attr('disabled', false);
                        }
                        else {
                            $(`#text-input-${key}`).attr('disabled', true);
                        }
                        value.forEach( v => {
                            let newOption = new Option(v,v, false, false);
                            $(`#text-input-${key}`).append(newOption); 
                        })
                    }
                    else {
                        $(`#text-input-${key}`).attr('disabled', true);
                    }
                }  
            }

            if (autofill) {
                autofillFYField(scenario_id, response['data']['ID']);
            }
    });
}

function getStormWeightedBased() {
    let type = $('input[name="storm_weighted_based"]:checked').val();
    let option = '';
    switch(type) {
        case '1': {
            let weight_type = $('input[name="weighted_score_based"]:checked').val();
            switch(weight_type) {
                case '1': {
                    option = 'BOTH';
                    break;
                }
                case '2': {
                    option = 'GUIDANCE';
                    break;
                }
                case '3': {
                    option = 'POM';
                    break;
                }
            }
            break;
        }
        case '2': {
            option = 'STORM';
            break;
        }
        default:
            break;
    }
    return option;
}

function autofillFYField(scenarioId, programId) {
    let input_object = get_input_object_insert_modal();
    $.post('/optimizer/scenario/' + scenarioId + '/simulation/table/insert/get', 
    {
        rhombus_token: rhombuscookie(),
        program_id: programId,
        score_option: getStormWeightedBased(),
        ...input_object
    }, 
    function(response) {
        $('#insert-coa-row-btn').attr('disabled', false);  
        currentEOCPID = programId;
        originalProgramData[currentEOCPID] = {}
        for (const [key, value] of Object.entries(response['data'])) {
            $(`#text-input-${key}`).empty();
            $(`#text-input-${key}`).val(value);
            originalProgramData[currentEOCPID][`20${key.slice(2)}`] = value;
        }
    });
}

function get_input_object_insert_modal(type = '') {

    let input_object = {}

    // since we have a specific order for the dropdown, please aware the order of these conditions   
    if ($('#text-input-PROGRAM_CODE').length > 0 && $('#text-input-PROGRAM_CODE').val() != '') {
        input_object['program_code'] = $('#text-input-PROGRAM_CODE').val();
    }
    if ($('#text-input-EOC_CODE').length > 0 && $('#text-input-EOC_CODE').val() != '' && type != 'PROGRAM_CODE') {
        input_object['eoc_code'] = $('#text-input-EOC_CODE').val();
    }
    if ($('#text-input-CAPABILITY_SPONSOR_CODE').length > 0 && $('#text-input-CAPABILITY_SPONSOR_CODE').val() != '' && 
        type != 'PROGRAM_CODE' && type != 'EOC_CODE') {
        input_object['capability_sponsor_code'] = $('#text-input-CAPABILITY_SPONSOR_CODE').val();
    }
    if ($('#text-input-ASSESSMENT_AREA_CODE').length > 0 && $('#text-input-ASSESSMENT_AREA_CODE').val() != '' && 
        type != 'PROGRAM_CODE' && type != 'EOC_CODE' && type != 'CAPABILITY_SPONSOR_CODE') {
        input_object['ass_area_code'] = $('#text-input-ASSESSMENT_AREA_CODE').val();
    }
    if ($('#text-input-RESOURCE_CATEGORY_CODE').length > 0 && $('#text-input-RESOURCE_CATEGORY_CODE').val() != '' && 
            type != 'PROGRAM_CODE' && type != 'EOC_CODE' && type != 'CAPABILITY_SPONSOR_CODE' && type != 'ASSESSMENT_AREA_CODE'
            ) {
        input_object['resource_category_code'] = $('#text-input-RESOURCE_CATEGORY_CODE').val();
    }
    if ($('#text-input-EVENT_NAME').length > 0 && $('#text-input-EVENT_NAME').val() != ''  && 
        type != 'PROGRAM_CODE' && type != 'EOC_CODE' && type != 'CAPABILITY_SPONSOR_CODE' && type != 'ASSESSMENT_AREA_CODE'
        && type != 'RESOURCE_CATEGORY_CODE') {
        input_object['event_name'] = $('#text-input-EVENT_NAME').val();
    }
    if ($('#text-input-EXECUTION_MANAGER_CODE').length > 0 && $('#text-input-EXECUTION_MANAGER_CODE').val() != ''  && 
    type != 'PROGRAM_CODE' && type != 'EOC_CODE' && type != 'CAPABILITY_SPONSOR_CODE' && type != 'ASSESSMENT_AREA_CODE'
    && type != 'RESOURCE_CATEGORY_CODE') {
        input_object['execution_manager_code'] = $('#text-input-EXECUTION_MANAGER_CODE').val();
    }
    if ($('#text-input-OSD_PROGRAM_ELEMENT_CODE').length > 0 && $('#text-input-OSD_PROGRAM_ELEMENT_CODE').val() != '' && 
        type != 'PROGRAM_CODE' && type != 'EOC_CODE' && type != 'CAPABILITY_SPONSOR_CODE' && type != 'ASSESSMENT_AREA_CODE'
        && type != 'RESOURCE_CATEGORY_CODE' && type != 'EVENT_NAME' && type != 'EXECUTION_MANAGER_CODE') {
        input_object['osd_pe_code'] = $('#text-input-OSD_PROGRAM_ELEMENT_CODE').val();
    }

    return input_object;
}

function resetInsertCoaTableRowForm(type, year) {
    $('#insert-coa-row-btn').attr('disabled', true);
    $('[id^="text-input-FY"]').val(0);
    showHideInsertCoaTableRowDropdown(type, true, true);
}

function showHideInsertCoaTableRowDropdown(type, disable=true, changeAll = false) {
    switch(type) {
        case 'PROGRAM_CODE': 
            $('#text-input-EOC_CODE').attr("disabled", disable);
            if (changeAll) {
                $('#text-input-POM_SPONSOR_CODE').attr("disabled", disable);
                $('#text-input-CAPABILITY_SPONSOR_CODE').attr("disabled", disable);
                $('#text-input-RESOURCE_CATEGORY_CODE').attr("disabled", disable);
                $('#text-input-ASSESSMENT_AREA_CODE').attr("disabled", disable);
                $('#text-input-OSD_PROGRAM_ELEMENT_CODE').attr("disabled", disable);
                $('#text-input-EVENT_NAME').attr("disabled", disable);
            }
            break;
        case 'EOC_CODE':
            $('#text-input-POM_SPONSOR_CODE').attr("disabled", disable);
            $('#text-input-CAPABILITY_SPONSOR_CODE').attr("disabled", disable);
            $('#text-input-RESOURCE_CATEGORY_CODE').attr("disabled", disable);
            $('#text-input-ASSESSMENT_AREA_CODE').attr("disabled", disable);
            $('#text-input-OSD_PROGRAM_ELEMENT_CODE').attr("disabled", disable);
            $('#text-input-EVENT_NAME').attr("disabled", disable);
            break;
        default:
            break;
    }
}

function validateYear(year, headers_map) {
    const onlyNumbers = /^-?[0-9]+$/;
    const input = $(`#text-input-${year}`).val()

    if(!onlyNumbers.test(input)){
        $(`#text-input-${year}`).addClass('bx--text-input--invalid')        
        $(`#invalid-icon-${year}`).removeClass('hidden')
        $(`#invalid-text-${year}`).removeClass('hidden')
        $(`#invalid-text-${year}`).text("Invalid input. Must only include numbers.")
        $('#insert-coa-row-btn').prop("disabled", true)
    } else if (input < 0) {
        $(`#text-input-${year}`).addClass('bx--text-input--invalid')        
        $(`#invalid-icon-${year}`).removeClass('hidden')
        $(`#invalid-text-${year}`).removeClass('hidden')
        $(`#invalid-text-${year}`).text("Invalid input. Must only include positive integers.")
        $('#insert-coa-row-btn').prop("disabled", true)
    }
    else {
        $(`#text-input-${year}`).removeClass('bx--text-input--invalid')
        $(`#invalid-icon-${year}`).addClass('hidden')
        $(`#invalid-text-${year}`).addClass('hidden')

        let hasEmptyField = false;
        for (const [key, value] of  Object.entries(headers_map)) {
            if ($(`#text-input-${value}`).val() == '') {
                hasEmptyField = true;
            }
        }

        if (!$('.bx--text-input').hasClass('bx--text-input--invalid') && !hasEmptyField) {
            $('#insert-coa-row-btn').prop("disabled", false)
        }
    }
}


class checkedSavedCoa {
    #chked = 0
    
    add() {
        this.#chked++;
    }
    
    remove() {
        this.#chked--;
    }

    get() {
        return this.#chked;
    }

    reset() {
        this.#chked = 0;
    }
}

function resetForm() {
    $('#coa-name').val('');
    $('#coa-description').val('');

    hideNotification();
}

function set_output_close_attr(is_confirm = false, scenario_id = null) {
    if (is_confirm) {
        $('#coa-output > div.bx--modal-container > div.bx--modal-header > button').attr('onclick', `openConfirmationModal("close", ${scenario_id})`);
        $('#coa-output .bx--modal-header > button').removeAttr('data-modal-close');
        $('#close-coa-results').attr('onclick', `openConfirmationModal("close", ${scenario_id})`);
        $('#close-coa-results').removeAttr('data-modal-close');
    }
    else {
        $('#coa-output .bx--modal-header > button').attr('data-modal-close', true);
        $('#coa-output .bx--modal-header > button').removeAttr('onclick');
        $('#close-coa-results').attr('data-modal-close', true);
        $('#close-coa-results').removeAttr('onclick');
    }
}

function show_coa_output (key, saved_coa_id) {
    let input = input_object[key]
    let selected_programs = coa_values_object?.[key]?.['selected_programs'] ?? [];
    set_output_close_attr(true, null);
    let selected_programs_ids_scores = filter_selected_programs(selected_programs);
    let ids = JSON.stringify(selected_programs_ids_scores['ids']);

    $('#coa-output-container').html(loading_icon);
    loadPageData('#coa-output-container', `/optimizer/scenario/${saved_coa_id}/simulation/table/output`, 
    {
        ids,
        budget: input['budget'],
        use_iss_extract: $('input[name="use_iss_extract"]:checked').val(),
        coa_table_id: key
    }, 
    function(){
        if(SCENARIO_STATE[saved_coa_id] =='CREATED'){
            $(`#coa-output-container`).addClass('d-none');
            $(`#coa-output-table`).removeClass('d-none');
            
        }
        if(SCENARIO_STATE[saved_coa_id] =='IN_PROGRESS'){
            toggleManualOverride(saved_coa_id);
            set_output_close_attr(true, saved_coa_id);
            show_original_outputs_toggle(false);
        }
        else if(SCENARIO_STATE[saved_coa_id] =='IN_REVIEW'){
            show_hide_override_table('show');
            show_override_metadata();
            disable_override();
            activate_review(saved_coa_id);
            disable_budget_table_edit_button()
            show_original_outputs_toggle();
        }
        else if(SCENARIO_STATE[saved_coa_id] =='APPROVED' || SCENARIO_STATE[saved_coa_id] =='DENIED'){
            show_hide_override_table('show');
            show_override_metadata();
            disable_override();
            disable_review();
            activate_approve_deny(SCENARIO_STATE[saved_coa_id].toLowerCase(), '', false);
            disable_budget_table_edit_button()
            show_original_outputs_toggle();
        }

        onReadyGears(saved_coa_id);
    });

}

function initEditorDataTable(
    id,
    editor_columns,
    indexOfOverrideYear,
    year_array,
    scenario_id,
    user_id
) {
    setUpEditor(id, editor_columns);
    editor_table.on('preSubmit', function (e, data, action) {
        let editrow = Object.keys(data.data)[0];
        let editColumn = Object.keys(data.data[editrow])[0];
        const newCellValue = data.data[editrow][editColumn];
        if (!validateEditCell(editColumn, newCellValue)) {
            const invalidMessage = {
                year: "Must only include positive integers."

            }
            const invalidText = !isNaN(parseInt(editColumn)) ? invalidMessage['year'] : invalidMessage[editColumn];
            const invalidInput = `<div class="invalid-text">${invalidText}</div>`
            $(`#DTE_Field_${editColumn}`).addClass('bx--text-input--invalid');
            if (!$(`.DTE_Field_Name_${editColumn}`).find('.invalid-text').length) {               
                $(`.DTE_Field_Name_${editColumn}`).append(invalidInput);
            } else {
                !$(`.DTE_Field_Name_${editColumn} .invalid-text`).text(invalidText)
            }
            return false;
        }
        
        if (typeof(parseInt(editColumn)) === 'number' && !isNaN(data.data[editrow][editColumn])) {
            let oldValue = parseInt(coa_output_override_table.row(`#${editrow}`).data()[editColumn] ?? 0);
            let newValue = parseInt(data.data[editrow][editColumn] ?? 0);
            let currentGrandTotal = parseInt(coa_output_override_table.row(`#${editrow}`).data()['FYDP']);
            // data.data[editrow]['FYDP'] = currentGrandTotal - oldValue + newValue;

            let updatedRow = coa_output_override_table.row(`#${editrow}`).data();
            let newFYDP = 0;
            year_array.forEach(year => {
                let yearVal = parseInt(data.data[editrow][year] ?? updatedRow[year] ?? 0);
                newFYDP += isNaN(yearVal) ? 0 : yearVal;
            });
            data.data[editrow]['FYDP'] = newFYDP;
            data.data[editrow][editColumn] = parseInt(data.data[editrow][editColumn]);
        }
    })
    .on('setData', function (e, json, data, action) {
        let index = coa_output_override_table.cell( editedCell ).index();
        let editedHeader = coa_output_override_table.column(index.column).header().textContent.trim();

        let newCellData = {}
        newCellData[editedHeader] = data[editedHeader];
        let newCellValue = data[editedHeader];
        let oldCellValue = coa_output_override_table.cell(index.row, index.column).data() ?? 0;
        let rowNode = coa_output_override_table.row(index.row).node();
        let rowId = rowNode?.id;

        updateOverridedBudgetImpactHistory(
            scenario_id,
            rowId,
            user_id,
            newCellData
        );

        //update table value
        if ( (index.column >= indexOfOverrideYear) && (index.column < (indexOfOverrideYear + year_array.length))) {
            if (Number.isFinite(oldCellValue) === false) {
                oldCellValue = 0;
            }
            updateGrandTotal(coa_output_override_table, index.row, 'edit', index.column, editedHeader, parseInt(oldCellValue), parseInt(newCellValue));
        }
    }).on('postEdit' , function ( e, json, data ) {
        $(".invalid-text").remove();
        const tooltipInfo = getTooltipInfo();
        // updateExportData();
        
        addClassToEditedCell($(editedCell), tooltipInfo)
        
        let index = coa_output_override_table.cell( editedCell ).index();
        addClassToEditedCell(
            $(coa_output_override_table.cell(index.row, indexOfOverrideYear + year_array.length).node()), 
            tooltipInfo
        );
    });
}

function isWithinBudgetFunc(
    table,
    editHeader,
    newGrandTotalValue,
    newCellValue
) {
    let columnIndex = getColumnIndexByName(table, editHeader);

    let budgetCellValue = getCellValueByColumnName(table, 0, columnIndex);
    let newUncommittedCellValue = budgetCellValue - newCellValue;
    table.cell(1, columnIndex).data(newUncommittedCellValue).draw(false);
    let grandTotalIndex = getColumnIndexByName(table, 'FYDP');
    let budgetGrandTotalValue = table.cell(0, grandTotalIndex).data();
    let newUncommittedGrandTotalCellValue =  budgetGrandTotalValue - newGrandTotalValue;
    table.cell(1,grandTotalIndex).data(newUncommittedGrandTotalCellValue).draw(false);
    let isCellWithinBudget = newUncommittedCellValue >= 0 ;
    addClassToEditedGrandCell(
        $(table.cell(1,columnIndex).node()),
        isCellWithinBudget,
        'uncommitted-cell'
    )
    let isGrandWithinBudget = newUncommittedGrandTotalCellValue >= 0;
    addClassToEditedGrandCell(
        $(table.cell(1,grandTotalIndex).node()),
        isGrandWithinBudget,
        'uncommitted-cell'
    )
    return {
        isCellWithinBudget,
        isGrandWithinBudget
    };
}

function updateGrandTotal(
    tableObject, rowIndex, 
    action, cellColumnIndex=null,  editHeader = null,
    previousCellValue=null, editedCellValue=null
) {
    const indexOfResource = override_headers.findIndex(item => item.data.toUpperCase() === "RESOURCE CATEGORY");
    let grandTotalRowIndex = tableObject.rows().indexes().filter(function (value, index) {
        return tableObject.cell(value, indexOfResource).data() === 'Committed Grand Total $K';
    })[0];
    const tooltipInfo = getTooltipInfo();
    if (action !== 'edit') {
        let columnIndex;
        for(let i=0; i<yearIndex.length; i++){
            columnIndex = i + indexOfOverrideYear;
            let grandTotalCellValue = tableObject.cell(grandTotalRowIndex, columnIndex).data();
            let cellValue = tableObject.cell(rowIndex, columnIndex).data();
            let newCellValue = action === 'delete' ? 
                grandTotalCellValue - cellValue : grandTotalCellValue + cellValue;
            editHeader = tableObject.column(columnIndex).header().textContent.trim();;
            let yearGrandTotalColumnIndex = indexOfOverrideYear + year_array.length
            let totalGrandTotalValue = tableObject.cell(grandTotalRowIndex, yearGrandTotalColumnIndex).data();
            let newtotalGrandTotalCellValue =  totalGrandTotalValue - grandTotalCellValue + newCellValue;
            tableObject.cell(grandTotalRowIndex, columnIndex).data(newCellValue).draw(false);
            let isWithinBudget = isWithinBudgetFunc(
                budget_uncommitted_override_table,
                editHeader,
                newtotalGrandTotalCellValue,
                newCellValue
            );
            
            let isCellWithinBudget = isWithinBudget['isCellWithinBudget'];
            
            addClassToEditedGrandCell(
                $(tableObject.cell(grandTotalRowIndex,columnIndex).node()),
                isCellWithinBudget,
                'proposal-cell',
                tooltipInfo,
                true
            )

            addClassToEditedCell($(tableObject.cell(rowIndex, columnIndex).node()), tooltipInfo)
        }
    } else {
        let grandTotalCellValue = tableObject.cell(grandTotalRowIndex, cellColumnIndex).data();
        let newCellValue = grandTotalCellValue - previousCellValue + editedCellValue;
        tableObject.cell(grandTotalRowIndex, cellColumnIndex).data(newCellValue).draw(false);
        let yearGrandTotalColumnIndex = indexOfOverrideYear + year_array.length
        addClassToEditedCell($(tableObject.cell(rowIndex,yearGrandTotalColumnIndex).node()), tooltipInfo)
        if (cellColumnIndex != yearGrandTotalColumnIndex) {
            let yearGrandTotalValue = tableObject.cell(rowIndex, yearGrandTotalColumnIndex).data();
            let newGrandCellValue =  yearGrandTotalValue - previousCellValue + editedCellValue;
  
            let totalGrandTotalValue = tableObject.cell(grandTotalRowIndex, yearGrandTotalColumnIndex).data();
            let newtotalGrandTotalCellValue =  totalGrandTotalValue - yearGrandTotalValue + newGrandCellValue;
            tableObject.cell(grandTotalRowIndex,yearGrandTotalColumnIndex).data(newtotalGrandTotalCellValue).draw(false);
            let isWithinBudget = isWithinBudgetFunc(
                budget_uncommitted_override_table,
                editHeader,
                newtotalGrandTotalCellValue,
                newCellValue
            );
            
            let isCellWithinBudget = isWithinBudget['isCellWithinBudget'];
            let isGrandWithinBudget = isWithinBudget['isGrandWithinBudget'];
            addClassToEditedGrandCell(
                $(tableObject.cell(grandTotalRowIndex,cellColumnIndex).node()),
                isCellWithinBudget,
                'proposal-cell',
                tooltipInfo,
                true
            )
            addClassToEditedGrandCell(
                $(tableObject.cell(grandTotalRowIndex,yearGrandTotalColumnIndex).node()),
                isGrandWithinBudget,
                'proposal-cell',
                tooltipInfo,
                true
            )

        }
    }
    disableSaveSubmit()
}

function getCellValueByColumnName(table, rowIndex, columnIndex) {
    let cellValue = table.cell(rowIndex, columnIndex).data();
    return cellValue;
}

function getColumnIndexByName(table, columnName) {
    let columnIndex = -1;
    let columns = table.settings().init().columns;
    columnName = columnName.toString();
    if (columnName !== 'DT_RowId') {
        columnName = columnName.replace('_', ' '); 
    }
    for (let i = 0; i < columns.length; i++) {
        if (columns[i].data === columnName) {
            columnIndex = i;
            break;
        }
    }
    return columnIndex;
}

function validateEditCell(header, value) {
    const onlyNumbers = /^-?\d+$/;
    if (value !== ""  && !isNaN(parseInt(header)) && value >= 0) {
        return onlyNumbers.test(value);
    }
}

function setUpEditor(id, editor_columns) {
    if(P1_FLAG=='1'){
        editor_table = new Editor({ 
            fields: editor_columns,
            table: `#${id}`
        });
    }
    else{
        editor_table = new DataTable.Editor({
            fields: editor_columns,
            table: `#${id}`
        });
    }
}

function randomId(length = 6) {
    return Math.random().toString(36).substring(2, length+2);
};

function openConfirmationModal(action, scenario_id = null, coa_table_id = null) {
    let coa_modal = $('#coa-output');
    let confirm_modal = $('#manual-override-confirm-coa');
    let confirm_button = $('#manual-override-confirm-btn');
    let cancel_button = $('#manual-override-cancel-btn');
    let close_button = $('#manual-override-confirm-coa-close-btn');
    
    let isManualOverride = !(
        $('#manual-override').is(':visible') && 
        $('#manual-override').is(':checked') === false && action == 'close');
    if (isManualOverride) {
        confirm_modal.addClass('is-visible');
        confirm_button.off('click');
        cancel_button.off('click');
        close_button.off('click');
    }

    cancel_button.on('click', function() {
                
        $('#manual-override').off('click');
        $('#manual-override').trigger('click');
        $('#manual-override').on('click', function () {
            openConfirmationModal('toggle', scenario_id);
        })
        confirm_modal.removeClass('is-visible');

    })
    close_button.on('click', function() {
        $('#manual-override').off('click');
        $('#manual-override').trigger('click');
        $('#manual-override').on('click', function () {
            openConfirmationModal('toggle', scenario_id);
        })
        confirm_modal.removeClass('is-visible');

    })
    switch (action) {
        case 'x':
            // fall through to close as these are the same action
        case 'close':
            if (isManualOverride) {
                $('#manual-override-action').html('close your COA Results?')
                confirm_button.on('click', function() {
                    confirm_modal.removeClass('is-visible');
                    coa_modal.removeClass('is-visible');
                    overrided_budget_impact_history = {};
                    $('.bx--body--with-modal-open').removeClass('bx--body--with-modal-open')
                });
            }
            else {
                coa_modal.removeClass('is-visible');
                $('.bx--body--with-modal-open').removeClass('bx--body--with-modal-open')
            }
            //getUserSavedCOA();
            break;
        case 'save':
            $('#manual-override-action').html('save the provided manual COA override?')
            confirm_button.on('click', function() {
                save_coa_form(scenario_id, 'Saved Successfully');
                updateCOATableUncommittedK(coa_table_id);
                confirm_modal.removeClass('is-visible');
            });
            break;
        case 'submit':
            $('#manual-override-action').html('submit this override for approval? Edited values will be automatically saved with submit button.')
            confirm_button.on('click', function() {
                save_coa_form(scenario_id , '');
                submit_for_coa_review(scenario_id, 'Submitted for Review');
                confirm_modal.removeClass('is-visible');
            })  
            break;
        case 'toggle':
            $('#manual-override-action').html(
                'toggle manual override mode?<br><strong>This change will not be reversible.</strong>'
            )
            confirm_button.on('click', function() {
                toggleManualOverride(scenario_id);
                confirm_modal.removeClass('is-visible');
                set_output_close_attr(true, scenario_id);
                disableCOATableInput(coa_table_id);
            })
            break;
        case 'approve':
                $('#manual-override-action').html('approve this override?')
                confirm_button.on('click', function() {
                    approve_coa_review(scenario_id);
                    confirm_modal.removeClass('is-visible');
                })
            break;
        case 'deny':
            $('#manual-override-action').html('deny this override?')
            confirm_button.on('click', function() {
                deny_coa_review(scenario_id);
                confirm_modal.removeClass('is-visible');
            })
            break;
        default:
            $('#manual-override-action').html('do this?');
            confirm_button.on('click', function() {
                confirm_modal.removeClass('is-visible');
            });
            break;     
    }
}

function updateCOATableUncommittedK(coa_table_id) {
    let isRC = $('input[name="use_iss_extract"]:checked').val() !== 'true';
    let rc = isRC ? 'rc-' : '';
    let data = $('#budget_uncommitted_override_table').DataTable().row(1).data();
    const uncommittedK = Object.entries(data)
        .filter(([key]) => /^\d{4}$/.test(key) || key === "FYDP")  // Keep only 4-digit year keys
        .map(([, value]) => {
            return isRC ? -1 * value : value;    
        });

    $(`#coa-table-${rc}${coa_table_id} .remaining`).each((i, elem) => {
        $(elem).html(uncommittedK[i]);
    });
}

function enableCOATableInput(key) {
    let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'rc-' : '';
    $(`#coa-table-${rc}${key} .deltaOptimizer`).prop('disabled', false);
}

function disableCOATableInput(key) {
    let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'rc-' : '';
    $(`#coa-table-${rc}${key} .deltaOptimizer`).prop('disabled', true);
}

function showHideToCutField(isShow) {
    let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? true : false;
    if (rc) {
        $('#to_cut-container').attr('hidden',!isShow);
    }
}
function showHideTrancheFilter(isShow) {
    //let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? true : false;
    if (isShow) {
        $('#coa-tranche-container').removeClass('d-none').addClass('d-flex');
    }
    else {
        $('#coa-tranche-container').addClass('d-none').removeClass('d-flex');
    }
}

function toggleManualOverride(scenario_id) {
    show_save_submit_review_button();
    disable_manual_override_toggle();
    show_override_metadata();
    if($('#manual-override').is(':checked')) {
        activate_override()
        change_scenario_status(scenario_id, 'IN_PROGRESS')
        SCENARIO_STATE[scenario_id] = 'IN_PROGRESS'
        $(`#coa-override-output-container`).removeClass('d-none');
        $(`#coa-output-table-container`).addClass('d-none'); 
        $(`#budget_uncommitted_override_table`).removeClass('d-none');
        $(`#budget_uncommitted_table`).addClass('d-none');
    } else {
        $("#override-accordion-wrapper").addClass("hidden");
        $(`#override-table`).addClass('d-none');
    }
}

function change_scenario_status(scenario_id, status_value){
    $.post(`/optimizer/scenario/${scenario_id}/change_scenario_status`,
        {
            'rhombus_token': rhombuscookie(),
            'status_value': status_value,
        }, 
        function(data){
            console.log('successful');
        }
    )
}

function save_coa_form(scenario_id, message) {
    manual_override_save(scenario_id);
    save_override_form(scenario_id);
    $(`#state-session-notification
    > .bx--inline-notification__details > .bx--inline-notification__text-wrapper
    > .bx--inline-notification__subtitle`).html(message)
    $('#state-session-notification').removeClass('d-none');
    setTimeout(() => {
        $('#state-session-notification').addClass('d-none');
    }, 3000);
}

function manual_override_save(scenario_id){
    let override_table = get_override_table();
    let override_table_metadata = get_override_table_metadata(scenario_id);

    let eoc_col =$('#coa-insert-row-btn').attr('data-eoc')
    let eocs =  coa_output_override_table.column(eoc_col).data().toArray().filter(item => item !== '');

    let eoc_codes = [...new Set(eocs)];
    $.post(`/optimizer/scenario/${scenario_id}/manual_override_save`,
    {
        'rhombus_token': rhombuscookie(),
        'override_table': override_table,
        'override_table_metadata': override_table_metadata,
        program_codes: selected_program_codes,
        eoc_codes
    }, function(data){
        display_output_banner();
    
    });
}

function save_override_form(scenario_id){
    let override_form = get_override_form();
    $.post(`/optimizer/scenario/${scenario_id}/save_override_form`,
    {
        'rhombus_token': rhombuscookie(),
        'override_form': override_form,
    }, function(data){
        console.log('successful');
    });
}



function get_override_table_metadata(scenario_id){
    return JSON.stringify(overrided_budget_impact_history[scenario_id]);
}

function activate_override(){
    $("#override-accordion-wrapper").removeClass("hidden");
    $(`#original-output-wrapper`).removeClass('d-none');
    $(`#coa-output-override-table`).removeClass('d-none');
    $("#coa-output-override-table_length" ).append(
        `<div id="editor-note" class="editor-note"> <div class="coa-banner green-background"> *Editing the table does not save the values. Use Save button to finalize the changes. </div></div>`
    )
    SCENARIO_STATE[scenario_id] = 'IN_PROGRESS'
}

function disable_override(){
    $(`#justification-text-input`).prop('disabled',true)
    $(`#save-override-button`).addClass('d-none')
    $(`#submit-coa`).addClass('d-none')
    $(`#override-table`).addClass('d-none')
    $(`#manual-override-wrapper`).addClass('d-none')
    $(`#editor-note`).addClass('d-none')
}

function activate_review(scenario_id){
    $(`#approve-coa`).removeClass('d-none')
    $(`#deny-coa`).removeClass('d-none')
    SCENARIO_STATE[scenario_id] = 'IN_REVIEW'
}

function disable_review(){
    $(`#approve-coa`).addClass('d-none')
    $(`#deny-coa`).addClass('d-none')
}

function activate_approve_deny(type, message, showNotification = true){
    $(`#${type}-banner`).removeClass('d-none')
    if (showNotification) {
        $(`#state-session-notification
        > .bx--inline-notification__details > .bx--inline-notification__text-wrapper
        > .bx--inline-notification__subtitle`).html(message)
        $('#state-session-notification').removeClass('d-none');
        setTimeout(() => {
            $('#state-session-notification').addClass('d-none');
        }, 3000);
    }
}

function get_override_form(){
    let justification = $(`#justification-text-input`).val();
    
    let obj_form = {
        'justification': justification
        
    };
    return JSON.stringify(obj_form);
}

function get_override_table(){
    let table_data =JSON.parse(JSON.stringify(coa_output_override_table.rows().data().toArray()));
    table_data.forEach(function(v){ delete v[''] });
    return JSON.stringify({
        'coa_output' : table_data,
        'budget_uncommitted': budget_uncommitted_override_table.data().toArray()
    });
}

function submit_for_coa_review(scenario_id, message){
    show_hide_override_table('show')
    change_scenario_status(scenario_id, 'IN_REVIEW')
    disable_override()
    activate_review(scenario_id)
    disable_budget_table_edit_button()
    toggle_original_outputs();
    $(`#state-session-notification
    > .bx--inline-notification__details > .bx--inline-notification__text-wrapper
    > .bx--inline-notification__subtitle`).html(message)
    $('#state-session-notification').removeClass('d-none');
    setTimeout(() => {
        $('#state-session-notification').addClass('d-none');
    }, 3000);
}

function show_hide_override_table(action) {
    if (action == 'show') {
        $(`#coa-override-output-container`).removeClass('d-none');
        $(`#coa-output-table-container`).addClass('d-none');

        $(`#budget_uncommitted_override_table`).removeClass('d-none');
        $(`#budget_uncommitted_table`).addClass('d-none');
    } else {
        $(`#coa-override-output-container`).addClass('d-none');
        $(`#coa-output-table-container`).removeClass('d-none');

        $(`#budget_uncommitted_override_table`).addClass('d-none');
        $(`#budget_uncommitted_table`).removeClass('d-none');
    }
}

function approve_coa_review(scenario_id){
    change_scenario_status(scenario_id, 'APPROVED')
    disable_review()
    activate_approve_deny('approved', 'Approved');
    toggle_original_outputs();
    SCENARIO_STATE[scenario_id] = 'APPROVED'
}

function deny_coa_review(scenario_id){
    change_scenario_status(scenario_id, 'DENIED')
    disable_review()
    activate_approve_deny('denied', 'Denied');
    toggle_original_outputs();
    SCENARIO_STATE[scenario_id] = 'DENIED'
}

function disable_budget_table_edit_button(){
    $(`#coa-override-output-container .coa-insert-row-btn`).attr('disabled', true);
    disableDeleteButton();
}

function disableDeleteButton(id) {
    coa_output_override_table.rows().every(function () {
        let rowData = this.data();
        let rowButton = $(rowData[""]);
        if (rowButton.length > 0) {
            $(rowButton).attr('disabled', true);
            rowData[""] = rowButton[0].outerHTML;
            this.data(rowData);
        }
    });
    coa_output_override_table.draw();
}

function show_save_submit_review_button() {
    $('#save-override-button').removeClass('d-none');
    $('#submit-coa').removeClass('d-none');
}


function disable_manual_override_toggle() {
    $(`#manual-override`).attr('disabled', true);
    $(`#manual-override`).prop('checked', true);
}

function show_original_outputs_toggle(checked) {
    $('#original-output-wrapper').removeClass("d-none");
    $('#original-output-wrapper').prop('checked', checked);
}

function show_override_metadata() {
    $("#override-accordion-wrapper").removeClass("hidden");
    $('#override-accordion-wrapper button').trigger('click');
}

function display_output_banner(){
    if(SCENARIO_STATE[scenario_id] !='CREATED'){
        $.post('/optimizer/get_display_banner',
        {
            'rhombus_token': rhombuscookie(),
            'temp': 'temp'
        }, function(data){
            data = JSON.parse(data);
            $('#coa-output-banner').html(sanitizeHtml(data['text'],{allowedAttributes:false, allowedTags:false}))
            $('#coa-output-banner').removeClass('d-none');
        })
        
    }
    else{
        $('#coa-output-banner').addClass('d-none')
    }
}

function export_coa_results() {
    let tableToExport = [];
    const columns = output_table.settings().init().columns;
    const headers_map = [];
    const export_headers = [];
    for (let i = 0; i < columns.length; i++) {
        let column_name = columns[i].data;
        if (column_name !== 'DT_RowId' && column_name !== 'POM SPONSOR') {
            headers_map.push(column_name);
            let export_name = set_export_header_name(column_name)
            export_headers.push(export_name);
        }
    }

    if (!$('#coa-output-table-container').hasClass('d-none')) {
        tableToExport =  output_table.data().toArray();
    }
    else {
        tableToExport = coa_output_override_table.data().toArray();
    }

    let filteredData = tableToExport.map(item => 
        headers_map.reduce((acc, key) => {
            if (key in item) {
                if (
                    ['POM Score', 'Guidance Score'].indexOf(key) !== -1 &&
                    typeof item[key] === 'number'
                ) {
                    acc[key] = item[key].toFixed(2);
                } else if(item[key] == null && typeof parseInt(key) === 'number') {
                    acc[key] = 0;
                } 
                else {
                    acc[key] = item[key].toString();
                }
            }
            return acc;
        }, {})
    );

    filteredData = filteredData.filter(item => item["RESOURCE CATEGORY"] !== "Committed Grand Total $K");

    let file_name = `OptimizerExport_${getCurrentFormattedTime()}.xlsx` ;
    const worksheetData = [
        export_headers, ...filteredData.map(item => headers_map.map(header => {

            if (!is_numeric(item[header])){
                return item[header];
            }
            else {
                if (has_floating_number(item[header])) {
                    return parseFloat(item[header]);
                }
                else {
                    return parseInt(item[header]);
                }
            }
        }))
    ];

    export_csv(file_name, worksheetData);
}

function has_floating_number(text) {
    return /\d+\.\d+/.test(text);
}

function is_numeric(str) {
    if (typeof str != "string") 
        return false 
    return !isNaN(str) && !isNaN(parseFloat(str)) 
}


function set_export_header_name(column_name) {
    let export_header_obj = {
        'Program': 'PROGRAM_CODE', 
        'EOC': 'EOC_CODE',
        'POM SPONSOR': 'POM_SPONSOR_CODE',
        'CAP SPONSOR': 'CAPABILITY_SPONSOR_CODE',
        'Event Name': 'EVENT_NAME',
        'EXECUTION MANAGER': 'EXECUTION_MANAGER_CODE',
        'OSD PE': 'OSD_PROGRAM_ELEMENT_CODE',
        'ASSESSMENT AREA': 'ASSESSMENT_AREA_CODE',
        'RESOURCE CATEGORY': 'RESOURCE_CATEGORY_CODE',
        'StoRM Score': 'StoRM Score',
        'POM Score': 'POM Score',
        'Guidance Score': 'Guidance Score',
        'FYDP': 'FYDP'
    }
    let export_header_name = '';
    if (!isNaN(column_name))  {
        export_header_name = `FY${column_name.slice(-2)}` 
    } else {
        export_header_name = export_header_obj[column_name];
    }
    return export_header_name;
}
function getCurrentFormattedTime() {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day}-${hours}-${minutes}-${seconds}`;
}

function export_csv(fileName, worksheetData) {
    // Create a worksheet
    const ws = XLSX.utils.aoa_to_sheet(worksheetData);

    /* add to workbook */
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    /* generate an XLSX file */
    XLSX.writeFile(wb, fileName);
}

function closeNotification(id) {
    $('#'+id).addClass('d-none');
}

function toggleOriginalOutputTable() {
    if($(`#original-output`).is(':checked')) {
        show_hide_override_table('hide');
    }
    else {
        show_hide_override_table('show');
    }
}

function addClassToEditedGrandCell(cell, isWithinBudget, className, tooltipInfo={}, addTooltip = false) {
    let cellName = `${(isWithinBudget ? 'valid' : 'invalid')}-${className}`
    cell.removeClass();
    cell.addClass(cellName);
    if (addTooltip) {        
        cell.addClass(
            `bx--tooltip__trigger bx--tooltip--a11y bx--tooltip--${tooltipInfo['direction']} bx--tooltip--align-start`
        );
        let tooltipMessage = `Modified By: ${tooltipInfo['user']}<br>Last Modified:<br>${tooltipInfo['date']}`;
    
        if (cell.find('span.bx--assistive-text').length >= 1) {
            cell.find('span.bx--assistive-text').html(tooltipMessage)
        }
        else {
            cell.html(
                cell.html() +
                `<span class="bx--assistive-text">${tooltipMessage}</span>`
            )
        }
    }
}

function filter_selected_programs(selected_programs) {
    let ids = [];
    let scores = {};
    for (let i = 0; i < selected_programs.length; i++) {
        let program = selected_programs[i];
        let program_id = program['program_id'];
        ids.push(program_id)
        if (!scores[program_id]) {
            scores[program_id] = {}
        }
        scores[program_id] = {
            'storm_score': program['total_storm_score'],
            'weighted_guidance_score': program['weighted_guidance_score'],
            'weighted_pom_score': program['weighted_pom_score']
        }
    }
    return {
        ids,
        scores
    };
}

function toggle_original_outputs(toggle=false) {
    $(`#original-output`).prop( "checked", toggle);
}

function disableSaveSubmit() {
    let new_year_array = [...year_array, 'FYDP'];
    for (let i = 0; i < new_year_array.length; i++) {
        let columnIndex = getColumnIndexByName(budget_uncommitted_override_table, new_year_array[i]);
        let cellValue = getCellValueByColumnName(budget_uncommitted_override_table, 1, columnIndex)
        if (cellValue < 0) {
            $('#save-override-button').attr('disabled', true)
            $('#submit-coa').attr('disabled', true)
            break;
        } else {
            $('#save-override-button').attr('disabled', false)
            $('#submit-coa').attr('disabled', false)
        }
    }
}


function loadTableMetadata (overrideTableMetadata, scenario_id) {
    if (overrided_budget_impact_history[scenario_id] == undefined) {
        overrided_budget_impact_history[scenario_id] = {}
        overrided_budget_impact_history[scenario_id]['coa_output'] = {}
        overrided_budget_impact_history[scenario_id]['budget_uncommitted'] = {}
    }
    if (overrideTableMetadata?.length || Object.keys(overrideTableMetadata)?.length) {
        overrided_budget_impact_history[scenario_id] = overrideTableMetadata;
    }
}

function updateOverridedBudgetImpactHistory(scenario_id, rowId, userId, data) {
    if (overrided_budget_impact_history[scenario_id] == undefined) {
        overrided_budget_impact_history[scenario_id] = {}
        overrided_budget_impact_history[scenario_id]['coa_output'] = {}
        overrided_budget_impact_history[scenario_id]['budget_uncommitted']['1'] = {}
    }
    if (overrided_budget_impact_history[scenario_id]['coa_output'][rowId] == undefined) {
        overrided_budget_impact_history[scenario_id]['coa_output'][rowId] = {}
    }
    if (overrided_budget_impact_history[scenario_id]['budget_uncommitted']['1'] == undefined) {
        overrided_budget_impact_history[scenario_id]['budget_uncommitted']['1'] = {}
    }
    let editInfo =  {
        'user_id': userId,
        'timestamp' : Date.now()
    }
    // add column to the map
    for (const key in data) {
        overrided_budget_impact_history[scenario_id]['coa_output'][rowId][key] = editInfo;
        overrided_budget_impact_history[scenario_id]['budget_uncommitted']['1'][key] = editInfo;
    }
    
}


function dropdown_all_view(type, id) {
    const dropdown_id = `${type}-${id}`;
    const dropdown = $(`#${dropdown_id}`);
    let allElement = $(`ul#select2-${dropdown_id}-container > li[title="ALL"]`);
    if (allElement.length) {
        allElement = allElement[0].outerHTML;
    }
    
    let selected_values = dropdown.val();
    let isSelectAll = !(selected_values.length === 0);
    const selectionButton = `#${dropdown_id}-selection`;
  
    const stringifyIsSelectAll = isSelectAll.toString();   

    const changeSelectAll = $(selectionButton).attr('data-select-all') !== selectionTextOptions[stringifyIsSelectAll];
    if (changeSelectAll) {        
        $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
        $(selectionButton).html(selectionTextOptions[stringifyIsSelectAll]);
    }
    
    if(selectionHasChanged(type) && selected_values.length > 0){
        const allIndex = selected_values.indexOf("ALL");
        if (allIndex !== -1) {
            selected_values.splice(allIndex, 1);
        }
        $(`ul#select2-${dropdown_id}-container > li[title="ALL"]`).remove()
    }
    else if (selected_values.includes("ALL")){
        selected_values = ['ALL'];
        const otherSelected = $(`ul#select2-${dropdown_id}-container > li.select2-selection__choice`);
        otherSelected.remove()
        $(`ul#select2-${dropdown_id}-container`).prepend(allElement)
    }

    lastSelectedItemsMap[type] = selected_values;
    dropdown.val(selected_values)
}

function handleDisableDropdown(type, id, tab_type) {
    const dropdown_id = `${type}-${id}`;
    const dropdown = $(`#${dropdown_id}`);
    let selected_values = dropdown.val();

    const currentDropdownId = `${dropdown_id}-dropdown`;
    const currentDropdownEl = $(`#${currentDropdownId}`);
    if (selected_values.length > 0 && !selected_values.includes("ALL")) {
        // Enable next level filter
        const nextDropdown = currentDropdownEl.next();
        const nextDropdownSelect = nextDropdown.find('select');
        const nextDropdownButton = nextDropdown.find('button');

        nextDropdownSelect.prop('disabled', false);
        nextDropdownButton.prop('disabled', false);
    } else {
        // Disable all higher level filters
        const nextDropdown = currentDropdownEl.nextAll();

        nextDropdown.each(function() {
            const nextDropdownSelect = $(this).find('select');
            const nextDropdownButton = $(this).find('button');

            nextDropdownSelect.prop('disabled', true);
            nextDropdownButton.prop('disabled', true);

            // Default all higher level filters to 'ALL' when disabled
            nextDropdownSelect.val(['ALL']);

            // Update the lastSelectedItemsMap values for higher level filters to 'ALL' as well
            let match = type.match(/(\d+)$/); // Grab the level number
            if (match) {
                let level = parseInt(match[0]) + 1; // Increment the level number
                type = type.replace(/(\d+)$/, level); // Update the type with the new level (Ex: jca-alignment-lvl1 --> jca-alignment-lvl2)
                lastSelectedItemsMap[type] = ['ALL'];
            }
        });
    }

    if (tab_type === 'jca-alignment') {
        handleThirdLevelFilterDisable(tab_type, id);
    }
}

function handleThirdLevelFilterDisable(tab_type, id) {
    const secondLevelDropdownValues = $(`#${tab_type}-lvl2-${id}`).val();
    const thirdLevelChecked = $(`#${tab_type}-lvl3-details-${id}`).is(':checked');
    const thirdLevelDropdownButton = $(`#${tab_type}-lvl3-${id}-selection`);
    const thirdLevelDropdownSelect = $(`#${tab_type}-lvl3-${id}`);

    // Enable third level filter if second level filter is not disabled and "3rd Level Details" checkbox is checked.
    if (thirdLevelChecked && secondLevelDropdownValues.length > 0 && !secondLevelDropdownValues.includes("ALL")) {
        thirdLevelDropdownButton.prop('disabled', false);
        thirdLevelDropdownSelect.prop('disabled', false);
    } else {
        thirdLevelDropdownButton.prop('disabled', true);
        thirdLevelDropdownSelect.prop('disabled', true);

        if (!(thirdLevelDropdownSelect.val().includes('ALL'))) {
            thirdLevelDropdownSelect.val(['ALL']);
        }
    }
}

function get_detailed_summary_input_object(id, type, tab_type=null, table_id=null) {

    let input_object = {};
    let extra_id = id != table_id && table_id != null ? `${table_id}-` : '';

    switch(tab_type) {
        case 'eoc-code':
            if ($(`#${type}-fy-${id}`).val() != "" && $(`#${type}-fy-${id}`).val() != null) {
                input_object["fy"] = fetch_all_inputs(`#${type}-fy-${id}`)
            } 

            if ($(`#${type}-sponsor-${extra_id}${id}`).val() != "" && $(`#${type}-sponsor-${extra_id}${id}`).val() != null) {
                input_object["cap_sponsor"] = fetch_all_inputs(`#${type}-sponsor-${extra_id}${id}`)
            }
            break;
        case 'jca-alignment': {
            if ($(`#${tab_type}-lvl3-details-${id}`).val() != "" && $(`#${tab_type}-lvl3-details-${id}`).val() != null) {
                input_object["details_checkbox"] =  $(`#${tab_type}-lvl3-details-${id}`).is(':checked');
            }

            if ($(`#${tab_type}-lvl3-${id}`).val() != "" && $(`#${tab_type}-lvl3-${id}`).val() != null) {
                input_object["lvl_3"] =  fetch_all_inputs(`#${tab_type}-lvl3-${id}`)
            }
        }
        case 'kop-ksp':
        case 'capability-gaps': {
            if ($(`#${tab_type}-lvl1-${id}`).val() != "" && $(`#${tab_type}-lvl1-${id}`).val() != null) {
                input_object["lvl_1"] =  fetch_all_inputs(`#${tab_type}-lvl1-${id}`)
            }

            if ($(`#${tab_type}-lvl2-${id}`).val() != "" && $(`#${tab_type}-lvl2-${id}`).val() != null) {
                input_object["lvl_2"] =  fetch_all_inputs(`#${tab_type}-lvl2-${id}`)
            }
            break;
        }
    } 
    return input_object;
}

function fetch_all_inputs(id) {
    let select2val =  $(id).val();
    if(select2val.includes('ALL') && select2val.length > 0){
        return $(`${id} > option`).map(function(){
            if(this.value != 'ALL' && this.value != ''){
                return this.value;
            }
        }).get();
    }
    return select2val;
}


function selectionHasChanged(id) {
    const lastSelections = lastSelectedItemsMap[id];
    return lastSelections.includes("ALL")
}

function dropdown_onchange(id, filter_type, tab_type, view_type, saved_coa_id = null) {
    let input_object = {}
    let table_id = id;
    let type = `${tab_type}-${filter_type}`;

    switch(filter_type) {
        case 'include-fy':
        case 'include-sponsor':
            if (view_type == 'comparison' && saved_coa_id != null) {
                table_id = saved_coa_id
                saved_coa_id = id
                type = `${filter_type}-${table_id}`; // Ex: 'include-sponsor-1'
            }

            if (view_type == 'summary') {
                type = filter_type;
            }
        
            dropdown_all_view(type, id);
            input_object = get_detailed_summary_input_object(id, 'include', tab_type, table_id);
            input_object['program_ids'] = JSON.stringify(get_selected_program_ids(table_id));
            input_object['saved_coa_id'] = saved_coa_id;
            input_object['mode'] = 'included';
            update_detailed_summary_data(
                id,
                tab_type,
                {
                    chart_container: `coa-detailed-${view_type}-${tab_type}-included-chart-${id}`,
                    table_container: `coa-detailed-${view_type}-${tab_type}-included-table-${id}`
                },
                input_object,
                {
                    selection: 'included',
                },
                view_type
            )
            break;
        case 'exclude-fy':
        case 'exclude-sponsor':
            if (view_type == 'comparison' && saved_coa_id != null) {
                table_id = saved_coa_id
                saved_coa_id = id
            }        
            dropdown_all_view(filter_type, id);
            input_object = get_detailed_summary_input_object(id, 'exclude', tab_type);
            input_object['program_ids'] = JSON.stringify(get_unselected_program_ids(table_id));
            input_object['saved_coa_id'] = saved_coa_id;
            input_object['mode'] = 'excluded';
            update_detailed_summary_data(
                id,
                tab_type,
                {
                    chart_container: `coa-detailed-${view_type}-${tab_type}-excluded-chart-${id}`,
                    table_container:`coa-detailed-${view_type}-${tab_type}-excluded-table-${id}`
                },
                input_object,
                {
                    selection: 'excluded',
                },
                view_type
            )
            break;
        case 'lvl1':
        case 'lvl2':
        case 'lvl3':
            dropdown_all_view(type, id);
            handleDisableDropdown(type, id, tab_type);            
        case 'level-checkbox':

            let saved_coa_ids = [];
            if (view_type == 'comparison') {
                saved_coa_ids = currentSavedCOA['saved_coa_ids'];
                table_id = 0;
            }
            else {
                saved_coa_ids = [saved_coa_id];
                table_id = id;
            }
            (async function () {
                for (const coa_id of saved_coa_ids) {
                    const input_object = get_detailed_summary_input_object(table_id, 'include', tab_type);
                    input_object['saved_coa_id'] = coa_id;
                    input_object['table_id'] = view_type == 'comparison' ? coa_id : table_id;
                    input_object['treemap_colors'] = JSON.stringify(treemap_colors);
            
                    const container_selectors = view_type === 'comparison' ? {
                        included_covered_table_container: `coa-detailed-${view_type}-${tab_type}-included-covered-table-${coa_id}`,
                        included_noncovered_table_container: `coa-detailed-${view_type}-${tab_type}-included-noncovered-table-${coa_id}`,
                        included_chart_container: `coa-detailed-${view_type}-${tab_type}-included-chart-${coa_id}`
                    } : {
                        included_covered_table_container: `coa-detailed-${view_type}-${tab_type}-included-covered-table-${table_id}`,
                        included_noncovered_table_container: `coa-detailed-${view_type}-${tab_type}-included-noncovered-table-${table_id}`,
                        excluded_covered_table_container: `coa-detailed-${view_type}-${tab_type}-excluded-covered-table-${table_id}`,
                        excluded_noncovered_table_container: `coa-detailed-${view_type}-${tab_type}-excluded-noncovered-table-${table_id}`,
                        included_chart_container: `coa-detailed-${view_type}-${tab_type}-included-chart-${table_id}`,
                        excluded_chart_container: `coa-detailed-${view_type}-${tab_type}-excluded-chart-${table_id}`
                    };
            
                    await update_detailed_summary_data(
                        table_id,
                        tab_type,
                        container_selectors,
                        input_object,
                        {},
                        view_type
                    );
                }
            })();

            if (tab_type === 'jca-alignment') {
                handleThirdLevelFilterDisable(tab_type, id);
            }

            break;

        default:
            break;
    }
}

async function update_detailed_summary_data(id, tab_type, containers, input_object, additional_input, view_type) {
    // show loading screen
    $('#overlay-loader').html(overlay_loading_html);

    await $.post(`/optimizer/get_detailed_summary_data/${tab_type}/update`,
    {
        'rhombus_token': rhombuscookie(),
        ...input_object
    }, 
    function(data){
        switch(tab_type) {
            case 'eoc-code': {
                let chart_container = containers['chart_container'];
                let table_container = containers['table_container'];
                let selection = additional_input['selection'];

                //clear table and graph
                $(`#${chart_container}`).empty();

                let detailed_summary_table = {}
                if (view_type == 'comparison') {
                    detailed_summary_table = detailed_summary_view[0][id][tab_type]['table'];
                    if(detailed_summary_table != undefined){
                        detailed_summary_table.destroy();
                    }
                    detailed_summary_view[0][id][tab_type]['graph'] = initDetailedSummaryChart(chart_container, data['data']['graph']);
                    detailed_summary_view[0][id][tab_type]['table'] = 
                        initDetailedSummaryDataTable(table_container, data['data']['table'], data['data']['headers'], tab_type);
                } else {
                    detailed_summary_table = detailed_summary_view[id][tab_type][selection]['table'];
                    if(detailed_summary_table != undefined){
                        detailed_summary_table.destroy();
                    }
                    detailed_summary_view[id][tab_type][selection]['graph'] = initDetailedSummaryChart(chart_container, data['data']['graph']);
                    detailed_summary_view[id][tab_type][selection]['table'] = 
                        initDetailedSummaryDataTable(table_container, data['data']['table'], data['data']['headers'], tab_type);
                }
                break;
            }
            case 'capability-gaps':
            case 'kop-ksp':
            case 'jca-alignment': {
                data = data['data']

                if (data['treemap_colors'] != undefined) {
                    treemap_colors = data['treemap_colors'];
                }
                let included_covered_table_container = containers['included_covered_table_container'];
                let included_noncovered_table_container = containers['included_noncovered_table_container'];
                let included_chart_container = containers['included_chart_container'];
                let detailed_summary_included_covered_table = {}
                let detailed_summary_included_noncovered_table = {}
                if (view_type == 'summary') {
                    detailed_summary_included_covered_table = detailed_summary_view[id][tab_type]['included']['table']['covered'];
                    detailed_summary_included_noncovered_table = detailed_summary_view[id][tab_type]['included']['table']['noncovered'];
                }
                else {
                    detailed_summary_included_covered_table = detailed_summary_view[0][input_object['saved_coa_id']][tab_type]['table']['covered'];
                    detailed_summary_included_noncovered_table = detailed_summary_view[0][input_object['saved_coa_id']][tab_type]['table']['noncovered'];
                }

                //clear table and graph
                $(`#${included_chart_container}`).empty();

                if(detailed_summary_included_covered_table != undefined){
                    detailed_summary_included_covered_table.destroy();
                }
                if(detailed_summary_included_noncovered_table != undefined){
                    detailed_summary_included_noncovered_table.destroy();
                }
                let inc_fydp = get_fydp_table(data['table']['included']['covered']);
                    
                $(`#${tab_type}-inc-fydp-${id}`).html(Highcharts.numberFormat(inc_fydp, 0, '.', ','));

                let exc_fydp = get_fydp_table(data['table']['excluded']['covered']);

                let fydp_title = inc_fydp;
                let data_type = 'included';
                if (currentSavedCOA['type_of_coas'][id] === 'RC_T') {
                    fydp_title = exc_fydp;
                    data_type = 'excluded';
                }

                if (view_type !== 'summary') {
                    $(`#${tab_type}-inc-fydp-${input_object['saved_coa_id']}`).html(Highcharts.numberFormat(fydp_title, 0, '.', ','));
                }

                if (view_type == 'summary') {
                    detailed_summary_view[id][tab_type]['included']['table']['covered'] = initDetailedSummaryDataTable(included_covered_table_container, data['table']['included']['covered'], data['table']['headers']['covered']);
                    detailed_summary_view[id][tab_type]['included']['table']['noncovered'] = initDetailedSummaryDataTable(included_noncovered_table_container,data['table']['included']['noncovered'], data['table']['headers']['noncovered']);
                    detailed_summary_view[id][tab_type]['included']['graph'] = initDetailedSummaryTreemap(included_chart_container, data['graph']['included'], inc_fydp);
                    detailed_summary_view[id][tab_type]['included']['table']['breakdown'] = data['program_breakdown']['included'];
                    detailed_summary_view[id][tab_type]['excluded']['table']['breakdown'] = data['program_breakdown']['excluded'];
                }
                else {
                    detailed_summary_view[0][input_object['saved_coa_id']][tab_type]['table']['covered'] = initDetailedSummaryDataTable(included_covered_table_container, data['table'][data_type]['covered'], data['table']['headers']['covered']);
                    detailed_summary_view[0][input_object['saved_coa_id']][tab_type]['table']['noncovered'] = initDetailedSummaryDataTable(included_noncovered_table_container,data['table'][data_type]['noncovered'], data['table']['headers']['noncovered']);
                    detailed_summary_view[0][input_object['saved_coa_id']][tab_type]['graph'] = initDetailedSummaryTreemap(included_chart_container, data['graph'][data_type], inc_fydp);
                    detailed_summary_view[0][input_object['saved_coa_id']][tab_type]['table']['breakdown'] = data['program_breakdown'][data_type];
                }

                if (view_type == 'summary') {
                    let excluded_covered_table_container = containers['excluded_covered_table_container'];
                    let excluded_noncovered_table_container = containers['excluded_noncovered_table_container'];
                    let excluded_chart_container = containers['excluded_chart_container'];
                    let detailed_summary_excluded_covered_table = detailed_summary_view[id][tab_type]['excluded']['table']['covered'];
                    let detailed_summary_excluded_noncovered_table = detailed_summary_view[id][tab_type]['excluded']['table']['noncovered'];


                    $(`#${excluded_chart_container}`).empty();

                    if(detailed_summary_excluded_covered_table != undefined){
                        detailed_summary_excluded_covered_table.destroy();
                    }
                    if(detailed_summary_excluded_noncovered_table != undefined){
                        detailed_summary_excluded_noncovered_table.destroy();
                    }

                    detailed_summary_view[id][tab_type]['excluded']['table']['noncovered'] = initDetailedSummaryDataTable(excluded_noncovered_table_container,data['table']['excluded']['noncovered'], data['table']['headers']['noncovered']);
                    detailed_summary_view[id][tab_type]['excluded']['table']['covered'] = initDetailedSummaryDataTable(excluded_covered_table_container,data['table']['excluded']['covered'], data['table']['headers']['covered']);
                    detailed_summary_view[id][tab_type]['excluded']['graph'] = initDetailedSummaryTreemap(excluded_chart_container,  data['graph']['excluded'], exc_fydp);
                }
                if ($(`#${tab_type}-lvl1-${id}`).val().length > 1) {
                    $(`#${tab_type}-lvl2-${id}`).val(['ALL']);

                    if (tab_type == 'jca-alignment') {
                        $(`#${tab_type}-lvl3-${id}`).val(['ALL']);
                    }
                }


                $(`#${tab_type}-lvl2-${id} option`).each(function() {
                    if ($(this).val() !== 'ALL' && !$(this).is(':selected')) {
                        $(this).remove();
                    }
                });

                data['filter']['lvl_2'].forEach( v => {
                    if ($(`#${tab_type}-lvl2-${id}`).find(`option[value='${v}']`).text() == '') {
                        var newOption = new Option(v, v, false, false);
                        $(`#${tab_type}-lvl2-${id}`).append(newOption);
                    }
                })

                if (tab_type == 'jca-alignment') {
                    if ($(`#${tab_type}-lvl2-${id}`).val().length > 1) {
                        $(`#${tab_type}-lvl3-${id}`).val(['ALL']);
                    }


                    $(`#${tab_type}-lvl3-${id} option`).each(function() {
                        if ($(this).val() !== 'ALL' && !$(this).is(':selected')) {
                            $(this).remove();
                        }
                    });

                    data['filter']['lvl_3'].forEach( v => {
                        if ($(`#${tab_type}-lvl3-${id}`).find(`option[value='${v}']`).text() == '') {
                            var newOption = new Option(v, v, false, false);
                            $(`#${tab_type}-lvl3-${id}`).append(newOption);
                        }
                    })
                }
                break;                
            }
            default:
                break;
        }

        $('#overlay-loader').html('');
    });
}

function dropdown_selection(target) {
    const dropdown = $(target);
    const selectionButton = `${target}-selection`;
    const isSelectAll = $(selectionButton).attr('data-select-all') === 'true';
    const stringifyIsSelectAll = isSelectAll.toString();
    $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
    $(selectionButton).html(selectionTextOptions[stringifyIsSelectAll]);

    if (!isSelectAll) {
        dropdown.val(null).trigger('change');
    } else {
        dropdown.val('ALL').trigger('change');
        if (dropdown.height() > 100) {
            dropdown.css('max-height', '100px');
            dropdown.css('overflow-y', 'auto');
        }
    }
}

function show_coa_detailed_summary(table_id, saved_coa_id, title, type_of_coa) {
    $('#coa-detailed-summary-heading').text('COA Detailed Summary');
    loadPageData(
        '#coa-detailed-summary-container', 
        `/optimizer/scenario/${saved_coa_id}/table/${table_id}/get_detailed_summary`, 
    {
        'rhombus_token': rhombuscookie(),
        title: title,
        type_of_coa: type_of_coa
    }, 
    function(){
        //default as eoc-code
        detailed_summary['summary'] = {};
        $('.bx--tabs__nav-item[data-type="eoc-code"]').trigger('click');
        
        // Disable lvl2 and lvl3 filters by default after loading detailed summary
        $("#coa-detailed-summary select[type='lvl2']").each(function() {
            $(this).prop('disabled', true);  
            $(this).prev().find('button').prop('disabled', true);
        });
        $("#coa-detailed-summary select[type='lvl3']").each(function() {
            $(this).prop('disabled', true);  
            $(this).prev().find('button').prop('disabled', true);
        });
    });

}

function show_coa_detailed_comparison() {
    $('#coa-detailed-summary-heading').text('COA Detailed Comparison');
    loadPageData(
        '#coa-detailed-summary-container', 
        `/optimizer/get_detailed_comparison`, 
    {
        'rhombus_token': rhombuscookie(),
        titles: currentSavedCOA['titles'],
        saved_coa_ids: currentSavedCOA['saved_coa_ids'],
        type_of_coas: currentSavedCOA['type_of_coas']
    }, 
    function(){
        //default as eoc-code
        detailed_summary['comparison'] = {};
        $('.bx--tabs__nav-item[data-type="eoc-code"]').trigger('click');

        // Disable lvl2 and lvl3 filters by default after loading detailed comparison
        $("#coa-detailed-summary select[type='lvl2']").each(function() {
            $(this).prop('disabled', true);  
            $(this).prev().find('button').prop('disabled', true);
        });
        $("#coa-detailed-summary select[type='lvl3']").each(function() {
            $(this).prop('disabled', true);  
            $(this).prev().find('button').prop('disabled', true);
        });
    });
}

function show_coa_detailed_comparison_content(type) {
    if (detailed_summary['comparison'] === undefined || detailed_summary['comparison'][type] === undefined) {
        let selected_program_ids = [];
        for (const [key, value] of Object.entries(coa_values_object)) {
            selected_program_ids.push(
                value['selected_programs'].map(item => item.program_id)
            )
        }

        $('#overlay-loader').html(overlay_loading_html);

        $.post(`/optimizer/get_detailed_comparison_data/${type}`,
        {
            'rhombus_token': rhombuscookie(),
            'selected_program_ids' : JSON.stringify(selected_program_ids),
            'saved_coa_ids': currentSavedCOA['saved_coa_ids'],
            'titles': currentSavedCOA['titles'],
        }, function(response){

            let data = response['data'];

            if (data['treemap_colors'] != undefined) {
                treemap_colors = data['treemap_colors']
            }

            if (detailed_summary['comparison'] == undefined) {
                detailed_summary['comparison'] = {}
            }
            detailed_summary['comparison'][type] = true;
            if (detailed_summary_view[0] == undefined) {
                detailed_summary_view[0] = {}
            }
            switch(type) {
                case 'eoc-code':
                    currentSavedCOA['saved_coa_ids'].forEach((saved_coa_id, index) => {

                        if (detailed_summary_view[0][saved_coa_id] == undefined) {
                            detailed_summary_view[0][saved_coa_id] = {
                                [type]: {}
                            }
                        }
                        else {
                            detailed_summary_view[0][saved_coa_id][type] = {}
                        }
                        detailed_summary_view[0][saved_coa_id][type]['table'] = initDetailedSummaryDataTable(
                            `coa-detailed-comparison-${type}-included-table-${saved_coa_id}`,
                            data[index]['table']['included']['data'], data[index]['table']['headers'], type
                        );
                        detailed_summary_view[0][saved_coa_id][type]['graph'] = initDetailedSummaryChart(
                            `coa-detailed-comparison-${type}-included-chart-${saved_coa_id}`,
                            data[index]['graph']['included']
                        );

                        //update filter
                        data[index]['filter']['included']['fy'].forEach( v => {
                            var newOption = new Option('FY' + v.toString().slice(-2), v, false, false);
                            $(`#include-fy-${saved_coa_id}`).append(newOption);
                        })
                        data[index]['filter']['included']['cap_sponsor'].forEach( v => {
                            var newOption = new Option(v, v, false, false);
                            $(`#include-sponsor-${index + 1}-${saved_coa_id}`).append(newOption);
                        })
                    })
                    break;
                case 'jca-alignment':
                case 'capability-gaps':
                case 'kop-ksp':
                    currentSavedCOA['saved_coa_ids'].forEach((saved_coa_id, index) => {

                        if (detailed_summary_view[0][saved_coa_id] == undefined) {
                            detailed_summary_view[0][saved_coa_id] = {
                                [type]: {
                                    'table': {},
                                    'graph': null
                                }
                            }
                        }
                        else {
                            detailed_summary_view[0][saved_coa_id][type] = {
                                'table': {},
                                'graph': null
                            }
                        }
                        let inc_fydp = get_fydp_table(data[saved_coa_id]['table']['included']['covered'])
                        $(`#${type}-inc-fydp-${saved_coa_id}`).html(Highcharts.numberFormat(inc_fydp, 0, '.', ','));

                        detailed_summary_view[0][saved_coa_id][type]['table']['covered'] = 
                            initDetailedSummaryDataTable(
                                `coa-detailed-comparison-${type}-included-covered-table-${saved_coa_id}`, 
                                data[saved_coa_id]['table']['included']['covered'], 
                                data[saved_coa_id]['table']['headers']['covered']
                            );
                        detailed_summary_view[0][saved_coa_id][type]['table']['noncovered'] = 
                            initDetailedSummaryDataTable(
                                `coa-detailed-comparison-${type}-included-noncovered-table-${saved_coa_id}`,
                                data[saved_coa_id]['table']['included']['noncovered'],
                                data[saved_coa_id]['table']['headers']['noncovered']
                            );
                        detailed_summary_view[0][saved_coa_id][type]['graph'] = 
                            initDetailedSummaryTreemap(
                                `coa-detailed-comparison-${type}-included-chart-${saved_coa_id}`,
                                data[saved_coa_id]['graph']['included'],
                                inc_fydp
                            );
                        detailed_summary_view[0][saved_coa_id][type]['table']['breakdown'] = data[saved_coa_id]['table']['program_breakdown']
                    })

                    //update filter
                    data['filter']['lvl_1'].forEach( v => {
                        var newOption = new Option(v, v, false, false);
                        $(`#${type}-lvl1-0`).append(newOption);
                    })
                    break;
                case 'issue-analysis':
                    issue_analysis_data = data;

                    const eventHeaders = data.event.headers;
                    const eventData = data.event.data;

                    let eventColumnDefs = eventHeaders.map((header, index) => {
                        return {
                            targets: index,
                            ...header,
                        };
                    });

                    const programEocHeaders = data.program_eoc.headers;
                    const programEocData = data.program_eoc.data;

                    let programEocColumnDefs = programEocHeaders.map((header, index) => {
                        return {
                            targets: index,
                            ...header,
                        };
                    });

                    //update filter
                    $(`#issue-analysis-event-filter`).empty();
                    $(`#issue-analysis-event-filter`).append(new Option("Capability Sponsor Request", "Capability Sponsor Request", false, false));

                    currentSavedCOA['titles'].forEach((title, index) => {
                        var newOption = new Option(title + " Proposed", title + " Proposed", false, false);
                        $(`#issue-analysis-event-filter`).append(newOption);
                    });

                    $(`#issue-analysis-eoc-filter`).empty();
                    $(`#issue-analysis-eoc-filter`).append(new Option("Capability Sponsor Request", "Capability Sponsor Request", false, false));

                    currentSavedCOA['titles'].forEach((title, index) => {
                        var newOption = new Option(title + " Proposed", title + " Proposed", false, false);
                        $(`#issue-analysis-eoc-filter`).append(newOption);
                    });

                    initIssueAnalysisTable('coa-detailed-comparison-issue-analysis-event-table', Object.values(eventData), eventColumnDefs);
                    initIssueAnalysisTable('coa-detailed-comparison-issue-analysis-program-eoc-table', programEocData, programEocColumnDefs, {
                        lengthMenu: [
                            [5, 10, 25, 50, 100], // Available values in the dropdown
                            [5, 10, 25, 50, 100]  // The labels for those values
                        ]
                    });
                    break;
            }
            $('#overlay-loader').html('');
        })
    }
}

function get_selected_program_ids(table_id) {
    //get program ids
    let selected_programs = coa_values_object?.[table_id]?.['selected_programs'] ?? [];
    let selected_programs_ids_scores = filter_selected_programs(selected_programs);
    return selected_programs_ids_scores['ids'];
}

function get_unselected_program_ids(table_id) {
    //get program ids
    let selected_ids = get_selected_program_ids(table_id),
    must_exclude = [];
    for (let i in input_object) {
        if (input_object[i]['must_exclude']?.length > 0) {
            must_exclude.concat(input_object[i]['must_exclude']);
        }
    }
    return selected_program_ids.filter(x => !selected_ids.includes(x)).concat(must_exclude);
}

function get_coa_program_breakdown(table_id, type, version, option) {
    let breakdown = {}
    if (detailed_summary_view[table_id] == undefined) {
        breakdown =  detailed_summary_view[0][table_id][type]['table']['breakdown'];
    }
    else {
        breakdown = detailed_summary_view[table_id][type][option]['table']['breakdown'];
    }
    let title = breakdown[version]['title'];

    loadPageData(
        '#coa-program-breakdown-container', 
        `/optimizer/get_program_breakdown/${type}`, 
    {
        'rhombus_token': rhombuscookie(),
        'title': title
    }, function(){
        program_breakdown_table.clear();
        breakdown[version]['data'].forEach(v => {
            program_breakdown_table.row.add(v);
        })
        program_breakdown_table.draw();
    });

}

// Detailed Comparison --> Issue Analysis --> Event --> Click on Event Name
function get_event_details(event_name) {
    $('#coa-event-details-container #event-details-event-name').html(event_name);

    const columnDefs = issue_analysis_data?.event?.event_detail_header?.map((header, index) => {
        return {
            targets: index,
            ...header,
        };
    }) ?? [];

    const data = issue_analysis_data?.program_eoc?.data?.filter((item => {
        return item.EVENT_NAME === event_name;
    })) ?? [];
    
    initIssueAnalysisTable('coa-event-details-table', data, columnDefs);
}

// Detailed Summary --> Issue Analysis --> Event --> Proposed Changes (...)
function get_proposed_changes(event_name) {
    $('#coa-proposed-changes-container #proposed-changes-event-name').html(event_name);

    const button = $('button[data-event-name="' + event_name + '"]');

    const proposedChanges = JSON.parse(button.attr('data-proposed-changes'));

    let fiscalYears = proposedChanges?.fiscal_years ?? [];

    const includedData = proposedChanges?.include.map(item => {
        let data = {
            ...item,
            ...item.FISCAL_YEAR
        }

        fiscalYears.forEach(fy => {
            data[fy] = data[fy] ?? 0;
        });

        return data;
    });

    const excludedData = proposedChanges?.exclude.map(item => {
        let data = {
            ...item,
            ...item.FISCAL_YEAR
        }

        fiscalYears.forEach(fy => {
            data[fy] = data[fy] ?? 0;
        });

        return data;
    });

    let columnDefs = [
            {
                targets: 0,
                data: "PROGRAM_CODE",
                title: "Program",
            },
            {
                targets: 1,
                data: "EOC_CODE",
                title: "EOC",
            },
            {
                targets: 2,
                data: "CAPABILITY_SPONSOR_CODE",
                title: "Capability Sponsor",
            },
            {
                targets: 3,
                data: "ASSESSMENT_AREA_CODE",
                title: "Assessment Area",
            },
            {
                targets: 4,
                data: "RESOURCE_CATEGORY_CODE",
                title: "Resource Category",
            },
            {
                targets: 5,
                data: "OSD_PROGRAM_ELEMENT_CODE",
                title: "OSD PE Code",
            },
            ...fiscalYears.map((fiscalYear, index) => {
                return {
                    targets: 6 + index,
                    data: `${fiscalYear}`,
                    title: `FY${fiscalYear}`,
                };
            }),
            {
                targets: 6 + fiscalYears.length,
                data: "FYDP",
                title: "FYDP Delta",
            },
    ];
    
    initIssueAnalysisTable('coa-proposed-changes-included-table', includedData, columnDefs, { searching: false, lengthChange: false });
    initIssueAnalysisTable('coa-proposed-changes-excluded-table', excludedData, columnDefs, { searching: false, lengthChange: false });
}

function initIssueAnalysisTable(tableId, data, columnDefs = [], customConfig = {}) {
    const config = {
        searching: true,
        lengthChange: true,
        ordering: true,
        iDisplayLength: 5,
        lengthMenu: [
            [5, 10, 25, 50], // Available values in the dropdown
            [5, 10, 25, 50]  // The labels for those values
        ],
        data,
        columnDefs,
        ...customConfig,
    };

    const table = $(`#${tableId}`);

    if ($.fn.dataTable.isDataTable(table)) {
        return table.DataTable().clear().rows.add(data).draw();
    } else {
        return table.DataTable(config);
    }
}

function get_fydp_table(data) {
    let fydp = 0;
    for (let td in data) {
        if (typeof data[td]['RESOURCE_K'] !== 'undefined') {
            fydp += data[td]['RESOURCE_K'];
        } else if (typeof data[td]['DELTA_AMT'] !== 'undefined') {
            fydp += data[td]['DELTA_AMT'];
        }
    }

    return fydp
}

function show_coa_detailed_summary_content(scenario_id, table_id, type) {

    if (detailed_summary['summary'][table_id] === undefined || detailed_summary['summary'][table_id][type] === undefined) {
        
        //get program ids
        let selected_ids = get_selected_program_ids(table_id);
        let unselected_ids = get_unselected_program_ids(table_id);

        $('#overlay-loader').html(overlay_loading_html);

        $.post(`/optimizer/scenario/${scenario_id}/get_detailed_summary_data`,
        {
            'rhombus_token': rhombuscookie(),
            'type': type,
            selected_ids: JSON.stringify(selected_ids),
            unselected_ids: JSON.stringify(unselected_ids),
            table_id: table_id
        }, function(response){

            let data = response['data'];

            if (detailed_summary['summary'][table_id] == undefined) {
                detailed_summary['summary'][table_id] = {}
            }
            detailed_summary['summary'][table_id][type] = true;

            if (type !== 'issue_analysis') {
                if (detailed_summary_view[table_id] == undefined) {
                    detailed_summary_view[table_id] = {
                        [type]: {
                            'included': {},
                            'excluded' : {}
                        }
                    }
                }
                else {
                    detailed_summary_view[table_id][type] = {
                        'included': {},
                        'excluded' : {}
                    }
                }
            }

            switch(type) {
                case 'eoc-code':
                    detailed_summary_view[table_id][type]['included']['table'] = initDetailedSummaryDataTable(`coa-detailed-summary-${type}-included-table-${table_id}`,data['table']['included']['data'], data['table']['headers'], type);
                    detailed_summary_view[table_id][type]['excluded']['table'] = initDetailedSummaryDataTable(`coa-detailed-summary-${type}-excluded-table-${table_id}`,data['table']['excluded']['data'], data['table']['headers'], type);
                    detailed_summary_view[table_id][type]['included']['graph'] = initDetailedSummaryChart(`coa-detailed-summary-${type}-included-chart-${table_id}`, data['graph']['included']);
                    detailed_summary_view[table_id][type]['excluded']['graph'] = initDetailedSummaryChart(`coa-detailed-summary-${type}-excluded-chart-${table_id}`, data['graph']['excluded']);
                    //update filter
                    data['filter']['included']['fy'].forEach( v => {
                        var newOption = new Option('FY' + v.toString().slice(-2), v, false, false);
                        $(`#include-fy-${table_id}`).append(newOption);
                    })
                    data['filter']['included']['cap_sponsor'].forEach( v => {
                        var newOption = new Option(v, v, false, false);
                        $(`#include-sponsor-${table_id}`).append(newOption);
                    })
                    data['filter']['excluded']['fy'].forEach( v => {
                        var newOption = new Option('FY' +  v.toString().slice(-2), v, false, false);
                        $(`#exclude-fy-${table_id}`).append(newOption);
                    })
                    data['filter']['excluded']['cap_sponsor'].forEach( v => {
                        var newOption = new Option(v, v, false, false);
                        $(`#exclude-sponsor-${table_id}`).append(newOption);
                    })
                    break;
                case 'kop-ksp':
                case 'capability-gaps':
                case 'jca-alignment': {
                    let inc_fydp = get_fydp_table(data['table']['included']['covered'])
                    
                    $(`#${type}-inc-fydp-${table_id}`).html(Highcharts.numberFormat(inc_fydp, 0, '.', ','));

                    let exc_fydp = get_fydp_table(data['table']['excluded']['covered'])

                    $(`#${type}-exc-fydp-${table_id}`).html(Highcharts.numberFormat(exc_fydp, 0, '.', ','));
                    
                    detailed_summary_view[table_id][type]['included']['graph'] = initDetailedSummaryTreemap(`coa-detailed-summary-${type}-included-chart-${table_id}`, data['graph']['included'], inc_fydp);
                    detailed_summary_view[table_id][type]['excluded']['graph'] = initDetailedSummaryTreemap(`coa-detailed-summary-${type}-excluded-chart-${table_id}`,  data['graph']['excluded'], exc_fydp);
                    detailed_summary_view[table_id][type]['included']['table'] = {}
                    detailed_summary_view[table_id][type]['excluded']['table'] = {}
                    detailed_summary_view[table_id][type]['included']['table']['covered'] = initDetailedSummaryDataTable(`coa-detailed-summary-${type}-included-covered-table-${table_id}`,data['table']['included']['covered'], data['table']['headers']['covered']);
                    detailed_summary_view[table_id][type]['excluded']['table']['covered'] = initDetailedSummaryDataTable(`coa-detailed-summary-${type}-excluded-covered-table-${table_id}`,data['table']['excluded']['covered'], data['table']['headers']['covered']);
                    detailed_summary_view[table_id][type]['included']['table']['noncovered'] = initDetailedSummaryDataTable(`coa-detailed-summary-${type}-included-noncovered-table-${table_id}`,data['table']['included']['noncovered'], data['table']['headers']['noncovered']);
                    detailed_summary_view[table_id][type]['excluded']['table']['noncovered'] = initDetailedSummaryDataTable(`coa-detailed-summary-${type}-excluded-noncovered-table-${table_id}`,data['table']['excluded']['noncovered'], data['table']['headers']['noncovered']);
                    detailed_summary_view[table_id][type]['included']['table']['breakdown'] = data['program_breakdown']['included'];
                    detailed_summary_view[table_id][type]['excluded']['table']['breakdown'] = data['program_breakdown']['excluded'];
                    //update filter
                    data['filter']['lvl_1'].forEach( v => {
                        var newOption = new Option(v, v, false, false);
                        $(`#${type}-lvl1-${table_id}`).append(newOption);
                    })
                    break;
                }
                case 'issue-analysis': {
                    issue_analysis_data = data;

                    if (detailed_summary_view[table_id] == undefined) {
                        detailed_summary_view[table_id] = {}
                    }
                    detailed_summary_view[table_id]['issue-analysis'] = {
                        'fully_funded': {},
                        'partially_funded' : {},
                        'non_funded' : {}
                    }

                    // Event Table
                    detailed_summary_view[table_id][type]['fully_funded'] = initDetailedSummaryDataTable(`coa-detailed-summary-issue-analysis-fully-funded-table`,data['event']['fully_funded'], data['event']['headers']);
                    detailed_summary_view[table_id][type]['partially_funded'] = initDetailedSummaryDataTable(`coa-detailed-summary-issue-analysis-partially-funded-table`,data['event']['partially_funded'], data['event']['headers']);
                    detailed_summary_view[table_id][type]['non_funded'] = initDetailedSummaryDataTable(`coa-detailed-summary-issue-analysis-non-funded-table`,data['event']['non_funded'], data['event']['headers']);

                    // Program/EOC Table
                    const programEocColumnDef = data['program_eoc']['headers']['eoc_information']['summary'].map((header, index) => {
                        return {
                            targets: index,
                            ...header,
                        };
                    }) ?? [];

                    detailed_summary_view[table_id][type]['eoc_information'] = initIssueAnalysisTable(`coa-detailed-summary-issue-analysis-eoc-information-table`,data['program_eoc']['eoc_information'], programEocColumnDef, {
                        searching: true,
                        lengthChange: true,
                        ordering: true,
                        iDisplayLength: 5,
                        lengthMenu: [
                            [5, 10, 25, 50, 100], // Available values in the dropdown
                            [5, 10, 25, 50, 100]  // The labels for those values
                        ]
                    });
                }
            }
            $('#overlay-loader').html('');
        })
    }
}

function updateIssueAnalysisEventFilter(tableId) {
    let selectedDeltaLines;
    let colNum;
    if(tableId === '#coa-event-details-table'){
        dropdown_all_view('issue-analysis', 'event-filter');
        selectedDeltaLines = $("#issue-analysis-event-filter").val();
        colNum = 6;
    } else if(tableId === '#coa-detailed-comparison-issue-analysis-program-eoc-table'){
        dropdown_all_view('issue-analysis', 'eoc-filter');
        selectedDeltaLines = $("#issue-analysis-eoc-filter").val();
        colNum = 7;
    }

    let table = $(`${tableId}`).DataTable();
    if (selectedDeltaLines.length === 0 || selectedDeltaLines.includes("ALL")) {
        selectedDeltaLines = ["ALL"];
        table.column(colNum).search("").draw();
    } else {
        let escapedFilters = selectedDeltaLines.map(value => value.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'));
        let filters = escapedFilters.join('|');
        table.column(colNum).search(filters, true, false).draw();
    }
}

function initDetailedSummaryDataTable(id, tableData, tableHeaders, type='') {

    let tableParams = {
        order: [],
        iDisplayLength: 5,   // number of rows to display
        rowHeight: '75px',
        "dom": 'rtip',
        ordering: false,
        data:tableData,
        columns: tableHeaders
    }

    switch(type) {
        case 'eoc-code':
            tableParams = {
                ...tableParams,
                "dom": 'lfrtip',
                "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]]
            }
            break;
        default:
            break;
    }
    return $(`#${id}`).DataTable(tableParams);
}

function initDetailedSummaryTreemap(id, data, fydp) {
    return Highcharts.chart(id, {
        series: [{
            type: 'treemap',
            layoutAlgorithm: 'stripes',
            alternateStartingDirection: true,
            borderColor: '#fff',
            borderRadius: 6,
            borderWidth: 2,
            dataLabels: {
              style: {
                textOutline: 'none'
              }
            },
            levels: [{
                level: 1,
                layoutAlgorithm: 'sliceAndDice',
                dataLabels: {
                  enabled: true,
                  align: 'left',
                  verticalAlign: 'top',
                  style: {
                    fontSize: '15px',
                    fontWeight: 'bold'
                  }
                }
            }],
            data: data
        }],
        title: {
            text: data.length == 0 ? 'No data to display' : null
        },
        // subtitle: {
        //     text:
        //         'Source: <a href="https://snl.no/Norge" target="_blank">SNL</a>',
        //     align: 'left'
        // },
        tooltip: { 
            useHTML: true,
            formatter: function() {
                var pointName = this.point.name ? this.point.name : this.point.parent;
                var pointNamePrefix = this.point.prefix ? this.point.prefix : '';
                var pointText = this.point.text;
                var pointPrograms = this.point.programs;
                console.log(this.point.name, this.point.value, fydp)
                var pointValue = Highcharts.numberFormat(this.point.value, 0, '.', ',');
                return `<strong>${pointNamePrefix} ` + pointName + '</strong>: ' + pointText + 
                       '<br><strong>Resource</strong>: ' + pointValue + ' $K' +
                       '<br><strong>% of FYDP</strong>: ' + (this.point.value/fydp*100).toFixed(2) + ' %' +
                       '<br><strong>Programs</strong>: ' + pointPrograms;
            },
        },
        exporting: {
            enabled: false
        },
        credits: {
            enabled: false
        },
    });
}

function initDetailedSummaryChart(id, data) {
    return Highcharts.chart(id, {
        chart: {
            backgroundColor: 'transparent',
            type: 'column'
        },
        title: {
            text: null,
            align: 'left'
        },
        xAxis: {
            categories: data['categories'],
            title: {
                text: null
            },
            gridLineWidth: 1,
            lineWidth: 0
        },
        exporting: {
            enabled: false
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Cost ($)'
            },
            stackLabels: {
                enabled: true
            }
        },
        tooltip: {
            valuePrefix: '$',
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true
                }
            }
        },
        credits: {
            enabled: false
        },
        series: data['series']
    });
}

$(onReadyCOA)

if (typeof process !== 'undefined' && process.env.JEST_WORKER_ID !== undefined) {
    module.exports = { SCENARIO_STATE, overrided_budget_impact_history, lastSelectedItemsMap, detailed_summary_view };
}

if (!window._rb) { window._rb = {}; }
window._rb.setCurrentCOA = setCurrentCOA;
window._rb.getCurrentCOA = getCurrentCOA;
window._rb.showCOAModal = showCOAModal;
window._rb.showLoadCOAModal = showLoadCOAModal;
window._rb.getCOAList = getCOAList;
window._rb.getUserSavedCOA = getUserSavedCOA;
window._rb.saveCOA = saveCOA;
window._rb.showLoadCOA = showLoadCOA;
window._rb.showCOA = showCOA;
window._rb.onReadyCOA = onReadyCOA;
window._rb.resetForm = resetForm;
window._rb.insertCoaTableRow = insertCoaTableRow;
window._rb.updateInsertCoaTableRowDropdown = updateInsertCoaTableRowDropdown;
window._rb.validateYear = validateYear;
window._rb.show_coa_output = show_coa_output;
window._rb.openConfirmationModal = openConfirmationModal;
window._rb.set_output_close_attr = set_output_close_attr;
window._rb.updateGrandTotal = updateGrandTotal;
window._rb.show_hide_override_table = show_hide_override_table;
window._rb.initEditorDataTable = initEditorDataTable;
window._rb.randomId = randomId;
window._rb.submit_for_coa_review = submit_for_coa_review;
window._rb.display_output_banner = display_output_banner;
window._rb.export_coa_results = export_coa_results; 
window._rb.toggleOriginalOutputTable = toggleOriginalOutputTable;
window._rb.closeNotification = closeNotification;
window._rb.filter_selected_programs = filter_selected_programs;
window._rb.loadTableMetadata = loadTableMetadata;
window._rb.updateOverridedBudgetImpactHistory = updateOverridedBudgetImpactHistory;
window._rb.dropdown_onchange = dropdown_onchange;
