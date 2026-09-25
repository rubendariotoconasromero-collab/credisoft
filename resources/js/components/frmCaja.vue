<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>
        <div class="page-content">
            <div class="container-fluid">
                
                <div v-if="view == 0" class="row">
                    <div class="col-12">
                        <ListaCajas 
                            :cajas="lista_caja"
                            :pagination="pagination"
                            :conteo-pendientes="lista_pagos_adm.length"
                            @filtrar="handleFiltrosCaja"
                            @cambiar-pagina="cambiarPagina"
                            @ver-desembolsos="verPagosAdministrativos"
                            @gestionar-pagos="gestionarPago"
                            @abrir-ingreso="abrirModalIngreso"
                            @abrir-gasto="abrirModalGasto"
                            @abrir-apertura="abrirModalAperturaCaja"
                            @ver-detalle="abrirModalDetallesCaja"
                        />
                    </div>
                </div>

                <div v-if="view == 1" class="row">
                    <div class="col-12">
                        <GestionDesembolsos 
                            :lista-pendientes="lista_pagos_adm"
                            @cerrar="cerrarModalPagosAdministrativos"
                            @desembolsar="registrarPagoAdm"
                        />
                    </div>
                </div>

                <div v-if="view == 2" class="row">
                    <div class="col-12">
                        <GestionCobros 
                            @cerrar="cerrarGestionPagos"
                        />
                    </div>
                </div>
                
            </div>
        </div>

        <div id="modalVerRegistros" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="myLargeModalLabel">Listado de pagos</h5>
                        <button @click="cerrarModalVerRegistros()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- <div class="col-md-12"> -->
                        <div class="row mb-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input @input="buscarPagoCaja()" v-model="fecha_inicio_pago" type="date" name=""
                                        id="" class="form-control form-control-sm">
                                    <button class="btn btn-success btn-sm me-1">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <input @input="buscarPagoCaja()" v-model="fecha_final_pago" type="date" name=""
                                        id="" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6 text-start">
                                <h5>
                                    <span style="border-radius:0" class="badge bg-success">Total Bs.: </span>
                                    <span style="border-radius:0" class="badge bg-outline-success text-success">{{
                                        totalPagos }}
                                    </span>
                                </h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <button @click="exportarPagosCaja()" class="btn btn-warning btn-sm text-white">
                                    <i class="fas fa-file-pdf"></i>
                                    Exportar a PDF
                                </button>
                            </div>
                        </div>
                        <!-- </div> -->
                        <div class="table-responsive" style="font-size:11px;">
                            <table class="table mb-4 table-sm table-striped table-hover">
                                <thead class="bg-success text-white text-uppercase">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Cliente</th>
                                        <th>Asesor</th>
                                        <th>Total credito</th>
                                        <th>Fecha pago</th>
                                        <th>Monto pago</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="" v-for="(item, index) in lista_pagos" :key="index">

                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.id }}</td>
                                        <td>{{ item.cliente }}</td>
                                        <td>{{ item.asesor }}</td>

                                        <td>{{ item.total_pago_credito }}</td>

                                        <td>{{ item.fecha_pago }}</td>
                                        <td>{{ item.monto_pago }}</td>
                                        <td>
                                            <span v-if="item.estado == 1" class="badge text-bg-success">Cancelado</span>
                                            <span v-else-if="item.estado == 0" class="badge text-bg-dark">Anulado</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table><br><br><br>
                        </div>
                        <div class="card-footer py-4">
                            <nav>
                                <ul class="pagination justify-content-end mb-0">
                                    <li class="page-item" v-if="paginationPagos.current_page > 1">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaPagos(paginationPagos.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberPagos" :key="page"
                                        :class="[page == isActivedPagos ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPaginaPagos(page)"
                                            :v-text="page">{{ page }}</a>
                                    </li>
                                    <li class="page-item"
                                        v-if="paginationPagos.current_page < paginationPagos.last_page">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaPagos(paginationPagos.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <div id="modalVerRegistrosIngresosCorrientes" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="myLargeModalLabel">Listado de ingresos corrientes</h5>
                        <button @click="cerrarModalVerRegistrosIngresosCorrientes()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- <div class="col-md-12"> -->

                        <div class="row mb-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input @input="buscarIngresosCorrientesCaja()"
                                        v-model="fecha_inicio_ingresos_corrientes" type="date" name="" id=""
                                        class="form-control form-control-sm">
                                    <button class="btn btn-success btn-sm me-1">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <input @input="buscarIngresosCorrientesCaja()"
                                        v-model="fecha_final_ingresos_corrientes" type="date" name="" id=""
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6 text-start">
                                <h5>
                                    <span style="border-radius:0" class="badge bg-success">Total Bs.: </span>
                                    <span style="border-radius:0" class="badge bg-outline-secondary text-success">{{
                                        totalIngresosCorrientes }} </span>
                                </h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <button @click="exportarIngresosCorrientesCaja()"
                                    class="btn btn-warning btn-sm text-white">
                                    <i class="fas fa-file-pdf"></i>
                                    Exportar a PDF
                                </button>
                            </div>
                        </div>
                        <!-- </div> -->
                        <div class="table-responsive" style="font-size:11px;">
                            <table class="table mb-4 table-sm table-striped table-hover">
                                <thead class="bg-success text-white text-uppercase">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Asesor</th>
                                        <th>Fecha pago</th>
                                        <th>Monto </th>
                                        <th>Descripcion</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="" v-for="(item, index) in lista_ingresos_corrientes" :key="index">

                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.id }}</td>
                                        <td>{{ item.asesor }}</td>
                                        <td>{{ item.fecha }}</td>
                                        <td>{{ parseFloat(item.monto_ingreso).toFixed(2) }}</td>
                                        <td>{{ item.descripcion }}</td>
                                        <td>
                                            <span v-if="item.estado == 1" class="badge text-bg-success">Cancelado</span>
                                            <span v-else-if="item.estado == 0" class="badge text-bg-dark">Anulado</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table><br><br><br>
                        </div>
                        <div class="card-footer py-4">
                            <nav>
                                <ul class="pagination justify-content-end mb-0">
                                    <li class="page-item" v-if="paginationIngresosCorrientes.current_page > 1">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaIngresosCorrientes(paginationIngresosCorrientes.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberIngresosCorrientes" :key="page"
                                        :class="[page == isActivedIngresosCorrientes ? 'active' : '']">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaIngresosCorrientes(page)" :v-text="page">{{
                                            page }}</a>
                                    </li>
                                    <li class="page-item"
                                        v-if="paginationIngresosCorrientes.current_page < paginationIngresosCorrientes.last_page">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaIngresosCorrientes(paginationIngresosCorrientes.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalVerRegistrosGastosCorrientes" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="myLargeModalLabel">Listado de gastos corrientes</h5>
                        <button @click="cerrarModalVerRegistrosGastosCorrientes()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input @input="buscarGastosCorrientesCaja()"
                                        v-model="fecha_inicio_gastos_corrientes" type="date" name="" id=""
                                        class="form-control form-control-sm">
                                    <button class="btn btn-success btn-sm me-1">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <input @input="buscarGastosCorrientesCaja()" v-model="fecha_final_gastos_corrientes"
                                        type="date" name="" id="" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6 text-start">
                                <h5>
                                    <span style="border-radius:0" class="badge bg-success">Total Bs.: </span>
                                    <span style="border-radius:0" class="badge bg-outline-secondary text-success">{{
                                        totalGastosCorrientes }} </span>
                                </h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <button @click="exportarGastosCorrientesCaja()"
                                    class="btn btn-warning btn-sm text-white">
                                    <i class="fas fa-file-pdf"></i>
                                    Exportar a PDF
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive" style="font-size:11px;">
                            <table class="table mb-4 table-sm table-striped table-hover">
                                <thead class="bg-success text-white text-uppercase">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Asesor</th>
                                        <th>Fecha pago</th>
                                        <th>Monto </th>
                                        <th>Descripcion</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="" v-for="(item, index) in lista_gastos_corrientes" :key="index">

                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.id }}</td>
                                        <td>{{ item.asesor }}</td>
                                        <td>{{ item.fecha }}</td>
                                        <td>{{ parseFloat(item.monto_gasto).toFixed(2) }}</td>
                                        <td>{{ item.descripcion }}</td>
                                        <td>
                                            <span v-if="item.estado == 1" class="badge text-bg-success">Cancelado</span>
                                            <span v-else-if="item.estado == 0" class="badge text-bg-dark">Anulado</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table><br><br><br>
                        </div>
                        <div class="card-footer py-4">
                            <nav>
                                <ul class="pagination justify-content-end mb-0">
                                    <li class="page-item" v-if="paginationGastosCorrientes.current_page > 1">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaGastosCorrientes(paginationGastosCorrientes.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberGastosCorrientes" :key="page"
                                        :class="[page == isActivedGastosCorrientes ? 'active' : '']">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaGastosCorrientes(page)" :v-text="page">{{ page
                                            }}</a>
                                    </li>
                                    <li class="page-item"
                                        v-if="paginationGastosCorrientes.current_page < paginationGastosCorrientes.last_page">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaGastosCorrientes(paginationGastosCorrientes.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalCobrarPagoAdm" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered disbursement-dialog">
                <div class="modal-content border-0 shadow-lg disbursement-modal">
                    
                    <div class="modal-header disbursement-header">
                        <h5 class="modal-title fw-bold text-uppercase">
                            <i class="fas fa-hand-holding-usd me-2" aria-hidden="true"></i>
                            Desembolso de Crédito
                        </h5>
                        <button type="button" class="btn-close" :disabled="desembolsando" aria-label="Cerrar" @click="cerrarModalCobrarPagoAdm()"></button>
                    </div>

                    <div class="modal-body disbursement-body">
                        
                        <div class="disbursement-client mb-3">
                            <div class="disbursement-section-title">
                                <i class="fas fa-user-tag" aria-hidden="true"></i> Datos de la operación
                            </div>
                            <div class="disbursement-client-grid">
                                <div class="disbursement-data disbursement-data-client">
                                    <span>Cliente</span>
                                    <strong class="text-uppercase">{{ pago_administrativo.cliente }}</strong>
                                </div>
                                <div class="disbursement-data">
                                    <span>Asesor</span>
                                    <strong class="text-uppercase">{{ pago_administrativo.asesor }}</strong>
                                </div>
                                <div class="disbursement-data">
                                    <span>Plan de pago</span>
                                    <strong>#{{ pago_administrativo.id_plan_pago }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="disbursement-amount-card amount-delivery">
                                    <span>Monto a entregar</span>
                                    <strong>{{ parseFloat(pago_administrativo.monto_solicitud).toFixed(2) }} <small>Bs</small></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="disbursement-amount-card amount-administrative">
                                    <span>Cobro administrativo</span>
                                    <strong>{{ parseFloat(pago_administrativo.monto).toFixed(2) }} <small>Bs</small></strong>
                                </div>
                            </div>
                        </div>

                        <div class="disbursement-confirmation mb-3">
                            <i class="fas fa-info-circle" aria-hidden="true"></i>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="checkCobroAdm" v-model="confirmacionCobroAdm">
                                <label class="form-check-label" for="checkCobroAdm">
                                    <strong>Confirmo el cobro de {{ parseFloat(pago_administrativo.monto).toFixed(2) }} Bs.</strong>
                                    <small>El cobro administrativo y el desembolso se registrarán juntos.</small>
                                </label>
                            </div>
                        </div>

                        <div class="disbursement-actions">
                            <button type="button" class="btn btn-outline-secondary" :disabled="desembolsando" @click="cerrarModalCobrarPagoAdm()">Cancelar</button>
                            <button :disabled="desembolsando || !confirmacionCobroAdm" @click="guardarPagoAdm()"
                                class="btn btn-success fw-bold">
                                
                                <span v-if="desembolsando" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                <i v-else class="fas fa-check-circle me-2"></i>

                                <span v-if="!desembolsando">Procesar desembolso y cobro</span>
                                <span v-else>Procesando registro...</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div id="modalVerDesembolsosRealizados" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="myLargeModalLabel">Listado de desembolsos y pagos adm.
                        </h5>
                        <button @click="cerrarModalVerDesembolsosRealizados()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- <div class="col-md-12"> -->
                        <div class="row mb-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input @input="buscarDesembolso()" v-model="fecha_inicio_desembolso"
                                        type="datetime-local" name="" id="" class="form-control form-control-sm">
                                    <button class="btn btn-success btn-sm me-1">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <input @input="buscarDesembolso()" v-model="fecha_final_desembolso"
                                        type="datetime-local" name="" id="" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6 text-start">
                                <h5>
                                    <span style="border-radius:0" class="badge bg-success">Total Desembolsos Bs.:
                                    </span>
                                    <span style="border-radius:0" class="badge bg-outline-secondary text-success">{{
                                        totalDesembolsos }} </span>
                                </h5>
                                <h5>
                                    <span style="border-radius:0" class="badge bg-success">Total Pagos adm. Bs.: </span>
                                    <span style="border-radius:0" class="badge bg-outline-secondary text-success">{{
                                        totalPagosAdm }} </span>
                                </h5>
                            </div>

                            <div class="col-md-6 text-end">
                                <button @click="exportarDesembolsosCaja()" class="btn btn-warning btn-sm text-white">
                                    <i class="fas fa-file-pdf"></i>
                                    Exportar a PDF
                                </button>
                            </div>
                        </div>
                        <!-- </div> -->
                        <div class="table-responsive" style="font-size:11px;">
                            <table class="table mb-4 table-sm table-striped table-hover">
                                <thead class="bg-success text-white text-uppercase">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Plan pago</th>
                                        <th>Cliente</th>
                                        <th>Asesor</th>
                                        <th>Fecha pago</th>
                                        <th>Monto Des.</th>
                                        <th>Pag. Adm.</th>
                                        <!-- <th>Descripcion</th> -->
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="" v-for="(item, index) in lista_desembolsos_realizados" :key="index">

                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.id }}</td>
                                        <td>{{ item.id_plan_pago }}</td>
                                        <td>{{ item.cliente }}</td>
                                        <td>{{ item.asesor }}</td>
                                        <td>{{ item.fecha }}</td>
                                        <td>{{ parseFloat(item.monto).toFixed(2) }}</td>
                                        <td>{{ parseFloat(item.monto_pago_adm).toFixed(2) }}</td>
                                        <!-- <td>{{ item.descripcion }}</td>                -->
                                        <td>
                                            <span v-if="item.estado == 1" class=" text-danger">Anulado</span>
                                            <span v-else-if="item.estado == 0" class=" text-success">Cancelado</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table><br><br><br>
                        </div>
                        <div class="card-footer py-4">
                            <nav>
                                <ul class="pagination justify-content-end mb-0">
                                    <li class="page-item" v-if="paginationDesembolsosRealizados.current_page > 1">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaDesembolsosRealizados(paginationDesembolsosRealizados.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumberDesembolsosRealizados" :key="page"
                                        :class="[page == isActivedDesembolsosRealidados ? 'active' : '']">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaDesembolsosRealizados(page)" :v-text="page">{{
                                            page }}</a>
                                    </li>
                                    <li class="page-item"
                                        v-if="paginationDesembolsosRealizados.current_page < paginationDesembolsosRealizados.last_page">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPaginaDesembolsosRealizados(paginationDesembolsosRealizados.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <!-- Modal -->
        <div class="modal fade" id="reporteModal" tabindex="-1" aria-labelledby="reporteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border border-3 border-info">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="reporteModalLabel">Generar Reportes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button @click="generarPlanPago()" class="btn btn-primary w-100 mb-3">Generar Plan de
                            Pago</button>
                        <button @click="generarComprobante()" class="btn btn-info w-100">Generar Comprobante de
                            Desembolso</button>
                    </div>
                </div>
            </div>
        </div>



        <ModalAperturaCaja 
            ref="modalAperturaRef" 
            @aperturada="getCajas(1)" 
        />

        <ModalMovimientoCaja 
            ref="modalIngresoRef" 
            tipo="ingreso" 
            @guardado="getCajas(1)" 
        />

        <ModalMovimientoCaja 
            ref="modalGastoRef" 
            tipo="gasto" 
            @guardado="getCajas(1)" 
        />

        <ModalDetalleCaja 
            ref="modalDetalleRef" 
            @cerrada="getCajas(1)" 
        />

    </main>

    <!-- End Page-content -->
</template>

<script>
import ListaCajas from './Caja/ListaCajas.vue'; // Ajusta la ruta si es necesario
import GestionDesembolsos from './Caja/GestionDesembolsos.vue'; // Ajusta la ruta si es necesario
import GestionCobros from './Caja/GestionCobros.vue'; // Ajusta la ruta si es necesario
import ModalAperturaCaja from './Caja/ModalAperturaCaja.vue';
import ModalMovimientoCaja from './Caja/ModalMovimientoCaja.vue';
import ModalDetalleCaja from './Caja/ModalDetalleCaja.vue';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2'

export default {
    components:{
        ListaCajas,
        GestionDesembolsos,
        GestionCobros,
        ModalAperturaCaja,
        ModalMovimientoCaja,
        ModalDetalleCaja
    },

    data() {
        return {
            confirmacionCobroAdm: false,
            mostrarDropdown: false,
            mostrarDropdownEgreso: false,
            formas_pago: [
                {
                    nombre: 'efectivo'
                },
                {
                    nombre: 'transferencia - QR'
                },
                {
                    nombre: 'Depósito banco'
                }
            ],
            id_plan_pago: null,
            filteredItemsMotivoIngreso: [],
            filteredItemsMotivoEgreso:[],
            buscar_motivo_ingreso:'',
            buscar_motivo_egreso:'',
            loading: false,
            motivo_ingreso: {
                id: 0,
                nombre: '',
                accion: 0,
            },
            motivo_gasto: {
                id: 0,
                nombre: '',
                accion: 0,
            },
            estado_caja:false,
            desembolsando:false,
            view: 0,
            movimientosCaja: [],
            totalIngresosCaja: 0,
            totalSalidasCaja: 0,
            filtro_movimiento: {
                tipo: 'todos',  // Puede ser 'ingreso' o 'retiro'
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),  // Fecha en formato 'YYYY-MM-DD'
                fecha_fin: moment().format('YYYY-MM-DD'),  // Fecha en formato 'YYYY-MM-DD'
            },
            pagination_movimientos_caja: {
                current_page: 1,
                last_page: 0,
                per_page: 10,
            },
            pagesNumber: [],
            transferencia: {
                mmonto: 0,
                descripcion: '',
            },
            preloader: false,
            fecha_inicio_gastos_corrientes: moment().format('YYYY-MM-DD'),
            fecha_final_gastos_corrientes: moment().format('YYYY-MM-DD'),
            fecha_inicio_ingresos_corrientes: moment().format('YYYY-MM-DD'),
            fecha_final_ingresos_corrientes: moment().format('YYYY-MM-DD'),
            fecha_inicio_pago: moment().format('YYYY-MM-DD'),
            fecha_final_pago: moment().format('YYYY-MM-DD'),
            totalPagosAdm: 0,
            totalDesembolsos: 0,
            guardando_gasto: false,
            guardando_ingreso: false,
            lista_pagos_adm: [],
            filtroCriterio: "", // CI o Cliente
            filtroTexto: "", // Texto a buscar
            lista_pagos_adm_filtrada: [], // Lista filtrada
            fecha_inicio: moment().format('YYYY-MM-DD'),
            fecha_final: moment().format('YYYY-MM-DD'),
            criterio: 'users.name',
            buscar: '',
            totalPagos: 0,
            totalPagosAmortizaciones: 0,
            totalIngresosCorrientes: 0,
            totalGastosCorrientes: 0,
            opcion: 'cliente.nombre',
            buscar: '',
            lista_pagos: [],
            lista_planes_pago: [],
            lista_desembolsos_realizados: [],
            lista_pagos_amortizaciones: [],
            lista_ingresos_corrientes: [],
            lista_gastos_corrientes: [],
            monto_ingreso: 0,
            monto_gasto: 0,
            lista_caja: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 2,

            paginationPagos: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offsetPagos: 2,

            paginationPagosAmortizaciones: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offsetPagosAmortizaciones: 2,

            paginationIngresosCorrientes: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offsetIngresosCorrientes: 2,


            paginationGastosCorrientes: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offsetGastosCorrientes: 2,

            paginationDesembolsosRealizados: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offsetDesembolsosRealizados: 2,

            paginationMovimientosCaja: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offsetMovimientosCaja: 2,

            monto_apertura: 0,

            caja: {
                id_caja: 0,
                fechahora_apertura: moment().format('YYYY-MM-DD HH:mm:ss'),
                // fechahora_apertura:moment().format('YYYY-MM-DD HH:mm:ss'),
                monto_inicial: 0,
                monto_final: 0,
                efectivo_total: 0,
                deposito_total: 0,
                efectivo_venta: 0,
                deposito_venta: 0,
                efectivo_gasto: 0,
                deposito_gasto: 0,
                total_ingreso: 0,
                total_egreso: 0,
                diferencia: 0,
                estado: '',
                estado_cajas: false,

                ingreso_total_pagos: 0,

                ingreso_total_ingresos: 0,
                pago_administrativo_total: 0,
                desembolso_total: 0,


            },

            gasto: {
                id_gasto: 0,
                monto: 0,
                descripcion: '',
                estado: 0,
                id_usuario: 0,
                id_caja: 0,
            },

            ingreso: {
                id_ingreso: 0,
                monto: 0,
                descripcion: '',
                estado: 0,
                id_usuario: 0,
                id_caja: 0,
            },

            pago_administrativo: {
                id_pago_adm: 0,
                monto: 0,
                descripcion: 0,
                id_plan_pago: 0,
                id_usuario: 0,
                id_caja: 0,
                cliente: 0,
                asesor: 0,
                fecha: moment().format('YYYY-MM-DD HH:mm:ss'),
            },
            usuario_actual: 0,
            idCajaPropia: null,
            item_pago_desembolso: null,

            fecha_inicio_desembolso: moment().format('YYYY-MM-DD HH:mm:ss'),
            fecha_final_desembolso: moment().format('YYYY-MM-DD HH:mm:ss'),

            idPlanPago: null,
            idCliente: null,

            lista_motivos_ingresos: [],
            lista_motivos_gastos: [],

            aperturada: false,
            
        }
    },
    computed: {
        itemsParaMostrarEgreso() {
            if (this.buscar_motivo_egreso.trim() === '') {
                return this.lista_motivos_gastos;
            }
            return this.filteredItemsMotivoEgreso;
        },
        // Computed para decidir qué mostrar: filtrados o todos
        itemsParaMostrar() {
            if (this.buscar_motivo_ingreso.trim() === '') {
                return this.lista_motivos_ingresos;
            }
            return this.filteredItemsMotivoIngreso;
        },
   
        isActived: function () {
            return this.pagination.current_page;
        },
        isActivedPagos: function () {
            return this.paginationPagos.current_page;
        },
        isActivedPagosAmortizaciones: function () {
            return this.paginationPagosAmortizaciones.current_page;
        },
        isActivedDesembolsosRealidados: function () {
            return this.paginationDesembolsosRealizados.current_page;
        },

        isActivedIngresosCorrientes: function () {
            return this.paginationIngresosCorrientes.current_page;
        },

        isActivedGastosCorrientes: function () {
            return this.paginationGastosCorrientes.current_page;
        },
        isActivedMovimientosCaja: function () {
            return this.paginationMovimientosCaja.current_page;
        },
        pagesNumber: function () {
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
        pagesNumberPagos: function () {
            if (!this.paginationPagos.to) {
                return [];
            }
            var from = this.paginationPagos.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetPagos * 2);
            if (to >= this.paginationPagos.last_page) {
                to = this.paginationPagos.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        pagesNumberPagosAmortizaciones: function () {
            if (!this.paginationPagosAmortizaciones.to) {
                return [];
            }
            var from = this.paginationPagosAmortizaciones.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetPagosAmortizaciones * 2);
            if (to >= this.paginationPagosAmortizaciones.last_page) {
                to = this.paginationPagosAmortizaciones.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        pagesNumberIngresosCorrientes: function () {
            if (!this.paginationIngresosCorrientes.to) {
                return [];
            }
            var from = this.paginationIngresosCorrientes.current_page - this.offsetIngresosCorrientes;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetIngresosCorrientes * 2);
            if (to >= this.paginationIngresosCorrientes.last_page) {
                to = this.paginationIngresosCorrientes.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },

        pagesNumberDesembolsosRealizados: function () {
            if (!this.paginationDesembolsosRealizados.to) {
                return [];
            }
            var from = this.paginationDesembolsosRealizados.current_page - this.offsetDesembolsosRealizados;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetDesembolsosRealizados * 2);
            if (to >= this.paginationDesembolsosRealizados.last_page) {
                to = this.paginationDesembolsosRealizados.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        pagesNumberGastosCorrientes: function () {
            if (!this.paginationGastosCorrientes.to) {
                return [];
            }
            var from = this.paginationGastosCorrientes.current_page - this.offsetGastosCorrientes;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetGastosCorrientes * 2);
            if (to >= this.paginationGastosCorrientes.last_page) {
                to = this.paginationGastosCorrientes.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },

        pagesNumberMovimientosCaja: function () {
            if (!this.paginationMovimientosCaja.to) {
                return [];
            }
            var from = this.paginationMovimientosCaja.current_page - this.offsetMovimientosCaja;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offsetMovimientosCaja * 2);
            if (to >= this.paginationMovimientosCaja.last_page) {
                to = this.paginationMovimientosCaja.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
    },
    methods: {
        async verificarBoveda() {
            try {
                const response = await axios.get('/verificar-boveda');
                this.aperturada = response.data.aperturada;
            } catch (error) {
                if (axios.isCancel(error)) return;
                console.error("Error al verificar boveda:", error);
            }
        },

        async abrirModalAperturaCaja() {
            await this.verificarBoveda();
            if(this.aperturada){
                this.$refs.modalAperturaRef.abrir();
            }else{
                Swal.fire({
                    title: '¡Bóveda no aperturada!',
                    text: 'No se puede abrir la caja porque la bóveda no ha sido aperturada. Por favor, contacte al administrador.',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        abrirModalIngreso() {
            this.$refs.modalIngresoRef.abrir();
        },
        abrirModalGasto() {
            this.$refs.modalGastoRef.abrir();
        },
        abrirModalDetallesCaja(item) {
            this.$refs.modalDetalleRef.abrir(item);
        },

        handleFiltrosCaja(filtros) {
            this.criterio = filtros.criterio;
            this.buscar = filtros.buscar;
            this.fecha_inicio = filtros.fecha_inicio;
            this.fecha_final = filtros.fecha_final;

            if (this.criterio === 'fecha') {
                this.buscarCajaFecha();
            } else {
                this.buscarCaja();
            }
        },


        async procesarPagoOrdenBD(idOrden) {
            this.preloader = true;
            try {
                // Asume que tienes una ruta para registrar el pago
                await axios.post('/caja/cobrar-orden-reprogramacion', { id_orden: idOrden });
                
                Swal.fire('¡Cobrado!', 'El pago se registró correctamente.', 'success');
                this.cargarOrdenesReprogramacion(); // Recargar tabla
            } catch (error) {
                if (axios.isCancel(error)) return;
                Swal.fire('Error', 'No se pudo procesar el pago.', 'error');
            } finally {
                this.preloader = false;
            }
        },

        imprimirRecibo(orden) {
            // Lógica para abrir PDF
            const url = `/reportes/recibo-reprogramacion/${orden.id}`;
            window.open(url, '_blank');
        },

        cerrarModalCobrarCuotas(){
            $('#paymentModal').modal('hide');
        },

        abrirModalCobrarCuotas(){
            $('#paymentModal').modal('show');
        },

        formatFecha(fecha) {
            return moment(fecha).format('DD/MM/YYYY');
        },
        formatNumero(numero) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 0 }).format(numero);
        },
     
        cerrarGestionPagos(){
            this.view=0;
        },
        async gestionarPago(){
            await this.consultarCajaAbiertaTransaccion();
            if (!this.estado_caja) { 
                this.view = 2; 
            }
        },
    
        seleccionarMotivoEgreso(item) {
            this.gasto.descripcion = item.nombre;
            this.buscar_motivo_egreso=item.nombre
            this.mostrarDropdownEgreso = false;
            this.filteredItemsMotivoEgreso = [];
        },
        seleccionarMotivoIngreso(item) {
            this.ingreso.descripcion = item.nombre;
            this.buscar_motivo_ingreso=item.nombre
            this.mostrarDropdown = false;
            this.filteredItemsMotivoIngreso = [];
        },

        filteredItemsMotivoEgresoMetodo(keyword) {
            if (!keyword || keyword.trim() === '') {
                this.filteredItemsMotivoEgreso = [];
                return;
            }
            
            const searchTerm = keyword.toLowerCase();
            this.filteredItemsMotivoEgreso = this.lista_motivos_gastos.filter(
                (item) => item.nombre.toLowerCase().includes(searchTerm)
            );
        },
        
        toggleDropdownEgreso() {
            this.mostrarDropdownEgreso = !this.mostrarDropdownEgreso;
            
            // Si se abre el dropdown sin búsqueda, mostrar todos
            if (this.mostrarDropdownEgreso && !this.buscar_motivo_egreso) {
                this.filteredItemsMotivoEgreso = [];
            }
        },

        filteredItemsMotivoIngresoMetodo(keyword) {
            if (!keyword || keyword.trim() === '') {
                this.filteredItemsMotivoIngreso = [];
                return;
            }
            
            const searchTerm = keyword.toLowerCase();
            this.filteredItemsMotivoIngreso = this.lista_motivos_ingresos.filter(
                (item) => item.nombre.toLowerCase().includes(searchTerm)
            );
        },
        
        toggleDropdown() {
            this.mostrarDropdown = !this.mostrarDropdown;
            
            // Si se abre el dropdown sin búsqueda, mostrar todos
            if (this.mostrarDropdown && !this.buscar_motivo_ingreso) {
                this.filteredItemsMotivoIngreso = [];
            }
        },

        async guardarMotivoIngreso() {
            try {
                this.loading = true;
                const response = await axios.post('/guardar_motivo_ingreso', this.motivo_ingreso);
                Swal.fire({
                    title: 'Guardado exitosamente',
                    text: 'El motivo de ingreso ha sido registrado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                this.cerrarModalMotivoIngreso();
            } catch (error) {
                if (axios.isCancel(error)) return;
                console.error('Error: ', error.message);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al guardar el motivo de ingreso. Por favor, inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
                this.preloader = false;
            }
        },

        async guardarMotivoGasto() {
            try {
                this.loading = true;
                const response = await axios.post('/guardar_motivo_gasto', this.motivo_gasto);
                Swal.fire({
                    title: 'Guardado exitosamente',
                    text: 'El motivo de gasto ha sido registrado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                this.cerrarModalMotivoGasto();
            } catch (error) {
                if (axios.isCancel(error)) return;
                console.error('Error: ', error.message);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al guardar el motivo de gasto. Por favor, inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
                this.preloader = false;
            }
        },
        async nuevoMotivoIngreso() {
            this.buscar_motivo_ingreso='',
            this.motivo_ingreso.nombre = '';
            this.motivo_ingreso.accion = 0;
            this.abrirModalMotivoIngreso();
        },

        async cancelarGuardarMotivoIngreso() {
            this.motivo_ingreso.nombre = '';
            this.cerrarModalMotivoIngreso();
        },

        async cerrarModalMotivoIngreso() {
            $('#modalMotivoIngreso').modal('hide');
        },

        async abrirModalMotivoIngreso() {
            $('#modalMotivoIngreso').modal('show');
        },

        async nuevoMotivoGasto() {
            this.buscar_motivo_egreso='',
            this.motivo_gasto.nombre = '';
            this.motivo_gasto.accion = 0;
            this.abrirModalMotivoGasto();
        },

        async cancelarGuardarMotivoGasto() {
            this.motivo_gasto.nombre = '';
            this.cerrarModalMotivoGasto();
        },

        async cerrarModalMotivoGasto() {
            $('#modalMotivoGasto').modal('hide');
        },

        async abrirModalMotivoGasto() {
            $('#modalMotivoGasto').modal('show');
        },
        limpiarMontoSiEsCero() {
            if (this.monto_apertura == 0) {
                this.monto_apertura = '';
            }
        },
        async obtenerMovimientosCaja(page = 1) {
            try {
                let response = await axios.get('/movimientos_caja', {
                    params: {
                        page: page,
                        id_caja: this.caja.id_caja,
                        tipo: this.filtro_movimiento.tipo,
                        fecha_inicio: this.filtro_movimiento.fecha_inicio,
                        fecha_fin: this.filtro_movimiento.fecha_fin,
                        per_page: this.pagination_movimientos_caja.per_page,
                    }
                });

                this.movimientosCaja = response.data.movimientos.data;

                this.paginationMovimientosCaja = {
                    total: response.data.movimientos.total,
                    current_page: response.data.movimientos.current_page,
                    per_page: response.data.movimientos.per_page,
                    last_page: response.data.movimientos.last_page,
                    from: response.data.movimientos.from,
                    to: response.data.movimientos.to
                };


                this.totalIngresosCaja = response.data.totales.ingresos;
                this.totalSalidasCaja = response.data.totales.salidas;


            } catch (error) {
                if (axios.isCancel(error)) return;
                console.error('Error al obtener movimientos de caja:', error);
            }
        },

        cambiarPaginaMovimientosCaja(page) {
            this.paginationMovimientosCaja.current_page = page;
            this.obtenerMovimientosCaja(page);
        },
        
        openModalReportePlanComprobante() {
            const modal = new bootstrap.Modal(document.getElementById('reporteModal'));
            modal.show();
        },

        generarPlanPago() {
            const url = `/reporte_planes_pago_cuotas_cliente?id_plan_pago=${this.idPlanPago}`;
            window.open(url, '_blank');
        },
        generarComprobante() {
            const url = `/generar_comprobante_cliente?id_cliente=${this.idCliente}&id_plan_pago=${this.idPlanPago}`;
            window.open(url, '_blank');
        },

        buscarGastosCorrientesCaja() {
            this.getGastosCorrientes(1);
        },
        buscarIngresosCorrientesCaja() {
            this.getIngresosCorrientes(1);
        },
        buscarPagoCaja() {
            this.getPagos(1);
        },
        exportarDesembolsosCaja() {
            // Construye la URL con el parámetro fecha_inicio
            const url = '/exportar_desembolsos_caja_pdf?id_caja=' + this.caja.id_caja + '&fecha_inicio=' + this.fecha_inicio_desembolso
                + '&fecha_final=' + this.fecha_final_desembolso;

            // Abre una nueva pestaña o ventana con la URL
            window.open(url, '_blank');
        },
        buscarDesembolso() {
            this.getDesembolsos(1);
        },
        async getDesembolsos(page) {

            await axios.get('/get_desembolsos?fecha_inicio=' + this.fecha_inicio_desembolso + '&fecha_final=' + this.fecha_final_desembolso
                + '&id_caja=' + this.caja.id_caja + '&page=' + page
            ).then((response) => {
                console.log(response);
                this.lista_desembolsos_realizados = response.data.desembolsos.data;

                this.paginationDesembolsosRealizados = {
                    total: response.data.desembolsos.total,
                    current_page: response.data.desembolsos.current_page,
                    per_page: response.data.desembolsos.per_page,
                    last_page: response.data.desembolsos.last_page,
                    from: response.data.desembolsos.from,
                    to: response.data.desembolsos.to
                };
                //this.totalDesembolsosRealizados=isNaN(parseFloat(response.data.totalIngresos).toFixed(2))?0:parseFloat(response.data.totalIngresos).toFixed(2);
                console.log('total desembolsos: ', response.data.totalDesembolsos);
                this.totalDesembolsos = isNaN(parseFloat(response.data.totalDesembolsos).toFixed(2)) ? 0 : parseFloat(response.data.totalDesembolsos).toFixed(2);
                this.totalPagosAdm = isNaN(parseFloat(response.data.totalPagosAdm).toFixed(2)) ? 0 : parseFloat(response.data.totalPagosAdm).toFixed(2);
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
                .finally(() => {

                })
        },
        async verDesembolsosCaja() {
            this.fecha_inicio_desembolso = moment().subtract(1, 'week').format('YYYY-MM-DD HH:mm:ss');
            this.fecha_final_desembolso = moment().format('YYYY-MM-DD HH:mm:ss');
            await this.getDesembolsos(1);
            this.abrirModalVerDesembolsos();
        },
        cerrarModalVerDesembolsosRealizados() {
            $('#modalVerDesembolsosRealizados').modal('hide');
        },
        abrirModalVerDesembolsos() {
            $('#modalVerDesembolsosRealizados').modal('show');
        },
        reporteCajas() {
            // Construye la URL con el parámetro fecha_inicio
            const url = '/reporte_cajas_fecha?lista_cajas=' + JSON.stringify(this.lista_caja);

            // Abre una nueva pestaña o ventana con la URL
            window.open(url, '_blank');
        },
        
        async guardarPagoAdm() {
            if (!this.confirmacionCobroAdm) {
                Swal.fire('Atención', 'Debe confirmar el cobro del pago administrativo marcando la casilla.', 'warning');
                return;
            }

            const montoDesembolso = parseFloat(this.pago_administrativo.monto_solicitud);
            this.desembolsando = true;

            // Evita solicitar las formas de pago cuando el saldo total de la
            // caja ya es insuficiente para cubrir el desembolso.
            const saldoSuficiente = await this.validarSaldoCajaParaEgreso(montoDesembolso);
            if (!saldoSuficiente) {
                this.desembolsando = false;
                return;
            }

            try {
                await axios.post('/registrar_pago_adm_desembolso', {
                    id_plan_pago: this.pago_administrativo.id_plan_pago,
                    id_caja: this.idCajaPropia,
                    monto_pago_adm: this.pago_administrativo.monto,
                    monto_desembolso: montoDesembolso,
                });

                // Ambas operaciones OK → mostrar comprobante y éxito
                this.openModalReportePlanComprobante();
                this.confirmacionCobroAdm = false;
                this.cerrarModalCobrarPagoAdm();
                await Swal.fire({
                    title: '¡Éxito!',
                    text: 'El cobro administrativo y el desembolso se registraron correctamente.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                });

            } catch (error) {
                if (axios.isCancel(error)) return;
                this.manejarErrorSaldoCaja(error, montoDesembolso);
            } finally {
                this.desembolsando = false;
                await this.getPagosAdm();
                this.getCajas(1);
            }
        },

        registrarPagoAdm(item) {
            this.abrirModalCobrarPagoAdm();
            this.pago_administrativo.id_plan_pago = item.id;
            this.pago_administrativo.id_caja = this.idCajaPropia;
            this.pago_administrativo.cliente = item.cliente;
            this.pago_administrativo.asesor = item.asesor;
            this.pago_administrativo.monto =  Number(item.monto_pago_adm).toFixed(2);
            this.pago_administrativo.monto_solicitud = item.tipo_solicitud=='Refinanciamiento'? item.monto_refinanciamiento: item.total_pagar_plan;
            this.item_pago_desembolso = item;

            this.idCliente = item.id_cliente;
            this.idPlanPago = item.id;

        },
        abrirModalCobrarPagoAdm() {
            this.pago_administrativo.monto = 0;
            this.pago_administrativo.descripcion = '';
            $('#modalCobrarPagoAdm').modal('show');
        },
        cerrarModalCobrarPagoAdm() {
            this.confirmacionCobroAdm = false;
            $('#modalCobrarPagoAdm').modal('hide');
        },

        async getPagosAdm() {
            await axios.get('/get_planes_pago_sin_pago_adm').then((response) => {
                this.lista_pagos_adm = response.data;
                this.lista_pagos_adm_filtrada = [...this.lista_pagos_adm];
                console.log(response.data);
            }).catch((error) => {
                    if (axios.isCancel(error)) return;
                console.log(error.message);
            })
        },
        async verPagosAdministrativos() {
            await this.consultarCajaAbiertaTransaccion();
            if (!this.estado_caja) { 
                this.view = 1; 
            }
        },

        abrirModalPagosAdministrativos() {
            this.view = 1;
        },
        cerrarModalPagosAdministrativos() {
            this.view = 0;
        },
        async actualizarDesembolso(item) {
            const monto = Number(item.importe_solicitud);

            // La verificación previa evita abrir la confirmación y el formulario
            // de pago cuando el saldo disponible ya resulta insuficiente.
            const saldoSuficiente = await this.validarSaldoCajaParaEgreso(monto);
            if (!saldoSuficiente) return;

            const result = await Swal.fire({
                title: '¿Confirmar desembolso?',
                html: `Se desembolsarán <strong>${this.formatMoney(monto)} Bs</strong> al cliente.<br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desembolsar',
                cancelButtonText: 'Cancelar',
            });

            if (!result.isConfirmed) return;

            try {
                await axios.post('/actualizar_desembolso', {
                    id_plan_pago: item.id,
                    id_caja:      this.idCajaPropia,
                    monto,
                });
                this.getPlanesPago();
                this.cerrarModalDesembolsos();
                Swal.fire({ title: 'Desembolso realizado', icon: 'success', timer: 1500, showConfirmButton: false });
                this.getCajas(1);
            } catch (error) {
                if (axios.isCancel(error)) return;
                this.manejarErrorSaldoCaja(error, monto);
            }
        },

        async guardarDesembolso(item) {
            const monto = Number(item.importe_solicitud);

            // Validar saldo (para llamadas directas, ej. desde el panel de desembolsos)
            // La validación de saldo se realiza en servidor sobre el componente de efectivo.

            try {
                await axios.post('/actualizar_desembolso', {
                    id_plan_pago: item.id,
                    id_caja:      this.idCajaPropia,
                    monto,
                });
                this.getCajas(1);
                return true;
            } catch (error) {
                if (axios.isCancel(error)) return;
                this.manejarErrorSaldoCaja(error, monto);
                return false;
            }
        },

        /**
         * Consulta el saldo actual de caja y muestra alerta si es insuficiente.
         * Retorna true si hay saldo suficiente, false si no.
         */
        async validarSaldoCajaParaEgreso(montoRequerido) {
            try {
                const { data } = await axios.get('/caja/saldo-actual');
                this.idCajaPropia = data.id_caja;

                if (!data.caja_abierta) {
                    Swal.fire('Sin caja abierta', 'No hay una caja abierta para realizar este egreso.', 'warning');
                    return false;
                }

                if (data.saldo < montoRequerido) {
                    const faltante = (montoRequerido - data.saldo).toFixed(2);
                    Swal.fire({
                        title: 'Saldo insuficiente en Caja',
                        html: `
                            <div class="text-start" style="font-size:0.9rem;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Saldo disponible en caja:</span>
                                    <strong class="text-success">${parseFloat(data.saldo).toFixed(2)} Bs</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Monto requerido:</span>
                                    <strong class="text-danger">${parseFloat(montoRequerido).toFixed(2)} Bs</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2 border-top pt-1">
                                    <span>Faltante:</span>
                                    <strong class="text-danger">${faltante} Bs</strong>
                                </div>
                                <div class="alert alert-warning py-1 px-2 mb-0" style="font-size:0.8rem;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Para continuar, solicite un <strong>traspaso de bóveda a caja</strong> por al menos <strong>${faltante} Bs</strong>.
                                </div>
                            </div>`,
                        icon: 'warning',
                        confirmButtonColor: '#ffc107',
                        confirmButtonText: 'Entendido',
                    });
                    return false;
                }

                return true;
            } catch (e) {
                console.warn('No se pudo verificar el saldo de caja:', e);
                if (!axios.isCancel(e) && ![401, 419].includes(e.response?.status)) {
                    await Swal.fire(
                        'No se pudo verificar el saldo',
                        'Intente nuevamente antes de registrar las formas de pago.',
                        'error'
                    );
                }
                return false;
            }
        },

        /**
         * Muestra el error de saldo insuficiente que devuelve el backend (422).
         */
        manejarErrorSaldoCaja(error, montoRequerido) {
            if (axios.isCancel(error)) return;
            if (error.response?.status === 422 && error.response?.data?.codigo === 'SALDO_CAJA_INSUFICIENTE') {
                const data = error.response.data;
                const faltante = data.faltante ?? (montoRequerido - (data.saldo_disponible ?? 0)).toFixed(2);
                Swal.fire({
                    title: 'Saldo insuficiente en Caja',
                    html: `
                        <div class="text-start" style="font-size:0.9rem;">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Saldo disponible en caja:</span>
                                <strong class="text-success">${parseFloat(data.saldo_disponible ?? 0).toFixed(2)} Bs</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Monto requerido:</span>
                                <strong class="text-danger">${parseFloat(data.monto_requerido ?? montoRequerido).toFixed(2)} Bs</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2 border-top pt-1">
                                <span>Faltante:</span>
                                <strong class="text-danger">${parseFloat(faltante).toFixed(2)} Bs</strong>
                            </div>
                            <div class="alert alert-warning py-1 px-2 mb-0" style="font-size:0.8rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                Solicite un <strong>traspaso de bóveda a caja</strong> para continuar.
                            </div>
                        </div>`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                });
            } else {
                Swal.fire('Operación no realizada', error.response?.data?.message || 'Ha ocurrido un error inesperado.', 'error');
            }
        },
        async getPlanesPago() {
            await axios.get('/get_planes_pago_sin_desembolso').then((response) => {
                this.lista_planes_pago = response.data;
                console.log(response.data);
            }).catch((error) => {
                    if (axios.isCancel(error)) return;
                console.log(error.message);
            })
        },
       

        buscarCajaFechaSelect() {
            if (this.criterio == 'fecha') {
                this.buscar = '';
                this.getCajasFecha(1);
            } else {
                this.buscar = '';
                this.getCajas(1);
            }
        },
        buscarCajaFecha() {

            this.getCajasFecha(1);
        },
        buscarCaja() {
            this.getCajas(1);
        },
        exportarGastosCorrientesCaja() {
            // Construye la URL con el parámetro fecha_inicio
            const url = '/exportar_gastos_corrientes_pdf?id_caja=' + this.caja.id_caja +
                '&fecha_inicio=' + this.fecha_inicio_gastos_corrientes + '&fecha_final=' + this.fecha_final_gastos_corrientes;

            // Abre una nueva pestaña o ventana con la URL
            window.open(url, '_blank');
        },
        exportarIngresosCorrientesCaja() {
            // Construye la URL con el parámetro fecha_inicio
            const url = '/exportar_ingresos_corrientes_pdf?id_caja=' + this.caja.id_caja + '&fecha_inicio=' + this.fecha_inicio_ingresos_corrientes
                + '&fecha_final=' + this.fecha_final_ingresos_corrientes;

            // Abre una nueva pestaña o ventana con la URL
            window.open(url, '_blank');
        },
        exportarPagosCaja() {
            // Construye la URL con el parámetro fecha_inicio
            const url = '/exportar_pagos_pdf?lista_pagos=' + JSON.stringify(this.lista_pagos) + '&id_caja=' + this.caja.id_caja + '&total_pagos=' + this.totalPagos
                + '&fecha_inicio=' + this.fecha_inicio_pago + '&fecha_final=' + this.fecha_final_pago;

            // Abre una nueva pestaña o ventana con la URL
            window.open(url, '_blank');
        },
        exportarPagosCajaAmortizacion() {
            // Construye la URL con el parámetro fecha_inicio
            const url = '/exportar_pagos_amortizacion_pdf?lista_pagos=' + JSON.stringify(this.lista_pagos_amortizaciones) + '&id_caja=' + this.caja.id_caja + '&total_pagos=' + this.totalPagosAmortizaciones;

            // Abre una nueva pestaña o ventana con la URL
            window.open(url, '_blank');
        },
        cerrarModalVerRegistrosGastosCorrientes() {
            $('#modalVerRegistrosGastosCorrientes').modal('hide');

        },
        abrirModalVerRegistrosGastosCorrientes() {
            this.fecha_inicio_gastos_corrientes = moment().subtract(1, 'week').format('YYYY-MM-DD');
            this.fecha_final_gastos_corrientes = moment().format('YYYY-MM-DD'),
                $('#modalVerRegistrosGastosCorrientes').modal('show');
            this.getGastosCorrientes(1);
        },
        cerrarModalVerRegistrosIngresosCorrientes() {
            $('#modalVerRegistrosIngresosCorrientes').modal('hide');
        },
        abrirModalVerRegistrosIngresosCorrientes() {
            this.fecha_inicio_ingresos_corrientes = moment().subtract(1, 'week').format('YYYY-MM-DD');
            this.fecha_final_ingresos_corrientes = moment().format('YYYY-MM-DD'),
                $('#modalVerRegistrosIngresosCorrientes').modal('show');
            this.getIngresosCorrientes(1);
        },
        getPagos(page) {
            axios.get('/get_pagos_caja?page=' + page + '&buscar=' + this.buscar + '&opcion=' + this.opcion + '&id_caja=' + this.caja.id_caja +
                '&fecha_inicio=' + this.fecha_inicio_pago + '&fecha_final=' + this.fecha_final_pago
            ).then((response) => {
                console.log(response);
                this.lista_pagos = response.data.pagos.data;
                this.paginationPagos = {
                    total: response.data.pagos.total,
                    current_page: response.data.pagos.current_page,
                    per_page: response.data.pagos.per_page,
                    last_page: response.data.pagos.last_page,
                    from: response.data.pagos.from,
                    to: response.data.pagos.to
                };
                this.totalPagos = isNaN(parseFloat(response.data.totalPagos).toFixed(2)) ? 0 : parseFloat(response.data.totalPagos).toFixed(2);
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
                .finally(() => {

                })
        },
        getPagosAmortizaciones(page) {
            axios.get('/get_pagos_caja_amortizaciones?page=' + page + '&buscar=' + this.buscar + '&opcion=' + this.opcion + '&id_caja=' + this.caja.id_caja).then((response) => {
                console.log(response);
                this.lista_pagos_amortizaciones = response.data.pagos.data;
                this.paginationPagos = {
                    total: response.data.pagos.total,
                    current_page: response.data.pagos.current_page,
                    per_page: response.data.pagos.per_page,
                    last_page: response.data.pagos.last_page,
                    from: response.data.pagos.from,
                    to: response.data.pagos.to
                };
                this.totalPagosAmortizaciones = isNaN(parseFloat(response.data.totalPagosAmortizacion).toFixed(2)) ? 0 : parseFloat(response.data.totalPagosAmortizacion).toFixed(2);
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
                .finally(() => {

                })
        },



        getIngresosCorrientes(page) {
            axios.get('/get_ingresos_corrientes?page=' + page + '&id_caja=' + this.caja.id_caja
                + '&fecha_inicio=' + this.fecha_inicio_ingresos_corrientes + '&fecha_final=' + this.fecha_final_ingresos_corrientes
            ).then((response) => {
                console.log(response);
                this.lista_ingresos_corrientes = response.data.ingresos.data;
                this.paginationIngresosCorrientes = {
                    total: response.data.ingresos.total,
                    current_page: response.data.ingresos.current_page,
                    per_page: response.data.ingresos.per_page,
                    last_page: response.data.ingresos.last_page,
                    from: response.data.ingresos.from,
                    to: response.data.ingresos.to
                };
                this.totalIngresosCorrientes = isNaN(parseFloat(response.data.totalIngresos).toFixed(2)) ? 0 : parseFloat(response.data.totalIngresos).toFixed(2);
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
                .finally(() => {

                })
        },
        getGastosCorrientes(page) {
            axios.get('/get_gastos_corrientes?page=' + page + '&id_caja=' + this.caja.id_caja +
                '&fecha_inicio=' + this.fecha_inicio_gastos_corrientes + '&fecha_final=' + this.fecha_final_gastos_corrientes
            ).then((response) => {
                console.log(response);
                this.lista_gastos_corrientes = response.data.gastos.data;
                this.paginationGastosCorrientes = {
                    total: response.data.gastos.total,
                    current_page: response.data.gastos.current_page,
                    per_page: response.data.gastos.per_page,
                    last_page: response.data.gastos.last_page,
                    from: response.data.gastos.from,
                    to: response.data.gastos.to
                };
                this.totalGastosCorrientes = isNaN(parseFloat(response.data.totalGastos).toFixed(2)) ? 0 : parseFloat(response.data.totalGastos).toFixed(2);
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
                .finally(() => {

                })
        },

        cerrarModalVerRegistros() {
            $('#modalVerRegistros').modal('show');
        },
        abrirModalVerRegistros() {
            this.fecha_inicio_pago = moment().subtract(1, 'week').format('YYYY-MM-DD');
            this.fecha_final_pago = moment().format('YYYY-MM-DD');

            $('#modalVerRegistros').modal('show');
            this.getPagos(1);
        },
       
        cerrarModalDetallesCaja() {
            $('#modalDetallesCaja').modal('hide');
        },
        async consultarCajaAbiertaTransaccion() {
            await axios.get('/caja_abierta').then((response) => {
                console.log(response);
                if (response.data.usuario_actual == -1) {
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Caja cerrada',
                        text: 'No hay una caja aperturada',
                        showConfirmButton: true,
                        // timer: 1500
                    });
                    this.estado_caja = true;
                } else {
                    console.log('existe caja aperturada');
                    this.estado_caja = false;
                }
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })

                .finally(() => {

                })
        },
       
        cerrarModalIngreso() {
            $('#modalIngreso').modal('hide');
        },
 
        cerrarModalGasto() {
            $('#modalGasto').modal('hide');
        },
        
        cerrarModalAperturaCaja() {
            $('#modalAbrirCaja').modal('hide');
        },
        cambiarPagina(page) {
            let me = this;
            me.pagination.current_page = page;
            me.getCajas(page);
        },

        cambiarPaginaPagos(page) {
            let me = this;
            me.paginationPagos.current_page = page;
            me.getPagos(page);
        },
        cambiarPaginaPagosAmortizaciones(page) {
            let me = this;
            me.paginationPagosAmortizaciones.current_page = page;
            me.getPagosAmortizaciones(page);
        },
        cambiarPaginaIngresosCorrientes(page) {
            let me = this;
            me.paginationIngresosCorrientes.current_page = page;
            me.getIngresosCorrientes(page);
        },
        cambiarPaginaDesembolsosRealizados(page) {
            let me = this;
            me.paginationDesembolsosRealizados.current_page = page;
            me.getDesembolsos(page);
        },
        cambiarPaginaGastosCorrientes(page) {
            let me = this;
            me.paginationGastosCorrientes.current_page = page;
            me.getGastosCorrientes(page);
        },

        async getCajas(page) {
            await axios.get('/get_caja?page=' + page + '&buscar=' + this.buscar + '&opcion=' + this.opcion + '&criterio=' + this.criterio + '&buscar=' + this.buscar).then((response) => {
                this.lista_caja = response.data.data;
                this.pagination = {
                    total: response.data.total,
                    current_page: response.data.current_page,
                    per_page: response.data.per_page,
                    last_page: response.data.last_page,
                    from: response.data.from,
                    to: response.data.to
                }
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
        },

        getCajasFecha(page) {
            axios.get('/get_caja_fecha?page=' + page + '&buscar=' + this.buscar + '&opcion=' + this.opcion + '&fecha_inicio=' + this.fecha_inicio + '&fecha_final=' + this.fecha_final).then((response) => {
                this.lista_caja = response.data.data;
                this.pagination = {
                    total: response.data.total,
                    current_page: response.data.current_page,
                    per_page: response.data.per_page,
                    last_page: response.data.last_page,
                    from: response.data.from,
                    to: response.data.to
                }
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })
        },

        async consultarCajaAbierta() {
            await axios.get('/caja_abierta').then((response) => {
                console.log(response);
                this.usuario_actual = response.data.usuario_actual;
                if (response.data.usuario_actual == 1) {
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Caja Abierta',
                        text: 'Ya tiene una caja abierta \n Debe cerrar la caja abierta',
                        showConfirmButton: true,
                        // timer: 1500
                    });
                    this.caja.estado_cajas = true;
                } else {
                    if (response.data.usuario_actual == 0) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Caja Abierta',
                            text: 'El usuario ' + response.data.usuario.toUpperCase() + ' tiene abierta una caja \n Debe cerrar la caja abierta',
                            showConfirmButton: true,
                            // timer: 1500
                        });
                        this.caja.estado_cajas = true;
                    } else {
                        console.log('no hay logueado');
                        this.caja.estado_cajas = false;
                    }

                }
            })
                .catch((error) => {
                    if (axios.isCancel(error)) return;
                    console.log(error.message);
                })

                .finally(() => {

                })
        },

    },
    async mounted() {
        this.preloader = true;
        console.log('Component mounted.');
        await this.getCajas(1);
        await this.getPlanesPago();
        await this.getPagosAdm();
        this.preloader = false;
    }

}

</script>

<style scoped>
    @import './styles/frmCaja.css';

    .disbursement-dialog { max-width: 610px; }
    .disbursement-modal { border-radius: 7px; overflow: hidden; }
    .disbursement-header {
        padding: 12px 18px;
        border: 0;
        background: #f4b247;
        color: #24292e;
    }
    .disbursement-header .modal-title { font-size: 14px; letter-spacing: 1.7px; }
    .disbursement-header .btn-close { filter: none; font-size: 11px; }
    .disbursement-body { padding: 15px 18px 17px; }
    .disbursement-client { border: 1px solid #dfe3e6; border-radius: 5px; overflow: hidden; }
    .disbursement-section-title {
        padding: 6px 10px;
        border-bottom: 1px solid #e5e8ea;
        background: #f5f7f6;
        color: #198754;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
    }
    .disbursement-client-grid { display: grid; grid-template-columns: 1.5fr 1fr .55fr; gap: 0; }
    .disbursement-data { min-width: 0; padding: 8px 10px; border-left: 1px solid #eceeef; }
    .disbursement-data:first-child { border-left: 0; }
    .disbursement-data span { display: block; margin-bottom: 2px; color: #7a8187; font-size: 9px; text-transform: uppercase; }
    .disbursement-data strong { display: block; overflow: hidden; color: #30363b; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    .disbursement-amount-card {
        height: 100%;
        padding: 9px 12px;
        border: 1px solid #dfe3e6;
        border-left: 3px solid #198754;
        border-radius: 4px;
        background: #f6faf8;
    }
    .disbursement-amount-card > span { display: block; margin-bottom: 2px; color: #6d747a; font-size: 9px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
    .disbursement-amount-card strong { color: #198754; font-size: 17px; }
    .disbursement-amount-card small { font-size: 10px; }
    .disbursement-amount-card.amount-administrative { border-left-color: #d99a2b; background: #fffaf1; }
    .disbursement-amount-card.amount-administrative strong { color: #b36c12; }
    .disbursement-confirmation {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 9px 11px;
        border: 1px solid #ead7a8;
        border-radius: 4px;
        background: #fff9eb;
        color: #41474c;
    }
    .disbursement-confirmation > i { margin-top: 3px; color: #c18419; font-size: 12px; }
    .disbursement-confirmation .form-check { min-height: 0; margin: 0; }
    .disbursement-confirmation .form-check-input { margin-top: 1px; cursor: pointer; }
    .disbursement-confirmation .form-check-label { padding-left: 3px; cursor: pointer; font-size: 11px; line-height: 1.35; }
    .disbursement-confirmation small { display: block; margin-top: 2px; color: #777d82; font-size: 9px; }
    .disbursement-actions { display: flex; justify-content: flex-end; gap: 8px; }
    .disbursement-actions .btn { min-height: 34px; padding: 6px 13px; border-radius: 3px; font-size: 10px; }

    @media (max-width: 575.98px) {
        .disbursement-dialog { margin: 10px; }
        .disbursement-body { padding: 12px; }
        .disbursement-client-grid { grid-template-columns: 1fr 1fr; }
        .disbursement-data { border-bottom: 1px solid #eceeef; }
        .disbursement-data-client { grid-column: 1 / -1; }
        .disbursement-actions { display: grid; grid-template-columns: auto 1fr; }
    }
</style>
