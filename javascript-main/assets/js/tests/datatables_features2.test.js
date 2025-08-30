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
global.clear_form = jest.fn();
global.action_button = jest.fn();
global.rhombuscookie = jest.fn();
global.custom_check = jest.fn((id) => id);
global.sanitizeHtml = jest.fn();

const params = {
    table_name: 'table',
    datatable_properties: {
      dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-3'B><'col-6 col-md-3'f><'col-3 col-md-1 toolbar_roles'><'col-3 col-md-1 exportDiv_roles'>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
      data: ['admin'],
      columns: [
        {data: 'id', mData: 'id', render: () =>{return}, mRender: () => {return}},
        {data: 'name', mData: 'name', render: () =>{return}, mRender: () => {return}},
        {data: 'status', mData: 'status', render: () =>{return}, mRender: () => {return}},
        {
            defaultContent: "<button class = 'rowBtn' onclick='role_table.delete_record($(this))' data-toggle='tooltip' data-placement='top' title='Delete'><i class='far fa-trash-alt'></i></button>",
            sDefaultContent: "<button class = 'rowBtn' onclick='role_table.delete_record($(this))' data-toggle='tooltip' data-placement='top' title='Delete'><i class='far fa-trash-alt'></i></button>"
        },
        {
            defaultContent: "<button class = 'rowBtn' onclick='role_table.edit_record($(this))' data-toggle='tooltip' data-placement='top' title='Edit'><i class='far fa-edit'></i></button>",
            sDefaultContent: "<button class = 'rowBtn' onclick='role_table.edit_record($(this))' data-toggle='tooltip' data-placement='top' title='Edit'><i class='far fa-edit'></i></button>"
        }
      ],
      scrollY: '60vh'
    },
    export_div: 'div.exportDiv_roles',
    form_ids: {
      title: 'rolesFormModalTitle',
      button: 'rolesSubmitRecord',
      modal: 'rolesFormModal',
      form: 'rolesForm'
    },
    error_ids: [ 'roles_error_name' ],
    delete_modal_ids: {
      heading: 'roles_confirmDeleteHeading',
      message: 'roles_confirmDeleteMessage',
      confirm: 'roles_confirmDeleteBtn',
      modal: 'roles_confirmDelete'
    },
    tooltips: ['.rowBtn'],
    overwrite_modal_ids: { modal: 'duplicateRecord', confirm: 'confirmDuplicateRecordBtn' },
    set_form_values: function() {return},
    get_form_values: function() {return {}},
    delete_message: function() {return},
    additional_reset_form: function() {return}
  }

const paramsNoDelete = {
table_name: 'roles_table',
datatable_properties: {
    dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-3'B><'col-6 col-md-3'f><'col-3 col-md-1 toolbar_roles'><'col-3 col-md-1 exportDiv_roles'>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
    data: [Array],
    columns: [Array],
    scrollY: '60vh'
},
export_div: 'div.exportDiv_roles',
form_ids: {
    title: 'rolesFormModalTitle',
    button: 'rolesSubmitRecord',
    modal: 'rolesFormModal',
    form: 'rolesForm'
},
error_ids: [ 'roles_error_name' ],
delete_modal_ids: {
    heading: 'roles_confirmDeleteHeading',
    message: 'roles_confirmDeleteMessage',
    confirm: 'roles_confirmDeleteBtn',
    modal: 'roles_confirmDelete'
},
overwrite_modal_ids: { modal: 'duplicateRecord', confirm: 'confirmDuplicateRecordBtn' },
set_form_values: function() {return},
get_form_values: function() {return}
}

// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');
// The datatables plugin attaches itself differently in node.js (see datatables/media/js/jquery.dataTables.js)
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);
require('select2')(jQuery);

