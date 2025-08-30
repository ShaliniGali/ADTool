
<style>

    #program-execution-drilldown-milestones-view .select2-selection__rendered {
        line-height: 25px !important;
        margin: 10px !important;
    }

    /* #program-execution-drilldown-milestones-dropdown {
        max-width: fit-content;
    } */
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

</style>
<div id="program-execution-drilldown-milestones-view" class="p-3">

    <div class="d-flex flex-column align-items-center justify-content-center w-100">
        <div class="d-flex flex-column align-items-center">
            <h3>Procurement Strategy: <span id="program-execution-drilldown-procurement-strategy-header"><?= $procurement_strategy; ?></span></h3>
            <h3><span id="program-execution-drilldown-program-header"><?= $selected_program; ?></span></h3>
        </div>
        <div class="d-flex w-100 justify-content-start">
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center mb-2 bx--label medium-label">Program Name</div>
                <select
                    id="program-execution-drilldown-milestones-dropdown"
                    class="selection-dropdown"
                    onchange="milestoneDropdownOnchange('<?= $tab_type;?>', '<?= $program_group; ?>')"
                    >
                    <option></option>
                    <?php foreach($program_selections as $index => $value): ?>
                            <option
                                value="<?= $value; ?>"
                                <?= ($value === $selected_program) ? 'selected' : ''; ?>
                            >
                                <?= $value; ?>
                            </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="program-execution-drilldown-milestones-table-view" class="d-flex flex-row justify-content-between w-100 my-5" >
            <?php $this->load->view('SOCOM/portfolio/milestones_table_view'); ?>
        </div>
    </div>
</div>

<script>
    $(`#program-execution-drilldown-milestones-dropdown`).select2({
        placeholder: "Select an option",
        width: '17vw'
    });
</script>