<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">

                <div v-if="view==0" class="card shadow-sm border-0">
                    <div class="card-header bg-success py-3 d-flex justify-content-center align-items-center">
                        <h5 class="header-title my-0 fw-bold text-white text-uppercase">
                            Historial de Desembolsos
                        </h5>
                    </div>

                    <div class="card-body bg-white">
                        <!-- Summary Cards (Restored to original size/layout) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="card border-primary border-opacity-25 shadow-sm h-100 bg-primary bg-opacity-10">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div>
                                            <h6 class="text-uppercase text-primary fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                                Total Capital Desembolsado
                                            </h6>
                                            <h4 class="mb-0 fw-bold text-dark">{{ formatNumero(totalDesembolsos) }} <small class="fs-6 text-muted">Bs.</small></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-info border-opacity-50 shadow-sm h-100 bg-info bg-opacity-10">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div>
                                            <h6 class="text-uppercase text-info-emphasis fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                                Gastos Administrativos Retenidos
                                            </h6>
                                            <h4 class="mb-0 fw-bold text-dark">{{ formatNumero(totalPagosAdm) }} <small class="fs-6 text-muted">Bs.</small></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters Row (Compacted) -->
                        <div class="row g-2 mb-3 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-light text-secondary fw-bold" style="font-size: 10px;">DESDE</span>
                                    <input @change="buscarDesembolsos" type="date" v-model="filtros.fecha_inicio" class="form-control">
                                    <span class="input-group-text bg-light text-secondary fw-bold" style="font-size: 10px;">HASTA</span>
                                    <input @change="buscarDesembolsos" type="date" v-model="filtros.fecha_final" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-light text-secondary fw-bold" style="font-size: 10px;"><i class="fas fa-filter"></i></span>
                                    <select v-model="filtros.criterio" class="form-select bg-white" style="max-width: 220px;" @change="buscarDesembolsos">
                                        <option value="cliente.nombre">Cliente</option>
                                        <option value="cliente.ci">CI</option>
                                        <option value="plan_pago.id">Cód. Crédito</option>
                                    </select>
                                    <input type="text" v-model="filtros.buscar" class="form-control" placeholder="Buscar..." @keyup.enter="buscarDesembolsos">
                                    <button class="btn btn-success px-3" @click="buscarDesembolsos">
                                        <i class="fas fa-search me-1"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table (Compacted) -->
                        <div class="table-container-custom table-responsive mt-2">
                            <table class="table table-striped table-hover table-compact-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">Crédito</th>
                                        <th class="text-center">Fecha</th>
                                        <th>Cliente</th>
                                        <th>Garantía</th>
                                        <th class="text-center">Plazo</th>
                                        <th class="text-end">Monto Des.</th>
                                        <th class="text-end">Gto. Adm.</th>
                                        <th>Asesor</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Op.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in listaDesembolsos" :key="item.id_plan_pago">
                                        <td class="text-center font-monospace py-1 fw-bold text-primary">#{{ item.id_plan_pago }}</td>
                                        <td class="text-center text-secondary" style="font-size: 0.78rem;">{{ formatearFecha(item.fecha) }}</td>
                                        <td class="py-1">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark text-uppercase" style="font-size: 0.85rem;">{{ item.cliente }}</span>
                                                <span class="text-muted small" style="font-size: 0.72rem;"><strong class="fw-semibold">CI:</strong> {{ item.ci }} {{ item.lugar_expedicion }}</span>
                                            </div>
                                        </td>
                                        <td class="text-uppercase py-1 text-truncate" style="max-width: 120px;" :title="item.tipo_garantia">{{ item.tipo_garantia }}</td>
                                        <td class="text-center py-1">
                                            <span class="badge bg-light text-dark border border-secondary-subtle px-2 py-1 rounded" style="font-size: 0.75rem;">
                                                {{ item.nro_cuotas }} - {{ item.lapso_capital }}
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-success bg-success bg-opacity-10 py-1">{{ formatNumero(item.monto) }}</td>
                                        <td class="text-end font-monospace fw-semibold text-info py-1">{{ formatNumero(item.monto_pago_adm) }}</td>
                                        <td class="text-uppercase py-1 text-secondary" style="font-size: 0.8rem;">{{ item.asesor }}</td>
                                        <td class="text-center py-1">
                                            <span v-if="item.estado == 1" class="badge bg-danger rounded badge-custom">Anulado</span>
                                            <span v-else class="badge bg-success rounded badge-custom">Entregado</span>
                                        </td>
                                        <td class="text-center py-1">
                                            <div class="btn-group">
                                                <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-h fs-5"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <li @click="verDetalles(item)">
                                                        <a class="dropdown-item text-dark" href="#">
                                                            <i class="fas fa-eye me-2 text-info"></i> Ver Detalles
                                                        </a>
                                                    </li>
                                                    <li @click="generarComprobante(item)">
                                                        <a class="dropdown-item text-dark" href="#">
                                                            <i class="fas fa-print me-2 text-primary"></i> Imprimir Comprobante
                                                        </a>
                                                    </li>
                                                    <li v-if="rolUsuario == 'Administrador' && item.estado != 1 && esHoy(item.fecha)" @click="anularDesembolso(item)">
                                                        <hr class="dropdown-divider">
                                                        <a class="dropdown-item text-danger fw-bold" href="#">
                                                            <i class="fas fa-times me-2"></i> Anular Desembolso
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="listaDesembolsos.length === 0">
                                        <td colspan="10" class="text-center py-5 text-muted fst-italic">
                                            <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                            No se encontraron desembolsos en este rango.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-end mt-3" v-if="pagination.last_page > 1">
                            <nav>
                                <ul class="pagination pagination-sm shadow-sm mb-1">
                                    <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page == pagination.current_page }">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: pagination.current_page >= pagination.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Detail View (Restored to original padding/sizes) -->
                <div v-if="view==1" class="card border-0 shadow-lg fade-in-animation">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title mb-0 fw-bold text-uppercase flex-grow-1 text-center text-white">
                            Detalles del Desembolso
                        </h5>
                        <button @click="cerrarDetalles()" type="button" class="btn-close btn-close-white"></button>
                    </div>

                    <div class="card-body bg-light p-4">
                        <template v-if="detalles.desembolso">

                            <div class="card mb-4 border-start border-4 border-primary shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="fw-bold text-uppercase text-muted mb-0"><i class="fas fa-user-circle me-2"></i>Información del Cliente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 text-center mb-3 mb-md-0">
                                            <img :src="detalles.desembolso.imagen ? '/img/cliente/'+detalles.desembolso.imagen : '/img/empresa/user_img2_old.png'"
                                                class="rounded-circle border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;" alt="Foto">
                                        </div>
                                        <div class="col-md-9">
                                            <h4 class="fw-bold text-dark text-uppercase mb-1">{{ detalles.desembolso.cliente }}</h4>
                                            <p class="text-muted mb-2"><i class="fas fa-id-card me-2"></i>CI: {{ detalles.desembolso.ci_cliente }} {{ detalles.desembolso.lugar_expedicion }}</p>
                                            
                                            <div class="row g-2 small">
                                                <div class="col-sm-6">
                                                    <div class="p-2 bg-light rounded border">
                                                        <span class="text-muted d-block" style="font-size: 0.7rem;">ACTIVIDAD ECONÓMICA</span>
                                                        <span class="fw-semibold text-dark">{{ detalles.desembolso.actividad || 'No registrada' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="p-2 bg-light rounded border">
                                                        <span class="text-muted d-block" style="font-size: 0.7rem;">TELÉFONO PRINCIPAL</span>
                                                        <span class="fw-semibold text-dark">
                                                            {{ detalles.telefonos.length > 0 ? detalles.telefonos[0].numero : 'No registrado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-success border-opacity-50 shadow-sm">
                                <div class="card-header bg-secondary bg-opacity-10 border-bottom-0 py-3">
                                    <h6 class="fw-bold text-uppercase text-dark mb-0"><i class="fas fa-handshake me-2"></i>Condiciones del Préstamo #{{ detalles.desembolso.id_plan_pago }}</h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-borderless table-striped mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="px-4 py-3" width="50%">
                                                    <span class="text-muted d-block small fw-bold">CAPITAL DESEMBOLSADO</span>
                                                    <span class="fs-4 fw-bold text-dark">{{ formatNumero(detalles.desembolso.importe_prestamo) }} Bs.</span>
                                                </td>
                                                <td class="px-4 py-3 border-start">
                                                    <span class="text-muted d-block small fw-bold">GARANTÍA</span>
                                                    <span class="fs-6 fw-semibold text-dark text-uppercase">{{ detalles.desembolso.garantia }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="text-muted d-block small fw-bold">PLAZO Y FORMA DE PAGO</span>
                                                    <span class="fs-6 fw-semibold text-dark">{{ detalles.desembolso.cuotas }} cuotas ({{ detalles.desembolso.lapso_capital }})</span>
                                                </td>
                                                <td class="px-4 py-3 border-start">
                                                    <span class="text-muted d-block small fw-bold">FECHAS CLAVE</span>
                                                    <span class="d-block small"> Aprobado: {{ formatearFecha(detalles.desembolso.fecha_credito) }}</span>
                                                    <span class="d-block small mt-1"> Desembolsado: {{ formatearFecha(detalles.desembolso.fecha_desembolso) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </template>
                        <div v-else class="alert alert-warning text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                            No se encontraron detalles para este desembolso.
                        </div>
                    </div>

                    <div class="card-footer bg-white text-end py-3 border-top">
                        <button class="btn btn-light border px-4 me-2" @click="cerrarDetalles">
                            <i class="fas fa-arrow-left me-1"></i> Volver al Listado
                        </button>
                        <button class="btn btn-primary px-4 fw-bold" @click="generarComprobante(detalles.desembolso)">
                            <i class="fas fa-print me-2"></i> Imprimir Comprobante
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

export default {
    props: {
        rolUsuario: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            view: 0,
            preloader: false,
            filtros: {
                fecha_inicio: moment().subtract(7, 'days').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'cliente.nombre',
                buscar: ''
            },
            detalles: {
                desembolso: {},
                telefonos: [],
                direcciones: [],
                empresa: {},
                fecha_reporte: '',
                monto_primera_cuota: '',
                fecha_primera_cuota: ''
            },
            listaDesembolsos: [],
            totalDesembolsos: 0,
            totalPagosAdm: 0,
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
        isActived() {
            return this.pagination.current_page;
        },
        pagesNumber() {
            if (!this.pagination.to) {
                return [];
            }
            var from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        esHoy(fechaDesembolso) {
            if (!fechaDesembolso) return false;
            const fechaItem = moment(fechaDesembolso, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD');
            const fechaActual = moment().format('YYYY-MM-DD');
            return fechaItem === fechaActual;
        },
        formatNumero(numero) {
            if (numero === undefined || numero === null) return '0.00';
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numero);
        },
        cerrarDetalles(){
            this.view=0;
        },
        formatearFecha(fecha) {
            return moment(fecha).format('DD/MM/YYYY');
        },
        buscarDesembolsos() {
            this.getDesembolsos(1);
        },
        getDesembolsos(page) {
            axios.get('/historial_desembolsos_pagos_listado', {
                params: {
                    page: page,
                    fecha_inicio: this.filtros.fecha_inicio,
                    fecha_final: this.filtros.fecha_final,
                    criterio: this.filtros.criterio,
                    buscar: this.filtros.buscar
                }
            })
                .then((response) => {
                    this.listaDesembolsos = response.data.desembolsos.data;
                    this.totalDesembolsos = response.data.totalDesembolsos;
                    this.totalPagosAdm = response.data.totalPagosAdm;
                    this.pagination = response.data.desembolsos;
                })
                .catch((error) => {
                    console.error('Error al obtener los desembolsos:', error);
                });
        },
        verDetalles(item) {
            this.preloader = true;
            axios.get('/get_comprobante_cliente_data', {
                params: {
                    id_cliente: item.id_cliente,
                    id_plan_pago: item.id_plan_pago
                }
            })
                .then((response) => {
                    this.detalles = response.data;
                    this.detalles.desembolso.id_cliente = response.data.desembolso.codcli;
                    this.view = 1;
                })
                .catch((error) => {
                    console.error('Error al obtener detalles:', error);
                    Swal.fire('Error', error.response?.data?.error || 'No se pudieron cargar los detalles.', 'error');
                })
                .finally(() => {
                    this.preloader = false;
                });
        },
        anularDesembolso(item) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/anular_desembolso', { id: item.id })
                        .then((response) => {
                            Swal.fire(
                                'Anulado!',
                                'El desembolso ha sido anulado.',
                                'success'
                            );
                            this.getDesembolsos(this.pagination.current_page);
                        })
                        .catch((error) => {
                            console.error('Error al anular el desembolso:', error);
                        });
                }
            });
        },
        generarComprobante(item) {
            const url = `/generar_comprobante_cliente?id_cliente=${item.id_cliente}&id_plan_pago=${item.id_plan_pago}`;
            window.open(url, '_blank');
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.getDesembolsos(page);
        }
    },
    mounted() {
        this.getDesembolsos(1);
    }
}
</script>

<style scoped>
.table-container-custom {
    min-height: 250px;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    background-color: white;
}

.table-compact-custom {
    font-size: 0.82rem;
    margin-bottom: 0;
}

.table-compact-custom th {
    background-color: #198754 !important; /* Solid premium success green */
    color: white !important;
    border-bottom: 2px solid #157347 !important;
    padding: 6px 8px;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    font-weight: 700;
}

.table-compact-custom td {
    padding: 5px 8px !important;
    vertical-align: middle;
}

.font-monospace {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.8rem;
}

.badge-custom {
    width: 90px;
    font-size: 0.7rem;
    padding: 4px 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-block;
}

.dropdown-toggle::after {
  display: none !important;
}

.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
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
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.info-compact-foto {
    width: 100%;
    max-height: 300px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-img {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    object-position: center;
    border-radius: 0.375rem;
}
</style>
