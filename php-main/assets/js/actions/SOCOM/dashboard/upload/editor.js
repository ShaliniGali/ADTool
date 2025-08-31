class UserDataChanges {
    #changedRows = {}
    setChangedData(row_id, row_hash, row_hash_fy, column, old_value, new_value, hot_editor) {

        this.#changedRows[row_hash].edits[colHeaders[column]] =
            {
                oldValue: old_value,
                newValue: new_value,
                columnChanged: colHeaders[column],
                fiscalYear: row_hash_fy
            };
    }

    setRowData(row_id, row_hash, hot_editor) {
        if (!this.#changedRows[row_hash]) {
            let row = hot_editor.getDataAtRow(row_id);
            let originalRow = {};
            for (let i in row) {
                originalRow[colHeaders[i]] = row[i];
            }
            this.#changedRows[row_hash] = {
                originalRow: originalRow,
                edits: {}
            };
        }
    }

    getChangedData() {
        return this.#changedRows;
    }

    clear() {
        this.#changedRows = {};
    }
}
let userChanges = new UserDataChanges();

function createHot_editor(container, rowHeaders, colHeaders, data, readOnly, licenseKey, options = {}) {
    const numericColumns = ['DELTA_AMT', 'RESOURCE_K', 'DELTA_O2B_AMT', 'DELTA_OCO_AMT', 'EVENT_NUMBER', 'O2B_AMT', 'OCO_AMT', 'PROP_AMT', 'PROP_O2B_AMT', 'PROP_OCO_AMT', 'RESOURCE_K'];
    const readyOnlyColumns = ['CAPABILITY_SPONSOR_CODE', 'EVENT_NAME', 'EVENT_TYPE', 'EVENT_NUMBER']
    const hideColumns = ['PROGRAM_ID']

    const columnsConfig = colHeaders.map(function(column) {
            let result;
            let isReadOnly = view_mode === '/1' || readyOnlyColumns.indexOf(column) !== -1;

            if (numericColumns.indexOf(column) !== -1) {
                result = {
                    type: 'numeric',
                    readOnly: isReadOnly
                }
            } else {
                result = {
                    type: 'text',
                    allowInvalid: false,
                    readOnly: isReadOnly
                }
            }
            return result;
        }   
    );
     const hiddenColumnIndexes = colHeaders
            .map((name, index) => hideColumns.includes(name) ? index : -1)
            .filter(index => index !== -1);
    
    let handsontable_args = {
        colHeaders: colHeaders,
        rowHeaders: rowHeaders,
        columns: columnsConfig,
        rowHeaderWidth: 200,
        colWidths: 150,
        data: data,
        readOnly: readOnly,
        licenseKey: licenseKey,
        manualColumnResize: false,
        manualRowResize: false,
        maxRows: 100,
        stretchH: 'none',
        autoColumnSize: true,
        hiddenColumns: {
            columns: hiddenColumnIndexes,
            indicators: false 
        },
    };

    for (let option in options) {
        handsontable_args[option] = options[option];
    }
    
    container.handsontable(handsontable_args);

    hotInstance = container.handsontable('getInstance');

    hotInstance.addHook('afterChange', function (changes, source) {
        if (source === 'loadData') return;
        changes.forEach(([row, prop, oldValue, newValue]) => {
            if (oldValue !== newValue) {

                const row_hash_fy = hotInstance.getDataAtRowProp(row, rowDataMap['FISCAL_YEAR']);
                const row_hash = hotInstance.getDataAtRowProp(row, rowDataMap['PROGRAM_ID']) + '_' + row_hash_fy;
                userChanges.setChangedData(row, row_hash, row_hash_fy, prop, oldValue, newValue, hotInstance);
            
                const colIndex = hotInstance.propToCol(prop);
    
                if (colIndex !== -1) {
                    hotInstance.setCellMeta(row, colIndex, 'className', 'edited-cell');
                }
            }
        });
        hotInstance.render();
    });

    hotInstance.addHook('beforeChange', function (changes, source) {
        if (source === 'loadData') return;
        changes.forEach(([row, prop, oldValue, newValue]) => {
            if (oldValue !== newValue) {
                const row_hash = hotInstance.getDataAtRowProp(row, rowDataMap['PROGRAM_ID']) + '_' + hotInstance.getDataAtRowProp(row, rowDataMap['FISCAL_YEAR']);
                userChanges.setRowData(row, row_hash, hotInstance);
            }
        });
    });

    return hotInstance;
}

function paginateData(data, pageSize) {
    const pages = [];
    for (let i = 0; i < data.length; i += pageSize) {
        pages.push(data.slice(i, i + pageSize));
    }
    return pages;
}

