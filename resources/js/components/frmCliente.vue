<template>
    <main>
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
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
                                    Gestión de clientes</h4>
                               
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
                    <div v-if="vista==0" class="row">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label for="opcion_asesor" class="text-dark">Asesor</label>
                                                <select @change="buscarCliente()" v-model="opcion_asesor" class="form-select form-control">
                                                    <option value="0">Todos los asesores</option>
                                                    <option v-for="(item, index) in lista_asesores" :key="index" :value="item.id">
                                                        {{ item.personal }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="criterio" class="text-dark">Criterio de Búsqueda</label>
                                                <select v-model="criterio" class="form-select form-control">
                                                    <option value="cliente.nombre">Nombre</option>
                                                    <option value="cliente.ci">CI</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="buscar" class="text-dark">Buscar</label>
                                                <div class="input-group">
                                                    <input v-model="buscar" type="text" class="form-control" @input="buscarCliente()">
                                                    <button class="btn btn-success">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <h6>Detalle de cantidad de clientes</h6>


                                        <div class="row">
                                            <div class="col-md-4">
                                                <h5 class="my-1">
                                                    <span style="border-radius:0" class="badge bg-danger fw-bold text-uppercase">Total clientes:  </span>
                                                    <span style="border-radius:0" class="badge text-danger fw-bold">{{clientes_sin_creditos}} Clientes </span>
                                                    
                                                </h5>
                                            </div>
                                            <div class="col-md-4">
                                                <h5 class="my-1">
                                                    <span style="border-radius:0" class="badge bg-success fw-bold text-uppercase">Clientes con creditos:  </span>
                                                    <span style="border-radius:0" class="badge text-success fw-bold">{{clientes_con_creditos}} Clientes </span>
                                                    
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="row">
                                            
                                            <div class="col-md-4" v-for="(item, index) in lista_cantidad_clientes" :key="index">
                                                <h5 class="my-1">
                                              
                                                    <span style="border-radius:0" class="badge bg-success fw-bold ">
                                                        <i class="fas fa-user"></i>
                                                        
                                                        {{item.personal}}:  </span>
                                                    <span style="border-radius:0" class="badge text-success fw-bold">{{ item.cant_clientes }} Clientes </span>
                                                </h5>
                                            </div>

                                        </div>
                                        
                                        <div class="table-responsive mt-3" style="font-size:12px">
                                            <table class="table mb-4 table-hover table-bordered table-striped table-sm">
                                                <thead class="bg-primary text-white text-uppercase" >
                                                    <tr style="background-color:#52BE80">
                                                        <th style="width: 20%;">Nombre</th>
                                                        <th style="width: 15%;">Asesor</th>
                                                        <th style="width: 10%;">CI</th>
                                                        <th style="width: 5%;">Sexo</th>
                                                        <th style="width: 10%;">E. civil</th>
                                                        <th style="width: 15%;">Actividad</th>
                                                        <th style="width: 10%;">Vivienda</th>
                                                        <th style="width: 5%;">Estado</th>
                                                        <th style="width: 10%;">Opciones</th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <tr class="" v-for="item in lista_clientes" :key="item.id">
                                                        <!-- <td>
                                                            <img :src="'img/cliente/'+(item.imagen==null || item.imagen==''?'default.png':item.imagen)" alt=""
                                                                    class="rounded avatar-md shadow">
                                                        </td> -->
                                                        <td>{{ item.nombre }}</td>
                                                        <td>
                                                            <template v-if='item.plan_pago_id==null'>
                                                                <h6 style="font-size:11px;" class="text-danger">sin creditos</h6>
                                                            </template>
                                                            <template v-else>
                                                            {{ item.personal }}
                                                            
                                                            </template>
                                                        </td>
                                                        <td>{{ item.ci }}</td>
                                                        <td>{{ item.sexo }}</td>
                                                        <td>{{ item.estado_civil }}</td>
                                                        <td>{{ item.actividad }}</td>
                                                        <td>{{ item.vivienda }}</td>
                                                        <td>
                                                            <span v-if="item.estado==1"
                                                                class="text-success">Activo</span>
                                                            <span v-else class="text-danger">Inactivo</span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group my-0 py-0">
                                                                <a 
                                                                    style="cursor:pointer;" class="text-success dropdown-toggle btn-sm my-0 py-0"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-ellipsis-h fa-lg fa-fw fs-4"></i>
                                                                </a>
                                                                <ul class="dropdown-menu my-0 py-0">
                                                                    <li @click="desactivarCliente(item)" v-if="item.estado==1">
                                                                        <a class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-times"></i> desactivar</a>
                                                                    </li>
                                                                    <li @click="activarCliente(item)" v-else><a
                                                                            class="dropdown-item text-success" href="#">
                                                                            <i class="fas fa-check"></i> activar</a>
                                                                    </li>
                                                                    <li @click="editarCliente(item)"><a
                                                                            class="dropdown-item text-primary" href="#">
                                                                            <i class="fas fa-pencil-alt"></i> editar</a></li>
                                                                    <li @click="verCliente(item)"><a
                                                                            class="dropdown-item text-info" href="#">
                                                                            <i class="fas fa-eye"></i> ver</a></li>
                                                                    <li @click="verInformacionCliente(item)"><a
                                                                            class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-file-pdf"></i> pdf</a></li>

                                                                    <li @click="historialCrediticio(item)"><a
                                                                            class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-history"></i> Historial crediticio</a></li>

                                                                    <li @click="abrirModalFoto(item)"><a
                                                                            class="dropdown-item text-warning" href="#">
                                                                            <i class="fas fa-user"></i> {{ item.imagen=='' || item.imagen==null? 'Agregar foto':'Ver/Cambiar foto'}}</a></li>
                                                                    
                                                                            
                                                                </ul>
                                                            </div>
                                                        </td>

                                                    </tr> 
                                                </tbody>
                                            </table>
                                         
                                        </div>
                                        <!-- Card Pagination -->
                                    </div>
                                </div>
                                <!-- End Card -->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>

                    <div v-if="vista==1" class="row">
                                
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">

                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="invoice-title d-flex align-items-center">
                                                        <button class="btn btn-danger" @click="regresarPrincipal()">
                                                            <i class="fas fa-arrow-left"></i>
                                                            Regresar
                                                        </button>
                                                        <h4 class="font-size-16 ms-4 my-0"><strong>{{ cliente.nombre }} | CI.: {{ cliente.ci }}</strong></h4>
                                                    </div>
                                                    <hr>
                                                    <p v-if="lista_creditos_cliente.length==0">Este cliente aun no tiene un plan de pagos</p>
                                                    <template v-for="(item, index) in lista_creditos_cliente" :key="index">
                                                        <div class="row">
                                                            <div class="col-3">
                                                                <address>
                                                                    <strong>#Deuda:</strong><br>
                                                                    <strong>Tasa interes:</strong><br>
                                                                    <strong>Monto Desembolso ({{item.moneda}}):</strong><br>
                                                                    <strong>Asesor:</strong>
                                                                    
                                                                </address>
                                                            </div>
                                                            <div class="col-3 text-end text-uppercase">
                                                                <address>
                                                                    <strong>{{ item.id_plan_pago }}</strong><br>
                                                                    {{ item.tasa }}<br>
                                                                    {{ item.importe_solicitud }}<br>
                                                                    {{ item.nombre_usuario }}<br>
                                                          
                                                                </address>
                                                            </div>

                                                            <div class="col-3">
                                                                <address>
                                                                    <strong>Estado:</strong><br>
                                                                    <strong>Fecha desembolso:</strong><br>
                                                                    <strong>Nro cuotas:</strong><br>
                                                                    <strong>Tipo desembolso:</strong>
                                                                    
                                                                </address>
                                                            </div>
                                                            <div class="col-3 text-end">
                                                                <address>
                                                                    <strong :class="(item.estado_plan==1)?'bg-success border border-1 border-success px-3 text-white': (item.estado_plan==0)?'bg-danger border border-1 border-danger px-3 text-white':''">{{ (item.estado_plan==1)?'EN PROCESO':(item.estado_plan==0)?'CANCELADO':''}}</strong><br>
                                                                    {{ formatearFecha(item.fecha_desembolso) }}<br>
                                                                    {{ item.nro_cuotas }}<br>
                                                                    {{ item.tipo_desembolso }}<br>
                                                          
                                                                </address>
                                                            </div>


                                                        </div>
                                                        <div class="row">
                                                                <div class="col-12">
                                                                    <div>
                                                                        <div class="py-2">
                                                                            <h3 class="font-size-16"><strong>Detalle de cuotas</strong></h3>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="table-responsive" style="font-size:12px;">
                                                                                <div v-if="cargando">Cargando cuotas...</div>
                                                                                <table v-else class="table table-sm table-bordered">
                                                                                    <thead class="bg-primary text-white">
                                                                                        <tr>
                                                                                            <th>Nro</th>
                                                                                            <th>Fecha</th>
                                                                                            <th>Capital</th>
                                                                                            <th>Interes</th>
                                                                                            <th>Saldo capital</th>
                                                                                            <th>Ahorro</th>
                                                                                            <th>Seguro</th>
                                                                                            <th>Total Bs</th>
                                                                                            <th>Estado</th>

                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                            
                                                                                        <tr  :class="(item_cuotas.dias_pasados>0 && item_cuotas.estado==1)?'bg-danger text-white':(item_cuotas.estado==2)?'bg-success text-white':(item_cuotas.estado==3)?'bg-secondary text-white':(item_cuotas.numero==null)?'bg-warning text-white':''" v-for="(item_cuotas, index_cuotas) in lista_cuotas_planes.filter(cuota => cuota.id_plan_pago === item.id_plan_pago && cuota.estado !==3)" :key="index_cuotas">

                                                                                            <td>{{ item_cuotas.numero==null?'Amortizacion':item_cuotas.numero}}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">fecha pago</p>{{ formatearFecha(item_cuotas.fecha) }}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">capital pagado</p>{{ item_cuotas.capital }}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">interes pagado</p>{{ item_cuotas.interes }}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">saldo capital</p>{{ item_cuotas.saldo_capital }}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">multa pagada</p>{{ item_cuotas.ahorro }}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">- - - -</p>{{ item_cuotas.seguro }}</td>
                                                                                            <td><p v-if="item_cuotas.numero==null" class="m-0 p-0">total pagado</p>{{ item_cuotas.total }}</td>
                                                                                            <td>
                                                                                                <span v-if="item_cuotas.estado==1" class="badge text-bg-primary">Por pagar</span>
                                                                                                <span v-else-if="item_cuotas.estado==0" class="badge text-bg-danger">Anulado</span>
                                                                                                <span v-else-if="item_cuotas.estado==2" class="badge text-bg-success">Cancelado</span>
                                                                                            </td>

                                                                                        </tr> 
                                                                                    </tbody> 
                                                                                </table>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                </div>
                                                        </div> 

                                                        <hr>

                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end col -->
                    </div> <!-- end row -->

                </div>
                <!-- end page-content-wrapper-->
            </div>
            <!-- Container-fluid -->
        </div>
        
        <!-- Modal cliente -->
        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalCliente" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" style="width:90%; max-width:90%">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 v-if="cliente.accion==0" class="modal-title text-white" id="miModalLabel">Agregar nuevo cliente</h5>
                        <h5 v-if="cliente.accion==1" class="modal-title text-white" id="miModalLabel">Modificar cliente</h5>
                        <h5 v-if="cliente.accion==2" class="modal-title text-white" id="miModalLabel">Información del cliente: {{ cliente.nombre   }} - {{cliente.ci+' '+cliente.lugar_expedicion}}</h5>
                        <button @click="cerrarModalNuevo()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="row">
                                <!-- Columna 1 -->
                                <div class="col-md-6">
                                    <!-- Campo Nombre -->
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input v-model="cliente.nombre" type="text" class="form-control"
                                            id="nombre" name="nombre" :disabled="cliente.accion==2?true:false">
                                            <small class="text-danger text-sm-start" v-if="cliente.nombre=='' && cliente.enviado==1">Ingrese un nombre *</small>
                                    </div>

                                    <!-- Campo Fecha de Nacimiento -->
                                    <div class="mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input v-model="cliente.fecha_nacimiento" type="date" class="form-control" id="fecha_nacimiento"
                                            name="fecha_nacimiento" :disabled="cliente.accion==2?true:false" required>
                                        <!-- <p class="text-danger text-sm-start" v-if="cliente_validaciones.fecha_nacimiento==false">Ingrese una fecha *</p> -->
                                        
                                    </div>

                                    <!-- Campo CI -->
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <label for="ci" class="form-label">CI</label>
                                                <input v-model="cliente.ci" type="text" :disabled="cliente.accion==2?true:false" class="form-control" id="ci" name="ci" required>
                                                <small class="text-danger text-sm-start" v-if="cliente.ci=='' && cliente.enviado==1">Ingrese un CI *</small>
                                                
                                            </div>
                                            <div class="col-md-5">
                                                <label for="ci" class="form-label">DPTO</label>
                                                <select :disabled="cliente.accion==2?true:false" v-model="cliente.lugar_expedicion" class="form-select"
                                                    id="lugar_expedicion" name="lugar_expedicion" required>
                                                    <option value="0" selected hidden disabled>Seleccione</option>
                                                    <option v-for="item in lugares_expedicion" :value="item.sigla"
                                                        :key="item.sigla">
                                                        {{ item.sigla }}</option>
                                                </select>
                                                <small class="text-danger text-sm-start" v-if="(cliente.lugar_expedicion=='0' || cliente.lugar_expedicion=='') && cliente.enviado==1">seleccione *</small>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo Sexo -->
                                    <div class="mb-3">
                                        <label for="sexo" class="form-label">Género</label>
                                        <select :disabled="cliente.accion==2?true:false" v-model="cliente.sexo" class="form-select" id="sexo" name="sexo"
                                            required>
                                            <option value="0" selected hidden disabled>Seleccione un genero</option>
                                            <option v-for="item in sexos" :value="item.nombre" :key="item.nombre">
                                                {{ item.nombre }}</option>
                                        </select>
                                        <small class="text-danger text-sm-start" v-if="(cliente.sexo=='0' || cliente.sexo=='') && cliente.enviado==1">Seleccione un sexo *</small>
                                        

                                    </div>
                                </div>

                                <!-- Columna 2 -->
                                <div class="col-md-6">
                                    <!-- Campo Estado Civil -->
                                    <div class="mb-3">
                                        <label for="estado_civil" class="form-label">Estado Civil</label>
                                        <select :disabled="cliente.accion==2?true:false" v-model="cliente.estado_civil" class="form-select" id="estado_civil"
                                            name="estado_civil" required>
                                            <option value="0" selected hidden disabled>Seleccione un estado civil
                                            </option>
                                            <option v-for="item in estados_civil" :value="item.nombre"
                                                :key="item.nombre">
                                                {{ item.nombre }}</option>
                                        </select>
                                        <small class="text-danger text-sm-start" v-if="(cliente.estado_civil=='0' || cliente.estado_civil=='') && cliente.enviado==1">Seleccione un estado civil *</small>

                                    </div>

                                    <!-- Campo Actividad -->
                                    <div class="mb-3">
                            
                                        <div class="col-md-12 mb-3">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="buscarActividad" class="form-label fw-semibold text-dark">Seleccione una actividad:</label>
                                                    <div class="input-group">
                                                        <input type="text" v-model="actividadClase.buscar" id="buscarActividad"
                                                            class="form-control text-dark" placeholder="Buscar actividad..."
                                                            @input="filtrarActividades(actividadClase.buscar)" autocomplete="off" required/>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" style="position:relative;">
                                                <template v-if="filteredItemsActividades.length > 0">
                                                    <div class="com-completion-results shadow"
                                                        style="z-index: 1050; position: absolute; top: 100%; width: 100%; background: #fff; border: 1px solid #ececec; max-height: 250px; overflow: auto;"
                                                        v-bind:style="{ display: filteredItemsActividades.length > 0 && actividadClase.buscar != '' ? 'block' : 'none' }">
                                                        <ul style="list-style: none; padding: 0; margin: 0;">
                                                            <li v-for="(actividadItem, index) in filteredItemsActividades" :key="index"
                                                                @click="seleccionarActividad(actividadItem)"
                                                                style="cursor: pointer; padding: 8px; border-bottom: 1px solid #ececec;"
                                                                class="dropdown-item-hover">
                                                                <div class="container-fluid p-0">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <h6 style="font-size: 14px; color: #000; margin: 0;">
                                                                                {{ actividadItem.nombre }} 
                                                                            </h6>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>
                                            
                                            </div>
                                        </div>
                                        <small class="text-danger text-sm-start" v-if="cliente.actividad=='' && cliente.enviado==1">Ingrese una actividad *</small>
                                        
                                    </div>

                                    <!-- Campo Vivienda -->
                                    <div class="mb-3">
                                        <label for="vivienda" class="form-label">Vivienda</label>
                                        <select :disabled="cliente.accion==2?true:false" v-model="cliente.vivienda" class="form-select" id="vivienda"
                                            name="vivienda" required>
                                            <option value="0" selected hidden disabled>Seleccione un tipo vivienda
                                            </option>
                                            <option v-for="item in viviendas" :value="item.nombre" :key="item.nombre">
                                                {{ item.nombre }}</option>
                                        </select>
                                        <small class="text-danger text-sm-start" v-if="(cliente.vivienda=='0' || cliente.vivienda=='') && cliente.enviado==1">Seleccione un tipo vivienda *</small>
                                    
                                    </div>

                                    <!-- Campo Ingreso Mensual -->
                                    <div class="mb-3">
                                        <label for="ingreso_mensual" class="form-label">Ingreso Mensual</label>
                                            <input v-model="cliente.ingreso_mensual" type="text" :disabled="cliente.accion==2?true:false" class="form-control" id="ingreso_mensual" name="ingreso_mensual" required
                                                onkeydown="if(event.key==='.' && event.target.value.includes('.')){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9.]*/g,'');">

                                        <small class="text-danger text-sm-start" v-if="cliente.ingreso_mensual=='' && cliente.enviado==1">Ingrese un ingreso_mensual *</small>
                                    
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <p><strong>
                                DIRECCIONES DE CONTACTO
                            </strong></p>
 
                            <!-- <div class="card border border-success border-2" v-for="(item, index) in lista_direcciones" :key="index">
                                <div class="card-header bg-success">
                                    <div class="row py-0 d-flex align-items-center">
                                        <div class="col-md-10">
                                            <label for="" class="my-0 text-white">Ingrese direcciones de contacto</label>
                                        </div>
                                 
                                        <div v-if="cliente.accion!=2" class="col-md-2">
                                            <div class="input-group d-flex justify-content-end">
                                                <a @click="agregarDireccion()" class="btn btn-success btn-sm" style="border-radius:50%">
                                                    <i class="fas fa-plus" ></i>
                                                </a>
                                                <a v-if="lista_direcciones.length>1" @click="quitarDireccion(index)" class="btn btn-danger btn-sm ms-1" style="border-radius:50%">
                                                    <i class="fas fa-times" ></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row py-0 d-flex align-items-start">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="tipoDireccion" class="my-0 text-dark" style="font-size:12px;">Tipo dirección</label>
                                                <select class="form-control form-control-sm form-select" v-model="item.tipo" id="tipoDireccion">
                                                    <option value="" selected hidden disabled>Seleccione...</option>
                                                    <option v-for="tipo in tiposDeDirecciones" :key="tipo" :value="tipo">
                                                        {{ tipo }}
                                                    </option>
                                                </select>
                                                <small class="text-danger" v-if="(item.tipo=='' || item.tipo==null) && cliente.enviado==1 ">Ingrese un dato *</small>

                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Departamento</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.departamento" class="form-control form-control-sm" placeholder="Ingrese un departamento">
                                                <small class="text-danger" v-if="(item.departamento=='' || item.departamento==null) && cliente.enviado==1 ">Ingrese un dato *</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Ciudad</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.ciudad" class="form-control form-control-sm" placeholder="Ingrese una ciudad">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Zona</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.zona" class="form-control form-control-sm" placeholder="Ingrese una zona">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Descripcion</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.descripcion" class="form-control form-control-sm" placeholder="Ingrese una descripcion">
                                                <small class="text-danger text-sm-start" v-if="(item.descripcion=='' || item.descripcion==null) && cliente.enviado==1 ">Ingrese un dato *</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Replace the existing address card section in the modalCliente modal -->
                            <div class="card border border-success border-2" v-for="(item, index) in lista_direcciones" :key="index">
                                <div class="card-header bg-success">
                                    <div class="row py-0 d-flex align-items-center">
                                        <div class="col-md-10">
                                            <label for="" class="my-0 text-white">Ingrese direcciones de contacto</label>
                                        </div>
                                        <div v-if="cliente.accion!=2" class="col-md-2">
                                            <div class="input-group d-flex justify-content-end">
                                                <a @click="agregarDireccion()" class="btn btn-success btn-sm" style="border-radius:50%">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                <a v-if="lista_direcciones.length>1" @click="quitarDireccion(index)" class="btn btn-danger btn-sm ms-1" style="border-radius:50%">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row py-0 d-flex align-items-start">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="tipoDireccion" class="my-0 text-dark" style="font-size:12px;">Tipo dirección</label>
                                                <select class="form-control form-control-sm form-select" v-model="item.tipo" id="tipoDireccion">
                                                    <option value="" selected hidden disabled>Seleccione...</option>
                                                    <option v-for="tipo in tiposDeDirecciones" :key="tipo" :value="tipo">{{ tipo }}</option>
                                                </select>
                                                <small class="text-danger" v-if="(item.tipo=='' || item.tipo==null) && cliente.enviado==1">Ingrese un dato *</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Departamento</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.departamento" class="form-control form-control-sm" placeholder="Ingrese un departamento">
                                                <small class="text-danger" v-if="(item.departamento=='' || item.departamento==null) && cliente.enviado==1">Ingrese un dato *</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Ciudad</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.ciudad" class="form-control form-control-sm" placeholder="Ingrese una ciudad">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Zona</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.zona" class="form-control form-control-sm" placeholder="Ingrese una zona">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Descripción</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.descripcion" class="form-control form-control-sm" placeholder="Ingrese una descripción">
                                                <small class="text-danger text-sm-start" v-if="(item.descripcion=='' || item.descripcion==null) && cliente.enviado==1">Ingrese un dato *</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row py-2">
                                        <div class="col-md-12">
                                            <button v-if="cliente.accion!=2 && (!item.lat || !item.lng)" @click="abrirModalMapa(index, 'add')" class="btn btn-primary btn-sm me-2">
                                                <i class="fas fa-map-marker-alt"></i> Agregar Ubicación
                                            </button>
                                            <button v-if="item.lat && item.lng" @click="abrirModalMapa(index, 'view')" class="btn btn-info btn-sm me-2">
                                                <i class="fas fa-eye"></i> Ver Ubicación
                                            </button>
                                            <button v-if="cliente.accion!=2 && item.lat && item.lng" @click="abrirModalMapa(index, 'update')" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Actualizar Ubicación
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                           
                            <hr>
                            <p><strong>
                                NÚMEROS DE CONTACTO
                            </strong></p>

                            <div class="card border border-success border-2" v-for="(item, index) in lista_telefonos" :key="index">
                                <div class="card-header bg-success">
                                    <div class="row py-0 d-flex align-items-center">
                                        <div class="col-md-10">
                                            <label for="" class="my-0 text-white">Ingrese referencias de contacto</label>
                                        </div>
                                 
                                        <div v-if="cliente.accion!=2" class="col-md-2">
                                            <div class="input-group d-flex justify-content-end">
                                                <a @click="agregarTelefono()" class="btn btn-success btn-sm" style="border-radius:50%">
                                                    <i class="fas fa-plus" ></i>
                                                </a>
                                                <a v-if="lista_telefonos.length>1" @click="quitarTelefono(index)" class="btn btn-danger btn-sm ms-1" style="border-radius:50%">
                                                    <i class="fas fa-times" ></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row py-0 d-flex align-items-start">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Tipo</label>
                                                <!-- <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.tipo" class="form-control form-control-sm" placeholder="Ingrese un tipo"> -->
                                                <select :disabled="cliente.accion==2?true:false" v-model="item.tipo" name="" id="" class="form-control form-control-sm form-select">
                                                    <option value="Numero telefono">Nr. Telf.</option>
                                                    <option value="Informacion contacto">Inf. Contacto</option>
                                                </select>
                                                <small class="text-danger" v-if="item.tipo=='' && cliente.enviado==1 ">Ingrese un dato *</small>

                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Numero telf.</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.numero"  class="form-control form-control-sm" placeholder="Ingrese nr telf.">
                                                <small class="text-danger" v-if="(item.numero=='' || item.numero==null) && cliente.enviado==1 ">Ingrese un dato *</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-8" v-if="item.tipo=='Numero telefono'">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Observación</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.observacion" class="form-control form-control-sm" placeholder="Ingrese obs.">
                                                <small class="text-danger" v-if="(item.observacion=='' || item.observacion==null) && item.tipo=='Numero telefono' && cliente.enviado==1 ">Ingrese un dato *</small>

                                            </div>
                                        </div>

                                        <div class="col-md-2" v-if="item.tipo=='Informacion contacto'">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Nombre</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.nombre" class="form-control form-control-sm" placeholder="Ingrese nombre.">
                                                <small class="text-danger" v-if="(item.nombre=='' || item.nombre==null) && cliente.enviado==1 && item.tipo=='Informacion contacto'">Ingrese un dato *</small>

                                            </div>
                                        </div>

                                        <div class="col-md-2" v-if="item.tipo=='Informacion contacto'">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Apellidos</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.apellidos" class="form-control form-control-sm" placeholder="Ingrese apellidos.">
                                                <small class="text-danger" v-if="(item.apellidos=='' || item.apellidos==null) && cliente.enviado==1 && item.tipo=='Informacion contacto'">Ingrese un dato *</small>

                                            </div>
                                        </div>

                                        <div class="col-md-3" v-if="item.tipo=='Informacion contacto'">
                                            <div class="form-group">
                                                <label for="" class="my-0 text-dark" style="font-size:12px;">Relación</label>
                                                <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.relacion" class="form-control form-control-sm" placeholder="Ingrese información.">
                                                <small class="text-danger" v-if="(item.relacion=='' || item.relacion==null) && cliente.enviado==1 && item.tipo=='Informacion contacto'">Ingrese un dato *</small>

                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-center">
                                <button @click="cerrarModalNuevo()" type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                    <i class="fas fa-times-circle"></i>
                                    Cerrar</button>
                                <button :disabled="guardando_cliente" v-if="cliente.accion==0" @click="guardarCliente()" type="button" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Guardar</button>
                                <button v-if="cliente.accion==1" @click="modificarCliente()" type="button" class="btn btn-success">
                                    <i class="fas fa-edit"></i>
                                    Modificar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalFoto" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="mySmallModalLabel">Foto del cliente</h5>
                        <button @click="cerrarModalFoto()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 p-3">
                                <div class="image-container">
                                    <img :src="cliente.imagen!=''?'/img/cliente/'+cliente.imagen:'/img/cliente/default.png'" alt="" >
                                </div>
                                <input style="font-size:11px;" class="form-control" type="file" name="" id="" @change="seleccionarImagen($event)">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="text-end">
                            <button @click="cerrarModalFoto()" class="btn btn-secondary">
                                <i class="fas fa-times-circle"></i>
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <!-- Add Google Maps Modal -->
        <div id="modalMapa" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mapaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="mapaModalLabel">{{ mapModalTitle }}</h5>
                        <button @click="cerrarModalMapa()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <input v-if="mapModalMode !== 'view'" type="text" v-model="searchQuery" id="searchMap" class="form-control mb-3" placeholder="Buscar ubicación...">
                                <div id="map" style="height: 400px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="cerrarModalMapa()" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cerrar
                        </button>
                        <button v-if="mapModalMode !== 'view'" @click="guardarUbicacion()" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Ubicación
                        </button>
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
                debounceTimeout: null,
                tiposDeDirecciones: [
                    'Casa',
                    'Oficina',
                    'Negocio',
                    'Apartamento',
                    'Edificio',
                    'Otra Residencia',
                    'Residencia Temporal',
                    'Domicilio Familiar',
                    'Domicilio Conyugal',
                    'Propiedad Alquilada',
                    'Propiedad Propia',
                    'Residencia Estudiantil',
                    'Habitación Compartida',
                    'Residencia Permanente',
                    'Piso',
                    'Suite',
                    'Condominio',
                    'Terreno',
                    'Local Comercial',
                    'Finca',
                    'Hacienda',
                    'Bodega',
                    'Depósito',
                    'Establecimiento Industrial',
                    'Zona Rural',
                    'Zona Urbana',
                    'Barrio',
                    'Colonia',
                    'Sector',
                    'Municipio'
                ],
                preloader:false,
                clientes_con_creditos:0,
                clientes_sin_creditos:0,
                opcion_asesor:0,
                guardando_cliente:false,
                lista_clientes:[],
                estados_civil: [{
                        nombre: 'soltero/a'
                    },
                    {
                        nombre: 'Casado/a'
                    },
                    {
                        nombre: 'divorciado/a'
                    },
                    {
                        nombre: 'viudo/a'
                    },
                    {
                        nombre: 'separado/a'
                    },
                    {
                        nombre: 'conviviente/a'
                    },

                ],

                viviendas: [{
                        nombre: 'Casa propia'
                    },
                    {
                        nombre: 'Alquiler'
                    },
                    {
                        nombre: 'Vivienda familiar'
                    },
                ],
                sexos: [{
                        nombre: 'Masculino'
                    },
                    {
                        nombre: 'Femenino'
                    },

                ],
                lugares_expedicion: [{
                        sigla: 'LP',
                        nombre: 'La Paz'
                    },
                    {
                        sigla: 'CB',
                        nombre: 'Cochabamba'
                    },
                    {
                        sigla: 'SC',
                        nombre: 'Santa Cruz'
                    },
                    {
                        sigla: 'OR',
                        nombre: 'Oruro'
                    },
                    {
                        sigla: 'PT',
                        nombre: 'Potosí'
                    },
                    {
                        sigla: 'TJ',
                        nombre: 'Tarija'
                    },
                    {
                        sigla: 'BN',
                        nombre: 'Beni'
                    },
                    {
                        sigla: 'PD',
                        nombre: 'Pando'
                    },
                    {
                        sigla: 'CH',
                        nombre: 'Chuquisaimagenfaltca'
                    },
                    {
                        sigla: 'Sin Expedición',
                        nombre: 'Sin Expedición'
                    },
                ],
                cliente: {
                    id_cliente:0,
                    nombre: '',
                    //fecha_nacimiento: moment().format('YYYY-MM-DD'),
                    fecha_nacimiento: moment().subtract(18, 'years').format('YYYY-MM-DD'), // Mayor de edad
                    ci: '',
                    ingreso_mensual:'',
                    actividad:'',
                    estado_civil: '0',
                    vivienda: '0',
                    sexo: '0',
                    lugar_expedicion: '0',
                    enviado: 0,
                    accion:0,
                    imagen:'',
                },
                cliente_validaciones: {
                    nombre: true,
                    //fecha_nacimiento: true,
                    ci: true,
                    ingreso_mensual:true,
                    actividad:true,
                    estado_civil: true,
                    vivienda: true,
                    sexo: true,
                    lugar_expedicion: true,
                },

                map: null,
                marker: null,
                searchQuery: '',
                mapModalMode: 'add', // 'add', 'view', 'update'
                mapModalTitle: '',
                currentAddressIndex: null,

                lista_direcciones: [{
                    id_direccion: 0,
                    tipo: '',
                    departamento: '',
                    ciudad: '',
                    zona: '',
                    descripcion: '',
                    referencia: '',
                    lat: null,
                    lng: null,
                }],
                lista_telefonos: [{
                    id_telefono: 0,
                    tipo: '',
                    numero: 'Numero telefono',
                    observacion: '',
                    nombre: '',
                    apellidos: '',
                    relacion: '',

                }],

                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 2,
                mensajeError: '',
                criterio:'cliente.nombre',
                buscar:'',

                vista:0,
                lista_creditos_cliente:[],
                lista_cuotas_planes:[],
                cargando: false,

                lista_asesores:[],
                lista_cantidad_clientes:[],
                filteredItemsActividades:[],
     
                actividadClase:{
                    id_actividad:0,
                    buscar:'',
                }

            }
        },
        computed:{
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
            initializeMap(lat = -17.7833, lng = -63.1821) { // Default to Santa Cruz, Bolivia
                const mapElement = document.getElementById('map');
                if (!mapElement) return;

                this.map = new google.maps.Map(mapElement, {
                    center: { lat, lng },
                    zoom: 12,
                });

                this.marker = new google.maps.Marker({
                    map: this.map,
                    position: { lat, lng },
                    draggable: this.mapModalMode !== 'view',
                });

                if (this.mapModalMode !== 'view') {
                    // Initialize Places Autocomplete
                    const input = document.getElementById('searchMap');
                    const autocomplete = new google.maps.places.Autocomplete(input);
                    autocomplete.bindTo('bounds', this.map);

                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace();
                        if (!place.geometry) return;

                        this.map.setCenter(place.geometry.location);
                        this.map.setZoom(15);
                        this.marker.setPosition(place.geometry.location);
                        this.searchQuery = place.formatted_address;
                    });

                    // Update marker position on drag
                    this.marker.addListener('dragend', () => {
                        const position = this.marker.getPosition();
                        this.updateAddressCoordinates(position.lat(), position.lng());
                    });

                    // Update marker position on map click
                    this.map.addListener('click', (event) => {
                        this.marker.setPosition(event.latLng);
                        this.updateAddressCoordinates(event.latLng.lat(), event.latLng.lng());
                    });
                }
            },


            
            abrirModalMapa(index, mode) {
                this.currentAddressIndex = index;
                this.mapModalMode = mode;
                this.searchQuery = '';

                if (mode === 'add') {
                    this.mapModalTitle = 'Agregar Ubicación';
                } else if (mode === 'view') {
                    this.mapModalTitle = 'Ver Ubicación';
                } else if (mode === 'update') {
                    this.mapModalTitle = 'Actualizar Ubicación';
                }

                $('#modalMapa').modal('show');

                this.$nextTick(() => {
                    const address = this.lista_direcciones[index];
                    const lat = parseFloat(address.lat) || -17.7833;
                    const lng = parseFloat(address.lng) || -63.1821;
                    this.initializeMap(lat, lng);
                });
            },

            cerrarModalMapa() {
                $('#modalMapa').modal('hide');
                this.map = null;
                this.marker = null;
                this.currentAddressIndex = null;
                this.searchQuery = '';
            },

            guardarUbicacion() {
                if (!this.lista_direcciones[this.currentAddressIndex].lat || !this.lista_direcciones[this.currentAddressIndex].lng) {
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Por favor, seleccione una ubicación en el mapa.',
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar',
                    });
                    return;
                }

                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Ubicación guardada',
                    showConfirmButton: false,
                    timer: 1000,
                });

                this.cerrarModalMapa();
            },

            async seleccionarActividad(item) {
                
                this.actividadClase.id_actividad = item.id;
                this.actividadClase.buscar = item.nombre;
                this.cliente.actividad=this.actividadClase.buscar;             
                this.filteredItemsActividades = [];
            
            },
            filtrarActividades(keyword) {
                if (keyword === '') {
                    this.filteredItemsActividades = [];
                    return;
                }
                this.filteredItemsActividades = this.actividades.filter(actividad =>
                    actividad.nombre.toLowerCase().includes(keyword.toLowerCase())
                );
            },
            async getActividades(){
                try{
                    const response=await axios.get('/get_actividades');
                    this.actividades=response.data;
                }catch(error){  
                    console.log(error.message);
                }
            },
            async getCantidadesClientes(){
                await axios.get('/cantidades_clientes')
                .then((response)=>{
                    console.log(response);
                    this.clientes_con_creditos=response.data.clientes_con_creditos;
                    this.clientes_sin_creditos=response.data.clientes_sin_creditos;
                })
                .catch((error)=>{
                    console.log(error,message);
                })
            },
            async getCantidadClienteAsesor(){
                await axios.get('/get_cantidad_clientes')
                .then((response)=>{
                    console.log(response);
                    this.lista_cantidad_clientes=response.data;
                })
                .catch((error)=>{
                    console.log(error,message);
                })
            },
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
            formatearFecha(fecha){
                return moment(fecha).format('DD/MM/YYYY');
            },
            async getCuotasPlanes(id_cliente) {
                this.cargando = true;
                await axios.get('/get_cuotas_planes', {
                    params: {
                    id_cliente: id_cliente
                    }
                })
                .then(response => {
                    this.lista_cuotas_planes = response.data;
                    this.cargando = false;
                  
                })
                .catch(error => {
                    console.error('Error al obtener las cuotas:', error);
                    this.lista_cuotas_planes = [];
                    this.cargando = false;
               
                });
                
            },
            async consultarCeditosCliente(){
                await axios.get('/get_creditos_cliente?id_cliente='+this.cliente.id_cliente).then((response)=>{
                    console.log(response);
                    this.lista_creditos_cliente=response.data;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },
            regresarPrincipal(){
                this.vista=0;
               
            },
            async historialCrediticio(item){
                this.cliente.id_cliente=item.id;
                this.cliente.nombre=item.nombre;
                this.cliente.ci=item.ci;
                await this.consultarCeditosCliente();
                await this.getCuotasPlanes(this.cliente.id_cliente);
                this.vista=1;
            },
            // buscarCliente(){
            //     this.getClientes(1);
            // },

            buscarCliente() {
                // Limpiar timeout anterior si aún no se ejecutaba
                if (this.debounceTimeout) {
                    clearTimeout(this.debounceTimeout);
                }

                // Establecer nuevo timeout
                this.debounceTimeout = setTimeout(() => {
                    this.getClientes(1); // Llamada real a la API después del debounce
                }, 3000); // 500 ms = 0.5 segundos sin escribir
            },
        
            imagenNoEncontrada() {
                this.cliente.imagen='default.png';
            },
            cerrarModalFoto(){
                $('#modalFoto').modal('hide');
            },
            abrirModalFoto(item){
                $('#modalFoto').modal('show');
                this.cliente.id_cliente=item.id;
                if(item.imagen!=null){
                    this.cliente.imagen=item.imagen;
                }else{
                    this.cliente.imagen='default.png';
                }
               
            },
            seleccionarImagen(event){
                    this.cliente.imagen = event.target.files[0];
                    this.agregarFoto();
                    
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Operación exitosa',
                        showConfirmButton: false,
                        timer: 1500
                    });
            },
            agregarFoto(){
           
                    const formData = new FormData();
                    formData.append('id_cliente', this.cliente.id_cliente);
                    formData.append('imagen', this.cliente.imagen);
            
                    axios.post('/fotoCliente', formData)
                    .then((response)=>{
                        console.log(response);
                        this.cliente.imagen=response.data.imagen;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        this.getClientes(1);
                    })

            },
            mostrarToastError(mensaje) {
                var miToast = new bootstrap.Toast(this.$refs.miToast);
                this.mensajeError = mensaje;
                miToast.show();
            },
           
            cambiarPagina(page){
                let me=this;
                me.pagination.current_page=page;
                me.getClientes(page);
            },
            abrirModalNuevo() {
                $('#modalCliente').modal('show');
                this.lista_direcciones= [{
                    id_direccion: 0,
                    tipo: '',
                    departamento: '',
                    ciudad: '',
                    zona: '',
                    descripcion: '',
                    referencia: '',
                    id_cliente: 0,
                    lat: null,
                    lng: null,
                    
                }];
                this.lista_telefonos= [{
                    id_telefono: 0,
                    tipo: 'Numero telefono',
                    numero: '',
                    observacion: '',
                    id_cliente: 0,
                    nombre: '',
                    apellidos: '',
                    relacion: '',
                }];

                this.cliente_validaciones= {
                    nombre: true,
                    //fecha_nacimiento: true,
                    ci: true,
                    ingreso_mensual:true,
                    actividad:true,
                    estado_civil: true,
                    vivienda: true,
                    sexo: true,
                    lugar_expedicion: true,
                };

                this.cliente={
                    id_cliente:0,
                    nombre: '',
                    // fecha_nacimiento: moment().format('YYYY-MM-DD'),
                    fecha_nacimiento: moment().subtract(18, 'years').format('YYYY-MM-DD'), // Mayor de edad
                    ci: '',
                    ingreso_mensual:'',
                    actividad:'',
                    estado_civil: '0',
                    vivienda: '0',
                    sexo: '0',
                    lugar_expedicion: '0',
                    enviado: 0,
                    accion:0,
                };

            },

            async getClientes(page){
                await axios.get('/get_clientes?page='+page+'&criterio='+this.criterio+'&buscar='+this.buscar+'&opcion_asesor='+this.opcion_asesor).then((response)=>{

                    this.lista_clientes=response.data;
                    console.log(response.data);
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },

            cerrarModalNuevo() {
                $('#modalCliente').modal('hide');
            },
            agregarDireccion(item) {
                const nuevaDireccion = {
                    id_direccion: this.lista_direcciones.length + 1, // Asigna un valor automático
                    tipo: '',
                    departamento: '',
                    ciudad: '',
                    zona: '',
                    descripcion: '',
                    referencia: '',
                    lat: null,
                    lng: null,
                };
                this.lista_direcciones.push(nuevaDireccion);
  
            },
            agregarTelefono() {
                const nuevoTelefono = {
                    id_telefono: this.lista_telefonos.length + 1, // Asigna un valor automático
                    tipo: 'Numero telefono',
                    numero: '',
                    observacion: '',
                    nombre: '',
                    apellidos: '',
                    relacion: '',
                
                };
                this.lista_telefonos.push(nuevoTelefono);
            },

            quitarDireccion(index) {
                if(this.lista_direcciones.length>1){
                    this.lista_direcciones.splice(index, 1);
                }
            },

            quitarTelefono(index) {
                if(this.lista_telefonos.length>1){
                    this.lista_telefonos.splice(index, 1);
                }
            },

            async guardarCliente() {
                try {
                    this.validarCliente();
                    this.cliente.enviado = 1;

                    const hasInvalidValidations = Object.values(this.cliente_validaciones).some(valor => !valor);
                    const hasEmptyAddressFields = this.lista_direcciones.some(d => 
                        !d.departamento || !d.descripcion || !d.tipo
                    );
                    const hasEmptyPhoneFields = this.lista_telefonos.some(t => 
                        (t.tipo === 'Informacion contacto' && (!t.nombre || !t.apellidos || !t.relacion || !t.numero)) ||
                        (t.tipo === 'Numero telefono' && (!t.numero || !t.observacion))
                    );

                    if (hasInvalidValidations || hasEmptyAddressFields || hasEmptyPhoneFields) {
                        await Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Faltan completar algunos datos!',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }

                    this.guardando_cliente = true;
                    this.cliente.direcciones = this.lista_direcciones;
                    this.cliente.telefonos = this.lista_telefonos;

                    const response = await axios.post('/save_cliente', this.cliente);

                    if (response.data.success) {
                        await Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Operación exitosa',
                            showConfirmButton: false,
                            timer: 1000
                        });
                        $('#modalCliente').modal('hide');
                        await Promise.all([
                            this.getClientes(1),
                            this.getCantidadClienteAsesor(),
                            this.getCantidadesClientes()
                        ]);
                    } else if (response.data.error === 'duplicate') {
                        await Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Cliente duplicado',
                            text: response.data.message,
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar'
                        });
                    }

                } catch (error) {
                    console.error('Error al guardar cliente:', error);

                    let errorMessage = 'Ocurrió un error al guardar el cliente';
                    if (error.response?.status === 422 && error.response?.data?.error === 'duplicate') {
                        errorMessage = error.response.data.message;
                        await Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Cliente duplicado',
                            text: errorMessage,
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        await Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                } finally {
                    this.guardando_cliente = false;
                }
            },

            modificarCliente(){
                var modificar_cliente=false;
                this.validarCliente();
                this.cliente.enviado=1;
                const algunValorEsFalse = Object.values(this.cliente_validaciones).some(valor => valor == false);
                const tieneCamposVaciosDireccion = this.lista_direcciones.some(objeto => objeto.departamento == '' || objeto.descripcion == '' || objeto.tipo == '');
                const tieneCamposVaciosTelefono = this.lista_telefonos.some(objeto => 
                    (objeto.tipo === 'Informacion contacto' && (objeto.nombre === '' || objeto.nombre === null || objeto.apellidos === '' ||  objeto.apellidos === null || objeto.relacion === '' || 
                    objeto.relacion === null || objeto.numero === '' || objeto.numero === null)) ||
                    (objeto.tipo === 'Numero telefono' && (objeto.numero === '' || objeto.observacion === '' || objeto.observacion === null || objeto.numero === null))
                );

                // Si algunValorEsFalse es true, significa que al menos uno de los valores es false
                if (algunValorEsFalse || tieneCamposVaciosDireccion || tieneCamposVaciosTelefono) {
                    console.log('no se envia');
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Faltan completar algunos datos!',
                        showConfirmButton: true,
                        textConfirmButton: 'Aceptar',
                        // timer: 1500
                    });

                }else{
                    this.cliente.direcciones=this.lista_direcciones;
                    this.cliente.telefonos=this.lista_telefonos;
                    console.log('se envia');

                    axios.post('/modify_cliente',this.cliente).then((response)=>{
                        console.log(response);
                        modificar_cliente=true;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        if(modificar_cliente==true){
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Operación exitosa',
                                showConfirmButton: false,
                                timer: 1000
                            });
                            $('#modalCliente').modal('hide');
                            this.getClientes(1);
                            this.getCantidadClienteAsesor();
                        }
                    })
                }
            },

            validarCliente(){
                
                this.cliente_validaciones.nombre=(this.cliente.nombre=='')?false:true;
                //this.cliente_validaciones.fecha_nacimiento=(this.cliente.fecha_nacimiento==moment().format('YYYY-MM-DD') || this.cliente.fecha_nacimiento=='')?false:true;
                this.cliente_validaciones.ci=(this.cliente.ci=='')?false:true;
                this.cliente_validaciones.actividad=(this.cliente.actividad=='')?false:true;
                this.cliente_validaciones.ingreso_mensual=(this.cliente.ingreso_mensual=='')?false:true;
                this.cliente_validaciones.estado_civil=(this.cliente.estado_civil==''|| this.cliente.estado_civil=='0')?false:true;
                this.cliente_validaciones.vivienda=(this.cliente.vivienda==''|| this.cliente.vivienda=='0')?false:true;
                this.cliente_validaciones.sexo=(this.cliente.sexo==''|| this.cliente.sexo=='0')?false:true;
                this.cliente_validaciones.lugar_expedicion=(this.cliente.lugar_expedicion==''|| this.cliente.lugar_expedicion=='0')?false:true;

                
            },
            activarCliente(item){
                axios.get('/activar_cliente?id_cliente='+item.id).then((response)=>{
                    console.log(response);
                    
                })
                .catch(()=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    this.getClientes(1);
                })
            },
            desactivarCliente(item){
                axios.get('/desactivar_cliente?id_cliente='+item.id).then((response)=>{
                    console.log(response);
                    
                })
                .catch(()=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    this.getClientes(1);
                })
            },
            async editarCliente(item){
                this.cliente.enviado=0;
                this.lista_direcciones=[];
                this.lista_telefonos=[];
                this.cliente.id_cliente=item.id;
                this.cliente.ci=item.ci;
                this.cliente.nombre=item.nombre;
                this.cliente.fecha_nacimiento=item.fecha_nacimiento;
                this.cliente.vivienda=item.vivienda;
                this.cliente.estado_civil=item.estado_civil;
                this.cliente.lugar_expedicion=item.lugar_expedicion;
                this.cliente.sexo=item.sexo;
                this.cliente.actividad=item.actividad;
                this.cliente.ingreso_mensual=item.ingreso_mensual;
                this.cliente.accion=1;

                this.preloader=true;
                await this.getDireccionTelefonoCliente();
                this.preloader=false;
                $('#modalCliente').modal('show');
            },

            async verCliente(item){
                this.lista_direcciones=[];
                this.lista_telefonos=[];
                this.cliente.id_cliente=item.id;
                this.cliente.ci=item.ci;
                this.cliente.nombre=item.nombre;
                this.cliente.fecha_nacimiento=item.fecha_nacimiento;
                this.cliente.vivienda=item.vivienda;
                this.cliente.estado_civil=item.estado_civil;
                this.cliente.lugar_expedicion=item.lugar_expedicion;
                this.cliente.sexo=item.sexo;
                this.cliente.actividad=item.actividad;
                this.cliente.ingreso_mensual=item.ingreso_mensual;
                this.cliente.accion=2;
                this.preloader=true;
                await this.getDireccionTelefonoCliente();
                this.preloader=false;
                $('#modalCliente').modal('show');
            },

            async getDireccionTelefonoCliente(){
                await axios.get('/get_direcciones_telefono?id_cliente='+this.cliente.id_cliente).then((response)=>{
                    this.lista_direcciones=response.data.direcciones;
                    this.lista_telefonos=response.data.telefonos;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },


            async verInformacionCliente(item) {
                // Mostrar animación de carga
                this.preloader = true;

                try {
                    // Construye la URL con el parámetro id_cliente
                    const url = '/clientes_pdf';

                    // Realizar la solicitud GET con Axios
                    const response = await axios.get(url, {
                        params: { id_cliente: item.id },
                        responseType: 'blob' // Especificamos que esperamos un blob (para PDF)
                    });

                    // Crear un objeto URL para el blob recibido
                    const blob = new Blob([response.data], { type: 'application/pdf' });
                    const fileURL = window.URL.createObjectURL(blob);

                    // Abrir el PDF en una nueva pestaña
                    window.open(fileURL, '_blank');
                } catch (error) {
                    console.error('Error al obtener el PDF del cliente:', error);
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Hubo un error al generar el reporte!; Error: '+error.message,
                        showConfirmButton: true,
                        textConfirmButton: 'Aceptar',
                        // timer: 1500
                    });
                    // Aquí puedes manejar el error, por ejemplo, mostrando un mensaje al usuario
                    
                } finally {
                    // Ocultar animación de carga
                    this.preloader = false;
                }
            },

            async inicializarDatos() {
                try {
                await this.getClientes(1);
                await this.getAsesores();
                await this.getCantidadClienteAsesor();
                await this.getCantidadesClientes();
                await this.getActividades();

                } catch (error) {
                    console.error('Error al cargar los datos iniciales:', error);
                // Aquí puedes manejar el error, por ejemplo, mostrar un mensaje al usuario
                } finally {
                    this.preloader = false;
                }
            }
        },
        async mounted() {
            console.log('Component mounted.');
            this.preloader = true;
            await this.inicializarDatos();
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


<style scoped>
/* Existing styles... */
.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

#map {
    height: 400px;
    width: 100%;
    border: 1px solid #ccc;
}

.modal-lg {
    max-width: 800px;
}

.btn-sm {
    font-size: 12px;
    padding: 5px 10px;
}
</style>