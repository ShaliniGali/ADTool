<div id="coa-notifications" class="d-flex flex-row mb-3">
  <?php
    $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'success-coa',
      'type' => 'success',
      'dnone' => true,
      'bx_notification_title' => 'Save COA',
      'bx_notification_subtitle' => 'Success',
      'close' => false
    ]
  );
  ?>

  <?php 
  $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'error-coa',
      'type' => 'error',
      'dnone' => true,
      'bx_notification_title' => 'Save COA',
      'bx_notification_subtitle' => 'Error',
      'close' => false
    ]
  );
  ?>
</div>

<div id="coa_outer_box" class="d-flex flex-wrap mt-3 pt-3">
  <div id="coa_tab_data"></div>
  <div id="coa_form_elems" class="d-flex flex-column h-75">
    <div class="bx--form-item">
                <label for="coa-name" class="bx--label">Title</label>
                <div class="bx--form__helper-text"></div>
                <input id="coa-name" type="text"
                  class="bx--text-input"
                  name="name" value=""
                  placeholder="COA Name" />
    </div>
    <div class="bx--form-item mt-1">
      <label for="text-input-title" class="bx--label">Description</label>
      <div class="bx--form__helper-text"></div>
      <textarea id="coa-description" class="bx--text-area"
        rows="4" cols="50"
        placeholder="COA Description"
        name="description"></textarea>
    </div>
  </div>
</div>