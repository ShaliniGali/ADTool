<style>
.icon-block{
    background:#4e4d4d;
}

.individual-icon:hover{
    background:#5f5e5e;

}

.page-button {
    z-index: 999;
    padding-right:10px;
    padding-left:10px;
    right: 0rem;
    top: 3rem;
}
.theme-button{
    padding: 3px 9px !important;
    text-align: center;
}
.current-breadcrumb-page {
    color: var(--cds-text-02);
}

</style>
<div id="overlay-loader"></div>
<div class="d-flex flex-row">
    <nav class="bx--breadcrumb bx--breadcrumb--no-trailing-slash" aria-label="breadcrumb">
        <div class="bx--breadcrumb-item">
            <a href="/" class="bx--link">Home</a>
        </div>
        <div class="bx--breadcrumb-item">
            <a href="/socom/index" class="bx--link">SOCOM</a>
        </div>
        <div class="bx--breadcrumb-item" id="<?= $page ?>-summary-breadcrumb" hidden>
            <a href="/socom/<?= $page ?>" class="bx--link"><?= isset($breadcrumb_text) ? $breadcrumb_text : '' ;?></a>
        </div>
        <div class="bx--breadcrumb-item" id="event-summary-breadcrumb" hidden>
        <a href="#"
            id="breadcrumb-event-summary"
            data-base-url="/socom/<?= $page ?>/event_summary_overall"
            class="bx--link">
            Overall Event Summary
        </a>
        </div>

        

        <div class="bx--breadcrumb-item" id="program-breakdown-breadcrumb" hidden>
            <a href="javascript:void(0);" onclick="view_onchange(1, 'program')" class="bx--link">Program Breakdown</a>
        </div>
    </nav>
</div>
<div class="d-flex flex-row">
    <h3 id="current-page-header" class="mr-3"><?= $current_page; ?></h3>
    <div class="d-flex flex-row">
        <?php if ($current_page == 'ZBT Summary' || $current_page == 'Issue Summary'): ?>
            <button onclick="window.location.href='/socom/<?= $page_summary_path; ?>/program_breakdown'"
                class="bx--btn bx--btn--primary header-button bx--btn--field"
                type="button">
                Program Breakdown
            </button>
            <?php if ($current_page == 'ZBT Summary'): ?>
                <button id="btn-event-summary"
                        data-url="/socom/<?= trim($page_summary_path,'/'); ?>/event_summary_overall"
                        class="bx--btn bx--btn--primary header-button bx--btn--field"
                        type="button">
                Event Summary
                </button>

            <?php elseif ($current_page == 'Issue Summary'): ?>
                <button id="btn-event-summary"
                        data-url="/socom/<?= trim($page_summary_path,'/'); ?>/event_summary_overall"
                        class="bx--btn bx--btn--primary header-button bx--btn--field"
                        type="button">
                    Event Summary
                </button> 
            <?php endif; ?>
            <button
                class="bx--btn bx--btn--primary header-button bx--btn--field"
                type="button" disabled>
                Export Data
            </button>
            <button
                class="bx--btn bx--btn--primary header-button bx--btn--field"
                disabled
                type="button">
                Export Visuals
            </button>
        <?php elseif (strpos($current_page, ' Program Breakdown')): ?>
            <button id="historical-pom-data-tag" onclick="view_onchange(1, 'details', '')" hidden
                class="bx--btn bx--btn--primary header-button bx--btn--field"
                type="button">
                Historical POM Data
            </button>
            <button id="eoc-summary-tag" onclick="view_onchange(1, 'summary', '')" hidden
                class="bx--btn bx--btn--primary header-button bx--btn--field"
                type="button">
                EOC Summary
            </button>
            <button id="eoc-historical-pom-tag" onclick="view_onchange(1, 'eoc_historical_pom', '<?= (is_array($program) || empty($program)) ? '' : htmlspecialchars($program); ?>')" hidden
                class="bx--btn bx--btn--primary header-button bx--btn--field"
                type="button" disabled>
                EOC Historical POM Data
            </button>
        <?php endif; ?>
    </div>
    <!-- <button id="toggle-theme-button" class="bx--btn bx--btn--tertiary bx--btn--icon-only
    bx--btn--sm theme-button ml-auto
    bx--tooltip__trigger bx--tooltip--top custom-tooltip-top"
    aria-label="Toggle Theme" data-target=".demo--panel--opt-2"
     onclick="toggle_theme();">
        <span class="fa fa-moon bx--btn__icon"></span>
    </button> -->
</div>
<?php if ($current_page == 'ZBT Summary'): ?>
    <div class="mt-3">
    <p>ZBT Summary is Connected with Position Year FY<?php echo $subapp_pom_year_zbt ?></p>
    </div>
<?php endif; ?>
<?php if ($current_page == 'Issue Summary'): ?>
    <div class="mt-3">
    <p>Issue Summary is Connected with Position Year FY<?php echo $subapp_pom_year_issue ?></p>
    </div>
<?php endif; ?>
<script>
    // Wait for jQuery and Carbon to be loaded
    $(document).ready(function() {
        // Check if required libraries are loaded
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded - breadcrumb functionality may not work');
            return;
        }
        
        // Check if Carbon Design System is loaded (check for common Carbon objects)
        if (typeof window.Carbon === 'undefined' && typeof window.carbon === 'undefined' && typeof window.bx === 'undefined') {
            console.warn('Carbon Design System may not be fully loaded - some styling may not work');
        }
        
        var currentProgram = '<?= (is_array($program) || empty($program)) ? '' : htmlspecialchars($program); ?>';

        //  Wire up both event summary header buttons by reading their data-url + current search
        $('#btn-event-summary').on('click', function(e){
            e.preventDefault();
            const base = $(this).data('url');
            // window.location.search already has filters
            window.location.href = base + window.location.search;
        });

        $('#breadcrumb-event-summary').on('click', function(e){
            e.preventDefault();
            const base = $(this).data('base-url');
            const qs = sessionStorage.getItem('oesFilters') || '';
            window.location.href = base + qs;
        });

        if($('html').hasClass('dark')) {
            $('.theme-button').addClass('bx--btn--primary').removeClass('bx--btn--tertiary');
        } else {
            $('.theme-button').addClass('bx--btn--tertiary').removeClass('bx--btn--primary');
        }
    });
</script>

