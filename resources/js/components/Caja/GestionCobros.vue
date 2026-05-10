<template>
    <div class="card shadow-lg border-0">
        
        <div v-if="vistaInterna === 'lista'">
            <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 text-center">
                    <h5 class="header-title my-0 fw-bold text-white text-uppercase">
                        <i class="fas fa-cash-register me-2"></i> Gestión de Pago de Cuotas/Ordenes
                    </h5>
                </div>
                <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
            </div>
            
            <div class="card-body">
                <ul class="nav nav-pills nav-justified mb-4 bg-white rounded list-header-pay p-3">
                    <li class="nav-item text-dark">
                        <a class="text-decoration-none py-1 w-100 fw-bold text-uppercase d-flex align-items-center justify-content-center border border-1" 
                        :class="tabActual === 'normal' ? 'bg-warning text-white border-warning' : 'text-dark bg-white hover-effect border-secondary'" 
                        href="#" @click.prevent="tabActual = 'normal'" style="cursor: pointer; transition: all 0.2s;">
                            Cuotas Normales
                        </a>
                    </li>
                    <li class="nav-item text-dark">
                        <a class="text-decoration-none py-1 w-100 fw-bold text-uppercase d-flex align-items-center justify-content-center border border-1" 
                        :class="tabActual === 'repro' ? 'bg-info text-white border-info' : 'text-dark bg-white hover-effect border-secondary'"
                        href="#" @click.prevent="cambiarTabRepro" style="cursor: pointer; transition: all 0.2s;">
                            <!-- <i class="fas fa-history me-2"></i>  -->
                            Ordenes de pago
                        </a>
                    </li>
                </ul>

                <div class="bg-white p-3 rounded border" style="min-height: 400px;">
                    
                    <div v-if="tabActual === 'normal'" class="fade-in-animation">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-filter text-muted"></i></span>
                                    <select @change="buscarPlanPago()" v-model="filtrosPlan.criterio" class="form-select">
                                        <option value="plan_pago.id">Cod. Credito</option>
                                        <option value="cliente.nombre">Nombre cliente</option>
                                        <option value="cliente.ci">CI</option>
                                    </select>
                                    <input v-model="filtrosPlan.fecha_inicio" type="date" class="form-control" @change="buscarPlanPago()">
                                    <input v-model="filtrosPlan.fecha_fin" type="date" class="form-control" @change="buscarPlanPago()">
                                    <input v-model="filtrosPlan.buscar" type="text" class="form-control" placeholder="Buscar..." @input="buscarPlanPago()">
                                    <button class="btn btn-success" @click="buscarPlanPago">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive text-uppercase" style="font-size:12px;">
                            <table class="table mb-0 table-hover table-striped table-sm align-middle">
                                <thead class="text-uppercase text-white">
                                    <tr class="align-middle table-warning">
                                        <th class="text-dark fw-bold"># Cred.</th>
                                        <th class="text-dark fw-bold">Cliente</th>
                                        <th class="text-dark fw-bold">CI</th>
                                        <th class="text-dark fw-bold text-center">Desembolso</th>
                                        <th class="text-dark fw-bold text-end">Monto</th>
                                        <th class="text-dark fw-bold text-center">Asesor</th>
                                        <th class="text-dark fw-bold text-center">F. Inicio</th>
                                        <th class="text-dark fw-bold text-center">F. Fin</th>
                                        <th class="text-dark fw-bold text-center">Cuotas</th>
                                        <th class="text-dark fw-bold text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in lista_planespago" :key="item.id">
                                        <td class="fw-bold text-center">{{ item.id }}</td>
                                        <td class="fw-bold">{{ item.cliente }}</td>
                                        <td>{{ item.ci }}</td>
                                        <td class="text-center">{{ formatDate(item.fecha_desembolso) }}</td>
                                        <td class="fw-bold text-end">{{ item.total_pagar_plan }}</td>
                                        <td class="text-center">{{ item.asesor }}</td>
                                        <td class="text-center">{{ formatDate(item.fecha_inicio_plan) }}</td>
                                        <td class="text-center">{{ formatDate(item.fecha_fin_plan) }}</td>
                                        <td class="text-center">
                                            {{ item.nro_cuotas }} <span class="text-muted" style="font-size:10px;">({{ item.lapso_capital }})</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-success btn-sm px-3 shadow-sm" @click="verDetallePlan(item)">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="lista_planespago.length === 0">
                                        <td colspan="10" class="text-center py-4 text-muted">No se encontraron planes vigentes.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="tabActual === 'repro'" class="fade-in-animation">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary fw-bold text-uppercase my-0">
                                <i class="fas fa-list-alt me-2"></i> Órdenes de Cobro Pendientes
                            </h6>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <select v-model="filtrosRepro.criterio" class="form-select" style="max-width: 300px;">
                                        <option value="cliente.nombre">Nombre</option>
                                        <option value="cliente.ci">CI</option>
                                        <option value="plan_pagos.id">Cód. Crédito</option>
                                    </select>
                                    
                                    <input type="text" class="form-control ms-1" 
                                        v-model="filtrosRepro.buscar" 
                                        placeholder="Escriba para buscar..." 
                                        @keyup.enter="cargarOrdenesReprogramacion">
                                    
                                    <button class="btn btn-success" @click="cargarOrdenesReprogramacion">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    
                                    <!-- <button v-if="filtrosRepro.buscar" class="btn btn-outline-secondary" @click="limpiarFiltroRepro">
                                        <i class="fas fa-times"></i>
                                    </button> -->
                                    <button class="btn btn-info btn-sm ms-1" @click="cargarOrdenesReprogramacion">
                                        <i class="fas fa-sync-alt"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="preloader" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-sm table-hover table-striped table-sm align-middle mb-0">
                                <thead class="table-warning text-uppercase small text-center">
                                    <tr>
                                        <th># Orden</th>
                                        <th>Fecha</th>
                                        <th class="text-start">Cliente</th>
                                        <!-- <th>Ref.</th> -->
                                        <th class="text-end border-start">Int. Calc.</th>
                                        <th class="text-end">Int. Cond.</th>
                                        <th class="text-end border-start">Mora Calc.</th>
                                        <th class="text-end">Mora Cond.</th>
                                        <th class="text-end border-start bg-warning bg-opacity-25">Total Cobrar</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="small text-center">
                                    <tr v-for="orden in lista_ordenes_repro" :key="orden.id">
                                        <td class="fw-bold text-primary">#{{ orden.id }}</td>
                                        <td>{{ orden.created_at_fmt }}</td>
                                        <td class="text-start fw-bold text-uppercase">{{ orden.cliente_nombre }}</td>
                                        <!-- <td><span class="badge bg-secondary">CRED-{{ orden.id_plan_pago_origen }}</span></td> -->
                                        <td class="text-end border-start">{{ orden.monto_interes_calculado }}</td>
                                        <td class="text-end text-muted fst-italic bg-light">
                                            <span v-if="orden.monto_condonado_interes > 0">-{{ orden.monto_condonado_interes }}</span>
                                            <span v-else>-</span>
                                        </td>
                                        
                                        <td class="text-end text-danger border-start">{{ orden.monto_mora_calculado }}</td>
                                        <td class="text-end text-muted fst-italic bg-light">
                                            <div v-if="orden.monto_condonado_mora > 0">
                                                -{{ orden.monto_condonado_mora }}
                                                <i v-if="orden.motivo_condonacion" 
                                                class="fas fa-info-circle text-info ms-1" 
                                                :title="'Motivo: ' + orden.motivo_condonacion"
                                                style="cursor: help;"></i>
                                            </div>
                                            <span v-else>-</span>
                                        </td>
                                        
                                        <td class="text-end fs-6 border-start bg-success bg-opacity-10" style="font-weight: 800; color:green">
                                            {{ orden.total_a_pagar }}
                                        </td>
                                        
                                        <td>
                                            <span v-if="orden.estado == 1" class="badge bg-warning text-dark border border-warning">POR PAGAR</span>
                                            <span v-else-if="orden.estado == 2" class="badge bg-success">PAGADO</span>
                                            <span v-else class="badge bg-secondary">NO DISP.</span>
                                        </td>

                                        
                                        
                                        <td>
                                            <button v-if="orden.estado == 1" 
                                                    class="btn btn-info btn-sm px-3 fw-bold" 
                                                    @click="cobrarOrdenReprogramacion(orden)">
                                                <i class="fas fa-money-bill-wave me-1"></i> Cobrar
                                            </button>
                                            <button v-else-if="orden.estado == 2" 
                                                    class="btn btn-outline-secondary btn-sm px-3" 
                                                    @click="imprimirRecibo(orden)">
                                                <i class="fas fa-print me-1"></i> Recibo
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="lista_ordenes_repro.length === 0">
                                        <td colspan="11" class="text-center py-5 text-muted fst-italic bg-white">
                                            <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                            No hay órdenes de reprogramación pendientes.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="vistaInterna === 'detalle'" class="card border-0">
            <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 text-center">
                    <h5 class="header-title my-0 fw-bold text-white text-uppercase">
                        PLAN DE PAGO | COD: {{ plan_pago.id }} | cliente: {{ plan_pago.cliente }}
                    </h5>
                </div>
                <button @click="cerrarDetallePlan()" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col text-center">
                        <a @click="generarPdf(plan_pago.id)" class="btn btn-info text-white me-2">
                            <i class="fas fa-file-pdf"></i> Generar PDF
                        </a>
                        <a @click="openPaymentModal()" class="btn btn-success">
                            <i class="fas fa-dollar-sign"></i> Cobrar Cuota/s
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded border bg-light h-100">
                            <div class="d-flex justify-content-between mb-2"><strong>Cliente:</strong> <span>{{ plan_pago.cliente }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><strong>CI:</strong> <span>{{ plan_pago.ci }} {{ plan_pago.lugar_expedicion }}</span></div>
                            <div class="d-flex justify-content-between"><strong>Garantía:</strong> <span>{{ plan_pago.tipo_garantia }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border bg-light h-100">
                            <div class="d-flex justify-content-between mb-2"><strong>Plazo:</strong> <span>{{ plan_pago.nro_cuotas }} {{ plan_pago.lapso_capital }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><strong>Monto:</strong> <span>{{ plan_pago.total_pagar_plan }} {{ plan_pago.moneda }}</span></div>
                            <div class="d-flex justify-content-between"><strong>Forma Pago:</strong> <span>{{ plan_pago.lapso_capital }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border bg-light h-100">
                            <div class="d-flex justify-content-between mb-2"><strong>Inicio:</strong> <span>{{ formatDate(plan_pago.fecha_inicio_plan) }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><strong>Fin:</strong> <span>{{ formatDate(plan_pago.fecha_fin_plan) }}</span></div>
                            <div v-if="dias_mora > 0" class="d-flex justify-content-between">
                                <span class="fw-bold text-danger">Mora General:</span>
                                <span class="fw-bold text-danger">{{ dias_mora }} Días</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="font-size:12px;">
                    <table class="table mb-4 table-striped table-sm table-hover border table-cuotas">
                        
                        <thead class="text-dark table-warning">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Capital</th>
                                <th class="text-center">Interes</th>
                                <th class="text-center">Saldo Cap.</th>
                                <th class="text-center">Total Bs</th>
                                <th class="text-center">Días Trans.</th>
                                
                                <th class="text-center text-primary">Int. Devengado</th>
                                <th class="text-center text-danger">Int. Moratorio</th>
                                
                                <th class="text-center">Int. Acumulado</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Pagar</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(cuota, index) in lista_cuotas_plan" :key="index" class="align-middle">
                                <td class="text-center">{{ cuota.numero }}</td>
                                <td class="text-center">{{ formatDate(cuota.fecha) }}</td>
                                
                                <!-- <td class="text-center fw-bold text-dark">{{ formatNumero(cuota.capital_neto) }}</td> -->
                                 <td class="text-center align-middle">
                                    <div class="fw-bold text-dark fs-6">{{ formatNumero(cuota.capital_neto) }}</div>
                                    <div v-if="cuota.capital_pagado_total > 0" class="text-success fw-bold lh-1 mt-1" style="font-size: 0.65rem;">
                                        Pagado: {{ formatNumero(cuota.capital_pagado_total) }} <br>
                                        ({{ cuota.porcentaje_capital_pagado }}%)
                                    </div>
                                </td>
                                
                                <td class="text-center">{{ formatNumero(cuota.interes) }}</td>
                                <td class="text-center">{{ formatNumero(cuota.saldo_capital) }}</td>
                                <td class="text-center">{{ formatNumero(cuota.total) }}</td>
                                <td class="text-center">{{ cuota.estado == 0 ? '---' : cuota.dias_transcurridos }}</td>
                                
                                <td class="text-center text-primary">
                                    {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_devengado_neto) }}
                                </td>
                                <td class="text-center text-danger fw-bold">
                                    {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_moratorio_neto) }}<br>
                                    <span v-if="parseFloat(cuota.interes_moratorio_neto) > 0" class="text-muted" style="font-size: 0.6rem;">({{ cuota.dias_pasados }} d)</span>
                                </td>
                                
                                <!-- <td class="text-center fw-bold text-primary border-start border-end">
                                    {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_acumulado_neto) }}
                                </td> -->
                                <td class="text-center align-middle border-start border-end">
                                    <div class="fw-bold text-primary fs-6">
                                        {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_acumulado_neto) }}
                                    </div>
                                    <div v-if="cuota.interes_pagado_total > 0" class="text-success fw-bold lh-1 mt-1" style="font-size: 0.65rem;">
                                        Pagado: {{ formatNumero(cuota.interes_pagado_total) }} <br>
                                        ({{ cuota.porcentaje_interes_pagado }}%)
                                    </div>
                                </td>
                                
                                <td class="text-center">
                                    <div v-if="parseFloat(cuota.mora_fija_neta) > 0 && cuota.estado != 2" class="mb-1">
                                        <span class="badge bg-danger text-white">
                                            Mora - {{ cuota.dias_pasados }} Dias
                                        </span>
                                    </div>
                                    <span class="badge rounded-pill" :class="getEstadoCuota(cuota).clase">
                                        {{ getEstadoCuota(cuota).texto }}
                                    </span>
                                </td>
                                
                                <!-- <td class="text-center">
                                    <input type="checkbox" v-model="selectedCuotas" :value="cuota.id" 
                                        :disabled="!isCheckboxEnabled(index)" 
                                        v-if="cuota.estado == 1 || cuota.estado == 3"
                                        style="transform: scale(1.3); cursor: pointer;">
                                </td> -->
                                <td class="text-center">
                                    <input type="checkbox" 
                                        :checked="selectedCuotas.includes(cuota.id)"
                                        :disabled="!isCheckboxEnabled(index)" 
                                        v-if="cuota.estado == 1 || cuota.estado == 3"
                                        @change="toggleCuotaSelection(index, cuota.id, $event.target.checked)"
                                        style="transform: scale(1.3); cursor: pointer;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

       
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    
                    <div class="modal-header bg-success text-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white text-success rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-hand-holding-usd fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="paymentModalLabel">Procesar Cobro de Cuota(s)</h5>
                                <small class="opacity-75">Cliente: {{ plan_pago.cliente }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" @click="cerrarModalCobrarCuotas"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="row g-4">
                            
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                        <h6 class="fw-bold text-uppercase text-muted mb-0"><i class="fas fa-receipt me-2"></i>Resumen de Deuda</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush mb-3">
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                                <span class="text-muted">Capital</span>
                                                <span class="fw-bold">{{ formatNumero(totalCapital) }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                                <span class="text-muted">Interés Acumulado</span>
                                                <span class="fw-bold">{{ formatNumero(totalInteres) }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                                <span class="text-muted text-danger">Multa por Mora</span>
                                                <span class="fw-bold text-danger">{{ formatNumero(totalMulta) }}</span>
                                            </li>
                                        </ul>
                                        
                                        <div class="d-flex justify-content-between align-items-end mt-2">
                                            <span class="text-uppercase fw-bold text-secondary small">Total Bruto</span>
                                            <span class="fs-4 fw-bold text-dark">{{ plan_pago.moneda }} {{ formatNumero(totalPagar) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">
                                
                                <div class="card border-warning border-opacity-50 shadow-sm mb-3" v-if="totalInteres > 0 || totalMulta > 0">
                                    <div class="card-header bg-warning bg-opacity-10 py-2">
                                        <h6 class="fw-bold text-warning-emphasis mb-0 small text-uppercase">
                                            <i class="fas fa-gift me-2"></i>Opciones de Condonación
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-sm-6" v-if="totalInteres > 0">
                                                <label class="form-label small fw-bold text-muted mb-1">Desc. Interés (Bs)</label>
                                                <input type="number" class="form-control form-control-sm border-warning bg-white" 
                                                    v-model.number="paymentDetails.monto_condonado_interes" 
                                                    min="0" :max="totalInteres"
                                                    @focus="$event.target.select()" @blur="verificarVacio('interes')" @input="validarMonto('interes')" placeholder="0.00">
                                            </div>
                                            <div class="col-sm-6" v-if="totalMulta > 0">
                                                <label class="form-label small fw-bold text-danger mb-1">Desc. Multa (Bs)</label>
                                                <input type="number" class="form-control form-control-sm border-danger bg-white" 
                                                    v-model.number="paymentDetails.monto_condonado_multa" 
                                                    min="0" :max="totalMulta"
                                                    @focus="$event.target.select()" @blur="verificarVacio('multa')" @input="validarMonto('multa')" placeholder="0.00">
                                            </div>
                                            <div class="col-12 mt-2" v-if="paymentDetails.monto_condonado_interes > 0 || paymentDetails.monto_condonado_multa > 0">
                                                <input type="text" class="form-control form-control-sm bg-white" 
                                                    v-model="paymentDetails.motivo_condonacion_interes" 
                                                    placeholder="Escriba el motivo de la condonación...">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-primary shadow-sm">
                                    <div class="card-body p-3">
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-success bg-opacity-10 rounded">
                                            <span class="text-primary fw-bold text-uppercase small">A Cobrar (Líquido)</span>
                                            <span class="fs-4 fw-bold text-primary">{{ plan_pago.moneda }} {{ formatNumero(totalLiquido) }}</span>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark mb-1">Efectivo a Recibir (Bs) *</label>
                                                <div class="input-group input-group-lg shadow-sm">
                                                    <span class="input-group-text bg-white border-primary"><i class="fas fa-money-bill-wave text-success"></i></span>
                                                    <input type="number" class="form-control fw-bold fs-4 text-end border-primary" 
                                                        v-model.number="paymentDetails.monto_recibido" 
                                                        min="1" step="0.01" style="color: #198754;">
                                                </div>
                                                <div v-if="paymentDetails.monto_recibido < totalLiquido" class="form-text text-warning fw-bold mt-1">
                                                    <i class="fas fa-info-circle"></i> Pago Parcial. Quedará un saldo de {{ formatNumero(totalLiquido - paymentDetails.monto_recibido) }} Bs.
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <label class="form-label small fw-bold text-muted mb-1">Fecha de Transacción</label>
                                                <input type="date" class="form-control form-control-sm bg-white" v-model="paymentDetails.fecha_pago">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-bold text-muted mb-1">Método de Pago</label>
                                                <select v-model="paymentDetails.forma_pago" class="form-select form-select-sm bg-white">
                                                    <option value="efectivo">Efectivo Físico</option>
                                                    <option value="transferencia - QR">Transferencia / QR</option>
                                                    <option value="Depósito banco">Depósito Bancario</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-white border-top-0 py-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-light px-4 border" @click="cerrarModalCobrarCuotas">Cancelar</button>
                        <button type="button" class="btn btn-success px-4 fw-bold shadow-sm" @click="procesarPagoCuotas">
                            <i class="fas fa-check-circle me-2"></i> CONFIRMAR COBRO: Bs {{ paymentDetails.monto_recibido ? formatNumero(paymentDetails.monto_recibido) : '0.00' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script>
import moment from "moment";
import axios from "axios";
import Swal from "sweetalert2";

export default {
    data() {
        return {
            vistaInterna: 'lista', // 'lista' | 'detalle'
            tabActual: 'normal',
            preloader: false,
            
            // Estado para Lista Planes
            lista_planespago: [],
            filtrosPlan: {
                criterio: 'cliente.nombre',
                buscar: '',
                fecha_inicio: moment().subtract(3, 'months').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD')
            },

            // Estado para Reprogramaciones
            lista_ordenes_repro: [],
            filtrosRepro: {
                criterio: 'cliente.nombre',
                buscar: ''
            },

            // Estado para Detalle/Pago
            plan_pago: {}, // Datos del plan seleccionado
            lista_cuotas_plan: [],
            dias_mora: 0,
            multa_dia: 3,
            selectedCuotas: [], // IDs seleccionados
            paymentDetails: {
                fecha_pago: moment().format('YYYY-MM-DD'),
                monto_condonado_interes: 0,
                motivo_condonacion_interes: '',
                monto_condonado_multa: 0,
                motivo_condonacion_multa: '',
                forma_pago: 'efectivo'
            }
        };
    },
    computed: {
        selectedCuotasDetails() {
            return this.lista_cuotas_plan.filter(cuota => this.selectedCuotas.includes(cuota.id));
        },
        totalCapital() {
            // Vue ya no resta, solo lee la variable limpia
            return this.selectedCuotasDetails.reduce((sum, c) => sum + parseFloat(c.capital_neto || 0), 0).toFixed(2);
        },
        totalInteres() {
            return this.selectedCuotasDetails.reduce((sum, c) => sum + parseFloat(c.interes_acumulado_neto || 0), 0).toFixed(2);
        },
        totalMulta() {
            return this.selectedCuotasDetails.reduce((sum, c) => sum + parseFloat(c.mora_fija_neta || 0), 0).toFixed(2);
        },
        totalPagar() {
            return (parseFloat(this.totalCapital) + parseFloat(this.totalInteres) + parseFloat(this.totalMulta)).toFixed(2);
        },
        totalLiquido() {
            const getVal = (v) => {
                if (v === '' || v === null || v === undefined) return 0;
                const parsed = parseFloat(v);
                return isNaN(parsed) ? 0 : parsed;
            };
            let liquido = getVal(this.totalPagar) - getVal(this.paymentDetails.monto_condonado_interes) - getVal(this.paymentDetails.monto_condonado_multa);
            return (liquido > 0 ? liquido : 0).toFixed(2);
        }
    },

    watch: {
        // Observamos el total a pagar calculado. Si cambia, actualizamos el input
        // de "Monto a Recibir" para que por defecto sugiera el pago total.
        /*totalLiquido: {
            handler(nuevoValor) {
                // Solo auto-rellenamos si el valor es válido y mayor a cero
                if (nuevoValor && parseFloat(nuevoValor) > 0) {
                    this.paymentDetails.monto_recibido = parseFloat(nuevoValor);
                }
            },
            immediate: true // Se ejecuta apenas se carga el componente
        }*/
    },
    mounted() {
        this.buscarPlanPago(); // Cargar inicial
    },
    methods: {
        formatDate(date) {
            if (!date) return '';
            return moment(date, 'YYYY-MM-DD').format('DD-MM-YYYY');
        },
        getEstadoCuota(cuota) {
            // 1. Estados cerrados
            if (cuota.estado == 2) {
                return { texto: 'Pagado', clase: 'bg-success text-white' };
            }
            if (cuota.estado == 0) {
                return { texto: 'Anulado', clase: 'bg-dark text-white' };
            }

            // 2. Verificamos si la cuota está vencida comparando fechas
            // Comparamos si la fecha de la cuota es estrictamente menor a HOY
            const hoy = moment().startOf('day');
            const fechaVencimiento = moment(cuota.fecha, 'YYYY-MM-DD').startOf('day');
            const estaVencida = fechaVencimiento.isBefore(hoy);

            // 3. Estado Parcial
            if (cuota.estado == 3) {
                return estaVencida 
                    ? { texto: 'Pago Parcial', clase: 'bg-danger text-white shadow-sm' } // Parcial y fecha pasada (Rojo)
                    : { texto: 'Pago Parcial', clase: 'bg-warning text-dark border border-warning' }; // Parcial a tiempo (Amarillo)
            }

            // 4. Estado Pendiente
            if (cuota.estado == 1) {
                return estaVencida 
                    ? { texto: 'Por pagar', clase: 'bg-danger text-white shadow-sm' } // Pendiente y fecha pasada (Rojo)
                    : { texto: 'Por pagar', clase: 'bg-info text-white' };            // Pendiente a tiempo (Celeste)
            }

            return { texto: 'Indefinido', clase: 'bg-light text-dark' };
        },
        verificarVacio(tipo) {
            if (tipo === 'interes') {
                if (this.paymentDetails.monto_condonado_interes === '' || 
                    this.paymentDetails.monto_condonado_interes === null || 
                    isNaN(this.paymentDetails.monto_condonado_interes)) {
                    
                    this.paymentDetails.monto_condonado_interes = 0;
                }
            } 
            else if (tipo === 'multa') {
                if (this.paymentDetails.monto_condonado_multa === '' || 
                    this.paymentDetails.monto_condonado_multa === null || 
                    isNaN(this.paymentDetails.monto_condonado_multa)) {
                    
                    this.paymentDetails.monto_condonado_multa = 0;
                }
            }
        },
        validarMonto(tipo) {
            if (tipo === 'interes') {
                const max = parseFloat(this.totalInteres) || 0;
                let valorInput = this.paymentDetails.monto_condonado_interes;

                // Si está vacío, no hacemos nada (la computed totalLiquido ya lo maneja como 0)
                if (valorInput === '' || valorInput === null) return;

                // Si intenta poner negativo, forzamos 0
                if (valorInput < 0) {
                    this.paymentDetails.monto_condonado_interes = 0;
                }
                // Si intenta poner más del total, forzamos el máximo
                else if (valorInput > max) {
                    this.paymentDetails.monto_condonado_interes = max;
                }
            } 
            else if (tipo === 'multa') {
                const max = parseFloat(this.totalMulta) || 0;
                let valorInput = this.paymentDetails.monto_condonado_multa;

                if (valorInput === '' || valorInput === null) return;

                if (valorInput < 0) {
                    this.paymentDetails.monto_condonado_multa = 0;
                } 
                else if (valorInput > max) {
                    this.paymentDetails.monto_condonado_multa = max;
                }
            }
        },
        // --- NAVEGACIÓN ---
        cambiarTabRepro() {
            this.tabActual = 'repro';
            this.cargarOrdenesReprogramacion();
        },
        cerrarDetallePlan() {
            this.vistaInterna = 'lista';
            this.plan_pago = {};
            this.lista_cuotas_plan = [];
        },

        // --- API LISTADOS ---
        async buscarPlanPago() {
            try {
                const response = await axios.get('/get_planespago_caja', {
                    params: {
                        page: 1, // Si necesitas paginación, agrégala luego
                        criterio: this.filtrosPlan.criterio,
                        buscar: this.filtrosPlan.buscar,
                        fecha_inicio: this.filtrosPlan.fecha_inicio,
                        fecha_fin: this.filtrosPlan.fecha_fin
                    }
                });
                this.lista_planespago = response.data;
            } catch (error) {
                console.error(error);
            }
        },
        async cargarOrdenesReprogramacion() {
            this.preloader = true;
            try {
                const response = await axios.get('/caja/get-ordenes-reprogramacion', {
                    params: this.filtrosRepro
                });
                this.lista_ordenes_repro = response.data;
            } catch (error) {
                console.error(error);
            } finally {
                this.preloader = false;
            }
        },
        limpiarFiltroRepro() {
            this.filtrosRepro.buscar = '';
            this.cargarOrdenesReprogramacion();
        },

        // --- DETALLE PLAN (View 3 logic) ---
        async verDetallePlan(item) {
            this.plan_pago = { ...item }; // Copia básica
            
            try {
                const response = await axios.get(`/listar_amortizaciones_cuotas?id_plan_pago=${item.id}`);
                this.lista_cuotas_plan = response.data.cuotas;
                this.dias_mora = response.data.dias_pasados_mora;
                this.multa_dia = response.data.multa_dia || 3;
                // Limpiar selección
                this.selectedCuotas = [];
                this.resetPaymentDetails();
                
                this.vistaInterna = 'detalle'; // Cambiar vista
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar las cuotas.', 'error');
            }
        },

        isCheckboxEnabled(index) {
            const firstUnpaid = this.lista_cuotas_plan.findIndex(c => c.estado == 1 || c.estado == 3);
            if (index === firstUnpaid) return true;
            // Permitir seleccionar si la anterior ya está seleccionada
            if (index > 0 && (this.lista_cuotas_plan[index - 1].estado == 1 || this.lista_cuotas_plan[index - 1].estado == 3)) {
                return this.selectedCuotas.includes(this.lista_cuotas_plan[index - 1].id);
            }
            return false;
        },

        toggleCuotaSelection(index, cuotaId, isChecked) {
            if (isChecked) {
                // Si el usuario MARCA la casilla, simplemente agregamos el ID al arreglo
                if (!this.selectedCuotas.includes(cuotaId)) {
                    this.selectedCuotas.push(cuotaId);
                }
            } else {
                // Si el usuario DESMARCA la casilla, debemos quitar esta cuota 
                // Y TODAS LAS QUE LE SIGUEN HACIA ABAJO.
                
                // 1. Recopilamos todos los IDs desde este índice hasta el final de la tabla
                const idsParaQuitar = [];
                for (let i = index; i < this.lista_cuotas_plan.length; i++) {
                    idsParaQuitar.push(this.lista_cuotas_plan[i].id);
                }
                
                // 2. Filtramos el arreglo original quitando todos esos IDs
                this.selectedCuotas = this.selectedCuotas.filter(id => !idsParaQuitar.includes(id));
            }
        },

        // --- MODAL PAGOS ---
        resetPaymentDetails() {
            this.paymentDetails = {
                fecha_pago: moment().format('YYYY-MM-DD'),
                monto_condonado_interes: 0,
                motivo_condonacion_interes: '',
                monto_condonado_multa: 0,
                motivo_condonacion_multa: '',
                forma_pago: 'efectivo'
            };
        },
        openPaymentModal() {
            if (this.selectedCuotas.length === 0) {
                Swal.fire('Atención', 'Seleccione al menos una cuota para pagar.', 'warning');
                return;
            }
            this.resetPaymentDetails();
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        },
        cerrarModalCobrarCuotas() {
            // Cerrar modal bootstrap manual (o usar jquery si prefieres consistencia)
            const modalEl = document.getElementById('paymentModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        },
        
        async procesarPagoCuotas() {
            try {
                const cuotasData = this.selectedCuotas.map(id => {
                    const c = this.selectedCuotasDetails.find(x => x.id === id);
                    return {
                        id_cuota: id,
                        capital_adeudado: parseFloat(c.capital_neto || 0),
                        interes_adeudado: parseFloat(c.interes_acumulado_neto || 0),
                        mora_adeudada: parseFloat(c.mora_fija_neta || 0)
                    };
                });

                const payload = {
                    id_plan_pago: this.plan_pago.id,
                    cuotas: cuotasData,
                    monto_recibido: parseFloat(this.paymentDetails.monto_recibido || 0),
                    fecha_pago: this.paymentDetails.fecha_pago,
                    forma_pago: this.paymentDetails.forma_pago,
                    condonacion_interes: parseFloat(this.paymentDetails.monto_condonado_interes) || 0,
                    motivo_condonacion_interes: this.paymentDetails.motivo_condonacion_interes || '',
                    condonacion_mora: parseFloat(this.paymentDetails.monto_condonado_multa) || 0,
                    motivo_condonacion_mora: this.paymentDetails.motivo_condonacion_multa || ''
                };

                if (payload.monto_recibido <= 0 && payload.condonacion_interes <= 0 && payload.condonacion_mora <= 0) {
                    Swal.fire('Atención', 'Debe ingresar un monto a recibir o aplicar una condonación.', 'warning');
                    return;
                }

                if (payload.monto_recibido > (parseFloat(this.totalLiquido) + 0.01)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Monto Inválido',
                        text: `El cliente solo debe ${this.totalLiquido} Bs en las cuotas seleccionadas. No puede cobrar de más.`
                    });
                    return;
                }

                const response = await axios.post('/pagar_cuotas', payload);
                Swal.fire('Transacción Exitosa', 'El pago se procesó correctamente.', 'success');
                this.cerrarModalCobrarCuotas();
                this.verDetallePlan(this.plan_pago);

            } catch (error) {
                console.error(error);
                Swal.fire('Error', error.response?.data?.error || 'No se pudo procesar el pago.', 'error');
            }
        },

        // --- REPROGRAMACIÓN ACTIONS ---
        cobrarOrdenReprogramacion(orden) {
            Swal.fire({
                title: 'Confirmar Cobro',
                text: `Cobrar ${orden.total_a_pagar} a ${orden.cliente_nombre}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, Cobrar'
            }).then(async (res) => {
                if (res.isConfirmed) {
                    try {
                        await axios.post('/caja/cobrar-orden-reprogramacion', { id_orden: orden.id });
                        Swal.fire('Cobrado', 'Orden procesada.', 'success');
                        this.cargarOrdenesReprogramacion();
                    } catch (e) {
                        Swal.fire('Error', 'Fallo al cobrar.', 'error');
                    }
                }
            });
        },
        imprimirRecibo(orden) {
            window.open(`/reportes/recibo-reprogramacion/${orden.id}`, '_blank');
        },
        generarPdf(id) {
            window.open(`/lista_cuotas_pdf_caja?id_plan_pago=${id}`, '_blank');
        },

        formatNumero(val) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
        },

    }
};
</script>

<style scoped>
    @import '../styles/frmCaja.css';
    .hover-effect:hover {
        background-color: #e9ecef;
    }

    .table-striped thead th{
        font-size:12px;
        font-weight: 700;
    }

    .table-striped tbody td{
        font-size:11px;
        color:#000;
    }

    .badge{
        border-radius:15px !important;
        min-width: 100px !important;
    }

    .list-header-pay li{
        padding-left: 0;
        margin-left: 0;
        padding-right: 0;
        margin-right: 0;
    }

    .table-cuotas th{
        font-size:11px !important;
    }

</style>
