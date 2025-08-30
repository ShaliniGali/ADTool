<div class="d-flex justify-content-between align-items-center ml-auto mr-auto w-75">
    <div data-content-switcher class="bx--content-switcher w-50 mb-5">
    <button class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".coa-share-admin-panel-1">My Shared COA</button>
    <button class="bx--content-switcher-btn" data-target=".coa-share-admin-panel-2">COA Shared to Me</button>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center ml-auto mr-auto w-75">
    <h4 class="font-weight-bold" cycleId=<?= !empty($active_cycle_with_criteria['CYCLE_ID']) ? $active_cycle_with_criteria['CYCLE_ID'] : '' ?>>Active Cycle: <span id="active-cycle-name"><?= !empty($active_cycle_with_criteria['CYCLE_NAME']) ? $active_cycle_with_criteria['CYCLE_NAME'] : 'No Active Cycle' ?></span></h4>
    <button id="share-coa-modal-btn" class="bx--btn bx--btn--primary">Open Share COA</button>
</div>

<!-- My Shared COA Table -->
<div class="d-flex flex-row h-100 coa-share-admin-panel-1">
    <div class="d-flex flex-column ml-auto mr-auto w-75 mb-5">
		<div class="neumorphism mb-auto mt-3">
            <div class="card bg-translucent flex-row w-100 neumorphism align-items-center">
                <div class="card-body w-85">
                    <div class="d-flex flex-row justify-content-between mb-3">
                        <h4 class="mb-3 mr-5">My Shared COA</h4>
                    </div>
                    <div class="bx--form-item pt-1" style="align-items: flex-end; color: #444 !important;">
                        <label class="bx--checkbox-label">
                            <input id="coa-shared-by-me-revoked-checkbox" class="bx--checkbox" type="checkbox" value="1" name="checkbox">
                            Show Revoked COAs
                        </label>
                    </div>
                    <table id="coa-shared-by-me-table" class="bx--data-table w-100">
                    </table>
                </div>
            </div>
		</div>
    </div>
</div>

<!-- COA Shared to Me Table -->
<div class="d-flex flex-row h-100 coa-share-admin-panel-2" hidden>
    <div class="d-flex flex-column ml-auto mr-auto w-75 mb-5">
		<div class="neumorphism mb-auto mt-3">
            <div class="card bg-translucent flex-row w-100 neumorphism align-items-center">
                <div class="card-body w-85">
                    <div class="d-flex flex-row mb-3">
                        <h4 class="mb-3 mr-5">COA Shared to Me</h4>
                    </div>
                    <!-- <div class="bx--form-item pt-1" style="align-items: flex-end; color: #444 !important;">
                        <label class="bx--checkbox-label">
                            <input id="coa-shared-to-me-revoked-checkbox" class="bx--checkbox" type="checkbox" value="1" name="checkbox">
                            Show Revoked COAs
                        </label>
                    </div> -->
                    <table id="coa-shared-to-me-table" class="bx--data-table w-100">
                    </table>
                </div>
            </div>
		</div>
    </div>
</div>

<!-- Share Modal -->
<?php
    $this->load->view('templates/carbon/carbon_modal', [
        'modal_id' => 'coa_share_view_modal',
        'role' => 'coa_share_view_modal',
        'title' => 'Share COA',
        'title_heading' => '',
        'basic_modal_body_id' => 'share_coa_view_body',
        'buttons' => [
            [
                'class' => 'bx--btn--secondary',
                'aria-label' => 'close',
                'text' => 'Close'
            ],
            [
                'class' => 'bx--btn--primary edit_button',
                'aria-label' => 'save',
                'text' => 'Share COA'
            ]
        ],
        'html_content' => $this->load->view('SOCOM/dashboard/coa_management/coa_share_view_modal', [
            'id' => 'coa_share_view_modal'
        ], true),
        'close_event' => 'closeShareCoaModal',
        'save_event' => 'shareCoa'
    ]);
?>

<style>
    #my-coa-table_wrapper {
        width: 100%;
        margin-top: 20px;
    }

    #my-coa-table_wrapper select {
        background-color: #FFF;
    }

    #my-coa-table_wrapper input {
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

const user_emails = <?= json_encode($user_emails); ?>;

let sharedCoaToMeTable;

let sharedCoaFromMeTable;


const sharedCoaByMeColDef = [
    { targets: 0, title: 'COA Name', data: 'COA_TITLE'},
    { targets: 1, title: 'COA Description', data: 'COA_DESCRIPTION' },
    { targets: 2, title: 'Shared Date', data: "SHARED_DATETIME" },
    {
        targets: 3,
        title: 'Shared To',
        data: 'NEW_USER_ID',
        render: function(data) {
            return user_emails[data] ?? '';
        }
    },
    {
        targets: 4,
        title: 'Actions',
        searchable: false,
        orderable: false,
        lengthChange: false,
        render: data => `<?php $this->load->view('SOCOM/dashboard/coa_management/list_overflow_menu', ['id' => '']); ?>`
    }
];

const sharedCoaToMeColDef = [
    { targets: 0, title: 'COA Name', data: 'COA_TITLE'},
    { targets: 1, title: 'COA Description', data: 'COA_DESCRIPTION' },
    { targets: 2, title: 'Shared Date', data: "SHARED_DATETIME" },
    {
        targets: 3,
        title: 'Shared By',
        data: 'ORIGINAL_USER_ID',
        render: function(data) {
            return user_emails[data] ?? ''; 
        }
    }
];

</script>
