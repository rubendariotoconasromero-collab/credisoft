<template>
    <main>
        <div class="page-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-warning py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Gestión de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <select v-model="criterio" class="form-control form-select">
                                        <option value="users.personal">Nombre personal</option>
                                        <option value="users.ci">CI</option>
                                    </select>
                                    <input :placeholder="'Ingrese texto a buscar'" v-model="buscar" type="text"
                                        class="form-control" @input="buscarUsuario()">
                                    <button class="btn btn-success btn-sm">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button @click="abrirModalNuevo()" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i>
                                    Nuevo usuario
                                </button>
                            </div>

                        </div>

                        <!-- <h6 class="fw-bold">Listado de usuarios</h6> -->
                        <div class="table-responsive" style="font-size:12px">
                            <table class="table table-striped table-hover table-sm ">
                                <thead class="text-white text-uppercase table-success">
                                    <tr>
                                        <th class="text-dark text-center text-uppercase fw-bold">Usuario</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">Personal</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">CI</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">Telefono</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">Rol</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">Vigencia Pass</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">Estado</th>
                                        <th class="text-dark text-center text-uppercase fw-bold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="" v-for="item in lista_usuarios" :key="item.id">

                                        <td class="text-capitalize">{{ item.name }}</td>
                                        <td class="text-capitalize fw-bold">{{ item.personal }}</td>
                                        <td class="text-capitalize fw-bold">{{ item.ci }}</td>
                                        <td class="text-capitalize">{{ item.telefono }}</td>
                                        <td class="text-capitalize">{{ item.rol }}</td>
                                        <td class="text-center">
                                            <div v-if="item.dias_restantes !== null">
                                                <span v-if="item.dias_restantes < 0" class="badge bg-danger">Vencida</span>
                                                <span v-else-if="item.dias_restantes <= 7" class="badge bg-warning text-dark">
                                                    Vence en {{ item.dias_restantes }} días
                                                </span>
                                                <span v-else class="badge bg-success">
                                                    Ok ({{ item.dias_restantes }} días)
                                                </span>
                                            </div>
                                            <span v-else class="text-muted small">Indefinido</span>
                                        </td>
                                        <td class="text-capitalize text-center">
                                            <span v-if="item.estado == 1" class="text-success">Activo</span>
                                            <span v-else class="text-danger">Inactivo</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group my-0 py-0">
                                                <a style="cursor:pointer;"
                                                    class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
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
                                </tbody>
                            </table>
                            <br>
                            <br>
                            <!-- Card Pagination -->
                            <div class="card-footer py-4">
                                <nav>
                                    <ul class="pagination justify-content-end mb-0">
                                        <li class="page-item" v-if="pagination.current_page > 1">
                                            <a class="page-link" href="#"
                                                @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                        </li>
                                        <li class="page-item" v-for="page in pagesNumber" :key="page"
                                            :class="[page == isActived ? 'active' : '']">
                                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page)"
                                                :v-text="page">{{ page }}</a>
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
                        <div class="modal-header bg-warning">
                            <h1 v-if="usuario.accion==0" class="modal-title text-dark fs-5" id="exampleModalLabel">
                                Registro nuevo
                                usuario</h1>
                            <h1 v-if="usuario.accion==1" class="modal-title text-dark fs-5" id="exampleModalLabel">
                                Modificar
                                usuario</h1>
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
                                <select v-model="usuario.id_rol" class="form-control" :required="true" id="rol">
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
                axios.get('/activar_usuario?id_usuario=' + usuario.id).then((response) => {
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
                axios.get('/desactivar_usuario?id_usuario=' + usuario.id).then((response) => {
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
</style>