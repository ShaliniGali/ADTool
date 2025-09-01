"use strict";

function save_file(uploadObj) {
    let file = $(uploadObj.getFileInputId())[0].files[0];

    upload_file(file, uploadObj);
}

function upload_file(file, uploadObj) {
    $(uploadObj.getSaveFileId()).prop('disabled', true).addClass('bx--btn--disabled');

    displayToastNotification('success', "Uploading File");

    process_file(file, uploadObj, upload_workbook);
}

function check_upload_sheet_name(uploadObj, fileInput) {
    let file = fileInput.files[0];
    
    $(uploadObj.getSaveFileId()).prop('disabled', true).addClass('bx--btn--disabled');

    displayToastNotification('success', "Processing Uploaded File");

    process_file(file, uploadObj, set_workbook);
}

function process_file(file, uploadObj, callback) {
    let filePromise = file.arrayBuffer(8);

    Promise.resolve(filePromise).then(
        function(buffer) {
            callback(uploadObj, buffer);
        }
    ).catch(function(error) {
        displayToastNotification("error", "Unable to process upload");
    });
}

function validate_file_type(uploadObj) {
    let file = $(uploadObj.getFileInputId())[0].files[0],
        result = true;
    
    if (uploadObj.getValidFileTypes().indexOf(file.type) == -1) {
        displayToastNotification("error", "File must be XLSX only.");

        result = false;
    }

    return result;
}

function validate_file_size(uploadObj) {
    let file = $(uploadObj.getFileInputId())[0].files[0];

    const DB_MAX_FILE_SIZE = uploadObj.getUploadSizePossible();

    if (file.size > DB_MAX_FILE_SIZE) {
        displayToastNotification("error", "File Size greater than "+DB_MAX_FILE_SIZE/Math.pow(1024,2) +"MB, please upload smaller file.");

        return false;
    }
}

function upload_workbook(uploadObj, buffer = false) {
    if (
        validate_file_type(uploadObj) === false || 
        validate_file_size(uploadObj) === false
    ) {
        return false;
    }
    let file = $(uploadObj.getFileInputId())[0].files[0];
    
    if (buffer !== false) {
        let workbook = XLSX.read(buffer, { type: "array" });
        let data = XLSX.write(workbook, {bookType: "xlsx", type: "array"});
        let form_data = new FormData();

        form_data.append('file', new Blob(
                [data]
        ), file.name);
        form_data.append('rhombus_token', rhombuscookie());
        
        if (uploadObj.validate()) {
            uploadObj.processFormData(form_data);
        }

        $.ajax(
            uploadObj.getUploadFileUrl(), {
            data: form_data,
            processData: false,
            contentType: false,
            method: 'POST',
            success: function (data) {
                if (data.status === true) {
                    displayToastNotification("success", data.messages.join('<br />'));
                } else {
                    displayToastNotification("error", data.messages.join('<br />'));
                }

                TabbedUpload.getCurrentTabObj().resetForm();
                
                $(TabbedUpload.getCurrentTabObj().getUploadTableId()).DataTable().ajax.reload(() => {
                    $(uploadObj.getFileListTab()).trigger('click');
                });
            },
            error: function(jqXHR) {
                if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.status === 'boolean') {
                    displayToastNotification("error", jqXHR.responseJSON.messages.join('<br />'));
                } else {
                    displayToastNotification("error", 'Failed to save upload.');
                }
            },
            complete: function() {
                $(TabbedUpload.getCurrentTabObj().getSaveFileId()).prop('disabled', false).removeClass('bx--btn--disabled');
            }
        });
    } else {
        return false;
    }
}

