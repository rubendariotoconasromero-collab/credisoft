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
                                    Reporte Desembolsos Pendientes</h4>
                               
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
                                                <!-- <div class="form-group mt-2 mb-3">
                                                    <label for="id_usuario">Asesor: </label>
                                                    <select @change="buscarPlanPago()" v-model="id_asesor" class="form-control form-select">
                                                        <option value="0" selected hidden disabled>Seleccione un asesor</option>
                                                        <option v-for="(item, index) in lista_asesores" :key="index" :value="item.id">
                                                            {{ item.personal }}
                                                        </option>
                                                    </select>
                                                </div> -->
                                                <div class="input-group my-1">
                                                    <input type="date" name="" id="" class="form-control" v-model="fecha_inicio">
                                                    <button class="btn-outline-success btn mx-2">
                                                        <i class="fas fa-arrow-right"></i>
                                                        Desde
                                                    </button>
                                                    
                                                    <button class="btn-outline-danger btn mx-2">
                                                        <i class="fas fa-arrow-left"></i>
                                                        Hasta
                                                    </button>
                                                    <input type="date" name="" id="" class="form-control" v-model="fecha_fin">

                                                </div>
                                            </div>

                                            <div class="col-md-12 my-3">
                                                <button class="btn btn-outline-warning w-100" @click="generarReporteDesembolsosPendientes()">
                                                    <i class="fas fa-print"></i>
                                                    Generar Reporte
                                                </button>
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

            
        </div>
        
      
    </main>
</template>

<script>
    import moment from 'moment';
    import Swal from 'sweetalert2'


    export default {
        data() {
            return {
                id_asesor:0,
                preloader:false,
                vista:0,


                fecha_fin:moment().format('YYYY-MM-DD'),
                fecha_inicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
                lista_asesores:[],
  

            }
        },
     
        methods: {
            async getAsesores(){
                await axios.get('/get_asesores')
                .then((response)=>{
                    console.log(response);
                    this.lista_asesores=response.data;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },
            generarReporteDesembolsosPendientes(){
                // if(this.id_asesor==0 || this.id_asesor==null){
                //     Swal.fire({
                //         position: 'center',
                //         icon: 'warning',
                //         title: 'Seleccione un asesor',
                //         // text:'No hay una caja aperturada',
                //         showConfirmButton: true,
                //         // timer: 1500
                //     });
                //     return ;
                // }
                // Construye la URL con el parámetro fecha_inicio
                const url = '/reporte_desembolsos_pendientes?fecha_final='+this.fecha_fin
                +'&fecha_inicial='+this.fecha_inicio+'&id_asesor='+this.id_asesor;

                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
          

        },
        async mounted() {
            this.preloader = true;
            console.log('Component mounted.');
            await this.getAsesores();
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
