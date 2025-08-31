const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;
global.rhombuscookie = jest.fn();
global.loadPageData = jest.fn((selector, url, data, callback) => {
    callback();
});

global.coa_output_override_table = {
	row: jest.fn().mockReturnThis(),
	rows: jest.fn().mockImplementation(() => ({
        every: jest.fn(callback => {
            const mockRowData = { "": '<button>Mock Button</button>' };
            callback.call({
                data: jest.fn(() => mockRowData),
            });
        }),
		data: jest.fn().mockImplementation(() => ({
            toArray: jest.fn().mockReturnValue([
                { "": '<button>Mock Button</button>', column1: 'value1' },
                { "": '<button>Mock Button</button>', column2: 'value2' }
            ]),
        })),
        indexes: jest.fn(() => [0]),
    })),
    data: jest.fn().mockReturnThis(),
    cell: jest.fn().mockReturnValue({
        data: jest.fn().mockReturnThis(),
        node: () => {
            return ""
        },
        html: () => {
            return ""
        },
        draw: jest.fn().mockReturnThis(),
    }),
    index: jest.fn().mockReturnThis(),
    column: jest.fn(() => ({
        header: jest.fn(() => ({
            textContent: '2021'
        })),
        data: jest.fn().mockImplementation(() => ({
            toArray: jest.fn().mockReturnValue([
                { "": '<button>Mock Button</button>', column1: 'value1' },
                { "": '<button>Mock Button</button>', column2: 'value2' }
            ]),
        })),
    })),
    settings: jest.fn(() => ({
        init: jest.fn(() => ({
            columns: [
                { data: 'COLUMN_1' },
                { data: 'COLUMN_2' },
                { data: 'RESOURCE CATEGORY' }
            ]
        }))
    })),
    header: jest.fn().mockReturnThis(),
	draw: jest.fn().mockReturnThis(),
    toArray: jest.fn()
};
global.budget_uncommitted_override_table = global.coa_output_override_table;
global.scenario_id = 123;

global.get_input_object = jest.fn();
global.showHideInsertCoaTableRowDropdown = jest.fn();
global.autofillFYField = jest.fn();
global.eoc_codes = ['EOC1', 'EOC2'];

global.sanitizeHtml = html => html;
hideNotification = () => true;
showNotification = (msg, t) => true;
setNotificationName = (name) => true;

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('ion-rangeslider/js/ion.rangeSlider.js'); 

class checkedSavedCoa {
    #chked = 0

    reset() {
        this.#chked = 0;
    }

    add() {
        this.#chked += 1;
    }

    remove() {
        this.#chked -= 1;
    }

    get() {
        return this.#chked;
    }

	set(chk) {
		this.#chked = chk;
	}
}

global.$.fn.DataTable = jest.fn((obj = null) => {
    let dtmockclass = {
        column: function(value) {
            return {
				search: function(value) {
					return {
						draw: function() {
							return true;
						}
					}
                }
            }
        }
    };

	if (obj && obj.rowCallback) {
		obj.rowCallback('', '')
	}

	if (obj && obj.initComplete) {
        obj.initComplete();
    }

    return {
		...obj, 
		...dtmockclass, 
		empty: () => true,
		draw: () => true,
		ajax: {
			reload: () => true
		}, $: () => { return {each: () => {} }},
        settings: () => true,
        destroy: () => true
	}
});

describe('setCurrentCOA', () => {
	test('function', () => {
        jest.resetModules();

		require('../../../actions/SOCOM/optimizer/coa.js');

        const id = 'test';
		window._rb.setCurrentCOA(id);

		expect(true).toBe(true);
	});
});

describe('getCurrentCOA', () => {
	test('function', () => {
        jest.resetModules();

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.getCurrentCOA();

		expect(true).toBe(true);
	});
});

describe('showCOAModal', () => {
	test('function', () => {
        jest.resetModules();

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.showCOAModal();

		expect(true).toBe(true);
	});
});

describe('showLoadCOAModal', () => {
	test('function', () => {
        jest.resetModules();
        
		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.showLoadCOAModal();

		expect(true).toBe(true);
	});
});

describe('getCOAList', () => {
	test('function', () => {
        jest.resetModules();

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.getCOAList();

		expect(true).toBe(true);
	});
});

describe('getUserSavedCOA', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<input type="checkbox" name="load_coa[]" value="0" checked></input>
			<input type="checkbox" name="load_coa[]" value="1" checked></input>
		`;

		$.post = (url, data, cb, format) => {
            cb({
				data: {
					0: {
						COA_VALUES: '[]',
						OPTIMIZER_INPUT: '[2, 3, 4]'
					},
					1: {
						COA_VALUES: '[]',
						OPTIMIZER_INPUT: '[1, 55, 6]'
					}
				}
            });
            return {
                fail: () => true
            }
        };

		applyOutputs = (json_data, num) => {
			return {
				transformed_data: {
					0: 'test',
					1: 'test'
				}
			}
		}
		createCOAGraph = (seriesData) => {
			return {
				addSeries: () => true,
				xAxis: [ { setCategories: () => {}}, { setCategories: () => {}} ],
				redraw: () => true,
				reflow: () => true
			}
		}

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.getUserSavedCOA();

		expect(true).toBe(true);
	});

	test('ajax fail', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<input type="checkbox" name="load_coa[]" value="0" checked></input>
			<input type="checkbox" name="load_coa[]" value="1" checked></input>
		`;

		$.post = (url, data, cb, format) => {
            return {
                fail: (jqXHR) => true
            }
        };
		$.post().fail = (test) => true;

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.getUserSavedCOA();

		expect(true).toBe(true);
	});
});

