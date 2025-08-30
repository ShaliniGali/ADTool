<style>
    #option-list thead th {
        border: 1px solid #111;
    }

    .blank-header {
        border-top: unset !important;
        border-left: unset !important;
        background: #f4f4f4 !important;
    }
</style>

<table id="option-list" class="bx--data-table w-100 table-border">
    <thead style="border-collapse:collapse; ">
        <tr style="border-collapse:collapse; ">
            <th colspan="3" class="blank-header"></th>
            <th colspan="5" style="text-align: center;">Dollars (Thousands)</th>
            <th></th>
            <th colspan="2" style="text-align: center;">Weighted Scores</th>
            <th></th>
            <th colspan="2" style="text-align: center;">StoRM Scores</th>
        </tr>
        <tr>
            <?php foreach ($table_headers as $header) : ?>
                <th><?= $header ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <!-- Table body (loaded by ajax call) -->


    </tbody>
</table>
