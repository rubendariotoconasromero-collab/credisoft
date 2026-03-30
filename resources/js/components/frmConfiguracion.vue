<template>
    <main class="config-management">
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Configuración de Datos
                        </h5>
                    </div>

                    <div class="card-body pt-4">
                        
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

                        <div class="tab-content bg-white py-4 border rounded-bottom shadow-sm">
                            
                            <div v-if="vista === 0" class="fade-in-animation">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-success">
                                    <h6 class="my-0 fw-bold text-success text-uppercase">Listado de Motivos de Ingresos</h6>
                                    <button @click="nuevoMotivoIngreso()" class="btn btn-success btn-sm fw-bold shadow-sm">
                                        <i class="fas fa-plus-circle me-1"></i> Nuevo Motivo
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover table-striped align-middle mb-0">
                                        <thead class="table-success text-center align-middle">
                                            <tr>
                                                <th class="text-uppercase fw-bold" width="50%">Motivo Ingreso</th>
                                                <th class="text-uppercase fw-bold" width="15%">Aplica en</th>
                                                <th class="text-uppercase fw-bold" width="15%">Estado</th>
                                                <th class="text-uppercase fw-bold" width="20%">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in motivos_ingreso" :key="item.id">
                                                <td class="text-uppercase fw-bold text-dark ps-3">{{ item.nombre }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.tipo === 'boveda'" class="badge bg-info bg-gradient rounded-pill px-3 shadow-sm" style="min-width: 90px;"> Bóveda</span>
                                                    <span v-else class="badge bg-primary bg-gradient rounded-pill px-3 shadow-sm" style="min-width: 90px;"> Caja</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="item.estado === 0" class="badge bg-success rounded-pill px-3 shadow-sm" style="min-width: 90px;">Activo</span>
                                                    <span v-else class="badge bg-danger rounded-pill px-3 shadow-sm" style="min-width: 90px;">Inactivo</span>
                                                </td>
                                                <td class="text-center">
                                      
                                                    <div class="btn-group my-0 py-0">
                                                        <a style="cursor:pointer;"
                                                            class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                                        </a>
                                                        <ul class="dropdown-menu my-0 py-0">
                                                            <li v-if="item.estado === 0" @click="desactivarMotivoIngreso(item)">
                                                                <a class="dropdown-item text-danger" href="#">
                                                                    <i class="fas fa-times-circle"></i> Desactivar</a>
                                                            </li>
                                                            <li v-else @click="activarMotivoIngreso(item)"><a
                                                                    class="dropdown-item text-success" href="#">
                                                                    <i class="fas fa-check"></i> Activar</a>
                                                            </li>
                                                            <li @click="editarMotivoIngreso(item)"><a
                                                                    class="dropdown-item text-primary" href="#">
                                                                    <i class="fas fa-pencil-alt"></i> Editar</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="motivos_ingreso.length === 0">
                                                <td colspan="4" class="text-center py-4 text-muted fst-italic">No hay motivos de ingreso registrados.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div v-if="vista === 1" class="fade-in-animation">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-danger">
                                    <h6 class="my-0 fw-bold text-danger text-uppercase">Listado de Motivos de Egresos</h6>
                                    <button @click="nuevoMotivoGasto()" class="btn btn-danger btn-sm fw-bold shadow-sm">
                                        <i class="fas fa-plus-circle me-1"></i> Nuevo Motivo
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover table-striped align-middle mb-0">
                                        <thead class="table-danger text-center align-middle">
                                            <tr>
                                                <th class="text-uppercase fw-bold" width="50%">Motivo Egreso</th>
                                                <th class="text-uppercase fw-bold" width="15%">Aplica en</th>
                                                <th class="text-uppercase fw-bold" width="15%">Estado</th>
                                                <th class="text-uppercase fw-bold" width="20%">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in motivos_gasto" :key="item.id">
                                                <td class="text-uppercase fw-bold text-dark ps-3">{{ item.nombre }}</td>
                                                <td class="text-center">
                                                    <span v-if="item.tipo === 'boveda'" class="badge bg-info bg-gradient rounded-pill px-3 shadow-sm" style="min-width: 90px;"> Bóveda</span>
                                                    <span v-else class="badge bg-warning text-dark bg-gradient rounded-pill px-3 shadow-sm" style="min-width: 90px;"> Caja</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="item.estado === 0" class="badge bg-success rounded-pill px-3 shadow-sm" style="min-width: 90px;">Activo</span>
                                                    <span v-else class="badge bg-danger rounded-pill px-3 shadow-sm" style="min-width: 90px;">Inactivo</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group my-0 py-0">
                                                        <a style="cursor:pointer;"
                                                            class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                                        </a>
                                                        <ul class="dropdown-menu my-0 py-0">
                                                            <li v-if="item.estado === 0" @click="desactivarMotivoGasto(item)">
                                                                <a class="dropdown-item text-danger" href="#">
                                                                    <i class="fas fa-times-circle"></i> Desactivar</a>
                                                            </li>
                                                            <li v-else @click="activarMotivoGasto(item)"><a
                                                                    class="dropdown-item text-success" href="#">
                                                                    <i class="fas fa-check"></i> Activar</a>
                                                            </li>
                                                            <li @click="editarMotivoGasto(item)"><a
                                                                    class="dropdown-item text-primary" href="#">
                                                                    <i class="fas fa-pencil-alt"></i> Editar</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="motivos_gasto.length === 0">
                                                <td colspan="4" class="text-center py-4 text-muted fst-italic">No hay motivos de egreso registrados.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal fade" id="modalMotivoIngreso" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success py-3">
                        <h5 class="modal-title text-white fw-bold text-uppercase">
                            <i class="fas" :class="motivo_ingreso.accion == 1 ? 'fa-edit' : 'fa-plus-circle'"></i>
                            {{ motivo_ingreso.accion == 1 ? 'Editar' : 'Nuevo' }} Motivo de Ingreso
                        </h5>
                        <button @click="cancelarGuardarMotivoIngreso()" type="button" class="btn-close btn-close-white"></button>
                    </div>
                    <form @submit.prevent="motivo_ingreso.accion == 0 ? guardarMotivoIngreso() : modificarMotivoIngreso()">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1 text-muted">Nombre del Motivo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-success" 
                                    v-model="motivo_ingreso.nombre" placeholder="Ej: Aporte de Capital..." required />
                            </div>
                            
                            <div class="form-group">
                                <label class="fw-bold mb-1 text-muted">Este motivo aplica para: <span class="text-danger">*</span></label>
                                <select class="form-select border-success" v-model="motivo_ingreso.tipo" required>
                                    <option value="caja">Caja (Movimientos del Cajero)</option>
                                    <option value="boveda">Bóveda (Bóveda Principal)</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer bg-light py-2 border-top">
                            <button @click="cancelarGuardarMotivoIngreso()" type="button" class="btn btn-secondary px-4">Cancelar</button>
                            <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm" :disabled="loading">
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
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger py-3">
                        <h5 class="modal-title text-white fw-bold text-uppercase">
                            <i class="fas" :class="motivo_gasto.accion == 1 ? 'fa-edit' : 'fa-plus-circle'"></i>
                            {{ motivo_gasto.accion == 1 ? 'Editar' : 'Nuevo' }} Motivo de Egreso
                        </h5>
                        <button @click="cancelarGuardarMotivoGasto()" type="button" class="btn-close btn-close-white"></button>
                    </div>
                    <form @submit.prevent="motivo_gasto.accion == 0 ? guardarMotivoGasto() : modificarMotivoGasto()">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1 text-muted">Nombre del Motivo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-danger" 
                                    v-model="motivo_gasto.nombre" placeholder="Ej: Pago de Servicios..." required />
                            </div>
                            
                            <div class="form-group">
                                <label class="fw-bold mb-1 text-muted">Este motivo aplica para: <span class="text-danger">*</span></label>
                                <select class="form-select border-danger" v-model="motivo_gasto.tipo" required>
                                    <option value="caja">Caja (Movimientos del Cajero)</option>
                                    <option value="boveda">Bóveda (Bóveda Principal)</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer bg-light py-2 border-top">
                            <button @click="cancelarGuardarMotivoGasto()" type="button" class="btn btn-secondary px-4">Cancelar</button>
                            <button type="submit" class="btn btn-danger fw-bold px-4 shadow-sm" :disabled="loading">
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

export default {
    data() {
        return {
            preloader: false,
            loading: false, 
            vista: 0,
            motivos_ingreso: [],
            motivos_gasto: [],
            // NUEVO: Agregamos el atributo tipo='caja' por defecto al reiniciar el form
            motivo_ingreso: { id: 0, nombre: '', tipo: 'caja', accion: 0 },
            motivo_gasto: { id: 0, nombre: '', tipo: 'caja', accion: 0 },
        };
    },
    methods: {
        async cambiarVista(vista) {
            this.vista = vista;
        },

        // --- MOTIVOS INGRESO ---
        async obtenerMotivosIngreso() {
            try {
                this.preloader = true;
                const response = await axios.get('/get_motivos_ingresos');
                this.motivos_ingreso = response.data;
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
                Swal.fire({ title: 'Éxito', text: 'Motivo registrado.', icon: 'success', timer: 1200 });
                await this.obtenerMotivosIngreso();
                this.cancelarGuardarMotivoIngreso();
            } catch (error) {
                Swal.fire('Error', 'No se pudo guardar.', 'error');
            } finally { this.loading = false; }
        },
        async modificarMotivoIngreso() {
            try {
                this.loading = true;
                await axios.post('/modificar_motivo_ingreso', this.motivo_ingreso);
                Swal.fire({ title: 'Éxito', text: 'Motivo modificado.', icon: 'success', timer: 1200 });
                await this.obtenerMotivosIngreso();
                this.cancelarGuardarMotivoIngreso();
            } catch (error) {
                Swal.fire('Error', 'No se pudo modificar.', 'error');
            } finally { this.loading = false; }
        },
        async activarMotivoIngreso(item) {
            this.preloader = true;
            await axios.post('/activar_motivo_ingreso', { id: item.id });
            await this.obtenerMotivosIngreso();
        },
        async desactivarMotivoIngreso(item) {
            this.preloader = true;
            await axios.post('/desactivar_motivo_ingreso', { id: item.id });
            await this.obtenerMotivosIngreso();
        },
        cancelarGuardarMotivoIngreso() {
            $('#modalMotivoIngreso').modal('hide');
            this.motivo_ingreso = { id: 0, nombre: '', tipo: 'caja', accion: 0 };
        },

        // --- MOTIVOS EGRESO ---
        async obtenerMotivosGastos() {
            try {
                this.preloader = true;
                const response = await axios.get('/get_motivos_gastos');
                this.motivos_gasto = response.data;
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
                Swal.fire({ title: 'Éxito', text: 'Motivo registrado.', icon: 'success', timer: 1200 });
                await this.obtenerMotivosGastos();
                this.cancelarGuardarMotivoGasto();
            } catch (error) {
                Swal.fire('Error', 'No se pudo guardar.', 'error');
            } finally { this.loading = false; }
        },
        async modificarMotivoGasto() {
            try {
                this.loading = true;
                await axios.post('/modificar_motivo_gasto', this.motivo_gasto);
                Swal.fire({ title: 'Éxito', text: 'Motivo modificado.', icon: 'success', timer: 1200 });
                await this.obtenerMotivosGastos();
                this.cancelarGuardarMotivoGasto();
            } catch (error) {
                Swal.fire('Error', 'No se pudo modificar.', 'error');
            } finally { this.loading = false; }
        },
        async activarMotivoGasto(item) {
            this.preloader = true;
            await axios.post('/activar_motivo_gasto', { id: item.id });
            await this.obtenerMotivosGastos();
        },
        async desactivarMotivoGasto(item) {
            this.preloader = true;
            await axios.post('/desactivar_motivo_gasto', { id: item.id });
            await this.obtenerMotivosGastos();
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
    transition: all 0.2s ease;
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