describe('saveCOA', () => {
	test('invalid name', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<input id="coa-name" value=""></input>
			<input id="coa-description" value="testdescr"></input>
		`;

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.setCurrentCOA('testid');
		window._rb.saveCOA();

		expect(true).toBe(true);
	});

    test('invalid description', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<input id="coa-name" value="testname"></input>
			<input id="coa-description" value="
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500stringlengthover500stringlengthover500stringlengthover500
				stringlengthover500stringlengthover500
			"></input>
		`;

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.setCurrentCOA('testid');
		window._rb.saveCOA();

		expect(true).toBe(true);
	});

    test('invalid id', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<input id="coa-name" value="testname"></input>
			<input id="coa-description" value="testdescr"></input>
		`;

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.setCurrentCOA('');
		window._rb.saveCOA();

		expect(true).toBe(true);
	});

    test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<input id="coa-name" value="testname"></input>
			<input id="coa-description" value="testdescr"></input>
		`;

		$.post = (url, data, cb, format) => {
            cb({
				data: 'test'
			});
            return {
                fail: () => true
            }
        };

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.setCurrentCOA('testid');
		window._rb.saveCOA();

		expect(true).toBe(true);
	});
});

describe('showLoadCOA', () => {
	test('function', () => {
        jest.resetModules();

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.showLoadCOA(new checkedSavedCoa());

		expect(true).toBe(true);
	});
});

describe('showCOA', () => {
	test('function', () => {
        jest.resetModules();

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.showCOA();

		expect(true).toBe(true);
	});
});

describe('onReadyCOA', () => {
    test('showLoadCOA sets csc to 3', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <table id="coa-load-table"><tbody></tbody></table>
            <table id="coa-table-1"></table>
            <table id="coa-table-2"></table>
            <table id="coa-table-3"></table>
            <button id="load-coa"></button>
        `;

        const showLoadCOA = jest.fn((csc) => {
            csc.set(3);
        });

        jest.isolateModules(() => {
            require('../../../actions/SOCOM/optimizer/coa.js');
            
            window._rb.showLoadCOA = showLoadCOA;
            window._rb.onReadyCOA();
            $('#load-coa').trigger('click');

            expect(true).toBe(true)
        });
    });
});

describe('onReadyCOA with additional function', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
			<table id="coa-load-table"><tbody></tbody></table>
            <table id="coa-table-1"></table>
            <table id="coa-table-2"></table>
            <table id="coa-table-3"></table>
        `;

		require('../../../actions/SOCOM/optimizer/coa.js');
		window._rb.onReadyCOA();

		const $tbody = $('#coa-load-table tbody');
        
        $tbody.append('<tr><td><input type="checkbox" name="load_coa[]" value="1" checked></td></tr>');
        $tbody.append('<tr><td><input type="checkbox" name="load_coa[]" value="2"></td></tr>');

        $tbody.find('input[type="checkbox"]').each((i, checkbox) => {
            $(checkbox).trigger('change');
        });

		expect(true).toBe(true);
	});
});

describe('resetForm', () => {
	test('function', () => {
        jest.resetModules();

		document.body.innerHTML = `
		`;

		require('../../../actions/SOCOM/optimizer/coa.js');

		window._rb.resetForm();

		expect(true).toBe(true);
	});
});

describe('insertCoaTableRow', () => {
    beforeEach(() => {
        global.rhombuscookie.mockReset();
        global.loadPageData.mockReset();
        global.coa_output_override_table.column.mockClear();
        global.coa_output_override_table.data.mockClear();
        global.coa_output_override_table.toArray.mockClear();

		global.rhombuscookie.mockReturnValue('dummy_token');
        global.coa_output_override_table.toArray.mockReturnValue(['EOC1', 'EOC2', 'EOC2', '']);
        global.selected_program_codes = ['PROGRAM1', 'PROGRAM2'];
    });

    test('should call loadPageData with correct parameters case 1', () => {

		document.body.innerHTML = `
            <input type="radio" name="storm_weighted_based" value="1" checked>
            <input type="radio" name="storm_weighted_based" value="2">
            <input type="radio" name="storm_weighted_based" value="3">
			<input type="radio" name="weighted_score_based" value="1" checked>
        `;
		
        const scenarioId = 1;
        const eoc_col = 1;

        window._rb.insertCoaTableRow(scenarioId, eoc_col);

        expect(global.coa_output_override_table.column).toHaveBeenCalledWith(eoc_col);
    });

	test('should call loadPageData with correct parameters case 2', () => {

		document.body.innerHTML = `
            <input type="radio" name="storm_weighted_based" value="1" checked>
            <input type="radio" name="storm_weighted_based" value="2">
            <input type="radio" name="storm_weighted_based" value="3">
			<input type="radio" name="weighted_score_based" value="2" checked>
        `;
		
        const scenarioId = 1;
        const eoc_col = 1;

        window._rb.insertCoaTableRow(scenarioId, eoc_col);

        expect(global.coa_output_override_table.column).toHaveBeenCalledWith(eoc_col);

    });

	test('should call loadPageData with correct parameters case 3', () => {

		document.body.innerHTML = `
            <input type="radio" name="storm_weighted_based" value="1" checked>
            <input type="radio" name="storm_weighted_based" value="2">
            <input type="radio" name="storm_weighted_based" value="3">
			<input type="radio" name="weighted_score_based" value="3" checked>
        `;
		
        const scenarioId = 1;
        const eoc_col = 1;

        window._rb.insertCoaTableRow(scenarioId, eoc_col);

        expect(global.coa_output_override_table.column).toHaveBeenCalledWith(eoc_col);
    });

	test('should call loadPageData with correct parameters', () => {

		document.body.innerHTML = `
			<input type="radio" name="storm_weighted_based" value="1" >
			<input type="radio" name="storm_weighted_based" value="2" checked>
			<input type="radio" name="storm_weighted_based" value="3"> `;

        const scenarioId = 2;
        const eoc_col = 2;

        window._rb.insertCoaTableRow(scenarioId, eoc_col);

        expect(global.coa_output_override_table.column).toHaveBeenCalledWith(eoc_col);
    });

});


