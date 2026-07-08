<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <!-- CARD PRINCIPAL -->
                <div class="card shadow-sm border-0 animate-fade-in">
                    <div class="card-header bg-warning bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-dark text-uppercase mx-auto" style="font-size: 14px; letter-spacing: 0.5px;">
                            <i class="fas fa-user-shield me-2"></i> Gestión de Roles
                        </h5>
                    </div>
                    <div class="card-body pt-2">

                        <!-- FILTROS Y ACCIONES -->
                        <div class="card bg-light border-0 mb-3 animate-fade-in">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-dark my-0 text-uppercase" style="font-size: 12px;">
                                            <i class="fas fa-list me-1 text-success"></i> Roles Registrados ({{ array_roles.length }})
                                        </h6>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end gap-1">
                                        <button @click="abrirModalNuevo()" class="btn btn-success btn-xs px-3" style="font-size: 10.5px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-plus-circle"></i> <span>Nuevo Rol</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LISTADO EN TARJETAS -->
                        <div class="row g-3">
                            <div v-for="rol in array_roles" :key="rol.id" class="col-md-6 col-lg-4 animate-fade-in">
                                <div class="card h-100 shadow-sm transition-hover"
                                     :style="rol.estado === 1 ? 'border: 1.5px solid #86efac;' : 'border: 1.5px solid #e5e7eb;'"
                                     style="background: #ffffff; border-radius: 6px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle p-2" :style="rol.estado === 1 ? 'background-color: #e6f4ea; color: #137333;' : 'background-color: #f3f4f6; color: #4b5563;'">
                                                        <i class="fas fa-user-shield"></i>
                                                    </div>
                                                    <h6 class="my-0 fw-bold text-dark text-uppercase" style="font-size: 12.5px; letter-spacing: 0.3px;">
                                                        {{ rol.nombre }}
                                                    </h6>
                                                </div>
                                                
                                                <div class="btn-group">
                                                    <a class="dropdown-toggle text-muted px-2 py-1 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <li v-if="rol.estado === 1" @click="toggleEstado(rol, 'desactivar')">
                                                            <a class="dropdown-item text-danger" href="#">
                                                                <i class="fas fa-times-circle me-2"></i> Desactivar
                                                            </a>
                                                        </li>
                                                        <li v-else @click="toggleEstado(rol, 'activar')">
                                                            <a class="dropdown-item text-success" href="#">
                                                                <i class="fas fa-check-circle me-2"></i> Activar
                                                            </a>
                                                        </li>
                                                        <li @click="editarRol(rol)">
                                                            <a class="dropdown-item text-primary" href="#">
                                                                <i class="fas fa-pencil-alt me-2"></i> Editar Rol
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top border-light">
                                            <span class="text-muted font-size-10">Estado del Rol</span>
                                            <span :class="rol.estado === 1 ? 'badge bg-success text-uppercase font-size-10 px-2 rounded' : 'badge bg-secondary text-uppercase font-size-10 px-2 rounded'">
                                                {{ rol.estado === 1 ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="nuevoRol" tabindex="-1" aria-labelledby="modalLabel" data-bs-backdrop="static">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content border border-secondary border-2">
                            <div class="modal-header bg-warning py-2 text-dark">
                                <h5 class="modal-title text-dark fw-bold text-uppercase" id="modalLabel" style="font-size: 14px;">
                                    <i class="fas" :class="rol.accion === 0 ? 'fa-plus-circle' : 'fa-edit'"></i> {{ rol.accion === 0 ? 'Nuevo Rol' : 'Modificar Rol' }}
                                </h5>
                                <button @click="cerrarModal" type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <label for="nombre" class="form-label fw-bold text-uppercase mb-0 text-muted" style="font-size: 11px;">Nombre del Rol</label>
                                            </div>
                                            <div class="col-md-10">
                                                <input v-model="rol.nombre" type="text" class="form-control form-control-sm text-uppercase fw-semibold" id="nombre"
                                                    placeholder="Ej: ASESOR DE CRÉDITO, CAJERO PRINCIPAL..." style="font-size: 12px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-2 px-1">
                                    <h6 class="fw-bold text-dark my-0 text-uppercase" style="font-size: 12px;">
                                        <i class="fas fa-key me-1 text-success"></i> Asignación de Permisos del Sistema
                                    </h6>
                                    <span class="badge bg-success font-size-10">{{ lista_roles.filter(p => p.activado).length }} asignados</span>
                                </div>

                                <div class="row g-2" style="max-height: 450px; overflow-y: auto; padding: 4px;">
                                    <div v-for="permiso in lista_roles" :key="permiso.id" class="col-md-6 col-lg-4">
                                        <div class="card border border-light-subtle h-100 cursor-pointer transition-hover"
                                             :style="permiso.activado ? 'background-color: #f0fdf4; border-color: #bbf7d0;' : 'background-color: #ffffff;'"
                                             @click="permiso.activado = !permiso.activado">
                                            <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                                <div class="pe-2" style="max-width: 80%;">
                                                    <span class="fw-semibold text-dark d-block text-capitalize" style="font-size: 11px; line-height: 1.3;">
                                                        {{ permiso.descripcion }}
                                                    </span>
                                                </div>
                                                <div class="form-check form-switch mb-0 ps-0 d-flex align-items-center">
                                                    <input class="form-check-input cursor-pointer" type="checkbox"
                                                        v-model="permiso.activado" :id="`switch-${permiso.id}`" style="width: 2.2em; height: 1.1em; margin-left: 0;"
                                                        @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button @click="cerrarModal" class="btn btn-secondary">
                                    <i class="fas fa-times-circle"></i>
                                    Cerrar</button>
                                <button :disabled="guardando_rol"
                                    @click="rol.accion === 0 ? guardarRol() : modificarRol()" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    <span v-if="guardando_rol" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ rol.accion === 0 ? 'Guardar' : 'Modificar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            guardando_rol: false,
            lista_roles: [],
            array_roles: [],
            rol: {
                id_rol: 0,
                nombre: '',
                estado: 1,
                accion: 0,
            },
        };
    },

    methods: {
        async abrirModalNuevo() {
            this.resetRol();
            await this.getRoles();
            $('#nuevoRol').modal('show');
        },

        async toggleEstado(rol, action) {
            try {
                const endpoint = action === 'activar' ? '/activar_rol' : '/desactivar_rol';
                await axios.get(`${endpoint}?id_rol=${rol.id}`);
                await this.getArrayRoles();
                this.showSuccess('Estado actualizado correctamente');
            } catch (error) {
                console.error(`Error al ${action} rol:`, error.message);
                this.showError('Error al actualizar el estado');
            }
        },

        async getRoles() {
            try {
                const { data } = await axios.get('/get_permisos');
                this.lista_roles = data.map(permiso => ({ ...permiso, activado: false }));
            } catch (error) {
                console.error('Error al obtener permisos:', error.message);
                this.showError('No se pudieron cargar los permisos');
            }
        },

        async getArrayRoles() {
            try {
                const { data } = await axios.get('/get_roles');
                this.array_roles = data;
            } catch (error) {
                console.error('Error al obtener roles:', error.message);
                this.showError('No se pudieron cargar los roles');
            }
        },

        cerrarModal() {
            $('#nuevoRol').modal('hide');
        },

        async guardarRol() {
            if (!this.validateForm()) return;

            this.guardando_rol = true;
            try {
                await axios.post('/save_rol', { ...this.rol, permisos: this.lista_roles });
                this.handleSuccess();
            } catch (error) {
                console.error('Error al guardar rol:', error.message);
                this.showError('Error al guardar el rol');
            } finally {
                this.guardando_rol = false;
            }
        },

        async modificarRol() {
            if (!this.validateForm()) return;

            this.guardando_rol = true;
            try {
                await axios.post('/modify_rol', { ...this.rol, permisos: this.lista_roles });
                this.handleSuccess();
            } catch (error) {
                console.error('Error al modificar rol:', error.message);
                this.showError('Error al modificar el rol');
            } finally {
                this.guardando_rol = false;
            }
        },

        async editarRol(rol) {
            try {
                Object.assign(this.rol, {
                    accion: 1,
                    nombre: rol.nombre,
                    estado: rol.estado,
                    id_rol: rol.id,
                });
                await this.getRoles();
                const { data } = await axios.get(`/get_permisos_rol?id_rol=${rol.id}`);
                this.lista_roles.forEach(permiso => {
                    permiso.activado = data.some(p => p.id === permiso.id);
                });
                $('#nuevoRol').modal('show');
            } catch (error) {
                console.error('Error al editar rol:', error.message);
                this.showError('No se pudo cargar la información del rol');
            }
        },

        // Helpers
        resetRol() {
            this.rol = { id_rol: 0, nombre: '', estado: 1, accion: 0 };
        },

        validateForm() {
            if (!this.rol.nombre) {
                this.showError('El nombre del rol es obligatorio');
                return false;
            }
            if (!this.lista_roles.some(p => p.activado)) {
                this.showError('Debe seleccionar al menos un permiso');
                return false;
            }
            return true;
        },

        handleSuccess() {
            this.showSuccess('Operación realizada con éxito');
            this.cerrarModal();
            this.getArrayRoles();
        },

        showSuccess(message) {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 1500,
            });
        },

        showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                timer: 2000,
            });
        },
    },

    mounted() {
        this.getArrayRoles();
    },
};
</script>

<style scoped>
.dropdown-toggle::after {
    display: none !important;
}

/* Hover transitions and helpers */
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.cursor-pointer {
    cursor: pointer;
}

/* Clases específicas para diseño extra compacto */
.table-compact th, .table-compact td {
    padding: 3px 5px !important;
    vertical-align: middle !important;
    font-size: 10.5px !important;
}
.table-compact th {
    font-weight: 700 !important;
    font-size: 10px !important;
}
.font-size-13 { font-size: 13px !important; }
.font-size-10 { font-size: 10px !important; }
.btn-xs {
    padding: 3px 8px !important;
    font-size: 10.5px !important;
    border-radius: 4px !important;
}

.form-check-input {
    border-radius: 2em !important;
}
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
    0%   { opacity: 0; }
    100% { opacity: 1; }
}

.animate-bounce {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
    50%       { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
</style>