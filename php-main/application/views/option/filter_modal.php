<style>
    .filter-container .select2-selection__rendered  {
        margin: unset !important
    }
</style>
<div>
    <div id="download_buttons" class="d-flex flex-row mb-2">
    <?php if(!isset($page) || $page !== 'optimizer'): ?>
         <div class="form-check form-check-inline complete">
            <input type="checkbox" class="bx--checkbox" value="1" name="optimizer_propogation" id="optimizer_propogation">
            <label for="optimizer_propogation" class="bx--checkbox-label ml-1">Propagate Filter to Optimizer</label>
        </div>
    <?php endif; ?>
    </div>

    <div class="d-flex flex-row filter-container">

        <div id="ass-area-dropdown" class="d-flex flex-column mr-5 mt-3" >
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Assessment Area Code</div>
                <div>
                    <button id="ass-area-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto" 
                    data-select-all="true"
                    type="button" onclick="dropdown_selection_filter('#ass-area-<?= $id ?>')"> 
                        Select All 
                    </button>
                    </div>
            </div>
            <select
                    id="ass-area-<?= $id ?>"
                    type="ass-area"
                    combination-id=""
                    class="selection-dropdown wss-selections"
                    multiple="multiple"
                    onchange="dropdown_onchange_filter(2, 'ass-area')"
                    >
                <option option="ALL">ALL</option>
                <?php foreach($ass_area as $value): ?>
                    <option value="<?= $value['ASSESSMENT_AREA_CODE']?>"><?= $value['ASSESSMENT_AREA']?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="program-dropdown-filter" class="d-flex flex-column mr-5 mt-3" >
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Program</div>
                <div>
                    <button id="program-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                    data-select-all="true" disabled
                    type="button" onclick="dropdown_selection_filter('#program-<?= $id ?>')"> 
                        Select All 
                    </button>
                    </div>
            </div>
            <select 
                id="program-<?= $id ?>" 
                type="program" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange_filter(1, 'program')"
                disabled
            >
                <option></option>
            </select>
        </div>
    </div>

    <div class="d-flex flex-row mx-3 mt-3 mb-1">
        <button id="filter_button" class="bx--btn bx--btn--primary">Filter</button>
    </div>

</div>

<script>

    $('#filter_button').on('click', function() {
        let tableId = $('#option-list').length ? '#option-list' : '#optimizer-table';
        if ($(tableId).length) {
            if (tableId === '#optimizer-table') {
                const select = document.getElementById("ass-area-<?= $id ?>");
                const selectedValues = Array.from(select.selectedOptions).map(option => option.value);
                let regexStringOne = selectedValues.join('|');
                regexStringOne = regexStringOne === "ALL" ? "" : regexStringOne;

                const programSelect = document.getElementById("program-<?= $id ?>");
                const programSelectedValues = Array.from(programSelect.selectedOptions).map(option => option.value);
                let regexStringTwo = programSelectedValues.join('|');
                regexStringTwo = regexStringTwo === "ALL" ? "" : regexStringTwo;

                $(tableId).DataTable().column(10).search(regexStringOne,true).column(11).search(regexStringTwo,true).draw();
                $('#filter_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
                displayToastNotification('success', 'Table Updated using Filter');
            }
            else {
                $(tableId).DataTable().ajax.reload(function() {
                $('#filter_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
                displayToastNotification('success', 'Table Updated using Filter');
                });
            }
        } else {
            console.log('Table not found!');
        }
    });

    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '16vw'
    })

    function show_error(msg) {
        let elem = $('div.bx--inline-notification.bx--inline-notification--error.export_error');
        elem.find('p.bx--inline-notification__subtitle').html(msg);
        elem.removeClass('d-none');
    }

    function hide_error() {
        let elem = $('div.bx--inline-notification.bx--inline-notification--error.export_error');
        elem.find('p.bx--inline-notification__subtitle').html('');
        elem.addClass('d-none');
    }

    
    function dropdown_selection_filter(target) {
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
            dropdown.val('ALL').trigger('change');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
        }
    }
    
    function dropdown_onchange_filter(id, type, row_id = null) {
        let input_object = {}

        switch(type) {
            case 'ass-area':
                dropdown_all_view_filter(type, id)
                update_optimizer_program_filter(id);
                break;
            case 'program':
                dropdown_all_view_filter(type, id)
            default:
                break;
            }
    }

    function get_input_object(id) {
        let input_object = {};

        var capabilitySponsorArray = <?php echo json_encode($capability_sponsor); ?>;
        var pomSponsorArray = <?php echo json_encode($pom_sponsor); ?>;

        input_object["capability_sponsor"] = capabilitySponsorArray
            .filter(sponsor => sponsor.SPONSOR_CODE) 
            .map(sponsor => sponsor.SPONSOR_CODE);
            
        input_object["pom_sponsor"] = pomSponsorArray
            .filter(sponsor => sponsor.SPONSOR_CODE) 
            .map(sponsor => sponsor.SPONSOR_CODE); 

        if ($("#ass-area-" + id).val() != "" && $("#ass-area-" + id).val() != null) {
            input_object["ass-area"] = fetch_all_inputs(`#ass-area-${id}`)
        }

        if ($('#program-' + id).val() != "" && $('#program-' + id).val() != null) {
            input_object["program"] = fetch_all_inputs(`#program-${id}`)
        }

        return input_object;
    } 

    function update_optimizer_program_filter(id) {
        let input_object = get_input_object(id);
        
        const programSelectionButton = `#program-${id}-selection`;
        $(programSelectionButton).attr('data-select-all', 'true');
        $(programSelectionButton).html(selectionTextOptions['false']);
        $(programSelectionButton).attr('disabled', true);

        if ($(`#program-${id}`).val().length) {
            $(`#program-${id}`).val(null).trigger('change')
        }
        if (input_object["ass-area"]) {
            $(`#program-${id}`).attr('disabled', true);
            $.post("/socom/program_group/filter/update", {
                rhombus_token: rhombuscookie(),
                pom: input_object["pom_sponsor"],
                cs: input_object["capability_sponsor"] ,
                'ass-area': input_object["ass-area"]
            }, function(data) {
                let programList = data['data'];
                let programOptions = '';
                programList.forEach( v => {
                    programOptions += `<option value="${v['PROGRAM_GROUP']}">${v['PROGRAM_GROUP']}</option>`;
                });
                $(`#program-${id}`).remove();

                $(`#program-dropdown-filter`).append(
                    `<select 
                    id="program-${id}" 
                    type="program" 
                    combination-id="" 
                    class="selection-dropdown" 
                    multiple="multiple"
                    onchange="dropdown_onchange_filter(2, 'program')"
                >
                    <option option="ALL">ALL</option>
                    ${programOptions}
                </select>`)

                $(`#program-${id}`).select2({
                    placeholder: "Select an option",
                    width: '16vw'
                }).on('change.select2', function() {
                        var dropdown = $(this).siblings('span.select2-container');
                        if (dropdown.height() > 100) {
                            dropdown.css('max-height', '100px');
                            dropdown.css('overflow-y', 'auto');
                        }
                })
                $(`#program-${id}`).prop('disabled', false);
                $(programSelectionButton).prop('disabled', false);

            })
        
        }
        else {
            $(`#program-${id}`).prop('disabled', true);
        }
    }
</script>