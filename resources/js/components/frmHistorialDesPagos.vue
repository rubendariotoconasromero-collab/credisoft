<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">

                <div v-if="view==0" class="card">
                    <div class="card-header bg-success py-2">
                        <h5 class="header-title my-0 text-center fw-semibold text-white text-uppercase">
                            Historial Desembolsos y Pagos
                        </h5>
                    </div>


                    <div class="card-body">
                        <!-- Filtros -->
                        <!-- <label for="fecha_inicio" class="text-dark fw-bold">Filtros</label> -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input @input="buscarDesembolsos" type="date" v-model="filtros.fecha_inicio"
                                        class="form-control">
                                    <input @input="buscarDesembolsos" type="date" v-model="filtros.fecha_final"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="input-group">
                                    <select v-model="filtros.criterio" class="form-select" @change="buscarDesembolsos">
                                        <option value="cliente.nombre">Nombre del Cliente</option>
                                        <option value="cliente.ci">CI del Cliente</option>
                                        <option value="plan_pago.id">ID del Plan de Pago</option>
                                    </select>
                                    <input type="text" v-model="filtros.buscar" class="form-control"
                                        @input="buscarDesembolsos">
                                    <button class="btn btn-success" @click="buscarDesembolsos">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                        </div>



                        <!-- Totales en Tabla -->
                        <div class="table-responsive" style="border:collapse">
                            <div class="col-lg-9 col-md-8 col-sm-12">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                        <!-- Total Desembolsos -->
                                        <tr>
                                            <th class="text-uppercase text-dark fw-bold">
                                                Total Desembolsos
                                                <br><small class="text-secondary fw-normal">Monto total
                                                    desembolsado</small>
                                            </th>
                                            <td class="text-end">
                                                <span class="fs-5 fw-bold text-dark">{{ totalDesembolsos }}</span>
                                            </td>

                                            <th class="text-uppercase text-dark fw-bold">
                                                Pagos Administrativos
                                                <br><small class="text-secondary fw-normal">Gastos asociados al
                                                    préstamo</small>
                                            </th>
                                            <td class="text-end">
                                                <span class="fs-5 fw-bold text-dark">{{
                                                    parseFloat(totalPagosAdm).toFixed(2) }}</span>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>




                        <!-- Tabla de Desembolsos -->
                        <div class="table-responsive mt-2" style="font-size:11px">
                            <table class="table table-hover table-striped table-sm" style="font-size:12px">
                                <thead class="text-white">
                                    <tr class="table-success">
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Credito</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Fecha</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Cliente</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Garantia</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Forma pago</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Cuotas</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Monto des.</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Pago Adm</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold">Asesor</th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold text-center">Estado
                                        </th>
                                        <th style="font-size:11px" class="text-uppercase fw-bold text-center">Op.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in listaDesembolsos" :key="item.id_plan_pago">
                                        <td style="font-size:11px" class="text-uppercase fw-bold">{{ item.id_plan_pago
                                            }}</td>
                                        <td style="font-size:11px" class="text-uppercase">{{ formatearFecha(item.fecha)
                                            }}</td>
                                        <td style="font-size:11px" class="text-uppercase fw-bold">
                                            {{ item.cliente }}
                                            <span style="font-size:11px" class="text-dark d-block">
                                                <strong>CI: </strong>{{ item.ci }} {{ item.lugar_expedicion }}</span>
                                        </td>
                                        <td style="font-size:11px" class="text-uppercase">{{ item.tipo_garantia }}</td>
                                        <td style="font-size:11px" class="text-uppercase">{{ item.lapso_capital }}</td>
                                        <td style="font-size:11px" class="text-uppercase">{{ item.nro_cuotas }}</td>
                                        <td style="font-size:11px" class="text-uppercase fw-bold">{{
                                            parseFloat(item.monto).toFixed(2) }}</td>
                                        <td style="font-size:11px" class="text-uppercase">{{
                                            parseFloat(item.monto_pago_adm).toFixed(2) }}</td>
                                        <td style="font-size:11px" class="text-uppercase fw-bold">{{ item.asesor }}</td>
                                        <td style="font-size:11px" class="text-uppercase text-center">
                                            <span v-if="item.estado == 1" class="badge bg-danger me-1"
                                                style="min-width: 90px; white-space: nowrap;">
                                                Anulado
                                            </span>
                                            <span v-else class="badge bg-success me-1"
                                                style="min-width: 90px; white-space: nowrap;">
                                                Entregado
                                            </span>
                                        </td>
                                        <td class="text-uppercase text-center">
                                            <div class="btn-group">
                                                <a style="cursor:pointer;" class="text-success dropdown-toggle btn-sm"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fs-4"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li @click="verDetalles(item)">
                                                        <a class="dropdown-item text-info" href="#">
                                                            <i class="fas fa-eye"></i> Ver Detalles
                                                        </a>
                                                    </li>
                                                    <li @click="anularDesembolso(item)" v-if="item.estado == 0">
                                                        <a class="dropdown-item text-danger" href="#">
                                                            <i class="fas fa-times"></i> Anular
                                                        </a>
                                                    </li>
                                                    <li @click="generarComprobante(item)">
                                                        <a class="dropdown-item text-primary" href="#">
                                                            <i class="fas fa-print"></i> Imprimir Comprobante
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="listaDesembolsos.length<=5">
                                <br><br><br><br><br><br><br><br><br><br>
                            </template>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-end">
                            <nav>
                                <ul class="pagination">
                                    <li class="page-item" v-if="pagination.current_page > 1">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page"
                                        :class="[page == isActived ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page
                                            }}</a>
                                    </li>
                                    <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                        <a class="page-link" href="#"
                                            @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>

                    </div>
                </div>


                <div v-if="view==1" class="card border-dark">
                    <!-- Card Header -->
                    <div
                        class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
                        <h5 class="card-title mb-0 fw-bold text-uppercase flex-grow-1 text-center text-white">
                            detalles del desembolso
                        </h5>
                        <button @click="cerrarDetalles()" type="button" class="btn-close btn-close-white"></button>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <template v-if="detalles.desembolso">


                            <!-- Sección Cliente -->
                            <div class="mb-3">
                                <h5 class="fw-bold section-title ms-2">INFORMACIÓN DEL CLIENTE</h5>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 col-sm-12 d-flex justify-content-center">
                                        <div class="info-compact-foto">
                                            <img :src="detalles.desembolso.imagen? '/img/cliente/'+detalles.desembolso.imagen:'/img/empresa/user_img2_old.png'"
                                                class="user-img" alt="Fotografía del cliente">
                                        </div>
                                    </div>
    
                                    <div class="col-md-8">
                                        <div class="border border-dark rounded-3 p-2">
                                            <table class="table table-striped mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td width="20%" class="fw-bold">Cliente:</td>
                                                        <td class="text-uppercase">{{ detalles.desembolso.cliente }} - {{
                                                            detalles.desembolso.ci_cliente }} <strong>{{
                                                                detalles.desembolso.lugar_expedicion }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Teléfono(s):</td>
                                                        <td>
                                                            <template v-if="detalles.telefonos.length > 0">
                                                                <div v-for="(telefono, index) in detalles.telefonos"
                                                                    :key="index" class="mb-1">
                                                                    <span v-if="telefono.tipo === 'Numero telefono'">{{
                                                                        telefono.numero }}</span>
                                                                    <span v-else>{{ telefono.numero }} - {{ telefono.nombre
                                                                        }} - {{ telefono.apellidos }} - {{ telefono.relacion
                                                                        }}</span>
                                                                </div>
                                                            </template>
                                                            <span v-else>No hay teléfonos disponibles</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Dirección(es):</td>
                                                        <td>
                                                            <template v-if="detalles.direcciones.length > 0">
                                                                <div v-for="direccion in detalles.direcciones"
                                                                    :key="direccion.id">
                                                                    <strong class="fw-bold">
                                                                        {{ direccion.tipo }}:
                                                                    </strong>
                                                                    {{
                                                                    (direccion.referencia ? direccion.referencia + ' - ' :
                                                                    '') +
                                                                    (direccion.descripcion ? direccion.descripcion + ' - ' :
                                                                    '') +
                                                                    (direccion.ciudad ? direccion.ciudad + ' - ' : '') +
                                                                    (direccion.departamento ? direccion.departamento : '')
                                                                    }}
                                                                </div>
                                                            </template>
                                                            <span v-else>No hay direcciones disponibles</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Actividad:</td>
                                                        <td>{{ detalles.desembolso.actividad }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Sección Préstamo -->
                            <div class="mb-3">
                                <h5 class="fw-bold section-title ms-2">INFORMACIÓN DEL PRÉSTAMO</h5>
                                <div class="border border-dark rounded-3 p-2">
                                    <table class="table table-striped mb-0">
                                        <tbody>
                                            <tr>
                                                <td width="20%" class="fw-bold">Nro. Crédito:</td>
                                                <td>{{ detalles.desembolso.id_plan_pago }}</td>
                                                <td width="20%" class="fw-bold">Forma de Pago:</td>
                                                <td>{{ detalles.desembolso.lapso_capital }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Importe Préstamo:</td>
                                                <td>{{ parseFloat(detalles.desembolso.importe_prestamo).toFixed(2) }}
                                                    Bs.</td>
                                                <td class="fw-bold">Garantía:</td>
                                                <td>{{ detalles.desembolso.garantia }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Fecha Crédito:</td>
                                                <td>{{ formatearFecha(detalles.desembolso.fecha_credito) }}</td>
                                                <td class="fw-bold">Plazo ({{ detalles.desembolso.lapso_capital }}):
                                                </td>
                                                <td>{{ detalles.desembolso.cuotas }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Fecha Desembolso:</td>
                                                <td>{{ formatearFecha(detalles.desembolso.fecha_desembolso) }}</td>
                                                <!-- <td class="fw-bold">Fecha Máx. Devolución:</td>
                                                <td>{{ formatearFecha(detalles.desembolso.fecha_max_devolucion) }}</td> -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>




                        </template>
                        <div v-else class="alert alert-warning">
                            No se encontraron detalles para este desembolso
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </main>
</template>

<script>
import moment from 'moment';
import Swal from 'sweetalert2'

export default {
    data() {
        return {
            view:0,
            preloader: false,
            filtros: {
                fecha_inicio: moment().subtract(7, 'days').format('YYYY-MM-DD'),
                fecha_final: moment().format('YYYY-MM-DD'),
                criterio: 'cliente.nombre',
                buscar: ''
            },
            detalles: {
                desembolso: {},
                telefonos: [],
                direcciones: [],
                empresa: {},
                fecha_reporte: '',
                monto_primera_cuota: '',
                fecha_primera_cuota: ''
            },
            listaDesembolsos: [],
            totalDesembolsos: 0,
            totalPagosAdm: 0,
        
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 2
        }
    },
    computed: {
        isActived() {
            return this.pagination.current_page;
        },
        pagesNumber() {
            if (!this.pagination.to) {
                return [];
            }
            var from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        cerrarDetalles(){
            this.view=0;
        },

        formatearFecha(fecha) {
            return moment(fecha).format('DD/MM/YYYY');
        },
        buscarDesembolsos() {
            this.getDesembolsos(1);
        },
        getDesembolsos(page) {
            // this.preloader = true;
            axios.get('/historial_desembolsos_pagos_listado', {
                params: {
                    page: page,
                    fecha_inicio: this.filtros.fecha_inicio,
                    fecha_final: this.filtros.fecha_final,
                    criterio: this.filtros.criterio,
                    buscar: this.filtros.buscar
                }
            })
                .then((response) => {
                    this.listaDesembolsos = response.data.desembolsos.data;
                    this.totalDesembolsos = response.data.totalDesembolsos;
                    this.totalPagosAdm = response.data.totalPagosAdm;
                    this.pagination = response.data.desembolsos;
                })
                .catch((error) => {
                    console.error('Error al obtener los desembolsos:', error);
                })
                .finally(() => {
                    // this.preloader = false;
                });
        },

        verDetalles(item) {
            this.preloader = true;
            axios.get('/get_comprobante_cliente_data', {
                params: {
                    id_cliente: item.id_cliente,
                    id_plan_pago: item.id_plan_pago
                }
            })
                .then((response) => {
                    this.detalles = response.data;
                    // $('#modalDetalles').modal('show');
                    this.view=1;
                })
                .catch((error) => {
                    console.error('Error al obtener detalles:', error);
                    Swal.fire('Error', error.response?.data?.error || 'No se pudieron cargar los detalles.', 'error');
                })
                .finally(() => {
                    this.preloader = false;
                });
        },
        anularDesembolso(item) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/anular_desembolso', { id: item.id })
                        .then((response) => {
                            Swal.fire(
                                'Anulado!',
                                'El desembolso ha sido anulado.',
                                'success'
                            );
                            this.getDesembolsos(this.pagination.current_page);
                        })
                        .catch((error) => {
                            console.error('Error al anular el desembolso:', error);
                        });
                }
            });
        },
        generarComprobante(item) {
            const url = `/generar_comprobante_cliente?id_cliente=${item.id_cliente}&id_plan_pago=${item.id_plan_pago}`;
            window.open(url, '_blank');
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.getDesembolsos(page);
        }
    },
    mounted() {
        this.getDesembolsos(1);
    }
}
</script>

<style scoped>
.info-compact-foto {
    width: 100%;
    max-height: 300px; /* Define el tamaño que desees */
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-img {
    width:100%;
    max-height: 300px;
    object-fit:contain; /* La imagen se ajusta y recorta para llenar el contenedor */
    object-position: center; /* Centra la imagen */
    border-radius: 0.375rem; /* Opcional: para que coincida con el estilo de img-thumbnail */
}

.dropdown-toggle::after {
  display: none !important;
}

.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
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
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}


</style>
