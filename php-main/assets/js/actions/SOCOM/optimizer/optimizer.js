
let textColor = 'var(--cds-text-02, #525252)';
let maxHeight;

function createCOAGraph(seriesData, ci=1,maxCeiling = 2400){
    let categories = [];
    for (let i = 1; i <= ci;i++) {
        categories.push(`COA${i}`);
    }
    const staticMax = maxCeiling;
    function computeMax(chart) {
    chart.yAxis[0].update({
        scrollbar:{
            enabled:true,
        }})

    if (chart._isDrilled) {
        return Math.min(staticMax, chart._drilledValue);
      }
  
      // top‑level: scan only the stackTotals
      let maxVal = 0;
      chart.series.forEach(s => {
        s.data.forEach(p => {
          if ((p.stackTotal || 0) > maxVal) {
            maxVal = p.stackTotal;
          }
        });
      });
      return Math.min(staticMax, maxVal);

  }

    const myChart= Highcharts.chart('coa-graph', {
        chart: {
            backgroundColor: 'transparent',
            type: 'column',
            events: {
            drilldown: function (e) {

                if (!e.seriesOptions) {
                    var chart = this;
                    var ddId = e.point.drilldown || e.point.name;
                    chart._isDrilled= true
                    chart._drilledValue   = e.point.y;

                    chart.recalcYMax();
                    chart.yAxis[0].update({
                        scrollbar:{
                            enabled:true,
                            showFull: false
                        }
                    });
                    // Build new xAxis categories from the drilldown series data:
                    const series = this.customDrilldownMapping

                    let mySeries=[];
                    for (const [key, value] of Object.entries(series)) {

                        let currSeries= value[ddId]
                        if(currSeries){
                            let input= currSeries;
                            const totalCOAs = chart.options.customCoaCount;
                            const result = {}

                            input.forEach((item, coaIndex) => {
                                // Ensure we process each COA's data as an array.
                                const itemsArray = Array.isArray(item) ? item : [item];
                                itemsArray.forEach(obj => {
                                // Use id, name and stack as the grouping key.
                                const groupKey = obj.id + '_' + obj.name+ '_'+obj.stack;
                                // Initialize the group if not already present.
                                if (!result[groupKey]) {
                                    // Create a data array of length totalCOAs, fill with empty arrays.
                                    const dataArr = new Array(totalCOAs).fill([]);

                                    result[groupKey] = {
                                    id: obj.id,
                                    name: obj.name,  // Use the first occurrence's name.
                                    stack: obj.stack,
                                    data: dataArr,
                                    
                                    tooltip: obj.tooltip,
                                    showInLegend: obj.showInLegend
                                    };
                                }
                                // Place the current data ( in obj.data[0]) in the correct COA index.
                                result[groupKey].data[key]=obj.data[0];
                                });
                            })
                            mySeries.push(Object.values(result))
                        }
                    }
                                if (mySeries) {
                                    
                                   
                                    mySeries.forEach(ser=>{
                                            if (Array.isArray(ser)) {
                                                ser.forEach(s=>{
                                                    chart.addSingleSeriesAsDrilldown(e.point, s);
                                                })
                                                
                                            }
                                            else{
                                             chart.addSingleSeriesAsDrilldown(e.point, ser);
                                            }
                                        })
                                    chart.applyDrilldown()
                                } else {
                                    console.log('No drilldown series found for key:', ddId);
                                }
                   }
                   
                            
                },
                drillup: function () {
                    var chart = this;
                    chart._isDrilled= false
                    chart.update({ chart: { animation: false }}, false);
                    chart.recalcYMax();

                  },
              }
              
        },
        title: {
            text: `COA Comparison`,
            style: {
                color: textColor,
                fontWeight: 'bolder'
            }
        },
        xAxis: {
            
            categories: categories,
            
            labels: {
                style: {
                    color: textColor,
                    fontSize: '13px'
                }
            },
            enabled: true,
            events: {
              click: function (e) {
                // Prevent drilldown when clicking on x-axis labels
                e.preventDefault();
              },
            },
        },
        customCoaCount: categories.length,
        yAxis: {
            min: 0,
            max: staticMax,
            tickInterval:200,
            title: {
                text: 'Score',
                style: {
                    color: textColor,
                    fontSize: '13px'
                }
            },
            scrollbar:{
                enabled:true,
                showFull: false
            },
            stackLabels: {
                enabled: true
            },
            labels: {
                style: {
                    color: textColor,
                    fontSize: '13px'
                }
            }
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        },
        plotOptions: {
            series: {
                cursor: 'pointer',
            },
            column: {
                stacking: 'normal',
                maxPointWidth: 50,
                groupPadding: 0.1,
                pointPadding: 0.1,
                dataLabels: {
                enabled: true
                }
            },
        },
        series: seriesData.series,
        drilldown: {
          series: []
        },
        credits:{
            enabled:false
        },
        exporting: {
            enabled: true,
        },
        legend: {
            enabled: true,
            labelFormat: "{name}"
        },
        height: 100
    });

    myChart.customDrilldownMapping = {};
    myChart.customDrilldownCategories = {};
    
    myChart.customCategories=seriesData.categories;
    myChart.myCustomSeries= seriesData.series
    myChart.coaIndex=0

    myChart.recalcYMax = function () {
        const newMax = computeMax(this);
        myChart.yAxis[0].update({ max: newMax }, false);
      };
    
      // do the first recalc
    myChart._isDrilled    = false;
    myChart._drilledValue = 0;
    myChart.recalcYMax();

    seriesData.drilldownSeries.forEach(item => {
        const myKey = item.id; // For example, "5G"
        // Initialize an array if it doesn't exist.
        if (!myChart.customDrilldownMapping[myChart.coaIndex]) {
          myChart.customDrilldownMapping[myChart.coaIndex] = [];
        }
        if (!myChart.customDrilldownMapping[myChart.coaIndex][myKey]) {
            myChart.customDrilldownMapping[myChart.coaIndex][myKey] = [];
          }
        // Append the current drilldown series object.
        myChart.customDrilldownMapping[myChart.coaIndex][myKey].push(item);
      });
     
    return myChart;
}

