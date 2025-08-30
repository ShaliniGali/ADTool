<!-- Save notification div -->
<div id="sharing-coa-notifications" class="d-flex flex-row mb-3">
  <?php
    $this->load->view('templates/carbon/inline_notification',
      [
        'notification_id' => 'success-sharing-coa',
        'type' => 'success',
        'dnone' => true,
        'bx_notification_title' => 'COA',
        'bx_notification_subtitle' => 'Success',
        'close' => false
      ]
    );
  ?>

  <?php 
  $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'error-sharing-coa',
      'type' => 'error',
      'dnone' => true,
      'bx_notification_title' => 'COA',
      'bx_notification_subtitle' => 'Error',
      'close' => false
    ]
  );
  ?>
</div>

  <div class="d-flex flex-column align-items-center mr-auto w-100">
    <?php
        $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
            'notification_id' => 'sharing_coa_form_instructions',
            'class' => 'bx--inline-notification--info',
            'bx_notification_title' => 'Sharing COA Instructions:',
            'bx_notification_subtitle' => 
              '<ul class="bx--list--unordered mt-2">
                <li class="bx--list__item">Select User(s) to share COA(s) with</li>
                <li class="bx--list__item">Select COA(s) to send to selected user(s)</li>
                <li class="bx--list__item">Click Share COA</li>
              </ul>',
            'close' => false
        ]);

        $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
            'id' => 'already-shared-coa-error',
            'class' => 'bx--inline-notification--error d-none',
            'bx_notification_title' => 'Unable to Share COA',
            'bx_notification_subtitle' => '',
            'close' => false
        ]);

        // print_r($user_emails);
    ?>
    <div class="bx--form-item w-100 mb-4">
      <div class="bx--select-input__wrapper">
        <select id="select-user-emails" class="bx--select-input" name="emails[]" multiple="multiple">
          <?php 
          // Don't display current user's email
            if (isset($user_emails[$user_id])) {
              unset($user_emails[$user_id]);
            };

            // Sort the user emails alphabetically while preserving id
            asort($user_emails);

            if (!empty($user_emails)): 
          ?>
              <?php foreach ($user_emails as $id => $email): ?>
                  <option class="bx--select-option" value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($email) ?></option>
              <?php endforeach; ?>
          <?php else: ?>
              <option>No emails found.</option>
          <?php endif; ?>
        </select>
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--select__arrow" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 11L3 6 3.7 5.3 8 9.6 12.3 5.3 13 6z"></path>
        </svg>
      </div>
      <table id="my-coa-table" class="display w-100"></table>
    </div>
</div>

<style>
  .select2-selection__rendered {
    margin: unset !important;
  }

  .bx--modal-content p {
    font-weight: 600;
  }

  .bx--inline-notification {
    margin-top: 0;
  }
</style>
