function toggleAllCheckboxes(selectAllToggle) {
    const parent = $(selectAllToggle).closest('.selection-filter-container');
    if($('.manning-force-box .bx--checkbox:not(:checked)',parent).length === 0){
        $('.manning-force-box  .bx--checkbox',parent).prop('checked',false)
        $('button[id*=count]', parent).each(function() {
            $(this).html(0);
        });
    }else{
        $('.manning-force-box  .bx--checkbox',parent).prop('checked',true)
        $('.manning-force-box  li', parent).each(function() {
            let children = $(this).find("ul").children().find('input[type=checkbox]:checked');
            $(this).find('button[id*=count]').html(children.length);
        });
    }

}

function renderFilters(filterData, parentElement, nestingLevel, type, tab_type){
    for(const key in filterData){
        let k = key.split('_');
        let li = document.createElement("li");
        li.classList.add('onlyList');
        let divWrapper = document.createElement("div");
        divWrapper.classList.add('list-checkbox-div')
        if(typeof(filterData[key]) === 'object') {
            li.id = `${type}-${nestingLevel}-${k[0]}-list`;
            renderCheckboxes(parentElement, key, nestingLevel, li, divWrapper, type, tab_type);
        } else {
            li.id = `${type}-${nestingLevel}-${filterData[key].split('_')[0]}-list`;
            renderCheckboxes(parentElement, filterData[key], nestingLevel, li, divWrapper, type, tab_type);
            let onlyButton = createOnlyButton(type,k[0]);
            divWrapper.appendChild(onlyButton);
        }
        
        if(typeof(filterData[key]) === 'object') {
            const subList = document.createElement("ul");
            subList.id = `${type}-child-${nestingLevel+1}-${k[0]}`;
            subList.classList.add('nested-list');
            subList.setAttribute('data-select-type',key);
            li.appendChild(subList);
            renderFilters(filterData[key], subList, nestingLevel+1, type, tab_type);

            //toggle filter visibility
            let toggleButton = toggleExpandButton(subList);
            divWrapper.prepend(toggleButton);
            let onlyButton = createOnlyButton(type,k[0], nestingLevel);
            divWrapper.appendChild(onlyButton);
            let children = $(li).children().find('input:checkbox(:checked)');
            if(children.length > 0){
                let countPill = createCountPill(type, k[0], children.length-1, nestingLevel);
                divWrapper.appendChild(countPill);
            }
        }
    }
}

