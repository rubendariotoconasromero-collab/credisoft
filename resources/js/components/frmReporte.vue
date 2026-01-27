<template>
    <main>
        <div class="page-content">
            <div class="container-fluid">
                <div class="card">
                    
                    
                    <div class="card shadow-lg">
                        <div class="card-header bg-warning bg-gradient py-2">
                            <h5 class="header-title my-0 text-center fw-semibold text-dark text-uppercase">
                                Reportes
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Columna de Reportes de Clientes -->
                                <div class="col-lg-6">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h5 class="mb-0 text-white"><i class="fas fa-users me-2"></i>Reportes de Clientes</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-3">
                                                <button @click="reporteListadoClientes()" class="btn btn-outline-secondary text-start d-flex align-items-center">
                                                    <i class="fas fa-file-pdf me-3 fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-0">Listado de clientes</h6>
                                                        <small class="">Reporte completo de todos los clientes</small>
                                                    </div>
                                                </button>
                                                
                                                <button @click="reportePlanesPagoClientes()" class="btn btn-outline-secondary text-start d-flex align-items-center">
                                                    <i class="fas fa-file-contract me-3 fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-0">Planes de pagos y cuotas</h6>
                                                        <small class="">Detalle de planes y estado de cuotas</small>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Columna de Reportes de Planes de Pagos -->
                                <div class="col-lg-6">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h5 class="mb-0 text-white"><i class="fas fa-credit-card me-2"></i>Planes de Pagos y Solicitudes</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-3">
                                                <button @click="abrirListadoGeneralPlanesPago()" class="btn btn-outline-secondary text-start d-flex align-items-center">
                                                    <i class="fas fa-list-alt me-3 fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-0">Listado general de planes de pago</h6>
                                                        <small class="">Resumen completo de todos los planes</small>
                                                    </div>
                                                </button>

                                                <button @click="abrirListadoGeneralSolicitudes()" class="btn btn-outline-secondary text-start d-flex align-items-center">
                                                    <i class="fas fa-list-alt me-3 fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-0">Listado general de solicitudes</h6>
                                                        <small class="">Resumen completo de todos las solicitudes</small>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- End Cardbody -->
                </div>
                <!-- end page-content-wrapper-->
            </div>
            <!-- Container-fluid -->
        </div>

        <div id="modalReportePlanesCuotas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width: 90%; max-width:90%">
                <div class="modal-content border border-2 border-secondary">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark fw-bold">LISTADO DE CLIENTES</h5>
                        <button @click="cerrarModalReportePlanesCuota()" type="button" class="btn-close btn-close-dark"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3 mt-3">
                            <!-- <label class="text-dark fw-bold">Filtros de búsqueda</label> -->
                            <div class="col-md-12">
                                <div class="input-group">
                                    <select v-model="searchCriteria" class="form-select">
                                        <option value="cliente.nombre">Nombre</option>
                                        <option value="cliente.ci">CI</option>
                                    </select>

                                    <input v-model="searchQuery" type="text" class="form-control"
                                        @input="buscarCliente()" />
                                    <button class="btn btn-success">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                        <div class="table-responsive" style="font-size:11px;">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-success text-white text-uppercase fw-bold">
                                    <tr>
                                        <th class="fw-bold text-uppercase">Nombre</th>
                                        <th class="fw-bold text-uppercase">CI</th>
                                        <th class="fw-bold text-uppercase">Sexo</th>
                                        <th class="fw-bold text-uppercase">E. Civil</th>
                                        <th class="fw-bold text-uppercase">Actividad</th>
                                        <th class="fw-bold text-uppercase">Vivienda</th>
                                        <th class="fw-bold text-uppercase">Estado</th>
                                        <th class="fw-bold text-uppercase">Op.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers" :key="customer.id" class="align-middle">
                                        <td class="text-uppercase fw-bold">
                                            {{ customer.nombre }}
                                        </td>
                                        <td class="text-uppercase fw-bold">
                                            {{ customer.ci }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.sexo }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.estado_civil }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.actividad }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.vivienda }}
                                        </td>
                                        <td class="text-uppercase">
                                            <span :class="customer.estado === 1
                                                ? 'badge bg-success w-100 text-center'
                                                : 'badge bg-danger w-100 text-center'" style="display: inline-block;">
                                                {{ customer.estado === 1 ? "Activo" : "Inactivo" }}
                                            </span>
                                        </td>
                                        <td class="position-relative p-2">
                                            <button class="btn btn-sm btn-warning text-dark" @click="verPlanesPago(customer)">
                                                 Planes
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="mb-0 text-dark">
                                Mostrando {{ from }} a {{ to }} de un total de {{ total }} registros.
                            </p>
                            <ul class="pagination mb-0">
                                <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                                    <a class="page-link text-dark" href="#" @click.prevent="prevPage">«</a>
                                </li>
                                <li
                                    v-for="p in totalPages"
                                    :key="p"
                                    class="page-item mx-1"
                                    :class="{ active: p === currentPage }"
                                >
                                    <a class="page-link text-dark" href="#" :class="{ 'text-white': p === currentPage }" @click.prevent="goToPage(p)">
                                        {{ p }}
                                    </a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
                                    <a class="page-link text-dark" href="#" @click.prevent="nextPage">»</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <div id="modalReporteSolicitudesPorUsuario" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reporte de solicitudes por usuario</h5>
                        <button @click="cerrarModalReporteSolicitudesPorUsuario()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="col-md-12">
                                    <label>Usuario</label>
                                    <select v-model="usuario.id_usuario" class="form-control ps-4 py-2" id="id_usuario"
                                        name="id_usuario" required>
                                        <option value="0" selected hidden disabled>Seleccione un usuario</option>
                                        <option style="padding-top: 10px" v-for="(item, index) in lista_usuarios"
                                            :value="item.id" :key="index">
                                            {{ item.personal }}</option>
                                    </select>
                                    <h5 class="font-size-14 mt-3 mb-2">Seleccione una o varias opciones</h5>
                                    <div class="form-check mb-2">
                                        <input v-model="solicitud.aprobados" class="form-check-input" type="checkbox"
                                            value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Aprobados
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input v-model="solicitud.por_aprobar" class="form-check-input" type="checkbox"
                                            value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            En espera
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input v-model="solicitud.anulados" class="form-check-input" type="checkbox"
                                            value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Anulados
                                        </label>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="container text-end">
                            <button @click="cerrarModalReporteSolicitudesPorUsuario()" class="btn btn-secondary mx-2">
                                Cerrar
                            </button>
                            <button @click="generarReporteSolicitudesUsuario()" class="btn btn-primary mx-2">
                                Generar reporte
                            </button>
                            <!-- <button class="btn btn-primary">
                                Guardar
                            </button> -->
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <div id="modalReporteSolicitudesPorRango" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reporte de solicitudes por rango de fecha</h5>
                        <button @click="cerrarModalReporteSolicitudesPorRango()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="col-md-12">
                                    <label>Ingrese rango de fechas</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Desde</label>
                                                <input class="form-control" type="text"
                                                    v-model="solicitud.fecha_inicial">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Hasta</label>
                                                <input class="form-control" type="text" v-model="solicitud.fecha_final">
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="font-size-14 mt-3 mb-2">Seleccione una o varias opciones</h5>
                                    <div class="form-check mb-2">
                                        <input v-model="solicitud.aprobados" class="form-check-input" type="checkbox"
                                            value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Aprobados
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input v-model="solicitud.por_aprobar" class="form-check-input" type="checkbox"
                                            value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            En espera
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input v-model="solicitud.anulados" class="form-check-input" type="checkbox"
                                            value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Anulados
                                        </label>
                                    </div>


                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="container text-end">
                            <button @click="cerrarModalReporteSolicitudesPorRango()" class="btn btn-secondary mx-2">
                                Cerrar
                            </button>
                            <button @click="reporteListadoSolicitudesPorRango()" class="btn btn-primary mx-2">
                                Generar reporte
                            </button>
                            <!-- <button class="btn btn-primary">
                                Guardar
                            </button> -->
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <div class="modal fade" id="modalReportePlanPagos" tabindex="-1" aria-labelledby="dateModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="dateModalLabel">Reporte de planes de pago por fecha</h5>
                        <button @click="cerrarModalReportePlanPagos()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Seleccione las fechas</h6>
                        <form>
                            <div class="mb-3">
                                <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio"
                                    v-model="fecha_inicio_plan_pago">
                            </div>
                            <div class="mb-3">
                                <label for="fechaFinal" class="form-label">Fecha Final</label>
                                <input type="date" class="form-control" id="fechaFinal" v-model="fecha_final_plan_pago">
                            </div>
                            <div class="mb-3">
                                <h5 class="font-size-14 mt-3 mb-2">Seleccione una o varias opciones</h5>
                                <div class="form-check mb-2">
                                    <input v-model="nuevo_plan_pago" class="form-check-input" type="checkbox"
                                        id="checkAprobadoPlan">
                                    <label class="form-check-label" for="checkAprobadoPlan">
                                        Nuevos
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input v-model="cancelado_plan_pago" class="form-check-input" type="checkbox"
                                        id="checkEsperaPlan">
                                    <label class="form-check-label" for="checkEsperaPlan">
                                        Cancelados
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input v-model="anulado_plan_pago" class="form-check-input" type="checkbox"
                                        id="checkAnuladoPlan">
                                    <label class="form-check-label" for="checkAnuladoPlan">
                                        Anulados
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button @click="listadoGeneralPlanesPago()" type="button" class="btn btn-success w-100">
                            <i class="fas fa-file-alt"></i> Generar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal listado solicitudes -->
        <div class="modal fade" id="modalReporteSolicitudes" tabindex="-1" aria-labelledby="dateModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="dateModalLabel">Reporte general de solicitudes</h5>
                        <button @click="cerrarModalReporteSolicitudes()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Seleccione las fechas</h6>
                        <form>
                            <div class="mb-3">
                                <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio"
                                    v-model="fecha_inicio_solicitud">
                            </div>
                            <div class="mb-3">
                                <label for="fechaFinal" class="form-label">Fecha Final</label>
                                <input type="date" class="form-control" id="fechaFinal" v-model="fecha_final_solicitud">
                            </div>
                            <div class="mb-3">
                                <h5 class="font-size-14 mt-3 mb-2">Seleccione una o varias opciones</h5>
                                <div class="form-check mb-2">
                                    <input v-model="nuevo_solicitud" class="form-check-input" type="checkbox"
                                        id="checkNuevoSolicitud">
                                    <label class="form-check-label" for="checkNuevoSolicitud">
                                        Nuevos
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input v-model="aprobado_solicitud" class="form-check-input" type="checkbox"
                                        id="checkAprobadoSolicitud">
                                    <label class="form-check-label" for="checkAprobadoSolicitud">
                                        Aprobados
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input v-model="anulado_solicitud" class="form-check-input" type="checkbox"
                                        id="checkAnuladoSolicitud">
                                    <label class="form-check-label" for="checkAnuladoSolicitud">
                                        Anulados
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button @click="reporteListadoGeneralSolicitudes()" type="button" class="btn btn-success w-100">
                            <i class="fas fa-file-alt"></i> Generar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalPlanesClientes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width:90%; max-width:90%">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark fw-bold">
                            <i class="fas fa-file-invoice-dollar"></i> CRÉDITOS | 
                            <span class="text-uppercase">{{ currentCustomer?.nombre +' - '+ currentCustomer?.ci }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-dark"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle table-sm">
                                <thead class="table-success">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold">#</th>
                                        <th scope="col" class="text-uppercase fw-bold">Monto</th>
                                        <th scope="col" class="text-uppercase fw-bold">Moneda</th>
                                        <th scope="col" class="text-uppercase fw-bold">Cuotas</th>
                                        <th scope="col" class="text-uppercase fw-bold">Tasa %</th>
                                        <th scope="col" class="text-uppercase fw-bold">Desembolso</th>
                                        <th scope="col" class="text-uppercase fw-bold">Primera Cuota</th>
                                        <th scope="col" class="text-uppercase fw-bold">Asesor</th>
                                        <th scope="col" class="text-uppercase fw-bold">Estado</th>
                                        <th scope="col" class="text-uppercase fw-bold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(plan, index) in lista_planes_pago" :key="plan.id">
                                        <td>{{ index + 1 }}</td>
                                        <td class="fw-bold">{{ plan.importe_solicitud }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ plan.moneda }}</span>
                                        </td>
                                        <td>{{ plan.nro_cuotas }} cuotas</td>
                                        <td>{{ plan.tasa }}%</td>
                                        <td>{{ formatDate(plan.fecha_desembolso) }}</td>
                                        <td>{{ formatDate(plan.fecha_primera_cuota) }}</td>
                                        <td>{{ plan.asesor }}</td>
                                        <td>
                                            <span style="width:100px;"
                                                :class="getEstadoClass(plan.estado, plan.tipo_solicitud)">

                                                <i v-if="plan.desembolso === 0" class="fas fa-exclamation-circle"
                                                    style="font-size:0.6rem; color:#fff; cursor:pointer"
                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                    data-bs-content="IMPORTE POR DESEMBOLSAR"
                                                    data-bs-trigger="hover"></i>
                                                {{ getEstadoText(plan.estado, plan.tipo_solicitud) }}
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" 
                                                        title="Ver Detalle" @click="generarPlanPago(plan.id_plan_pago)">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!lista_planes_pago || lista_planes_pago.length === 0">
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No se encontraron planes de pago
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalPlanesGeneral" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width:95%; max-width:95%">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark text-uppercase fw-bold">
                            LISTADO GENERAL DE CREDITOS
                        </h5>
                        <button type="button" class="btn-close btn-close-dark"
                            data-bs-dismiss="modal" aria-label="Close" @click="cerrarModalPlanesGeneral()"></button>
                    </div>
                    <div class="modal-body">
                        <!-- seccion de filtros -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <!-- Asesor -->
                                    <select @change="buscarPlanPago()" v-model="filtroPlanPago.opcion_asesor" class="form-select">
                                        <option value="0">Todos los asesores</option>
                                        <option v-for="(item, index) in lista_asesores" :key="index" :value="item.id">
                                            {{ item.personal }}
                                        </option>
                                    </select>

                                    <!-- Criterio -->
                                    <select @change="buscarPlanPago()" v-model="filtroPlanPago.criterio" class="form-select">
                                        <option value="plan_pago.id">Cod. Credito</option>
                                        <option value="cliente.nombre">Nombre cliente</option>
                                        <option value="cliente.ci">CI</option>
                                    </select>

                                    <!-- Estado -->
                                    <select @change="buscarPlanPago()" v-model="filtroPlanPago.estado_credito" class="form-select">
                                        <option value="todos">Todos</option>
                                        <option value="vigentes">Vigentes</option>
                                        <option value="vencidos">Vencidos</option>
                                    </select>

                                    <!-- Fecha Inicio -->
                                    <input v-model="filtroPlanPago.fecha_inicio" type="date" class="form-control"
                                        @change="buscarPlanPago()">

                                    <!-- Fecha Fin -->
                                    <input v-model="filtroPlanPago.fecha_fin" type="date" class="form-control"
                                        @change="buscarPlanPago()">

                                    <!-- Buscador -->
                                    <input v-model="filtroPlanPago.buscar_plan_pago" type="text" class="form-control" placeholder="Buscar...">

                                    <button class="btn btn-success" @click="buscarPlanPago">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button @click="generatePlanesPagoGeneralPDF()" class="btn btn-warning btn-sm ms-1">
                                        <i class="fas fa-file-pdf"></i>
                                        PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="font-size:12px;">
                            <table class="table table-striped table-hover align-middle table-sm">
                                <thead class="table-success">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold">Credito</th>
                                        <th scope="col" class="text-uppercase fw-bold">Cliente</th>
                                        <th scope="col" class="text-uppercase fw-bold">Codeudores/Garantes</th>
                                        <th scope="col" class="text-uppercase fw-bold">Monto</th>
                                        <th scope="col" class="text-uppercase fw-bold">Cuotas</th>
                                        <th scope="col" class="text-uppercase fw-bold">Tasa %</th>
                                        <th scope="col" class="text-uppercase fw-bold">Inicio</th>
                                        <th scope="col" class="text-uppercase fw-bold">Finaliza</th>
                                        <th scope="col" class="text-uppercase fw-bold">Asesor</th>
                                        <th scope="col" class="text-uppercase fw-bold">Estado</th>
                                        <th scope="col" class="text-uppercase fw-bold">Op.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(plan, index) in lista_planes_pago_general" :key="plan.id">
                                        <td>{{ plan.id_plan_pago }}</td>
                                        <td>{{ plan.cliente }}</td>
                                        <td>
                                            <ul v-if="plan.codeudores" style="margin: 0; padding-left: 20px;">
                                                <li v-for="codeudor in plan.codeudores.split(',')" :key="codeudor">
                                                    {{ codeudor.trim() }}
                                                </li>
                                            </ul>
                                            <span v-else>Sin codeudores</span>
                                        </td>
                                        <td class="fw-bold">{{ plan.importe_solicitud }}</td>
                       
                                        <td class="text-uppercase">
                                            <small style="font-size:10px;">
                                                {{ plan.nro_cuotas }}
                                                <small style="font-size:10px;">
                                                    ({{ plan.lapso_capital }})
                                                </small>
                                            </small>
                                        </td>
                                        <td>{{ plan.tasa }}%</td>
                                        <td>{{ formatDate(plan.fecha_desembolso) }}</td>
                                        <td>{{ formatDate(plan.fecha_primera_cuota) }}</td>
                                        <td>{{ plan.asesor }}</td>
                                        <td>
                                            <span style="width:100px;" :class="getEstadoClass(plan.estado, plan.tipo_solicitud)">
                                                <i v-if="plan.desembolso === 0" class="fas fa-exclamation-circle"
                                                    style="font-size:0.6rem; color:#fff; cursor:pointer"
                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                    data-bs-content="IMPORTE POR DESEMBOLSAR"
                                                    data-bs-trigger="hover"></i>
                                                {{ getEstadoText(plan.estado, plan.tipo_solicitud) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" 
                                                        title="Ver Detalle" @click="generarPlanPago(plan.id_plan_pago)">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!lista_planes_pago_general || lista_planes_pago_general.length === 0">
                                        <td colspan="11" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No se encontraron planes de pago
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="mb-0 text-dark">
                                Mostrando {{ paginationPlanesGeneral.from }} a {{ paginationPlanesGeneral.to }} de un total de {{ paginationPlanesGeneral.total }} registros.
                            </p>
                            <ul class="pagination mb-0">
                                <li class="page-item" :class="{ disabled: paginationPlanesGeneral.currentPage <= 1 }">
                                    <a class="page-link text-dark" href="#" @click.prevent="prevPage()">«</a>
                                </li>
                                <li
                                    v-for="p in paginationPlanesGeneral.totalPages"
                                    :key="p"
                                    class="page-item mx-1"
                                    :class="{ active: p === paginationPlanesGeneral.currentPage }"
                                >
                                    <a class="page-link text-dark" href="#" :class="{ 'text-white': p === paginationPlanesGeneral.currentPage }" @click.prevent="goToPage(p)">
                                        {{ p }}
                                    </a>
                                </li>
                                <li class="page-item" :class="{ disabled: paginationPlanesGeneral.currentPage >= paginationPlanesGeneral.totalPages }">
                                    <a class="page-link text-dark" href="#" @click.prevent="nextPage()">»</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalSolicitudes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width:95%; max-width:95%">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark fw-bold">
                            Listado General de Solicitudes
                        </h5>
                        <button type="button" class="btn-close btn-close-dark"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4 mt-1">
                            <!-- Date Range Filter -->
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input v-model="filtroSolicitudes.fecha_inicio" type="date" class="form-control" @change="getSolicitudes()"/>

                                    <input v-model="filtroSolicitudes.fecha_fin" type="date" class="form-control" @change="getSolicitudes()"/>
                                </div>
                            </div>

                            <!-- Status and Search Filters -->
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select v-model="filtroSolicitudes.estado_solicitud" class="form-select"
                                        @change="getSolicitudes()">
                                        <option value="todos">Todos</option>
                                        <option value="1">Nuevo</option>
                                        <option value="2">Aprobados</option>
                                        <option value="0">Anulados</option>
                                    </select>
                                    <select v-model="filtroSolicitudes.criterio" class="form-select">
                                        <option value="cliente.nombre">
                                            Cliente
                                        </option>
                                        <option value="cliente.ci">
                                            CI Cliente
                                        </option>
                                    </select>
                                    <input v-model="filtroSolicitudes.buscar_solicitud" type="text" class="form-control" placeholder="Buscar..."/>
                                    <button class="btn btn-success">
                                        <i class="fas fa-search"></i>
                                    </button>

                                    <button class="btn btn-warning text-dark ms-2">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                        <div class="table-responsive" style="font-size:12px;">
                            <table class="table table-striped table-hover align-middle table-sm">
                                <thead class="table-success">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold">#</th>
                                        <th scope="col" class="text-uppercase fw-bold">Cliente</th>
                                        <th scope="col" class="text-uppercase fw-bold">codeudores/Garantes</th>
                                        <th scope="col" class="text-uppercase fw-bold">Monto</th>
                                        <th scope="col" class="text-uppercase fw-bold">Cuotas</th>
                                        <th scope="col" class="text-uppercase fw-bold">Tasa %</th>
                                        <th scope="col" class="text-uppercase fw-bold">Desembolso</th>
                                        <th scope="col" class="text-uppercase fw-bold">Asesor</th>
                                        <th scope="col" class="text-uppercase fw-bold">Estado</th>
                                        <!-- <th scope="col" class="text-uppercase fw-bold">Op.</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(solicitud, index) in lista_solicitudes" :key="solicitud.id">
                                        <td>{{ solicitud.id_solicitud }}</td>
                                        <td class="fw-bold">{{ solicitud.cliente }}</td>
                                        <td>
                                            <ul v-if="solicitud.codeudores" style="margin: 0; padding-left: 20px;">
                                                <li v-for="codeudor in solicitud.codeudores.split(',')" :key="codeudor">
                                                    {{ codeudor.trim() }}
                                                </li>
                                            </ul>
                                            <span v-else>Sin codeudores</span>
                                        </td>
                                        <td class="fw-bold">{{ solicitud.importe_solicitud }}</td>
                                        <td>{{ solicitud.nro_cuotas }} cuotas</td>
                                        <td>{{ solicitud.tasa }}%</td>
                                        <td>{{ formatDate(solicitud.fecha_desembolso) }}</td>
                                        <td>{{ solicitud.asesor }}</td>
                                        <td>
                                            <span style="width:100px;"
                                                :class="getEstadoClass(solicitud.estado, solicitud.tipo_solicitud)">

                                                <i v-if="solicitud.desembolso === 0" class="fas fa-exclamation-circle"
                                                    style="font-size:0.6rem; color:#fff; cursor:pointer"
                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                    data-bs-content="IMPORTE POR DESEMBOLSAR"
                                                    data-bs-trigger="hover"></i>
                                                {{ getEstadoText(solicitud.estado, solicitud.tipo_solicitud) }}
                                            </span>
                                        </td>
                                        
                                    </tr>
                                    <tr v-if="!lista_solicitudes || lista_solicitudes.length === 0">
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No se encontraron solicitudes
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="mb-0 text-dark">
                                Mostrando {{ paginationSolicitudes.from }} a {{ paginationSolicitudes.to }} de un total de {{ paginationSolicitudes.total }} registros.
                            </p>
                            <ul class="pagination mb-0">
                                <li class="page-item" :class="{ disabled: paginationSolicitudes.currentPage <= 1 }">
                                    <a class="page-link text-dark" href="#" @click.prevent="prevPageSolicitudes()">«</a>
                                </li>
                                <li
                                    v-for="p in paginationSolicitudes.totalPages"
                                    :key="p"
                                    class="page-item mx-1"
                                    :class="{ active: p === paginationSolicitudes.currentPage }"
                                >
                                    <a class="page-link text-dark" href="#" :class="{ 'text-white': p === paginationSolicitudes.currentPage }" @click.prevent="goToPageSolicitudes(p)">
                                        {{ p }}
                                    </a>
                                </li>
                                <li class="page-item" :class="{ disabled: paginationSolicitudes.currentPage >= paginationSolicitudes.totalPages }">
                                    <a class="page-link text-dark" href="#" @click.prevent="nextPageSolicitudes()">»</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    import axios from 'axios';
    import Swal from 'sweetalert2'
    import moment from 'moment';
    import { debounce } from 'lodash';


    export default {
        data() {
            return {
                filtroSolicitudes:{
                    criterio:'cliente.nombre',
                    estado_solicitud:'todos',
                    buscar_solicitud:'',
                    fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                    fecha_fin: moment().format('YYYY-MM-DD'),
                },

                paginationSolicitudes:{
                    currentPage: 1,
                    totalPages: 0,
                    from: 0,
                    to: 0,
                    total: 0,
                },

                lista_solicitudes:[],

                filtroPlanPago:{
                    opcion_asesor:0,
                    criterio:'cliente.nombre',
                    estado_credito:'todos',
                    buscar_plan_pago:'',
                    fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                    fecha_fin: moment().format('YYYY-MM-DD'),
                },
                lista_asesores:[],
                lista_planes_pago_general:[],
                paginationPlanesGeneral:{
                    currentPage: 1,
                    totalPages: 0,
                    from: 0,
                    to: 0,
                    total: 0,
                },

                currentPage: 1,
                totalPages: 0,
                from: 0,
                to: 0,
                total: 0,
                advisorOption: 0,
                searchCriteria: "cliente.nombre",
                searchQuery: "",
                customers:[],
                currentCustomer:null,


                nuevo_plan_pago:false,
                anulado_plan_pago:false,
                cancelado_plan_pago:false,

                fecha_final_solicitud:moment().format('YYYY-MM-DD'),
                fecha_inicio_solicitud:moment().format('YYYY-MM-DD'),

                nuevo_solicitud:false,
                anulado_solicitud:false,
                aprobado_solicitud:false,
                
                fecha_inicio_plan_pago:moment().format('YYYY-MM-DD'),
                fecha_final_plan_pago:moment().format('YYYY-MM-DD'),
                isVisibleCliente:false,
                lista_usuarios:[],

                items_cliente:[],
                lista_planes_pago:[],
                usuario:{
                    id_usuario:0,
                },
                cliente:{
                    id_cliente:0,
                    idd_cliente:'',
                    nombre:'',
                    ci:0,
                    lugar_expedicion:'',
                    buscar:'',
                    id_plan_pago:0,
                },


                solicitud:{
                    aprobados:false,
                    por_aprobar:false,
                    anulados:false,
                    fecha_inicial:moment().format('YYYY-MM-DD'),
                    fecha_final:moment().format('YYYY-MM-DD'),
         
                },

            }
        },
        computed:{
            filteredItemsCliente() {
                const searchTermLower = this.cliente.idd_cliente.toLowerCase();
                return this.items_cliente.filter(item => {
                    const nombreLower = item.nombre.toLowerCase();
                    const ciLower = item.ci.toLowerCase();
                    return nombreLower.includes(searchTermLower) || ciLower.includes(searchTermLower);
                });
            },
        },
        watch: {
            searchQuery: debounce(function(newVal) {
                this.getClientes();
            }, 300),

            'filtroSolicitudes.buscar_solicitud':debounce(function(newVal) {
                this.getSolicitudes();
            }, 300)
        },
        methods: {

            cerrarModalSolicitudes(){
                $('#modalSolicitudes').modal('hide')
            },
            abrirModalSolicitudes(){
                $('#modalSolicitudes').modal('show')
            },
            async abrirListadoGeneralSolicitudes(){
                this.filtroSolicitudes={
                    criterio:'cliente.nombre',
                    estado_solicitud:'todos',
                    buscar_solicitud:'',
                    fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                    fecha_fin: moment().format('YYYY-MM-DD'),
                };
                this.abrirModalSolicitudes();
                await this.getSolicitudes();
            },
            prevPageSolicitudes() {
                if (this.paginationSolicitudes.currentPage > 1) {
                    this.paginationSolicitudes.currentPage--;
                    this.getSolicitudes(this.paginationSolicitudes.currentPage);
                }
            },
            nextPageSolicitudes() {
                if (this.paginationSolicitudes.currentPage < this.paginationSolicitudes.totalPages) {
                    this.paginationSolicitudes.currentPage++;
                    this.getSolicitudes(this.paginationSolicitudes.currentPage);
                }
            },
            goToPageSolicitudes(page) {
                if (page >= 1 && page <= this.paginationSolicitudes.totalPages) {
                    this.paginationSolicitudes.currentPage = page;
                    this.getSolicitudes(this.paginationSolicitudes.currentPage);
                }
            },


            async generateSolicitudesGeneralPDF() {
                try {
                    // Mostrar alerta de procesamiento
                    Swal.fire({
                        title: 'Generando PDF...',
                        text: 'Por favor espere mientras se procesa el documento',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const response = await axios.get('/get_solicitudes_general_pdf', {
                        params: {
                            page,
                            criterio: this.filtroSolicitudes.criterio,
                            buscar: this.filtroSolicitudes.buscar_solicitud,
                            estado: this.filtroSolicitudes.estado_solicitud,
                            fecha_inicio: this.filtroSolicitudes.fecha_inicio,
                            fecha_fin: this.filtroSolicitudes.fecha_fin,
                        },
                        responseType: 'blob',
                    });

                    // Cerrar el loading
                    Swal.close();

                    // Crear una URL para el blob y abrir en una nueva pestaña
                    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
                    window.open(url, '_blank');

                    // Opcional: Mostrar mensaje de éxito
                    Swal.fire({
                        title: '¡PDF Generado!',
                        text: 'El documento se ha generado correctamente',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                } catch (error) {
                    // Cerrar el loading en caso de error
                    Swal.close();
                    
                    console.error("Error generating PDF:", error);
                    
                    // Mostrar mensaje de error
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al generar el PDF',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },


            async getSolicitudes(page=1){
                try{
                    const response=await axios.get('/get_solicitudes_general', {
                        params: {
                            page,
                            criterio: this.filtroSolicitudes.criterio,
                            buscar: this.filtroSolicitudes.buscar_solicitud,
                            estado: this.filtroSolicitudes.estado_solicitud,
                            fecha_inicio: this.filtroSolicitudes.fecha_inicio,
                            fecha_fin: this.filtroSolicitudes.fecha_fin,
                        },
                    });

                    const data = response.data;
                    this.lista_solicitudes = data.data;
                    this.paginationSolicitudes.from = data.from;
                    this.paginationSolicitudes.to = data.to;
                    this.paginationSolicitudes.total = data.total;
                    this.paginationSolicitudes.currentPage = data.current_page;
                    this.paginationSolicitudes.totalPages = data.last_page;

                } catch (error) {
                    console.error("Error fetching solicitudes:", error);
                } finally {
                    //this.preloader = false;
                }
            },
            async generatePlanesPagoGeneralPDF() {
                try {
                    // Mostrar alerta de procesamiento
                    Swal.fire({
                        title: 'Generando PDF...',
                        text: 'Por favor espere mientras se procesa el documento',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const response = await axios.get('/get_planes_pago_general_pdf', {
                        params: {
                            criterio: this.filtroPlanPago.criterio,
                            buscar: this.filtroPlanPago.buscar_plan_pago,
                            estado: this.filtroPlanPago.estado_credito,
                            asesor: this.filtroPlanPago.opcion_asesor,
                            fecha_inicio: this.filtroPlanPago.fecha_inicio,
                            fecha_fin: this.filtroPlanPago.fecha_fin,
                            id_cliente: this.filtroPlanPago.id_cliente,
                        },
                        responseType: 'blob',
                    });

                    // Cerrar el loading
                    Swal.close();

                    // Crear una URL para el blob y abrir en una nueva pestaña
                    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
                    window.open(url, '_blank');

                    // Opcional: Mostrar mensaje de éxito
                    Swal.fire({
                        title: '¡PDF Generado!',
                        text: 'El documento se ha generado correctamente',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                } catch (error) {
                    // Cerrar el loading en caso de error
                    Swal.close();
                    
                    console.error("Error generating PDF:", error);
                    
                    // Mostrar mensaje de error
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al generar el PDF',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            async buscarPlanPago(){
                await this.getPlanesPagoGeneral();
            },
            async getAsesores() {
                await axios.get('/get_asesores')
                    .then((response) => {
                        console.log(response);
                        this.lista_asesores = response.data;
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
            },
            cerrarModalPlanesGeneral(){
                $('#modalPlanesGeneral').modal('hide');
            },
            abrirModalPlanesGeneral(){
                $('#modalPlanesGeneral').modal('show');
            },
          
            prevPagePlanes() {
                if (this.paginationPlanesGeneral.currentPage > 1) {
                    this.paginationPlanesGeneral.currentPage--;
                    this.getClientes(this.paginationPlanesGeneral.currentPage);
                }
            },
            nextPagePlanes() {
                if (this.paginationPlanesGeneral.currentPage < this.paginationPlanesGeneral.totalPages) {
                    this.paginationPlanesGeneral.currentPage++;
                    this.getClientes(this.paginationPlanesGeneral.currentPage);
                }
            },
            goToPagePlanes(page) {
                if (page >= 1 && page <= this.paginationPlanesGeneral.totalPages) {
                    this.paginationPlanesGeneral.currentPage = page;
                    this.getClientes(this.paginationPlanesGeneral.currentPage);
                }
            },

            async abrirListadoGeneralPlanesPago(){
                this.filtroPlanPago={
                    opcion_asesor:0,
                    criterio:'cliente.nombre',
                    estado_credito:'todos',
                    buscar_plan_pago:'',
                    fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                    fecha_fin: moment().format('YYYY-MM-DD'),
                };
                await this.getAsesores(); 
                await this.getPlanesPagoGeneral();
                this.abrirModalPlanesGeneral();
            },

            async getPlanesPagoGeneral(page=1){
                try{
                    const response=await axios.get('/get_planes_pago_general', {
                        params: {
                            page,
                            criterio: this.filtroPlanPago.criterio,
                            buscar: this.filtroPlanPago.buscar_plan_pago,
                            estado: this.filtroPlanPago.estado_credito,
                            asesor: this.filtroPlanPago.opcion_asesor,
                            fecha_inicio: this.filtroPlanPago.fecha_inicio,
                            fecha_fin: this.filtroPlanPago.fecha_fin,
                        },
                    });

                    const data = response.data;
                    this.lista_planes_pago_general = data.data;
                    this.paginationPlanesGeneral.from = data.from;
                    this.paginationPlanesGeneral.to = data.to;
                    this.paginationPlanesGeneral.total = data.total;
                    this.paginationPlanesGeneral.currentPage = data.current_page;
                    this.paginationPlanesGeneral.totalPages = data.last_page;

                } catch (error) {
                    console.error("Error fetching customers:", error);
                } finally {
                    //this.preloader = false;
                }
                
            },
            generarPlanPago(id_plan_pago) {
                const url = `/reporte_planes_pago_cuotas_cliente?id_plan_pago=${id_plan_pago}`;
                window.open(url, '_blank');
            },
            getEstadoText(estado, tipo_solicitud) {
                if (estado == 1) {
                    if (tipo_solicitud == 'Nuevo') return "Nuevo";
                    if (tipo_solicitud == 'Reprogramada') return "Reprogramación";
                    if (tipo_solicitud == 'Refinanciada') return "Refinanciada";
                } else if (estado == 2) {
                    if (tipo_solicitud == 'Nuevo') return "Normal";
                    if (tipo_solicitud == 'Reprogramada') return "Reprogramado";
                    if (tipo_solicitud == 'Refinanciada') return "Refinanciada";

                } else if (estado == 0) {
                    return "Anulado";
                } else {
                    return "Desconocido";
                }
            },
            getEstadoClass(estado, tipo_solicitud) {
                if (estado == 1) {
                    if (tipo_solicitud == 'Nuevo') return "badge bg-info badge-fixed-width";
                    if (tipo_solicitud == 'Reprogramada') return "badge bg-warning badge-fixed-width";
                    if (tipo_solicitud == 'Refinanciada') return "badge bg-primary badge-fixed-width";
                    return "badge bg-info badge-fixed-width"; // default para estado 1
                } else if (estado == 2) {
                    if (tipo_solicitud == 'Nuevo') return "badge bg-success badge-fixed-width";
                    if (tipo_solicitud == 'Reprogramada') return "badge bg-primary badge-fixed-width";
                    if (tipo_solicitud == 'Refinanciada') return "badge bg-primary badge-fixed-width";
                    return "badge bg-success badge-fixed-width"; // default para estado 2
                } else if (estado == 0) {
                    return "badge bg-danger badge-fixed-width";
                } else {
                    return "badge bg-secondary badge-fixed-width";
                }
            },
            
            
            formatDate(fecha) {
                if (!fecha) return '-';
                return new Date(fecha).toLocaleDateString('es-ES');
            },
            cerrarModalPlanesCliente(){
                $('#modalPlanesClientes').modal('hide');
            },
            abrirModalPlanesCliente(){
                $('#modalPlanesClientes').modal('show');
            },
            async verPlanesPago(customer){
                this.currentCustomer=customer;
                await this.getPlanesPagoCliente(this.currentCustomer.id);
                this.abrirModalPlanesCliente();
            },
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.getClientes(this.currentPage);
                }
            },
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.getClientes(this.currentPage);
                }
            },
            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                    this.getClientes(this.currentPage);
                }
            },
            cerrarModalReportePlanPagos(){
                $('#modalReportePlanPagos').modal('hide');
            },
            abrirModalReportePlanPagos(){
                $('#modalReportePlanPagos').modal('show');
            },
            listadoPlanesPagoCancelados(){
                const url = '/reporte_planes_pago_cancelados';
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            listadoPlanesPagoEnProceso(){
                const url = '/reporte_planes_pago_en_proceso';
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },

            
            listadoGeneralPlanesPago(){

                let cancelados=this.cancelado_plan_pago==true?2:'';
                let nuevos=this.nuevo_plan_pago==true?1:'';
                let anulados=this.anulado_plan_pago==true?10:'';

                if(cancelados=='' && nuevos=='' && anulados==''){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debe seleccionar una opcion de búsqueda',
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar',
                        //timer: 1500

                    });
                    return 0;
                }
                
                const url = '/reporte_general_planes_pago?fecha_inicio='+this.fecha_inicio_plan_pago+
                '&fecha_final='+this.fecha_final_plan_pago+'&nuevo='+nuevos+'&anulado='+anulados
                +'&cancelado='+cancelados;
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            
            },
            abrirModalReporteSolicitudesPorRango(){
                this.solicitud.aprobados=false;
                this.solicitud.por_aprobar=false;
                this.solicitud.anulados=false;
                $('#modalReporteSolicitudesPorRango').modal('show'); 
            },
            cerrarModalReporteSolicitudesPorRango(){
                $('#modalReporteSolicitudesPorRango').modal('hide'); 
            },
            reporteListadoSolicitudesPorRango(){
                let aprobados=this.solicitud.aprobados==true?2:'';
                let por_aprobar=this.solicitud.por_aprobar==true?1:'';
                let anulados=this.solicitud.anulados==true?3:'';

                
                if(!this.solicitud.aprobados && !this.solicitud.por_aprobar && !this.solicitud.anulados){
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Debe seleccionar una opcion de búsqueda',
                            showConfirmButton: true,
                            // timer: 1500

                        });
                }else{
                        const url = '/reporte_solicitudes_por_rango?fecha_inicial='+this.solicitud.fecha_inicial+'&fecha_final='+this.solicitud.fecha_final+'&aprobados='+aprobados+'&por_aprobar='+por_aprobar+'&anulados='+anulados;
                    
                        // Abre una nueva pestaña o ventana con la URL
                        window.open(url, '_blank');
                }

                
            },
            generarReporteSolicitudesUsuario(){
                let aprobados=this.solicitud.aprobados==true?2:'';
                let por_aprobar=this.solicitud.por_aprobar==true?1:'';
                let anulados=this.solicitud.anulados==true?3:'';

                if(this.usuario.id_usuario==0){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debe seleccionar un usuario',
                        showConfirmButton: true,
                        // timer: 1500

                    });
                }else{
                    if(!this.solicitud.aprobados && !this.solicitud.por_aprobar && !this.solicitud.anulados){
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Debe seleccionar una opcion de la lista',
                            showConfirmButton: true,
                            // timer: 1500

                        });
                    }else{
                        const url = '/reporte_solicitudes_por_usuario?id_usuario='+this.usuario.id_usuario+'&aprobados='+aprobados+'&por_aprobar='+por_aprobar+'&anulados='+anulados;
                    
                        // Abre una nueva pestaña o ventana con la URL
                        window.open(url, '_blank');
                    }

                }
            },
            abrirModalReporteSolicitudesPorUsuario(){
                this.usuario.id_usuario=0;
                this.solicitud.aprobados=false;
                this.solicitud.por_aprobar=false;
                this.solicitud.anulados=false;
                $('#modalReporteSolicitudesPorUsuario').modal('show');
            },
            cerrarModalReporteSolicitudesPorUsuario(){
                $('#modalReporteSolicitudesPorUsuario').modal('hide');
            },
            reporteListadoSolicitudesPorUsuario(){
                this.getUsuarios();
                this.abrirModalReporteSolicitudesPorUsuario();

            },
            reporteListadoSolicitudesAnuladas(){
                const url = '/reporte_solicitudes_anuladas';
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            reporteListadoSolicitudesPorAprobar(){
                const url = '/reporte_solicitudes_por_aprobar';
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            reporteListadoSolicitudesAprobadas(){
                // Construye la URL con el parámetro fecha_inicio
                const url = '/reporte_solicitudes_aprobadas';
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            cerrarModalReporteSolicitudes(){
                $('#modalReporteSolicitudes').modal('hide');
            },
            reporteSolicitudesGeneral(){
                this.nuevo_solicitud=false;
                this.anulado_solicitud=false;
                this.aprobado_solicitud=false;
    
                this.fecha_inicio_solicitud=moment().subtract(1, 'week').format('YYYY-MM-DD');
                this.fecha_final_solicitud=moment().format('YYYY-MM-DD');

                this.abrirModalReporteSolicitudes();
            },
            abrirModalReporteSolicitudes(){

                $('#modalReporteSolicitudes').modal('show');
            },
            reporteListadoGeneralSolicitudes(){
                //let cancelados=this.cancelado_plan_pago==true?2:'';
                let nuevos=this.nuevo_solicitud==true?1:'';
                let aprobados=this.aprobado_solicitud==true?2:'';
                let anulados=this.anulado_solicitud==true?10:'';


                if(nuevos=='' && anulados=='' && aprobados==''){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debe seleccionar una opcion de búsqueda',
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar',
                        //timer: 1500

                    });
                    return 0;
                }


                // Construye la URL con el parámetro fecha_inicio

                const url = '/reporte_general_solicitudes?fecha_inicio='+this.fecha_inicio_solicitud+
                '&fecha_final='+this.fecha_final_solicitud+'&nuevo='+nuevos+'&anulado='+anulados+'&aprobado='+aprobados;
            
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');


        
            },
            generarReportePlanesCuotas(){
                if(this.cliente.id_cliente==0){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debe seleccionar un cliente',
                        showConfirmButton: true,
                        // timer: 1500

                    });
                }else{
                    if(this.cliente.id_plan_pago==0 && this.lista_planes_pago.length!=0){
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            title: 'Debe seleccionar un plan de pago',
                            showConfirmButton: true,
                            // timer: 1500

                        });
                    }else{
                        if(this.cliente.id_plan_pago==0 && this.lista_planes_pago.length==0){
                            Swal.fire({
                                position: 'center',
                                icon: 'warning',
                                title: 'Advertencia',
                                title: 'Este cliente no tiene plan de pagos',
                                showConfirmButton: true,
                                // timer: 1500

                            });
                        }
                        
                        else{
                            // Construye la URL con el parámetro fecha_inicio
                            const url = '/reporte_planes_pago_cuotas_cliente?id_plan_pago='+this.cliente.id_plan_pago;
            
                            // Abre una nueva pestaña o ventana con la URL
                            window.open(url, '_blank');
                        }
                    }
                    
                }
            },
            async getPlanesPagoCliente(id_cliente){
                await axios.get('/get_planes_pago_cliente?id_cliente='+id_cliente).then((response)=>{
                    this.lista_planes_pago=response.data;
                })
                .catch((error)=>{
                    console.log(error.message)
                })
            },


            async getClientes(page=1) {
                //this.preloader = true;
                try {
                    const response = await axios.get("/get_clientes_paginate", {
                        params: {
                            page,
                            criterio: this.searchCriteria,
                            buscar: this.searchQuery,
                        },
                    });

                    const data = response.data;
                    this.customers = data.data;
                    this.from = data.from;
                    this.to = data.to;
                    this.total = data.total;
                    this.currentPage = data.current_page;
                    this.totalPages = data.last_page;

                } catch (error) {
                    console.error("Error fetching customers:", error);
                } finally {
                    //this.preloader = false;
                }
            },

            getUsuarios() {
                axios.get('/get_usuarios_sin').then((response) => {
                    this.lista_usuarios = response.data;
                
                })
                .catch((error) => {
                    console.log(error.message);
                })
            },

            seleccionarCliente(item) {
                console.log(item);
                this.isVisibleCliente= false;
                this.cliente.buscar=item.nombre +' '+item.ci;
                this.cliente.id_cliente=item.id;
                
                this.getPlanesPagoCliente(this.cliente.id_cliente);
            },
            // OLD
            // reportePlanesPagoClientes(){
            //     this.getClientes();
            //     this.lista_planes_pago=[];
            //     this.cliente={
            //         id_cliente:0,
            //         idd_cliente:'',
            //         nombre:'',
            //         ci:0,
            //         lugar_expedicion:'',
            //         buscar:'',
            //         id_plan_pago:0,
            //     };
            //     this.abrirModalReportePlanesCuotas();
            // },

            reportePlanesPagoClientes(){
                this.getClientes();
                this.lista_planes_pago=[];
                this.cliente={
                    id_cliente:0,
                    idd_cliente:'',
                    nombre:'',
                    ci:0,
                    lugar_expedicion:'',
                    buscar:'',
                    id_plan_pago:0,
                };
                this.abrirModalReportePlanesCuotas();
            },
            cerrarModalReportePlanesCuota(){
                $('#modalReportePlanesCuotas').modal('hide');
            },
            abrirModalReportePlanesCuotas(){
                $('#modalReportePlanesCuotas').modal('show');
            },
            async reporteListadoClientes() {
                try {
                    // Mostrar alerta de espera
                    const loadingAlert = Swal.fire({
                        title: 'Generando reporte',
                        text: 'Por favor espere mientras se procesa el listado de clientes...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Hacer la petición con Axios
                    const response = await axios({
                        method: 'get',
                        url: '/reporte_listado_clientes',
                        responseType: 'blob' // Importante para recibir el PDF
                    });

                    // Cerrar alerta de espera
                    await loadingAlert.close();

                    // Crear URL del blob para el PDF
                    const blob = new Blob([response.data], { type: 'application/pdf' });
                    const pdfUrl = window.URL.createObjectURL(blob);
                    
                    // Abrir el PDF en una nueva pestaña
                    const newTab = window.open(pdfUrl, '_blank');
                    
                    // Si el navegador bloquea la apertura de ventanas, ofrecer descarga
                    if (!newTab || newTab.closed || typeof newTab.closed === 'undefined') {
                        const downloadLink = document.createElement('a');
                        downloadLink.href = pdfUrl;
                        downloadLink.download = 'reporte_general_cliente.pdf';
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }

                    // Liberar memoria después de 1 minuto
                    setTimeout(() => {
                        window.URL.revokeObjectURL(pdfUrl);
                    }, 60000);

                } catch (error) {
                    // Cerrar alerta de espera si está abierta
                    if (Swal.isVisible()) {
                        await Swal.close();
                    }
                    
                    // Mostrar error
                    let errorMessage = 'Ocurrió un error al generar el reporte';
                    if (error.response && error.response.status === 500) {
                        errorMessage = 'Error en el servidor al generar el PDF';
                    } else if (error.message) {
                        errorMessage += ': ' + error.message;
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error'
                    });
                    
                    console.error('Error al generar reporte:', error);
                }
            },
            mostrarToastError(mensaje) {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                this.mensajeError = mensaje;
                miToast.show();
            },
            cerrarToastError() {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                miToast.hide();
            },
        },
        mounted() {
            console.log('Component mounted.');
        }


    }

