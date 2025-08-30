
<?php $budget_uncommitted_loop_headers = array_map(
        function($item) {  return $item["data"]; },
        $budget_uncommitted_table_headers
    );
?>
<div>
    <table id="budget_uncommitted_table" class="bx--data-table ml-0">
    </table>
</div>

<div>
    <table id="budget_uncommitted_override_table" class="bx--data-table ml-0 d-none">
        <thead>
        <tr>
            <?php foreach ($budget_uncommitted_loop_headers as $header_key => $header_name): ?>
            <th <?php if(is_numeric($header_key) || $header_name == 'FYDP'): ?>
                        style="text-align: center !important"<?php endif;?>>
                    <span class="bx--table-header-label"><?= is_numeric((int)$header_name) ? ('FY'. substr($header_name, -2)) : $header_name; ?></span>
                    </th>
            <?php endforeach; ?>
        </tr>
        </thead>
    </table>
</div>

<script>
    if(budget_uncommitted_table != undefined){
        budget_uncommitted_table.clear();
        budget_uncommitted_table.destroy();
    }
    var budget_uncommitted_data = <?= json_encode($budget_uncommitted_table_data); ?>;
    var budget_uncommitted_headers = <?= json_encode($budget_uncommitted_table_headers); ?>;
    var budget_uncommitted_table = initDataTable(
        'budget_uncommitted_table', budget_uncommitted_data, budget_uncommitted_headers
    )

    if(budget_uncommitted_override_table != undefined){
        budget_uncommitted_override_table.clear();
        budget_uncommitted_override_table.destroy();
    }
    var budget_uncommitted_override_data = <?= json_encode($budget_uncommitted_override_table_data); ?>;
    var budget_uncommitted_override_headers = <?= json_encode($budget_uncommitted_table_headers); ?>;

    var budget_uncommitted_override_table = initDataTable(
        'budget_uncommitted_override_table', budget_uncommitted_override_data, budget_uncommitted_override_headers
    )
    <?php
    $newIds = [];
    foreach($new_program_data as $npdi => $npd):
     /*   $program_value['ID'] . "_" . $program_value['EOC_CODE'] . "_"
                . str_replace(' ', '/', $program_value['RESOURCE_CATEGORY_CODE'])
                . '_' . $program_value['OSD_PE'] ;*/
       
        $id = $npd['ID'];
        
        $newIds[$id] = ($type_of_coa === 'ISS_EXTRACT'  ? $npd['DELTA_AMT'] : $npd['RESOURCE_K']);
    ?>
    <?php endforeach;
    unset($new_program_data);
    ?>

    <?php
    $originalIds = [];
    foreach($original_program_data as $npdi => $npd):
        $id = $npd['ID'];
        
        $originalIds[$id] = ($type_of_coa === 'ISS_EXTRACT'  ? $npd['DELTA_AMT'] : $npd['RESOURCE_K']);
    ?>
    <?php endforeach;
    unset($original_program_data);
    ?>
    
    var newProgramData = JSON.parse('<?= json_encode($newIds); ?>');
    var originalProgramData = JSON.parse('<?= json_encode($originalIds); ?>');

    function initDataTable(container, tableData, tableHeaders) {
        return $(`#${container}`).DataTable({
            ordering: false,
            dom: 't',
            rowHeight: '75px',
            data:tableData,
            columns: tableHeaders
        });
    }

</script>