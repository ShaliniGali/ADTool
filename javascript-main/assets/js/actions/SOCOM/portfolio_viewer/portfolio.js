"use strict";



var overlay_loading_html = `
<div class="bx--loading-overlay">
<div data-loading class="bx--loading">
  <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
    <title>Loading</title>
    <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
  </svg>
</div>
</div>`;

let compareProgramsTable;
let milestoneRequirementsTable;
const amsDatalimit = 5;
const amsDataStringlimit = 150;
let amsDataList = [];
let availableComponentsMap = [];
let subCategoryShowing = [];
let dropdownData = {};
let categoryMap = {};
let minMaxMap = {}

let requirementsCellStyle = {
    'COMPLETE': 'green-cell',
    'IN PROGRESS': 'ember-cell',
    'OVERDUE': 'red-cell',
};

// called in filter_view.php
function onReady() {
    fetchChartDataAmount();
    fetchChartDataTopProgram();
    updateAllBudgetLineGraphs('budget-trend-overview');
    fetchChartDataSelectedProgram('budget-trend-overview');
    $('input[name=dollar]').on('change', function (e) {
        handleDollarRadioSelect(e.target.value);
    });
    $('#toggle-pb-lines').on('change', function () {
        updateAllBudgetLineGraphs('budget-trend-overview');
    });
}

function updateFieldingTableOnChange() {
    updateFieldingCompmonentsDropdown(() => {
        fetchFieldingTableData();
    });
}

function fetchBudgetTrendOverviewGraph(input_object, graphIdx = 1) {
    return new Promise((resolve, reject) => {
        $.post(`/portfolio/budget_trend_overview/graph/update`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (response) {
                let formattedData = [];
                let categories = [];

                const pbYears = Array.isArray(response) && response.length > 0
                    ? Object.keys(response[0]).filter(key => key !== 'FISCAL_YEAR')
                    : [];

                pbYears.forEach(pbYear => {
                    formattedData.push({ name: pbYear, data: [] });
                });

                response.sort((a, b) => a.FISCAL_YEAR - b.FISCAL_YEAR);
                response.forEach(entry => {
                    categories.push(entry.FISCAL_YEAR);
                    pbYears.forEach((pbYear, index) => {
                        const value = entry[pbYear] !== 0 ? Math.round(entry[pbYear]) : null;
                        formattedData[index].data.push(value);
                    });
                });

                const formattedResponse = {
                    data: formattedData,
                    categories,
                    type: 'graph',
                    idx: graphIdx
                };

                resolve(formattedResponse);
            })
            .fail((error) => {
                reject(error);
            });
    });
}

function fetchFinalEnactedBudgetGraph(input_object, graphIdx = 1) {
    return new Promise((resolve, reject) => {
        $.post(`/portfolio/final_enacted_budget/graph/update`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (response) {
                let formattedData = [{ name: 'Enacted', data: [], color: '#000' }, { name: 'Execution', data: [], color: '#008c3a' }];
                let categories = [];

                response.sort((a, b) => a.FISCAL_YEAR - b.FISCAL_YEAR);
                response.forEach(entry => {
                    categories.push(entry.FISCAL_YEAR);

                    const enactedVal = entry['SUM_ENT'] !== 0 ? Math.round(entry['SUM_ENT']) : null;
                    const executionVal = entry['SUM_ACTUALS'] !== 0 ? Math.round(entry['SUM_ACTUALS']) : null;
                    formattedData[0].data.push(enactedVal);
                    formattedData[1].data.push(executionVal);
                });

                const formattedResponse = {
                    data: formattedData,
                    categories,
                    type: 'graph',
                    idx: graphIdx
                };

                resolve(formattedResponse);
            })
            .fail((error) => {
                reject(error);
            });
    });
}

function fetchExecutionGraph(input_object) {
    return new Promise((resolve, reject) => {
        $.post(`/portfolio/execution/graph/update`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (response) {
                let formattedData = [{ name: 'Enacted', data: [], color: '#000' }];
                let categories = [];

                response.forEach(entry => {
                    categories.push(entry.FISCAL_YEAR);

                    const value = entry['SUM_ACTUALS'] !== 0 ? entry['SUM_ACTUALS'] : null;
                    formattedData[0].data.push(value);
                });

                const formattedResponse = {
                    data: formattedData,
                    categories,
                };

                resolve(formattedResponse);
            })
            .fail((error) => {
                reject(error);
            });
    });
}

function updateAllBudgetLineGraphs(tabType) {
    let input_object = getSeletedFilterOptions(tabType);
    $('#overlay-loader').html(overlay_loading_html);

    const showPB = $('#toggle-pb-lines').is(':checked');

    let promises = [];
    if (showPB) {
        promises.push(fetchBudgetTrendOverviewGraph(input_object));
    }
    promises.push(fetchFinalEnactedBudgetGraph(input_object));

    Promise.allSettled(promises)
        .then(results => {
            let graphData = {
                categories: [],
                data: [],
                title: '',
                plotLines: []
            }

            results.forEach((result, index) => {
                const { status, value } = result;
                if (status === 'fulfilled') {
                    const allCategories = [...graphData.categories, ...value.categories];
                    let categoriesSet = new Set(allCategories);
                    graphData['categories'] = [...categoriesSet];
                    graphData['data'] = [...graphData.data, ...value.data];
                } else {
                    console.error(`Promise ${index + 1} rejected with reason:`, result.reason);
                }
            });
            let title = `Budgeting Trends ${graphData['categories'][0]} - ${graphData['categories'][graphData['categories'].length - 1]}`;
            $(`#${tabType}-chart-title`).text(title);


            plotGraph(`${tabType}-line-plot`, graphData);
            $(`#${tabType}-line-plot-disclaimer`).text('All $ amounts are in $K');
        })
        .finally(() => {
            $('#overlay-loader').html('');
        });
}

