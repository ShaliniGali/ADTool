<!-- Save notification div -->
<div id="cycle-notifications" class="d-flex flex-row mb-3">
  <?php
    $this->load->view('templates/carbon/inline_notification',
      [
        'notification_id' => 'success-cycles',
        'type' => 'success',
        'dnone' => true,
        'bx_notification_title' => 'Cycle',
        'bx_notification_subtitle' => 'Success',
        'close' => false
      ]
    );
  ?>

  <?php 
  $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'error-cycles',
      'type' => 'error',
      'dnone' => true,
      'bx_notification_title' => 'Option Cycle',
      'bx_notification_subtitle' => 'Error',
      'close' => false
    ]
  );
  ?>
</div>

  <div class="d-flex flex-column align-items-center mr-auto w-100">
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
        <label for="edit-text-input-cycle-name" class="bx--label">Name</label>
        <div class="bx--form__helper-text"></div>
        <div class="d-flex flex-row w-100">
            <input id="edit-text-input-cycle-name" type="text"
                class="bx--text-input pt-4 pb-4" name="Name" value="<?= set_value('name'); ?>"
                placeholder="Cycle Name"
            >
        </div>
    </div>

    <div class="bx--form-item w-100 mb-4">
        <label for="edit-cycle-description" class="bx--label">Description (Optional)</label>
        <div class="bx--form__helper-text"></div>
        <textarea id="edit-cycle-description" class="bx--text-area " name="description" rows="4" cols="50" placeholder="Cycle Description" value="<?= set_value('description'); ?>"></textarea>
    </div>
</div>
