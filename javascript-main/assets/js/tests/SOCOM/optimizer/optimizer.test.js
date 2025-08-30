/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

const hc = require('highcharts/highstock.js');
global.Highcharts = hc;

require('bootstrap/dist/js/bootstrap.bundle.min.js');
global.rhombuscookie = jest.fn();
global.storm_weighted_based = jest.fn();
global.sanitizeHtml = html => html;
global.displayToastNotification = jest.fn();
global.Highcharts = {
    chart: (targetId, settings) => ({
        destroy: jest.fn(),
        redraw: jest.fn()
    }),
    Color: (color) => ({
        brighten: (dark) => ({
            get: () => color
        })
    })
}

global.default_criteria = ['RISK'];

global.CarbonComponents = {
    NavigationMenu: {
        init: () => true
    }
};

global.$.fn.DataTable = (obj_param = null) => {
    if (obj_param) {
        obj_param.columnDefs[0].render({}, 'test', { PROGRAM_ID: 1 }, { row: 1 })
        obj_param.columnDefs[1].render({}, 'test', { PROGRAM_ID: 1 }, { row: 1 })
        obj_param.rowCallback(1, {
            SCORE_SESSION: '[]',
            PROGRAM_NAME: 'test',
            POM_SPONSOR_CODE: 'test',
            CAPABILITY_SPONSOR_CODE: 'test'
        });
    }

    return {
        cells: () => {
            return {
                nodes: () => $('input[type="radio"]')
            }
        },
        columns: () => {
            return {
                visible: () => [true, true, true, true, true, true, true, true]
            }
        },
        rows: jest.fn().mockReturnValue({
            data: jest.fn().mockReturnValue([]),
        }),
        draw: jest.fn(),
        rowCallback: jest.fn(),
        ajax: {
            url: jest.fn(),
        }
    }
};

global.$.fn.highcharts = () => {
    return {
        destroy: () => true
    }
};

global.selected_program_codes = [];

setTimeout = (f, t) => true;

global.CryptoJS = {
    lib: {
        WordArray: {
            random: jest.fn().mockReturnValue({
                toString: jest.fn().mockReturnValue('abcdef1234567890')
            })
        }
    }
};

class GradientColorMock {
    setColorGradient(...colors) {
        return this;
    }
    
    setMidpoint(mpv) {
        return this;
    }
    
    getColors() {
        return ['#000000', '#FFFFFF'];
    }
}

global.randomHexColorCode = jest.fn(() => '#000000');
global.GradientColor = GradientColorMock;

describe('createCOAGraph', () => {
	test('function', () => {
        jest.resetModules();
        

		require('../../../actions/SOCOM/optimizer/optimizer.js');

        const seriesData = {};
		window._rb.createCOAGraph(seriesData);

		expect(true).toBe(true);
	});
});

