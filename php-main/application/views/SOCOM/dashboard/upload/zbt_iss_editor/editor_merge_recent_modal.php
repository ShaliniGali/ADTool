<style>
    .ht-wrapper {
        height: 50px;
    }
    .original-row-table {
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
    }
    #merge-rows-wrapper {
        height: 80vh;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 0 1rem;
    }
    #merge-rows {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    #merge-rows-wrapper > .button-row {
        flex-shrink: 0;
        margin-top: 1rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .bx--modal-content {
        overflow: none !important
    }
</style>

<div id="merge-rows-wrapper">
    <div id="merge-rows" class="d-flex flex-column">
    <!-- Handsontables appended here dynamically -->
    </div>

    <div class="button-row">
        <div class="bx--form-item mr-2">
            <button id="refresh-editor" class="bx--btn bx--btn--secondary" type="button" onclick="refreshData()">Refresh</button>
        </div>
        <div class="bx--form-item">
            <button id="overwrite-changes" class="bx--btn bx--btn--primary" type="button" onclick="saveChanges()">Overwrite</button>
        </div>
    </div>
</div>
<div id="merge-conflict-html" class="d-none m-2 flex-column">
    <div class="d-flex flex-column">
        <div><h5>Original Row</h5></div>
        <div class="original-row-table"></div> <!-- Handsontable -->
    </div>
    <div class="d-flex flex-column mt-1">
        <div><h5>My Edits</h5></div>
        <div class="my-details-table"></div> <!-- Handsontable for column-wise conflicts -->
    </div>
    <div class="d-flex flex-column mt-1">
        <div><h5>Conflicting Edits</h5></div>
        <div class="conflict-details-table"></div> <!-- Handsontable for column-wise conflicts -->
    </div>
</div>