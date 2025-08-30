"use strict";

let lastSelectedItemsMap = {
    'pom': [],
    'ass-area': [],
    'cs': [],
    'program': [],
    'resource_category': [],
    'execution-manager': [],
    'program-name': [],
    'eoc-code': [],
    'osd-pe': [],
}
let compareFlag = false;
const selectionTextOptions = {
    'false': 'Select All',
    'true': 'Deselect All'
}
let textColor = 'var(--cds-text-02, #525252)';
let pieChart = 'var(--cds-pie-01)'
let minMaxMap = {}
var overlay_loading_html = `
<div class="bx--loading-overlay">
<div data-loading class="bx--loading">
  <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
    <title>Loading</title>
    <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
  </svg>
</div>
</div>`;

const allFilterTypes = ["ass-area", "cs", "execution-manager", "program", "program-name", "eoc-code", "resource_category", "osd-pe"];
const defaultFilterTypes = ["ass-area", "cs", "program", "resource_category"];
let fullSeriesData= {}
let allCategories= {}

function get_pb_comparison_graph(id, data) { 

    fullSeriesData[id] = JSON.parse(JSON.stringify(data['data']));
    Highcharts.chart(id, {
        title: {
            text: `PB Comparison from ${data['categories'][0]} to ${data['categories'][data['categories'].length - 1]}`,
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
                pointStart: data[0],
                connectNulls: true
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

    let chart = $('#'+id).highcharts();
    allCategories[id]= chart.xAxis[0].categories;

}

function dropdown_onchange(id, type, row_id = null) {
    const allFiltersShown = all_filters_shown();
    let prevFilterWithinLimit;

    let input_object = {}
    switch(type) {
        case 'pom':
        case 'ass-area':
        case 'cs':
            dropdown_all_view(type, id);
            prevFilterWithinLimit = check_filter_limit(type, id, 5);
            if (allFiltersShown) {
                update_execution_manager_filter(id, prevFilterWithinLimit); // Populate Execution Manager filters
            } else {
                update_program_filter(id, prevFilterWithinLimit); // Populate Program Group filters
            }
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag);
            break;
        case 'execution-manager':
            dropdown_all_view(type, id);
            prevFilterWithinLimit = check_filter_limit(type, id, 5);
            if (allFiltersShown) {
                update_program_filter(id, prevFilterWithinLimit); // Populate Program Group filters
            }
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag);
            break;
        case 'program': // Program Group
            dropdown_all_view(type, id);
            prevFilterWithinLimit = check_filter_limit(type, id, 5);
            if (allFiltersShown) {
                update_program_name_filter(id, prevFilterWithinLimit); // Populate Program Name filters
            } else {
                update_resource_category_filter(id, prevFilterWithinLimit); // Populate Resource Category filters
            }
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag);
            break;
        case 'program-name':
            dropdown_all_view(type, id);
            prevFilterWithinLimit = check_filter_limit(type, id, 5);
            if (allFiltersShown) {
                update_eoc_code_filter(id, prevFilterWithinLimit); // Populate EOC Code filters
            }
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag);
            break;
        case 'eoc-code':
            dropdown_all_view(type, id);
            prevFilterWithinLimit = check_filter_limit(type, id, 5);
            if (allFiltersShown) {
                update_resource_category_filter(id, prevFilterWithinLimit); // Populate Resource Category filters
            }
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag);
            break;
        case 'resource_category':
            dropdown_all_view(type, id);
            prevFilterWithinLimit = check_filter_limit(type, id, 5);
            if (allFiltersShown) {
                update_osd_pe_filter(id, prevFilterWithinLimit); // Populate OSD PE filters
            }
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag);
            break;
        case 'osd-pe':
            dropdown_all_view(type, id);
            handle_disable_apply_filter_button(id);
            handle_disable_compare_button(id, compareFlag); 
            break;
        case 'filter': // onclick for 'Apply Filter'
            input_object = get_input_object(id);
            $('#apply-filter-loading').attr('hidden', false);
            $('#overlay-loader').html(overlay_loading_html);
            $('#pb-comparison-filter-compare').attr('disabled', false);

            $('#chart-2-container').addClass('d-none').removeClass('d-flex');
            $('#chart-1-container').addClass('w-100').removeClass('w-50');
            $('#list-1').empty();
            
            update_pb_comparison_graph(id, input_object, 'chart-1',function() {
                update_graph(id, $('#chart-1'));
                // reset toggle
                $('#pb-common-range-toggle-container').attr('hidden', true);
                $('#pb-common-range-toggle').prop('checked', false);
                
                //set chart min max
                minMaxMap['chart-1'] = {
                    'min': $('#chart-1').highcharts().yAxis[0].min,
                    'max': $('#chart-1').highcharts().yAxis[0].max
                };

                resetYearSlider(1)
                yearSlider('chart-1',1) 
            });

            compareFlag = true;
            break;

        case 'compare':
            input_object = get_input_object(id);
            $('#apply-filter-loading').attr('hidden', false);

            $('#chart-1-container').addClass('w-50').removeClass('w-100');
            $('#chart-1').highcharts().reflow();

            $('#chart-2-container').addClass('d-flex').removeClass('d-none');
            $('#list-2').empty();

            update_pb_comparison_graph(id, input_object, 'chart-2', function() {

                update_graph(id, $('#chart-2'));

                //set chart min max
                minMaxMap['chart-2'] = {
                    'min': $('#chart-2').highcharts().yAxis[0].min,
                    'max': $('#chart-2').highcharts().yAxis[0].max
                };
                $('#pb-common-range-toggle-container').attr('hidden', false);
                $('#pb-common-range-toggle').prop('checked', false).trigger('change');
                resetYearSlider(2)
                yearSlider('chart-2',2) 
            });

            $('#pb-comparison-filter-compare').attr('disabled', true);            
            break;
        default:
            break;
    }
}

