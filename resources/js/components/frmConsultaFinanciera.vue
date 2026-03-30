<template>
    <main class="financial-consultation">
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <div v-if="view === 0" class="card border-0">
                 
                    <div class="card-header bg-success bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-white fw-bold text-uppercase">
                            Libro Mayor y Consultas Financieras
                        </h5>
                    </div>

                    <div class="card-body pt-3">
                        
                        <div class="row mb-4 g-2 align-items-end">
                            
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1">Tipo de Libro</label>
                                <select v-model="filtros.tipo_libro" class="form-select shadow-sm border-0" @change="buscarConFiltros()">
                                    <option value="GENERAL">Libro General</option>
                                    <option value="OPERATIVO">Libro Operativo (Caja)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Rango de Fechas</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 11px;">DESDE</span>
                                    <input type="date" @change="buscarConFiltros()" v-model="filtros.fecha_inicio" class="form-control border-start-0">
                                    <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 11px;">HASTA</span>
                                    <input type="date" @change="buscarConFiltros()" v-model="filtros.fecha_final" class="form-control border-start-0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Tipo de Movimiento</label>
                                <select v-model="filtros.tipo" class="form-select shadow-sm border-0" @change="buscarConFiltros()">
                                    <option value="TODOS">Todos los movimientos</option>
                                    <option value="CAPITAL" v-if="filtros.tipo_libro === 'GENERAL'">Pago de Capital</option>
                                    <option value="INTERES">Pago de Interés</option>
                                    <option value="MORA">Multas / Mora</option>
                                    <option value="DESEMBOLSO" v-if="filtros.tipo_libro === 'GENERAL'">Desembolsos</option>
                                    <option value="GASTOSADM">Gastos Administrativos</option>
                                    <option value="INGRESO_CAJA">Ingresos Extra</option>
                                    <option value="EGRESO_CAJA">Egresos Extra</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1">Buscar (Descripción)</label>
                                <input v-model="filtros.buscar" @keyup.enter="buscarConFiltros()" type="text" class="form-control shadow-sm border-0" placeholder="Ej. CREDITO: 5034" />
                            </div>
                            <div class="col-md-1 text-end">
                                <button class="btn btn-success w-100 shadow-sm fw-bold" @click="buscarConFiltros()"><i class="fas fa-search"></i></button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 text-end">
                                <button @click="exportarExcelDinamico" class="btn btn-success fw-bold me-2 text-white">
                                    <i class="fas fa-file-excel me-1"></i> Exportar Excel
                                </button>
                                <button @click="imprimirReporteDinamico" class="btn btn-warning fw-bold">
                                    <i class="fas fa-print me-1"></i> Imprimir Reporte
                                </button>
                            </div>
                        </div>

                        <ul class="nav nav-pills custom-tabs mb-0 d-flex justify-content-center" role="tablist">
                            <li class="nav-item mx-1" role="presentation">
                                <button @click="tabActivo = 'GENERAL'" class="nav-link active px-4 fw-bold text-uppercase" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab">
                                    Tabla General
                                </button>
                            </li>
                            <li class="nav-item mx-1" role="presentation">
                                <button @click="tabActivo = 'INGRESOS'" class="nav-link px-4 fw-bold text-uppercase text-success" data-bs-toggle="pill" data-bs-target="#tab-ingresos" type="button" role="tab">
                                    Solo Ingresos
                                </button>
                            </li>
                            <li class="nav-item mx-1" role="presentation">
                                <button @click="tabActivo = 'EGRESOS'" class="nav-link px-4 fw-bold text-uppercase text-danger" data-bs-toggle="pill" data-bs-target="#tab-egresos" type="button" role="tab">
                                    Solo Egresos
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content bg-white p-3 border rounded-bottom shadow-sm">
                            
                            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-success text-center align-middle">
                                            <tr>
                                                <th width="5%">Nro</th>
                                                <th width="10%">Fecha</th>
                                                <th class="text-start" width="15%">Tipo</th>
                                                <th width="35%" class="text-start ps-2">Descripción</th>
                                                <th width="11%" class="text-primary bg-primary bg-opacity-10">Ingreso (Debe)</th>
                                                <th width="11%" class="text-danger bg-danger bg-opacity-10">Egreso (Haber)</th>
                                                <th width="13%" class="bg-light border-start-2 border-dark">Saldo Caja</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaFiltrada" :key="item.nro">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-secondary" style="font-size: 0.7rem;">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase">{{ item.descripcion }}</td>
                                                
                                                <td class="text-end fw-semibold text-primary bg-primary bg-opacity-10">
                                                    {{ item.debe > 0 ? formatNumero(item.debe) : '' }}
                                                </td>
                                                <td class="text-end fw-semibold text-danger bg-danger bg-opacity-10">
                                                    {{ item.haber > 0 ? formatNumero(item.haber) : '' }}
                                                </td>
                                                
                                                <td class="text-end fw-bold border-start-2 border-dark bg-light">{{ formatNumero(item.saldo) }}</td>
                                            </tr>
                                            <tr v-if="listaFiltrada.length === 0">
                                                <td colspan="7" class="text-center py-4 text-muted fst-italic">No hay movimientos registrados.</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-secondary text-dark fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 fw-bold text-dark">TOTAL DEL PERIODO:</td>
                                                <td class="text-end fw-bold text-dark">{{ formatNumero(totalIngresosPeriodo) }}</td>
                                                <td class="text-end fw-bold text-dark">{{ formatNumero(totalEgresosPeriodo) }}</td>
                                                <td class="text-end fw-bold text-dark">-</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-ingresos" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-success bg-opacity-10 text-center align-middle">
                                            <tr>
                                                <th width="5%">Nro</th>
                                                <th width="10%">Fecha</th>
                                                <th width="15%" class="text-start">Tipo de Ingreso</th>
                                                <th width="50%" class="text-start ps-2">Detalle / Descripción</th>
                                                <th width="20%" class="text-dark fw-bold">Monto Recaudado <span class="text-capitalize">(Bs)</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in ingresosFiltrados" :key="'ing_'+item.nro">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-dark" style="font-size: 0.7rem;">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase">{{ item.descripcion }}</td>
                                                <td class="text-end fw-bold text-dark fs-6 bg-success bg-opacity-10">{{ formatNumero(item.debe) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-secondary text-dark fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 fs-6">TOTAL INGRESOS FILTRADOS:</td>
                                                <td class="text-end fs-6">{{ formatNumero(totalIngresosPeriodo) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-egresos" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-danger bg-opacity-10 text-center align-middle">
                                            <tr>
                                                <th width="5%">Nro</th>
                                                <th width="10%">Fecha</th>
                                                <th class="text-start" width="15%">Tipo de Egreso</th>
                                                <th width="50%" class="text-start ps-2">Detalle / Descripción</th>
                                                <th width="20%" class="text-dark fw-bold">Monto Saliente <span class="text-capitalize">(Bs)</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in egresosFiltrados" :key="'egr_'+item.nro">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-dark" style="font-size: 0.7rem;">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase">{{ item.descripcion }}</td>
                                                <td class="text-end fw-bold text-dark fs-6 bg-danger bg-opacity-10">{{ formatNumero(item.haber) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-secondary text-dark fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 fs-6">TOTAL EGRESOS FILTRADOS:</td>
                                                <td class="text-end fs-6">{{ formatNumero(totalEgresosPeriodo) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.last_page > 1">
                            <span class="text-muted small">
                                Mostrando página {{ pagination.current_page }} de {{ pagination.last_page }} 
                                (Total registros: {{ pagination.total }})
                            </span>
                            <nav>
                                <ul class="pagination shadow-sm mb-0">
                                    <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Anterior</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page == pagination.current_page }">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: pagination.current_page >= pagination.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

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
            tabActivo: 'GENERAL',
            preloader: false,
            view: 0,
            filtros: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                tipo_libro: 'GENERAL', // NUEVA VARIABLE
                tipo: 'TODOS',
                buscar: '',
                page: 1
            },
            movimientos: [],
            totalIngresosPeriodo: 0,
            totalEgresosPeriodo: 0,
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 0
            },
            offset: 2
        };
    },
    computed: {
        listaFiltrada() {
            return this.movimientos;
        },
        ingresosFiltrados() {
            return this.movimientos.filter(i => parseFloat(i.debe) > 0);
        },
        egresosFiltrados() {
            return this.movimientos.filter(i => parseFloat(i.haber) > 0);
        },
        pagesNumber() {
            if (!this.pagination.last_page) return [];
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) to = this.pagination.last_page;
            
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    mounted() {
        this.fetchLibroMayor(1);
    },
    methods: {
        exportarExcelDinamico() {
            const queryParams = new URLSearchParams({
                fecha_inicio: this.filtros.fecha_inicio,
                fecha_final: this.filtros.fecha_final,
                tipo_libro: this.filtros.tipo_libro, // Pasamos el tipo de libro
                tipo: this.filtros.tipo,
                buscar: this.filtros.buscar
            }).toString();
            
            let endpoint = '';
            if (this.tabActivo === 'GENERAL') endpoint = '/reportes/libro-mayor/excel';
            else if (this.tabActivo === 'INGRESOS') endpoint = '/reportes/ingresos/excel';
            else if (this.tabActivo === 'EGRESOS') endpoint = '/reportes/egresos/excel';

            window.open(`${endpoint}?${queryParams}`, '_blank');
        },
        
        async imprimirReporteDinamico() {
            let endpoint = '';
            if (this.tabActivo === 'GENERAL') endpoint = '/reportes/libro-mayor';
            else if (this.tabActivo === 'INGRESOS') endpoint = '/reportes/ingresos';
            else if (this.tabActivo === 'EGRESOS') endpoint = '/reportes/egresos';

            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Procesando el documento PDF, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const queryParams = new URLSearchParams({
                    fecha_inicio: this.filtros.fecha_inicio,
                    fecha_final: this.filtros.fecha_final,
                    tipo_libro: this.filtros.tipo_libro, // Pasamos el tipo de libro
                    tipo: this.filtros.tipo,
                    buscar: this.filtros.buscar
                }).toString();
                
                const response = await axios.get(`${endpoint}?${queryParams}`, {
                    responseType: 'blob'
                });

                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                
                Swal.close();
                window.open(url, '_blank');
                setTimeout(() => window.URL.revokeObjectURL(url), 10000);

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo generar el reporte.', 'error');
            }
        },

        formatNumero(numero) {
            if (numero === undefined || numero === null) return '0.00';
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numero);
        },
        buscarConFiltros() {
            // Si el usuario cambia de libro y tenía un filtro no válido, lo reseteamos
            if (this.filtros.tipo_libro === 'OPERATIVO' && (this.filtros.tipo === 'CAPITAL' || this.filtros.tipo === 'DESEMBOLSO')) {
                this.filtros.tipo = 'TODOS';
            }
            this.fetchLibroMayor(1);
        },
        cambiarPagina(page) {
            this.fetchLibroMayor(page);
        },
        async fetchLibroMayor(page) {
            this.preloader = true;
            this.filtros.page = page; 
            
            try {
                const response = await axios.get('/libro-mayor', { params: this.filtros });
                
                this.movimientos = response.data.movimientos.data;
                this.pagination = response.data.movimientos;
                this.totalIngresosPeriodo = response.data.totales.ingresos;
                this.totalEgresosPeriodo = response.data.totales.egresos;
                
            } catch (error) {
                console.error("Error obteniendo libro mayor:", error);
            } finally {
                this.preloader = false;
            }
        }
    }
};
</script>

<style scoped>
/* Tabs Personalizados */
.custom-tabs {
    border-bottom: 2px solid #dee2e6;
}
.custom-tabs .nav-link {
    color: #6c757d !important;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    transition: all 0.05s ease;
    font-size: 0.85rem;
    margin-bottom: -2px;
}
.custom-tabs .nav-link:hover {
    background-color: #198754 !important;
    color:#ffffff !important;
}
.custom-tabs .nav-link.active {
    background-color: #ffffff;
    color: #198754 !important;
    border-color: #dee2e6;
    border-top: 3px solid #198754;
    border-bottom: 3px solid #ffffff;
    z-index: 2;
    position: relative;
}
.custom-tabs .nav-link.active:hover { color: #ffffff !important; }

/* Tabla Ledger */
.ledger-table {
    font-family: inherit;
    font-size: 0.8rem;
}
.ledger-table th {
    vertical-align: middle;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 10px 5px;
}
.ledger-table td {
    vertical-align: middle;
    padding: 6px 5px;
}
.border-start-2 { border-left: 2px solid #dee2e6 !important; }

/* Animaciones */
.fade-in-animation { animation: fadeIn 0.3s ease-in-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Preloader */
.preloader {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex; justify-content: center; align-items: center; z-index: 9999;
}
.spinner {
    border: 4px solid rgba(25, 135, 84, 0.2);
    border-top: 4px solid #198754;
    border-radius: 50%;
    width: 50px; height: 50px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>