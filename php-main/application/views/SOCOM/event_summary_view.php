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
    $js_files['datatable'] = ["datatables_v1.min.js", 'global'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $js_files['event_summary_view'] = ['actions/SOCOM/event_summary.js', 'custom'];
    $js_files['gears_percentage'] = ['actions/SOCOM/optimizer/gears_percentage.js','custom'];
    
    if(P1_FLAG){
        $js_files['Editor'] = ["Editor.js", 'global'];
    }
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<style>
    #program-description{
        text-align: center;
    }

    #ao-ad-container .column {
        width: 160px;
    }

    .ao-ad-header {
        font-size: 14px;
        margin-bottom: 0.4rem;
        white-space: nowrap;
    }

    #chart-1 {
        min-width: 640px;
        height: unset;
    }

    #chart-2 {
        overflow-y: scroll;
    }

    #event-funding-lines-table thead > tr > th.sorting {
        padding-left: 10px;
    }

    #event-funding-lines-table thead .sorting {
        background-image: none;
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
    .ember-cell{
        background: #FFFF00 !important;
        color: black !important;
        font-weight: bold;
    }

    #ao-ad-comment{
    bottom: 25px;
    position: relative;
    }

    .bx--modal-header__heading{
        top: 20px;
        position: relative;
        font-size:1.3rem;
    }

    #event-justification-short-text{
        font-size:1.3rem;
        text-align:justify;
    }
     #ao-rec-btn{
        font-size:1.2rem;
     }
     #ao-comment-btn{
        font-size:1.2rem;
     }
     #ad-approval-btn{
        font-size:1.2rem;
     }
     #ad-comment-btn{
        font-size:1.2rem;
     }
     #final-ad-action-btn{
        font-size:1.2rem;
     }
     #event-status-text{
        font-size:1.3rem;
     }
     #ao-ad-dropdown-list{
        font-size:1.2rem;
     }

</style>
<?php $this->load->view('SOCOM/toast_notifications');?>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'ao-ad-comment-view-modal',
    'role' => 'comment_view_save',
    'title_heading' => 'Comment',
    'basic_modal_body_id' => 'ao_ad_comment_view_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary edit_button',
            'aria-label' => 'save',
            'text' => 'Save Comment'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/eoc_summary_comment_field_view', [
        'id' => 'ao_ad_comment_view'
    ], true),
    'close_event' => 'function() { 
                $("#ao-ad-comment-textarea").val("");
                $("#ao-ad-comment-view-modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
    'save_event' => 'saveAOADComment'
]);
?>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'ao-ad-dropdown-view-modal',
    'role' => 'dropdown_view_save',
    'title_heading' => 'Dropdown Heading',
    'basic_modal_body_id' => 'ao_ad_dropdown_view_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
    'html_content' => $this->load->view('SOCOM/eoc_summary_dropdown_field_view', [
        'id' => 'ao_ad_dropdown_view',
    ], true),
    'close_event' => 'function() { 
                $("#ao-ad-dropdown-selection").off("change.dropdown_onchange");
                $("#ao-ad-dropdown-view-modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
]);
?>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'event-justification-view-modal',
    'role' => 'view_event_justification',
    'title_heading' => 'Event Justification',
    'basic_modal_body_id' => 'event-justification-content',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
    'html_content' => $this->load->view('SOCOM/eoc_summary_event_justification_view', [
        'id' => 'event_justification_view',
    ], true),
    'close_event' => 'function() { 
                $("#event-justification-view-modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
]);
?>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'final-ad-granted-table-view-modal',
    'role' => 'dropdown_view_save',
    'title_heading' => 'Final AD Review',
    'basic_modal_body_id' => 'final-ad-granted-table-content',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary edit_button',
            'aria-label' => 'save',
            'text' => 'Submit'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/final_ad_granted_table_view', [
        'id' => 'ao_ad_dropdown_view',
    ], true),
    'save_event' => "function () { 
        openFinalADReivewConfirmationModal();
    }",
    'close_event' => 'function() {
        $("#final-ad-granted-table-view-modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
    }',
]);
?>
<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'gear_override_modal',
    'role' => 'fy_override_by_percentage',
    'title' => 'Override by Percentage',
    'title_heading' => '',
    'basic_modal_body_id' => 'gear_override_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/optimizer/gears_percentage_view', [
        'id' => 'gears_view'
    ], true),
    'close_event' => 'function() { 
                $("#gear_override_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }'
]);
$modal_id = null;
?>

