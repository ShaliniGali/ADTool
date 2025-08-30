<div id="coa-detailed-comparison-jca-alignment-container-<?= $table_id?>" class="white-background p-3">
    <h2 class="pt-2 pb-2" style="text-align: center;">Detailed Comparison between <?= $title; ?></h2>
    <div class="d-flex flex-row justify-content-between p-2">
        <div id="coa-detailed-comparison-jca-alignment-<?= $table_id?>-container" class="w-100">
            <div data-content-switcher class="bx--content-switcher" role="tablist" aria-label="Demo switch content" style="max-width: 16rem;">
                <button class="bx--content-switcher-btn bx--content-switcher--selected"
                    data-target="#coa-detailed-comparison-jca-alignment-chart-<?= $table_id?>-container" role="tab"  aria-selected="true"  >
                    <span class=bx--content-switcher__label>Visual</span>
                </button>
                <button class="bx--content-switcher-btn"
                    data-target="#coa-detailed-comparison-jca-alignment-table-<?= $table_id?>-container" role="tab"  >
                    <span class=bx--content-switcher__label>Data</span>
                </button>
            </div>
        </div>
        <div class="d-flex flex-column">
            <div class="d-flex flex-row">
                <div id="jca-alignment-lvl1-<?= $table_id?>-dropdown" class="d-flex flex-column mr-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="mb-1 bx--label medium-label">1st Level</div>
                        <div>
                            <button id="jca-alignment-lvl1-<?= $table_id?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                            data-select-all="false"
                            type="button" onclick="dropdown_selection('#jca-alignment-lvl1-<?= $table_id?>')">
                                Deselect All
                            </button>
                            </div>
                    </div>
                    <select
                            id="jca-alignment-lvl1-<?= $table_id?>"
                            type="lvl1"
                            combination-id=""
                            class="selection-dropdown"
                            onchange="dropdown_onchange(<?= $table_id?>, 'lvl1', 'jca-alignment', 'comparison', <?= $scenario_id ?>)"
                            multiple="multiple"
                            >
                        <option option="ALL" selected>ALL</option>
                    </select>
                </div>
                <div id="jca-alignment-lvl2-<?= $table_id?>-dropdown" class="d-flex flex-column mr-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="mb-1 bx--label medium-label">2nd Level</div>
                        <div>
                            <button id="jca-alignment-lvl2-<?= $table_id?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                            data-select-all="false"
                            type="button" onclick="dropdown_selection('#jca-alignment-lvl2-<?= $table_id?>')">
                                Deselect All
                            </button>
                            </div>
                    </div>
                    <select
                            id="jca-alignment-lvl2-<?= $table_id?>"
                            type="lvl2"
                            combination-id=""
                            class="selection-dropdown"
                            onchange="dropdown_onchange(<?= $table_id?>, 'lvl2', 'jca-alignment', 'comparison', <?= $scenario_id ?>)"
                            multiple="multiple"
                            >
                        <option option="ALL" selected>ALL</option>

                    </select>
                </div>
                <div id="jca-alignment-lvl3-<?= $table_id?>-dropdown" class="d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="mb-1 bx--label medium-label">3rd Level</div>
                        <div>
                            <button id="jca-alignment-lvl3-<?= $table_id?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                            data-select-all="false"
                            type="button" onclick="dropdown_selection('#jca-alignment-lvl3-<?= $table_id?>')">
                                Deselect All
                            </button>
                            </div>
                    </div>
                    <select
                            id="jca-alignment-lvl3-<?= $table_id?>"
                            type="lvl3"
                            combination-id=""
                            class="selection-dropdown"
                            onchange="dropdown_onchange(<?= $table_id?>, 'lvl3', 'jca-alignment', 'comparison', <?= $scenario_id ?>)"
                            multiple="multiple"
                            >
                        <option option="ALL" selected>ALL</option>
                    </select>
                </div>
            </div>
            <div class="d-flex flex-row mt-2 justify-content-end">
                <input id="jca-alignment-lvl3-details-<?= $table_id?>" class="checkbox mr-2 mb-1 flex-row" type='checkbox' onclick="dropdown_onchange(<?= $table_id?>, 'level-checkbox', 'jca-alignment', 'comparison')" /> 
                <label for="jca-alignment-lvl3-details-<?= $table_id?>" class="mb-1 bx--label medium-label flex-row">3rd Level Details</label>
            </div>
        </div>
    </div>
    <div id="coa-detailed-comparison-jca-alignment-chart-<?= $table_id?>-container" class="d-flex flex-row mt-2 <?= count($saved_coa_ids) >= 3 ? '' : 'justify-content-center'; ?>" style="overflow-x: scroll;">
        <?php foreach($saved_coa_ids as $idx => $saved_coa_id): ?>
            <div class="w-50">
                <div class="p-2">
                    <h4><?= $titles[$idx] ;?></h4><h4>FYDP: <span id="jca-alignment-inc-fydp-<?= $saved_coa_id?>"></span></h4>
                </div>
                <div class="w-100 d-flex flex-row">
                    <div id="coa-detailed-comparison-jca-alignment-included-chart-<?= $saved_coa_id?>" class="pie-chart-container w-100"></div>
                </div>
              
            </div>
        <?php endforeach; ?>
    </div>
    <div id="coa-detailed-comparison-jca-alignment-table-<?= $table_id?>-container" class="d-flex flex-row mt-2 <?= count($saved_coa_ids) >= 3 ? '' : 'justify-content-center'; ?>" style="overflow-x: scroll;" hidden>
        <?php foreach($saved_coa_ids as $idx => $saved_coa_id): ?>
            <div class="w-50">
                <div class="p-2">
                    <h4><?= $titles[$idx] ;?></h4>
                </div>
                <div class="w-100 d-flex flex-column p-2">
                    <div class="">
                        <h5>Covered</h5>
                        <table id="coa-detailed-comparison-jca-alignment-included-covered-table-<?= $saved_coa_id?>" class="bx--data-table w-100" >
                            <thead>
                                <tr>
                                    <?php foreach ($headers['covered'] as $header): ?>
                                        <th>
                                            <span class="bx--table-header-label"><?= $header['title']; ?></span>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="mt-2">
                        <h5>Non-covered</h5>
                        <table id="coa-detailed-comparison-jca-alignment-included-noncovered-table-<?= $saved_coa_id ?>" class="bx--data-table w-100" >
                            <thead>
                                <tr>
                                    <?php foreach ($headers['noncovered'] as $header): ?>
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
        <?php endforeach; ?>
    </div>
</div>
<script>
    CarbonComponents.ContentSwitcher.init()
    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '12vw'
    })
</script>