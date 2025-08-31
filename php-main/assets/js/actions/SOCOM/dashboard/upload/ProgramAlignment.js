class ProgramAlignment extends TabbedUpload {
    cid

    constructor( ) {
        let name = 'ProgramAlignment';
        let validFileTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        let uploadSizePossible = 20971520;
        let sheetNamesPossible = ['data', 'metadata'];
        let fileInputLabel = '#file-input-label'
        let fileInputId = '#dashboard-file-uploader'
        let uploadFormId = '#upload-form'
        let saveFileId = '#save-file'
        let uploadTableId = '#upload-list'
        let uploadFileUrl = '/dashboard/import_upload/program_alignment/upload_file'
        let uploadListUrl = '/dashboard/import_upload/program_alignment/list_view'
        let deleteUrl = '/dashboard/import_upload/program_alignment/delete_file'
        let cancelUrl = '/dashboard/import_upload/program_alignment/cancel_file'
        let processUrl = '/dashboard/import_upload/program_alignment/process_file'
        let processedTableId  = '#processed-list'
        let processedUrl = '/dashboard/import_upload/program_alignment/results_list_view'
        let versionInputId =  '#version-input'
        let titleInputId =  '#title-input'
        let descriptionInputId =  '#description'
        let tabId = '#tab-myuser-1'
        let uploadTab = '#upload-tab1'
        let fileListTab = '#file-list-tab1'

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
    
    clearInterval() {
        if (this.cid !== undefined) {
            clearInterval(this.cid);
        }
    }
    
    getUploadTableColumns() {
        return get_upload_table_columns();
    }

    getProcessedTableColumns() {
        return get_processed_table_columns_program_alignment();
    }
}