<template>
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 text-center">
                <h5 class="header-title my-0 fw-bold text-uppercase">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Detalle del Plan de Pago
                </h5>
            </div>
            <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-white"></button>
        </div>

        <div class="card-body p-4 bg-light">
            <div class="card mb-3 border-start border-4 border-primary shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img @click="$emit('ver-ficha', plan.id_cliente)" 
                                 :src="plan.imagen_cliente ? '/img/cliente/' + plan.imagen_cliente : '/img/empresa/user_img2_old.png'" 
                                 class="rounded-circle border shadow-sm" 
                                 style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;" 
                                 alt="Cliente">
                        </div>
                        <div class="col">
                            <h5 class="fw-bold text-dark mb-0 text-uppercase">{{ plan.cliente }}</h5>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-id-card me-1"></i> {{ plan.ci }} {{ plan.lugar_expedicion }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-home me-1"></i> {{ plan.vivienda }}
                            </div>
                            <div class="small text-secondary">
                                <i class="fas fa-briefcase me-1"></i> {{ plan.actividad }}
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <span v-if="plan.estado == 2" class="badge rounded bg-success fs-6 px-3">APROBADO</span>
                            <span v-else-if="plan.estado == 1" class="badge rounded bg-warning text-dark fs-6 px-3">PENDIENTE</span>
                            <span v-else-if="plan.estado == 0" class="badge rounded bg-danger fs-6 px-3">ANULADO</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-white fw-bold text-uppercase small border-bottom">
                    Datos Generales del Crédito
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-md-4">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Monto Total:</span>
                                    <span class="fw-bold text-primary">{{ plan.moneda }} {{ formatNumero(plan.total_pagar_plan) }}</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Tasa Interés:</span>
                                    <span class="fw-bold">{{ plan.tasa }}% ({{ plan.tipo_tasa }})</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Garantía:</span>
                                    <span class="fw-bold text-end" style="max-width: 150px;">{{ plan.tipo_garantia }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4 border-start">
                            <ul class="list-unstyled mb-0 px-2">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Plazo:</span>
                                    <span class="fw-bold">{{ plan.nro_cuotas }} Cuotas</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Frecuencia:</span>
                                    <span class="fw-bold">{{ plan.lapso_capital }}</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Tipo Solicitud:</span>
                                    <span class="badge rounded bg-info text-white">{{ plan.tipo_solicitud }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4 border-start">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Fecha Inicio:</span>
                                    <span>{{ formatFecha(plan.fecha_inicio_plan) }}</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Fecha Fin:</span>
                                    <span>{{ formatFecha(plan.fecha_fin_plan) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="plan.id_solicitud_origen" class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm py-2 px-3 mb-3">
                <div class="small">
                    <i class="fas fa-history fa-lg me-2"></i>
                    <strong>Antecedente:</strong> Este crédito proviene de una reprogramación.
                </div>
                <button @click="$emit('ver-original', plan.id_solicitud_origen)" 
                        class="btn btn-warning btn-sm fw-bold text-dark border border-dark">
                    <i class="fas fa-search me-1"></i> Ver Crédito Original
                </button>
            </div>

            <div class="card border-secondary shadow-sm">
                <div class="card-header bg-warning bg-opacity-75 text-white py-1 d-flex justify-content-between align-items-center">
                    <h5 class="text-dark fw-bold text-uppercase">Listado de Cuotas</h5>
                    <button @click="generarPdfCuotasPlanPago()" class="btn btn-light btn-sm fw-bold" style="font-size: 0.7rem;">
                        <i class="fas fa-file-pdf text-danger me-1"></i> PDF
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm mb-0 small">
                            <thead class="table-warning text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha Venc.</th>
                                    <th class="text-end">Capital</th>
                                    <th class="text-end">Interés</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(cuota, index) in cuotas" :key="index">
                                    <td class="text-center fw-bold">{{ cuota.numero }}</td>
                                    <td class="text-center">{{ formatFecha(cuota.fecha) }}</td>
                                    <td class="text-end">{{ formatNumero(cuota.capital) }}</td>
                                    <td class="text-end">{{ formatNumero(cuota.interes) }}</td>
                                    <td class="text-end fw-bold">{{ formatNumero(cuota.total) }}</td>
                                    <td class="text-end text-muted">{{ formatNumero(cuota.saldo_capital) }}</td>
                                    <td class="text-center">
                                        <div v-if="esPrimerRegistroConMora(index, cuota) && cuota.dias_pasados > 0">
                                            <span class="badge rounded bg-danger mb-1">MORA ({{ cuota.dias_pasados }}d)</span>
                                        </div>
                                        <span class="badge rounded-pill" :class="getEstadoCuota(cuota).clase" style="min-width: 80px;">
                                            {{ getEstadoCuota(cuota).texto }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!cuotas || cuotas.length === 0">
                                    <td colspan="7" class="text-center p-4 text-muted fst-italic">
                                        No hay cuotas registradas para este plan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';

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
    // Definimos los eventos que este componente envía al padre
    emits: ['cerrar', 'ver-ficha', 'ver-original'],
    methods: {
        formatFecha(fechaISO) {
            if (!fechaISO) return 'N/A';
            return moment(fechaISO).format('DD/MM/YYYY');
        },
        formatNumero(numero) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(numero);
        },
        generarPdfCuotasPlanPago() {
            // Usamos la prop 'plan'
            const url = '/lista_cuotas_planpago_pdf?id_planpago=' + this.plan.id_plan_pago;
            window.open(url, '_blank');
        },
        getEstadoCuota(cuota) {
            if (cuota.estado === 2) return { texto: 'Pagado', clase: 'bg-success' };
            if (cuota.estado === 0) return { texto: 'Anulado', clase: 'bg-dark' };
            if (cuota.estado === 1) {
                return cuota.dias_pasados > 0 
                    ? { texto: `Vencida (${cuota.dias_pasados}d)`, clase: 'bg-danger' }
                    : { texto: 'Pendiente', clase: 'bg-warning text-dark' };
            }
            return { texto: 'Indefinido', clase: 'bg-light text-dark' };
        },
        esPrimerRegistroConMora(index, cuotaActual) {
            // Lógica para mostrar la etiqueta MORA solo en la primera cuota vencida
            const primerIndiceConMora = this.cuotas.findIndex(c => c.dias_pasados > 0);
            return index === primerIndiceConMora;
        }
    }
}
</script>