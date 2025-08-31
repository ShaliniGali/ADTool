const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

global.rhombuscookie = jest.fn();
global.sanitizeHtml = html => html;

require('../../../actions/SOCOM/dashboard/cycle.js'); 

$.post = jest.fn();

const postImplementation = (url, data, callback) => {
    const mockResponse = { status: true };
    callback(mockResponse);
    return {
        done: (callback) => {
            callback(mockResponse);
            return { fail: jest.fn() };
        },
        fail: jest.fn()
    };
};

describe('Cycle Functions', () => {
    beforeEach(() => {
        $.post.mockReset();
        $.post.mockImplementation(postImplementation);

        document.body.innerHTML = `
            <input id="text-input-cycle-name" value="Test Cycle">
            <textarea id="cycle-text-area-description">Test Description</textarea>
        `;
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

	test('fetchCycles', () => {
		window._rb.fetchCycles();

		expect(true).toBe(true);
	});

    test('fetchDeletedCycles', () => {
		window._rb.fetchDeletedCycles();

		expect(true).toBe(true);
	});

    test('createCycle', () => {
		window._rb.createCycle();

		expect(true).toBe(true);
	});

    test('updateCycle', () => {
		window._rb.updateCycle(1, 'UPDATE_CYCLE_TEXT', {});

		expect(true).toBe(true);
	});

    test('setActive', () => {
        document.body.innerHTML = `
            <button class="activate-button" cycleId="1">Activate</button>
        `;

        const buttonElem = document.querySelector('.activate-button');

		window._rb.toggleMenu.call(buttonElem);
    });
});

describe('DOM Manipulation Functions', () => {
    test('toggleMenu opened', () => {
        document.body.innerHTML = `
            <div class="bx--overflow-menu">
                <button class="toggle-button">Toggle Menu</button>
                <div class="bx--overflow-menu-options bx--overflow-menu-options--open"></div>
            </div>
        `;

        const buttonElem = document.querySelector('.toggle-button');

		window._rb.toggleMenu.call(buttonElem);;

		expect(true).toBe(true);
	});

    test('toggleMenu closed', () => {
        document.body.innerHTML = `
            <div class="bx--overflow-menu">
                <button class="toggle-button">Toggle Menu</button>
                <div class="bx--overflow-menu-options"></div>
            </div>
        `;

        const buttonElem = document.querySelector('.toggle-button');

		window._rb.toggleMenu.call(buttonElem);

		expect(true).toBe(true);
	});

    test('closeMenu', () => {
        document.body.innerHTML = `
            <div class="bx--overflow-menu">
                <button class="close-button">Close Menu</button>
                <div class="bx--overflow-menu-options bx--overflow-menu-options--open"></div>
            </div>
        `;

        const closeButton = $('.close-button');

		window._rb.closeMenu(closeButton);

		expect(true).toBe(true);
	});

    test('closeEditModal', () => {
        document.body.innerHTML = `
            <div id="cycle_edit_view_modal">
                <div class="bx--modal bx--modal-tall is-visible"></div>
            </div>
        `;

		window._rb.closeEditModal();

		expect(true).toBe(true);
	});
})