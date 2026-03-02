<template>
    <Teleport to="body">
        <div class="modal fade" :id="modalId" tabindex="-1" style="z-index: 1080;">
            <div class="modal-dialog modal-xl" style="width:90%; max-width:90%;height:100%">
                <div class="modal-content border border-2 border-primary" style="height:100%">
                    
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-list-alt me-2"></i> {{ titulo }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                    </div>
                    
                    <div class="modal-body p-0 d-flex flex-column">
                        
                        <div class="bg-light p-2 border-bottom d-flex gap-2 align-items-center flex-wrap">
                            <input type="text" class="form-control form-control-sm" style="width: 250px;" v-model="filtros.buscar" placeholder="Buscar descripción..." @keyup.enter="cargarDatos">
                            <input type="date" class="form-control form-control-sm w-auto" v-model="filtros.fecha_inicio" title="Fecha Inicio">
                            <input type="date" class="form-control form-control-sm w-auto" v-model="filtros.fecha_fin" title="Fecha Fin">
                            
                            <button class="btn btn-sm btn-primary" @click="cargarDatos">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <button class="btn btn-sm btn-secondary" @click="limpiarFiltros">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>

                            <button class="btn btn-sm btn-danger ms-auto fw-bold shadow-sm" @click="generarReporteGeneral" :disabled="loading || lista.length === 0">
                                <i class="fas fa-file-pdf"></i> Reporte PDF
                            </button>
                        </div>

                        <div v-if="loading" class="text-center py-5 flex-grow-1">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        
                        <div v-else class="table-responsive flex-grow-1">
                            <table class="table table-sm table-striped table-hover mb-0" style="font-size:11px;">
                                <thead class="table-light sticky-top shadow-sm">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Descripción / Cliente</th>
                                        <th class="text-end">Monto</th>
                                        <!-- <th class="text-center">Recibo</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in lista" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.fecha || item.fecha_pago || item.created_at }}</td>
                                        <td>
                                            <span class="fw-bold">{{ item.descripcion || item.detalle || 'Cobro de Cuota' }}</span>
                                            <div v-if="item.cliente" class="text-muted small">
                                                <i class="fas fa-user me-1"></i> {{ item.cliente }}
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">{{ formatMoney(item.monto || item.monto_pago) }}</td>
                                        <!-- <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary py-0 px-2" @click="imprimirRecibo(item)" title="Imprimir Recibo Individual">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td> -->
                                    </tr>
                                    <tr v-if="lista.length === 0">
                                        <td colspan="4" class="text-center py-4 text-muted">No hay registros con los filtros actuales.</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold sticky-bottom border-top">
                                    <tr>
                                        <td colspan="3" class="text-end text-uppercase fw-bold">Total:</td>
                                        <td class="text-end text-primary fs-6">{{ formatMoney(total) }}</td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="bg-light p-2 border-top d-flex justify-content-between align-items-center" v-if="pagination.last_page > 1">
                                <span class="text-muted small">
                                    Mostrando {{ lista.length }} registros de un total de {{ pagination.total }}
                                </span>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                        <button class="page-link" @click="cargarDatos(pagination.current_page - 1)">Anterior</button>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link text-dark">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                                    </li>
                                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                        <button class="page-link" @click="cargarDatos(pagination.current_page + 1)">Siguiente</button>
                                    </li>
                                </ul>
                            </div>
                        </div>


                        
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>
import axios from 'axios';
import moment from 'moment';


export default {
    data() {
        return {
            modalId: 'modalListaGenerica',
            titulo: 'Detalle',
            lista: [],
            loading: false,
            tipo: '', 
            currentCajaId: null,
            // Objeto reactivo para los filtros
            filtros: {
                buscar: '',
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_fin: moment().format('YYYY-MM-DD')
            },
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 0
            }
        }
    },
    computed: {
        total() {
            return this.lista.reduce((acc, item) => acc + parseFloat(item.monto || item.monto_pago || 0), 0);
        }
    },
    methods: {
        abrir(tipo, idCaja) {
            this.tipo = tipo;
            this.currentCajaId = idCaja;
            this.limpiarFiltros(false); // Limpia filtros sin recargar aún
            $(`#${this.modalId}`).modal('show');
            this.cargarDatos(); // Carga inicial
        },
        cerrar() {
            $(`#${this.modalId}`).modal('hide');
        },
        limpiarFiltros(recargar = true) {
            this.filtros.buscar = '';
            this.filtros.fecha_inicio = moment().subtract(1, 'months').format('YYYY-MM-DD');
            this.filtros.fecha_fin = moment().format('YYYY-MM-DD');
            if(recargar) this.cargarDatos();
        },
        async cargarDatos(page = 1) {
            this.loading = true;
            this.lista = [];
            try {
                const response = await axios.get('/caja/movimientos/listado', { 
                    params: { 
                        id_caja: this.currentCajaId,
                        tipo: this.tipo,
                        page: page, // <-- Enviamos la página solicitada a Laravel
                        ...this.filtros 
                    } 
                });
                
                // Laravel con paginate() envía los datos dentro de 'data'
                this.lista = response.data.data; 
                
                // Actualizamos los datos de paginación para los botones
                this.pagination = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total
                };
                
                const titulos = {
                    'ingresos': 'Detalle de Ingresos Corrientes',
                    'egresos': 'Detalle de Gastos Corrientes',
                    'cobros': 'Detalle de Cobros de Cuotas',
                    'desembolsos': 'Detalle de Desembolsos'
                };
                this.titulo = titulos[this.tipo] || 'Detalle de Movimientos';

            } catch (error) {
                console.error("Error cargando detalle:", error);
                Swal.fire('Error', 'No se pudo cargar la información.', 'error');
            } finally {
                this.loading = false;
            }
        },
        formatMoney(value) {
            return parseFloat(value || 0).toFixed(2);
        },
        imprimirRecibo(item) {
            // Recibo individual
            let url = `/reporte/recibo/${this.tipo}/${item.id}`;
            window.open(url, '_blank');
        },
        generarReporteGeneral() {
            // Arma los parámetros GET para enviarlos al generador de PDF
            const params = new URLSearchParams({
                id_caja: this.currentCajaId,
                tipo: this.tipo,
                buscar: this.filtros.buscar,
                fecha_inicio: this.filtros.fecha_inicio,
                fecha_fin: this.filtros.fecha_fin
            }).toString();
            
            // Abre el PDF en una nueva pestaña
            window.open(`/caja/movimientos/reporte-pdf?${params}`, '_blank');
        }
    }
}
</script>