function renderPaginationControls(totalPages, currentPage, onPageChange) {
    const dropdown = $('#pagination-dropdown');
    dropdown.empty();
    for (let i = 0; i < totalPages; i++) {
        const option = $(`<option value="${i}" ${i === currentPage ? 'selected' : ''}>Page ${i + 1}</option>`);
        dropdown.append(option);
    }
    dropdown.select2({
        minimumResultsForSearch: Infinity,
        width: 'resolve'
    });
    dropdown.off('change').on('change', function () {
        const selectedPage = parseInt($(this).val());
        onPageChange(selectedPage);
    });
}

function fetchData(pageNum) {
    $.ajax({
        url: `/dashboard/import_upload/fetch_data_editor${view_mode}`,
        method: 'POST',
        data: { page: pageNum, usr_dt_upload: usr_dt_upload, rhombus_token: () => { return rhombuscookie(); }},
        dataType: 'json',
        success: function (response) {
            const data = response.data;
            const rowHeaders = response.row_headers;
            const colHeaders = response.col_headers;
            editor_start_time = response.edit_start_time	;
            console.log(editor_start_time);
            if (!window.hotInstance) {
                const container = $('#handsontable-editor');
                window.hotInstance = createHot_editor(container, rowHeaders, colHeaders, data, false, handson_license);
            } else {
                hotInstance.loadData(data);
                hotInstance.updateSettings({
                    rowHeaders: rowHeaders
                });
            }
            
            renderPaginationControls(response.total_pages, pageNum, fetchData);
        },
        error: function (err) {
            console.error('Error fetching data', err);
        }
    });
}

function searchDataEditor(selectedColumn, searchValue) {
    $.ajax({
        url: `/dashboard/import_upload/search_data_editor${view_mode}`,
        method: 'GET',
        data: {
            column: selectedColumn,
            query: searchValue,
            usr_dt_upload: usr_dt_upload,
            rhombus_token: () => { return rhombuscookie(); }
        },
        dataType: 'json',
        success: function (response) {
            const data = response.data;
            const rowHeaders = response.row_headers;
            const colHeaders = response.col_headers;

            if(!data || data.length === 0) {
                displayToastNotification('error', 'No results found. Please try another combination.');
                hotInstance.loadData([]);
                renderPaginationControls(0, 0, () => {});
                return;
            }

            const totalPages = Math.ceil(data.length / 100);
            const paginatedData = paginateData(data, 100);
            if (!window.hotInstance) {
                const container = $('#handsontable-editor');
                window.hotInstance = createHot_editor(container, rowHeaders, colHeaders, paginatedData[0], false, handson_license);
            } else {
                hotInstance.loadData(paginatedData[0]);
                hotInstance.updateSettings({
                    rowHeaders: rowHeaders
                });
            }

            renderPaginationControls(totalPages, 0, function (selectedPage) {
                hotInstance.loadData(paginatedData[selectedPage]);
                const rowHeadersForPage = [];
                for (
                    let i = selectedPage * 100 + 1;
                    i <= selectedPage * 100 + paginatedData[selectedPage].length;
                    i++
                ) {
                    rowHeadersForPage.push(`Row ${i}`);
                }
                hotInstance.updateSettings({
                    rowHeaders: rowHeadersForPage
                });
            });
        },
        error: function (err) {
            console.error('Search failed', err);
        }
    });
}

function saveDataEdits() {
    const changedData = userChanges.getChangedData();
    if (Object.keys(changedData).length === 0) {
        displayToastNotification('error', 'No changes to save.');
        return;
    }
    $.ajax({
        url: '/dashboard/import_upload/save_data_edits',
        method: 'POST',
        // contentType: 'application/json',
        data: { changes: JSON.stringify(changedData), 
                usr_dt_upload: usr_dt_upload,  
                editor_start_time: editor_start_time,
                rhombus_token: () => { return rhombuscookie(); } 
        },
        // dataType: 'json',
        success: function (response) {
            if (response.status) {
                displayToastNotification('success', 'Save successful');
                userChanges.clear();
                fetchData(0);
            } else {
                displayToastNotification('error', 'Save error');
            }
        },
        error: function () {
            displayToastNotification('error', 'Failed to save changes.');
        }
    });
}

function onReadyEditor() {
    $(function () {
        fetchData(0);
        $('#column-headings-dropdown').select2({
            width: 'resolve',
            placeholder: 'Select Column'
        });
        $('#search-btn').on('click', function () {
            const selectedColumn = $('#column-headings-dropdown').val();
            const searchValue = $('#search-input').val().trim();
            if (!selectedColumn || searchValue === '') {
                displayToastNotification('error', 'Please select a column and enter a search term.');
                return;
            }
            searchDataEditor(selectedColumn, searchValue);
        });
        $('#refresh-btn').on('click', function () {  
            $('#column-headings-dropdown').val(null).trigger('change');
            $('#search-input').val('');
            fetchData(0);
        });    
    });
    
}

document.addEventListener('DOMContentLoaded', function () {
    const saveBtn = document.getElementById('save-edits-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            saveDataEdits();
        });
    }
});

$(onReadyEditor);