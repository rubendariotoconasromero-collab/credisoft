<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-warning bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Mi Perfil
                        </h5>
                    </div>
    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-header bg-info text-white fw-bold">
                                        <i class="fas fa-id-card me-1"></i> Información Personal
                                    </div>
                                    <div class="card-body">
                                        <form @submit.prevent="actualizarInfo">
                                            
                                            <div class="text-center mb-4">
                                                <div class="avatar-circle bg-light text-info mx-auto mb-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <h5 class="fw-bold mb-0">{{ usuario.personal }}</h5>
                                                <span class="badge bg-secondary">{{ rol_nombre }}</span>
                                                
                                                <div v-if="dias_restantes !== null" class="mt-2">
                                                    <span v-if="dias_restantes > 7" class="text-success small fw-bold">
                                                        <i class="fas fa-check-circle"></i> Contraseña vigente ({{ dias_restantes }} días)
                                                    </span>
                                                    <span v-else class="text-danger small fw-bold animation-blink">
                                                        <i class="fas fa-exclamation-circle"></i> Contraseña vence en {{ dias_restantes }} días
                                                    </span>
                                                </div>
                                            </div>
        
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label small text-muted">Nombre Completo (Personal)</label>
                                                    <input v-model="usuario.personal" type="text" class="form-control" required>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">C.I.</label>
                                                    <input v-model="usuario.ci" type="text" class="form-control bg-light" readonly title="Contacte al administrador para cambiar su CI">
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Teléfono</label>
                                                    <input v-model="usuario.telefono" type="text" class="form-control">
                                                </div>
        
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Nombre de Usuario (Login)</label>
                                                    <input v-model="usuario.name" type="text" class="form-control" required>
                                                </div>
        
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Email</label>
                                                    <input v-model="usuario.email" type="email" class="form-control">
                                                </div>
                                            </div>
        
                                            <div class="d-grid gap-2 mt-4">
                                                <button type="submit" class="btn btn-info text-white" :disabled="loadingInfo">
                                                    <i v-if="loadingInfo" class="fas fa-spinner fa-spin"></i>
                                                    <span v-else><i class="fas fa-save me-1"></i> Actualizar Datos</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
        
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-header bg-warning text-dark fw-bold">
                                        <i class="fas fa-key me-1"></i> Seguridad y Contraseña
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-light border mb-4 small">
                                            <i class="fas fa-info-circle text-warning me-1"></i>
                                            Se recomienda cambiar su contraseña cada 90 días. La nueva contraseña debe tener al menos 6 caracteres.
                                        </div>
        
                                        <form @submit.prevent="actualizarPassword">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Contraseña Actual</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white"><i class="fas fa-lock"></i></span>
                                                    <input v-model="pass.current_password" :type="verActual ? 'text' : 'password'" class="form-control" placeholder="Ingrese su contraseña actual" required>
                                                    <button type="button" class="btn btn-outline-secondary" @click="verActual = !verActual">
                                                        <i :class="verActual ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                    </button>
                                                </div>
                                                <small v-if="errors.current_password" class="text-danger">{{ errors.current_password[0] }}</small>
                                            </div>
        
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Nueva Contraseña</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white"><i class="fas fa-key"></i></span>
                                                    <input v-model="pass.new_password" :type="verNueva ? 'text' : 'password'" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                                                    <button type="button" class="btn btn-outline-secondary" @click="verNueva = !verNueva">
                                                        <i :class="verNueva ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                    </button>
                                                </div>
                                            </div>
        
                                            <div class="mb-4">
                                                <label class="form-label small fw-bold">Confirmar Nueva Contraseña</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white"><i class="fas fa-check-circle"></i></span>
                                                    <input v-model="pass.new_password_confirmation" :type="verNueva ? 'text' : 'password'" class="form-control" placeholder="Repita la nueva contraseña" required>
                                                </div>
                                                <small v-if="pass.new_password && pass.new_password_confirmation && pass.new_password !== pass.new_password_confirmation" class="text-danger">
                                                    * Las contraseñas no coinciden
                                                </small>
                                            </div>
        
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-warning text-dark fw-bold" 
                                                    :disabled="loadingPass || (pass.new_password !== pass.new_password_confirmation)">
                                                    <i v-if="loadingPass" class="fas fa-spinner fa-spin"></i>
                                                    <span v-else>Cambiar Contraseña</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
            usuario: {
                id: 0,
                name: '',
                personal: '',
                ci: '',
                telefono: '',
                email: ''
            },
            rol_nombre: '',
            dias_restantes: null,
            
            // Datos para password
            pass: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            
            // UI States
            loadingInfo: false,
            loadingPass: false,
            verActual: false,
            verNueva: false,
            errors: {}
        }
    },
    mounted() {
        this.cargarPerfil();
    },
    methods: {
        async cargarPerfil() {
            try {
                const response = await axios.get('/perfil/get_datos');
                this.usuario = response.data.usuario;
                this.rol_nombre = response.data.rol_nombre;
                this.dias_restantes = response.data.dias_restantes;
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los datos del perfil', 'error');
            }
        },

        async actualizarInfo() {
            this.loadingInfo = true;
            try {
                await axios.post('/perfil/update_info', this.usuario);
                Swal.fire({
                    icon: 'success',
                    title: 'Datos Actualizados',
                    text: 'Tu información personal ha sido guardada.',
                    timer: 1500,
                    showConfirmButton: false
                });
                // Opcional: Recargar página si cambiaste el nombre que sale en el navbar
                // location.reload(); 
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo actualizar la información. Verifique el email (no puede estar duplicado).', 'error');
            } finally {
                this.loadingInfo = false;
            }
        },

        async actualizarPassword() {
            this.loadingPass = true;
            this.errors = {}; // Limpiar errores

            try {
                await axios.post('/perfil/update_password', this.pass);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Contraseña Renovada',
                    text: 'Su contraseña ha sido cambiada exitosamente. La vigencia se ha reiniciado.',
                    confirmButtonText: 'Entendido'
                });

                // Limpiar formulario
                this.pass = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: ''
                };
                
                // Recargar para actualizar el contador de días
                this.cargarPerfil();

            } catch (error) {
                if (error.response && error.response.status === 422) {
                    // Errores de validación (ej. pass actual incorrecta)
                    this.errors = error.response.data.errors;
                    
                    if(this.errors.current_password){
                        Swal.fire('Error', this.errors.current_password[0], 'error');
                    } else {
                        Swal.fire('Error', 'Verifique los datos ingresados.', 'warning');
                    }
                } else {
                    console.error(error);
                    Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
                }
            } finally {
                this.loadingPass = false;
            }
        }
    }
}
</script>

<style scoped>
.avatar-circle {
    background-color: #f8f9fa;
    border: 2px solid #e9ecef;
}
.animation-blink {
    animation: blinker 1.5s linear infinite;
}
@keyframes blinker {
    50% { opacity: 0.5; }
}
</style>