<style>
    #budget-trend-chart-container {
        display: flex;
        flex-direction: column;
        background: none;
        border-radius: 12px;
        width: 100%;
        padding: 12px;
    }

    .card-container {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        width: 100%;
    }
</style>

<div id="budget-trend-overview-container" class="d-flex flex-row w-100">
    <div class="filter-container">
        <?php $this->load->view('SOCOM/portfolio_viewer/components/common/filter_view.php', array(
            'tab_type' => 'budget-trend-overview'
        )); ?>
    </div>
    <div id="budget-trend-chart-container">
        <div class="card-container mb-3">
            <?php $this->load->view('SOCOM/portfolio_viewer/line_plot_view.php', ['type' => 'budget-trend-overview']); ?>
        </div>
        <?php $this->load->view('SOCOM/portfolio_viewer/bar_chart_view.php', array()); ?>
    </div>

</div>