const loadingMessages = [
    "Retrieve FYDP Resource $ for the Programs",
    "Calculate Weighted Scores for the Programs (POM and Guidance Scores)",
    "Incorporate Priority (Must Include) and Remove From Play (Must Exclude) Program IDs",
    "Incorporate Options for Weighted Scores",
    "Incorporate Options for Full FYDP Year Supporting",
    "Ingest Maximum Delta (Bogey Numbers)",
    "Finding Optimized Set of Programs given Resources",
    "Sum POM and Guidance Scores for the chosen Programs",
];

let spinnerHtml = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="sr-only">Loading...</span></div>';

function runOptimizer(iel) {
    let wsb, all_years;

    let ryes = $('#r-yes').is(':checked');
    if(ryes){
        all_years = 1; 
    }
    else{
        all_years = 0;
    }

    switch($('input[name=weighted_score_based]:checked').val()) {
        case '1':
            wsb = 1;
        break;
        case '2':
            wsb = 2;
        break;
        case '3':
        default:
            wsb = 3;
        break;
    }

    let include = iel.getInc(), exclude = iel.getExcl();
    let programs = [];
    
    $('#optimizer-table')
        .DataTable()
        .rows({
            search: 'applied'
        })
        .data()
        .map(function(row) { programs.push(row['PROGRAM_ID']); })

    // let isExtractChecked = $('#extract-checkbox-dataset').is(':checked');
    const $radioButtons = $('input[name="use_iss_extract"]');
    let rc = $radioButtons.filter(':checked').val() !== 'true' ? 'rc-':'';

    let data = {
        rhombus_token: rhombuscookie(), 
        weight_id: $('#fp-weight-sel option:selected').val(), 
        must_include: JSON.stringify(include),
        must_exclude: JSON.stringify(exclude),
        support_all_years: all_years,
        budget: [],
        syr: fy_list[0],
        eyr: fy_list[fy_list.length -1]+1,
        programs: JSON.stringify(programs),
        option: wsb,
        storm_flag: $('#r-storm').prop('checked'),
        iss_extract: $radioButtons.filter(':checked').val() === "true",
        per_resource_optimizer: $('input[name=per_resource_optimizer]').prop('checked')
    }, deltas = {};

    $(`#coa-table-${rc}1 input[type=text].deltaOptimizer`).each(function() {
        deltas[$(this).attr('year')] = this.value;
    });
    let type_of_coa = data['iss_extract'] ? 'ISS_EXTRACT' : 'ISS';
    let deltaOrder = Object.keys(deltas);
    deltaOrder = deltaOrder.sort();
    for (let i in deltaOrder) {
        data.budget.push(parseInt(deltas[deltaOrder[i]]));
    }
    showLoadingSummary();
    $.post(`/optimizer/${type_of_coa}/optimize`, 
        data, 
        function(data) {
            if (
                data.coa.detail instanceof Array && 
                typeof data.coa.detail[0] !== undefined && 
                typeof data.coa.detail[0].msg === 'string'
            ) {
                displayToastNotification('error', data.coa.detail[0].msg);
                return;
            } else if (typeof data.coa.detail === 'string') {
                displayToastNotification('error', data.coa.detail);
                return;
            } else if (data.coa.warning) {
                displayToastNotification('error', data.coa.warning);
                return;
            }
            let program_groups ={};
            if (data?.coa?.['selected_programs']?.length > 0) {

                data.coa.selected_programs.forEach(program=>{
                    program_groups[program.program_id]=program.program_group;
                })

                setCurrentCOA(data.id); 
                if (data.id.match(/\d+/)) {
                    $('#save-coa').prop('disabled', false);
                    $('#create-coa').prop('disabled', false);
                }
                //$('#coa-graph').highcharts().destroy();
               // $('#coa-graph').empty().css('height', '150px');
                let chart, seriesData = applyOutputs(data.coa, 1, wsb, $('#r-storm').prop('checked'), program_groups);
                if (seriesData !== false) {
                    chart = createCOAGraph(seriesData, 1);
                    chart.redraw();
                    chart = null;
                }
            } else {
                displayToastNotification('error', 'The optimizer did not select any programs');
            }
        },
        "json"
    ).fail(function(jqXHR) {
        if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.warning === 'string') {
            showNotification(jqXHR.responseJSON.warning, 'error');
        } else if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.detail === 'string') {
            displayToastNotification('error', jqXHR.responseJSON.detail);
        } else {
            displayToastNotification('error', 'Unable to contact Optimizer');
        }
    })
}

let tranche_assignment = {}; 
let allCoaSeriesData = {}; 

