    <template>
        <main>
            <div class="page-content px-0 mx-0">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark fw-bold py-1">
                            <h5 class="mb-0 text-center text-uppercase fw-bold">Panel Informativo</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Resumen de estadísticas -->
                            <div class="row g-4 mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 bg-success text-white shadow-sm">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-2">Clientes</h6>
                                                <h3 class="mb-3">{{ cantidad_clientes }}</h3>
                                                <p class="mb-0 small">Clientes registrados</p>
                                            </div>
                                            <i
                                                class="bi bi-person-circle display-6 opacity-25 position-absolute end-0 bottom-0 p-3"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 bg-success text-white shadow-sm">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-2">Solicitudes</h6>
                                                <h3 class="mb-3">{{ cantidad_solicitudes }}</h3>
                                                <p class="mb-0 small">Solicitudes registradas</p>
                                            </div>
                                            <i
                                                class="bi bi-file-earmark-text display-6 opacity-25 position-absolute end-0 bottom-0 p-3"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 bg-success text-white shadow-sm">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-2">Planes de Pagos</h6>
                                                <h3 class="mb-3">{{ cantidad_planes }}</h3>
                                                <p class="mb-0 small">Planes aprobados</p>
                                            </div>
                                            <i
                                                class="bi bi-wallet2 display-6 opacity-25 position-absolute end-0 bottom-0 p-3"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 bg-success text-white shadow-sm">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-2">Préstamos Activos</h6>
                                                <h3 class="mb-3">{{ estadisticas.activos }}</h3>
                                                <p class="mb-0 small">Préstamos en proceso</p>
                                            </div>
                                            <i
                                                class="bi bi-graph-up display-6 opacity-25 position-absolute end-0 bottom-0 p-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos -->
                            <div class="row g-4 mb-4">
                                <div class="col-lg-6">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Estadísticas de Préstamos</h5>
                                            <canvas ref="pieChart" class="w-100" style="max-height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Préstamos por Mes</h5>
                                            <canvas ref="barChart" class="w-100" style="max-height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Ingresos Mensuales</h5>
                                            <canvas ref="lineChart" class="w-100" style="max-height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Top 5 Clientes con Mayor Cartera Activa</h5>
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Cliente</th>
                                                        <th scope="col">Cartera Activa (Bs)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="cliente in top_clientes" :key="cliente.id">
                                                        <td>{{ cliente.nombre }} {{ cliente.apellido_paterno }} {{
                                                            cliente.apellido_materno }}</td>
                                                        <td>{{ cliente.cartera_activa }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </main>
    </template>

<script>
import Chart from 'chart.js/auto';
import axios from 'axios';

export default {
    data() {
        return {
            cantidad_clientes: 0,
            cantidad_solicitudes: 0,
            cantidad_planes: 0,
            estadisticas: {
                activos: 0,
                pagados: 0,
                morosos: 0,
                monto_desembolsado: 0,
                saldo_pendiente: 0,
                total_recaudado: 0,
                ingresos_dia: 0,
                ingresos_mes: 0,
                ingresos_anio: 0
            },
            datos_planes_usuarios: [],
            datos_solicitudes_usuario: [],
            datos_pagos: [],
            top_clientes: [],
            barChart: null,
            pieChart: null,
            lineChart: null,
            colors: ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#17a2b8']
        };
    },
    methods: {
        getCantidadClientes() {
            axios.get('/cantidad_clientes')
                .then(response => {
                    this.cantidad_clientes = response.data.cantidad_clientes;
                })
                .catch(error => {
                    console.error('Error fetching cantidad_clientes:', error.message);
                });
        },
        getCantidadSolicitudes() {
            axios.get('/cantidad_solicitudes')
                .then(response => {
                    this.cantidad_solicitudes = response.data.cantidad_solicitudes;
                })
                .catch(error => {
                    console.error('Error fetching cantidad_solicitudes:', error.message);
                });
        },
        getCantidadPlanes() {
            axios.get('/cantidad_planes')
                .then(response => {
                    this.cantidad_planes = response.data.cantidad_planes;
                })
                .catch(error => {
                    console.error('Error fetching cantidad_planes:', error.message);
                });
        },
        getEstadisticasPrestamos() {
            axios.get('/estadisticas_prestamos')
                .then(response => {
                    this.estadisticas = response.data;
                    this.renderPieChart();
                })
                .catch(error => {
                    console.error('Error fetching estadisticas_prestamos:', error.message);
                });
        },
        getCreditosPorUsuario() {
            axios.get('/cantidad_creditos_x_usuario')
                .then(response => {
                    this.datos_planes_usuarios = response.data.planes_pago;
                    this.datos_solicitudes_usuario = response.data.solicitudes;
                    this.renderBarChart();
                })
                .catch(error => {
                    console.error('Error fetching creditos_x_usuario:', error.message);
                });
        },
        getDatosPagos() {
            axios.get('/datos_pagos_grafico')
                .then(response => {
                    this.datos_pagos = response.data;
                    this.renderLineChart();
                })
                .catch(error => {
                    console.error('Error fetching datos_pagos:', error.message);
                });
        },
        getTopClientes() {
            axios.get('/top_clientes')
                .then(response => {
                    this.top_clientes = response.data;
                })
                .catch(error => {
                    console.error('Error fetching top_clientes:', error.message);
                });
        },
        renderBarChart() {
            const ctx = this.$refs.barChart.getContext('2d');
            if (this.barChart) this.barChart.destroy();
            this.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.datos_planes_usuarios.map(usuario => usuario.personal),
                    datasets: [
                        {
                            label: 'Créditos gestionados',
                            data: this.datos_planes_usuarios.map(usuario => usuario.cantidad_creditos),
                            backgroundColor: this.colors[0],
                            borderColor: this.colors[0],
                            borderWidth: 1
                        },
                        {
                            label: 'Solicitudes registradas',
                            data: this.datos_solicitudes_usuario.map(usuario => usuario.cantidad_solicitudes),
                            backgroundColor: this.colors[1],
                            borderColor: this.colors[1],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true },
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        },
        renderPieChart() {
            const ctx = this.$refs.pieChart.getContext('2d');
            if (this.pieChart) this.pieChart.destroy();
            this.pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Activos', 'Pagados', 'Morosos'],
                    datasets: [{
                        data: [
                            this.estadisticas.activos,
                            this.estadisticas.pagados,
                            this.estadisticas.morosos
                        ],
                        backgroundColor: [this.colors[1], this.colors[3], this.colors[2]],
                        borderColor: [this.colors[1], this.colors[3], this.colors[2]],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Distribución por Estado de Préstamo' }
                    }
                }
            });
        },
        renderLineChart() {
            const ctx = this.$refs.lineChart.getContext('2d');
            if (this.lineChart) this.lineChart.destroy();
            this.lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.datos_pagos.map(pago => pago.mes),
                    datasets: [{
                        label: 'Ingresos Mensuales',
                        data: this.datos_pagos.map(pago => pago.ingresos),
                        fill: false,
                        borderColor: this.colors[4],
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Ingresos (Bolivianos)' }
                        },
                        x: { title: { display: true, text: 'Mes' } }
                    }
                }
            });
        }
    },
    mounted() {
        this.getCantidadClientes();
        this.getCantidadSolicitudes();
        this.getCantidadPlanes();
        this.getEstadisticasPrestamos();
        this.getCreditosPorUsuario();
        this.getDatosPagos();
        this.getTopClientes();
    }
};
</script>

<style scoped>
.card {
    border-radius: 0.5rem;
}



.card-body {
    padding: 1.5rem;
}

.table {
    margin-bottom: 0;
}

.table th,
.table td {
    vertical-align: middle;
}

canvas {
    max-width: 100%;
}
</style>