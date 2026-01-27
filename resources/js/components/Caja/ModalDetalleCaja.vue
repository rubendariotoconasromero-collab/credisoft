<template>
    <div class="modal fade" id="modalDetalleCaja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="width:90%; max-width:90%;">
            <div class="modal-content border border-2 border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Detalles y Cierre de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <table class="table table-sm table-bordered table-striped" style="font-size:12px">
                                <thead class="bg-success text-white">
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
                            <h6 class="fw-bold text-success">Últimos Movimientos</h6>
                            <div class="table-responsive" style="max-height: 400px; font-size:11px;">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Desc.</th>
                                            <th>Hora</th>
                                            <th class="text-end">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(mov, i) in movimientos" :key="i">
                                            <td>
                                                <span class="badge" :class="getBadgeClass(mov.tipo_movimiento)">
                                                    {{ mov.tipo_movimiento }}
                                                </span>
                                            </td>
                                            <td>{{ mov.descripcion }}</td>
                                            <td>{{ formatHora(mov.fecha) }}</td>
                                            <td class="text-end fw-bold">{{ mov.monto }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button v-if="caja.estado == 1" @click="cerrarCaja" class="btn btn-danger btn-lg shadow">
                        <i class="fas fa-lock me-2"></i> CERRAR CAJA DEFINITIVAMENTE
                    </button>
                    <button v-else class="btn btn-secondary" disabled>Caja Cerrada</button>
                </div>
            </div>
        </div>
        
        <ModalListaMovimientos ref="modalListaRef" />
    </div>
</template>

<script>
import ModalListaMovimientos from './ModalListaMovimientos.vue';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';

export default {
    components: { ModalListaMovimientos },
    data() {
        return {
            caja: {},
            movimientos: [],
            loading: false
        }
    },
    computed: {
        totalIngresos() {
            return parseFloat(this.caja.ingreso_total || 0) + 
                   parseFloat(this.caja.ingreso_total_ingreso || 0) + 
                   parseFloat(this.caja.pago_administrativo_total || 0) +
                   parseFloat(this.caja.ingreso_total_amortizacion || 0);
        },
        totalEgresos() {
            return parseFloat(this.caja.egreso_total || 0) + 
                   parseFloat(this.caja.desembolso_total || 0); // Asumiendo que desembolso está separado o incluido según tu backend
        },
        saldoFinal() {
            return (parseFloat(this.caja.monto_inicial || 0) + this.totalIngresos) - this.totalEgresos;
        }
    },
    methods: {
        async abrir(itemCaja) {
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
                // Aquí usamos tu endpoint de movimientos para llenar la tabla derecha
                const res = await axios.get('/movimientos_caja', { params: { id_caja: id, per_page: 50 } });
                this.movimientos = res.data.movimientos.data;
                // Si necesitas refrescar los totales de la caja (porque el item de la lista puede estar viejo),
                // deberías llamar a un endpoint get_caja_detalle específico aquí.
            } catch (e) {
                console.error(e);
            }
        },
        async cerrarCaja() {
            const check = await axios.get('/caja_abierta');
            // Validar que soy el dueño
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
                        await axios.post('/close_caja', { id_caja: this.caja.id, monto_final: this.saldoFinal });
                        Swal.fire('Cerrada', 'La caja se ha cerrado correctamente.', 'success');
                        this.cerrar();
                        this.$emit('cerrada');
                    } catch (e) {
                        Swal.fire('Error', 'No se pudo cerrar la caja.', 'error');
                    }
                }
            });
        },
        format(val) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(val || 0);
        },
        formatHora(f) { return moment(f).format('HH:mm'); },
        getBadgeClass(tipo) {
            if(tipo.includes('Ingreso') || tipo.includes('Cobro')) return 'bg-success';
            return 'bg-danger';
        }
    }
}
</script>