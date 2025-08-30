<div class="d-flex flex-column w-100">
    <div class="d-flex flex-row">
        <div class="justify-content-end mt-auto w-50">
            <button id="load-coa-modal-btn" class="bx--btn bx--btn--primary">Load COA</button>
        </div>
        <div id="budget-table-container" class="d-flex w-50">
            <table id="budget-table" class="display w-100"></table>
        </div>
    </div>

    <div class="d-flex flex-row w-100 justify-content-center mt-5 mb-5" id="merge-coa-table-container">
        <div class="d-flex w-100 justify-content-center p-2"> 
            <h2>Click Load COA to merge COA</h2> 
        </div>
    </div>

    <div class="bx--loading-overlay d-none" style="z-index: 90000;" id="merge-coa-table-loading">
      <div data-loading class="bx--loading">
        <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
            <title>Loading</title>
            <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
        </svg>
      </div>
    </div>
</div>

<!-- Merge Modal -->
<?php
    $this->load->view('templates/carbon/carbon_modal', [
        'modal_id' => 'coa_merge_view_modal',
        'role' => 'coa_merge_view_modal',
        'title' => 'Merge COA',
        'title_heading' => '',
        'basic_modal_body_id' => 'merge_coa_view_body',
        'buttons' => [
            [
                'class' => 'bx--btn--secondary',
                'aria-label' => 'close',
                'text' => 'Close'
            ],
            [
                'class' => 'bx--btn--primary load-coa-btn',
                'aria-label' => 'save',
                'text' => 'Load COA'
            ]
        ],
        'html_content' => $this->load->view('SOCOM/dashboard/coa_management/coa_merge_view_modal', [
            'id' => 'coa_merge_view_modal'
        ], true),
        'close_event' => 'closeMergeCoaModal',
        'save_event' => 'loadCoa'
    ]);
?>

<?php 
    $this->load->view('templates/carbon/carbon_modal', [
        'modal_id' => 'coa_merge_save_view_modal',
        'role' => 'coa_merge_save_view_modal',
        'title' => '',
        'title_heading' => 'Merge COA',
        'basic_modal_body_id' => 'merge_coa_save_view_body',
        'buttons' => [
            [
                'class' => 'bx--btn--secondary',
                'aria-label' => 'close',
                'text' => 'Close'
            ],
            [
                'class' => 'bx--btn--primary merge-coa-btn',
                'aria-label' => 'save',
                'text' => 'Merge COA'
            ]
        ],
        'html_content' => $this->load->view('SOCOM/dashboard/coa_management/coa_merge_save_view_modal', [
            'id' => 'coa_merge_save_view_modal'
        ], true),
        'close_event' => 'closeSaveMergeCoaModal',
        'save_event' => 'mergeCOA'
    ]);
?>

<style>

    #budget-table_wrapper {
        width: 100%;
        margin-top: 20px;
    }

    #show-coa-table_wrapper {
        width: 100%;
        margin-top: 20px;
    }

    #show-coa-table_wrapper select {
        background-color: #FFF;
    }

    #show-coa-table_wrapper input {
        background-color: #FFF;
    }

    table.dataTable thead > tr > th.sorting {
        padding-left: 18px;
    }

    table.dataTable thead .sorting {
        background-image: none;
    }

    .dataTables_filter {
        margin: 8px 0;
    }
</style>

<script>

</script>
