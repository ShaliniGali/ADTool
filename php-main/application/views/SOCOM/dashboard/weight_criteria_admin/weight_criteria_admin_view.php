<div data-content-switcher class="bx--content-switcher w-50 mb-5">
  <button class="bx--content-switcher-btn" data-target=".criteria-admin-panel-1">Create Criteria</button>
  <button class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".criteria-admin-panel-2">View Criteria</button>
</div>

<div class="d-flex flex-column align-items-start mr-auto w-75 mt-5 criteria-admin-panel-1" style="max-width: 736px" hidden>
    <h4 class="font-weight-bold mb-2" cycleId=<?= !empty($active_cycle_with_criteria['CYCLE_ID']) ? $active_cycle_with_criteria['CYCLE_ID'] : '' ?>>Active Cycle: <span id="active-cycle-name"><?= !empty($active_cycle_with_criteria['CYCLE_NAME']) ? $active_cycle_with_criteria['CYCLE_NAME'] : 'No Active Cycle' ?></span></h4>

    <?php
        $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
            'id' => 'criteria-form-success',
            'class' => 'bx--inline-notification--success',
            'file_upload_class' => 'd-flex flex-column',
            'bx_notification_title' => 'Criteria <span class="criteria-name-text font-italic">' . htmlspecialchars($active_cycle_with_criteria['CRITERIA_NAME']) . '"</span> has already been created for this cycle.',
            'bx_notification_subtitle' => '<ul class="bx--list--unordered mt-2">
                <li class="bx--list__item">Criteria terms can be viewed in the "View Criteria" tab</li>
            </ul>',
            'close' => false
        ]);
    ?>

    <!-- Criteria Form -->
    <div id="criteria-name-term-form" class="d-flex flex-column align-items-start mr-auto w-100">
        <?php
            $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
                'id' => 'criteria_admin_form_instructions',
                'class' => 'bx--inline-notification--info',
                'file_upload_class' => 'd-flex flex-column',
                'bx_notification_title' => 'Criteria Name Instructions:',
                'bx_notification_subtitle' => '<ul class="bx--list--unordered mt-2">
                    <li class="bx--list__item">Create a criteria name for the set of criteria terms</li>
                    <li class="bx--list__item">Criteria Term format is A-Z, 0-9 \' \' and _</li>
                    <li class="bx--list__item">Add up to 15 criteria terms</li>
                    <li class="bx--list__item">Criteria name and terms cannot be edited or deleted after submitting</li>
                </ul>',
                'close' => false
            ]);
        ?>
        <div class="bx--form-item w-100 mb-4">
            <label for="text-input-criteria-name" class="bx--label">Criteria Name</label>
            <div class="bx--form__helper-text"></div>
            <div class="d-flex flex-row w-100">
                <input id="text-input-criteria-name" type="text"
                    class="bx--text-input pt-4 pb-4" 
                    name="text-input-criteria-name" 
                    criteriaId="<?= !empty($active_cycle_with_criteria['CRITERIA_ID']) ? $active_cycle_with_criteria['CRITERIA_ID'] : 0 ?>" 
                    value="<?= !empty($active_cycle_with_criteria['CRITERIA_NAME']) ? $active_cycle_with_criteria['CRITERIA_NAME'] : set_value('name'); ?>"
                    placeholder="Criteria Name"
                >
            </div>
        </div>

        <div class="bx--form-item w-100 mb-4">
            <label for="text-input-criteria-term" class="bx--label">Criteria Terms</label>
            <div class="bx--form__helper-text"></div>
            <div id="criteria-term-input-container" class="d-flex flex-column w-100">
                <!-- Container that criteria term inputs are added in -->
            </div>
        </div>

        <button id="create-criteria-btn" class="bx--btn bx--btn--primary m-auto rhombus-form-submit" type="submit">Create Criteria</button>
    </div>
</div>

<!-- Criteria Terms Table -->
<div class="d-flex flex-row h-100 mt-3 mb-3 criteria-admin-panel-2">
    <div class="d-flex flex-column ml-auto mr-auto w-75 mb-5">
        <?php
                $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
                'id' => 'criteria-terms-warning',
                'class' => 'bx--inline-notification--warning',
                'file_upload_class' => 'd-flex flex-column',
                'bx_notification_title' => 'No Criteria Terms',
                'bx_notification_subtitle' => '<ul class="bx--list--unordered mt-2">
                    <li class="bx--list__item">Please create criteria in the "Create Criteria" tab</li>
                </ul>',
                'close' => false
            ]);
        ?>
		<div class="neumorphism mb-auto mt-3">
            <div class="card bg-translucent flex-row w-100 neumorphism align-items-center">
                <div class="card-body w-85">
                    <div class="d-flex flex-row mb-3">
                    <h4 class="mb-3 mr-5">
                        <span id="criteria-terms-table-header">Criteria Terms</span>
                        <span class="criteria-name-text font-weight-bold"><?php $active_cycle_with_criteria['CRITERIA_NAME']; ?></span>
                    </h4>
                    </div>
                    <table id="criteria-terms-list-table" class="bx--data-table w-100">
                    </table>
                </div>
            </div>
		</div>
    </div>
</div>
<?php
    $this->load->view('templates/carbon/carbon_modal', [
        'modal_id' => 'editDescriptionModal',
        'role' => 'edit_description',
        'title' => 'Edit Description',
        'title_heading' => '',
        'basic_modal_body_id' => 'edit_description_body',
        'buttons' => [
            [
                'class' => 'bx--btn--secondary',
                'aria-label' => 'close',
                'text' => 'Close'
            ],
            [
                'class' => 'bx--btn--primary edit_button',
                'aria-label' => 'save',
                'text' => 'Save Description'
            ]
        ],
        'html_content' => $this->load->view('SOCOM/dashboard/weight_criteria_admin/edit_description_modal', [
            'id' => 'editDescriptionModal'
        ], true),
        'close_event' => 'closeEditModal',
        'save_event' => 'saveDescription'
    ]);
?>
