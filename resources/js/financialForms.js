import axios from 'axios';
import Swal from 'sweetalert2';
import '../css/financial-forms.css';

const mixedPaths = new Set(['/pagar_cuotas', '/caja/cobrar-orden-reprogramacion', '/save_ingreso', '/save_gasto', '/guardar_pago_adm', '/actualizar_desembolso']);
const combinedDisbursementPath = '/registrar_pago_adm_desembolso';
const paymentContexts = {
    '/pagar_cuotas': 'Cobro de cuota',
    '/save_ingreso': 'Ingreso de caja',
    '/save_gasto': 'Gasto de caja',
    '/guardar_pago_adm': 'Pago administrativo',
    '/actualizar_desembolso': 'Desembolso',
};
const moneyPaths = new Set([...mixedPaths, combinedDisbursementPath, '/caja/aperturar', '/close_caja', '/ingresar_boveda', '/retirar_boveda', '/aperturar_boveda', '/anular_planpago', '/anular_pago', '/anular_ingreso', '/anular_gasto', '/anular_desembolso', '/anular_ingreso_caja', '/anular_egreso_caja', '/pagos/anular-transaccion']);
const uuid = () => 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
    const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
    return (c === 'x' ? r : (r & 3) | 8).toString(16);
});
const pending = new Map();
moneyPaths.add('/solicitud/registrar-especial');
moneyPaths.add('/solicitud/aprobar-reprogramacion-final');
moneyPaths.add('/solicitud/cancelar-especial');
const storageKey = text => {
    let a = 2166136261, b = 5381;
    for (const character of text) { a = Math.imul(a ^ character.charCodeAt(0), 16777619); b = Math.imul(b, 33) ^ character.charCodeAt(0); }
    return `credisoft-intento-${a >>> 0}-${b >>> 0}`;
};

const formatMoney = value => new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
}).format(value);

