<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">


                <div v-if="view==0" class="card">
                    <div class="card-header bg-success py-2">
                        <h5 class="header-title my-0 text-center fw-semibold text-white text-uppercase">
                            Historial de Ingresos
                        </h5>
                    </div>
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-3" id="seccionesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="ingresos-caja-tab" data-bs-toggle="tab"
                                data-bs-target="#ingresos-caja" type="button" role="tab" aria-controls="ingresos-caja"
                                aria-selected="true">
                                <i class="fas fa-cash-register me-1"></i>
                                Ingresos Caja Actual
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historial-ingresos-tab" data-bs-toggle="tab"
                                data-bs-target="#historial-ingresos" type="button" role="tab"
                                aria-controls="historial-ingresos" aria-selected="false">
                                <i class="fas fa-history me-1"></i>
                                Historial de Ingresos
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="seccionesTabContent">
                        <!-- Ingresos Caja Actual -->
                        <!-- seccion caja -->
                        <div class="tab-pane fade show active" id="ingresos-caja" role="tabpanel"
                            aria-labelledby="ingresos-caja-tab">


                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input @input="buscarIngresosCaja" type="date" v-model="filtros_caja.fecha_inicio"
                                                class="form-control">
                                            <input @input="buscarIngresosCaja" type="date" v-model="filtros_caja.fecha_final"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <select v-model="filtros_caja.criterio" class="form-select"
                                                @change="buscarIngresosCaja">
                                                <option value="users.name">Nombre del Asesor</option>
                                                <option value="users.personal">Personal del Asesor</option>
                                            </select>
                                            <input type="text" v-model="filtros_caja.buscar" class="form-control"
                                                @input="buscarIngresosCaja">
                                            <button class="btn btn-success" @click="buscarIngresosCaja">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>



                                <!-- Totales en Tabla -->
                                <div class="table-responsive " style="border:none">
                                    <table class="table align-middle mb-0 w-50">
                                        <tbody>

                                            <tr>
                                                <th class="text-uppercase text-dark fw-semibold ms-0 ps-0">
                                                    Total Ingresos Caja Actual
                                                    <br><small class="text-secondary fw-normal">Monto total de
                                                        Ingresos Caja Actual</small>
                                                </th>
                                                <td class="text-end ms-0 ps-0">
                                                    <span class="fs-5 fw-bold text-dark">{{ totalIngresosCaja }}</span>
                                                </td>

                                            </tr>

                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tabla de Ingresos -->
                                <div class="table-responsive mt-2" style="font-size:12px">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="text-white">
                                            <tr class="bg-success">
                                                <!-- <th>Cajero/a</th> -->
                                                <th class="text-uppercase fw-bold">Asesor/a</th>
                                                <th class="text-uppercase fw-bold">Personal</th>
                                                <th class="text-uppercase fw-bold">Fecha</th>
                                                <th class="text-uppercase fw-bold">Monto Ingreso</th>
                                                <th class="text-uppercase fw-bold">Descripción</th>
                                                <th class="text-uppercase fw-bold">Estado</th>
                                                <th class="text-uppercase fw-bold">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaIngresosCaja" :key="item.id">
                                                <!-- <td>{{ item.cajero }}</td> -->
                                                <td>{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td>{{ parseFloat(item.monto_ingreso).toFixed(2) }}</td>
                                                <td>{{ item.descripcion }}</td>
                                                <td>
                                                    <span v-if="item.estado == 1" class="text-success">Cancelado</span>
                                                    <span v-else class="text-danger">Anulado</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;"
                                                            class="text-success dropdown-toggle btn-sm"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fs-4"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li @click="anularIngreso(item)">
                                                                <a class="dropdown-item text-danger" href="#">
                                                                    <i class="fas fa-times"></i> Anular
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Paginación -->
                                <div class="d-flex justify-content-end">
                                    <nav>
                                        <ul class="pagination">
                                            <li class="page-item" v-if="paginationCaja.current_page > 1">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPagina(paginationCaja.current_page - 1)">Ant</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberCaja" :key="page"
                                                :class="[page == isActivedCaja ? 'active' : '']">
                                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{
                                                    page
                                                    }}</a>
                                            </li>
                                            <li class="page-item" v-if="paginationCaja.current_page < paginationCaja.last_page">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPagina(paginationCaja.current_page + 1)">Sig</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>

                            </div>
                        </div>

                        <!-- Historial de Ingresos -->
                        <!-- seccion historial -->
                        <div class="tab-pane fade" id="historial-ingresos" role="tabpanel"
                            aria-labelledby="historial-ingresos-tab">
                          
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input @input="buscarIngresos" type="date" v-model="filtros.fecha_inicio"
                                                class="form-control">
                                            <input @input="buscarIngresos" type="date" v-model="filtros.fecha_final"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <select v-model="filtros.criterio" class="form-select"
                                                @change="buscarIngresos">
                                                <option value="users.name">Nombre del Asesor</option>
                                                <option value="users.personal">Personal del Asesor</option>
                                            </select>
                                            <input type="text" v-model="filtros.buscar" class="form-control"
                                                @input="buscarIngresos">
                                            <button class="btn btn-success" @click="buscarIngresos">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>



                                <!-- Totales en Tabla -->
                                <div class="table-responsive " style="border:none">
                                    <table class="table align-middle mb-0 w-50">
                                        <tbody>

                                            <tr>
                                                <th class="text-uppercase text-dark fw-semibold ms-0 ps-0">
                                                    Total Ingresos
                                                    <br><small class="text-secondary fw-normal">Monto total de
                                                        Ingresos</small>
                                                </th>
                                                <td class="text-end ms-0 ps-0">
                                                    <span class="fs-5 fw-bold text-dark">{{ totalIngresos }}</span>
                                                </td>

                                            </tr>

                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tabla de Ingresos -->
                                <div class="table-responsive mt-2" style="font-size:12px">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="text-white">
                                            <tr class="bg-success">
                                                <!-- <th>Cajero/a</th> -->
                                                <th class="text-uppercase fw-bold">Asesor/a</th>
                                                <th class="text-uppercase fw-bold">Personal</th>
                                                <th class="text-uppercase fw-bold">Fecha</th>
                                                <th class="text-uppercase fw-bold">Monto Ingreso</th>
                                                <th class="text-uppercase fw-bold">Descripción</th>
                                                <th class="text-uppercase fw-bold">Estado</th>
                                                <th class="text-uppercase fw-bold">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaIngresos" :key="item.id">
                                                <!-- <td>{{ item.cajero }}</td> -->
                                                <td>{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td>{{ parseFloat(item.monto_ingreso).toFixed(2) }}</td>
                                                <td>{{ item.descripcion }}</td>
                                                <td>
                                                    <span v-if="item.estado == 1" class="text-success">Cancelado</span>
                                                    <span v-else class="text-danger">Anulado</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;"
                                                            class="text-success dropdown-toggle btn-sm"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fs-4"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li @click="anularIngreso(item)">
                                                                <a class="dropdown-item text-danger" href="#">
                                                                    <i class="fas fa-times"></i> Anular
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Paginación -->
                                <div class="d-flex justify-content-end">
                                    <nav>
                                        <ul class="pagination">
                                            <li class="page-item" v-if="pagination.current_page > 1">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumber" :key="page"
                                                :class="[page == isActived ? 'active' : '']">
                                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{
                                                    page
                                                    }}</a>
                                            </li>
                                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>

                            </div>
                        </div>
                    </div>



                </div>

            </div>
            <!-- Container-fluid -->
        </div>
    </main>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2'

