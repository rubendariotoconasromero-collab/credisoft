import axios from 'axios';
import Swal from 'sweetalert2';

const mixedPaths = new Set(['/pagar_cuotas', '/save_ingreso', '/save_gasto', '/guardar_pago_adm', '/actualizar_desembolso']);
const moneyPaths = new Set([...mixedPaths, '/caja/aperturar', '/close_caja', '/ingresar_boveda', '/retirar_boveda', '/aperturar_boveda', '/anular_planpago', '/anular_pago', '/anular_ingreso', '/anular_gasto', '/anular_desembolso', '/anular_ingreso_caja', '/anular_egreso_caja', '/pagos/anular-transaccion']);
const uuid = () => 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
    const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
    return (c === 'x' ? r : (r & 3) | 8).toString(16);
});
const pending = new Map();
const storageKey = text => {
    let a = 2166136261, b = 5381;
    for (const character of text) { a = Math.imul(a ^ character.charCodeAt(0), 16777619); b = Math.imul(b, 33) ^ character.charCodeAt(0); }
    return `credisoft-intento-${a >>> 0}-${b >>> 0}`;
};

async function breakdown(amount, changeAllowed) {
    const rows = [];
    const result = await Swal.fire({ target: document.querySelector('.modal.show') || document.body,
        title: 'Formas de pago', width: 780,
        html: `<p>Total a aplicar: <strong>${amount.toFixed(2)} Bs</strong></p><div id="payment-parts"></div><button id="add-payment-part" type="button" class="btn btn-sm btn-outline-primary mt-2">Agregar otra forma</button><p class="small mt-2">${changeAllowed ? 'Si entrega más efectivo, se calculará el cambio.' : 'La suma debe coincidir con el total.'} Depósitos y transferencias requieren referencia.</p>`,
        showCancelButton: true, confirmButtonText: 'Registrar operación', cancelButtonText: 'Cancelar', focusConfirm: false,
        didOpen: () => {
            const container = Swal.getHtmlContainer().querySelector('#payment-parts');
            const add = (value = '') => {
                const row = document.createElement('div');
                row.className = 'border rounded p-2 mb-2 text-start';
                row.innerHTML = `<div class="row g-2"><div class="col-sm-4"><label class="form-label small">Forma</label><select class="form-select form-select-sm"><option value="efectivo">Efectivo</option><option value="deposito">Depósito</option><option value="transferencia">Transferencia / QR</option></select></div><div class="col-sm-3"><label class="form-label small">Importe (Bs)</label><input class="form-control form-control-sm amount" type="number" min="0.01" step="0.01"></div><div class="col-sm-4 reference-group" hidden><label class="form-label small">Referencia</label><input class="form-control form-control-sm reference" maxlength="150"></div><div class="col-sm-1"><button type="button" class="btn btn-sm btn-outline-danger remove" aria-label="Quitar forma">×</button></div></div><div class="proof-group" hidden><label class="small mt-2">Imagen opcional del comprobante (JPG, PNG, GIF; máximo 2 MB)</label><input class="form-control form-control-sm proof" type="file" accept="image/jpeg,image/png,image/gif"></div>`;
                row.querySelector('.amount').value = value;
                row.querySelector('select').onchange = event => {
                    const noncash = event.target.value !== 'efectivo';
                    row.querySelector('.reference-group').hidden = !noncash;
                    row.querySelector('.proof-group').hidden = !noncash;
                    if (!noncash) {
                        row.querySelector('.reference').value = '';
                        row.querySelector('.proof').value = '';
                    }
                };
                row.querySelector('.remove').onclick = () => { row.remove(); rows.splice(rows.indexOf(row), 1); };
                container.appendChild(row); rows.push(row);
            };
            add(amount.toFixed(2));
            Swal.getHtmlContainer().querySelector('#add-payment-part').onclick = () => add();
        },
        preConfirm: () => {
            const parts = []; const proofs = []; let total = 0; let cash = 0;
            for (const row of rows) {
                const method = row.querySelector('select').value;
                const raw = row.querySelector('.amount').value;
                const reference = method === 'efectivo' ? '' : row.querySelector('.reference').value.trim();
                if (!/^\d+(\.\d{1,2})?$/.test(raw) || Number(raw) <= 0 || (method !== 'efectivo' && !reference)) {
                    Swal.showValidationMessage('Ingrese importes positivos con hasta dos decimales y referencias para los medios no efectivos.'); return false;
                }
                const cents = Math.round(Number(raw) * 100); total += cents; if (method === 'efectivo') cash += cents;
                parts.push({ forma_pago: method, monto: Number(raw).toFixed(2), referencia: reference });
                const file = method === 'efectivo' ? null : row.querySelector('.proof').files[0];
                if (file && (file.size > 2097152 || !['image/jpeg','image/png','image/gif'].includes(file.type))) { Swal.showValidationMessage('El comprobante debe ser una imagen de hasta 2 MB.'); return false; }
                if (file) proofs.push({ index: parts.length - 1, file });
            }
            const delta = total - Math.round(amount * 100);
            if (!parts.length || delta < 0 || (!changeAllowed && delta !== 0) || delta > cash) {
                Swal.showValidationMessage('El desglose no coincide. Solo puede existir cambio procedente de efectivo.'); return false;
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
    if (path.includes('anular') && !body.motivo) {
        const result = await Swal.fire({ target: document.querySelector('.modal.show') || document.body, title: 'Motivo de anulación', input: 'textarea', inputAttributes: { maxlength: 1000 }, showCancelButton: true,
            inputValidator: value => value.trim().length < 3 ? 'Ingrese el motivo de la anulación.' : undefined });
        if (!result.isConfirmed) throw new axios.CanceledError('Anulación cancelada');
        body.motivo = result.value.trim();
    }
    if (mixedPaths.has(path) && !body.partes) {
        const amount = Number(body.monto_aplicado ?? body.monto_recibido ?? body.monto ?? 0);
        if (amount > 0) {
            const details = await breakdown(amount, path === '/pagar_cuotas');
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
            await axios.post(`/operaciones/${response.data.codigo_transaccion}/comprobante`, form);
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
