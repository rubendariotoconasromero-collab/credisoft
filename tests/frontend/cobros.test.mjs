import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import moment from 'moment';
import { parse } from '@vue/compiler-sfc';

function component() {
    const { descriptor } = parse(readFileSync(new URL('../../resources/js/components/Caja/GestionCobros.vue', import.meta.url), 'utf8'));
    const requests = [];
    const context = { moment, tienePermiso: () => true, Swal: { fire() {} }, axios: {
        get: () => new Promise((resolve, reject) => requests.push({ resolve, reject })), isCancel: () => false,
    }};
    vm.runInNewContext(descriptor.script.content.replace(/^import .*;\r?$/gm, '').replace('export default', 'globalThis.component ='), context);
    const options = context.component;
    const state = options.data();
    state.$nextTick = callback => callback();
    for (const [name, method] of Object.entries(options.methods)) state[name] = method.bind(state);
    for (const [name, getter] of Object.entries(options.computed)) Object.defineProperty(state, name, { get: getter.bind(state) });
    state.plan_pago = { id: 1 };
    state.selectedCuotas = [1];
    return { state, requests };
}
const response = date => ({ data: { fecha_corte: date, revision: date,
    cuotas: [{ id: 1, estado: 1, capital_neto: '100.00', interes_acumulado_neto: '10.00', mora_fija_neta: '0.00' }] } });

test('reopening keeps the date of the debt snapshot', () => {
    const { state } = component();
    state.fechaDeuda = '2026-09-01'; state.revisionDeuda = 'rev';
    state.lista_cuotas_plan = response(state.fechaDeuda).data.cuotas;
    state.resetPaymentDetails();
    assert.equal(state.paymentDetails.fecha_pago, '2026-09-01');
    assert.equal(state.paymentDetails.monto_recibido, 110);
    assert.equal(state.puedeContinuarCobro, true);
});

test('only the latest date response can enable collection', async () => {
    const { state, requests } = component();
    state.paymentDetails.fecha_pago = '2026-09-01';
    const first = state.actualizarFechaCobro();
    state.paymentDetails.fecha_pago = '2026-09-02';
    const second = state.actualizarFechaCobro();
    assert.equal(state.puedeContinuarCobro, false);
    requests[1].resolve(response('2026-09-02')); await second;
    requests[0].resolve(response('2026-09-01')); await first;
    assert.equal(state.fechaDeuda, '2026-09-02');
    assert.equal(state.revisionDeuda, '2026-09-02');
    assert.equal(state.puedeContinuarCobro, true);
});

test('a failed recalculation leaves collection disabled', async () => {
    const { state, requests } = component();
    state.fechaDeuda = state.paymentDetails.fecha_pago;
    state.revisionDeuda = 'previous'; state.paymentDetails.monto_recibido = 10;
    const calculation = state.actualizarFechaCobro();
    requests[0].reject(new Error('network')); await calculation;
    assert.equal(state.puedeContinuarCobro, false);
    assert.equal(state.calculandoDeuda, false);
});