describe('Rhombus_Datatable', () => {
    test('Rhombus_Datatable.createColumnSelector', () => {
        jest.resetModules();

        Event.prototype.stopPropagation = jest.fn(() => null);
        Date.prototype.getTime = () => '';

        $.fn.ready = jest.fn((callback) => callback())
    
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
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    columns: () => ({
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
                    }),
                    column: () => ({
                        visible: () => ({
                            draw: () => null
                        })
                    })
            }
        })
        Rhombus_Datatable.setup_add_record();
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.datatable_properties.buttons[1].action();
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
        global.table = $('#table').DataTable();
        const targetDiv = $('#target-div');
        Rhombus_Datatable.createColumnSelector(columnName, table, targetDiv);

        $('#_dropdown').trigger('click');
        $('#_columnCheck0').val('false')
        $('#_columnCheck0').get(0).dispatchEvent(new Event('change'));
        $('#_dropdown_column_selector').trigger('click');

        expect($.fn.ready).toHaveBeenCalled();
    });

    test('Rhombus_Datatable.repositionExportButton', () => {
        jest.resetModules();

        const id = 'element';
        document.body.innerHTML = `
            <div>
                <button class="buttons-excel"></button>
                <div id="${id}"></div>
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        const element = $(`#${id}`);
        Rhombus_Datatable.repositionExportButton(element);

        expect(element.children().length).toBe(1);
    });

    test('Rhombus_Datatable.repositionExportButton no delete', () => {
        jest.resetModules();

        const id = 'element';
        document.body.innerHTML = `
            <div>
                <button class="buttons-excel"></button>
                <div id="${id}"></div>
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(paramsNoDelete);
        Rhombus_Datatable.delete_message({id: "1"})
        const element = $(`#${id}`);
        Rhombus_Datatable.repositionExportButton(element);

        expect(element.children().length).toBe(1);
    });

    test('Rhombus_Datatable.copyToClipboard', () => {
        jest.resetModules();

        require('../global/datatables_features2');

        document.execCommand = jest.fn(() => null);

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        const event = {
            parent: () => {
                return {
                    append: jest.fn(),
                    text: () => ""
                }
            }
        }
        Rhombus_Datatable.init_table();
        window._rb.copyToClipboard(event);
        
        expect(document.execCommand).toHaveBeenCalled();
    });


    test('Rhombus_Datatable.initTooltips', () => {
        jest.resetModules();

        const copyBtnClass = 'copyBtn';
        const tooltipClass = 'tooltip';
        document.body.innerHTML = `
            <div>
                <button class="${copyBtnClass}"></button>
                <button class="${tooltipClass}"></button>
            </div>
        `;

        require('../global/datatables_features2');

        global.setTimeout = (callback) => callback();
        $.fn.tooltip = jest.fn(() => null);
        $.fn.ready = jest.fn((callback) => callback());

        const tooltips = $(`.${tooltipClass}`);
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        window._rb.initTooltips([tooltips]);

        $('.copyBtn').trigger('click');

        expect($.fn.tooltip).toHaveBeenCalledTimes(4);

        // no tooltips
        window._rb.initTooltips(null);

        expect($.fn.tooltip).toHaveBeenCalledTimes(5);
    });

    test('Rhombus_Datatable.addDropdown', () => {
        jest.resetModules();

        const targetId = 'targetId';

        document.body.innerHTML = `
            <div id="${targetId}"></div>
        `;

        require('../global/datatables_features2');

        const list = [
            ['value', 'text']
        ];
        const dropdownCallback = jest.fn(() => null);
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        window._rb.addDropdown(list, $(`#${targetId}`), dropdownCallback);

        $('select').trigger('change');

        expect(dropdownCallback).toHaveBeenCalled();
    });

    test('Rhombus_Datatable edit record', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
    
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
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        const editButton = window._rb.edit_button("testtable");
        Rhombus_Datatable.get_row_data = jest.fn();
        Rhombus_Datatable.edit_record(editButton);

        expect(Rhombus_Datatable.get_row_data).toHaveBeenCalled();
    });

    test('Rhombus_Datatable delete record', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        const deleteButton = window._rb.delete_button("testtable");
        Rhombus_Datatable.get_row_data = jest.fn();
        Rhombus_Datatable.delete_record(deleteButton);

        expect(Rhombus_Datatable.get_row_data).toHaveBeenCalled();
    });

    test('Rhombus_Datatable init delete', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <button id="roles_confirmDeleteBtn" class = 'rowBtn' data-toggle='tooltip' data-placement='top' title='Delete'><i class='far fa-trash-alt'></i></button>
                    </tbody>
                </table>
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.initialize_delete("/facs_manager/delete_facs", false, {'facs_type':'roles'})
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table'
        }
        expect(true).toBe(true);
    });

    test('Rhombus_Datatable init delete add row data', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <button id="roles_confirmDeleteBtn" class = 'rowBtn' data-toggle='tooltip' data-placement='top' title='Delete'><i class='far fa-trash-alt'></i></button>
                    </tbody>
                </table>
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        $.post = (url, post_data, callback) => {
            if(url == ("/facs_manager/delete_facs")) callback({
                result: 'results'
            })
        }
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: jest.fn(),
                    rows: {
                        add: () => {
                            return {
                                draw: jest.fn()
                            }
                        }
                    },
                    clear:  jest.fn(),
                    columns: () => ({
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
                    }),
                    column: () => ({
                        visible: () => ({
                            draw: () => jest.fn()
                        })
                    })
            }
        })
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.initialize_delete("/facs_manager/delete_facs",['data'], {'facs_type':'roles'})
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        $("#roles_confirmDeleteBtn").trigger('click')
        expect(global.action_button).toHaveBeenCalled();
    });

    test('Rhombus_Datatable additional reset', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.resetFormValues(params.form_ids, params.error_ids, params.additional_reset_form);

        expect(global.clear_form).toHaveBeenCalled();
    });
    test('Rhombus_Datatable init overwrite', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <button id="confirmDuplicateRecordBtn" class = 'rowBtn' data-toggle='tooltip' data-placement='top' title='Edit'><i class='far fa-trash-alt'></i></button>
                    </tbody>
                </table>
                '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        $.post = (url, post_data, callback) => {
            if(url == ("/facs_manager/edit_facs")) callback({
                result: 'success'
            })
        }
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: jest.fn(),
                    rows: {
                        add: () => {
                            return {
                                draw: jest.fn()
                            }
                        }
                    },
                    clear:  jest.fn(),
                    columns: () => ({
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
                    }),
                    column: () => ({
                        visible: () => ({
                            draw: () => jest.fn()
                        })
                    })
            }
        })
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.initialize_overwrite("/facs_manager/edit_facs")
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        $("#confirmDuplicateRecordBtn").trigger('click')
        expect(global.action_button).toHaveBeenCalled();
    });

    test('Rhombus_Datatable init submit check unique data', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <form id="rolesForm" type="submit">
                <button id="rolesSubmitRecord" class = 'rowBtn' data-toggle='tooltip' value="editData" data-placement='top' title='Edit'>
                <i class='far fa-trash-alt'></i>
                </button>
            </form>
            <div id="rolesFormModal>
            </div>
            '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        $.post = (url, post_data, callback) => {
            if(url == ("/facs_manager/add_facs")) callback({
                result: 'success'
            })
        }
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: jest.fn(),
                    rows: {
                        add: () => {
                            return {
                                draw: jest.fn()
                            }
                        }
                    },
                    clear:  jest.fn(),
                    columns: () => ({
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
                    }),
                    column: () => ({
                        visible: () => ({
                            draw: () => jest.fn()
                        })
                    })
            }
        })
        const check_func = jest.fn(() => true);
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_func , ['data'], {'facs_type':'role_mappings'})
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        $("#rolesForm").trigger('submit')
        $("#rolesSubmitRecord").trigger('click')
        expect(global.action_button).toHaveBeenCalled();
    });

    test('Rhombus_Datatable init submit post', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <form id="rolesForm" type="submit">
                <button id="rolesSubmitRecord" class = 'rowBtn' data-toggle='tooltip' value="addData" data-placement='top' title='Edit'>
                <i class='far fa-trash-alt'></i>
                </button>
            </form>
            <div id="rolesFormModal>
            </div>
            '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        $.post = (url, post_data, callback) => {
            if(url == ("/facs_manager/add_facs")) callback({
                result: 'success',
                validation: 'success'
            })
        }
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: jest.fn(),
                    rows: {
                        add: () => {
                            return {
                                draw: jest.fn()
                            }
                        }
                    },
                    clear:  jest.fn(),
                    columns: () => ({
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
                    }),
                    column: () => ({
                        visible: () => ({
                            draw: () => jest.fn()
                        })
                    })
            }
        })
        const check_func = jest.fn(() => false);
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_func , ['data'], {'facs_type':'role_mappings'})
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        $("#rolesForm").trigger('submit')
        $("#rolesSubmitRecord").trigger('click')
        expect(global.action_button).toHaveBeenCalled();
    });
    test('Rhombus_Datatable init submit show error message', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <form id="rolesForm" type="submit">
                <button id="rolesSubmitRecord" class = 'rowBtn' data-toggle='tooltip' value="addData" data-placement='top' title='Edit'>
                <i class='far fa-trash-alt'></i>
                </button>
            </form>
            <div id="rolesFormModal>
            </div>
            '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        $.post = (url, post_data, callback) => {
            if(url == ("/facs_manager/add_facs")) callback({
                result: 'success',
                validation: 'fail',
                errors: {
                    "roles_error_name": "ERROR"
                }
            })
        }
        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        // const check_func = jest.fn(() => false);
        Rhombus_Datatable.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs")
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        $("#rolesForm").trigger('submit')
        $("#rolesSubmitRecord").trigger('click')
        expect(global.action_button).toHaveBeenCalled();
    });
    test('Rhombus_Datatable check_record_exist no result', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <form id="rolesForm" type="submit">
                <button id="rolesSubmitRecord" class = 'rowBtn' data-toggle='tooltip' value="addData" data-placement='top' title='Edit'>
                <i class='far fa-trash-alt'></i>
                </button>
            </form>
            <div id="rolesFormModal>
            </div>
            '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: () => {
                        return {
                            data: () => false
                        }
                    },
                    rows: () => {
                        return {
                            indexes: jest.fn(() => {
                                const indices = ['1']
                                Array.prototype.any = jest.fn()
                                return indices
                            })
                        }
                    }
            }
        })
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        Rhombus_Datatable.check_record_exist(global.custom_check)
        expect(global.custom_check).toHaveBeenCalled();
    });

    test('Rhombus_Datatable check_record_exist', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
        document.body.innerHTML = `
            <div id="target-div"></div>
            <div>
            <form id="rolesForm" type="submit">
                <button id="rolesSubmitRecord" class = 'rowBtn' data-toggle='tooltip' value="addData" data-placement='top' title='Edit'>
                <i class='far fa-trash-alt'></i>
                </button>
            </form>
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: () => {
                        return {
                            data: () => {
                                return {
                                    any: jest.fn()
                                }
                            }
                        }
                    },
                    rows: () => {
                        return {
                            indexes: jest.fn(() => {
                                const indices = ['1']
                                Array.prototype.any = jest.fn()
                                return indices
                            })
                        }
                    },
                    clear:  jest.fn(),
                    columns: () => ({
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
                    }),
                    column: () => ({
                        visible: () => ({
                            draw: () => jest.fn()
                        })
                    })
            }
        })
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        Rhombus_Datatable.check_record_exist(global.custom_check)
        expect(global.custom_check).toHaveBeenCalled();
    });

    test('Rhombus_Datatable get row data', () => {
        jest.resetModules();
        $.fn.ready = jest.fn((callback) => callback())
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
            <form id="rolesForm" type="submit">
            <button id="rolesSubmitRecord" class = 'rowBtn' data-toggle='tooltip' value="addData" data-placement='top' title='Edit'>
                <tr>
                <td>data0</td>
                <td>data1</td>
                </tr>
                <i class='far fa-trash-alt'></i>
                </button>
            </form>
            </tbody>
            </table>
            <div id="rolesFormModal>
            </div>
            '<ul class="dropdown-menu keep-open" id="_dropdown_column_selector"><li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">id<input id="_columnCheck0" type="checkbox" aria-label="Checkbox for following text input" value="id"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">name<input id="_columnCheck1" type="checkbox" aria-label="Checkbox for following text input" value="name"><span class="checkmark mr-2"><span></span></span></label></li><li class="columnSelect"><label class="checkboxcontainer ml-2 ">status<input id="_columnCheck2" type="checkbox" aria-label="Checkbox for following text input" value="status"><span class="checkmark mr-2"><span></span></span></label></li></ul>'
            </div>
        `;

        require('../global/datatables_features2');

        const Rhombus_Datatable = new window._rb.Rhombus_Datatable(params);
        Rhombus_Datatable.init_table = jest.fn(() => {
            Rhombus_Datatable.data_table =  { 
                    row: (tr) => {
                        return {
                            data: jest.fn()
                        }
                    }
            }
        })
        Rhombus_Datatable.init_table();
        Rhombus_Datatable.selectedRowData = {
            'id': 'roles_table',
            'row_data_name': 'data'
        }
        let button = $("#rolesSubmitRecord").html()
        Object.prototype.closest = jest.fn(() => {
            return {
                hasClass: jest.fn(),
                prev: jest.fn()
            }
        })
        Rhombus_Datatable.get_row_data(button)
        expect(button.closest).toHaveBeenCalled();
    });
});
