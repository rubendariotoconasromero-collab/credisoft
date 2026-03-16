<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">
                <div v-if="view==0" class="card border-0">
                    
                    <div class="card-header bg-success py-3 d-flex justify-content-center align-items-center">
                        <h5 class="header-title my-0 fw-bold text-white text-uppercase">
                            <i class="fas fa-balance-scale me-2"></i> Historial de Ingresos y Gastos
                        </h5>
                    </div>

                    <ul class="nav nav-pills mb-4 mt-3 d-flex justify-content-center px-2 custom-tabs" id="seccionesTab" role="tablist">
                        <li class="nav-item mx-1" role="presentation">
                            <button class="nav-link active px-4 py-2 fw-bold text-uppercase" id="ingresos-caja-tab" data-bs-toggle="pill"
                                data-bs-target="#ingresos-caja" type="button" role="tab" aria-controls="ingresos-caja" aria-selected="true">
                                <i class="fas fa-arrow-down me-1"></i> Ingresos (Caja Actual)
                            </button>
                        </li>
                        <li class="nav-item mx-1" role="presentation">
                            <button class="nav-link px-4 py-2 fw-bold text-uppercase" id="egresos-caja-tab" data-bs-toggle="pill"
                                data-bs-target="#egresos-caja" type="button" role="tab" aria-controls="egresos-caja" aria-selected="false">
                                <i class="fas fa-arrow-up me-1"></i> Egresos (Caja Actual)
                            </button>
                        </li>
                        <li class="nav-item mx-1" role="presentation">
                            <button class="nav-link px-4 py-2 fw-bold text-uppercase" id="historial-ingresos-tab" data-bs-toggle="pill"
                                data-bs-target="#historial-ingresos" type="button" role="tab" aria-controls="historial-ingresos" aria-selected="false">
                                <i class="fas fa-history me-1"></i> Hist. General Ingresos
                            </button>
                        </li>
                        <li class="nav-item mx-1" role="presentation">
                            <button class="nav-link px-4 py-2 fw-bold text-uppercase" id="historial-gastos-tab" data-bs-toggle="pill"
                                data-bs-target="#historial-gastos" type="button" role="tab" aria-controls="historial-gastos" aria-selected="false">
                                <i class="fas fa-history me-1"></i> Hist. General Gastos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content bg-white p-3 rounded-bottom" id="seccionesTabContent">
                        
                        <div class="tab-pane fade show active fade-in-animation" id="ingresos-caja" role="tabpanel" aria-labelledby="ingresos-caja-tab">
                            
                            <div class="row mb-3">
                                <div class="col-lg-8 mb-2 mb-lg-0">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 11px;">DESDE</span>
                                                <input @change="buscarIngresosCaja" type="date" v-model="filtros_caja.fecha_inicio" class="form-control border-start-0">
                                                <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 11px;">HASTA</span>
                                                <input @change="buscarIngresosCaja" type="date" v-model="filtros_caja.fecha_final" class="form-control border-start-0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <select v-model="filtros_caja.criterio" class="form-select bg-white" style="max-width: 140px;" @change="buscarIngresosCaja">
                                                    <option value="users.name">Asesor</option>
                                                    <option value="users.personal">Personal</option>
                                                </select>
                                                <input type="text" v-model="filtros_caja.buscar" class="form-control border-start-0" placeholder="Buscar..." @keyup.enter="buscarIngresosCaja">
                                                <button class="btn btn-success px-3" @click="buscarIngresosCaja"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border-primary border-opacity-25 shadow-sm bg-primary bg-opacity-10 h-100 mb-0">
                                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-uppercase text-primary fw-bold d-block" style="font-size: 0.7rem;">Total Ingresos Caja Actual</span>
                                                <h4 class="mb-0 fw-bold text-dark">{{ formatNumero(totalIngresosCaja) }} <small class="fs-6 text-muted">Bs.</small></h4>
                                            </div>
                                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fas fa-plus fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="table-responsive" style="font-size:12px">
                                    <table class="table table-hover table-striped mb-0 align-middle table-sm">
                                        <thead class="table-success text-white text-uppercase" style="font-size: 11px;">
                                            <tr>
                                                <th class="py-3 ps-3">Asesor/a</th>
                                                <th class="py-3">Personal</th>
                                                <th class="py-3">Fecha</th>
                                                <th class="py-3 text-end text-white bg-success border-success">Monto (Bs)</th>
                                                <th class="py-3 ps-3">Descripción</th>
                                                <th class="py-3 text-center">Estado</th>
                                                <th class="py-3 text-center">Op.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaIngresosCaja" :key="item.id">
                                                <td class="ps-3 fw-bold">{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td class="text-end fw-bold text-success bg-success bg-opacity-10 fs-6">{{ formatNumero(item.monto_ingreso) }}</td>
                                                <td class="ps-3 text-muted">{{ item.descripcion }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.estado == 1" class="badge bg-success">CANCELADO</span>
                                                    <span v-else class="badge bg-danger">ANULADO</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h fs-5"></i>
                                                        </a>
                                                        <ul class="dropdown-menu shadow">
                                                            <li @click="anularIngreso(item)">
                                                                <a class="dropdown-item text-danger fw-bold" href="#">
                                                                    <i class="fas fa-times me-2"></i> Anular Ingreso
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="listaIngresosCaja.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i> No hay ingresos registrados.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end" v-if="paginationCaja.last_page > 1">
                                <nav><ul class="pagination shadow-sm mb-0">
                                    <li class="page-item" :class="{disabled: paginationCaja.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(paginationCaja.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberCaja" :key="page" :class="[page == isActivedCaja ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: paginationCaja.current_page >= paginationCaja.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(paginationCaja.current_page + 1)">Sig</a>
                                    </li>
                                </ul></nav>
                            </div>
                        </div>

                        <div class="tab-pane fade fade-in-animation" id="egresos-caja" role="tabpanel" aria-labelledby="egresos-caja-tab">
                            
                            <div class="row mb-3">
                                <div class="col-lg-8 mb-2 mb-lg-0">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 11px;">DESDE</span>
                                                <input @change="buscarEgresosCaja" type="date" v-model="filtros_egresos_caja.fecha_inicio" class="form-control border-start-0">
                                                <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 11px;">HASTA</span>
                                                <input @change="buscarEgresosCaja" type="date" v-model="filtros_egresos_caja.fecha_final" class="form-control border-start-0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <select v-model="filtros_egresos_caja.criterio" class="form-select bg-white" style="max-width: 140px;" @change="buscarEgresosCaja">
                                                    <option value="users.name">Asesor</option>
                                                    <option value="users.personal">Personal</option>
                                                </select>
                                                <input type="text" v-model="filtros_egresos_caja.buscar" class="form-control border-start-0" placeholder="Buscar..." @keyup.enter="buscarEgresosCaja">
                                                <button class="btn btn-success px-3" @click="buscarEgresosCaja"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border-danger border-opacity-25 shadow-sm bg-danger bg-opacity-10 h-100 mb-0">
                                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-uppercase text-danger fw-bold d-block" style="font-size: 0.7rem;">Total Egresos Caja Actual</span>
                                                <h4 class="mb-0 fw-bold text-dark">{{ formatNumero(totalEgresosCaja) }} <small class="fs-6 text-muted">Bs.</small></h4>
                                            </div>
                                            <div class="bg-danger text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fas fa-minus fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="table-responsive" style="font-size:12px">
                                    <table class="table table-sm table-hover table-striped mb-0 align-middle">
                                        <thead class="table-danger text-white text-uppercase" style="font-size: 11px;">
                                            <tr>
                                                <th class="py-3 ps-3">Asesor/a</th>
                                                <th class="py-3">Personal</th>
                                                <th class="py-3">Fecha</th>
                                                <th class="py-3 text-end text-white bg-danger border-danger">Monto (Bs)</th>
                                                <th class="py-3 ps-3">Descripción</th>
                                                <th class="py-3 text-center">Estado</th>
                                                <th class="py-3 text-center">Op.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaEgresosCaja" :key="item.id">
                                                <td class="ps-3 fw-bold">{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td class="text-end fw-bold text-danger bg-danger bg-opacity-10 fs-6">{{ formatNumero(item.monto_gasto) }}</td>
                                                <td class="ps-3 text-muted">{{ item.descripcion }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.estado == 1" class="badge bg-success">CANCELADO</span>
                                                    <span v-else class="badge bg-danger">ANULADO</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h fs-5"></i>
                                                        </a>
                                                        <ul class="dropdown-menu shadow">
                                                            <li @click="anularEgresoCaja(item)">
                                                                <a class="dropdown-item text-danger fw-bold" href="#">
                                                                    <i class="fas fa-times me-2"></i> Anular Egreso
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="listaEgresosCaja.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i> No hay egresos registrados.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end" v-if="paginationEgresosCaja.last_page > 1">
                                <nav><ul class="pagination shadow-sm mb-0">
                                    <li class="page-item" :class="{disabled: paginationEgresosCaja.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresosCaja(paginationEgresosCaja.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberEgresosCaja" :key="page" :class="[page == isActivedEgresosCaja ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresosCaja(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: paginationEgresosCaja.current_page >= paginationEgresosCaja.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresosCaja(paginationEgresosCaja.current_page + 1)">Sig</a>
                                    </li>
                                </ul></nav>
                            </div>
                        </div>

                        <div class="tab-pane fade fade-in-animation" id="historial-ingresos" role="tabpanel" aria-labelledby="historial-ingresos-tab">
                            
                            <div class="row mb-3">
                                <div class="col-lg-8 mb-2 mb-lg-0">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 11px;">DESDE</span>
                                                <input @change="buscarIngresos" type="date" v-model="filtros.fecha_inicio" class="form-control border-start-0">
                                                <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 11px;">HASTA</span>
                                                <input @change="buscarIngresos" type="date" v-model="filtros.fecha_final" class="form-control border-start-0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <select v-model="filtros.criterio" class="form-select bg-white" style="max-width: 140px;" @change="buscarIngresos">
                                                    <option value="users.name">Asesor</option>
                                                    <option value="users.personal">Personal</option>
                                                </select>
                                                <input type="text" v-model="filtros.buscar" class="form-control border-start-0" placeholder="Buscar..." @keyup.enter="buscarIngresos">
                                                <button class="btn btn-success px-3" @click="buscarIngresos"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border-primary border-opacity-25 shadow-sm bg-primary bg-opacity-10 h-100 mb-0">
                                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-uppercase text-primary fw-bold d-block" style="font-size: 0.7rem;">Total Histórico Ingresos</span>
                                                <h4 class="mb-0 fw-bold text-dark">{{ formatNumero(totalIngresos) }} <small class="fs-6 text-muted">Bs.</small></h4>
                                            </div>
                                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fas fa-chart-line fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="table-responsive" style="font-size:12px">
                                    <table class="table table-sm table-hover table-striped mb-0 align-middle">
                                        <thead class="table-success text-white text-uppercase" style="font-size: 11px;">
                                            <tr>
                                                <th class="py-3 ps-3">Asesor/a</th>
                                                <th class="py-3">Personal</th>
                                                <th class="py-3">Fecha</th>
                                                <th class="py-3 text-end text-white bg-success border-success">Monto Ing. (Bs)</th>
                                                <th class="py-3 ps-3">Descripción</th>
                                                <th class="py-3 text-center">Estado</th>
                                                <th class="py-3 text-center">Op.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaIngresos" :key="item.id">
                                                <td class="ps-3 fw-bold">{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td class="text-end fw-bold text-success bg-success bg-opacity-10 fs-6">{{ formatNumero(item.monto_ingreso) }}</td>
                                                <td class="ps-3 text-muted">{{ item.descripcion }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.estado == 1" class="badge bg-success">CANCELADO</span>
                                                    <span v-else class="badge bg-danger">ANULADO</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h fs-5"></i>
                                                        </a>
                                                        <ul class="dropdown-menu shadow">
                                                            <li @click="anularIngreso(item)">
                                                                <a class="dropdown-item text-danger fw-bold" href="#">
                                                                    <i class="fas fa-times me-2"></i> Anular Ingreso
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="listaIngresos.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i> No hay ingresos registrados.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end" v-if="pagination.last_page > 1">
                                <nav><ul class="pagination shadow-sm mb-0">
                                    <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: pagination.current_page >= pagination.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                    </li>
                                </ul></nav>
                            </div>
                        </div>

                        <div class="tab-pane fade fade-in-animation" id="historial-gastos" role="tabpanel" aria-labelledby="historial-gastos-tab">
                            
                            <div class="row mb-3">
                                <div class="col-lg-8 mb-2 mb-lg-0">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 11px;">DESDE</span>
                                                <input @change="buscarGastos" type="date" v-model="filtros_gastos.fecha_inicio" class="form-control border-start-0">
                                                <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 11px;">HASTA</span>
                                                <input @change="buscarGastos" type="date" v-model="filtros_gastos.fecha_final" class="form-control border-start-0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group shadow-sm">
                                                <select v-model="filtros_gastos.criterio" class="form-select bg-white" style="max-width: 140px;" @change="buscarGastos">
                                                    <option value="users.name">Asesor</option>
                                                    <option value="users.personal">Personal</option>
                                                </select>
                                                <input type="text" v-model="filtros_gastos.buscar" class="form-control border-start-0" placeholder="Buscar..." @keyup.enter="buscarGastos">
                                                <button class="btn btn-success px-3" @click="buscarGastos"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border-danger border-opacity-25 shadow-sm bg-danger bg-opacity-10 h-100 mb-0">
                                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-uppercase text-danger fw-bold d-block" style="font-size: 0.7rem;">Total Histórico Gastos</span>
                                                <h4 class="mb-0 fw-bold text-dark">{{ formatNumero(totalGastos) }} <small class="fs-6 text-muted">Bs.</small></h4>
                                            </div>
                                            <div class="bg-danger text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fas fa-chart-pie fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="table-responsive" style="font-size:12px">
                                    <table class="table table-sm table-hover table-striped mb-0 align-middle">
                                        <thead class="table-danger text-white text-uppercase" style="font-size: 11px;">
                                            <tr>
                                                <th class="py-3 ps-3">Asesor/a</th>
                                                <th class="py-3">Personal</th>
                                                <th class="py-3">Fecha</th>
                                                <th class="py-3 text-end text-white bg-danger border-danger">Monto (Bs)</th>
                                                <th class="py-3 ps-3">Descripción</th>
                                                <th class="py-3 text-center">Estado</th>
                                                <th class="py-3 text-center">Op.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in listaGastos" :key="item.id">
                                                <td class="ps-3 fw-bold">{{ item.asesor }}</td>
                                                <td>{{ item.personal }}</td>
                                                <td>{{ formatearFecha(item.fecha) }}</td>
                                                <td class="text-end fw-bold text-danger bg-danger bg-opacity-10 fs-6">{{ formatNumero(item.monto_gasto) }}</td>
                                                <td class="ps-3 text-muted">{{ item.descripcion }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.estado == 1" class="badge bg-success">CANCELADO</span>
                                                    <span v-else class="badge bg-danger">ANULADO</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h fs-5"></i>
                                                        </a>
                                                        <ul class="dropdown-menu shadow">
                                                            <li @click="anularGasto(item)">
                                                                <a class="dropdown-item text-danger fw-bold" href="#">
                                                                    <i class="fas fa-times me-2"></i> Anular Gasto
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="listaGastos.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i> No hay gastos registrados.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end" v-if="paginationGastos.last_page > 1">
                                <nav><ul class="pagination shadow-sm mb-0">
                                    <li class="page-item" :class="{disabled: paginationGastos.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaGastos(paginationGastos.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberGastos" :key="page" :class="[page == isActivedGastos ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaGastos(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: paginationGastos.current_page >= paginationGastos.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaGastos(paginationGastos.current_page + 1)">Sig</a>
                                    </li>
                                </ul></nav>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
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
        formatNumero(numero) {
            if (numero === undefined || numero === null) return '0.00';
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numero);
        },
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
/* Tabs Personalizados y Modernos */
.custom-tabs {
    border-bottom: 2px solid #e9ecef;
}
.custom-tabs .nav-link {
    color: #6c757d !important;
    background-color: transparent !important;
    border: none !important;
    border-bottom: 3px solid transparent !important;
    border-radius: 0 !important;
    transition: all 0.3s ease !important;
    font-size: 0.85rem !important;
}

.custom-tabs .nav-link i {
    color: #6c757d !important;
}
.custom-tabs .nav-link:hover {
    color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.05) !important;
    border-bottom-color: rgba(25, 135, 84, 0.3) !important;
}
.custom-tabs .nav-link.active {
    color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-bottom-color: #198754 !important;
}

.custom-tabs .nav-link.active i{
    color: #198754 !important;
}


/* Animación Suave entre Pestañas */
.fade-in-animation {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Menú de los 3 puntos */
.dropdown-toggle::after {
  display: none !important;
}

/* Preloader */
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(4px);
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.table th { vertical-align: middle; }
.table td { vertical-align: middle; }
</style>