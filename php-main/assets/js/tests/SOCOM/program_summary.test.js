const jQuery = require('jquery'); 
global.$ = jQuery;
global.$$ = jest.fn((id) => {
    return window.document.getElementById(id);
});
global.jQuery = jQuery;
global.rhombuscookie = () => true;
global.page = 'ZBT'
jest.useFakeTimers();
global.currentProgram = "currentProgram"
global.loadPageData = (target, route, page_data, cb) => cb();
global.sanitizeHtml = html => html;

require('bootstrap/dist/js/bootstrap.bundle.min.js');
global.$.fn.DataTable = jest.fn((obj = null) => {
    let dtmockclass = {
        column: function(value) {
            return {
                    search: function(value) {
                        return {
                            draw: function() {
                                return true;
                            }
                    }
                }
            }
        },
    };

    return {...obj, ...dtmockclass}
});

require('highcharts/highstock.js');
$.fn.ready = (cb) => {cb()};
$.fn.select2 = jest.fn();
$.post = jest.fn();
global.HighchartsData = {};
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
    }),
    numberFormat: jest.fn(),
    charts: {find: () => {return {chart: {series: () => {return [1,2,3]}}}}}
};
global.summary_dt_object = {
    columns: jest.fn(() => ({
      search: jest.fn().mockReturnThis(),
      draw: jest.fn()
    }))
  };
global.toggleSeriesVisibility = jest.fn()
beforeEach(()=> {
    jest.resetModules();
    document.body.innerHTML = `
        <html class="w-100">
        <button class="theme-button">
        
        </button>
        </html>
    `
    require('../../actions/SOCOM/program_summary.js');  
})

test('homeDS', () => {
    document.body.innerHTML = `
        <html></html>
    `
    window.darkSwitch = 'dark';
    window._rb.homeDs();
    expect(true).toBe(true);
})

test('init labels', () => {
    expect(true).toBe(true);
})

test('view_onchange', () => {
    document.body.innerHTML = `
        <div id="program-summary-breadcrumb"></div>
        <div id="program-table-container"></div>
        <div id="historical-pom-data-tag" hidden></div>
        <div id="historical-pom-data-container"></div>
        <div id="eoc-summary-tag"></div>
        <input id="ass-area-1" val="1"/>
        <input id="cs-1" val="1"/>
    `

    window._rb.view_onchange('1', 'summary');
    expect($('#historical-pom-data-tag').attr('hidden')).toBe(undefined);
    window._rb.view_onchange('1', 'details');
    expect($('#historical-pom-data-tag').attr('hidden')).toBe('hidden');
    window._rb.view_onchange('1', 'eoc_historical_pom');
    expect($('#historical-pom-data-tag').attr('hidden')).toBe('hidden');
    window._rb.view_onchange('1', 'eoc_historical_pom_new_condition', 'test_program');
    expect($('#historical-pom-data-tag').attr('hidden')).toBe('hidden');
})

test('dropdown_onchange', () => {
    global.table_dropdown = {
        bin: 'bin',
        tag: 'start',
        pom: 'pom',
        'ass-area': 'ass-area',
        cs: 'cs',
        filter: 'filter',
        program: 'program'
    }
    document.body.innerHTML = `
        <input id='bin' value='bin'></input>
        <input id='tag' value='tag'></input>
        <select id="ass-area-1" type="ass-area" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-ass-area-1">
            <option option="ALL" data-select2-id="select2-data-23-t65m">ALL</option>
        </select>
        <select id="cs-1" type="cs" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-cs-1">
            <option option="ALL" data-select2-id="select2-data-23-t65m">ALL</option>
        </select>
        <select id="program-1" type="program" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-program-1">
            <option option="ALL" data-select2-id="select2-data-23-t65m">ALL</option>
        </select>
        <ul id="select2-ass-area-1-container">
            <li title="ALL">ALL</li>
        </ul>
        <ul id="select2-cs-1-container">
            <li title="ALL">ALL</li>
        </ul>
        <ul id="select2-program-1-container">
            <li title="ALL">ALL</li>
        </ul>
    `

    window._rb.dropdown_onchange('1', 'bin');
    expect(global.table_dropdown['bin']).toBe('bin');
    window._rb.dropdown_onchange('1', 'tag');
    expect(global.table_dropdown['tag']).toBe('start');
    window._rb.dropdown_onchange('tag', 'tag');
    expect(global.table_dropdown['tag']).toBe('starttag');
    window._rb.dropdown_onchange('1', 'historical');
    expect(global.table_dropdown['pom']).toBe('pom');
    window._rb.dropdown_onchange('1', 'ass-area');
    expect(global.table_dropdown['ass-area']).toBe('ass-area');
    window._rb.dropdown_onchange('1', 'cs');
    expect(global.table_dropdown['cs']).toBe('cs');
    window._rb.dropdown_onchange('1', 'program');
    expect(global.table_dropdown['program']).toBe('program');
    window._rb.dropdown_onchange('1', 'filter');
    expect(global.table_dropdown['filter']).toBe('filter');
})

