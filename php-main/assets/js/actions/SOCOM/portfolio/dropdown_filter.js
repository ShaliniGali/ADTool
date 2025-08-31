let selectionTextOptions = {
    'false': 'Select All',
    'true': 'Deselect All'
}

let lastSelectedItemsMap = {}
let prevFilterWithinLimit;

function dropdown_onchange(type) {
    switch(type) {
        case 'compare-programs':
            dropdown_all_view(type);
            prevFilterWithinLimit = check_filter_limit(type, 2);
            if (prevFilterWithinLimit){
                compareProgramsOnReady();
            }
            break;
        case 'budget-trend-overview':
            dropdown_all_view(type);
            fetchChartDataAmount();

            fetchChartDataSelectedProgram(type);
            break;
        case 'fielding-component':
            dropdown_all_view(type);
            updateFieldingInformationText();
            fetchFieldingTableData(type);
            break;
        case 'funding-resource-category':
            dropdown_all_view(type);
            updateProgramExecutionDrilldownAMSGraph(type);
            break;
        default:
            break;
    }
}

function dropdown_all_view(type) {
    const dropdown = $(`#${type}`);
    let allElement = $(`ul#select2-${type}-container > li[title="ALL"]`);
    if (allElement.length) {
        allElement = allElement[0].outerHTML;
    }
    let selected_values = dropdown.val();
    let isSelectAll = !(selected_values.length === 0);
    const selectionButton = `#${type}-selection`;

    const stringifyIsSelectAll = isSelectAll.toString();

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
        $(`ul#select2-${type}-container > li[title="ALL"]`).remove();
    }
    else if (selected_values.includes("ALL")){
        selected_values = ['ALL'];
        const otherSelected = $(`ul#select2-${type}-container > li.select2-selection__choice`);
        otherSelected.remove();
        $(`ul#select2-${type}-container`).prepend(allElement);
    }
    lastSelectedItemsMap[type] = selected_values;
    dropdown.val(selected_values);
}

function selectionHasChanged(id) {
    const lastSelections = lastSelectedItemsMap[id];
    return lastSelections.includes("ALL");
}

function dropdown_selection(target) {
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

function get_dropdown_filter_input(type, input_key) {
    let input_object = {};

    if ($(`#${type}`).val() != "" && $(`#${type}`).val() != null) {
        input_object[input_key] = fetch_all_inputs(`#${type}`);
    }

    return input_object;
}

function fetch_all_inputs(id) {
let select2val =  $(id).val();
if(select2val.includes('ALL') && select2val.length > 0){
    return $(`${id} > option`).map(function(){
        if(this.value != 'ALL' && this.value != ''){
            return this.value;
        }
    }).get();
}
return select2val;
}

function update_filter_options(type, options, defaultSelectAll=false) {
    let optionElements = '';
    options.forEach(option => {
        optionElements += `<option value="${option}">${option}</option>`;
    });

    $(`#${type}`).select2('destroy')
    $(`#${type}`).remove();

    let selected = '';
    if (defaultSelectAll) {
        selected = 'selected';
    } 

    $(`#${type}-dropdown`).append(
        `<select 
        id="${type}" 
        type="${type}" 
        combination-id="" 
        class="selection-dropdown" 
        multiple="multiple"
        onchange="dropdown_onchange('${type}')"
    >
        <option value="ALL" ${selected}>ALL</option>
        ${optionElements}
    </select>`)

    $(`#${type}`).select2({
        placeholder: "Select an option",
        width: '17vw'
    })
    .on('change.select2', function() {
        let dropdown = $(this).siblings('span.select2-container');
        if (dropdown.height() > 100) {
            dropdown.css('max-height', '100px');
            dropdown.css('overflow-y', 'auto');
        }
    });

    if (defaultSelectAll) {
        $(`#${type}`).val('ALL').trigger('change');
        if ( $(`#${type}`).val().length == 0 ) {
            $(`#${type}`).val('ALL').trigger('change');
        }
    }
}
function check_filter_limit(type, limit) {
    const $dropdown = $(`#${type}`);

    const filterErrorId = `${type}-filter-error`;
    const $filterError = $(`#${filterErrorId}`);

    if ($dropdown.val().length > limit) {
        
        if ($filterError.length === 0) {
            const filterText = `filter${limit > 1 ? "s" : ""}`;
            $(`#${type}-dropdown`).append(
                `<div id="${filterErrorId}" class="alert alert-warning mb-0 mt-2" style="width: 16vw;">
                    You can only apply up to ${limit} ${filterText}. Please remove some ${filterText} to proceed.
                </div>`
            );
        }
        return false;
    } else {
        if ($filterError.length) {
            $filterError.remove();
        }
        return true;
    }
}