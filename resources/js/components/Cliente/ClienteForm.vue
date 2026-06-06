<template>
  <div class="cliente-form-container">
    <div class="card">
        <div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 text-center">
                <h5 class="header-title my-0 fw-bold text-dark text-uppercase">
                    {{ tituloFormulario }}
                </h5>
            </div>
            <a @click="cerrarFormulario()" type="button" class="btn-close btn-close-white"></a>
        </div>
        
        <form @submit.prevent="accion === 0 ? saveCustomer() : updateCustomer()">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group my-2">
                            <label class="fw-bold">Nombre</label>
                            <input v-model="currentCustomer.nombre" :disabled="accion === 2" class="form-control text-capitalize" />
                            <small v-if="!currentCustomer.nombre && currentCustomer.enviado" class="text-danger">Ingrese un nombre *</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">Fecha de Nacimiento</label>
                            <input v-model="currentCustomer.fecha_nacimiento" type="date" :disabled="accion === 2" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">CI</label>
                            <input v-model="currentCustomer.ci" :disabled="accion === 2" class="form-control" />
                            <small v-if="!currentCustomer.ci && currentCustomer.enviado" class="text-danger">Ingrese un CI *</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">DPTO</label>
                            <select v-model="currentCustomer.lugar_expedicion" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="place in expeditionPlaces" :key="place.sigla" :value="place.sigla">{{ place.sigla }}</option>
                            </select>
                            <small v-if="(!currentCustomer.lugar_expedicion || currentCustomer.lugar_expedicion === '0') && currentCustomer.enviado" class="text-danger">Seleccione *</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">Género</label>
                            <select v-model="currentCustomer.sexo" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="gender in genders" :key="gender.nombre" :value="gender.nombre">{{ gender.nombre }}</option>
                            </select>
                            <small v-if="(!currentCustomer.sexo || currentCustomer.sexo === '0') && currentCustomer.enviado" class="text-danger">Seleccione *</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group my-2">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="fw-bold text-dark">Seleccione una actividad:</label>
                                    <div class="input-group mt-0 pt-0">
                                        <textarea @keydown.enter.prevent :disabled="accion === 2" v-model="actividadClase.buscar" 
                                            class="form-control text-dark" placeholder="Buscar actividad..."
                                            @input="filtrarActividades(actividadClase.buscar)" autocomplete="off" rows="2">
                                        </textarea>
                                    </div>
                                    <small v-if="(!currentCustomer.actividad || actividadClase.buscar == '') && currentCustomer.enviado" class="text-danger">Ingrese una actividad *</small>
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

                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">Estado Civil</label>
                            <select v-model="currentCustomer.estado_civil" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="status in maritalStatuses" :key="status.nombre" :value="status.nombre">{{ status.nombre }}</option>
                            </select>
                            <small v-if="(!currentCustomer.estado_civil || currentCustomer.estado_civil === '0') && currentCustomer.enviado" class="text-danger">Seleccione *</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">Vivienda</label>
                            <select v-model="currentCustomer.vivienda" :disabled="accion === 2" class="form-select">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="housing in housingTypes" :key="housing.nombre" :value="housing.nombre">{{ housing.nombre }}</option>
                            </select>
                            <small v-if="(!currentCustomer.vivienda || currentCustomer.vivienda === '0') && currentCustomer.enviado" class="text-danger">Seleccione *</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-2">
                            <label class="fw-bold">Ingreso Mensual</label>
                            <input v-model="currentCustomer.ingreso_mensual" :disabled="accion === 2" class="form-control" @input="filterNumericInput" />
                            <small v-if="!currentCustomer.ingreso_mensual && currentCustomer.enviado" class="text-danger">Ingrese un ingreso *</small>
                        </div>
                    </div>
                </div>

                <hr />
                <p><strong class="fw-bold">Direcciones de Contacto</strong></p>
                <div v-for="(address, index) in addresses" :key="index" class="card border border-dark border-2 mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-0 pe-0">
                        <label class="text-dark fw-bold">Dirección {{ index + 1 }}</label>
                        <div>
                            <a href="#" v-if="accion !== 2 && (!address.lat || !address.lng)" @click="openMapModal(index, 'add')" class="btn btn-info btn-sm me-2"><i class="fas fa-map-marker-alt"></i> Agregar Ubicación</a>
                            <a href="#" v-if="address.lat && address.lng" @click="openMapModal(index, 'view')" class="btn btn-info btn-sm me-2"><i class="fas fa-eye"></i> Ver Ubicación</a>
                            <a href="#" v-if="accion !== 2 && address.lat && address.lng" @click="openMapModal(index, 'update')" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Actualizar Ubicación</a>
                            <button v-if="accion !== 2" type="button" class="btn btn-success btn-sm ms-5" @click="addAddress"><i class="fas fa-plus"></i></button>
                            <button v-if="accion !== 2 && addresses.length > 1" type="button" class="btn btn-danger btn-sm ms-2" @click="removeAddress(index)"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="card-body grid">
                        <div>
                            <label class="fw-bold">Tipo</label>
                            <select v-model="address.tipo" :disabled="accion === 2" class="form-select">
                                <option value="" disabled>Seleccione</option>
                                <option v-for="type in addressTypes" :key="type" :value="type">{{ type }}</option>
                            </select>
                            <small v-if="!address.tipo && currentCustomer.enviado" class="text-danger">Requerido *</small>
                        </div>
                        <div>
                            <label class="fw-bold">Departamento</label>
                            <select v-model="address.departamento" :disabled="accion === 2" class="form-control">
                                <option value="">Seleccione</option>
                                <option v-for="depto in departamentosBolivia" :key="depto.id" :value="depto.nombre">{{ depto.nombre }}</option>
                            </select>
                            <small v-if="!address.departamento && currentCustomer.enviado" class="text-danger">Requerido *</small>
                        </div>
                        <div>
                            <label class="fw-bold">Ciudad</label>
                            <input v-model="address.ciudad" :disabled="accion === 2" class="form-control text-capitalize" />
                        </div>
                        <div>
                            <label class="fw-bold">Zona</label>
                            <input v-model="address.zona" :disabled="accion === 2" class="form-control" />
                        </div>
                        <div>
                            <label class="fw-bold">Descripción</label>
                            <input v-model="address.descripcion" :disabled="accion === 2" class="form-control" />
                            <small v-if="!address.descripcion && currentCustomer.enviado" class="text-danger">Requerido *</small>
                        </div>
                    </div>
                </div>

                <hr />
                <p><strong class="fw-bold">Números de Contacto</strong></p>
                <div v-for="(phone, index) in phones" :key="index" class="card border border-dark border-2 mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-0 pe-0">
                        <label class="text-dark fw-bold">Teléfono {{ index + 1 }}</label>
                        <div v-if="accion !== 2">
                            <button type="button" class="btn btn-success btn-sm" @click="addPhone"><i class="fas fa-plus"></i></button>
                            <button v-if="phones.length > 1" type="button" class="btn btn-danger btn-sm ms-2" @click="removePhone(index)"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="card-body grid">
                        <div>
                            <label class="fw-bold">Tipo</label>
                            <select v-model="phone.tipo" :disabled="accion === 2" class="form-select">
                                <option value="Numero telefono">Nr. Telf.</option>
                                <option value="Informacion contacto">Inf. Contacto</option>
                            </select>
                        </div>
                        <div>
                            <label class="fw-bold">Número</label>
                            <input v-model="phone.numero" :disabled="accion === 2" class="form-control" />
                            <small v-if="!phone.numero && currentCustomer.enviado" class="text-danger">Requerido *</small>
                        </div>
                        <div v-if="phone.tipo === 'Numero telefono'">
                            <label class="fw-bold">Observación</label>
                            <input v-model="phone.observacion" :disabled="accion === 2" class="form-control" />
                             <small v-if="!phone.observacion && phone.tipo === 'Numero telefono' && currentCustomer.enviado" class="text-danger">Requerido *</small>
                        </div>
                        <template v-if="phone.tipo === 'Informacion contacto'">
                            <div>
                                <label class="fw-bold">Nombre</label>
                                <input v-model="phone.nombre" :disabled="accion === 2" class="form-control text-capitalize" />
                                <small v-if="!phone.nombre && currentCustomer.enviado" class="text-danger">Requerido *</small>
                            </div>
                            <div>
                                <label class="fw-bold">Apellidos</label>
                                <input v-model="phone.apellidos" :disabled="accion === 2" class="form-control text-capitalize" />
                                <small v-if="!phone.apellidos && currentCustomer.enviado" class="text-danger">Requerido *</small>
                            </div>
                            <div>
                                <label class="fw-bold">Relación</label>
                                <input v-model="phone.relacion" :disabled="accion === 2" class="form-control" />
                                <small v-if="!phone.relacion && currentCustomer.enviado" class="text-danger">Requerido *</small>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" @click="cerrarFormulario">
                    <i class="fas fa-times-circle"></i> Cerrar
                </button>
                <button v-if="accion === 0" :disabled="savingCustomer" type="submit" class="btn btn-success ms-2">
                    <span v-if="!savingCustomer"><i class="fas fa-save"></i> Guardar</span>
                    <span v-else><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                </button>
                <button v-if="accion === 1" :disabled="modifyCustomer" type="submit" class="btn btn-success ms-2">
                    <span v-if="!modifyCustomer"><i class="fas fa-edit"></i> Modificar</span>
                    <span v-else><i class="fas fa-spinner fa-spin"></i> Modificando...</span>
                </button>
            </div>
        </form>

        <div class="modal fade" id="modalMapaComponente" tabindex="-1" aria-hidden="true">
             <div class="modal-dialog modal-lg">
                <div class="modal-content border border-dark border-2">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark">{{ mapModalTitle }}</h5>
                        <button @click="closeMapModal()" type="button" class="btn-close btn-close-dark" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         <div id="mapComponent" style="height: 400px; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeMapModal()" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cerrar</button>
                        <button v-if="mapModalMode !== 'view'" @click="saveLocation()" class="btn btn-success"><i class="fas fa-save"></i> Guardar Ubicación</button>
                    </div>
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
    name: 'ClienteForm',
    props: {
        clienteId: {
            type: [Number, String],
            default: 0
        },
        clienteData: { // Objeto básico (nombre, ci) pasado desde la lista
            type: Object,
            default: () => null
        },
        accion: {
            type: Number,
            default: 0 // 0: Nuevo, 1: Editar, 2: Ver
        }
    },
    emits: ['cerrar', 'guardado'],
    data() {
        return {
            // Datos estáticos
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
            addressTypes: ["Casa", "Oficina", "Negocio", "Apartamento", "Otra Residencia", "Domicilio Familiar", "Alquiler", "Propia", "Zona Rural"],

            // Estado del formulario
            currentCustomer: this.getDefaultCustomer(),
            addresses: [],
            phones: [],
            savingCustomer: false,
            modifyCustomer: false,
            
            // Actividades
            actividades: [],
            filteredItemsActividades: [],
            actividadClase: { id_actividad: 0, buscar: "" },

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
            if (this.accion === 0) return "Agregar Cliente";
            if (this.accion === 1) return "Modificar Cliente";
            return "Información del Cliente";
        }
    },
    watch: {
        // Observar cambios en el ID del cliente o la acción para cargar datos
        clienteId: {
            immediate: true,
            handler(newVal) {
                if (newVal && newVal !== 0) {
                    this.loadCustomerFullData(newVal);
                } else if (this.accion === 0) {
                    this.resetForm();
                }
            }
        },
        clienteData: {
            immediate: true,
            handler(newVal) {
                if (newVal && this.accion !== 0) {
                    this.currentCustomer = { ...newVal, enviado: 0 };
                    this.currentCustomer.lugar_expedicion = this.normalizeExpeditionPlace(this.currentCustomer.lugar_expedicion);
                    this.actividadClase.buscar = this.currentCustomer.actividad || "";
                }
            }
        }
    },
    mounted() {
        this.getActividades();
        // Si hay datos básicos pasados por prop al editar, inicializarlos
        if(this.clienteData && this.accion !== 0) {
             this.currentCustomer = { ...this.clienteData, enviado: 0 };
             this.currentCustomer.lugar_expedicion = this.normalizeExpeditionPlace(this.currentCustomer.lugar_expedicion);
             this.actividadClase.buscar = this.currentCustomer.actividad || "";
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
                imagen: "",
            };
        },
        resetForm() {
            this.currentCustomer = this.getDefaultCustomer();
            this.addresses = [{ tipo: "", departamento: "", ciudad: "", zona: "", descripcion: "" }];
            this.phones = [{ tipo: "Numero telefono", numero: "", observacion: "" }];
            this.actividadClase.buscar = "";
        },
        async loadCustomerFullData(id) {
            try {
                // Si no tenemos la data básica, la buscamos (opcional, depende de tu backend)
                // Aquí asumimos que fetchCustomerDetails trae telefonos y direcciones
                const response = await axios.get(`/get_direcciones_telefono?id_cliente=${id}`);
                this.addresses = response.data.direcciones.map(d => ({
                    ...d,
                    lat: d.lat ? parseFloat(d.lat) : null,
                    lng: d.lng ? parseFloat(d.lng) : null,
                }));
                this.phones = response.data.telefonos;
                
                // Asegurarse que currentCustomer tenga la data actualizada si es edición
                if(this.accion !== 0 && !this.currentCustomer.id) {
                    // Si no se pasó clienteData, tendrías que hacer un fetch del cliente aquí también
                    // Por ahora asumimos que el padre pasa clienteData o que este endpoint devuelve todo
                }
            } catch (error) {
                console.error("Error cargando detalles:", error);
            }
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
                    Swal.fire({ icon: "success", title: "Cliente guardado", timer: 1000 });
                    this.currentCustomer.id = response.data.id;
                    this.$emit('guardado', this.currentCustomer);
                    this.cerrarFormulario();
                } else if (response.data.error === "duplicate") {
                    Swal.fire("Cliente duplicado", response.data.message, "error");
                }
            } catch (error) {
                console.error("Error al guardar:", error);
                Swal.fire("Error", "Ocurrió un error al guardar", "error");
            } finally {
                this.savingCustomer = false;
            }
        },
        async updateCustomer() {
            if (!this.validateCustomer()) return;
            this.currentCustomer.enviado = 1;
            this.modifyCustomer = true;
            try {
                await axios.post("/modify_cliente", {
                    ...this.currentCustomer,
                    direcciones: this.addresses,
                    telefonos: this.phones,
                });
                Swal.fire({ icon: "success", title: "Cliente actualizado", timer: 1000 });
                this.$emit('guardado', this.currentCustomer);
                this.cerrarFormulario();
            } catch (error) {
                console.error("Error updating:", error);
            } finally {
                this.modifyCustomer = false;
            }
        },
        validateCustomer() {
            const customerValid = this.currentCustomer.nombre && this.currentCustomer.ci && 
                this.currentCustomer.ingreso_mensual && this.currentCustomer.actividad && 
                this.currentCustomer.estado_civil !== "0" && this.currentCustomer.vivienda !== "0" && 
                this.currentCustomer.sexo !== "0";
            
            const addressValid = this.addresses.every(a => a.tipo && a.departamento && a.descripcion);
            const phoneValid = this.phones.every(p => p.tipo && p.numero);
            
            if (!(customerValid && addressValid && phoneValid)) {
                Swal.fire("Advertencia", "Faltan datos por completar", "warning");
            }
            return customerValid && addressValid && phoneValid;
        },
        // --- Métodos Auxiliares (Direcciones, Teléfonos, Actividades) ---
        addAddress() {
            this.addresses.push({ tipo: "", departamento: "", ciudad: "", zona: "", descripcion: "", lat: null, lng: null });
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
        filterNumericInput(event) {
            event.target.value = event.target.value.replace(/[^0-9.]*/g, "");
        },
        async getActividades() {
            try {
                const response = await axios.get("/get_actividades");
                this.actividades = response.data;
            } catch (error) { console.log(error); }
        },
        filtrarActividades(keyword) {
            if (keyword === "") { this.filteredItemsActividades = []; return; }
            this.filteredItemsActividades = this.actividades.filter((act) => act.nombre.toLowerCase().includes(keyword.toLowerCase()));
        },
        seleccionarActividad(item) {
            this.actividadClase.id_actividad = item.id;
            this.actividadClase.buscar = item.nombre;
            this.currentCustomer.actividad = item.nombre;
            this.filteredItemsActividades = [];
        },
        // --- Lógica del Mapa ---
        openMapModal(index, mode) {
            this.currentAddressIndex = index;
            this.mapModalMode = mode;
            this.mapModalTitle = mode === 'add' ? 'Agregar Ubicación' : (mode === 'view' ? 'Ver Ubicación' : 'Actualizar Ubicación');
            
            // Usamos jQuery para abrir el modal porque así estaba en el original, 
            // pero le cambié el ID a #modalMapaComponente para evitar conflictos si se usa globalmente.
            $('#modalMapaComponente').modal('show'); 
            
            this.$nextTick(() => {
                const address = this.addresses[index];
                if (mode === 'view' && address.lat && address.lng) {
                    this.initializeMap(parseFloat(address.lat), parseFloat(address.lng));
                } else {
                    // Lógica de geolocalización por defecto
                    this.initializeMap(-17.7833, -63.1821); 
                }
            });
        },
        closeMapModal() {
            $('#modalMapaComponente').modal('hide');
        },
        initializeMap(lat, lng) {
            const mapElement = document.getElementById('mapComponent'); // ID único
            if (!mapElement) return;
            
            this.map = new google.maps.Map(mapElement, { center: { lat, lng }, zoom: 12 });
            this.marker = new google.maps.Marker({
                map: this.map, position: { lat, lng }, draggable: this.mapModalMode !== 'view'
            });
            
            if (this.mapModalMode !== 'view') {
                this.map.addListener('click', (event) => {
                    this.marker.setPosition(event.latLng);
                    this.updateAddressCoordinates(event.latLng.lat(), event.latLng.lng());
                });
                this.marker.addListener('dragend', () => {
                    const pos = this.marker.getPosition();
                    this.updateAddressCoordinates(pos.lat(), pos.lng());
                });
            }
        },
        updateAddressCoordinates(lat, lng) {
            if (this.currentAddressIndex !== null) {
                this.addresses[this.currentAddressIndex].lat = lat;
                this.addresses[this.currentAddressIndex].lng = lng;
            }
        },
        saveLocation() {
            // Validar y cerrar
            if(this.addresses[this.currentAddressIndex].lat) {
                 this.closeMapModal();
                 Swal.fire({icon: 'success', title: 'Ubicación guardada', timer: 1000, showConfirmButton: false});
            }
        }
    }
};
</script>

<style scoped>
@import './../styles/frmCliente.css';
/* Estilos adicionales específicos del form si son necesarios */
.com-completion-results::-webkit-scrollbar { width: 8px; }
.com-completion-results::-webkit-scrollbar-track { background: #f1f1f1; }
.com-completion-results::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
</style>