function set_workbook(uploadObj, buffer = false) {
    const SHEET_NAMES_POSSIBLE = uploadObj.getSheetNamesPossible();

    if (
        validate_file_type(uploadObj) === false || 
        validate_file_size(uploadObj) === false
    ) {
        return false;
    }
    
    let result = false, workbook;

    if (buffer !== false) {
        workbook = XLSX.read(buffer, { type: "array" });
    }

    if (
        typeof workbook.SheetNames === 'object' &&
        (
            workbook.SheetNames instanceof Array
        ) && 
        (
            workbook.SheetNames.length === SHEET_NAMES_POSSIBLE.length && 
            workbook.SheetNames.sort().toString() === SHEET_NAMES_POSSIBLE.toString()
        )
    ) {
        
        $(uploadObj.getVersionInputId()).val(sanitizeHtml(workbook.Sheets.metadata['D2']?.v, {allowedAttributes:{}, allowedTags:[]}));
        $(uploadObj.getTitleInputId()).val(sanitizeHtml(workbook.Sheets.metadata['B2']?.v, {allowedAttributes:{}, allowedTags:[]}));
        $(uploadObj.getDescriptionInputId()).html(sanitizeHtml(workbook.Sheets.metadata['C2']?.v, {allowedAttributes:{}, allowedTags:[]}));
        
        result = true;
    } else if (SHEET_NAMES_POSSIBLE.length !== 0) {
        displayToastNotification("error", `${SHEET_NAMES_POSSIBLE} SheetNames found in workbook`);

        result = false;
    } else {
        result = true;
    }

    if (result === true) {
        $(uploadObj.getSaveFileId()).prop('disabled', false).removeClass('bx--btn--disabled');
        displayToastNotification("success", "Proceed to upload");
    }

    return result;
}
/*
* Overflow menu for unprocessed uploads
* 
*/
function show_menu(element,parent) {
    let elem = $(element).next('div.bx--overflow-menu-options');
    if (elem.hasClass('bx--overflow-menu-options--open')) {
        elem.removeClass('bx--overflow-menu-options--open');
    } else {
        $(`${parent} div.bx--overflow-menu-options--open`).removeClass('bx--overflow-menu-options--open');
        elem.addClass('bx--overflow-menu-options--open');
    }
}

function delete_upload(uploadObj, elem) {
    set_status_xhr(uploadObj.getDeleteUrl(), $(elem), uploadObj);
}

function cancel_upload(uploadObj, elem) {
    set_status_xhr(uploadObj.getCancelUrl(), $(elem), uploadObj);
}

function process_upload(uploadObj, elem) {
    set_status_xhr(uploadObj.getProcessUrl(), $(elem), uploadObj);
}

function set_status_xhr(url, elem, uploadObj) {
    elem.closest('div.bx--overflow-menu-options-options').removeClass('bx--overflow-menu-options-options--open');
    
    let data = {file: elem.attr('file'), rhombus_token: rhombuscookie(), name: uploadObj.getName()};

    $.ajax(url, {
        method: 'POST',
        data: data,
        success: function (data) { 
            displayToastNotification("success", data.status);

            $(uploadObj.getUploadTableId()).DataTable().ajax.reload();
            $(uploadObj.getProcessedTableId()).DataTable().ajax.reload();
        },
        error: function (jqXHR) {
            if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.status === 'string') {
                displayToastNotification("error", jqXHR.responseJSON.status);
            } else {
                displayToastNotification("error", 'Error processing the file.');
            }
        }
    });
}

function onReadyFileUpload() {
    TabbedUpload.onReadyMain();

    getTableUploadMessages().then(function(data) {
        addNotifications(data);
    });
}

function getTableUploadMessages() {
    let url="/dashboard/import_upload/messages/"

    return $.ajax(url, {
        method: 'GET',
        dataType: 'json',
        }).then(function(data) {
            displayToastNotification("success", "Notifications Loaded");
            return JSON.parse(data);
        }).catch(function(jqXHR) {
            if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.status === 'string') {
                displayToastNotification("error", jqXHR.responseJSON.status);
            } else {
                displayToastNotification("error", 'Error retrieving notification');
            }
        });
}

function acknowledgeNotification(message_id) {
    let url="import_upload/acknowledge_message"

    let data = {}

    data['message_id'] = message_id
    data['rhombus_token'] = rhombuscookie();

    $.ajax(url, {
        method: 'POST',
        data: data,
        success: function (data) { 
            data = JSON.parse(data)
            displayToastNotification("success", "Acknowledged notification");

            const table = document.querySelector('#upload-notifications tbody');
            const child = document.getElementById(message_id);
            table.removeChild(child);
        },
        error: function (jqXHR) {
            if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.status === 'string') {
                displayToastNotification("error", jqXHR.responseJSON.status);
            } else {
                displayToastNotification("error", 'Error acknowledging notification');
            }
        }
    });
}

