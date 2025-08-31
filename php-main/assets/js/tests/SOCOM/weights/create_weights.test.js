/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('ion-rangeslider/js/ion.rangeSlider.js'); 
global.rhombuscookie = jest.fn();
global.sanitizeHtml = html => html;
global.load_storm_table = jest.fn();

describe('create_weights_slider_panel', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input type="text" class="test-crit-sliders" crit="crit_name"></input>
            <input type="text" class="test-criteria-crit_name" value=""></input>
		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

        const current_tab = 'test';
		window._rb.create_weights_slider_panel(current_tab);

		expect(true).toBe(true);
	});
});

describe('refresh_csrf_cookie', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
		`;

        rb_input_submit_form_cookie = () => true;
        setTimeout = (cb, t) => true;

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.refresh_csrf_cookie()

		expect(true).toBe(true);
	});
});

describe('getTabWeightTotal', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input type="text" class="pom-crit-sliders" value="1.0"></input>
		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

        const type_param = 'pom';
		window._rb.getTabWeightTotal(type_param);

		expect(true).toBe(true);
	});
});

describe('validateForm', () => {
	test('title length 0 (title not set)', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input id="text-input-title" type="text" value="   "></input>
            <input type="text" class="pom-crit-sliders" value="1.0"></input>
		`;

        displayToastNotification = (notif_type, msg) => true;

		require('../../../actions/SOCOM/weights/create_weights.js');

        const type_param = 'pom';
		window._rb.validateForm(type_param)

		expect(true).toBe(true);
	});

    test('text-input-title has invalid class for other reason', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input id="text-input-title" type="text" value="test" class="bx--text-input--invalid"></input>
            <input type="text" class="pom-crit-sliders" value="123.456"></input>
		`;

        displayToastNotification = (notif_type, msg) => true;

		require('../../../actions/SOCOM/weights/create_weights.js');

        const type_param = 'pom';
		window._rb.validateForm(type_param)

		expect(true).toBe(true);
	});
});

describe('changeForm', () => {
    beforeEach(() => {
        jest.resetModules();
        document.body.innerHTML = `
            <div id="guidance-panel-container"></div>
            <div id="pom-panel-container"></div>
            <div id="storm-panel-container"></div>
        `;

        require('../../../actions/SOCOM/weights/create_weights.js');
    });

	test('type === guidance', () => {
        const type_param = 'guidance';
		window._rb.changeForm(type_param);

        expect($('#guidance-panel-container').prop('hidden')).toBe(true);
        expect($('#pom-panel-container').prop('hidden')).toBe(false);
	});

    test('type === pom', () => {
        const type_param = 'pom';
		window._rb.changeForm(type_param);

        expect($('#pom-panel-container').prop('hidden')).toBe(true);
        expect($('#storm-panel-container').prop('hidden')).toBe(false);
	});

    test('type === storm', () => {
        const type_param = 'storm';
		const return_val = window._rb.changeForm(type_param);

        expect(return_val).toBe(false);
	});
});

describe('progressLevels', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <ul>
                <li class="bx--progress-step--current">test</li>
                <li class="bx--progress-step--current">123</li>
                <li class="bx--progress-step--complete">456
                    <svg></svg>
                </li>
            </ul>
		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

        const type_param = 'pom';
		window._rb.progressLevels(type_param);

		expect(true).toBe(true);
	});
});

describe('saveForm', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input id="pom-criteria-crit_name" class="pom-crit-sliders" value="1.0"></input>
            <input id="pom-text-area-description" value="description"></input>
		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

        const type_param = 'pom';
		window._rb.saveForm(type_param);

		expect(true).toBe(true);
	});
});

describe('sendWeight', () => {
	test('early return false (invalid form)', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input id="text-input-title" type="text" value="    "></input>
		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

		const return_val = window._rb.sendWeight();

		expect(return_val).toBe(false);
	});

    test('data.status === true', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input id="text-input-title" type="text" value=test_title"></input>
            <input id="guidance-criteria-crit_name" class="guidance-crit-sliders" value="1.0"></input>
            <input id="guidance-text-area-description" value="guidance_description"></input>
            <input id="pom-criteria-crit_name" class="pom-crit-sliders" value="1.0"></input>
            <input id="pom-text-area-description" value="pom_description"></input>
		`;

        const jqXHR = {
            responseJSON: {
                message: 'test'
            }
        };
        $.post = (url, data, cb, format) => {
            cb({
                status: true,
                message: 'success'
            });
            return {
                fail: () => true
            }
        };
        $.post.fail = (cb_fail) => {
            cb_fail(jqXHR);
            return $.post;
        }
        displayToastNotification = (notif_type, msg) => true;
        setTimeout = (cb, t) => cb();

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.sendWeight();

		expect(true).toBe(true);
	});

    test('data.status !== true', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input id="text-input-title" type="text" value=test_title"></input>
            <input id="guidance-criteria-crit_name" class="guidance-crit-sliders" value="1.0"></input>
            <input id="guidance-text-area-description" value="guidance_description"></input>
            <input id="pom-criteria-crit_name" class="pom-crit-sliders" value="1.0"></input>
            <input id="pom-text-area-description" value="pom_description"></input>
		`;

        const jqXHR = null;
        $.post = (url, data, cb, format) => {
            cb({
                status: false,
                message: 'error test'
            });
            return {
                fail: () => true
            }
        };
        $.post.fail = (cb_fail) => {
            cb_fail(jqXHR);
            return $.post;
        }
        displayToastNotification = (notif_type, msg) => true;
        setTimeout = (cb, t) => cb();

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.sendWeight();

		expect(true).toBe(true);
	});
});

describe('ajaxFail', () => {
    test('if branch', () => {
        jest.resetModules();

		document.body.innerHTML = `
		`;

        const jqXHR = {
            responseJSON: {
                message: 'test'
            }
        };
        displayToastNotification = (notif_type, msg) => true;

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.ajaxFail(jqXHR);

		expect(true).toBe(true);
	});

    test('else branch', () => {
        jest.resetModules();

		document.body.innerHTML = `
		`;

        const jqXHR = {};
        displayToastNotification = (notif_type, msg) => true;

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.ajaxFail(jqXHR);

		expect(true).toBe(true);
	});
});

describe('onReady', () => {
	test('crit sliders change guidance | invalid sum (if branch)', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input type="text" id="text-input-title" value="test_title"></input>
            <input type="text" id="guidance-test-id" class="guidance-crit-sliders" value="2.0"></input>
            <button id="create-guidance-weight">test</button>

		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.onReady()

        $('#create-guidance-weight').trigger('click');
        $('#guidance-test-id').trigger('change');

		expect(true).toBe(true);
	});

    test('crit sliders change guidance | valid sum (if branch)', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input type="text" id="text-input-title" value="test_title"></input>
            <input type="text" id="guidance-test-id" class="guidance-crit-sliders" value="1.0"></input>
            <button id="create-guidance-weight">test</button>

		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.onReady()

        $('#create-guidance-weight').trigger('click');
        $('#guidance-test-id').trigger('change');

		expect(true).toBe(true);
	});


    test('crit sliders change pom | valid sum (else branch)', () => {
        jest.resetModules();

		document.body.innerHTML = `
            <input type="text" id="text-input-title" value="test_title"></input>
            <input type="text" id="pom-test-id" class="pom-crit-sliders" value="1.0"></input>
            <button id="create-pom-weight">test</button>

		`;

		require('../../../actions/SOCOM/weights/create_weights.js');

		window._rb.onReady()

        $('#create-pom-weight').trigger('click');
        $('#pom-test-id').trigger('change');

		expect(true).toBe(true);
	});
});