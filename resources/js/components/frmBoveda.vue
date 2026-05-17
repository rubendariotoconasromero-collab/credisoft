<template>
    <main class="vault-management">
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning bg-gradient py-3">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            <i class="fas fa-vault me-2"></i>Gestión de Bóveda
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        
                        <!-- Vault Status and Actions Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-lg-7">
                                <div v-if="boveda.id_boveda === 0" class="card border-0 bg-light-secondary shadow-sm h-100">
                                    <div class="card-body d-flex align-items-center p-4">
                                        <!-- <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-4" style="width: 64px; height: 64px; min-width: 64px;">
                                            <i class="fas fa-lock fa-2x"></i>
                                        </div> -->
                                        <div>
                                            <h4 class="fw-bold mb-1 text-uppercase text-secondary">Bóveda Cerrada</h4>
                                            <p class="text-muted mb-0">Debe realizar la apertura para registrar movimientos financieros.</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="card border-0 bg-light-success shadow-sm h-100 border-start border-4 border-success">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 56px; height: 56px; min-width: 56px;">
                                                <i class="fas fa-shield-alt fa-2x"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-uppercase mb-0 text-success small">Saldo Actual en Bóveda</h6>
                                                <h2 class="fw-bold mb-0 text-dark">
                                                    {{ formatNumero(boveda.saldo_actual) }} <span class="fs-5 text-muted fw-normal">Bs.</span>
                                                </h2>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-4 text-muted small">
                                            <span><i class="fas fa-calendar-day me-2 text-success"></i><b>Apertura:</b> {{ boveda.fecha_apertura }}</span>
                                            <span><i class="fas fa-user me-2 text-success"></i><b>Usuario:</b> {{ boveda.usuario_apertura }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5 d-flex align-items-center justify-content-lg-end">
                                <div class="w-100" style="max-width: 400px;">
                                    <button v-if="boveda.id_boveda === 0" class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-3" @click="aperturarBoveda()">
                                        <i class="fas fa-key me-2"></i> Aperturar Bóveda
                                    </button>
                                    
                                    <div v-else class="row g-2">
                                        <div class="col-6">
                                            <button class="btn btn-success btn-lg w-100 shadow-sm h-100 py-3" @click="ingresoBoveda()">
                                                <i class="fas fa-plus-circle d-block mb-1 fs-4"></i>
                                                <span class="small fw-bold">Añadir</span>
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-danger btn-lg w-100 shadow-sm h-100 py-3" @click="retiroBoveda()">
                                                <i class="fas fa-minus-circle d-block mb-1 fs-4"></i>
                                                <span class="small fw-bold">Retirar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mb-4 shadow-sm">
                            <div class="card-body p-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="fw-bold extra-small mb-1 text-muted text-uppercase">Tipo Movimiento</label>
                                        <div class="input-group input-group-sm">
                                            <!-- <span class="input-group-text bg-white border-end-0"><i class="fas fa-filter text-muted"></i></span> -->
                                            <select @change="buscarMovimientoBoveda()" v-model="filtroTipoMovimiento" class="form-select border-2">
                                                <option value="todos">Todos</option>
                                                <option value="ingreso">Ingresos</option>
                                                <option value="salida">Salidas</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold extra-small mb-1 text-muted text-uppercase">Fecha Inicio</label>
                                        <input @input="buscarMovimientoBoveda()" type="date" v-model="fechaInicio" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold extra-small mb-1 text-muted text-uppercase">Fecha Fin</label>
                                        <input @input="buscarMovimientoBoveda()" type="date" v-model="fechaFin" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="bg-white border rounded p-2 text-center h-100 shadow-xs">
                                                    <span class="d-block extra-small fw-bold text-success text-uppercase mb-1">Total Ingresos</span>
                                                    <span class="h6 fw-bold mb-0 text-dark">{{ formatNumero(totalIngresosBoveda) }} <small class="text-muted">Bs.</small></span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-white border rounded p-2 text-center h-100 shadow-xs">
                                                    <span class="d-block extra-small fw-bold text-danger text-uppercase mb-1">Total Salidas</span>
                                                    <span class="h6 fw-bold mb-0 text-dark">{{ formatNumero(totalSalidasBoveda) }} <small class="text-muted">Bs.</small></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive rounded shadow-sm border">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="table-success text-dark text-uppercase fw-bold small">
                                    <tr>
                                        <th class="text-center py-3" style="width: 60px;">#</th>
                                        <th class="py-3" style="width: 140px;">Movimiento</th>
                                        <th class="text-end pe-4 py-3">Monto (Bs)</th>
                                        <th class="py-3">Descripción / Detalle</th>
                                        <th class="py-3">Usuario Responsable</th>
                                        <th class="text-center py-3">Fecha / Hora</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr v-for="(movimiento, index) in movimientosBoveda" :key="index">
                                        <td class="text-center fw-bold text-muted">{{ index + 1 }}</td>
                                        <td>
                                            <span :class="movimiento.tipo_movimiento === 'ingreso' ? 'badge bg-success py-2 px-3 w-100 shadow-xs' : 'badge bg-danger py-2 px-3 w-100 shadow-xs'">
                                                <i :class="movimiento.tipo_movimiento === 'ingreso' ? 'fas fa-arrow-up me-1' : 'fas fa-arrow-down me-1'"></i>
                                                {{ movimiento.tipo_movimiento.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-end pe-4 font-monospace fs-6 text-dark">{{ formatNumero(movimiento.monto) }}</td>
                                        <td class="text-uppercase">
                                            <div class="fw-semibold">{{ movimiento.descripcion }}</div>
                                            <div v-if="movimiento.socio_nombres" class="mt-1">
                                                <span class="badge bg-light text-primary border border-primary-subtle py-1 px-2">
                                                    <i class="fas fa-handshake me-1"></i> SOCIO: {{ movimiento.socio_nombres }} {{ movimiento.socio_apellidos }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-uppercase">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs bg-soft-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                                    <i class="fas fa-user-circle text-muted"></i>
                                                </div>
                                                {{ movimiento.personal }}
                                            </div>
                                        </td>
                                        <td class="text-center text-muted">{{ formatFecha(movimiento.fecha) }}</td>
                                    </tr>
                                    <tr v-if="movimientosBoveda.length === 0">
                                        <td colspan="6" class="text-center py-5 text-muted bg-white">
                                            <div class="py-4">
                                                <i class="fas fa-search-dollar fa-3x mb-3 opacity-25"></i>
                                                <h5 class="fw-normal">No se encontraron movimientos</h5>
                                                <p class="mb-0 small text-muted">Ajuste los filtros para ver otros resultados.</p>
                                            </div>
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
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-2 border-dark">
                    <div class="modal-header bg-success py-2">
                        <h5 class="modal-title text-white fw-bold text-uppercase"><i class="fas fa-plus-circle me-2"></i> Ingresar a Bóveda</h5>
                        <button @click="cerrarModalIngresoBoveda()" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="validarIngreso">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1">Monto a Ingresar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white fw-bold">Bs.</span>
                                    <input type="number" class="form-control fw-bold text-dark" v-model="montoIngreso" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-group mb-3 position-relative">
                                <label class="fw-bold mb-1 text-muted">Concepto / Motivo</label>
                                
                                <div class="input-group shadow-sm rounded">
                                    <!-- <span class="input-group-text bg-white border-success text-success">
                                        <i class="fas fa-list-ul"></i>
                                    </span> -->
                                    
                                    <input type="text" class="form-control text-uppercase fw-bold border-success" 
                                        v-model="busquedaIngreso" 
                                        placeholder="Buscar o seleccionar motivo..."
                                        @input="filtrarIngresos" 
                                        @focus="abrirListaIngreso"
                                        @blur="cerrarListaIngreso" 
                                        required autocomplete="off">
                                    
                                    <button type="button" class="btn bg-white border border-success text-success" 
                                            @mousedown.prevent="toggleListaIngreso">
                                        <i class="fas" :class="mostrarListaIngreso ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </button>
                                </div>

                                <ul v-if="mostrarListaIngreso" class="dropdown-menu show w-100 shadow-lg border-0 mt-1" 
                                    style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1050;">
                                    
                                    <li v-if="resultadosIngreso.length === 0" class="dropdown-item text-muted fst-italic text-center py-2">
                                        <i class="fas fa-search me-1"></i> No se encontraron coincidencias...
                                    </li>
                                    
                                    <li v-for="(opcion, idx) in resultadosIngreso" :key="idx">
                                        <a class="dropdown-item text-uppercase py-2 fw-semibold custom-dropdown-item hover-success" 
                                           href="#" @mousedown.prevent="seleccionarIngreso(opcion)">
                                            {{ opcion }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="descripcionIngresoSeleccionada === 'Aporte de capital'" class="form-group mb-3 fade-in-animation">
                                <label class="fw-bold mb-1 text-primary">Seleccionar Inversionista (Socio)</label>
                                <select class="form-select border-primary" v-model="id_socio_ingreso" required>
                                    <option value="" disabled selected>Seleccione al socio aportante...</option>
                                    <option v-for="socio in lista_socios" :key="socio.id" :value="socio.id">
                                        {{ socio.nombre_completo }} (CI: {{ socio.ci }})
                                    </option>
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
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-2 border-dark">
                    <div class="modal-header bg-danger py-2">
                        <h5 class="modal-title text-white fw-bold text-uppercase"><i class="fas fa-minus-circle me-2"></i> Retirar de Bóveda</h5>
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
                                    <input type="number" class="form-control fw-bold text-danger" v-model="montoRetiro" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-group mb-3 position-relative">
                                <label class="fw-bold mb-1 text-muted">Concepto / Motivo</label>
                                
                                <div class="input-group shadow-sm rounded">
                                    <!-- <span class="input-group-text bg-white border-danger text-danger">
                                        <i class="fas fa-list-ul"></i>
                                    </span> -->
                                    
                                    <input type="text" class="form-control text-uppercase fw-bold border-danger" 
                                        v-model="busquedaRetiro" 
                                        placeholder="Buscar o seleccionar motivo..."
                                        @input="filtrarRetiros" 
                                        @focus="abrirListaRetiro"
                                        @blur="cerrarListaRetiro" 
                                        required autocomplete="off">
                                    
                                    <button type="button" class="btn bg-white border border-danger text-danger" 
                                            @mousedown.prevent="toggleListaRetiro">
                                        <i class="fas" :class="mostrarListaRetiro ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </button>
                                </div>

                                <ul v-if="mostrarListaRetiro" class="dropdown-menu show w-100 shadow-lg border-0 mt-1" 
                                    style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1050;">
                                    
                                    <li v-if="resultadosRetiro.length === 0" class="dropdown-item text-muted fst-italic text-center py-2">
                                        <i class="fas fa-search me-1"></i> No se encontraron coincidencias...
                                    </li>
                                    
                                    <li v-for="(opcion, idx) in resultadosRetiro" :key="idx">
                                        <a class="dropdown-item text-uppercase py-2 fw-semibold custom-dropdown-item hover-danger" 
                                           href="#" @mousedown.prevent="seleccionarRetiro(opcion)">
                                            {{ opcion }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="descripcionRetiroSeleccionada === 'Pago de dividendos'" class="form-group mb-3 fade-in-animation">
                                <label class="fw-bold mb-1 text-danger">Seleccionar Beneficiario (Socio)</label>
                                <select class="form-select border-danger" v-model="id_socio_retiro" required>
                                    <option value="" disabled selected>Seleccione al socio que recibe...</option>
                                    <option v-for="socio in lista_socios" :key="socio.id" :value="socio.id">
                                        {{ socio.nombre_completo }} (CI: {{ socio.ci }})
                                    </option>
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
                busquedaIngreso: '',
                mostrarListaIngreso: false,
                resultadosIngreso: [],

                busquedaRetiro: '',
                mostrarListaRetiro: false,
                resultadosRetiro: [],

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
                ],
                opcionesRetiro: [
                ],

                esIngreso:false,
                motivos:[],

                vista:0,
                boveda:{
                    id_boveda:0,
                    saldo_actual:0,
                    fecha_apertura:moment().format('YYYY-MM-DD'),
                    usuario_apertura: '',
                },

                movimientosBoveda:[],

                pagination_movimientos_boveda: {
                    total: 0,
                    current_page: 1,
                    per_page: 40,
                    last_page: 0,
                    from: 0,
                    to: 0
                },
                offset_movimientos_boveda: 2,

                lista_socios: [],
                id_socio_ingreso: '',
                id_socio_retiro: '',
            }
        },
     
        methods: {
            async cargarMotivos(ingresoOEgreso) {
                this.esIngreso = ingresoOEgreso;
                const url = this.esIngreso ? '/get_motivos_ingresos_activos?tipo=boveda' : '/get_motivos_gastos_activos?tipo=boveda';
                try {
                    const res = await axios.get(url);
                    const nombresMotivos = res.data.map(item => item.nombre);
                    
                    if (this.esIngreso) {
                        this.opcionesIngreso = nombresMotivos;
                        if (!this.opcionesIngreso.includes('otro')) this.opcionesIngreso.push('otro');
                    } else {
                        this.opcionesRetiro = nombresMotivos;
                        if (!this.opcionesRetiro.includes('otro')) this.opcionesRetiro.push('otro');
                    }
                } catch (e) {
                    console.error("Error al cargar motivos:", e);
                }
            },
            async abrirListaIngreso() {
                await this.cargarMotivos(true);
                this.filtrarIngresos(); 
                this.mostrarListaIngreso = true;
            },
            toggleListaIngreso() {
                if (this.mostrarListaIngreso) this.mostrarListaIngreso = false;
                else this.abrirListaIngreso();
            },
            cerrarListaIngreso() {
                setTimeout(() => { this.mostrarListaIngreso = false; }, 150);
            },
            filtrarIngresos() {
                this.descripcionIngresoSeleccionada = this.busquedaIngreso; 

                if (!this.busquedaIngreso) {
                    this.resultadosIngreso = this.opcionesIngreso;
                } else {
                    const term = this.busquedaIngreso.toLowerCase();
                    this.resultadosIngreso = this.opcionesIngreso.filter(op => op.toLowerCase().includes(term));
                }
                this.mostrarListaIngreso = true;
            },
            seleccionarIngreso(opcion) {
                this.busquedaIngreso = opcion;
                this.descripcionIngresoSeleccionada = opcion;
                this.mostrarListaIngreso = false;
            },

            async abrirListaRetiro() {
                await this.cargarMotivos(false);
                this.filtrarRetiros(); 
                this.mostrarListaRetiro = true;
            },
            toggleListaRetiro() {
                if (this.mostrarListaRetiro) this.mostrarListaRetiro = false;
                else this.abrirListaRetiro();
            },
            cerrarListaRetiro() {
                setTimeout(() => { this.mostrarListaRetiro = false; }, 150);
            },
            filtrarRetiros() {
                this.descripcionRetiroSeleccionada = this.busquedaRetiro; 

                if (!this.busquedaRetiro) {
                    this.resultadosRetiro = this.opcionesRetiro;
                } else {
                    const term = this.busquedaRetiro.toLowerCase();
                    this.resultadosRetiro = this.opcionesRetiro.filter(op => op.toLowerCase().includes(term));
                }
                this.mostrarListaRetiro = true;
            },
            seleccionarRetiro(opcion) {
                this.busquedaRetiro = opcion;
                this.descripcionRetiroSeleccionada = opcion;
                this.mostrarListaRetiro = false;
            },

            async getSocios() {
                try {
                    const response = await axios.get('/socio/activos'); 
                    this.lista_socios = response.data;
                } catch (error) { console.error('Error al traer socios:', error); }
            },
            async aperturarBoveda(){
                const confirmar = await Swal.fire({
                    title: '¿Aperturar Bóveda?',
                    text: 'Esta acción crea el fondo central de la institución. Solo debe realizarse una vez.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, aperturar',
                    cancelButtonText: 'Cancelar',
                });
                if (!confirmar.isConfirmed) return;

                try {
                    await this.aperturarBovedaPrivate();
                    await this.getBoveda();
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Se aperturó la Bóveda correctamente',
                        confirmButtonText: 'Aceptar',
                        icon: 'success',
                        timer: 1500,
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
            ingresoBoveda(){
                this.montoIngreso = '';
                this.descripcionIngresoSeleccionada = '';
                this.otraDescripcionIngreso = '';
                this.id_socio_ingreso = '';
                this.busquedaIngreso = '';
                this.abrirModalIngresoBoveda();
            },

            retiroBoveda(){
                if(this.boveda.id_boveda==0){
                    Swal.fire({ title:'Atención', text:'Debe Aperturar Bóveda primero', icon:'warning'});
                    return;
                }
                this.montoRetiro = '';
                this.descripcionRetiroSeleccionada = '';
                this.otraDescripcionRetiro = '';
                this.id_socio_retiro = '';
                this.busquedaRetiro = '';
                this.abrirModalRetiroBoveda();
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
                if (!this.montoIngreso || parseFloat(this.montoIngreso) <= 0) {
                    Swal.fire('Advertencia', 'Ingrese un monto válido mayor a cero.', 'warning');
                    return;
                }
                if (!this.descripcionIngresoSeleccionada) {
                    Swal.fire('Advertencia', 'Seleccione o escriba el concepto del ingreso.', 'warning');
                    return;
                }
                // BUG-07: comparación case-insensitive
                if (this.descripcionIngresoSeleccionada.toLowerCase() === 'aporte de capital' && !this.id_socio_ingreso) {
                    Swal.fire('Advertencia', 'Debe seleccionar el Socio/Inversionista que hace el aporte.', 'warning');
                    return;
                }
                if (this.descripcionIngresoSeleccionada.toLowerCase() === 'otro' && !this.otraDescripcionIngreso.trim()) {
                    Swal.fire('Advertencia', 'Debe especificar el detalle del ingreso.', 'warning');
                    return;
                }

                let descripcionFinal = this.descripcionIngresoSeleccionada.toLowerCase() === 'otro'
                    ? `otro: ${this.otraDescripcionIngreso}`
                    : this.descripcionIngresoSeleccionada;

                this.ingresarABoveda(descripcionFinal);
            },

            validarRetiro() {
                if (!this.montoRetiro || !this.descripcionRetiroSeleccionada) {
                    Swal.fire('Advertencia', 'Por favor complete todos los campos.', 'warning');
                    return;
                }
                // BUG-06: usar parseFloat() para evitar comparación de strings
                if ((parseFloat(this.boveda.saldo_actual) - parseFloat(this.montoRetiro)) < 0) {
                    Swal.fire('Error', 'No tiene saldo suficiente para el retiro.', 'error');
                    return;
                }
                // BUG-07: comparación case-insensitive
                if (this.descripcionRetiroSeleccionada.toLowerCase() === 'pago de dividendos' && !this.id_socio_retiro) {
                    Swal.fire('Advertencia', 'Debe seleccionar al Socio que recibe los dividendos.', 'warning');
                    return;
                }
                if (this.descripcionRetiroSeleccionada.toLowerCase() === 'otro' && !this.otraDescripcionRetiro.trim()) {
                    Swal.fire('Advertencia', 'Debe especificar el detalle del retiro.', 'warning');
                    return;
                }

                let descripcionFinal = this.descripcionRetiroSeleccionada.toLowerCase() === 'otro'
                    ? `otro: ${this.otraDescripcionRetiro}`
                    : this.descripcionRetiroSeleccionada;

                this.retirarDeBoveda(descripcionFinal);
            },

            async ingresarABoveda(descripcion) {
                try {
                    await axios.post('/ingresar_boveda', {
                        monto: this.montoIngreso,
                        descripcion: descripcion,
                        id_socio: this.descripcionIngresoSeleccionada.toLowerCase() === 'aporte de capital' ? this.id_socio_ingreso : null
                    });

                    Swal.fire({title:'Éxito', text: 'Ingreso a bóveda registrado con éxito.', icon:'success', timer:1500});
                    this.montoIngreso = ''; 
                    this.descripcionIngresoSeleccionada = '';
                    await this.getMovimientosBoveda(); 
                    await this.getBoveda();
                    this.cerrarModalIngresoBoveda();
                    this.id_socio_ingreso = '';
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
                        id_socio: this.descripcionRetiroSeleccionada.toLowerCase() === 'pago de dividendos' ? this.id_socio_retiro : null
                    });

                    Swal.fire({title:'Éxito', text:'Retiro de bóveda registrado con éxito.', icon:'success', timer:1500});
                    this.montoRetiro = ''; 
                    this.descripcionRetiroSeleccionada = '';
                    this.cerrarModalRetiroBoveda();
                    await this.getMovimientosBoveda(); 
                    await this.getBoveda();
                    this.id_socio_retiro = '';
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
                        this.boveda.usuario_apertura = response.data.usuario_apertura;
                    })
                    .catch((error) => {
                        console.error('Error al obtener la información de la bóveda:', error);
                    });
            },

            formatNumero(value) {
                const n = parseFloat(value || 0);
                return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },

            formatFecha(fecha) {
                return fecha ? moment(fecha).format('DD/MM/YYYY HH:mm') : '---';
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
            await this.getSocios();
            this.preloader=false;
        }
    }
</script>

<style scoped>
.bg-light-success {
    background-color: #f0fdf4;
}
.bg-light-secondary {
    background-color: #f8f9fa;
}
.bg-soft-primary {
    background-color: rgba(59, 130, 246, 0.1);
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.extra-small {
    font-size: 0.65rem;
}
.vault-management .card {
    transition: transform 0.2s ease-in-out;
}
.vault-management .btn-lg {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.custom-dropdown-item {
    transition: all 0.2s ease-in-out;
    border-bottom: 1px solid #f8f9fa;
}
.custom-dropdown-item:last-child {
    border-bottom: none;
}
.hover-success:hover {
    background-color: #e8f5e9 !important;
    color: #198754 !important;
}
.hover-danger:hover {
    background-color: #fce4e4 !important;
    color: #dc3545 !important;
}
.dropdown-menu::-webkit-scrollbar {
    width: 6px;
}
.dropdown-menu::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 4px;
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