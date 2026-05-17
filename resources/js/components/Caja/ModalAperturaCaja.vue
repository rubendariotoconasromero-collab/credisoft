<template>
    <div class="modal fade" id="modalAbrirCaja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-success bg-gradient py-3 border-0 rounded-top-4">
                    <h5 class="modal-title text-uppercase text-white fw-bold">
                        <i class="fas fa-cash-register me-2"></i>Apertura de Caja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Status Card -->
                    <div class="card border-0 bg-light-info mb-4 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <!-- <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                                <i class="fas fa-vault fa-lg"></i>
                            </div> -->
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-0 small text-center" style="letter-spacing: 0.5px;">Disponible en Bóveda</h6>
                                <h4 class="text-primary fw-bold mb-0">
                                    {{ formatNumero(saldoBoveda) }} <span class="fs-6 text-muted fw-normal">Bs.</span>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <p class="text-muted small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Monto Inicial para la Jornada</p>
                        
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-4">
                                <i class="fas fa-money-bill-wave text-success fs-4"></i>
                            </span>
                            <input v-model="monto" type="number" 
                                class="form-control border-0 text-center fw-bold fs-3 py-3"
                                placeholder="0.00" 
                                autofocus 
                                step="0.01" 
                                @focus="$event.target.select()"
                                style="color: #2c3e50;">
                            <span class="input-group-text bg-white border-0 pe-4 fw-bold text-muted">Bs.</span>
                        </div>
                        
                        <div v-if="parseFloat(monto) > parseFloat(saldoBoveda)" class="mt-2 animate__animated animate__headShake">
                            <small class="text-danger fw-bold">
                                <i class="fas fa-exclamation-triangle me-1"></i> El monto supera el saldo de bóveda
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 bg-light-warning py-3 rounded-3 d-flex align-items-center mb-0">
                        <i class="fas fa-info-circle text-warning fs-4 me-3"></i>
                        <p class="mb-0 small text-dark">
                            Este monto será transferido desde la <strong>Bóveda Central</strong> a su <strong>Caja Personal</strong> para iniciar operaciones.
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-4 pt-0">
                    <button @click="guardar" class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-3 rounded-pill transition-all" :disabled="procesando || parseFloat(monto) > parseFloat(saldoBoveda)">
                        <span v-if="procesando" class="spinner-border spinner-border-sm me-2"></span>
                        <span v-else><i class="fas fa-unlock-alt me-2"></i> CONFIRMAR APERTURA</span>
                    </button>
                    <button @click="cerrar" class="btn btn-link w-100 text-muted text-decoration-none mt-2 small fw-bold">
                        CANCELAR
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            monto: 0,
            saldoBoveda: 0,
            procesando: false
        }
    },
    methods: {
        formatNumero(value) {
            const n = parseFloat(value || 0);
            return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        async fetchSaldoBoveda() {
            try {
                const response = await axios.get('/get_boveda');
                this.saldoBoveda = response.data.saldo_actual || 0;
            } catch (error) {
                console.error("Error al obtener saldo de bóveda:", error);
                this.saldoBoveda = 0;
            }
        },
        async abrir() {
            axios.get('/caja_abierta').then(async res => {
                if (res.data.usuario_actual == 1) {
                    Swal.fire('Atención', 'Ya tienes una caja abierta.', 'warning');
                } else {
                    await this.fetchSaldoBoveda();
                    this.monto = 0;
                    $('#modalAbrirCaja').modal('show');
                }
            });
        },
        cerrar() {
            $('#modalAbrirCaja').modal('hide');
        },
        async guardar() {
            if (this.monto === '' || parseFloat(this.monto) < 0) {
                Swal.fire('Atención', 'Ingrese un monto inicial válido.', 'warning');
                return;
            }

            if (parseFloat(this.monto) > parseFloat(this.saldoBoveda)) {
                Swal.fire('Atención', 'El monto inicial no puede ser mayor al saldo disponible en Bóveda.', 'warning');
                return;
            }

            this.procesando = true;
            try {
                const response = await axios.post('/caja/aperturar', { 
                    monto_inicial: this.monto 
                });

                Swal.fire({
                    title: '¡Excelente!',
                    text: response.data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                this.$emit('aperturada'); 
                this.cerrar();
                this.monto = 0;
            } catch (error) {
                console.error("Error en apertura:", error);
                let mensaje = 'No se pudo abrir la caja.';
                if (error.response && error.response.data && error.response.data.message) {
                    mensaje = error.response.data.message;
                }
                Swal.fire('Error', mensaje, 'error');
            } finally {
                this.procesando = false;
            }
        }
    }
}
</script>

<style scoped>
.bg-light-info {
    background-color: #f0f9ff;
}
.bg-light-warning {
    background-color: #fffbeb;
}
.rounded-4 {
    border-radius: 1rem !important;
}
.rounded-top-4 {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
}
.transition-all {
    transition: all 0.3s ease;
}
.transition-all:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.animate__headShake {
    animation: headShake 1s ease-in-out;
}
@keyframes headShake {
  0% { transform: translateX(0); }
  6.5% { transform: translateX(-6px) rotateY(-9deg); }
  18.5% { transform: translateX(5px) rotateY(7deg); }
  31.5% { transform: translateX(-3px) rotateY(-5deg); }
  43.5% { transform: translateX(2px) rotateY(3deg); }
  50% { transform: translateX(0); }
}
</style>