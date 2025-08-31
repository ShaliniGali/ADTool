/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

require('bootstrap/dist/js/bootstrap.bundle.min.js');

test('validateIP_errorIdNotNull_regexMatch', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div id="parent-div">
            <input id="main-field" class="ip-field" type="text" value="12.34.123.234"></input>
            <input id="field2" class="ip-field" type="text" value="test"></input>
            <input id="field3" class="ip-field" type="text" value="test"></input>
            <input id="field4" class="ip-field" type="text" value="test"></input>
        </div>
        <p id="error-id"></p>
    `;

    require('../global/ip_address_helper');

    const field = document.getElementById('main-field');
    const className = 'ip-field';
    const errorId = 'error-id';
    window._rb.validateIP(field, className, errorId);

    expect(true).toBe(true);
});

test('validateIP_errorIdNull_regexNoMatch', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div id="parent-div">
            <input id="main-field" class="ip-field" type="text" value="no-match"></input>
            <input id="field2" class="ip-field" type="text" value="test"></input>
            <input id="field3" class="ip-field" type="text" value="test"></input>
            <input id="field4" class="ip-field" type="text" value="test"></input>
        </div>
        <p id="error-id"></p>
    `;

    require('../global/ip_address_helper');

    const field = document.getElementById('main-field');
    const className = 'ip-field';
    const errorId = 'error-id';
    window._rb.validateIP(field, className, errorId);

    expect(true).toBe(true);
});

test('getIpFieldsDOMList', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div id="parent-div" name="parent-div">
            <input id="field1" class="ip-field" type="text" value="test"></input>
            <input id="field2" class="ip-field" type="text" value="test"></input>
            <input id="field3" class="ip-field" type="text" value="test"></input>
            <input id="field4" class="ip-field" type="text" value="test"></input>
        </div>
        <p id="error-id"></p>
    `;

    require('../global/ip_address_helper');

    const name = 'parent-div';
    const result =  window._rb.getIpFieldsDOMList(name);

    expect(result).toBeDefined();
});
