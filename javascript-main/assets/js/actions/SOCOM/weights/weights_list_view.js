"use strict";
    
function onReady() {
  setNotificationName('weights');
    $('#criteria-list').DataTable({
        info: 'Weights Criteria List',
        autoWidth: true,
        ajax: {
            url: "/socom/resource_constrained_coa/criteria/weights/list/data",
            type: 'GET'
        },
        columns: column_definition,
        start: 0,
        length: 10,
        lengthChange: false,
        "order": [],
        "createdRow": function ( row, data, index ) {
            if (data){
                let elem = $('td', row).eq(column_definition.length - 1).find('div.bx--overflow-menu > button.bx--overflow-menu__trigger');
                // edit is disabled for now
                elem.next('div.bx--overflow-menu-options').find('button[role=edit]').prop('disabled', false).addClass('button-not-allowed').attr('weight', data['WEIGHT_ID']).on('click', show_weight_view_modal );
                elem.next('div.bx--overflow-menu-options').find('button[role=delete]').prop('disabled', false).attr('weight', data['WEIGHT_ID']).on('click', delete_weight );
                elem.removeClass('d-none').on('click', show_menu);
            } else {
                $('td', row).eq(column_definition.length - 1).empty();
            }
        },
    });
};

function show_menu() {
    let elem = $(this).next('div.bx--overflow-menu-options');
    if (elem.hasClass('bx--overflow-menu-options--open')) {
        elem.removeClass('bx--overflow-menu-options--open');
    } else {
        $('#criteria-list div.bx--overflow-menu-options--open').removeClass('bx--overflow-menu-options--open');
        elem.addClass('bx--overflow-menu-options--open');
    }
};

