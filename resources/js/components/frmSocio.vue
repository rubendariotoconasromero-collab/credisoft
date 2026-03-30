<template>
    <main class="page-content px-0 mx-0">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm">
               <div class="card-header bg-warning py-2">
                    <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                        Gestión de Socios / Inversionistas
                    </h5>
                </div>
            
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <select class="form-select form-control" v-model="criterio">
                                    <option value="nombres">Nombres</option>
                                    <option value="apellidos">Apellidos</option>
                                    <option value="ci">Cédula (CI)</option>
                                </select>
                                <input type="text" v-model="buscar" @keyup.enter="listarDatos(1, buscar, criterio)" class="form-control border-success" placeholder="Texto a buscar...">
                                <button type="submit" @click="listarDatos(1, buscar, criterio)" class="btn btn-success">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-success" @click="abrirModal('registrar')">
                                <i class="fas fa-plus-circle"></i> Nuevo Socio
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped table-sm align-middle table-partner">
                            <thead class="table-success text-center">
                                <tr>
                                    <th width="25%" class="text-start">Nombre Completo</th>
                                    <th width="15%">CI / Doc</th>
                                    <th width="15%">Teléfono</th>
                                    <th width="20%">Email</th>
                                    <th width="10%">Estado</th>
                                    <th width="15%">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in arrayDatos" :key="item.id">
                                    <td class="fw-bold text-start">{{ item.nombres }} {{ item.apellidos }}</td>
                                    <td class="text-center">{{ item.ci || '---' }}</td>
                                    <td class="text-center">{{ item.telefono || '---' }}</td>
                                    <td class="text-center text-muted">{{ item.email || '---' }}</td>
                                    <td class="text-center">
                                        <span v-if="item.estado == 1" class="badge bg-success">Activo</span>
                                        <span v-else class="badge bg-danger">Inactivo</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group my-0 py-0">
                                            <a style="cursor:pointer;"
                                                class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                data-bs-toggle="dropdown" aria-expanded="false">
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
                                    <td colspan="6" class="text-center py-4 text-muted fst-italic">No hay socios registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                        <template v-if="arrayDatos.length <= 7">
                            <br><br><br><br><br><br>
                        </template>
                    </div>

                    <nav v-if="pagination.last_page > 1">
                        <ul class="pagination justify-content-end mb-0 shadow-sm">
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

            <div class="modal fade" :class="{'show d-block': modal}" tabindex="-1" :style="modal ? 'background: rgba(0,0,0,0.5)' : ''">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-success text-white py-3">
                            <h5 class="modal-title fw-bold text-white">
                                <i class="fas fa-user-tie me-2"></i> <span v-text="tituloModal"></span>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="cerrarModal()"></button>
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
                        <div class="modal-footer bg-light py-2 border-top">
                            <button type="button" class="btn btn-secondary px-4" @click="cerrarModal()">Cancelar</button>
                            <button type="button" v-if="tipoAccion==1" class="btn btn-success fw-bold px-4 shadow-sm" @click="registrarDato()">
                                <i class="fas fa-save me-1"></i> Guardar Socio
                            </button>
                            <button type="button" v-if="tipoAccion==2" class="btn btn-warning fw-bold px-4 text-dark shadow-sm" @click="actualizarDato()">
                                <i class="fas fa-sync-alt me-1"></i> Actualizar
                            </button>
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

    .table-partner .badge{
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
        border-radius: 15px;
        min-width:100px;
    }

    .table-partner td{
        font-size:12px;
    }
</style>