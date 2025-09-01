"use strict";

let fy_years = {};
let pom_cycle_type = '';

const coa_merge_table = {
    'budget': {},
    'coa': {}
}

const overlay_loading_html = `
<div class="bx--loading-overlay" style="z-index: 10000;">
<div data-loading class="bx--loading">
  <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
    <title>Loading</title>
    <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
  </svg>
</div>
</div>`;

function onReady() {
    let csc = new checkedSavedCoa()

    if ($('input[name="use_iss_extract_share_coa"]').filter(':checked').val() === "true") {
        pom_cycle_type = 'ISS_EXTRACT';
    }
    else{
        pom_cycle_type = 'RC_T';
    }

    $('#load-coa-modal-btn').on('click', () => { showLoadCOA(csc); } );
    initCOASelectionDatatable(csc);
    initBudgetDatatable();

    // Initialize the following
    let openLoadCoaModalBtn = $('#load-coa-modal-btn');
    openLoadCoaModalBtn.on('click', openMergeCoaModal);

    handleCloseMenuOnClickOutside();


    const radioButtons = $('input[name="use_iss_extract_share_coa"]');
    radioButtons.on('change', function () {
        $('#merge-coa-table-container').html(
            `<div class="d-flex w-100 justify-content-center p-2"> <h2>Click Load COA to merge COA</h2> </div>`
        );
        if ($('input[name="use_iss_extract_share_coa"]').filter(':checked').val() === "true") {
            pom_cycle_type = 'ISS_EXTRACT';
        }
        else{
            pom_cycle_type = 'RC_T';
        }
    });


};

class checkedSavedCoa {
    #chked = 0
    
    add() {
        this.#chked++;
    }
    
    remove() {
        this.#chked--;
    }

    get() {
        return this.#chked;
    }

    reset() {
        this.#chked = 0;
    }
}

function loadCoa() {
    let selectedCoas = [];
    $("#merge-coa-table-loading").removeClass("d-none");

    // Loop through each row in the DataTable and grab all data from 'checked' rows
    $('#show-coa-table').DataTable().$('input[name="load_coa[]"]:checked').each(function(i, elem) { selectedCoas.push(elem.value); }) 

    // send warning messag to user if selected COAs is not equal to 2
    if (selectedCoas.length !== 2) {
        displayToastNotification('error', 'Error: Please select exactly two COAs');
        $("#merge-coa-table-loading").addClass("d-none");
        return false;
    }

    let input_object =  {
        selected_coas: selectedCoas,
        pom_cycle_type: pom_cycle_type
    }

    // Add CSRF token only if it exists (for production)
    let csrf_token = rhombuscookie();
    if (csrf_token) {
        input_object.rhombus_token = csrf_token;
    }

    //merge_coa_table
    let url = '/dashboard/coa_management/get_selected_coa';
    loadPageData(
        '#merge-coa-table-container',
        url,
        input_object,
        function() {
            getProposedBudgetValue(); // Call this instead of setBudgetTable() to properly set up budget inputs
            closeMergeCoaModal();
            $("#merge-coa-table-loading").addClass("d-none");
        }
    )
}

function getMergedRows() {
    let selected_rows = []; 
    let unselected_rows = [];

    let included_selected_row_ids = [];
    let included_unselected_row_ids = [];
    Object.keys(coa_merge_table['coa']).forEach(key => {
        let selected_row_ids = [];
        $(`#coa-merge-table-${key}`).DataTable().$('input[name="select_eoc[]"]:checked').each(function(i, elem) { 
            selected_row_ids.push(elem.value);
            included_selected_row_ids.push(elem.value);
        })

        // Iterate through the rows
        coa_merge_table['coa'][key].rows().every( function () {
            let row_data = this.data();

            // Check if the value in the specified column matches your search value
            if (selected_row_ids.includes(row_data['ID'])) {
                selected_rows.push(row_data);
            }
        });
    });

    Object.keys(coa_merge_table['coa']).forEach(key => {
        $(`#coa-merge-table-${key}`).DataTable().$('input[name="select_eoc[]"]:not(:checked)').each(function(i, elem) { 
            if (!included_selected_row_ids.includes(elem.value) && !included_unselected_row_ids.includes(elem.value)) {
                included_unselected_row_ids.push(elem.value);
            }
        }) 
    });

    let unselected_row_ids = [];
    Object.keys(coa_merge_table['coa']).forEach(key => {
        // Iterate through the rows
        coa_merge_table['coa'][key].rows().every( function () {
            let row_data = this.data();

            // Check if the value in the specified column matches your search value
            if (included_unselected_row_ids.includes(row_data['ID']) && !unselected_row_ids.includes(row_data['ID']) ) {
                unselected_row_ids.push(row_data['ID']);
                unselected_rows.push(row_data);
            }
        });
    });

    return {
        'unselected_rows': unselected_rows,
        'selected_rows': selected_rows
    }
}

