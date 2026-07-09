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
                <ul class="nav mb-3 mt-1 d-flex justify-content-center px-2 custom-tabs border-bottom" role="tablist">
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link px-4 py-2 fw-bold text-uppercase border-0" 
                            :class="{ active: tabActual === 'normal' }" 
                            @click.prevent="tabActual = 'normal'" type="button">
                            <i class="fas fa-calendar-alt me-2"></i> Cuotas Normales
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link px-4 py-2 fw-bold text-uppercase border-0" 
                            :class="{ active: tabActual === 'repro' }" 
                            @click.prevent="cambiarTabRepro" type="button">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Órdenes de pago
                        </button>
                    </li>
                </ul>

                <div class="bg-white p-3 rounded border" style="min-height: 400px;">
                    
                    <div v-if="tabActual === 'normal'" class="fade-in-animation">
                        <div class="row mb-2">
                            <div class="col-md-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-secondary-subtle"><i class="fas fa-filter text-muted"></i></span>
                                    <select @change="buscarPlanPago()" v-model="filtrosPlan.criterio" class="form-select border-secondary-subtle" style="max-width: 150px;">
                                        <option value="plan_pago.id">Cod. Credito</option>
                                        <option value="cliente.nombre">Nombre cliente</option>
                                        <option value="cliente.ci">CI</option>
                                    </select>
                                    <span class="input-group-text bg-light text-muted" style="font-size: 11px;">Desde</span>
                                    <input v-model="filtrosPlan.fecha_inicio" type="date" class="form-control border-secondary-subtle" @change="buscarPlanPago()" style="max-width: 130px;">
                                    <span class="input-group-text bg-light text-muted" style="font-size: 11px;">Hasta</span>
                                    <input v-model="filtrosPlan.fecha_fin" type="date" class="form-control border-secondary-subtle" @change="buscarPlanPago()" style="max-width: 130px;">
                                    <input v-model="filtrosPlan.buscar" type="text" class="form-control border-secondary-subtle" placeholder="Buscar cliente, CI o código..." @input="buscarPlanPago()">
                                    <button class="btn btn-success px-3 fw-bold border-secondary-subtle" @click="buscarPlanPago">
                                        <i class="fas fa-search me-1"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive text-uppercase">
                            <table class="table mb-0 table-hover table-striped align-middle table-compact-general">
                                <thead class="text-uppercase text-white">
                                    <tr class="align-middle table-warning">
                                        <th class="text-dark fw-bold text-center"># Cred.</th>
                                        <th class="text-dark fw-bold">Cliente</th>
                                        <th class="text-dark fw-bold text-center">CI</th>
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
                                        <td class="fw-bold text-center text-primary">{{ item.id }}</td>
                                        <td class="fw-bold">{{ item.cliente }}</td>
                                        <td class="text-center">{{ item.ci }}</td>
                                        <td class="text-center">{{ formatDate(item.fecha_desembolso) }}</td>
                                        <td class="fw-bold text-end text-success">{{ item.total_pagar_plan }}</td>
                                        <td class="text-center text-muted">{{ item.asesor }}</td>
                                        <td class="text-center">{{ formatDate(item.fecha_inicio_plan) }}</td>
                                        <td class="text-center">{{ formatDate(item.fecha_fin_plan) }}</td>
                                        <td class="text-center">
                                            {{ item.nro_cuotas }} <span class="text-muted" style="font-size: 9px;">({{ item.lapso_capital }})</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-success btn-sm px-3 shadow-sm border-0" @click="verDetallePlan(item)" title="Gestionar Cobro">
                                                <i class="fas fa-hand-holding-usd me-1"></i> Cobrar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="lista_planespago.length === 0">
                                        <td colspan="10" class="text-center py-4 text-muted fst-italic">
                                            <i class="fas fa-folder-open me-1"></i> No se encontraron planes vigentes.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="tabActual === 'repro'" class="fade-in-animation">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-primary fw-bold text-uppercase my-0 small">
                                <i class="fas fa-list-alt me-2"></i> Órdenes de Cobro Pendientes
                            </h6>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-secondary-subtle"><i class="fas fa-filter text-muted"></i></span>
                                    <select v-model="filtrosRepro.criterio" class="form-select border-secondary-subtle" style="max-width: 150px;">
                                        <option value="cliente.nombre">Nombre</option>
                                        <option value="cliente.ci">CI</option>
                                        <option value="plan_pagos.id">Cód. Crédito</option>
                                    </select>
                                    
                                    <input type="text" class="form-control border-secondary-subtle" 
                                        v-model="filtrosRepro.buscar" 
                                        placeholder="Escriba para buscar..." 
                                        @keyup.enter="cargarOrdenesReprogramacion">
                                    
                                    <button class="btn btn-success px-3 fw-bold border-secondary-subtle" @click="cargarOrdenesReprogramacion">
                                        <i class="fas fa-search me-1"></i> Buscar
                                    </button>
                                    
                                    <button class="btn btn-info text-white px-3 fw-bold border-secondary-subtle" @click="cargarOrdenesReprogramacion">
                                        <i class="fas fa-sync-alt me-1"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="preloader" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0 table-compact-general">
                                <thead class="table-warning text-uppercase text-center">
                                    <tr>
                                        <th class="text-dark fw-bold"># Orden</th>
                                        <th class="text-dark fw-bold">Fecha</th>
                                        <th class="text-dark fw-bold text-start">Cliente</th>
                                        <th class="text-dark fw-bold text-end border-start">Int. Calc.</th>
                                        <th class="text-dark fw-bold text-end">Int. Cond.</th>
                                        <th class="text-dark fw-bold text-end border-start">Multa Calc.</th>
                                        <th class="text-dark fw-bold text-end">Multa Cond.</th>
                                        <th class="text-dark fw-bold text-end border-start bg-warning bg-opacity-10">Total Cobrar</th>
                                        <th class="text-dark fw-bold">Estado</th>
                                        <th class="text-dark fw-bold">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    <tr v-for="orden in lista_ordenes_repro" :key="orden.id">
                                        <td class="fw-bold text-primary">#{{ orden.id }}</td>
                                        <td>{{ orden.created_at_fmt }}</td>
                                        <td class="text-start fw-bold text-uppercase">{{ orden.cliente_nombre }}</td>
                                        <td class="text-end border-start font-monospace">{{ orden.monto_interes_calculado }}</td>
                                        <td class="text-end text-muted fst-italic bg-light font-monospace">
                                            <span v-if="orden.monto_condonado_interes > 0">-{{ orden.monto_condonado_interes }}</span>
                                            <span v-else>-</span>
                                        </td>
                                        
                                        <td class="text-end text-danger border-start font-monospace">{{ orden.monto_mora_calculado }}</td>
                                        <td class="text-end text-muted fst-italic bg-light font-monospace">
                                            <div v-if="orden.monto_condonado_mora > 0">
                                                -{{ orden.monto_condonado_mora }}
                                                <i v-if="orden.motivo_condonacion" 
                                                class="fas fa-info-circle text-info ms-1" 
                                                :title="'Motivo: ' + orden.motivo_condonacion"
                                                style="cursor: help;"></i>
                                            </div>
                                            <span v-else>-</span>
                                        </td>
                                        
                                        <td class="text-end border-start bg-success bg-opacity-10 font-monospace" style="font-weight: 700; color: #198754; font-size: 12px;">
                                            {{ orden.total_a_pagar }}
                                        </td>
                                        
                                        <td>
                                            <span v-if="orden.estado == 1" class="badge bg-warning text-dark border border-warning">POR PAGAR</span>
                                            <span v-else-if="orden.estado == 2" class="badge bg-success">PAGADO</span>
                                            <span v-else class="badge bg-secondary">NO DISP.</span>
                                        </td>
                                        
                                        <td>
                                            <button v-if="orden.estado == 1" 
                                                    class="btn btn-info text-white btn-sm px-3 fw-bold" 
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
                                        <td colspan="10" class="text-center py-4 text-muted fst-italic bg-white">
                                            <i class="fas fa-folder-open fa-lg mb-1 d-block text-secondary"></i>
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
                <!-- Action Buttons: sm size, compact layout -->
                <div class="row mb-2">
                    <div class="col text-center">
                        <a @click="generarPdf(plan_pago.id)" class="btn btn-info btn-sm text-white me-2 shadow-sm fw-bold">
                            <i class="fas fa-file-pdf me-1"></i> Generar PDF
                        </a>
                        <a @click="openPaymentModal()" class="btn btn-success btn-sm shadow-sm fw-bold">
                            <i class="fas fa-dollar-sign me-1"></i> Cobrar Cuota/s
                        </a>
                    </div>
                </div>

                <!-- Overview Summary Blocks: compact padding & font size -->
                <div class="row g-2 mb-2" style="font-size: 0.78rem;">
                    <div class="col-md-4">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="d-flex justify-content-between mb-1"><strong>Cliente:</strong> <span class="text-uppercase text-dark fw-semibold">{{ plan_pago.cliente }}</span></div>
                            <div class="d-flex justify-content-between mb-1"><strong>CI:</strong> <span>{{ plan_pago.ci }} {{ plan_pago.lugar_expedicion }}</span></div>
                            <div class="d-flex justify-content-between"><strong>Garantía:</strong> <span>{{ plan_pago.tipo_garantia }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="d-flex justify-content-between mb-1"><strong>Plazo:</strong> <span>{{ plan_pago.nro_cuotas }} {{ plan_pago.lapso_capital }}</span></div>
                            <div class="d-flex justify-content-between mb-1"><strong>Monto:</strong> <span class="font-monospace fw-bold text-dark">{{ formatNumero(plan_pago.total_pagar_plan) }} {{ plan_pago.moneda }}</span></div>
                            <div class="d-flex justify-content-between"><strong>Forma Pago:</strong> <span>{{ plan_pago.lapso_capital }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="d-flex justify-content-between mb-1"><strong>Inicio:</strong> <span>{{ formatDate(plan_pago.fecha_inicio_plan) }}</span></div>
                            <div class="d-flex justify-content-between mb-1"><strong>Fin:</strong> <span>{{ formatDate(plan_pago.fecha_fin_plan) }}</span></div>
                            <div v-if="dias_mora > 0" class="d-flex justify-content-between">
                                <span class="fw-bold text-danger">Multa General:</span>
                                <span class="fw-bold text-danger">{{ dias_mora }} Días</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="font-size:11px;">
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
                                
                                <th class="text-center text-danger">Multa a Pagar</th>
                                <th class="text-center fw-bold">Total a Pagar</th>

                                <th class="text-center">Int. Acumulado</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Pagar</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(cuota, index) in lista_cuotas_plan" :key="index" class="align-middle">
                                <td class="text-center">{{ cuota.numero }}</td>
                                <td class="text-center">{{ formatDate(cuota.fecha) }}</td>
                                
                                <td class="text-center align-middle">
                                    <div class="fw-bold text-dark fs-6">{{ formatNumero(cuota.capital) }}</div>
                                    <div v-if="parseFloat(cuota.capital_pagado_total) > 0" class="text-success fw-bold lh-1 mt-1" style="font-size: 0.65rem;">
                                        <span>{{ cuota.porcentaje_capital_pagado }}%</span><br>
                                        {{ formatNumero(cuota.capital_pagado_total) }} Bs
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
                                    {{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_moratorio_neto) }}
                                </td>

                                <td class="text-center text-danger fw-bold">
                                    <div class="fw-bold text-danger fs-6">
                                        {{ cuota.estado == 0 ? '---' : formatNumero(cuota.mora_fija_neta) }}
                                    </div>
                                    <div v-if="cuota.mora_pagada_total > 0" class="text-success fw-bold lh-1 mt-1" style="font-size: 0.65rem;">
                                        Pagado: {{ formatNumero(cuota.mora_pagada_total) }} <br>
                                        ({{ cuota.porcentaje_mora_pagada }}%)
                                    </div>
                                </td>

                                <td class="text-center fw-bold text-dark bg-light">
                                    {{ cuota.estado == 0 ? '---' : formatNumero(parseFloat(cuota.capital_neto || 0) + parseFloat(cuota.interes_acumulado_neto || 0) + parseFloat(cuota.mora_fija_neta || 0)) }}
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

                        <!-- Selector de modalidad de pago -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body py-2 px-3">
                                <label class="form-label small fw-bold text-muted mb-2 text-uppercase d-block">
                                    <i class="fas fa-sliders-h me-1"></i> Modalidad de Pago
                                </label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button v-for="m in modalidades" :key="m.value"
                                            type="button"
                                            class="btn btn-sm fw-bold text-uppercase"
                                            :class="paymentDetails.modalidad === m.value ? m.classActive : 'btn-outline-secondary'"
                                            @click="paymentDetails.modalidad = m.value">
                                        {{ m.label }}
                                    </button>
                                </div>
                                <div v-if="paymentDetails.modalidad !== 'completo' && paymentDetails.modalidad !== 'parcial'"
                                     class="mt-2 small text-warning fw-bold">
                                    <i class="fas fa-info-circle"></i>
                                    Solo se registra {{
                                        paymentDetails.modalidad === 'solo_interes' ? 'el interés' :
                                        paymentDetails.modalidad === 'solo_mora' ? 'la multa' :
                                        'interés + multa'
                                    }}. El capital permanece pendiente.
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">

                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                        <h6 class="fw-bold text-uppercase text-muted mb-0"><i class="fas fa-receipt me-2"></i>Resumen de Deuda</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush mb-3">
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-bottom-0 pb-1">
                                                <span class="text-muted small">Capital Adeudado</span>
                                                <span class="fw-bold text-dark">{{ formatNumero(totalCapital) }}</span>
                                            </li>
                                            
                                            <!-- Desglose de Intereses -->
                                            <li class="list-group-item px-0 bg-transparent border-bottom-0 py-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small">Interés Devengado</span>
                                                    <span class="fw-bold text-primary">{{ formatNumero(totalInteresDevengado) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Interés Moratorio</span>
                                                    <span class="fw-bold text-danger">{{ formatNumero(totalInteresMoratorio) }}</span>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-top pt-2 pb-1">
                                                <span class="text-secondary fw-bold small">Subtotal Intereses</span>
                                                <span class="fw-bold text-primary">{{ formatNumero(totalInteres) }}</span>
                                            </li>

                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent pt-1 border-bottom-0">
                                                <span class="text-muted small">Multa Fija</span>
                                                <span class="fw-bold text-danger">{{ formatNumero(totalMulta) }}</span>
                                            </li>
                                        </ul>
                                        
                                        <div class="alert alert-dark border-0 p-2 px-3 mb-0 d-flex justify-content-between align-items-center shadow-sm">
                                            <span class="text-uppercase fw-bold opacity-75" style="font-size: 0.7rem;">TOTAL BRUTO</span>
                                            <span class="fw-bold mb-0 fs-5">{{ plan_pago.moneda }} {{ formatNumero(totalPagar) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">
                                
                                <div class="card border-warning border-opacity-50 shadow-sm mb-3" v-if="showCondInt || showCondMora">
                                    <div class="card-header bg-warning bg-opacity-10 py-2">
                                        <h6 class="fw-bold text-warning-emphasis mb-0 small text-uppercase">
                                            <i class="fas fa-gift me-2"></i>Opciones de Condonación
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-sm-6" v-if="showCondInt">
                                                <label class="form-label small fw-bold text-muted mb-1">Desc. Interés (Bs)</label>
                                                <input type="number" class="form-control form-control-sm border-warning bg-white"
                                                    v-model.number="paymentDetails.monto_condonado_interes"
                                                    min="0" :max="totalInteres"
                                                    @focus="$event.target.select()" @blur="verificarVacio('interes')" @input="validarMonto('interes')" placeholder="0.00">
                                            </div>
                                            <div class="col-sm-6" v-if="showCondMora">
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
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-bold text-dark mb-0 small text-uppercase">Efectivo a Recibir (Bs) *</label>
                                                    <div class="form-check form-switch mb-0"
                                                         v-if="paymentDetails.modalidad === 'parcial'">
                                                        <input class="form-check-input" type="checkbox" id="toggleCobrarTotal"
                                                            v-model="cobrarTotal" @change="handleCobrarTotal">
                                                        <label class="form-check-label small fw-bold text-primary" for="toggleCobrarTotal" style="cursor:pointer;">COBRAR TODO</label>
                                                    </div>
                                                    <span v-else class="badge bg-secondary small">Auto-calculado</span>
                                                </div>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text bg-white border-primary py-2"><i class="fas fa-money-bill-wave text-success"></i></span>
                                                    <input type="number" class="form-control fw-bold text-end border-primary py-2"
                                                        v-model.number="paymentDetails.monto_recibido"
                                                        min="1" step="0.01" style="color: #198754;"
                                                        :readonly="paymentDetails.modalidad !== 'parcial'"
                                                        :class="{'bg-light': paymentDetails.modalidad !== 'parcial'}"
                                                        @input="cobrarTotal = false"
                                                        @focus="$event.target.select()">
                                                </div>
                                                <div v-if="paymentDetails.modalidad === 'parcial' && paymentDetails.monto_recibido < totalLiquido" class="form-text text-warning fw-bold mt-1" style="font-size: 0.7rem;">
                                                    <i class="fas fa-info-circle"></i> Pago Parcial. Saldo: {{ formatNumero(totalLiquido - paymentDetails.monto_recibido) }} Bs.
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
            cobrarTotal: false,
            paymentDetails: {
                fecha_pago: moment().format('YYYY-MM-DD'),
                modalidad: 'completo',
                monto_recibido: 0,
                monto_condonado_interes: 0,
                motivo_condonacion_interes: '',
                monto_condonado_multa: 0,
                motivo_condonacion_multa: '',
                forma_pago: 'efectivo'
            },
            modalidades: [
                { value: 'completo',     label: 'Cuota Completa',        classActive: 'btn-success' },
                { value: 'solo_interes', label: 'Solo Interés',          classActive: 'btn-primary' },
                { value: 'solo_mora',    label: 'Solo Multa',            classActive: 'btn-danger' },
                { value: 'interes_mora', label: 'Interés + Multa',       classActive: 'btn-warning text-dark' },
                { value: 'parcial',      label: 'Pago Parcial (libre)',  classActive: 'btn-info text-white' },
            ]
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
        totalInteresDevengado() {
            return this.selectedCuotasDetails.reduce((sum, c) => sum + parseFloat(c.interes_devengado_neto || 0), 0).toFixed(2);
        },
        totalInteresMoratorio() {
            return this.selectedCuotasDetails.reduce((sum, c) => sum + parseFloat(c.interes_moratorio_neto || 0), 0).toFixed(2);
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
            const val = (v) => {
                if (v === '' || v === null || v === undefined) return 0;
                const p = parseFloat(v);
                return isNaN(p) ? 0 : p;
            };
            const m  = this.paymentDetails.modalidad;
            const ci = val(this.paymentDetails.monto_condonado_interes);
            const cm = val(this.paymentDetails.monto_condonado_multa);
            let base;
            if      (m === 'solo_interes') base = val(this.totalInteres) - ci;
            else if (m === 'solo_mora')    base = val(this.totalMulta) - cm;
            else if (m === 'interes_mora') base = val(this.totalInteres) + val(this.totalMulta) - ci - cm;
            else                           base = val(this.totalPagar) - ci - cm; // completo, parcial
            return (base > 0 ? base : 0).toFixed(2);
        },
        showCondInt() {
            return ['completo', 'parcial', 'solo_interes', 'interes_mora'].includes(this.paymentDetails.modalidad)
                && parseFloat(this.totalInteres) > 0;
        },
        showCondMora() {
            return ['completo', 'parcial', 'solo_mora', 'interes_mora'].includes(this.paymentDetails.modalidad)
                && parseFloat(this.totalMulta) > 0;
        }
    },

    watch: {
        totalLiquido(nuevoValor) {
            if (this.cobrarTotal) {
                this.paymentDetails.monto_recibido = parseFloat(nuevoValor);
            }
        },
        'paymentDetails.modalidad'(newVal) {
            if (newVal === 'parcial') {
                this.cobrarTotal = false;
                this.paymentDetails.monto_recibido = 0;
            } else {
                // Para modos fijos, auto-rellenar el monto con el total líquido
                this.cobrarTotal = (newVal === 'completo');
                this.$nextTick(() => {
                    this.paymentDetails.monto_recibido = parseFloat(this.totalLiquido);
                });
            }
            // Limpiar condonaciones del componente que no aplica
            if (!['completo', 'parcial', 'solo_interes', 'interes_mora'].includes(newVal)) {
                this.paymentDetails.monto_condonado_interes = 0;
            }
            if (!['completo', 'parcial', 'solo_mora', 'interes_mora'].includes(newVal)) {
                this.paymentDetails.monto_condonado_multa = 0;
            }
        }
    },
    mounted() {
        this.buscarPlanPago(); // Cargar inicial
    },
    methods: {
        handleCobrarTotal() {
            if (this.cobrarTotal) {
                this.paymentDetails.monto_recibido = parseFloat(this.totalLiquido);
            }
        },
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
                modalidad: 'completo',
                monto_recibido: 0,
                monto_condonado_interes: 0,
                motivo_condonacion_interes: '',
                monto_condonado_multa: 0,
                motivo_condonacion_multa: '',
                forma_pago: 'efectivo'
            };
            this.cobrarTotal = false;
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
                    modalidad_pago: this.paymentDetails.modalidad,
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
                
                this.cerrarModalCobrarCuotas();
                this.verDetallePlan(this.plan_pago);

                Swal.fire({
                    title: '¡Pago Exitoso!',
                    text: 'La transacción se procesó correctamente. ¿Desea generar el comprobante de pago?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-print me-2"></i>Sí, Generar',
                    cancelButtonText: 'No por ahora'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (response.data.codigo_transaccion) {
                            window.open(`/imprimir/recibo/${response.data.codigo_transaccion}`, '_blank');
                        } else {
                            Swal.fire('Error', 'No se encontró el código de transacción para generar el recibo.', 'error');
                        }
                    }
                });

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
    
    /* Tabs Personalizados y Modernos (Warning Style) */
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
        color: #e0a800 !important;
        background-color: rgba(255, 193, 7, 0.05) !important;
        border-bottom-color: rgba(255, 193, 7, 0.3) !important;
    }
    .custom-tabs .nav-link.active {
        color: #e0a800 !important;
        background-color: rgba(255, 193, 7, 0.1) !important;
        border-bottom-color: #ffc107 !important;
    }

    .custom-tabs .nav-link.active i{
        color: #e0a800 !important;
    }

    /* Animación Suave entre Pestañas */
    .fade-in-animation {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Estilos ultra-compactos para listas generales */
    .table-compact-general th,
    .table-compact-general td {
        padding: 4px 6px !important;
        vertical-align: middle !important;
        font-size: 11px !important;
    }
    .table-compact-general th {
        font-weight: 700 !important;
        letter-spacing: 0.2px;
    }
    .table-compact-general .btn-sm {
        padding: 3px 8px !important;
        font-size: 10px !important;
    }

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

    /* Estilos ultra-compactos estilo Ledger Contable Senior */
    .table-cuotas th,
    .table-cuotas td {
        padding: 3px 5px !important;
        vertical-align: middle !important;
        font-size: 10.5px !important;
    }
    .table-cuotas th {
        font-weight: 700 !important;
        letter-spacing: 0.3px;
    }
    .table-cuotas input[type="checkbox"] {
        transform: scale(1.1) !important;
    }
    .table-cuotas .badge {
        min-width: 80px !important;
        font-size: 9px !important;
        padding: 3px 6px !important;
    }
</style>
