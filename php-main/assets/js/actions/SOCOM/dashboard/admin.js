"use strict"

let user_admin_table = function() {
    user_admin_table = null;

    return $(`#admin-list`).DataTable({
        columnDefs: [{
                targets: 0,
                data: "EMAIL",
                name: "Email",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 1,
                data: "GROUP",
                name: "Requested Status",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 2,
                data: 'IS_DELETED',
                name: "Active",
                defaultContent: '0',
                searchable: false
            },
            {
                targets: 3,
                data: 'UPDATED_DATETIME',
                name: "Last Updated Date",
                defaultContent: '0',
                searchable: true
            },
            {
                targets: 4,
                data: 'UPDATE_EMAIL',
                name: "Admin Approver",
                defaultContent: '0',
                searchable: true
            }
        ],
        ajax: {
            url: "/dashboard/admin/admin_users/list/get",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px',
        rowCallback: function(row, data) {
            if (['AO', 'AD'].indexOf(data['GROUP']) != -1) {
                $('td:eq(1)', row).html(data['GROUP'] + ' Admin');
            }

            let chk = data['IS_DELETED'] === 1 ? '' : 'checked', text = data['IS_DELETED'] === 0 ? 'Active' : 'Disabled', cb = $(`<div class="bx--form-item bx--checkbox-wrapper">
            <input id="admin-checkbox-${data['ID']}" class="bx--checkbox admin-group-status" type="checkbox" value="1" name="active" ${chk}>
            <label for="admin-checkbox-${data['ID']}" class="bx--checkbox-label">${text}</label>
          </div>`);
          
            $(cb).find('input.admin-group-status').data('EMAIL', data['EMAIL'])
            
            $('td:eq(2)', row).html(cb);
        }

    });
}

let user_ao_ad_table = function() {
    user_ao_ad_table = null;
   return  $(`#ao-ad-list`).DataTable({
        columnDefs: [{
                targets: 0,
                data: "EMAIL",
                name: "Email",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 1,
                data: "GROUP",
                name: "Requested Status",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 2,
                data: 'IS_DELETED',
                name: "Active",
                defaultContent: '0',
                searchable: false
            },
            {
                targets: 3,
                data: 'UPDATED_DATETIME',
                name: "Last Updated Date",
                defaultContent: '0',
                searchable: true
            },
            {
                targets: 4,
                data: 'UPDATE_EMAIL',
                name: "Admin Approver",
                defaultContent: '0',
                searchable: true
            }
        ],
        ajax: {
            url: "/dashboard/admin/ao_ad_users/list/get",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px',
        rowCallback: function(row, data) {
            if (['AO', 'AD'].indexOf(data['GROUP']) != -1) {
                $('td:eq(1)', row).html(data['GROUP']);
            }
            
            let chk = data['IS_DELETED'] === 1 ? '' : 'checked', text = data['IS_DELETED'] === 0 ? 'Active' : 'Disabled', cb = $(`<div class="bx--form-item bx--checkbox-wrapper">
            <input id="ao-ad-checkbox-${data['ID']}" class="bx--checkbox ao-ad-group-status" type="checkbox" value="1" name="ao-ad-active" ${chk}>
            <label for="ao-ad-checkbox-${data['ID']}" class="bx--checkbox-label">${text}</label>
          </div>`);
          
            $(cb).find('input.ao-ad-group-status').data('EMAIL', data['EMAIL'])
            
            $('td:eq(2)', row).html(cb);
        }

    });
}

let user_cycle_table = function() {
    user_cycle_table = null;
   return  $(`#cycle-list`).DataTable({
        columnDefs: [{
                targets: 0,
                data: "EMAIL",
                name: "Email",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 1,
                data: "GROUP",
                name: "Requested Status",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 2,
                data: 'IS_DELETED',
                name: "Active",
                defaultContent: '0',
                searchable: false
            },
            {
                targets: 3,
                data: 'UPDATED_DATETIME',
                name: "Last Updated Date",
                defaultContent: '0',
                searchable: true
            },
            {
                targets: 4,
                data: 'UPDATE_EMAIL',
                name: "Admin Approver",
                defaultContent: '0',
                searchable: true
            }
        ],
        ajax: {
            url: "/dashboard/admin/cycle_users/list/get",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px',
        rowCallback: function(row, data) {
            if (['Cycle', 'Weight'].indexOf(data['GROUP']) != -1) {
                $('td:eq(1)', row).html(data['GROUP']);
            }
            
            let chk = data['IS_DELETED'] === 1 ? '' : 'checked', text = data['IS_DELETED'] === 0 ? 'Active' : 'Disabled', cb = $(`<div class="bx--form-item bx--checkbox-wrapper">
            <input id="cycle-checkbox-${data['ID']}" class="bx--checkbox cycle-group-status" type="checkbox" value="1" name="cycle-active" ${chk}>
            <label for="cycle-checkbox-${data['ID']}" class="bx--checkbox-label">${text}</label>
          </div>`);
            
            $(cb).find('input.cycle-group-status').data('EMAIL', data['EMAIL'])
            
            $('td:eq(2)', row).html(cb);
        }

    });
}

let user_pom_table = function() {
    user_pom_table = null;
   return  $(`#pom-list`).DataTable({
        columnDefs: [{
                targets: 0,
                data: "EMAIL",
                name: "Email",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 1,
                data: "GROUP",
                name: "Pom Status",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 2,
                data: 'IS_DELETED',
                name: "Active",
                defaultContent: '0',
                searchable: false
            },
            {
                targets: 3,
                data: 'UPDATED_DATETIME',
                name: "Last Updated Date",
                defaultContent: '0',
                searchable: true
            },
            {
                targets: 4,
                data: 'UPDATE_EMAIL',
                name: "Admin Approver",
                defaultContent: '0',
                searchable: true
            }
        ],
        ajax: {
            url: "/dashboard/admin/pom_users/list/get",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px',
        rowCallback: function(row, data) {
            if (['Pom Admin', 'Pom User'].indexOf(data['GROUP']) !== -1) {
                $('td:eq(1)', row).html(data['GROUP']);
            }
            
            let chk = data['IS_DELETED'] === 1 ? '' : 'checked', text = data['IS_DELETED'] === 0 ? 'Active' : 'Disabled', cb = $(`<div class="bx--form-item bx--checkbox-wrapper">
            <input id="pom-checkbox-${data['ID']}" class="bx--checkbox pom-group-status" type="checkbox" value="1" name="pom-active" ${chk}>
            <label for="pom-checkbox-${data['ID']}" class="bx--checkbox-label">${text}</label>
          </div>`);
            
            $(cb).find('input.pom-group-status').data('EMAIL', data['EMAIL'])
            
            $('td:eq(2)', row).html(cb);
        }

    });
}

let user_cap_table = function() {
    user_cap_table = null;
   return  $(`#cap-sponsor-list`).DataTable({
        columnDefs: [{
                targets: 0,
                data: "EMAIL",
                name: "Email",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 1,
                data: "GROUP",
                name: "Cap Sponsor Status",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 2,
                data: 'IS_DELETED',
                name: "Active",
                defaultContent: '0',
                searchable: false
            },
            {
                targets: 3,
                data: 'UPDATED_DATETIME',
                name: "Last Updated Date",
                defaultContent: '0',
                searchable: true
            },
            {
                targets: 4,
                data: 'UPDATE_EMAIL',
                name: "Admin Approver",
                defaultContent: '0',
                searchable: true
            }
        ],
        ajax: {
            url: "/dashboard/admin/cap_users/list/get",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px',
        rowCallback: function(row, data) {
                $('td:eq(1)', row).html(data['GROUP']);
            
            let chk = data['IS_DELETED'] === 1 ? '' : 'checked', text = data['IS_DELETED'] === 0 ? 'Active' : 'Disabled', cb = $(`<div class="bx--form-item bx--checkbox-wrapper">
            <input id="cap-checkbox-${data['ID']}" class="bx--checkbox cap-group-status" type="checkbox" value="1" name="cap-active" ${chk}>
            <label for="cap-checkbox-${data['ID']}" class="bx--checkbox-label">${text}</label>
          </div>`);
            
            $(cb).find('input.cap-group-status').data('EMAIL', data['EMAIL'])
            
            $('td:eq(2)', row).html(cb);
        }

    });
}

function set_cycle_status() {
    let sid, email;
    if (this.checked === true) {
        sid = 0
    } else {
        sid = 1
    }
    email = $(this).data('EMAIL');

    return $.post('/dashboard/admin/cycle_users/status/save', 
        {
            rhombus_token: rhombuscookie(),
            sid: sid,
            email: email
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Cycle or Weight Criteria Status Change Complete');

                $('#cycle-list').DataTable().ajax.reload();
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to change Cycle or Weight Criteria Status'); });
}

function set_ao_ad_status() {
    let sid, email;
    if (this.checked === true) {
        sid = 0
    } else {
        sid = 1
    }
    email = $(this).data('EMAIL');

    return $.post('/dashboard/admin/ao_ad_users/status/save', 
        {
            rhombus_token: rhombuscookie(),
            sid: sid,
            email: email
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'AO or AD Commenter Status Change Complete');

                $('#ao-ad-list').DataTable().ajax.reload();
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to change AO or AD Commenter Status'); });
}

function set_admin_status() {
    let sid, email;
    if (this.checked === true) {
        sid = 0
    } else {
        sid = 1
    }
    email = $(this).data('EMAIL');

    return $.post('/dashboard/admin/admin_users/status/save', 
        {
            rhombus_token: rhombuscookie(),
            sid: sid,
            email: email
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Admin Status Change Complete');

                $('#admin-list').DataTable().ajax.reload();
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to change Admin Status'); });
}

function set_pom_status(){
    let sid, email;
    if (this.checked === true) {
        sid = 0
    } else {
        sid = 1
    }
    email = $(this).data('EMAIL');

    return $.post('/dashboard/admin/pom_users/status/save', 
        {
            rhombus_token: rhombuscookie(),
            sid: sid,
            email: email
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Pom Status Change Complete');

                $('#pom-list').DataTable().ajax.reload();
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to change Pom Status'); });
}

function set_cap_status(){
    let sid, email;
    if (this.checked === true) {
        sid = 0
    } else {
        sid = 1
    }
    email = $(this).data('EMAIL');

    return $.post('/dashboard/admin/cap_users/status/save', 
        {
            rhombus_token: rhombuscookie(),
            sid: sid,
            email: email
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Cap Sponsor Status Change Complete');

                $('#cap-sponsor-list').DataTable().ajax.reload();
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to change Cap Sponsor Status'); });
}

function onReady() {
    user_cycle_table();

    user_ao_ad_table();

    user_admin_table();

    user_pom_table();

    user_cap_table();
    
    $('#admin-list').on('change', 'input.admin-group-status', set_admin_status);
    $('#ao-ad-list').on('change', 'input.ao-ad-group-status', set_ao_ad_status);
    $('#cycle-list').on('change', 'input.cycle-group-status', set_cycle_status);
    $('#pom-list').on('change', 'input.pom-group-status', set_pom_status);
    $('#cap-sponsor-list').on('change', 'input.cap-group-status', set_cap_status);
}

$(onReady);

if (!window._rb) { window._rb = {}; }
window._rb.onReady = onReady;
window._rb.user_admin_table = user_admin_table;
window._rb.user_ao_ad_table = user_ao_ad_table;
window._rb.user_cycle_table = user_cycle_table;
window._rb.user_pom_table = user_pom_table;
window._rb.user_cap_table = user_cap_table;
window._rb.set_admin_status = set_admin_status;
window._rb.set_ao_ad_status = set_ao_ad_status;
window._rb.set_cycle_status = set_cycle_status;
window._rb.set_pom_status = set_pom_status;
window._rb.set_cap_status = set_cap_status;