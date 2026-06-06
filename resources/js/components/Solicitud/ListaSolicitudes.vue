<template>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning py-1">
            <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                Gestión de Solicitudes
            </h5>
        </div>
        <div class="card-body py-2">
            <div class="row g-2 mb-3 mt-1 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-secondary"><i class="far fa-calendar-alt"></i></span>
                        <input v-model="filtrosLocales.fecha_inicial" type="date" class="form-control"
                            @change="emitirBusqueda" />
                        <input v-model="filtrosLocales.fecha_final" type="date" class="form-control"
                            @change="emitirBusqueda" />
                    </div>
                    <small v-if="fechaInvalida" class="text-danger fw-bold d-block mt-1" style="font-size: 0.7rem;">
                        <i class="fas fa-exclamation-circle me-1"></i> Inicial > Final
                    </small>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-secondary" style="font-size: 0.75rem;">Estado</span>
                        <select v-model="filtrosLocales.estado" class="form-select" @change="emitirBusqueda">
                            <option value="todos">Todos</option>
                            <option value="1">Nuevo</option>
                            <option value="2">Aprobados</option>
                            <option value="0">Anulados</option>
                        </select>
                        <select v-model="filtrosLocales.criterio" class="form-select">
                            <option value="cliente.nombre">Nombre cliente</option>
                            <option value="cliente.ci">CI cliente</option>
                        </select>
                        <input v-model="filtrosLocales.buscar" type="text" class="form-control" placeholder="Buscar..."
                            @input="emitirBusqueda" />
                        <button class="btn btn-success" @click="emitirBusqueda">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <button @click="$emit('nueva-solicitud')" class="btn btn-success btn-sm me-1" data-bs-toggle="tooltip"
                        title="Crear nueva solicitud">
                        <i class="fas fa-plus me-1"></i> Nuevo
                    </button>
                    <button @click="$emit('abrir-calculadora')" class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                        title="Abrir calculadora de crédito">
                        <i class="fas fa-calculator me-1"></i> Calculadora
                    </button>
                </div>
            </div>

            <div class="table-container-custom table-responsive">
                <table class="table table-striped table-hover table-compact-custom align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Cliente</th>
                            <th>Gar./Cod.</th>
                            <th>Asesor</th>
                            <th class="text-end">Importe</th>
                            <th class="text-center">Plazo</th>
                            <th class="text-center">Tasa</th>
                            <th class="text-center">F. Reg.</th>
                            <th class="text-center">F. Des.</th>
                            <th>Tipo</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Op.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="solicitudes.length > 0">
                            <tr v-for="item in solicitudes" :key="item.id">
                                <td class="text-center font-monospace py-1 fw-semibold text-secondary">{{ item.id }}</td>
                                <td class="text-uppercase py-1">
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">{{ item.cliente }}</span>
                                        <span class="text-muted small" style="font-size: 0.72rem;"><strong class="fw-semibold">CI:</strong> {{ item.ci }}</span>
                                    </div>
                                </td>
                                <td class="text-uppercase py-1">
                                    <div v-if="obtenerCodeudores(item.id).length > 0" class="codeudor-list">
                                        <div v-for="codeudor in obtenerCodeudores(item.id)" :key="codeudor.id"
                                            class="text-truncate text-secondary" style="font-size: 0.72rem; max-width: 150px;" :title="codeudor.nombre">
                                            <i class="fas fa-user-friends me-1 text-muted"></i>{{ codeudor.nombre }}
                                        </div>
                                    </div>
                                    <div v-else class="text-muted fst-italic" style="font-size: 0.72rem;">
                                        Sin codeudores
                                    </div>
                                </td>
                                <td class="text-uppercase py-1 text-secondary" style="font-size: 0.8rem;">{{ item.personal }}</td>
                                <td class="text-end font-monospace py-1 fw-bold text-dark">{{ formatMoneda(item.importe_solicitud) }}</td>
                                <td class="text-center py-1">
                                    <span class="badge bg-light text-dark border border-secondary-subtle px-2 py-1 rounded" style="font-size: 0.75rem;">
                                        {{ item.nro_cuotas }} - {{ item.lapso_capital }}
                                    </span>
                                </td>
                                <td class="text-center py-1 font-monospace">{{ item.tasa }}%</td>
                                <td class="text-center py-1 text-secondary" style="font-size: 0.78rem;">{{ formatearFecha(item.fecha) }}</td>
                                <td class="text-center py-1 text-secondary" style="font-size: 0.78rem;">{{ formatearFecha(item.fecha_desembolso) }}</td>
                                <td class="text-uppercase py-1 text-truncate" style="max-width: 120px; font-size: 0.8rem;" :title="item.tipo_garantia">
                                    {{ item.tipo_garantia }}
                                </td>
    
                                <td class="text-uppercase text-center py-1">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        <span v-if="item.desembolso === 0 && item.estado === 2"
                                            class="badge bg-dark badge-custom text-white rounded">
                                            x desembolsar
                                        </span>
                                        <span class="badge badge-custom rounded"
                                            :class="getEstadoBadgeClass(item.estado, item.tipo_solicitud)">
                                            {{ getEstadoText(item.estado, item.tipo_solicitud) }}
                                        </span>
                                        <span v-if="item.observacion && item.observacion.trim() !== ''"
                                            class="badge bg-danger badge-custom text-white rounded"
                                            data-bs-toggle="tooltip" :title="item.observacion">
                                            OBSERVADO
                                        </span>
                                    </div>
                                </td>
    
                                <td class="text-uppercase text-center py-1">
                                    <div class="dropdown">
                                        <a class="text-success dropdown-toggle" style="cursor: pointer"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h fa-lg"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end shadow"
                                            style="position: absolute; z-index: 1000; min-width: 200px;">
                                            
                                            <li v-if="item.estado === 1" @click="$emit('anular', item)">
                                                <a class="dropdown-item text-danger" href="#">
                                                    <i class="fas fa-times me-1"></i> Anular
                                                </a>
                                            </li>
                                            <li v-else-if="item.estado === 0" @click="$emit('activar', item)">
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="fas fa-check me-1"></i> Activar
                                                </a>
                                            </li>
    
                                            <li v-if="item.estado === 1" @click="$emit('editar', item)">
                                                <a class="dropdown-item text-primary" href="#">
                                                    <i class="fas fa-pencil-alt me-1"></i> Editar
                                                </a>
                                            </li>
                                            <li @click="$emit('ver', item)">
                                                <a class="dropdown-item text-info" href="#">
                                                    <i class="fas fa-eye me-1"></i> Ver
                                                </a>
                                            </li>
    
                                            <li v-if="esAprobable(item)" @click="$emit('aprobar-solicitud', item)">
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="fas fa-money-bill me-1"></i> Aprobar solicitud
                                                </a>
                                            </li>
                                            <li v-if="esReprogramable(item)" @click="$emit('aprobar-reprogramacion', item)">
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="fas fa-money-bill me-1"></i> Aprobar Reprogramación
                                                </a>
                                            </li>
                                            <li v-if="esRefinanciable(item)" @click="$emit('aprobar-refinanciamiento', item)">
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="fas fa-money-bill me-1"></i> Aprobar Refinanciamiento
                                                </a>
                                            </li>
    
                                            <li v-if="tieneGarantias(item)" @click="$emit('ver-garantias', item.id)">
                                                <a class="dropdown-item text-secondary" href="#">
                                                    <i class="fas fa-images me-1"></i> Garantías
                                                </a>
                                            </li>
                                            <li @click="$emit('ver-respaldos', item.id)">
                                                <a class="dropdown-item text-secondary" href="#">
                                                    <i class="fas fa-folder-open me-1"></i> Respaldos de Verificación
                                                </a>
                                            </li>
                                            <li v-if="item.estado === 2" @click="$emit('ver-hoja-aprobacion', item)">
                                                <a class="dropdown-item text-dark" href="#">
                                                    <i class="fas fa-check-circle me-1"></i> Ver Hoja Aprobacion
                                                </a>
                                            </li>
                                            <li v-if="item.estado !== 2" @click="$emit('ver-hoja-solicitud', item)">
                                                <a class="dropdown-item text-dark" href="#">
                                                    <i class="fas fa-file-alt me-1"></i> Ver Hoja Solicitud
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="12" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                                    <h5 class="fw-normal">No se encontraron solicitudes</h5>
                                    <p class="small">Intenta ajustar los filtros de búsqueda o registra una nueva solicitud.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="pagination.last_page > 1" class="mt-3">
                <ul class="pagination pagination-sm justify-content-end mb-1">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <a class="page-link" href="#" @click.prevent="$emit('cambiar-pagina', pagination.current_page - 1)">Anterior</a>
                    </li>
                    <li v-for="page in pagesNumber" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
                        <a class="page-link" href="#" @click.prevent="$emit('cambiar-pagina', page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="$emit('cambiar-pagina', pagination.current_page + 1)">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import Swal from 'sweetalert2';

