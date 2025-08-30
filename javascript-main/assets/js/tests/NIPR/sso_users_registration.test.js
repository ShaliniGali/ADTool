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
global.action_button = jest.fn(() => true );
global.rhombuscookie = jest.fn(() => true );


// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');

test('given no accounts', () => {
    // The require method caches required files, so we resetting files is necessary since
    // our JS isn't actually modular and is intended to produce side-effects
    jest.resetModules();

    // Inject globals
    // TODO: ideally globals should be cleaned up after test completion; need to investigate Jest's setUp/tearDown API
    // globals.exampleGlobal = null;

    // Mock markup for client-side JS to manipulate
    let orig_html = `
    <div>
        <button id="send_accounts"></button>
        <input id="input_accounts" type="text" value="">
    </div>
`;
    document.body.innerHTML = orig_html;

    require('../../NIPR/sso_users_registration');

    $('#send_accounts').trigger('click');

    expect(document.body.innerHTML).toBe(orig_html);
});

test('given valid emails', () => {
    jest.resetModules();

    const emails = [
        'test_1@email.com',
        'test_2@email.com'
    ];

    //global.email_list = emails;
    global.rhombus_email_domain = 'TRUE';
    global.valid_domains = 'email.com';
    
    document.body.innerHTML = `
        <div>
            <button id="send_accounts"></button>
            <input id="input_accounts" type="text" value="${emails.join(',')}">
            <div id="confirm_generate_emails"></div>
        </div>
    `;

    require('../../NIPR/sso_users_registration');

    $('#send_accounts').trigger('click');

    let result = $('#confirm_generate_emails').html();
    expect(result).toBe('test_1@email.com<br>test_2@email.com<br>');
});

test('duplicate emails', () => {
    jest.resetModules();

    const emails = [
        'test_1@email.com',
        'test_1@email.com'
    ];

    //global.email_list = emails;
    global.rhombus_email_domain = 'TRUE';
    global.valid_domains = 'email.com';
    
    document.body.innerHTML = `
        <div>
            <button id="send_accounts"></button>
            <input id="input_accounts" type="text" value="${emails.join(',')}">
            <div id="confirm_generate_emails"></div>
        </div>
    `;

    require('../../NIPR/sso_users_registration');

    $('#send_accounts').trigger('click');

    let result = $('#confirm_generate_emails').html();
    expect(result).toBe('test_1@email.com<br>');
});

test('given invalid emails', () => {
    jest.resetModules();

    const emails = [
        'test_1email.com',
        'test_2email.com'
    ];

    global.rhombus_email_domain = 'TRUE';

    document.body.innerHTML = `
        <div>
            <button id="send_accounts"></button>
            <input id="input_accounts" type="text" value="${emails.join(',')}">
            <div id="confirm_generate_emails"></div>
            <div id="results_div" class="d-none"></div>
            <div id="info_tip" class="d-none"></div>
            <div id="confirm_generate"></div>
            <div id="confirm_error_emails_header" class="d-none"></div>
            <div id="confirm_error_emails" class="d-none"></div>
            
        </div>
    `;

    require('../../NIPR/sso_users_registration');

    $('#send_accounts').trigger('click');

    let html = $('#confirm_error_emails').html();

    expect(html).toBe('test_1email.com<br>test_2email.com<br>');
});

test('confirm registration fail', () => {
    jest.resetModules();

    global.email_list = 'email1@test.com';
    global.$.post = function(url, post_data, callback, json) {
        callback({result:'fail'}, true);

        return {always: (callback) => callback()};
    }

    document.body.innerHTML = `
        <div>
            <button id="confirm_generate_confirm"></button>
            <div id="input_accounts"></div>
        </div>
    `;

    require('../../NIPR/sso_users_registration');

    $('#confirm_generate_confirm').trigger('click');

    let html = $('#input_accounts').html();

    expect(html).toBe('');
});

test('confirm registration success', () => {
    jest.resetModules();

    global.email_list = 'email1@test.com';
    global.$.post = function(url, post_data, callback, json) {
        callback({
            "entities": {'usersAdded': ['email1@test.com']}, 
            "entitiesSSO": {'usersAdded': ['email1@test.com']}, 
            "entitiesKeycloak": {'usersAlreadyExist': ['email1@test.com']}
        }, true);

        return {always: (callback) => callback()};
    }

    document.body.innerHTML = `
        <div>
            <button id="confirm_generate_confirm"></button>
            <div id="results_div">
                <div id="info_tip"></div>
                <table id="results_table"></table>
            </div>
        </div>
    `;

    require('../../NIPR/sso_users_registration');

    $('#confirm_generate_confirm').trigger('click');

    let html = $('#results_table').html();

    expect(html).toBe('<tr><td>email1@test.com</td><td class=\"text-center\"><i class=\"fas fa-check text-success\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"User successfully added\"></i></td><td class=\"text-center\"><i class=\"fas fa-check text-success\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"User successfully added\"></i></td><td class=\"text-center\"><i class=\"fas fa-minus text-gray\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"User already exists\"></i></td></tr>');
});

test('check_account_input function false', () => {
    jest.resetModules();


    require('../../NIPR/sso_users_registration');

    document.body.innerHTML = `
    <div>
        <button id="send_accounts"></button>
        <input id="input_accounts" value="email1@test.com" />
    </div>
`;

    window._rb.check_account_input();

    let disabled = $('#send_accounts').prop('disabled');

    expect(disabled).toBe(false);
});

test('check_account_input function true', () => {
    jest.resetModules();


    require('../../NIPR/sso_users_registration');

    document.body.innerHTML = `
    <div>
        <button id="send_accounts"></button>
        <input id="input_accounts" value="" />
    </div>
`;

    window._rb.check_account_input();

    let disabled = $('#send_accounts').prop('disabled');

    expect(disabled).toBe(true);
});