function yearSlider(chart_id,id){
    $('#slider-container-'+id).show();
    
    let currChart= `#${chart_id}`

    let catLen= $(currChart).highcharts().xAxis[0].categories.length
    $("#year-slider-"+id).ionRangeSlider({
        grid: true,
        type:"double",
        min: $(currChart).highcharts().xAxis[0].categories[0],
        max: $(currChart).highcharts().xAxis[0].categories[catLen-1],
        from: $(currChart).highcharts().xAxis[0].categories[0],
        step: 1,
        prettify_enabled: false,
        drag_interval: true,
        grid_num: catLen-1,
        skin:"round",
        onChange: function (data) {

            let chart = $(currChart).highcharts();

            let fromVal =data.from;
            let toVal = data.to;
            let categories= allCategories[chart_id]
            let series= fullSeriesData[chart_id]

            let filteredCategories = categories.filter(function (year) {
                return year >= fromVal && year <= toVal;
            });

            let startIdx = categories.findIndex(function (year) {
                return year === fromVal;
            });
            let endIdx = categories.findIndex(function (year) {
                return year === toVal;
            }) + 1; // +1 to include the 'to' year

            let filteredSeries = series.map(ser => ({
                name: ser.name,
                data: ser.data.slice(startIdx, endIdx)
            }));

            chart.update({
                xAxis: {
                    categories: filteredCategories
                },
                series: filteredSeries,
                title: {
                    text: `PB Comparison from ${filteredCategories[0]} to ${filteredCategories[filteredCategories.length - 1]}`
                }
            }, true);
        },
    });
}

function resetYearSlider(id) {

    let sliderInstance = $("#year-slider-"+id).data("ionRangeSlider");

    if (sliderInstance) {
        sliderInstance.reset()
    }
}

function check_filter_limit(type, id, limit) {
    const dropdown_id = `${type}-${id}`;
    const $dropdown = $(`#${dropdown_id}`);

    const filterErrorId = `${type}-filter-error`;
    const $filterError = $(`#${filterErrorId}`);

    if ($dropdown.val().length > limit) {
        if ($filterError.length === 0) {
            const filterText = `filter${limit > 1 ? "s" : ""}`;
            $(`#${type}-dropdown`).append(
                `<div id="${filterErrorId}" class="alert alert-warning mb-0 mt-2" style="width: 16vw;">
                    You can only apply up to ${limit} ${filterText}. Please remove some ${filterText} to proceed.
                </div>`
            );
        }
        return false;
    } else {
        if ($filterError.length) {
            $filterError.remove();
        }
        return true;
    }
}

function update_graph(id, chartId) {
    $('#overlay-loader').html('');
    $('#apply-filter-loading').attr('hidden', true);

    let titleText = addElementsToList(id);

    if (chartId.highcharts() == undefined) {
        $('#budget-to-execution-filter-compare').attr('disabled', true);
    } else {
        chartId.highcharts().setTitle(null, { text: titleText.substring(0, titleText.length - 3)});
    }
}

