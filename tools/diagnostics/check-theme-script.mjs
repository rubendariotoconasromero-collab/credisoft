import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

// Execute the actual shared theme script, including pages without chart helpers.
const source = readFileSync(new URL('../../public/assets/js/app.js', import.meta.url), 'utf8');
for (const colors of [null, '["--chart-color"]', 'invalid-json']) {
    const charts = [];
    let initialized = false;
    const jquery = new Proxy({}, {
        get: (_, key) => (...args) => {
            if (key === 'prop' && args.length === 1) return false;
            if (key === 'sparkline') charts.push(args[1].barColor);
            return jquery;
        },
    });
    const context = {
        console,
        jQuery: () => jquery,
        window: { location: { href: 'http://localhost/administracion' }, sessionStorage: true },
        document: {
            documentElement: {},
            querySelectorAll: () => [],
            addEventListener: () => {},
            getElementById: id => id.startsWith('header-chart-') && colors !== null
                ? { getAttribute: () => colors } : null,
        },
        sessionStorage: { getItem: () => null, setItem: () => {} },
        getComputedStyle: () => ({ getPropertyValue: () => '#123456' }),
        Waves: { init: () => { initialized = true; } },
    };
    vm.runInNewContext(source, context);
    assert.equal(initialized, true, 'Theme startup must finish without dashboard globals.');
    assert.equal(charts.length, colors === '["--chart-color"]' ? 2 : 0);
    for (const chart of charts) assert.equal(chart[0], '#123456');
}
console.log('Theme startup passed: no charts, valid colors and malformed colors.');