export default {
    props: {
        solicitudes: { type: Array, required: true },
        pagination: { type: Object, required: true },
        codeudoresTabla: { type: Array, default: () => [] },
        rolUsuario: { type: String, required: true },
        // Pasamos valores iniciales de filtros si se desea
        filtrosIniciales: { type: Object, required: true } 
    },
    data() {
        return {
            offset: 2,
            filtrosLocales: {
                fecha_inicial: '',
                fecha_final: '',
                estado: 'todos',
                criterio: 'cliente.nombre',
                buscar: ''
            }
        };
    },
    created() {
        // Inicializar filtros locales con props
        this.filtrosLocales = { ...this.filtrosIniciales };
    },
    computed: {
        pagesNumber() {
            if (!this.pagination.to) return [];
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + this.offset * 2;
            if (to >= this.pagination.last_page) to = this.pagination.last_page;
            const pagesArray = [];
            while (from <= to) {
                pagesArray.push(from++);
            }
            return pagesArray;
        },
        fechaInvalida() {
            if (this.filtrosLocales.fecha_inicial && this.filtrosLocales.fecha_final) {
                return this.filtrosLocales.fecha_inicial > this.filtrosLocales.fecha_final;
            }
            return false;
        }
    },
    methods: {
        emitirBusqueda() {
            // Validación de fechas
            if (this.filtrosLocales.fecha_inicial && this.filtrosLocales.fecha_final) {
                if (this.filtrosLocales.fecha_inicial > this.filtrosLocales.fecha_final) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación de Fechas',
                        text: 'La fecha inicial no puede ser mayor a la fecha final.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }
            }
            this.$emit('filtrar', this.filtrosLocales);
        },
        obtenerCodeudores(solicitudId) {
            return this.codeudoresTabla.filter(
                (codeudor) => codeudor.id_solicitud === solicitudId
            );
        },
        formatearFecha(fechaISO) {
            return fechaISO ? moment(fechaISO).format("DD/MM/YYYY") : "-";
        },
        formatMoneda(val) {
            if (val === undefined || val === null) return "-";
            const num = parseFloat(val);
            if (isNaN(num)) return val;
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        // --- Lógica de Permisos / Visualización en botones ---
        esAprobable(item) {
            return item.estado !== 0 &&
                   item.estado !== 2 &&
                   item.tipo_solicitud !== 'Reprogramacion' &&
                   item.tipo_solicitud !== 'Refinanciamiento' &&
                   this.rolUsuario === 'Administrador' &&
                   (!item.observacion || item.observacion.trim() === '');
        },
        esReprogramable(item) {
            return item.estado == 1 &&
                   item.tipo_solicitud == 'Reprogramacion' &&
                   this.rolUsuario === 'Administrador' &&
                   (!item.observacion || item.observacion.trim() === '');
        },
        esRefinanciable(item) {
            return item.estado == 1 &&
                   item.tipo_solicitud == 'Refinanciamiento' &&
                   this.rolUsuario === 'Administrador' &&
                   (!item.observacion || item.observacion.trim() === '');
        },
        tieneGarantias(item) {
            const tiposConGarantia = [
                'Empeño de electrodoméstico u Otros', 'Custodia de Papeles de Moto',
                'Prendario o Quirografaria', 'Empeño Joyas (oro)',
                'Custodia Inmueble o Lote terreno', 'Custodia de Vehículo Automovil', ''
            ];
            return tiposConGarantia.includes(item.tipo_garantia);
        },
        // --- Clases y Textos de Estado ---
        getEstadoText(estado, tipo_solicitud) {
            if (estado == 1) {
                if (tipo_solicitud == 'Nuevo') return "Nuevo";
                if (tipo_solicitud == 'Reprogramacion') return "Reprogramación";
                if (tipo_solicitud == 'Refinanciamiento') return "Refinanciación";
            } else if (estado == 2) {
                if (tipo_solicitud == 'Nuevo') return "Normal";
                if (tipo_solicitud == 'Reprogramacion') return "Reprogramado";
                if (tipo_solicitud == 'Refinanciamiento') return "Refinanciada";
            } else if (estado == 0) {
                return "Anulado";
            } else {
                return "Desconocido";
            }
        },
        getEstadoBadgeClass(estado, tipo_solicitud) {
            if (estado == 1) {
                if (tipo_solicitud == 'Nuevo') return "bg-info text-white";
                if (tipo_solicitud == 'Reprogramacion' || tipo_solicitud == 'Refinanciamiento') return "bg-warning text-dark";
                return "bg-info text-white";
            } else if (estado == 2) {
                return "bg-success text-white";
            } else if (estado == 0) {
                return "bg-danger text-white";
            } else {
                return "bg-secondary text-white";
            }
        }
    }
};
</script>

<style scoped>
@import './../styles/frmSolicitud.css';

.table-container-custom {
    min-height: 250px;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    background-color: white;
}

.table-compact-custom {
    font-size: 0.82rem;
    margin-bottom: 0;
}

.table-compact-custom th {
    background-color: #198754 !important; /* Solid premium success green */
    color: white !important;
    border-bottom: 2px solid #157347 !important;
    padding: 6px 8px;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    font-weight: 700;
}

.table-compact-custom td {
    padding: 6px 8px !important;
    vertical-align: middle;
}

.font-monospace {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.8rem;
}

.badge-custom {
    width: 100px;
    font-size: 0.7rem;
    padding: 4px 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: block;
}

.codeudor-list {
    max-height: 48px;
    overflow-y: auto;
}

/* Custom fine scrollbar for codeudor list */
.codeudor-list::-webkit-scrollbar {
    width: 3px;
}
.codeudor-list::-webkit-scrollbar-track {
    background: transparent;
}
.codeudor-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 2px;
}
</style>
