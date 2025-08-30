<div id="bar-chart-view" class="d-flex flex-column align-items-center justify-content-around">
    

    <div class="w-100 d-flex justify-content-end">
        <?php $this->load->view('SOCOM/portfolio/dropdown_filter_view',
        array(
            'options' => $filtered_program_groups,
            'title' => 'Program Group',
            'view_type' => 'budget-trend-overview',
            'default_select_all' => true,
            'select_all_button' => true
        )
        );?>
    </div>

    <div class="d-flex flex-row w-100">
        
        <div class="d-flex flex-column align-items-center w-100">
            <!-- Top 10 Program Groups -->
            <div id="bar-chart-top-program" class=" w-100">
            </div>
        </div>
        <div class="d-flex flex-column align-items-center w-100">
            <!-- Selected Program Groups -->
            <div id="<?= $tab_type; ?>-selected-program-chart" class=" w-100" >
            </div>
        </div>
        <div class="d-flex flex-column align-items-center w-100">
        <!-- Amount by Budget Authority -->
            <div id="bar-chart-budget-authority" class=" w-100">
            </div>
        </div>
    </div>
</div>