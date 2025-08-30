class TabbedUpload {
    cid
    static #currentTabObj
    static #tabs

    constructor(
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
        fileListTab,
    ) {
        this.#name = name
        this.#validFileTypes = validFileTypes
        this.#uploadSizePossible = uploadSizePossible
        this.#sheetNamesPossible = sheetNamesPossible
        this.#fileInputLabel = fileInputLabel
        this.#fileInputId = fileInputId
        this.#uploadFormId = uploadFormId
        this.#saveFileId = saveFileId
        this.#uploadTableId = uploadTableId
        this.#uploadFileUrl = uploadFileUrl
        this.#uploadListUrl = uploadListUrl
        this.#deleteUrl = deleteUrl
        this.#cancelUrl = cancelUrl
        this.#processUrl = processUrl
        this.#processedTableId = processedTableId
        this.#processedUrl = processedUrl
        this.#versionInputId = versionInputId
        this.#titleInputId = titleInputId
        this.#descriptionInputId = descriptionInputId
        this.#tabId = tabId
        this.#uploadTab = uploadTab
        this.#fileListTab = fileListTab
    }

    #name
    getName() {
        return this.#name;
    }

    #versionInputId
    getVersionInputId() {
        return this.#versionInputId;
    }

    #validFileTypes
    getValidFileTypes() {
        return this.#validFileTypes;
    }
    #uploadSizePossible
    getUploadSizePossible() {
        return this.#uploadSizePossible;
    }

    #sheetNamesPossible
    getSheetNamesPossible() {
        return this.#sheetNamesPossible;
    }

    #fileInputLabel
    getFileInputLabel() {
        return this.#fileInputLabel
    }
    #fileInputId
    getFileInputId() {
        return this.#fileInputId
    }

    #saveFileId
    getSaveFileId() {
        return this.#saveFileId;
    }

    #uploadFormId;
    getUploadFormId() {
        return this.#uploadFormId;
    }

    #uploadTableId;
    getUploadTableId() {
        return this.#uploadTableId;
    }
    #uploadListUrl
    getUploadListUrl() {
        return this.#uploadListUrl;
    }
    #deleteUrl
    getDeleteUrl() {
        return this.#deleteUrl;
    }
    #cancelUrl
    getCancelUrl() {
        return this.#cancelUrl
    }
    #processUrl
    getProcessUrl() {
        return this.#processUrl;
    }

    #processedTableId
    getProcessedTableId() {
        return this.#processedTableId;
    }
    #processedUrl
    getProcessedUrl() {
        return this.#processedUrl;
    }

    #uploadFileUrl;
    getUploadFileUrl() {
        return this.#uploadFileUrl;
    }

    #titleInputId;
    getTitleInputId() {
        return this.#titleInputId;
    }

    #descriptionInputId
    getDescriptionInputId() {
        return this.#descriptionInputId;
    }

    #tabId
    getTabId() {
        return this.#tabId;
    }

    #uploadTab
    getUploadTab() {
        return this.#uploadTab;
    }

    #fileListTab
    getFileListTab() {
        return this.#fileListTab
    }

    validateForm() {

        return true;
    }

    processFormData(form_data) {
        form_data.append('version', $(this.getVersionInputId()).val());
        form_data.append('title', $(this.getTitleInputId()).val());
        form_data.append('description', $(this.getDescriptionInputId()).val());
        return true
    }

    resetForm() {
        $(this.getVersionInputId()).val('');
        $(this.getTitleInputId()).val('');
        $(this.getDescriptionInputId()).empty();

        TabbedUpload.clearAllIntervals();
    }

    clearInterval() { }

    getUploadTableColumns() { return get_upload_table_columns(); }

    getProcessedTableColumns() { return get_processed_table_columns(); }

    validate() {
        return true;
    }

    onInit() {
        TabbedUpload.setCurrentTabObj(this);
        
        $(this.getUploadTab()).click();

        this.resetForm(this);
        
        this.cid = setInterval(() => { updateDataTables(this) }, 180000);
        
        return this;
    }

    static setCurrentTabObj(TabbedUploadObj) {
        TabbedUpload.#currentTabObj = TabbedUploadObj;
    }

    static getCurrentTabObj() {
        return TabbedUpload.#currentTabObj;
    }

    static setTabObjs(tabs) {
        this.#tabs = tabs;
    }

    static getTabObjs() {
        return this.#tabs;
    }
    
    onReady() {   
        this.onInit();

        getNewFileUploadDataTable(TabbedUpload.getCurrentTabObj());
        getProcessedFileUploadDataTable(TabbedUpload.getCurrentTabObj());

        $(this.getFileInputLabel()).on('click', function() { TabbedUpload.getCurrentTabObj().resetForm(TabbedUpload.getCurrentTabObj()) });
        $(this.getFileInputId()).on('change', function() { check_upload_sheet_name(TabbedUpload.getCurrentTabObj(), this); });

        $(this.getUploadFormId()).on('submit', function(e) { e.preventDefault(); return false; });
        $(this.getSaveFileId()).on('click', function() { save_file(TabbedUpload.getCurrentTabObj()) });

        $(this.getTabId()).off('click', tabOnReady);

        $(this.getTabId()).on('click', function() { TabbedUpload.getTabObjs()[this.id].onInit(); } );
    }

    static onReadyMain() {

        const tabs = {
            'tab-myuser-1': new ProgramAlignment(),
            'tab-myuser-2': new InPOMCycle(),
            'tab-myuser-3': new OutOfPOMCycle(),
            'tab-myuser-4': new ZBTIssue()
        }

        tabs['tab-myuser-1'].onReady();

        TabbedUpload.setTabObjs(tabs)

        //tab listener
        for(let tab in tabs) {
            if (tab == 'tab-myuser-1') {
                continue;
            }

            $(tabs[tab].getTabId()).on('click', tabOnReady);
        }
    }

    static clearAllIntervals(tabs) {
        for (let tab in tabs) {
            if (!(tabs[tab] instanceof TabbedUpload)) {
                continue;
            }

            tabs[tab].clearInterval();
        }
    }
}

function tabOnReady() { TabbedUpload.getTabObjs()[this.id].onReady() } 