import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { parse } from '@vue/compiler-sfc';

const source = readFileSync(new URL('../../resources/js/components/PlanPago/DesgloseExigibleCuota.vue', import.meta.url), 'utf8');
const { descriptor } = parse(source);
const context = {};
vm.runInNewContext(descriptor.script.content.replace('export default', 'globalThis.component ='), context);

function desglose(cuota) {
    const state = { cuota };
    for (const [name, getter] of Object.entries(context.component.computed)) {
        Object.defineProperty(state, name, { get: getter.bind(state) });
    }
    return state;
}

test('una cuota pagada reconcilia lo programado con recargos y condonaciones', () => {
    const cuota = desglose({ total: '275.00', total_registrado_antes_condonacion: '321.00',
        interes_condonado_total: '20.00', mora_condonado_total: '21.00' });
    assert.equal(cuota.variacion, 4600);
    assert.equal(cuota.condonado, 4100);
    assert.equal(cuota.exigible, 28000);
});

test('el interés futuro aún no devengado reduce el exigible actual', () => {
    const cuota = desglose({ total: '275.00', total_registrado_antes_condonacion: '250.00',
        interes_condonado_total: '0.00', mora_condonado_total: '0.00' });
    assert.equal(cuota.variacion, -2500);
    assert.equal(cuota.exigible, 25000);
});
