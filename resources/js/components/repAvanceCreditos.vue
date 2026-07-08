<template>
    <main class="avance-creditos-report">
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">

                <!-- CARD PRINCIPAL -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-white text-uppercase mx-auto" style="font-size: 14px; letter-spacing: 0.5px;">
                            <i class="fas fa-chart-line me-2"></i> Avance de Pago de Créditos
                        </h5>
                    </div>

                    <div class="card-body pt-2">

                        <!-- FILTROS -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-end">
                                    <!-- % Desde -->
                                    <div class="col-md-1">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">% Desde</label>
                                        <input v-model.number="filtros.pct_inicio" type="number" min="0" max="100" class="form-control form-control-sm" placeholder="0" @change="getAvanceCreditos" />
                                    </div>
                                    <!-- % Hasta -->
                                    <div class="col-md-1">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">% Hasta</label>
                                        <input v-model.number="filtros.pct_fin" type="number" min="0" max="100" class="form-control form-control-sm" placeholder="100" @change="getAvanceCreditos" />
                                    </div>
                                    <!-- Buscar Cliente -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Cliente</label>
                                        <input v-model="filtros.buscar_cliente" type="text" class="form-control form-control-sm" placeholder="Nombre o CI..." @input="debouncedGet" />
                                    </div>
                                    <!-- Asesor -->
                                    <div class="col-md-2">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Asesor</label>
                                        <select v-model="filtros.id_asesor" class="form-select form-select-sm" @change="getAvanceCreditos">
                                            <option value="">Todos</option>
                                            <option v-for="a in asesores" :key="a.id" :value="a.id">{{ a.personal }}</option>
                                        </select>
                                    </div>
                                    <!-- Frecuencia -->
                                    <div class="col-md-1">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Frec.</label>
                                        <select v-model="filtros.lapso_capital" class="form-select form-select-sm" @change="getAvanceCreditos">
                                            <option value="">Todas</option>
                                            <option value="Semanal">Semanal</option>
                                            <option value="Quincenal">Quincenal</option>
                                            <option value="Mensual">Mensual</option>
                                        </select>
                                    </div>
                                    <!-- Estado -->
                                    <div class="col-md-1">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Estado</label>
                                        <select v-model="filtros.estado_plan" class="form-select form-select-sm" @change="getAvanceCreditos">
                                            <option value="todos">Todos</option>
                                            <option value="1">Vigente</option>
                                            <option value="2">Terminado</option>
                                        </select>
                                    </div>
                                    <!-- Botones Filtrar/Limpiar -->
                                    <div class="col-md-3 d-flex gap-1">
                                        <button class="btn btn-success btn-xs px-3 flex-grow-1" @click="getAvanceCreditos" style="font-size: 11px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-search"></i> <span>Filtrar</span>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-xs px-3 flex-grow-1" @click="limpiarFiltros" style="font-size: 11px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-trash-alt"></i> <span>Limpiar</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Exportaciones -->
                                <div class="row g-2 mt-1">
                                    <div class="col-12 d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-danger btn-xs px-3" @click="exportarPdf" style="font-size: 11px;">
                                            <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                                        </button>
                                        <button class="btn btn-outline-success btn-xs px-3" @click="exportarExcel" style="font-size: 11px;">
                                            <i class="fas fa-file-excel me-1"></i> Exportar Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TARJETAS RESUMEN -->
                        <div class="row g-2 mb-3">
                            <!-- Card 1: Total Créditos -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-success-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Créditos</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ registros.length }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-credit-card fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 2: Monto Total -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-primary-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Monto Total</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.monto_total) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-coins fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 3: Capital Pagado -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-info-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Capital Pagado</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.capital_pagado) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 4: Promedio Avance -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-warning-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Promedio Avance</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ totales.promedio_pct }}%</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-percentage fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENCABEZADO TABLA -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase animate-fade-in" style="font-size: 12px;">
                                <i class="fas fa-list me-1 text-success"></i> Créditos Encontrados ({{ registros.length }})
                            </h6>
                        </div>

                        <!-- TABLA -->
                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-sm align-middle table-compact">
                                <thead class="thead-avance text-uppercase fw-bold text-center">
                                    <tr>
                                        <th>Cód.</th>
                                        <th>Cliente</th>
                                        <th>Asesor</th>
                                        <th>Frecuencia</th>
                                        <th>Total Crédito</th>
                                        <th>Capital Pagado</th>
                                        <th>Cuotas Pagadas</th>
                                        <th style="min-width:130px;">Avance</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in registros" :key="item.plan_pago_id" class="animate-fade-in">
                                        <td class="fw-bold text-success text-center">#{{ item.credito_id }}</td>
                                        <td class="text-start">
                                            <div class="text-uppercase fw-bold text-dark" style="font-size: 10.5px;">{{ item.cliente_nombre }}</div>
                                            <div class="text-muted fw-normal" style="font-size: 9px; margin-top: 1px;">
                                                <i class="far fa-id-card text-secondary me-1"></i>C.I. {{ item.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="text-uppercase text-muted">{{ item.asesor_nombre }}</td>
                                        <td class="text-center text-muted" style="font-size: 10px;">{{ item.lapso_capital }}</td>
                                        <td class="fw-bold text-end" style="color:#065f46;">{{ formatMoney(item.total_pagar, item.moneda) }}</td>
                                        <td class="fw-bold text-end" style="color:#0284c7;">{{ formatMoney(item.capital_pagado, item.moneda) }}</td>
                                        <td class="text-center">
                                            <span class="badge-avance badge-avance-cuotas">
                                                {{ item.cuotas_pagadas }}/{{ item.nro_cuotas }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 20px; background-color: #e5e7eb;">
                                                    <div
                                                        class="progress-bar"
                                                        role="progressbar"
                                                        :style="{width: Math.min(item.porcentaje_pagado, 100) + '%', borderRadius: '20px'}"
                                                        :class="progressClass(item.porcentaje_pagado)"
                                                    ></div>
                                                </div>
                                                <span class="fw-bold" :class="progressTextClass(item.porcentaje_pagado)" style="font-size: 10px; min-width: 38px; text-align: right;">
                                                    {{ item.porcentaje_pagado }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span :class="item.estado_plan === 2 ? 'badge-avance badge-avance-terminado' : 'badge-avance badge-avance-vigente'">
                                                {{ item.estado_plan === 2 ? 'Terminado' : 'Vigente' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="registros.length === 0">
                                        <td colspan="9" class="text-center text-muted py-5 bg-white rounded border">
                                            <i class="fas fa-search fa-3x mb-3 text-secondary animate-bounce"></i>
                                            <p class="mb-0 fw-bold font-size-13 text-muted">No se encontraron créditos con los filtros seleccionados.</p>
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
            registros: [],
            asesores: [],
            filtros: {
                pct_inicio: 0,
                pct_fin: 100,
                buscar_cliente: '',
                id_asesor: '',
                lapso_capital: '',
                estado_plan: 'todos'
            },
            totales: {
                monto_total: 0,
                capital_pagado: 0,
                promedio_pct: 0
            }
        };
    },
    methods: {
        async getAvanceCreditos() {
            this.preloader = true;
            try {
                const response = await axios.get('/get_avance_creditos_rep', { params: this.filtros });
                this.registros = response.data;
                this.calcularTotales();
            } catch (error) {
                console.error('Error al obtener avance de créditos:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un problema al obtener el reporte.' });
            } finally {
                this.preloader = false;
            }
        },
        async getAsesores() {
            try {
                const response = await axios.get('/get_asesores');
                this.asesores = response.data;
            } catch (error) {
                console.error('Error al obtener asesores:', error);
            }
        },
        calcularTotales() {
            let monto = 0, cap = 0, pctSum = 0;
            this.registros.forEach(r => {
                monto  += parseFloat(r.total_pagar || 0);
                cap    += parseFloat(r.capital_pagado || 0);
                pctSum += parseFloat(r.porcentaje_pagado || 0);
            });
            this.totales = {
                monto_total:   monto,
                capital_pagado: cap,
                promedio_pct:  this.registros.length > 0
                    ? (pctSum / this.registros.length).toFixed(1)
                    : 0
            };
        },
        limpiarFiltros() {
            this.filtros = {
                pct_inicio: 0,
                pct_fin: 100,
                buscar_cliente: '',
                id_asesor: '',
                lapso_capital: '',
                estado_plan: 'todos'
            };
            this.getAvanceCreditos();
        },
        exportarPdf() {
            const params = new URLSearchParams(this.filtros).toString();
            window.open('/exportar_avance_creditos_pdf?' + params, '_blank');
        },
        exportarExcel() {
            const params = new URLSearchParams(this.filtros).toString();
            window.open('/exportar_avance_creditos_excel?' + params, '_blank');
        },
        progressClass(pct) {
            const p = parseFloat(pct);
            if (p >= 75) return 'bg-success';
            if (p >= 50) return 'bg-info';
            if (p >= 25) return 'bg-warning';
            return 'bg-danger';
        },
        progressTextClass(pct) {
            const p = parseFloat(pct);
            if (p >= 75) return 'text-success';
            if (p >= 50) return 'text-info';
            if (p >= 25) return 'text-warning';
            return 'text-danger';
        },
        formatMoney(value, currency = 'Bs.') {
            if (value === null || value === undefined) return '-';
            const formatted = new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            return `${formatted} ${currency}`;
        },
        formatDate(date) {
            if (!date) return '-';
            return moment(date).format('DD/MM/YYYY');
        }
    },
    created() {
        this.debouncedGet = debounce(this.getAvanceCreditos, 400);
    },
    async mounted() {
        await Promise.all([this.getAvanceCreditos(), this.getAsesores()]);
    }
};
</script>

<style scoped>
.preloader {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

/* Thead con gradiente verde — directo en th */
.thead-avance th {
    background: linear-gradient(135deg, #065f46, #10b981) !important;
    color: #ffffff !important;
    border-color: #047857 !important;
}

/* Badges base */
.badge-avance {
    display: inline-block;
    padding: 2px 9px;
    font-size: 9px !important;
    font-weight: 600;
    border-radius: 20px;
    min-width: 60px;
    text-align: center;
    white-space: nowrap;
}
.badge-avance-cuotas   { background-color: #1e3a8a; color: #fff; }
.badge-avance-vigente  { background-color: #059669; color: #fff; }
.badge-avance-terminado { background-color: #6b7280; color: #fff; }

.table-compact th, .table-compact td {
    padding: 3px 5px !important;
    vertical-align: middle !important;
    font-size: 10.5px !important;
}
.table-compact th {
    font-weight: 700 !important;
    font-size: 10px !important;
}
.font-size-13 { font-size: 13px !important; }
.btn-xs {
    padding: 3px 8px !important;
    font-size: 10.5px !important;
    border-radius: 4px !important;
}

/* Gradientes */
.bg-success-gradient {
    background: linear-gradient(135deg, #065f46, #10b981) !important;
}
.bg-primary-gradient {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;
}
.bg-info-gradient {
    background: linear-gradient(135deg, #155e75, #06b6d4) !important;
}
.bg-warning-gradient {
    background: linear-gradient(135deg, #d97706, #f59e0b) !important;
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

.animate-fade-in {
    animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
    0%   { opacity: 0; }
    100% { opacity: 1; }
}

.animate-bounce {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
    50%       { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
</style>