function addNotifications(notificationData) {
    const tbody = document.querySelector('#upload-notifications tbody');

    if (notificationData.pending_messages.length === 0 && notificationData.stream_messages.length == 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan='4' style='text-align: center'>No Notifications</td>`
        tbody.appendChild(tr);
        return
    }

    function addRows(messages, status, isUnread) {
        messages.forEach(([id, content]) => {
            const tr = document.createElement('tr');
            tr.id = id;

            if (isUnread) {
                tr.classList.add('unread-message');
            }

            const unixTime = parseInt(id.split("-")[0]);
            const time = new Date(unixTime);

            tr.innerHTML = `
                <td>${content.message}</td>
                <td>${time.toLocaleString()}</td>
                <td>${status}</td>
                <td>
                    <button class="bx--btn bx--btn--secondary bx--btn--sm px-1 delete-btn">
                        Clear
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    addRows(notificationData.stream_messages, "New", true);
    addRows(notificationData.pending_messages, "", false);
    
    
}

function updateDataTables(uploadObj) {
    $(uploadObj.getUploadTableId()).DataTable().ajax.reload();
    $(uploadObj.getProcessedTableId()).DataTable().ajax.reload();
}

function getProcessedFileUploadDataTable(uploadObj) {
    $(uploadObj.getProcessedTableId()).DataTable({
        destroy: true,
        info: 'Processed Upload Files and Results',
        autoWidth: false,
        ajax: {
            url: uploadObj.getProcessedUrl(),
            dataSrc: 'data'
        },
        columns: uploadObj.getProcessedTableColumns(),

        "order": []
    });
}

function getAdminFileUploadDataTable(uploadObj) {
    $(uploadObj.getAdminTableId()).DataTable({
        destroy: true,
        info: 'Admin Upload Files and Results',
        autoWidth: false,
        ajax: {
            url: uploadObj.getAdminUrl(),
            dataSrc: 'data'
        },
        columns: uploadObj.getAdminTableColumns(),

        "order": []
    });
}


function activate_upload(id) {
    let url='/dashboard/import_upload/upload_file';

    let data = {}

    data['row_id'] = id;
    data['name'] = TabbedUpload.getCurrentTabObj().getName();
    data['rhombus_token'] = rhombuscookie();

    $.ajax(url, {
        method: 'POST',
        data: data,
        success: function (data) { 
            displayToastNotification("success", "Activate upload successful");
        },
        error: function (jqXHR) {
            if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.status === 'string') {
                displayToastNotification("error", jqXHR.responseJSON.status);
            } else {
                displayToastNotification("error", 'Error activating upload');
            }
        }
    });
}

function getNewFileUploadDataTable(uploadObj) {
    // Suppress DataTables warnings globally for this table
    $.fn.dataTable.ext.errMode = 'none';
    
    let upload_table_columns = uploadObj.getUploadTableColumns();
    $(uploadObj.getUploadTableId()).DataTable({
        destroy: true,
        info: 'User Uploaded Files',
        autoWidth: false,
        ajax: {
            url: uploadObj.getUploadListUrl(),
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                // Suppress DataTables AJAX warnings
                console.log('DataTable AJAX warning suppressed:', thrown);
            }
        },
        "columns": upload_table_columns,
        "order": [],
        "language": {
            emptyTable: "No uploads found"
        },
        "createdRow": function(row, data, index) {
            let elem = $('td', row).eq(upload_table_columns.length - 1).find('div.bx--overflow-menu > button.bx--overflow-menu__trigger');					
            elem.removeClass('d-none').on('click', function(){ show_menu(this,TabbedUpload.getCurrentTabObj().getUploadTableId())});
            elem.next('div.bx--overflow-menu-options').find('button[role=cancel]').prop('disabled', false).attr('file', data['ID']).on('click', function() { cancel_upload(TabbedUpload.getCurrentTabObj(), this) });
            elem.next('div.bx--overflow-menu-options').find('button[role=edit]').prop('disabled', false).attr('file', data['ID']).on('click', function() { process_upload(TabbedUpload.getCurrentTabObj(), this) });
            elem.next('div.bx--overflow-menu-options').find('button[role=delete]').prop('disabled', false).attr('file', data['ID']).on('click', function() { delete_upload(TabbedUpload.getCurrentTabObj(), this) });
        }
    });
}

$(onReadyFileUpload);