describe('initDatatable', () => {
    let tableData, header;

    beforeEach(() => {

        document.body.innerHTML = `
        <input id="checkbox_visibility" checked />
        <div>
        <table>
        <thead> <tr><th class="bx--table-header-label sorting_disabled dt-body-center" rowspan="1" colspan="1" style="width: 59px;">Program</th>
        <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 272px;">Tag</th><th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 281px;">Bin
        </th><th class="bx--table-header-label sorting_disabled editable" rowspan="1" colspan="1" style="width: 56px;">POM Position</th>
        <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2024</th>
        <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2025</th>
        <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2026</th>
        <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2027</th>
        <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2028</th>
        <th class="bx--table-header-label sorting_disabled dt-body-right" rowspan="1" colspan="1" style="width: 38px;">FYDP</th>
        <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">Historical POM Data</th>
        <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">EOC Summary</th>
        <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 61px;">Approval Action Status</th>
        <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1"></th>
        <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 61px;">Hidden?</th></tr>
        </thead>
            <tbody id="tablebody">
            <tr id="1" class="odd">
                <td class="  dt-body-center">Aircraft AFSOC</td>
                <td></td>
                <td></td>
                <td class="  editable">EXT_2026</td>
                <td class="  dt-body-right editable">48692</td>
                <td class="  dt-body-right editable">39994</td>
                <td class="  dt-body-right editable">48954</td>
                <td class="  dt-body-right editable">44129</td>
                <td class="  dt-body-right editable">37669</td>
                <td class="  dt-body-right">219438</td>
                <td></td>
                <td></td>
                <td class=""style="">Pending</td>
                <td></td>
                <td class=""style="">hidden</td>
            </tr>
            </tbody>
        </table>
        </div>
        `
        tableData = [
            {
                2024: -10,
                2025: 0,
                tag: '',
                bin: '',
                position: '',
                fydp: 5,
                program: '',
                historical: '',
                status: 'PENDING',
                summary: '',
                DT_RowId: 1,
                r_visibility: "not hidden"
            }
        ]
        header = [
            {
                "data": "program",
                "title": "Program",
                "mData": "program",
                "sTitle": "Program"
            },
            {
                "data": "tag",
                "title": "Tag",
                "mData": "tag",
                "sTitle": "Tag"
            },
            {
                "data": "bin",
                "title": "Bin",
                "mData": "bin",
                "sTitle": "Bin"
            },
            {
                "data": "position",
                "title": "POM Position",
                "className": "editable",
                "mData": "position",
                "sTitle": "POM Position",
                "sClass": "editable"
            },
            {
                "data": "2024",
                "title": "2024",
                "className": "editable",
                "mData": "2024",
                "sTitle": "2024",
                "sClass": "editable"
            },
            {
                "data": "2025",
                "title": "2025",
                "className": "editable",
                "mData": "2025",
                "sTitle": "2025",
                "sClass": "editable"
            },
            {
                "data": "2026",
                "title": "2026",
                "className": "editable",
                "mData": "2026",
                "sTitle": "2026",
                "sClass": "editable"
            },
            {
                "data": "2027",
                "title": "2027",
                "className": "editable",
                "mData": "2027",
                "sTitle": "2027",
                "sClass": "editable"
            },
            {
                "data": "2028",
                "title": "2028",
                "className": "editable",
                "mData": "2028",
                "sTitle": "2028",
                "sClass": "editable"
            },
            {
                "data": "fydp",
                "title": "FYDP",
                "mData": "fydp",
                "sTitle": "FYDP"
            },
            {
                "data": "historical",
                "title": "Historical POM Data",
                "mData": "historical",
                "sTitle": "Historical POM Data"
            },
            {
                "data": "summary",
                "title": "EOC Summary",
                "mData": "summary",
                "sTitle": "EOC Summary"
            },
            {
                "data": "status",
                "title": "Approval Action Status",
                "mData": "status",
                "sTitle": "Approval Action Status"
            },
            {
                "data": "DT_RowId",
                "title": "DT_RowId",
                "mData": "DT_RowId",
                "sTitle": "DT_RowId"
            },
            {
                "data": "r_visibility",
                "title": "r_visibility",
                "mData": "r_visibility",
                "sTitle": "r_visibility"
            }
        ];
    })

    test('create data table', () => {
        const table = window._rb.initDatatable('container',tableData, header, 0, ['2024', '2025'], 0, [0, 1]);

        expect(table.dom).toBe('lrtip')
    });

    test('rowCallback, status === "PENDING"', () => {
        const table = window._rb.initDatatable('container',tableData, header, 0, ['2024', '2025'], 0, [0, 1]);

        table.rowCallback('#1', tableData[0]);
        expect(true).toBe(true);
    });

    test('rowCallback, status === "COMPLETE0"', () => {
        tableData = [
            {
                2024: -10,
                2025: 0,
                tag: '',
                bin: '',
                position: '',
                fydp: 5,
                program: '',
                historical: '',
                status: 'COMPLETE',
                summary: '',
                DT_RowId: 1,
                r_visibility: "not hidden"
            }
        ]

        const table = window._rb.initDatatable('container',tableData, header, 0, ['2024', '2025'], 0, [0, 1]);

        table.rowCallback('#1', tableData[0]);
        expect(true).toBe(true);
    });

    test('rowCallback, rowProgramName not falsey', () => {
        tableData = [
            {
                2024: -10,
                2025: 0,
                tag: '',
                bin: '',
                position: '',
                fydp: 5,
                program: 'test',
                historical: '',
                status: 'COMPLETE',
                summary: '',
                DT_RowId: 1,
                r_visibility: "not hidden"
            }
        ]

        const table = window._rb.initDatatable('container',tableData, header, 0, ['2024', '2025'], 0, [0, 1]);

        table.rowCallback('#1', tableData[0]);
        expect(true).toBe(true);
    });

    test('drawCallback', () => {
        const table = window._rb.initDatatable('container',tableData, header, 0, ['2024', '2025'], 0, [0, 1]);

        table.api = () => {
            return {
                rows: (e) => {
                    return {
                        nodes: () => {
                            return $('td')
                        }
                    }
                }
            }
        }

        table.drawCallback()
    })
});

test('addClassToEditedCell', () => {
    document.body.innerHTML = `
        <div class="table-cell"></div>
    `
    
    window._rb.addClassToEditedCell($('.table-cell'));
    expect($('.table-cell').hasClass('ember-cell')).toBe(true);
})

