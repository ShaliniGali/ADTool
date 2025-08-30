<style>
    .dt-body-right{
        text-align: right !important;
    }
    .dt-body-center{
        text-align: center !important;
    }
    .hidden {
        display: none !important;
    }
    .dropdown-invalid-text {
        color: #da1e28;
        margin: .25rem 0 0;
        font-size: .75rem;
    }
    .invalid-outline {
        outline: 2px solid #da1e28;
    }
    #coa-table-insert-container .bx--label {
        font-size: 0.9rem;
        color: #000000;
    }

    [id^='text-input-'].bx--text-input:disabled {
        border-bottom: 1px solid #282222;
    }

    .select2-container.select2-container--default.select2-container--open {
        z-index: 99999;
    }

    #coa-table-insert-container .select2-selection.select2-selection--single{
        align-items: center !important;
        display: flex !important;
    }

</style>



<div id="coa-table-insert-container" class="my-2">
    <div class="d-flex dropdown-values flex-column w-100">
        <?php $row = 0; ?>
    <?php foreach ($headers as $header => $title) : ?>
        <?php if($row % 2 == 0): ?>
            <div class="d-flex flex-row">
        <?php endif ?>
           
                <?php if(strpos($header, 'FY') === false && !in_array($header, $weighted_score_keys)): ?>
                    <div class="bx--form-item m-1"
                        style="min-width: 14rem; flex: 1;"
                    >
                        <label for="text-input-<?= $header; ?>" class="bx--label"><?= $title ?> * </label>
                        <select onchange="<?=  "updateInsertCoaTableRowDropdown('${header}', '${scenario_id}', '${current_year}', '${type_of_coa}')"; ?>" class="form-control selection-dropdown"
                                id="text-input-<?= $header; ?>" <?= ($header === 'PROGRAM_CODE') ? '': 'disabled';?>>
                            <option></option>

                            <?php if($header === 'PROGRAM_CODE'): ?>
                                <?php foreach($program_code_list as $index => $value): ?>
                                    <option data-pom="<?= $value['POM_SPONSOR_CODE']; ?>" value="<?= $value['PROGRAM_CODE'] ?>"><?= $value['PROGRAM_CODE'] ?></option>
                                <?php endforeach; ?>
                            <?php endif ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div data-text-input 
                        class="bx--form-item bx--text-input-wrapper m-1" style="flex: 1;">
                        <label for="text-input-<?= $header; ?>" class="bx--label">
                            <?= $header; ?> *
                        </label>
                        <div class="bx--text-input__field-wrapper" data-invalid>
                            <svg id="invalid-icon-<?= $header; ?>" focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--text-input__invalid-icon hidden" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M8,1C4.2,1,1,4.2,1,8s3.2,7,7,7s7-3.1,7-7S11.9,1,8,1z M7.5,4h1v5h-1C7.5,9,7.5,4,7.5,4z M8,12.2	c-0.4,0-0.8-0.4-0.8-0.8s0.3-0.8,0.8-0.8c0.4,0,0.8,0.4,0.8,0.8S8.4,12.2,8,12.2z"></path><path d="M7.5,4h1v5h-1C7.5,9,7.5,4,7.5,4z M8,12.2c-0.4,0-0.8-0.4-0.8-0.8s0.3-0.8,0.8-0.8	c0.4,0,0.8,0.4,0.8,0.8S8.4,12.2,8,12.2z" data-icon-path="inner-path" opacity="0"></path>
                            </svg>
                            <input id="text-input-<?= $header; ?>" type="text"
                            class="bx--text-input bx--text-input--light"
                            placeholder=""
                            value="0"  <?= in_array($header, $weighted_score_keys) ? 'disabled' : ''; ?>>
                        </div>
                        <div class="bx--form-requirement hidden" id="invalid-text-<?= $header; ?>">
                            Invalid Input
                        </div>
                    </div>
                <?php endif; ?>
        <?php if($row % 2 == 1): ?>
            </div>
        <?php endif ?>
    <?php
            $row++;
        endforeach;
    ?>
    </div>
</div>



<script>
//CarbonComponents.Dropdown.init();

