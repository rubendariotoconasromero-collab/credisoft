<template>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning py-1">
            <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                Gestión de Solicitudes
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4 mt-1">
                <div class="col-md-4">
                    <div class="input-group">
                        <input v-model="filtrosLocales.fecha_inicial" type="date" class="form-control"
                            @change="emitirBusqueda" />
                        <input v-model="filtrosLocales.fecha_final" type="date" class="form-control"
                            @change="emitirBusqueda" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
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
                    <button @click="$emit('nueva-solicitud')" class="btn btn-success" data-bs-toggle="tooltip"
                        title="Crear nueva solicitud">
                        <i class="fas fa-plus me-1"></i> Nuevo
                    </button>
                    <button @click="$emit('abrir-calculadora')" class="btn btn-info px-3 ms-2" data-bs-toggle="tooltip"
                        title="Abrir calculadora de crédito">
                        <i class="fas fa-calculator me-1"></i> Calculadora
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-success">
                        <tr>
                            <th class="text-dark text-uppercase fw-bold">#</th>
                            <th class="text-dark text-uppercase fw-bold">Cliente</th>
                            <th class="text-dark text-uppercase fw-bold">Gar./Cod.</th>
                            <th class="text-dark text-uppercase fw-bold">Asesor</th>
                            <th class="text-dark text-uppercase fw-bold">Importe</th>
                            <th class="text-dark text-uppercase fw-bold text-center">Plazo</th>
                            <th class="text-dark text-uppercase fw-bold">Tasa</th>
                            <th class="text-dark text-uppercase fw-bold">F. Reg.</th>
                            <th class="text-dark text-uppercase fw-bold">F. Des.</th>
                            <th class="text-dark text-uppercase fw-bold">Tipo</th>
                            <th class="text-dark text-uppercase fw-bold">Estado</th>
                            <th class="text-dark text-uppercase fw-bold">Op.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in solicitudes" :key="item.id">
                            <td class="text-uppercase">{{ item.id }}</td>
                            <td class="text-uppercase fw-bold">
                                <span class="d-block">
                                    <strong class="fw-bold">CI: </strong>
                                    {{ item.ci }}
                                </span>
                                {{ item.cliente }}
                            </td>
                            <td class="text-uppercase">
                                <div v-if="obtenerCodeudores(item.id).length > 0">
                                    <p v-for="codeudor in obtenerCodeudores(item.id)" :key="codeudor.id"
                                        class="mb-1 text-truncate">
                                        <small class="text-muted">* </small>{{ codeudor.nombre }}
                                    </p>
                                </div>
                                <div v-else class="text-center text-muted fst-italic">
                                    Sin codeudores
                                </div>
                            </td>
                            <td class="text-uppercase fw-bold">{{ item.personal }}</td>
                            <td class="text-uppercase">{{ item.importe_solicitud }}</td>
                            <td>
                                <span
                                    class="badge text-dark rounded text-uppercase border border-secondary bg-white d-inline-block w-100 text-center pt-1"
                                    style="max-width: 150px;font-size: 10px;">
                                    {{ item.nro_cuotas }} - {{ item.lapso_capital }}
                                </span>
                            </td>
                            <td class="text-uppercase">{{ item.tasa }}</td>
                            <td class="text-uppercase">{{ formatearFecha(item.fecha) }}</td>
                            <td class="text-uppercase">{{ formatearFecha(item.fecha_desembolso) }}</td>
                            <td class="text-uppercase">{{ item.tipo_garantia }}</td>

                            <td class="text-uppercase text-center">
                                <span v-if="item.desembolso === 0 && item.estado === 2" style="width:110px;"
                                    class="badge bg-dark badge-fixed-width text-white d-block rounded">
                                    x desembolsar
                                </span>
                                <span style="width:110px;" class="rounded"
                                    :class="getEstadoClass(item.estado, item.tipo_solicitud)">
                                    {{ getEstadoText(item.estado, item.tipo_solicitud) }}
                                </span>

                                <span v-if="item.observacion && item.observacion.trim() !== ''"
                                    class="mt-1 badge bg-danger badge-fixed-width text-white d-block rounded"
                                    style="width:110px;">
                                    OBSERVADO
                                </span>
                            </td>

                            <td class="text-uppercase position-relative text-center">
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
                    </tbody>
                </table>
                <template v-if="solicitudes.length < 15">
                    <br><br><br><br><br><br><br><br><br><br>
                </template>
            </div>

            <nav v-if="pagination.last_page > 1" class="mt-4">
                <ul class="pagination justify-content-end">
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
        }
    },
    methods: {
        emitirBusqueda() {
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
        // --- Lógica de Permisos / Visualización en botones ---
        esAprobable(item) {
            return item.estado !== 0 &&
                   item.estado !== 2 &&
                   item.tipo_solicitud !== 'Reprogramacion' &&
                   item.tipo_solicitud !== 'Refinanciamiento' &&
                   this.rolUsuario === 'administrador' &&
                   (!item.observacion || item.observacion.trim() === '');
        },
        esReprogramable(item) {
            return item.estado == 1 &&
                   item.tipo_solicitud == 'Reprogramacion' &&
                   this.rolUsuario === 'administrador' &&
                   (!item.observacion || item.observacion.trim() === '');
        },
        esRefinanciable(item) {
            return item.estado == 1 &&
                   item.tipo_solicitud == 'Refinanciamiento' &&
                   this.rolUsuario === 'administrador' &&
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
        getEstadoClass(estado, tipo_solicitud) {
            if (estado == 1) {
                if (tipo_solicitud == 'Nuevo') return "badge bg-info badge-fixed-width d-block mt-1";
                if (tipo_solicitud == 'Reprogramacion' || tipo_solicitud == 'Refinanciamiento') return "badge bg-warning badge-fixed-width d-block mt-1 text-dark";
                return "badge bg-info badge-fixed-width d-block mt-1";
            } else if (estado == 2) {
                return "badge bg-success badge-fixed-width d-block mt-1";
            } else if (estado == 0) {
                return "badge bg-danger badge-fixed-width d-block mt-1";
            } else {
                return "badge bg-secondary badge-fixed-width d-block mt-1";
            }
        }
    }
};
</script>

<style scoped>
@import './../styles/frmSolicitud.css';
.badge-fixed-width {
    width: 110px;
}
</style>