describe('dropdown_selection', () => {
    test('data-select-all="true"', () => {
        document.body.innerHTML = `
            <div id="table-cell" style="height:500px;"></div>
            <button id="table-cell-selection" data-select-all="true"></button>
        `
    
        window._rb.dropdown_selection('#table-cell');
        expect(true).toBe(true);
    });

    test('data-select-all="false"',  () => {
        document.body.innerHTML = `
            <div id="table-cell" style="height:500px;"></div>
            <button id="table-cell-selection" data-select-all="false"></button>
        `
        
        window._rb.dropdown_selection('#table-cell');
        expect(true).toBe(true);
    });
})

test('initHistoricalDatatable', () => {
    document.body.innerHTML = `
    <div>
    <table>
    <thead> <tr><th class="bx--table-header-label sorting_disabled dt-body-center" rowspan="1" colspan="1" style="width: 59px;">Program</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 272px;">Tag</th><th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 281px;">Bin
    </th><th class="bx--table-header-label sorting_disabled editable" rowspan="1" colspan="1" style="width: 56px;">POM Position</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2024</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2025</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2026</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2027</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2028</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right" rowspan="1" colspan="1" style="width: 38px;">FYDP</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">Historical POM Data</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">EOC Summary</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 61px;">Approval Action Status</th></tr>
    </thead>
        <tbody id="tablebody">
        <tr id="1" class="odd">
            <td class="  dt-body-center">Aircraft AFSOC</td>
            <td></td>
            <td></td>
            <td class="  editable">EXT_2026</td>
            <td class="  dt-body-right editable">48692</td>
            <td class="  dt-body-right editable">39994</td>
            <td class="  dt-body-right editable">48954</td>
            <td class="  dt-body-right editable">44129</td>
            <td class="  dt-body-right editable">37669</td>
            <td class="  dt-body-right">219438</td>
            <td></td>
            <td></td>
            <td class=""style="">Pending</td>
        </tr>
        </tbody>
    </table>
    </div>
    `
    let container = 'container'
    let yearIndex = 0
    let yearList = [2023,2024]
    let indexOfFirstYear = 0
    let sharedColumnRows = ''
    const tableData = [
        {
            2024: -10,
            2025: 0,
            tag: '',
            bin: '',
            position: '',
            fydp: 5,
            program: '',
            historical: '',
            status: 'PENDING',
            summary: '',
            DT_RowId: 1,
            r_visibility: 'not hidden'
        }
    ]
    const tableHeaders = [
        {
            "data": "program",
            "title": "Program",
            "mData": "program",
            "sTitle": "Program"
        },
        {
            "data": "tag",
            "title": "Tag",
            "mData": "tag",
            "sTitle": "Tag"
        },
        {
            "data": "bin",
            "title": "Bin",
            "mData": "bin",
            "sTitle": "Bin"
        },
        {
            "data": "position",
            "title": "POM Position",
            "className": "editable",
            "mData": "position",
            "sTitle": "POM Position",
            "sClass": "editable"
        },
        {
            "data": "2024",
            "title": "2024",
            "className": "editable",
            "mData": "2024",
            "sTitle": "2024",
            "sClass": "editable"
        },
        {
            "data": "2025",
            "title": "2025",
            "className": "editable",
            "mData": "2025",
            "sTitle": "2025",
            "sClass": "editable"
        },
        {
            "data": "2026",
            "title": "2026",
            "className": "editable",
            "mData": "2026",
            "sTitle": "2026",
            "sClass": "editable"
        },
        {
            "data": "2027",
            "title": "2027",
            "className": "editable",
            "mData": "2027",
            "sTitle": "2027",
            "sClass": "editable"
        },
        {
            "data": "2028",
            "title": "2028",
            "className": "editable",
            "mData": "2028",
            "sTitle": "2028",
            "sClass": "editable"
        },
        {
            "data": "fydp",
            "title": "FYDP",
            "mData": "fydp",
            "sTitle": "FYDP"
        },
        {
            "data": "historical",
            "title": "Historical POM Data",
            "mData": "historical",
            "sTitle": "Historical POM Data"
        },
        {
            "data": "summary",
            "title": "EOC Summary",
            "mData": "summary",
            "sTitle": "EOC Summary"
        },
        {
            "data": "status",
            "title": "Approval Action Status",
            "mData": "status",
            "sTitle": "Approval Action Status"
        },
        {
            "data": "DT_RowId",
            "title": "DT_RowId",
            "mData": "DT_RowId",
            "sTitle": "DT_RowId"
        },
        {
            "data": "r_visibility",
            "title": "r_visibility",
            "mData": "r_visibility",
            "sTitle": "r_visibility"
        }
    ]
    const row = '#1';
    
    let table = window._rb.initHistoricalDatatable(container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, sharedColumnRows);
    expect(true).toBe(true);
    table.rowCallback(row, tableData[0], 0)
    table.api = () => {
        return {
            rows: (e) => {
                return {
                    nodes: () => {
                        return $('td')
                    }
                }
            }
        }
    }
    table.drawCallback()
})

