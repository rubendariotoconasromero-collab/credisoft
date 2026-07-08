<template>
    <main class="pagos-programados-report">
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">

                <!-- CARD PRINCIPAL -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-white text-uppercase mx-auto" style="font-size: 14px; letter-spacing: 0.5px;">
                            <i class="fas fa-calendar-check me-2"></i> Reporte de Pagos Programados
                        </h5>
                    </div>

                    <div class="card-body pt-2">

                        <!-- FILTROS -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-end">
                                    <!-- Fecha Inicio -->
                                    <div class="col-md-2">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Desde</label>
                                        <input v-model="filtros.fecha_inicio" type="date" class="form-control form-control-sm" @change="getPagosProgramados" />
                                    </div>
                                    <!-- Fecha Fin -->
                                    <div class="col-md-2">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Hasta</label>
                                        <input v-model="filtros.fecha_fin" type="date" class="form-control form-control-sm" @change="getPagosProgramados" />
                                    </div>
                                    <!-- Buscar Cliente -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Cliente</label>
                                        <input v-model="filtros.buscar_cliente" type="text" class="form-control form-control-sm" placeholder="Nombre o CI..." @input="debouncedGet" />
                                    </div>
                                    <!-- Asesor -->
                                    <div class="col-md-2">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Asesor</label>
                                        <select v-model="filtros.id_asesor" class="form-select form-select-sm" @change="getPagosProgramados">
                                            <option value="">Todos</option>
                                            <option v-for="a in asesores" :key="a.id" :value="a.id">{{ a.personal }}</option>
                                        </select>
                                    </div>
                                    <!-- Frecuencia -->
                                    <div class="col-md-1">
                                        <label class="form-label mb-0 text-muted fw-bold text-uppercase" style="font-size: 9px;">Frec.</label>
                                        <select v-model="filtros.lapso_capital" class="form-select form-select-sm" @change="getPagosProgramados">
                                            <option value="">Todas</option>
                                            <option value="Semanal">Semanal</option>
                                            <option value="Quincenal">Quincenal</option>
                                            <option value="Mensual">Mensual</option>
                                        </select>
                                    </div>
                                    <!-- Botones Filtrar/Limpiar -->
                                    <div class="col-md-2 d-flex gap-1">
                                        <button class="btn btn-primary btn-xs px-3 flex-grow-1" @click="getPagosProgramados" style="font-size: 11px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
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
                            <!-- Card 1: Cuotas Programadas -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-primary-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Cuotas Programadas</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ cuotas.length }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-calendar-alt fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 2: Capital Total -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-info-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Capital Total</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.capital) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-coins fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 3: Interés Total -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-warning-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Interés Total</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.interes) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-percent fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 4: Total a Cobrar -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-success-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Total a Cobrar</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.total) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENCABEZADO DE LA TABLA -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase animate-fade-in" style="font-size: 12px;">
                                <i class="fas fa-list me-1 text-primary"></i> Cuotas Encontradas ({{ cuotas.length }})
                            </h6>
                        </div>

                        <!-- TABLA -->
                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-sm align-middle table-compact">
                                <thead class="thead-pp text-uppercase fw-bold text-center">
                                    <tr>
                                        <th>Cód.</th>
                                        <th>Cliente</th>
                                        <th>Asesor</th>
                                        <th>Cuota</th>
                                        <th>Frecuencia</th>
                                        <th>Fecha Venc.</th>
                                        <th>Capital</th>
                                        <th>Interés</th>
                                        <th>Total</th>
                                        <th>Días</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in cuotas" :key="item.cuota_id" class="animate-fade-in">
                                        <td class="fw-bold text-primary text-center">#{{ item.credito_id }}</td>
                                        <td class="text-start">
                                            <div class="text-uppercase fw-bold text-dark" style="font-size: 10.5px;">{{ item.cliente_nombre }}</div>
                                            <div class="text-muted fw-normal" style="font-size: 9px; margin-top: 1px;">
                                                <i class="far fa-id-card text-secondary me-1"></i>C.I. {{ item.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="text-uppercase text-muted">{{ item.asesor_nombre }}</td>
                                        <td class="text-center">
                                            <span class="badge-pp badge-pp-primary">
                                                {{ item.nro_cuota }}/{{ item.nro_cuotas }}
                                            </span>
                                        </td>
                                        <td class="text-center text-muted" style="font-size: 10px;">{{ item.lapso_capital }}</td>
                                        <td class="text-center fw-bold">{{ formatDate(item.fecha_vencimiento) }}</td>
                                        <td class="fw-bold text-end" style="color:#1e3a8a;">{{ formatMoney(item.capital_pendiente, item.moneda) }}</td>
                                        <td class="fw-bold text-end" style="color:#d97706;">{{ formatMoney(item.interes_pendiente, item.moneda) }}</td>
                                        <td class="fw-bold text-end text-success">{{ formatMoney(item.monto_pendiente, item.moneda) }}</td>
                                        <td class="text-center">
                                            <span :class="badgeDias(item.dias_para_pago)">
                                                {{ labelDias(item.dias_para_pago) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span :class="item.cuota_estado === 3 ? 'badge-pp badge-pp-parcial' : 'badge-pp badge-pp-pendiente'">
                                                {{ item.cuota_estado === 3 ? 'Parcial' : 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="cuotas.length === 0">
                                        <td colspan="11" class="text-center text-muted py-5 bg-white rounded border">
                                            <i class="fas fa-calendar-times fa-3x mb-3 text-secondary animate-bounce"></i>
                                            <p class="mb-0 fw-bold font-size-13 text-muted">No se encontraron cuotas programadas para los filtros seleccionados.</p>
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
            cuotas: [],
            asesores: [],
            filtros: {
                fecha_inicio: moment().format('YYYY-MM-DD'),
                fecha_fin: moment().add(7, 'days').format('YYYY-MM-DD'),
                buscar_cliente: '',
                id_asesor: '',
                lapso_capital: ''
            },
            totales: {
                capital: 0,
                interes: 0,
                total: 0
            }
        };
    },
    methods: {
        async getPagosProgramados() {
            this.preloader = true;
            try {
                const response = await axios.get('/get_pagos_programados_rep', { params: this.filtros });
                this.cuotas = response.data;
                this.calcularTotales();
            } catch (error) {
                console.error('Error al obtener pagos programados:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un problema al obtener el reporte de pagos programados.' });
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
            let cap = 0, int = 0, tot = 0;
            this.cuotas.forEach(c => {
                cap += parseFloat(c.capital_pendiente || 0);
                int += parseFloat(c.interes_pendiente || 0);
                tot += parseFloat(c.monto_pendiente || 0);
            });
            this.totales = { capital: cap, interes: int, total: tot };
        },
        limpiarFiltros() {
            this.filtros = {
                fecha_inicio: moment().format('YYYY-MM-DD'),
                fecha_fin: moment().add(7, 'days').format('YYYY-MM-DD'),
                buscar_cliente: '',
                id_asesor: '',
                lapso_capital: ''
            };
            this.getPagosProgramados();
        },
        exportarPdf() {
            const params = new URLSearchParams(this.filtros).toString();
            window.open('/exportar_pagos_programados_pdf?' + params, '_blank');
        },
        exportarExcel() {
            const params = new URLSearchParams(this.filtros).toString();
            window.open('/exportar_pagos_programados_excel?' + params, '_blank');
        },
        badgeDias(dias) {
            const d = parseInt(dias);
            if (d < 0)   return 'badge-pp badge-pp-vencida';
            if (d === 0) return 'badge-pp badge-pp-hoy';
            if (d <= 3)  return 'badge-pp badge-pp-pronto';
            return 'badge-pp badge-pp-ok';
        },
        labelDias(dias) {
            const d = parseInt(dias);
            if (d < 0)  return Math.abs(d) + 'd vencida';
            if (d === 0) return 'HOY';
            return 'En ' + d + 'd';
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
        this.debouncedGet = debounce(this.getPagosProgramados, 400);
    },
    async mounted() {
        await Promise.all([this.getPagosProgramados(), this.getAsesores()]);
    }
};
</script>

<style scoped>
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

/* Thead con gradiente — aplica directo a th para evitar que Bootstrap lo sobreescriba */
.thead-pp th {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;
    color: #ffffff !important;
    border-color: #2d4fa0 !important;
}

/* Badges base — pill redondeado */
.badge-pp {
    display: inline-block;
    padding: 2px 9px;
    font-size: 9px !important;
    font-weight: 600;
    border-radius: 20px;
    min-width: 60px;
    text-align: center;
    white-space: nowrap;
}
.badge-pp-primary  { background-color: #1e3a8a; color: #fff; }
.badge-pp-vencida  { background-color: #dc2626; color: #fff; }
.badge-pp-hoy      { background-color: #d97706; color: #fff; }
.badge-pp-pronto   { background-color: #f59e0b; color: #fff; }
.badge-pp-ok       { background-color: #059669; color: #fff; }
.badge-pp-parcial  { background-color: #0284c7; color: #fff; }
.badge-pp-pendiente { background-color: #4b5563; color: #fff; }

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
.font-size-12 { font-size: 12px !important; }
.font-size-11 { font-size: 11px !important; }
.font-size-10 { font-size: 10px !important; }
.font-size-9  { font-size: 9px !important; }
.btn-xs {
    padding: 3px 8px !important;
    font-size: 10.5px !important;
    border-radius: 4px !important;
}

/* Gradientes */
.bg-primary-gradient {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;
}
.bg-info-gradient {
    background: linear-gradient(135deg, #155e75, #06b6d4) !important;
}
.bg-warning-gradient {
    background: linear-gradient(135deg, #d97706, #f59e0b) !important;
}
.bg-success-gradient {
    background: linear-gradient(135deg, #065f46, #10b981) !important;
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
    0%, 100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: none;
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
</style>