function runOptimizerRc(iel) {
    let wsb;

    switch($('input[name=weighted_score_based]:checked').val()) {
        case '1':
            wsb = 1;
        break;
        case '2':
            wsb = 2;
        break;
        case '3':
        default:
            wsb = 3;
        break;
    }

    // new RC_T fields
    let num_tranches = parseInt($('#tranche-select > option:selected').val()),
        cut_by_percentage, tranches, percent_allocation;
    
    if (
        !Number.isFinite(num_tranches) || 
        (num_tranches <= 0 && num_tranches > 5)) {
            num_tranches = 1;
    }

    let include = iel.getInc(), exclude = iel.getExcl();
    let programSet = new Set();
    let programs = [];
    
    // get program groups from the program alignment table
    $('#optimizer-table')
        .DataTable()
        .rows({
            search: 'applied'
        })
        .data()
        .map(function(row) { programSet.add(row['PROGRAM_ID']); })
    
    programSet.forEach( value => {
        programs.push(value)
    });

    // let isExtractChecked = $('#extract-checkbox-dataset').is(':checked');
    const $radioButtons = $('input[name="use_iss_extract"]');
    let rc = $radioButtons.filter(':checked').val() !== 'true' ? 'rc-':'';

    tranches = $('#kept_tranches').handsontable('getInstance').getData()[0];
    percent_allocation = $('#cuts_perc_alloc').handsontable('getInstance').getData()[0];

    let data = {
        rhombus_token: rhombuscookie(), 
        weight_id: $('#fp-weight-sel option:selected').val(), 
        must_include: JSON.stringify(include),
        must_exclude: JSON.stringify(exclude),
        num_tranches: num_tranches,
        percent_allocation: percent_allocation,
        tranches: tranches,
        cut_by_percentage: cut_by_percentage,
        budget: [],
        syr: fy_list[0],
        eyr: fy_list[fy_list.length -1]+1,
        programs: JSON.stringify(programs),
        option: wsb,
        storm_flag: $('#r-storm').prop('checked'),
        iss_extract: false,
        per_resource_optimizer: $('input[name=per_resource_optimizer]').prop('checked'),
        keep_cutting: $('input[name=cut_resource_optimizer-rc]').prop('checked')
    }, deltas = {};

    $(`#coa-table-${rc}1 input[type=text].deltaOptimizer`).each(function() {
        deltas[$(this).attr('year')] = this.value;
    });
    let type_of_coa = 'RC_T';
    let deltaOrder = Object.keys(deltas);
    deltaOrder = deltaOrder.sort();
    for (let i in deltaOrder) {
        data.budget.push(parseInt(deltas[deltaOrder[i]]));
    }
    showLoadingSummary();
    $.post(`/optimizer/${type_of_coa}/optimize`, 
        data, 
        function(data) {
            if (
                data.coa.detail instanceof Array && 
                typeof data.coa.detail[0] !== undefined && 
                typeof data.coa.detail[0].msg === 'string'
            ) {
                displayToastNotification('error', data.coa.detail[0].msg);
                return;
            } else if (typeof data.coa.detail === 'string') {
                displayToastNotification('error', data.coa.detail);
                return;
            } else if (data.coa.warning) {
                displayToastNotification('error', data.coa.warning);
                return;
            }
            let program_groups ={};
            if (data?.coa?.['selected_programs']?.length > 0) {

                data.coa.selected_programs.forEach(program=>{
                    program_groups[program.program_id]=program.program_group;
                });

                setCurrentCOA(data.id); 
                if (data.id.match(/\d+/)) {
                    $('#save-coa').prop('disabled', false);
                    $('#create-coa').prop('disabled', false);
                }
                //$('#coa-graph').highcharts().destroy();
               // $('#coa-graph').empty().css('height', '150px');
                let chart, seriesData = applyOutputs(data.coa, 1, wsb, $('#r-storm').prop('checked'), program_groups);
                
                // use program group from data.coa.tranche_assignment to filter seriesData
                if (type_of_coa === 'RC_T') {
                    tranche_assignment = data.coa.tranche_assignment;
                    allCoaSeriesData = seriesData;
                }
                
                if (seriesData !== false) {
                    // create global variable to save tranches 
                    
                    chart = createCOAGraph(seriesData, 1);
                    chart.redraw();
                    chart = null;
                }
            } else {
                displayToastNotification('error', 'The optimizer did not select any programs');
            }
        },
        "json"
    ).fail(function(jqXHR) {
        if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.warning === 'string') {
            showNotification(jqXHR.responseJSON.warning, 'error');
        } else if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.detail === 'string') {
            displayToastNotification('error', jqXHR.responseJSON.detail);
        } else {
            displayToastNotification('error', 'Unable to contact Optimizer');
        }
    })
}

function showLoadingSummary(){
    $('#loading-wrapper').removeClass('d-none');
    $('#net_speed_box').html('Running Optimizer' + spinnerHtml);
    const delay = 500;

    setTimeout(() => $('#net_speed_box').html(loadingMessages.join('<br />')+ spinnerHtml), delay);

    setTimeout(() => $('#loading-wrapper').addClass('d-none'), delay * 5);
}

