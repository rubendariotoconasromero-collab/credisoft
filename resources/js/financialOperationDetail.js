import axios from 'axios';
import Swal from 'sweetalert2';

const money = value => new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
const labels = { efectivo: 'Efectivo', deposito: 'Depósito', transferencia: 'Transferencia / QR' };
const element = (tag, text, className = '') => {
    const node = document.createElement(tag);
    node.textContent = text;
    node.className = className;
    return node;
};
const link = (text, href) => {
    const node = element('a', text);
    node.href = href;
    node.target = '_blank';
    node.rel = 'noopener';
    return node;
};

export async function mostrarDetalleOperacion(movement, target) {
    let operation;
    try {
        if (movement.codigo_operacion) {
            operation = (await axios.get(`/operaciones/${movement.codigo_operacion}/detalle`)).data;
        }
    } catch (error) {
        await Swal.fire({ target, icon: 'error', title: 'No se pudo cargar el detalle', text: error.response?.data?.message || 'Intente nuevamente.' });
        return;
    }
    const content = element('div', '', 'text-start');
    content.append(element('p', movement.descripcion));
    content.append(element('p', `Total: ${money(operation?.monto ?? movement.monto)} Bs`, 'fw-bold'));
    if (!operation) {
        if (movement.fecha) content.append(element('p', `Fecha: ${movement.fecha}`));
        if (movement.estado != null) content.append(element('p', `Estado: ${Number(movement.estado) === 1 ? 'Registrado' : 'Anulado'}`));
        if (movement.id_caja) content.append(element('p', `Caja: ${movement.id_caja}`));
        content.append(element('p', 'Movimiento histórico sin desglose de formas de pago vinculado. No se pueden adjuntar comprobantes desde este registro.', 'text-muted'));
    } else {
        content.append(element('p', `Fecha: ${operation.fecha} · ${operation.anulado_at ? 'Anulado' : 'Registrado'}`));
        if (Number(operation.cambio)) content.append(element('p', `Cambio en efectivo: ${money(operation.cambio)} Bs`));
        const responsive = element('div', '', 'table-responsive');
        const table = element('table', '', 'table table-bordered align-middle');
        const head = element('thead', '');
        const header = element('tr', '');
        for (const title of ['Forma de pago', 'Importe aplicado (Bs)', 'Referencia', 'Comprobante']) header.append(element('th', title));
        head.append(header);
        const body = element('tbody', '');
        for (const part of operation.partes) {
            const row = element('tr', '');
            const noncash = ['deposito', 'transferencia'].includes(part.forma_pago);
            row.append(element('td', labels[part.forma_pago] || part.forma_pago));
            row.append(element('td', money(part.monto), 'text-end'));
            row.append(element('td', noncash ? (part.referencia || 'Sin referencia registrada') : 'No corresponde'));
            const proofCell = element('td', '');
            if (part.comprobante_url) {
                proofCell.append(link('Ver imagen', part.comprobante_url));
            } else if (part.puede_adjuntar && noncash) {
                const input = element('input', '', 'form-control form-control-sm mb-2');
                input.type = 'file';
                input.accept = 'image/jpeg,image/png,image/gif';
                input.setAttribute('aria-label', `Imagen para ${labels[part.forma_pago]}, componente ${part.indice + 1}`);
                const button = element('button', 'Adjuntar imagen', 'btn btn-sm btn-outline-primary');
                button.type = 'button';
                const status = element('p', '', 'small mt-2 mb-0');
                status.setAttribute('role', 'status');
                button.onclick = async () => {
                    const file = input.files[0];
                    status.className = 'small text-danger mt-2 mb-0';
                    if (!file || file.size > 2097152 || !['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                        status.textContent = 'Seleccione una imagen JPG, PNG o GIF de hasta 2 MB.';
                        return;
                    }
                    input.disabled = button.disabled = true;
                    status.textContent = 'Guardando imagen…';
                    Swal.getCloseButton().disabled = true;
                    const data = new FormData();
                    data.append('indice', part.indice);
                    data.append('imagen', file);
                    try {
                        await axios.post(`/operaciones/${operation.codigo}/comprobante`, data);
                        proofCell.replaceChildren(link('Ver imagen', `/operaciones/${operation.codigo}/comprobante/${part.indice}`));
                        proofCell.append(element('p', 'Imagen guardada', 'small text-success mb-0'));
                    } catch (error) {
                        status.textContent = error.response?.data?.message || 'No se pudo adjuntar. Cierre y vuelva a abrir el detalle para comprobar el estado antes de reintentar.';
                        input.disabled = button.disabled = false;
                    } finally {
                        if (Swal.getCloseButton()) Swal.getCloseButton().disabled = false;
                    }
                };
                proofCell.append(input, button, status);
            } else {
                proofCell.textContent = noncash ? 'Sin imagen adjunta' : 'No corresponde';
            }
            row.append(proofCell);
            body.append(row);
        }
        table.append(head, body);
        responsive.append(table);
        content.append(responsive);
        if (!operation.partes.length) content.append(element('p', 'Este movimiento no tiene componentes de pago registrados.', 'text-muted'));
        content.append(element('p', 'La imagen es opcional y corresponde únicamente al depósito o transferencia/QR de su fila.', 'small text-muted'));
        content.append(link('Ver recibo PDF', `/operaciones/${operation.codigo}/recibo`));
    }
    await Swal.fire({ target, title: 'Detalle del movimiento', html: content, width: 900,
        showConfirmButton: false, showCloseButton: true, closeButtonAriaLabel: 'Cerrar detalle',
        allowOutsideClick: false, allowEscapeKey: false });
}
