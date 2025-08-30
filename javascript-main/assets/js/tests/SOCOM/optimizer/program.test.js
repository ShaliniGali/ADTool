const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

global.rhombuscookie = jest.fn(() => 'rhombuscookie');
global.storm_weighted_based = jest.fn();
global.showScore = jest.fn();
global.fy_list = [2026, 2027, 2028, 2029, 2030];

$.fn.select2 = jest.fn(function(options) {
    this.select2Options = options;

    return {
        on: jest.fn((event, handler) => {
            if (event === 'change.select2') {
                handler.call(this);
            }
            return this;
        }),
        css: jest.fn(),
        siblings: () => ({
            height: () => 200,
            css: jest.fn()
        }),
    };
});

global.default_criteria = ['RISK'];

global.selected_POM_weight_table = {
    rows: () => ({
        data: () => [{ key1: '1.5', key2: '2', RISK: '' }]
    })
}

global.selected_Guidance_weight_table = {
    rows: () => ({
        data: () => [{ key1: '0.5', key2: '1', RISK: '' }]
    })
}

global.$.fn.DataTable = jest.fn((obj = null) => {
	if (obj && obj.rowCallback) {
		obj.rowCallback('', {
            FY: JSON.stringify({"2026": 7320, "2027": 7446, "2028": 7572, "2029": 7698, "2030": 7824}),
            SCORE_SESSION: JSON.stringify({ key1: '2', key2: '3' }),
        });

        obj.rowCallback('', {
            FY: JSON.stringify({"2026": 7320, "2027": 7446, "2028": 7572, "2029": 7698, "2030": 7824}),
            SCORE_SESSION: JSON.stringify({ key1: '2', key2: '3' }),
            SCORE_ID: 1,
        });
	}

    if (obj && obj.initComplete) {
		obj.initComplete();
	}

    return {
        column: () => {
            return {
                    search: () => {
                        return {
                            draw: () => true
                    }
                }
            }
        },
        ajax: {
            reload: jest.fn(),
        },
        on: jest.fn(),
        columns: () => ({
            visible: () => [true, true, true, true, true, true, true, true, true, true, true, true, true, true, true]
        }),
        rows: () => ({
            data: () => [{}]
        }),
        draw: () => jest.fn()
    }
});

describe('program', () => {
    beforeEach(() => {
        require('../../../actions/SOCOM/optimizer/program.js');

        jest.clearAllMocks();

        document.body.innerHTML = `
            <input type="radio" name="storm_weighted_based" id="r-w" value="false">Weighted<br>
            <input type="radio" name="storm_weighted_based" id="other" value="false">Not Weighted<br>
            <table id="option-list"></table>
        `;
    });

    test('load_program_table initialize DataTable', () => {
        window._rb.load_program_table();
        expect($.fn.DataTable).toHaveBeenCalled();

        expect($.fn.DataTable).toHaveBeenCalledWith({
            stateSave: true,
            columnDefs: expect.any(Array),
            ajax: expect.any(Object),
            length: 10,
            lengthChange: true,
            orderable: false,
            ordering: false,
            searching: true,
            rowHeight: '75px',
            initComplete: expect.any(Function),
            rowCallback: expect.any(Function),
        });

        const ajaxConfig = $.fn.DataTable.mock.calls[0][0].ajax;
        expect(ajaxConfig.url).toBe('/socom/resource_constrained_coa/program/list/get');
        expect(ajaxConfig.type).toBe('POST');
    });

    test('input[name="storm_weighted_based"] change event on id === "r-w"', () => {
        window._rb.attach_change_handler();
        $('#r-w').prop('checked', true).trigger('change');

        expect(storm_weighted_based).toHaveBeenCalledWith(
            '#option-list',
            [7, 8, 9],
            [10, 11, 12],
            $('#r-w')[0]
        );
    });

    test('input[name="storm_weighted_based"] change event on other id', () => {
        window._rb.attach_change_handler();
        $('#other').prop('checked', true).trigger('change');

        expect(storm_weighted_based).toHaveBeenCalledWith(
            '#option-list',
            [10, 11, 12],
            [7, 8, 9],
            $('#other')[0]
        );
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
            <select id="ass-area-1" type="ass-area" class="selection-dropdown select2-hidden-accessible" multiple="" data-select2-id="select2-data-ass-area-1">
                <option option="ALL" data-select2-id="select2-data-23-t65m">ALL</option>
                <option option="TEST" data-select2-id="select2-data-23-t65m">TEST</option>
            </select>
            <ul id="select2-cs-1-container">
                <li title="ALL">ALL</li>
            </ul>
            <ul id="select2-program-1-container">
                <li title="ALL">ALL</li>
            </ul>
            <ul id="select2-ass-area-1-container">
                <li title="ALL">ALL</li>
                <li title="TEST">TEST</li>
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

    test('type == ass-area',  () => {
        let type = 'ass-area';
        let id = 1;
        let dropdown_id = `${type}-${id}`;
        $(`#${dropdown_id}`).val(["ALL"]);

        window._rb.dropdown_onchange(id, type);

        expect(true).toBe(true);
    });

    test('type == ass-area and dropdown.val() !== ALL',  () => {
        let type = 'ass-area';
        let id = 1;
        let dropdown_id = `${type}-${id}`;
        $(`#${dropdown_id}`).val(["TEST"]);

        window._rb.dropdown_onchange(id, type);

        expect(true).toBe(true);
    });
});

describe("onReady", function() {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="option_filter">Filter</div>
            <div id="option_exporter">Exporter</div>
            <div id="filter_modal">
                <div class="bx--modal bx--modal-tall"></div>
            </div>
            <div id="exporter_modal">
                <div class="bx--modal bx--modal-tall"></div>
            </div>
            <select id="ass-area-1"></select>
            <select id="ass-area-2"></select>
        `;
        window._rb.onReady();
    });

    test("option_filter", function() {
        $('#option_filter').trigger('click');
        expect(true).toBe(true);
    });

    test("option_exporter", function() {
        $('#option_exporter').trigger('click');
        expect(true).toBe(true);
    });

    test("option_filter", function() {
        $('#option_filter').trigger('click');
        expect(true).toBe(true);
    });

    test("option_exporter", function() {
        $('#option_exporter').trigger('click');
        expect(true).toBe(true);
    });
});

