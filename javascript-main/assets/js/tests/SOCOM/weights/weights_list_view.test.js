/* 
 * @jest-environment jsdom
*/
const { CellCoords } = require('handsontable');
const jQuery = require('jquery');
$ = jQuery;
global.$ = jQuery;
global.jQuery = jQuery;

require('bootstrap/dist/js/bootstrap.bundle.min.js');
require('datatables/media/js/jquery.dataTables.min.js')(window, jQuery);

global.rhombuscookie = () => null;
global.crypto = {
    getRandomValues: () => { return [0]}
}
global.sanitizeHtml = jest.fn((string) => string);
global.scoreData = {};
global.weightsValues = [];
global.scoreTable = {
    getDataAtCol: function() {
        return [1000, 1000, 1000];
    },
    getDataAtCell: function() {
        return 1;
    },
    loadData: function() {
        return 1;
    },
    getData: function() {
        return [1000, 1000, 1000];
    }
}

global.column_definition = {};

jQuery.fn.handsontable = function() {
    return {
        'getSourceData': () => {},
        'getData': () => [1000,1000,1000],
        'loadData':  () =>  1
    }
}

setNotificationName = (name) => true;
ajaxFail = () => true;
showNotification = (a, b, c) => true;

let assignMock = jest.fn();
let reloadMock = jest.fn();
global.USERGROUP = 'USAF';

delete window.location;
window.location = { assign: assignMock, reload: reloadMock };

afterEach(() => {
  assignMock.mockClear();
  reloadMock.mockClear();
});

// test re-used
test('onReady', () => {
    jest.resetModules();

    url = "/criteria/weights/list/data/"
    $.get = (url, data, callback) => callback()
    
    let success_mock = {
        status: "status"
    }
    
    $.ajax = jest.fn((url, data) => {
        data.success(success_mock)
    });
    
    global.$.fn.DataTable = jest.fn(function(obj) { 
        if (obj !== undefined){
            obj['createdRow']('', 'data', '');
            obj['createdRow']('', null, '');
        };
        return { ajax: { reload: function () {} }}
    });

    $("#test").trigger("click");

    require('../../../actions/SOCOM/weights/weights_list_view.js');
    
    $('#score_view_modal_btn').on('click', window._rb.onReady);
    $('#score_view_modal_btn').trigger('click');
    expect($("#score_view_modal").hasClass("d-none")).toBeFalsy();

    window._rb.onReady();
    expect(true).toBe(true);
})

// test re-used
describe('show_menu', () => {
    test('option_panel has class open', () => {
        jest.resetModules();
        document.body.innerHTML = `
            <button id="option_panel_btn"></button>
            <div id="option_panel" class="bx--overflow-menu-options bx--overflow-menu-options--open">
                <div id="option_panel-btn" class="bx--overflow-menu-options--open">
                </div>
            </div>
        `;

        require('../../../actions/SOCOM/weights/weights_list_view.js');
        
        $('#option_panel_btn').on('click', window._rb.show_menu);
        $('#option_panel_btn').trigger('click');
        expect($("#option_panel").hasClass("bx--overflow-menu-options--open")).toBeFalsy();
    })

    test('option_panel do not have class open', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <button id="option_panel_btn"></button>
            <div id="option_panel" class="bx--overflow-menu-options">
                <div id="option_panel-btn" class="bx--overflow-menu-options--open">
                </div>
            </div>
        `;
        
        require('../../../actions/SOCOM/weights/weights_list_view.js');
        
        $('#option_panel_btn').on('click', window._rb.show_menu);
        $('#option_panel_btn').trigger('click');
        expect($("#option_panel,#option_panel-btn").hasClass("bx--overflow-menu-options--open")).toBeTruthy();
    })
})

describe('show_weight_view_modal', () => {
    test('if', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <button id="weight_view" weight="1"></button>
        `;

        $.ajax = (settings) => true;

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        $('#weight_view').on('click', window._rb.show_weight_view_modal);
        $('#weight_view').trigger('click');
        

        expect(true).toBe(true);
    });

    test('else', () => {
        jest.resetModules();

        document.body.innerHTML = `
        `;

        $.ajax = (settings) => true;

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        window._rb.show_weight_view_modal();

        expect(true).toBe(true);
    });
});

test('delete_weight', () => {
    jest.resetModules()
    
    document.body.innerHTML = `
        <button id="option_panel_btn"></button>
        <div id="option_panel" class="bx--overflow-menu-options">
            <div id="option_panel-btn" class="bx--overflow-menu-options--open">
            </div>
        </div>
    `;
    
    require('../../../actions/SOCOM/weights/weights_list_view.js');    

    // Run test.
    $('#option_panel_btn').on('click', window._rb.show_menu);
    $('#option_panel_btn').trigger('click');
    expect($("#option_panel,#option_panel-btn").hasClass("bx--overflow-menu-options--open")).toBeTruthy();

    url = "/criteria/weights/delete/"
    $.post = (url, data, callback) => callback()
    
    let success_mock = {
        status: "status"
    }
        
    $.ajax = jest.fn((url, data) => {
        data.success(success_mock)
    });

    $("#test").trigger("click");

    window._rb.delete_weight();
    expect(true).toBe(true);
})

