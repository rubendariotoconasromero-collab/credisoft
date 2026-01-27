<template>
    <main>
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <div class="page-title">
                                <h4 class="mb-0 font-size-18">Estado de Resultados</h4>
                                <ol class="breadcrumb">
                                    <!-- <li class="breadcrumb-item active">Welcome to Agroxa Dashboard</li> -->
                                </ol>
                            </div>

                            <!-- <div class="state-information d-none d-sm-block">
                                <div class="state-graph">
                                    <div id="header-chart-1" data-colors='["--bs-primary"]'></div>
                                    <div class="info">Balance $ 2,317</div>
                                </div>
                                <div class="state-graph">
                                    <div id="header-chart-2" data-colors='["--bs-danger"]'></div>
                                    <div class="info">Item Sold 1230</div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="page-content-wrapper">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <h5 class="text-uppercase">
                                            Actualizado a la fecha actual: {{ fecha_actual }}
                                        </h5>
                                        <h5>Expresado en Bolivianos</h5>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-md-6">
                                            <div class="card border border-2 border-primary">
                                                <div class="card-header bg-primary ">
                                                    <h5 class="text-white">INGRESOS</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 text-end">
                                                            <p>Gastos Administrativos: </p>
                                                        </div>
                                                        <div class="col-md-6 text-start">
                                                            <p>{{ isNaN(parseFloat(suma_pagos_administrativos).toFixed(2))?0: parseFloat(suma_pagos_administrativos).toFixed(2)}}</p>
                                                        </div>
                
                                                        <div class="col-md-6 text-end">
                                                            <p>Intereses Pagados: </p>
                                                        </div>
                                                        <div class="col-md-6 text-start">
                                                            <p>{{ isNaN(parseFloat(suma_intereses).toFixed(2))?0: parseFloat(suma_intereses).toFixed(2)}}</p>
                                                        </div>
                
                                                        <div class="col-md-6 text-end">
                                                            <p>Multas - Moras: </p>
                                                        </div>
                                                        <div class="col-md-6 text-start">
                                                            <p>{{ isNaN(parseFloat(suma_multas).toFixed(2))?0:parseFloat(suma_multas).toFixed(2) }}</p>
                                                        </div>
                
                                                        <div class="col-md-6 text-end">
                                                            <p>Otros ingresos: </p>
                                                        </div>
                                                        <div class="col-md-6 text-start">
                                                            <p>{{ isNaN(parseFloat(otros_ingresos).toFixed(2))?0: parseFloat(otros_ingresos).toFixed(2)}}</p>
                                                        </div>
                
                                                        <div class="col-md-6 text-end">
                                                            <h5>TOTAL INGRESOS</h5>
                                                        </div>
                                                        <div class="col-md-6 text-start">
                                                            <p>{{ total_ingresos = (parseFloat(suma_pagos_administrativos==null?0:suma_pagos_administrativos) + parseFloat(suma_intereses==null?0:suma_intereses) + parseFloat(suma_multas==null?0:suma_multas) + parseFloat(otros_ingresos==null?0:otros_ingresos)).toFixed(2)}}</p>
                                                        </div>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="card border border-2 border-danger">
                                                        <div class="card-header bg-danger ">
                                                            <h5 class="text-white">EGRESOS</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6 text-end">
                                                                    <p>Gastos generales: </p>
                                                                </div>
                                                                <div class="col-md-6 text-start">
                                                                    <p>{{ parseFloat(suma_total_egresos==null?0:suma_total_egresos).toFixed(2) }}</p>
                                                                </div>
                                                                <div class="col-md-6 text-end">
                                                                    <h5>TOTAL EGRESOS</h5>
                                                                </div>
                                                                <div class="col-md-6 text-start">
                                                                    <p>{{ parseFloat(suma_total_egresos==null?0:suma_total_egresos).toFixed(2) }}</p>
                                                                </div>
                                                                
                                                            </div> 
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="card border border-2 border-success">
                                                        <div class="card-header bg-success ">
                                                            <h5 class="text-white">UTILIDADES</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6 text-end">
                                                                    <p>Ingresos - Egresos: </p>
                                                                </div>
                                                                <div class="col-md-6 text-start">
                                                                    <p>{{ parseFloat(total_ingresos==null?0:total_ingresos).toFixed(2) +' - '+ parseFloat(suma_total_egresos==null?0:suma_total_egresos).toFixed(2) }}</p>
                                                                </div>
                                                                <div class="col-md-6 text-end">
                                                                    <h5>TOTAL UTILIDAD</h5>
                                                                </div>
                                                                <div class="col-md-6 text-start">
                                                                    <p>{{ total_utilidad = (parseFloat(total_ingresos==null?0:total_ingresos).toFixed(2) - parseFloat(suma_total_egresos==null?0:suma_total_egresos).toFixed(2)).toFixed(2) }}</p>
                                                                </div>
                                                                
                                                            </div> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table class="table mb-4 table-bordered table-striped table-hover">
                                                <thead class="bg-info text-white">
                                                    <tr>
                                                        <th scope="col">Nombre</th>
                                                        <th scope="col">Aporte</th>
                                                        <th scope="col">Participacion %</th>
                                                        <th scope="col">Distribucion</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(item, index) in lista_resultados" :key="index">
                                                        <td><input v-model="item.nombre" type="text" class="form-control" placeholder="Nombre"></td>
                                                        <td><input v-model="item.aporte" type="number" class="form-control" placeholder="Aporte"></td>
                                                        <td><input :disabled="true" v-model="item.participacion" :value="item.participacion=parseFloat(((isNaN(item.aporte)?0:item.aporte)/(total_aporte==null || isNaN(total_aporte)?0:total_aporte))*100).toFixed(3)" type="number" class="form-control"></td>
                                                        <td><input :disabled="true" v-model="item.distribucion" :value="item.distribucion=(parseFloat(total_utilidad==null?0:total_utilidad)*parseFloat((item.participacion==null || isNaN(item.participacion)?0:item.participacion)/100)).toFixed(3)" type="number" class="form-control" placeholder="Distribucion"></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a v-if="index==lista_resultados.length-1" @click="addItem()" class="btn btn-success btn-sm me-1">
                                                                    <i class="fas fa-plus"></i>
                                                                </a>
                                                                <a v-else @click="deleteItem(index)" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Puedes agregar más filas según sea necesario -->
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>Total:</th>
                                                        <th><input :value="total_aporte= this.lista_resultados.reduce((total, elemento) => total + elemento.aporte, 0)" type="number" class="form-control" readonly></th>
                                                        <th><input :value="total_participacion= (this.lista_resultados.reduce((total, elemento) => parseFloat(total) + parseFloat(elemento.participacion==null?0:elemento.participacion), 0)).toFixed(2)" type="number" class="form-control" readonly></th>
                                                        <th><input :value="total_distribucion= (this.lista_resultados.reduce((total, elemento) => parseFloat(total) + parseFloat(elemento.distribucion), 0)).toFixed(2)" type="text" class="form-control" readonly></th>
                                                        
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>


                                    

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                


                <!-- end page-content-wrapper-->

            </div>
            <!-- Container-fluid -->
        </div>
    </main>

    <!-- End Page-content -->
