function toggleAllCheckboxes(selectAllToggle) {
  const parent = $(selectAllToggle).closest('.selection-filter-container');
  if ($('.manning-force-box .bx--checkbox:not(:checked)', parent).length === 0) {
    $('.manning-force-box .bx--checkbox', parent).prop('checked', false);
    $('button[id*=count]', parent).each(function() {
      $(this).html(0);
    });
  } else {
    $('.manning-force-box .bx--checkbox', parent).prop('checked', true);
    $('.manning-force-box li', parent).each(function() {
      let children = $(this)
        .find("ul")
        .children()
        .find('input[type=checkbox]:checked');
      $(this).find('button[id*=count]').html(children.length);
    });
  }
}

function renderFilters(filterData, parentElement, nestingLevel, type, tab_type) {
  for (const key in filterData) {
    let k = key.split('_');
    let li = document.createElement("li");
    li.classList.add('onlyList','bx--form-item',`lvl-${nestingLevel+1}`);

    let divWrapper = document.createElement("div");
    divWrapper.classList.add('list-checkbox-div');

    if (typeof(filterData[key]) === 'object') {
      li.id = `${type}-${nestingLevel}-${k[0]}-list`;
      renderCheckboxes(parentElement, key, nestingLevel, li, divWrapper, type, tab_type);

      const subList = document.createElement("ul");
      subList.id = `${type}-child-${nestingLevel+1}-${k[0]}`;
      subList.classList.add('nested-list');
      subList.setAttribute('data-select-type', key);
      li.appendChild(subList);

      renderFilters(filterData[key], subList, nestingLevel+1, type, tab_type);

      let onlyButton = createOnlyButton(type, k[0], nestingLevel);
      divWrapper.appendChild(onlyButton);

      let children = $(li).children().find('input:checkbox:checked');
      if (children.length > 0) {
        let countPill = createCountPill(type, k[0], children.length-1, nestingLevel);
        divWrapper.appendChild(countPill);
      }

      let toggleButton = toggleExpandButton(subList);
      divWrapper.appendChild(toggleButton);

    } else {
      li.id = `${type}-${nestingLevel}-${filterData[key].split('_')[0]}-list`;
      renderCheckboxes(parentElement, filterData[key], nestingLevel, li, divWrapper, type, tab_type);

      let onlyButton = createOnlyButton(type, k[0]);
      divWrapper.appendChild(onlyButton);
    }

    parentElement.appendChild(li);
  }
}

function renderCheckboxes(parent, label, nestingLevel, list, div, type, tab_type) {
  let splitLabel = label.split('_');
  let checkbox = document.createElement("input");
  checkbox.type = "checkbox";
  let checkboxLabel = document.createElement('label');

  if (nestingLevel === 0) {
    checkbox.setAttribute('data-select-type', splitLabel);
    checkbox.id   = `${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
    checkboxLabel.htmlFor = checkbox.id;
    checkbox.name = checkbox.id;

  } else if (nestingLevel === 1) {
    let parentId = $(parent).closest('ul')[0].id.split('-')[1];
    checkbox.setAttribute('data-select-type', parentId);
    checkbox.setAttribute('data-parent-type', parentId+'_'+splitLabel[0]);
    checkbox.id   = `${parentId}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
    checkboxLabel.htmlFor = checkbox.id;
    checkbox.name = checkbox.id;

  } else {
    let gp = $(parent).parent().parent()[0].id.split('-')[1];
    let p  = $(parent).parent()[0].id.split('-')[1];
    checkbox.setAttribute('data-pcar-select-type', gp);
    checkbox.setAttribute('data-mws-select-type', gp+'_'+p);
    checkbox.setAttribute(
      'data-child-type',
      $(parent).closest('ul')[0].id.split('-')[1]+'_'+splitLabel[0]
    );
    checkbox.id   = `${gp}-${p}-${tab_type}-${type}-${splitLabel[0]}-${nestingLevel}`;
    checkboxLabel.htmlFor = checkbox.id;
    checkbox.name = checkbox.id;
  }

  checkbox.value = splitLabel[0];
  checkbox.classList.add('bx--checkbox', `all-${type}-checkboxes`, 'pcar-title');
  checkbox.checked = true;

  if (nestingLevel === 0) label = label.replace('_',' ');
  checkboxLabel.classList.add("bx--checkbox-label");
  checkboxLabel.appendChild(document.createTextNode(label));

  div.appendChild(checkbox);
  div.appendChild(checkboxLabel);
  list.appendChild(div);

  if (nestingLevel>0) list.style.marginLeft = `${nestingLevel*30}px`;
  if (nestingLevel===0) list.style.paddingBottom='0rem';
  parent.appendChild(list);

  checkbox.onclick = function(){ checkboxHandling(this,list); }
}

function createOnlyButton(type,label,nestingLevel) {
  let onlyButton = document.createElement('span');
  let buttonElem = document.createElement('button');
  onlyButton.id = `${type}-${label}-${nestingLevel}-only`;
  buttonElem.classList.add('bx--btn','bx--btn--ghost','bx--btn--sm','onlyBtn','p-2');
  buttonElem.innerHTML = 'Only';
  buttonElem.onclick = function(){ onlyButtonHandleClick(this,type); }
  onlyButton.appendChild(buttonElem);
  return onlyButton;
}