export default {
    data() {
        return {
            view:0,
            preloader: false,
            filtros: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'users.name',
                buscar: ''
            },

            filtros_caja: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'users.name',
                buscar: ''
            },


            listaIngresos: [],
            listaIngresosCaja: [],
            totalIngresos: 0,
            totalIngresosCaja: 0,
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0
            },
            paginationCaja: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 2,
            offsetCaja: 2,
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
        },

        isActivedCaja() {
            return this.paginationCaja.current_page;
        },
        pagesNumberCaja() {
            if (!this.paginationCaja.to) {
                return [];
            }
            var from = this.paginationCaja.current_page - this.offsetCaja;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetCaja * 2);
            if (to >= this.paginationCaja.last_page) {
                to = this.paginationCaja.last_page;
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
        formatearFecha(fecha) {
            return moment(fecha).format('DD/MM/YYYY');
        },
        buscarIngresos() {
            this.getIngresos(1);
        },
        buscarIngresosCaja(){
            this.getIngresosCaja(1);
        },
        async getIngresosCaja(page) {
            this.preloader = true;
            await axios.get('/historial_ingresos_listado_caja', {
                params: {
                    page: page,
                    fecha_inicio: this.filtrosCaja.fecha_inicio,
                    fecha_final: this.filtrosCaja.fecha_final,
                    criterio: this.filtrosCaja.criterio,
                    buscar: this.filtrosCaja.buscar
                }
            })
                .then((response) => {
                    this.listaIngresosCaja = response.data.ingresos.data;
                    this.totalIngresosCaja = response.data.totalIngresos;
                    this.paginationCaja = response.data.ingresos;
                })
                .catch((error) => {
                    console.error('Error al obtener los ingresos:', error);
                })
                .finally(() => {
                    this.preloader = false;
                });
        },
        async getIngresos(page) {
            this.preloader = true;
            await axios.get('/historial_ingresos_listado', {
                params: {
                    page: page,
                    fecha_inicio: this.filtros.fecha_inicio,
                    fecha_final: this.filtros.fecha_final,
                    criterio: this.filtros.criterio,
                    buscar: this.filtros.buscar
                }
            })
                .then((response) => {
                    this.listaIngresos = response.data.ingresos.data;
                    this.totalIngresos = response.data.totalIngresos;
                    this.pagination = response.data.ingresos;
                })
                .catch((error) => {
                    console.error('Error al obtener los ingresos:', error);
                })
                .finally(() => {
                    this.preloader = false;
                });
        },
        anularIngreso(item) {
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
                    axios.post('/anular_ingreso', { id: item.id })
                        .then((response) => {
                            if (response.data.respuesta == 1) {
                                Swal.fire(
                                    'Anulado!',
                                    'El ingreso ha sido anulado.',
                                    'success'
                                );
                                this.getIngresos(this.pagination.current_page);
                            } else {
                                Swal.fire(
                                    'No se ha podido anular!',
                                    'La caja a la que se hizo el ingreso ya cerro.',
                                    'warning'
                                );
                            }
                        })
                        .catch((error) => {
                            console.error('Error al anular el ingreso:', error);
                        });
                }
            });
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.getIngresos(page);
        },

        cambiarPaginaCaja(page) {
            this.paginationCaja.current_page = page;
            this.getIngresosCaja(page);
        }
    },
    mounted() {
        this.getIngresos(1);
    }
}
</script>

<style scoped>
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

p {
    color: white;
    margin-top: 10px;
}
</style>
