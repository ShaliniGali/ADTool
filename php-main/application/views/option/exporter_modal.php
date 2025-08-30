<style>
    .filter-container .select2-selection__rendered  {
        margin: unset !important
    }
</style>


<div>


    <div class="d-flex flex-row filter-container">

        <div id="ass-area-dropdown" class="d-flex flex-column mr-5 mt-3" >
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Assessment Area Code</div>
                <div>
                    <button id="ass-area-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto" 
                    data-select-all="true"
                    type="button" onclick="dropdown_selection_exporter('#ass-area-<?= $id ?>')"> 
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
                    onchange="dropdown_onchange_exporter(1, 'ass-area')"
                    >
                <option option="ALL">ALL</option>
                <?php foreach($ass_area as $value): ?>
                    <option value="<?= $value['ASSESSMENT_AREA_CODE']?>"><?= $value['ASSESSMENT_AREA']?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="program-dropdown" class="d-flex flex-column mr-5 mt-3" >
            <div class="d-flex align-items-center justify-content-between">
                <div class="mb-1 mr-1 bx--label medium-label">Program</div>
                <div>
                    <button id="program-<?= $id ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                    data-select-all="true" disabled
                    type="button" onclick="dropdown_selection_exporter('#program-<?= $id ?>')"> 
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
                onchange="dropdown_onchange_exporter(1, 'program')"
                disabled
            >
                <option></option>
            </select>
        </div>
    </div>

    <div class="d-flex flex-row mx-3 mt-3 mb-1">
        <button id="export_button" class="bx--btn bx--btn--primary">Export</button>
    </div>

</div>

<script>

    $(".selection-dropdown").select2({
        placeholder: "Select an option",
        width: '16vw'
    })

    $('#export_button').on('click', function() {
        const id = 1
        input_object = get_input_object_exporter(id);

        $.ajax({
            type: "POST",
            url: `/socom/resource_constrained_coa/program/export`,
            data: {
                rhombus_token: rhombuscookie(),
                'ass-area': input_object["ass-area"],
                program: input_object["program"],
                use_iss_extract: $('input[name="use_iss_extract"]:checked').val(),
            },
            xhrFields: {
                responseType: 'arraybuffer'
            },
            processData: true,
        }).done(function (response, textStatus, jqXHR) {
            const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const downloadUrl = URL.createObjectURL(blob);
                
                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                cdisposition = jqXHR.getResponseHeader('Content-Disposition');
                filename = cdisposition
                .match(/^inline; filename="(program_export_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}\.xlsx)"$/);
                downloadLink.download = filename[1] ?filename[1]:"exported_file.xlsx";
                document.body.appendChild(downloadLink);
                
                downloadLink.click();
                
                document.body.removeChild(downloadLink);
                URL.revokeObjectURL(downloadUrl);

            $('#exporter_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
            displayToastNotification('success', 'Program Scoring Template Downloaded using Export');
        });

    });

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
 
    function dropdown_selection_exporter(target) {
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
    
    function dropdown_onchange_exporter(id, type, row_id = null) {
        let input_object = {}
        switch(type) {
            case 'ass-area':
                dropdown_all_view_filter(type, id)
                update_program_filter(id);
                break;
            case 'program':
                dropdown_all_view_filter(type, id)
            default:
                break;
            }
    }

    function get_input_object_exporter(id) {
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

    function update_program_filter(id) {
        let input_object = get_input_object_exporter(id);

        
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

                $(`#program-dropdown`).append(
                    `<select 
                    id="program-${id}" 
                    type="program" 
                    combination-id="" 
                    class="selection-dropdown" 
                    multiple="multiple"
                    onchange="dropdown_onchange_exporter(1, 'program')"
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

            });
        
        }
        else {
            $(`#program-${id}`).prop('disabled', true);
        }
    }
</script>