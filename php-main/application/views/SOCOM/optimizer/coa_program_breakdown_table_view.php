<div id="coa-program-breakdown-<?= $type ?>-container" class="white-background p-3 mr-3">
    <h3 class="pt-2 pb-3" style="text-align: center;"><?= $title; ?></h3>
    <table id="coa-program-breakdown-<?= $type ?>-table" class="bx--data-table ml-0">
        <thead>
        <tr>
            <?php foreach ($headers as $header): ?>
                <th>
                    <span class="bx--table-header-label"><?= $header['title']; ?></span>
                </th>
            <?php endforeach; ?>
        </tr>
        </thead>
    </table>
</div>

<script>
    var type = '<?= $type ?>';
    var id = `coa-program-breakdown-${type}-table`;
    var headers = <?= json_encode($headers); ?>;

    if(Object.keys(program_breakdown_table).length !== 0){
        program_breakdown_table.destroy();
    }
    program_breakdown_table = initDetailedSummaryDataTable(id,[],headers);

</script>