function applyOutputs(outputData, GP=1, stack=3, storm_flag=false, program_group_map = null) {
    let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'rc-' : '';
    let type_of_coa = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'RC_T' : 'ISS_EXTRACT';
    var imgs = document.querySelectorAll(`#coa-table-${rc}${GP} .remaining`);
    // Set up an event handler.
    if (typeof outputData['remaining'] === 'undefined') {
        return false;
    }
    let years = fy_list;
    let i = 0;
    let total = 0;
    imgs.forEach((IMG)=>{
        if(i<5){
            total += parseInt(outputData['remaining'][years[i]]);
            $(IMG).html(outputData['remaining'][years[i]]);
        }
        else{
            $(IMG).html(total);
        }
        i++;
    })

   // const hexvals = [0,1,2,3,4,5,6,7,8,9,'A',B,C,D,E,F];
    const randomHexColorCode = () => {
        startHex = CryptoJS.lib.WordArray.random(32).toString().substring(0,6);

        return '#'+startHex;
    };

    

    let mpv = (outputData['selected_programs'].length <= 3 ? 3 : outputData['selected_programs'].length+1),
        cgh = (outputData['selected_programs'].length <= 5 ? 150*(outputData['selected_programs'].length+1) : 75*(outputData['selected_programs'].length+1)),
        colors;

        if (outputData['selected_programs'].length <= 10) {
            colors = new GradientColor()
                .setColorGradient(randomHexColorCode(), randomHexColorCode())
                .setMidpoint(mpv)
                .getColors()
        } else {
            colors = new GradientColor()
                .setColorGradient(randomHexColorCode(), randomHexColorCode(), randomHexColorCode())
                .setMidpoint(mpv)
                .getColors()
        }

    // if (cgh > parseInt($('#coa-graph').css('height'))) {
    //     $('#coa-graph').css('height', cgh + 'px');
    // }

    // section that creates data for coa graph
    let seriesData = {
        'data': {},
        'transformed_data': [],
        'series': [],
        'drilldownCategories': {},
        'drilldownSeries':{},
        'categories': []
    }

    if (storm_flag == true) {
        outputData['selected_programs'].forEach((element, index) => {
            const program_id = covertToProgramId(
                type_of_coa,
                {
                    'program_code': element['program_code'] ?? '',
                    'cap_sponsor': element['capability_sponsor'] ?? '',
                    'pom_sponsor': element['pom_sponsor'] ?? '',
                    'ass_area_code': element['assessment_area_code'] ?? '',
                    'execution_manager': element['execution_manager_code'] ?? '',
                    'resource_category': element['resource_category_code'] ?? '',
                    'eoc_code': element['eoc_code'] ?? '',
                    'osd_pe_code': element['osd_pe'] ?? '',
                    'event_name': element['event_name'] ?? ''
                },
                false
            );
            let program_group = '';
            if (element.program_group !== undefined) {
                program_group = element.program_group;
            }
            else if (program_group_map !== null) {
                program_group = program_group_map[element['program_id']];
            }

            if ([1,3].indexOf(stack) != -1) {
                seriesData['data'][element['program_id']+'StoRM' + GP] = {
                    'name': program_id,
                    'coa-id': GP, // coa-id
                    'data': [parseInt(element['total_storm_score'])],
                    'stack': 'StoRM',
                    'pointStart': (parseInt(GP)-1),
                    'color':  colors[index],
                    'program_group': program_group
                }
            }
        });
    } else {
        outputData['selected_programs'].forEach((element, index) => {
            const program_id = covertToProgramId(
                type_of_coa,
                {
                    'program_code': element['program_code'] ?? '',
                    'cap_sponsor': element['capability_sponsor'] ?? '',
                    'pom_sponsor': element['pom_sponsor'] ?? '',
                    'ass_area_code': element['assessment_area_code'] ?? '',
                    'execution_manager': element['execution_manager_code'] ?? '',
                    'resource_category': element['resource_category_code'] ?? '',
                    'eoc_code': element['eoc_code'] ?? '',
                    'osd_pe_code': element['osd_pe'] ?? '',
                    'event_name': element['event_name'] ?? ''
                },
                false
            );

            let program_group = '';
            if (element.program_group !== undefined) {
                program_group = element.program_group;
            }
            else if (program_group_map !== null) {
                program_group = program_group_map[element['program_id']];
            }
            if ([1,3].indexOf(stack) != -1) {
                seriesData['data'][element['program_id']+'POM' + GP] = {
                    'name': program_id,
                    'data': [parseInt(element['weighted_pom_score'])],
                    'stack': 'POM',
                    'pointStart': (parseInt(GP)-1),
                    'color': colors[index],
                    'program_group': program_group
                }
            }
            if ([1,2].indexOf(stack) != -1) {
                seriesData['data'][element['program_id']+'GUIDANCE' + GP] = {
                    'name': program_id,
                    'data': [parseInt(element['weighted_guidance_score'])],
                    'stack': 'Guidance',
                    'pointStart': (parseInt(GP)-1),
                    'color': colors[index],
                    'program_group': program_group
                }
            }
        });
    }

        Object.keys(seriesData['data']).forEach(element => {
        seriesData['transformed_data'].push(seriesData['data'][element])
    })


        const aggregatedMainSeries = {};


        seriesData.transformed_data.forEach(element => {
        const group = element.program_group;
        if (!aggregatedMainSeries[group]) {
            // Initialize a new aggregated entry.

            const ddId = group //+ '_COA' + GP;
            aggregatedMainSeries[group] = {
            name: group,
            y: 0,
            drilldown: ddId,
            stack: element.stack,
            pointStart: element.pointStart,
            color: element.color
            };
        }

        

        // Sum the y values.
        aggregatedMainSeries[group].y += element.data[0];

        try {
        // seriesData series 
        seriesData.series = Object.keys(aggregatedMainSeries).map(group => ({
        name: aggregatedMainSeries[group].name,
        data: [{
            y: Number(Number(aggregatedMainSeries[group].y).toFixed(2)),
            drilldown: aggregatedMainSeries[group].drilldown,
            name: aggregatedMainSeries[group].name,
            stack: aggregatedMainSeries[group].stack
        }],
        pointStart: aggregatedMainSeries[group].pointStart,
        color: aggregatedMainSeries[group].color
        }));
        } catch (e) {
            console.log(e);
            console.log(group);
            console.log(element, group) 
        }

    });


        const categorySet = new Set();
        seriesData.series.forEach(s => {
        categorySet.add(s.name);
        });
        seriesData.categories = Array.from(categorySet); // e.g., ["BATTERY", "YUMMY", ...]

      
        
      const groupedSeries = {};
      Object.entries(seriesData.transformed_data).forEach(([key, element]) => {
        // Use element.program_group as the grouping key (e.g., "NOSQL")
        const group = element.program_group;
        const ddId = group
        
          groupedSeries[key] = {
            id: ddId,
            name: element.name,
            stack: element.stack,
            'pointStart': (parseInt(GP)-1),
            data: [],
            tooltip: {
              headerFormat: "<b>{series.userOptions.name} - {series.userOptions.stack}</b><br/>",
              pointFormat: "Value: {point.stackTotal}"
            },
            showInLegend: true
          };
        //}
        try {
            // Push the current data point into the group's data array
            groupedSeries[key].data.push([
                    element.name,   // For example: "NOSQLSIL_MARSOC_AT&L_A"
                    Number(Number(element.data[0]).toFixed(2)),    // For example: 75 
            ]);
        } catch(e) {
            console.log(e)
            console.log(element)
        }
      });

      
      // Convert the groupedSeries object into an array and assign it to seriesData.drilldownSeries
      seriesData.drilldownSeries = Object.values(groupedSeries);
    return seriesData;
}

