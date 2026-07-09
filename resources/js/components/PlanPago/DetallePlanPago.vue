<template>
    <div class="card border-0 shadow-lg">
        
        <div class="card-header bg-warning py-3 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 text-center">
                <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                    <i class="fas fa-list-alt me-2"></i> Detalle del Plan de Pago
                </h5>
            </div>
            <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-dark shadow-none"></button>
        </div>

        <div class="card-body p-4 bg-light">
            
            <div v-if="plan.id_solicitud_origen" class="alert alert-warning d-flex align-items-center justify-content-between py-2 px-3 mb-4 shadow-sm border-warning">
                <div class="small text-dark">
                    <i class="fas fa-history fa-lg me-2"></i>
                    <strong>Antecedente:</strong> Este crédito proviene de una reprogramación o refinanciamiento anterior.
                </div>
                <button @click="$emit('ver-original', plan.id_solicitud_origen)" 
                        class="btn btn-warning btn-sm fw-bold text-dark border border-dark shadow-sm">
                    <i class="fas fa-search me-1"></i> Ver Crédito Original
                </button>
            </div>

            <div class="row g-4 mb-4">
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom pb-2 pt-3">
                            <h6 class="fw-bold text-success mb-0 text-dark text-uppercase">
                                <i class="fas fa-user-tie me-2"></i> Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <img @click="$emit('ver-ficha', plan.id_cliente)" 
                                     :src="plan.imagen_cliente ? '/img/cliente/' + plan.imagen_cliente : '/img/empresa/user_img2_old.png'" 
                                     class="rounded-circle border border-2 border-success p-1 shadow-sm hover-zoom" 
                                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;" 
                                     title="Ver ficha del cliente"
                                     alt="Cliente">
                                <div class="ms-3">
                                    <h5 class="fw-bold text-dark mb-1 text-uppercase">{{ plan.cliente }}</h5>
                                    <div class="text-muted small">
                                        <i class="fas fa-id-card me-1 text-secondary"></i> CI: {{ plan.ci }} {{ plan.lugar_expedicion }}
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-home me-1 text-secondary"></i> {{ plan.vivienda }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-briefcase me-1 text-secondary"></i> {{ plan.actividad }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom pb-2 pt-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-primary mb-0 text-uppercase">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Detalles del Plan
                            </h6>
                            <span v-if="plan.estado == 2 || plan.estado_plan == 1" class="badge bg-success text-uppercase px-3 py-1 shadow-sm rounded-pill">
                                <i class="fas fa-check-circle me-1"></i> Plan Aprobado
                            </span>
                            <span v-else-if="plan.estado == 0" class="badge bg-danger text-uppercase px-3 py-1 shadow-sm rounded-pill">
                                <i class="fas fa-times-circle me-1"></i> Anulado
                            </span>
                            <span v-else class="badge bg-warning text-dark text-uppercase px-3 py-1 shadow-sm rounded-pill">
                                <i class="fas fa-clock me-1"></i> Pendiente
                            </span>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3 small">
                                <div class="col-6">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                                        <span class="text-muted">Monto Total:</span>
                                        <span class="fw-bold text-primary fs-6">{{ plan.moneda }} {{ formatNumero(plan.total_pagar_plan) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                                        <span class="text-muted">Tasa Interés:</span>
                                        <span class="fw-bold text-dark">{{ plan.tasa }}% ({{ plan.tipo_tasa }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Garantía:</span>
                                        <span class="fw-bold text-dark text-end" style="font-size: 0.75rem;">{{ plan.tipo_garantia }}</span>
                                    </div>
                                </div>
                                <div class="col-6 border-start">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1 ps-2">
                                        <span class="text-muted">Plazo/Freq:</span>
                                        <span class="fw-bold text-dark">{{ plan.nro_cuotas }} {{ plan.lapso_capital }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1 ps-2">
                                        <span class="text-muted">Inicio:</span>
                                        <span class="fw-bold text-dark">{{ formatFecha(plan.fecha_inicio_plan) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between ps-2">
                                        <span class="text-muted">Finaliza:</span>
                                        <span class="fw-bold text-dark">{{ formatFecha(plan.fecha_fin_plan) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="text-dark fw-bold text-uppercase my-0">
                        <i class="fas fa-list-ol me-2 text-warning"></i> Cronograma de Cuotas
                    </h6>
                    <button @click="generarPdfCuotasPlanPago()" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm">
                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                    </button>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive" style="font-size:12px;">
                        <table class="table mb-0 table-striped table-hover align-middle table-listado-cuotas table-sm">
                            <thead class="text-dark table-warning text-center align-middle">
                                <tr>
                                    <th width="3%">#</th>
                                    <th width="7%">Fecha Venc.</th>
                                    <th width="9%">Capital</th>
                                    <th width="8%">Interés Fijo</th>
                                    <th width="8%">Saldo Cap.</th>
                                    <th width="9%">Total Bs</th>
                                    
                                    <th width="8%" class="text-primary border-start">Int. Devengado</th>
                                    <th width="8%" class="text-danger">Int. Moratorio</th>
                                    <th width="9%" class="border-start border-end">Int. Acumulado</th>
                                    
                                    <th width="14%" class="bg-success bg-opacity-10 text-success border-end">Total Pagado</th>
                                    
                                    <th width="9%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(cuota, index) in cuotas" :key="index" class="text-center align-middle">
                                    <td class="fw-bold text-muted">{{ cuota.numero }}</td>
                                    <td>{{ formatFecha(cuota.fecha) }}</td>
                                    
                                    <td class="fw-bold text-dark fs-6">
                                        <div>{{ formatNumero(cuota.capital) }}</div>
                                        <div v-if="parseFloat(cuota.capital_pagado_total) > 0" class="text-success lh-1 mt-1" style="font-size: 0.65rem;">
                                            <span class="fw-bold">{{ cuota.porcentaje_capital_pagado }}%</span><br>
                                            {{ formatNumero(cuota.capital_pagado_total) }} Bs
                                        </div>
                                    </td>
                                    <td>{{ formatNumero(cuota.interes) }}</td>
                                    <td class="text-muted">{{ formatNumero(cuota.saldo_capital) }}</td>
                                    <td class="fw-bold text-dark">{{ formatNumero(cuota.total) }}</td>
                                    
                                    <td class="text-primary border-start">
                                        {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_devengado_neto) }}<br>
                                        <span class="text-muted" style="font-size: 0.6rem;">({{ cuota.dias_transcurridos }} d)</span>
                                    </td>
                                    <td class="text-danger fw-bold">
                                        {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_moratorio_neto) }}<br>
                                        <span v-if="parseFloat(cuota.interes_moratorio_neto) > 0" class="text-muted" style="font-size: 0.6rem;">({{ cuota.dias_pasados }} d)</span>
                                    </td>
                                    <td class="fw-bold text-primary border-start border-end fs-6">
                                        {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_acumulado_neto) }}
                                    </td>
                                    
                                    <td class="bg-success bg-opacity-10 border-end">
                                        <div v-if="calcularTotalPagado(cuota) > 0">
                                            <span class="d-block fw-bold text-success fs-6">{{ formatNumero(calcularTotalPagado(cuota)) }} Bs</span>
                                            <span class="d-block text-muted lh-1 mb-1" style="font-size: 0.65rem;">
                                                Cap: {{ formatNumero(cuota.capital_pagado_total) }} | Int: {{ formatNumero(cuota.interes_pagado_total) }}
                                                <template v-if="parseFloat(cuota.mora_pagada) > 0">| Multa: {{ formatNumero(cuota.mora_pagada) }}</template>
                                            </span>
                                            <button @click="verDetallePagos(cuota)" class="btn btn-outline-success btn-sm py-0 px-2 shadow-sm" style="font-size: 0.65rem;" title="Ver detalle de pagos">
                                                <i class="fas fa-receipt"></i> Ver Historial
                                            </button>
                                        </div>
                                        <div v-else class="text-muted small fst-italic">Sin pagos</div>
                                    </td>
                                    
                                    <td>
                                        <div v-if="parseFloat(cuota.mora_fija_neta) > 0 && cuota.estado != 2" class="mb-1">
                                            <span class="badge bg-danger text-white rounded-pill shadow-sm" style="min-width: 90px; font-size: 0.65rem;">
                                                Multa {{ cuota.dias_pasados }} Días
                                            </span>
                                        </div>
                                        <span class="badge rounded-pill shadow-sm" :class="getEstadoCuota(cuota).clase" style="min-width: 90px; font-size: 0.65rem;">
                                            {{ getEstadoCuota(cuota).texto }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="!cuotas || cuotas.length === 0">
                                    <td colspan="11" class="text-center p-5 text-muted fst-italic bg-white">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                        Cargando o no hay cuotas registradas para este plan...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="modalHistorialPagos" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white py-3">
                        <h5 class="modal-title fw-bold text-uppercase">
                            <i class="fas fa-receipt me-2"></i> Historial de Pagos
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="cerrarModalPagos()"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        
                        <div v-if="cuota_seleccionada" class="alert border-success bg-white shadow-sm p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <div>
                                    <h5 class="fw-bold text-success text-uppercase mb-1">
                                        <i class="fas fa-calendar-check me-1"></i> Cuota Nro. {{ cuota_seleccionada.numero }}
                                    </h5>
                                    <span class="text-muted fw-semibold" style="font-size: 0.85rem;">
                                        Total Cuota: <strong class="text-dark">{{ formatNumero(cuota_seleccionada.total) }} Bs</strong>
                                    </span>
                                </div>
                                
                                <div class="text-end">
                                    <span class="badge bg-success px-3 py-2 fs-6 shadow-sm mb-1">
                                        {{ formatNumero(calcularTotalPagado(cuota_seleccionada)) }} Bs Pagados
                                    </span>
                                    <div class="text-success fw-bold mt-1" style="font-size: 0.8rem;">
                                        {{ calcularPorcentaje(cuota_seleccionada) }}% Pagado
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress shadow-sm" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar bg-success progress-bar-striped" 
                                     :class="{'progress-bar-animated': calcularPorcentaje(cuota_seleccionada) > 0 && calcularPorcentaje(cuota_seleccionada) < 100}"
                                     role="progressbar" 
                                     :style="{ width: calcularPorcentaje(cuota_seleccionada) + '%' }">
                                </div>
                            </div>
                        </div>

                        <div v-if="cargando_pagos" class="text-center py-5">
                            <div class="spinner-border text-success" role="status"></div>
                            <div class="mt-2 text-muted small fw-bold">Cargando historial...</div>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-bordered table-striped table-sm text-center align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-success text-uppercase">
                                    <tr>
                                        <th>Fecha del Pago</th>
                                        <th class="text-end">Abono Capital</th>
                                        <th class="text-end">Abono Interés</th>
                                        <th class="text-end text-danger">Abono Multa</th>
                                        <th class="text-end fw-bold">Total Recibo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(pago, i) in pagos_cuota" :key="i">
                                        <td>
                                            <span class="fw-bold text-dark">{{ formatFecha(pago.fecha_pago) }}</span><br>
                                            <small class="text-muted" style="font-size: 0.65rem;">Recibo #{{ pago.id }}</small>
                                        </td>
                                        <td class="text-end">{{ formatNumero(pago.pago_capital) }}</td>
                                        <td class="text-end">{{ formatNumero(pago.pago_interes) }}</td>
                                        <td class="text-end text-danger">{{ formatNumero(pago.pago_mora) }}</td>
                                        <td class="text-end fw-bold text-success bg-success bg-opacity-10">
                                            {{ formatNumero(parseFloat(pago.pago_capital) + parseFloat(pago.pago_interes) + parseFloat(pago.pago_mora)) }} Bs
                                        </td>
                                    </tr>
                                    <tr v-if="pagos_cuota.length === 0">
                                        <td colspan="5" class="text-muted fst-italic py-4">No se encontraron registros activos de pago para esta cuota.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top-0">
                        <button type="button" class="btn btn-secondary px-4 fw-bold" @click="cerrarModalPagos()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'DetallePlanPago',
    props: {
        plan: {
            type: Object,
            required: true
        },
        cuotas: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            cuota_seleccionada: null,
            pagos_cuota: [],
            cargando_pagos: false
        }
    },
    emits: ['cerrar', 'ver-ficha', 'ver-original'],
    methods: {
        formatFecha(fechaISO) {
            if (!fechaISO) return 'N/A';
            return moment(fechaISO).format('DD/MM/YYYY');
        },
        formatNumero(numero) {
            if (numero === undefined || numero === null || isNaN(numero)) return '0.00';
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(numero);
        },
        generarPdfCuotasPlanPago() {
            const url = '/lista_cuotas_planpago_pdf?id_planpago=' + this.plan.id_plan_pago;
            window.open(url, '_blank');
        },
        
        // --- NUEVOS MÉTODOS PARA EL HISTORIAL DE PAGOS ---
        calcularTotalPagado(cuota) {
            const cap = parseFloat(cuota.capital_pagado_total) || 0;
            const int = parseFloat(cuota.interes_pagado_total) || 0;
            const mora = parseFloat(cuota.mora_pagada) || 0;
            return cap + int + mora;
        },

        calcularPorcentaje(cuota) {
            if (!cuota) return '0.0';
            
            const totalPagado = this.calcularTotalPagado(cuota);
            const totalCuota = parseFloat(cuota.total) || 1; // || 1 Evita error de división por cero
            
            let porcentaje = (totalPagado / totalCuota) * 100;
            
            // Lo limitamos a 100 en el frontend visualmente en caso de que el cliente 
            // haya pagado un poco más por temas de multas de mora.
            if (porcentaje > 100) porcentaje = 100;
            
            return porcentaje.toFixed(1);
        },

        async verDetallePagos(cuota) {
            this.cuota_seleccionada = cuota;
            this.pagos_cuota = [];
            this.cargando_pagos = true;
            
            // Abrimos el modal
            const modalEl = document.getElementById('modalHistorialPagos');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            try {
                const response = await axios.get('/get_pagos_cuota', { params: { id_cuota: cuota.id } });
                this.pagos_cuota = response.data;
            } catch (error) {
                console.error("Error al obtener los pagos:", error);
                Swal.fire('Error', 'No se pudo cargar el historial de pagos.', 'error');
            } finally {
                this.cargando_pagos = false;
            }
        },
        cerrarModalPagos() {
            const modalEl = document.getElementById('modalHistorialPagos');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
            this.cuota_seleccionada = null;
            this.pagos_cuota = [];
        },

        getEstadoCuota(cuota) {
            if (cuota.estado == 2) {
                return { texto: 'Pagado', clase: 'bg-success text-white' };
            }
            if (cuota.estado == 3) {
                return { texto: 'Pago Parcial', clase: 'bg-warning text-dark border border-warning' };
            }
            if (cuota.estado == 0) {
                return { texto: 'Anulado', clase: 'bg-dark text-white' };
            }
            if (cuota.estado == 1) {
                if (cuota.dias_pasados > 0) {
                    return { texto: 'Con Multa', clase: 'bg-danger text-white' }; 
                } else {
                    return { texto: 'Pendiente', clase: 'bg-info text-white' }; 
                }
            }
            return { texto: 'Indefinido', clase: 'bg-light text-dark' };
        }
    }
}
</script>

<style scoped>
    .table-listado-cuotas th, .table-listado-cuotas td {
        font-size: 12px !important;
    }
    
    .hover-zoom {
        transition: transform 0.2s ease-in-out;
    }
    .hover-zoom:hover {
        transform: scale(1.05);
    }
</style>
