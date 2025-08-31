/**
 * @jest-environment jsdom
 */

const jqueryDataTablesMin = require('datatables/media/js/jquery.dataTables.min.js');
const jQuery = require('jquery');
global.$ = jQuery;
require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);
require('select2')($);
require('jquery-datepicker')($);
global.HAS_SUBAPPS = '0'
global.sanitizeHtml = html => html;
global.account_type_select = `
    <select>
    <option>admin</option>
    <option>user</option>
    </select>
`;
global.facs = true;
global.app_name = 'Base Code';

global.RhombusDatatable = class RhombusDatatable {
    constructor () {
    }
    addDropdown(){return}
    createColumnSelector(){return}
    refreshTable(){return}
    repositionExportButton(){return}
    initTooltips(){return}
    initCopyFunctionality(){return}
}

test('renderUserApps', () => {
    document.body.innerHTML =
    '<div>' +
    '</div>';
  
    let apps = {
        'test1@rhombuspower.com': 'Knowledge Graph',
        'test2@rhombuspower.com': 'Competition',
    }, css = 'badge-success';

    require('../actions/account_manager');
	let result_html = window._rb.renderUserApps(apps, css);
    
    expect(result_html).toBe("<div class='badge-text d-flex flex-row justify-content-center mb-1'><span class='badge-success badge badge-pill mr-2'>Knowledge Graph</span></div><div class='badge-text d-flex flex-row justify-content-center mb-1'><span class='badge-success badge badge-pill mr-2'>Competition</span></div>");
});

test('setGlobalDeleteRow', () => {

    document.body.innerHTML =
    '<div>' +
    '</div>';

    let curr_id = null
    let curr_email = null

    require('../actions/account_manager');
	window._rb.setGlobalDeleteRow(curr_id,curr_email);
   
    expect(true).toBe(true);
});

test('constructSSODatatable', () => {

    document.body.innerHTML =
    '<div>' +
    '</div>';

    let account_type_select = null
    let type = "sampleType"
    let full = "sampleFull"
    columns = [
        {data: {}}
    ]


    require('../actions/account_manager');

    $.fn.DataTable = jest.fn((obj) => {
		let output = {
			'data': 'sampleDataForOutput'
		}

        //obj.columnDefs[0].render({}, null, {active_apps: {}, requested_apps: {}})
        //obj.columnDefs[1].render({}, null, {active_apps: {}, requested_apps: {}})
        obj.columnDefs[2].render({}, null, {active_apps: {}, requested_apps: {}})
        obj.columnDefs[3].render({}, null, {active_apps: {}, requested_apps: {}})
        obj.fnDrawCallback(null, null)
	})

	window._rb.constructSSODatatable({});
   
    expect(true).toBe(true);
});

test('constructAccountsDatatable', () => {

    document.body.innerHTML =
    '<div>' +
    '</div>';

    let account_type_select = null
    let type = "sampleType"
    let full = "sampleFull"
    columns = [
        {data: {}}
    ]

    require('../actions/account_manager');

    $.fn.DataTable = jest.fn((obj) => {
		let output = {
			'data': 'sampleDataForOutput'
		}

        obj.columnDefs[0].render({}, null, null)
        obj.columnDefs[1].render({}, null, null)
        obj.columnDefs[2].render({}, null, {status: 'Active'})
        obj.columnDefs[3].render({}, null, null)
        obj.fnDrawCallback(null, null)
	})

	window._rb.constructAccountsDatatable({});
   
    expect(true).toBe(true);
});

beforeEach(() => {

$.fn.DataTable = () => {
    return {
        row: () => {
            return {
                data: jest.fn(() => [{"id":"1","email":"test@rhombuspower.com","status":"Active","account_type":"ADMIN","admin_expiry":"09/03/2021"}]),
            }
        },
        rows: {
            add: jest.fn((data) => {
                $("#account_update_modal_title").append(data)
            }),
        },
        clear:  jest.fn(),
        columns: {
            adjust: () => {
                return {
                    draw: jest.fn()
                }
            }
        },
        column: () => ({
            visible: () => ({
                draw: () => jest.fn()
            })
        })
    }
}
});

