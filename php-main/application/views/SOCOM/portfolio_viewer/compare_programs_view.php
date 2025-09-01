<style>
    #compare-programs-container {
        gap: 20px;
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

    .card-style {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    #compare-programs-table thead th {
        padding-left: 0.6rem;
        background-color: rgb(235, 235, 235);
        border-bottom: 1px solid rgb(235, 235, 235);
        border-right: 1px solid rgb(235, 235, 235);
        border-left: 1px solid rgb(235, 235, 235);
    }

    #compare-programs-table td {
        padding-left: 0.6rem;
        background-color: rgb(255, 255, 255);
    }

    #compare-programs-table {
        border: 1px solid #ffffff;
    }


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
        background-color: rgb(246, 246, 246);
        padding: 2rem;
        border-radius: 12px 12px 0 0;
        box-sizing: border-box;
    }

    .select2-selection__rendered {
        margin: unset !important;
    }

    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 12px;
        min-height: 38px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border-radius: 4px;
        background-color: #e9f5ff;
        border: 1px solid #b6dfff;
        margin-top: 4px;
        margin-right: 4px;
        font-size: 0.875rem;
        color: #004080;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        margin-right: 4px;
        color: #004080;
    }

    #compare-programs-no-data {
        min-height: 55vh;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
</style>

<div id="compare-programs-container h-90" class="p-3">

    <div class="card-style mb-4">
        <div class="mb-3 d-flex flex-rows align-items-center flex-wrap gap-3">
            <?php
            $this->load->view(
                'SOCOM/portfolio_viewer/components/common/dropdown_filter_view',
                array(
                    'options' => $program_groups,
                    'title' => 'Program Group',
                    'view_type' => 'compare-programs',
                    'default_select_all' => false,
                    'select_all_button' => false,
                    'id' => 'program-group-select',
                    'width' => '17vw'
                )
            );
            ?>

            <div id="appn-dropdown-container" class="d-none  mr-2" style="width: 17vw;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center bx--label medium-label">APPN</div>
                </div>
                <select id="APPN" class="selection-dropdown select2" multiple="multiple" onchange="applyAPPNFilter()"></select>
            </div>


            <div class="d-flex align-items-center gap-4" id="compare-programs-common-toggle-wrapper" style="flex-wrap: wrap;">
                <div class="bx--form-item ml-4" id="compare-programs-common-range-toggle-container" hidden>
                    <input class="bx--toggle-input" id="compare-programs-common-range-toggle" type="checkbox" onchange="common_range_toggle()">
                    <label class="bx--toggle-input__label" for="compare-programs-common-range-toggle">
                        Make common y-axis range
                        <span class="bx--toggle__switch">
                            <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                            <span class="bx--toggle__text--on" aria-hidden="true">On</span>
                        </span>
                    </label>
                </div>

                <div class="bx--form-item ml-4" id="compare-programs-toggle-pb-lines-container">
                    <input class="bx--toggle-input" id="compare-programs-toggle-pb-lines" type="checkbox" checked>
                    <label class="bx--toggle-input__label" for="compare-programs-toggle-pb-lines">
                        PB Lines
                        <span class="bx--toggle__switch">
                            <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                            <span class="bx--toggle__text--on" aria-hidden="true">On</span>
                        </span>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <div class="card-style" id="compare-programs-no-data">
        <div class="d-flex justify-content-center w-100 p-2">
            <h2>Select program group to see data</h2>
        </div>
    </div>


    <div class="card-style d-none" id="execution-plots-card">
        <div class="d-flex flex-row w-100 gap-3 execution-row">
            <div id="execution-data-plot-1-container" class="execution-data-plot-container w-50">
                <div id="execution-data-plot-1" class="execution-data-plot"></div>
            </div>
            <div id="execution-data-plot-2-container" class="execution-data-plot-container w-50" style="border-left:1px solid rgb(240, 240, 240)">
                <div id="execution-data-plot-2" class="execution-data-plot"></div>
            </div>
        </div>
    </div>

    <div class="chart-column full-width mb-3 d-none" id="chart-and-table-card">
        <div class="d-flex flex-row w-100 gap-3 flex-wrap">
            <div class="chart-header w-100">Selected Program Groups</div>
            <!-- Uncomment below line for graph chart -->
            <!-- <div id="compare-programs-selected-program-chart-container" class="d-flex flex-row justify-content-center align-items-center w-100 w-md-50 p-4">
                <div class="w-100" id="compare-programs-selected-program-chart"></div>
            </div> -->

            <div class="d-flex flex-column align-items-center w-100 w-md-50 p-4" id="compare-programs-table-container">
                <div id="compare-programs-table-dropdown-container" class="w-100 d-flex flex-row mb-2">
                    <div id="fy-dropdown-container" class="d-none">
                        <div class="d-flex align-items-center mb-1 bx--label medium-label">FY</div>
                        <select id="FY" class="select2"></select>
                    </div>
                </div>
                <table class="display dataTable cell-border table-style w-100 bx--data-table pt-3" id="compare-programs-table" style="text-align:center;"></table>
            </div>
        </div>
    </div>
</div>

<script>
    $('#APPN').on('change', function() {
        applyAPPNFilter();
    });
</script>