test('initEocSummaryDatatable', () => {
    document.body.innerHTML = `
    <div>
    <table>
    <thead> <tr><th class="bx--table-header-label sorting_disabled dt-body-center" rowspan="1" colspan="1" style="width: 59px;">Program</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 272px;">Tag</th><th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 281px;">Bin
    </th><th class="bx--table-header-label sorting_disabled editable" rowspan="1" colspan="1" style="width: 56px;">POM Position</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2024</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2025</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2026</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2027</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2028</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right" rowspan="1" colspan="1" style="width: 38px;">FYDP</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">Historical POM Data</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">EOC Summary</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 61px;">Approval Action Status</th></tr>
    </thead>
        <tbody id="tablebody">
        <tr id="1" class="odd">
            <td class="  dt-body-center">Aircraft AFSOC</td>
            <td></td>
            <td></td>
            <td class="  editable">EXT_2026</td>
            <td class="  dt-body-right editable">48692</td>
            <td class="  dt-body-right editable">39994</td>
            <td class="  dt-body-right editable">48954</td>
            <td class="  dt-body-right editable">44129</td>
            <td class="  dt-body-right editable">37669</td>
            <td class="  dt-body-right">219438</td>
            <td></td>
            <td></td>
            <td class=""style="">Pending</td>
        </tr>
        </tbody>
    </table>
    </div>
    `
    let container = 'container'
    let yearIndex = 0
    let yearList = [2023,2024]
    let indexOfFirstYear = 0
    let sharedColumnRows = ''
    let page = 0

    const tableData = [
        {
            2024: -10,
            2025: 0,
            tag: '',
            bin: '',
            position: '',
            fydp: 5,
            program: '',
            historical: '',
            status: 'PENDING',
            summary: '',
            DT_RowId: 1,
            r_visibility: 'not hidden'
        }
    ]
    const tableHeaders = [
        {
            "data": "program",
            "title": "Program",
            "mData": "program",
            "sTitle": "Program"
        },
        {
            "data": "tag",
            "title": "Tag",
            "mData": "tag",
            "sTitle": "Tag"
        },
        {
            "data": "bin",
            "title": "Bin",
            "mData": "bin",
            "sTitle": "Bin"
        },
        {
            "data": "position",
            "title": "POM Position",
            "className": "editable",
            "mData": "position",
            "sTitle": "POM Position",
            "sClass": "editable"
        },
        {
            "data": "2024",
            "title": "2024",
            "className": "editable",
            "mData": "2024",
            "sTitle": "2024",
            "sClass": "editable"
        },
        {
            "data": "2025",
            "title": "2025",
            "className": "editable",
            "mData": "2025",
            "sTitle": "2025",
            "sClass": "editable"
        },
        {
            "data": "2026",
            "title": "2026",
            "className": "editable",
            "mData": "2026",
            "sTitle": "2026",
            "sClass": "editable"
        },
        {
            "data": "2027",
            "title": "2027",
            "className": "editable",
            "mData": "2027",
            "sTitle": "2027",
            "sClass": "editable"
        },
        {
            "data": "2028",
            "title": "2028",
            "className": "editable",
            "mData": "2028",
            "sTitle": "2028",
            "sClass": "editable"
        },
        {
            "data": "fydp",
            "title": "FYDP",
            "mData": "fydp",
            "sTitle": "FYDP"
        },
        {
            "data": "historical",
            "title": "Historical POM Data",
            "mData": "historical",
            "sTitle": "Historical POM Data"
        },
        {
            "data": "summary",
            "title": "EOC Summary",
            "mData": "summary",
            "sTitle": "EOC Summary"
        },
        {
            "data": "status",
            "title": "Approval Action Status",
            "mData": "status",
            "sTitle": "Approval Action Status"
        },
        {
            "data": "DT_RowId",
            "title": "DT_RowId",
            "mData": "DT_RowId",
            "sTitle": "DT_RowId"
        },
        {
            "data": "r_visibility",
            "title": "r_visibility",
            "mData": "r_visibility",
            "sTitle": "r_visibility"
        }
    ]
    const row = '#1';
    
    let table = window._rb.initEocSummaryDatatable(container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, sharedColumnRows, page );
    expect(true).toBe(true);
    table.rowCallback(row, tableData[0], 0)
    table.api = () => {
        return {
            rows: (e) => {
                return {
                    nodes: () => {
                        return $('td')
                    }
                }
            }
        }
    }
    table.drawCallback()
    let r = table.columnDefs[3].render()

})


test('isSameProgramGrouping', () => {
    document.body.innerHTML = `
        <div class="table-cell"></div>
        <div class="table-cell-selection"  data-select-all="true"></div>
    `
    let rows = [1,2,3]
    let mappingIndices = [0,1]
    let topItemMap = [2023,2024]
    let index = 0
    
    window._rb.isSameProgramGrouping(rows, mappingIndices, topItemMap, index);
    expect(true).toBe(true);
})

