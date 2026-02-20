<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">

                <div v-if="view==0">
                    <div class="card">
                        <div class="card-header bg-success py-2">
                            <h5 class="header-title my-0 text-center fw-semibold text-white text-uppercase">
                                Historial de Transacciones (Pagos)
                            </h5>
                        </div>

                        <div class="row mb-3 mt-3">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white mb-3 h-100">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-uppercase font-size-12 mb-2">Total Recaudado (Caja)</h6>
                                        <h4 class="mb-0 fw-bold">{{ formatMonto(kpis.total_recaudado) }} Bs.</h4>
                                        <small class="text-white-50">Suma de Pagos + Multas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white mb-3 h-100">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-uppercase font-size-12 mb-2">Total Multas Cobradas</h6>
                                        <h4 class="mb-0 fw-bold">{{ formatMonto(kpis.total_multas) }} Bs.</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white mb-3 h-100">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-uppercase font-size-12 mb-2">Total Condonado</h6>
                                        <h4 class="mb-0 fw-bold">{{ formatMonto(kpis.total_condonado) }} Bs.</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold" style="font-size: 11px;">DESDE</span>
                                        <input @change="buscarPagos" type="date" v-model="filtros.fecha_inicio" class="form-control">
                                        <span class="input-group-text bg-light fw-bold" style="font-size: 11px;">HASTA</span>
                                        <input @change="buscarPagos" type="date" v-model="filtros.fecha_final" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <select v-model="filtros.criterio" class="form-select" @change="buscarPagos">
                                            <option value="cliente.nombre">Nombre Cliente</option>
                                            <option value="cliente.ci">CI Cliente</option>
                                            <option value="codigo">Cód. Transacción</option>
                                            <option value="users.name">Cajero</option>
                                        </select>
                                        <input type="text" v-model="filtros.buscar" class="form-control" placeholder="Buscar..." @keyup.enter="buscarPagos">
                                        <button class="btn btn-success" @click="buscarPagos">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mt-2" style="font-size:11px">
                                <table class="table table-hover table-striped table-sm" style="font-size:12px">
                                    <thead class="text-white">
                                        <tr class="table-success">
                                            <th class="text-uppercase fw-bold">Cod. Transacción</th>
                                            <th class="text-uppercase fw-bold">Fecha</th>
                                            <th class="text-uppercase fw-bold">Cliente</th>
                                            <th class="text-uppercase fw-bold text-center">Cuotas Pagadas</th>
                                            <th class="text-uppercase fw-bold text-center">Cant.</th>
                                            <th class="text-uppercase fw-bold text-end bg-success text-white">Total Pagado</th>
                                            <th class="text-uppercase fw-bold text-center">Método</th>
                                            <th class="text-uppercase fw-bold">Cajero</th>
                                            <th class="text-uppercase fw-bold text-center">Estado</th>
                                            <th class="text-uppercase fw-bold text-center">Op.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in listaPagos" :key="item.codigo_transaccion">
                                            <td class="fw-bold text-primary">{{ item.codigo_transaccion }}</td>
                                            <td>{{ formatearFecha(item.fecha_pago) }}</td>
                                            <td>
                                                <div v-if="item.cliente_data">
                                                    <span class="fw-bold d-block text-uppercase">
                                                        {{ item.cliente_data.nombre }} {{ item.cliente_data.apellido }}
                                                    </span>
                                                    <small class="text-muted">CI: {{ item.cliente_data.ci }}</small>
                                                </div>
                                                <div v-else class="text-danger">
                                                    <i class="fas fa-exclamation-circle"></i> Datos no disp.
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark text-uppercase rounded text-white">
                                                    Cuotas: {{ item.detalles_cuotas }}
                                                </span>
                                            </td>
                                            <td class="text-center fw-bold">{{ item.cantidad_cuotas }}</td>
                                            
                                            <td class="text-end fw-bold fs-6">
                                                {{ calcularTotalFila(item.total_pagado, item.total_multa) }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge rounded-pill text-dark border text-uppercase">{{ item.forma_pago }}</span>
                                            </td>
                                            <td>{{ item.usuario ? item.usuario.name : 'Sistema' }}</td>
                                            <td class="text-center">
                                                <span v-if="item.estado == 0" class="badge bg-danger">ANULADO</span>
                                                <span v-else class="badge bg-success">COMPLETADO</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-h fs-4"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li @click="imprimirRecibo(item)">
                                                            <a class="dropdown-item text-primary" href="#">
                                                                <i class="fas fa-print me-2"></i> Imprimir Recibo
                                                            </a>
                                                        </li>
                                                        <li v-if="item.estado == 1" @click="anularTransaccion(item)">
                                                            <hr class="dropdown-divider">
                                                            <a class="dropdown-item text-danger" href="#">
                                                                <i class="fas fa-times me-2"></i> Anular Transacción
                                                            </a>
                                                        </li>
                                                        <li @click="verDetalles(item)">
                                                            <hr class="dropdown-divider">
                                                            <a class="dropdown-item text-dark" href="#">
                                                                <i class="fas fa-eye me-2"></i> Ver Detalles
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="listaPagos.length === 0">
                                            <td colspan="10" class="text-center py-4 text-muted">
                                                <i class="fas fa-search me-1"></i> No se encontraron transacciones.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <template v-if="listaPagos.length<=8">
                                    <br><br><br><br><br><br><br>
                                </template>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <nav>
                                    <ul class="pagination">
                                        <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                        </li>
                                        <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page == pagination.current_page }">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                        </li>
                                        <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="view==1" class="card border-dark shadow-lg">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                        <h5 class="card-title mb-0 fw-bold text-uppercase flex-grow-1 text-center text-white">
                            <i class="fas fa-receipt me-2"></i> Detalles Transacción: {{ detalleTransaccion.cabecera?.codigo }}
                        </h5>
                        <button @click="cerrarDetalles()" type="button" class="btn-close btn-close-white"></button>
                    </div>

                    <div class="card-body bg-light">
                        <div class="row mb-4 bg-white p-3 border rounded mx-1">
                            <div class="col-md-6 border-end">
                                <h6 class="text-muted text-uppercase fw-bold font-size-12">Datos del Cliente</h6>
                                <div v-if="detalleTransaccion.cabecera?.cliente">
                                    <h5 class="fw-bold mb-1 text-primary">
                                        {{ detalleTransaccion.cabecera.cliente.nombre }} {{ detalleTransaccion.cabecera.cliente.apellido }}
                                    </h5>
                                    <p class="mb-0 text-dark"><strong>CI:</strong> {{ detalleTransaccion.cabecera.cliente.ci }}</p>
                                    <p class="mb-0 text-dark"><strong>Crédito N°:</strong> {{ detalleTransaccion.cabecera.plan_pago_id }}</p>
                                </div>
                            </div>
                            <div class="col-md-6 ps-4">
                                <h6 class="text-muted text-uppercase fw-bold font-size-12">Datos del Pago</h6>
                                <p class="mb-1"><strong>Fecha:</strong> {{ formatearFecha(detalleTransaccion.cabecera?.fecha) }}</p>
                                <p class="mb-1"><strong>Cajero:</strong> {{ detalleTransaccion.cabecera?.cajero }}</p>
                                <h4 class="text-success fw-bold mt-2 border-top pt-2">
                                    Total: {{ formatMonto(detalleTransaccion.cabecera?.total_transaccion) }} Bs.
                                </h4>
                            </div>
                        </div>

                        <h6 class="fw-bold ms-2 text-uppercase mb-2">Desglose de Cuotas Pagadas</h6>
                        <div class="table-responsive bg-white border rounded">
                            <table class="table table-bordered table-striped mb-0 text-center">
                                <thead class="bg-secondary text-white">
                                    <tr>
                                        <th>Cuota #</th>
                                        <th>Monto Cuota</th>
                                        <th>Multa / Mora</th>
                                        <th>Condonado</th>
                                        <th>Subtotal Pagado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pago in detalleTransaccion.detalles" :key="pago.id">
                                        <td class="fw-bold">Cuota {{ pago.cuota.numero }}</td>
                                        <td class="text-end">{{ formatMonto(pago.monto_cuota) }}</td>
                                        <td class="text-end text-danger">
                                            {{ formatMonto(pago.multa_total) }}
                                            <div v-if="pago.dias_retrasados > 0" class="badge bg-warning text-dark font-size-10">
                                                {{ pago.dias_retrasados }} días retraso
                                            </div>
                                        </td>
                                        <td class="text-end text-success">-{{ formatMonto(pago.monto_condonado) }}</td>
                                        <td class="text-end fw-bold bg-light">
                                            {{ calcularTotalFila(pago.monto_pago, pago.multa_total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white text-end py-3">
                        <button class="btn btn-secondary me-2" @click="cerrarDetalles">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                        <button class="btn btn-primary" @click="imprimirRecibo({codigo_transaccion: detalleTransaccion.cabecera.codigo})">
                            <i class="fas fa-print me-1"></i> Imprimir Recibo
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2';
import axios from 'axios';

export default {
    name: 'HistorialPagos',
    data() {
        return {
            view: 0,
            preloader: false,
            listaPagos: [],
            // Inicialización de objeto para evitar errores undefined
            detalleTransaccion: {}, 
            kpis: {
                total_recaudado: 0,
                total_multas: 0,
                total_condonado: 0
            },
            filtros: {
                fecha_inicio: moment().format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'cliente.nombre',
                buscar: ''
            },
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 2
        }
    },
    computed: {
        pagesNumber() {
            if (!this.pagination.to) return [];
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
    methods: {
        verDetalles(item) {
            this.preloader = true;
            axios.get(`/historial-pagos/detalles/${item.codigo_transaccion}`)
                .then(response => {
                    this.detalleTransaccion = response.data;
                    this.view = 1; 
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
                })
                .finally(() => this.preloader = false);
        },

        // MÉTODO AGREGADO: Cierra la vista de detalles
        cerrarDetalles() {
            this.view = 0;
            this.detalleTransaccion = {}; // Limpiar datos para evitar parpadeos en la próxima apertura
        },

        // Helper para formatear montos y evitar NaN
        formatMonto(val) {
            let num = parseFloat(val);
            if (isNaN(num)) num = 0;
            return num.toFixed(2);
        },

        // Helper para sumar montos seguramente
        calcularTotalFila(monto1, monto2) {
            const m1 = parseFloat(monto1) || 0;
            const m2 = parseFloat(monto2) || 0;
            return (m1 + m2).toFixed(2);
        },

        formatearFecha(fecha) {
            return fecha ? moment(fecha).format('DD/MM/YYYY HH:mm') : '-';
        },
        
        buscarPagos() {
            this.pagination.current_page = 1;
            this.getPagos(1);
        },

        cambiarPagina(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page;
                this.getPagos(page);
            }
        },

        getPagos(page) {
            this.preloader = true;
            axios.get('/historial-pagos', { 
                params: {
                    page: page,
                    fecha_inicio: this.filtros.fecha_inicio,
                    fecha_final: this.filtros.fecha_final,
                    criterio: this.filtros.criterio,
                    buscar: this.filtros.buscar
                }
            })
            .then(response => {
                this.listaPagos = response.data.pagos.data;
                this.pagination = response.data.pagos;
                this.kpis = response.data.kpis;
            })
            .catch(error => {
                console.error("Error obteniendo pagos:", error);
                Swal.fire('Error', 'No se pudo cargar el historial de pagos.', 'error');
            })
            .finally(() => {
                this.preloader = false;
            });
        },

        imprimirRecibo(item) {
            const url = `/imprimir/recibo/${item.codigo_transaccion}`;
            window.open(url, '_blank');
        },

        anularTransaccion(item) {
            Swal.fire({
                title: '¿Anular Transacción?',
                text: `Se anularán TODOS los pagos (${item.cantidad_cuotas} cuotas) asociados a la transacción ${item.codigo_transaccion}. El dinero se descontará de la caja actual.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, anular todo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.preloader = true;
                    axios.post(`/pagos/anular-transaccion`, { 
                        codigo: item.codigo_transaccion 
                    })
                    .then(response => {
                        Swal.fire('Anulado', 'La transacción ha sido anulada correctamente.', 'success');
                        this.getPagos(this.pagination.current_page);
                    })
                    .catch(error => {
                        console.error(error);
                        Swal.fire('Error', error.response?.data?.message || 'Error al anular la transacción.', 'error');
                    })
                    .finally(() => {
                        this.preloader = false;
                    });
                }
            });
        }
    },
    mounted() {
        this.getPagos(1);
    }
}
</script>

<style scoped>
/* Estilos para el Preloader */
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Ajustes Visuales */
.dropdown-toggle::after {
    display: none !important;
}

.badge {
    font-size:11px;
}

.table th {
    vertical-align: middle;
}
.table td {
    vertical-align: middle;
    text-transform: uppercase;
}
</style>