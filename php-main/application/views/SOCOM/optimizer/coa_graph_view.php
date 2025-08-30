
<div id="coa-tranche-container" class="d-none flex-column mt-3 ">
    <label for="coa-tranche-select" class="bx--label">Tranches</label>
    <select id="coa-tranche-select" class="coa-tranche-select" name="coa_tranche_display" style="width: 200px;">
        <option value="All">All</option>
    </select>
</div>

<div id="coa-graph" style="height:1000px" >
</div>

<script>
    $('#coa-tranche-select').select2();

    function getMaxStack(seriesArray) {

        const n = seriesArray[0].data.length;
        // init an array of zeros for each COA
        const totals = Array(n).fill(0);

        // sum up each series’ y at each COA index
        seriesArray.forEach(series => {
            series.data.forEach((pt, idx) => {
            const y = (pt && pt.y != null) ? pt.y : 0;
            totals[idx] += y || 0;
            });
        });

        // find the maximum stack total
        const maxStack = Math.max(...totals, 0);
        return maxStack / 7;
    }

    $('#coa-tranche-select').on('change', function () {
        const val = $(this).val();
        let selectedTranche = Number.isInteger(+val) ? +val - 1 : val;

        if ($('#coa-graph').highcharts()) {
            $('#coa-graph').highcharts().destroy();
        }
        
        if(selectedTranche !== "All") {

            // for mutiple COAs
            if (tranche_assignment[0]['tranche_assignment']) { // checks if there's multiple COAs based on formatting
                let allCoaSeriesChartData = JSON.parse(JSON.stringify(allCoaSeriesData[0]));
                let allCoaDrilldownChartData = [];
                let allValidGroups = new Set();
                for (let unitIndex in tranche_assignment) {

                    // setting up drilldown data (allCoaDrilldownChartData)
                    let tempCoaSeriesData = {
                        series: [],
                        categories: [],
                        drilldownSeries: [],
                    }

                    if (tranche_assignment[unitIndex] === null) {
                        allCoaSeriesChartData.series.forEach(obj => {
                            if (obj.data[unitIndex] === undefined) {
                                obj.data[unitIndex] = {
                                    y: null
                                }
                            }
                            else {
                                obj.data[unitIndex].y = null;
                            }
                        });

                        let allCoaSeriesDataCopy = JSON.parse(JSON.stringify(allCoaSeriesData[unitIndex])); 
                        tempCoaSeriesData.series = allCoaSeriesDataCopy.series;
                        tempCoaSeriesData.categories = allCoaSeriesDataCopy.categories;
                        tempCoaSeriesData.drilldownSeries = [];
                        allCoaDrilldownChartData.push({
                            'series': tempCoaSeriesData
                        });
                        continue;
                    }

                    const unitData = tranche_assignment[unitIndex];
                    const unitTranches = unitData['tranche_assignment'];

                    let validGroups = new Set(unitTranches[selectedTranche]?.program_group);
                    let validProgramIds = new Set(unitTranches[selectedTranche]?.program_id);
                    
                    for (const element of validGroups) {
                        allValidGroups.add(element);
                    }

                    // setting up drilldown data (allCoaDrilldownChartData)
                    let allCoaSeriesDataCopy = JSON.parse(JSON.stringify(allCoaSeriesData[unitIndex])); 
                    tempCoaSeriesData.series = allCoaSeriesDataCopy.series.filter(obj => validGroups.has(obj.name));
                    tempCoaSeriesData.categories = allCoaSeriesDataCopy.categories.filter(cat => validGroups.has(cat));
                    tempCoaSeriesData.drilldownSeries = allCoaSeriesDataCopy.drilldownSeries.filter(obj => validProgramIds.has(obj.name));
                    
                    //only update the drilldown series for the first coa because we are not adding drilldown series of first coa after the chart is created  
                    if (parseInt(unitIndex) === 0 || unitIndex === 0) {
                        allCoaSeriesChartData.drilldownSeries = allCoaSeriesDataCopy.drilldownSeries.filter(obj => validProgramIds.has(obj.name));
                    }

                    // getting the program group => score map
                    let programGroupScores = updateNewScorebyDrilldownSeries(tempCoaSeriesData.drilldownSeries);


                    allCoaDrilldownChartData.push({
                        'series': tempCoaSeriesData,
                        'scores': programGroupScores
                    });

                    allCoaSeriesChartData.series.forEach(obj => {
                        if (obj.data[unitIndex] === undefined) {
                            obj.data.push({y: null});
                        }
                        if (validGroups.has(obj.name)) {
                            obj.data[unitIndex].y = programGroupScores[obj.name] ?? null ;
                        }
                        else {
                            obj.data[unitIndex].y = null;
                        }
                    });
                }

                allCoaSeriesChartData.series = allCoaSeriesChartData.series.filter(
                    obj =>  allValidGroups.has(obj.name)
                );
   
                const tranchMax = selectedTranche=='full_keep'?'2400': getMaxStack(allCoaSeriesChartData.series);

                let chart = createCOAGraph(JSON.parse(JSON.stringify(allCoaSeriesChartData)), Object.keys(tranche_assignment).length, tranchMax);
                chart.xAxis[0].setCategories(global_titles);
                allCoaDrilldownChartData.forEach((drilldownSeries, unitIndex) => {
                    if (unitIndex > 0) {
                        updateDrilldownSeries(chart, drilldownSeries['series'], unitIndex);
                    }
                });
                        
            } else {  // for single COAs

                let validGroups = new Set(tranche_assignment[selectedTranche].program_group);
                let validProgramIds = new Set(tranche_assignment[selectedTranche].program_id);
                let allCoaSeriesDataCopy = JSON.parse(JSON.stringify(allCoaSeriesData));
                allCoaSeriesDataCopy.series = allCoaSeriesDataCopy.series.filter(obj => validGroups.has(obj.name));
                allCoaSeriesDataCopy.categories = allCoaSeriesDataCopy.categories.filter(cat => validGroups.has(cat));
                allCoaSeriesDataCopy.drilldownSeries = allCoaSeriesDataCopy.drilldownSeries.filter(obj => validProgramIds.has(obj.name));

                let programGroupScores = updateNewScorebyDrilldownSeries(allCoaSeriesDataCopy.drilldownSeries);
                allCoaSeriesDataCopy.series.forEach((mainSeries) => {
                    mainSeries.data[0].y =  programGroupScores[mainSeries.name];
                });

                const tranchMax = getMaxStack(allCoaSeriesDataCopy.series);

                createCOAGraph(JSON.parse(JSON.stringify(allCoaSeriesDataCopy)), 1, tranchMax);
            }
        } else { // wiuth All selection

            if (tranche_assignment[0]['tranche_assignment']) { // multiple coas
                
                let chart = {}

                for (let unitIndex in tranche_assignment) {
                    let tempCoaSeriesData = {
                        series: [],
                        categories: [],
                        drilldownSeries: [],
                    }

                    let allCoaSeriesDataCopy = JSON.stringify(allCoaSeriesData[unitIndex]);
                    allCoaSeriesDataCopy = JSON.parse(allCoaSeriesDataCopy);
                    tempCoaSeriesData.series = allCoaSeriesDataCopy.series;
                    tempCoaSeriesData.categories = allCoaSeriesDataCopy.categories;
                    tempCoaSeriesData.drilldownSeries = allCoaSeriesDataCopy.drilldownSeries;

                    let programGroupScores = updateNewScorebyDrilldownSeries(tempCoaSeriesData.drilldownSeries);
                    tempCoaSeriesData.series.forEach((mainSeries) => {
                        mainSeries.data[0].y =  programGroupScores[mainSeries.name];
                    });

                    if (Object.keys(chart).length === 0) {
                        chart = createCOAGraph(JSON.parse(JSON.stringify(tempCoaSeriesData)),  Object.keys(tranche_assignment).length);
                    }
                    else { 
                        updateDrilldownSeries(chart, tempCoaSeriesData, unitIndex);
                    }
                }
                chart.xAxis[0].setCategories(global_titles);
            }
            else {
                let chart = createCOAGraph(allCoaSeriesData, 1);
                chart.xAxis[0].setCategories(global_titles);
            }

     
        }

    });

    function updateNewScorebyDrilldownSeries(drilldownSeriesData) {
        let programGroupScores = {}
        drilldownSeriesData.forEach((mainSeries) => {
            if (programGroupScores[mainSeries.id] === undefined) {
                programGroupScores[mainSeries.id] = mainSeries?.data[0][1] ?? 0;
            }
            else {
                programGroupScores[mainSeries.id] += mainSeries?.data[0][1] ?? 0;
            }
        });
        return programGroupScores;
    }

    function updateDrilldownSeries(chart, seriesData, coaIdx) {
        let ddId= null
        chart.coaIndex = chart.coaIndex+1

        seriesData.series.forEach((mainSeries) => {


            let found = false;

            // Loop through the current series in the chart
            chart.series.forEach(existingSeries => {
                // Check if the existingSeries first data point's name matches mainSeries's first data point's name
                // existingSeries.data[coaIdx].y = programGroupScores[existingSeries.name] ?? 0;
                // console.log( 'existingSeries name', programGroupScores[existingSeries.name]);
                if (
                existingSeries.data && 
                existingSeries.data.length > 0 && 
                existingSeries.name === mainSeries.name

                ) {
                found = true;
                // If a data point already exists at chart.coaIndex, update it,
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
        chart.redraw();
    }


</script>