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
global.sanitizeHtml = jest.fn((string) => string);

// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');
// The datatables plugin attaches itself differently in node.js (see datatables/media/js/jquery.dataTables.js)
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);

global.rhombuscookie = () => null;
global.action_button = jest.fn(() => true );
global.clear_form = jest.fn(() => true );

beforeAll(() => {
	jest.resetModules();
	require('../actions/forms');
});

describe('single_field_submit', () => {
	
	beforeEach(() => {
		document.body.innerHTML = `<form id='field_form'>
								<input id='field' type='text' value='0' required>
								<button id='field_cancel'></button>
								<button id='field_save_cancel'></button></form>
								<span id='field_error'></span>`;
		field = $('#field');
		field_form = $('#field_form');
		field_cancel = $('#field_cancel');
		field_save_cancel = $('#field_save_cancel');
		field_error = $('#field_error');
	});
	let field;
	let field_form;
	let field_cancel;
	let field_save_cancel;
	let field_error;

	test('field_not_valid', () => {
		field.attr('value', '');
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field_form.submit();
		expect(field_form.hasClass('was-validated')).toBe(true);
	});

	test('field_is_valid', () => {
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field_form.submit();
		expect(field_form.hasClass('was-validated')).toBe(false);
	});


	test('not_org_val', () => {
		field_save_cancel.attr('class', 'd-none');
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field.trigger('input');
		expect(field_save_cancel.hasClass('d-none')).toBe(false);
	});

	test('is_org_val', () => {
		field.attr('org-val', '0');
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field.trigger('input');
		expect(field_save_cancel.hasClass('d-none')).toBe(true);
	});

	test('field_post_successful', () => {
		$.post = (url, post_data, callback) => callback({result:'success'});
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field_form.submit();
		expect((field_save_cancel).hasClass('d-none')).toBe(true);
	});

	test('field_post_unsuccessful', () => {
		$.post = (url, post_data, callback) => callback({result:'', error: 'error message'});
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field_form.submit();
		expect(field_error.html()).toBe("error message");
	});

	test('field_click_cancel', () => {
		window._rb.single_field_submit('field', 'controller_placeholder', 'key', 'field_error');
		field_cancel.click();
		expect((field_save_cancel).hasClass('d-none')).toBe(true);
	});

});

describe('password_change_submit', () => {
	
	beforeEach(() => {
		document.body.innerHTML = `<form id='password_form'>
								<input id='password' type='text' value='0' required>
								<button id='password_cancel'></button>
								<span id='password_message'></span>`;
		password_field = $('#password');
		password_form = $('#password_form');
		password_cancel = $('#password_cancel');
		password_message = $('#password_message');
	});
	let password_field;
	let password_form;
	let password_cancel;
	let password_message;

	test('password_not_valid', () => {
		password_field.attr('value', '');
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_form.submit();
		expect(password_form.hasClass('was-validated')).toBe(true);
	});

	test('password_is_valid', () => {
		password_form.attr('class', 'was-validated');
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_form.submit();
		expect(password_form.hasClass('was-validated')).toBe(false);
	});

	test('password_post_successful', () => {
		$.post = (url, post_data, callback) => callback({result:'success'});
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_form.submit();
		expect(password_message.html()).toBe('<span class="text-success">Your password has been changed.</span>');
	});

	test('password_post_match_fail', () => {
		$.post = (url, post_data, callback) => callback({result:'failure_new_password_match'});
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_form.submit();
		expect(password_message.html()).toBe('<span class="text-danger">Your new password is not matching.</span>');
	});

	test('password_post_fail', () => {
		$.post = (url, post_data, callback) => callback({result:'fail', error: 'error'});
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_form.submit();
		expect(password_message.html()).toBe('<span class="text-danger">error</span>');
	});

	test('password_post_wrong', () => {
		$.post = (url, post_data, callback) => callback({result:''});
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_form.submit();
		expect(password_message.html()).toBe('<span class="text-danger">Your current password is not right.</span>');
	});

	test('password_click_cancel', () => {
		password_form.addClass('was-validated');
		window._rb.password_change_submit('password', 'password_1', 'password_2', 'password_3', 'controller_placeholder');
		password_cancel.click();
		expect(password_form.hasClass('was-validated')).toBe(false);
	});

});

describe('notificationSubmit', () => {

	beforeEach(() => {
	global.rhombuscookie = () => null;
	document.body.innerHTML = `<input id='input' type='checkbox' value='test' checked>
									<div id='notification_result'></div>`;
	});
	const ids = ['input']

	test('notification_unsuccessful', () => {
		$.post = (url, post_data, callback) => callback({result:''});
		window._rb.notificationSubmit(ids);
		$('#input').change();
		expect($('#notification_result').html()).toStrictEqual("");
	});

	test('notification_successful', () => {
		$.post = (url, post_data, callback) => callback({result:'success'});
		window._rb.notificationSubmit(ids);
		$('#input').change();
		expect($('#notification_result').text()).toBe('Your notifications have been saved');
	});
});

describe('formatState', () => {
	document.body.innerHTML = `<div id='state' value='0'>state text</div>`;
	const element = $('#state')[0];
	const state = {
		'element': element,
		'id': element.id,
		'text': element.textContent
	}
	
	test('state_locked', () => {
		const formatState = window._rb.formatState(state);
		expect(formatState).toStrictEqual($(`<span><i class="fas fa-lock fa-fw mr-3"></i>state text</span>`));
	});
	
	test('state_unlocked', () => {
		element.value = 1;
		const formatState = window._rb.formatState(state);
		expect(formatState).toStrictEqual($(`<span><i class="fas fa-lock-open fa-fw mr-3"></i>state text</span>`));
	});

	test('state_no_id', () => {
		state.id = '';
		const formatState = window._rb.formatState(state);
		expect(formatState).toBe(`state text`);
	});
});
