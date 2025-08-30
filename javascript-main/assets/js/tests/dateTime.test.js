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

test('Date.prototype.formatDateTime_ifLess', () => {
    jest.resetModules();

    require('../global/dateTime');

    const date = new Date(4801856705000);
    const result = date.formatDateTime();

    expect(result).toBeDefined();
});

test('Date.prototype.formatDateTime_else', () => {
    jest.resetModules();

    require('../global/dateTime');

    const date = new Date(4801936840000);
    const result = date.formatDateTime();

    expect(result).toBeDefined();
});

test('Date.prototype.timeDifference_noDiff', () => {
    jest.resetModules();

    require('../global/dateTime');

    const date = new Date(4801935905000);
    const result = date.timeDifference(date);

    expect(result).toBeDefined();
});

test('Date.prototype.timeDifference_diff', () => {
    jest.resetModules();

    require('../global/dateTime');

    const date = new Date(Date.now());
    const result = date.timeDifference(4807362630000);

    expect(result).toBeDefined();
});

test('Date.prototype.formatMonthDate', () => {
    jest.resetModules();

    require('../global/dateTime');

    const date = new Date(Date.now());
    const result = date.formatMonthDate();
    
    expect(result).toBeDefined();
});

test('Date.prototype.phpToJsTimestamp', () => {
    jest.resetModules();

    require('../global/dateTime');

    const date = new Date(Date.now());
    const result = date.phpToJsTimestamp();
    
    expect(result).toBeDefined();
});