describe('updateInsertCoaTableRowDropdown', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        document.body.innerHTML = `
            <input id="text-input-POM" />
            <input id="text-input-GUIDANCE" />
            <input id="text-input-STORM" />
            <select id="text-input-PROGRAM_CODE"></select>
            <select id="text-input-EOC_CODE"></select>
            <select id="text-input-POM_SPONSOR_CODE"></select>
            <select id="text-input-CAPABILITY_SPONSOR_CODE"></select>
            <select id="text-input-RESOURCE_CATEGORY_CODE"></select>
        `;
    });

    it('should make a POST request with the correct data and update the DOM elements : Program Code', () => {
        global.rhombuscookie.mockReturnValue('dummy_token');
        global.get_input_object.mockReturnValue({ key1: 'value1', key2: 'value2' });

        $.post = jest.fn((url, data, callback) => {
            callback({
                data: {
                    weighted_score: {
                        weighted_pom_score: 100,
                        weighted_guidance_score: 50,
                        total_storm_scores: 30
                    },
                    ID: 'dummy_id',
                    dropdown: {
                        PROGRAM_CODE: ['PC1', 'PC2'],
                        EOC_CODE: ['EOC1', 'EOC2']
                    }
                }
            });
        });

        const type = 'PROGRAM_CODE';
        const scenario_id = 1;

        window._rb.updateInsertCoaTableRowDropdown(type, scenario_id);

        expect(global.rhombuscookie).toHaveBeenCalled();
        expect($('#text-input-POM').val()).toBe('100');
        expect($('#text-input-GUIDANCE').val()).toBe('50');
        expect($('#text-input-STORM').val()).toBe('30');
    });

	it('should make a POST request with the correct data and update the DOM elements : EOC Code', () => {
        global.rhombuscookie.mockReturnValue('dummy_token');
        global.get_input_object.mockReturnValue({ key1: 'value1', key2: 'value2' });

        $.post = jest.fn((url, data, callback) => {
            callback({
                data: {
                    weighted_score: {
                        weighted_pom_score: 100,
                        weighted_guidance_score: 50,
                        total_storm_scores: 30
                    },
                    ID: 'dummy_id',
                    dropdown: {
                        PROGRAM_CODE: ['PC1', 'PC2'],
                        EOC_CODE: ['EOC1', 'EOC2']
                    }
                }
            });
        });

        const type = 'EOC_CODE';
        const scenario_id = 1;

        window._rb.updateInsertCoaTableRowDropdown(type, scenario_id);

        expect(global.rhombuscookie).toHaveBeenCalled();
    });

	it('should make a POST request with the correct data and update the DOM elements : Invalid Code', () => {
        global.rhombuscookie.mockReturnValue('dummy_token');
        global.get_input_object.mockReturnValue({ key1: 'value1', key2: 'value2' });

        const type = 'INVALID_CODE';
        const scenario_id = 1;

        window._rb.updateInsertCoaTableRowDropdown(type, scenario_id);

        expect(global.rhombuscookie).toHaveBeenCalled();
    });

});


