class ZBTIssue extends TabbedUpload { 
    #cid;

    #adminTableId  = '#admin-list5'
    #adminUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/results_list_view_admin'


    constructor() {
        let name = 'ZBTIssue';
        let validFileTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        let uploadSizePossible = 20971520;
        let sheetNamesPossible = [];
        let fileInputLabel = '#file-input-label4'
        let fileInputId = '#dashboard-file-uploader4'
        let uploadFormId = '#upload-form4'
        let saveFileId = '#save-file4'
        let uploadTableId = '#upload-list4'
        let uploadFileUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/upload_file'
        let uploadListUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/list_view'
        let deleteUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/delete_file'
        let cancelUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/cancel_file'
        let processUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/process_file'
        let processedTableId  = '#processed-list4'
        let processedUrl = '/dashboard/import_upload/database_save_zbt_issue_data_upload/results_list_view'
        let versionInputId =  '#version-input4'
        let titleInputId =  '#title-input4'
        let descriptionInputId =  '#description4'
        let tabId = '#tab-myuser-4'
        let uploadTab = '#upload-tab4'
        let fileListTab = '#file-list-tab4'


        super(
            name,
            validFileTypes,
            uploadSizePossible,
            sheetNamesPossible,
            fileInputLabel,
            fileInputId,
            uploadFormId,
            saveFileId,
            uploadTableId,
            uploadFileUrl,
            uploadListUrl,
            deleteUrl,
            cancelUrl,
            processUrl,
            processedTableId,
            processedUrl,
            versionInputId,
            titleInputId,
            descriptionInputId,
            tabId,
            uploadTab,
            fileListTab
        )
    }
    #yearInputId = '#year-input4'
    #tableListingInputId = '#table-listing-input4'

    getAdminTableId() {
        return this.#adminTableId
    }

    getAdminUrl() {
        return this.#adminUrl;
    }
    
    getYearInputId() {
        return this.#yearInputId;
    }

    getTableListingInputId() {
        return this.#tableListingInputId;
    }

    processFormData(form_data) {
        form_data.append('year', $(this.getYearInputId()).val());
        form_data.append('table-listing', $(this.getTableListingInputId()).val());
        super.processFormData(form_data);
    }

    resetForm() {
        $(this.getYearInputId()).val(0);
        $(this.getTableListingInputId()).val(0);
        super.resetForm(this);
    }

    clearInterval() {
        if (this.cid !== undefined) {
            clearInterval(this.cid);
        }
    }

    #adminApproveBtnId = '#admin-approve-btn'
    getAdminApproveBtnId() {
        return this.#adminApproveBtnId
    }
    #adminApproveDescr = '#admin-approve-descr'
    getAdminApproveDescr() {
        return this.#adminApproveDescr
    }
    #adminApproveSubmitBtnCls = '.admin-submit-btn'
    getAdminApproveClass() {
        return this.#adminApproveSubmitBtnCls
    }
    
    #capSponsorSubmitBtnId = '#cap-sponsor-submit-btn'
    getCapSponsorSubmitBtnId() {
        return this.#capSponsorSubmitBtnId
    }
    #capSonsporDesc = '#desc-sponsor-submit-btn'
    getCapSonsorDesc() {
        return this.#capSonsporDesc
    }
    #capSponsorRowSubmitBtnCls = '.cap-sponsor-submit-btn'
    getCapSponsorBtnCls() {
        return this.#capSponsorRowSubmitBtnCls
    }

    saveAdminApprove(data) {
        console.log(data)
        let textarea;
        if ($(this).hasClass(this.getAdminApproveClass())) {
            textarea = $(this.getAdminApproveDescr()).val();
        }

        // create ajax to save submission, textarea content optional
        const postdata = {
            description: textarea,
            map_id: data.ID,
            rhombus_token: rhombuscookie()
        }
        let status = 'success';
        return $.post('/dashboard/import_upload/save_approve', postdata, function(response) {
            console.log('Admin approve success:', response);
        }).fail(function(jqXHR) {
            console.error('Admin approve failed:', jqXHR.responseText);
            status = 'fail';
        });
    }

    saveCapUserSubmit(data) {
        console.log(data)
        let textarea = '';
        if ($(this).hasClass(this.getCapSponsorBtnCls())) {
            textarea = $(this.getCapSonsorDesc()).val()
        } 

        // create ajax to save submission, textarea content optional
        const postdata = {
            description: textarea,
            map_id: data.ID,
            rhombus_token: rhombuscookie()
        }
         $.post('/dashboard/import_upload/save_submit', postdata, function(response) {
            console.log('Cap User submit success:', response);
        }).fail(function(jqXHR) {
            console.error('Cap User submit failed:', jqXHR.responseText);
        });
    }

    parseTable(row_id) {
        let url='/dashboard/import_upload/database_save_zbt_issue_data_upload/parse_file';
    
        let data = {}
        data['row_id'] = row_id;
        data['rhombus_token'] = rhombuscookie();
    
        let status = 'success';
        $.ajax(url, {
            method: 'POST',
            data: data,
            success: function (data) { 
                status = "success";
            },
            error: function (jqXHR) {
                status = "fail";
            }
        });
        return status;
    }

