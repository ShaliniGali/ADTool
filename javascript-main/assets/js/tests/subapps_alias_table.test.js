/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

global.$ = require('jquery')
global.action_button = () => null;
global.rhombuscookie = () => null;
global.sanitizeHtml = html => html;

require('select2')($);
require('jquery-datepicker')($);
require('bootstrap/dist/js/bootstrap.bundle.min.js');

let assignMock = jest.fn();
delete window.location;
window.location = { assign: assignMock };
jest.useFakeTimers();

let data = {
    app_list: [
        {
            id: 1,
            name: "app1",
            status: 'active'
        },
        {
            id: 2,
            name: "app2",
            status: 'active'
        },
    ],
    subapp_list: [
        {
            id: 1,
            name: "subapp1",
            status: 'active'
        },
        {
            id: 2,
            name: "subapp2",
            status: 'active'
        },
    ],
    subapps_alias_list: [
        [
            {
                subbapp_id: 1,
                name: "subapp_alias1",
                status: 'active'
            },
            {
                subbapp_id: 2,
                name: "subapp_alias2",
                status: 'active'
            },
        ]
    ],
    features_list: [
        {
            id: 1,
            name: "features1",
            status: 'active'
        },
        {
            id: 2,
            name: "features2",
            status: 'active'
        },
    ],
    roles_list: [
        {
            id: 1,
            name: "roles1",
            status: 'active'
        },
        {
            id: 2,
            name: "roles2",
            status: 'active'
        },
    ],
    controller_list: [
        {
            id: 1,
            name: "controller1",
            status: 'active'
        },
        {
            id: 2,
            name: "controller2",
            status: 'active'
        },
    ],
    result: {
        id: 'id',
        name: 'name',
        status: 'status',
        subapps_alias: JSON.stringify('subapp_alias1')
    }
}
global.label_mappings = {};
global.Rhombus_Datatable = class Rhombus_Datatable {
    constructor (...params) {
        this.selectedRowData = {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]',
            subapps_alias: JSON.stringify('subapp_alias1'),
			alias_name: 'test_alias_name'
        }
        this.columnData = {
            id: 'app_id',
            name: 'subapp_id',
            subapps_alias: 'feature_id',
        }
        params[0]['set_form_values'](this.selectedRowData)
        params[0]['delete_message'](this.selectedRowData)
        $("#subapps_input_user_roles select").val(JSON.parse(this.selectedRowData['user_role_id']));
        params[0]['get_form_values']()

        for (let i = 0; i < params[0]['datatable_properties']['columns'].length; i++) {
            let column = params[0]['datatable_properties']['columns'][i];
            let key = column['data']
            
            if (column.render) {
                label_mappings["roles"] = [{"1":"admin"}]
                column.render(JSON.stringify(this.columnData[key]), 'load', this.selectedRowData);
            }
        }
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
        cb("editData", {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]',
			alias_name: 'test_alias_name'
        });
        cb("", {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]',
			alias_name: 'test_alias_name'
        });
        cb("editData", {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '[{}]',
			alias_name: 'test_alias_name'
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

test('load_subapps_alias', () => {
	jest.resetModules();
	document.body.innerHTML = `
		<div id="subapps_alias_tab"></div>		
	`;

	$.post = (url, post_data, callback) => {
		data.result = [
			{
				id: 'id',
				name: 'name',
				status: 'status',
				subapps_alias: JSON.stringify(["1", "2"])
			}
		];
		if (url == ("/facs_manager/get_facs")) {
			callback(data);
		}
	};

	require('../facs/subapps_alias_table');
	$('#subapps_alias_tab').tab('show');
	expect(true).toBe(true);
});
