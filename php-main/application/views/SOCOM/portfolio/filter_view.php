<style>
    .filter-selections {
        /* 
        width: 250px;
        flex: 0 0 250px;
         margin-right: 1rem; */
    }
    .filter-selections-height{
        max-height: 100vh;
        height: fit-content;
        overflow: auto;
    }
    .filter-selections header {
        border-bottom: solid 1px var(--cds-border-subtle);
    }
    /* .pcar-remove-span {
        cursor: pointer;
        margin-left: var(--cds-spacing-01, 0.125rem);
    }
    .pcar-only-container {
        color: var(--cds-link-primary, #0f62fe);
        cursor: pointer;
        height: var(--cds-container-01, 1.5rem);
        display: inline-block;
        margin-right: var(--cds-spacing-02, 0.25rem);
    }
    .pcar-count-container {
        border-radius: var(--cds-spacing-04, 0.75rem);
        padding: var(--cds-spacing-01, 0.125rem) var(--cds-spacing-03, 0.5rem);
        background: var(--cds-disabled-02, #c6c6c6);
        display: inline-block;
        margin-top: 0.0625rem;
    } */
    .list-headers {
        padding-top: 1rem;
    }
    .nested-list {
        display: none;
    }
    .expand-button {
        position: relative;
        z-index: 1;
        cursor: pointer;
    }
    .onlyBtn{
        opacity: 0;
        transition: opacity 0.2s;
        width: fit-content;
        cursor: pointer;
        padding: calc(0.375rem - 3px) 12px !important;
    }
    .onlyList:hover .onlyBtn{
        opacity: 1;
    }
    .list-checkbox-div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 2rem;
    }
    .bx--checkbox-label {
        font-weight: bold;
        padding-top: var(--cds-spacing-01, 2px);
        padding-left: var(--cds-spacing-06, 24px);
        font-size: var(--cds-body-02-font-size);
    }
    .bx--form-item.lvl-1 {
        padding: var(--cds-spacing-02, 4px) var(--cds-spacing-03, 8px);
    }
    .bx--form-item.lvl-2 {
        padding: var(--cds-spacing-02, 4px)
        var(--cds-spacing-03, 8px) var(--cds-spacing-02, 4px)
        var(--cds-spacing-07, 32px);
    }
</style>
<div id="<?=$tab_type;?>-selection-filter"
    class="filter-selections selection-filter-container"
    style="overflow-y: auto" >
            <header class="d-flex justify-content-between">
                <h4>Filters</h4>
                <button class="bx--btn bx--btn--ghost bx--btn--sm" type="button" onclick="toggleAllCheckboxes(this);">
                    Select/Deselect All
                </button>
            </header>
            <fieldset class="bx-feildset pt-3">
                <legend></legend>
                <ul id="<?= $tab_type; ?>-checkbox-wrapper" class="manning-force-box">
                    <!-- Dynamic checkbox will be added here -->
                </ul>
            </fieldset>
            <button class="bx--btn bx--btn--primary"
            style="width:10vw" id="<?=$tab_type;?>-<?=$tile_name;?>-apply-filter"
            type="button" onclick="applyFilters('<?=$tab_type;?>')">
                Apply Filters
            </button>
        </div>

<script>
    $( document ).ready(function() {
        let tab_type = '<?= $tab_type; ?>';
        let dropdownData = <?= json_encode($budget_trend_overview['dropdown'], true) ?>;
        const dropdownList = document.getElementById(tab_type+"-checkbox-wrapper");
        subCategoryShowing = <?= json_encode($budget_trend_overview['sub_category_showing'], true) ?>;
        categoryMap = <?= json_encode($budget_trend_overview['category_map'], true) ?>;

        renderFilters(dropdownData, dropdownList, 0, tab_type, tab_type);

        // default expand all options
        $('.expand-button').trigger('click');

        onReady();
    });
</script>
