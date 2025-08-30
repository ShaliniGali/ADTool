
<div class="table-container ml-auto mr-auto" id="coa-output-table-container">
<table id="output-table"
 class="override-tables bx--data-table 
 ml-0
 " >
    <thead>
      <tr>
        <?php $headers = $table_headers;?>
        <?php foreach ($headers as $header_key => $header_name): ?>
          <th <?php if(is_numeric($header_key) || $header_name == 'FYDP'): ?>
                    style="text-align: center !important"<?php endif;?>>
                  <span class="bx--table-header-label"><?= $header_name; ?></span>
                </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tfoot></tfoot>
  </table>
</div>

<div class="table-container ml-auto mr-auto d-none" id="coa-override-output-container">
<table id="coa-output-override-table"
 class="override-tables bx--data-table w-100 
 ml-0
 " >
    <thead>
      <tr>
        <?php $headers = array_map(
                            function($item) {  return $item["data"]; },
                            $datatable_headers_override
                        );
        ?>
        
        <?php foreach ($headers as $header_key => $header_name): ?>
          <th <?php if(is_numeric($header_key) || $header_name == 'FYDP'): ?>
                    style="text-align: center !important"<?php endif;?>>
                  <span class="bx--table-header-label"><?= $header_name; ?></span>
                </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tfoot></tfoot>
  </table>
</div>