function calculateTotalWeight (row, weight_table) {
    let SESSION = JSON.parse(row['SCORE_SESSION']);
    if (SESSION !== null) {
        let weightData = weight_table.rows().data()[0];
        
        if (
            (!(weightData instanceof Object) || typeof weightData[default_criteria[0]] !== 'string')
        ) {
            return '0';
        }

        let totalWeight = 0;
        Object.keys(SESSION).forEach(pKey => {
            let pKeyR = pKey;
            let multi = parseFloat(SESSION[pKey]) * parseFloat(weightData[pKeyR]);
            totalWeight += multi;
        });

        return totalWeight.toFixed(2);
    }
    return '0';
}

let load_optimizer_table = function() {
    //custom sort for checkboxes
    $.fn.dataTable.ext.order['dom-checkbox'] = function (settings, col) {
        return this.api().column(col, { order: 'index' }).nodes().map(function (td) {
          return $('input', td).prop('checked') ? '1' : '0';
        });
      };

    load_optimizer_table = null;    
    $("#optimizer-table").DataTable({
        columnDefs: [
        { 
                'targets': 0,
                'searchable': false,
                'orderable': true,
                'className': 'dt-body-center',
                'orderDataType': 'dom-checkbox',
                'render': function (data, type, full, meta){
                    return `<input id="include-${meta.row}" type="checkbox" name="include[]" value="${full.PROGRAM_ID}">`;
                },
        },
        { 
            'targets': 1,
            'searchable': false,
            'orderable': true,
            'className': 'dt-body-center',
            'orderDataType': 'dom-checkbox',
            'render': function (data, type, full, meta){
                return `<input id="exclude-${meta.row}" type="checkbox" name="exclude[]" value="${full.PROGRAM_ID}">`;
            }
        },
            { targets: 2, data: 'col_2', name: "Event Name", defaultContent: '' },
            { targets: 3, data: 'col_3', name: "Program", defaultContent: ''},
            { targets: 4, data: function (row, type, val, meta) { 
                return calculateTotalWeight(row, selected_Guidance_weight_table) 
            }, name: "GUIDANCE", defaultContent: '0', visible: false },
            { targets: 5, data: function (row, type, val, meta) { 
                return calculateTotalWeight(row, selected_POM_weight_table) 
            }, name: "POM", defaultContent: '0', visible: false },
            { targets: 6, data: 'storm_id', name: "StoRM ID", defaultContent: '0', visible: false },
            { targets: 7, data: 'RESOURCE_CATEGORY_CODE', name: 'Resource Category Code', visible: true },
            { targets: 8, data: 'storm', name: "StoRM Score", defaultContent: '0', visible: true },
            { targets: 9, data: 'fydp', name: "FYDP", defaultContent: '0', visible: true },
            { targets: 10, data: 'ASSESSMENT_AREA_CODE', name: 'Assessment Area Code', visible: false },
            { targets: 11, data: 'PROGRAM_GROUP', name: 'Program Group', visible: false },
            { targets: 12, data: 'CAPABILITY_SPONSOR_CODE', name: 'Capability Sponsor Code', visible: false }
        ],
        initComplete: function() {
            storm_iss_rc_check($('input[name="storm_weighted_based"]')[0]);
        },
        headerCallback: function(thead, data, start, end, display) {
            let tds = $('td.labelYearOptimizer'), inputy = $('input.deltaOptimizer'), yi = 0;
            
            
            for (let ti = 0; ti < (3*fy_list.length);){
                tds[ti].innerHTML = parseInt(fy_list[yi]);
                $(inputy[ti++]).attr('year', fy_list[yi]);
                    
                if (yi === 4) {
                    yi = 0
                } else {
                    yi++
                }
            }
            
            if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
                $(thead).find('th:eq(2)').html('Event Name');
                $(thead).find('th:eq(3)').html('Program');
            } else {
                $(thead).find('th:eq(2)').html('Program');
                $(thead).find('th:eq(3)').html('Capability Sponsor');
            }
        },
        ajax: {
            url: "/socom/resource_constrained_coa/program/list/get",
            type: 'POST',
            data: function() { 
                return {
                    use_iss_extract: $('input[name="use_iss_extract"]:checked').val(),
                    'ass-area': fetch_all_inputs_filter('#ass-area-2'),
                    program: fetch_all_inputs_filter('#program-2', true),
                    rhombus_token: rhombuscookie()
                };
            },
            dataSrc: function (json) {
                let fy_list = json.year_list ?? fy_list;
                let scores = json.scores ?? []

                $('input[name="use_iss_extract"]').prop('disabled', false);
                $('input[name="use_iss_extract"]').next('label').removeClass('bx--tile--is-selected');
                $('input[name="use_iss_extract"]:checked').next('label').addClass('bx--tile--is-selected');
                $('div[aria-labelledby=loading-id-3]').addClass('d-none');

                if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
                    $('#business_rules_div').prop('hidden', true);
                    $('#issue_coa_header').removeClass('d-none');
                    $('#resource_coa_header').addClass('d-none');
                } else {
                    $('#issue_coa_header').addClass('d-none');
                    $('#resource_coa_header').removeClass('d-none');
                    $('#business_rules_div').prop('hidden', false);
                }
                
                selected_program_codes = [];
                selected_program_ids = [];

                for (let i in json.data) {
                    selected_program_codes.push(json.data[i]['PROGRAM_CODE']);
                    selected_program_ids.push(json.data[i]['PROGRAM_ID']);
                
                    if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
                        json.data[i]['col_2'] = json.data[i]['EVENT_NAME'];
                        json.data[i]['col_3'] = json.data[i]['PROGRAM_CODE'];
                    } else {
                        json.data[i]['col_2'] = json.data[i]['PROGRAM_CODE'];
                        json.data[i]['col_3'] = json.data[i]['CAPABILITY_SPONSOR_CODE'];
                    }
                    json.data[i]['fydp'] = 0;
                    let json_data_FY_list = JSON.parse(json.data[i]['FY']);
                    for (let yi in fy_list) {
                        if (json_data_FY_list[fy_list[yi]] === undefined) {
                            json.data[i]['fydp']  += 0;
                        }
                        else {
                            json.data[i]['fydp']  += json_data_FY_list[fy_list[yi]];
                        }
                    }

                    json.data[i]['SCORE_ID'] = scores?.[json.data[i]['PROGRAM_ID']]?.['SCORE_ID'] ?? JSON.stringify('');
                    json.data[i]['SCORE_SESSION'] = scores?.[json.data[i]['PROGRAM_ID']]?.['SCORE_SESSION'] ?? JSON.stringify('');
                }

                return json.data;
            }
        },
        rowHeight: '75px',
        rowCallback: function (row, data) {
            $('td:eq(0) input', row).val(data['PROGRAM_ID'])
            $('td:eq(1) input', row).val(data['PROGRAM_ID'])
        },
    });
}

