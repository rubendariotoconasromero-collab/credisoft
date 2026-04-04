<template>
    <div class="card shadow-sm border-info">
        <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 text-center">
                <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                    Desembolsos y pagos adm. pendientes
                </h5>
            </div>
            <button @click="$emit('cerrar')" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" v-model="filtros.criterio">
                        <option value="">-- Seleccionar criterio --</option>
                        <option value="ci">CI</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            v-model="filtros.texto"
                            :placeholder="`Buscar por ${filtros.criterio || 'criterio'}`"
                        />
                        <button class="btn btn-success" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        
            <div class="table-responsive" style="font-size:12px">
                <table class="table mb-4 table-sm table-striped table-hover table-desembolsos">
                    <thead class="text-uppercase table-warning">
                        <tr>
                            <th class="text-dark fw-bold">#</th>
                            <th class="text-dark fw-bold">Plan pago</th>
                            <th class="text-dark fw-bold">Fecha desembolso</th>
                            <th class="text-dark fw-bold">Cliente</th>
                            <th class="text-dark fw-bold">Asesor</th>
                            <th class="text-dark fw-bold text-end">Monto</th>
                            <th class="text-dark fw-bold text-end">Pago Adm.</th>
                            <th class="text-dark fw-bold text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <tr v-if="listaFiltrada.length === 0">
                            <td colspan="8" class="text-center text-muted py-3">
                                No se encontraron desembolsos pendientes.
                            </td>
                        </tr>
                        <tr v-for="(item, index) in listaFiltrada" :key="item.id">
                            <td class="text-uppercase">{{ index + 1 }}</td>
                            <td class="text-uppercase fw-bold">{{ item.id }}</td>
                            <td class="text-uppercase">{{ item.fecha_desembolso }}</td>
                            <td class="text-uppercase fw-bold">
                                {{ item.cliente }}
                                <span class="text-muted d-block small">
                                    <strong>CI: </strong> {{ item.ci }}
                                </span>
                            </td>
                            <td class="text-uppercase">{{ item.asesor }}</td>
                            <td class="text-uppercase text-end">
                                {{ formatMoney(item.tipo_solicitud == 'Refinanciamiento' ? item.monto_refinanciamiento : item.total_pagar_plan) }}
                            </td>
                            <td class="text-uppercase text-end text-dark fw-bold">
                                {{ formatMoney(calcularPagoAdm(item)) }}
                            </td>
                            <td class="text-uppercase text-center">
                                <button
                                    style="font-size:10px"
                                    @click="$emit('desembolsar', item)"
                                    class="btn btn-sm btn-success my-0 py-1 shadow-sm"
                                >
                                    <i class="fas fa-dollar-sign me-1"></i> Desembolsar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        listaPendientes: {
            type: Array,
            required: true,
            default: () => []
        }
    },
    data() {
        return {
            filtros: {
                criterio: '',
                texto: ''
            }
        };
    },
    computed: {
        listaFiltrada() {
            if (!this.filtros.criterio || !this.filtros.texto) {
                return this.listaPendientes;
            }

            const texto = this.filtros.texto.toLowerCase();
            return this.listaPendientes.filter(item => {
                const valor = item[this.filtros.criterio];
                if (valor === undefined || valor === null) return false;
                return String(valor).toLowerCase().includes(texto);
            });
        }
    },
    methods: {
        formatMoney(value) {
            const val = parseFloat(value || 0);
            return isNaN(val) ? '0.00' : val.toFixed(2);
        },
        calcularPagoAdm(item) {
            const monto = item.tipo_solicitud == 'Refinanciamiento' 
                ? parseFloat(item.monto_refinanciamiento) 
                : parseFloat(item.total_pagar_plan);
            return monto * 0.01;
        }
    }
};
</script>

<style scoped>
    @import '../styles/frmCaja.css';

    .table-desembolsos th{
        font-size:12px !important;
    }
</style>