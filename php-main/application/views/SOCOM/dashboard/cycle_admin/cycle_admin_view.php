<div data-content-switcher class="bx--content-switcher w-50 mb-5">
  <button class="bx--content-switcher-btn" data-target=".cycle-admin-panel-1">Create Cycle</button>
  <button class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".cycle-admin-panel-2">Manage Cycle</button>
</div>

<!-- Cycle Admin Form -->
<div class="d-flex flex-column align-items-center mr-auto w-75 mt-5 cycle-admin-panel-1" style="max-width: 736px" hidden>
    <?php
        $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
            'notification_id' => 'cycle_admin_form_instructions',
            'class' => 'bx--inline-notification--info',
            'file_upload_class' => 'd-flex flex-column',
            'bx_notification_title' => 'Cycle Name Instructions:',
            'bx_notification_subtitle' => 
                '<ul class="bx--list--unordered mt-2">
                    <li class="bx--list__item"><span class="font-weight-bold">FY</span>{<span class="font-weight-bold text-info">YEAR</span>}_{<span class="font-weight-bold text-info">MOST RECENT POSITION</span>}_{<span class="font-weight-bold text-info">#</span>}</li>
                    <li class="bx--list__item">e.g. <span class="font-weight-bold">FY27_ZBT_1</span> or <span class="font-weight-bold">FY27_ISS_2</span></li>
                </ul>',
            'close' => false
        ]);
    ?>
    <div class="bx--form-item w-100 mb-4">
        <label for="text-input-cycle-name" class="bx--label">Name</label>
        <div class="bx--form__helper-text"></div>
        <div class="d-flex flex-row w-100">
            <input id="text-input-cycle-name" type="text"
                class="bx--text-input pt-4 pb-4" name="text-input-cycle-name" value="<?= set_value('name'); ?>"
                placeholder="Cycle Name"
            >
        </div>
    </div>

    <div class="bx--form-item w-100 mb-4">
        <label for="cycle-text-area-description" class="bx--label">Description (Optional)</label>
        <div class="bx--form__helper-text"></div>
        <textarea id="cycle-text-area-description" class="bx--text-area " name="cycle-text-area-description" rows="4" cols="50" placeholder="Cycle Description" value="<?= set_value('description'); ?>"></textarea>
    </div>

    <button id="create-cycle-btn" class="bx--btn bx--btn--primary m-auto rhombus-form-submit" type="submit">Create Cycle</button>
</div>

<!-- Cycle Admin Management Table -->
<div class="d-flex flex-row h-100 mt-5 mb-5 cycle-admin-panel-2">
    <div class="d-flex flex-column ml-auto mr-auto w-75 mb-5">
		<div class="neumorphism mb-auto mt-3">
            <div class="card bg-translucent flex-row w-100 neumorphism align-items-center">
                <div class="card-body w-85">
                    <div class="d-flex flex-row mb-3">
                        <h4 class="mb-3 mr-5">Cycles List</h4>
                    </div>
                    <div class="bx--form-item pt-1" style="align-items: flex-end; color: #444 !important;">
                        <label class="bx--checkbox-label">
                            <input id="cycle-deleted-checkbox" class="bx--checkbox" type="checkbox" value="1" name="checkbox">
                            Show Deleted Cycles
                        </label>
                    </div>
                    <table id="cycle-list-table" class="bx--data-table w-100">
                    </table>
                </div>
            </div>
		</div>
    </div>
</div>

<!-- Edit Cycle Modal -->
<?php
    $this->load->view('templates/carbon/carbon_modal', [
        'modal_id' => 'cycle_edit_view_modal',
        'role' => 'cycle_edit_view',
        'title' => 'Edit Cycle',
        'title_heading' => '',
        'basic_modal_body_id' => 'cycle_edit_view_body',
        'buttons' => [
            [
                'class' => 'bx--btn--secondary',
                'aria-label' => 'close',
                'text' => 'Close'
            ],
            [
                'class' => 'bx--btn--primary edit_button',
                'aria-label' => 'save',
                'text' => 'Save Cycle'
            ]
        ],
        'html_content' => $this->load->view('SOCOM/dashboard/cycle_admin/cycle_edit_view_modal', [
            'id' => 'cycle_edit_view'
        ], true),
        'close_event' => 'closeEditModal',
        'save_event' => 'saveCycle'
    ]);
?>
