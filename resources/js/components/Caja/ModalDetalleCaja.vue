<template>
    <div class="modal fade" id="modalDetalleCaja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable cash-detail-dialog">
            <div class="modal-content cash-detail-modal">
                <div class="modal-header cash-detail-header">
                    <h5 class="modal-title"><i class="fas fa-cash-register me-2" aria-hidden="true"></i>Detalles y cierre de caja</h5>
                    <button type="button" class="btn-close" aria-label="Cerrar" @click="cerrar"></button>
                </div>
                <div class="modal-body cash-detail-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <section class="cash-section mb-2">
                            <h6 class="cash-section-title"><i class="fas fa-chart-pie" aria-hidden="true"></i> Resumen por forma de pago</h6>
                            <table v-if="resumen" class="table table-sm mb-0 payment-summary-table">
                                <thead><tr><th>Forma de pago</th><th>Ingresos (Bs)</th><th>Egresos (Bs)</th></tr></thead>
                                <tbody>
                                    <tr v-for="(totales, forma) in formasPagoVisibles" :key="forma">
                                        <td>{{ { efectivo: 'Efectivo', deposito: 'Depósito', transferencia: 'Transferencia', sin_clasificar: 'Por revisar' }[forma] }}</td>
                                        <td>{{ format(totales.ingresos) }}</td><td>{{ format(totales.egresos) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            </section>
                            <div v-if="resumen?.requiere_revision" class="cash-review-alert">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                <span>Existen operaciones históricas que deben revisarse antes del cierre.</span>
                            </div>
                            <div v-if="caja.estado == 1 && puedeCerrar" class="cash-count-card">
                                <div class="cash-count-input">
                                    <label for="cash-counted">Efectivo contado <span>(Bs)</span></label>
                                    <input id="cash-counted" v-model="efectivoContado" type="number" min="0" step="0.01" class="form-control form-control-sm" placeholder="0,00">
                                </div>
                                <div class="cash-count-stat">
                                    <span>Esperado</span><strong>{{ format(efectivoEsperado) }} Bs</strong>
                                </div>
                                <div v-if="efectivoContado !== ''" class="cash-count-stat" :class="Number(efectivoContado) - efectivoEsperado === 0 ? 'is-balanced' : 'is-different'">
                                    <span>Diferencia</span><strong>{{ format(Number(efectivoContado) - efectivoEsperado) }} Bs</strong>
                                </div>
                            </div>
                            <section class="cash-section">
                            <h6 class="cash-section-title"><i class="fas fa-list-ul" aria-hidden="true"></i> Composición del saldo</h6>
                            <table class="table table-sm table-striped mb-0 cash-breakdown-table">
                                <thead>
                                    <tr>
                                        <th>Detalle</th>
                                        <th class="text-end">Valor (Bs)</th>
                                        <th class="text-center cash-view-column">Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Monto Inicial</td>
                                        <td class="text-end fw-bold">{{ format(caja.monto_inicial) }}</td>
                                        <td></td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="ps-3"><i class="fas fa-plus text-success me-1"></i> Cobro Cuotas</td>
                                        <td class="text-end">{{ format(caja.ingreso_total) }}</td>
                                        <td class="text-center">
                                            <button v-if="caja.ingreso_total > 0" 
                                                    @click="verDetalle('cobros_cuotas')" 
                                                    class="btn btn-sm btn-outline-primary py-0 px-1 border-0">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3"><i class="fas fa-plus text-success me-1"></i> Ingresos Varios</td>
                                        <td class="text-end">{{ format(caja.ingreso_total_ingreso) }}</td>
                                        <td class="text-center">
                                            <button v-if="caja.ingreso_total_ingreso > 0" 
                                                    @click="verDetalle('ingresos_corrientes')" 
                                                    class="btn btn-sm btn-outline-primary py-0 px-1 border-0">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="ps-3"><i class="fas fa-plus text-success me-1"></i> Pagos Adm.</td>
                                        <td class="text-end">{{ format(caja.pago_administrativo_total) }}</td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3"><i class="fas fa-minus text-danger me-1"></i> Desembolsos</td>
                                        <td class="text-end">{{ format(caja.desembolso_total) }}</td>
                                        <td class="text-center">
                                            <button v-if="caja.desembolso_total > 0" 
                                                    @click="verDetalle('desembolsos')" 
                                                    class="btn btn-sm btn-outline-primary py-0 px-1 border-0">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3"><i class="fas fa-minus text-danger me-1"></i> Gastos Varios</td>
                                        <td class="text-end">{{ format(caja.egreso_total) }}</td>
                                        <td class="text-center">
                                            <button v-if="caja.egreso_total > 0" 
                                                    @click="verDetalle('gastos_corrientes')" 
                                                    class="btn btn-sm btn-outline-primary py-0 px-1 border-0">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <tr class="table-secondary fw-bold">
                                        <td>TOTAL INGRESOS</td>
                                        <td class="text-end text-success">{{ format(totalIngresos) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-secondary fw-bold">
                                        <td>TOTAL EGRESOS</td>
                                        <td class="text-end text-danger">{{ format(totalEgresos) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-warning text-dark fw-bold border-top border-dark">
                                        <td>SALDO EN CAJA</td>
                                        <td class="text-end">{{ format(saldoFinal) }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            </section>
                        </div>

                        <div class="col-lg-7">
                            <section class="cash-section movements-section">
                            <div class="cash-section-heading">
                                <h6 class="cash-section-title mb-0"><i class="fas fa-exchange-alt" aria-hidden="true"></i> Últimos movimientos</h6>
                                <span class="movement-count">{{ movimientos.length }} registros</span>
                            </div>
                            <div class="table-responsive movements-table-wrap">
                                <table class="table table-sm table-striped mb-0 movements-table">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Hora</th>
                                            <th class="text-end">Monto</th>
                                            <th class="text-center movement-actions-column">Op.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(mov, i) in movimientos" :key="mov.id || i">
                                            <td>
                                                <span class="badge movement-type-badge" :class="getBadgeClass(mov.tipo_movimiento)">
                                                    {{ mov.tipo_movimiento }}
                                                </span>
                                            </td>
                                            <td class="movement-description" :title="mov.descripcion">{{ mov.descripcion }}
                                                <span v-if="mov.anulado_at" class="badge bg-secondary ms-1">Anulado</span>
                                            </td>
                                            <td>{{ formatHora(mov.fecha) }}</td>
                                            <td class="text-end fw-bold font-monospace">{{ format(mov.monto) }}</td>
                                            <td class="text-center">
                                                <div class="btn-group movement-actions">
                                                    <button type="button" class="movement-menu-button dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" :aria-label="`Acciones del movimiento ${i + 1}`">
                                                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1">
                                                        <li>
                                                            <a class="dropdown-item text-primary" href="#" @click.prevent="verOperacion(mov)">
                                                                <i class="fas fa-eye me-2" aria-hidden="true"></i>Ver detalle
                                                            </a>
                                                        </li>
                                                        <li v-if="puedeAnularMovimiento(mov)">
                                                            <a class="dropdown-item text-danger" href="#" @click.prevent="anularOperacion(mov.codigo_operacion)">
                                                                <i class="fas fa-times-circle me-2" aria-hidden="true"></i>Anular
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="movimientos.length === 0">
                                            <td colspan="5" class="movement-empty">
                                                <i class="fas fa-inbox" aria-hidden="true"></i>
                                                <span>No hay movimientos registrados.</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            </section>
                        </div>
                    </div>
                </div>
                <div class="modal-footer cash-detail-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="cerrar">Cancelar</button>
                    <button v-if="caja.estado == 1 && puedeCerrar" @click="cerrarCaja" class="btn btn-danger">
                        <i class="fas fa-lock me-2"></i>Cerrar caja definitivamente
                    </button>
                    <span v-else class="cash-readonly-status"><i class="fas fa-info-circle me-1"></i>{{ caja.estado == 1 ? 'Solo consulta: caja de otro usuario' : 'Caja cerrada' }}</span>
                </div>
            </div>
        </div>
        
        <ModalListaMovimientos ref="modalListaRef" />
    </div>
</template>

<script>
import ModalListaMovimientos from './ModalListaMovimientos.vue';
import { mostrarDetalleOperacion } from '../../financialOperationDetail';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';
import { sesion, tienePermiso } from '../../router/session';

export default {
    components: { ModalListaMovimientos },
    data() {
        return {
            caja: {},
            resumen: null,
            efectivoContado: '',
            movimientos: [],
            loading: false
        }
    },
    computed: {
        formasPagoVisibles() {
            return Object.fromEntries(
                Object.entries(this.resumen?.formas_pago || {}).filter(([forma, totales]) =>
                    forma !== 'sin_clasificar' || Number(totales.ingresos) !== 0 || Number(totales.egresos) !== 0
                )
            );
        },
        puedeAnular() { return tienePermiso('anular_finanzas'); },
        puedeCerrar() { return tienePermiso('controlcaja') && this.caja.usuario === sesion.name; },
        totalIngresos() {
            if (this.resumen) return Number(this.resumen.total_ingresos);
            return parseFloat(this.caja.ingreso_total || 0) + 
                   parseFloat(this.caja.ingreso_total_ingreso || 0) + 
                   parseFloat(this.caja.pago_administrativo_total || 0) +
                   parseFloat(this.caja.ingreso_total_amortizacion || 0);
        },
        totalEgresos() {
            if (this.resumen) return Number(this.resumen.total_egresos);
            return parseFloat(this.caja.egreso_total || 0) + 
                   parseFloat(this.caja.desembolso_total || 0); // Asumiendo que desembolso está separado o incluido según tu backend
        },
        saldoFinal() {
            if (this.resumen) return Number(this.resumen.saldo_disponible);
            return (parseFloat(this.caja.monto_inicial || 0) + this.totalIngresos) - this.totalEgresos;
        },
        efectivoEsperado() {
            if (this.resumen) return Number(this.resumen.efectivo_esperado);
            return this.saldoFinal;
        }
    },
    methods: {
        puedeAnularMovimiento(movimiento) {
            return Boolean(
                movimiento.codigo_operacion &&
                ['cobro', 'ingreso', 'egreso', 'administrativo', 'desembolso', 'transferencia_caja'].includes(movimiento.tipo_operacion) &&
                !movimiento.anulado_at &&
                this.caja.estado == 1 &&
                this.puedeCerrar &&
                this.puedeAnular
            );
        },
        async anularOperacion(codigo) {
            try { await axios.post('/pagos/anular-transaccion', { codigo }); await this.cargarDetallesFrescos(this.caja.id); }
            catch (e) { if (!axios.isCancel(e)) Swal.fire('No se pudo anular', e.response?.data?.message || e.message, 'error'); }
        },
        async verOperacion(movimiento) {
            await mostrarDetalleOperacion(movimiento, this.$el);
        },
        async abrir(itemCaja) {
            this.resumen = null;
            this.efectivoContado = '';
            this.caja = { ...itemCaja }; // Copia básica
            $('#modalDetalleCaja').modal('show');
            await this.cargarDetallesFrescos(itemCaja.id);
        },
        cerrar() {
            $('#modalDetalleCaja').modal('hide');
        },

        verDetalle(tipo) {
            // Llamamos al hijo pasando el tipo y el ID de la caja actual
            this.$refs.modalListaRef.abrir(tipo, this.caja.id);
        },

        async cargarDetallesFrescos(id) {
            try {
                const [res, summary] = await Promise.all([
                    axios.get('/movimientos_caja', { params: { id_caja: id, per_page: 50 } }),
                    axios.get('/caja/resumen-financiero', { params: { id_caja: id } })
                ]);
                this.resumen = summary.data;
                this.movimientos = res.data.movimientos.data;
                // Si necesitas refrescar los totales de la caja (porque el item de la lista puede estar viejo),
                // deberías llamar a un endpoint get_caja_detalle específico aquí.
            } catch (e) {
                console.error(e);
            }
        },
        async cerrarCaja() {
            try {
                this.resumen = (await axios.get('/caja/resumen-financiero', { params: { id_caja: this.caja.id } })).data;
                const check = await axios.get('/caja_abierta');
                if (check.data.usuario_actual != 1) { 
                    Swal.fire('Error', 'Solo el usuario que abrió la caja puede cerrarla.', 'error');
                    return;
                }
                Swal.fire({
                    title: '¿Cerrar Caja?',
                    text: `El saldo final calculado es: ${this.format(this.saldoFinal)}. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Cerrar',
                    confirmButtonColor: '#d33'
                }).then(async (res) => {
                    if (res.isConfirmed) {
                        try {
                            const payload = { id_caja: this.caja.id, efectivo_contado: this.efectivoContado };
                            await axios.post('/close_caja', payload);
                            Swal.fire('Cerrada', 'La caja se ha cerrado correctamente.', 'success');
                            this.cerrar();
                            this.$emit('cerrada');
                            
                        } catch (e) {
                            console.error("Error al cerrar caja:", e);
                            Swal.fire('Error', e.response?.data?.message || 'No se pudo cerrar la caja.', 'error');
                        }
                    }
                });
            } catch (error) {
                Swal.fire('Error', 'No se pudo verificar el estado de la caja.', 'error');
            }
        },
        format(val) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(val || 0);
        },
        formatHora(f) { return moment(f).format('HH:mm'); },
        getBadgeClass(tipo) {
            if(tipo.includes('ingreso') || tipo.includes('Cobro')) return 'bg-success';
            return 'bg-danger';
        }
    }
}
</script>

<style scoped>
    .cash-detail-dialog {
        width: calc(100% - 32px);
        max-width: 1180px;
        margin: 16px auto;
    }

    .cash-detail-modal {
        max-height: calc(100vh - 32px);
        border: 1px solid #e6a73f;
        border-radius: 7px;
        overflow: hidden;
    }

    .cash-detail-header {
        min-height: 43px;
        padding: 10px 16px;
        border: 0;
        background: #f4b247;
        color: #20252a;
    }

    .cash-detail-header .modal-title {
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 1.8px;
        text-transform: uppercase;
    }

    .cash-detail-header .btn-close {
        font-size: 11px;
    }

    .cash-detail-body {
        padding: 12px 14px;
        background: #f8f9fa;
    }

    .cash-section {
        border: 1px solid #dfe3e6;
        border-radius: 5px;
        overflow: hidden;
        background: #fff;
    }

    .cash-section-title,
    .cash-section-heading {
        min-height: 34px;
        margin: 0;
        padding: 8px 10px;
        border-bottom: 1px solid #e5e8ea;
        background: #f1f3f4;
        color: #30363b;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .cash-section-title i {
        margin-right: 5px;
        color: #198754;
    }

    .cash-section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cash-section-heading .cash-section-title {
        min-height: 0;
        padding: 0;
        border: 0;
    }

    .movement-count {
        padding: 2px 7px;
        border-radius: 10px;
        background: #e2e6e9;
        color: #667078;
        font-size: 9px;
        letter-spacing: 0;
        text-transform: none;
    }

    .payment-summary-table,
    .cash-breakdown-table,
    .movements-table {
        font-size: 10.5px;
    }

    .payment-summary-table th,
    .cash-breakdown-table th,
    .movements-table th {
        padding: 7px 9px;
        border-color: #e3e6e8;
        background: #343a40;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .payment-summary-table td,
    .cash-breakdown-table td,
    .movements-table td {
        padding: 6px 9px;
        border-color: #e8eaec;
        vertical-align: middle;
    }

    .payment-summary-table th:not(:first-child),
    .payment-summary-table td:not(:first-child) {
        text-align: right;
    }

    .cash-review-alert {
        display: flex;
        gap: 7px;
        margin: 8px 0;
        padding: 7px 9px;
        border: 1px solid #f0c4c8;
        border-radius: 4px;
        background: #fff5f6;
        color: #a72d38;
        font-size: 10px;
        line-height: 1.4;
    }

    .cash-count-card {
        display: grid;
        grid-template-columns: 1.2fr .9fr .9fr;
        align-items: end;
        gap: 8px;
        margin: 8px 0;
        padding: 8px 10px;
        border: 1px solid #dce8e2;
        border-left: 3px solid #198754;
        border-radius: 4px;
        background: #f5faf7;
    }

    .cash-count-input label,
    .cash-count-stat span {
        display: block;
        margin-bottom: 3px;
        color: #6b7379;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .cash-count-input label span {
        color: #899096;
    }

    .cash-count-input .form-control {
        min-height: 31px;
        border-color: #b9c8c0;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 700;
        text-align: right;
        box-shadow: none;
    }

    .cash-count-input .form-control:focus {
        border-color: #198754;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, .12);
    }

    .cash-count-stat {
        padding: 5px 8px;
        border-left: 1px solid #d7e0db;
    }

    .cash-count-stat strong {
        color: #30363b;
        font-size: 11px;
        white-space: nowrap;
    }

    .cash-count-stat.is-balanced strong { color: #198754; }
    .cash-count-stat.is-different strong { color: #dc3545; }

    .cash-breakdown-table .cash-view-column {
        width: 42px;
    }

    .cash-breakdown-table .btn {
        color: #198754;
        font-size: 10px;
    }

    .cash-breakdown-table tr:last-child td {
        border-top: 2px solid #e6a73f;
        background: #fff6e6 !important;
        font-size: 12px;
    }

    .movements-section {
        overflow: visible;
    }

    .movements-table-wrap {
        max-height: 475px;
        min-height: 190px;
        overflow: auto;
    }

    .movements-table {
        table-layout: fixed;
    }

    .movements-table th:nth-child(1) { width: 90px; }
    .movements-table th:nth-child(3) { width: 58px; }
    .movements-table th:nth-child(4) { width: 92px; }
    .movements-table .movement-actions-column { width: 46px; }

    .movement-type-badge {
        min-width: 70px;
        padding: 3px 7px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .movement-description {
        overflow: hidden;
        color: #5e666d;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .movement-description .badge {
        min-width: 0;
        border-radius: 8px;
        font-size: 7px;
    }

    .movement-menu-button {
        width: 29px;
        height: 27px;
        border: 1px solid transparent;
        border-radius: 3px;
        background: transparent;
        color: #198754;
        font-size: 14px;
    }

    .movement-menu-button:hover,
    .movement-menu-button:focus-visible,
    .movement-menu-button[aria-expanded="true"] {
        border-color: #badbcc;
        background: #eaf6f0;
        outline: none;
    }

    .movement-menu-button::after {
        display: none;
    }

    .movement-actions .dropdown-menu {
        min-width: 135px;
        font-size: 10px;
    }

    .movement-actions .dropdown-item {
        padding: 6px 10px;
    }

    .movement-empty {
        height: 150px;
        color: #8a9197;
        text-align: center;
    }

    .movement-empty i {
        display: block;
        margin-bottom: 6px;
        color: #adb3b8;
        font-size: 22px;
    }

    .cash-detail-footer {
        justify-content: flex-end;
        gap: 7px;
        min-height: 52px;
        padding: 9px 14px;
        border-top-color: #e3e6e8;
        background: #fff;
    }

    .cash-detail-footer .btn {
        min-height: 32px;
        margin: 0;
        padding: 6px 12px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 700;
    }

    .cash-readonly-status {
        padding: 6px 9px;
        border-radius: 3px;
        background: #eef0f2;
        color: #687078;
        font-size: 10px;
    }

    @media (max-width: 991.98px) {
        .cash-detail-dialog { width: calc(100% - 20px); margin: 10px auto; }
        .cash-detail-modal { max-height: calc(100vh - 20px); }
        .movements-table-wrap { max-height: 330px; }
    }

    @media (max-width: 575.98px) {
        .cash-detail-body { padding: 9px; }
        .cash-count-card { grid-template-columns: 1fr 1fr; }
        .cash-count-input { grid-column: 1 / -1; }
        .cash-count-stat:first-of-type { border-left: 0; }
        .cash-detail-footer { flex-wrap: nowrap; }
        .cash-detail-footer .btn-danger { flex: 1; }
    }
</style>
