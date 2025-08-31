class InPOMCycle extends TabbedUpload {
    cid;
    
    constructor() {
        let name = 'InPOMCycle';
        let validFileTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        let uploadSizePossible = 20971520;
        let sheetNamesPossible = [];
        let fileInputLabel = '#file-input-label2'
        let fileInputId = '#dashboard-file-uploader2'
        let uploadFormId = '#upload-form2'
        let saveFileId = '#save-file2'
        let uploadTableId = '#upload-list2'
        let uploadFileUrl = '/dashboard/import_upload/database_save_in-pom_cycle_data_upload/upload_file'
        let uploadListUrl = '/dashboard/import_upload/database_save_in-pom_cycle_data_upload/list_view'
        let deleteUrl = '/dashboard/import_upload/database_save_in-pom_cycle_data_upload/delete_file'
        let cancelUrl = '/dashboard/import_upload/database_save_in-pom_cycle_data_upload/cancel_file'
        let processUrl = '/dashboard/import_upload/database_save_in-pom_cycle_data_upload/process_file'
        let processedTableId  = '#processed-list2'
        let processedUrl = '/dashboard/import_upload/database_save_in-pom_cycle_data_upload/results_list_view'
        let versionInputId =  '#version-input2'
        let titleInputId =  '#title-input2'
        let descriptionInputId =  '#description2'
        let tabId = '#tab-myuser-2'
        let uploadTab = '#upload-tab2'
        let fileListTab = '#file-list-tab2'

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
    #yearInputId = '#year-input2'
    #tableListingInputId = '#table-listing-input2'

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

    getProcessedTableColumns() { return get_processed_table_columns_in_pom(); }
    
}

function update_modal_content(data) {
    $('#type-name').text(data.TYPE || '-')
    $('#upload-owner').text(data.email || '-');
    $('#upload-datetime').text(data.CREATED_TIMESTAMP || '-');
    $('#is-active-status').text(data.IS_ACTIVE === 1 ? 'True' : 'False');
    $('#upload-table-name').text(data.TABLE_NAME || '-');
    $('#upload-table-create').text(data.CREATE_TIME || '-');
    $('#upload-table-update').text(data.UPDATE_TIME || '-');
    $('#current-time').text(data.CURRENT_TIME || '-');

    const btnContainer = $('#status-action-buttons');
    btnContainer.empty();
    btnContainer.append(`
        <button class="bx--btn bx--btn--primary" id="view-table-btn">
                    View
        </button>
    `);
    $('#view-table-btn').off().on('click', function () {
        window.open(`/dashboard/import_upload/editor_view/${data.ID}?mode=view`);
    });
}