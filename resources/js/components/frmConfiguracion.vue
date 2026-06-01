<template>
    <main class="config-management">

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Configuración de Datos
                        </h5>
                    </div>

                    <div class="card-body pt-3">
                        
                        <ul class="nav nav-pills custom-tabs mb-0 d-flex justify-content-center" role="tablist">
                            <li class="nav-item mx-1" role="presentation">
                                <button @click="cambiarVista(0)" class="nav-link px-4 fw-bold text-uppercase" :class="{'active text-success border-success-tab': vista === 0}" type="button">
                                    Motivos de Ingresos
                                </button>
                            </li>
                            <li class="nav-item mx-1" role="presentation">
                                <button @click="cambiarVista(1)" class="nav-link px-4 fw-bold text-uppercase" :class="{'active text-danger border-danger-tab': vista === 1}" type="button">
                                    Motivos de Egresos
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content bg-white p-3 border rounded-bottom shadow-sm">
                            
                            <div v-if="vista === 0" class="fade-in-animation">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-success">
                                    <!-- Filtro de Tipo (Caja / Bóveda) -->
                                    <div style="width: 290px;">
                                        <div class="input-group input-group-sm shadow-sm border bg-white rounded-0" style="overflow: hidden;">
                                            <span class="input-group-text bg-light text-muted border-0 fw-bold rounded-0" style="font-size: 10px; letter-spacing: 0.5px;">FILTRAR POR</span>
                                            <select v-model="filtroIngresoTipo" class="form-select border-0 py-1 rounded-0" @change="obtenerMotivosIngreso(1)" style="font-size: 0.8rem; cursor: pointer;">
                                                <option value="todos">Todos</option>
                                                <option value="caja">Caja</option>
                                                <option value="boveda">Bóveda</option>
                                            </select>
                                        </div>
                                    </div>

                                    <button @click="nuevoMotivoIngreso()" class="btn btn-success btn-sm fw-bold shadow-sm rounded-0">
                                        <i class="fas fa-plus-circle me-1"></i> Nuevo Motivo
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-success text-center align-middle">
                                            <tr>
                                                <th class="text-uppercase fw-bold" width="50%">Motivo Ingreso</th>
                                                <th class="text-uppercase fw-bold" width="15%">Aplica en</th>
                                                <th class="text-uppercase fw-bold" width="15%">Estado</th>
                                                <th class="text-uppercase fw-bold" width="20%">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="preloader">
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <span class="spinner-border spinner-border text-success mb-2" role="status" style="width: 2rem; height: 2rem;"></span>
                                                    <div class="small fw-semibold">Cargando datos...</div>
                                                </td>
                                            </tr>
                                            <tr v-else v-for="item in motivos_ingreso" :key="item.id">
                                                <td class="text-uppercase fw-semibold text-dark ps-2" style="font-size: 0.75rem;">{{ item.nombre }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.tipo === 'boveda'" class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Bóveda
                                                    </span>
                                                    <span v-else class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Caja
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="item.estado === 0" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Activo
                                                    </span>
                                                    <span v-else class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Inactivo
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group my-0 py-0">
                                                        <button class="btn btn-light btn-sm text-secondary border shadow-sm py-1 px-2 rounded-0"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v fa-sm"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" style="font-size: 0.8rem; border-radius: 8px;">
                                                            <li v-if="item.estado === 0" @click="desactivarMotivoIngreso(item)">
                                                                <a class="dropdown-item text-danger py-1" href="#">
                                                                    <i class="fas fa-times-circle me-1"></i> Desactivar
                                                                </a>
                                                            </li>
                                                            <li v-else @click="activarMotivoIngreso(item)">
                                                                <a class="dropdown-item text-success py-1" href="#">
                                                                    <i class="fas fa-check me-1"></i> Activar
                                                                </a>
                                                            </li>
                                                            <li @click="editarMotivoIngreso(item)">
                                                                <a class="dropdown-item text-primary py-1" href="#">
                                                                    <i class="fas fa-pencil-alt me-1"></i> Editar
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="!preloader && motivos_ingreso.length === 0">
                                                <td colspan="4" class="text-center py-4 text-muted fst-italic">No hay motivos de ingreso registrados.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Paginación Motivos de Ingreso -->
                                <div class="d-flex justify-content-between align-items-center mt-3" v-if="paginacionIngreso.last_page > 1">
                                    <span class="text-muted small">
                                        Página {{ paginacionIngreso.current_page }} de {{ paginacionIngreso.last_page }}
                                        ({{ paginacionIngreso.total }} registros)
                                    </span>
                                    <nav>
                                        <ul class="pagination shadow-sm mb-0">
                                            <li class="page-item" :class="{disabled: paginacionIngreso.current_page <= 1}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaIngreso(paginacionIngreso.current_page - 1)">Anterior</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberIngreso" :key="page"
                                                :class="{active: page == paginacionIngreso.current_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaIngreso(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" :class="{disabled: paginacionIngreso.current_page >= paginacionIngreso.last_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaIngreso(paginacionIngreso.current_page + 1)">Siguiente</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <div v-if="vista === 1" class="fade-in-animation">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-danger">
                                    <!-- Filtro de Tipo (Caja / Bóveda) -->
                                    <div style="width: 290px;">
                                        <div class="input-group input-group-sm shadow-sm border bg-white rounded-0" style="overflow: hidden;">
                                            <span class="input-group-text bg-light text-muted border-0 fw-bold rounded-0" style="font-size: 10px; letter-spacing: 0.5px;">FILTRAR POR</span>
                                            <select v-model="filtroGastoTipo" class="form-select border-0 py-1 rounded-0" @change="obtenerMotivosGastos(1)" style="font-size: 0.8rem; cursor: pointer;">
                                                <option value="todos">Todos</option>
                                                <option value="caja">Caja</option>
                                                <option value="boveda">Bóveda</option>
                                            </select>
                                        </div>
                                    </div>

                                    <button @click="nuevoMotivoGasto()" class="btn btn-danger btn-sm fw-bold shadow-sm rounded-0">
                                        <i class="fas fa-plus-circle me-1"></i> Nuevo Motivo
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle ledger-table mb-0 table-striped">
                                        <thead class="table-danger text-center align-middle">
                                            <tr>
                                                <th class="text-uppercase fw-bold" width="50%">Motivo Egreso</th>
                                                <th class="text-uppercase fw-bold" width="15%">Aplica en</th>
                                                <th class="text-uppercase fw-bold" width="15%">Estado</th>
                                                <th class="text-uppercase fw-bold" width="20%">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="preloader">
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <span class="spinner-border spinner-border text-danger mb-2" role="status" style="width: 2rem; height: 2rem;"></span>
                                                    <div class="small fw-semibold">Cargando datos...</div>
                                                </td>
                                            </tr>
                                            <tr v-else v-for="item in motivos_gasto" :key="item.id">
                                                <td class="text-uppercase fw-semibold text-dark ps-2" style="font-size: 0.75rem;">{{ item.nombre }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.tipo === 'boveda'" class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Bóveda
                                                    </span>
                                                    <span v-else class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Caja
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="item.estado === 0" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Activo
                                                    </span>
                                                    <span v-else class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 shadow-sm" style="min-width: 80px; font-size: 0.7rem;">
                                                        Inactivo
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group my-0 py-0">
                                                        <button class="btn btn-light btn-sm text-secondary border shadow-sm py-1 px-2 rounded-0"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v fa-sm"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" style="font-size: 0.8rem; border-radius: 8px;">
                                                            <li v-if="item.estado === 0" @click="desactivarMotivoGasto(item)">
                                                                <a class="dropdown-item text-danger py-1" href="#">
                                                                    <i class="fas fa-times-circle me-1"></i> Desactivar
                                                                </a>
                                                            </li>
                                                            <li v-else @click="activarMotivoGasto(item)">
                                                                <a class="dropdown-item text-success py-1" href="#">
                                                                    <i class="fas fa-check me-1"></i> Activar
                                                                </a>
                                                            </li>
                                                            <li @click="editarMotivoGasto(item)">
                                                                <a class="dropdown-item text-primary py-1" href="#">
                                                                    <i class="fas fa-pencil-alt me-1"></i> Editar
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="!preloader && motivos_gasto.length === 0">
                                                <td colspan="4" class="text-center py-4 text-muted fst-italic">No hay motivos de egreso registrados.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Paginación Motivos de Egreso -->
                                <div class="d-flex justify-content-between align-items-center mt-3" v-if="paginacionGasto.last_page > 1">
                                    <span class="text-muted small">
                                        Página {{ paginacionGasto.current_page }} de {{ paginacionGasto.last_page }}
                                        ({{ paginacionGasto.total }} registros)
                                    </span>
                                    <nav>
                                        <ul class="pagination shadow-sm mb-0">
                                            <li class="page-item" :class="{disabled: paginacionGasto.current_page <= 1}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaGasto(paginacionGasto.current_page - 1)">Anterior</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagesNumberGasto" :key="page"
                                                :class="{active: page == paginacionGasto.current_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaGasto(page)">{{ page }}</a>
                                            </li>
                                            <li class="page-item" :class="{disabled: paginacionGasto.current_page >= paginacionGasto.last_page}">
                                                <a class="page-link" href="#" @click.prevent="cambiarPaginaGasto(paginacionGasto.current_page + 1)">Siguiente</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal fade" id="modalMotivoIngreso" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success bg-gradient py-2">
                        <h5 class="modal-title text-white fw-bold text-uppercase fs-6">
                            <i class="fas" :class="motivo_ingreso.accion == 1 ? 'fa-edit' : 'fa-plus-circle'"></i>
                            {{ motivo_ingreso.accion == 1 ? 'Editar' : 'Nuevo' }} Motivo de Ingreso
                        </h5>
                        <button @click="cancelarGuardarMotivoIngreso()" type="button" class="btn-close btn-close-white"></button>
                    </div>
                    <form @submit.prevent="motivo_ingreso.accion == 0 ? guardarMotivoIngreso() : modificarMotivoIngreso()">
                        <div class="modal-body p-3">
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1 text-muted small">Nombre del Motivo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-sm border-success" 
                                    v-model="motivo_ingreso.nombre" placeholder="Ej: Aporte de Capital..." required />
                            </div>
                            
                            <div class="form-group">
                                <label class="fw-bold mb-1 text-muted small">Este motivo aplica para: <span class="text-danger">*</span></label>
                                <select class="form-select shadow-sm border-success" v-model="motivo_ingreso.tipo" required>
                                    <option value="caja">Caja (Movimientos del Cajero)</option>
                                    <option value="boveda">Bóveda (Bóveda Principal)</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer bg-light py-2 border-top">
                            <button @click="cancelarGuardarMotivoIngreso()" type="button" class="btn btn-secondary btn-sm px-3">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-sm fw-bold px-3 shadow-sm" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <i v-else class="fas fa-save me-1"></i>
                                {{ motivo_ingreso.accion == 1 ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalMotivoGasto" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger bg-gradient py-2">
                        <h5 class="modal-title text-white fw-bold text-uppercase fs-6">
                            <i class="fas" :class="motivo_gasto.accion == 1 ? 'fa-edit' : 'fa-plus-circle'"></i>
                            {{ motivo_gasto.accion == 1 ? 'Editar' : 'Nuevo' }} Motivo de Egreso
                        </h5>
                        <button @click="cancelarGuardarMotivoGasto()" type="button" class="btn-close btn-close-white"></button>
                    </div>
                    <form @submit.prevent="motivo_gasto.accion == 0 ? guardarMotivoGasto() : modificarMotivoGasto()">
                        <div class="modal-body p-3">
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1 text-muted small">Nombre del Motivo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-sm border-danger" 
                                    v-model="motivo_gasto.nombre" placeholder="Ej: Pago de Servicios..." required />
                            </div>
                            
                            <div class="form-group">
                                <label class="fw-bold mb-1 text-muted small">Este motivo aplica para: <span class="text-danger">*</span></label>
                                <select class="form-select shadow-sm border-danger" v-model="motivo_gasto.tipo" required>
                                    <option value="caja">Caja (Movimientos del Cajero)</option>
                                    <option value="boveda">Bóveda (Bóveda Principal)</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer bg-light py-2 border-top">
                            <button @click="cancelarGuardarMotivoGasto()" type="button" class="btn btn-secondary btn-sm px-3">Cancelar</button>
                            <button type="submit" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <i v-else class="fas fa-save me-1"></i>
                                {{ motivo_gasto.accion == 1 ? 'Actualizar' : 'Guardar' }}
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
import axios from 'axios';
import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

export default {
    data() {
        return {
            preloader: false,
            loading: false, 
            vista: 0,
            motivos_ingreso: [],
            motivos_gasto: [],
            motivo_ingreso: { id: 0, nombre: '', tipo: 'caja', accion: 0 },
            motivo_gasto: { id: 0, nombre: '', tipo: 'caja', accion: 0 },

            // Filtros de Tipo
            filtroIngresoTipo: 'todos',
            filtroGastoTipo: 'todos',

            // Paginación
            paginacionIngreso: { current_page: 1, last_page: 1, total: 0 },
            paginacionGasto: { current_page: 1, last_page: 1, total: 0 },
            offset: 2,
        };
    },
    computed: {
        pagesNumberIngreso() {
            return this.buildPages(this.paginacionIngreso.current_page, this.paginacionIngreso.last_page);
        },
        pagesNumberGasto() {
            return this.buildPages(this.paginacionGasto.current_page, this.paginacionGasto.last_page);
        }
    },
    methods: {
        buildPages(current, lastPage) {
            let from = Math.max(1, current - this.offset);
            let to   = Math.min(lastPage, from + this.offset * 2);
            const pages = [];
            for (let i = from; i <= to; i++) pages.push(i);
            return pages;
        },
        cambiarPaginaIngreso(page) {
            if (page >= 1 && page <= this.paginacionIngreso.last_page) {
                this.obtenerMotivosIngreso(page);
            }
        },
        cambiarPaginaGasto(page) {
            if (page >= 1 && page <= this.paginacionGasto.last_page) {
                this.obtenerMotivosGastos(page);
            }
        },
        async cambiarVista(vista) {
            this.vista = vista;
        },

        // --- MOTIVOS INGRESO ---
        async obtenerMotivosIngreso(page = 1) {
            try {
                this.preloader = true;
                const response = await axios.get('/get_motivos_ingresos', {
                    params: { 
                        page,
                        tipo: this.filtroIngresoTipo
                    }
                });
                this.motivos_ingreso = response.data.data;
                this.paginacionIngreso = response.data;
            } catch (error) {
                console.error('Error al obtener motivos de ingreso', error);
            } finally {
                this.preloader = false;
            }
        },
        async nuevoMotivoIngreso() {
            this.motivo_ingreso = { id: 0, nombre: '', tipo: 'caja', accion: 0 };
            $('#modalMotivoIngreso').modal('show');
        },
        async editarMotivoIngreso(item) {
            this.motivo_ingreso = { id: item.id, nombre: item.nombre, tipo: item.tipo || 'caja', accion: 1 };
            $('#modalMotivoIngreso').modal('show');
        },
        async guardarMotivoIngreso() {
            try {
                this.loading = true;
                await axios.post('/guardar_motivo_ingreso', this.motivo_ingreso);
                Toast.fire({
                    icon: 'success',
                    title: 'Motivo de ingreso registrado exitosamente'
                });
                await this.obtenerMotivosIngreso(1);
                this.cancelarGuardarMotivoIngreso();
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo guardar el motivo'
                });
            } finally { this.loading = false; }
        },
        async modificarMotivoIngreso() {
            try {
                this.loading = true;
                await axios.post('/modificar_motivo_ingreso', this.motivo_ingreso);
                Toast.fire({
                    icon: 'success',
                    title: 'Motivo de ingreso modificado correctamente'
                });
                await this.obtenerMotivosIngreso(this.paginacionIngreso.current_page);
                this.cancelarGuardarMotivoIngreso();
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo modificar el motivo'
                });
            } finally { this.loading = false; }
        },
        async activarMotivoIngreso(item) {
            try {
                this.preloader = true;
                await axios.post('/activar_motivo_ingreso', { id: item.id });
                Toast.fire({
                    icon: 'success',
                    title: 'Motivo de ingreso activado'
                });
                await this.obtenerMotivosIngreso(this.paginacionIngreso.current_page);
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo activar el motivo'
                });
            } finally {
                this.preloader = false;
            }
        },
        async desactivarMotivoIngreso(item) {
            try {
                this.preloader = true;
                await axios.post('/desactivar_motivo_ingreso', { id: item.id });
                Toast.fire({
                    icon: 'warning',
                    title: 'Motivo de ingreso desactivado'
                });
                await this.obtenerMotivosIngreso(this.paginacionIngreso.current_page);
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo desactivar el motivo'
                });
            } finally {
                this.preloader = false;
            }
        },
        cancelarGuardarMotivoIngreso() {
            $('#modalMotivoIngreso').modal('hide');
            this.motivo_ingreso = { id: 0, nombre: '', tipo: 'caja', accion: 0 };
        },

        // --- MOTIVOS EGRESO ---
        async obtenerMotivosGastos(page = 1) {
            try {
                this.preloader = true;
                const response = await axios.get('/get_motivos_gastos', {
                    params: { 
                        page,
                        tipo: this.filtroGastoTipo
                    }
                });
                this.motivos_gasto = response.data.data;
                this.paginacionGasto = response.data;
            } catch (error) {
                console.error('Error al obtener motivos de gasto', error);
            } finally {
                this.preloader = false;
            }
        },
        async nuevoMotivoGasto() {
            this.motivo_gasto = { id: 0, nombre: '', tipo: 'caja', accion: 0 };
            $('#modalMotivoGasto').modal('show');
        },
        async editarMotivoGasto(item) {
            this.motivo_gasto = { id: item.id, nombre: item.nombre, tipo: item.tipo || 'caja', accion: 1 };
            $('#modalMotivoGasto').modal('show');
        },
        async guardarMotivoGasto() {
            try {
                this.loading = true;
                await axios.post('/guardar_motivo_gasto', this.motivo_gasto);
                Toast.fire({
                    icon: 'success',
                    title: 'Motivo de egreso registrado exitosamente'
                });
                await this.obtenerMotivosGastos(1);
                this.cancelarGuardarMotivoGasto();
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo guardar el motivo'
                });
            } finally { this.loading = false; }
        },
        async modificarMotivoGasto() {
            try {
                this.loading = true;
                await axios.post('/modificar_motivo_gasto', this.motivo_gasto);
                Toast.fire({
                    icon: 'success',
                    title: 'Motivo de egreso modificado correctamente'
                });
                await this.obtenerMotivosGastos(this.paginacionGasto.current_page);
                this.cancelarGuardarMotivoGasto();
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo modificar el motivo'
                });
            } finally { this.loading = false; }
        },
        async activarMotivoGasto(item) {
            try {
                this.preloader = true;
                await axios.post('/activar_motivo_gasto', { id: item.id });
                Toast.fire({
                    icon: 'success',
                    title: 'Motivo de egreso activado'
                });
                await this.obtenerMotivosGastos(this.paginacionGasto.current_page);
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo activar el motivo'
                });
            } finally {
                this.preloader = false;
            }
        },
        async desactivarMotivoGasto(item) {
            try {
                this.preloader = true;
                await axios.post('/desactivar_motivo_gasto', { id: item.id });
                Toast.fire({
                    icon: 'warning',
                    title: 'Motivo de egreso desactivado'
                });
                await this.obtenerMotivosGastos(this.paginacionGasto.current_page);
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo desactivar el motivo'
                });
            } finally {
                this.preloader = false;
            }
        },
        cancelarGuardarMotivoGasto() {
            $('#modalMotivoGasto').modal('hide');
            this.motivo_gasto = { id: 0, nombre: '', tipo: 'caja', accion: 0 };
        },
    },

    async mounted() {
        this.preloader = true;
        await Promise.all([this.obtenerMotivosIngreso(), this.obtenerMotivosGastos()]);
        this.preloader = false;
    },
};
</script>

