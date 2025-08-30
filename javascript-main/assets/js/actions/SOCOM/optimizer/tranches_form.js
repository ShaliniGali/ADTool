/**
 * Create a handsontable.
 */
function createHot_tranches(container, rowHeaders, data, readOnly, validator_callback, licenseKey, options = {}) {
    container.addClass('htCenter');
    let handsontable_args = {
        colHeaders: [1,2,3,4],
        rowHeaders: rowHeaders,
        columns: [
            {
                type: 'numeric',
                validator: validator_callback,
                allowInvalid: false
            }
        ],
        rowHeaderWidth: 100,
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

    return container.handsontable('getInstance').render();
}

function tranchesValidator(value, callback) {
    if (
        value > 1.0 || 
        value < 0.0 || 
        (
            typeof value === 'string' && 
            value.match(/^\d+(\.\d+)?$/) === null
        )
    ) {
        console.log('heere');
        showNotification('Score value must be greater than 0 and less than 100', 'error');
        callback(false);
    } else {
        hideNotification();
        callback(true);
    }
}

function  percAllocValidator(value, callback) {
    if (
        value >= 1.0 || 
        value <= 0.0 || 
        (
            typeof value === 'string' && 
            value.match(/^\d+(\.\d+)?$/) === null
        )
    ) {
        console.log('heere');
        showNotification('Score value must be greater than 0 and less than 100', 'error');
        callback(false);
    } else {
        hideNotification();
        callback(true);
    }
}

function onReadyTranches() {
    let num_tranches = $('#tranche-select > option:selected').val(), col_head_tranches = [];

    $('#tranche-select').on('change', function() {
        const numTranches = $('#tranche-select > option:selected').val();
        
        let keptTranches = $('#kept_tranches').handsontable('getInstance'),
            cutTranches = $('#cuts_perc_alloc').handsontable('getInstance'),
            keptTranchesLen = keptTranches.getDataAtRow(0).filter(x => x !== null).length,
            requiredCols = numTranches;

        let newTranchCols = defaultTranches(requiredCols),
            newCutCols = defaultCutsPercAlloc(requiredCols);

        let columnSettingsKept = [], columnSettingsPerc = [];
        for (let i = 0; i < requiredCols; i++) {
            columnSettingsKept.push({
                type: 'numeric',
                validator: tranchesValidator,
                allowInvalid: false
            });
            columnSettingsPerc.push({
                type: 'numeric',
                validator: percAllocValidator,
                allowInvalid: false
            });
        }

        keptTranches.updateSettings({
            columns: columnSettingsKept
        });
        cutTranches.updateSettings({
            columns: columnSettingsPerc
        })

        keptTranches.loadData(newTranchCols);
        cutTranches.loadData(newCutCols);


        keptTranches.render();
        cutTranches.render();
    });

    createHot_tranches($('#kept_tranches'), '% Cuts', defaultTranches(num_tranches), false, tranchesValidator, handson_license);

    createHot_tranches($('#cuts_perc_alloc'), '% Cuts', defaultCutsPercAlloc(num_tranches), false, percAllocValidator, handson_license);
}

/*

DEFAULT_TRANCHES = [(0.25 * (x+1)) - 0.25 for x in range(no_of_tranches, 0, -1)] 

# 1 tranche - [0.25]
# 2 tranches - [0.5, 0.25]
# 3 tranches - [0.75, 0.5, 0.25]
# 4 tranches [1.0, 0.75, 0.5, 0.25]

*/

function defaultTranches(num_tranches) {
    let row = [];

    for (let i = 0; i < num_tranches ; i++) {
        row.push((0.25 * (i+1)).toFixed(2));
    }

    return [row.reverse()];
}

function defaultCutsPercAlloc(num_tranches) {
    let row = [], val = (1/num_tranches).toFixed(2);
    for (i=0; i < num_tranches ; i++) {
        row.push(val)
    }
    
    let total = row.reduce((accumulator, currentValue) => parseFloat(accumulator) + parseFloat(currentValue), 0),
    difference = (1.00 - total).toFixed(2);

    console.log('difference', difference, total);

    if (difference !== 0.00) {
        row[num_tranches-1] = parseFloat(row[num_tranches-1]) + parseFloat(difference);
    }
    return [row];
}



$(onReadyTranches)