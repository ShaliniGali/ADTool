/**
 * @jest-environment jsdom
 */

const { Callbacks } = require('jquery');
const jQuery = require('jquery');
$ = jQuery;
global.P1_FLAG=true
global.CarbonComponents = {
    NavigationMenu: {
        init: jest.fn(),
    },
    Modal: {
        init: jest.fn(),
    },
    Accordion: {
        init: jest.fn(),
    },
    Tab: {
        init: jest.fn(),
    },
    Checkbox: {
        init: jest.fn(),
    },
    Notification: {
        init: jest.fn(),
    },
    DatePicker: {
        init: jest.fn(),
    },
    Tooltip: {
        init: jest.fn(),
    },
    OverflowMenu: {
        init: jest.fn(),
    },
    ProductSwitcher: {
        init: jest.fn(),
    },
    NumberInput: {
        init: jest.fn(),
    },
    Dropdown: {
        init: jest.fn(),
    },
    ContentSwitcher: {
        init: jest.fn(),
    }
}

global.Highcharts = null
global.heatmap = jest.fn(Highcharts)
global.highchartsMore = jest.fn(Highcharts)
global.highchartsSankey = jest.fn(Highcharts)
global.histogram_bellcurve = jest.fn(Highcharts)
global.indicators = jest.fn(Highcharts)
global.trendline = jest.fn(Highcharts)
global.highchartsExport = jest.fn(Highcharts)




test("filler", ()=>{
    require("../actions/p1_socom.js")
    window._rb.p1_socom();
    expect(true).toBe(true)
})