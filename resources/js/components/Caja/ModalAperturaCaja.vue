<template>
    <div class="modal fade animate__animated animate__fadeIn" id="modalAbrirCaja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content border border-secondary shadow-lg rounded-0">
                <!-- Flat, compact header -->
                <div class="modal-header bg-success text-white py-2 border-0 rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title text-uppercase fw-bold m-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                        Apertura de Caja
                    </h6>
                    <button type="button" class="btn-close btn-close-white" style="font-size: 0.65rem;" @click="cerrar"></button>
                </div>

                <div class="modal-body p-3">
                    <!-- Available in Vault Info Box (Flat, compact) -->
                    <div class="border p-2 mb-3 bg-light d-flex justify-content-between align-items-center rounded-0">
                        <div>
                            <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.55rem; letter-spacing: 0.5px;">Disponible en Bóveda</span>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 1rem;">
                                {{ formatNumero(saldoBoveda) }} <small class="text-muted fw-normal" style="font-size: 0.7rem;">Bs.</small>
                            </span>
                        </div>
                        <div class="text-success opacity-75">
                            <i class="fas fa-vault fa-lg"></i>
                        </div>
                    </div>

                    <!-- Monto input section -->
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1 text-center" style="font-size: 0.6rem; letter-spacing: 0.5px;">
                            Monto Inicial para la Jornada <span class="text-danger">*</span>
                        </label>
                        
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border rounded-0 px-2 text-muted fw-bold" style="font-size: 0.75rem;">
                                Bs.
                            </span>
                            <input v-model="monto" type="number" 
                                class="form-control border text-center fw-bold rounded-0"
                                placeholder="0.00" 
                                autofocus 
                                step="0.01" 
                                @focus="$event.target.select()"
                                style="color: #2c3e50; font-size: 0.95rem; height: 35px;">
                        </div>
                        
                        <!-- Error feedback -->
                        <div v-if="parseFloat(monto) > parseFloat(saldoBoveda)" class="mt-1 text-center animate__animated animate__headShake">
                            <small class="text-danger fw-bold" style="font-size: 0.7rem;">
                                <i class="fas fa-exclamation-triangle me-1"></i> Supera el saldo de bóveda
                            </small>
                        </div>
                    </div>

                    <!-- Clean alert note -->
                    <div class="border border-warning bg-warning bg-opacity-10 p-2 d-flex align-items-start rounded-0">
                        <i class="fas fa-info-circle text-warning mt-1 me-2" style="font-size: 0.8rem;"></i>
                        <p class="mb-0 text-dark lh-sm" style="font-size: 0.68rem; text-align: justify;">
                            Este monto será transferido desde la <strong>Bóveda Central</strong> a su <strong>Caja Personal</strong> para iniciar operaciones.
                        </p>
                    </div>
                </div>
                
                <!-- Compact footer with flat buttons side-by-side -->
                <div class="modal-footer border-0 p-3 pt-0">
                    <div class="d-flex gap-2 w-100">
                        <button @click="cerrar" class="btn btn-secondary btn-sm rounded-0 fw-bold flex-fill py-2 text-uppercase" style="font-size: 0.7rem;">
                            Cancelar
                        </button>
                        <button @click="guardar" class="btn btn-success btn-sm rounded-0 fw-bold flex-fill py-2 text-uppercase" style="font-size: 0.7rem;" :disabled="procesando || parseFloat(monto) > parseFloat(saldoBoveda)">
                            <span v-if="procesando" class="spinner-border spinner-border-sm me-1"></span>
                            <span v-else><i class="fas fa-check-circle me-1"></i> Aperturar</span>
                        </button>
                    </div>
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
.lh-sm {
    line-height: 1.25 !important;
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