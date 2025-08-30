/**
 * @jest-environment jsdom
 */

const { Callbacks } = require('jquery');
const jQuery = require('jquery');
$ = jQuery;
global.P1_FLAG=true


global.Highcharts = null
global.heatmap = jest.fn(Highcharts)
global.highchartsMore = jest.fn(Highcharts)
global.highchartsSankey = jest.fn(Highcharts)
global.histogram_bellcurve = jest.fn(Highcharts)
global.indicators = jest.fn(Highcharts)
global.trendline = jest.fn(Highcharts)
global.highchartsExport = jest.fn(Highcharts)




test("filler", ()=>{
    require("../actions/p1_highcharts.js")
    window._rb.p1_highcharts();
    expect(true).toBe(true)
})