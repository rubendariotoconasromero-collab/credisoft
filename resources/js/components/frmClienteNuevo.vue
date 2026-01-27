<template>
    <main class="customer-management">
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content px-0 mx-0">
            <div class="container-fluid">
                <div v-if="view === 0" class="card">
                    <div class="card-header bg-warning bg-gradient py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Gestión de Clientes
                        </h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row mb-3 mt-3">
                            <div class="col-md-8 my-1">
                                <div class="input-group">
                                    <select v-model="searchCriteria" class="form-select">
                                        <option value="cliente.nombre">Nombre</option>
                                        <option value="cliente.ci">CI</option>
                                    </select>

                                    <input v-model="searchQuery" type="text" class="form-control"
                                        @input="buscarCliente()" />
                                    <button class="btn btn-success">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-warning ms-1" @click="imprimirReporte()">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 my-1 text-end">
                                <button @click="openNewCustomerModal()" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i>
                                    Nuevo
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-success text-white text-uppercase fw-bold">
                                    <tr>
                                        <th class="fw-bold text-uppercase">Nombre</th>
                                        <!-- <th class="fw-bold text-uppercase">Asesor</th> -->
                                        <th class="fw-bold text-uppercase">CI</th>
                                        <th class="fw-bold text-uppercase">Sexo</th>
                                        <th class="fw-bold text-uppercase">E. Civil</th>
                                        <th class="fw-bold text-uppercase">Actividad</th>
                                        <th class="fw-bold text-uppercase">Vivienda</th>
                                        <th class="fw-bold text-uppercase">Estado</th>
                                        <th class="fw-bold text-uppercase">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers" :key="customer.id" class="align-middle">
                                        <td class="text-uppercase fw-bold">
                                            {{ customer.nombre }}
                                        </td>

                                        <td class="text-uppercase fw-bold">
                                            {{ customer.ci }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.sexo }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.estado_civil }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.actividad }}
                                        </td>
                                        <td class="text-uppercase">
                                            {{ customer.vivienda }}
                                        </td>
                                        <td class="text-uppercase">
                                            <span :class="customer.estado === 1
                                                ? 'badge bg-success w-100 text-center'
                                                : 'badge bg-danger w-100 text-center'" style="display: inline-block;">
                                                {{ customer.estado === 1 ? "Activo" : "Inactivo" }}
                                            </span>
                                        </td>
                                        <td class="position-relative p-2 text-center">
                                            <div class="btn-group" role="group">
                                                <a style="cursor: pointer" class="text-success dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </a>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                                    data-bs-auto-close="outside" aria-labelledby="dropdownMenuLink">
                                                    <!-- Desactivar -->
                                                    <li v-if="customer.estado === 1"
                                                        @click="toggleCustomerStatus(customer)">
                                                        <a class="dropdown-item d-flex align-items-center text-danger"
                                                            href="#">
                                                            <i class="fas fa-times me-2"></i>
                                                            Desactivar
                                                        </a>
                                                    </li>

                                                    <!-- Activar -->
                                                    <li v-else @click="toggleCustomerStatus(customer)">
                                                        <a class="dropdown-item d-flex align-items-center text-success"
                                                            href="#">
                                                            <i class="fas fa-check me-2"></i>
                                                            Activar
                                                        </a>
                                                    </li>

                                                    <!-- Editar -->
                                                    <li @click="editCustomer(customer)">
                                                        <a class="dropdown-item d-flex align-items-center text-primary"
                                                            href="#">
                                                            <i class="fas fa-pencil-alt me-2"></i>
                                                            Editar
                                                        </a>
                                                    </li>

                                                    <!-- Ver -->
                                                    <li @click="viewCustomer(customer)">
                                                        <a class="dropdown-item d-flex align-items-center text-info"
                                                            href="#">
                                                            <i class="fas fa-eye me-2"></i>
                                                            Ver
                                                        </a>
                                                    </li>

                                                    <!-- PDF -->
                                                    <li @click="viewCustomerPdf(customer)">
                                                        <a class="dropdown-item d-flex align-items-center text-danger"
                                                            href="#">
                                                            <i class="fas fa-file-pdf me-2"></i>
                                                            PDF
                                                        </a>
                                                    </li>

                                                    <!-- Foto -->
                                                    <li @click="openPhotoModal(customer)">
                                                        <a class="dropdown-item d-flex align-items-center text-warning"
                                                            href="#">
                                                            <i class="fas fa-user me-2"></i>
                                                            {{
                                                            customer.imagen
                                                            ? "Ver/Cambiar Foto"
                                                            : "Agregar Foto"
                                                            }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="customers.length < 5">
                                <br /><br /><br /><br /><br /><br />
                                <br /><br /><br /><br /><br /><br />
                            </template>
                        </div>


                    </div>
                </div>

                <!-- seccion nuevo/modificar cliente -->
                <div class="card" v-if="view == 2">
                    <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 text-center">
                            <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                                {{
                                currentCustomer.accion == 0
                                ? "Agregar Cliente"
                                : (currentCustomer.accion == 1 ? "Modificar Cliente" : "Información del Cliente")
                                }}
                            </h5>
                        </div>
                        <a @click="closeModal()" type="button" class="btn-close btn-close-white"></a>
                    </div>
                    <form @submit.prevent="
                        currentCustomer.accion === 0 ? saveCustomer() : updateCustomer()
                        ">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">Nombre</label>
                                        <input v-model="currentCustomer.nombre" :disabled="currentCustomer.accion === 2"
                                            class="form-control text-capitalize" />
                                        <small v-if="!currentCustomer.nombre && currentCustomer.enviado"
                                            class="text-danger">Ingrese un
                                            nombre *</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">Fecha de Nacimiento</label>
                                        <input v-model="currentCustomer.fecha_nacimiento" type="date"
                                            :disabled="currentCustomer.accion === 2" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">CI</label>
                                        <input v-model="currentCustomer.ci" :disabled="currentCustomer.accion === 2"
                                            class="form-control" />
                                        <small v-if="!currentCustomer.ci && currentCustomer.enviado"
                                            class="text-danger">Ingrese un CI
                                            *</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">DPTO</label>
                                        <select v-model="currentCustomer.lugar_expedicion"
                                            :disabled="currentCustomer.accion === 2" class="form-select">
                                            <option value="0" disabled>Seleccione</option>
                                            <option v-for="place in expeditionPlaces" :key="place.sigla"
                                                :value="place.sigla">
                                                {{ place.sigla }}
                                            </option>
                                        </select>
                                        <small v-if="
                                            (!currentCustomer.lugar_expedicion ||
                                                currentCustomer.lugar_expedicion === '0') &&
                                            currentCustomer.enviado
                                        " class="text-danger">Seleccione *</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">Género</label>
                                        <select v-model="currentCustomer.sexo" :disabled="currentCustomer.accion === 2"
                                            class="form-select">
                                            <option value="0" disabled>Seleccione</option>
                                            <option v-for="gender in genders" :key="gender.nombre"
                                                :value="gender.nombre">
                                                {{ gender.nombre }}
                                            </option>
                                        </select>
                                        <small v-if="
                                            (!currentCustomer.sexo ||
                                                currentCustomer.sexo === '0') &&
                                            currentCustomer.enviado
                                        " class="text-danger">Seleccione *</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group my-2">
                                        <div class="row">
                                            <div class="col-md-12">

                                                <label for="buscarActividad" class="fw-bold text-dark">Seleccione una
                                                    actividad:</label>
                                                <div class="input-group mt-0 pt-0">
                                                    <textarea @keydown.enter.prevent
                                                        :disabled="currentCustomer.accion === 2"
                                                        v-model="actividadClase.buscar" id="buscarActividad"
                                                        class="form-control text-dark" placeholder="Buscar actividad..."
                                                        @input="filtrarActividades(actividadClase.buscar)"
                                                        autocomplete="off" rows="2">
                                                    </textarea>
                                                </div>

                                                <small v-if="
                                                    (!currentCustomer.actividad ||
                                                        actividadClase.buscar == '') &&
                                                    currentCustomer.enviado
                                                " class="text-danger">Ingrese una actividad *</small>
                                            </div>
                                            <div class="col-md-12" style="position:relative;">
                                                <template v-if="filteredItemsActividades.length > 0">
                                                    <div class="com-completion-results shadow" style="
                                                        z-index: 1050;
                                                        position: absolute;
                                                        top: 100%;
                                                        width: 100%;
                                                        background: #fff;
                                                        border: 1px solid #ececec;
                                                        max-height: 250px;
                                                        overflow: auto;
                                                        " v-bind:style="{
                                                            display:
                                                                filteredItemsActividades.length > 0 &&
                                                                    actividadClase.buscar != ''
                                                                    ? 'block'
                                                                    : 'none',
                                                        }">
                                                        <ul style="list-style: none; padding: 0; margin: 0">
                                                            <li v-for="(actividadItem, index) in filteredItemsActividades" :key="index"
                                                                @click="seleccionarActividad(actividadItem)" style="
                                                                cursor: pointer;
                                                                padding: 8px;
                                                                border-bottom: 1px solid #ececec;
                                                                " class="dropdown-item-hover">
                                                                <div class="container-fluid p-0">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <h6 style="
                                                                                font-size: 14px;
                                                                                color: #000;
                                                                                margin: 0;
                                                                                ">
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
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">Estado Civil</label>
                                        <select v-model="currentCustomer.estado_civil"
                                            :disabled="currentCustomer.accion === 2" class="form-select">
                                            <option value="0" disabled>Seleccione</option>
                                            <option v-for="status in maritalStatuses" :key="status.nombre"
                                                :value="status.nombre">
                                                {{ status.nombre }}
                                            </option>
                                        </select>
                                        <small v-if="
                                            (!currentCustomer.estado_civil ||
                                                currentCustomer.estado_civil === '0') &&
                                            currentCustomer.enviado
                                        " class="text-danger">Seleccione *</small>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">Vivienda</label>
                                        <select v-model="currentCustomer.vivienda"
                                            :disabled="currentCustomer.accion === 2" class="form-select">
                                            <option value="0" disabled>Seleccione</option>
                                            <option v-for="housing in housingTypes" :key="housing.nombre"
                                                :value="housing.nombre">
                                                {{ housing.nombre }}
                                            </option>
                                        </select>
                                        <small v-if="
                                            (!currentCustomer.vivienda ||
                                                currentCustomer.vivienda === '0') &&
                                            currentCustomer.enviado
                                        " class="text-danger">Seleccione *</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group my-2">
                                        <label class="fw-bold">Ingreso Mensual</label>
                                        <input v-model="currentCustomer.ingreso_mensual"
                                            :disabled="currentCustomer.accion === 2" class="form-control"
                                            @input="filterNumericInput" />
                                        <small v-if="
                                            !currentCustomer.ingreso_mensual &&
                                            currentCustomer.enviado
                                        " class="text-danger">Ingrese un ingreso *</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Addresses -->
                            <hr />
                            <p><strong class="fw-bold">Direcciones de Contacto</strong></p>
                            <div v-for="(address, index) in addresses" :key="index"
                                class="card border border-dark border-2 mb-3">
                                <div
                                    class="card-header bg-light d-flex justify-content-between align-items-center py-0 pe-0">
                                    <label class="text-dark fw-bold">Dirección {{ index + 1 }}</label>

                                    <div>
                                        <a href="#"
                                            v-if="currentCustomer.accion !== 2 && (!address.lat || !address.lng)"
                                            @click="openMapModal(index, 'add')" class="btn btn-info btn-sm me-2">
                                            <i class="fas fa-map-marker-alt"></i> Agregar Ubicación
                                        </a>
                                        <a href="#" v-if="address.lat && address.lng"
                                            @click="openMapModal(index, 'view')" class="btn btn-info btn-sm me-2">
                                            <i class="fas fa-eye"></i> Ver Ubicación
                                        </a>
                                        <a href="#" v-if="currentCustomer.accion !== 2 && address.lat && address.lng"
                                            @click="openMapModal(index, 'update')" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Actualizar Ubicación
                                        </a>

                                        <button v-if="currentCustomer.accion !== 2" type="button"
                                            class="btn btn-success btn-sm ms-5" @click="addAddress">
                                            <i class="fas fa-plus"></i>
                                        </button>

                                        <button v-if="currentCustomer.accion !== 2 && addresses.length > 1"
                                            type="button" class="btn btn-danger btn-sm ms-2"
                                            @click="removeAddress(index)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body grid">
                                    <div>
                                        <label class="fw-bold">Tipo</label>
                                        <select v-model="address.tipo" :disabled="currentCustomer.accion === 2"
                                            class="form-select">
                                            <option value="" disabled>Seleccione</option>
                                            <option v-for="type in addressTypes" :key="type" :value="type">
                                                {{ type }}
                                            </option>
                                        </select>
                                        <small v-if="!address.tipo && currentCustomer.enviado"
                                            class="text-danger">Requerido *</small>
                                    </div>

                                    <div>
                                        <label class="fw-bold">Departamento</label>
                                        <select v-model="address.departamento" :disabled="currentCustomer.accion === 2"
                                            class="form-control">
                                            <option value="">Seleccione un departamento</option>
                                            <option v-for="depto in departamentosBolivia" :key="depto.id"
                                                :value="depto.nombre">
                                                {{ depto.nombre }}
                                            </option>
                                        </select>
                                        <small v-if="!address.departamento && currentCustomer.enviado"
                                            class="text-danger">Requerido
                                            *</small>
                                    </div>
                                    <div>
                                        <label class="fw-bold">Ciudad</label>
                                        <input v-model="address.ciudad" :disabled="currentCustomer.accion === 2"
                                            class="form-control text-capitalize" />
                                    </div>
                                    <div>
                                        <label class="fw-bold">Zona</label>
                                        <input v-model="address.zona" :disabled="currentCustomer.accion === 2"
                                            class="form-control" />
                                    </div>
                                    <div>
                                        <label class="fw-bold">Descripción</label>
                                        <input v-model="address.descripcion" :disabled="currentCustomer.accion === 2"
                                            class="form-control" />
                                        <small v-if="!address.descripcion && currentCustomer.enviado"
                                            class="text-danger">Requerido
                                            *</small>
                                    </div>




                                </div>
                            </div>

                            <!-- Phones -->
                            <hr />
                            <p><strong class="fw-bold">Números de Contacto</strong></p>
                            <div v-for="(phone, index) in phones" :key="index"
                                class="card border border-dark border-2 mb-3">
                                <div
                                    class="card-header bg-light d-flex justify-content-between align-items-center py-0 pe-0">
                                    <label class="text-dark fw-bold">Teléfono {{ index + 1 }}</label>
                                    <div v-if="currentCustomer.accion !== 2">
                                        <button type="button" class="btn btn-success btn-sm" @click="addPhone">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button v-if="phones.length > 1" type="button"
                                            class="btn btn-danger btn-sm ms-2" @click="removePhone(index)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body grid">
                                    <div>
                                        <label class="fw-bold">Tipo</label>
                                        <select v-model="phone.tipo" :disabled="currentCustomer.accion === 2"
                                            class="form-select">
                                            <option value="Numero telefono">Nr. Telf.</option>
                                            <option value="Informacion contacto">
                                                Inf. Contacto
                                            </option>
                                        </select>
                                        <small v-if="!phone.tipo && currentCustomer.enviado"
                                            class="text-danger">Requerido *</small>
                                    </div>
                                    <div>
                                        <label class="fw-bold">Número</label>
                                        <input v-model="phone.numero" :disabled="currentCustomer.accion === 2"
                                            class="form-control" />
                                        <small v-if="!phone.numero && currentCustomer.enviado"
                                            class="text-danger">Requerido *</small>
                                    </div>
                                    <div v-if="phone.tipo === 'Numero telefono'">
                                        <label class="fw-bold">Observación</label>
                                        <input v-model="phone.observacion" :disabled="currentCustomer.accion === 2"
                                            class="form-control" />
                                        <small v-if="
                                            !phone.observacion &&
                                            phone.tipo === 'Numero telefono' &&
                                            currentCustomer.enviado
                                        " class="text-danger">Requerido *</small>
                                    </div>
                                    <template v-if="phone.tipo === 'Informacion contacto'">
                                        <div>
                                            <label class="fw-bold">Nombre</label>
                                            <input v-model="phone.nombre" :disabled="currentCustomer.accion === 2"
                                                class="form-control text-capitalize" />
                                            <small v-if="!phone.nombre && currentCustomer.enviado"
                                                class="text-danger">Requerido *</small>
                                        </div>
                                        <div>
                                            <label class="fw-bold">Apellidos</label>
                                            <input v-model="phone.apellidos" :disabled="currentCustomer.accion === 2"
                                                class="form-control text-capitalize" />
                                            <small v-if="!phone.apellidos && currentCustomer.enviado"
                                                class="text-danger">Requerido *</small>
                                        </div>
                                        <div>
                                            <label class="fw-bold">Relación</label>
                                            <input v-model="phone.relacion" :disabled="currentCustomer.accion === 2"
                                                class="form-control" />
                                            <small v-if="!phone.relacion && currentCustomer.enviado"
                                                class="text-danger">Requerido *</small>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" @click="closeModal">
                                <i class="fas fa-times-circle"></i> Cerrar
                            </button>

                            <button v-if="currentCustomer.accion === 0" :disabled="savingCustomer" type="submit"
                                class="btn btn-success ms-2" :class="{ 'btn-loading': savingCustomer }">
                                <span v-if="!savingCustomer">
                                    <i class="fas fa-save"></i> Guardar
                                </span>
                                <span v-else>
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>

                            <!-- Botón Modificar -->
                            <button v-if="currentCustomer.accion === 1" :disabled="modifyCustomer" type="submit"
                                class="btn btn-success ms-2" :class="{ 'btn-loading': modifyCustomer }">
                                <span v-if="!modifyCustomer">
                                    <i class="fas fa-edit"></i> Modificar
                                </span>
                                <span v-else>
                                    <i class="fas fa-spinner fa-spin"></i> Modificando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Photo Modal -->
                <div id="modalFoto" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-dark border-2">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title text-dark">
                                    <i class="fas fa-camera me-2"></i>Foto del Cliente
                                </h5>
                                <button type="button" class="btn-close btn-close-dark" @click="closePhotoModal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="image-preview-container mb-4">
                                    <img :src="previewImage || (currentCustomer.imagen
                                        ? `/img/cliente/${currentCustomer.imagen}`
                                        : '/img/cliente/default.png')" class="img-thumbnail customer-photo"
                                        alt="Foto del cliente" />
                                </div>

                                <div class="file-upload-wrapper">
                                    <label for="customerPhotoUpload" class="btn btn-outline-success w-100">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Seleccionar nueva foto
                                    </label>
                                    <input id="customerPhotoUpload" type="file" class="d-none" accept="image/*"
                                        @change="previewPhoto" />
                                </div>

                                <div class="mt-3 text-muted small">
                                    Formatos soportados: JPG, PNG. Tamaño máximo: 2MB
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" @click="closePhotoModal">
                                    <i class="fas fa-times-circle me-1"></i> Cerrar
                                </button>
                                <button class="btn btn-success" @click="savePhoto" :disabled="!selectedFile">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Google Maps Modal -->
        <div id="modalMapa" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-dark border-2">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark" id="mapaModalLabel">{{ mapModalTitle }}</h5>
                        <button @click="closeMapModal()" type="button" class="btn-close btn-close-dark"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- <input v-if="mapModalMode !== 'view'" type="text" v-model="searchQuery" id="searchMap" class="form-control mb-3" placeholder="Buscar ubicación..."> -->
                                <div id="map" style="height: 400px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeMapModal()" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cerrar
                        </button>
                        <button v-if="mapModalMode !== 'view'" @click="saveLocation()" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Ubicación
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </main>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";