</script>

<style scoped>
    .bg-dark{
        background-color: #3a5d85 !important;
        background: #3a5d85 !important;
    }
    .bg-purple {
        background-color: #6f42c1;
    }
    .btn-outline-purple {
        color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #575757;
    }

    .btn-outline-secondary:hover small,
    .btn-outline-secondary:hover h6 {
        color: #fff;
    }


    .card {
        border-radius: 0.5rem;
        /* transition: transform 0.05s; */
    }

  
.form-control-custom option {
        margin-top: 10px; /* Ajusta el valor según tus necesidades */
        margin-bottom: 10px; /* Ajusta el valor según tus necesidades */
    }
.estado-activo {
        background-color: #4caf50;
        /* Fondo verde para indicar activo */
        color: #fff;
        /* Texto blanco para contrastar */
        padding: 10px;
    }

    .estado-inactivo {
        background-color: #f44336;
        /* Fondo rojo para indicar inactivo */
        color: #fff;
        /* Texto blanco para contrastar */
        padding: 10px;
    }

    .search-container {
  position: relative;
}

ul.list-group {
  position: absolute;
  z-index: 1;
  background-color: white;
  list-style-type: none;
  padding: 0;
  margin: 0;
  border: 1px solid #ccc;
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
  width: 100%;
}