</template>

<script>
    import moment from 'moment';
    import 'moment/locale/es';

    export default {
  
        data(){
            return{
                fecha_actual:moment().format('DD-MM-YYYY'),
                suma_pagos_administrativos:0,
                suma_intereses:0,
                suma_multas:0,
                otros_ingresos:0,
                total_ingresos:0,
                suma_total_egresos:0,
                total_utilidad:0,

                lista_resultados:[
                    {
                        nombre:'',
                        aporte:0,
                        participacion:0,
                        distribucion:0,
                    }
                ],

                total_aporte:0,
                total_participacion:0,
                total_distribucion:0,
             
              
            }
        },
        watch: {
            
        },
        methods:{
            deleteItem(indice){
                this.lista_resultados.splice(indice, 1);
            },
            addItem(){
                this.lista_resultados.push(
                    {
                        nombre:'',
                        aporte:0,
                        participacion:0,
                        distribucion:0,
                    }
                );
            },
            getMontoTotalEgresos(){
                axios.get('/get_monto_total_egresos')
                .then((response)=>{
                    console.log(response);
                    this.suma_total_egresos=response.data.suma_total_egresos;
                })

                .catch((error)=>{
                    console.log(error.message);
                })
            },
            getPagosAdministrativos(){
                axios.get('get_pagos_administrativos')
                .then((response)=>{
                    console.log(response);
                    this.suma_pagos_administrativos=response.data.suma_pagos_administrativos;
                })

                .catch((error)=>{
                    console.log(error.message);
                })
            },
            getMontoInteresesPagados(){
                axios.get('/get_monto_intereses')
                .then((response)=>{
                    console.log(response);
                    this.suma_intereses=response.data.sum_total_interes;
                })

                .catch((error)=>{
                    console.log(error.message);
                })
            },

            getMontoMultas(){
                axios.get('/get_monto_multas')
                .then((response)=>{
                    console.log(response);
                    this.suma_multas=response.data.suma_multas;
                })

                .catch((error)=>{
                    console.log(error.message);
                })
            },
            getMontoOtrosIngresos(){
                axios.get('/get_monto_otros_ingresos')
                .then((response)=>{
                    console.log(response);
                    this.otros_ingresos=response.data.otros_ingresos;
                })

                .catch((error)=>{
                    console.log(error.message);
                })
            },
        },
        mounted() {
            console.log('Component mounted.')
            this.getPagosAdministrativos();
            this.getMontoInteresesPagados();
            this.getMontoMultas();
            this.getMontoOtrosIngresos();
            this.getMontoTotalEgresos();
           
        }
    }

</script>
