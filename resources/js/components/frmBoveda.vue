<template>
    <main class="vault-management">
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <div class="card">
                    <div class="card-header bg-warning bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Gestión de Bóveda
                        </h5>
                    </div>

                    <div class="card-body pt-4">
                        
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-6">
                                <div v-if="boveda.id_boveda === 0" class="alert alert-secondary border-secondary shadow-sm d-flex align-items-center mb-0 p-3" role="alert">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-lock fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading fw-bold mb-0 text-uppercase text-dark" style="font-size: 1rem;">Bóveda Cerrada</h5>
                                        <small class="text-muted">Debe realizar la apertura para registrar movimientos.</small>
                                    </div>
                                </div>

                                <div v-else class="alert alert-success border-success shadow-sm d-flex align-items-center mb-0 p-3" role="alert">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-vault fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-uppercase mb-0 text-success" style="font-size: 0.8rem;">Saldo Actual en Bóveda</h6>
                                        <h3 class="fw-bold mb-0 text-dark">{{ boveda.saldo_actual }} <span class="fs-6 text-muted">Bs.</span></h3>
                                        <small class="text-muted"><i class="fas fa-calendar-day me-1"></i> Apertura: {{ boveda.fecha_apertura }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 text-end mt-3 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-start gap-2">
                                    <button v-if="boveda.id_boveda === 0" class="btn btn-success fw-bold shadow-sm px-4" @click="aperturarBoveda()">
                                        <i class="fas fa-key me-2"></i> Aperturar Bóveda
                                    </button>
                                    
                                    <template v-else>
                                        <button class="btn btn-success shadow-sm" @click="ingresoBoveda()">
                                            <i class="fas fa-plus-circle me-1"></i> Añadir Fondos
                                        </button>
                                        <button class="btn btn-danger shadow-sm" @click="retiroBoveda()">
                                            <i class="fas fa-minus-circle me-1"></i> Retirar Fondos
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 text-muted">TIPO MOVIMIENTO</label>
                                <select @change="buscarMovimientoBoveda()" v-model="filtroTipoMovimiento" class="form-select form-select-sm border-secondary">
                                    <option value="todos">Todos los Movimientos</option>
                                    <option value="ingreso">Solo Ingresos</option>
                                    <option value="salida">Solo Salidas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 text-muted">FECHA INICIO</label>
                                <input @input="buscarMovimientoBoveda()" type="date" v-model="fechaInicio" class="form-control form-control-sm border-secondary">
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1 text-muted">FECHA FIN</label>
                                <input @input="buscarMovimientoBoveda()" type="date" v-model="fechaFin" class="form-control form-control-sm border-secondary">
                            </div>
                            
                            <div class="col-md-3">
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <div class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 w-100 text-end">
                                        Ingresos: <strong class="fs-6 ms-1">{{ totalIngresosBoveda }} Bs.</strong>
                                    </div>
                                    <div class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 w-100 text-end">
                                        Salidas: <strong class="fs-6 ms-1">{{ totalSalidasBoveda }} Bs.</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="font-size: 12px">
                            <table class="table table-hover table-striped table-sm align-middle border">
                                <thead class="table-success text-dark text-uppercase fw-bold">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th style="width: 120px;">Tipo</th>
                                        <th class="text-end pe-4">Monto (Bs)</th>
                                        <th>Descripción</th>
                                        <th>Usuario</th>
                                        <th class="text-center">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(movimiento, index) in movimientosBoveda" :key="index">
                                        <td class="text-center fw-bold text-muted">{{ index + 1 }}</td>
                                        <td>
                                            <span :class="movimiento.tipo_movimiento === 'ingreso' ? 'badge bg-success w-100' : 'badge bg-danger w-100'">
                                                {{ movimiento.tipo_movimiento }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-end pe-4 font-monospace fs-6 text-dark">{{ movimiento.monto }}</td>
                                        <td class="text-uppercase">{{ movimiento.descripcion }}</td>
                                        <td class="text-uppercase small"><i class="fas fa-user-circle me-1 text-muted"></i> {{ movimiento.personal }}</td>
                                        <td class="text-center">{{ movimiento.fecha }}</td>
                                    </tr>
                                    <tr v-if="movimientosBoveda.length === 0">
                                        <td colspan="6" class="text-center py-5 text-muted fst-italic bg-light">
                                            <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                            No se encontraron movimientos en este rango de fechas.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <nav v-if="pagination_movimientos_boveda.last_page > 1" class="mt-3">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <li class="page-item" :class="{ disabled: pagination_movimientos_boveda.current_page <= 1 }">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination_movimientos_boveda.current_page - 1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page == isActived }">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination_movimientos_boveda.current_page >= pagination_movimientos_boveda.last_page }">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination_movimientos_boveda.current_page + 1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalIngresoBoveda" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-2 border-dark">
                    <div class="modal-header bg-warning py-2">
                        <h5 class="modal-title text-dark fw-bold text-uppercase"><i class="fas fa-plus-circle me-2"></i> Ingresar a Bóveda</h5>
                        <button @click="cerrarModalIngresoBoveda()" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="validarIngreso">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1">Monto a Ingresar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white fw-bold">Bs.</span>
                                    <input type="number" class="form-control form-control-lg fw-bold text-dark" v-model="montoIngreso" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1">Concepto / Motivo</label>
                                <select class="form-select" v-model="descripcionIngresoSeleccionada" required>
                                    <option value="" disabled selected>Seleccione una opción...</option>
                                    <option v-for="opcion in opcionesIngreso" :key="opcion" :value="opcion">{{ opcion }}</option>
                                </select>
                            </div>
                            <div v-if="descripcionIngresoSeleccionada === 'otro'" class="form-group">
                                <label class="fw-bold mb-1">Especificar Detalle</label>
                                <textarea class="form-control" v-model="otraDescripcionIngreso" rows="2" placeholder="Escriba el detalle del ingreso..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button @click="cerrarModalIngresoBoveda()" type="button" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Cancelar</button>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Registrar Ingreso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRetiroBoveda" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-2 border-dark">
                    <div class="modal-header bg-warning py-2">
                        <h5 class="modal-title text-dark fw-bold text-uppercase"><i class="fas fa-minus-circle me-2"></i> Retirar de Bóveda</h5>
                        <button @click="cerrarModalRetiroBoveda()" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="validarRetiro">
                        <div class="modal-body p-4">
                            <div class="alert alert-warning py-2 mb-3 d-flex justify-content-between align-items-center">
                                <small class="text-dark">Saldo disponible:</small>
                                <strong class="fs-5">{{ boveda.saldo_actual }} Bs.</strong>
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1">Monto a Retirar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white fw-bold">Bs.</span>
                                    <input type="number" class="form-control form-control-lg fw-bold text-danger" v-model="montoRetiro" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1">Concepto / Motivo</label>
                                <select class="form-select" v-model="descripcionRetiroSeleccionada" required>
                                    <option value="" disabled selected>Seleccione una opción...</option>
                                    <option v-for="opcion in opcionesRetiro" :key="opcion" :value="opcion">{{ opcion }}</option>
                                </select>
                            </div>
                            <div v-if="descripcionRetiroSeleccionada === 'otro'" class="form-group">
                                <label class="fw-bold mb-1">Especificar Detalle</label>
                                <textarea class="form-control" v-model="otraDescripcionRetiro" rows="2" placeholder="Escriba el detalle del retiro..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button @click="cerrarModalRetiroBoveda()" type="button" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Cancelar</button>
                            <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-save me-1"></i> Registrar Retiro</button>
                        </div>
                    </form>
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
                // Filtros
                filtroTipoMovimiento: 'todos', 
                fechaInicio: moment().subtract(1, 'months').format('YYYY-MM-DD'), 
                fechaFin: moment().format('YYYY-MM-DD'), 

                fecha_apertura_boveda:moment().format('YYYY-MM-DD'),
                saldo_actual_boveda:0,
                totalIngresosBoveda:0,
                totalSalidasBoveda:0,
                preloader:false,

                montoIngreso: '',
                montoRetiro: '',

                descripcionIngresoSeleccionada: '',
                otraDescripcionIngreso: '',

                descripcionRetiroSeleccionada: '',
                otraDescripcionRetiro: '',

                opcionesIngreso: [
                    'Devolución de préstamo',
                    'Aporte de capital',
                    'Recuperación de deudas',
                    'Inversión de terceros',
                    'Subvenciones o donaciones',
                    'otro'
                ],
                opcionesRetiro: [
                    'Otorgamiento de préstamo',
                    'Gastos operativos',
                    'Devolución de capital a socios',
                    'Pago de dividendos',
                    'Compra de activos',
                    'Pagos de impuestos',
                    'Pago de comisiones',
                    'Pago de salarios y beneficios',
                    'otro'
                ],

                vista:0,
                boveda:{
                    id_boveda:0,
                    saldo_actual:0,
                    fecha_apertura:moment().format('YYYY-MM-DD'),
                },

                movimientosBoveda:[],

                pagination_movimientos_boveda: {
                    total: 0,
                    current_page: 1,
                    per_page: 10,
                    last_page: 0,
                    from: 0,
                    to: 0
                },
                offset_movimientos_boveda: 2,
                fecha_fin:moment().format('YYYY-MM-DD'),
            }
        },
     
        methods: {
            async aperturarBoveda(){
                try {
                    await this.aperturarBovedaPrivate();
                    await this.getBoveda();
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Se aperturó la Bóveda correctamente',
                        confirmButtonText: 'Aceptar',
                        icon: 'success',
                        timer: 1500
                    });
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.response?.data?.message || 'Ocurrió un error inesperado',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                    });
                }
            },
            async aperturarBovedaPrivate(){
                await axios.post('/aperturar_boveda');
            },
            retiroBoveda(){
                if(this.boveda.id_boveda==0){
                    Swal.fire({
                        title:'Atención',
                        text:'Debe Aperturar Bóveda primero',
                        confirmButtonText:'Aceptar',
                        icon:'warning',
                    });
                    return;
                }
                this.montoRetiro = '';
                this.descripcionRetiroSeleccionada = '';
                this.otraDescripcionRetiro = '';
                this.abrirModalRetiroBoveda();
            },

            ingresoBoveda(){
                this.montoIngreso = '';
                this.descripcionIngresoSeleccionada = '';
                this.otraDescripcionIngreso = '';
                this.abrirModalIngresoBoveda();
            },
            
            abrirModalIngresoBoveda() {
                $('#modalIngresoBoveda').modal('show');
            },
            
            abrirModalRetiroBoveda() {
                $('#modalRetiroBoveda').modal('show');
            },

            cerrarModalIngresoBoveda() {
                $('#modalIngresoBoveda').modal('hide');   
            },
            
            cerrarModalRetiroBoveda() {
                $('#modalRetiroBoveda').modal('hide');   
            },

            validarIngreso() {
                if (!this.montoIngreso || !this.descripcionIngresoSeleccionada) {
                    Swal.fire('Advertencia', 'Por favor complete todos los campos.', 'warning');
                    return;
                }
                let descripcionFinal = this.descripcionIngresoSeleccionada === 'otro'
                    ? `otro: ${this.otraDescripcionIngreso}`
                    : this.descripcionIngresoSeleccionada;

                this.ingresarABoveda(descripcionFinal);
            },

            validarRetiro() {
                if (!this.montoRetiro || !this.descripcionRetiroSeleccionada) {
                    Swal.fire('Advertencia', 'Por favor complete todos los campos.', 'warning');
                    return;
                }
                if ((this.boveda.saldo_actual - this.montoRetiro) < 0) {
                    Swal.fire('Error', 'No tiene saldo suficiente para el retiro.', 'error');
                    return;
                }
                let descripcionFinal = this.descripcionRetiroSeleccionada === 'otro'
                    ? `otro: ${this.otraDescripcionRetiro}`
                    : this.descripcionRetiroSeleccionada;
                    
                this.retirarDeBoveda(descripcionFinal); 
            },

            async ingresarABoveda(descripcion) {
                try {
                    await axios.post('/ingresar_boveda', {
                        monto: this.montoIngreso,
                        descripcion: descripcion,
                    });

                    Swal.fire({title:'Éxito', text: 'Ingreso a bóveda registrado con éxito.', icon:'success', timer:1500});
                    this.montoIngreso = ''; 
                    this.descripcionIngresoSeleccionada = '';
                    await this.getMovimientosBoveda(); 
                    await this.getBoveda();
                    this.cerrarModalIngresoBoveda();
                } catch (error) {
                    Swal.fire('Error', 'Ocurrió un error al ingresar a la bóveda.', 'error');
                    console.error('Error al ingresar a bóveda:', error);
                }
            },

            async retirarDeBoveda(descripcion) {
                try {
                    await axios.post('/retirar_boveda', {
                        monto: this.montoRetiro,
                        descripcion: descripcion,
                    });

                    Swal.fire({title:'Éxito', text:'Retiro de bóveda registrado con éxito.', icon:'success', timer:1500});
                    this.montoRetiro = ''; 
                    this.descripcionRetiroSeleccionada = '';
                    this.cerrarModalRetiroBoveda();
                    await this.getMovimientosBoveda(); 
                    await this.getBoveda();
                } catch (error) {
                    Swal.fire('Error', 'Ocurrió un error al retirar de la bóveda.', 'error');
                    console.error('Error al retirar de bóveda:', error);
                }
            },
            async getBoveda() {
                await axios.get('/get_boveda') 
                    .then((response) => {
                        this.boveda.id_boveda = response.data.id_boveda;
                        this.boveda.saldo_actual = response.data.saldo_actual;
                        this.boveda.fecha_apertura = response.data.fecha_apertura;
                    })
                    .catch((error) => {
                        console.error('Error al obtener la información de la bóveda:', error);
                    });
            },

            cambiarPagina(page){
                this.pagination_movimientos_boveda.current_page=page;
                this.getMovimientosBoveda(page);
            },

            async buscarMovimientoBoveda() {
                await this.getMovimientosBoveda();
            },
            
            async getMovimientosBoveda(page=1) {
                const params = {
                    page: page,
                    tipo: this.filtroTipoMovimiento,
                    fecha_inicio: this.fechaInicio,
                    fecha_fin: this.fechaFin
                };
                await axios.get('/get_movimientos_boveda', { params }) 
                    .then((response) => {
                        this.movimientosBoveda = response.data.movimientos.data;

                        this.pagination_movimientos_boveda = {
                            total: response.data.movimientos.total,
                            current_page: response.data.movimientos.current_page,
                            per_page: response.data.movimientos.per_page,
                            last_page: response.data.movimientos.last_page,
                            from: response.data.movimientos.from,
                            to: response.data.movimientos.to
                        };

                        this.totalIngresosBoveda = response.data.totales.ingresos;
                        this.totalSalidasBoveda = response.data.totales.salidas;
                    })
                    .catch((error) => {
                        console.error('Error al obtener los movimientos:', error);
                    });
            },
        },

        computed: {
            isActived: function(){
                return this.pagination_movimientos_boveda.current_page;
            },
            pagesNumber: function(){
                if(!this.pagination_movimientos_boveda.to){
                    return [];
                }                
                var from = this.pagination_movimientos_boveda.current_page - this.offset_movimientos_boveda;
                if(from < 1){
                    from = 1;
                }
                var to = from + (this.offset_movimientos_boveda * 2);
                if(to >= this.pagination_movimientos_boveda.last_page){
                    to = this.pagination_movimientos_boveda.last_page;
                }
                var pagesArray = [];
                while(from <= to){
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;
            },
        },
        async mounted() {
            this.preloader = true;
            await this.getBoveda();
            await this.getMovimientosBoveda();
            this.preloader=false;
        }
    }
</script>

<style scoped>
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
  border-top: 4px solid #f1c40f; /* Amarillo para coincidir con el tema */
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.table-responsive .badge{
    border-radius:20px;
}
</style>