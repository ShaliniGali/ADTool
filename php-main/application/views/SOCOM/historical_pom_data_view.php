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

    #historical-pom-table-output .select2-selection__rendered {
        margin: unset !important
    }

    .historical-pom-switch {
        width: 25%;
    }

</style>

<div class="exec-container-child-1">
    <div class="d-flex justify-content-end">
        <div data-content-switcher class="bx--content-switcher historical-pom-switch" role="tablist" aria-label="Demo switch content">
        <button class="bx--content-switcher-btn"
            data-target="#current-pom-cycle-graph-container" role="tab"  aria-selected="true"  >
            <span class=bx--content-switcher__label>Current POM Cycle</span>
        </button>
        <button class="bx--content-switcher-btn bx--content-switcher--selected"
            data-target="#historical-pom-graph-container" role="tab"  >
            <span class=bx--content-switcher__label>Historical POM</span>
        </button>
        </div>
    </div>

    <div id="current-pom-cycle-graph-container" hidden>
        <div class="d-flex flex-column justify-content-center" id="program-description">
            <h1><?= $program; ?></h1>
            <!-- <p>Program description </p> -->
        </div>

        <div class="chart-container" id="current-pom-cycle-graph"> Chart1</div>
    </div>


    <div id="historical-pom-graph-container">
        <div class="d-flex flex-column justify-content-center" id="program-description">
            <h1><?= $program; ?></h1>
            <!-- <p>Program description </p> -->
        </div>

        
        <div class="chart-container" id="historical-pom-graph"> Chart2</div>
    </div>

    <?php $this->load->view('SOCOM/historical_pom_table_view', $data['historical_pom']); ?>
</div>


<script>
    CarbonComponents.ContentSwitcher.init()
    $(function() {
        let historicalGraphData = <?= json_encode($data['historical_graph']); ?>;
        let currentGraphData = <?= json_encode($data['current_pom_cycle_graph']); ?>;
        initHistoricalGraph('current-pom-cycle-graph', currentGraphData);
        initHistoricalGraph('historical-pom-graph', historicalGraphData);
    })
</script>