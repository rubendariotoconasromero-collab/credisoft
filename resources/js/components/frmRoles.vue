<template>
    <main class="">
        <div class="page-content">
            <div class="container-fluid">

                <div class="card">
                    <div class="card-header bg-warning py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Gestión de Roles
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3 mt-0">
                            <div class="col-md-12 text-end">
                                <button @click="abrirModalNuevo()" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i>
                                    Nuevo Rol
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped align-middle table-roles"
                                style="font-size: 12px;">
                                <thead class="text-white text-uppercase table-success">
                                    <tr>
                                        <th class="text-start fw-bold text-dark text-uppercase">Rol</th>
                                        <th class="text-start fw-bold text-dark text-uppercase">Estado</th>
                                        <th class="text-center fw-bold text-dark text-uppercase">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="rol in array_roles" :key="rol.id">
                                        <td class="text-capitalize fw-bold">{{ rol.nombre }}</td>
                                        <td>
                                            <span :class="rol.estado === 1 ? 'badge bg-success' : 'badge bg-danger'">
                                                {{ rol.estado === 1 ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a class="dropdown-toggle text-success" data-bs-toggle="dropdown"
                                                    style="cursor: pointer;">
                                                    <i class="fas fa-ellipsis-h fs-4"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li v-if="rol.estado === 1"
                                                        @click="toggleEstado(rol, 'desactivar')">
                                                        <a class="dropdown-item text-danger" href="#">
                                                            <i class="fas fa-times me-1"></i> Desactivar
                                                        </a>
                                                    </li>
                                                    <li v-else @click="toggleEstado(rol, 'activar')">
                                                        <a class="dropdown-item text-success" href="#">
                                                            <i class="fas fa-check me-1"></i> Activar
                                                        </a>
                                                    </li>
                                                    <li @click="editarRol(rol)">
                                                        <a class="dropdown-item text-primary" href="#">
                                                            <i class="fas fa-pencil-alt me-1"></i> Editar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>





                <!-- Modal -->
                <div class="modal fade" id="nuevoRol" tabindex="-1" aria-labelledby="modalLabel">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title text-dark fw-bold" id="modalLabel">
                                    {{ rol.accion === 0 ? 'Nuevo Rol' : 'Modificar Rol' }}
                                </h5>
                                <button @click="cerrarModal" type="button" class="btn-close btn-close-dark"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre</label>
                                    <input v-model="rol.nombre" type="text" class="form-control" id="nombre"
                                        placeholder="Ingrese el nombre del rol">
                                </div>
                                <label class="form-label">Permisos</label>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <tbody>
                                            <tr v-for="permiso in lista_roles" :key="permiso.id">
                                                <td class="fw-bold">{{ permiso.descripcion }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            v-model="permiso.activado" :id="`switch-${permiso.id}`">
                                                        <label class="form-check-label" :for="`switch-${permiso.id}`">
                                                            {{ permiso.activado ? 'Activado' : 'Desactivado' }}
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button @click="cerrarModal" class="btn btn-outline-secondary">
                                    <i class="fas fa-times-circle"></i>
                                    Cerrar</button>
                                <button :disabled="guardando_rol"
                                    @click="rol.accion === 0 ? guardarRol() : modificarRol()" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i>
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
.table-roles .badge {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
    border-radius: 15px;
    min-width: 100px;
}

.card {
    border: none;
    border-radius: 8px;
}

.table th,
.table td {
    vertical-align: middle;
}

.btn-group .dropdown-menu {
    min-width: 120px;
}

.form-check-input:checked {
    background-color: #52BE80;
    border-color: #52BE80;
    border-radius:10px;
}

.dropdown-toggle::after {
    display: none !important;
}
</style>