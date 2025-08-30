<style>
    .table-container-pq-aq {
        display: flex;
        flex-direction: row;
        gap: 50px;
        justify-content: center !important;
        align-items: center !important;
        width: 100%;
        flex-wrap: wrap;
        text-align: center;
    }
    .fielding-table-view{
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        width: 100%;
    }
    [id^="planned-quantities-table-program-execution-drilldown"],
    [id^="actual-quantities-table-program-execution-drilldown"] {
        color: black;
        border-collapse: collapse;
        background: white;
        min-width: 600px;
        max-width: 100%;
        margin: auto;
        flex-grow: 1;
    }
    [id^="planned-quantities-table-program-execution-drilldown"] th,
    [id^="actual-quantities-table-program-execution-drilldown"] th {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
        background-color: rgb(203, 203, 203);
    }
    [id^="planned-quantities-table-program-execution-drilldown"] td, [id^="actual-quantities-table-program-execution-drilldown"] td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
        background-color: rgb(244, 244, 244);
    }
    .dropdown-spacing {
        display: flex;
        gap: 30px;
        align-items: center;
        margin-bottom: 50px;
    }
    #program-execution-drilldown-fielding-table-view .select2-selection__rendered {
        line-height: 25px !important;
        margin: 10px !important;
    }

    #fielding-component-dropdown .select2-selection__rendered {
        margin: unset !important;
    }
</style>

<div id="program-execution-drilldown-fielding-table-view" class="fielding-table-view">
    <div id="program-execution-drilldown-fielding-table-no-data" class="d-flex justify-content-center w-100 p-2" hidden>
        <h2>No data found</h2>
    </div>
    <div id="program-execution-drilldown-fielding-table-container">
        <div class="w-100 d-flex justify-content-center mb-3">
            <h3>
                <span id="selected-program-group-program-execution-drilldown"></span> - Fielding Information for <span id="fielding-information-text-program-execution-drilldown"></span>
            </h3>
        </div>
        <!-- Dropdowns -->
        <div class="d-flex dropdown-spacing">
            <div class="cds--form-item">
                <label for="program-execution-drilldown-fiscal-year-dropdown" class="d-flex align-items-center mt-3 mb-2 bx--label medium-label fielding-dropdown-label">Select Year</label>
                    <select id="program-execution-drilldown-fiscal-year-dropdown" class="" onchange="updateFieldingTableOnChange()">
                    </select>
            </div>

            <div class="fielding-dropdown-container w-100 d-flex justify-content-end">
                <?php $this->load->view('SOCOM/portfolio/dropdown_filter_view', [
                    'options' => $components,
                    'title' => 'Component',
                    'view_type' => 'fielding-component',
                    'default_select_all' => false,
                    'select_all_button' => true
                ]); ?>
            </div>
        </div>

        <!-- Tables -->
        <div class="table-container-pq-aq">
            <!-- Planned Quantities Table -->
            <section>
                <h4>Planned Quantities</h4>
                <table class="cds--data-table" id="planned-quantities-table-program-execution-drilldown">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
            <!-- Actual Quantities Table -->
            <section>
                <h4>Actual Quantities</h4>
                <table class="cds--data-table" id="actual-quantities-table-program-execution-drilldown">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>

        </div>
    </div>

</div>
