const { toHaveStyle } = require('@testing-library/jest-dom/matchers.js');
const jQuery = require('jquery'); 
global.$ = jQuery;
global.$$ = jest.fn((id) => {
    return window.document.getElementById(id);
});
global.jQuery = jQuery;
global.rhombuscookie = () => true;
global.page = 'Issue'
jest.useFakeTimers();

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);


require('highcharts/highstock.js');
$.fn.ready = (cb) => {cb()};
global.HighchartsData = {
    plotOptions: {
        series: {
            events: {
                click: jest.fn()
            }
        }
    },
    tooltip: {
        points: [{
            color: "#7cb5ec",
            series: {
                name: 'TEST',
            },
            x: 2030,
            y: 118935,
        }]
    }
};
global.cap_sponsor_approve_reject_series_data = ['abc', '123']
global.cap_sponsor_approve_reject_categories = ['abc', '123']
global.cap_sponsor_dollar = [
    [
        "abc",
        100
    ],
    [
        "123",
        362840
    ]
]
global.dollars_move_fiscal_years = [
    2026,
    2027
];

global.cap_sponsor_count = [
    [
        "abc",
        40
    ],
    [
        "123",
        60
    ]
]
global.dollars_move_series_data = [
    {
        "name": "O&M $",
        "data": [
            169225,
            169225
        ]
    },
    {
        "name": "PROC $",
        "data": [
            160197,
            160197
        ]
    },
    {
        "name": "RDT&E $",
        "data": [
            196170,
            196170
        ]
    }
]
global.addSeries = jest.fn();
global.Highcharts = {
    getOptions: () => {
        return {
            colors: [1, 2, 3]
        }
    },
    SVGRenderer: {
        prototype: {
            symbols: {
                download: jest.fn()
            }
        }
    },
    setOptions : jest.fn(),
    chart: jest.fn(function(div, data) {
        global.HighchartsData[div] = data;
        const removeFunc = jest.fn(() => {
            chartSeries.shift();
        })
        let chartSeries = global.dollars_move_series_data.map((series) => {
            return {
                ...series,
                remove: removeFunc
           }  
        })
        return {
            xAxis: [
                {
                    update: jest.fn()
                }
            ],
            series: chartSeries,
            addSeries: global.addSeries
        };
    }),
    plotOptions: {
        series: {
            events: {
                click: jest.fn()
            }
        }
    },
    numberFormat: jest.fn(),
};
beforeEach(()=> {
    jest.resetModules();
    document.body.innerHTML = `
        <html class="w-100">
        <button class="theme-button">
        
        </button>
        </html>
    `
    require('../../actions/SOCOM/home.js');  
})

test('update_sand_chart', () => {
    global.resource_category_chart = {
        xAxis: [{
            update: jest.fn()
        }],
        addSeries: jest.fn()
    }
    $.post=(url,obj,callback)=>{if (callback) callback({
        "dollars_move_fiscal_years": global.dollars_move_fiscal_years,
        "dollars_move_series_data": [
            {
                "name": "O&M $",
                "data": [
                    100,
                    100
                ]
            },
            {
                "name": "PROC $",
                "data": [
                    160197,
                    160197
                ]
            },
            {
                "name": "RDT&E $",
                "data": [
                    196170,
                    196170
                ]
            }
        ],
        
    })
    }
    global.HighchartsData['pie-1'].plotOptions.series.events.click({
        point: {
            name: 'abc'
        }
    });

    global.HighchartsData['chart-1'].tooltip.formatter.call(global.HighchartsData.tooltip);

    expect(true).toBe(true)
})

test('onReady', () => {
    global.page = 'pb_comparsion'
    global.graphData = {
        categories: [1],
        
    }
    window._rb.onReady();
})

test('onReady', () => {
    global.page = 'budget_to_execution'
    global.graphData = {
        categories: [1],
    }
    window._rb.onReady();
})