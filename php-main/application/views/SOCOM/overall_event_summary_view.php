<?php

  $this->load->view('templates/essential_javascripts');

?>

<?php
    $js_files = array();
    $CI = &get_instance();

    $js_files['toggle_theme'] = ["actions/SOCOM/toggle_theme.js", 'custom'];
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
    $js_files['select2'] = ["select2.full.js", 'global'];
    $js_files['highstock'] = ["highstock.js", 'global'];
    $js_files['heatmap'] = ["heatmap.js", 'global'];
    $js_files['exporting'] = ["exporting.js", 'global'];
    $js_files['no_data'] = ["no-data-to-display.js", 'global'];
    $js_files['highcharts_more'] = ["highcharts-more.js", 'global'];
    $js_files['accessibility'] = ["accessibility.js", 'global'];
    $js_files['ion_range'] = ["ion.rangeSlider.js", 'global'];
    $js_files['datatables'] = ["datatables.min.js", 'global'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $js_files['overall_event_summary_view'] = ['actions/SOCOM/overall_event_summary.js', 'custom'];
    $js_files['xlsx'] = ['xlsx.full.min.js', 'global'];
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);

    $cap_sponsor_group = auth_zbt_summary_role_cap();
    $is_guest = auth_zbt_summary_role_guest();
?>

<style>
    #program-description{
        text-align: center;
    }

    /* #ao-ad-container .column {
        width: 160px;
    } */

    .ao-ad-header {
        font-size: 14px;
        margin-bottom: 0.4rem;
        white-space: nowrap;
    }

    #chart-1 {
        min-width: 640px;
        height: unset;
        margin:10px;
    }

    #overall-event-summary-table thead > tr > th.sorting {
        padding-left: 10px;
    }

    #overall-event-summary-table thead .sorting {
        background-image: none;
    }

    #overall-event-summary-table_wrapper {
        width: 100%;
    }

    table.dataTable > tbody {
        font-size: 1.2em !important;
    }

    .dataTables_filter {
        margin: 8px 0;
    }
    .highlight-red{
        background-color: #f65959 !important;
        color: #fff !important;
    }
    #event-fy-table td{
        color: black;
    }
    #balance-warning{
        width: fit-content;
        margin: auto;
        color: #161616;
        display: none;
    }

    .filter-container .select2-selection__rendered  {
        margin: unset !important
    }
    
    .ember-cell{
        background: #FFFF00 !important;
        color: black !important;
        font-weight: bold;
    }

    .toggle-position{
        margin-right: 5rem !important;
    }

</style>
<?php $this->load->view('SOCOM/toast_notifications');?>