ul.list-group li {
  padding: 8px 12px;
  cursor: pointer;
}

ul.list-group li:hover {
  background-color: #f2f2f2;
}

span.toggle-results {
  display: block;
  text-align: center;
  margin-top: 8px;
  cursor: pointer;
}

.input-group-append {
  position: absolute;
  right: 0;
  top: 0;
  height: 100%;
}



.rotate-icon {
  transform: rotate(180deg);
}

.dropdown-wrapper {
  position: relative;
}

.dropdown-wrapper .selected-item {
  height: 25px;
  border-radius: 5px;
  padding: 5px 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-wrapper .selected-item .drop-down-icon {
  transform: rotate(0deg);
  transition: all 0.5s ease;
}

.dropdown-wrapper .selected-item .drop-down-icon.dropdown {
  transform: rotate(180deg);
}

.dropdown-wrapper .dropdown-popover {
  position: absolute;
  border: 2px solid lightgray;
  top: 46;
  left: 0;
  right: 0;
  background-color: #fff;
  max-width: 100%;
  align-items: center;
  padding: 10px;
  visibility: hidden;
  transition: all 0.35s linear;
  max-height: 0px;
  overflow: hidden;
}

.dropdown-wrapper .dropdown-popover.visible {
  max-height: 450px;
  visibility: visible;
}

.dropdown-wrapper .dropdown-popover input {
  width: 100%;
  height: 30px;
  border: 2px solid lightgray;
  font-size: 18px;
  padding-left: 8px;
}

.dropdown-wrapper .dropdown-popover .options {
  width: 100%;
  padding-top: 12px;
}

.dropdown-wrapper .dropdown-popover .options ul {
  list-style: none;
  text-align: left;
  padding-left: 2px;
  max-height: 200px;
  overflow-y: scroll;
  overflow-x: hidden;
}

.dropdown-wrapper .dropdown-popover .options li {
  width: 100%;
  border-bottom: 1px solid lightgray;
  padding: 5px;
  border: 1px solid lightgray;
  background-color: #f1f1f1;
  cursor: pointer;
}

.dropdown-wrapper .dropdown-popover .options li:hover {
  background: #44536E;
  color: #fff;
  font-weight: bold;
}

.container {
  /* Estilos para el contenedor principal de la página de registro de venta */
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.total-container {
  /* Estilos para el contenedor del Total a Pagar */
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #f5f5f5;
  padding: 10px 20px;
  border-radius: 4px;
}

.total-label {
  /* Estilos para la etiqueta "Total a Pagar" */
  font-size: 18px;
  font-weight: bold;
}

.total-amount {
  /* Estilos para el monto total */
  font-size: 24px;
  color: #00a8e8;
  font-weight: bold;
}
</style>

