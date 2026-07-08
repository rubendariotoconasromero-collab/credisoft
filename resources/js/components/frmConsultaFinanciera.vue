<template>
    <main class="financial-consultation">

        <div v-if="preloaderCaja || preloaderDiario" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <div class="card border-0">

                    <div class="card-header bg-success bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-white text-uppercase">
                            Consultas Financieras
                        </h5>
                    </div>

                    <div class="card-body pt-3">

                        <!-- TABS NAV -->
                        <ul class="nav nav-pills custom-tabs mb-0 d-flex justify-content-center" role="tablist">
                            <li class="nav-item mx-1">
                                <button @click="cambiarTab('FLUJO_CAJA')"
                                        :class="['nav-link px-4 fw-bold text-uppercase', tabActivo === 'FLUJO_CAJA' ? 'active' : '']"
                                        type="button">
                                    Flujo de Caja
                                </button>
                            </li>
                            <li class="nav-item mx-1">
                                <button @click="cambiarTab('LIBRO_DIARIO')"
                                        :class="['nav-link px-4 fw-bold text-uppercase', tabActivo === 'LIBRO_DIARIO' ? 'active' : '']"
                                        type="button">
                                    Libro Diario
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content bg-white p-3 border rounded-bottom shadow-sm">

                            <!-- ================================================ -->
                            <!-- TAB 1: FLUJO DE CAJA                              -->
                            <!-- Todos los movimientos con saldo bóveda corrido    -->
                            <!-- ================================================ -->
                            <div v-show="tabActivo === 'FLUJO_CAJA'">

                                <!-- Filtros -->
                                <div class="row mb-2 g-2 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Tipo de Libro</label>
                                        <select v-model="filtrosCaja.tipo_libro" class="form-select form-select-sm shadow-sm border-0" @change="buscarCaja()">
                                            <option value="GENERAL">Libro General</option>
                                            <option value="OPERATIVO">Libro Operativo (Caja)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Rango de Fechas</label>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 9px; padding: 0.25rem 0.5rem;">DESDE</span>
                                            <input type="date" @change="buscarCaja()" v-model="filtrosCaja.fecha_inicio" class="form-control form-control-sm border-start-0" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                                            <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 9px; padding: 0.25rem 0.5rem;">HASTA</span>
                                            <input type="date" @change="buscarCaja()" v-model="filtrosCaja.fecha_final" class="form-control form-control-sm border-start-0" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Tipo de Movimiento</label>
                                        <select v-model="filtrosCaja.tipo" class="form-select form-select-sm shadow-sm border-0" @change="buscarCaja()">
                                            <option value="TODOS">Todos</option>
                                            <option value="CAPITAL"       v-if="filtrosCaja.tipo_libro === 'GENERAL'">Pago de Capital</option>
                                            <option value="INTERES">Pago de Interés</option>
                                            <option value="MORA">Multas / Mora</option>
                                            <option value="DESEMBOLSO"    v-if="filtrosCaja.tipo_libro === 'GENERAL'">Desembolsos</option>
                                            <option value="GASTOSADM">Gastos Adm.</option>
                                            <option value="INGRESO_CAJA">Ingresos Extra</option>
                                            <option value="EGRESO_CAJA">Egresos Extra</option>
                                            <option value="BOVEDA_INGRESO" v-if="filtrosCaja.tipo_libro === 'GENERAL'">Ingreso Bóveda</option>
                                            <option value="BOVEDA_EGRESO"  v-if="filtrosCaja.tipo_libro === 'GENERAL'">Egreso Bóveda</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Buscar (Descripción)</label>
                                        <input v-model="filtrosCaja.buscar" @keyup.enter="buscarCaja()" type="text"
                                               class="form-control form-control-sm shadow-sm border-0" placeholder="Ej: CREDITO: 5034" />
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-success btn-sm w-100 shadow-sm fw-bold" @click="buscarCaja()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-2 d-flex gap-1 justify-content-end">
                                        <button @click="exportarCaja()" class="btn btn-success btn-sm shadow-sm fw-bold w-50" title="Exportar Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </button>
                                        <button @click="imprimirCaja()" class="btn btn-warning btn-sm shadow-sm fw-bold w-50" title="Imprimir PDF">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla Flujo de Caja -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="header-flujo text-center align-middle">
                                            <tr>
                                                <th width="4%">Nro</th>
                                                <th width="9%">Fecha</th>
                                                <th width="12%" class="text-start ps-2">Tipo</th>
                                                <th class="text-start ps-2">Descripción</th>
                                                <th width="11%">Debe (Bs)</th>
                                                <th width="11%">Haber (Bs)</th>
                                                <th width="13%">Capital Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Saldo de apertura del periodo (solo en la primera página) -->
                                            <tr v-if="movimientosCaja.length > 0 && paginacionCaja.current_page === 1" class="fila-saldo-anterior">
                                                <td></td>
                                                <td class="text-center fw-semibold text-muted">{{ filtrosCaja.fecha_inicio }}</td>
                                                <td colspan="3" class="ps-2 fst-italic text-muted text-uppercase" style="font-size:0.72rem;">
                                                    <i class="fas fa-flag-checkered me-1"></i> Saldo Anterior (apertura del periodo)
                                                </td>
                                                <td class="text-end col-saldo fw-bold">{{ formatNumero(saldoAnteriorCaja) }}</td>
                                            </tr>
                                            <tr v-for="item in movimientosCaja" :key="item.nro"
                                                :class="{ 'fila-transfer': item.tipo === 'TRANSFER_INTERNO' }">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center fw-semibold text-dark">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-secondary ps-2" style="font-size:0.7rem;">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase fw-semibold" style="font-size:0.75rem;">
                                                    {{ item.descripcion }}
                                                    <span v-if="item.tipo === 'TRANSFER_INTERNO'" class="badge bg-secondary ms-1" style="font-size:0.6rem;" title="Movimiento interno bóveda↔caja: no altera el capital total">
                                                        <i class="fas fa-exchange-alt"></i> Neto cero
                                                    </span>
                                                    <button v-if="item.id_plan_pago"
                                                            class="btn btn-link btn-sm p-0 ms-1 align-baseline text-primary"
                                                            style="font-size:0.75rem;"
                                                            title="Ver información del crédito asociado"
                                                            @click="verCredito(item.id_plan_pago)">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                </td>
                                                <td class="text-end col-debe">
                                                    {{ item.debe > 0 ? formatNumero(item.debe) : '' }}
                                                </td>
                                                <td class="text-end col-haber">
                                                    {{ item.haber > 0 ? formatNumero(item.haber) : '' }}
                                                </td>
                                                <td class="text-end col-saldo">
                                                    {{ formatNumero(item.saldo) }}
                                                </td>
                                            </tr>
                                            <tr v-if="movimientosCaja.length === 0">
                                                <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x d-block mb-2 opacity-25"></i>
                                                    No hay movimientos en el período seleccionado.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="fw-bold text-dark">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 bg-light">TOTAL DEL PERIODO:</td>
                                                <td class="text-end col-debe-total">{{ formatNumero(totalIngresosCaja) }}</td>
                                                <td class="text-end col-haber-total">{{ formatNumero(totalEgresosCaja) }}</td>
                                                <td class="text-end col-saldo-total">{{ formatNumero(saldoFinalCaja) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-end pe-3 text-muted" style="font-size:0.7rem;">
                                                    Saldo Anterior {{ formatNumero(saldoAnteriorCaja) }} + Debe − Haber = Saldo Final &nbsp;·&nbsp; (totales excl. transferencias internas)
                                                </td>
                                                <td class="text-end col-saldo-total" style="font-size:0.7rem;">SALDO FINAL</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Capital Total (último asiento) -->
                                <div class="d-flex justify-content-end mt-2">
                                    <div class="card border-0 shadow rounded-3 bg-dark text-white" style="min-width:340px;">
                                        <div class="card-body py-2 px-4 d-flex justify-content-between align-items-center gap-4">
                                            <div>
                                                <div class="fw-bold text-uppercase text-white-50" style="font-size:0.7rem; letter-spacing:1px;">
                                                    <i class="fas fa-coins me-1"></i> Capital Total (Bóveda + Caja)
                                                </div>
                                                <div class="text-white-50" style="font-size:0.65rem;">
                                                    al {{ filtrosCaja.fecha_final }}
                                                </div>
                                            </div>
                                            <div class="fs-4 fw-bold" :class="saldoBoveda >= 0 ? 'text-success' : 'text-danger'">
                                                {{ formatNumero(saldoBoveda) }}
                                                <small class="fs-6 text-white-50">Bs</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paginación Flujo de Caja -->
                                <div class="d-flex justify-content-between align-items-center mt-2" v-if="paginacionCaja.last_page > 1">
                                     <span class="text-muted small" style="font-size: 0.75rem;">
                                         Página {{ paginacionCaja.current_page }} de {{ paginacionCaja.last_page }}
                                         ({{ paginacionCaja.total }} registros)
                                     </span>
                                     <nav>
                                         <ul class="pagination pagination-sm shadow-sm mb-0">
                                             <li class="page-item" :class="{disabled: paginacionCaja.current_page <= 1}">
                                                 <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(paginacionCaja.current_page - 1)">Ant</a>
                                             </li>
                                             <li class="page-item" v-for="page in pagesNumberCaja" :key="page"
                                                 :class="{active: page == paginacionCaja.current_page}">
                                                 <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(page)">{{ page }}</a>
                                             </li>
                                             <li class="page-item" :class="{disabled: paginacionCaja.current_page >= paginacionCaja.last_page}">
                                                 <a class="page-link" href="#" @click.prevent="cambiarPaginaCaja(paginacionCaja.current_page + 1)">Sig</a>
                                             </li>
                                         </ul>
                                     </nav>
                                 </div>
                            </div>

                            <!-- ================================================ -->
                            <!-- TAB 2: LIBRO DIARIO                               -->
                            <!-- Solo ingresos recaudados                          -->
                            <!-- ================================================ -->
                            <div v-show="tabActivo === 'LIBRO_DIARIO'">

                                <!-- Filtros Libro Diario -->
                                <div class="row mb-2 g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Rango de Fechas</label>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <span class="input-group-text bg-white text-muted fw-bold" style="font-size: 9px; padding: 0.25rem 0.5rem;">DESDE</span>
                                            <input type="date" @change="buscarDiario()" v-model="filtrosDiario.fecha_inicio" class="form-control form-control-sm border-start-0" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                                            <span class="input-group-text bg-white text-muted fw-bold border-start-0" style="font-size: 9px; padding: 0.25rem 0.5rem;">HASTA</span>
                                            <input type="date" @change="buscarDiario()" v-model="filtrosDiario.fecha_final" class="form-control form-control-sm border-start-0" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Tipo de Ingreso</label>
                                        <select v-model="filtrosDiario.tipo" class="form-select form-select-sm shadow-sm border-0" @change="buscarDiario()">
                                            <option value="TODOS">Todos los ingresos</option>
                                            <option value="INTERES">Pago de Interés</option>
                                            <option value="MORA">Multas / Mora</option>
                                            <option value="GASTOSADM">Gastos Administrativos</option>
                                            <option value="INGRESO_CAJA">Otros Ingresos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Buscar (Descripción)</label>
                                        <input v-model="filtrosDiario.buscar" @keyup.enter="buscarDiario()" type="text"
                                               class="form-control form-control-sm shadow-sm border-0" placeholder="Descripción..." />
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-success btn-sm w-100 shadow-sm fw-bold" @click="buscarDiario()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-2 d-flex gap-1 justify-content-end">
                                        <button @click="exportarDiario()" class="btn btn-success btn-sm shadow-sm fw-bold w-50" title="Exportar Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </button>
                                        <button @click="imprimirDiario()" class="btn btn-warning btn-sm shadow-sm fw-bold w-50" title="Imprimir PDF">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla Libro Diario -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="header-diario text-center align-middle" style="font-size: 0.75rem;">
                                            <tr>
                                                <th width="4%">Nro</th>
                                                <th width="9%">Fecha</th>
                                                <th width="14%" class="text-start ps-2">Tipo de Ingreso</th>
                                                <th class="text-start ps-2">Detalle / Descripción</th>
                                                <th width="14%">Ingreso (Bs)</th>
                                                <th width="16%">Acumulado Ingresos (Bs)</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size: 0.75rem;">
                                            <tr v-for="item in ingresosDiarioPaginados" :key="'ing_' + item.nro">
                                                <td class="text-center text-muted">{{ item.nro }}</td>
                                                <td class="text-center fw-semibold text-dark">{{ item.fecha }}</td>
                                                <td class="text-start fw-bold text-secondary ps-2">{{ item.tipo }}</td>
                                                <td class="ps-2 text-dark text-uppercase fw-semibold">
                                                    {{ item.descripcion }}
                                                    <button v-if="item.id_plan_pago"
                                                            class="btn btn-link btn-sm p-0 ms-1 align-baseline text-primary"
                                                            style="font-size:0.75rem;"
                                                            title="Ver información del crédito asociado"
                                                            @click="verCredito(item.id_plan_pago)">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                </td>
                                                <td class="text-end col-debe">
                                                    {{ item.debe > 0 ? formatNumero(item.debe) : '' }}
                                                </td>
                                                <td class="text-end col-saldo">
                                                    {{ formatNumero(item.totalAcumulado) }}
                                                </td>
                                            </tr>
                                            <tr v-if="ingresosDiario.length === 0">
                                                <td colspan="6" class="text-center py-5 text-muted fst-italic">
                                                    <i class="fas fa-folder-open fa-2x d-block mb-2 opacity-25"></i>
                                                    No hay ingresos en el período seleccionado.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="fw-bold text-dark" style="font-size: 0.75rem;">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 bg-light">TOTAL INGRESOS DEL PERIODO:</td>
                                                <td class="text-end col-debe-total">{{ formatNumero(totalIngresosDiario) }}</td>
                                                <td class="text-end col-saldo-total">{{ formatNumero(totalIngresosDiario) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Capital Total (Libro Diario) -->
                                <div class="d-flex justify-content-end mt-2">
                                    <div class="card border-0 shadow rounded-3 bg-dark text-white" style="min-width:340px;">
                                        <div class="card-body py-2 px-4 d-flex justify-content-between align-items-center gap-4">
                                            <div>
                                                <div class="fw-bold text-uppercase text-white-50" style="font-size:0.7rem; letter-spacing:1px;">
                                                    <i class="fas fa-coins me-1"></i> Capital Total (Bóveda + Caja)
                                                </div>
                                                <div class="text-white-50" style="font-size:0.65rem;">
                                                    al {{ filtrosDiario.fecha_final }}
                                                </div>
                                            </div>
                                            <div class="fs-4 fw-bold" :class="saldoTotalDiario >= 0 ? 'text-success' : 'text-danger'">
                                                {{ formatNumero(saldoTotalDiario) }}
                                                <small class="fs-6 text-white-50">Bs</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paginación Libro Diario (client-side) -->
                                <div class="d-flex justify-content-between align-items-center mt-2" v-if="paginacionDiario.last_page > 1">
                                     <span class="text-muted small" style="font-size: 0.75rem;">
                                         Página {{ paginacionDiario.current_page }} de {{ paginacionDiario.last_page }}
                                         ({{ paginacionDiario.total }} registros)
                                     </span>
                                     <nav>
                                         <ul class="pagination pagination-sm shadow-sm mb-0">
                                             <li class="page-item" :class="{disabled: paginacionDiario.current_page <= 1}">
                                                 <a class="page-link" href="#" @click.prevent="cambiarPaginaDiario(paginacionDiario.current_page - 1)">Ant</a>
                                             </li>
                                             <li class="page-item" v-for="page in pagesNumberDiario" :key="page"
                                                 :class="{active: page == paginacionDiario.current_page}">
                                                 <a class="page-link" href="#" @click.prevent="cambiarPaginaDiario(page)">{{ page }}</a>
                                             </li>
                                             <li class="page-item" :class="{disabled: paginacionDiario.current_page >= paginacionDiario.last_page}">
                                                 <a class="page-link" href="#" @click.prevent="cambiarPaginaDiario(paginacionDiario.current_page + 1)">Sig</a>
                                             </li>
                                         </ul>
                                     </nav>
                                 </div>
                            </div>

                        </div><!-- /tab-content -->
                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div>
        </div>

        <!-- ================================================ -->
        <!-- MODAL: INFORMACIÓN DEL CRÉDITO ASOCIADO           -->
        <!-- ================================================ -->
        <div class="modal fade" id="modalCreditoConsulta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success bg-gradient text-white py-2">
                        <h6 class="modal-title fw-bold text-uppercase mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Información del Crédito
                            <span v-if="creditoInfo"> #{{ creditoInfo.credito_id }}</span>
                        </h6>
                        <button type="button" class="btn-close btn-close-white" @click="cerrarModalCredito"></button>
                    </div>

                    <div class="modal-body p-3">
                        <div v-if="cargandoCredito" class="text-center py-5">
                            <div class="spinner-border text-success" role="status"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando información del crédito...</p>
                        </div>

                        <div v-else-if="creditoInfo">
                            <!-- Datos del cliente -->
                            <div class="card border-0 bg-light mb-2">
                                <div class="card-body py-2 px-3">
                                    <h6 class="text-success fw-bold text-uppercase mb-2" style="font-size:0.75rem;">
                                        <i class="fas fa-user me-1"></i> Cliente
                                    </h6>
                                    <div class="row g-1" style="font-size:0.8rem;">
                                        <div class="col-md-6"><strong>Nombre:</strong> <span class="text-uppercase">{{ creditoInfo.cliente }}</span></div>
                                        <div class="col-md-3"><strong>C.I.:</strong> {{ creditoInfo.ci }} {{ creditoInfo.lugar_expedicion }}</div>
                                        <div class="col-md-3"><strong>Asesor:</strong> <span class="text-uppercase">{{ creditoInfo.asesor }}</span></div>
                                        <div class="col-md-6"><strong>Dirección:</strong> {{ creditoInfo.direccion || '—' }}</div>
                                        <div class="col-md-6"><strong>Teléfono(s):</strong> {{ creditoInfo.telefonos && creditoInfo.telefonos.length ? creditoInfo.telefonos.join(', ') : '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del crédito -->
                            <div class="card border-0 bg-light mb-2">
                                <div class="card-body py-2 px-3">
                                    <h6 class="text-success fw-bold text-uppercase mb-2" style="font-size:0.75rem;">
                                        <i class="fas fa-coins me-1"></i> Crédito
                                    </h6>
                                    <div class="row g-1" style="font-size:0.8rem;">
                                        <div class="col-md-4"><strong>Cód. Crédito:</strong> #{{ creditoInfo.credito_id }}</div>
                                        <div class="col-md-4"><strong>Cód. Plan:</strong> {{ creditoInfo.plan_pago_id }}</div>
                                        <div class="col-md-4"><strong>Estado:</strong> <span :class="estadoPlanClase(creditoInfo.estado_plan)">{{ estadoPlanTexto(creditoInfo.estado_plan) }}</span></div>
                                        <div class="col-md-4"><strong>Monto:</strong> {{ formatNumero(creditoInfo.importe_solicitud) }} {{ creditoInfo.moneda }}</div>
                                        <div class="col-md-4"><strong>Total a pagar:</strong> {{ formatNumero(creditoInfo.total_pagar) }} {{ creditoInfo.moneda }}</div>
                                        <div class="col-md-4"><strong>Saldo pendiente:</strong> <span class="fw-bold text-danger">{{ formatNumero(creditoInfo.saldo_pendiente) }} {{ creditoInfo.moneda }}</span></div>
                                        <div class="col-md-4"><strong>Plazo:</strong> {{ creditoInfo.nro_cuotas }} ({{ creditoInfo.lapso_capital }})</div>
                                        <div class="col-md-4"><strong>Tasa:</strong> {{ creditoInfo.tasa }}%</div>
                                        <div class="col-md-4"><strong>Garantía:</strong> {{ creditoInfo.tipo_garantia || '—' }}</div>
                                        <div class="col-md-4"><strong>Desembolso:</strong> {{ formatFecha(creditoInfo.fecha_desembolso) }}</div>
                                        <div class="col-md-4"><strong>Inicio plan:</strong> {{ formatFecha(creditoInfo.fecha_inicio) }}</div>
                                        <div class="col-md-4"><strong>Fin plan:</strong> {{ formatFecha(creditoInfo.fecha_fin) }}</div>
                                        <div class="col-md-12"><strong>Destino:</strong> {{ creditoInfo.destino_prestamo || '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de cuotas -->
                            <div class="row g-2 text-center">
                                <div class="col">
                                    <div class="border rounded py-2 bg-white">
                                        <div class="fw-bold fs-5 text-dark">{{ creditoInfo.total_cuotas }}</div>
                                        <div class="text-muted text-uppercase" style="font-size:0.65rem;">Cuotas Totales</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border rounded py-2 bg-white">
                                        <div class="fw-bold fs-5 text-success">{{ creditoInfo.cuotas_pagadas }}</div>
                                        <div class="text-muted text-uppercase" style="font-size:0.65rem;">Pagadas</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border rounded py-2 bg-white">
                                        <div class="fw-bold fs-5 text-warning">{{ creditoInfo.cuotas_pendientes }}</div>
                                        <div class="text-muted text-uppercase" style="font-size:0.65rem;">Pendientes</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border rounded py-2 bg-white">
                                        <div class="fw-bold fs-5 text-danger">{{ creditoInfo.cuotas_vencidas }}</div>
                                        <div class="text-muted text-uppercase" style="font-size:0.65rem;">Vencidas</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-5 text-muted">
                            <i class="fas fa-exclamation-circle fa-2x mb-2 d-block opacity-50"></i>
                            No se pudo cargar la información del crédito.
                        </div>
                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalCredito">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            tabActivo: 'FLUJO_CAJA',
            preloaderCaja: false,
            preloaderDiario: false,

            // ── Flujo de Caja ──────────────────────────────────────────
            filtrosCaja: {
                fecha_inicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
                fecha_final:  moment().format('YYYY-MM-DD'),
                tipo_libro:   'GENERAL',
                tipo:         'TODOS',
                buscar:       '',
            },
            movimientosCaja:   [],
            totalIngresosCaja: 0,
            totalEgresosCaja:  0,
            saldoBoveda:       0,   // capital total real (bóveda+caja) al corte — tarjeta
            saldoAnteriorCaja: 0,   // saldo de apertura del periodo
            saldoFinalCaja:    0,   // saldo final del periodo (según filtros)
            paginacionCaja: { current_page: 1, last_page: 1, total: 0 },

            // ── Libro Diario ───────────────────────────────────────────
            filtrosDiario: {
                fecha_inicio: moment().startOf('month').format('YYYY-MM-DD'),
                fecha_final:  moment().format('YYYY-MM-DD'),
                tipo:         'TODOS',
                buscar:       '',
            },
            ingresosDiario:      [],
            totalIngresosDiario: 0,
            saldoTotalDiario:    0,
            paginaActualDiario:  1,
            libroDiarioCargado:  false,

            // ── Modal Crédito asociado ─────────────────────────────────
            creditoInfo:     null,
            cargandoCredito: false,
            modalCredito:    null,

            offset: 2,
        };
    },

    computed: {
        // Flujo de Caja — páginas para el paginador
        pagesNumberCaja() {
            return this.buildPages(this.paginacionCaja.current_page, this.paginacionCaja.last_page);
        },

        // Libro Diario — paginación client-side
        paginacionDiario() {
            const perPage = 20;
            const total   = this.ingresosDiario.length;
            return {
                current_page: this.paginaActualDiario,
                last_page:    Math.max(1, Math.ceil(total / perPage)),
                total,
            };
        },
        ingresosDiarioPaginados() {
            const perPage = 20;
            const start   = (this.paginaActualDiario - 1) * perPage;
            return this.ingresosDiario.slice(start, start + perPage);
        },
        pagesNumberDiario() {
            return this.buildPages(this.paginaActualDiario, this.paginacionDiario.last_page);
        },
    },

    mounted() {
        this.fetchFlujoCaja(1);
    },

    methods: {
        // ── Helpers ───────────────────────────────────────────────────
        buildPages(current, lastPage) {
            let from = Math.max(1, current - this.offset);
            let to   = Math.min(lastPage, from + this.offset * 2);
            const pages = [];
            for (let i = from; i <= to; i++) pages.push(i);
            return pages;
        },

        formatNumero(numero) {
            if (numero == null) return '0.00';
            return new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(numero);
        },

        async generarPDF(endpoint) {
            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Procesando el PDF, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });
            try {
                const response = await axios.get(endpoint, { responseType: 'blob' });
                const url = window.URL.createObjectURL(
                    new Blob([response.data], { type: 'application/pdf' })
                );
                Swal.close();
                window.open(url, '_blank');
                setTimeout(() => window.URL.revokeObjectURL(url), 10000);
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo generar el reporte.', 'error');
            }
        },

        // ── Tabs ──────────────────────────────────────────────────────
        cambiarTab(tab) {
            this.tabActivo = tab;
            if (tab === 'LIBRO_DIARIO' && !this.libroDiarioCargado) {
                this.fetchLibroDiario();
            }
        },

        // ── Flujo de Caja ─────────────────────────────────────────────
        buscarCaja() {
            const soloGeneral = ['CAPITAL', 'DESEMBOLSO', 'BOVEDA_INGRESO', 'BOVEDA_EGRESO'];
            if (this.filtrosCaja.tipo_libro === 'OPERATIVO' && soloGeneral.includes(this.filtrosCaja.tipo)) {
                this.filtrosCaja.tipo = 'TODOS';
            }
            this.fetchFlujoCaja(1);
        },

        cambiarPaginaCaja(page) {
            if (page >= 1 && page <= this.paginacionCaja.last_page) {
                this.fetchFlujoCaja(page);
            }
        },

        async fetchFlujoCaja(page = 1) {
            this.preloaderCaja = true;
            try {
                const response = await axios.get('/libro-mayor', {
                    params: { ...this.filtrosCaja, page },
                });
                this.movimientosCaja   = response.data.movimientos.data;
                this.paginacionCaja    = response.data.movimientos;
                this.totalIngresosCaja = response.data.totales.ingresos;
                this.totalEgresosCaja  = response.data.totales.egresos;
                this.saldoBoveda       = response.data.capital_total_real ?? 0;
                this.saldoAnteriorCaja = response.data.saldo_anterior ?? 0;
                this.saldoFinalCaja    = response.data.saldo_boveda ?? 0;
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los movimientos.', 'error');
            } finally {
                this.preloaderCaja = false;
            }
        },

        exportarCaja() {
            const qs = new URLSearchParams({ ...this.filtrosCaja }).toString();
            window.open(`/reportes/libro-mayor/excel?${qs}`, '_blank');
        },

        async imprimirCaja() {
            const qs = new URLSearchParams({ ...this.filtrosCaja }).toString();
            await this.generarPDF(`/reportes/libro-mayor?${qs}`);
        },

        // ── Libro Diario ──────────────────────────────────────────────
        buscarDiario() {
            this.paginaActualDiario = 1;
            this.fetchLibroDiario();
        },

        cambiarPaginaDiario(page) {
            if (page >= 1 && page <= this.paginacionDiario.last_page) {
                this.paginaActualDiario = page;
            }
        },

        async fetchLibroDiario() {
            this.preloaderDiario = true;
            try {
                const response = await axios.get('/libro-mayor', {
                    params: {
                        ...this.filtrosDiario,
                        tipo_libro: 'GENERAL',
                        page: 1,
                    },
                });
                const tiposIngresoReal = ['INTERES', 'MORA', 'GASTOSADM', 'INGRESO_CAJA'];
                let acumulado = 0;
                this.ingresosDiario = (response.data.ingresos_lista || [])
                    .filter(item => tiposIngresoReal.includes(item.tipo))
                    .map((item, idx) => {
                        acumulado += (item.debe || 0);
                        return { ...item, nro: idx + 1, totalAcumulado: acumulado };
                    });
                this.totalIngresosDiario = acumulado;
                this.saldoTotalDiario    = response.data.capital_total_real ?? 0;
                this.libroDiarioCargado  = true;
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los ingresos.', 'error');
            } finally {
                this.preloaderDiario = false;
            }
        },

        exportarDiario() {
            const qs = new URLSearchParams({ ...this.filtrosDiario, tipo_libro: 'GENERAL' }).toString();
            window.open(`/reportes/ingresos/excel?${qs}`, '_blank');
        },

        async imprimirDiario() {
            const qs = new URLSearchParams({ ...this.filtrosDiario, tipo_libro: 'GENERAL' }).toString();
            await this.generarPDF(`/reportes/ingresos?${qs}`);
        },

        // ── Crédito asociado ──────────────────────────────────────────
        async verCredito(idPlanPago) {
            if (!idPlanPago) return;
            this.creditoInfo = null;
            this.cargandoCredito = true;

            // Abrir modal (instancia Bootstrap reutilizable)
            if (!this.modalCredito) {
                this.modalCredito = new bootstrap.Modal(document.getElementById('modalCreditoConsulta'));
            }
            this.modalCredito.show();

            try {
                const response = await axios.get('/consulta-financiera/info-credito', {
                    params: { id_plan_pago: idPlanPago },
                });
                this.creditoInfo = response.data;
            } catch (error) {
                console.error(error);
                this.creditoInfo = null;
                Swal.fire('Error', error.response?.data?.error || 'No se pudo cargar la información del crédito.', 'error');
            } finally {
                this.cargandoCredito = false;
            }
        },

        cerrarModalCredito() {
            if (this.modalCredito) this.modalCredito.hide();
        },

        formatFecha(fecha) {
            if (!fecha) return '—';
            return moment(fecha).format('DD/MM/YYYY');
        },

        estadoPlanTexto(estado) {
            const mapa = { 0: 'Anulado', 1: 'Vigente', 2: 'Finalizado' };
            return mapa[estado] ?? 'Desconocido';
        },

        estadoPlanClase(estado) {
            const mapa = {
                0: 'badge bg-dark',
                1: 'badge bg-success',
                2: 'badge bg-secondary',
            };
            return mapa[estado] ?? 'badge bg-light text-dark';
        },
    },
};
</script>

<style scoped>
/* Tabs */
.custom-tabs {
    border-bottom: 2px solid #dee2e6;
}
.custom-tabs .nav-link {
    color: #6c757d !important;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    font-size: 0.85rem;
    margin-bottom: -2px;
    transition: all 0.05s ease;
}
.custom-tabs .nav-link:hover {
    background-color: #198754 !important;
    color: #fff !important;
}
.custom-tabs .nav-link.active {
    background-color: #fff;
    color: #198754 !important;
    border-color: #dee2e6;
    border-top: 3px solid #198754;
    border-bottom: 3px solid #fff;
    z-index: 2;
    position: relative;
}
.custom-tabs .nav-link.active:hover { color: #fff !important; }

/* Tabla */
.ledger-table { font-size: 0.75rem; }
.ledger-table th {
    vertical-align: middle;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
    padding: 6px 5px;
}
.ledger-table td { vertical-align: middle; padding: 4px 5px; }

/* Color updates for high contrast and clarity */
.ledger-table thead.header-flujo th,
.ledger-table thead.header-diario th {
    background-color: #198754 !important; /* Premium Success Green */
    color: #ffffff !important;
    border-bottom: 2px solid #157347 !important;
}

.ledger-table th, .ledger-table td {
    border: 1px solid #b2c1d3 !important; /* Higher contrast borders (Slate-300 level) for strong structure */
}

/* Custom column contrast styling */
.col-debe {
    background-color: #e8f5e9 !important; /* Very light high-quality green background */
    color: #1b5e20 !important; /* Dark forest green text */
    font-weight: 600;
}
.col-haber {
    background-color: #ffebee !important; /* Very light high-quality red background */
    color: #c62828 !important; /* Dark crimson red text */
    font-weight: 600;
}
.col-saldo {
    background-color: #fffde7 !important; /* Very light high-quality gold/yellow background */
    color: #263238 !important; /* Dark slate grey text */
    font-weight: 700;
}

/* Footer Totals styling */
.col-debe-total {
    background-color: #c8e6c9 !important; /* Higher contrast green for total */
    color: #1b5e20 !important;
}
.col-haber-total {
    background-color: #ffcdd2 !important; /* Higher contrast red for total */
    color: #c62828 !important;
}
.col-saldo-total {
    background-color: #fff9c4 !important; /* Higher contrast yellow for total */
    color: #263238 !important;
}

/* Fila de saldo anterior (apertura del periodo) */
.fila-saldo-anterior td {
    background-color: #fff8e1 !important;
    border-top: 2px solid #ffca28 !important;
    border-bottom: 2px solid #ffca28 !important;
}
/* Filas de transferencia interna (neto cero) — atenuadas para distinguirlas */
.fila-transfer td {
    background-color: #f1f3f5 !important;
    color: #6c757d !important;
    font-style: italic;
}

/* Preloader */
.preloader {
    position: fixed;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.8);
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
    to { transform: rotate(360deg); }
}
</style>
