<div id="coa-detailed-comparison-issue-analysis-container" class="white-background coa-detailed-comparison-issue-analysis-container">
    <h2 class="pt-2 pb-2" style="text-align: center;">Detailed Comparison between <?= $title; ?> by <span id="coa-detailed-comparison-issue-analysis-tab-label">Event</span></h2>
    <div class="d-flex flex-row justify-content-between p-2">
        <div id="coa-detailed-comparison-issue-analysis-container" class="w-100">
            <div data-content-switcher class="bx--content-switcher" role="tablist" aria-label="Demo switch content" style="max-width: 16rem;">
                <button class="bx--content-switcher-btn bx--content-switcher--selected"
                    data-target="#coa-detailed-comparison-issue-analysis-chart-container" role="tab"  aria-selected="true"  >
                    <span class=bx--content-switcher__label>Event</span>
                </button>
                <button class="bx--content-switcher-btn"
                    data-target="#coa-detailed-comparison-issue-analysis-table-container" role="tab"  >
                    <span class=bx--content-switcher__label>Program/EOC</span>
                </button>
            </div>
        </div>
    </div>
    <div id="coa-detailed-comparison-issue-analysis-chart-container" class="d-flex flex-row mt-2 <?= count($saved_coa_ids) >= 3 ? '' : 'justify-content-center'; ?>" style="overflow-x: scroll;">
        <div class="p-2 w-100">
            <table id="coa-detailed-comparison-issue-analysis-event-table" class="bx--data-table ml-0"></table>
        </div>
    </div>
    <div id="coa-detailed-comparison-issue-analysis-table-container" class="d-flex flex-row mt-2 <?= count($saved_coa_ids) >= 2 ? '' : 'justify-content-center'; ?>" style="overflow-x: scroll;" hidden>
        <div class="p-2 w-100">
        <div class="d-flex flex-column">
            <div class="d-flex flex-row justify-content-end">
                <div id="issue-analysis-eoc-filter-dropdown" class="d-flex flex-column mb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="mb-1 bx--label medium-label">Delta Line</div>
                          <div>
                              <button id="issue-analysis-eoc-filter-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                              data-select-all="false"
                              type="button" onclick="dropdown_selection('#issue-analysis-eoc-filter')">
                                  Deselect All
                              </button>
                          </div>
                    </div>
                    <select
                            id="issue-analysis-eoc-filter"
                            type="delta-line"
                            combination-id=""
                            class="selection-dropdown"
                            onchange="updateIssueAnalysisEventFilter('#coa-detailed-comparison-issue-analysis-program-eoc-table')"
                            multiple="multiple"
                            >
                        <option option="ALL" selected>ALL</option>
                    </select>
                </div>
            </div>
        </div>
            <table id="coa-detailed-comparison-issue-analysis-program-eoc-table" class="bx--data-table ml-0 w-100">
                <thead>
                    <tr>
                        <?php foreach ($headers['program_eoc']['eoc_information']['comparison'] as $header): ?>
                            <th>
                                <span class="bx--table-header-label"><?= $header['title']; ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    CarbonComponents.ContentSwitcher.init()
    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '12vw'
    })

    $('.coa-detailed-comparison-issue-analysis-container .bx--content-switcher-btn').on('click', function() {
        $('.coa-detailed-comparison-issue-analysis-container .bx--content-switcher-btn').removeClass('bx--content-switcher--selected');
        
        $(this).addClass('bx--content-switcher--selected');
        
        const selectedTab = $('.coa-detailed-comparison-issue-analysis-container .bx--content-switcher-btn.bx--content-switcher--selected');
        
        const selectedTabLabel = selectedTab.find('.bx--content-switcher__label').text();

        $('#coa-detailed-comparison-issue-analysis-tab-label').text(selectedTabLabel);
    });
</script>