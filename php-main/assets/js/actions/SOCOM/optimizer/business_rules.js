function applyBusinessRules(iel) {

    const selectedProgram = get_dropdown_val('program-group-area');
    const selectedCapabilitySponsor = get_dropdown_val('capability-sponsor');
    const selectedResourceCategory = get_dropdown_val('resource-category');

    id = get_business_rules_id();

    const selectedRule = document.getElementById("rule-select").value;

    if ($(`#resource-category-${id}`).is(':disabled') || $(`#capability-sponsor-${id}`).is(':disabled')) {
        displayToastNotification('error', 'Select All Options to Proceed')
    }
    else if (selectedProgram.length === 0 || selectedCapabilitySponsor.length === 0
        || selectedResourceCategory.length === 0 || selectedRule === '0') {
        displayToastNotification('error', 'Select All Options to Proceed')
    }
    else if (!saveHistory(selectedProgram, selectedResourceCategory, selectedCapabilitySponsor, selectedRule)) {
        displayToastNotification('error', 'Business Rule Applied Already')
    }
    else {
        let filtered_table = $('#optimizer-table').DataTable();
        var count = 0;
        var count = 0;
        if (selectedRule === 'Priority') {
            filtered_table.rows().every(function () {
                var rowData = this.data();

                const programMatch = selectedProgram.includes('Select All') || selectedProgram.includes(rowData['PROGRAM_GROUP']);
                const resourceCategoryMatch = selectedResourceCategory.includes('Select All') || selectedResourceCategory.includes(rowData['RESOURCE_CATEGORY_CODE']);
                const capabilitySponsorMatch = selectedCapabilitySponsor.includes('Select All') || selectedCapabilitySponsor.includes(rowData['CAPABILITY_SPONSOR_CODE']);

                if (programMatch && resourceCategoryMatch && capabilitySponsorMatch) {
                    var cell0 = this.cell({ row: this.index(), column: 0 }).node(),
                        check0 = $(cell0).find('input[type="checkbox"][name="include[]"]');
                    if (check0.prop('checked') == false) {
                        check0.prop('checked', true);
                        toggleOptimizerCheck(check0[0], iel);
                    }
                    check0.checked = true;
                    count++;
                }
            });

        } else {
            filtered_table.rows().every(function () {
                var rowData = this.data();

                const programMatch = selectedProgram.includes('Select All') || selectedProgram.includes(rowData['PROGRAM_GROUP']);
                const resourceCategoryMatch = selectedResourceCategory.includes('Select All') || selectedResourceCategory.includes(rowData['RESOURCE_CATEGORY_CODE']);
                const capabilitySponsorMatch = selectedCapabilitySponsor.includes('Select All') || selectedCapabilitySponsor.includes(rowData['CAPABILITY_SPONSOR_CODE']);

                if (programMatch && resourceCategoryMatch && capabilitySponsorMatch) {
                    var cell1 = this.cell({ row: this.index(), column: 1 }).node(),
                        check1 = $(cell1).find('input[type="checkbox"][name="exclude[]"]');
                    if (check1.prop('checked') == false) {
                        check1.prop('checked', true);
                        toggleOptimizerCheck(check1[0], iel);
                    }

                    count++;
                }
            });
        }

        $('#business_rules_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
        displayToastNotification('success', `Business Rules Applied to ${count} Rows`);
    }
}

//Add Newly Applied Rule to Business Rules Histroy Table
function saveHistory(program_group, resource_category, capability_sponsor, rule) {
    let business_rules_history_table = $('#business-rules-history-table').DataTable();

    let rowExists = false;

    let maxId = 0;

    business_rules_history_table.rows().every(function () {
        let data = this.data();
        if (
            data[0].toString() === program_group.toString() &&
            data[1].toString() === resource_category.toString() &&
            data[2].toString() === capability_sponsor.toString() &&
            data[3] === rule
        ) {
            rowExists = true;
            return false;
        }

        //keep track of highest button ID
        const match = data[4]?.match(/id="undo-btn-(\d+)"/);

        if (match) {
            const idNum = parseInt(match[1], 10);
            if (!isNaN(idNum)) maxId = Math.max(maxId, idNum);
        }
    });

    if (!rowExists) {
        const newId = `undo-btn-${maxId + 1}`;

        business_rules_history_table.row.add([
            program_group,
            resource_category,
            capability_sponsor,
            rule,
            `<button id="${newId}"
                class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto"
                type="button" onclick="">
                Undo
            </button>`
        ]).draw(false);
        return true;
    }

}

//Undo Business Rule Selections
function undoBusinessRule(buttonId, iel) {
    const table = $('#business-rules-history-table').DataTable();
    const button = document.getElementById(buttonId);
    const row = table.row($(button).closest('tr'));
    const rowData = row.data();

    const selectedProgram = rowData[0];
    const selectedResourceCategory = rowData[1];
    const selectedCapabilitySponsor = rowData[2];

    let count = 0;

    $('#optimizer-table').DataTable().rows().every(function () {
        var currentRowData = this.data();

        const programMatch = selectedProgram.includes('Select All') || selectedProgram.includes(currentRowData['PROGRAM_GROUP']);
        const resourceCategoryMatch = selectedResourceCategory.includes('Select All') || selectedResourceCategory.includes(currentRowData['RESOURCE_CATEGORY_CODE']);
        const capabilitySponsorMatch = selectedCapabilitySponsor.includes('Select All') || selectedCapabilitySponsor.includes(currentRowData['CAPABILITY_SPONSOR_CODE']);

        if (programMatch && resourceCategoryMatch && capabilitySponsorMatch) {
            var cell0 = this.cell({ row: this.index(), column: 0 }).node(),
                check0 = $(cell0).find('input[type="checkbox"][name="include[]"]');
            if (check0.prop('checked') == true) {
                check0.prop('checked', false);
                toggleOptimizerCheck(check0[0], iel);
            }

            var cell1 = this.cell({ row: this.index(), column: 1 }).node(),
                check1 = $(cell1).find('input[type="checkbox"][name="exclude[]"]');
            if (check1.prop('checked') == true) {
                check1.prop('checked', false);
                toggleOptimizerCheck(check1[0], iel);
            }


            count++;
        }
    });

    row.remove().draw(false);
    displayToastNotification('success', `Business Rules Cleared for ${count} Rows`);
}

function clearBusinessRules(iel) {
    $("#business-rules-table").DataTable().clear().draw();
    $("#business-rules-history-table").DataTable().clear().draw();
    reset_dropdown('capability-sponsor');
    reset_dropdown('resource-category');
    reset_dropdown('program-group-area');
    get_program_group();

    $('#optimizer-table').DataTable().rows().every(function () {

        var cell0 = this.cell({ row: this.index(), column: 0 }).node(),
            check0 = $(cell0).find('input[type="checkbox"][name="include[]"]');
        if (check0.prop('checked') == true) {
            check0.prop('checked', false);
            toggleOptimizerCheck(check0[0], iel);
        }

        var cell1 = this.cell({ row: this.index(), column: 1 }).node(),
            check1 = $(cell1).find('input[type="checkbox"][name="exclude[]"]');
        if (check1.prop('checked') == true) {
            check1.prop('checked', false);
            toggleOptimizerCheck(check1[0], iel);
        }
    });

}

if (!window._rb) { window._rb = {}; }
window._rb.applyBusinessRules = applyBusinessRules;
window._rb.undoBusinessRule = undoBusinessRule;
window._rb.saveHistory = saveHistory;
window._rb.clearBusinessRules = clearBusinessRules;