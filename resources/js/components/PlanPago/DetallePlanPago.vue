<template>
    <div class="card border-0 shadow-lg">
        
        <div class="card-header bg-warning py-3 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 text-center">
                <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                    <i class="fas fa-list-alt me-2"></i> Detalle del Plan de Pago
                </h5>
            </div>
            <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-dark shadow-none"></button>
        </div>

        <div class="card-body p-4 bg-light">
            
            <div v-if="plan.id_solicitud_origen" class="alert alert-warning d-flex align-items-center justify-content-between py-2 px-3 mb-4 shadow-sm border-warning">
                <div class="small text-dark">
                    <i class="fas fa-history fa-lg me-2"></i>
                    <strong>Antecedente:</strong> Este crédito proviene de una reprogramación o refinanciamiento anterior.
                </div>
                <button @click="$emit('ver-original', plan.id_solicitud_origen)" 
                        class="btn btn-warning btn-sm fw-bold text-dark border border-dark shadow-sm">
                    <i class="fas fa-search me-1"></i> Ver Crédito Original
                </button>
            </div>

            <div class="row g-4 mb-4">
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom pb-2 pt-3">
                            <h6 class="fw-bold text-success mb-0 text-dark text-uppercase">
                                <i class="fas fa-user-tie me-2"></i> Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <img @click="$emit('ver-ficha', plan.id_cliente)" 
                                     :src="plan.imagen_cliente ? '/img/cliente/' + plan.imagen_cliente : '/img/empresa/user_img2_old.png'" 
                                     class="rounded-circle border border-2 border-success p-1 shadow-sm hover-zoom" 
                                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;" 
                                     title="Ver ficha del cliente"
                                     alt="Cliente">
                                <div class="ms-3">
                                    <h5 class="fw-bold text-dark mb-1 text-uppercase">{{ plan.cliente }}</h5>
                                    <div class="text-muted small">
                                        <i class="fas fa-id-card me-1 text-secondary"></i> CI: {{ plan.ci }} {{ plan.lugar_expedicion }}
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-home me-1 text-secondary"></i> {{ plan.vivienda }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-briefcase me-1 text-secondary"></i> {{ plan.actividad }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom pb-2 pt-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-primary mb-0 text-uppercase">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Detalles del Plan
                            </h6>
                            <span v-if="Number(plan.estado_plan) === 1" class="badge bg-success text-uppercase px-3 py-1 shadow-sm rounded-pill">
                                <i class="fas fa-check-circle me-1"></i> Plan vigente
                            </span>
                            <span v-else-if="Number(plan.estado_plan) === 0" class="badge bg-danger text-uppercase px-3 py-1 shadow-sm rounded-pill">
                                <i class="fas fa-times-circle me-1"></i> Anulado
                            </span>
                            <span v-else class="badge bg-warning text-dark text-uppercase px-3 py-1 shadow-sm rounded-pill">
                                <i class="fas fa-clock me-1"></i> Pendiente
                            </span>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3 small">
                                <div class="col-6">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                                        <span class="text-muted">Monto Total:</span>
                                        <span class="fw-bold text-primary fs-6">{{ plan.moneda }} {{ formatNumero(plan.total_pagar_plan) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                                        <span class="text-muted">Tasa Interés:</span>
                                        <span class="fw-bold text-dark">{{ plan.tasa }}% ({{ plan.tipo_tasa }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Garantía:</span>
                                        <span class="fw-bold text-dark text-end" style="font-size: 0.75rem;">{{ plan.tipo_garantia }}</span>
                                    </div>
                                </div>
                                <div class="col-6 border-start">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1 ps-2">
                                        <span class="text-muted">Plazo/Freq:</span>
                                        <span class="fw-bold text-dark">{{ plan.nro_cuotas }} {{ plan.lapso_capital }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1 ps-2">
                                        <span class="text-muted">Inicio:</span>
                                        <span class="fw-bold text-dark">{{ formatFecha(plan.fecha_inicio_plan) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between ps-2">
                                        <span class="text-muted">Finaliza:</span>
                                        <span class="fw-bold text-dark">{{ formatFecha(plan.fecha_fin_plan) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="text-dark fw-bold text-uppercase my-0">
                        <i class="fas fa-list-ol me-2 text-warning"></i> Cronograma de Cuotas
                    </h6>
                    <button @click="generarPdfCuotasPlanPago()" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm">
                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                    </button>
                </div>
                
                <div class="card-body p-0">
                    <div class="px-3 py-2 bg-light border-bottom resumen-cuotas">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                            <div>
                                <strong>Saldo a pagar al {{ formatFecha(cuotas[0]?.fecha_corte) }}</strong>
                                <div class="small text-muted">Capital pendiente + interés pendiente + multa pendiente. No incluye interés futuro no devengado.</div>
                            </div>
                            <strong class="fs-6 text-primary">{{ formatNumero(totalPendiente) }} Bs</strong>
                        </div>
                        <p class="small text-muted mb-0 mt-2">La cuota programada incluye capital e interés fijo; el importe exigible cambia con atrasos, adelantos y condonaciones. Los importes cobrados no incluyen condonaciones.</p>
                        <div v-if="resumenMulta" class="resumen-multa mt-2" aria-label="Resumen de multa del crédito">
                            <div class="resumen-multa__titulo">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                Multa única del crédito · {{ formatNumero(3) }} Bs por día · al {{ formatFecha(cuotas[0]?.fecha_corte) }}
                            </div>
                            <div class="resumen-multa__valores">
                                <div><span>Generada</span><strong>{{ formatNumero(resumenMulta.generada) }} Bs</strong></div>
                                <div><span>Cobrada</span><strong>{{ formatNumero(resumenMulta.cobrada) }} Bs</strong></div>
                                <div><span>Condonada</span><strong>{{ formatNumero(resumenMulta.condonada) }} Bs</strong></div>
                                <div><span>Pendiente</span><strong>{{ formatNumero(resumenMulta.pendiente) }} Bs</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped table-hover align-middle table-listado-cuotas table-sm">
                            <thead class="table-warning text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha venc.</th>
                                    <th>Capital</th>
                                    <th>Interés fijo</th>
                                    <th>Saldo cap.</th>
                                    <th title="Capital e interés fijo programados; no incluye recargos">Total Bs</th>
                                    <th class="text-primary border-start">Int. devengado<small class="d-block">Generado</small></th>
                                    <th class="text-danger">Int. moratorio<small class="d-block">Generado</small></th>
                                    <th class="border-start border-end">Int. acumulado<small class="d-block">Total registrado</small></th>
                                    <th class="text-danger">Multa<small class="d-block">Aplicada a la cuota</small></th>
                                    <th class="bg-success bg-opacity-10 text-success border-end">Total pagado</th>
                                    <th>Estado / A pagar</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr v-for="cuota in cuotas" :key="cuota.id">
                                    <td class="fw-bold text-muted">{{ cuota.numero }}</td>
                                    <td class="text-nowrap">{{ formatFecha(cuota.fecha) }}</td>
                                    <td>
                                        <strong>{{ formatNumero(cuota.capital) }}</strong>
                                        <small v-if="Number(cuota.capital_pagado_total) > 0" class="d-block text-success">
                                            {{ cuota.porcentaje_capital_pagado }}%<br>{{ formatNumero(cuota.capital_pagado_total) }} Bs
                                        </small>
                                    </td>
                                    <td>{{ formatNumero(cuota.interes) }}</td>
                                    <td class="text-muted">{{ formatNumero(cuota.saldo_capital) }}</td>
                                    <td>
                                        <strong>{{ formatNumero(cuota.total) }}</strong>
                                        <small class="d-block text-muted">Programado</small>
                                        <small class="d-block fw-bold" title="Capital + interés + multa registrados antes de condonar">Antes de cond.: {{ formatNumero(cuota.total_registrado_antes_condonacion) }}</small>
                                    </td>
                                    <td class="text-primary border-start">
                                        <template v-if="cuota.origen_desglose_interes !== 'sin_desglose_historico'">
                                            {{ formatNumero(cuota.interes_ordinario_generado) }}
                                            <small class="d-block text-muted">({{ cuota.dias_transcurridos }} d)</small>
                                        </template>
                                        <small v-else class="text-muted" title="El registro antiguo no conserva el desglose ordinario y moratorio">Sin detalle histórico</small>
                                        <template v-if="cuota.desglose_cobros_interes_completo">
                                            <small class="d-block text-success">Cobrado: {{ formatNumero(cuota.interes_ordinario_cobrado) }}</small>
                                            <small class="d-block text-muted">Cond.: {{ formatNumero(cuota.interes_ordinario_condonado) }}</small>
                                        </template>
                                        <small v-else class="d-block text-muted">Cobros sin desglose</small>
                                        <small class="d-block fw-bold">Pend.: {{ formatNumero(cuota.interes_devengado_neto) }}</small>
                                    </td>
                                    <td class="text-danger">
                                        <template v-if="cuota.origen_desglose_interes !== 'sin_desglose_historico'">
                                            {{ formatNumero(cuota.interes_moratorio_generado) }}
                                            <small v-if="cuota.origen_desglose_interes === 'registro_cobro'" class="d-block text-muted">Histórico</small>
                                            <small v-else-if="cuota.dias_pasados > 0" class="d-block text-muted">({{ cuota.dias_pasados }} d)</small>
                                        </template>
                                        <small v-else class="text-muted" title="El registro antiguo no conserva el desglose ordinario y moratorio">Sin detalle histórico</small>
                                        <template v-if="cuota.desglose_cobros_interes_completo">
                                            <small class="d-block text-success">Cobrado: {{ formatNumero(cuota.interes_moratorio_cobrado) }}</small>
                                            <small class="d-block text-muted">Cond.: {{ formatNumero(cuota.interes_moratorio_condonado) }}</small>
                                        </template>
                                        <small v-else class="d-block text-muted">Cobros sin desglose</small>
                                        <small class="d-block fw-bold">Pend.: {{ formatNumero(cuota.interes_moratorio_neto) }}</small>
                                    </td>
                                    <td class="border-start border-end">
                                        <strong title="Interés cobrado + condonado + pendiente">Total: {{ formatNumero(cuota.interes_total_registrado) }}</strong>
                                        <small class="d-block fw-bold">Pend.: {{ formatNumero(cuota.interes_acumulado_neto) }}</small>
                                        <small v-if="Number(cuota.interes_cobrado_total) > 0" class="d-block text-success">Cobrado: {{ formatNumero(cuota.interes_cobrado_total) }}</small>
                                        <small v-if="Number(cuota.interes_condonado_total) > 0" class="d-block text-muted">Cond.: {{ formatNumero(cuota.interes_condonado_total) }}</small>
                                    </td>
                                    <td class="text-danger">
                                        <strong>Total: {{ formatNumero(cuota.multa_total_registrada) }}</strong>
                                        <small class="d-block fw-bold">Pend.: {{ formatNumero(cuota.mora_fija_neta) }}</small>
                                        <small v-if="Number(cuota.mora_cobrado_total) > 0" class="d-block text-success">Cobrado: {{ formatNumero(cuota.mora_cobrado_total) }}</small>
                                        <small v-if="Number(cuota.mora_condonado_total) > 0" class="d-block text-muted">Cond.: {{ formatNumero(cuota.mora_condonado_total) }}</small>
                                    </td>
                                    <td class="bg-success bg-opacity-10 border-end">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <strong class="text-success text-nowrap">{{ formatNumero(calcularTotalPagado(cuota)) }} Bs</strong>
                                            <button @click="verDetallePagos(cuota)" class="btn btn-outline-success btn-xs" title="Ver detalle de pagos y pendientes" :aria-label="'Ver detalle de cuota ' + cuota.numero"><i class="fas fa-receipt" aria-hidden="true"></i></button>
                                        </div>
                                        <small class="d-block text-muted text-nowrap">C: {{ formatNumero(cuota.capital_cobrado_total) }} · I: {{ formatNumero(cuota.interes_cobrado_total) }} · Multa: {{ formatNumero(cuota.mora_cobrado_total) }}</small>
                                        <small v-if="calcularTotalCondonado(cuota) > 0" class="d-block text-muted">Condonado: {{ formatNumero(calcularTotalCondonado(cuota)) }} Bs</small>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getEstadoCuota(cuota).clase">{{ getEstadoCuota(cuota).texto }}</span>
                                        <small class="d-block fw-bold text-primary mt-1 text-nowrap">A pagar: {{ formatNumero(cuota.total_a_pagar) }} Bs</small>
                                    </td>
                                </tr>
                                <tr v-if="!cuotas.length"><td colspan="12" class="p-4 text-muted">No hay cuotas disponibles.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="modalHistorialPagos" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-cuota">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white py-2 px-3">
                        <div>
                            <h5 class="modal-title fw-bold mb-0"><i class="fas fa-receipt me-2" aria-hidden="true"></i>Detalle y pagos de la cuota</h5>
                            <small v-if="cuota_seleccionada" class="text-white-50">Cuota {{ cuota_seleccionada.numero }} · Vence {{ formatFecha(cuota_seleccionada.fecha) }}</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" aria-label="Cerrar detalle de cuota" @click="cerrarModalPagos()"></button>
                    </div>
                    <div class="modal-body p-3 bg-light">
                        <template v-if="cuota_seleccionada">
                            <div class="cuota-resumen bg-white border rounded-3 p-2 mb-3">
                                <div class="cuota-resumen__valores">
                                    <div><span>Programado</span><strong>{{ formatNumero(cuota_seleccionada.total) }} Bs</strong></div>
                                    <div><span>Cobrado</span><strong class="text-success">{{ formatNumero(calcularTotalPagado(cuota_seleccionada)) }} Bs</strong></div>
                                    <div><span>Condonado</span><strong>{{ formatNumero(calcularTotalCondonado(cuota_seleccionada)) }} Bs</strong></div>
                                    <div class="cuota-resumen__pendiente"><span>A pagar</span><strong>{{ formatNumero(cuota_seleccionada.total_a_pagar) }} Bs</strong></div>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="progress flex-grow-1" role="progressbar" :aria-valuenow="Number(calcularPorcentaje(cuota_seleccionada))" aria-valuemin="0" aria-valuemax="100" aria-label="Porcentaje cubierto" style="height: 6px;">
                                        <div class="progress-bar bg-success" :style="{ width: calcularPorcentaje(cuota_seleccionada) + '%' }"></div>
                                    </div>
                                    <small class="text-muted text-nowrap">{{ calcularPorcentaje(cuota_seleccionada) }}% cubierto</small>
                                </div>
                                <small class="text-muted">Cobros + condonaciones · Importes al {{ formatFecha(cuota_seleccionada.fecha_corte) }}</small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0">Desglose de deuda</h6>
                                <small class="text-muted">Importes en Bs</small>
                            </div>
                            <div class="table-responsive mb-2">
                                <table class="table table-sm table-bordered align-middle bg-white text-end mb-0 tabla-detalle-cuota">
                                    <thead><tr><th class="text-start">Concepto</th><th>Cobrado</th><th>Condonado</th><th>Pendiente</th></tr></thead>
                                    <tbody>
                                        <tr><th class="text-start">Capital</th><td>{{ formatNumero(cuota_seleccionada.capital_cobrado_total) }}</td><td>—</td><td>{{ formatNumero(cuota_seleccionada.capital_neto) }}</td></tr>
                                        <template v-if="cuota_seleccionada.desglose_cobros_interes_completo">
                                            <tr><th class="text-start">Interés ordinario</th><td>{{ formatNumero(cuota_seleccionada.interes_ordinario_cobrado) }}</td><td>{{ formatNumero(cuota_seleccionada.interes_ordinario_condonado) }}</td><td>{{ formatNumero(cuota_seleccionada.interes_devengado_neto) }}</td></tr>
                                            <tr><th class="text-start">Interés moratorio</th><td>{{ formatNumero(cuota_seleccionada.interes_moratorio_cobrado) }}</td><td>{{ formatNumero(cuota_seleccionada.interes_moratorio_condonado) }}</td><td>{{ formatNumero(cuota_seleccionada.interes_moratorio_neto) }}</td></tr>
                                        </template>
                                        <tr v-else><th class="text-start">Interés ordinario + moratorio<br><small class="text-muted">Historial sin desglose completo</small></th><td>{{ formatNumero(cuota_seleccionada.interes_cobrado_total) }}</td><td>{{ formatNumero(cuota_seleccionada.interes_condonado_total) }}</td><td>{{ formatNumero(cuota_seleccionada.interes_acumulado_neto) }}</td></tr>
                                        <tr><th class="text-start">Multa</th><td>{{ formatNumero(cuota_seleccionada.mora_cobrado_total) }}</td><td>{{ formatNumero(cuota_seleccionada.mora_condonado_total) }}</td><td>{{ formatNumero(cuota_seleccionada.mora_fija_neta) }}</td></tr>
                                    </tbody>
                                    <tfoot class="table-primary fw-bold"><tr><th class="text-start">Totales</th><td>{{ formatNumero(calcularTotalPagado(cuota_seleccionada)) }}</td><td>{{ formatNumero(calcularTotalCondonado(cuota_seleccionada)) }}</td><td>{{ formatNumero(cuota_seleccionada.total_a_pagar) }}</td></tr></tfoot>
                                </table>
                            </div>
                            <div class="small text-muted mb-2">Total registrado antes de condonar: <strong>{{ formatNumero(cuota_seleccionada.total_registrado_antes_condonacion) }} Bs</strong> · Capital: {{ formatNumero(cuota_seleccionada.capital) }} · Interés: {{ formatNumero(cuota_seleccionada.interes_total_registrado) }} · Multa: {{ formatNumero(cuota_seleccionada.multa_total_registrada) }}</div>
                            <div v-if="!cuota_seleccionada.desglose_cobros_interes_completo || cuota_seleccionada.origen_desglose_interes === 'sin_desglose_historico'" class="alert alert-warning py-2 px-3 small mb-2">
                                Este registro antiguo no conserva todo el desglose de interés. Se muestra el total registrado sin inventar una distribución.
                            </div>
                            <details class="small text-muted mb-3">
                                <summary class="detalle-aclaracion">Cómo se calculan estos importes</summary>
                                <div class="pt-1">La multa se calcula por crédito; aquí figura lo aplicado a esta cuota. Es distinta del interés moratorio. En cobros nuevos, el interés se aplica primero al ordinario y luego al moratorio; las condonaciones se aplican antes del cobro. Un pendiente de cero no implica que el importe original haya sido cero.</div>
                            </details>
                        </template>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h6 class="fw-bold mb-0">Historial de pagos</h6>
                            <small v-if="!cargando_pagos" class="text-muted">{{ pagos_cuota.length }} {{ pagos_cuota.length === 1 ? 'registro' : 'registros' }}</small>
                        </div>
                        <div v-if="cargando_pagos" class="text-center py-3" role="status">
                            <div class="spinner-border spinner-border-sm text-success" aria-hidden="true"></div>
                            <span class="ms-2 text-muted small">Cargando historial...</span>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-bordered table-striped table-sm text-end align-middle mb-0 tabla-historial-cuota">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-start">Fecha / recibo</th>
                                        <th>Capital</th>
                                        <th>Interés</th>
                                        <th class="text-danger">Multa</th>
                                        <th>Total cobrado</th>
                                        <th class="bg-warning bg-opacity-25">Condonado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(pago, i) in pagos_cuota" :key="i">
                                        <td class="text-start">
                                            <strong class="text-dark text-nowrap">{{ formatFecha(pago.fecha_pago) }}</strong>
                                            <small class="d-block text-muted">Recibo #{{ pago.id }}<template v-if="pago.nombre_usuario"> · {{ pago.nombre_usuario }}</template></small>
                                        </td>
                                        <td>{{ formatNumero(pago.pago_capital) }}</td>
                                        <td>
                                            <strong>{{ formatNumero(pago.pago_interes) }}</strong>
                                            <template v-if="pago.desglose_interes">
                                                <small class="d-block text-muted">Ord.: {{ formatNumero(pago.desglose_interes.ordinario_cobrado) }} · Mor.: {{ formatNumero(pago.desglose_interes.moratorio_cobrado) }}</small>
                                            </template>
                                            <small v-else class="d-block text-muted">Sin desglose histórico</small>
                                        </td>
                                        <td class="text-danger">{{ formatNumero(pago.pago_mora) }}</td>
                                        <td class="fw-bold text-success bg-success bg-opacity-10">
                                            {{ formatNumero(parseFloat(pago.pago_capital) + parseFloat(pago.pago_interes) + parseFloat(pago.pago_mora)) }} Bs
                                        </td>
                                        <td class="bg-warning bg-opacity-10">
                                            <template v-if="parseFloat(pago.monto_condonado) > 0">
                                                <strong class="text-dark">{{ formatNumero(pago.monto_condonado) }} Bs</strong>
                                                <small class="d-block text-muted">
                                                    <span v-if="parseFloat(pago.monto_condonado_interes) > 0">Int: {{ formatNumero(pago.monto_condonado_interes) }}</span>
                                                    <span v-if="parseFloat(pago.monto_condonado_interes) > 0 && parseFloat(pago.monto_condonado_mora) > 0"> · </span>
                                                    <span v-if="parseFloat(pago.monto_condonado_mora) > 0" class="text-danger">Multa: {{ formatNumero(pago.monto_condonado_mora) }}</span>
                                                </small>
                                                <div v-if="pago.desglose_interes" class="small text-muted">
                                                    <small class="d-block">Ord.: {{ formatNumero(pago.desglose_interes.ordinario_condonado) }} · Mor.: {{ formatNumero(pago.desglose_interes.moratorio_condonado) }}</small>
                                                </div>
                                                <small v-if="pago.motivo_condonacion" class="d-block text-muted motivo-condonacion" :title="pago.motivo_condonacion">Motivo: {{ pago.motivo_condonacion }}</small>
                                            </template>
                                            <span v-else class="text-muted">—</span>
                                        </td>
                                    </tr>
                                    <tr v-if="pagos_cuota.length === 0">
                                        <td colspan="6" class="text-muted text-center py-3">Esta cuota aún no tiene pagos registrados.</td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="pagos_cuota.length > 0" class="table-light fw-bold">
                                    <tr>
                                        <td class="text-start">Totales</td>
                                        <td>{{ formatNumero(totalCapitalPagos()) }}</td>
                                        <td>{{ formatNumero(totalInteresPagos()) }}</td>
                                        <td class="text-danger">{{ formatNumero(totalMoraPagos()) }}</td>
                                        <td class="text-success bg-success bg-opacity-10">{{ formatNumero(totalPagadoPagos()) }} Bs</td>
                                        <td class="text-dark bg-warning bg-opacity-10">
                                            <span v-if="totalCondonadoPagos() > 0" class="fw-bold">{{ formatNumero(totalCondonadoPagos()) }} Bs</span>
                                            <span v-else class="text-muted">—</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-white py-2 px-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" @click="cerrarModalPagos()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'DetallePlanPago',
    props: {
        plan: {
            type: Object,
            required: true
        },
        cuotas: {
            type: Array,
            default: () => []
        },
        resumenMulta: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            cuota_seleccionada: null,
            pagos_cuota: [],
            cargando_pagos: false
        }
    },
    emits: ['cerrar', 'ver-ficha', 'ver-original'],
    computed: {
        totalPendiente() {
            return this.cuotas.reduce((total, cuota) => total + Math.round(Number(cuota.total_a_pagar || 0) * 100), 0) / 100;
        }
    },
    methods: {
        formatFecha(fechaISO) {
            if (!fechaISO) return 'N/A';
            return moment(fechaISO).format('DD/MM/YYYY');
        },
        formatNumero(numero) {
            if (numero === undefined || numero === null || isNaN(numero)) return '0.00';
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(numero);
        },
        generarPdfCuotasPlanPago() {
            const url = '/lista_cuotas_planpago_pdf?id_planpago=' + this.plan.id_plan_pago;
            window.open(url, '_blank');
        },
        
        // --- NUEVOS MÉTODOS PARA EL HISTORIAL DE PAGOS ---
        calcularTotalPagado(cuota) {
            if (!cuota) return 0;
            const cap = parseFloat(cuota.capital_cobrado_total) || 0;
            const int = parseFloat(cuota.interes_cobrado_total) || 0;
            const mora = parseFloat(cuota.mora_cobrado_total) || 0;
            return cap + int + mora;
        },

        calcularTotalCondonado(cuota) {
            if (!cuota) return 0;
            const intCond = parseFloat(cuota.interes_condonado_total) || 0;
            const moraCond = parseFloat(cuota.mora_condonado_total) || 0;
            return intCond + moraCond;
        },

        calcularPorcentaje(cuota) {
            if (!cuota) return '0.0';
            if (Number(cuota.estado) === 2) return '100.0';
            
            const totalCubierto = this.calcularTotalPagado(cuota) + this.calcularTotalCondonado(cuota);
            const totalCuota = totalCubierto + (parseFloat(cuota.total_a_pagar) || 0);
            
            let porcentaje = totalCuota > 0 ? (totalCubierto / totalCuota) * 100 : 0;
            if (porcentaje > 100) porcentaje = 100;
            
            return porcentaje.toFixed(1);
        },

        totalCapitalPagos() {
            return this.pagos_cuota.reduce((acc, p) => acc + (parseFloat(p.pago_capital) || 0), 0);
        },

        totalInteresPagos() {
            return this.pagos_cuota.reduce((acc, p) => acc + (parseFloat(p.pago_interes) || 0), 0);
        },

        totalMoraPagos() {
            return this.pagos_cuota.reduce((acc, p) => acc + (parseFloat(p.pago_mora) || 0), 0);
        },

        totalPagadoPagos() {
            return this.pagos_cuota.reduce((acc, p) => acc + (parseFloat(p.pago_capital) || 0) + (parseFloat(p.pago_interes) || 0) + (parseFloat(p.pago_mora) || 0), 0);
        },

        totalCondonadoPagos() {
            return this.pagos_cuota.reduce((acc, p) => acc + (parseFloat(p.monto_condonado) || 0), 0);
        },

        async verDetallePagos(cuota) {
            this.cuota_seleccionada = cuota;
            this.pagos_cuota = [];
            this.cargando_pagos = true;
            
            // Abrimos el modal
            const modalEl = document.getElementById('modalHistorialPagos');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            try {
                const response = await axios.get('/get_pagos_cuota', { params: { id_cuota: cuota.id } });
                this.pagos_cuota = response.data;
            } catch (error) {
                console.error("Error al obtener los pagos:", error);
                Swal.fire('Error', 'No se pudo cargar el historial de pagos.', 'error');
            } finally {
                this.cargando_pagos = false;
            }
        },
        cerrarModalPagos() {
            const modalEl = document.getElementById('modalHistorialPagos');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
            this.cuota_seleccionada = null;
            this.pagos_cuota = [];
        },

        getEstadoCuota(cuota) {
            if (cuota.estado == 2) {
                return { texto: 'Pagado', clase: 'bg-success text-white' };
            }
            if (cuota.estado == 3) {
                return { texto: 'Pago Parcial', clase: 'bg-warning text-dark border border-warning' };
            }
            if (cuota.estado == 0) {
                return { texto: 'Anulado', clase: 'bg-dark text-white' };
            }
            if (cuota.estado == 1) {
                if (cuota.dias_pasados > 0) {
                    return { texto: 'Vencida', clase: 'bg-danger text-white' };
                } else {
                    return { texto: 'Pendiente', clase: 'bg-info text-white' }; 
                }
            }
            return { texto: 'Indefinido', clase: 'bg-light text-dark' };
        }
    }
}
</script>

<style scoped>
    .table-listado-cuotas th, .table-listado-cuotas td {
        font-size: 11px !important;
        padding: 0.4rem 0.3rem;
        line-height: 1.25;
        font-variant-numeric: tabular-nums;
    }
    
    .table-listado-cuotas {
        min-width: 1200px;
    }
    .table-listado-cuotas small {
        font-size: 10px;
        line-height: 1.2;
    }
    .table-listado-cuotas .badge {
        font-size: 10px;
    }
    .resumen-cuotas {
        font-size: 12px;
    }
    .resumen-multa {
        padding: 0.55rem 0.75rem;
        border: 1px solid #f0c77b;
        border-left: 3px solid #dc8a16;
        border-radius: 0.4rem;
        background: #fffaf0;
    }
    .resumen-multa__titulo {
        color: #805007;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .resumen-multa__valores {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem 1.25rem;
    }
    .resumen-multa__valores div {
        display: flex;
        gap: 0.35rem;
        white-space: nowrap;
    }
    .resumen-multa__valores strong {
        font-variant-numeric: tabular-nums;
    }

    .modal-cuota .modal-body {
        font-size: 0.82rem;
    }
    .cuota-resumen__valores {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.4rem;
    }
    .cuota-resumen__valores > div {
        display: flex;
        flex-direction: column;
        padding: 0.2rem 0.5rem;
        border-right: 1px solid #e9ecef;
        min-width: 0;
    }
    .cuota-resumen__valores > div:last-child {
        border-right: 0;
    }
    .cuota-resumen__valores span {
        color: #6c757d;
        font-size: 0.7rem;
    }
    .cuota-resumen__valores strong {
        font-size: 0.9rem;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .cuota-resumen__pendiente strong {
        color: #0d6efd;
    }
    .tabla-detalle-cuota,
    .tabla-historial-cuota {
        font-size: 0.76rem;
        font-variant-numeric: tabular-nums;
    }
    .tabla-detalle-cuota th,
    .tabla-detalle-cuota td,
    .tabla-historial-cuota th,
    .tabla-historial-cuota td {
        padding: 0.3rem 0.4rem;
    }
    .tabla-detalle-cuota {
        min-width: 520px;
    }
    .tabla-historial-cuota {
        min-width: 690px;
    }
    .tabla-historial-cuota small {
        font-size: 0.67rem;
        line-height: 1.2;
    }
    .motivo-condonacion {
        max-width: 145px;
        overflow-wrap: anywhere;
    }
    .detalle-aclaracion {
        cursor: pointer;
        width: fit-content;
    }
    @media (max-width: 575.98px) {
        .cuota-resumen__valores {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cuota-resumen__valores > div:nth-child(2) {
            border-right: 0;
        }
    }

    .hover-zoom {
        transition: transform 0.2s ease-in-out;
    }
    .hover-zoom:hover {
        transform: scale(1.05);
    }

    .btn-xs {
        padding: 1px 4px;
        font-size: 0.68rem;
        line-height: 1.2;
    }
</style>
