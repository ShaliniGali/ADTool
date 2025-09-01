<style>
  .filter-toggle-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: 0;
    margin-right: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    width: 24px;
    height: 24px;
  }

  .filter-toggle-btn .bar {
    display: block;
    width: 100%;
    height: 3px;
    background-color: #333;
    border-radius: 3px;
    transition: all 0.3s ease-in-out;
  }
    .filter-toggle-btn:focus {
    outline: none;
    border: none;
    box-shadow: none;
  }
    .filter-toggle-btn.is-active {
    outline: none;
    border: none;
    box-shadow: none;
  }
  .filter-selections {
    width: 21rem;
    border-radius: 10px;
    background: #ffffff;
    padding: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  .filter-selections header {
    border-bottom: 1px solid var(--cds-border-subtle);
    display: flex;
    align-items: center;
  }
  .filter-selections.collapsed > .bx-fieldset,
  .filter-selections.collapsed > .apply-filter,
  .filter-selections.collapsed > header > h4,
  .filter-selections.collapsed > header > .bx--btn {
    display: none;
  }
  .filter-selections.collapsed {
      width: auto;
      height: fit-content;
      background: none;
      padding: 10px;
      box-shadow: none;
  }
  .filter-selections.collapsed > header {
      border-bottom: none;
  }
  .filter-selections-height {
    max-height: 100vh;
    height: fit-content;
    overflow: auto;
  }
  .filter-selections header h4 {
    font-size: 1rem;
    margin: 0;
    padding: 0.7rem 0;
    flex-grow: 1;
  }
  .list-headers {
    padding-top: 1rem;
  }
  .nested-list {
    display: none;
  }
  .expand-button, 
  .onlyBtn {
    cursor: pointer;
  }
  .expand-button {
    position: relative;
    z-index: 1;
  }
  .list-checkbox-div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border-radius: 5px 5px 0 0;
    transition: background-color 0.2s ease;
    position: relative;
  }
  .bx--checkbox-label {
    padding-top: var(--cds-spacing-01, 2px);
    padding-left: var(--cds-spacing-06, 24px);
  }
  .bx--form-item {
    list-style: none;
  }
  .bx--form-item.lvl-1 {
    margin: 0 12px 0.6rem;
    padding: 0;
    border: 2px solid rgb(246, 245, 245);
    border-radius: 5px;
    background: transparent;
    box-shadow: none;
  }
  .bx--form-item.lvl-1>.list-checkbox-div {
    background: rgb(245, 245, 245);
    width: 100%;
    padding: 1rem;
    border-radius: 5px 5px 0 0;
  }
  .bx--form-item.lvl-1>ul {
    margin: 0;
    padding: 0;
  }
  .bx--form-item.lvl-1 li.lvl-2 {
    padding: 0.5rem 0 0 0;
    margin: 0;
    background: transparent;
    border: none;
    box-shadow: none;
  }
  .bx--form-item.lvl-1 li.lvl-2 .bx--checkbox-label {
    font-weight: normal;
    font-size: 0.75rem;
  }
  .bx--form-item.lvl-1>.list-checkbox-div input[type="checkbox"],
  .bx--form-item.lvl-1>.list-checkbox-div .bx--checkbox-label::before,
  .bx--form-item.lvl-1>.list-checkbox-div .bx--checkbox-label::after {
    display: none;
    content: none;
  }
  .bx--checkbox + .bx--checkbox-label::before {
    border: 2px solid #0f62fe;
  }
  .bx--checkbox:checked + .bx--checkbox-label::before {
    background: #0f62fe;
    border-color: #0f62fe;
  }
  .bx--checkbox:focus + .bx--checkbox-label::before {
    outline: 2px solid #0f62fe;
    outline-offset: 1px;
  }
  .bx--checkbox:checked + .bx--checkbox-label::after {
    border-color: #fff;
  }
  .onlyBtn {
    opacity: 0;
    padding: 4px 12px;
    font-size: 0.75rem;
    border-radius: 12px;
    background: #ffffff;
    color: #1567ff;
    border: 1px solid #1567ff;
    white-space: nowrap;
    transition: opacity 0.2s ease, background 0.2s ease, color 0.2s ease;
  }
  .onlyBtn:hover {
    background: #1567ff;
    color: #1567ff;
  }
  li.lvl-2:hover .onlyBtn,
  .bx--form-item.lvl-1>.list-checkbox-div:hover .onlyBtn {
    opacity: 1;
  }
  .onlyBtn.onlyBtn--selected {
    opacity: 1 !important;
    background-color: #0f62fe !important;
    color: white !important;
  }
  .bx--btn--primary.apply-filter {
    width: auto;
    min-width: 120px;
    padding: 0.5rem 1.25rem;
    border-radius: 12px;
    display: block;
    margin: 1rem auto 0 auto;
    text-align: center;
  }
  fieldset.bx-fieldset {
    border: none;
    padding: 0;
    margin: 0;
  }
  .nested-list {
    display: none;
  }
  .bx--form-item.collapsed>.list-checkbox-div {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
  }
  .bx--form-item.collapsed>ul {
    display: none !important;
  }
  .bx--form-item.lvl-1>.list-checkbox-div .bx--checkbox-label {
    font-size: 0.9rem;
    padding-left: 12px;
    font-weight: 600;
  }
  .hover-highlight {
    background-color: rgba(21, 103, 255, 0.08);
  }
  .bx--form-item.lvl-1>.list-checkbox-div:hover,
  li.lvl-2:hover {
    background-color: rgba(21, 103, 255, 0.08);
  }
  .bx--btn--ghost:hover {
    background-color: rgba(21, 103, 255, 0.08);
  }