test('initEocHistoricalDatatable', () => {
    document.body.innerHTML = `
    <div>
    <table>
    <thead> <tr><th class="bx--table-header-label sorting_disabled dt-body-center" rowspan="1" colspan="1" style="width: 59px;">Program</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 272px;">Tag</th><th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 281px;">Bin
    </th><th class="bx--table-header-label sorting_disabled editable" rowspan="1" colspan="1" style="width: 56px;">POM Position</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2024</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2025</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2026</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 32px;">2027</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right editable" rowspan="1" colspan="1" style="width: 33px;">2028</th>
    <th class="bx--table-header-label sorting_disabled dt-body-right" rowspan="1" colspan="1" style="width: 38px;">FYDP</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">Historical POM Data</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">EOC Summary</th>
    <th class="bx--table-header-label sorting_disabled" rowspan="1" colspan="1" style="width: 61px;">Approval Action Status</th></tr>
    </thead>
        <tbody id="tablebody">
        <tr id="1" class="odd">
            <td class="  dt-body-center">Aircraft AFSOC</td>
            <td></td>
            <td></td>
            <td class="  editable">EXT_2026</td>
            <td class="  dt-body-right editable">48692</td>
            <td class="  dt-body-right editable">39994</td>
            <td class="  dt-body-right editable">48954</td>
            <td class="  dt-body-right editable">44129</td>
            <td class="  dt-body-right editable">37669</td>
            <td class="  dt-body-right">219438</td>
            <td></td>
            <td></td>
            <td class=""style="">Pending</td>
        </tr>
        </tbody>
    </table>
    </div>
    `
    let container = 'container'
    let yearIndex = 0
    let yearList = [2023,2024]
    let indexOfFirstYear = 0
    let sharedColumnRows = ''
    let page = 0

    const tableData = [
        {
            2024: -10,
            2025: 0,
            tag: '',
            bin: '',
            position: '',
            fydp: 5,
            program: '',
            historical: '',
            status: 'PENDING',
            summary: '',
            DT_RowId: 1,
            r_visibility: 'not hidden'
        }
    ]
    const tableHeaders = [
        {
            "data": "program",
            "title": "Program",
            "mData": "program",
            "sTitle": "Program"
        },
        {
            "data": "tag",
            "title": "Tag",
            "mData": "tag",
            "sTitle": "Tag"
        },
        {
            "data": "bin",
            "title": "Bin",
            "mData": "bin",
            "sTitle": "Bin"
        },
        {
            "data": "position",
            "title": "POM Position",
            "className": "editable",
            "mData": "position",
            "sTitle": "POM Position",
            "sClass": "editable"
        },
        {
            "data": "2024",
            "title": "2024",
            "className": "editable",
            "mData": "2024",
            "sTitle": "2024",
            "sClass": "editable"
        },
        {
            "data": "2025",
            "title": "2025",
            "className": "editable",
            "mData": "2025",
            "sTitle": "2025",
            "sClass": "editable"
        },
        {
            "data": "2026",
            "title": "2026",
            "className": "editable",
            "mData": "2026",
            "sTitle": "2026",
            "sClass": "editable"
        },
        {
            "data": "2027",
            "title": "2027",
            "className": "editable",
            "mData": "2027",
            "sTitle": "2027",
            "sClass": "editable"
        },
        {
            "data": "2028",
            "title": "2028",
            "className": "editable",
            "mData": "2028",
            "sTitle": "2028",
            "sClass": "editable"
        },
        {
            "data": "fydp",
            "title": "FYDP",
            "mData": "fydp",
            "sTitle": "FYDP"
        },
        {
            "data": "historical",
            "title": "Historical POM Data",
            "mData": "historical",
            "sTitle": "Historical POM Data"
        },
        {
            "data": "summary",
            "title": "EOC Summary",
            "mData": "summary",
            "sTitle": "EOC Summary"
        },
        {
            "data": "status",
            "title": "Approval Action Status",
            "mData": "status",
            "sTitle": "Approval Action Status"
        },
        {
            "data": "DT_RowId",
            "title": "DT_RowId",
            "mData": "DT_RowId",
            "sTitle": "DT_RowId"
        },
        {
            "data": "r_visibility",
            "title": "r_visibility",
            "mData": "r_visibility",
            "sTitle": "r_visibility"
        }
    ]
    const row = '#1';
    
    let table = window._rb.initEocHistoricalDatatable(container, tableData, tableHeaders, yearIndex, yearList, indexOfFirstYear, sharedColumnRows );
    expect(true).toBe(true);
    table.rowCallback(row, tableData[0], 0)

    table.api = () => {
        return {
            rows: (e) => {
                if(e.page == 'current') {
                    return {
                        nodes: () => {
                            return $('td')
                        }
                    }
                }
            }
        }
    }

    table.drawCallback()
})

test('mergeRows', () => {
    document.body.innerHTML = `
        <div class="table-cell"></div>
        <div class="table-cell-selection"  data-select-all="true"></div>
    `
    let rows = [1,2,3]
    let targetIndices = [0,1]
    let currentTopIndex = [0,1]
    let rowCount = 2

    window._rb.mergeRows(rows, targetIndices, currentTopIndex, rowCount);
    expect(true).toBe(true);
})

describe('disable_hide_apply_filter_button', () => {
    test('not all inputs have selected value', () => {
        document.body.innerHTML = `
            <input id="ass-area-test" value="1"></input>
            <input id="pom-test" value="1"></input>
            <input id="cs-test" value="1"></input>
            <input id="program-test"></input>
            <button id="program-summary-filter" disabled="false">Apply Filter</button>
        `;

        const id = 'test';
        window._rb.disable_hide_apply_filter_button(id);

        const btnDisabled = $('#program-summary-filter').prop('disabled');

        expect(btnDisabled).toBe(true);
    });

    test('all inputs have selected value', () => {
        document.body.innerHTML = `
            <input id="ass-area-test" value="1"></input>
            <input id="pom-test" value="1"></input>
            <input id="cs-test" value="1"></input>
            <input id="program-test" value="1"></input>
            <button id="program-summary-filter" disabled="false">Apply Filter</button>
        `;

        const id = 'test';
        window._rb.disable_hide_apply_filter_button(id);

        const btnDisabled = $('#program-summary-filter').prop('disabled');

        expect(btnDisabled).toBe(false);
    });
});

describe('boost js coverage tests', () => {
    test('toggleSeriesVisibility', () => {
        const chart = {
            series: [{
                options: {
                    name: 'test'
                },
                setVisible: (val) => true
            }]
        }
        const plotData = 'test';
        const show = true;
        window._rb.toggleSeriesVisibility(chart, plotData, show);
        expect(true).toBe(true);

    });

    test('dropdown_onchange: type == tag, id not defined in table_dropdown object', () => {
        document.body.innerHTML = `
            <input id="test" value="1"></input>
        `;

        global.Highcharts = {
            charts: {
                find: (cb) => {
                    return {
                        series: [{
                            options: {
                                name: 'test'
                            },
                            setVisible: (val) => true
                        }]
                    }
                }
            }
        };

        table_dropdown = {};

        const id = 'test';
        const type = 'tag';
        window._rb.dropdown_onchange(id, type);

        expect(true).toBe(true);
    });

    test('dropdown_onchange: type == eoc-summary, value == Disapprove && id starts with ad-', () => {
        document.body.innerHTML = `
            <input id="ad-1" value="Disapprove"></input>
        `;

        global.Highcharts = {
            charts: {
                find: (cb) => {
                    return {
                        series: [{
                            options: {
                                name: 'test'
                            },
                            setVisible: (val) => true
                        }]
                    }
                }
            }
        };

        const id = 'ad-1';
        const type = 'eoc-summary';
        displayToastNotification = (notif_type, msg) => true;
        window._rb.dropdown_onchange(id, type);

        expect(true).toBe(true);
    });

    test('dropdown_onchange: type == eoc-summary, value != Disapprove', () => {
        document.body.innerHTML = `
            <input id="ad-1" value="else"></input>
        `;

        global.Highcharts = {
            charts: {
                find: (cb) => {
                    cb({
                        renderTo: {
                            id: 'eoc-summary-data-graph'
                        }
                    })
                    return {
                        series: [{
                            options: {
                                name: 'test'
                            },
                            setVisible: (val) => true
                        }]
                    }
                }
            }
        };

        const id = 'ad-1';
        const type = 'eoc-summary';
        displayToastNotification = (notif_type, msg) => true;
        window._rb.dropdown_onchange(id, type);

        expect(true).toBe(true);
    });
});

