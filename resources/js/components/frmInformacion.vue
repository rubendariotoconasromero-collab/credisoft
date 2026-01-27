<template>
    <main>
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">

            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-warning py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Información de la empresa
                        </h5>
                    </div>
                    <div class="card-body">

                        <!-- <h4 class="card-title">Informacion de la empresa</h4> -->
                        <form @submit.prevent="modificarInformacionEmpresa()">

                            <!-- Agrega esto si estás utilizando Laravel para proteger contra CSRF -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input :disabled="habilitar" v-model="mi_empresa.nombre" type="text"
                                            class="form-control" id="nombre" name="nombre" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nit" class="form-label">NIT</label>
                                        <input :disabled="habilitar" v-model="mi_empresa.nit" type="text"
                                            class="form-control" id="nit" name="nit" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input :disabled="habilitar" v-model="mi_empresa.direccion" type="text"
                                            class="form-control" id="direccion" name="direccion" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input :disabled="habilitar" v-model="mi_empresa.telefono" type="text"
                                            class="form-control" id="telefono" name="telefono" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <input :disabled="habilitar" v-model="mi_empresa.email" type="email"
                                            class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo</label>
                                        <input :disabled="habilitar" type="file" class="form-control" id="imagen"
                                            name="imagen" accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                                            @change="previewImage($event)" ref="imagenInput">
                                    </div>
                                    <div class="mb-3">
                                        <img :src="logoPreview" alt="Vista previa del logo" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-end">
                                    <button v-if="habilitar == true" @click="habilitarModificar()" type="button"
                                        class="btn btn-warning text-dark ms-1">Modificar</button>
                                    <template v-if="habilitar == false">
                                        <button type="submit" class="btn btn-success ms-1">
                                            <i class="fas fa-save"></i>
                                            Guardar</button>
                                        <button @click="habilitar = true" type="button" class="btn btn-dark ms-1">
                                            <i class="fas fa-times-circle"></i>
                                            Cancelar</button>
                                    </template>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- End Cardbody -->
                </div>
                <!-- end page-content-wrapper-->
            </div>
            <!-- Container-fluid -->
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

                preloader: false,
                mi_empresa: {
                    id_mi_empresa: 0,
                    nombre: '',
                    nit: '',
                    direccion: '',
                    email: '',
                    telefono: '',
                    logo: '',
                    imagen_logo: null,
                },
                mensajeError: '',
                logoPreview:'',
                habilitar:true,

            }
        },
        methods: {

            previewImage(event) {
                const file = event.target.files[0];
                console.log(file.name);
                this.mi_empresa.logo=file.name;
                if (file) {
                    this.logoPreview = URL.createObjectURL(file);
                } else {
                    this.logoPreview = ''; // Vacía la vista previa si no se selecciona ninguna imagen
                }
            },
            habilitarModificar(){
                this.habilitar=false;
            },
            async getinformacionEmpresa() {
                
                await axios.get('/get_mi_empresa').then((response) => {
                            console.log(response.data);
                            this.mi_empresa.id_mi_empresa= response.data['id'];
                            this.mi_empresa.nombre= response.data['nombre']
                            this.mi_empresa.nit= response.data['nit'];
                            this.mi_empresa.direccion= response.data['direccion'];
                            this.mi_empresa.email= response.data['email'];
                            this.mi_empresa.telefono= response.data['telefono'];
                            this.mi_empresa.logo= response.data['logo'];
                            this.logoPreview='img/'+this.mi_empresa.logo;
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
                    .finally(()=>{
                        
                    })
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



            async modificarInformacionEmpresa() {

                const formData = new FormData();
                formData.append('imagen', this.$refs.imagenInput.files[0]);
                formData.append('id_mi_empresa', this.mi_empresa.id_mi_empresa);
                formData.append('nombre', this.mi_empresa.nombre);
                formData.append('nit', this.mi_empresa.nit);
                formData.append('direccion', this.mi_empresa.direccion);
                formData.append('email', this.mi_empresa.email);
                formData.append('telefono', this.mi_empresa.telefono);
                formData.append('logo', this.mi_empresa.logo);
              
       
                var guardado=false;
               
                await axios.post('/modify_miempresa', formData).then((response) => {
                        console.log(response);
                        guardado=true;
                   
                    })
                    .catch((error) => {
                        console.log(error.message);
                        
                    })
                    .finally(()=>{
                        if(guardado){
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Operación exitosa',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            this.getinformacionEmpresa();
                            this.habilitar=true;
                        }
                    })
            },


            async modificarInformacionEmpresa() {
                const formData = new FormData();

                // Campos obligatorios
                formData.append('id_mi_empresa', this.mi_empresa.id_mi_empresa);
                formData.append('nombre', this.mi_empresa.nombre);
                formData.append('nit', this.mi_empresa.nit);
                formData.append('direccion', this.mi_empresa.direccion);
                formData.append('email', this.mi_empresa.email);
                formData.append('telefono', this.mi_empresa.telefono);

                // Solo agregar imagen si se seleccionó un archivo
                const fileInput = this.$refs.imagenInput;
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append('imagen', fileInput.files[0]);
                }

                // Opcional: si el logo es parte del modelo (y no se modifica), puedes enviarlo también
                if (this.mi_empresa.logo) {
                    formData.append('logo', this.mi_empresa.logo);
                }

                try {
                    const response = await axios.post('/modify_miempresa', formData);
                    console.log(response);

                    // Éxito
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Operación exitosa',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    await this.getinformacionEmpresa();
                    this.habilitar = true;

                } catch (error) {
                    // Manejo de errores
                    let errorMessage = 'Error al actualizar la información.';
                    if (error.response) {
                        // El servidor respondió con un código de error (ej: 422, 500)
                        console.log('Error en respuesta:', error.response.data);
                        errorMessage = error.response.data.message || errorMessage;
                    } else if (error.request) {
                        // No hubo respuesta del servidor
                        errorMessage = 'No se pudo conectar con el servidor.';
                    } else {
                        // Otro tipo de error
                        console.log('Error desconocido:', error.message);
                        errorMessage = error.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Ha ocurrido un error...',
                        text: errorMessage,
                        confirmButtonText: 'Cerrar'
                    });

                } finally {
                    // Acciones adicionales al final, si las necesitas
                }
            },

        },
        async mounted() {
            this.preloader=true;
            console.log('Component mounted.');
            await this.getinformacionEmpresa();
            this.preloader=false;

        }


    }

</script>

<style scoped>
    .preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
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
</style>
