<template>
    <div class="card shadow-sm">
        <div class="card-header bg-warning py-1 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 text-center">
                <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                    Gestión de Aprobación de Crédito
                </h5>
            </div>
            <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
        </div>

        <div class="card-body py-2">
            <div class="row mb-2">
                <div class="col-12 text-center">
                    <button v-if="solicitud.estado === 1" 
                            @click="$emit('aprobar')" 
                            class="btn btn-info btn-sm me-2" 
                            :disabled="procesando"
                            data-bs-toggle="tooltip" title="Aprobar solicitud">
                        <i v-if="!procesando" class="fas fa-thumbs-up me-1"></i>
                        <i v-else class="fas fa-spinner fa-spin me-1"></i>
                        {{ procesando ? 'Aprobando...' : 'Aprobar' }}
                    </button>

                    <button v-if="solicitud.estado === 2" class="btn btn-sm me-2 text-white"
                        style="background-color:#009c26; border-color: #009c26;" disabled>
                        <i class="fas fa-thumbs-up me-1"></i> Aprobada
                    </button>

                    <button @click="$emit('generar-pdf')" class="btn btn-warning text-dark btn-sm me-2"
                        data-bs-toggle="tooltip" title="Generar PDF">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                </div>
            </div>

            <div class="row g-2 mt-1">
                <div class="col-md-4">
                    <div class="info-card-compact h-100">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Cliente:</span>
                            <span class="text-uppercase text-truncate ms-2 fw-semibold text-dark" style="max-width: 70%;" :title="cliente.nombre">
                                {{ cliente.nombre }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">CI:</span>
                            <span class="fw-semibold text-dark">{{ cliente.ci }} {{ cliente.lugar_expedicion }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Garantía:</span>
                            <span class="fw-semibold text-dark">{{ solicitud.tipo_garantia }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card-compact h-100">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Plazo:</span>
                            <span class="fw-semibold text-dark">{{ solicitud.nro_cuotas }} ({{ solicitud.lapso_capital }})</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Monto:</span>
                            <span class="fw-bold text-primary">{{ formatMoneda(solicitud.importe_solicitud) }} {{ solicitud.moneda }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Forma de Pago:</span>
                            <span class="fw-semibold text-dark">{{ solicitud.lapso_capital }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card-compact h-100">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Fecha Desembolso:</span>
                            <span class="fw-semibold text-dark">{{ formatearFecha(solicitud.fecha_desembolso) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Fecha 1ra Cuota:</span>
                            <span class="fw-semibold text-dark">{{ formatearFecha(solicitud.fecha_primera_cuota) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-scrollable mt-3">
                <table class="table table-bordered table-striped table-hover table-compact-custom align-middle">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-center">Nro</th>
                            <th class="text-uppercase text-center">Fecha</th>
                            <th class="text-uppercase text-end">Capital</th>
                            <th class="text-uppercase text-end">Interés</th>
                            <th class="text-uppercase text-end">Saldo Capital</th>
                            <th class="text-uppercase text-end">Total Cuota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in cuotas" :key="item.nro">
                            <td class="text-center fw-bold text-muted">{{ item.nro }}</td>
                            <td class="text-center">{{ formatearFecha(item.fecha) }}</td>
                            <td class="text-end font-monospace">{{ formatMoneda(item.capital) }}</td>
                            <td class="text-end font-monospace text-danger">{{ formatMoneda(item.interes) }}</td>
                            <td class="text-end font-monospace text-muted">{{ formatMoneda(item.saldo_capital) }}</td>
                            <td class="text-end font-monospace fw-bold text-success">{{ formatMoneda(item.total_cuota) }}</td>
                        </tr>
                        <tr v-if="cuotas.length === 0">
                            <td colspan="6" class="text-center text-muted py-3">
                                No se ha generado el plan de pagos.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-center py-2">
            <button type="button" class="btn btn-secondary btn-sm" @click="$emit('cerrar')">
                <i class="fas fa-times me-1"></i> Cerrar
            </button>
        </div>
    </div>
</template>

<script>
import moment from "moment";

export default {
    props: {
        solicitud: { type: Object, required: true },
        cliente: { type: Object, required: true }, // Objeto 'cliente_simulacion' del padre
        cuotas: { type: Array, default: () => [] }, // Array 'lista_cuotas'
        procesando: { type: Boolean, default: false } // 'aprobando_solicitud'
    },
    methods: {
        formatearFecha(fecha) {
            return fecha ? moment(fecha).format("DD/MM/YYYY") : "-";
        },
        formatMoneda(val) {
            if (val === undefined || val === null) return "-";
            const num = parseFloat(val);
            if (isNaN(num)) return val;
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    }
};
</script>

<style scoped>
.table-scrollable {
    max-height: 700px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.03);
}

/* Scrollbar styling for a cleaner look */
.table-scrollable::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.table-scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.table-scrollable::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}
.table-scrollable::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}

.table-compact-custom {
    font-size: 0.85rem;
    margin-bottom: 0;
}

.table-compact-custom th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: #198754 !important; /* Solid premium success green */
    color: white !important;
    border-bottom: 2px solid #157347 !important;
    padding: 6px 10px;
    font-size: 0.78rem;
    letter-spacing: 0.5px;
    font-weight: 700;
}

.table-compact-custom td {
    padding: 4px 10px !important;
}

.font-monospace {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.82rem;
}

.info-card-compact {
    padding: 8px 12px !important;
    background-color: #f8f9fa;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    font-size: 0.82rem;
}

.info-card-compact .d-flex {
    margin-bottom: 3px !important;
}

.info-card-compact .d-flex:last-child {
    margin-bottom: 0 !important;
}
</style>