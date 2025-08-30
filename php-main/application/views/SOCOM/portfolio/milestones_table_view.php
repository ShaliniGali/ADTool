<style>
    .vertical {
        width: 1px;
        height: 100%;
        background-color: #e0e0e0;
        margin: 0 10px;
        display: inline-block;
        vertical-align: middle;
    }

    .horizontal {
        width: 100%;
        background-color: #e0e0e0;
        display: inline-block;
        vertical-align: middle;
    }

    .star-icon {
        width: 10%;
        margin: 0 10px;
        display: flex;
    }

    .bordered-star {
        stroke: #000000;
        stroke-width: 2;
    }

    .completed-milestone:hover {
        cursor: pointer; /* change cursor to a pointing hand */
    }

</style>

<?php foreach($milestone_data as $key => $value): ?>
    <div id="<?= $key; ?>-milestone-container" class="d-flex flex-column w-100">
        <div class="d-flex justify-content-center m-2"><h4><?= $value['title']; ?></h4></div>
        <?php foreach($value['data'] as $data): ?>
            <hr class="horizontal" />
            <div class="" style="height: 1rem;"> 
            </div>
            <div 
                class="d-flex flex-row justify-content-between milestone-container px-3 <?= $data['HAS_REQUIREMENTS'] ? 'completed-milestone' : ''; ?>"
                onclick="showMilestonesRequirementsModal('<?= $data['PXID']; ?>', '<?= $data['MILESTONE']; ?>', '<?= $value['title']; ?>', '  <?= $data['HAS_REQUIREMENTS']; ?>')"
                <?= $data['HAS_REQUIREMENTS'] ? 'data-modal-target="#program-execution-drilldown-milestones-requirements"' : ''; ?>
            >
                <div class="d-flex flex-row w-50">
                    <div class="star-icon">
                        <svg id="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                            <defs><style>.cls-1{fill:none;}</style></defs><title>star--filled</title>
                            <path class="bordered-star" fill="<?= $value['fill']; ?>" d="M16,2l-4.55,9.22L1.28,12.69l7.36,7.18L6.9,30,16,25.22,25.1,30,23.36,19.87l7.36-7.17L20.55,11.22Z"/>
                            <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32"/>
                        </svg>
                    </div>
                    <div class="d-flex align-items-center">
                        <h4><?= $data['MILESTONE']; ?></h4>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <table>
                        <tr>
                            <td class="p-1"><h4><strong>Start Year:</strong></h4></td>
                            <td class="p-1"><h4><?= strtotime($data['START_DATE']) <= 0 ? 'N/A' : date('Y', strtotime($data['START_DATE'])); ?></h4></td>
                        </tr>
                        <tr>
                            <td class="p-1"><h4><strong>End Year:</strong></h4></td>
                            <td class="p-1"><h4><?= strtotime($data['END_DATE']) <= 0 ? 'N/A' : date('Y', strtotime($data['END_DATE'])); ?></h4></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="d-flex flex-row justify-content-end w-100 px-3" style="height: 1rem;"> 
                <?php if($data['HAS_REQUIREMENTS']): ?>
                    <h1>*</h1>
                <?php endif; ?>
            </div>
        <?php endforeach;?>
        <hr class="horizontal" />
    </div>
    <?php if($key !== 'future'): ?>
        <div><hr class="vertical" /> </div>
    <?php endif; ?>
<?php endforeach;?>

<script>
    $(`#program-execution-drilldown-procurement-strategy-header`).html('<?= $procurement_strategy; ?>');
    $(`#program-execution-drilldown-program-header`).html('<?= $selected_program; ?>');
</script>