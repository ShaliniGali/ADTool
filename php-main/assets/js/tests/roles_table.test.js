/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

// Inject globals
const jQuery = require('jquery'); 

global.$ = jQuery;
global.action_button = () => null;
global.rhombuscookie = () => null;
global.sanitizeHtml = html => html;

jest.useFakeTimers(); // or you can set "timers": "fake" globally in configuration file

require('select2')($);
require('jquery-datepicker')($);

// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');


let assignMock = jest.fn();

delete window.location;
window.location = { assign: assignMock };
global.roles = {
    "admin": {
        id: 1,
        name: "Hey",
        status: "active"
    }
}
global.Rhombus_Datatable = class Rhombus_Datatable {
    constructor (...params) {
        this.selectedRowData = {
            id: 1,
            name: "Hey",
            status: "active"
        }
        for (let i = 0; i < params[0]['datatable_properties']['columns'].length; i++) {
            let column = params[0]['datatable_properties']['columns'][i];
            let key = column['data']
            if (column.render){
                column.render(this.selectedRowData[key]);
            }
        }
        params[0]['set_form_values'](this.selectedRowData)
        params[0]['delete_message'](this.selectedRowData)
        params[0]['get_form_values']()
    }
    static copy_button = 'value'
    static edit_button(){return}
    static delete_button(){return}
    static addDropdown(){
        return
    }
    createColumnSelector(){return}
    setup_add_record(){return}
    init_table(){return}
    initialize_delete(){return}
    initialize_submit(add_url, edit_url, cb, add_row_data = false, extra_post_data = false){
        global.first_load_role_mappings = false;
        cb("editData", {
            name: "hello"
        });
        cb("", {
            name: "hey"
        });
        return
    }
    refreshTable(){return}
    check_record_exist(innerCb){
        innerCb(this.selectedRowData)
    }
}

afterEach(() => {
  assignMock.mockClear();
});

rhombus_dark_mode = () => {return}

test('load role table', () => {
    jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<table id="roles_table">
		</table>
	`;

	// Include source file.
	require('../facs/roles_table');

	// Run test.
	window._rb.role_table
    expect(true).toBe(true);
})