<template>
    <main>
        <div class="page-content ps-0 px-0">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <div class="page-title">
                                <h4 class="mb-0 font-size-18 text-uppercase">Gestión de cuotas  - pagos</h4>
                                
                                <ol class="breadcrumb">
                                    <!-- <li class="breadcrumb-item active">Welcome to Agroxa Dashboard</li> -->
                                </ol>
                            </div>

                            <!-- <div class="state-information d-none d-sm-block">
                                <button  class="btn btn-primary">Nuevo
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div> -->


                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="page-content-wrapper">
                    
                    <div class="row">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                      
                                        <div class="row">
                                            <!-- <div class="col-md-4 text-start">
                                                <h6 class="">Listado de pagos</h6>
                                            </div> -->
                                            <div class="col-md-12 text-center">
                                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                                    <input @click="cambiarVista(0)" v-model="vista" type="radio" class="btn-check " name="btnradio" id="btnradio1"
                                                        autocomplete="off" value="0">
                                                    <label class="btn btn-outline-success btn-sm" for="btnradio1">CUOTAS POR PAGAR</label>
                            
                                                    <input @click="cambiarVista(1)" v-model="vista" type="radio" class="btn-check " name="btnradio" id="btnradio2"
                                                        autocomplete="off" value="1">
                                                    <label class="btn btn-outline-success btn-sm" for="btnradio2">CUOTAS PAGADAS</label>

                                                    <input @click="cambiarVista(2)" v-model="vista" type="radio" class="btn-check " name="btnradio" id="btnradio3"
                                                        autocomplete="off" value="2">
                                                    <label class="btn btn-outline-success btn-sm" for="btnradio3">CUOTAS - PAGOS ANULADOS</label>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <!-- SECCION PARA BUSQUEDA -->
                                        <!-- <div class="row mb-2 mt-3">
                                            <div class="col-md-6 text-start">
                                                <div class="input-group">
                                                    <select v-model="opcion_vista_cuotas" class="form-control form-control-sm" @change="inputVistaCuotasFechaSelect()">
                                                        
                                                        <option value="cliente.nombre">Nombre Cliente</option>
                                                        <option value="cliente.ci">CI cliente</option>
                                                        <option v-if="(vista_cuotas==1 && vista==0) || vista>0" value="fecha">Fecha</option>
                                                    </select>
                                                    <input v-if="opcion_vista_cuotas!='fecha'" v-model="buscar_vista_cuotas" @input="inputVistaCuotas()" type="text" class="form-control form-control-sm">
                                                    <input v-if="opcion_vista_cuotas=='fecha'" v-model="fecha_inicial" @input="inputVistaCuotasFecha()" type="date" class="form-control form-control-sm">
                                                    <input v-if="opcion_vista_cuotas=='fecha'" v-model="fecha_final" @input="inputVistaCuotasFecha()" type="date" class="form-control form-control-sm">
                                                    <button class="btn btn-primary btn-sm">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div> -->
                                            <!-- <div v-if="vista==0" class="col-md-6 text-end">
                                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                                    <input v-model="vista_cuotas" @click="cambiarVistaCuotas(0)" type="radio" class="btn-check" name="btnradio1" id="btnradio4"
                                                        autocomplete="off" value="0">
                                                    <label class="btn btn-outline-success btn-sm" for="btnradio4">EN MORA - PROXIMAS A PAGAR</label>
                            
                                                    <input v-model="vista_cuotas" @click="cambiarVistaCuotas(1)" type="radio" class="btn-check" name="btnradio1" id="btnradio5"
                                                        autocomplete="off" value="1">
                                                    <label class="btn btn-outline-success btn-sm" for="btnradio5">TODAS LAS CUOTAS POR PAGAR</label>
                                                </div>
                                            </div> -->
                                        <!-- </div> -->

                                        <!-- FIN SECCION PARA BUSQUEDA -->
                                        <template v-if="vista==0">
                                            <h6 class="text-center text-uppercase my-3">Búsqueda cuotas por pagar</h6>
                                            <div class="row mb-3">
                                                 
                                                 <div class="col-md-3" >
                                                     <label for="fecha_inicial" class="text-dark">Fecha Inicial</label>
                                                     <input v-model="fecha_inicial_cuotas_por_pagar" @input="buscandoCuotasPorPagar()" type="date" class="form-control form-control-sm">
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="fecha_final" class="text-dark">Fecha Final</label>
                                                     <input v-model="fecha_final_cuotas_por_pagar" @input="buscandoCuotasPorPagar()" type="date" class="form-control form-control-sm">
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="opcion_vista_cuotas" class="text-dark">Criterio de Búsqueda</label>
                                                     <select v-model="opcion_vista_cuotas_por_pagar" class="form-control form-control-sm form-select" @change="buscandoCuotasPorPagar()">
                                                         <option value="cliente.nombre">Nombre Cliente</option>
                                                         <option value="cliente.ci">CI cliente</option>
                                                         <option value="cuota.id_plan_pago">Cod. Plan pago</option>
                                                     </select>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="buscar_vista_cuotas" class="text-dark">Buscar</label>
                                                     <div class="input-group">
                                                         <input v-model="buscar_vista_cuotas_por_pagar" @input="buscandoCuotasPorPagar()" type="text" class="form-control form-control-sm">
                                                         <button class="btn btn-success btn-sm">
                                                             <i class="fas fa-search"></i>
                                                         </button>
                                                     </div>
                                                 </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <div class="form-check form-check-inline">
                                                        <input @change="buscandoCuotasPorPagar()" class="form-check-input" type="radio" name="estadoCuota" id="todos" value="todos" v-model="estadoCuota">
                                                        <label class="form-check-label" for="todos">Todos</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input @change="buscandoCuotasPorPagar()" class="form-check-input" type="radio" name="estadoCuota" id="conMora" value="con_mora" v-model="estadoCuota">
                                                        <label class="form-check-label" for="conMora">Con Mora</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input @change="buscandoCuotasPorPagar()" class="form-check-input" type="radio" name="estadoCuota" id="sinMora" value="sin_mora" v-model="estadoCuota">
                                                        <label class="form-check-label" for="sinMora">Sin Mora</label>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="row my-2">
                                                <div class="col-md-4">
                                                    <h5>
                                                        <span style="border-radius:0" class="badge bg-success">Total Cuotas x pagar Bs.:  </span>

                                                        <span style="border-radius:0" class="badge text-success">{{ totalCuotas }} </span>
                                                    </h5>
                                                </div>

                                                <div class="col-md-4">
                                                    <h5>
                                                        <span style="border-radius:0" class="badge bg-danger">Total cuotas en mora Bs.:  </span>

                                                        <span style="border-radius:0" class="badge text-danger">{{ totalCuotasMora }} </span>
                                                    </h5>
                                                </div>

                                                <div class="col-md-4">
                                                    <h5>
                                                        <span style="border-radius:0" class="badge bg-info">Total cuotas sin mora Bs.:  </span>

                                                        <span style="border-radius:0" class="badge text-info">{{ totalCuotasSinMora }}  </span>
                                                    </h5>
                                                </div>
                                            </div>
                                        </template>







                                        <div v-if="vista==0" class="row">
                                           

                                            <div v-if="vista_cuotas==0" class="col-md-12">
                                                <div class="table-responsive text-uppercase" style="font-size:12px">
                                                    <table class="table mb-4 table-hover table-sm">
                                                        <thead class="bg-primary text-white text-uppercase">
                                                            <!-- <tr style="background-color:#52BE80" >
                                                 
                                                                <th>Credito</th>
                                                                <th>Cliente</th>
                                                                <th>Asesor</th>
                                                                <th>Monto D.</th>
                                                                <th>Cuota</th>
                                                                <th>Saldo cap.</th>
                                                                <th>Interes</th>
                                                                <th>Fecha pago</th>
                                                                <th>Cuota deuda</th>
                                                                <th>Estado</th>
                                                                <th>Opciones</th>
                                                            </tr> -->
                                                            <tr style="background-color:#52BE80">
                                                                <!-- <th>#</th> -->
                                                                <!-- <th>Codigo</th> -->
                                                                <th>Crédito</th>
                                                                <th>Cliente</th>
                                                                <th>Asesor</th>
                                                                <th>Monto</th>
                                                                <th>Cuota</th>
                                                                <th>Saldo Cap.</th>
                                                                <th>Interés</th>
                                                                <th>Fecha Pago</th>
                                                                <th>Deuda Cuota</th>
                                                                <th>Estado</th>
                                                                <th>Opciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="(item, index) in lista_cuotas" :key="index" style="vertical-align: middle">
        
                                                                <!-- <td>{{ index+1 }}</td> -->
                                                                <td>{{ item.id_plan_aux!=0? item.id_plan_aux:item.plan_pago }}</td>
                                                                <!-- <td>{{ item.id }}</td> -->
                                                                <td>{{ item.cliente }}</td>
                                                                <td>{{ item.nombre_asesor }}</td>
                                                                <td>{{ item.total_pago_credito }}</td>
                                                                <td>{{ item.cuota +' / '+ item.nro_cuotas}}</td>
                                                                <td>{{ item.saldo_capital}}</td>
                                                                <td>{{ item.interes}}</td>
                                                                <td>{{ item.fecha_a_pagar }}</td>
                                                                <td>{{ item.monto_a_pagar }}</td>
                                                           
                                                            
                                                                <!-- <td class="text-left">
                                                                    <span v-if="item.estado==1" class="text-warning d-block fw-bold">Por pagar</span>
                                                                    <span v-if="item.dias_pasados>0" class="text-danger fw-bold">{{ item.dias_pasados }} - retraso</span>
                                                                 
                                                                </td> -->
                                                                <!-- <td class="text-left">
                                                                    <span v-if="item.estado==1" class="text-warning d-block fw-bold">Pendiente</span>
                                                                    <span v-if="item.dias_pasados > 0" class="fw-bold">
                                                                        <span>Pendiente /</span> 
                                                                        <span class="text-danger">{{ item.dias_pasados }}</span> <span>Mora</span>
                                                                    </span>
                                                                </td> -->
                                                                <td class="text-left text-danger" style="font-size:10px;">
                                                                    <span class="fw-bold" v-if="item.dias_pasados > 0">
                                                                        Pend.
                                                                        <span v-if="item.dias_pasados > 0"> / <span class="text-danger">{{ item.dias_pasados }}</span> Mora</span>
                                                                    </span>
                                                                    <span v-else class="fw-bold">
                                                                        Pendiente
                                                                        
                                                                    </span>
                                                                </td>

                                                                <td class="text-center">
                                                                    <div class="btn-group my-0 py-0 text-end">
                                                                        <a style="cursor:pointer;"
                                                                            class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                                            data-bs-toggle="dropdown" aria-expanded="false"
                                                                            >
                                                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                                                        </a>
                                                                        <ul class="dropdown-menu">
                                                                            <li>
                                                                                <a @click="abrirModalPagarCuota(item)" class="dropdown-item text-success" href="#">
                                                                                    <i class="fas fa-money-bill-wave"></i> Cobrar</a>
                                                                            </li>
                                                                            
                                                                            <!-- <li v-if="item.estado_plan==1">
                                                                                <a class="dropdown-item text-danger" href="#">
                                                                                    <i class="fas fa-times"></i> Anular</a>
                                                                            </li>
                                                                            <li  v-else-if="item.estado_plan==0"><a
                                                                                    class="dropdown-item text-success" href="#">
                                                                                    <i class="fas fa-check"></i> Activar</a>
                                                                            </li>
                                                                            <li @click="abrirModalVerCuotas(item)"><a
                                                                                    class="dropdown-item text-success" href="#">
                                                                                    <i class="fas fa-eye"></i> Ver cuotas</a>
                                                                            </li> -->
                                                            
                
                                                                        </ul>
                                                                    </div>
                                                                </td>
        
                                                            </tr> 
                                                        </tbody> 
                                                    </table>
                                                    <br>
                                                    <br>
                                                    <br>
                                                    
                                                </div>
                                            </div>

                                            <div v-if="vista_cuotas==1" class="col-md-12">
                                                <div class="table-responsive text-uppercase" style="font-size:12px;">
                                                    <table class="table mb-4 table-hover table-striped table-sm">
                                                        <thead class="bg-primary text-white text-uppercase">
                                                            <tr style="background-color:rgb(0, 146, 37)" >
                                                                <!-- <th>#</th> -->
                                                                <!-- <th>Codigo</th> -->
                                                                <th>Credito</th>
                                                                <th>Cliente</th>
                                                                <th>Asesor</th>
                                                                <th>Monto Des.</th>
                                                                <th>Cuotas</th>
                                                                <th>Saldo Cap.</th>
                                                                <th>Interes</th>
                                                                <th>Fecha a pagar</th>
                                                                <th>Cuota deuda</th>
                                                                <th>Estado</th>
                                                                <!-- <th>Opciones</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="(item, index) in lista_cuotas_total" :key="index">
        
                                                                <!-- <td>{{ index+1 }}</td> -->
                                                                <td>{{ item.plan_pago }}</td>
                                                                <!-- <td>{{ item.id }}</td> -->
                                                                <td>{{ item.cliente }}</td>
                                                                <td>{{ item.nombre_asesor }}</td>
                                                                <td>{{ item.total_pago_credito }}</td>
                                                                <td>{{ item.cuota +' / '+ item.nro_cuotas}}</td>
                                                                <td>{{ item.saldo_capital }}</td>
                                                                <td>{{ item.interes }}</td>
                                                                <td>{{ item.fecha_a_pagar }}</td>
                                                                <td>{{ item.monto_a_pagar }}</td>
                                                           
                                                            
                                                                <td class="text-center">
                                                                    <span v-if="item.estado==1" class="badge text-bg-warning d-block">Por pagar</span>
                                                                    <span v-if="item.dias_pasados>0" class="badge text-bg-danger">{{ item.dias_pasados }} - retraso</span>
                                                                 
                                                                </td>
                                                              
        
                                                            </tr> 
                                                        </tbody> 
                                                    </table>
                                                    <br><br><br>
                                                </div>
                                            </div>
                                            <!-- Card Pagination -->
                                            <div v-if="vista_cuotas==0" class="card-footer py-4">
                                                <nav>
                                                    <ul class="pagination justify-content-end mb-0">
                                                        <li class="page-item" v-if="pagination_lista_cuotas.current_page > 1">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaCuotas(pagination_lista_cuotas.current_page - 1)">Ant</a>
                                                        </li>
                                                        <li class="page-item" v-for="page in pagesNumber_lista_cuotas" :key="page"
                                                            :class="[page==isActived_lista_cuotas ? 'active' :'']">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaCuotas(page)"
                                                                :v-text="page">{{ page }}</a>
                                                        </li>
                                                        <li class="page-item"
                                                            v-if="pagination_lista_cuotas.current_page < pagination_lista_cuotas.last_page">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaCuotas(pagination_lista_cuotas.current_page + 1)">Sig</a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                            </div>

                                            <div v-if="vista_cuotas==1" class="card-footer py-4">
                                                <nav>
                                                    <ul class="pagination justify-content-end mb-0">
                                                        <li class="page-item" v-if="pagination_lista_cuotas_total.current_page > 1">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaCuotasTotal(pagination_lista_cuotas_total.current_page - 1)">Ant</a>
                                                        </li>
                                                        <li class="page-item" v-for="page in pagesNumber_lista_cuotas_total" :key="page"
                                                            :class="[page==isActived_lista_cuotas_total ? 'active' :'']">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaCuotasTotal(page)"
                                                                :v-text="page">{{ page }}</a>
                                                        </li>
                                                        <li class="page-item"
                                                            v-if="pagination_lista_cuotas_total.current_page < pagination_lista_cuotas_total.last_page">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaCuotasTotal(pagination_lista_cuotas_total.current_page + 1)">Sig</a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                            </div>
                                        </div>
                                        

                                        <template v-if="vista==1">
                                            <h6 class="text-center text-uppercase my-3">Búsqueda cuotas pagadas</h6>
                                            <div class="row mb-3">
                                                 
                                                 <div class="col-md-3" >
                                                     <label for="fecha_inicial" class="text-dark">Fecha Inicial</label>
                                                     <input v-model="fecha_inicial_cuotas_pagadas" @input="buscandoCuotasPagadas()" type="date" class="form-control form-control-sm">
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="fecha_final" class="text-dark">Fecha Final</label>
                                                     <input v-model="fecha_final_cuotas_pagadas" @input="buscandoCuotasPagadas()" type="date" class="form-control form-control-sm">
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="opcion_vista_cuotas" class="text-dark">Criterio de Búsqueda</label>
                                                     <select v-model="opcion_vista_cuotas_pagadas" class="form-select form-control form-control-sm" @change="buscandoCuotasPagadas()">
                                                         <option value="cliente.nombre">Nombre Cliente</option>
                                                         <option value="cliente.ci">CI cliente</option>
                                                         <option value="plan_pago.id">Cod. Plan pago</option>
                                                     </select>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="buscar_vista_cuotas" class="text-dark">Buscar</label>
                                                     <div class="input-group">
                                                         <input v-model="buscar_vista_cuotas_pagadas" @input="buscandoCuotasPagadas()" type="text" class="form-control form-control-sm">
                                                         <button class="btn btn-success btn-sm">
                                                             <i class="fas fa-search"></i>
                                                         </button>
                                                     </div>
                                                 </div>
                                            </div>

                                            

                                            <div class="row my-2">
                                                <div class="col-md-4">
                                                    <h5>
                                                        <span style="border-radius:0" class="badge bg-success">Total Cuotas pagadas Bs.:  </span>

                                                        <span style="border-radius:0" class="badge text-success">{{ totalPagos }} </span>
                                                    </h5>
                                                </div>

                                                
                                            </div>


                                        </template>


                                        <div v-if="vista==1" class="row">

                                            <div class="col-md-12">
                                                <div class="table-responsive text-uppercase" style="font-size:12px">
                                                    <table class="table mb-4 table-striped table-hover table-sm">
                                                        <thead class="bg-primary text-white text-uppercase">
                                                            <!-- <tr style="background-color:#52BE80" >
                                                      
                                                                <th>Pago</th>
                                                                <th>Cliente</th>
                                                                <th>Asesor</th>
                                                                <th>Credito</th>
                                                                <th>Monto des.</th>
                                                                <th>Cuotas</th>
                                                                <th>Saldo cap.</th>
                                                                <th>Interes</th>
                                                                <th>Fecha pagado</th>
                                                                <th>Forma pago</th>
                                                                <th>Monto pagado</th>
                                                                <th>Estado</th>
                                                                <th>Op.</th>
                                                            </tr> -->
                                                            <tr style="background-color:#52BE80">
                                                                <!-- <th>#</th> -->
                                                                <th>Pago</th>
                                                                <th>Cliente</th>
                                                                <th>Asesor</th>
                                                                <th>Crédito</th>
                                                                <th>Monto Des.</th>
                                                                <th>Cuotas</th>
                                                                <th>Saldo Cap.</th>
                                                                <th>Interés</th>
                                                                <th>F. Pago</th>
                                                                <th>Forma</th>
                                                                <th>Monto Pag.</th>
                                                                <th>Estado</th>
                                                                <th>Opc.</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="" v-for="(item, index) in lista_pagos" :key="index">
        
                                                                <!-- <td>{{ index+1 }}</td> -->
                                                                <td>{{ item.id }}</td>
                                                                <td>{{ item.cliente }}</td>
                                                                <td>{{item.nombre_asesor}}</td>
                                                                <td>{{ item.plan_pago }}</td>
                                                                <td>{{ item.total_pago_credito }}</td>
                                                                <td>{{ item.cuota +' / '+ item.nro_cuotas}}</td>
                                                                <td>{{ item.saldo_capital }}</td>
                                                                <td>{{ item.interes }}</td>
                                                                <td>{{ item.fecha_pago }}</td>
                                                                <td>{{ item.forma_pago }}</td>
                                                                <td>{{ item.monto_pago }}</td>
                                                           
                                                            
                                                                <td>
                                                                    <span v-if="item.estado == 1" class="badge bg-success">Cancelado</span>
                                                                    <span v-else-if="item.estado == 0" class="badge bg-danger">Anulado</span>
                                                                </td>

                                                                <td>
                                                                    <div class="btn-group">
                                                                        <a  style="cursor:pointer;"
                                                                            
                                                                            class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                                                            
                                                                        </a>
                                                                        <ul class="dropdown-menu">
                                                                            <li v-if="item.estado==1 && esFechaActual(item.fecha_pago)">
                                                                                <a @click="abrirModalAnularPago(item)" class="dropdown-item text-danger" href="#">
                                                                                    <i class="fas fa-times"></i> Anular</a>
                                                                            </li>
                                                                            <li v-if="item.estado==1">
                                                                                <a @click="generarTicket(item)" class="dropdown-item text-danger" href="#">
                                                                                    <i class="fas fa-file-pdf"></i> Generar Ticket</a>
                                                                            </li>
                                                                            <li v-if="item.forma_pago=='transferencia - QR'">
                                                                                <a @click="ingresarRespaldo(item)" class="dropdown-item text-success" href="#">
                                                                                    <i class="fas fa-camera"></i> Ingresar respaldo</a>
                                                                            </li>
                                                                            
                                                                            <!-- <li v-if="item.estado_plan==1">
                                                                                <a class="dropdown-item text-danger" href="#">
                                                                                    <i class="fas fa-times"></i> Anular</a>
                                                                            </li>
                                                                            <li  v-else-if="item.estado_plan==0"><a
                                                                                    class="dropdown-item text-success" href="#">
                                                                                    <i class="fas fa-check"></i> Activar</a>
                                                                            </li>
                                                                            <li @click="abrirModalVerCuotas(item)"><a
                                                                                    class="dropdown-item text-success" href="#">
                                                                                    <i class="fas fa-eye"></i> Ver cuotas</a>
                                                                            </li> -->
                                                            
                
                                                                        </ul>
                                                                    </div>
                                                                </td>
        
                                                            </tr> 
                                                        </tbody> 
                                                    </table><br><br><br>
                                                </div>
                                            </div>
                                            <!-- Card Pagination -->
                                            <div class="card-footer py-4">
                                                <nav>
                                                    <ul class="pagination justify-content-end mb-0">
                                                        <li class="page-item" v-if="pagination.current_page > 1">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                                        </li>
                                                        <li class="page-item" v-for="page in pagesNumber" :key="page"
                                                            :class="[page==isActived ? 'active' :'']">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPagina(page)"
                                                                :v-text="page">{{ page }}</a>
                                                        </li>
                                                        <li class="page-item"
                                                            v-if="pagination.current_page < pagination.last_page">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                            </div>
                                        </div>


                                        <template v-if="vista==2">
                                            <h6 class="text-center text-uppercase my-3">Búsqueda cuotas anuladas</h6>
                                            <div class="row mb-3">
                                                 
                                                 <div class="col-md-3" >
                                                     <label for="fecha_inicial" class="text-dark">Fecha Inicial</label>
                                                     <input v-model="fecha_inicial_cuotas_anuladas" @input="buscandoCuotasAnuladas()" type="date" class="form-control form-control-sm">
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="fecha_final" class="text-dark">Fecha Final</label>
                                                     <input v-model="fecha_final_cuotas_anuladas" @input="buscandoCuotasAnuladas()" type="date" class="form-control form-control-sm">
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="opcion_vista_cuotas" class="text-dark">Criterio de Búsqueda</label>
                                                     <select v-model="opcion_vista_cuotas_anuladas" class="form-select form-control-sm form-control" @change="buscandoCuotasAnuladas()">
                                                         <option value="cliente.nombre">Nombre Cliente</option>
                                                         <option value="cliente.ci">CI cliente</option>
                                                         <option value="plan_pago.id">Cod. Plan pago</option>

                                                     </select>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label for="buscar_vista_cuotas" class="text-dark">Buscar</label>
                                                     <div class="input-group">
                                                         <input v-model="buscar_vista_cuotas_anuladas" @input="buscandoCuotasAnuladas()" type="text" class="form-control form-control-sm">
                                                         <button class="btn btn-success btn-sm">
                                                             <i class="fas fa-search"></i>
                                                         </button>
                                                     </div>
                                                 </div>
                                            </div>

                                            <div class="row my-2">
                                                <div class="col-md-4">
                                                    <h5>
                                                        <span style="border-radius:0" class="badge bg-success">Total Pagos anulados Bs.:  </span>

                                                        <span style="border-radius:0" class="badge text-success">{{ parseFloat(totalPagosAnulados).toFixed(2) }} </span>
                                                    </h5>
                                                </div>
                                            </div>
                                        </template>

                                        <div v-if="vista==2" class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive text-uppercase" style="font-size:12px;" >
                                                    <table class="table mb-4 table-sm table-striped table-hover">
                                                        <thead class="bg-primary text-white text-uppercase">
                                                            <tr style="background-color:#52BE80" >
                                                                <!-- <th>#</th> -->
                                                                <th>Codigo</th>
                                                                <th>Cliente</th>
                                                                <th>Asesor</th>
                                                                <th>Plan Pago</th>
                                                                <th>Cuota</th>
                                                                <th>Fecha pago</th>
                                                                <th>Monto pago</th>
                                                                <th>Estado</th>
                                                              
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="" v-for="(item, index) in lista_pagos_anulados" :key="index">
        
                                                                <!-- <td>{{ index+1 }}</td> -->
                                                                <td>{{ item.id }}</td>
                                                                <td>{{ item.cliente }}</td>
                                                                <td>{{ item.asesor }}</td>
                                                                <td>{{ item.plan_pago }}</td>
                                                                <td>{{ item.cuota +' / '+ item.nro_cuotas}}</td>
                                                                <td>{{ item.fecha_pago }}</td>
                                                                <td>{{ item.monto_pago }}</td>
                                                           
                                                            
                                                                <td>
                                                                    <span v-if="item.estado == 1" class="badge bg-success">Cancelado</span>
                                                                    <span v-else-if="item.estado == 0" class="badge bg-danger">Anulado</span>
                                                                </td>

                                                            </tr> 
                                                        </tbody> 
                                                    </table><br><br><br>
                                                </div>
                                            </div>
                                            <!-- Card Pagination -->
                                            <div class="card-footer py-4">
                                                <nav>
                                                    <ul class="pagination justify-content-end mb-0">
                                                        <li class="page-item" v-if="pagination_lista_anulados.current_page > 1">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaAnulados(pagination_lista_anulados.current_page - 1)">Ant</a>
                                                        </li>
                                                        <li class="page-item" v-for="page in pagesNumber_lista_anulados" :key="page"
                                                            :class="[page==isActived_lista_anulados ? 'active' :'']">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaAnulados(page)"
                                                                :v-text="page">{{ page }}</a>
                                                        </li>
                                                        <li class="page-item"
                                                            v-if="pagination_lista_anulados.current_page < pagination_lista_anulados.last_page">
                                                            <a class="page-link" href="#"
                                                                @click.prevent="cambiarPaginaListaAnulados(pagination_lista_anulados.current_page + 1)">Sig</a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <!-- End Cardbody -->
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


        <!--<div id="modalAnularPago" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="container border border-3 border-danger">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mySmallModalLabel">Información pago</h5>
                            <button @click="cerrarModalAnularPago()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <label class="col-md-6">
                                    <strong>
                                        Cliente:
                                    </strong>
                                </label>
                                <p class="col-md-6">
                                    {{pago.cliente}}
                                </p>  
                            </div>
                            <div class="row">
                                <label class="col-md-6">
                                    <strong>
                                        Fecha pago:
                                    </strong>
                                </label>
                                <p class="col-md-6">
                                    {{pago.fecha_pago}}
                                </p>  
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-6">
                                    <strong>
                                        Forma pago:
                                    </strong>
                                </label>
                                <p class="col-md-6">
                                    {{pago.forma_pago}}
                                </p> 
                            </div>
                            <div class="row">
                                <label class="col-md-6">
                                    <strong>
                                        Monto pago:
                                    </strong>
                                </label>
                                <p class="col-md-6">
                                    {{pago.monto_pago}}
                                </p>  
                            </div>
                            <div class="row">
                                <label class="col-md-6">
                                    <strong>
                                        Cobrado por:
                                    </strong>
                                </label>
                                <p class="col-md-6">
                                    {{pago.usuario}}
                                </p>  
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="cerrarModalAnularPago()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button @click="anularPago()" type="button" class="btn btn-danger">Anular</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>-->

        <div id="modalAnularPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content border-2 border-success border shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white" id="mySmallModalLabel">Información del Pago</h5>
                        <button @click="cerrarModalAnularPago()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="font-size:13px;">
                        <div class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Cliente:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.cliente}}</p>
                                </div>  
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Fecha de pago:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.fecha_pago}}</p>
                                </div>  
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="row pb-2" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Forma de pago:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.forma_pago}}</p> 
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Monto pago:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.monto_pago}}</p>
                                </div>  
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Cobrado por:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.usuario}}</p>
                                </div>  
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button @click="cerrarModalAnularPago()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button @click="anularPago()" type="button" class="btn btn-success">Anular pago</button>
                    </div>
                </div>
            </div>
        </div>




        
        <div id="modalPagarCuota" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content border-2 border-success border shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white" id="mySmallModalLabel">Pago | Cliente: {{ pago.cliente }}</h5>
                        <button @click="cerrarModalPagarCuota()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="font-size:13px;">
                        <!-- <div class="mb-2 ">
                            <div class="row" style=" border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Cliente:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.cliente}}</p>
                                </div>  
                            </div>
                        </div> -->
                        <!-- <div class="mb-2">
                            <div class="row" style=" border-bottom: 1px solid #DCDEDD">
                                <label class="col-6 fw-bold text-muted">Fecha de pago:</label>
                                <div class="col-6">
                                    <p class="mb-0">{{pago.fecha_pago}}</p>
                                </div>  
                            </div>
                        </div> -->
                        
                        <div class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Capital:</label>
                                    
                                <div class="col-6">
                                    <p class="mb-0">{{ pago.capital }}</p>
                                </div>  
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="row" style=" border-bottom: 1px solid #DCDEDD">
                                <label class="col-6 fw-bold text-muted">Interés de cuota:</label>
                                <div :class="parseFloat(pago.interes) - (isNaN(parseFloat(pago.monto_condonado_interes)) ? 0 : parseFloat(pago.monto_condonado_interes)) < 0 ? 'col-6 text-decoration-line-through text-danger' : 'col-6'">
                                    <p class="mb-0">{{ parseFloat(pago.interes) - (isNaN(parseFloat(pago.monto_condonado_interes)) ? 0 : parseFloat(pago.monto_condonado_interes)) }}</p>
                                </div>  
                            </div>
                        </div>

                        <div v-if="pago.seguro>0" class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Seguro:</label>
                                    
                                <div class="col-6">
                                    <p class="mb-0">{{ pago.seguro }}</p>
                                </div>  
                            </div>
                        </div>

                        <div v-if="pago.ahorro>0" class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-muted">Ahorro:</label>
                                    
                                <div class="col-6">
                                    <p class="mb-0">{{ pago.ahorro }}</p>
                                </div>  
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                <label class="col-6 fw-bold text-dark">Monto Cuota:</label>
                                    
                                <div class="col-6">
                                    <h5 class="mb-0 text-dark">{{ pago.monto_a_pagar }}</h5>
                                </div>  
                            </div>
                        </div>

                        <div v-if="pago.dias_pasados > 0">
                            <div class="mb-2">
                                <div class="row" style=" border-bottom: 1px solid #DCDEDD">
                                    <label class="col-6 fw-bold text-muted">Días retrasados:</label>
                                    <div class="col-6">
                                        <p class="mb-0">{{pago.dias_pasados}} <span class="ms-2 badge bg-danger text-white">En mora</span></p>
                                    </div>  
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="row pb-2" style=" border-bottom: 1px solid #DCDEDD">
                                    <label class="col-6 fw-bold text-muted">Multa por día:</label>
                                    <div class="col-6">
                                        <input v-model="pago.multa_mora" type="text" class="form-control form-control-sm py-0" :disabled="true">
                                    </div>  
                                </div>
                            </div>
                            <!-- <div class="mb-2">
                                <div class="row" style=" border-bottom: 1px solid #DCDEDD">
                                    <label class="col-6 fw-bold text-muted">Total multa:</label>
                                    <div :class="(isNaN((parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2)) ? 0 : (parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2)) < 0 ? 'col-6 text-decoration-line-through text-danger' : 'col-6'">
                                        <p class="mb-0">{{ isNaN((parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2)) ? 0 : (parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2) }}</p>
                                    </div>  
                                </div>
                            </div> -->
                            <!-- <div class="mb-2">
                                <div class="row" style=" border-bottom: 1px solid #DCDEDD">
                                    <label class="col-6 fw-bold text-muted">Total multa:</label>
                                    <div :class="(isNaN((parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2)) ? 0 : (parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2)) < 0 ? 'col-6 text-decoration-line-through text-danger' : 'col-6'">
                                        <p class="mb-0">{{ isNaN((parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2)) ? 0 : (parseFloat(pago.dias_pasados * pago.multa_mora) - parseFloat((pago.monto_condonado == '') ? 0 : pago.monto_condonado)).toFixed(2) }}</p>
                                    </div>  
                                </div>
                            </div> -->
                            <div class="mb-2">
                                <div class="row" style="border-bottom: 1px solid #DCDEDD;">
                                    <label class="col-6 fw-bold text-muted">Total multa:</label>
                                    
                                    <div :class="computedClass">
                                        <p class="mb-0">{{ computedMulta }}</p>
                                    </div>  
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="row pb-2" style=" border-bottom: 1px solid #DCDEDD">
                                    <label class="col-6 fw-bold text-muted">Monto multa-condonación:</label>
                                    <div class="col-6">
                                        <input v-model="pago.monto_condonado" type="text" class="form-control form-control-sm py-0">
                                    </div>  
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="row pb-2" style=" border-bottom: 1px solid #DCDEDD">
                                    <label class="col-6 fw-bold text-muted">Motivo condonación:</label>
                                    <div class="col-6">
                                        <input v-model="pago.motivo_condonacion" type="text" class="form-control form-control-sm py-0">
                                    </div>  
                                </div>
                            </div>

                            
                        </div>

                        <div class="mb-2">
                            <div class="row pb-2" style=" border-bottom: 1px solid #DCDEDD">
                                <label class="col-6 fw-bold text-muted">Forma de pago:</label>
                                <div class="col-6">
                                    <select v-model="pago.forma_pago" class="form-select form-select-sm form-control form-control-sm">
                                        <option v-for="(item, index) in formas_pago" :key="index" :value="item.nombre">{{ item.nombre }}</option>
                                    </select>  
                                </div>
                            </div>
                        </div>


                        <div class="mb-2">
                            <div class="row" style=" border-bottom: 1px solid #DCDEDD">
                                <h4 class="col-6 fw-bold text-dark">TOTAL A COBRAR:</h4>
                                <div class="col-6">
                                    <h4 class="mb-0 text-dark">{{parseFloat(pago.monto_a_pagar ) + parseFloat(computedMulta)}} Bs.</h4>
                                </div>  
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button @click="cerrarModalPagarCuota()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button @click="pagarCuota()" type="button" class="btn btn-success">Cobrar</button>
                    </div>
                </div>
            </div>
        </div>


        <div id="modalRespaldo" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mySmallModalLabel">Imagen del respaldo</h5>
                        <button @click="cerrarModalRespaldo()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 p-3">
                                <div class="image-container">
                                    <img :src="pago.imagen!=''?'/img/pago/'+pago.imagen:'/img/pago/default.png'" alt="" >
                                </div>
                                <input class="form-control" type="file" name="" id="" @change="seleccionarImagen($event)">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="container text-end">
                            <button @click="cerrarModalRespaldo()" class="btn btn-secondary mx-2">
                                Cerrar
                            </button>
                            <!-- <button class="btn btn-primary">
                                Guardar
                            </button> -->
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
     

        <div class="position-fixed top-0 end-0 toast" style="z-index: 1050" ref="miToast" role="alert"
            aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="2000">
            <div class="toast-header bg-danger" style="border:none">
                <strong class="me-auto text-white">{{ mensajeError }}</strong>
                <button type="button" class="btn btn-danger text-white" @click="cerrarToastError()" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
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
                formas_pago:[
                    {
                        nombre:'efectivo'
                    },
                    {
                        nombre:'transferencia - QR'
                    },
                    {
                        nombre:'Depósito banco'
                    }
                ],

                totalPagosAnulados:0,
                opcion_vista_cuotas_anuladas:'cliente.nombre',
                buscar_vista_cuotas_anuladas:'',
                fecha_inicial_cuotas_anuladas:moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_final_cuotas_anuladas:moment().format('YYYY-MM-DD'),

                opcion_vista_cuotas_pagadas:'cliente.nombre',
                buscar_vista_cuotas_pagadas:'',
                fecha_inicial_cuotas_pagadas:moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_final_cuotas_pagadas:moment().format('YYYY-MM-DD'),

                totalCuotasMora:0,
                totalCuotasSinMora:0,
                totalCuotas:0,
                estadoCuota:'todos',

                opcion_vista_cuotas_por_pagar:'cliente.nombre',
                buscar_vista_cuotas_por_pagar:'',
                fecha_inicial_cuotas_por_pagar:moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_final_cuotas_por_pagar:moment().add(1, 'months').format('YYYY-MM-DD'),

                fecha_inicial:moment().format('YYYY-MM-DD'),
                fecha_final:moment().format('YYYY-MM-DD'),
                totalPagos:0,
                vista:0,
                lista_pagos:[],
                lista_cuotas:[],
                lista_pagos_anulados:[],
                lista_cuotas_total:[],
   
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                pagination_lista_cuotas : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },

                pagination_lista_cuotas_total : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },

                pagination_lista_anulados : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
               
                pago:{
                    id_pago:0,
                    fecha_pago:moment().format('YYYY-MM-DD'),
                    monto_pago:0,
                    id_cuota:0,
                    id_usuario:0,
                    multa_mora:0,
                    monto_condonado:0,
                    motivo_condonacion:'',
                    forma_pago:'efectivo',
                    imagen:'',
                    interes:0,
                    monto_condonado_interes:0,
                },

                

                offset : 2,
                offset_lista_cuotas : 2,
                offset_lista_cuotas_total : 2,
                offset_lista_anulados : 2,
                mensajeError: '',

                vista_cuotas:0,
                buscar_vista_cuotas:'',

                opcion_vista_cuotas:'cliente.nombre',
                estado_caja:false,

            }
        },
        watch:{
           
        },
        computed:{
            computedMulta:function() {
                // Calcula la multa
                let multa_mora=this.pago.multa_mora==null?0:parseFloat(this.pago.multa_mora);
                let multaTotal = parseFloat(parseFloat(this.pago.dias_pasados) * multa_mora);
                console.log('multatotal', multaTotal);
                let montoCondonado = (this.pago.monto_condonado=='' || this.pago.monto_condonado==null)? 0:parseFloat(this.pago.monto_condonado);
                console.log('montocondonado', montoCondonado);

                let totalMulta = (multaTotal - montoCondonado).toFixed(2);

                // Devuelve el valor calculado, manejando casos NaN
                return isNaN(totalMulta) ? 0 : totalMulta;
            },
            computedClass:function() {
                // Determina la clase CSS en función del valor calculado
                return this.computedMulta < 0 
                    ? 'col-6 text-decoration-line-through text-danger' 
                    : 'col-6';
            },
           
            isActived_lista_cuotas: function(){
                return this.pagination_lista_cuotas.current_page;
            },
            isActived_lista_cuotas_total: function(){
                return this.pagination_lista_cuotas_total.current_page;
            },
            isActived_lista_anulados: function(){
                return this.pagination_lista_anulados.current_page;
            },
            isActived: function(){
                return this.pagination.current_page;
            },
            pagesNumber: function(){
                if(!this.pagination.to){
                    return [];
                }                
                var from = this.pagination.current_page - this.offset;
                if(from < 1){
                    from = 1;
                }
                var to = from + (this.offset * 2);
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }
                var pagesArray = [];
                while(from <= to){
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;
            },
            pagesNumber_lista_cuotas: function(){
                    if(!this.pagination_lista_cuotas.to){
                        return [];
                    }                
                    var from = this.pagination_lista_cuotas.current_page - this.offset_lista_cuotas;
                    if(from < 1){
                        from = 1;
                    }
                    var to = from + (this.offset_lista_cuotas * 2);
                    if(to >= this.pagination_lista_cuotas.last_page){
                        to = this.pagination_lista_cuotas.last_page;
                    }
                    var pagesArray = [];
                    while(from <= to){
                        pagesArray.push(from);
                        from++;
                    }
                    return pagesArray;
                },

                pagesNumber_lista_cuotas_total: function(){
                    if(!this.pagination_lista_cuotas_total.to){
                        return [];
                    }                
                    var from = this.pagination_lista_cuotas_total.current_page - this.offset_lista_cuotas_total;
                    if(from < 1){
                        from = 1;
                    }
                    var to = from + (this.offset_lista_cuotas_total * 2);
                    if(to >= this.pagination_lista_cuotas_total.last_page){
                        to = this.pagination_lista_cuotas_total.last_page;
                    }
                    var pagesArray = [];
                    while(from <= to){
                        pagesArray.push(from);
                        from++;
                    }
                    return pagesArray;
                },
                pagesNumber_lista_anulados: function(){
                        if(!this.pagination_lista_anulados.to){
                            return [];
                        }                
                        var from = this.pagination_lista_anulados.current_page - this.offset_lista_anulados;
                        if(from < 1){
                            from = 1;
                        }
                        var to = from + (this.offset_lista_anulados * 2);
                        if(to >= this.pagination_lista_anulados.last_page){
                            to = this.pagination_lista_anulados.last_page;
                        }
                        var pagesArray = [];
                        while(from <= to){
                            pagesArray.push(from);
                            from++;
                        }
                        return pagesArray;
                    },
                
            },
        methods: {
            buscandoCuotasAnuladas(){
                this.getPagosListaAnulados(1);
            },
            buscandoCuotasPagadas(){
                this.getPagos(1);
            },
            buscandoCuotasPorPagar(){
                this.getPagosListaCuotas(1);
            },
            agregarFoto(){
                // if(this.cliente.imagen==''){
                //     this.mostrarToastError('Debe seleccionar una imagen');
                // }else{
                    const formData = new FormData();
                    formData.append('id_pago', this.pago.id_pago);
                    formData.append('imagen', this.pago.imagen);
            
                    axios.post('/fotoRespaldoPago', formData)
                    .then((response)=>{
                        console.log(response);
                        this.pago.imagen=response.data.imagen;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        //this.getClientes(1);
                        this.getPagos(1);
                    })
                // }
            },
            seleccionarImagen(event){
                    this.pago.imagen = event.target.files[0];
                    this.agregarFoto();
                    
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Operación exitosa',
                        showConfirmButton: false,
                        timer: 1500
                    });
            },
            cerrarModalRespaldo(){
                $('#modalRespaldo').modal('hide');
            },
            abrirModalRespaldo(){
                $('#modalRespaldo').modal('show');
            },
            ingresarRespaldo(item){
                this.abrirModalRespaldo();
                this.pago.id_pago=item.id;
                if(item.imagen!=null){
                    this.pago.imagen=item.imagen;
                }else{
                    this.pago.imagen='default.png';
                }
            },
            inputVistaCuotasFechaSelect(){
                if(this.opcion_vista_cuotas=='fecha'){
                    this.inputVistaCuotasFecha();
                }else{
                    this.inputVistaCuotas();
                }
            },
            inputVistaCuotasFecha(){
                if(this.vista==0){
                    if(this.vista_cuotas==0){
                        this.getPagosListaCuotasFecha(1);
                    }else{
                        if(this.vista_cuotas==1){
                            this.getPagosListaCuotasTotalFecha(1);
                        }
                    }
                }else{
                    if(this.vista==1){
                        this.getPagosFecha(1);
                    }else{
                        if(this.vista==2){
                            this.getPagosListaAnuladosFecha(1);
                        }
                    }
                }
            },
            generarTicket(item){
                
                // Construye la URL con el parámetro fecha_inicio
                const url = '/generar_ticket_pdf?id_pago='+item.id;
    
                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
                
            },
            async consultarCajaAbierta(){
                await axios.get('/caja_abierta').then((response)=>{
                    console.log(response);
                    if(response.data.usuario_actual==-1){
                        Swal.fire({
                                position: 'center',
                                icon: 'warning',
                                title: 'Caja cerrada',
                                text:'No hay una caja aperturada',
                                showConfirmButton: true,
                                // timer: 1500
                            });
                        this.estado_caja=true;
                    }else{
                            console.log('existe caja aperturada');
                            this.estado_caja=false;
                    }
                })
                .catch((error)=>{
                    console.log(error.message);
                })

                .finally(()=>{

                })
            },
            inputVistaCuotas(){
                if(this.vista==0){
                    if(this.vista_cuotas==0){
                        this.getPagosListaCuotas(1);
                    }else{
                        if(this.vista_cuotas==1){
                            this.getPagosListaCuotasTotal(1);
                        }
                    }
                }else{
                    if(this.vista==1){
                        this.getPagos(1);
                    }else{
                        if(this.vista==2){
                            this.getPagosListaAnulados(1);
                        }
                    }
                }
            },
            cambiarVistaCuotas(opcion){
                this.vista_cuotas=opcion;
                this.buscar_vista_cuotas='';
                this.opcion_vista_cuotas='cliente.nombre'
                if(this.vista_cuotas==0){
                    this.getPagosListaCuotas(1);
                }else{
                    if(this.vista_cuotas==1){
                        this.getPagosListaCuotasTotal(1);
                    }
                }
            },
            pagarCuota(){
                if(this.pago.dias_pasados>0){
                    if(this.pago.multa_mora==''){
                        console.log('registrara el pago');
                        this.mostrarToastError('Debe ingresar un valor para la multa');
                    }
                    else{
                        if(isNaN((parseFloat(this.pago.dias_pasados * this.pago.multa_mora) - parseFloat((this.pago.monto_condonado=='')?0:this.pago.monto_condonado)).toFixed(2))?0:(parseFloat(this.pago.dias_pasados * this.pago.multa_mora) - parseFloat((this.pago.monto_condonado=='')?0:this.pago.monto_condonado)).toFixed(2)<0){
                            Swal.fire({
                                position: 'center',
                                icon: 'warning',
                                title: 'Advertencia',
                                text: 'El monto condonado no puede ser mayor al monto de la multa!',
                                //timer: 1500
                                showConfirmButton:true,
                                confirmButtonText:'Aceptar',

                            });
                        }
                        else{
                            if(parseFloat(this.pago.interes) < (isNaN(parseFloat(this.pago.monto_condonado_interes))?0:parseFloat(this.pago.monto_condonado_interes))){
                                Swal.fire({
                                    position: 'center',
                                    icon: 'warning',
                                    title: 'Advertencia',
                                    text: 'El monto condonado no puede ser mayor al monto del interes!',
                                    //timer: 1500
                                    showConfirmButton:true,
                                    confirmButtonText:'Aceptar',

                                });
                            }
                            else{

                                console.log('pago con retraso');
                                let monto_pago=this.pago.monto_a_pagar;
                                this.pago.monto_pago=monto_pago;
                                // this.pago.monto_condonado=this.pago.monto_condonado+this.pago.monto_condonado_interes;

                                let guardado=false;
                                axios.post('/save_pago', this.pago).then((response)=>{
                                    console.log(response);
                                    guardado=true;
                                })
                                .catch((error)=>{
                                    console.log(error.message);
                                })
                                .finally(()=>{
                                    if(guardado){
                                        Swal.fire({
                                            position: 'top-right',
                                            icon: 'success',
                                            title: 'Operación exitosa',
                                            text: 'Pago cobrado con exito!',
                                            timer: 1500
                                        });
                                        $('#modalPagarCuota').modal('hide');
                                        this.getPagosListaCuotas(1);
                                        this.getPagosListaCuotasTotal(1);
                                        this.getPagos(1);
                                    }
                                })
                            }
                        }
                    }
                }else{
                    console.log('pago sin retraso');
                    let monto_pago=this.pago.monto_a_pagar;
                    this.pago.monto_pago=monto_pago;
                    let guardado=false;
                    axios.post('/save_pago', this.pago).then((response)=>{
                        console.log(response);
                        guardado=true;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        if(guardado){
                            Swal.fire({
                                position: 'top-right',
                                icon: 'success',
                                title: 'Operación exitosa',
                                text: 'Pago cobrado con exito!',
                                timer: 1500
                            });
                            $('#modalPagarCuota').modal('hide');
                            this.getPagosListaCuotas(1);
                            this.getPagosListaCuotasTotal(1);
                            this.getPagos(1);
                        }
                    })
                }
            },
            cerrarModalPagarCuota(){
                $('#modalPagarCuota').modal('hide');
            },
            async abrirModalPagarCuota(item){
                await this.consultarCajaAbierta();
                if(!this.estado_caja){
                    $('#modalPagarCuota').modal('show');
                    this.pago.id_cuota=item.id;
                    //this.pago.id_pago=item.id;
                    //this.pago.fecha_pago=item.fecha_pago;
                    this.pago.monto_a_pagar=item.monto_a_pagar;
                    this.pago.usuario=item.asesor;
                    this.pago.cliente=item.cliente;
                    this.pago.nro_cuotas=item.nro_cuotas;
                    this.pago.id_plan_pago=item.plan_pago;
                    this.pago.cuota=item.plan_pago;
                    this.pago.dias_pasados=item.dias_pasados;
                    this.pago.multa_mora=3;
                    this.pago.monto_condonado=0;
                    this.pago.interes=item.interes;
                    this.pago.monto_condonado_interes=0;
                    this.pago.motivo_condonacion='';
                    this.pago.capital=item.capital;
                    this.pago.seguro=item.seguro;
                    this.pago.ahorro=item.ahorro;
                }else{

                }

            },
            esFechaActual(fechaPago) {
                const fechaActual = new Date();
                const partesFechaPago = fechaPago.split('-');
                
                const anioPago = parseInt(partesFechaPago[0], 10);
                const mesPago = parseInt(partesFechaPago[1], 10) - 1; // Restamos 1 al mes porque en JavaScript los meses van de 0 a 11
                const diaPago = parseInt(partesFechaPago[2], 10);

                const fechaPagoDate = new Date(anioPago, mesPago, diaPago);
                
                // Compara las fechas sin tener en cuenta la hora
                fechaActual.setHours(0, 0, 0, 0);
                fechaPagoDate.setHours(0, 0, 0, 0);

                return fechaActual.getTime() === fechaPagoDate.getTime();
            },
            anularPago(){
                let anulado=false;
                axios.post('/anular_pago', 
                {id_pago:this.pago.id_pago, id_plan_pago:this.pago.id_plan_pago, 
                    nro_cuotas:this.pago.nro_cuotas, id_cuota:this.pago.id_cuota})
                .then((response)=>{
                    console.log(response);
                    anulado=true;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    if(anulado){
                        Swal.fire({
                            position: 'top-right',
                            icon: 'success',
                            title: 'Operación exitosa',
                            text: 'Pago anulado con exito!',
                            timer: 1500
                        });
                        this.getPagos(1);
                        this.getPagosListaAnulados(1);
                        this.getPagosListaCuotas(1);
                        this.getPagosListaCuotasTotal(1);
                        $('#modalAnularPago').modal('hide');
                    }
                })
            },
            cerrarModalAnularPago(){
                $('#modalAnularPago').modal('hide');
            },
            abrirModalAnularPago(item){
                $('#modalAnularPago').modal('show');
                this.pago.id_cuota=item.id_cuota;
                this.pago.id_pago=item.id;
                this.pago.fecha_pago=item.fecha_pago;
                this.pago.monto_pago=item.monto_pago;
                this.pago.usuario=item.asesor;
                this.pago.cliente=item.cliente;
                this.pago.nro_cuotas=item.nro_cuotas;
                this.pago.id_plan_pago=item.plan_pago;
            },

            
            async cambiarVista(valor){
                this.vista=valor;
                this.buscar_vista_cuotas='';
                this.opcion_vista_cuotas='cliente.nombre';
                
                if(this.vista==0){
                    if(this.vista_cuotas==0){
                        this.opcion_vista_cuotas_por_pagar='cliente.nombre',
                        this.buscar_vista_cuotas_por_pagar='',
                        this.fecha_inicial_cuotas_por_pagar=moment().subtract(1, 'months').format('YYYY-MM-DD');
                        this.fecha_final_cuotas_por_pagar=moment().format('YYYY-MM-DD');
                        this.estadoCuota='todos';
                        await this.getPagosListaCuotas(1);

                    }else{
                        if(this.vista_cuotas==1){
                            this.getPagosListaCuotasTotal(1);
                        }
                    }
                }else{
                    if(this.vista==1){
                        this.opcion_vista_cuotas_pagadas='cliente.nombre';
                        this.buscar_vista_cuotas_pagadas='';
                        this.fecha_inicial_cuotas_pagadas=moment().subtract(1, 'months').format('YYYY-MM-DD');
                        this.fecha_final_cuotas_pagadas=moment().format('YYYY-MM-DD');
                        await this.getPagos(1);
                    }else{
                        if(this.vista==2){
                            this.getPagosListaAnulados(1);
                        }
                    }
                }
                console.log(this.vista);
            },
            getPagosFecha(page){
                axios.get('/get_pagos_fecha?page='+page+'&fecha_inicial='+this.fecha_inicial+'&fecha_final='+this.fecha_final).then((response)=>{
                    console.log(response);
                    this.lista_pagos=response.data.pagos.data;
                    this.pagination={total:response.data.pagos.total, 
                            current_page:response.data.pagos.current_page,
                            per_page: response.data.pagos.per_page,
                            last_page: response.data.pagos.last_page,
                            from: response.data.pagos.from,
                            to: response.data.pagos.to
                    };
                    this.totalPagos=isNaN(parseFloat(response.data.totalPagos).toFixed(2))?0:parseFloat(response.data.totalPagos).toFixed(2);
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },
            async getPagos(page){
                await axios.get('/get_pagos?page='+page+
                '&buscar='+this.buscar_vista_cuotas_pagadas+'&opcion='+this.opcion_vista_cuotas_pagadas
                +'&fecha_inicial='+this.fecha_inicial_cuotas_pagadas+'&fecha_final='+this.fecha_final_cuotas_pagadas).then((response)=>{
                    console.log(response);
                    this.lista_pagos=response.data.pagos.data;
                    this.pagination={total:response.data.pagos.total, 
                            current_page:response.data.pagos.current_page,
                            per_page: response.data.pagos.per_page,
                            last_page: response.data.pagos.last_page,
                            from: response.data.pagos.from,
                            to: response.data.pagos.to
                    };
                    this.totalPagos=isNaN(parseFloat(response.data.totalPagos).toFixed(2))?0:parseFloat(response.data.totalPagos).toFixed(2);
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },
            async getPagosListaCuotasFecha(page){
                await axios.get('/get_pagos_lista_cuotas_fecha?page='+page+'&fecha_inicial='+this.fecha_inicial+'&fecha_final='+this.fecha_final).then((response)=>{
                    console.log(response);
                    this.lista_cuotas=response.data.data;
                    this.pagination_lista_cuotas={total:response.data.total, 
                            current_page:response.data.current_page,
                            per_page: response.data.per_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                    }
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },
            async getPagosListaCuotas(page){
                await axios.get('/get_pagos_lista_cuotas?page='+page+'&buscar='+
                this.buscar_vista_cuotas_por_pagar+'&opcion='+this.opcion_vista_cuotas_por_pagar
                +'&fecha_inicial='+this.fecha_inicial_cuotas_por_pagar+'&fecha_final='+this.fecha_final_cuotas_por_pagar+'&estado_cuota='+this.estadoCuota).then((response)=>{
                    console.log(response);
                    this.lista_cuotas=response.data.pagos.data;
                    // this.pagination_lista_cuotas={
                    //         total:response.data.total, 
                    //         current_page:response.data.current_page,
                    //         per_page: response.data.per_page,
                    //         last_page: response.data.last_page,
                    //         from: response.data.from,
                    //         to: response.data.to
                    // };
                    this.pagination_lista_cuotas=response.data.pagos;
                    this.totalCuotas = response.data.totalCuotas;
                    this.totalCuotasMora = response.data.totalCuotasMora;
                    this.totalCuotasSinMora = response.data.totalCuotasSinMora;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },

            getPagosListaCuotasTotalFecha(page){
                axios.get('/get_pagos_lista_cuotas_total_fecha?page='+page+'&fecha_inicial='+this.fecha_inicial+'&fecha_final='+this.fecha_final).then((response)=>{
                    console.log(response);
                    this.lista_cuotas_total=response.data.data;
                    this.pagination_lista_cuotas_total={total:response.data.total, 
                            current_page:response.data.current_page,
                            per_page: response.data.per_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                    }
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },

            getPagosListaCuotasTotal(page){
                axios.get('/get_pagos_lista_cuotas_total?page='+page+'&buscar='+this.buscar_vista_cuotas+'&opcion='+this.opcion_vista_cuotas).then((response)=>{
                    console.log(response);
                    this.lista_cuotas_total=response.data.data;
                    this.pagination_lista_cuotas_total={total:response.data.total, 
                            current_page:response.data.current_page,
                            per_page: response.data.per_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                    }
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },

            /*
                opcion_vista_cuotas_anuladas:'cliente.nombre',
                buscar_vista_cuotas_anuladas:'',
                fecha_inicial_cuotas_anuladas:moment().subtract(1, 'months').format('YYYY-MM-DD'),
                fecha_inicial_cuotas_final:moment().format('YYYY-MM-DD'),
            */
            async getPagosListaAnulados(page){
                await axios.get('/get_pagos_lista_anulados?page='+page
                    +'&buscar='+this.buscar_vista_cuotas_anuladas
                    +'&opcion='+this.opcion_vista_cuotas_anuladas
                    +'&fecha_inicial='+this.fecha_inicial_cuotas_anuladas
                    +'&fecha_final='+this.fecha_final_cuotas_anuladas
                ).then((response)=>{
                    console.log(response);
                    this.lista_pagos_anulados=response.data.pagos_anulados.data;
                    // this.pagination_lista_anulados={
                    //         total:response.data.total, 
                    //         current_page:response.data.current_page,
                    //         per_page: response.data.per_page,
                    //         last_page: response.data.last_page,
                    //         from: response.data.from,
                    //         to: response.data.to
                    // };
                    this.pagination_lista_anulados=response.data.pagos_anulados;
                    this.totalPagosAnulados=response.data.totalPagosAnulados;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },

            getPagosListaAnuladosFecha(page){
                axios.get('/get_pagos_lista_anulados_fecha?page='+page+'&fecha_inicial='+this.fecha_inicial+'&fecha_final='+this.fecha_final).then((response)=>{
                    console.log(response);
                    this.lista_pagos_anulados=response.data.data;
                    this.pagination_lista_anulados={total:response.data.total, 
                            current_page:response.data.current_page,
                            per_page: response.data.per_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                    }
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    
                })
            },
            cambiarPagina(page){
                let me=this;
                me.pagination.current_page=page;
                me.getPagos(page);
            },

            cambiarPaginaListaCuotas(page){
                let me=this;
                me.pagination_lista_cuotas.current_page=page;
                me.getPagosListaCuotas(page);
            },

            cambiarPaginaListaCuotasTotal(page){
                let me=this;
                me.pagination_lista_cuotas_total.current_page=page;
                me.getPagosListaCuotasTotal(page);
            },

            cambiarPaginaListaAnulados(page){
                let me=this;
                me.pagination_lista_anulados.current_page=page;
                me.getPagosListaAnulados(page);
            },

            mostrarToastError(mensaje) {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                this.mensajeError = mensaje;
                miToast.show();
            },
            cerrarToastError() {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                miToast.hide();
            },
           
        },
        mounted() {
            console.log('Component mounted.');
            this.getPagosListaCuotas(1);
            this.getPagos(1);
            this.getPagosListaAnulados(1);
            this.getPagosListaCuotasTotal(1);

   
            // this.getClientes(1);
        }


    }

</script>
<style>
.dots::after {
    content: '\00a0.\00a0.\00a0.\00a0.'; /* Agrega los puntos suspensivos */
    white-space: nowrap; /* Evita que los puntos suspensivos se envuelvan */
}
</style>

<style>
#modalPagarCuota .modal-content {
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    background-color: #f8f9fa;
}

#modalPagarCuota .modal-header {
    background-color: #333;
    color: #fff;
    border-bottom: none;
}

#modalPagarCuota .modal-footer {
    background-color: #f1f1f1;
}

#modalPagarCuota .btn-close-white {
    filter: invert(1);
}

#modalPagarCuota .fw-bold {
    font-weight: 600;
}

#modalPagarCuota .text-muted {
    color: #6c757d;
}

#modalPagarCuota .badge {
    background-color: #ffc107;
    color: #000;
}
</style>
