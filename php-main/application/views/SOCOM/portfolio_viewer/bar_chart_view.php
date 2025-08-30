<div id="bar-chart-view" class="d-flex flex-column align-items-center">

    <div class="chart-column full-width mb-3">
        <div class="chart-header">Top 10 Program Groups</div>
        <div id="bar-chart-top-program" class="w-100 p-2"></div>
    </div>
    <div class="chart-column full-width mb-3 px-3 py-3">
        <?php $this->load->view(
            'SOCOM/portfolio_viewer/dropdown_filter_view',
            array(
                'options' => $filtered_program_groups,
                'title' => 'Program Group',
                'view_type' => 'budget-trend-overview',
                'default_select_all' => true,
                'select_all_button' => true,
                'class' => '',
                'width' => '100%'
            )
        ); ?>

        <!-- Charts inside dropdown card -->
        <div class="bar-chart-wrapper mt-3">
            <div class="chart-column">
                <div class="chart-header">Selected Program Groups</div>
                <div id="<?= $tab_type; ?>-selected-program-chart" class="w-100 p-2"></div>
            </div>

            <div class="chart-column">
                <div class="chart-header">Amount By Appropriation</div>
                <div id="bar-chart-budget-authority" class="w-100 p-2"></div>
            </div>
        </div>
    </div>


</div>


<style>
    .bar-chart-wrapper {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        width: 100%;
        flex-wrap: wrap;
    }

    .chart-column {
        flex: 1;
        min-width: 300px;
        border-radius: 12px;
        background-color: #fff;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.05);
    }


    .chart-column.full-width {
        width: 100%;
    }


    @media (max-width: 768px) {
        .bar-chart-wrapper {
            flex-direction: column;
        }
    }

    .chart-header {
        font-size: 1rem;
        font-weight: 500;
        color: #161616;
        white-space: nowrap;
        margin-bottom: 1rem;
        /* background: linear-gradient(to right, rgb(246, 246, 246), #ffffff); */
        background-color: rgb(246, 246, 246);
        padding: 2rem;
        border-radius: 12px 12px 0 0;
        box-sizing: border-box;
    }
</style>