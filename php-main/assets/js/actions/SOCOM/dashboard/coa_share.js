"use strict";
    
function onReady() {
    
    const $radioButtons = $('input[name="use_iss_extract_share_coa"]');

    // Initialize My COA Table (inside modal)
    $("#my-coa-table").DataTable({
        columnDefs: [
            {
                targets: 0,
                title: 'Select',
                searchable: false,
                orderable: false,
                className: 'select-header dt-body-center',
                render: function (data, type, full, meta){
                    return '<input type="checkbox" name="selected_coa[]" value="">';
                }
            },
            { targets: 1, title: 'COA Name', data: "COA_TITLE"},
            { targets: 2, title: 'COA Description', data: "COA_DESCRIPTION" },
            { targets: 3, title: 'Created Date', data: "CREATED_DATETIME" },
        ],
        initComplete: function() {
            $('#my-coa-table .select-header').removeClass('sorting_asc');
        },
        ajax: {
            url: "/dashboard/coa_management/get_my_coa",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
                use_iss_extract: function() { return $radioButtons.filter(':checked').val() === "true"; }
            },
        },
        rowCallback: function (row, data) {
            $('td:eq(0) input', row).val(data['ID']);
            return data;
        },
    });
    
    // "Shared By Me" Table
    $("#coa-shared-by-me-table").DataTable({
        columnDefs: sharedCoaByMeColDef,
        initComplete: function() {},
        ajax: {
            url: "/dashboard/coa_management/get_coa_shared_by_me",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
                is_revoked: 0,
                use_iss_extract: function() { return $radioButtons.filter(':checked').val() === "true"; }
            },
        },
        rowCallback: function (row, data) {},
        createdRow: function (row, data, index) {
            if (data) {
                let actionBtn = $('td', row).eq(sharedCoaByMeColDef.length - 1).find('div.bx--overflow-menu > button.bx--overflow-menu__trigger');
                actionBtn.next('div.bx--overflow-menu-options').find('button[role=delete]').prop('disabled', false).attr({'shared-coa-id': data['SHARED_COA_ID']}).on('click', function() {
                    revokeCoa(this, 'SHARED_BY_ME');
                });
                actionBtn.on('click', toggleMenu);
            } else {
                $('td', row).eq(cycleColumnDefinition.length - 1).empty();
            }
        },
        order: [],
        autoWidth: false
    });

    // "Shared To Me" Table
    $("#coa-shared-to-me-table").DataTable({
        columnDefs: sharedCoaToMeColDef,
        initComplete: function() {},
        ajax: {
            url: "/dashboard/coa_management/get_coa_shared_to_me",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
                is_revoked: 0,
                use_iss_extract: function() { return $radioButtons.filter(':checked').val() === "true"; }
            },
        },
        rowCallback: function (row, data) {},
        createdRow: function (row, data, index) {
            if (data) {
                let actionBtn = $('td', row).eq(sharedCoaToMeColDef.length - 1).find('div.bx--overflow-menu > button.bx--overflow-menu__trigger');
                actionBtn.next('div.bx--overflow-menu-options').find('button[role=delete]').prop('disabled', false).attr({'shared-coa-id': data['SHARED_COA_ID']}).on('click', function() {
                    revokeCoa(this, 'SHARED_TO_ME');
                });
                actionBtn.on('click', toggleMenu);
            } else {
                $('td', row).eq(cycleColumnDefinition.length - 1).empty();
            }
        },
        order: [],
        autoWidth: false
    });

    $("#select-user-emails").select2({
        placeholder: "Select User Email(s)",
    });

    // Initialize the following
    let openShareCoaModalBtn = $('#share-coa-modal-btn');
    openShareCoaModalBtn.on('click', openShareCoaModal);

    $('#coa-shared-by-me-revoked-checkbox').on('click', function() {
        toggleShowRevoked(this, 'SHARED_BY_ME');
    });

    $('#coa-shared-to-me-revoked-checkbox').on('click', function() {
        toggleShowRevoked(this, 'SHARED_TO_ME');
    });

    const radioButtons = $('input[name="use_iss_extract_share_coa"]');
    radioButtons.on('change', function () {
        radioButtons.prop('disabled', true); 
        
        $('input[name="use_iss_extract_share_coa"]').prop('disabled', false);
        $('input[name="use_iss_extract_share_coa"]').next('label').removeClass('bx--tile--is-selected');
        $('input[name="use_iss_extract_share_coa"]:checked').next('label').addClass('bx--tile--is-selected');
        console.log($('input[name="use_iss_extract_share_coa"]:checked').val());
        console.log($('input[name="use_iss_extract_share_coa"]:checked').val());
        $('#coa-shared-by-me-table').DataTable().ajax.reload();
        $('#coa-shared-to-me-table').DataTable().ajax.reload();
        $('#my-coa-table').DataTable().ajax.reload();
    });

    handleCloseMenuOnClickOutside();
};

