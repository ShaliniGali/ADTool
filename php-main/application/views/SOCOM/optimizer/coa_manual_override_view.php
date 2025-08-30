<div data-modal id="coa-output" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="coa-output-label" aria-describedby="coa-output-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 95%;">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="coa-output-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="coa-output-heading">COA Manual Override</p>
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
          class="bx--modal-close__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
          <path d="M12 4.7L11.3 4 8 7.3 4.7 4 4 4.7 7.3 8 4 11.3 4.7 12 8 8.7 11.3 12 12 11.3 8.7 8z">

          </path>
        </svg>
      </button>
    </div>

    <div class="bx--modal-content ml-auto mr-auto overflow-auto h-100 w-100" id="coa-output-container">
    <div data-loading class="bx--loading">
        <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
            <title>Loading</title>
            <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
        </svg>
    </div>
    </div>

    <div class="bx--modal-footer">
        <button id="close-coa-results" class="bx--btn bx--btn--secondary"
            type="button" data-modal-close>Close</button>
        <button id="download-coa-results" tabindex="0" class="bx--btn bx--btn--primary load_button" type="button">
            Download Results <div class="margin-right:8em;"> <i class="fas fa-file-excel fa-2x"></i></div>
        </button>
    </div>
  </div>
  <span tabindex="0"></span>
</div>



<div data-modal id="manual-override-confirm-coa" class="bx--modal" role="dialog"
  aria-modal="true" aria-labelledby="manual-override-confirm-coa-label"
  aria-describedby="manual-override-confirm-coa-heading" tabindex="-1">
  <span tabindex="0" role="link" class="bx--visually-hidden">Focus sentinel</span>
  <div role="dialog" class="bx--modal-container bx--modal-container--sm"
  aria-label="Label" aria-modal="true" tabindex="-1">
    <div class="bx--modal-header">
      <h3 id="manual-override-confirm-coa-heading"
      class="bx--modal-header__heading">COA Manual Override Confirmation</h3>
      <button id="manual-override-confirm-coa-close-btn"
      class="bx--modal-close" type="button" data-modal-close aria-label="close">
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg"
        fill="currentColor" aria-hidden="true" width="20" height="20" viewBox="0 0 32 32"
        class="bx--modal-close__icon">
          <path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z">

          </path>
        </svg>
      </button>
    </div>
    <div id="manual-override-confirm-coa-body" class="bx--modal-content"
    aria-labelledby="manual-override-confirm-heading">
      Are you sure you would like to <span id="manual-override-action">do this?</span>
    </div>
    <div class="bx--modal-footer bx--btn-set">
      <button id="manual-override-cancel-btn" tabindex="0"
      class="bx--btn bx--btn--secondary" type="button" data-modal-close>Cancel</button>
      <button id="manual-override-confirm-btn" tabindex="0"
      class="bx--btn bx--btn--primary" type="button" data-modal-primary-focus>Continue</button>
    </div>
  </div>
  <span tabindex="0" role="link" class="bx--visually-hidden"></span>
</div>

<script>
    $('#download-coa-results').on('click', function() {
        export_coa_results();
    })
</script>