/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 * 
 * 
 * TODO: script to test might not actually be used anywhere so we may just end up deleting it.
 * 
 */

// Inject globals
const d3 = require('./dist/assets/d3.min.js'); 
global.d3 = d3;

describe('class Colors', () => {
    test('colorGradient', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();
        const colorGradient = colors.colorGradient(2, ['#000', '#fff']);
        expect(colorGradient.length).toBe(2);
        expect(colorGradient[0]).toBe('rgb(0, 0, 0)');
        expect(colorGradient[1]).toBe('rgb(255, 255, 255)');
    });


    test('green', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(colors.green).toBe('#28bfa4');
    });

    test('green', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(colors.green).toBe('#28bfa4');
    });

    test('red', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(colors.red).toBe('#ff0032');
    });

    test('yellow', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(colors.yellow).toBe('#ebff95');
    });

    test('LowCountPointColors', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(Array.isArray(colors.LowCountPointColors)).toBe(true);
        expect(colors.LowCountPointColors.length).toBeGreaterThan(0);
    });

    test('HighCountPointColors', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(Array.isArray(colors.HighCountPointColors)).toBe(true);
        expect(colors.HighCountPointColors.length).toBeGreaterThan(0);
    });

    test('graphBG', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        expect(typeof colors.graphBG).toBe('string');
    });

    test('liveGraphData', () => {
        jest.resetModules();

        require('../global/colors');

        const colors = new window._rb.Colors();

        const liveGraphData = colors.liveGraphData();

        expect(Array.isArray(liveGraphData.base)).toBe(true);
        expect(typeof liveGraphData.green).toBe('string');
        expect(typeof liveGraphData.db_api).toBe('string');
        expect(typeof liveGraphData.email_api).toBe('string');
        expect(typeof liveGraphData.codelab_api).toBe('string');
    });
});