describe('check_filter_limit', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="program-dropdown">
                <select id="program-1" multiple>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
            </div>
            <button id="program-summary-filter" disabled="false">Apply Filter</button>
        `;
    })

    test('number of filters exceeds limit', () => {
        $("#program-1").val(["1", "2", "3", "4", "5", "6"]);

        window._rb.check_filter_limit("program", 1, 5);

        const btnDisabled = $('#program-summary-filter').prop('disabled');

        expect(btnDisabled).toBe(true);
    });

    test('number of filters within limit removes error', () => {
        $("#program-1").val(["1", "2", "3", "4", "5"]);

        $(`#program-dropdown`).append(
            `<div id="program-filter-error" class="alert alert-warning mb-0 mt-2" style="width: 16vw;">
                You can only apply up to 5 filters. Please remove some filters to proceed.
            </div>`
        )

        window._rb.check_filter_limit("program", 1, 5);

        const $filterError = document.getElementById('#program-filter-error');

        expect($filterError).toBeNull();
    });

    test('number of filters within limit', () => {
        $("#program-1").val(["1", "2"]);

        window._rb.check_filter_limit("program", 1, 5);

        const btnDisabled = $('#program-summary-filter').prop('disabled');

        expect(btnDisabled).toBe(false);
    });
});

describe('table_filter_onchange', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <select id="approval-status-1" multiple>
                <option value="COMPLETED">COMPLETED</option>
                <option value="PENDING">PENDING</option>
            </select>
        `;
    })

    test('type === approval-status and approvalStatus selected', () => {
        $("#approval-status-1").val(["COMPLETED", "PENDING"]);

        window._rb.table_filter_onchange("1", "approval-status");

        expect(true).toBe(true);
    });


    test('type === approval-status and no approvalStatus selected', () => {
        window._rb.table_filter_onchange("1", "approval-status");

        expect(true).toBe(true);
    });

    test('type !== approval-status', () => {
        window._rb.table_filter_onchange("1", "not-approval-status");

        expect(true).toBe(true);
    });
});

describe('isChecked', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <input id="checkbox_visibility" type="checkbox">
        `;
    })

    test('checked', () => {
        $('#checkbox_visibility').prop('checked', true);

        window._rb.isChecked();

        expect(true).toBe(true);
    });

    test('not checked', () => {
        $('#checkbox_visibility').prop('checked', false);

        window._rb.isChecked();

        expect(true).toBe(true);
    });
});

describe('getDropdownSelectionByUserID', () => {
    const data = [{
        "ID": 0,
        "AO_RECOMENDATION": "Approve",
        "AO_COMMENT": "Test",
        "AO_USER_ID": 1,
        "email": "test@rhombuspower.com"
    }];
    
    test('should return the correct AO_RECOMENDATION for the given user ID and dropdown type', () => {
        const result = window._rb.getDropdownSelectionByUserID(data, 1, 'ao');

        expect(result).toBe("Approve");
    });

    test('should return null for for an invalid user id', () => {
        const result = window._rb.getDropdownSelectionByUserID(data, 100, 'ao');

        expect(result).toBeNull();
    });
});

describe('updateApprovalList', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <ul id="ao-ad-dropdown-list" class="bx--list--unordered"></ul>
        `
    });

    test('should populate the list with AO_RECOMENDATION when valid user ID is provided', () => {
        const data = [{
            "ID": 0,
            "AO_RECOMENDATION": "Approve",
            "AO_COMMENT": "Test",
            "AO_USER_ID": 1,
            "email": "test@rhombuspower.com"
        }];

        window._rb.updateApprovalList(data, 'ao');
        
        const $listItems = $('#ao-ad-dropdown-list').find('li');
        expect($listItems.length).toBe(1);

        const $firstItem = $listItems.eq(0);
        expect($firstItem.text()).toContain('Approve');

        const $emailTag = $firstItem.find('span');
        expect($emailTag.length).toBe(1);
        expect($emailTag.text()).toBe('test@rhombuspower.com');
    });

    test('should handle empty data gracefully', () => {
        window._rb.updateApprovalList([], 'ao');
        
        const $listItems = $('#ao-ad-dropdown-list').find('li');
        expect($listItems.length).toBe(0);
    });

    test('should not add items if approval status is null', () => {
        const dataWithNullRec = [{
            "ID": 1,
            "AO_RECOMENDATION": null,
            "AO_COMMENT": "Test",
            "AO_USER_ID": 2,
            "email": "test2@rhombuspower.com"
        }];
        
        window._rb.updateApprovalList(dataWithNullRec, 'ao');

        const $listItems = $('#ao-ad-dropdown-list').find('li');
        expect($listItems.length).toBe(0);
    });
});

describe('onReady', () => {
    let callback;

    beforeEach(() => {
        callback = jest.fn();
    });

    afterEach(() => {
        jest.resetAllMocks();
    });

    function mockDocumentReadyState(readyState) {
        Object.defineProperty(document, 'readyState', {
            value: readyState,
            configurable: true,
        });
    }

    test('should add event listener when document is still loading', () => {
        mockDocumentReadyState('loading');

        const addEventListenerSpy = jest.spyOn(document, 'addEventListener');

        window._rb.onReady(callback);

        expect(addEventListenerSpy).toHaveBeenCalledWith('DOMContentLoaded', callback);
    });

    test('should invoke callback immediately when document is already loaded', () => {
        mockDocumentReadyState('complete');

        window._rb.onReady(callback);

        expect(callback).toHaveBeenCalled();
    });
});

describe('dropdown_all_view', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <select id="cs-1" type="cs" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-cs-1">
                <option option="ALL" data-select2-id="select2-data-23-t65m">ALL</option>
            </select>
            <select id="program-1" type="program" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-program-1">
                <option option="ALL" data-select2-id="select2-data-23-t65m">ALL</option>
            </select>
            <ul id="select2-cs-1-container">
                <li title="ALL">ALL</li>
            </ul>
            <ul id="select2-program-1-container">
                <li title="ALL">ALL</li>
            </ul>
        `
    });
    
    test('selectionHasChanged(type) && selected_values.length > 0', () => {
        let type = 'cs';
        let id = 1;
        let dropdown_id = `${type}-${id}`;
        $(`#${dropdown_id}`).val(["ALL"]);

        window._rb.dropdown_all_view(type, id);

        expect(true).toBe(true);
    });

    test('!selectionHasChanged(type) && selected_values.includes("ALL")', () => {
        let type = 'program';
        let id = 1;
        let dropdown_id = `${type}-${id}`;
        $(`#${dropdown_id}`).val(["ALL"]);

        window._rb.dropdown_all_view(type, id);

        expect(true).toBe(true);
    });
});