function plotGraph(id, data) {
    return Highcharts.chart(id, {
        title: {
            text: data.title,
        },
        xAxis: {
            categories: data['categories'],
            plotLines: data.plotLines
        },
        yAxis: {
            title: {
                text: data.title_y !== undefined ? data.title_y : 'Dollars (Thousands)'
            },
            labels: {
                formatter: function () {
                    // Custom formatting logic here
                    if (data.title_y == undefined) {
                        return '$' + Highcharts.numberFormat(this.value, 0, '.', ',');
                    }
                    else {
                        return Highcharts.numberFormat(this.value, 0, '.', ',');
                    }
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
        credits: {
            enabled: false
        },
        exporting: {
            enabled: false
        },
        subtitle: {
            text: data['subtitle'] !== undefined ? data['subtitle'] : ''
        },
    });
}

function plotChart(id, data) {
    Highcharts.chart(id, {
        chart: {
            type: 'column'
        },
        title: {
            text: data.title,
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
        series: data['data'],
        credits: {
            enabled: false
        },
        exporting: {
            enabled: false
        }
    });
}

function applyFilters(type) {
    if (type === 'budget-trend-overview') {
        updateAllBudgetLineGraphs(type);
        updateProgramGroupDropdown(type);
        // fetchChartDataAmount();
        fetchChartDataTopProgram();
    }
}

function getInputObject(type) {
    let input_object = {};
    if (type === 'budget-trend-overview') {
        input_object = getSeletedFilterOptions(type);
    }

    // get program group dropdown here
    input_object['PROGRAM_GROUP'] = get_dropdown_filter_input(type, 'PROGRAM_GROUP')['PROGRAM_GROUP'];

    return input_object;
}

function getSeletedFilterOptions(type) {
    let selectedOptions = {};
    $(`#${type}-checkbox-wrapper [id^="${type}-child-"]`).each(function () {
        let selectType = $(this).data('select-type');
        if ($(this).data('select-type') != undefined) {
            let selectedValues = $(this).find('.pcar-title:checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectType == 'APPROPRIATION') {
                selectedOptions['RESOURCE_CATEGORY'] = selectedValues;
            }
            else if (selectType == 'ASSESSMENT AREA CODE') {
                selectedOptions['ASSESSMENT_AREA_CODE'] = selectedValues;
            }
            else if (selectedOptions['CAPABILITY_SPONSOR_CODE'] !== undefined) {
                selectedOptions['CAPABILITY_SPONSOR_CODE'] = selectedOptions['CAPABILITY_SPONSOR_CODE'].concat(selectedValues);
            }
            else {
                selectedOptions['CAPABILITY_SPONSOR_CODE'] = selectedValues;
            }
        }
    })

    $(`#${type}-checkbox-wrapper [id^="${type}-${type}"]`).each(function () {
        let selectType = $(this).data('select-type');
        if ($(this).data('select-type') != undefined) {
            if ($(this).is(':checked') && !subCategoryShowing.includes(selectType)) {
                let category = selectType.replace(/,/g, "_");
                selectedOptions['CAPABILITY_SPONSOR_CODE'] = selectedOptions['CAPABILITY_SPONSOR_CODE'].concat(categoryMap[category]);
            }
        }
    });

    selectedOptions['INFLATION_ADJ'] = $('input[name=dollar]:checked').val() === 'constant-fy' ? false : true;
    return selectedOptions;
}

function handleDollarRadioSelect(value) {
    updateAllBudgetLineGraphs('budget-trend-overview');
}

function fetchChartDataAmount() {
    let filterData = getInputObject('budget-trend-overview');

    $.ajax({
        url: '/portfolio/amount/chart/update',
        type: 'POST',
        data: { ...filterData, rhombus_token: rhombuscookie() },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                renderStackedBarChart('bar-chart-budget-authority', reverseSeries(response.data), 'Amount By Appropriation');
            } else {
                console.error('Failed to fetch chart data:', response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function fetchChartDataTopProgram() {
    let filterData = getSeletedFilterOptions('budget-trend-overview');

    $.ajax({
        url: '/portfolio/top_program/chart/update',
        type: 'POST',
        data: { ...filterData, rhombus_token: rhombuscookie() },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                renderStackedBarChart('bar-chart-top-program', reverseSeries(response.data), 'Top 10 Program Groups');
            } else {
                console.error('Failed to fetch chart data:', response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function fetchChartDataSelectedProgram(type, resource_category = null) {
    let filterData = getInputObject(type);

    if (resource_category !== null) {
        filterData['RESOURCE_CATEGORY'] = resource_category;
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/portfolio/selected_program/chart/update',
            type: 'POST',
            data: { ...filterData, rhombus_token: rhombuscookie() },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    response.type = 'chart';
                    resolve(response);
                    updateChartSelectedProgram(type, response.data);
                } else {
                    console.error('Failed to fetch chart data:', response.error);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        })
    });
}

function updateChartSelectedProgram(type, chartData) {
    if (!chartData.chart3) {
        console.error("Missing chart data:", chartData);
        return;
    }
    renderStackedBarChart(`${type}-selected-program-chart`, reverseSeries(chartData.chart3), 'Selected Program Groups', type);
}

function reverseSeries(chartData) {
    return {
        categories: chartData.categories,
        series: formatSeriesNames(chartData.series.reverse())
    };
}

function formatSeriesNames(series) {
    return series.map(s => ({
        ...s,
        name: `FY${s.name}` // Prepend "FY" to each year
    }));
}

function renderStackedBarChart(containerId, chartData, title, tabType = null) {
   Highcharts.chart(containerId, {
    chart: {
        type: 'bar'
    },
    title: {
        text: null 
    },
    xAxis: {
        categories: chartData.categories,
    },
    yAxis: {
        min: 0,
        title: {
            text: ''
        },
        stackLabels: {
            enabled: true,
            formatter: function () {
                return formatLargeNumber(this.total);
            }
        }
    },
    legend: {
        reversed: true
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
                        if (`${tabType}-selected-program-chart` == containerId ||
                            `bar-chart-top-program` == containerId
                        ) {
                            showProgramExecutionDrilldown(this.category);
                        }
                    }
                }
            }
        }
    },
    series: chartData.series,
    credits: {
        enabled: false
    },
    exporting: {
        enabled: false
    }
});

}

function formatLargeNumber(value) {
    if (value >= 1_000_000_000) {
        return `$${(value / 1_000_000_000).toFixed(1)}B`;
    } else if (value >= 1_000_000) {
        return `$${(value / 1_000_000).toFixed(1)}M`;
    } else if (value >= 1_000) {
        return `$${(value / 1_000).toFixed(1)}K`;
    }
    return `$${value}`;
}

function updateProgramExecutionDrillDownCharts(programGroup) {
    let input_object = {}
    input_object['PROGRAM_GROUP'] = [programGroup];
    $('#overlay-loader').html(overlay_loading_html);

    Promise.allSettled([
        fetchFundingGraphDropdown(input_object),
        fetchFieldingTableDropdown(input_object),
        fetchProgramExecutionDrilldownFundingGraph(input_object),
        fetchProgramExecutionDrilldownAMSGraph(input_object),
        fetchProgramExecutionDrilldownMetadata(input_object),
        fetchProgramExecutionDrilldownMilestone(input_object, 'get')
    ])
        .then(results => {

            results.forEach((result, index) => {
                const { status, value } = result;

                if (status === 'fulfilled') {
                    let graphData = {};
                    switch (value.type) {
                        case 'funding':
                            graphData = {
                                categories: [],
                                data: [],
                                title: '',
                                plotLines: [{
                                    color: 'black',
                                    dashStyle: 'dash',
                                    value: value.latest_pb_year_index,
                                    width: 2
                                }]
                            }

                            graphData['categories'] = value.categories;
                            graphData['data'] = value.data;
                            graphData.title = `${programGroup} - Funding Line Graph`;
                            graphData.subtitle = `ACTUALS from PPBES-MIS shown left of vertical dotted line 
                        (through ${value.latest_pb_year - 1}). PB${value.latest_pb_year} FYDP right of vertical dashed 
                        line (${value.latest_pb_year} through ${value.latest_pb_year + 4}).`

                            plotGraph(`program-execution-drilldown-line-plot`, graphData);
                            break;
                        case 'ams':
                            graphData = {
                                categories: [],
                                data: [],
                                title: ''
                            }

                            graphData['categories'] = value.categories;
                            graphData['data'] = value.data;
                            graphData.title = `${programGroup} - Financial Execution Line Graph`;

                            plotGraph(`program-execution-drilldown-ams-graph`, graphData);
                            break;
                        case 'metadata':
                            amsDataList = value.data;
                            showAmsDataList(value.data);
                            break;
                        case 'milestone':
                            break;
                        case 'fielding':
                            showHideFieldingTable(value.message == undefined);
                            break;
                        case 'funding-dropdown':
                            break;
                        default:
                            console.error(`Invalid Program Group: ${programGroup}`);
                            break;
                    }
                } else {
                    console.error(`Promise ${index + 1} rejected with reason:`, result.reason);
                }
            });
        })
        .finally(() => {
            $(`#program-execution-drilldown-view-container`).removeClass('d-none');
            $(`#program-execution-drilldown-no-data`).removeClass('d-flex').addClass('d-none');
            $('#overlay-loader').html('');
        });
}

function showHideFieldingTable(show = true) {
    if (show) {
        $(`#program-execution-drilldown-fielding-table-no-data`).attr('hidden', true);
        $(`#program-execution-drilldown-fielding-table-container`).attr('hidden', false);
    }
    else {
        $(`#program-execution-drilldown-fielding-table-no-data`).attr('hidden', false);
        $(`#program-execution-drilldown-fielding-table-container`).attr('hidden', true);
    }
}

function fetchProgramExecutionDrilldownFundingGraph(input_object) {
    return new Promise((resolve, reject) => {
        $.post(`/portfolio/program_execution_drilldown/graph/funding/update`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (response) {
                response.type = 'funding';
                resolve(response);
            })
            .fail((error) => {
                reject(error);
            });
    });
}

function updateProgramExecutionDrilldownAMSGraph(type) {
    let selectedResourceCategory = get_dropdown_filter_input(`${type}`, 'RESOURCE_CATEGORY_CODE')['RESOURCE_CATEGORY_CODE'];
    let programGroup = $(`#program-execution-drilldown-program-group-dropdown`).val();
    let input_object = {};
    input_object['RESOURCE_CATEGORY_CODE'] = selectedResourceCategory;
    input_object['PROGRAM_GROUP'] = [programGroup];
    $('#overlay-loader').html(overlay_loading_html);

    Promise.allSettled([
        fetchProgramExecutionDrilldownAMSGraph(input_object)
    ])
        .then(results => {
            results.forEach((result, index) => {
                const { status, value } = result;

                if (status === 'fulfilled') {
                    let graphData = {};

                    graphData = {
                        categories: [],
                        data: [],
                        title: ''
                    }

                    graphData['categories'] = value.categories;
                    graphData['data'] = value.data;

                    if (value.data.length === 0) {
                        graphData.title = `${programGroup} - Financial Execution Line Graph (No Data)`;
                    }
                    else {
                        graphData.title = `${programGroup} - Financial Execution Line Graph`;
                        graphData.subtitle = 'Data from AMS Financial Execution Module linked via PPBES-MIS.<br/>Shows $ as $K/Dollars (Thousands) from Financial Execution Module able to be mapped to PPBES-MIS.';
                    }

                    plotGraph(`program-execution-drilldown-ams-graph`, graphData);
                } else {
                    console.error(`Promise ${index + 1} rejected with reason:`, result.reason);
                }
            });
        })
        .finally(() => {
            $('#overlay-loader').html('');
        });
}

function fetchProgramExecutionDrilldownAMSGraph(input_object) {
    return new Promise((resolve, reject) => {
        $.post(`/portfolio/program_execution_drilldown/graph/ams/update`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (response) {
                if (response) {
                    response['type'] = 'ams';
                }
                resolve(response);
            })
            .fail((error) => {
                reject(error);
            });
    });
}

function fetchProgramExecutionDrilldownMetadata(input_object) {
    return new Promise((resolve, reject) => {
        $.post(`/portfolio/program_execution_drilldown/metadata/get`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (response) {
                response.type = 'metadata';
                resolve(response);
            })
            .fail((error) => {
                reject(error);
            });
    });
}

function fetchProgramExecutionDrilldownMilestone(input_object, action) {
    return new Promise((resolve, reject) => {
        let route = "";
        if (action === 'update') {
            route = `#program-execution-drilldown-milestones-table-view`;
        }
        else {
            route = `#program-execution-drilldown-milestones-container`;
        }

        loadPageData(
            route,
            `/portfolio/program_execution_drilldown/milestone/${action}`, input_object,
            function (data) {
                let response = {}
                response.status = 'success';
                response.type = 'milestone';
                resolve(response);
            });
    });
}

function showProgramExecutionDrilldown(programGroup) {
    $(`#tab-link-program-execution-drilldown-container`).parent().trigger('click');
    $(`#program-execution-drilldown-program-group-dropdown`).val(programGroup).trigger('change');
    updateProgramExecutionDrillDownCharts(programGroup);
}

function showHideProgramExecutionDrilldown(view) {


    $(`#program-execution-drilldown-view-container`).removeClass('d-none');

    if ($(`#program-execution-drilldown-container`).hasClass('d-none')) {
        $(`#program-execution-drilldown-container`).removeClass('d-none');
        $(`#${view}-container`).addClass('d-none');

        if (view == 'budget-trend-overview') {
            $(`#${view}-container`).removeClass('d-flex');
        }
    }
    else {
        $(`#program-execution-drilldown-${view}-container`).addClass('d-none');
        $(`#${view}-container`).removeClass('d-none');
        if (view == 'budget-trend-overview') {
            $(`#${view}-container`).addClass('d-flex');
        }
    }
}

function updateProgramGroupDropdown(type) {
    let input_object = getSeletedFilterOptions(type);
    input_object['rhombus_token'] = rhombuscookie();

    $.post(`/portfolio/program_group/dropdown/get`, input_object, function (response) {
        update_filter_options(type, response, true);
    })
        .fail((error) => {
            reject(error);
        });
}

function compareProgramsOnReady() {
    updateCompareProgramsPlot();

    $('#compare-programs-toggle-pb-lines').on('change', function () {
        updateCompareProgramsPlot();
    });
}

function fetchCompareProgramsTableData(input_object) {


    if (!input_object['PROGRAM_GROUP']) {
        return;
    }

    let url = `/portfolio/compare_programs/budgets/table/update`;

    return new Promise((resolve, reject) => {
        $.post(url,
            {
                rhombus_token: rhombuscookie(),
                ...input_object
            },
            function (response) {
                if (response.status === 'success') {
                    let data = response.data;

                    response.type = 'table';
                    resolve(response);
                    updateCompareProgramsTable(data);
                } else {
                    // TODO: Handle error if the response status is not 'success'
                }
            },
            "json"
        ).fail(function (jqXHR) {
            // TODO: Handle the error if the AJAX request fails
        });
    });
}

function applyAPPNFilter() {
    let input_object = {};
    input_object['PROGRAM_GROUP'] = get_dropdown_filter_input(type, 'PROGRAM_GROUP')['PROGRAM_GROUP'];
    input_object['RESOURCE_CATEGORY'] = get_dropdown_filter_input('APPN', 'RESOURCE_CATEGORY')['RESOURCE_CATEGORY'];

    if ($('#APPN').attr('appn') == 'loaded') {
        updateCompareProgramsPlot(true, input_object['RESOURCE_CATEGORY']);
        fetchChartDataSelectedProgram('compare-programs', input_object['RESOURCE_CATEGORY']);
    }
}

function updateCompareProgramsTable(data) {
    const columns = [
        { data: 'PROGRAM_FULLNAME', title: 'Program Name', defaultContent: 0 },
        { data: 'EOC_CODE', title: 'EOC Code', defaultContent: 0 },
        { data: 'PE', title: 'PE', defaultContent: 0 },
        { data: 'APPN', title: 'APPN', defaultContent: 0 },
        { data: 'FY', title: 'FY', defaultContent: 0 },
        { data: 'SPENT', title: '$K SPENT', defaultContent: 0 },
        { data: 'BUDGET', title: '$K BUDGET', defaultContent: 0 },
        { data: 'GOAL', title: '$K GOAL', defaultContent: 0 },
        { data: '$K AT RISK', title: '$K AT RISK', defaultContent: 0 },
        { data: '% SPENT', title: '% SPENT', defaultContent: 0 },
        { data: '% GOAL', title: '% GOAL', defaultContent: 0 },
        { data: '% DELTA', title: '% DELTA', defaultContent: 0 }
    ];

    if ($.fn.DataTable.isDataTable('#compare-programs-table')) {
        $('#compare-programs-table').DataTable().destroy();
        $("#FY").html('');
        $("#APPN").html('');
    }

    if ($(`#fy-dropdown-container`).hasClass('d-none')) {
        $(`#fy-dropdown-container`).removeClass('d-none');
        $(`#appn-dropdown-container`).removeClass('d-none');
        $(`#chart-and-table-card`).removeClass('d-none');
        $(`#execution-plots-card`).removeClass('d-none');
        
    }


    compareProgramsTable = $('#compare-programs-table').DataTable({
        paging: true,
        searching: true,
        lengthChange: true,
        responsive: true,
        data: data,
        columnDefs: columns.map((column, i) => ({
            ...column,
            targets: i,
            // className: 'dt-center'
        })),
        initComplete: function () {
            this.api().columns().every(function () {
                let title = this.header();

                //replace spaces with dashes
                title = $(title).html().replace(/[\W]/g, '-');
                let column = this;


                let selChange = function () {
                    //Get the "text" property from each selected data 
                    //regex escape the value and store in array
                    let data = $.map($(this).select2('data'), function (value, key) {
                        return value.text ? '^' + $.fn.dataTable.util.escapeRegex(value.text) + '$' : null;
                    });

                    //if no data selected use ""
                    if (data.length === 0) {
                        data = [""];
                    }

                    //join array into string with regex or (|)
                    let val = data.join('|');

                    //search for the option(s) selected
                    column.search(val ? val : '', true, false).draw();

                };

                if (['APPN', 'FY'].includes(title)) {
                    let select = $(`#${title}`);

                    //select.empty();
                    select.off('change', selChange).on('change', selChange);

                    column.data().unique().sort().each(function (d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>');
                    });

                    $('#' + title).val([]).select2({
                        multiple: true,
                        closeOnSelect: true,
                        placeholder: "Select a " + title,
                        width: '14vw'
                    });

                }
            });
            $('#APPN').attr('appn', 'loaded');
            $('#FY').attr('fy', 'loaded');
        }
    });
}

function updateCompareProgramsPlot(applyAPPNFilter = false, resource_category = []) {
    let input_object = get_dropdown_filter_input('compare-programs', 'PROGRAM_GROUP');

    if (!input_object['PROGRAM_GROUP']) {
        return;
    }

    let input_object_trending_1 = {};
    let input_object_trending_2 = {};
    let promiseList = [];

    const showPBLines = $('#compare-programs-toggle-pb-lines').prop('checked');

    input_object_trending_1['PROGRAM_GROUP'] = [input_object['PROGRAM_GROUP'][0]];
    input_object_trending_1['RESOURCE_CATEGORY'] = resource_category;
    if (showPBLines) {
        promiseList.push(fetchBudgetTrendOverviewGraph(input_object_trending_1));
    }
    promiseList.push(fetchFinalEnactedBudgetGraph(input_object_trending_1));

    if (input_object['PROGRAM_GROUP'].length >= 2) {
        input_object_trending_2['PROGRAM_GROUP'] = [input_object['PROGRAM_GROUP'][1]];
        input_object_trending_2['RESOURCE_CATEGORY'] = resource_category;
        if (showPBLines) {
            promiseList.push(fetchBudgetTrendOverviewGraph(input_object_trending_2, 2));
        }
        promiseList.push(fetchFinalEnactedBudgetGraph(input_object_trending_2, 2));
    }

    if (!applyAPPNFilter) {
        promiseList.push(fetchCompareProgramsTableData(input_object));
        promiseList.push(fetchChartDataSelectedProgram('compare-programs'));
    }

    $('#overlay-loader').html(overlay_loading_html);
    Promise.allSettled([
        ...promiseList
    ])
        .then(results => {
            let graphData = [];
            for (let i = 0; i < input_object['PROGRAM_GROUP'].length; i++) {
                graphData.push({
                    categories: [],
                    data: [],
                })
            }

            results.forEach((result, index) => {
                const { status, value } = result;
                switch (value.type) {
                    case 'graph':
                        if (status === 'fulfilled') {
                            let graphIdx = value.idx;
                            const allCategories = [...graphData[graphIdx - 1].categories, ...value.categories];
                            let categoriesSet = new Set(allCategories);
                            graphData[graphIdx - 1]['categories'] = [...categoriesSet];
                            graphData[graphIdx - 1]['data'] = [...graphData[graphIdx - 1].data, ...value.data];
                        } else {
                            console.error(`Promise ${index + 1} rejected with reason:`, result.reason);
                        }
                        break;
                    case 'table':
                    case 'chart':
                        break;
                    default:
                        break;
                }
            });
            graphData.forEach((data, index) => {
                data.title = `Budgeting Trends ${data['categories'][0]} - ${data['categories'][data['categories'].length - 1]} by ${input_object['PROGRAM_GROUP'][index]}`;
                let newGraph = plotGraph(`execution-data-plot-${index + 1}`, data);
                minMaxMap[`execution-data-plot-${index + 1}`] = {
                    'min': newGraph.yAxis[0].min,
                    'max': newGraph.yAxis[0].max
                }
            })
        })
        .finally(() => {

            if (input_object['PROGRAM_GROUP'].length > 0) {
                $('#compare-programs-toggle-pb-lines-container').removeClass('d-none');
            } else {
                $('#compare-programs-toggle-pb-lines-container').addClass('d-none');
            }

            if (input_object['PROGRAM_GROUP'].length >= 2) {
                $('#execution-data-plot-1-container').removeClass('w-100').addClass('w-50');
                $('#execution-data-plot-2-container').attr('hidden', false);
                $('#compare-programs-common-range-toggle').prop('checked', false);
                $('#compare-programs-common-range-toggle-container').attr('hidden', false);
            }
            else {
                $('#execution-data-plot-1-container').removeClass('w-50').addClass('w-100');
                $('#execution-data-plot-2-container').attr('hidden', true);
                $('#compare-programs-common-range-toggle-container').attr('hidden', true);
                $('#compare-programs-common-range-toggle').prop('checked', false);
            }

            $('#compare-programs-no-data').removeClass('d-flex').addClass('d-none');
            $('#overlay-loader').html('');
        });
}

function fetchFieldingTableData() {

    let selectedFiscalYear = $(`#program-execution-drilldown-fiscal-year-dropdown`).val();
    let selectedComponents = get_dropdown_filter_input(`fielding-component`, 'COMPONENT')['COMPONENT'];
    let selectedProgramGroup = $(`#program-execution-drilldown-program-group-dropdown`).val();

    let requestData = {
        PROGRAM_GROUP: [selectedProgramGroup],
        FISCAL_YEAR: selectedFiscalYear,
        COMPONENT: selectedComponents,
        rhombus_token: rhombuscookie()
    };

    $.ajax({
        url: '/portfolio/fielding_view/table/get',
        type: 'POST',
        data: requestData,
        dataType: 'json',
        success: function (response) {
            if (response.success && response?.data?.planned_actual_data) {
                updateFieldingTable(selectedProgramGroup, response.data.planned_actual_data);
                updateFieldingInformationText()
            } else {
                console.error('Failed to fetch fielding table data:', response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function fetchFundingGraphDropdown(input_object) {

    let requestData = {
        ...input_object,
        rhombus_token: rhombuscookie()
    };

    let programGroup = input_object['PROGRAM_GROUP'][0];
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/portfolio/program_execution_drilldown/dropdown/funding/get',
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    response.type = 'funding-dropdown';
                    resolve(response);
                    if (Object.keys(response.dropdowns).length > 0) {
                        let div = $(`#program-execution-drilldown-funding-container`);
                        div.attr('program-group', programGroup);
                        update_filter_options(`funding-resource-category`, response.dropdowns, true);
                    }
                } else {
                    console.error('Failed to fetch funding drodpdown options:', response.error);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    })
}

function fetchFieldingTableDropdown(input_object) {

    let requestData = {
        ...input_object,
        rhombus_token: rhombuscookie()
    };

    let programGroup = input_object['PROGRAM_GROUP'][0];
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/portfolio/fielding_view/dropdown/get',
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    response.type = 'fielding';
                    resolve(response);
                    if (Object.keys(response.dropdowns).length > 0) {
                        updateDropdownOptions(`program-execution-drilldown-fiscal-year-dropdown`, response.dropdowns.fiscal_years, false, programGroup, response.dropdowns.selected_year);
                        updateDropdownOptions(`fielding-component`, response.dropdowns.components, true, programGroup);

                        availableComponentsMap = response.dropdowns.components;
                        updateFieldingInformationText();

                        $(`#program-execution-drilldown-fiscal-year-dropdown`).select2({
                            placeholder: "Select Year",
                            width: '17vw'
                        });

                        fetchFieldingTableData();
                    }
                } else {
                    console.error('Failed to fetch fielding table data:', response.error);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    });
}

function updateDropdownOptions(selector, options, isMultiSelect, programGroup, selectedYear = '') {

    let dropdown = $(`#${selector}`);
    dropdown.empty('');
    dropdown.attr('program-group', programGroup);
    if (!isMultiSelect) {
        let uniqueOptions = [...new Set(options)];
        uniqueOptions.forEach(option => {
            if (option == selectedYear) {
                dropdown.append(`<option value="${option}" selected>${option}</option>`);
            }
            else {
                dropdown.append(`<option value="${option}">${option}</option>`);
            }
        });
    }
    else {
        update_filter_options(selector, options, true);
    }
}

function updateFieldingTable(programGroup, data) {
    let plannedTable = $(`#planned-quantities-table-program-execution-drilldown`);
    let actualTable = $(`#actual-quantities-table-program-execution-drilldown`);
    if ($.fn.DataTable.isDataTable(plannedTable)) {
        plannedTable.DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable(actualTable)) {
        actualTable.DataTable().destroy();
    }
    plannedTable.find('tbody').empty();
    actualTable.find('tbody').empty();

    let aggregatedData = {};
    data.forEach(row => {
        let item = row.FIELDING_ITEM;
        if (!aggregatedData[item]) {
            aggregatedData[item] = {
                SUM_PLAN_QUANTITY: 0,
                SUM_ACTUAL_QUANTITY: 0,
                FIELDING_ITEM: item
            };
        }

        aggregatedData[item].SUM_PLAN_QUANTITY += row.SUM_PLAN_QUANTITY;
        aggregatedData[item].SUM_ACTUAL_QUANTITY += row.SUM_ACTUAL_QUANTITY;
    });

    let plannedRows = "";
    let actualRows = "";
    Object.values(aggregatedData).forEach(row => {
        plannedRows += `
            <tr>
            <td> 
                <a  
                    href="#" 
                    onclick="showFieldingQuantitiesModal('${row.FIELDING_ITEM}', '${programGroup}', 'PLAN')" 
                    data-modal-target="#program-execution-drilldown-fielding-quantities"
                > 
                    ${row.FIELDING_ITEM}
                </a>
            </td>
            <td>${row.SUM_PLAN_QUANTITY}</td>
            </tr>
                `;
        actualRows += `
            <tr>
                <td>                
                    <a  
                        href="#" 
                        onclick="showFieldingQuantitiesModal('${row.FIELDING_ITEM}', '${programGroup}', 'ACTUAL')" 
                        data-modal-target="#program-execution-drilldown-fielding-quantities"
                    > 
                        ${row.FIELDING_ITEM}
                    </a>
                </td>
            <td>${row.SUM_ACTUAL_QUANTITY}</td>
            </tr>
                `;
    });
    plannedTable.find('tbody').html(plannedRows);
    actualTable.find('tbody').html(actualRows);
    plannedTable.DataTable({
        paging: false,
        searching: false,
        ordering: true,
        autoWidth: false,
        info: false
    });
    actualTable.DataTable({
        paging: false,
        searching: false,
        ordering: true,
        autoWidth: false,
        info: false
    });
}

function showAmsDataList(value) {
    let amsDataListHtmText = '';
    let exceedStringLimit = false
    for (const [key, values] of Object.entries(value)) {
        let title = key.replace(/_/g, " ");

        let items = '';
        values.forEach((item, idx) => {
            if (idx < amsDatalimit) {
                if (item.length > amsDataStringlimit) {
                    item = item.substring(0, amsDataStringlimit) + '...';
                    exceedStringLimit = true
                }
                items += `<li class="bx--list__item my-2">${item}</li>`
            }
        });

        if (values.length > amsDatalimit || exceedStringLimit) {
            items += `<a 
                href="#" 
                onclick="showAMSDataModal('${key}')" 
                data-modal-target="#program-execution-drilldown-ams-data"> 
                        More...
                </a>`   
        }

         amsDataListHtmText += `<div class="metadata-section">
            <div class="metadata-section__title"><h5>${title}</h5></div>
                <ul class="metadata-list">
                    ${items}
                </ul>
        </div>`;
    }

    $(`#program-execution-drilldown-ams-data-container`).html(amsDataListHtmText);
}

function showAMSDataModal(type) {
    let amsDataListHtmText = '';

    // update title
    $('#program-execution-drilldown-ams-data-heading').html(type.replace(/_/g, " "));

    const amsData = amsDataList[type];
    let items = '';

    amsData.forEach((item) => {
        items += `<li class="bx--list__item my-2">${item}</li>`
    });

    amsDataListHtmText += `<div class="p-3">
                            <ul class="bx--list--unordered">${items}</ul>
                            </div>`;
    $(`#program-execution-drilldown-ams-modal-data-container`).html(amsDataListHtmText);
}

function getPlannedActualGraphData(data, item, fiscalYears) {

    let graphData = {}
    let fielding_data = {
        'PLAN_QUANTITY': {},
        'ACTUAL_QUANTITY': {}
    };

    // default to zero
    fiscalYears.forEach(fiscal_year => {
        fielding_data['PLAN_QUANTITY'][fiscal_year] = 0;
        fielding_data['ACTUAL_QUANTITY'][fiscal_year] = 0;
    })

    data.forEach(item => {
        fielding_data['PLAN_QUANTITY'][item['PLAN_FISCAL_YEAR']] += item['SUM_PLAN_QUANTITY'];
        fielding_data['ACTUAL_QUANTITY'][item['PLAN_FISCAL_YEAR']] += item['SUM_ACTUAL_QUANTITY'];
    })

    graphData['categories'] = fiscalYears;
    graphData['data'] = [
        {
            'name': 'Planned Qty',
            'data': Object.values(fielding_data['PLAN_QUANTITY'])
        },
        {
            'name': 'Actual Qty',
            'data': Object.values(fielding_data['ACTUAL_QUANTITY'])
        }
    ];
    graphData.title = `${item} - Planned/Actual Qty`;
    graphData['title_y'] = `Fielded Quantities`;

    return graphData;
}

function getCumulativeGraphData(data, item, fiscalYears, type) {
    let graphData = {}
    let startYear = Math.min(...fiscalYears);

    let cumulative_data = {
        'FUNDING_QUANTITY': {},
        'DELIVERY_QUANTITY': {},
        'FIELDING_QUANTITY': {}
    };

    // default to zero
    fiscalYears.forEach(fiscal_year => {
        cumulative_data['FUNDING_QUANTITY'][fiscal_year] = 0;
        cumulative_data['DELIVERY_QUANTITY'][fiscal_year] = 0;
        cumulative_data['FIELDING_QUANTITY'][fiscal_year] = 0;
    })


    for (const [key, value] of Object.entries(data)) {
        value.forEach((item, idx) => {
            cumulative_data[`${key.toUpperCase()}_QUANTITY`][item['PLAN_FISCAL_YEAR']] = item[`SUM_${type}_QUANTITY`];
        })
    }

    for (const [key, value] of Object.entries(cumulative_data)) {
        for (const [year, data] of Object.entries(value)) {
            if (parseInt(year) !== startYear) {
                cumulative_data[key][year] = cumulative_data[key][year] + cumulative_data[key][parseInt(year - 1).toString()];
            }
        }
    }

    graphData['categories'] = fiscalYears;
    graphData['data'] = [
        {
            'name': 'Funding Qty',
            'data': Object.values(cumulative_data['FUNDING_QUANTITY'])
        },
        {
            'name': 'Delivery Qty',
            'data': Object.values(cumulative_data['DELIVERY_QUANTITY'])
        },
        {
            'name': 'Fielding Qty',
            'data': Object.values(cumulative_data['FIELDING_QUANTITY'])
        }
    ];
    graphData.title = `${item} - Cumulative Qty`;
    graphData['title_y'] = `Fielded Quantities`;

    return graphData;
}

function showFieldingQuantitiesModal(item, programGroup, type) {

    let input_object = {
        'PROGRAM_GROUP': [programGroup],
        'COMPONENT': get_dropdown_filter_input('fielding-component', 'COMPONENT')['COMPONENT'],
        'FIELDING_ITEM': item
    }

    if ($('#fielding-component').val().includes('ALL')) {
        input_object['COMPONENT'] = [];
    }

    $('#fielding-quantities-planned-actual-graph-switch').trigger('click');

    $('#overlay-loader').html(overlay_loading_html);
    $.post(`/portfolio/program_execution_drilldown/graph/fielding/update`,
        {
            'rhombus_token': rhombuscookie(),
            ...input_object
        },
        function (response) {
            if (response.success && response.data) {
                let cumulativeData = response.data['cumulative_data'];

                let uniqueFiscalYears = [];
                for (const [_, value] of Object.entries(cumulativeData)) {
                    uniqueFiscalYears = uniqueFiscalYears.concat([...new Set(value.map(item => item['PLAN_FISCAL_YEAR']))]);
                }

                let startYear = Math.min(...uniqueFiscalYears);
                let endYear = Math.max(...uniqueFiscalYears);
                let fiscalYears = Array.from({ length: endYear - startYear + 1 }, (_, i) => startYear + i);

                let plannedActualGraphData = getPlannedActualGraphData(response.data['cumulative_data']['fielding'], item, fiscalYears);
                let cumulativeGraphData = getCumulativeGraphData(cumulativeData, item, fiscalYears, type);

                plotGraph(`fielding-quantities-planned-actual-line-plot`, plannedActualGraphData);
                plotGraph(`fielding-quantities-cumulative-line-plot`, cumulativeGraphData);
            }
            $('#overlay-loader').html('');
        })
        .fail((error) => {
            reject(error);
        });
}

function convertRequirementsDate(dateStr) {
    if (dateStr != null) {
        const d = new Date(dateStr);
        return `${d.getMonth() + 1}/${d.getDate()}/${d.getFullYear()}`;
    }
    else {
        return 'N/A';
    }
}

function showMilestonesRequirementsModal(pxid, milestone, milestoneStatus, milestoneCompleted) {
    let input_object = {
        'PXID': pxid,
        'MILESTONE': milestone,
        'MILESTONE_STATUS': milestoneStatus
    }
    let selectedProgramGroup = $(`#program-execution-drilldown-program-group-dropdown`).val();

    $('#milestones-requirements-table-header').html(`Requirements for ${selectedProgramGroup}, Milestone: ${milestone} `);

    if (milestoneCompleted) {
        $.post(`/portfolio/program_execution_drilldown/milestone/requirements/get`,
            {
                'rhombus_token': rhombuscookie(),
                ...input_object
            },
            function (data) {
                if (data.length > 0) {
                    data.forEach(item => {
                        item['IS_REQUIREMENT_CURRENT'] = item['IS_CURRENT'] ? 'CURRENT' : 'NOT CURRENT';
                        item['START_DATE'] = convertRequirementsDate(item['START_DATE']);
                        item['DUE_DATE'] = convertRequirementsDate(item['DUE_DATE']);
                        item['COMPLETED_DATE'] = convertRequirementsDate(item['COMPLETED_DATE']);
                    });
                    updateMilestoneRequirementsTable(data);
                }
            })
            .fail((error) => {
                reject(error);
            });
    }
}

function updateMilestoneRequirementsTable(data) {

    const columns = [
        { data: 'REQUIREMENT', title: 'Requirement', defaultContent: '' },
        { data: 'COMPLETE_CHECK', title: 'Requirement Status', defaultContent: '' },
        { data: 'IS_REQUIREMENT_CURRENT', title: 'Is Requirement Current', defaultContent: '' },
        { data: 'START_DATE', title: 'Start Date', defaultContent: 'N/A' },
        { data: 'DUE_DATE', title: 'Due Date', defaultContent: 'N/A' },
        { data: 'COMPLETED_DATE', title: 'Completion Date', defaultContent: 'N/A' },
        { data: 'COMPLETION_STATUS', title: 'Completion Status', defaultContent: 'N/A' }
    ];

    if ($.fn.DataTable.isDataTable('#milestones-requirements-table')) {
        milestoneRequirementsTable.destroy();
    }

    milestoneRequirementsTable = $('#milestones-requirements-table').DataTable({
        paging: true,
        searching: true,
        lengthChange: true,
        responsive: true,
        data: data,
        columnDefs: columns.map((column, i) => ({
            ...column,
            targets: i,
            // className: 'dt-center'
        })),
        createdRow: function (row, data, dataIndex) {
            $(row).find('td:eq(6)').addClass(requirementsCellStyle[data["COMPLETION_STATUS"]]);
        }
    });
}

function milestoneDropdownOnchange(view, programGroup) {
    let input_object = {
        'PROGRAM': $(`#program-execution-drilldown-milestones-dropdown`).val(),
        'PROGRAM_GROUP': programGroup,
    }
    $('#overlay-loader').html(overlay_loading_html);
    fetchProgramExecutionDrilldownMilestone(input_object, 'update').finally(() => {
        $('#overlay-loader').html('');
    });;
}

function updateFieldingCompmonentsDropdown(callback = null) {
    let selectedFiscalYear = $(`#program-execution-drilldown-fiscal-year-dropdown`).val();
    let selectedProgramGroup = $(`#program-execution-drilldown-program-group-dropdown`).val();

    let requestData = {
        PROGRAM_GROUP: [selectedProgramGroup],
        FISCAL_YEAR: selectedFiscalYear,
        rhombus_token: rhombuscookie()
    };

    $.ajax({
        url: '/portfolio/fielding_view/dropdown/component/get',
        type: 'POST',
        data: requestData,
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                availableComponentsMap = response.dropdowns.components;
                updateDropdownOptions(`fielding-component`, response.dropdowns.components, true, selectedProgramGroup, selectedFiscalYear);
                if (callback) {
                    callback();
                }
            } else {
                console.error('Failed to fetch fielding table data:', response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function updateFieldingInformationText() {
    let selectedComponents = $(`#fielding-component`).val();
    let selectedProgramGroup = $(`#program-execution-drilldown-program-group-dropdown`).val();
    let availableComponents = availableComponentsMap || [];
    let displayText;

    if (!selectedComponents || selectedComponents.length === 0 || selectedComponents.includes("ALL")) {
        displayText = availableComponents.length > 0 ? availableComponents.join(', ') : '';
    }
    else {
        displayText = selectedComponents.join(', ');
    }
    $(`#fielding-information-text-program-execution-drilldown`).text(`${displayText}`);
    $(`#selected-program-group-program-execution-drilldown`).text(selectedProgramGroup);
}

function common_range_toggle() {
    if ($('#compare-programs-common-range-toggle').prop('checked')) {
        let alignedMin = Math.min(
            $('#execution-data-plot-1').highcharts().yAxis[0].min,
            $('#execution-data-plot-2').highcharts().yAxis[0].min
        )
        let alignedMax = Math.max(
            $('#execution-data-plot-1').highcharts().yAxis[0].max,
            $('#execution-data-plot-2').highcharts().yAxis[0].max
        )

        // Update chart1 y-axis
        $('#execution-data-plot-1').highcharts().yAxis[0].update({
            min: alignedMin,
            max: alignedMax
        });

        // Update chart2 y-axis
        $('#execution-data-plot-2').highcharts().yAxis[0].update({
            min: alignedMin,
            max: alignedMax
        });
    }
    else {
        // Revert to original y-axis ranges
        // Update chart1 y-axis
        $('#execution-data-plot-1').highcharts().yAxis[0].update({
            min: minMaxMap['execution-data-plot-1'].min,
            max: minMaxMap['execution-data-plot-1'].max
        });

        // Update chart2 y-axis
        $('#execution-data-plot-2').highcharts().yAxis[0].update({
            min: minMaxMap['execution-data-plot-2'].min,
            max: minMaxMap['execution-data-plot-2'].max
        });
    }
}

function programGroupDropdownOnchange(tab_type) {
    let programGroup = $(`#${tab_type}-program-group-dropdown`).val();
    updateProgramExecutionDrillDownCharts(programGroup);
}