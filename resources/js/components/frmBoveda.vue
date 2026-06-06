<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">

                <div class="card border-0 shadow-sm">

                    <!-- ── HEADER ──────────────────────────────────────────── -->
                    <div class="card-header bg-warning bg-gradient py-2 d-flex align-items-center justify-content-between">
                        <h5 class="my-0 fw-bold text-dark text-uppercase mb-0" style="font-size:0.95rem;">
                            <i class="fas fa-vault me-2"></i>Gestión de Bóveda
                        </h5>
                        <span v-if="boveda.id_boveda !== 0" class="badge bg-dark bg-opacity-25 text-dark fw-normal" style="font-size:0.7rem;">
                            <i class="fas fa-circle text-success me-1" style="font-size:0.55rem;"></i>Activa
                        </span>
                    </div>

                    <div class="card-body p-3">

                        <!-- ── PANEL SUPERIOR: saldo + acciones ───────────── -->
                        <div class="row g-2 mb-3">

                            <!-- Saldo -->
                            <div class="col-md-7">
                                <!-- Bóveda cerrada -->
                                <div v-if="boveda.id_boveda === 0"
                                     class="h-100 border border-secondary border-opacity-25 bg-light d-flex align-items-center gap-3 px-3 py-2">
                                    <i class="fas fa-lock fa-2x text-secondary opacity-50"></i>
                                    <div>
                                        <div class="fw-bold text-secondary text-uppercase mb-0" style="font-size:0.8rem;">Bóveda no aperturada</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Realice la apertura para registrar movimientos financieros.</div>
                                    </div>
                                </div>

                                <!-- Bóveda activa -->
                                <div v-else
                                     class="h-100 border-start border-4 border-success bg-success bg-opacity-10 d-flex align-items-center gap-3 px-3 py-2">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                                         style="width:44px;height:44px;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-success fw-bold text-uppercase mb-0" style="font-size:0.65rem;letter-spacing:0.5px;">Saldo Actual en Bóveda</div>
                                        <div class="fw-bold text-dark lh-1" style="font-size:1.6rem;">
                                            {{ formatNumero(boveda.saldo_actual) }}
                                            <span class="fw-normal text-muted" style="font-size:0.9rem;">Bs.</span>
                                        </div>
                                    </div>
                                    <div class="text-muted border-start ps-3" style="font-size:0.7rem;line-height:1.6;">
                                        <div><i class="fas fa-calendar-day me-1 text-success"></i>{{ boveda.fecha_apertura }}</div>
                                        <div><i class="fas fa-user me-1 text-success"></i>{{ boveda.usuario_apertura }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="col-md-5 d-flex align-items-stretch gap-2">
                                <button v-if="boveda.id_boveda === 0"
                                        class="btn btn-success w-100 fw-bold shadow-sm"
                                        @click="aperturarBoveda()">
                                    <i class="fas fa-key me-2"></i>Aperturar Bóveda
                                </button>

                                <template v-else>
                                    <button class="btn btn-success flex-fill fw-bold shadow-sm"
                                            @click="abrirIngreso()">
                                        <i class="fas fa-plus-circle me-1"></i>
                                        <span style="font-size:0.85rem;">Añadir Ingreso</span>
                                    </button>
                                    <button class="btn btn-danger flex-fill fw-bold shadow-sm"
                                            @click="abrirRetiro()">
                                        <i class="fas fa-minus-circle me-1"></i>
                                        <span style="font-size:0.85rem;">Registrar Retiro</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- ── FILTROS ──────────────────────────────────────── -->
                        <div class="row g-2 align-items-end mb-2">
                            <div class="col-md-3">
                                <label class="form-label fw-bold mb-1 text-muted text-uppercase" style="font-size:0.65rem;">Tipo de Movimiento</label>
                                <select v-model="filtroTipoMovimiento" @change="buscarMovimientoBoveda()"
                                        class="form-select form-select-sm border shadow-sm" style="border-radius:0;">
                                    <option value="todos">Todos los movimientos</option>
                                    <option value="ingreso">Solo Ingresos</option>
                                    <option value="salida">Solo Salidas / Egresos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold mb-1 text-muted text-uppercase" style="font-size:0.65rem;">Desde</label>
                                <input type="date" v-model="fechaInicio" @input="buscarMovimientoBoveda()"
                                       class="form-control form-control-sm border shadow-sm" style="border-radius:0;">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold mb-1 text-muted text-uppercase" style="font-size:0.65rem;">Hasta</label>
                                <input type="date" v-model="fechaFin" @input="buscarMovimientoBoveda()"
                                       class="form-control form-control-sm border shadow-sm" style="border-radius:0;">
                            </div>

                            <!-- Totales del período -->
                            <div class="col-md-5">
                                <div class="d-flex gap-2 h-100 align-items-end">
                                    <div class="flex-fill border bg-success bg-opacity-10 px-3 py-1 text-center" style="border-radius:0;">
                                        <div class="text-success fw-bold text-uppercase mb-0" style="font-size:0.6rem;">Total Ingresos</div>
                                        <div class="fw-bold text-dark" style="font-size:0.9rem;">{{ formatNumero(totalIngresosBoveda) }} <small class="text-muted fw-normal">Bs.</small></div>
                                    </div>
                                    <div class="flex-fill border bg-danger bg-opacity-10 px-3 py-1 text-center" style="border-radius:0;">
                                        <div class="text-danger fw-bold text-uppercase mb-0" style="font-size:0.6rem;">Total Salidas</div>
                                        <div class="fw-bold text-dark" style="font-size:0.9rem;">{{ formatNumero(totalSalidasBoveda) }} <small class="text-muted fw-normal">Bs.</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── TABLA ───────────────────────────────────────── -->
                        <div class="table-responsive border shadow-sm" style="border-radius:0;">
                            <table class="table table-sm table-hover align-middle mb-0 table-striped bov-table">
                                <thead class="table-warning text-dark text-uppercase">
                                    <tr>
                                        <th class="text-center ps-3" style="width:46px;">#</th>
                                        <th style="width:130px;">Tipo</th>
                                        <th class="text-end pe-3" style="width:130px;">Monto (Bs)</th>
                                        <th>Descripción / Concepto</th>
                                        <th style="width:140px;">Asesor</th>
                                        <th class="text-center" style="width:120px;">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loading -->
                                    <tr v-if="preloader">
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <span class="spinner-border spinner-border-sm text-warning me-2"></span>
                                            Cargando movimientos...
                                        </td>
                                    </tr>

                                    <!-- Rows -->
                                    <tr v-else v-for="(mov, idx) in movimientosBoveda" :key="idx">
                                        <td class="text-center ps-3 text-muted fw-semibold">{{ idx + 1 }}</td>

                                        <td>
                                            <!-- Transferencia interna (neutra) -->
                                            <span v-if="esTransferenciaInterna(mov)"
                                                  class="badge rounded-pill fw-semibold px-2 py-1"
                                                  style="font-size:0.65rem;background:#e5e7eb;color:#6b7280;">
                                                <i class="fas fa-exchange-alt me-1"></i>Transf. Interna
                                            </span>
                                            <!-- Ingreso -->
                                            <span v-else-if="mov.tipo_movimiento === 'ingreso'"
                                                  class="badge rounded-pill fw-semibold px-2 py-1"
                                                  style="font-size:0.65rem;background:#dcfce7;color:#16a34a;">
                                                <i class="fas fa-arrow-up me-1"></i>Ingreso
                                            </span>
                                            <!-- Salida -->
                                            <span v-else
                                                  class="badge rounded-pill fw-semibold px-2 py-1"
                                                  style="font-size:0.65rem;background:#fee2e2;color:#dc2626;">
                                                <i class="fas fa-arrow-down me-1"></i>Egreso
                                            </span>
                                        </td>

                                        <td class="text-end pe-3 fw-bold font-monospace text-dark">
                                            {{ formatNumero(mov.monto) }}
                                        </td>

                                        <td style="font-size:0.78rem;">
                                            <div class="fw-semibold text-dark text-uppercase">{{ getCleanDescripcion(mov) }}</div>
                                            <span v-if="mov.socio_nombres"
                                                  class="badge mt-1 fw-normal text-uppercase rounded"
                                                  style="font-size:0.6rem;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;">
                                                {{ mov.socio_nombres }} {{ mov.socio_apellidos }}
                                            </span>
                                        </td>

                                        <td style="font-size:0.75rem;">
                                            <div class="d-flex flex-column text-uppercase">
                                                <div class="d-flex align-items-center gap-1">
                                                    <i class="fas fa-user-circle text-muted opacity-50"></i>
                                                    <span class="text-dark fw-semibold">{{ getPersonalName(mov.personal) }}</span>
                                                </div>
                                                <div v-if="getPersonalRole(mov.personal)" 
                                                     class="text-muted fw-bold ps-3 mt-1" 
                                                     style="font-size:0.6rem; letter-spacing: 0.5px;">
                                                    {{ getPersonalRole(mov.personal) }}
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center text-muted" style="font-size:0.72rem;">
                                            {{ formatFecha(mov.fecha) }}
                                        </td>
                                    </tr>

                                    <!-- Vacío -->
                                    <tr v-if="!preloader && movimientosBoveda.length === 0">
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-search-dollar fa-2x mb-2 d-block opacity-25"></i>
                                            <span style="font-size:0.85rem;">No se encontraron movimientos con los filtros actuales.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ── PAGINACIÓN ───────────────────────────────────── -->
                        <div v-if="pagination.last_page > 1"
                             class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted" style="font-size:0.75rem;">
                                Página {{ pagination.current_page }} de {{ pagination.last_page }}
                                ({{ pagination.total }} registros)
                            </span>
                            <nav>
                                <ul class="pagination pagination-sm shadow-sm mb-0">
                                    <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">
                                            <i class="fas fa-chevron-left" style="font-size:0.6rem;"></i>
                                        </a>
                                    </li>
                                    <li v-for="page in pagesNumber" :key="page"
                                        class="page-item" :class="{active: page === pagination.current_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li class="page-item" :class="{disabled: pagination.current_page >= pagination.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">
                                            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>

                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- MODAL: INGRESO A BÓVEDA                                           -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalIngresoBoveda" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius:0;">

                    <div class="modal-header bg-success bg-gradient py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;">
                                <i class="fas fa-plus text-white" style="font-size:0.7rem;"></i>
                            </div>
                            <h6 class="modal-title text-white fw-bold text-uppercase mb-0" style="font-size:0.85rem;">
                                Registrar Ingreso a Bóveda
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white btn-sm" @click="cerrarIngreso()"></button>
                    </div>

                    <form @submit.prevent="validarIngreso">
                        <div class="modal-body p-3">

                            <!-- Monto -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:0.65rem;">
                                    Monto a Ingresar
                                </label>
                                <div class="input-group input-group-sm shadow-sm border" style="border-radius:0;overflow:hidden;">
                                    <span class="input-group-text bg-success text-white fw-bold border-0" style="border-radius:0;font-size:0.8rem;">Bs.</span>
                                    <input type="number"
                                           class="form-control border-0 fw-bold text-dark"
                                           style="border-radius:0;font-size:1rem;"
                                           v-model="montoIngreso"
                                           placeholder="0.00"
                                           step="0.01" min="0.01"
                                           required />
                                </div>
                            </div>

                            <!-- Concepto con LiveSearch -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:0.65rem;">
                                    Concepto / Motivo
                                </label>
                                <live-search
                                    v-model="motivoIngreso"
                                    :options="opcionesIngreso"
                                    :loading="cargandoIngreso"
                                    placeholder="Buscar o seleccionar concepto..."
                                    color="success"
                                    :required="true"
                                />
                            </div>

                            <!-- Socio (solo para Aporte de capital) -->
                            <transition name="slide-down">
                                <div v-if="mostrarSocioIngreso" class="mb-3">
                                    <label class="form-label fw-bold text-success text-uppercase mb-1" style="font-size:0.65rem;">
                                        <i class="fas fa-handshake me-1"></i>Socio Aportante
                                    </label>
                                    <select class="form-select form-select-sm border-success shadow-sm"
                                            style="border-radius:0;"
                                            v-model="id_socio_ingreso"
                                            required>
                                        <option value="" disabled>Seleccione el socio...</option>
                                        <option v-for="s in lista_socios" :key="s.id" :value="s.id">
                                            {{ s.nombre_completo }} — CI: {{ s.ci }}
                                        </option>
                                    </select>
                                </div>
                            </transition>

                            <!-- Detalle libre (solo para "otro") -->
                            <transition name="slide-down">
                                <div v-if="mostrarDetalleIngreso">
                                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:0.65rem;">
                                        Especificar Detalle
                                    </label>
                                    <textarea class="form-control form-control-sm border shadow-sm"
                                              style="border-radius:0;font-size:0.8rem;"
                                              v-model="otraDescripcionIngreso"
                                              rows="2"
                                              placeholder="Describa el ingreso..."
                                              required></textarea>
                                </div>
                            </transition>

                        </div>

                        <div class="modal-footer bg-light border-top py-2 px-3 gap-2">
                            <button type="button" class="btn btn-secondary btn-sm px-3 fw-bold" style="border-radius:0;" @click="cerrarIngreso()">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-success btn-sm px-3 fw-bold shadow-sm" style="border-radius:0;">
                                <i class="fas fa-save me-1"></i>Registrar Ingreso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- MODAL: RETIRO DE BÓVEDA                                           -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalRetiroBoveda" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius:0;">

                    <div class="modal-header bg-danger bg-gradient py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;">
                                <i class="fas fa-minus text-white" style="font-size:0.7rem;"></i>
                            </div>
                            <h6 class="modal-title text-white fw-bold text-uppercase mb-0" style="font-size:0.85rem;">
                                Registrar Retiro de Bóveda
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white btn-sm" @click="cerrarRetiro()"></button>
                    </div>

                    <form @submit.prevent="validarRetiro">
                        <div class="modal-body p-3">

                            <!-- Indicador de saldo disponible -->
                            <div class="d-flex justify-content-between align-items-center bg-warning bg-opacity-10 border border-warning border-opacity-25 px-3 py-2 mb-3"
                                 style="border-radius:0;font-size:0.78rem;">
                                <span class="text-dark fw-semibold">
                                    <i class="fas fa-info-circle text-warning me-1"></i>Saldo disponible:
                                </span>
                                <span class="fw-bold text-dark font-monospace">{{ formatNumero(boveda.saldo_actual) }} Bs.</span>
                            </div>

                            <!-- Monto -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:0.65rem;">
                                    Monto a Retirar
                                </label>
                                <div class="input-group input-group-sm shadow-sm border" style="border-radius:0;overflow:hidden;">
                                    <span class="input-group-text bg-danger text-white fw-bold border-0" style="border-radius:0;font-size:0.8rem;">Bs.</span>
                                    <input type="number"
                                           class="form-control border-0 fw-bold text-danger"
                                           style="border-radius:0;font-size:1rem;"
                                           v-model="montoRetiro"
                                           placeholder="0.00"
                                           step="0.01" min="0.01"
                                           required />
                                </div>
                            </div>

                            <!-- Concepto con LiveSearch -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:0.65rem;">
                                    Concepto / Motivo
                                </label>
                                <live-search
                                    v-model="motivoRetiro"
                                    :options="opcionesRetiro"
                                    :loading="cargandoRetiro"
                                    placeholder="Buscar o seleccionar concepto..."
                                    color="danger"
                                    :required="true"
                                />
                            </div>

                            <!-- Socio (solo para Pago de dividendos) -->
                            <transition name="slide-down">
                                <div v-if="mostrarSocioRetiro" class="mb-3">
                                    <label class="form-label fw-bold text-danger text-uppercase mb-1" style="font-size:0.65rem;">
                                        <i class="fas fa-handshake me-1"></i>Socio Beneficiario
                                    </label>
                                    <select class="form-select form-select-sm border-danger shadow-sm"
                                            style="border-radius:0;"
                                            v-model="id_socio_retiro"
                                            required>
                                        <option value="" disabled>Seleccione el socio...</option>
                                        <option v-for="s in lista_socios" :key="s.id" :value="s.id">
                                            {{ s.nombre_completo }} — CI: {{ s.ci }}
                                        </option>
                                    </select>
                                </div>
                            </transition>

                            <!-- Detalle libre (solo para "otro") -->
                            <transition name="slide-down">
                                <div v-if="mostrarDetalleRetiro">
                                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:0.65rem;">
                                        Especificar Detalle
                                    </label>
                                    <textarea class="form-control form-control-sm border shadow-sm"
                                              style="border-radius:0;font-size:0.8rem;"
                                              v-model="otraDescripcionRetiro"
                                              rows="2"
                                              placeholder="Describa el retiro..."
                                              required></textarea>
                                </div>
                            </transition>

                        </div>

                        <div class="modal-footer bg-light border-top py-2 px-3 gap-2">
                            <button type="button" class="btn btn-secondary btn-sm px-3 fw-bold" style="border-radius:0;" @click="cerrarRetiro()">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm" style="border-radius:0;">
                                <i class="fas fa-save me-1"></i>Registrar Retiro
                            </button>
                        </div>
                    </form>
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
            // ── Bóveda ────────────────────────────────────────────────────────
            boveda: {
                id_boveda: 0,
                saldo_actual: 0,
                fecha_apertura: '',
                usuario_apertura: '',
            },
            preloader: false,

            // ── Movimientos ───────────────────────────────────────────────────
            movimientosBoveda: [],
            totalIngresosBoveda: 0,
            totalSalidasBoveda: 0,
            pagination: { total: 0, current_page: 1, last_page: 0, from: 0, to: 0 },
            offset: 2,

            // ── Filtros ───────────────────────────────────────────────────────
            filtroTipoMovimiento: 'todos',
            fechaInicio: moment().subtract(1, 'month').format('YYYY-MM-DD'),
            fechaFin: moment().format('YYYY-MM-DD'),

            // ── Modal Ingreso ─────────────────────────────────────────────────
            montoIngreso: '',
            motivoIngreso: '',
            opcionesIngreso: [],
            cargandoIngreso: false,
            otraDescripcionIngreso: '',
            id_socio_ingreso: '',

            // ── Modal Retiro ──────────────────────────────────────────────────
            montoRetiro: '',
            motivoRetiro: '',
            opcionesRetiro: [],
            cargandoRetiro: false,
            otraDescripcionRetiro: '',
            id_socio_retiro: '',

            // ── Socios ────────────────────────────────────────────────────────
            lista_socios: [],
        };
    },

    computed: {
        // Campos condicionales del modal de ingreso
        mostrarSocioIngreso()   { return this.motivoIngreso.toLowerCase() === 'aporte de capital'; },
        mostrarDetalleIngreso() { return this.motivoIngreso.toLowerCase() === 'otro'; },

        // Campos condicionales del modal de retiro
        mostrarSocioRetiro()    { return this.motivoRetiro.toLowerCase() === 'pago de dividendos'; },
        mostrarDetalleRetiro()  { return this.motivoRetiro.toLowerCase() === 'otro'; },

        // Páginas del paginador
        pagesNumber() {
            if (!this.pagination.to) return [];
            let from = Math.max(1, this.pagination.current_page - this.offset);
            let to   = Math.min(this.pagination.last_page, from + this.offset * 2);
            const pages = [];
            for (let i = from; i <= to; i++) pages.push(i);
            return pages;
        },
    },

    async mounted() {
        this.preloader = true;
        await Promise.all([
            this.getBoveda(),
            this.getMovimientosBoveda(),
            this.getSocios(),
        ]);
        this.preloader = false;
    },

    methods: {

        // ── Helpers ───────────────────────────────────────────────────────────
        formatNumero(value) {
            const n = parseFloat(value || 0);
            return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        formatFecha(fecha) {
            return fecha ? moment(fecha).format('DD/MM/YY HH:mm') : '---';
        },
        esTransferenciaInterna(mov) {
            const d = (mov.descripcion || '').toLowerCase();
            return d.includes('transferencia a caja') || d.includes('transferencia desde caja');
        },
        getCleanDescripcion(mov) {
            let desc = mov.descripcion || '';
            if (mov.personal) {
                const personalEscaped = mov.personal.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp('\\s*-\\s*(asesor:?\\s*)?' + personalEscaped + '\\s*$', 'i');
                desc = desc.replace(regex, '');
            }
            return desc.trim();
        },
        getPersonalName(personal) {
            if (!personal) return '';
            const parts = personal.split('(');
            return parts[0].trim();
        },
        getPersonalRole(personal) {
            if (!personal) return '';
            const parts = personal.split('(');
            return parts[1] ? parts[1].replace(')', '').trim() : '';
        },

        // ── API ───────────────────────────────────────────────────────────────
        async getBoveda() {
            try {
                const { data } = await axios.get('/get_boveda');
                this.boveda.id_boveda       = data.id_boveda;
                this.boveda.saldo_actual    = data.saldo_actual;
                this.boveda.fecha_apertura  = data.fecha_apertura;
                this.boveda.usuario_apertura = data.usuario_apertura;
            } catch (e) {
                console.error('Error al obtener bóveda:', e);
            }
        },

        async getMovimientosBoveda(page = 1) {
            try {
                const { data } = await axios.get('/get_movimientos_boveda', {
                    params: {
                        page,
                        tipo:        this.filtroTipoMovimiento,
                        fecha_inicio: this.fechaInicio,
                        fecha_fin:   this.fechaFin,
                    },
                });
                this.movimientosBoveda   = data.movimientos.data;
                this.pagination          = data.movimientos;
                this.totalIngresosBoveda = data.totales.ingresos;
                this.totalSalidasBoveda  = data.totales.salidas;
            } catch (e) {
                console.error('Error al obtener movimientos:', e);
            }
        },

        async getSocios() {
            try {
                const { data } = await axios.get('/socio/activos');
                this.lista_socios = data;
            } catch (e) {
                console.error('Error al obtener socios:', e);
            }
        },

        async cargarMotivosIngreso() {
            this.cargandoIngreso = true;
            try {
                const { data } = await axios.get('/get_motivos_ingresos_activos?tipo=boveda');
                this.opcionesIngreso = data.map(m => m.nombre);
            } catch (e) {
                console.error('Error al cargar motivos de ingreso:', e);
            } finally {
                this.cargandoIngreso = false;
            }
        },

        async cargarMotivosRetiro() {
            this.cargandoRetiro = true;
            try {
                const { data } = await axios.get('/get_motivos_gastos_activos?tipo=boveda');
                this.opcionesRetiro = data.map(m => m.nombre);
            } catch (e) {
                console.error('Error al cargar motivos de retiro:', e);
            } finally {
                this.cargandoRetiro = false;
            }
        },

        // ── Paginación ────────────────────────────────────────────────────────
        cambiarPagina(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.getMovimientosBoveda(page);
            }
        },
        buscarMovimientoBoveda() {
            this.getMovimientosBoveda(1);
        },

        // ── Apertura ──────────────────────────────────────────────────────────
        async aperturarBoveda() {
            const confirmar = await Swal.fire({
                title: '¿Aperturar Bóveda?',
                text: 'Esta acción crea el fondo central de la institución. Solo debe realizarse una vez.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, aperturar',
                cancelButtonText: 'Cancelar',
            });
            if (!confirmar.isConfirmed) return;

            try {
                await axios.post('/aperturar_boveda');
                await this.getBoveda();
                Swal.fire({ title: 'Éxito', text: 'Bóveda aperturada correctamente.', icon: 'success', timer: 1500, showConfirmButton: false });
            } catch (error) {
                Swal.fire('Error', error.response?.data?.message || 'Ocurrió un error inesperado.', 'error');
            }
        },

        // ── Modal Ingreso ─────────────────────────────────────────────────────
        async abrirIngreso() {
            this.montoIngreso = '';
            this.motivoIngreso = '';
            this.otraDescripcionIngreso = '';
            this.id_socio_ingreso = '';
            this.cargarMotivosIngreso();
            $('#modalIngresoBoveda').modal('show');
        },
        cerrarIngreso() {
            $('#modalIngresoBoveda').modal('hide');
        },

        validarIngreso() {
            if (!this.montoIngreso || parseFloat(this.montoIngreso) <= 0) {
                Swal.fire('Advertencia', 'Ingrese un monto válido mayor a cero.', 'warning');
                return;
            }
            if (!this.motivoIngreso.trim()) {
                Swal.fire('Advertencia', 'Seleccione o escriba el concepto del ingreso.', 'warning');
                return;
            }
            if (this.mostrarSocioIngreso && !this.id_socio_ingreso) {
                Swal.fire('Advertencia', 'Debe seleccionar el Socio/Inversionista que hace el aporte.', 'warning');
                return;
            }
            if (this.mostrarDetalleIngreso && !this.otraDescripcionIngreso.trim()) {
                Swal.fire('Advertencia', 'Debe especificar el detalle del ingreso.', 'warning');
                return;
            }

            const descripcion = this.mostrarDetalleIngreso
                ? `otro: ${this.otraDescripcionIngreso.trim()}`
                : this.motivoIngreso;

            this.ingresarABoveda(descripcion);
        },

        async ingresarABoveda(descripcion) {
            try {
                await axios.post('/ingresar_boveda', {
                    monto:    this.montoIngreso,
                    descripcion,
                    id_socio: this.mostrarSocioIngreso ? this.id_socio_ingreso : null,
                });
                Swal.fire({ title: 'Éxito', text: 'Ingreso registrado correctamente.', icon: 'success', timer: 1500, showConfirmButton: false });
                this.cerrarIngreso();
                await Promise.all([this.getBoveda(), this.getMovimientosBoveda()]);
            } catch (error) {
                Swal.fire('Error', 'No se pudo registrar el ingreso.', 'error');
                console.error(error);
            }
        },

        // ── Modal Retiro ──────────────────────────────────────────────────────
        async abrirRetiro() {
            if (this.boveda.id_boveda === 0) {
                Swal.fire('Atención', 'Debe aperturar la Bóveda primero.', 'warning');
                return;
            }
            this.montoRetiro = '';
            this.motivoRetiro = '';
            this.otraDescripcionRetiro = '';
            this.id_socio_retiro = '';
            this.cargarMotivosRetiro();
            $('#modalRetiroBoveda').modal('show');
        },
        cerrarRetiro() {
            $('#modalRetiroBoveda').modal('hide');
        },

        validarRetiro() {
            if (!this.montoRetiro || parseFloat(this.montoRetiro) <= 0) {
                Swal.fire('Advertencia', 'Ingrese un monto válido mayor a cero.', 'warning');
                return;
            }
            if (parseFloat(this.boveda.saldo_actual) - parseFloat(this.montoRetiro) < 0) {
                Swal.fire('Error', 'Saldo insuficiente en bóveda para realizar el retiro.', 'error');
                return;
            }
            if (!this.motivoRetiro.trim()) {
                Swal.fire('Advertencia', 'Seleccione o escriba el concepto del retiro.', 'warning');
                return;
            }
            if (this.mostrarSocioRetiro && !this.id_socio_retiro) {
                Swal.fire('Advertencia', 'Debe seleccionar el Socio beneficiario del pago de dividendos.', 'warning');
                return;
            }
            if (this.mostrarDetalleRetiro && !this.otraDescripcionRetiro.trim()) {
                Swal.fire('Advertencia', 'Debe especificar el detalle del retiro.', 'warning');
                return;
            }

            const descripcion = this.mostrarDetalleRetiro
                ? `otro: ${this.otraDescripcionRetiro.trim()}`
                : this.motivoRetiro;

            this.retirarDeBoveda(descripcion);
        },

        async retirarDeBoveda(descripcion) {
            try {
                await axios.post('/retirar_boveda', {
                    monto:    this.montoRetiro,
                    descripcion,
                    id_socio: this.mostrarSocioRetiro ? this.id_socio_retiro : null,
                });
                Swal.fire({ title: 'Éxito', text: 'Retiro registrado correctamente.', icon: 'success', timer: 1500, showConfirmButton: false });
                this.cerrarRetiro();
                await Promise.all([this.getBoveda(), this.getMovimientosBoveda()]);
            } catch (error) {
                Swal.fire('Error', 'No se pudo registrar el retiro.', 'error');
                console.error(error);
            }
        },
    },
};
</script>

<style scoped>
/* Tabla compacta */
.bov-table { font-size: 0.8rem; }
.bov-table th {
    font-size: 0.7rem;
    letter-spacing: 0.4px;
    padding: 8px 6px;
    vertical-align: middle;
}
.bov-table td { padding: 6px 6px; vertical-align: middle; }

/* Utilitarios */
.bg-light-success { background-color: #f0fdf4; }
.bg-light-secondary { background-color: #f8f9fa; }

/* Animación slide-down para campos condicionales */
.slide-down-enter-active { transition: all 0.2s ease-out; }
.slide-down-leave-active { transition: all 0.15s ease-in; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