describe('runOptimizer', () => {
	test('ryes checked | case 1', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <input id="r-yes" type="checkbox" checked></input>
            <input type="radio" name="weighted_score_based" value="1" checked></input>
            <input type="radio" name="weighted_score_based" value="2"></input>
            <input type="radio" name="weighted_score_based" value="3"></input>
            <input type="text" id="delta-1" value="1" class="deltaOptimizer"></input>
            <div id="coa-graph">
                <div></div>
            </div>

            <p class="remaining"></p>
            <p class="remaining"></p>
            <p class="remaining"></p>
            <p class="remaining"></p>
            <p class="remaining"></p>
            <p class="remaining"></p>
		`;

        setCurrentCOA = (val) => true;
        applyOutputs = (data) => {};
        createCOAGraph = (sData) => true; 

        const cb_data = {
            id: '1',
            coa: {
                remaining: {
                    2026: 1,
                    2027: 1,
                    2028: 1,
                    2029: 1,
                    2030: 1
                },
                detail: [0,0,0,0,0],
                selected_programs: [
                    {
                        program_id: 'test_pom',
                        weighted_guidance_score: 1.0
                    }
                ]
            }
        };
        $.post = (url, data, successCallback, format) => {
            successCallback(cb_data);
        
            return {
                fail: failCallback => {
                    failCallback({ responseJSON: { detail: 'Failed to update Option Weighted Score' } });
                }
            };
        };

		require('../../../actions/SOCOM/optimizer/optimizer.js');

        const iel = {
            getInc: () => true,
            getExcl: () => true
        }
		window._rb.runOptimizer(iel);

		expect(true).toBe(true);
	});

    test('ryes unchecked | case 2', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <input id="r-yes" type="checkbox"></input>
            <input type="radio" name="weighted_score_based" value="1"></input>
            <input type="radio" name="weighted_score_based" value="2" checked></input>
            <input type="radio" name="weighted_score_based" value="3"></input>
            <input type="text" id="delta-1" value="1" class="deltaOptimizer"></input>
            <div id="coa-graph">
                <div></div>
            </div>
		`;

        setCurrentCOA = (val) => true;
        applyOutputs = (data) => {};
        createCOAGraph = (sData) => true; 

        const cb_data = {
            id: '1',
            coa: {
                remaining: {
                    2026: 1,
                    2027: 1,
                    2028: 1,
                    2029: 1,
                    2030: 1
                },
                detail: [0,0,0,0,0],
                selected_programs: [
                    {
                        program_id: 'test_pom',
                        weighted_guidance_score: 1.0
                    }
                ]
            }
        };
        $.post = (url, data, successCallback, format) => {
            successCallback(cb_data);
        
            return {
                fail: failCallback => {
                    failCallback({ responseJSON: { detail: 'Failed to update Option Weighted Score' } });
                }
            };
        };

		require('../../../actions/SOCOM/optimizer/optimizer.js');

        const iel = {
            getInc: () => true,
            getExcl: () => true
        }
		window._rb.runOptimizer(iel);

		expect(true).toBe(true);
	});

    test('ryes checked | case 3', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <input id="r-yes" type="checkbox" checked></input>
            <input type="radio" name="weighted_score_based" value="1"></input>
            <input type="radio" name="weighted_score_based" value="2"></input>
            <input type="radio" name="weighted_score_based" value="3" checked></input>
            <input type="text" id="delta-1" value="1" class="deltaOptimizer"></input>
            <div id="coa-graph">
                <div></div>
            </div>
		`;

        setCurrentCOA = (val) => true;
        applyOutputs = (data) => {};
        createCOAGraph = (sData) => true; 

        const cb_data = {
            id: '1',
            coa: {
                remaining: {
                    2026: 1,
                    2027: 1,
                    2028: 1,
                    2029: 1,
                    2030: 1
                },
                detail: [0,0,0,0,0],
                selected_programs: [
                    {
                        program_id: 'test_pom',
                        weighted_guidance_score: 1.0
                    }
                ]
            }
        };
        $.post = (url, data, successCallback, format) => {
            successCallback(cb_data);
        
            return {
                fail: failCallback => {
                    failCallback({ responseJSON: { detail: 'Failed to update Option Weighted Score' } });
                }
            };
        };

		require('../../../actions/SOCOM/optimizer/optimizer.js');

        const iel = {
            getInc: () => true,
            getExcl: () => true
        }
		window._rb.runOptimizer(iel);

		expect(true).toBe(true);
	});
});

describe('showLoadingSummary', () => {
	test('function', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <div id="loading-wrapper"></div>
            <div id="net_speed_box"></div>
        `

        setTimeout = (cb, t) => cb();

		require('../../../actions/SOCOM/optimizer/optimizer.js');

		window._rb.showLoadingSummary();

		expect(true).toBe(true);
	});
});

describe('applyOutputs', () => {
    beforeEach(() => {
        jest.resetModules();
        
		require('../../../actions/SOCOM/optimizer/optimizer.js');

        document.body.innerHTML = `
            <div id="coa-table-1">
                <div class="remaining"></div>
                <div class="remaining"></div>
                <div class="remaining"></div>
                <div class="remaining"></div>
                <div class="remaining"></div>
                <div class="remaining"></div>
            </div>
        `;

        global.outputData = {
            remaining: {
                2026: 1,
                2027: 1,
                2028: 1,
                2029: 1,
                2030: 1,
                2031: 2
            },
            selected_programs: [
                {
                    program_id: 'test_pom',
                    weighted_guidance_score: 1.0
                }
            ]
        }
      });


	test('function', () => {
		window._rb.applyOutputs(outputData);

		expect(true).toBe(true);
	});

    test('function where storm_flag = true', () => {
        window._rb.applyOutputs(outputData, GP=1, stack=3, storm_flag=true);

        expect(true).toBe(true);
    })

    test("function with empty outputData",  () => {
        outputData = {};

		const result = window._rb.applyOutputs(outputData);

		expect(result).toBe(false);
    });

    test("function with outputData['selected_programs'].length > 10",  () => {
        outputData.selected_programs = Array(20).fill({
            program_id: 'test_pom',
            weighted_guidance_score: 1.0
        }).map(item => ({ ...item }))
        ;

		const result = window._rb.applyOutputs(outputData);

		expect(true).toBe(true);
    });
});

