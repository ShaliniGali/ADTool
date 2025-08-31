"use strict";
    
function onReady() {
    // Initialize Cycle Table
    cycleTable = $('#cycle-list-table').DataTable({
        info: 'Cycle List',
        autoWidth: true,
        ajax: {
            url: '/dashboard/cycles/list/get',
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
        },
        columns: cycleColumnDefinition,
        start: 0,
        length: 10,
        lengthChange: true,
        order: [],
        createdRow: function (row, data, index) {
            if (data) {
                let actionBtn = $('td', row).eq(cycleColumnDefinition.length - 1).find('div.bx--overflow-menu > button.bx--overflow-menu__trigger');
                actionBtn.next('div.bx--overflow-menu-options').find('button[role=activate]').prop('disabled', false).attr('cycleId', data['ID']).on('click', setActive);
                actionBtn.next('div.bx--overflow-menu-options').find('button[role=edit]').prop('disabled', false).attr({'cycleId': data['ID'], 'cycleName': data['CYCLE_NAME'], 'cycleDesc': data['DESCRIPTION']}).on('click', showEditModal);
                actionBtn.next('div.bx--overflow-menu-options').find('button[role=delete]').prop('disabled', false).attr({'cycleId': data['ID'], 'isActive': data['IS_ACTIVE']}).on('click', deleteCycle);
                actionBtn.on('click', toggleMenu);
            } else {
                $('td', row).eq(cycleColumnDefinition.length - 1).empty();
            }
        },
    });


    $('#weight-criteria-admin').on('click', weightTerms);

    if($('#weight-criteria-admin').attr('aria-selected') === 'true') {
        weightTerms();
    }

    // Initialize the following
    let createCycleBtn = $('#create-cycle-btn');
    createCycleBtn.on('click', createCycle);

    let showDeletedCheckbox = $('#cycle-deleted-checkbox');
    showDeletedCheckbox.on('click', toggleShowDeleted);

    handleCloseMenuOnClickOutside();

    $(document).on('click', '#weight-criteria-admin .bx--overflow-menu__trigger', function (event) {
        event.stopPropagation();
        toggleMenu.call(this);
    });

    $(document).on('click', '#closeEditModal, .bx--modal-close, .bx--btn--secondary', function () {
        $('div.bx--modal.bx--modal-tall').removeClass('is-visible');
    });

    $(document).on('click', '.edit-description-btn', function (event) {
        event.stopPropagation();
        let id = $(this).data('id');
        let description = $(this).attr('data-description');
        let criteria_name_id = $(this).data('criteriaNameId');

        if (description === "null" || description === null || description === undefined) {
            description = '';
        }

        $('#descriptionId').val(id);
        $('#descriptionInput').val(description);
        $('#criteriaNameId').val(criteria_name_id);
        $('div.bx--modal.bx--modal-tall').addClass('is-visible');


        closeMenu($(this));
    });

    $(document).on('click', '#saveDescription', saveDescription);

    $(document).on('click', '.delete-description-btn', deleteDescription);

};


let weightTerms = function () {
    // Initialize Criteria Terms Table
    criteriaTermsTable = $('#criteria-terms-list-table').DataTable({
        info: 'Criteria Terms List',
        autoWidth: true,
        ajax: {
            url: '/dashboard/cycles/criteria/terms/get',
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: function (json) {
                handleCriteriaAdminDisplay(json.CRITERIA_NAME);

                return json.data;
            }
        },
        columns: criteriaTermsColumnDefinition,
        start: 0,
        length: 10,
        lengthChange: true,
        order: []
    });

    setupCriteriaTermsInput();

    let createCriteriaBtn = $('#create-criteria-btn');
    createCriteriaBtn.on('click', createCriteria);

    $('#weight-criteria-admin').off('click', weightTerms);

    weightTerms = function() {
        criteriaTermsTable.ajax.reload();
    }
};

function fetchCycles() {
    let url = '/dashboard/cycles/list/get';
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
        }, 
        function(response) {
            if (response.success === true) {
                const cycles = response.data;
                
                // Unhide 'Actions' column when showing all cycles. 'Actions' column is hidden when 'Show Deleted Cycles' checkbox is checked
                const actionsColumnIndex = 4;
                if (cycleTable.column(actionsColumnIndex).visible() === false) {
                    cycleTable.column(actionsColumnIndex).visible(true);
                }
                if (cycleTable.rows().data().toArray().length > 0) {
                    cycleTable.ajax.reload();
                }
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to Fetch Cycles');
    });
}

