/**
 * @jest-environment jsdom
 */

 const jQuery = require('jquery'); 
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
 } };
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

 test('hideNotification', () => {
  jest.resetModules();
  require("../../actions/SOCOM/notification.js");
  window._rb.hideNotification();
  expect(true).toBe(true);
});


test('showNotification neither error/success', () => {
  jest.resetModules();
  require("../../actions/SOCOM/notification.js");

  const msg = 'test';
  const type = 'test';

  window._rb.showNotification(msg, type);
  expect(true).toBe(true);
});

test('showNotification list true success', () => {
  jest.resetModules();

  document.body.innerHTML = `
  `;

  require("../../actions/SOCOM/notification.js");

  const msg = 'test';
  const type = 'success';
  const list = true;

  window._rb.showNotification(msg, type, list);
  expect(true).toBe(true);
});

test('ajaxFail if', () => {
  jest.resetModules();

  document.body.innerHTML = `
  `;

  require("../../actions/SOCOM/notification.js");

  const jqXHR = {
      responseJSON: {
          message: 'test'
      }
  }
  window._rb.ajaxFail(jqXHR);
  expect(true).toBe(true);
});

test('ajaxFail else', () => {
  jest.resetModules();

  document.body.innerHTML = `
  `;

  require("../../actions/SOCOM/notification.js");

  const jqXHR = {
      responseJSON: 'else'
  }
  window._rb.ajaxFail(jqXHR);
  expect(true).toBe(true);
});

test('setNotificationName', () => {
  jest.resetModules();

  document.body.innerHTML = `
  `;

  require("../../actions/SOCOM/notification.js");

  const name = 'name';
  window._rb.setNotificationName(name);
  expect(true).toBe(true);
})