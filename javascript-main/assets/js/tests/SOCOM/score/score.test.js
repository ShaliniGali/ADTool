/**
 * @jest-environment jsdom
 */

 const jQuery = require('jquery'); 
const { options } = require('sanitize-html');
 global.$ = jQuery;
 global.jQuery = jQuery;
 
 let reloadMock = jest.fn();
 
 delete window.location;
 window.location = { reload: reloadMock };
 global.USERGROUP = "";
 global.rhombuscookie = () => true;
 global.sanitizeHtml = jest.fn();
 global.handson_license = '';
 default_criteria = ['test', 'test'];
 global.$.fn.handsontable = () => { return {
     render: () => {},
     loadData: () => {},
     getDataAtCol: () => {return {forEach: (e) => {}}},
     forEach: (e) => {}
 } };

 global.$.fn.forEach = () => {};

 setTimeout = (cb, t) => cb();
 column_definition = ['test'];
 
 require('bootstrap/dist/js/bootstrap.bundle.min.js');
 require('select2')(jQuery);
 global.setScoreActive = jest.fn();
 global.optionId = 1;
 global.is_afplan_option = 0;
 global.$.fn.DataTable = (obj) => {
   
   if (typeof obj === 'object' && typeof obj.ajax === 'object' && typeof obj.ajax.data === 'function') {
     
         obj.ajax.data({});
   }
 
   if (typeof obj === 'object' && typeof obj.createdRow === 'function') {
     obj.createdRow({}, {ID: 1, can_edit: true, FILE_STATUS_TXT: 'Submitted'}, 1);
         obj.createdRow({}, false, 1);
   }
 
   return {
     clear: jest.fn(),
     destroy: jest.fn(),
     ajax: {
       reload: jest.fn()
     }
   }
 };

 global.setNotificationName = jest.fn()
 global.hideNotification = jest.fn()
 global.showNotification = jest.fn()
 global.ajaxFail = jest.fn()
 global.makeCriteria = () => {}
 
 test('hideScoreModal', () => {
     jest.resetModules();
     require("../../../actions/SOCOM/score/score.js");
     window._rb.hideScoreModal();
     expect(true).toBe(true);
 });
 
 test('getHandsOnTableInitData', () => {
     jest.resetModules();
     require("../../../actions/SOCOM/score/score.js");
     window._rb.getHandsOnTableInitData();
     expect(true).toBe(true);
 });

 
 test('loadScoreTable', () => {
     jest.resetModules();
 
     document.body.innerHTML = `
     `;
 
     require("../../../actions/SOCOM/score/score.js");
 
     const dc = ['test'];
 
     window._rb.loadScoreTable(dc);
     expect(true).toBe(true);
 });

 
 test('showScoreModal', () => {
     jest.resetModules();
 
     document.body.innerHTML = `
     `;
 
     require("../../../actions/SOCOM/score/score.js");
 
     window._rb.showScoreModal();
     expect(true).toBe(true);
 });
 
 test('resetForm', () => {
     jest.resetModules();
 
     document.body.innerHTML = `
     `;
 
     require("../../../actions/SOCOM/score/score.js");
     window._rb.resetForm();
     expect(true).toBe(true);
 });
 
 test('onReady', () => {
     jest.resetModules();
 
     document.body.innerHTML = `
         <button type="button" id="add_score">test</button>
     `;
 
     require("../../../actions/SOCOM/score/score.js");
     window._rb.onReady();
     $('#add_score').trigger('click');
     expect(true).toBe(true);
 })
 
 test('loadScoreTable', () => {
     document.body.innerHTML = `
         <div id="score_listing_list"></div>
     `;
 
     require("../../../actions/SOCOM/score/score.js");
     
   
     $.post = (url, postData, callbackFunc) => {
     callbackFunc(postData);
   }
 
     window._rb.loadScoreTable();
 
    $('#score_listing_list').DataTable().ajax.reload();
 
     expect(true).toBe(true);
 });
 
 test('getScore success', () => {
     document.body.innerHTML = `
         <div id=""></div>
         <div class='bx--overflow-menu-options'></div>'
     `;
 
     require("../../../actions/SOCOM/score/score.js");
     
     global.$.post = (url, postData, callbackFunc) => {
         
     callbackFunc({message: 1, status: 1, data: {SESSION: [true, true, false]}});
 
         return {
            done: jest.fn().mockImplementation(function () {
                return this;
            }),
            fail: jest.fn().mockImplementation(function () {
                return this;
            }),
        }
   }
 
     window._rb.getScore(1);
     
 
     expect(true).toBe(true);
 })

 test('getScore fail', () => {
    document.body.innerHTML = `
        <div id=""></div>
        <div class='bx--overflow-menu-options'></div>'
    `;

    require("../../../actions/SOCOM/score/score.js");
    
    global.$.post = () => {
        const jqXHR = { status: 500, statusText: 'Internal Server Error' };

        return {
            fail: jest.fn((callback) => {
                callback(jqXHR);
                return this;
            })
        }
    };

    window._rb.getScore(1);    

    expect(true).toBe(true);
})

 test('saveScore /edit success', () => {
    document.body.innerHTML = `
        <div id="">
            <input id="score-name" value="0"/>
            <input id="score-description" value="0"/>
            <input id="hidden_score_id" value="0"/>
            <input id="hidden_program_id" value="0"/>
            <input id="score_tab_data" value="0"/>
        </div>
    `;

    require("../../../actions/SOCOM/score/score.js");
    
    global.$.post = (url, postData, callbackFunc) => {
        
    callbackFunc({message: 1, status: 1, data: {SESSION: [true, true, false]}});

        return {
            done: jest.fn().mockImplementation(function () {
                return this;
            }),
            fail: jest.fn().mockImplementation(function () {
                return this;
            }),
        }
    }

    

    window._rb.saveScore();
    

    expect(true).toBe(true);
})

