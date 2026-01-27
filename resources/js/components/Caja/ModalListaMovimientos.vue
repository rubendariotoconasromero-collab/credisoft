<template>
    <Teleport to="body">
        <div class="modal fade" :id="modalId" tabindex="-1" style="z-index: 1080;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border border-2 border-info">
                    <div class="modal-header bg-info text-white py-2">
                        <h6 class="modal-title fw-bold text-uppercase">
                            <i class="fas fa-list-alt me-2"></i> {{ titulo }}
                        </h6>
                        <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div v-if="loading" class="text-center py-5">
                            <div class="spinner-border text-info" role="status"></div>
                        </div>
                        
                        <div v-else class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0" style="font-size: 12px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha/Hora</th>
                                        <th>Descripción / Cliente</th>
                                        <th class="text-end">Monto</th>
                                        <th class="text-center">Recibo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in lista" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.fecha_hora || item.created_at }}</td>
                                        <td>
                                            <span class="fw-bold">{{ item.descripcion || item.detalle }}</span>
                                            <div v-if="item.cliente" class="text-muted small">
                                                <i class="fas fa-user me-1"></i> {{ item.cliente }}
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">{{ formatMoney(item.monto || item.monto_pago) }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary py-0 px-2" @click="imprimir(item)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="lista.length === 0">
                                        <td colspan="5" class="text-center py-4 text-muted">No hay registros en esta categoría.</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold border-top">
                                    <tr>
                                        <td colspan="3" class="text-end">TOTAL:</td>
                                        <td class="text-end">{{ formatMoney(total) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrar">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            modalId: 'modalListaGenerica',
            titulo: 'Detalle',
            lista: [],
            loading: false,
            tipo: '' // 'ingreso', 'gasto', 'cobro'
        }
    },
    computed: {
        total() {
            return this.lista.reduce((acc, item) => acc + parseFloat(item.monto || item.monto_pago || 0), 0);
        }
    },
    methods: {
        async abrir(tipo, idCaja) {
            this.tipo = tipo;
            this.loading = true;
            this.lista = [];
            $(`#${this.modalId}`).modal('show');

            try {
                let url = '';
                // Definimos la URL y el Título según el botón presionado
                if (tipo === 'ingresos_corrientes') {
                    this.titulo = 'Detalle de Ingresos Corrientes';
                    url = '/get_ingresos_caja_listado'; 
                } else if (tipo === 'gastos_corrientes') {
                    this.titulo = 'Detalle de Gastos Corrientes';
                    url = '/get_gastos_caja_listado';
                } else if (tipo === 'cobros_cuotas') {
                    this.titulo = 'Detalle de Cobros de Cuotas';
                    url = '/get_cobros_caja_listado'; // Ajusta a tu ruta real
                } else if (tipo === 'desembolsos') {
                    this.titulo = 'Detalle de Desembolsos';
                    url = '/get_desembolsos_caja_listado';
                }

                // Ajusta los parámetros según lo que espere tu backend
                const response = await axios.get(url, { params: { id_caja: idCaja } });
                this.lista = response.data;

            } catch (error) {
                console.error("Error cargando detalle:", error);
            } finally {
                this.loading = false;
            }
        },
        cerrar() {
            $(`#${this.modalId}`).modal('hide');
        },
        formatMoney(value) {
            return parseFloat(value).toFixed(2);
        },
        imprimir(item) {
            // Lógica genérica de impresión
            let url = '';
            if (this.tipo === 'ingresos_corrientes') url = `/reporte/ingreso/${item.id}`;
            else if (this.tipo === 'gastos_corrientes') url = `/reporte/gasto/${item.id}`;
            // Agrega más casos según necesites
            if(url) window.open(url, '_blank');
        }
    }
}
</script>