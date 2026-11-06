<template>
    <main class="credit-extract-management">
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <!-- VISTA 0: LISTADO DE CRÉDITOS Y FILTROS -->
                <div v-if="vista === 0" class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-warning py-1">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Extracto de Crédito de Clientes
                        </h5>
                    </div>

                    <div class="card-body py-2">
                        <div class="row g-2 mb-3 mt-1 align-items-start">
                            <div class="col-xl-4 col-lg-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary" title="Fecha de registro">
                                        <i class="far fa-calendar-alt" aria-hidden="true"></i><span class="ms-1">Registro</span>
                                    </span>
                                    <input v-model="filtros.fecha_inicio" type="date" class="form-control"
                                        aria-label="Registro desde" @change="getCreditos" />
                                    <input v-model="filtros.fecha_fin" type="date" class="form-control"
                                        aria-label="Registro hasta" @change="getCreditos" />
                                </div>
                                <small v-if="fechasInvalidas" class="text-danger fw-bold d-block mt-1">
                                    La fecha inicial no puede ser posterior a la final.
                                </small>
                            </div>

                            <div class="col-xl-6 col-lg-5">
                                <div class="input-group input-group-sm extracto-filtros">
                                    <select v-model="filtros.estado_plan" class="form-select" aria-label="Estado del crédito" @change="getCreditos">
                                        <option value="todos">Todos los estados</option>
                                        <option value="vigente">Vigentes</option>
                                        <option value="terminado">Terminados</option>
                                    </select>
                                    <input v-model="filtros.id_credito" type="text" inputmode="numeric" class="form-control extracto-codigo"
                                        placeholder="Código" aria-label="Código de crédito" @input="programarBusqueda" />
                                    <input v-model="filtros.buscar_cliente" type="search" class="form-control extracto-busqueda"
                                        placeholder="Nombre o CI del cliente" aria-label="Nombre o CI del cliente" @input="programarBusqueda" />
                                    <button type="button" class="btn btn-success" title="Buscar" aria-label="Buscar créditos" @click="getCreditos">
                                        <i class="fas fa-search" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-2 text-lg-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros">
                                    <i class="fas fa-eraser me-1" aria-hidden="true"></i> Limpiar filtros
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase small">
                                <i class="fas fa-list me-1 text-success" aria-hidden="true"></i>
                                Créditos encontrados <span class="badge bg-light text-dark border ms-1">{{ creditos.length }}</span>
                            </h6>
                        </div>

                        <div class="table-container-custom table-responsive">
                            <table class="table table-striped table-hover table-compact-custom extracto-list-table align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-center"># Cred.</th>
                                        <th>Cliente</th>
                                        <th>Asesor</th>
                                        <th class="text-end">Préstamo</th>
                                        <th class="text-end">Total plan</th>
                                        <th class="text-center">Tasa</th>
                                        <th class="text-center">F. inicio</th>
                                        <th class="text-center">F. fin</th>
                                        <th class="text-center">Cuotas</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Op.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in creditos" :key="item.id">
                                        <td class="text-center font-monospace fw-semibold text-secondary">{{ item.id }}</td>
                                        <td>
                                            <div class="extracto-cliente">
                                                <span class="text-dark fw-bold text-uppercase text-truncate" :title="item.cliente_nombre">{{ item.cliente_nombre }}</span>
                                                <span class="text-muted"><strong class="fw-semibold">CI:</strong> {{ item.cliente_ci || '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-secondary text-uppercase extracto-asesor" :title="item.asesor_nombre">{{ item.asesor_nombre || '-' }}</td>
                                        <td class="text-end font-monospace fw-semibold text-nowrap">{{ formatMoney(item.importe_solicitud, item.moneda) }}</td>
                                        <td class="text-end font-monospace fw-bold text-dark text-nowrap">{{ formatMoney(item.total_pagar, item.moneda) }}</td>
                                        <td class="text-center font-monospace">{{ item.tasa }}%</td>
                                        <td class="text-center text-secondary text-nowrap extracto-fecha">{{ formatDate(item.fecha_inicio) }}</td>
                                        <td class="text-center text-secondary text-nowrap extracto-fecha">{{ formatDate(item.fecha_fin) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border border-secondary-subtle extracto-plazo rounded">
                                                {{ item.nro_cuotas }} - {{ item.lapso_capital }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge extracto-estado rounded"
                                                :class="Number(item.estado) === 1 ? 'extracto-estado--vigente' : 'extracto-estado--terminado'">
                                                {{ Number(item.estado) === 1 ? 'Vigente' : 'Terminado' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-link text-success p-1 dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    :aria-label="'Opciones del crédito ' + item.id" title="Opciones">
                                                    <i class="fas fa-ellipsis-h fa-lg" aria-hidden="true"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <li><button type="button" class="dropdown-item" @click="verDetalle(item.id)">
                                                        <i class="fas fa-eye me-2 text-info" aria-hidden="true"></i>Ver detalle
                                                    </button></li>
                                                    <li><button type="button" class="dropdown-item" @click="generarPdfCuotasPlanPago(item.id)">
                                                        <i class="fas fa-file-pdf me-2 text-danger" aria-hidden="true"></i>Exportar PDF
                                                    </button></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="creditos.length === 0">
                                        <td colspan="11" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-folder-open fa-3x mb-3" aria-hidden="true"></i>
                                                <h5 class="fw-normal">No se encontraron créditos</h5>
                                                <p class="small mb-0">Intenta ajustar los filtros de búsqueda.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- VISTA 1: DETALLE DE EXTRACTO -->
                <div v-if="vista === 1" class="card shadow-sm border-0">
                    <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-dark text-uppercase">{{ tituloFormulario }}</h5>
                        <button type="button" class="btn-close btn-close-dark" aria-label="Volver al listado" @click="cerrarFormulario"></button>
                    </div>

                    <div class="card-body bg-light p-3">
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold text-uppercase mb-2 extracto-section-title"><i class="fas fa-user-tie me-2 text-success"></i>Cliente</h6>
                                        <div class="fw-bold text-dark text-uppercase">{{ detalle.credito.cliente_nombre }}</div>
                                        <div class="text-muted small">CI: {{ detalle.credito.cliente_ci }} {{ detalle.credito.cliente_expedicion }}</div>
                                        <div class="text-muted small">Actividad: {{ detalle.credito.cliente_actividad || 'No registrada' }}</div>
                                        <div class="text-muted small">Asesor: {{ detalle.credito.asesor_nombre || 'No registrado' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <h6 class="fw-bold text-uppercase mb-0 extracto-section-title"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Crédito #{{ detalle.credito.id }}</h6>
                                            <span class="badge extracto-estado rounded"
                                                :class="Number(detalle.credito.estado) === 1 ? 'extracto-estado--vigente' : 'extracto-estado--terminado'">
                                                {{ Number(detalle.credito.estado) === 1 ? 'Vigente' : 'Terminado' }}
                                            </span>
                                        </div>
                                        <div class="extracto-datos-credito small">
                                            <div><span>Monto prestado</span><strong>{{ formatMoney(detalle.credito.importe_solicitud, detalle.credito.moneda) }}</strong></div>
                                            <div><span>Total del plan</span><strong>{{ formatMoney(detalle.credito.total_pagar, detalle.credito.moneda) }}</strong></div>
                                            <div><span>Tasa / plazo</span><strong>{{ detalle.credito.tasa }}% · {{ detalle.credito.nro_cuotas }} {{ detalle.credito.lapso_capital }}</strong></div>
                                            <div><span>Inicio / fin</span><strong>{{ formatDate(detalle.credito.fecha_inicio) }} · {{ formatDate(detalle.credito.fecha_fin) }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-uppercase mb-0 extracto-section-title">Situación del crédito</h6>
                                    <span class="small text-muted">Al {{ formatDate(detalle.fecha_corte) }} · {{ detalle.credito.moneda }}</span>
                                </div>
                                <div class="extracto-resumen-grid">
                                    <div><span>Programado</span><strong>{{ formatAmount(detalle.totales.programado) }}</strong></div>
                                    <div><span>Variación al corte</span><strong>{{ formatSignedAmount(detalle.totales.variacion) }}</strong></div>
                                    <div><span>Condonado</span><strong>{{ formatAmount(detalle.totales.condonado) }}</strong></div>
                                    <div class="extracto-resumen-grid__exigible"><span>Total neto al corte</span><strong>{{ formatAmount(detalle.totales.exigible) }}</strong></div>
                                    <div><span>Cobrado</span><strong class="text-success">{{ formatAmount(detalle.totales.cobrado) }}</strong></div>
                                    <div><span>Pendiente</span><strong class="text-primary">{{ formatAmount(detalle.totales.pendiente) }}</strong></div>
                                </div>
                                <p class="small text-muted mb-0 mt-2">Programado + variación − condonado = total neto al corte = cobrado + pendiente. La variación incluye recargos o interés futuro aún no devengado. El pendiente incluye capital de cuotas futuras; no representa únicamente lo vencido.</p>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h6 class="fw-bold text-uppercase mb-0 extracto-section-title"><i class="fas fa-list-ol me-2 text-warning"></i>Cronograma y pagos</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm" @click="generarPdfCuotasPlanPago(detalle.credito.id)">
                                    <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle extracto-detalle-table mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Vence</th>
                                                <th class="text-end">Capital</th>
                                                <th class="text-end">Interés fijo</th>
                                                <th class="text-end" title="Saldo programado del plan y capital aún pendiente de esta cuota">Saldo capital</th>
                                                <th class="text-end">Programado</th>
                                                <th class="text-end">Int. devengado<small class="d-block">Total registrado</small></th>
                                                <th class="text-end">Int. moratorio<small class="d-block">Total registrado</small></th>
                                                <th class="text-end">Multa<small class="d-block">Total registrado</small></th>
                                                <th class="text-end">Condonado</th>
                                                <th class="text-end">Total neto</th>
                                                <th class="text-end">Cobrado</th>
                                                <th class="text-end">Pendiente</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Pagos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template v-for="cuota in detalle.cuotas" :key="cuota.id">
                                                <tr>
                                                    <td class="text-center fw-bold text-secondary">{{ cuota.numero }}</td>
                                                    <td class="text-nowrap">{{ formatDate(cuota.fecha) }}</td>
                                                    <td class="text-end">{{ formatAmount(cuota.capital) }}</td>
                                                    <td class="text-end">{{ formatAmount(cuota.interes) }}</td>
                                                    <td class="text-end">
                                                        <strong>{{ formatAmount(cuota.saldo_capital) }}</strong>
                                                        <small class="d-block text-muted">Pend. cuota: {{ formatAmount(cuota.capital_neto) }}</small>
                                                    </td>
                                                    <td class="text-end fw-semibold">{{ formatAmount(cuota.total) }}</td>
                                                    <td class="text-end extracto-concepto">
                                                        <template v-if="cuota.desglose_cobros_interes_completo">
                                                            <strong>{{ formatAmount(cuota.interes_ordinario_total_extracto) }}</strong>
                                                            <small class="d-block text-success">Cob.: {{ formatAmount(cuota.interes_ordinario_cobrado) }}</small>
                                                            <small class="d-block text-muted">Cond.: {{ formatAmount(cuota.interes_ordinario_condonado) }}</small>
                                                        </template>
                                                        <small v-else class="d-block text-muted">Sin desglose histórico</small>
                                                        <small class="d-block fw-semibold">Pend.: {{ formatAmount(cuota.interes_devengado_neto) }}</small>
                                                    </td>
                                                    <td class="text-end extracto-concepto">
                                                        <template v-if="cuota.desglose_cobros_interes_completo">
                                                            <strong>{{ formatAmount(cuota.interes_moratorio_total_extracto) }}</strong>
                                                            <small class="d-block text-success">Cob.: {{ formatAmount(cuota.interes_moratorio_cobrado) }}</small>
                                                            <small class="d-block text-muted">Cond.: {{ formatAmount(cuota.interes_moratorio_condonado) }}</small>
                                                        </template>
                                                        <small v-else class="d-block text-muted">Sin desglose histórico</small>
                                                        <small class="d-block fw-semibold">Pend.: {{ formatAmount(cuota.interes_moratorio_neto) }}</small>
                                                    </td>
                                                    <td class="text-end extracto-concepto">
                                                        <strong>{{ formatAmount(cuota.multa_total_registrada) }}</strong>
                                                        <small class="d-block text-success">Cob.: {{ formatAmount(cuota.mora_cobrado_total) }}</small>
                                                        <small class="d-block text-muted">Cond.: {{ formatAmount(cuota.mora_condonado_total) }}</small>
                                                        <small class="d-block fw-semibold">Pend.: {{ formatAmount(cuota.mora_fija_neta) }}</small>
                                                    </td>
                                                    <td class="text-end">{{ formatAmount(cuota.total_condonado) }}</td>
                                                    <td class="text-end fw-bold text-primary">
                                                        {{ formatAmount(cuota.exigible_neto) }}
                                                        <small class="d-block text-muted fw-normal">Variación: {{ formatSignedAmount(cuota.variacion_programado) }}</small>
                                                    </td>
                                                    <td class="text-end fw-bold text-success">{{ formatAmount(cuota.total_cobrado) }}</td>
                                                    <td class="text-end fw-bold">{{ formatAmount(cuota.total_a_pagar) }}</td>
                                                    <td class="text-center"><span class="badge extracto-cuota-estado" :class="getBadgeCuotaClass(cuota.estado)">{{ getBadgeCuotaText(cuota.estado) }}</span></td>
                                                    <td class="text-center">
                                                        <button v-if="cuota.pagos.length" type="button" class="btn btn-sm btn-outline-success"
                                                            :aria-expanded="isCuotaExpanded(cuota.id)" :aria-label="(isCuotaExpanded(cuota.id) ? 'Ocultar' : 'Ver') + ' pagos de la cuota ' + cuota.numero"
                                                            @click="toggleCuotaPagos(cuota.id)">
                                                            {{ cuota.pagos.length }} <i class="fas ms-1" :class="isCuotaExpanded(cuota.id) ? 'fa-chevron-up' : 'fa-chevron-down'" aria-hidden="true"></i>
                                                        </button>
                                                        <span v-else class="text-muted">—</span>
                                                    </td>
                                                </tr>
                                                <tr v-if="isCuotaExpanded(cuota.id) && cuota.pagos.length" class="extracto-pagos-row">
                                                    <td colspan="15">
                                                        <div class="small fw-bold mb-2">Pagos aplicados a la cuota {{ cuota.numero }}</div>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered bg-white align-middle mb-0 extracto-pagos-table">
                                                                <thead><tr>
                                                                    <th>Fecha / recibo</th><th class="text-end">Capital</th><th class="text-end">Interés</th>
                                                                    <th class="text-end">Multa</th><th class="text-end">Cobrado</th>
                                                                    <th class="text-end">Condonado</th><th>Forma</th><th class="text-center">Detalle</th>
                                                                </tr></thead>
                                                                <tbody>
                                                                    <tr v-for="pago in cuota.pagos" :key="pago.id">
                                                                        <td><strong>{{ formatDate(pago.fecha_pago) }}</strong><small class="d-block text-muted">#{{ pago.codigo_transaccion || pago.id }}</small></td>
                                                                        <td class="text-end">{{ formatAmount(pago.pago_capital) }}</td>
                                                                        <td class="text-end">{{ formatAmount(pago.pago_interes) }}</td>
                                                                        <td class="text-end">{{ formatAmount(pago.pago_mora) }}</td>
                                                                        <td class="text-end fw-bold text-success">{{ formatAmount(pago.monto_pago) }}</td>
                                                                        <td class="text-end">{{ formatAmount(pago.monto_condonado) }}</td>
                                                                        <td>{{ pago.forma_pago || 'Sin clasificar' }}</td>
                                                                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary" @click="verDetallePago(pago)">Ver</button></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr v-if="!detalle.cuotas.length"><td colspan="15" class="text-center text-muted py-4">No hay cuotas registradas.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DETALLE DE UN ABONO A UNA CUOTA -->
                <div v-if="mostrarModalPago && pagoSeleccionado" class="custom-modal-backdrop" @click.self="mostrarModalPago = false">
                    <div class="custom-modal-content card shadow-lg border-0 animate-scale-in" role="dialog" aria-modal="true" aria-labelledby="tituloDetalleAbono">
                        <div class="card-header bg-success py-2 d-flex justify-content-between align-items-center text-white">
                            <h6 id="tituloDetalleAbono" class="my-0 fw-bold"><i class="fas fa-receipt me-2"></i>Abono aplicado a esta cuota</h6>
                            <button type="button" class="btn-close btn-close-white" @click="mostrarModalPago = false" aria-label="Cerrar detalle"></button>
                        </div>
                        <div class="card-body p-3 small">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <div><span class="d-block text-muted">Fecha / recibo</span><strong>{{ formatDate(pagoSeleccionado.fecha_pago) }} · #{{ pagoSeleccionado.codigo_transaccion || pagoSeleccionado.id }}</strong></div>
                                <div class="text-end"><span class="d-block text-muted">Cobrado en esta cuota</span><strong class="text-success">{{ formatMoney(pagoSeleccionado.monto_pago, detalle.credito.moneda) }}</strong></div>
                            </div>
                            <div class="extracto-abono-desglose border rounded p-2 mb-2">
                                <div><span>Capital</span><strong>{{ formatAmount(pagoSeleccionado.pago_capital) }}</strong></div>
                                <div><span>Interés</span><strong>{{ formatAmount(pagoSeleccionado.pago_interes) }}</strong></div>
                                <div><span>Multa</span><strong>{{ formatAmount(pagoSeleccionado.pago_mora) }}</strong></div>
                                <div><span>Condonado</span><strong>{{ formatAmount(pagoSeleccionado.monto_condonado) }}</strong></div>
                            </div>
                            <div v-if="pagoSeleccionado.desglose_interes" class="text-muted mb-2">
                                Interés cobrado: ordinario {{ formatAmount(pagoSeleccionado.desglose_interes.ordinario_cobrado) }},
                                moratorio {{ formatAmount(pagoSeleccionado.desglose_interes.moratorio_cobrado) }}.
                            </div>
                            <div v-else-if="Number(pagoSeleccionado.pago_interes) > 0" class="text-muted mb-2">El pago histórico no conserva el desglose del interés.</div>
                            <div v-if="Number(pagoSeleccionado.monto_condonado) > 0" class="small mb-2">
                                <div>Interés condonado: {{ formatAmount(pagoSeleccionado.monto_condonado_interes) }} · Multa condonada: {{ formatAmount(pagoSeleccionado.monto_condonado_mora) }}</div>
                                <div v-if="pagoSeleccionado.motivo_condonacion" class="text-muted">Motivo: {{ pagoSeleccionado.motivo_condonacion }}</div>
                            </div>
                            <div class="border-top pt-2">
                                <strong>Forma de pago: {{ pagoSeleccionado.forma_pago || 'Sin clasificar' }}</strong>
                                <div v-if="pagoSeleccionado.partes_operacion?.length" class="mt-1">
                                    <small class="text-muted d-block">Distribución del recibo completo (puede incluir otras cuotas):</small>
                                    <div v-if="pagoSeleccionado.monto_operacion !== null" class="d-flex justify-content-between gap-2">
                                        <span>Total cobrado en el recibo</span><strong>{{ formatAmount(pagoSeleccionado.monto_operacion) }}</strong>
                                    </div>
                                    <div v-for="(parte, index) in pagoSeleccionado.partes_operacion" :key="index" class="d-flex justify-content-between gap-2">
                                        <span>{{ parte.forma_pago }}<template v-if="parte.referencia"> · {{ parte.referencia }}</template></span>
                                        <strong>{{ formatAmount(parte.monto) }}</strong>
                                    </div>
                                    <small v-if="Number(pagoSeleccionado.cambio_operacion) > 0" class="text-muted">Cambio entregado: {{ formatAmount(pagoSeleccionado.cambio_operacion) }}</small>
                                </div>
                                <small v-else class="text-muted d-block">No existe un desglose de formas para este recibo histórico.</small>
                                <small class="text-muted d-block mt-1">Cajero: {{ pagoSeleccionado.cajero_nombre || 'No registrado' }}</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-2 text-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="mostrarModalPago = false">Cerrar</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            preloader: false,
            busquedaTimer: null,
            consultaListado: 0,
            vista: 0,
            creditos: [],
            filtros: {
                id_credito: '',
                buscar_cliente: '',
                estado_plan: 'todos',
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD')
            },
            detalle: {
                credito: {},
                cuotas: [],
                totales: { programado: 0, variacion: 0, condonado: 0, exigible: 0, cobrado: 0, pendiente: 0 },
                fecha_corte: null
            },
            pagoSeleccionado: null,
            mostrarModalPago: false,
            expandedCuotas: {}
        };
    },
    computed: {
        fechasInvalidas() {
            return Boolean(this.filtros.fecha_inicio && this.filtros.fecha_fin
                && this.filtros.fecha_inicio > this.filtros.fecha_fin);
        },
        tituloFormulario() {
            if (this.detalle && this.detalle.credito && this.detalle.credito.id) {
                return `Detalles de Extracto — Crédito #${this.detalle.credito.id}`;
            }
            return "Detalles de Extracto";
        }
    },
    methods: {
        programarBusqueda() {
            clearTimeout(this.busquedaTimer);
            this.busquedaTimer = setTimeout(() => this.getCreditos(), 300);
        },
        async getCreditos() {
            clearTimeout(this.busquedaTimer);
            const consulta = ++this.consultaListado;
            if (this.fechasInvalidas) {
                this.preloader = false;
                return;
            }
            this.preloader = true;
            try {
                const response = await axios.get('/get_creditos_rep', { params: this.filtros });
                if (consulta === this.consultaListado) this.creditos = response.data;
            } catch (error) {
                if (consulta !== this.consultaListado) return;
                console.error("Error al obtener créditos:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un problema al obtener el listado de créditos.'
                });
            } finally {
                if (consulta === this.consultaListado) this.preloader = false;
            }
        },
        async verDetalle(id_plan_pago) {
            this.preloader = true;
            try {
                const response = await axios.get('/get_detalle_credito_extracto', {
                    params: { id_plan_pago }
                });
                this.detalle = response.data;
                this.expandedCuotas = {}; // Resetear estados de expansión
                this.vista = 1; // Cambiar a la vista de detalle
            } catch (error) {
                console.error("Error al obtener detalle del crédito:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información detallada del crédito.'
                });
            } finally {
                this.preloader = false;
            }
        },
        generarPdfCuotasPlanPago(id_plan_pago) {
            const url = `/rep_extracto_credito?id_plan_pago=${id_plan_pago}`;
            window.open(url, '_blank');
        },
        limpiarFiltros() {
            clearTimeout(this.busquedaTimer);
            this.filtros = {
                id_credito: '',
                buscar_cliente: '',
                estado_plan: 'todos',
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD')
            };
            this.getCreditos();
        },
        cerrarFormulario() {
            this.vista = 0;
        },
        verDetallePago(pago) {
            this.pagoSeleccionado = pago;
            this.mostrarModalPago = true;
        },
        toggleCuotaPagos(cuotaId) {
            this.expandedCuotas = {
                ...this.expandedCuotas,
                [cuotaId]: !this.expandedCuotas[cuotaId]
            };
        },
        isCuotaExpanded(cuotaId) {
            return !!this.expandedCuotas[cuotaId];
        },
        formatMoney(value, currency = 'Bs.') {
            if (value === null || value === undefined) return '-';
            const formatted = new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
            return `${formatted} ${currency}`;
        },
        formatAmount(value) {
            const amount = Number(value);
            return Number.isFinite(amount)
                ? new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount)
                : '—';
        },
        formatSignedAmount(value) {
            const amount = Number(value);
            if (!Number.isFinite(amount)) return '—';
            return `${amount >= 0 ? '+' : '−'}${this.formatAmount(Math.abs(amount))}`;
        },
        formatDate(date) {
            if (!date) return '-';
            return moment(date).format('DD/MM/YYYY');
        },
        getBadgeCuotaClass(estado) {
            switch(Number(estado)) {
                case 0: return 'bg-secondary text-white';
                case 1: return 'bg-primary text-white';
                case 2: return 'bg-success text-white';
                case 3: return 'bg-warning text-dark';
                default: return 'bg-light text-dark';
            }
        },
        getBadgeCuotaText(estado) {
            switch(Number(estado)) {
                case 0: return 'Anulada';
                case 1: return 'Pendiente';
                case 2: return 'Pagada';
                case 3: return 'P. Parcial';
                default: return 'Desconocido';
            }
        }
    },
    async mounted() {
        await this.getCreditos();
    },
    beforeUnmount() {
        clearTimeout(this.busquedaTimer);
    }
};
</script>

<style scoped>
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}
.border-start-4 {
    border-left-width: 4px !important;
}
.table-container-custom:has(.dropdown-menu.show) {
    padding-bottom: 130px;
}
.table-container-custom .dropdown-menu {
    min-width: 155px;
}
.extracto-list-table {
    min-width: 1120px;
    margin-bottom: 0;
    font-size: 0.82rem;
}
.extracto-list-table th {
    padding: 6px 8px;
    border-bottom: 2px solid #157347 !important;
    background-color: #198754 !important;
    color: #fff !important;
    font-size: 0.72rem !important;
    font-weight: 700;
    letter-spacing: 0.35px;
    white-space: nowrap;
}
.extracto-list-table td {
    padding: 6px 8px !important;
    vertical-align: middle;
}
.extracto-cliente {
    display: flex;
    flex-direction: column;
    max-width: 195px;
}
.extracto-cliente > span:first-child {
    font-size: 0.84rem;
}
.extracto-cliente > span:last-child {
    font-size: 0.71rem;
}
.extracto-asesor {
    max-width: 155px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.78rem;
}
.extracto-fecha {
    font-size: 0.77rem;
}
.extracto-plazo {
    padding: 4px 7px;
    font-size: 0.72rem;
    font-weight: 500;
    text-transform: none;
    white-space: nowrap;
}
.extracto-estado {
    display: inline-block;
    min-width: 82px;
    padding: 4px 7px;
    font-size: 0.64rem;
    letter-spacing: 0.25px;
    text-transform: uppercase;
}
.extracto-estado--vigente {
    background-color: #198754 !important;
    color: #fff !important;
}
.extracto-estado--terminado {
    background-color: #475569 !important;
    color: #fff !important;
    border: 1px solid #334155;
}
.extracto-section-title {
    font-size: 0.82rem;
    letter-spacing: 0.35px;
}
.extracto-datos-credito {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.3rem 1rem;
}
.extracto-datos-credito > div {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    border-bottom: 1px solid #edf0f2;
}
.extracto-datos-credito span,
.extracto-resumen-grid span {
    color: #6c757d;
}
.extracto-datos-credito strong,
.extracto-resumen-grid strong {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.extracto-resumen-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.5rem;
}
.extracto-resumen-grid > div {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.5rem 0.6rem;
    border: 1px solid #e5e9ec;
    border-radius: 0.4rem;
    background: #f8faf9;
    font-size: 0.73rem;
}
.extracto-resumen-grid strong {
    font-size: 0.94rem;
    text-align: left;
}
.extracto-resumen-grid__exigible {
    border-color: #b6d4fe !important;
    background: #eef5ff !important;
    color: #0b5ed7;
}
.extracto-detalle-table {
    min-width: 1650px;
    font-size: 0.76rem;
    font-variant-numeric: tabular-nums;
}
.extracto-detalle-table th {
    padding: 0.55rem 0.4rem;
    background: #eaf4ee;
    color: #1c3c2c;
    font-size: 0.68rem;
    text-transform: uppercase;
    white-space: nowrap;
}
.extracto-detalle-table td {
    padding: 0.4rem;
}
.extracto-concepto {
    min-width: 105px;
    line-height: 1.35;
}
.extracto-cuota-estado {
    min-width: 78px;
    padding: 0.3rem 0.4rem;
    font-size: 0.63rem;
    text-transform: uppercase;
}
.extracto-pagos-row > td {
    background: #edf5f0 !important;
    padding: 0.75rem 1rem !important;
}
.extracto-pagos-table {
    font-size: 0.72rem;
    min-width: 760px;
}
.extracto-pagos-table th {
    background: #f1f5f9;
}
.extracto-abono-desglose {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.4rem 1rem;
    background: #f8faf9;
}
.extracto-abono-desglose > div {
    display: flex;
    justify-content: space-between;
    gap: 0.4rem;
}
@media (max-width: 991.98px) {
    .extracto-resumen-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .extracto-datos-credito,
    .extracto-resumen-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
.extracto-codigo {
    max-width: 100px;
}
.extracto-busqueda {
    min-width: 145px;
}
@media (max-width: 991.98px) {
    .extracto-filtros {
        flex-wrap: wrap;
    }
    .extracto-filtros > .form-select,
    .extracto-filtros > .form-control {
        flex: 1 1 145px;
        max-width: none;
    }
}
.btn-group .dropdown-menu {
    border: none;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    border-radius: 6px;
    padding: 6px 0;
}
.btn-group .dropdown-item {
    font-size: 11px;
    padding: 6px 12px;
    transition: all 0.2s ease;
}
.btn-group .dropdown-item:hover {
    background-color: rgba(0, 255, 170, 0.15) !important;
    color: #00ffaa !important;
}

/* Clases específicas para diseño extra compacto */
.table-compact th, .table-compact td {
    padding: 3px 5px !important;
    vertical-align: middle !important;
    font-size: 10.5px !important;
}
.table-compact th {
    font-weight: 700 !important;
    font-size: 10px !important;
}
.font-size-11 {
    font-size: 11px !important;
}
.font-size-10 {
    font-size: 10px !important;
}
.font-size-9 {
    font-size: 9px !important;
}
.font-size-8 {
    font-size: 8px !important;
}
.btn-xs {
    padding: 3px 8px !important;
    font-size: 10.5px !important;
    border-radius: 4px !important;
}

/* Estilos para el Modal de Pago Personalizado (Glassmorphism Backdrop) */
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.55);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100000;
    backdrop-filter: blur(3px);
}
.custom-modal-content {
    background: #ffffff;
    border-radius: 8px;
    width: 92%;
    max-width: 520px;
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3) !important;
}
.animate-scale-in {
    animation: scaleIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes scaleIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* Efecto de fondo celeste sumamente tenue para las filas secundarias de pagos desplegadas */
.table-info-light td {
    background-color: rgba(52, 152, 219, 0.06) !important;
    border-color: rgba(52, 152, 219, 0.15) !important;
}
.btn-info {
    background-color: #38bdf8 !important;
    border-color: #0ea5e9 !important;
    color: #ffffff !important;
}
.btn-info:hover {
    background-color: #0ea5e9 !important;
    border-color: #0284c7 !important;
}


</style>
