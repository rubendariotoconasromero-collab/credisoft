<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-success py-2">
                        <h5 class="header-title my-0 text-center fw-semibold text-white text-uppercase">
                            Configuración de datos
                        </h5>
                    </div>
                    <!-- Bootstrap 5 Tabs -->
                    <ul class="nav nav-tabs my-3 d-flex justify-content-center" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{ 'active': vista == 0 }" id="ingresos-tab"
                                data-bs-toggle="tab" data-bs-target="#ingresos" type="button" role="tab"
                                aria-controls="ingresos" aria-selected="true" @click="cambiarVista(0)">Motivos
                                Ingresos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{ 'active': vista == 1 }" id="egresos-tab"
                                data-bs-toggle="tab" data-bs-target="#egresos" type="button" role="tab"
                                aria-controls="egresos" aria-selected="false" @click="cambiarVista(1)">Motivos
                                Egresos</button>
                        </li>
                    </ul>
                    <div class="card-body">


                        <div class="tab-content" id="configTabsContent">
                            <!-- Motivos Ingresos Tab -->
                            <div class="tab-pane fade" :class="{ 'show active': vista == 0 }" id="ingresos"
                                role="tabpanel" aria-labelledby="ingresos-tab">
                                <div class="row mb-3">
                                    <div class="col-md-12 d-flex flex-row align-items-center">
                                        <h6 class="my-0">Listado de motivos ingresos</h6>
                                        <button @click="nuevoMotivoIngreso()" class="btn btn-success ms-3 my-0">
                                            <i class="fas fa-plus-circle"></i> Nuevo motivo ingreso
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive mt-3" style="font-size:13px;">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="bg-primary text-white text-uppercase">
                                            <tr class="table-success">
                                                <th class="text-uppercase fw-bold">Motivo Ingreso</th>
                                                <th class="text-uppercase fw-bold text-center">Estado</th>
                                                <th class="text-uppercase fw-bold text-center">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in motivos_ingreso" :key="item.id">
                                                <td class="text-uppercase fw-bold">{{ item.nombre }}</td>
                                                <td class="text-uppercase text-center">
                                                    <span v-if="item.estado === 0"
                                                    class="text-white text-uppercase badge bg-success badge-fixed-width" style="width:110px;"
                                                    >Activo</span>
                                                    <span v-else 
                                                    class="text-white text-uppercase badge bg-danger badge-fixed-width" style="width:110px;"
                                                    >Inactivo</span>
                                                </td>
                                                <td class="text-uppercase text-center">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;"
                                                            class="dropdown-toggle my-0 py-0 text-success"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-4"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li @click="desactivarMotivoIngreso(item)"
                                                                v-if="item.estado === 0">
                                                                <a class="dropdown-item text-danger" href="#"><i
                                                                        class="fas fa-times"></i> Desactivar</a>
                                                            </li>
                                                            <li @click="activarMotivoIngreso(item)" v-else>
                                                                <a class="dropdown-item text-success" href="#"><i
                                                                        class="fas fa-check"></i> Activar</a>
                                                            </li>
                                                            <li @click="editarMotivoIngreso(item)">
                                                                <a class="dropdown-item text-info" href="#"><i
                                                                        class="fas fa-pencil-alt"></i> Editar</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <template v-if="motivos_ingreso.length < 10">
                                        <br><br><br><br><br>
                                    </template>
                                </div>
                            </div>

                            <!-- Motivos Egresos Tab -->
                            <div class="tab-pane fade" :class="{ 'show active': vista == 1 }" id="egresos"
                                role="tabpanel" aria-labelledby="egresos-tab">
                                <div class="row mb-3">
                                    <div class="col-md-12 d-flex flex-row align-items-center">
                                        <h6 class="my-0">Listado de motivos egresos</h6>
                                        <button @click="nuevoMotivoGasto()" class="btn btn-success ms-3 my-0">
                                            <i class="fas fa-plus-circle"></i> Nuevo motivo egreso
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive mt-3" style="font-size:13px;">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="bg-primary text-white text-uppercase">
                                            <tr class="table-success">
                                                <th class="text-uppercase fw-bold">Motivo Egreso</th>
                                                <th class="text-uppercase fw-bold">Estado</th>
                                                <th class="text-uppercase fw-bold">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in motivos_gasto" :key="item.id">
                                                <td class="text-uppercase fw-bold">{{ item.nombre }}</td>
                                                <!-- <td class="text-uppercase">
                                                    <span v-if="item.estado === 0" class="text-success">Activo</span>
                                                    <span v-else class="text-danger">Inactivo</span>
                                                </td> -->
                                                <td class="text-uppercase">
                                                    <span v-if="item.estado === 0"
                                                        class="badge bg-success d-inline-block"
                                                        style="min-width: 80px;">Activo</span>
                                                    <span v-else class="badge bg-danger d-inline-block"
                                                        style="min-width: 80px;">Inactivo</span>
                                                </td>
                                                <td class="text-uppercase">
                                                    <div class="btn-group">
                                                        <a style="cursor:pointer;"
                                                            class="dropdown-toggle my-0 py-0 text-success"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-4"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li @click="desactivarMotivoGasto(item)"
                                                                v-if="item.estado === 0">
                                                                <a class="dropdown-item text-danger" href="#"><i
                                                                        class="fas fa-times"></i>
                                                                    Desactivar</a>
                                                            </li>
                                                            <li @click="activarMotivoGasto(item)" v-else>
                                                                <a class="dropdown-item text-success" href="#"><i
                                                                        class="fas fa-check"></i>
                                                                    Activar</a>
                                                            </li>
                                                            <li @click="editarMotivoGasto(item)">
                                                                <a class="dropdown-item text-info" href="#"><i
                                                                        class="fas fa-pencil-alt"></i>
                                                                    Editar</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <template v-if="motivos_gasto.length < 10">
                                        <br><br><br><br><br>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Motivo Ingreso -->
        <div class="modal fade" id="modalMotivoIngreso" tabindex="-1" aria-labelledby="modalLabelIngreso"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="modalLabelIngreso">{{ motivo_ingreso.accion == 1 ?
                            'Editar' :
                            'Nuevo' }} Motivo de Ingreso</h5>
                        <button @click="cancelarGuardarMotivoIngreso()" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form
                            @submit.prevent="motivo_ingreso.accion == 0 ? guardarMotivoIngreso() : modificarMotivoIngreso()">
                            <div class="mb-3">
                                <label for="nombreIngreso" class="form-label">Nombre</label>
                                <input type="text" class="form-control text-uppercase" id="nombreIngreso"
                                    v-model="motivo_ingreso.nombre" required />
                            </div>
                            <div class="modal-footer">
                                <button @click="cancelarGuardarMotivoIngreso()" type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success position-relative" :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    {{ motivo_ingreso.accion == 1 ? 'Actualizar' : 'Guardar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Motivo Gasto -->
        <div class="modal fade" id="modalMotivoGasto" tabindex="-1" aria-labelledby="modalLabelGasto"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border border-2 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="modalLabelGasto">{{ motivo_gasto.accion == 1 ? 'Editar' :
                            'Nuevo'
                            }} Motivo de Gasto</h5>
                        <button @click="cancelarGuardarMotivoGasto()" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form
                            @submit.prevent="motivo_gasto.accion == 0 ? guardarMotivoGasto() : modificarMotivoGasto()">
                            <div class="mb-3">
                                <label for="nombreGasto" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombreGasto" v-model="motivo_gasto.nombre"
                                    required />
                            </div>
                            <div class="modal-footer">
                                <button @click="cancelarGuardarMotivoGasto()" type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success position-relative" :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    {{ motivo_gasto.accion == 1 ? 'Actualizar' : 'Guardar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        


    </main>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            
            preloader: false,
            loading: false, // New state for button loaders
            vista: 0,
            motivos_ingreso: [],
            motivos_gasto: [],
            motivo_ingreso: {
                id: 0,
                nombre: '',
                accion: 0,
            },
            motivo_gasto: {
                id: 0,
                nombre: '',
                accion: 0,
            },
        };
    },

    methods: {
        async cambiarVista(vista) {
            this.vista = vista;
        },

        async modificarMotivoIngreso() {
            try {
                this.loading = true;
                const response = await axios.post('/modificar_motivo_ingreso', this.motivo_ingreso);
                Swal.fire({
                    title: 'Modificado exitosamente',
                    text: 'El motivo de ingreso ha sido modificado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosIngreso();
                this.cerrarModalMotivoIngreso();
            } catch (error) {
                console.error('Error: ', error.message);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al modificar el motivo de ingreso. Por favor, inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
                this.preloader = false;
            }
        },

        async modificarMotivoGasto() {
            try {
                this.loading = true;
                const response = await axios.post('/modificar_motivo_gasto', this.motivo_gasto);
                Swal.fire({
                    title: 'Modificado exitosamente',
                    text: 'El motivo de gasto ha sido modificado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosGastos();
                this.cerrarModalMotivoGasto();
            } catch (error) {
                console.error('Error: ', error.message);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al modificar el motivo de gasto. Por favor, inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
                this.preloader = false;
            }
        },

        async guardarMotivoIngreso() {
            try {
                this.loading = true;
                const response = await axios.post('/guardar_motivo_ingreso', this.motivo_ingreso);
                Swal.fire({
                    title: 'Guardado exitosamente',
                    text: 'El motivo de ingreso ha sido registrado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosIngreso();
                this.cerrarModalMotivoIngreso();
            } catch (error) {
                console.error('Error: ', error.message);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al guardar el motivo de ingreso. Por favor, inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
                this.preloader = false;
            }
        },

        async guardarMotivoGasto() {
            try {
                this.loading = true;
                const response = await axios.post('/guardar_motivo_gasto', this.motivo_gasto);
                Swal.fire({
                    title: 'Guardado exitosamente',
                    text: 'El motivo de gasto ha sido registrado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosGastos();
                this.cerrarModalMotivoGasto();
            } catch (error) {
                console.error('Error: ', error.message);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al guardar el motivo de gasto. Por favor, inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
                this.preloader = false;
            }
        },

        async nuevoMotivoIngreso() {
            this.motivo_ingreso.nombre = '';
            this.motivo_ingreso.accion = 0;
            this.abrirModalMotivoIngreso();
        },

        async cancelarGuardarMotivoIngreso() {
            this.motivo_ingreso.nombre = '';
            this.cerrarModalMotivoIngreso();
        },

        async cerrarModalMotivoIngreso() {
            $('#modalMotivoIngreso').modal('hide');
        },

        async abrirModalMotivoIngreso() {
            $('#modalMotivoIngreso').modal('show');
        },

        async nuevoMotivoGasto() {
            this.motivo_gasto.nombre = '';
            this.motivo_gasto.accion = 0;
            this.abrirModalMotivoGasto();
        },

        async cancelarGuardarMotivoGasto() {
            this.motivo_gasto.nombre = '';
            this.cerrarModalMotivoGasto();
        },

        async cerrarModalMotivoGasto() {
            $('#modalMotivoGasto').modal('hide');
        },

        async abrirModalMotivoGasto() {
            $('#modalMotivoGasto').modal('show');
        },

        async obtenerMotivosIngreso() {
            try {
                this.preloader = true;
                const response = await axios.get('/get_motivos_ingresos');
                this.motivos_ingreso = response.data;
            } catch (error) {
                console.error('Error al obtener motivos de ingreso', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al cargar los motivos de ingreso.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.preloader = false;
            }
        },

        async activarMotivoIngreso(item) {
            try {
                this.preloader = true;
                this.motivo_ingreso.id = item.id;
                await axios.post('/activar_motivo_ingreso', this.motivo_ingreso);
                Swal.fire({
                    title: 'Se activó el motivo ingreso',
                    text: 'El motivo ingreso ha sido activado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosIngreso();
            } catch (error) {
                console.error('Error al activar el motivo ingreso', error);
                Swal.fire({
                    title: 'Ha ocurrido un error',
                    text: 'Error al activar el motivo ingreso.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.preloader = false;
            }
        },

        async desactivarMotivoIngreso(item) {
            try {
                this.preloader = true;
                this.motivo_ingreso.id = item.id;
                await axios.post('/desactivar_motivo_ingreso', this.motivo_ingreso);
                Swal.fire({
                    title: 'Se desactivó el motivo ingreso',
                    text: 'El motivo ingreso ha sido desactivado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosIngreso();
            } catch (error) {
                console.error('Error al desactivar motivo de ingreso', error);
                Swal.fire({
                    title: 'Ha ocurrido un error',
                    text: 'Error al desactivar el motivo ingreso.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.preloader = false;
            }
        },

        async editarMotivoIngreso(item) {
            this.motivo_ingreso.accion = 1;
            this.motivo_ingreso.id = item.id;
            this.motivo_ingreso.nombre = item.nombre;
            this.abrirModalMotivoIngreso();
        },

        async obtenerMotivosGastos() {
            try {
                this.preloader = true;
                const response = await axios.get('/get_motivos_gastos');
                this.motivos_gasto = response.data;
            } catch (error) {
                console.error('Error al obtener motivos de gasto', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al cargar los motivos de gasto.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.preloader = false;
            }
        },

        async activarMotivoGasto(item) {
            try {
                this.preloader = true;
                this.motivo_gasto.id = item.id;
                await axios.post('/activar_motivo_gasto', this.motivo_gasto);
                Swal.fire({
                    title: 'Se activó el motivo gasto',
                    text: 'El motivo gasto ha sido activado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosGastos();
            } catch (error) {
                console.error('Error al activar motivo de gasto', error);
                Swal.fire({
                    title: 'Ha ocurrido un error',
                    text: 'Error al activar el motivo gasto.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.preloader = false;
            }
        },

        async desactivarMotivoGasto(item) {
            try {
                this.preloader = true;
                this.motivo_gasto.id = item.id;
                await axios.post('/desactivar_motivo_gasto', this.motivo_gasto);
                Swal.fire({
                    title: 'Se desactivó el motivo gasto',
                    text: 'El motivo gasto ha sido desactivado correctamente.',
                    icon: 'success',
                    timer: 1200,
                });
                await this.obtenerMotivosGastos();
            } catch (error) {
                console.error('Error al desactivar motivo de gasto', error);
                Swal.fire({
                    title: 'Ha ocurrido un error',
                    text: 'Error al desactivar el motivo gasto.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.preloader = false;
            }
        },

        async editarMotivoGasto(item) {
            this.motivo_gasto.accion = 1;
            this.motivo_gasto.id = item.id;
            this.motivo_gasto.nombre = item.nombre;
            this.abrirModalMotivoGasto();
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
.dropdown-toggle::after {
  display: none !important;
}


.nav-item .nav-link{
    background-color: #ffffff;
    border: 2px solid #4bbf73 !important;
    color:#000000 !important;
    font-weight: 500;
    padding-top:10px;
    padding-bottom:10px;
}

.nav-item .nav-link:hover{
    background-color: #4bbf73 !important;
    border: 2px solid #4bbf73;
    color:#ffffff !important;
    font-weight: 500;
    padding-top:10px;
    padding-bottom:10px;
    transition: none;
}

.nav-item .nav-link.active{
    background-color: #4bbf73 !important;
    border: 2px solid #4bbf73;
    color:#ffffff !important;
    font-weight: 500;
    padding-top:10px;
    padding-bottom:10px;
    transition: none;
}


.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>