<div class="content-wrapper">
    <header>
        <?php $this->load->view('SOCOM/header_buttons_view',
            array('current_page'=>'Event Summary', 'page' => $page, 'page_summary_path' => $page)
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
            <div class = "d-flex flex-row justify-content-center ">
                <div class= "d-flex flex-column w-100 justify-content-center" id="chart-1-container">
                    <div class="chart-container mb-0 position-relative" id="chart-1">
                        <div class="d-flex flex-column m-3 position-absolute" style="top: 0; right: 0;">
                            <p>Event Status:</p>
                            <div class="card bg-translucent flex-row neumorphism align-items-center" style="max-width: 550px;"> 
                                <div id="event-status-container" class="card-body d-flex flex-column align-items-center p-3" style="gap: 0.5rem;"> 
                                    <p id="event-status-text" class="font-weight-bold">Not Decided</p>
                                </div>
                            </div>
                        </div>
                        <?php if (auth_aoad_role_admin() || auth_aoad_role_user()) : ?>
                            <div class="d-flex flex-column m-3 position-absolute" style="top: 10;">
                                <p>Review Status:</p>
                                <div class="card bg-translucent flex-row neumorphism align-items-center" style="max-width: 250px;"> 
                                    <div id="review-status-container" class="card-body d-flex flex-column align-items-center p-3" style="gap: 0.5rem;"> 
                                        <p id="review-status-text" class="font-weight-bold">Not Decided</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex flex-column w-100 align-items-center p-3" style="gap: 0.5rem;">
                            <h2 id="event-name-header" class="event-name-header"><?= $selected_event ?></h2>
                            <h2 id="event-title-header"></h2>
                            <div class="card bg-translucent flex-row neumorphism align-items-center" style="max-width: 550px;">
                                <div id="event-justification-container" class="card-body d-flex flex-column align-items-center p-3" style="gap: 0.5rem;">
                                    <p class="font-weight-bold" style="font-size:2rem">Event Justification: </p>
                                    <p id="event-justification-short-text"></p>
                                    <button id="event-justification-view-more-btn" class='bx--btn bx--btn--sm bx--btn--primary' style="display: none;">View More</button>
                                </div>
                            </div>
                            <table class="display dataTable cell-border table-style w-100 bx--data-table" id="event-fy-table" class="" style="text-align:center;"></table>
                            <?php
                                $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
                                    'id' => 'balance-warning',
                                    'class' => 'bx--inline-notification--error balance-warning',
                                    'bx_notification_title' => 'ZBT is currently out of balance!',
                                    'close' => false
                                ]);
                            ?>
                            <?php if (auth_aoad_role_admin() || auth_aoad_role_user()) : ?> 
                            <div id="ao-ad-container">
                                <div class="row align-items-center text-center">
                                    <div class="col-md column">
                                        <p class="ao-ad-header">AO Recommendation</p>
                                        <button id="ao-rec-btn" class="bx--btn bx--btn--sm bx--btn--primary w-100">View</button>
                                    </div>
                                    <div class="col-md column">
                                        <p class="ao-ad-header">AO Comment</p>
                                        <button id="ao-comment-btn" class="bx--btn bx--btn--sm bx--btn--primary w-100">View</button>
                                    </div>
                                    <div class="col-md column">
                                        <p class="ao-ad-header">AD Approval</p>
                                        <button id="ad-approval-btn" class="bx--btn bx--btn--sm bx--btn--primary w-100">View</button>
                                    </div>
                                    <div class="col-md column">
                                        <p class="ao-ad-header">AD Comment</p>
                                        <button id="ad-comment-btn" class="bx--btn bx--btn--sm bx--btn--primary w-100">View</button>
                                    </div>
                                    <div class="col-md column">
                                        <p class="ao-ad-header">Final AD Action</p>
                                        <button id="final-ad-action-btn" class="bx--btn bx--btn--sm bx--btn--primary w-100">View</button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <div class="d-flex flex-row justify-content-center">
                    <div id="event-dropdown" class="d-flex flex-column mr-3 mt-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Event Name</div>
                        </div>
                        <select
                                id="select-event-name"
                                type="event"
                                combination-id=""
                                class="selection-dropdown wss-selections"
                                >
                            <?php foreach($event_names as $value): ?>
                                <option value="<?= $value['EVENT_NAME']?>" <?= $value['EVENT_NAME'] === $selected_event ? 'selected' : ''; ?> ><?= $value['EVENT_NAME']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column w-100 justify-content-center" id= "chart-2-container" >
                <div class="chart-container" id="chart-2">
                    <div class="d-flex flex-column w-100 align-items-center p-3">
                        <div>
                            <div class="d-flex flex-column align-items-end mr-5">
                                <div class="bx--form-item mr-3 pr-3 d-none">
                                    <input class="bx--toggle-input" id="proposed-granted-toggle" type="checkbox" 
                                        onclick="toggleGrantedTable()">
                                    <label class="bx--toggle-input__label" for="proposed-granted-toggle">    
                                        <span class="bx--toggle__switch">
                                            <span class="bx--toggle__text--off" aria-hidden="true">Proposed</span>
                                            <span class="bx--toggle__text--on" aria-hidden="true">Granted</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center" id="event-funding-lines-table-container">
                                <h2 id="funding-lines-header"><span class="event-name-header"><?= $selected_event ?></span> Funding Lines</h2>
                                <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" id="event-funding-lines-table" class="" style="text-align:center;"></table>
                            </div>
                            <div class="d-flex flex-column align-items-center" id="event-final-ad-granted-table-container">
                                <h2 id="final-ad-granted-header" class="d-none"><span class="event-name-header"><?= $selected_event ?></span> Funding Lines (Granted)</h2>
                                <table class="d-none display dataTable cell-border table-style w-100 bx--data-table pt-3" id="event-final-ad-granted-table" class="" style="text-align:center;"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div data-modal id="final-ad-confirm-modal" class="bx--modal" role="dialog"
    aria-modal="true" aria-labelledby="final-ad-confirm-label"
    aria-describedby="final-ad-confirm-heading" tabindex="-1">
    <span tabindex="0" role="link" class="bx--visually-hidden"></span>
    <div role="dialog" class="bx--modal-container bx--modal-container--sm"
    aria-label="Label" aria-modal="true" tabindex="-1">
        <div class="bx--modal-header">
        <h3 id="final-ad-confirm-heading"
        class="bx--modal-header__heading">Final AD Confirmation</h3>
        <button id="final-ad-confirm-close-btn"
        class="bx--modal-close" type="button" data-modal-close aria-label="close">
            <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" aria-hidden="true" width="20" height="20" viewBox="0 0 32 32"
            class="bx--modal-close__icon">
            <path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z">

            </path>
            </svg>
        </button>
        </div>
        <div id="final-ad-confirm-body" class="bx--modal-content d-flex flex-column"
        aria-labelledby="final-ad-confirm-heading">
            <div class="my-2">Are you sure would like to <span id="final-ad-action" class="font-weight-bold"></span>?</div>
            <div>This action is final and cannot be undone.</div>
        </div>
        <div class="bx--modal-footer bx--btn-set">
        <button id="final-ad-cancel-btn" tabindex="0"
        class="bx--btn bx--btn--secondary" type="button" data-modal-close>Cancel</button>
        <button id="final-ad-confirm-btn" tabindex="0"
        class="bx--btn bx--btn--primary" type="button" data-modal-primary-focus>Confirm</button>
        </div>
    </div>
    <span tabindex="0" role="link" class="bx--visually-hidden"></span>
