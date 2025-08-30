<style>
  .handsontable th {
    white-space: pre-line;
    word-wrap: break-word;
    max-width: 30px;
  }
  
  .score-weight-class {
    border-style: solid;
    border-width: 0px 1px 1px 1px;
    border-color: #cccccc;
    background-color: #f0f0f0;
  }

  .weighted-score-class {
    border-style: solid;
    border-width: 0px 1px 1px 0px;
    border-color: #cccccc;
    background-color: #f0f0f0;
  }

  #error-weight {
    margin-bottom: 0;
  }

</style>
<!-- Save notification div -->
<div id="weight-notifications" class="d-flex flex-row mb-3">
  <?php
    $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'success-weights',
      'type' => 'success',
      'dnone' => true,
      'bx_notification_title' => 'Weight',
      'bx_notification_subtitle' => 'Success',
      'close' => false
    ]
  );
  ?>

  <?php 
  $this->load->view('templates/carbon/inline_notification',
    [
      'notification_id' => 'error-weights',
      'type' => 'error',
      'dnone' => true,
      'bx_notification_title' => 'Option Weight',
      'bx_notification_subtitle' => 'Error',
      'close' => false
    ]
  );
  ?>
</div>
<nav data-tabs class="bx--tabs" role="navigation">
  <div class="bx--tabs-trigger" tabindex="0">
    <a href="javascript:void(0)" class="bx--tabs-trigger-text" tabindex="-1"></a>
      <svg class="bx--dropdown__arrow" width="10" height="5" viewBox="0 0 10 5" fill-rule="evenodd">
        <path d="M10 0L5 5 0 0z"></path>
      </svg>
  </div>
  <ul class="bx--tabs__nav bx--tabs__nav--hidden" role="tablist">
        <li class="bx--tabs__nav-item bx--tabs__nav-item--selected " data-target=".tab-1" role="presentation">
          <a tabindex="0" id="tab-link-1" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="weight-guidance-tab" aria-selected="true">Guidance Weight</a>
        </li>
        <li class="bx--tabs__nav-item" data-target=".tab-2" role="presentation">
          <a tabindex="0" id="tab-link-2" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="weight-pom-tab">POM Weight</a>
        </li>
  </ul>
</nav>
<div style="padding: 1rem;">
  <div id="weight-guidance-tab" class="tab-1" role="tabpanel" aria-labelledby="tab-link-1" aria-hidden="false">
    <div id="weight-guidance-div" style="height:280px; overflow:hidden;"></div>
    <input id="hidden_weight_id" type="hidden"/>
    <div class="d-flex flex-row-reverse">
      <div class="d-flex flex-row-reverse score-weight-class" style="height:25px; width: 102px; line-height:30px; font-size:12px;">
        <span class="pr-1" id="weight-guidance-sum-text"></span>
      </div>
    </div>
  </div>

  <div id="weight-pom-tab"  class="tab-2" role="tabpanel" aria-labelledby="tab-link-2" aria-hidden="true" hidden>
    <div id="weight-pom-div" style="height:280px; overflow:hidden;"></div>
    <input id="hidden_weight_id" type="hidden"/>
    <div class="d-flex flex-row-reverse">
      <div class="d-flex flex-row-reverse score-weight-class" style="height:25px; width: 102px; line-height:30px; font-size:12px;">
        <span class="pr-1" id="weight-pom-sum-text"></span>
      </div>
    </div>
  </div>
</div>