function mergeCOA() {

    let name = $('#coa-name').val(),
    description = $('#coa-description').val();

    if (typeof name !== 'string' || name.trim().length === 0) {
        displayToastNotification('error', 'Title must have a value');
        return false;
    }

    if (typeof description !== 'string' || description.trim().length >= 500) {
        displayToastNotification('error', 'Description must be less than 500');
        return false;
    }

    let coa_type = $('input[name=weighted_score_based]:checked').val();
    let merged_rows = getMergedRows();

    let post_data = {}
    post_data['rhombus_token'] = rhombuscookie();
    post_data['budget_uncommitted'] = coa_merge_table['budget'].data().toArray();
    post_data['selected_rows'] = JSON.stringify(merged_rows['selected_rows']);
    post_data['coa_name'] = name;
    post_data['coa_description'] = description;
    post_data['coa_type'] = coa_type;
    post_data['coa_ids'] = Object.keys(coa_merge_table['coa']);
    post_data['use_iss_extract'] = function() { return $('input[name="use_iss_extract_share_coa"]').filter(':checked').val() === "true"; }

    $('#overlay-loader').html(overlay_loading_html);
    $.post('/dashboard/coa_management/merge_coa', post_data, 
        function(response) {
            displayToastNotification('success', 'COA has been merged successfully.');
            $('#overlay-loader').html('');
            closeSaveMergeCoaModal();
        }
    )
}

function updateBudgetTable(data, isChecked) {

    let oldCommittedData = coa_merge_table['budget'].row(1).data();
    let oldUncommittedData = coa_merge_table['budget'].row(2).data();

    let newCommittedData = {'HEADER': 'Committed'}
    let newUncommittedData = {'HEADER': 'Uncommitted'}

    let committedFYDP = parseInt(oldCommittedData['FYDP_K'])
    let uncommittedFYDP = parseInt(oldUncommittedData['FYDP_K'])

    let isExceed = false 
    if (isChecked) {
        fy_years[pom_cycle_type].forEach(year => {
            newCommittedData[year] = parseInt(oldCommittedData[year]) + parseInt(data[year])
            newUncommittedData[year] = parseInt(oldUncommittedData[year]) - parseInt(data[year])
            committedFYDP += parseInt(data[year])
            uncommittedFYDP -= parseInt(data[year])
            isExceed = newUncommittedData[year] < 0 || uncommittedFYDP < 0
        })
    }
    else {
        fy_years[pom_cycle_type].forEach(year => {
            newCommittedData[year] = parseInt(oldCommittedData[year]) - parseInt(data[year])
            newUncommittedData[year] = parseInt(oldUncommittedData[year]) + parseInt(data[year])
            committedFYDP -=  parseInt(data[year])
            uncommittedFYDP += parseInt(data[year])
            isExceed = newUncommittedData[year] < 0 || uncommittedFYDP < 0
        })
    }
    newCommittedData['FYDP_K'] = committedFYDP
    newUncommittedData['FYDP_K'] = uncommittedFYDP

    coa_merge_table['budget'].row( 1 ).data( newCommittedData ).draw();
    coa_merge_table['budget'].row( 2 ).data( newUncommittedData ).draw();

    //disable merge coa button if there is any negative value
    disableMergeCOAButton(isExceed);
}

