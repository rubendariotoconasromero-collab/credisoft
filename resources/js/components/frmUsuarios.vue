<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <!-- CARD PRINCIPAL -->
                <div class="card shadow-sm border-0 animate-fade-in">
                    <div class="card-header bg-warning bg-gradient py-2 d-flex justify-content-between align-items-center">
                        <h5 class="header-title my-0 fw-bold text-dark text-uppercase mx-auto" style="font-size: 14px; letter-spacing: 0.5px;">
                            <i class="fas fa-users-cog me-2"></i> Gestión de Usuarios
                        </h5>
                    </div>
                    <div class="card-body pt-2">

                        <!-- FILTROS Y ACCIONES -->
                        <div class="card bg-light border-0 mb-3 animate-fade-in">
                            <div class="card-body p-2">
                                <div class="row g-2 align-items-center">
                                    <!-- Criterio -->
                                    <div class="col-md-3">
                                        <select v-model="criterio" class="form-select form-select-sm" @change="buscarUsuario()">
                                            <option value="users.personal">Nombre personal</option>
                                            <option value="users.ci">CI</option>
                                        </select>
                                    </div>
                                    <!-- Buscar -->
                                    <div class="col-md-5">
                                        <input placeholder="Ingrese texto a buscar..." v-model="buscar" type="text"
                                            class="form-control form-control-sm" @input="buscarUsuario()">
                                    </div>
                                    <!-- Acciones -->
                                    <div class="col-md-4 d-flex justify-content-end gap-1">
                                        <button @click="abrirModalNuevo()" class="btn btn-success btn-xs px-3" style="font-size: 10.5px; height: 31px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fas fa-plus-circle"></i> <span>Nuevo usuario</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENCABEZADO LISTADO -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark my-0 text-uppercase animate-fade-in" style="font-size: 12px;">
                                <i class="fas fa-users me-1 text-success"></i> Usuarios Registrados ({{ pagination.total }})
                            </h6>
                        </div>

                        <!-- TABLA -->
                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm align-middle table-compact">
                                <thead class="table-success text-white text-uppercase fw-bold text-center">
                                    <tr>
                                        <th class="text-start">Usuario</th>
                                        <th class="text-start">Personal</th>
                                        <th>CI</th>
                                        <th>Teléfono</th>
                                        <th class="text-start">Rol</th>
                                                                                <th>Vig. Contraseña</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in lista_usuarios" :key="item.id" class="animate-fade-in">
                                        <td class="fw-bold text-primary text-start text-uppercase">{{ item.name }}</td>
                                        <td class="text-uppercase fw-bold text-dark text-start">{{ item.personal }}</td>
                                        <td class="text-center fw-bold text-dark">{{ item.ci }}</td>
                                        <td class="text-center text-muted">{{ item.telefono }}</td>
                                        <td class="text-start text-uppercase text-muted" style="font-size: 10px;">{{ item.rol }}</td>
                                        <td class="text-center">
                                            <div v-if="item.dias_restantes !== null">
                                                <span v-if="item.dias_restantes < 0" class="badge bg-danger text-uppercase font-size-10 px-2 rounded" style="width: 100px; display: inline-block; text-align: center;">Vencida</span>
                                                <span v-else-if="item.dias_restantes <= 7" class="badge bg-warning text-dark text-uppercase font-size-10 px-2 rounded" style="width: 100px; display: inline-block; text-align: center;">
                                                    {{ item.dias_restantes }} días
                                                </span>
                                                <span v-else class="badge bg-info text-white text-uppercase font-size-10 px-2 rounded" style="width: 100px; display: inline-block; text-align: center;">
                                                    {{ item.dias_restantes }} días
                                                </span>
                                            </div>
                                            <span v-else class="text-muted small">Indefinido</span>
                                        </td>
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
                                                    <li @click="desactivar(item)" v-if="item.estado == 1">
                                                        <a class="dropdown-item text-danger" href="#">
                                                            <i class="fas fa-times-circle"></i> Desactivar</a>
                                                    </li>
                                                    <li @click="activar(item)" v-else><a
                                                            class="dropdown-item text-success" href="#">
                                                            <i class="fas fa-check"></i> Activar</a>
                                                    </li>
                                                    <li @click="editarUsuario(item)"><a
                                                            class="dropdown-item text-primary" href="#">
                                                            <i class="fas fa-pencil-alt"></i> Editar</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="lista_usuarios.length === 0">
                                        <td colspan="8" class="text-center text-muted py-5 bg-white rounded border">
                                            <i class="fas fa-search fa-3x mb-3 text-secondary animate-bounce"></i>
                                            <p class="mb-0 fw-bold font-size-13 text-muted">No se encontraron usuarios con los criterios seleccionados.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Card Pagination -->
                            <div class="card-footer py-2 bg-transparent border-0 d-flex justify-content-end">
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" v-if="pagination.current_page > 1">
                                            <a class="page-link" href="#"
                                                @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                        </li>
                                        <li class="page-item" v-for="page in pagesNumber" :key="page"
                                            :class="[page == isActived ? 'active' : '']">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                        </li>
                                        <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                            <a class="page-link" href="#"
                                                @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal nuevo usuario -->
        <div class="modal fade" id="nuevoUsuario" tabindex="-1" aria-labelledby="exampleModalLabel"
            data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog">
                <form class="needs-validation" novalidate>
                    <div class="modal-content border border-secondary border-2">
                        <div class="modal-header bg-warning py-2 text-dark">
                            <h5 v-if="usuario.accion==0" class="modal-title fw-bold text-dark text-uppercase" id="exampleModalLabel" style="font-size: 14px;">
                                <i class="fas fa-user-plus me-2"></i> Registro Nuevo Usuario
                            </h5>
                            <h5 v-if="usuario.accion==1" class="modal-title fw-bold text-dark text-uppercase" id="exampleModalLabel" style="font-size: 14px;">
                                <i class="fas fa-user-edit me-2"></i> Modificar Usuario
                            </h5>
                            <button @click="cerrarModalNuevo()" type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-2">
                                <label for="personal">Personal</label>
                                <input v-model="usuario.personal" type="text" class="form-control" id="personal"
                                    autocomplete="off" required>
                                <small v-if="(usuario.personal=='' || usuario.personal==null) && !validar_guardar"
                                    class="text-danger">
                                    * Debe ingresar un nombre para el personal
                                </small>

                            </div>

                            <div class="form-group mb-2">
                                <label for="ci">CI</label>
                                <input v-model="usuario.ci" type="number" class="form-control" id="ci"
                                    autocomplete="off" required>
                                <small v-if="(usuario.ci=='' || usuario.ci==null) && !validar_guardar"
                                    class="text-danger">
                                    * Debe ingresar un CI
                                </small>

                            </div>

                            <div class="form-group mb-2">
                                <label for="telefono">Telefono</label>
                                <input v-model="usuario.telefono" type="number" class="form-control" id="telefono"
                                    autocomplete="off" required>
                                <small v-if="(usuario.telefono=='' || usuario.telefono==null) && !validar_guardar"
                                    class="text-danger">
                                    * Debe ingresar un nr. de telefono
                                </small>

                            </div>

                            <div class="form-group mb-2">
                                <label for="nombre">Usuario</label>
                                <input v-model="usuario.nombre" type="text" class="form-control" id="nombre"
                                    autocomplete="off" required>
                                <small v-if="(usuario.nombre=='' || usuario.nombre==null) && !validar_guardar"
                                    class="text-danger">
                                    * Debe ingresar un nombre para el usuario
                                </small>

                            </div>

                            <div class="form-group mb-2">
                                <label for="password">Contraseña</label>
                                <div class="input-group">
                                    <input v-model="usuario.password" :type="ver_pass?'text':'password'"
                                        class="form-control" id="password" autocomplete="off" required>
                                    <a @click="ver_pass=!ver_pass" class="btn btn-success">
                                        <i :class="ver_pass?'fas fa-eye-slash': 'fas fa-eye'"></i>
                                    </a>
                                </div>
                                <small v-if="(usuario.password=='' || usuario.password==null) && !validar_guardar"
                                    class="text-danger">
                                    * Debe ingresar una contraseña
                                </small>

                            </div>

                            <div class="form-group mb-2">
                                <label for="rol">Rol</label>
                                <select v-model="usuario.id_rol" class="form-select" :required="true" id="rol">
                                    <option value="0" selected hidden disabled>Seleccione un rol</option>
                                    <option v-for="item in lista_roles" :value="item.id" :key="item.id">
                                        {{ item.nombre }}</option>
                                </select>
                                <small v-if="(usuario.id_rol=='' || usuario.id_rol==null) && !validar_guardar"
                                    class="text-danger">
                                    * Debe seleccionar un rol
                                </small>
                            </div>

                            <div class="form-group mb-2">
                                <label for="dias_vigencia">Días de vigencia de contraseña</label>
                                <input v-model="usuario.dias_vigencia" type="number" class="form-control" id="dias_vigencia" 
                                    placeholder="Ej: 90" required>
                                <small class="text-muted" style="font-size: 11px;">
                                    Días antes de pedir cambio. (Por defecto 90)
                                </small>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button @click="cerrarModalNuevo()" type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">
                                <i class="fas fa-times-circle"></i>
                                Cerrar</button>
                            <button :disabled="guardando_usuario" v-if="usuario.accion==0" @click="guardarUsuario();"
                                type="button" class="btn btn-success">
                                <i class="fas fa-save"></i>
                                Guardar</button>
                            <button v-if="usuario.accion==1" @click="modificarUsuario();" type="button"
                                class="btn btn-success">
                                <i class="fas fa-save"></i>
                                Modificar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Elemento donde se mostrará el toast -->
        <div class="position-fixed top-0 end-0 toast" style="z-index: 1050" ref="miToast" role="alert"
            aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="2000">
            <div class="toast-header bg-danger" style="border:none">
                <strong class="me-auto text-white">{{ mensajeError }}</strong>
                <button type="button" class="btn btn-danger text-white" @click="cerrarToastError()" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </main>

    <!-- End Page-content -->
