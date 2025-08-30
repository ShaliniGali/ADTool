<style>
    .green-cell{
        background-color: #04d704;
    }

    .red-cell{
        background-color: red;
    }

    .yellow-cell{
        background-color: yellow;
    }

    .red-text{
        color: red;
    }

    .table-container {
        justify-content: center;
    }

    #program-description{
        text-align: center;
    }

    #eoc-historical-pom-table-output .select2-selection__rendered  {
        margin: unset !important
    }

</style>


<div class="exec-container-child-1">
    <div class="d-flex flex-column justify-content-center" id="program-description">
        <h1><?= $program; ?></h1>
        <!-- <p>Program description </p> -->
    </div>
    <div class="chart-container" id="eoc-historical-pom-data-graph"> Chart1</div>
    <?php $this->load->view('SOCOM/eoc_historical_pom_table_view', $data['historical_pom']); ?>
</div>


<script>
    $(function() {
        let graphData = <?= json_encode($data['historical_graph']); ?>;
        Highcharts.chart('eoc-historical-pom-data-graph', {
            title: {
                text: `${graphData['program']} Changes from 26EXT to 26ZBT`,
            },
            xAxis: {
                categories: graphData['categories']
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
                    pointStart: graphData[0]
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
            series: graphData['data'],
            credits:{
                enabled:false
            },
            exporting: {
                enabled: false
            }
        });  
    })
</script>