<style>
  .card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(9, 30, 66, 0.1);
    border: 1px solid #DFE1E6;
    margin-bottom: 16px;
    overflow: hidden;
  }

  .tabs {
    display: flex;
    border-bottom: 1px solid #dfe1e6;
    margin-bottom: 24px;
  }

  .tabs__button {
    background: none;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    color: #5a6872;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    transition: color 0.2s, border-color 0.2s;
  }

  .tabs__button.is-active {
    color: #0052CC;
    border-bottom-color: #0052CC;
    background-color: rgb(223, 235, 255);
  }

  .tabs__button:focus {
    outline: none;
    border: none;
    border-bottom: 3px solid #0052CC;
  }


  .tab-content {
    display: none;
  }

  .tab-content.is-active {
    display: block;
  }

  #program-execution-drilldown-funding-chart,
  #program-execution-drilldown-ams-graph {
    width: 100%;
    min-height: 300px;
  }

  #program-execution-drilldown-funding-chart #program-execution-drilldown-container,
  #program-execution-drilldown-line-plot-view #program-execution-drilldown-container {
    background: transparent !important;
    padding: 0 !important;
  }

  .dropdown-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 24px;
  }

  .deselect-all-button {
    margin-left: 16px;
  }

  .bottom-row {
    display: flex;
    gap: 24px;
    align-items: stretch;
  }

  .bottom-row>.flex-chart {
    flex: 3;
  }

  .bottom-row>.flex-meta {
    flex: 1;
  }

  @media (max-width: 768px) {
    .bottom-row {
      flex-direction: column;
    }
  }

  #program-execution-drilldown-header {
    display: none !important;
  }

  .card__body {
    padding: 16px 20px;
  }

  .card__header {
    padding: 16px 20px;
    border-bottom: 1px solid #DFE1E6;
    font-weight: 600;
    font-size: 1rem;
    color: #172B4D;
    background-color: #F4F5F7;
  }

  .card__footer {
    padding: 16px 20px;
    border-top: 1px solid #DFE1E6;
    background-color: #F4F5F7;
    text-align: right;
  }

  .flex-chart,
  .flex-meta {
    display: flex;
    flex-direction: column;
  }

  .flex-chart .card,
  .flex-meta .card {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  #program-execution-no-data {
    min-height: 55vh;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }

  .flex-meta {
    line-height: 1.6;
    font-size: 0.95rem;
  }

  .flex-meta .bx--structured-list-row {
    margin-bottom: 12px;
    padding: 12px 16px;
    background-color: #f9f9f9;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
  }

  .flex-meta .bx--structured-list-row:last-child {
    margin-bottom: 0;
  }

  .flex-meta .bx--structured-list-tbody {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .flex-meta .bx--structured-list-thead {
    padding-bottom: 12px;
    font-weight: 600;
    font-size: 1rem;
    color: #4a4a4a;
  }
</style>
<?php $this->load->view('SOCOM/portfolio_viewer/components/modals/fielding_quantities_graph_modal_view.php'); ?>
<?php $this->load->view('SOCOM/portfolio_viewer/components/modals/milestones_requirements_modal_view.php'); ?>
<?php $this->load->view('SOCOM/portfolio_viewer/components/modals/ams_data_modal_view.php'); ?>

<div id="program-execution-drilldown-container" class="p-3">
  <div class="card">
    <div class="card__body">
      <?php
      $this->load->view('SOCOM/portfolio_viewer/components/common/program_group_dropdown_view.php', [
        'program_groups'   => $program_groups,
        'container_class'  => '',
        'container_style'  => '',
        'title' => 'Program Group'
      ]);
      ?></div>
  </div>

  <div id="program-execution-drilldown-no-data">
    <div class="card-style" id="program-execution-no-data">
      <div class="d-flex justify-content-center w-100 p-2">
        <h2>Select program group to see data</h2>
      </div>
    </div>
  </div>

  <div id="program-execution-drilldown-card" class="card d-none">
    <div class="card__body">

      <!-- Sub-tabs -->
      <div class="tabs" role="tablist">
        <button
          class="tabs__button  is-active"
          onclick="switchTab(event,'funding')">
          Funding
        </button>
        <button
          class="tabs__button"
          onclick="switchTab(event,'milestones')">
          Milestones
        </button>
        <button
          class="tabs__button"
          onclick="switchTab(event,'fielding')">
          Fielding
        </button>
      </div>

      <?php $this->load->view('SOCOM/portfolio_viewer/components/program_execution_drilldown/funding_view.php'); ?>

      <div id="program-execution-drilldown-milestones-container" class="tab-content"></div>

      <div id="program-execution-drilldown-fielding-container" class="tab-content ">
        <div class="p-3">
          <?php $this->load->view(
            'SOCOM/portfolio_viewer/components/program_execution_drilldown/fielding_table_view.php'
          ); ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  function onProgramGroupChange() {
    const val =
      $('#program-execution-drilldown-program-group-dropdown').val();
    $('#program-execution-drilldown-no-data').toggle(!val);
    $('#program-execution-drilldown-card').toggleClass('d-none', !val);
    programGroupDropdownOnchange('program-execution-drilldown');
  }


  function switchTab(evt, tab) {
    $('.tabs__button').removeClass('is-active');
    $('.tab-content').removeClass('is-active');
    $(evt.currentTarget).addClass('is-active');
    $(`#program-execution-drilldown-${tab}-container`).addClass('is-active');
  }


  $('#program-execution-drilldown-program-group-dropdown').select2({
    placeholder: "Select an option",
    width: '17vw'
  });
</script>