
<style>
    .bx--radio-button-group {
      margin-top: 0;
    }
    .bx--fieldset {
      margin-bottom: 1rem;
    }
    .hidden {
      display: none !important;
    }
    .approve-btn, .deny-btn {
        width: 90%
    }
</style>

<div style="display: flex; justify-content: space-between; width:90vw;">
  <div style="display:flex; width: 30%;">
      <div class="d-flex flex-row">
          <button id="save-override-button" class="bx--btn bx--btn--primary mr-2 d-none" type="button" onclick="openConfirmationModal('save', <?= $scenario_id ?>, <?= $coa_table_id; ?>)">
          Save
          </button>
          <button id="submit-coa"
          class="bx--btn bx--btn--primary d-none" type="button" onclick="openConfirmationModal('submit', <?= $scenario_id ?>)">
          Submit for SOCOM Review
          </button>
      </div>
      <div class="d-flex flex-row">
          <button id="approve-coa"
          class="bx--btn bx--btn--primary mr-2 d-none approve-btn" type="button" onclick="openConfirmationModal('approve', <?= $scenario_id ?>)">
              Approve
          </button>
          <button id="deny-coa"
          class="bx--btn bx--btn--danger d-none deny-btn" type="button" onclick="openConfirmationModal('deny', <?= $scenario_id ?>)">
              Deny
          </button>
      </div>
      <button class="bx--tag bx--tag--green d-none" id="approved-tag-">
        <span class="bx--tag__label">Approved</span>
      </button>
      <button class="bx--tag bx--tag--red d-none" id="denied-tag-">
        <span class="bx--tag__label">Denied</span>
      </button>
  </div>
  <div style="display: flex">
      <div class="bx--form-item d-none" id="original-output-wrapper">
          <input class="bx--toggle-input" id="original-output" type="checkbox" onclick="toggleOriginalOutputTable()">
          <label class="bx--toggle-input__label" for="original-output">
              Original Outputs
              <span class="bx--toggle__switch">
              <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
              <span class="bx--toggle__text--on" aria-hidden="true">On</span>
              </span>
          </label>
      </div>
      <div style="padding-left:2rem;">
          <div class="bx--form-item" id="manual-override-wrapper">
              <input class="bx--toggle-input" id="manual-override" type="checkbox"
              onclick="openConfirmationModal('toggle',  <?= $scenario_id ?>, <?= $coa_table_id ?>)">
              <label class="bx--toggle-input__label" for="manual-override">
              Manual Override
              <span class="bx--toggle__switch">
                  <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                  <span class="bx--toggle__text--on" aria-hidden="true">On</span>
              </span>
              </label>
          </div>
      </div>
  </div>

      
</div>

<div id="approved-banner" class="d-none baseline-banner-style green-background mt-2">Approved</div>
<div id="denied-banner" class="d-none baseline-banner-style red-background mt-2">Denied</div>
<div id="override-accordion-wrapper" class="hidden" style="padding-top: 1rem">
  <ul data-accordion class="bx--accordion">
    <li id='override-accordion' data-accordion-item class="bx--accordion__item">
      <button class="bx--accordion__heading" aria-expanded="true" aria-controls="override-accordion-panel">
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--accordion__arrow" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M11 8L6 13 5.3 12.3 9.6 8 5.3 3.7 6 3z"></path></svg>
        <div class="bx--accordion__title">Entries Manual Override</div>
      </button>
      <div id="override-accordion-panel" class="bx--accordion__content" style="padding-right: 1rem">
        <fieldset class="bx--fieldset">
        <div class="bx--form-item">
          <div class="bx--form-item bx--text-area-wrapper w-100" style="flex-direction: row; align-items: center;">
              <label for="justification-text-input" style="padding-right: 10px;">Justification:</label>
              <div class="bx--text-area__field-wrapper w-100">
                <textarea id="justification-text-input" type="text" class="bx--text-area bx--text-area--light" placeholder=""
                 rows="5"
                ><?= isset($override_form['justification']) &&  is_string($override_form['justification']) ? $override_form['justification'] : '';  ?></textarea>
              </div>
            </div>
        </div>
        </fieldset>
      </div>
    </li>
  </ul>
</div>

<div class="bx--tab-content" style="width:90vw;overflow-y:auto">
  <div id="tab-panel-1-container" class="tab-1-container tab-containers" role="tabpanel" aria-labelledby="tab-link-1-container"
    aria-hidden="false" >
    <div id="budget-uncommitted-container" class="d-flex">
        <div class="w-100">
          <div id="override-table-legend-container" class="d-flex flex-column">
            <div id="override-table-legend">
              <h4>Legend</h4>
              <div class="legend-status-color-row d-flex">
                <div class="legend-col">
                  <p class="legend-title mb-1">Color</p>
                  <div class="pl-1 legend-color green legend-row"></div>
                  <div class="pl-1 legend-color red legend-row"></div>
                  <div class="pl-1 legend-color yellow legend-row"></div>
                  <div class="pl-1 legend-color blue legend-row"></div>
                </div>
                <div class="legend-col">
                    <p class="legend-title">Status</p>
                    <p class="legend-row legend-text">Valid</p>
                    <p class="legend-row legend-text">Invalid</p>
                    <p class="legend-row legend-text">Edited</p>
                    <p class="legend-row legend-text">Partial</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php $this->load->view('SOCOM/optimizer/budget_uncommitted_table_view')?>
    </div>
    <div id="coa-output-table">
      <?php $this->load->view('SOCOM/optimizer/output_override_table_view')?>
    </div>
  </div>
</div>

<script>
  // document.getElementById("download-coa-results").onclick = function(){
  //   let type = $("#coa-data-types > li.bx--tabs__nav-item--selected > a").text().toLowerCase();
  //   // export_coa_results();
  // }
    SCENARIO_STATE[<?= $scenario_id ?>] = '<?= $state; ?>';
    var scenario_id = <?= $scenario_id ?>;
    var type_of_coa = '<?= $type_of_coa ?>';
    CarbonComponents.Accordion.init();
    $('#coa-insert-row-btn').on('click', function (e) {
        $('#insert-coa-row-btn').attr('disabled', true);
        $('#coa-output').removeClass('is-visible');
        let eoc_col = $(this).attr('data-eoc');
        insertCoaTableRow(scenario_id, type_of_coa);
    });
</script>


