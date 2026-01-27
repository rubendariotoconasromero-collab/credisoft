<template>
    <div class="modal fade" :id="modalId" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-3" :class="esIngreso ? 'border-success' : 'border-danger'">
                <div class="modal-header text-white" :class="esIngreso ? 'bg-success' : 'bg-danger'">
                    <h5 class="modal-title fw-bold">
                        <i :class="esIngreso ? 'fas fa-money-bill-trend-up' : 'fas fa-money-bill-wave'" class="me-2"></i>
                        Registro de {{ esIngreso ? 'Ingreso' : 'Egreso' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <form @submit.prevent="guardar">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input v-model="form.monto" type="number" class="form-control form-control-lg text-center" 
                                    placeholder="0.00" required min="0.1" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Motivo</label>
                            <div class="position-relative">
                                <div class="input-group">
                                    <input type="text" class="form-control text-uppercase" 
                                        v-model="busqueda" 
                                        :placeholder="`Buscar motivo de ${tipo}...`"
                                        @input="filtrarMotivos" @focus="mostrarLista = true" required autocomplete="off">
                                    
                                    <button type="button" class="btn btn-outline-secondary" @click="mostrarLista = !mostrarLista">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    
                                    <button type="button" class="btn" :class="esIngreso ? 'btn-success' : 'btn-danger'" 
                                            @click="abrirModalNuevoMotivo" title="Crear Nuevo Motivo">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <div v-if="mostrarLista && resultados.length > 0" class="dropdown-menu show w-100 shadow" 
                                     style="max-height: 200px; overflow-y: auto;">
                                    <a v-for="m in resultados" :key="m.id" class="dropdown-item text-uppercase" 
                                       href="#" @click.prevent="seleccionarMotivo(m)">
                                        {{ m.nombre }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div v-if="esMotivoVario" class="mb-3">
                            <label class="form-label fw-semibold">Detalle adicional</label>
                            <textarea v-model="form.descripcion_otro" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-lg fw-bold text-white" :class="esIngreso ? 'btn-success' : 'btn-danger'" :disabled="procesando">
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
        async abrir() {
            // Validar caja abierta
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
        
        // --- Gestión de Motivos ---
        async cargarMotivos() {
            const url = this.esIngreso ? '/get_motivos_ingresos' : '/get_motivos_gastos';
            try {
                const res = await axios.get(url);
                this.motivos = res.data;
                this.resultados = []; // Limpia resultados de búsqueda previos
            } catch (e) {
                console.error(e);
            }
        },
        filtrarMotivos() {
            if (!this.busqueda) {
                this.resultados = [];
                return;
            }
            this.mostrarLista = true;
            const term = this.busqueda.toLowerCase();
            this.resultados = this.motivos.filter(m => m.nombre.toLowerCase().includes(term));
        },
        seleccionarMotivo(item) {
            this.form.descripcion = item.nombre;
            this.busqueda = item.nombre;
            this.mostrarLista = false;
        },

        // --- LÓGICA DEL NUEVO MODAL ---
        abrirModalNuevoMotivo() {
            // Pasamos el tipo al hijo para que sepa si guardar en ingreso o gasto
            this.$refs.modalNuevoMotivoRef.abrir(this.tipo);
        },
        async onMotivoCreado(nombreMotivo) {
            // 1. Recargamos la lista del servidor
            await this.cargarMotivos();
            
            // 2. Buscamos el objeto completo del nuevo motivo
            const nuevo = this.motivos.find(m => m.nombre === nombreMotivo);
            
            // 3. Lo seleccionamos automáticamente
            if (nuevo) {
                this.seleccionarMotivo(nuevo);
            } else {
                // Fallback por si acaso
                this.form.descripcion = nombreMotivo;
                this.busqueda = nombreMotivo;
            }
        },

        // --- Guardado del Movimiento ---
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