export default {
    data() {
        return {
            departamentosBolivia: [
                { id: 1, nombre: "La Paz" },
                { id: 2, nombre: "Cochabamba" },
                { id: 3, nombre: "Santa Cruz" },
                { id: 4, nombre: "Oruro" },
                { id: 5, nombre: "Potosí" },
                { id: 6, nombre: "Tarija" },
                { id: 7, nombre: "Chuquisaca" },
                { id: 8, nombre: "Beni" },
                { id: 9, nombre: "Pando" },
            ],
            preloader: false,
            customers: [],
            advisors: [],
            advisorCustomerStats: [],
            customersWithCredits: 0,
            customersWithoutCredits: 0,
            advisorOption: 0,
            searchCriteria: "cliente.nombre",
            searchQuery: "",
            view: 0,
            currentCustomer: this.getDefaultCustomer(),
            selectedFile: null,
            previewImage: null,
            addresses: [],
            phones: [],
            customerCredits: [],
            planInstallments: [],
            savingCustomer: false,
            modifyCustomer: false,
            loading: false,
            errorMessage: "",
            addressTypes: [
                "Casa",
                "Oficina",
                "Negocio",
                "Apartamento",
                "Edificio",
                "Otra Residencia",
                "Residencia Temporal",
                "Domicilio Familiar",
                "Domicilio Conyugal",
                "Propiedad Alquilada",
                "Propiedad Propia",
                "Residencia Estudiantil",
                "Habitación Compartida",
                "Residencia Permanente",
                "Piso",
                "Suite",
                "Condominio",
                "Terreno",
                "Local Comercial",
                "Finca",
                "Hacienda",
                "Bodega",
                "Depósito",
                "Establecimiento Industrial",
                "Zona Rural",
                "Zona Urbana",
                "Barrio",
                "Colonia",
                "Sector",
                "Municipio",
            ],
            expeditionPlaces: [
                {
                    sigla: "LP",
                    nombre: "La Paz",
                },
                {
                    sigla: "CB",
                    nombre: "Cochabamba",
                },
                {
                    sigla: "SC",
                    nombre: "Santa Cruz",
                },
                {
                    sigla: "OR",
                    nombre: "Oruro",
                },
                {
                    sigla: "PT",
                    nombre: "Potosí",
                },
                {
                    sigla: "TJ",
                    nombre: "Tarija",
                },
                {
                    sigla: "BN",
                    nombre: "Beni",
                },
                {
                    sigla: "PD",
                    nombre: "Pando",
                },
                {
                    sigla: "CH",
                    nombre: "Chuquisaca",
                },
                {
                    sigla: "Sin Expedición",
                    nombre: "Sin Expedición",
                },
            ],
            genders: [
                {
                    nombre: "Masculino",
                },
                {
                    nombre: "Femenino",
                },
            ],
            maritalStatuses: [
                {
                    nombre: "soltero/a",
                },
                {
                    nombre: "Casado/a",
                },
                {
                    nombre: "divorciado/a",
                },
                {
                    nombre: "viudo/a",
                },
                {
                    nombre: "separado/a",
                },
                {
                    nombre: "conviviente/a",
                },
            ],
            housingTypes: [
                {
                    nombre: "Casa propia",
                },
                {
                    nombre: "Alquiler",
                },
                {
                    nombre: "Vivienda familiar",
                },
            ],

            actividades: [],
            filteredItemsActividades: [],
            actividadClase: {
                id_actividad: 0,
                buscar: "",
            },
            debounceTimeout: null,
            map: null,
            marker: null,
            mapSearchQuery: '',
            mapModalMode: 'add', // 'add', 'view', 'update'
            mapModalTitle: '',
            currentAddressIndex: null,
        };
    },
    computed: {
        formatDate() {
            return (date) => moment(date).format("DD/MM/YYYY");
        },
    },
    methods: {
        imprimirReporte() {
            Swal.fire({
                title: 'Generando Reporte',
                text: 'Por favor espere, estamos preparando el PDF...',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let url = '/cliente/reporte';
            axios.get(url, {
                params: {
                    buscar: this.buscar,
                    criterio: this.criterio,
                    opcion_asesor: this.opcion_asesor
                },
                responseType: 'blob'
            })
            .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'reporte_clientes.pdf');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                Swal.close();
            })
            .catch((error) => {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo generar el reporte.'
                });
            });
        },
        initializeMap(lat = -17.7833, lng = -63.1821) { 
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
                const input = document.getElementById('searchMap');
                const autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', this.map);

                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (!place.geometry) return;

                    this.map.setCenter(place.geometry.location);
                    this.map.setZoom(15);
                    this.marker.setPosition(place.geometry.location);
                    this.mapSearchQuery = place.formatted_address;
                    this.updateAddressCoordinates(place.geometry.location.lat(), place.geometry.location.lng());
                });

                this.marker.addListener('dragend', () => {
                    const position = this.marker.getPosition();
                    this.updateAddressCoordinates(position.lat(), position.lng());
                });

                this.map.addListener('click', (event) => {
                    this.marker.setPosition(event.latLng);
                    this.updateAddressCoordinates(event.latLng.lat(), event.latLng.lng());
                });
            }
        },

        updateAddressCoordinates(lat, lng) {
            if (this.currentAddressIndex !== null) {
                this.addresses[this.currentAddressIndex].lat = lat;
                this.addresses[this.currentAddressIndex].lng = lng;
            }
        },

        openMapModal(index, mode) {
            this.currentAddressIndex = index;
            this.mapModalMode = mode;
            this.mapSearchQuery = '';

            if (mode === 'add') {
                this.mapModalTitle = 'Agregar Ubicación';
            } else if (mode === 'view') {
                this.mapModalTitle = 'Ver Ubicación';
            } else if (mode === 'update') {
                this.mapModalTitle = 'Actualizar Ubicación';
            }

            $('#modalMapa').modal('show');

            this.$nextTick(() => {
                const address = this.addresses[index];
                if (mode === 'view' && address.lat && address.lng) {
                    // For view mode, use saved coordinates
                    this.initializeMap(parseFloat(address.lat), parseFloat(address.lng));
                } else if (mode === 'add' || mode === 'update') {
                    // For add or update mode, try to get current location
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                this.initializeMap(lat, lng);
                            },
                            (error) => {
                                console.warn('Geolocation error:', error.message);
                                // Fallback to default coordinates or saved coordinates for update
                                const lat = mode === 'update' && address.lat ? parseFloat(address.lat) : -17.7833;
                                const lng = mode === 'update' && address.lng ? parseFloat(address.lng) : -63.1821;
                                this.initializeMap(lat, lng);
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'info',
                                    title: 'Ubicación no disponible',
                                    text: 'No se pudo obtener la ubicación actual. Mostrando ubicación predeterminada.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                });
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 5000,
                                maximumAge: 0,
                            }
                        );
                    } else {
                        console.warn('Geolocation not supported by this browser.');
                        // Fallback to default coordinates or saved coordinates for update
                        const lat = mode === 'update' && address.lat ? parseFloat(address.lat) : -17.7833;
                        const lng = mode === 'update' && address.lng ? parseFloat(address.lng) : -63.1821;
                        this.initializeMap(lat, lng);
                        Swal.fire({
                            position: 'top-end',
                            icon: 'info',
                            title: 'Geolocation no soportada',
                            text: 'El navegador no soporta geolocalización. Mostrando ubicación predeterminada.',
                            showConfirmButton: false,
                            timer: 2000,
                        });
                    }
                }
            });
        },

        closeMapModal() {
            $('#modalMapa').modal('hide');
            this.map = null;
            this.marker = null;
            this.currentAddressIndex = null;
            this.mapSearchQuery = '';
        },

        saveLocation() {
            if (!this.addresses[this.currentAddressIndex].lat || !this.addresses[this.currentAddressIndex].lng) {
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

            this.closeMapModal();
        },
        previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: "error",
                        title: "Archivo demasiado grande",
                        text: "La imagen debe ser menor a 2MB",
                        timer: 2000
                    });
                    event.target.value = '';
                    return;
                }
                if (!file.type.match('image.*')) {
                    Swal.fire({
                        icon: "error",
                        title: "Formato no soportado",
                        text: "Por favor selecciona una imagen JPG o PNG",
                        timer: 2000
                    });
                    event.target.value = '';
                    return;
                }
                this.selectedFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewImage = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        async savePhoto() {
            if (!this.selectedFile) return;

            const formData = new FormData();
            formData.append("id_cliente", this.currentCustomer.id);
            formData.append("imagen", this.selectedFile);

            try {
                const response = await axios.post("/fotoCliente", formData);
                this.currentCustomer.imagen = response.data.imagen;
                Swal.fire({
                    icon: "success",
                    title: "Foto actualizada",
                    timer: 1500
                });
                this.previewImage = null;
                this.selectedFile = null;
                document.getElementById('customerPhotoUpload').value = '';
                await this.fetchCustomers(1);
                this.closePhotoModal();
            } catch (error) {
                console.error("Error updating photo:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo actualizar la foto",
                    timer: 2000
                });
            }
        },
        buscarCliente() {
            if (this.debounceTimeout) {
                clearTimeout(this.debounceTimeout);
            }
            this.debounceTimeout = setTimeout(() => {
                this.fetchCustomers(1);
            }, 300);
        },
        async seleccionarActividad(item) {
            this.actividadClase.id_actividad = item.id;
            this.actividadClase.buscar = item.nombre;
            this.currentCustomer.actividad = this.actividadClase.buscar;
            this.filteredItemsActividades = [];
        },
        filtrarActividades(keyword) {
            if (keyword === "") {
                this.filteredItemsActividades = [];
                return;
            }
            this.filteredItemsActividades = this.actividades.filter((actividad) =>
                actividad.nombre.toLowerCase().includes(keyword.toLowerCase())
            );
        },
        async getActividades() {
            try {
                const response = await axios.get("/get_actividades");
                this.actividades = response.data;
            } catch (error) {
                console.log(error.message);
            }
        },
        getDefaultCustomer() {
            return {
                id_cliente: 0,
                nombre: "",
                fecha_nacimiento: moment().subtract(18, "years").format("YYYY-MM-DD"),
                ci: "",
                ingreso_mensual: "",
                actividad: "",
                estado_civil: "0",
                vivienda: "0",
                sexo: "0",
                lugar_expedicion: "0",
                enviado: 0,
                accion: 0,
                imagen: "",
            };
        },
        async fetchCustomers(page) {
            try {
                const response = await axios.get("/get_clientes", {
                    params: {
                        page,
                        criterio: this.searchCriteria,
                        buscar: this.searchQuery,
                        opcion_asesor: this.advisorOption,
                    },
                });
                this.customers = response.data;
            } catch (error) {
                console.error("Error fetching customers:", error);
            } finally {
            }
        },
        async fetchAdvisors() {
            try {
                const response = await axios.get("/get_asesores");
                this.advisors = response.data;
            } catch (error) {
                console.error("Error fetching advisors:", error);
            }
        },
        async fetchCustomerStats() {
            try {
                const response = await axios.get("/cantidades_clientes");
                this.customersWithCredits = response.data.clientes_con_creditos;
                this.customersWithoutCredits = response.data.clientes_sin_creditos;
            } catch (error) {
                console.error("Error fetching stats:", error);
            }
        },
        async fetchAdvisorCustomerStats() {
            try {
                const response = await axios.get("/get_cantidad_clientes");
                this.advisorCustomerStats = response.data;
            } catch (error) {
                console.error("Error fetching advisor stats:", error);
            }
        },
        async initializeData() {
            await Promise.all([
                this.fetchCustomers(1),
                this.fetchAdvisors(),
                this.fetchAdvisorCustomerStats(),
                this.fetchCustomerStats(),
                this.getActividades(),
            ]);
        },
        openNewCustomerModal() {
            this.actividadClase = {
                id_actividad: 0,
                buscar: "",
            };
            this.currentCustomer = this.getDefaultCustomer();
            this.addresses = [
                {
                    tipo: "",
                    departamento: "",
                    ciudad: "",
                    zona: "",
                    descripcion: "",
                },
            ];
            this.phones = [
                {
                    tipo: "Numero telefono",
                    numero: "",
                    observacion: "",
                },
            ];
    
            this.view = 2;
        },
        async editCustomer(customer) {
            this.currentCustomer = {
                ...customer,
                accion: 1,
                enviado: 0,
            };
            this.actividadClase.buscar = this.currentCustomer.actividad;
            await this.fetchCustomerDetails(customer.id);
            this.view = 2;
        },
        async viewCustomer(customer) {
            this.currentCustomer = {
                ...customer,
                accion: 2,
            };
            this.actividadClase.buscar = this.currentCustomer.actividad;
            await this.fetchCustomerDetails(customer.id);
            this.view = 2;
        },

        async fetchCustomerDetails(id) {
            this.preloader = true;
            try {
                const response = await axios.get(`/get_direcciones_telefono?id_cliente=${id}`);
                this.addresses = response.data.direcciones.map(d => ({
                    ...d,
                    lat: d.lat ? parseFloat(d.lat) : null,
                    lng: d.lng ? parseFloat(d.lng) : null,
                }));
                this.phones = response.data.telefonos;
            } catch (error) {
                console.error("Error fetching details:", error);
            } finally {
                this.preloader = false;
            }
        },
        async toggleCustomerStatus(customer) {
            try {
                const endpoint =
                    customer.estado === 1 ? "/desactivar_cliente" : "/activar_cliente";
                await axios.get(`${endpoint}?id_cliente=${customer.id}`);
                await this.fetchCustomers(1);
            } catch (error) {
                console.error("Error toggling status:", error);
            }
        },
        async viewCreditHistory(customer) {
            this.currentCustomer = {
                ...customer,
            };
            this.view = 1;
            await Promise.all([
                this.fetchCustomerCredits(customer.id),
                this.fetchPlanInstallments(customer.id),
            ]);
        },
        async fetchCustomerCredits(id) {
            try {
                const response = await axios.get(
                    `/get_creditos_cliente?id_cliente=${id}`
                );
                this.customerCredits = response.data;
            } catch (error) {
                console.error("Error fetching credits:", error);
            }
        },
        async fetchPlanInstallments(id) {
            this.loading = true;
            try {
                const response = await axios.get("/get_cuotas_planes", {
                    params: {
                        id_cliente: id,
                    },
                });
                this.planInstallments = response.data;
            } catch (error) {
                console.error("Error fetching installments:", error);
            } finally {
                this.loading = false;
            }
        },
        getInstallmentClass(installment) {
            if (installment.dias_pasados > 0 && installment.estado === 1)
                return "bg-danger text-white";
            if (installment.estado === 2) return "bg-success text-white";
            if (installment.estado === 3) return "bg-secondary text-white";
            if (!installment.numero) return "bg-warning text-white";
            return "";
        },
        getInstallmentStatusClass(status) {
            return status === 1
                ? "badge text-bg-primary"
                : status === 0
                    ? "badge text-bg-danger"
                    : "badge text-bg-success";
        },
        getInstallmentStatusText(status) {
            return status === 1
                ? "Por pagar"
                : status === 0
                    ? "Anulado"
                    : "Cancelado";
        },
        async viewCustomerPdf(customer) {
            this.preloader = true;
            try {
                const response = await axios.get("/clientes_pdf", {
                    params: {
                        id_cliente: customer.id,
                    },
                    responseType: "blob",
                });
                const blob = new Blob([response.data], {
                    type: "application/pdf",
                });
                window.open(window.URL.createObjectURL(blob), "_blank");
            } catch (error) {
                console.error("Error fetching PDF:", error);
                Swal.fire("Error", "No se pudo generar el PDF", "error");
            } finally {
                this.preloader = false;
            }
        },
        openPhotoModal(customer) {
            this.currentCustomer = {
                ...customer,
            };
            $("#modalFoto").modal("show");
        },
        closePhotoModal() {
            this.previewImage = null;
            this.selectedFile = null;
            document.getElementById('customerPhotoUpload').value = '';
            $('#modalFoto').modal('hide');
        },
        async updatePhoto(event) {
            const formData = new FormData();
            formData.append("id_cliente", this.currentCustomer.id_cliente);
            formData.append("imagen", event.target.files[0]);
            try {
                const response = await axios.post("/fotoCliente", formData);
                this.currentCustomer.imagen = response.data.imagen;
                Swal.fire({
                    icon: "success",
                    title: "Foto actualizada",
                    timer: 1500,
                });
                await this.fetchCustomers(1);
            } catch (error) {
                console.error("Error updating photo:", error);
            }
        },
        closeModal() {
            this.view = 0;
        },
        async saveCustomer() {
            this.currentCustomer.enviado = 1;
            if (!this.validateCustomer()) return;
            this.savingCustomer = true;
            try {
                const response = await axios.post("/save_cliente", {
                    ...this.currentCustomer,
                    direcciones: this.addresses,
                    telefonos: this.phones,
                });
                if (response.data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Cliente guardado",
                        timer: 1000,
                    });
                    this.closeModal();
                    await this.initializeData();
                } else if (response.data.error === "duplicate") {
                    Swal.fire("Cliente duplicado", response.data.message, "error");
                }
            } catch (error) {
                console.error("Error al guardar cliente:", error);

                let errorMessage = "Ocurrió un error al guardar el cliente";
                if (
                    error.response?.status === 422 &&
                    error.response?.data?.error === "duplicate"
                ) {
                    errorMessage = error.response.data.message;
                    await Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Cliente duplicado",
                        text: errorMessage,
                        showConfirmButton: true,
                        confirmButtonText: "Aceptar",
                    });
                } else {
                    await Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error",
                        text: errorMessage,
                        showConfirmButton: true,
                        confirmButtonText: "Aceptar",
                    });
                }
            } finally {
                this.savingCustomer = false;
            }
        },
        async updateCustomer() {
            if (!this.validateCustomer()) return;
            this.currentCustomer.enviado = 1;
            try {
                this.modifyCustomer = true;
                await axios.post("/modify_cliente", {
                    ...this.currentCustomer,
                    direcciones: this.addresses,
                    telefonos: this.phones,
                });
                Swal.fire({
                    icon: "success",
                    title: "Cliente actualizado",
                    timer: 1000,
                });
                this.closeModal();
                await this.fetchCustomers(1);
            } catch (error) {
                console.error("Error updating customer:", error);
            } finally {
                this.modifyCustomer = false;
            }
        },
        validateCustomer() {
            const customerValid =
                this.currentCustomer.nombre &&
                this.currentCustomer.ci &&
                this.currentCustomer.ingreso_mensual &&
                this.currentCustomer.actividad &&
                this.currentCustomer.estado_civil !== "0" &&
                this.currentCustomer.vivienda !== "0" &&
                this.currentCustomer.sexo !== "0" &&
                this.currentCustomer.lugar_expedicion !== "0";
            const addressValid = this.addresses.every(
                (a) => a.tipo && a.departamento && a.descripcion
            );
            const phoneValid = this.phones.every(
                (p) =>
                    p.tipo &&
                    p.numero &&
                    (p.tipo === "Numero telefono"
                        ? p.observacion
                        : p.nombre && p.apellidos && p.relacion)
            );
            const isValid = customerValid && addressValid && phoneValid;
            if (!isValid)
                Swal.fire("Advertencia", "Faltan datos por completar", "warning");
            return isValid;
        },
        addAddress() {
            this.addresses.push({
                tipo: "",
                departamento: "",
                ciudad: "",
                zona: "",
                descripcion: "",
                lat: null,
                lng: null,
            });
        },
        removeAddress(index) {
            if (this.addresses.length > 1) this.addresses.splice(index, 1);
        },
        addPhone() {
            this.phones.push({
                tipo: "Numero telefono",
                numero: "",
                observacion: "",
            });
        },
        removePhone(index) {
            if (this.phones.length > 1) this.phones.splice(index, 1);
        },
        filterNumericInput(event) {
            event.target.value = event.target.value.replace(/[^0-9.]*/g, "");
            if (event.target.value.includes(".") && event.key === ".")
                event.preventDefault();
        },
        showToast(message) {
            this.errorMessage = message;
            new bootstrap.Toast(this.$refs.toast).show();
        },
        closeToast() {
            new bootstrap.Toast(this.$refs.toast).hide();
        },
    },
    async mounted() {
        this.preloader = true;
        await this.initializeData();
        this.preloader = false;

    },
};
</script>

