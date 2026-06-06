<template>
    <div class="codeudor-form-container">
        <div class="card">
            <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 text-center">
                    <h5 class="header-title my-0 fw-bold text-dark fw-bold text-uppercase">
                        {{ tituloFormulario }}
                    </h5>
                </div>
                <button @click="cerrarFormulario()" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
            </div>

            <form @submit.prevent="accion === 0 ? saveGuarantor() : updateGuarantor()">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="fw-bold">Nombre</label>
                            <input v-model="currentGuarantor.nombre" :disabled="accion === 2" class="form-control text-capitalize" />
                            <small v-if="!currentGuarantor.nombre && currentGuarantor.enviado" class="text-danger">Ingrese un nombre *</small>
                        </div>
                        <div class="col-md-2">
                            <label class="fw-bold">Fecha de Nacimiento</label>
                            <input v-model="currentGuarantor.fecha_nacimiento" type="date" :disabled="accion === 2" class="form-control" />
                        </div>
                        <div class="col-md-2">
                            <label class="fw-bold">CI</label>
                            <input v-model="currentGuarantor.ci" :disabled="accion === 2" class="form-control" />
                            <small v-if="!currentGuarantor.ci && currentGuarantor.enviado" class="text-danger">Ingrese un CI *</small>
                        </div>

                        <div class="col-md-2">
                            <label class="fw-bold">DPTO</label>
                            <select v-model="currentGuarantor.lugar_expedicion" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="place in expeditionPlaces" :key="place.sigla" :value="place.sigla">{{ place.sigla }}</option>
                            </select>
                            <small v-if="(!currentGuarantor.lugar_expedicion || currentGuarantor.lugar_expedicion === '0') && currentGuarantor.enviado" class="text-danger">Seleccione *</small>
                        </div>
                        <div class="col-md-2">
                            <label class="fw-bold">Género</label>
                            <select v-model="currentGuarantor.sexo" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="gender in genders" :key="gender.nombre" :value="gender.nombre">{{ gender.nombre }}</option>
                            </select>
                            <small v-if="(!currentGuarantor.sexo || currentGuarantor.sexo === '0') && currentGuarantor.enviado" class="text-danger">Seleccione *</small>
                        </div>
                        <div class="col-md-2">
                            <label class="fw-bold">Estado Civil</label>
                            <select v-model="currentGuarantor.estado_civil" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="status in maritalStatuses" :key="status.nombre" :value="status.nombre">{{ status.nombre }}</option>
                            </select>
                            <small v-if="(!currentGuarantor.estado_civil || currentGuarantor.estado_civil === '0') && currentGuarantor.enviado" class="text-danger">Seleccione *</small>
                        </div>

                        <div class="col-md-2">
                            <label class="fw-bold">Ingreso Mensual</label>
                            <input v-model="currentGuarantor.ingreso_mensual" :disabled="accion === 2" class="form-control" @input="filterNumericInput" />
                            <small v-if="!currentGuarantor.ingreso_mensual && currentGuarantor.enviado" class="text-danger">Ingrese un ingreso *</small>
                        </div>

                        <div class="col-md-2">
                            <label class="fw-bold">Vivienda</label>
                            <select v-model="currentGuarantor.vivienda" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="housing in housingTypes" :key="housing.nombre" :value="housing.nombre">{{ housing.nombre }}</option>
                            </select>
                            <small v-if="(!currentGuarantor.vivienda || currentGuarantor.vivienda === '0') && currentGuarantor.enviado" class="text-danger">Seleccione *</small>
                        </div>

                        <div class="col-md-2">
                            <label class="fw-bold">Garante/Codeudor</label>
                            <select v-model="currentGuarantor.tipo" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option value="Garante">Garante</option>
                                <option value="Codeudor">Codeudor</option>
                            </select>
                            <small v-if="(!currentGuarantor.tipo || currentGuarantor.tipo === '0') && currentGuarantor.enviado" class="text-danger">Seleccione *</small>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group my-0">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="fw-bold text-dark">Seleccione una actividad:</label>
                                        <div class="input-group mt-0 pt-0">
                                            <textarea 
                                                @keydown.enter.prevent 
                                                :disabled="accion === 2" 
                                                v-model="actividadClase.buscar" 
                                                class="form-control text-dark" 
                                                placeholder="Buscar actividad..."
                                                @input="filtrarActividades(actividadClase.buscar)" 
                                                autocomplete="off" 
                                                rows="2">
                                            </textarea>
                                        </div>
                                        <small v-if="(!currentGuarantor.actividad || actividadClase.buscar == '') && currentGuarantor.enviado" class="text-danger">
                                            Ingrese una actividad *
                                        </small>
                                    </div>
                                    <div class="col-md-12" style="position:relative;">
                                        <template v-if="filteredItemsActividades.length > 0">
                                            <div class="com-completion-results shadow" style="z-index: 1050; position: absolute; top: 100%; width: 100%; background: #fff; border: 1px solid #ececec; max-height: 250px; overflow: auto;">
                                                <ul style="list-style: none; padding: 0; margin: 0">
                                                    <li v-for="(actividadItem, index) in filteredItemsActividades" :key="index" @click="seleccionarActividad(actividadItem)" style="cursor: pointer; padding: 8px; border-bottom: 1px solid #ececec;">
                                                        <h6 style="font-size: 14px; color: #000; margin: 0;">{{ actividadItem.nombre }}</h6>
                                                    </li>
                                                </ul>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <p><strong>Direcciones de Contacto</strong></p>
                    <div v-for="(address, index) in addresses" :key="index" class="card border border-dark border-2 mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <label class="text-dark fw-bold">Dirección {{ index + 1 }}</label>
                            <div>
                                <a href="#" v-if="accion !== 2 && (!address.lat || !address.lng)" @click="openMapModal(index, 'add')" class="btn btn-info btn-sm me-2"><i class="fas fa-map-marker-alt"></i> Agregar Ubicación</a>
                                <a href="#" v-if="address.lat && address.lng" @click="openMapModal(index, 'view')" class="btn btn-info btn-sm me-2"><i class="fas fa-eye"></i> Ver Ubicación</a>
                                <a href="#" v-if="accion !== 2 && address.lat && address.lng" @click="openMapModal(index, 'update')" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Actualizar Ubicación</a>
                                <button v-if="accion !== 2" type="button" class="btn btn-success btn-sm" @click="addAddress"><i class="fas fa-plus"></i></button>
                                <button v-if="accion !== 2 && addresses.length > 1" type="button" class="btn btn-danger btn-sm ms-2" @click="removeAddress(index)"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="fw-bold">Tipo</label>
                                    <select v-model="address.tipo" :disabled="accion === 2" class="form-select">
                                        <option value="" disabled>Seleccione</option>
                                        <option v-for="type in addressTypes" :key="type" :value="type">{{ type }}</option>
                                    </select>
                                    <small v-if="!address.tipo && currentGuarantor.enviado" class="text-danger">Requerido *</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Departamento</label>
                                    <select v-model="address.departamento" :disabled="accion === 2" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option v-for="depto in departamentosBolivia" :key="depto.id" :value="depto.nombre">{{ depto.nombre }}</option>
                                    </select>
                                    <small v-if="!address.departamento && currentGuarantor.enviado" class="text-danger">Requerido *</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Ciudad</label>
                                    <input v-model="address.ciudad" :disabled="accion === 2" class="form-control text-capitalize" />
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Zona</label>
                                    <input v-model="address.zona" :disabled="accion === 2" class="form-control" />
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold">Descripción</label>
                                    <input v-model="address.descripcion" :disabled="accion === 2" class="form-control" />
                                    <small v-if="!address.descripcion && currentGuarantor.enviado" class="text-danger">Requerido *</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <p><strong>Números de Contacto</strong></p>
                    <div v-for="(phone, index) in phones" :key="index" class="card border border-dark border-2 mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <label class="text-dark fw-bold">Teléfono {{ index + 1 }}</label>
                            <div v-if="accion !== 2">
                                <button type="button" class="btn btn-success btn-sm" @click="addPhone"><i class="fas fa-plus"></i></button>
                                <button v-if="phones.length > 1" type="button" class="btn btn-danger btn-sm ms-2" @click="removePhone(index)"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="fw-bold">Tipo</label>
                                    <select v-model="phone.tipo" :disabled="accion === 2" class="form-select" @change="resetPhoneFields(phone)">
                                        <option value="Numero telefono">Nr. Telf.</option>
                                        <option value="Informacion contacto">Inf. Contacto</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="fw-bold">Número</label>
                                    <input v-model="phone.numero" :disabled="accion === 2" class="form-control" />
                                    <small v-if="!phone.numero && currentGuarantor.enviado" class="text-danger">Requerido *</small>
                                </div>
                                <div v-if="phone.tipo === 'Numero telefono'" class="col-md-7">
                                    <label class="fw-bold">Observación</label>
                                    <input v-model="phone.observacion" :disabled="accion === 2" class="form-control" />
                                    <small v-if="!phone.observacion && phone.tipo === 'Numero telefono' && currentGuarantor.enviado" class="text-danger">Requerido *</small>
                                </div>
                                <template v-if="phone.tipo === 'Informacion contacto'">
                                    <div class="col-md-2">
                                        <label class="fw-bold">Nombre</label>
                                        <input v-model="phone.nombre" :disabled="accion === 2" class="form-control text-capitalize" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="fw-bold">Apellidos</label>
                                        <input v-model="phone.apellidos" :disabled="accion === 2" class="form-control text-capitalize" />
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold">Relación</label>
                                        <input v-model="phone.relacion" :disabled="accion === 2" class="form-control" />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary" @click="cerrarFormulario">
                        <i class="fas fa-times-circle"></i> Cerrar
                    </button>
                    <button v-if="accion === 0" :disabled="savingGuarantor" type="submit" class="btn btn-success ms-2" :class="{ 'btn-loading': savingGuarantor }">
                        <span v-if="!savingGuarantor"><i class="fas fa-save"></i> Guardar</span>
                        <span v-else><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                    <button :disabled="modifyGuarantor" v-if="accion === 1" type="submit" class="btn btn-success ms-2" :class="{ 'btn-loading': modifyGuarantor }">
                        <span v-if="!modifyGuarantor"><i class="fas fa-edit"></i> Modificar</span>
                        <span v-else><i class="fas fa-spinner fa-spin"></i> Modificando...</span>
                    </button>
                </div>
            </form>
        </div>

        <div id="modalPhotoComponent" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-dark border-2">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title text-white"><i class="fas fa-camera me-2"></i>Foto del Codeudor/Garante</h5>
                        <button type="button" class="btn-close btn-close-white" @click="closePhotoModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="image-preview-container mb-4">
                            <img :src="previewImage || (currentGuarantor.imagen ? `/img/codeudor/${currentGuarantor.imagen}` : '/img/codeudor/default.png')" class="img-thumbnail customer-photo" alt="Foto" />
                        </div>
                        <div class="file-upload-wrapper">
                            <label for="codeudorPhotoUpload" class="btn btn-outline-success w-100">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Seleccionar nueva foto
                            </label>
                            <input id="codeudorPhotoUpload" type="file" class="d-none" accept="image/*" @change="previewPhoto" />
                        </div>
                        <div class="mt-3 text-muted small">Formatos soportados: JPG, PNG. Tamaño máximo: 2MB</div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="closePhotoModal"><i class="fas fa-times-circle me-1"></i> Cerrar</button>
                        <button class="btn btn-success" @click="savePhoto" :disabled="!selectedFile"><i class="fas fa-save me-1"></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalMapaComponentCodeudor" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border border-secondary border-2">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark fw-bold">{{ mapModalTitle }}</h5>
                        <button @click="closeMapModal()" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         <div id="mapComponentCodeudor" style="height: 400px; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeMapModal()" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cerrar</button>
                        <button v-if="mapModalMode !== 'view'" @click="saveLocation()" class="btn btn-success"><i class="fas fa-save"></i> Guardar Ubicación</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";