</style>

<div id="<?= $tab_type; ?>-selection-filter"
     class="filter-selections selection-filter-container"
     style="overflow-y: auto;">
  <header class="d-flex justify-content-between">
    <button class="filter-toggle-btn" aria-label="Toggle Filter Menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </button>

    <h4 class="ml-3"> Filters</h4>
    <button class="bx--btn bx--btn--ghost bx--btn--sm"
            type="button"
            onclick="selectAllCheckboxes(this);">
      Select All
    </button>
    <button class="bx--btn bx--btn--ghost bx--btn--sm"
            type="button"
            onclick="deselectAllCheckboxes(this);">
      Deselect All
    </button>
  </header>
  <fieldset class="bx-fieldset pt-3">
    <legend></legend>
    <ul id="<?= $tab_type; ?>-checkbox-wrapper"
        class="manning-force-box">
    </ul>
  </fieldset>
  <button class="bx--btn bx--btn--primary apply-filter"
          style="width:7vw"
          id="<?= $tab_type; ?>-<?= $tile_name; ?>-apply-filter"
          type="button"
          onclick="applyFilters('<?= $tab_type; ?>')">
              <i class="fas fa-filter"></i> Apply Filter
  </button>
</div>

<script>
  $(document).ready(function() {
    $('.filter-toggle-btn').on('click', function() {
        $(this).toggleClass('is-active');
        $(this).closest('.filter-selections').toggleClass('collapsed');
        
        setTimeout(function() {
            if (typeof Highcharts !== 'undefined') {
                Highcharts.charts.forEach(function(chart) {
                    if (chart && chart.reflow) {
                        chart.reflow();
                    }
                });
            }
            window.dispatchEvent(new Event('resize'));
        }, 300);
    });

    let tab_type = '<?= $tab_type; ?>';
    let dropdownData = <?= json_encode($budget_trend_overview['dropdown'], true) ?>;
    const dropdownList = document.getElementById(tab_type + "-checkbox-wrapper");
    subCategoryShowing = <?= json_encode($budget_trend_overview['sub_category_showing'], true) ?>;
    categoryMap = <?= json_encode($budget_trend_overview['category_map'], true) ?>;

    renderFilters(dropdownData, dropdownList, 0, tab_type, tab_type);
    $('.expand-button').trigger('click');
    onReady();
  });
</script>
