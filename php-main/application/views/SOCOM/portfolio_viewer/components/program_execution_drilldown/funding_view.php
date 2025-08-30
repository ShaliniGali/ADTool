<style>

  .metadata-card .card__body {
    background: rgb(255, 255, 255);
    max-height: 400px;
    overflow-y: auto;
  }

  .metadata-section+.metadata-section {
    margin-top: 24px;
    border-top: 1px solid #dfe1e6;
    padding-top: 24px;
  }

  .metadata-section__title {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #172B4D;
    margin-bottom: 12px;
  }

  .metadata-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .metadata-item {
    padding: 8px 0;
    border-bottom: 1px solid #dfe1e6;
  }

  .metadata-item:last-child {
    border-bottom: none;
  }

  .metadata-item-label {
    font-weight: 600;
    color: #172B4D;
  }

  .metadata-item-content {
    margin-top: 4px;
    color: #42526E;
    line-height: 1.4;
  }

  
</style>
<div id="program-execution-drilldown-funding-container" class="tab-content is-active">
  <div class="card">
    <div class="card__body">
      <div id="program-execution-drilldown-funding-chart">
        <?php $this->load->view(
          'SOCOM/portfolio_viewer/line_plot_view.php',
          ['type' => 'program-execution-drilldown']
        ); ?>
      </div>
    </div>
  </div>

  <div class="dropdown-card">
    <?php $this->load->view(
      'SOCOM/portfolio_viewer/dropdown_filter_view',
      [
        'options'            => $resource_categories,
        'title'              => 'Resource Category',
        'view_type'          => 'funding-resource-category',
        'default_select_all' => false,
        'select_all_button'  => true,
        'width' => "100%"
      ]
    ); ?>
  </div>

  <div class="bottom-row">
    <div class="flex-chart">
      <div class="card">
        <div class="card__body">
          <div id="program-execution-drilldown-ams-graph"></div>
        </div>
      </div>
    </div>

    <section class="flex-meta card p-4">
      <div id="program-execution-drilldown-ams-data-container" class="bx--structured-list-tbody">
      </div>
    </section>
  </div>
</div>