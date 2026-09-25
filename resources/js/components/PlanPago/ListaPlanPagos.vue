<template>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning py-1">
            <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                Gestión de cartera de planes de pago
            </h5>
        </div>

        <div class="card-body py-2">
            <div class="row g-2 mb-3 mt-1 align-items-start">
                <div class="col-xl-3 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-secondary" title="Rango de fechas">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                        <input v-model="filtros.fecha_inicio" type="date" class="form-control"
                            aria-label="Fecha inicial" @change="emitirBusqueda" />
                        <input v-model="filtros.fecha_fin" type="date" class="form-control"
                            aria-label="Fecha final" @change="emitirBusqueda" />
                    </div>
                    <small v-if="fechaInvalida" class="text-danger fw-bold d-block mt-1 fecha-error">
                        <i class="fas fa-exclamation-circle me-1"></i> La fecha inicial debe ser anterior a la final
                    </small>
                </div>

                <div class="col-xl-7 col-lg-6">
                    <div class="input-group input-group-sm filtros-cartera">
                        <select v-model="filtros.opcion_asesor" class="form-select" aria-label="Asesor"
                            @change="emitirBusqueda">
                            <option value="0">Todos los asesores</option>
                            <option v-for="item in asesores" :key="item.id" :value="item.id">
                                {{ item.personal }}
                            </option>
                        </select>
                        <select v-model="filtros.estado_credito" class="form-select" aria-label="Estado del crédito"
                            @change="emitirBusqueda">
                            <option value="todos">Todos los estados</option>
                            <option value="vigentes">Vigentes</option>
                            <option value="vencidos">Vencidos</option>
                        </select>
                        <select v-model="filtros.criterio" class="form-select" aria-label="Criterio de búsqueda">
                            <option value="plan_pago.id">Código crédito</option>
                            <option value="cliente.nombre">Nombre cliente</option>
                            <option value="cliente.ci">CI cliente</option>
                        </select>
                        <input v-model="filtros.buscar" type="text" class="form-control campo-busqueda"
                            placeholder="Buscar..." aria-label="Buscar cartera" @input="emitirBusqueda" />
                        <button class="btn btn-success" type="button" title="Buscar" @click="emitirBusqueda">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-2 text-end">
                    <button type="button" class="btn btn-success btn-sm" title="Exportar cartera a Excel"
                        @click="$emit('exportar')">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </div>

            <div class="table-container-custom table-responsive">
                <table class="table table-striped table-hover table-compact-custom align-middle">
                    <thead>
                        <tr>
                            <th class="text-center"># Cred.</th>
                            <th>Cliente</th>
                            <th>Gar./Cod.</th>
                            <th>Asesor</th>
                            <th class="text-center">Desembolso</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">F. inicio</th>
                            <th class="text-center">F. fin</th>
                            <th class="text-center">Cuotas</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Op.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="lista.length > 0">
                            <tr v-for="item in lista" :key="item.id">
                                <td class="text-center font-monospace py-1 fw-semibold text-secondary">{{ item.id }}</td>
                                <td class="text-uppercase py-1">
                                    <div class="d-flex flex-column cliente-cell">
                                        <span class="text-dark fw-bold cliente-nombre" :title="item.cliente">{{ item.cliente }}</span>
                                        <span class="text-muted cliente-ci"><strong class="fw-semibold">CI:</strong> {{ item.ci || '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-uppercase py-1">
                                    <div v-if="obtenerCodeudores(item.id_solicitud).length > 0" class="codeudor-list">
                                        <div v-for="codeudor in obtenerCodeudores(item.id_solicitud)" :key="codeudor.id"
                                            class="text-truncate text-secondary codeudor-item" :title="codeudor.nombre">
                                            <i class="fas fa-user-friends me-1 text-muted"></i>{{ codeudor.nombre }}
                                        </div>
                                    </div>
                                    <div v-else class="text-muted fst-italic texto-secundario">Sin codeudores</div>
                                </td>
                                <td class="text-uppercase py-1 text-secondary asesor-cell" :title="item.asesor">{{ item.asesor }}</td>
                                <td class="text-center py-1 text-secondary fecha-cell">{{ formatearFecha(item.fecha_desembolso) }}</td>
                                <td class="text-end font-monospace py-1 fw-bold text-dark text-nowrap">{{ formatMoneda(item.total_pagar_plan) }}</td>
                                <td class="text-center py-1 text-secondary fecha-cell">{{ formatearFecha(item.fecha_inicio_plan) }}</td>
                                <td class="text-center py-1 text-secondary fecha-cell"
                                    :class="{ 'text-decoration-line-through': Number(item.estado_plan) === 10 }">
                                    {{ formatearFecha(item.fecha_fin_plan) }}
                                </td>
                                <td class="text-center py-1">
                                    <span class="badge bg-light text-dark border border-secondary-subtle plazo-badge rounded">
                                        {{ item.nro_cuotas }} - {{ item.lapso_capital }}
                                    </span>
                                </td>
                                <td class="text-uppercase text-center py-1">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        <span class="badge badge-custom rounded" :class="estadoBadgeClass(item)">{{ estadoTexto(item) }}</span>
                                        <span v-if="Number(item.desembolso) === 1" class="badge bg-warning text-dark badge-custom rounded">Sin desembolso</span>
                                    </div>
                                </td>
                                <td class="text-uppercase text-center py-1">
                                    <div class="dropdown">
                                        <a class="text-success dropdown-toggle" href="#" role="button"
                                            data-bs-toggle="dropdown" aria-expanded="false" title="Opciones" @click.prevent>
                                            <i class="fas fa-ellipsis-h fa-lg"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li v-if="Number(item.estado_plan) === 1 || Number(item.estado_plan) === 5">
                                                <a href="#" class="dropdown-item"
                                                    :class="Number(item.estado_plan) === 5 ? 'text-muted disabled' : 'text-danger'"
                                                    @click.prevent="emitirSiDisponible('anular', item)">
                                                    <i class="fas fa-times me-1"></i> Anular
                                                    <small v-if="Number(item.estado_plan) === 5" class="d-block fst-italic accion-aclaracion">Pendiente de aprobación</small>
                                                </a>
                                            </li>
                                            <li v-else-if="Number(item.estado_plan) === 0">
                                                <a href="#" class="dropdown-item text-success" @click.prevent="$emit('activar', item)">
                                                    <i class="fas fa-check me-1"></i> Activar
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="dropdown-item text-info" @click.prevent="$emit('ver-detalle', item)">
                                                    <i class="fas fa-eye me-1"></i> Ver detalle
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="dropdown-item"
                                                    :class="Number(item.estado_plan) === 5 ? 'text-muted disabled' : 'text-success'"
                                                    @click.prevent="emitirSiDisponible('generar-contrato', item)">
                                                    <i class="fas fa-file-contract me-1"></i> Generar contrato
                                                </a>
                                            </li>
                                            <template v-if="Number(item.estado_plan) === 1 || Number(item.estado_plan) === 5">
                                                <li>
                                                    <a href="#" class="dropdown-item"
                                                        :class="Number(item.estado_plan) === 5 ? 'text-muted disabled' : 'text-dark'"
                                                        @click.prevent="emitirReprogramacion(item, 'REPROGRAMACION')">
                                                        <i class="fas fa-clock me-1"></i> Reprogramar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="dropdown-item fw-bold"
                                                        :class="Number(item.estado_plan) === 5 ? 'text-muted disabled' : 'text-success'"
                                                        @click.prevent="emitirReprogramacion(item, 'REFINANCIAMIENTO')">
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
                                    <p class="small mb-0">Intenta ajustar los filtros de búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2';

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
        };
    },
    computed: {
        fechaInvalida() {
            return Boolean(this.filtros.fecha_inicio && this.filtros.fecha_fin && this.filtros.fecha_inicio > this.filtros.fecha_fin);
        }
    },
    mounted() {
        this.emitirBusqueda();
    },
    methods: {
        emitirBusqueda() {
            if (this.fechaInvalida) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validación de fechas',
                    text: 'La fecha inicial no puede ser mayor a la fecha final.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            this.$emit('filtrar', { ...this.filtros });
        },
        obtenerCodeudores(solicitudId) {
            return this.codeudoresGlobal.filter(codeudor => codeudor.id_solicitud === solicitudId);
        },
        formatearFecha(fecha) {
            return fecha ? moment(fecha).format('DD/MM/YYYY') : '-';
        },
        formatMoneda(valor) {
            const numero = Number(valor);
            if (!Number.isFinite(numero)) return '-';
            return numero.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        estadoTexto(item) {
            const estado = Number(item.estado_plan);
            if (estado === 0) return 'Anulado';
            if (estado === 2) return 'Completado';
            if (estado === 10) return 'Amortizado';
            if (estado === 5) return 'En proceso';
            if (estado === 1 && item.fecha_fin_plan < this.fechaActual) return 'Vencido';
            if (estado === 1) return 'Vigente';
            return 'Sin estado';
        },
        estadoBadgeClass(item) {
            const estado = Number(item.estado_plan);
            if (estado === 0) return 'bg-dark text-white';
            if (estado === 2) return 'bg-success text-white';
            if (estado === 10) return 'bg-info text-white';
            if (estado === 5) return 'bg-warning text-dark';
            if (estado === 1 && item.fecha_fin_plan < this.fechaActual) return 'bg-danger text-white';
            if (estado === 1) return 'bg-success text-white';
            return 'bg-secondary text-white';
        },
        emitirSiDisponible(evento, item) {
            if (Number(item.estado_plan) !== 5) this.$emit(evento, item);
        },
        emitirReprogramacion(item, tipo) {
            if (Number(item.estado_plan) !== 5) this.$emit('reprogramar', item, tipo);
        }
    }
};
</script>

