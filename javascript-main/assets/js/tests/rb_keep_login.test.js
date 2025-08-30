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
global.timeout_max_time = 10;
jest.useFakeTimers(); // or you can set "timers": "fake" globally in configuration file
// jest.spyOn(global, 'setTimeout');

require('select2')($);
require('jquery-datepicker')($);

$.fn.ready = (cb) => { cb() };
rhombus_dark_mode = () => { return }
$.post = (url,params) => {
    if (url == ('/login/nothing')) return;
}

let assignMock = jest.fn();
let reloadMock = jest.fn();

delete window.location;
window.location = { assign: assignMock, reload: reloadMock };

afterEach(() => {
  assignMock.mockClear();
  reloadMock.mockClear();
});

// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');


describe('testing time', () => {

    beforeEach(() => {
        jest.resetModules();

        document.body.innerHTML = `<div class="modal-body bg-light text-center">
                The maximum session time is <?= RHOMBUS_SSO_TIMEOUT?> minutes.<br>
                <text id="timeout_time_countdown">Timeout in:sec</text><br>
                Would you like to continue?
            </div>
            <div class="modal-footer bg-light border-0">
                <button id="user_timeout_no_button"  onclick="window.location='/login/logout'" type="button" class="btn btn-secondary" data-dismiss="modal">No, log out</button>
                <button id="user_timeout_continue_button" type="button" class="btn btn-primary" data-dismiss="modal">Yes, keep me logged in</button>
            </div>`;
        require('../essential/rb_keep_login');
    });

    test('run all else', () => {
        jest.runAllTimers();
        expect(true).toBe(true);
    });

    test('run click', () => {
        jest.runAllTimers();
        $('#user_timeout_continue_button').trigger('click');
        expect(true).toBe(true);
    });

    test('user used the page true', () => {
        $(document).trigger('mousemove');
        $(document).trigger('keydown');
        $(document).trigger('click');
        $(document).trigger('scroll');
        jest.runAllTimers();
        expect(true).toBe(true);
    });
});

test('user timeout continue button click', () => {
    // The require method caches required files, so we resetting files is necessary since
    // our JS isn't actually modular and is intended to produce side-effects
    jest.resetModules();

    // Mock markup for client-side JS to manipulate
    document.body.innerHTML = `
    <div>
    <button id="user_timeout_continue_button" />
    </div>
    `;
    require('../essential/rb_keep_login');

    $('#user_timeout_continue_button').trigger('click');

    // TODO: We'll want to inspect side-effects after code to test is ran
    //       in this case, we should check that the DOM was manipulated as expected (<div id="confirm_generate_emails"></div> should have more elements in it)
    // expect(global.timeoutInterval).toBeNull();
    expect(true).toBe(true);
});


test('document events', () => {


    // The require method caches required files, so we resetting files is necessary since
    // our JS isn't actually modular and is intended to produce side-effects
    jest.resetModules();

    require('../essential/rb_keep_login');

    $(document).trigger('mousemove');
    $(document).trigger('keydown');
    $(document).trigger('click');
    $(document).trigger('scroll');

    // TODO: We'll want to inspect side-effects after code to test is ran
    //       in this case, we should check that the DOM was manipulated as expected (<div id="confirm_generate_emails"></div> should have more elements in it)
    expect(true).toBe(true);
});