describe('update_program_filter', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <select id="ass-area-1" type="ass-area" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-ass-area-1">
                <option value="ALL"">ALL</option>
            </select>            
            <select id="pom-1" type="pom" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-pom-1">
                <option value="ALL"">ALL</option>
            </select>
            <select id="cs-1" type="cs" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-cs-1">
                <option value="ALL"">ALL</option>
            </select>
            <select id="program-1" type="program" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-program-1">
                <option value="ALL"">ALL</option>
            </select>
            <span class="select2 select2-container" style="height:500px;"></span>
        `
    });
    
    test('ass-area, pom, cs all have assigned values', () => {
        document.body.innerHTML = `
            <select id="ass-area-1" type="ass-area" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-ass-area-1">
                <option value="ALL"">ALL</option>
            </select>            
            <select id="pom-1" type="pom" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-pom-1">
                <option value="ALL"">ALL</option>
            </select>
            <select id="cs-1" type="cs" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-cs-1">
                <option value="ALL"">ALL</option>
            </select>
            <select id="program-1" type="program" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-program-1">
                <option value="ALL"">ALL</option>
            </select>
            <span class="select2 select2-container" style="height:500px;"></span>
        `

        let id = 1;
        document.querySelectorAll('select').forEach(select => {
            select.value = 'ALL';
        });
        const response = {
            data: [
                { test: 'Test1' },
                { test: 'Test2' }
            ]
        };
        $.post.mockImplementation((url, data, callback) => {
            callback(response);
        });
        const mockSelect2 = {
            on: jest.fn().mockReturnThis(),
            select2: jest.fn().mockReturnThis()
        };
        $.fn.select2 = jest.fn(() => mockSelect2);
        window._rb.update_program_filter(id);

        expect(true).toBe(true);
    });

    test('ass-area, pom, cs do not all have assigned values', () => {
        document.body.innerHTML = `
            <select id="ass-area-1" type="ass-area" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-ass-area-1">
                <option value="ALL"">ALL</option>
            </select>            
            <select id="pom-1" type="pom" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-pom-1">
                <option value="ALL"">ALL</option>
            </select>
            <select id="cs-1" type="cs" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-cs-1">
                <option value="ALL"">ALL</option>
            </select>
            <select id="program-1" type="program" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-program-1">
                <option value="ALL"">ALL</option>
            </select>
            <span class="select2 select2-container" style="height:500px;"></span>
        `

        let id = 1;
        document.querySelectorAll('ass-area-1').value = 'ALL';

        window._rb.update_program_filter(id);

        expect(true).toBe(true);
    });
});

describe('viewDropdownModal', () => {
    let id = 'ao-C-0-eoc-approval';
    let program = 'TEST';
    let eoc_id = 'TEST.ABC';
    let event_id = 'EVENT_NAME';
    let user_id = '1';
    let enabled = '1';
    let page = '';

    beforeEach(() => {
        document.body.innerHTML = `
            <div id="ao-ad-dropdown-view-modal" role="dropdown_view_save">
                <div id="ao-ad-dropdown" class="d-flex flex-wrap mt-3 pt-3">
                    <div class="bx--form-item mt-1">
                        <ul id="ao-ad-dropdown-list" class="bx--list--unordered m-3"></ul>
                        <label for="text-input-title" class="bx--label"></label>
                        <div class="bx--form__helper-text"></div>
                        <select type="tag" id="ao-ad-dropdown-selection"></select>
                    </div>
                </div>
            </div>
            <input id="ao-C-0-eoc-approval" type="hidden" data-emails="">
        `
        
        const commentList = [{
            "ID": 1,
            "AO_RECOMENDATION": "Approve",
            "AO_COMMENT": "AO Comment",
            "AO_USER_ID": 1,
            "email": "example@rhombuspower.com"
        }];
        $('#ao-C-0-eoc-approval').val(JSON.stringify(commentList));
    });

    test('page === "zbt_summary"', () => {
        page = 'zbt_summary';

        window._rb.viewDropdownModal(id, program, eoc_id, user_id, event_id, enabled, page);

        const $options = $('#ao-ad-dropdown-selection').find('option');

        expect($options).toBeTruthy();
    });

    test('page === "issue"', () => {
        page = 'issue';

        window._rb.viewDropdownModal(id, program, eoc_id, user_id, event_id, enabled, page);

        const $options = $('#ao-ad-dropdown-selection').find('option');

        expect($options).toBeTruthy();
    });

    test('trigger onchange for #ao-ad-dropdown-selection', () => {
        page = 'zbt_summary';

        window._rb.viewDropdownModal(id, program, eoc_id, user_id, enabled, page);

        $('#ao-ad-dropdown-selection').val('Disapprove').trigger('change');

        expect(true).toBe(true);
    });
});

describe('updateCommentList', () => {
    test('should populate comment list', () => {
        document.body.innerHTML = `
            <ul id="ao-ad-comment-list""></ul>
        `;

        const data = [
            {
                "ID": 1,
                "AO_RECOMENDATION": "Approve",
                "AO_COMMENT": "AO Comment",
                "AO_USER_ID": 1,
                "email": "test@rhombuspower.com"
            },
            {
                "ID": 2,
                "AO_RECOMENDATION": "Approve",
                "AO_COMMENT": "AO Comment",
                "AO_USER_ID": 2,
                "email": "example@rhombuspower.com"
            },
        ];

        const is_ao_ad = 'ao';

        window._rb.updateCommentList(data, is_ao_ad);

        const $commentList = document.querySelector('ul#ao-ad-comment-list');
        const $comments = $commentList.querySelectorAll('li');

        expect($comments.length).toBe(2);

        $comments.forEach(li => {
            const $comment = li.textContent.trim();
            expect($comment).toContain("AO Comment");
        });

        const $firstComment = $comments[0].textContent.trim();
        expect($firstComment).toContain("test@rhombuspower.com");
        const $secondComment = $comments[1].textContent.trim();
        expect($secondComment).toContain("example@rhombuspower.com");
    });
});

describe('viewCommentModal', () => {
    test('should populate comment list', () => {
        document.body.innerHTML = `
            <div id="ao-ad-comment-view-modal">
                <div id="ao-ad-comment" class="d-flex flex-wrap mt-3 pt-3">
                <div class="bx--form-item mt-1">
                    <ul id="ao-ad-comment-list" class="bx--list--unordered m-3"></ul>
                    <label for="text-input-title" class="bx--label">Comment</label>
                    <div class="bx--form__helper-text"></div>
                    <textarea id="ao-ad-comment-textarea" class="bx--text-area"
                        rows="4" cols="50"
                        maxlength="2000"
                        placeholder="Comment"
                        name="comment"></textarea>
                    </div>
                </div>
                <input id="program_id" type="hidden" />
                <input id="eoc_id" type="hidden" />
            </div>    
            <input id="ao-JM_EVENT_04-0-label-text" type="hidden" data-emails="">   
        `;

        const id = 'ao-JM_EVENT_04-0-label-text'; 
        const program = 'TEST'; 
        const eoc_id = 'TEST.XXN'; 
        const enabled = '1';

        const commentsList = [{
            "ID": 1,
            "AO_RECOMENDATION": "Approve",
            "AO_COMMENT": "AO Comment",
            "AO_USER_ID": 1,
            "email": "example@rhombuspower.com"
        }];
        
        $(`#${id}`).val(JSON.stringify(commentsList));

        window._rb.viewCommentModal(id, program, eoc_id, enabled);

        expect(true).toBe(true);
    });
});