function renderCheckboxes(parent, label, nestingLevel, list, div, type, tab_type){
    let splitLabel = label.split('_');
    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    let checkboxLabel = document.createElement('label');
    if(nestingLevel == 0) {
        checkbox.setAttribute('data-select-type',splitLabel)
        checkbox.id = `${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
        checkboxLabel.htmlFor = `${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
        checkbox.name = `${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
    } else if(nestingLevel == 1) {
        checkbox.setAttribute('data-select-type',$(parent).closest('ul')[0]['id'].split('-')[1]);
        checkbox.setAttribute ('data-parent-type',$(parent).closest('ul')[0]['id'].split('-')[1]+'_'+splitLabel[0]);
        let parentId = $(parent).closest('ul')[0]['id'].split('-')[1];
        checkbox.id = `${parentId}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
        checkboxLabel.htmlFor = `${parentId}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
        checkbox.name = `${parentId}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
    } else if(nestingLevel == 2) {
        checkbox.setAttribute('data-pcar-select-type',$(parent).parent().parent()[0]['id'].split('-')[1]);
        checkbox.setAttribute('data-mws-select-type',$(parent).parent().parent()[0]['id'].split('-')[1]+'_'+$(parent).parent()[0]['id'].split('-')[1]);
        checkbox.setAttribute ('data-child-type',$(parent).closest('ul')[0]['id'].split('-')[1]+'_'+splitLabel[0]);
        let childParentId =$(parent).parent().parent()[0]['id'].split('-')[1];
        let pID = $(parent).parent()[0]['id'].split('-')[1];
        checkbox.id = `${childParentId}-${pID}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
        checkboxLabel.htmlFor = `${childParentId}-${pID}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
        checkbox.name = `${childParentId}-${pID}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
    }
    checkbox.value = splitLabel[0];

    checkbox.classList.add('bx--checkbox');
    checkbox.classList.add(`all-${type}-checkboxes`);
    checkbox.classList.add('pcar-title');
    checkbox.checked = true;

    if (nestingLevel == 0) {
        label = label.replace('_', ' ');
    }
    
    checkboxLabel.classList.add("bx--checkbox-label");
    checkboxLabel.appendChild(document.createTextNode(label));

    div.appendChild(checkbox);
    div.appendChild(checkboxLabel);
    list.appendChild(div);

    if(nestingLevel > 0){
        list.style.marginLeft = `${nestingLevel * 30}px`
    }
    if(nestingLevel === 0){
        list.style.paddingBottom = '1rem';
    }
    parent.appendChild(list);
    checkbox.onclick = function() {checkboxHandling(this, list);}
}

function createOnlyButton(type,label, nestingLevel) {
    //Pill Button
    let onlyButton = document.createElement('span');
    let buttonElem = document.createElement('button');
    onlyButton.id = `${type}-${label}-${nestingLevel}-only`;
    buttonElem.classList.add('bx--btn');
    buttonElem.classList.add('bx--btn--ghost');
    buttonElem.classList.add('bx--btn--sm');
    buttonElem.classList.add('onlyBtn');
    buttonElem.innerHTML = 'Only';
    buttonElem.onclick = function() {onlyButtonHandleClick(this, type);}
    onlyButton.appendChild(buttonElem);
    return onlyButton;
}

function toggleExpandButton(subList) {
    const toggleButton = document.createElement("img");
    toggleButton.src = "/assets/images/plus_icon.svg"
    toggleButton.classList.add("expand-button");
    toggleButton.addEventListener('click', function(){
        if(subList){
            if(subList.style.display === 'none' || subList.style.display === ""){
                subList.style.display = "block";
                toggleButton.src = "/assets/images/minus_icon.svg"
            } else {
                subList.style.display = "none";
                toggleButton.src = "/assets/images/plus_icon.svg"
            }
        }
    });
    return toggleButton;
}

function createCountPill(type, label, counter, nestingLevel){
    //Count Pill
    let countPill = document.createElement('button');
    countPill.id = `${type}-${label}-${nestingLevel}-count`;
    countPill.classList.add("bx--tag");
    countPill.classList.add("bx--tag--cool-gray");
    countPill.innerHTML = counter;

    return countPill;
}

function onlyButtonHandleClick(el, type) {
    let li = $(el).closest('li');
    let input = $(li).find('input[type=checkbox]')[0];
    let parentElement = $(input).closest('.manning-force-box').attr('id');
    $(`#${parentElement} .all-${type}-checkboxes`).prop('checked', false);
    $(input).click();
    $('.filter-selections li').each(function() {
        let children = $(this).find("ul").children().find('input[type=checkbox]:checked');
        $(this).find('button[id*=count]').html(children.length);
    });
    
}

function checkboxHandling(el, li) {
    $(li).children().find('input[type=checkbox]').prop('checked',el.checked);
    $('.filter-selections li').each(function() {
        let children = $(this).find("ul").children().find('input[type=checkbox]:checked');
        $(this).find('button[id*=count]').html(children.length);
    });
    let parentLi = $(li).parent().get(0).closest('li');
    let accordianLi = $(parentLi).hasClass("bx--accordion__item");
    while(parentLi && !accordianLi){
        let parentCheckbox = $(parentLi).find('input[type=checkbox]')[0];
        if(parentCheckbox){
            let parentChildCheckboxes = $(parentLi).children().next().find('input[type=checkbox]');
            let parentCheckedChildCheckbox = Array.from(parentChildCheckboxes).filter(childCheckbox => childCheckbox.checked);
            if(parentCheckedChildCheckbox.length === 0){
                $(parentCheckbox).prop('checked', el.checked);
                updateCount(parentLi,0);
            } else {
                $(parentCheckbox).prop('checked', true);
                updateCount(parentLi, parentCheckedChildCheckbox.length);
            }
        }
        parentLi = $(parentLi).parent().get(0).closest('li');
        accordianLi = $(parentLi).hasClass("bx--accordion__item");
    }
}

function updateCount(parentLi, count){
    let element = $(parentLi).children().find("button[id*=count]")[0];
    $(element).html(count);
}


function getSelectedFilters(page) {
    // pcar_mws_mds selection
    let pcar_values = get_checked_pcar(page);
    let pcar_mws = {}
    let pcar_mws_mds = {};

    for (let i = 0; i < pcar_values.length; i++) {
        set_checked_mws(pcar_mws, pcar_values, i, page)
    }

    let new_pcar_mws = [];
    for (const key in pcar_mws) {
        let temp1 = key;
        for (const element of pcar_mws[temp1]) {
            new_pcar_mws.push(temp1 + '_' + element);
        }
    }

    for (const element of new_pcar_mws) {
        set_checked_mds(pcar_mws_mds, element, page);
    }

    let pcar_mws_mds_array = [];

    for (const key in pcar_mws_mds) {
        let temp = key;
        let temp1 = key.split('_');
        let formatted_key = temp1[0] + ':' + temp1[1];
        for (const element of pcar_mws_mds[temp]) {
            pcar_mws_mds_array.push(pcar_mws_mds_map[formatted_key + ':' + element]);
        }
    }
    // pcar_mws_mds selection
    // segment_cmd selection
    let selected_segment = get_selected_segment(page);
    let segment_cmd = {};

    for (const element of selected_segment) {
        set_selected_cmd(segment_cmd, element, page);
    }

    let segment_cmd_array = [];
    for (const k in segment_cmd) {
        let temp = k;
        for (const element of segment_cmd[temp]) {
            segment_cmd_array.push(segment_cmd_map[segment_title_map[temp] + ':' + element]);
        }
    }
    // segment_cmd selection
    return [pcar_mws_mds_array, segment_cmd_array];
}