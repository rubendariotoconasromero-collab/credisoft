<template>
    <main class="guarantor-management">
        <!-- Preloader -->
        <div v-if="preloader" class="preloader">
            <div class="spinner"></div>
        </div>

        <div class="page-content">
            <div class="container-fluid">

                <div v-if="view == 0" class="card mt-0">
                    <div class="card-header bg-warning py-2">
                        <h5 class="header-title my-0 text-center fw-bold text-dark text-uppercase">
                            Gestión de Codeudores
                        </h5>
                    </div>
                    <div class="card-body mt-0 px-4 pt-0">
                        <div class="my-3">
                            <div class="row">
                                <div class="col-md-8 my-1">
                                    <div class="input-group">
                                        <select v-model="searchCriteria" class="form-select">
                                            <option value="codeudor.nombre">Nombre</option>
                                            <option value="codeudor.ci">CI</option>
                                        </select>
                                        <input v-model="searchQuery" type="text" class="form-control"
                                            @input="buscarCodeudor()" />
                                        <button class="btn btn-success">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-warning ms-1" @click="imprimirReporteCodeudores()">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 my-1 text-end">
                                    <button @click="openNewGuarantorModal()" class="btn btn-success">
                                        <i class="fas fa-plus-circle"></i>
                                        Nuevo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- <h6 class="mt-3">Listado de Codeudores/Garantes</h6> -->
                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-success text-dark text-uppercase">
                                    <tr class="table-success text-dark text-uppercase">
                                        <th class="fw-bold text-uppercase">Nombre</th>
                                        <th class="fw-bold text-uppercase">CI</th>
                                        <th class="fw-bold text-uppercase">Sexo</th>
                                        <th class="fw-bold text-uppercase">E.Civil</th>
                                        <th class="fw-bold text-uppercase">Actividad</th>
                                        <th class="fw-bold text-uppercase">Vivienda</th>
                                        <th class="fw-bold text-uppercase">Tipo</th>
                                        <th class="fw-bold text-uppercase">Estado</th>
                                        <th class="fw-bold text-uppercase">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in guarantors" :key="item.id" class="align-middle">
                                        <td class="text-uppercase fw-bold">
                                            {{ item.nombre }}
                                        </td>
                                        <td class="text-uppercase">{{ item.ci }}</td>
                                        <td class="text-uppercase">{{ item.sexo }}</td>
                                        <td class="text-uppercase">
                                            {{ item.estado_civil }}
                                        </td>
                                        <td class="text-uppercase">{{ item.actividad }}</td>
                                        <td class="text-uppercase">{{ item.vivienda }}</td>
                                        <td class="text-uppercase">{{ item.tipo }}</td>
                                        <td class="text-uppercase">
                                            <span :class="item.estado === 1
                                                ? 'badge bg-success w-100 text-center'
                                                : 'badge bg-danger w-100 text-center'" style="display: inline-block;">
                                                {{ item.estado === 1 ? "Activo" : "Inactivo" }}
                                            </span>
                                        </td>
                                        <td class="text-uppercase position-relative text-center">
                                            <div class="dropdown">
                                          
                                                <a class="text-success dropdown-toggle" style="cursor: pointer"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <li v-if="item.estado === 1" @click="deactivateGuarantor(item)">
                                                        <a class="dropdown-item text-danger" href="#"><i
                                                                class="fas fa-times"></i> Desactivar</a>
                                                    </li>
                                                    <li v-else @click="activateGuarantor(item)">
                                                        <a class="dropdown-item text-success" href="#"><i
                                                                class="fas fa-check"></i> Activar</a>
                                                    </li>
                                                    <li @click="editGuarantor(item)">
                                                        <a class="dropdown-item text-primary" href="#"><i
                                                                class="fas fa-pencil-alt"></i> Editar</a>
                                                    </li>
                                                    <li @click="viewGuarantor(item)">
                                                        <a class="dropdown-item text-info" href="#"><i
                                                                class="fas fa-eye"></i> Ver</a>
                                                    </li>
                                                    <li @click="viewGuarantorPdf(item)">
                                                        <a class="dropdown-item text-danger" href="#"><i
                                                                class="fas fa-file-pdf"></i> PDF</a>
                                                    </li>
                                                    <li @click="openPhotoModal(item)">
                                                        <a class="dropdown-item text-warning" href="#"><i
                                                                class="fas fa-user"></i>
                                                            {{
                                                                item.imagen
                                                                    ? "Ver/Cambiar Foto"
                                                                    : "Agregar Foto"
                                                            }}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="guarantors.length<=7">
                                <br><br><br><br><br><br><br><br><br><br><br><br><br>
                            </template>
                        </div>

                    </div>
                </div>


                <div v-if="view == 1" class="card">
                    <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 text-center">
                            <h5 class="header-title my-0 fw-bold text-dark fw-bold text-uppercase">
                                {{
                                    currentGuarantor.action === 0
                                        ? "Agregar Nuevo Garante/Codeudor"
                                        : currentGuarantor.action === 1
                                            ? "Modificar Garante/Codeudor"
                                            : `Información del Garante/Codeudor: ${currentGuarantor.nombre} - ${currentGuarantor.ci}
                                ${currentGuarantor.lugar_expedicion}`
                                }}
                            </h5>
                        </div>
                        <button @click="closeModal()" type="button" class="btn-close btn-close-white"
                            aria-label="Close"></button>
                    </div>

                    <form @submit.prevent="currentGuarantor.action === 0 ? saveGuarantor() : updateGuarantor()">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Primera fila: Nombre, Fecha de Nacimiento, CI -->
                                <div class="col-md-4">
                                    <label class="fw-bold">Nombre</label>
                                    <input v-model="currentGuarantor.nombre" :disabled="currentGuarantor.action === 2"
                                        class="form-control text-capitalize" />
                                    <small v-if="
                                        !currentGuarantor.nombre && currentGuarantor.enviado
                                    " class="text-danger">Ingrese un nombre *</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Fecha de Nacimiento</label>
                                    <input v-model="currentGuarantor.fecha_nacimiento" type="date"
                                        :disabled="currentGuarantor.action === 2" class="form-control" />
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">CI</label>
                                    <input v-model="currentGuarantor.ci" :disabled="currentGuarantor.action === 2"
                                        class="form-control" />
                                    <small v-if="!currentGuarantor.ci && currentGuarantor.enviado"
                                        class="text-danger">Ingrese un CI *</small>
                                </div>

                                <!-- Segunda fila: DPTO, Género, Estado Civil -->
                                <div class="col-md-2">
                                    <label class="fw-bold">DPTO</label>
                                    <select v-model="currentGuarantor.lugar_expedicion"
                                        :disabled="currentGuarantor.action === 2" class="form-select">
                                        <option value="0" disabled>Seleccione</option>
                                        <option v-for="place in expeditionPlaces" :key="place.sigla"
                                            :value="place.sigla">
                                            {{ place.sigla }}
                                        </option>
                                    </select>
                                    <small v-if="
                                        (!currentGuarantor.lugar_expedicion ||
                                            currentGuarantor.lugar_expedicion === '0') &&
                                        currentGuarantor.enviado
                                    " class="text-danger">Seleccione *</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Género</label>
                                    <select v-model="currentGuarantor.sexo" :disabled="currentGuarantor.action === 2"
                                        class="form-select">
                                        <option value="0" disabled>Seleccione</option>
                                        <option v-for="gender in genders" :key="gender.nombre" :value="gender.nombre">
                                            {{ gender.nombre }}
                                        </option>
                                    </select>
                                    <small v-if="
                                        (!currentGuarantor.sexo ||
                                            currentGuarantor.sexo === '0') &&
                                        currentGuarantor.enviado
                                    " class="text-danger">Seleccione *</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Estado Civil</label>
                                    <select v-model="currentGuarantor.estado_civil"
                                        :disabled="currentGuarantor.action === 2" class="form-select">
                                        <option value="0" disabled>Seleccione</option>
                                        <option v-for="status in maritalStatuses" :key="status.nombre"
                                            :value="status.nombre">
                                            {{ status.nombre }}
                                        </option>
                                    </select>
                                    <small v-if="
                                        (!currentGuarantor.estado_civil ||
                                            currentGuarantor.estado_civil === '0') &&
                                        currentGuarantor.enviado
                                    " class="text-danger">Seleccione *</small>
                                </div>

                                <!-- Tercera fila: Ingreso Mensual, Actividad, Vivienda -->
                                <div class="col-md-2">
                                    <label class="fw-bold">Ingreso Mensual</label>
                                    <input v-model="currentGuarantor.ingreso_mensual"
                                        :disabled="currentGuarantor.action === 2" class="form-control"
                                        @input="filterNumericInput" />
                                    <small v-if="
                                        !currentGuarantor.ingreso_mensual &&
                                        currentGuarantor.enviado
                                    " class="text-danger">Ingrese un ingreso *</small>
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Vivienda</label>
                                    <select v-model="currentGuarantor.vivienda"
                                        :disabled="currentGuarantor.action === 2" class="form-select">
                                        <option value="0" disabled>Seleccione</option>
                                        <option v-for="housing in housingTypes" :key="housing.nombre"
                                            :value="housing.nombre">
                                            {{ housing.nombre }}
                                        </option>
                                    </select>
                                    <small v-if="
                                        (!currentGuarantor.vivienda ||
                                            currentGuarantor.vivienda === '0') &&
                                        currentGuarantor.enviado
                                    " class="text-danger">Seleccione *</small>
                                </div>

                                <!-- Cuarta fila: Tipo -->
                                <div class="col-md-2">
                                    <label class="fw-bold">Garante/Codeudor</label>
                                    <select v-model="currentGuarantor.tipo" :disabled="currentGuarantor.action === 2"
                                        class="form-select">
                                        <option value="0" disabled>Seleccione</option>
                                        <option value="Garante">Garante</option>
                                        <option value="Codeudor">Codeudor</option>
                                    </select>
                                    <small v-if="
                                        (!currentGuarantor.tipo ||
                                            currentGuarantor.tipo === '0') &&
                                        currentGuarantor.enviado
                                    " class="text-danger">Seleccione *</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">Actividad</label>
                                    <textarea v-model="activitySearch" :disabled="currentGuarantor.action === 2"
                                        class="form-control" placeholder="Buscar actividad..." @input="filterActivities"
                                        rows="2" @keydown.enter.prevent>
                                    </textarea>
                                    <div v-if="filteredActivities.length > 0 && activitySearch"
                                        class="activity-dropdown">
                                        <ul>
                                            <li v-for="activity in filteredActivities" :key="activity.id"
                                                @click="selectActivity(activity)">
                                                {{ activity.nombre }}
                                            </li>
                                        </ul>
                                    </div>
                                    <small v-if="
                                        !currentGuarantor.actividad && currentGuarantor.enviado
                                    " class="text-danger">Seleccione una actividad *</small>
                                </div>
                            </div>

                            <!-- Addresses -->
                            <hr />
                            <p><strong>Direcciones de Contacto</strong></p>
                            <div v-for="(address, index) in addresses" :key="index"
                                class="card border border-dark border-2 mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <label class="text-dark fw-bold">Dirección {{ index + 1 }}</label>
                                    <div>

                                        <a href="#" v-if="currentGuarantor.action !== 2 && (!address.lat || !address.lng)" @click="openMapModal(index, 'add')" class="btn btn-info btn-sm me-2">
                                            <i class="fas fa-map-marker-alt"></i> Agregar Ubicación
                                        </a>
                                        <a  href="#" v-if="address.lat && address.lng" @click="openMapModal(index, 'view')" class="btn btn-info btn-sm me-2">
                                            <i class="fas fa-eye"></i> Ver Ubicación
                                        </a>
                                        <a href="#" v-if="currentGuarantor.action !== 2 && address.lat && address.lng" @click="openMapModal(index, 'update')" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Actualizar Ubicación
                                        </a>

                                        <button v-if="currentGuarantor.action !== 2" type="button" class="btn btn-success btn-sm" @click="addAddress">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button v-if="currentGuarantor.action !== 2 && addresses.length > 1" type="button"
                                            class="btn btn-danger btn-sm ms-2" @click="removeAddress(index)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="fw-bold">Tipo</label>
                                            <select v-model="address.tipo" :disabled="currentGuarantor.action === 2"
                                                class="form-select">
                                                <option value="" disabled>Seleccione</option>
                                                <option v-for="type in addressTypes" :key="type" :value="type">
                                                    {{ type }}
                                                </option>
                                            </select>
                                            <small v-if="!address.tipo && currentGuarantor.enviado"
                                                class="text-danger">Requerido *</small>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="fw-bold">Departamento</label>
                                            <select v-model="address.departamento"
                                                :disabled="currentGuarantor.action === 2" class="form-control">
                                                <option value="">Seleccione un departamento</option>
                                                <option v-for="depto in departamentosBolivia" :key="depto.id"
                                                    :value="depto.nombre">
                                                    {{ depto.nombre }}
                                                </option>
                                            </select>
                                            <small v-if="
                                                !address.departamento && currentGuarantor.enviado
                                            " class="text-danger">Requerido *</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="fw-bold">Ciudad</label>
                                            <input v-model="address.ciudad" :disabled="currentGuarantor.action === 2"
                                                class="form-control text-capitalize" />
                                        </div>
                                        <div class="col-md-2">
                                            <label class="fw-bold">Zona</label>
                                            <input v-model="address.zona" :disabled="currentGuarantor.action === 2"
                                                class="form-control" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Descripción</label>
                                            <input v-model="address.descripcion"
                                                :disabled="currentGuarantor.action === 2" class="form-control" />
                                            <small v-if="
                                                !address.descripcion && currentGuarantor.enviado
                                            " class="text-danger">Requerido *</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phones -->
                            <hr />
                            <p><strong>Números de Contacto</strong></p>
                            <div v-for="(phone, index) in phones" :key="index"
                                class="card border border-dark border-2 mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <label class="text-dark fw-bold">Teléfono {{ index + 1 }}</label>
                                    <div v-if="currentGuarantor.action !== 2">
                                        <button type="button" class="btn btn-success btn-sm" @click="addPhone">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button v-if="phones.length > 1" type="button"
                                            class="btn btn-danger btn-sm ms-2" @click="removePhone(index)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="fw-bold">Tipo</label>
                                            <select v-model="phone.tipo" :disabled="currentGuarantor.action === 2"
                                                class="form-select" @change="resetPhoneFields(phone)">
                                                <option value="Numero telefono">Nr. Telf.</option>
                                                <option value="Informacion contacto">
                                                    Inf. Contacto
                                                </option>
                                            </select>
                                            <small v-if="!phone.tipo && currentGuarantor.enviado"
                                                class="text-danger">Requerido *</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="fw-bold">Número</label>
                                            <input v-model="phone.numero" :disabled="currentGuarantor.action === 2"
                                                class="form-control" />
                                            <small v-if="!phone.numero && currentGuarantor.enviado"
                                                class="text-danger">Requerido *</small>
                                        </div>
                                        <div v-if="phone.tipo === 'Numero telefono'" class="col-md-7">
                                            <label class="fw-bold">Observación</label>
                                            <input v-model="phone.observacion" :disabled="currentGuarantor.action === 2"
                                                class="form-control" />
                                            <small v-if="
                                                !phone.observacion &&
                                                phone.tipo === 'Numero telefono' &&
                                                currentGuarantor.enviado
                                            " class="text-danger">Requerido *</small>
                                        </div>
                                        <template v-if="phone.tipo === 'Informacion contacto'">
                                            <div class="col-md-2">
                                                <label class="fw-bold">Nombre</label>
                                                <input v-model="phone.nombre" :disabled="currentGuarantor.action === 2"
                                                    class="form-control text-capitalize" />
                                                <small v-if="!phone.nombre && currentGuarantor.enviado"
                                                    class="text-danger">Requerido *</small>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="fw-bold">Apellidos</label>
                                                <input v-model="phone.apellidos"
                                                    :disabled="currentGuarantor.action === 2" class="form-control text-capitalize" />
                                                <small v-if="!phone.apellidos && currentGuarantor.enviado"
                                                    class="text-danger">Requerido *</small>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="fw-bold">Relación</label>
                                                <input v-model="phone.relacion"
                                                    :disabled="currentGuarantor.action === 2" class="form-control" />
                                                <small v-if="!phone.relacion && currentGuarantor.enviado"
                                                    class="text-danger">Requerido *</small>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- Footer -->
                        <div class="card-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" @click="closeModal">
                                <i class="fas fa-times-circle"></i> Cerrar
                            </button>
                            <!-- <button v-if="currentGuarantor.action === 0" :disabled="savingGuarantor" type="submit"
                                class="btn btn-success ms-2">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button v-if="currentGuarantor.action === 1" type="submit" class="btn btn-success ms-2">
                                <i class="fas fa-edit"></i> Modificar
                            </button> -->
                            <button v-if="currentGuarantor.action === 0" :disabled="savingGuarantor" type="submit"
                                class="btn btn-success ms-2" :class="{ 'btn-loading': savingGuarantor }">
                                <span v-if="!savingGuarantor">
                                    <i class="fas fa-save"></i> Guardar
                                </span>
                                <span v-else>
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>

                            <button :disabled="modifyGuarantor" v-if="currentGuarantor.action === 1" type="submit"
                                class="btn btn-success ms-2" :class="{ 'btn-loading': modifyGuarantor }">

                                <span v-if="!modifyGuarantor">
                                    <i class="fas fa-edit"></i> Modificar
                                </span>
                                <span v-else>
                                    <i class="fas fa-spinner fa-spin"></i> Modificando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Photo Modal -->
        <div id="modalPhoto" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-dark border-2">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-camera me-2"></i>Foto del Codeudor/Garante
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closePhotoModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="image-preview-container mb-4">
                            <img :src="previewImage || (currentGuarantor.imagen
                                ? `/img/codeudor/${currentGuarantor.imagen}`
                                : '/img/codeudor/default.png')" class="img-thumbnail customer-photo"
                                alt="Foto del codeudor" />
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


        <!-- Google Maps Modal -->
        <div id="modalMapa" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-secondary border-2">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark fw-bold" id="mapaModalLabel">{{ mapModalTitle }}</h5>
                        <button @click="closeMapModal()" type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
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
            selectedFile: null,
            previewImage: null,
            view: 0,
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
            savingGuarantor: false,
            modifyGuarantor: false,
            guarantors: [],
            currentGuarantor: this.getDefaultGuarantor(),
            addresses: [],
            phones: [],
            activities: [],
            filteredActivities: [],
            activitySearch: "",
            searchCriteria: "codeudor.nombre",
            searchQuery: "",
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
        imprimirReporteCodeudores() {
            Swal.fire({
                title: 'Generando Reporte',
                text: 'Preparando listado de codeudores...',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let url = '/codeudor/reporte'; 
            
            axios.get(url, {
                params: {
                    buscar: this.buscar,
                    criterio: this.criterio
                },
                responseType: 'blob'
            })
            .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'reporte_codeudores.pdf');
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
                    text: 'Hubo un problema al generar el reporte de codeudores.'
                });
            });
        },
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
                    this.mapSearchQuery = place.formatted_address;
                    this.updateAddressCoordinates(place.geometry.location.lat(), place.geometry.location.lng());
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
                // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: "error",
                        title: "Archivo demasiado grande",
                        text: "La imagen debe ser menor a 2MB",
                        timer: 2000
                    });
                    event.target.value = ''; // Clear input
                    return;
                }
                // Validate file type
                if (!file.type.match('image.*')) {
                    Swal.fire({
                        icon: "error",
                        title: "Formato no soportado",
                        text: "Por favor selecciona una imagen JPG o PNG",
                        timer: 2000
                    });
                    event.target.value = ''; // Clear input
                    return;
                }
                this.selectedFile = file;
                // Generate preview
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
            formData.append("id_codeudor", this.currentGuarantor.id);
            formData.append("imagen", this.selectedFile);
            
            try {
                const response = await axios.post("/fotoCodeudor", formData);
                this.currentGuarantor.imagen = response.data.imagen;
                Swal.fire({
                    icon: "success",
                    title: "Foto actualizada",
                    timer: 1500
                });
                this.previewImage = null;
                this.selectedFile = null;
                document.getElementById('customerPhotoUpload').value = ''; // Clear input
                await this.fetchGuarantors(1);
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
        buscarCodeudor() {
            // Limpiar timeout anterior si aún no se ejecutaba
            if (this.debounceTimeout) {
                clearTimeout(this.debounceTimeout);
            }

            // Establecer nuevo timeout
            this.debounceTimeout = setTimeout(() => {
                this.fetchGuarantors(); // Llamada real a la API después del debounce
            }, 300); // 500 ms = 0.5 segundos sin escribir
        },

        resetPhoneFields(phone) {
            if (phone.tipo === "Numero telefono") {
                phone.nombre = "";
                phone.apellidos = "";
                phone.relacion = "";
            } else if (phone.tipo === "Informacion contacto") {
                phone.observacion = "";
            }
        },
        getDefaultGuarantor() {
            return {
                id_cliente: 0,
                nombre: "",
                // fecha_nacimiento: moment().format('YYYY-MM-DD'),
                fecha_nacimiento: moment().subtract(18, "years").format("YYYY-MM-DD"),
                ci: "",
                ingreso_mensual: "",
                tipo: "0",
                actividad: "",
                estado_civil: "0",
                vivienda: "0",
                sexo: "0",
                lugar_expedicion: "0",
                enviado: 0,
                action: 0,
                imagen: "",
            };
        },

        async fetchGuarantors() {

            try {
                const response = await axios.get("/get_codeudores", {
                    params: {
                        criterio: this.searchCriteria,
                        buscar: this.searchQuery,
                    },
                });
                this.guarantors = response.data;
            } catch (error) {
                console.error("Error fetching guarantors:", error);
            }
        },
        async fetchActivities() {
            try {
                const response = await axios.get("/get_actividades");
                this.activities = response.data;
            } catch (error) {
                console.error("Error fetching activities:", error);
            }
        },
        filterActivities() {
            if (!this.activitySearch) {
                this.filteredActivities = [];
                return;
            }
            this.filteredActivities = this.activities.filter((activity) =>
                activity.nombre
                    .toLowerCase()
                    .includes(this.activitySearch.toLowerCase())
            );
        },
        selectActivity(activity) {
            this.currentGuarantor.actividad = activity.nombre;
            this.activitySearch = activity.nombre;
            this.filteredActivities = [];
        },
        async initializeData() {
            await Promise.all([this.fetchGuarantors(), this.fetchActivities()]);

        },
        openNewGuarantorModal() {
            this.currentGuarantor = this.getDefaultGuarantor();
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
            this.activitySearch = "";
            //$("#modalGuarantor").modal("show");
            this.view = 1;
        },
        async editGuarantor(item) {
            this.currentGuarantor = {
                ...item,
                action: 1,
                enviado: 0,
            };
            this.activitySearch = item.actividad;
            await this.fetchGuarantorDetails(item.id);
            //$("#modalGuarantor").modal("show");
            this.view = 1;
        },
        async viewGuarantor(item) {
            this.currentGuarantor = {
                ...item,
                action: 2,
            };
            this.activitySearch = item.actividad;
            await this.fetchGuarantorDetails(item.id);
            //$("#modalGuarantor").modal("show");
            this.view = 1;
        },
        async fetchGuarantorDetails(id) {
            this.preloader = true;
            try {
                const response = await axios.get(
                    `/get_direcciones_telefono_codeudor?id_codeudor=${id}`
                );
                this.addresses = response.data.direcciones;
                this.phones = response.data.telefonos;
            } catch (error) {
                console.error("Error fetching details:", error);
            } finally {
                this.preloader = false;
            }
        },
        async activateGuarantor(item) {
            try {
                await axios.get(`/activar_codeudor?id_codeudor=${item.id}`);
                await this.fetchGuarantors();
            } catch (error) {
                console.error("Error activating guarantor:", error);
            }
        },
        async deactivateGuarantor(item) {
            try {
                await axios.get(`/desactivar_codeudor?id_codeudor=${item.id}`);
                await this.fetchGuarantors();
            } catch (error) {
                console.error("Error deactivating guarantor:", error);
            }
        },
        async viewGuarantorPdf(item) {
            this.preloader = true;
            try {
                const response = await axios.get("/codeudores_pdf", {
                    params: {
                        id_codeudor: item.id,
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
        openPhotoModal(item) {
            this.currentGuarantor = {
                ...item,
            };
            $("#modalPhoto").modal("show");
        },
        closePhotoModal() {
            this.previewImage = null;
            this.selectedFile = null;
            document.getElementById('customerPhotoUpload').value = ''; // Clear input
            $("#modalPhoto").modal("hide");
        },

        closeModal() {
            // $("#modalGuarantor").modal("hide");
            this.view = 0;
        },
        async saveGuarantor() {
            this.currentGuarantor.enviado = 1;
            if (!this.validateGuarantor()) return;
            this.savingGuarantor = true;
            try {
                const response = await axios.post("/save_codeudor", {
                    ...this.currentGuarantor,
                    direcciones: this.addresses,
                    telefonos: this.phones,
                });
                if (response.data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Garante/Codeudor guardado",
                        timer: 1000,
                    });
                    this.closeModal();
                    await this.initializeData();
                } else if (response.data.error === "duplicate") {
                    Swal.fire("Error", response.data.message, "error");
                }
            } catch (error) {
                console.error("Error saving guarantor:", error);
                let errorMessage = "Ocurrió un error al guardar el cliente";
                if (
                    error.response?.status === 422 &&
                    error.response?.data?.error === "duplicate"
                ) {
                    errorMessage = error.response.data.message;
                    await Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Codeudor/Garante duplicado",
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
                this.savingGuarantor = false;
            }
        },
        async updateGuarantor() {
            this.currentGuarantor.enviado = 1;
            if (!this.validateGuarantor()) return;
            try {
                this.modifyGuarantor = true;
                await axios.post("/modify_codeudor", {
                    ...this.currentGuarantor,
                    direcciones: this.addresses,
                    telefonos: this.phones,
                });
                Swal.fire({
                    icon: "success",
                    title: "Garante/Codeudor actualizado",
                    timer: 1000,
                });
                this.closeModal();
                await this.fetchGuarantors(1);
            } catch (error) {
                console.error("Error updating guarantor:", error);
            } finally {
                this.modifyGuarantor = false;
            }
        },
        validateGuarantor() {
            const guarantorValid =
                this.currentGuarantor.nombre &&
                this.currentGuarantor.ci &&
                this.currentGuarantor.ingreso_mensual &&
                this.currentGuarantor.actividad &&
                this.currentGuarantor.estado_civil !== "0" &&
                this.currentGuarantor.vivienda !== "0" &&
                this.currentGuarantor.sexo !== "0" &&
                this.currentGuarantor.lugar_expedicion !== "0" &&
                this.currentGuarantor.tipo !== "0";
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
            const isValid = guarantorValid && addressValid && phoneValid;
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
.btn-close-white{
    color:#000;
    
}
.dropdown-toggle::after {
  display: none !important;
}
.guarantor-management {

    background-color: #f5f7fb;
}

/* .page-content {
    margin: 0 auto;
} */

.page-title-box {
    padding: 15px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.filters {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin: 0px 0;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* .card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.card-body {
    padding: 20px;
} */

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
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

.activity-dropdown {
    position: absolute;
    z-index: 1050;
    width: 100%;
    background: #fff;
    border: 1px solid #ececec;
    max-height: 250px;
    overflow: auto;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.activity-dropdown ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.activity-dropdown li {
    padding: 8px;
    cursor: pointer;
    border-bottom: 1px solid #ececec;
}

.activity-dropdown li:hover {
    background-color: #f0f0f0;
}
</style>

<style scoped>
/* Estilos personalizados para el modal de foto */
.customer-photo {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #198754;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
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

/* Efecto hover para el botón de subir */
.btn-outline-success:hover {
    background-color: #198754;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .customer-photo {
        width: 150px;
        height: 150px;
    }
}
</style>