</template>

<script>
    import axios from 'axios';
    import Swal from 'sweetalert2'

    export default {
        data() {
            return {
                criterio:'users.personal',
                buscar:'',
                ver_pass:false,
                guardando_usuario:false,
                lista_usuarios: [],
                lista_roles: [],

                usuario: {
                    id_usuario: 0,
                    personal: '',
                    ci: '',
                    telefono: '',
                    nombre: '',
                    email: '',
                    password: '',
                    id_rol: 0,
                    accion: 0,
                    dias_vigencia: 90, // <--- AGREGAR ESTO (Valor por defecto)
                    accion: 0,
                },
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 2,
                mensajeError: '',
                validar_guardar:true,


            }
        },
        computed:{
            isActived: function(){
                return this.pagination.current_page;
            },
            pagesNumber: function(){
                if(!this.pagination.to){
                    return [];
                }                
                var from = this.pagination.current_page - this.offset;
                if(from < 1){
                    from = 1;
                }
                var to = from + (this.offset * 2);
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }
                var pagesArray = [];
                while(from <= to){
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;
            },
        },
        methods: {
            buscarUsuario(){
                this.getUsuarios(1);
            },
            cambiarPagina(page){
                let me=this;
                me.pagination.current_page=page;
                me.getUsuarios(page);
            },
            abrirModalNuevo() {
                $('#nuevoUsuario').modal('show');
                this.lista_roles_agregar = [];
                this.usuario.personal = '';
                this.usuario.ci = '';
                this.usuario.telefono = '';
                this.usuario.nombre = '';
                this.usuario.password = '';
                this.usuario.email = '';
                this.usuario.id_rol = 0;
                this.usuario.accion = 0;
                this.usuario.dias_vigencia = 90;
                this.validar_guardar = true;
                //this.getUsuarios(1);
                this.getRoles();
            },
            activar(usuario) {
                axios.post('/activar_usuario', { id_usuario: usuario.id }).then((response) => {
                        console.log(response);
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
                    .finally(() => {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Operación exitosa',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        this.getUsuarios(1);
                    })
            },
            desactivar(usuario) {
                axios.post('/desactivar_usuario', { id_usuario: usuario.id }).then((response) => {
                        console.log(response);
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
                    .finally(() => {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Operación exitosa',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        this.getUsuarios(1);
                    })
            },
            getUsuarios(page) {
                axios.get('/get_usuarios?page='+page+'&criterio='+this.criterio+'&buscar='+this.buscar).then((response) => {
                        this.lista_usuarios = response.data.data;
                        this.pagination={total:response.data.total, 
                            current_page:response.data.current_page,
                            per_page: response.data.per_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                        }
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
            },

            getRoles() {
                axios.get('/get_roles_usuarios').then((response) => {
                        this.lista_roles = response.data;
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
            },



            cerrarModalNuevo() {
                $('#nuevoUsuario').modal('hide');
            },


            mostrarToastError(mensaje) {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                this.mensajeError = mensaje;
                miToast.show();
            },
            cerrarToastError() {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                miToast.hide();
            },
            editarUsuario(item) {
                this.usuario.accion = 1;
                this.usuario.id_usuario = item.id;
                this.usuario.personal = item.personal;
                this.usuario.ci = item.ci;
                this.usuario.telefono = item.telefono;
                this.usuario.nombre = item.name;
                this.usuario.email = item.email;
                this.usuario.estado = item.estado;
                this.usuario.id_rol = item.id_rol;

                this.usuario.id_rol = item.id_rol;
                this.usuario.dias_vigencia = item.dias_vigencia; // <--- CARGAR EL DATO

                this.usuario.password = '';
                this.validar_guardar = true;
                $('#nuevoUsuario').modal('show');
                this.getRoles();


            },
            guardarUsuario() {
                
                let url = "/save_usuario"

                if(this.usuario.nombre == '' || this.usuario.nombre==null || this.usuario.password==''
                    || this.usuario.password==null || this.usuario.id_rol == 0 || this.usuario.personal==''
                    || this.usuario.personal==null || this.usuario.ci=='' || this.usuario.ci==null
                    || this.usuario.telefono=='' || this.usuario.telefono==null
                ){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Faltan datos por completar',
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar',
                    });
                    this.validar_guardar=false;
                }
                else{
                    this.guardando_usuario=true;
                    axios.post(url, this.usuario)
                    .then((response) => {
                        console.log(response);
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
                    .finally(() => {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Usuario registrado',
                            showConfirmButton: false,
                            timer: 1000
                        });

                        $('#nuevoUsuario').modal('hide');
                        this.getUsuarios(1);
                        this.guardando_usuario = false;
                    })
                }
            },

            modificarUsuario() {
                let url = "/modify_usuario"
                this.usuario.accion = 1;
                if (this.usuario.nombre == '' || this.usuario.nombre==null || this.usuario.password==''
                    || this.usuario.password==null || this.usuario.id_rol == 0 || this.usuario.personal==''
                    || this.usuario.personal==null || this.usuario.ci=='' || this.usuario.ci==null
                    || this.usuario.telefono=='' || this.usuario.telefono==null) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Faltan datos por completar',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar',
                        });
                    this.validar_guardar=false;
                } 
                
                else {
                   
                    axios.post(url, this.usuario).then((response) => {
                        console.log(response);
                    })
                        .catch((error) => {
                            console.log(error.message)
                        })
                        .finally(() => {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Operación exitosa',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#nuevoUsuario').modal('hide');
                            this.getUsuarios(1);

                        })
                }
            },

        },
        mounted() {
            console.log('Component mounted.');
            this.getUsuarios(1);
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

/* Estilo para select general (ej. en modales) */
.form-select {
    border: 1px solid #ced4da !important;
    background-color: #ffffff !important;
    color: #495057 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
    padding-right: 2rem !important;
}
.form-select:focus {
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