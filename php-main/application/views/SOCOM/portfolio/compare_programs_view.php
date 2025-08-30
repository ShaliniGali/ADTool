<style>
   #compare-programs-container {
      /* display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-template-rows: repeat(auto, 1fr); */
      gap: 20px;
      background: white;
      border-radius: 12px;
      padding: 12px;
   }

    #compare-programs-table-container .dataTables_wrapper {
        width: 100%;
        margin: 0 auto;
    }

    #compare-programs-table-container table.dataTable {
        width: 100% !important;
    }
</style>

<div id="compare-programs-container" class="p-3">
    <div class="mb-5 d-flex flex-rows align-items-center">
        <?php
                $this->load->view('SOCOM/portfolio/dropdown_filter_view',
                array(
                    'options' => $program_groups,
                    'title' => 'Program Group',
                    'view_type' => 'compare-programs',
                    'default_select_all' => false,
                    'select_all_button' => false
                ));
            ?>

        <div id="appn-dropdown-container" class="d-none">
            <div class="d-flex align-items-center mb-1 bx--label medium-label">
                APPN
            </div>
            <select id="APPN" class="select2"></select>
        </div>

        <div class="bx--form-item" id="compare-programs-common-range-toggle-container" hidden>
            <input class="bx--toggle-input" id="compare-programs-common-range-toggle" type="checkbox" onchange="common_range_toggle()">
            <label class="bx--toggle-input__label" for="compare-programs-common-range-toggle">
                Make common y-axis range
                <span class="bx--toggle__switch">
                <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                <span class="bx--toggle__text--on" aria-hidden="true">On</span>
                </span>
            </label>
        </div>
    </div>

    <div id="compare-programs-no-data" class="d-flex justify-content-center w-100 p-2"> <h2>Select program group to see data</h2> </div>

    <div id="compare-programs-toggle-pb-lines-container" class="bx--form-item ml-4 pb-4 d-none">
        <input class="bx--toggle-input" id="compare-programs-toggle-pb-lines" type="checkbox" checked>
            <label class="bx--toggle-input__label" for="compare-programs-toggle-pb-lines"> PB Lines
            <span class="bx--toggle__switch">
                <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                <span class="bx--toggle__text--on" aria-hidden="true">On</span>
            </span>
        </label>
    </div>
    <div class="d-flex flex-row w-100">
        <div id="execution-data-plot-1-container" class="execution-data-plot-container w-50">
            <div id="execution-data-plot-1" class="execution-data-plot"></div>
        </div>
        <div id="execution-data-plot-2-container" class="execution-data-plot-container w-50">
            <div id="execution-data-plot-2" class="execution-data-plot"></div>
        </div>
    </div>
   <div class="d-flex flex-row w-100">
        <div id="compare-programs-selected-program-chart-container" class="d-flex flex-row justify-content-center align-items-center w-50">
            <div class="w-100" id="compare-programs-selected-program-chart"></div>
        </div>
        <div class="d-flex flex-column align-items-center w-50" id="compare-programs-table-container">
            <div id="compare-programs-table-dropdown-container" class="w-100 d-flex flex-row mb-2" >
                <div id="fy-dropdown-container" class="d-none">
                    <div class="d-flex align-items-center mb-1 bx--label medium-label">
                        FY
                    </div>
                    <select id="FY" class="select2"></select>
                </div>
            </div>
            <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" id="compare-programs-table" class="" style="text-align:center;"></table>
        </div>
    </div>
</div>

<script>
    $('#APPN').on('change', function () {
        applyAPPNFilter();
    });
</script>