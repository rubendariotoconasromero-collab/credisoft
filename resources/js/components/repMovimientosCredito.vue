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
                                    Reporte Extrato Movimientos</h4>
                               
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
                                        <div class="row mb-3">
                                            <div class="col-md-12 my-2">

                                                <div class="form-group">
                                                    <label class="fw-bold" for="">Seleccione cliente....</label>
                                                    <div class="input-group">
                                                        <section class="dropdown-wrapper form-control p-2 bg-light position-relative"
                                                            style="border-radius: 0.375rem; border: 1px solid #ced4da;">
                                                            <div @click="isVisibleCliente = !isVisibleCliente"
                                                                class="selected-item p-2 d-flex justify-content-between align-items-center border border-secondary rounded"
                                                                style="cursor: pointer; transition: background-color 0.3s;">
                                                                <!-- Texto que muestra si se ha seleccionado un cliente o no -->
                                                                <span v-if="cliente.buscar === ''" class="text-muted">Seleccione un cliente</span>
                                                                <span v-else>{{ cliente.buscar }}</span>

                                                                <!-- Icono del dropdown -->
                                                                <svg :class="isVisibleCliente ? 'dropdown' : ''" class="drop-down-icon"
                                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                                                    <path fill="none" d="M0 0h24v24H0z" />
                                                                    <path d="M12 10.828l-4.95 4.95-1.414-1.414L12 8l6.364 6.364-1.414 1.414z" />
                                                                </svg>
                                                            </div>

                                                            <!-- Dropdown para búsqueda -->
                                                            <div :class="isVisibleCliente ? 'visible' : 'invisible'"
                                                                class="dropdown-popover shadow-lg"
                                                                style="position: absolute; top: 100%; left: 0; z-index: 9999; width: 100%; max-height: 250px; overflow-y: auto; background-color: white; border: 1px solid #ced4da; border-radius: 0.375rem; transition: all 0.3s ease-in-out;">
                                                                <input type="text" class="form-control form-control-sm mb-2 border-0 px-2"
                                                                    placeholder="Buscar por CI..." v-model="cliente.ci"
                                                                    aria-label="Buscar cliente..."
                                                                    style="font-size:14px; border-bottom: 1px solid #ced4da;">

                                                                <!-- Mensaje cuando no se encuentra ningún cliente -->
                                                                <div v-if="filteredItemsCliente.length === 0" class="text-center text-muted p-2">
                                                                    <small>No existe el cliente</small>
                                                                </div>

                                                                <!-- Lista de clientes filtrados -->
                                                                <ul class="list-group list-group-flush">
                                                                    <li v-for="(cliente, index) in filteredItemsCliente" :key="index"
                                                                        @click="seleccionarCliente(cliente)"
                                                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                                        style="cursor: pointer; transition: background-color 0.2s;">
                                                                        <span class="fw-bold">{{ cliente.nombre }}</span>
                                                                        <span class="fw-bold">{{ cliente.ci }}</span>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </section>

                                                        <button class="btn btn-success btn-rounded mx-1">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                        <button @click="limpiar()" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>



                                            </div>
                                        </div>
                                        <h6 class="fw-bold">Detalle de creditos: </h6>
                                        <h6 class="text-secondary" v-if="items_creditos.length==0">
                                            Aún no tiene creditos.
                                        </h6>
                                        <template v-if="id_cliente!=0 && items_creditos.length!=0">
        
                                                <!-- <div class="row">
                                                    <div class="col-md-4">
                                                        <h5 class="my-1">
                                                            <span style="border-radius:0" class="badge bg-danger fw-bold text-uppercase">Total clientes:  </span>
                                                            <span style="border-radius:0" class="badge text-danger fw-bold">Clientes </span>
                                                            
                                                        </h5>
                                                    </div>
                                            
                                                </div> -->
                                    
                                                
                                                <div class="table-responsive mt-3" style="font-size:14px">
                                                    <table class="table mb-4 table-hover table-bordered table-striped table-sm">
                                                        <thead class="bg-primary text-white text-uppercase">
                                                            <tr style="background-color:#52BE80">
                                                                <th>Opciones</th>
                                                                <th>Credito</th>
                                                                <th>Total a Pagar</th>
                                                                <th>Fecha de Inicio</th>
                                                                <th>Fecha de Fin</th>
                                                                <th>Número de Cuotas</th>
                                                                <th>Lapso de Capital</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="item in items_creditos" :key="item.id" style="vertical-align: middle">
                                                                <td>
                                                                    <!-- Aquí puedes añadir las opciones para cada crédito, como editar o eliminar -->
                                                                    <button @click="generarPdfMovimientos(item.id)" class="btn btn-outline-success btn-sm btn-rounded">
                                                                        <i class="fas fa-print"></i>
                                                                        Generar extracto</button>
                                                                    </td>
                                                                <td>{{ item.id }}</td>
                                                                <td>{{ item.total_pagar }}</td>
                                                                <td>{{ item.fecha_inicio }}</td>
                                                                <td>{{ item.fecha_fin }}</td>
                                                                <td>{{ item.nro_cuotas }}</td>
                                                                <td>{{ item.lapso_capital }}</td>
                                                                <td>
                                                                    <span v-if="item.estado == 1" class="badge bg-success">Activo</span>
                                                                    <span v-else class="badge bg-danger">Inactivo</span>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                </div>
                                                <!-- Card Pagination -->
                                        </template>
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

            
        </div>
        
      
    </main>
