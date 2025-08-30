let iss_extract_editor_values = {},
iss_editor_values = {};
function onReadyGears(saved_coa_id) {
    iss_extract_editor_values = new orignalEditorValues();
    iss_editor_values = new orignalEditorValues();

    $('#coa-manual-override-modal-button-container-1 > div.coa-override-modal-button, #coa-manual-override-modal-button-container-rc-1 > div.coa-override-modal-button')
        .on('click', function() {
            iss_extract_editor_values = new orignalEditorValues();
            iss_editor_values = new orignalEditorValues();
    });

    // gear row
    $('#coa-output-override-table,#final-ad-review-table').on('click', 'button.coa-gear-row-btn', function () {
        $('#gears_percentage_sel').val('100').trigger('change');

        $('#gear_override_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
        
        let gearRowIndex = getTable().row( $(this).closest('td').parent() ).index();
        let gearsData = getTable().row(gearRowIndex).data(), fyData = [];
        
        $('#gears_program_name').html(gearsData?.['Program'] ?? gearsData['PROGRAM_CODE']);
        $('#gears_eoc_code').html(gearsData?.['EOC'] ?? gearsData?.['EOC_CODE']);
        if ($('input[name="use_iss_extract"]:checked').val() === 'false') {
            $('#gears_event_name_li').addClass('d-none');
        } else {
            if ($('#gears_event_name_li').hasClass('d-none')) {
                $('#gears_event_name_li').removeClass('d-none');
            }
            $('#gears_event_name').html(gearsData?.['Event Name'] ?? gearsData['EVENT_NAME']);
        }
        
        let editor_values = ($('input[name="use_iss_extract"]:checked').val() === 'true' ? 
            iss_extract_editor_values : iss_editor_values)

        setEditorDefault(gearRowIndex, editor_values);
    
        for (let y of year_array) {
            fyData.push([editor_values.getRow(gearRowIndex, y), 0]);
        }
        let gHot = loadFYDataTable(fyData);
        

        const gps_change = function() {
            $('#success-gears').addClass('d-none');

            let percentage = parseInt($('#gears_percentage_sel').val());

            if (percentage >= 0 && percentage <= 100) {
                percentage /= 100;

                changeRowPercentageHot(
                    percentage, 
                    gHot
                );
                
                percentage *= 100;
                
                $('#success-gears p.bx--inline-notification__subtitle').html(`Please review table with values updated by ${percentage} Percentage`);
                $('#success-gears').removeClass('d-none');
            }
        },
        gps_click = function() {
            $('#success-gears').addClass('d-none');

            let percentage = parseInt($('#gears_percentage_sel').val());

            if (percentage >= 0 && percentage <= 100) {
                percentage /= 100;
                
                changeRowPercentage(
                    gearRowIndex, 
                    percentage, 
                    editor_values, 
                    saved_coa_id
                );

                percentage *= 100;

                $('#success-gears p.bx--inline-notification__subtitle').html(`Updated manual override row using ${percentage} Percentage`);
                $('#success-gears').removeClass('d-none');
            }
        },
        reset_click = function() {
            $('#success-gears').addClass('d-none');

            resetRowValues(
                gearRowIndex,
                editor_values,
                gHot, 
                saved_coa_id
            );

            $('#success-gears p.bx--inline-notification__subtitle').html(`Reset default values in table and manual override row to the original values`);
            $('#success-gears').removeClass('d-none');
        };

        $('#gears_reset_percentage').off('click').on('click', reset_click);

        $('#gears_percentage_sel').off('change').on('change', gps_change);

        $('#gears_set_percentage').off('click').on('click', gps_click);
    });
}

function getTableDefault() {
    let gearTable;
    if (typeof coa_output_override_table !== 'undefined') {
        gearTable = output_table;
    } else if (typeof output_table['finalAdReview'] !== 'undefined') {
        gearTable = output_table['finalAdReview']
    }

    return gearTable;
}

function getTable() {
    let gearTable;
    if (typeof coa_output_override_table !== 'undefined') {
        gearTable = coa_output_override_table;
    } else if (typeof output_table['finalAdReview'] !== 'undefined') {
        gearTable = output_table['finalAdReview']
    }

    return gearTable;
}

class orignalEditorValues {
    yearValues = {};

    setRow(row, fy, yearValue) {
        if (this.yearValues?.[row] === undefined) {
            this.yearValues[row] = {};
        }
        if (
            year_array.lastIndexOf(fy) != -1 && 
            this.yearValues[row]?.[fy] === undefined
        ) {
            this.yearValues[row][fy] = parseInt(yearValue);
        }
    }

    resetRow(row) {
        if (
            this.yearValues[row] !== undefined
        ) {
            this.yearValues[row] = {};
        }
    }

    getRow(row, fy) {
        return this.yearValues[row]?.[fy] ?? 0;
    }
}

function getCIValue() {
    let ci, col;
    if ($('#coa-output-override-table').length > 0) {
        col = $('#coa-output-override-table > thead > tr > th > span:contains('+year_array[0]+')').parent();
    } else {
        col = $('#final-ad-review-table > thead > tr > th:contains(FY'+year_array[0]+')');
    }
    ci = getTable()
        .column(col)
        .index('visible');
    return ci;
}

function resetRowValues(rowIndex, editor_values, gHot, saved_coa_id) {
    gHot.suspendRender();
    let data = gHot.getData();
    let page = getTable().page();
    let len = getTable().page.len();

    let c = 0;
    for(let y of year_array) {
        const rowData = getTable().row(rowIndex).data();

        let originalValue =  originalProgramData[rowData['DT_RowId']][y] ?? 0;
        let ci = getCIValue();
        
        editedCell = $('#coa-output-override-table tr:eq('+(rowIndex+1-page*len)+') > td:eq('+(ci+c)+'), #final-ad-review-table tr:eq('+(rowIndex+1-page*len)+') > td:eq('+(ci+c)+')')[0];
        editedHeader = y;
        $( editedCell).click();

        editor_table
            .edit(rowIndex, false)
            .set(String(y), String(originalValue))
            .submit();
        
        data[c][1] = originalValue;
        c++;
    }

    gHot.updateData(data);
    gHot.resumeRender();

    getTable().draw(false);
}

function changeRowPercentageHot(percentage, gHot) {
    gHot.suspendRender();
    let data = gHot.getData();
    for(let d in data) {
        data[d][1] = Math.round((data[d][0]*percentage));
    }
    gHot.updateData(data);
    gHot.resumeRender();
}

function resetEditorDefault(rowIndex) {
    let editor_values = ($('input[name="use_iss_extract"]:checked').val() === 'true' ? 
        iss_extract_editor_values : iss_editor_values)
    editor_values.resetRow(rowIndex);
}

function getDTRowId(data) {
    let params = {
        'program_code': data['Program'],
        'pom_sponsor': data['POM SPONSOR'],
        'cap_sponsor': data['CAP SPONSOR'],
        'ass_area_code': data['ASSESSMENT AREA'],
        'execution_manager': data['EXECUTION MANAGER'],
        'resource_category': data['RESOURCE CATEGORY'],
        'eoc_code': data['EOC'],
        'osd_pe_code': data['OSD PE'],
        'event_name': data['EVENT NAME'],
    }
    const programId = covertToProgramId(type_of_coa, params);
    return programId;
}

function setEditorDefault(rowIndex, editor_values) {
    for(let y of year_array) {
        let currentValue = getTableDefault().row(rowIndex).data()?.[y] ?? 0;
        currentValue = parseInt(currentValue);
        rowData = getTable().row(rowIndex).data();
        let rowDataId;
        let iss_check = ($('input[name="use_iss_extract"]:checked').val() === 'true' ? 
        true : false);

        if (
            typeof newProgramData === 'object' &&
            typeof newProgramData[rowData['DT_RowId']] === 'object' &&
            typeof newProgramData[rowData['DT_RowId']][y] === 'number'
        ) {
            currentValue = newProgramData[rowData['DT_RowId']][y];
        } else if (
            typeof newProgramData === 'object' &&
            typeof newProgramData[getDTRowId(rowData)] === 'object' &&
            typeof newProgramData[getDTRowId(rowData)][y] === 'number'
        ) {
            currentValue = newProgramData[getDTRowId(rowData)][y];
        } 
        // for overall event summart
        else if (Number.isFinite(currentValue) === true && output_table['finalAdReview'] !== undefined) {
            currentValue = currentValue;
        }
        // for new added row in manual override table
        else if (rowData != undefined && Number.isFinite(rowData[y]) === true) {
            currentValue = rowData[y];
        }
        else {
            currentValue = 0;
        }
        if (Number.isFinite(editor_values.getRow(rowIndex, y)) === true) {
            editor_values.setRow(rowIndex, y, parseInt(currentValue));
        }  
    }
}

function changeRowPercentage(rowIndex, percentage, editor_values, saved_coa_id) {
    const table = getTable();
    const rowData = table.row(rowIndex).data();

    let c = 0;

    for (let y of year_array) {
        let currentValue = rowData?.[y] ?? 0;
        currentValue = parseInt(currentValue);
        if (!Number.isFinite(currentValue)) currentValue = 0;

        if (Number.isFinite(editor_values.getRow(rowIndex, y))) {
            editor_values.setRow(rowIndex, y, currentValue);
        }

        const originalValue = editor_values.getRow(rowIndex, y);
        const newValue = Math.round(originalValue * percentage);
        const ci = getCIValue();
        const rowNode = table.row(rowIndex).node();

        if (rowNode) {
            const $tds = $('td', rowNode);
            const $cell = $tds.eq(ci + c);
            if ($cell.find('.coa-gear-row-btn').length === 0) {
                $cell.addClass('ember-cell');
            } else {
                $cell.removeClass('ember-cell');
            }
            $($cell).click();
        }

        editor_table
            .edit(rowIndex, false)
            .set(String(y), String(newValue))
            .submit();
        c++;
    }
    const rowNode = getTable().row(rowIndex).node();
    if (rowNode) {
        const $tds = $('td', rowNode);
        $tds.each(function () {
            const $cell = $(this);
            if ($cell.find('.coa-gear-row-btn').length > 0) {
                $cell.attr('class', 'dt-body-center');
            }
        });
    }
    table.draw(false);
}




function loadFYDataTable(fy_data = []) {
    let container = $('#gears_tab_data');

    return createHot_gears(container, year_array, ['Original', 'Percentage Updated'], fy_data, handson_license)
}

/**
 * Create a handsontable.
 */
function createHot_gears(container, rowHeaders, colHeaders, data, licenseKey, options = {}) {
    let handsontable_args = {
        colHeaders: colHeaders,
        rowHeaders: rowHeaders,
        columns: [
            {
                type: 'numeric',
                validator: 'integer',
                allowInvalid: true
            },
            {
                type: 'numeric',
                validator: 'integer',
                allowInvalid: true
            }
        
        ],
        height: 'auto',
        rowHeaderWidth: 100,
        colWidths: 80,
        data: data,
        readOnly: true,
        licenseKey: licenseKey,
        manualColumnResize: false,
        manualRowResize: false
    };

    for (let option in options) {
        handsontable_args[option] = options[option];
    }

    container.handsontable(handsontable_args);

    return container.handsontable('getInstance');
}

$(function() {
    Handsontable.validators.registerValidator('integer', (val) => { return String(val).match(/^\d*$/) !== null });
});