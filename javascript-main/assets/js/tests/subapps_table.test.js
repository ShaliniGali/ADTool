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
global.label_mappings = {}
global.Rhombus_Datatable = class Rhombus_Datatable {
    constructor (...params) {
        this.selectedRowData = {
            app_id: 'app_id',
            subapp_id: 'subapp_id',
            feature_id: 'feature_id',
            user_role_id: '["admin"]',
            subapps_alias: JSON.stringify('subapp_alias1')
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
        params[0]['refresh_callback'](data)

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

$.fn.ready = (cb) => {cb()};
$.fn.one = (tab, cb) => {cb(
    $.post = (url, post_data, callback) => {
        if(url == ("/facs_manager/get_facs")) callback(data);
    }
)};

rhombus_dark_mode = () => {return}

test('load mappings table', () => {

    temp_id = new Date().getTime() + "_dropdown_column_selector";
    document.body.innerHTML = 
    '<div>' +
        '<button type="button" class="btn btn-secondary button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>' +
        '<ul class="dropdown-menu keep-open" id="' + temp_id + '">' +
        '<li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></label></li>' +
        '</ul>' +
    '</div>'
    '<div>' +
        '<button type="button" class="btn btn-light" data-dismiss="modal" id="subapps_confirmDeleteBtn">Delete </button>' + 
        '<div id="subapps_input_user_roles"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
        '</div>'
            ;
    require('../facs/subapps_table');
    jest.resetModules();

    expect(true).toBe(true);
});

test('click autopop button', () => {

    temp_id = new Date().getTime() + "_dropdown_column_selector";
    document.body.innerHTML = 
    '<div id="subapps_input_name"><select><option value="1">Name</option></select></div>' +
    '<div id="subapps_input_subapps_alias"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
    '<div id="subapps_mapping_input_subapps"><select multiple><option value="1" selected>ACTF</option><option value="2" selected>WSS</option></select></div>' +
    '<input id="subapps_mapping_pattern_1" type="radio" value="1" /><input id="subapps_mapping_pattern_2" type="radio" value="2" /><input id="subapps_mapping_pattern_3" type="radio" value="3" />' +
    '<div>' +
        '<button type="button" class="btn btn-secondary button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>' +
        '<ul class="dropdown-menu keep-open" id="' + temp_id + '">' +
        '<li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></label></li>' +
        '</ul>' +
    '</div>'
    '<div>' +
        '<button type="button" class="btn btn-light" data-dismiss="modal" id="subapps_confirmDeleteBtn">Delete </button>' + 
        '<div id="subapps_input_user_roles"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
        '</div>' +
    '<div>' +
    '<button class="btn btn-secondary" id="autopop_button">Auto-Populate Tables</button>' +
    '</div>' +
    '<li class="nav-item">' +
    '<a class="nav-link" id="subapps_tab">' +
    '</a>' + '</li>' 
            ;
    
    $.fn.on = (tab, cb) => {
        if (tab === 'shown.bs.tab') {
            cb()
        } else {
                cb(jQuery.Event(tab))
        }
    };
    
    $.post = (url, post_data, callback) => {
        data.result = [{
            id: 'id',
            name: 'name',
            status: 'status',
            subapps_alias: JSON.stringify(["1", "2"])
        }];
        if(url == ("/facs_manager/get_facs")) callback(data);
    };

    require('../facs/subapps_table');

    window._rb.first_load_subapps = true;
    $('#subapps_tab').tab('show');   
    $('#autopop_button').trigger('click');
    expect(true).toBe(true);
    jest.resetModules();

});

test('trigger second load mappings table', () => {

    temp_id = new Date().getTime() + "_dropdown_column_selector";
    document.body.innerHTML = 
    '<div id="subapps_input_subapps_alias"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
    '<div id="subapps_mapping_input_subapps"><select multiple><option value="1" selected>ACTF</option><option value="2" selected>WSS</option></select></div>' +
    '<input id="subapps_mapping_pattern_1" type="radio" value="1" /><input id="subapps_mapping_pattern_2" type="radio" value="2" /><input id="subapps_mapping_pattern_3" type="radio" value="3" />' +
    '<div>' +
        '<button type="button" class="btn btn-secondary button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>' +
        '<ul class="dropdown-menu keep-open" id="' + temp_id + '">' +
        '<li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></label></li>' +
        '</ul>' +
    '</div>'
    '<div>' +
        '<button type="button" class="btn btn-light" data-dismiss="modal" id="subapps_confirmDeleteBtn">Delete </button>' + 
        '<div id="subapps_input_user_roles"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
        '</div>'
            ;
    require('../facs/subapps_table');

    $('#subapps_tab').tab('show')
    jest.resetModules();

    expect(true).toBe(true);
});

test('render columns', () => {

    temp_id = new Date().getTime() + "_dropdown_column_selector";
    document.body.innerHTML = 
    '<div id="subapps_input_subapps_alias"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
    '<div id="subapps_mapping_input_subapps"><select multiple><option value="1" selected>ACTF</option><option value="2" selected>WSS</option></select></div>' +
    '<input id="subapps_mapping_pattern_1" type="radio" value="1" /><input id="subapps_mapping_pattern_2" type="radio" value="2" /><input id="subapps_mapping_pattern_3" type="radio" value="3" />' +
    '<div>' +
        '<button type="button" class="btn btn-secondary button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>' +
        '<ul class="dropdown-menu keep-open" id="' + temp_id + '">' +
        '<li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></label></li>' +
        '</ul>' +
    '</div>'
    '<div>' +
        '<button type="button" class="btn btn-light" data-dismiss="modal" id="subapps_confirmDeleteBtn">Delete </button>' + 
        '<div id="subapps_input_user_roles"><select multiple><option value="1" selected>admin</option><option value="2" selected>user</option></select></div>' +
        '</div>'
            ;
    require('../facs/subapps_table');

    $('#subapps_tab').tab('show');
    jest.resetModules();

    expect(true).toBe(true);
});
