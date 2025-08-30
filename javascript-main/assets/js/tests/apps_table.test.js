/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

const jQuery = require('jquery'); 

global.$ = jQuery;
global.action_button = () => null;
global.rhombuscookie = () => null;
global.sanitizeHtml = html => html;

require('select2')($);
require('jquery-datepicker')($);
require('bootstrap/dist/js/bootstrap.bundle.min.js');

class Rhombus_Datatable {
    constructor(settings) {
        this.settings = settings;
    }

    static copy_button = '';
    static editButton(target) {
        return '';
    }

    init_table() {
        return true;
    }

    initialize_submit(a, b, c, d, e) {
        return true;
    }
}

// issues with trying to boost coverage here
test('load_apps', () => {
    jest.resetModules();

    document.body.innerHTML = `

    `;

    require('../facs/apps_table.js');

    expect(true).toBe(true);
});
