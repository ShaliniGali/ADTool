/**
 * @jest-environment jsdom
 */

const $ = require('jquery');
global.$ = global.jQuery = $;

$.post = jest.fn();

global.action_button = jest.fn();
global.rhombuscookie = jest.fn(() => 'test_token');
global.username = 'test_user';
global.clear_form = jest.fn();

require('bootstrap/dist/js/bootstrap.bundle.min.js');

describe('Password Reset Form Tests', () => {
  let preventDefaultMock;
  let stopPropagationMock;

  beforeEach(() => {
    jest.resetModules();

    document.body.innerHTML = `
      <form id="rhombus_password_reset">
        <input id="user_password_reset" type="password" required />
        <input id="user_password_reset_confirm" type="password" required />
        <div id="user_password_reset_msg" class="d-none"></div>
        <div id="user_password_reset_confirm_msg" class="d-none"></div>
        <button id="rhombus_password_reset_submit" type="submit">Submit</button>
      </form>
      <div id="reset_password_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div id="reset_password_modal_title" class="modal-header"></div>
            <div id="reset_password_modal_body" class="modal-body"></div>
          </div>
        </div>
      </div>
      <div id="reset-card"></div>
      <div id="success-card"></div>
    `;

    preventDefaultMock = jest.fn();
    stopPropagationMock = jest.fn();

    jest.spyOn(HTMLFormElement.prototype, 'submit').mockImplementation(() => {});

    $.post.mockImplementation((url, data, callback) => {
      callback({ result: 'success' }, 'success');
    });

    jest.clearAllMocks();

    require('../login/reset_login_password');
  });

  afterEach(() => {
    jest.restoreAllMocks();
    document.body.innerHTML = '';
  });

  test('Form submission with valid password should succeed', () => {
    $('#user_password_reset').val('ValidPassword1!');
    $('#user_password_reset_confirm').val('ValidPassword1!');

    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    $('#rhombus_password_reset').trigger(event);

    expect($.post).toHaveBeenCalledWith(
      '/login/confirm_reset_password',
      {
        username: 'test_user',
        Password: 'ValidPassword1!',
        ConfirmPassword: 'ValidPassword1!',
        rhombus_token: 'test_token'
      },
      expect.any(Function),
      'json'
    );
  });

  test('Form submission with invalid password strength should fail', () => {
    $('#user_password_reset').val('weak');
    $('#user_password_reset_confirm').val('weak');

    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    $('#rhombus_password_reset').trigger(event);

    expect($('#user_password_reset_msg').hasClass('d-none')).toBeFalsy();
  });

  test('Form submission with password mismatch should fail', () => {
    $('#user_password_reset').val('ValidPassword1!');
    $('#user_password_reset_confirm').val('DifferentPassword2!');

    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    $('#rhombus_password_reset').trigger(event);

    expect($('#user_password_reset_confirm_msg').html()).toBe('Password does not match');
  });

  test('Form submission with invalid form validity should fail', () => {
    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    const formElement = document.getElementById('rhombus_password_reset');
    jest.spyOn(formElement, 'checkValidity').mockReturnValue(false);

    $('#rhombus_password_reset').trigger(event);

    expect($('#rhombus_password_reset').hasClass('was-validated')).toBe(true);
  });

  test('Form submission with previously used password should show failure modal', () => {
    $('#user_password_reset').val('UsedPassword1!');
    $('#user_password_reset_confirm').val('UsedPassword1!');

    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    $.post.mockImplementationOnce((url, data, callback) => {
      callback({ result: 'password_used' }, 'success');
    });

    $.fn.modal = jest.fn();

    $('#rhombus_password_reset').trigger(event);

    expect($('#reset_password_modal_title').html()).toContain('Failure!');
  });

  test('Form submission with server error should show error modal', () => {
    $('#user_password_reset').val('ValidPassword1!');
    $('#user_password_reset_confirm').val('ValidPassword1!');

    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    $.post.mockImplementationOnce((url, data, callback) => {
      callback({ result: 'error' }, 'success');
    });

    $.fn.modal = jest.fn();

    $('#rhombus_password_reset').trigger(event);

    expect($('#reset_password_modal_title').html()).toContain('Error');
  });

  test('Form submission with unknown server response should handle gracefully', () => {
    $('#user_password_reset').val('ValidPassword1!');
    $('#user_password_reset_confirm').val('ValidPassword1!');

    const event = $.Event('submit');
    event.preventDefault = jest.fn();

    $.post.mockImplementationOnce((url, data, callback) => {
      callback({ result: 'unexpected_value' }, 'success');
    });

    $.fn.modal = jest.fn();

    $('#rhombus_password_reset').trigger(event);

    expect(clear_form).toHaveBeenCalledWith('rhombus_password_reset');
  });
}); 