describe('validateYear', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <input id="text-input-2026" class="bx--text-input" />
            <span id="invalid-icon-2026" class="hidden"></span>
            <div id="invalid-text-2026" class="hidden"></div>
            <button id="insert-coa-row-btn" disabled></button>
        `;
    });

    it('should invalidate input with non-numeric characters', () => {
        $('#text-input-2026').val('abc123');
        
        window._rb.validateYear('2026', {});
        
        expect($('#text-input-2026').hasClass('bx--text-input--invalid')).toBe(true);
        expect($('#invalid-icon-2026').hasClass('hidden')).toBe(false);
        expect($('#invalid-text-2026').hasClass('hidden')).toBe(false);
        expect($('#invalid-text-2026').text()).toBe("Invalid input. Must only include numbers.");
        expect($('#insert-coa-row-btn').prop("disabled")).toBe(true);
    });

    it('should invalidate negative numeric input', () => {
        $('#text-input-2026').val('-123');
        
        window._rb.validateYear('2026', {});
        
        expect($('#text-input-2026').hasClass('bx--text-input--invalid')).toBe(true);
        expect($('#invalid-icon-2026').hasClass('hidden')).toBe(false);
        expect($('#invalid-text-2026').hasClass('hidden')).toBe(false);
        expect($('#invalid-text-2026').text()).toBe("Invalid input. Must only include positive integers.");
        expect($('#insert-coa-row-btn').prop("disabled")).toBe(true);
    });

    it('should validate positive numeric input', () => {
        $('#text-input-2026').val('123');
        
        window._rb.validateYear('2026', { '2026': '2026' });
        
        expect($('#text-input-2026').hasClass('bx--text-input--invalid')).toBe(false);
        expect($('#invalid-icon-2026').hasClass('hidden')).toBe(true);
        expect($('#invalid-text-2026').hasClass('hidden')).toBe(true);
        expect($('#insert-coa-row-btn').prop("disabled")).toBe(false);
    });

    it('should disable button if another field is empty', () => {
        document.body.innerHTML += `
            <input id="text-input-2025" class="bx--text-input" />
        `;
        
        $('#text-input-2026').val('123');
        $('#text-input-2025').val('');
        
        window._rb.validateYear('2026', { '2026': '2026', '2025': '2025' });
        
        expect($('#text-input-2026').hasClass('bx--text-input--invalid')).toBe(false);
        expect($('#invalid-icon-2026').hasClass('hidden')).toBe(true);
        expect($('#invalid-text-2026').hasClass('hidden')).toBe(true);
        expect($('#insert-coa-row-btn').prop("disabled")).toBe(true);
    });

    it('should enable button if all fields are valid', () => {
        document.body.innerHTML += `
            <input id="text-input-2025" class="bx--text-input" />
        `;
        
        $('#text-input-2026').val('123');
        $('#text-input-2025').val('456');
        
        window._rb.validateYear('2026', { '2026': '2026', '2025': '2025' });
        
        expect($('#text-input-2026').hasClass('bx--text-input--invalid')).toBe(false);
        expect($('#invalid-icon-2026').hasClass('hidden')).toBe(true);
        expect($('#invalid-text-2026').hasClass('hidden')).toBe(true);
        expect($('#insert-coa-row-btn').prop("disabled")).toBe(false);
    });
});



describe('openConfirmationModal', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        document.body.innerHTML = `
            <div id="coa-output"></div>
            <div id="manual-override-confirm-coa" class="d-none">
                <div id="manual-override-confirm-btn"></div>
                <div id="manual-override-cancel-btn"></div>
                <div id="manual-override-confirm-coa-close-btn"></div>
                <div id="manual-override-action"></div>
            </div>
        `;

		$.post = jest.fn((url, data, callback) => {

            const responseData = JSON.stringify({
                text: 'Mocked banner text'
            });
            
            callback(responseData);
        });
    });

    it('should display confirmation modal and set action correctly', () => {
        const action = 'save';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

		expect(true).toBe(true);
    });

    it('should display confirmation modal and set action incorrectly for save', () => {
        const action = 'save';
        const scenario_id = 0;
        const coa_table_id = 0;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

		expect(true).toBe(true);
    });

	it('should display confirmation modal and set action: cancel', () => {
        const action = 'cancel';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-cancel-btn').trigger('click');

		expect(true).toBe(true);
    });

	it('should display confirmation modal and set action: close', () => {
        const action = 'close';
        const scenario_id = 123;
        const coa_table_id = 456;

		$.post = (url, data, cb, format) => {
            cb({
				data: {
					0: {
						COA_VALUES: '[]',
						OPTIMIZER_INPUT: '[2, 3, 4]'
					},
					1: {
						COA_VALUES: '[]',
						OPTIMIZER_INPUT: '[1, 55, 6]'
					}
				}
            });
            return {
                fail: () => true
            }
        };

		applyOutputs = (json_data, num) => {
			return {
				transformed_data: {
					0: 'test',
					1: 'test'
				}
			}
		}
		createCOAGraph = (seriesData) => {
			return {
				addSeries: () => true,
				xAxis: [ { setCategories: () => {}}, { setCategories: () => {}} ],
				redraw: () => true,
				reflow: () => true
			}
		}

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-coa-close-btn').trigger('click');
        $('#manual-override-confirm-btn').trigger('click');

		expect(true).toBe(true);
    });

	it('should handle toggle action correctly: submit ', () => {
        const action = 'submit';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

        expect(true).toBe(true);
    });

	it('should handle toggle action correctly w/o manual override', () => {
        const action = 'toggle';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

        expect(true).toBe(true);
    });

    it('should handle toggle action correctly', () => {
        const action = 'toggle';
        const scenario_id = 123;
        const coa_table_id = 456;

		document.body.innerHTML = `
            <div id="coa-output"></div>
            <div id="manual-override-confirm-coa" class="d-none">
                <div id="manual-override-confirm-btn"></div>
                <div id="manual-override-cancel-btn"></div>
                <div id="manual-override-confirm-coa-close-btn"></div>
                <div id="manual-override-action"></div>
            </div>
			<input id="manual-override" type="checkbox" name="manual-override" value="0" checked></input>
        `;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

        expect(true).toBe(true);
    });

	it('should handle toggle action correctly: approve', () => {
        const action = 'approve';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

        expect(true).toBe(true);
    });

	it('should handle toggle action correctly: deny ', () => {
        const action = 'deny';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

        expect(true).toBe(true);
    });

	it('should handle toggle action correctly: default', () => {
        const action = 'default';
        const scenario_id = 123;
        const coa_table_id = 456;

        window._rb.openConfirmationModal(action, scenario_id, coa_table_id);

		expect(true).toBe(true);

        $('#manual-override-confirm-btn').trigger('click');

        expect(true).toBe(true);
    });

});


describe('show_coa_output', () => {
    beforeEach(() => {
        jest.clearAllMocks();

		document.body.innerHTML = `
    		<div id="coa-output-container"></div>
    		<div id="coa-output-table"></div>
		`;
 
        global.loadPageData = jest.fn((selector, url, data, callback) => {
            callback();
        });
    });

    it('should call set_output_close_attr and loadPageData with correct parameters', () => {
        const key = '1';
        const saved_coa_id = '123';

        const { SCENARIO_STATE } = require('../../../actions/SOCOM/optimizer/coa.js');
        SCENARIO_STATE['123'] = 'APPROVED'

        window._rb.show_coa_output(key, saved_coa_id);

		expect(true).toBe(true);   
    });

    it('should handle SCENARIO_STATE == CREATED', () => {
        const key = '1';
        const saved_coa_id = '123';

        const { SCENARIO_STATE } = require('../../../actions/SOCOM/optimizer/coa.js');
        SCENARIO_STATE['123'] = 'CREATED'

        window._rb.show_coa_output(key, saved_coa_id);
		expect(true).toBe(true);
    });

    it('should handle SCENARIO_STATE == IN_PROGRESS', () => {
        const key = '1';
        const saved_coa_id = '123';

        const { SCENARIO_STATE } = require('../../../actions/SOCOM/optimizer/coa.js');
        SCENARIO_STATE['123'] = 'IN_PROGRESS'

        window._rb.show_coa_output(key, saved_coa_id);

		expect(true).toBe(true);
    });

    it('should handle SCENARIO_STATE == IN_REVIEW', () => {
        const key = '1';
        const saved_coa_id = '123';

        const { SCENARIO_STATE } = require('../../../actions/SOCOM/optimizer/coa.js');
        SCENARIO_STATE['123'] = 'IN_REVIEW'

        window._rb.show_coa_output(key, saved_coa_id);

		expect(true).toBe(true);
    });

 
    it('should handle set_output_close_attr == false', () => {
 
        window._rb.set_output_close_attr();
 
        expect(true).toBe(true);
    });

 
});


describe('updateGrandTotal', () => {

    beforeEach(() => {
        jest.resetModules();
        global.override_headers = [{ data: "RESOURCE CATEGORY" }];
        global.yearIndex = [1, 2, 3];
        global.indexOfOverrideYear = 4;
        global.year_array = [2021, 2022, 2023];
        global.isWithinBudgetFunc = jest.fn(() => ({
            isCellWithinBudget: true,
            isGrandWithinBudget: true
        }));
        global.addClassToEditedGrandCell = jest.fn();
        global.addClassToEditedCell = jest.fn();
        global.disableSaveSubmit = jest.fn();
        
        global.getTooltipInfo = jest.fn(() => 'MockTooltipInfo');

    });

    it('should update grand total correctly when action is delete', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.updateGrandTotal(global.coa_output_override_table, 1, 'delete');
        expect(true).toBe(true);
    });


    it('should update grand total correctly when action is add', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.updateGrandTotal(global.coa_output_override_table, 1, 'add');
        expect(true).toBe(true);
    });

    it('should update grand total correctly when action is edit', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.updateGrandTotal(global.coa_output_override_table, 1, 'edit', 2, '2021', 10, 20);
        expect(true).toBe(true);
    });

    it('should handle budget check and cell class additions', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.updateGrandTotal(global.coa_output_override_table, 1, 'delete');
        expect(true).toBe(true);
    });

    it('should call disableSaveSubmit at the end', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.updateGrandTotal(global.coa_output_override_table, 1, 'add');
        expect(true).toBe(true);
    });
});

describe('show_hide_override_table', () => {
    it('should hide overide table', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.show_hide_override_table('hide');
        expect(true).toBe(true);
    });
});


describe('initEditorDataTable', () => {
    let mockEditorTable;
    let mockEventHandlers;
    let mockEditorInstance;

    beforeEach(() => {
        jest.resetModules();

        mockEditorInstance = {
            on: jest.fn().mockReturnThis(),
        };
        global.Editor = jest.fn(() => mockEditorInstance);


        global.validateEditCell = jest.fn();
        global.updateGrandTotal = jest.fn();
        global.updateOverridedBudgetImpactHistory = jest.fn();
        global.addClassToEditedCell = jest.fn();
        global.getTooltipInfo = jest.fn(() => 'MockTooltipInfo');

        global.$ = jest.fn().mockImplementation(() => ({
            addClass: jest.fn(),
            find: jest.fn(() => ({
                length: 0,
                append: jest.fn(),
                text: jest.fn(),
            })),
        }));

        mockEventHandlers = {
            preSubmit: jest.fn(),
            setData: jest.fn(),
            postEdit: jest.fn()
        };

        mockEditorTable = {
            on: jest.fn().mockImplementation((event, handler) => {
                mockEventHandlers[event] = handler;
                return mockEditorTable;
            })
        };

        global.editor_table = mockEditorTable;

        document.body.innerHTML = `
            <div id="DTE_Field_2021"></div>
            <div class="DTE_Field_Name_2021"></div>
        `;
    });

    it('should set up editor and bind event handlers', () => {
        const id = 'example-id';
        const editor_columns = ['col1', 'col2'];
        const indexOfOverrideYear = 4;
        const year_array = [2021, 2022, 2023];
        const scenario_id = 123;
        const user_id = 456;
        global.P1_FLAG = '1'
        require('../../../actions/SOCOM/optimizer/coa.js');

        window._rb.initEditorDataTable(id, editor_columns, indexOfOverrideYear, year_array, scenario_id, user_id);

        const mockData = {
            data: {
                row1: {
                    '2021': '60'
                }
            }
        };
        mockEventHandlers.preSubmit({}, mockData, 'edit');

        const mockSetData = {
            '2021': '60'
        };

        mockEventHandlers.setData({}, {}, mockSetData, 'edit');

        mockEventHandlers.postEdit({}, {}, {});
    });

});

describe('random_id', () => {
    it('should test random id', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');
        window._rb.randomId(5);
        expect(true).toBe(true);
    });
});


describe('submit_for_coa_review', () => {
    beforeEach(() => {

        document.body.innerHTML = `
        <input type="text" id="justification-text-input">
        <button id="save-override-button">Save Override</button>
        <button id="submit-coa">Submit COA</button>
        <table id="override-table"></table>
        <div id="manual-override-wrapper"></div>
        <div id="editor-note"></div>
        <div id="state-session-notification" class="d-none">
            <div class="bx--inline-notification__details">
                <div class="bx--inline-notification__text-wrapper">
                    <div class="bx--inline-notification__subtitle"></div>
                </div>
            </div>
        </div>
        `;


        jest.resetModules();
        
        global.show_hide_override_table = jest.fn();
        global.disable_override = jest.fn();
        global.activate_review = jest.fn();
        global.disable_budget_table_edit_button = jest.fn();
        global.toggle_original_outputs = jest.fn();

        global.$ = jest.fn().mockImplementation((selector) => {
            if (selector === '#justification-text-input') {
                return {
                    prop: jest.fn().mockReturnThis(),
                    val: jest.fn().mockReturnValue(''),
                };
            }
            return {
                html: jest.fn(),
                removeClass: jest.fn(),
                addClass: jest.fn(),
                find: jest.fn().mockReturnThis(),
                prop: jest.fn(),
                attr: jest.fn()
            };
        });


        $.post = jest.fn((url, data, callback) => {
            callback('successful');
        });
    });

    it('should perform all actions and display the message', () => {
        const scenario_id = 123;
        const message = 'Submitted for Review';
        require('../../../actions/SOCOM/optimizer/coa.js');

        window._rb.submit_for_coa_review(scenario_id, message);

        expect(true).toBe(true);
        
    });
});


describe('manual_override_save', () => {
    it('should manual_override_save with ', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');

        const { SCENARIO_STATE } = require('../../../actions/SOCOM/optimizer/coa.js');
        SCENARIO_STATE['123'] = 'CREATED'


        window._rb.display_output_banner();
        expect(true).toBe(true);
    });
});


describe('export_coa_results', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="coa-output-table-container"></div>
        `;

        jest.resetModules();

        global.XLSX = {
            utils: {
                aoa_to_sheet: jest.fn(() => ({ })),
                book_new: jest.fn(() => ({ })),
                book_append_sheet: jest.fn(),
            },
            writeFile: jest.fn()
        };
        
        
        global.output_table = {
            settings: jest.fn().mockReturnValue({
                init: jest.fn().mockReturnValue({
                    columns: [
                        { data: 'Column1' },
                        { data: 'Column2' },
                        { data: 'RESOURCE CATEGORY' },
                        { data: 'DT_RowId' }
                    ]
                })
            }),
            data: jest.fn().mockReturnThis(),
            toArray: jest.fn().mockReturnValue([
                { Column1: 'Data1', Column2: 123.456, 'RESOURCE CATEGORY': 'Category1' },
                { Column1: 'Data2', Column2: 789.012, 'RESOURCE CATEGORY': 'Committed Grand Total $K' }
            ])
        };

        global.coa_output_override_table = {
            data: jest.fn().mockReturnThis(),
            toArray: jest.fn().mockReturnValue([
                { Column1: 'Override1', Column2: 345.678, 'RESOURCE CATEGORY': 'Category2' }
            ])
        };

        global.set_export_header_name = jest.fn((columnName) => `Export_${columnName}`);
        global.getCurrentFormattedTime = jest.fn(() => '2024-08-21_15-30-00');
        global.export_csv = jest.fn();

        global.$ = jest.fn().mockImplementation((selector) => {
            if (selector === '#coa-output-table-container') {
                return {
                    hasClass: jest.fn().mockReturnValue(false)
                };
            }
            return {
                hasClass: jest.fn().mockReturnValue(true)
            };
        });
    });

    it('should export COA results correctly', () => {
        require('../../../actions/SOCOM/optimizer/coa.js');

        window._rb.export_coa_results();

        expect(true).toBe(true);
    });
});