function resetCOA(iel) {
    $('#create-coa').prop('disabled', true);
    $('#save-coa').prop('disabled', true);

    $('input.deltaOptimizer').val(0);

    iel.reset();

    $('input[type="checkbox"]', $("#optimizer-table").DataTable().cells().nodes()).prop('checked',false);

    setCurrentCOA(null);

    let rc = $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'rc-' : '';
    $(`#coa-table-${rc}1 span.remaining, #coa-table-${rc}2 span.remaining, #coa-table-${rc}3 span.remaining`).html('0');
    $('span.delta-fydp').html('0');

    $(`#otable-${rc}2, #otable-${rc}3`).hide();

    $('#run-optimizer').prop('disabled', false);
    $('.coa-save').hide();

    if ($('#coa-graph').highcharts() !== undefined) {
        $('#coa-graph').highcharts().destroy();
    }

    $('#coa-graph').empty();
    $('.coa-override-modal-button').hide();
    $('.coa-detailed-summary').hide();
    $(`#detailed-comparison-btn`).remove();
    $('.deltaOptimizer').attr('disabled', false);

    //reset to cut button
    $('#to_cut').val('').trigger('change')
    showHideToCutField(true);
    showHideTrancheFilter(false);
}

function storm_iss_rc_check(elem) {
    if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
        storm_weighted_based('#optimizer-table', [6,8], [4,5,7], elem);
    } else {
        storm_weighted_based('#optimizer-table', [7,8], [4,5,6], elem);
    }
    // reload the table to fix the width issue
    $('#pom-table').DataTable().draw();
    $('#guidance-table').DataTable().draw();
    $('#optimizer-table').DataTable().draw();
}

function weighted_iss_rc_check(elem) {
    if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
        storm_weighted_based('#optimizer-table', [4,5], [6,7,8], elem);
    } else {
        storm_weighted_based('#optimizer-table', [4,5,7], [6,8], elem);
    }
    // reload the table to fix the width issue
    $('#pom-table').DataTable().draw();
    $('#guidance-table').DataTable().draw();
    $('#optimizer-table').DataTable().draw();
}

