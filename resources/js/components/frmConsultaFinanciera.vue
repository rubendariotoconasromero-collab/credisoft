<template>
    <main class="financial-consultation">

        <div v-if="preloaderCaja || preloaderDiario" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <div class="card border-0">

                    <div class="card-header bg-success bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-white text-uppercase">
                            Consultas Financieras
                        </h5>
                    </div>

                    <div class="card-body pt-3">

                        <!-- TABS NAV -->
                        <ul class="nav nav-pills custom-tabs mb-0 d-flex justify-content-center" role="tablist">
                            <li class="nav-item mx-1">
                                <button @click="cambiarTab('FLUJO_CAJA')"
                                        :class="['nav-link px-4 fw-bold text-uppercase', tabActivo === 'FLUJO_CAJA' ? 'active' : '']"
                                        type="button">
                                    <i class="fas fa-stream me-2"></i> Flujo de Caja
                                </button>
                            </li>
                            <li class="nav-item mx-1">
                                <button @click="cambiarTab('LIBRO_DIARIO')"
                                        :class="['nav-link px-4 fw-bold text-uppercase', tabActivo === 'LIBRO_DIARIO' ? 'active' : '']"
                                        type="button">
                                    <i class="fas fa-arrow-circle-down me-2"></i> Libro Diario
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content bg-white p-3 border rounded-bottom shadow-sm">

                            <!-- ================================================ -->
                            <!-- TAB 1: FLUJO DE CAJA                              -->
                            <!-- Todos los movimientos con saldo bóveda corrido    -->
                            <!-- ================================================ -->
                            <div v-show="tabActivo === 'FLUJO_CAJA'">

                                <!-- Filtros -->
                                <div class="row mb-3 g-2 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Tipo de Libro</label>
                                        <select v-model="filtrosCaja.tipo_libro" class="form-select shadow-sm border-0" @change="buscarCaja()">
                                            <option value="GENERAL">Libro General</option>
                                            <option value="OPERATIVO">Libro Operativo (Caja)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Rango de Fechas</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-muted fw-bold" style="font-size:11px;">DESDE</span>
                                            <input type="date" @change="buscarCaja()" v-model="filtrosCaja.fecha_inicio" class="form-control border-start-0">
                                            <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size:11px;">HASTA</span>
                                            <input type="date" @change="buscarCaja()" v-model="filtrosCaja.fecha_final" class="form-control border-start-0">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Tipo de Movimiento</label>
                                        <select v-model="filtrosCaja.tipo" class="form-select shadow-sm border-0" @change="buscarCaja()">
                                            <option value="TODOS">Todos</option>
                                            <option value="CAPITAL"       v-if="filtrosCaja.tipo_libro === 'GENERAL'">Pago de Capital</option>
                                            <option value="INTERES">Pago de Interés</option>
                                            <option value="MORA">Multas / Mora</option>
                                            <option value="DESEMBOLSO"    v-if="filtrosCaja.tipo_libro === 'GENERAL'">Desembolsos</option>
                                            <option value="GASTOSADM">Gastos Adm.</option>
                                            <option value="INGRESO_CAJA">Ingresos Extra</option>
                                            <option value="EGRESO_CAJA">Egresos Extra</option>
                                            <option value="BOVEDA_INGRESO" v-if="filtrosCaja.tipo_libro === 'GENERAL'">Ingreso Bóveda</option>
                                            <option value="BOVEDA_EGRESO"  v-if="filtrosCaja.tipo_libro === 'GENERAL'">Egreso Bóveda</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Buscar (Descripción)</label>
                                        <input v-model="filtrosCaja.buscar" @keyup.enter="buscarCaja()" type="text"
                                               class="form-control shadow-sm border-0" placeholder="Ej: CREDITO: 5034" />
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-success w-100 shadow-sm fw-bold" @click="buscarCaja()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-1 d-flex gap-1 justify-content-end">
                                        <button @click="exportarCaja()" class="btn btn-success btn-sm shadow-sm fw-bold" title="Exportar Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </button>
                                        <button @click="imprimirCaja()" class="btn btn-warning btn-sm shadow-sm fw-bold" title="Imprimir PDF">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla Flujo de Caja -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-primary text-center align-middle">
                                            <tr>
                                                <th width="4%">Nro</th>
                                                <th width="9%">Fecha</th>
                                                <th width="12%" class="text-start ps-2">Tipo</th>
                                                <th class="text-start ps-2">Descripción</th>
                                                <th width="11%" class="text-success bg-success bg-opacity-10">Debe (Bs)</th>
                                                <th width="11%" class="text-danger bg-danger bg-opacity-10">Haber (Bs)</th>
                                                <th width="13%" class="bg-warning bg-opacity-25 fw-bold">Capital Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in movimientosCaja" :key="item.nro">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-secondary ps-2" style="font-size:0.7rem;">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase" style="font-size:0.75rem;">{{ item.descripcion }}</td>
                                                <td class="text-end fw-semibold text-success bg-success bg-opacity-10">
                                                    {{ item.debe > 0 ? formatNumero(item.debe) : '' }}
                                                </td>
                                                <td class="text-end fw-semibold text-danger bg-danger bg-opacity-10">
                                                    {{ item.haber > 0 ? formatNumero(item.haber) : '' }}
                                                </td>
                                                <td class="text-end fw-bold bg-warning bg-opacity-25">
                                                    {{ formatNumero(item.saldo) }}
                                                </td>
                                            </tr>
                                            <tr v-if="movimientosCaja.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x d-block mb-2 opacity-25"></i>
                                                    No hay movimientos en el período seleccionado.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-secondary fw-bold text-dark">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3">TOTAL DEL PERIODO:</td>
                                                <td class="text-end text-success">{{ formatNumero(totalIngresosCaja) }}</td>
                                                <td class="text-end text-danger">{{ formatNumero(totalEgresosCaja) }}</td>
                                                <td class="text-end text-muted" style="font-size:0.7rem;">excl. transf. internas</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Capital Total (último asiento) -->
                                <div class="d-flex justify-content-end mt-3">
                                    <div class="card border-0 shadow rounded-3 bg-dark text-white" style="min-width:340px;">
                                        <div class="card-body py-2 px-4 d-flex justify-content-between align-items-center gap-4">
                                            <div>
                                                <div class="fw-bold text-uppercase text-white-50" style="font-size:0.7rem; letter-spacing:1px;">
                                                    <i class="fas fa-coins me-1"></i> Capital Total (Bóveda + Caja)
                                                </div>
                                                <div class="text-white-50" style="font-size:0.65rem;">
                                                    al {{ filtrosCaja.fecha_final }}
                                                </div>
                                            </div>
                                            <div class="fs-4 fw-bold" :class="saldoBoveda >= 0 ? 'text-success' : 'text-danger'">
                                                {{ formatNumero(saldoBoveda) }}
                                                <small class="fs-6 text-white-50">Bs</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paginación Flujo de Caja -->
                                <div class="d-flex justify-content-between align-items-center mt-3" v-if="paginacionCaja.last_page > 1">
                                    <span class="text-muted small">
                                        Página {{ paginacionCaja.current_page }} de {{ paginacionCaja.last_page }}
                                        ({{ paginacionCaja.total }} registros)
                                    </span>
                                    <nav>
                                        <ul class="pagination shadow-sm mb-0">
                                            <li class="page-item" :class="{disabled: paginacionCaja.current_page <= 1}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(paginacionCaja.current_page - 1)">Anterior</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberCaja" :key="page"
                                                :class="{active: page == paginacionCaja.current_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" :class="{disabled: paginacionCaja.current_page >= paginacionCaja.last_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(paginacionCaja.current_page + 1)">Siguiente</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <!-- ================================================ -->
                            <!-- TAB 2: LIBRO DIARIO                               -->
                            <!-- Solo ingresos recaudados                          -->
                            <!-- ================================================ -->
                            <div v-show="tabActivo === 'LIBRO_DIARIO'">

                                <!-- Filtros Libro Diario -->
                                <div class="row mb-3 g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Rango de Fechas</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-muted fw-bold" style="font-size:11px;">DESDE</span>
                                            <input type="date" @change="buscarDiario()" v-model="filtrosDiario.fecha_inicio" class="form-control border-start-0">
                                            <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size:11px;">HASTA</span>
                                            <input type="date" @change="buscarDiario()" v-model="filtrosDiario.fecha_final" class="form-control border-start-0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Tipo de Ingreso</label>
                                        <select v-model="filtrosDiario.tipo" class="form-select shadow-sm border-0" @change="buscarDiario()">
                                            <option value="TODOS">Todos los ingresos</option>
                                            <option value="INTERES">Pago de Interés</option>
                                            <option value="MORA">Multas / Mora</option>
                                            <option value="GASTOSADM">Gastos Administrativos</option>
                                            <option value="INGRESO_CAJA">Otros Ingresos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Buscar (Descripción)</label>
                                        <input v-model="filtrosDiario.buscar" @keyup.enter="buscarDiario()" type="text"
                                               class="form-control shadow-sm border-0" placeholder="Descripción..." />
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-success w-100 shadow-sm fw-bold" @click="buscarDiario()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-1 d-flex gap-1 justify-content-end">
                                        <button @click="exportarDiario()" class="btn btn-success btn-sm shadow-sm fw-bold" title="Exportar Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </button>
                                        <button @click="imprimirDiario()" class="btn btn-warning btn-sm shadow-sm fw-bold" title="Imprimir PDF">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla Libro Diario -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-success text-center align-middle">
                                            <tr>
                                                <th width="4%">Nro</th>
                                                <th width="9%">Fecha</th>
                                                <th width="13%" class="text-start ps-2">Tipo de Ingreso</th>
                                                <th class="text-start ps-2">Detalle / Descripción</th>
                                                <th width="12%" class="text-success bg-success bg-opacity-10">Debe (Bs)</th>
                                                <th width="12%" class="text-danger bg-danger bg-opacity-10">Haber (Bs)</th>
                                                <th width="13%" class="bg-warning bg-opacity-25 fw-bold">Total Acum. (Bs)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in ingresosDiarioPaginados" :key="'ing_' + item.nro">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-dark ps-2" style="font-size:0.7rem;">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase" style="font-size:0.75rem;">{{ item.descripcion }}</td>
                                                <td class="text-end fw-semibold text-success bg-success bg-opacity-10">
                                                    {{ item.debe > 0 ? formatNumero(item.debe) : '' }}
                                                </td>
                                                <td class="text-end fw-semibold text-danger bg-danger bg-opacity-10">
                                                    {{ item.haber > 0 ? formatNumero(item.haber) : '' }}
                                                </td>
                                                <td class="text-end fw-bold bg-warning bg-opacity-25">
                                                    {{ formatNumero(item.totalAcumulado) }}
                                                </td>
                                            </tr>
                                            <tr v-if="ingresosDiario.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x d-block mb-2 opacity-25"></i>
                                                    No hay ingresos en el período seleccionado.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-success fw-bold text-dark">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3">TOTAL INGRESOS DEL PERIODO:</td>
                                                <td class="text-end fs-6">{{ formatNumero(totalIngresosDiario) }}</td>
                                                <td></td>
                                                <td class="text-end fs-6 bg-warning bg-opacity-25">{{ formatNumero(totalIngresosDiario) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Capital Total (Libro Diario) -->
                                <div class="d-flex justify-content-end mt-3">
                                    <div class="card border-0 shadow rounded-3 bg-dark text-white" style="min-width:340px;">
                                        <div class="card-body py-2 px-4 d-flex justify-content-between align-items-center gap-4">
                                            <div>
                                                <div class="fw-bold text-uppercase text-white-50" style="font-size:0.7rem; letter-spacing:1px;">
                                                    <i class="fas fa-coins me-1"></i> Capital Total (Bóveda + Caja)
                                                </div>
                                                <div class="text-white-50" style="font-size:0.65rem;">
                                                    al {{ filtrosDiario.fecha_final }}
                                                </div>
                                            </div>
                                            <div class="fs-4 fw-bold" :class="saldoTotalDiario >= 0 ? 'text-success' : 'text-danger'">
                                                {{ formatNumero(saldoTotalDiario) }}
                                                <small class="fs-6 text-white-50">Bs</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paginación Libro Diario (client-side) -->
                                <div class="d-flex justify-content-between align-items-center mt-3" v-if="paginacionDiario.last_page > 1">
                                    <span class="text-muted small">
                                        Página {{ paginacionDiario.current_page }} de {{ paginacionDiario.last_page }}
                                        ({{ paginacionDiario.total }} registros)
                                    </span>
                                    <nav>
                                        <ul class="pagination shadow-sm mb-0">
                                            <li class="page-item" :class="{disabled: paginacionDiario.current_page <= 1}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaDiario(paginacionDiario.current_page - 1)">Anterior</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberDiario" :key="page"
                                                :class="{active: page == paginacionDiario.current_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaDiario(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" :class="{disabled: paginacionDiario.current_page >= paginacionDiario.last_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaDiario(paginacionDiario.current_page + 1)">Siguiente</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                        </div><!-- /tab-content -->
                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div>
        </div>
    </main>
</template>

<script>
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            tabActivo: 'FLUJO_CAJA',
            preloaderCaja: false,
            preloaderDiario: false,

            // ── Flujo de Caja ──────────────────────────────────────────
            filtrosCaja: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final:  moment().format('YYYY-MM-DD'),
                tipo_libro:   'GENERAL',
                tipo:         'TODOS',
                buscar:       '',
            },
            movimientosCaja:   [],
            totalIngresosCaja: 0,
            totalEgresosCaja:  0,
            saldoBoveda:       0,
            paginacionCaja: { current_page: 1, last_page: 1, total: 0 },

            // ── Libro Diario ───────────────────────────────────────────
            filtrosDiario: {
                fecha_inicio: moment().startOf('month').format('YYYY-MM-DD'),
                fecha_final:  moment().format('YYYY-MM-DD'),
                tipo:         'TODOS',
                buscar:       '',
            },
            ingresosDiario:      [],
            totalIngresosDiario: 0,
            saldoTotalDiario:    0,
            paginaActualDiario:  1,
            libroDiarioCargado:  false,

            offset: 2,
        };
    },

    computed: {
        // Flujo de Caja — páginas para el paginador
        pagesNumberCaja() {
            return this.buildPages(this.paginacionCaja.current_page, this.paginacionCaja.last_page);
        },

        // Libro Diario — paginación client-side
        paginacionDiario() {
            const perPage = 20;
            const total   = this.ingresosDiario.length;
            return {
                current_page: this.paginaActualDiario,
                last_page:    Math.max(1, Math.ceil(total / perPage)),
                total,
            };
        },
        ingresosDiarioPaginados() {
            const perPage = 20;
            const start   = (this.paginaActualDiario - 1) * perPage;
            return this.ingresosDiario.slice(start, start + perPage);
        },
        pagesNumberDiario() {
            return this.buildPages(this.paginaActualDiario, this.paginacionDiario.last_page);
        },
    },

    mounted() {
        this.fetchFlujoCaja(1);
    },

    methods: {
        // ── Helpers ───────────────────────────────────────────────────
        buildPages(current, lastPage) {
            let from = Math.max(1, current - this.offset);
            let to   = Math.min(lastPage, from + this.offset * 2);
            const pages = [];
            for (let i = from; i <= to; i++) pages.push(i);
            return pages;
        },

        formatNumero(numero) {
            if (numero == null) return '0.00';
            return new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(numero);
        },

        async generarPDF(endpoint) {
            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Procesando el PDF, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });
            try {
                const response = await axios.get(endpoint, { responseType: 'blob' });
                const url = window.URL.createObjectURL(
                    new Blob([response.data], { type: 'application/pdf' })
                );
                Swal.close();
                window.open(url, '_blank');
                setTimeout(() => window.URL.revokeObjectURL(url), 10000);
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo generar el reporte.', 'error');
            }
        },

        // ── Tabs ──────────────────────────────────────────────────────
        cambiarTab(tab) {
            this.tabActivo = tab;
            if (tab === 'LIBRO_DIARIO' && !this.libroDiarioCargado) {
                this.fetchLibroDiario();
            }
        },

        // ── Flujo de Caja ─────────────────────────────────────────────
        buscarCaja() {
            const soloGeneral = ['CAPITAL', 'DESEMBOLSO', 'BOVEDA_INGRESO', 'BOVEDA_EGRESO'];
            if (this.filtrosCaja.tipo_libro === 'OPERATIVO' && soloGeneral.includes(this.filtrosCaja.tipo)) {
                this.filtrosCaja.tipo = 'TODOS';
            }
            this.fetchFlujoCaja(1);
        },

        cambiarPaginaCaja(page) {
            if (page >= 1 && page <= this.paginacionCaja.last_page) {
                this.fetchFlujoCaja(page);
            }
        },

        async fetchFlujoCaja(page = 1) {
            this.preloaderCaja = true;
            try {
                const response = await axios.get('/libro-mayor', {
                    params: { ...this.filtrosCaja, page },
                });
                this.movimientosCaja   = response.data.movimientos.data;
                this.paginacionCaja    = response.data.movimientos;
                this.totalIngresosCaja = response.data.totales.ingresos;
                this.totalEgresosCaja  = response.data.totales.egresos;
                this.saldoBoveda       = response.data.saldo_boveda ?? 0;
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los movimientos.', 'error');
            } finally {
                this.preloaderCaja = false;
            }
        },

        exportarCaja() {
            const qs = new URLSearchParams({ ...this.filtrosCaja }).toString();
            window.open(`/reportes/libro-mayor/excel?${qs}`, '_blank');
        },

        async imprimirCaja() {
            const qs = new URLSearchParams({ ...this.filtrosCaja }).toString();
            await this.generarPDF(`/reportes/libro-mayor?${qs}`);
        },

        // ── Libro Diario ──────────────────────────────────────────────
        buscarDiario() {
            this.paginaActualDiario = 1;
            this.fetchLibroDiario();
        },

        cambiarPaginaDiario(page) {
            if (page >= 1 && page <= this.paginacionDiario.last_page) {
                this.paginaActualDiario = page;
            }
        },

        async fetchLibroDiario() {
            this.preloaderDiario = true;
            try {
                const response = await axios.get('/libro-mayor', {
                    params: {
                        ...this.filtrosDiario,
                        tipo_libro: 'GENERAL',
                        page: 1,
                    },
                });
                const tiposIngresoReal = ['INTERES', 'MORA', 'GASTOSADM', 'INGRESO_CAJA'];
                let acumulado = 0;
                this.ingresosDiario = (response.data.ingresos_lista || [])
                    .filter(item => tiposIngresoReal.includes(item.tipo))
                    .map((item, idx) => {
                        acumulado += (item.debe || 0);
                        return { ...item, nro: idx + 1, totalAcumulado: acumulado };
                    });
                this.totalIngresosDiario = acumulado;
                this.saldoTotalDiario    = response.data.saldo_boveda ?? 0;
                this.libroDiarioCargado  = true;
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los ingresos.', 'error');
            } finally {
                this.preloaderDiario = false;
            }
        },

        exportarDiario() {
            const qs = new URLSearchParams({ ...this.filtrosDiario, tipo_libro: 'GENERAL' }).toString();
            window.open(`/reportes/ingresos/excel?${qs}`, '_blank');
        },

        async imprimirDiario() {
            const qs = new URLSearchParams({ ...this.filtrosDiario, tipo_libro: 'GENERAL' }).toString();
            await this.generarPDF(`/reportes/ingresos?${qs}`);
        },
    },
};
</script>

<style scoped>
/* Tabs */
.custom-tabs {
    border-bottom: 2px solid #dee2e6;
}
.custom-tabs .nav-link {
    color: #6c757d !important;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    font-size: 0.85rem;
    margin-bottom: -2px;
    transition: all 0.05s ease;
}
.custom-tabs .nav-link:hover {
    background-color: #198754 !important;
    color: #fff !important;
}
.custom-tabs .nav-link.active {
    background-color: #fff;
    color: #198754 !important;
    border-color: #dee2e6;
    border-top: 3px solid #198754;
    border-bottom: 3px solid #fff;
    z-index: 2;
    position: relative;
}
.custom-tabs .nav-link.active:hover { color: #fff !important; }

/* Tabla */
.ledger-table { font-size: 0.8rem; }
.ledger-table th {
    vertical-align: middle;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 10px 5px;
}
.ledger-table td { vertical-align: middle; padding: 6px 5px; }

/* Preloader */
.preloader {
    position: fixed;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.spinner {
    border: 4px solid rgba(25, 135, 84, 0.2);
    border-top: 4px solid #198754;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
