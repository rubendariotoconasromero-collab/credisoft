<template>
    <main class="credit-mora-management">
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <!-- VISTA PRINCIPAL: LISTADO DE CRÉDITOS EN MORA -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-danger bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-white text-uppercase mx-auto" style="font-size: 14px; letter-spacing: 0.5px;">
                            <i class="fas fa-exclamation-triangle me-2 animate-pulse"></i> Créditos en Mora y Gestión de Cobros
                        </h5>
                    </div>

                    <div class="card-body pt-2">
                        
                        <!-- FILTROS COMPACTOS EN UNA SOLA FILA -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-center">
                                    <!-- Filtro Código -->
                                    <div class="col-md-2">
                                        <input v-model="filtros.id_credito" type="text" class="form-control form-control-sm" placeholder="Cód. Crédito..." @input="getCreditosMora" />
                                    </div>
                                    
                                    <!-- Filtro Cliente -->
                                    <div class="col-md-6">
                                        <input v-model="filtros.buscar_cliente" type="text" class="form-control form-control-sm" placeholder="Nombre o CI de Cliente..." @input="getCreditosMora" />
                                    </div>

                                    <!-- Botones de Acción -->
                                    <div class="col-md-4 d-flex gap-1">
                                        <button class="btn btn-danger btn-xs px-3 flex-grow-1" @click="getCreditosMora" style="font-size: 11px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-search"></i> <span>Filtrar</span>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-xs px-3 flex-grow-1" @click="limpiarFiltros" style="font-size: 11px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-trash-alt"></i> <span>Limpiar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN DE TOTALIZADORES (CARDS GLASSMORPHISM) -->
                        <div class="row g-2 mb-3">
                            <!-- Card 1: Capital en Mora -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-primary-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Capital Mora</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.capital) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-coins fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Interés en Mora -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-warning-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Interés Mora</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.interes) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-percent fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Total Amortizaciones en Mora -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-success-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Cuotas Acumuladas</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.cuotas) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Multas Acumuladas -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm totalizer-card bg-danger-gradient text-white">
                                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="totalizer-label d-block text-uppercase">Multas Acumuladas</span>
                                            <h6 class="totalizer-val my-1 fw-bold">{{ formatMoney(totales.multas) }}</h6>
                                        </div>
                                        <div class="totalizer-icon bg-white-opacity-20 rounded-circle p-2">
                                            <i class="fas fa-exclamation-circle fa-lg animate-pulse"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA PRINCIPAL DE CRÉDITOS EN MORA -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase animate-fade-in" style="font-size: 12px;">
                                <i class="fas fa-list me-1 text-danger"></i> Clientes con Retrasos Encontrados ({{ creditos.length }})
                            </h6>
                        </div>

                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm align-middle table-compact">
                                <thead class="table-danger text-white text-uppercase fw-bold text-center">
                                    <tr>
                                        <th>Cód.</th>
                                        <th>Cliente</th>
                                        <th>Teléfonos</th>
                                        <th>Asesor</th>
                                        <th>Monto</th>
                                        <th>Plazo</th>
                                        <th>Cuotas</th>
                                        <th>Días</th>
                                        <th>Cap. Mora</th>
                                        <th>Int. Mora</th>
                                        <th>Cuota Mora</th>
                                        <th>Multas</th>
                                        <th>Acc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in creditos" :key="item.credito_id" class="animate-fade-in">
                                        <td class="fw-bold text-danger text-center">#{{ item.credito_id }}</td>
                                        <td class="text-start">
                                            <div class="text-uppercase fw-bold text-dark" style="font-size: 10.5px;">{{ item.cliente_nombre }}</div>
                                            <div class="text-muted fw-normal" style="font-size: 9px; margin-top: 1px;">
                                                <i class="far fa-id-card text-secondary me-1"></i>C.I. {{ item.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="text-start">
                                            <div v-if="item.telefonos_list && item.telefonos_list.length">
                                                <div v-for="(tel, idx) in item.telefonos_list" :key="idx" class="d-flex align-items-center mb-1 text-dark" style="font-size: 9.5px; line-height: 1.1;">
                                                    <i class="fas fa-phone-alt text-danger me-1" style="font-size: 8px;"></i>
                                                    <span class="fw-semibold">{{ tel }}</span>
                                                </div>
                                            </div>
                                            <span v-else class="text-muted italic" style="font-size: 9px;">Sin teléfonos</span>
                                        </td>
                                        <td class="text-uppercase text-muted">{{ item.asesor_nombre }}</td>
                                        <td class="fw-bold text-end text-success">{{ formatMoney(item.importe_solicitud, item.moneda) }}</td>
                                        <td class="text-center">{{ item.nro_cuotas }} cuot. ({{ item.lapso_capital }})</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger text-white text-uppercase font-size-10 px-2 rounded" style="width: 80px; display: inline-block;">
                                                {{ item.cuotas_mora_count }} {{ item.cuotas_mora_count == 1 ? 'Cuota' : 'Cuotas' }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold text-danger font-size-11">
                                            <i class="far fa-clock me-1 text-danger"></i> {{ item.dias_mora_max }} d.
                                        </td>
                                        <td class="fw-bold text-end text-primary">{{ formatMoney(item.total_capital_mora, item.moneda) }}</td>
                                        <td class="fw-bold text-end text-warning">{{ formatMoney(item.total_interes_mora, item.moneda) }}</td>
                                        <td class="fw-bold text-end text-dark">{{ formatMoney(item.total_cuota_mora, item.moneda) }}</td>
                                        <td class="fw-bold text-end text-danger">{{ formatMoney(item.total_multas_mora, item.moneda) }}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a style="cursor: pointer" class="text-danger" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v fa-lg"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="background-color: #1e293b;">
                                                    <li @click="verDetalleMora(item)">
                                                        <a class="dropdown-item text-white" href="#"><i class="fas fa-eye me-2 text-warning"></i> Ver Cuotas en Mora</a>
                                                    </li>
                                                    <li @click="generarPdfExtracto(item.plan_pago_id)">
                                                        <a class="dropdown-item text-white" href="#"><i class="fas fa-file-pdf me-2 text-danger"></i> Exportar Extracto</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="creditos.length === 0">
                                        <td colspan="13" class="text-center text-muted py-5 bg-white rounded border">
                                            <i class="fas fa-smile-beam fa-3x mb-3 text-success animate-bounce"></i>
                                            <p class="mb-0 fw-bold font-size-13 text-success">¡Enhorabuena! No hay clientes con cuotas en mora.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- MODAL DETALLADO DE CUOTAS EN MORA (GLASSMORPHISM BACKDROP) -->
                <div v-if="mostrarModal" class="custom-modal-backdrop" @click.self="mostrarModal = false">
                    <div class="custom-modal-content card shadow-lg border-0 animate-scale-in" style="max-width: 650px; width: 95%;">
                        <div class="card-header bg-danger bg-gradient py-2 d-flex justify-content-between align-items-center text-white">
                            <h6 class="my-0 fw-bold text-uppercase" style="font-size: 12px;">
                                <i class="fas fa-receipt me-2"></i> Cuotas y Acumulados en Mora — Crédito #{{ creditoSeleccionado.credito_id }}
                            </h6>
                            <button type="button" class="btn-close btn-close-white" @click="mostrarModal = false" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-3 font-size-11">
                            <!-- Datos del Cliente Resumen -->
                            <div class="border border-danger-subtle rounded p-2 mb-3 bg-light bg-gradient">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">Cliente</span>
                                        <span class="fw-bold text-dark text-uppercase font-size-12">{{ creditoSeleccionado.cliente_nombre }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">C.I.</span>
                                        <span class="fw-bold text-dark">{{ creditoSeleccionado.cliente_ci }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">Máximo Atraso</span>
                                        <span class="fw-bold text-danger font-size-12">{{ creditoSeleccionado.dias_mora_max }} días</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabla de Detalles de Cuotas Vencidas -->
                            <h6 class="fw-bold text-dark mb-2 text-uppercase" style="font-size: 10px;">Desglose de Amortizaciones Vencidas</h6>
                            <div class="table-responsive" style="max-height: 220px;">
                                <table class="table table-bordered table-sm table-compact align-middle mb-0">
                                    <thead class="table-dark text-white text-uppercase font-size-9 text-center">
                                        <tr>
                                            <th>Nro</th>
                                            <th>Vencimiento</th>
                                            <th>Días Retraso</th>
                                            <th>Capital</th>
                                            <th>Interés</th>
                                            <th>Multa Estimada</th>
                                            <th>Total Cuota</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="cuota in creditoSeleccionado.cuotas_mora" :key="cuota.id" class="text-center">
                                            <td class="fw-bold">{{ cuota.numero }}</td>
                                            <td>{{ formatDate(cuota.fecha) }}</td>
                                            <td class="fw-bold text-danger">{{ cuota.dias_pasados }} días</td>
                                            <td class="text-end text-muted">
                                                <div>{{ formatMoney(cuota.capital_neto) }}</div>
                                                <div v-if="parseFloat(cuota.capital_pagado) > 0" class="text-success" style="font-size: 8px; font-weight: bold;">
                                                    Pagado: {{ formatMoney(cuota.capital_pagado) }}
                                                </div>
                                            </td>
                                            <td class="text-end text-muted">
                                                <div>{{ formatMoney(cuota.interes_acumulado_neto) }}</div>
                                                <div v-if="parseFloat(cuota.interes_pagado) > 0" class="text-success" style="font-size: 8px; font-weight: bold;">
                                                    Pagado: {{ formatMoney(cuota.interes_pagado) }}
                                                </div>
                                            </td>
                                            <td class="text-end text-danger fw-bold">{{ formatMoney(cuota.mora_fija_neta) }}</td>
                                            <td class="text-end text-primary fw-bold">
                                                <div>{{ formatMoney(cuota.total_a_pagar) }}</div>
                                                <div v-if="parseFloat(cuota.capital_pagado) > 0 || parseFloat(cuota.interes_pagado) > 0" class="text-muted" style="font-size: 8px; font-weight: normal;">
                                                    Org: {{ formatMoney(cuota.total) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span :class="cuota.estado === 3 ? 'badge bg-info text-white rounded font-size-9 px-1' : 'badge bg-warning text-dark rounded font-size-9 px-1'">
                                                    {{ cuota.estado === 3 ? 'P. Parcial' : 'Pendiente' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Resumen del Modal -->
                            <div class="border border-danger rounded p-2 mt-3 bg-danger-light-opacity">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-dark fw-bold">Capital Total en Mora:</span>
                                    <span class="fw-bold text-dark">{{ formatMoney(creditoSeleccionado.total_capital_mora) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-dark">Interés Total en Mora:</span>
                                    <span class="fw-bold text-dark">{{ formatMoney(creditoSeleccionado.total_interes_mora) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-danger fw-bold">Fines/Multas Acumuladas:</span>
                                    <span class="fw-bold text-danger">{{ formatMoney(creditoSeleccionado.total_multas_mora) }}</span>
                                </div>
                                <hr class="my-1 border-top border-danger opacity-20">
                                <div class="d-flex justify-content-between">
                                    <span class="text-danger fw-bold font-size-12">Total Acumulado Reclamado (Bs.):</span>
                                    <span class="fw-bold text-danger font-size-12">
                                        {{ formatMoney(parseFloat(creditoSeleccionado.total_cuota_mora) + parseFloat(creditoSeleccionado.total_multas_mora)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end p-2 bg-light">
                            <button class="btn btn-secondary btn-xs px-3 py-1" @click="mostrarModal = false" style="font-size: 11px;">
                                <i class="fas fa-times-circle me-1"></i> Cerrar
                            </button>
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

export default {
    data() {
        return {
            preloader: false,
            creditos: [],
            filtros: {
                id_credito: '',
                buscar_cliente: ''
            },
            totales: {
                capital: 0,
                interes: 0,
                cuotas: 0,
                multas: 0
            },
            creditoSeleccionado: {},
            mostrarModal: false
        };
    },
    methods: {
        async getCreditosMora() {
            this.preloader = true;
            try {
                const response = await axios.get('/get_creditos_mora_rep', { params: this.filtros });
                this.creditos = response.data;
                this.calcularTotales();
            } catch (error) {
                console.error("Error al obtener créditos en mora:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un problema al obtener el listado de créditos en mora.'
                });
            } finally {
                this.preloader = false;
            }
        },
        calcularTotales() {
            let cap = 0;
            let int = 0;
            let cuot = 0;
            let mult = 0;

            this.creditos.forEach(c => {
                cap += parseFloat(c.total_capital_mora || 0);
                int += parseFloat(c.total_interes_mora || 0);
                cuot += parseFloat(c.total_cuota_mora || 0);
                mult += parseFloat(c.total_multas_mora || 0);
            });

            this.totales = {
                capital: cap,
                interes: int,
                cuotas: cuot,
                multas: mult
            };
        },
        limpiarFiltros() {
            this.filtros = {
                id_credito: '',
                buscar_cliente: ''
            };
            this.getCreditosMora();
        },
        verDetalleMora(item) {
            this.creditoSeleccionado = item;
            this.mostrarModal = true;
        },
        generarPdfExtracto(id_plan_pago) {
            const url = `/rep_extracto_credito?id_plan_pago=${id_plan_pago}`;
            window.open(url, '_blank');
        },
        formatMoney(value, currency = 'Bs.') {
            if (value === null || value === undefined) return '-';
            const formatted = new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
            return `${formatted} ${currency}`;
        },
        formatDate(date) {
            if (!date) return '-';
            return moment(date).format('DD/MM/YYYY');
        }
    },
    async mounted() {
        await this.getCreditosMora();
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
.btn-group .dropdown-menu {
    border: none;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    border-radius: 6px;
    padding: 6px 0;
}
.btn-group .dropdown-item {
    font-size: 11px;
    padding: 6px 12px;
    transition: all 0.2s ease;
}
.btn-group .dropdown-item:hover {
    background-color: rgba(239, 68, 68, 0.15) !important;
    color: #ef4444 !important;
}

/* Clases específicas para diseño extra compacto */
.table-compact th, .table-compact td {
    padding: 3px 5px !important;
    vertical-align: middle !important;
    font-size: 10.5px !important;
}
.table-compact th {
    font-weight: 700 !important;
    font-size: 10px !important;
}
.font-size-12 { font-size: 12px !important; }
.font-size-11 { font-size: 11px !important; }
.font-size-10 { font-size: 10px !important; }
.font-size-9 { font-size: 9px !important; }
.btn-xs {
    padding: 3px 8px !important;
    font-size: 10.5px !important;
    border-radius: 4px !important;
}

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

/* Estilos para el Modal (Glassmorphism Backdrop) */
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.55);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100000;
    backdrop-filter: blur(3px);
}
.custom-modal-content {
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3) !important;
}
.bg-danger-light-opacity {
    background-color: rgba(239, 68, 68, 0.05);
}

.animate-scale-in {
    animation: scaleIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes scaleIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

.animate-bounce {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8,0,1,1);
    }
    50% {
        transform: none;
        animation-timing-function: cubic-bezier(0,0,0.2,1);
    }
}
</style>
