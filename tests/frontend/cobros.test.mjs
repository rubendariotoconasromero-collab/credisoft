import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import moment from 'moment';
import { parse } from '@vue/compiler-sfc';

function component() {
    const { descriptor } = parse(readFileSync(new URL('../../resources/js/components/Caja/GestionCobros.vue', import.meta.url), 'utf8'));
    const requests = [];
    const posts = [];
    const context = { moment, DesgloseExigibleCuota: {}, tienePermiso: () => true, Swal: { fire: () => Promise.resolve({ isConfirmed: false }) }, axios: {
        get: () => new Promise((resolve, reject) => requests.push({ resolve, reject })),
        post: (url, payload) => { posts.push({ url, payload }); return Promise.resolve({ data: {} }); },
        isCancel: () => false,
    }};
    vm.runInNewContext(descriptor.script.content.replace(/^import .*;\r?$/gm, '').replace('export default', 'globalThis.component ='), context);
    const options = context.component;
    const state = options.data();
    state.$nextTick = callback => callback();
    for (const [name, method] of Object.entries(options.methods)) state[name] = method.bind(state);
    for (const [name, getter] of Object.entries(options.computed)) Object.defineProperty(state, name, { get: getter.bind(state) });
    state.plan_pago = { id: 1 };
    state.selectedCuotas = [1];
    return { state, requests, posts };
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

test('mixed collection requires matching parts and non-cash references', () => {
    const { state } = component();
    state.fechaDeuda = state.paymentDetails.fecha_pago;
    state.revisionDeuda = 'rev';
    state.lista_cuotas_plan = response(state.fechaDeuda).data.cuotas;
    state.paymentDetails.modalidad = 'parcial';
    state.paymentDetails.monto_recibido = 110;
    state.paymentDetails.forma_pago = 'mixto';
    state.partesMixtas.efectivo = 50;
    state.partesMixtas.transferencia = 60;
    assert.equal(state.puedeContinuarCobro, false);
    state.partesMixtas.referencia_transferencia = 'QR-123';
    assert.equal(state.formaPagoValida, true);
    assert.equal(state.puedeContinuarCobro, true);
    state.partesMixtas.transferencia = 70;
    assert.equal(state.puedeContinuarCobro, false);
});

test('a transfer needs a reference and a condonation needs a reason', () => {
    const { state } = component();
    state.fechaDeuda = state.paymentDetails.fecha_pago;
    state.revisionDeuda = 'rev';
    state.lista_cuotas_plan = response(state.fechaDeuda).data.cuotas;
    state.paymentDetails.monto_recibido = 100;
    state.paymentDetails.forma_pago = 'transferencia';
    assert.equal(state.puedeContinuarCobro, false);
    state.paymentDetails.referencia = 'QR-456';
    state.paymentDetails.monto_condonado_interes = 10;
    assert.equal(state.puedeContinuarCobro, false);
    state.paymentDetails.motivo_condonacion_interes = 'Convenio';
    assert.equal(state.puedeContinuarCobro, true);
});

test('mixed payment sends the selected amounts and references to collection', async () => {
    const { state, posts } = component();
    state.fechaDeuda = state.paymentDetails.fecha_pago;
    state.revisionDeuda = 'rev';
    state.lista_cuotas_plan = response(state.fechaDeuda).data.cuotas;
    state.paymentDetails.modalidad = 'parcial';
    state.paymentDetails.monto_recibido = 110;
    state.paymentDetails.forma_pago = 'mixto';
    state.partesMixtas.efectivo = 50;
    state.partesMixtas.transferencia = 60;
    state.partesMixtas.referencia_transferencia = 'QR-123';
    state.cerrarModalCobrarCuotas = () => {};
    state.verDetallePlan = () => {};
    await state.procesarPagoCuotas();
    assert.equal(posts.length, 1);
    assert.equal(posts[0].url, '/pagar_cuotas');
    assert.deepEqual(JSON.parse(JSON.stringify(posts[0].payload.partes)), [
        { forma_pago: 'efectivo', monto: 50, referencia: '' },
        { forma_pago: 'transferencia', monto: 60, referencia: 'QR-123' }
    ]);
});