function setBudgetTable() {
    let newBudgetData = {'HEADER': 'Proposed Budget'}
    let newCommittedData = {'HEADER': 'Committed'}
    let newUncommittedData = {'HEADER': 'Uncommitted'}

    fy_years[pom_cycle_type].forEach( year => {
        newBudgetData[year] =  parseInt($(`#text-input-budget-${year}`).val())
        newUncommittedData[year] =  parseInt($(`#text-input-budget-${year}`).val())
        newCommittedData[year] = 0
    })
    newBudgetData['FYDP_K'] = parseInt($(`#text-input-budget-fydp`).val())
    newCommittedData['FYDP_K'] = 0
    newUncommittedData['FYDP_K'] = parseInt($(`#text-input-budget-fydp`).val())

    coa_merge_table['budget'].row( 0 ).data( newBudgetData ).draw();
    coa_merge_table['budget'].row( 1 ).data( newCommittedData ).draw();
    coa_merge_table['budget'].row( 2 ).data( newUncommittedData ).draw();
}

function getProposedBudgetValue() {

    let ids = [];

    // Loop through each row in the DataTable and grab all data from 'checked' rows
    $('#show-coa-table').DataTable().$('input[name="load_coa[]"]:checked').each(function(i, elem) { ids.push(elem.value); }) 

    let input_object =  {
        pom_cycle_type,
        ids
    }

    $("#merge-coa-table-loading").removeClass("d-none");

    // get proposed budget value
    let url = '/dashboard/coa_management/get_proposed_budget';
    loadPageData(
        '#proposed-budget-input',
        url,
        input_object,
        function(response, status, xhr) {
            if (status === 'error') {
                console.error('Error calling loadPageData:', xhr.status, xhr.statusText);
                attachFYDPBudgetListener();
                $("#merge-coa-table-loading").addClass('d-none'); // Hide spinner on error
                return;
            }
            
            $.post('/optimizer/get_coa_data', 
                {
                    rhombus_token: rhombuscookie(),
                    ids
                }, 
                function(response) {
                    let data = response['data'];
        
                    if (data.length > 0) { 
                        let fydp_k = 0;
                        let budget = [];
                        if (pom_cycle_type === 'ISS_EXTRACT') {
                            const result = getProposedBudget(data)
                            fydp_k = result.fydp_k;
                            budget = result.budget;
                        }
                        else {
                            const result = getProposedBudgetRC(data)
                            fydp_k = result.fydp_k;
                            budget = result.budget;
                        }

                        fy_years[pom_cycle_type].forEach((year, i) => {
                            $(`#text-input-budget-${year}`).val(budget[i])
                        })
                        $(`#text-input-budget-fydp`).val(fydp_k)
                    }
                    attachFYDPBudgetListener();
                    $("#merge-coa-table-loading").addClass('d-none'); // Always hide spinner regardless of data
                }
            ).fail(function(xhr, status, error) {
                console.error('Error calling /optimizer/get_coa_data:', error);
                attachFYDPBudgetListener();
                $("#merge-coa-table-loading").addClass('d-none'); // Hide spinner even on error
            });
        }
    )
}

function getProposedBudget(data) {
    let fydp_k = 0;
    let budget = [];
    data.forEach(element => {
        let optimizer_input = JSON.parse(element['OPTIMIZER_INPUT']);
        let temp_budget = [];

        if (optimizer_input != null) {
            if (Array.isArray(optimizer_input)) {
                optimizer_input = optimizer_input[0]
            }
            temp_budget = optimizer_input['budget'];
        }
        else {
            let session = JSON.parse(element['OVERRIDE_TABLE_SESSION']);
            session['budget_uncommitted'].forEach(v => {
                if (v['TYPE'] === 'Proposed Budget $K') {
                    fy_years[pom_cycle_type].forEach(year => {
                        temp_budget.push(v[year])
                    })
                }
            })
        }

        let temp_fydp = temp_budget.reduce((accumulator, currentValue) => {
            return parseInt(accumulator) + parseInt(currentValue)
        },0);

        if (temp_fydp > fydp_k) {
            fydp_k = temp_fydp; 
            budget = temp_budget;
        }
    });

    return {
        fydp_k,
        budget
    }
}