function fetchDeletedCycles() {
    let url = '/dashboard/cycles/list/get_deleted';
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
        }, 
        function(response) {
            if (response.success === true) {
                const deletedCycles = response.data;
                cycleTable.clear().rows.add(deletedCycles).draw();

                // Hide 'Actions' column when showing deleted cycles
                const actionsColumnIndex = 4;
                cycleTable.column(actionsColumnIndex).visible(false);

                if (deletedCycles.length > 0) {
                    displayToastNotification('success', 'Displaying Deleted Cycles');
                } else {
                    displayToastNotification('success', 'No Deleted Cycles to Display');
                }
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Error: Unable to Fetch Deleted Cycles');
    });
}

function createCycle() {
    let cycleName = sanitizeHtml($(`#text-input-cycle-name`).val(), { allowedAttributes:{}, allowedTags:[]});
    let cycleDesc = sanitizeHtml($(`#cycle-text-area-description`).val(), { allowedAttributes:{}, allowedTags:[]});
    
    if (cycleName.trim().length === 0) {
        displayToastNotification('error', 'Error: Cycle Name Cannot Be Empty');
        return false;
    }

    let url = '/dashboard/cycles/create';
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            cycle_name: cycleName,
            cycle_desc: cycleDesc,
        }, 
        function(response) {
            if (response.success === true) {
                $('#text-input-cycle-name').val('');
                $('#cycle-text-area-description').val('');

                fetchCycles();
                fetchActiveCycle();

                displayToastNotification('success', 'Cycle Created Successfully');
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        if (jqXHR.status === 409) {
            displayToastNotification('error', 'Error: A cycle with this name already exists.');
        } else {
            displayToastNotification('error', jqXHR.responseJSON.message);
        }
    });
}

