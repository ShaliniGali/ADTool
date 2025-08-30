/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 * 
 */

// Inject globals
const jQuery = require('jquery'); 
global.$ = jQuery;
//global.jQuery = jQuery;
global.rhombuscookie = () => null;
global.sanitizeHtml = function(message, obj) { return message; };

beforeEach(() => {
	jest.resetModules();
});

beforeEach(() => {
	document.body.innerHTML = `
    <div class="d-flex flex-row justify-content-center w-100">
        <div class="position-fixed d-none" id="kg-toast-notification-success" style="z-index:99999;">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div data-notification class="bx--inline-notification bx--inline-notification--low-contrast" role="alert">
                    <div class="bx--inline-notification__details">
                        <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true">
                            <path fill="none" d="M16,8a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,16,8Zm4,13.875H17.125v-8H13v2.25h1.875v5.75H12v2.25h8Z"></path>
                            <path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2Zm0,6a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,16,8Zm4,16.125H12v-2.25h2.875v-5.75H13v-2.25h4.125v8H20Z"></path>
                        </svg>
                        <div class="bx--inline-notification__text-wrapper">
                            <p class="bx--inline-notification__title"></p>
                            <p class="bx--inline-notification__subtitle"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-fixed d-none" id="kg-toast-notification-error" style="z-index:99999;">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div data-notification class="bx--inline-notification bx--inline-notification--low-contrast" role="alert">
                    <div class="bx--inline-notification__details">
                        <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true">
                            <path fill="none" d="M16,8a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,16,8Zm4,13.875H17.125v-8H13v2.25h1.875v5.75H12v2.25h8Z"></path>
                            <path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2Zm0,6a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,16,8Zm4,16.125H12v-2.25h2.875v-5.75H13v-2.25h4.125v8H20Z"></path>
                        </svg>
                        <div class="bx--inline-notification__text-wrapper">
                            <p class="bx--inline-notification__title"></p>
                            <p class="bx--inline-notification__subtitle"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`
});

beforeEach(() => {
	require('../actions/toast_notifications');
});

test('Notification div should be visible.', () => {
    const status = 'success';
    const message = 'Test error message.';

    window._rb.displayToastNotification(status, message, 0);

    expect(true).toBe(true);
});

test('Notification div should have appropriate message.', () => {
    const status = 'success';
    const message = 'Test error message.';

    window._rb.displayToastNotification(status, message);
    expect(true).toBe(true);

});

jest.useFakeTimers();
test('Notification div should not be visible after x seconds', () => {
    const status = 'success';
    const message = 'Test error message.';

    window._rb.displayToastNotification(status, message);
    jest.runAllTimers();
    const isNotVisible = $('#kg-toast-notification-' + status).hasClass('d-none');
    expect(isNotVisible).toBe(true);
});
