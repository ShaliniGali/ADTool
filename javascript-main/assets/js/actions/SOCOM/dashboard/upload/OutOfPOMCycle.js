class OutOfPOMCycle extends TabbedUpload { 
    #cid;

    constructor() {
        let name = 'OutOfPOMCycle';
        let validFileTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        let uploadSizePossible = 20971520;
        let sheetNamesPossible = [];
        let fileInputLabel = '#file-input-label3'
        let fileInputId = '#dashboard-file-uploader3'
        let uploadFormId = '#upload-form3'
        let saveFileId = '#save-file3'
        let uploadTableId = '#upload-list3'
        let uploadFileUrl = '/dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/upload_file'
        let uploadListUrl = '/dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/list_view'
        let deleteUrl = '/dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/delete_file'
        let cancelUrl = '/dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/cancel_file'
        let processUrl = '/dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/process_file'
        let processedTableId  = '#processed-list3'
        let processedUrl = '/dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/results_list_view'
        let versionInputId =  '#version-input3'
        let titleInputId =  '#title-input3'
        let descriptionInputId =  '#description3'
        let tabId = '#tab-myuser-3'
        let uploadTab = '#upload-tab3'
        let fileListTab = '#file-list-tab3'

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
    
    #yearInputId = '#year-input3'
    #tableListingInputId = '#table-listing-input3'

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

    getProcessedTableColumns() { return get_processed_table_columns_out_of_pom(); }
}

function update_modal_content_out_of_pom(data) {
    $('#type-name').text(data.TYPE || '-')
    $('#upload-owner').text(data.email || '-');
    $('#upload-datetime').text(data.CREATED_TIMESTAMP || '-');
    $('#is-active-status').text(data.IS_ACTIVE === 1 ? 'True' : 'False');
    $('#upload-table-name').text(data.TABLE_NAME || '-');
    $('#upload-table-create').text(data.CREATE_TIME || '-');
    $('#upload-table-update').text(data.UPDATE_TIME || '-');
    $('#current-time').text(data.CURRENT_TIME || '-');


    $('#view-table-btn').off().on('click', function () {
        window.open(`/dashboard/import_upload/editor_view/${data.ID}?mode=view`);
    });
}