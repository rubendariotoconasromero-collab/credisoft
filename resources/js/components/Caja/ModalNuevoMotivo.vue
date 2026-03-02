<template>
    <Teleport to="body">
        <div class="modal fade" :id="dynamicId" tabindex="-1" data-bs-backdrop="static" style="z-index: 1070;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border border-2" :class="claseBorde">
                    <div class="modal-header text-white py-2" :class="claseColor">
                        <h6 class="modal-title fw-bold text-uppercase" style="font-size: 0.9rem;">
                            <i class="fas fa-plus-circle me-1"></i> {{ tituloModal }}
                        </h6>
                        <button type="button" class="btn-close btn-close-white btn-sm" @click="cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="guardar">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Nombre del Motivo</label>
                                <input v-model="nombre" type="text" class="form-control" 
                                    :placeholder="placeholderInput" 
                                    required ref="inputNombre">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-sm fw-bold text-white" :class="claseColor" :disabled="procesando">
                                    <span v-if="procesando" class="spinner-border spinner-border-sm me-1"></span>
                                    GUARDAR
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    props: {
        // Recibimos un sufijo para hacer el ID único (será 'ingreso' o 'gasto')
        suffix: { type: String, required: true, default: 'general' }
    },
    data() {
        return {
            nombre: '',
            tipo: '', 
            procesando: false
        }
    },
    computed: {
        // Generamos el ID único combinando el nombre base + el sufijo
        dynamicId() {
            return `modalNuevoMotivo_${this.suffix}`;
        },
        esIngreso() {
            return this.tipo === 'ingreso';
        },
        tituloModal() {
            return this.esIngreso ? 'Nuevo Motivo de Ingreso' : 'Nuevo Motivo de Egreso';
        },
        placeholderInput() {
            return this.esIngreso ? 'EJ: VENTA DE ACTIVOS' : 'EJ: COMPRA DE LIMPIEZA';
        },
        claseColor() {
            return this.esIngreso ? 'bg-success btn-success' : 'bg-danger btn-danger';
        },
        claseBorde() {
            return this.esIngreso ? 'border-success' : 'border-danger';
        }
    },
    methods: {
        abrir(tipoAccion) {
            this.tipo = tipoAccion;
            this.nombre = '';
            this.procesando = false;
            
            // Usamos el ID dinámico para abrir ESTE modal específico
            $(`#${this.dynamicId}`).modal('show');
            
            setTimeout(() => {
                if(this.$refs.inputNombre) this.$refs.inputNombre.focus();
            }, 500);
        },
        cerrar() {
            // Cerramos usando el mismo ID dinámico
            $(`#${this.dynamicId}`).modal('hide');
        },
        async guardar() {
            if (!this.nombre.trim()) return;

            this.procesando = true;
            const url = this.esIngreso ? '/guardar_motivo_ingreso' : '/guardar_motivo_gasto';

            try {
                await axios.post(url, { nombre: this.nombre, accion: 0 });
                
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: `Motivo de ${this.esIngreso ? 'ingreso' : 'egreso'} agregado`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });

                this.$emit('creado', this.nombre);
                this.cerrar();

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo guardar el motivo', 'error');
            } finally {
                this.procesando = false;
            }
        }
    }
}
</script>