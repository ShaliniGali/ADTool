<style>
    .filter-container .select2-selection__rendered {
        margin: unset !important
    }
</style>
<div>
    <div class="d-flex flex-row filter-container">
        <div id="program-group-area-dropdown" class="d-flex flex-column mr-5 mt-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Program Group</div>
                <div>
                    <button id="program-group-area-<?= $id ?>-selection"
                        class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto" data-select-all="true"
                        type="button" onclick="dropdown_selection_business_rules('#program-group-area-<?= $id ?>')">
                        Select All
                    </button>
                </div>
            </div>
        </div>

        <div id="resource-category-filter" class="d-flex flex-column mr-5 mt-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Resource Category</div>
                <div>
                    <button id="resource-category-<?= $id ?>-selection"
                        class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                        data-select-all="true" disabled type="button"
                        onclick="dropdown_selection_business_rules('#resource-category-<?= $id ?>')">
                        Select All
                    </button>
                </div>
            </div>
            <select id="resource-category-<?= $id ?>" type="program" class="selection-dropdown" multiple="multiple"
                onchange="dropdown_onchange_business_rules('<?= $id ?>', 'resource-category')" disabled>
                <option></option>
            </select>
        </div>

        <div id="capability-sponsor-filter" class="d-flex flex-column mr-5 mt-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Capability Sponsor</div>
                <div>
                    <button id="capability-sponsor-<?= $id ?>-selection"
                        class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                        data-select-all="true" disabled type="button"
                        onclick="dropdown_selection_business_rules('#capability-sponsor-<?= $id ?>')">
                        Select All
                    </button>
                </div>
            </div>
            <select id="capability-sponsor-<?= $id ?>" type="program" class="selection-dropdown" multiple="multiple"
                onchange="dropdown_onchange_business_rules('<?= $id ?>', 'capability-sponsor')" disabled>
                <option></option>
            </select>
        </div>
    </div>
    <div class="d-flex flex-row justify-content-between filter-container">
        <div id="rule-filter" class="d-flex flex-column mr-5 mt-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Rule</div>
            </div>
            <select id="rule-select" name="rule-select" class="bx--select-input">
                <option disabled selected value="0"> Select an option </option>
                <option value="Priority">Priority</option>
                <option value="Remove From Play">Remove From Play</option>
            </select>
        </div>

        <div class="d-flex align-items-end">
            <button id="view_selection_button"
                class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" is-current="false"
                type="button" onclick="view_current_selections()">
                View Applied Business Rules
            </button>
        </div>
    </div>

    <div id="business_rules_list_outer_box" class="d-flex flex-column justify-content-center mt-3 pt-3">
        <h3 class="bx--modal-header__heading mb-3">Current Selection</h3>
        <table id="business-rules-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Capability Sponsor</th>
                    <th>Resource Category</th>
                    <th>FYDP</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div id="business_rules_history_box" class="d-flex flex-column justify-content-center mt-3 pt-3" hidden>
        <h3 class="bx--modal-header__heading mb-3">Business Rules History</h3>
        <table id="business-rules-history-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Program Group</th>
                    <th>Resource Category</th>
                    <th>Capability Sponsor</th>
                    <th>Rule</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>

