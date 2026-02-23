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
                                    <input v-model="searchQuery" type="text" class="form-control" @input="buscarCliente()" />
                                    <button class="btn btn-success"><i class="fas fa-search"></i></button>
                                    <button class="btn btn-warning ms-1" @click="imprimirReporte()"><i class="fas fa-print"></i></button>
                                </div>
                            </div>
                            <div class="col-md-4 my-1 text-end">
                                <button @click="openNewCustomerModal()" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Nuevo
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive table-customers" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-success text-white text-uppercase fw-bold">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>CI</th>
                                        <th>Sexo</th>
                                        <th>E. Civil</th>
                                        <th>Actividad</th>
                                        <th>Vivienda</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers" :key="customer.id" class="align-middle">
                                        <td class="text-uppercase fw-bold">{{ customer.nombre }}</td>
                                        <td class="text-uppercase fw-bold">{{ customer.ci }}</td>
                                        <td class="text-uppercase">{{ customer.sexo }}</td>
                                        <td class="text-uppercase">{{ customer.estado_civil }}</td>
                                        <td class="text-uppercase">{{ customer.actividad }}</td>
                                        <td class="text-uppercase">{{ customer.vivienda }}</td>
                                        <td class="text-uppercase">
                                            <span :class="customer.estado === 1 ? 'badge bg-success w-100' : 'badge bg-danger w-100'">
                                                {{ customer.estado === 1 ? "Activo" : "Inactivo" }}
                                            </span>
                                        </td>
                                        <td class="position-relative p-2 text-center">
                                            <div class="btn-group" role="group">
                                                <a style="cursor: pointer" class="text-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li v-if="customer.estado === 1" @click="toggleCustomerStatus(customer)">
                                                        <a class="dropdown-item text-danger" href="#"><i class="fas fa-times me-2"></i> Desactivar</a>
                                                    </li>
                                                    <li v-else @click="toggleCustomerStatus(customer)">
                                                        <a class="dropdown-item text-success" href="#"><i class="fas fa-check me-2"></i> Activar</a>
                                                    </li>
                                                    <li @click="editCustomer(customer)">
                                                        <a class="dropdown-item text-primary" href="#"><i class="fas fa-pencil-alt me-2"></i> Editar</a>
                                                    </li>
                                                    <li @click="viewCustomer(customer)">
                                                        <a class="dropdown-item text-info" href="#"><i class="fas fa-eye me-2"></i> Ver</a>
                                                    </li>
                                                    <li @click="viewCustomerPdf(customer)">
                                                        <a class="dropdown-item text-danger" href="#"><i class="fas fa-file-pdf me-2"></i> PDF</a>
                                                    </li>
                                                    <li @click="openPhotoModal(customer)">
                                                        <a class="dropdown-item text-warning" href="#">
                                                            <i class="fas fa-user me-2"></i> {{ customer.imagen ? "Ver/Cambiar Foto" : "Agregar Foto" }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="customers.length <= 7">
                                <br><br><br><br><br><br><br><br><br><br><br><br><br>
                            </template>
                        </div>
                    </div>
                </div>

                <ClienteForm 
                    v-if="view === 2"
                    :cliente-id="selectedCustomerId"
                    :cliente-data="selectedCustomerData"
                    :accion="formAction"
                    @cerrar="closeModal"
                    @guardado="onCustomerSaved"
                />

                <div id="modalFoto" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-dark border-2">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title text-dark"><i class="fas fa-camera me-2"></i>Foto del Cliente</h5>
                                <button type="button" class="btn-close btn-close-dark" @click="closePhotoModal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="image-preview-container mb-4">
                                    <img :src="previewImage || (selectedCustomerData && selectedCustomerData.imagen ? `/img/cliente/${selectedCustomerData.imagen}` : '/img/cliente/default.png')" class="img-thumbnail customer-photo" alt="Foto" />
                                </div>
                                <div class="file-upload-wrapper">
                                    <label for="customerPhotoUpload" class="btn btn-outline-success w-100">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Seleccionar nueva foto
                                    </label>
                                    <input id="customerPhotoUpload" type="file" class="d-none" accept="image/*" @change="previewPhoto" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" @click="closePhotoModal"><i class="fas fa-times-circle me-1"></i> Cerrar</button>
                                <button class="btn btn-success" @click="savePhoto" :disabled="!selectedFile"><i class="fas fa-save me-1"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";
import ClienteForm from './Cliente/ClienteForm.vue'; 

export default {
    components: {
        ClienteForm
    },
    data() {
        return {
            preloader: false,
            customers: [],
            searchCriteria: "cliente.nombre",
            searchQuery: "",
            view: 0,
            
            // Variables para interactuar con el componente hijo y modals
            selectedCustomerId: 0,
            selectedCustomerData: null,
            formAction: 0, // 0: Nuevo, 1: Editar, 2: Ver

            // Foto Modal Vars
            selectedFile: null,
            previewImage: null,
            
            debounceTimeout: null,
        };
    },
    methods: {
        // --- Navegación y Apertura del Formulario ---
        openNewCustomerModal() {
            this.selectedCustomerId = 0;
            this.selectedCustomerData = null;
            this.formAction = 0;
            this.view = 2; // Mostrar componente hijo
        },
        editCustomer(customer) {
            this.selectedCustomerId = customer.id;
            this.selectedCustomerData = customer; // Pasamos datos básicos para llenar mientras carga
            this.formAction = 1;
            this.view = 2;
        },
        viewCustomer(customer) {
            this.selectedCustomerId = customer.id;
            this.selectedCustomerData = customer;
            this.formAction = 2;
            this.view = 2;
        },
        closeModal() {
            this.view = 0;
            this.selectedCustomerId = 0;
            this.selectedCustomerData = null;
        },
        async onCustomerSaved() {
            // El formulario nos avisa que guardó, refrescamos la lista
            await this.fetchCustomers(1);
        },

        // --- Lógica del Listado (API Calls) ---
        buscarCliente() {
            if (this.debounceTimeout) clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                this.fetchCustomers(1);
            }, 300);
        },
        async fetchCustomers(page) {
            try {
                const response = await axios.get("/get_clientes", {
                    params: {
                        page,
                        criterio: this.searchCriteria,
                        buscar: this.searchQuery,
                        opcion_asesor: 0, // Asumo valor por defecto si no se usa
                    },
                });
                this.customers = response.data;
            } catch (error) {
                console.error("Error fetching customers:", error);
            }
        },
        async toggleCustomerStatus(customer) {
            try {
                const endpoint = customer.estado === 1 ? "/desactivar_cliente" : "/activar_cliente";
                await axios.get(`${endpoint}?id_cliente=${customer.id}`);
                await this.fetchCustomers(1);
            } catch (error) {
                console.error("Error toggling status:", error);
            }
        },
        
        imprimirReporte() {
            Swal.fire({
                title: 'Generando Reporte',
                text: 'Por favor espere...',
                icon: 'info',
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            axios.get('/cliente/reporte', {
                params: { buscar: this.searchQuery, criterio: this.searchCriteria },
                responseType: 'blob'
            }).then((response) => {
                const file = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(file);
                window.open(url, '_blank');
                Swal.close();
            }).catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar el reporte.' });
            });
        },
        
        async viewCustomerPdf(customer) {
            this.preloader = true;
            try {
                const response = await axios.get("/clientes_pdf", {
                    params: { id_cliente: customer.id },
                    responseType: "blob",
                });
                const blob = new Blob([response.data], { type: "application/pdf" });
                window.open(window.URL.createObjectURL(blob), "_blank");
            } catch (error) {
                console.error("Error fetching PDF:", error);
            } finally {
                this.preloader = false;
            }
        },

        // --- Lógica de Foto (Mantenida en el listado para acciones rápidas) ---
        openPhotoModal(customer) {
            this.selectedCustomerData = customer;
            $("#modalFoto").modal("show");
        },
        closePhotoModal() {
            this.previewImage = null;
            this.selectedFile = null;
            document.getElementById('customerPhotoUpload').value = '';
            $('#modalFoto').modal('hide');
        },
        previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                 this.selectedFile = file;
                 const reader = new FileReader();
                 reader.onload = (e) => { this.previewImage = e.target.result; };
                 reader.readAsDataURL(file);
            }
        },
        async savePhoto() {
            if (!this.selectedFile) return;
            const formData = new FormData();
            formData.append("id_cliente", this.selectedCustomerData.id); // Ojo: verifica si es .id o .id_cliente en tu objeto customer
            formData.append("imagen", this.selectedFile);

            try {
                await axios.post("/fotoCliente", formData);
                Swal.fire({ icon: "success", title: "Foto actualizada", timer: 1500 });
                this.closePhotoModal();
                await this.fetchCustomers(1);
            } catch (error) {
                Swal.fire({ icon: "error", title: "Error", text: "No se pudo actualizar la foto" });
            }
        }
    },
    async mounted() {
        this.preloader = true;
        await this.fetchCustomers(1);
        this.preloader = false;
    },
};
</script>

<style scoped>
    @import './styles/frmCliente.css';
</style>