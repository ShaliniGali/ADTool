"use strict";

function homeDs() {
    if (window.darkSwitch === 'dark') {
        $('html').addClass('dark');
    }
    if($('html').hasClass('dark')) {
        $('.theme-button').addClass('bx--btn--primary').removeClass('bx--btn--tertiary');
    } else {
        $('.theme-button').addClass('bx--btn--tertiary').removeClass('bx--btn--primary');
    }
}

var lastSelectedItemsMap = {
    'pom': ["ALL"],
    'ass-area': ["ALL"],
    'cs': ["ALL"],
    'program': [],
    'summary': ["ALL"],
    'details': ["ALL"],
    'approval': ["ALL"],
}

const selectionTextOptions = {
    'false': 'Select All',
    'true': 'Deselect All'
}

const chartColors = {
    'green': '#7eab55',
    'red':'#f65959'
};

let selected_options = {}

$(function() {

    $(`#${page}-summary-breadcrumb`).attr("hidden",false);
    $('#program-breakdown-breadcrumb').attr("hidden",true);
    if (selected_options['program_code'] !== '' && selected_options['cs'] !== '' && 
        selected_options['ass_area'] !== '' && selected_options['approval'] !== '' 
    ) {
        view_onchange(1, `summary`, '', selected_options['program_code']);
    }
});

var overlay_loading_html = `
<div class="bx--loading-overlay">
<div data-loading class="bx--loading">
  <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
    <title>Loading</title>
    <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
  </svg>
</div>
</div>`;

function view_onchange(id, view, program='', program_code='', view_id='') {
    if (program) {
        currentProgram = program;
    }
    if (currentProgram) {
        program = currentProgram
    }

    if (!program_code && selected_options['program_code']) {
        program_code = selected_options['program_code'];
    }

    // reset dropdown
    if (view_id == '') {
        $('#eoc-summary-container').html('');
        $('#historical-pom-data-container').html('');
        lastSelectedItemsMap['summary'] = ['ALL'];
        lastSelectedItemsMap['details'] = ['ALL'];
    }

    $('#program-breakdown-breadcrumb').attr("hidden",false);
    $('#program-breakdown-stacked-chart-container').attr("hidden",true);
    let input_object = get_input_object(id, view);
    input_object['program'] = program;
    input_object['program_code'] = program_code;
    input_object['view'] = view;
    if (view === 'details') {
        //setup php route to grab data
        $('#historical-pom-data-tag').attr("hidden",true);
        $('#eoc-historical-pom-data-container').attr("hidden",true);
        $('#historical-pom-data-container').attr("hidden",false);
        $('#eoc-summary-tag').attr("hidden",false);
        $('#eoc-summary-container').attr('hidden', true);
        $('#filter-summary-row').attr('hidden', true);
        $('#eoc-historical-pom-tag').attr("hidden",true);
        $('#overlay-loader').html(overlay_loading_html);

        let url =  '';
        if (view_id == '') {
            url = `/socom/${page}/historical_pom`
            view_id = '#historical-pom-data-container'
        }
        else {
            url =  `/socom/${page}/historical_pom/update`;
            view_id = `#${view_id}-data-view-container`
        }

        loadPageData(
            view_id,
            url,
            input_object,
            function() {
                $('#overlay-loader').html('');
            }
        )
    } else if (view === 'summary') {
        $('#historical-pom-data-tag').attr("hidden",false);
        $('#historical-pom-data-container').attr("hidden",true);
        $('#eoc-summary-tag').attr("hidden",true);
        $('#eoc-summary-container').attr('hidden', false);
        $('#filter-summary-row').attr('hidden', true);
        $('#eoc-historical-pom-tag').attr("hidden",true);
        $('#eoc-historical-pom-data-container').attr("hidden", true)

        let dropdown_object = {
            'zbt_summary': [
                'Approve',
                'Approve at Scale',
                'Disapprove'
            ],
            'issue': [
                'Approve',
                'Approve at Scale',
                'Disapprove'
            ]
        }

        let url =  '';
        if (view_id == '') {
            url =  `/socom/${page}/eoc_summary`;
            view_id = '#eoc-summary-container'
        }
        else {
            url =  `/socom/${page}/eoc_summary/update`;
            view_id = `#${view_id}-data-view-container`
        }

        input_object['dropdowns'] = dropdown_object[page]
        $('#overlay-loader').html(overlay_loading_html);
        loadPageData(
            view_id,
            url,
            input_object,
            function() {
                $(".selection-dropdown").select2({
                    placeholder: "Select an option",
                    width: 'auto'
                });
                $('#overlay-loader').html('');
            }
        )
    } else if (view === 'eoc_historical_pom') {
        $('#historical-pom-data-tag').attr("hidden",true);
        $('#historical-pom-data-container').attr("hidden",true);
        $('#eoc-summary-tag').attr("hidden",false);
        $('#eoc-summary-container').attr('hidden', true);
        $('#filter-summary-row').attr('hidden', true);
        $('#eoc-historical-pom-tag').attr("hidden",true);
        $('#eoc-historical-pom-data-container').attr("hidden", false)
        $('#overlay-loader').html(overlay_loading_html);
        show_eoc_historical_pom_view(input_object);
    }
    else {
        reset_program_summary_view();

        $(".selection-dropdown").select2({
            placeholder: "Select an option",
            width: '16vw'
        });
    }

    if (view == 'program') {
        clear_params();
    }

    const pageHeaderMap = {
        'zbt_summary': 'ZBT',
        'issue': 'Issue',
        'program': 'Program Breakdown',
        'details': 'Program Historical POM',
        'summary': 'EOC Details',
        'eoc_historical_pom': 'EOC Historical POM'
    } 
    const pageHeader = `${pageHeaderMap[page]} ${pageHeaderMap[view]}`
    $('#current-page-header').html(pageHeader);
}

function get_eoc_code_list_from_program_summary_table(program) {
    let rowIndex = -1;
    summary_dt_object.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var rowData = this.data();
        if (rowData['program'] === program) {
            rowIndex = rowIdx;
            return false; // stop iterating
        }
    });
    summary_dt_object.cell(rowIndex, 1).data();

    let eoc_code_list_string =  summary_dt_object.cell(rowIndex, 1).data();
    let eoc_code_list = eoc_code_list_string.split(',<br/>');

    return eoc_code_list;
}

