"use strict"

function showScoreModal() {
    $('#score_view_modal > div.bx--modal.bx--modal-tall').addClass('is-visible');
    setTimeout(
        function() {
            $('#score_tab_data').handsontable('getInstance').render();
        }, 
        1
    );
}

function hideScoreModal() {
    $('#score_view_modal > div.bx--modal.bx--modal-tall').removeClass('is-visible')
}

function loadScoreTable(default_criteria, score_data = []) {
    let container = $('#score_tab_data');

    createHot_score(container, default_criteria, ['Score Value'], score_data, false, handson_license)
}

function makeCriteria() {
    let data = [];
    $('#score_tab_data')
        .handsontable('getInstance')
        .getDataAtCol(0)
        .forEach(function(val, i){
            data.push([default_criteria[i], val]);
        });

    return Object.fromEntries(data);
}

function resetForm() {
    $('#score_tab_data')
        .handsontable('getInstance').loadData(getHandsOnTableInitData());
    $('#score-name-i').val('');
    $('#score-description').val('');
    $('#hidden_score_id').val('');

    hideNotification();
}

function setForm(data) {
    let score_data = [];
    
    Object
        .values(data['SESSION'])
        .forEach(
            function(value) {
                score_data.push([value]);
            });
    
    if (score_data.length != default_criteria.length) {
        score_data = getHandsOnTableInitData();
    }

    $('#score_tab_data')
        .handsontable('getInstance')
        .loadData(score_data);
    $('#score-name-i').val(
        decodeHtmlEntities(
            sanitizeHtml(
                data['NAME'], 
                { allowedAttributes:{}, allowedTags:[]}
            )
        )
    );
    $('#score-description').val(
        decodeHtmlEntities(
            sanitizeHtml(
                data['DESCRIPTION'], 
                { allowedAttributes:{}, allowedTags:[]}
            )
        )
    );
}

function decodeHtmlEntities(text){
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
}

function deleteScore() {
    let data, 
        score_id = $(this).attr('score');
    
    hideNotification();

    data = {
        rhombus_token: rhombuscookie(), 
        score_id: score_id,
    };

    return $.post('/socom/program/score/delete', 
        data, 
        function (data) {
            showNotification(data.message, (data.status === true ? 'success' : 'error'), true);
        },
        "json"
    ).fail(ajaxFail);
}

function saveScore() {
    let score_name = $('#score-name-i').val(),
        score_description = $('#score-description').val(),
        score_data = makeCriteria(),
        score_id = $('#hidden_score_id').val(),
        programId = $('#hidden_program_id').val(),
        iss_extract = $('input[name="use_iss_extract"]:checked').val() === 'true' ? 'ISS_EXTRACT' : 'RC_T',
        url, data;
    hideNotification();

    
    // replace the space in the key by underscore
    for(let item of Object.keys(score_data)) {
        if (item.match(/ /) !== null) {
            score_data[item.replace(/\s/g,"-")] = score_data[item];
            delete score_data[item];
        }
    }

    data = {
        rhombus_token: rhombuscookie(), 
        score_name: score_name,
        score_description: score_description,
        program_id: programId,
        score_data: score_data,
        iss_extract: iss_extract
    };
    
    if (score_id.match(/^\d+$/) !== null) {
        data['score_id'] = score_id;
        url = '/socom/resource_constrained_coa/program/score/edit';
    } else {
        url = '/socom/resource_constrained_coa/program/score/create';
    }

    $.post(url, 
        data, 
        function (data) {
            showNotification(data.message, (data.status === true ? 'success' : 'error'));
            
            $('#option-list').DataTable().ajax.reload()
        },
        "json"
    ).fail(function(jqXHR) { ajaxFail(jqXHR, 'Failed to save Program Score'); });
}

function percentageValidator (value, callback) {
    if (
        value > 100 || 
        value <= 0 || 
        (
            typeof value === 'string' && 
            value.match(/^\d+(\.\d+)?$/) === null
        )
    ) {
        showNotification('Score value must be greater than 0 and less than 100', 'error');
        callback(false);
    } else {
        hideNotification();
        callback(true);
    }
}

/**
 * Create a handsontable.
 */
function createHot_score(container, rowHeaders, colHeaders, data, readOnly, licenseKey, options = {}) {
    let handsontable_args = {
        colHeaders: colHeaders,
        rowHeaders: rowHeaders,
        columns: [
            {
                type: 'numeric',
                validator: percentageValidator,
                allowInvalid: false
            }
        ],
        rowHeaderWidth: 250,
        colWidths: 100,
        data: data,
        readOnly: readOnly,
        licenseKey: licenseKey,
        manualColumnResize: false,
        manualRowResize: false
    };

    for (let option in options) {
        handsontable_args[option] = options[option];
    }
    
    container.handsontable(handsontable_args);

    return container.handsontable('getInstance');
}

function getScore(score_id, program_id) {
    let data = {
        rhombus_token: rhombuscookie(),
        score_id: score_id,
        program_id: program_id,
        type_of_coa: $('input[name="use_iss_extract"]:checked').val() !== 'true' ? 'RC_T' : 'ISS_EXTRACT'
    };

    return $.post(
        '/socom/resource_constrained_coa/program/score/get',
        data,
        function (data) {
            showNotification(data.message, (data.status === true ? 'success' : 'error'));
            
            setForm(data.data);
        },
        'json'
    ).fail(function(jqXHR) { ajaxFail(jqXHR, 'Failed to get Program Score'); });
}

function getHandsOnTableInitData() {
    let rows = [];
    for (let i = 0; i < default_criteria.length; i++) {
        rows[i] = ['0.00'];
    }

    return rows;
}

function onReady() {
    let rows = getHandsOnTableInitData();
    
    //loadScoreListing();
    
    loadScoreTable(default_criteria);

    setNotificationName('score');
}

function showScore(){

    let SCORE = $(this).parent().data('SCORE'),
        SCORE_ID = String($(this).parent().data('SCORE_ID')),
        PROGRAM_ID = $(this).parent().data('PROGRAM_ID'),
        PROGRAM_NAME_TXT = $(this).parent().data('PROGRAM_NAME_TXT');
    
    if (SCORE_ID.match(/^\d+$/) != null && PROGRAM_ID.trim() !== '') {
        $('#hidden_score_id').val(SCORE_ID);
        $('#hidden_program_id').val(PROGRAM_ID);
        $('#score-name h5').html(`Score for ${PROGRAM_NAME_TXT} Program`)

        editScore(SCORE_ID, PROGRAM_ID);
    } else if (PROGRAM_ID.trim() !== '') {
        $('#hidden_score_id').val('');
        $('#hidden_program_id').val(PROGRAM_ID);
        $('#score-name h5').html(`Score for ${PROGRAM_NAME_TXT}`)
        resetForm();
        showScoreModal()
    } else {
        console.log(SCORE_ID);
        console.log(PROGRAM_ID);
    }
}

function editScore(score_id, program_id) {
    getScore(score_id, program_id).done(function() {
        showScoreModal();  
    });
}

$(onReady);

if (!window._rb) window._rb = {
    hideScoreModal: hideScoreModal,
    getHandsOnTableInitData: getHandsOnTableInitData,
    loadScoreTable: loadScoreTable,
    showScoreModal: showScoreModal,
    resetForm: resetForm,
    onReady: onReady,
    getScore: getScore,
    saveScore: saveScore,
    deleteScore: deleteScore,
    createHot_score: createHot_score,
    showScore: showScore,
    editScore: editScore,
    percentageValidator: percentageValidator
}