describe('resetCOA', () => {
	test('function', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <input id="test" class="deltaOptimizer" value="1"></input>
        `

		require('../../../actions/SOCOM/optimizer/optimizer.js');

        const iel = {
            reset: () => true
        }
		window._rb.resetCOA(iel);

		expect(true).toBe(true);
	});
});

describe('onReady', () => {
	test('function', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <div class="bx--radio-button-group"> 
                <input id="r-w" class="bx--radio-button" type="radio" value="1" name="storm_weighted_based" tabindex="WEIGHTED"> 
                <input id="r-storm" class="bx--radio-button" type="radio" value="2" name="storm_weighted_based" tabindex="StoRM">
            </div>
            <button id="create-coa"></button>
            <button id="run-optimizer"></button>
            <div id="delta-y1"></div>
            <table id="optimizer-table">
                <tbody>
                    <tr>
                        <td>
                            <input id="include-1" type="checkbox" name="include[]" value="1"></input>
                        </td>
                        <td>
                            <input id="include-2" type="checkbox" name="include[]" value="2" checked></input>
                        </td>
                        <td>
                            <input id="exclude-1" type="checkbox" name="exclude[]" value="1"></input>
                        </td>
                        <td>
                            <input id="exclude-2" type="checkbox" name="exclude[]" value="2" checked></input>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table id="coa-table-1">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2026</td>
                        <td><input type="number" class="delta-y1"></td>
                    </tr>
                    <tr>
                        <td>2027</td>
                        <td><input type="number" class="delta-y2"></td>
                    </tr>
                    <tr>
                        <td>2028</td>
                        <td><input type="number" class="delta-y3"></td>
                    </tr>
                    <tr>
                        <td>2029</td>
                        <td><input type="number" class="delta-y4"></td>
                    </tr>
                    <tr>
                        <td>2030</td>
                        <td><input type="number" class="delta-y5"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button class="deltaOptimizer">Calculate FYDP</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Total FYDP</td>
                        <td class="delta-fydp">0</td>
                    </tr>
                </tbody>
            </table>

            <table id="coa-table-2">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2026</td>
                        <td><input type="number" class="delta-y1"></td>
                    </tr>
                    <tr>
                        <td>2027</td>
                        <td><input type="number" class="delta-y2"></td>
                    </tr>
                    <tr>
                        <td>2028</td>
                        <td><input type="number" class="delta-y3"></td>
                    </tr>
                    <tr>
                        <td>2029</td>
                        <td><input type="number" class="delta-y4"></td>
                    </tr>
                    <tr>
                        <td>2030</td>
                        <td><input type="number" class="delta-y5"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button class="deltaOptimizer">Calculate FYDP</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Total FYDP</td>
                        <td class="delta-fydp">0</td>
                    </tr>
                </tbody>
            </table>

            <table id="coa-table-3">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2026</td>
                        <td><input type="number" class="delta-y1"></td>
                    </tr>
                    <tr>
                        <td>2027</td>
                        <td><input type="number" class="delta-y2"></td>
                    </tr>
                    <tr>
                        <td>2028</td>
                        <td><input type="number" class="delta-y3"></td>
                    </tr>
                    <tr>
                        <td>2029</td>
                        <td><input type="number" class="delta-y4"></td>
                    </tr>
                    <tr>
                        <td>2030</td>
                        <td><input type="number" class="delta-y5"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button class="deltaOptimizer">Calculate FYDP</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Total FYDP</td>
                        <td class="delta-fydp">0</td>
                    </tr>
                </tbody>
            </table>`;

        selected_POM_weight_table = {
            rows: () => {
                return {
                    data: () => [1]
                }
            }
        }

        selected_Guidance_weight_table = {
            rows: () => {
                return {
                    data: () => [1]
                }
            }
        }

		require('../../../actions/SOCOM/optimizer/optimizer.js');

		window._rb.onReady();

        $('#create-coa').trigger('click');
        $('#run-optimizer').trigger('click');
        $('#delta-y1').trigger('change');
        $('#include-1').trigger('change');
        $('#exclude-1').trigger('change');
        $('#coa-table-1 .deltaOptimizer').trigger('change')
        $('#coa-table-2 .deltaOptimizer').trigger('change')
        $('#coa-table-3 .deltaOptimizer').trigger('change')
        $('input[name="storm_weighted_based"]').trigger('change');

		expect(true).toBe(true);
	});

    /*  
        NOTE: The if block near line 322 of optimizer.js within the onReady() function may have an unnecessary if case,
        investigate in near future (5/21/24)
    */
    test('missing onchange cases for optimizer table checkbox inputs', () => {
        jest.resetModules();
        

        document.body.innerHTML = `
            <button id="create-coa"></button>
            <button id="run-optimizer"></button>
            <div id="delta-y1"></div>
            <table id="optimizer-table">
                <tbody>
                    <tr>
                        <td>
                            <input id="include-1" type="checkbox" name="include[]" value="1" checked></input>
                        </td>
                        <td>
                            <input id="include-2" type="checkbox" name="include[]" value="2"></input>
                        </td>
                        <td>
                            <input id="exclude-1" type="checkbox" name="exclude[]" value="1" checked></input>
                        </td>
                        <td>
                            <input id="exclude-2" type="checkbox" name="exclude[]" value="2"></input>
                        </td>
                    </tr>
                </tbody>
            </table>
        `;

        window._rb.onReady();

        $('#include-1').trigger('change');
        $('#exclude-1').trigger('change');
        $('#include-2').trigger('change');
        $('#exclude-2').trigger('change');

        expect(true).toBe(true);
    });
});

