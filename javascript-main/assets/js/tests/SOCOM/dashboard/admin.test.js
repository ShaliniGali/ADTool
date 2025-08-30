const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

global.rhombuscookie = jest.fn(() => 'rhombuscookie');
global.displayToastNotification = jest.fn();

global.$.fn.DataTable = jest.fn((obj = null) => {
	if (obj && obj.rowCallback) {
		obj.rowCallback('', {
            GROUP: 'AO',
        });
	}

    return {
        column: () => {
            return {
                    search: () => {
                        return {
                            draw: () => true
                    }
                }
            }
        },
        ajax: {
            reload: jest.fn(),
        },
        on: jest.fn(),
    }
});

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

require('../../../actions/SOCOM/dashboard/admin.js');

describe('Admin and AO/AD Table Functions', () => {
    beforeEach(() => {
        $.post.mockReset();
        $.post.mockImplementation(postImplementation);

        document.body.innerHTML = `
            <table id="admin-list"></table>
            <table id="ao-ad-list"></table>
        `;
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    test('onReady', () => {
        window._rb.onReady();
        expect(true).toBe(true);
    });

    test('user_admin_table initializes the DataTable correctly', () => {
        window._rb.user_admin_table();
        expect($.fn.DataTable).toHaveBeenCalled();

        expect($.fn.DataTable).toHaveBeenCalledWith({
            columnDefs: expect.any(Array),
            ajax: expect.any(Object),
            length: 10,
            lengthChange: true,
            orderable: false,
            ordering: false,
            searching: true,
            rowHeight: '75px',
            rowCallback: expect.any(Function),
        });

        const ajaxConfig = $.fn.DataTable.mock.calls[0][0].ajax;
        expect(ajaxConfig.url).toBe('/dashboard/admin/admin_users/list/get');
        expect(ajaxConfig.type).toBe('POST');
        expect(ajaxConfig.data.rhombus_token()).toBe('rhombuscookie');
        expect(ajaxConfig.dataSrc).toBe('data');
    });

    test('user_ao_ad_table initializes the DataTable correctly', () => {
        window._rb.user_ao_ad_table();
        expect($.fn.DataTable).toHaveBeenCalled();

        expect($.fn.DataTable).toHaveBeenCalledWith({
            columnDefs: expect.any(Array),
            ajax: expect.any(Object),
            length: 10,
            lengthChange: true,
            orderable: false,
            ordering: false,
            searching: true,
            rowHeight: '75px',
            rowCallback: expect.any(Function),
        });

        const ajaxConfig = $.fn.DataTable.mock.calls[0][0].ajax;
        expect(ajaxConfig.url).toBe('/dashboard/admin/ao_ad_users/list/get');
        expect(ajaxConfig.type).toBe('POST');
        expect(ajaxConfig.data.rhombus_token()).toBe('rhombuscookie');
        expect(ajaxConfig.dataSrc).toBe('data');
    });
});

describe('set_cycle_status', () => {
    beforeEach(() => {
        $.post.mockReset();
        $.post.mockImplementation(postImplementation);

        const email = 'test@example.com';
        document.body.innerHTML = `
            <div>
                <input type="checkbox" class="cycle-status-checkbox" data-EMAIL="${email}" checked>
            </div>
        `;
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    test('set_cycle_status checked', () => {
        const checkbox = document.querySelector('input.cycle-status-checkbox');

        window._rb.set_cycle_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('success', 'Cycle or Weight Criteria Status Change Complete');
    });

    test('set_cycle_status unchecked', () => {
        const checkbox = document.querySelector('input.cycle-status-checkbox');

        checkbox.checked = false;

        window._rb.set_cycle_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('success', 'Cycle or Weight Criteria Status Change Complete');
    });

    test('set_cycle_status ajax fail', () => {
        const checkbox = document.querySelector('input.cycle-status-checkbox');

        $.post.mockImplementation(() => {
            return {
                fail: (callback) => {
                    callback();
                },
            };
        });

        window._rb.set_cycle_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('error', 'Unable to change Cycle or Weight Criteria Status');
    });
});

describe('set_admin_status', () => {
    beforeEach(() => {
        $.post.mockReset();
        $.post.mockImplementation(postImplementation);

        const email = 'test@example.com';
        document.body.innerHTML = `
            <div>
                <input type="checkbox" class="admin-group-status" data-EMAIL="${email}" checked>
            </div>
        `;
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    test('set_admin_status checked', () => {
        const checkbox = document.querySelector('input.admin-group-status');

        window._rb.set_admin_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('success', 'Admin Status Change Complete');
    });

    test('set_admin_status unchecked', () => {
        const checkbox = document.querySelector('input.admin-group-status');

        checkbox.checked = false;

        window._rb.set_admin_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('success', 'Admin Status Change Complete');
    });

    test('set_admin_status ajax fail', () => {
        const checkbox = document.querySelector('input.admin-group-status');

        $.post.mockImplementation(() => {
            return {
                fail: (callback) => {
                    callback();
                },
            };
        });

        window._rb.set_admin_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('error', 'Unable to change Admin Status');
    });
});

describe('set_ao_ad_status', () => {
    beforeEach(() => {
        $.post.mockReset();
        $.post.mockImplementation(postImplementation);

        const email = 'test@example.com';
        document.body.innerHTML = `
            <div>
                <input type="checkbox" class="ao-ad-group-status" data-EMAIL="${email}" checked>
            </div>
        `;
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    test('set_ao_ad_status checked', () => {
        const checkbox = document.querySelector('input.ao-ad-group-status');

        window._rb.set_ao_ad_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('success', 'AO or AD Commenter Status Change Complete');
    });

    test('set_ao_ad_status unchecked', () => {
        const checkbox = document.querySelector('input.ao-ad-group-status');

        checkbox.checked = false;

        window._rb.set_ao_ad_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('success', 'AO or AD Commenter Status Change Complete');
    });

    test('set_ao_ad_status ajax fail', () => {
        const checkbox = document.querySelector('input.ao-ad-group-status');

        $.post.mockImplementation(() => {
            return {
                fail: (callback) => {
                    callback();
                },
            };
        });

        window._rb.set_ao_ad_status.call(checkbox);

        expect(displayToastNotification).toHaveBeenCalledWith('error', 'Unable to change AO or AD Commenter Status');
    });
});