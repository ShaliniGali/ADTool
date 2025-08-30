/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

// Inject globals
const jQuery = require('jquery'); 
global.$ = jQuery;
global.sanitizeHtml = html => html;


// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');

test('request_rb_kc-test', () => {
    jest.resetModules();

    // Inject globals
    global.rhombuscookie = () => null;

    // Mock markup for client-side JS to manipulate
    document.body.innerHTML = `
        <button id="reqaccess" onclick="request_rb_kc()"> Request RB KC Button </button>
        <div class="h-100">For Callback</div>
    `;

    require('../actions/request_account_registration');
    $.post = (url, post_data, callback) => {
        if(url == ('/rb_kc/requestRegistration')) callback({'message':'some message'})
    }
    action_button = ()=>{return}
    window._rb.request_rb_kc();
    expect($('.h-100').html()).toBe('For Callback');
});

test('request_rb_p1-test', () => {
    jest.resetModules();

    // Inject globals
    global.rhombuscookie = () => null;

    // Mock markup for client-side JS to manipulate
    document.body.innerHTML = `
        <button id="reqaccess" onclick="request_rb_kc()"> Request RB P1 Button </button>
        <div class="h-100">For Callback</div>
    `;

    require('../actions/request_account_registration');
    $.post = (url, post_data, callback) => {
        if(url == ('/rb_p1/requestRegistration')) callback({'message':'some message'})
    }
    global.action_button = ()=>{return}
    window._rb.request_rb_p1();
    expect($('.h-100').html()).toBe('For Callback');
});

test('request-test', () => {
    jest.resetModules();

    const req = 'something';
    // Inject globals
    global.rhombuscookie = () => null;

    // Mock markup for client-side JS to manipulate
    document.body.innerHTML = `
        <button id="reqaccess" onclick="request('${req}')"> Request Button </button>
        <div class="px-3 h-100"> For Callback </div>
    `;

    require('../actions/request_account_registration');
    $.post = (url, post_data, callback) => {
        if(url == ('/sso/requestRegistration')) callback({'message':'some message'})
    }
    action_button = ()=>{return}
    window._rb.request('${req}');
    expect(true).toBe(true);
});