<script>
    $(function() {
    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '13vw'
    })

    $('#business-rules-history-table').DataTable({
        "oLanguage": {
            "sEmptyTable": "No applied business rules"
        }
    });
    $('#business-rules-table').DataTable();
    get_program_group();
    });
    function get_program_group() {

        $.post("/socom/filter/business_rules/update_program_group", {
            rhombus_token: rhombuscookie(),
        }, function (data) {
            let programList = data['data'];
            let programOptions = '';
            programList.forEach(v => {
                programOptions += `<option value="${v['PROGRAM_GROUP']}">${v['PROGRAM_GROUP']}</option>`;
            });
            $(`#program-group-area-<?= $id ?>`).remove();

            $(`#program-group-area-dropdown`).append(
                `<select
                    id="program-group-area-<?= $id ?>"
                    type="program-group-area"
                    combination-id=""
                    class="selection-dropdown wss-selections"
                    multiple="multiple"
                    onchange="dropdown_onchange_business_rules('<?= $id ?>', 'program-group-area')"
                >
                <option option="Select All">Select All</option>
                ${programOptions}
                </select>`)

            $("#program-group-area-<?= $id ?>").select2({
                placeholder: "Select an option",
                width: '13vw'
            })
        })
    }

    function load_business_rules_table(program_group_type, resource_category_type, capability_sponsor_type) {

        var selectedProgram = get_dropdown_val(program_group_type);
        var resourceCategory = get_dropdown_val(resource_category_type);
        var capabilitySponsor = get_dropdown_val(capability_sponsor_type);

        if (selectedProgram == null || capabilitySponsor == null || resourceCategory == null) {
            return
        }

        const columnMap = {
            'PROGRAM_CODE': 0,
            'CAPABILITY_SPONSOR_CODE': 1,
            'RESOURCE_CATEGORY_CODE': 2,
            'fydp': 3,

        };

        let sourceTable = $('#optimizer-table').DataTable();
        let targetTable = $('#business-rules-table').DataTable();

        targetTable.clear().draw();

        let matchedRows = sourceTable.rows().data().toArray()

        if (!selectedProgram.includes('Select All')) {
            matchedRows = matchedRows.filter(row => selectedProgram.includes(row['PROGRAM_GROUP']));
        }
        if (!resourceCategory.includes('Select All')) {
            matchedRows = matchedRows.filter(row => resourceCategory.includes(row['RESOURCE_CATEGORY_CODE']));
        }
        if (!capabilitySponsor.includes('Select All')) {
            matchedRows = matchedRows.filter(row => capabilitySponsor.includes(row['CAPABILITY_SPONSOR_CODE']));
        }

        matchedRows.forEach(sourceRow => {
            let newRow = new Array(targetTable.columns().count()).fill('');

            for (let srcIdx in columnMap) {
                let tgtIdx = columnMap[srcIdx];
                newRow[tgtIdx] = sourceRow[srcIdx];
            }

            targetTable.row.add(newRow);
        });

        targetTable.draw();

    }

    function dropdown_selection_business_rules(target) {
        const dropdown = $(target);
        const selectionButton = `${target}-selection`;
        const isSelectAll = $(selectionButton).attr('data-select-all') === 'true';
        const stringifyIsSelectAll = isSelectAll.toString();
        const selectionTextOptions = {
            'false': 'Select All',
            'true': 'Deselect All'
        }
        $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
        $(selectionButton).html(selectionTextOptions[stringifyIsSelectAll]);
        if (!isSelectAll) {
            dropdown.val(null).trigger('change');
        } else {
            dropdown.val(null).trigger('change');
            dropdown.val('Select All').trigger('change');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
        }
    }

    function dropdown_onchange_business_rules(id, type, type2 = null, type3 = null) {
        let input_object = {}

        switch (type) {
            case 'program-group-area':
                dropdown_all_view_business_rules(type, id)
                update_resource_category_business_rules(type, id);
                break;
            case 'resource-category':
                dropdown_all_view_business_rules(type, id)
                update_capability_sponsor_business_rules(type2, type, id)
            case 'capability-sponsor':
                dropdown_all_view_business_rules(type, id)
                load_business_rules_table(type2, type3, type)
            default:
                break;
        }
    }

    function dropdown_all_view_business_rules(type, id) {
        const dropdown_id = `${type}-${id}`;
        const dropdown = $(`#${dropdown_id}`);

        const selectElement = document.getElementById(dropdown_id);
        const options = selectElement.options;

        const selectionButton = `#${dropdown_id}-selection`;

        let selected_values = dropdown.val();
        if (selected_values.includes("Select All")) {
            $(selectionButton).attr('data-select-all', 'false');
            $(selectionButton).html('Deselect All');


            if (selected_values.length > 1) {
                dropdown.val('Select All').trigger("change");
            }

            for (let i = 1; i < options.length; i++) {
                options[i].disabled = true
            }
        }
        if (!selected_values.includes("Select All")) {
            $(selectionButton).attr('data-select-all', 'true');
            $(selectionButton).html('Select All');

            for (let i = 1; i < options.length; i++) {
                options[i].disabled = false
            }
        }
    }

    function update_resource_category_business_rules(type, id) {
        reset_dropdown('capability-sponsor')

        const program_group_dropdown_id = `${type}-${id}`;
        selectedProgram = $(`#${program_group_dropdown_id}`);

        var programGroup = selectedProgram.val();

        if (programGroup.length === 0) {
            reset_dropdown('resource-category');

        }
        else {
            $.post("/socom/filter/business_rules/update_resource_category", {
                rhombus_token: rhombuscookie(),
                'program-group': programGroup
            }, function (data) {
                let resourceCategoryList = data['data'];
                let resourceCategoryOptions = '';
                resourceCategoryList.forEach(v => {
                    resourceCategoryOptions += `<option value="${v['RESOURCE_CATEGORY_CODE']}">${v['RESOURCE_CATEGORY_CODE']}</option>`;
                });

                $(`#resource-category-<?= $id ?>`).select2('destroy');
                $(`#resource-category-<?= $id ?>`).remove();

                $(`#resource-category-filter`).append(
                    `<select id="resource-category-<?= $id ?>" type="program" class="selection-dropdown"
                    multiple="multiple" onchange="dropdown_onchange_business_rules('<?= $id ?>', 'resource-category', 'program-group-area')">
                    <option option="Select All">Select All</option>
                    ${resourceCategoryOptions}
                    </select>`)

                $("#resource-category-<?= $id ?>").select2({
                    placeholder: "Select an option",
                    width: '13vw'
                })

                $(`#resource-category-<?= $id ?>-selection`).prop('disabled', false);
            })
        }
    }

    function update_capability_sponsor_business_rules(program_group_type, resource_category_type, id) {

        var programGroup = get_dropdown_val(program_group_type);
        var resourceCategory = get_dropdown_val(resource_category_type);

        if (programGroup.length === 0 || resourceCategory.length === 0) {
            reset_dropdown('capability-sponsor')
        }
        else {
            $.post("/socom/filter/business_rules/update_capability_sponsor", {
                rhombus_token: rhombuscookie(),
                'resource-category': resourceCategory,
                'program-group': programGroup
            }, function (data) {
                let capabilitySponsorList = data['data'];
                let capabilitySponsorOptions = '';
                capabilitySponsorList.forEach(v => {
                    capabilitySponsorOptions += `<option value="${v['CAPABILITY_SPONSOR_CODE']}">${v['CAPABILITY_SPONSOR_CODE']}</option>`;
                });

                $(`#capability-sponsor-<?= $id ?>`).select2('destroy');
                $(`#capability-sponsor-<?= $id ?>`).remove();

                $(`#capability-sponsor-filter`).append(
                    `<select id="capability-sponsor-<?= $id ?>" type="program" class="selection-dropdown"
                    multiple="multiple" onchange="dropdown_onchange_business_rules('<?= $id ?>', 'capability-sponsor', 'program-group-area', 'resource-category')">
                    <option option="Select All">Select All</option>
                    ${capabilitySponsorOptions}
                    </select>`)

                $("#capability-sponsor-<?= $id ?>").select2({
                    placeholder: "Select an option",
                    width: '13vw'
                })

                $(`#capability-sponsor-<?= $id ?>-selection`).prop('disabled', false);
            })
        }
    }

    function reset_dropdown(dropdown_id) {
        $(`#${dropdown_id}-<?= $id ?>`).select2('destroy');
        $(`#${dropdown_id}-<?= $id ?>`).remove();

        $(`#${dropdown_id}-filter`).append(
            `<select id="${dropdown_id}-<?= $id ?>" type="program" class="selection-dropdown"
                multiple="multiple"
                disabled>
                <option></option>
            </select>`)

        $(`#${dropdown_id}-<?= $id ?>`).select2({
            placeholder: "Select an option",
            width: '13vw'
        })

        $(`#${dropdown_id}-<?= $id ?>-selection`).attr('data-select-all', 'true');
        $(`#${dropdown_id}-<?= $id ?>-selection`).html('Select All');
        $(`#${dropdown_id}-<?= $id ?>-selection`).prop('disabled', true);
    }

    function get_dropdown_val(dropdown_id) {
        const group_dropdown_id = `${dropdown_id}-<?= $id ?>`;
        selected_dropdown_val = $(`#${group_dropdown_id}`);

        return selected_dropdown_val.val();
    }

    function get_business_rules_id() {
        return '<?= $id ?>';
    }

    function view_current_selections() {
        selectionButton = $("#view_selection_button")
        const isCurrent = selectionButton.attr('is-current') === 'true';
        const stringifyIsCurrent = isCurrent.toString();
        const selectionTextOptions = {
            'false': 'View Rows of Current Selection',
            'true': 'View Applied Business Rules'
        }
        $(selectionButton).attr('is-current', (!isCurrent).toString());
        $(selectionButton).html(selectionTextOptions[stringifyIsCurrent]);
        if (!isCurrent) {
            $(`#business_rules_history_box`).prop('hidden', false);
            $(`#business_rules_list_outer_box`).prop('hidden', true);
        } else {
            $(`#business_rules_history_box`).prop('hidden', true);
            $(`#business_rules_list_outer_box`).prop('hidden', false);
        }
    }
</script>