async function breakdown(amount, changeAllowed, context = 'Operación de caja') {
    const rows = [];
    const result = await Swal.fire({ target: document.querySelector('.modal.show') || document.body,
        title: '<i class="fas fa-wallet" aria-hidden="true"></i><span>Formas de pago</span>',
        width: 720,
        html: `<div class="payment-context">${context}</div>
            <div class="payment-total-card">
                <div><span class="payment-total-label">Total a aplicar</span><small>Distribuya el importe entre una o más formas</small></div>
                <strong>${formatMoney(amount)} <span>Bs</span></strong>
            </div>
            <div id="payment-parts" class="payment-parts"></div>
            <button id="add-payment-part" type="button" class="payment-add-button">
                <i class="fas fa-plus-circle" aria-hidden="true"></i> Agregar otra forma
            </button>
            <div class="payment-summary" aria-live="polite">
                <div><span>Registrado</span><strong id="payment-entered">0,00 Bs</strong></div>
                <div><span id="payment-balance-label">Pendiente</span><strong id="payment-balance">${formatMoney(amount)} Bs</strong></div>
            </div>
            <div class="payment-notices" aria-label="Indicaciones para registrar el pago">
                <div class="payment-notice payment-notice-total">
                    <i class="fas fa-calculator" aria-hidden="true"></i>
                    <span><strong>Importes:</strong> ${changeAllowed ? 'complete el total; solo el efectivo puede generar cambio.' : 'la suma debe completar exactamente el total.'}</span>
                </div>
                <div class="payment-notice payment-notice-reference">
                    <i class="fas fa-receipt" aria-hidden="true"></i>
                    <span><strong>Pagos no efectivos:</strong> la referencia es obligatoria y el comprobante es opcional.</span>
                </div>
            </div>`,
        showCancelButton: true, confirmButtonText: 'Registrar operación', cancelButtonText: 'Cancelar', focusConfirm: false,
        buttonsStyling: false,
        customClass: {
            popup: 'financial-payment-popup',
            title: 'financial-payment-title',
            htmlContainer: 'financial-payment-content',
            actions: 'financial-payment-actions',
            confirmButton: 'financial-payment-confirm',
            cancelButton: 'financial-payment-cancel',
            validationMessage: 'financial-payment-validation',
        },
        didOpen: () => {
            const htmlContainer = Swal.getHtmlContainer();
            const container = htmlContainer.querySelector('#payment-parts');
            const entered = htmlContainer.querySelector('#payment-entered');
            const balance = htmlContainer.querySelector('#payment-balance');
            const balanceLabel = htmlContainer.querySelector('#payment-balance-label');
            const updateRows = () => {
                rows.forEach((paymentRow, index) => {
                    paymentRow.querySelector('.payment-row-number').textContent = index + 1;
                    const remove = paymentRow.querySelector('.remove');
                    remove.hidden = rows.length === 1;
                });
                const total = rows.reduce((sum, paymentRow) => {
                    const value = Number(paymentRow.querySelector('.amount').value);
                    return sum + (Number.isFinite(value) ? value : 0);
                }, 0);
                const difference = amount - total;
                entered.textContent = `${formatMoney(total)} Bs`;
                balanceLabel.textContent = difference < -0.004 ? 'Excedente' : 'Pendiente';
                balance.textContent = `${formatMoney(Math.abs(difference))} Bs`;
                balance.classList.toggle('is-complete', Math.abs(difference) < 0.005);
                balance.classList.toggle('is-over', difference < -0.004);
            };
            const add = (value = '') => {
                const row = document.createElement('div');
                row.className = 'payment-part';
                row.innerHTML = `<div class="payment-part-header">
                    <span class="payment-row-number"></span><span>Forma de pago</span>
                    <button type="button" class="payment-remove-button remove" aria-label="Quitar esta forma de pago" title="Quitar forma"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                </div>
                <div class="payment-fields">
                    <label class="payment-field"><span>Medio</span><select class="form-select form-select-sm">
                        <option value="efectivo">Efectivo</option><option value="deposito">Depósito</option><option value="transferencia">Transferencia / QR</option>
                    </select></label>
                    <label class="payment-field"><span>Importe (Bs)</span><div class="payment-amount-wrap"><span>Bs</span><input class="form-control form-control-sm amount" type="number" min="0.01" step="0.01" inputmode="decimal"></div></label>
                    <label class="payment-field reference-group" hidden><span>Referencia <b>*</b></span><input class="form-control form-control-sm reference" maxlength="150" placeholder="N.º de operación"></label>
                </div>
                <label class="proof-group payment-proof" hidden>
                    <span><i class="far fa-image" aria-hidden="true"></i> Comprobante <small>Opcional · JPG, PNG o GIF · máx. 2 MB</small></span>
                    <input class="form-control form-control-sm proof" type="file" accept="image/jpeg,image/png,image/gif">
                </label>`;
                const amountInput = row.querySelector('.amount');
                amountInput.value = value;
                amountInput.addEventListener('input', updateRows);
                row.querySelector('select').addEventListener('change', event => {
                    const noncash = event.target.value !== 'efectivo';
                    row.querySelector('.reference-group').hidden = !noncash;
                    row.querySelector('.proof-group').hidden = !noncash;
                    if (!noncash) {
                        row.querySelector('.reference').value = '';
                        row.querySelector('.proof').value = '';
                    }
                });
                row.querySelector('.remove').addEventListener('click', () => {
                    row.remove(); rows.splice(rows.indexOf(row), 1); updateRows();
                });
                container.appendChild(row); rows.push(row); updateRows();
            };
            add(amount.toFixed(2));
            htmlContainer.querySelector('#add-payment-part').addEventListener('click', () => {
                add();
                rows.at(-1).querySelector('select').focus();
            });
        },
        preConfirm: () => {
            const parts = []; const proofs = []; let total = 0; let cash = 0;
            rows.forEach(row => row.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid')));
            for (const [index, row] of rows.entries()) {
                const method = row.querySelector('select').value;
                const raw = row.querySelector('.amount').value;
                const reference = method === 'efectivo' ? '' : row.querySelector('.reference').value.trim();
                if (!/^\d+(\.\d{1,2})?$/.test(raw) || Number(raw) <= 0) {
                    const field = row.querySelector('.amount');
                    field.classList.add('is-invalid'); field.focus();
                    Swal.showValidationMessage(`Forma ${index + 1}: ingrese un importe mayor a 0, con máximo dos decimales.`); return false;
                }
                if (method !== 'efectivo' && !reference) {
                    const field = row.querySelector('.reference');
                    field.classList.add('is-invalid'); field.focus();
                    Swal.showValidationMessage(`Forma ${index + 1}: escriba la referencia del depósito o transferencia/QR.`); return false;
                }
                const cents = Math.round(Number(raw) * 100); total += cents; if (method === 'efectivo') cash += cents;
                parts.push({ forma_pago: method, monto: Number(raw).toFixed(2), referencia: reference });
                const file = method === 'efectivo' ? null : row.querySelector('.proof').files[0];
                if (file && !['image/jpeg','image/png','image/gif'].includes(file.type)) {
                    row.querySelector('.proof').classList.add('is-invalid');
                    Swal.showValidationMessage(`Forma ${index + 1}: el comprobante debe ser una imagen JPG, PNG o GIF.`); return false;
                }
                if (file && file.size > 2097152) {
                    row.querySelector('.proof').classList.add('is-invalid');
                    Swal.showValidationMessage(`Forma ${index + 1}: el comprobante supera el tamaño máximo de 2 MB.`); return false;
                }
                if (file) proofs.push({ index: parts.length - 1, file });
            }
            const delta = total - Math.round(amount * 100);
            if (!parts.length) {
                Swal.showValidationMessage('Agregue al menos una forma de pago.'); return false;
            }
            if (delta < 0) {
                Swal.showValidationMessage(`Faltan ${formatMoney(Math.abs(delta) / 100)} Bs para completar el total.`); return false;
            }
            if (!changeAllowed && delta > 0) {
                Swal.showValidationMessage(`La suma excede el total en ${formatMoney(delta / 100)} Bs. Ajuste los importes.`); return false;
            }
            if (delta > cash) {
                Swal.showValidationMessage('El cambio solo puede provenir del importe registrado en efectivo.'); return false;
            }
            return { parts, proofs };
        }
    });
    if (!result.isConfirmed) throw new axios.CanceledError('Operación cancelada');
    return result.value;
}

axios.interceptors.request.use(async config => {
    const path = config.url?.split('?')[0];
    if (config.method !== 'post' || !moneyPaths.has(path)) return config;
    const body = { ...config.data };
    if ((path.includes('anular') || path === '/solicitud/cancelar-especial') && !body.motivo) {
        const result = await Swal.fire({ target: document.querySelector('.modal.show') || document.body, title: 'Motivo de anulación', input: 'textarea', inputAttributes: { maxlength: 1000 }, showCancelButton: true,
            inputValidator: value => value.trim().length < 3 ? 'Ingrese el motivo de la anulación.' : undefined });
        if (!result.isConfirmed) throw new axios.CanceledError('Anulación cancelada');
        body.motivo = result.value.trim();
    }
    if (path === combinedDisbursementPath && (!body.partes_pago_adm || !body.partes_desembolso)) {
        const administrativeAmount = Number(body.monto_pago_adm || 0);
        const disbursementAmount = Number(body.monto_desembolso || 0);
        const administrative = await breakdown(administrativeAmount, false, 'Cobro administrativo');
        const disbursement = await breakdown(disbursementAmount, false, 'Desembolso del crédito');
        body.partes_pago_adm = administrative.parts;
        body.partes_desembolso = disbursement.parts;
        config.financialProofs = [
            ...administrative.proofs.map(proof => ({ ...proof, operation: 'pago_administrativo' })),
            ...disbursement.proofs.map(proof => ({ ...proof, operation: 'desembolso' })),
        ];
    } else if (mixedPaths.has(path) && !body.partes) {
        const amount = Number(body.monto_aplicado ?? body.monto_recibido ?? body.monto ?? 0);
        if (amount > 0) {
            const details = await breakdown(amount, ['/pagar_cuotas', '/caja/cobrar-orden-reprogramacion'].includes(path), paymentContexts[path]);
            body.partes = details.parts; config.financialProofs = details.proofs;
            if (path === '/pagar_cuotas') body.monto_aplicado = amount.toFixed(2);
        } else body.partes = [];
    }
    config.data = body;
    // Preserve the intent after network/5xx failures. The server also compares the full payload hash.
    const fingerprint = path + JSON.stringify(body);
    if (!pending.has(fingerprint)) pending.set(fingerprint, sessionStorage.getItem(storageKey(fingerprint)) || uuid());
    sessionStorage.setItem(storageKey(fingerprint), pending.get(fingerprint));
    config.headers['Idempotency-Key'] = pending.get(fingerprint);
    config.financialIntent = fingerprint;
    return config;
});

axios.interceptors.response.use(async response => {
    if (response.config.financialIntent) { pending.delete(response.config.financialIntent); sessionStorage.removeItem(storageKey(response.config.financialIntent)); }
    for (const proof of response.config.financialProofs || []) {
        try {
            const form = new FormData(); form.append('imagen', proof.file); form.append('indice', proof.index);
            const operationResult = proof.operation ? response.data[proof.operation] : response.data;
            await axios.post(`/operaciones/${operationResult.codigo_transaccion}/comprobante`, form);
        } catch {
            await Swal.fire('Operación registrada', 'El dinero se registró correctamente, pero no se pudo adjuntar la imagen opcional. Puede añadirla desde el historial.', 'warning');
        }
    }
    return response;
}, error => {
    if (error.response?.status >= 400 && error.response?.status < 500 && error.response.status !== 409) {
        pending.delete(error.config?.financialIntent);
        if (error.config?.financialIntent) sessionStorage.removeItem(storageKey(error.config.financialIntent));
    }
    return Promise.reject(error);
});