<style scoped>
/* Tabs Personalizados */
.dropdown-toggle::after {
    display: none !important;
}
.custom-tabs {
    border-bottom: 2px solid #dee2e6;
}
.custom-tabs .nav-link {
    color: #6c757d !important;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    transition: all 0.05s ease;
    font-size: 0.85rem;
    margin-bottom: -2px;
}
.custom-tabs .nav-link:hover {
    background-color: #e9ecef !important;
}

/* Tab Activo Ingresos */
.custom-tabs .nav-link.border-success-tab.active {
    background-color: #ffffff !important;
    color: #198754 !important;
    border-color: #dee2e6;
    border-top: 3px solid #198754;
    border-bottom: 3px solid #ffffff;
    z-index: 2;
    position: relative;
}

/* Tab Activo Egresos */
.custom-tabs .nav-link.border-danger-tab.active {
    background-color: #ffffff !important;
    color: #dc3545 !important;
    border-color: #dee2e6;
    border-top: 3px solid #dc3545;
    border-bottom: 3px solid #ffffff;
    z-index: 2;
    position: relative;
}

/* Tabla compacta */
.ledger-table { font-size: 0.8rem; }
.ledger-table th {
    vertical-align: middle;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 8px 5px;
}
.ledger-table td { vertical-align: middle; padding: 5px 5px; }

/* Animaciones */
.fade-in-animation {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Preloader */
.preloader {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex; justify-content: center; align-items: center; z-index: 9999;
}
.spinner {
    border: 4px solid rgba(25, 135, 84, 0.2);
    border-top: 4px solid #198754;
    border-radius: 50%;
    width: 50px; height: 50px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>