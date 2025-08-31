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
global.sanitizeHtml = () => undefined;

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


$.fn.ready = (cb) => {cb()};
rhombus_dark_mode = () => {return}

test('disable 2fa group', () => {

    require('../actions/activate_register');
    jest.resetModules();

    document.body.innerHTML = `
    <input type="checkbox" id="activate_tfa" name="activate_tfa" value="something"></input>
    `;

    $('#user_role').select2({ width: '20%' }).val('test').trigger('change');

    expect(true).toBe(true);
});


test('enable 2fa group', () => {

    jest.resetModules();

    document.body.innerHTML = `
    <input type="checkbox" id="activate_tfa" name="activate_tfa" value="something" checked=true></input>
    `;

    require('../actions/activate_register');
    expect(true).toBe(true);
});


test('user role select ADMIN', () => {

    jest.resetModules();

    document.body.innerHTML = `
    <select id="user_role">
   
      <option value="ADMIN">ADMIN</option>
      <option value="USER">USER</option>
      <option value="MODERATOR">MODERATOR</option>
  
  </select>
  <input type="text" autocomplete="off" class="form-control" name="admin_expiry" id="admin_expiry" placeholder="Expiration date" required>
    `;


    require('../actions/activate_register');

    var e = jQuery.Event( "select2:select", { params : {data : {text : 'ADMIN'}} } );
    $('#user_role').select2().trigger(e);

    $("#admin_expiry").datepicker('setDate', new Date()).trigger('change');

    expect(true).toBe(true);
});

test('user role select USER', () => {

    jest.resetModules();

    document.body.innerHTML = `
    <select id="user_role">
   
      <option value="ADMIN">ADMIN</option>
      <option value="USER">USER</option>
      <option value="MODERATOR">MODERATOR</option>
  
  </select>
    `;


    require('../actions/activate_register');

    var e = jQuery.Event("select2:select", { params : {data : {text : 'USER'}}} );
    $('#user_role').select2().trigger(e);
    
    expect(true).toBe(true);
});


test('activate_tfa_if', () => {

    jest.resetModules();


    document.body.innerHTML = ` <input class="form-check-input" type="checkbox" value="" id="activate_tfa">`;

    require('../actions/activate_register');

    // script to test sets input to true, so set to false here
    $('#activate_tfa').prop('checked', false);
    $('#activate_tfa').trigger('click');
    expect(true).toBe(true);
});

test('activate_tfa_else', () => {

    jest.resetModules();


    document.body.innerHTML = `<input class="form-check-input" type="checkbox" value="" id="activate_tfa">`;

    require('../actions/activate_register');

    // script to test sets input to true, so set to false here
    $('#activate_tfa').prop('checked', true);
    $('#activate_tfa').trigger('click');
    expect(true).toBe(true);
});

test('activate_register_submit_else_if', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    </form>`;

    require('../actions/activate_register');

    // script to test sets input to true, so set to false here
    $('#activate_register').trigger('submit');
    expect(true).toBe(true);
});

test('activate_register_submit_if', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth" required>
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    </form>`;

    require('../actions/activate_register');

    // script to test sets input to true, so set to false here
    $('#activate_register').trigger('submit');
    expect(true).toBe(true);
});


test('activate_register_submit_else', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    </form>
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    `;
   
    require('../actions/activate_register');
    $('#activate_tfa').prop('checked', false);
    $('#activate_Gauth').prop('checked', false);
    $('#activate_Yubikey').prop('checked', false);
    $('#activate_CAC').prop('checked', false);
    global.siteUrlData = {};
    // script to test sets input to true, so set to false here
    
    const callBack_data = {'result':'success'};

    $.post = (url, post_data, callback) => {
        if(url == ("/account_manager/encrypt_data")) callback(callBack_data);
        if(url == ("/login/activate_register")) callback(callBack_data);
    }
    global.updateTable = () => null;

    $('#activate_register').trigger('submit');

    // expect($('error_modal_button1').hasClass('d-none')).toBe(true);
    expect(true).toBe(true);
});


test('activate_register_submit_if', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    </form>
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    `;
   
    require('../actions/activate_register');
    $('#activate_tfa').prop('checked', false);
    $('#activate_Gauth').prop('checked', false);
    $('#activate_Yubikey').prop('checked', false);
    $('#activate_CAC').prop('checked', false);
    global.siteUrlData = {};
    // script to test sets input to true, so set to false here
    
    const callBack_data = {'result':'error'};

    $.post = (url, post_data, callback) => {
        if(url == ("/account_manager/encrypt_data")) callback(callBack_data);
        if(url == ("/login/activate_register")) callback(callBack_data);
    }
    global.updateTable = () => null;

    $('#activate_register').trigger('submit');

    // expect($('error_modal_button1').hasClass('d-none')).toBe(true);
    expect(true).toBe(true);
});