function getProposedBudgetRC(data) {
    let fydp_k = 0;
    let budget = [];

    data.forEach(element => {
        let optimizer_input = JSON.parse(element['OPTIMIZER_INPUT']);

        let temp_budget = [];
        if (optimizer_input) {
            const years = JSON.parse(element['YEAR_LIST']);    
            const original_resource_k = element['original_resource_k'];
            const budget = optimizer_input[0]['budget'];

            // let committed_grand_total = {};
            years.forEach( (year, idx) => {
               temp_budget.push(original_resource_k[year] - budget[idx]);
            })
        }
        else {
            let session = JSON.parse(element['OVERRIDE_TABLE_SESSION']);
            session['budget_uncommitted'].forEach(v => {
                if (v['TYPE'] === 'Proposed Budget $K') {
                    fy_years[pom_cycle_type].forEach(year => {
                        temp_budget.push(v[year])
                    })
                }
            })
        }

        let temp_fydp = temp_budget.reduce((accumulator, currentValue) => {
            return parseInt(accumulator) + parseInt(currentValue)
        },0);

        if (temp_fydp > fydp_k) {
            fydp_k = temp_fydp; 
            budget = temp_budget;
        }
    })

    return {
        fydp_k,
        budget
    }
}

function initCOASelectionDatatable(csc) {
    $("#show-coa-table").DataTable({
        columnDefs: [
            { 
                'targets': 0,
                'title': 'Select',
                'searchable': false,
                'orderable': false,
                'className': 'dt-body-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="load_coa[]" value="">';
                }
            },
            { targets: 1, data: "COA_TITLE", name: "COA_NAME",  title: "COA NAME", defaultContent: ''},
            { targets: 2, data: "COA_DESCRIPTION", name: "COA_DESCRIPTION", title: 'COA Description', defaultContent: '' },
            { targets: 3,  data: "CREATED_DATETIME", name: "CREATED_DATETIME", title: 'Created Date' }
        ],
        initComplete: function() {
            $('#show-coa-table tbody').on('change', 'td input[name="load_coa[]"]', function() {
                
                if (this.checked === false) {
                    csc.remove();
                }

                if (csc.get() === 2) {
                    this.checked = false;
                    return false;
                }
                
                if (this.checked === true) {
                    csc.add()
                }

                if (csc.get() === 2) {
                    //enable proposed budget section
                    getProposedBudgetValue();
                    $('.load-coa-btn').attr('disabled', false);
                    $('#proposed-budget-input').removeClass('hidden')
                }
                else {
                    //disable proposed budget section
                    $('.load-coa-btn').attr('disabled', true);
                    $('#proposed-budget-input').addClass('hidden')
                }
            });
        },
        ajax: {
            url: "/dashboard/coa_management/get_my_coa",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
                use_iss_extract: function() { return $('input[name="use_iss_extract_share_coa"]').filter(':checked').val() === "true"; }
            }
        },
        rowCallback: function (row, data) {
            $('td:eq(0) input', row).val(data['SAVED_COA_ID']);

            return data;
        },
    });
}

function initBudgetDatatable() {
    let year_targets = [];
    fy_years[pom_cycle_type].forEach((year, idx) => {
        year_targets.push(
            { targets: idx + 1, data: year, name: year,  title: "FY" + year, defaultContent: 0}
        )
    })

    coa_merge_table['budget'] = $("#budget-table").DataTable({
        searching: false, 
        paging: false, 
        info: false,
        columnDefs: [
            {
                'targets': 0,
                'title': '',
                'searchable': false,
                'orderable': false,
                'data': "HEADER"
            },
            ...year_targets,
            { targets: 6, data: "FYDP_K", name: "FYDP_K",  title: "FYDP $K", defaultContent: 0}
        ],
        order: [],
        data: [{'HEADER': 'Proposed Budget'}, {'HEADER': 'Committed'}, {'HEADER': 'Uncommitted'}],
        rowCallback: function (row, data) {
            if (Object.keys(coa_merge_table['budget']).length !== 0) {
                for (const [key, value] of Object.entries(data)) {
                    let columnIdx =  coa_merge_table['budget'].column(`${key}:name`).index();
                    if (typeof value === "number" && value < 0) {
                        $(row).find(`td:eq(${columnIdx})`).addClass('invalid-uncommitted-cell');
                    }
                    else {
                        $(row).find(`td:eq(${columnIdx})`).removeClass('invalid-uncommitted-cell');
                    }
                }
            }
            return data;
        },
    });
}

