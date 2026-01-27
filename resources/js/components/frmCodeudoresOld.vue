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
                                    <i class="fas fa-user-shield"></i>
                                    Gestión de codeudores/garantes</h4>
                               
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
                                            <div class="col-md-3">
                                                <label for="criterio" class="text-dark">Criterio de Búsqueda</label>
                                                <select v-model="criterio" class="form-select form-control">
                                                    <option value="codeudor.nombre">Nombre Codeudor</option>
                                                    <option value="codeudor.ci">CI Codeudor</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="buscar" class="text-dark">Buscar</label>
                                                <div class="input-group">
                                                    <input v-model="buscar" type="text" class="form-control" @input="buscarCliente()">
                                                    <button class="btn btn-success btn-sm">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <h6 class="mt-3">Listado de Codeudores</h6>
                                        <div class="table-responsive" style="font-size:12px">
                                            <table class="table mb-4 table-hover table-bordered table-striped table-sm">
                                                <thead class="bg-primary text-white text-uppercase" >
                                                    <tr style="background-color:#52BE80" >
                                                        <!-- <th>Foto</th> -->
                                                        <th>Nombre</th>
                                                        <th>CI</th>
                                                        <th>Sexo</th>
                                                        <th>Estado civil</th>
                                                        <th>Actividad</th>
                                                        <th>Vivienda</th>
                                                        <th>Estado</th>
                                                        <th>Opciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="" v-for="item in lista_clientes" :key="item.id">
                                                        <!-- <td>
                                                            <img :src="'img/cliente/'+(item.imagen==null || item.imagen==''?'default.png':item.imagen)" alt=""
                                                                    class="rounded avatar-md shadow">
                                                        </td> -->
                                                        <td>{{ item.nombre }}</td>
                                                        <td>{{ item.ci }}</td>
                                                        <td>{{ item.sexo }}</td>
                                                        <td>{{ item.estado_civil }}</td>
                                                        <td>{{ item.actividad }}</td>
                                                        <td>{{ item.vivienda }}</td>
                                                        <!-- <td>{{ item.estado }}</td> -->
                                                        <td>
                                                            <span v-if="item.estado==1"
                                                                class="text-success">Activo</span>
                                                            <span v-else class="text-danger">Inactivo</span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group my-0 py-0">
                                                                <a 
                                                                    style="cursor:pointer;" class="dropdown-toggle btn-sm my-0 py-0 text-success"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-ellipsis-h fa-lg fa-fw fs-4"></i>
                                                                </a>
                                                                <ul class="dropdown-menu my-0 py-0">
                                                                    <li @click="desactivarCodeudor(item)" v-if="item.estado==1">
                                                                        <a class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-times"></i> desactivar</a>
                                                                    </li>
                                                                    <li @click="activarCodeudor(item)" v-else><a
                                                                            class="dropdown-item text-success" href="#">
                                                                            <i class="fas fa-check"></i> activar</a>
                                                                    </li>
                                                                    <li @click="editarCodeudor(item)"><a
                                                                            class="dropdown-item text-primary" href="#">
                                                                            <i class="fas fa-pencil-alt"></i> editar</a></li>
                                                                    <li @click="verCodeudor(item)"><a
                                                                            class="dropdown-item text-info" href="#">
                                                                            <i class="fas fa-eye"></i> ver</a></li>
                                                                    <li @click="verInformacionCodeudor(item)"><a
                                                                            class="dropdown-item text-danger" href="#">
                                                                            <i class="fas fa-file-pdf"></i> pdf</a></li>

                                                                    <li @click="abrirModalFoto(item)"><a
                                                                            class="dropdown-item text-warning" href="#">
                                                                            <i class="fas fa-user"></i> {{ item.imagen=='' || item.imagen==null? 'Agregar foto':'Ver/Cambiar foto'}}</a></li>
                                                                    
                                                                            
                                                                </ul>
                                                            </div>
                                                        </td>

                                                    </tr> 
                                                </tbody>
                                            </table>
                                            <br>
                                            <br>
                                        </div>
                                        <!-- Card Pagination -->
                                        <!-- <div class="card-footer py-4">
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
                                        </div> -->
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

        <!-- Modal cliente -->
        <div class="modal fade bs-example-modal-xl" data-bs-backdrop="static" id="modalCodeudor" tabindex="-1"
            aria-labelledby="miModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" style="width:90%; max-width:90%">
                <div class="modal-content border border-success border-2">
                    <div class="modal-header bg-success">
                        <h5 v-if="cliente.accion==0" class="modal-title text-white" id="miModalLabel">Agregar nuevo garante/codeudor</h5>
                        <h5 v-if="cliente.accion==1" class="modal-title text-white" id="miModalLabel">Modificar garante/codeudor</h5>
                        <h5 v-if="cliente.accion==2" class="modal-title text-white" id="miModalLabel">Información del garante/codeudor: {{ cliente.nombre   }} - {{cliente.ci+' '+cliente.lugar_expedicion}}</h5>
                        <button @click="cerrarModalNuevo()" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="row">
                                <!-- Campo Nombre -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input v-model="cliente.nombre" type="text" class="form-control" id="nombre" name="nombre" :disabled="cliente.accion == 2" required>
                                    <p class="text-danger text-sm-start" v-if="cliente.nombre == '' && cliente.enviado == 1">Ingrese un nombre *</p>
                                </div>

                                <!-- Campo Fecha de Nacimiento -->
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input v-model="cliente.fecha_nacimiento" type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" :disabled="cliente.accion == 2" required>
                                </div>

                                <!-- Campo CI -->
                                <div class="col-md-3 mb-3">
                                    <label for="ci" class="form-label">CI</label>
                                    <input v-model="cliente.ci" type="text" class="form-control" id="ci" name="ci" :disabled="cliente.accion == 2" required>
                                    <p class="text-danger text-sm-start" v-if="cliente.ci == '' && cliente.enviado == 1">Ingrese un CI *</p>
                                </div>

                                <!-- Campo Lugar de Expedición -->
                                <div class="col-md-3 mb-3">
                                    <label for="lugar_expedicion" class="form-label">DPTO</label>
                                    <select v-model="cliente.lugar_expedicion" class="form-control form-select" id="lugar_expedicion" name="lugar_expedicion" :disabled="cliente.accion == 2" required>
                                        <option value="0" selected hidden disabled>Seleccione</option>
                                        <option v-for="item in lugares_expedicion" :value="item.sigla" :key="item.sigla">{{ item.sigla }}</option>
                                    </select>
                                    <p class="text-danger text-sm-start" v-if="(cliente.lugar_expedicion == '0' || cliente.lugar_expedicion == '') && cliente.enviado == 1">Seleccione *</p>
                                </div>

                                <!-- Campo Sexo -->
                                <div class="col-md-3 mb-3">
                                    <label for="sexo" class="form-label">Sexo</label>
                                    <select v-model="cliente.sexo" class="form-control form-select" id="sexo" name="sexo" :disabled="cliente.accion == 2" required>
                                        <option value="0" selected hidden disabled>Seleccione un sexo</option>
                                        <option v-for="item in sexos" :value="item.nombre" :key="item.nombre">{{ item.nombre }}</option>
                                    </select>
                                    <p class="text-danger text-sm-start" v-if="(cliente.sexo == '0' || cliente.sexo == '') && cliente.enviado == 1">Seleccione un sexo *</p>
                                </div>

                                <!-- Campo Estado Civil -->
                                <div class="col-md-3 mb-3">
                                    <label for="estado_civil" class="form-label">Estado Civil</label>
                                    <select v-model="cliente.estado_civil" class="form-control form-select" id="estado_civil" name="estado_civil" :disabled="cliente.accion == 2" required>
                                        <option value="0" selected hidden disabled>Seleccione un estado civil</option>
                                        <option v-for="item in estados_civil" :value="item.nombre" :key="item.nombre">{{ item.nombre }}</option>
                                    </select>
                                    <p class="text-danger text-sm-start" v-if="(cliente.estado_civil == '0' || cliente.estado_civil == '') && cliente.enviado == 1">Seleccione un estado civil *</p>
                                </div>

                                <!-- Campo Ingreso Mensual -->
                                <div class="col-md-3 mb-3">
                                    <label for="ingreso_mensual" class="form-label">Ingreso Mensual</label>
                                    <input v-model="cliente.ingreso_mensual" type="text" class="form-control" id="ingreso_mensual" name="ingreso_mensual" :disabled="cliente.accion == 2" required
                                        onkeydown="if(event.key === '.' && event.target.value.includes('.')) { event.preventDefault(); }" 
                                        oninput="event.target.value = event.target.value.replace(/[^0-9.]*/g, '');">
                                    <p class="text-danger text-sm-start" v-if="cliente.ingreso_mensual == '' && cliente.enviado == 1">Ingrese un ingreso mensual *</p>
                                </div>

                                <!-- Campo Actividad -->
                                <div class="col-md-6 mb-3">
                                    <label for="buscarActividad" class="form-label fw-semibold text-dark">Seleccione una actividad:</label>
                                    <div class="input-group">
                                        <input type="text" v-model="actividadClase.buscar" id="buscarActividad" class="form-control text-dark" placeholder="Buscar actividad..." 
                                            @input="filtrarActividades(actividadClase.buscar)" autocomplete="off" :disabled="cliente.accion == 2" required>
                                    </div>
                                    <template v-if="filteredItemsActividades.length > 0">
                                        <div class="com-completion-results shadow" style="z-index: 1050; position: absolute; top: 100%; width: 100%; background: #fff; border: 1px solid #ececec; max-height: 250px; overflow: auto;"
                                            v-bind:style="{ display: filteredItemsActividades.length > 0 && actividadClase.buscar != '' ? 'block' : 'none' }">
                                            <ul style="list-style: none; padding: 0; margin: 0;">
                                                <li v-for="(actividadItem, index) in filteredItemsActividades" :key="index" @click="seleccionarActividad(actividadItem)" 
                                                    style="cursor: pointer; padding: 8px; border-bottom: 1px solid #ececec;" class="dropdown-item-hover">
                                                    <h6 style="font-size: 14px; color: #000; margin: 0;">{{ actividadItem.nombre }}</h6>
                                                </li>
                                            </ul>
                                        </div>
                                    </template>
                                    <p class="text-danger text-sm-start" v-if="cliente.actividad == '' && cliente.enviado == 1">Ingrese una actividad *</p>
                                </div>

                                <!-- Campo Vivienda -->
                                <div class="col-md-3 mb-3">
                                    <label for="vivienda" class="form-label">Vivienda</label>
                                    <select v-model="cliente.vivienda" class="form-control form-select" id="vivienda" name="vivienda" :disabled="cliente.accion == 2" required>
                                        <option value="0" selected hidden disabled>Seleccione un tipo vivienda</option>
                                        <option v-for="item in viviendas" :value="item.nombre" :key="item.nombre">{{ item.nombre }}</option>
                                    </select>
                                    <p class="text-danger text-sm-start" v-if="(cliente.vivienda == '0' || cliente.vivienda == '') && cliente.enviado == 1">Seleccione un tipo vivienda *</p>
                                </div>

                                

                                <!-- Campo Tipo (Nuevo) -->
                                <div class="col-md-3 mb-3">
                                    <label for="tipo" class="form-label">Tipo</label>
                                    <select v-model="cliente.tipo" class="form-control form-select" id="tipo" name="tipo" :disabled="cliente.accion == 2" required>
                                        <option value="0" selected hidden disabled>Seleccione un tipo</option>
                                        <option value="Garante">Garante</option>
                                        <option value="Codeudor">Codeudor</option>
                                    </select>
                                    <p class="text-danger text-sm-start" v-if="(cliente.tipo == '0' || cliente.tipo == '') && cliente.enviado == 1">Seleccione un tipo *</p>
                                </div>
                            </div>
                            <hr>
                            <p><strong>
                                DIRECCIONES DE CONTACTO
                            </strong></p>
                            
                            <div class="card border border-success border-2" v-for="(item, index) in lista_direcciones" :key="index">
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
                                                <label for="tipoDireccion" class="my-0 text-dark" style="font-size:12px;">Tipo</label>
                                                <select class="form-control form-control-sm form-select" v-model="item.tipo" id="tipoDireccion">
                                                    <option value="" selected hidden disabled>Seleccione...</option>
                                                    <option v-for="tipo in tiposDeDirecciones" :key="tipo" :value="tipo">
                                                        {{ tipo }}
                                                    </option>
                                                </select>
                                                <!-- <input :disabled="cliente.accion==2?true:false" type="text" v-model="item.tipo" class="form-control form-control-sm" placeholder="Ingrese un tipo"> -->
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
                                                <select :disabled="cliente.accion==2?true:false" v-model="item.tipo" name="" id="" class="form-select form-control-sm form-control">
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

                                        <div class="col-md-3" v-if="item.tipo=='Informacion contacto'">
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
                                <button :disabled="guardando_cliente" v-if="cliente.accion==0" @click="guardarCodeudor()" type="button" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Guardar</button>
                                <button v-if="cliente.accion==1" @click="modificarCodeudor()" type="button" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Modificar</button>
                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalFoto" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mySmallModalLabel">Foto del cliente</h5>
                        <button @click="cerrarModalFoto()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 p-3">
                                <div class="image-container">
                                    <img :src="cliente.imagen!=''?'/img/cliente/'+cliente.imagen:'/img/cliente/default.png'" alt="" >
                                </div>
                                <input class="form-control" type="file" name="" id="" @change="seleccionarImagen($event)">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="container text-end">
                            <button @click="cerrarModalFoto()" class="btn btn-secondary mx-2">
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
        <!-- Elemento donde se mostrará el toast -->
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
                actividades:[],
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
                    fecha_nacimiento: moment().format('YYYY-MM-DD'),
                    ci: '',
                    ingreso_mensual:'',
                    tipo:'Codeudor',
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
                    tipo:true,
                    actividad:true,
                    estado_civil: true,
                    vivienda: true,
                    sexo: true,
                    lugar_expedicion: true,
                },
                lista_direcciones: [{
                    id_direccion: 0,
                    tipo: '',
                    departamento: '',
                    ciudad: '',
                    zona: '',
                    descripcion: '',
                    referencia: '',
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
                criterio:'codeudor.nombre',
                buscar:'',
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
            buscarCliente(){
                this.getCodeudores(1);
            },
            //@error=""
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
                // if(this.cliente.imagen==''){
                //     this.mostrarToastError('Debe seleccionar una imagen');
                // }else{
                    const formData = new FormData();
                    formData.append('id_codeudor', this.cliente.id_cliente);
                    formData.append('imagen', this.cliente.imagen);
            
                    axios.post('/fotoCodeudor', formData)
                    .then((response)=>{
                        console.log(response);
                        this.cliente.imagen=response.data.imagen;
                    })
                    .catch((error)=>{
                        console.log(error.message);
                    })
                    .finally(()=>{
                        this.getCodeudores(1);
                    })
                // }
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
                me.getCodeudores(page);
            },
            abrirModalNuevo() {
                $('#modalCodeudor').modal('show');
                this.lista_direcciones= [{
                    id_direccion: 0,
                    tipo: '',
                    departamento: '',
                    ciudad: '',
                    zona: '',
                    descripcion: '',
                    referencia: '',
                    id_cliente: 0,
                    
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
                    fecha_nacimiento: moment().format('YYYY-MM-DD'),
                    ci: '',
                    ingreso_mensual:'',
                    tipo:'Codeudor',
                    actividad:'',
                    estado_civil: '0',
                    vivienda: '0',
                    sexo: '0',
                    lugar_expedicion: '0',
                    enviado: 0,
                    accion:0,
                };

            },

            async getCodeudores(page){
                await axios.get('/get_codeudores?page='+page+'&criterio='+this.criterio+'&buscar='+this.buscar).then((response)=>{
                    this.lista_clientes=response.data;
                    // this.lista_clientes=response.data.data;
                    /*this.pagination={total:response.data.total, 
                            current_page:response.data.current_page,
                            per_page: response.data.per_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                        }*/
                })
                .catch((error)=>{
                    console.log(error.message);
                })
            },

            cerrarModalNuevo() {
                $('#modalCodeudor').modal('hide');
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
                };
                this.lista_direcciones.push(nuevaDireccion);
  
            },
            agregarTelefono(item) {
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

            // guardarCodeudor(){
                
            //     var guardar_cliente=false;
            //     this.validarCodeudor();
            //     this.cliente.enviado=1;
            //     const algunValorEsFalse = Object.values(this.cliente_validaciones).some(valor => valor == false);
            //     const tieneCamposVaciosDireccion = this.lista_direcciones.some(objeto => objeto.departamento == '' || objeto.descripcion == '' || objeto.tipo == '');
            //     const tieneCamposVaciosTelefono = this.lista_telefonos.some(objeto => 
            //         (objeto.tipo === 'Informacion contacto' && (objeto.nombre === '' || objeto.nombre === null || objeto.apellidos === '' ||  objeto.apellidos === null || objeto.relacion === '' || 
            //         objeto.relacion === null || objeto.numero === '' || objeto.numero === null)) ||
            //         (objeto.tipo === 'Numero telefono' && (objeto.numero === '' || objeto.observacion === '' || objeto.observacion === null || objeto.numero === null))
            //     );

            //     // Si algunValorEsFalse es true, significa que al menos uno de los valores es false
            //     if (algunValorEsFalse || tieneCamposVaciosDireccion || tieneCamposVaciosTelefono) {
            //         console.log('no se envia');
            //         Swal.fire({
            //             position: 'center',
            //             icon: 'warning',
            //             title: 'Advertencia',
            //             text: 'Faltan completar algunos datos!',
            //             showConfirmButton: true,
            //             textConfirmButton: 'Aceptar',
            //             // timer: 1500
            //         });
                    
            //     }else{
            //         this.guardando_cliente=true;
            //         this.cliente.direcciones=this.lista_direcciones;
            //         this.cliente.telefonos=this.lista_telefonos;
            //         console.log('se envia');
            //         axios.post('/save_codeudor',this.cliente).then((response)=>{
            //             console.log(response);
            //             guardar_cliente=true;
            //         })
            //         .catch((error)=>{
            //             console.log(error.message);
            //         })
            //         .finally(()=>{
            //             if(guardar_cliente==true){
            //                 Swal.fire({
            //                     position: 'top-end',
            //                     icon: 'success',
            //                     title: 'Operación exitosa',
            //                     showConfirmButton: false,
            //                     timer: 1500
            //                 });
            //                 $('#modalCodeudor').modal('hide');
            //                 this.getCodeudores(1);

            //             }
            //             this.guardando_cliente=false;
            //         })
            //     }
            // },

            async guardarCodeudor() {
                try {
                    this.validarCodeudor();
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

                    const response = await axios.post('/save_codeudor', this.cliente);

                    if (response.data.success) {
                        await Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Operación exitosa',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#modalCodeudor').modal('hide');
                        await this.getCodeudores(1);
                    } else if (response.data.error === 'duplicate') {
                        await Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Codeudor duplicado',
                            text: response.data.message,
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar'
                        });
                    }

                } catch (error) {
                    console.error('Error al guardar codeudor:', error);

                    let errorMessage = 'Ocurrió un error al guardar el codeudor';
                    if (error.response?.status === 422 && error.response?.data?.error === 'duplicate') {
                        errorMessage = error.response.data.message;
                        await Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Codeudor duplicado',
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

            modificarCodeudor(){
                var modificar_cliente=false;
                this.validarCodeudor();
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
                }else{
                    this.cliente.direcciones=this.lista_direcciones;
                    this.cliente.telefonos=this.lista_telefonos;
                    console.log('se envia');
                    axios.post('/modify_codeudor',this.cliente).then((response)=>{
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
                                timer: 1500
                            });
                            $('#modalCodeudor').modal('hide');
                            this.getCodeudores(1);


                        }
                    })
                }
            },

            validarCodeudor(){
                
                this.cliente_validaciones.nombre=(this.cliente.nombre=='')?false:true;
                //this.cliente_validaciones.fecha_nacimiento=(this.cliente.fecha_nacimiento==moment().format('YYYY-MM-DD') || this.cliente.fecha_nacimiento=='')?false:true;
                this.cliente_validaciones.ci=(this.cliente.ci=='')?false:true;
                this.cliente_validaciones.actividad=(this.cliente.actividad=='')?false:true;
                this.cliente_validaciones.ingreso_mensual=(this.cliente.ingreso_mensual=='')?false:true;
                this.cliente_validaciones.tipo=(this.cliente.tipo=='')?false:true;
                this.cliente_validaciones.estado_civil=(this.cliente.estado_civil==''|| this.cliente.estado_civil=='0')?false:true;
                this.cliente_validaciones.vivienda=(this.cliente.vivienda==''|| this.cliente.vivienda=='0')?false:true;
                this.cliente_validaciones.sexo=(this.cliente.sexo==''|| this.cliente.sexo=='0')?false:true;
                this.cliente_validaciones.lugar_expedicion=(this.cliente.lugar_expedicion==''|| this.cliente.lugar_expedicion=='0')?false:true;

                
            },
            activarCodeudor(item){
                axios.get('/activar_codeudor?id_codeudor='+item.id).then((response)=>{
                    console.log(response);
                    
                })
                .catch(()=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    this.getCodeudores(1);
                })
            },
            desactivarCodeudor(item){
                axios.get('/desactivar_codeudor?id_codeudor='+item.id).then((response)=>{
                    console.log(response);
                    
                })
                .catch(()=>{
                    console.log(error.message);
                })
                .finally(()=>{
                    this.getCodeudores(1);
                })
            },
            async editarCodeudor(item){
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
                this.cliente.tipo=item.tipo;
                this.cliente.accion=1;
                this.preloader=true;
                await this.getDireccionTelefonoCodeudor();
                this.preloader=false;
                $('#modalCodeudor').modal('show');
            },

            async getDireccionTelefonoCodeudor(){
                await axios.get('/get_direcciones_telefono_codeudor?id_codeudor='+this.cliente.id_cliente).then((response)=>{
                    this.lista_direcciones=response.data.direcciones;
                    this.lista_telefonos=response.data.telefonos;
                })
                .catch((error)=>{
                    console.log(error.message);
                });
            },

            async verCodeudor(item){
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
                this.cliente.tipo=item.tipo;
                this.cliente.accion=2;

                this.preloader=true;
                await this.getDireccionTelefonoCodeudor();
                this.preloader=false;
                $('#modalCodeudor').modal('show');
            },

            async verInformacionCodeudor(item) {
                // // Construye la URL con el parámetro fecha_inicio
                // const url = '/codeudores_pdf?id_codeudor='+item.id;

                // // Abre una nueva pestaña o ventana con la URL
                // window.open(url, '_blank');

                // // Mostrar animación de carga
                this.preloader = true;

                try {
                    // Construye la URL con el parámetro id_cliente
                   

                    // Realizar la solicitud GET con Axios
                    const response = await axios.get('/codeudores_pdf', {
                        params: { id_codeudor: item.id },
                        responseType: 'blob' // Especificamos que esperamos un blob (para PDF)
                    });

                    // Crear un objeto URL para el blob recibido
                    const blob = new Blob([response.data], { type: 'application/pdf' });
                    const fileURL = window.URL.createObjectURL(blob);

                    // Abrir el PDF en una nueva pestaña
                    window.open(fileURL, '_blank');
                } catch (error) {
                    console.error('Error al obtener el PDF del garante/codeudor:', error);
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
                    await this.getCodeudores(1);
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
            this.preloader=true;
            console.log('Component mounted.');
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
