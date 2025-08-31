const { Callbacks } = require('jquery');
const jQuery = require('jquery');
$ = jQuery;
require('datatables.net');

global.displayToastNotification = jest.fn();
const { storm_weighted_based } = require('../../../actions/SOCOM/weights/weight_view');


test("weight_view", ()=>{
    require("../../../actions/SOCOM/weights/weight_view.js")

    document.body.innerHTML = `
    <div id="show-weighted-score" class="d-flex"></div>
    <div id="show-storm-score" class="d-none"></div>
    <div id="weight_chooser" class="d-flex"></div>
    <div id="weighted_values"></div>
    <select id="fp-weight-sel">
        <option value="1">Option 1</option>
    </select>
    `;

    $.fn.DataTable = jest.fn().mockReturnValue({
    columns: jest.fn().mockReturnThis(),
    visible: jest.fn(),
    ajax: {
        reload: jest.fn()
    }
    });

    // Mock elements
    const datatable = '#datatable';
    const showCols = [0, 1];
    const hideCols = [2, 3];

    // Test case for r-storm
    const elemStorm = { checked: true, id: 'r-storm' };
    window._rb.storm_weighted_based(datatable, showCols, hideCols, elemStorm);

    expect($('#show-weighted-score').hasClass('d-none')).toBe(true);
    expect($('#show-storm-score').hasClass('d-flex')).toBe(true);

    // Test case for r-w
    const elemW = { checked: true, id: 'r-w' };
    window._rb.storm_weighted_based(datatable, showCols, hideCols, elemW);

    expect($('#show-storm-score').hasClass('d-none')).toBe(true);
    expect($('#show-weighted-score').hasClass('d-flex')).toBe(true);
    expect($('#weight_chooser').hasClass('d-flex')).toBe(true);
    expect(displayToastNotification).not.toHaveBeenCalled();

    // Test case for r-w with no options in fp-weight-sel
    $('#fp-weight-sel').empty();
    window._rb.storm_weighted_based(datatable, showCols, hideCols, elemW);
    expect(displayToastNotification).toHaveBeenCalledWith('error', 'Please create weights by clicking the "Weights Builder" button.', 5000);
});