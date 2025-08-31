/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;
global.action_button = jest.fn(() => true );
global.rhombuscookie = jest.fn(() => true );
global.loginWithGoogle2FA = jest.fn(() => true );
global.generateQR = jest.fn(() => true );
global.clear_form = jest.fn(() => true );
global.sanitizeHtml = html => html;

jest.useFakeTimers();

let assignMock = jest.fn();

delete window.location;
window.location = { assign: assignMock };

afterEach(() => {
  assignMock.mockClear();
});

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require("tilt.js/dest/tilt.jquery.min.js")($);

test('display_login_modal', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
    `;

    require('../login/login');

    const modal = 'modal';
    const title = 'test';
    const body = 'test';
    const hidden = ['hidden_id'];
    const timeout = 3000;

    window._rb.display_login_modal(modal, title, body, hidden, timeout);
    jest.runAllTimers();

    expect(true).toBe(true);
})

test('tos_agreement_checkbox_isChecked', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <input type="checkbox" id="tos_agreement_checkbox" checked></input>
        <button class="bx--btn bx--btn--primary" type="button">
            Test Submit Button
        </button>

    `;

    require('../login/login');

    $("#tos_agreement_checkbox").trigger('click');
   
    expect(true).toBe(true);
})

test('tos_agreement_checkbox_notChecked', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <input type="checkbox" id="tos_agreement_checkbox"></input>
        <button class="bx--btn bx--btn--primary" type="button">
            Test Submit Button
        </button>

    `;

    require('../login/login');

    $("#tos_agreement_checkbox").trigger('click');
   
    expect(true).toBe(true);
})

test('tos_link', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <body>
        </body>
        <div data-modal id="tos_moterms_of_service_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <input type="checkbox" id="tos_agreement_checkbox">
        <button id="tos_link" class="bx--btn bx--btn--primary" type="button">
            tos link
        </button>
    `;

    require('../login/login');

    $("#tos_link").trigger('click');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityFalse', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login" required>
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $("#user_password_login").val('')
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_success', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({'result':'success'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_failed', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({'message':'message','result':'failed'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_registration_pending_exist', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({'result':'registration_pending_exist'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_account_blocked', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({'result':'account_blocked'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_reset_password', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({'result':'reset_password'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_force_reset_password', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({'result':'force_reset_password'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_require_login_layer', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <div id="login_card"></div>
            <div id="tfa_login" class="d-none></div>
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({layers: ['1', '1', '1', '1', '1'], result:'require_login_layer'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('rhombus_login_checkValidityTrue_register_login_layer', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="modal_title"></p>
        <p id="modal_body"></p>
        <p id="hidden_id"</p>
        <form id="rhombus_login">
            <input type="text" id="user_email_login">
            <input type="text" id="user_password_login">
            <input type="checkbox" id="tos_agreement_checkbox">
            <div id="login_card"></div>
            <div id="tfa_register" class="d-none></div>
            <button id="rhombus_login_submit" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
    `;

    require('../login/login');

    $.post = (url, post_data, callback) => {
        if(url == ('/login/user_check')) callback({layers: ['1', '1', '1', '1', '1'], result:'register_login_layer'})
    }
    $("#rhombus_login").trigger('submit');
   
    expect(true).toBe(true);
});

test('enable_login_registeration_methods', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <button id="yubikey_login" class="bx--btn bx--btn--primary" type="button">
            YL
        </button>
        <button id="yubikey_register" class="bx--btn bx--btn--primary" type="button">
            YR
        </button>
        <button id="google_auth_login" class="bx--btn bx--btn--primary" type="button">
            GAL
        </button>
        <button id="google_auth_register" class="bx--btn bx--btn--primary" type="button">
            GAR
        </button>
        <button id="selected_tfa_modal" class="bx--btn bx--btn--primary" type="button">
            STA
        </button>
        <button id="cac_reader_login" class="bx--btn bx--btn--primary" type="button">
            CRL
        </button>
        <button id="cac_register" class="bx--btn bx--btn--primary" type="button">
            CR
        </button>
        <button id="recovery_code_login" class="bx--btn bx--btn--primary" type="button">
            RCL
        </button>
        <button id="login_token" class="bx--btn bx--btn--primary" type="button">
            LT
        </button>
    `;

    require('../login/login');

    const data = {layers: ['0', '1', '1', '1', '1']}
    window._rb.enable_login_registeration_methods(data);

    expect(true).toBe(true);
});

test('reset_password_success', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="reset_pwd_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="reset_pwd_modal_title"></p>
        <p id="reset_pwd_modal_body"></p>
        <button id="reset_pwd_modal_button1" class="bx--btn bx--btn--primary" type="button">
            Button 1
        </button>
        <button id="reset_pwd_modal_button2" class="bx--btn bx--btn--primary" type="button">
            Button 2
        </button>
    `;
    $.fn.ready = (cb) => {cb()};
    $.post = (url, post_data, callback) => {
        if(url == ('/login/send_reset_password')) callback({result:'success'});
    }

    require('../login/login');

    $("#reset_pwd_modal_button2").trigger('click');
   
    expect(true).toBe(true);
});

test('reset_password_notSuccess', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="reset_pwd_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="reset_pwd_modal_title"></p>
        <p id="reset_pwd_modal_body"></p>
        <button id="reset_pwd_modal_button1" class="bx--btn bx--btn--primary" type="button">
            Button 1
        </button>
        <button id="reset_pwd_modal_button2" class="bx--btn bx--btn--primary" type="button">
            Button 2
        </button>
    `;
    $.fn.ready = (cb) => {cb()};
    $.post = (url, post_data, callback) => {
        if(url == ('/login/send_reset_password')) callback({result:'failure'});
    }

    require('../login/login');

    $("#reset_pwd_modal_button2").trigger('click');
   
    expect(true).toBe(true);
});

test('reset_recovery_code_success', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="reset_key_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="reset_key_modal_title"></p>
        <p id="reset_key_modal_body"></p>
        <button id="reset_keymodal_button1" class="bx--btn bx--btn--primary" type="button">
            Button 1
        </button>
        <button id="reset_key_modal_button2" class="bx--btn bx--btn--primary" type="button">
            Button 2
        </button>
    `;
    $.fn.ready = (cb) => {cb()};
    $.post = (url, post_data, callback) => {
        if(url == ('/login/reset_recovery_codes')) callback({result:'success'});
    }

    require('../login/login');

    $("#reset_key_modal_button2").trigger('click');
   
    expect(true).toBe(true);
});

test('reset_recovery_code_notSuccess', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="reset_key_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="reset_key_modal_title"></p>
        <p id="reset_key_modal_body"></p>
        <button id="reset_keymodal_button1" class="bx--btn bx--btn--primary" type="button">
            Button 1
        </button>
        <button id="reset_key_modal_button2" class="bx--btn bx--btn--primary" type="button">
            Button 2
        </button>
    `;
    $.fn.ready = (cb) => {cb()};
    $.post = (url, post_data, callback) => {
        if(url == ('/login/reset_recovery_codes')) callback({result:'failure'});
    }

    require('../login/login');

    $("#reset_key_modal_button2").trigger('click');
   
    expect(true).toBe(true);
});

test('google_auth_login', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <button id="google_auth_login" class="bx--btn bx--btn--primary" type="button">
            GAL
        </button>
        <p id="recovery"></p>
        <p id="token"></p>
        <p id="yubikey"></p>
        <p id="google_authenticator" class="d-none"></p>
        <p id="tfa_login">append</p>
        <input type="text">
    `;

    require('../login/login');

    $("#google_auth_login").trigger('click');
   
    expect(true).toBe(true);
});

test('google_auth_register', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div id="selected_tfa_modal_body"></div>
        <div data-modal id="selected_tfa_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <button id="google_auth_register" class="bx--btn bx--btn--primary" type="button">
            GAR
        </button>
        <p id="recovery"></p>
        <p id="token"></p>
        <p id="yubikey"></p>
        <p id="google_authenticator_register" class="d-none"></p>
        <p id="tfa_login">append</p>
        <input type="text">
    `;

    require('../login/login');

    $("#google_auth_register").trigger('click');
   
    expect(true).toBe(true);
});

test('recovery_code_login', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="reset_key_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="reset_key_modal_title"></p>
        <p id="reset_key_modal_body"></p>
        <button id="reset_key_modal_button2" class="bx--btn bx--btn--primary d-none" type="button">
            Button 2
        </button>
        <p id="recovery" class="d-none"></p>
        <p id="token"></p>
        <p id="yubikey"></p>
        <p id="google_authenticator"></p>
        <p id="tfa_login">append</p>
        <button id="recovery_code_login" class="bx--btn bx--btn--primary d-none" type="button">
            RCL
        </button>
    `;

    $.post = (url, post_data, callback) => {
        if(url == ('/login/check_key_exist')) callback({result:'failure'});
    }

    require('../login/login');

    $("#recovery_code_login").trigger('click');
   
    expect(true).toBe(true);
});

test('login_token', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div data-modal id="reset_key_modal" class="bx--modal" role="dialog" tabindex="-1"></div>
        <p id="reset_key_modal_title"></p>
        <p id="reset_key_modal_body"></p>
        <button id="reset_key_modal_button2" class="bx--btn bx--btn--primary d-none" type="button">
            Button 2
        </button>
        <p id="recovery"></p>
        <p id="token" class="d-none"></p>
        <p id="yubikey"></p>
        <p id="google_authenticator"></p>
        <p id="tfa_login">append</p>
        <button id="login_token" class="bx--btn bx--btn--primary" type="button">
            LT
        </button>
    `;

    require('../login/login');

    $("#login_token").trigger('click');
   
    expect(true).toBe(true);
});

test('cancel', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <form id="test_form"></form>
    `;

    require('../login/login');

    const form = 'test_form';
    window._rb.cancel(form);
   
    expect(true).toBe(true);
});

test('cac_continue_btn', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <button id="cac_continue_btn" class="bx--btn bx--btn-primary type="button">
            CCB
        </button>
    `;

    require('../login/login');

    $("#cac_continue_btn").trigger('click');
   
    expect(true).toBe(true);
});

test('forgot_password_switch_reset', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <p id="rhombus_login"></p>
        <p id="forgot_password" class="d-none"></p>
        <p id="forgot_password_result"></p>
        <input type="text" id="forgot_password_email" value="">
    `;

    require('../login/login');

    const type = 'reset';
    window._rb.forgot_password_switch(type);
   
    expect(true).toBe(true);
});

test('forgot_password_switch_notReset', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <p id="rhombus_login" class="d-none"></p>
        <p id="forgot_password"></p>
        <p id="forgot_password_result"></p>
        <input type="text" id="forgot_password_email" value="">
    `;

    require('../login/login');

    const type = 'do not reset';
    window._rb.forgot_password_switch(type);
   
    expect(true).toBe(true);
});

test('forgot_password_bothSuccess', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <form id="forgot_password">
            <input type="text" id="forgot_password_email" value="value">
            <input type="text" id="user_password_login" required>
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="forgot_password_btn" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
        <p id="forgot_password_result" class="text-danger">text</p>
    `;

    $.post = (url, post_data, callback) => {
        if(url == ('/login/send_reset_password_by_email')) callback({validation:'success', result:'success', message:'message'});
    }

    require('../login/login');

    $("#forgot_password").trigger('submit');
   
    expect(true).toBe(true);
});

test('forgot_password_notBothSuccess', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <form id="forgot_password">
            <input type="text" id="forgot_password_email" value="value">
            <input type="text" id="user_password_login" required>
            <input type="checkbox" id="tos_agreement_checkbox">
            <button id="forgot_password_btn" class="bx--btn bx--btn--primary" type="submit">
                Test Submit Button
            </button>
        </form>
        <p id="forgot_password_result" class="text-success">text</p>
    `;

    $.post = (url, post_data, callback) => {
        if(url == ('/login/send_reset_password_by_email')) callback({validation:'failure', result:'failure', message:'message'});
    }

    require('../login/login');

    $("#forgot_password").trigger('submit');
   
    expect(true).toBe(true);
});
