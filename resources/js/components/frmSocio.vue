<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <!-- CARD PRINCIPAL -->
                <div class="card shadow-sm border-0 animate-fade-in">
                    <div class="card-header bg-warning bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-dark text-uppercase mx-auto" style="font-size: 14px; letter-spacing: 0.5px;">
                            <i class="fas fa-user-tie me-2"></i> Gestión de Socios / Inversionistas
                        </h5>
                    </div>
                    <div class="card-body pt-2">

                        <!-- FILTROS Y ACCIONES -->
                        <div class="card bg-light border-0 mb-3 animate-fade-in">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-center">
                                    <!-- Criterio -->
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" v-model="criterio" @change="listarDatos(1, buscar, criterio)">
                                            <option value="nombres">Nombres</option>
                                            <option value="apellidos">Apellidos</option>
                                            <option value="ci">Cédula (CI)</option>
                                        </select>
                                    </div>
                                    <!-- Buscar -->
                                    <div class="col-md-5">
                                        <input type="text" v-model="buscar" @keyup.enter="listarDatos(1, buscar, criterio)" @input="listarDatos(1, buscar, criterio)" class="form-control form-control-sm" placeholder="Ingrese texto a buscar...">
                                    </div>
                                    <!-- Acciones -->
                                    <div class="col-md-4 d-flex justify-content-end gap-1">
                                        <button class="btn btn-success btn-xs px-3" @click="abrirModal('registrar')" style="font-size: 10.5px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-plus-circle"></i> <span>Nuevo Socio</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENCABEZADO LISTADO -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase animate-fade-in" style="font-size: 12px;">
                                <i class="fas fa-users me-1 text-success"></i> Socios Registrados ({{ pagination.total }})
                            </h6>
                        </div>

                        <!-- TABLA -->
                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm align-middle table-compact">
                                <thead class="table-success text-white text-uppercase fw-bold text-center">
                                    <tr>
                                        <th class="text-start">Nombre Completo</th>
                                        <th>CI / Doc</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in arrayDatos" :key="item.id" class="animate-fade-in">
                                        <td class="text-uppercase fw-bold text-dark text-start">{{ item.nombres }} {{ item.apellidos }}</td>
                                        <td class="text-center fw-bold text-dark">{{ item.ci || '---' }}</td>
                                        <td class="text-center text-muted">{{ item.telefono || '---' }}</td>
                                        <td class="text-center text-muted">{{ item.email || '---' }}</td>
                                        <td class="text-center">
                                            <span v-if="item.estado == 1" class="badge bg-success text-uppercase font-size-10 px-2 rounded" style="width: 80px; display: inline-block; text-align: center;">Activo</span>
                                            <span v-else class="badge bg-secondary text-uppercase font-size-10 px-2 rounded" style="width: 80px; display: inline-block; text-align: center;">Inactivo</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group my-0 py-0">
                                                <a style="cursor:pointer;"
                                                    class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                    data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                                </a>
                                                <ul class="dropdown-menu my-0 py-0">
                                                    <li @click="desactivar(item.id)" v-if="item.estado == 1">
                                                        <a class="dropdown-item text-danger" href="#">
                                                            <i class="fas fa-times-circle"></i> Desactivar</a>
                                                    </li>
                                                    <li @click="activar(item.id)" v-else><a
                                                            class="dropdown-item text-success" href="#">
                                                            <i class="fas fa-check"></i> Activar</a>
                                                    </li>
                                                    <li @click="abrirModal('actualizar', item)"><a
                                                            class="dropdown-item text-primary" href="#">
                                                            <i class="fas fa-pencil-alt"></i> Editar</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="arrayDatos.length === 0">
                                        <td colspan="6" class="text-center text-muted py-5 bg-white rounded border">
                                            <i class="fas fa-search fa-3x mb-3 text-secondary animate-bounce"></i>
                                            <p class="mb-0 fw-bold font-size-13 text-muted">No se encontraron socios con los criterios seleccionados.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Card Pagination -->
                            <div class="card-footer py-2 bg-transparent border-0 d-flex justify-content-end">
                                <nav v-if="pagination.last_page > 1">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                        </li>
                                        <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{active: page == pagination.current_page}">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)">{{ page }}</a>
                                        </li>
                                        <li class="page-item" :class="{disabled: pagination.current_page >= pagination.last_page}">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- modal nuevo/actualizar socio -->
                <div class="modal fade" :class="{'show d-block': modal}" tabindex="-1" :style="modal ? 'background: rgba(0,0,0,0.5)' : ''" data-bs-backdrop="static">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border border-secondary border-2">
                            <div class="modal-header bg-warning py-2 text-dark">
                                <h5 class="modal-title fw-bold text-dark text-uppercase" style="font-size: 14px;">
                                    <i class="fas fa-user-tie me-2"></i> <span v-text="tituloModal"></span>
                                </h5>
                                <button type="button" class="btn-close" @click="cerrarModal()"></button>
                            </div>
                            <div class="modal-body p-4">
                                <form action="" method="post" enctype="multipart/form-data" class="form-horizontal row g-3">
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted mb-1">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" v-model="form.nombres" class="form-control border-success" placeholder="Nombres del socio...">
                                        <span v-if="errorMostrarMsj && !form.nombres" class="text-danger small fw-bold">El nombre es obligatorio.</span>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted mb-1">Apellidos</label>
                                        <input type="text" v-model="form.apellidos" class="form-control" placeholder="Apellidos...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted mb-1">Cédula / Documento</label>
                                        <input type="text" v-model="form.ci" class="form-control" placeholder="Ej. 1234567">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted mb-1">Teléfono / Celular</label>
                                        <input type="text" v-model="form.telefono" class="form-control" placeholder="Ej. 77712345">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-muted mb-1">Correo Electrónico</label>
                                        <input type="email" v-model="form.email" class="form-control" placeholder="correo@ejemplo.com">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-muted mb-1">Dirección Física</label>
                                        <input type="text" v-model="form.direccion" class="form-control" placeholder="Avenida, Calle, Nro...">
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal()">
                                    <i class="fas fa-times-circle"></i> Cerrar
                                </button>
                                <button type="button" v-if="tipoAccion==1" class="btn btn-success" @click="registrarDato()">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" v-if="tipoAccion==2" class="btn btn-success" @click="actualizarDato()">
                                    <i class="fas fa-save"></i> Modificar
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
            form: {
                id: 0,
                nombres: '',
                apellidos: '',
                ci: '',
                telefono: '',
                direccion: '',
                email: ''
            },
            arrayDatos: [],
            modal: 0,
            tituloModal: '',
            tipoAccion: 0,
            errorMostrarMsj: 0,
            pagination: {
                'total': 0, 'current_page': 0, 'per_page': 0, 'last_page': 0, 'from': 0, 'to': 0,
            },
            offset: 3,
            criterio: 'nombres',
            buscar: ''
        }
    },
    computed: {
        pagesNumber: function() {
            if (!this.pagination.to) return [];
            var from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            var to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) to = this.pagination.last_page;
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        listarDatos(page, buscar, criterio) {
            let url = `/socio/get_socios?page=${page}&buscar=${buscar}&criterio=${criterio}`;
            axios.get(url).then((response) => {
                this.arrayDatos = response.data.data;
                this.pagination = response.data.pagination;
            }).catch(function (error) {
                console.log(error);
            });
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarDatos(page, buscar, criterio);
        },
        validarDato() {
            this.errorMostrarMsj = 0;
            if (!this.form.nombres) this.errorMostrarMsj = 1;
            return this.errorMostrarMsj;
        },
        registrarDato() {
            if (this.validarDato()) return;
            axios.post('/socio/registrar', this.form).then((response) => {
                this.cerrarModal();
                this.listarDatos(1, '', 'nombres');
                Swal.fire({ position: 'top-end', icon: 'success', title: '¡Socio Registrado!', showConfirmButton: false, timer: 1500 });
            }).catch((error) => {
                Swal.fire('Error', 'No se pudo guardar el registro.', 'error');
            });
        },
        actualizarDato() {
            if (this.validarDato()) return;
            axios.put('/socio/actualizar', this.form).then((response) => {
                this.cerrarModal();
                this.listarDatos(this.pagination.current_page, '', 'nombres');
                Swal.fire({ position: 'top-end', icon: 'success', title: '¡Datos Actualizados!', showConfirmButton: false, timer: 1500 });
            }).catch((error) => {
                Swal.fire('Error', 'No se pudo actualizar el registro.', 'error');
            });
        },
        desactivar(id) {
            Swal.fire({
                title: '¿Desactivar Socio?', text: 'El socio ya no aparecerá en las listas de selección.', icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, Desactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.put('/socio/desactivar', { 'id': id }).then(() => {
                        this.listarDatos(this.pagination.current_page, '', 'nombres');
                        Swal.fire('¡Desactivado!', 'El socio ha sido desactivado.', 'success');
                    });
                }
            })
        },
        activar(id) {
            Swal.fire({
                title: '¿Activar Socio?', icon: 'question', showCancelButton: true,
                confirmButtonColor: '#28a745', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, Activar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.put('/socio/activar', { 'id': id }).then(() => {
                        this.listarDatos(this.pagination.current_page, '', 'nombres');
                        Swal.fire('¡Activado!', 'El socio está activo nuevamente.', 'success');
                    });
                }
            })
        },
        abrirModal(accion, data = []) {
            this.modal = 1;
            this.errorMostrarMsj = 0;
            switch(accion){
                case 'registrar': {
                    this.tituloModal = 'Registrar Nuevo Socio';
                    this.tipoAccion = 1;
                    this.form = { id: 0, nombres: '', apellidos: '', ci: '', telefono: '', direccion: '', email: '' };
                    break;
                }
                case 'actualizar': {
                    this.tituloModal = 'Actualizar Datos del Socio';
                    this.tipoAccion = 2;
                    this.form = { 
                        id: data['id'], 
                        nombres: data['nombres'], 
                        apellidos: data['apellidos'] || '', 
                        ci: data['ci'] || '', 
                        telefono: data['telefono'] || '', 
                        direccion: data['direccion'] || '', 
                        email: data['email'] || ''
                    };
                    break;
                }
            }
        },
        cerrarModal() {
            this.modal = 0;
            this.tituloModal = '';
            this.form = { id: 0, nombres: '', apellidos: '', ci: '', telefono: '', direccion: '', email: '' };
        }
    },
    mounted() {
        this.listarDatos(1, this.buscar, this.criterio);
    }
}
</script>

<style scoped>
.dropdown-toggle::after {
    display: none !important;
}

/* Estilos personalizados para inputs y selects de filtros */
.form-select-sm {
    border: 1px solid #ced4da !important;
    background-color: #ffffff !important;
    color: #495057 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
    padding-right: 2rem !important;
    height: 31px !important;
    font-size: 11px !important;
    border-radius: 4px !important;
}

.form-control-sm {
    border: 1px solid #ced4da !important;
    background-color: #ffffff !important;
    color: #495057 !important;
    height: 31px !important;
    font-size: 11px !important;
    border-radius: 4px !important;
}

.form-select-sm:focus, .form-control-sm:focus {
    border-color: #198754 !important;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
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

/* Paginación con verde success */
.pagination .page-item.active .page-link {
    background-color: #198754 !important;
    border-color: #198754 !important;
    color: #ffffff !important;
}
.pagination .page-link {
    color: #198754;
}
.pagination .page-link:hover {
    color: #146c43;
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