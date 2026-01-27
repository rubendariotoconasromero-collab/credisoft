<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
            <!-- <p>Generando reporte...</p> -->
        </div>

        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <div class="page-title">
                                <h4 class="mb-0 font-size-18 text-uppercase">
                                    <i class="fas fa-address-book"></i>
                                    Gestión de Boveda</h4>
                               
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="page-content-wrapper">
                    <div v-if="vista==0" class="row">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row my-3">
                                            <!-- <div class="col-md-6">

                                                    <div class="card-body border-bottom border-1 border-success">
                                                        <h4 class="text-uppercase text-dark">SALDO ACTUAL EN BOVEDA : <strong> {{ saldo_actual_boveda }}</strong></h4>

                                                              
                                                        <span class="badge bg-light text-info"></span> <span
                                                        class="">Fecha apertura</span>: <strong>{{ fecha_apertura_boveda }}</strong>

                                                        
                                                    </div>
                                            
                                            </div> -->

                                            <template v-if="boveda.id_boveda==0">
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm border-success mb-4">
                                                        <div class="card-body">
                                                            <h4 class="text-uppercase text-dark mb-1">
                                                                <i class="fas fa-vault"></i> AUN NO SE APERTURO BOVEDA 
                                                                <!-- <strong class="text-success">{{ boveda.saldo_actual }}</strong> -->
                                                            </h4>
                                                  
                                                            <!-- <p class="text-muted mb-1">
                                                                <span class="badge bg-light text-info"><i class="fas fa-calendar-alt"></i></span> 
                                                                <span class="text-dark">Fecha de apertura: </span> 
                                                                <strong>{{ boveda.fecha_apertura }}</strong>
                                                            </p> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm border-success mb-4">
                                                        <div class="card-body">
                                                            <h4 class="text-uppercase text-dark mb-1">
                                                                <i class="fas fa-vault"></i> SALDO ACTUAL EN BÓVEDA: 
                                                                <strong class="text-success">{{ boveda.saldo_actual }}</strong>
                                                            </h4>
                                                  
                                                            <p class="text-muted mb-1">
                                                                <span class="badge bg-light text-info"><i class="fas fa-calendar-alt"></i></span> 
                                                                <span class="text-dark">Fecha de apertura: </span> 
                                                                <strong>{{ boveda.fecha_apertura }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>


                                            <div class="col-md-6 d-flex justify-content-end">
                                                <div class="d-flex align-items-center justify-content-end">

                                                    <button v-if="boveda.id_boveda==0" class="btn btn-success me-2" @click="aperturarBoveda()">
                                                        <i class="fas fa-arrow-up"></i>
                                                        Apeturar Boveda
                                                    </button>


                                                    <button class="btn btn-success me-2" @click="ingresoBoveda()">
                                                        <i class="fas fa-arrow-up"></i>
                                                        Añadir Fondos
                                                    </button>
    
                                                    <button class="btn btn-danger" @click="retiroBoveda()">
                                                        <i class="fas fa-arrow-down"></i>
                                                        Retirar Fondos
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-12">

                                                    <div class="mt-2">
                                                        <!-- Información de la Bóveda -->
                                                        <!-- <div class="card mb-4">
                                                            <div class="card-header">
                                                                Información de la Bóveda
                                                            </div>
                                                            <div class="card-body">
                                                                <p><strong>Saldo Actual:</strong> ${{ boveda.saldo_actual }}</p>
                                                                <p><strong>Fecha de Apertura:</strong> {{ boveda.fecha_apertura }}</p>
                                                            </div>
                                                        </div> -->

                                                        

                                                        <!-- Tabla de Movimientos de Bóveda -->
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <!-- Sección de filtros -->
                                                                <div class="row mb-4">
                                                                    <!-- Filtro por tipo de movimiento -->
                                                                    <div class="col-md-4 py-0">
                                                                        <label for="tipoMovimiento">Filtrar por:</label>
                                                                        <select @change="buscarMovimientoBoveda()" v-model="filtroTipoMovimiento" class="form-select" id="tipoMovimiento">
                                                                            <option value="todos">Ingresos y Salidas</option>
                                                                            <option value="ingreso">Ingresos</option>
                                                                            <option value="salida">Salidas</option>
                                                                        </select>
                                                                    </div>

                                                                    <!-- Filtro por rango de fechas -->
                                                                    <div class="col-md-4 py-0">
                                                                        <label for="fechaInicio">Fecha Inicio:</label>
                                                                        <input @input="buscarMovimientoBoveda()" type="date" v-model="fechaInicio" class="form-control" id="fechaInicio">
                                                                    </div>
                                                                    <div class="col-md-4 py-0">
                                                                        <label for="fechaFin">Fecha Fin:</label>
                                                                        <input @input="buscarMovimientoBoveda()" type="date" v-model="fechaFin" class="form-control" id="fechaFin">
                                                                    </div>
                                                                </div>

                                                                <div class="row mb-2">
                                                                    <div class="col-md-6 py-0">
                                                                        <h5>
                                                                            <span style="border-radius:0" class="badge bg-success fw-bold">Total Ingresos Bs.:  </span>
                                                                            <span style="border-radius:0" class="badge text-success fw-bold">{{  totalIngresosBoveda }}  </span>
                                                                        </h5>
                                                                    </div>

                                                                    <div class="col-md-6 py-0">
                                                                        <h5>
                                                                            <span style="border-radius:0" class="badge bg-danger fw-bold">Total Salidas Bs.:  </span>
                                                                            <span style="border-radius:0" class="badge text-danger fw-bold">{{  totalSalidasBoveda }}  </span>
                                                                        </h5>
                                                                    </div>

                                                                </div>

                                                                <h6>
                                                                    Movimientos de Bóveda
                                                                </h6>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-striped table-hover table-bordered">
                                                                        <thead class="bg-success text-white">
                                                                            <tr>
                                                                                <th scope="col">#</th>
                                                                                <th scope="col">Tipo de Movimiento</th>
                                                                                <th scope="col">Monto</th>
                                                                                <th scope="col">Descripción</th>
                                                                                <th scope="col">Usuario</th>
                                                                                <th scope="col">Fecha</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr v-for="(movimiento, index) in movimientosBoveda" :key="index">
                                                                                <th scope="row">{{ index + 1 }}</th>
                                                                                <td>{{ movimiento.tipo_movimiento }}</td>
                                                                                <td>${{ movimiento.monto }}</td>
                                                                                <td>{{ movimiento.descripcion }}</td>
                                                                                <td>{{ movimiento.personal }}</td>
                                                                                <td>{{ movimiento.fecha }}</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <nav>
                                                                    <ul class="pagination justify-content-end mb-0">
                                                                        <li class="page-item" v-if="pagination_movimientos_boveda.current_page > 1">
                                                                            <a class="page-link" href="#"
                                                                                @click.prevent="cambiarPagina(pagination_movimientos_boveda.current_page - 1)">Ant</a>
                                                                        </li>
                                                                        <li class="page-item" v-for="page in pagesNumber" :key="page"
                                                                            :class="[page==isActived ? 'active' :'']">
                                                                            <a class="page-link" href="#"
                                                                                @click.prevent="cambiarPagina(page)"
                                                                                :v-text="page">{{ page }}</a>
                                                                        </li>
                                                                        <li class="page-item"
                                                                            v-if="pagination_movimientos_boveda.current_page < pagination_movimientos_boveda.last_page">
                                                                            <a class="page-link" href="#"
                                                                                @click.prevent="cambiarPagina(pagination_movimientos_boveda.current_page + 1)">Sig</a>
                                                                        </li>
                                                                    </ul>
                                                                </nav>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                      
                                    </div>
                                </div>
                                <!-- End Card -->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>

                </div>
                <!-- end page-content-wrapper-->
            </div>
            <!-- Container-fluid -->

            <!-- Modal para Ingresar a Bóveda -->
            <div class="modal fade" id="modalIngresoBoveda" tabindex="-1"  data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content border border-1 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="modalIngresoLabel">Ingresar a Bóveda</h5>
                        <button @click="cerrarModalIngresoBoveda()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                        <form @submit.prevent="validarIngreso">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="montoIngreso" class="form-label">Monto a Ingresar</label>
                                    <input type="number" class="form-control" id="montoIngreso" v-model="montoIngreso" placeholder="Ingrese el monto" required>
                                </div>
                                <!-- Select de descripción de ingreso -->
                                <div class="mb-3">
                                    <label for="descripcionIngreso" class="form-label">Descripción de Ingreso</label>
                                    <select class="form-select" v-model="descripcionIngresoSeleccionada">
                                        <option value="" selected hidden disabled>Eliga una opción...</option>
                                        <option v-for="opcion in opcionesIngreso" :key="opcion" :value="opcion">{{ opcion }}</option>
                                    </select>
                                </div>

                                <!-- Textarea que aparece cuando se selecciona "Otro" -->
                                <div v-if="descripcionIngresoSeleccionada === 'otro'" class="mb-3">
                                    <label for="otraDescripcionIngreso" class="form-label">Especificar otro</label>
                                    <textarea class="form-control" id="otraDescripcionIngreso" v-model="otraDescripcionIngreso" rows="3" placeholder="Ingrese la descripción"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button @click="cerrarModalIngresoBoveda()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Ingresar</button>
                            </div>
                        </form>
                 
                    </div>
                </div>
            </div>

            
            <!-- Modal retiro a boveda -->
            <div class="modal fade" id="modalRetiroBoveda" tabindex="-1" aria-labelledby="modalRetiroLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content border border-1 border-success">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="modalRetiroLabel">Retirar de Bóveda</h5>
                        <button @click="cerrarModalRetiroBoveda()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                        <form @submit.prevent="validarRetiro">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="montoRetiro" class="form-label">Monto a Retirar</label>
                                    <input type="number" class="form-control" id="montoRetiro" v-model="montoRetiro" placeholder="Ingrese el monto" required>
                                </div>
                                <!-- Select de descripción de retiro -->
                                <div class="mb-3">
                                    <label for="descripcionRetiro" class="form-label">Descripción de Retiro</label>
                                    <select class="form-select" v-model="descripcionRetiroSeleccionada">
                                        <option value="" selected hidden disabled>Eliga una opción...</option>
                                        <option v-for="opcion in opcionesRetiro" :key="opcion" :value="opcion">{{ opcion }}</option>
                                    </select>
                                </div>

                                <!-- Textarea que aparece cuando se selecciona "Otro" -->
                                <div v-if="descripcionRetiroSeleccionada === 'otro'" class="mb-3">
                                    <label for="otraDescripcionRetiro" class="form-label">Especificar otro</label>
                                    <textarea class="form-control" id="otraDescripcionRetiro" v-model="otraDescripcionRetiro" rows="3" placeholder="Ingrese la descripción"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button @click="cerrarModalRetiroBoveda()"  type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Retirar</button>
                            </div>
                        </form>
                 
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
                // Filtros
                filtroTipoMovimiento: 'todos', // Valores: 'todos', 'ingreso', 'salida'
                fechaInicio: moment().subtract(1, 'months').format('YYYY-MM-DD'), // Un mes antes de la fecha actual
                fechaFin: moment().format('YYYY-MM-DD'), // Fecha actual


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

                // Motivos de ingreso de capital
                opcionesIngreso: [

                    'Devolución de préstamo',
                    'Aporte de capital',
                    // 'Ingresos por intereses',
                    // 'Cobro de comisiones',
                    'Recuperación de deudas',
                    // 'Depósito bancario',
                    'Inversión de terceros',
                    'Subvenciones o donaciones',
                    // 'Transferencias de otras cuentas',
                    // 'Rendimientos de inversiones',
                    // 'Ganancias por ventas de productos/servicios',
                    'otro'
                ],
                // Motivos de salida de capital
                opcionesRetiro: [
                    'Otorgamiento de préstamo',
                    // 'Pago de intereses',
                    'Gastos operativos',
                    'Devolución de capital a socios',
                    'Pago de dividendos',
                    'Compra de activos',
                    // 'Transferencia a otras cuentas',
                    'Pagos de impuestos',
                    // 'Pago de proveedores',
                    'Pago de comisiones',
                    // 'Inversiones en otras empresas',
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
                    per_page: 10, // Número de elementos por página
                    last_page: 0,
                    from: 0,
                    to: 0
                },
                offset_movimientos_boveda: 2, // Offset para mostrar número de páginas

                fecha_fin:moment().format('YYYY-MM-DD'),
  

            }
        },
     
        methods: {
            async aperturarBoveda(){

                try {
                    // Intentamos aperturar la bóveda
                    await this.aperturarBovedaPrivate();

                    // Intentamos obtener los datos actualizados de la bóveda
                    await this.getBoveda();

                    // Si todo sale bien, mostramos un mensaje de éxito
                    Swal.fire({
                        title: 'Se aperturó la Bóveda',
                        confirmButtonText: 'Aceptar',
                        icon: 'success',
                    });

                } catch (error) {
                    // Capturamos cualquier error y mostramos un mensaje de error
                    Swal.fire({
                        title: 'Error al aperturar la Bóveda',
                        text: error.response?.data?.message || 'Ocurrió un error inesperado',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                    });
                }

            },
            async aperturarBovedaPrivate(){
                await axios.post('/aperturar_boveda') // Ajusta la URL según tu backend
                    .then((response) => {
                        console.log(response);
                    })
                    .catch((error) => {
                        console.error('Error:', error.message);
                    });
            },
            retiroBoveda(){
                if(this.boveda.id_boveda==0){
                    Swal.fire({
                        title:'Advertencia',
                        text:'Debe Aperturar Boveda',
                        confirmButtonText:'Aceptar',
                        icon:'warning',

                    });
                    
                    return;
                }
                this.montoRetiro=0;
                this.descripcionRetiroSeleccionada='',
                this.otraDescripcionRetiro='',
                this.abrirModalRetiroBoveda();
            },

            ingresoBoveda(){
                this.montoIngreso=0;
                this.descripcionIngresoSeleccionada='',
                this.otraDescripcionIngreso='',
                this.abrirModalIngresoBoveda();
            },
            // Método para abrir el modal de ingreso a bóveda
            abrirModalIngresoBoveda() {
                // let modalIngreso = new bootstrap.Modal(document.getElementById('modalIngresoBoveda'));
                // modalIngreso.show();
                $('#modalIngresoBoveda').modal('show');
            },
            // Método para abrir el modal de retiro de bóveda
            abrirModalRetiroBoveda() {
                // let modalRetiro = new bootstrap.Modal(document.getElementById('modalRetiroBoveda'));
                // modalRetiro.show();
                $('#modalRetiroBoveda').modal('show');

            },

            // Método para abrir el modal de ingreso a bóveda
            cerrarModalIngresoBoveda() {
                // let modalIngreso = new bootstrap.Modal(document.getElementById('modalIngresoBoveda'));
                // modalIngreso.hide();
                $('#modalIngresoBoveda').modal('hide');   
            },
            // Método para abrir el modal de retiro de bóveda
            cerrarModalRetiroBoveda() {
                // let modalRetiro = new bootstrap.Modal(document.getElementById('modalRetiroBoveda'));
                // modalRetiro.hide();
                $('#modalRetiroBoveda').modal('hide');   
            },

            // Validar ingreso antes de enviar el formulario
            validarIngreso() {
                if (!this.montoIngreso || !this.descripcionIngresoSeleccionada) {
                    Swal.fire('Advertencia', 'Por favor complete todos los campos.', 'warning');
                    return;
                }

                // Concatenar si es "otro"
                let descripcionFinal = this.descripcionIngresoSeleccionada === 'otro'
                    ? `otro: ${this.otraDescripcionIngreso}`
                    : this.descripcionIngresoSeleccionada;


                this.ingresarABoveda(descripcionFinal); // Si pasa la validación, llamar al método de ingreso
            },

            // Validar retiro antes de enviar el formulario
            validarRetiro() {
                if (!this.montoRetiro || !this.descripcionRetiroSeleccionada) {
                    Swal.fire('Advertencia', 'Por favor complete todos los campos.', 'warning');
                    return;
                }
                if ((this.boveda.saldo_actual - this.montoRetiro) <= 0) {
                    Swal.fire('Advertencia', 'No tiene saldo suficiente para el retiro.', 'warning');
                    return;
                }

                // Concatenar si es "otro"
                let descripcionFinal = this.descripcionRetiroSeleccionada === 'otro'
                    ? `otro: ${this.otraDescripcionRetiro}`
                    : this.descripcionRetiroSeleccionada;
                    
                this.retirarDeBoveda(descripcionFinal); // Si pasa la validación, llamar al método de retiro
            },

            // Método asíncrono para ingresar a bóveda con SweetAlert2
            async ingresarABoveda(descripcion) {
                try {
                    const response = await axios.post('/ingresar_boveda', {
                        monto: this.montoIngreso,
                        descripcion: descripcion,
                    });

                    Swal.fire({title:'Éxito', text: 'Ingreso a bóveda registrado con éxito.', icon:'success', timer:1500});
                    this.montoIngreso = ''; // Limpiar campos
                    this.descripcionIngreso = '';
                    await this.getMovimientosBoveda(); // Refrescar los movimientos
                    await this.getBoveda();
                    this.cerrarModalIngresoBoveda();
                } catch (error) {
                    Swal.fire('Error', 'Ocurrió un error al ingresar a la bóveda.', 'error');
                    console.error('Error al ingresar a bóveda:', error);
                }
            },

            // Método asíncrono para retirar de bóveda con SweetAlert2
            async retirarDeBoveda(descripcion) {
                try {
                    const response = await axios.post('/retirar_boveda', {
                        monto: this.montoRetiro,
                        descripcion: descripcion,
                    });

                    Swal.fire({title:'Éxito', text:'Retiro de bóveda registrado con éxito.', icon:'success', timer:1500});
                    this.montoRetiro = ''; // Limpiar campos
                    this.descripcionRetiro = '';
                    this.cerrarModalRetiroBoveda();
                    await this.getMovimientosBoveda(); // Refrescar los movimientos
                    await this.getBoveda();
                } catch (error) {
                    Swal.fire('Error', 'Ocurrió un error al retirar de la bóveda.', 'error');
                    console.error('Error al retirar de bóveda:', error);
                }
            },
            async getBoveda() {
                await axios.get('/get_boveda') // Ajusta la URL según tu backend
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

            // Método para buscar con filtros
            async buscarMovimientoBoveda() {
                await this.getMovimientosBoveda();
            },
            // Método para obtener los movimientos de bóveda y calcular los totales
            async getMovimientosBoveda(page=1) {
                const params = {
                    page: page,
                    tipo: this.filtroTipoMovimiento,
                    fecha_inicio: this.fechaInicio,
                    fecha_fin: this.fechaFin
                };
                await axios.get('/get_movimientos_boveda?page=', { params }) // Ajusta la URL según tu backend
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
                        console.error('Error al obtener los movimientos de la bóveda:', error);
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
            console.log('Component mounted.');
            await this.getBoveda();
            await this.getMovimientosBoveda();
            this.preloader=false;
        }


    }

</script>

<style>
.image-container {
    width: 100%;
    height: auto;
    margin-bottom:1rem;
}
.image-container img {
    width: 100%;
    height: auto;
    max-width: 100%; /* Evita que la imagen se estire más allá de su tamaño natural */
}

</style>

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
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

p {
  color: white;
  margin-top: 10px;
}
</style>

<style>


/* Estilo general del dropdown y su contenedor */
.dropdown-wrapper {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    transition: background-color 0.3s ease-in-out;
}

.dropdown-wrapper:hover {
    background-color: #e9ecef;
}

/* Estilo del elemento seleccionado */
.selected-item {
    border-radius: 0.375rem;
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: background-color 0.3s ease;
}

.selected-item:hover {
    background-color: #f1f3f5;
}

/* Estilo del dropdown cuando está visible o invisible */
.dropdown-popover {
    background-color: white;
    border-radius: 0.375rem;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    max-height: 250px;
    overflow-y: auto;
    transition: all 0.3s ease-in-out;
}

.dropdown-popover.visible {
    display: block;
}

.dropdown-popover.invisible {
    display: none;
}

/* Estilo del campo de entrada (input) */
.form-control-sm {
    border: none;
    padding: 0.5rem;
    font-size: 14px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #ced4da;
    transition: background-color 0.3s ease-in-out;
}

.form-control-sm:focus {
    background-color: #ffffff;
    outline: none;
}

/* Estilo de los elementos de la lista (li) */
.list-group-item {
    padding: 0.5rem 1rem;
    transition: background-color 0.2s ease-in-out;
}

.list-group-item:hover {
    background-color: #f1f3f5;
}

/* Estilo del mensaje de lista vacía */
.text-muted {
    font-size: 13px;
}

/* Estilo de los resultados */
.fw-bold {
    font-weight: 600;
}

.bg-success-50 {
    background-color: rgba(161, 209, 163, 0.857); /* Color success con 50% de opacidad */
}
</style>
