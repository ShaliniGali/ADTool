<!-- Save notification div -->
<div id="sharing-coa-notifications" class="d-flex flex-row mb-3">
  <?php
    $this->load->view('templates/carbon/inline_notification',
      [
        'notification_id' => 'success-merging-coa',
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
      'notification_id' => 'error-merging-coa',
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
            'notification_id' => 'merging_coa_form_instructions',
            'class' => 'bx--inline-notification--info',
            'file_upload_class' => 'd-flex flex-column',
            'bx_notification_title' => 'Load COA Instructions:',
            'bx_notification_subtitle' => 
              '<ul class="bx--list--unordered mt-2">
                <li class="bx--list__item">Select two COA(s) to be merged</li>
                <li class="bx--list__item">Review and modify the proposed budget</li>
                <li class="bx--list__item">Click Load COA</li>
              </ul>',
            'close' => false
        ]);


    ?>
    <div class="bx--form-item w-100 mb-4">
      <table id="show-coa-table" class="display w-100"></table>
    </div>
</div>

<div id="proposed-budget-input" class="hidden">
</div>

<style>
    .select2-selection__rendered {
        margin: unset !important;
    }
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    /* Firefox */
    input[type=number] {
    -moz-appearance: textfield;
    }

    .hidden {
      display: none !important;
    }
</style>
