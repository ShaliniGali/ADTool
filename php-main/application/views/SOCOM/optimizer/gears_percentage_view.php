<div class="d-flex flex-row">
  <div id="gears-name" class="mb-3 ml-3 mt-1 w-50 d-flex flex-column">
    <ul class="bx--list--unordered">
      <li class="bx--list__item">
        Program: <span id="gears_program_name"></span>
      </li>
      <li class="bx--list__item">
        EOC Code: <span id="gears_eoc_code"></span>
      </li>
      <li id="gears_event_name_li" class="bx--list__item">
        Event: <span id="gears_event_name"></span>
      </li>
    </ul>
  </div>
  <div id="gears-notifications" class="d-flex flex-column">
    <?php
      $this->load->view('templates/carbon/inline_notification',
      [
        'notification_id' => 'success-gears',
        'type' => 'success',
        'dnone' => true,
        'bx_notification_title' => 'Percentage Update Success',
        'bx_notification_subtitle' => 'Success',
        'close' => false
      ]
    );
    ?>
    <?php 
    $this->load->view('templates/carbon/inline_notification',
      [
        'notification_id' => 'error-gears',
        'type' => 'error',
        'dnone' => true,
        'bx_notification_title' => 'Percentage Update Error',
        'bx_notification_subtitle' => 'Error',
        'close' => false
      ]
    );
    ?>
  </div>
</div>
<div id="gears_outer_box" class="d-flex flex-column w-100">
  <div id="gear_top_elems" class="d-flex flex-row w-100">
    <div class="d-flex flex-column w-50">
      <div class="bx--form-item">
                  <label for="gear-name-i" class="bx--label">Select Percentage</label>
                  <div class="bx--form__helper-text"></div>
                  <select id="gears_percentage_sel">
                    <?php for($i = 100; $i >= 0; $i--): ?>
                      <option value="<?= $i ?>"><?=$i?>%</option>
                    <?php endfor; ?>
                  </select>
      </div>
      <div class="d-flex flex-row mt-1 w-50">
        <div class="bx--form-item mr-3">
            <button id="gears_reset_percentage" tabindex="1" class="bx--btn bx--btn--secondary" type="button">Reset Percentage</button>
        </div>
        <div class="bx--form-item">
            <button id="gears_set_percentage" tabindex="0" class="bx--btn bx--btn--primary" type="button">Set Percentage</button>
        </div>
      </div>
    </div>
    <div class="w-50">
        <div id="gears_tab_data" class="ml-5"></div>
    </div>
  </div>
  
</div>
<input id="row_id" type="hidden" />

<script>
  $('#gears_percentage_sel').select2({
        placeholder: "Select an option",
        width: '12vw',
        matcher: function(params, data) {
          // Do not display the item if there is no 'text' property
          if (typeof data.text === 'undefined') {
            return null;
          }

          // return exact matches only
          if (data.text === `${params.term}%`) {
            var modifiedData = $.extend({}, data, true);

            return modifiedData;
          }

          // Return `null` if the term should not be displayed
          return null;
        },
        language: {
            noResults: function() {
            return "";
            }
        }
  });
</script>