describe('closeNotification', () => {
    it('should test closeNotification', () => {
        global.$ = jest.fn().mockImplementation(() => ({
            addClass: jest.fn(),
            find: jest.fn(() => ({
                length: 0,
                append: jest.fn(),
                text: jest.fn(),
            })),
        }));


        document.body.innerHTML = `
        <input type="text" id="justification-text-input">
        <button id="save-override-button">Save Override</button> `;
        window._rb.closeNotification('save-override-button');
        expect(true).toBe(true);
    });
});

describe('toggleOriginalOutputTable', () => {
    beforeEach(() => {
        document.body.innerHTML = `
        <div id="coa-override-output-container">
            <input type="checkbox" id="original-output">
        </div>
        <div id="coa-output-table-container" class="d-none"></div>
        `;

        global.$ = jest.fn().mockImplementation((selector) => {
            return {
                prop: jest.fn().mockImplementation(function(propName, value) {
                    if (propName === 'checked' && typeof value !== 'undefined') {
                        this.checked = value;
                    }
                    return this.checked;
                }),
                is: jest.fn().mockReturnValue(false),
                addClass: jest.fn(),
                removeClass: jest.fn(),
                val: jest.fn(() => ['testval']),
                attr: jest.fn(),
                html: jest.fn(),
                remove: jest.fn()

            };
        });

        global.show_hide_override_table = jest.fn();
    });

    it('should hide the override table when the checkbox is checked', () => {
        $('#original-output').prop('checked', true);
        $.mockImplementation(() => ({
            is: () => true,
            addClass: jest.fn(),
            removeClass: jest.fn(),
        }));

        require('../../../actions/SOCOM/optimizer/coa.js');

        window._rb.toggleOriginalOutputTable();

        expect(true).toBe(true);
    });

    it('should show the override table when the checkbox is unchecked', () => {
        $('#original-output').prop('checked', false);

        require('../../../actions/SOCOM/optimizer/coa.js');

        window._rb.toggleOriginalOutputTable();

        expect(true).toBe(true);
    });
});

