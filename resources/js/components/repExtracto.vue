<template>
    <main class="credit-extract-management">
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <!-- VISTA 0: LISTADO DE CRÉDITOS Y FILTROS -->
                <div v-if="vista === 0" class="card shadow-sm border-0">
                    <div class="card-header bg-warning bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-dark text-uppercase mx-auto" style="font-size: 14px;">
                             Extracto de Crédito de Clientes
                        </h5>
                    </div>

                    <div class="card-body pt-2">
                        <!-- SECCIÓN DE FILTROS COMPACTA -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-center">
                                    <!-- Filtro Código de Crédito -->
                                    <div class="col-md-1">
                                        <input v-model="filtros.id_credito" type="text" class="form-control form-control-sm" placeholder="Código" @input="getCreditos" />
                                    </div>
                                    
                                    <!-- Filtro por Cliente (Nombre/CI) -->
                                    <div class="col-md-3">
                                        <input v-model="filtros.buscar_cliente" type="text" class="form-control form-control-sm" placeholder="Nombre o CI de Cliente..." @input="getCreditos" />
                                    </div>

                                    <!-- Filtro por Estado -->
                                    <div class="col-md-2">
                                        <select v-model="filtros.estado_plan" class="form-select form-select-sm" @change="getCreditos">
                                            <option value="todos">Todos los Estados</option>
                                            <option value="vigente">Vigentes (Activos)</option>
                                            <option value="terminado">Terminados (Cancelados)</option>
                                        </select>
                                    </div>

                                    <!-- Filtro Rango Fechas -->
                                    <div class="col-md-2">
                                        <input v-model="filtros.fecha_inicio" type="date" class="form-control form-control-sm" placeholder="Desde" @change="getCreditos" />
                                    </div>
                                    <div class="col-md-2">
                                        <input v-model="filtros.fecha_fin" type="date" class="form-control form-control-sm" placeholder="Hasta" @change="getCreditos" />
                                    </div>

                                    <!-- Botones en la misma fila -->
                                    <div class="col-md-2 d-flex gap-1">
                                        <button class="btn btn-success btn-xs px-2 flex-grow-1" @click="getCreditos" style="font-size: 10.5px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-search"></i> <span>Filtrar</span>
                                        </button>
                                        <button class="btn btn-outline-danger btn-xs px-2 flex-grow-1" @click="limpiarFiltros" style="font-size: 10.5px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-trash-alt"></i> <span>Limpiar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA DE LISTADO DE CRÉDITOS -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase" style="font-size: 12px;">
                                <i class="fas fa-list me-1"></i> Créditos Encontrados ({{ creditos.length }})
                            </h6>
                        </div>

                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm align-middle table-compact">
                                <thead class="table-success text-white text-uppercase fw-bold text-center">
                                    <tr>
                                        <th>Cód. Crédito</th>
                                        <th>Cliente</th>
                                        <th>C.I.</th>
                                        <th>Importe Préstamo</th>
                                        <th>Total a Pagar</th>
                                        <th>Tasa</th>
                                        <th>Cuotas / Frecuencia</th>
                                        <th>Rango Fechas</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in creditos" :key="item.id">
                                        <td class="fw-bold text-primary text-center">#{{ item.id }}</td>
                                        <td class="text-uppercase fw-bold">{{ item.cliente_nombre }}</td>
                                        <td class="fw-bold text-center">{{ item.cliente_ci }}</td>
                                        <td class="fw-bold text-success text-end">{{ formatMoney(item.importe_solicitud, item.moneda) }}</td>
                                        <td class="fw-bold text-danger text-end">{{ formatMoney(item.total_pagar, item.moneda) }}</td>
                                        <td class="text-center">{{ item.tasa }}%</td>
                                        <td class="text-center">{{ item.nro_cuotas }} cuotas ({{ item.lapso_capital }})</td>
                                        <td>
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <span class="badge bg-light text-dark border border-success-subtle rounded px-2 py-1" style="width: 105px; display: inline-block; font-weight: 500; font-size: 9.5px;">
                                                    <i class="far fa-calendar-alt text-success me-1"></i> {{ formatDate(item.fecha_inicio) }}
                                                </span>
                                                <span class="badge bg-light text-dark border border-danger-subtle rounded px-2 py-1" style="width: 105px; display: inline-block; font-weight: 500; font-size: 9.5px;">
                                                    <i class="far fa-calendar-check text-danger me-1"></i> {{ formatDate(item.fecha_fin) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span :class="item.estado === 1 ? 'badge bg-success text-uppercase font-size-10 px-2 rounded' : 'badge bg-secondary text-uppercase font-size-10 px-2 rounded'" style="width: 110px; display: inline-block; text-align: center;">
                                                {{ item.estado === 1 ? 'Vigente' : 'Terminado' }}
                                            </span>
                                        </td>
                                        <td class="text-center position-relative">
                                            <div class="btn-group">
                                                <a style="cursor: pointer" class="text-success" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="background-color: #2a3b50;">
                                                    <li @click="verDetalle(item.id)">
                                                        <a class="dropdown-item text-white" href="#"><i class="fas fa-eye me-2 text-info"></i> Ver Detalle</a>
                                                    </li>
                                                    <li @click="generarPdfCuotasPlanPago(item.id)">
                                                        <a class="dropdown-item text-white" href="#"><i class="fas fa-file-pdf me-2 text-danger"></i> Exportar PDF</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="creditos.length === 0">
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">No se encontraron créditos vigentes o terminados con los filtros seleccionados.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- VISTA 1: DETALLE DE CRÉDITO Y CUOTAS COMBINADAS (COMPACTO Y DESPLEGABLE) -->
                <div v-if="vista === 1" class="card shadow-sm border-0">
                    <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 text-center">
                            <h5 class="header-title my-0 fw-bold text-dark text-uppercase" style="font-size: 14px;">
                                {{ tituloFormulario }}
                            </h5>
                        </div>
                        <a @click="cerrarFormulario()" type="button" class="btn-close btn-close-white" style="cursor: pointer;"></a>
                    </div>

                    <div class="card-body pt-2">
                        <!-- RESUMEN HORIZONTAL COMPACTO: CLIENTE Y CRÉDITO -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-12">
                                <div class="card border border-primary border-start-4 mb-0 bg-light bg-gradient shadow-none">
                                    <div class="card-body p-2">
                                        <div class="row align-items-center">
                                            <!-- Sección Cliente -->
                                            <div class="col-md-5 border-end border-secondary-subtle">
                                                <h6 class="fw-bold text-success text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.05em;">
                                                    <i class="fas fa-user-tie me-1"></i> Información del Cliente
                                                </h6>
                                                <div class="d-flex flex-wrap gap-x-3 gap-y-1 font-size-11" style="row-gap: 4px; column-gap: 16px;">
                                                    <span>Cliente: <strong class="text-uppercase text-dark">{{ detalle.credito.cliente_nombre }}</strong></span>
                                                    <span>C.I.: <strong class="text-dark">{{ detalle.credito.cliente_ci }} {{ detalle.credito.cliente_expedicion }}</strong></span>
                                                    <span>Actividad: <strong class="text-secondary text-uppercase">{{ detalle.credito.cliente_actividad || 'N/A' }}</strong></span>
                                                </div>
                                            </div>
                                            <!-- Sección Crédito -->
                                            <div class="col-md-7 ps-md-3">
                                                <h6 class="fw-bold text-primary text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.05em;">
                                                    <i class="fas fa-file-contract me-1"></i> Datos de Crédito
                                                </h6>
                                                <div class="d-flex flex-wrap gap-x-3 gap-y-1 font-size-11 align-items-center" style="row-gap: 4px; column-gap: 18px;">
                                                    <span>Monto: <strong class="text-primary">{{ formatMoney(detalle.credito.importe_solicitud, detalle.credito.moneda) }}</strong></span>
                                                    <span>Tasa: <strong class="text-dark">{{ detalle.credito.tasa }}%</strong></span>
                                                    <span>Plazo: <strong class="text-dark">{{ detalle.credito.nro_cuotas }} cuotas ({{ detalle.credito.lapso_capital }})</strong></span>
                                                    <span>Total Plan: <strong class="text-danger">{{ formatMoney(detalle.credito.total_pagar, detalle.credito.moneda) }}</strong></span>
                                                    <span>Asesor: <strong class="text-uppercase text-dark">{{ detalle.credito.asesor_nombre }}</strong></span>
                                                    <span>Estado: <span :class="detalle.credito.estado === 1 ? 'badge bg-success font-size-9 px-2 rounded' : 'badge bg-secondary font-size-9 px-2 rounded'">{{ detalle.credito.estado === 1 ? 'Vigente' : 'Terminado' }}</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA DE CUOTAS Y PAGOS COMBINADOS COMPACTA (UNA SOLA FILA DE ENCABEZADO) -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase" style="font-size: 12px;">
                                <i class="fas fa-money-bill-wave me-1"></i> Cronograma de Amortizaciones y Pagos Realizados
                            </h6>
                            <button class="btn btn-danger btn-xs px-2 py-1" @click="generarPdfCuotasPlanPago(detalle.credito.id)" style="font-size: 10.5px; display: flex; align-items: center; gap: 4px; border-radius: 4px;">
                                <i class="fas fa-file-pdf"></i> <span>Exportar PDF</span>
                            </button>
                        </div>

                        <div class="table-responsive" style="font-size: 10.5px;">
                            <table class="table table-bordered table-sm align-middle table-compact">
                                <thead class="table-dark text-white text-uppercase fw-bold text-center">
                                    <tr class="align-middle">
                                        <th style="width: 45px;">Nro</th>
                                        <th style="width: 90px;">Vencimiento</th>
                                        <th style="width: 85px;">Capital</th>
                                        <th style="width: 85px;">Interés</th>
                                        <th style="width: 95px;">Total Cuota</th>
                                        <th style="width: 95px;">Estado</th>
                                        <th class="table-info text-dark" style="width: 90px;">Fecha Pago</th>
                                        <th class="table-info text-dark" style="width: 95px;">Recibido</th>
                                        <th class="table-info text-dark" style="width: 90px;">Mora Cobrada</th>
                                        <th class="table-info text-dark" style="width: 70px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="cuota in detalle.cuotas" :key="cuota.id">
                                        <!-- CASO A: LA CUOTA TIENE PAGOS -->
                                        <template v-if="cuota.pagos.length > 0">
                                            <!-- Fila Principal (Resumen de la cuota y sumatoria de pagos) -->
                                            <tr class="text-center align-middle">
                                                <!-- Columnas de Cuota (siempre visibles) -->
                                                <td :rowspan="isCuotaExpanded(cuota.id) ? cuota.pagos.length + 1 : 1" class="fw-bold text-dark">{{ cuota.numero }}</td>
                                                <td :rowspan="isCuotaExpanded(cuota.id) ? cuota.pagos.length + 1 : 1">{{ formatDate(cuota.fecha) }}</td>
                                                <td :rowspan="isCuotaExpanded(cuota.id) ? cuota.pagos.length + 1 : 1" class="text-end fw-bold text-secondary">{{ formatMoney(cuota.capital) }}</td>
                                                <td :rowspan="isCuotaExpanded(cuota.id) ? cuota.pagos.length + 1 : 1" class="text-end text-muted">{{ formatMoney(cuota.interes) }}</td>
                                                <td :rowspan="isCuotaExpanded(cuota.id) ? cuota.pagos.length + 1 : 1" class="text-end fw-bold text-primary">{{ formatMoney(cuota.total) }}</td>
                                                <td :rowspan="isCuotaExpanded(cuota.id) ? cuota.pagos.length + 1 : 1">
                                                    <span :class="getBadgeCuotaClass(cuota.estado)" style="width: 110px; display: inline-block; text-align: center;">
                                                        {{ getBadgeCuotaText(cuota.estado) }}
                                                    </span>
                                                </td>
                                                
                                                <!-- Columnas de Resumen de Pagos en la Fila Principal -->
                                                <td class="table-info text-muted font-size-10 fw-bold">-</td>
                                                <td class="table-info fw-bold text-success text-end">{{ formatMoney(getSumMontoPago(cuota.pagos)) }}</td>
                                                <td class="table-info fw-bold text-danger text-end">{{ formatMoney(getSumPagoMora(cuota.pagos)) }}</td>
                                                <td class="table-info text-center">
                                                    <button class="btn btn-xs py-0 px-2 fw-bold" :class="isCuotaExpanded(cuota.id) ? 'btn-secondary' : 'btn-info'" @click="toggleCuotaPagos(cuota.id)" style="font-size: 10px; border-radius: 4px;">
                                                        <i class="fas me-1" :class="isCuotaExpanded(cuota.id) ? 'fa-compress-alt' : 'fa-expand-alt'"></i>
                                                        {{ isCuotaExpanded(cuota.id) ? 'Ocultar' : 'Pagos' }}
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Filas Secundarias Desplegadas (para cada pago individual) -->
                                            <tr v-if="isCuotaExpanded(cuota.id)" v-for="pago in cuota.pagos" :key="pago.id" class="text-center align-middle table-info-light">
                                                <td class="fw-bold bg-light-subtle">{{ formatDate(pago.fecha_pago) }}</td>
                                                <td class="fw-bold text-success text-end bg-light-subtle">{{ formatMoney(pago.monto_pago) }}</td>
                                                <td class="fw-bold text-danger text-end bg-light-subtle">{{ formatMoney(pago.pago_mora) }}</td>
                                                <td class="text-center bg-light-subtle">
                                                    <button class="btn btn-xs btn-outline-info py-0 px-2 fw-bold" @click="verDetallePago(pago)" style="font-size: 9.5px; border-radius: 4px;">
                                                        <i class="fas fa-eye me-1"></i> Ver
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>

                                        <!-- CASO B: LA CUOTA NO TIENE PAGOS -->
                                        <tr v-if="cuota.pagos.length === 0" class="text-center align-middle">
                                            <td class="fw-bold text-dark">{{ cuota.numero }}</td>
                                            <td>{{ formatDate(cuota.fecha) }}</td>
                                            <td class="text-end fw-bold text-secondary">{{ formatMoney(cuota.capital) }}</td>
                                            <td class="text-end text-muted">{{ formatMoney(cuota.interes) }}</td>
                                            <td class="text-end fw-bold text-primary">{{ formatMoney(cuota.total) }}</td>
                                            <td>
                                                <span :class="getBadgeCuotaClass(cuota.estado)" style="width: 110px; display: inline-block; text-align: center;">
                                                    {{ getBadgeCuotaText(cuota.estado) }}
                                                </span>
                                            </td>
                                            <td colspan="4" class="text-muted small py-1 bg-light text-center">
                                                <i class="fas fa-exclamation-circle me-1 text-warning"></i> Sin pagos registrados
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- MODAL DETALLE DE PAGO (GLASSMORPHISM) -->
                <div v-if="mostrarModalPago" class="custom-modal-backdrop" @click.self="mostrarModalPago = false">
                    <div class="custom-modal-content card shadow-lg border-0 animate-scale-in">
                        <div class="card-header bg-info bg-gradient py-2 d-flex justify-content-between align-items-center text-white">
                            <h6 class="my-0 fw-bold text-uppercase" style="font-size: 12px;"><i class="fas fa-receipt me-2"></i> Detalle del Pago Recibido</h6>
                            <button type="button" class="btn-close btn-close-white" @click="mostrarModalPago = false" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-3 font-size-11">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">Recibo / Cód. Transacción</span>
                                    <span class="fw-bold text-dark font-size-12">{{ pagoSeleccionado.codigo_transaccion || 'N/A' }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">Fecha de Pago</span>
                                    <span class="fw-bold text-dark">{{ formatDate(pagoSeleccionado.fecha_pago) }}</span>
                                </div>
                            </div>
                            
                            <div class="border border-info rounded p-2 mb-3 bg-light bg-gradient">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted fw-bold">Monto Total Cobrado:</span>
                                    <span class="fw-bold text-success font-size-12">{{ formatMoney(pagoSeleccionado.monto_pago) }}</span>
                                </div>
                                <hr class="my-1 border-top border-info opacity-20">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Abono a Capital:</span>
                                    <span class="fw-bold text-dark">{{ formatMoney(pagoSeleccionado.pago_capital) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Abono a Interés:</span>
                                    <span class="fw-bold text-dark">{{ formatMoney(pagoSeleccionado.pago_interes) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span class="text-danger">Mora Cobrada:</span>
                                    <span class="fw-bold text-danger">{{ formatMoney(pagoSeleccionado.pago_mora) }}</span>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">Forma de Pago</span>
                                    <span class="badge bg-secondary font-size-9 px-2 py-1 text-uppercase rounded">{{ pagoSeleccionado.forma_pago }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block small mb-1 fw-bold text-uppercase" style="font-size: 9px;">Cajero Responsable</span>
                                    <span class="fw-bold text-dark text-uppercase text-truncate d-block">{{ pagoSeleccionado.cajero_nombre }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end p-2 bg-light">
                            <button class="btn btn-secondary btn-xs px-3 py-1" @click="mostrarModalPago = false" style="font-size: 11px;">
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
            vista: 0,
            creditos: [],
            filtros: {
                id_credito: '',
                buscar_cliente: '',
                estado_plan: 'todos',
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD')
            },
            detalle: {
                credito: {},
                cuotas: []
            },
            pagoSeleccionado: null,
            mostrarModalPago: false,
            expandedCuotas: {}
        };
    },
    computed: {
        tituloFormulario() {
            if (this.detalle && this.detalle.credito && this.detalle.credito.id) {
                return `Detalles de Extracto — Crédito #${this.detalle.credito.id}`;
            }
            return "Detalles de Extracto";
        }
    },
    methods: {
        async getCreditos() {
            this.preloader = true;
            try {
                const response = await axios.get('/get_creditos_rep', { params: this.filtros });
                this.creditos = response.data;
            } catch (error) {
                console.error("Error al obtener créditos:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un problema al obtener el listado de créditos.'
                });
            } finally {
                this.preloader = false;
            }
        },
        async verDetalle(id_plan_pago) {
            this.preloader = true;
            try {
                const response = await axios.get('/get_detalle_credito_extracto', {
                    params: { id_plan_pago }
                });
                this.detalle = response.data;
                this.expandedCuotas = {}; // Resetear estados de expansión
                this.vista = 1; // Cambiar a la vista de detalle
            } catch (error) {
                console.error("Error al obtener detalle del crédito:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información detallada del crédito.'
                });
            } finally {
                this.preloader = false;
            }
        },
        generarPdfCuotasPlanPago(id_plan_pago) {
            const url = `/rep_extracto_credito?id_plan_pago=${id_plan_pago}`;
            window.open(url, '_blank');
        },
        limpiarFiltros() {
            this.filtros = {
                id_credito: '',
                buscar_cliente: '',
                estado_plan: 'todos',
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD')
            };
            this.getCreditos();
        },
        cerrarFormulario() {
            this.vista = 0;
        },
        verDetallePago(pago) {
            this.pagoSeleccionado = pago;
            this.mostrarModalPago = true;
        },
        toggleCuotaPagos(cuotaId) {
            this.expandedCuotas = {
                ...this.expandedCuotas,
                [cuotaId]: !this.expandedCuotas[cuotaId]
            };
        },
        isCuotaExpanded(cuotaId) {
            return !!this.expandedCuotas[cuotaId];
        },
        getSumMontoPago(pagos) {
            return pagos.reduce((sum, p) => sum + parseFloat(p.monto_pago || 0), 0);
        },
        getSumPagoMora(pagos) {
            return pagos.reduce((sum, p) => sum + parseFloat(p.pago_mora || 0), 0);
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
        },
        getBadgeCuotaClass(estado) {
            switch(estado) {
                case 0: return 'badge bg-secondary text-uppercase font-size-9 px-1 rounded'; // Anulado
                case 1: return 'badge bg-warning text-dark text-uppercase font-size-9 px-1 rounded'; // Pendiente
                case 2: return 'badge bg-success text-uppercase font-size-9 px-1 rounded'; // Pagada completamente
                case 3: return 'badge bg-info text-white text-uppercase font-size-9 px-1 rounded'; // Pago parcial
                default: return 'badge bg-light text-uppercase font-size-9 px-1 rounded';
            }
        },
        getBadgeCuotaText(estado) {
            switch(estado) {
                case 0: return 'Anulada';
                case 1: return 'Pendiente';
                case 2: return 'Pagada';
                case 3: return 'P. Parcial';
                default: return 'Desconocido';
            }
        }
    },
    async mounted() {
        await this.getCreditos();
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
.border-start-4 {
    border-left-width: 4px !important;
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
    background-color: rgba(0, 255, 170, 0.15) !important;
    color: #00ffaa !important;
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
.font-size-11 {
    font-size: 11px !important;
}
.font-size-10 {
    font-size: 10px !important;
}
.font-size-9 {
    font-size: 9px !important;
}
.font-size-8 {
    font-size: 8px !important;
}
.btn-xs {
    padding: 3px 8px !important;
    font-size: 10.5px !important;
    border-radius: 4px !important;
}

/* Estilos para el Modal de Pago Personalizado (Glassmorphism Backdrop) */
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
    width: 92%;
    max-width: 380px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3) !important;
}
.animate-scale-in {
    animation: scaleIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes scaleIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* Efecto de fondo celeste sumamente tenue para las filas secundarias de pagos desplegadas */
.table-info-light td {
    background-color: rgba(52, 152, 219, 0.06) !important;
    border-color: rgba(52, 152, 219, 0.15) !important;
}
.btn-info {
    background-color: #38bdf8 !important;
    border-color: #0ea5e9 !important;
    color: #ffffff !important;
}
.btn-info:hover {
    background-color: #0ea5e9 !important;
    border-color: #0284c7 !important;
}


</style>
