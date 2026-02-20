<template>
    <div class="modal fade" id="modalAbrirCaja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border border-2 border-white shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-uppercase text-white">Apertura de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-muted mb-3">Ingrese el monto inicial:</p>
                    <div class="input-group mb-3">
                        <input v-model="monto" type="number" class="form-control form-control-lg text-center"
                            placeholder="0.00" autofocus step="0.01" @focus="$event.target.select()">
                    </div>
                    <div class="mt-4">
                        <button @click="guardar" class="btn btn-success btn-lg w-100" :disabled="procesando">
                            <span v-if="procesando" class="spinner-border spinner-border-sm"></span>
                            <span v-else><i class="fas fa-unlock me-2"></i> ABRIR CAJA</span>
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
            procesando: false
        }
    },
    methods: {
        abrir() {
            // Verificar si ya hay caja abierta antes de mostrar
            axios.get('/caja_abierta').then(res => {
                if (res.data.usuario_actual == 1) {
                    Swal.fire('Atención', 'Ya tienes una caja abierta.', 'warning');
                } else {
                    this.monto = 0;
                    $('#modalAbrirCaja').modal('show');
                }
            });
        },
        cerrar() {
            $('#modalAbrirCaja').modal('hide');
        },
        async guardar() {
            if (this.monto === '' || this.monto < 0) {
                Swal.fire('Atención', 'Ingrese un monto inicial válido.', 'warning');
                return;
            }
            this.procesando = true;
            try {
                const response = await axios.post('/caja/aperturar', { 
                    monto_inicial: this.monto 
                });

                Swal.fire('Éxito', response.data.message, 'success');
                this.$emit('aperturada'); 
                this.cerrar();
                this.monto = 0;
            } catch (error) {
                console.error("Error en apertura:", error);
                // Intentar obtener el mensaje de error del backend
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