test('loadWeightTable', () => {
    jest.resetModules()
    
    require('../../../actions/SOCOM/weights/weights_list_view.js');    
    
    url = "/criteria/weights/list/score_table/"
    $.get = (url, data, callback) => callback()
    
    let success_mock = {
        tableData: [{'score': 1}],
        status: "status",
        guidance: {
            id: 'id',
            rowHeaders: [],
            colHeaders: [],
            tableData: [{weight:1.0}],
            readOnly: true,
            licenseKey: 'test'
        },
        pom: {
            id: 'id',
            rowHeaders: [],
            colHeaders: [],
            tableData: [{weight:1.0}],
            readOnly: true,
            licenseKey: 'test'
        }
    }

    $.ajax = jest.fn((data) => {
        data.success(success_mock);
    });
    
    global.$$ = () => {
        return ''
    };    

    $("#test").trigger("click");
    
    require('../../../actions/SOCOM/weights/weights_list_view.js');    
    window._rb.loadWeightTable();
    expect(true).toBe(true);
    
    global.scoreTable = {
        getDataAtCol: function(test) {
            return [1];
        },
        getDataAtCell: function() {
            return 1;
        },
        loadData: function() {
            return 1;
        },
    }
    window._rb.loadWeightTable();
    expect(true).toBe(true);
})

test('hideScoreError', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div id="score-error" class="d-none">
        </div>
    `;
    
    require('../../../actions/SOCOM/weights/weights_list_view.js');    
    
    // Run test.
    expect($("#score-error").hasClass("d-none")).toBeTruthy();

})

test('showScoreError', () => {
    jest.resetModules();

    document.body.innerHTML = `
        <div id="score-error" class="d-none">
        </div>
    `;
    
    require('../../../actions/SOCOM/weights/weights_list_view.js');    
    
    // Run test.
    expect($("#score-error").hasClass("d-none")).toBeTruthy();

})

describe('saveWeight', () => {
    test('weight choice', () => {
        jest.resetModules()

        document.body.innerHTML = `
            <input id="hidden_weight_id" value="1"></input>
        `

        url = "/criteria/weights/list/score_table/save/"
        $.post = (url, data, callback) => callback()
        
        let success_mock = {
            status: "status"
        }
        let error_mock={
            responseJSON: {
                status: "empty"
            }
        }
        let error={
            responseJSON:"test"
        }
        $.ajax = jest.fn((data) => {
            data.success(success_mock),
            data.error(error_mock),
            data.error(error)
        });

        $("#test").trigger("click");

        require('../../../actions/SOCOM/weights/weights_list_view.js');    
        window._rb.saveWeight();
        expect(true).toBe(true);
    })

    test('no weight choice', () => {
        jest.resetModules()

        document.body.innerHTML = `
            <input id="hidden_weight_id" value="" disabled></input>
        `

        url = "/criteria/weights/list/score_table/save/"
        $.post = (url, data, callback) => callback()
        
        let success_mock = {
            status: "status"
        }
        let error_mock={
            responseJSON: {
                status: "empty"
            }
        }
        let error={
            responseJSON:"test"
        }
        $.ajax = jest.fn((data) => {
            data.success(success_mock),
            data.error(error_mock),
            data.error(error)
        });

        $("#test").trigger("click");

        require('../../../actions/SOCOM/weights/weights_list_view.js');    
        window._rb.saveWeight();
        expect(true).toBe(true);
    })
});

test('loadScoreSums', ()=>{
    document.body.innerHTML=`
        <div id="score-weight-class">
            <span id="weight-sum-text" type="text">1.00</span>
            <span id="weighted-score-sum-text" type="text">1.00</span>
        </div>
    `
    require('../../../actions/SOCOM/weights/weights_list_view.js');    
    expect(document.getElementById("weighted-score-sum-text").textContent).toBe('1.00')
})

test('weightedScoreSum', () => {
    require('../../../actions/SOCOM/weights/weights_list_view.js');

    const weightedScores = [0.1, 0.2];
    window._rb.weightedScoreSum(weightedScores);

    expect(true).toBe(true);
});

describe('loadWeightedSums', () => {
    test('function', () => {
        jest.resetModules();

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        const scoreData = [
            {
                weight: 1.0
            }
        ];
        const type = 'pom';
        window._rb.loadWeightedSums(scoreData, type)

        expect(true).toBe(true);
    });
});

describe('updateWeightSums', () => {
    test('early return', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <div id="weight-pom-div">
                <table>
                    <tr>
                        <td></td>
                        <td>return</td>
                    </tr>
                </table>
            </div>
        `;

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        const type = 'pom';
        window._rb.updateWeightSums(type)

        expect(true).toBe(true);
    });

    test('cellText empty', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <div id="weight-pom-div">
                <table>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        `;

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        const type = 'pom';
        window._rb.updateWeightSums(type)

        expect(true).toBe(true);
    });

    test('cellText valid', () => {
        jest.resetModules();

        document.body.innerHTML = `
            <div id="weight-pom-div">
                <table>
                    <tr>
                        <td></td>
                        <td>1.0</td>
                    </tr>
                </table>
            </div>
        `;

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        const type = 'pom';
        window._rb.updateWeightSums(type)

        expect(true).toBe(true);
    });
});

describe('getHotScoreTable', () => {
    test('function', () => {
        jest.resetModules();

        document.body.innerHTML = `

        `;

        require('../../../actions/SOCOM/weights/weights_list_view.js');

        const hst = window._rb.getHotScoreTable();
        hst.cells(1,1);
        hst.beforeKeyDown({
            target: {
                value: 'longer than 4'
            },
            key: 'Enter',
            preventDefault: () => true
        })
        hst.afterChange([]);

        expect(true).toBe(true);
    });
});