<style scoped>
@import '../../components/styles/frmPlanPago.css';

.fecha-error { font-size: 0.7rem; }
.filtros-cartera .form-select,
.filtros-cartera .form-control { min-width: 115px; }
.filtros-cartera .campo-busqueda { min-width: 135px; }

.table-container-custom {
    min-height: 250px;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    background-color: #fff;
}
.table-container-custom:has(.dropdown-menu.show) { padding-bottom: 250px; }
.table-container-custom .dropdown-menu {
    min-width: 205px;
    max-height: 285px;
    overflow-y: auto;
}
.table-compact-custom {
    min-width: 1180px;
    margin-bottom: 0;
    font-size: 0.82rem;
}
.table-compact-custom th {
    padding: 6px 8px;
    border-bottom: 2px solid #157347 !important;
    background-color: #198754 !important;
    color: #fff !important;
    font-size: 0.72rem !important;
    font-weight: 700;
    letter-spacing: 0.35px;
    white-space: nowrap;
}
.table-compact-custom td {
    padding: 6px 8px !important;
    vertical-align: middle;
}
.font-monospace {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.8rem;
}
.cliente-cell,
.asesor-cell { max-width: 185px; }
.cliente-nombre,
.asesor-cell {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cliente-nombre { font-size: 0.84rem; }
.cliente-ci,
.texto-secundario,
.codeudor-item { font-size: 0.71rem; }
.codeudor-item { max-width: 155px; }
.codeudor-list {
    max-height: 48px;
    overflow-y: auto;
}
.codeudor-list::-webkit-scrollbar { width: 3px; }
.codeudor-list::-webkit-scrollbar-thumb {
    border-radius: 2px;
    background: #ccc;
}
.fecha-cell {
    font-size: 0.77rem;
    white-space: nowrap;
}
.plazo-badge {
    padding: 4px 7px;
    font-size: 0.72rem;
    font-weight: 500;
    text-transform: none;
    white-space: nowrap;
}
.badge-custom {
    display: block;
    width: 92px;
    padding: 3px 5px;
    font-size: 0.61rem;
    letter-spacing: 0.25px;
    text-transform: uppercase;
}
.accion-aclaracion {
    padding-left: 1.3rem;
    font-size: 0.64rem;
}
@media (max-width: 991.98px) {
    .filtros-cartera { flex-wrap: wrap; }
    .filtros-cartera > .form-select,
    .filtros-cartera > .form-control { flex: 1 1 145px; }
}
</style>