<script>
    var year_array = <?= isset($year_array) ? json_encode($year_array) : '[]';?>;
    var current_user = <?=isset($current_user) ?  json_encode($current_user) : 'null';?>;
    var tooltipColumnRight = 3;
    var user_id =  '<?=isset($user_id) ?  $user_id : null;?>';
    var scenario_id =  <?=isset($scenario_id) ?  $scenario_id : null;?>;
    var overrideScoreIndex = [], scoreIndex = [], yearIndex = [], overrideHiddenScoreIndex = [], hiddenScoreIndex = [];
    var data = <?= json_encode($table_data) ;?>;
    var headers = <?= json_encode($data_headers) ;?>;
    var override_headers = <?=json_encode($datatable_headers_override);?>;
    var override_data = <?=json_encode($override_data);?>;
    var override_table_metadata = <?=json_encode($override_table_metadata);?>;
    var hidden_score_column = <?= json_encode($hidden_score_column);?>;
    for(let index = 0; index < override_headers.length; ++index){
        if(typeof(override_headers[index]) === 'number' || override_headers[index] === 'FYDP'){
            yearIndex.push(index);
        }
        if (
            typeof override_headers[index]['data'] === 'string' && 
            ['POM Score', 'Guidance Score'].indexOf(override_headers[index]['data']) !== -1
        ) {
            scoreIndex.push((index-2));
            overrideScoreIndex.push(index);
        }
        if (
            typeof override_headers[index]['data'] === 'string' && 
            hidden_score_column.indexOf(override_headers[index]['data']) !== -1
        ) {
            hiddenScoreIndex.push((index-2));
            overrideHiddenScoreIndex.push(index);
        }
    }

    // hide pom sponsor, ass area code column and exection manager code
    var hiddentColumnIdx = [2, 4];
    hiddentColumnIdx.forEach((index) => {
        hiddenScoreIndex.push(index);
        overrideHiddenScoreIndex.push(index + 2);
    });

    var current_years = parseInt(year_array[0]);
    var indexOfYear = headers.findIndex(item => item.data == current_years);
    loadTableMetadata(override_table_metadata, scenario_id)
    SCENARIO_STATE[scenario_id] = '<?= $state; ?>';
    if(output_table != undefined){
        output_table.clear();
        output_table.destroy();
    }
    var output_table = initDatatable(
        'output-table', data, headers, indexOfYear, scoreIndex, hiddenScoreIndex
    );
    if(coa_output_override_table != undefined){
        coa_output_override_table.clear();
        coa_output_override_table.destroy();
    }
    var indexOfOverrideYear = override_headers.findIndex(item => item.data == current_years);

    for(let index = indexOfOverrideYear; index < override_headers.length - 1; ++index){
        yearIndex.push(index);
    }
    var coa_output_override_table = initDatatable(
        'coa-output-override-table', override_data, override_headers,
        indexOfOverrideYear, overrideScoreIndex, overrideHiddenScoreIndex
    );
    updateCellStyle(
        scenario_id,
        overrided_budget_impact_history,
        override_data,
        indexOfOverrideYear
    );

    var editor_columns = <?= isset($datatable_field) ? json_encode($datatable_field) : []; ?>;
    editor_columns.forEach((element, index) => {
        element['type'] = 'text';
    })
    var editor_table;
    initEditorDataTable(
        'coa-output-override-table',
        editor_columns,
        indexOfOverrideYear,
        year_array,
        scenario_id,
        user_id
    );
    $(`#coa-output-override-table_length`).addClass('d-flex flex-row');
    var editedCell;
    coa_output_override_table.on('click', 'tbody td:not(:first-child)', function (e) {
        editedCell = this;
        let index = coa_output_override_table.cell( editedCell ).index();
        let editedHeader = coa_output_override_table.column(index.column).header().textContent.trim();
        $(`#DTE_Field_${editedHeader}`).removeClass('bx--text-input--invalid');
        $(".invalid-text").remove();
        if (!uneditable_column.includes(editedHeader) && (index.row != coa_output_override_table.rows().count() - 1)
            && SCENARIO_STATE[scenario_id] === 'IN_PROGRESS') {
                editor_table.inline(this);
        }
    });

    function initDatatable(container, tableData, tableHeaders, indexOfFirstYear, sIndex, hIndices) {
        // Total Data
        const footerData = tableData.find(data => data?.['RESOURCE CATEGORY'] === 'Committed Grand Total $K');
        indexOfFirstYear = indexOfFirstYear - hIndices.length;
        return $(`#${container}`).DataTable({
            order: [],
            iDisplayLength: 10,   // number of rows to display
            rowHeight: '75px',
            data:tableData,
            columns: tableHeaders,
            columnDefs: [
                {
                    targets: 0,
                    orderable: container === 'coa-output-override-table' ? false : true,
                },
                {
                    targets: yearIndex,
                    className: 'dt-body-right',
                    render: function (data, type, row) {
                        return parseInt(data);
                    }
                },
                {
                    targets: sIndex,
                    className: 'dt-body-right',
                    render: function (data, type, row) {
                        if (data !== '') {
                            return Number(data).toFixed(2);
                        }
                        else {
                            return data
                        }
                    }
                },
                {
                    targets: '_all',
                    className: 'dt-body-center'
                },
                {
                    targets: hIndices.concat([Object.keys(tableData[0]).length - 1]),
                    visible: false
                }
            ],
            'rowCallback': function(row, data, index){
                const year_total_array = [...year_array, 'TOTAL'];
                for(let i=0; i < year_total_array.length; i++){
                    if(data[year_total_array[i]] < 0){
                        $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('background-color', '#f65959');
                        $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('color', '#FFF');
                    } else if (
                        data[year_total_array[i]] === null
                        ) {
                        cellValue = 0;
                        $(row).find('td:eq(' + (i + indexOfFirstYear) + ')').text(cellValue);
                        $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('background-color', '#01b2f3');
                        $(row).find('td:eq('+(i+indexOfFirstYear)+')').css('color', '#FFF');
                    }
                }
                if (data?.['RESOURCE CATEGORY'] === 'Committed Grand Total $K') {
                    // $(row).find('td').each(function() {
                    //     let cellValue = $(this).text();
                    //     if (!(cellValue < 0)) {
                    //         $(this).addClass('grey-highlight datatable-column');
                    //     }
                    // });
                }
            },
            initComplete: () => {
                // disableDownloadDetailed(false);
            },
            createdRow: (row, data, dataIndex) => {
                const uniqueId = data?.DT_RowId || randomId();
                $(row).attr('id', `${uniqueId}`)

                if (data?.['RESOURCE CATEGORY'] === 'Committed Grand Total $K') {
                    // Hide 'Total' row since we will be adding that to the footer
                    $(row).hide();
                }
            },
            drawCallback: function (settings) {
                const table = $(this);

                const rows = table.DataTable().rows().every(function(rowIdx, tableLoop, rowLoop) {
                    const data = this.data();
                    if (data['RESOURCE CATEGORY'] === 'Committed Grand Total $K') {
                        // Add 'Total' row to the footer 
                        table.find('tfoot').empty();
                        table.find('tfoot').append(this.node());
                        table.find('tfoot tr').show();
                    }
                });
            }
        });
    }

    function updateCellStyle(session_id, session, newData, indexOfOverrideYear) {
        if (session && session?.[session_id]) {
            for (const [table, value] of Object.entries(session[session_id])) {
                rowLatestInfo = {'timestamp': 0};
                if (session?.[session_id]?.[table]) {
                    for (const [row, rowInfo] of Object.entries(session[session_id][table])) {
                        if (session?.[session_id]?.[table]?.[row]) {
                            for (const [year, cellInfo] of Object.entries(session[session_id][table][row])) {
                                let tableObject = this[`${table}_override_table`];
                                let metaDataFuncName = `update_${table}_override_table_metadata`;
                                this[metaDataFuncName](
                                    session_id,
                                    tableObject,
                                    table,
                                    year,
                                    row,
                                    indexOfOverrideYear
                                );                            
                            }
                        }
                    }
            }
            }
        }
        
    }

    function getTooltipInfo(session_id = null, tableObject=null, table=null, row=null,  header=null) {
        tooltipInfo = {}
        if (tableObject == null) {
            let currentDate = new Date()
            tooltipInfo = {
                user: current_user,
                date: currentDate.toLocaleDateString() + ' ' + currentDate.toLocaleTimeString(),
                direction: 'left'
            }
        }
        else {
            let info = overrided_budget_impact_history[session_id][table][row][header];
            let tooltipDate = new Date(info['timestamp']);
            tooltipInfo = {
                user: info['user_name'] || current_user,
                date: tooltipDate.toLocaleDateString() + ' ' + tooltipDate.toLocaleTimeString(),
                timestamp: info['timestamp'],
                direction: 'left'
            };
        }
        return tooltipInfo;
    }

    function getColumnIndexByHeader(tableObject, headerTitle) {
        headerTitle = headerTitle.replace("_", " ");
        return tableObject.column(':contains("' + headerTitle + '")').index();
    }

    function updateExportData() {
        // combination_map[combination_id].pbes = pbes_override_dt_object[id].data().toArray();
    }

    function isNumeric(value) {
        return (value !== null) || !isNaN(value) && !isNaN(parseFloat(value)); 
    }

    function addClassToEditedCell(cell, tooltipInfo={}) {
        cell.addClass('ember-cell');
        cell.addClass(
            `bx--tooltip__trigger bx--tooltip--a11y bx--tooltip--${tooltipInfo['direction']} bx--tooltip--align-start`
        );
        let tooltipMessage = `Modified By: ${tooltipInfo['user']}<br>Last Modified:<br>${tooltipInfo['date']}`;

        if (cell.find('span.bx--assistive-text').length >= 1) {
            cell.find('span.bx--assistive-text').html(tooltipMessage)
        }
        else {
            let cellValue = isNumeric(cell.html()) ? cell.html() : 0; 
            cell.html(
                cellValue +
                `<span class="bx--assistive-text">${tooltipMessage}</span>`
            )
        }
    }
    
    function update_coa_output_override_table_metadata(
        session_id,
        tableObject,
        table,
        year,
        row,
        indexOfOverrideYear
    ) {
        let latestInfo = {'timestamp': 0}
        if (tableObject) {
            const indexOfResource = override_headers.findIndex(item => item.data.toUpperCase() === "RESOURCE CATEGORY");
            let columnIndex = getColumnIndexByName(tableObject, year)
            let grandTotalRowIndex = tableObject.rows().indexes().filter(function (value, index) {
                return tableObject.cell(value, indexOfResource).data() === 'Committed Grand Total $K';
            })[0];
            let rowIndex = parseInt(row);
            tableObject.rows().nodes().each(function (rowNode, index) {
                if (rowNode.id === row) {
                    rowIndex = index;  // Get the row index
                }
            });
            let tooltipInfo = getTooltipInfo(session_id, tableObject, table, row, year)
            addClassToEditedCell($(tableObject.cell(rowIndex,columnIndex).node()), tooltipInfo);
            if (latestInfo['timestamp'] < tooltipInfo['timestamp']) {
                latestInfo = tooltipInfo;
            }

            if (rowLatestInfo['timestamp'] < tooltipInfo['timestamp']) {
                rowLatestInfo = tooltipInfo;
            }

            if ( (columnIndex >= indexOfOverrideYear)
                && (columnIndex < (indexOfOverrideYear + year_array.length))) {
                addClassToEditedCell($(tableObject.cell(
                    rowIndex, indexOfOverrideYear + year_array.length).node()), rowLatestInfo);
                let grandTotalCellValue = tableObject.cell(grandTotalRowIndex, columnIndex).data();
                let cellValue = tableObject.cell(rowIndex, columnIndex).data() ?? 0;
                let yearGrandTotalColumnIndex = indexOfOverrideYear + year_array.length
                let totalGrandTotalValue = tableObject.cell(grandTotalRowIndex, yearGrandTotalColumnIndex).data();
                tableObject.cell(grandTotalRowIndex, columnIndex).data(grandTotalCellValue).draw(false);
                let isWithinBudget = isWithinBudgetFunc(
                    budget_uncommitted_override_table,
                    year,
                    totalGrandTotalValue,
                    grandTotalCellValue
                );
                addClassToEditedGrandCell(
                    $(tableObject.cell(grandTotalRowIndex,columnIndex).node()),
                    isWithinBudget,
                    'proposal-cell',
                    tooltipInfo,
                    true
                )
                addClassToEditedGrandCell(
                    $(tableObject.cell(grandTotalRowIndex,yearGrandTotalColumnIndex).node()),
                    isWithinBudget,
                    'proposal-cell',
                    tooltipInfo,
                    true
                )
            }
        }
    }
    
    function update_budget_uncommitted_override_table_metadata(
        session_id,
        tableObject,
        table,
        year,
        row,
        indexOfOverrideYear
    ) {

    }

    // delete row
    $('#coa-output-override-table').on('click', 'button.coa-delete-row-btn', function () {
        let deleteRowIndex = coa_output_override_table.row( $(this).parents('tr') ).index();
        resetEditorDefault(deleteRowIndex);
        updateGrandTotal(coa_output_override_table, deleteRowIndex, 'delete');
        coa_output_override_table
        .row( $(this).parents('tr') )
        .remove()
        .draw(false);
    });
</script>


<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'gear_override_modal',
    'role' => 'fy_override_by_percentage',
    'title' => 'Override by Percentage',
    'title_heading' => '',
    'basic_modal_body_id' => 'gear_override_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/optimizer/gears_percentage_view', [
        'id' => 'gears_view'
    ], true),
    'close_event' => 'function() { 
                $("#gear_override_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }'
]);
$modal_id = null;
?>