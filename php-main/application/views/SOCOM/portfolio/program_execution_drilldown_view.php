<style>
    .program-execution-drilldown-container  {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        width: 100%;
    }

    #program-execution-drilldown-program-group-dropdown {
        max-width: fit-content;
    }
    #program-execution-drilldown-container .select2-selection__rendered {
        margin: 10px !important;
    }

    #program-execution-drilldown-ams-graph-container .select2-selection__rendered {
        margin: unset !important;
    }
</style>

<?php $this->load->view('SOCOM/portfolio/fielding_quantities_graph_modal_view.php'); ?>
<?php $this->load->view('SOCOM/portfolio/milestones_requirements_modal_view.php'); ?>
<?php $this->load->view('SOCOM/portfolio/ams_data_modal_view.php'); ?>

<!-- Dynamic checkbox will be added here -->
<div id="program-execution-drilldown-container" class="program-execution-drilldown-container p-3">

    <div class="d-flex w-100 justify-content-start mb-5">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center mb-2 bx--label medium-label">Program Group</div>
            <select
                id="program-execution-drilldown-program-group-dropdown"
                class="selection-dropdown"
                onchange="programGroupDropdownOnchange('program-execution-drilldown')"
                >
                <option></option>
                <?php foreach($program_groups as $index => $value): ?>
                        <option value="<?= $value; ?>">
                            <?= $value; ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="program-execution-drilldown-no-data" class="d-flex justify-content-center w-100 p-2"> 
        <h2>Select program group to see data</h2>
    </div>
    <div id="program-execution-drilldown-view-container" class="d-none">
        <div class="d-flex flex-row justify-content-between w-25 mb-2">
            <div data-content-switcher class="bx--content-switcher historical-pom-switch" role="tablist" aria-label="Demo switch content">
                <button class="bx--content-switcher-btn bx--content-switcher--selected"
                    data-target="#program-execution-drilldown-funding-container" role="tab"  aria-selected="true"  >
                    <span class=bx--content-switcher__label>Funding</span>
                </button>
                <button class="bx--content-switcher-btn"
                    data-target="#program-execution-drilldown-milestones-container" role="tab"  >
                    <span class=bx--content-switcher__label>Milestones</span>
                </button>
                <button class="bx--content-switcher-btn"
                    data-target="#program-execution-drilldown-fielding-container" role="tab"  >
                    <span class=bx--content-switcher__label>Fielding</span>
                </button>
            </div>
        </div>
        <div id="program-execution-drilldown-funding-container" class="">
            <div class="d-flex flex-column align-items-center justify-content-start p-3">
                <?php $this->load->view('SOCOM/portfolio/line_plot_view.php', ['type' => 'program-execution-drilldown']);?>

                <div class="d-flex flex-row w-100">
                            <div id="program-execution-drilldown-ams-graph-container" class="w-100">
                                <div class="w-100 d-flex justify-content-start">
                                    <?php $this->load->view('SOCOM/portfolio/dropdown_filter_view', [
                                        'options' => [],
                                        'title' => 'Resource Category',
                                        'view_type' => 'funding-resource-category',
                                        'default_select_all' => false,
                                        'select_all_button' => true
                                    ]); ?>
                                </div>
                                <div id="program-execution-drilldown-ams-graph" class=""></div>
                            </div>
                            <section class="bx--structured-list">
                                <div class="bx--structured-list-thead">
                                    <div class="bx--structured-list-row bx--structured-list-row--header-row">
                                    </div>
                                </div>
                                <div id="program-execution-drilldown-ams-data-container" class="bx--structured-list-tbody">
                                </div>
                            </section>
                </div>

            </div>
        </div>
        <div id="program-execution-drilldown-milestones-container" class="" hidden>
        </div>
        <div id="program-execution-drilldown-fielding-container" class="" hidden>
            <div class="d-flex flex-row align-items-center justify-content-start p-3">
                <?php $this->load->view('SOCOM/portfolio/fielding_table_view.php'); ?>
            </div>
        </div>
    </div>

</div>

<script>
    $(`#program-execution-drilldown-program-group-dropdown`).select2({
        placeholder: "Select an option",
        width: '17vw'
    });
</script>