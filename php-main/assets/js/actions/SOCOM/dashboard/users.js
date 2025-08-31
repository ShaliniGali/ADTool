
"use strict"


function save_admin_status() {
    let gid = $('#admin-status-id > option:selected').val();
    gid = parseInt(gid);
    
    if (gid < 1 || gid > 2) {
        displayToastNotification('error', 'Group chosen is not available. Please refresh and try again');
        return false;
    }

    return $.post('/dashboard/myuser/admin/save', 
        {
            rhombus_token: rhombuscookie(),
            gid: gid
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Admin Status Request Sent');
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to save Admin Status'); });
}

function save_ao_ad_status() {
    let gid = $('#ao-ad-status-id > option:selected').val();
    if (gid < 1 || gid > 4) {
        displayToastNotification('error', 'AO or AD status chosen is not available. Please refresh and try again');
        return false;
    }

    return $.post('/dashboard/myuser/ao_ad/save', 
        {
            rhombus_token: rhombuscookie(),
            gid: gid
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'AO or AD Status Request Sent');
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to save AO or AD Status'); });
}

function save_cycle_status() {
    let gid = $('#cycle-status-id > option:selected').val();
    if (gid < 1 || gid > 4) {
        displayToastNotification('error', 'Cycle or Weight Criteria status chosen is not available. Please refresh and try again');
        return false;
    }

    return $.post('/dashboard/myuser/cycle_users/save', 
        {
            rhombus_token: rhombuscookie(),
            gid: gid
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Cycle or Weight Criteria Status Request Sent');
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to save Cycle or Weight Criteria Status'); });
}

function save_pom_status(){
    let gid = $('#pom-status-id > option:selected').val();
    if (gid < 1 || gid > 4) {
        displayToastNotification('error', 'Pom status chosen is not available. Please refresh and try again');
        return false;
    }

    return $.post('/dashboard/myuser/pom/save', 
        {
            rhombus_token: rhombuscookie(),
            gid: gid
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Pom Status Request Sent');
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to save Pom Status'); });
}

function save_cap_status(){
    let gid = $('#cap-status-id > option:selected').val();
    if (cap_groups_all.indexOf(gid) === -1) {
        displayToastNotification('error', 'Cap Sponsor status chosen is not available. Please refresh and try again');
        return false;
    }

    return $.post('/dashboard/myuser/cap/save', 
        {
            rhombus_token: rhombuscookie(),
            gid: gid
        }, 
        function(data) {
            if (data.status === true) {
                displayToastNotification('success', 'Cap Sponsor Status Request Sent');
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', 'Unable to save Cap Sponsor Status'); });
}

function onReady() {
    $('#admin-status-save').on('click', save_admin_status);

    $('#ao-ad-status-save').on('click', save_ao_ad_status);

    $('#cycle-status-save').on('click', save_cycle_status);

    $('#pom-status-save').on('click', save_pom_status);

    $('#cap-status-save').on('click', save_cap_status);
}


$(onReady);

if (!window._rb) { window._rb = {}; }
window._rb.save_admin_status = save_admin_status;
window._rb.save_ao_ad_status = save_ao_ad_status;
window._rb.save_cycle_status = save_cycle_status;
window._rb.save_pom_status = save_pom_status;
window._rb.save_cap_status = save_cap_status;
window._rb.onReady = onReady;