/**
 * IF SSO IS TRUE TESTS
 */
test('sso accounts and register click success', () => {
    jest.resetModules();

    sso = [
        {"id":"1","email":"test@rhombuspower.com","status":"Active","account_type":"ADMIN","admin_expiry":"09/03/2021"},
        {"id":"2","email":null,"status":"RegistrationPending"}
    ];
    columns = [{"data":"id","visible":true},{"data":"email","visible":true},{"data":"status","visible":true},{"data":"account_type","visible":true},{"data":"admin_expiry","visible":true}, {"data":null, "visible":false}];
    $.fn.ready = (cb) => {cb()};
    rhombus_dark_mode = ()=>{return}
    rhombuscookie = ()=>{return}
    $.post = (url, post_data, callback) => {
        if(url == ('/account_manager/registerSSOUser')) callback({'status':'success'})
        if(url == ("/account_manager/updateUser")) callback({'result':'success'})
        if(url == ("/account_manager/getAccountData")) callback(sso)
    }


    document.body.innerHTML =
    '<div id="targetDiv">' +
    '  <button id="registerSSOBtn" onclick=""> </button>' +
    '  <table id="accountManagerTable"> <tr><button class="register"/></tr></table>' +
    '  <div id="account_update_modal_title" />' +
    '</div>';
    require('../global/datatables_features');
    require('../actions/account_manager');
    
    $('.register').trigger('click');
    $('#registerSSOBtn').trigger('click');
    expect($("#account_update_modal_title").text()).toBe('Success!');

    $('.admin_expiry').val('10/10/2021').trigger("change")
    $('.save').trigger('click');
    $('#delete').trigger('click');
    expect($("#account_update_modal_title").text()).toBe('Success!');
});

test('sso accounts and register click fail', () => {
    jest.resetModules();
    accountData = [
        {"id":"1","email":"test@rhombuspower.com","account_type":"ADMIN","admin_expiry":"09/03/2021","status":"Active"},
        {"id":"2","email":"test2@rhombuspower.com","account_type":"USER","admin_expiry":"09/03/2021","status":"RegistrationPending"}
    ]
    sso = [
        {"id":"1","email":"test@rhombuspower.com","status":"Active","account_type":"ADMIN","admin_expiry":"09/03/2021"},
        {"id":"2","email":null,"status":"RegistrationPending"}
    ];
    columns = [{"data":"id","visible":true},{"data":"email","visible":true},{"data":"status","visible":true},{"data":"account_type","visible":true},{"data":"admin_expiry","visible":true}];
    $.fn.ready = (cb) => {cb()};
    rhombus_dark_mode = ()=>{return}
    rhombuscookie = ()=>{return}
    $.post = (url, post_data, callback) => {
        if(url == ('/account_manager/getAccountData')) callback(accountData)
        if(url == ('/account_manager/registerSSOUser')) callback({'status':'fail'})
        if(url == ("/account_manager/updateUser")) callback({'result':'fail'})
    }


    document.body.innerHTML =
    '<div id="targetDiv">' +
    '  <button id="registerSSOBtn"> </button>' +
    '  <table id="accountManagerTable"> <tr><button class="register"/></tr></table>' +
    '  <tr><button class="save"/></tr></table>' +
    '  <div id="account_update_modal_title" />' +
    '</div>';
    // document.body.innerHTML = 
    // '<div>' +
    // '  <table id="accountManagerTable" />' +
    // '  <button id="registerSSOBtn" />' +
    // '  <div id="account_update_modal_title" />' +
    // '</div>';
    require('../global/datatables_features');
    require('../actions/account_manager');
    
    $('.register').trigger('click');
    $('#registerSSOBtn').trigger('click');
    expect($("#account_update_modal_title").text()).toBe('Failure!');
});

