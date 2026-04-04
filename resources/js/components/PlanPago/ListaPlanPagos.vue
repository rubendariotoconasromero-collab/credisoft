<template>
    <div class="card">
        <div class="card-header bg-warning py-1">
            <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                GESTION DE CARTERA DE PLAN DE PAGOS
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="input-group">
                        <select @change="emitirBusqueda" v-model="filtros.opcion_asesor" class="form-select">
                            <option value="0">Todos los asesores</option>
                            <option v-for="(item, index) in asesores" :key="index" :value="item.id">
                                {{ item.personal }}
                            </option>
                        </select>
                        <select @change="emitirBusqueda" v-model="filtros.criterio" class="form-select">
                            <option value="plan_pago.id">Cod. Credito</option>
                            <option value="cliente.nombre">Nombre cliente</option>
                            <option value="cliente.ci">CI</option>
                        </select>
                        <select @change="emitirBusqueda" v-model="filtros.estado_credito" class="form-select">
                            <option value="todos">Todos</option>
                            <option value="vigentes">Vigentes</option>
                            <option value="vencidos">Vencidos</option>
                        </select>
                        <input v-model="filtros.fecha_inicio" type="date" class="form-control" @change="emitirBusqueda">
                        <input v-model="filtros.fecha_fin" type="date" class="form-control" @change="emitirBusqueda">
                        <input v-model="filtros.buscar" type="text" class="form-control" placeholder="Buscar..." @input="emitirBusqueda">
                        
                        <button class="btn btn-success" @click="emitirBusqueda">
                            <i class="fas fa-search"></i>
                        </button>
                        <button @click="$emit('exportar')" class="btn btn-success btn-sm ms-1">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive text-uppercase table-plan-payments">
                <table class="table mb-4 table-hover table-striped table-sm">
                    <thead class="text-uppercase text-dark">
                        <tr class="align-middle table-success">
                            <th class="text-dark text-uppercase fw-bold" width="7%"># Cred.</th>
                            <th class="text-dark text-uppercase fw-bold" width="15%">Cliente</th>
                            <th class="text-dark text-uppercase fw-bold" width="15%">Gar./Cod.</th>
                            <th class="text-dark text-uppercase fw-bold" width="10%">Desembolso</th>
                            <th class="text-dark text-uppercase fw-bold" width="8%">Monto</th>
                            <th class="text-dark text-uppercase fw-bold" width="12%">Asesor</th>
                            <th class="text-dark text-uppercase fw-bold" width="10%">F. inicio</th>
                            <th class="text-dark text-uppercase fw-bold" width="10%">F. fin</th>
                            <th class="text-dark text-uppercase fw-bold" width="10%">Cuotas</th>
                            <th class="text-dark text-uppercase fw-bold text-center" width="5%">Estado</th>
                            <th class="text-dark text-uppercase fw-bold text-center" width="5%">Op.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="lista.length > 0">
                            <tr v-for="item in lista" :key="item.id" style="vertical-align: middle">
                                <td class="text-uppercase">{{ item.id }}</td>
                                <td class="text-uppercase fw-bold">{{ item.cliente }}</td>
                                
                                <td class="text-uppercase">
                                    <div v-if="obtenerCodeudores(item.id_solicitud).length > 0">
                                        <p v-for="codeudor in obtenerCodeudores(item.id_solicitud)" :key="codeudor.id" class="mb-1 text-truncate">
                                            <small class="text-muted">• </small>{{ codeudor.nombre }}
                                        </p>
                                    </div>
                                    <div v-else class="text-center text-muted fst-italic">
                                        Sin codeudores
                                    </div>
                                </td>
    
                                <td class="text-uppercase">{{ item.fecha_desembolso }}</td>
                                <td class="text-uppercase fw-bold">{{ item.total_pagar_plan }}</td>
                                <td class="text-uppercase">{{ item.asesor }}</td>
                                <td class="text-uppercase">{{ item.fecha_inicio_plan }}</td>
                                
                                <td class="text-uppercase" :style="(item.estado_plan == 10) ? 'text-decoration: line-through;' : ''">
                                    {{ item.fecha_fin_plan }}
                                </td>
                                
                                <td class="text-uppercase" :style="(item.estado_plan == 10) ? 'text-decoration: line-through;' : ''">
                                    <small style="font-size:10px;">
                                        {{ item.nro_cuotas }}
                                        <small style="font-size:10px;">({{ item.lapso_capital }})</small>
                                    </small>
                                </td>
    
                                
                                <td class="text-uppercase text-center">
                                    <span style="width:110px;" v-if="(item.estado_plan == 1 && item.fecha_fin_plan >= fechaActual)" class="badge badge-fixed-width text-bg-success d-inline-block mt-1">Nuevo</span>
                                    <span style="width:110px;" v-if="(item.estado_plan == 1 && item.fecha_fin_plan < fechaActual)" class="badge badge-fixed-width text-bg-danger d-inline-block mt-1">Vencido</span>
                                    <span style="width:110px;" v-else-if="item.estado_plan == 0" class="badge badge-fixed-width text-bg-dark d-inline-block mt-1">Anulado</span>
                                    <span style="width:110px;" v-else-if="item.estado_plan == 2" class="badge badge-fixed-width text-bg-success d-inline-block mt-1">Completado</span>
                                    <span style="width:110px;" v-else-if="item.estado_plan == 10" class="badge badge-fixed-width text-bg-info d-inline-block mt-1">Amortizado</span>
                                    <span style="width:110px;" v-else-if="item.estado_plan == 5" class="badge badge-fixed-width text-bg-warning text-dark d-inline-block mt-1">
                                         En Proceso
                                    </span>
                                    <span style="width:110px;" v-if="item.desembolso == 1" class="badge badge-fixed-width text-bg-warning d-inline-block mt-1">Sin Desemb.</span>
                                </td>
    
                                <td class="text-uppercase text-center">
                                    <div class="btn-group my-0 py-0">
                                        <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm my-0 py-0 text-center" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                        </a>
                                        <ul class="dropdown-menu my-0 py-0">
                                            
                                            <li v-if="item.estado_plan == 1 || item.estado_plan == 5">
                                                <a @click="item.estado_plan == 5 ? null : $emit('anular', item)" 
                                                class="dropdown-item" 
                                                :class="item.estado_plan == 5 ? 'text-muted disabled' : 'text-danger'" 
                                                href="#">
                                                    <i class="fas fa-times"></i> Anular
                                                    <small v-if="item.estado_plan == 5" class="d-block fst-italic" style="font-size:10px;">(Pendiente Aprob.)</small>
                                                </a>
                                            </li>
    
                                            <li v-else-if="item.estado_plan == 0">
                                                <a @click="$emit('activar', item)" class="dropdown-item text-success" href="#">
                                                    <i class="fas fa-check"></i> Activar
                                                </a>
                                            </li>
    
                                            <li>
                                                <a @click="$emit('ver-detalle', item)" class="dropdown-item text-info" href="#">
                                                    <i class="fas fa-info me-1"></i> Ver detalle
                                                </a>
                                            </li>
    
                                            <li>
                                                <a @click="item.estado_plan == 5 ? null : $emit('generar-contrato', item)" 
                                                class="dropdown-item"
                                                :class="item.estado_plan == 5 ? 'text-muted disabled' : 'text-success'" 
                                                href="#">
                                                    <i class="fas fa-file-contract"></i> Generar contrato
                                                </a>
                                            </li>
    
                                            <template v-if="item.estado_plan == 1 || item.estado_plan == 5">
                                                <li>
                                                    <a @click="item.estado_plan == 5 ? null : $emit('reprogramar', item, 'REPROGRAMACION')" 
                                                    class="dropdown-item"
                                                    :class="item.estado_plan == 5 ? 'text-muted disabled' : 'text-dark'" 
                                                    href="#">
                                                        <i class="fas fa-clock me-1"></i> Reprogramar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a @click="item.estado_plan == 5 ? null : $emit('reprogramar', item, 'REFINANCIAMIENTO')" 
                                                    class="dropdown-item fw-bold"
                                                    :class="item.estado_plan == 5 ? 'text-muted disabled' : 'text-success'" 
                                                    href="#">
                                                        <i class="fas fa-hand-holding-usd me-1"></i> Refinanciar
                                                    </a>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                                    <h5 class="fw-normal">No se encontraron planes de pago</h5>
                                    <p class="small">Intenta ajustar los filtros de búsqueda o registra un nuevo plan.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- <template v-if="lista.length <= 30"> -->
                    <br><br><br><br><br><br>
                <!-- </template> -->
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    name: 'ListaPlanPagos',
    props: {
        lista: { type: Array, default: () => [] },
        asesores: { type: Array, default: () => [] },
        codeudoresGlobal: { type: Array, default: () => [] },
        fechaActual: { type: String, default: '' }
    },
    emits: ['filtrar', 'exportar', 'anular', 'activar', 'ver-detalle', 'generar-contrato', 'reprogramar'],
    data() {
        return {
            filtros: {
                opcion_asesor: 0,
                criterio: 'cliente.nombre',
                estado_credito: 'todos',
                fecha_inicio: moment().subtract(3, 'month').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD'),
                buscar: ''
            }
        }
    },
    mounted() {
        this.emitirBusqueda();
    },
    methods: {
        emitirBusqueda() {
            // Enviamos los filtros al padre para que él haga la petición Axios
            this.$emit('filtrar', this.filtros);
        },
        obtenerCodeudores(solicitudId) {
            // Lógica filtrada de codeudores
            return this.codeudoresGlobal.filter(c => c.id_solicitud === solicitudId);
        }
    }
}
</script>

<style scoped>
    @import '../../components/styles/frmPlanPago.css';

    .table-plan-payments th{
        font-size:12px !important;
    }
</style>