<div class="bx--tabs bx--tabs--container">
    <div id="score-notifications-list" class="d-flex flex-row mb-3">
        <div class="w-50"> 
        <?php
            $this->load->view('templates/carbon/inline_notification',
            [
            'notification_id' => 'success-list-score',
            'type' => 'success',
            'dnone' => true,
            'bx_notification_title' => 'Program Score',
            'bx_notification_subtitle' => 'Success',
            'close' => false
            ]
        );
        ?>

        <?php 
        $this->load->view('templates/carbon/inline_notification',
            [
            'notification_id' => 'error-list-score',
            'type' => 'error',
            'dnone' => true,
            'bx_notification_title' => 'Program Score',
            'bx_notification_subtitle' => 'Error',
            'close' => false
            ]
        );
        ?>
        </div>
    </div>
    <div class="d-flex flex-column w-100 px-5">
        <table id="score_listing_list" class="bx--data-table w-100">
        </table>
    </div>
</div>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'score_view_modal',
    'role' => 'score_view_list',
    'title' => 'Add/Edit Score',
    'title_heading' => '',
    'basic_modal_body_id' => 'score_view_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary edit_button',
            'aria-label' => 'save',
            'text' => 'Save Score'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/score/score_modal_view', [
        'id' => 'core_view'
    ], true),
    'close_event' => 'function() { 
                $("#score_view_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
    'save_event' => 'saveScore'
]);
?>

<script>
    const default_criteria = JSON.parse('<?= json_encode($default_criteria) ?>');
    const handson_license = '<?= RHOMBUS_HANDSONTABLE_LICENSE ?>';

    let column_definition = [{
            title: 'Name',
            data: 'NAME'
        },
        {
            title: 'Description',
            data: 'DESCRIPTION'
        },
        {
            title: 'Last Modified',
            data: 'UPDATED_TIMESTAMP'
        },
        {
            title: 'Active Score',
            data: 'IS_ACTIVE'
        },
        {
            searchable: false,
            orderable: false,
            lengthChange: false,
            render: data => `<?php $this->load->view('SOCOM/score/list_overflow_menu', ['id' => '']); ?>`
        }
    ];
</script>