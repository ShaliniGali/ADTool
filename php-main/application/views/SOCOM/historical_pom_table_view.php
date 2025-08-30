<div class="overflow-auto">
    <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" 
    id="historical-pom-table-output" class="" style="text-align:center;">
        <thead>
            <?php foreach($headers as $header): ?>
                <th class="bx--table-header-label"><?= $header['title']; ?></th>
            <?php endforeach; ?>
        </thead>
    </table>
</div>


<script>
    var table_dropdown = {}
    var historicalData = <?=json_encode($data);?>;
    var historicalHeader = <?=json_encode($headers);?>;
    var yearIndex = <?=json_encode($yearIndex);?>;
    var indexOfYear = <?= $indexOfYear; ?>;
    var yearList = <?=json_encode($yearList);?>;
    var sharedColumnRows = [0, 1, 2]
    if (historical_pom_dt_object) {
        historical_pom_dt_object.destroy();
    }
    var historical_pom_dt_object = initHistoricalDatatable(
        'historical-pom-table-output',
        historicalData,
        historicalHeader,
        yearIndex,
        yearList,
        indexOfYear,
        sharedColumnRows
    );

    $("#historical-pom-table-output .selection-dropdown").select2({
        placeholder: "Select an option",
        width: '16vw'
    });
</script>