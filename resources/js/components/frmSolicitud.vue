<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <ListaSolicitudes
                    v-if="view == 0"
                    :solicitudes="filteredSolicitudes"
                    :pagination="pagination"
                    :codeudores-tabla="lista_codeudores_tabla"
                    :rol-usuario="rolUsuario"
                    :filtros-iniciales="{
                        fecha_inicial: fecha_inicial_buscar,
                        fecha_final: fecha_final_buscar,
                        estado: criterio_estado,
                        criterio: criterio,
                        buscar: buscar
                    }"
                    @filtrar="handleFiltros"
                    @cambiar-pagina="cambiarPagina"
                    @nueva-solicitud="abrirModalNuevo"
                    @abrir-calculadora="abrirCalculadoraCredito"
                    @anular="desactivarSolicitud"
                    @activar="activarSolicitud"
                    @editar="editarSolicitud"
                    @ver="verSolicitud"
                    @aprobar-solicitud="abrirModalSimulacionPlanPago"
                    @aprobar-reprogramacion="aprobarReprogramacion"
                    @aprobar-refinanciamiento="aprobarReprogramacion"
                    @ver-garantias="abrirModalGarantias"
                    @ver-respaldos="abrirModalRespaldos"
                    @ver-hoja-aprobacion="verHojaAprobacion"
                    @ver-hoja-solicitud="verHojaSolicitud"
                />

                <div v-if="view == 1" class="card shadow-sm">
                    <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 text-center">
                            <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                                {{
                                solicitud.accion === 0
                                ? "Agregar Nueva Solicitud"
                                : solicitud.accion === 1
                                ? "Modificar Solicitud"
                                : "Información de la Solicitud"
                                }}
                            </h5>
                        </div>
                        <button @click="cerrarModalNuevo()" type="button" class="btn-close btn-close-dark"
                            aria-label="Close"></button>
                    </div>

                    <form @submit.prevent="
                        solicitud.accion === 0
                            ? guardarSolicitud()
                            : modificarSolicitud()
                        ">
                        <div class="card-body">

                            <div class="card mb-4 border shadow-sm">
                                <div class="card-header bg-light border-bottom text-dark py-2">
                                    <h6 class="mb-0 fw-bold text-uppercase d-flex align-items-center">
                                        <i class="fas fa-user me-2"></i> Datos del Cliente
                                    </h6>
                                </div>

                                <div class="card-body p-3">
                                    
                                    <div v-if="!cliente.id_cliente" class="mb-2">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Buscar o Registrar Cliente</label>
                                        <div class="position-relative">
                                            <div class="input-group">
                                                <span class="input-group-text bg-success border-end-0">
                                                    <i class="fas fa-search text-white"></i>
                                                </span>
                                                <input 
                                                    v-model="cliente.idd_cliente" 
                                                    type="text" 
                                                    class="form-control border-start-0 text-uppercase"
                                                    placeholder="INGRESE NOMBRE O CI..."
                                                    @input="filteredItemsClienteMetodo(cliente.idd_cliente)"
                                                    :disabled="solicitud.accion === 2"
                                                    autocomplete="off"
                                                />
                                                <button 
                                                    v-if="solicitud.accion !== 2"
                                                    class="btn btn-success fw-bold px-4" 
                                                    type="button"
                                                    @click="abrirModalCliente(0)">
                                                    <i class="fas fa-plus-circle me-1"></i> NUEVO
                                                </button>
                                            </div>

                                            <div v-if="filteredItemsCliente.length > 0"
                                                class="dropdown-menu show w-100 border mt-1 shadow-sm p-0"
                                                style="max-height: 200px; overflow-y: auto; z-index: 1050;">
                                                <a v-for="clienteItem in filteredItemsCliente" :key="clienteItem.id"
                                                    class="dropdown-item py-2 px-3 border-bottom d-flex justify-content-between align-items-center"
                                                    @click="seleccionarCliente(clienteItem)"
                                                    style="cursor: pointer;">
                                                    <div>
                                                        <span class="fw-bold d-block text-uppercase small">{{ clienteItem.nombre }}</span>
                                                        <span class="text-muted small"><i class="fas fa-briefcase me-1"></i> {{ clienteItem.actividad }}</span>
                                                    </div>
                                                    <span class="badge bg-light text-dark border">{{ clienteItem.ci }} {{ clienteItem.lugar_expedicion }}</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-text small text-muted mt-1 ms-1">
                                            <i class="fas fa-info-circle me-1"></i> Busque por nombre/CI o haga clic en "Nuevo" para registrar.
                                        </div>
                                    </div>

                                    <div v-else>
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <div>
                                                <span class="badge bg-secondary me-2">ID: {{ cliente.id_cliente }}</span>
                                                <span class="fw-bold text-uppercase text-dark">{{ cliente.nombre }}</span>
                                            </div>
                                            
                                            <div v-if="solicitud.accion !== 2">
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-outline-primary me-1" 
                                                    @click="abrirModalCliente(1)"
                                                    title="Modificar datos del cliente">
                                                    <i class="fas fa-edit me-1"></i> Editar
                                                </button>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    @click="limpiarSeleccionCliente()"
                                                    title="Deseleccionar cliente">
                                                    <i class="fas fa-times me-1"></i> Quitar
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row g-0 border rounded bg-light">
                                            <div class="col-md-2 d-flex justify-content-center align-items-center bg-white border-end p-2">
                                                <img :src="cliente.imagen_validate ? cliente.imagen : '/img/cliente/default.png'"
                                                    class="img-thumbnail" 
                                                    style="height: 85px; width: 85px; object-fit: cover;"
                                                    alt="Foto Cliente">
                                            </div>

                                            <div class="col-md-3 border-end">
                                                <div class="p-2">
                                                    <label class="d-block text-muted small fw-bold mb-0">DOCUMENTO IDENTIDAD</label>
                                                    <span class="fw-bold text-dark">{{ cliente.ci }} {{ cliente.lugar_expedicion }}</span>
                                                    
                                                    <label class="d-block text-muted small fw-bold mb-0 mt-2">SEXO</label>
                                                    <span class="text-dark small text-uppercase">{{ cliente.sexo }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-4 border-end">
                                                <div class="p-2">
                                                    <label class="d-block text-muted small fw-bold mb-0">ACTIVIDAD ECONÓMICA</label>
                                                    <span class="text-dark small text-uppercase text-truncate d-block">{{ cliente.actividad }}</span>
                                                    
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <label class="d-block text-muted small fw-bold mb-0">EST. CIVIL</label>
                                                            <span class="text-dark small text-uppercase">{{ cliente.estado_civil }}</span>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="d-block text-muted small fw-bold mb-0">VIVIENDA</label>
                                                            <span class="text-dark small text-uppercase">{{ cliente.vivienda }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 bg-white">
                                                <div class="p-2 h-100 d-flex flex-column justify-content-center">
                                                    <label class="d-block text-muted small fw-bold mb-1">INGRESO MENSUAL</label>
                                                    <div class="fs-5 fw-bold text-success border-start border-4 border-success ps-2">
                                                        Bs. {{ cliente.ingreso_mensual?.toLocaleString('es-BO') || '0' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modalClienteOverlay" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:90%;">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-body p-0">
                                            <ClienteForm 
                                                v-if="mostrarModalCliente"
                                                :cliente-id="clienteFormId"
                                                :accion="clienteFormAccion"
                                                @cerrar="cerrarModalCliente"
                                                @guardado="alGuardarCliente"
                                                :cliente-data="selectedCustomerData"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card mb-4 border-success shadow-sm">
                                <div class="card-header bg-white border-bottom" id="headerCodeudor">
                                    <div
                                        class="d-flex justify-content-between align-items-center border-bottom pb-1 border-2 border-dark">
                                        <h6 class="mb-0 fw-bold text-uppercase" id="tituloCabecera">
                                            Seleccione Codeudores/Garantes
                                        </h6>
                                        <div class="form-check form-switch" v-if="solicitud.accion !== 2">
                                            <input :disabled="solicitud.accion === 2" v-model="sinCodeudor"
                                                class="form-check-input fs-6" type="checkbox" id="switchSinCodeudor"
                                                @change="seleccionSinCodeudor(sinCodeudor)">
                                            <label class="form-check-label small fs-6" for="switchSinCodeudor">
                                                Sin Codeudor/Garante
                                            </label>
                                        </div>
                                    </div>
                                </div>
                             
                                <div class="card-body" v-show="sinCodeudor" id="mensajeSinCodeudor">
                                    <div class="alert alert-light border mb-0 text-danger fw-bold">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Se ha seleccionado la opción "Sin Codeudor/Garante" para este préstamo.
                                    </div>
                                </div>

                                <!-- <div class="card-body" v-show="!sinCodeudor">
                               
                                    <div v-for="(
item, index
                                            ) in lista_codeudores" :key="index" class="row g-3 mb-3">
                                        <div class="col-md-4 position-relative">
                                            <div class="input-group">
                                                <input v-model="item.select_codeudor
                                                    .codeudor
                                                    .idd_codeudor
                                                    " type="text" class="form-control text-uppercase"
                                                    placeholder="Buscar codeudor..." :disabled="solicitud.accion === 2
                                                        " @input="
                                                        filteredItemsCodeudorMetodo(
                                                            item.select_codeudor
                                                                .codeudor
                                                                .idd_codeudor,
                                                            index
                                                        )
                                                        " />
                                                <button v-if="
                                                    solicitud.accion !== 2
                                                " @click="addCodeudor" type="button"
                                                    class="btn btn-success me-1 rounded-circle ms-2"
                                                    data-bs-toggle="tooltip" title="Agregar codeudor"
                                                    style="width: 50px; height:50px; padding:0px;">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button v-if="
                                                    solicitud.accion !==
                                                    2 &&
                                                    lista_codeudores.length >
                                                    1
                                                " @click="
                                                deleteCodeudor(index)
                                                " type="button" class="btn btn-danger rounded-circle"
                                                    data-bs-toggle="tooltip" title="Eliminar codeudor"
                                                    style="width: 50px; height:50px; padding:0px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <div v-if="
                                                item.select_codeudor
                                                    .codeudor
                                                    .filteredItemsCodeudorAux
                                                    .length > 0
                                            " class="dropdown-menu show w-100" style="
                                                        max-height: 200px;
                                                        overflow-y: auto;
                                                        cursor: pointer;
                                                    ">
                                                <a v-for="codeudorItem in item
                                                    .select_codeudor
                                                    .codeudor
                                                    .filteredItemsCodeudorAux" :key="codeudorItem.id"
                                                    class="dropdown-item text-uppercase" @click="
                                                        seleccionarCodeudor(
                                                            codeudorItem,
                                                            index
                                                        )
                                                        ">{{
                                                    codeudorItem.nombre
                                                    }}
                                                    (CI:
                                                    {{ codeudorItem.ci }})</a>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <input v-model="item.select_codeudor
                                                .codeudor.ci
                                                " type="text" class="form-control" disabled />
                                        </div>
                                        <div class="col-md-5">
                                            <textarea v-model="item.select_codeudor.codeudor.actividad"
                                                class="form-control" disabled rows="2">
                            </textarea>
                                        </div>
                                    </div>

                                </div> -->

                                <div class="card-body" v-show="!sinCodeudor">
                                    <div v-for="(item, index) in lista_codeudores" :key="index" class="row g-3 mb-3 align-items-center">
                                        
                                        <div class="col-md-4 position-relative">
                                            <label class="form-label small fw-bold text-muted mb-1" v-if="index === 0">BUSCAR CODEUDOR</label>
                                            <div class="input-group">
                                                <input 
                                                    v-model="item.select_codeudor.codeudor.idd_codeudor" 
                                                    type="text" 
                                                    class="form-control text-uppercase shadow-sm"
                                                    placeholder="Escriba nombre o CI..." 
                                                    :disabled="solicitud.accion === 2" 
                                                    @input="filteredItemsCodeudorMetodo(item.select_codeudor.codeudor.idd_codeudor, index)" 
                                                />
                                                
                                                <button v-if="solicitud.accion !== 2" 
                                                    @click="addCodeudor" type="button"
                                                    class="btn btn-success ms-1 shadow-sm"
                                                    data-bs-toggle="tooltip" title="Agregar otro codeudor">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button v-if="solicitud.accion !== 2 && lista_codeudores.length > 1" 
                                                    @click="deleteCodeudor(index)" type="button" 
                                                    class="btn btn-outline-danger ms-1 shadow-sm"
                                                    data-bs-toggle="tooltip" title="Quitar este codeudor">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <div v-if="item.select_codeudor.codeudor.filteredItemsCodeudorAux.length > 0" 
                                                class="dropdown-menu show w-100 border-0 shadow mt-1" 
                                                style="max-height: 250px; overflow-y: auto; z-index: 1050;">
                                                <a v-for="codeudorItem in item.select_codeudor.codeudor.filteredItemsCodeudorAux" 
                                                :key="codeudorItem.id"
                                                class="dropdown-item py-2 px-3 border-bottom d-flex justify-content-between align-items-center" 
                                                @click="seleccionarCodeudor(codeudorItem, index)"
                                                style="cursor: pointer;">
                                                    <div>
                                                        <span class="fw-bold d-block text-uppercase small">{{ codeudorItem.nombre }}</span>
                                                        <span class="text-muted small"><i class="fas fa-briefcase me-1"></i> {{ codeudorItem.actividad }}</span>
                                                    </div>
                                                    <span class="badge bg-light text-dark border">{{ codeudorItem.ci }}</span>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold text-muted mb-1" v-if="index === 0">INFORMACIÓN DETALLADA</label>
                                            
                                            <div v-if="item.select_codeudor.codeudor.id_codeudor !== 0" class="row g-0 border rounded bg-light shadow-sm position-relative overflow-hidden">
                                                
                                                <div class="col-md-2 d-flex justify-content-center align-items-center bg-white border-end p-2">
                                                    <img :src="item.select_codeudor.codeudor.imagen ? '/img/codeudor/' + item.select_codeudor.codeudor.imagen : '/img/codeudor/default.png'"
                                                        class="img-thumbnail rounded-circle" 
                                                        style="height: 70px; width: 70px; object-fit: cover;"
                                                        alt="Foto">
                                                </div>

                                                <div class="col-md-3 border-end">
                                                    <div class="p-2 d-flex flex-column justify-content-center h-100">
                                                        <label class="d-block text-muted small fw-bold mb-0" style="font-size: 0.65rem;">DOCUMENTO IDENTIDAD</label>
                                                        <span class="fw-bold text-dark text-uppercase" style="font-size: 0.85rem;">
                                                            {{ item.select_codeudor.codeudor.ci }}
                                                        </span>
                                                        
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <div>
                                                                <label class="d-block text-muted small fw-bold mb-0" style="font-size: 0.65rem;">SEXO</label>
                                                                <span class="text-dark small text-uppercase">{{ item.select_codeudor.codeudor.sexo || '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 border-end">
                                                    <div class="p-2 d-flex flex-column justify-content-center h-100">
                                                        <label class="d-block text-muted small fw-bold mb-0" style="font-size: 0.65rem;">ACTIVIDAD ECONÓMICA</label>
                                                        <span class="text-dark small text-uppercase text-truncate d-block mb-1" :title="item.select_codeudor.codeudor.actividad">
                                                            {{ item.select_codeudor.codeudor.actividad || 'No registrada' }}
                                                        </span>
                                                        
                                                        <div class="row g-0">
                                                            <div class="col-6">
                                                                <label class="d-block text-muted small fw-bold mb-0" style="font-size: 0.65rem;">EST. CIVIL</label>
                                                                <span class="text-dark small text-uppercase">{{ item.select_codeudor.codeudor.estado_civil || '-' }}</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="d-block text-muted small fw-bold mb-0" style="font-size: 0.65rem;">VIVIENDA</label>
                                                                <span class="text-dark small text-uppercase">{{ item.select_codeudor.codeudor.vivienda || '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 bg-white">
                                                    <div class="p-2 h-100 d-flex flex-column justify-content-center">
                                                        <label class="d-block text-muted small fw-bold mb-1" style="font-size: 0.65rem;">INGRESO MENSUAL</label>
                                                        <div class="fs-6 fw-bold text-success border-start border-3 border-success ps-2">
                                                            Bs. {{ item.select_codeudor.codeudor.ingreso_mensual?.toLocaleString('es-BO') || '0' }}
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div v-else class="border rounded bg-light p-3 text-center text-muted border-dashed">
                                                <small><i class="fas fa-arrow-left me-2"></i> Busque y seleccione un codeudor para ver su información.</small>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div class="card mb-4 border-success shadow-sm">

                                <div class="card-header bg-white text-dark">
                                    <h6
                                        class="mb-0 text-text-dark fw-bold border-bottom pb-2 border-2 border-dark text-uppercase">
                                        COMPLETE LA INFORMACIÓN DE LA SOLICITUD
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Importe Solicitud</label>
                                                <input v-model="solicitud.importe_solicitud
                                                    " type="number" class="form-control" :disabled="solicitud.accion === 2
                                                    " :class="{
                                                            'is-invalid':
                                                                !solicitud.importe_solicitud &&
                                                                solicitud.enviado,
                                                        }" required />
                                                <div class="invalid-feedback">
                                                    Ingrese un importe válido
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Moneda</label>
                                                    <select v-model="solicitud.moneda
                                                        " class="form-select" :disabled="solicitud.accion ===
                                                        2
                                                        " :class="{
                                                                'is-invalid':
                                                                    !solicitud.moneda &&
                                                                    solicitud.enviado,
                                                            }" required>
                                                        <option value="" disabled>
                                                            Seleccione moneda
                                                        </option>
                                                        <option v-for="item in lista_monedas" :key="item.nombre"
                                                            :value="item.nombre">
                                                            {{ item.nombre }}
                                                        </option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Seleccione una moneda
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Tipo de Cuota</label>
                                                    <select v-model="tipo_tasa" class="form-select" :disabled="solicitud.accion ===
                                                        2
                                                        " @change="
                                                            " :class="{
                                                                'is-invalid':
                                                                    !tipo_tasa &&
                                                                    solicitud.enviado,
                                                            }" required>
                                                        <option value="" disabled>
                                                            Seleccione un tipo
                                                        </option>
                                                        <option value="fija">
                                                            Cuota Fija
                                                        </option>
                                                        <option value="amortizable">
                                                            Cuota Variable
                                                        </option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Seleccione tipo de tasa
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Forma de Pago</label>
                                                    <select v-model="solicitud.lapso_capital
                                                        " class="form-select" :disabled="solicitud.accion ===
                                                        2
                                                        " @change="actualizarCuotasSolicitud()
                                                                " :class="{
                                                                        'is-invalid':
                                                                            !solicitud.lapso_capital &&
                                                                            solicitud.enviado,
                                                                    }" required>
                                                        <option value="" disabled>
                                                            Seleccione
                                                        </option>
                                                        <option v-for="item in lapso_capitales" :key="item.nombre"
                                                            :value="item.nombre">
                                                            {{ item.nombre }}
                                                        </option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Seleccione un lapso
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-2">
                                                <div class="col-md-3"
                                                    v-if="solicitud.accion == 1 || solicitud.accion == 0">

                                                    <label class="form-label fw-semibold">Plazo (Meses)</label>
                                                    <input v-model="solicitud.plazo" type="number"
                                                        @input="actualizarCuotasSolicitud()" class="form-control"
                                                        required />


                                                </div>
                                                <div
                                                    :class="solicitud.accion == 1 || solicitud.accion == 0 ? 'col-md-3' : 'col-md-6'">
                                                    <label class="form-label fw-semibold">Plazo Cuotas</label>
                                                    <input v-model="solicitud.nro_cuotas
                                                        " type="number" class="form-control" :disabled="solicitud.accion ===
                                                        2
                                                        " :class="{
                                                                'is-invalid':
                                                                    !solicitud.nro_cuotas &&
                                                                    solicitud.enviado,
                                                            }" required />
                                                    <div class="invalid-feedback">
                                                        Ingrese número de cuotas
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Tasa %</label>
                                                    <input v-model="solicitud.tasa" type="number" step="0.01" min="0"
                                                        class="form-control" :disabled="solicitud.accion ===
                                                            2
                                                            " :class="{
                                                            'is-invalid':
                                                                !solicitud.tasa &&
                                                                solicitud.enviado,
                                                        }" required />
                                                    <div class="invalid-feedback">
                                                        Ingrese tasa
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Fecha Desembolso</label>

                                                    <input v-model="solicitud.fecha_desembolso
                                                        " type="date" class="form-control" :disabled="solicitud.accion ===
                                                        2
                                                        " @input="
                                                                " />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Fecha Primera
                                                        Cuota</label>
                                                    <input v-model="solicitud.fecha_primera_cuota
                                                        " type="date" class="form-control"
                                                        :disabled="(solicitud.accion === 2)?true: false" />
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label fw-semibold">Destino Préstamo</label>
                                                <input v-model="solicitud.destino_prestamo
                                                    " type="text" class="form-control" :disabled="solicitud.accion === 2
                                                    " :class="{
                                                            'is-invalid':
                                                                !solicitud.destino_prestamo &&
                                                                solicitud.enviado,
                                                        }" required />
                                                <div class="invalid-feedback">
                                                    Ingrese destino del préstamo
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-2">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Tipo Desembolso</label>
                                                    <select v-model="solicitud.tipo_desembolso
                                                        " class="form-select" :disabled="solicitud.accion ===
                                                        2
                                                        " :class="{
                                                                'is-invalid':
                                                                    !solicitud.tipo_desembolso &&
                                                                    solicitud.enviado,
                                                            }" required>
                                                        <option value="" disabled>
                                                            Seleccione
                                                        </option>
                                                        <option v-for="item in tipos_desembolsos" :key="item.nombre"
                                                            :value="item.nombre">
                                                            {{ item.nombre }}
                                                        </option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Seleccione tipo de
                                                        desembolso
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Tipo Garantía</label>
                                                    <select v-model="solicitud.tipo_garantia
                                                        " class="form-select" :disabled="solicitud.accion ===
                                                        2
                                                        " :class="{
                                                                'is-invalid':
                                                                    !solicitud.tipo_garantia &&
                                                                    solicitud.enviado,
                                                            }" required>
                                                        <option value="" disabled>
                                                            Seleccione
                                                        </option>
                                                        <option v-for="item in tipos_garantias" :key="item.nombre"
                                                            :value="item.nombre">
                                                            {{ item.nombre }}
                                                        </option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Seleccione tipo de
                                                        garantía
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                      
                            <div v-if="['Empeño de electrodoméstico u Otros', 'Custodia de Papeles de Moto', 'Prendario o Quirografaria', 'Empeño Joyas (oro)', 'Custodia Inmueble o Lote terreno', 'Custodia de Vehículo Automovil'].includes(solicitud.tipo_garantia)"
                                class="card mb-4 shadow-sm">
                                <div class="card-header bg-white text-dark">
                                    <h6
                                        class="mb-0 text-text-dark fw-bold border-bottom pb-2 border-2 border-dark text-uppercase">
                                        INGRESE LA INFORMACIÓN DE LAS GARANTIAS
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" style="border: none; border-collapse: collapse;">
                                            <thead>
                                                <tr style="border: none;">
                                                    <th class="fw-bold"
                                                        style="border: none; padding-left: 0; margin-left: 0;">
                                                        Descripción
                                                    </th>
                                                    <th v-if="solicitud.accion !== 2" class="fw-bold text-center"
                                                        style="border: none;">
                                                        Opciones
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in lista_garantias" :key="index"
                                                    style="border: none;">
                                                    <td style="border: none; padding-left: 0; margin-left: 0;">
                                                        <input v-model="item.descripcion" type="text"
                                                            class="form-control" :disabled="solicitud.accion === 2"
                                                            :class="{
                                                                'is-invalid': !item.descripcion && solicitud.enviado
                                                            }" required />
                                                        <div class="invalid-feedback">
                                                            Ingrese una descripción
                                                        </div>
                                                    </td>
                                                    <td v-if="solicitud.accion !== 2" class="text-center"
                                                        style="border: none;">
                                                        <button @click="agregarGarantia" type="button"
                                                            class="btn btn-success btn-sm me-1 rounded-circle"
                                                            data-bs-toggle="tooltip" title="Agregar garantía">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button v-if="lista_garantias.length > 1"
                                                            @click="quitarGarantia(index)" type="button"
                                                            class="btn btn-danger btn-sm rounded-circle"
                                                            data-bs-toggle="tooltip" title="Eliminar garantía">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                
                            <div v-if="solicitud.observacion != ''" class="card mb-4 border-success shadow-sm">

                                <div class="card-header bg-white text-dark">
                                    <h6
                                        class="mb-0 text-text-dark fw-bold border-bottom pb-2 border-2 border-dark text-uppercase">
                                        OBSERVACIONES
                                        <i class="fas fa-exclamation-triangle ms-2 fs-4" style="color:red"></i>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <textarea class="form-control" v-model="solicitud.observacion" name="" id=""
                                                rows="3" disabled></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary me-2" @click="cerrarModalNuevo"
                                data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cerrar
                            </button>

                            <button v-if="solicitud.accion === 0" type="submit" class="btn btn-success"
                                :disabled="guardando_solicitud">
                                <i v-if="!guardando_solicitud" class="fas fa-save"></i>
                                <i v-if="guardando_solicitud" class="fas fa-spinner fa-spin"></i>
                                {{ guardando_solicitud ? 'Guardando...' : 'Guardar' }}
                            </button>

                            <button v-if="solicitud.accion === 1" type="submit" class="btn btn-success"
                                :disabled="guardando_solicitud">
                                <i v-if="!guardando_solicitud" class="fas fa-save"></i>
                                <i v-if="guardando_solicitud" class="fas fa-spinner fa-spin"></i>
                                {{ guardando_solicitud ? 'Modificando...' : 'Modificar' }}
                            </button>

                            <button
                                v-if="solicitud.estado != 2 && solicitud.accion === 2 && rolUsuario === 'administrador' && solicitud.observacion == ''"
                                type="button" class="btn btn-info" @click="abrirModalObservacion('guardar')">
                                <i class="fas fa-eye"></i> Observar
                            </button>
                            <button
                                v-if="solicitud.accion === 2 && rolUsuario === 'administrador' && solicitud.observacion != ''"
                                type="button" class="btn btn-info me-2" @click="abrirModalObservacion('modificar')">
                                <i class="fas fa-pencil-alt"></i> Modificar Observación
                            </button>
                            <button @click="eliminarObservacion()"
                                v-if="solicitud.accion === 2 && rolUsuario === 'administrador' && solicitud.observacion != ''"
                                type="button" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Eliminar Observación
                            </button>
                        </div>
                    </form>
                </div>

                <AprobacionCredito 
                    v-if="view == 2"
                    :solicitud="solicitud"
                    :cliente="cliente_simulacion"
                    :cuotas="lista_cuotas"
                    :procesando="aprobando_solicitud"
                    @aprobar="aprobarSolicitud(solicitud.id_solicitud)"
                    @generar-pdf="listaCuotasPdf(solicitud.id_solicitud)"
                    @cerrar="cerrarModalSimulacionPlanPago"
                />

                <!-- SECCIÓN DE APROBACIÓN DE REPROGRAMACIÓN -->
                <div v-if="view === 'aprobar_reprogramacion'" class="card shadow border-0">
                    
                    <!-- Encabezado Distintivo -->
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-uppercase">
                            <i class="fas fa-check-double me-2"></i> Auditoría y Aprobación de {{ solicitud_editar.tipo_solicitud }}
                        </h5>
                        <button @click="cerrarModalAprobacion()" type="button" class="btn-close btn-close-white"></button>
                    </div>

                    <div class="card-body bg-light">
                        
                        <!-- 1. Encabezado del Cliente -->
                        <div class="card mb-4 shadow-sm border-start border-2 border-primary">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ solicitud_editar.cliente }}</h5>
                                        <span class="text-muted small">
                                            <i class="fas fa-id-card me-1"></i> {{ solicitud_editar.ci }} {{ solicitud_editar.lugar_expedicion }}
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-briefcase me-1"></i> {{ solicitud_editar.actividad }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge bg-warning text-dark p-2">Solicitud #{{ solicitud_editar.id }}</div>
                                        <div class="small text-muted mt-1">Fecha Solicitud: {{ formatFecha(solicitud_editar.fecha) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 border-2">
                            <div class="col-lg-5">
                                <div v-if="orden_pago_actual" class="card mb-3 border-primary shadow-sm">
                                    <div class="card-header bg-primary text-white py-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold small text-uppercase">
                                                <i class="fas fa-file-invoice-dollar me-2"></i> Orden de Cobro Pendiente
                                            </span>
                                            <!-- <span v-if="orden_pago_actual.estado == 0" class="badge bg-light text-dark small">No Disponible</span> -->
                                            <span v-if="orden_pago_actual.estado == 1" class="badge bg-warning text-dark small">Por Pagar</span>
                                            <span v-else-if="orden_pago_actual.estado == 2" class="badge bg-success small">Pagado</span>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-borderless mb-0 small">
                                            <tbody>
                                                <tr class="border-bottom">
                                                    <td class="ps-3 text-muted">Interés Calculado:</td>
                                                    <td class="text-end pe-3 fw-bold">{{ formatNumero(orden_pago_actual.monto_interes_calculado) }}</td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="ps-3 text-muted">Mora Calculada:</td>
                                                    <td class="text-end pe-3 fw-bold text-danger">{{ formatNumero(orden_pago_actual.monto_mora_calculado) }}</td>
                                                </tr>

                                                <tr v-if="orden_pago_actual.se_condono_interes" class="bg-light text-success fst-italic">
                                                    <td class="ps-3"><i class="fas fa-arrow-down me-1"></i> Desc. Interés:</td>
                                                    <td class="text-end pe-3">-{{ formatNumero(orden_pago_actual.monto_condonado_interes) }}</td>
                                                </tr>
                                                <tr v-if="orden_pago_actual.se_condono_mora" class="bg-light text-success fst-italic">
                                                    <td class="ps-3"><i class="fas fa-arrow-down me-1"></i> Desc. Mora:</td>
                                                    <td class="text-end pe-3">-{{ formatNumero(orden_pago_actual.monto_condonado_mora) }}</td>
                                                </tr>

                                                <tr class="bg-primary bg-opacity-10 border-top border-primary">
                                                    <td class="ps-3 py-2 fw-bold text-primary text-uppercase">Total a Cobrar en Caja:</td>
                                                    <td class="text-end pe-3 py-2 fs-6 fw-bold text-primary">
                                                        {{ formatNumero(orden_pago_actual.total_a_pagar) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <div v-if="orden_pago_actual.motivo_condonacion" class="p-2 bg-light border-top text-muted small">
                                            <strong><i class="fas fa-comment-alt me-1"></i> Motivo Condonación:</strong> 
                                            {{ orden_pago_actual.motivo_condonacion }}
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-secondary shadow-sm">
                                    <div class="card-header bg-dark text-white fw-bold">
                                        <i class="fas fa-history me-2"></i> Crédito Original (Antecedente)
                                    </div>
                                    <div class="card-body">
                                        <!-- Datos Resumidos -->
                                        <ul class="list-group list-group-flush small mb-3">
                                            <li class="list-group-item d-flex justify-content-between bg-light">
                                                <span>Monto Otorgado:</span>
                                                <strong class="text-dark">{{ original_data.moneda }} {{ original_data.monto_original }}</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Forma de Pago:</span>
                                                <span>{{ original_data.nro_cuotas }} cuotas ({{ original_data.lapso }})</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Tasa Interés:</span>
                                                <span>{{ original_data.tasa }}%</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between bg-warning bg-opacity-10 border-warning">
                                                <span class="fw-bold text-dark">Saldo Capital Actual:</span>
                                                <strong class="text-danger fs-6">{{ original_data.moneda }} {{ original_data.saldo_actual }}</strong>
                                            </li>
                                        </ul>

                                        <!-- Botón Desplegable Cuotas -->
                                        <button class="btn btn-outline-secondary btn-sm w-100 mb-2" type="button" 
                                                @click="ver_cuotas_originales = !ver_cuotas_originales">
                                            <i :class="ver_cuotas_originales ? 'fas fa-chevron-up' : 'fas fa-list'"></i>
                                            {{ ver_cuotas_originales ? 'Ocultar Historial de Pagos' : 'Ver Historial de Pagos' }}
                                        </button>

                                        <!-- Tabla Desplegable (Scrollable) -->
                                        <div v-if="ver_cuotas_originales" class="border rounded bg-white table-responsive" style="max-height: 300px;">
                                            <table class="table table-sm table-hover mb-0" style="font-size: 0.65rem;">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th style="font-size: 0.65rem;">#</th>
                                                        <th style="font-size: 0.65rem;">Fecha</th>
                                                        <th style="font-size: 0.65rem;">Capital</th>
                                                        <th style="font-size: 0.65rem;">Interes</th>
                                                        <th style="font-size: 0.65rem;">Saldo Capital</th>
                                                        <th style="font-size: 0.65rem;" class="text-end">Total</th>
                                                        <th style="font-size: 0.65rem;" class="text-center">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="c in original_data.lista_cuotas" :key="c.id">
                                                        <td>{{ c.numero }}</td>
                                                        <td>{{ formatFecha(c.fecha) }}</td>
                                                        <td class="text-end">{{ c.capital }}</td>
                                                        <td class="text-end">{{ c.interes }}</td>
                                                        <td class="text-end">{{ c.saldo_capital }}</td>
                                                        <td class="text-end">{{ c.total }}</td>
                                                        <td class="text-center">
                                                            <span class="badge rounded" :class="getEstadoCuota(c).clase" style="font-size: 0.6rem;">
                                                                {{ getEstadoCuota(c).texto }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7">

                                <div class="card border-warning shadow-sm mb-4">
                                    <div class="card-header bg-warning bg-opacity-25 text-dark fw-bold d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-search me-2"></i> Observaciones de Auditoría
                                        </span>
                                        <span v-if="solicitud_editar.observacion" class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Observado
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-9">
                                                <div v-if="solicitud_editar.observacion"
                                                    class="text-dark mb-0">
                                                    <i class="fas fa-comment-dots me-2 text-muted"></i>
                                                    {{ solicitud_editar.observacion }}
                                                </div>
                                                <div v-else class="text-muted fst-italic p-2">
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    Sin observaciones registradas. La solicitud está limpia para aprobación.
                                                </div>
                                            </div>
            
                                            <div class="col-md-3 text-end" v-if="rolUsuario === 'administrador'">
                                                <button v-if="!solicitud_editar.observacion"
                                                    @click="abrirObservacionRepro('guardar')"
                                                    class="btn btn-outline-danger fw-bold btn-sm">
                                                    <i class="fas fa-plus-circle me-1"></i> Observar Solicitud
                                                </button>
            
                                                <div v-else class="d-grid gap-2">
                                                    <button @click="abrirObservacionRepro('modificar')"
                                                        class="btn btn-warning btn-sm text-dark">
                                                        <i class="fas fa-pencil-alt me-1"></i> Modificar Observación
                                                    </button>
                                                    <button @click="eliminarObservacionRepro()"
                                                        class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash-alt me-1"></i> Levantar Observación
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-success shadow-sm">
                                    <div class="card-header bg-success text-white fw-bold small py-1 d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-edit me-1"></i> Propuesta ({{ solicitud_editar.tipo_solicitud }})</span>
                                        <span class="badge bg-white text-success">Editable</span>
                                    </div>
                                    <div class="card-body p-3">
                                        
                                        <form @submit.prevent="simularNuevaTabla">
                                            <div class="row g-2 mb-3">
                                                
                                                <!-- BLOQUE DE MONTOS DINÁMICO -->
                                                <div class="col-md-12">
                                                    
                                                    <!-- CASO A: REFINANCIAMIENTO (Muestra desglose) -->
                                                    <div v-if="solicitud_editar.tipo_solicitud == 'Refinanciamiento'" 
                                                        class="alert alert-warning p-2 mb-2 border-warning">
                                                        
                                                        <h6 class="fw-bold text-dark border-bottom border-dark pb-1 mb-2 small">
                                                            <i class="fas fa-coins me-1"></i> Desglose de Operación
                                                        </h6>
                                                        
                                                        <div class="row g-1 small">
                                                            <div class="col-8 text-muted">Saldo Deuda Anterior:</div>
                                                            <div class="col-4 text-end fw-bold">
                                                                {{ formatNumero(solicitud_editar.importe_solicitud - (solicitud_editar.monto_refinanciamiento || 0)) }}
                                                            </div>
                                                            
                                                            <div class="col-8 text-dark fw-bold">
                                                                <i class="fas fa-plus-circle me-1"></i> Capital Adicional:
                                                            </div>
                                                            <div class="col-4 text-end fw-bold text-dark fw-bold">
                                                                {{ formatNumero(solicitud_editar.monto_refinanciamiento) }}
                                                            </div>

                                                            <div class="col-8 text-dark fw-bold pt-1" style="font-size:0.9rem">NUEVO MONTO TOTAL:</div>
                                                            <div class="col-4 text-end fw-bold text-dark pt-1" style="font-size:0.9rem">
                                                                {{ solicitud_editar.moneda }} {{ formatNumero(solicitud_editar.importe_solicitud) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- CASO B: REPROGRAMACIÓN (Solo total) -->
                                                    <div v-else>
                                                        <label class="form-label small fw-bold text-success mb-0">Monto a Reprogramar (Capital + Int.)</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-success text-white border-success">{{ solicitud_editar.moneda }}</span>
                                                            <input type="text" class="form-control fw-bold text-success bg-white" 
                                                                :value="formatNumero(solicitud_editar.importe_solicitud)" readonly disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- FIN BLOQUE MONTOS -->

                                                <!-- Frecuencia -->
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-0">Frecuencia</label>
                                                    <select class="form-select form-select-sm bg-light border-success" v-model="solicitud_editar.lapso_capital">
                                                        <option value="Mensual">Mensual</option>
                                                        <option value="Quincenal">Quincenal</option>
                                                        <option value="Semanal">Semanal</option>
                                                        <option value="Diario">Diario</option>
                                                    </select>
                                                </div>
                                                <!-- Plazo -->
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-0">Plazo (Meses)</label>
                                                    <input type="number" class="form-control form-control-sm bg-light border-success" v-model.number="solicitud_editar.plazo_meses" min="1">
                                                </div>
                                                <!-- Nro Cuotas -->
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-0">Nro. Cuotas</label>
                                                    <input type="number" class="form-control form-control-sm bg-secondary text-dark" v-model="solicitud_editar.nro_cuotas" disabled>
                                                </div>

                                                <!-- Fechas -->
                                                <div class="col-md-6">
                                                    <label class="form-label small mb-0">Fecha Aprobación</label>
                                                    <input type="date" class="form-control form-control-sm" v-model="solicitud_editar.fecha_desembolso">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small mb-0">Fecha 1ra Cuota</label>
                                                    <input type="date" class="form-control form-control-sm" v-model="solicitud_editar.fecha_primera_cuota">
                                                </div>
                                            </div>

                                            <!-- Botón Simular -->
                                            <div class="d-grid mb-3">
                                                <button type="submit" class="btn btn-success btn-sm fw-bold shadow-sm">
                                                    <i class="fas fa-sync-alt me-1"></i> Actualizar Proyección de Pagos
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Tabla Nueva (Simulada) -->
                                        <div class="table-responsive border rounded bg-white">
                                            <table class="table table-sm table-hover mb-0 small" style="font-size: 0.75rem;">
                                                <thead class="table-light text-center">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Fecha</th>
                                                        <th>Capital</th>
                                                        <th>Interés</th>
                                                        <th>Saldo Capital</th>
                                                        <th class="fw-bold">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="c in lista_cuotas_simuladas" :key="c.nro">
                                                        <td class="text-center fw-bold">{{ c.nro }}</td>
                                                        <td class="text-center">{{ formatFecha(c.fecha) }}</td>
                                                        <td class="text-end">{{ formatNumero(c.capital) }}</td>
                                                        <td class="text-end">{{ formatNumero(c.interes) }}</td>
                                                        <td class="text-end">{{ formatNumero(c.saldo_capital) }}</td>
                                                        <td class="text-end fw-bold text-dark">{{ formatNumero(c.total_cuota) }}</td>
                                                    </tr>
                                                    <tr v-if="lista_cuotas_simuladas.length === 0">
                                                        <td colspan="5" class="text-center text-muted p-4 fst-italic">
                                                            Haga clic en "Actualizar Proyección" para ver el plan.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Footer de Acciones -->
                    <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary px-4" @click="cerrarModalAprobacion()">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-success px-4 fw-bold" @click="aprobarDefinitivamente()">
                            <i class="fas fa-check-circle me-2"></i> APROBAR {{ solicitud_editar.tipo_solicitud }}
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <ModalObservacion 
            ref="modalObservacionRef" 
            @guardado-exito="onObservacionGuardada"
        />
        <ModalGarantias ref="modalGarantiasRef" />
        <ModalRespaldos ref="modalRespaldosRef" />
        <ModalCalculadoraCredito ref="modalCalculadoraRef" />

        

    </main>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";
import debounce from "lodash/debounce";
import axios from "axios";
import ListaSolicitudes from './Solicitud/ListaSolicitudes.vue';
import AprobacionCredito from './Solicitud/AprobacionCredito.vue';
import ModalObservacion from './Solicitud/ModalObservacion.vue';
import ModalGarantias from './Solicitud/ModalGarantias.vue';
import ModalRespaldos from './Solicitud/ModalRespaldos.vue';
import ModalCalculadoraCredito from './Solicitud/ModalCalculadoraCredito.vue';
import ClienteForm from './Cliente/ClienteForm.vue'; 



export default {
    components: {
        ListaSolicitudes,
        AprobacionCredito,
        ModalObservacion,
        ModalGarantias,
        ModalRespaldos,
        ModalCalculadoraCredito,
        ClienteForm
    },
    props: {
        rolUsuario: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            mostrarModalCliente: false, // Controla si se renderiza el componente
            clienteFormAccion: 0,       // 0: Nuevo, 1: Editar
            clienteFormId: 0,
            orden_pago_actual: null,
            solicitud_editar: {},
            original_data: {
                monto_original: 0,
                saldo_actual: 0,
                tasa: 0,
                nro_cuotas: 0,
                lapso: '',
                moneda: '',
                lista_cuotas: []
            },
            ver_cuotas_originales: false,
            lista_cuotas_simuladas: [], // Para la previsualización derecha
            fecha_hoy: moment().format("YYYY-MM-DD"),
            refinanciando_solicitud: false,
            reprogramando_prestamo: false,
            refinanciando_prestamo: false,
            capitalizar: false,
            reprogramando_solicitud: false,
            observacion: '',
            guardando_observacion: false,
            eliminando_observacion: false,
            id_solicitud: null,
            sinCodeudor: false, // Estado inicial del switch
            view: 0,
            tipo_tasa: "amortizable",
            fecha_inicial_buscar: moment()
                .subtract(3, "months")
                .format("YYYY-MM-DD"),
            fecha_final_buscar: moment().format("YYYY-MM-DD"),
            criterio_estado: "todos",
            criterio: "cliente.nombre",
            buscar: "",
            lista_codeudores_tabla: [],
            preloader: false,
            aprobando_solicitud: false,
            guardando_solicitud: false,
            lista_solicitudes: [],
            lista_cuotas: [],
            items_cliente: [],
            lista_monedas: [
                {
                    nombre: "Bolivianos",
                },
                {
                    nombre: "Dolares",
                },
            ],
            lapso_capitales: [
                // {
                //     nombre: "Diario",
                // },
                {
                    nombre: "Semanal",
                },
                {
                    nombre: "Quincenal",
                },
                {
                    nombre: "Mensual",
                },
            ],
            tipos_desembolsos: [
                {
                    nombre: "Efectivo",
                },
                {
                    nombre: "Depósito",
                },
                {
                    nombre: "Transferencia",
                },
                {
                    nombre: "QR",
                },
            ],
            tipos_garantias: [
                {
                    nombre: "Garante Personal",
                },
                {
                    nombre: "Prendario o Quirografaria",
                },
                {
                    nombre: "Custodia de Papeles de Moto",
                },
                {
                    nombre: "Custodia Inmueble o Lote terreno",
                },
                {
                    nombre: "Custodia de Vehículo Automovil",
                },
                {
                    nombre: "Empeño Joyas (oro)",
                },
                {
                    nombre: "Empeño de electrodoméstico u Otros",
                },
            ],
            solicitud: {
                id_solicitud: 0,
                importe_solicitud: 0,
                moneda: "",
                lapso_capital: "",
                nro_cuotas: 0,
                tasa: 0,
                fecha_desembolso: moment().format("YYYY-MM-DD"),
                fecha_primera_cuota: moment().format("YYYY-MM-DD"),
                destino_prestamo: "",
                monto_pago_adm: 0,
                estado: "",
                id_cliente: 0,
                id_usuario: 0,
                tipo_garantia: "",
                tipo_desembolso: "",
                tipo_tasa: "",
                lista_codeudores: [],
                enviado: 0,
                accion: 0,
            },

            cliente: {
                id_cliente: 0,
                idd_cliente: "",
                nombre: "",
                ci: "",
                actividad: "",
                lugar_expedicion: "",
                
            },
            cliente_simulacion: {},
            lista_garantias: [
                {
                    id_garantia: 0,
                    descripcion: "",
                },
            ],

            pagination: {
                total: 0,
                current_page: 0,
                per_page: 0,
                last_page: 0,
                from: 0,
                to: 0,
            },

            lista_codeudores: [
                {
                    select_codeudor: {
                        isVisibleCodeudor: false,
                        codeudor: {
                            id_codeudor: 0,
                            idd_codeudor: "",
                            nombre: "",
                            ci: "",
                            lugar_expedicion: "",
                            actividad: "",
                            items_codeudor: [],
                            filteredItemsCodeudorAux: [],
                        },
                    },
                },
            ],
            filteredItemsCliente: [],
            selectedCustomerData: null,
        };
    },
    watch: {
        'solicitud_editar.plazo_meses': function() { this.calcularNroCuotasEdicion(); },
        'solicitud_editar.lapso_capital': function() { this.calcularNroCuotasEdicion(); }
    },

    computed: {
      
        filteredSolicitudes() {
            return this.lista_solicitudes.filter(
                (solicitud) => solicitud.estado !== 10
            );
        },
  
    },
    methods: {
        async obtenerClientePorId(idCliente) {
            this.preloader = true; 
            try {
                const response = await axios.get('/get_cliente_info', {
                    params: {
                        id: idCliente
                    }
                });

                const datos = response.data;

                if (datos.fecha_nacimiento) {
                    datos.fecha_nacimiento = moment(datos.fecha_nacimiento).format("YYYY-MM-DD");
                }
                this.selectedCustomerData = datos;

            } catch (error) {
                console.error("Error al obtener el cliente:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información del cliente.'
                });
            } finally {
                this.preloader = false;
            }
        },

        async abrirModalCliente(accion) {
            this.clienteFormAccion = accion;
            
            if (accion === 1 && this.cliente.id_cliente) {
                this.clienteFormId = this.cliente.id_cliente;
                await this.obtenerClientePorId(this.clienteFormId);
            } else {
                this.clienteFormId = 0;
            }

            this.mostrarModalCliente = true;
            
            // Usamos jQuery para mostrar el modal wrapper de Bootstrap
            this.$nextTick(() => {
                $('#modalClienteOverlay').modal('show');
            });
        },

        // Cierra el modal
        cerrarModalCliente() {
            $('#modalClienteOverlay').modal('hide');
            // Esperamos a que termine la animación de bootstrap para destruir el componente
            setTimeout(() => {
                this.mostrarModalCliente = false;
                this.clienteFormId = 0;
            }, 300);
        },

        // Callback cuando el ClienteForm emite "guardado"
        async alGuardarCliente() {
            // 1. Recargar la lista de clientes en memoria para el buscador
            await this.getClientes(); 

            // 2. Si estábamos editando, actualizar los datos del cliente seleccionado en la vista actual
            if (this.clienteFormAccion === 1 && this.clienteFormId) {
                // Buscar el cliente actualizado en la lista recién cargada
                const clienteActualizado = this.items_cliente.find(c => c.id === this.clienteFormId);
                if (clienteActualizado) {
                    this.seleccionarCliente(clienteActualizado);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Datos del cliente actualizados',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } 
            // 3. Si estábamos creando uno nuevo
            else if (this.clienteFormAccion === 0) {
                // Opcional: Podrías buscar el último cliente creado y seleccionarlo automáticamente
                // O simplemente notificar al usuario que ya puede buscarlo
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Cliente creado exitosamente. Puede buscarlo ahora.',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            
            this.cerrarModalCliente();
        },
        abrirObservacionRepro(accion) {
            const textoInicial = accion === 'modificar' ? this.solicitud_editar.observacion : '';
            this.$refs.modalObservacionRef.abrir(this.solicitud_editar.id, textoInicial);
        },

        async eliminarObservacionRepro() {
            try {
                await axios.post('/guardar_observacion', {
                    id_solicitud: this.solicitud_editar.id,
                    observacion: '',
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Observación levantada',
                    showConfirmButton: false,
                    timer: 1200,
                });
                this.solicitud_editar.observacion = '';
                await this.getSolicitudes(this.pagination.current_page);

            } catch (error) {
                console.error('Error delete observacion:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar la observación' });
            }
        },
        abrirCalculadoraCredito() {
            this.$refs.modalCalculadoraRef.abrir();
        },
        abrirModalObservacion(accion) {
            const textoInicial = accion === 'modificar' ? this.solicitud.observacion : '';
            this.$refs.modalObservacionRef.abrir(this.solicitud.id, textoInicial);
        },

        // NUEVO MÉTODO PARA MANEJAR EL ÉXITO
        async onObservacionGuardada() {
            await this.getSolicitudes(this.pagination.current_page);
            // 2. Lógica condicional según la vista actual
            if (this.view === 'aprobar_reprogramacion') {
                // Si estamos en la aprobación, NO salimos a la lista (view = 0).
                // Buscamos el ítem actualizado en la lista recién cargada para actualizar el texto en pantalla.
                const itemActualizado = this.lista_solicitudes.find(s => s.id === this.solicitud_editar.id);
                if (itemActualizado) {
                    this.solicitud_editar.observacion = itemActualizado.observacion;
                }
            } else {
                this.view = 0; 
            }
        },

        handleFiltros(filtros) {
            this.fecha_inicial_buscar = filtros.fecha_inicial;
            this.fecha_final_buscar = filtros.fecha_final;
            this.criterio_estado = filtros.estado;
            this.criterio = filtros.criterio;
            this.buscar = filtros.buscar;
            this.buscarSolicitudDebounced();
        },
        getEstadoCuota(cuota) {
            // Asumiendo que tu data de ejemplo es correcta: estado: 2 = Pagado/Cancelado
            // estado: 1 = Pendiente
            // estado: 0 = Anulado
            // dias_pasados: > 0 = Vencida (En Mora)
            if (cuota.estado === 2) {
                return { texto: 'Pagado', clase: 'bg-success' };
            }
            
            if (cuota.estado === 0) {
                return { texto: 'Anulado', clase: 'bg-secondary' };
            }

            if (cuota.estado === 1) {
                if (cuota.dias_pasados > 0) {
                    // Está pendiente Y han pasado días
                    return { texto: `Vencida (${cuota.dias_pasados} días)`, clase: 'bg-danger' };
                } else {
                    // Está pendiente, pero aún no se ha vencido
                    return { texto: 'Pendiente', clase: 'bg-warning text-dark' };
                }
            }
            return { texto: 'Indefinido', clase: 'bg-light text-dark' };
        },
        formatFecha(fechaISO) {
            if (!fechaISO) {
                return 'N/A';
            }
            // Puedes cambiar 'DD/MM/YYYY' al formato que prefieras
            return moment(fechaISO).format('DD/MM/YYYY');
        },
    
        async aprobarReprogramacion(item) {
            this.preloader = true;
            this.lista_cuotas_simuladas = [];
            this.ver_cuotas_originales = false;

            try {

                this.solicitud_editar = JSON.parse(JSON.stringify(item));

                if(this.solicitud_editar.fecha_desembolso) {
                    this.solicitud_editar.fecha_desembolso = this.solicitud_editar.fecha_desembolso.split('T')[0];
                } else {
                    this.solicitud_editar.fecha_desembolso = new Date().toISOString().split('T')[0];
                }
                
                if(this.solicitud_editar.fecha_primera_cuota) {
                    this.solicitud_editar.fecha_primera_cuota = this.solicitud_editar.fecha_primera_cuota.split('T')[0];
                }

                let factorDivisor = 1;
                if (this.solicitud_editar.lapso_capital == 'Semanal') factorDivisor = 4;
                else if (this.solicitud_editar.lapso_capital == 'Quincenal') factorDivisor = 2;
                
                this.solicitud_editar.plazo_meses = Math.ceil(this.solicitud_editar.nro_cuotas / factorDivisor);


                try {
                    const resOrden = await axios.get(`/get_orden_pago_reprogramacion?id_solicitud=${item.id}`);
                    if (resOrden.data && resOrden.data.id) {
                        this.orden_pago_actual = resOrden.data;
                    } else {
                        // Si llegó null, vacío o algo raro, forzamos null para que el v-if funcione
                        this.orden_pago_actual = null;
                    }
                } catch (err) {
                    console.log("No hay orden de pago o error al cargarla", err);
                    this.orden_pago_actual = null; // En error también limpiamos
                }


                if (item.id_solicitud_origen) {
                    const response = await axios.get(`/solicitud/detalle-completo/${item.id_solicitud_origen}`);
                    this.original_data = {
                        solicitud: response.data.solicitud,
                        monto_original: response.data.solicitud.importe_solicitud,
                        tasa: response.data.solicitud.tasa,
                        tipo_tasa: response.data.solicitud.tipo_tasa,
                        nro_cuotas: response.data.solicitud.nro_cuotas,
                        lapso: response.data.solicitud.lapso_capital,
                        lista_cuotas: response.data.lista_cuotas,
                        saldo_actual: this.calcularSaldoCapitalOriginal(response.data.lista_cuotas) 
                    };
                }
                this.simularNuevaTabla();
                this.view = 'aprobar_reprogramacion';

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los datos para la aprobación.', 'error');
            } finally {
                this.preloader = false;
            }
        },

        // Helper para calcular saldo de la lista original
        calcularSaldoCapitalOriginal(cuotas) {
            if (!cuotas) return 0;
            // Sumar capital de cuotas pendientes (Estado 1)
            return cuotas
                .filter(c => c.estado === 1)
                .reduce((acc, curr) => acc + parseFloat(curr.capital), 0);
        },

        // --- 2. LÓGICA DE EDICIÓN ---

        calcularNroCuotasEdicion() {
            const plazo = parseFloat(this.solicitud_editar.plazo_meses) || 0;
            const frecuencia = this.solicitud_editar.lapso_capital;
            
            if (plazo <= 0) return;

            let factor = 1;
            if (frecuencia === 'Semanal') factor = 4; 
            if (frecuencia === 'Quincenal') factor = 2;
            if (frecuencia === 'Mensual') factor = 1;
            if (frecuencia === 'Diario') factor = 30;

            this.solicitud_editar.nro_cuotas = Math.round(plazo * factor);
        },

        async simularNuevaTabla() {
            const backupSolicitud = { ...this.solicitud }; // Guardar estado actual
            const backupLista = [...this.lista_cuotas];    // Guardar lista actual
            this.solicitud = {
                importe_solicitud: this.solicitud_editar.importe_solicitud,
                nro_cuotas: this.solicitud_editar.nro_cuotas,
                tasa: this.solicitud_editar.tasa, // Mantenemos la tasa original (o editable si quieres)
                lapso_capital: this.solicitud_editar.lapso_capital,
                fecha_desembolso: this.solicitud_editar.fecha_desembolso,
                fecha_primera_cuota: this.solicitud_editar.fecha_primera_cuota,
                tipo_tasa: this.solicitud_editar.tipo_tasa || 'fija'
            };
            this.lista_cuotas = []; 
            await this.generarPlanPagosGeneral();
            this.lista_cuotas_simuladas = [...this.lista_cuotas];
            this.solicitud = backupSolicitud;
            this.lista_cuotas = backupLista;
        },

        // --- 4. APROBACIÓN FINAL ---
        
        async aprobarDefinitivamente() {
            if (this.solicitud_editar.observacion && this.solicitud_editar.observacion.trim() !== '') {
                Swal.fire('No permitido', 'No se puede aprobar una solicitud que tiene observaciones pendientes.', 'error');
                return;
            }
            // Validación básica
            if (this.lista_cuotas_simuladas.length === 0) {
                Swal.fire('Atención', 'Por favor genere la tabla de pagos antes de aprobar.', 'warning');
                return;
            }

            const result = await Swal.fire({
                title: '¿Aprobar Reprogramación?',
                text: "Esta acción es irreversible. Se anulará el crédito anterior y se activará este nuevo plan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Sí, Aprobar'
            });

            if (result.isConfirmed) {
                this.preloader = true;
                try {
                    await axios.post('/solicitud/aprobar-reprogramacion-final', {
                        id_solicitud: this.solicitud_editar.id,
                        datos_actualizados: this.solicitud_editar, // Enviamos todo el objeto editado
                        cuotas: this.lista_cuotas_simuladas
                    });

                    Swal.fire('¡Aprobado!', 'La reprogramación ha sido procesada correctamente.', 'success');
                    await this.getSolicitudes(1);
                    this.cerrarModalAprobacion();
                    // this.listarSolicitudes(); // No olvides recargar tu lista principal

                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo procesar la aprobación.', 'error');
                } finally {
                    this.preloader = false;
                }
            }
        },

        cerrarModalAprobacion() {
            this.view = 0; // Volver a la lista
            this.solicitud_editar = {};
            this.original_data = { lista_cuotas: [] };
        },

        seleccionarCliente(item) {
            this.cliente.idd_cliente = item.nombre;
            this.cliente.id_cliente = item.id;
            this.cliente.imagen = '/img/cliente/'+item.imagen;
            this.cliente.imagen_validate = item.imagen;
            this.cliente.nombre = item.nombre; // Opcional, si lo necesitas almacenar
            this.cliente.ci = item.ci;
            this.cliente.lugar_expedicion = item.lugar_expedicion; // Si tienes un campo para mostrarlo
            this.cliente.sexo = item.sexo; // Si tienes un campo para mostrarlo
            this.cliente.estado_civil = item.estado_civil; // Si tienes un campo para mostrarlo
            this.cliente.vivienda = item.vivienda; // Si tienes un campo para mostrarlo
            this.cliente.ingreso_mensual = item.ingreso_mensual; // Si tienes un campo para mostrarlo
            this.cliente.actividad = item.actividad;
            this.solicitud.id_cliente = item.id;
            this.filteredItemsCliente = [];
        },
        limpiarSeleccionCliente() {
            this.cliente.id_cliente = 0; // O null, dependiendo de tu inicialización
            this.cliente.nombre = '';
            this.cliente.idd_cliente = '';
            this.cliente.ci = '';
            this.cliente.lugar_expedicion = '';
            this.cliente.fecha_nacimiento = '';
            this.cliente.sexo = '';
            this.cliente.estado_civil = '';
            this.cliente.vivienda = '';
            this.cliente.ingreso_mensual = null;
            this.cliente.actividad = '';
        },
  
        formatNumero(numero) {
            if (numero === -0 || Object.is(numero, -0)) {
                numero = 0;
            }
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 0 }).format(numero);
        },

        actualizarCuotasSolicitud() {
            if (this.solicitud.lapso_capital == 'Semanal') {
                this.solicitud.nro_cuotas = this.solicitud.plazo * 4;
            }

            if (this.solicitud.lapso_capital == 'Quincenal') {
                this.solicitud.nro_cuotas = this.solicitud.plazo * 2;
            }

            if (this.solicitud.lapso_capital == 'Mensual') {
                this.solicitud.nro_cuotas = this.solicitud.plazo * 1;
            }
        },
    
        verHojaSolicitud(solicitud) {
            const datos_aprobacion = {
                id_solicitud: solicitud.id,
                cliente: solicitud.cliente,
                ci: solicitud.ci,
                lugar_expedicion: solicitud.lugar_expedicion,
                actividad: solicitud.actividad,
                personal: solicitud.personal,
                lapso_capital: solicitud.lapso_capital,
                importe_solicitud: solicitud.importe_solicitud,
                nro_cuotas: solicitud.nro_cuotas,
                tipo_garantia: solicitud.tipo_garantia,
                tasa: solicitud.tasa,
                asesor: solicitud.asesor,
                id_cliente: solicitud.id_cliente,
                id_usuario: solicitud.id_usuario,
                asesor: solicitud.asesor,
                fecha_solicitud: solicitud.fecha,

            }
            const datosCodificados = encodeURIComponent(JSON.stringify(datos_aprobacion));
            const url = `/reporte_hoja_solicitud?solicitud=${datosCodificados}`;
            window.open(url, '_blank');
        },
        verHojaAprobacion(solicitud) {
            const datos_aprobacion = {
                id_solicitud: solicitud.id,
                cliente: solicitud.cliente,
                ci: solicitud.ci,
                lugar_expedicion: solicitud.lugar_expedicion,
                actividad: solicitud.actividad,
                personal: solicitud.personal,
                lapso_capital: solicitud.lapso_capital,
                importe_solicitud: solicitud.importe_solicitud,
                nro_cuotas: solicitud.nro_cuotas,
                tipo_garantia: solicitud.tipo_garantia,
                tasa: solicitud.tasa,
                asesor: solicitud.asesor,
                id_cliente: solicitud.id_cliente,
                id_usuario: solicitud.id_usuario,
                asesor: solicitud.asesor,
            }
            const datosCodificados = encodeURIComponent(JSON.stringify(datos_aprobacion));
            const url = `/reporte_hoja_aprobacion?solicitud=${datosCodificados}`;
            window.open(url, '_blank');
        },
        async eliminarObservacion() {
            try {
                this.eliminando_observacion = true;
                await axios.post('/guardar_observacion', {
                    id_solicitud: this.solicitud.id,
                    observacion: '',
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Observación actualizada',
                    showConfirmButton: false,
                    timer: 1200,
                });
                await this.getSolicitudes(1);
                this.view = 0;
            } catch (error) {
                console.error('Error delete observacion:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo eliminar la observación',
                });
            } finally {

                this.eliminando_observacion = false;
            }
        },
        
        abrirModalRespaldos(id_solicitud) {
            this.$refs.modalRespaldosRef.abrir(id_solicitud);
        },

        
        seleccionSinCodeudor(sin_codeudor) {
            if (sin_codeudor) {
                this.lista_codeudores = [
                    {
                        select_codeudor: {
                            isVisibleCodeudor: false,
                            codeudor: {
                                id_codeudor: 0,
                                idd_codeudor: "",
                                nombre: "",
                                ci: "",
                                lugar_expedicion: "",
                                actividad: "",
                                items_codeudor: [],
                                filteredItemsCodeudorAux: [],
                            },
                        },
                    },
                ];
                const objectCodeudor = this.lista_codeudores_tabla.filter(item => item.nombre == 'SIN GARANTE')[0];
                this.seleccionarCodeudor(objectCodeudor, 0);
            } else {
                this.lista_codeudores = [
                    {
                        select_codeudor: {
                            isVisibleCodeudor: false,
                            codeudor: {
                                id_codeudor: 0,
                                idd_codeudor: "",
                                nombre: "",
                                ci: "",
                                lugar_expedicion: "",
                                actividad: "",
                                items_codeudor: [],
                                filteredItemsCodeudorAux: [],
                            },
                        },
                    },
                ];
                this.getCodeudores(0);
            }

        },
    
        formatearFecha(fecha) {
            return fecha ? moment(fecha).format("DD/MM/YYYY") : "-";
        },

       
        getCodeudoresItem(solicitudId) {
            return this.lista_codeudores_tabla.filter(
                (codeudor) => codeudor.id_solicitud === solicitudId
            );
        },

        async getClientes() {
            try {
                const { data } = await axios.get("/get_clientes_sin");
                this.items_cliente = data;
            } catch (error) {
                console.error("Error fetching clients:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los clientes",
                });
            }
        },
        async getCodeudores(pos) {
            try {
                const { data } = await axios.get("/get_codeudores_sin");
                this.lista_codeudores[
                    pos
                ].select_codeudor.codeudor.items_codeudor = data;
            } catch (error) {
                console.error("Error fetching codeudores:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los codeudores",
                });
            }
        },
        async getCodeudoresTabla() {
            try {
                const { data } = await axios.get("/get_codeudores_solicitudes");
                this.lista_codeudores_tabla = data;
            } catch (error) {
                console.error("Error fetching codeudores tabla:", error);
            }
        },
        async getSolicitudes(page) {
            try {
                const { data } = await axios.get("/get_solicitudes", {
                    params: {
                        page,
                        criterio: this.criterio,
                        buscar: this.buscar,
                        estado: this.criterio_estado,
                        fecha_inicial: this.fecha_inicial_buscar,
                        fecha_final: this.fecha_final_buscar,
                    },
                });
                this.lista_solicitudes = data.data;
                this.pagination = {
                    total: data.total,
                    current_page: data.current_page,
                    per_page: data.per_page,
                    last_page: data.last_page,
                    from: data.from,
                    to: data.to,
                };
            } catch (error) {
                console.error("Error fetching solicitudes:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar las solicitudes",
                });
            } finally {

            }
        },


        async getGarantiasSolicitud(id_solicitud) {
            try {
                const response = await axios.get(
                    `/get_garantias?id_solicitud=${id_solicitud}`
                );
                this.lista_garantias = response.data.garantias.length
                    ? response.data.garantias.map((garantia) => ({
                        id_garantia: garantia.id,
                        descripcion: garantia.descripcion,
                    }))
                    : [
                        {
                            id_garantia: 0,
                            descripcion: "",
                        },
                    ];
            } catch (error) {
                console.error("Error al obtener garantías:", error.message);
            }
        },
        
        // async cargarCodeudores(solicitudId) {
        //     try {
        //         const { data } = await axios.get(
        //             `/get_codeudores_solicitud?id_solicitud=${solicitudId}`
        //         );
        //         this.lista_codeudores = data.length
        //             ? data.map((item) => ({
        //                 select_codeudor: {
        //                     isVisibleCodeudor: false,
        //                     codeudor: {
        //                         id_codeudor: item.id,
        //                         idd_codeudor: item.nombre,
        //                         nombre: item.nombre,
        //                         ci: `${item.ci} - ${item.lugar_expedicion}`,
        //                         lugar_expedicion: item.lugar_expedicion,
        //                         actividad: item.actividad,
        //                         items_codeudor: [],
        //                         filteredItemsCodeudorAux: [],
        //                     },
        //                 },
        //             }))
        //             : [
        //                 {
        //                     select_codeudor: {
        //                         isVisibleCodeudor: false,
        //                         codeudor: {
        //                             id_codeudor: 0,
        //                             idd_codeudor: "",
        //                             nombre: "",
        //                             ci: "",
        //                             lugar_expedicion: "",
        //                             actividad: "",
        //                             items_codeudor: [],
        //                             filteredItemsCodeudorAux: [],
        //                         },
        //                     },
        //                 },
        //             ];
        //         this.lista_codeudores.forEach((_, index) =>
        //             this.getCodeudores(index)
        //         );
        //     } catch (error) {
        //         console.error("Error loading codeudores:", error);
        //     }
        // },

        async cargarCodeudores(solicitudId) {
            try {
                const { data } = await axios.get(
                    `/get_codeudores_solicitud?id_solicitud=${solicitudId}`
                );
                this.lista_codeudores = data.length
                    ? data.map((item) => ({
                        select_codeudor: {
                            isVisibleCodeudor: false,
                            codeudor: {
                                id_codeudor: item.id,
                                idd_codeudor: item.nombre,
                                nombre: item.nombre,
                                ci: `${item.ci} ${item.lugar_expedicion ? item.lugar_expedicion : ''}`,
                                lugar_expedicion: item.lugar_expedicion,
                                actividad: item.actividad,
                                // --- MAPEO DE DATOS EXTENDIDOS ---
                                sexo: item.sexo,
                                estado_civil: item.estado_civil,
                                vivienda: item.vivienda,
                                ingreso_mensual: item.ingreso_mensual,
                                imagen: item.imagen,
                                // ---------------------------------
                                items_codeudor: [],
                                filteredItemsCodeudorAux: [],
                            },
                        },
                    }))
                    : [
                        {
                            select_codeudor: {
                                isVisibleCodeudor: false,
                                codeudor: {
                                    id_codeudor: 0,
                                    idd_codeudor: "",
                                    nombre: "",
                                    ci: "",
                                    lugar_expedicion: "",
                                    actividad: "",
                                    items_codeudor: [],
                                    filteredItemsCodeudorAux: [],
                                    // Inicializar vacíos para evitar errores visuales
                                    sexo: "", estado_civil: "", vivienda: "", ingreso_mensual: 0, imagen: ""
                                },
                            },
                        },
                    ];
                
                // Cargar la lista completa de opciones para cada fila (para permitir cambios)
                this.lista_codeudores.forEach((_, index) =>
                    this.getCodeudores(index)
                );
            } catch (error) {
                console.error("Error loading codeudores:", error);
            }
        },

        buscarSolicitudDebounced: debounce(function () {
            this.getSolicitudes(1);
        }, 500),
        filteredItemsClienteMetodo(keyword) {
            if (!keyword) {
                this.filteredItemsCliente = [];
                return;
            }
            const searchTerm = keyword.toLowerCase();
            this.filteredItemsCliente = this.items_cliente.filter(
                (item) =>
                    item.nombre.toLowerCase().includes(searchTerm) ||
                    item.ci.toLowerCase().includes(searchTerm)
            );
        },
        filteredItemsCodeudorMetodo(keyword, index) {
            if (!keyword) {
                this.lista_codeudores[
                    index
                ].select_codeudor.codeudor.filteredItemsCodeudorAux = [];
                return;
            }
            const searchTerm = keyword.toLowerCase();
            this.lista_codeudores[
                index
            ].select_codeudor.codeudor.filteredItemsCodeudorAux =
                this.lista_codeudores[
                    index
                ].select_codeudor.codeudor.items_codeudor.filter(
                    (item) =>
                        item.nombre.toLowerCase().includes(searchTerm) ||
                        item.ci.toLowerCase().includes(searchTerm)
                );
        },

        cambiarPagina(page) {
            if (
                page < 1 ||
                page > this.pagination.last_page ||
                page === this.pagination.current_page
            )
                return;
            this.getSolicitudes(page);
        },

        addCodeudor() {
            const newIndex =
                this.lista_codeudores.push({
                    select_codeudor: {
                        isVisibleCodeudor: false,
                        codeudor: {
                            id_codeudor: 0,
                            idd_codeudor: "",
                            nombre: "",
                            ci: "",
                            lugar_expedicion: "",
                            actividad: "",
                            items_codeudor: [],
                            filteredItemsCodeudorAux: [],
                        },
                    },
                }) - 1;
            this.getCodeudores(newIndex);
        },
        deleteCodeudor(index) {
            if (this.lista_codeudores.length > 1) {
                this.lista_codeudores.splice(index, 1);
            }
        },
        
        // seleccionarCodeudor(item, index) {
        //     this.lista_codeudores[index].select_codeudor.codeudor.idd_codeudor =
        //         item.nombre;
        //     this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor =
        //         item.id;
        //     this.lista_codeudores[
        //         index
        //     ].select_codeudor.codeudor.ci = `${item.ci} - ${item.lugar_expedicion}`;
        //     this.lista_codeudores[index].select_codeudor.codeudor.actividad =
        //         item.actividad;
        //     this.lista_codeudores[
        //         index
        //     ].select_codeudor.codeudor.filteredItemsCodeudorAux = [];
        // },

        seleccionarCodeudor(item, index) {
            // Referencia al objeto codeudor específico en la lista
            let codeudorTarget = this.lista_codeudores[index].select_codeudor.codeudor;

            // Asignación de datos básicos
            codeudorTarget.idd_codeudor = item.nombre;
            codeudorTarget.id_codeudor = item.id;
            codeudorTarget.ci = `${item.ci} ${item.lugar_expedicion ? item.lugar_expedicion : ''}`;
            codeudorTarget.actividad = item.actividad;

            // --- NUEVOS CAMPOS PARA LA TARJETA ---
            codeudorTarget.sexo = item.sexo;
            codeudorTarget.estado_civil = item.estado_civil;
            codeudorTarget.vivienda = item.vivienda;
            codeudorTarget.ingreso_mensual = item.ingreso_mensual;
            codeudorTarget.imagen = item.imagen; // Asegúrate de que tu backend envíe este campo
            // -------------------------------------

            // Limpiar lista de búsqueda
            codeudorTarget.filteredItemsCodeudorAux = [];
        },

        abrirModalNuevo() {
            this.sinCodeudor = false;
            this.resetSolicitud();
            this.getClientes();
            this.getCodeudores(0);
            this.view = 1;
        },
        cerrarModalNuevo() {
            this.view = 0;
            this.resetSolicitud();
        },
        resetSolicitud() {
            this.tipo_tasa = "amortizable";
            this.cliente = {
                id_cliente: 0,
                idd_cliente: "",
                nombre: "",
                ci: "",
                actividad: "",
                lugar_expedicion: "",
            };
            this.lista_codeudores = [
                {
                    select_codeudor: {
                        isVisibleCodeudor: false,
                        codeudor: {
                            id_codeudor: 0,
                            idd_codeudor: "",
                            nombre: "",
                            ci: "",
                            lugar_expedicion: "",
                            actividad: "",
                            items_codeudor: [],
                            filteredItemsCodeudorAux: [],
                        },
                    },
                },
            ];
            this.lista_garantias = [
                {
                    id_garantia: 0,
                    descripcion: "",
                },
            ];
            this.solicitud = {
                id_solicitud: 0,
                importe_solicitud: 0,
                moneda: "",
                lapso_capital: "",
                nro_cuotas: 0,
                tasa: 0,
                fecha_desembolso: moment().format("YYYY-MM-DD"),
                fecha_primera_cuota: moment().format("YYYY-MM-DD"),
                destino_prestamo: "",
                monto_pago_adm: 0,
                estado: "",
                id_cliente: 0,
                id_usuario: 0,
                tipo_garantia: "",
                tipo_desembolso: "",
                tipo_tasa: "",
                lista_codeudores: [],
                enviado: 0,
                accion: 0,
                observacion: '',
                plazo: 0,

            };
        },
        async guardarSolicitud() {
            try {
                this.guardando_solicitud = true;
                this.solicitud.enviado = 1;
                this.solicitud.tipo_tasa = this.tipo_tasa;
                this.solicitud.garantias = this.lista_garantias;
                this.solicitud.lista_codeudores = this.lista_codeudores
                    .map((item) => item.select_codeudor.codeudor)
                    .filter((item) => item.id_codeudor);

                if (!this.validateSolicitud()) return;

                await axios.post("/save_solicitud", this.solicitud);
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Solicitud guardada",
                    showConfirmButton: false,
                    timer: 1500,
                });
                await this.getCodeudoresTabla();
                await this.getSolicitudes(1);
                this.cerrarModalNuevo();
            } catch (error) {
                console.error("Error saving solicitud:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo guardar la solicitud",
                });
            } finally {
                this.guardando_solicitud = false;
            }
        },
        async modificarSolicitud() {
            try {
                this.guardando_solicitud = true;
                this.solicitud.enviado = 1;
                this.solicitud.id_solicitud = this.solicitud.id;
                this.solicitud.tipo_tasa = this.tipo_tasa;
                this.solicitud.garantias = this.lista_garantias;
                this.solicitud.lista_codeudores = this.lista_codeudores
                    .map((item) => item.select_codeudor.codeudor)
                    .filter((item) => item.id_codeudor);

                if (!this.validateSolicitud()) return;

                await axios.post("/modify_solicitud", this.solicitud);
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Solicitud modificada",
                    showConfirmButton: false,
                    timer: 1500,
                });
                await this.getCodeudoresTabla();
                await this.getSolicitudes(1);
                this.cerrarModalNuevo();
            } catch (error) {
                console.error("Error modifying solicitud:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo modificar la solicitud",
                });
            } finally {
                this.guardando_solicitud = false;
            }
        },

        validateSolicitud() {
            const hasDuplicates =
                new Set(
                    this.solicitud.lista_codeudores.map((c) => c.id_codeudor)
                ).size !== this.solicitud.lista_codeudores.length;

            const hasEmptyGarantias =
                ["Prendario", "Empeño", "Joyas", "Custodia Inmbueble", "Custodia Vehiculo"].includes(
                    this.solicitud.tipo_garantia
                ) &&
                this.lista_garantias.some((g) => !g.descripcion);
            const isValid =
                this.solicitud.importe_solicitud > 0 &&
                this.solicitud.moneda &&
                this.solicitud.lapso_capital &&
                this.solicitud.nro_cuotas > 0 &&
                this.solicitud.tasa > 0 &&
                this.solicitud.destino_prestamo &&
                this.solicitud.id_cliente &&
                this.solicitud.tipo_garantia &&
                this.solicitud.tipo_desembolso;

            if (hasDuplicates) {
                Swal.fire({
                    icon: "warning",
                    title: "Advertencia",
                    text: "Existen codeudores duplicados",
                });
                return false;
            }
            if (!isValid || hasEmptyGarantias) {
                Swal.fire({
                    icon: "warning",
                    title: "Advertencia",
                    text: "Complete todos los campos requeridos",
                });
                return false;
            }
            return true;
        },
        async editarSolicitud(item) {
            try {
                this.preloader = true;
                this.solicitud = {
                    ...item,
                    accion: 1,
                    enviado: 0,
                    lista_codeudores: [],
                };
                this.solicitud.plazo = this.solicitud.lapso_capital == 'Semanal' ? this.solicitud.nro_cuotas / 4 : (this.solicitud.lapso_capital == 'Quincenal' ? this.solicitud.nro_cuotas / 2 : this.solicitud.nro_cuotas / 1);
                this.tipo_tasa = item.tipo_tasa;
                const cliente = this.items_cliente.find(
                    (c) => c.id === item.id_cliente
                );
                
                if (cliente) this.seleccionarCliente(cliente);
                await this.cargarCodeudores(item.id);
                if (this.lista_codeudores.length <= 1 && this.lista_codeudores[0].select_codeudor.codeudor.nombre == 'SIN GARANTE') {
                    this.sinCodeudor = true;
                } else {
                    this.sinCodeudor = false;
                }
                await this.getGarantiasSolicitud(item.id);
                if (!this.lista_codeudores.length) {
                    this.addCodeudor();
                }
                $("#modalSolicitud").modal("show");
            } catch (error) {
                console.error("Error editing solicitud:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo cargar la solicitud para edición",
                });
            } finally {
                this.preloader = false;
                this.view = 1;
            }
        },
        async verSolicitud(item) {
            try {
                this.preloader = true;
                this.solicitud = {
                    ...item,
                    accion: 2,
                    enviado: 0,
                    lista_codeudores: [],
                };
                this.tipo_tasa = item.tipo_tasa;
                const cliente = this.items_cliente.find(
                    (c) => c.id === item.id_cliente
                );
                if (cliente) this.seleccionarCliente(cliente);
                await this.cargarCodeudores(item.id);
                if (this.lista_codeudores.length <= 1 && this.lista_codeudores[0].select_codeudor.codeudor.nombre == 'SIN GARANTE') {
                    this.sinCodeudor = true;
                } else {
                    this.sinCodeudor = false;
                }
                await this.getGarantiasSolicitud(item.id);
                $("#modalSolicitud").modal("show");
            } catch (error) {
                console.error("Error viewing solicitud:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo cargar la solicitud para visualización",
                });
            } finally {
                this.preloader = false;
                this.view = 1;
            }
        },
        async activarSolicitud(item) {
            try {
                await axios.get(`/activar_solicitud?id_solicitud=${item.id}`);
                this.getSolicitudes(this.pagination.current_page);
                Swal.fire({
                    icon: "success",
                    title: "Solicitud activada",
                    showConfirmButton: false,
                    timer: 1500,
                });
            } catch (error) {
                console.error("Error activating solicitud:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo activar la solicitud",
                });
            }
        },
        async desactivarSolicitud(item) {
            const result = await Swal.fire({
                title: '¿Está seguro?',
                text: "La solicitud será anulada y no podrá procesarse. Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            });
            if (result.isConfirmed) {
                try {
                    await axios.get(`/desactivar_solicitud?id_solicitud=${item.id}`);
                    this.getSolicitudes(this.pagination.current_page);
                    
                    Swal.fire({
                        icon: "success",
                        title: "Solicitud anulada",
                        text: "El registro ha sido anulado correctamente.",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                } catch (error) {
                    console.error("Error deactivating solicitud:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo anular la solicitud. Intente nuevamente.",
                    });
                }
            }
        },

        agregarGarantia() {
            this.lista_garantias.push({
                id_garantia: 0,
                descripcion: "",
            });
        },
        quitarGarantia(index) {
            if (this.lista_garantias.length > 1) {
                this.lista_garantias.splice(index, 1);
            }
        },
        abrirModalGarantias(id_solicitud) {
            this.$refs.modalGarantiasRef.abrir(id_solicitud);
        },

        async abrirModalSimulacionPlanPago(item) {
            this.solicitud = {
                ...item,
                accion: 2,
            };
            this.solicitud.id_solicitud = item.id;
            this.cliente_simulacion =
                this.items_cliente.find((c) => c.id === item.id_cliente) || {};
            await this.generarPlanPagosGeneral();
            this.view = 2;
        },


        cerrarModalSimulacionPlanPago() {
            this.solicitud = {
                ...this.solicitud,
                accion: 0,
            };
            this.cliente_simulacion = {};
            this.lista_cuotas = [];
            this.view = 0;
        },
        aprobarSolicitud(solicitud_id) {
            this.aprobando_solicitud = true;
            if (this.lista_cuotas.length > 0) {
                Swal.fire({
                    title: '¿Estás seguro de aprobar el prestamo?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let operacion = false;
                        axios.post('/save_planpagos_cuotas', {
                            detalles: JSON.stringify(this.lista_cuotas),
                            id_solicitud: this.solicitud.id_solicitud,
                            fecha_final: this.lista_cuotas[this.lista_cuotas.length - 1].fecha,
                            fecha_inicio_plan_pago: this.solicitud.fecha_desembolso,
                            tasa: this.solicitud.tasa,
                            nro_cuotas: this.solicitud.nro_cuotas,
                            lapso_capital: this.solicitud.lapso_capital,
                            moneda: this.solicitud.moneda,
                        })
                            .then((response) => {
                                console.log('respuesta:', response);
                                operacion = true;
                            })
                            .catch((error) => {
                                console.log(error.message);
                            })
                            .finally(() => {
                                if (operacion) {
                                    Swal.fire({
                                        position: 'center',
                                        icon: 'success',
                                        title: 'Operación exitosa',
                                        text: 'Solicitud aprobada, Verifique en Plan de Pagos',
                                        showConfirmButton: true,
                                        confirmButtonText: 'Aceptar',
                                    });
                                    this.getSolicitudes(1);
                                    this.solicitud.estado = 2;
                                }
                                this.aprobando_solicitud = false;

                            })
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire('Cancelado', 'La acción ha sido cancelada', 'error');
                        this.aprobando_solicitud = false;

                    }
                });
            } else {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Primero debe generar el plan de pagos',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                });
                this.aprobando_solicitud = false;

            }

        },

        async generarPlanPagosGeneral() {
            if (this.solicitud.tipo_tasa == 'amortizable') {
                await this.generarPlanPagos();
                console.log('amortizable');
            } else {
                if (this.solicitud.tipo_tasa == 'fija') {
                    await this.generarPlanPagosTasaFija();
                    console.log('fija');
                }
            }
        },


        async generarPlanPagosTasaFija() {
            if (this.solicitud.importe_solicitud == 0 ||
                this.solicitud.nro_cuotas == 0 ||
                this.solicitud.tasa == 0 ||
                this.solicitud.lapso_capital == '0') {
                return;
            }
            this.lista_cuotas = [];
            const monto_total = parseFloat(this.solicitud.importe_solicitud);
            let saldo_capital = monto_total;
            let fecha_inicio = moment(this.solicitud.fecha_desembolso);
            let fecha_pago = moment(this.solicitud.fecha_primera_cuota);
            const tasa_mensual = parseFloat(this.solicitud.tasa) / 100;
            let cuotas_por_mes = 1;
            let periodo_mes_actual = 1;
            let interes_mensual = 0;
            let interes_acumulado = 0;

            switch (this.solicitud.lapso_capital) {
                case 'Mensual':
                    cuotas_por_mes = 1;
                    break;
                case 'Quincenal':
                    cuotas_por_mes = 2;
                    break;
                case 'Semanal':
                    cuotas_por_mes = 4;
                    break;
                case 'Diario':
                    cuotas_por_mes = 30; // Aproximación
                    break;
                default:
                    throw new Error('Período de pago no soportado');
            }

            // Calcular cuota fija mensual usando fórmula del método francés
            const cuota_mensual = monto_total *
                (tasa_mensual * Math.pow(1 + tasa_mensual, this.solicitud.nro_cuotas / cuotas_por_mes)) /
                (Math.pow(1 + tasa_mensual, this.solicitud.nro_cuotas / cuotas_por_mes) - 1);

            // Calcular cuota para el período específico
            let cuota_periodo = cuota_mensual / cuotas_por_mes;

            for (let nro_cuota = 1; nro_cuota <= this.solicitud.nro_cuotas; nro_cuota++) {
                // Calcular días reales entre pagos
                const dias_periodo = fecha_pago.diff(fecha_inicio, 'days');
                let dias_diferencia = 0;
                let monto_diferencia_dias = 0;
                //let dias_diferencia_menos=0;
                let mas_menos = true;// true mas

                // Calcular el interés mensual completo (solo en la primera cuota del mes)
                if ((nro_cuota - 1) % cuotas_por_mes === 0) {
                    interes_mensual = saldo_capital * tasa_mensual;
                    interes_acumulado = 0;
                    console.log('entro en: ' + nro_cuota);
                    console.log('saldo capital: ' + saldo_capital);
                    console.log('tasa mensual: ' + tasa_mensual);


                    if (nro_cuota == 1) {
                        // Convertir fecha_inicio a objeto moment
                        const inicio = moment(fecha_inicio);
                        // Fecha fin = fecha_inicio + 1 mes
                        let fin = null;
                        switch (this.solicitud.lapso_capital) {
                            case 'Mensual':
                                fin = inicio.clone().add(30, 'days');
                                break;
                            case 'Quincenal':
                                fin = inicio.clone().add(15, 'days');
                                break;
                            case 'Semanal':
                                fin = inicio.clone().add(7, 'days');
                                break;
                            case 'Diario':
                                fin = inicio.clone().add(1, 'days');
                                break;
                            default:
                                throw new Error('Período de pago no soportado');
                        }

                        // Guardar fecha fin como cadena formateada
                        let fecha_fin = fin.format('YYYY-MM-DD');
                        // Calcular diferencia en días (redondeada)
                        const dias = fin.diff(inicio, 'days');

                        console.log('dias: ', dias);

                        let diferencia_dias = dias_periodo;
                        let cant_dias = (this.solicitud.lapso_capital == 'Semanal') ? 28 : 30;
                        if (diferencia_dias > dias) {
                            dias_diferencia = diferencia_dias - dias;
                            monto_diferencia_dias = (interes_mensual / cant_dias) * dias_diferencia;
                        } else {
                            if (diferencia_dias < dias) {
                                dias_diferencia = dias - diferencia_dias;
                                mas_menos = false;
                                monto_diferencia_dias = (interes_mensual / cant_dias) * dias_diferencia;
                            }
                        }
                        console.log('diferencia dias: ', dias_diferencia);
                        console.log('monto diferencia: ', monto_diferencia_dias);

                    }
                }

                // Distribuir el interés mensual entre las cuotas del mes
                const interes_periodo = interes_mensual / cuotas_por_mes;
                interes_acumulado += interes_periodo;

                console.log('interes mensual ' + nro_cuota + ' :' + interes_mensual);
                console.log('cuotas por mes ' + nro_cuota + ' :' + cuotas_por_mes);
                console.log('interes acumulado ' + nro_cuota + ' :' + interes_acumulado);
                // Ajustar en la última cuota del mes para compensar redondeos
                let interes_ajustado = (nro_cuota % cuotas_por_mes === 0 || nro_cuota === this.solicitud.nro_cuotas)
                    ? interes_mensual - (interes_acumulado - interes_periodo)
                    : interes_periodo;


                let capital_periodo = cuota_periodo - interes_ajustado;

                // Actualizar saldo
                saldo_capital -= capital_periodo;

                // Agregar cuota a la lista
                this.lista_cuotas.push({
                    nro: nro_cuota,
                    fecha: fecha_pago.format('YYYY-MM-DD'),
                    capital: Math.round(capital_periodo),
                    saldo_capital: Math.round(saldo_capital),

                    interes: Math.round((mas_menos) ? interes_ajustado + parseFloat(monto_diferencia_dias) : interes_ajustado - parseFloat(monto_diferencia_dias)),
                    total_cuota: Math.round((Math.round((mas_menos) ? cuota_periodo + parseFloat(monto_diferencia_dias) : cuota_periodo - parseFloat(monto_diferencia_dias))).toFixed(0)),
                });

                // Actualizar fechas para el próximo período
                fecha_inicio = fecha_pago;

                switch (this.solicitud.lapso_capital) {
                    case 'Mensual':
                        fecha_pago = moment(fecha_pago).add(1, 'month');
                        break;
                    case 'Quincenal':
                        fecha_pago = moment(fecha_pago).add(15, 'days');
                        break;
                    case 'Semanal':
                        fecha_pago = moment(fecha_pago).add(1, 'week');
                        break;
                    case 'Diario':
                        fecha_pago = moment(fecha_pago).add(1, 'day');
                        break;
                }

            }
        },

        async generarPlanPagos() {
            this.lista_cuotas = [];
            let contador = 1;
            this.solicitud.saldo_capital_sq = 0;
            var fecha_inicio = moment(this.solicitud.fecha_desembolso);
            var fecha_final = moment(this.solicitud.fecha_primera_cuota);
            let capital_aux = this.solicitud.importe_solicitud / this.solicitud.nro_cuotas;
            let saldo_capital_aux = this.solicitud.importe_solicitud;
            this.solicitud.saldo_capital_sq = this.solicitud.importe_solicitud;

            while (contador <= this.solicitud.nro_cuotas) {

                let dias_inicio_fin = fecha_final.diff(fecha_inicio, 'days');

                console.log('dias diferencia:', dias_inicio_fin);

                let interes = 0;
                if (contador == 1) {

                    interes = (saldo_capital_aux * (this.solicitud.tasa / 100)) / 30;// saca interes x dia

                } else {
                    if (this.solicitud.lapso_capital == 'Mensual') {
                        interes = (saldo_capital_aux * (this.solicitud.tasa / 100)) / dias_inicio_fin;
                    } else {
                        if (this.solicitud.lapso_capital == 'Semanal') {
                            interes = (this.solicitud.saldo_capital_sq * (this.solicitud.tasa / 100)) / 4;
                        } else {
                            if (this.solicitud.lapso_capital == 'Quincenal') {
                                interes = (this.solicitud.saldo_capital_sq * (this.solicitud.tasa / 100)) / 2;
                            }
                        }
                    }
                }
                saldo_capital_aux = saldo_capital_aux - capital_aux;
                if (this.solicitud.lapso_capital == 'Mensual') {

                } else {
                    if (this.solicitud.lapso_capital == 'Semanal' && (contador % 4 == 0)) {
                        this.solicitud.saldo_capital_sq = saldo_capital_aux;
                    }
                    if (this.solicitud.lapso_capital == 'Quincenal' && (contador % 2 == 0)) {
                        this.solicitud.saldo_capital_sq = saldo_capital_aux;
                    }
                }
                console.log('dias inicio fin cuota ' + contador + ' :', dias_inicio_fin)
                console.log('interes 1: ', interes);
                this.lista_cuotas.push({
                    nro: contador,
                    fecha: fecha_final.format('YYYY-MM-DD'),
                    capital: Math.round(parseFloat(capital_aux).toFixed(0)),
                    interes: Math.round(parseFloat(interes * (this.solicitud.lapso_capital == 'Mensual' || contador == 1 ? dias_inicio_fin : 1)).toFixed(0)),
                    saldo_capital: Math.round(parseFloat(saldo_capital_aux).toFixed(0)),
                    total_cuota: Math.round(parseFloat(Math.round(parseFloat(capital_aux)) + Math.round(parseFloat(interes * (this.solicitud.lapso_capital == 'Mensual' || contador == 1 ? dias_inicio_fin : 1)).toFixed(0)))),
                });
                fecha_inicio = fecha_final;
                let aux_fecha_final = fecha_final;
                if (this.solicitud.lapso_capital == 'Mensual') {
                    fecha_final = moment(aux_fecha_final).add(1, 'month');
                } else {
                    if (this.solicitud.lapso_capital == 'Quincenal') {
                        fecha_final = moment(aux_fecha_final).add(15, 'days');
                    } else {
                        if (this.solicitud.lapso_capital == 'Semanal') {
                            fecha_final = moment(aux_fecha_final).add(1, 'week');
                        } else {
                            if (this.solicitud.lapso_capital == 'Diario') {
                                fecha_final = moment(aux_fecha_final).add(1, 'day');
                            }
                        }
                    }
                }
                contador++;
            }
        },

        listaCuotasPdf(solicitud_id) {
            if (this.lista_cuotas.length == 0) {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Primero debe generar el plan de pagos',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                });
            } else {
                const url = '/lista_cuotas_pdf?id_solicitud=' + solicitud_id + '&detalles=' + JSON.stringify(this
                    .lista_cuotas);
                window.open(url, '_blank');
            }
        },
     
    
    },
    async mounted() {
        this.preloader = true;
        await this.getClientes();
        await this.getCodeudoresTabla();
        await this.getSolicitudes(1);
        this.preloader = false;

    },
};
</script>

<style scoped>
    @import './styles/frmSolicitud.css';

    /* Dropdown personalizado */
    .dropdown-item:hover {
        background-color: #f8f9fa;
        border-left: 2px solid #198754; /* Borde verde al pasar el mouse */
    }

    .dropdown-item {
        border-left: 2px solid #ffffff; /* Borde verde al pasar el mouse */
    }

   

   
</style>



