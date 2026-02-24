<template>
    <main class="financial-consultation">
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <div v-if="view === 0" class="card">
                    <div class="card-header bg-warning bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Consultas Financieras
                        </h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row mb-3 mt-3">
                            <div class="col-md-8 my-1">
                                <div class="input-group">
                                    <select v-model="searchCriteria" class="form-select">
                                        <option value="cliente">Cliente</option>
                                        <option value="fecha">Fecha</option>
                                        <option value="monto">Monto</option>
                                    </select>
                                    <input v-model="searchQuery" type="text" class="form-control" placeholder="Buscar..." />
                                    <button class="btn btn-success"><i class="fas fa-search"></i></button>
                                    <button class="btn btn-warning ms-1"><i class="fas fa-print"></i></button>
                                </div>
                            </div>
                            <div class="col-md-4 my-1 text-end">
                                <button class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Nueva Consulta
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-success text-white text-uppercase fw-bold">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Tipo Consulta</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items" :key="item.id" class="align-middle">
                                        <td class="text-uppercase fw-bold">{{ item.fecha }}</td>
                                        <td class="text-uppercase fw-bold">{{ item.cliente }}</td>
                                        <td class="text-uppercase">{{ item.tipo }}</td>
                                        <td class="text-uppercase">{{ item.monto }}</td>
                                        <td class="text-uppercase">
                                            <span :class="item.estado === 1 ? 'badge bg-success w-100' : 'badge bg-danger w-100'">
                                                {{ item.estado === 1 ? "Completado" : "Pendiente" }}
                                            </span>
                                        </td>
                                        <td class="position-relative p-2 text-center">
                                            <div class="btn-group" role="group">
                                                <a style="cursor: pointer" class="text-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li><a class="dropdown-item text-primary" href="#"><i class="fas fa-pencil-alt me-2"></i> Editar</a></li>
                                                    <li><a class="dropdown-item text-info" href="#"><i class="fas fa-eye me-2"></i> Ver</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="items.length === 0">
                                        <td colspan="6" class="text-center">No se encontraron registros.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            preloader: false,
            items: [],
            searchCriteria: "cliente",
            searchQuery: "",
            view: 0,
        };
    },
    mounted() {
        // Simulación de carga inicial
        console.log('Módulo de Consultas Financieras con diseño de Clientes cargado.');
    }
};
</script>

<style scoped>
/* Estilos replicados de la estructura del sistema */
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.header-title {
    letter-spacing: 1px;
}

.table-responsive {
    min-height: 300px;
}
</style>