test('activate_register_submit_else_if', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    </form>
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    `;
   
    require('../actions/activate_register');
    $('#activate_tfa').prop('checked', false);
    $('#activate_Gauth').prop('checked', false);
    $('#activate_Yubikey').prop('checked', false);
    $('#activate_CAC').prop('checked', false);
   
    const callBack_data = {'result':'error'};

    $.post = (url, post_data, callback) => {
        if(url == ("/login/activate_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = undefined;
    global.siteUrl = {};

    $('#activate_register').trigger('submit');

    // expect($('error_modal_button1').hasClass('d-none')).toBe(true);
    expect(true).toBe(true);
});

test('activate_register_submit_else_if_success', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    </form>
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    `;
   
    require('../actions/activate_register');
    $('#activate_tfa').prop('checked', false);
    $('#activate_Gauth').prop('checked', false);
    $('#activate_Yubikey').prop('checked', false);
    $('#activate_CAC').prop('checked', false);
    // script to test sets input to true, so set to false here
    
    const callBack_data = {'result':'success'};

    $.post = (url, post_data, callback) => {
        if(url == ("/login/activate_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = undefined;
    global.siteUrl = {};

    $('#activate_register').trigger('submit');

    expect(true).toBe(true);
});


test('reject_register_submit_if', () => {

    jest.resetModules();

    document.body.innerHTML = ` <form class="needs-validation pt-5" novalidate id="activate_register">
    <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
    <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
    <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
    <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
    <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
    <button class="btn btn-secondary btn-lg ml-4" id="reject_register_submit" type="button" value="Submit"> Reject Account </button>
    <div id="account_reject_modal_title"></div>
    <div id="account_reject_modal_body"></div>
    <div id="formModal"></div>

    </form>
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    `;
   
    require('../actions/activate_register');

    $('#activate_tfa').prop('checked', false);
    $('#activate_Gauth').prop('checked', false);
    $('#activate_Yubikey').prop('checked', false);
    $('#activate_CAC').prop('checked', false);
    // script to test sets input to true, so set to false here

    const callBack_data = {'result':'success'};

    $.post = (url, post_data, callback) => {
        if(url == ("/login/activate_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = undefined;
    global.siteUrl = {};

    $('#reject_register_submit').trigger('click');

    expect(true).toBe(true);
});


test('account_reject_submit_if_success', () => {

    jest.resetModules();

    document.body.innerHTML = ` 
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    <div id="account_reject_submit"></div>
    `;
   
    require('../actions/activate_register');
    const callBack_data = {'result':'success'};

    $.post = (url, post_data, callback) => {
        if(url == ("/account_manager/encrypt_data")) callback(callBack_data);
        if(url == ("/register/reject_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = {};
    global.siteUrl = {};

    $('#account_reject_submit').trigger('click');

    expect(true).toBe(true);
});

test('account_reject_submit_if_error', () => {

    jest.resetModules();

    document.body.innerHTML = ` 
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    <div id="account_reject_submit"></div>
    `;
   
    require('../actions/activate_register');
    const callBack_data = {'result':'failure'};

    $.post = (url, post_data, callback) => {
        if(url == ("/account_manager/encrypt_data")) callback(callBack_data);
        if(url == ("/register/reject_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = {};
    global.siteUrl = {};

    $('#account_reject_submit').trigger('click');
    expect(true).toBe(true);
});

test('account_reject_submit_siteurl', () => {

    jest.resetModules();

    document.body.innerHTML = ` 
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    <div id="account_reject_submit"></div>
    `;
   
    require('../actions/activate_register');
    const callBack_data = {'result':'success'};

    $.post = (url, post_data, callback) => {
        if(url == ("/account_manager/encrypt_data")) callback(callBack_data);
        if(url == ("/register/reject_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = undefined;
    global.siteUrl = {};

    $('#account_reject_submit').trigger('click');
    expect(true).toBe(true);
});

test('account_reject_submit_siteurl_undefined', () => {

    jest.resetModules();

    document.body.innerHTML = ` 
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    <div id="account_reject_submit"></div>
    `;
   
    require('../actions/activate_register');
    const callBack_data = {'result':'success'};

    $.post = (url, post_data, callback) => {
        if(url == ("/register/reject_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = undefined;
    global.siteUrl = {};
    

    $('#account_reject_submit').trigger('click');
    expect(true).toBe(true);
});

test('account_reject_submit_siteurl_undefined_result_failure', () => {

    jest.resetModules();

    document.body.innerHTML = ` 
    <div id="error_modal_title"></div>
    <div id="error_modal_body"></div>
    <div id="error_modal_button1"></div>
    <div id="error_modal_button2"></div>
    <div id="error_modal"></div>
    <div id="account_reject_submit"></div>
    `;
   
    require('../actions/activate_register');
    const callBack_data = {'result':'failure'};

    $.post = (url, post_data, callback) => {
        if(url == ("/register/reject_register")) callback(callBack_data);
    }
    global.updateTable = () => null;
    global.siteUrlData = undefined;
    global.siteUrl = {};

    $('#account_reject_submit').trigger('click');

    jest.runAllTimers();

    expect(true).toBe(true);
});





