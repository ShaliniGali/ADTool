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

    .ember-cell{
        background: #FFFF00 !important;
        color: black !important;
        font-weight: bold;
    }

    #program-table-container .select2-selection__rendered {
        margin: unset
    }

    #table-filter-container {
        width: 30%;
        margin-left: auto;
        position: relative;
        top: 35px;
        z-index: 10;
    }

    #approval-status-dropdown > span:nth-child(3) {
        width: 22vw !important;
    }

</style>

<div class="m-3">
    <?php if (isset($headers) && isset($data) && !empty($data) ): ?>
        
        <div id="table-filter-container" class="mb-2 d-flex flex-row justify-content-end">
            <div class="d-flex flex-column mt-5">
                <div id="approval-status-dropdown" class="d-flex flex-column mr-2" >
                    <label for="approval-status-1" class="mb-1 bx--label medium-label mr-2 flex-row" >Approval Action Status</label>
                    <select
                        id="approval-status-1"
                        type="approval-status"
                        combination-id=""
                        class="selection-dropdown"
                        multiple="multiple"
                        onchange="table_filter_onchange(1, 'approval-status')"
                        style="width: 25vw;"
                    >
                        <option value="COMPLETED" selected>COMPLETED</option>
                        <option value="PENDING" selected>PENDING</option>
                    </select>
                </div>

                <div class="d-flex flex-row mt-2">
                    <input id="checkbox_visibility" class="checkbox mr-2 mb-1 flex-row" type='checkbox' onclick="isChecked()" /> 
                    <label for="checkbox_visibility" class="mb-1 bx--label medium-label flex-row">Show Program List Including Zero FYDP Funding</label>
                </div>
            </div>
        </div>

       
        
        <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" id="program-table-output" class="" style="text-align:center;">
            <thead>
                <?php foreach($headers as $header): ?>
                    <th class="bx--table-header-label"><?= $header['title']; ?></th>
                <?php endforeach; ?>
            </thead>
        </table>
    <?php elseif(isset($headers) && empty($data)): ?>
        <div class="d-flex w-100 p-2">
            <h2>No JCA Alignment</h2>
        </div>
    <?php else: ?>
        <div class="d-flex w-100 p-2">
            <h2>Click Apply Filter to see data</h2>
        </div>
    <?php endif;?>
</div>



<script>

<?php if (isset($headers) && isset($data)): ?>
    var table_dropdown = {}
    var summaryData = <?=json_encode($data);?>;
    var summaryHeaders = <?=json_encode($headers);?>;
    var yearIndex = <?=json_encode($yearIndex);?>;
    var indexOfYear = <?= $indexOfYear; ?>;
    var yearList = <?=json_encode($yearList);?>;
    var editorColumns = <?=json_encode($editor_columns);?>;
    var columnIndexMap = {}
    var rowspanCount = <?=$rowspan;?>;
    var rowPerPage = <?=$rowPerPage;?>;
    var lengthMenu = <?=json_encode($lengthMenu);?>;

    var sharedColumnRows = [0, 1, summaryHeaders.length - 4, summaryHeaders.length - 3, summaryHeaders.length - 2];
    Object.entries(summaryHeaders).forEach(([key, value]) => {
        columnIndexMap[value['data']] = key;
    });


    if (!jQuery.isEmptyObject(summary_dt_object)){
        summary_dt_object.destroy();
    }

    if (summaryData !== null && summaryData.length > 0) { 

        var summary_dt_object = initDatatable(
            'program-table-output',
            summaryData,
            summaryHeaders,
            yearIndex,
            yearList,
            indexOfYear,
            sharedColumnRows,
            rowspanCount,
            rowPerPage,
            lengthMenu
        );


        var summary_editor_object;
        if(P1_FLAG=='1'){
            summary_editor_object = new Editor({
                fields: editorColumns,
                table: '#program-table-output'
            });
        }
        else{
            summary_editor_object = new DataTable.Editor({
                fields: editorColumns,
                table: '#program-table-output'
            });
        }
        $("#program-table-output_length" ).addClass('d-flex flex-row');

        var editedCell;
        summary_dt_object.on('click', '.editable', function (e) {
            editedCell = this;
            //summary_editor_object.inline(this);
        }).on('draw', function () {
            $(".selection-dropdown").select2({
                placeholder: "Select an option",
                width: '16vw'
            });
        });

        summary_editor_object.on('preSubmit', function (e, data, action) {
            let editrow = Object.keys(data.data)[0];
            let editColumn = Object.keys(data.data[editrow])[0];
            const newCellValue = data.data[editrow][editColumn];

            if (typeof(parseInt(editColumn)) === 'number' && !isNaN(data.data[editrow][editColumn])) {
                let oldValue = parseInt(summary_dt_object.row(editrow - 1).data()[editColumn]);
                let newValue = parseInt(data.data[editrow][editColumn]);
                let currentGrandTotal = parseInt(summary_dt_object.row(editrow - 1).data()['fydp']);

                data.data[editrow]['fydp'] = currentGrandTotal - oldValue + newValue;
                data.data[editrow][editColumn] = parseInt(data.data[editrow][editColumn]);
            }

            data.data[editrow]['status'] = 'In Review';
        })
        .on('postEdit' , function ( e, json, data ) {

            //change style to the edited cell
            addClassToEditedCell($(editedCell))

            //change style to FYDP
            let index = summary_dt_object.cell( editedCell ).index();
            if (index.column >= indexOfYear) {
                addClassToEditedCell(
                    $(summary_dt_object.cell(index.row, columnIndexMap['fydp']).node())
                );
            }

            //Change style to Approval Action Status
            addClassToEditedCell(
                $(summary_dt_object.cell(index.row,  columnIndexMap['status']).node())
            );
            $(".selection-dropdown").select2({
                placeholder: "Select an option",
                width: '16vw'
            });

            for (const [key, value] of Object.entries(table_dropdown)) {
                $(`#${key}`).val(value).trigger('change')
            }
        });
    }
<?php endif; ?>
</script>