function initCOADatatable(id, data, headers, saved_coa_id, visible_score_columns = []) {
    
    let init_invisible_columns = [];
    if (pom_cycle_type === 'ISS_EXTRACT'){
        init_invisible_columns = [1, 5, 6, 8, 9, 10, 11, 14, 15, 16, 17, 18];
    }
    else {
        init_invisible_columns = [1, 5, 6, 8, 9, 10, 13, 14, 15, 16, 17];
    }
    
    let invisible_columns = init_invisible_columns.filter(item => !visible_score_columns.includes(item));

    coa_merge_table['coa'][saved_coa_id] = $(`#${id}`).DataTable({
        data: data,
        columns: headers,
        columnDefs: [
        { 
            'targets': 0,
            'title': 'Select',
            'searchable': false,
            'orderable': false,
            'className': 'dt-body-center',
            'render': function (data, type, full, meta){
                return '<input type="checkbox" name="select_eoc[]" value="">';
            },
        },
        {
            targets: invisible_columns,
            visible: false
        }],
        initComplete: function() {
            $(`#${id} tbody`).on('change', 'td input[name="select_eoc[]"]', function() {
                let row_index = coa_merge_table['coa'][saved_coa_id].cell($(this).parent()[0]).index()['row'];
                let row_data = coa_merge_table['coa'][saved_coa_id].row(row_index).data();

                updateBudgetTable(row_data, this.checked);
                disableCOATableRow(saved_coa_id,row_data['ID'], this.checked);
            });
        },
        rowCallback: function (row, data) {
            $('td:eq(0) input', row).val(data['ID']);
            return data;
        }
    });
}

function disableMergeCOAButton(isExceed) {
    let numOfChecked = $('[id^="coa-merge-table-"] tbody td input[name="select_eoc[]"]:checked').length
    if (numOfChecked > 0 && !isExceed) {
        $('#merge-coa-modal-btn').attr('disabled', false);
    }
    else {
        $('#merge-coa-modal-btn').attr('disabled', true);
    }
}

function disableCOATableRow(saved_coa_id, id, isChecked) {
    for (const [key, value] of Object.entries(coa_merge_table['coa'])) {
        if (key != saved_coa_id) {
            value.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                var data = this.data();
                if (data['ID'] === id) {
                    let duplicated_row_checkbox = $(value.cell(rowIdx, 0).nodes()).find('input[name="select_eoc[]"]')[0];
                    $(duplicated_row_checkbox).attr('disabled', isChecked)
                }
            });
        }
    }
}

function attachFYDPBudgetListener() {
    fy_years[pom_cycle_type].forEach(year => {
        $(`#text-input-budget-${year}`).off('input').on('input', function() {
            let fydp_k = 0;
            fy_years[pom_cycle_type].forEach(y => {
                let val = parseInt($(`#text-input-budget-${y}`).val()) || 0;
                fydp_k += val;
            });
            $(`#text-input-budget-fydp`).val(fydp_k);
        });
    });
}

function showLoadCOA(csc) {
    csc.reset();
    $('.load-coa-btn').attr('disabled', true);
    $('#proposed-budget-input').addClass('hidden')
    $("#show-coa-table").DataTable().ajax.reload();
    openMergeCoaModal();
}

function showMergeCOA() {
    $('#coa-name').val('')
    $('#coa-description').val('');
    openSaveMergeCoaModal();
}

function openMergeCoaModal() {
    $('#coa_merge_view_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
}

function closeMergeCoaModal() {
    $("#merge-coa-table-loading").addClass('d-none');
    $("#coa_merge_view_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
}

function openSaveMergeCoaModal() {
    $('#coa_merge_save_view_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
}

function closeSaveMergeCoaModal() {
    $("#coa_merge_save_view_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
}

function handleCloseMenuOnClickOutside() {
    $(document).on('click', function(event) {
        // Check if the clicked element is not the menu trigger or inside the menu
        if (!$(event.target).closest('.bx--overflow-menu__trigger').length && !$(event.target).closest('.bx--overflow-menu-options').length) {
            $('.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
        }
    });
}

$(onReady); 
  
if (!window._rb) window._rb = {}
window._rb.onReady = onReady;