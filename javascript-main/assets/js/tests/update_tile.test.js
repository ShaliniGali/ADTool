const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;
global.rhombuscookie = jest.fn(() => true);
global.loginWithGoogle2FA = jest.fn(() => true);
global.sanitizeHtml = jest.fn(() => '<div></div>');

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require("tilt.js/dest/tilt.jquery.min.js")($);

beforeEach(() => {
	jest.resetModules();
});

test('populate_fields', () => {
	document.body.innerHTML = `
		<div></div>
	`
	let callbackData = JSON.stringify([{
		description: 'test',
		icon: 'test',
		label: 'test'
	}]);
	$.post = (url, postData, callbackFunc) => {
		callbackFunc(callbackData, 200);
	}

	require('../sso_tiles/update_tile.js');
	let actual = window._rb.populate_fields();
	expect(actual).toBe(undefined);
});

test('save_tiles if invalid', () => {
	document.body.innerHTML = '\
		<div id="select-tile"></div>\
		<div id="svg-text"></div>\
		<div id="label-text"></div>\
		<div id="description-text"></div>\
	';
	let callbackData = JSON.stringify({
		'result': 'success'
	});
	$.post = (url, postData, callbackFunc) => {
		callbackFunc(callbackData);
	}

	require('../sso_tiles/update_tile.js');
	let actual = window._rb.save_tiles();
	expect(actual).toBe(undefined);
});

test('save_tiles else valid', () => {
	document.body.innerHTML = '\
		<select id="select-title">\
			<option selected>test_tile1</option>\
			<option>test_tile2</option>\
		</select>\
		<select id="svg-text">\
			<option selected>test_svg1</option>\
			<option>test_svg2</option>\
		</select>\
		<select id="label-text">\
			<option selected>test_label1</option>\
			<option>test_label2</option>\
		</select>\
		<select id="description-text">\
			<option selected>test_desc1</option>\
			<option>test_desc2</option>\
		</select>\
	';
	let callbackData = JSON.stringify({
		'result': 'success'
	});
	$.post = (url, postData, callbackFunc) => {
		callbackFunc(callbackData);
	}

	require('../sso_tiles/update_tile.js');
	let actual = window._rb.save_tiles();
	expect(actual).toBe(undefined);
});
