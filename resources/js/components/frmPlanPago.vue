<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <ListaPlanPagos 
                    v-if="view == 0"
                    :lista="lista_planespago"
                    :asesores="lista_asesores"
                    :codeudores-global="lista_codeudores_tabla"
                    :fecha-actual="fecha_actual"
                    @filtrar="handleBusqueda"
                    @exportar="exportExcelPlanPagos"
                    @anular="anularPlanPago"
                    @activar="activarPlanPago"
                    @ver-detalle="abrirModalVerCuotas"
                    @generar-contrato="generarContrato"
                    @reprogramar="reprogramar"
                />

                <DetallePlanPago 
                    v-if="view == 1"
                    :plan="plan_pago"
                    :cuotas="lista_cuotas_plan"
                    @cerrar="cerrarModalCuotas"
                    @ver-ficha="verFicha"
                    @ver-original="verDetalleOriginal"
                />

                <div v-if="view == 2" class="card shadow-sm">
                    <div class="card-header bg-warning py-1 d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 text-center">
                            <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                                {{ modo_operacion == 'REFINANCIAMIENTO' ? 'REFINANCIAMIENTO DEL CREDITO' : 'REPROGRAMACIÓN DEL CREDITO' }}
                            </h5>
                        </div>
                        <button @click="cerrarReprogramacion()" type="button" class="btn-close btn-close-dark"
                            aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="registrarSolicitudReprogramacion">
                        <div class="card-body">
                            <div v-if="error_reprogramacion" 
                                class="alert alert-danger fw-bold text-center border border-danger shadow-sm mb-4">
                                <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
                                {{ error_reprogramacion }}
                                <hr class="my-2">
                                <small>Por favor, regularice la situación antes de continuar.</small>
                            </div>
                            
                            <div class="card mb-4 border-success shadow-sm">
                                <div class="card-header bg-white text-dark">
                                    <h6
                                        class="mb-0 text-text-dark fw-bold border-bottom pb-2 border-2 border-dark text-uppercase">
                                        Cliente
                                    </h6>
                                </div>

                                <div class="card-body p-3">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-semibold small">
                                                Información del Cliente
                                            </h6>

                                        </div>
                                        <div class="card border">
                                            <div class="card-body p-2">
                                                <div class="row g-2" style="font-size: 0.8rem;">

                                                    <div class="col-12 mb-3">
                                                        <div class="py-2 rounded d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <strong class="d-block text-dark text-uppercase"
                                                                    style="font-size: 0.95rem;">{{
                                                                    plan_pago.cliente }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 col-6">
                                                        <div class="info-compact-foto">
                                                            <img :src="plan_pago.imagen_validate ? plan_pago.imagen : '/img/empresa/user_img2_old.png'"
                                                                class="user-img" alt="Fotografía del cliente">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 col-6">
                                                        <div class="info-compact mt-3">
                                                            <strong class="d-block text-dark"
                                                                style="font-size: 0.9rem;">CI</strong>
                                                            <span class="fw-semibold">{{ plan_pago.ci }}</span>
                                                            <span class="badge bg-secondary ms-1"
                                                                style="font-size: 0.8rem;">{{
                                                                plan_pago.lugar_expedicion }}</span>

                                                            <strong class="d-block mt-3 text-dark"
                                                                style="font-size: 0.9rem; color: #6c757d;">SEXO</strong>
                                                            <span>{{ plan_pago.sexo }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 col-6">
                                                        <div class="info-compact mt-3">
                                                            <strong class="d-block text-dark"
                                                                style="font-size: 0.9rem; color: #6c757d;">ESTADO
                                                                CIVIL</strong>
                                                            <span>{{ plan_pago.estado_civil }}</span>
                                                            <strong class="d-block text-dark mt-3"
                                                                style="font-size: 0.9rem; color: #6c757d;">VIVIENDA</strong>
                                                            <span>{{ plan_pago.vivienda }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 col-6">
                                                        <div class="info-compact mt-3">
                                                            <strong class="d-block text-dark"
                                                                style="font-size: 0.7rem; color: #6c757d;">INGRESO
                                                                (Bs)</strong>
                                                            <span class="fw-bold text-dark"
                                                                style="font-size: 0.9rem;">{{
                                                                plan_pago.ingreso_mensual?.toLocaleString('es-BO') ||
                                                                'N/A' }}</span>

                                                            <strong class="d-block text-dark mt-3"
                                                                style="font-size: 0.7rem; color: #6c757d;">ACTIVIDAD</strong>
                                                            <span>{{ plan_pago.actividad }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card mb-4 border-success shadow-sm">

                                <div class="card-header border-bottom" id="headerCodeudor">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-uppercase border-bottom pb-2 border-2 border-dark"
                                            id="tituloCabecera">
                                            Codeudores/Garantes
                                        </h6>
                                    </div>
                                </div>

                                <div class="card-body" v-show="sinCodeudor" id="mensajeSinCodeudor">
                                    <div class="alert alert-light border mb-0 text-danger fw-bold">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Se ha seleccionado la opción "Sin Codeudor/Garante" para este préstamo.
                                    </div>
                                </div>

                                <div class="card-body" v-show="!sinCodeudor">
                                    <ul class="list-group list-group-flush">
                                        <li v-for="(codeudor, index) in lista_codeudores" :key="codeudor.id"
                                            class="list-group-item px-0">

                                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                                <h5 class="mb-0 fw-bold text-dark">
                                                    Garante {{ index + 1 }}: {{ codeudor.nombre }}
                                                </h5>
                                                <span class="badge bg-primary rounded-pill">{{ codeudor.tipo }}</span>
                                            </div>

                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 col-md-3">CI:</dt>
                                                <dd class="col-sm-8 col-md-9">{{ codeudor.ci }} {{
                                                    codeudor.lugar_expedicion }}</dd>

                                                <dt class="col-sm-4 col-md-3">Actividad:</dt>
                                                <dd class="col-sm-8 col-md-9">{{ codeudor.actividad }}</dd>

                                                <dt class="col-sm-4 col-md-3">Ingreso Mensual:</dt>
                                                <dd class="col-sm-8 col-md-9">Bs. {{ codeudor.ingreso_mensual }}</dd>

                                                <dt class="col-sm-4 col-md-3">Vivienda:</dt>
                                                <dd class="col-sm-8 col-md-9">{{ codeudor.vivienda }}</dd>

                                                <dt class="col-sm-4 col-md-3">Estado Civil:</dt>
                                                <dd class="col-sm-8 col-md-9">{{ codeudor.estado_civil }}</dd>
                                            </dl>

                                            <hr v-if="index < lista_codeudores.length - 1" class="my-3">
                                        </li>
                                    </ul>
                                </div>
                            </div>


                            <div class="card mb-4 border-success shadow-sm">
                                <div class="card-header bg-white text-dark">
                                    <h6
                                        class="mb-0 text-text-dark fw-bold border-bottom pb-2 border-2 border-dark text-uppercase">
                                        DATOS DEL CRÉDITO
                                    </h6>
                                </div>

                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-lg-6">
                                            <dl class="row">

                                                <dt class="col-sm-5 col-md-4">Monto:</dt>
                                                <dd class="col-sm-7 col-md-8 fw-bold fs-5 text-dark">
                                                    {{ formatNumero(plan_pago.importe_solicitud) }} {{ plan_pago.moneda }} 
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Tasa de Interés:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.tasa }}% {{ plan_pago.tipo_tasa }}
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Forma de Pago:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.lapso_capital }}
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Número de Cuotas:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.nro_cuotas }}
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Destino:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.destino_prestamo }}
                                                </dd>

                                            </dl>
                                        </div>

                                        <div class="col-lg-6 border-lg-start">
                                            <dl class="row">
                                                <dt class="col-sm-5 col-md-4">Plazo (Meses):</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.plazo }} meses
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Fecha Desembolso:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.fecha_desembolso }}
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Fecha 1ra Cuota:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.fecha_primera_cuota }}
                                                </dd>

                                                <dt class="col-sm-5 col-md-4">Tipo Desembolso:</dt>
                                                <dd class="col-sm-7 col-md-8">
                                                    {{ plan_pago.tipo_desembolso }}
                                                </dd>
                                                
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="['Empeño de electrodoméstico u Otros', 'Custodia de Papeles de Moto', 'Prendario o Quirografaria', 'Empeño Joyas (oro)', 'Custodia Inmueble o Lote terreno', 'Custodia de Vehículo Automovil'].includes(plan_pago.tipo_garantia)"
                                class="card mb-4 border-primary shadow-sm">

                                <div class="card-header bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-uppercase border-bottom pb-2 border-2 border-dark text-dark">
                                            Información de las Garantías
                                        </h6>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        
                                        <li v-for="(item, index) in lista_garantias" :key="index" 
                                            class="list-group-item px-0 py-3">
                                            
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                                        Garantía {{ index + 1 }}
                                                    </span>
                                                </div>
                                                
                                                <div class="flex-grow-1 border-start ps-3 border-3 border-light">
                                                    <h6 class="mb-1 text-muted small text-uppercase fw-bold">
                                                        Descripción del Bien / Documento
                                                    </h6>
                                                    <p class="mb-0 fw-semibold text-dark fs-6">
                                                        {{ item.descripcion }}
                                                    </p>
                                                </div>
                                            </div>

                                        </li>

                                        <li v-if="lista_garantias.length === 0" class="list-group-item px-0 text-center py-4">
                                            <span class="text-muted fst-italic">
                                                <i class="fas fa-info-circle me-1"></i> No se detallaron descripciones adicionales.
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card mb-4 border-primary shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 fw-bold text-uppercase border-bottom pb-2 border-2 border-dark text-dark">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                        Selección de Pagos Previos
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center my-3">
                                        <transition name="fade">
                                            <div v-if="pago_previo.seleccionado" class="container-fluid pb-2 px-0">
                                                <div class="row g-2">
                                                    
                                                    <div class="col-4">
                                                        <div class="p-2 border rounded bg-light d-flex align-items-center h-100 position-relative overflow-hidden">
                                                            <div class="position-absolute top-0 start-0 bottom-0 bg-primary" style="width: 4px;"></div>
                                                            
                                                            <div class="ms-2 me-3 text-primary">
                                                                <i class="fas fa-percentage fa-lg"></i>
                                                            </div>
                                                            <div>
                                                                <div class="small text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Interés</div>
                                                                <div class="d-flex align-items-baseline">
                                                                    <span class="fw-bold text-dark fs-6">
                                                                        {{ formatNumero(pago_previo.detalle_interes) }}
                                                                    </span>
                                                                    
                                                                    <span v-if="pago_previo.condonar_interes" 
                                                                        class="badge bg-danger bg-opacity-10 text-danger ms-2" 
                                                                        style="font-size: 0.6rem;">
                                                                        <i class="fas fa-arrow-down"></i> {{ formatNumero(pago_previo.monto_condonar_interes) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-4">
                                                        <div class="p-2 border rounded bg-light d-flex align-items-center h-100 position-relative overflow-hidden">
                                                            <div class="position-absolute top-0 start-0 bottom-0 bg-danger" style="width: 4px;"></div>
                                                            
                                                            <div class="ms-2 me-3 text-danger">
                                                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                                                            </div>
                                                            <div>
                                                                <div class="small text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Multa</div>
                                                                <div class="d-flex align-items-baseline">
                                                                    <span class="fw-bold text-dark fs-6">
                                                                        {{ formatNumero(pago_previo.detalle_mora) }}
                                                                    </span>

                                                                    <span v-if="pago_previo.condonar_mora" 
                                                                        class="badge bg-danger bg-opacity-10 text-danger ms-2" 
                                                                        style="font-size: 0.6rem;">
                                                                        <i class="fas fa-arrow-down"></i> {{ formatNumero(pago_previo.monto_condonar_mora) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-4">
                                                        <div class="p-2 border border-success rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-between h-100">
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                    style="width: 35px; height: 35px;">
                                                                    <i class="fas fa-cash-register"></i>
                                                                </div>
                                                                <div class="lh-1">
                                                                    <div class="text-primary text-uppercase fw-bold" style="font-size: 0.7rem;">Total a Pagar</div>
                                                                    <small class="text-primary" style="font-size: 0.65rem;">(Previo a Reprogramar)</small>
                                                                </div>
                                                            </div>
                                                            <div class="fs-5 fw-bold text-primary">
                                                                {{ formatNumero(pago_previo.total_pagar) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </transition>
                                    </div>

                                    <transition name="fade">
                                        <div v-if="pago_previo.seleccionado" class="rounded p-3 mt-2 border border-secondary border-opacity-25">
                                            <h6 class="small fw-bold text-muted text-uppercase mb-2">
                                                <i class="fas fa-hand-holding-heart me-1"></i> Opciones de Condonación
                                            </h6>
                                            
                                            <div class="row g-3 align-items-end">

                                                <div class="col-md-3" v-if="pago_previo.detalle_interes>0">
                                                    <div class="form-check form-switch mb-1">
                                                        <input class="form-check-input cursor-pointer rounded" type="checkbox" role="switch" 
                                                            id="checkCondInt" 
                                                            v-model="pago_previo.condonar_interes"
                                                            @change="toggleCondonacion('interes')">
                                                        <label class="form-check-label small fw-bold" for="checkCondInt">Condonar Interés</label>
                                                    </div>
                                                    <div class="input-group input-group-sm" v-if="pago_previo.condonar_interes">
                                                        <input type="number" class="form-control" 
                                                            v-model.number="pago_previo.monto_condonar_interes" 
                                                            min="1" :max="pago_previo.detalle_interes" step="0.01"
                                                            @input="calcularTotalesPago()">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 border-start" v-if="pago_previo.detalle_mora>0">
                                                    <div class="form-check form-switch mb-1">
                                                        <input class="form-check-input cursor-pointer rounded" type="checkbox" role="switch" 
                                                            id="checkCondMora" 
                                                            v-model="pago_previo.condonar_mora"
                                                            @change="toggleCondonacion('mora')">
                                                        <label class="form-check-label small fw-bold" for="checkCondMora">Condonar Multa</label>
                                                    </div>
                                                    <div class="input-group input-group-sm" v-if="pago_previo.condonar_mora">
                                                        <input type="number" class="form-control" 
                                                            v-model.number="pago_previo.monto_condonar_mora" 
                                                            min="1" :max="pago_previo.detalle_mora" step="0.01"
                                                            @input="calcularTotalesPago()">
                                                    </div>
                                                </div>

                                                <div class="col-md-6 border-start">
                                                    <label class="form-label small text-muted mb-1">Motivo de condonación (Opcional)</label>
                                                    <input type="text" class="form-control form-control-sm" 
                                                        v-model="pago_previo.motivo_condonacion" 
                                                        :disabled="!pago_previo.condonar_interes && !pago_previo.condonar_mora"
                                                        placeholder="Ej. Acuerdo gerencial / Buen cliente...">
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <div class="card-body p-0">
                                    
                                    <div v-if="pago_previo.seleccionado" class="alert alert-success m-2 py-1 small text-center">
                                        <strong>{{ pago_previo.descripcion }}</strong>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-hover small mb-0 align-middle">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th rowspan="2" class="align-middle">#</th>
                                                    <th rowspan="2" class="align-middle">Vencimiento</th>
                                                    <th colspan="4" class="border-start border-end bg-primary bg-opacity-10 text-primary">
                                                        Cálculo de Interés
                                                    </th>
                                                    <th rowspan="2" class="bg-danger bg-opacity-10 text-danger border-end align-middle" width="8%">
                                                        Multa
                                                    </th>
                                                    <th rowspan="2" class="text-end align-middle">Capital</th>
                                                    <th rowspan="2" class="text-center align-middle">Estado</th>
                                                    <th rowspan="2" class="text-center align-middle">Total Cuota</th>
                                                    <th rowspan="2" class="text-center align-middle">Saldo Capital</th>
                                                </tr>
                                                <tr>
                                                    <th class="text-center text-muted small bg-primary bg-opacity-10" width="7%">Int. Fijo</th>
                                                    <th class="text-center text-primary small fw-bold bg-primary bg-opacity-10" width="10%">Int. Devengado</th>
                                                    <th class="text-center text-danger small fw-bold bg-primary bg-opacity-10" width="10%">Int. Moratorio</th>
                                                    <th class="text-center fw-bold text-primary bg-primary bg-opacity-10 border-end" width="9%">Total / Pagar</th>
                                                </tr>
                                            </thead>
                                            
                                            <tbody>
                                                <tr v-for="(cuota, index) in lista_cuotas_plan" :key="cuota.id" 
                                                    :class="{'opacity-50': !esFilaHabilitada(index) && cuota.estado !== 2}">
                                                    
                                                    <td class="text-center fw-bold">{{ cuota.numero }}</td>
                                                    <td class="text-center">{{ formatFecha(cuota.fecha) }}</td>
                                                    
                                                    <!-- Int. Fijo -->
                                                    <td class="text-center border-start bg-primary bg-opacity-10">
                                                        <div class="fw-semibold text-dark">{{ formatNumero(cuota.interes) }}</div>
                                                        <div class="text-muted" style="font-size: 0.62rem;">Bs / cuota</div>
                                                    </td>

                                                    <!-- Int. Devengado -->
                                                    <td class="text-center bg-primary bg-opacity-10">
                                                        <template v-if="cuota.estado !== 2 && cuota.estado !== 0">
                                                            <div class="fw-bold text-primary" style="font-size: 0.8rem;">
                                                                {{ formatNumero(cuota.interes_devengado_neto) }}
                                                            </div>
                                                            <div class="text-muted" style="font-size: 0.62rem;">
                                                                <i class="fas fa-calendar-day"></i> {{ cuota.dias_transcurridos }} días
                                                            </div>
                                                        </template>
                                                        <span v-else class="text-muted small">---</span>
                                                    </td>

                                                    <!-- Int. Moratorio -->
                                                    <td class="text-center bg-primary bg-opacity-10">
                                                        <template v-if="parseFloat(cuota.interes_moratorio_neto) > 0 && cuota.estado !== 2 && cuota.estado !== 0">
                                                            <div class="fw-bold text-danger" style="font-size: 0.8rem;">
                                                                {{ formatNumero(cuota.interes_moratorio_neto) }}
                                                            </div>
                                                            <div class="text-danger" style="font-size: 0.62rem;">
                                                                <i class="fas fa-clock"></i> {{ cuota.dias_pasados }} días venc.
                                                            </div>
                                                        </template>
                                                        <span v-else class="text-muted small">-</span>
                                                    </td>

                                                    <!-- Total a pagar (checkbox) -->
                                                    <td class="text-center border-end bg-primary bg-opacity-10">
                                                        <div v-if="cuota.estado !== 2">
                                                            <div v-if="parseFloat(cuota.interes_acumulado_neto) > 0"
                                                                class="d-flex flex-column align-items-center">
                                                                <input class="form-check-input m-0 shadow-none border-primary"
                                                                    type="checkbox"
                                                                    style="cursor: pointer; transform: scale(1.15);"
                                                                    :disabled="!esFilaHabilitada(index)"
                                                                    :checked="esInteresMarcado(index)"
                                                                    @change="clickCheckInteres(index)">
                                                                <small class="fw-bold text-primary mt-1" style="font-size: 0.8rem;">
                                                                    {{ formatNumero(cuota.interes_acumulado_neto) }}
                                                                </small>
                                                                <div class="text-muted" style="font-size: 0.62rem;">Bs total</div>
                                                                <span v-if="cuota.estado == 3"
                                                                    class="badge bg-info text-white border border-primary py-0 px-2 mt-1"
                                                                    style="font-size: 0.55rem;">
                                                                    Parcial
                                                                </span>
                                                            </div>
                                                            <div v-else class="text-muted small">-</div>
                                                        </div>
                                                    </td>

                                                    <td class="text-center border-end bg-danger bg-opacity-10">
                                                        <div v-if="cuota.estado !== 2">
                                                            <div v-if="parseFloat(cuota.mora_fija_neta) > 0 && (index === 0 || parseFloat(lista_cuotas_plan[index-1].mora_fija_neta) <= 0)" class="d-flex flex-column align-items-center">
                                                                <input class="form-check-input m-0 shadow-none border-danger" 
                                                                    type="checkbox" 
                                                                    style="cursor: pointer; transform: scale(1.15);"
                                                                    :disabled="!esFilaHabilitada(index)"
                                                                    :checked="esMoraMarcada(index)"
                                                                    @change="clickCheckMora(index)">
                                                                
                                                                <small class="fw-bold text-danger mt-1" style="font-size: 0.75rem;">
                                                                    {{ formatNumero(cuota.mora_fija_neta) }}
                                                                </small>
                                                            </div>
                                                            <div v-else class="text-muted small">-</div>
                                                        </div>
                                                    </td>

                                                    <td class="text-end fw-bold text-dark">
                                                        <div>Bs. {{ formatNumero(cuota.capital) }}</div>
                                                        <div v-if="parseFloat(cuota.capital_pagado_total) > 0" class="text-success lh-1 mt-1" style="font-size: 0.65rem;">
                                                            <span class="fw-bold">{{ cuota.porcentaje_capital_pagado }}%</span><br>
                                                            {{ formatNumero(cuota.capital_pagado_total) }} Bs
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="text-center">
                                                        <div v-if="parseFloat(cuota.mora_fija_neta) > 0 && cuota.estado != 2" class="mb-1">
                                                            <span class="badge bg-danger text-white rounded-pill" style="font-size: 0.55rem; min-width: 70px;">
                                                                Multa {{ cuota.dias_pasados }}d
                                                            </span>
                                                        </div>
                                                        <span class="badge rounded-pill" :class="getEstadoCuota(cuota).clase" style="font-size:0.65rem; min-width: 90px;">
                                                            {{ getEstadoCuota(cuota).texto }}
                                                        </span>
                                                    </td>
                                                    
                                                    <td class="text-center text-muted">Bs. {{ formatNumero(cuota.total) }}</td>
                                                    <td class="text-center text-muted">Bs. {{ formatNumero(cuota.saldo_capital) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div v-if="lista_cuotas_plan.length > 0" class="bg-light p-2 text-center text-muted fst-italic small border-top">
                                        <i class="fas fa-info-circle"></i> Solo puede seleccionar pagos si la cuota anterior tiene fecha pasada.
                                    </div>
                                </div>
                            </div>
            
                            <div class="card mb-4 border-warning shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-uppercase border-bottom pb-2 border-2 border-dark text-dark">
                                            <i class="fas fa-history me-2 text-dark"></i>
                                            Datos de la {{ modo_operacion == 'REFINANCIAMIENTO' ? 'Refinanciación' : 'Reprogramación' }}
                                        </h6>
                                        <span v-if="modo_operacion == 'REFINANCIAMIENTO'" class="badge bg-success">CON CAPITAL ADICIONAL</span>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <div class="row g-2">
                                 
                                                <div :class="modo_operacion == 'REFINANCIAMIENTO' ? 'col-md-6' : 'col-md-12'">
                                                    <div class="form-floating">
                                                        <input type="text" 
                                                            class="form-control form-control-lg bg-light text-secondary fw-bold" 
                                                            id="reproMontoBase" 
                                                            :value="plan_pago.moneda + ' ' + saldoTotalParaReprogramar"
                                                            readonly disabled>
                                                        <label for="reproMontoBase">Saldo Deuda Actual (Base)</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6" v-if="modo_operacion == 'REFINANCIAMIENTO'">
                                                    <div class="form-floating">
                                                        <input type="number" 
                                                            class="form-control form-control-lg border-success text-success fw-bold" 
                                                            id="montoAdicional" 
                                                            v-model.number="monto_adicional" 
                                                            min="0" 
                                                            placeholder="0">
                                                        <label for="montoAdicional">Monto Adicional (Efectivo)</label>
                                                    </div>
                                                </div>

                                                <div class="col-12" v-if="modo_operacion == 'REFINANCIAMIENTO'">
                                                    <div class="alert alert-success text-center py-2 mb-0">
                                                        <small class="text-uppercase fw-bold">Nuevo Monto Total del Crédito</small>
                                                        <div class="fs-3 fw-bold">
                                                            {{ plan_pago.moneda }} {{ (parseFloat(monto_base_deuda) + parseFloat(monto_adicional || 0)).toFixed(2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating">
                                                <select class="form-select" id="reproFormaPago" v-model="plan_pago.forma_pago_reprogramacion" required>
                                                    <option value="" disabled selected>Seleccione...</option>
                                                    <option v-for="lapso in lapso_capitales" :key="lapso.nombre" :value="lapso.nombre">
                                                        {{ lapso.nombre }}
                                                    </option>
                                                </select>
                                                <label for="reproFormaPago">Forma de Pago</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-floating">
                                                <input type="number" class="form-control" id="reproPlazoMeses" 
                                                    v-model.number="plan_pago.plazo_meses" min="1" placeholder="Ej: 6" required>
                                                <label for="reproPlazoMeses">Nuevo Plazo (Meses)</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-floating">
                                                <input type="number" class="form-control" id="reproNumCuotas" 
                                                    v-model="plan_pago.numero_cuotas_reprogramacion" readonly disabled>
                                                <label for="reproNumCuotas">N° de Cuotas</label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4 border-info shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 fw-bold text-uppercase border-bottom pb-2 border-2 border-dark text-dark">
                                        <i class="fas fa-file-invoice-dollar me-2 text-dark"></i>
                                        Simulación del Nuevo Plan de Pagos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-center gap-2 mb-3">
                                        <button type="button" 
                                                class="btn btn-info fw-bold" 
                                                @click="generarPlanReprogramacion">
                                            
                                            <i class="fas fa-cogs me-2"></i> 
                                            Calcular Nuevo Plan de Pagos
                                        </button>
                                        <button type="button" 
                                                class="btn btn-warning fw-bold me-2 text-dark" 
                                                @click="generarReportePlanReprogramacion">
                                            
                                            <i class="fas fa-print me-2"></i> 
                                            Generar PDF del Plan
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        
                                        <table class="table table-sm table-striped table-hover small">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" class="text-center">#</th>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col" class="text-end">Capital</th>
                                                    <th scope="col" class="text-end">Interés</th>
                                                    <th scope="col" class="text-end fw-bold">Total Cuota</th>
                                                    <th scope="col" class="text-end">Saldo Capital</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="cuota in lista_cuotas" :key="cuota.nro">
                                                    <th scope="row" class="text-center fw-medium">{{ cuota.nro }}</th>
                                                    <td>{{ cuota.fecha }}</td>
                                                    <td class="text-end">Bs. {{ cuota.capital }}</td>
                                                    <td class="text-end">Bs. {{ cuota.interes }}</td>
                                                    
                                                    <td class="text-end fw-bold text-dark">Bs. {{ cuota.total_cuota }}</td>
                                                    <td class="text-end text-muted">Bs. {{ cuota.saldo_capital }}</td>
                                                </tr>
                                                
                                                <tr v-if="!lista_cuotas || lista_cuotas.length === 0">
                                                    <td colspan="6" class="text-center text-muted p-4 fst-italic">
                                                        Presione "Calcular Nuevo Plan de Pagos" para ver la simulación.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer d-flex justify-content-center border-top border-2 border-dark pt-4">
                            <button type="button" class="btn btn-secondary me-2" @click="cerrarReprogramacion()"
                                data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cerrar
                            </button>

                            <button type.="submit" 
                                    class="btn btn-success" 
                                    :disabled="reprogramando_solicitud || !!error_reprogramacion">
                                    
                                <i v-if="!reprogramando_solicitud" class="fas fa-save"></i>
                                <i v-if="reprogramando_solicitud" class="fas fa-spinner fa-spin"></i>
                                
                                {{ reprogramando_solicitud ? 'Registrando...' : (modo_operacion=='REFINANCIAMIENTO'? 'REFINANCIAR':'REPROGRAMAR') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <ModalHistorialOriginal :datos="datos_original_modal" />
        <ModalDetalleCliente ref="modalClienteRef" />
    </main>
</template>

<script>
import ModalDetalleCliente from './layouts/ModalDetalleCliente.vue';
import DetallePlanPago from './PlanPago/DetallePlanPago.vue';
import ListaPlanPagos from './PlanPago/ListaPlanPagos.vue';
import ModalHistorialOriginal from './PlanPago/ModalHistorialOriginal.vue';
import moment from 'moment';
import Swal from 'sweetalert2'
import {formas_pago, lista_monedas, tipos_desembolsos, lapso_capitales} from '../constants';

export default {
    components: {
        ModalDetalleCliente,
        ModalHistorialOriginal,
        DetallePlanPago,
        ListaPlanPagos, 
    },
    data() {
        return {
            idx_sel_interes: -1, 
            idx_sel_mora: -1,
            pago_previo: {
                seleccionado: false,
                total_pagar: 0,
                detalle_interes: 0,
                detalle_mora: 0,
                descripcion: '',
                ids_cuotas_interes: [],
                ids_cuotas_mora: [],
                condonar_interes: false,
                monto_condonar_interes: 0,
                condonar_mora: false,
                monto_condonar_mora: 0,
                motivo_condonacion: ''
            },
            
            filtros_actuales: {},
            datos_original_modal: null, // Datos para el modal del crédito original
            error_reprogramacion: null,
            modo_operacion: 'REPROGRAMACION', // 'REPROGRAMACION' o 'REFINANCIAMIENTO'
            monto_adicional: 0, // Dinero extra para refinanciamiento
            monto_base_deuda: 0, // El saldo real que viene del backend
            monto_a_reprogramar: 0.00,
            reprogramando_solicitud:false,
            lista_garantias:[],
            lista_codeudores:[],
            sinCodeudor: false,
            view: 0,
            fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
            fecha_fin: moment().format('YYYY-MM-DD'),
            lista_cuotas_amortizacion: [],
            tipo_tasa: 'amortizable',
            preloader: false,
            lista_amortizaciones: [],
            lista_planes_pago_ligados: [],
            tasa_plan_nuevo: 'amortizable',
            bloqueado: true,
            codigo_credito: 0,
            nombre_cliente: '',
            estado_credito: 'todos',
            fecha_actual: moment().format('YYYY-MM-DD'),
            lista_cantidad_clientes: [],
            lista_asesores: [],
            lista_codeudores_tabla: [],
            en_proceso: false,
            cancelado: false,
            criterio_asesor: '0',
            formas_pago: formas_pago,
            prueba: 0,
            estado_caja: false,
            solicitud: {
                id_solicitud: 0,
                importe_solicitud: 0,
                moneda: '0',
                lapso_capital: '0',
                nro_cuotas: 0,
                tasa: 0,
                fecha_desembolso: moment().format('YYYY-MM-DD'),
                fecha_primera_cuota: moment().format('YYYY-MM-DD'),
                estado: '',
                id_cliente: 0,
                nombre: 0,
                id_usuario: 0,
                tipo_garantia: '0',
                tipo_desembolso: '0',
                enviado: 0,
                accion: 0,
            },
            lista_monedas: lista_monedas,
            tipos_desembolsos: tipos_desembolsos,
            lapso_capitales: lapso_capitales,
            buscar: '',
            lista_cuotas_plan: [],
            lista_planespago: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            plan_pago: {
                cliente: '',
                ci: '',
                id_cliente: 0,
                lugar_expedicion: '',
                tipo_garantia: '',
                nro_cuotas: 0,
                lapso_capital: '',
                importe_solicitud: 0,
                moneda: '',
                fecha_desembolso: '',
                tasa: 0,
                id_plan_pago: 0,
                fecha_inicio_plan: 0,
                fecha_fin_plan: 0,
                total_pagar_plan: 0,
                estado_plan: 1,
                id_solicitud: 0,
                tipo_desembolso: '',
                tipo_solicitud: '',
                id_solicitud_origen: '',
                estado: 1,
                monto_original_historico:0,
                forma_pago_reprogramacion: '',
                plazo_meses: null,
                numero_cuotas_reprogramacion: 0,
                imagen_cliente:null,
                estado_civil:'',
                vivienda:'',
                ingreso_mensual:0,
                actividad:'',
                asesor:'',
                tipo_tasa:'',
            },
            cuota: {
                id_cuota: 0,
                total: 0,
                numero: 0
            },
            pago: {
                id_pago: 0,
                fecha_pago: moment().format('YYYY-MM-DD'),
                monto_pago: 0,
                id_cuota: 0,
            },
            offset: 2,
            plan_pago_vigente: false,
            lista_cuotas: [],
            tasa_plan: 'amortizable',
            cantidad_ahorro: 0,
            cantidad_seguro: 0,
            seguro: false,
            ahorro: false,
        }
    },
    computed: {
    
        saldoTotalParaReprogramar() {
            if (!this.lista_cuotas_plan || this.lista_cuotas_plan.length === 0) return 0;
            
            let totalDeuda = 0;
            
            // 1. SUMAR LA DEUDA NETA ACTUAL
            this.lista_cuotas_plan.forEach((cuota) => {
                // Ignorar cuotas ya pagadas completamente
                if (cuota.estado === 2) return; 

                // Sumamos los saldos restantes netos (Capital + Interés Acumulado + Mora Fija)
                totalDeuda += parseFloat(cuota.capital_neto || 0);
                totalDeuda += parseFloat(cuota.interes_acumulado_neto || 0);
                totalDeuda += parseFloat(cuota.mora_fija_neta || 0);
            });

            // 2. RESTAR LO SELECCIONADO EN LOS CHECKBOXES DE PAGOS PREVIOS
            if (this.pago_previo.seleccionado) {
                totalDeuda -= parseFloat(this.pago_previo.detalle_interes || 0);
                totalDeuda -= parseFloat(this.pago_previo.detalle_mora || 0);
            }

            // Evitar negativos por seguridad
            return this.formatNumero(totalDeuda) > 0 ? this.formatNumero(totalDeuda) : 0;
        },

        infoMoraActual() {
            // Verificamos que exista la lista
            if (!this.lista_cuotas_plan || this.lista_cuotas_plan.length === 0) return null;

            // Filtramos cuotas que están Pendientes (estado 1) Y tienen días de retraso (> 0)
            const vencidas = this.lista_cuotas_plan.filter(c => c.estado == 1 && c.dias_pasados > 0);

            if (vencidas.length === 0) return null; // No hay mora

            // Calculamos totales
            const totalMonto = vencidas.reduce((sum, c) => sum + parseFloat(c.total || 0), 0);
            const maxDias = Math.max(...vencidas.map(c => c.dias_pasados));

            return {
                cantidad: vencidas.length,
                monto: totalMonto,
                dias: maxDias
            };
        },
        isActived: function () {
            return this.pagination.current_page;
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
    },
    watch: {
        monto_adicional(val) {
            // const extra = parseFloat(val) || 0;
            // const base = parseFloat(this.monto_base_deuda) || 0;
            // this.plan_pago.importe_solicitud = (base + extra).toFixed(2);
            const extra = parseFloat(val) || 0;
            const base = parseFloat(this.saldoTotalParaReprogramar) || 0; // <--- USAR LA COMPUTED
            
            // this.plan_pago.importe_solicitud = (base + extra).toFixed(2);
        },

        saldoTotalParaReprogramar(newVal) {
            const extra = parseFloat(this.monto_adicional) || 0;
            const base = parseFloat(newVal) || 0;
            // this.plan_pago.importe_solicitud = (base + extra).toFixed(2);
            
            // Opcional: Actualizar el monto base que se pasa al componente hijo si usas props
            this.monto_base_deuda = base; 
        },
 
        'plan_pago.plazo_meses'(newPlazo) {
            this.calcularCuotasReprogramacion();
        },

        'plan_pago.forma_pago_reprogramacion'(newForma) {
            this.calcularCuotasReprogramacion();
        },
       
    },

    methods: {
        toggleCondonacion(tipo) {
            if (tipo === 'interes') {
                // Si lo activa, sugerimos el monto total por defecto
                if (this.pago_previo.condonar_interes) {
                    this.pago_previo.monto_condonar_interes = this.pago_previo.detalle_interes;
                } else {
                    this.pago_previo.monto_condonar_interes = 0;
                }
            }
            if (tipo === 'mora') {
                if (this.pago_previo.condonar_mora) {
                    this.pago_previo.monto_condonar_mora = this.pago_previo.detalle_mora;
                } else {
                    this.pago_previo.monto_condonar_mora = 0;
                }
            }
            this.calcularTotalesPago();
        },
        limpiarSeleccionPago() {
            this.idx_sel_interes = -1; 
            this.idx_sel_mora = -1;
            this.pago_previo = {
                seleccionado: false,
                total_pagar: 0,
                detalle_interes: 0,
                detalle_mora: 0,
                descripcion: '',
                ids_cuotas_interes: [],
                ids_cuotas_mora: []
            };

            this.pago_previo.condonar_interes = false;
            this.pago_previo.monto_condonar_interes = 0;
            this.pago_previo.condonar_mora = false;
            this.pago_previo.monto_condonar_mora = 0;
            this.pago_previo.motivo_condonacion = '';
        },
        calcularInteresALaFecha(cuota, index) {
            return parseFloat(cuota.interes_acumulado || 0);
        },
        
        esFilaHabilitada(index) {
            if (index === 0) return true;
            const cuotaAnterior = this.lista_cuotas_plan[index - 1];
            const fechaAnterior = moment(cuotaAnterior.fecha);
            const hoy = moment();

            return fechaAnterior.isBefore(hoy, 'day');
        },

        clickCheckInteres(index) {
            if (!this.esFilaHabilitada(index)) return;
            // Validamos que haya interés neto para cobrar
            if (parseFloat(this.lista_cuotas_plan[index].interes_acumulado_neto) <= 0) return;

            if (index === this.idx_sel_interes) {
                this.idx_sel_interes = index - 1;
            } else if (index < this.idx_sel_interes) {
                this.idx_sel_interes = index - 1; 
            } else {
                this.idx_sel_interes = index; 
            }
            this.calcularTotalesPago();
        },

        clickCheckMora(index) {
            if (!this.esFilaHabilitada(index)) return;
            // Validamos que haya mora neta para cobrar
            if (parseFloat(this.lista_cuotas_plan[index].mora_fija_neta) <= 0) return;

            if (index === this.idx_sel_mora) {
                this.idx_sel_mora = index - 1;
            } else if (index < this.idx_sel_mora) {
                this.idx_sel_mora = index - 1;
            } else {
                this.idx_sel_mora = index;
            }
            this.calcularTotalesPago();
        },

        calcularTotalesPago() {
            const inputState = {
                condonar_interes: this.pago_previo.condonar_interes,
                monto_condonar_interes: this.pago_previo.monto_condonar_interes,
                condonar_mora: this.pago_previo.condonar_mora,
                monto_condonar_mora: this.pago_previo.monto_condonar_mora,
                motivo_condonacion: this.pago_previo.motivo_condonacion
            };

            this.pago_previo = {
                seleccionado: false, total_pagar: 0, detalle_interes: 0, detalle_mora: 0,
                descripcion: '', ids_cuotas_interes: [], ids_cuotas_mora: [],
                ...inputState 
            };

            let sumaInteres = 0;
            let sumaMora = 0;
            let ultimaCuotaInt = 0;

            // Sumar Intereses Netos seleccionados
            if (this.idx_sel_interes > -1) {
                for (let i = 0; i <= this.idx_sel_interes; i++) {
                    const c = this.lista_cuotas_plan[i];
                    sumaInteres += parseFloat(c.interes_acumulado_neto || 0);
                    this.pago_previo.ids_cuotas_interes.push(c.id);
                    ultimaCuotaInt = c.numero;
                }
            }

            // Sumar Moras Netas seleccionadas
            if (this.idx_sel_mora > -1) {
                for (let i = 0; i <= this.idx_sel_mora; i++) {
                    const c = this.lista_cuotas_plan[i];
                    const mora = parseFloat(c.mora_fija_neta || 0);
                    if (mora > 0) {
                        sumaMora += mora;
                        this.pago_previo.ids_cuotas_mora.push(c.id);
                    }
                }
            }

            // Procesar totales y validaciones de condonación
            if (sumaInteres > 0 || sumaMora > 0) {
                this.pago_previo.seleccionado = true;
                this.pago_previo.detalle_interes = sumaInteres;
                this.pago_previo.detalle_mora = sumaMora;
                
                // Validaciones (Se mantienen igual a como las tenías)
                if (this.pago_previo.condonar_interes) {
                    if (this.pago_previo.monto_condonar_interes > sumaInteres) this.pago_previo.monto_condonar_interes = sumaInteres;
                    if(this.pago_previo.monto_condonar_interes <= 0) this.pago_previo.monto_condonar_interes = 1;
                } else { this.pago_previo.monto_condonar_interes = 0; }

                if (this.pago_previo.condonar_mora) {
                    if (this.pago_previo.monto_condonar_mora > sumaMora) this.pago_previo.monto_condonar_mora = sumaMora;
                    if(this.pago_previo.monto_condonar_mora <= 0) this.pago_previo.monto_condonar_mora = 1;
                } else { this.pago_previo.monto_condonar_mora = 0; }

                const totalNeto = (sumaInteres - this.pago_previo.monto_condonar_interes) + (sumaMora - this.pago_previo.monto_condonar_mora);
                this.pago_previo.total_pagar = totalNeto > 0 ? totalNeto : 0;

                let txt = [];
                if (sumaInteres > 0) txt.push(`Interés Acum. (Cuotas 1-${ultimaCuotaInt})`);
                if (sumaMora > 0) txt.push(`Multas`);
                this.pago_previo.descripcion = "Pago Previo: " + txt.join(" + ");
            }
        },

        // Auxiliar visual
        esInteresMarcado(index) { return index <= this.idx_sel_interes; },
        esMoraMarcada(index) { return index <= this.idx_sel_mora; },

        async reprogramar(item, tipo = 'REPROGRAMACION') {
            this.preloader = true;
            this.limpiarSeleccionPago();
            this.error_reprogramacion = null;
            this.lista_cuotas = [];
            this.plan_pago = {};
            this.sinCodeudor = false;
            this.modo_operacion = tipo; 
            this.monto_adicional = 0; 
            this.view = 2; 
            try {
                this.plan_pago.id_plan_pago = item.id;
                this.plan_pago.cliente = item.cliente;
                this.plan_pago.imagen_validate = item.imagen;
                this.plan_pago.imagen = '/img/cliente/' + (item.imagen || '');
                this.plan_pago.ci = item.ci;
                this.plan_pago.id_cliente = item.id_cliente;
                this.plan_pago.id_solicitud = item.id_solicitud;
                this.plan_pago.lugar_expedicion = item.lugar_expedicion;
                this.plan_pago.sexo = item.sexo;
                this.plan_pago.estado_civil = item.estado_civil;
                this.plan_pago.vivienda = item.vivienda;
                this.plan_pago.ingreso_mensual = item.ingreso_mensual;
                this.plan_pago.actividad = item.actividad;
                this.plan_pago.tipo_garantia = item.tipo_garantia;
                this.plan_pago.moneda = item.moneda;
                this.plan_pago.tipo_tasa = item.tipo_tasa;
                this.plan_pago.lapso_capital = item.lapso_capital;
                this.plan_pago.forma_pago_reprogramacion=this.plan_pago.lapso_capital;
                this.plan_pago.nro_cuotas = item.nro_cuotas;
                this.plan_pago.tasa = item.tasa;
                this.plan_pago.fecha_desembolso = item.fecha_desembolso;
                this.plan_pago.fecha_primera_cuota = item.fecha_primera_cuota;
                this.plan_pago.destino_prestamo = item.destino_prestamo;
                this.plan_pago.tipo_desembolso = item.tipo_desembolso;
                this.plan_pago.importe_solicitud = item.importe_solicitud;
                this.plan_pago.plazo = this.plan_pago.lapso_capital == 'Semanal' ? this.plan_pago.nro_cuotas / 4 : (this.plan_pago.lapso_capital == 'Quincenal' ? this.plan_pago.nro_cuotas / 2 : this.plan_pago.nro_cuotas / 1);
                this.lista_codeudores = this.getCodeudoresItem(item.id_solicitud);
                this.sinCodeudor = (!this.lista_codeudores || this.lista_codeudores.length === 0 || (this.lista_codeudores.length === 1 && this.lista_codeudores[0].nombre?.toUpperCase() === 'SIN GARANTE'));
                await this.getGarantiasSolicitud(item.id_solicitud);
                await this.getCuotas({ id_plan_pago: item.id });
                const data = await this.obtenerCalculoReprogramacion(item.id); 
                this.monto_base_deuda = parseFloat(data.monto_a_reprogramar);

            } catch (error) {
                this.error_reprogramacion = error.response?.data?.message || 'Error al validar el préstamo.';
                const saldoRef = parseFloat(item.saldo_pendiente || item.importe_solicitud);
                this.plan_pago.importe_solicitud = saldoRef;
                this.monto_base_deuda = saldoRef;

            } finally {
                this.preloader = false;
            }
        },

        verFicha(idCliente) {
            this.$refs.modalClienteRef.abrirModal(idCliente);
        },
  
        async verDetalleOriginal(id_solicitud_original) {
            this.preloader = true;
            try {
                const response = await axios.get(`/solicitud/detalle-completo/${id_solicitud_original}`);
                this.datos_original_modal = response.data;
                $('#modalDetalleOriginal').modal('show'); 

            } catch (error) {
                console.error('Error al cargar datos originales:', error);
                Swal.fire('Error', 'No se pudo cargar el historial completo del crédito original.', 'error');
            } finally {
                this.preloader = false;
            }
        },
   
        getEstadoCuota(cuota) {
            if (cuota.estado == 2) return { texto: 'Pagado', clase: 'bg-success' };
            if (cuota.estado == 3) return { texto: 'Parcial', clase: 'bg-warning text-dark' };
            if (cuota.estado == 0) return { texto: 'Anulado', clase: 'bg-dark' };
            if (cuota.estado == 1) {
                return cuota.dias_pasados > 0 
                    ? { texto: `Vencida (${cuota.dias_pasados}d)`, clase: 'bg-danger' }
                    : { texto: 'Pendiente', clase: 'bg-info text-white' };
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
        
        async registrarSolicitudReprogramacion() {
            if (!this.lista_cuotas || this.lista_cuotas.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Acción requerida', text: 'Por favor, primero presione "Calcular Nuevo Plan de Pagos".' });
                return;
            }
            if (this.modo_operacion === 'REFINANCIAMIENTO' && (!this.monto_adicional || this.monto_adicional <= 0)) {
                Swal.fire({ icon: 'warning', title: 'Dato faltante', text: 'Para refinanciar, debe ingresar un Monto Adicional mayor a 0.' });
                return;
            }
            this.reprogramando_solicitud = true;
            const textoAccion = this.modo_operacion === 'REFINANCIAMIENTO' ? 'Refinanciamiento' : 'Reprogramación';
            this.plan_pago.importe_solicitud=this.saldoTotalParaReprogramar;
            this.plan_pago.fecha_primera_cuota=this.solicitud.fecha_primera_cuota;
            Swal.fire({
                title: `Registrando ${textoAccion}...`,
                text: 'Por favor espere.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await axios.post('/solicitud/registrar-especial', {
                    plan_pago: this.plan_pago,
                    tipo_operacion: this.modo_operacion,
                    monto_adicional: this.monto_adicional,
                    pago_intereses: (this.pago_previo && this.pago_previo.seleccionado) ? this.pago_previo : null
                });
                Swal.fire({
                    icon: 'success',
                    title: '¡Solicitud Registrada!',
                    text: response.data.message
                });
                this.cerrarReprogramacion(); 
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Registrar',
                    text: error.response?.data?.message || 'No se pudo registrar la solicitud.'
                });
            } finally {
                this.reprogramando_solicitud = false;
            }
        },

        async generarReportePlanReprogramacion() {
            if (!this.lista_cuotas || this.lista_cuotas.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Acción requerida',
                    text: 'Por favor, primero presione "Calcular Nuevo Plan de Pagos".',
                });
                return;
            }

            // 2. Mostrar alerta de carga
            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Por favor espere mientras preparamos su PDF.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // 3. Enviar los datos del plan Y las cuotas simuladas al backend
                this.plan_pago.importe_solicitud=this.saldoTotalParaReprogramar;
                const response = await axios.post('/reportes/plan-pago-reprogramado-pdf', {
                    // Enviamos ambos objetos:
                    plan_pago: this.plan_pago, // Contiene datos del cliente y los nuevos términos
                    cuotas: this.lista_cuotas     // Contiene las cuotas calculadas
                }, {
                    responseType: 'blob' // ¡Esencial para recibir un PDF!
                });

                // 4. Procesar la respuesta (el PDF)
                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                
                // 5. Crear un enlace temporal para la descarga
                const link = document.createElement('a');
                link.href = url;
                
                // Formatear la fecha actual para el nombre del archivo
                const fechaHoy = new Date().toISOString().slice(0, 10);
                link.setAttribute('download', `PlanReprogramado-${this.plan_pago.ci}-${fechaHoy}.pdf`);
                
                document.body.appendChild(link);
                link.click();

                // 6. Limpiar y cerrar
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                Swal.close(); // Cerrar la alerta de carga

            } catch (error) {
                console.error('Error al generar el PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Generar PDF',
                    text: 'No se pudo generar el reporte. Intente de nuevo.',
                });
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

        async generarPlanReprogramacion() {
            try {
                if (!this.plan_pago.forma_pago_reprogramacion || !this.plan_pago.numero_cuotas_reprogramacion) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos incompletos',
                        text: 'Por favor, complete la "Nueva Forma de Pago" y el "Nuevo Plazo" primero.',
                    });
                    return;
                }
                this.solicitud.tasa = this.plan_pago.tasa;
                this.solicitud.tipo_tasa = this.plan_pago.tipo_tasa;
                // El importe base es el saldo actual pendiente; en refinanciamiento se suma el monto adicional
                const saldoBase = parseFloat(this.saldoTotalParaReprogramar) || 0;
                const extra = parseFloat(this.monto_adicional) || 0;
                this.solicitud.importe_solicitud = saldoBase + extra;
                // --- Datos Nuevos de la Reprogramación ---
                this.solicitud.lapso_capital = this.plan_pago.forma_pago_reprogramacion;
                this.solicitud.nro_cuotas = this.plan_pago.numero_cuotas_reprogramacion;
                // --- Fechas Nuevas (Como solicitaste) ---
                const fechaHoy = moment();
                this.solicitud.fecha_desembolso = fechaHoy.format('YYYY-MM-DD'); // Fecha de Hoy
                this.solicitud.fecha_primera_cuota = this.calcularNuevaPrimeraCuota(fechaHoy);
                await this.generarPlanPagosGeneral();

            } catch (error) {
                console.error("Error al generar el plan de reprogramación:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Cálculo',
                    text: 'No se pudo simular el plan de pagos. Verifique los datos.',
                });
            }
        },

        calcularNuevaPrimeraCuota(fechaBase) {
            const formaPago = this.plan_pago.forma_pago_reprogramacion;
            let nuevaFecha = moment(fechaBase); // Clonar la fecha base (hoy)
            switch (formaPago) {
                case 'Mensual':
                    nuevaFecha.add(1, 'month');
                    break;
                case 'Quincenal':
                    nuevaFecha.add(15, 'days');
                    break;
                case 'Semanal':
                    nuevaFecha.add(7, 'days'); // O add(1, 'week')
                    break;
                case 'Diario':
                    nuevaFecha.add(1, 'day');
                    break;
                default:
                    // Por defecto, 1 mes
                    console.warn("Forma de pago desconocida, usando 1 mes por defecto.");
                    nuevaFecha.add(1, 'month');
                    break;
            }
            return nuevaFecha.format('YYYY-MM-DD');
        },

        calcularCuotasReprogramacion() {
            // Usamos parseFloat para asegurar que 'plazo_meses' sea un número
            const plazo = parseFloat(this.plan_pago.plazo_meses);
            const formaPago = this.plan_pago.forma_pago_reprogramacion;

            // Si no hay datos válidos, resetea a 0
            if (!plazo || !formaPago || plazo <= 0) {
                this.plan_pago.numero_cuotas_reprogramacion = 0;
                return;
            }

            // Aplicamos la lógica de cálculo
            if (formaPago === 'Mensual') {
                // 1 cuota por mes
                this.plan_pago.numero_cuotas_reprogramacion = plazo * 1;
            } else if (formaPago === 'Quincenal') {
                // 2 cuotas por mes
                this.plan_pago.numero_cuotas_reprogramacion = plazo * 2;
            } else if (formaPago === 'Semanal') {
                // 4 cuotas por mes (aprox.)
                this.plan_pago.numero_cuotas_reprogramacion = plazo * 4;
            }
        },
        cerrarReprogramacion() {
            this.view = 0;
        },

        async obtenerCalculoReprogramacion(idPlanPago) {
            try {
                const response = await axios.get(`/reprogramacion/calcular/${idPlanPago}`);
                return response.data;
            
            } catch (error) {
                console.error("Error en obtenerCalculoReprogramacion:", error);
                throw error; // Relanza el error
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

        formatFecha(fecha) {
            return moment(fecha).format('DD/MM/YYYY');
        },
        formatNumero(numero) {
            // maximumFractionDigits: 0 asegura que no se muestren decimales
            return new Intl.NumberFormat('es-BO', { maximumFractionDigits: 0 }).format(numero);
        },

        exportExcelPlanPagos() {
            axios({
                url: '/export-planes_pago', // La ruta que definiste en Laravel
                method: 'GET',
                responseType: 'blob', // Para manejar la respuesta como un archivo binario
            })
                .then((response) => {
                    // Crear un enlace para descargar el archivo
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'custom_data.xlsx'); // Nombre del archivo
                    document.body.appendChild(link);
                    link.click();
                })
                .catch((error) => {
                    console.error("Error exporting data:", error);
                });
        },

        seleccionarLapsoCapital() {
            if (this.solicitud.lapso_capital == 'Mensual') {
                const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                const fechaPrimeraCuota = fechaDesembolso.add(1, 'month');
                this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
            } else {
                if (this.solicitud.lapso_capital == 'Quincenal') {
                    const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                    const fechaPrimeraCuota = fechaDesembolso.add(15, 'days');
                    this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                } else {
                    if (this.solicitud.lapso_capital == 'Semanal') {
                        const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                        const fechaPrimeraCuota = fechaDesembolso.add(7, 'days');
                        this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                    } else {
                        if (this.solicitud.lapso_capital == 'Diario') {
                            const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                            const fechaPrimeraCuota = fechaDesembolso.add(1, 'days');
                            this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                        }
                    }
                }
            }
        },

        async getAmortizacionesPlan(id_plan_pago) {
            try {
                const response = await axios.get('/get_amortizaciones_plan', {
                    params: { id_plan_pago: id_plan_pago }
                });
                return response.data;
            } catch (error) {
                console.error('Error al obtener las cuotas del plan:', error.message);
                return [];
            }
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
        obtenerFechaActual() {
            return moment().format('YYYY-MM-DD');
        },
        async getCodeudoresTabla() {
            const url = '/get_codeudores_solicitudes';

            await axios.get(url).then((response) => {
                console.log(response.data);
                this.lista_codeudores_tabla = response.data;
            })
                .catch(function (error) {
                    console.log(error);
                })
        },
        getCodeudoresItem(solicitudId) {
            return this.lista_codeudores_tabla.filter(
                (codeudor) => codeudor.id_solicitud === solicitudId
            );
        },
        generarContrato(item) {
            const url = '/generar_contrato?nombre_cliente=' + item.cliente + '&ci=' + item.ci +
                '&lugar_expedicion=' + item.lugar_expedicion + '&monto_total=' + item.total_pagar_plan + '&nro_cuotas=' + item.nro_cuotas + '&lapso_capital=' + item.lapso_capital + '&fecha_inicio=' + item.fecha_desembolso
                + '&id_solicitud=' + item.id_solicitud;
            window.open(url, '_blank');
        },

        cambiarEstado() {
            this.cantidad_ahorro = this.ahorro ? this.cantidad_ahorro : 0;
            this.cantidad_seguro = this.seguro ? this.cantidad_seguro : 0;
        },
        generalPlanPagosGeneral() {
            if (this.solicitud.importe_solicitud == 0 || this.solicitud.importe_solicitud == ''
                || this.solicitud.moneda == '' || this.solicitud.lapso_capital == ''
                || this.solicitud.nro_cuotas == 0 || this.solicitud.nro_cuotas == ''
                || this.solicitud.tipo_desembolso == '' || this.solicitud.tasa == '' || this.solicitud.tasa == 0) {
                this.solicitud.enviado = 1;
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Debe rellenar todos los campos para generar plan de pago',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                });
            } else {
                if (this.tasa_plan == 'amortizable') {
                    this.generarPlanPagos();
                    console.log('amortizable');
                } else {
                    if (this.tasa_plan == 'fija') {
                        this.generarPlanPagosTasaFija();
                        console.log('fija');
                    }
                }
            }
        },
        retornarDiasPlanPago(lapso_capital) {
            if (lapso_capital == 'Diario') {
                return 1;
            } else if (lapso_capital == 'Semanal') {
                return 7;
            } else if (lapso_capital == 'Quincenal') {
                return 15;
            } else if (lapso_capital == 'Mensual') {
                return 30;
            }
        },

        activarPlanPago(item) {
            let activado = false;
            axios.post('/activar_planpago', { id_planpago: item.id }).then((response) => {
                console.log(response);
                activado = true;
            })
                .catch((error) => {
                    console.log(error.message);
                })
                .finally(() => {
                    if (activado) {
                        Swal.fire({
                            position: 'top-right',
                            icon: 'success',
                            title: 'Operación exitosa',
                            text: 'Plan de pago activado con exito!',
                            timer: 1500
                        });
                        this.getPlanesPago(1);
                    } else {
                        console.log('ocurrio un error al activar');
                    }

                })
        },
        async consultarPlanVigente(plan_pago_id) {
            let resultado = 0;
            await axios.get('/consulta_plan_pago_vigente?id_planpago=' + plan_pago_id)
                .then((response) => {
                    console.log(response);
                    resultado = response.data.respuesta;
                })
                .catch((error) => {
                    console.log(error.message);
                })
                .finally(() => {
                    if (resultado == 0) {
                        this.plan_pago_vigente = false;
                    } else {
                        if (resultado == 1) {
                            this.plan_pago_vigente = true;
                        }
                    }
                })
        },
        async anularPlanPago(item) {
            // 1. Verificamos si tiene pagos
            await this.consultarPlanVigente(item.id);

            if (this.plan_pago_vigente) {
                // CASO: No se puede anular
                console.log('El plan de pago no se puede anular, porque ya tiene pagos cancelados');
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'No se puede anular el plan de pago porque ya tiene cuotas canceladas.',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                });
            } else {
                // CASO: Sí se puede anular -> PREGUNTAMOS CONFIRMACIÓN
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Está a punto de anular este plan de pagos. ¡Esta acción no se puede revertir!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33', // Rojo para acciones destructivas
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    // Si el usuario confirma (clic en "Sí, anular")
                    if (result.isConfirmed) {
                        
                        let anulado_planpago = false;
                        this.preloader = true; // Opcional: mostrar spinner mientras procesa

                        await axios.post('/anular_planpago', { id_planpago: item.id })
                            .then((response) => {
                                console.log(response);
                                anulado_planpago = true;
                            })
                            .catch((error) => {
                                console.log(error.message);
                                Swal.fire('Error', 'Ocurrió un error al intentar anular.', 'error');
                            })
                            .finally(() => {
                                this.preloader = false; // Ocultar spinner
                                
                                if (anulado_planpago) {
                                    Swal.fire({
                                        position: 'top-right',
                                        icon: 'success',
                                        title: 'Operación exitosa',
                                        text: 'Plan de pago anulado con éxito!',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    // Recargar la tabla
                                    this.getPlanesPago(1);
                                }
                            });
                    }
                });
            }
        },
       
        gestionarEstado(estado_plan) {
            if (estado_plan == 1) {
                return 'Activo';
            } else if (estado_plan == 0) {
                return 'Anulado';
            }
            else if (estado_plan == 2) {
                return 'Cancelado';
            }
            else if (estado_plan == 10) {
                return 'Amortizado';
            }
        },

        cerrarModalCuotas() {
            this.view = 0;
            this.plan_pago = {};
            this.lista_cuotas_plan = [];
        },

        async abrirModalVerCuotas(item) {
            this.preloader = true;
            this.prueba = 0;
            this.plan_pago.id_plan_pago = item.id;
            this.plan_pago.id = item.id;
            this.plan_pago.cliente = item.cliente;
            this.plan_pago.ci = item.ci;
            this.plan_pago.lugar_expedicion = item.lugar_expedicion;
            this.plan_pago.tipo_garantia = item.tipo_garantia;
            this.plan_pago.nro_cuotas = item.nro_cuotas;
            this.plan_pago.lapso_capital = item.lapso_capital;
            this.plan_pago.tasa = item.tasa;
            this.plan_pago.fecha_inicio_plan = item.fecha_inicio_plan;
            this.plan_pago.fecha_ultima_amortizacion = item.fecha_ultima_amortizacion;
            this.plan_pago.fecha_fin_plan = item.fecha_fin_plan;
            this.plan_pago.total_pagar_plan = item.total_pagar_plan;
            this.plan_pago.estado_plan = item.estado_plan;
            this.plan_pago.id_cliente = item.id_cliente;
            this.plan_pago.id_solicitud = item.id_solicitud;
            this.plan_pago.tipo_desembolso = item.tipo_desembolso;
            this.plan_pago.tipo_solicitud = item.tipo_solicitud;
            this.plan_pago.tipo_tasa = item.tipo_tasa;
            this.plan_pago.moneda = item.moneda;
            this.plan_pago.estado = item.estado_plan;
            this.plan_pago.id_solicitud_origen = item.id_solicitud_origen;
            this.plan_pago.imagen_cliente=item.imagen;
            this.plan_pago.estado_civil=item.estado_civil;
            this.plan_pago.vivienda=item.vivienda;
            this.plan_pago.ingreso_mensual=item.ingreso_mensual;
            this.plan_pago.actividad=item.actividad;
            this.plan_pago.asesor=item.asesor;
            await this.getCuotas(this.plan_pago);
            console.log('lista amortizaciones: ', this.lista_amortizaciones);
            const cant = this.lista_planes_pago_ligados.length;
            console.log('CANTIDAD PLANES LIGADOS: ', cant);
            this.preloader = false;
            this.view = 1;

        },
        async getCuotas(item) {
            this.lista_cuotas_plan = [];
            // Llama al endpoint unificado que acabamos de ajustar
            await axios.get('/listar_amortizaciones_cuotas_planpago?id_plan_pago=' + item.id_plan_pago)
                .then((response) => {
                    console.log("Cuotas cargadas unificadas:", response.data);
                    // Como el backend ahora retorna $resultado['cuotas'], data ya es el array
                    this.lista_cuotas_plan = response.data; 
                })
                .catch((error) => {
                    console.log(error.message);
                })
        },
        handleBusqueda(filtros) {
            this.filtros_actuales = filtros; // Guárdalos en data() para usarlos al paginar
            this.getPlanesPago(1);
        },

        async getPlanesPago(page) {
            const f = this.filtros_actuales || {};
            await axios.get('/get_planespago?page=' + page +
                '&criterio=' + (f.criterio || 'cliente.nombre') +
                '&buscar=' + (f.buscar || '') +
                '&opcion_asesor=' + (f.opcion_asesor || 0) +
                '&estado_credito=' + (f.estado_credito || 'todos') +
                '&fecha_inicio=' + (f.fecha_inicio || '') +
                '&fecha_fin=' + (f.fecha_fin || ''))
                .then((response) => {
                    this.lista_planespago = response.data;
                })
                .catch((error) => {
                    console.log(error.message);
                });
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.getPlanesPago(page);
        },

    },
    async mounted() {
        this.preloader = true;
        await this.getCodeudoresTabla();
        await this.getAsesores();
        this.preloader = false;
    }
}

</script>
<style scoped>
    @import './styles/frmPlanPago.css';
</style>
