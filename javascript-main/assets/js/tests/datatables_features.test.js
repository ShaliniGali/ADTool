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
global.jQuery = jQuery;
global.sanitizeHtml = html => html;

// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');
// The datatables plugin attaches itself differently in node.js (see datatables/media/js/jquery.dataTables.js)
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);
require('select2')(jQuery);

describe('RhombusDatatable', () => {
    test('RhombusDatatable.createColumnSelector', () => {
        jest.resetModules();

        Event.prototype.stopPropagation = jest.fn(() => null);
        Date.prototype.getTime = () => '';
        $.fn.ready = (callback) => callback();

        document.body.innerHTML = `
            <div id="target-div"></div>
            <div>
                <table id="table">
                    <thead>
                        <tr>
                            <th>header0</th>
                            <th>header1</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>data0</td>
                            <td>data1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;

        require('../global/datatables_features');

        const rhombusDatatable = new window._rb.RhombusDatatable();

        const columnName = [
            {
                data: 'data1',
                visible: true
            },
            {
                data: 'data2',
                visible: false
            },
            {
                data: null,
                visible: false
            }
        ];

        const table = $('#table').DataTable();
        // TODO: for some reason the datatable isn't well-formed, so these are mocked to avoid errors
        table.columns = () => ({
            every: (callback) => {
                let counter = 0;
                const values = ['false', null];
                const context = {
                    dataSrc: () => {
                        const value = values[counter];
                        counter++;
                        return value;
                    },
                    index: () => 0
                }
                callback.call(context);
                callback.call(context);
            }
        });
        table.column = () => ({
            visible: () => ({
                draw: () => null
            })
        });

        const targetDiv = $('#target-div');
        rhombusDatatable.createColumnSelector(columnName, table, targetDiv);

        $('#_dropdown').trigger('click');
        $('#_columnCheck0').val('false')
        $('#_columnCheck0').get(0).dispatchEvent(new Event('change'));

        expect(Event.prototype.stopPropagation).toHaveBeenCalled();
    });

    test('RhombusDatatable.initEditButtons', () => {
        jest.resetModules();

        const editClass = 'edit-class';
        const modalIds = {
            title: 'title',
            button: 'button',
            modal: 'modal'
        };

        document.body.innerHTML = `
            <div>
                <div id="${modalIds.title}"></div>
                <div id="${modalIds.button}"></div>
                <div id="${modalIds.modal}"></div>

                <table id="table">
                    <thead>
                        <tr>
                            <th>test header</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <button class="${editClass}"></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;

        require('../global/datatables_features');

        const rhombusDatatable = new window._rb.RhombusDatatable();

        const table = $('#table').DataTable();
        const selectedRowData = [null];
        const resetForm = () => null;
        const setValues = () => null;
        rhombusDatatable.initEditButtons(editClass, modalIds, table, selectedRowData, resetForm, setValues);

        $(`.${editClass}`).trigger('click');

        expect($(`#${modalIds.title}`).text()).toBe('Edit Record');
        expect($(`#${modalIds.button}`).text()).toBe('Edit Record');
    });

    test('RhombusDatatable.initDeleteFunctionality', () => {
        jest.resetModules();

        const modalIds = {
            heading: 'heading',
            message: 'message',
            modal: 'modal',
            footer: 'footer',
            confirm: 'confirm',
        };
        const deleteButtons = 'delete-buttons';

        global.rhombuscookie = () => null;

        document.body.innerHTML = `
            <div>
                <div id="${modalIds.heading}"></div>
                <div id="${modalIds.message}"></div>
                <div id="${modalIds.modal}"></div>


                <button id="${modalIds.footer}">
                    <div id="${modalIds.confirm}"></div>
                </button>

                <button class="${deleteButtons}"></button>

                <table id="table">
                    <thead>
                        <tr>
                            <th>test header</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>test data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;

        require('../global/datatables_features');

        const refresh = false;
        const dataTable = $('#table').DataTable();
        dataTable.row = () => {
            return {
                data: () => [{ id: 'id' }]
            }
        };
        const message = () => 'message';
        const url = 'url';
        const selectedRowData = [null];
        const refreshTable = () => null;
        const tableName = () => 'table-name';
        const include_user_id = true;
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.initDeleteFunctionality(refresh, deleteButtons, dataTable, message, modalIds, url, selectedRowData, refreshTable, tableName, include_user_id);

        // TODO: how to revert this (ie in a tearDown method or save the original then restore)
        $.post = (url, options, callback) => callback();

        $(`.${deleteButtons}`).trigger('click');
        $(`#${modalIds.confirm}`).trigger('click');

        expect($(`#${modalIds.heading}`).text()).toBe('Are you sure you want to delete?');

        // excluding user id, also trigger default tableName
        rhombusDatatable.initDeleteFunctionality(refresh, deleteButtons, dataTable, message, modalIds, url, selectedRowData, refreshTable);

        $(`.${deleteButtons}`).trigger('click');
        $(`#${modalIds.confirm}`).trigger('click');

        expect($(`#${modalIds.heading}`).text()).toBe('Are you sure you want to delete?');

        // don't refresh
        rhombusDatatable.initDeleteFunctionality(!refresh, deleteButtons, dataTable, message, modalIds, url, selectedRowData, refreshTable);

        $(`.${deleteButtons}`).trigger('click');
        $(`#${modalIds.confirm}`).trigger('click');

        expect($(`#${modalIds.heading}`).text()).toBe('Are you sure you want to delete?');
    });

    test('RhombusDatatable.initExportButton', () => {
        jest.resetModules();

        $.fn.dataTable.Buttons = function() { return this; };

        const excelBtnClass = 'buttons-excel';
        const targetId = 'button';
        document.body.innerHTML = `
            <div id="${targetId}"></div>
            <button class="${excelBtnClass}"></button>
            <table>
                <thead><tr><th>header</th></tr></thead>
                <tbody><tr><td>data</td></tr></tbody>
            </table>
        `;

        require('../global/datatables_features');

        const table = $('table').DataTable();
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.initExportButton(table, $(`#${targetId}`));

        expect($(`#${targetId}`).children().length).toBe(1);
    });

    test('RhombusDatatable.repositionExportButton', () => {
        jest.resetModules();

        const id = 'element';
        document.body.innerHTML = `
            <div>
                <button class="buttons-excel"></button>
                <div id="${id}"></div>
            </div>
        `;

        require('../global/datatables_features');

        const rhombusDatatable = new window._rb.RhombusDatatable();
        const element = $(`#${id}`);
        rhombusDatatable.repositionExportButton(element);

        expect(element.children().length).toBe(1);
    });

    test('RhombusDatatable.showCopyButton', () => {
        jest.resetModules();

        require('../global/datatables_features');

        const rhombusDatatable = new window._rb.RhombusDatatable();
        const copyButton = $(rhombusDatatable.showCopyButton);

        expect(copyButton.get(0).tagName).toBe('BUTTON');
    });

    test('RhombusDatatable.copyToClipboard', () => {
        jest.resetModules();

        require('../global/datatables_features');

        document.execCommand = jest.fn(() => null);

        const textToCopy = 'text-to-copy';
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.copyToClipboard(textToCopy, document.createElement('DIV'));

        expect(document.execCommand).toHaveBeenCalled();
    });

    test('RhombusDatatable.initCopyFunctionality', () => {
        jest.resetModules();

        const copyBtnClass = 'copyBtn';
        document.body.innerHTML = `
            <div>
                <button class="${copyBtnClass}"></button>
            </div>
        `;

        require('../global/datatables_features');

        document.execCommand = jest.fn(() => null);

        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.initCopyFunctionality();

        $('.copyBtn').trigger('click');

        expect(document.execCommand).toHaveBeenCalled();
    });

    test('RhombusDatatable.initTooltips', () => {
        jest.resetModules();

        const copyBtnClass = 'copyBtn';
        const tooltipClass = 'tooltip';
        document.body.innerHTML = `
            <div>
                <button class="${copyBtnClass}"></button>
                <button class="${tooltipClass}"></button>
            </div>
        `;

        require('../global/datatables_features');

        global.setTimeout = (callback) => callback();
        $.fn.tooltip = jest.fn(() => null);
        $.fn.ready = jest.fn((callback) => callback());

        const tooltips = $(`.${tooltipClass}`);
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.initTooltips([tooltips]);

        $('.copyBtn').trigger('click');

        expect($.fn.tooltip).toHaveBeenCalledTimes(4);

        // no tooltips
        rhombusDatatable.initTooltips(null);

        expect($.fn.tooltip).toHaveBeenCalledTimes(5);
    });

    test('RhombusDatatable.addDropdown', () => {
        jest.resetModules();

        const targetId = 'targetId';

        document.body.innerHTML = `
            <div id="${targetId}"></div>
        `;

        require('../global/datatables_features');

        const list = [
            ['value', 'text']
        ];
        const dropdownCallback = jest.fn(() => null);
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.addDropdown(list, $(`#${targetId}`), dropdownCallback);

        $('select').trigger('change');

        expect(dropdownCallback).toHaveBeenCalled();
    });

    test('RhombusDatatable.addRecord', () => {
        jest.resetModules();

        const formIds = {
            title: 'title',
            button: 'button',
            modal: 'modal'
        };

        document.body.innerHTML = `
            <div id="${formIds.title}"></div>
            <div id="${formIds.button}"></div>
            <div id="${formIds.modal}"></div>
        `;

        require('../global/datatables_features');

        const selectedRowData = [{}];
        const resetFormValues = jest.fn(() => null);
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.addRecord(selectedRowData, resetFormValues, formIds);

        expect(resetFormValues).toHaveBeenCalled();
        expect($(`#${formIds.title}`).text()).toBe('Add Record');
        expect($(`#${formIds.button}`).text()).toBe('Add Record');
    });

    test('RhombusDatatable.initOverwriteConfirm', () => {
        jest.resetModules();

        $.post = jest.fn((_url, _payload, callback) => callback());

        const modalIds = {
            footer: 'footer',
            confirm: 'confirm',
        };

        document.body.innerHTML = `
            <div id="${modalIds.footer}">
                <button id="${modalIds.confirm}"></button>
            </div>
        `;

        require('../global/datatables_features');

        const formValues = () => ({});
        const duplicateId = () => null;
        const url = 'url';
        const postCallback = jest.fn(() => null);
        const rhombusDatatable = new window._rb.RhombusDatatable();
        rhombusDatatable.initOverwriteConfirm(modalIds, formValues, duplicateId, url, postCallback);

        $(`#${modalIds.confirm}`).trigger('click');

        expect($.post).toHaveBeenCalled();
        expect(postCallback).toHaveBeenCalled();
    });
});
