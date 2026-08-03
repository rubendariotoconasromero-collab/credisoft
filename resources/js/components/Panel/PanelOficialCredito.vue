<template>
    <main>
        <div class="page-content px-0 mx-0">
            <div class="container-fluid">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning text-dark fw-bold py-2">
                        <h5 class="mb-0 text-center text-uppercase fw-bold">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Mi Cartera — Oficial de Crédito
                        </h5>
                    </div>
                </div>

                <div v-if="cargando" class="text-center py-5">
                    <div class="spinner-border text-success" role="status"></div>
                </div>

                <template v-else>
                    <!-- Resumen de cartera -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-success text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Mis clientes</h6>
                                    <h3 class="mb-1">{{ datos.mis_clientes }}</h3>
                                    <p class="mb-0 small">Con al menos una solicitud propia</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-primary text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Créditos activos</h6>
                                    <h3 class="mb-1">{{ datos.creditos_activos }}</h3>
                                    <p class="mb-0 small">{{ formatMoney(datos.saldo_cartera_pendiente) }} pendiente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 bg-info text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-white-50 mb-2" style="font-size:0.7rem;">Solicitudes por aprobar</h6>
                                    <h3 class="mb-1">{{ datos.solicitudes_pendientes }}</h3>
                                    <p class="mb-0 small">Sin plan de pago generado</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 shadow-sm" :class="datos.creditos_en_mora > 0 ? 'bg-danger text-white' : 'bg-light'">
                                <div class="card-body">
                                    <h6 class="text-uppercase mb-2" :class="datos.creditos_en_mora > 0 ? 'text-white-50' : 'text-muted'" style="font-size:0.7rem;">
                                        Créditos en mora
                                    </h6>
                                    <h3 class="mb-1">{{ datos.creditos_en_mora }}</h3>
                                    <p class="mb-0 small">{{ formatMoney(datos.monto_en_mora) }} en mora</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>Próximos vencimientos</h6>
                                        <p class="text-muted small mb-0">Cuotas de mi cartera que vencen en los próximos 7 días.</p>
                                    </div>
                                    <div class="fw-bold text-dark" style="font-size:1.5rem;">{{ datos.proximos_vencimientos }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex flex-column justify-content-center gap-2">
                                    <router-link to="/solicitud" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus-circle me-1"></i>Nueva Solicitud
                                    </router-link>
                                    <router-link to="/clientes" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-user-tie me-1"></i>Ver mis Clientes
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas solicitudes -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Mis últimas solicitudes</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th class="text-end">Monto</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="s in datos.ultimas_solicitudes" :key="s.id">
                                            <td>{{ s.cliente }}</td>
                                            <td class="text-end">{{ formatMoney(s.importe_solicitud) }} {{ s.moneda }}</td>
                                            <td class="text-center">
                                                <span class="badge" :class="estadoBadge(s.estado)">{{ estadoTexto(s.estado) }}</span>
                                            </td>
                                            <td class="text-center">{{ formatFecha(s.created_at) }}</td>
                                        </tr>
                                        <tr v-if="datos.ultimas_solicitudes.length === 0">
                                            <td colspan="4" class="text-center text-muted py-3">Todavía no registraste ninguna solicitud.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
    name: "PanelOficialCredito",
    data() {
        return {
            cargando: true,
            datos: {
                mis_clientes: 0,
                creditos_activos: 0,
                saldo_cartera_pendiente: 0,
                solicitudes_pendientes: 0,
                creditos_en_mora: 0,
                monto_en_mora: 0,
                proximos_vencimientos: 0,
                ultimas_solicitudes: [],
            },
        };
    },
    methods: {
        formatMoney(value) {
            return new Intl.NumberFormat("es-BO", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0) + " Bs.";
        },
        formatFecha(fecha) {
            if (!fecha) return "-";
            return moment(fecha).format("DD/MM/YYYY");
        },
        estadoTexto(estado) {
            const mapa = { 0: "Anulada", 1: "Activa", 2: "Aprobada", 3: "Reprogramada" };
            return mapa[estado] ?? "Desconocido";
        },
        estadoBadge(estado) {
            const mapa = {
                0: "bg-secondary",
                1: "bg-info text-dark",
                2: "bg-success",
                3: "bg-warning text-dark",
            };
            return mapa[estado] ?? "bg-light text-dark";
        },
        async cargarPanel() {
            this.cargando = true;
            try {
                const { data } = await axios.get("/panel/oficial");
                this.datos = data;
            } catch (error) {
                console.error("Error cargando panel de oficial de crédito:", error);
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
