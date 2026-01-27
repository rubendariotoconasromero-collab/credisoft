<template>
    <div class="modal fade" id="modalDetalleOriginal" tabindex="-1" aria-labelledby="modalDetalleOriginalLabel" aria-hidden="true" v-if="datos">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold text-uppercase text-dark" id="modalDetalleOriginalLabel">
                        <i class="fas fa-history me-2"></i> Expediente del Crédito Original #{{ datos.solicitud.id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body bg-light">
                    <div class="card mb-3 shadow-sm border-start border-4 border-info">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img :src="datos.solicitud.cliente.imagen 
                                                    ? '/img/cliente/' + datos.solicitud.cliente.imagen 
                                                    : '/img/empresa/user_img2_old.png'" 
                                            class="rounded-circle border shadow-sm" 
                                            style="width: 60px; height: 60px; object-fit: cover;"
                                            alt="Cliente">
                                </div>
                                <div class="col-md-5">
                                    <h5 class="fw-bold text-dark mb-0 text-uppercase">{{ datos.solicitud.cliente.nombre }}</h5>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-id-card me-1"></i> {{ datos.solicitud.cliente.ci }} {{ datos.solicitud.cliente.lugar_expedicion }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-map-marker-alt me-1"></i> {{ datos.solicitud.cliente.vivienda }}
                                    </div>
                                </div>
                                <div class="col-md-5 border-start">
                                    <div class="small fw-bold text-secondary text-uppercase">Actividad Económica</div>
                                    <div class="small text-dark mb-1">{{ datos.solicitud.cliente.actividad }}</div>
                                    <div class="small text-muted">
                                        Ingreso Mensual: <span class="fw-bold text-info">{{ datos.solicitud.moneda }} {{ formatNumero(datos.solicitud.cliente.ingreso_mensual) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-lg-6">
                            <div class="card h-100 border-secondary shadow-sm">
                                <div class="card-header bg-warning text-dark fw-bold small py-1">
                                    <i class="fas fa-file-contract me-1"></i> Condiciones del Crédito
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <span class="text-muted d-block">Monto Otorgado:</span>
                                            <span class="fw-bold fs-6 text-dark">{{ datos.solicitud.moneda }} {{ formatNumero(datos.solicitud.importe_solicitud) }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Tasa de Interés:</span>
                                            <span class="fw-bold text-dark">{{ datos.solicitud.tasa }}% ({{ datos.solicitud.tipo_tasa }})</span>
                                        </div>
                                        <div class="col-12"><hr class="my-2"></div>
                                        
                                        <div class="col-6">
                                            <span class="text-muted d-block">Plazo / Frecuencia:</span>
                                            <span class="fw-bold text-dark">{{ datos.solicitud.nro_cuotas }} Cuotas ({{ datos.solicitud.lapso_capital }})</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Destino:</span>
                                            <span class="text-dark">{{ datos.solicitud.destino_prestamo }}</span>
                                        </div>
                                        <div class="col-12"><hr class="my-2"></div>

                                        <div class="col-6">
                                            <span class="text-muted d-block">Fecha Desembolso:</span>
                                            <span class="text-dark">{{ formatFecha(datos.solicitud.fecha_desembolso) }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Tipo Desembolso:</span>
                                            <span class="badge bg-light text-dark border">{{ datos.solicitud.tipo_desembolso }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card h-100 border-warning shadow-sm">
                                <div class="card-header bg-warning text-dark fw-bold small py-1">
                                    <i class="fas fa-shield-alt me-1"></i> Respaldos y Garantías
                                </div>
                                <div class="card-body p-0">
                                    
                                    <div class="p-3 border-bottom">
                                        <h6 class="small fw-bold text-uppercase text-secondary mb-2">
                                            Tipo: <span class="text-dark">{{ datos.solicitud.tipo_garantia }}</span>
                                        </h6>
                                        <div v-if="datos.solicitud.garantias && datos.solicitud.garantias.length > 0">
                                            <ul class="list-group list-group-flush small">
                                                <li v-for="g in datos.solicitud.garantias" :key="g.id" class="list-group-item px-0 py-1 border-0">
                                                    <i class="fas fa-check-circle text-success me-2"></i> {{ g.descripcion }}
                                                </li>
                                            </ul>
                                        </div>
                                        <div v-else class="text-muted small fst-italic">No se registraron descripciones de garantías específicas.</div>
                                    </div>

                                    <div class="p-3 bg-light">
                                        <h6 class="small fw-bold text-uppercase text-secondary mb-2">Codeudores / Garantes</h6>
                                        <div v-if="datos.solicitud.codeudores && datos.solicitud.codeudores.length > 0">
                                            <div v-for="cod in datos.solicitud.codeudores" :key="cod.id" class="d-flex align-items-center mb-2 p-2 bg-white rounded border shadow-sm">
                                                <div class="bg-secondary rounded-circle p-1 me-2 text-white" style="width: 30px; height: 30px; text-align: center;">
                                                    <i class="fas fa-user fa-xs"></i>
                                                </div>
                                                <div class="small flex-grow-1">
                                                    <div class="fw-bold text-dark">{{ cod.nombre }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">CI: {{ cod.ci }}</div>
                                                </div>
                                                <span class="badge bg-info text-white" style="font-size: 0.65rem;">{{ cod.tipo || 'Garante' }}</span>
                                            </div>
                                        </div>
                                        <div v-else class="text-muted small fst-italic">
                                            <i class="fas fa-info-circle me-1"></i> Sin codeudores asignados.
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div> <div class="card border-dark shadow-sm">
                        <div class="card-header bg-dark text-white fw-bold small py-1 d-flex justify-content-between">
                            <span><i class="fas fa-list-ol me-2"></i> Historial de Pagos / Plan de Pagos</span>
                            <span class="badge bg-white text-dark">{{ datos.lista_cuotas.length }} Cuotas</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0 small">
                                    <thead class="table-warning text-white">
                                        <tr>
                                            <th class="text-center py-2">#</th>
                                            <th class="text-center py-2">Fecha Venc.</th>
                                            <th class="text-end py-2">Capital</th>
                                            <th class="text-end py-2">Interés</th>
                                            <th class="text-end py-2">Saldo Cap.</th>
                                            <th class="text-end py-2 fw-bold">Total Cuota</th>
                                            <th class="text-center py-2">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="cuota in datos.lista_cuotas" :key="cuota.id">
                                            <td class="text-center fw-bold">{{ cuota.numero }}</td>
                                            <td class="text-center">{{ formatFecha(cuota.fecha) }}</td>
                                            <td class="text-end">{{ formatNumero(cuota.capital) }}</td>
                                            <td class="text-end">{{ formatNumero(cuota.interes) }}</td>
                                            <td class="text-end text-muted">{{ formatNumero(cuota.saldo_capital) }}</td>
                                            <td class="text-end fw-bold text-dark">{{ formatNumero(cuota.total) }}</td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill" :class="getEstadoCuota(cuota).clase" style="min-width: 80px;">
                                                    {{ getEstadoCuota(cuota).texto }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="!datos.lista_cuotas.length">
                                            <td colspan="7" class="text-center p-3 text-muted">
                                                No se encontraron cuotas generadas para este plan.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar Expediente
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    name: 'ModalHistorialOriginal',
    props: {
        datos: {
            type: Object,
            default: null
        }
    },
    methods: {
        formatFecha(fechaISO) {
            if (!fechaISO) return 'N/A';
            return moment(fechaISO).format('DD/MM/YYYY');
        },
        formatNumero(numero) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(numero);
        },
        getEstadoCuota(cuota) {
            if (cuota.estado === 2) {
                return { texto: 'Pagado', clase: 'bg-success' };
            }
            if (cuota.estado === 0) {
                return { texto: 'Anulado', clase: 'bg-dark' };
            }
            if (cuota.estado === 1) {
                if (cuota.dias_pasados > 0) {
                    return { texto: `Vencida (${cuota.dias_pasados} días)`, clase: 'bg-danger' };
                } else {
                    return { texto: 'Pendiente', clase: 'bg-warning text-dark' };
                }
            }
            return { texto: 'Indefinido', clase: 'bg-light text-dark' };
        }
    }
}
</script>