<style scoped>
.dropdown-toggle::after {
  display: none !important;
}
.dropdown-item-hover:hover {
    background-color: #1a1a1a; /* Color de fondo al hacer hover */
}
.dropdown-item-hover:hover h6 {
    color: #fff !important; /* Color de texto blanco al hacer hover */
}
.table-hover tbody tr:hover,
.table-hover tbody tr:focus-within {
    background-color: transparent !important;
}

.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.dropdown-item:hover {
    background-color: #f1f1f1;
    color: inherit;
}

.customer-management {
    background-color: #f5f7fb;
}

.filters {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 20px 0;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.stat-item {
    background-color: #fff;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1rem;
    font-weight: bold;
}


.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.grid-totals {
    display: grid;
    background-color: #fff;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    border-radius: 8px;
    box-shadow: 2px 2px 2px 2px rgba(0, 0, 0, 0.1);
}

.credit-details {
    margin-bottom: 20px;
}

.installments h3 {
    margin: 20px 0 10px;
}

.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
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
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.image-container {
    width: 100%;
    margin-bottom: 1rem;
}

.image-container img {
    width: 100%;
    height: auto;
    max-width: 100%;
    border-radius: 8px;
}
</style>


<style scoped>
.customer-photo {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #198754;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.customer-photo:hover {
    transform: scale(1.05);
}

.image-preview-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 220px;
}

.file-upload-wrapper {
    position: relative;
    overflow: hidden;
}

.btn-outline-success:hover {
    background-color: #198754;
    color: white;
}

@media (max-width: 576px) {
    .customer-photo {
        width: 150px;
        height: 150px;
    }
}
</style>