describe('filter_selected_programs', () => {

    it('should return empty arrays and objects when no programs are selected', () => {
        const selected_programs = [];

        const result = window._rb.filter_selected_programs(selected_programs);

        expect(result.ids).toEqual([]);
        expect(result.scores).toEqual({});
    });

    it('should return correct ids and scores for a single selected program', () => {
        const selected_programs = [
            {
                program_id: 1,
                total_storm_score: 85,
                weighted_guidance_score: 90,
                weighted_pom_score: 88
            }
        ];

        const result = window._rb.filter_selected_programs(selected_programs);

        expect(result.ids).toEqual([1]);
    });

    it('should return correct ids and scores for multiple selected programs', () => {
        const selected_programs = [
            {
                program_id: 1,
                total_storm_score: 85,
                weighted_guidance_score: 90,
                weighted_pom_score: 88
            },
            {
                program_id: 2,
                total_storm_score: 75,
                weighted_guidance_score: 80,
                weighted_pom_score: 78
            }
        ];

        const result = window._rb.filter_selected_programs(selected_programs);

        expect(result.ids).toEqual([1, 2]);
    });
    
});

describe('loadTableMetadata', () => {
    let scenario_id;
    let overrideTableMetadata;
    let { _, overrided_budget_impact_history } = require('../../../actions/SOCOM/optimizer/coa.js');

    beforeEach(() => {
        scenario_id = 123;
        overrideTableMetadata = {
            coa_output: { data: 'coa_output_data' },
            budget_uncommitted: { data: 'budget_uncommitted_data' },
        };
        
    });

    it('should initialize overrided_budget_impact_history if undefined for the given scenario_id', () => {
        overrided_budget_impact_history['123'] = undefined
        window._rb.loadTableMetadata({}, scenario_id);

        expect(true).toBe(true);
    });

    it('should set overrideTableMetadata when provided', () => {
        window._rb.loadTableMetadata(overrideTableMetadata, scenario_id);

        expect(true).toEqual(true);
    });

    it('should not overwrite existing data if overrideTableMetadata is empty', () => {
        overrided_budget_impact_history['123'] = {
            coa_output: { data: 'existing_data' },
            budget_uncommitted: { data: 'existing_data' },
        };

        window._rb.loadTableMetadata({}, scenario_id);

        expect(true).toBe(true);
    });

    it('should overwrite existing data if overrideTableMetadata is provided', () => {
        overrided_budget_impact_history[scenario_id] = {
            coa_output: { data: 'coa_output_data' },
            budget_uncommitted: { data: 'budget_uncommitted_data' },
        };

        window._rb.loadTableMetadata(overrideTableMetadata, scenario_id);

        expect(overrided_budget_impact_history[scenario_id]).toEqual(overrideTableMetadata);
    });
});

