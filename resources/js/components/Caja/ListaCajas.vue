<template>
    <div class="card shadow-sm">
        <div class="card-header bg-warning py-2">
            <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                Control de caja
            </h5>
        </div>
        <div class="card-body">

            <div class="row mb-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <select v-model="filtros.criterio" class="form-select" @change="emitirBusqueda">
                            <option value="users.name">Usuario</option>
                            <option value="fecha">Fecha</option>
                        </select>

                        <input v-if="filtros.criterio != 'fecha'" 
                               placeholder="Buscar..." 
                               v-model="filtros.buscar"
                               type="text" class="form-control" 
                               @input="emitirBusqueda">

                        <template v-if="filtros.criterio == 'fecha'">
                            <input v-model="filtros.fecha_inicio" type="date" class="form-control" @change="emitirBusqueda">
                            <input v-model="filtros.fecha_final" type="date" class="form-control" @change="emitirBusqueda">
                        </template>

                        <button class="btn btn-info" type="button" @click="emitirBusqueda">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-7 d-flex justify-content-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-end text-end">
                        <button @click="$emit('ver-desembolsos')" class="btn btn-outline-info position-relative">
                            <i class="fas fa-dollar-sign me-1"></i> Desembolsos
                            <span v-if="conteoPendientes > 0" class="badge bg-danger rounded position-absolute top-0 end-0 translate-middle p-1">
                                {{ conteoPendientes }} Pendientes
                            </span>
                        </button>

                        <button class="btn btn-outline-success position-relative" @click="$emit('gestionar-pagos')">
                            <i class="fas fa-check-circle me-1"></i> Pagos
                        </button>

                        <button @click="$emit('abrir-ingreso')" class="btn btn-success text-white">
                            <i class="fas fa-dollar-sign me-1"></i> Ingreso
                        </button>

                        <button @click="$emit('abrir-gasto')" class="btn btn-danger text-white">
                            <i class="fas fa-receipt me-1"></i> Egreso
                        </button>

                        <button @click="$emit('abrir-apertura')" class="btn btn-info">
                            <i class="fas fa-money-bill me-1"></i> Abrir Caja
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="font-size:12px">
                <table class="table mb-4 table-hover table-striped table-sm">
                    <thead>
                        <tr class="text-white text-uppercase table-warning">
                            <th class="text-dark fw-bold">#</th>
                            <th class="text-dark fw-bold">Codigo</th>
                            <th class="text-dark fw-bold">Usuario</th>
                            <th class="text-dark fw-bold">Apertura</th>
                            <th class="text-dark fw-bold">Cierre</th>
                            <th class="text-dark fw-bold text-end">$ Inicio</th>
                            <th class="text-dark fw-bold text-end">$ Cierre</th>
                            <th class="text-dark fw-bold text-end">Ingreso T.</th>
                            <th class="text-dark fw-bold text-end">Egreso T.</th>
                            <th class="text-dark fw-bold text-end">Diferencia</th>
                            <th class="text-dark fw-bold text-center">Estado</th>
                            <th class="text-dark fw-bold text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in cajas" :key="item.id">
                            <td class="text-uppercase">{{ index + 1 }}</td>
                            <td class="text-uppercase">{{ item.id }}</td>
                            <td class="text-uppercase">{{ item.usuario }}</td>
                            <td class="text-uppercase">{{ item.fechahora_apertura }}</td>
                            <td class="text-uppercase text-center">
                                {{ item.fechahora_cierre || '..........' }}
                            </td>
                            
                            <td class="text-uppercase text-end">{{ formatMoney(item.monto_inicial) }}</td>
                            <td class="text-uppercase text-end fw-bold">{{ calcularCierre(item) }}</td>
                            <td class="text-uppercase text-end text-success">{{ calcularIngresoTotal(item) }}</td>
                            <td class="text-uppercase text-end text-danger">{{ formatMoney(item.egreso_total) }}</td>
                            <td class="text-uppercase text-end fw-bold" 
                                :class="calcularDiferencia(item) >= 0 ? 'text-success' : 'text-danger'">
                                {{ calcularDiferencia(item) }}
                            </td>

                            <td class="text-uppercase text-center">
                                <span v-if="item.estado == 1" class="badge text-bg-success">Abierta</span>
                                <span v-else class="badge text-bg-danger">Cerrada</span>
                            </td>
                            <td class="text-uppercase text-center">
                                <div class="btn-group my-0 py-0">
                                    <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm my-0 py-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h fa-lg fa-fw fs-4"></i>
                                    </a>
                                    <ul class="dropdown-menu my-0 py-0">
                                        <li>
                                            <a @click="$emit('ver-detalle', item)" class="dropdown-item text-info" href="#">
                                                <i class="fas fa-eye"></i> Ver Más
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="cajas.length === 0">
                            <td colspan="12" class="text-center py-4 text-muted">No se encontraron registros de caja.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer py-4">
                <nav v-if="pagination.last_page > 1">
                    <ul class="pagination justify-content-end mb-0">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                        </li>
                        <li class="page-item" v-for="page in pagesNumber" :key="page"
                            :class="{ active: page === pagination.current_page }">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
    </div>
</template>

<script>
import moment from "moment";

export default {
    props: {
        cajas: { type: Array, required: true },
        pagination: { type: Object, required: true },
        conteoPendientes: { type: Number, default: 0 }
    },
    data() {
        return {
            filtros: {
                criterio: 'users.name',
                buscar: '',
                fecha_inicio: moment().format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD')
            },
            offset: 2
        }
    },
    computed: {
        pagesNumber() {
            if (!this.pagination.to) return [];
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
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
            this.$emit('filtrar', this.filtros);
        },
        cambiarPagina(page) {
            this.$emit('cambiar-pagina', page);
        },
        
        // --- Helpers de Formato y Cálculo (Extracción de lógica del template) ---
        formatMoney(value) {
            const val = parseFloat(value || 0);
            return isNaN(val) ? '0.00' : val.toFixed(2);
        },
        
        calcularIngresoTotal(item) {
            const total = parseFloat(item.ingreso_total || 0) +
                          parseFloat(item.pago_administrativo_total || 0) +
                          parseFloat(item.ingreso_total_ingreso || 0) +
                          parseFloat(item.ingreso_total_amortizacion || 0);
            return this.formatMoney(total);
        },

        calcularCierre(item) {
            const ingresos = parseFloat(this.calcularIngresoTotal(item));
            const egresos = parseFloat(item.egreso_total || 0);
            const inicial = parseFloat(item.monto_inicial || 0);
            return this.formatMoney(ingresos - egresos + inicial);
        },

        calcularDiferencia(item) {
            const ingresos = parseFloat(this.calcularIngresoTotal(item));
            const egresos = parseFloat(item.egreso_total || 0);
            return this.formatMoney(ingresos - egresos);
        }
    }
};
</script>

<style scoped>
    @import '../styles/frmCaja.css';
</style>
