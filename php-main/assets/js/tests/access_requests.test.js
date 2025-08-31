const jQuery = require('jquery');
global.$ = jQuery;
global.jQuery = jQuery;
global.rhombuscookie = jest.fn(() => true);
global.loginWithGoogle2FA = jest.fn(() => true);
global.sanitizeHtml = jest.fn(() => '<div></div>');
global.ajaxSetup = jest.fn(() => true);
$.fn.ready = (callback) => { callback() };

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require("tilt.js/dest/tilt.jquery.min.js")($);

beforeEach(() => {
	jest.resetModules();
});

test('access_error', () => {
	document.body.innerHTML = '\
		<div id="modal-facs"></div>\
	';

	let jqXJR = {
		'responseJSON': {
			'type': 'test_type',
			'message': 'test_message'
		}
	}

	require('../facs/access_requests.js');
	let actual = window._rb.access_error(jqXJR);
	expect(actual).toBe(undefined);
});

