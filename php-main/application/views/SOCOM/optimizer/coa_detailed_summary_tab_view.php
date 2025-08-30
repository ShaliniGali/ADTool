<style>
  .dataTable thead > tr > th.sorting {
    padding-left: 10px;
  }

  .bx--tab-content .select2-selection__rendered  {
        margin: unset !important
    }

</style>

<div data-tabs class="bx--tabs bx--tabs--container">
  <div class="bx--tabs-trigger" tabindex="0">
    <a href="javascript:void(0)" class="bx--tabs-trigger-text" tabindex="-1"></a>
    <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" aria-modal="true"><path d="M8 11L3 6 3.7 5.3 8 9.6 12.3 5.3 13 6z"></path></svg>
  </div>
  <ul id="coa-detailed-tabs" class="bx--tabs__nav bx--tabs__nav--hidden" role="tablist">
    <li
      class="bx--tabs__nav-item bx--tabs__nav-item--selected"
      data-type="eoc-code"
      data-target=".tab-1-container" role="tab"  aria-selected="true"  >
      <a tabindex="0" id="tab-link-1-container" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab"
        aria-controls="tab-panel-1-container">EOC Codes/Resource Category</a>
    </li>
    <li
      class="bx--tabs__nav-item"
      data-type="jca-alignment"
      data-target=".tab-2-container" role="tab"  >
      <a tabindex="0" id="tab-link-2-container" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab"
        aria-controls="tab-panel-2-container">JCA Alignment</a>
    </li>
    <li
      class="bx--tabs__nav-item"
      data-type="kop-ksp"
      data-target=".tab-3-container" role="tab"  >
      <a tabindex="0" id="tab-link-3-container" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab"
        aria-controls="tab-panel-3-container">KOPs/KSPs</a>
    </li>
    <li
      class="bx--tabs__nav-item"
      data-type="capability-gaps"
      data-target=".tab-4-container" role="tab"  
      >
      <a tabindex="0" id="tab-link-4-container" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab"
        aria-controls="tab-panel-4-container">Capability Gaps</a>
    </li>
    <?php if ($is_iss_extract): ?>
        <li
        class="bx--tabs__nav-item"
        data-type="issue-analysis"
        data-target=".tab-5-container" role="tab"  
        >
        <a tabindex="0" id="tab-link-5-container" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab"
            aria-controls="tab-panel-5-container">Issue Analysis</a>
        </li>
    <?php endif; ?>
  </ul>
</div>
<div class="bx--tab-content">
  <div id="tab-panel-1-container" class="tab-1-container" role="tabpanel" aria-labelledby="tab-link-1-container"
    aria-hidden="false" >
    <?php if ($type === 'summary'): ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_summary_eoc_code_view',[
            'headers' => $detailed_summary_headers['eoc-code']
        ]); ?>
    <?php else: ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_comparison_eoc_code_view',[
            'headers' => $detailed_summary_headers['eoc-code']
        ]); ?>
    <?php endif; ?>
  </div>
  <div id="tab-panel-2-container" class="tab-2-container" role="tabpanel" aria-labelledby="tab-link-2-container"
    aria-hidden="true"  hidden>
    <?php if ($type === 'summary'): ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_summary_jca_alignment_view',[
            'headers' => $detailed_summary_headers['jca-alignment']
        ]); ?>
    <?php else: ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_comparison_jca_alignment_view',[
            'headers' => $detailed_summary_headers['jca-alignment']
        ]); ?>
    <?php endif; ?>
  </div>
  <div id="tab-panel-3-container" class="tab-3-container" role="tabpanel" aria-labelledby="tab-link-3-container"
    aria-hidden="true"  hidden>
    <?php if ($type === 'summary'): ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_summary_kop_ksp_view',[
            'headers' => $detailed_summary_headers['kop-ksp']
        ]); ?>
    <?php else: ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_comparison_kop_ksp_view',[
            'headers' => $detailed_summary_headers['kop-ksp']
        ]); ?>
    <?php endif; ?>
  </div>
  <div id="tab-panel-4-container" class="tab-4-container" role="tabpanel" aria-labelledby="tab-link-4-container"
    aria-hidden="true"  hidden>
    <?php if ($type === 'summary'): ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_summary_capability_gaps_view',[
            'headers' => $detailed_summary_headers['capability-gaps']
        ]); ?>
    <?php else: ?>
        <?php $this->load->view('SOCOM/optimizer/coa_detailed_comparison_capability_gaps_view',[
            'headers' => $detailed_summary_headers['capability-gaps']
        ]); ?>
    <?php endif; ?>
  </div>
  <?php if ($is_iss_extract): ?>
    <div id="tab-panel-5-container" class="tab-5-container" role="tabpanel" aria-labelledby="tab-link-5-container"
        aria-hidden="true"  hidden>
        <?php if ($type === 'summary'): ?>
            <?php $this->load->view('SOCOM/optimizer/coa_detailed_summary_issue_analysis_view',[
                'headers' => $detailed_summary_headers['issue-analysis']
            ]); ?>
        <?php else: ?>
            <?php $this->load->view('SOCOM/optimizer/coa_detailed_comparison_issue_analysis_view',[
                'headers' => $detailed_summary_headers['issue-analysis']
            ]); ?>
        <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<script>

  //todo: probably need to specificy a specifc class to not mess up with any nav items 
  $("#coa-detailed-tabs > .bx--tabs__nav-item").on('click', function(){

    if(!$(this).hasClass('bx--tabs__nav-item--disabled')){

        let prevTab = $("#coa-detailed-tabs > .bx--tabs__nav-item--selected");
        let prevContainer = $(prevTab).attr('data-target');
        $(`${prevContainer}`).attr('hidden', true);
        $("#coa-detailed-tabs > .bx--tabs__nav-item").removeClass('bx--tabs__nav-item--selected');
        $(this).addClass('bx--tabs__nav-item--selected');
        let targetContainer = $(this).attr('data-target');

        if (!$(this).hasClass('bx--tabs__nav-item--disabled')){
            let targetContainerType = $(this).attr('data-type');
            $(`${targetContainer}`).attr('hidden', false);
            if ('<?= $type; ?>' === 'summary') {
                show_coa_detailed_summary_content(<?= $scenario_id;?>, <?= $table_id;?>, targetContainerType);
            }
            else {
                show_coa_detailed_comparison_content(targetContainerType);
            }
        }
    }
  });

</script>