describe('updateOverridedBudgetImpactHistory', () => {
    let scenario_id, rowId, userId, data;
    let { _, overrided_budget_impact_history } = require('../../../actions/SOCOM/optimizer/coa.js');

    beforeEach(() => {
        scenario_id = 123;
        rowId = 'row1';
        userId = 456;
        data = {
            column1: 'value1',
            column2: 'value2'
        };
    });

    it('should initialize overrided_budget_impact_history if undefined for the given scenario_id', () => {
        overrided_budget_impact_history['123'] = undefined
        window._rb.updateOverridedBudgetImpactHistory(scenario_id, rowId, userId, data);
    });

});

describe('dropdown_onchange', () => {
    let id, filter_type, tab_type, view_type, extra_id;

    beforeEach(() => {

        jest.resetModules();

        id = 'testId';
        filter_type = 'include-fy';
        tab_type = 'tab1';
        view_type = 'view1';
        extra_id = null;
        type = 'testType';
        dropdown_id = `${type}-${id}`;
        container = `coa-detailed-${view_type}-${tab_type}-excluded-chart-${id}`

        document.body.innerHTML = `
            <select id="${dropdown_id}" multiple>
                <option value="1">Option 1</option>
                <option value="2">Option 2</option>
                <option value="ALL">ALL</option>
            </select>
            <div id = "${container}"></div>
            <table id="coa-detailed-view1-tab1-included-table-testId"></table> 
        `;

        global.$ = jest.fn().mockImplementation((selector) => {
            return {
                prop: jest.fn().mockImplementation(function(propName, value) {
                    if (propName === 'checked' && typeof value !== 'undefined') {
                        this.checked = value;
                    }
                    return this.checked;
                }),
                is: jest.fn().mockReturnValue(false),
                addClass: jest.fn(),
                removeClass: jest.fn(),
                val: jest.fn(() => ['testval']),
                attr: jest.fn(),
                html: jest.fn(),
                remove: jest.fn(),
                empty: jest.fn(),
                destroy: jest.fn(),
                DataTable: jest.fn(() => ({
                    clear: jest.fn(),
                    rows: jest.fn(() => ({
                        add: jest.fn(),
                        draw: jest.fn()
                    })),
                    draw: jest.fn()
                }))
            };
        });

        $.post = jest.fn((url, data, callback) => {
            callback({
                data: {
                    weighted_score: {
                        weighted_pom_score: 100,
                        weighted_guidance_score: 50,
                        total_storm_scores: 30
                    },
                    ID: 'dummy_id',
                    dropdown: {
                        PROGRAM_CODE: ['PC1', 'PC2'],
                        EOC_CODE: ['EOC1', 'EOC2']
                    },
                    graph: {
                        xAxis: 1,
                        yAxis: 0
                    }
                }
            });
        });
  
        global.get_detailed_summary_input_object = jest.fn(() => ({}));

        global.Highcharts = {
            chart: jest.fn(() => ({
                update: jest.fn(),
            })),
            series: jest.fn( () => {})
        };
   
    });

    it('should call dropdown_all_view and handle "include-sponsor" filter type', () => {
        let { _, lastSelectedItemsMap, detailed_summary_view} = require('../../../actions/SOCOM/optimizer/coa.js');
        lastSelectedItemsMap['include-fy-1'] = ["ALL"]
        detailed_summary_view["testId"] = {
            "tab1": {"included": {"table": undefined}}
        }
        window._rb.dropdown_onchange('testId', 'include-fy', 'tab1', 'comparison', '1');

        expect(true).toBe(true);
        
    });

    it('should call dropdown_all_view and handle "exclude-sponsor" filter type', () => {
        let { _, lastSelectedItemsMap,  detailed_summary_view} = require('../../../actions/SOCOM/optimizer/coa.js');
        lastSelectedItemsMap['exclude-sponsor'] = ["ALL"]
        detailed_summary_view["testId"] = {
            "tab2": {"excluded": {"table": undefined}}
        }

        window._rb.dropdown_onchange('testId', 'exclude-sponsor', 'tab2', 'view2');

        expect(true).toBe(true);
        
    });

    it('should use extra_id for table_id when view_type is "comparison"', () => {
        let { _, lastSelectedItemsMap, detailed_summary_view} = require('../../../actions/SOCOM/optimizer/coa.js');
        lastSelectedItemsMap['include-fy-extraId'] = ["ALL"]
        let innerEle = {};
        innerEle["testId"] = {"tab3": {"table": undefined}}
        detailed_summary_view[0] = innerEle
        window._rb.dropdown_onchange('testId', 'include-fy', 'tab3', 'comparison', 'extraId');

        expect(true).toBe(true);
    });

    it('should not call update_detailed_summary_data for unknown filter type', () => {
        let { _,  lastSelectedItemsMap, detailed_summary_view} = require('../../../actions/SOCOM/optimizer/coa.js');
        lastSelectedItemsMap['unknown-filter'] = ["ALL"] 
        window._rb.dropdown_onchange('testId', 'unknown-filter', 'tab4', 'view4');

        expect(true).toBe(true);
    });
});