</div>

<div data-modal id="final-ad-review-confirm-modal" class="bx--modal" role="dialog"
    aria-modal="true" aria-labelledby="final-ad-review-confirm-label"
    aria-describedby="final-ad-review-confirm-heading" tabindex="-1">
    <span tabindex="0" role="link" class="bx--visually-hidden"></span>
    <div role="dialog" class="bx--modal-container bx--modal-container--sm"
    aria-label="Label" aria-modal="true" tabindex="-1">
        <div class="bx--modal-header">
        <h3 id="final-ad-review-confirm-heading"
        class="bx--modal-header__heading">Final AD Review Confirmation</h3>
        <button id="final-ad-review-confirm-close-btn"
        class="bx--modal-close" type="button" data-modal-close aria-label="close">
            <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" aria-hidden="true" width="20" height="20" viewBox="0 0 32 32"
            class="bx--modal-close__icon">
            <path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z">

            </path>
            </svg>
        </button>
        </div>
        <div id="final-ad-review-confirm-body" class="bx--modal-content d-flex flex-column"
        aria-labelledby="final-ad-review-confirm-heading">
            <div class="my-2">Are you sure would like to make these changes?</div>
            <div>This action is final and cannot be undone.</div>
        </div>
        <div class="bx--modal-footer bx--btn-set">
        <button id="final-ad-review-cancel-btn" tabindex="0"
        class="bx--btn bx--btn--secondary" type="button" data-modal-close>Cancel</button>
        <button id="final-ad-review-confirm-btn" tabindex="0"
        class="bx--btn bx--btn--primary" type="button" data-modal-primary-focus>Confirm</button>
        </div>
    </div>
    <span tabindex="0" role="link" class="bx--visually-hidden"></span>
</div>

<script>
    const handson_license = '<?= RHOMBUS_HANDSONTABLE_LICENSE ?>';
    const base_url_js = <?php echo json_encode(base_url()); ?>;
    const page = <?= json_encode($page); ?>;
    const graphData = <?= json_encode($graphData); ?>;
    const userEmails = <?= json_encode($user_emails); ?>;
    const userId = <?= json_encode($user_id); ?>;
    let aoData = <?= json_encode($ao_data); ?>;
    let adData = <?= json_encode($ad_data); ?>;
    let finalAdData = <?= json_encode($finalAdData); ?>;
    const dropdownChoices = <?= json_encode($aoad_dropdown_choices); ?>;
    const isAoUser = <?= json_encode($is_ao_user); ?>;
    const isAdUser = <?= json_encode($is_ad_user); ?>;


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
</script>
