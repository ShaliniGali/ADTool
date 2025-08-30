<div data-modal id="coa-event-details" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="coa-event-details-label" aria-describedby="coa-event-details-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 75%;">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="coa-event-details-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="coa-event-details-heading">Event Details</p>
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

    <div class="bx--modal-content overflow-auto p-3 m-3 white-background" id="coa-event-details-container">
        <h3 id="event-details-event-name" class="pt-2" style="text-align: center;"></h3>
        <div class="d-flex flex-column">
            <div class="d-flex flex-row justify-content-end">
                <div id="issue-analysis-event-filter-dropdown" class="d-flex flex-column mb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="mb-1 bx--label medium-label">Delta Line</div>
                          <div>
                              <button id="issue-analysis-event-filter-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                              data-select-all="false"
                              type="button" onclick="dropdown_selection('#issue-analysis-event-filter')">
                                  Deselect All
                              </button>
                          </div>
                    </div>
                    <select
                            id="issue-analysis-event-filter"
                            type="delta-line"
                            combination-id=""
                            class="selection-dropdown"
                            onchange="updateIssueAnalysisEventFilter('#coa-event-details-table')"
                            multiple="multiple"
                            >
                        <option option="ALL" selected>ALL</option>
                    </select>
                </div>
            </div>
        </div>
        <table id="coa-event-details-table" class="display dataTable cell-border table-style w-100 bx--data-table"></table>
    </div>

    <div class="bx--modal-footer">
        <button id="close-coa-event-details-results" class="bx--btn bx--btn--secondary"
            type="button" data-modal-close>Close</button>
        <button id="download-coa-event-details-results" tabindex="0" class="bx--btn bx--btn--primary load_button" type="button" disabled>
            Download Results <div class="margin-right:8em;"> <i class="fas fa-file-excel fa-2x"></i></div>
        </button>
    </div>
  </div>
  <span tabindex="0"></span>
</div>