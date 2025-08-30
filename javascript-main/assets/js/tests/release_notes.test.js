const jQuery = require('jquery'); 
global.$ = jQuery;
require('select2')(jQuery);

describe('getNotes', () => {
    beforeEach(() => {
        require('../actions/release_notes');
        document.body.innerHTML = `
        <select id="release-notes-select"><option selected value="10">1.0</option><option value="20">2.0</option></select>
        <div id="head-20">text here</div>
        <div id="head-10">text here</div>
        `

        global.loadPageData = jest.fn(function(id, url, cb) {
            cb();
        });
        global.$.fn.select2 = () => {
            return {
                on: (action, callback) => {callback() }
            }
        }
    })

    test('getNotes successfully called', () => {
        document.body.innerHTML += `<div id="release-notes-main"></div>`
        window._rb.getNotes();
        $('#release-notes-select').trigger('change');
        expect(global.loadPageData).toHaveBeenCalled();
    })

})