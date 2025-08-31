const jQuery = require('jquery'); 
global.$ = jQuery;
global.jQuery = jQuery;

global.rb_en_de_fixed = jest.fn();

beforeEach(()=> {
    jest.resetModules();
    document.body.innerHTML = `
        <html class="w-100">
        <button class="theme-button">
        
        </button>
        </html>
    `
    require('../../actions/SOCOM/toggle_theme');  
})

test('toggle_theme', () => {
    window._rb.toggle_theme();
    const theme_button = $('.theme-button');
    expect(theme_button.hasClass('bx--btn--primary')).toBe(true);
    window._rb.toggle_theme();
    expect(theme_button.hasClass('bx--btn--tertiary')).toBe(true);
})