function toggleExpandButton(subList) {
  const toggleButton = document.createElement("img");
  toggleButton.src = "/assets/images/plus_icon.svg";
  toggleButton.classList.add("expand-button");
  toggleButton.addEventListener('click',function(){
    if(!subList) return;
    if(subList.style.display==='none'||subList.style.display==="") {
      subList.style.display="block";
      toggleButton.src="/assets/images/minus_icon.svg";
    } else {
      subList.style.display="none";
      toggleButton.src="/assets/images/plus_icon.svg";
    }
  });
  return toggleButton;
}

function createCountPill(type,label,counter,nestingLevel) {
  let countPill = document.createElement('button');
  countPill.id = `${type}-${label}-${nestingLevel}-count`;
  countPill.classList.add("bx--tag","bx--tag--blue");
  countPill.innerHTML = counter;
  return countPill;
}

function onlyButtonHandleClick(el,type) {
  $('.onlyBtn.onlyBtn--selected').removeClass('onlyBtn--selected');
  let li = $(el).closest('li');
  let input = $(li).find('input[type=checkbox]')[0];
  let parentElement = $(input).closest('.manning-force-box').attr('id');
  $(`#${parentElement} .all-${type}-checkboxes`).prop('checked',false);
  $(input).click();
  $('.filter-selections li').each(function(){
    let children = $(this)
      .find("ul").children()
      .find('input[type=checkbox]:checked');
    $(this).find('button[id*=count]').html(children.length);
  });
  $(el).addClass('onlyBtn--selected');
}

function checkboxHandling(el,li) {
  // clear pill on any manual checkbox click
  $('.onlyBtn.onlyBtn--selected').removeClass('onlyBtn--selected');
  // propagate down and up and refresh counts…
  $(li).children().find('input[type=checkbox]').prop('checked',el.checked);
  $('.filter-selections li').each(function(){
    let children = $(this)
      .find("ul").children()
      .find('input[type=checkbox]:checked');
    $(this).find('button[id*=count]').html(children.length);
  });

  let parentLi=$(li).parent().get(0).closest('li'),
      accordianLi=$(parentLi).hasClass("bx--accordion__item");
  while(parentLi && !accordianLi){
    let parentCheckbox=$(parentLi).find('input[type=checkbox]')[0];
    if(parentCheckbox){
      let siblingChecks=$(parentLi).children().next()
        .find('input[type=checkbox]');
      let checked=Array.from(siblingChecks).filter(c=>c.checked);
      if(checked.length===0){
        $(parentCheckbox).prop('checked',el.checked);
        updateCount(parentLi,0);
      } else {
        $(parentCheckbox).prop('checked',true);
        updateCount(parentLi,checked.length);
      }
    }
    parentLi=$(parentLi).parent().get(0).closest('li');
    accordianLi=$(parentLi).hasClass("bx--accordion__item");
  }
}

function updateCount(parentLi,count){
  let element=$(parentLi).children().find("button[id*=count]")[0];
  $(element).html(count);
}


function getSelectedFilters(page) {
    let pcar_values = get_checked_pcar(page);
    let pcar_mws = {};
    let pcar_mws_mds = {};

    for (let i = 0; i < pcar_values.length; i++) {
        set_checked_mws(pcar_mws, pcar_values, i, page);
    }

    let new_pcar_mws = [];
    for (const key in pcar_mws) {
        for (const el of pcar_mws[key]) {
            new_pcar_mws.push(key + '_' + el);
        }
    }

    for (const element of new_pcar_mws) {
        set_checked_mds(pcar_mws_mds, element, page);
    }

    const pcar_mws_mds_array = [];
    for (const key in pcar_mws_mds) {
        let [pcar, mws] = key.split('_');
        const mapKey = pcar + ':' + mws;
        for (const md of pcar_mws_mds[key]) {
            pcar_mws_mds_array.push(pcar_mws_mds_map[mapKey + ':' + md]);
        }
    }

    let selected_segment = get_selected_segment(page);
    let segment_cmd = {};
    for (const seg of selected_segment) {
        set_selected_cmd(segment_cmd, seg, page);
    }

    const segment_cmd_array = [];
    for (const key in segment_cmd) {
        for (const cmd of segment_cmd[key]) {
            segment_cmd_array.push(segment_cmd_map[segment_title_map[key] + ':' + cmd]);
        }
    }

    return [pcar_mws_mds_array, segment_cmd_array];
}

function selectAllCheckboxes(button){
  $('.onlyBtn.onlyBtn--selected').removeClass('onlyBtn--selected');
  const parent=$(button).closest('.selection-filter-container');
  $('.manning-force-box .bx--checkbox',parent).prop('checked',true);
  $('.manning-force-box li',parent).each(function(){
    let children=$(this).find("ul").children().find('input[type=checkbox]:checked');
    $(this).find('button[id*=count]').html(children.length);
  });
}

function deselectAllCheckboxes(button){
  $('.onlyBtn.onlyBtn--selected').removeClass('onlyBtn--selected');
  const parent=$(button).closest('.selection-filter-container');
  $('.manning-force-box .bx--checkbox',parent).prop('checked',false);
  $('button[id*=count]',parent).each(function(){
    $(this).html(0);
  });
}
