<template>
    <main>
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="page-content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card shadow-sm border-0">
                            <!-- Card Header - Manteniendo el estilo bg-warning del proyecto -->
                            <div class="card-header bg-warning py-2">
                                <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                                    <i class="fas fa-info-circle me-2"></i>Información de la empresa
                                </h5>
                            </div>

                            <div class="card-body p-4">
                                <form @submit.prevent="modificarInformacionEmpresa">
                                    <div class="row">
                                        <!-- Lado Izquierdo: Campos de Texto -->
                                        <div class="col-md-7 border-end">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label for="nombre" class="form-label fw-bold small text-muted text-uppercase">Nombre de la Institución</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                                                        <input :disabled="habilitar" v-model="mi_empresa.nombre" type="text"
                                                            class="form-control text-uppercase" id="nombre" placeholder="Nombre completo" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="nit" class="form-label fw-bold small text-muted text-uppercase">NIT</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fas fa-file-invoice text-muted"></i></span>
                                                        <input :disabled="habilitar" v-model="mi_empresa.nit" type="text"
                                                            class="form-control" id="nit" placeholder="Número de NIT" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="telefono" class="form-label fw-bold small text-muted text-uppercase">Teléfono / Celular</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                                        <input :disabled="habilitar" v-model="mi_empresa.telefono" type="text"
                                                            class="form-control" id="telefono" placeholder="Ej: 70000000" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <label for="email" class="form-label fw-bold small text-muted text-uppercase">Correo Electrónico</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                                        <input :disabled="habilitar" v-model="mi_empresa.email" type="email"
                                                            class="form-control" id="email" placeholder="ejemplo@correo.com" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <label for="direccion" class="form-label fw-bold small text-muted text-uppercase">Dirección</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                                        <input :disabled="habilitar" v-model="mi_empresa.direccion" type="text"
                                                            class="form-control text-uppercase" id="direccion" placeholder="Calle, número, zona..." required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lado Derecho: Logo Preview -->
                                        <div class="col-md-5">
                                            <div class="px-3">
                                                <label class="form-label fw-bold small text-muted text-uppercase d-block text-center mb-3">Logo Institucional</label>
                                                
                                                <div class="text-center mb-4">
                                                    <div class="d-inline-block p-2 border rounded bg-white shadow-sm">
                                                        <img :src="logoPreview" alt="Logo preview" class="img-fluid" 
                                                            style="max-height: 180px; width: auto; object-fit: contain;">
                                                    </div>
                                                </div>

                                                <div class="mb-3" v-if="!habilitar">
                                                    <input type="file" class="form-control form-control-sm" id="imagen"
                                                        name="imagen" accept="image/*"
                                                        @change="previewImage($event)" ref="imagenInput">
                                                    <div class="form-text small">Tamaño recomendado: 500x500px (PNG/JPG)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer Actions -->
                                    <div class="row mt-4 pt-3 border-top">
                                        <div class="col-md-12 text-center">
                                            <button v-if="habilitar" @click="habilitarModificar" type="button"
                                                class="btn btn-warning px-4 fw-bold shadow-sm">
                                                <i class="fas fa-edit me-1"></i> MODIFICAR INFORMACIÓN
                                            </button>
                                            
                                            <template v-else>
                                                <button type="submit" class="btn btn-success px-4 fw-bold me-2 shadow-sm">
                                                    <i class="fas fa-save me-1"></i> GUARDAR CAMBIOS
                                                </button>
                                                <button @click="habilitar = true" type="button" class="btn btn-dark px-4 shadow-sm">
                                                    <i class="fas fa-times-circle me-1"></i> CANCELAR
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </form>
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
                preloader: false,
                mi_empresa: {
                    id_mi_empresa: 0,
                    nombre: '',
                    nit: '',
                    direccion: '',
                    email: '',
                    telefono: '',
                    logo: '',
                },
                logoPreview: '',
                habilitar: true,
            }
        },
        methods: {
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.mi_empresa.logo = file.name;
                    this.logoPreview = URL.createObjectURL(file);
                }
            },
            
            habilitarModificar() {
                this.habilitar = false;
            },

            async getinformacionEmpresa() {
                try {
                    const response = await axios.get('/get_mi_empresa');
                    const data = response.data;
                    this.mi_empresa = {
                        id_mi_empresa: data.id,
                        nombre: data.nombre,
                        nit: data.nit,
                        direccion: data.direccion,
                        email: data.email,
                        telefono: data.telefono,
                        logo: data.logo,
                    };
                    this.logoPreview = data.logo ? 'img/' + data.logo : '/img/default-company.png';
                } catch (error) {
                    console.error('Error fetching company info:', error);
                }
            },

            async modificarInformacionEmpresa() {
                const formData = new FormData();
                formData.append('id_mi_empresa', this.mi_empresa.id_mi_empresa);
                formData.append('nombre', this.mi_empresa.nombre);
                formData.append('nit', this.mi_empresa.nit);
                formData.append('direccion', this.mi_empresa.direccion);
                formData.append('email', this.mi_empresa.email);
                formData.append('telefono', this.mi_empresa.telefono);

                const fileInput = this.$refs.imagenInput;
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append('imagen', fileInput.files[0]);
                }

                if (this.mi_empresa.logo) {
                    formData.append('logo', this.mi_empresa.logo);
                }

                try {
                    this.preloader = true;
                    await axios.post('/modify_miempresa', formData);
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Información actualizada correctamente',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    await this.getinformacionEmpresa();
                    this.habilitar = true;
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar la información.',
                    });
                } finally {
                    this.preloader = false;
                }
            },
        },
        async mounted() {
            this.preloader = true;
            await this.getinformacionEmpresa();
            this.preloader = false;
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
        background-color: rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .form-control:disabled {
        background-color: #e9ecef !important;
        border-color: #dee2e6 !important;
        color: #495057 !important;
        opacity: 1; /* Para evitar la transparencia por defecto de algunos navegadores */
        cursor: not-allowed;
    }

    .input-group-text {
        border-right: none;
        transition: background-color 0.3s ease;
    }

    /* Estilo para el icono/addon cuando el input está deshabilitado */
    .input-group:has(.form-control:disabled) .input-group-text {
        background-color: #e2e5e8 !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
    }

    .form-control {
        border-left: none;
    }

    .input-group:focus-within .input-group-text {
        border-color: #86b7fe;
        background-color: #fff;
    }
</style>
