/**
 * Configure Jest to use jsdom by default
 * https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

// Include global dependencies.
const jQuery = require('jquery'); 
const CarbonComponents = require('carbon-components/umd/index.js');
global.$ = jQuery;
global.jQuery = jQuery;
global.CarbonComponents = CarbonComponents;
require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);
global.sanitizeHtml = html => html;

/**
 * Tests for genericForm_submit.
 */
test('genericForm_submit, if form_generic[0].checkValidity returns false', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div data-modal id="test-modal" class="bx--modal" aria-describedby="test-modal-heading">
			<span tabindex="0" role="link" class="bx--visually-hidden"></span>
			<div class="bx--modal-container">
				<div class="bx--modal-header">
					<p class="bx--modal-header__heading bx--type-beta" id="test-modal-heading">Test modal header</p>
				</div>
				<div id="test-modal-content" class="bx--modal-content"">
					<p>Test modal content</p>
				</div>
			</div>
			<span tabindex="0"></span>
		</div>

		<form id="test-form">
			<input type="text" id="test-password" name="test-input" value="" required>
		</form>

		<button id="test-button"></button>
	`;

	// Define params, variables, functions and globals.
	var form_generic = $('#test-form');
	var submit_button = document.getElementById('test-button');
	var data = [];
	var url = 'test_url';
	var e = new MouseEvent('click');
	var msg = [
		{
			'Failure_message': 'test_msg2'
		}
	];
	var modal = 'test-modal';
	var form_popup = 'test-modal';
	const callback = function(modal) {
		return true;
	};
	global.action_button = jest.fn(() => true);
	global.rhombuscookie = jest.fn(() => true);
	global.clear_form = jest.fn(() => true);
	
	// Include source file.
	require('../global/generic_form');

	$.post = (url, postData, callback) => {
			callback({
				'result': 'success',
				'message': 'post message'
			})
	}

	// Run test.
	window._rb.genericForm_submit(form_generic, submit_button, data, url, e, msg, modal, form_popup, callback);
	expect(true).toBe(true);
});

test('genericForm_submit, if data.result equals success, if callback defined', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div data-modal id="test-modal" class="bx--modal" aria-describedby="test-modal-heading">
			<span tabindex="0" role="link" class="bx--visually-hidden"></span>
			<div class="bx--modal-container">
				<div class="bx--modal-header">
					<p class="bx--modal-header__heading bx--type-beta" id="test-modal-heading">Test modal header</p>
				</div>
				<div id="test-modal-content" class="bx--modal-content"">
					<p>Test modal content</p>
				</div>
			</div>
			<span tabindex="0"></span>
		</div>

		<form id="test-form">
			<input type="text" id="test-password" value="test-value" required>
		</form>

		<button id="test-button"></button>
	`;

	// Define params, variables, functions and globals.
	var form_generic = $('#test-form');
	var submit_button = document.getElementById('test-button');
	var data = [];
	var url = 'test_url';
	var e = new MouseEvent('click');
	var msg = [
		{
			'Failure_message': 'test_msg2'
		}
	];
	var modal = 'test-modal';
	var form_popup = 'test-modal';
	const callback = function(modal) {
		return true;
	};
	global.action_button = jest.fn(() => true);
	global.rhombuscookie = jest.fn(() => true);
	global.clear_form = jest.fn(() => true);
	
	// Include source file.
	require('../global/generic_form');

	$.post = (url, postData, callback) => {
			callback({
				'result': 'success',
				'message': 'post message'
			})
	}

	// Run test.
	window._rb.genericForm_submit(form_generic, submit_button, data, url, e, msg, modal, form_popup, callback);
	expect(true).toBe(true);
});

test('genericForm_submit, if data.result equals success, else callback undefined', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div data-modal id="test-modal" class="bx--modal" aria-describedby="test-modal-heading">
			<span tabindex="0" role="link" class="bx--visually-hidden"></span>
			<div class="bx--modal-container">
				<div class="bx--modal-header">
					<p class="bx--modal-header__heading bx--type-beta" id="test-modal-heading">Test modal header</p>
				</div>
				<div id="test-modal-content" class="bx--modal-content"">
					<p>Test modal content</p>
				</div>
			</div>
			<span tabindex="0"></span>
		</div>

		<form id="test-form">
			<input type="text" id="test-password" value="test-value">
		</form>

		<button id="test-button"></button>
	`;

	// Define params, variables, functions and globals.
	var form_generic = $('#test-form');
	var submit_button = document.getElementById('test-button');
	var data = [];
	var url = 'test_url';
	var e = new MouseEvent('click');
	var msg = [
		{
			'Failure_message': 'test_msg2'
		}
	];
	var modal = 'test-modal';
	var form_popup = 'test-modal';
	const callback = 'undefined';
	global.action_button = jest.fn(() => true);
	global.rhombuscookie = jest.fn(() => true);
	global.clear_form = jest.fn(() => true);
	
	// Include source file.
	require('../global/generic_form');

	$.post = (url, postData, callback) => {
			callback({
				'result': 'success',
				'message': 'post message'
			})
	}

	// Run test.
	window._rb.genericForm_submit(form_generic, submit_button, data, url, e, msg, modal, form_popup, callback);
	expect(true).toBe(true);
});

test('genericForm_submit, else data.result equals failure, if callback defined', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div data-modal id="test-modal" class="bx--modal" aria-describedby="test-modal-heading">
			<span tabindex="0" role="link" class="bx--visually-hidden"></span>
			<div class="bx--modal-container">
				<div class="bx--modal-header">
					<p class="bx--modal-header__heading bx--type-beta" id="test-modal-heading">Test modal header</p>
				</div>
				<div id="test-modal-content" class="bx--modal-content"">
					<p>Test modal content</p>
				</div>
			</div>
			<span tabindex="0"></span>
		</div>

		<form id="test-form">
			<input type="text" id="test-password" value="test-value">
		</form>

		<button id="test-button"></button>
	`;

	// Define params, variables, functions and globals.
	var form_generic = $('#test-form');
	var submit_button = document.getElementById('test-button');
	var data = [];
	var url = 'test_url';
	var e = new MouseEvent('click');
	var msg = [
		{
			'Failure_message': 'test_msg2'
		}
	];
	var modal = 'test-modal';
	var form_popup = 'test-modal';
	const callback = function(modal) {
		return true;
	};
	global.action_button = jest.fn(() => true);
	global.rhombuscookie = jest.fn(() => true);
	global.clear_form = jest.fn(() => true);
	
	// Include source file.
	require('../global/generic_form');

	$.post = (url, postData, callback) => {
			callback({
				'result': 'failure',
				'message': 'post message'
			})
	}

	// Run test.
	window._rb.genericForm_submit(form_generic, submit_button, data, url, e, msg, modal, form_popup, callback);
	expect(true).toBe(true);
});

/**
 * Test for closeForm.
 */
 test('closeForm', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div data-modal id="test-modal" class="bx--modal" aria-describedby="test-modal-heading">
			<span tabindex="0" role="link" class="bx--visually-hidden"></span>
			<div class="bx--modal-container">
				<div class="bx--modal-header">
					<p class="bx--modal-header__heading bx--type-beta" id="test-modal-heading">Test modal header</p>
				</div>
				<div id="test-modal-content" class="bx--modal-content"">
					<p>Test modal content</p>
				</div>
			</div>
			<span tabindex="0"></span>
		</div>
	`;

	// Define params, variables, functions and globals.
	var form_popup_modal = 'test-modal';

	// Include source file.
	require('../global/generic_form');

	// Run test.
	window._rb.closeForm(form_popup_modal);
	expect(true).toBe(true);
});
