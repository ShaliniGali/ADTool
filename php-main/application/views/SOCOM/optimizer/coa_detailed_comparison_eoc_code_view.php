<div id="coa-detailed-comparison-eoc-code-container-<?= $table_id ?>" class="white-background p-3">
    <h2 class="pt-2 pb-2" style="text-align: center;">Detailed Comparison between <?= $title; ?></h2>
    <div class="d-flex flex-row mt-2 <?= count($saved_coa_ids) >= 3 ? '' : 'justify-content-center'; ?>" style="overflow-x: scroll;">
        <?php foreach($saved_coa_ids as $idx => $saved_coa_id): ?>
            <div class="">
                <div class="p-2">
                    <h4><?= $titles[$idx] ;?></h4>
                </div>
                <div class="w-100 d-flex flex-column">
                    <div class="p-2">
                        <div class="d-flex flex-row justify-content-end">
                            <div id="include-sponsor-dropdown-<?= $saved_coa_id; ?>" class="d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="mb-1 bx--label medium-label">Cap Sponsor</div>
                                    <div>
                                        <button id="include-sponsor-<?= $idx + 1 ?>-<?= $saved_coa_id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                                        data-select-all="false"
                                        type="button" onclick="dropdown_selection('#include-sponsor-<?= $idx + 1 ?>-<?= $saved_coa_id ?>')">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>
                                <select
                                        id="include-sponsor-<?= $idx + 1 ?>-<?= $saved_coa_id ?>"
                                        type="sponsor"
                                        combination-id=""
                                        class="selection-dropdown"
                                        onchange="dropdown_onchange(<?= $saved_coa_id ?>, 'include-sponsor', 'eoc-code', 'comparison', <?= $idx + 1 ?>)"
                                        multiple="multiple"
                                        >
                                    <option option="ALL" selected>ALL</option>
        
                                </select>
                            </div>
                        </div>
                        <div id="coa-detailed-comparison-eoc-code-included-chart-<?= $saved_coa_id ?>" class="pie-chart-container"></div>
                        <table id="coa-detailed-comparison-eoc-code-included-table-<?= $saved_coa_id ?>" class="bx--data-table ml-0">
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
        <?php endforeach; ?>
    </div>
</div>
<script>
    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '12vw'
    })
</script>