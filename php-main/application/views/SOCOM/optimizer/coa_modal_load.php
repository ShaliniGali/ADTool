<div id="coa-notifications" class="d-flex flex-row mb-3">
  <?php
  $this->load->view(
    'templates/carbon/inline_notification',
    [
      'notification_id' => 'success-coa-load',
      'type' => 'success',
      'dnone' => true,
      'bx_notification_title' => 'Load COA',
      'bx_notification_subtitle' => 'Success',
      'close' => false
    ]
  );
  ?>

  <?php
  $this->load->view(
    'templates/carbon/inline_notification',
    [
      'notification_id' => 'error-coa-load',
      'type' => 'error',
      'dnone' => true,
      'bx_notification_title' => 'Load COA',
      'bx_notification_subtitle' => 'Error',
      'close' => false
    ]
  );
  ?>
</div>

<div id="coa_list_outer_box" class="d-flex flex-wrap mt-3 pt-3">
  <table id="coa-load-table" class="display" style="width:100%">
    <thead>
      <tr>
        <th>Priority</th>
        <th>COA Name</th>
        <th>COA Description</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>