function attach_change_handler() {
    $('input[name="storm_weighted_based"]').on('change', function() { 
        
        if (this.id === 'r-w') {
            $(`#optimizer-table`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get/scored');

            weighted_iss_rc_check(this);
        } else {
            $(`#optimizer-table`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get');

            storm_iss_rc_check(this);
        }
    });
}

function dropdown_onchange_filter(id, type, row_id = null) {
    let input_object = {}
    switch(type) {
        case 'ass-area':
            dropdown_all_view_filter(type, id)
            update_program_filter(id);
            break;
        default:
            break;
        }
}

let lastSelectedItemsMapOpt = {
    'pom': [],
    'ass-area': [],
    'cs': [],
    'program': []
 }
 const selectionTextOptionsOpt = {
    'false': 'Select All',
    'true': 'Deselect All'
 }
 
 
 function selectionHasChangedFilter(id) {
    const lastSelections = lastSelectedItemsMapOpt[id];
    return lastSelections.includes("ALL")
 }
 
function dropdown_all_view_filter(type, id) {
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

    const selectionTextOptionsOpt = {
        'false': 'Select All',
        'true': 'Deselect All'
    }    

    const changeSelectAll = $(selectionButton).attr('data-select-all') !== selectionTextOptionsOpt[stringifyIsSelectAll];
    if (changeSelectAll) {        
        $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
        $(selectionButton).html(selectionTextOptionsOpt[stringifyIsSelectAll]);
    }
    
    if(selectionHasChangedFilter(type) && selected_values.length > 0){
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
    lastSelectedItemsMapOpt[type] = selected_values;
    dropdown.val(selected_values)
}

function fetch_all_inputs_filter(id, emptyAll = false) {
    let select2val =  $(id).val();
    if((emptyAll === false && select2val.includes('ALL')) && select2val.length > 0){
        return $(`${id} > option`).map(function(){
            if(this.value != 'ALL' && this.value != ''){
                return this.value;
            }
        }).get();
    }
    return (select2val == 'ALL' && emptyAll === true ? [] : select2val);
}

function get_input_object(id) {
    let input_object = {};

    if ($("#ass-area-" + id).val() != "" && $("#ass-area-" + id).val() != null) {
        input_object["ass-area"] = fetch_all_inputs_filter(`#ass-area-${id}`)
    }
    return input_object;
}

function update_program_filter(id) {
    let input_object = get_input_object(id);
    
    const programSelectionButton = `#program-${id}-selection`;
    $(programSelectionButton).attr('data-select-all', 'true');
    $(programSelectionButton).html(selectionTextOptionsOpt['false']);
    $(programSelectionButton).attr('disabled', true);

    if ($(`#program-${id}`).val().length) {
        $(`#program-${id}`).val(null).trigger('change')
    }
    if (input_object["ass-area"] && input_object["cs"] && input_object["pom"]) {
        $(`#program-${id}`).attr('disabled', true);
        $.post("/socom/program_group/filter/update", {
            rhombus_token: rhombuscookie(),
            pom: input_object["pom"],
            cs: input_object["cs"],
            'ass-area': input_object["ass-area"]
        }, function(data) {
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
                onchange="dropdown_onchange_filter(1, 'program')"
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
            $(programSelectionButton).attr('disabled', false);
        })
    }
    else {
        $(`#program-${id}`).attr('disabled', true);
    }
}

function fetch_proposed_cuts() {
    let to_cut = parseInt($('#to_cut > option:selected').val());

    if (!isNaN(to_cut)) {
        $.post("/socom/resource_constrained_coa/fetch/proposed_cuts", {
            rhombus_token: rhombuscookie(),
            percentage: to_cut
        }, function(data) {
            displayToastNotification('success', data['message']);

            for (let i in data.data) {
                $('#coa-table-rc-1 input.deltaOptimizer[year='+data.data[i]['FISCAL_YEAR']+']')
                    .val(data.data[i]['RESOURCE_K_CUT']).trigger('change');
            }
        }).fail(function(jqXHR) {
            if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.message === 'string') {
                showNotification(jqXHR.responseJSON.message, 'error');
            } else if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.detail === 'string') {
                displayToastNotification('error', jqXHR.responseJSON.detail);
            } else {
                displayToastNotification('error', 'Unable to fetch Proposed Cuts $k');
            }
        });
    }
}

function addCoaTrancheSelector(loadedCoa=false, loadedNumOfTranches = null, mergeCOAOnly = false) {
    

    if (loadedCoa == false) {
        $("#coa-tranche-container").removeClass('d-none').addClass('d-flex');
        const numOfTranches = parseInt($("#tranche-select").val(), 10);
    
        $("#coa-tranche-select option").not('[value="All"]').remove();
    
        let $after = $("#coa-tranche-select option[value='All']");
        for (let i = 1; i <= numOfTranches; i++) {
            const option = new Option(`Tranche ${i}`, i, false, false);
            $after.after(option);
            $after = $(option);
        }

        if (!mergeCOAOnly) {
            const option = new Option(`Fully Funded`, 'full_keep', false, false);
            $after.after(option);
            $after = $(option);
        }
    
        $("#coa-tranche-select").trigger('change.select2');
    } else {
        $("#coa-tranche-container").removeClass('d-none').addClass('d-flex');
        const numOfTranches = loadedNumOfTranches;
    
        $("#coa-tranche-select option").not('[value="All"]').remove();
    
        let $after = $("#coa-tranche-select option[value='All']");
        for (let i = 1; i <= numOfTranches; i++) {
            const option = new Option(`Tranche ${i}`, i, false, false);
            $after.after(option);
            $after = $(option);
        }

        if (!mergeCOAOnly) {
            const option = new Option(`Fully Funded`, 'full_keep', false, false);
            $after.after(option);
            $after = $(option);
        }
    
        $("#coa-tranche-select").trigger('change.select2');
    }

}

function onReady() {
    attach_change_handler();
    const iel = new incExclList();

    $('#create-coa').on('click', function() {
        clearBusinessRules(iel);
        resetCOA(iel);
    });

    // for business rules event
    $('#business_rules_modal .load_button').on('click', function() {
        applyBusinessRules(iel);
    })

    $('#run-optimizer').on('click', function() {
        $('#create-coa').prop('disabled', true);
        $('#save-coa').prop('disabled', true);

        let use_iss_extract = $('input[name="use_iss_extract"]').filter(':checked').val() === "true" ? true : false;
        if (use_iss_extract) {
            runOptimizer(iel);
        } else {
            runOptimizerRc(iel);
            addCoaTrancheSelector();

        }

        clearBusinessRules(iel);
    });
    
    $('input[name="storm_weighted_based"]').on('change', function() { 
        
        if (this.id === 'r-w') {
            $(`#optimizer-table`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get/scored');
            weighted_iss_rc_check(this);
        } else {
            $(`#optimizer-table`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get');
            storm_iss_rc_check(this);
        }
        // reload the table to fix the width issue
        $('#pom-table').DataTable().draw();
        $('#guidance-table').DataTable().draw();
    });

    for (let i = 1; i <= 3; i++) {
        $(`#coa-table-${i} .deltaOptimizer`).on('change', () => {
            let fydp_val = parseInt(
                parseInt($(`#coa-table-${i} .delta-y1`).val())
                + parseInt($(`#coa-table-${i} .delta-y2`).val())
                + parseInt($(`#coa-table-${i} .delta-y3`).val())
                + parseInt($(`#coa-table-${i} .delta-y4`).val())
                + parseInt($(`#coa-table-${i} .delta-y5`).val())
            );
            $(`#coa-table-${i} .delta-fydp`).text(fydp_val)
        });
        $(`#coa-table-rc-${i} .deltaOptimizer`).on('change', () => {
            let fydp_val = parseInt(
                parseInt($(`#coa-table-rc-${i} .delta-y1`).val())
                + parseInt($(`#coa-table-rc-${i} .delta-y2`).val())
                + parseInt($(`#coa-table-rc-${i} .delta-y3`).val())
                + parseInt($(`#coa-table-rc-${i} .delta-y4`).val())
                + parseInt($(`#coa-table-rc-${i} .delta-y5`).val())
            );
            $(`#coa-table-rc-${i} .delta-fydp`).text(fydp_val)
        });
    }

    $('#optimizer-table tbody').on('change', 'td input[name="include[]"], td input[name="exclude[]"]', function() {
        toggleOptimizerCheck(this, iel)
    });
    $('#business-rules-history-table').on('click', 'td button.bx--btn', function() { undoBusinessRule(this.id, iel); });

    $('#option_filter').on('click', function() {
        
        $('#filter_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
        $(`#ass-area-2`).select2({
            placeholder: "Select an option",
            width: '16vw'
        }).on('change.select2', function() {
            var dropdown = $(this).siblings('span.select2-container');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
        });
    });
 

    const $radioButtons = $('input[name="use_iss_extract"]');
    $radioButtons.on('change', function () {
        $radioButtons.prop('disabled', true);
        resetCOA(iel);
        if ($('input[name="storm_weighted_based"]:checked').val() === '1') {
            weighted_iss_rc_check(this);
        } else {
            storm_iss_rc_check(this);
        }
        $('div[aria-labelledby=loading-id-3]').removeClass('d-none');

        $('#optimizer-table').DataTable().ajax.reload();
    });


    $('#to_cut').on('change', fetch_proposed_cuts);
}

function toggleOptimizerCheck(elem, iel) {
    let alt = {
        include:'exclude',
        exclude: 'include'  
    };
    let matches = elem.id.match(/^(include|exclude)-(\d+)$/);
    $(`#${alt[matches[1]]}-${matches[2]}:checked`).prop('checked', false);
    
    if (matches[1] === 'include' && elem.checked === true) {
        iel.addInc(elem.value);
    } else if (matches[1] === 'include' && elem.checked === false) {
        iel.removeInc(elem.value);
    }

    if (matches[1] === 'exclude' && elem.checked === true) {
        iel.addExcl(elem.value);
    } else if (matches[1] === 'exclude' && elem.checked === false) {
        iel.removeExcl(elem.value);
    }
}

class incExclList {
    #inc = []
    #excl = []
    
    addInc(value) {
        this.#inc.push(value);
    }
    
    removeInc(value) {
        let r = this.#inc.indexOf(value);
        if (r != -1) {
            delete this.#inc[r];
        }
    }

    addExcl(value) {
        this.#excl.push(value)
    }
    
    removeExcl(value) {
        let r = this.#excl.indexOf(value);
        if (r != -1) {
            delete this.#excl[r];
        }
    }

    getInc() {
        return [...this.#inc];
    }

    getExcl() {
        return [...this.#excl];
    }

    reset() {
        this.#excl = [];
        this.#inc = [];
    }
}

$(onReady)

if (!window._rb) { window._rb = {}; }
window._rb.createCOAGraph = createCOAGraph;
window._rb.runOptimizer = runOptimizer;
window._rb.showLoadingSummary = showLoadingSummary;
window._rb.applyOutputs = applyOutputs;
window._rb.resetCOA = resetCOA;
window._rb.onReady = onReady;
window._rb.calculateTotalWeight = calculateTotalWeight;
window._rb.load_optimizer_table = load_optimizer_table;
window._rb.incExclList = incExclList;