function fetchMyCoa() {
    let url = '/dashboard/coa_management/get_my_coa';
    let use_iss_extract = $('input[name="use_iss_extract_share_coa"]:checked').val();

    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            use_iss_extract: use_iss_extract
        }, 
        function(response) {
            if (response.success === true) {
                const coas = response.data;

                let myCoaTable = $('#my-coa-table').DataTable();

                myCoaTable.clear().rows.add(coas).draw();
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to Fetch COAs');
    });
}

function fetchCoaSharedByMe(isRevoked = 0) {
    let url = '/dashboard/coa_management/get_coa_shared_by_me';
    let use_iss_extract = $('input[name="use_iss_extract_share_coa"]:checked').val();

    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            is_revoked: isRevoked,
            use_iss_extract: use_iss_extract
        }, 
        function(response) {
            if (response.success === true) {
                const coas = response.data;

                let coaSharedByMeTable = $('#coa-shared-by-me-table').DataTable();

                const actionsColumnIndex = 4;
                if (isRevoked === 1) {
                    coaSharedByMeTable.column(actionsColumnIndex).visible(false);

                    if (coas.length > 0) {
                        displayToastNotification('success', 'Displaying Revoked COAs');
                    } else {
                        displayToastNotification('success', 'No Revoked COAs to Display');
                    }
                } else {
                    // Hide Actions menu when showing "Revoked" COAs
                    coaSharedByMeTable.column(actionsColumnIndex).visible(true); 
                }

                coaSharedByMeTable.clear().rows.add(coas).draw();
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to Fetch COAs');
    });
}

function fetchCoaSharedToMe(isRevoked = 0) {
    let url = '/dashboard/coa_management/get_coa_shared_to_me';
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            is_revoked: isRevoked
        }, 
        function(response) {
            if (response.success === true) {
                const coas = response.data;

                let coaSharedToMeTable = $('#coa-shared-to-me-table').DataTable();

                coaSharedToMeTable.clear().rows.add(coas).draw();
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to Fetch COAs');
    });
}

