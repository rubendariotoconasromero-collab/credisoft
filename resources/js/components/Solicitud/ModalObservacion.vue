<template>
    <div class="modal fade" id="modalObservacion" tabindex="-1" aria-labelledby="modalObservacionLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title text-dark fw-bold" id="modalObservacionLabel">
                        <i class="fas fa-eye me-2"></i>
                        {{ tituloModal }}
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" @click="cerrar"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observación</label>
                        <textarea class="form-control" v-model="observacionLocal" rows="5"
                            placeholder="Ingrese su observación aquí"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="guardar"
                        :disabled="!observacionLocal.trim() || guardando">
                        <i v-if="!guardando" class="fas fa-save me-1"></i>
                        <i v-if="guardando" class="fas fa-spinner fa-spin me-1"></i>
                        {{ guardando ? 'Guardando...' : 'Guardar' }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="cerrar">
                        <i class="fas fa-times me-1"></i> Cerrar
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
            idSolicitud: null,
            observacionLocal: '',
            guardando: false,
            esEdicion: false
        };
    },
    computed: {
        tituloModal() {
            return this.esEdicion ? 'Modificar Observación' : 'Agregar Observación';
        }
    },
    methods: {
        // Este método será llamado por el PADRE usando $refs
        abrir(id, textoActual = '') {
            this.idSolicitud = id;
            this.observacionLocal = textoActual || '';
            this.esEdicion = !!textoActual; // Si hay texto, es edición
            this.guardando = false;
            
            // Abrir modal vía jQuery (como en tu proyecto original)
            $('#modalObservacion').modal('show');
        },

        cerrar() {
            this.observacionLocal = '';
            this.idSolicitud = null;
            this.guardando = false;
            $('#modalObservacion').modal('hide');
            this.$emit('cerrar');
        },

        async guardar() {
            if (!this.observacionLocal.trim()) {
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Por favor, ingrese una observación' });
                return;
            }

            this.guardando = true;
            try {
                await axios.post('/guardar_observacion', {
                    id_solicitud: this.idSolicitud,
                    observacion: this.observacionLocal.trim(),
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Observación guardada',
                    showConfirmButton: false,
                    timer: 1500,
                });

                // Emitimos evento al padre para que recargue la lista
                this.$emit('guardado-exito');
                this.cerrar();

            } catch (error) {
                console.error('Error saving observacion:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la observación' });
            } finally {
                this.guardando = false;
            }
        }
    }
};
</script>

<style scoped>
@import './../styles/frmSolicitud.css';
</style>