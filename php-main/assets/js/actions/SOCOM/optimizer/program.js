let load_program_table = function() {
    load_program_table = null;
    $(`#option-list`).DataTable({
        stateSave: true,
        columnDefs: [{
                targets: 0,
                data: 'PROGRAM_CODE',
                name: "Program",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 1,
                data: 'CAPABILITY_SPONSOR_CODE',
                name: "Capability Sponsor",
                defaultContent: '',
                searchable: true
            },
            {
                targets: 2,
                data: 'RESOURCE_CATEGORY_CODE',
                name: 'Resource Category Code',
                defaultContent: '0',
                visible: true,
                searchable: false
            },
            {
                targets: 3,
                data: 'FY',
                name: "2026",
                defaultContent: '',
                searchable: true,
                render: function(data) {
                    if (data && typeof data === 'string') {
                        try {
                            const fyData = JSON.parse(data);
                            return fyData['2026'] || '';
                        } catch (e) {
                            return '';
                        }
                    }
                    return '';
                }
            },
            {
                targets: 4,
                data: 'FY',
                name: "2027",
                defaultContent: '',
                searchable: true,
                render: function(data) {
                    if (data && typeof data === 'string') {
                        try {
                            const fyData = JSON.parse(data);
                            return fyData['2027'] || '';
                        } catch (e) {
                            return '';
                        }
                    }
                    return '';
                }
            },
            {
                targets: 5,
                data: 'FY',
                name: "2028",
                defaultContent: '',
                searchable: true,
                render: function(data) {
                    if (data && typeof data === 'string') {
                        try {
                            const fyData = JSON.parse(data);
                            return fyData['2028'] || '';
                        } catch (e) {
                            return '';
                        }
                    }
                    return '';
                }
            },
            {
                targets: 6,
                data: 'FY',
                name: "2029",
                defaultContent: '',
                searchable: true,
                render: function(data) {
                    if (data && typeof data === 'string') {
                        try {
                            const fyData = JSON.parse(data);
                            return fyData['2029'] || '';
                        } catch (e) {
                            return '';
                        }
                    }
                    return '';
                }
            },
            {
                targets: 7,
                data: 'FY',
                name: "2030",
                defaultContent: '',
                searchable: true,
                render: function(data) {
                    if (data && typeof data === 'string') {
                        try {
                            const fyData = JSON.parse(data);
                            return fyData['2030'] || '';
                        } catch (e) {
                            return '';
                        }
                    }
                    return '';
                }
            },
            {
                targets: 8,
                data: 'PROGRAM_GROUP',
                name: "Program Group",
                defaultContent: '',
                visible: true,
                searchable: true
            },
            {
                targets: 9,
                data: 'POM_SPONSOR_CODE',
                name: "POM",
                defaultContent: '0',
                visible: true,
                searchable: false
            },
            {
                targets: 10,
                data: 'ASSESSMENT_AREA_CODE',
                name: "Assessment Area",
                defaultContent: '',
                visible: true,
                searchable: false
            },
            {
                targets: 11,
                data: 'storm_id',
                name: "StoRM ID",
                defaultContent: '0',
                visible: true,
                searchable: false
            },
            {
                targets: 12,
                data: 'storm',
                name: "StoRM Score",
                defaultContent: '0',
                visible: true,
                searchable: false
            },
            {
                targets: 13,
                data: null,
                name: "Weights: POM",
                defaultContent: '',
                visible: true,
                searchable: false,
                orderable: false
            },
        ],
        ajax: {
            url: "/socom/resource_constrained_coa/program/list/get",
            type: 'POST',
            data: function() {                                                                             
                return {
                    optimizer_propogation: $('#optimizer_propogation').is(':checked'),
                    'ass-area': fetch_all_inputs('#ass-area-2'),
                    program: fetch_all_inputs('#program-2', true),
                    use_iss_extract: $('input[name="use_iss_extract"]:checked').val(),
                    rhombus_token: rhombuscookie()
                };
            },
            dataSrc: function (json) {
                $('input[name="use_iss_extract"]').prop('disabled', false);
                $('input[name="use_iss_extract"]').next('label').removeClass('bx--tile--is-selected');
                $('input[name="use_iss_extract"]:checked').next('label').addClass('bx--tile--is-selected');
                $('div[aria-labelledby=loading-id-3]').addClass('d-none');

                if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
                    $('#issue_coa_header').removeClass('d-none');
                    $('#resource_coa_header').addClass('d-none');
                } else {
                    $('#issue_coa_header').addClass('d-none');
                    $('#resource_coa_header').removeClass('d-none');
                }
                
                let fy_list = json.year_list ?? fy_list;
                let scores = json.scores ?? [];

                for (let i in json.data) {
                    let FY = JSON.parse(json.data[i]['FY'])
                    let c = 2;
                    for (let yi of fy_list) {
                        json.data[i]['col_' + c] = (Number.isSafeInteger(FY[yi]) ? parseInt(FY[yi]) : 0);
                        c++;
                    }

                    // Store PROGRAM_ID for use in rowCallback
                    json.data[i]['PROGRAM_ID_FOR_DISPLAY'] = String(json.data[i]['PROGRAM_ID']);

                    // Keep the original field names for the new column structure
                    // The DataTable column definitions now use EVENT_NAME, PROGRAM_CODE, etc. directly
                    
                    json.data[i]['SCORE_ID'] = scores?.[json.data[i]['PROGRAM_ID']]?.['SCORE_ID'] ?? JSON.stringify('');
                    json.data[i]['SCORE_SESSION'] = scores?.[json.data[i]['PROGRAM_ID']]?.['SCORE_SESSION'] ?? JSON.stringify('');
                }

                return json.data;
            }
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px',
        initComplete: function() {
            storm_iss_rc_check($('input[name="storm_weighted_based"]')[0]);
        },
        headerCallback: function(thead, data, start, end, display){
            let children = $(thead).next().children(':gt(1)');
            if (
                $('input[name="use_iss_extract"]:checked').val() !== "true" &&
                $('input[name="storm_weighted_based"]:checked').val() !== "1"
            ) {
                children = $(thead).next().children(":gt(2)");
            }
            let visible = $(`#option-list`).DataTable().columns().visible();
            
            for (let yi in fy_list) {
                children[yi].innerHTML = '<b>' + parseInt(fy_list[yi]) + '</b>';
            }

            if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
                $(thead).next().find('th:eq(0)').html('Event Name');
                $(thead).next().find('th:eq(1)').html('Program');
            } else {
                $(thead).next().find('th:eq(0)').html('Program');
                $(thead).next().find('th:eq(1)').html('Capability Sponsor');
                if ($('input[name="storm_weighted_based"]:checked').val() !== '1') {
                    $(thead).next().find('th:eq(2)').html('Resource Category Code');
                }
            }

            if (visible[7]) {
                $(thead).next().find("th:eq(8)").attr('style', 'max-width: 150px; width: 150px !important;');
            }
        },
        rowCallback: function(row, data) {
            let SESSION = JSON.parse(data['SCORE_SESSION']),
                visible = $(`#option-list`).DataTable().columns().visible();
            let scoreCol = 8;
            let pomCol = 9;
            let gCol = 10;
            if ($('input[name="storm_weighted_based"]:checked').val() === '1') {
                scoreCol = 7;
                pomCol = 8;
                gCol = 9;
            }
            
            let type_of_coa = $('input[name="use_iss_extract"]:checked').val() === 'true' ? 'ISS_EXTRACT' : 'RC_T';
            let program_id = covertToProgramId(
                type_of_coa,
                {
                    'program_code': data['PROGRAM_CODE'] ?? '',
                    'cap_sponsor': data['CAPABILITY_SPONSOR_CODE'] ?? '',
                    'pom_sponsor': data['POM_SPONSOR_CODE'] ?? '',
                    'ass_area_code': data['ASSESSMENT_AREA_CODE'] ?? '',
                    'execution_manager': data['EXECUTION_MANAGER_CODE'] ?? '',
                    'resource_category': data['RESOURCE_CATEGORY_CODE'] ?? '',
                    'eoc_code': data['EOC_CODE'] ?? '',
                    'osd_pe_code': data['OSD_PROGRAM_ELEMENT_CODE'] ?? '',
                    'event_name': data['EVENT_NAME'] ?? ''
                },
                false
            );

            if (visible[scoreCol]) {
                let btnScoreTxt;
                if (typeof data['SCORE_ID'] === 'number') {
                    btnScoreTxt = 'Edit Score';
                    $(`td:eq(${scoreCol})`, row).data("SCORE_ID", parseInt(data['SCORE_ID']));
                } else {
                    btnScoreTxt = 'Add Score';
                    $(`td:eq(${scoreCol})`, row).data("SCORE_ID", null);
                }

                $(`td:eq(${scoreCol})`, row).html(`<button class="bx--btn bx--btn--primary" type="button">${btnScoreTxt}</button>`);
                $(`td:eq(${scoreCol}) button`, row).on('click', showScore);
                
                $(`td:eq(${scoreCol})`, row).data("PROGRAM_ID", data['PROGRAM_ID']);
                if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
                    $(`td:eq(${scoreCol})`, row).data("EVENT_NAME", data['EVENT_NAME']);
                    $(`td:eq(${scoreCol})`, row).data("PROGRAM_CODE", data['PROGRAM_CODE']);
                } else {
                    $(`td:eq(${scoreCol})`, row).data("CAPABILITY_SPONSOR_CODE", data['CAPABILITY_SPONSOR_CODE']);
                    $(`td:eq(${scoreCol})`, row).data("PROGRAM_CODE", data['PROGRAM_CODE']);
                }

                $(`td:eq(${scoreCol})`, row).data("PROGRAM_NAME_TXT", program_id);
                $(`td:eq(${scoreCol})`, row).css({
                    "max-width": "150px",
                });
            }

            // The DataTable column definitions now handle the display automatically
            // No need to manually set HTML content as the columns are configured with the correct data fields
            
            if (visible[pomCol] && visible[gCol]) {
                if (SESSION !== null) {
                    let pomData = selected_POM_weight_table.rows().data()[0];

                    let guidanceData = selected_Guidance_weight_table.rows().data()[0];
                    
                    if (
                        (!(pomData instanceof Object) || typeof pomData[default_criteria[0]] !== 'string') ||  
                        (!(guidanceData instanceof Object) || typeof guidanceData[default_criteria[0]] !== 'string')
                    ) {
                        return data;
                    }

                    let pomTotal = 0;
                    Object.keys(SESSION).forEach(pKey => {
                        let pKeyR = pKey
                        let multi = parseFloat(SESSION[pKey]) * parseFloat(pomData[pKeyR]);
                        pomTotal += multi
                    });

                    let gTotal = 0;
                    Object.keys(SESSION).forEach(pKey => {
                        let pKeyR = pKey
                        let multi = parseFloat(SESSION[pKey]) * parseFloat(guidanceData[pKeyR]);
                        gTotal += multi
                    });

                    $(`td:eq(${pomCol})`, row).html('<b>' + gTotal.toFixed(2) + '</b>');
                    $(`td:eq(${gCol})`, row).html('<b>' + pomTotal.toFixed(2) + '</b>');
                }
            }
        }

    });
}