function openShareCoaModal() {
    $('#coa_share_view_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
}

function closeShareCoaModal() {
    $("#coa_share_view_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
}

function toggleMenu() {
    let menuElem = $(this).next('div.bx--overflow-menu-options');
    if (menuElem.hasClass('bx--overflow-menu-options--open')) {
        menuElem.removeClass('bx--overflow-menu-options--open');
    } else {
        $('div.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
        menuElem.addClass('bx--overflow-menu-options--open');
    }
};

function closeMenu(buttonElem) {
    buttonElem.closest('.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
};

function revokeCoa(buttonElem, tableType) {
    const sharedCoaId = $(buttonElem).attr('shared-coa-id');

    let url = '/dashboard/coa_management/revoke_coa';

    $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            shared_coa_id: sharedCoaId
        }, 
        function(response) {
            if (response.success === true) {
                switch (tableType) {
                    case 'SHARED_BY_ME':
                        fetchCoaSharedByMe();
                        break;
                    case 'SHARED_TO_ME':
                        fetchCoaSharedToMe();
                        break;
                }
                displayToastNotification('success', 'Successfully Revoked COA');
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to Revoke COA');
    });

    closeMenu($(buttonElem));
};

function toggleShowRevoked(checkbox) {
    const isChecked = $(checkbox).is(':checked');

    if (isChecked) {
        // Show Revoked COAs Shared by Me where IS_REVOKED = 1
        fetchCoaSharedByMe(1);
    } else {
        // Show All COAs Shared by Me where IS_REVOKED = 0
        fetchCoaSharedByMe(0);
    }
}

function shareCoa() {
    let selectedCoas = []; // [{ ID, COA_TITLE, COA_DESCRIPTION, SAVED_COA_ID }]

    let myCoaTable = $('#my-coa-table').DataTable();

    // Loop through each row in the DataTable and grab all data from 'checked' rows
    myCoaTable.rows().every(function () {
        let checkbox = $(this.node()).find('input[name="selected_coa[]"]');

        if (checkbox.is(':checked')) {
            let rowData = this.data();
            selectedCoas.push(rowData);
        }
    });

    let selectedEmailIds = $("#select-user-emails").val().map(function(id) {
        return parseInt(id);
    });

    if (selectedEmailIds.length === 0) {
        displayToastNotification('error', 'Error: Please select at least one user email');
        return false;
    }

    if (selectedCoas.length === 0) {
        displayToastNotification('error', 'Error: Please select at least one COA');
        return false;
    }

    let coaSharedByMeTable = $("#coa-shared-by-me-table").DataTable();

    let coaSharedByMe = coaSharedByMeTable.rows().data().toArray();

    // Create hash map for quick lookup { coaId: [userIds] }
    let coaSharedByMeMap = {};
    coaSharedByMe.forEach((coa) => {
        let coaId = coa.ORIGINAL_COA_ID;
        let userId = coa.NEW_USER_ID;

        if (coaSharedByMeMap[coaId]) {
            coaSharedByMeMap[coaId].push(userId);
        } else {
            coaSharedByMeMap[coaId] = [userId];
        }
    });

    // Check if COA(s) can be shared to selected user(s)
    for (let i = 0; i < selectedCoas.length; i++) {
        let selectedCoa = selectedCoas[i];
        let coaId = selectedCoa.ID;
        let coaTitle = selectedCoa.COA_TITLE;

        // Show error if user tries to share selected COA(s) that originated from selected users
        let foundOriginalUserId = selectedEmailIds.find((userId) => userId === selectedCoa.ORIGINAL_USER_ID);
        if (foundOriginalUserId !== undefined) {

            $('#already-shared-coa-error .bx--inline-notification__subtitle').html(sanitizeHtml(`
                <ul class="bx--list--unordered mt-2">
                    <li class="bx--list__item"><span class="font-weight-bold">${coaTitle}</span> originated from: <span class="font-weight-bold">${user_emails[foundOriginalUserId]}</span>.</li>
                    <li class="bx--list__item">Please re-select COAs and/or users and try again.</li>
                </ul>`, {
                    allowedTags: false,
                    allowedAttributes: false
                }
            ));

            $('#already-shared-coa-error').removeClass('d-none');

            return;
        }
        $('#already-shared-coa-error').addClass('d-none');

        // Show error if user tries to share selected COA(s) that have already been shared to selected users
        if (coaSharedByMeMap[coaId]) {
            const duplicateUserIds = selectedEmailIds.filter(id => coaSharedByMeMap[coaId].includes(id));

            if (duplicateUserIds.length > 0) {
                const duplicateUserEmails = duplicateUserIds.map(userId => user_emails[userId]);
    
                $('#already-shared-coa-error .bx--inline-notification__subtitle').html(sanitizeHtml(`
                    <ul class="bx--list--unordered mt-2">
                        <li class="bx--list__item"><span class="font-weight-bold">${coaTitle}</span> has already been shared to: <span class="font-weight-bold">${duplicateUserEmails.join(', ')}</span>.</li>
                        <li class="bx--list__item">Please re-select COAs and/or users and try again.</li>
                    </ul>`, {
                        allowedTags: false,
                        allowedAttributes: false
                    }
                ));
    
                $('#already-shared-coa-error').removeClass('d-none');

                return;
            }
        }
        $('#already-shared-coa-error').addClass('d-none');
    }

    let url = '/dashboard/coa_management/share_coa';
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            selected_email_ids: selectedEmailIds,
            selected_coas: selectedCoas
        }, 
        function(response) {
            if (response.success === true) {
                // Update "My Shared COA" table
                fetchCoaSharedByMe();
                closeShareCoaModal();
                displayToastNotification('success', 'Successfully Shared COAs');

                // Reset email select2 and checkboxes
                $('#select-user-emails').val(null).trigger('change');
                $('input[name="selected_coa[]"]').each(function() {
                    if ($(this).prop('checked')) {
                        $(this).prop('checked', false);
                    };
                });
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to Share COAs');
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

$(onReady); 
  
if (!window._rb) window._rb = {}
window._rb.onReady = onReady;