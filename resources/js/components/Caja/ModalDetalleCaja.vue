<template>
    <div class="modal fade" id="modalDetalleCaja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="width:90%; max-width:90%;height:90%">
            <div class="modal-content border border-2 border-warning">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Detalles y Cierre de Caja</h5>
                    <button type="button" class="btn-close btn-close-dark" @click="cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <h6 class="fw-bold text-dark">Resumen de movimientos</h6>
                            <table v-if="resumen" class="table table-sm table-bordered">
                                <thead><tr><th>Forma de pago</th><th>Ingresos (Bs)</th><th>Egresos (Bs)</th></tr></thead>
                                <tbody>
                                    <tr v-for="(totales, forma) in formasPagoVisibles" :key="forma">
                                        <td>{{ { efectivo: 'Efectivo', deposito: 'Depósito', transferencia: 'Transferencia', sin_clasificar: 'Por revisar' }[forma] }}</td>
                                        <td>{{ format(totales.ingresos) }}</td><td>{{ format(totales.egresos) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-if="resumen?.requiere_revision" class="text-danger">Hay operaciones históricas que requieren revisión antes del cierre.</p>
                            <div v-if="caja.estado == 1 && puedeCerrar" class="mb-3">
                                <label class="form-label">Efectivo contado (Bs)</label>
                                <input v-model="efectivoContado" type="number" min="0" step="0.01" class="form-control">
                                <p v-if="efectivoContado !== ''" class="small mt-1">Diferencia: {{ format(Number(efectivoContado) - saldoFinal) }} Bs</p>
                            </div>
                            <table class="table table-sm table-bordered table-striped" style="font-size:12px">
                                <thead class="table-dark text-white">
                                    <tr>
                                        <th>Detalle</th>
                                        <th class="text-end">Valor (Bs)</th>
                                        <th class="text-center" style="width: 50px;">Ver</th>
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
                                        <td class="fs-6">SALDO EN CAJA</td>
                                        <td class="text-end fs-6">{{ format(saldoFinal) }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-7">
                            <h6 class="fw-bold text-dark">Últimos Movimientos</h6>
                            <div class="table-responsive" style="max-height: 90%; font-size:11px;">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="bg-light sticky-top table-dark">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Desc.</th>
                                            <th>Hora</th>
                                            <th class="text-end">Monto</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(mov, i) in movimientos" :key="i">
                                            <td>
                                                <span class="badge" :class="getBadgeClass(mov.tipo_movimiento)" style="min-width: 100px;">
                                                    {{ mov.tipo_movimiento }}
                                                </span>
                                            </td>
                                            <td class="text-uppercase">{{ mov.descripcion }}
                                                <span v-if="mov.anulado_at" class="badge bg-secondary d-block">Anulado</span>
                                            </td>
                                            <td>{{ formatHora(mov.fecha) }}</td>
                                            <td class="text-end fw-bold">{{ mov.monto }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <button class="btn btn-sm btn-outline-primary" @click="verOperacion(mov)">Ver detalle</button>
                                                    <button v-if="mov.codigo_operacion && ['cobro','ingreso','egreso','administrativo','desembolso','transferencia_caja'].includes(mov.tipo_operacion) && !mov.anulado_at && caja.estado == 1 && puedeCerrar && puedeAnular" class="btn btn-sm btn-outline-danger" @click="anularOperacion(mov.codigo_operacion)">Anular</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button v-if="caja.estado == 1 && puedeCerrar" @click="cerrarCaja" class="btn btn-danger shadow">
                        <i class="fas fa-lock me-2"></i> CERRAR CAJA DEFINITIVAMENTE
                    </button>
                    <button v-else class="btn btn-secondary" disabled>{{ caja.estado == 1 ? 'Solo consulta: caja de otro usuario' : 'Caja Cerrada' }}</button>
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
            if (this.resumen) return Number(this.resumen.efectivo_esperado);
            return (parseFloat(this.caja.monto_inicial || 0) + this.totalIngresos) - this.totalEgresos;
        }
    },
    methods: {
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
    .table-responsive .badge{
        border-radius:20px;
        min-width:100px;
    }
</style>
