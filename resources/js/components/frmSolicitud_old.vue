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
                                <h4 class="mb-0 font-size-18 text-uppercase">Gestión de solicitudes</h4>
                                
                            </div>

                            <div class="state-information d-sm-block">
                                <button @click="abrirModalNuevo()" class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    Nuevo
                                </button>
                            </div>


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
                                        <div class="row mb-3">
                                            
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <input @input="buscarSolicitud()" v-model="fecha_inicial_buscar" type="date" name="" id="" class="form-control">
                                                    <button class="btn btn-success me-1">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </button>
                                                    <button class="btn btn-danger">
                                                        <i class="fas fa-arrow-left"></i>
                                                    </button>
                                                    <input @input="buscarSolicitud()" v-model="fecha_final_buscar" type="date" name="" id="" class="form-control">
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                            
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <select v-model="criterio_estado" class="form-select form-control" @change="buscarSolicitud()">
                                                        <option value="todos">Todos</option>
                                                        <option value="1">Nuevo</option>
                                                        <option value="2">Aprobados</option>
                                                        <option value="0">Anulados</option>
                                                    </select>
                                                    <select v-model="criterio" class="form-select form-control">
                                                        <option value="cliente.nombre">Nombre cliente</option>
                                                        <option value="cliente.ci">CI cliente</option>
                                                    </select>
                                                    <input :placeholder="'Ingrese texto a buscar'" v-model="buscar" type="text" class="form-control" @input="buscarSolicitud()">
                                                    <button class="btn btn-success">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <button @click="abrirCalculadoraCredito()" class="btn btn-outline-success btn-sm px-3" style="border-radius:25px !important">
                                                    <i class="fas fa-calculator"></i>
                                                     Calculadora de crédito
                                                </button>
                                            </div>
                                        </div>

                                        <h6 class="mt-3">Listado de solicitudes</h6>
                                        <div class="table-responsive" style="font-size:12px">
                                            <table class="table mb-4 table-sm table-striped table-hover">
                                                <thead class="text-uppercase bg-primary text-white">
                                                    <tr style="background-color:#52BE80" >
                                                        <th>Nro</th>
                                                        <th>Cliente</th>
                                                        <th>Garantes</th>
                                                        <th>Asesor</th>
                                                        <th>Importe</th>
                                                        <th>Cuotas</th>
                                                        <th>Tasa</th>
                                                        <th>F. Reg.</th>
                                                        <th>F. Des.</th>
                                                        <th>Tipo</th>
                                                        <th>Estado</th>
                                                        <th>Op.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="" v-for="item in lista_solicitudes.filter(solicitud => solicitud.estado !==10)" :key="item.id">

                                                        <td>{{ item.id }}</td>
                                                        <td>{{ item.cliente }}</td>
                                                        <td>
                                                            <p class="my-0 my-0 text-dark" v-for="(item, index) in lista_codeudores_tabla.filter(codeudor => codeudor.id_solicitud === item.id)" :key="index">
                                                                * {{item.nombre}}
                                                            </p>
                                                        </td>
                                                        <td>{{ item.personal }}</td>
                                                        <td>{{ item.importe_solicitud }}</td>
                                                        <td>{{ item.nro_cuotas }}</td>
                                                        <td>{{ item.tasa }}</td>

                                                        <td>{{ formatearFecha(item.fecha) }}</td>
                                                        
                                                        <td>{{  formatearFecha(item.fecha_desembolso)}}</td>
                                                        <td>{{ item.tipo_garantia }}</td>
                                                        <td>
                                                            <span v-if="item.estado==1" class="text-info">Nuevo</span>
                                                            <span v-else-if="item.estado==0" class="text-danger">Anulado</span>
                                                            <span v-else-if="item.estado==2" class="text-success">Aprobado</span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a 
                                                                    style="cursor:pointer;" class="text-success dropdown-toggle btn-sm my-0 py-0"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-ellipsis-h fa-lg fa-fw fs-4"></i>

                                                                </a>
                                                                <ul class="dropdown-menu my-0 py-0">
                                                                    <li @click="desactivarSolicitud(item)" v-if="item.estado==1">
                                                                        <a class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-times"></i> Anular</a>
                                                                    </li>
                                                                    <li @click="activarSolicitud(item)" v-else-if="item.estado==0"><a
                                                                            class="dropdown-item text-success" href="#">
                                                                            <i class="fas fa-check"></i> activar</a>
                                                                    </li>
                                                                    <li v-if="item.estado==1" @click="editarSolicitud(item)"><a
                                                                            class="dropdown-item text-primary" href="#">
                                                                            <i class="fas fa-pencil-alt"></i> editar</a></li>
                                                                    <li @click="verSolicitud(item)"><a
                                                                            class="dropdown-item text-info" href="#">
                                                                            <i class="fas fa-eye"></i> ver</a></li>

                                                                    <li v-if="item.estado!=0 && item.estado!=2 && rolUsuario=='administrador'" @click="abrirModalSimulacionPlanPago(item)"><a
                                                                            class="dropdown-item text-success" href="#">
                                                                            <i class="fas fa-money-bill"></i> Aprobar solicitud</a></li>

                                                                    <li v-if="item.tipo_garantia=='Prendario'" @click="abrirModalGarantias(item.id)"><a
                                                                            class="dropdown-item text-secondary" href="#">
                                                                            <i class="fas fa-images"></i> Garantias</a></li>
                                                                    <li @click="abrirModalRespaldos(item.id)"><a
                                                                            class="dropdown-item text-warning" href="#">
                                                                            <i class="fas fa-images"></i> Respaldos</a></li>

                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr> 
                                                </tbody> 
                                            </table>
                                            
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
                                            <template v-if="lista_solicitudes.length<5">
                                                <br><br><br><br><br><br>
                                            </template>
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

        <!-- Modal solicitud -->
        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalSolicitud" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width:85%; width:85%">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 v-if="solicitud.accion==0" class="modal-title text-white" id="miModalLabel">Agregar nueva solicitud</h5>
                        <h5 v-if="solicitud.accion==1" class="modal-title text-white" id="miModalLabel">Modificar solicitud</h5>
                        <h5 v-if="solicitud.accion==2" class="modal-title text-white" id="miModalLabel">Información de la solicitud: </h5>
                        <button @click="cerrarModalNuevo()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card border border-1 border-success">
                                        <div class="card-header bg-success">
                                            <h5 class="text-white my-0">
                                                <i class="fas fa-user"></i>
                                                Seleccione cliente
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="mb-3">
                                                        <label for="id_cliente" class="form-label fw-bold">Cliente</label>

                                                        <section class="dropdown-wrapper form-control p-1 bg-white" :style="(solicitud.accion === 2) ? { 'pointer-events': 'none' } : {}">
                                                            <div @click="isVisibleCliente = !isVisibleCliente" class="selected-item p-0">
                                                                <span v-if="cliente.buscar==''">Seleccione un cliente</span>
                                                                <span v-else>{{cliente.buscar }} </span>
                                                                <svg :class="isVisibleCliente ? 'dropdown' : ''" class="drop-down-icon"
                                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                                                    <path fill="none" d="M0 0h24v24H0z" />
                                                                    <path d="M12 10.828l-4.95 4.95-1.414-1.414L12 8l6.364 6.364-1.414 1.414z" /></svg>
                                                            </div>
                                                            <div :class="isVisibleCliente  ? 'visible' : 'invisible'" class="dropdown-popover bg-white"
                                                                style="position: absolute; z-index: 9999;">
                                                                <input type="text" class="form-control" placeholder="Buscar cliente.."
                                                                    v-model="cliente.idd_cliente" aria-label="Buscar cliente.." style="font-size:11px">
                                                                <div class="text-center"><span v-if="filteredItemsCliente.length === 0">No existe el cliente
                                                                        </span></div>
                                                                <div class="options">
                                                                    <ul >
                                                                        <li @click="seleccionarCliente(cliente)"
                                                                            v-for="(cliente, index) in filteredItemsCliente" :key="index">
                                                                            {{cliente.nombre + ' - '+ cliente.ci}}</li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </section>
                                                        <small class="text-danger" v-if="(solicitud.id_cliente==0) && solicitud.enviado==1">seleccione un cliente *</small>
                                                        
                                                    </div>
                                                </div>
        
        
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="id_cliente" class="form-label fw-bold">CI</label>
                                                        <input :disabled="true" v-model="cliente.ci" type="text" class="form-control">
                                                    </div>
                                                </div>
        
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="id_cliente" class="form-label fw-bold">Actividad</label>
                                                        <input :disabled="true" v-model="cliente.actividad" type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    

                                </div>


                                <div class="col-md-12">
                                    <div class="card border border-1 border-success">
                                        <div class="card-header bg-success">
                                            <h5 class="text-white my-0">
                                                <i class="fas fa-users-cog"></i>
                                                Seleccione codeudores/garantes
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <!-- <label for="id_cliente" class="form-label">Codeudor</label> -->

                                                        <template v-for="(item, index) in lista_codeudores" :key="index">

                                                                 <div class="col-md-5">
                                                                     <label for="id_cliente" class="form-label fw-bold">Nombre</label>
     
                                                                     <div class="input-group">
                                                                         <section class="dropdown-wrapper form-control p-1 bg-white" :style="(solicitud.accion === 2) ? { 'pointer-events': 'none' } : {}">
                                                                             <div @click="item.select_codeudor.isVisibleCodeudor= !item.select_codeudor.isVisibleCodeudor" class="selected-item p-1">
                                                                                 <span v-if="item.select_codeudor.codeudor.buscar==''">Seleccione un codeudor</span>
                                                                                 <span v-else>{{item.select_codeudor.codeudor.buscar }} </span>
                                                                                 <svg :class="item.select_codeudor.isVisibleCodeudor ? 'dropdown' : ''" class="drop-down-icon"
                                                                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                                                                     <path fill="none" d="M0 0h24v24H0z" />
                                                                                     <path d="M12 10.828l-4.95 4.95-1.414-1.414L12 8l6.364 6.364-1.414 1.414z" /></svg>
                                                                             </div>
                                                                             <div :class="item.select_codeudor.isVisibleCodeudor  ? 'visible' : 'invisible'" class="dropdown-popover"
                                                                                 style="position: absolute; z-index: 9999;">
                                                                                 <input type="text" class="form-control" placeholder="Buscar codeudor.."
                                                                                     v-model="item.select_codeudor.codeudor.idd_codeudor" aria-label="Buscar cliente.." style="font-size:11px">
                                                                                 <div class="text-center"><span v-if="filteredItemsCodeudor(index).length === 0">No existe el codeudor
                                                                                         </span></div>
                                                                                 <div class="options">
                                                                                     <ul >
                                                                                         <li @click="seleccionarCodeudor(codeudor, index)"
                                                                                             v-for="(codeudor, index2) in filteredItemsCodeudor(index)" :key="index2">
                                                                                             {{codeudor.nombre + ' - '+ codeudor.ci}}</li>
                                                                                     </ul>
                                                                                 </div>
                                                                             </div>
                                                                         </section>

                                                                         <template v-if="solicitud.accion!=2">
                                                                             <button @click="addCodeudor()" type="button"  style="border-radius:50%" class="btn btn-success ms-1">
                                                                                 <i class="fas fa-plus"></i>
                                                                             </button>
                                                                             <button v-if="lista_codeudores.length>1" @click="deleteCodeudor(index)" type="button"  style="border-radius:50%" class="btn btn-danger ms-1">
                                                                                 <i class="fas fa-times"></i>
                                                                             </button>   
                                                                         </template>
                                                                         
                                                                     </div>
                                                                     <small class="text-danger" v-if="item.select_codeudor.codeudor.id_codeudor==0 && solicitud.enviado==1">seleccione un codeudor *</small>

                                                                 </div>
                                                                 

                                                                <div class="col-md-3">
                                                                    <div class="mb-3">
                                                                        <label for="id_cliente" class="form-label fw-bold">CI</label>
                                                                        <input :disabled="true" v-model="item.select_codeudor.codeudor.ci" type="text" class="form-control">
                                                                    </div>
                                                                </div>
                        
                                                                <div class="col-md-4">
                                                                    <div class="mb-3">
                                                                        <label for="id_cliente" class="form-label fw-bold">Actividad</label>
                                                                        <input  :disabled="true" v-model="item.select_codeudor.codeudor.actividad" type="text" class="form-control">
                                                                    </div>
                                                                </div>

                                                        </template>
                                                      

                                                    </div>
                                                </div>
        
        
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="card border border-success border-1">

                                        <div class="card-body row">
                                            <div class="col-md-6">
                                
                                                <div class="mb-3">
                                                    <label for="importe_solicitud" class="form-label fw-bold">Importe solicitud</label>
                                                    <input v-model="solicitud.importe_solicitud" type="text" class="form-control" id="importe_solicitud"
                                                        name="importe_solicitud" :disabled="solicitud.accion==2?true:false" required
                                                        onkeydown="if(event.key==='.' && event.target.value.includes('.')){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9.]*/g,'');">
                                                    <small class="text-danger text-sm-start" v-if="(solicitud.importe_solicitud==0 || solicitud.importe_solicitud=='') && solicitud.enviado==1">ingrese un importe *</small>
                                                </div>
            
                           
                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="moneda" class="form-label fw-bold">Moneda</label>
                                                            <select :disabled="solicitud.accion==2?true:false" v-model="solicitud.moneda" class="form-select"
                                                                id="moneda" name="moneda" required>
                                                                <option value="0" selected hidden disabled>Seleccione moneda</option>
                                                                <option v-for="item in lista_monedas" :value="item.nombre"
                                                                    :key="item.nombre">
                                                                    {{ item.nombre }}</option>
                                                            </select>
            
                                                            <small class="text-danger" v-if="(solicitud.moneda=='0' || solicitud.moneda=='') && solicitud.enviado==1">seleccione una moneda*</small>
            
                                                        </div>
            
                                                        <div class="col-md-4">
                                                            <label for="solicitud_tipo_tasa" class="form-label fw-bold">Tipo tasa</label>
                                                            <select @change="(tipo_tasa=='fija' || tipo_tasa=='amortizable')?seleccionarLapsoCapitalFormulario():''" :disabled="solicitud.accion==2?true:false" v-model="tipo_tasa" class="form-select"
                                                                id="solicitud_tipo_tasa" name="solicitud_tipo_tasa" required>
                                                                <option value="0" selected hidden disabled>Seleccione un tipo</option>
                                                                <option value="fija">Fija</option>
                                                                <option value="amortizable">Amortizable</option>
                                                            </select>
            
                                                            <small class="text-danger" v-if="(solicitud.tipo_tasa=='0' || solicitud.tipo_tasa=='') && solicitud.enviado==1">seleccione tipo tasa*</small>
            
                                                        </div>
            
                                                        <div class="col-md-4">
                                                            <label for="lapso_capital" class="form-label fw-bold">Lapso capital</label>
                                                            <select @change="(tipo_tasa=='fija' || tipo_tasa=='amortizable')? seleccionarLapsoCapitalFormulario():''" :disabled="solicitud.accion==2?true:false" v-model="solicitud.lapso_capital" class="form-select"
                                                                id="lapso_capital" name="lapso_capital" required>
                                                                <option value="0" selected hidden disabled>Seleccione</option>
                                                                <option v-for="item in lapso_capitales" :value="item.nombre"
                                                                    :key="item.nombre">
                                                                    {{ item.nombre }}</option>
                                                            </select>
                                                            <small class="text-danger" v-if="(solicitud.lapso_capital=='0' || solicitud.lapso_capital=='') && solicitud.enviado==1">seleccione una opción*</small>
            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="nro_cuotas" class="form-label fw-bold">Nro. cuotas</label>
                                                        <input v-model="solicitud.nro_cuotas" type="number" :disabled="solicitud.accion==2?true:false" class="form-control" id="nro_cuotas" name="nro_cuotas" required>
                                                        <small class="text-danger" v-if="solicitud.nro_cuotas=='' && solicitud.enviado==1">Ingrese nro cuotas *</small>
                                                                
                                                    </div>
                                                    <div class="mb-3 col-md-6">
                                                        <label for="tasa" class="form-label fw-bold">Tasa %</label>
                                                        <input v-model="solicitud.tasa" type="text" :disabled="solicitud.accion==2?true:false" class="form-control" id="tasa" name="tasa" required
                                                        onkeydown="if(event.key==='.'){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');">
                                                        <small class="text-danger" v-if="solicitud.tasa=='' && solicitud.enviado==1">Ingrese tasa *</small>
                                                          
                                                    </div>
                                                </div>
                                            </div>
            
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="fecha_desembolso" class="form-label fw-bold">Fecha desembolso</label>
                                                            <input @input="(tipo_tasa=='fija' || tipo_tasa=='amortizable')? seleccionarLapsoCapitalFormulario():''" v-model="solicitud.fecha_desembolso" :disabled="solicitud.accion==2?true:false" type="date" class="form-control" id="fecha_desembolso" name="fecha_desembolso"
                                                                required>
                                                            <!-- <p class="text-danger text-sm-start" v-if="solicitud.actividad=='' && solicitud.enviado==1">Ingrese una actividad *</p> -->
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <!-- :disabled="solicitud.accion==2?true:false"  -->
                                                            <label for="fecha_primera_cuota" class="form-label fw-bold">Fecha primera cuota</label>
                                                            <input v-model="solicitud.fecha_primera_cuota" 
                                                            :disabled="(tipo_tasa=='fija' || tipo_tasa=='amortizable')?true:false" 
                                                            type="date" class="form-control" id="fecha_primera_cuota" name="fecha_primera_cuota"
                                                            required>
                                                            <!-- <p class="text-danger text-sm-start" v-if="solicitud.actividad=='' && solicitud.enviado==1">Ingrese una actividad *</p> -->
                                                            
                                                        </div>
                                                    </div>
                                                </div>
            
                                                <div class="mb-3">
                                                    <label for="destino_prestamo" class="form-label fw-bold">Destino prestamo</label>
                                                    <input  :disabled="solicitud.accion==2?true:false" v-model="solicitud.destino_prestamo" type="text" class="form-control" id="destino_prestamo"
                                                        name="destino_prestamo" required>
                                                    <small class="text-danger" v-if="solicitud.destino_prestamo=='' && solicitud.enviado==1">Ingrese un destino prestamo *</small>
                                                </div>
            
                                                <div class="mb-3">
                                                    <div class="row">
            
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="tipo_desembolso" class="form-label fw-bold">Tipo desembolso</label>
                                                                <select :disabled="solicitud.accion==2?true:false" v-model="solicitud.tipo_desembolso" class="form-select"
                                                                    id="tipo_desembolso" name="tipo_desembolso" required>
                                                                    <option value="0" selected hidden disabled>Seleccione</option>
                                                                    <option v-for="item in tipos_desembolsos" :value="item.nombre"
                                                                        :key="item.nombre">
                                                                        {{ item.nombre }}</option>
                                                                    <option value="10">Eletrodoméstico</option>
                                                                    <option value="11">Mueble</option>
                                                                    
                                                                </select>
                                                                <small class="text-danger" v-if="(solicitud.tipo_desembolso=='0' || solicitud.tipo_desembolso=='') && solicitud.enviado==1">seleccione un tipo desembolso *</small>
                                                        
                                                            </div>
                                                        </div>
            
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="tipo_garantia" class="form-label fw-bold">Tipo garantia</label>
                                                                <select :disabled="solicitud.accion==2?true:false" v-model="solicitud.tipo_garantia" class="form-select"
                                                                    id="tipo_garantia" name="tipo_garantia" required>
                                                                    <option value="0" selected hidden disabled>Seleccione</option>
                                                                    <option v-for="item in tipos_garantias" :value="item.nombre"
                                                                        :key="item.nombre">
                                                                        {{ item.nombre }}</option>
                                                                </select>
                                                                <small class="text-danger" v-if="(solicitud.tipo_garantia=='0' || solicitud.tipo_garantia=='') && solicitud.enviado==1">seleccione un tipo garantia*</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <hr>

                            <div v-if="solicitud.tipo_garantia=='Prendario'" class="table-responsive">
                                <table class="table mb-1">
                                    <thead>
                                        <tr>
                                   
                                            <th class="fw-bold">Descripción</th>
                                            <!-- <th>Archivo</th> -->
                                          
                                            <th v-if="cliente.accion!=2" class="fw-bold">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="" v-for="(item, index) in lista_garantias" :key="index">

                                  
                                            <td><input :disabled="solicitud.accion==2?true:false" type="text" v-model="item.descripcion" class="form-control">
                                                <small class="text-danger text-sm-start" v-if="item.descripcion=='' && solicitud.enviado==1 ">Ingrese una descripcion *</small>
                                            </td>
                                          
                                            <td v-if="cliente.accion!=2">
                                                <div class="btn-group">
                                                    <a @click="agregarGarantia()" style="border-radius:50%" class="btn btn-success text-white mx-1">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    <a v-if="lista_garantias.length>1" @click="quitarGarantia(index)" style="border-radius:50%" class="btn btn-danger text-white">

                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>

                            

                            <div class="modal-footer d-flex justify-content-center">
                                <button @click="cerrarModalNuevo()" type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                    <i class="fas fa-times-circle"></i>
                                    Cerrar</button>
                                <button :disabled="guardando_solicitud" v-if="solicitud.accion==0" @click="guardarSolicitud()" type="button" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Guardar</button>
                                <button v-if="solicitud.accion==1" @click="modificarSolicitud()" type="button" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Modificar</button>
                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div> 


        <!-- Modal cliente -->
        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalGarantias" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="miModalLabel">Vista de Garantias</h5>
                        
                        <button @click="cerrarModalGarantias(), limpiarImagenes()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <form @submit.prevent="guardarImagen()">
                        <div class="modal-body">
                            <div clas="row" v-for="(item, index) in lista_garantias_imagenes" :key="index">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descripcion">
                                            Descripcion garantia
                                        </label>
                                        <input class="form-control" type="input" :value="item.descripcion" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4" v-for="(item2, index2) in item.lista_imagenes" :key="index2">
                                            
                                                <div class="card m-1">
                                                    <img :id="'imagenPreview'+index+''+index2" style="width: 100%; height: auto; object-fit: contain;" alt="imagen_descripcion"
                                                    :src="item2.id_garantia!=0?'img/garantia/'+item2.imagen:'img/garantia/default.png'">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="input-group">
                                                        <input type="file" class="form-control" @change="seleccionarImagen($event, index2, index, item)">
                                                        <button @click="agregarImagen(index)" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button @click="eliminarImagen(index, index2)" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                      
                                        </div>
                                    </div>
                                </div>
    
                            </div>
                        </div>
    
                        <div class="modal-footer">
                            <button @click="cerrarModalGarantias()" type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cerrar</button>
                            <!-- <button type="submit" class="btn btn-primary">Guardar</button> -->
                        </div>
                    </form>
                </div>
            </div>
        </div> 


        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalSimulacionPlanPago" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width:90%; width:90%">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="miModalLabel">Gestión de Aprobación del Credito</h5>
                        
                        <button @click="cerrarModalSimulacionPlanPago()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    
                    <div class="modal-body">
                            <div class="row mb-3">
                           
                                <div class="col-md-12 text-center mb-3">
                                    <a :disabled="aprobando_solicitud" v-if="solicitud.estado==1" @click="aprobarSolicitud(solicitud.id_solicitud)" class="btn btn-success btn-sm ms-2">
                                        <i class="fas fa-thumbs-up"></i>
                                        Aprobar solicitud</a>
                                    <a v-if="solicitud.estado==2" class="btn btn-danger btn-sm ms-2">
                                        <i class="fas fa-thumbs-up"></i>
                                        Solicitud aprobada</a>
                                    <a @click="listaCuotasPdf(solicitud.id_solicitud)" class="btn btn-info btn-sm ms-2">
                                        <i class="fas fa-file-pdf"></i>
                                        Generar PDF</a>
                                    <a @click="generalPlanPagosGeneral()" class="btn btn-primary btn-sm ms-2">Generar plan pagos</a>
                                    
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row">
                                        <label class="col-md-4">
                                            <strong>Cliente:</strong>
                                        </label>
                                        <p class="col-md-8">{{ cliente_simulacion.nombre }}</p>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-4">
                                            <strong>CI:</strong>
                                        </label>
                                        <p class="col-md-8">{{ cliente_simulacion.ci+' '+ cliente_simulacion.lugar_expedicion}}</p>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-4">
                                            <strong>Garantia:</strong>
                                        </label>
                                        <p class="col-md-8">{{ solicitud.tipo_garantia }}</p>
                                    </div>
                                    
                                </div>

                                <div class="col-md-4">
                                    <div class="row">
                                        <label class="col-md-6">
                                            <strong>Plazo:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.nro_cuotas +' ('+solicitud.lapso_capital+')'}}</p>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-6">
                                            <strong>Monto desembolso:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.importe_solicitud +' '+solicitud.moneda}}</p>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-6">
                                            <strong>Forma de pago:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.lapso_capital}}</p>
                                    </div>
                                    
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <label class="col-md-6">
                                            <strong>Nro. Cuotas:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.nro_cuotas }}</p>
                                    </div>
                                    <!-- <div class="row">
                                        <label class="col-md-6">
                                            <strong>fecha desembolso:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.fecha_desembolso}}</p>
                                    </div> -->
                                    <div class="row">
                                        <label class="col-md-6">
                                            <strong>fecha desembolso:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.fecha_desembolso}}</p>
                                        <label class="col-md-6">
                                            <strong>fecha inicio cuota:</strong>
                                        </label>
                                        <p class="col-md-6">{{ solicitud.fecha_primera_cuota}}</p>
                                    </div>
                                    <div class="row input-group">
                                        <!-- <label class="col-md-6">
                                            <strong>Estado:</strong>
                                        </label>
                                        <p class="col-md-6">{{ gestionarEstado(solicitud.estado)}}</p> -->
                                        <!-- <div class="input-group"> -->
                                            <h5 class="col-md-12">
                                                <span style="border-radius:5px" :class="gestionarEstado(solicitud.estado)=='Nuevo'?'badge bg-success':gestionarEstado(solicitud.estado)=='Aprobado'?'badge bg-danger':gestionarEstado(solicitud.estado)=='Anulado'?'badge bg-dark':''">Estado: </span>
                                                <span style="border-radius:5px" class="badge text-success">{{ gestionarEstado(solicitud.estado) }} </span>
                                            </h5>
                                        <!-- </div> -->
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                                <!-- <div class="col-md-6 text-start">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="input-group">
                                                    <input :disabled="solicitud.estado==2" class="form-check-input mx-2" type="checkbox" id="checkbox1" v-model="ahorro" @change="cambiarEstado()">
                                                    <label class="form-check-label mx-2" for="checkbox1">
                                                    Ahorro
                                                    </label>
                                                    <input @input="cambiarEstado()" :disabled="!ahorro" class="form-control" type="text" v-model="cantidad_ahorro">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="input-group">
                                                    <input :disabled="solicitud.estado==2" class="form-check-input mx-2" type="checkbox" id="checkbox2" v-model="seguro"  @change="cambiarEstado()">
                                                    <label class="form-check-label mx-2" for="checkbox2">
                                                    Seguro
                                                    </label>
                                                    <input @input="cambiarEstado()" :disabled="!seguro" class="form-control" type="text" v-model="cantidad_seguro">
                                                </div>        
                                            </div>
                                        </div>
                                </div> -->
                                
                                <!-- <div class="col-md-12 text-center mt-2">
                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                        <input @click="generarPlanPagosTasaFija()" v-model="tasa_plan" value="fija" type="radio" class="btn-check btn-sm" name="btnradio" id="btnradio1"
                                            autocomplete="off" >
                                        <label class="btn btn-outline-success btn-sm" for="btnradio1">Tasa fija</label>
        
                                        <input @click="generarPlanPagos()" v-model="tasa_plan" value="amortizable" type="radio" class="btn-check btn-sm" name="btnradio" id="btnradio2"
                                            autocomplete="off" checked>
                                        <label class="btn btn-outline-success btn-sm" for="btnradio2">Tasa Amortizable</label>
                                    </div>
                                </div> -->
                            </div>
                            
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table mb-4 table-bordered table-hover table-sm table-striped">
                                                <thead class="text-white bg-success">
                                                    <tr>
                                                        <th>Nro</th>
                                                        <th>Fecha</th>
                                                        <th>Capital</th>
                                                        <th>Recargo %</th>
                                                        <th>Saldo capital</th>
                                                        <th v-if="ahorro">Ahorro</th>
                                                        <th v-if="seguro">Seguro</th>
                                                        <th>Total Bs</th>
                                                      
                                                    </tr>
                                                </thead>
                                                <tbody>
                          
                                                    <tr class="" v-for="(item, index) in lista_cuotas" :key="index">

                                                        <td>{{ item.nro }}</td>
                                                        <td>{{ formatearFecha(item.fecha)}}</td>
                                                        <td>{{ item.capital }}</td>
                                                        <td>{{ item.interes }}</td>
                                                        <td>{{ item.saldo_capital }}</td>
                                                        <td v-if="ahorro">{{ item.ahorro }}</td>
                                                        <td v-if="seguro">{{ item.seguro }}</td>
                                                        <td>{{ item.total_cuota }}</td>
                                                  
                                                    </tr> 
                                                </tbody> 
                                        </table>
                                        <br>
                                    </div>
                                </div>
                            </div>
                            
                    </div>
    
                    <div class="modal-footer">
                            <button @click="cerrarModalSimulacionPlanPago()" type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">
                                <i class="fas fa-times-circle"></i>
                                Cerrar</button>
                            <!-- <button type="submit" class="btn btn-primary">Guardar</button> -->
                    </div>
                    
                </div>
            </div>
        </div> 



        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalRespaldos" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="miModalLabel">Gestión de respaldos</h5>
                        
                        <button @click="cerrarModalRespaldos()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    
                    <div class="modal-body">
                            <div class="row">
                                <div v-for="(item, index) in items_respaldos" :key="index" class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <input v-model="item.descripcion" type="text" class="form-control" placeholder="Ingrese una descripción">
                                        </div>
                                        <div class="card-body">
                                            <img :src="(item.imagen==null || item.imagen=='')?'img/respaldos/default.png':'img/respaldos/'+item.imagen" class="img-fluid shadow"
                                                alt="Responsive image">
                                        </div>
                                        <div class="card-footer">
                                            <div class="input-group">
                                                <input type="file" class="form-control" @change="seleccionarRespaldo($event, index)">
                                                
                                                <button @click="agregarRespaldo()" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button @click="quitarRespaldo(index)" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                    </div>
    
                    <div class="modal-footer">
                            <button @click="cerrarModalRespaldos()" type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cerrar</button>
                            <!-- <button type="submit" class="btn btn-primary">Guardar</button> -->
                    </div>
                    
                </div>
            </div>
        </div> 

        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalCalculadoraCredito" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width:90%; width:90%">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="miModalLabel">Calculo de cuotas y plan de pago</h5>
                        
                        <button @click="cerrarModalCalculadoraCredito()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Monto credito</label>
                                    <input v-model="solicitud_simulacion.importe_solicitud" class="form-control" type="number">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Lapso capital</label>
                                    <select @change="(tipo_tasa=='fija')? seleccionarLapsoCapital():''" v-model="solicitud_simulacion.lapso_capital" class="form-control"
                                        id="" name="lapso_capital" required>
                                        <option value="0" selected hidden disabled>Seleccione</option>
                                        <option v-for="item in lapso_capitales" :value="item.nombre"
                                            :key="item.nombre">
                                            {{ item.nombre }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nro. Cuotas</label>
                                    <input v-model="solicitud_simulacion.nro_cuotas" class="form-control" type="number">
                                </div>
                            </div>
                 
                        </div>

                        <div class="row mt-3"> 
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="tasa" class="form-label">Tasa %</label>
                                        <input v-model="solicitud_simulacion.tasa" type="text" class="form-control" id="tasa" name="tasa" required
                                        onkeydown="if(event.key==='.'){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="solicitud_simulacion_tipo_tasa" class="form-label">Tipo tasa</label>
                                    <select @change="(tipo_tasa=='fija')?seleccionarLapsoCapital():''" v-model="tipo_tasa" class="form-control"
                                        id="solicitud_simulacion_tipo_tasa" name="solicitud_simulacion_tipo_tasa" required>
                                        <option value="0" selected hidden disabled>Seleccione un tipo</option>
                                        <option value="fija">Tasa fija</option>
                                        <option value="amortizable">Tasa variable</option>
                                    </select>

                                        <!-- <input  v-model="tipo_tasa" type="text" class="form-control" id="tasa" name="tasa" required
                                        onkeydown="if(event.key==='.'){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');"> -->
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_desembolso" class="form-label">Fecha desembolso</label>
                                    <input @input="(tipo_tasa=='fija')?seleccionarLapsoCapital():''" v-model="solicitud_simulacion.fecha_desembolso" type="date" class="form-control border border-success border-1" id="fecha_desembolso" name="fecha_desembolso"
                                    required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_primera_cuota" class="form-label">Fecha primera cuota</label>
                                    <input :disabled="(tipo_tasa=='fija')? true:false" v-model="solicitud_simulacion.fecha_primera_cuota" type="date" class="form-control border border-success border-1" id="fecha_primera_cuota" name="fecha_primera_cuota"
                                    required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button v-if="tipo_tasa=='fija'" @click="generarPlanPagosTasaFijaSimulacion()" class="btn btn-info mx-2">
                                    <i class="fas fa-lock"></i>
                                    Tasa fija
                                </button>

                                <button v-if="tipo_tasa=='amortizable'"  @click="generarPlanPagosSimulacion()" class="btn btn-success">
                                    <i class="fas fa-equals"></i>
                                    Tasa Variable
                                </button>
                                <button @click="imprimirSimulacionCuotas()" class="btn btn-primary ms-2">
                                    <i class="fas fa-print"></i>
                                    Imprimir
                                </button>
                            </div>
                        </div>

                        <div class="row mt-3 text-center">

                                <!-- <div class="col-md-6 text-start mt-1 mb-2">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <input class="form-check-input mx-2" type="checkbox" id="checkbox10" v-model="solicitud_simulacion.ahorro" @change="cambiarEstadoSimulacion()">
                                                <label class="form-check-label mx-2" for="checkbox10">
                                                Ahorro
                                                </label>
                                                <input @input="cambiarEstadoSimulacion()" :disabled="!solicitud_simulacion.ahorro" class="form-control" type="text" v-model="solicitud_simulacion.cantidad_ahorro">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <input class="form-check-input mx-2" type="checkbox" id="checkbox11" v-model="solicitud_simulacion.seguro"  @change="cambiarEstadoSimulacion()">
                                                <label class="form-check-label mx-2" for="checkbox11">
                                                Seguro
                                                </label>
                                                <input @input="cambiarEstadoSimulacion()" :disabled="!solicitud_simulacion.seguro" class="form-control" type="text" v-model="solicitud_simulacion.cantidad_seguro">
                                            </div>        
                                        </div>
                                    </div>
                                </div> -->

                            <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table mb-4 table-striped table-hover table-bordered table-sm" >
                                                <thead class="text-white bg-success">
                                                    <tr>
                                                        <th>Nro</th>
                                                        <th>Fecha</th>
                                                        <th>Capital</th>
                                                        <th>Recargo %</th>
                                                        <th>Saldo capital</th>
                                                        <th v-if="solicitud_simulacion.ahorro">Ahorro</th>
                                                        <th v-if="solicitud_simulacion.seguro">Seguro</th>
                                                        <th>Total Bs</th>
                                                      
                                                    </tr>
                                                </thead>
                                                <tbody>
                          
                                                    <tr class="" v-for="(item, index) in lista_cuotas_simulacion" :key="index">

                                                        <td>{{ item.nro }}</td>
                                                        <td>{{ item.fecha }}</td>
                                                        <td>{{ item.capital }}</td>
                                                        <td>{{ item.interes }}</td>
                                                        <td>{{ (item.saldo_capital==-0)?0:item.saldo_capital }}</td>
                                                        <td v-if="solicitud_simulacion.ahorro">{{ item.ahorro }}</td>
                                                        <td v-if="solicitud_simulacion.seguro">{{ item.seguro }}</td>
                                                        <td>{{ item.total_cuota }}</td>
                                                  
                                                    </tr> 
                                                </tbody> 
                                        </table>
                                        <br>
                                    </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="modal-footer">
                            <button @click="cerrarModalCalculadoraCredito()" type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">
                                <i class="fas fa-times-circle"></i>
                                Cerrar</button>
                            <!-- <button type="submit" class="btn btn-primary">Guardar</button> -->
                    </div>
                    
                </div>
            </div>
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
        props: {
            rolUsuario: {
            type: String,
            required: true,
            },
        },
        data() {
            return {
                tipo_tasa:'amortizable',
                fecha_inicial_buscar:moment().subtract(3, 'months').format('YYYY-MM-DD'),
                fecha_final_buscar:moment().format('YYYY-MM-DD'),
                criterio_estado:'todos',
                preloader:false,
                lista_codeudores_tabla:[],
                criterio:'cliente.nombre',
                buscar:'',
                tasa_plan:'amortizable',
                aprobando_solicitud:false,
                guardando_solicitud:false,
                seguro:false,
                ahorro:false,
                cantidad_ahorro:0,
                cantidad_seguro:0,
                lista_solicitudes:[],
                lista_cuotas:[],
                lista_cuotas_simulacion:[],
                plan_pago_simulacion:[],
                
                items_cliente:[],
                items_cliente_simulacion:[],
                isVisibleCliente:false,
                lista_monedas:[
                    {nombre:'Bolivianos'},
                    {nombre:'Dolares'},
                ],
                lapso_capitales:[
                    {nombre:'Diario'},
                    {nombre:'Semanal'},
                    {nombre:'Quincenal'},
                    {nombre:'Mensual'},
                ],

                tipos_desembolsos:[
                    {nombre:'Efectivo'},
                    {nombre:'Depósito'},
                    {nombre:'Transferencia'},
                    {nombre:'QR'},
                ],

                tipos_garantias:[
                    {nombre:'Personal'},
                    {nombre:'Prendario'},
       
                ],

                solicitud: {
                    id_solicitud:0,
                    importe_solicitud: 0,
                    moneda:'0',
                    lapso_capital:'0',
                    nro_cuotas:0,
                    tasa:0,
                    fecha_desembolso: moment().format('YYYY-MM-DD'),
                    fecha_primera_cuota: moment().format('YYYY-MM-DD'),
                    fecha_inicio_plan_pago: moment().format('YYYY-MM-DD'),
                    destino_prestamo: '',
                    monto_pago_adm:0,
                    estado:'',
                    id_cliente:0,
                    id_codeudor:0,
                    id_usuario:0,
                    tipo_garantia:'0',
                    tipo_desembolso:'0',
                    tipo_tasa:'',
                    lista_codeudores:[],
                    enviado: 0,
                    accion:0,
                    lista_codeudores:[],
                },

                solicitud_simulacion: {
                    
                    importe_solicitud: 0,
                    lapso_capital:'0',
                    nro_cuotas:0,

                    cantidad_ahorro:0,
                    cantidad_seguro:0,
                    ahorro:true,
                    seguro:true,

                    tasa:0,
                    fecha_desembolso: moment().format('YYYY-MM-DD'),
                    fecha_primera_cuota: moment().format('YYYY-MM-DD'),
                
                },
                cliente:{
                    id_cliente:0,
                    idd_cliente:'',
                    nombre:'',
                    ci:0,
                    actividad:'',
                    lugar_expedicion:'',
                    buscar:'',
                },
                cliente_simulacion:{

                },
                
                lista_garantias: [{
                    id_garantia: 0,
                    descripcion: '',
                    
                }],

                lista_garantias_imagenes: [{
                    id_garantia: 0,
                    descripcion: '',
                    lista_imagenes:[
                    {id_imagen:0,
                    id_garantia:0,
                    imagen:'',
                    imagen_file:null,
                    }
                    ],
                    
                }],
                // lista_telefonos: [{
                //     id_telefono: 0,
                //     tipo: '',
                //     numero: '',
                //     observacion: '',

                // }],

                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 2,

                items_respaldos:[],

                respaldo:{
                    id_respaldo:0,
                    id_solicitud:0,
                    descripcion:'',
                    imagen:null,
                },
                
                mensajeError: '',
                lista_codeudores:[
                    {
                        select_codeudor:{
                            isVisibleCodeudor:false,
                            codeudor:{
                                id_codeudor:0,
                                idd_codeudor:'',
                                nombre:'',
                                ci:0,
                                lugar_expedicion:'',
                                buscar:'',
                                items_codeudor:[],
                                actividad:'',

                            },
                        },
                    }
                ],

                lista_codeudores_enviar:[],

                items_codeudores_solicitud:[],

                lista_codeudores_editar:[],

            }
        },
        computed:{
            filteredItemsCliente() {
                const searchTermLower = this.cliente.idd_cliente.toLowerCase();
                return this.items_cliente.filter(item => {
                    const nombreLower = item.nombre.toLowerCase();
                    const ciLower = item.ci.toLowerCase();
                    return nombreLower.includes(searchTermLower) || ciLower.includes(searchTermLower);
                });
                
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
        },

        methods: {
            
            seleccionarLapsoCapitalFormulario(){
                if(this.solicitud.lapso_capital=='Mensual'){
                    const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                    const fechaPrimeraCuota = fechaDesembolso.add(1, 'month');
                  
                    this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                }else{
                    if(this.solicitud.lapso_capital=='Quincenal'){
                        const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                        const fechaPrimeraCuota = fechaDesembolso.add(15, 'days');
                        this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                    }else{
                        if(this.solicitud.lapso_capital=='Semanal'){
                            const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                            const fechaPrimeraCuota = fechaDesembolso.add(7, 'days');
                            this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                        }else{
                            if(this.solicitud.lapso_capital=='Diario'){
                                const fechaDesembolso = moment(this.solicitud.fecha_desembolso);
                                const fechaPrimeraCuota = fechaDesembolso.add(1, 'days');
                                this.solicitud.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                            }
                        }
                    }
                }
            },
            seleccionarLapsoCapital(){
                if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                    const fechaDesembolso = moment(this.solicitud_simulacion.fecha_desembolso);
                    const fechaPrimeraCuota = fechaDesembolso.add(1, 'month');
                    this.solicitud_simulacion.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                }else{
                    if(this.solicitud_simulacion.lapso_capital=='Quincenal'){
                        const fechaDesembolso = moment(this.solicitud_simulacion.fecha_desembolso);
                        const fechaPrimeraCuota = fechaDesembolso.add(15, 'days');
                        this.solicitud_simulacion.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                    }else{
                        if(this.solicitud_simulacion.lapso_capital=='Semanal'){
                            const fechaDesembolso = moment(this.solicitud_simulacion.fecha_desembolso);
                            const fechaPrimeraCuota = fechaDesembolso.add(7, 'days');
                            this.solicitud_simulacion.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                        }else{
                            if(this.solicitud_simulacion.lapso_capital=='Diario'){
                                const fechaDesembolso = moment(this.solicitud_simulacion.fecha_desembolso);
                                const fechaPrimeraCuota = fechaDesembolso.add(1, 'days');
                                this.solicitud_simulacion.fecha_primera_cuota = fechaPrimeraCuota.format('YYYY-MM-DD');
                            }
                        }
                    }
                }
            },
            async obtenerCodeudores(){
                const url='/get_codeudores_sin';
           
                await axios.get(url).then((response)=>{
                    console.log(response.data);
                    this.lista_codeudores_editar=response.data;
                    //me.items_cliente_simulacion=response.data;
                })
                .catch(function(error){
                    console.log(error);
                })
            },
            formatearFecha(fecha){
                return moment(fecha).format('DD/MM/YYYY');
            },
            deleteCodeudor(index){
                this.lista_codeudores.splice(index, 1);
                //this.lista_codeudores_enviar.splice(index, 1)
            },
            addCodeudor(){
                const nuevo_index=this.lista_codeudores.push(
                    {
                        select_codeudor:{
                            isVisibleCodeudor:false,
                            codeudor:{
                                id_codeudor:0,
                                idd_codeudor:'',
                                nombre:'',
                                ci:0,
                                lugar_expedicion:'',
                                actividad:'',
                                buscar:'',
                                items_codeudor:[],
                            },
                        },
                    }
                )-1;

                
                this.getCodeudores(nuevo_index);
               
            },
            filteredItemsCodeudor(index) {
                const searchTermLowerCodeudor = this.lista_codeudores[index].select_codeudor.codeudor.idd_codeudor.toLowerCase();
                return this.lista_codeudores[index].select_codeudor.codeudor.items_codeudor.filter(item => {
                    const nombreCodeudorLower = item.nombre.toLowerCase();
                    const ciCodeudorLower = item.ci.toLowerCase();
                    return nombreCodeudorLower.includes(searchTermLowerCodeudor) || ciCodeudorLower.includes(searchTermLowerCodeudor);
                });
              
            },
            imprimirSimulacionCuotas(){
                // Muestra un mensaje de carga con SweetAlert2
                if(this.lista_cuotas_simulacion.length==0){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Primero debe generar las cuotas Tasa fija/Amortizable',
                        timer:1500,
                    });
                }else{
                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Generando el reporte, por favor espera.',
                        allowOutsideClick: false,
                        onBeforeOpen: () => {
                            Swal.showLoading();
                        }
                    });
        
                    const url = '/imprimir_cuotas_simulacion';
                    const data = {
                       detalles:JSON.stringify(this.lista_cuotas_simulacion),
                       importe_solicitud:this.solicitud_simulacion.importe_solicitud,
                       lapso_capital:this.solicitud_simulacion.lapso_capital,
                       nro_cuotas:this.solicitud_simulacion.nro_cuotas,
                       tasa:this.solicitud_simulacion.tasa,
                       fecha_desembolso:this.solicitud_simulacion.fecha_desembolso,
                       fecha_primera_cuota:this.solicitud_simulacion.fecha_primera_cuota,
                    };
        
                        
                        axios.post(url, data, { responseType: 'blob' })
                        .then(response => {
                            const blob = new Blob([response.data], { type: 'application/pdf' });
                            const url = window.URL.createObjectURL(blob);
        
                            // Abre la URL del archivo en una nueva ventana o pestaña
                            window.open(url, '_blank');
        
                            // Cierra el mensaje de carga con SweetAlert2
                            Swal.close();
                        })
                        .catch(error => {
                            console.error('Error al generar el reporte', error);
        
                            // Muestra un mensaje de error con SweetAlert2
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un error al generar el reporte. Por favor, inténtalo de nuevo.',
                            });
                        });
                }
                
            },
            /*generarPlanPagosSimulacion(){
                if(this.solicitud_simulacion.importe_solicitud==0 || this.solicitud_simulacion.nro_cuotas==0 || this.solicitud_simulacion.tasa==0
                || this.solicitud_simulacion.lapso_capital=='0'){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia.!!!',
                        text: 'Faltan datos por ingresar.!!!',
                        showConfirmButton: true,
                        confirmButtonText:'Aceptar',
                       
                    });
                }else{
                    // if(this.lista_cuotas.length==0){
                        this.lista_cuotas_simulacion=[];
                        let monto_total=this.solicitud_simulacion.importe_solicitud;
        
                        const cuota_sin_interes = Math.ceil(monto_total/this.solicitud_simulacion.nro_cuotas);
                        let contador=1;
                        const fechaMoment = moment(this.solicitud_simulacion.fecha_primera_cuota);
                        let fecha_cuota = fechaMoment.clone(); // Clonamos la fecha para no modificar la original
                        let cantidad_dias=this.retornarDiasPlanPago(this.solicitud_simulacion.lapso_capital);
                        //
                            // let fecha_cuota_aux=moment();
                            // // Obtener el día de la fecha
                            // let dia_aux = fecha_cuota_aux.date();
                            // // Puedes usar la variable 'dia' en tu código
                            // console.log('Día:', dia_aux);
                            // let fecha_inicio_aux=fecha_cuota_aux;
                            // // Agregar un mes y establecer el día en el mismo día del mes siguiente
    
                            // fecha_cuota_aux.add(1, 'months').date(dia_aux);
    
                            // // Obtener la diferencia en días
                            // let diferenciaDias_aux = fecha_cuota_aux.diff(fecha_inicio_aux, 'days');
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                // Obtener el día de la fecha
                                let dia = fecha_cuota.date();
                                // Puedes usar la variable 'dia' en tu código
                                console.log('Día:', dia);
                                let fecha_inicio=fecha_cuota;
                                // Agregar un mes y establecer el día en el mismo día del mes siguiente
                                fecha_cuota.add(1, 'months').date(dia);
    
                                // Obtener la diferencia en días
                                //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                            }else{
                                fecha_cuota.add(cantidad_dias, 'days');
                            }
    
                        //
                        while(monto_total>0){
                            this.lista_cuotas_simulacion.push({
                                nro:contador,
                                fecha:fecha_cuota.format('YYYY-MM-DD'),
                                capital:Math.round(parseFloat(parseFloat(Math.ceil(cuota_sin_interes)).toFixed(2))),
                                // interes:parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2),
                                interes:Math.round(parseFloat((this.solicitud_simulacion.lapso_capital=='Mensual')?parseFloat(Math.ceil(monto_total*(this.solicitud_simulacion.tasa/100))).toFixed(2):((parseFloat(Math.ceil(monto_total*(this.solicitud_simulacion.tasa/100)))/30)*cantidad_dias).toFixed(2))),
                                saldo_capital:Math.round(parseFloat(parseFloat(Math.ceil(monto_total-cuota_sin_interes)).toFixed(2))),
                                ahorro:this.cantidad_ahorro,
                                seguro:this.cantidad_seguro,
                                total_cuota:Math.round(parseFloat(parseFloat(Math.ceil(parseFloat(this.cantidad_ahorro)+parseFloat(this.cantidad_seguro)+cuota_sin_interes+(monto_total*(this.solicitud_simulacion.tasa/100)))).toFixed(2))),
                            });
                            contador=parseInt(parseInt(contador)+1);
                            monto_total=monto_total-cuota_sin_interes;
                            // Agregar 30 días a la fecha actual
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                // Obtener el día de la fecha
                                let dia = fecha_cuota.date();
                                // Puedes usar la variable 'dia' en tu código
                                console.log('Día:', dia);
                                let fecha_inicio=fecha_cuota;
                                // Agregar un mes y establecer el día en el mismo día del mes siguiente
                                fecha_cuota.add(1, 'months').date(dia);
    
                                // Obtener la diferencia en días
                                //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                            }else{
                                fecha_cuota.add(cantidad_dias, 'days');
                            }
                        }
    
                    // }else{
                    //     console.log('plan de pagos ya generado');
                    // }
                    // condicion para cuando se genera una fila de demás
                    if(this.lista_cuotas_simulacion[this.lista_cuotas_simulacion.length-1].saldo_capital<0){
                        this.lista_cuotas_simulacion[this.lista_cuotas_simulacion.length-1].saldo_capital=0;
                    }
                }
            },*/
            generarPlanPagosSimulacion(){
                if(this.solicitud_simulacion.importe_solicitud==0 || this.solicitud_simulacion.nro_cuotas==0 || this.solicitud_simulacion.tasa==0
                || this.solicitud_simulacion.lapso_capital=='0'){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia.!!!',
                        text: 'Faltan datos por ingresar.!!!',
                        showConfirmButton: true,
                        confirmButtonText:'Aceptar',
                       
                    });
                }else{
       
                    //     this.lista_cuotas_simulacion=[];
                    //     let monto_total=this.solicitud_simulacion.importe_solicitud;
        
                    //     const cuota_sin_interes = Math.ceil(monto_total/this.solicitud_simulacion.nro_cuotas);
                    //     let contador=1;



                    //     const fechaMoment = moment(this.solicitud_simulacion.fecha_primera_cuota);
                    //     const fechaMomentDesembolso = moment(this.solicitud_simulacion.fecha_desembolso); // fecha inicio

                    //     const diaAux_aux = fechaMoment.date();
                        

                    //     fechaMoment.subtract(1, 'months').date(diaAux_aux);
                    //     this.solicitud_simulacion.fecha_inicio_plan_pago=fechaMoment.format('YYYY-MM-DD');
                    //     let fecha_cuota = fechaMoment.clone(); // Clonamos la fecha para no modificar la original

                    //     let cantidad_dias=this.retornarDiasPlanPago(this.solicitud_simulacion.lapso_capital);

                    //     let diferenciaDias = fecha_cuota.diff(fechaMomentDesembolso, 'days');
                   
                    //     const diaAux = fecha_cuota.date();
                    //     let fechaMomentProximaCuota = fechaMoment.clone();
                    
                    //     fechaMomentProximaCuota.add(1, 'months').date(diaAux);
                    //     let diferenciaDiasFechas = fechaMomentProximaCuota.diff(fecha_cuota, 'days');

                    //     console.log('diferencias dias', diferenciaDiasFechas);
                    //     fecha_cuota.add(1, 'months').date(diaAux);

                        
                    

                    //     let interesPagar=((parseFloat(Math.ceil(monto_total*(this.solicitud_simulacion.tasa/100)))/30)*diferenciaDiasFechas).toFixed(0);

                    //     while(monto_total>0){
                    //         let interesDespuesPrimerCuota=parseFloat(Math.ceil(monto_total*(this.solicitud_simulacion.tasa/100))).toFixed(2);
                    //         let interesInicial= parseFloat(contador==1 && diferenciaDias>0? ((parseFloat(Math.ceil(monto_total*(this.solicitud_simulacion.tasa/100)))/30)*diferenciaDias).toFixed(0):0);
                    //         let interesNoMensual=((parseFloat(Math.ceil(monto_total*(this.solicitud_simulacion.tasa/100)))/30)*cantidad_dias).toFixed(2);
                    //         console.log(interesPagar);
                    //         this.lista_cuotas_simulacion.push({
                    //             nro:contador,
                    //             fecha:fecha_cuota.format('YYYY-MM-DD'),
                    //             capital:Math.round(parseFloat(parseFloat(Math.ceil(cuota_sin_interes)).toFixed(2))),
                    //             // interes:parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2),
                    //             // interes:Math.round(parseFloat((this.solicitud.lapso_capital=='Mensual')?parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2 ): ((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*cantidad_dias).toFixed(2))) + parseFloat(contador==1 && diferenciaDias>0? ((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*diferenciaDias).toFixed(0):0),
                    //             interes:Math.round(parseFloat((this.solicitud_simulacion.lapso_capital=='Mensual')?((contador==1)?  interesPagar  : interesDespuesPrimerCuota ): interesNoMensual)) + interesInicial,

                    //             saldo_capital:Math.round(parseFloat(parseFloat(Math.ceil(monto_total-cuota_sin_interes)).toFixed(2))),
                    //             ahorro:this.cantidad_ahorro,
                    //             seguro:this.cantidad_seguro,
                    //             total_cuota:Math.round(parseFloat(parseFloat(Math.ceil(parseFloat(this.cantidad_ahorro)+parseFloat(this.cantidad_seguro)+cuota_sin_interes+  Math.round(parseFloat((this.solicitud_simulacion.lapso_capital=='Mensual')?((contador==1)?  interesPagar  : interesDespuesPrimerCuota ): interesNoMensual))     )))) + interesInicial,
                    //         });
                            
                    //         contador=parseInt(parseInt(contador)+1);
                    //         monto_total=monto_total-cuota_sin_interes;
                    //         // Agregar 30 días a la fecha actual
                    //         if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                    //             // Obtener el día de la fecha
                    //             let dia = fecha_cuota.date();
                    //             // Puedes usar la variable 'dia' en tu código
                    //             console.log('Día:', dia);
                    //             let fecha_inicio=fecha_cuota;
                    //             // Agregar un mes y establecer el día en el mismo día del mes siguiente
                    //             fecha_cuota.add(1, 'months').date(dia);

                    //             // Obtener la diferencia en días
                    //             //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                    //         }else{
                    //             fecha_cuota.add(cantidad_dias, 'days');
                    //         }
                    //     }

                    // if(this.lista_cuotas_simulacion[this.lista_cuotas_simulacion.length-1].saldo_capital<0){
                    //     this.lista_cuotas_simulacion[this.lista_cuotas_simulacion.length-1].saldo_capital=0;
                    // }




                    // Nuevo metodo calculo de cuotas

                    this.lista_cuotas_simulacion=[];// inicia array vacio
                    let contador=1;

                    var fecha_inicio=moment(this.solicitud_simulacion.fecha_desembolso);
                    var fecha_final=moment(this.solicitud_simulacion.fecha_primera_cuota);

                    let capital_aux=this.solicitud_simulacion.importe_solicitud/this.solicitud_simulacion.nro_cuotas;
                    let saldo_capital_aux=this.solicitud_simulacion.importe_solicitud;


                    while(contador<=this.solicitud_simulacion.nro_cuotas){

                        let dias_inicio_fin =fecha_final.diff(fecha_inicio, 'days');

                        console.log('dias diferencia:', dias_inicio_fin);

                        // let interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/((contador==1 && this.solicitud_simulacion.lapso_capital=='Mensual')?fecha_inicio.daysInMonth():(contador==1 && this.solicitud_simulacion.lapso_capital=='Quincenal')?15:(contador==1 && this.solicitud_simulacion.lapso_capital=='Semanal')?7:(contador==1 && this.solicitud_simulacion.lapso_capital=='Diario')?1:dias_inicio_fin);
                        let interes=0;
                        if(contador==1){
                            interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/fecha_inicio.daysInMonth();
                        }else{
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/dias_inicio_fin;
                            }else{
                                interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/fecha_inicio.daysInMonth();
                            }
                        }


                        saldo_capital_aux=saldo_capital_aux - capital_aux;


                        this.lista_cuotas_simulacion.push({
                            nro:contador,
                            fecha:fecha_final.format('YYYY-MM-DD'),
                            capital:parseFloat(capital_aux).toFixed(0),
                            // interes:parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2),
                            // interes:Math.round(parseFloat((this.solicitud.lapso_capital=='Mensual')?parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2 ): ((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*cantidad_dias).toFixed(2))) + parseFloat(contador==1 && diferenciaDias>0? ((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*diferenciaDias).toFixed(0):0),
                            interes:parseFloat(interes*dias_inicio_fin).toFixed(0),

                            saldo_capital:parseFloat(saldo_capital_aux).toFixed(0),
                            ahorro:parseFloat(this.solicitud_simulacion.cantidad_ahorro==null || this.solicitud_simulacion.cantidad_ahorro==''?0:this.solicitud_simulacion.cantidad_ahorro).toFixed(0),
                            seguro:parseFloat(this.solicitud_simulacion.cantidad_seguro==null || this.solicitud_simulacion.cantidad_seguro==''?0:this.solicitud_simulacion.cantidad_seguro).toFixed(0),
                            // total_cuota:parseFloat(parseFloat(capital_aux) + parseFloat(interes*dias_inicio_fin) + (this.solicitud_simulacion.ahorro?parseFloat(this.solicitud_simulacion.cantidad_ahorro):0) + (this.solicitud_simulacion.seguro?parseFloat(this.solicitud_simulacion.cantidad_seguro):0)).toFixed(0),
                            total_cuota: (() => {
                                const capital = parseFloat(capital_aux);
                                const interesTotal = parseFloat(interes * dias_inicio_fin);
                                const ahorro = this.solicitud_simulacion.ahorro ? parseFloat(this.solicitud_simulacion.cantidad_ahorro || 0) : 0;
                                const seguro = this.solicitud_simulacion.seguro ? parseFloat(this.solicitud_simulacion.cantidad_seguro || 0) : 0;

                                return (capital + interesTotal + ahorro + seguro).toFixed(0);
                            })(),
                        });
                        // console.log('fecha inicio', fecha_inicio);
                        // console.log('fecha final', fecha_final);
                        fecha_inicio=fecha_final;
                        let aux_fecha_final=fecha_final;
                        if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                            fecha_final=moment(aux_fecha_final).add(1, 'month');
                        }else{
                            if(this.solicitud_simulacion.lapso_capital=='Quincenal'){
                                fecha_final=moment(aux_fecha_final).add(15, 'days');
                            }else{
                                if(this.solicitud_simulacion.lapso_capital=='Semanal'){
                                    fecha_final=moment(aux_fecha_final).add(1, 'week');
                                }else{
                                    if(this.solicitud_simulacion.lapso_capital=='Diario'){
                                        fecha_final=moment(aux_fecha_final).add(1, 'day');

                                    }
                                }
                            }
                        }
                        contador++;
                    }
                }
            },
            generarPlanPagosTasaFijaSimulacion(){
                // if(this.lista_cuotas.length==0){
                if(this.solicitud_simulacion.importe_solicitud==0 || this.solicitud_simulacion.nro_cuotas==0 || this.solicitud_simulacion.tasa==0
                || this.solicitud_simulacion.lapso_capital=='0'){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia.!!!',
                        text: 'Faltan datos por ingresar.!!!',
                        showConfirmButton: true,
                        confirmButtonText:'Aceptar',
                       
                    });
                }else{

                        /*// Inicia array vacio
                        this.lista_cuotas_simulacion=[];
                        // Monto total prestamo
                        let monto_total=parseFloat(this.solicitud_simulacion.importe_solicitud);
                        // cant. dias | Mensual, Quincenal, Semanal, Dia
                        let cantidad_dias=this.retornarDiasPlanPago(this.solicitud_simulacion.lapso_capital);

                        
                        console.log('cantidad dias: ', cantidad_dias);

                        let tasa_pago = (this.solicitud_simulacion.lapso_capital=='Mensual')? parseFloat(this.solicitud_simulacion.tasa/100):parseFloat(((this.solicitud_simulacion.tasa/100)/30)*cantidad_dias);
                        // let tasa_fija = (tasa_pago/this.solicitud.nro_cuotas);
                        let tasa_fija = tasa_pago;
                        
                        let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas));
                        let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas) - 1);

                        let cuota_tasa_fija = parseFloat(monto_total*(numerador_formula/denominador_formula));

                        console.log('Cuota tasa fija', cuota_tasa_fija);
                        
        
                        const cuota_sin_interes = Math.ceil(monto_total/this.solicitud_simulacion.nro_cuotas);

                        let contador=1;
                        // MODIFICACION 19.02.24
                        // const fechaMoment = moment(this.solicitud_simulacion.fecha_primera_cuota);
                        // let fecha_cuota = fechaMoment.clone(); // Clonamos la fecha para no modificar la original

                        // MODIFICACION 19.02.24
                        const fechaMoment = moment(this.solicitud_simulacion.fecha_primera_cuota);
                        const fechaMomentDesembolso = moment(this.solicitud_simulacion.fecha_desembolso); // fecha inicio
                        const diaAux_aux = fechaMoment.date();// dia de la primera cuota

                        fechaMoment.subtract(1, 'months').date(diaAux_aux);

                        let fecha_cuota = fechaMoment.clone(); // Clonamos la fecha para no modificar la original
                        let diferenciaDias = fecha_cuota.diff(fechaMomentDesembolso, 'days');

                        const diaAux = fecha_cuota.date();
                        let fechaMomentProximaCuota = fechaMoment.clone();
                    
                        fechaMomentProximaCuota.add(1, 'months').date(diaAux);
                        let diferenciaDiasFechas = fechaMomentProximaCuota.diff(fecha_cuota, 'days');

                        console.log('diferencias dias', diferenciaDiasFechas);

                        //
                            // let fecha_cuota_aux=fecha_cuota;
                            // // Obtener el día de la fecha
                            // let dia_aux = fecha_cuota_aux.date();
                            // // Puedes usar la variable 'dia' en tu código
                            // console.log('Día:', dia_aux);
                            // let fecha_inicio_aux=fecha_cuota_aux;
                            // // Agregar un mes y establecer el día en el mismo día del mes siguiente
                            // fecha_cuota_aux.add(1, 'months').date(dia_aux);

                            // // Obtener la diferencia en días
                            // let diferenciaDias_aux = fecha_cuota_aux.diff(fecha_inicio_aux, 'days');
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                // Obtener el día de la fecha
                                let dia = fecha_cuota.date();
                                // Puedes usar la variable 'dia' en tu código
                                console.log('Día:', dia);
                                let fecha_inicio=fecha_cuota;
                                // Agregar un mes y establecer el día en el mismo día del mes siguiente
                                fecha_cuota.add(1, 'months').date(dia);

                                // Obtener la diferencia en días
                                //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                            }else{
                                fecha_cuota.add(cantidad_dias, 'days');
                            }
                        //
                        while(monto_total>0){
                            // let interes_cuota=(this.solicitud.lapso_capital=='Mensual')?parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2):((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*cantidad_dias).toFixed(2);
                            let interes_cuota=(this.solicitud_simulacion.lapso_capital=='Mensual')?parseFloat(monto_total*(this.solicitud_simulacion.tasa/100)):((parseFloat(monto_total*(this.solicitud_simulacion.tasa/100))/30)*cantidad_dias);

                            // let abono_capital_cuota=parseFloat(Math.ceil(cuota_tasa_fija))-parseFloat(Math.ceil(interes_cuota));
                            let abono_capital_cuota=parseFloat(cuota_tasa_fija)-parseFloat(interes_cuota);
                            this.lista_cuotas_simulacion.push({
                                nro:contador,
                                fecha:fecha_cuota.format('YYYY-MM-DD'),
                                // interes:parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2),
                                interes:Math.round(parseFloat(interes_cuota.toFixed(2))),
                                // abono_capital:parseFloat(Math.ceil(cuota_sin_interes)).toFixed(2),
                                capital:Math.round(parseFloat(abono_capital_cuota.toFixed(2))),
                                // saldo_capital:parseFloat(Math.ceil(monto_total-cuota_tasa_fija)).toFixed(2),
                                saldo_capital:Math.round(parseFloat(parseFloat(monto_total-abono_capital_cuota).toFixed(2))),
                                ahorro:parseFloat(this.solicitud_simulacion.cantidad_ahorro==null || this.solicitud_simulacion.cantidad_ahorro==''?0:this.solicitud_simulacion.cantidad_ahorro).toFixed(0),
                                seguro:parseFloat(this.solicitud_simulacion.cantidad_seguro==null || this.solicitud_simulacion.cantidad_seguro==''?0:this.solicitud_simulacion.cantidad_seguro).toFixed(0),
                                // total_cuota:parseFloat(Math.ceil(parseFloat(this.cantidad_ahorro)+parseFloat(this.cantidad_seguro)+cuota_sin_interes+(monto_total*(this.solicitud.tasa/100)))).toFixed(2),
                                total_cuota:Math.round(parseFloat(parseFloat(cuota_tasa_fija) + (this.solicitud_simulacion.ahorro?parseFloat(this.solicitud_simulacion.cantidad_ahorro==null || this.solicitud_simulacion.cantidad_ahorro==''?0:this.solicitud_simulacion.cantidad_ahorro):0) + (this.solicitud_simulacion.seguro?parseFloat(this.solicitud_simulacion.cantidad_seguro==null || this.solicitud_simulacion.cantidad_seguro==''?0:this.solicitud_simulacion.cantidad_seguro):0)).toFixed(2)),
                            })
                            contador=parseInt(parseInt(contador)+1);
                            monto_total=parseFloat(monto_total-abono_capital_cuota).toFixed(2);
                            // Agregar 30 días a la fecha actual
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                // Obtener el día de la fecha
                                let dia = fecha_cuota.date();
                                // Puedes usar la variable 'dia' en tu código
                                console.log('Día:', dia);
                                let fecha_inicio=fecha_cuota;
                                // Agregar un mes y establecer el día en el mismo día del mes siguiente
                                fecha_cuota.add(1, 'months').date(dia);

                                // Obtener la diferencia en días
                                //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                            }else{
                                fecha_cuota.add(cantidad_dias, 'days');
                            }
                        }
                    // }else{
                    //     console.log('plan de pagos ya generado');
                    // }
                    if(this.lista_cuotas_simulacion.length>this.solicitud_simulacion.nro_cuotas){
                        this.lista_cuotas_simulacion.pop();
                    }*/

                    this.lista_cuotas_simulacion=[];
                    let monto_total=parseFloat(this.solicitud_simulacion.importe_solicitud);
                    let contador=1;
                    let fecha_inicio=moment(this.solicitud_simulacion.fecha_desembolso);
                    let fecha_final=moment(this.solicitud_simulacion.fecha_primera_cuota);
                    
                    let saldo_capital_aux=this.solicitud_simulacion.importe_solicitud;
                    //let cantidad_dias=this.retornarDiasPlanPago(this.solicitud_simulacion.lapso_capital);


                    while(contador<=this.solicitud_simulacion.nro_cuotas){
                            let dias_inicio_fin = fecha_final.diff(fecha_inicio, 'days');
                            let cuotaTasaFija=0;
                            let interes=0;

                            if(contador==1){
           
                                // let tasa_pago = (this.solicitud_simulacion.lapso_capital=='Mensual')? parseFloat(this.solicitud_simulacion.tasa/100) : parseFloat(((this.solicitud_simulacion.tasa/100)/30)*dias_inicio_fin);
                                let tasa_pago = (this.solicitud_simulacion.lapso_capital=='Mensual')? 
                                   parseFloat(((this.solicitud_simulacion.tasa/100))) : 
                                   parseFloat(((this.solicitud_simulacion.tasa/100)/30)*dias_inicio_fin);

                                let tasa_fija = tasa_pago;

                                let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas));
                                let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas) - 1);

                                cuotaTasaFija  = parseFloat(monto_total*(numerador_formula/denominador_formula));
                                //let interes_cuota=(this.solicitud_simulacion.lapso_capital=='Mensual')?parseFloat(monto_total*(this.solicitud_simulacion.tasa/100)):((parseFloat(monto_total*(this.solicitud_simulacion.tasa/100))/30)*cantidad_dias);

                                // interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/dias_inicio_fin;
                                interes= (this.solicitud_simulacion.lapso_capital=='Mensual')? (parseFloat(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/dias_inicio_fin):(parseFloat(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/30);

                                console.log('Cuota tasa fija', cuotaTasaFija);
                                
                            }else{
                                if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                    let tasa_pago = parseFloat(this.solicitud_simulacion.tasa/100);
                                
                                    let tasa_fija = tasa_pago;

                                    let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas));
                                    let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas) - 1);

                                    cuotaTasaFija  = parseFloat(monto_total*(numerador_formula/denominador_formula));
                                    // interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/dias_inicio_fin;

                                    interes=parseFloat(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/dias_inicio_fin;

                                    console.log('Cuota tasa fija', cuotaTasaFija);
                                }else{
                                    // interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/fecha_inicio.daysInMonth();
                                    let tasa_pago = parseFloat(((this.solicitud_simulacion.tasa/100)/30) * dias_inicio_fin);
                                    let tasa_fija = tasa_pago;

                                    let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas));
                                    let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas) - 1);

                                    cuotaTasaFija  = parseFloat(monto_total*(numerador_formula/denominador_formula));
                                    interes=parseFloat(parseFloat(saldo_capital_aux*(this.solicitud_simulacion.tasa/100)))/30;
                                    
                                    console.log('Cuota tasa fija', cuotaTasaFija);  
                                }
                            }

                            let abono_capital_cuota=parseFloat(cuotaTasaFija)-parseFloat(interes * dias_inicio_fin);
                            console.log('dias inicio - fin: ', dias_inicio_fin);

                            this.lista_cuotas_simulacion.push({
                                nro:contador,
                                fecha:fecha_final.format('YYYY-MM-DD'),
                                interes:Math.round(parseFloat(interes*dias_inicio_fin)),
                                capital:Math.round(parseFloat(abono_capital_cuota)),
                                saldo_capital:Math.round(parseFloat(parseFloat(saldo_capital_aux-abono_capital_cuota))),
                                ahorro:parseFloat(this.solicitud_simulacion.cantidad_ahorro==null || this.solicitud_simulacion.cantidad_ahorro==''?0:this.solicitud_simulacion.cantidad_ahorro).toFixed(0),
                                seguro:parseFloat(this.solicitud_simulacion.cantidad_seguro==null || this.solicitud_simulacion.cantidad_seguro==''?0:this.solicitud_simulacion.cantidad_seguro).toFixed(0),

                                // total_cuota:parseFloat(cuotaTasaFija).toFixed(0),
                                total_cuota: (() => {
                                    const cuotaBase = parseFloat(cuotaTasaFija);
                                    const ahorroMonto = this.solicitud_simulacion.ahorro ? parseFloat(this.solicitud_simulacion.cantidad_ahorro || 0) : 0;
                                    const seguroMonto = this.solicitud_simulacion.seguro ? parseFloat(this.solicitud_simulacion.cantidad_seguro || 0) : 0;
                                    const montoAdicional = ahorroMonto + seguroMonto;
                                    
                                    return (cuotaBase + montoAdicional).toFixed(0);
                                })(),
                            });

                            saldo_capital_aux=parseFloat(saldo_capital_aux-abono_capital_cuota).toFixed(2);
                        
                            fecha_inicio = fecha_final;
                            let aux_fecha_final = fecha_final;

                            if (this.solicitud_simulacion.lapso_capital == 'Mensual') {
                                fecha_final = moment(aux_fecha_final).add(1, 'month');
                            } else {
                                if (this.solicitud_simulacion.lapso_capital == 'Quincenal') {
                                    fecha_final = moment(aux_fecha_final).add(15, 'days');
                                } else {
                                    if (this.solicitud_simulacion.lapso_capital == 'Semanal') {
                                        fecha_final = moment(aux_fecha_final).add(1, 'week');
                                    } else {
                                        if (this.solicitud_simulacion.lapso_capital == 'Diario') {
                                            fecha_final = moment(aux_fecha_final).add(1, 'day');
                                        }
                                    }
                                }
                            }
                            contador++;
                    }

                }
            },

           
            /*generarPlanPagosTasaFijaSimulacion(){
                // if(this.lista_cuotas.length==0){
                if(this.solicitud_simulacion.importe_solicitud==0 || this.solicitud_simulacion.nro_cuotas==0 || this.solicitud_simulacion.tasa==0
                || this.solicitud_simulacion.lapso_capital=='0'){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia.!!!',
                        text: 'Faltan datos por ingresar.!!!',
                        showConfirmButton: true,
                        confirmButtonText:'Aceptar',
                       
                    });
                }else{

                        this.lista_cuotas_simulacion=[];
                        let monto_total=parseFloat(this.solicitud_simulacion.importe_solicitud);
                        let cantidad_dias=this.retornarDiasPlanPago(this.solicitud_simulacion.lapso_capital);
                        let tasa_pago = (this.solicitud_simulacion.lapso_capital=='Mensual')? parseFloat(this.solicitud_simulacion.tasa/100):parseFloat(((this.solicitud_simulacion.tasa/100)/30)*cantidad_dias);
                        // let tasa_fija = (tasa_pago/this.solicitud.nro_cuotas);
                        let tasa_fija = tasa_pago;
                        let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas));
                        let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud_simulacion.nro_cuotas) - 1);

                        let cuota_tasa_fija = parseFloat(monto_total*(numerador_formula/denominador_formula));
                        
        
                        const cuota_sin_interes = Math.ceil(monto_total/this.solicitud_simulacion.nro_cuotas);

                        let contador=1;
                        const fechaMoment = moment(this.solicitud_simulacion.fecha_primera_cuota);
                        let fecha_cuota = fechaMoment.clone(); // Clonamos la fecha para no modificar la original
                        //
                            // let fecha_cuota_aux=fecha_cuota;
                            // // Obtener el día de la fecha
                            // let dia_aux = fecha_cuota_aux.date();
                            // // Puedes usar la variable 'dia' en tu código
                            // console.log('Día:', dia_aux);
                            // let fecha_inicio_aux=fecha_cuota_aux;
                            // // Agregar un mes y establecer el día en el mismo día del mes siguiente
                            // fecha_cuota_aux.add(1, 'months').date(dia_aux);

                            // // Obtener la diferencia en días
                            // let diferenciaDias_aux = fecha_cuota_aux.diff(fecha_inicio_aux, 'days');
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                // Obtener el día de la fecha
                                let dia = fecha_cuota.date();
                                // Puedes usar la variable 'dia' en tu código
                                console.log('Día:', dia);
                                let fecha_inicio=fecha_cuota;
                                // Agregar un mes y establecer el día en el mismo día del mes siguiente
                                fecha_cuota.add(1, 'months').date(dia);

                                // Obtener la diferencia en días
                                //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                            }else{
                                fecha_cuota.add(cantidad_dias, 'days');
                            }
                        //
                        while(monto_total>0){
                            // let interes_cuota=(this.solicitud.lapso_capital=='Mensual')?parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2):((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*cantidad_dias).toFixed(2);
                            let interes_cuota=(this.solicitud_simulacion.lapso_capital=='Mensual')?parseFloat(monto_total*(this.solicitud_simulacion.tasa/100)):((parseFloat(monto_total*(this.solicitud_simulacion.tasa/100))/30)*cantidad_dias);

                            // let abono_capital_cuota=parseFloat(Math.ceil(cuota_tasa_fija))-parseFloat(Math.ceil(interes_cuota));
                            let abono_capital_cuota=parseFloat(cuota_tasa_fija)-parseFloat(interes_cuota);
                            this.lista_cuotas_simulacion.push({
                                nro:contador,
                                fecha:fecha_cuota.format('YYYY-MM-DD'),
                                // interes:parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2),
                                interes:Math.round(parseFloat(interes_cuota.toFixed(2))),
                                // abono_capital:parseFloat(Math.ceil(cuota_sin_interes)).toFixed(2),
                                capital:Math.round(parseFloat(abono_capital_cuota.toFixed(2))),
                                // saldo_capital:parseFloat(Math.ceil(monto_total-cuota_tasa_fija)).toFixed(2),
                                saldo_capital:Math.round(parseFloat(parseFloat(monto_total-abono_capital_cuota).toFixed(2))),
                                ahorro:this.cantidad_ahorro,
                                seguro:this.cantidad_seguro,
                                // total_cuota:parseFloat(Math.ceil(parseFloat(this.cantidad_ahorro)+parseFloat(this.cantidad_seguro)+cuota_sin_interes+(monto_total*(this.solicitud.tasa/100)))).toFixed(2),
                                total_cuota:Math.round(parseFloat(parseFloat(cuota_tasa_fija).toFixed(2))),
                            })
                            contador=parseInt(parseInt(contador)+1);
                            monto_total=parseFloat(monto_total-abono_capital_cuota).toFixed(2);
                            // Agregar 30 días a la fecha actual
                            if(this.solicitud_simulacion.lapso_capital=='Mensual'){
                                // Obtener el día de la fecha
                                let dia = fecha_cuota.date();
                                // Puedes usar la variable 'dia' en tu código
                                console.log('Día:', dia);
                                let fecha_inicio=fecha_cuota;
                                // Agregar un mes y establecer el día en el mismo día del mes siguiente
                                fecha_cuota.add(1, 'months').date(dia);

                                // Obtener la diferencia en días
                                //let diferenciaDias = fecha_cuota.diff(fecha_inicio, 'days');
                            }else{
                                fecha_cuota.add(cantidad_dias, 'days');
                            }
                        }
                    // }else{
                    //     console.log('plan de pagos ya generado');
                    // }
                    if(this.lista_cuotas_simulacion.length>this.solicitud_simulacion.nro_cuotas){
                        this.lista_cuotas_simulacion.pop();
                    }
                }
            },*/
            abrirCalculadoraCredito(){
                this.tipo_tasa='amortizable';
                this.lista_cuotas_simulacion=[];
                this.solicitud_simulacion.nro_cuotas=0;
                this.solicitud_simulacion.tasa=0;
                this.solicitud_simulacion.importe_solicitud=0;
                this.solicitud_simulacion.lapso_capital='0';
                this.solicitud_simulacion.fecha_primera_cuota= moment().format('YYYY-MM-DD');
                this.solicitud_simulacion.fecha_desembolso= moment().format('YYYY-MM-DD');
                this.solicitud_simulacion.ahorro=false;
                this.solicitud_simulacion.seguro=false;
                this.solicitud_simulacion.cantidad_ahorro=0;
                this.solicitud_simulacion.cantidad_seguro=0;

                this.abrirModalCalculadoraCredito();
            },
            cerrarModalCalculadoraCredito(){
                $('#modalCalculadoraCredito').modal('hide');
            },
            abrirModalCalculadoraCredito(){
                $('#modalCalculadoraCredito').modal('show');
            },
            buscarSolicitud(){
                this.getSolicitudes(1);
            },
            // generalPlanPagosGeneral(){
            //     if(this.tasa_plan=='amortizable'){
            //         this.generarPlanPagos();
            //         console.log('amortizable');
            //     }else{
            //         if(this.tasa_plan=='fija'){
            //             this.generarPlanPagosTasaFija();
            //             console.log('fija');
            //         }
            //     }
            // },
            generalPlanPagosGeneral(){
                if(this.solicitud.tipo_tasa=='amortizable'){
                    this.generarPlanPagos();
                    console.log('amortizable');
                }else{
                    if(this.solicitud.tipo_tasa=='fija'){
                        this.generarPlanPagosTasaFija();
                        console.log('fija');
                    }
                }
            },
            guardarRespaldo(index){
                if(this.respaldo.imagen!=undefined && this.respaldo.imagen!=null){
                        this.respaldo.id_solicitud=this.solicitud.id_solicitud;
                        const formData = new FormData();
                        formData.append('id_respaldo', this.respaldo.id_respaldo);
                        formData.append('id_solicitud', this.respaldo.id_solicitud);
                        formData.append('imagen', this.respaldo.imagen);
                        formData.append('descripcion', this.respaldo.descripcion);
                
                        axios.post('/imagenRespaldo', formData)
                        .then((response)=>{
                            console.log(response);
                            this.items_respaldos[index].imagen=response.data.imagen;
                            this.items_respaldos[index].id_respaldo=response.data.id_respaldo;
                            this.items_respaldos[index].id_solicitud=response.data.id_solicitud;
                            this.items_respaldos[index].descripcion=response.data.descripcion;
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Operación exitosa',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .catch((error)=>{
                            console.log(error.message);
                        })
                        .finally(()=>{
        
                        })
                }else{
                    console.log('aun no se ha cargado la imagen');
                }
            },
         
            seleccionarRespaldo(event, index){
                // Realiza tu lógica de validación aquí
                if (this.items_respaldos[index].descripcion!='') {
                    this.respaldo.imagen = event.target.files[0];
                    this.respaldo.id_respaldo=this.items_respaldos[index].id_respaldo;
                    this.respaldo.id_solicitud=this.items_respaldos[index].id_solicitud;
                    this.respaldo.descripcion=this.items_respaldos[index].descripcion;
                    this.guardarRespaldo(index);
  
                } else {
                    // Muestra una alerta de validación fallida
                    console.log('abrir imagen');
                    this.mostrarToastError('Debe ingresar una descripción para el respaldo');
                    // Prevén el comportamiento predeterminado del enlace
                    event.preventDefault();
                }
            },
            agregarRespaldo(){
                this.items_respaldos.push(
                    {
                        id_respaldo:0,
                        imagen:'',
                    }
                )
            },
            quitarRespaldo(index){
                if(this.items_respaldos[index].id_respaldo!=0){
                    console.log('se eliminara desde la bd');
                    axios.get('/delete_respaldo?id_respaldo='+this.items_respaldos[index].id_respaldo)
                    .then((response)=>{
                        console.log(response);
                        this.getRespaldos(this.items_respaldos[index].id_solicitud);
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                }else{
                    if(this.items_respaldos.length>1){
                        this.items_respaldos.splice(index, 1);
                    }
                }
            },
            cerrarModalRespaldos(){
                $('#modalRespaldos').modal('hide');
            },
            getRespaldos(solicitud_id){
                axios.get('/get_respaldos?id_solicitud='+solicitud_id)
                .then((response)=>{
                    console.log(response.data);
                    this.items_respaldos=response.data;
                    if(this.items_respaldos.length==0){
                        this.items_respaldos.push({
                            id_respaldo:0,
                            id_solicitud:0,
                            descripcion:'',
                        })
                    }
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },
            abrirModalRespaldos(solicitud_id){
                this.solicitud.id_solicitud=solicitud_id;
                this.getRespaldos(this.solicitud.id_solicitud);
                $('#modalRespaldos').modal('show');
            },
            cambiarEstado(){
                this.cantidad_ahorro=this.ahorro?this.cantidad_ahorro:0;
                this.cantidad_seguro=this.seguro?this.cantidad_seguro:0;
                if(this.lista_cuotas.length>0){
                    if(this.tasa_plan=='amortizable'){
                        this.generarPlanPagos();
                    }else{
                        if(this.tasa_plan=='fija'){
                            this.generarPlanPagosTasaFija();
                        }
                    }
                }
            },

            cambiarEstadoSimulacion(){
                this.solicitud_simulacion.cantidad_ahorro=this.solicitud_simulacion.ahorro?this.solicitud_simulacion.cantidad_ahorro:0;
                this.solicitud_simulacion.cantidad_seguro=this.solicitud_simulacion.seguro?this.solicitud_simulacion.cantidad_seguro:0;
                if(this.lista_cuotas_simulacion.length>0){
                    if(this.tipo_tasa=='amortizable'){
                        this.generarPlanPagosSimulacion();
                    }else{
                        if(this.tipo_tasa=='fija'){
                            this.generarPlanPagosTasaFijaSimulacion();
                        }
                    }
                }
            },

            aprobarSolicitud(solicitud_id){
                this.aprobando_solicitud=true;
                if(this.lista_cuotas.length>0){
                    Swal.fire({
                        title: '¿Estás seguro de aprobar el prestamo?',
                        // text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'No, cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Aquí puedes ejecutar la acción que deseas cuando el usuario hace clic en "Sí, continuar"
                            //Swal.fire('¡Acción confirmada!', 'La acción ha sido ejecutada.', 'success');
                            let operacion=false;
                            axios.post('/save_planpagos_cuotas', {
                                detalles: JSON.stringify(this.lista_cuotas),
                                id_solicitud:this.solicitud.id_solicitud, 
                                fecha_final:this.lista_cuotas[this.lista_cuotas.length-1].fecha,
                                fecha_inicio_plan_pago:this.solicitud.fecha_inicio_plan_pago,
                                tasa:this.solicitud.tasa,
                                nro_cuotas:this.solicitud.nro_cuotas,
                                lapso_capital:this.solicitud.lapso_capital,
                                moneda:this.solicitud.moneda,
                            })
                            .then((response)=>{
                                console.log('respuesta:', response);
                                operacion=true;
                            })
                            .catch((error)=>{
                                console.log(error.message);
                            })
                            .finally(()=>{
                                if(operacion){
                                    Swal.fire({
                                        position: 'center',
                                        icon: 'success',
                                        title: 'Operación exitosa',
                                        text: 'Solicitud aprobada, Verifique en Plan de Pagos',
                                        showConfirmButton: true,
                                        // showCancelButton: true,
                                        confirmButtonText:'Aceptar',
                                        // cancelButtonText:'Cancelar',
                                        // timer: 1500
                                    });
                                    this.getSolicitudes(1);
                                    this.solicitud.estado=2;
                                }
                                this.aprobando_solicitud=false;

                            })
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Aquí puedes ejecutar algo si el usuario hace clic en "No, cancelar" o cierra el cuadro de diálogo
                            Swal.fire('Cancelado', 'La acción ha sido cancelada', 'error');
                            this.aprobando_solicitud=false;

                        }
                    });
                }else{
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Primero debe generar el plan de pagos',
                        showConfirmButton: true,
                        // showCancelButton: true,
                        confirmButtonText:'Aceptar',
                        // cancelButtonText:'Cancelar',
                        // timer: 1500
                    });
                    this.aprobando_solicitud=false;

                }

            },
            listaCuotasPdf(solicitud_id) {
             
                if(this.lista_cuotas.length==0){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Primero debe generar el plan de pagos',
                        showConfirmButton: true,
                        // showCancelButton: true,
                        confirmButtonText:'Aceptar',
                        // cancelButtonText:'Cancelar',
                        // timer: 1500
                    });
                }else{
                    // Construye la URL con el parámetro fecha_inicio
                    const url = '/lista_cuotas_pdf?id_solicitud='+solicitud_id+'&detalles='+JSON.stringify(this.lista_cuotas);
    
                    // Abre una nueva pestaña o ventana con la URL
                    window.open(url, '_blank');
                }

          
            },
            generarPlanPagosTasaFija(){
                
                    this.lista_cuotas=[];
                    let monto_total=parseFloat(this.solicitud.importe_solicitud);
                    let contador=1;
                    let fecha_inicio=moment(this.solicitud.fecha_desembolso);
                    let fecha_final=moment(this.solicitud.fecha_primera_cuota);
                    let saldo_capital_aux=this.solicitud.importe_solicitud;
                    //let cantidad_dias=this.retornarDiasPlanPago(this.solicitud.lapso_capital);

                    while(contador<=this.solicitud.nro_cuotas){

                            let dias_inicio_fin = fecha_final.diff(fecha_inicio, 'days');
                            let cuotaTasaFija=0;

                            let interes=0;

                            if(contador==1){
           
                                // let tasa_pago = (this.solicitud_simulacion.lapso_capital=='Mensual')? parseFloat(this.solicitud_simulacion.tasa/100) : parseFloat(((this.solicitud_simulacion.tasa/100)/30)*dias_inicio_fin);
                                let tasa_pago = (this.solicitud.lapso_capital=='Mensual')? 
                                   parseFloat(((this.solicitud.tasa/100))) : 
                                   parseFloat(((this.solicitud.tasa/100)/30)*dias_inicio_fin);

                                let tasa_fija = tasa_pago;

                                let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud.nro_cuotas));
                                let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud.nro_cuotas) - 1);

                                cuotaTasaFija  = parseFloat(monto_total*(numerador_formula/denominador_formula));
                                //let interes_cuota=(this.solicitud_simulacion.lapso_capital=='Mensual')?parseFloat(monto_total*(this.solicitud_simulacion.tasa/100)):((parseFloat(monto_total*(this.solicitud_simulacion.tasa/100))/30)*cantidad_dias);

                                //interes=parseFloat(saldo_capital_aux*(this.solicitud.tasa/100))/30;
                                interes= (this.solicitud.lapso_capital=='Mensual')? (parseFloat(saldo_capital_aux*(this.solicitud.tasa/100))/dias_inicio_fin):(parseFloat(saldo_capital_aux*(this.solicitud.tasa/100))/30);

                                console.log('Cuota tasa fija', cuotaTasaFija);
                                
                            }else{
                                if(this.solicitud.lapso_capital=='Mensual'){
                                    let tasa_pago = parseFloat(this.solicitud.tasa/100);
                                    let tasa_fija = tasa_pago;

                                    let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud.nro_cuotas));
                                    let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud.nro_cuotas) - 1);

                                    cuotaTasaFija  = parseFloat(monto_total*(numerador_formula/denominador_formula));
                                    interes=parseFloat(saldo_capital_aux*(this.solicitud.tasa/100))/dias_inicio_fin;
                                    console.log('Cuota tasa fija', cuotaTasaFija);
                                }else{
                                    // interes=(saldo_capital_aux*(this.solicitud.tasa/100))/fecha_inicio.daysInMonth();
                                    let tasa_pago = parseFloat(((this.solicitud.tasa/100)/30)*dias_inicio_fin);
                                    let tasa_fija = tasa_pago;

                                    let numerador_formula = parseFloat(tasa_fija*((1+tasa_fija)**this.solicitud.nro_cuotas));
                                    let denominador_formula = parseFloat(((1+tasa_fija)**this.solicitud.nro_cuotas) - 1);

                                    cuotaTasaFija  = parseFloat(monto_total*(numerador_formula/denominador_formula));
                                    interes=parseFloat(parseFloat(saldo_capital_aux*(this.solicitud.tasa/100))/30);

                                    console.log('Cuota tasa fija: ', cuotaTasaFija);  
                                    console.log('Interes: ', interes);  
                                }
                            }

                            let abono_capital_cuota=parseFloat(cuotaTasaFija)-parseFloat(interes * dias_inicio_fin);


                            this.lista_cuotas.push({
                                nro:contador,
                                fecha:fecha_final.format('YYYY-MM-DD'),
                                interes:Math.round(parseFloat(interes*dias_inicio_fin)),
                                capital:Math.round(parseFloat(abono_capital_cuota)),
                                saldo_capital:Math.round(parseFloat(parseFloat(saldo_capital_aux-abono_capital_cuota))),
                                ahorro:parseFloat(this.cantidad_ahorro==null || this.cantidad_ahorro==''?0:this.cantidad_ahorro).toFixed(0),
                                seguro:parseFloat(this.cantidad_seguro==null || this.cantidad_seguro==''?0:this.cantidad_seguro).toFixed(0),

                                // total_cuota:parseFloat(parseFloat(cuotaTasaFija).toFixed(0) + parseFloat((this.ahorro?parseFloat(this.cantidad_ahorro):0) + (this.seguro?parseFloat(this.cantidad_seguro):0)).toFixed(0)).toFixed(0),
                                total_cuota: (() => {
                                    const cuotaBase = parseFloat(cuotaTasaFija);
                                    const ahorroMonto = this.ahorro ? parseFloat(this.cantidad_ahorro || 0) : 0;
                                    const seguroMonto = this.seguro ? parseFloat(this.cantidad_seguro || 0) : 0;
                                    const montoAdicional = ahorroMonto + seguroMonto;
                                    
                                    return (cuotaBase + montoAdicional).toFixed(0);
                                })(),

                            });

                            saldo_capital_aux=parseFloat(saldo_capital_aux-abono_capital_cuota).toFixed(2);
                        

                            fecha_inicio = fecha_final;
                            let aux_fecha_final = fecha_final;

                            if (this.solicitud.lapso_capital == 'Mensual') {
                                fecha_final = moment(aux_fecha_final).add(1, 'month');
                            } else {
                                if (this.solicitud.lapso_capital == 'Quincenal') {
                                    fecha_final = moment(aux_fecha_final).add(15, 'days');
                                } else {
                                    if (this.solicitud.lapso_capital == 'Semanal') {
                                        fecha_final = moment(aux_fecha_final).add(1, 'week');
                                    } else {
                                        if (this.solicitud.lapso_capital == 'Diario') {
                                            fecha_final = moment(aux_fecha_final).add(1, 'day');

                                        }
                                    }
                                }
                            }

                            contador++;
                            
                    }
            },
           
            generarPlanPagos(){
               

                    this.lista_cuotas=[];// inicia array vacio
                    let contador=1;

                    var fecha_inicio=moment(this.solicitud.fecha_desembolso);
                    var fecha_final=moment(this.solicitud.fecha_primera_cuota);

                    let capital_aux=this.solicitud.importe_solicitud/this.solicitud.nro_cuotas;
                    let saldo_capital_aux=this.solicitud.importe_solicitud;


                    while(contador<=this.solicitud.nro_cuotas){

                        let dias_inicio_fin =fecha_final.diff(fecha_inicio, 'days');

                        console.log('dias diferencia:', dias_inicio_fin);

                        // let interes=(saldo_capital_aux*(this.solicitud_simulacion.tasa/100))/((contador==1 && this.solicitud_simulacion.lapso_capital=='Mensual')?fecha_inicio.daysInMonth():(contador==1 && this.solicitud_simulacion.lapso_capital=='Quincenal')?15:(contador==1 && this.solicitud_simulacion.lapso_capital=='Semanal')?7:(contador==1 && this.solicitud_simulacion.lapso_capital=='Diario')?1:dias_inicio_fin);
                        let interes=0;
                        if(contador==1){
                            interes=(saldo_capital_aux*(this.solicitud.tasa/100))/fecha_inicio.daysInMonth();
                        }else{
                            if(this.solicitud.lapso_capital=='Mensual'){
                                interes=(saldo_capital_aux*(this.solicitud.tasa/100))/dias_inicio_fin;
                            }else{
                                interes=(saldo_capital_aux*(this.solicitud.tasa/100))/fecha_inicio.daysInMonth();
                            }
                        }
                        saldo_capital_aux=saldo_capital_aux - capital_aux;


                        this.lista_cuotas.push({
                            nro:contador,
                            fecha:fecha_final.format('YYYY-MM-DD'),
                            capital:parseFloat(capital_aux).toFixed(0),
                            // interes:parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2),
                            // interes:Math.round(parseFloat((this.solicitud.lapso_capital=='Mensual')?parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100))).toFixed(2 ): ((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*cantidad_dias).toFixed(2))) + parseFloat(contador==1 && diferenciaDias>0? ((parseFloat(Math.ceil(monto_total*(this.solicitud.tasa/100)))/30)*diferenciaDias).toFixed(0):0),
                            interes:parseFloat(interes*dias_inicio_fin).toFixed(0),

                            saldo_capital:parseFloat(saldo_capital_aux).toFixed(0),
                            ahorro:parseFloat(this.cantidad_ahorro==null || this.cantidad_ahorro==''?0:this.cantidad_ahorro).toFixed(0),
                            seguro:parseFloat(this.cantidad_seguro==null || this.cantidad_seguro==''?0:this.cantidad_seguro).toFixed(0),
                            total_cuota:parseFloat(parseFloat(capital_aux) + parseFloat(interes*dias_inicio_fin) + (this.ahorro?parseFloat(this.cantidad_ahorro):0) + (this.seguro?parseFloat(this.cantidad_seguro):0)).toFixed(0),
                        });
                        // console.log('fecha inicio', fecha_inicio);
                        // console.log('fecha final', fecha_final);
                        fecha_inicio=fecha_final;
                        let aux_fecha_final=fecha_final;
                        if(this.solicitud.lapso_capital=='Mensual'){
                            fecha_final=moment(aux_fecha_final).add(1, 'month');
                        }else{
                            if(this.solicitud.lapso_capital=='Quincenal'){
                                fecha_final=moment(aux_fecha_final).add(15, 'days');
                            }else{
                                if(this.solicitud.lapso_capital=='Semanal'){
                                    fecha_final=moment(aux_fecha_final).add(1, 'week');
                                }else{
                                    if(this.solicitud.lapso_capital=='Diario'){
                                        fecha_final=moment(aux_fecha_final).add(1, 'day');

                                    }
                                }
                            }
                        }
                        contador++;
                    }

            },
            retornarDiasPlanPago(lapso_capital){
                if(lapso_capital=='Diario'){
                    return 1;
                }else if(lapso_capital=='Semanal'){
                    return 7;
                }else if(lapso_capital=='Quincenal'){
                    return 15;
                }else if(lapso_capital=='Mensual'){
                    return 30;
                }
            },

            gestionarEstado(estado){
                if(estado==1){
                    return 'En espera';
                }else if(estado==2){
                    return 'Aprobado';
                }else if(estado==0){
                    return 'Anulado';
                }
            },
            abrirModalSimulacionPlanPago(item){
                this.lista_cuotas=[];
                this.cantidad_ahorro=0;
                this.cantidad_seguro=0;
                this.seguro=false;
                this.ahorro=false;
                //this.plan_pago_simulacion
                //this.cliente = await this.encontrarCliente(item.id_cliente);
                
                this.cliente_simulacion = this.items_cliente_simulacion.find(objeto => objeto.id == item.id_cliente) || null;
                this.lista_garantias=[];
                this.solicitud.id_solicitud=item.id;
                this.solicitud.importe_solicitud=item.importe_solicitud;
                this.solicitud.moneda=item.moneda;
                this.solicitud.lapso_capital=item.lapso_capital;
                this.solicitud.nro_cuotas=item.nro_cuotas;
                this.solicitud.tasa=item.tasa;
                this.solicitud.fecha_desembolso=item.fecha_desembolso;
                this.solicitud.fecha_primera_cuota=item.fecha_primera_cuota;
                this.solicitud.destino_prestamo=item.destino_prestamo;
                this.solicitud.monto_pago_adm=item.monto_pago_adm;
                this.solicitud.id_cliente=item.id_cliente;
                this.solicitud.id_usuario=item.id_usuario;
                this.solicitud.tipo_garantia=item.tipo_garantia;
                this.solicitud.tipo_desembolso=item.tipo_desembolso;
                this.solicitud.estado=item.estado;
                this.solicitud.tipo_tasa=item.tipo_tasa;
                console.log(item.tipo_tasa);
                $('#modalSimulacionPlanPago').modal('show');
            },

            

            cerrarModalSimulacionPlanPago(item){

                //this.plan_pago_simulacion
                $('#modalSimulacionPlanPago').modal('hide');
            },
            guardarImagenes(){

                // const lista_objetos = this.lista_garantias_imagenes.map(imagen => imagen.lista_imagenes);
                // console.log(lista_objetos);
                // const lista_objetos=[];
                // for(let i=0; i<this.lista_garantias_imagenes.length;i++){
                //     lista_objetos.push(this.lista_garantias_imagenes[i].lista_imagenes)
                // }

                const formData = new FormData();

                this.lista_garantias_imagenes.forEach(imagen => {
                formData.append('imagenes[]', imagen.lista_imagenes);
                });

                axios.post('/solicitud_garantia_imagenes', formData)
                .then((response)=>{
                    console.log(response);
                })
                .catch((error)=>{
                    console.log(error.message);
                })

                // const lista_objetos=[];
                // for(let i=0; i<this.lista_garantias_imagenes.length;i++){
                //     lista_objetos.push(this.lista_garantias_imagenes[i].lista_imagenes)
                // }
                // //Convertir el objeto lista_objetos a una cadena JSON
                // const lista_objetos_json = JSON.stringify(lista_objetos);
                // const formData = new FormData();
                // //Agregar la cadena JSON como un campo FormData
                // formData.append('imagenes', lista_objetos_json);
  
                // axios.post('/solicitud_garantia_imagenes', formData, {
                // headers: {
                //     'Content-Type': 'multipart/form-data', // Importante: Establecer el tipo de contenido como 'multipart/form-data'
                // },
                // })
                // .then((response) => {
                //     console.log(response);
                // })
                // .catch((error) => {
                //     console.error(error.message);
                // });
            },
            guardarImagen(){
                
            },
            seleccionarImagen(event, index, index_padre, item) {
                
                const input = event.target;
                if (input.files && input.files[0]) {
                    // Actualiza el atributo src de la imagen con la vista previa de la imagen seleccionada
                    const imagenSeleccionada = URL.createObjectURL(input.files[0]);
                    const nombreImagen = input.files[0].name;
                    this.lista_garantias_imagenes[index_padre].lista_imagenes[index].imagen=nombreImagen;
                    //this.lista_garantias_imagenes[index_padre].lista_imagenes[index].imagen_file=imagenSeleccionada;
                    this.lista_garantias_imagenes[index_padre].lista_imagenes[index].imagen_file=input.files[0];
                    this.lista_garantias_imagenes[index_padre].lista_imagenes[index].id_garantia=item.id;
                    // guardando
                    let guardado=false;
                    const formData = new FormData();
                    formData.append('imagen', this.lista_garantias_imagenes[index_padre].lista_imagenes[index].imagen_file);
                    formData.append('id_garantia', this.lista_garantias_imagenes[index_padre].lista_imagenes[index].id_garantia);
                    formData.append('id_imagen', this.lista_garantias_imagenes[index_padre].lista_imagenes[index].id_imagen);

                    axios.post('/guardar_imagen', formData).then((response) => {
                        console.log(response);
                        guardado=true;
                        this.getImagenes(this.lista_garantias_imagenes[index_padre].id_solicitud);
                    })
                    .catch((error) => {
                        console.log(error.message);
                        
                    })
                    .finally(()=>{
                        if(guardado){
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Operación exitosa',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            //this.getinformacionEmpresa();
                        }
                    })

                    // Actualiza el atributo src del elemento con id "imagenPreview"
                    //document.getElementById('imagenPreview'+index_padre+''+index).src = imagenSeleccionada;
                }
            },
            limpiarImagenes(){
                for(let i=0; i<this.lista_garantias_imagenes.length; i++){
                    document.getElementById('imagenPreview'+i+''+0).src = '';
                }
            },
            eliminarImagen(index_padre, index_hijo){
                let eliminado_imagen=false;
                if(this.lista_garantias_imagenes[index_padre].lista_imagenes[index_hijo].id_garantia==0){
                    this.lista_garantias_imagenes[index_padre].lista_imagenes.splice(index_hijo, 1);
                }else{

                    console.log('este debe eliminar de la bd tambien');
                    axios.get('/eliminar_imagen?id_imagen='+this.lista_garantias_imagenes[index_padre].lista_imagenes[index_hijo].id_imagen)
                    .then((response)=>{
                        console.log(response);
                        eliminado_imagen=true;
                    }).catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        if(eliminado_imagen){
                            this.getImagenes(this.lista_garantias_imagenes[index_padre].id_solicitud);
                        }
                    })
                }
            },
            agregarImagen(index){
                console.log('agregando');
                const nuevaImagen={
                    id_imagen:0,
                    id_garantia:0,
                    imagen:'',
                    imagen_file:null,
                }
                this.lista_garantias_imagenes[index].lista_imagenes.push(nuevaImagen);
            },
            cerrarModalGarantias(){
                $('#modalGarantias').modal('hide');
            },

            abrirModalGarantias(id_solicitud){
                axios.get('/get_garantias?id_solicitud='+id_solicitud).then((response)=>{
                    this.lista_garantias_imagenes=response.data.garantias;
                    for(let i=0; i<this.lista_garantias_imagenes.length;i++){

                        axios.get('/get_imagenes_garantia?id_garantia='+this.lista_garantias_imagenes[i].id)
                        .then((response)=>{
                            console.log(response);
                            const lista_imagen_garantia = response.data;
                            if(lista_imagen_garantia.length==0){
                                const lista_imagenes=[{
                                    id_imagen:0,
                                    id_garantia:0,
                                    imagen:'',
                                    imagen_file:null,
                                }];
                                this.lista_garantias_imagenes[i].lista_imagenes=lista_imagenes;
                            }else{
                                this.lista_garantias_imagenes[i].lista_imagenes=[];
                                for(let j=0; j<lista_imagen_garantia.length; j++){
                                    this.lista_garantias_imagenes[i].lista_imagenes.push(
                                        {
                                            id_imagen:lista_imagen_garantia[j].id,
                                            id_garantia:lista_imagen_garantia[j].id_garantia,
                                            imagen:lista_imagen_garantia[j].imagen,
                                            imagen_file:null,
                                        }
                                    )
                                }
                            }
                        }).catch((error)=>{
                            console.log(error.message);
                        })
                        
                    }

         
                })
                .catch((error)=>{
                    console.log(error.message);
                });
                $('#modalGarantias').modal('show');
            },
            getImagenes(id_solicitud){
                axios.get('/get_garantias?id_solicitud='+id_solicitud).then((response)=>{
                    this.lista_garantias_imagenes=response.data.garantias;
                    for(let i=0; i<this.lista_garantias_imagenes.length;i++){

                        axios.get('/get_imagenes_garantia?id_garantia='+this.lista_garantias_imagenes[i].id)
                        .then((response)=>{
                            console.log(response);
                            const lista_imagen_garantia = response.data;
                            if(lista_imagen_garantia.length==0){
                                const lista_imagenes=[{
                                    id_imagen:0,
                                    id_garantia:0,
                                    imagen:'',
                                    imagen_file:null,
                                }];
                                this.lista_garantias_imagenes[i].lista_imagenes=lista_imagenes;
                            }else{
                                this.lista_garantias_imagenes[i].lista_imagenes=[];
                                for(let j=0; j<lista_imagen_garantia.length; j++){
                                    this.lista_garantias_imagenes[i].lista_imagenes.push(
                                        {
                                            id_imagen:lista_imagen_garantia[j].id,
                                            id_garantia:lista_imagen_garantia[j].id_garantia,
                                            imagen:lista_imagen_garantia[j].imagen,
                                            imagen_file:null,
                                        }
                                    )
                                }
                            }
                        }).catch((error)=>{
                            console.log(error.message);
                        })
                        
                    }

         
                })
                .catch((error)=>{
                    console.log(error.message);
                });
            },
            seleccionarCliente(item) {
                console.log(item);
                this.isVisibleCliente= false;
                this.cliente.buscar=item.nombre +' - '+item.ci;
                this.cliente.id_cliente=item.id;
                this.solicitud.id_cliente=this.cliente.id_cliente;
                this.cliente.ci=item.ci +' - '+item.lugar_expedicion;
                this.cliente.actividad=item.actividad;
            },
            seleccionarCodeudor(item, index) {
                console.log(item);
                this.lista_codeudores[index].select_codeudor.isVisibleCodeudor= false;
                this.lista_codeudores[index].select_codeudor.codeudor.buscar=item.nombre +' - '+item.ci;
                
                this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor=item.id;
                this.lista_codeudores[index].select_codeudor.codeudor.lugar_expedicion=item.lugar_expedicion;
                this.lista_codeudores[index].select_codeudor.codeudor.ci=item.ci +' - '+item.lugar_expedicion;
                this.lista_codeudores[index].select_codeudor.codeudor.actividad=item.actividad;

                // this.solicitud.id_codeudor=this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor;
                // lista_codeudores_enviar.push({
                //     'id_codeudor':this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor
                // });
            },
            async getClientes(){
                const url='/get_clientes_sin';
               
                await axios.get(url).then((response)=>{
                    console.log(response.data);
                    this.items_cliente=response.data;
                    this.items_cliente_simulacion=response.data;
                })
                .catch(function(error){
                    console.log(error);
                })
            },

            async getCodeudoresTabla(){
                const url='/get_codeudores_solicitudes';
           
                await axios.get(url).then((response)=>{
                    console.log(response.data);
                    this.lista_codeudores_tabla=response.data;
                    //me.items_cliente_simulacion=response.data;
                })
                .catch(function(error){
                    console.log(error);
                })
            },
            async getCodeudores(pos){
                const url='/get_codeudores_sin';
                let me = this;
                await axios.get(url).then(async function(response){
                    console.log(response.data);
                    me.lista_codeudores[pos].select_codeudor.codeudor.items_codeudor=response.data;
                    //me.items_cliente_simulacion=response.data;
                })
                .catch(function(error){
                    console.log(error);
                })
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
            cambiarPagina(page){
                let me=this;
                me.pagination.current_page=page;
                me.getSolicitudes(page);
            },
            abrirModalNuevo() {
                this.tipo_tasa='amortizable';
                this.cliente={
                    id_cliente:0,
                    idd_cliente:'',
                    nombre:'',
                    ci:0,
                    lugar_expedicion:'',
                    buscar:'',
                };

            

                this.lista_codeudores=[
                    {
                        select_codeudor:{
                            isVisibleCodeudor:false,
                            codeudor:{
                                id_codeudor:0,
                                idd_codeudor:'',
                                nombre:'',
                                ci:0,
                                lugar_expedicion:'',
                                buscar:'',
                                actividad:'',
                                items_codeudor:[],
                            },
                        },
                    },
                ],
                
                this.lista_codeudores_enviar=[];
                this.getClientes();

                this.getCodeudores(0);
                

                this.lista_garantias= [{
                    id_garantia: 0,
                    descripcion: '',
                }];

                this.solicitud={
                    id_solicitud:0,
                    importe_solicitud: 0,
                    moneda:'0',
                    lapso_capital:'0',
                    nro_cuotas:0,
                    tasa:0,
                    fecha_desembolso: moment().format('YYYY-MM-DD'),
                    fecha_primera_cuota: moment().format('YYYY-MM-DD'),
                    destino_prestamo: '',
                    monto_pago_adm: 0,
                    estado:'',
                    id_cliente:0,
                    id_codeudor:0,
                    id_usuario:0,
                    tipo_garantia:'0',
                    tipo_desembolso:'0',
                    lista_codeudores:[],
                    enviado: 0,
                    accion:0,
                };
                $('#modalSolicitud').modal('show');


            },

       

            cerrarModalNuevo() {
                $('#modalSolicitud').modal('hide');
            },
            agregarGarantia() {
                const nuevaGarantia = {
                    id_garantia: this.lista_garantias.length + 1, // Asigna un valor automático
                    descripcion: '',
                };
                this.lista_garantias.push(nuevaGarantia);
  
            },
            // agregarTelefono(item) {
            //     const nuevoTelefono = {
            //         id_telefono: this.lista_telefonos.length + 1, // Asigna un valor automático
            //         tipo: '',
            //         numero: '',
            //         observacion: '',
                
            //     };
            //     this.lista_telefonos.push(nuevoTelefono);
            // },

            quitarGarantia(index) {
                if(this.lista_garantias.length>1){
                    this.lista_garantias.splice(index, 1);
                }
            },

            // quitarTelefono(index) {
            //     if(this.lista_telefonos.length>1){
            //         this.lista_telefonos.splice(index, 1);
            //     }
            // },

            /*async guardarSolicitud(){
                
                this.guardando_solicitud=true;
                var guardar_solicitud=false;
                this.solicitud.enviado=1;
                this.lista_codeudores_enviar=[];

                for(let i=0; i<this.lista_codeudores.length; i++){
                    this.lista_codeudores_enviar.push(this.lista_codeudores[i].select_codeudor.codeudor);
                }
                this.solicitud.lista_codeudores=[];
                this.solicitud.lista_codeudores=this.lista_codeudores_enviar;

                const idCodeudores = {};
                const duplicates = [];

                for (const codeudor of this.lista_codeudores_enviar) {
                    if (idCodeudores[codeudor.id_codeudor]) {
                        duplicates.push(codeudor);
                    } else {
                        idCodeudores[codeudor.id_codeudor] = true;
                    }
                }


                const tieneCamposVaciosGarantias = this.lista_garantias.some(objeto => objeto.descripcion == '');

                // const algunValorEsVacio = (this.solicitud.importe_solicitud!=0 && this.solicitud.importe_solicitud!='')
                // && this.solicitud.moneda!='0' && this.solicitud.lapso_capital!='0' && (this.solicitud.nro_cuotas!='' &&  this.solicitud.nro_cuotas!=0)
                // && (this.solicitud.tasa!='' && this.solicitud.tasa!=0) && this.solicitud.destino_prestamo!='' 
                // && this.solicitud.id_cliente!=0 && this.solicitud.tipo_garantia!='0' && this.solicitud.tipo_desembolso!='0'
                // && this.solicitud.monto_pago_adm!=null;

                const algunValorEsVacio = (
                    this.solicitud.importe_solicitud > 0 && 
                    this.solicitud.moneda != '0' && 
                    this.solicitud.lapso_capital != '0' && 
                    this.solicitud.nro_cuotas > 0 && 
                    this.solicitud.tasa > 0 && 
                    this.solicitud.destino_prestamo != '' && 
                    this.solicitud.id_cliente != 0 && 
                    this.solicitud.tipo_garantia != '0' && 
                    this.solicitud.tipo_desembolso != '0' && 
                    this.solicitud.monto_pago_adm != null
                );

                if(duplicates.length > 0){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Existen codeudores repetidos',
                        showConfirmButton: true,
                        //timer: 1500
                    });
                    this.guardando_solicitud=false;

                }else{
                    if (algunValorEsVacio==false || (tieneCamposVaciosGarantias && this.solicitud.tipo_garantia=='Prendario')) {
                        console.log('no se envia');
                        this.guardando_solicitud=false;
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Debe completar los campos requeridos',
                            showConfirmButton: true,
                            //timer: 1500
                        });
                    }else{
                        this.solicitud.tipo_tasa=this.tipo_tasa;
                        this.solicitud.garantias=this.lista_garantias;
                        console.log('se envia');
                        axios.post('/save_solicitud',this.solicitud).then((response)=>{
                            console.log(response);
                            guardar_solicitud=true;
                        })
                        .catch((error)=>{
                            console.log(error.message);
                        })
                        .finally(()=>{
                            if(guardar_solicitud==true){
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Operación exitosa',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                this.getCodeudoresTabla();
                                this.getSolicitudes(1);
                                $('#modalSolicitud').modal('hide');
    
                                
    
                            }
                            this.guardando_solicitud=false;
                        })
                       
                        console.log('se envia');
                    }
                }
          
            },*/

            async guardarSolicitud() {
                try {
                    this.guardando_solicitud = true;
                    this.solicitud.enviado = 1;

                    // Preparar la lista de codeudores
                    let lista_codeudores_aux=this.lista_codeudores.map(item => item.select_codeudor.codeudor);
                    this.solicitud.lista_codeudores = lista_codeudores_aux.filter(item=>item.id_codeudor!=0);
                    // this.solicitud.lista_codeudores = this.lista_codeudores.filter(codeudor => codeudor.id_codeudor !== 0);

                    // Verificar codeudores duplicados usando un Set
                    const codeudorIds = new Set();
                    const duplicados = this.solicitud.lista_codeudores.some(codeudor => {
                        if (codeudorIds.has(codeudor.id_codeudor)) {
                            return true;
                        }
                        codeudorIds.add(codeudor.id_codeudor);
                        return false;
                    });

                    // Verificar si hay campos vacíos en las garantías
                    const tieneCamposVaciosGarantias = this.lista_garantias.some(objeto => objeto.descripcion === '');

                    // Verificar si hay algún campo vacío en la solicitud
                    const algunValorEsVacio = (
                        this.solicitud.importe_solicitud > 0 &&
                        this.solicitud.moneda !== '0' &&
                        this.solicitud.lapso_capital !== '0' &&
                        this.solicitud.nro_cuotas > 0 &&
                        this.solicitud.tasa > 0 &&
                        this.solicitud.destino_prestamo !== '' &&
                        this.solicitud.id_cliente !== 0 &&
                        this.solicitud.tipo_garantia !== '0' &&
                        this.solicitud.tipo_desembolso !== '0' &&
                        //(this.solicitud.monto_pago_adm !== null || this.solicitud.monto_pago_adm>0) &&
                        this.solicitud.lista_codeudores.length > 0
                    );

                    // Manejo de errores y validaciones
                    if (duplicados) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Existen codeudores repetidos',
                            showConfirmButton: true,
                        });
                        return;
                    }

                    if (!algunValorEsVacio || (tieneCamposVaciosGarantias && this.solicitud.tipo_garantia === 'Prendario')) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Debe completar los campos requeridos',
                            showConfirmButton: true,
                        });
                        return;
                    }

                    // Enviar la solicitud si pasa las validaciones
                    this.solicitud.tipo_tasa = this.tipo_tasa;
                    this.solicitud.garantias = this.lista_garantias;

                    const response = await axios.post('/save_solicitud', this.solicitud);
                    console.log(response);

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Operación exitosa',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    this.getCodeudoresTabla();
                    this.getSolicitudes(1);
                    $('#modalSolicitud').modal('hide');

                } catch (error) {
                    console.log(error.message);
                } finally {
                    this.guardando_solicitud = false;
                }
            },

            /*modificarSolicitud(){
                var modificar_solicitud=false;
                this.solicitud.enviado=1;

                this.lista_codeudores_enviar=[];
                for(let i=0; i<this.lista_codeudores.length; i++){
                    this.lista_codeudores_enviar.push(this.lista_codeudores[i].select_codeudor.codeudor);
                }
                this.solicitud.lista_codeudores=[];
                this.solicitud.lista_codeudores=this.lista_codeudores_enviar;

                const idCodeudores = {};
                const duplicates = [];

                for (const codeudor of this.lista_codeudores_enviar) {
                    if (idCodeudores[codeudor.id_codeudor]) {
                        duplicates.push(codeudor);
                    } else {
                        idCodeudores[codeudor.id_codeudor] = true;
                    }
                }


                const tieneCamposVaciosGarantias = this.lista_garantias.some(objeto => objeto.descripcion == '');
                const algunValorEsVacio = (this.solicitud.importe_solicitud!=0 && this.solicitud.importe_solicitud!='')
                && this.solicitud.moneda!='0' && this.solicitud.lapso_capital!='0' && (this.solicitud.nro_cuotas!='' &&  this.solicitud.nro_cuotas!=0)
                && (this.solicitud.tasa!='' && this.solicitud.tasa!=0) && this.solicitud.destino_prestamo!='' 
                && this.solicitud.id_cliente!=0 && this.solicitud.tipo_garantia!='0' && this.solicitud.tipo_desembolso!='0'
                && this.solicitud.monto_pago_adm!=null;


          
                if(duplicates.length > 0){
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Existen codeudores repetidos',
                        showConfirmButton: true,
                        //timer: 1500
                    });
                    //this.guardando_solicitud=false;

                }else{
                    if (algunValorEsVacio==false || (tieneCamposVaciosGarantias && this.solicitud.tipo_garantia=='Prendario')) {
                        console.log('no se envia');
                    }else{
                        this.solicitud.garantias=this.lista_garantias;
                        this.solicitud.tipo_tasa=this.tipo_tasa;
                        console.log('se envia');
                        axios.post('/modify_solicitud',this.solicitud).then((response)=>{
                            console.log(response);
                            modificar_solicitud=true;
                        })
                        .catch((error)=>{
                            console.log(error.message);
                        })
                        .finally(()=>{
                            if(modificar_solicitud==true){
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Operación exitosa',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                this.getCodeudoresTabla();
                                this.getSolicitudes(1);
                                $('#modalSolicitud').modal('hide');
                                this.solicitud.enviado=0;
    
                                
    
                            }
                        })
                        console.log('se envia');
                    }
                }
            },*/


            modificarSolicitud() {
                this.solicitud.enviado = 1;

                // Preparar la lista de codeudores a enviar
                // this.solicitud.lista_codeudores = this.lista_codeudores.map(item => item.select_codeudor.codeudor);
                let lista_codeudores_aux = this.lista_codeudores.map(item => item.select_codeudor.codeudor);
                this.solicitud.lista_codeudores=lista_codeudores_aux.filter(item=>item.id_codeudor!=0);
                // Verificar duplicados en los codeudores
                const idCodeudores = new Set();
                const duplicates = this.solicitud.lista_codeudores.filter(codeudor => {
                    if (idCodeudores.has(codeudor.id_codeudor)) {
                        return true;
                    }
                    idCodeudores.add(codeudor.id_codeudor);
                    return false;
                });

                // Verificar si existen campos vacíos en las garantías
                const tieneCamposVaciosGarantias = this.lista_garantias.some(objeto => objeto.descripcion === '');

                // Validar campos de la solicitud
                const algunValorEsVacio = (
                    this.solicitud.importe_solicitud > 0 &&
                    this.solicitud.moneda !== '0' &&
                    this.solicitud.lapso_capital !== '0' &&
                    this.solicitud.nro_cuotas > 0 &&
                    this.solicitud.tasa > 0 &&
                    this.solicitud.destino_prestamo !== '' &&
                    this.solicitud.id_cliente !== 0 &&
                    this.solicitud.tipo_garantia !== '0' &&
                    this.solicitud.tipo_desembolso !== '0' &&
                    this.solicitud.monto_pago_adm != null &&
                    this.solicitud.monto_pago_adm > 0
                );

                // Validar y manejar duplicados
                if (duplicates.length > 0) {
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Existen codeudores repetidos',
                        showConfirmButton: true,
                    });
                    return; // Detener el proceso si hay duplicados
                }

                // Validar y manejar campos vacíos
                if (!algunValorEsVacio || (tieneCamposVaciosGarantias && this.solicitud.tipo_garantia === 'Prendario')) {
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debe completar los campos requeridos',
                        showConfirmButton: true,
                    });
                    return; // Detener el proceso si hay campos vacíos
                }

                // Enviar la solicitud si pasa las validaciones
                this.solicitud.garantias = this.lista_garantias;
                this.solicitud.tipo_tasa = this.tipo_tasa;

                axios.post('/modify_solicitud', this.solicitud)
                    .then(response => {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Operación exitosa',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        this.getCodeudoresTabla();
                        this.getSolicitudes(1);
                        $('#modalSolicitud').modal('hide');
                        this.solicitud.enviado = 0;
                    })
                    .catch(error => {
                        console.log(error.message);
                    });
            },


            // validarCliente(){
                
            //     this.cliente_validaciones.nombre=(this.cliente.nombre=='')?false:true;
            //     //this.cliente_validaciones.fecha_nacimiento=(this.cliente.fecha_nacimiento==moment().format('YYYY-MM-DD') || this.cliente.fecha_nacimiento=='')?false:true;
            //     this.cliente_validaciones.ci=(this.cliente.ci=='')?false:true;
            //     this.cliente_validaciones.actividad=(this.cliente.actividad=='')?false:true;
            //     this.cliente_validaciones.ingreso_mensual=(this.cliente.ingreso_mensual=='')?false:true;
            //     this.cliente_validaciones.estado_civil=(this.cliente.estado_civil==''|| this.cliente.estado_civil=='0')?false:true;
            //     this.cliente_validaciones.vivienda=(this.cliente.vivienda==''|| this.cliente.vivienda=='0')?false:true;
            //     this.cliente_validaciones.sexo=(this.cliente.sexo==''|| this.cliente.sexo=='0')?false:true;
            //     this.cliente_validaciones.lugar_expedicion=(this.cliente.lugar_expedicion==''|| this.cliente.lugar_expedicion=='0')?false:true;

                
            // },
            activarSolicitud(item){
                axios.get('/activar_solicitud?id_solicitud='+item.id).then((response)=>{
                    console.log(response);
                    
                })
                .catch(()=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    this.getSolicitudes(1);
                })
            },
            desactivarSolicitud(item){
                axios.get('/desactivar_solicitud?id_solicitud='+item.id).then((response)=>{
                    console.log(response);
                    
                })
                .catch(()=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    this.getSolicitudes(1);
                    
                })
            },

            async getSolicitudes(page){
                await axios.get('/get_solicitudes?page='+page+'&criterio='+this.criterio+'&buscar='+this.buscar+'&estado='+this.criterio_estado+
                    '&fecha_inicial='+this.fecha_inicial_buscar+'&fecha_final='+this.fecha_final_buscar
                ).then((response)=>{
                    console.log(response);
                    this.lista_solicitudes=response.data.data;
                    this.pagination={total:response.data.total, 
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
            async editarSolicitud(item){
                this.preloader=true;
                this.solicitud.lista_codeudores=[];
                
                this.items_codeudores_solicitud=[];
                //await this.getCodeudorSolicitud(item.id);

                this.lista_garantias= [{
                    id_garantia: 0,
                    descripcion: '',
                }];

                this.solicitud.id_solicitud=item.id;
                this.solicitud.importe_solicitud=item.importe_solicitud;
                this.solicitud.moneda=item.moneda;
                this.solicitud.lapso_capital=item.lapso_capital;
                this.solicitud.nro_cuotas=item.nro_cuotas;
                this.solicitud.tasa=item.tasa;
                this.solicitud.fecha_desembolso=item.fecha_desembolso;
                this.solicitud.fecha_primera_cuota=item.fecha_primera_cuota;
                this.solicitud.destino_prestamo=item.destino_prestamo;
                this.solicitud.monto_pago_adm=item.monto_pago_adm;
                this.solicitud.id_cliente=item.id_cliente;
                this.tipo_tasa=item.tipo_tasa;
                const objetoEncontrado = this.items_cliente.find(objeto => objeto.id == this.solicitud.id_cliente);
                this.seleccionarCliente(objetoEncontrado);
                // const objetoEncontradoCodeudor = this.select_codeudor.codeudor.items_codeudor.find(objeto => objeto.id == this.items_codeudores_solicitud[0].id_codeudor);
                // this.seleccionarCodeudor(objetoEncontradoCodeudor);

                await this.cargarCodeudores(item.id);

                if(this.lista_codeudores.length==0){
                    this.lista_codeudores=[
                        {
                            select_codeudor:{
                                isVisibleCodeudor:false,
                                codeudor:{
                                    id_codeudor:0,
                                    idd_codeudor:'',
                                    nombre:'',
                                    ci:0,
                                    lugar_expedicion:'',
                                    buscar:'',
                                    actividad:'',
                                    items_codeudor:[],
                                },
                            },
                        },
                    ];

                    this.getCodeudores(0);

                }

                this.solicitud.id_usuario=item.id_usuario;
                this.solicitud.tipo_garantia=item.tipo_garantia;
                this.solicitud.tipo_desembolso=item.tipo_desembolso;
                this.solicitud.accion=1;
                this.solicitud.enviado=0;

                await this.getGarantiasSolicitud();

                this.preloader=false;


                $('#modalSolicitud').modal('show');
            },

            async getGarantiasSolicitud(){
                await axios.get('/get_garantias?id_solicitud='+this.solicitud.id_solicitud).then((response)=>{
                    this.lista_garantias=response.data.garantias;
                    let lista_aux=[];
                    if(this.lista_garantias.length==0){
                        this.lista_garantias= [{
                            id_garantia: 0,
                            descripcion: '',
                        }];
                    }else{
                        for( let i=0; i<this.lista_garantias.length; i++){
                            lista_aux.push(
                                {
                                    id_garantia: this.lista_garantias[i].id,
                                    descripcion: this.lista_garantias[i].descripcion,
                                }
                            )
                        }
                        this.lista_garantias=lista_aux;
                    }
         
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },

            async getCodeudorSolicitud(id_solicitud){
                await axios.get('/get_codeudor_solicitud?id_solicitud='+id_solicitud).then((response)=>{
                    this.items_codeudores_solicitud=response.data;
                }).catch((error)=>{
                    console.log(error.message);
                })
            },

            async cargarCodeudores(id_solicitud){
                
                await this.getCodeudorSolicitud(id_solicitud);
                await this.obtenerCodeudores();
                this.lista_codeudores=[];
                for(let i=0; i<this.items_codeudores_solicitud.length; i++){
                    
                  
                    // this.lista_codeudores[i].select_codeudor.isVisibleCodeudor= false;
                    // this.lista_codeudores[i].select_codeudor.codeudor.buscar=this.items_codeudores_solicitud[i].nombre +' '+this.items_codeudores_solicitud[i].ci;
                    
                    // this.lista_codeudores[i].select_codeudor.codeudor.id_codeudor=this.items_codeudores_solicitud[i].id_codeudor;

                    this.lista_codeudores.push(
                        {
                            select_codeudor:{
                                isVisibleCodeudor:false,
                                codeudor:{
                                    id_codeudor:this.items_codeudores_solicitud[i].id_codeudor,
                                    idd_codeudor:'',
                                    nombre:'',
                                    actividad:this.items_codeudores_solicitud[i].actividad,
                                    ci:this.items_codeudores_solicitud[i].ci +' - '+this.items_codeudores_solicitud[i].lugar_expedicion,
                                    lugar_expedicion:this.items_codeudores_solicitud[i].lugar_expedicion,
                                    buscar:this.items_codeudores_solicitud[i].nombre +' '+this.items_codeudores_solicitud[i].ci,
                                    items_codeudor:this.lista_codeudores_editar,
                                },
                            },
                        }
                    );
                    // this.seleccionarCodeudor(this.items_codeudores_solicitud[i], i);
                }
            },
            seleccionarCodeudorSolicitud(item, index) {


                console.log(item);
                this.lista_codeudores[index].select_codeudor.isVisibleCodeudor= false;
                this.lista_codeudores[index].select_codeudor.codeudor.buscar=item.nombre +' '+item.ci;
                
                this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor=item.id;
                // this.solicitud.id_codeudor=this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor;
                // lista_codeudores_enviar.push({
                //     'id_codeudor':this.lista_codeudores[index].select_codeudor.codeudor.id_codeudor
                // });
            },


            async verSolicitud(item){
                
                this.items_codeudores_solicitud=[];
                //await this.getCodeudorSolicitud(item.id);
                this.lista_garantias=[];
                this.solicitud.id_solicitud=item.id;
                this.solicitud.importe_solicitud=item.importe_solicitud;
                this.solicitud.moneda=item.moneda;
                this.solicitud.lapso_capital=item.lapso_capital;
                this.solicitud.nro_cuotas=item.nro_cuotas;
                this.solicitud.tasa=item.tasa;
                this.solicitud.fecha_desembolso=item.fecha_desembolso;
                this.solicitud.fecha_primera_cuota=item.fecha_primera_cuota;
                this.solicitud.destino_prestamo=item.destino_prestamo;
                this.solicitud.monto_pago_adm=item.monto_pago_adm;
                this.solicitud.id_cliente=item.id_cliente;
                const objetoEncontrado = this.items_cliente.find(objeto => objeto.id == this.solicitud.id_cliente);
                this.seleccionarCliente(objetoEncontrado);

                // const objetoEncontradoCodeudor = this.select_codeudor.codeudor.items_codeudor.find(objeto => objeto.id == this.items_codeudores_solicitud[0].id_codeudor);
                // this.seleccionarCodeudor(objetoEncontradoCodeudor);
                await this.cargarCodeudores(item.id);
                this.solicitud.id_usuario=item.id_usuario;
                this.solicitud.tipo_garantia=item.tipo_garantia;
                this.solicitud.tipo_desembolso=item.tipo_desembolso;
                this.solicitud.accion=2;
                axios.get('/get_garantias?id_solicitud='+this.solicitud.id_solicitud).then((response)=>{
                    this.lista_garantias=response.data.garantias;
         
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                $('#modalSolicitud').modal('show');
            },

            // verInformacionCliente(item) {
            // // Construye la URL con el parámetro fecha_inicio
            // const url = '/clientes_pdf?id_cliente='+item.id;

            // // Abre una nueva pestaña o ventana con la URL
            // window.open(url, '_blank');
            // },
        },


        async mounted() {
            console.log('Component mounted.');
            this.preloader=true;
            await this.getClientes();
            await this.getSolicitudes(1);
            //await this.getCodeudores();
            await this.getCodeudoresTabla();
            this.preloader=false;
        }


    }

</script>
<style scoped>
.estado-activo {
        background-color: #4caf50;
        /* Fondo verde para indicar activo */
        color: #fff;
        /* Texto blanco para contrastar */
        padding: 10px;
    }

    .estado-inactivo {
        background-color: #f44336;
        /* Fondo rojo para indicar inactivo */
        color: #fff;
        /* Texto blanco para contrastar */
        padding: 10px;
    }

    .search-container {
  position: relative;
}

ul.list-group {
  position: absolute;
  z-index: 1;
  background-color: white;
  list-style-type: none;
  padding: 0;
  margin: 0;
  border: 1px solid #ccc;
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
  width: 100%;
}

ul.list-group li {
  padding: 8px 12px;
  cursor: pointer;
}

ul.list-group li:hover {
  background-color: #f2f2f2;
}

span.toggle-results {
  display: block;
  text-align: center;
  margin-top: 8px;
  cursor: pointer;
}

.input-group-append {
  position: absolute;
  right: 0;
  top: 0;
  height: 100%;
}



.rotate-icon {
  transform: rotate(180deg);
}

.dropdown-wrapper {
  position: relative;
}

.dropdown-wrapper .selected-item {
  height: 24px;
  border-radius: 5px;
  padding: 5px 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-wrapper .selected-item .drop-down-icon {
  transform: rotate(0deg);
  transition: all 0.5s ease;
}

.dropdown-wrapper .selected-item .drop-down-icon.dropdown {
  transform: rotate(180deg);
}

.dropdown-wrapper .dropdown-popover {
  position: absolute;
  border: 2px solid lightgray;
  top: 46;
  left: 0;
  right: 0;
  background-color: #fff;
  max-width: 100%;
  align-items: center;
  padding: 10px;
  visibility: hidden;
  transition: all 0.35s linear;
  max-height: 0px;
  overflow: hidden;
}

.dropdown-wrapper .dropdown-popover.visible {
  max-height: 450px;
  visibility: visible;
}

.dropdown-wrapper .dropdown-popover input {
  width: 100%;
  height: 30px;
  border: 2px solid rgb(255, 255, 255);
  font-size: 18px;
  padding-left: 8px;
}

.dropdown-wrapper .dropdown-popover .options {
  width: 100%;
  padding-top: 12px;
}

.dropdown-wrapper .dropdown-popover .options ul {
  list-style: none;
  text-align: left;
  padding-left: 2px;
  max-height: 200px;
  overflow-y: scroll;
  overflow-x: hidden;
}

.dropdown-wrapper .dropdown-popover .options li {
  width: 100%;
  border-bottom: 1px solid lightgray;
  padding: 5px;
  border: 1px solid lightgray;
  background-color: #ffffff;
  cursor: pointer;
}

.dropdown-wrapper .dropdown-popover .options li:hover {
  background: #1abc7b;
  color: #fff;
  font-weight: bold;
}

.container {
  /* Estilos para el contenedor principal de la página de registro de venta */
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.total-container {
  /* Estilos para el contenedor del Total a Pagar */
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #f5f5f5;
  padding: 10px 20px;
  border-radius: 4px;
}

.total-label {
  /* Estilos para la etiqueta "Total a Pagar" */
  font-size: 18px;
  font-weight: bold;
}

.total-amount {
  /* Estilos para el monto total */
  font-size: 24px;
  color: #00a8e8;
  font-weight: bold;
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

/* p {
  color: white;
  margin-top: 10px;
} */
</style>
