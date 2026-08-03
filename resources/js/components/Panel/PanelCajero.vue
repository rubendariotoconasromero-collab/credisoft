<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning text-dark fw-bold py-2">
                        <h5 class="mb-0 text-center text-uppercase fw-bold">
                            <i class="fas fa-cash-register me-2"></i>Panel del Cajero
                        </h5>
                    </div>
                </div>

                <div v-if="cargando" class="text-center py-5">
                    <div class="spinner-border text-success" role="status"></div>
                </div>

                <template v-else>
                    <!-- Estado de caja -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-2" style="font-size:0.75rem;letter-spacing:0.05em;">
                                        Estado de caja
                                    </h6>
                                    <template v-if="datos.caja_abierta">
                                        <span class="badge bg-success mb-2"><i class="fas fa-lock-open me-1"></i>Abierta</span>
                                        <span v-if="!datos.es_mi_caja" class="badge bg-secondary mb-2 ms-1">
                                            Abierta por {{ datos.usuario_caja }}
                                        </span>
                                        <div class="text-muted small">
                                            Desde {{ formatFecha(datos.fecha_apertura) }} · Monto inicial {{ formatMoney(datos.monto_inicial) }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="badge bg-danger mb-2"><i class="fas fa-lock me-1"></i>Cerrada</span>
                                        <div class="text-muted small">No hay ninguna caja abierta en este momento.</div>
                                    </template>
                                </div>

                                <div v-if="datos.caja_abierta" class="text-end">
                                    <div class="text-uppercase text-muted" style="font-size:0.7rem;letter-spacing:0.05em;">
                                        Saldo actual disponible
                                    </div>
                                    <div class="fw-bold text-success" style="font-size:1.75rem;">
                                        {{ formatMoney(datos.saldo_actual) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <router-link to="/caja" class="btn btn-success btn-sm">
                                    <i class="fas fa-arrow-right me-1"></i>
                                    {{ datos.caja_abierta ? "Ir a Control de Caja" : "Aperturar Caja" }}
                                </router-link>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen del día -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-success text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Cobros de hoy</h6>
                                    <h3 class="mb-1">{{ datos.cobros_hoy_cantidad }}</h3>
                                    <p class="mb-0 small">{{ formatMoney(datos.cobros_hoy_monto) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-primary text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Desembolsos de hoy</h6>
                                    <h3 class="mb-1">{{ datos.desembolsos_hoy_cantidad }}</h3>
                                    <p class="mb-0 small">{{ formatMoney(datos.desembolsos_hoy_monto) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-info text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Ingresos corrientes</h6>
                                    <h3 class="mb-1">{{ formatMoney(datos.ingresos_hoy_monto) }}</h3>
                                    <p class="mb-0 small">Hoy</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-danger text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Gastos corrientes</h6>
                                    <h3 class="mb-1">{{ formatMoney(datos.gastos_hoy_monto) }}</h3>
                                    <p class="mb-0 small">Hoy</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carga de trabajo -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-calendar-day text-warning me-2"></i>Cuotas que vencen hoy</h6>
                                <p class="text-muted small mb-0">En toda la cartera de la institución, para dimensionar la jornada.</p>
                            </div>
                            <div class="fw-bold text-dark" style="font-size:1.5rem;">{{ datos.cuotas_vencen_hoy }}</div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </main>
</template>

<script>
import axios from "axios";
import moment from "moment";

export default {
    name: "PanelCajero",
    data() {
        return {
            cargando: true,
            datos: {
                caja_abierta: false,
                es_mi_caja: false,
                usuario_caja: null,
                monto_inicial: 0,
                fecha_apertura: null,
                saldo_actual: 0,
                cobros_hoy_cantidad: 0,
                cobros_hoy_monto: 0,
                desembolsos_hoy_cantidad: 0,
                desembolsos_hoy_monto: 0,
                ingresos_hoy_monto: 0,
                gastos_hoy_monto: 0,
                cuotas_vencen_hoy: 0,
            },
        };
    },
    methods: {
        formatMoney(value) {
            return new Intl.NumberFormat("es-BO", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0) + " Bs.";
        },
        formatFecha(fecha) {
            if (!fecha) return "-";
            return moment(fecha).format("DD/MM/YYYY HH:mm");
        },
        async cargarPanel() {
            this.cargando = true;
            try {
                const { data } = await axios.get("/panel/cajero");
                this.datos = data;
            } catch (error) {
                console.error("Error cargando panel de cajero:", error);
            } finally {
                this.cargando = false;
            }
        },
    },
    mounted() {
        this.cargarPanel();
    },
};
</script>