export default {
    name: 'CodeudorForm',
    props: {
        codeudorId: {
            type: [Number, String],
            default: 0
        },
        accion: {
            type: Number,
            default: 0 // 0: Nuevo, 1: Editar, 2: Ver
        },
        initialData: {
            type: Object,
            default: () => null
        }
    },
    emits: ['cerrar', 'guardado'],
    data() {
        return {
            // Datos Estáticos
            departamentosBolivia: [
                { id: 1, nombre: "La Paz" }, { id: 2, nombre: "Cochabamba" }, { id: 3, nombre: "Santa Cruz" },
                { id: 4, nombre: "Oruro" }, { id: 5, nombre: "Potosí" }, { id: 6, nombre: "Tarija" },
                { id: 7, nombre: "Chuquisaca" }, { id: 8, nombre: "Beni" }, { id: 9, nombre: "Pando" },
            ],
            expeditionPlaces: [
                { sigla: "LP", nombre: "La Paz" }, { sigla: "CB", nombre: "Cochabamba" }, { sigla: "SC", nombre: "Santa Cruz" },
                { sigla: "OR", nombre: "Oruro" }, { sigla: "PT", nombre: "Potosí" }, { sigla: "TJ", nombre: "Tarija" },
                { sigla: "BN", nombre: "Beni" }, { sigla: "PD", nombre: "Pando" }, { sigla: "CH", nombre: "Chuquisaca" },
                { sigla: "Sin Expedición", nombre: "Sin Expedición" }
            ],
            genders: [{ nombre: "Masculino" }, { nombre: "Femenino" }],
            maritalStatuses: [{ nombre: "soltero/a" }, { nombre: "Casado/a" }, { nombre: "divorciado/a" }, { nombre: "viudo/a" }, { nombre: "separado/a" }, { nombre: "conviviente/a" }],
            housingTypes: [{ nombre: "Casa propia" }, { nombre: "Alquiler" }, { nombre: "Vivienda familiar" }],
            addressTypes: [
                "Casa", "Oficina", "Negocio", "Apartamento", "Edificio", "Otra Residencia", "Residencia Temporal",
                "Domicilio Familiar", "Propiedad Alquilada", "Propiedad Propia", "Zona Rural", "Zona Urbana", "Barrio"
            ],

            // Estado del formulario
            currentGuarantor: this.getDefaultGuarantor(),
            addresses: [],
            phones: [],
            savingGuarantor: false,
            modifyGuarantor: false,

            actividades: [],
            filteredItemsActividades: [], // Antes filteredActivities
            actividadClase: { id_actividad: 0, buscar: "" },

            // Foto
            selectedFile: null,
            previewImage: null,

            // Mapa
            map: null,
            marker: null,
            mapModalMode: 'add',
            mapModalTitle: '',
            currentAddressIndex: null,
        };
    },
    computed: {
        tituloFormulario() {
            if (this.accion === 0) return "Agregar Nuevo Garante/Codeudor";
            if (this.accion === 1) return "Modificar Garante/Codeudor";
            return `Información del Garante/Codeudor: ${this.currentGuarantor.nombre || ''}`;
        }
    },
    watch: {
        codeudorId: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal !== 0) {
                    this.loadGuarantorDetails(newVal);
                } else if (this.accion === 0) {
                    this.resetForm();
                }
            }
        },
        initialData: {
            immediate: true,
            handler(newVal) {
                if(newVal && this.accion !== 0) {
                     this.currentGuarantor = { ...newVal, enviado: 0 };
                     this.currentGuarantor.lugar_expedicion = this.normalizeExpeditionPlace(this.currentGuarantor.lugar_expedicion);
                     this.actividadClase.buscar = newVal.actividad || "";
                }
            }
        }
    },
    mounted() {
        this.fetchActivities();
        // Si hay datos iniciales y no se requiere fetch inmediato
        if(this.initialData && this.accion !== 0) {
            this.currentGuarantor = { ...this.initialData, enviado: 0 };
            this.currentGuarantor.lugar_expedicion = this.normalizeExpeditionPlace(this.currentGuarantor.lugar_expedicion);
            this.actividadClase.buscar = this.initialData.actividad;
            // Aún cargamos detalles (direcciones/teléfonos) si tenemos ID
            if(this.codeudorId) this.loadGuarantorDetails(this.codeudorId);
        }
    },
    methods: {
        cerrarFormulario() {
            this.$emit('cerrar');
        },
        normalizeExpeditionPlace(value) {
            if (!value) return "0";
            const val = value.toString().trim();
            // Buscar por sigla (LP, CB, etc.)
            const foundBySigla = this.expeditionPlaces.find(
                p => p.sigla.toLowerCase() === val.toLowerCase()
            );
            if (foundBySigla) return foundBySigla.sigla;

            // Buscar por nombre (La Paz, Cochabamba, etc.)
            const foundByNombre = this.expeditionPlaces.find(
                p => p.nombre.toLowerCase() === val.toLowerCase()
            );
            if (foundByNombre) return foundByNombre.sigla;

            return val;
        },
        getDefaultGuarantor() {
            return {
                id_cliente: 0,
                nombre: "",
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
                imagen: "",
            };
        },
        resetForm() {
            this.currentGuarantor = this.getDefaultGuarantor();
            this.addresses = [{ tipo: "", departamento: "", ciudad: "", zona: "", descripcion: "" }];
            this.phones = [{ tipo: "Numero telefono", numero: "", observacion: "" }];
            this.actividadClase.buscar = "";
        },
        async loadGuarantorDetails(id) {
            try {
                const response = await axios.get(`/get_direcciones_telefono_codeudor?id_codeudor=${id}`);
                this.addresses = response.data.direcciones.map(d => ({
                    ...d,
                    lat: d.lat ? parseFloat(d.lat) : null,
                    lng: d.lng ? parseFloat(d.lng) : null,
                }));
                this.phones = response.data.telefonos;
            } catch (error) {
                console.error("Error fetching details:", error);
            }
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
                    Swal.fire({ icon: "success", title: "Guardado correctamente", timer: 1000 });
                    this.$emit('guardado');
                    this.cerrarFormulario();
                } else if (response.data.error === "duplicate") {
                    Swal.fire("Error", response.data.message, "error");
                }
            } catch (error) {
                console.error("Error saving:", error);
                Swal.fire("Error", "Ocurrió un error al guardar", "error");
            } finally {
                this.savingGuarantor = false;
            }
        },
        async updateGuarantor() {
            this.currentGuarantor.enviado = 1;
            if (!this.validateGuarantor()) return;
            this.modifyGuarantor = true;
            try {
                await axios.post("/modify_codeudor", {
                    ...this.currentGuarantor,
                    direcciones: this.addresses,
                    telefonos: this.phones,
                });
                Swal.fire({ icon: "success", title: "Actualizado correctamente", timer: 1000 });
                this.$emit('guardado');
                this.cerrarFormulario();
            } catch (error) {
                console.error("Error updating:", error);
            } finally {
                this.modifyGuarantor = false;
            }
        },
        validateGuarantor() {
            const basicValid = this.currentGuarantor.nombre && this.currentGuarantor.ci &&
                this.currentGuarantor.ingreso_mensual && this.currentGuarantor.actividad &&
                this.currentGuarantor.tipo !== "0";
            
            const addressValid = this.addresses.every(a => a.tipo && a.departamento && a.descripcion);
            const phoneValid = this.phones.every(p => p.tipo && p.numero);

            if (!(basicValid && addressValid && phoneValid)) {
                Swal.fire("Advertencia", "Faltan datos por completar", "warning");
            }
            return basicValid && addressValid && phoneValid;
        },
        
        // --- Auxiliares (Direcciones, Telefonos, Actividades) ---
        addAddress() {
            this.addresses.push({ tipo: "", departamento: "", ciudad: "", zona: "", descripcion: "" });
        },
        removeAddress(index) {
            if (this.addresses.length > 1) this.addresses.splice(index, 1);
        },
        addPhone() {
            this.phones.push({ tipo: "Numero telefono", numero: "", observacion: "" });
        },
        removePhone(index) {
            if (this.phones.length > 1) this.phones.splice(index, 1);
        },
        resetPhoneFields(phone) {
             if (phone.tipo === "Numero telefono") { phone.nombre = ""; phone.apellidos = ""; phone.relacion = ""; } 
             else if (phone.tipo === "Informacion contacto") { phone.observacion = ""; }
        },
        filterNumericInput(event) {
            event.target.value = event.target.value.replace(/[^0-9.]*/g, "");
        },
     
        async fetchActivities() { // Puedes renombrarlo a getActividades si prefieres consistencia total
            try {
                const response = await axios.get("/get_actividades");
                this.actividades = response.data; // Antes this.activities
            } catch (error) { console.error(error); }
        },

        filtrarActividades(keyword) {
            if (keyword === "") { 
                this.filteredItemsActividades = []; 
                return; 
            }
            this.filteredItemsActividades = this.actividades.filter((act) => 
                act.nombre.toLowerCase().includes(keyword.toLowerCase())
            );
        },

        seleccionarActividad(item) {
            this.actividadClase.id_actividad = item.id;
            this.actividadClase.buscar = item.nombre;
            this.currentGuarantor.actividad = item.nombre; // Asegura que se guarde en el modelo principal
            this.filteredItemsActividades = [];
        },

        // --- Lógica Foto ---
        previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                 if (file.size > 2 * 1024 * 1024) return Swal.fire("Error", "Imagen muy grande", "error");
                 this.selectedFile = file;
                 const reader = new FileReader();
                 reader.onload = (e) => { this.previewImage = e.target.result; };
                 reader.readAsDataURL(file);
            }
        },
        async savePhoto() {
            if (!this.selectedFile) return;
            const formData = new FormData();
            formData.append("id_codeudor", this.currentGuarantor.id || this.currentGuarantor.id_codeudor); // Verificar campo ID
            formData.append("imagen", this.selectedFile);
            
            try {
                const response = await axios.post("/fotoCodeudor", formData);
                this.currentGuarantor.imagen = response.data.imagen;
                Swal.fire({ icon: "success", title: "Foto actualizada", timer: 1500 });
                this.closePhotoModal();
                this.$emit('guardado'); // Para refrescar lista en padre si muestra foto
            } catch (error) { console.error(error); }
        },
        closePhotoModal() {
            $('#modalPhotoComponent').modal('hide');
            this.previewImage = null;
            this.selectedFile = null;
        },

        // --- Lógica del Mapa ---
        openMapModal(index, mode) {
            this.currentAddressIndex = index;
            this.mapModalMode = mode;
            this.mapModalTitle = mode === 'add' ? 'Agregar Ubicación' : (mode === 'view' ? 'Ver Ubicación' : 'Actualizar');
            
            $('#modalMapaComponentCodeudor').one('shown.bs.modal', () => {
                const address = this.addresses[index];
                if (address && address.lat && address.lng) {
                    this.initializeMap(parseFloat(address.lat), parseFloat(address.lng));
                } else {
                    this.initializeMap(-17.7833, -63.1821); 
                }
            }).modal('show');
        },
        closeMapModal() { $('#modalMapaComponentCodeudor').modal('hide'); },
        initializeMap(lat, lng) {
            const mapElement = document.getElementById('mapComponentCodeudor');
            if (!mapElement) return;
            
            if (this.map) {
                this.map.remove();
                this.map = null;
            }
            
            const L = window.L;
            this.map = L.map(mapElement).setView([lat, lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(this.map);
            
            const isDraggable = this.mapModalMode !== 'view';
            this.marker = L.marker([lat, lng], { draggable: isDraggable }).addTo(this.map);
            
            if (isDraggable) {
                // Si las coordenadas no están seteadas, guardamos la posición inicial por defecto
                const address = this.addresses[this.currentAddressIndex];
                if (address && (!address.lat || !address.lng)) {
                    this.updateCoords(lat, lng);
                }
                
                this.map.on('click', (event) => {
                    this.marker.setLatLng(event.latlng);
                    this.updateCoords(event.latlng.lat, event.latlng.lng);
                });
                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng();
                    this.updateCoords(pos.lat, pos.lng);
                });
            }
        },
        updateCoords(lat, lng) {
            if (this.currentAddressIndex !== null) {
                this.addresses[this.currentAddressIndex].lat = lat;
                this.addresses[this.currentAddressIndex].lng = lng;
            }
        },
        saveLocation() {
            if(this.addresses[this.currentAddressIndex].lat) {
                this.closeMapModal();
                Swal.fire({icon: 'success', title: 'Ubicación guardada', timer: 1000, showConfirmButton: false});
            }
        }
    }
};
</script>

<style scoped>
@import '../styles/frmCliente.css'; /* Asegúrate de que los estilos base estén disponibles */

/* Estilos específicos del form */
.customer-photo { width: 200px; height: 200px; object-fit: cover; border-radius: 50%; border: 3px solid #198754; }
.image-preview-container { display: flex; justify-content: center; align-items: center; min-height: 220px; }
.activity-dropdown::-webkit-scrollbar { width: 8px; }
.com-completion-results::-webkit-scrollbar { width: 8px; }
.com-completion-results::-webkit-scrollbar-track { background: #f1f1f1; }
.com-completion-results::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
</style>