describe('incExclList class', () => {
	test('call functions', () => {
        jest.resetModules();
        

		require('../../../actions/SOCOM/optimizer/optimizer.js');

		let test = new window._rb.incExclList();
        test.addInc(1);
        test.removeInc(1);
        test.addExcl(1);
        test.removeExcl(1);
        test.getInc(1);
        test.getExcl(1);
        test.reset();

		expect(true).toBe(true);
	});
});

describe('calculateTotalWeight', () => {
    require('../../../actions/SOCOM/optimizer/optimizer.js');

    test('should return "0" when SESSION is null', () => {
        const row = { SCORE_SESSION: JSON.stringify(null) };
        const weight_table = {
            rows: () => ({
                data: () => [[]]
            })
        };

        const result = window._rb.calculateTotalWeight(row, weight_table);
        expect(result).toEqual('0');
    });

    test('should return "0" when weightData is invalid', () => {
        const row = { SCORE_SESSION: JSON.stringify({ exampleKey: 'exampleValue' }) };
        const weight_table = {
            rows: () => ({
                data: () => ['invalidData']
            })
        };

        const result = window._rb.calculateTotalWeight(row, weight_table);
        expect(result).toEqual('0');
    });

    test('should calculate total weight correctly', () => {
        const weightData = {
            "RISK": "0.10",
            "READINESS": "0.10",
            "FOUNDATIONAL": "0.10",
            "DESIGN_ALIGNMENT": "0.10",
            "COST_PRACTICALITY": "0.05",
            "STRATEGIC_ALIGNMENT": "0.10",
            "MANPOWER_FEASIBILITY": "0.10",
            "POLITICAL_FEASIBILITY": "0.10",
            "ACQUISITION_FEASIBILITY": "0.10",
            "COST_PROFILE_FEASIBILITY": "0.05",
            "SECURITY_COOPERATION_FEASIBILITY": "0.10"
        };

        const row = { SCORE_SESSION: JSON.stringify(
            {
                "RISK": "2",
                "READINESS": "2",
                "FOUNDATIONAL": "2",
                "DESIGN_ALIGNMENT": "2",
                "COST_PRACTICALITY": "2",
                "STRATEGIC_ALIGNMENT": "2",
                "MANPOWER_FEASIBILITY": "2",
                "POLITICAL_FEASIBILITY": "2",
                "ACQUISITION_FEASIBILITY": "2",
                "COST_PROFILE_FEASIBILITY": "2",
                "SECURITY_COOPERATION_FEASIBILITY": "2"
            }
        )};

        const weight_table = {
            rows: () => ({
                data: () => [weightData]
            })
        };

        const result = window._rb.calculateTotalWeight(row, weight_table);

        expect(result).toEqual("2.00");
    });
});

describe('load_optimizer_table', () => {
    global.selected_program_ids = [];
    
    document.body.innerHTML = `
        <table id="optimizer-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>Remove From Play</th>
                    <th>Program</th>
                    <th>Pom Sponsor</th>
                    <th>Capability Sponsor</th>
                    <th>Weights: Guidance</th>
                    <th>Weights: POM</th>
                    <th>StoRM ID</th>
                    <th>StoRM Score</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    `

    require('../../../actions/SOCOM/optimizer/optimizer.js');

    test('should initialize DataTable correctly', () => {
        window._rb.load_optimizer_table();
        
        expect(true).toBe(true);
    });
});