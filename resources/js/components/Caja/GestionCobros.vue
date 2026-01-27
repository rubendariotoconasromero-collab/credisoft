<template>
    <div class="card shadow-lg border-0">
        
        <div v-if="vistaInterna === 'lista'">
            <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 text-center">
                    <h5 class="header-title my-0 fw-bold text-white text-uppercase">
                        <i class="fas fa-cash-register me-2"></i> Gestión de Pago de Cuotas
                    </h5>
                </div>
                <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
            </div>
            
            <div class="card-body">
                
                <ul class="nav nav-pills nav-justified mb-4 bg-light rounded">
                    <li class="nav-item text-dark">
                        <a class="text-decoration-none py-1 w-100 fw-bold text-uppercase d-flex align-items-center justify-content-center" 
                        :class="tabActual === 'normal' ? 'bg-warning text-white' : 'text-dark bg-transparent hover-effect'" 
                        href="#" @click.prevent="tabActual = 'normal'" style="cursor: pointer; transition: all 0.2s;">
                            <!-- <i class="fas fa-file-invoice-dollar me-2"></i>  -->
                            Cuotas Normales
                        </a>
                    </li>
                    <li class="nav-item me-0 pe-0 text-dark">
                        <a class="text-decoration-none py-1 w-100 fw-bold text-uppercase d-flex align-items-center justify-content-center" 
                        :class="tabActual === 'repro' ? 'bg-info text-white' : 'text-dark bg-transparent hover-effect'"
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
                                        <td class="text-center">{{ item.fecha_desembolso }}</td>
                                        <td class="fw-bold text-end">{{ item.total_pagar_plan }}</td>
                                        <td class="text-center">{{ item.asesor }}</td>
                                        <td class="text-center">{{ item.fecha_inicio_plan }}</td>
                                        <td class="text-center">{{ item.fecha_fin_plan }}</td>
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
                            <div class="d-flex justify-content-between mb-2"><strong>Inicio:</strong> <span>{{ plan_pago.fecha_inicio_plan }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><strong>Fin:</strong> <span>{{ plan_pago.fecha_fin_plan }}</span></div>
                            <div v-if="dias_mora > 0" class="d-flex justify-content-between">
                                <span class="fw-bold text-danger">Mora:</span>
                                <span class="fw-bold text-danger">{{ dias_mora }} Días</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="font-size:12px;">
                    <table class="table mb-4 table-striped table-sm table-hover border">
                        <thead class="text-dark table-warning">
                            <tr>
                                <th class="text-center">Nro</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Capital</th>
                                <th class="text-center">Interes</th>
                                <th class="text-center">Saldo Cap.</th>
                                <th class="text-center">Total Bs</th>
                                <th class="text-center">Días Trans.</th>
                                <th class="text-center">Int. Acumulado</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(cuota, index) in lista_cuotas_plan" :key="index" class="align-middle">
                                <td class="text-center">{{ cuota.numero }}</td>
                                <td class="text-center">{{ formatFecha(cuota.fecha) }}</td>
                                <td class="text-center">{{ formatNumero(cuota.capital) }}</td>
                                <td class="text-center">{{ formatNumero(cuota.interes) }}</td>
                                <td class="text-center">{{ formatNumero(cuota.saldo_capital) }}</td>
                                <td class="text-center fw-bold">{{ formatNumero(cuota.total) }}</td>
                                <td class="text-center">{{ cuota.estado == 0 ? '---' : cuota.dias_transcurridos }}</td>
                                <td class="text-center">{{ cuota.estado == 0 ? '---' : formatNumero(cuota.interes_acumulado) }}</td>
                                <td class="text-center">
                                    <span v-if="esPrimerRegistroConMora(index) && cuota.dias_pasados > 0" class="badge bg-danger text-white">
                                        {{ cuota.dias_pasados }} - EN MORA
                                    </span>
                                    <span v-else-if="cuota.estado == 1" class="badge bg-info text-dark">Por pagar</span>
                                    <span v-else-if="cuota.estado == 2" class="badge bg-success">Pagado</span>
                                    <span v-else-if="cuota.estado == 0" class="badge bg-dark">Anulado</span>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" v-model="selectedCuotas" :value="cuota.id" 
                                           :disabled="!isCheckboxEnabled(index)" v-if="cuota.estado == 1">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="paymentModalLabel">Detalles del Pago</h5>
                        <button type="button" class="btn-close btn-close-white" @click="cerrarModalCobrarCuotas"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Cliente</label>
                                <input type="text" class="form-control" :value="plan_pago.cliente" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Fecha de Pago</label>
                                <input type="date" class="form-control" v-model="paymentDetails.fecha_pago">
                            </div>

                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-light border-warning mb-2">
                                            <div class="card-body p-2">
                                                <h6 class="fw-bold text-warning">Intereses</h6>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Total</span>
                                                    <input type="text" class="form-control text-center" :value="totalInteres" disabled>
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Condonar</span>
                                                    <input type="number" class="form-control text-center" v-model.number="paymentDetails.monto_condonado_interes" min="0" :max="totalInteres">
                                                </div>
                                                <textarea class="form-control form-control-sm" v-model="paymentDetails.motivo_condonacion_interes" placeholder="Motivo condonación..." rows="1"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light border-danger mb-2">
                                            <div class="card-body p-2">
                                                <h6 class="fw-bold text-danger">Multas ({{ dias_mora }} días)</h6>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Total</span>
                                                    <input type="text" class="form-control text-center" :value="totalMulta" disabled>
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Condonar</span>
                                                    <input type="number" class="form-control text-center" v-model.number="paymentDetails.monto_condonado_multa" min="0" :max="totalMulta">
                                                </div>
                                                <textarea class="form-control form-control-sm" v-model="paymentDetails.motivo_condonacion_multa" placeholder="Motivo condonación..." rows="1"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold small">Capital</label>
                                <input type="text" class="form-control fw-bold" :value="totalCapital" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small text-primary">Total a Pagar</label>
                                <input type="text" class="form-control fw-bold text-primary" :value="totalPagar" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small text-success">Líquido (Con Dscto)</label>
                                <input type="text" class="form-control fw-bold text-success border-success" :value="totalLiquido" disabled>
                            </div>

                            <div class="col-12">
                                <label class="fw-bold">Forma de Pago</label>
                                <select v-model="paymentDetails.forma_pago" class="form-select">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia - QR">Transferencia - QR</option>
                                    <option value="Depósito banco">Depósito banco</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalCobrarCuotas">Cancelar</button>
                        <button type="button" class="btn btn-primary fw-bold" @click="procesarPagoCuotas">
                            <i class="fas fa-check-circle me-1"></i> Cobrar {{ totalLiquido }}
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
        // --- Computed de Pagos (Complejo) ---
        selectedCuotasDetails() {
            return this.lista_cuotas_plan.filter(cuota => this.selectedCuotas.includes(cuota.id));
        },
        totalCapital() {
            return this.selectedCuotasDetails.reduce((sum, c) => sum + parseFloat(c.capital), 0).toFixed(2);
        },
        totalInteres() {
            const today = moment();
            return this.selectedCuotasDetails.reduce((sum, c) => {
                const fCuota = moment(c.fecha, 'YYYY-MM-DD');
                if (!fCuota.isValid()) return sum;
                // Si fecha es futura, usar interes acumulado (devengado parcial) si existe, sino normal
                // Nota: Tu lógica original usaba interes_acumulado si isAfter. Ajusta según negocio.
                return sum + (fCuota.isAfter(today) 
                    ? parseFloat(c.interes_acumulado || 0) 
                    : parseFloat(c.interes || 0));
            }, 0).toFixed(2);
        },
        totalMulta() {
            // Multa fija global (dias_mora * tarifa) aplicada una vez por transacción o por cuota? 
            // Tu código original sumaba (dias_mora * multa) EN CADA CUOTA en computed, 
            // pero luego en 'totalMulta' solo usaba (dias_mora * multa) una vez. Usaré la segunda lógica.
            return (this.dias_mora * this.multa_dia).toFixed(2);
        },
        totalPagar() {
            return (parseFloat(this.totalCapital) + parseFloat(this.totalInteres) + parseFloat(this.totalMulta)).toFixed(2);
        },
        totalLiquido() {
            const desc = parseFloat(this.paymentDetails.monto_condonado_interes) + parseFloat(this.paymentDetails.monto_condonado_multa);
            return (parseFloat(this.totalPagar) - desc).toFixed(2);
        }
    },
    mounted() {
        this.buscarPlanPago(); // Cargar inicial
    },
    methods: {
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

        // --- LÓGICA DE PAGO ---
        isCheckboxEnabled(index) {
            const firstUnpaid = this.lista_cuotas_plan.findIndex(c => c.estado == 1);
            if (index === firstUnpaid) return true;
            // Permitir seleccionar si la anterior ya está seleccionada
            if (index > 0 && this.lista_cuotas_plan[index].estado == 1) {
                return this.selectedCuotas.includes(this.lista_cuotas_plan[index - 1].id);
            }
            return false;
        },
        esPrimerRegistroConMora(index) {
            const primerConMora = this.lista_cuotas_plan.findIndex(c => c.dias_pasados > 0);
            return index === primerConMora;
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
                // Mapear datos para backend
                const cuotasData = this.selectedCuotas.map(id => {
                    const c = this.selectedCuotasDetails.find(x => x.id === id);
                    return {
                        id_cuota: id,
                        fecha_pago: this.paymentDetails.fecha_pago,
                        monto_pago: c.total, // El backend espera el monto total nominal
                        dias_pasados: c.dias_pasados,
                        forma_pago: this.paymentDetails.forma_pago,
                        // Condonaciones se envían globales o prorrateadas, aquí lo enviamos tal cual tu lógica original
                        // OJO: Tu lógica original enviaba los campos de condonación repetidos en cada cuota.
                        monto_condonado_interes: parseFloat(this.paymentDetails.monto_condonado_interes) || 0,
                        motivo_condonacion_interes: this.paymentDetails.motivo_condonacion_interes || '',
                        monto_condonado_multa: parseFloat(this.paymentDetails.monto_condonado_multa) || 0,
                        motivo_condonacion_multa: this.paymentDetails.motivo_condonacion_multa || ''
                    };
                });

                const response = await axios.post('/pagar_cuotas', {
                    id_plan_pago: this.plan_pago.id,
                    cuotas: cuotasData
                });

                Swal.fire('Pago Exitoso', 'Las cuotas han sido cobradas.', 'success');
                this.cerrarModalCobrarCuotas();
                // Recargar tabla
                this.verDetallePlan(this.plan_pago);

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo procesar el pago.', 'error');
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

        // --- UTILS ---
        formatNumero(val) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
        },
        formatFecha(f) {
            return moment(f).format('DD/MM/YYYY');
        }
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
        border-radius:10px;
        min-width: 100px;
    }

</style>
