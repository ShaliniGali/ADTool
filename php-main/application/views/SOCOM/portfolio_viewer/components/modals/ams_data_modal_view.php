<div data-modal id="program-execution-drilldown-ams-data" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="program-execution-drilldown-ams-data-label" aria-describedby="program-execution-drilldown-ams-data-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 97%; border-radius:10px">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="program-execution-drilldown-ams-data-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="program-execution-drilldown-ams-data-heading">AMS Data</p>
      </div>
      <?php $this->load->view('SOCOM/optimizer/notification_success_view',array(
            "message"=>"Saved Successfully",
            "class"=>"d-none",
            "id"=>"state-session-notification",
            "custom_close"=>"closeNotification('state-session-notification')"
        )); ?>
      <button class="bx--modal-close" type="button"  aria-label="close modal" data-modal-close>
        <svg focusable="false" preserveAspectRatio="xMidYMid meet"
        style="will-change: transform;" xmlns="http://www.w3.org/2000/svg"
          class="bx--modal-close__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" aria-modal="true">
          <path d="M12 4.7L11.3 4 8 7.3 4.7 4 4 4.7 7.3 8 4 11.3 4.7 12 8 8.7 11.3 12 12 11.3 8.7 8z">

          </path>
        </svg>
      </button>
    </div>

    <div class="bx--modal-content ml-auto mr-auto overflow-auto h-100 w-100" style="
    overflow-x: hidden !important;" id="program-execution-drilldown-ams-modal-data-container">
    <div data-loading class="bx--loading">
        <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
            <title>Loading</title>
            <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
        </svg>
    </div>
    </div>

    <div class="bx--modal-footer">
        <button id="close-program-execution-drilldown-ams-data-results" class="bx--btn bx--btn--secondary"
            type="button" data-modal-close>Close</button>
    </div>
  </div>
  <span tabindex="0"></span>
</div>

<script>
    // $('#download-program-execution-drilldown-ams-data-results').on('click', function() {
    //     console.log('click download detail')
    // })
</script>

<style>
    .dataTables_wrapper {
        font-weight: normal;
    }

</style>