function toggleMenu() {
    let menuElem = $(this).next('div.bx--overflow-menu-options');
    if (menuElem.hasClass('bx--overflow-menu-options--open')) {
        menuElem.removeClass('bx--overflow-menu-options--open');
    } else {
        $('#cycle-list-table div.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
        menuElem.addClass('bx--overflow-menu-options--open');
    }
};

function closeMenu(buttonElem) {
    buttonElem.closest('.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
}

function closeEditModal() {
    $("#cycle_edit_view_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
}

function updateCycle(cycleId, updateType, data = {}) {
    return $.post('/dashboard/cycles/update', 
        {
            rhombus_token: rhombuscookie(),
            id: cycleId,
            update_type: updateType,
            ...data,
        }, 
        function(response) {
            if (response.success === true) {
                fetchCycles();
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', jqXHR.responseJSON.message);
    });
}

function setActive(e) {
    const buttonElem = $(this);
    const cycleId = buttonElem.attr('cycleId');

    updateCycle(cycleId, 'ACTIVATE_CYCLE')
        .then((response) => {
            if (response.success === true) {
                displayToastNotification('success', 'Activated Cycle Successfully');
                fetchActiveCycle();
            }
        })

    closeMenu(buttonElem);
};

function showEditModal(e) {
    const buttonElem = $(this);
    const cycleId = buttonElem.attr('cycleId');
    const cycleName = buttonElem.attr('cycleName');
    const cycleDesc = buttonElem.attr('cycleDesc');

    $('#edit-text-input-cycle-name').attr('cycleId', cycleId);

    $('#edit-text-input-cycle-name').val(cycleName);

    $('#edit-cycle-description').val(cycleDesc);

    $('#cycle_edit_view_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

    closeMenu(buttonElem);
};

function deleteCycle(e) {
    const buttonElem = $(this);
    const cycleId = buttonElem.attr('cycleId');
    const isActive = parseInt(buttonElem.attr('isActive')) == 1 ? true : false;

    if (isActive) {
        displayToastNotification('error', 'Active Cycles Cannot be Deleted');
    } else {
        updateCycle(cycleId, 'DELETE_CYCLE').then((response) => {
            if (response.success === true) { 
                displayToastNotification('success', 'Deleted Cycle Successfully');
            }
        });
    }

    closeMenu(buttonElem);
};

function toggleShowDeleted() {
    const isChecked = $(this).is(':checked');
    // Show Deleted Cycles
    if (isChecked) {
        fetchDeletedCycles();
    } else {
        // Show All Cycles (Non-Deleted)
        fetchCycles();
    }
};
  
function saveCycle() {
    let cycleName = sanitizeHtml($(`#edit-text-input-cycle-name`).val(), { allowedAttributes:{}, allowedTags:[]});
    let cycleDesc = sanitizeHtml($(`#edit-cycle-description`).val(), { allowedAttributes:{}, allowedTags:[]});
    
    if (cycleName.trim().length === 0) {
        displayToastNotification('error', 'Error: Cycle Name Cannot Be Empty');
        return false;
    }

    let cycleId = $(`#edit-text-input-cycle-name`).attr('cycleId');

    let data = { cycle_name: cycleName, cycle_desc: cycleDesc };

    updateCycle(cycleId, 'UPDATE_CYCLE_TEXT', data).then((response) => {
        if (response.success === true) {
            displayToastNotification('success', 'Updated Cycle Name/Description Successfully');
            closeEditModal();
        }
    });
};

function fetchActiveCycle() {
    let url = '/dashboard/cycles/get_active';
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
        }, 
        function(response) {
            if (response.success === true) {
                const { data } = response;

                const updatedCycleId = data?.CYCLE_ID ?? 0;
                const updatedCycleName = data?.CYCLE_NAME;
                $('#active-cycle-name').attr('cycleId', updatedCycleId);
                $('#active-cycle-name').html(updatedCycleName);

                const updatedCriteriaId = data?.CRITERIA_ID ?? 0;
                const updatedCriteriaName = data?.CRITERIA_NAME;
                $('#text-input-criteria-name').attr('criteriaId', updatedCriteriaId);
                $('#text-input-criteria-name').val(updatedCriteriaName);

                if (updatedCriteriaName !== null && updatedCriteriaName.length > 0) {
                    handleCriteriaAdminDisplay(updatedCriteriaName);
                    fetchCriteriaTerms();
                } else {
                    handleCriteriaAdminDisplay();
                }


                fetchCriteriaTerms();

                // Reset criteria terms input
                $('input[name="text-input-criteria-term"]').val('');
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Error: Unable to Fetch Active Cycle');
    });
}

// Create Criteria Name + Criteria Terms
function createCriteria() {
    let criteriaId = $('#text-input-criteria-name').attr('criteriaId') ?? 0;
    let criteriaName = sanitizeHtml($(`#text-input-criteria-name`).val(), { allowedAttributes:{}, allowedTags:[]});
    let cycleId = $('#active-cycle-name').attr('cycleId');
    let criteriaTermValues = $('input[name="text-input-criteria-term"]').map(function() {
        return $(this).val();
    }).get().filter(function(val) {
        return val !== '';
    });
    
    if (criteriaName.trim().length === 0) {
        displayToastNotification('error', 'Error: Criteria Name Cannot Be Empty');
        return false;
    }

    if (criteriaTermValues.length === 0) {
        displayToastNotification('error', 'Error: Criteria Term Cannot Be Empty. Please Add at Least 1 Criteria Term.');
        return false;
    }

    // Check for duplicate criteria terms
    const duplicateTerm = checkDuplicate(criteriaTermValues);
    if (duplicateTerm) {
        displayToastNotification('error', `Error: Criteria Term "${duplicateTerm}" is Duplicated. Please Update Criteria Terms to be Unique.`);
        return false;
    }

    let url = '/dashboard/cycles/criteria/create';

    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            criteria_id: criteriaId, 
            criteria_name: criteriaName,
            cycle_id: cycleId,
            criteria_terms: criteriaTermValues,
        }, 
        function(response) {
            if (response.success === true) {
                fetchActiveCycle();
                displayToastNotification('success', 'Criteria Created Successfully');
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.success === 'boolean') {
            displayToastNotification("error", jqXHR.responseJSON.message);
        } else {
            displayToastNotification('error', 'Error: Unable to Create Criteria');
        }
    });
}

function checkDuplicate(arr) {
    let termCount = {};
    for (let term of arr) {
        if (termCount[term]) {
            return term;
        }
        termCount[term] = 1;
    }
    return false;
}

function handleCriteriaAdminDisplay(criteriaName) {
    let criteriaForm = $('#criteria-name-term-form');
    let criteriaFormSuccess = $('#criteria-form-success');
    let criteriaTermsTableHeader = $('#criteria-terms-table-header');
    let criteriaTermsWarning = $('#criteria-terms-warning');

    if (criteriaName) {
        criteriaForm.attr("hidden", true);
        criteriaFormSuccess.attr("hidden", false);
        criteriaTermsWarning.attr("hidden", true);
        criteriaTermsTableHeader.html('Criteria Terms for ');
    } else {
        criteriaForm.attr("hidden", false);
        criteriaFormSuccess.attr("hidden", true);
        criteriaTermsWarning.attr("hidden", false);
        criteriaTermsTableHeader.html('Criteria Terms');
    }

    let criteriaNameElements = $('.criteria-name-text');
    criteriaNameElements.each(function() {
        $(this).html(criteriaName);
    });
}

function fetchCriteriaTerms() {
    $('#criteria-terms-list-table').DataTable().ajax.reload();
}

function setupCriteriaTermsInput() {
    // Create 15 input rows with incrementing IDs
    for (let i = 0; i < 20; i++) {
        $('#criteria-term-input-container').append(`
            <div class="criteria-term-input-row d-flex flex-row align-items-center w-100" ${i === 0 ? '' : 'hidden'}>
                <input id="text-input-criteria-term-${i}" type="text"
                        class="bx--text-input pt-4 pb-4 mb-3" name="text-input-criteria-term"
                        placeholder="Criteria Term">
                <button class="add-criteria-input-btn bx--btn--tertiary ml-3 h6" style="width: 2rem; height: 2rem;">+</button>
            </div>
        `);
    }

    // Handle button clicks to show the next row and remove the current button
    $('#criteria-term-input-container').on('click', '.add-criteria-input-btn', function() {
        const currentRow = $(this).closest('.criteria-term-input-row');
        const nextRow = currentRow.next('.criteria-term-input-row');

        if (nextRow.length) {
            nextRow.attr('hidden', false);
        }
        currentRow.find('.add-criteria-input-btn').remove(); // Remove current button
    });
}

function handleCloseMenuOnClickOutside() {
    $(document).on('click', function(event) {
        // Check if the clicked element is not the menu trigger or inside the menu
        if (!$(event.target).closest('.bx--overflow-menu__trigger').length && !$(event.target).closest('.bx--overflow-menu-options').length) {
            $('.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
        }
    });
}

//save and edit description for weight criteria admin
function saveDescription() {
    let id = $('#descriptionId').val();
    let description = $('#descriptionInput').val().trim();
    let criteria_name_id = $('#criteriaNameId').val();

    if (description === '') {
        description = null;
    }
    
    $.ajax({
        url: '/dashboard/cycles/criteria/terms/update',
        type: 'POST',
        data: { id: id, description: description, criteria_name_id: criteria_name_id, rhombus_token: rhombuscookie() },
        success: function (response) {
            if (response.success) {
                displayToastNotification('success', 'Description updated successfully');
                let row = $(`.edit-description-btn[data-id="${id}"]`).closest('tr');
                row.find('td:eq(1)').text(description || '');
                row.find('.edit-description-btn').attr('data-description', description || '');
            } else {
                displayToastNotification('error', 'Error updating description');
            }
        },
        error: function () {
            alert('Failed to update description.');
            displayToastNotification('error', 'Failed to update description.');
        }
    });
    $('div.bx--modal.bx--modal-tall').removeClass('is-visible');
}

//delete description for weight criteria admin
function deleteDescription(event) {
    event.stopPropagation();
    let id = $(this).data('id');
    let criteria_name_id = $(this).data('criteria-name-id');
    if (confirm('Are you sure you want to delete this description?')) {
        $.ajax({
            url: '/dashboard/cycles/criteria/terms/delete',
            type: 'POST',
            data: { id: id, criteria_name_id: criteria_name_id, rhombus_token: rhombuscookie() },
            success: function (response) {
                if (response.success) {
                    displayToastNotification('success', 'Description deleted successfully');
                    let row = $(`.delete-description-btn[data-id="${id}"]`).closest('tr');
                    row.find('td:eq(1)').text('');
                    row.find('.edit-description-btn').attr('data-description', '');
                    row.find('.delete-description-btn').attr('data-description', '');
                } else {
                    displayToastNotification('error', 'Error deleting description');
                }
            },
            error: function (xhr, status, error) {
                displayToastNotification('error', 'Failed to delete description.');
            }
        });
    }
    closeMenu($(this));
}
 
$(onReady); 
  
if (!window._rb) window._rb = {
    onReady,
    fetchCycles,
    fetchDeletedCycles,
    createCycle,
    toggleMenu,
    closeMenu,
    closeEditModal,
    updateCycle,
    setActive,
    showEditModal,
    deleteCycle,
    toggleShowDeleted,
    saveCycle,
    fetchActiveCycle,
    createCriteria,
    checkDuplicate,
    handleCriteriaAdminDisplay,
    fetchCriteriaTerms,
    setupCriteriaTermsInput,
    handleCloseMenuOnClickOutside
}