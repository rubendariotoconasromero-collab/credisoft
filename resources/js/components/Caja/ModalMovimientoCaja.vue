<template>
    <div class="modal fade" :id="modalId" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-3" :class="esIngreso ? 'border-success' : 'border-danger'">
                <div class="modal-header text-white" :class="esIngreso ? 'bg-success' : 'bg-danger'">
                    <h5 class="modal-title fw-bold text-white">
                        Registro de {{ esIngreso ? 'Ingreso' : 'Egreso' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <form @submit.prevent="guardar">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text text-white" :class="esIngreso ? 'bg-success' : 'bg-danger'">Bs</span>
                                <input v-model="form.monto" type="number" class="form-control text-center" 
                                    placeholder="0.00" required min="0.1" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label fw-semibold">Motivo del {{ esIngreso ? 'Ingreso' : 'Egreso' }} <span class="text-danger">*</span></label>
                            
                            <div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-white" :class="esIngreso ? 'border-success text-success' : 'border-danger text-danger'">
                                    <i class="fas fa-list-ul"></i>
                                </span>
                                
                                <input type="text" class="form-control fw-bold" 
                                    :class="esIngreso ? 'border-success' : 'border-danger'"
                                    v-model="busqueda" 
                                    :placeholder="`Seleccione o busque un motivo...`"
                                    @input="filtrarMotivos" 
                                    @focus="abrirLista"
                                    @blur="cerrarLista"
                                    required autocomplete="off">
                                
                                <button type="button" class="btn bg-white border" 
                                        :class="esIngreso ? 'border-success text-success' : 'border-danger text-danger'"
                                        @mousedown.prevent="toggleLista">
                                    <i class="fas" :class="mostrarLista ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                                
                                <!-- <button type="button" class="btn fw-bold text-white px-3" 
                                        :class="esIngreso ? 'btn-success' : 'btn-danger'" 
                                        @click="abrirModalNuevoMotivo" title="Crear Nuevo Motivo">
                                    <i class="fas fa-plus"></i>
                                </button> -->
                            </div>

                            <ul v-if="mostrarLista" class="dropdown-menu show w-100 shadow-lg border-0 mt-1" 
                                style="max-height: 220px; overflow-y: auto; position: absolute; z-index: 1050;">
                                
                                <li v-if="resultados.length === 0" class="dropdown-item text-muted fst-italic text-center py-2">
                                    <i class="fas fa-search me-1"></i> No se encontraron coincidencias...
                                </li>
                                
                                <li v-for="m in resultados" :key="m.id">
                                    <a class="dropdown-item text-uppercase py-2 fw-semibold custom-dropdown-item" 
                                    :class="esIngreso ? 'hover-success' : 'hover-danger'"
                                    href="#" @mousedown.prevent="seleccionarMotivo(m)">
                                        {{ m.nombre }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div v-if="esMotivoVario" class="mb-3">
                            <label class="form-label fw-semibold">Detalle adicional</label>
                            <textarea v-model="form.descripcion_otro" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn fw-bold text-white" :class="esIngreso ? 'btn-success' : 'btn-danger'" :disabled="procesando">
                                <span v-if="procesando" class="spinner-border spinner-border-sm"></span>
                                <span v-else>Guardar {{ esIngreso ? 'Ingreso' : 'Egreso' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <ModalNuevoMotivo 
            ref="modalNuevoMotivoRef" 
            :suffix="tipo" 
            @creado="onMotivoCreado" 
        />

    </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import ModalNuevoMotivo from './ModalNuevoMotivo.vue'; // <--- IMPORTAR

export default {
    components: { ModalNuevoMotivo }, // <--- REGISTRAR
    props: {
        tipo: { type: String, required: true } // 'ingreso' o 'gasto'
    },
    data() {
        return {
            form: { monto: 0, descripcion: '', descripcion_otro: '' },
            busqueda: '',
            motivos: [],
            resultados: [],
            mostrarLista: false,
            procesando: false
        }
    },
    computed: {
        esIngreso() { return this.tipo === 'ingreso'; },
        modalId() { return this.esIngreso ? 'modalIngreso' : 'modalGasto'; },
        esMotivoVario() {
            // Detecta si es "Otros ingresos" o "Otros gastos" para pedir detalle extra
            if (!this.form.descripcion) return false;
            const desc = this.form.descripcion.toLowerCase();
            return desc.includes('otros ingresos') || desc.includes('otros gastos');
        }
    },
    methods: {
        abrirLista() {
            this.filtrarMotivos(); 
            this.mostrarLista = true;
        },

        toggleLista() {
            if (this.mostrarLista) {
                this.mostrarLista = false;
            } else {
                this.abrirLista();
            }
        },

        cerrarLista() {
            setTimeout(() => {
                this.mostrarLista = false;
            }, 150);
        },

        async abrir() {
            const check = await axios.get('/caja_abierta');
            if (check.data.usuario_actual == -1) {
                Swal.fire('Caja Cerrada', 'Debes abrir caja para registrar movimientos.', 'warning');
                return;
            }
            this.resetear();
            await this.cargarMotivos();
            $(`#${this.modalId}`).modal('show');
        },
        cerrar() {
            $(`#${this.modalId}`).modal('hide');
        },
        resetear() {
            this.form = { monto: 0, descripcion: '', descripcion_otro: '' };
            this.busqueda = '';
            this.mostrarLista = false;
        },
        
        async cargarMotivos() {
            const url = this.esIngreso ? '/get_motivos_ingresos_activos?tipo=caja' : '/get_motivos_gastos_activos?tipo=caja';
            try {
                const res = await axios.get(url);
                this.motivos = res.data;
                this.resultados = this.motivos;
            } catch (e) {
                console.error(e);
            }
        },
        
        filtrarMotivos() {
            if (!this.busqueda) {
                this.resultados = this.motivos;
            } else {
                const term = this.busqueda.toLowerCase();
                this.resultados = this.motivos.filter(m => m.nombre.toLowerCase().includes(term));
            }
            this.mostrarLista = true;
        },

        seleccionarMotivo(item) {
            this.form.descripcion = item.nombre;
            this.busqueda = item.nombre;
            this.mostrarLista = false;
        },

        abrirModalNuevoMotivo() {
            this.$refs.modalNuevoMotivoRef.abrir(this.tipo);
        },
        async onMotivoCreado(nombreMotivo) {
            await this.cargarMotivos();
            const nuevo = this.motivos.find(m => m.nombre === nombreMotivo);

            if (nuevo) {
                this.seleccionarMotivo(nuevo);
            } else {
                this.form.descripcion = nombreMotivo;
                this.busqueda = nombreMotivo;
            }
        },

        async guardar() {
            if(!this.form.descripcion) {
                Swal.fire('Falta Motivo', 'Seleccione o cree un motivo.', 'warning');
                return;
            }
            this.procesando = true;
            const url = this.esIngreso ? '/save_ingreso' : '/save_gasto';
            
            try {
                await axios.post(url, this.form);
                Swal.fire('Guardado', 'Movimiento registrado con éxito.', 'success');
                this.$emit('guardado');
                this.cerrar();
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo guardar el movimiento.', 'error');
            } finally {
                this.procesando = false;
            }
        }
    }
}
</script>

<style scoped>

.custom-dropdown-item {
    transition: all 0.2s ease-in-out;
    border-bottom: 1px solid #f8f9fa;
}
.custom-dropdown-item:last-child {
    border-bottom: none;
}
.hover-success:hover {
    background-color: #e8f5e9 !important;
    color: #198754 !important;
}
.hover-danger:hover {
    background-color: #fce4e4 !important;
    color: #dc3545 !important;
}

.dropdown-menu::-webkit-scrollbar {
    width: 6px;
}
.dropdown-menu::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 4px;
}
</style>