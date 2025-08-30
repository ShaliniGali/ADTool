<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'business_rules_modal',
    'role' => 'business_rules_filter_list',
    'title' => 'Filter by Business Rules',
    'title_heading' => 'Business Rules',
    'basic_modal_body_id' => 'business_rules_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary load_button',
            'aria-label' => 'save',
            'text' => 'Apply'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/optimizer/business_rules', [
        'id' => 'business_rules_view'
    ], true),
    'close_event' => 'function() {
                $("#business_rules_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
    'save_event' => '' //save event functions in business_rules.js
]);

?>