<div id="score-name" class="mb-3"><h5></h5></div>
<div id="score-notifications" class="d-flex flex-row">
  <?php
    $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'success-score',
      'type' => 'success',
      'dnone' => true,
      'bx_notification_title' => 'Option Score',
      'bx_notification_subtitle' => 'Success',
      'close' => false
    ]
  );
  ?>

  <?php 
  $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'error-score',
      'type' => 'error',
      'dnone' => true,
      'bx_notification_title' => 'Option Score',
      'bx_notification_subtitle' => 'Error',
      'close' => false
    ]
  );
  ?>
</div>
<div id="score_outer_box" class="d-flex flex-wrap">
  <div id="score_tab_data"></div>
  <div id="score_form_elems" class="d-flex flex-column">
    <div class="bx--form-item">
                <label for="score-name-i" class="bx--label">Title</label>
                <div class="bx--form__helper-text"></div>
                <input id="score-name-i" type="text"
                  class="bx--text-input"
                  name="name" value=""
                  placeholder="Score Name" />
    </div>
    <div class="bx--form-item mt-1">
      <label for="text-input-title" class="bx--label">Description</label>
      <div class="bx--form__helper-text"></div>
      <textarea id="score-description" class="bx--text-area"
        rows="4" cols="50" 
        placeholder="Score Description" 
        name="description"></textarea>
    </div>
  </div>
</div>
<input id="hidden_score_id" type="hidden" />
<input id="hidden_program_id" type="hidden" />