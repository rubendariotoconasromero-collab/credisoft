<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-danger py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-white text-uppercase mx-auto" style="font-size:14px;">
                            <i class="fas fa-exclamation-triangle me-2"></i> Reporte de Créditos en Mora
                        </h5>
                    </div>

                    <div class="card-body pt-2">

                        <!-- FILTROS -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-end">

                                    <div class="col-md-1">
                                        <label class="form-label mb-0" style="font-size:10px;font-weight:600;">Cód. Crédito</label>
                                        <input v-model="filtros.id_credito" type="text" class="form-control form-control-sm" placeholder="Ej: 123" @input="getCreditosMora" />
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label mb-0" style="font-size:10px;font-weight:600;">Cliente (Nombre o CI)</label>
                                        <input v-model="filtros.buscar_cliente" type="text" class="form-control form-control-sm" placeholder="Nombre o CI..." @input="getCreditosMora" />
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-0" style="font-size:10px;font-weight:600;">Asesor</label>
                                        <select v-model="filtros.id_asesor" class="form-select form-select-sm" @change="getCreditosMora">
                                            <option value="">Todos los asesores</option>
                                            <option v-for="a in asesores" :key="a.id" :value="a.id">{{ a.personal }}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-0" style="font-size:10px;font-weight:600;">Días mora mínimos</label>
                                        <input v-model="filtros.dias_mora_min" type="number" min="0" class="form-control form-control-sm" placeholder="Ej: 30" @change="getCreditosMora" />
                                    </div>

                                    <div class="col-md-4 d-flex gap-1 align-items-end">
                                        <button class="btn btn-danger btn-xs px-2 flex-grow-1" @click="getCreditosMora" style="font-size:10.5px;height:31px;">
                                            <i class="fas fa-search me-1"></i> Filtrar
                                        </button>
                                        <button class="btn btn-outline-secondary btn-xs px-2 flex-grow-1" @click="limpiarFiltros" style="font-size:10.5px;height:31px;">
                                            <i class="fas fa-trash-alt me-1"></i> Limpiar
                                        </button>
                                        <button class="btn btn-outline-danger btn-xs px-2 flex-grow-1" @click="exportarPdf" :disabled="creditos.length === 0" style="font-size:10.5px;height:31px;">
                                            <i class="fas fa-file-pdf me-1"></i> PDF
                                        </button>
                                        <button class="btn btn-outline-success btn-xs px-2 flex-grow-1" @click="exportarExcel" :disabled="creditos.length === 0" style="font-size:10.5px;height:31px;">
                                            <i class="fas fa-file-excel me-1"></i> Excel
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RESUMEN TOTALES -->
                        <div v-if="creditos.length > 0" class="row g-2 mb-3">
                            <!-- Card 1: Créditos en mora -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-primary-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Créditos en mora</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ creditos.length }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-coins fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Total cuotas vencidas -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-warning-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Total cuotas vencidas</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ totalCuotasMora }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Capital pendiente -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-success-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Capital pendiente</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totalCapitalMora) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-percent fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Deuda total en mora -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-danger-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Deuda total en mora</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totalDeudaMora) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-exclamation-circle fa-lg animate-pulse"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENCABEZADO TABLA -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase" style="font-size:12px;">
                                <i class="fas fa-list me-1 text-danger"></i> Clientes con Cuotas Vencidas ({{ creditos.length }})
                            </h6>
                        </div>

                        <!-- TABLA PRINCIPAL -->
                        <div class="table-responsive" style="font-size:11px;">
                            <table class="table table-hover table-sm align-middle table-compact">
                                <thead class="table-danger text-white text-uppercase fw-bold text-center">
                                    <tr>
                                        <th style="width:30px;"></th>
                                        <th>Cód.</th>
                                        <th>Cliente</th>
                                        <th>C.I.</th>
                                        <th>Asesor</th>
                                        <th>Cuotas Mora</th>
                                        <th>Días Mora</th>
                                        <th>Saldo Capital</th>
                                        <th>Capital Pendiente</th>
                                        <th>Interés Pendiente</th>
                                        <th>Mora</th>
                                        <th>Total Deuda Mora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="c in creditos" :key="c.plan_pago_id">
                                        <!-- Fila principal del crédito -->
                                        <tr class="align-middle">
                                            <td class="text-center">
                                                <button class="btn btn-xs py-0 px-1 fw-bold"
                                                    :class="isExpanded(c.plan_pago_id) ? 'btn-secondary' : 'btn-outline-danger'"
                                                    @click="toggleExpand(c.plan_pago_id)"
                                                    style="font-size:9px;border-radius:3px;"
                                                    :title="isExpanded(c.plan_pago_id) ? 'Ocultar cuotas' : 'Ver cuotas'"
                                                >
                                                    <i class="fas" :class="isExpanded(c.plan_pago_id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                </button>
                                            </td>
                                            <td class="fw-bold text-danger text-center">#{{ c.credito_id }}</td>
                                            <td class="fw-bold text-uppercase">{{ c.cliente_nombre }}</td>
                                            <td class="text-center">{{ c.cliente_ci }}</td>
                                            <td class="text-uppercase" style="font-size:10px;">{{ c.asesor_nombre }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-danger rounded-pill">{{ c.cuotas_mora_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span :class="badgeDias(c.dias_mora_max)">{{ c.dias_mora_max }} días</span>
                                            </td>
                                            <td class="text-end fw-bold text-primary">{{ formatMoney(c.saldo_pendiente) }}</td>
                                            <td class="text-end">{{ formatMoney(c.total_capital_mora) }}</td>
                                            <td class="text-end text-muted">{{ formatMoney(c.total_interes_mora) }}</td>
                                            <td class="text-end text-danger">{{ formatMoney(c.total_multas_mora) }}</td>
                                            <td class="text-end fw-bold text-danger">{{ formatMoney(c.total_cuota_mora) }}</td>
                                        </tr>

                                        <!-- Filas de cuotas en mora (misma tabla → columnas alineadas con el crédito) -->
                                        <template v-if="isExpanded(c.plan_pago_id)">
                                            <tr v-for="cuota in c.cuotas_mora" :key="cuota.id" class="cuota-row">
                                                <td class="cuota-indent"></td>

                                                <!-- Identificación de la cuota (ocupa Cód · Cliente · CI · Asesor · Cuotas) -->
                                                <td colspan="5" class="text-start ps-4">
                                                    <span class="text-danger me-1 fw-bold">↳</span>
                                                    <span class="fw-bold">Cuota #{{ cuota.numero }}</span>
                                                    <span class="text-muted mx-1">·</span>
                                                    <span class="text-muted">vence {{ formatDate(cuota.fecha) }}</span>
                                                    <span class="badge ms-2" :class="getEstadoCuota(cuota).clase" style="font-size:8.5px;">
                                                        {{ getEstadoCuota(cuota).texto }}
                                                    </span>
                                                    <span class="text-muted ms-2" style="font-size:9px;">
                                                        ({{ cuota.dias_transcurridos }} días transc.)
                                                    </span>
                                                </td>

                                                <!-- Días Mora (alineado con "Días Mora" del crédito) -->
                                                <td class="text-center">
                                                    <span :class="badgeDias(cuota.dias_pasados)" style="font-size:9px;">
                                                        {{ cuota.dias_pasados }} días
                                                    </span>
                                                </td>

                                                <!-- Saldo Capital -->
                                                <td class="text-end">{{ formatMoney(cuota.saldo_capital) }}</td>

                                                <!-- Capital Pendiente (alineado con total del crédito) -->
                                                <td class="text-end">
                                                    <div>{{ formatMoney(cuota.capital_neto) }}</div>
                                                    <div v-if="parseFloat(cuota.capital_pagado_total) > 0" class="text-success lh-1 mt-1" style="font-size:8.5px;">
                                                        Pagado: {{ formatMoney(cuota.capital_pagado_total) }} ({{ cuota.porcentaje_capital_pagado }}%)
                                                    </div>
                                                </td>

                                                <!-- Interés Pendiente (acumulado) con desglose devengado/moratorio -->
                                                <td class="text-end">
                                                    <div class="text-muted">{{ formatMoney(cuota.interes_acumulado_neto) }}</div>
                                                    <div class="lh-1 mt-1" style="font-size:8.5px;">
                                                        <span class="text-primary">Dev: {{ formatMoney(cuota.interes_devengado_neto) }}</span>
                                                        <span class="text-danger ms-1">Mor: {{ formatMoney(cuota.interes_moratorio_neto) }}</span>
                                                    </div>
                                                    <div v-if="parseFloat(cuota.interes_pagado_total) > 0" class="text-success lh-1" style="font-size:8.5px;">
                                                        Pagado: {{ formatMoney(cuota.interes_pagado_total) }}
                                                    </div>
                                                </td>

                                                <!-- Mora (alineado con "Mora" del crédito) -->
                                                <td class="text-end text-danger">
                                                    <div>{{ formatMoney(cuota.mora_fija_neta) }}</div>
                                                    <div v-if="parseFloat(cuota.mora_pagada_total) > 0" class="text-success lh-1 mt-1" style="font-size:8.5px;">
                                                        Pagado: {{ formatMoney(cuota.mora_pagada_total) }}
                                                    </div>
                                                </td>

                                                <!-- Total a Pagar (alineado con "Total Deuda Mora" del crédito) -->
                                                <td class="text-end fw-bold text-danger">{{ formatMoney(cuota.total_a_pagar) }}</td>
                                            </tr>
                                        </template>
                                    </template>

                                    <!-- Fila vacía -->
                                    <tr v-if="creditos.length === 0 && !preloader">
                                        <td colspan="12" class="text-center text-muted py-5">
                                            <i class="fas fa-check-circle fa-3x mb-2 text-success d-block"></i>
                                            No se encontraron créditos en mora con los filtros seleccionados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2';
import debounce from 'lodash/debounce';

export default {
    data() {
        return {
            preloader: false,
            creditos: [],
            asesores: [],
            expandedRows: {},
            filtros: {
                id_credito:    '',
                buscar_cliente: '',
                id_asesor:     '',
                dias_mora_min: '',
            },
        };
    },
    computed: {
        totalCuotasMora() {
            return this.creditos.reduce((s, c) => s + (parseInt(c.cuotas_mora_count) || 0), 0);
        },
        totalCapitalMora() {
            return this.creditos.reduce((s, c) => s + (parseFloat(c.total_capital_mora) || 0), 0);
        },
        totalDeudaMora() {
            return this.creditos.reduce((s, c) => s + (parseFloat(c.total_cuota_mora) || 0), 0);
        },
    },
    methods: {
        getCreditosMora: debounce(async function () {
            this.preloader = true;
            try {
                const response = await axios.get('/get_creditos_mora_rep', { params: this.filtros });
                this.creditos = response.data;
            } catch (error) {
                console.error(error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte de mora.' });
            } finally {
                this.preloader = false;
            }
        }, 400),

        async getAsesores() {
            try {
                const { data } = await axios.get('/get_asesores');
                this.asesores = data;
            } catch (e) {
                console.error(e);
            }
        },

        limpiarFiltros() {
            this.filtros = { id_credito: '', buscar_cliente: '', id_asesor: '', dias_mora_min: '' };
            this.expandedRows = {};
            this.getCreditosMora();
        },

        exportarPdf() {
            const params = new URLSearchParams(this.filtros).toString();
            window.open('/exportar_creditos_mora_pdf?' + params, '_blank');
        },

        exportarExcel() {
            const params = new URLSearchParams(this.filtros).toString();
            window.open('/exportar_creditos_mora_excel?' + params, '_blank');
        },

        toggleExpand(planPagoId) {
            this.expandedRows = {
                ...this.expandedRows,
                [planPagoId]: !this.expandedRows[planPagoId],
            };
        },

        isExpanded(planPagoId) {
            return !!this.expandedRows[planPagoId];
        },

        badgeDias(dias) {
            if (dias >= 90)  return 'badge bg-danger';
            if (dias >= 30)  return 'badge bg-warning text-dark';
            return 'badge bg-secondary';
        },

        getEstadoCuota(cuota) {
            // Todas las cuotas de este reporte están vencidas (fecha < hoy)
            if (cuota.estado === 3) {
                return { texto: 'Pago Parcial', clase: 'bg-warning text-dark border border-warning' };
            }
            return { texto: 'Vencida', clase: 'bg-danger text-white' };
        },

        formatMoney(value) {
            if (value === null || value === undefined) return '-';
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' Bs.';
        },

        formatDate(date) {
            if (!date) return '-';
            return moment(date).format('DD/MM/YYYY');
        },
    },
    async mounted() {
        await Promise.all([this.getCreditosMora(), this.getAsesores()]);
    },
};
</script>

<style scoped>
.preloader {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.4); display: flex;
    justify-content: center; align-items: center; z-index: 99999;
}
.table-compact th, .table-compact td {
    padding: 3px 5px !important;
    vertical-align: middle !important;
    font-size: 10.5px !important;
}
.table-compact th { font-weight: 700 !important; font-size: 10px !important; }
.border-start-3 { border-left-width: 3px !important; }
.btn-xs { padding: 3px 8px !important; font-size: 10.5px !important; border-radius: 4px !important; }

/* Gradients para los totalizadores */
.bg-primary-gradient {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;
}
.bg-warning-gradient {
    background: linear-gradient(135deg, #d97706, #f59e0b) !important;
}
.bg-success-gradient {
    background: linear-gradient(135deg, #065f46, #10b981) !important;
}
.bg-danger-gradient {
    background: linear-gradient(135deg, #7f1d1d, #ef4444) !important;
}

.totalizer-card {
    border-radius: 6px;
    transition: transform 0.2s ease-in-out;
}
.totalizer-card:hover {
    transform: translateY(-2px);
}
.totalizer-label {
    font-size: 8.5px;
    font-weight: bold;
    letter-spacing: 0.5px;
    opacity: 0.85;
}
.totalizer-val {
    font-size: 14px !important;
    letter-spacing: 0.2px;
}
.bg-white-opacity-20 {
    background-color: rgba(255, 255, 255, 0.2);
}

/* Filas de detalle de cuotas en mora (dentro de la tabla principal) */
.cuota-row td {
    background-color: #fff7f7 !important;
    border-bottom: 1px solid #fde2e2 !important;
    font-size: 10px !important;
    vertical-align: middle !important;
}
/* Barra lateral que agrupa visualmente las cuotas bajo su crédito */
.cuota-row .cuota-indent {
    border-left: 3px solid #dc3545 !important;
}
.cuota-row .badge {
    min-width: auto !important;
}
</style>
