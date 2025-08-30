<style>
    table.dataTable.no-footer {
        border-bottom: 0px solid #111;
    }

    .table-container-pq-aq {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 50px;
        justify-content: flex-start !important;
        align-items: flex-start !important;
        width: 100%;
        text-align: center;
        border: none;
    }

    .fielding-table-view {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        min-width: 100%;
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
        border: none;
    }

    /* Shared padding for both th and td */
    [id^="planned-quantities-table-program-execution-drilldown"] th,
    [id^="actual-quantities-table-program-execution-drilldown"] th,
    [id^="planned-quantities-table-program-execution-drilldown"] td,
    [id^="actual-quantities-table-program-execution-drilldown"] td {
        padding: 16px !important;
        text-align: left;
        background-color: white;
        color: black;
        border: none !important;
    }

    /* Fixed header */
    [id^="planned-quantities-table-program-execution-drilldown"] thead th,
    [id^="actual-quantities-table-program-execution-drilldown"] thead th {
        position: sticky;
        top: 0;
        background-color: #f9f9f9;
        z-index: 2;
    }

    /* Section title row */
    .section-title-row {
        background-color: #eaeaea;
        font-weight: normal;
        text-align: left;
    }

    /* Make links black */
    [id^="planned-quantities-table-program-execution-drilldown"] a,
    [id^="actual-quantities-table-program-execution-drilldown"] a {
        color: black !important;
        text-decoration: none;
    }

    /* Highlight only tbody rows on hover */
    [id^="planned-quantities-table-program-execution-drilldown"] tbody tr:hover td,
    [id^="actual-quantities-table-program-execution-drilldown"] tbody tr:hover td {
        background-color: #f0f0f0;
        cursor: pointer;
    }

    .dropdown-spacing {
        display: flex;
        gap: 30px;
        align-items: flex-start;
        justify-content: flex-start;
        width: 100%;
        max-width: 800px;
        margin: 6rem 0 50px;
    }

    .cds--form-item,
    .fielding-dropdown-container {
        flex: 0 0 auto !important;
        width: auto !important;
        justify-content: flex-start !important;
    }

    #program-execution-drilldown-fielding-table-view .select2-selection__rendered {
        line-height: 25px !important;
        margin: 10px !important;
    }

    #fielding-component-dropdown .select2-selection__rendered {
        margin: unset !important;
    }
    .header-title-fielding{
        margin: 30px !important;
    }
    .table-container-pq-aq > section {
    width: 45%;
}

#planned-quantities-table-program-execution-drilldown,
#actual-quantities-table-program-execution-drilldown {
    width: 100%;
}

</style>


<div id="program-execution-drilldown-fielding-table-view" class="fielding-table-view w-100">
    <div id="program-execution-drilldown-fielding-table-no-data" class="d-flex justify-content-center w-100 p-2" hidden>
        <h2>No data found</h2>
    </div>

    <div id="program-execution-drilldown-fielding-table-container" class="w-100">
        <div class="w-100 d-flex justify-content-center mt-4 mb-4 header-title-fielding" >
            <h3>
                <span id="selected-program-group-program-execution-drilldown"></span> - Fielding Information for <span id="fielding-information-text-program-execution-drilldown"></span>
            </h3>
        </div>

        <!-- Dropdowns -->
        <div class="d-flex dropdown-spacing">
            <div class="cds--form-item">
                <label for="program-execution-drilldown-fiscal-year-dropdown" class="d-flex align-items-start mt-3 mb-2 bx--label medium-label fielding-dropdown-label">Select Year</label>
                <select id="program-execution-drilldown-fiscal-year-dropdown" onchange="updateFieldingTableOnChange()">
                </select>
            </div>

            <div class="fielding-dropdown-container w-100 d-flex justify-content-start">
                <?php $this->load->view('SOCOM/portfolio_viewer/dropdown_filter_view', [
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
                <table class="cds--data-table" id="planned-quantities-table-program-execution-drilldown">
                    <thead>
                        <tr class="section-title-row">
                            <th colspan="2">Planned Quantities</th>
                        </tr>
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
                <table class="cds--data-table" id="actual-quantities-table-program-execution-drilldown">
                    <thead>
                        <tr class="section-title-row">
                            <th colspan="2">Actual Quantities</th>
                        </tr>
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