/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

// Inject globals
const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;
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

afterEach(() => {
  assignMock.mockClear();
});

const data = {
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
    subapps_mapping: [
        1,2,3
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
        [1],
    ],
    result: [{
        user_role_id: JSON.stringify([1,2]),
        name: 'name',
        status: 'status',
        subapp_id: 2
    }]
}
global.label_mappings = {
    subapps_mapping: [1,2,3]
}
global.Rhombus_Datatable = class Rhombus_Datatable {
    constructor (...params) {
        this.selectedRowData = {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]'
        }
        for (let i = 0; i < params[0]['datatable_properties']['columns'].length; i++) {
            let column = params[0]['datatable_properties']['columns'][i];
            let key = column['data']
            if (column.render) {
                label_mappings["roles"] = [{"1":"admin"}]
                label_mappings["subapps_mapping"] = [1,2,3];
                column.render(this.selectedRowData[key], 'load', {subapp_id: 2});
            }
        }
        params[0]['set_form_values'](this.selectedRowData)
        params[0]['delete_message'](this.selectedRowData)
        $("#role_mappings_input_user_roles select").val(JSON.parse(this.selectedRowData['user_role_id']));

        params[0]['get_form_values']()
    }
    static copy_button = 'value'
    static edit_button(){return}
    static delete_button(){return}
    static addDropdown(){return}
    createColumnSelector(){return}
    setup_add_record(){return}
    init_table(){return}
    initialize_delete(){return}
    initialize_submit(add_url, edit_url, cb, add_row_data = false, extra_post_data = false){
        global.first_load_role_mappings = false;
        cb("editData", {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]'
        });
        cb("", {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]'
        });
        cb("editData", {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '[{}]'
        });
        return
    }
    refreshTable(){return}
    check_record_exist(innerCb){
        innerCb(this.selectedRowData)
    }
}
$.post = (url, post_data, callback) => {
    if (url ==='/facs_manager/autopop') callback(data);
    if (url ==='/facs_manager/get_facs') callback(data);
}

rhombus_dark_mode = () => {return}
describe('load_mappings_table', () => {
    beforeEach(() => {
        document.body.innerHTML = `
        <div id="role_mappings_input_user_roles"><select multiple><option value="1">Admin</option><option value="2">User</option></select></div>
        <div>
            <button type="button" class="btn btn-secondary button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>
            <ul class="dropdown-menu keep-open" id="1690503385150_dropdown_column_selector">
                <li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li>
            </ul>
            <div id="role_mappings_tab"> <button id="autopop_button"></button><button id="mapping_pattern_1" onchange=""><input checked name="mapping_pattern" value="mapping_pattern_1"></button><button id="mapping_pattern_2" onchange=""><input name="mapping_pattern" value="mapping_pattern_1"></button><button id="mapping_pattern_3" onchange=""><input name="mapping_pattern" value="mapping_pattern_1"></button></div>
            </div>
            <div id="autopop_input_user_roles">
            <button type="button" class="btn btn-light" data-dismiss="modal" id="role_mappings_confirmDeleteBtn">Delete </button>
            <select class="btn btn-group select2-hidden-accessible" id="1690501113060_dropdown" value="" required="" data-select2-id="select2-data-1690501113060_dropdown" multiple="" tabindex="-1" aria-hidden="true">
                <option value="1" data-select2-id="select2-data-8-d8xw">USER</option>
                <option value="2">MODERATOR</option>
                <option value="3">ADMIN</option>
                <option value="4">ADMIN_2</option>
                <option value="5">ADMIN_3</option>
            </select>
            </div>
            <div>
            <form id="autopopForm">
                <div id="autopop_input_user_roles">
                <select class="btn btn-group select2-hidden-accessible" id="1690501113060_dropdown" value="" required="" data-select2-id="select2-data-1690501113060_dropdown" multiple="" tabindex="-1" aria-hidden="true">
                    <option value="1" data-select2-id="select2-data-8-d8xw">USER</option>
                    <option value="2">MODERATOR</option>
                    <option value="3">ADMIN</option>
                    <option value="4">ADMIN_2</option>
                    <option value="5">ADMIN_3</option>
                </select>
                </div>
            </form>
        </div>
        `
        $("#mapping_pattern_1")[0].addEventListener = jest.fn((e, callback) => {
            callback(data)
        })
        $("#mapping_pattern_2")[0].addEventListener = jest.fn((e, callback) => {
            callback(data)
        })
        $("#mapping_pattern_3")[0].addEventListener = jest.fn((e, callback) => {
            callback(data)
        })
        require('../facs/role_mappings_table');
    })
    test('load mappings table', () => {
        jest.resetModules();
        $("#role_mappings_tab").trigger('shown.bs.tab');
        $("#autopop_button").trigger('click');
        $("#mapping_pattern_1").trigger('change');
        expect($("#mapping_pattern_1 > input[name='mapping_pattern']").attr('checked')).toBe('checked');
        expect(global.first_load_role_mappings).toBe(false);
        $("#role_mappings_tab").trigger('shown.bs.tab');
    });
})
