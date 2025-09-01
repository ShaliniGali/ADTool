<style>
  #program-execution-drilldown-fielding-quantities {
    z-index: 20;
  }

  .bx--content-switcher {
    width: 100% !important;
  }

  .bx--content-switcher-btn {
    flex: 1 1 0;
    width: 100%;
    color: #000000 !important;
    border: none !important;
    border-bottom: 3px solid transparent !important;
    background: none !important;
    transition: color 0.2s, border-color 0.2s !important;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 0;
    font-size: 14px;
    text-align: center;
  }

  .bx--content-switcher-btn:hover {
    color: #0052CC !important;
  }

  .bx--content-switcher-btn:focus {
    outline: none !important;
    box-shadow: none !important;
    border-bottom: 3px solid #0052CC !important;
  }

  .bx--content-switcher--selected {
    color: #0052CC !important;
    border-bottom: 3px solid #0052CC !important;
    background-color: rgb(223, 235, 255) !important;
    font-weight: 600;
  }

  .bx--content-switcher__label {
    font-size: 14px;
  }

  .bx--content-switcher-btn::after {
    background-color: #f4f4f4 !important;
  }
</style>



<div data-modal id="program-execution-drilldown-fielding-quantities" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="program-execution-drilldown-fielding-quantities-label" aria-describedby="program-execution-drilldown-fielding-quantities-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 97%; border-radius:10px">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="program-execution-drilldown-fielding-quantities-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="program-execution-drilldown-fielding-quantities-heading">Fielding Quantities</p>
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
    overflow-x: hidden !important; padding: 24px !important;" id="program-execution-drilldown-fielding-quantities-container">

      <div class="d-flex flex-row w-100 mb-4">
        <div data-content-switcher class="bx--content-switcher historical-pom-switch" role="tablist" aria-label="Demo switch content">
          <button 
            id="fielding-quantities-cumulative-graph-switch"
            class="bx--content-switcher-btn"
            data-target="#fielding-quantities-cumulative-graph-container"
            role="tab">
            <span class="bx--content-switcher__label">Cumulative Fielding Qty</span>
          </button>
          <button
            id="fielding-quantities-planned-actual-graph-switch"
            class="bx--content-switcher-btn bx--content-switcher--selected"
            data-target="#fielding-quantities-planned-actual-graph-container"
            role="tab"
            aria-selected="true">
            <span class="bx--content-switcher__label">Actual vs Planned Qty</span>
          </button>
        </div>
      </div>

        <div id="fielding-quantities-planned-actual-graph-container" class="d-flex flex-row align-items-center justify-content-center">
            <?php $this->load->view('SOCOM/portfolio_viewer/components/common/line_plot_view.php', ['type' => 'fielding-quantities-planned-actual']);?>
        </div>
        <div id="fielding-quantities-cumulative-graph-container" class="d-flex flex-row align-items-center justify-content-center" hidden>
            <?php $this->load->view('SOCOM/portfolio_viewer/components/common/line_plot_view.php', ['type' => 'fielding-quantities-cumulative']);?>
        </div>
        <?php $this->load->view('SOCOM/portfolio_viewer/components/common/export_dropdown_view.php'); ?>
    </div>
  </div>
  <span tabindex="0"></span>
</div>
<script>
  const headerEl = document.getElementById("fielding-quantities-planned-actual-header");
  if (headerEl) {
    headerEl.remove();
  }
  const headerEl2 = document.getElementById("fielding-quantities-cumulative-header");
  if (headerEl2) {
    headerEl2.remove();
  }
</script>