</template>

<script>
    import moment from 'moment';
    import Swal from 'sweetalert2'


    export default {
        data() {
            return {


                cliente: {
                    id: 0,
                    nombre: '',
                    fecha_nacimiento: '',
                    ci: '',
                    lugar_expedicion: '',
                    buscar: '',
                },
                id_cliente:0,
                isVisibleCliente: false,
                items_cliente: [],  // Lista de clientes obtenida desde la base de datos
                items_creditos:[],
              
                preloader:false,
                vista:0,
  

            }
        },
        computed:{
            filteredItemsCliente() {
                const searchTermLower = this.cliente.ci.toLowerCase();  // Utilizamos 'ci' como el campo de búsqueda, pero puedes modificarlo
                return this.items_cliente.filter(item => 
                    item.ci.toLowerCase().includes(searchTermLower) || 
                    item.nombre.toLowerCase().includes(searchTermLower)
                );
            }
        },
        methods: {
            generarPdfMovimientos(id_plan_pago){
                // Construye la URL con el parámetro fecha_inicio
                const url = '/reporte_extracto_movimientos?id_plan_pago='+id_plan_pago;

                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            limpiar(){
                this.limpiarInputCliente();
                this.id_cliente=0;
                this.items_creditos=[];
            },

            limpiarInputCliente(){

                this.cliente= {
                    id: 0,
                    nombre: '',
                    fecha_nacimiento: '',
                    ci: '',
                    lugar_expedicion: '',
                    buscar: '',
                };
                
            },
            async seleccionarCliente(cliente) {
                this.preloader=true;
                this.cliente = {
                    ...this.cliente,
                    buscar: cliente.nombre + ' - ' + cliente.ci,
                    id: cliente.id,
                    fecha_nacimiento: cliente.fecha_nacimiento,
                    lugar_expedicion: cliente.lugar_expedicion
                };
                this.isVisibleCliente = false;  // Ocultar el dropdown después de la selección
                this.id_cliente=cliente.id;
                await this.getCreditos();
                this.preloader=false;

            },
   
            async getClientes(){
                await axios.get('/get_clientes_rep').then((response)=>{
                    this.items_cliente=response.data;
                    console.log(response.data);
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },

            async getCreditos(){
                await axios.get('/get_creditos_rep?id_cliente='+this.id_cliente).then((response)=>{
                    this.items_creditos=response.data;
                    console.log(response.data);
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },

        },
        async mounted() {
            this.preloader = true;
            console.log('Component mounted.');
            await this.getClientes();
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
</style>
