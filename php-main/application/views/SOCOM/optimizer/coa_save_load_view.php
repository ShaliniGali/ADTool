<div class="d-flex flex-column w-100">
    <div class="d-flex flex-row justify-content-between mb-3">
        <button id="save-coa" class="bx--btn bx--btn--primary uniform-length-button" disabled type="button">
            Save COA
        </button>
        <button 
                id="run-optimizer"
                class="bx--btn bx--btn--primary uniform-length-button"
                type="button">
                Run Optimizer
            </button>
    </div>

    <div class="d-flex flex-row justify-content-between">
        <button id="load-coa" class="bx--btn bx--btn--primary uniform-length-button" type="button">
            Load COA
        </button>
        <button id="create-coa" class="bx--btn bx--btn--primary uniform-length-button" disabled type="button">
            Create New COA
        </button>
    </div>
</div>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'coa_save_modal',
    'role' => 'coa_view_list',
    'title' => 'Save the current Optimization Run',
    'title_heading' => 'COA Save',
    'basic_modal_body_id' => 'coa_view_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary edit_button',
            'aria-label' => 'save',
            'text' => 'Save COA'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/optimizer/coa_modal_view', [
        'id' => 'core_view'
    ], true),
    'close_event' => 'function() {
                $("#coa_save_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
    'save_event' => 'saveCOA'
]);


$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'coa_load_modal',
    'role' => 'coa_load_list',
    'title' => 'View in Graph up to Three COA',
    'title_heading' => 'COA Load',
    'basic_modal_body_id' => 'coa_load_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary load_button',
            'aria-label' => 'save',
            'text' => 'View COA'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/optimizer/coa_modal_load', [
        'id' => 'core_load_view'
    ], true),
    'close_event' => 'function() {
                $("#coa_load_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
    'save_event' => 'getUserSavedCOA'
]);
$this->load->view('SOCOM/optimizer/coa_simulation_table_insert_modal_view');

?>

<style>
.uniform-length-button{
    max-width: 200px;
    width: 100%;
}
</style>