$(".selection-dropdown").select2({
    placeholder:'Select an option',
    dropdownParent: $('#coa-table-insert-modal-container')
});

var year_lists = <?= json_encode($year_headers); ?>;
var current_year = <?= $current_year; ?>;
var headers_map = <?= json_encode($headers_map); ?>;
var scenario_id = <?= $scenario_id; ?>;
var user_id = <?= $user_id; ?>;
var type_of_coa = '<?= $type_of_coa; ?>';

for (const year in year_lists) {
    $(`#text-input-${year}`).on("change", () => validateYear(year, headers_map));
}

$('#text-input-EOC_CODE').on('change', function(event, type) {
    if (type !== 'UPDATE_DROPDOWN') {
        updateInsertCoaTableRowDropdown('EOC_CODE', scenario_id, current_year, type_of_coa);
    }
});

// var yearColumn = [];
// for(let index = 0; index < dataHeaders.length; ++index){
//     if(typeof(dataHeaders[index].data) === 'number'){
//         yearColumn.push(dataHeaders[index].data);
//     }
// }

$('#insert-coa-row-btn').unbind();
$('#insert-coa-row-btn').on('click', function () {
    for (const year in year_lists) {
        validateYear(year, headers_map);
    }

    if ($('.bx--text-input').hasClass('bx--text-input--invalid')) {
        $('#insert-coa-row-btn').prop("disabled", true)
        return;
    }

    let insertData = { ...coa_output_override_table.row(0).data() };

    insertData['FYDP'] = 0;
    for (const [key, value] of  Object.entries(insertData)){
        let new_value = $(`#text-input-${headers_map[key]}`).val() != undefined ?  $(`#text-input-${headers_map[key]}`).val() : insertData[key];

        if (headers_map[key] === 'POM_SPONSOR_CODE') {
            let event_name = $('#text-input-EVENT_NAME').val();
            if (current_pom_sponsor_code.length > 0)  {
                new_value = current_pom_sponsor_code[0];
            }
        }
        if (!isNaN(parseInt(key))) {
            insertData[key] = parseInt(new_value);
            insertData['FYDP'] += parseInt(insertData[key]);
        }
        else {
            if (typeof value === 'number') {
                insertData[key] = parseInt(new_value)
            }
            else {
                insertData[key] = new_value;
            }
        }
    }
    insertData[''] =  `<button class="bx--btn bx--btn--ghost bx--btn--sm coa-delete-row-btn">
        <i class="fa fa-trash" aria-hidden="true"></i></button>`;

    // place grand total to last row
    let lastRow = coa_output_override_table.rows().count() - 1;
    
    // update newProgramData
    rowDataId = currentEOCPID;
    
    insertData['DT_RowId'] = rowDataId;
    newProgramData[rowDataId] = {};
    for (const year in year_lists) {
        newProgramData[rowDataId]['20' + year.slice(-2)] = insertData['20' + year.slice(-2)] ?? 0;
    }

    var rowData = coa_output_override_table.row(lastRow).data();
    coa_output_override_table.rows(lastRow).remove().draw(false);
    coa_output_override_table.row.add(insertData).draw();
    coa_output_override_table.row.add(rowData).draw();
    
    updateGrandTotal(coa_output_override_table, coa_output_override_table.rows().count() - 2, 'insert');

    let formattedData = formatInsertData(insertData);
    updateOverridedBudgetImpactHistory(
        scenario_id,
        insertData['DT_RowId'],
        user_id,
        formattedData
    );

    coa_output_override_table.columns().header().toArray().forEach((value, index) => {
        if (index != 0) {
            let tooltipInfo = getTooltipInfo();
            if (index < tooltipColumnRight) {
                tooltipInfo['direction'] = 'right';
            }
            addClassToEditedCell($(coa_output_override_table.cell(lastRow, index).node()), tooltipInfo)
        }
    })

    //updateExportData();
    $('#close-coa-row-btn').trigger('click');
})

function formatInsertData(data) {
    let formattedData = {};
    for (const [key, value] of Object.entries(data)) {
        if (!(key ==  "")){
            formattedData[key.replace(' ', '_')] = value;
        }
    }
    return formattedData;
}
</script>