<template>
    <!-- El ID es único para invocarlo vía Bootstrap -->
    <div class="modal fade" id="modalFichaCliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                
                <!-- Encabezado -->
                <div class="modal-header bg-warning text-white py-2">
                    <h5 class="modal-title fw-bold text-uppercase small text-dark">
                        <i class="fas fa-address-card me-2"></i> Ficha Integral del Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal()"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body bg-light p-0">
                    
                    <!-- A. LOADING STATE -->
                    <div v-if="loading" class="text-center p-5 my-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                        <h6 class="text-muted fw-bold text-uppercase">Recuperando Expediente...</h6>
                    </div>

                    <!-- B. CONTENIDO DEL CLIENTE -->
                    <div v-else-if="cliente">
                        
                        <!-- 1. PERFIL SUPERIOR -->
                        <div class="bg-white p-4 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-auto text-center">
                                    <div class="position-relative d-inline-block">
                                        <img :src="cliente.imagen ? '/img/cliente/' + cliente.imagen : '/img/empresa/user_img2_old.png'" 
                                             class="rounded-circle border border-3 border-light shadow" 
                                             style="width: 90px; height: 90px; object-fit: cover;"
                                             @error="$event.target.src='/img/empresa/user_img2_old.png'">
                                        
                                        <span v-if="cliente.estado == 1" 
                                              class="position-absolute bottom-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle"
                                              title="Activo">
                                        </span>
                                        <span v-else 
                                              class="position-absolute bottom-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"
                                              title="Inactivo">
                                        </span>
                                    </div>
                                </div>
                                <div class="col">
                                    <h4 class="fw-bold text-dark mb-1 text-uppercase">{{ cliente.nombre }}</h4>
                                    <div class="text-muted mb-2 small">
                                        <i class="fas fa-id-card me-1"></i> {{ cliente.ci }} {{ cliente.lugar_expedicion }}
                                        <span class="mx-2 text-secondary">|</span>
                                        <span class="text-uppercase">{{ cliente.vivienda }}</span>
                                    </div>
                                    
                                    <div class="d-flex gap-3 small mt-2">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-birthday-cake me-1 text-danger"></i> 
                                            {{ calcularEdad(cliente.fecha_nacimiento) }} Años
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-venus-mars me-1 text-info"></i> 
                                            {{ cliente.sexo }}
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-ring me-1 text-warning"></i> 
                                            {{ cliente.estado_civil }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <!-- 2. INFO ECONÓMICA -->
                            <h6 class="text-uppercase fw-bold text-primary border-bottom pb-2 mb-3 small">
                                <i class="fas fa-briefcase me-2"></i> Información Económica
                            </h6>
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body bg-white rounded border-start border-4 border-primary">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <label class="small text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Actividad Principal</label>
                                            <div class="text-dark">{{ cliente.actividad }}</div>
                                        </div>
                                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                            <label class="small text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Ingreso Mensual</label>
                                            <div class="fw-bold text-success fs-5">{{ formatNumero(cliente.ingreso_mensual) }} Bs.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- 3. DIRECCIONES -->
                                <div class="col-lg-6">
                                    <h6 class="text-uppercase fw-bold text-primary border-bottom pb-2 mb-3 small">
                                        <i class="fas fa-map-marked-alt me-2"></i> Direcciones
                                    </h6>
                                    
                                    <div v-if="cliente.direcciones && cliente.direcciones.length > 0" class="d-grid gap-2">
                                        <div v-for="dir in cliente.direcciones" :key="dir.id" class="card border shadow-sm">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="badge bg-warning text-dark rounded">{{ dir.tipo }}</span>
                                                    <a v-if="dir.lat && dir.lng" 
                                                       :href="`https://maps.google.com/?q=${dir.lat},${dir.lng}`" 
                                                       target="_blank" 
                                                       class="btn btn-link btn-sm p-0 text-decoration-none">
                                                        <i class="fas fa-external-link-alt"></i> Mapa
                                                    </a>
                                                </div>
                                                <div class="small fw-bold text-dark">{{ dir.departamento }} - {{ dir.ciudad }}</div>
                                                <div class="small text-muted">{{ dir.zona }}</div>
                                                <div class="small mt-1 border-top pt-1">{{ dir.descripcion }}</div>
                                                <div class="small text-muted fst-italic mt-1" v-if="dir.referencia">
                                                    <i class="fas fa-info-circle me-1"></i>Ref: {{ dir.referencia }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="alert alert-light border text-center small text-muted py-3">
                                        <i class="fas fa-map-signs mb-2 d-block fa-2x text-secondary opacity-25"></i>
                                        Sin direcciones registradas
                                    </div>
                                </div>

                                <!-- 4. TELÉFONOS -->
                                <div class="col-lg-6">
                                    <h6 class="text-uppercase fw-bold text-primary border-bottom pb-2 mb-3 small">
                                        <i class="fas fa-phone-alt me-2"></i> Contactos
                                    </h6>

                                    <div v-if="cliente.telefonos && cliente.telefonos.length > 0">
                                        <ul class="list-group shadow-sm">
                                            <li v-for="tel in cliente.telefonos" :key="tel.id" class="list-group-item list-group-item-action p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ tel.numero }}</div>
                                                        <div class="small text-muted">
                                                            <span v-if="tel.nombre">{{ tel.nombre }} {{ tel.apellidos }}</span>
                                                            <span v-if="tel.relacion">({{ tel.relacion }})</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-warning text-dark border">{{ tel.tipo }}</span>
                                                </div>
                                                <div v-if="tel.observacion" class="small text-muted fst-italic mt-1 border-top pt-1">
                                                    Obs: {{ tel.observacion }}
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div v-else class="alert alert-light border text-center small text-muted py-3">
                                        <i class="fas fa-phone-slash mb-2 d-block fa-2x text-secondary opacity-25"></i>
                                        Sin teléfonos registrados
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ERROR STATE -->
                    <div v-else class="text-center p-5">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                        <h6 class="text-danger">No se pudo cargar la información del cliente.</h6>
                    </div>

                </div>
                
                <div class="modal-footer bg-white py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold px-4" @click="cerrarModal()">
                        Cerrar Ficha
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';
import axios from 'axios';

export default {
    name: 'ModalDetalleCliente',
    data() {
        return {
            cliente: null,
            loading: false,
            modalInstance: null // Para guardar la instancia de Bootstrap
        };
    },
    methods: {
        // --- MÉTODO PÚBLICO ---
        async abrirModal(idCliente) {
            this.cliente = null;
            this.loading = true;
            
            // 1. Instanciar y mostrar el modal
            const modalEl = document.getElementById('modalFichaCliente');
            if (modalEl) {
                // eslint-disable-next-line no-undef
                this.modalInstance = new bootstrap.Modal(modalEl);
                this.modalInstance.show();
            }

            // 2. Cargar datos del backend
            try {
                const response = await axios.get(`/cliente/ficha-completa/${idCliente}`);
                this.cliente = response.data;
            } catch (error) {
                console.error("Error al cargar ficha del cliente:", error);
                // Opcional: Mostrar toast de error
            } finally {
                this.loading = false;
            }
        },

        cerrarModal() {
            if (this.modalInstance) {
                this.modalInstance.hide();
            }
        },

        // --- HELPERS ---
        formatFecha(fecha) {
            if (!fecha) return 'N/A';
            return moment(fecha).format('DD/MM/YYYY');
        },
        calcularEdad(fecha) {
            if (!fecha) return 0;
            return moment().diff(moment(fecha), 'years');
        },
        formatNumero(value) {
            if (!value) return '0.00';
            return parseFloat(value).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>

<style scoped>
    /* Ajustes finos para que se vea profesional */
    .modal-header { border-bottom: 3px solid #ffc107; } /* Borde amarillo credisoft */
    .badge { font-weight: 500; letter-spacing: 0.5px; }
    .card { transition: transform 0.2s; }
    /* Scrollbar fino para el modal */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #555; }
</style>