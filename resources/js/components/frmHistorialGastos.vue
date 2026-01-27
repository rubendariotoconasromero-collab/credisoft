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
                            Historial de Ingresos y Gastos
                        </h5>
                    </div>
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-3 mt-2 d-flex justify-content-center" id="seccionesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="ingresos-caja-tab" data-bs-toggle="tab"
                                data-bs-target="#ingresos-caja" type="button" role="tab" aria-controls="ingresos-caja"
                                aria-selected="true">
                                <!-- <i class="fas fa-cash-register me-1"></i> -->
                                Ingresos Caja Actual
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="egresos-caja-tab" data-bs-toggle="tab"
                                data-bs-target="#egresos-caja" type="button" role="tab" aria-controls="egresos-caja"
                                aria-selected="false">
                                <!-- <i class="fas fa-cash-register me-1"></i> -->
                                Egresos Caja Actual
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historial-ingresos-tab" data-bs-toggle="tab"
                                data-bs-target="#historial-ingresos" type="button" role="tab"
                                aria-controls="historial-ingresos" aria-selected="false">
                                <!-- <i class="fas fa-history me-1"></i> -->
                                Historial de Ingresos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historial-gastos-tab" data-bs-toggle="tab"
                                data-bs-target="#historial-gastos" type="button" role="tab"
                                aria-controls="historial-gastos" aria-selected="false">
                                <!-- <i class="fas fa-history me-1"></i> -->
                                Historial de Gastos
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="seccionesTabContent">
                        <!-- Ingresos Caja Actual -->
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
                                <div class="table-responsive" style="border:none">
                                    <table class="table align-middle mb-0 w-50">
                                        <tbody>
                                            <tr>
                                                <th class="text-uppercase text-dark fw-semibold ms-0 ps-0">
                                                    Total Ingresos Caja Actual
                                                    <br><small class="text-secondary fw-normal">Monto total de Ingresos Caja Actual</small>
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
                                            <tr class="table-success">
                                                <th class="text-uppercase fw-bold" style="width: 18%;">Asesor/a</th>
                                                <th class="text-uppercase fw-bold" style="width: 18%;">Personal</th>
                                                <th class="text-uppercase fw-bold" style="width: 10%;">Fecha</th>
                                                <th class="text-uppercase fw-bold" style="width: 10%;">Monto Ing.</th>
                                                <th class="text-uppercase fw-bold" style="width: 26%;">Descripción</th>
                                                <th class="text-uppercase fw-bold text-center" style="width: 8%;">Estado</th>
                                                <th class="text-uppercase fw-bold text-center" style="width: 10%;">Op.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaIngresosCaja" :key="item.id">
                                                <td>{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td>{{ parseFloat(item.monto_ingreso).toFixed(2) }}</td>
                                                <td>{{ item.descripcion }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.estado == 1" class="text-white text-uppercase badge bg-success badge-fixed-width" style="width:110px;">Cancelado</span>
                                                    <span v-else class="text-white text-uppercase badge bg-danger badge-fixed-width" style="width:110px;">Anulado</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm"
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
                                <template v-if="listaIngresosCaja.length<7">
                                    <br><br><br><br><br><br><br>
                                </template>

                                <!-- Paginación -->
                                <div class="d-flex justify-content-end">
                                    <nav>
                                        <ul class="pagination">
                                            <li class="page-item" v-if="paginationCaja.current_page > 1">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPaginaCaja(paginationCaja.current_page - 1)">Ant</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberCaja" :key="page"
                                                :class="[page == isActivedCaja ? 'active' : '']">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" v-if="paginationCaja.current_page < paginationCaja.last_page">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPaginaCaja(paginationCaja.current_page + 1)">Sig</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <!-- Egresos Caja Actual -->
                        <div class="tab-pane fade" id="egresos-caja" role="tabpanel"
                            aria-labelledby="egresos-caja-tab">
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input @input="buscarEgresosCaja" type="date" v-model="filtros_egresos_caja.fecha_inicio"
                                                class="form-control">
                                            <input @input="buscarEgresosCaja" type="date" v-model="filtros_egresos_caja.fecha_final"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <select v-model="filtros_egresos_caja.criterio" class="form-select"
                                                @change="buscarEgresosCaja">
                                                <option value="users.name">Nombre del Asesor</option>
                                                <option value="users.personal">Personal del Asesor</option>
                                            </select>
                                            <input type="text" v-model="filtros_egresos_caja.buscar" class="form-control"
                                                @input="buscarEgresosCaja">
                                            <button class="btn btn-success" @click="buscarEgresosCaja">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totales en Tabla -->
                                <div class="table-responsive" style="border:none">
                                    <table class="table align-middle mb-0 w-50">
                                        <tbody>
                                            <tr>
                                                <th class="text-uppercase text-dark fw-semibold ms-0 ps-0">
                                                    Total Egresos Caja Actual
                                                    <br><small class="text-secondary fw-normal">Monto total de Egresos Caja Actual</small>
                                                </th>
                                                <td class="text-end ms-0 ps-0">
                                                    <span class="fs-5 fw-bold text-dark">{{ totalEgresosCaja }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tabla de Egresos -->
                                <div class="table-responsive mt-2" style="font-size:12px">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="text-white">
                                            <tr class="bg-success">
                                                <th class="text-uppercase fw-bold">Asesor/a</th>
                                                <th class="text-uppercase fw-bold">Personal</th>
                                                <th class="text-uppercase fw-bold"> nmolFecha</th>
                                                <th class="text-uppercase fw-bold">Monto Gasto</th>
                                                <th class="text-uppercase fw-bold">Descripción</th>
                                                <th class="text-uppercase fw-bold">Estado</th>
                                                <th class="text-uppercase fw-bold">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaEgresosCaja" :key="item.id">
                                                <td>{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td>{{ parseFloat(item.monto_gasto).toFixed(2) }}</td>
                                                <td>{{ item.descripcion }}</td>
                                                <td>
                                                    <span v-if="item.estado == 1" class="text-success">Cancelado</span>
                                                    <span v-else class="text-danger">Anulado</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fs-4"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li @click="anularEgresoCaja(item)">
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
                                            <li class="page-item" v-if="paginationEgresosCaja.current_page > 1">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPaginaEgresosCaja(paginationEgresosCaja.current_page - 1)">Ant</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberEgresosCaja" :key="page"
                                                :class="[page == isActivedEgresosCaja ? 'active' : '']">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresosCaja(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" v-if="paginationEgresosCaja.current_page < paginationEgresosCaja.last_page">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPaginaEgresosCaja(paginationEgresosCaja.current_page + 1)">Sig</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Ingresos -->
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
                                <div class="table-responsive" style="border:none">
                                    <table class="table align-middle mb-0 w-50">
                                        <tbody>
                                            <tr>
                                                <th class="text-uppercase text-dark fw-semibold ms-0 ps-0">
                                                    Total Ingresos
                                                    <br><small class="text-secondary fw-normal">Monto total de Ingresos</small>
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
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm"
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
                                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
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

                        <!-- Historial de Gastos -->
                        <div class="tab-pane fade" id="historial-gastos" role="tabpanel"
                            aria-labelledby="historial-gastos-tab">
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input @input="buscarGastos" type="date" v-model="filtros_gastos.fecha_inicio"
                                                class="form-control">
                                            <input @input="buscarGastos" type="date" v-model="filtros_gastos.fecha_final"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <select v-model="filtros_gastos.criterio" class="form-select"
                                                @change="buscarGastos">
                                                <option value="users.name">Nombre del Asesor</option>
                                                <option value="users.personal">Personal del Asesor</option>
                                            </select>
                                            <input type="text" v-model="filtros_gastos.buscar" class="form-control"
                                                @input="buscarGastos">
                                            <button class="btn btn-success" @click="buscarGastos">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totales en Tabla -->
                                <div class="table-responsive" style="border:none">
                                    <table class="table align-middle mb-0 w-50">
                                        <tbody>
                                            <tr>
                                                <th class="text-uppercase text-dark fw-semibold ms-0 ps-0">
                                                    Total Gastos Bs.
                                                    <br><small class="text-secondary fw-normal">Monto total de Gastos</small>
                                                </th>
                                                <td class="text-end ms-0 ps-0">
                                                    <span class="fs-5 fw-bold text-dark">{{ totalGastos }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tabla de Gastos -->
                                <div class="table-responsive mt-2" style="font-size:12px">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="text-white">
                                            <tr class="bg-success">
                                                <th class="text-uppercase fw-bold">Asesor/a</th>
                                                <th class="text-uppercase fw-bold">Personal</th>
                                                <th class="text-uppercase fw-bold">Fecha</th>
                                                <th class="text-uppercase fw-bold">Monto Gasto</th>
                                                <th class="text-uppercase fw-bold">Descripción</th>
                                                <th class="text-uppercase fw-bold">Estado</th>
                                                <th class="text-uppercase fw-bold">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaGastos" :key="item.id">
                                                <td>{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td>{{ parseFloat(item.monto_gasto).toFixed(2) }}</td>
                                                <td>{{ item.descripcion }}</td>
                                                <td>
                                                    <span v-if="item.estado == 1" class="text-success">Cancelado</span>
                                                    <span v-else class="text-danger">Anulado</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fs-4"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li @click="anularGasto(item)">
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
                                            <li class="page-item" v-if="paginationGastos.current_page > 1">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPaginaGastos(paginationGastos.current_page - 1)">Ant</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberGastos" :key="page"
                                                :class="[page == isActivedGastos ? 'active' : '']">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaGastos(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" v-if="paginationGastos.current_page < paginationGastos.last_page">
                                                <a class="page-link" href="#"
                                                    @click.prevent="cambiarPaginaGastos(paginationGastos.current_page + 1)">Sig</a>
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
            view: 0,
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
            filtros_egresos_caja: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'users.name',
                buscar: ''
            },
            filtros_gastos: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'users.name',
                buscar: ''
            },
            listaIngresos: [],
            listaIngresosCaja: [],
            listaEgresosCaja: [],
            listaGastos: [],
            totalIngresos: 0,
            totalIngresosCaja: 0,
            totalEgresosCaja: 0,
            totalGastos: 0,
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
            paginationEgresosCaja: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0
            },
            paginationGastos: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 2,
            offsetCaja: 2,
            offsetEgresosCaja: 2,
            offsetGastos: 2
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
            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
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
            let from = this.paginationCaja.current_page - this.offsetCaja;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offsetCaja * 2);
            if (to >= this.paginationCaja.last_page) {
                to = this.paginationCaja.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        isActivedEgresosCaja() {
            return this.paginationEgresosCaja.current_page;
        },
        pagesNumberEgresosCaja() {
            if (!this.paginationEgresosCaja.to) {
                return [];
            }
            let from = this.paginationEgresosCaja.current_page - this.offsetEgresosCaja;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offsetEgresosCaja * 2);
            if (to >= this.paginationEgresosCaja.last_page) {
                to = this.paginationEgresosCaja.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        isActivedGastos() {
            return this.paginationGastos.current_page;
        },
        pagesNumberGastos() {
            if (!this.paginationGastos.to) {
                return [];
            }
            let from = this.paginationGastos.current_page - this.offsetGastos;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offsetGastos * 2);
            if (to >= this.paginationGastos.last_page) {
                to = this.paginationGastos.last_page;
            }
            let pagesArray = [];
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
        buscarIngresosCaja() {
            this.getIngresosCaja(1);
        },
        buscarEgresosCaja() {
            this.getEgresosCaja(1);
        },
        buscarGastos() {
            this.getGastos(1);
        },
        async getIngresosCaja(page) {
            this.preloader = true;
            await axios.get('/historial_ingresos_listado_caja', {
                params: {
                    page: page,
                    fecha_inicio: this.filtros_caja.fecha_inicio,
                    fecha_final: this.filtros_caja.fecha_final,
                    criterio: this.filtros_caja.criterio,
                    buscar: this.filtros_caja.buscar
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
        async getEgresosCaja(page) {
            this.preloader = true;
            await axios.get('/historial_egresos_listado_caja', {
                params: {
                    page: page,
                    fecha_inicio: this.filtros_egresos_caja.fecha_inicio,
                    fecha_final: this.filtros_egresos_caja.fecha_final,
                    criterio: this.filtros_egresos_caja.criterio,
                    buscar: this.filtros_egresos_caja.buscar
                }
            })
                .then((response) => {
                    this.listaEgresosCaja = response.data.egresos.data;
                    this.totalEgresosCaja = response.data.totalEgresos;
                    this.paginationEgresosCaja = response.data.egresos;
                })
                .catch((error) => {
                    console.error('Error al obtener los egresos:', error);
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
        async getGastos(page) {
            this.preloader = true;
            await axios.get('/historial_gastos_listado', {
                params: {
                    page: page,
                    fecha_inicio: this.filtros_gastos.fecha_inicio,
                    fecha_final: this.filtros_gastos.fecha_final,
                    criterio: this.filtros_gastos.criterio,
                    buscar: this.filtros_gastos.buscar
                }
            })
                .then((response) => {
                    this.listaGastos = response.data.gastos.data;
                    this.totalGastos = response.data.totalGastos;
                    this.paginationGastos = response.data.gastos;
                })
                .catch((error) => {
                    console.error('Error al obtener los gastos:', error);
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
                                this.getIngresosCaja(this.paginationCaja.current_page);
                            } else {
                                Swal.fire(
                                    'No se ha podido anular!',
                                    'La caja a la que se hizo el ingreso ya cerró.',
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
        anularEgresoCaja(item) {
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
                    axios.post('/anular_egreso_caja', { id: item.id })
                        .then((response) => {
                            if (response.data.respuesta == 1) {
                                Swal.fire(
                                    'Anulado!',
                                    'El egreso ha sido anulado.',
                                    'success'
                                );
                                this.getEgresosCaja(this.paginationEgresosCaja.current_page);
                                this.getGastos(this.paginationGastos.current_page);
                            } else {
                                Swal.fire(
                                    'No se ha podido anular!',
                                    'La caja a la que se hizo el egreso ya cerró.',
                                    'warning'
                                );
                            }
                        })
                        .catch((error) => {
                            console.error('Error al anular el egreso:', error);
                        });
                }
            });
        },
        anularGasto(item) {
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
                    axios.post('/anular_gasto', { id: item.id })
                        .then((response) => {
                            if (response.data.respuesta == 1) {
                                Swal.fire(
                                    'Anulado!',
                                    'El gasto ha sido anulado.',
                                    'success'
                                );
                                this.getGastos(this.paginationGastos.current_page);
                                this.getEgresosCaja(this.paginationEgresosCaja.current_page);
                            } else {
                                Swal.fire(
                                    'No se ha podido anular!',
                                    'La caja a la que se hizo el gasto ya cerró.',
                                    'warning'
                                );
                            }
                        })
                        .catch((error) => {
                            console.error('Error al anular el gasto:', error);
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
        },
        cambiarPaginaEgresosCaja(page) {
            this.paginationEgresosCaja.current_page = page;
            this.getEgresosCaja(page);
        },
        cambiarPaginaGastos(page) {
            this.paginationGastos.current_page = page;
            this.getGastos(page);
        }
    },
    mounted() {
        this.getIngresosCaja(1);
        this.getEgresosCaja(1);
        this.getIngresos(1);
        this.getGastos(1);
    }
}
</script>

<style scoped>
.dropdown-toggle::after {
  display: none !important;
}

.nav-item .nav-link{
    background-color: #ffffff;
    border: 2px solid #4bbf73;
    color:#000000 !important;
    font-weight: 500;
    padding-top:10px;
    padding-bottom:10px;
}

.nav-item .nav-link:hover{
    background-color: #4bbf73 !important;
    border: 2px solid #4bbf73;
    color:#ffffff !important;
    font-weight: 500;
    padding-top:10px;
    padding-bottom:10px;
    transition: none;
}

.nav-item .nav-link.active{
    background-color: #4bbf73 !important;
    border: 2px solid #4bbf73;
    color:#ffffff !important;
    font-weight: 500;
    padding-top:10px;
    padding-bottom:10px;
    transition: none;
}

.nav-item .nav-link i{
    color:#000000 !important;
    font-weight: 400;
}

.nav-item .nav-link i:hover{
    color:#ffffff !important;
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

p {
    color: white;
    margin-top: 10px;
}
</style>