<div class="d-flex flex-column w-100 px-2 py-3 my-3">
    <div id="weighted_sel" class="d-flex flex-row justify-content-between ml-2 w-100">
        <div class="d-flex flex-row w-50">
            <?php $this->load->view('components/radioButtonGroup', [
                            'name' => 'storm_weighted_based',
                            'label' => 'Choose Weighted or StoRM',
                            'useTile' => false,
                            'radioButtons' => [
                                'WEIGHTED' => [
                                    'id' => 'r-w',
                                    'value' => '1',
                                    'key' => 1,
                                    'checked' => false,
                                    'label' => 'Weighted'
                                ],
                                'StoRM' => [
                                    'id' => 'r-storm',
                                    'value' => '2',
                                    'key' => 2,
                                    'checked' => true,
                                    'label' => 'StoRM'
                                ]
                            ]
            ]); ?>

    </div>
    <?php
       $selected_value = filter_var($this->session->userdata('use_iss_extract') ?? false, FILTER_VALIDATE_BOOLEAN); // Default to 'false' (Resource Constraining)
    ?>
    <div id="dataset-radio-buttons-optimizer" class="d-flex flex-row w-25 px-2 align-top">
        <div  style="width: 1rem;height: 1rem;"><?php $this->load->view('components/loading_small', ['hidden' => true]); ?></div>
        <div class="ml-1"></div>
        <?php $this->load->view('components/radioButtonGroup', [
                    'name' => 'use_iss_extract',
                    'label' => 'POM Cycle Optimization Type',
                    'useTile' => true,
                    'radioButtons' => [
                        'ISS EXTRACT' => [
                            'id' => 'r-iss-extract-program',
                            'value' => 'true',
                            'key' => 2,
                            'checked' => ($selected_value === true),
                            'label' => 'Issue Optimization'
                        ],
                        'ISS' => [
                            'id' => 'r-iss-program',
                            'value' => 'false',
                            'key' => 1,
                            'checked' => ($selected_value === false),
                            'label' => 'Resource Constraining'
                        ]
                    ]
        ]); ?>
        </div>
        
    </div>
    <div id="weighted_row"  class="d-none flex-row ml-2 w-100">
                    <div id="weight_chooser" class="d-flex flex-column">
                            <?php
                                $this->load->view('SOCOM/program/available_weight_chooser', [
                                    'fpWeightId' => 'fp-weight-sel',
                                    'onchange' =>'changeProgramDropdown()'
                                ]);
                            ?>
                    </div>
                <div id="weighted_values" class="d-flex flex-column w-100">
                    <?php
                        $this->load->view('SOCOM/program/weight_criteria_values', [
                            'title' => 'Guidance',
                            'criteria' => 'guidance',
                            'default_criteria_description' => $default_criteria_description
                        ]);
                    ?>

                    <?php
                        $this->load->view('SOCOM/program/weight_criteria_values', [
                            'title' => 'POM',
                            'criteria' => 'pom',
                            'default_criteria_description' => $default_criteria_description
                        ]);
                    ?>
                </div>
    </div>
</div>

<script>
$(document).ready(function () {
    function initializeCoaTable($table) {
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        let dt = $table.DataTable({
            columnDefs: [
                { width: '10%', targets: 0 },
                { width: '50%', targets: 1 },
                { width: '40%', targets: 2 }
            ],
            order: [],
            ordering: false,
            searching: false,
            paging: false,
            length: 100,
            scrollX: true,
            lengthChange: false,
            autoWidth: false,
            info: false,
            language: {
                emptyTable: "",
                zeroRecords: ""
            }
        });
        if(dt.data().count() === 0){
            $table.find('tbody').hide();
        }
    }

    function adjustVisibleTables($container) {
        setTimeout(function () {
            $container.find('table.display:visible').each(function () {
                const $table = $(this);
                if (!$.fn.DataTable.isDataTable($table)) {
                    initializeCoaTable($table);
                } else {
                    $table.DataTable().columns.adjust().draw();
                }
            });
        }, 150);
    }

    $('input[name="use_iss_extract"]').on('change', function () {
        let selectedValue = $(this).val();
        $.ajax({
            url: "/socom/resource_constrained_coa/program/list/update",
            type: "POST",
            data: { use_iss_extract: selectedValue, rhombus_token: rhombuscookie() },
            success: function (response) {
                console.log("Selection updated successfully.");
                if (selectedValue === "true") {
                    $('#iss-optimizer-options').removeClass('d-none');
                    $('#iss-opt-table-view').removeClass('d-none');
                    $('#rc-optimizer-options').addClass('d-none');
                    $('#rc-opt-table-view').addClass('d-none');

                    adjustVisibleTables($('#iss-opt-table-view'));
                } else {
                    $('#rc-optimizer-options').removeClass('d-none');
                    $('#rc-opt-table-view').removeClass('d-none');
                    $('#iss-optimizer-options').addClass('d-none');
                    $('#iss-opt-table-view').addClass('d-none');

                    adjustVisibleTables($('#rc-opt-table-view'));
                }
                
            },
            error: function () {
                console.log("Error updating selection.");
            }
        });
    });
    // Initial page load: show/hide divs based on default session value
    const isIssueOptimization = <?= $selected_value ? 'true' : 'false' ?>;
    if (isIssueOptimization) {
        $('#iss-optimizer-options').removeClass('d-none');
        $('#iss-opt-table-view').removeClass('d-none');
        $('#rc-optimizer-options').addClass('d-none');
        $('#rc-opt-table-view').addClass('d-none');

        adjustVisibleTables($('#iss-opt-table-view'));
    } else {
        $('#rc-optimizer-options').removeClass('d-none');
        $('#rc-opt-table-view').removeClass('d-none');
        $('#iss-optimizer-options').addClass('d-none');
        $('#iss-opt-table-view').addClass('d-none');

        adjustVisibleTables($('#rc-opt-table-view'));
    }
});
</script>