<div class="content-wrapper">
    <header>
        <?php $this->load->view('SOCOM/header_buttons_view',
            array('current_page'=>'Overall Event Summary', 'page' => $page, 'page_summary_path' => $page)
        );?>
        <?php if ($page == 'zbt_summary'): ?>
            <div class="mt-3">
                <p>ZBT Summary is Connected with Position Year FY<?php echo $subapp_pom_year ?></p>
            </div>
        <?php endif; ?>
        <?php if ($page == 'issue'): ?>
            <div class="mt-3">
                <p>Issue Summary is Connected with Position Year FY<?php echo $subapp_pom_year ?></p>
            </div>
        <?php endif; ?>
    </header>
    <div class="grey-background">
        <div class="d-flex flex-column">
            
            <div class= "d-flex flex-column w-100 justify-content-center" id="chart-1-container">

                <div class="w-66 filter-container d-flex flex-row justify-content-end">
                    <div class="d-flex flex-row">

                        <div id="cap-sponsor-dropdown" class="d-flex flex-column mr-5 mt-3" >
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="mb-1 bx--label medium-label"><?= ($page == 'zbt_summary') ? 'ZBT' : 'Issue' ?> Capability Sponsor</div>
                                <div>
                                    <button id="cap-sponsor-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                    data-select-all="false"
                                    type="button" onclick="dropdown_selection('#cap-sponsor')"> 
                                        Deselect All 
                                    </button>
                                </div>
                            </div>
                            <select
                                    id="cap-sponsor"
                                    type="cap-sponsor"
                                    combination-id=""
                                    class="selection-dropdown"
                                    multiple="multiple"
                                    onchange="dropdown_onchange('cap-sponsor')"
                                    >
                                <?php// if ($is_guest==null) : ?>
                                            <option option="ALL" selected>ALL</option>
                                <?php //endif ; ?>
                                
                                <?php foreach($capability_sponsor as $value): ?>
                                    <?php //if( ($value['CAPABILITY_SPONSOR_CODE'] ==  $cap_sponsor_group) && ($is_guest!=null)) :?>
                                        <option value="<?= $value['CAPABILITY_SPONSOR_CODE']?>"><?= $value['CAPABILITY_SPONSOR_CODE']?></option>
                                    <?php // else: ?>
                                        <?php //if ($is_guest==null) : ?>
                                            <!-- <option value="<?= $value['CAPABILITY_SPONSOR_CODE']?>"><?= $value['CAPABILITY_SPONSOR_CODE']?></option> -->
                                        <?php //endif ; ?>
                                    <?php //endif; ?>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div id="aac-dropdown" class="d-flex flex-column mr-5 mt-3" >
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="mb-1 bx--label medium-label">Assessment Area Code</div>
                                <div>
                                    <button id="aac-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                    data-select-all="false"
                                    type="button" onclick="dropdown_selection('#aac')">
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            <select
                                    id="aac"
                                    type="aac"
                                    combination-id=""
                                    class="selection-dropdown"
                                    multiple="multiple"
                                    onchange="dropdown_onchange('aac')"
                                    >
                                <option option="ALL" selected>ALL</option>
                                <?php foreach($aac_list as $value):?>
                                    <option value="<?= $value['ASSESSMENT_AREA_CODE']?>"><?= $value['ASSESSMENT_AREA_CODE']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="ad-consensus-dropdown" class="d-flex flex-column mr-5 mt-3" hidden>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="mb-1 bx--label medium-label">AD Consensus</div>
                                <div>
                                    <button id="ad-consensus-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                    data-select-all="false"
                                    type="button" onclick="dropdown_selection('#ad-consensus')"> 
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            <select
                                    id="ad-consensus"
                                    type="ad-consensus"
                                    combination-id=""
                                    class="selection-dropdown"
                                    onchange="dropdown_onchange('ad-consensus')"
                                    multiple="multiple"
                                    >
                                <option option="ALL" selected>ALL</option>
                                <?php foreach($ad_consensus_filter_choices as $choice): ?>
                                    <option value="<?= $choice ?>"><?= $choice?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="review-status-dropdown" class="d-flex flex-column mt-3" hidden>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="mb-1 bx--label medium-label">Review Status</div>
                                <div>
                                    <button id="review-status-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                                    data-select-all="false"
                                    type="button" onclick="dropdown_selection('#review-status')"> 
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            <select
                                    id="review-status"
                                    type="review-status"
                                    combination-id=""
                                    class="selection-dropdown"
                                    onchange="dropdown_onchange('review-status')"
                                    multiple="multiple"
                                    >
                                <option option="ALL" selected>ALL</option>
                                <?php foreach($review_status_choices as $choice): ?>
                                    <option value="<?= $choice ?>"><?= $choice?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

       
            <div class="d-flex flex-column w-100 justify-content-center" id= "chart-2-container" >
                <div class="chart-container h-100 w-100" id="chart-2" style="">
                    <div class="d-flex flex-column w-100 align-items-center p-3">
                        <h2 id="funding-lines-header"><span class="event-name-header"><?= $selected_event ?></span>Overall Event Summary</h2>
                        <div>
                        <table class="display dataTable cell-border table-style bx--data-table pt-3" id="overall-event-sum-table" class="" style="text-align:center;"></table>
                        </div>
                        <div class="d-flex w-100">
                            <div class="ml-auto">
                                <div class="bx--form-item toggle-position">
                                    <input class="bx--toggle-input" id="proposed-granted-toggle-oest" type="checkbox" checked
                                        onclick="toggleGrantedOESTable()">       
                                    <label class="bx--toggle-input__label" for="proposed-granted-toggle-oest">    
                                        <span class="bx--toggle__switch">
                                            <span class="bx--toggle__text--off" aria-hidden="true">Proposed</span>
                                            <span class="bx--toggle__text--on" aria-hidden="true">Granted</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" id="overall-event-summary-table" style="text-align:center;"></table>
                        <div class="w-100 d-flex flex-row justify-content-end mt-5">
                            <button class="bx--btn bx--btn--primary" onclick="export_results()">Export</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    const eventSummaryType =  <?= json_encode($event_type); ?>;
    const page = <?= json_encode($page); ?>;
    const selectedCapSponsor = <?= json_encode($selected_cap_sponsor); ?>

    const selectedADConsensus  = <?= json_encode($selected_ad_consensus); ?>;
    const selectedReviewStatus = <?= json_encode($selected_review_status); ?>;

    const selectedAAC          = <?= json_encode($selected_aac); ?>;

    const test= 'test';

    $(`#${page}-summary-breadcrumb`).attr("hidden",false);


    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '16vw'
    })
    .on('change.select2', function() {
            var dropdown = $(this).siblings('span.select2-container');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
    })

    if (selectedCapSponsor.length)   $('#cap-sponsor').val(selectedCapSponsor).trigger('change.select2');
   
    if (selectedADConsensus.length)  $('#ad-consensus').val(selectedADConsensus).trigger('change');
    if (selectedReviewStatus.length) $('#review-status').val(selectedReviewStatus).trigger('change');

    if (selectedAAC.length)          $('#aac').val(selectedAAC).trigger('change');



  $(document).ready(function(){


    //  On load: if there are ?…[] params, restore them into the selects
    const sp = new URLSearchParams(window.location.search);
    ['cap-sponsor','ad-consensus','review-status','aac'].forEach(name => {
        const raw = sp.get(name);
        if (raw) {
        const vals = raw.split(',');
        $(`#${name}`).val(vals).trigger('change.select2');
        }
    });
    const isRestricted = <?= json_encode(auth_zbt_summary_role_restricted()); ?>;
    const isUser = <?= json_encode(auth_zbt_summary_role_user()); ?>;
    const isAdmin = <?= json_encode(auth_zbt_summary_role_admin()); ?>;
    const isGuest = <?= json_encode(auth_zbt_summary_role_guest()); ?>;
 
     if(isUser){
         document.getElementById("ad-consensus-dropdown").attributes.removeNamedItem("hidden");
         document.getElementById("review-status-dropdown").attributes.removeNamedItem("hidden");
     }else if(isAdmin){
         document.getElementById("ad-consensus-dropdown").attributes.removeNamedItem("hidden");
         document.getElementById("review-status-dropdown").attributes.removeNamedItem("hidden");
     }
  });
</script>
