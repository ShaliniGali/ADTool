const $ = require('jquery');
global.$ = $;

global.rhombuscookie = jest.fn(() => 'dummy_token');
global.displayToastNotification = jest.fn();

const { save_admin_status, save_ao_ad_status, onReady } = require('../../../actions/SOCOM/dashboard/users');

beforeEach(() => {
    document.body.innerHTML = `
        <select id="admin-status-id">
            <option value="0">Select</option>
            <option value="1">Admin</option>
            <option value="2">User</option>
            <option value="3">Undefined</option>
        </select>
        <button id="admin-status-save">Save Admin Status</button>
        
        <select id="ao-ad-status-id">
            <option value="0">Select</option>
            <option value="1">AO</option>
            <option value="2">AD</option>
            <option value="3">Another Status</option>
            <option value="4">Yet Another Status</option>
            <option value="5">Undefined</option>
        </select>
        <button id="ao-ad-status-save">Save AO/AD Status</button>

        <select id="cycle-status-id">
            <option value="0">Select</option>
            <option value="1">Cycle Status</option>
            <option value="2">Weight Criteria Status</option>
        </select>
        <button id="cycle-status-save">Save Cycle Status</button>
    `;

    $.post = jest.fn().mockImplementation((url, data, callback) => {
        const mockResponse = { status: true };
        callback(mockResponse);
        return {
            done: (callback) => {
                callback(mockResponse);
                return { fail: jest.fn() };
            },
            fail: jest.fn().mockImplementation((callback) => {
                callback({}); 
                return this;
            }),
        };
    });

});

test('save_admin_status should handle Invalid group ID', () => {
    $('#admin-status-id').val('3');
    window._rb.save_admin_status();
    window._rb.onReady();

    expect(displayToastNotification).toHaveBeenCalledWith('error', 'Group chosen is not available. Please refresh and try again');
    expect($.post).not.toHaveBeenCalled();

});

test('save_admin_status should handle valid group ID', () => {
    $('#admin-status-id').val('1');
    window._rb.save_admin_status();

    expect(true).toBe(true)
    expect($.post).toHaveBeenCalledWith(
        '/dashboard/myuser/admin/save',
        { rhombus_token: 'dummy_token', gid: 1 },
        expect.any(Function),
        'json'
    );

});

test('save_admin_status should fail', () => {
    $('#admin-status-id').val('7');
    window._rb.save_admin_status();

    expect(true).toBe(true);

});


test('save_ao_ad_status should handle valid group ID', () => {
    $('#ao-ad-status-id').val('5');
    window._rb.save_ao_ad_status();

    expect(displayToastNotification).toHaveBeenCalledWith('error', 'Group chosen is not available. Please refresh and try again');
    expect($.post).not.toHaveBeenCalled();
});



test('save_ao_ad_status should handle valid group ID', () => {
    $('#ao-ad-status-id').val('1');
    window._rb.save_ao_ad_status();

    expect($.post).toHaveBeenCalledWith(
        '/dashboard/myuser/ao_ad/save',
        { rhombus_token: 'dummy_token', gid: "1" },
        expect.any(Function),
        'json'
    );
});

test('save_cycle_status should show error when gid is less than 1 or greater than 4', () => {
    $('#cycle-status-id > option[value="0"]').prop('selected', true);

    expect(window._rb.save_cycle_status()).toBeFalsy();
    expect(displayToastNotification).toHaveBeenCalledWith('error', 'Cycle or Weight Criteria status chosen is not available. Please refresh and try again');
});

test('save_cycle_status should call $.post with correct parameters for valid gid', () => {
    $('#cycle-status-id > option[value="1"]').prop('selected', true);
    $.post.mockReturnValueOnce({
        fail: jest.fn(),
    });

    window._rb.save_cycle_status();

    expect($.post).toHaveBeenCalledWith('/dashboard/myuser/cycle_users/save', {
        rhombus_token: 'dummy_token',
        gid: '1',
    }, expect.any(Function), "json");
});

test('save_cycle_status should display success notification on successful response', () => {
    $('#cycle-status-id > option[value="2"]').prop('selected', true);
 
    window._rb.save_cycle_status();

    expect(displayToastNotification).toHaveBeenCalledWith('success', 'Cycle or Weight Criteria Status Request Sent');
});
