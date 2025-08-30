<style>


.green-cell{
    background-color: rgb(126, 171, 85) !important;
    color: rgb(255, 255, 255) !important;
    font-weight: bold;
}

.red-cell{
    background-color: rgb(230, 72, 72) !important;
    color: rgb(255, 255, 255) !important;
    font-weight: bold;
}

.ember-cell{
    background: #FFFF00 !important;
    color: black !important;
    font-weight: bold;
}

 #milestones-requirements-table thead th {
        padding-left: 0.6rem;
        background-color: rgb(235, 235, 235);
        border-bottom: 1px solid rgb(235, 235, 235);
        border-right: 1px solid rgb(235, 235, 235);
        border-left: 1px solid rgb(235, 235, 235);
    }

    #milestones-requirements-table td {
        padding-left: 0.6rem;
        background-color: rgb(255, 255, 255);
    }

    #milestones-requirements-table {
        border: 1px solid #ffffff;
    }
</style>

<div data-modal id="program-execution-drilldown-milestones-requirements" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="program-execution-drilldown-milestones-requirements-label" aria-describedby="program-execution-drilldown-milestones-requirements-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 97%; border-radius:10px">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="program-execution-drilldown-milestones-requirements-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="program-execution-drilldown-milestones-requirements-heading">Milestone Requirements</p>
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
    overflow-x: hidden !important; padding: 24px !important;" id="program-execution-drilldown-milestones-requirements-container">

    <div class="d-flex flex-column align-items-center"> 
        <h3 id="milestones-requirements-table-header"></h3> 
    </div>

        <table class="display dataTable cell-border table-style w-100 bx--data-table" id="milestones-requirements-table" class="" style="text-align:center;"></table>
    </div>
  </div>
  <span tabindex="0"></span>
</div>