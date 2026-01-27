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

        <div class="card-body">
            <div class="row mb-3">
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

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <div class="p-3 rounded bg-white border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Cliente:</span>
                            <span class="text-uppercase">{{ cliente.nombre }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">CI:</span>
                            <span>{{ cliente.ci }} {{ cliente.lugar_expedicion }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Garantía:</span>
                            <span>{{ solicitud.tipo_garantia }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded bg-white border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Plazo:</span>
                            <span>{{ solicitud.nro_cuotas }} ({{ solicitud.lapso_capital }})</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Monto:</span>
                            <span>{{ solicitud.importe_solicitud }} {{ solicitud.moneda }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Forma de Pago:</span>
                            <span>{{ solicitud.lapso_capital }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded bg-white border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Fecha Desembolso:</span>
                            <span>{{ formatearFecha(solicitud.fecha_desembolso) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Fecha Inicio Cuota:</span>
                            <span>{{ formatearFecha(solicitud.fecha_primera_cuota) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered table-sm table-striped">
                    <thead class="table-success">
                        <tr>
                            <th class="text-uppercase fw-bold text-center">Nro</th>
                            <th class="text-uppercase fw-bold text-center">Fecha</th>
                            <th class="text-center text-uppercase fw-bold">Capital</th>
                            <th class="text-center text-uppercase fw-bold">Interés</th>
                            <th class="text-center text-uppercase fw-bold">Saldo Capital</th>
                            <th class="text-center text-uppercase fw-bold">Total Bs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in cuotas" :key="item.nro">
                            <td class="text-center">{{ item.nro }}</td>
                            <td class="text-center">{{ formatearFecha(item.fecha) }}</td>
                            <td class="text-center">{{ item.capital }}</td>
                            <td class="text-center">{{ item.interes }}</td>
                            <td class="text-center">{{ item.saldo_capital }}</td>
                            <td class="text-center fw-bold">{{ item.total_cuota }}</td>
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

        <div class="card-footer text-center">
            <button type="button" class="btn btn-secondary" @click="$emit('cerrar')">
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
        }
    }
};
</script>