function addElementsToList(id) {
    let titleText = "";
    const elements = getElementsForList(id);

    $.each(elements, function (index, elem) {
        titleText += index + ": " + elem + ",  ";
    });
    return titleText;
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

function update_pb_comparison_graph(id, input_object, chart_id, callback=null) {
    input_object = get_input_object(id);

    const allFiltersShown = all_filters_shown();

    const allFiltersSelected = allFiltersShown
        ? allFilterTypes.every(type => input_object[type])
        : defaultFilterTypes.every(type => input_object[type]);

    let input = {
        cs: input_object["cs"],
        'ass-area': input_object["ass-area"],
        program: input_object["program"],
        resource_category: input_object["resource_category"], 
    };

    if (allFiltersShown) {
        input['execution-manager'] = input_object['execution-manager'];
        input['program-name'] = input_object["program-name"];
        input['eoc-code'] = input_object["eoc-code"];
        input['osd-pe'] = input_object["osd-pe"];
    }

    if (allFiltersSelected) {
        $.post("/socom/pb_comparison/graph/update", { 
            ...input,
            rhombus_token: rhombuscookie() 
        }, function (data) {
            let graphData = data;
            if (!graphData) {
                $(`#chart-1`).html(
                    `<div class="d-flex w-100 p-2 justify-content-center">
                        <h2>Click Apply Filter to see data</h2>
                    </div>`
                )
            }
            showGraph(graphData, chart_id);
            
            if (callback) {
                callback();
            }
        })
    }
}

function showGraph(graphData, chart_id) {
    let dataYears = [], newData = [];
    for (let i in graphData.data) {
        const initialValue = 0
        dataYears[i] = graphData.data[i]['data'].reduce((accumulator, currentValue) => Number(accumulator) + Number(currentValue), initialValue,)
        
        let fy_year = parseInt(graphData.data[i]['name'].split(' ')[1]);
        if (graphData.dashed_line.includes(fy_year)) {
            graphData.data[i].dashStyle = 'dash'
        }

        if (dataYears[i] === 0) {
            delete graphData.data[i];
        } else {
            newData.push(graphData.data[i]);
        }
    }
    graphData.data = newData;
    const initialValue = 0;
    let allSum = dataYears.reduce((accumulator, currentValue) => Number(accumulator) + Number(currentValue), initialValue,)
    if (allSum === 0 || graphData.data.length === 0) {
        $(`#chart-1`).html(
            `<div class="d-flex w-100 p-2 justify-content-center">
                        <h2>Click Apply Filter to see data</h2>
                    </div>`
        );
        displayToastNotification('error', 'No data is available for selected filters. Please select new filters');
    } else {
        get_pb_comparison_graph(chart_id, graphData)
    }
}

async function update_resource_category_filter(id, prevFilterWithinLimit) {
    let input_object = get_input_object(id);

    const resourceCategoryButton = `#resource_category-${id}-selection`;
    $(resourceCategoryButton).attr('data-select-all', 'true');
    $(resourceCategoryButton).html(selectionTextOptions['false']);
    $(resourceCategoryButton).attr('disabled', true);

    if ($(`#resource_category-${id}`).val().length) {
        $(`#resource_category-${id}`).val(null).trigger('change')
    }

    const allFiltersShown = all_filters_shown();

    const previousFiltersSelected = allFiltersShown ? 
    input_object["ass-area"] && input_object["cs"] && input_object['execution-manager'] && 
    input_object['program'] && input_object['program-name'] && input_object['eoc-code'] :
    input_object["ass-area"] && input_object["cs"] && input_object["program"];

    let input = {
        cs: input_object["cs"],
        'ass-area': input_object["ass-area"],
        program: JSON.stringify(input_object["program"]),
    };

    if (allFiltersShown) {
        input['execution-manager'] = input_object['execution-manager'];
        input['program-name'] = JSON.stringify(input_object["program-name"]);
        input['eoc-code'] = JSON.stringify(input_object["eoc-code"]);
    }

    if (previousFiltersSelected && prevFilterWithinLimit) {
        try {
            $(`#resource_category-${id}`).attr('disabled', true);
            $('#resource_category-loading').attr('hidden', false);
            const data = await $.post("/socom/pb_comparison/filter/resource_category/update", {
                rhombus_token: rhombuscookie(),
                ...input,
            });

            let resourceCategoryList = data['data'];
            let resourceCategoryOptions = '';
            resourceCategoryList.forEach( v => {
                resourceCategoryOptions += `<option value="${v['RESOURCE_CATEGORY_CODE']}">${v['RESOURCE_CATEGORY_CODE']}</option>`;
            });

            $(`#resource_category-${id}`).select2('destroy')
            $(`#resource_category-${id}`).remove();

            $(`#resource_category_dropdown`).append(
                `<select 
                id="resource_category-${id}" 
                type="resource_category" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange(1, 'resource_category')"
                disabled
            >
                <option option="ALL">ALL</option>
                ${resourceCategoryOptions}
            </select>`)

            $(`#resource_category-${id}`).select2({
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

            $(`#resource_category-${id}`).attr('disabled', false);
            $(resourceCategoryButton).attr('disabled', false);
        } catch (error) {
            console.error('Error updating Resource Category filter', error);
        } finally {
            $('#resource_category-loading').attr('hidden', true);
        }  
    }
    else {
        $(`#resource_category-${id}`).attr('disabled', true);
    }
}

async function update_program_filter(id, prevFilterWithinLimit) {
    let input_object = get_input_object(id);
    
    const programSelectionButton = `#program-${id}-selection`;
    $(programSelectionButton).attr('data-select-all', 'true');
    $(programSelectionButton).html(selectionTextOptions['false']);
    $(programSelectionButton).attr('disabled', true);

    if ($(`#program-${id}`).val().length) {
        $(`#program-${id}`).val(null).trigger('change')
    }

    const allFiltersShown = all_filters_shown();

    const previousFiltersSelected = allFiltersShown ? 
    input_object["ass-area"] && input_object["cs"] && input_object['execution-manager'] :
    input_object["ass-area"] && input_object["cs"];

    let input = {
        section: 'pb_comparison',
        cs: input_object["cs"],
        'ass-area': input_object["ass-area"],
    };

    if (allFiltersShown) {
        input['execution-manager'] = input_object['execution-manager'];
    }

    if (previousFiltersSelected && prevFilterWithinLimit) {
        try {
            $(`#program-${id}`).attr('disabled', true);
            $('#program-loading').attr('hidden', false);
            const data = await $.post("/socom/pb_comparison/filter/program/update", {
                rhombus_token: rhombuscookie(),
                ...input,
            })
            let programList = data['data'];
            let programOptions = '';
            programList.forEach( v => {
                programOptions += `<option value="${v['PROGRAM_GROUP']}">${v['PROGRAM_GROUP']}</option>`;
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
                width: '17vw'
            })
            .on('change.select2', function() {
                    var dropdown = $(this).siblings('span.select2-container');
                    if (dropdown.height() > 100) {
                        dropdown.css('max-height', '100px');
                        dropdown.css('overflow-y', 'auto');
                    }
            })

            $(`#program-${id}`).attr('disabled', false);
            $(programSelectionButton).attr('disabled', false);
        } catch (error) {
            console.error('Error updating Program filter', error);
        } finally {
            $('#program-loading').attr('hidden', true);
        }
    }
    else {
        $(`#program-${id}`).attr('disabled', true);
    }
}

async function update_execution_manager_filter(id, prevFilterWithinLimit) {
    let input_object = get_input_object(id);
    
    const executionManagerSelectionButton = `#execution-manager-${id}-selection`;
    $(executionManagerSelectionButton).attr('data-select-all', 'true');
    $(executionManagerSelectionButton).html(selectionTextOptions['false']);
    $(executionManagerSelectionButton).attr('disabled', true);

    if ($(`#execution-manager-${id}`).val().length) {
        $(`#execution-manager-${id}`).val(null).trigger('change')
    }

    const previousFiltersSelected = input_object["ass-area"] && input_object["cs"];

    if (previousFiltersSelected && prevFilterWithinLimit) {
        try {
            $(`#execution-manager-${id}`).attr('disabled', true);
            $('#execution-manager-loading').attr('hidden', false);
    
            const data = await $.post("/socom/pb_comparison/filter/execution_manager/update", {
                rhombus_token: rhombuscookie(),
                cs: input_object["cs"],
                'ass-area': input_object["ass-area"]
            })

            let executionManagerList = data['data'];
            let executionManagerOptions = '';
            executionManagerList.forEach( v => {
                executionManagerOptions += `<option value="${v['EXECUTION_MANAGER_CODE']}">${v['EXECUTION_MANAGER_CODE']}</option>`;
            });

            $(`#execution-manager-${id}`).select2('destroy')
            $(`#execution-manager-${id}`).remove();

            $(`#execution-manager-dropdown`).append(
                `<select 
                id="execution-manager-${id}" 
                type="execution-manager" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange(1, 'execution-manager')"
            >
                <option option="ALL">ALL</option>
                ${executionManagerOptions}
            </select>`)

            $(`#execution-manager-${id}`).select2({
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

            $(`#execution-manager-${id}`).attr('disabled', false);
            $(executionManagerSelectionButton).attr('disabled', false);
        } catch (error) {
            console.error('Error updating Execution Manager filter', error);
        } finally {
            $('#execution-manager-loading').attr('hidden', true);
        }
    } else {
        $(`#execution-manager-${id}`).attr('disabled', true);
    }
}

async function update_program_name_filter(id, prevFilterWithinLimit) {
    let input_object = get_input_object(id);
    
    const programNameSelectionButton = `#program-name-${id}-selection`;
    $(programNameSelectionButton).attr('data-select-all', 'true');
    $(programNameSelectionButton).html(selectionTextOptions['false']);
    $(programNameSelectionButton).attr('disabled', true);

    if ($(`#program-name-${id}`).val().length) {
        $(`#program-name-${id}`).val(null).trigger('change')
    }

    const previousFiltersSelected = 
        input_object["ass-area"] && input_object["cs"] && input_object["execution-manager"] && input_object["program"];

    if (previousFiltersSelected && prevFilterWithinLimit) {
        try {
            $(`#program-name-${id}`).attr('disabled', true);
            $('#program-name-loading').attr('hidden', false);
            const data = await $.post("/socom/pb_comparison/filter/program_name/update", {
                rhombus_token: rhombuscookie(),
                cs: input_object["cs"],
                'ass-area': input_object["ass-area"],
                'execution-manager': input_object["execution-manager"],
                program: JSON.stringify(input_object["program"]),
            });

            let programNameList = data['data'];
            let programNameOptions = '';
            programNameList.forEach( v => {
                programNameOptions += `<option value="${v['PROGRAM_NAME']}">${v['PROGRAM_NAME']}</option>`;
            });

            $(`#program-name-${id}`).select2('destroy')
            $(`#program-name-${id}`).remove();

            $(`#program-name-dropdown`).append(
                `<select 
                id="program-name-${id}" 
                type="program-name" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange(1, 'program-name')"
            >
                <option option="ALL">ALL</option>
                ${programNameOptions}
            </select>`)

            $(`#program-name-${id}`).select2({
                placeholder: "Select an option",
                width: '17vw'
            })
            .on('change.select2', function() {
                    var dropdown = $(this).siblings('span.select2-container');
                    if (dropdown.height() > 100) {
                        dropdown.css('max-height', '100px');
                        dropdown.css('overflow-y', 'auto');
                    }
            });

            $(`#program-name-${id}`).attr('disabled', false);
            $(programNameSelectionButton).attr('disabled', false);
        } catch (error) {
            console.error('Error updating Program Name filter', error);
        } finally {
            $('#program-name-loading').attr('hidden', true);
        }
    } else {
        $(`#program-name-${id}`).attr('disabled', true);
    }
}

async function update_eoc_code_filter(id, prevFilterWithinLimit) {
    let input_object = get_input_object(id);
    
    const eocCodeSelectionButton = `#eoc-code-${id}-selection`;
    $(eocCodeSelectionButton).attr('data-select-all', 'true');
    $(eocCodeSelectionButton).html(selectionTextOptions['false']);
    $(eocCodeSelectionButton).attr('disabled', true);

    if ($(`#eoc-code-${id}`).val().length) {
        $(`#eoc-code-${id}`).val(null).trigger('change')
    }

    const previousFiltersSelected = 
        input_object["ass-area"] && input_object["cs"] && input_object["execution-manager"] && 
        input_object["program"] && input_object["program-name"];

    if (previousFiltersSelected && prevFilterWithinLimit) {
        try {
            $(`#eoc-code-${id}`).attr('disabled', true);
            $('#eoc-code-loading').attr('hidden', false);
    
            const data = await $.post("/socom/pb_comparison/filter/eoc_code/update", {
                rhombus_token: rhombuscookie(),
                cs: input_object["cs"],
                'ass-area': input_object["ass-area"],
                'execution-manager': input_object["execution-manager"],
                program: JSON.stringify(input_object["program"]),
                'program-name': JSON.stringify(input_object["program-name"]),
            })

            let eocCodeList = data['data'];
            let eocCodeOptions = '';
            eocCodeList.forEach( v => {
                eocCodeOptions += `<option value="${v['EOC_CODE']}">${v['EOC_CODE']}</option>`;
            });

            $(`#eoc-code-${id}`).select2('destroy')
            $(`#eoc-code-${id}`).remove();

            $(`#eoc-code-dropdown`).append(
                `<select 
                id="eoc-code-${id}" 
                type="eoc-code" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange(1, 'eoc-code')"
            >
                <option option="ALL">ALL</option>
                ${eocCodeOptions}
            </select>`)

            $(`#eoc-code-${id}`).select2({
                placeholder: "Select an option",
                width: '17vw'
            })
            .on('change.select2', function() {
                    var dropdown = $(this).siblings('span.select2-container');
                    if (dropdown.height() > 100) {
                        dropdown.css('max-height', '100px');
                        dropdown.css('overflow-y', 'auto');
                    }
            });

            $(`#eoc-code-${id}`).attr('disabled', false);
            $(eocCodeSelectionButton).attr('disabled', false);
        } catch (error) {
            console.error('Error updating EOC Code filter', error);
        } finally {
            $('#eoc-code-loading').attr('hidden', true);
        }
    } else {
        $(`#eoc-code-${id}`).attr('disabled', true);
    }
}

async function update_osd_pe_filter(id, prevFilterWithinLimit) {
    let input_object = get_input_object(id);
    
    const osdPeSelectionButton = `#osd-pe-${id}-selection`;
    $(osdPeSelectionButton).attr('data-select-all', 'true');
    $(osdPeSelectionButton).html(selectionTextOptions['false']);
    $(osdPeSelectionButton).attr('disabled', true);

    if ($(`#osd-pe-${id}`).val().length) {
        $(`#osd-pe-${id}`).val(null).trigger('change')
    }

    const previousFiltersSelected = input_object["ass-area"] && input_object["cs"] && 
    input_object["execution-manager"] && input_object["program"] && 
    input_object["program-name"] && input_object["eoc-code"] && input_object["resource_category"];

    if (previousFiltersSelected && prevFilterWithinLimit) {
        try {
            $(`#osd-pe-${id}`).attr('disabled', true);
            $('#osd-pe-loading').attr('hidden', false);
            const data = await $.post("/socom/pb_comparison/filter/osd_pe/update", {
                rhombus_token: rhombuscookie(),
                cs: input_object["cs"],
                'ass-area': input_object["ass-area"],
                'execution-manager': input_object["execution-manager"],
                program: JSON.stringify(input_object["program"]),
                'program-name': JSON.stringify(input_object["program-name"]),
                'eoc-code': JSON.stringify(input_object["eoc-code"]),
                'resource_category': input_object["resource_category"],
            })

            let osdPeList = data['data'];
            let osdPeOptions = '';
            osdPeList.forEach( v => {
                osdPeOptions += `<option value="${v['OSD_PROGRAM_ELEMENT_CODE']}">${v['OSD_PROGRAM_ELEMENT_CODE']}</option>`;
            });
    
            $(`#osd-pe-${id}`).select2('destroy')
            $(`#osd-pe-${id}`).remove();
    
            $(`#osd-pe-dropdown`).append(
                `<select 
                id="osd-pe-${id}" 
                type="osd-pe" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange(1, 'osd-pe')"
            >
                <option option="ALL">ALL</option>
                ${osdPeOptions}
            </select>`)
    
            $(`#osd-pe-${id}`).select2({
                placeholder: "Select an option",
                width: '17vw'
            })
            .on('change.select2', function() {
                    var dropdown = $(this).siblings('span.select2-container');
                    if (dropdown.height() > 100) {
                        dropdown.css('max-height', '100px');
                        dropdown.css('overflow-y', 'auto');
                    }
            });
    
            $(`#osd-pe-${id}`).attr('disabled', false);
            $(osdPeSelectionButton).attr('disabled', false);
        } catch (error) {
            console.error('Error updating OSD PE filter', error);
        } finally {
            $('#osd-pe-loading').attr('hidden', true);
        }
    } else {
        $(`#osd-pe-${id}`).attr('disabled', true);
    }
}

function handle_disable_apply_filter_button(id) {
    let input_object = get_input_object(id);
    const allFiltersShown = all_filters_shown();

    const allFiltersSelected = allFiltersShown
        ? allFilterTypes.every(type => input_object[type])
        : defaultFilterTypes.every(type => input_object[type]);

    const prevFilterWithinLimit = allFiltersShown
        ? allFilterTypes.every(type => check_filter_limit(type, id, 5))
        : defaultFilterTypes.every(type => check_filter_limit(type, id, 5));

    if (allFiltersSelected && prevFilterWithinLimit) {
        $('#pb-comparison-filter').attr('disabled', false);
    }
    else {
        $('#pb-comparison-filter').attr('disabled', true);
    }
}

function handle_disable_compare_button(id, compareFlag) {
    if (compareFlag) {
        let input_object = get_input_object(id);

        const allFiltersShown = all_filters_shown();
        const allFiltersSelected = allFiltersShown
            ? allFilterTypes.every(key => input_object[key])
            : defaultFilterTypes.every(key => input_object[key]);

        const prevFilterWithinLimit = allFiltersShown
            ? allFilterTypes.every(type => check_filter_limit(type, id, 5))
            : defaultFilterTypes.every(type => check_filter_limit(type, id, 5));

        if (allFiltersSelected && prevFilterWithinLimit) {
            $('#pb-comparison-filter-compare').attr('disabled', false);
        }
        else {
            $('#pb-comparison-filter-compare').attr('disabled', true);
        }
    }
}

function get_input_object(id) {
    let input_object = {};

    if ($("#ass-area-" + id).val() != "" && $("#ass-area-" + id).val() != null) {
        input_object["ass-area"] = fetch_all_inputs(`#ass-area-${id}`)
    }

    if ($('#cs-' + id).val() != "" && $('#cs-' + id).val() != null) {
        input_object["cs"] = fetch_all_inputs(`#cs-${id}`)
    }

    if ($('#program-' + id).val() != "" && $('#program-' + id).val() != null) {
        input_object["program"] = $('#program-' + id).val()
    }

    if ($('#resource_category-' + id).val() != "" && $('#resource_category-' + id).val() != null) {
        input_object["resource_category"] = fetch_all_inputs(`#resource_category-${id}`)
    }

    if ($('#execution-manager-' + id).val() != "" && $('#execution-manager-' + id).val() != null) {
        input_object["execution-manager"] = fetch_all_inputs(`#execution-manager-${id}`)
    }

    if ($('#program-name-' + id).val() != "" && $('#program-name-' + id).val() != null) {
        input_object["program-name"] = $(`#program-name-${id}`).val();
    }

    if ($('#eoc-code-' + id).val() != "" && $('#eoc-code-' + id).val() != null) {
        input_object["eoc-code"] = $(`#eoc-code-${id}`).val();
    }

    if ($('#osd-pe-' + id).val() != "" && $('#osd-pe-' + id).val() != null) {
        input_object["osd-pe"] = $(`#osd-pe-${id}`).val();
    }

    return input_object;
}

function getElementsForList(id) {
    let input_object = {};
    let output = [];

    if ($("#ass-area-" + id).val() != "" && $("#ass-area-" + id).val() != null) {
        output = fetch_singular_inputs(`#ass-area-${id}`);
        if (output != null)
            input_object["Assessment Area"] = output;
    }

    if ($('#cs-' + id).val() != "" && $('#cs-' + id).val() != null) {
        output = fetch_singular_inputs(`#cs-${id}`);
        if (output != null)
            input_object["Capability Sponsor"] = output
    }

    if ($('#execution-manager-' + id).val() != "" && $('#execution-manager-' + id).val() != null) {
        output = fetch_singular_inputs(`#execution-manager-${id}`);
        if (output != null)
            input_object["Execution Manager"] = output
    }

    if ($('#program-' + id).val() != "" && $('#program-' + id).val() != null) {
        output = fetch_singular_inputs(`#program-${id}`);
        if (output != null)
            input_object["Program Group"] = output
    }

    if ($('#program-name-' + id).val() != "" && $('#program-name-' + id).val() != null) {
        output = fetch_singular_inputs(`#program-name-${id}`);
        if (output != null)
            input_object["Program Name"] = output
    }

    if ($('#eoc-code-' + id).val() != "" && $('#eoc-code-' + id).val() != null) {
        output = fetch_singular_inputs(`#eoc-code-${id}`);
        if (output != null)
            input_object["EOC Code"] = output
    }

    if ($('#resource_category-' + id).val() != "" && $('#resource_category-' + id).val() != null) {
        output = fetch_singular_inputs(`#resource_category-${id}`);
        if (output != null)
            input_object["Appropriation"] = output
    }

    if ($('#osd-pe-' + id).val() != "" && $('#osd-pe-' + id).val() != null) {
        output = fetch_singular_inputs(`#osd-pe-${id}`);
        if (output != null)
            input_object["OSD PE"] = output
    }

    return input_object;
}

function fetch_singular_inputs(id) {
    let select2val =  $(id).val();
    if(!select2val.includes('ALL') && select2val.length > 0){
        return select2val;
    }
    return null;
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

function dropdown_selection(target) {
    const dropdown = $(target);
    const selectionButton = `${target}-selection`;
    const isSelectAll = $(selectionButton).attr('data-select-all') === 'true';
    const stringifyIsSelectAll = isSelectAll.toString();
    const selectionTextOptions = {
        'false': 'Select All',
        'true': 'Deselect All'
    }
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

function common_range_toggle() {
    if($('#pb-common-range-toggle').prop('checked')) {
        let alignedMin = Math.min(
            $('#chart-1').highcharts().yAxis[0].min,
            $('#chart-2').highcharts().yAxis[0].min
        )
        let alignedMax = Math.max(
            $('#chart-1').highcharts().yAxis[0].max,
            $('#chart-2').highcharts().yAxis[0].max
        )

        // Update chart1 y-axis
        $('#chart-1').highcharts().yAxis[0].update({
            min: alignedMin,
            max: alignedMax
        });

        // Update chart2 y-axis
        $('#chart-2').highcharts().yAxis[0].update({
            min: alignedMin,
            max: alignedMax
        });
    } 
    else {
        // Revert to original y-axis ranges
        // Update chart1 y-axis
        $('#chart-1').highcharts().yAxis[0].update({
            min: minMaxMap['chart-1'].min,
            max: minMaxMap['chart-1'].max
        });

        // Update chart2 y-axis
        $('#chart-2').highcharts().yAxis[0].update({
            min: minMaxMap['chart-2'].min,
            max: minMaxMap['chart-2'].max
        });
    }
}

function toggle_all_filters(id) {
    const allFiltersBtn = $('#all-filters-button');
    const filterContainer = $('#filter-container');
    const additionalFilterTypes = [
        'execution-manager', 'program-name', 'eoc-code', 'osd-pe',
    ];
    if (filterContainer.hasClass('expanded')) {
        // Hide additional filters
        additionalFilterTypes.forEach(type => {
            $(`#${type}-dropdown`).attr('hidden', true);
        });
        filterContainer.removeClass('expanded');
        allFiltersBtn.text('Show All Filters');
    } else {
        // Show additional filters
        additionalFilterTypes.forEach(type => {
            $(`#${type}-dropdown`).removeAttr('hidden');
        });
        filterContainer.addClass('expanded');
        allFiltersBtn.text('Hide All Filters');
    }

    ['execution-manager', 'program', 'program-name', 'eoc-code', 
    'resource_category', 'osd-pe'].forEach(type => {
        disable_filter(type, id);
        clear_filter(type, id);
    });

    ['cs', 'ass-area'].forEach(type => {
        const filter_value = $(`#${type}-${id}`).val();
        clear_filter(type, id);
        $(`#${type}-${id}`).val(filter_value).trigger('change');
    });
}

function all_filters_shown() {
    const filterContainer = $('#filter-container');
    return filterContainer.hasClass('expanded');
}

function clear_filter(type, id) {
    if ($(`#${type}-${id}`).val().length) {
        $(`#${type}-${id}`).val(null).trigger('change');
    }
}

function disable_filter(type, id) {
    const selectionButton = `#${type}-${id}-selection`;
    $(selectionButton).attr('data-select-all', 'true');
    $(selectionButton).html(selectionTextOptions['false']);
    $(selectionButton).attr('disabled', true);

    const selectionFilter = `#${type}-${id}`;

    $(selectionFilter).attr('disabled', true);
}

if (!window._rb) window._rb = {};
window._rb.get_pb_comparison_graph = get_pb_comparison_graph;
window._rb.dropdown_onchange = dropdown_onchange;
window._rb.update_pb_comparison_graph = update_pb_comparison_graph;
window._rb.update_program_filter = update_program_filter;
window._rb.handle_disable_apply_filter_button = handle_disable_apply_filter_button;
window._rb.get_input_object = get_input_object;
window._rb.dropdown_selection = dropdown_selection;