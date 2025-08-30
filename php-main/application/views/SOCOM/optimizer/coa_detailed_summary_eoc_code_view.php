<div id="coa-detailed-summary-eoc-code-container-<?= $table_id ?>" class="white-background p-3">
    <h2 class="pt-2 pb-2" style="text-align: center;">Detailed Summary of <strong><?= $title; ?></strong></h2>
    <div class="d-flex flex-row mt-2">
        <div class="w-50 mr-2">
            <div class="p-2 d-flex flex-row justify-content-between">
                <h4>Include:</h4>
                <div class="d-flex flex-row">
                    <div id="include-sponsor-dropdown" class="d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Cap Sponsor</div>
                            <div>
                                <button id="include-sponsor-<?= $table_id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                                data-select-all="false"
                                type="button" onclick="dropdown_selection('#include-sponsor-<?= $table_id ?>')">
                                    Deselect All
                                </button>
                                </div>
                        </div>
                        <select
                                id="include-sponsor-<?= $table_id ?>"
                                type="include-sponsor"
                                combination-id=""
                                class="selection-dropdown"
                                onchange="dropdown_onchange(<?= $table_id ?>, 'include-sponsor', 'eoc-code', 'summary', <?= $scenario_id; ?>)"
                                multiple="multiple"
                                >
                            <option option="ALL" selected>ALL</option>
 
                        </select>
                    </div>
                </div>
            </div>
            <div class="w-100 d-flex flex-column">
                <div id="coa-detailed-summary-eoc-code-included-chart-<?= $table_id ?>" class="pie-chart-container w-100"></div>
                <div class="p-2">
                    <table id="coa-detailed-summary-eoc-code-included-table-<?= $table_id ?>" class="bx--data-table ml-0">
                        <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th>
                                    <span class="bx--table-header-label"><?= $header['title']; ?></span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
        <div class="w-50">
            <div class="p-2 d-flex flex-row justify-content-between">
                <h4>Exclude:</h4>
                <div class="d-flex flex-row">
                    <div id="exclude-sponsor-dropdown" class="d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="mb-1 bx--label medium-label">Cap Sponsor</div>
                            <div>
                                <button id="exclude-sponsor-<?= $table_id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                                data-select-all="false"
                                type="button" onclick="dropdown_selection('#exclude-sponsor-<?= $table_id ?>')">
                                    Deselect All
                                </button>
                                </div>
                        </div>
                        <select
                                id="exclude-sponsor-<?= $table_id ?>"
                                type="exclude-sponsor"
                                combination-id=""
                                class="selection-dropdown"
                                onchange="dropdown_onchange(<?= $table_id ?>, 'exclude-sponsor', 'eoc-code', 'summary', <?= $scenario_id; ?>)"
                                multiple="multiple"
                                >
                            <option option="ALL" selected>ALL</option>
 
                        </select>
                    </div>
                </div>
            </div>
            <div class="w-100 d-flex flex-column">
                <div id="coa-detailed-summary-eoc-code-excluded-chart-<?= $table_id ?>" class="pie-chart-container"></div>
                <div class="p-2">
                    <table id="coa-detailed-summary-eoc-code-excluded-table-<?= $table_id ?>" class="bx--data-table ml-0">
                        <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th>
                                    <span class="bx--table-header-label"><?= $header['title']; ?></span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                    </table>
                </div>
      
            </div>
        </div>
    </div>
</div>

<script>
    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '16vw'
    })
</script>