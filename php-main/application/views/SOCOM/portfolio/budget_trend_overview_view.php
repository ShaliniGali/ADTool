<style>
    #budget-trend-chart-container {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        width: 100%;
        padding: 12px;
    }
</style>

<div id="budget-trend-overview-container" class="d-flex flex-row w-100">
    <div class="filter-container">
        <?php $this->load->view('SOCOM/portfolio/filter_view.php',array(
            'tab_type'=>'budget-trend-overview'
        ));?>
    </div>
    <div id="budget-trend-chart-container">
        <div class="d-flex flex-row align-items-center justify-content-start">
            <?php $this->load->view('SOCOM/portfolio/line_plot_view.php', ['type' => 'budget-trend-overview']);?>
        </div>
        <?php $this->load->view('SOCOM/portfolio/bar_chart_view.php', array());?>
    </div>
</div>
