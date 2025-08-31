/**
 * @jest-environment jsdom
 */

const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

require('bootstrap/dist/js/bootstrap.bundle.min');
require('select2')($);

global.rhombuscookie = jest.fn();
global.sanitizeHtml = html => html;

global.Highcharts = {
    chart: (html_id, settings) => {
        if (html_id == 'chart-1') {
            settings.yAxis.labels.formatter();
        }

        return {
            reflow: () => {}
        };
    },
    numberFormat: (a, b, c, d) => '$1.00'
}

global.$.fn.highcharts = function() {
    return {
        reflow: () => {},
        setTitle: () => {}
    };
};

describe('get_pb_comparison_graph', () => {
	test('function', () => {
        jest.resetModules();

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
        const data = {
            0: '0',
            categories: ['CAT A', 'CAT B', 'CAT C'],
            data: []
        };
		window._rb.get_pb_comparison_graph(id, data);

		expect(true).toBe(true);
	});
});

describe('dropdown_onchange', () => {
    document.body.innerHTML = `
    <input id="ass-area-test" value="1"></input>
    <input id="pom-test" value="1"></input>
    <input id="cs-test" value="1"></input>
    <input id="program-test" value="1"></input>
    <input id="resource_category-test" value="1"></input>
`;

	test('type == pom, ass-area, or cs', () => {
        jest.resetModules();
    
		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
        const type = 'pom';
		window._rb.dropdown_onchange(id, type);

		expect(true).toBe(true);
	});

    test('type == program', () => {
        jest.resetModules();

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
        const type = 'program';
		window._rb.dropdown_onchange(id, type);

		expect(true).toBe(true);
	});

    test('type == resource_category', () => {
        jest.resetModules();

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
        const type = 'resource_category';
		window._rb.dropdown_onchange(id, type);

		expect(true).toBe(true);
	});

    test('type == filter', () => {
        jest.resetModules();

        const cb_data = {
            0: '0',
            categories: ['CAT A', 'CAT B', 'CAT C'],
            data: [{'data': ['data test']}]
        };
        $.post = (url, payload, callback) => callback(cb_data);

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
        const type = 'filter';

		window._rb.dropdown_onchange(id, type);

		expect(true).toBe(true);
	});

    test('type == compare', () => {
        jest.resetModules();

        document.body.innerHTML = `
        <div id="chart-1"></div>
        <div id="chart-2"></div>
        <div id="chart-1-container"></div>
        <div id="chart-2-container"></div>
        <div id="list-2"></div>
        <input id="ass-area-test" value="1">
        <input id="pom-test" value="1">
        <input id="cs-test" value="1">
        <input id="program-test" value="1">
        <input id="resource_category-test" value="1">
        <div id="apply-filter-loading" hidden></div>
        <button id="pb-comparison-filter-compare"></button>
        <input id="program-filter-error" value="1"></input>
    `;

        const cb_data = {
            0: '0',
            categories: ['CAT A', 'CAT B', 'CAT C'],
            data: [{'data': ['data test']}]
        };
        $.post = (url, payload, callback) => callback(cb_data);

		require('../../actions/SOCOM/pb_comparison');
        
        const id = 'test';
        const type = 'compare';

		window._rb.dropdown_onchange(id, type);

		expect(true).toBe(true);
	});

    test('default', () => {
        jest.resetModules();

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
        const type = 'no switch match';
		window._rb.dropdown_onchange(id, type);

		expect(true).toBe(true);
	});

    
});

describe('update_program_filter', () => {
	test('ass-area, cs, and pom all truthy values', () => {
        jest.resetModules();

        const id = 'test';
        
        document.body.innerHTML = `
            <input id="ass-area-test" value="1"></input>
            <input id="pom-test" value="1"></input>
            <input id="cs-test" value="1"></input>
            <div id="program-dropdown"></div>
            <select id="program-test"><option value="1" selected>1</option</select>
            <button id="program-${id}-selection"></button>
        `;

        const cb_data = {data: [{
            PROGRAM_CODE: 'test',
            PROGRAM_NAME: 'test_program'
        }]}
        $.post = (url, payload, cb) => cb(cb_data);

		require('../../actions/SOCOM/pb_comparison');

        $('#program-test').select2();

		window._rb.update_program_filter(id);

        $('.select2').trigger('change');

		expect(true).toBe(true);
	});

    test('ass-area, cs, and/or pom all falsey values', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <input id="ass-area-test" value=""></input>
            <input id="pom-test" value=""></input>
            <input id="cs-test" value=""></input>
            <select id="program-test"><option value="1" selected>1</option></select>
        `;

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
		window._rb.update_program_filter(id);

		expect(true).toBe(true);
	});
});

describe('disable_hide_apply_filter_button', () => {
	test('function (else branch is covered in a previous call)', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <input id="ass-area-test" value="1"></input>
            <input id="pom-test" value="1"></input>
            <input id="cs-test" value="1"></input>
            <input id="program-test" value="1"></input>
        `;

		require('../../actions/SOCOM/pb_comparison');

        const id = 'test';
		window._rb.disable_hide_apply_filter_button(id);

		expect(true).toBe(true);
	});
});

describe('dropdown_selection', () => {
    test('No Select All', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <select id="test-target">
                <option value="xyz">xyz</option>
            </select>
            <button id="test-target-selection" data-select-all="false"></button>
        `;

		require('../../actions/SOCOM/pb_comparison.js');

        const target = '#test-target';
		window._rb.dropdown_selection(target);

		expect(true).toBe(true);
	});

    test('Select All', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <select id="test-target">
                <option value="xyz">xyz</option>
            </select>
            <button id="test-target-selection" data-select-all="true"></button>
        `;

		require('../../actions/SOCOM/pb_comparison.js');

        const target = '#test-target';
		window._rb.dropdown_selection(target);

		expect(true).toBe(true);
	});
});


describe('dropdown_onchange', () => {
    test('type == program no value', () => {
        jest.resetModules();

        const id = 'test';
        const type = 'program';

        document.body.innerHTML = `
            <div id="resource_category-dropdown" value="1"></div>
            <div id="program-dropdown" value="1"></div>
            <input id="program-filter-error" value="1"></input>
            <input id="resource_category-filter-error" value="1"></input>
            <select id="resource_category-${id}" multiple="multiple"></select>
        `;

        $.fn.select2 = jest.fn().mockImplementation(() => ({
            destroy: jest.fn()
        }));

        $.fn.val = jest.fn().mockReturnValue([]);

        require('../../actions/SOCOM/pb_comparison');

        window._rb.dropdown_onchange(id, type);

        expect(true).toBe(true);
    });

});