function storm_iss_rc_check(elem) {
    if ($('input[name="use_iss_extract"]:checked').val() === 'true') {
        storm_weighted_based('#option-list', [12,13], [2, 8, 9, 10], elem, false);
    } else {
        storm_weighted_based('#option-list', [2,13], [8, 9, 10, 12], elem, false);
    }

    $('#pom-table').DataTable().draw();
    $('#guidance-table').DataTable().draw();
    $('#option-list').DataTable().columns.adjust().draw();
}

function weighted_iss_rc_check(elem) {
    storm_weighted_based('#option-list', [8, 9, 10], [2,12,13], elem, true);

    $('#pom-table').DataTable().draw();
    $('#guidance-table').DataTable().draw();
    $('#option-list').DataTable().columns.adjust().draw();
}

function attach_change_handler() {
    $('input[name="storm_weighted_based"]').on('change', function() {
        if (this.id === 'r-w') {
            $(`#option-list`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get');
            weighted_iss_rc_check(this);
        } else {
            $(`#option-list`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get');
            storm_iss_rc_check(this);
        }
    });
}

function dropdown_onchange_export(id, type, row_id = null) {
    let input_object = {}
    switch(type) {
        case 'ass-area':
            dropdown_all_view(type, id)
            update_program_filter(id);
            break;
        default:
            break;
        }
}

let lastSelectedItemsMap = {
    'pom': [],
    'ass-area': [],
    'cs': [],
    'program': []
 }
 const selectionTextOptions = {
    'false': 'Select All',
    'true': 'Deselect All'
 }
 
 
 function selectionHasChanged(id) {
    const lastSelections = lastSelectedItemsMap[id];
    return lastSelections.includes("ALL")
 }
 
function dropdown_all_view_filter(type, id) {
    const dropdown_id = `${type}-${id}`;
    const dropdown = $(`#${dropdown_id}`);
    let allElement = $(`ul#select2-${dropdown_id}-container > li[title="ALL"]`);
    if (allElement.length) {
        allElement = allElement[0].outerHTML;
    }
    let selected_values = dropdown.val();
    let isSelectAll = !(selected_values.length === 0);
    const selectionButton = `#${dropdown_id}-selection`;

    const stringifyIsSelectAll = isSelectAll.toString();

    const selectionTextOptions = {
        'false': 'Select All',
        'true': 'Deselect All'
    }    

    const changeSelectAll = $(selectionButton).attr('data-select-all') !== selectionTextOptions[stringifyIsSelectAll];
    if (changeSelectAll) {        
        $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
        $(selectionButton).html(selectionTextOptions[stringifyIsSelectAll]);
    }
    
    if(selectionHasChanged(type) && selected_values.length > 0){
        const allIndex = selected_values.indexOf("ALL");
        if (allIndex !== -1) {
            selected_values.splice(allIndex, 1);
        }
        $(`ul#select2-${dropdown_id}-container > li[title="ALL"]`).remove()
    }
    else if (selected_values.includes("ALL")){
        selected_values = ['ALL'];
        const otherSelected = $(`ul#select2-${dropdown_id}-container > li.select2-selection__choice`);
        otherSelected.remove()
        $(`ul#select2-${dropdown_id}-container`).prepend(allElement)
    }
    lastSelectedItemsMap[type] = selected_values;
    dropdown.val(selected_values)
}

function fetch_all_inputs(id, emptyAll = false) {
    let select2val =  $(id).val();
    if((emptyAll === false && select2val.includes('ALL')) && select2val.length > 0){
        return $(`${id} > option`).map(function(){
            if(this.value != 'ALL' && this.value != ''){
                return this.value;
            }
        }).get();
    }
    return (select2val == 'ALL' && emptyAll === true ? [] : select2val);
}

function get_input_object(id) {
    let input_object = {};

    if ($("#ass-area-" + id).val() != "" && $("#ass-area-" + id).val() != null) {
        input_object["ass-area"] = fetch_all_inputs(`#ass-area-${id}`)
    }
    return input_object;
}

function update_program_filter(id) {
    let input_object = get_input_object(id);
    
    const programSelectionButton = `#program-${id}-selection`;
    $(programSelectionButton).attr('data-select-all', 'true');
    $(programSelectionButton).html(selectionTextOptions['false']);
    $(programSelectionButton).attr('disabled', true);

    if ($(`#program-${id}`).val().length) {
        $(`#program-${id}`).val(null).trigger('change')
    }
    if (input_object["ass-area"] && input_object["cs"] && input_object["pom"]) {
        $(`#program-${id}`).attr('disabled', true);
        $.post("/socom/program_group/filter/update", {
            rhombus_token: rhombuscookie(),
            pom: input_object["pom"],
            cs: input_object["cs"],
            'ass-area': input_object["ass-area"]
        }, function(data) {
            let programList = data['data'];
            let programOptions = '';
            programList.forEach( v => {
                programOptions += `<option value="${v['PROGRAM_GROUP']}">${v['PROGRAM_GROUP']}</option>`;
            });
            $(`#program-${id}`).select2('destroy')

            $(`#program-${id}`).remove();

            $(`#program-dropdown`).append(
                `<select 
                id="program-${id}" 
                type="program" 
                combination-id="" 
                class="selection-dropdown" 
                multiple="multiple"
                onchange="dropdown_onchange_export(1, 'program')"
                disabled
            >
                <option option="ALL">ALL</option>
                ${programOptions}
            </select>`)

            $(`#program-${id}`).select2({
                placeholder: "Select an option",
                width: '16vw'
            })
            .on('change.select2', function() {
                    var dropdown = $(this).siblings('span.select2-container');
                    if (dropdown.height() > 100) {
                        dropdown.css('max-height', '100px');
                        dropdown.css('overflow-y', 'auto');
                    }
            })
            $(`#program-${id}`).attr('disabled', false);
            $(programSelectionButton).attr('disabled', false);
        })
    }
    else {
        $(`#program-${id}`).attr('disabled', true);
    }
}

function onReady() { 
    attach_change_handler();
    
    // Initialize the DataTable with a small delay to ensure DOM is ready
    setTimeout(function() {
        load_program_table();
    }, 100);
    
    $('#option_filter').on('click', function() {
        
        $('#filter_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
        $(`#ass-area-2`).select2({
            placeholder: "Select an option",
            width: '16vw'
        }).on('change.select2', function() {
            var dropdown = $(this).siblings('span.select2-container');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
        })
    });


    $('#option_exporter').on('click', function() {
    
        $('#exporter_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

        $(`#ass-area-1`).select2({
            placeholder: "Select an option",
            width: '16vw'
        }).on('change.select2', function() {
            var dropdown = $(this).siblings('span.select2-container');
            if (dropdown.height() > 100) {
                dropdown.css('max-height', '100px');
                dropdown.css('overflow-y', 'auto');
            }
        })
    });


    const $radioButtons = $('input[name="use_iss_extract"]');
    $radioButtons.on('change', function () {
        $radioButtons.prop('disabled', true);
        $('div[aria-labelledby=loading-id-3]').removeClass('d-none');
        if ($('input[name="storm_weighted_based"]:checked').val() === '1') {
            weighted_iss_rc_check(this);
        } else {
            storm_iss_rc_check(this);
        }
        $('#option-list').DataTable().ajax.reload();
    });

    $('#option_exporter').on('click', function() {
		$('#exporter_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
	});

	$('#option_filter').on('click', function() {
		$('#filter_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
	});
};

$(onReady);

if(!window._rb) window._rb = {};
window._rb = {
    load_program_table,
    attach_change_handler,
    dropdown_all_view_filter,
    dropdown_onchange_export,
    attach_change_handler,
    onReady
}