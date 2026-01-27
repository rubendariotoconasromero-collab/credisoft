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
                                <h4 class="mb-0 font-size-18 text-uppercase">Gestión de cartera de creditos - vigentes y vencidos</h4>
                                <ol class="breadcrumb">
                                </ol>
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
                                            <div class="col-md-3">
                                                <label for="opcion_asesor" class="text-dark">Asesor</label>
                                                <select @change="buscarPlanPago()" v-model="opcion_asesor" class="form-control form-control-sm form-select form-select-sm">
                                                    <option value="0">Todos los asesores</option>
                                                    <option v-for="(item, index) in lista_asesores" :key="index" :value="item.id">
                                                        {{ item.personal }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="criterio" class="text-dark">Criterio de Búsqueda</label>
                                                <select @change="buscarPlanPago()" v-model="criterio" class="form-control form-control-sm form-select form-select-sm">
                                                    <option value="plan_pago.id">Cod. Credito</option>
                                                    <option value="cliente.nombre">Nombre cliente</option>
                                                    <option value="cliente.ci">CI</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="estado" class="text-dark">Estado del Crédito</label>
                                                <select @change="buscarPlanPago()" v-model="estado_credito" class="form-control form-control-sm form-select form-select-sm">
                                                    <option value="todos">Todos</option>
                                                    <option value="vigentes">Vigentes</option>
                                                    <option value="vencidos">Vencidos</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="buscar" class="text-dark">Buscar</label>
                                                <div class="input-group">
                                                    <input v-model="buscar" type="text" class="form-control form-control-sm" @input="buscarPlanPago()">
                                                    <button class="btn btn-success btn-sm" @click="buscarPlanPago">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-uppercase py-0">
                                                <h5>
                                                    <span style="border-radius:0" class="badge bg-danger fw-bold">Total creditos:  </span>
                                                    <span style="border-radius:0" class="badge text-danger">{{ lista_cantidad_clientes.reduce((total, item) => total + item.cantidad_creditos, 0) }} Creditos registrados </span>
                                                </h5>
                                            </div>
                                        </div>

                                        

                                        <div class="row">
                                            <h6>
                                                Cartera de creditos por Asesor
                                            </h6>
                                            <div class="col-md-4 text-uppercase" v-for="(item, index) in lista_cantidad_clientes" :key="index">
                                                <h5>
                                                    <!-- <span style="border-radius:0" class="badge bg-success">Asesor:  </span> -->
                                                    <span style="border-radius:0" class="badge bg-success fw-bold">{{item.personal}}:  </span>
                                                    <span style="border-radius:0" class="badge text-success">{{ item.cantidad_creditos }} Creditos. </span>
                                                </h5>
                                            </div>
                                        </div>

                                        <div class="row my-2">
                                            <div class="col-md-12 text-center">
                                                <button @click="exportExcelPlanPagos()" class="btn btn-success btn-sm">
                                                    <i class="fas fa-file-excel"></i>
                                                    Exportar excel
                                                </button>
                                            </div>
                                        </div>



                                        <!-- <h4 class="card-title text-uppercase mt-3">Listado de Planes de Pago</h4> -->
                                        <div class="table-responsive text-uppercase" style="font-size:10px;">
                                            <table class="table mb-4 table-hover table-striped table-sm table-bordered">
                                                <thead class="text-uppercase text-white bg-primary">
                                                    <tr style="background-color:#52BE80; vertical-align: middle">
                                                        <th width="7%">Credito</th>
                                                        <th width="15%">Cliente</th>
                                                        <th width="15%">Codeudores</th>
                                                        <th width="10%">Fecha Des.</th>
                                                        <th width="8%">Monto</th>
                                                        <th width="12%">Asesor</th>
                                                        <th width="10%">F. inicio</th>
                                                        <th width="10%">F. fin</th>
                                                        <th width="10%">Cuotas</th>
                                                        <th width="5%">Estado</th>
                                                        <th width="5%">Op.</th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <tr class="" v-for="item in lista_planespago" :key="item.id" style="vertical-align: middle">

                                                        <td>{{ item.id }}</td>
                                                        <td>{{ item.cliente }}</td>
                                                        <td>

                                                            <p class="mb-0 mt-0" v-for="(item, index) in lista_codeudores_tabla.filter(codeudor => codeudor.id_solicitud === item.id_solicitud)" :key="index">
                                                                * {{item.nombre}}
                                                            </p>
                                                        </td>

                                                        <td>{{ item.fecha_desembolso }}</td>
                                                        <td>{{item.total_pagar_plan}}</td>
                                                        <td>{{ item.asesor }}</td>

                                                        <td>{{item.fecha_inicio_plan}}</td>
                                                        <td :style="(item.estado_plan==10)?'text-decoration: line-through;':''">{{item.fecha_fin_plan}}</td>
                                                        <td :style="(item.estado_plan==10)?'text-decoration: line-through;':''">
                                                  
                                                            <small style="font-size:10px;">
                                                                {{item.nro_cuotas}}
                                                                <small style="font-size:10px;">
                                                                    ({{item.lapso_capital}})
                                                                </small>
                                                            </small>
                                                        </td>

                                                        <td>
                                                            <span v-if="(item.estado_plan==1 && item.fecha_fin_plan>=fecha_actual)" class="badge text-bg-success d-inline-block mt-1">Nuevo</span>
                                                            <span v-if="(item.estado_plan==1 && item.fecha_fin_plan<fecha_actual)" class="badge text-bg-danger d-inline-block mt-1">Vencido</span>
                                                            <span v-else-if="item.estado_plan==0" class="badge text-bg-dark d-inline-block mt-1">Anulado</span>
                                                            <span v-else-if="item.estado_plan==2" class="badge text-bg-success d-inline-block mt-1">Completado</span>
                                                            <span v-else-if="item.estado_plan==10" class="badge text-bg-info d-inline-block mt-1">Amortizado</span>

                                                            <span v-if="item.desembolso==1" class="badge text-bg-warning d-inline-block mt-1">Sin Desemb.</span>
                                                          
                                                        </td>

                                                        <td>
                                                            <div class="btn-group my-0 py-0">
                                                                <a style="cursor:pointer;"
                                                                    class="text-success dropdown-toggle btn-sm my-0 py-0 text-center"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-ellipsis-h fa-lg fa-fw fs-3"></i>
                                                                </a>
                                                                <ul class="dropdown-menu my-0 py-0">
                                                                    <li v-if="item.estado_plan==1">
                                                                        <a @click="anularPlanPago(item)" class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-times"></i> Anular</a>
                                                                    </li>
                                                                    <li  v-else-if="item.estado_plan==0"><a
                                                                            @click="activarPlanPago(item)"
                                                                            class="dropdown-item text-success" href="#">
                                                                            <i class="fas fa-check"></i> Activar</a>
                                                                    </li>
                                                                    <li @click="abrirModalVerCuotas(item)"><a
                                                                            class="dropdown-item text-info" href="#">
                                                                            <i class="fas fa-info me-1"></i> Ver cuotas</a>
                                                                    </li>
                                                                    <li @click="generarContrato(item)"><a
                                                                            class="dropdown-item text-success" href="#">
                                                                            <i class="fas fa-file-contract"></i> Generar contrato</a>
                                                                    </li>

                                                                </ul>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                </tbody>
                                            </table>
                                            <template v-if="lista_planespago.length<=3">
                                                <br>
                                                <br>
                                                <br>
                                                <br>
                                                <br>
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


        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalCuotas" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl"  style="width:90%; max-width:90%;">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-uppercase text-white" id="miModalLabel">Información del plan de pago | COD: {{ plan_pago.id_plan_pago }} | cliente: {{ plan_pago.cliente }}</h5>

                        <button @click="cerrarModalCuotas()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                            <div class="row mb-3">
                             
                                <div class="col text-center">

                                    <a @click="generarPdfCuotasPlanPago()" class="btn btn-info btn-sm mx-2">
                                        <i class="fas fa-file-pdf"></i>
                                        Generar PDF</a>
                                    <a v-if="plan_pago.estado_plan==1" @click="amortizarPlanPago()" class="btn btn-warning btn-sm mx-2">
                                        <i class="fas fa-chart-line"></i>
                                        Amortizar</a>
                                    <a v-else-if="plan_pago.estado==2"  class="btn btn-danger btn-sm mx-2 text-white">
                                        Cancelado</a>
                                </div>


                            </div>


                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="border p-3 rounded">
                                        <h6 class="text-success mb-3">Información del Cliente</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Cliente:</span>
                                            <span>{{ plan_pago.cliente }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">CI:</span>
                                            <span>{{ plan_pago.ci + ' ' + plan_pago.lugar_expedicion }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Garantía:</span>
                                            <span>{{ plan_pago.tipo_garantia }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border p-3 rounded">
                                        <h6 class="text-success mb-3">Detalles del Plan</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Plazo:</span>
                                            <span>{{ plan_pago.nro_cuotas + ' ' + plan_pago.lapso_capital }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Monto Desembolso:</span>
                                            <span>{{ plan_pago.total_pagar_plan + ' ' + plan_pago.moneda }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Forma de Pago:</span>
                                            <span>{{ plan_pago.lapso_capital }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border p-3 rounded">
                                        <h6 class="text-success mb-3">Fechas y Estado</h6>

                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Inicio:</span>
                                            <span>{{ plan_pago.fecha_inicio_plan }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Fin:</span>
                                            <span>{{ plan_pago.fecha_fin_plan }}</span>
                                        </div>

                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted">Estado:</span>
                                            <h6 class="text-uppercase fw-bold" :class="{
                                                        'text-success': plan_pago.estado_plan == 1,  // Activo
                                                        'text-dark': plan_pago.estado_plan == 0,   // Anulado
                                                        'text-danger': plan_pago.estado_plan == 2,  // Cancelado
                                                        'text-warning': plan_pago.estado_plan == 10  // Amortizado
                                                    }">{{ gestionarEstado(plan_pago.estado_plan) }}</h6>
                                        </div>
                                      
                                    </div>
                                </div>
                            </div>

                            


                            <div class="row" v-if="lista_cuotas_plan.filter(cuota => cuota.amortizado !==1).length>0">
                                <div class="col-md-12">
                                    <div class="table-responsive" style="font-size:12px;">
                                        <table class="table mb-4 table-bordered table-sm table-hover">
                                                <thead class="text-white bg-success">
                                                    <tr>
                                                        <th>Nro</th>
                                                        <th>Fecha</th>
                                                        <th>Capital</th>
                                                        <th>Interes</th>
                                                        <th>Saldo capital</th>
                                                        <th>Total Bs</th>
                                                        <th>Estado</th>
                                                        <!-- <th>Opciones</th> -->

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template v-for="(cuota, index) in lista_cuotas_plan" :key="index">
                                                     
                                                        <tr style="vertical-align: middle;"
                                                            v-for="(amortizacion, idx) in cuota.amortizaciones"
                                                            :key="idx"
                                                            class="table-white"
                                                            >
                                                            <td style="width:10%;" class="bg-secondary text-white border border-light border-1 py-0">
                                                                Amortización
                                                            </td>
                                                           
                                                            <td class="p-0 border border-light border-1">
                                                                <div class="card p-0 m-0">
                                                                    <div class="small-title text-white bg-secondary card-header p-1 py-0 m-0">Fecha P.:</div>
                                                                    <div class="cell-value card-body p-1 py-0 m-0">{{ formatFecha(amortizacion.fecha) }}</div>
                                                                </div>
                                                            </td>

                                                            <td class="p-0 border border-light border-1">
                                                                <div class="card p-0 m-0">
                                                                    <div class="small-title text-white bg-secondary card-header p-1 py-0 m-0">Capital P.:</div>
                                                                    <div class="cell-value card-body p-1 py-0 m-0">{{ formatNumero(amortizacion.capital_pagado) }}</div>
                                                                </div>
                                                            </td>

                                                            <td class="p-0 border border-light border-1">
                                                                <div class="card p-0 m-0">
                                                                    <div class="small-title text-white bg-secondary card-header p-1 py-0 m-0">Interés P.:</div>
                                                                    <div class="cell-value card-body p-1 py-0 m-0">{{ formatNumero(amortizacion.interes_pagado) }}</div>
                                                                </div>
                                                            </td>

                                                            <td class="p-0 border border-light border-1">
                                                                <div class="card p-0 m-0">
                                                                    <div class="small-title text-white bg-secondary card-header p-1 py-0 m-0">Multa P.:</div>
                                                                    <div class="cell-value card-body p-1 py-0 m-0">{{ formatNumero(amortizacion.multa_pagada) }}</div>
                                                                </div>
                                                            </td>

                                                            <td class="p-0 border border-light border-1">
                                                                <div class="card p-0 m-0">
                                                                    <div class="small-title text-white bg-secondary card-header p-1 py-0 m-0">Saldo Capital:</div>
                                                                    <div class="cell-value card-body p-1 py-0 m-0">{{ formatNumero(amortizacion.saldo_pendiente) }}</div>
                                                                </div>
                                                            </td>

                                                            <td class="p-0 border border-light border-1">
                                                                <div class="card p-0 m-0">
                                                                    <div class="small-title text-white bg-secondary card-header p-1 py-0 m-0">Monto P.:</div>
                                                                    <div class="cell-value card-body p-1 py-0 m-0">{{ formatNumero(amortizacion.monto_pago) }}</div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ cuota.numero }}</td>
                                                            <td>{{ formatFecha(cuota.fecha) }}</td>
                                                            <td>{{ formatNumero(cuota.capital) }}</td>
                                                            <td>{{ formatNumero(cuota.interes) }}</td>

                                                            <td>{{ formatNumero(cuota.saldo_capital) }}</td>
                                                            <td>{{ formatNumero(cuota.total) }}</td>
                                                            <td style="width:10%;">
                                                                <div v-if="esPrimerRegistroConMora(index) && cuota.dias_pasados!=null">
                                                                    <span class="badge bg-danger text-white text-uppercase badge-fixed-width">{{ cuota.dias_pasados }} - en mora</span>
                                                                </div>
                                                                <span v-if="cuota.estado == 1" class="badge bg-warning text-uppercase badge-fixed-width">Por pagar</span>
                                                                <span v-else-if="cuota.estado == 2" class="badge bg-success text-uppercase badge-fixed-width">Cancelado</span>
                                                                <span v-else-if="cuota.estado == 0" class="badge bg-dark text-uppercase badge-fixed-width">Anulado</span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                        </table>
                                        <br>
                                    </div>
                                </div>
                            </div>

                        
                            <div class="row" v-if="lista_cuotas_plan.filter(cuota => cuota.amortizado !==1).length<=0 && lista_amortizaciones.length>0">
                                <div class="col-md-12">
                                    <h6>
                                        Plan de pago amortizado.
                                    </h6>
                                </div>
                            </div>

                        
                            <hr class="border border-success border-4">

                            <div v-if="lista_planes_pago_ligados.length>0" class="row">
                                <div class="col-md-12 text-center">
                                    <h5>Planes de pago amortizados</h5>
                                </div>
                            </div>

                            <div class="row">


                                <div class="col-md-12 mt-3" v-for="(item, index) in lista_planes_pago_ligados" :key="index">



                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col text-center">
                                                <a v-if="item.estado_plan==1" @click="amortizarPlanPagoPorPlan(item)" class="btn btn-warning btn-sm mx-2">
                                                    <i class="fas fa-chart-line"></i>
                                                    Amortizar</a>
                                                <a v-else-if="item.estado_plan==2"  class="btn btn-danger btn-sm mx-2 text-white">
                                                    Cancelado</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 mt-3">
                                       
                                        <div class="col-md-6">
                                            <div class="border p-3 rounded">
                                                <h6 class="text-success mb-3">Detalles del Plan</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Plazo:</span>
                                                    <span>{{ item.nro_cuotas + ' ' + item.lapso_capital }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Monto Desembolso:</span>
                                                    <span>{{ item.total_pagar_plan + ' ' + item.moneda }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Forma de Pago:</span>
                                                    <span>{{ item.lapso_capital }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border p-3 rounded">
                                                <h6 class="text-success mb-3">Fechas y Estado</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Nro. Cuotas:</span>
                                                    <span>{{ item.nro_cuotas }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Inicio:</span>
                                                    <span>{{ item.fecha_inicio_plan }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Fin:</span>
                                                    <span>{{ item.fecha_fin_plan }}</span>
                                                </div>


                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold text-muted">Estado:</span>
                                                    <h6 class="text-uppercase fw-bold" :class="{
                                                                'text-success': item.estado_plan == 1,  // Activo
                                                                'text-dark': item.estado_plan == 0,   // Anulado
                                                                'text-danger': item.estado_plan == 2,  // Cancelado
                                                                'text-warning': item.estado_plan == 10  // Amortizado
                                                            }">{{ gestionarEstado(item.estado_plan) }}</h6>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                               
                                    <div class="row" v-if="item.cuotas.filter(cuota => cuota.amortizado !==1).length>0">
                                        <div class="col-md-12">
                                            <div class="table-responsive" style="font-size:12px;">
                                                <table class="table mb-4 table-bordered table-sm">
                                                        <thead class="text-white" style="background-color: #52BE80">
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

                                                         
                                                            <tr style="vertical-align: middle"
                                                                    v-for="(item_cuota, index) in item.cuotas.filter(cuota => cuota.amortizado !==1)" :key="index">

                                                                <td>{{ item_cuota.numero==null?'Amortizacion':item_cuota.numero}}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">fecha pago</p>{{ item_cuota.fecha }}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">capital pagado</p>{{ item_cuota.capital }}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">interes pagado</p>{{ item_cuota.interes }}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">saldo capital</p>{{ item_cuota.saldo_capital }}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">multa pagada</p>{{ item_cuota.ahorro }}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">- - - -</p>{{ item_cuota.seguro }}</td>
                                                                <td><p v-if="item_cuota.numero==null" class="m-0 p-0">total pagado</p>{{ item_cuota.total }}</td>
                                                                <td class="text-center">
                                                                    <div v-if="esPrimerRegistroConMoraPorPlan(index, item)">
                                                                        <span class="badge bg-danger text-white text-uppercase">{{ item_cuota.dias_pasados }} - en mora</span>
                                                                    </div>
                                                                    <div v-if="item_cuota.estado == 1">
                                                                        <span class="badge bg-warning text-white text-uppercase">Por pagar</span>
                                                                    </div>
                                                                    <div v-else-if="item_cuota.estado == 0">
                                                                        <span class="badge bg-dark text-uppercase text-white">Anulado</span>
                                                                    </div>
                                                                    <div v-else-if="item_cuota.estado == 2">
                                                                        <span class="badge bg-success text-uppercase text-white">Cancelado</span>
                                                                    </div>

                                                                    <div v-if="item_cuota.numero == null">
                                                                        <span class="badge bg-info text-uppercase text-white">Amortización</span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                </table>
                                                <br>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row" v-if="item.cuotas.filter(cuota => cuota.amortizado !==1).length<=0 && item.amortizaciones.length>0">
                                        <div class="col-md-12">
                                            <h6>
                                                Plan de pago amortizado.
                                            </h6>
                                        </div>
                                    </div>

                                    <!-- lista de amortizaciones -->

                                    <div v-if="item.amortizaciones.length>0" class="row">
                                        
                                        <div class="col-md-12">
                                            <!-- <div class="col-md-12 text-center"> -->
                                                <h6>Amortizacion crédito | codigo: {{  item.amortizaciones[0].id}}</h6>
                                            <!-- </div> -->
                                        </div>

                                        <div class="col-md-12">
                                            <div class="table-responsive" style="font-size:12px;">
                                                <table class="table mb-4 table-bordered table-sm">
                                                        <thead class="text-white" style="background-color: #52BE80">
                                                            <tr>
                                                                <th>Fecha pago</th>
                                                                <th>Capital Pagado</th>
                                                                <th>Interes Pagado</th>
                                                                <th>Saldo capital</th>
                                                                <th>Multa Pagada</th>
                                                                <th>Total Seguro</th>
                                                                <th>Total Pagado</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr
                                                                v-for="(item, index) in item.amortizaciones" :key="index">
                                                                <td>{{ item.fecha }}</td>
                                                                <td>{{ item.capital_pagado }}</td>
                                                                <td>{{ item.interes_pagado }}</td>
                                                                <td>{{ item.saldo_pendiente }}</td>
                                                                <td>{{ item.multa_pagada }}</td>
                                                                <td>{{ item.total_seguro }}</td>
                                                                <td>{{ item.monto_pago }}</td>
                                                            </tr>
                                                        </tbody>
                                                </table>
                                                <br>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="border border-success border-4">
                                </div>

                            </div>

                    </div>

                    <div class="modal-footer">
                            <button @click="cerrarModalCuotas()" type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>



        <div class="modal fade" id="modalPagoCuota" tabindex="-1" aria-labelledby="exampleModalPopoversLabel" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="container rounded border border-4 border-success">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalPopoversLabel">Registro de pago de cuota</h5>
                            <button @click="cerrarModalPago()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-5 ">
                                    <strong class="">
                                        CLIENTE:
                                    </strong>
                                </div>
                                <div class="col-md-7">
                                    <p class="text-uppercase">
                                        {{ plan_pago.cliente }}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 ">
                                    <strong class="">
                                        PLAN PAGO #:
                                    </strong>
                                </div>
                                <div class="col-md-7">
                                    <p class="text-uppercase">
                                        {{ plan_pago.id_plan_pago }}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 ">
                                    <strong class="">
                                        CUOTA #:
                                    </strong>
                                </div>
                                <div class="col-md-7">
                                    <p class="text-uppercase">
                                        {{ cuota.numero +' / '+plan_pago.nro_cuotas}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 ">
                                    <strong class="">
                                        MONTO A CANCELAR Bs.:
                                    </strong>
                                </div>
                                <div class="col-md-7">
                                    <p class="text-uppercase">
                                        {{ cuota.total }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="cerrarModalPago()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button @click="pagarCuota()" type="button" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalVerPago" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="container border border-3 border-danger">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mySmallModalLabel">Datos liquidación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
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
                            <button @click="cerrarModalVerPago()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button @click="anularCuota()" type="button" class="btn btn-danger">Anular</button>
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>


        <div id="modalAmortizacion" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="width:95%; max-width:95%;">
                <div class="modal-content border border-2 border-success">
                    <div class="">
                        <div class="modal-header bg-success">
                            <h5 class="modal-title text-white" id="mySmallModalLabel">
                                <strong>
                                    CREDITO: {{ amortizacion.id_plan_pago }} | CLIENTE: {{ amortizacion.cliente }}
                                </strong>
                            </h5>
                            <button @click="cerrarModalAmortizacion()" type="button" class="btn-close btn-close-white"  aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="row">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                Saldo capital ultimo pago:
                                            </strong>
                                        </label>
                                        <p class="col-md-6">
                                            {{amortizacion.saldo_capital_ultimo_pago}}
                                        </p>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                interes cuotas Bs:
                                            </strong>
                                        </label>
                                        <p class="col-md-6">
                                            {{amortizacion.interes_cuotas_acumuladas}}
                                            (
                                                cuotas:

                                                <template v-for="(item, index ) in lista_cuotas_amortizacion.filter(objeto => objeto.estado === 1 && objeto.fecha < obtenerFechaActual() &&  objeto.amortizado === 0)" :key="index">
                                                    {{ item.numero }}[{{ item.interes }}]
                                                    <template v-if="index+1!=lista_cuotas_amortizacion.filter(objeto => objeto.estado === 1 && objeto.fecha < obtenerFechaActual() &&  objeto.amortizado === 0).length">
                                                        ,
                                                    </template>
                                                </template>
                                            )
                                        </p>
                                    </div>

                                    <div class="row">
                                        <label v-if="amortizacion.hay_cuotas_sin_pagar" class="col-md-6 text-end">
                                            <strong>
                                                Interes dias restantes:
                                            </strong>
                                        </label>
                                        <label v-if="!amortizacion.hay_cuotas_sin_pagar" class="col-md-6 text-end">
                                            <strong>
                                                Interes dias transcurridos:
                                            </strong>
                                        </label>

                                        <p class="col-md-6">

                                    
                                            
                                            <template v-if="amortizacion.hay_cuotas_sin_pagar">
                                                dia {{amortizacion.dias_restantes}} de {{ amortizacion.dias_entre_ultima_proxima_cuota}} = {{ amortizacion.dias_entre_ultima_proxima_cuota -  amortizacion.dias_restantes}} dias restantes
                                            </template>
                                            <template v-else-if="!amortizacion.hay_cuotas_sin_pagar">
                                                {{amortizacion.dias_restantes}} despues de la ult. cuota
                                            </template>

                                        </p>
                                    </div>
                                    <div v-if="amortizacion.dias_entre_ultima_proxima_cuota>0" class="row">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                Interes por dias:
                                            </strong>
                                        </label>
                                        <p class="col-md-6">
                                            <!-- {{amortizacion.dias_restantes}} x {{ parseFloat(amortizacion.interes_dias).toFixed(2) }} = {{ parseFloat(amortizacion.total_interes_dias).toFixed(2) }} -->
                                            {{amortizacion.dias_restantes }} x {{ parseFloat(amortizacion.interes_dias).toFixed(2) }} = {{ parseFloat(amortizacion.total_interes_dias).toFixed(2) }} Bs.

                                        </p>
                                    </div>

                                    <div v-else class="row">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                Interes por dias hasta la fecha:
                                            </strong>
                                        </label>
                                        <p class="col-md-6">
                                            <!-- {{amortizacion.dias_restantes}} x {{ parseFloat(amortizacion.interes_dias).toFixed(2) }} = {{ parseFloat(amortizacion.total_interes_dias).toFixed(2) }} -->
                                            {{ amortizacion.dias_restantes }} x {{ parseFloat(amortizacion.interes_dias).toFixed(2) }} = {{ parseFloat(amortizacion.total_interes_dias).toFixed(2) }} Bs.

                                        </p>
                                    </div>

                                    <div class="row d-flex ">
                                        <label class="col-md-6 text-end mt-2 justify-content-center">
                                            <strong>
                                                TOTAL INTERES Bs:
                                            </strong>
                                        </label>
                                        <div class="col-md-6">
                                            <input :disabled="true" @input="ajustarMontoLiquidacion()" type="text" class="form-control" v-model="amortizacion.total_interes">
                                        </div>
                                    </div>



                                    <div class="row d-flex align-items-center my-3">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                MORA:
                                            </strong>
                                        </label>
                                        <div class="col-md-6">


                                            <div v-if="amortizacion.tiene_mora" class="input-group d-flex align-items-top">
                                                <label for="multaDias" class="form-label mx-1 mt-1">DIAS</label>
                                                <input :disabled="true" type="text" class="form-control form-control-sm" id="multaDias" v-model="amortizacion.multa_dias">
                                                <label for="multaDias" class="form-label mx-1 ms-1 mt-1">X</label>
                                                <input @input="ajustarMontoMulta()" type="text" class="form-control form-control-sm" id="monto" v-model="amortizacion.monto_multa">
                                                <label for="multaDias" class="form-label mx-1 ms-1 mt-1">=</label>
                                                <label  class="form-label mx-1 ms-1 mt-1">{{  amortizacion.monto_multa_total}}</label>
                                            </div>
                                            <label v-else class="col-md-6">
                                                    SIN MORA
                                            </label>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <label class="col-md-6 text-end fw-bold">
                                                TOTAL LIQUIDACIÓN Bs:
                                        </label>
                                        <p class="col-md-6">
                                            <strong style="font-size:17px">
                                                {{ amortizacion.total_liquidacion }}
                                            </strong>
                                        </p>
                                    </div>

                                    <div class="row">
                                        <label class="col-md-12 text-center">
                                            <strong style="font-size:16px;">
                                                DATOS DE AMORTIZACION
                                            </strong>
                                        </label>
                                    </div>

                                    <div class="row mb-3 d-flex align-items-center">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                Forma pago:
                                            </strong>
                                        </label>
                                        <div class="col-md-6">
                                            <select v-model="amortizacion.forma_pago" class="form-control" >
                                                <option v-for="(item, index) in formas_pago" :key="index" :value="item.nombre">{{ item.nombre }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row d-flex align-items-center">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                AMORTIZACION:
                                            </strong>
                                        </label>
                                        <div class="col-md-6">
                                            <input style="font-size:18px;" type="text" class="form-control" v-model="amortizacion.amortizacion">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <label class="col-md-6 text-end">
                                            <strong>
                                                SALDO CAPITAL NUEVO:
                                            </strong>
                                        </label>
                                        <label class="col-md-6 text-danger">
                                            <strong style="font-size:17px">
                                                {{ (parseFloat(amortizacion.saldo_capital_nuevo)-(isNaN(parseFloat(amortizacion.amortizacion))?0:parseFloat(amortizacion.amortizacion))).toFixed(2)}} Bs.
                                            </strong>
                                        </label>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 text-end">
                                            <label for="liquidar_deuda" class="text-end">Liquidar deuda</label>

                                        </div>
                                        <div class="col-md-6 text-start">
                                            <input class="mt-1" type="checkbox" v-model="amortizacion.liquidar_deuda">
                                        </div>
                                    </div>

                                    <div class="modal-footer text-center d-flex justify-content-center">
                                        <button  @click="gestionarPlanPago()" type="button" class="btn btn-success" >Gestionar plan pago</button>
                                    </div>
                                </div>

                                <div class="col-md-7" style="border-left: 2px solid #0dcaf0;" :class="{ 'disabled': bloqueado }">
                                    <div v-if="bloqueado"  class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>ADVERTENCIA</strong> Debe gestionar PLAN PAGO primero.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="importe_solicitud" class="form-label">Importe solicitud</label>
                                                <input :disabled="true" v-model="solicitud.importe_solicitud" type="text" class="form-control" id="importe_solicitud" name="importe_solicitud" onkeydown="if(event.key==='.' && event.target.value.includes('.')){event.preventDefault();}" oninput="event.target.value = event.target.value.replace(/[^0-9.]*/g,'');">
                                                <p class="text-danger text-sm-start" v-if="(solicitud.importe_solicitud==0 || solicitud.importe_solicitud=='') && solicitud.enviado==1">Ingrese un importe *</p>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="lapso_capital" class="form-label">Lapso capital</label>
                                                            <select @change="(tipo_tasa=='fija')? seleccionarLapsoCapital():''" :disabled="true" v-model="solicitud.lapso_capital" class="form-control" id="lapso_capital" name="lapso_capital" required>
                                                                <option value="0" selected hidden disabled>Seleccione...</option>
                                                                <option v-for="item in lapso_capitales" :value="item.nombre" :key="item.nombre">
                                                                    {{ item.nombre }}
                                                                </option>
                                                            </select>
                                                            <p class="text-danger text-sm-start" v-if="(solicitud.lapso_capital=='0' || solicitud.lapso_capital=='') && solicitud.enviado==1">Seleccione una opción *</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="nro_cuotas" class="form-label">Nro. cuotas</label>
                                                            <!-- <input :disabled="bloqueado" v-model="solicitud.nro_cuotas" type="number" class="form-control" id="nro_cuotas" name="nro_cuotas" required> -->
                                                            <input :disabled="true" v-model="solicitud.nro_cuotas" type="number" class="form-control" id="nro_cuotas" name="nro_cuotas" required>
                                                            <p class="text-danger text-sm-start" v-if="solicitud.nro_cuotas=='' && solicitud.enviado==1">Ingrese nro cuotas *</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="tasa" class="form-label">Tasa %</label>
                                                        <!-- <input :disabled="bloqueado" v-model="solicitud.tasa" type="text" class="form-control" id="tasa" name="tasa" required onkeydown="if(event.key==='.'){event.preventDefault();}" oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');"> -->
                                                        <input :disabled="true" v-model="solicitud.tasa" type="text" class="form-control" id="tasa" name="tasa" required onkeydown="if(event.key==='.'){event.preventDefault();}" oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');">

                                                        <p class="text-danger text-sm-start" v-if="solicitud.tasa=='' && solicitud.enviado==1">Ingrese tasa *</p>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="tipo_desembolso" class="form-label">Tipo desembolso</label>
                                                        <select :disabled="true" v-model="solicitud.tipo_desembolso" class="form-control" id="tipo_desembolso" name="tipo_desembolso" required>
                                                            <option value="0" selected hidden disabled>Seleccione</option>
                                                            <option value="Amortización">Amortización</option>
                                                            
                                                            <option v-for="item in tipos_desembolsos" :value="item.nombre" :key="item.nombre">
                                                                {{ item.nombre }}
                                                            </option>
                                                        </select>
                                                        <p class="text-danger text-sm-start" v-if="(solicitud.tipo_desembolso=='0' || solicitud.tipo_desembolso=='') && solicitud.enviado==1">Seleccione un tipo desembolso *</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="solicitud_simulacion_tipo_tasa" class="form-label">Tipo tasa</label>
                                                        <select @change="(tipo_tasa=='fija')?seleccionarLapsoCapital():''" v-model="tipo_tasa" class="form-control"
                                                            id="solicitud_simulacion_tipo_tasa" name="solicitud_simulacion_tipo_tasa" required>
                                                            <!-- <option value="0" selected hidden disabled>Seleccione un tipo</option> -->
                                                            <option value="fija">Fija</option>
                                                            <option value="amortizable">Amortizable</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    
                                                    <div class="mb-3">
                                                        <label for="fecha_primera_cuota" class="form-label">Primera cuota</label>
                                                        <input  :disabled="(bloqueado || solicitud.accion==2 || tipo_tasa=='fija')" v-model="solicitud.fecha_primera_cuota" type="date" class="form-control" id="fecha_primera_cuota" name="fecha_primera_cuota" required>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                              

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table mb-4 table-striped table-hover table-sm table-bordered">
                                                    <thead class="bg-success text-white">
                                                        <tr>
                                                            <th>Nro</th>
                                                            <th>Fecha</th>
                                                            <th>Capital</th>
                                                            <th>Recargo ({{ solicitud.tasa }})%</th>
                                                            <th>Saldo capital</th>
                                                            <th v-if="ahorro">Ahorro</th>
                                                            <th v-if="seguro">Seguro</th>
                                                            <th>Total Bs</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="" v-for="(item, index) in lista_cuotas" :key="index">
                                                            <td>{{ item.nro }}</td>
                                                            <td>{{ item.fecha }}</td>
                                                            <td>{{ item.capital }}</td>
                                                            <td>{{ item.interes }}</td>
                                                            <td>{{ (item.saldo_capital==-0)?0:item.saldo_capital}}</td>
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

                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <button :disabled="bloqueado"  class="btn btn-sm me-2" style="background-color:#f9e79f" @click="generarNuevoPlanPagoAmortizacion()">
                                                <i class="fas fa-sync"></i>
                                                Generar cuotas</button>
                                            <button :disabled="bloqueado"  class="btn btn-sm btn-secondary me-2" @click="cancelarGestionarPlanPago()">
                                                <i class="fas fa-times-circle"></i>
                                                Cancelar</button>
                                            <button :disabled="bloqueado"  @click="guardarPlanPagoAmortizacionNuevo()" class="btn btn-sm btn-success"  >
                                                <i class="fas fa-check"></i>
                                                Guardar</button>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->



            <!--  Modal content for the above example -->
            <div id="modalNuevoPlanPago" class="modal fade bs-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content border border-4 border-dark" style="background-color: #E8E8E8">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myExtraLargeModalLabel">
                                GESTIONAR PLAN DE PAGO</h5>
                            <button @click="cerrarModalNuevoPlanPago()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="importe_solicitud" class="form-label">Importe solicitud</label>
                                        <input :disabled="true" v-model="solicitud.importe_solicitud" type="text" class="form-control" id="importe_solicitud"
                                            name="importe_solicitud"
                                            onkeydown="if(event.key==='.' && event.target.value.includes('.')){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9.]*/g,'');">
                                        <p class="text-danger text-sm-start" v-if="(solicitud.importe_solicitud==0 || solicitud.importe_solicitud=='') && solicitud.enviado==1">ingrese un importe *</p>
                                    </div>
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <label for="moneda" class="form-label">Moneda</label>
                                                <select :disabled="true" v-model="solicitud.moneda" class="form-control"
                                                    id="moneda" name="moneda" required>
                                                    <option value="0" selected hidden disabled>Seleccione moneda</option>
                                                    <option v-for="item in lista_monedas" :value="item.nombre"
                                                        :key="item.nombre">
                                                        {{ item.nombre }}</option>
                                                </select>
                                                <p class="text-danger text-sm-start" v-if="(solicitud.moneda=='0' || solicitud.moneda=='') && solicitud.enviado==1">seleccione una moneda*</p>

                                            </div>
                                            <div class="col-md-4">
                                                <label for="lapso_capital" class="form-label">Lapso capital</label>
                                                <select :disabled="true" v-model="solicitud.lapso_capital" class="form-control"
                                                    id="lapso_capital" name="lapso_capital" required>
                                                    <option value="0" selected hidden disabled>Seleccione</option>
                                                    <option v-for="item in lapso_capitales" :value="item.nombre"
                                                        :key="item.nombre">
                                                        {{ item.nombre }}</option>
                                                </select>
                                                <p class="text-danger text-sm-start" v-if="(solicitud.lapso_capital=='0' || solicitud.lapso_capital=='') && solicitud.enviado==1">seleccione una opción*</p>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="nro_cuotas" class="form-label">Nro. cuotas</label>
                                                <input v-model="solicitud.nro_cuotas" type="number"  class="form-control" id="nro_cuotas" name="nro_cuotas" required>
                                                <p class="text-danger text-sm-start" v-if="solicitud.nro_cuotas=='' && solicitud.enviado==1">Ingrese nro cuotas *</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="tasa" class="form-label">Tasa %</label>
                                                <input :disabled="true" v-model="solicitud.tasa" type="text"  class="form-control" id="tasa" name="tasa" required
                                                onkeydown="if(event.key==='.'){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');">
                                                <p class="text-danger text-sm-start" v-if="solicitud.tasa=='' && solicitud.enviado==1">Ingrese tasa *</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label for="fecha_primera_cuota" class="form-label">Fecha primera cuota</label>
                                        <input v-model="solicitud.fecha_primera_cuota" :disabled="solicitud.accion==2?true:false" type="date" class="form-control" id="fecha_primera_cuota" name="fecha_primera_cuota"
                                            required>
                                        <!-- <p class="text-danger text-sm-start" v-if="solicitud.actividad=='' && solicitud.enviado==1">Ingrese una actividad *</p> -->
                                    </div>

                                    <div class="mb-3">
                                        <label for="tipo_desembolso" class="form-label">Tipo desembolso</label>
                                        <select v-model="solicitud.tipo_desembolso" class="form-control"
                                            id="tipo_desembolso" name="tipo_desembolso" required>
                                            <option value="0" selected hidden disabled>Seleccione</option>
                                            <option v-for="item in tipos_desembolsos" :value="item.nombre"
                                                :key="item.nombre">
                                                {{ item.nombre }}</option>
                                        </select>
                                        <p class="text-danger text-sm-start" v-if="(solicitud.tipo_desembolso=='0' || solicitud.tipo_desembolso=='') && solicitud.enviado==1">seleccione un tipo desembolso *</p>

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 text-start">
                                    <div class="row">
                                            <div class="col-md-5">
                                                <div class="input-group">
                                                    <input :disabled="solicitud.estado==2" class="form-check-input mx-2" type="checkbox" id="checkbox1" v-model="ahorro" @change="cambiarEstado()">
                                                    <label class="form-check-label mx-2" for="checkbox1">
                                                    Ahorro
                                                    </label>
                                                    <input :disabled="!ahorro" class="form-control" type="text" v-model="cantidad_ahorro">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="input-group">
                                                    <input :disabled="solicitud.estado==2" class="form-check-input mx-2" type="checkbox" id="checkbox2" v-model="seguro"  @change="cambiarEstado()">
                                                    <label class="form-check-label mx-2" for="checkbox2">
                                                    Seguro
                                                    </label>
                                                    <input :disabled="!seguro" class="form-control" type="text" v-model="cantidad_seguro">
                                                </div>
                                            </div>
                                    </div>

                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                        <input @click="generarPlanPagosTasaFija()" v-model="tasa_plan" value="fija" type="radio" class="btn-check" name="btnradio" id="btnradio1"
                                                autocomplete="off" >
                                        <label class="btn btn-outline-danger" for="btnradio1">Tasa fija</label>

                                        <input @click="generarPlanPagos()" v-model="tasa_plan" value="amortizable" type="radio" class="btn-check" name="btnradio" id="btnradio2"
                                            autocomplete="off" checked>
                                        <label class="btn btn-outline-danger" for="btnradio2">Tasa Amortizable</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table mb-4 table-striped table-hover">
                                                <thead class="bg-primary text-white">
                                                    <tr>
                                                        <th>Nro</th>
                                                        <th>Fecha</th>
                                                        <th>Capital</th>
                                                        <th>Interes ({{ solicitud.tasa }})%</th>
                                                        <th>Saldo capital</th>
                                                        <th v-if="ahorro">Ahorro</th>
                                                        <th v-if="seguro">Seguro</th>
                                                        <th>Total Bs</th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr class="" v-for="(item, index) in lista_cuotas" :key="index">

                                                        <td>{{ item.nro }}</td>
                                                        <td>{{ item.fecha }}</td>
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
                            <button @click="generalPlanPagosGeneral()" type="button" class="btn btn-info mx-1 text-white">Generar plan Pago</button>
                            <button @click="guardarPlanPago()" type="button" class="btn btn-primary mx-1 text-white">Guardar</button>
                            <button @click="cerrarModalNuevoPlanPago()" type="button" class="btn btn-danger mx-1 text-white">Cancelar</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
        </div>
    </main>
</template>

<script>
    import moment from 'moment';
    import Swal from 'sweetalert2'


    export default {
        data() {
            return {
                lista_cuotas_amortizacion:[],
                tipo_tasa:'amortizable',
                preloader:false,
                lista_amortizaciones:[],
                lista_planes_pago_ligados:[],
                tasa_plan_nuevo:'amortizable',
                bloqueado:true,
                codigo_credito:0,
                nombre_cliente:'',
                estado_credito:'todos',
                fecha_actual:moment().format('YYYY-MM-DD'),
                lista_cantidad_clientes:[],
                opcion_asesor:0,
                lista_asesores:[],
                lista_codeudores_tabla:[],
                en_proceso: false,
                cancelado: false,
                listado_usuarios:[],
                criterio_asesor:'0',
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
                prueba:0,
                estado_caja:false,
                solicitud: {
                    id_solicitud:0,
                    importe_solicitud: 0,
                    moneda:'0',
                    lapso_capital:'0',
                    nro_cuotas:0,
                    tasa:0,
                    fecha_desembolso: moment().format('YYYY-MM-DD'),
                    fecha_primera_cuota: moment().format('YYYY-MM-DD'),
                    //destino_prestamo: '',
                    estado:'',
                    id_cliente:0,
                    id_usuario:0,
                    tipo_garantia:'0',
                    tipo_desembolso:'0',

                    enviado: 0,
                    accion:0,
                },
                lista_monedas:[
                    {nombre:'Bolivianos'},
                    {nombre:'Dolares'},
                ],
                tipos_desembolsos:[
                    {nombre:'Efectivo'},
                    {nombre:'Depósito'},
                    {nombre:'Transferencia'},
                    {nombre:'QR'},
                ],
                lapso_capitales:[
                    {nombre:'Diario'},
                    {nombre:'Semanal'},
                    {nombre:'Quincenal'},
                    {nombre:'Mensual'},
                ],
                criterio:'cliente.nombre',
                buscar:'',
                lista_cuotas_plan:[],
                lista_planespago:[],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                plan_pago:{
                    cliente:'',
                    ci:'',
                    lugar_expedicion:'',
                    tipo_garantia:'',
                    nro_cuotas:0,
                    lapso_capital:'',
                    importe_solicitud:0,
                    moneda:'',
                    fecha_desembolso:'',
                    tasa:0,
                    // del plan
                    id_plan_pago:0,
                    fecha_inicio_plan:0,
                    fecha_fin_plan:0,
                    total_pagar_plan:0,
                    estado_plan:1,
                    id_solicitud:0,
                    tipo_desembolso:'',
                    estado:1,
                },
                cuota:{
                    id_cuota:0,
                    total:0,
                    numero:0
                },
                pago:{
                    id_pago:0,
                    fecha_pago:moment().format('YYYY-MM-DD'),
                    monto_pago:0,
                    id_cuota:0,

                },
                offset : 2,
                plan_pago_vigente:false,
                amortizacion:{
                    saldo_capital_ultimo_pago:0,
                    interes_cuotas_acumuladas:0,
                    dias_restantes:0,
                    dias_entre_ultima_proxima_cuota:0,
                    interes_dias:0,
                    total_interes_dias:0,
                    total_interes:0,
                    multa_dias:0,
                    monto_multa:0,
                    monto_multa_total:0,
                    total_liquidacion:0,
                    amortizacion:0,
                    saldo_capital_nuevo:0,
                    numero_cuota_inicio:0,
                    id_cuota:0,
                    total_ahorro:0,
                    total_seguro:0,
                    forma_pago:'efectivo',
                    liquidar_deuda:false,
                },
                lista_cuotas:[],
                tasa_plan:'amortizable',
                cantidad_ahorro:0,
                cantidad_seguro:0,
                seguro:false,
                ahorro:false,

                // mensajeError: '',

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
        watch:{
            'amortizacion.amortizacion':function(){
                console.log('cambio');

            },
            'amortizacion.liquidar_deuda':function(){
                if(this.amortizacion.liquidar_deuda){
                    this.amortizacion.amortizacion=this.amortizacion.saldo_capital_nuevo;
                }else{
                    this.amortizacion.amortizacion=0;
                }
            },
            'criterio':function(){
                if(this.criterio=='users.personal'){
                    this.criterio_asesor='defecto';
                    console.log('deberia cambiar');
                }else{
                    this.buscar='';
                    this.getPlanesPago(1);
                }
            }
        },

        methods: {
            formatFecha(fecha) {
                return moment(fecha).format('DD/MM/YYYY');
            },
            formatNumero(numero) {
                return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(numero);
            },

            exportExcelPlanPagos() {
                axios({
                    url: '/export-planes_pago', // La ruta que definiste en Laravel
                    method: 'GET',
                    responseType: 'blob', // Para manejar la respuesta como un archivo binario
                })
                .then((response) => {
                    // Crear un enlace para descargar el archivo
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'custom_data.xlsx'); // Nombre del archivo
                    document.body.appendChild(link);
                    link.click();
                })
                .catch((error) => {
                    console.error("Error exporting data:", error);
                });
            },

            seleccionarLapsoCapital(){
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

            esPrimerRegistroConMoraPorPlan(index, item) {
                // Encuentra el índice del primer elemento con dias_pasados > 0
                const primerIndiceConMora = item.cuotas.findIndex(
                    cuota => cuota.dias_pasados > 0
                );
                // Devuelve verdadero solo si el índice actual es el mismo que el primer índice encontrado
                return index === primerIndiceConMora;
            },

            guardarPlanPagoAmortizacionPorPlan(item){
                let monto_multa = (isNaN(parseFloat(this.amortizacion.monto_multa_total))?0:parseFloat(this.amortizacion.monto_multa_total));
                let monto_interes = (isNaN(parseFloat(this.amortizacion.total_interes))?0:parseFloat(this.amortizacion.total_interes));
                let monto_amortizacion = parseFloat(this.amortizacion.amortizacion) - (parseFloat(monto_multa) + parseFloat(monto_interes));
                let saldo_pendiente=this.solicitud.importe_solicitud;
                let guardado=false;

                if(this.lista_cuotas.length==0){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'Debe generar un plan de pago primero.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return ;
                }
                        axios.post('/save_amortizacion',{
                            'monto_multa':monto_multa,
                            'monto_interes':monto_interes,
                            'monto_capital':monto_amortizacion,
                            'detalles': JSON.stringify(this.lista_cuotas),
                            'id_plan_pago':item.id,// id_plan_pago
                            'id_plan_aux':this.plan_pago.id_plan_pago,// id_plan_aux
                            'saldo_pendiente':saldo_pendiente,
                            'numero_cuota_inicio':this.amortizacion.numero_cuota_inicio,
                            'tipo_desembolso':this.solicitud.tipo_desembolso,
                            'fecha_primera_cuota':this.solicitud.fecha_primera_cuota,
                            'id_solicitud':this.plan_pago.id_solicitud,
                            'fecha_fin':this.lista_cuotas[this.lista_cuotas.length-1].fecha,
                            'id_cuota':item.cuotas[item.cuotas.length-1].id,
                            'forma_pago':this.amortizacion.forma_pago,
                            'lapso_capital':this.solicitud.lapso_capital,
                            'nro_cuotas':this.solicitud.nro_cuotas,
                            'tasa':this.solicitud.tasa,
                            'tipo_desembolso':this.solicitud.tipo_desembolso,
                            'diferencia':10,
                            'total_seguro':this.amortizacion.total_seguro,

                        }).then((response)=>{
                            console.log(response);
                            this.getCuotas(this.plan_pago);
                            this.cerrarModalNuevoPlanPago();
                            this.cerrarModalAmortizacion();
                            guardado=true;
                            this.getPlanesPago(1);

                        })
                        .catch((error)=>{
                            console.log(error);
                        })
                        .finally(()=>{
                            if(guardado){
                                Swal.fire({
                                    title:'Operación exitosa',
                                    text:'Se registro con éxito',
                                    icon:'success',
                                    position:'top-rigth',
                                    timer:1500,
                                });
                                this.plan_pago.estado_plan=10;
                            }
                        })

            },

            async amortizarPlanPagoPorPlan(item){
                
                this.cancelarGestionarPlanPago();
                await this.consultarCajaAbierta();
                this.bloqueado=true;
                this.tasa_plan_nuevo='amortizable';

                if(!this.estado_caja){
                    let fecha_actual = moment().format('YYYY-MM-DD');
                    this.amortizacion.amortizacion=0;// cantidad amortizar
                    
                    const cuotas_sin_pagar = item.cuotas.filter(objeto => objeto.estado === 1 && objeto.amortizado === 0);
                    const ultimo_saldo_capital_objeto = cuotas_sin_pagar[0];
                    const ultimo_saldo_capital = parseFloat(ultimo_saldo_capital_objeto.saldo_capital) + parseFloat(ultimo_saldo_capital_objeto.capital);
                    // ULTIMO SALDO CAPITAL
                    this.amortizacion.saldo_capital_ultimo_pago=ultimo_saldo_capital;

                    // OBTENIENDO INTERES CUOTAS
                    const array_cuotas_sin_pagar = item.cuotas.filter(objeto => objeto.estado === 1 &&  objeto.amortizado === 0);

                    const array_cuotas_sin_pagar_hasta_fecha = array_cuotas_sin_pagar.filter(objeto => objeto.fecha < fecha_actual);
                    // cantidad cuotas sin pagar
                    this.amortizacion.interes_cuotas_acumuladas=array_cuotas_sin_pagar_hasta_fecha.length;

                    // DIAS RESTANTES
                    this.amortizacion.hay_cuotas_sin_pagar=false;
                    const array_cuotas_sin_pagar_fecha_proxima = array_cuotas_sin_pagar.filter(objeto => objeto.fecha >= fecha_actual);

                    const array_cuotas_menor_fecha_actual = item.cuotas.filter(objeto => objeto.fecha < fecha_actual && (objeto.estado === 1 || objeto.estado === 2) &&  
                    objeto.amortizado === 0);

                    if(array_cuotas_sin_pagar_fecha_proxima.length>0){
                        this.amortizacion.hay_cuotas_sin_pagar=true; 

                    }

                    this.amortizacion.dias_restantes=0;
                    if(array_cuotas_menor_fecha_actual.length>0){
                        const fecha_ultima_cuota_hasta_fecha=array_cuotas_menor_fecha_actual[array_cuotas_menor_fecha_actual.length-1].fecha;
                        const diferencia_dias = moment(fecha_actual).diff(moment(fecha_ultima_cuota_hasta_fecha), 'days');
                        this.amortizacion.dias_restantes=diferencia_dias;
                        if(array_cuotas_sin_pagar_fecha_proxima.length>0){
                            const fecha_anterior_cuota=fecha_ultima_cuota_hasta_fecha;
                            const fecha_proxima_cuota=array_cuotas_sin_pagar_fecha_proxima[0].fecha;
                            const dias_entre_anterior_siguiente=moment(fecha_proxima_cuota).diff(moment(fecha_anterior_cuota), 'days');
                            this.amortizacion.dias_entre_ultima_proxima_cuota=dias_entre_anterior_siguiente;
                        }
                    }else {
                        const fecha_inicio_plan_pago = this.lista_planes_pago_ligados[this.lista_planes_pago_ligados.length-1].fecha_inicio_plan;
                        const dias_entre_inicio_y_actual= moment(fecha_actual).diff(moment(fecha_inicio_plan_pago), 'days');
                        const fecha_primera_cuota=this.lista_planes_pago_ligados[this.lista_planes_pago_ligados.length-1].cuotas[0].fecha;
                        const dias_entre_inicio_primer_cuota = moment(fecha_primera_cuota).diff(moment(fecha_inicio_plan_pago), 'days');
                        this.amortizacion.dias_entre_ultima_proxima_cuota = dias_entre_inicio_primer_cuota;
                        this.amortizacion.dias_restantes=dias_entre_inicio_y_actual;

                        console.log('fecha inicio plan: ', fecha_inicio_plan_pago);
                        console.log('fecha primera cuota: ', fecha_primera_cuota);

                        // dias_restantes
                    }

                    // CALCULO INTERES DIARIO
                    const interes_x_dias= parseFloat((this.amortizacion.saldo_capital_ultimo_pago * (item.tasa/100)) / 30);
                    const interes_dia=parseFloat(interes_x_dias * this.amortizacion.dias_restantes).toFixed(0);

                    this.amortizacion.interes_dias=interes_x_dias;
                    this.amortizacion.total_interes_dias=interes_dia// TOTAL INTERES DIAS HASTA FECHA;



                    const suma_interes = array_cuotas_sin_pagar_hasta_fecha.reduce((acumulador, objeto) => {
                        return acumulador + parseFloat(objeto.interes || 0);
                    }, 0);

                    const suma_ahorro = array_cuotas_sin_pagar_hasta_fecha.reduce((acumulador, objeto) => {
                        return acumulador + parseFloat(objeto.ahorro || 0);
                    }, 0);

                    const suma_seguro = array_cuotas_sin_pagar_hasta_fecha.reduce((acumulador, objeto) => {
                        return acumulador + parseFloat(objeto.seguro || 0);
                    }, 0);

                    this.amortizacion.total_ahorro=suma_ahorro;
                    this.amortizacion.total_seguro=suma_seguro;
                    this.amortizacion.total_interes=parseFloat(parseFloat(suma_interes) + parseFloat(this.amortizacion.total_interes_dias)).toFixed(2);

                    const index_registro_mora=this.primerRegistroConMora();
                    this.amortizacion.tiene_mora=false;
                    this.amortizacion.multa_dias=0;
                    if(index_registro_mora!=null){
                        this.amortizacion.tiene_mora=true;
                        this.amortizacion.multa_dias=index_registro_mora.dias_pasados;
                    }

                    // TOTAL LIQUIDACION
                    this.amortizacion.total_liquidacion= parseFloat(this.amortizacion.monto_multa_total) +  parseFloat(this.amortizacion.total_interes) + parseFloat(this.amortizacion.saldo_capital_ultimo_pago) 
                    + parseFloat(this.amortizacion.total_seguro) -parseFloat(this.amortizacion.total_ahorro);

                    this.amortizacion.saldo_capital_nuevo=this.amortizacion.total_liquidacion;

                    // ULTIMAS INSTRUCCIONES
                    this.amortizacion.liquidar_deuda=false;
                    this.abrirModalAmortizacion();
                    this.amortizacion.cliente=this.plan_pago.cliente;
                    this.amortizacion.id_plan_pago=item.id_plan_pago;

                    // para calculo de intereses de cuotas
                    this.lista_cuotas_amortizacion=this.lista_planes_pago_ligados[this.lista_planes_pago_ligados.length-1].cuotas;

                }
            },

            async getAmortizaciones() {
                try {
                    const response = await axios.get('/get_amortizaciones', {
                        params: { id_plan_pago: this.plan_pago.id_plan_pago }
                    });
                    
                    // Devuelve el array de cuotas
                    this.lista_amortizaciones=response.data;
                } catch (error) {
                    console.error('Error al obtener las cuotas del plan:', error.message);
                    return []; // Devuelve un array vacío en caso de error
                }
            },

            async getAmortizacionesPlan(id_plan_pago) {
                try {
                    const response = await axios.get('/get_amortizaciones_plan', {
                        params: { id_plan_pago: id_plan_pago }
                    });
                    
                    // Devuelve el array de cuotas
                    return response.data;
                } catch (error) {
                    console.error('Error al obtener las cuotas del plan:', error.message);
                    return []; // Devuelve un array vacío en caso de error
                }
            },


            async getCuotasPorPlan(id_plan_pago) {
                try {
                    const response = await axios.get('/get_cuotas_plan', {
                        params: { id_plan_pago: id_plan_pago }
                    });
                    
                    // Devuelve el array de cuotas
                    return response.data;
                } catch (error) {
                    console.error('Error al obtener las cuotas del plan:', error.message);
                    return []; // Devuelve un array vacío en caso de error
                }
            },

            async getPlanesPagoLigados(item) {
                try {
                    const response = await axios.get('/get_planes_pago_ligados', {
                    params: { id_plan_pago: item.id }
                    });

                    this.lista_planes_pago_ligados = await Promise.all(response.data.map(async (plan) => {
                    const cuotas = await this.getCuotasPorPlan(plan.id);
                    const amortizaciones=await this.getAmortizacionesPlan(plan.id);
                    return {
                        ...plan,
                        cuotas, // Agregar las cuotas como una nueva propiedad del plan
                        amortizaciones,
                    };
                    }));

                    // Ordenar la lista de planes de pago de forma ascendente por un atributo
                    this.lista_planes_pago_ligados.sort((a, b) => a.id - b.id);

                    console.log(this.lista_planes_pago_ligados);
                } catch (error) {
                    console.error('Error al obtener los planes de pago ligados:', error.message);
                }
            },
         

            async guardarPlanPagoAmortizacion() {
                let monto_multa = isNaN(parseFloat(this.amortizacion.monto_multa_total)) ? 0 : parseFloat(this.amortizacion.monto_multa_total);
                let monto_interes = isNaN(parseFloat(this.amortizacion.total_interes)) ? 0 : parseFloat(this.amortizacion.total_interes);
                let monto_amortizacion = parseFloat(this.amortizacion.amortizacion) - (parseFloat(monto_multa) + parseFloat(monto_interes));
                let saldo_pendiente = this.solicitud.importe_solicitud;
                let guardado = false;

                if (this.lista_cuotas.length == 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'Debe generar un plan de pago primero.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                try {
                    const response = await axios.post('/save_amortizacion', {
                        'monto_multa': monto_multa,
                        'monto_interes': monto_interes,
                        'monto_capital': monto_amortizacion,
                        'detalles': JSON.stringify(this.lista_cuotas),

                        'id_plan_pago': (this.lista_planes_pago_ligados.length>0)? this.lista_planes_pago_ligados[this.lista_planes_pago_ligados.length-1].id: this.plan_pago.id_plan_pago,
                        'id_plan_pago_principal':  this.plan_pago.id_plan_pago,


                        'saldo_pendiente': saldo_pendiente,
                        'numero_cuota_inicio': this.amortizacion.numero_cuota_inicio,
                        'tipo_desembolso': this.solicitud.tipo_desembolso,
                        'fecha_primera_cuota': this.solicitud.fecha_primera_cuota,
                        'id_solicitud': this.plan_pago.id_solicitud,
                        'fecha_fin': this.lista_cuotas[this.lista_cuotas.length - 1].fecha,
                        'id_cuota': (this.lista_cuotas_plan[this.lista_cuotas_plan.length - 1].numero == null) ? this.lista_cuotas_plan[this.lista_cuotas_plan.length - 2].id : this.lista_cuotas_plan[this.lista_cuotas_plan.length - 1].id,
                        'forma_pago': this.amortizacion.forma_pago,
                        'lapso_capital': this.solicitud.lapso_capital,
                        'nro_cuotas': this.solicitud.nro_cuotas,
                        'tasa': this.solicitud.tasa,
                        'tipo_desembolso': this.solicitud.tipo_desembolso,
                        'diferencia': 0,
                        'total_seguro': this.amortizacion.total_seguro,
                    });

                    console.log(response);
                    await this.getCuotas(this.plan_pago);
                    await this.cerrarModalNuevoPlanPago();
                    await this.cerrarModalAmortizacion();
                    guardado = true;

                    await this.getPlanesPago(1);
                    await this.getPlanesPagoLigados(this.plan_pago);
                    //await this.getAmortizaciones();
                } catch (error) {
                    console.log(error);
                } finally {
                    if (guardado) {
                        Swal.fire({
                            title: 'Operación exitosa',
                            text: 'Se registró con éxito',
                            icon: 'success',
                            position: 'top-right',
                            timer: 1500,
                        });
                        this.plan_pago.estado_plan = 10;
                    }
                }
            },

            async guardarPlanPagoAmortizacionNuevo() {
                let monto_multa = isNaN(parseFloat(this.amortizacion.monto_multa_total)) ? 0 : parseFloat(this.amortizacion.monto_multa_total);
                let monto_interes = isNaN(parseFloat(this.amortizacion.total_interes)) ? 0 : parseFloat(this.amortizacion.total_interes);
                let monto_amortizacion = parseFloat(this.amortizacion.amortizacion) - (parseFloat(monto_multa) + parseFloat(monto_interes));
                let saldo_pendiente = this.solicitud.importe_solicitud;
                let guardado = false;

                if (this.lista_cuotas.length == 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'Debe generar un plan de pago primero.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                try {
                    const response = await axios.post('/save_amortizacion_nuevo', {
                        'monto_multa': monto_multa,
                        'monto_interes': monto_interes,
                        'monto_capital': monto_amortizacion,
                        'detalles': JSON.stringify(this.lista_cuotas),
                        'id_plan_pago':  this.plan_pago.id_plan_pago,
                        'saldo_pendiente': saldo_pendiente,
                        'numero_cuota_inicio': this.solicitud.nuevo_numero_cuota,
                        'tipo_desembolso': this.solicitud.tipo_desembolso,
                        'fecha_primera_cuota': this.solicitud.fecha_primera_cuota,
                        'id_solicitud': this.plan_pago.id_solicitud,
                        'fecha_fin': this.lista_cuotas[this.lista_cuotas.length - 1].fecha,
                        'id_cuota': (this.lista_cuotas_plan[this.lista_cuotas_plan.length - 1].numero == null) ? this.lista_cuotas_plan[this.lista_cuotas_plan.length - 2].id : this.lista_cuotas_plan[this.lista_cuotas_plan.length - 1].id,
                        'forma_pago': this.amortizacion.forma_pago,
                        'lapso_capital': this.solicitud.lapso_capital,
                        'nro_cuotas': this.solicitud.nro_cuotas,
                        'tasa': this.solicitud.tasa,
                        'tipo_desembolso': this.solicitud.tipo_desembolso,
                        'diferencia': 0,
                        'total_seguro': this.amortizacion.total_seguro,
                    });

                    console.log(response);
                    await this.getCuotas(this.plan_pago);
                    await this.cerrarModalNuevoPlanPago();
                    await this.cerrarModalAmortizacion();
                    await this.getPlanesPago(1);
                    //await this.getAmortizaciones();
                    guardado = true;
                } catch (error) {
                    console.log(error);
                } finally {
                    if (guardado) {
                        Swal.fire({
                            title: 'Operación exitosa',
                            text: 'Se registró con éxito',
                            icon: 'success',
                            position: 'top-right',
                            timer: 1500,
                        });
                    }
                }
            },

            primerRegistroConMora() {
                // Encuentra el primer objeto en la lista con dias_pasados > 0
                return this.lista_cuotas_plan.find(cuota => cuota.dias_pasados > 0) || null;
            },
            esPrimerRegistroConMora(index) {
                // Encuentra el índice del primer elemento con dias_pasados > 0
                const primerIndiceConMora = this.lista_cuotas_plan.findIndex(
                    cuota => cuota.dias_pasados > 0
                );
                // Devuelve verdadero solo si el índice actual es el mismo que el primer índice encontrado
                return index === primerIndiceConMora;
            },
            async getCantidadClienteAsesor(){
                await axios.get('/get_cantidad_creditos')
                .then((response)=>{
                    console.log(response);
                    this.lista_cantidad_clientes=response.data;
                })
                .catch((error)=>{
                    console.log(error.message);
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
            obtenerFechaActual() {
            return moment().format('YYYY-MM-DD');
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
            generarContrato(item){
                // Construye la URL con el parámetro fecha_inicio
                const url = '/generar_contrato?nombre_cliente='+item.cliente+'&ci='+item.ci+
                '&lugar_expedicion='+item.lugar_expedicion+'&monto_total='+item.total_pagar_plan+'&nro_cuotas='+item.nro_cuotas+'&lapso_capital='+item.lapso_capital+'&fecha_inicio='+item.fecha_desembolso
                +'&id_solicitud='+item.id_solicitud;

                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            async getUsuarios() {
                await axios.get('/get_usuarios_sin').then((response) => {
                        this.listado_usuarios = response.data;

                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
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
            guardarPlanPago(){
                let monto_multa = (isNaN(parseFloat(this.amortizacion.monto_multa_total))?0:parseFloat(this.amortizacion.monto_multa_total));
                let monto_interes = (isNaN(parseFloat(this.amortizacion.total_interes))?0:parseFloat(this.amortizacion.total_interes));
                let monto_amortizacion = parseFloat(this.amortizacion.amortizacion) - (parseFloat(monto_multa) + parseFloat(monto_interes));
                let saldo_pendiente=this.solicitud.importe_solicitud;

                console.log(monto_multa);
                console.log(monto_interes);
                console.log(monto_amortizacion);
                let guardado=false;

                if(this.amortizacion.amortizacion<monto_multa+monto_interes){
                    console.log('el monto debe ser mayor al interes y multa que se debe');
                    Swal.fire({
                        title:'Advertencia',
                        text:'el monto debe ser mayor al interes y multa que se debe',
                        icon:'warning',
                        position:'center',
                        confirmButton:true,
                        confirmButtonText:'Aceptar',
                    });
                }else{
                    if(this.lista_cuotas.length<=0){
                        Swal.fire({
                            title:'Advertencia',
                            text:'Debe generar un plan de pagos primero',
                            icon:'warning',
                            position:'center',
                            confirmButton:true,
                            confirmButtonText:'Aceptar',
                        });
                    }else{

                        axios.post('/save_amortizacion',{
                            'monto_multa':monto_multa,
                            'monto_interes':monto_interes,
                            'monto_capital':monto_amortizacion,
                            'detalles': JSON.stringify(this.lista_cuotas),
                            'id_plan_pago':this.plan_pago.id_plan_pago,
                            'saldo_pendiente':saldo_pendiente,
                            'numero_cuota_inicio':this.amortizacion.numero_cuota_inicio,
                            'tipo_desembolso':this.solicitud.tipo_desembolso,
                            'fecha_primera_cuota':this.solicitud.fecha_primera_cuota,
                            'id_solicitud':this.plan_pago.id_solicitud,
                            'fecha_fin':this.lista_cuotas[this.lista_cuotas.length-1].fecha,
                            'id_cuota':this.lista_cuotas_plan[this.lista_cuotas_plan.length-1].id,
                            'forma_pago':this.amortizacion.forma_pago,
                        }).then((response)=>{
                            console.log(response);
                            this.getCuotas(this.plan_pago);
                            
                            this.cerrarModalNuevoPlanPago();
                            this.cerrarModalAmortizacion();
                            guardado=true;
                            this.plan_pago.nro_cuotas=response.data.nro_cuotas;
                            this.plan_pago.fecha_fin_plan=response.data.fecha_fin;
                            this.plan_pago.fecha_fin_plan=response.data.fecha_fin;
                            this.plan_pago.fecha_inicio_plan=response.data.fecha_inicio_plan;
                            this.getPlanesPago(1);
                            this.getPlanesPagoLigados(this.plan_pago);


                        })
                        .catch((error)=>{
                            console.log(error);
                        })
                        .finally(()=>{
                            if(guardado){
                                Swal.fire({
                                    title:'Operación exitosa',
                                    text:'Se registro con éxito',
                                    icon:'success',
                                    position:'top-rigth',
                                    // confirmButton:true,
                                    // confirmButtonText:'Aceptar',
                                    timer:1500,
                                });
                            }
                        })
                        console.log('se acepta la amortizacion');
                    }
                }

            },
            cambiarEstado(){
                this.cantidad_ahorro=this.ahorro?this.cantidad_ahorro:0;
                this.cantidad_seguro=this.seguro?this.cantidad_seguro:0;
                // if(this.lista_cuotas.length>0){
                //     this.generarPlanPagos();
                // }
            },
            generalPlanPagosGeneral(){
                if(this.solicitud.importe_solicitud==0 || this.solicitud.importe_solicitud==''
                || this.solicitud.moneda=='' || this.solicitud.lapso_capital==''
                || this.solicitud.nro_cuotas==0 || this.solicitud.nro_cuotas==''
                || this.solicitud.tipo_desembolso=='' || this.solicitud.tasa=='' || this.solicitud.tasa==0){
                    this.solicitud.enviado=1;
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debe rellenar todos los campos para generar plan de pago',
                        showConfirmButton: true,
                        // showCancelButton: true,
                        confirmButtonText:'Aceptar',
                        // cancelButtonText:'Cancelar',
                        //timer: 1500
                    });
                }else{
                    if(this.tasa_plan=='amortizable'){
                        this.generarPlanPagos();
                        console.log('amortizable');
                    }else{
                        if(this.tasa_plan=='fija'){
                            this.generarPlanPagosTasaFija();
                            console.log('fija');
                        }
                    }
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

            generarPlanPagosTasaFija(){
                
                    this.lista_cuotas=[];
                    let monto_total=parseFloat(this.solicitud.importe_solicitud);
                    let contador=1;
                    const fecha_actual=moment().format('YYYY-MM-DD');
                    let fecha_inicio=moment(fecha_actual);
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

                                ahorro: parseFloat(this.cantidad_ahorro || 0).toFixed(0),
                                seguro: parseFloat(this.cantidad_seguro || 0).toFixed(0),

                                total_cuota:(parseFloat(cuotaTasaFija)+
                                (this.ahorro ? parseFloat(this.cantidad_ahorro || 0) : 0) +
                                (this.seguro ? parseFloat(this.cantidad_seguro || 0) : 0)).toFixed(0),

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
                    const fecha_actual=moment().format('YYYY-MM-DD');
                    var fecha_inicio=moment(fecha_actual);
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
                            
                            ahorro: parseFloat(this.cantidad_ahorro || 0).toFixed(0),
                            seguro: parseFloat(this.cantidad_seguro || 0).toFixed(0),

                            // total_cuota:parseFloat(parseFloat(capital_aux) + parseFloat(interes*dias_inicio_fin) + (this.ahorro?parseFloat(this.cantidad_ahorro):0) + (this.seguro?parseFloat(this.cantidad_seguro):0)).toFixed(0),
                            total_cuota: (
                                parseFloat(capital_aux) +
                                (parseFloat(interes) * dias_inicio_fin) +
                                (this.ahorro ? parseFloat(this.cantidad_ahorro || 0) : 0) +
                                (this.seguro ? parseFloat(this.cantidad_seguro || 0) : 0)
                            ).toFixed(0),

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

            ajustarMontoLiquidacion(){
                this.amortizacion.total_liquidacion=((isNaN(parseFloat(this.amortizacion.saldo_capital_ultimo_pago))?parseFloat(0): parseFloat(this.amortizacion.saldo_capital_ultimo_pago))+(isNaN(parseFloat(this.amortizacion.total_interes))?parseFloat(0):parseFloat(this.amortizacion.total_interes)) +(isNaN(parseFloat(this.amortizacion.monto_multa_total))?parseFloat(0): parseFloat(this.amortizacion.monto_multa_total))).toFixed(2);
                //this.amortizacion.total_liquidacion=isNaN((parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total)).toFixed(2))?0:(parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total)).toFixed(2);
                this.amortizacion.saldo_capital_nuevo=((isNaN(parseFloat(this.amortizacion.saldo_capital_ultimo_pago))?0: parseFloat(this.amortizacion.saldo_capital_ultimo_pago)) + (isNaN(parseFloat(this.amortizacion.total_interes))?0:parseFloat(this.amortizacion.total_interes)) +(isNaN(parseFloat(this.amortizacion.monto_multa_total))?0: parseFloat(this.amortizacion.monto_multa_total))).toFixed(2);// + seguro - ahorro

            },

            generarNuevoPlanPagoAmortizacionOpcion(opcion) {
                // Validar el campo lapso_capital
                if (!this.solicitud.lapso_capital || this.solicitud.lapso_capital === '0') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'Debe seleccionar un lapso de capital válido.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                // Validar el campo nro_cuotas
                if (!this.solicitud.nro_cuotas || this.solicitud.nro_cuotas <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'El número de cuotas debe ser mayor que 0.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                // Validar el campo tasa
                if (!this.solicitud.tasa || this.solicitud.tasa <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'La tasa debe ser un valor mayor que 0.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                // Validar la fecha de la primera cuota
                const fechaPrimeraCuota = moment(this.solicitud.fecha_primera_cuota);
                const fechaActual = moment();
                if (!fechaPrimeraCuota.isValid() || fechaPrimeraCuota.isSameOrBefore(fechaActual, 'day')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'La fecha de la primera cuota debe ser posterior a la fecha actual.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }
                // Si todas las validaciones se pasan, generar el nuevo plan de pago
                // Tu lógica para generar el plan de pago aquí

                if(opcion=='amortizable'){
                    this.generarPlanPagos();
                }else{
                    this.generarPlanPagosTasaFija();
                }
                console.log("Generar nuevo plan de pago con amortización");

            },
            generarNuevoPlanPagoAmortizacion() {
                // Validar el campo lapso_capital
                if (!this.solicitud.lapso_capital || this.solicitud.lapso_capital === '0') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'Debe seleccionar un lapso de capital válido.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                // Validar el campo nro_cuotas
                if (!this.solicitud.nro_cuotas || this.solicitud.nro_cuotas <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'El número de cuotas debe ser mayor que 0.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                // Validar el campo tasa
                if (!this.solicitud.tasa || this.solicitud.tasa <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'La tasa debe ser un valor mayor que 0.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }

                // Validar la fecha de la primera cuota
                const fechaPrimeraCuota = moment(this.solicitud.fecha_primera_cuota);
                const fechaActual = moment();
                if (!fechaPrimeraCuota.isValid() || fechaPrimeraCuota.isSameOrBefore(fechaActual, 'day')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'La fecha de la primera cuota debe ser posterior a la fecha actual.',
                        confirmButtonText: 'Aceptar',
                        position: 'center',
                    });
                    return;
                }
                // Si todas las validaciones se pasan, generar el nuevo plan de pago
                // Tu lógica para generar el plan de pago aquí

                if(this.tipo_tasa=='amortizable'){
                    this.generarPlanPagos();
                }else{
                    this.generarPlanPagosTasaFija();
                }
                console.log("Generar nuevo plan de pago con amortización");

            },
           
            gestionarPlanPago(){

                // Datos para liquidar deuda y operacion amortizacion
                let cuotasPorPagar = this.lista_cuotas_plan.filter(objeto => objeto.estado === 2);
                let cantidad_cuotas_pagadas=cuotasPorPagar.length;

                let monto_multa = parseFloat(this.amortizacion.monto_multa_total) || 0;
                let monto_interes = parseFloat(this.amortizacion.total_interes) || 0;
                let monto_amortizacion = parseFloat(this.amortizacion.amortizacion) - (monto_multa + monto_interes);
                let saldo_pendiente = this.solicitud.importe_solicitud;
                const monto_minimo=monto_multa+monto_interes;

                if(this.amortizacion.amortizacion<monto_minimo){
                    console.log('el monto debe ser mayor al interes y multa que se debe');
                    Swal.fire({
                        title:'Advertencia',
                        html: `
                            <p>Monto mínimo para amortizar: <strong>${monto_minimo}</strong></p>
                            <p>El monto debe ser mayor al interés y multa que se debe.</p>
                        `,
                        icon:'warning',
                        position:'center',
                        confirmButton:true,
                        confirmButtonText:'Aceptar',
                    });
                    this.cancelarGestionarPlanPago();


                }else{
                    if(this.amortizacion.amortizacion<0){
                        console.log('el monto no debe ser negativo');
                        Swal.fire({
                            title:'Advertencia',
                            text:'el monto no debe ser negativo',
                            icon:'warning',
                            position:'center',
                            confirmButton:true,
                            confirmButtonText:'Aceptar',
                        });
                        this.cancelarGestionarPlanPago();

                    }else{
                        if((parseFloat(this.amortizacion.saldo_capital_nuevo) - parseFloat(this.amortizacion.amortizacion))<0){
                            console.log('la amortizacion no puede ser mayor al saldo capital');
                            Swal.fire({
                                title:'Advertencia',
                                text:'la amortizacion no puede ser mayor al saldo capital',
                                icon:'warning',
                                position:'center',
                                confirmButton:true,
                                confirmButtonText:'Aceptar',
                            });
                            this.cancelarGestionarPlanPago();

                        }else{
                            if((parseFloat(this.amortizacion.saldo_capital_nuevo) - parseFloat(this.amortizacion.amortizacion))==0){
                                Swal.fire({
                                    title: '¿Confirma liquidar la deuda?',
                                    text: 'Esta acción liquidará la deuda. ¿Estás seguro?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Sí, llevar a cabo',
                                    cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                    // result.value es true si el usuario hace clic en "Sí, llevar a cabo"
                                    if (result.value) {
                                        // Aquí puedes realizar la acción que deseas
                                        axios.post('/liquidar_deuda', {
                                            'monto_multa':monto_multa,
                                            'monto_interes':monto_interes,
                                            'monto_capital':monto_amortizacion,
                                            
                                            'id_plan_pago':this.plan_pago.id_plan_pago,

                                            'saldo_pendiente':saldo_pendiente,
                                            'cantidad_cuotas_pagadas':cantidad_cuotas_pagadas,
                                            'total_seguro':this.amortizacion.total_seguro,


                                            'id_cuota':this.lista_cuotas_plan[this.lista_cuotas_plan.length-1].id,
                                            'forma_pago':this.amortizacion.forma_pago,

                                        }).then((response)=>{
                                            this.plan_pago.estado=response.data.estado_plan;
                                            Swal.fire('Deuda liquidada', 'La acción ha sido llevada a cabo con éxito.', 'success');
                                            this.getCuotas(this.plan_pago);
                                            this.getPlanesPago(1);
                                            this.cerrarModalAmortizacion();

                                        }).catch((error)=>{
                                            console.log(error.message);
                                            Swal.fire('Error', 'Ha ocurrido un error.', 'error');
                                        })
                                    } else {
                                        // El usuario hizo clic en "Cancelar" o cerró la notificación
                                        Swal.fire('Acción cancelada', 'La acción no fue llevada a cabo.', 'info');
                                    }
                                    });

                            }else{


                                // datos para nuevos planpago
                                let saldoCapitalNuevo = parseFloat(this.amortizacion.saldo_capital_nuevo) || 0;
                                let amortizacion = parseFloat(this.amortizacion.amortizacion) || 0;
                                let importeSolicitud = (saldoCapitalNuevo - amortizacion).toFixed(2);

                                this.solicitud.importe_solicitud = importeSolicitud;

                                // this.solicitud.nro_cuotas=0;
                                // this.solicitud.nro_cuotas=this.solicitud.nuevo_nro_cuotas.length - this.amortizacion.interes_cuotas_acumuladas;
                                this.solicitud.nro_cuotas=this.solicitud.nuevo_nro_cuotas.length;
                                
                                
                                this.solicitud.tasa=this.plan_pago.tasa;
                                this.solicitud.lapso_capital=this.plan_pago.lapso_capital;
                                //this.solicitud.moneda='';
                                this.solicitud.enviado='0';
                                this.solicitud.tipo_desembolso='Amortización';
                                this.solicitud.fecha_primera_cuota=moment().format('YYYY-MM-DD');

                                this.ahorro = false;
                                this.seguro = false;
                                this.cantidad_ahorro = '';
                                this.cantidad_seguro = '';
                                this.tasa_plan_nuevo = 'amortizable';
                                this.lista_cuotas = [];
                                this.bloqueado = false; 

                            }
                        }
                    }
                }
            },

            cancelarGestionarPlanPago(){
                this.solicitud.importe_solicitud = 0;

                this.solicitud.nro_cuotas = 0;
                this.solicitud.tasa = 0;
                this.solicitud.lapso_capital = '';
                //this.solicitud.moneda='';
                this.solicitud.enviado = '0';
                this.solicitud.tipo_desembolso = 'Amortización';

                this.ahorro = false;
                this.seguro = false;
                this.cantidad_ahorro = '';
                this.cantidad_seguro = '';
                this.tasa_plan_nuevo = 'amortizable';
                this.lista_cuotas = [];
                this.bloqueado = true; 
            },

            gestionarPlanPagoOld(){

                let cuotasPorPagar = this.lista_cuotas_plan.filter(objeto => objeto.estado === 2);
                
                let cantidad_cuotas_pagadas=cuotasPorPagar.length;
                console.log('cantidad cuotas pagadas', cantidad_cuotas_pagadas);
                this.solicitud.nro_cuotas=0;
                this.solicitud.tasa=this.plan_pago.tasa;
                this.solicitud.lapso_capital=this.plan_pago.lapso_capital;
                this.solicitud.moneda=this.plan_pago.moneda;

                // this.solicitud.tipo_desembolso=this.plan_pago.tipo_desembolso;
                this.solicitud.enviado='0';
                this.lista_cuotas=[];
                let monto_multa = (isNaN(parseFloat(this.amortizacion.monto_multa_total))?0:parseFloat(this.amortizacion.monto_multa_total));
                let monto_interes = (isNaN(parseFloat(this.amortizacion.total_interes))?0:parseFloat(this.amortizacion.total_interes));
                let monto_amortizacion = parseFloat(this.amortizacion.amortizacion) - (parseFloat(monto_multa) + parseFloat(monto_interes));
                let saldo_pendiente=this.solicitud.importe_solicitud;

                if(this.amortizacion.amortizacion<monto_multa+monto_interes){
                    console.log('el monto debe ser mayor al interes y multa que se debe');
                    Swal.fire({
                        title:'Advertencia',
                        text:'el monto debe ser mayor al interes y multa que se debe',
                        icon:'warning',
                        position:'center',
                        confirmButton:true,
                        confirmButtonText:'Aceptar',
                    });
                }else{
                    if(this.amortizacion.amortizacion<0){
                        console.log('el monto no debe ser negativo');
                        Swal.fire({
                            title:'Advertencia',
                            text:'el monto no debe ser negativo',
                            icon:'warning',
                            position:'center',
                            confirmButton:true,
                            confirmButtonText:'Aceptar',
                        });
                    }else{
                        if((parseFloat(this.amortizacion.saldo_capital_nuevo) - parseFloat(this.amortizacion.amortizacion))<0){
                            console.log('la amortizacion no puede ser mayor al saldo capital');
                            Swal.fire({
                                title:'Advertencia',
                                text:'la amortizacion no puede ser mayor al saldo capital',
                                icon:'warning',
                                position:'center',
                                confirmButton:true,
                                confirmButtonText:'Aceptar',
                            });
                        }else{
                            if((parseFloat(this.amortizacion.saldo_capital_nuevo) - parseFloat(this.amortizacion.amortizacion))==0){
                                Swal.fire({
                                    title: '¿Confirma liquidar la deuda?',
                                    text: 'Esta acción liquidará la deuda. ¿Estás seguro?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Sí, llevar a cabo',
                                    cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                    // result.value es true si el usuario hace clic en "Sí, llevar a cabo"
                                    if (result.value) {
                                        // Aquí puedes realizar la acción que deseas
                                        axios.post('/liquidar_deuda', {
                                            'monto_multa':monto_multa,
                                            'monto_interes':monto_interes,
                                            'monto_capital':monto_amortizacion,
                                            // 'detalles': JSON.stringify(this.lista_cuotas),
                                            'id_plan_pago':this.plan_pago.id_plan_pago,
                                            'saldo_pendiente':saldo_pendiente,

                                            'cantidad_cuotas_pagadas':cantidad_cuotas_pagadas,
                                            'id_cuota':this.lista_cuotas_plan[this.lista_cuotas_plan.length-1].id,
                                            'forma_pago':this.amortizacion.forma_pago,

                                        }).then((response)=>{
                                            this.plan_pago.estado=response.data.estado_plan;
                                            Swal.fire('Deuda liquidada', 'La acción ha sido llevada a cabo con éxito.', 'success');
                                            this.getCuotas(this.plan_pago);
                                            this.getPlanesPago(1);
                                            this.cerrarModalAmortizacion();

                                        }).catch((error)=>{
                                            console.log(error.message);
                                            Swal.fire('Error', 'Ha ocurrido un error.', 'error');

                                        })
                                    } else {
                                        // El usuario hizo clic en "Cancelar" o cerró la notificación
                                        Swal.fire('Acción cancelada', 'La acción no fue llevada a cabo.', 'info');
                                    }
                                    });
                            }else{
                                let importe_solicitud= (parseFloat(this.amortizacion.saldo_capital_nuevo)-(isNaN(parseFloat(this.amortizacion.amortizacion))?0:parseFloat(this.amortizacion.amortizacion))).toFixed(2);
                                this.solicitud.importe_solicitud=importe_solicitud;
                                this.abrirModalNuevoPlanPago();
                            }
                        }
                    }
                }
            },


            abrirModalNuevoPlanPago(){
                $('#modalNuevoPlanPago').modal('show');
            },
            async cerrarModalNuevoPlanPago(){
                $('#modalNuevoPlanPago').modal('hide');
            },
            ajustarMontoMulta(){
                this.amortizacion.monto_multa_total=(isNaN(parseFloat(this.amortizacion.monto_multa))?0:parseFloat(this.amortizacion.monto_multa))*(isNaN(parseFloat(this.amortizacion.multa_dias))?0: parseFloat(this.amortizacion.multa_dias));

                this.amortizacion.total_liquidacion=((isNaN(parseFloat(this.amortizacion.saldo_capital_ultimo_pago))?parseFloat(0): parseFloat(this.amortizacion.saldo_capital_ultimo_pago))+(isNaN(parseFloat(this.amortizacion.total_interes))?parseFloat(0):parseFloat(this.amortizacion.total_interes)) +(isNaN(parseFloat(this.amortizacion.monto_multa_total))?parseFloat(0): parseFloat(this.amortizacion.monto_multa_total))).toFixed(2);
                //this.amortizacion.total_liquidacion=isNaN((parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total)).toFixed(2))?0:(parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total)).toFixed(2);
                this.amortizacion.saldo_capital_nuevo=((isNaN(parseFloat(this.amortizacion.saldo_capital_ultimo_pago))?0: parseFloat(this.amortizacion.saldo_capital_ultimo_pago)) + (isNaN(parseFloat(this.amortizacion.total_interes))?0:parseFloat(this.amortizacion.total_interes)) +(isNaN(parseFloat(this.amortizacion.monto_multa_total))?0: parseFloat(this.amortizacion.monto_multa_total))).toFixed(2);// + seguro - ahorro
            },



            async amortizarPlanPago(){
                // fecha_inicio_plan

                this.cancelarGestionarPlanPago();
                await this.consultarCajaAbierta();
                this.bloqueado=true;
                this.tasa_plan_nuevo='amortizable';

                if(!this.estado_caja){
                    let fecha_actual = moment().format('YYYY-MM-DD');
                    console.log('Fecha actual: ', fecha_actual);
                    this.amortizacion.amortizacion=0;// cantidad amortizar
                    const cuotas_sin_pagar = this.lista_cuotas_plan.filter(objeto => objeto.estado === 1 && objeto.amortizado === 0);
                    this.solicitud.nuevo_nro_cuotas=cuotas_sin_pagar;
                    this.solicitud.nuevo_numero_cuota=cuotas_sin_pagar[0].numero;
                    //this.solicitud.nuevo_fecha_primera_cuota=cuotas_sin_pagar[0].fecha;
           
                    console.log('CUOTAS SIN PAGAR: ', cuotas_sin_pagar.length);
                    const ultimo_saldo_capital_objeto = cuotas_sin_pagar[0];
                    const ultimo_saldo_capital = parseFloat(ultimo_saldo_capital_objeto.saldo_capital) + parseFloat(ultimo_saldo_capital_objeto.capital);
                    console.log('ultimo saldo capital: ',ultimo_saldo_capital);
                    // ULTIMO SALDO CAPITAL
                    this.amortizacion.saldo_capital_ultimo_pago=ultimo_saldo_capital;

                    // OBTENIENDO INTERES CUOTAS
                    const array_cuotas_sin_pagar = this.lista_cuotas_plan.filter(objeto => objeto.estado === 1 &&  objeto.amortizado === 0);

                    const array_cuotas_sin_pagar_hasta_fecha = array_cuotas_sin_pagar.filter(objeto => objeto.fecha < fecha_actual);
                    console.log('array cuotas sin pagar hasta fecha: ', array_cuotas_sin_pagar_hasta_fecha);
                    // cantidad cuotas sin pagar
                    this.amortizacion.interes_cuotas_acumuladas=array_cuotas_sin_pagar_hasta_fecha.length;

                    // DIAS RESTANTES
                    this.amortizacion.hay_cuotas_sin_pagar=false;
                    const array_cuotas_sin_pagar_fecha_proxima = array_cuotas_sin_pagar.filter(objeto => objeto.fecha >= fecha_actual);

                    const array_cuotas_menor_fecha_actual = this.lista_cuotas_plan.filter(objeto => objeto.fecha < fecha_actual && (objeto.estado === 1 || objeto.estado === 2) &&  
                    objeto.amortizado === 0);

                    console.log('array cuotas menor fecha actual: ', array_cuotas_menor_fecha_actual.length);

                    if(array_cuotas_sin_pagar_fecha_proxima.length>0){
                        this.amortizacion.hay_cuotas_sin_pagar=true; 
                    }

                    this.amortizacion.dias_restantes=0;
                    if(array_cuotas_menor_fecha_actual.length>0){
                        const fecha_ultima_cuota_hasta_fecha=array_cuotas_menor_fecha_actual[array_cuotas_menor_fecha_actual.length-1].fecha;
                        const diferencia_dias = moment(fecha_actual).diff(moment(fecha_ultima_cuota_hasta_fecha), 'days');
                        this.amortizacion.dias_restantes=diferencia_dias;
                        if(array_cuotas_sin_pagar_fecha_proxima.length>0){
                            const fecha_anterior_cuota=fecha_ultima_cuota_hasta_fecha;
                            const fecha_proxima_cuota=array_cuotas_sin_pagar_fecha_proxima[0].fecha;
                            const dias_entre_anterior_siguiente=moment(fecha_proxima_cuota).diff(moment(fecha_anterior_cuota), 'days');
                            this.amortizacion.dias_entre_ultima_proxima_cuota=dias_entre_anterior_siguiente;
                        }
                    } else {
                        console.log('pasa else');
                        // const fecha_inicio_plan_pago = this.plan_pago.fecha_inicio_plan;
                        const fecha_inicio_plan_pago = this.plan_pago.fecha_ultima_amortizacion;
                        const dias_entre_inicio_y_actual= moment(fecha_actual).diff(moment(fecha_inicio_plan_pago), 'days');
                        const fecha_primera_cuota=this.lista_cuotas_plan[0].fecha;
                        const dias_entre_inicio_primer_cuota = moment(fecha_primera_cuota).diff(moment(fecha_inicio_plan_pago), 'days');
                        this.amortizacion.dias_entre_ultima_proxima_cuota = dias_entre_inicio_primer_cuota;
                        this.amortizacion.dias_restantes=dias_entre_inicio_y_actual;

                        console.log('fecha inicio plan: ', fecha_inicio_plan_pago);
                        console.log('fecha primera cuota: ', fecha_primera_cuota);

                        // dias_restantes
                    }

                    // CALCULO INTERES DIARIO
                    const interes_x_dias= parseFloat((this.amortizacion.saldo_capital_ultimo_pago * (this.plan_pago.tasa/100)) / 30);
                    const interes_dia=parseFloat(interes_x_dias * this.amortizacion.dias_restantes).toFixed(0);

                    this.amortizacion.interes_dias=interes_x_dias;
                    this.amortizacion.total_interes_dias=interes_dia// TOTAL INTERES DIAS HASTA FECHA;

                    // SUMATORIA DE INTERESES DE LAS CUOTAS VENCIDAS
                    const suma_interes = array_cuotas_sin_pagar_hasta_fecha.reduce((acumulador, objeto) => {
                        return acumulador + parseFloat(objeto.interes || 0);
                    }, 0);

                    const suma_ahorro = array_cuotas_sin_pagar_hasta_fecha.reduce((acumulador, objeto) => {
                        return acumulador + parseFloat(objeto.ahorro || 0);
                    }, 0);

                    const suma_seguro = array_cuotas_sin_pagar_hasta_fecha.reduce((acumulador, objeto) => {
                        return acumulador + parseFloat(objeto.seguro || 0);
                    }, 0);

                    this.amortizacion.total_interes=parseFloat(parseFloat(suma_interes) + parseFloat(this.amortizacion.total_interes_dias)).toFixed(2);
                    this.amortizacion.total_ahorro=suma_ahorro;
                    this.amortizacion.total_seguro=suma_seguro;

                    const index_registro_mora=this.primerRegistroConMora();
                    this.amortizacion.tiene_mora=false;
                    this.amortizacion.multa_dias=0;
                    if(index_registro_mora!=null){
                        this.amortizacion.tiene_mora=true;
                        this.amortizacion.multa_dias=index_registro_mora.dias_pasados;
                    }

                    // TOTAL LIQUIDACION
                    this.amortizacion.total_liquidacion= parseFloat(this.amortizacion.monto_multa_total) +  parseFloat(this.amortizacion.total_interes) + parseFloat(this.amortizacion.saldo_capital_ultimo_pago) 
                    + parseFloat(this.amortizacion.total_seguro) -parseFloat(this.amortizacion.total_ahorro);

                    this.amortizacion.saldo_capital_nuevo=this.amortizacion.total_liquidacion;

                    

                    // ULTIMAS INSTRUCCIONES
                    this.amortizacion.liquidar_deuda=false;
                    this.abrirModalAmortizacion();
                    this.amortizacion.cliente=this.plan_pago.cliente;
                    this.amortizacion.id_plan_pago=this.plan_pago.id_plan_pago;

                    // para calculo de intereses cuotas
                    this.lista_cuotas_amortizacion=this.lista_cuotas_plan;

                }
            },
            async amortizarPlanPagoOld(){
                await this.consultarCajaAbierta();

                if(!this.estado_caja){
                    this.amortizacion.amortizacion=0;

                    // calculando saldo_capital
                    let ultimo_saldo_capital=0;// estado 1:cuota sin pagar;
                    let objeto_ultimo_saldo_capital = this.lista_cuotas_plan.filter(objeto => objeto.estado === 1 && objeto.amortizado === 0);
                    let ultimoSaldoCapital = objeto_ultimo_saldo_capital[0];

                    // sumatorioa de ultima cuota no cancelada (saldo capital + capital)
                    ultimo_saldo_capital=parseFloat(ultimoSaldoCapital.saldo_capital) + parseFloat(ultimoSaldoCapital.capital);

                    // let fechaActual = new Date().toISOString().split('T')[0];//'yy/mm/dd'
                    let fechaActual = moment().format('YYYY-MM-DD');

                    //let fechaActual = '2023-12-06';

                    console.log('fecha actual iso', fechaActual);
                    // obteniendo ultimas cuotas no canceladas

                    // Filtra la lista para obtener objetos con estado igual a 1 y fecha menor a la fecha actual
                    let objetosFiltrados = this.lista_cuotas_plan.filter(objeto => objeto.estado === 1 && objeto.fecha < fechaActual &&  objeto.amortizado === 0);
                    console.log('objetos filtrados', objetosFiltrados);

                    let cuotas_sin_pagar = this.lista_cuotas_plan.filter(objeto => objeto.estado === 1 && objeto.amortizado === 0);
                    // Suma la propiedad Interes de los objetos filtrados
                    let sumaInteres = objetosFiltrados.reduce((acumulador, objeto) => acumulador + objeto.interes, 0);
                    let sumaAhorro = objetosFiltrados.reduce((acumulador, objeto) => acumulador + objeto.ahorro, 0);
                    let sumaSeguro= objetosFiltrados.reduce((acumulador, objeto) => acumulador + objeto.seguro, 0);
                    console.log('suma interes:', sumaInteres);

                    // Filtra la lista para obtener solo los objetos con estado igual a 1
                    let cuotasPorPagar = this.lista_cuotas_plan.filter(objeto => objeto.estado === 1 && objeto.fecha > fechaActual && objeto.amortizado === 0);
                    console.log(cuotasPorPagar[0]);
                    this.amortizacion.numero_cuota_inicio=cuotas_sin_pagar[0].numero;
                    // calculado diferencia en dias

                    // Convierte las fechas a objetos Date
                    let fechaActualObjeto = new Date(fechaActual);
                    console.log('cuotas por pagar',  cuotasPorPagar.length);

                    let fechaCuotaObjeto = (cuotasPorPagar.length>0)?new Date(cuotasPorPagar[0].fecha):new Date(fechaActual);
                    console.log('longitud:',objetosFiltrados.length);

                    // let objetosFiltradosPagNoPag = this.lista_cuotas_plan.filter(objeto1 =>( objeto1.estado === 1 ||  objeto1.estado === 2) && objeto1.fecha < fechaActual);
                    let objetosFiltradosPagNoPag = this.lista_cuotas_plan.filter(objeto1 =>( objeto1.estado === 1 ||  objeto1.estado === 2) && objeto1.fecha <= fechaActual && objeto1.amortizado === 0);

                    // let fechaAnteriorCuotaObjeto = (objetosFiltradosPagNoPag.length>0)? new Date(objetosFiltradosPagNoPag[objetosFiltradosPagNoPag.length-1].fecha):new Date((objetosFiltradosPagNoPag.length<=0)?fechaActual:objetosFiltradosPagNoPag[0].fecha);
                    let fechaAnteriorCuotaObjeto = (objetosFiltradosPagNoPag.length>0)? new Date(objetosFiltradosPagNoPag[objetosFiltradosPagNoPag.length-1].fecha):new Date((objetosFiltradosPagNoPag.length<=0)?new Date(this.plan_pago.fecha_inicio_plan):objetosFiltradosPagNoPag[0].fecha);
                    //let fechaAnteriorCuotaObjeto = (objetosFiltradosPagNoPag.length>0)? new Date(objetosFiltradosPagNoPag[objetosFiltradosPagNoPag.length-1].fecha):new Date((objetosFiltradosPagNoPag.length<=0)?fechaActual:objetosFiltradosPagNoPag[0].fecha);// ultimo change

                    // para calcular la cantidad de dias de primar cuota vencida a fecha actual
                    // let fechaPrimeraCuotaVencida = (objetosFiltrados.length>0)? new Date(objetosFiltrados[0].fecha):0;
                    // let fechaPrimeraCuotaVencida = (objetosFiltrados.length>0)? new Date(objetosFiltrados[0].fecha):new Date(this.plan_pago.fecha_inicio_plan);//ultimo change
                    let fechaPrimeraCuotaVencida = (objetosFiltrados.length>0)? new Date(objetosFiltrados[0].fecha):new Date(fechaActual);

                    // Calcula la diferencia en milisegundos entre las dos fechas
                    console.log('fecha cuota objeto',fechaCuotaObjeto, 'fecha actual objeto',fechaActualObjeto);
                    let diferenciaEnMilisegundos = fechaCuotaObjeto - fechaActualObjeto;
                    console.log('diferencia milisegundos', diferenciaEnMilisegundos);
                    // diferencia entre ultima cuota cumplida y cuota por pagar
                    let diferenciaEnMilisegundosAnterior = fechaCuotaObjeto  - fechaAnteriorCuotaObjeto;
                    // para calcular la cantidad de dias de primar cuota vencida a fecha actual
                    let diferenciaEnMilisegundosfechaPrimeraCuotaVencida = (fechaPrimeraCuotaVencida!=0)?fechaActualObjeto  - fechaPrimeraCuotaVencida: 0;


                    // Convierte la diferencia a días
                    let diferenciaEnDias = diferenciaEnMilisegundos / (1000 * 60 * 60 * 24);
                    // Convierte la diferencia a días de cuota anterior
                    let diferenciaEnDiasAnterior = diferenciaEnMilisegundosAnterior / (1000 * 60 * 60 * 24);
                    // Convierte la diferencia a días de primera cuota vencida a fecha actual
                    let diferenciaEnDiasfechaPrimeraCuotaVencida = (diferenciaEnMilisegundosfechaPrimeraCuotaVencida>0)?diferenciaEnMilisegundosfechaPrimeraCuotaVencida / (1000 * 60 * 60 * 24):0;



                    this.amortizacion.saldo_capital_ultimo_pago=ultimo_saldo_capital;
                    this.amortizacion.interes_cuotas_acumuladas=sumaInteres;

                    this.amortizacion.total_ahorro=sumaAhorro;
                    this.amortizacion.total_seguro=sumaSeguro;


                    this.amortizacion.dias_restantes=diferenciaEnDias>diferenciaEnDiasAnterior?0:diferenciaEnDias;
                    this.amortizacion.dias_entre_ultima_proxima_cuota=diferenciaEnDias>diferenciaEnDiasAnterior?0:diferenciaEnDiasAnterior;
                    console.log('FECHA ACTUAL:',fechaActual);
                    console.log('FECHA FIN PLAN:',this.plan_pago.fecha_fin_plan);
                    if(fechaActual>this.plan_pago.fecha_fin_plan){
                        console.log('ARRAY CUOTAS:', cuotasPorPagar);

                        this.amortizacion.interes_dias=this.lista_cuotas_plan[0].interes/30;

                    }else{
                        this.amortizacion.interes_dias=(cuotasPorPagar.length>0)?cuotasPorPagar[0].interes/diferenciaEnDiasAnterior:0;

                    }
                    console.log('interes dias prueba:',this.amortizacion.interes_dias);
                    // this.amortizacion.total_interes_dias=this.amortizacion.interes_dias*diferenciaEnDias;

                    this.amortizacion.total_interes_dias=parseFloat(this.amortizacion.interes_dias)*(parseFloat(this.amortizacion.dias_entre_ultima_proxima_cuota)-parseFloat(this.amortizacion.dias_restantes));



                    this.amortizacion.total_interes=parseFloat(this.amortizacion.interes_cuotas_acumuladas+this.amortizacion.total_interes_dias).toFixed(2);
                    this.amortizacion.multa_dias=diferenciaEnDiasfechaPrimeraCuotaVencida;
                    this.amortizacion.total_liquidacion=isNaN((parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total)).toFixed(2))?0:(parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total) + (parseFloat(this.amortizacion.total_seguro)) - (parseFloat(this.amortizacion.total_ahorro))).toFixed(2);
                    this.amortizacion.saldo_capital_nuevo=isNaN(parseFloat(parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.multa_dias)))?0:parseFloat(parseFloat(this.amortizacion.saldo_capital_ultimo_pago)+parseFloat(this.amortizacion.total_interes)+parseFloat(this.amortizacion.monto_multa_total)+ (parseFloat(this.amortizacion.total_seguro)) - (parseFloat(this.amortizacion.total_ahorro)));// + seguro - ahorro
                    //this.amortizacion.id_cuota=
                    console.log(diferenciaEnDias);
                    this.amortizacion.liquidar_deuda=false;

                    this.abrirModalAmortizacion();
                }

            },
            abrirModalAmortizacion(){
               $('#modalAmortizacion').modal('show');
            },
            async cerrarModalAmortizacion(){
               $('#modalAmortizacion').modal('hide');
            },
            buscarPlanPago(){
                if(this.criterio!='users.personal'){
                    //this.buscar='';
                    this.getPlanesPago(1);
                }
            },
            buscarPlanPagoAsesor(){
                if(this.criterio=='users.personal'){
                    this.criterio='users.personal';
                    this.buscar=this.criterio_asesor;
                    this.getPlanesPago(1);
                }
            },

            activarPlanPago(item){
                let activado=false;
                axios.post('/activar_planpago', {id_planpago:item.id}).then((response)=>{
                        console.log(response);
                        activado=true;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        if(activado){
                            Swal.fire({
                                position: 'top-right',
                                icon: 'success',
                                title: 'Operación exitosa',
                                text: 'Plan de pago activado con exito!',
                                // showConfirmButton: true,
                                // showCancelButton: true,
                                // confirmButtonText:'Aceptar',
                                // cancelButtonText:'Cancelar',
                                timer: 1500
                            });
                            this.getPlanesPago(1);
                        }else{
                            console.log('ocurrio un error al activar');
                        }

                    })
            },
            generarPdfCuotasPlanPago(){
                // Construye la URL con el parámetro fecha_inicio
                const url = '/lista_cuotas_planpago_pdf?id_planpago='+this.plan_pago.id_plan_pago;

                // Abre una nueva pestaña o ventana con la URL
                window.open(url, '_blank');
            },
            async consultarPlanVigente(plan_pago_id){
                let resultado=0;
                await axios.get('/consulta_plan_pago_vigente?id_planpago='+plan_pago_id)
                .then((response)=>{
                    console.log(response);
                    resultado=response.data.respuesta;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    if(resultado==0){
                        this.plan_pago_vigente=false;
                    }else{
                        if(resultado==1){
                            this.plan_pago_vigente=true;
                        }
                    }

                })
            },
            async anularPlanPago(item){
                await this.consultarPlanVigente(item.id);
                if(this.plan_pago_vigente){
                    console.log('El plan de pago no se puede anular, porque ya tiene pagos cancelados');
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'No se puede anular plan de pago, ya tiene cuotas canceladas!',
                        showConfirmButton: true,
                        // showCancelButton: true,
                        confirmButtonText:'Aceptar',
                        // cancelButtonText:'Cancelar',
                        //timer: 1500
                    });
                }else{
                    let anulado_planpago=false;
                    await axios.post('/anular_planpago', {id_planpago:item.id}).then((response)=>{
                        console.log(response);
                        anulado_planpago=true;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        if(anulado_planpago){
                            Swal.fire({
                                position: 'top-right',
                                icon: 'success',
                                title: 'Operación exitosa',
                                text: 'Plan de pago anulado con exito!',
                                // showConfirmButton: true,
                                // showCancelButton: true,
                                // confirmButtonText:'Aceptar',
                                // cancelButtonText:'Cancelar',
                                timer: 1500
                            });
                            this.getPlanesPago(1);

                        }else{
                            console.log('ocurrio un error al anular plan de pago')
                        }
                    })
                }
            },

            anularCuota(){
                let eliminado=false;
                axios.post('/anular_pago', {id_pago:this.pago.id_pago, id_plan_pago:this.plan_pago.id_plan_pago, nro_cuotas:this.plan_pago.nro_cuotas, id_cuota:this.pago.id_cuota})
                .then((response)=>{
                    console.log('respuesta', response);
                    eliminado=true;
                })
                .catch((error)=>{
                    console.log(error.message);

                })
                .finally(()=>{
                    if(eliminado){
                        Swal.fire({
                            position: 'top-right',
                            icon: 'success',
                            title: 'Operación exitosa',
                            text: 'Pago anulado con exito!',
                            // showConfirmButton: true,
                            // showCancelButton: true,
                            // confirmButtonText:'Aceptar',
                            // cancelButtonText:'Cancelar',
                            timer: 1500
                        });
                        this.getCuotas(this.plan_pago);
                        let plan_pago_cancelado=true;
                        for(let i=0;i<this.lista_cuotas_plan.length;i++){
                            if(this.lista_cuotas_plan[i].estado==1){
                                plan_pago_cancelado=false;
                            }
                        }
                        if(plan_pago_cancelado){
                            this.plan_pago.estado_plan=2;
                        }else{
                            this.plan_pago.estado_plan=1;
                        }
                        $('#modalVerPago').modal('hide');
                    }
                })
            },
            abrirModalVerPago(item){
                axios.get('/get_pago?id_cuota='+item.id).then((response)=>{
                    console.log('respuesta',response.data[0]);
                    let pago_obtenido=response.data[0];
                    this.pago.id_pago=pago_obtenido.id;
                    this.pago.fecha_pago=pago_obtenido.fecha_pago;
                    this.pago.monto_pago=pago_obtenido.monto_pago;
                    this.pago.id_cuota=pago_obtenido.id_cuota;
                    this.pago.usuario=pago_obtenido.usuario;

                }).catch((error)=>{
                    console.log(error.message);
                })
                .finally(()=>{

                })

                $('#modalVerPago').modal('show');
            },
            cerrarModalVerPago(){
                $('#modalVerPago').modal('hide');
            },
            pagarCuota(){
                this.pago.id_plan_pago=this.plan_pago.id_plan_pago;
                let guardar=false;
                let me=this;
                axios.post('/save_pago', this.pago)
                .then((response)=>{
                    console.log(response);
                    guardar=true;
                    this.plan_pago.estado_plan=response.data.estado_plan;

                })
                .catch((error)=>{
                    console.log(error.message);
                })
                .finally(function(){
                    if(guardar){
                        Swal.fire({
                            position: 'top-right',
                            icon: 'success',
                            title: 'Operación exitosa',
                            text: 'Cuota cancelada con exito!',
                            // showConfirmButton: true,
                            // showCancelButton: true,
                            // confirmButtonText:'Aceptar',
                            // cancelButtonText:'Cancelar',
                            timer: 1500
                        });
                        me.getCuotas(me.plan_pago);
                        me.getPlanesPago(1);
                        $('#modalPagoCuota').modal('hide');

                    }
                })
            },
            anterioresCuotasPagadas(posicion){
                let pagados=true;
                for(let i=0; i<posicion;i++){
                    if(this.lista_cuotas_plan[i].estado==1){
                        pagados=false;
                    }
                }
                return pagados;
            },
            anterioresCuotasPagadasParaAnular(posicion){
                let pagados=true;
                for(let i=0; i<posicion;i++){
                    if(this.lista_cuotas_plan[i].estado==1){
                        pagados=false;
                    }
                }
                return pagados;
            },
            abrirModalPagarCuota(item){
                $('#modalPagoCuota').modal('show');
                this.cuota.numero=item.numero;
                this.cuota.total=item.total;
                this.cuota.id_cuota=item.id;

                this.pago.monto_pago=this.cuota.total;
                this.pago.id_cuota=this.cuota.id_cuota;
            },
            cerrarModalPago(){
                $('#modalPagoCuota').modal('hide');
            },
            gestionarEstado(estado_plan){
                if(estado_plan==1){
                    return 'Activo';
                }else if(estado_plan==0){
                    return 'Anulado';
                }
                else if(estado_plan==2){
                    return 'Cancelado';
                }
                else if(estado_plan==10){
                    return 'Amortizado';
                }
            },
            cerrarModalCuotas(){
                $('#modalCuotas').modal('hide');
            },
            async abrirModalVerCuotas(item){

                this.preloader=true;
                this.prueba=0;
                this.plan_pago.id_plan_pago=item.id;
                this.plan_pago.id=item.id;
                this.plan_pago.cliente=item.cliente;
                this.plan_pago.ci=item.ci;
                this.plan_pago.lugar_expedicion=item.lugar_expedicion;
                this.plan_pago.tipo_garantia=item.tipo_garantia;
                this.plan_pago.nro_cuotas=item.nro_cuotas;
                this.plan_pago.lapso_capital=item.lapso_capital;
                this.plan_pago.tasa=item.tasa;
                this.plan_pago.fecha_inicio_plan=item.fecha_inicio_plan;
                this.plan_pago.fecha_ultima_amortizacion=item.fecha_ultima_amortizacion;
                this.plan_pago.fecha_fin_plan=item.fecha_fin_plan;
                this.plan_pago.total_pagar_plan=item.total_pagar_plan;
                this.plan_pago.estado_plan=item.estado_plan;
                this.plan_pago.id_cliente=item.id_cliente;
                this.plan_pago.id_solicitud=item.id_solicitud;
                this.plan_pago.tipo_desembolso=item.tipo_desembolso;
                this.plan_pago.moneda=item.moneda;
                this.plan_pago.estado=item.estado_plan;
                
                $('#modalCuotas').modal('show');

                await this.getCuotas(this.plan_pago);
                //await this.getPlanesPagoLigados(item);
                //await this.getAmortizaciones();
                console.log('lista amortizaciones: ', this.lista_amortizaciones);
                const cant=this.lista_planes_pago_ligados.length;
                console.log('CANTIDAD PLANES LIGADOS: ', cant);
                this.preloader=false;


            },
            async getCuotas(item){
                this.lista_cuotas_plan=[];
                await axios.get('/listar_amortizaciones_cuotas?id_plan_pago='+item.id_plan_pago)
                .then((response)=>{
                    console.log(response);
                    this.lista_cuotas_plan=response.data;
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },

            /*async getCuotas(item) {
                try {
                    // Primera solicitud para obtener los participantes
                    const response = await axios.get('/get_cuotas_plan?id_plan_pago='+item.id_plan_pago);
                    
                    // Iteramos sobre las cuotas
                    const cuotasAmortizaciones = await Promise.all(
                        response.data.map(async (cuota) => {
                            // Segunda solicitud para obtener las pruebas del participante
                            const amortizaciones_cuota = await axios.get(`/listar_amortizaciones?id_cuota=${cuota.id}`);
                            
                            // Añadimos las pruebas al objeto participante
                            cuota.amortizaciones = amortizaciones_cuota.data;
                            
                            return cuota;  // Retornamos el participante con las pruebas añadidas
                        })
                    );

                    // Asignamos los participantes con sus pruebas inscritas al arreglo
                    this.lista_cuotas_plan = cuotasAmortizaciones;
                } catch (error) {
                    console.log(error.message);
                }
            },*/

            async getPlanesPago(page){
                await axios.get('/get_planespago?page='+page+'&criterio='+this.criterio+'&buscar='+this.buscar+'&opcion_asesor='
                +this.opcion_asesor+'&estado_credito='+this.estado_credito).then((response)=>{
                    console.log(response);
                    // this.lista_planespago=response.data.data;
                    // this.pagination={total:response.data.total,
                    //         current_page:response.data.current_page,
                    //         per_page: response.data.per_page,
                    //         last_page: response.data.last_page,
                    //         from: response.data.from,
                    //         to: response.data.to
                    // }
                    this.lista_planespago=response.data;
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
                me.getPlanesPago(page);
            },









        },
        async mounted() {
            this.preloader=true;
            console.log('Component mounted.');
            await this.getCodeudoresTabla();
            await this.getUsuarios();
            await this.getPlanesPago(1);
            await this.getAsesores();
            await this.getCantidadClienteAsesor();
            this.preloader=false;


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
    .position-relative {
        position: relative; /* Necesario para contener el overlay */
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Color oscuro con opacidad */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10; /* Asegura que el overlay esté encima de todo el contenido */
    }

    .overlay-text {
        color: #fff; /* Color del texto */
        font-size: 18px;
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

<style>
    .small-title {
        font-size: 0.60rem; /* Tamaño de letra pequeño */
        color: #b42100; /* Color gris similar a 'text-muted' de Bootstrap */
        margin-bottom: 1px; /* Espacio entre el título y el valor */
    }

    .cell-value {
        font-size: 0.75rem; /* Tamaño de letra ligeramente más pequeño que el normal */
    }

    .badge-fixed-width {
        width: 70px; /* Establece el ancho que desees */
        display: inline-block; /* Necesario para que el ancho surta efecto en elementos inline */
        text-align: center; /* Opcional: centra el texto dentro del badge */
    }
</style>


