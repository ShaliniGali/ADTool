"use strict"

function homeDs() {
    if (window.darkSwitch === 'dark') {
        $('html').addClass('dark');
    }
    if($('html').hasClass('dark')) {
        $('.theme-button').addClass('bx--btn--primary').removeClass('bx--btn--tertiary');
    } else {
        $('.theme-button').addClass('bx--btn--tertiary').removeClass('bx--btn--primary');
    }

}

function toggle_theme() {
    $('html').toggleClass('dark');
    if($('html').hasClass('dark')) {
        $('.theme-button').addClass('bx--btn--primary').removeClass('bx--btn--tertiary');
    } else {
        $('.theme-button').addClass('bx--btn--tertiary').removeClass('bx--btn--primary');
    }
    localStorage.setItem('darkSwitch', rb_en_de_fixed("en", $('html').hasClass('dark') ? 'dark' : 'light'));
    $("#toggle-theme-button").blur(); 
}

if (!window._rb) { window._rb = {}; }

window._rb.toggle_theme = toggle_theme;
window._rb.homeDs = homeDs;