/**
 * IF SSO IS FALSE TESTS
 */

 test('NON SSO accounts and select2 USER', () => {
    jest.resetModules();

    sso = false;
    accountData = [
        {"id":"1","email":"test@rhombuspower.com","account_type":"ADMIN","admin_expiry":"09/03/2021","status":"Active"},
        {"id":"2","email":"test2@rhombuspower.com","account_type":"USER","admin_expiry":"09/03/2021","status":"RegistrationPending"}
    ]
    columns = [{"data":"id","visible":true},{"data":"email","visible":true},{"data":"status","visible":true},{"data":"account_type","visible":true},{"data":"admin_expiry","visible":true}, {"data":null, "visible":false}];
    $.fn.ready = (cb) => {cb()};
    rhombus_dark_mode = ()=>{return}
    rhombuscookie = ()=>{return}
    $.post = (url, post_data, callback) => {
        if(url == ('/account_manager/getAccountData')) callback(accountData)
    }

    document.body.innerHTML =
    '<div id="targetDiv">' +
    '  <button id="registerSSOBtn" onclick=""> </button>' +
    '  <table id="accountManagerTable"> <tr><button class="register"/></tr></table>' +
    '  <div id="account_update_modal_title" />' +
    `<input type='text' autocomplete='off'  name='admin_expiry' class='form-control admin_expiry'  placeholder="Expiration date" disabled>` + 
    ' <select class="account_type"></select>' +
    '</div>';

    require('../global/datatables_features');
    require('../actions/account_manager');
    
    $('.register').trigger('click');
    $('.account_type').trigger('select2:select').prop("disabled", true);

    expect($('.admin_expiry').prop('disabled')).toBe(true);
});

test('NON SSO accounts and select2 ADMIN', () => {
    jest.resetModules();

    global.facs = false;
    accountData = [
        {"id":"1","email":"test@rhombuspower.com","account_type":"ADMIN","admin_expiry":"09/03/2021","status":"Active"},
    ]
    columns = [{"data":"id","visible":true},{"data":"email","visible":true},{"data":"status","visible":true},{"data":"account_type","visible":true},{"data":"admin_expiry","visible":true}, {"data":null, "visible":false}];
    $.fn.ready = (cb) => {cb()};
    rhombus_dark_mode = ()=>{return}

    document.body.innerHTML =
    '<div id="targetDiv">' +
    '  <button id="registerSSOBtn" onclick=""> </button>' +
    '  <table id="accountManagerTable"> <tr><button class="register"/></tr></table>' +
    '  <div id="account_update_modal_title" />' +
    `<input type='text' autocomplete='off'  name='admin_expiry' class='form-control admin_expiry'  placeholder="Expiration date">` + 
    ' <select class="account_type"></select>' +
    '</div>';

    require('../global/datatables_features');
    require('../actions/account_manager');

    $('.account_type').trigger('select2:select')
    expect($('.admin_expiry').prop('disabled')).toBe(false);
});

test('delete trigger', () => {
    jest.resetModules();

    sso = [
        {"id":"1","email":"test@rhombuspower.com","account_type":"ADMIN","admin_expiry":"09/03/2021","status":"Active"}
    ];

    columns = [{"data":"id","visible":true},{"data":"email","visible":true},{"data":"status","visible":true},{"data":"account_type","visible":true},{"data":"admin_expiry","visible":true}, {"data":null, "visible":false}];
    $.fn.ready = (cb) => {cb()};
    rhombus_dark_mode = ()=>{return}
    $.post = (url, post_data, callback) => {
        if(url == ("/account_manager/deleteAccount")) callback({'result':'success'})
    }

    document.body.innerHTML =
    '<div>' +
    '  <table id="accountManagerTable" />' +
    '  <button id="account_delete_submit" />' +
    '  <div id="confirm_delete_modal_title" />' +
    '</div>';

    require('../global/datatables_features');
    require('../actions/account_manager');

    $('#delete').trigger('click');
    $('#account_delete_submit').trigger('click');
    
    expect(true).toBe(true);
});
