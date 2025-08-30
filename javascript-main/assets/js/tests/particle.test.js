/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */

// Inject globals
const jQuery = require('jquery'); 
global.$ = jQuery;

// require('particles.js');

// Run jQuery plugins (see project root webpack.config.js for what file to load for the jQuery plugin to attach to the global jQuery instance correctly)
require('bootstrap/dist/js/bootstrap.bundle.min.js');

test('particle-dark-test', () => {
    // The require method caches required files, so we resetting files is necessary since
    // our JS isn't actually modular and is intended to produce side-effects
    jest.resetModules();

    // Inject globals
    // TODO: ideally globals should be cleaned up after test completion; need to investigate Jest's setUp/tearDown API
    // globals.exampleGlobal = null;

    // Mock markup for client-side JS to manipulate
    document.body.setAttribute("data-theme","dark");

    particlesJS = ()=>{return}

    require('../actions/particle');

    expect(true).toBe(true);
});

test('particle-light-test', () => {
    // The require method caches required files, so we resetting files is necessary since
    // our JS isn't actually modular and is intended to produce side-effects
    jest.resetModules();

    // Inject globals
    // TODO: ideally globals should be cleaned up after test completion; need to investigate Jest's setUp/tearDown API
    // globals.exampleGlobal = null;

    // Mock markup for client-side JS to manipulate
    document.body.setAttribute("data-theme","light");

    particlesJS = ()=>{return}

    require('../actions/particle');

    expect(true).toBe(true);
});