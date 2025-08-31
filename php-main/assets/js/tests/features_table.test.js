const jQuery = require('jquery');
global.$ = jQuery;
global.jQuery = jQuery;
global.rhombuscookie = jest.fn(() => true);
global.loginWithGoogle2FA = jest.fn(() => true);
global.sanitizeHtml = jest.fn(() => '<div></div>');
$.fn.ready = (callback) => { callback() };

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require("tilt.js/dest/tilt.jquery.min.js")($);

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
		params[0]['refresh_callback'](this.selectedRowData)
        params[0]['get_form_values']()
    }
    static copy_button = 'value'
    static edit_button(){return}
    static delete_button(){return}
    static addDropdown(bool1, jQueryDiv, func, bool2, json){
		func();	// Note: using this as a callback because the actual file does the same.
        return;
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

beforeEach(() => {
	jest.resetModules();
});

test('features_tab if first_load_features == true', () => {
	document.body.innerHTML = '\
		<div id="features_tab"></div>\
	';
	let callbackData = {
		'result': 'success'
	};
	$.post = (url, postData, callbackFunc) => {
		callbackFunc(callbackData);
	}

	require('../facs/features_table.js');
	$('#features_tab').trigger('shown.bs.tab');
	expect(true).toBe(true);
});

test('features_tab else first_load_features == false', () => {
	document.body.innerHTML = '\
		<div id="features_tab"></div>\
	';
	let callbackData = {
		'result': 'success',
		'function_list': 'test_list'
	};
	$.post = (url, postData, callbackFunc) => {
		callbackFunc(callbackData);
	}

	require('../facs/features_table.js');
	$('#features_tab').trigger('shown.bs.tab');
	$('#features_tab').trigger('shown.bs.tab');
	expect(true).toBe(true);
});

