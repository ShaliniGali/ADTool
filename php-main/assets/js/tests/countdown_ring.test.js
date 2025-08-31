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
global.sanitizeHtml = html => html;
require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);

/**
 * Tests for showTimer.
 */
test('showTimer, if format is minute', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div id="test-div">
			<div class="timer">

			</div>
		</div>
	`;

	// Define params, variables, functions and globals.
	var startTime = 0;
	var format = 'minutes';
	var expiryMessage = 'test message';
	var parentElement = $('#test-div');
	global.setInterval = (handler) => handler();

	// Include source file.
	require('../global/countdown_ring');

	// Run test.
	window._rb.showTimer(startTime, format, expiryMessage, parentElement);
	expect(true).toBe(true);
});

test('showTimer, else format is seconds', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div id="test-div">
			<div class="timer">

			</div>
		</div>
	`;

	// Define params, variables, functions and globals.
	var startTime = 0;
	var format = 'seconds';
	var expiryMessage = 'test message';
	var parentElement = $('#test-div');
	global.setInterval = (handler) => handler();

	// Include source file.
	require('../global/countdown_ring');

	// Run test.
	window._rb.showTimer(startTime, format, expiryMessage, parentElement);
	expect(true).toBe(true);
});

test('showTimer, if startTime equals 60', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div id="test-div">
			<div class="timer">

			</div>
		</div>
	`;

	// Define params, variables, functions and globals.
	var startTime = 60;
	var format = 'minutes';
	var expiryMessage = 'test message';
	var parentElement = $('#test-div');
	global.setInterval = (handler) => handler();

	// Include source file.
	require('../global/countdown_ring');

	// Run test.
	window._rb.showTimer(startTime, format, expiryMessage, parentElement);
	expect(true).toBe(true);
});