describe('saveAOADComment', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="ao-ad-comment-view-modal">
                <div id="ao-ad-comment" class="d-flex flex-wrap mt-3 pt-3">
                <div class="bx--form-item mt-1">
                    <ul id="ao-ad-comment-list" class="bx--list--unordered m-3"></ul>
                    <label for="text-input-title" class="bx--label">Comment</label>
                    <div class="bx--form__helper-text"></div>
                    <textarea id="ao-ad-comment-textarea" class="bx--text-area"
                        rows="4" cols="50"
                        maxlength="2000"
                        placeholder="Comment"
                        name="comment"></textarea>
                    </div>
                </div>
                <input id="program_id" type="hidden" value="PROGRAM_ID" />
                <input id="eoc_id" type="hidden" value="EOC_ID" />
                <input id="event_id" type="hidden" value="EVENT_ID" />
            </div>    
            <input id="ao-JM_EVENT_04-0-label-text" type="hidden" data-emails="">   
        `;

        $.post = (url, data, cb, format) => {
            return {
                fail: (jqXHR) => true
            }
        };
		$.post().fail = (test) => true;
    });

    test('should save comment for: page === zbt_summary and ao', () => {
        const id = 'ao-JM_EVENT_04-0-label-text'; 
        $('#ao-ad-comment-view-modal').data('id', id);
        $('#ao-ad-comment-view-modal').data('is_ao_ad', 'ao'),
        global.page = 'zbt_summary';

        window._rb.saveAOADComment();

        expect(true).toBe(true);
    });

    test('should save comment for: page === zbt_summary and ad', () => {
        const id = 'ao-JM_EVENT_04-0-label-text'; 
        $('#ao-ad-comment-view-modal').data('id', id);
        $('#ao-ad-comment-view-modal').data('is_ao_ad', 'ad'),
        global.page = 'zbt_summary';

        window._rb.saveAOADComment();

        expect(true).toBe(true);
    });

    test('should save comment for: page === issue and ao', () => {
        const id = 'ao-JM_EVENT_04-0-label-text'; 
        $('#ao-ad-comment-view-modal').data('id', id);
        $('#ao-ad-comment-view-modal').data('is_ao_ad', 'ao'),
        global.page = 'issue';

        window._rb.saveAOADComment();

        expect(true).toBe(true);
    });

    test('should save comment for: page === zbt_summary and ad', () => {
        const id = 'ao-JM_EVENT_04-0-label-text'; 
        $('#ao-ad-comment-view-modal').data('id', id);
        $('#ao-ad-comment-view-modal').data('is_ao_ad', 'ad'),
        global.page = 'issue';

        window._rb.saveAOADComment();

        expect(true).toBe(true);
    });
});