jest.mock('jquery', () => {
    const m$ = jest.fn(() => m$);
    m$.DataTable = jest.fn();
    m$.fn = {
        DataTable: m$.DataTable
    };
    return m$;
});

const jQuery = require('jquery');
$ = jQuery;

global.rhombuscookie = jest.fn(() => 'mock_token');

describe('load_storm_table', () => {
    beforeEach(() => {
        require('../../../actions/SOCOM/weights/storm');
        global.load_storm_table = window._rb.load_storm_table;
        $.mockClear();
        $.fn.DataTable.mockClear();
    });

    test('should initialize the DataTable with correct settings', () => {
        document.body.innerHTML = `
            <table id="storm-score-display"></table>
        `;

        global.load_storm_table();

        expect($.fn.DataTable).toHaveBeenCalledWith({
            columnDefs: [
                { targets: 0, data: "storm_id", name: "StoRM ID", defaultContent: '' },
                { targets: 1, data: "storm", name: "StoRM", defaultContent: '' }
            ],
            ajax: {
                url: "/socom/resource_constrained_coa/program/list/get_storm",
                type: 'POST',
                data: {
                    rhombus_token: expect.any(Function),
                },
                dataSrc: 'data',
            },
            length: 10,
            lengthChange: true,
            orderable: false,
            ordering: false,
            searching: true,
            rowHeight: '75px'
        });

        const dataCall = $.fn.DataTable.mock.calls[0][0].ajax.data.rhombus_token;
        expect(dataCall()).toBe('mock_token');

    });

    test('should not reinitialize the table if load_storm_table is null', () => {
        global.load_storm_table = null;

        if (global.load_storm_table) global.load_storm_table();

        expect($.fn.DataTable).not.toHaveBeenCalled();
    });
});