    upsertTable(position) {
        let url='/dashboard/import_upload/database_save_zbt_issue_data_upload/upsert_file';
    
        let data = {}
        data['position'] = position;
        data['rhombus_token'] = rhombuscookie();
    
        let status = 'success';
        $.ajax(url, {
            method: 'POST',
            data: data,
            success: function (data) { 
                $('#admin-list5').DataTable().ajax.reload()
                status = "success";
                if (data?.detail) {
                    status = data['detail'];
                }
            },
            error: function (jqXHR) {
                status = "fail";
            }
        });
        return status;
    }


    getProcessedTableColumns() { return get_processed_table_columns_zbt_issue(); }

    getAdminTableColumns() { return get_admin_table_columns_zbt_issue(); }

    onReady() {
        super.onReady();
        if (typeof isAdmin !== 'undefined' && isAdmin === true) {
            getAdminFileUploadDataTable(TabbedUpload.getCurrentTabObj());
        }
    }
}

function update_modal_content_zbt_iss_guest(data) {
    $('#type-name').text(data.TYPE || '-')
    $('#upload-owner').text(data.email || '-');
    $('#upload-datetime').text(data.CREATED_TIMESTAMP || '-');
    $('#is-active-status').text(data.IS_ACTIVE === 1 ? 'True' : 'False');
    $('#upload-final-table-name').text(data.TABLE_NAME || '-');
    $('#upload-final-table-create').text(data.FINAL_CREATE_TIME || '-');
    $('#upload-final-table-update').text(data.FINAL_UPDATE_TIME || '-');

    $('#upload-dirty-table-name').text(data.DIRTY_TABLE_NAME || '-');
    $('#upload-dirty-table-create').text(data.DIRTY_CREATE_TIME || '-');
    $('#upload-dirty-table-update').text(data.DIRTY_UPDATE_TIME || '-');

    $('#current-time').text(data.CURRENT_TIME || '-');

    $('#edit-guest-table-btn').off().on('click', function() {
        window.open(`/dashboard/import_upload/editor_view/${data.ID}`);
    });

    $(TabbedUpload.getCurrentTabObj().getCapSponsorSubmitBtnId()).off().on('click', TabbedUpload.getCurrentTabObj().saveCapUserSubmit(data));

}

function update_modal_content_zbt_iss_admin(data) {
    $('#type-name').text(data.TYPE || '-')
    $('#upload-owner').text(data.email || '-');
    $('#upload-datetime').text(data.CREATED_TIMESTAMP || '-');
    $('#is-active-status').text(data.IS_ACTIVE === 1 ? 'True' : 'False');
    $('#upload-table-name').text(data.TABLE_NAME || '-');
    $('#upload-table-create').text(data.CREATE_TIME || '-');
    $('#upload-table-update').text(data.UPDATE_TIME || '-');
    $('#current-time').text(data.CURRENT_TIME || '-');
    
    $('#view-admin-table-btn').off().on('click', function () {
        window.open(`/dashboard/import_upload/editor_view/${data.ID}/1?mode=view`);
    });
}

function handleParseUpload(id, table) {
    let url='/dashboard/import_upload/database_save_zbt_issue_data_upload/parse_file';

    let data = {}

    data['row_id'] = id;
    data['name'] = TabbedUpload.getCurrentTabObj().getName();
    data['rhombus_token'] = rhombuscookie();

    let status = 'success';
    $.ajax(url, {
        method: 'POST',
        data: data,
        success: function (data) { 
            table.ajax.reload()
            status = "success";
        },
        error: function (jqXHR) {
            status = "error";
        }
    });
    return status
}

function handleSaveApproveClick(event) {
    const table = $('#admin-list5').DataTable();
    const rowData = table.row($(event.currentTarget).closest('tr')).data();

    let position  = '';
    if (rowData['DIRTY_TABLE_NAME'].includes('ISS')) {
        position = 'iss';
    }
    else if (rowData['DIRTY_TABLE_NAME'].includes('ZBT')) {
        position = 'zbt';
    }

    const currentTab = TabbedUpload.getCurrentTabObj();
    if (currentTab && typeof currentTab.saveAdminApprove === 'function') {
        let done = currentTab.saveAdminApprove(rowData);
        done.done(function(status) {

            console.log(position)
            console.log(status)
            if (position != '' && status?.status == 'Submission saved successfully.') {
                status = currentTab.upsertTable(position);
                if (status == 'success') {
                    $('#admin-list5').DataTable().ajax.reload();
                    displayToastNotification("success", "Submit table successfully")
                }
                else {
                    displayToastNotification("error", "Error submit table")
                }
            }
        });
    } else {
        console.error('saveAdminApprove not found.');
    }
}


function handleSaveSubmitClick(event) {
    const table = $('#processed-list4').DataTable();
    const rowData = table.row($(event.currentTarget).closest('tr')).data();

    const currentTab = TabbedUpload.getCurrentTabObj();
    if (currentTab && typeof currentTab.saveCapUserSubmit === 'function') {
        currentTab.saveCapUserSubmit(rowData);
        displayToastNotification("success", "Submit table successfully")
    } else {
        displayToastNotification("error", "saveCapUserSubmit not found.")
    }
}

function handleSaveParseClick(event) {
    const table = $('#processed-list4').DataTable();
    const rowData = table.row($(event.currentTarget).closest('tr')).data();

    const currentTab = TabbedUpload.getCurrentTabObj();
    let status = currentTab.parseTable(rowData['ID']);
    if (status == 'success') {
        table.ajax.reload();
        displayToastNotification("success", "Submit table successfully")
    }
    else {
        displayToastNotification("error", "Error submit table")
    }
    
}
