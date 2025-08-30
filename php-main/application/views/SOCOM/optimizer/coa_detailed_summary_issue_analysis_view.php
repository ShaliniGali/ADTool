<style>
    .program-eoc-table-header {
        text-align: center !important;
        background-color: white !important;
    }
</style>

<div id="coa-detailed-summary-issue-analysis-container" class="white-background coa-detailed-summary-issue-analysis-container">
    <h2 class="pt-2 pb-2" style="text-align: center;">COA Issue Summary for <strong><?= $title; ?></strong> by <span id="coa-detailed-summary-issue-analysis-tab-label">Event</span></h2>
    <div class="d-flex flex-row justify-content-between p-2">
        <div id="coa-detailed-summary-issue-analysis-container" class="w-100">
            <div data-content-switcher class="bx--content-switcher" role="tablist" aria-label="Demo switch content" style="max-width: 16rem;">
                <button class="bx--content-switcher-btn bx--content-switcher--selected"
                    data-target="#coa-detailed-summary-issue-analysis-event-container" role="tab" aria-selected="true">
                    <span class=bx--content-switcher__label>Event</span>
                </button>
                <button class="bx--content-switcher-btn"
                    data-target="#coa-detailed-summary-issue-analysis-program-eoc-container" role="tab"  >
                    <span class=bx--content-switcher__label>Program/EOC</span>
                </button>
            </div>
        </div>
    </div>
    <div id="coa-detailed-summary-issue-analysis-event-container" class="d-flex flex-row mt-2">
        <div class="p-2 w-100">
            <h4 class="text-center">Fully Funded Issues</h4>
            <table id="coa-detailed-summary-issue-analysis-fully-funded-table" class="bx--data-table ml-0">
                <thead>
                    <tr>
                        <?php foreach ($headers['event']['summary'] as $header): ?>
                            <th>
                                <span class="bx--table-header-label"><?= $header['title']; ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="p-2 w-100">
            <h4 class="text-center">Partially Funded Issues</h4>
            <table id="coa-detailed-summary-issue-analysis-partially-funded-table" class="bx--data-table ml-0">
                <thead>
                    <tr>
                        <?php foreach ($headers['event']['summary'] as $header): ?>
                            <th>
                                <span class="bx--table-header-label"><?= $header['title']; ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="p-2 w-100">
            <h4 class="text-center">Non-Funded Issues</h4>
            <table id="coa-detailed-summary-issue-analysis-non-funded-table" class="bx--data-table ml-0">
                <thead>
                    <tr>
                        <?php foreach ($headers['event']['summary'] as $header): ?>
                            <th>
                                <span class="bx--table-header-label"><?= $header['title']; ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="coa-detailed-summary-issue-analysis-program-eoc-container" class="d-flex flex-column mt-2" style="overflow-x: scroll;" hidden>
        <div class="p-2">
            
            <table id="coa-detailed-summary-issue-analysis-eoc-information-table" class="bx--data-table ml-0 w-100">
                <thead>
                    <tr>
                        <th class="program-eoc-table-header" colspan="7">
                            EOC Information
                        </th>
                        <th class="program-eoc-table-header" colspan="6">
                            Requested Funding from Capability Sponsor
                        </th>
                        <th class="program-eoc-table-header" colspan="6">
                            <?= $title; ?> Proposed Funding
                        </th>
                    </tr>
                    <tr>
                        <?php foreach ($headers['program_eoc']['eoc_information']['summary'] as $header): ?>
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

    $('.coa-detailed-summary-issue-analysis-container .bx--content-switcher-btn').on('click', function() {
        $('.coa-detailed-summary-issue-analysis-container .bx--content-switcher-btn').removeClass('bx--content-switcher--selected');
        
        $(this).addClass('bx--content-switcher--selected');

        const selectedTab = $('.coa-detailed-summary-issue-analysis-container .bx--content-switcher-btn.bx--content-switcher--selected');
        
        const selectedTabLabel = selectedTab.find('.bx--content-switcher__label').text();

        $('#coa-detailed-summary-issue-analysis-tab-label').text(selectedTabLabel);
    });
</script>