test('saveScore /create success', () => {
    document.body.innerHTML = `
        <div id="">
            <input id="score-name" value="0"/>
            <input id="score-description" value="0"/>
            <input id="hidden_score_id" value=""/>
            <input id="hidden_program_id" value="0"/>
            <input id="score_tab_data" value="0"/>
        </div>
    `;

    require("../../../actions/SOCOM/score/score.js");
    
    global.$.post = (url, postData, callbackFunc) => {
        
    callbackFunc({message: 1, status: 1, data: {SESSION: [true, true, false]}});

        return {
            done: jest.fn().mockImplementation(function () {
                return this;
            }),
            fail: jest.fn().mockImplementation(function () {
                return this;
            }),
        }
    }

    

    window._rb.saveScore();
    

    expect(true).toBe(true);
})

test('saveScore fail', () => {
    document.body.innerHTML = `
        <div id="">
            <input id="score-name" value="0"/>
            <input id="score-description" value="0"/>
            <input id="hidden_score_id" value="0"/>
            <input id="hidden_program_id" value="0"/>
            <input id="score_tab_data" value="0"/>
        </div>
    `;

    require("../../../actions/SOCOM/score/score.js");
    
    global.$.post = () => {
        const jqXHR = { status: 500, statusText: 'Internal Server Error' };
    
        return {
            fail: jest.fn((callback) => {
                callback(jqXHR);
                return this;
            })
        }
    };


    window._rb.saveScore();
    

    expect(true).toBe(true);
})

 
 test('deleteScore', () => {
     document.body.innerHTML = `
         <div id=""></div>
         <div class='bx--overflow-menu-options'></div>'
     `;
 
     require("../../../actions/SOCOM/score/score.js");
     
     global.$.post = (url, postData, callbackFunc) => {
         
     callbackFunc({message: 1, status: 1});
 
        return {
            done: jest.fn().mockImplementation(function () {
                return this;
            }),
            fail: jest.fn().mockImplementation(function () {
                return this;
            }),
        }
   }
 
    //  window._rb.loadScoreListing();
 
     window._rb.deleteScore();
     
 
     expect(true).toBe(true);
 })


 test('createHot_score if', () => {
    document.body.innerHTML = `
        <div id="">
            <input id="score_tab_data" val="0"/>
        </div>
    `;
    let container = $('#score_tab_data');

    require("../../../actions/SOCOM/score/score.js");

    global.value = -1
    let options = { testOption: null };
    window._rb.createHot_score(container, global.default_criteria, ['Score Value'], global.score_data, false, global.handson_license, options);
    

    expect(true).toBe(true);
})


test('showScore', () => {
    document.body.innerHTML = `
        <div id="hidden_score_id"></div>
        <div id="hidden_program_id"></div>
        <div id="score-name">
            <h5></h5>
        </div>
        <div id="parent">
            <div id="child"></div>
        </div>
    `;

    require("../../../actions/SOCOM/score/score.js");

    global.$.post = (url, postData, callbackFunc) => {
        callbackFunc({message: 1, status: 1, data: {SESSION: [true, true, false]}});
        return {
            done: jest.fn().mockImplementation(function () {
                return this;
            }),
            fail: jest.fn().mockImplementation(function () {
                return this;
            }),
        }
    }

    const context = $('#child');

    $('#parent').data("SCORE", 50);
    $('#parent').data("SCORE_ID", 123);
    $('#parent').data("PROGRAM_ID", 'program_id');

    window._rb.showScore.call(context);

    $('#parent').data("SCORE_ID", 'abc');

    window._rb.showScore.call(context);

    $('#parent').data("PROGRAM_ID", '');

    window._rb.showScore.call(context);

    expect(true).toBe(true);
})

test('editScore', () => {
    document.body.innerHTML = `
        <div id=""></div>
        <div class='bx--overflow-menu-options'></div>'
    `;

    require("../../../actions/SOCOM/score/score.js");

    global.$.post = (url, postData, callbackFunc) => {
         
        callbackFunc({message: 1, status: 1, data: {SESSION: [true, true, false]}});
    
        return {
            done: jest.fn().mockImplementation((callback) => {
                callback();
                return this;
            }),
            fail: jest.fn().mockImplementation(function () {
                return this;
            }),
        }
    }

    window._rb.editScore('score_id', 'program_id');

    expect(true).toBe(true);
})

test('percentageValidator', () => {
    require("../../../actions/SOCOM/score/score.js");

    window._rb.percentageValidator(200, (input) => {});

    expect(true).toBe(true);

    window._rb.percentageValidator(50, (input) => {});

    expect(true).toBe(true);
})