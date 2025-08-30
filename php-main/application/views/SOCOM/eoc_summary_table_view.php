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
    'role' => 'dropdown_view_save',
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

<div class="overflow-auto">
    <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" 
    id="eoc-summary-table-output" class="" style="text-align:center;">
        <thead>
            <?php foreach($headers as $header): ?>
                <th class="bx--table-header-label"><?= $header['title']; ?></th>
            <?php endforeach; ?>
        </thead>
    </table>
</div>


<script>
    var table_dropdown = {}
    var eocSummaryData = <?=json_encode($data);?>;
    var eocSummaryHeader = <?=json_encode($headers);?>;
    var yearIndex = <?=json_encode($yearIndex);?>;
    var indexOfYear = <?= $indexOfYear; ?>;
    var yearList = <?=json_encode($yearList);?>;
    var initHeadersIndexList = <?= json_encode($initHeadersIndexList); ?>;
    var aeaoIndexList = <?=json_encode($aeaoIndexList);?>;
    var eoc_summary_dt_object = initEocSummaryDatatable(
        'eoc-summary-table-output',
        eocSummaryData,
        eocSummaryHeader,
        yearIndex,
        yearList,
        indexOfYear,
        aeaoIndexList,
        initHeadersIndexList,
        page
    );

    $("#eoc-summary-table-output .selection-dropdown").select2({
        placeholder: "Select an option",
        width: '16vw'
    });
</script>