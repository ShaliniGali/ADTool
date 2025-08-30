<div data-modal id="coa-proposed-changes" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="coa-proposed-changes-label" aria-describedby="coa-proposed-changes-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 75%;">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="coa-proposed-changes-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="coa-proposed-changes-heading">Proposed Changes</p>
      </div>
      <button class="bx--modal-close" type="button"  aria-label="close modal" data-modal-close>
        <svg focusable="false" preserveAspectRatio="xMidYMid meet"
        style="will-change: transform;" xmlns="http://www.w3.org/2000/svg"
          class="bx--modal-close__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" aria-modal="true">
          <path d="M12 4.7L11.3 4 8 7.3 4.7 4 4 4.7 7.3 8 4 11.3 4.7 12 8 8.7 11.3 12 12 11.3 8.7 8z">

          </path>
        </svg>
      </button>
    </div>

    <div class="bx--modal-content overflow-auto p-3 m-3 white-background" id="coa-proposed-changes-container">
        <h3 id="proposed-changes-event-name" class="pt-2" style="text-align: center;"></h3>
        <h4 class="pt-4">Included:</h4>
        <table id="coa-proposed-changes-included-table" class="display dataTable cell-border table-style w-100 bx--data-table"></table>
        <h4 class="pt-4">Excluded:</h4>
        <table id="coa-proposed-changes-excluded-table" class="display dataTable cell-border table-style w-100 bx--data-table"></table>
    </div>

    <div class="bx--modal-footer">
        <button id="close-coa-proposed-changes-results" class="bx--btn bx--btn--secondary"
            type="button" data-modal-close>Close</button>
        <button id="download-coa-proposed-changes-results" tabindex="0" class="bx--btn bx--btn--primary load_button" type="button" disabled>
            Download Results <div class="margin-right:8em;"> <i class="fas fa-file-excel fa-2x"></i></div>
        </button>
    </div>
  </div>
  <span tabindex="0"></span>
</div>