function show_eoc_historical_pom_view(input_object) {
    loadPageData(
        '#eoc-historical-pom-data-container',
        `/socom/${page}/eoc_historical_pom`,
        input_object,
        function() {
            $('#overlay-loader').html('');
        }
    )
}

function reset_program_summary_view(){
    $('#historical-pom-data-tag').attr("hidden",true);
    $('#historical-pom-data-container').attr("hidden",true);
    $('#eoc-summary-tag').attr("hidden",true);
    $('#eoc-summary-container').attr('hidden', true);
    $('#eoc-historical-pomtag').attr("hidden",true);
    $('#eoc-historical-pom-data-container').attr("hidden",true);
    $('#program-breakdown-stacked-chart-container').attr("hidden",false);
    $('#filter-summary-row').attr('hidden', false);

}

function clear_params() {
    const currentUrl = window.location.href;
    const url = new URL(currentUrl);
    url.search = '';
    window.history.replaceState({}, document.title, url.toString());
}

function add_params(params) {
    const currentUrl = window.location.href;
    const url = new URL(currentUrl);
    for (const [key, value] of Object.entries(params)) {
        url.searchParams.set(key, value);
    }
    window.history.replaceState({}, document.title, url.toString());
}

function get_input_object(id, view='') {
    let input_object = {};
    if ($("#ass-area-" + id).val() != "" && $("#ass-area-" + id).val() != null) {
        input_object["ass-area"] = fetch_all_inputs(`#ass-area-${id}`)
    }

    if ($('#cs-' + id).val() != "" && $('#cs-' + id).val() != null) {
        input_object["cs"] = fetch_all_inputs(`#cs-${id}`)
    }


    if ($('#program-' + id).val() != "" && $('#program-' + id).val() != null) {
        input_object["program"] = fetch_all_inputs('#program-' + id);
        if ($('#program-' + id).val().includes('ALL')) {
            input_object["selected_all_programs"] = true; 
        }
    }

    if ($('#approval-' + id).val() != "" && $('#program-' + id).val() != '') {
        input_object["approval"] = $('#approval-' + id).val();
    }

    if ($(`#${view}-eoc-code`).val() != "" && $(`#${view}-eoc-code`).val() != null) {
        input_object["eoc_code"] = fetch_all_inputs(`#${view}-eoc-code`);
        if ($(`#${view}-eoc-code`).val().includes('ALL')) {
            input_object["selected_all_eoc_codes"] = true; 
            input_object["eoc_code"] = [];
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


function dropdown_onchange(id, type, event_id = null) {
    let input_object = {}
    switch(type) {
        case 'bin':
        case 'tag':
            if ( table_dropdown[id] === undefined) {
                table_dropdown[id] = $(`#${id}`).val();
            } else {
                table_dropdown[id] = table_dropdown[id].concat( $(`#${id}`).val());
            }
            break;
        case 'pom':
        case 'ass-area':
        case 'cs':
            dropdown_all_view(type, id);
            update_program_filter(id);
            break;
        case 'approval':
            disable_hide_apply_filter_button(id);
            break;
        case 'program':
            dropdown_all_view(type, id);
            disable_hide_apply_filter_button(id);
            break;
        case 'filter':
            input_object = get_input_object(id);

            $('#program-summary-filter').attr('disabled', true);
            $('#apply-filter-loading').attr('hidden', false);
            $('#overlay-loader').html(overlay_loading_html);
            fetchProgramBreakdownChartData(input_object, function() {
                $('#program-summary-filter').attr('disabled',   disable_hide_apply_filter_button(id));
                $('#apply-filter-loading').attr('hidden', true);
                $('#overlay-loader').html('');
                if ( selected_options['program_code'] == '') {
                    reset_program_summary_view();
                }
                else {
                    selected_options['program_code'] = '';
                }
            });

            // update_program_summary_table(id, input_object, function() {
            //     // update_program_summary_card(id, input_object, function() {
            //     //     $('#program-summary-filter').attr('disabled',   disable_hide_apply_filter_button(id));
            //     //     $('#apply-filter-loading').attr('hidden', true);
            //     //     $('#overlay-loader').html('');
            //     //     if ( selected_options['program_code'] == '') {
            //     //         reset_program_summary_view();
            //     //     }
            //     //     else {
            //     //         selected_options['program_code'] = '';
            //     //     }
            //     // })

            //     $('#program-summary-filter').attr('disabled',   disable_hide_apply_filter_button(id));
            //     $('#apply-filter-loading').attr('hidden', true);
            //     $('#overlay-loader').html('');
            //     if ( selected_options['program_code'] == '') {
            //         reset_program_summary_view();
            //     }
            //     else {
            //         selected_options['program_code'] = '';
            //     }
            // });
            break;
        case 'save':
            input_object = get_input_object(id);

            $('#save-manual-changes').attr('disabled', true);
            $('#apply-save-loading').attr('hidden', false);
            input_object['is_manual_changes'] = true;
            $('#overlay-loader').html(overlay_loading_html);
            save_program_summary_table(id, input_object, function() {
                $('#save-manual-changes').attr('disabled',   disable_hide_apply_filter_button(id));
                $('#apply-save-loading').attr('hidden', true);
                $('#overlay-loader').html('');    
            })
            break;
        case 'eoc-summary':
            // save value
            save_ao_ad_dropdown(id, 'dropdown', page, event_id);
            break;
        case 'eoc-code':
            dropdown_all_view(id, type);
            let view_id = id === 'details' ? 'historical-pom' : 'eoc-summary';
            view_onchange(1, id, '', selected_options['program_code'], view_id);
            break;
        default:
            break;
    }
}

function check_filter_limit(type, id, limit) {
    const dropdown_id = `${type}-${id}`;
    const $dropdown = $(`#${dropdown_id}`);

    const filterErrorId = `${type}-filter-error`;
    const $filterError = $(`#${filterErrorId}`);

    const $applyFilterBtn = $('#program-summary-filter');

    if ($dropdown.val().length > limit) {
        $applyFilterBtn.attr('disabled', true);

        if ($filterError.length === 0) {
            const filterText = `filter${limit > 1 ? "s" : ""}`;
            $(`#${type}-dropdown`).append(
                `<div id="${filterErrorId}" class="alert alert-warning mb-0 mt-2" style="width: 16vw;">
                    You can only apply up to ${limit} ${filterText}. Please remove some ${filterText} to proceed.
                </div>`
            );
        }
    } else {
        $applyFilterBtn.attr('disabled', false);
        if ($filterError.length) {
            $filterError.remove();
        }
    }
}

function table_filter_onchange(id, type) {
    switch(type) {
        case 'approval-status':
            approvalStatusDropdownOnchange(id);
            break;
        default:
            break;
    }
}

function approvalStatusDropdownOnchange(id) {
    let approvalStatus = [];
    let input_object = get_input_object(id);
    if ($('#approval-status-' + id).val() != "" && $('#approval-status-' + id).val() != null) {
        approvalStatus = $('#approval-status-' + id).val()
    }

    // update summary card
    update_program_summary_card(id, input_object, function () {
        //filter out the table
        if ( approvalStatus.length !== 0) {
            var regexPattern = approvalStatus.join('|');
            summary_dt_object.columns(12).search(regexPattern, true, false).draw()
        }
        else {
            summary_dt_object.columns(12).search('No Data').draw()
        }

    }, approvalStatus);
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

function selectionHasChanged(id) {
    const lastSelections = lastSelectedItemsMap[id];
    return lastSelections.includes("ALL")
}

function toggleSeriesVisibility(chart, plotData, show) {
    chart.series.forEach(function(series) {
        if (series.options.name === plotData) {
            series.setVisible(show);
        }
    });
}

function disable_hide_apply_filter_button(id) {
    let input_object = get_input_object(id);

    console.log(input_object)
    if (input_object["ass-area"] && input_object["cs"] && input_object["program"] &&  input_object["approval"]) {
        $('#program-summary-filter').attr('disabled', false);
        $('#save-manual-changes').attr('disabled', false);
    }
    else {
        $('#program-summary-filter').attr('disabled', true);
        $('#save-manual-changes').attr('disabled', true);
    }
}

function update_program_filter(id) {
    let input_object = get_input_object(id);
    const programSelectionButton = `#program-${id}-selection`;
    $(programSelectionButton).attr('data-select-all', 'true');
    $(programSelectionButton).html(selectionTextOptions['false']);
    $(programSelectionButton).attr('disabled', true)
    if ($(`#program-${id}`).val().length) {
        $(`#program-${id}`).val(null).trigger('change')
    }
    if (input_object["ass-area"] && input_object["cs"]) {
        $(`#program-${id}`).attr('disabled', true);
        $.post("/socom/program_breakdown/filter/update", {
            rhombus_token: rhombuscookie(),
            cs: input_object["cs"],
            'ass-area': input_object["ass-area"],
            page: page,
            section: 'program_summary'
        }, function(data) {
            if (data !== null) { 
                let programList = data['data'];
                let programOptions = '';
                programList.forEach( v => {
                    let selected = selected_options['program_group'] === v['PROGRAM_GROUP'] ? 'selected' : '';
                    programOptions += `<option value="${v['PROGRAM_GROUP']}" ${selected}>${v['PROGRAM_GROUP']}</option>`;
                });

                if (selected_options['approval']) {
                    $(`#approval-${id}`).val(selected_options['approval']).trigger('change');
                }

                $(`#program-${id}`).select2('destroy')

                $(`#program-${id}`).remove();

                $(`#program-dropdown`).append(
                    `<select 
                    id="program-${id}" 
                    type="program" 
                    combination-id="" 
                    class="selection-dropdown" 
                    multiple="multiple"
                    onchange="dropdown_onchange(1, 'program')"
                    disabled
                >
                    <option option="ALL">ALL</option>
                    ${programOptions}
                </select>`)


                $(`#program-${id}`).select2({
                    placeholder: "Select an option",
                    width: '16vw'
                })
                .on('change.select2', function() {
                        var dropdown = $(this).siblings('span.select2-container');
                        if (dropdown.height() > 100) {
                            dropdown.css('max-height', '100px');
                            dropdown.css('overflow-y', 'auto');
                        }
                })
                $(`#program-${id}`).attr('disabled', false);
                $(`#approval-${id}`).attr('disabled', false);
                $(programSelectionButton).attr('disabled', false)
                disable_hide_apply_filter_button(id);

                // trigger apply filter if program is defined on url 
                if (selected_options['program_group'] != '' && selected_options['approval'] != '') {
                    $('#program-summary-filter').trigger('click');
                    //reset program group
                    selected_options['program_group'] = '';
                }

            }
        })
       
    }
    else {
        $(`#program-${id}`).attr('disabled', true);
    }
}

function update_approval_filter(id) {
    let input_object = get_input_object(id);
    const approvalSelectionButton = `#approval-${id}-selection`;
    $(approvalSelectionButton).attr('data-select-all', 'true');
    $(approvalSelectionButton).html(selectionTextOptions['false']);
    $(approvalSelectionButton).attr('disabled', true)
    if ($(`#approval-${id}`).val().length) {
        $(`#approval-${id}`).val(null).trigger('change')
    }



    if (input_object["ass-area"] && input_object["cs"] && input_object["program"]) {
        $(`#approval-${id}`).attr('disabled', true);
        $.post("/socom/program_breakdown/filter/update", {
            rhombus_token: rhombuscookie(),
            ...input_object,
            page: page
        }, function(data) {
            if (data !== null) { 
                let programList = data['data'];
                let programOptions = '';
                programList.forEach( v => {
                    let selected = selected_options['program_group'] === v['PROGRAM_GROUP'] ? 'selected' : '';
                    programOptions += `<option value="${v['PROGRAM_GROUP']}" ${selected}>${v['PROGRAM_GROUP']}</option>`;
                });

                $(`#program-${id}`).select2('destroy')

                $(`#program-${id}`).remove();

                $(`#program-dropdown`).append(
                    `<select 
                    id="program-${id}" 
                    type="program" 
                    combination-id="" 
                    class="selection-dropdown" 
                    multiple="multiple"
                    onchange="dropdown_onchange(1, 'program')"
                    disabled
                >
                    <option option="ALL">ALL</option>
                    ${programOptions}
                </select>`)


                $(`#program-${id}`).select2({
                    placeholder: "Select an option",
                    width: '16vw'
                })
                .on('change.select2', function() {
                        var dropdown = $(this).siblings('span.select2-container');
                        if (dropdown.height() > 100) {
                            dropdown.css('max-height', '100px');
                            dropdown.css('overflow-y', 'auto');
                        }
                })
                $(`#program-${id}`).attr('disabled', false);
                $(programSelectionButton).attr('disabled', false)
                disable_hide_apply_filter_button(id);

                // trigger apply filter if program is defined on url 
                if (selected_options['program_group'] != '') {
                    $('#program-summary-filter').trigger('click');
                    //reset program group
                    selected_options['program_group'] = '';
                }

            }
        })
       
    }
    else {
        $(`#program-${id}`).attr('disabled', true);
    }
}

function update_program_summary_card(id, input_object, callback = null, approval_status=['PENDING', 'COMPLETED']) {
    loadPageData(
        "#program-summary-card-container",
        `/socom/${page}/program_breakdown/card/update`,
        { 
            'approval-status': approval_status,
            ...input_object
        }, function() {
            if (callback) {
                callback();
            }
        }
    );
}

function update_program_summary_table(id, input_object, callback = null) {
    $.post(`/socom/${page}/program_breakdown/table/update`,
        {
            rhombus_token: rhombuscookie(),
            ...input_object
        }, 
        function(data) {
            if (data.message === undefined) {
                $('#program-table-container').html(data);
                $('#program-summary-filter').attr('disabled',   disable_hide_apply_filter_button(id));
                $(`#program-table-container #approval-status-${id}.selection-dropdown`).select2({
                    placeholder: "Pending / Completed",
                    width: '18vw'
                })
            }
        }
    )
    .done(function(jqXHR) { 
        if (jqXHR.message !== undefined) {
            displayToastNotification('success', jqXHR.message);
        }
        if (callback) {
            callback();
        }
    });
}

function save_program_summary_table(id, input_object, callback = null) {
    $.post(`/socom/${page}/program_breakdown/table/save`,
        {
            rhombus_token: rhombuscookie(),
            ...input_object
        }, 
        function(data) {
            if (callback) {
                callback();
            }
        },
        "json"
    )
    .done(function(jqXHR) { 
        if (jqXHR.message !== undefined) {
            displayToastNotification('success', jqXHR.message);
        }
    });
}

function initDatatable(container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, sharedColumnRows, rowspanCount, rowPerPage, lengthMenu) {
    let currentProgramName = null;
    return $(`#${container}`).DataTable({
        dom: 'lrtip',
        order: [],
        orderable: false,
        ordering: false,
        iDisplayLength: rowPerPage,   // number of rows to display
        rowHeight: '75px',
        data:tableData,
        columns: tableHeaders,
        lengthMenu: lengthMenu,
        columnDefs: [
            {
                targets: yearIndex,
                className: 'dt-body-right'
            },
            {
                targets: 0,
                orderable: false,
                className: 'dt-body-center'
            },
            {
                targets: Object.keys(tableData[0]).length - 3,
                visible: false
            },
            {
                targets: Object.keys(tableData[0]).length - 2,
                visible: false
            }
        ],
        'rowCallback': function(row, data, index){
            for(let i=0; i<yearList.length; i++){
                if(data[yearList[i]] < 0){
                    $(row).find('td:eq('+(i+indexOfFirstYear + 1)+')').css('background-color', '#f65959');
                    $(row).find('td:eq('+(i+indexOfFirstYear + 1)+')').css('color', '#FFF');
                }
            }
            const rowProgramName = data['program'];
            const isFirstRow = (rowProgramName !== currentProgramName) && (!!rowProgramName);
            if (isFirstRow) {
                currentProgramName = rowProgramName
            } else {
                currentProgramName = null;
            }
            if (data['status'] === 'PENDING' ) {
                $(row).find('td:eq('+(tableHeaders.length - 3)+')').css('background-color', '#FFBF00');
                $(row).find('td:eq('+(tableHeaders.length - 3)+')').css('color', '#FFF');
                if (isFirstRow) {
                    $(row).find('td:eq('+(tableHeaders.length - 3)+')').html('Pending');
                } else {
                    $(row).find('td:eq('+(tableHeaders.length - 3)+')').html('');
                }
            } else  {
                $(row).find('td:eq('+(tableHeaders.length - 3)+')').css('background-color', '#7EAB55');
                $(row).find('td:eq('+(tableHeaders.length - 3)+')').css('color', '#FFF');
                if (isFirstRow) {
                    $(row).find('td:eq('+(tableHeaders.length - 3)+')').html('Complete');
                } else {
                    $(row).find('td:eq('+(tableHeaders.length - 3)+')').html('');
                }
            }

            if (rowProgramName) {
                for (let column of sharedColumnRows) {
                    $(row).find('td:eq('+(column)+')').css('border-bottom', 'none');
                }
            } else if (!rowProgramName) {
                for (let column of sharedColumnRows) {
                    $(row).find('td:eq('+(column)+')').css('border-top', 'none');
                    $(row).find('td:eq('+(column)+')').css('border-bottom', 'none');
                }
            }
        },
        
        'drawCallback' :  function () {
            let table = this.api();
            let rows = table.rows({ page: 'current' }).nodes();

            let programCount = rowspanCount;

            let targetProgramList = [];
            tableData.forEach( v => {
                targetProgramList.push(v['program']);
            })

            rows.each(function(element, index) {
                let cells = $(element).find('td');
                if (index % rowspanCount !== 0) {                    
                    cells.each(function(index) {
                        let cellContent = $(this).text();

                        if (targetProgramList.includes(cellContent)) {
                            $(this).remove();
                        }
                    });
                }

                if (index % rowspanCount == 0) { 
                    let initProgramCell = $(rows[index]).find('td:eq(0)');
                    let initBinCell = $(rows[index]).find('td:eq(1)');
                    let initTagCell = $(rows[index]).find('td:eq(2)');
                    let initHistoricalCell = $(rows[index]).find('td:eq(10)');
                    let initEOCCell = $(rows[index]).find('td:eq(11)');
                    let initApprovalCell = $(rows[index]).find('td:eq(12)');

                
                    initProgramCell.attr('rowspan', programCount);
                    initBinCell.attr('rowspan', programCount);
                    initTagCell.attr('rowspan', programCount);
                    initHistoricalCell.attr('rowspan', programCount);
                    initEOCCell.attr('rowspan', programCount);
                    initApprovalCell.attr('rowspan', programCount);
                }
            });
        },
    
        initComplete: function() { 
            isChecked();
        }
    });
}

function isChecked() {
    let checked = $('#checkbox_visibility').prop('checked');

    if (checked) {
        $('#program-table-output').DataTable().column(14).search("hidden").draw();
    } else {
        $('#program-table-output').DataTable().column(14).search("not hidden").draw();
    }
}

function onReady(callback) {

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
};

function addClassToEditedCell(cell, tooltipInfo={}) {
    cell.addClass('ember-cell');
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

function initHistoricalDatatable(container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, sharedColumnRows) {
    const displayLength = -1;
    return $(`#${container}`).DataTable({
        dom: 'ti',
        order: [],
        orderable: false,
        ordering: false,
        iDisplayLength: displayLength,
        rowHeight: '75px',
        data:tableData,
        columns: tableHeaders,
        columnDefs: [
            {
                targets: yearIndex,
                className: 'dt-body-right'
            },
            {
                targets: 0,
                orderable: false,
                className: 'dt-body-center'
            },
            {
                targets: Object.keys(tableData[0]).length - 3,
                visible: false
            }
        ],
        'rowCallback': function(row, data, index){
            for(let i=0; i<yearList.length; i++){
                if(data[yearList[i]] < 0){
                    $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('background-color', '#f65959');
                    $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('color', '#FFF');
                }
            }
    
        },
        'drawCallback' :  function () {
            let table = this.api();
            let rows = table.rows({ page: 'current' }).nodes();
            let programCount = rows.length;
        
            let targetProgram = tableData[0]['program'];
            let targetBin = tableData[0]['bin'];
            let targetTag = tableData[0]['tag'];        
            rows.each(function(element, index) {
                let cells = $(element).find('td');
                if (index !== 0) {                    
                    cells.each(function(index) {
                        let cellContent = $(this).text();
            
                        if (cellContent === targetProgram) {
                            $(this).remove();
                        }
                    });
                }
            });
            let initProgramCell = $(rows[0]).find('td:eq(0)');
            let initBinCell = $(rows[0]).find('td:eq(1)');
            //let initTagCell = $(rows[0]).find('td:eq(2)');
        
            initProgramCell.attr('rowspan', programCount);
            initBinCell.attr('rowspan', programCount);
            //initTagCell.attr('rowspan', programCount);
        }
    
    });
}

function initEocSummaryDatatable(
    container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, aeaoIndexList, initHeadersIndexList, page
) {
    const displayLength = -1;

    return $(`#${container}`).DataTable({
        dom: 'ti',
        order: [],
        orderable: false,
        ordering: false,
        iDisplayLength: displayLength,
        rowHeight: '75px',
        data:tableData,
        columns: tableHeaders,
        columnDefs: [
            {
                targets: yearIndex,
                className: 'dt-body-right'
            },
            {
                targets: 0,
                orderable: false,
                className: 'dt-body-center'
            },
            {
                targets: tableHeaders.length - 1,
                visible: false
            },
            {
                "targets": "_all", 
                "render": function(data, type, row, meta) {
                    return data === null ? '' : data;
                }
            }
        ],
        'rowCallback': function(row, data, index){
            for(let i=0; i<yearList.length; i++){
                if(data[yearList[i]] < 0){
                    $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('background-color', '#f65959');
                    $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('color', '#FFF');
                }
            }
    
        },
        'drawCallback' :  function () {
            let table = this.api();
            let rows = table.rows({ page: 'current' }).nodes();
            let targetIndices = initHeadersIndexList;
            let fieldIndices = aeaoIndexList;
            let currentTopIndex = 0;
            let removeList = [];
            let modulo = 3;
            rows.each(function(element, index) {
                if ((index % modulo) !== 0) {              
                    targetIndices.forEach(idx => {
                       fieldIndices.forEach(i => {                    
                            $(`td:eq(${i})`, element).remove();
                        })      
                        removeList.push($(`td:eq(${idx})`, element))
                    })
                    while(removeList.length) {
                        let lastItem = removeList.pop();
                        lastItem.remove();
                    }
                } else {
                    let rowCount = modulo;
                    fieldIndices.forEach( index => {
                        let element = $(rows[currentTopIndex]).find(`td:eq(${index})`);
                        element.attr('rowspan', rowCount);
                    })
                    targetIndices.forEach( index => {
                        let element = $(rows[currentTopIndex]).find(`td:eq(${index})`);
                        element.attr('rowspan', rowCount);
                    })
                    currentTopIndex += modulo
                }
                
            });
        
        }
    
    });
}

function isSameProgramGrouping(rows, mappingIndices, topItemMap, index) {
    let isSameGroup = true;
    for (let i = 0; i < mappingIndices.length; i++) {
        let idx = mappingIndices[i];
        const mappedCell = topItemMap[idx];
        const currentCell = $(rows[index]).find(`td:eq(${idx})`).text();
        if (mappedCell !== currentCell) {
            isSameGroup = false;
            break;
        }
    }
    return isSameGroup;
}

function initEocHistoricalDatatable(container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, sharedColumnRows) {
    const displayLength = -1;
    return $(`#${container}`).DataTable({
        order: [],
        orderable: false,
        ordering: false,
        iDisplayLength: displayLength,
        rowHeight: '75px',
        data:tableData,
        columns: tableHeaders,
        columnDefs: [
            {
                targets: yearIndex,
                className: 'dt-body-right'
            },
            {
                targets: 0,
                orderable: false,
                className: 'dt-body-center'
            },
            {
                targets: Object.keys(tableData[0]).length - 2,
                visible: false
            }
        ],
        'rowCallback': function(row, data, index){
            for(let i=0; i<yearList.length; i++){
                if(data[yearList[i]] < 0){
                    $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('background-color', '#f65959');
                    $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('color', '#FFF');
                }
            }
    
        },
        'drawCallback' :  function () {
            let table = this.api();
            let rows = table.rows({ page: 'current' }).nodes();
            let targetIndices = [0, 1, 2, 3, 4, 5, 6];
            let mappingIndices = [0, 1, 2, 3, 6];
            let removeList = [];
            let rowCount = 0;
            let currentTopIndex = 0;
            let topItemMap = {}
            mappingIndices.forEach((index) => {
                topItemMap[index] = $(rows[0]).find(`td:eq(${index})`).text();
            })
            rows.each(function(element, index) {
                let isSameGroup = isSameProgramGrouping(rows, mappingIndices, topItemMap, index);
                if ((index !== 0)  && isSameGroup) {        
                    targetIndices.forEach(idx => {
                        removeList.push($(`td:eq(${idx})`, element))
                    })
                    while(removeList.length) {
                        let lastItem = removeList.pop();
                        lastItem.remove();
                    }
                } else {
                    mappingIndices.forEach((idx) => {
                        topItemMap[idx] = $(rows[index]).find(`td:eq(${idx})`).text();
                    })
                    mergeRows(rows, targetIndices, currentTopIndex, rowCount);
                    currentTopIndex += rowCount
                    rowCount = 0;
                }
                rowCount++;
            });
            mergeRows(rows, targetIndices, currentTopIndex, rowCount);
        }
    
    });
}

function mergeRows(rows, targetIndices, currentTopIndex, rowCount) {
    targetIndices.forEach((index) => {
        const cell = $(rows[currentTopIndex]).find(`td:eq(${index})`);
        cell.attr('rowspan', rowCount)
    })
}


function getDropdownSelectionByUserID(data, user_id, is_ao_ad) {
    const userObject = data.find(item => is_ao_ad === 'ao' ? item.AO_USER_ID == user_id : item.AD_USER_ID == user_id);

    let recommendationKey;
    if (userObject) {
        recommendationKey = is_ao_ad === 'ao' ? 'AO_RECOMENDATION' : 'AD_RECOMENDATION';
    }

    return recommendationKey ? userObject[recommendationKey] : null;
}

function updateApprovalList(data, is_ao_ad) {
    const $approvalList = $('#ao-ad-dropdown-list');
    let $approvalListEls = "";

    data.forEach(item => {
        console.log(item)
        const approvalStatus = is_ao_ad === 'ao' ? item.AO_RECOMENDATION : item.AD_RECOMENDATION;
        if (approvalStatus !== null) {
            const $emailTag = item.email ? `<span class="bx--tag bx--tag--orange">${item.email}</span>` : '';
            const trashIcon = item.SHOW_CAN == 1 ? `<i class="fas fa-trash mx-2 delete-icon-dropdown-ps" style="cursor: pointer;"></i>` : '';

            $approvalListEls += `<li class='bx--list__item d-flex align-items-center' data-id='${item.ID}'>
                                    ${approvalStatus}
                                    ${$emailTag}
                                    ${trashIcon}
                                </li>`;
            // $approvalArr.push($($approvalListEls).data('id', item.ID))
        }
    });

    $approvalList.html($approvalListEls);
}

function viewDropdownModal(id, event_id, user_id, enabled, page) {
    let is_ao_ad = id.startsWith('ao-') ? 'ao' : 'ad'; // returns 'ao' or 'ad'

    $('#ao-ad-dropdown-view-modal').data('id', id);
    $('#event_id').val(sanitizeHtml(event_id,  { allowedAttributes:{}, allowedTags:[]}));

    let data = JSON.parse($(`#${id}`).val());
    let currentSelection = getDropdownSelectionByUserID(data, user_id, is_ao_ad);

    $('#ao-ad-dropdown-view-modal').data('is_ao_ad', is_ao_ad);

    $('#ao-ad-dropdown-view-modal div div h3').html(is_ao_ad === 'ao' ? 'AO Recommendation Review' : 'AD Approval Review');
    $('#ao-ad-dropdown-view-modal label[for="text-input-title"]').html(is_ao_ad === 'ao' ? 'AO Recommendation' : 'AD Approval');;
    $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection').data('id', id);
    
    // Disable/enable dropdown based on user's ao/ad status
    $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection').prop('disabled', !enabled);
    $('#ao-ad-dropdown-view-modal button.edit_button').prop('disabled', !enabled);
  
    let $optionEls = '<option></option>';

    if (page === 'zbt_summary') {
        [
            'Approve',
            'Approve at Scale',
            'Disapprove'
        ].forEach(option => {
            $optionEls += `<option ${option === currentSelection ? 'selected' : ''}>${option}</option>`;
        });
    } else if (page === 'issue') {
        [
            'Approve',
            'Approve at Scale',
            'Disapprove'
        ].forEach(option => {
            $optionEls += `<option ${option === currentSelection ? 'selected' : ''}>${option}</option>`;
        });
    }

    $('#ao-ad-dropdown-selection').html($optionEls);

    $('#ao-ad-dropdown-selection').on('change.dropdown_onchange', function() {
        dropdown_onchange('ao-ad-dropdown-selection', `eoc-summary`, event_id);
    });

    $('#ao-ad-dropdown-view-modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

    updateApprovalList(data, is_ao_ad);
}

function viewEventJustification(id, event_justification_data, user_id, enabled, event_id, page){

    var storedEventJustification = JSON.parse(event_justification_data);
    
    $('#event-justification-text').text(storedEventJustification);
    $('#event-justification-view-modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
}

function updateCommentList(data, is_ao_ad) {
    const $commentList = $('#ao-ad-comment-list');
    let $commentListEls = "";

    data.forEach(item => {
        const commentText = is_ao_ad === 'ao' ? item.AO_COMMENT : item.AD_COMMENT;
        if (commentText !== null && commentText.length > 0) {
            const $emailTag = item.email ? `<span class="bx--tag bx--tag--orange">${item.email}</span>` : '';
            const trashIcon = item.SHOW_CAN == 1 ? `<i class="fas fa-trash mx-2 delete-icon-comment-ps" style="cursor: pointer;"></i>` : '';

            $commentListEls += `<li class='bx--list__item d-flex align-items-center' data-id='${item.ID}'>
                                    ${commentText}
                                    ${$emailTag}
                                    ${trashIcon}
                                </li>`;
            // $commentListArr.push($($commentListEls).data('id', item.ID));
            // $($commentListEls).data('id', item.ID);
        }
    });

    $commentList.html($commentListEls);
}

function viewCommentModal(id, event_id, enabled) {
    $('#event_id').val(sanitizeHtml(event_id,  { allowedAttributes:{}, allowedTags:[]}));
    $('#ao-ad-comment-view-modal div div h3').html(id.startsWith('ao-') ? 'AO Comment Review' : 'AD Comment Review');
    $('#ao-ad-comment-view-modal').data('id', id);
    $('#ao-ad-comment-view-modal').data('is_ao_ad', id.startsWith('ao-') ? 'ao' : 'ad');
    
    $('#ao-ad-comment-view-modal button.edit_button').prop('disabled', !enabled);

    $('#ao-ad-comment-view-modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

    let comments = JSON.parse($(`#${id}`).val());
    let is_ao_ad = $('#ao-ad-comment-view-modal').data('is_ao_ad');

    updateCommentList(comments, is_ao_ad);
}

function saveAOADComment() {
    let value = sanitizeHtml($(`#ao-ad-comment-textarea`).val(), { allowedAttributes:{}, allowedTags:[]}),
        is_ao_ad = $('#ao-ad-comment-view-modal').data('is_ao_ad'),
        event_id = sanitizeHtml($('#event_id').val(), { allowedAttributes:{}, allowedTags:[]}),
        url;

    if (page === 'zbt_summary') {
        if (is_ao_ad === 'ao') {
            url = '/socom/zbt_summary/eoc_summary/ao/comment/save';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/zbt_summary/eoc_summary/ad/comment/save';
        }
    } else if (page === 'issue') {
        if (is_ao_ad === 'ao') {
            url = '/socom/issue/eoc_summary/ao/comment/save';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/issue/eoc_summary/ad/comment/save';
        }
    }

    if (url === undefined ||
        event_id.trim().length === 0
    ) {
        displayToastNotification('error', 'AO or AD Comment Save Error');
        return false;
    }

    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            value: value,
            event_id: event_id,
        }, 
        function(data) {
            if (data.status === true) {
                $('#ao-ad-comment-view-modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
                $('#ao-ad-comment-textarea').val('');

                const updatedResults = data.comments;

                let updatedResultsWithEmail = updatedResults.map(comment => {
                    let user_id = null;

                    if (is_ao_ad === 'ao') {
                        user_id = comment['AO_USER_ID']; 
                    } else if (is_ao_ad === 'ad') {
                        user_id = comment['AD_USER_ID'];
                    }

                    comment['email'] = user_emails[user_id];

                    return comment;
                })

                // Re-render current comment list with updated values
                updateCommentList(updatedResultsWithEmail, is_ao_ad);
                updatedResultsWithEmail = JSON.stringify(updatedResultsWithEmail);

                // Update comments for all rows with same event_id
                const hiddenInputComments = $(`.${is_ao_ad}-${event_id}-comments`);
                hiddenInputComments.val(updatedResultsWithEmail);

                displayToastNotification('success', 'AO or AD Comment Save Complete');
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to Save AO or AD Comment'); });
}

function save_ao_ad_dropdown(id, type, page, event_id) {
    let elem = $(`#${id}`), value = elem.val(), url;

    let is_ao_ad = $('#ao-ad-dropdown-view-modal').data('is_ao_ad');

    if (page === 'zbt_summary') {
        if (is_ao_ad === 'ao') {
            url = '/socom/zbt_summary/eoc_summary/ao/dropdown/save';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/zbt_summary/eoc_summary/ad/dropdown/save';
        }
    } else if (page === 'issue') {
        if (is_ao_ad === 'ao') {
            url = '/socom/issue/eoc_summary/ao/dropdown/save';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/issue/eoc_summary/ad/dropdown/save';
        }
    }

    if (
        ['dropdown'].indexOf(type) === -1 || 
        url === undefined ||
        event_id.trim().length === 0
    ) {
        displayToastNotification('error', 'AO or AD Comment Save Error');

        return false;
    }

    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            value: sanitizeHtml(value, { allowedAttributes:{}, allowedTags:[]}),
            type: sanitizeHtml(type, { allowedAttributes:{}, allowedTags:[]}),
            event_id: sanitizeHtml(event_id, { allowedAttributes:{}, allowedTags:[]})
        }, 
        function(data) {
            if (data.status === true) {
                const updatedResults = data.dropdown;

                let updatedResultsWithEmail = updatedResults.map(dropdown => {
                    let user_id = null;

                    if (is_ao_ad === 'ao') {
                        user_id = dropdown['AO_USER_ID']; 
                    } else if (is_ao_ad === 'ad') {
                        user_id = dropdown['AD_USER_ID'];
                    }

                    dropdown['email'] = user_emails[user_id];

                    return dropdown;
                })

                // Re-render current approvals list with updated values
                updateApprovalList(updatedResultsWithEmail, is_ao_ad);
                updatedResultsWithEmail = JSON.stringify(updatedResultsWithEmail);

                // Update approvals for all rows with same event_id
                const hiddenInputApprovals = $(`.${is_ao_ad}-${event_id}-approvals`);
                hiddenInputApprovals.val(updatedResultsWithEmail);
                
                console.log(type);

                const typeMap = {
                    'ao': 'AO Recommendation',
                    'ad': 'AD Approval',
                }

                displayToastNotification('success', `${typeMap[is_ao_ad]} Save Complete`);
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to Save AO or AD Dropdown'); });
}

function fetchProgramBreakdownChartData(input_object, callback = null) {
    $.ajax({
        url: `/socom/${page}/program_breakdown/graph/update`,
        type: 'POST',
        data: {...input_object, rhombus_token: rhombuscookie()},
        dataType: 'json',
        success: function(response) {
            if (response.message !== undefined) {
                displayToastNotification(response.type, response.message);
            }   
            else if (response.success && response.data) {
                renderStackedBarChart('program-breakdown-stacked-chart', response.data);
            }    
            else {
                console.error('Failed to fetch chart data:', response.error);
            }

            if (callback) {
                callback();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function renderStackedBarChart(containerId, data) {
    
    Highcharts.chart(containerId, {
        chart: {
            type: 'column'
        },
        legend: {
            enabled: false
        },
        title: {
            text: data.title
        },
        xAxis: {
            categories: data.categories,
        },
        yAxis: {
            title: {
                text: ''
            },
            stackLabels: {
                enabled: true,
                formatter: function () {
            
                    // Format the total value  
                    const category =  this.axis.chart.xAxis[0].categories[this.x];
                    return Highcharts.numberFormat(data.total[category], 0, '.', ',');
                },
            }
        },
        tooltip: {
            useHTML: true, // Allows custom HTML formatting
            formatter: function () {
                // Mimic default tooltip styling
                const category = this.key;
                let currentValue = this.y;


                if (this.color === chartColors['red']) {
                    currentValue = -currentValue;
                }

                const value = Highcharts.numberFormat(currentValue, 0); // Format number with commas
                const programCode = this.series.userOptions.program_code; // Custom property

                return `
                    <span style="font-size:10px">${category}</span><br/>
                    <p style="font-size:0.8rem;">
                        <span style="color:${this.color};">\u25CF</span> 
                        ${programCode}: <strong>${value}</strong>
                    </p>
                `;
            }     
        },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false
                },
                point: {
                    events: {
                        click: function () {
                            let program_code = this.series.userOptions.program_code;
                            add_params({
                                'program-code': program_code
                            });
                            selected_options['program_code'] = program_code;
                            currentProgram = this.series.name;

                            view_onchange(1, 'summary', this.series.name, program_code)
                        }
                    }
                }
            },
            column: {
                dataLabels: {
                    enabled: true,
                    inside: true,
                    format : '{point.series.name}'
                }
            }
        },
        series: data.series,
        credits: {
            enabled: false
        },
        exporting: {
            enabled: false
        }
    });
}

function initHistoricalGraph(id, data) {
    Highcharts.chart(id, {
        title: {
            text: data['title'],
        },
        subtitle: {
            text: data['subtitle']
        },
        xAxis: {
            categories: data['categories']
        },
        yAxis: {
            title: {
                text: 'Dollars (Thousands)'
            },
            labels: {
                formatter: function () {
                    // Custom formatting logic here
                    return '$' + Highcharts.numberFormat(this.value, 0, '.', ','); // Adding dollar sign and separator
                }
            }
        },
        tooltip: {
            shared: true,
            headerFormat: '<span style="font-size:12px"><b>{point.key}</b></span><br>'
        },
        plotOptions: {
            series: {
                pointStart: data[0]
            },
            area: {
                stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            }
        },
        series: data['data'],
        credits:{
            enabled:false
        },
        exporting: {
            enabled: false
        }
    });  
}

$(document).on('click', '.delete-icon-comment-ps', function(){
    let $item = $(this).closest('li')
    
    const itemId = $item.data('id');
    
    // const type = $(this).hasClass('delete-icon-comment-ps') ? 'program' : 'event';
    // $item.remove();

    let is_ao_ad = $('#ao-ad-comment-view-modal').data('is_ao_ad');
    let event_id = sanitizeHtml($('#event_id').val(), { allowedAttributes:{}, allowedTags:[]});
    let url;

    if (page === 'zbt_summary') {
        if (is_ao_ad === 'ao') {
            url = '/socom/zbt_summary/eoc_summary/ao/comment/delete';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/zbt_summary/eoc_summary/ad/comment/delete';
        }
    } else if (page === 'issue') {
        if (is_ao_ad === 'ao') {
            url = '/socom/issue/eoc_summary/ao/comment/delete';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/issue/eoc_summary/ad/comment/delete';
        }
    }

    if (url === undefined ||
        event_id.trim().length === 0
    ) {
        displayToastNotification('error', 'AO or AD Comment Delete Error');
        return false;
    }

    $.ajax({
        url, 
        type: 'POST',
        data: { id: itemId, rhombus_token: rhombuscookie(), event_id: event_id },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                console.log("deletion sucessful");
                let aoad_elem = $(`.${is_ao_ad}-${event_id}-comments`),
                    aoad_val = JSON.parse(aoad_elem.val());
                
                if (aoad_val instanceof Array) {
                    const findAOADIndexFn = (element) => element?.ID == itemId;
                    const findAOADIndex = aoad_val.findIndex(findAOADIndexFn);
                    console.log(findAOADIndex, aoad_val)
                    if (findAOADIndex !== -1) {
                        let aoad_new_val = [];
                        for (const aoadi of aoad_val.values()) {
                            if (aoadi === aoad_val[findAOADIndex]) {
                                continue;
                            }
                            aoad_new_val.push(aoadi);
                        }
                        aoad_elem.val(JSON.stringify(aoad_new_val));
                        aoad_val = aoad_new_val = [];
                    }
                }
                
                $item.remove(); 
            } else {
                alert('Failed to delete the item. Please try again.');
            }
        },
        error: function () {
            alert('An error occurred while deleting the item.');
        },
    });
});

$(document).on('click', '.delete-icon-dropdown-ps', function(){
    let $item = $(this).closest('li')
    const itemId = $item.data('id');
    // const type = $(this).hasClass('delete-icon-dropdown-ps') ? 'program' : 'event';
    // $item.remove();

    let is_ao_ad = $('#ao-ad-dropdown-view-modal').data('is_ao_ad');
    let event_id = sanitizeHtml($('#event_id').val(), { allowedAttributes:{}, allowedTags:[]});
    let url;

    if (page === 'zbt_summary') {
        if (is_ao_ad === 'ao') {
            url = '/socom/zbt_summary/eoc_summary/ao/dropdown/delete';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/zbt_summary/eoc_summary/ad/dropdown/delete';
        }
    } else if (page === 'issue') {
        if (is_ao_ad === 'ao') {
            url = '/socom/issue/eoc_summary/ao/dropdown/delete';
        } else if (is_ao_ad === 'ad') {
            url= '/socom/issue/eoc_summary/ad/dropdown/delete';
        }
    }

    if (url === undefined ||
        event_id.trim().length === 0
    ) {
        displayToastNotification('error', 'AO or AD Dropdown Delete Error');
        return false;
    }

    $.ajax({
        url,
        type: 'POST',
        data: { id: itemId, rhombus_token: rhombuscookie(), event_id: event_id },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                console.log("deletion sucessful");
                let elem_class = `.${is_ao_ad}-${event_id}-approvals`.replaceAll(" ", "_");
                let aoad_elem = $(elem_class),
                    aoad_val = JSON.parse(aoad_elem.val());
            
                if (aoad_val instanceof Array) {
                    const findAOADIndexFn = (element) => element?.ID == itemId;
                    const findAOADIndex = aoad_val.findIndex(findAOADIndexFn);
                    if (findAOADIndex !== -1) {
                        let aoad_new_val = [];
                        for (const aoadi of aoad_val.values()) {
                            if (aoadi === aoad_val[findAOADIndex]) {
                                continue;
                            }
                            aoad_new_val.push(aoadi);
                        }
                        aoad_elem.val(JSON.stringify(aoad_new_val));
                        aoad_val = aoad_new_val = [];
                    }
                }

                $item.remove(); 
            } else {
                alert('Failed to delete the item. Please try again.');
            }
        },
        error: function () {
            alert('An error occurred while deleting the item.');
        },
    });
});

if(!window._rb) window._rb = {};
window._rb = {
    homeDs,
    view_onchange,
    dropdown_onchange,
    initDatatable,
    addClassToEditedCell,
    dropdown_selection,
    initHistoricalDatatable,
    initEocSummaryDatatable,
    isSameProgramGrouping,
    initEocHistoricalDatatable,
    mergeRows,
    toggleSeriesVisibility,
    disable_hide_apply_filter_button,
    check_filter_limit,
    table_filter_onchange,
    onReady,
    isChecked,
    getDropdownSelectionByUserID,
    updateApprovalList,
    dropdown_all_view,
    update_program_filter,
    viewDropdownModal,
    updateCommentList,
    viewCommentModal,
    saveAOADComment,
    save_ao_ad_dropdown

}