function show_weight_view_modal() {
    let elem = $(this);
    let weight_id = $(this).attr('weight');
    hideNotification();
    if (typeof weight_id !== "undefined" && weight_id.match(/^[0-9a]+$/).length === 1){
        $('#weight_view_modal div.bx--modal-header > h3.bx--modal-header__heading').empty();
        let scoreTableLocal = $('#weight-modal-div').handsontable('getInstance');
        if (typeof scoreTableLocal !== 'undefined') {
          let scoreData = scoreTableLocal.getData();
          let newData = []
          for (let i = 0; i < scoreData.length; i++) {
            newData.push([i, 1, '']);
          }
          scoreTableLocal.loadData(newData)
        }
        loadWeightTable(weight_id)
        $('#hidden_weight_id').val(weight_id);
        elem.closest('div.bx--overflow-menu-options').removeClass('bx--overflow-menu-options--open')
        $('#weight_view_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
    } else { 
        console.log('no weight id found'); 
    }
}

/**
  * Delete weights (calls function in model that marks as deleted).
  * Note: updates every appropriate table related to weight.
*/
function delete_weight() {
  let elem = $(this).next('div.bx--overflow-menu-options');
  elem.closest('div.bx--overflow-menu-options').removeClass('bx--overflow-menu-options--open')

  let weight_id = $(this).attr('weight');

  $.ajax('/socom/resource_constrained_coa/weights/delete/'+weight_id, {
      method: 'POST',
      data: {weight_id: weight_id, rhombus_token: function() { return rhombuscookie(); }},
      success: function () {
        $('#criteria-list').DataTable().ajax.reload();
      }
  });
};

function getHotScoreTable() {

  const hotScoreTable = {
    stretchH: 'all',
    width: '100%',
    columns: [
      {
        data: 'criteria',
        editor: false,
        readOnly: true
      },
      {
        data: 'weight',
        readOnly: false
      }
    ],
    colWidths: [300, 75, 75, 75],
    cells: function (row, col) {
      let cellProperties = {};
      return cellProperties;
    },
    beforeKeyDown: function(e) {
      if ((!/^\d*$/.test(e.key) && e.key != 'Backspace' && e.key != '.') || 
        (e.target.value.length >= 4 && e.key != 'Backspace')) {
        e.preventDefault();
      }
    },
    afterChange: function(changes) {
      updateWeightSums('guidance');
      updateWeightSums('pom');
    }
  }

  return hotScoreTable;
}

/**
 * Load score table view for the corresponding criteria.
*/
function loadWeightTable(weight_id) {
    weight_id = parseInt(weight_id);
    
    return $.ajax({
      url: `/socom/resource_constrained_coa/criteria/weights/get/${weight_id}`,
      method: 'get',
      dataType: 'json',
      success: function(data) {
        $('#weight_view_modal div.bx--modal-header > h3.bx--modal-header__heading').html(sanitizeHtml(data.title));
        createHot(
          data['guidance'].id,
          data['guidance'].rowHeaders,
          data['guidance'].colHeaders,
          data['guidance'].tableData,
          data['guidance'].readOnly,
          data['guidance'].licenseKey,
          getHotScoreTable()
        );
        loadWeightedSums(data['guidance'].tableData, 'guidance');
        createHot(
          data['pom'].id,
          data['pom'].rowHeaders,
          data['pom'].colHeaders,
          data['pom'].tableData,
          data['pom'].readOnly,
          data['pom'].licenseKey,
          getHotScoreTable()
        );
        loadWeightedSums(data['pom'].tableData, 'pom');
      }
    });
  }
  
  function weightedScoreSum(weightedScores) {
    let initialValue = 0;
    let weightedSum = weightedScores.reduce(
      (previousValue, currentValue) => parseFloat(previousValue) + parseFloat(currentValue),
      initialValue
    );
    
    return weightedSum;
  }
  
  function saveWeight(weight_id) {
    hideNotification();
      let weightFD = new FormData();
  
      let weight_choice = $('#hidden_weight_id').val();
      if (weight_choice) {
          weight_id = parseInt(weight_choice);
      } else {
          weight_id = null;
      }
      let weightDataG = $('#weight-guidance-div').handsontable('getInstance').getSourceData(),
        weightDataP = $('#weight-pom-div').handsontable('getInstance').getSourceData();
      
      weightFD.append("weight_id", weight_id);
      weightFD.append("weight_data", JSON.stringify({guidance: weightDataG, pom: weightDataP}));
      weightFD.append('rhombus_token', rhombuscookie());
  
      $.ajax({
          url: '/socom/resource_constrained_coa/weights/list/save',
          type: 'post',
          data: weightFD,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function (data) {
              showNotification('Score saved successfully.', 'success', false);
          },
          error: ajaxFail});
  }
  
  /**
   * Create a handsontable.
   */
  function createHot(container, rowHeaders, colHeaders, data, readOnly, licenseKey, options = {}) {
    let handsontable_args = {
      // rowHeaders: rowHeaders,
      colHeaders: colHeaders,
      data: data,
      readOnly: readOnly,
      licenseKey: licenseKey,
      manualColumnResize: false,
      manualRowResize: false,
      autoRowSize: true,
      renderAllRows: true
    };
  
    for (let option in options) {
      handsontable_args[option] = options[option];
    }

    $('#'+ container).handsontable(handsontable_args);

    return $('#'+ container).handsontable('getInstance');
  }
  
  /**
   * Load the content of both sum divs with weight sum and weighted score sum respectively.
   */
  function loadWeightedSums(scoreData, type) {
    if (['guidance','pom'].indexOf(type) != -1) {
      let weightSum = 0.0;
     
      for (let i = 0; i < scoreData.length; i++) {
        weightSum += parseFloat(scoreData[i]['weight']);
      }
      
      $(`#weight-${type}-sum-text`).text('Sum: ' + parseFloat(weightSum).toFixed(2));
    }
  }
  
  function updateWeightSums(type) {
    if (['guidance','pom'].indexOf(type) != -1) {
      let cells = $(`#weight-${type}-div td:nth-child(2)`);
      let weightSum = 0.0;

      for (let cell of cells) {
        let cellText = cell.innerHTML.trim();
        if (cellText.match(/[A-Za-z]/)) {
          $(`#weight-${type}-sum-text`).text('Sums: N/A');
          return;
        }
        if (cellText == '') {
          weightSum += 0.0;
        } else {
          weightSum += parseFloat(cell.innerHTML.trim());
        }
      }

      $(`#weight-${type}-sum-text`).text('Sum: ' + parseFloat(weightSum).toFixed(2));
    }
  }

$(onReady); 
  
if (!window._rb) window._rb = {}
window._rb.onReady = onReady;
window._rb.show_menu = show_menu;
window._rb.show_weight_view_modal = show_weight_view_modal;
window._rb.weightedScoreSum = weightedScoreSum;
window._rb.saveWeight = saveWeight;
window._rb.loadWeightTable = loadWeightTable;
window._rb.loadWeightedSums = loadWeightedSums;
window._rb.delete_weight = delete_weight;
window._rb.updateWeightSums = updateWeightSums;
window._rb.getHotScoreTable = getHotScoreTable;
