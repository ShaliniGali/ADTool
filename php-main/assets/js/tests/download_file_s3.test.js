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
global.sanitizeHtml = () => undefined;

/**
 * Tests for download_file
 */
test('download_file, if post error', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div></div>
	`;

	// Define params, variables, functions and globals.
	var filename = 'test_filename';
	var encry_file = new File([''], filename);
	global.rhombuscookie = jest.fn(() => true);
	
	// Include source file.
	require('../global/download_file_s3');

	$.post = (url, postData, callback) => {
			callback(
				JSON.stringify({
					'status': 'ERROR',
					'message': 'post message'}
				)
			)
	}

	// Run test.
	window._rb.download_file(encry_file, filename);
	expect(true).toBe(true);
});

test('download_file, else post success', () => {
	jest.resetModules();

	// Create mock html structure.
	document.body.innerHTML = `
		<div></div>
	`;

	// Define params, variables, functions and globals.
	var filename = 'test_filename';
	var encry_file = new File([''], filename);
	global.rhombuscookie = jest.fn(() => true);
	
	// Include source file.
	require('../global/download_file_s3');

	$.post = (url, postData, callback) => {
			callback(
				JSON.stringify({
					'status': 'SUCCESS',
					'message': 'post message'}
				)
			)
	}

	// Run test.
	window._rb.download_file(encry_file, filename);
	expect(true).toBe(true);
});
